<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Bank extends Model
{
	use Sortable;
		
	protected $guarded = [];
    
    protected $table = 'bank';
    
    public $sortable = ['id','bank_code','bank_name','bank_branch','bank_status'];
    
    
    public function bankStatusSortable($query, $direction)
	{
		$direction = ($direction == 'asc')? 'desc' : 'asc';
		return $query->orderBy('bank_status', $direction);
	} 
	public function scopeActive($query)
    {
        return $query->where('bank_status', 1);
    }
    
}
