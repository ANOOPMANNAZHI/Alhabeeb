<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class UnitType extends Model
{
	use Sortable;
	
	protected $guarded = [];
	
	public $sortable = ['id','unit_types_name','unit_types_status'];


/*
*  Status - Active
*
*/
public function scopeActive($query)
{
    return $query->where('unit_types_status', 1);
} 


/*
*  Enquiry
*/
public function enquiries() {
       return $this->belongsToMany('\Modules\Sales\Entities\SalesEnquiry', 'preferred_unit_types','unit_type_id','sale_enquiry_id');
 }
 
 
 

	public function buildingTypesStatusSortable($query, $direction)
    {
		$direction = ($direction == 'asc')? 'desc' : 'asc';
        return $query->orderBy('building_types_status', $direction);
    }
    
    
    
 

	public function unitTypesStatusSortable($query, $direction)
    {
		$direction = ($direction == 'asc')? 'desc' : 'asc';
        return $query->orderBy('unit_types_status', $direction);
    }   
	public function unitTypeCount(){

      return $this->hasMany('Modules\Masters\Entities\UnitTypeCount');
  	}  
  	
	
     
}
