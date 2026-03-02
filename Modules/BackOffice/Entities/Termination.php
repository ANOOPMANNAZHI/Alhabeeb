<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Termination extends Model
{
    
    use Sortable;
    protected $guarded = [];
    protected $table = 'termination';
    public $sortable = ['id','contract_id'];
    protected $dates = ['termination_takenover_date','termination_date'];

    
    /*
    *  Termination User
    */
    public function terminationUsers() {
           return $this->belongsToMany('\Spatie\Permission\Models\Role', 'termination_users','termination_id','role_id');
    }
    /*
    *
    * Work Flow Process
    */
    public function workFlowProcess(){

      return $this->belongsTo('Modules\General\Entities\WorkFlowProcess');
    }
    /*
    *
    * Tenant Contract
    *
    */
    public function tenantContract(){

      return $this->belongsTo('Modules\Sales\Entities\TenantContract','contract_id','id');
    }
    /*
    *
    * Landlord Contract
    *
    */
    public function landlordContract(){

      return $this->belongsTo('Modules\Sales\Entities\LandlordContract','contract_id','id');
    }
    /*
    * 
    *  salesuser
    *
    */
    public function terminationUser() {
        
           return $this->hasMany('Modules\BackOffice\Entities\TerminationUser','termination_id');
    }
    /*
    * Status
    *
    */
    public function WorkFlowProcessesCode(){
       
        return $this->belongsTo('\Modules\General\Entities\WorkFlowProcess','work_flow_processes_code','work_flow_processes_code');
    }
    /*
     *  Termination Status Class
     */
    public function getWorkFlowProcessesCodeClassAttribute()
    {

        $code =  $this->work_flow_processes_code; 

        switch($code){

             case 501:
                       $class = 'label-danger';
                        break;
         
             case 502: $class = 'label-info';
                        break; 

             case 503:  $class = 'label-warning';
                        break; 

             case 504: 
                        $class = 'label-primary';
                        break; 

             case 505:  $class = 'label-primary';
                        break;         
                        
             case 506:  $class = 'label-primary';
                        break;
                        
             case 507:  $class = 'label-primary';
                        break; 

             case 508: 
                         $class = 'label-success';
                        break;                                      
             
             default : $class = 'label-danger';
                        break;
              

        }


        return $class;

        
    }
    /*
    * Status Name
    *
    */
    public function getTerminationTypeStatusNameAttribute()
    {     
        switch($this->termination_type_status){
          case '0' : return 'Normal';
          case '1' : return 'Premature';  
          case '2' : return 'Under Approval';
          case '3' : return 'Approved';
          case '4' : return 'Rejected';      
        }
    }
    /*
    * Resubmit
    *
    */
    public function getIsResubmitNameAttribute()
    {     
        switch($this->is_resubmit){
          
          case '1' : return 'No';  
          case '2' : return 'Yes';
    
        }
    }
    /*
    * Status
    *
    */
    public function getTerminationTypeStatusClassAttribute(){
       
        $code =  $this->termination_type_status; 
        switch($code){

             case 0:
               $class = 'label-primary';
                break;
             case 1: $class = 'label-info';
                       break;
             case 2: $class = 'label-general';
                       break; 
             case 3: $class = 'label-success';
                       break; 
             case 4: $class = 'label-danger';
                       break;                
             
             default : $class = 'label-danger';
                        break;
              

        }


        return $class;
    }
    /*
    *
    * Filter  
    *
    */
    public function scopeFilter($query, $request){

        if(isset($request->termination_date)){
            $query->where('termination_date','ilike','%'.$request->termination_date.'%'); 
        }

        return $query;

    }
    /*
    *
    *  assignedTo
    */
     public function assignedTo(){
      return $this->belongsTo('App\User','assigned_to','id');
    } 



    /*
    * 
    *  Termination Checklist
    *
    */
   /* public function terminationChecklist() {
        
           return $this->hasMany('Modules\BackOffice\Entities\TerminationChecklist','termination_id')->whereNotNull('work_id');
    }*/
    /*
    * 
    *  Termination Checklist
    *
    */
    /*public function terminationChecklistOther() {
        
           return $this->hasMany('Modules\BackOffice\Entities\TerminationChecklist','termination_id')->whereNull('work_id');
    }*/
}
