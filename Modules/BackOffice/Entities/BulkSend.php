<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

/** A bulk send (Mail -> Send). Texts are a frozen copy taken when the send started. */
class BulkSend extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    const RUNNING = 'running';
    const PAUSED = 'paused';
    const CANCELLED = 'cancelled';
    const DONE = 'done';

    const NOTE_DAILY_LIMIT = 'Daily limit reached, continues tomorrow';
    const NOTE_SAFETY_STOP = 'Paused: mail server refusing';

    const AUDIENCE_LABELS = [
        'tenant_active' => 'Active tenants',
        'tenant_past'   => 'Past tenants (ended in the last 12 months)',
        'landlord'      => 'Landlords',
        'staff'         => 'Staff',
    ];

    protected $table = 'bulk_sends';
    protected $guarded = [];
    protected $casts = ['filters' => 'array', 'send_email' => 'boolean', 'send_sms' => 'boolean',
        'finished_at' => 'datetime',
    ];

    public function recipients()
    {
        return $this->hasMany(BulkSendRecipient::class, 'bulk_send_id');
    }

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id')->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /** message_log.template_key for this send's messages. */
    public function logKey()
    {
        return 'bulk:' . ($this->template_id ? $this->template_id : 'one-off');
    }

    public function texts()
    {
        return ['email_subject' => $this->email_subject, 'email_body' => $this->email_body, 'sms_body' => $this->sms_body];
    }

    public function pendingCount()
    {
        return $this->recipients()->where('status', BulkSendRecipient::PENDING)->count();
    }
}
