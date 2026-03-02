<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class PreferredLocation extends Model
{
	
	 use Sortable;
	 
	 
	protected 	$guarded = [];

    public 		$sortable = ['location_id','sale_enquiry_id'];

	

    /*
    *  Status - Active
    *
    */
    public function PreferredLocation($query)
    {
        return $this->hasOne('\Modules\Masters\Entities\Location', 'location_id');
	       
    }
    
    public function amentityTypesStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('amentity_types_status', $direction);
		}   

   
}
