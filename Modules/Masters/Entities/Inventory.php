<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Inventory extends Model
{
	use Sortable;
	
	protected $guarded = [];
	
	public $sortable = ['id','inventories_name','inventories_brand_name'];
	
	
	
   
     
}
