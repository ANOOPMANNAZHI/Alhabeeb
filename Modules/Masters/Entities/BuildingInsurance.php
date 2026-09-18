<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class BuildingInsurance extends Model
{
    use \App\Concerns\LegacyDateSerialization;
	 use sortable;
     protected $fillable = [];
     protected $guarded = [];
     protected $table = 'building_insurance';
     protected $casts = ['insurance_start' => 'datetime', 'insurance_end' => 'datetime'];
	 public $sortable = ['insurance_company','insurance_start','insurance_end'];
	
      /*
  	* Building
  	*
  	*/
  	public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building','building_id');
  	}
}
