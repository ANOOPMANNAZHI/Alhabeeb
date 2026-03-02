<?php

namespace Modules\General\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class WorkFlowCategory extends Model
{
	use Sortable;
	 
    protected $fillable = [];
    
    public $sortable = ['id','assign_field_name','assign_field_status'];

    /*
    *
    * Price range
    *
    */
    public function priceRange() {

    	return $this->belongsTo('Modules\Masters\Entities\PriceRange');
    }
    /*
    *
    * work flow process
    *
    */
    public function workFlowProcess() {

    	return $this->belongsTo('Modules\General\Entities\WorkFlowProcess');
    }
    /*
    *
    * Location
    *
    */
    public function location() {

        return $this->belongsTo('Modules\Masters\Entities\Location');
    }
}
