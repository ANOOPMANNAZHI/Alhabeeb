<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Country extends Model
{
	use Sortable;
	protected $guarded = [];
	
	public $sortable = ['id','countries_code','countries_name','countries_status'];
	
	
	
    
 public function countriesStatusSortable($query, $direction)
	{
		$direction = ($direction == 'asc')? 'desc' : 'asc';
		return $query->orderBy('countries_status', $direction);
	}   

     
}
