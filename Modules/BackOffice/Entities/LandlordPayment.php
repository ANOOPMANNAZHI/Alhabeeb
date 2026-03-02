<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandlordPayment extends Model
{
	use Sortable;
  use SoftDeletes;
   protected $guarded = [];
    protected $table = 'landlord_payments';
    protected $dates = ['landlord_payment_date','created_at','landlord_payment_cancel_date','landlord_payment_posted_date'];
     /*
    *
    * Bank Info
    *
    *
    */

    public function bankInfo(){

    	return $this->belongsTo('Modules\Masters\Entities\Bank','bank_id','id');
    }
     /*
    *
    *Landlord Contract
    *
    *
    */
    public function landlordContract(){

      return $this->belongsTo('Modules\Sales\Entities\LandlordContract','landlord_contract_id','id');
    }
    /*
    *
    *Landlord Invoice
    *
    *
    */
    public function landlordInvoice(){

      return $this->belongsTo('Modules\BackOffice\Entities\LandlordInvoice')->withTrashed();
    }
    /*
    *
    *  Vendor
    */
    public function vendor(){

      return $this->belongsTo('Modules\Masters\Entities\Vendor');
    }  
    /*
    *
    *  Currency
    */
    public function currency(){

      return $this->belongsTo('Modules\Masters\Entities\Currency','landlord_payment_currency_id','id');
    } 
    /*
    * Payment Method
    *
    */

    public function getLandlordPaymentMethodNameAttribute()
    {     
        switch($this->landlord_payment_method){
          case '0' : return 'Bank Transfer';
          case '1' : return 'Cheque';        
        }
    }
    public function getLandlordPaymentApprovalStatusNameAttribute()
    {     
        switch($this->landlord_payment_approval_status){
          case '0' : return 'Draft';
          case '1' : return 'Draft';
          case '2' : return 'label-warning | Pending For Approval';        
          case '3' : return 'label-warning | Pending For Unapproval';        
          case '4' : return 'label-success | Approved';        
          case '5' : return 'label-danger | Reject';        
        }
       
    }
    public function scopeClosure($query, $result = array()){


    $closure =  $closure_or =  $vendor_nameq =  $vendor_name_or =  $contract_noq = $contract_no_or = $invoice_noq = $invoice_no_or =array();
        // $customer_name, $email , $phone  , $status
    $quick_search_flag = false;
    $qiuck_search  = array();

    if(count($result) > 0){
      list($closure, $closure_or,$vendor_nameq,$vendor_name_or,$contract_noq,$contract_no_or,$invoice_noq,$invoice_no_or,$qiuck_search) =  $result;

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
      $query->whereHas('landlordContract', function ($query) use($vendor_nameq){
      $query->whereHas('vendorInfo', function ($query) use($vendor_nameq){
        foreach($vendor_nameq as $vendor_name_val){                             
          $query->where('vendor_name', $vendor_name_val[1],$vendor_name_val[2]);
        }   
      });             
      });             
      return $query;                                       
    }) 
    ->when($vendor_name_or , function ($query) use($vendor_name_or){   
    $query->whereHas('landlordContract', function ($query) use($vendor_name_or){             
      $query->orwhereHas('vendorInfo', function ($query) use($vendor_name_or){
        foreach($vendor_name_or as $vendor_name_or_val){                           
          $query->where('vendor_name', $vendor_name_or_val[1],$vendor_name_or_val[2]);
        }   
      });             
      });             
      return $query;                                       
    })
    ->when($contract_noq, function ($query) use($contract_noq){                  
      $query->whereHas('landlordContract', function ($query) use($contract_noq){
        foreach($contract_noq as $contract_no_val){                             
          $query->where('landlord_contract_no', $contract_no_val[1],$contract_no_val[2]);
        }   
      });             
      return $query;                                       
    }) 

    ->when($contract_no_or , function ($query) use($contract_no_or){                
      return $query->orwhereHas('landlordContract', function ($query) use($contract_no_or){
        foreach($contract_no_or as $contract_no_or_val){                           
          return $query->where('landlord_contract_no', $contract_no_or_val[1],$contract_no_or_val[2]);
        }
        return $query;    
      });             
      return $query;                                       
    })->when($invoice_noq, function ($query) use($invoice_noq){                  
      $query->whereHas('landlordInvoice', function ($query) use($invoice_noq){
        foreach($invoice_noq as $invoice_no_val){                             
          $query->where('landlord_invoice_voucher_no', $invoice_no_val[1],$invoice_no_val[2]);
        }   
      });             
      return $query;                                       
    }) 

    ->when($invoice_no_or , function ($query) use($invoice_no_or){                
      return $query->orwhereHas('landlordInvoice', function ($query) use($invoice_no_or){
        foreach($invoice_no_or as $invoice_no_or_val){                           
          return $query->where('landlord_invoice_voucher_no', $invoice_no_or_val[1],$invoice_no_or_val[2]);
        }
        return $query;    
      });             
      return $query;                                       
    })
    ->when($quick_search_flag , function ($query) use($qiuck_search){  

      list($landlord_payment_no, $vendor_id,$landlord_contract_id, $landlord_invoice_id,$landlord_payment_date,$landlord_payment_amount,$vendor_code,$landlord_payment_approval_status) =   $qiuck_search;

      $query->when($landlord_payment_no, function ($query, $landlord_payment_no) {
        return $query->where('landlord_payment_no', 'ilike',  '%'.$landlord_payment_no.'%');
      }) 
      ->when($landlord_payment_amount, function ($query, $landlord_payment_amount) {
        return $query->where('landlord_payment_amount', 'ilike',  '%'.$landlord_payment_amount.'%');
      }) 
      ->when($landlord_payment_approval_status, function ($query, $landlord_payment_approval_status) {
        return $query->where('landlord_payment_approval_status', 'ilike',  '%'.$landlord_payment_approval_status.'%');
      })
      ->when($landlord_payment_date, function ($query) use($landlord_payment_date){                                
        return $query->whereDate('landlord_payment_date','=', $landlord_payment_date);                            
      })
      ->when($vendor_id , function ($query) use($vendor_id){
        $query->whereHas('landlordContract', function ($query) use($vendor_id){
        $query->whereHas('vendorInfo', function ($query) use($vendor_id){
          return $query->where('vendor_name','ilike', '%'.$vendor_id.'%');
        });
        });
        return $query;                     
      })
      ->when($vendor_code , function ($query) use($vendor_code){
        $query->whereHas('landlordContract', function ($query) use($vendor_code){
        $query->whereHas('vendorInfo', function ($query) use($vendor_code){
          return $query->where('vendor_code','ilike', '%'.$vendor_code.'%');
        });
        });
        return $query;                     
      })->when($landlord_invoice_id , function ($query) use($landlord_invoice_id){
        $query->whereHas('landlordInvoice', function ($query) use($landlord_invoice_id){
          return $query->where('landlord_invoice_voucher_no','ilike', '%'.$landlord_invoice_id.'%');
        });
        return $query;                     
      })
      ->when($landlord_contract_id , function ($query) use($landlord_contract_id){
        $query->whereHas('landlordContract', function ($query) use($landlord_contract_id){
          return $query->where('landlord_contract_no','ilike', '%'.$landlord_contract_id.'%');
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

      





}
