<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class BuildingInsurance extends Model
{
	 use sortable;
     protected $fillable = [];
     protected $guarded = [];
     protected $table = 'building_insurance';
     protected $dates=['insurance_start','insurance_end'];
	 public $sortable = ['insurance_company','insurance_start','insurance_end'];
	
      /*
  	* Building
  	*
  	*/
  	public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building','building_id');
  	}
}
