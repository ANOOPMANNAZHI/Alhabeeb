<?php

namespace Modules\General\Entities;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use \App\Concerns\LegacyDateSerialization;
        protected $guarded = [];
    protected $fillable = [];
    protected $table = 'actions';
}
