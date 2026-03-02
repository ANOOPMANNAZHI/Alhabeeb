<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TerminationChecklist extends Model
{
   	protected $guarded = [];
	protected $table = 'termination_checklists';
	public $timestamps = false;
	/*
    *
    *  work
    */
    public function work(){

      return $this->belongsTo('Modules\Masters\Entities\Work','work_id','id');
    }
    /*
    *
    *  work
    */
    public function subWorks(){

      return $this->belongsTo('Modules\Masters\Entities\SubWork','sub_work_id','id');
    }
    
}
