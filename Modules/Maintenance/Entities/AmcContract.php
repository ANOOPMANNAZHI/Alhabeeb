<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class AmcContract extends Model
{
    use Sortable;
    protected $guarded = [];
    protected $table = 'amc_contract';
    protected $dates = ['amc_contract_period_from','amc_contract_period_to'];
    public $sortable = ['id','amc_contract_no','amc_contract_period_from','amc_contract_period_to','vendor_id','building_id','payment_method_id','amc_contract_cost'];

    /*
    *
    *  Vendor
    */
    public function vendor(){

      return $this->belongsTo('Modules\Masters\Entities\Vendor');
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
    *  Payment Method
    */
    public function paymentMethod(){

      return $this->belongsTo('Modules\Masters\Entities\PaymentMethod');
    }
 
   public function scopeClosure($query, $result = array()){


        $closure =  $closure_or =  $vendor_nameq =  $vendor_name_or =  $building_nameq = $building_name_or =$payment_method_codeq =$payment_method_code_or = array();
        // $customer_name, $email , $phone  , $status
        $quick_search_flag = false;
        $qiuck_search  = array();
   
        if(count($result) > 0){
            list($closure, $closure_or,$vendor_nameq,$vendor_name_or,$building_nameq,$building_name_or,$payment_method_codeq,$payment_method_code_or,$qiuck_search) =  $result;
        
        if(count($qiuck_search) > 0)
            $quick_search_flag = true; 
        }
        $query->when($closure, function ($query) use($closure){
        return $query->where($closure);
        })

        ->when($closure_or, function ($query) use($closure_or){
            return $query->orwhere($closure_or);
        })  
        ->when($vendor_nameq, function ($query) use($vendor_nameq){                  
            $query->whereHas('vendor', function ($query) use($vendor_nameq){
               foreach($vendor_nameq as $vendor_name_val){                             
                    $query->where('vendor_name', $vendor_name_val[1],$vendor_name_val[2]);
                }   
            });             
        return $query;                                       
        }) 
        ->when($vendor_name_or , function ($query) use($vendor_name_or){                
            $query->orwhereHas('vendor', function ($query) use($vendor_name_or){
               foreach($vendor_name_or as $vendor_name_or_val){                           
                    $query->where('vendor_name', $vendor_name_or_val[1],$vendor_name_or_val[2]);
                }   
            });             
        return $query;                                       
        })
        ->when($building_nameq, function ($query) use($building_nameq){                  
            $query->whereHas('building', function ($query) use($building_nameq){
               foreach($building_nameq as $building_name_val){                             
                    $query->where('building_name', $building_name_val[1],$building_name_val[2]);
                }   
            });             
        return $query;                                       
        }) 

        ->when($building_name_or , function ($query) use($building_name_or){                
            return $query->orwhereHas('building', function ($query) use($building_name_or){
               foreach($building_name_or as $building_name_or_val){                           
                    return $query->where('building_name', $building_name_or_val[1],$building_name_or_val[2]);
                }
            return $query;    
            });             
        return $query;                                       
        })
         ->when($payment_method_codeq, function ($query) use($payment_method_codeq){                  
            $query->whereHas('paymentMethod', function ($query) use($payment_method_codeq){
               foreach($payment_method_codeq as $payment_method_code_val){                             
                    $query->where('payment_method_code', $payment_method_code_val[1],$payment_method_code_val[2]);
                }   
            });             
        return $query;                                       
        }) 
        ->when($payment_method_code_or , function ($query) use($payment_method_code_or){                
            $query->orwhereHas('paymentMethod', function ($query) use($payment_method_code_or){
               foreach($payment_method_code_or as $payment_method_code_or_val){                           
                    $query->where('payment_method_code', $payment_method_code_or_val[1],$payment_method_code_or_val[2]);
                }   
            });             
        return $query;                                       
        })
        ->when($quick_search_flag , function ($query) use($qiuck_search){  
           
           list($amc_contract_no, $vendor_id,$building_id, $payment_method_id,$amc_contract_period_from,$amc_contract_period_to) =   $qiuck_search;
                     
                $query->when($amc_contract_no, function ($query, $amc_contract_no) {
                    return $query->where('amc_contract_no', 'ilike',  '%'.$amc_contract_no.'%');
                })
                ->when($amc_contract_period_from, function ($query) use($amc_contract_period_from){                                
                 return $query->whereDate('amc_contract_period_from','=', $amc_contract_period_from);                            
                })
                ->when($amc_contract_period_to, function ($query) use($amc_contract_period_to){                                
                 return $query->whereDate('amc_contract_period_to','=', $amc_contract_period_to);                            
                })
                ->when($vendor_id , function ($query) use($vendor_id){
                    $query->whereHas('vendor', function ($query) use($vendor_id){
                        return $query->where('vendor_name','ilike', '%'.$vendor_id.'%');
                    });
                    return $query;                     
                })
                ->when($building_id , function ($query) use($building_id){
                    $query->whereHas('building', function ($query) use($building_id){
                        return $query->where('building_name','ilike', '%'.$building_id.'%');
                    });
                    return $query;                     
                })
                ->when($payment_method_id , function ($query) use($payment_method_id){
                    $query->whereHas('paymentMethod', function ($query) use($payment_method_id){
                        return $query->where('payment_method_code','ilike', '%'.$payment_method_id.'%');
                    });
                    return $query;                     
                });
                                                      
        });    
       return $query; 
    }
    /*
    *
    *  Amc Contract
    */
    public function amcSchedule(){

      return $this->hasOne('Modules\Maintenance\Entities\AmcSchedule','amc_contract_id','id');
    }
       /*
    *
    *  schedule
    */
    /*public function amcSchedules(){

      return $this->hasMany('Modules\Masters\Entities\AmcSchedule','amc_contract_id','id');
    }*/
    /*
    *
    * schedule
    */
    public function schedules(){

      return $this->hasMany('Modules\Maintenance\Entities\AmcSchedule','amc_contract_id','id')->where('amc_schedule_status',0);
    }
    /*
    *
    * schedule
    */
    public function cancelSchedules(){

      return $this->hasMany('Modules\Maintenance\Entities\AmcSchedule','amc_contract_id','id')->where('amc_schedule_status',2);
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
           
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','fc_att']) as $key => $val){

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
                                 
                                  if($request->operation[$keyName] == 'ilike%...%' ){
                                    $fieldValue = '%'.$request->fieldValue[$keyName].'%';
                                    $operation = 'ilike';
                                  }else{
                                  $operation = $request->operation[$keyName];
                                  $fieldValue = $request->fieldValue[$keyName];
                                  }
                                
                                  if ($i == 0)
                                    $curr_logic = 'and';
                                  else
                                    $curr_logic = $next_logic;

                                  $next_logic =  $request->logic[$keyName]; $i++;

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

                               if($subSearchVal['operation'] ==  'ilike%...%' )
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

}   
