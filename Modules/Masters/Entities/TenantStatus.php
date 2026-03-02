<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TenantStatus extends Model
{
	 use Sortable;
	protected $guarded = [];
	
	public $sortable = ['id','tenant_statuses_name','tenant_statuses_status'];
	
	
	
	public function TenantStatusesStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('tenant_statuses_status', $direction);
		}



   
}
