<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class RequestHistory extends Model
{
    protected $guarded = [];
    protected $table = 'approve_unapprove_request_history';
    protected $dates = ['created_at'];
}
