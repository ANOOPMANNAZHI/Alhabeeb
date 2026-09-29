<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;

/**
 * Sends a system template to one recipient: email when the template has one
 * and the recipient has an address, SMS only when the template's Send SMS
 * switch is on. Never throws for delivery problems; every attempt is logged.
 */
class SystemMessenger
{
    /** @var callable ($mobile, $message) => bool; replaced in tests */
    public static $smsSender = 'sendSms';

    public static function send($key, MessageRecipient $to, array $values, array $options = [])
    {
        $note = null;
        $template = MessageTemplate::where('key', $key)->where('kind', MessageTemplate::KIND_SYSTEM)->first();
        if (!$template) {
            $d = SystemTemplateDefaults::get($key);
            if (!$d) {
                throw new \InvalidArgumentException("Unknown system template key: $key");
            }
            unset($d['stage'], $d['placeholders']);
            $template = new MessageTemplate($d + ['key' => $key, 'kind' => MessageTemplate::KIND_SYSTEM]);
            $note = 'template_missing: default wording used';
        }
        $base = [
            'template_key' => $key, 'recipient_name' => $to->name,
            'related_type' => isset($options['related'][0]) ? $options['related'][0] : null,
            'related_id'   => isset($options['related'][1]) ? $options['related'][1] : null,
            'triggered_by' => \Auth::check() ? \Auth::id() : null,
        ];
        $result = ['email' => null, 'sms' => null];

        if (!$template->is_active) {
            // Log against the channel the template actually uses (an SMS-only template shows as SMS).
            $channel = ($template->email_subject || $template->email_body || !$template->sms_body) ? 'email' : 'sms';
            self::log($base + ['channel' => $channel, 'recipient' => $channel === 'sms' ? $to->mobile : $to->email,
                               'status' => MessageLog::SKIPPED_OFF, 'note' => $note]);
            $result[$channel] = MessageLog::SKIPPED_OFF;
            return $result;
        }

        $values = $values + ['today' => date('d/m/Y'), 'company_name' => 'Al Habib & Co. L.L.C'];
        $blocks = isset($options['blocks']) ? $options['blocks'] : [];
        $out = MessageTemplateRenderer::render($template, $values, $blocks);
        if ($out['unknown']) {
            $note = trim($note . ' unknown placeholders: ' . implode(', ', $out['unknown']));
        }

        if ($out['subject'] !== null && $out['html'] !== null) {
            $row = $base + ['channel' => 'email', 'recipient' => $to->email, 'cc' => $to->cc ? implode(', ', $to->cc) : null,
                            'subject' => $out['subject'], 'body' => $out['html'], 'note' => $note];
            if (!$to->email) {
                $result['email'] = MessageLog::SKIPPED_NO_ADDRESS;
            } else {
                try {
                    $pending = Mail::to($to->email);
                    if ($to->cc) {
                        $pending->cc($to->cc);
                    }
                    $pending->send(new TemplatedMail($out['subject'], $out['html'], isset($options['attachments']) ? $options['attachments'] : []));
                    $result['email'] = MessageLog::SENT;
                } catch (\Throwable $e) {
                    $result['email'] = MessageLog::FAILED;
                    $row['note'] = trim($note . ' ' . $e->getMessage());
                }
            }
            self::log($row + ['status' => $result['email']]);
        }

        if ($template->sms_enabled && $out['sms'] !== null) {
            $row = $base + ['channel' => 'sms', 'recipient' => $to->mobile, 'body' => $out['sms'], 'note' => $note];
            if (!$to->mobile) {
                $result['sms'] = MessageLog::SKIPPED_NO_MOBILE;
            } else {
                try {
                    $ok = call_user_func(self::$smsSender, $to->mobile, $out['sms'], []);
                    $result['sms'] = $ok ? MessageLog::SENT : MessageLog::FAILED;
                    if (!$ok) {
                        $row['note'] = trim($note . ' SMS gateway refused or SMS is disabled in settings');
                    }
                } catch (\Throwable $e) {
                    $result['sms'] = MessageLog::FAILED;
                    $row['note'] = trim($note . ' ' . $e->getMessage());
                }
            }
            self::log($row + ['status' => $result['sms']]);
        }
        return $result;
    }

    private static function log(array $row)
    {
        // Fit the columns: on PostgreSQL an overflowing insert would abort a caller's open transaction.
        $limits = ['template_key' => 100, 'recipient_name' => 150, 'recipient' => 190, 'subject' => 255, 'related_type' => 50];
        foreach ($limits as $field => $max) {
            if (isset($row[$field]) && mb_strlen((string) $row[$field], 'UTF-8') > $max) {
                $row[$field] = mb_substr((string) $row[$field], 0, $max, 'UTF-8');
            }
        }
        try {
            // Inside a caller's transaction this is a SAVEPOINT: a failed insert rolls back only
            // itself instead of aborting the caller's (PostgreSQL) transaction, e.g. a bank-sheet posting.
            \DB::transaction(function () use ($row) {
                MessageLog::create($row + ['created_at' => now()]);
            });
        } catch (\Throwable $e) {
            \Log::error('message_log write failed: ' . $e->getMessage());
        }
    }
}
