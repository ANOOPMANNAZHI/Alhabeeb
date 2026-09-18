<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;

class UnitTypeCount extends Model
{
    use \App\Concerns\LegacyDateSerialization;

	
	protected $guarded = [];
	protected $table = 'unit_type_count';
     
}
