<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Reason extends Model
{
	 use Sortable;
	 
	protected $guarded = [];
	
	public $sortable = ['id','reasons_code','reasons_status'];
	
	
	
  	
    
    public function reasonsStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('reasons_status', $direction);
		}  
    
}
