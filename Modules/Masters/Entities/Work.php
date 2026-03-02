<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Work extends Model
{
	 use Sortable;
	 
	protected $guarded = [];
	
	public $sortable = ['id','works_code','works_status','works_type'];
    

    /*
    *
    * vendor table/contractor
    */
    public function workLink(){

      return $this->hasOne('Modules\Masters\Entities\ContractorWorkLink','work_id');
  	}
  	
  	
  	
  	
    
    public function worksTypeSortable($query, $direction)
		{
			if($direction == 'asc')
			$orderedStatuses = '2,0,1';
			else
			$orderedStatuses = '1,0,2';
			
			return $query->orderByRaw('FIELD(works_type ,2)');
			 
		}   

  	
  	
    
    public function worksStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('works_status', $direction);
		}   
    /*
    *
    * sub Work
    */
    public function subWork(){

      return $this->hasMany('Modules\Masters\Entities\SubWork','works_id');
    }
    /*
    *
    * Tremination Checklist
    */
    public function TerminationWork(){

      return $this->hasMany('Modules\BackOffice\Entities\TerminationChecklist','work_id','id');
    }
  	
  	
  	  public function scopeActive($query)
    {
        return $query->where('works_status', 1);
    }
  	
}
