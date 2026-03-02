<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepositRefund extends Model
{
  use Sortable;
  use SoftDeletes;
  protected $guarded = [];
  protected $table = 'deposit_refund';
  protected $dates = ['deposit_refund_date','deposit_refund_valid_from','deposit_refund_valid_to','deposit_refund_cancelled_date','deposit_refund_posted_date','created_at'];
  public $sortable = ['id'];

     /*
    *
    *Tenant Contract
    *
    *
    */
     public function tenantContract(){

      return $this->belongsTo('Modules\Sales\Entities\TenantContract','tenant_contract_id','id');
    }
       /*
    *
    *Receipt Generation
    *
    *
    */
       public function receiptGeneration(){

        return $this->belongsTo('Modules\BackOffice\Entities\ReceiptsGeneration','receipts_generation_id','id');
      }
      /*
    * Payment Method
    *
    */
      public function getDepositRefundApprovalStatusNameAttribute()
      {     
         
        switch($this->deposit_refund_approval_status){
          case '0' : return 'label-primary | Draft';
          case '1' : return 'label-primary | Draft';
          case '2' : return 'label-warning | Pending For Approval';        
          case '3' : return 'label-warning | Pending For Draft';        
          case '4' : return 'label-success | Approved';        
          case '5' : return 'label-danger  | Reject';        
        }
      }
      public function getDepositRefundPaymentMethodNameAttribute()
      {     
        switch($this->deposit_refund_payment_method){
          case '1' : return 'Cheque';        
          case '2' : return 'Cash';
        }
      }
      /*
   * DepositRefundDimension 
   *
   */
      public function depositRefundDimension(){

       return $this->hasMany('Modules\BackOffice\Entities\DepositRefundDimension')->orderBy('id','ASC');

     }

      /*
   * DepositRefundDimension 
   *
   */
      public function depositRefundDim(){
       
       return $this->hasOne('Modules\BackOffice\Entities\DepositRefundDimension');
       
     }
 /**
     * Get all of the owning dim1 models.
     */
    public function dim1()
    {
        return $this->morphTo();
    }
   
     /**
     * Get all of the owning dim1 models.
     */
    public function dim2()
    {
        return $this->morphTo();
    }
     public function scopeClosure($query, $result = array()){


      $closure =  $closure_or =  $contract_noq = $contract_no_or = $receipt_noq = $receipt_no_or =array();
        // $customer_name, $email , $phone  , $status
      $quick_search_flag = false;
      $qiuck_search  = array();

      if(count($result) > 0){
        list($closure, $closure_or,$contract_noq,$contract_no_or,$receipt_noq,$receipt_no_or,$qiuck_search) =  $result;

        if(count($qiuck_search) > 0)
          $quick_search_flag = true; 
      }
      $query->when($closure, function ($query) use($closure){
        return $query->where($closure);
      })

      ->when($closure_or, function ($query) use($closure_or){
        return $query->orwhere($closure_or);
      })  
      ->when($receipt_noq, function ($query) use($receipt_noq){                  
        $query->whereHas('receiptGeneration', function ($query) use($receipt_noq){
          foreach($receipt_noq as $receipt_no_val){                             
            $query->where('receipts_generation_receipt_no', $receipt_no_val[1],$receipt_no_val[2]);
          }   
        });             
        return $query;                                       
      }) 
      ->when($receipt_no_or , function ($query) use($receipt_no_or){   
        $query->whereHas('receiptGeneration', function ($query) use($receipt_no_or){ 
          foreach($receipt_no_or as $receipt_no_or_val){                           
            $query->where('receipts_generation_receipt_no', $receipt_no_or_val[1],$receipt_no_or_val[2]);
          }   
        });              
        return $query;                                       
      })
      ->when($contract_noq, function ($query) use($contract_noq){                  
        $query->whereHas('tenantContract', function ($query) use($contract_noq){
          foreach($contract_noq as $contract_no_val){                             
            $query->where('tenant_contract_no', $contract_no_val[1],$contract_no_val[2]);
          }   
        });             
        return $query;                                       
      }) 

      ->when($contract_no_or , function ($query) use($contract_no_or){                
        return $query->orwhereHas('tenantContract', function ($query) use($contract_no_or){
          foreach($contract_no_or as $contract_no_or_val){                           
            return $query->where('tenant_contract_no', $contract_no_or_val[1],$contract_no_or_val[2]);
          }
          return $query;    
        });             
        return $query;                                       
      })
      ->when($quick_search_flag , function ($query) use($qiuck_search){  

        list($deposit_refund_no, $receipts_generation_id,$building_id, $building_code,$deposit_refund_date,$unit_id,$tenant_name,$tenant_code,$tenant_contract_id,$deposit_refund_payment_method,$deposit_refund_amt,$deposit_refund_approval_status) =   $qiuck_search;

        $query->when($deposit_refund_no, function ($query, $deposit_refund_no) {
          return $query->where('deposit_refund_no', 'ilike',  '%'.$deposit_refund_no.'%');
        }) ->when($deposit_refund_payment_method, function ($query, $deposit_refund_payment_method) {
          return $query->where('deposit_refund_payment_method', 'ilike',  '%'.$deposit_refund_payment_method.'%');
        }) ->when($deposit_refund_amt, function ($query, $deposit_refund_amt) {
          return $query->where('deposit_refund_amt', 'ilike',  '%'.$deposit_refund_amt.'%');
        }) ->when($deposit_refund_approval_status, function ($query, $deposit_refund_approval_status) {
          return $query->where('deposit_refund_approval_status', 'ilike',  '%'.$deposit_refund_approval_status.'%');
        }) 
        ->when($deposit_refund_date, function ($query) use($deposit_refund_date){                                
          return $query->whereDate('deposit_refund_date','=', $deposit_refund_date);                            
        })
        ->when($receipts_generation_id , function ($query) use($receipts_generation_id){
          $query->whereHas('receiptGeneration', function ($query) use($receipts_generation_id){
            return $query->where('receipts_generation_receipt_no','ilike', '%'.$receipts_generation_id.'%');
          });
          return $query;                     
        })
        ->when($building_id , function ($query) use($building_id){
          $query->whereHas('tenantContract', function ($query) use($building_id){
            $query->whereHas('building', function ($query) use($building_id){
              return $query->where('building_name','ilike', '%'.$building_id.'%');
            });
          });
          return $query;                     
        }) ->when($building_code , function ($query) use($building_code){
          $query->whereHas('tenantContract', function ($query) use($building_code){
            $query->whereHas('building', function ($query) use($building_code){
              return $query->where('building_code','ilike', '%'.$building_code.'%');
            });
          });
          return $query;                     
        }) ->when($unit_id , function ($query) use($unit_id){
          $query->whereHas('tenantContract', function ($query) use($unit_id){
            $query->whereHas('building', function ($query) use($unit_id){
              $query->whereHas('unit', function ($query) use($unit_id){
                return $query->where('unit_code','ilike', '%'.$unit_id.'%');
              });
            });
          });
          return $query;                     
        })->when($tenant_name , function ($query) use($tenant_name){
          $query->whereHas('tenantContract', function ($query) use($tenant_name){
            $query->whereHas('tenant', function ($query) use($tenant_name){
              return $query->where('tenant_name','ilike', '%'.$tenant_name.'%');
            });
          });
          return $query;                     
        })->when($tenant_code , function ($query) use($tenant_code){
          $query->whereHas('tenantContract', function ($query) use($tenant_code){
            $query->whereHas('tenant', function ($query) use($tenant_code){
              return $query->where('tenant_code','ilike', '%'.$tenant_code.'%');
            });
          });
          return $query;                     
        })->when($tenant_contract_id , function ($query) use($tenant_contract_id){
          $query->whereHas('tenantContract', function ($query) use($tenant_contract_id){
            return $query->where('tenant_contract_no','ilike', '%'.$tenant_contract_id.'%');
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

                   if(strpos($key, 'status') !== false) 
                     $query->where($key,$val); 
                    

                    $query->where($key,'ilike','%'.$val.'%'); 
                   
                    
                }

               }                   
               
             }
        }
          
      }
      return $query;

   }
	/*
    * Bank Info
    */
  public function bankInfo(){

    return $this->belongsTo('Modules\Masters\Entities\Bank','bank_id','id');
  }
     




}
