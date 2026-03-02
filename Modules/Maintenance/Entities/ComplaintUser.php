<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;

class ComplaintUser extends Model
{
    protected $guarded = [];
    protected $table = 'complaint_users';
    public $timestamps = false;
}
