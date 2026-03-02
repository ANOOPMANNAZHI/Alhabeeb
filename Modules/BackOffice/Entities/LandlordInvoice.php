<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandlordInvoice extends Model
{
	use Sortable , SoftDeletes;

	protected $guarded = [];

	protected $table = 'landlord_invoice';

	protected $dates = ['landlord_invoice_voucher_date','landlord_invoice_posted_date','landlord_invoice_cancelled_date','created_at'];
  
    /*
    * Landlord Contract 
    */
    public function landlordContractInfo(){

    	return $this->belongsTo('Modules\Sales\Entities\LandlordContract','landlord_contract_id','id');
    }

   /*
    * Landlord  
    */
    public function landlordInfo(){

      return $this->landlordContractInfo->vendorName();
    }




     /*
    *
    *Landlord Invoice Dimension
    *
    *
    */
    public function landlordInvoiceDimension(){

      return $this->hasMany('Modules\BackOffice\Entities\LandlordInvoiceDimension','landlord_invoice_id');
    }
    
    
    
   /*
    *
    *Landlord Invoice Dimension
    *
    *
    */
    public function landlordInvoiceDistributionBreakup(){

      return $this->hasMany('Modules\BackOffice\Entities\LandlordInvioceDistributionBreakup','landlord_invoice_id');
    }



   /*
   *
   *  SendForApproval
   *
   */
   public function getSendForApprovalAttribute(){

      if($this->landlord_invoice_status == 1 && $this->landlord_invoice_approval_status != 2)
        return true;
      else 
        return false;
   }
   /*
   *
   *  SendForUnapproval
   *
   */
   public function getSendForUnapprovalAttribute(){
    // Approved                                   Approved 
    if($this->landlord_invoice_status == 2 && $this->landlord_invoice_approval_status == 4)
        return true;
    else 
        return false;

   }


   /*
   *  Invoice pending for approval/ unapproval
   *
   */
   public function scopeApproval($query){
          
     return  $query->whereIn('landlord_invoice_approval_status',[2,3]);

   }


   

 /*
   *
   *  Filter 
   *
   */
   public function scopeFilter($query, $request){

 //   dd($request);

      if(isset($request)){ 

      $fieldNameFlag = false;  
      $generalSearch = array();           
           
         foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route']) as $key => $val){
             
           if($key == 'fieldValue' || $key == 'fieldValues' )
              continue;

           if($key == 'fieldName' && $fieldNameFlag == true) 
             continue;
             
             
             

             if(!empty($val)){ 
             
             
             
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
                                 // $key =  key(next($request->fieldName));
                                //  dd($key);
                                  if ($i == 0)
                                    $curr_logic = 'and';
                                  else
                                    $curr_logic = $next_logic;

                                  $next_logic =  $request->logic[$keyName]; $i++;

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

                            $query->whereHas($method[0], function ($query) use($method,$searchVal){

                                 foreach($searchVal as $subSearchVal ){
                                  if($subSearchVal['logic'] == 'and' ){

                                    if($subSearchVal['operation'] ==  'ilike%...%')
                                      $query->where($method[1],'ilike','%'.$subSearchVal['fieldValue'].'%');
                                    else
                                      $query->where($method[1],$subSearchVal['operation'],$subSearchVal['fieldValue']); 

                                  }else{
                                    if($subSearchVal['operation'] ==  'ilike%...%')
                                      $query->orWhere($method[1],'ilike','%'.$subSearchVal['fieldValue'].'%');
                                    else
                                      $query->orWhere($method[1],$subSearchVal['operation'],$subSearchVal['fieldValue']);
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

                  switch($key){
                   
                    case 'approval_status'  :  if($val == 'Y')
                                               $query->whereIn('general_ledger_status',[2,3]);
                                               else
                                               $query->whereNotIn('general_ledger_status',[2,3]); 
                                               break; 

                    case 'post_status'  :  if($val == 'Y')
                                               $query->where('general_ledger_status',3);
                                               else
                                               $query->where('general_ledger_status','!=',3); 
                                               break;

                    default: $query->where($key,'ilike','%'.$val.'%');
                             break;                                            
                  }               


                   } 
                   }
               }
             }
          
      }
      return $query;

   }

  
    
}
