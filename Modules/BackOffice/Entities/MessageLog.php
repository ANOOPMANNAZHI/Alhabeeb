<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    const SENT = 'sent';
    const FAILED = 'failed';
    const SKIPPED_OFF = 'skipped_off';
    const SKIPPED_NO_ADDRESS = 'skipped_no_address';
    const SKIPPED_NO_MOBILE = 'skipped_no_mobile';
    const STATUSES = [
        'sent' => 'Sent', 'failed' => 'Failed', 'skipped_off' => 'Skipped: switched off',
        'skipped_no_address' => 'Skipped: no email address', 'skipped_no_mobile' => 'Skipped: no mobile',
    ];

    protected $table = 'message_log';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime'];

    /** Readable template name for the Sent log (bulk sends store bulk:<id> / bulk:one-off). */
    public function getTemplateLabelAttribute()
    {
        $key = (string) $this->template_key;
        if ($key === 'bulk:one-off') {
            return 'Bulk: one-off message';
        }
        if (strpos($key, 'bulk:') === 0) {
            $t = MessageTemplate::withTrashed()->find((int) substr($key, 5));
            return 'Bulk: ' . ($t ? $t->name : 'template #' . substr($key, 5));
        }
        $t = MessageTemplate::where('key', $key)->first();
        return $t ? $t->name : $key;
    }
}
