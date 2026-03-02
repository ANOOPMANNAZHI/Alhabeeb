<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;


class TerminationUser extends Model
{
    protected $guarded = [];
    protected $table = 'termination_users';
    public $timestamps = false;
}
