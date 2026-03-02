<?php

namespace Modules\General\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ProcessAssign extends Model
{
	  use Sortable;
    
    protected $guarded 	= [];
    protected $table 	= 'process_assigns';
    
     

    public function work_process(){
    
        return $this->hasOne(WorkFlowProcess::class,'work_flow_processes_code','work_flow_processes_code');
    }

    public function location(){
        return $this->hasOne(\Modules\Masters\Entities\Location::class,'id','location_id');
    }
	
    public function price(){
        return $this->hasOne(\Modules\Masters\Entities\PriceRange::class,'id','price_range_id');
    }
    /*
    * 
    *  Process Assign Roles
    *
    */
    public function processAssignRoles() {
        
           return $this->belongsToMany('Spatie\Permission\Models\Role', 'process_assign_users','process_assigns_id','role_id')->distinct()->select('role_id','roles.name');
    }
    /*
    * 
    *  Process Assign Users
    *
    */
    public function processAssignUsers() {
        
           return $this->belongsToMany('\App\User', 'process_assign_users','process_assigns_id','user_id')->distinct()->select('user_id','username');
    }
    /*
    * 
    *  Process Assign Users and Role
    *
    */
    public function processAssignUserRole() {

       return $this->hasMany('Modules\General\Entities\ProcessAssignUser',
        'process_assigns_id');        
       //  return $this->belongsToMany('Modules\General\Entities\ProcessAssignUsers','process_assigns_id','id');
    }


    public function processAssignRes() {
        
           return $this->belongsToMany('Spatie\Permission\Models\Role', 'process_assign_users','process_assigns_id','role_id');
    }
    
    /*
    * 
    *  Assign
    *
    */
    public function assign() {
        
           return $this->hasMany('Modules\General\Entities\ProcessAssignUser','process_assigns_id');
    }

}
