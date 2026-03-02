<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Currency extends Model
{
	use Sortable;
	
	protected $guarded = [];
	
   
    protected $table = 'currency';
    
    
    public $sortable = ['id','currency_code','currency_name','currency_status'];
    
    
     
 public function currencyStatusSortable($query, $direction)
	{
		$direction = ($direction == 'asc')? 'desc' : 'asc';
		return $query->orderBy('currency_status', $direction);
	}   

/*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('currency_status', 1);
    }

}
