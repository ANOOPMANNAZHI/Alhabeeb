<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Designation extends Model
{
	 use Sortable;
    protected $guarded = [];
   
    protected $table = 'designation';
    
    public $sortable = ['id','designation_code','designation_name','designation_status'];
    
    
    
    public function designationStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('designation_status', $direction);
		}
    
    
}
