<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ManagementType extends Model
{
	use Sortable;
    protected $guarded = [];
    
    public $sortable = ['id','management_types_name','management_types_status'];

    
/*
*  Status - Active
*
*/
public function scopeActive($query)
{
    return $query->where('management_types_status', 1);
} 


  public function managementTypesStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('management_types_status', $direction);
		}
    
}
