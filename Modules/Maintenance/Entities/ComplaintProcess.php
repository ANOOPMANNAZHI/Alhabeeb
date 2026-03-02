<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ComplaintProcess extends Model
{
	 use Sortable;
    protected $guarded = [];
    protected $table = 'complaint_processes';
    public $sortable = ['id','complaint_enquiries_id','complaint_checklists_id','work_flow_processes_code'];
    /*
    *
    * Complaint  Enquiry
    */
    public function complaintEnquiry(){

      return $this->belongsTo('Modules\Maintenance\Entities\ComplaintEnquiry','complaint_enquiries_id');
  	}
    /*
    *
    * Complaint  Enquiry
    */
    public function complaintChecklist(){

      return $this->belongsTo('Modules\Maintenance\Entities\ComplaintChecklist','complaint_checklists_id','id');
    }
    /*
    * 
    *  CreatedBy
    *
    */
    public function createdBy() {
        
           return $this->belongsTo('App\User','created_by');
    }
    /*
    * 
    *  CreatedBy
    *
    */
    public function updatedBy() {
        
           return $this->belongsTo('App\User','updated_by');
    }
    /*
    *
    * Work Flow Process
    */
    public function workFlowProcess(){

      return $this->belongsTo('Modules\General\Entities\WorkFlowProcess','work_flow_processes_code','work_flow_processes_code');
    }



    /*
    *  complaintUser
    */
    public function complaintUsers() {
           return $this->belongsToMany('\Spatie\Permission\Models\Role', 'complaint_users','complaint_processes_id','role_id');
    }
    /*
    * 
    *  complaintUser
    *
    */
    public function complaintUser() {
        
           return $this->hasMany('Modules\Maintenance\Entities\ComplaintUser','complaint_processes_id');
    }


    /*
    *  complaintUsers Scope  
    *
    **/
    public function scopeComplaintUsers($query){
 
        $user = \Auth::user();
        $roles = $user->getRoles();


        $query->whereHas('complaintUsers', function ($query) use($roles,$user) {

            $query->when( (!$user->hasRole(['super_admin','maintenance_coordinator'])), function ($query) use($roles) {
                   $query->where(function ($query) use($roles){
                        $query->where(function ($query) use($roles){
                                              $query->whereNull('user_id')
                                                    ->whereIn('role_id', $roles)
                                                    ->where('status','=',1);
                                  })
                              ->orWhere(function ($query) use($roles){
                                  $query->whereIn('role_id', $roles)
                                        ->where('user_id','=', \Auth::user()->id)
                                        ->where('status','=',1);
                                });
                            });
                           // ->where('status','=',1);
                            })->where('status','=',1);
                       }); 
       

        return $query;

    }



}
