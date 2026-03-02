<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Acc_codes extends Model
{
	use Sortable;
	 
	protected $guarded = [];

	protected $table = 'acc_codes';
	
	public $sortable = ['id','acc_code_val','acc_code_desc'];
}