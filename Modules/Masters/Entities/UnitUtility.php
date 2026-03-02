<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class UnitUtility extends Model
{
	use Sortable;
	
    protected $guarded = [];
    
    public $sortable = ['id','amc_contract_no'];


    /*
  	* HomeUtility
  	*
  	*/
  	  public function homeUtility(){

      return $this->belongsTo('Modules\Masters\Entities\HomeUtility','home_utility_id');
  	}



    /*
  	* HomeUtility
  	*
  	*/
  	  public function unit(){

      return $this->belongsTo('Modules\Masters\Entities\Unit','unit_id');
  	}
	
  	
}
