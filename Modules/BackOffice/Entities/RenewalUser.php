<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class RenewalUser extends Model
{
    protected $guarded = [];
    protected $table = 'renewal_users';
    public $timestamps = false;
}
