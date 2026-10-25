<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

/** One person in a bulk send, with the placeholder values worked out when the send started. */
class BulkSendRecipient extends Model
{
    const PENDING = 'pending';
    const SENT = 'sent';
    const FAILED = 'failed';
    const SKIPPED = 'skipped';
    const CANCELLED = 'cancelled';

    protected $table = 'bulk_send_recipients';
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = ['values' => 'array'];
    protected $dates = ['processed_at'];

    public function bulkSend()
    {
        return $this->belongsTo(BulkSend::class, 'bulk_send_id');
    }
}
