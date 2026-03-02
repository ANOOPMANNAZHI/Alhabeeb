<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ViewLandlordPayment extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table = 'view_landlord_payment';
	public $sortable = ['id','landlord_payment_no', 'landlord_payment_date', 'landlord_invoice_id','landlord_payment_invoice_amt','landlord_payment_balance_amt', 'landlord_payment_amount','landlord_payment_method','vendor_name','vendor_code','landlord_contract_name','landlord_contract_no','landlord_payment_status','landlord_payment_approval_status','building_name'];
	protected $dates = ['landlord_payment_date','created_at'];
	
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
          case '0' : return 'Cash';
          case '1' : return 'Cheque';        
        }
    }
    public function getLandlordPaymentApprovalStatusNameAttribute()
    {     
        switch($this->landlord_payment_approval_status){
          case '0' : return 'Active';
          case '1' : return 'label-primary | Draft';
          case '2' : return 'label-warning | Pending';        
          case '3' : return 'label-warning | Pending For Draft';        
          case '4' : return 'label-success | Approved';        
          case '5' : return 'label-danger  | Reject';        
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
							$query->where('landlord_payment_approval_status',4)->where('landlord_payment_status',3); 
						else
							$query->where('landlord_payment_approval_status',$val)->where('landlord_payment_status', 2); 
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
