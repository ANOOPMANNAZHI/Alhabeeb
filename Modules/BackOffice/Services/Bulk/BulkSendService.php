<?php

namespace Modules\BackOffice\Services\Bulk;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\BackOffice\Entities\BulkSend;
use Modules\BackOffice\Entities\BulkSendRecipient;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\MessagePlaceholders;
use Modules\BackOffice\Services\MessageTemplateRenderer;

/** Creating and controlling bulk sends. Sending itself is BulkSender (every minute). */
class BulkSendService
{
    private static function fail($field, $message)
    {
        throw ValidationException::withMessages([$field => [$message]]);
    }

    private static function blank($v)
    {
        return trim((string) $v) === '';
    }

    public static function texts(array $in, $audience)
    {
        if (!empty($in['template_id'])) {
            $t = MessageTemplate::where('kind', MessageTemplate::KIND_BULK)->where('is_active', true)->find((int) $in['template_id']);
            if (!$t) {
                self::fail('template_id', 'Choose an active Bulk template.');
            }
            if ($t->audience !== 'any' && $t->audience !== RecipientFinder::templateAudience($audience)) {
                $labels = MessagePlaceholders::AUDIENCES;
                self::fail('template_id', 'This template is for ' . $labels[$t->audience] . ' and cannot be sent to ' . BulkSend::AUDIENCE_LABELS[$audience] . '.');
            }
            return ['template_id' => $t->id, 'email_subject' => $t->email_subject, 'email_body' => $t->email_body, 'sms_body' => $t->sms_body];
        }
        $body = isset($in['email_body']) ? (string) $in['email_body'] : '';
        $body = MessageTemplateRenderer::isBlankHtml($body) ? null : MessageTemplateRenderer::cleanHtml($body);
        return [
            'template_id'   => null,
            'email_subject' => self::blank(isset($in['email_subject']) ? $in['email_subject'] : '') ? null : trim($in['email_subject']),
            'email_body'    => $body,
            'sms_body'      => self::blank(isset($in['sms_body']) ? $in['sms_body'] : '') ? null : trim($in['sms_body']),
        ];
    }

    /** Audience, channels and texts checked together; used by start() and the review screen. */
    public static function checkMessage(array $in)
    {
        $audience = isset($in['audience']) ? (string) $in['audience'] : '';
        if (!isset(BulkSend::AUDIENCE_LABELS[$audience])) {
            self::fail('audience', 'Choose who to send to.');
        }
        $email = !empty($in['send_email']);
        $sms = !empty($in['send_sms']);
        if (!$email && !$sms) {
            self::fail('channels', 'Choose email, SMS or both.');
        }
        $texts = self::texts($in, $audience);
        if ($email && ($texts['email_subject'] === null || $texts['email_body'] === null)) {
            self::fail('email_subject', 'Email needs a subject and a body.');
        }
        if ($sms && $texts['sms_body'] === null) {
            self::fail('sms_body', 'This message has no SMS text.');
        }
        return $texts;
    }

    public static function start(array $in, $userId)
    {
        $texts = self::checkMessage($in);
        $audience = (string) $in['audience'];
        $email = !empty($in['send_email']);
        $sms = !empty($in['send_sms']);

        $filters = isset($in['filters']) ? (array) $in['filters'] : [];
        $people = RecipientFinder::people($audience, $filters);
        foreach (isset($in['exclude']) ? (array) $in['exclude'] : [] as $key) {
            unset($people[$key]);
        }
        $everyone = null; // the whole audience, loaded at most once for all added people
        foreach (isset($in['add']) ? (array) $in['add'] : [] as $key) {
            if (!isset($people[$key])) {
                if ($everyone === null) {
                    $everyone = RecipientFinder::people($audience, []);
                }
                if (isset($everyone[$key])) {
                    $people[$key] = $everyone[$key];
                }
            }
        }
        unset($everyone);
        if (!$people) {
            self::fail('recipients', 'Nobody to send to.');
        }

        return DB::transaction(function () use ($texts, $audience, $filters, $email, $sms, $people, $userId) {
            $send = BulkSend::create($texts + [
                'audience' => $audience, 'filters' => $filters, 'send_email' => $email, 'send_sms' => $sms,
                'status' => BulkSend::RUNNING, 'total' => count($people), 'created_by' => $userId,
            ]);
            self::insertRecipients($send, array_values($people));
            return $send;
        });
    }

