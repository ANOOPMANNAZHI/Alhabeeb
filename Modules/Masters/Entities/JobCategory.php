<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class JobCategory extends Model
{
	use Sortable;
    protected $guarded = [];
	protected $table = 'job_category';
	public $sortable = ['id','job_category_code','job_category_name','job_category_status'];
    
    
     
 public function jobCategoryStatusSortable($query, $direction)
	{
		$direction = ($direction == 'asc')? 'desc' : 'asc';
		return $query->orderBy('job_category_status', $direction);
	}   
}
