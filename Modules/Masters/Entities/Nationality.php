<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [];
    protected $table = 'nationalities';
}
