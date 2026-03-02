<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Support\Str;
use Carbon\Carbon;
class LandlordContract extends Model
{
	use Sortable;
    protected $guarded 	= [] ;
   
    protected $table  = 'landlord_contract';
    protected $dates  = ['landlord_contract_valid_to_date','landlord_contract_valid_from_date','start_date','end_date'];
    public $sortable  = ['landlord_contract_no','created_at','landlord_contract_duration','landlord_contract_valid_from_date','landlord_contract_valid_to_date','landlord_contract_status'];
    /*
    *
    * Landlord vendor info
    */
    public function vendorInfo(){
    
      return $this->belongsTo('Modules\Masters\Entities\Vendor','vendor_id','id');
    }
    /*
  	* Vendor Name
  	*
  	*/
  	  public function vendorName(){

      return $this->belongsTo('Modules\Masters\Entities\Vendor','vendor_id');
  	}

     /*
    *
    * Landlord building info
    */
    public function buildingInfo(){
    
      return $this->belongsTo('Modules\Masters\Entities\Building','building_id','id');
    }
	/*
    *
    * Payment method model eg) Monthly, Half yearly, Quraterly.
    */
    public function paymentMethodInfo(){
    
      return $this->belongsTo('Modules\Masters\Entities\PaymentMethod','landlord_contract_payment_type','payment_method_index');
    }
    /*
    *
    * Managment Type model eg) Comprehensive, Commisssion, Normal.
    */
    public function managementTypeInfo(){
    
      return $this->belongsTo('Modules\Masters\Entities\ManagementType','management_id','id');
    }
    
      /*
    *
    * Market executive employee namme.
    */
    public function marketExecutiveEmployeeInfo(){
    
      return $this->belongsTo('Modules\Masters\Entities\Employee','landlord_marketing_executive','id')->withTrashed();
    }
    /*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('landlord_contract_status', 1);
    }
    /*
    *
    *
    * Status NAme
    *
    */
    public function getLandlordContractStatusNameAttribute()
    {     
        switch($this->landlord_contract_status){
		  case '3' : return 'Pending-Direct';	
		  case '2' : return 'Unapproved-Direct';	
          case '1' : return 'Approved';
          case '0' : return 'Pending';        
        }
    }
    /*
    * 
    *  Landord sale enquiry
    *
    */
    public function landlordSale() {
        
           return $this->hasMany('Modules\Sales\Entities\Sales','sales_enquiry_id','sale_enquiry_id');
    }
    /*
    *
    * Tenant 
    */
    public function salesEnquiry(){

      return $this->belongsTo('Modules\Sales\Entities\SalesEnquiry','sale_enquiry_id');
    }
        public function scopeClosure($query, $result = array()){


        $closure =  $closure_or =  $building_nameq = $building_name_or =$vendor_nameq = $vendor_name_or = array();
        // $customer_name, $email , $phone  , $status
        $quick_search_flag = false;
        $qiuck_search  = array();
        if(count($result) > 0){
            list($closure, $closure_or,$building_nameq,$building_name_or,$vendor_nameq,$vendor_name_or,$qiuck_search) =  $result;
        
        if(count($qiuck_search) > 0)
            $quick_search_flag = true; 
        }
        $query->when($closure, function ($query) use($closure){
        return $query->where($closure);
        })

        ->when($closure_or, function ($query) use($closure_or){
            return $query->orwhere($closure_or);
        })
        ->when($building_nameq, function ($query) use($building_nameq){                  
            $query->whereHas('buildingInfo', function ($query) use($building_nameq){
               foreach($building_nameq as $building_name_val){                             
                    $query->where('building_name', $building_name_val[1],$building_name_val[2]);
                }   
            });             
        return $query;                                       
        }) 

        ->when($building_name_or , function ($query) use($building_name_or){                
            return $query->orwhereHas('buildingInfo', function ($query) use($building_name_or){
               foreach($building_name_or as $building_name_or_val){                           
                    return $query->where('building_name', $building_name_or_val[1],$building_name_or_val[2]);
                }
            return $query;    
            });             
        return $query;                                       
        })
        ->when($vendor_nameq, function ($query) use($vendor_nameq){                  
            $query->whereHas('vendorInfo', function ($query) use($vendor_nameq){
               foreach($vendor_nameq as $vendor_name_val){                             
                    $query->where('vendor_name', $vendor_name_val[1],$vendor_name_val[2]);
                }   
            });             
        return $query;                                       
        }) 

        ->when($vendor_name_or , function ($query) use($vendor_name_or){                
            return $query->orwhereHas('vendorInfo', function ($query) use($vendor_name_or){
               foreach($vendor_name_or as $vendor_name_or_val){                           
                    return $query->where('vendor_name', $vendor_name_or_val[1],$vendor_name_or_val[2]);
                }
            return $query;    
            });             
        return $query;                                       
        })
        ->when($quick_search_flag , function ($query) use($qiuck_search){  
           
           list($landlord_contract_old_no,$landlord_contract_no,$building_id, $vendor_id,$landlord_contract_amt,$landlord_contract_valid_from_date,$landlord_contract_valid_to_date) =   $qiuck_search;
                     
                $query->when($landlord_contract_old_no, function ($query, $landlord_contract_old_no) {
                    return $query->where('landlord_contract_no', 'ilike',  '%'.$landlord_contract_old_no.'%');
                })
                ->when($landlord_contract_no, function ($query, $landlord_contract_no) {
                    return $query->where('landlord_contract_no', 'ilike',  '%'.$landlord_contract_no.'%');
                })
                /*->when($landlord_contract_name, function ($query, $landlord_contract_name) {
                    return $query->where('landlord_contract_name', 'ilike',  '%'.$landlord_contract_name.'%');
                })*/
                ->when($landlord_contract_amt, function ($query, $landlord_contract_amt) {
                    return $query->where('landlord_contract_amt', 'ilike',  '%'.$landlord_contract_amt.'%');
                })
                ->when($landlord_contract_valid_from_date, function ($query) use($landlord_contract_valid_from_date){                                
                 return $query->whereDate('landlord_contract_valid_from_date','=', $landlord_contract_valid_from_date);                            
                })
                ->when($landlord_contract_valid_to_date, function ($query) use($landlord_contract_valid_to_date){                                
                 return $query->whereDate('landlord_contract_valid_to_date','=', $landlord_contract_valid_to_date);                            
                }) 
                ->when($building_id , function ($query) use($building_id){
                    $query->whereHas('buildingInfo', function ($query) use($building_id){
                        return $query->where('building_name','ilike', '%'.$building_id.'%');
                    });
                    return $query;                     
                })
                ->when($vendor_id , function ($query) use($vendor_id){
                    $query->whereHas('vendorInfo', function ($query) use($vendor_id){
                        return $query->where('vendor_name','ilike', '%'.$vendor_id.'%');
                    });
                    return $query;                     
                });
                                                      
        });    
       return $query; 
    }
    
    
    

