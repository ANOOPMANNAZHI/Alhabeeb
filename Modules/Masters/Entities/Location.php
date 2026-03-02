<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Location extends Model
{
	
	use Sortable;
	
	protected $guarded = [];
	
	public $sortable = ['id','locations_code','locations_name','locations_status'];



/*
*  Status - Active
*
*/
public function scopeActive($query)
{
    return $query->where('locations_status', 1);
} 



/*
*  Enquiry
*/
public function enquiries() {

       return $this->belongsToMany('\Modules\Sales\Entities\SalesEnquiry', 'preferred_locations','location_id','sale_enquiry_id');
       
 }
 
 
 
 
    
public function locationsStatusSortable($query, $direction)
	{
		$direction = ($direction == 'asc')? 'desc' : 'asc';
		return $query->orderBy('locations_status', $direction);
	}   





}
   
