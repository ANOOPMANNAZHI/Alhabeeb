<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class BuildingAmentity extends Model
{
	use Sortable;
	
    protected $guarded = [];

    protected $table = 'building_amentity';
    
    public $sortable = ['id','amc_contract_no'];


    /*
  	* amentityType
  	*
  	*/
  	  public function amentityType(){

      return $this->belongsTo('Modules\Masters\Entities\AmentityType','amentity_type_id');
  	}
  


    /*
  	* amentityType
  	*
  	*/
  	  public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building','landlord_building_id');
  	}
	/*
    * AMC
    *
    */
      public function amc(){

      return $this->belongsTo('Modules\Maintenance\Entities\AmcContract','amc_contract_no','amc_contract_no');
    }
}
