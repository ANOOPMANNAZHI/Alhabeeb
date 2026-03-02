<?php

namespace Modules\General\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ProcessActionLink extends Model
{
    use Sortable;
    
    protected $guarded 	= [];
    protected $table 	= 'process_action_links';

    public function action_name(){

    return $this->hasOne(Action::class,'id','actions_id');
    }

    public function process_name(){

        return $this->hasOne(WorkFlowProcess::class,'work_flow_processes_code','work_flow_processes_code');
    }

   	public function process_name_next_stage(){

        return $this->hasOne(WorkFlowProcess::class,'work_flow_processes_code','next_processes_code');
    }
   
}
