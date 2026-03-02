<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class PriceRange extends Model
{
	use Sortable;
	
	protected $guarded = [];
     
    public $sortable = ['id','price_ranges_name','price_ranges_from','price_ranges_to','price_ranges_status'];


/*
*  Status - Active
*
*/
public function scopeActive($query)
{
    return $query->where('price_ranges_status', 1);
} 




/*
*  Enquiry
*/
public function enquiries() {
       return $this->belongsToMany('\Modules\Sales\Entities\SalesEnquiry', 'preferred_price_ranges','price_range_id','sale_enquiry_id');
 }



  
  public function priceRangesStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('price_ranges_status', $direction);
		}


 
}
