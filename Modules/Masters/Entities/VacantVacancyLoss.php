<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;

class VacantVacancyLoss extends Model
{
    use Sortable;
	
  protected $guarded = [];

  protected $table = 'vacant_vacany_loss';

  protected $dates = [ 'vacant_from','vacant_to'];
  
  public $sortable = ['building_name','building_no','unit_no','location_name','rent_per_month','unit_type'];


  /*
	* Building
	*
	*/
  public function building(){

    return $this->belongsTo('Modules\Masters\Entities\Building','building_id');
	}


	/*
	* Unit Type
	*
	*/
	public function unit(){

	return $this->belongsTo('Modules\Masters\Entities\UnitType','unit_type_id');
	}
}
