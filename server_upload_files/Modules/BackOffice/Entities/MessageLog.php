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
    protected $dates = ['created_at'];
}
