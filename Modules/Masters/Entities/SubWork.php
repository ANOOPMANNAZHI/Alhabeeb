<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class SubWork extends Model
{
    use Sortable;
	protected $guarded = [];
	protected $table = 'sub_work';
	public $sortable = ['id','works_id','sub_work','sub_work_status'];
    public $timestamps = false;
	/*
    *
    *  work
    */
    public function work(){

      return $this->belongsTo('Modules\Masters\Entities\Work','works_id','id');
    }
}
