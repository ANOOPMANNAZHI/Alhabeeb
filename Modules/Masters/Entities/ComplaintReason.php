<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ComplaintReason extends Model
{
	 use Sortable;
	 
	protected $guarded = [];
     
    protected $table = 'complaint_reason';
    
    public $sortable = ['id','complaint_reason_name','complaint_reason_status'];
	
	
	
	public function ComplaintReasonStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('complaint_reason_status', $direction);
		}

}
