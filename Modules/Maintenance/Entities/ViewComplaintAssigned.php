<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\User;
use Carbon\Carbon;

class ViewComplaintAssigned extends Model
{
    use Sortable;
    protected $guarded = [];
    protected $table = 'view_complaint_assign';
    public $sortable = ['complaint_ticket_no','complaint_date','complaint_mob_no','building_name','unit_code','works_code','employee_name','concat','ticket_status'];

     /*
    *
    * Complaint Enquiry
    */
    public function complaintEnquiry(){

      return $this->belongsTo('Modules\Maintenance\Entities\ComplaintEnquiry','complaint_enquiries_id');
    }
       /*
    *
    * Work 
    */
    public function work(){

      return $this->belongsTo('Modules\Masters\Entities\Work','work_id');
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
   *  Assigned To  for List 
   *
   **/
   public function scopeAssignedUsers($query){

      $user = \Auth::user();
      $rolesNames = $user->getRoleNames()->toArray();
    //  $query->when($user->hasRole(['maintenance_supervisor','maintenance_coordinator','super_admin']),function($query){

    //          $query->where('assigned_to_type',0)
    ////                ->where('assigned_to',\Auth::user()->id)
    //                ->orderBy('assigned_to','DESC');   
    //  });
      $query->when($user->hasRole(['maintenance_supervisor','maintenance_engineer','maintenance_coordinator']),function($query){
             
              $query->where('user_id',\Auth::user()->id);
                      
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
    *  Building
    */
    public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building');
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
   *  Filter 
   *
   */
   public function scopeFilter($query, $request){

      if(isset($request)){     

      $fieldNameFlag = false;  
      $generalSearch = array(); 
      
       
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','unit__unit_code','params','assigned_to','complaintTicketsAll__work__works_code','complaintTicketsAll__complaint_ticket_no','vip_status','hours','time','sub_open_days']) as $key => $val){         

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
/*
    *
    * VIP -  Tenant 
    */
    public function vipTenant(){

      return $this->belongsTo('Modules\Sales\Entities\Tenant','tenant_id','id')->where('status',1);
    }
    public function scopeVipOpenAssigned($query,$request){
  $complaint_status = $request->vip_status;
  if(isset($request->vip_status)){
    $query->where('complaint_status','!=',$complaint_status)->has('vipTenant');
    return $query;
  }
  return $query;
}
/*
*
*Assigned Complaints – Open – More than 24 hrs./48 hrs./All
*
*/
public function scopeAssignedComplaint($query,$request){ 
  $hours = $request->hours; 
  $userId = User::role(['maintenance_engineer'])->pluck('id'); 
  if(!empty($request->hours)&&($request->hours!='all')){
          $dt = Carbon::now(); // Produces something like "2020-01-27 16:02:47.063656 Asia/Muscat (+04:00)"
          $dt->subHour($hours);
          $query->where(function ($query)use($userId,$dt){
           $query->whereIn('assigned_to',$userId)->where('created_at','<=',$dt);
           return $query;
         });
        }elseif(!empty($request->hours)&&($request->hours=='all')){
         $query->where(function ($query)use($userId){
           $query->whereIn('assigned_to',$userId);
           return $query;
         });
       }
       else{
         $user = \Auth::user();
         $rolesNames = $user->getRoleNames()->toArray();
    //  $query->when($user->hasRole(['maintenance_supervisor','maintenance_coordinator','super_admin']),function($query){

    //          $query->where('assigned_to_type',0)
    ////                ->where('assigned_to',\Auth::user()->id)
    //                ->orderBy('assigned_to','DESC');   
    //  });
         $query->when($user->hasRole(['maintenance_supervisor','maintenance_engineer','maintenance_coordinator']),function($query){

          $query->where('user_id',\Auth::user()->id);
          return $query;        
        });

       }
       
       return $query;
     }
     /*
     *
     *All
     *
     */
     public function scopeAllAssignedComplaint($query,$request){
      $userId = User::role(['maintenance_engineer'])->pluck('id'); 
      $query->where(function ($query)use($userId){
       $query->whereIn('assigned_to',$userId);
       return $query;
     });
      return $query;
    }
    /*
*
*Assigned Tickets – Open – Excluding cases of sub-contractor – More than 24
hrs./48 hrs./All
*
*/
public function scopeAssignedComplaintSupervisor($query,$request){ 
  $time = $request->time; 
  if(!empty($request->time)&&($request->time!='all')){
          $dt = Carbon::now(); // Produces something like "2020-01-27 16:02:47.063656 Asia/Muscat (+04:00)"
          $dt->subHour($time);
          $query->where(function ($query)use($dt){
           $query->where('assigned_to_type',0)->where('created_at','<=',$dt);
           return $query;
         });
        }
        elseif(!empty($request->time)&&($request->time=='all')){
         $query->where(function ($query){
           $query->where('assigned_to_type',0);
           return $query;
         });
       }
       else{
        
       }
       return $query;
     }
     /*
     *
     *All
     *
     */
     public function scopeAllAssignedComplaintSupervisor($query,$request){
      $query->where(function ($query){
       $query->where('assigned_to_type',0);
       return $query;
     });
      return $query;
    }
  /*
*
*Ticket with Sub contractors – Open cases – more than 1 day/3 days/5 days/All -
*
*/
public function scopeAssignedComplaintSubcontractor($query,$request){ 
  $sub_open_days = $request->sub_open_days; 
  if(!empty($request->sub_open_days)&&($request->sub_open_days!='all')){
          $dt = Carbon::now(); // Produces something like "2020-01-27 16:02:47.063656 Asia/Muscat (+04:00)"
          $dt->subDays($sub_open_days);
          $query->where(function ($query)use($dt){
           $query->where('assigned_to_type',1)->where('created_at','<=',$dt);
           return $query;
         });
        }
        elseif(!empty($request->sub_open_days)&&($request->sub_open_days=='all')){
         $query->where(function ($query){
           $query->where('assigned_to_type',1);
           return $query;
         });
       }
       else{
        
       }
       return $query;
     }
     /*
     *
     *All
     *
     */
     public function scopeAllAssignedComplaintSubcontractor($query,$request){
      $query->where(function ($query){
       $query->where('assigned_to_type',1);
       return $query;
     });
      return $query;
    }
    /*
    *
    */

}
