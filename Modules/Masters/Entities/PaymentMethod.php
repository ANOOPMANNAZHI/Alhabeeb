<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class PaymentMethod extends Model
{
	use Sortable;
	protected $guarded = [];
     
    protected $table = 'payment_method';
    
     public $sortable = ['id','payment_method_code','payment_method_status']; 
     
     
      
  public function paymentMethodStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('payment_method_status', $direction);
		}
	 
    
     public function scopeActive($query)
    {
        return $query->where('payment_method_status', 1);
    }
}
