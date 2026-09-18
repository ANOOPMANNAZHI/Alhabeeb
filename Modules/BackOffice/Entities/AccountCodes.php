<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class AccountCodes extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $fillable = [];

    protected $guarded = [];
    protected $table = 'acc_codes';
}
