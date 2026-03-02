<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class EnquirySource extends Model
{
	use Sortable;
	
	protected $guarded = [];
	
	public $sortable = ['id','enquiry_sources_name','enquiry_sources_status'];



	/*
	*  Status - Active
	*
	*/
	public function scopeActive($query)
	{
		return $query->where('enquiry_sources_status', 1);
	} 


  
	public function enquirySourcesStatusSortable($query, $direction)
	{
		$direction = ($direction == 'asc')? 'desc' : 'asc';
		return $query->orderBy('enquiry_sources_status', $direction);
	}
	public function salesEnquirySource()
	{
		return $this->hasOne('\Modules\Sales\Entities\UnitType\SalesEnquiry');
	}

   
}
