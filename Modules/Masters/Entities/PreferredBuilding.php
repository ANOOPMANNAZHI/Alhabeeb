<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class PreferredBuilding extends Model
{
    use Sortable;
	 
	 
	protected 	$guarded = [];
	protected 	$table = 'preferred_buildings';
    public 		$sortable = ['building_id','are_building_id'];
    /*
    * ARE Name
    *
    **/
    public function buildingAssignToName(){

       return $this->belongsTo('Modules\Masters\Entities\AreBuildingAssign','are_building_id');
    }
}
