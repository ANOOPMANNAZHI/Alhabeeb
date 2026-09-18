<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class RequestHistory extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [];
    protected $table = 'approve_unapprove_request_history';
    protected $casts = ['created_at' => 'datetime'];
}
