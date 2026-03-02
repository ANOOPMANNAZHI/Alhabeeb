<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;
use DB;
class ViewTenantContract extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table   = 'view_tenant_contract';
	public $sortable   = ['id','tenant_contract_no', 'tenant_contract_start_date', 'tenant_contract_valid_to_date','tenant_contract_rent','email_count', 'tenant_contract_status','tenant_renewal_termination_status','tenant_contract_is_reg_municipality','pdc_check','invoice_check','tenant_name','building_name','unit_code'];
	protected $dates   = ['tenant_contract_valid_to_date','tenant_contract_start_date','tenant_contract_last_paid_date'];
	
	/*
  *  Scope are For Dashboard
  *
  */
  public function scopeAreDashboardFilter($query, $request){

    $user = \Auth::user();
    $role_count = count($user->roles);
    $headUser = \Auth::user()->id;

    if(isset($request) && $request->role=='are' ) { 
        
        if($user->hasRole('are')){ 
          $query->whereHas('building', function ($query){       
           $query->whereHas('areBuildings', function ($query){
             $query->where('user_id','=', \Auth::user()->id);
             return $query;
           });   
           return $query;   
          }); 
        }
        elseif(!isset($request)  && $request->role=='are_team_lead'){
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
    }

  return $query;
}
/*
  *  Scope are DASHBOARD oNLOAD
  *
  */
  public function scopeAreDashloadFilter($query){

    $user = \Auth::user();
    $role_count = count($user->roles);
    $headUser = \Auth::user()->id;

    if($user->hasRole('are')){ 
      $query->whereHas('building', function ($query){       
       $query->whereHas('areBuildings', function ($query){
         $query->where('user_id','=', \Auth::user()->id);
         return $query;
       });   
       return $query;   
      }); 
    }
    elseif($user->hasRole('are_team_lead')){
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
  /*
   *
   *  Filter 
   *
   */
  public function scopeFilter($query, $request){

    if(isset($request)){     

      $fieldNameFlag = false;  
      $generalSearch = array(); 

      if($request->route()->getName() == 'tenantRenewalContract' )
       $request->merge(['contract_no' => $request->tenant_contract_old_no ]);


     if($request->route()->getName() == 'renewalContractApproval' )
      $request->merge(['contract_no' => $request->new_contract_no ]);



       // $request->request->remove('tenant_contract_old_no');        

    

     // dd($request);
    
    foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','fc_att','new_contract_no','tenant_contract_old_no','termination_date','expdays','grace_period','under_penalty','today','areUser','receivable_date','areUser_id','expiredContracts','vaccatingdays','role']) as $key => $val){         

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



         if( !empty($request->fieldValue[$keyName]) && !empty($request->fieldValue[$keyName]) && !empty($valueName) && $valueName  != 'tenant_contract_status') {

          $curr_logic =  ($i == 0)? 'and' : $next_logic;
          $next_logic =  $request->logic[$keyName]; $i++;


          if($request->route()->getName() == 'tenantRenewalContract' && $valueName == 'tenant_contract_no' )
            continue;

          if( (in_array($request->route()->getName(),['renewalContractApproval','tenantRenewedContract']) !== false) && $valueName == 'tenant_contract_old_no' )
            continue;                                

          if($request->route()->getName() == 'tenantRenewalContract' && $valueName == 'tenant_contract_old_no' )
           $valueName = 'tenant_contract_no';



         if($request->operation[$keyName] == 'ilike%...%' ){
          $fieldValue = '%'.$request->fieldValue[$keyName].'%';
          $operation = 'ilike';
        }else{
          $operation = $request->operation[$keyName];
          $fieldValue = $request->fieldValue[$keyName];
        }
                                 // $key =  key(next($request->fieldName));
                                //  dd($key);


        $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic); 

      }elseif($valueName  == 'tenant_contract_status'){

        $curr_logic =  ($i == 0)? 'and' : $next_logic; 
        $next_logic =  $request->logic[$keyName]; $i++;

        $operation = ($request->operation[$keyName] == '!=') ? '!=' : '=';
        $fieldValue = $request->fieldValue[$keyName];

        $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic);

      }

    }

                       //     dd($generalSearch);                              
    $generalSearch = collect($generalSearch)->groupBy('field');

                           // dd($generalSearch);

                             // dd($generalSearch)  ;
           //      continue;
  }else{ 
    $key = $val;                  
    $val = ($request->fieldName != 'tenant_contract_status') ? $request->fieldValue : $request->fieldValues;
  }
} 