   /*
   *
   *  Landlord Invoice 
   **/
   public function landlordInvoice(){
     return $this->hasOne('Modules\BackOffice\Entities\LandlordInvoice','landlord_contract_id');
   }

   
   /*
	*
	*Expiring Landlord Contracts within 90 days and expiring
	*
	*/
	public function scopeExpiringExpired($query,$request){    
	  $expdays = $request->expdays;
	  $today = date('Y-m-d');
	  $nextExpDays = Carbon::today()->addDays($expdays);
	  if(isset($request->expdays)){
		  $query->where('management_id',1)
		  ->where(function ($query)use($today,$nextExpDays){
		   $query->whereDate('landlord_contract_valid_to_date','<', $nextExpDays)
		   ->orWhere('landlord_contract_valid_to_date','<',$today);
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


      

      // if($request->route()->getName() == 'renewalContractApproval' )
      //   $request->merge(['contract_no' => $request->new_contract_no ]);
     
    
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','expdays','expiredContracts']) as $key => $val){         

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


                                  if($request->route()->getName() == 'renewalContract' && $valueName == 'landlord_contract_no' )
                                    continue;

                                  // if( (in_array($request->route()->getName(),['renewalContract','tenantRenewedContract']) !== false) && $valueName == 'tenant_contract_old_no' )
                                  //   continue;                                

                                  if($request->route()->getName() == 'renewalContract' && $valueName == 'old_landlord_contract_no' )
                                     $valueName = 'landlord_contract_no';

                                  if($request->route()->getName() != 'renewalContract' && $valueName == 'old_landlord_contract_no' )
                                     continue;;




                                 
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
                //  echo $key.' '.$val; exit;
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
//
                   if(Str::contains($key, ['status', 'landlord_contract_valid_to_date','landlord_contract_valid_from_date']) !== false ) 
                     $query->where($key,$val); 
                   else{

                     $route_name =   $request->route()->getName();

                     switch($route_name){

                         case 'renewalContract' :   if($key == 'landlord_contract_no') 
                                                    $key = ''; 

                                                    if($key == 'old_landlord_contract_no')
                                                    $key = 'landlord_contract_no'; 
                                                    break;


                         default :   if($key == 'old_landlord_contract_no') 
                                      $key = '';
                                     break;                          

                     }



                    // if($request->route()->getName() == 'renewalContract' && $key = 'old_landlord_contract_no')
                    // $key = 'landlord_contract_no';  
                    // elseif($key = 'old_landlord_contract_no') 
                    //   continue;

                     if(!empty($key))
                     $query->where($key,'ilike','%'.$val.'%'); 
                   }
                  
                    
                }

               }                   
               
             }
        }
          
      }
      return $query;

   }




}
