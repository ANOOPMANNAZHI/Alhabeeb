<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class AmentityType extends Model
{
	
	 use Sortable;
	 
	 
	protected $guarded = [];

    public $sortable = ['id','amentity_types_name','amentity_types_status'];

	

    /*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('amentity_types_status', 1);
    }
    
    
    
    public function amentityTypesStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('amentity_types_status', $direction);
		}   

   
}
