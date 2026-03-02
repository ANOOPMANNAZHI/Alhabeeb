<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class BuildingType extends Model
{
	use Sortable;
	
	protected $guarded = [];
	
	public $sortable = ['id','building_types','building_types_status'];
   

/*
*  Status - Active
*
*/
public function scopeActive($query)
{
    return $query->where('building_types_status', 1)->orderBy('building_types_name', 'asc');
} 



public function buildingTypesStatusSortable($query, $direction)
    {
		$direction = ($direction == 'asc')? 'desc' : 'asc';
        return $query->orderBy('building_types_status', $direction);
    }


}
