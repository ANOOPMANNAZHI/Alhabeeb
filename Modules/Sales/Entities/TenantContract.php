<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;
use DB;

class TenantContract extends Model
{
	use Sortable;
	protected $guarded = [];
    protected $fillable = [];
    protected $table = 'tenant_contracts';
    protected $dates = ['tenant_contract_start_date','tenant_contract_effective_date','tenant_contract_valid_to_date','tenant_contract_valid_from_date','created_at','tenant_contract_last_paid_date','tenant_contract_vacant_since','tenant_contract_receipt_date','tenant_penalty_start_date','tenant_penalty_valid_to_date','vaccant_date','tenant_contract_registered_date'];
	public $sortable = ['tenant_contract_no','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','building_id','unit_id','tenant_contract_start_date','tenant_id'];
   
   /*protected $attributes = ['tenant_contract_status'=>1];
	*/
    /*
    *
    * Tenant Contract Building
    */
    public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building','building_id');
    }

    /*
    *
    * Tenant Contract DepositRefund
    */
    public function depositRefund(){

      return $this->hasMany('Modules\BackOffice\Entities\DepositRefund','tenant_contract_id');
    }
    /*
    *
    * Tenant Contract Building
    */
    public function tenantContractOld(){

      return $this->belongsTo('Modules\Sales\Entities\TenantContract','tenant_contract_old_no','tenant_contract_no');
    }
    /*
    *
    * Tenant Contract Unit
    */
    public function Unit(){

      return $this->belongsTo('Modules\Masters\Entities\Unit');
    }
    /*
    *
    *  Tenant Contract Duration Type 
    */
    /*public function getTenantContractDurationTypeAttribute($value)
    {

    	if($value == 1)
    		$name = 'Year';
    	else if($value == 2)
    		$name = 'Month';
    	else if($value == 3)
    		$name = 'Day';
    	return $name;
	}*/
    /*
    *
    *  Tenant payment Method
    */
    public function getTenantContractPaymentNameAttribute()
    {
        switch($this->tenant_contract_payment_type){
          case '1' : return 'Monthly';
          case '2' : return 'Bi-Monthly';
          case '3' : return 'Quarterly';
          case '4' : return 'Half Yearly';
          case '5' : return 'Yearly';
        }
    }

    /*
    *
    * Last Paid Date for display.
    *
    * tenant_contract_last_paid_date is only written when a rent receipt is
    * POSTED to AX (RentReceiptGenerationController.php:1606 and
    * RoutinesController.php:917), so a receipt that was generated but not yet
    * posted left the screen showing "NA" even though the tenant had paid.
    * This also looks at the receipts themselves, so the date appears as soon
    * as the receipt exists.
    *
    * Only type 0 (tenant/rent) receipts count - type 1 general and type 2
    * deposit receipts do not represent rent paid-up-to. Cancelled receipts
    * (status 2) are ignored. The later of the two values wins, so this can
    * only move the date forward, never behind the posted figure.
    */
    public function getDisplayLastPaidDateAttribute()
    {
        $storedDate = $this->tenant_contract_last_paid_date;

        $receiptDate = \Modules\BackOffice\Entities\ReceiptsGeneration::where('tenant_contract_id', $this->id)
            ->where('receipts_generation_type', 0)
            ->where('receipts_generation_status', '!=', 2)
            ->whereNotNull('receipts_generation_eff_to')
            ->max('receipts_generation_eff_to');

        if (empty($receiptDate)) {
            return $storedDate;
        }

        $receiptDate = Carbon::parse($receiptDate);

        if (empty($storedDate)) {
            return $receiptDate;
        }

        return $receiptDate->gt($storedDate) ? $receiptDate : $storedDate;
    }
    
    /*
    *
    * Tenant 
    */
    public function tenant(){

      return $this->belongsTo('Modules\Sales\Entities\Tenant','tenant_id');
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
    public function tenantDocument(){

      return $this->hasMany('Modules\Sales\Entities\TenantDocument','tenant_contract_id');
    }
    /*
    *
    * Discussion Form
    */
    public function discussion(){

      return $this->hasMany('Modules\BackOffice\Entities\DiscussionForums','tenant_contract_id')->latest();
    }
    /*
    *
    * tenantRenewalForm
    */
    public function tenantRenewalForm(){

      return $this->hasMany('Modules\BackOffice\Entities\TenantRenewalForm','tenant_contract_id');
    }

    public function tenantRenewalFormLatest(){

      return $this->hasOne('Modules\BackOffice\Entities\TenantRenewalForm','tenant_contract_id')->latest();
    }
    /*
    *
    *  Tenant Contract Status
    */
   /* public function getTenantContractStatusAttribute($value)
    {

        if($value == 0)
            $name = 'Inactive';
        else if($value == 1)
            $name = 'Active';
        else if($value == 2)
            $name = 'Expired';
        return $name;
    }*/
    /*
    * Status Name
    *
    */

    public function getTenantContractStatusNameAttribute()
    {     
        switch($this->tenant_contract_status){
          case '1' : return 'Active';
          case '0' : return 'Inactive';        
        }
    }
    
    /*
    * Status Name
    *
    */
    public function getTenantContractRegisteredInNameAttribute()
    {     
       
        /*if($value == 1)
            $name = 'Muscat';
        else if($value == 2)
            $name = 'Not In Muscat';
        return $name;*/
        switch($this->tenant_contract_registered_in){
          case '1' : return 'Muscat';
          case '0' : return 'Not In Muscat';        
        }
    }
    /*
    *  Direct Status Name
    *
    */

    public function getStatusNameAttribute()
    {     
        switch($this->status){
          case '0' : return 'Normal';
          case '2' : return 'On Hold';
          case '3' : return 'No Maintenance';
          case '4' : return 'Blacklisted';        
          case '5' : return 'Move to Legal';        
          case '7' : return 'Alert';        
          case '8' : return 'Observation';        
          case '9' : return 'Watch';        
        }
    }
    /*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('tenant_contract_status', 1);
    } 
    /*
    * Search   
    *
    *
    *
    */
    public function scopeClosure($query, $result = array()){

        $closure =  $closure_or =  $building =  $building_or =  $unit = $unit_or  =  $tenant = $tenant_or = array();
     
        // $customer_name, $email , $phone  , $status
        $quick_search_flag = false;
        $qiuck_search  = array();
   
        if(count($result) > 0){
            list($closure, $closure_or,$building,$building_or,$unit,$unit_or,$tenant , $tenant_or, $qiuck_search) =  $result;
        
        if(count($qiuck_search) > 0)
            $quick_search_flag = true; 
        }

       //dd($result) ; exit;
        
        $query->when($closure, function ($query) use($closure){
        return $query->where($closure);
        })
        ->when($closure_or, function ($query) use($closure_or){
            return $query->orwhere($closure_or);
        })  
        ->when($building, function ($query) use($building){               
            $query->whereHas('building', function ($query) use($building){
               foreach($building as $building_val){                             
                    $query->where('building_name', $building_val[1],$building_val[2]);                 
                } 
                return $query;   
            });             
        return $query;                                       
        }) 
        ->when($building_or , function ($query) use($building_or){                
            $query->orwhereHas('building', function ($query) use($building_or){
               foreach($building_or as $building_or_val){                           
                    $query->orWhere('building_name', $building_or_val[1],$building_or_val[2]);
                }   
            });             
        return $query;                                       
        })
        ->when($unit, function ($query) use($unit){                  
            $query->whereHas('Unit', function ($query) use($unit){
               foreach($unit as $unit_val){                             
                    $query->where('unit_code', $unit_val[1],$unit_val[2]);
                }   
            });             
        return $query;                                       
        }) 
        ->when($unit_or , function ($query) use($unit_or){                
            $query->orwhereHas('Unit', function ($query) use($unit_or){
               foreach($unit_or as $unit_or_val){                           
                    $query->orWhere('unit_code', $unit_or_val[1],$unit_or_val[2]);
                }   
            });             
        return $query;                                       
        })
        ->when($tenant, function ($query) use($tenant){                  
            $query->whereHas('tenant', function ($query) use($tenant){
               foreach($tenant as $tenant_val){                             
                    $query->where('tenant_name', $tenant_val[1],$tenant_val[2]);
                }   
            });             
        return $query;                                       
        }) 
        ->when($tenant_or , function ($query) use($tenant_or){                
            $query->orwhereHas('tenant', function ($query) use($tenant_or){
               foreach($tenant_or as $tenant_or_val){                           
                    $query->orWhere('tenant_name', $tenant_or_val[1],$tenant_or_val[2]);
                }   
            });             
        return $query;                                       
        })
        ->when($quick_search_flag , function ($query) use($qiuck_search){  
           
           list($contract_no, $tenant_contract_old_no,$building_id , $unit_id, $tenant_contract_start_date, $tenant_contract_valid_to_date,$tenant_id,$tenant_contract_rent,$tenant_contract_muncipality_agr_no,$locationId,$tenant_contact_no,$way_no) =   $qiuck_search;

                $query->when($contract_no, function ($query, $contract_no) {
                    return $query->where('tenant_contract_no', 'ilike',  '%'.$contract_no.'%');
                })
                ->when($tenant_contract_old_no, function ($query, $tenant_contract_old_no) {
                    return $query->where('tenant_contract_no', 'ilike',  '%'.$tenant_contract_old_no.'%');
                })
                ->when($building_id, function ($query, $building_id) {
                    $query->whereHas('building', function ($query) use($building_id){
                        return $query->where('building_name','ilike', '%'.$building_id.'%');
                    });
                    return $query; 
                })     
                ->when($unit_id , function ($query) use($unit_id){
                    $query->whereHas('Unit', function ($query) use($unit_id){
                        return $query->where('unit_code','ilike', '%'.$unit_id.'%');
                    });
                    return $query;                     
                })
                
                ->when($tenant_contract_start_date, function ($query, $tenant_contract_start_date) {
                    return $query->whereDate('tenant_contract_start_date', '=',  $tenant_contract_start_date);
                })  
                ->when($tenant_contract_valid_to_date , function ($query) use($tenant_contract_valid_to_date){
                    return $query->whereDate('tenant_contract_valid_to_date', '=',  $tenant_contract_valid_to_date);                     
                })
                ->when($tenant_id , function ($query) use($tenant_id){
                    $query->whereHas('tenant', function ($query) use($tenant_id){
                        return $query->where('tenant_name','ilike', '%'.$tenant_id.'%');
                    });
                    return $query;                     
                })
                ->when($tenant_contract_rent, function ($query, $tenant_contract_rent) {
                    return $query->where('tenant_contract_rent', 'ilike','%'.$tenant_contract_rent.'%');
                })
                ->when($tenant_contract_muncipality_agr_no, function ($query, $tenant_contract_muncipality_agr_no) {
                    return $query->where('tenant_contract_muncipality_agr_no', 'ilike','%'.  $tenant_contract_muncipality_agr_no.'%');
                })
                ->when($locationId , function ($query) use($locationId){
                    $query->whereHas('tenant', function ($query) use($locationId){
                        $query->whereHas('location', function ($query) use($locationId){
                            return $query->where('locations_name','ilike', '%'.$locationId.'%');
                        });
                    });
                    return $query;                     
                })
                ->when($tenant_contact_no , function ($query) use($tenant_contact_no){
                    $query->whereHas('tenant', function ($query) use($tenant_contact_no){
                        return $query->where('tenant_contact_no','ilike', '%'.$tenant_contact_no.'%');
                    });
                    return $query;                     
                })
                ->when($way_no , function ($query) use($way_no){
                    $query->whereHas('tenant', function ($query) use($way_no){
                        return $query->where('tenant_contact_address','ilike', '%'.$way_no.'%');
                    });
                    return $query;                     
                });
                    
                                                      
        });                         
       
       return $query; 
    }
    /*
    *  Renewal Termination Contract  Status Name
    *
    */

    public function getTenantRenewalTerminationStatusNameAttribute()
    {     
        switch($this->tenant_renewal_termination_status){
          case '0' : return 'Normal';
          case '1' : return 'Requested';
          case '2' : return 'Under Renewal';
          case '3' : return 'Under Approval';
          case '4' : return 'Rejected';        
        }
    }
    /*
    *  Renewal Termination Contract Class
    */
    public function getTenantRenewalTerminationStatusClassAttribute()
    {

        $code =  $this->tenant_renewal_termination_status; 

        switch($code){

             case 1:
               $class = 'label-success';
                break;

             case 3:           
                $class = 'label-info';
                break; 
             case 2: 
                $class = 'label-warning';
                break; 
             case 4: 
                $class = 'label-danger';
                break; 
             default : $class = 'label-danger';
                        break;
              

        }


        return $class;

        
    }
    /*
    *
    * Tenant Contract Renewal
    */
    public function renewalContract(){

      return $this->hasOne('Modules\BackOffice\Entities\Renewal','new_contract_id','id');
    }
    /*
    *
    * Tenant Contract Termination
    */
    public function terminationContract(){

      return $this->hasOne('Modules\BackOffice\Entities\Termination','contract_id','id')->orderBy('id', 'desc')->limit(1);
    }
    /*
    * 
    *  Termination Checklist
    *
    */
    public function terminationChecklist() {
        
           return $this->hasMany('Modules\BackOffice\Entities\TerminationChecklist','termination_contract_id')->whereNotNull('work_id');
    }
    /*
    * 
    *  Termination Checklist
    *
    */
    public function terminationChecklistOther() {
        
           return $this->hasMany('Modules\BackOffice\Entities\TerminationChecklist','termination_contract_id')->whereNull('work_id');
    }
    /*
    *
    * Market executive employee namme.
    */
    public function marketExecutiveEmployeeInfo(){
    
      return $this->belongsTo('Modules\Masters\Entities\Employee','tenant_marketing_executive','id');
    }  
     /*
    *
    * Market executive user namme.
    */
    public function marketExecutiveUserInfo(){
    
      return $this->belongsTo('App\User','tenant_marketing_executive','id');
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
     /*
    *
    *Deposit Receipt Generation
    *
    *
    */
    public function receiptGenerationList(){

      return $this->hasMany('Modules\BackOffice\Entities\ReceiptsGeneration','tenant_contract_id','id')->where('receipts_generation_type',2);
    } 


    
  /*
  *  Scope Revoke
  *
  */
  public function scopeRevoke($query){    

    $query->where(function ($query){
       $query->where('tenant_contract_is_revoke','=',1)
             ->orWhere(function ($query){
                  $query->where('tennat_contract_direct_indirect_status','=',1)
                       ->where('work_flow_processes_code',107);
                       return $query;
          }); 
             return $query;
        });

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
    
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','fc_att','new_contract_no','tenant_contract_old_no','termination_date']) as $key => $val){         

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

     public function scopeDirectIndirect($query,$request){

      if(!isset($request->fieldName)  || ( isset($request->fieldName) && !in_array('tenant_contract_status', $request->fieldName) ) ){          

           $query->where(function($query) {
              $query->where('tennat_contract_direct_indirect_status', 1)
                    ->whereIn('tenant_contract_status',[1,0]);
                    })
                   ->orWhere(function($query) {
                      $query->where('tennat_contract_direct_indirect_status', 0)
                            ->where('tenant_contract_status',1);
                    });
         }
       
        return $query;
     }


  /*
   *
   *  Filter 
   *
   */
  /*
   public function scopeFilter($query, $request){

      if(isset($request)){          
           
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax']) as $key => $val){

           if($key == 'fieldValue' || $key == 'fieldValues' )
              continue;

             if($val != ''){  

              if($key == 'fieldName'){
                  $key = $val;                  
                  $val = ($request->fieldName != 'tenant_contract_status') ? $request->fieldValue : $request->fieldValues;
               }      
 
                if(strpos($key, '__') !== false) {

                    $method = explode('__',$key);

                    $query->whereHas($method[0], function ($query) use($method,$val){
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
      return $query;

   } */
/*
*
*Total Receivables
*
*/
public function scopeReceivables($query,$request){    
    $days = $request->days;
    $nextDays = Carbon::today()->addDays($days);
    $today = date('Y-m-d');
    $query->where(function ($query)use($today,$nextDays){
       $query->whereDate('tenant_contract_effective_date','<=',$today)
       ->whereDate('tenant_contract_valid_to_date','>=',$nextDays);
       return $query;
   });
    return $query;
}
/*
*
*Expiring Tenant Contracts
*
*/
public function scopeExpiringContracts($query,$request){    
    $expdays = $request->expdays;
    $today = date('Y-m-d');
    $nextExpDays = Carbon::today()->addDays($expdays);
    $query->where(function ($query)use($today,$nextExpDays){
       $query->whereDate('tenant_contract_valid_to_date','<',$nextExpDays);
       return $query;
   });
    return $query;
}
/*
*
*Expired Tenant Contracts -Grace Period-------------pending work
*
*/
public function scopeGracePeriodContracts($query){    
    $expdays = 30;
    //dd($expdays);
    $today = date('Y-m-d');
    $nextExpDays = Carbon::today()->addDays($expdays);
  //  dd($nextExpDays);
    $query->where(function ($query)use($today,$expdays){
      // $query->whereDate('tenant_contract_valid_to_date','<=',$today);
      //$query->whereRaw('DATE_PART(DATE(tenant_contract_valid_to_date),?) < ?')
            //->setBindings([$today,$expdays]);
       
      // $query->where(DB::raw('DATE_ADD(tenant_contract_valid_to_date, 3)'), '<', $today);
        // $query->whereRaw('date(tenant_contract_valid_to_date) <= DATE_ADD(tenant_contract_valid_to_date, INTERVAL 30 DAY)');



       return $query;
   });
    return $query;
}

/*
*
*Unregistered Contracts
*
*/
public function scopeUnregisteredContracts($query,$request){

$unregistered = $request->tenant_contract_is_reg_municipality; 
dd($unregistered); 
    $query->where(function ($query){
       $query->where('tenant_contract_is_reg_municipality','=',$unregistered);
       return $query;
   });
    return $query;
}
/*
*
*Unregistered Contracts
*
*/
public function buildingPreferred(){
   return $this->hasOne('Modules\Masters\Entities\PreferredBuilding','building_id','building_id')->where('assign_to',null);

}
 /*
* ARE Name
*
**/
public function buildingAssignToName(){

   return $this->belongsTo('Modules\Masters\Entities\AreBuildingAssign','are_building_id');
}
/*
*
*  Are
*/
 public function areUser(){
  return $this->belongsTo('App\User','user_id','id');
} 
/*
*
*  Created By
*/
 public function createdBy(){
  return $this->belongsTo('App\User','created_by','id');
}
/*
* Status Name
*
*/

public function getIsDirectContractPendingNameAttribute()
{    
	switch($this->is_direct_contract_pending){
	  case '1' : return 'Normal';
	  case '2' : return 'Under Approval';        
	}
}
 /*
*
* Sales Enquiry
*/
public function salesInfo(){

  return $this->belongsTo('Modules\Sales\Entities\SalesEnquiry','sale_enquiry_id','id');
}

/*
    *
    *Rent Receipt Generation
    *
    */ 

    public function rentReceiptGenerationList(){

      return $this->hasMany('Modules\BackOffice\Entities\ReceiptsGeneration','tenant_contract_id','id')->where('receipts_generation_type',0);
    }
	
	/*
    *
    *General Receipt Generation
    *
    */ 

    public function generalReceiptGenerationList(){

      return $this->hasMany('Modules\BackOffice\Entities\ReceiptsGeneration','tenant_contract_id','id')->where('receipts_generation_type',1);
    } 
	/*
    *
    * Tenant Contract Comment
    */
    public function tenantContractComment(){

      return $this->hasMany('Modules\BackOffice\Entities\TenantContractComment','tenant_contract_id','id');
    }
	/*
    * Invoice Dimenision details
    */
    public function tenantInvoiceDimensionInfo(){
    
      return $this->hasMany('Modules\BackOffice\Entities\TenantInvoiceDimension','invoice_id');
    }
	/*
    * Invoices per contract
    */
    public function tenantInvoiceList(){
    
      return $this->hasMany('Modules\BackOffice\Entities\invoice','tenant_contract_id','id'); 
    }
}
