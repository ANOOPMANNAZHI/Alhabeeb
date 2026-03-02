<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;

class LegalUser extends Model
{
    protected $guarded = [];
    protected $table = 'legal_users';
    public $timestamps = false;
}
