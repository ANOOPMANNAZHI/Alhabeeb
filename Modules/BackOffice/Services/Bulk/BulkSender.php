<?php

namespace Modules\BackOffice\Services\Bulk;

use Modules\BackOffice\Entities\BulkSend;
use Modules\BackOffice\Entities\BulkSendRecipient;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Services\MessageRecipient;
use Modules\BackOffice\Services\SystemMessenger;

/**
 * Runs every minute (bulk-messages:send): sends the next pending recipients of
 * running bulk sends at the configured rate, below Office 365's limits.
 */
class BulkSender
{
    const SAFETY_STOP_AFTER = 5;
    /** Office 365 allows 30 messages a minute per mailbox; a higher setting is capped. */
    const MAX_PER_MINUTE = 30;

    private static function setting($key, $default)
    {
        $row = prefixData($key);
        $v = $row ? (int) $row->configuration_value : 0;
        return $v > 0 ? $v : $default;
    }

    public static function emailsToday()
    {
        return MessageLog::where('related_type', 'bulk_send')->where('channel', 'email')
            ->whereIn('status', [MessageLog::SENT, MessageLog::FAILED])
            ->where('created_at', '>=', now()->startOfDay())->count();
    }

    public static function run()
    {
        $summary = ['processed' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
        $perMinute = min(self::MAX_PER_MINUTE, self::setting('bulk_emails_per_minute', 25));
        $perDay = self::setting('bulk_emails_per_day', 8000);
        $today = self::emailsToday();

        if ($today >= $perDay) {
            BulkSend::where('status', BulkSend::RUNNING)->update(['note' => BulkSend::NOTE_DAILY_LIMIT]);
            return $summary;
        }
        BulkSend::where('status', BulkSend::RUNNING)->where('note', BulkSend::NOTE_DAILY_LIMIT)->update(['note' => null]);

        $limit = min($perMinute, $perDay - $today);
        $rows = BulkSendRecipient::where('status', BulkSendRecipient::PENDING)
            ->whereIn('bulk_send_id', BulkSend::where('status', BulkSend::RUNNING)->pluck('id'))
            ->orderBy('bulk_send_id')->orderBy('id')->limit($limit)->get();

        $sends = [];
        foreach ($rows as $row) {
            if (!isset($sends[$row->bulk_send_id])) {
                $sends[$row->bulk_send_id] = BulkSend::find($row->bulk_send_id);
            }
            $send = $sends[$row->bulk_send_id];
            if ($send->fresh()->status !== BulkSend::RUNNING) {
                continue;
            }
            try {
                $status = self::sendOne($send, $row);
            } catch (\Throwable $e) {
                // One bad row must never stall the send or be sent again on the next run.
                $status = BulkSendRecipient::FAILED;
                $changed = BulkSendRecipient::where('id', $row->id)->where('status', BulkSendRecipient::PENDING)->update([
                    'status' => BulkSendRecipient::FAILED,
                    'note' => mb_substr('error: ' . $e->getMessage(), 0, 255, 'UTF-8'),
                    'processed_at' => now(),
                ]);
                if ($changed === 1) {
                    $send->increment('failed');
                }
                \Log::error('Bulk send #' . $send->id . ' row #' . $row->id . ' failed: ' . $e->getMessage());
            }
            $summary['processed']++;
            $summary[$status]++;
        }

        foreach (BulkSend::where('status', BulkSend::RUNNING)->get() as $send) {
            if ($send->pendingCount() === 0) {
                $send->update(['status' => BulkSend::DONE, 'finished_at' => now()]);
            }
        }
        return $summary;
    }

    /** @return string sent|failed|skipped */
    private static function sendOne(BulkSend $send, BulkSendRecipient $row)
    {
        $result = SystemMessenger::sendTexts(
            $send->logKey(), $send->texts(),
            new MessageRecipient($row->name, $row->email, $row->mobile),
            $row->values ? $row->values : [],
            ['related' => ['bulk_send', $send->id], 'channels' => ['email' => $send->send_email, 'sms' => $send->send_sms], 'with_notes' => true]
        );

        $parts = [];
        $statuses = [];
        foreach (['email', 'sms'] as $ch) {
            if ($result[$ch] !== null) {
                $reason = isset($result['notes'][$ch]) ? trim((string) $result['notes'][$ch]) : '';
                // the failure reason, as written (masked) to the sent log
                $parts[] = $ch . ': ' . $result[$ch] . ($result[$ch] === MessageLog::FAILED && $reason !== '' ? ' (' . $reason . ')' : '');
                $statuses[] = $result[$ch];
            }
        }
        if (in_array(MessageLog::SENT, $statuses, true)) {
            $status = BulkSendRecipient::SENT;
        } elseif (in_array(MessageLog::FAILED, $statuses, true)) {
            $status = BulkSendRecipient::FAILED;
        } else {
            $status = BulkSendRecipient::SKIPPED;
        }
        $row->update(['status' => $status, 'note' => mb_substr(implode('; ', $parts), 0, 255, 'UTF-8'), 'processed_at' => now()]);
        $send->increment($status);

        if ($result['email'] === MessageLog::FAILED) {
            $send->increment('email_fail_streak');
            if ((int) $send->fresh()->email_fail_streak >= self::SAFETY_STOP_AFTER) {
                // only a running send: never overwrite a Cancel (or Pause) pressed meanwhile
                BulkSend::where('id', $send->id)->where('status', BulkSend::RUNNING)
                    ->update(['status' => BulkSend::PAUSED, 'note' => BulkSend::NOTE_SAFETY_STOP]);
            }
        } elseif ($result['email'] === MessageLog::SENT && (int) $send->email_fail_streak !== 0) {
            $send->update(['email_fail_streak' => 0]);
        }
        return $status;
    }
}
