<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TenantType extends Model
{
	use Sortable;
	
	protected $guarded = [];
	
	public $sortable = ['id','tenant_types_name','tenant_types_status'];
     

/*
*  Status - Active
*
*/
public function scopeActive($query)
{
    return $query->where('tenant_types_status', 1);
} 



    
     public function tenantTypesStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('tenant_types_status', $direction);
		}
		

}
