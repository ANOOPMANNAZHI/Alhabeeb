<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Region extends Model
{
    use \App\Concerns\LegacyDateSerialization;
	
	protected $guarded = [];
    protected $table = 'regions';
	

}
   
