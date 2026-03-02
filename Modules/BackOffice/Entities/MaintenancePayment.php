<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenancePayment extends Model
{
	use Sortable;
  use SoftDeletes;
	protected $guarded = [];
	protected $table = 'maintenance_payment';
	protected $dates = ['maintenance_payment_date','created_at','deleted_at'];
	 public $sortable = ['id','maintenance_payment_no','maintenance_payment_date','vendor_id','maintenance_payment_method','bank_id','maintenance_payment_amount'];
    /*
    * Bank Info
    */
    public function bankInfo(){

    	return $this->belongsTo('Modules\Masters\Entities\Bank','bank_id','id');
    }
   /*
    *
    *  Vendor
    */
   public function vendor(){

   	return $this->belongsTo('Modules\Masters\Entities\Vendor');
   }
   /*
    * Payment Method
    *
    */

    public function getMaintenancePaymentMethodNameAttribute()
    {     
        switch($this->maintenance_payment_method){
          case '1' : return 'Cash';
          case '2' : return 'Cheque';        
        }
    }
    
	/*
    * Maintenance Payment Status
    */
	
    public function getMaintenancePaymentApprovalStatusNameAttribute()
    {     
        switch($this->maintenance_payment_approval_status){
          case '0' : return 'Active';
          case '1' : return 'label-primary | Draft';
          case '2' : return 'label-success | Pending For Approval';        
          case '3' : return 'label-warning | Pending For Draft';   
          case '4' : return 'label-success | Approved';        
          case '5' : return 'label-warning | Reject';        
        }
    }
   public function scopeClosure($query, $result = array()){


   	$closure =  $closure_or =  $vendor_nameq =  $vendor_name_or =  $bank_nameq = $bank_name_or =array();
        // $customer_name, $email , $phone  , $status
   	$quick_search_flag = false;
   	$qiuck_search  = array();

   	if(count($result) > 0){
   		list($closure, $closure_or,$vendor_nameq,$vendor_name_or,$bank_nameq,$bank_name_or,$qiuck_search) =  $result;

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
   	->when($bank_nameq, function ($query) use($bank_nameq){                  
   		$query->whereHas('bankInfo', function ($query) use($bank_nameq){
   			foreach($bank_nameq as $bank_name_val){                             
   				$query->where('bank_name', $bank_name_val[1],$bank_name_val[2]);
   			}   
   		});             
   		return $query;                                       
   	}) 

   	->when($bank_name_or , function ($query) use($bank_name_or){                
   		return $query->orwhereHas('bankInfo', function ($query) use($bank_name_or){
   			foreach($bank_name_or as $bank_name_or_val){                           
   				return $query->where('bank_name', $bank_name_or_val[1],$bank_name_or_val[2]);
   			}
   			return $query;    
   		});             
   		return $query;                                       
   	})
   	->when($quick_search_flag , function ($query) use($qiuck_search){  

   		list($maintenance_payment_no, $vendor_id,$bank_id, $maintenance_payment_method,$maintenance_payment_date,$maintenance_payment_amount,$vendor_code,$maintenance_payment_approval_status) =   $qiuck_search;

   		$query->when($maintenance_payment_no, function ($query, $maintenance_payment_no) {
   			return $query->where('maintenance_payment_no', 'ilike',  '%'.$maintenance_payment_no.'%');
   		})
      ->when($maintenance_payment_method, function ($query, $maintenance_payment_method) {
        return $query->where('maintenance_payment_method', 'ilike',  '%'.$maintenance_payment_method.'%');
      })  
      ->when($maintenance_payment_approval_status, function ($query, $maintenance_payment_approval_status) {
        return $query->where('maintenance_payment_approval_status', 'ilike',  '%'.$maintenance_payment_approval_status.'%');
      })
      ->when($maintenance_payment_amount, function ($query, $maintenance_payment_amount) {
        return $query->where('maintenance_payment_amount', 'ilike',  '%'.$maintenance_payment_amount.'%');
      })
   		->when($maintenance_payment_date, function ($query) use($maintenance_payment_date){                                
   			return $query->whereDate('maintenance_payment_date','=', $maintenance_payment_date);                            
   		})
   		->when($vendor_id , function ($query) use($vendor_id){
        $query->whereHas('vendor', function ($query) use($vendor_id){
          return $query->where('vendor_name','ilike', '%'.$vendor_id.'%');
        });
        return $query;                     
      })
      ->when($vendor_code , function ($query) use($vendor_code){
   			$query->whereHas('vendor', function ($query) use($vendor_code){
   				return $query->where('vendor_code','ilike', '%'.$vendor_code.'%');
   			});
   			return $query;                     
   		})
   		->when($bank_id , function ($query) use($bank_id){
   			$query->whereHas('bankInfo', function ($query) use($bank_id){
   				return $query->where('bank_name','ilike', '%'.$bank_id.'%');
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
     
    
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route']) as $key => $val){         

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

                   if( $key == 'status') {
                  if($val == 6 )
                    $query->where('maintenance_payment_approval_status',4)->where('maintenance_payment_status',3); 
                  elseif($val == 1) // Unapprove
                    $query->where('maintenance_payment_approval_status',$val)->where('maintenance_payment_status',4);
                  else
                    $query->where('maintenance_payment_approval_status',$val)->Where('maintenance_payment_status','!=', 3); 
                          }else
                
                  $query->where($key,'ilike','%'.$val.'%'); 
                   
                    
                }

               }                   
               
             }
        }
          
      }
      return $query;

   }




}
