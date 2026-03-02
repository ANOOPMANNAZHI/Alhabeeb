<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class VendorType extends Model
{
	use Sortable;
	
	protected $guarded = [];
	
	public $sortable = ['id','vendor_types_name','vendor_types_status'];


	
/*
*  Status - Active
*
*/
public function scopeActive($query)
{
    return $query->where('vendor_types_status', 1);
} 



  
  public function vendorTypesStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('vendor_types_status', $direction);
		}

    
}