    private static function insertRecipients(BulkSend $send, array $people)
    {
        foreach (array_chunk($people, 500) as $chunk) {
            $rows = [];
            foreach ($chunk as $p) {
                $rows[] = [
                    'bulk_send_id' => $send->id, 'person_type' => $p['person_type'], 'person_id' => $p['person_id'],
                    'name' => mb_substr((string) $p['name'], 0, 150, 'UTF-8'), 'email' => $p['email'] !== null ? mb_substr($p['email'], 0, 190, 'UTF-8') : null,
                    'mobile' => $p['mobile'] !== null ? mb_substr($p['mobile'], 0, 30, 'UTF-8') : null,
                    'values' => json_encode($p['values']), 'status' => BulkSendRecipient::PENDING,
                ];
            }
            DB::table('bulk_send_recipients')->insert($rows);
        }
    }

    public static function pause(BulkSend $send)
    {
        if ($send->status === BulkSend::RUNNING) {
            $send->update(['status' => BulkSend::PAUSED]);
        }
    }

    public static function resume(BulkSend $send)
    {
        if ($send->status === BulkSend::PAUSED) {
            $send->update(['status' => BulkSend::RUNNING, 'note' => null, 'email_fail_streak' => 0]);
        }
    }

    public static function cancel(BulkSend $send)
    {
        if (in_array($send->status, [BulkSend::RUNNING, BulkSend::PAUSED], true)) {
            DB::transaction(function () use ($send) {
                $send->recipients()->where('status', BulkSendRecipient::PENDING)
                    ->update(['status' => BulkSendRecipient::CANCELLED, 'processed_at' => now()]);
                $send->update(['status' => BulkSend::CANCELLED, 'finished_at' => now()]);
            });
        }
    }

    public static function resendFailed(BulkSend $send, $userId)
    {
        return DB::transaction(function () use ($send, $userId) {
            // Locked re-read: two clicks (or two open pages) cannot both resend.
            $send = BulkSend::where('id', $send->id)->lockForUpdate()->firstOrFail();
            if (!in_array($send->status, [BulkSend::DONE, BulkSend::CANCELLED], true)) {
                self::fail('recipients', 'Resend is only possible for a finished or cancelled send.');
            }
            if ($send->resent_send_id) {
                self::fail('recipients', 'Failed messages of this send were already resent (send #' . $send->resent_send_id . ').');
            }
            $failed = $send->recipients()->where('status', BulkSendRecipient::FAILED)->orderBy('id')->get();
            if ($failed->isEmpty()) {
                self::fail('recipients', 'No failed messages to resend.');
            }
            $new = BulkSend::create($send->texts() + [
                'template_id' => $send->template_id, 'audience' => $send->audience, 'filters' => $send->filters,
                'send_email' => $send->send_email, 'send_sms' => $send->send_sms,
                'status' => BulkSend::RUNNING, 'total' => $failed->count(), 'created_by' => $userId,
            ]);
            $people = [];
            foreach ($failed as $r) {
                $people[] = ['person_type' => $r->person_type, 'person_id' => $r->person_id, 'name' => $r->name,
                             'email' => $r->email, 'mobile' => $r->mobile, 'values' => $r->values ? $r->values : []];
            }
            self::insertRecipients($new, $people);
            $send->update(['resent_send_id' => $new->id]);
            return $new;
        });
    }
}
