<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ComplaintChecklist extends Model
{
    use Sortable;


    protected $guarded = [];

    protected $table = 'complaint_checklists';

    /*
    *
    * Complaint Enquiry
    */
    public function complaintEnquiry(){

      return $this->belongsTo('Modules\Maintenance\Entities\ComplaintEnquiry','complaint_enquiries_id');
    }
    
    /*
    *
    * Complaint Process
    */
    public function complaintProcessAll(){

      return $this->hasMany('Modules\Maintenance\Entities\ComplaintProcess','complaint_checklists_id','id');
    }
    /*
    *
    * Complaint Service Report
    */
    public function complaintServiceReport(){

      return $this->hasOne('Modules\Maintenance\Entities\ComplaintServiceReportChecklist','checklist_id','id');
    }
    /*
    *  Ticket Status
    *
    */
    public function scopeStatus($query){

      return $query->where('ticket_status',0);
    }
    /*
    *
    * Work 
    */
    public function work(){

      return $this->belongsTo('Modules\Masters\Entities\Work','work_id');
    }
    /*
    * Status
    *
    */
    public function flowStatus(){
       
        return $this->belongsTo('\Modules\General\Entities\WorkFlowProcess','work_flow_processes_code','work_flow_processes_code');
    }
    /*
     *  Enquiry Status Class
     */
    public function getProcessFlowClassAttribute()
    {

    	$code =  $this->work_flow_processes_code; 

    	switch($code){

    		 case 701:
    		 case 201:
    		           $class = 'label-danger';
    		            break;

    		 case 102:    		 
    		 case 202: $class = 'label-info';
    		            break; 
             case 103: 
             case 203:  $class = 'label-warning';
    		            break; 

    		 case 104: 
    		            $class = 'label-primary';
    		            break; 

    		 case 105:  $class = 'label-primary';
    		            break;         
    		            
    		 case 106:  $class = 'label-primary';
    		            break;
    		            
    		 case 107:  $class = 'label-primary';
    		            break; 

    		 case 108: 
    		 case 204:  $class = 'label-success';
    		            break;                                      
             
             default : $class = 'label-danger';
    		            break;
    		  

    	}


    	return $class;

    	
    }
    /*
    * Checklist Status Name
    *
    */
    public function getTicketStatusNameAttribute()
    {     
        switch($this->ticket_status){
          case '0' : return 'Open';  
          case '1' : return 'In Progress';
          case '2' : return 'Attended';  
          case '3' : return 'Completed';
          case '4' : return 'Closed';
          //case '5' : return 'Open';   //Generated Service Report    
        }
    } 
    
    /*
     *  Ticket Status
     */
    public function getTicketStatusClassAttribute()
    {

        $code =  $this->ticket_status; 

        switch($code){
             
            case 0:           
                $class = 'label-info';
                break; 
            case 1: 
                $class = 'label-warning';
                break;
            case 2:
               $class = 'label-success';
                break;
            case 3:
               $class = 'label-primary';
                break;
            case 4:
               $class = 'label-danger';
                break;
 
             default : $class = 'label-info';
                        break;
              

        }


        return $class;

        
    }
    /*
    * 
    *  Assigned Person
    *
    */
    public function assignedPerson() {
            
           return $this->belongsTo('App\User','assigned_to');
    }
    /*
    * 
    *  Assigned Person
    *
    */
    public function assignedContractor() {
            
           return $this->belongsTo('Modules\Masters\Entities\Vendor','assigned_to');
    }
    /*
    * 
    *  Sub Assigned Person
    *
    */
    public function subAssignedPerson() {
            
           return $this->belongsTo('App\User','sub_assigned_to');
    }

   /*
   *  Assigned To  for List 
   *
   **/
   public function scopeAssignedUsers($query){

    $user = \Auth::user();
    $query->when((!$user->hasRole('maintenance_coordinator') && $user->hasRole(['maintenance_supervisor','maintenance_engineer'])),function($query){
              $query->where('assigned_to_type',0)
                    ->where('assigned_to',\Auth::user()->id)
                    ->orderBy('assigned_to','DESC');   
                        });
     return $query;

   }

    /*
    *  Assigned To Filter 
    *
    **/
 public function scopeAssignedTo($query,$request){
     
   if(isset($request->assigned_to)){
   
    $assigned_to = $request->assigned_to;
      $query->whereNotNull('assigned_to')
            ->where(function ($query)use($assigned_to) {
                    $query->where('assigned_to_type',0)
                          ->whereHas('assignedPerson.employee', function($query) use($assigned_to){
                             $query->where('employee_name','ilike','%'.$assigned_to.'%');
                          });
             })
            ->orWhere(function ($query)use($assigned_to) {
                    $query->where('assigned_to_type',1)
                          ->whereHas('assignedContractor', function($query) use($assigned_to){
                             $query->where('vendor_name','ilike','%'.$assigned_to.'%');
                          });
              });
            
                     
    }

       return $query;

    }
	 /*
    *  Category To Filter 
    *
    **/
	public function scopeCategory($query,$request){
		 
		 if(isset($request->complaintTicketsAll__work__works_code)){
		 
		  $category = $request->complaintTicketsAll__work__works_code;

		  $query->whereHas('work', function ($query) use($category){
								  return $query->where('works_code','ilike', '%'.$category.'%');
							  });
		  }
      if(isset($request->complaintTicketsAll__complaint_ticket_no)){
        $complaint_ticket_no = $request->complaintTicketsAll__complaint_ticket_no;
        $query->when($complaint_ticket_no, function ($query) use($complaint_ticket_no){
          return $query->where('complaint_ticket_no','ilike', '%'.$complaint_ticket_no.'%');
          });
      }
		   return $query;

	}


   /*
   *
   *  Filter 
   *
   */
   public function scopeFilter($query, $request){

      if(isset($request)){     

      $fieldNameFlag = false;  
      $generalSearch = array(); 
      
    
        foreach($request->only(['subAssignedPerson__employee__employee_name','assignedPerson__employee__employee_name']) as $key => $val){         

           if($key == 'fieldValue' || $key == 'fieldValues' )
              continue;

           if($key == 'fieldName' && $fieldNameFlag == true) 
             continue;

             if($val == '')   
             continue;         

              

                 if(strpos($key, '__') !== false) {

                    $method = explode('__',$key);

                    $query->whereHas($method[0], function ($query) use($method,$val){
                       if(count($method)>2){
                         $query->whereHas($method[1], function ($query) use($method,$val){
                           return $query->where($method[2],'ilike', '%'.$val.'%');
                         });
                       }else
                        return $query->where($method[1],'ilike', '%'.$val.'%');
                    });                   

                }else{

                   if(strpos($key, 'status') !== false) 
                     $query->where($key,$val);
                    else
                    $query->where($key,'ilike','%'.$val.'%');                    
                    
                }                                
               
             
        }
          
      }
      return $query;

   }


}
 