if($key == 'fieldName' && is_array($request->fieldName)){ 

  foreach($generalSearch  as $searchKey => $searchVal){
                //    echo  $searchVal[0][0];
                   //    dd($searchVal);
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
 else{
  if(in_array($key,['contract_no','tenant_contract_old_no'])  !== false)
   $key = 'tenant_contract_no';

 $query->where($key,'ilike','%'.$val.'%'); 
}

}

}                   

}
}

}
return $query;

}
	/*
    *
    * Tenant Contract Building
    */
  public function building(){

    return $this->belongsTo('Modules\Masters\Entities\Building','building_id');
  }
    /*
*
*Unregistered Contracts
*
*/
public function scopeUnregisteredContracts($query,$request){  
  $unregistered = $request->tenant_contract_is_reg_municipality;  
  if(!empty($request->tenant_contract_is_reg_municipality)){
    $query->where(function ($query)use($unregistered){
     $query->where('tenant_contract_is_reg_municipality','=',$unregistered);
     return $query;
   });
  }
  return $query;
}
/*
*
*Total Receivables
*
*/
public function scopeReceivables($query,$request){    
  
  $today = date('Y-m-d');
  $lastdays = Carbon::today()->subDays(365);

  // if(!empty($request->days)){
  //   $days = $request->days;
  //   $nextDays = Carbon::today()->addDays($days);

  //   $query->where(function ($query)use($today,$nextDays){
  //    $query->whereDate('tenant_contract_valid_to_date','>=',$lastdays);
  //    // ->whereDate('tenant_contract_valid_to_date','<=',date('Y-m-d'));
  //    return $query;
  //  });
  // }   

   // $lastdays = Carbon::today()->subDays(365);
   
    $query->where(function ($query)use($lastdays){
     $query->whereDate('tenant_contract_valid_to_date','>=',$lastdays)
     ->whereDate('tenant_contract_valid_to_date','<=',Carbon::today());
     return $query;
   });
  

  if(!empty($request->areUser_id)){

   $areUser_id = $request->areUser_id;

   $query->whereHas('building', function ($query)use($areUser_id){       
      $query->whereHas('areBuildings', function ($query)use($areUser_id){
             $query->where('user_id','=', $areUser_id);
        });     
    });
  }
 

  return $query;
}
/*
*
*Expiring Tenant Contracts -30/60/90/120
*
*/
public function scopeExpiringContracts($query,$request){    
  $expdays = $request->expdays;
  $nextExpDays = Carbon::today()->addDays($expdays);
  $today = date('Y-m-d');
  if(!empty($request->expdays)){
   $query->where(function ($query)use($nextExpDays,$today){
     $query->whereDate('tenant_contract_valid_to_date','<',$nextExpDays)->whereDate('tenant_contract_valid_to_date','>=',$today)->where('tenant_renewal_termination_status','!=',6)->where('tenant_contract_status',1);;
     return $query;
   }); 
 }

 return $query;
}
/*
*Contracts expired within Grace Period -30 days
*
*/
public function scopeGracePeriod($query,$request){    
  $gracePeriod = $request->grace_period;
   $today = date('Y-m-d');
  $nextDays = Carbon::today()->subDays($gracePeriod);
  if(!empty($request->grace_period)){
   $query->where(function ($query)use($nextDays,$today){
     $query->whereDate('tenant_contract_valid_to_date','>=',$nextDays)->whereDate('tenant_contract_valid_to_date','<=',$today);
     return $query;
   }); 
 }
}
/*
*Contracts expired – Under Penalty (After 30 days)
*
*/
public function scopeUnderPenalty($query,$request){    
  $underPenalty = $request->under_penalty;
  $today = date('Y-m-d');
  $nextDays = Carbon::today()->subDays($underPenalty);
  if(!empty($request->under_penalty)){
   $query->where(function ($query)use($nextDays,$today){
     $query->whereDate('tenant_contract_valid_to_date','<',$nextDays);
	 //->where('tenant_penalty_invoice_amt','>',0);
     return $query;
   }); 
 }
}
/*
*Average Receivables
*/
public function scopeAverageReceivables($query,$request){    
  $today = date('Y-m-d');
  if(!empty($request->today)){
    $query->where(function ($query)use($today){
     $query->whereDate('tenant_contract_valid_to_date','>=',$today)
     ->where('status','!=',5);
     return $query;
   });
  }
  return $query;
}
/*
*
* Tenant 
*/
public function tenant(){

  return $this->belongsTo('Modules\Sales\Entities\Tenant','tenant_id','id');
}
/*
  *  Scope Revoke
  *
  */
  /*
  *  Scope Revoke
  *
  */
  public function scopeRevoke($query){    

    $query->where(function ($query){
       $query->where('tenant_contract_is_revoke','=',1)
             ->orWhere(function ($query){
                  $query->where('tennat_contract_direct_indirect_status','=',1)
                       ->where('contract_work_flow',107);
                       return $query;
          }); 
             return $query;
        });

   return $query;
  }
        /*
  *  ExpiredContracts
  *
  */
  public function scopeContractsExpired($query){    

     $query->where('tenant_contract_status', 1)
           ->whereDate('tenant_contract_valid_to_date', '<', Carbon::now()->toDateString());


   return $query;
  }

  /*
  *  ExpiredContracts
  *
  */
  public function scopeExpiredContracts($query,$request){    

   if(isset($request->expiredContracts)){
     $query->where('tenant_contract_status', 1)
           ->whereDate('tenant_contract_valid_to_date', '<', Carbon::now()->toDateString());
   }


   return $query;
  }


  public function scopeDirectIndirect($query,$request){

      if(!isset($request->fieldName)  || ( isset($request->fieldName) && !in_array('tenant_contract_status', $request->fieldName) ) ){          

           $query->where(function($query) {
              $query->where('tennat_contract_direct_indirect_status', 1)
                    ->whereIn('tenant_contract_status',[1,0])
					->where('tenant_renewal_termination_status','!=',8);
                    })
                   ->orWhere(function($query) {
                      $query->where('tennat_contract_direct_indirect_status', 0)
                            ->where('tenant_contract_status',1)
							->where('tenant_renewal_termination_status','!=',8);
                    });
         }
		 $query->where('tenant_renewal_termination_status','!=',8);
       
        return $query;
     }
  /*
    *
    * Tenant Contract Termination
    */
    public function terminationContract(){

      return $this->hasOne('Modules\BackOffice\Entities\Termination','contract_id','id')->orderBy('id', 'desc')->limit(1);
    }

  /*
*Tenant Vacating
*/
public function scopeTenantVacatting($query,$request){    
  $vaccatingdays = $request->vaccatingdays;
  $nextDays = Carbon::today()->addDays($vaccatingdays);
   $today = date('Y-m-d');
  if(!empty($request->vaccatingdays)){
    $query->whereHas('terminationContract',function ($query)use($nextDays){
     $query->whereDate('termination_date','<=',$nextDays);
     return $query;
   })->whereDate('tenant_contract_valid_to_date','<=',$nextDays)->whereDate('tenant_contract_valid_to_date','>=',$today);
  }
  return $query;
}
/*
*
*  tenant contract list 
*
**/ 
public function scopeTenantContract($query){

  $query->orWhere(function ($query){
           $query->orWhere('tennat_contract_direct_indirect_status','=',1)
           ->where('work_flow_processes_code','=',107);
     })->orWhere(function ($query){
       $query->orWhere('tenant_contract_status','=',1)
             ->Where('contract_work_flow','=',108);
     });
  
   return  $query;
}
/*
    *
    *Receipt Generation
    *
    *
    */
    public function receiptGeneration(){

      return $this->hasOne('Modules\BackOffice\Entities\ReceiptsGeneration','tenant_contract_id','id')->where('receipts_generation_type',2);
    }


}
