<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;

class ViewComplaintReview extends Model
{
    use Sortable;
    protected $guarded = [];
    protected $table = 'view_complaint_review';
    public $sortable = ['service_report_no','complaint_date', 'complaint_no', 'complainer_name', 'complaint_mob_no', 'building_name','unit_code','locations_name','service_report_no','complaint_assign_status'];
    protected $dates = ['complaint_date'];

     /*
    *
    * Complaint Enquiry
    */
    public function complaintEnquiry(){

      return $this->belongsTo('Modules\Maintenance\Entities\ComplaintEnquiry','complaint_enquiries_id');
    }
    /*
    *  complaintUser
    */
    public function complaintUsers() {
           return $this->belongsToMany('\Spatie\Permission\Models\Role', 'complaint_users','complaint_processes_id','role_id');
    }
     /*
    *  complaintUsers Scope  
    *
    **/
    public function scopeComplaintUsers($query){
 
        $user = \Auth::user();
        $roles = $user->getRoles();
        if($user->hasRole(['super_admin','maintenance_coordinator'])){

          return $query;
        }

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
                    
       

        return $query;

    }
    /*
    * Service Report Status Name
    *
    */
    public function getServiceReportStatusNameAttribute()
    {     
        switch($this->complaint_assign_status){
          case '0' : return 'Open';  
          case '1' : return 'In Progress';
          case '2' : return 'Attended';  
          case '3' : return 'Completed';
          case '4' : return 'Closed';
          case '5' : return 'Send To Landlord'; 
          case '6' : return 'Rejected';    
        }
    }
    /*
    *  Enquiry Status Class
    */
    public function getServiceReportStatusClassAttribute()
    {

        $code =  $this->complaint_assign_status; 

        switch($code){

          case 2:
           $class = 'label-danger';
            break;

          case 5:           
            $class = 'label-info';
            break;
          case 6:           
            $class = 'label-warning';
            break; 
         default : 
            $class = 'label-warning';
            break;
              

        }


        return $class;

        
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
                  $query->where('concat','ilike','%'.$assigned_to.'%');
                        
             });
                     
    }
       return $query;

    }
    /*
    *
    * Complaint Enquiry
    */
    public function complaintTicketsAll(){

      return $this->hasMany('Modules\Maintenance\Entities\ComplaintChecklist','complaint_enquiries_id','id');
    }

    /*
    *  Category To Filter 
    *
    **/
     /*
    *
    * Sales Enquiry
    */
    public function complaintTickets(){

      return $this->hasMany('Modules\Maintenance\Entities\ComplaintChecklist','complaint_enquiries_id','id')->where('ticket_status','=', 0);
    }
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
      if(isset($request->ticket_status)){
        $ticket_status = $request->ticket_status;
        $query->when($ticket_status, function ($query) use($ticket_status){
          return $query->where('ticket_status','ilike', '%'.$ticket_status.'%');
          });
      }
      return $query;

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
    *  Sub Assigned Person
    *
    */
    public function subAssignedPerson() {
            
           return $this->belongsTo('App\User','sub_assigned_to');
    }
     /*
    * 
    *  Sub Assigned Person
    *
    */
    public function scopeAdditionalColum($query,$request) {
            
        if(isset($request)){     
          foreach($request->only(['unit__unit_code','building__building_name']) as $key => $val){    

              if($key==='unit__unit_code')
               $query->where('unit_code','ilike','%'.$val.'%'); 
              else if($key==='building__building_name')
               $query->where('building_name','ilike','%'.$val.'%'); 
          }
        }
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

       
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','building__building_name','unit__unit_code','params','assigned_to','complaintTicketsAll__work__works_code','complaintTicketsAll__complaint_ticket_no','review_days']) as $key => $val){         
   
           if($key == 'fieldValue' || $key == 'fieldValues' )
              continue;

           if($key == 'fieldName' && $fieldNameFlag == true) 
             continue;

             if($val != ''){  
                     
              if($key == 'fieldName'){

                  if(is_array($request->fieldName)){
                    $fieldNameFlag = true;                 


                         $i = 0; 
                         foreach ($request->fieldName as $keyName => $valueName) {



                               if( !empty($request->fieldValue[$keyName]) && !empty($request->fieldValue[$keyName]) && !empty($valueName) ) {
                                 
                                  $curr_logic =  ($i == 0)? 'and' : $next_logic;
                                  $next_logic =  $request->logic[$keyName]; $i++;

                                 
                                  if($request->operation[$keyName] == 'ilike%...%' ){
                                    $fieldValue = '%'.$request->fieldValue[$keyName].'%';
                                    $operation = 'ilike';
                                  }else{
                                  $operation = $request->operation[$keyName];
                                  $fieldValue = $request->fieldValue[$keyName];
                                  }
                                 

                                  $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic); 

                               }

                              }

                                                   
                            $generalSearch = collect($generalSearch)->groupBy('field');

                           
                  }else{ 
                  $key = $val;                  
                  $val = ($request->fieldName != 'tenant_contract_status') ? $request->fieldValue : $request->fieldValues;
                }
               } 

              if($key == 'fieldName' && is_array($request->fieldName)){ 

                    foreach($generalSearch  as $searchKey => $searchVal){
                    
                       if(strpos($searchVal[0]['field'], '__') !== false) {

                            $method = explode('__',$searchVal[0]['field']);

                            if(count($method) > 2)
                            {
                              $searchMethod = $method[0].'.'. $method[1];
                              $searchfor = $method[2];
                            }else{
                              $searchMethod = $method[0];
                              $searchfor = $method[1];
                            }

                            $query->whereHas($searchMethod, function ($query) use($searchMethod,$searchVal,$searchfor){

                                 foreach($searchVal as $subSearchVal ){ 

                                  if($subSearchVal['logic'] == 'and' ){

                                    if($subSearchVal['operation'] ==  'ilike%...%')
                                      $query->where($searchfor,'ilike','%'.$subSearchVal['fieldValue'].'%');
                                    else{

                                      $subSearchVal['fieldValue']=($searchfor == 'complaint_mob_no')?'00968'.$subSearchVal['fieldValue']:$subSearchVal['fieldValue'];

                                      $query->where($searchfor,'=',$subSearchVal['fieldValue']); 
                                    }

                                  }else{

                                    if($subSearchVal['operation'] ==  'ilike%...%')
                                      $query->orWhere($searchfor,'ilike','%'.$subSearchVal['fieldValue'].'%');
                                    else{
                                      $subSearchVal['fieldValue']=($searchfor == 'complaint_mob_no')?'00968'.$subSearchVal['fieldValue']:$subSearchVal['fieldValue'];
                                      $query->orWhere($searchfor,$subSearchVal['operation'],$subSearchVal['fieldValue']);
                                      }
                                    }
                                  }                             

                                                                  
                            });

                        }else{

                           foreach($searchVal as $subSearchVal ){
                              if($subSearchVal['logic'] == 'and' ){

                                if($subSearchVal['operation'] ==  'ilike%...%')
                                  $query->where($subSearchVal['field'],'ilike','%'.$subSearchVal['fieldValue'].'%');
                                else
                                  $query->where($subSearchVal['field'],$subSearchVal['operation'],$subSearchVal['fieldValue']); 

                              }else{
                                if($subSearchVal['operation'] ==  'ilike%...%')
                                  $query->orWhere($subSearchVal['field'],'ilike','%'.$subSearchVal['fieldValue'].'%');
                                else
                                  $query->orWhere($subSearchVal['field'],$subSearchVal['operation'],$subSearchVal['fieldValue']);
                              }                             

                           }
                            
                        } 

                    }                
               }else{

              
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

             
        }
          
      }
      return $query;

   }

 public function scopeLandlordApprovalPending($query,$request){
  $complaint_assign_status = $request->complaint_assign_status;
  if(isset($request->complaint_assign_status)){
    $query->where('complaint_assign_status',$complaint_assign_status)->assignedEngineer();
    return $query;
  }
  return $query;
}


// waiting for Landlord Approval
public function scopeLandlordReview($query,$request){ 
  $review_days = $request->review_days; 
  if(!empty($request->review_days)&&($request->review_days!='all')){
          $dt = Carbon::now(); // Produces something like "2020-01-27 16:02:47.063656 Asia/Muscat (+04:00)"
          $dt->subDays($review_days);
          $query->where(function ($query)use($dt){
           $query->where('landlord_approval_date','<=',$dt);
           return $query;
         });
        }
        elseif(!empty($request->review_days)&&($request->review_days=='all')){
         $query->where(function ($query){
           $query->where('landlord_approval_date','!=',null);
           return $query;
         });
       }
       else{
       }
       return $query;
     }
	 /*
*Assigned Engineer
*/
  public function scopeAssignedEngineer($query){

    $user = \Auth::user();
    $query->when($user->hasRole(['maintenance_engineer']),function($query){
              $query->where('assigned_to_type',0)
                    ->where('assigned_to',\Auth::user()->id)
                    ->orderBy('assigned_to','DESC');  
                        });
     return $query;

   }
   /*
*Assigned Engineer
*/

}
