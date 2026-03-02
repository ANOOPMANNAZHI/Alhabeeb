<?php

namespace Modules\General\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class WorkFlowProcess extends Model
{
	use Sortable;
	
   
    protected $guarded = [];
    protected $table = 'work_flow_processes';
    
    
    public $sortable = ['id','work_flow_processes_name'];

   public function workflow(){

    		return $this->hasOne(WorkFlow::class,'id','work_flows_id');
    	
    }
}
