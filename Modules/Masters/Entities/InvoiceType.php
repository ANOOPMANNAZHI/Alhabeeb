<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class InvoiceType extends Model
{
	use Sortable;
	protected $guarded = [];
	
	 public $sortable = ['id','invoice_types_name','invoice_types_status']; 
	 
	 
	 
  public function invoiceTypesStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('invoice_types_status', $direction);
		}
	 
     
}
