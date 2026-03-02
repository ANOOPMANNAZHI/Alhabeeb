<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ContractorWorkLink extends Model
{
	use Sortable;
	
	protected $guarded = [];
	
	public $sortable = ['id','contractor_work_status'];
     
    /*
    *
    * vendor table/contractor
    */
    public function vendor(){

      return $this->belongsTo('Modules\Masters\Entities\Vendor');
  	}
  	/*
    *
    * Work table
    */
    public function work(){

      return $this->belongsTo('Modules\Masters\Entities\Work');
  	} 
  	
  	
  	  
    public function contractorWorkStatusSortable($query, $direction)
	{
		$direction = ($direction == 'asc')? 'desc' : 'asc';
		return $query->orderBy('contractor_work_status', $direction);
	} 
	
	

}
