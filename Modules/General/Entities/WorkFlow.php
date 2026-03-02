<?php

namespace Modules\General\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class WorkFlow extends Model
{
	
	use Sortable;
   
    protected $guarded 	= [];
    protected $table 	= 'work_flows';
    
    
    public $sortable = ['id','work_flows_name','work_flows_status'];
    
    
    

    /*
    * Default Role
    *
    */
    public function defaultRole(){

    	return $this->belongsTo('Spatie\Permission\Models\Role','default_role');
    } 



    /*
    * Default User
    *
    **/
    public function defaultUser(){

        return $this->belongsTo('App\User','default_user_id')->withDefault();

    }


    /*
    * WorkFlow Process
    *
    ***/
     public function workFlowProcess(){

        return $this->hasMany('Modules\General\Entities\WorkFlowProcess','work_flows_id');

    }


}
