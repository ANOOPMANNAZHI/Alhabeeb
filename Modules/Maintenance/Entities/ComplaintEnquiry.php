<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;

class ComplaintEnquiry extends Model
{
  use Sortable;
  protected $guarded = [];
  protected $table = 'complaint_enquiries';
  protected $dates = ['complaint_date'];
  public $sortable = ['id','complaint_no','complainer_name','complaint_mob_no','building_id','location_id',
  'unit_id','created_at','complaint_status'];

  /** facilityManager   Scope ****/
    public function scopeFacilityManager($query){

      $user = \Auth::user();

      if($user->hasRole('facility_manager')){

       $query->where('complaint_status','!=',2)
             ->whereDate('created_at', '<', Carbon::now()->subDays(2)->toDateString())
             ->has('vipTenant'); 
       }
             return $query;

    }

    /*
    *
    * Sales Enquiry
    */
    public function complaintTickets(){

      return $this->hasMany('Modules\Maintenance\Entities\ComplaintChecklist','complaint_enquiries_id','id')->where('ticket_status','=', 0);
    }
    public function complaintTicketsStatus(){

      return $this->hasMany('Modules\Maintenance\Entities\ComplaintChecklist','complaint_enquiries_id','id')->where('ticket_status','=', 4);
    }
    /*
    *
    * Complaint Enquiry
    */
    public function complaintTicketsAll(){

      return $this->hasMany('Modules\Maintenance\Entities\ComplaintChecklist','complaint_enquiries_id','id');
    }
    /*
    *
    * Complaint Process
    */
    public function ComplaintProcess(){

      return $this->hasMany('Modules\Maintenance\Entities\ComplaintProcess','complaint_enquiries_id','id');
    }
    /*
    *
    * Complaint Process
    */
    public function ComplaintProcessNote(){

      return $this->hasMany('Modules\Maintenance\Entities\ComplaintProcess','complaint_enquiries_id','id')->where('complaint_processes_note','<>','');
    }
    /*
    *
    * Work Flow Process
    */
    public function workFlowProcess(){

      return $this->belongsTo('Modules\General\Entities\WorkFlowProcess','work_flow_processes_code','work_flow_processes_code');
    }
    /*
    *
    *  Building
    */
    public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building');
    }
    /*
    *
    * Unit
    */
    public function Unit(){

      return $this->belongsTo('Modules\Masters\Entities\Unit');
    }
    /*
	*  Location
	*/
	public function location() {

    return $this->belongsTo('\Modules\Masters\Entities\Location','location_id','id');

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
    * Tenant 
    */
    public function occupant(){

      return $this->belongsTo('Modules\Masters\Entities\Occupant','occupant_id');
    }
    /*
    *
    * Tenant 
    */
    public function tenant(){

      return $this->belongsTo('Modules\Sales\Entities\Tenant');
    }


  /*
  *  PreferredTime
  */
  public function preferredTime() {

    return $this->belongsToMany('\Modules\Maintenance\Entities\PreferredTime','complaint_preferred_time','complaint_enquiry_id','preferred_time_id');

  }


  /*
  *
  *
  */
  public function getCategoryNameAttribute(){
   switch($this->complainer_category){

    case 1 : return 'Occupied';
    case 2 : return 'Vacant';
    case 3 : return 'Others';
  }
}

    /*
    * Tenant Status Name
    *
    */
    public function getTenantStatusNameAttribute()
    {     
      switch($this->tenant_status){

        case '6' : return 'Vip';
        case '5' : return 'Legal';
        case '4' : return 'Blacklisted';
        case '3' : return 'No Maintenance';
        case '2' : return 'On Hold';
        case '1' : return 'Maintained By Landlord';
        case '0' : return 'Normal';        
      }
    }
    /*
    *  Enquiry Status Class
    */
    public function getTenantStatusClassAttribute()
    {

      $code =  $this->tenant_status; 

      switch($code){
        case 5:
        $class = 'label-warning';
        break;
        case 4:
        $class = 'label-danger';
        break;
        case 3:
        $class = 'label-success';
        break;
        case 2:
        $class = 'label-info';
        break;

        case 0:           
        $class = 'label-info';
        break; 
        case 1: 
        $class = 'label-danger';
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
    public function getComplaintStatusNameAttribute()
    {     
      switch($this->complaint_status){
        case '2' : return 'Closed';
        case '1' : return 'Partialy Closed';
        case '0' : return 'Open';        
      }
    }
    /*
    * Status Name
    *
    */
    public function getPriorityStatusNameAttribute()
    {    
      switch($this->priority_status){          
        case '1' : return 'High';
        case '0' : return 'Normal';        
      }
    }
    /*
    *  Enquiry Status Class
    */
    public function getPriorityStatusClassAttribute()
    {

      $code =  $this->priority_status; 

      switch($code){            

       case 0:           
       $class = 'label-info';
       break; 
       case 1: 
       $class = 'label-danger';
       break; 
       default : $class = 'label-warning';
       break;


     }


     return $class;


    }/*
    *  Enquiry Status Class
    */
    public function getComplaintStatusClassAttribute()
    {

      $code =  $this->complaint_status; 

      switch($code){

       case 2:
       $class = 'label-success';
       break;

       case 0:           
       $class = 'label-info';
       break; 
       case 1: 
       $class = 'label-warning';
       break; 
       default : $class = 'label-danger';
       break;


     }


     return $class;


   }

    /*
    * Where Query 
    *
    */
    public function scopeWhereClosure($query, $closure){

     return $query->where($closure);
   }

    /*
     * OrWhere Query 
     */
    public function scopeOrWhereClosure($query, $closure){

     return $query->orWhere($closure);
   }
    /*
    * OrWhere Query 
    *
    *
    *
    */
    public function scopeClosure($query, $result = array()){


      $closure =  $closure_or =  $locationq =  $location_or =  $unitq = $unit_or = array();

        // $customer_name, $email , $phone  , $status
      $quick_search_flag = false;
      $qiuck_search  = array();

      if(count($result) > 0){
        list($closure, $closure_or,$locationq,$location_or,$unitq,$unit_or,$qiuck_search) =  $result;
        
        if(count($qiuck_search) > 0)
          $quick_search_flag = true; 
      }

       //dd($closure) ; exit;

      $query->when($closure, function ($query) use($closure){
        return $query->where($closure);
      })
      ->when($closure_or, function ($query) use($closure_or){
        return $query->orwhere($closure_or);
      })  
      ->when($locationq, function ($query) use($locationq){               
        $query->whereHas('location', function ($query) use($locationq){
         foreach($locationq as $location_date_val){                             
          $query->where('locations_name', $location_date_val[1],$location_date_val[2]);                 
        } 
        return $query;   
      });             
        return $query;                                       
      }) 
      ->when($location_or , function ($query) use($location_or){                
        $query->orwhereHas('location', function ($query) use($location_or){
         foreach($location_or as $location_or_val){                           
          $query->where('locations_name', $location_or_val[1],$location_or_val[2]);
        }   
      });             
        return $query;                                       
      })
      ->when($unitq, function ($query) use($unitq){                  
        $query->whereHas('Unit', function ($query) use($unitq){
         foreach($unitq as $unit_val){                             
          $query->where('unit_code', $unit_val[1],$unit_val[2]);
        }   
      });             
        return $query;                                       
      }) 
      ->when($unit_or , function ($query) use($unit_or){                
        $query->orwhereHas('Unit', function ($query) use($unit_or){
         foreach($unit_or as $unit_or_val){                           
          $query->where('unit_code', $unit_or_val[1],$unit_or_val[2]);
        }   
      });             
        return $query;                                       
      })
      ->when($quick_search_flag , function ($query) use($qiuck_search){  

       list($ticket_no,$complaint_no,$complainer_name , $complaint_mob_no, $building_id, $complaint_date, $unit, $location,$status,$category,$tenant_status) =   $qiuck_search;

       $query->when($ticket_no, function ($query, $ticket_no) {
        $query->whereHas('complaintTicketsAll', function ($query) use($ticket_no){
          return $query->where('complaint_ticket_no','ilike', '%'.$ticket_no.'%');
        });
        return $query; 
      })     
       ->when($complaint_no, function ($query, $complaint_no) {
        return $query->where('complaint_no', 'ilike',  '%'.$complaint_no.'%');
      })
       ->when($complainer_name, function ($query, $complainer_name) {
        return $query->where('complainer_name','ilike', '%'.$complainer_name.'%');
      })

       ->when($complaint_mob_no, function ($query, $complaint_mob_no) {
        return $query->where('complaint_mob_no', 'ilike',  '%'.$complaint_mob_no.'%');
      })  
       ->when($building_id , function ($query) use($building_id){
        $query->whereHas('building', function ($query) use($building_id){
          return $query->where('building_name','ilike', '%'.$building_id.'%');
        });
        return $query;                     
      })

       ->when($complaint_date, function ($query) use($complaint_date){                                
         return $query->whereDate('complaint_date','=', $complaint_date);                            
       })

       ->when($unit , function ($query) use($unit){
        $query->whereHas('Unit', function ($query) use($unit){
          return $query->where('unit_code','ilike', '%'.$unit.'%');
        });
        return $query;                     
      })
       ->when($location , function ($query) use($location){
        $query->whereHas('location', function ($query) use($location){
          return $query->where('locations_name','ilike', '%'.$location.'%');
        });
        return $query;                     
      })
       ->when(($status != ''), function ($query) use($status){                                
         return $query->where('complaint_status','=', $status);                            
       })
       ->when(($tenant_status != ''), function ($query) use($tenant_status){                                
         return $query->where('tenant_status','=', $tenant_status);                            
       })
       ->when($category , function ($query) use($category){
        $query->whereHas('complaintTicketsAll', function ($query) use($category){
         return $query->whereHas('work', function ($query) use($category){
          return $query->where('works_code','ilike', '%'.$category.'%');
        });
       });
        return $query;                     
      });



     });                         

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
      

      foreach($request->except(['_token','list_by_week','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','assigned_to','subAssignedPerson__employee__employee_name','assignedPerson__employee__employee_name','params','from','to','vip_status','maintained_status','days','close','list_by_week','not_closeed']) as $key => $val){         

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
            else
              $query->where($searchfor,$subSearchVal['operation'],$subSearchVal['fieldValue']); 

          }else{
            if($subSearchVal['operation'] ==  'ilike%...%')
              $query->orWhere($searchfor,'ilike','%'.$subSearchVal['fieldValue'].'%');
            else
              $query->orWhere($searchfor,$subSearchVal['operation'],$subSearchVal['fieldValue']);
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
* Scope week
*
*
*/
public function scopeWeek($query,$request){
  
  if(isset($request->list_by_week)){
    $query->whereDate('created_at', '>=', \Carbon\Carbon::now()->subDays(7)->toDateString());
    return $query;
  }
  return $query;
}
  /*
* Scope VIP – Open Complaints ,ie except closed Status
*
*
*/
public function scopeVipOpen($query,$request){
  $complaint_status = $request->vip_status;
  if(isset($request->vip_status)){
    $query->where('complaint_status','!=',$complaint_status)->has('vipTenant');
    return $query;
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
    /*
    *
    *  Maintained by Landlord Building
    */
    public function landlordBuilding(){

      return $this->belongsTo('Modules\Masters\Entities\Building','building_id','id')->where('building_maintenance_info',1);
    } 
    /*
* Scope Maintained by Landlord – Open Complaints ,ie except closed Status
*
*
*/
public function scopeMaintainedLandlord($query,$request){
  $complaint_status = $request->maintained_status;
  if(isset($request->maintained_status)){
    $query->where('complaint_status','!=',$complaint_status)->has('landlordBuilding');
    return $query;
  }
  return $query;
}  
/*
* Scope Maintenance pending
*
*
*/
public function scopeMaintenancePending($query,$request){
  $oneWeek = $request->days;
  $nextExpDays = Carbon::today()->subDays($oneWeek);
  if(isset($request->days)){
    $query->whereDate('created_at','<=',$nextExpDays)->where('complaint_status','!=',2);
    return $query;
  }
  return $query;
}
/*
* Scope complaints not closed
*
*
*/
public function scopeNotClosed($query,$request){
  $complaintStatus = $request->close;
  if(isset($request->close)){
    $query->where('complaint_status','!=',$complaintStatus);
    return $query;
  }
  return $query;
}
/*
*
*
*/
public function scopeMaintenanceFilter($query){

  $user = \Auth::user();
  $role_count = count($user->roles);
  $headUser = \Auth::user()->id;

  $oneWeek = 7;
  $nextExpDays = Carbon::today()->subDays($oneWeek);

  if($role_count == 1 && $user->hasRole('are')){        
   $query->where(function ($query)use($nextExpDays){
   $query->whereDate('created_at','<=',$nextExpDays)->where('complaint_status','!=',2);
     return $query;
   });  
 }
 }
  /*
* Scope complaints not closed For CEO   
*
* Enquiries pending beyond 7 days
*/
public function scopeNotClosedForCeo($query,$request){
 
  if(isset($request->not_closeed)){
   $query->where('complaint_status','!=',2)
         ->whereDate('created_at', '<=', Carbon::now()->subDays(7)->toDateString());
  }
  return $query;
}
 /*
  *  Scope are
  *
  */
   public function scopeAreFilter($query){

    $user = \Auth::user();
    $role_count = count($user->roles);
    $headUser = \Auth::user()->id;

    if($role_count == 1 && $user->hasRole('are')){ 
      $query->whereHas('building', function ($query){       
        $query->whereHas('areBuildings', function ($query){
          $query->where('user_id','=', \Auth::user()->id);
          return $query;
        });   
        return $query;   
      }); 
    }
    elseif($role_count == 1 && $user->hasRole('are_team_lead')){
      $query->whereHas('building', function ($query)use($headUser){
        $query->whereHas('areBuildings', function ($query)use($headUser){
          $query->whereHas('user', function ($query)use($headUser){
            $query->whereHas('employee', function ($query)use($headUser){
              $query->where('head_user','=', $headUser);
            });
          });
          $query->orWhere('user_id','=', \Auth::user()->id);
          return $query;
        });
      });

    }

    return $query;
   }

}
