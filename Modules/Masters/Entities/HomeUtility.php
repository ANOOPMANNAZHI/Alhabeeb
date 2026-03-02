<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class HomeUtility extends Model
{
	 use Sortable;
	protected $guarded = [];
	
	 public $sortable = ['id','home_utilities_code','home_utilities_make','home_utilities_status'];
    


    /*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('home_utilities_status', 1);
    }   
    
    
    
    public function homeUtilitiesStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('home_utilities_status', $direction);
		}
    
     	
  	public function getHomeUtility(){

      return $this->hasMany('Modules\Masters\Entities\UnitUtility');
  	}   
  		

}
