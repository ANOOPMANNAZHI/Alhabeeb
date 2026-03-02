<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceInvoice extends Model
{
	use Sortable , SoftDeletes;

    protected $guarded = [];    

    public $sortable = ['id','maintenance_invoice_no','maintenance_invoice_date','vendor_id' ,'maintenance_invoice_payment_method','maintenance_invoice_refer_amt'];

    protected $dates = ['created_at','maintenance_invoice_date'];

   /*
   * Vendor 
   *
   */
   public function vendor(){
     
     return $this->belongsTo('Modules\Masters\Entities\Vendor');
     
   }

  /*
   * MaintenanceInvoiceDetails 
   *
   */
   public function maintenanceInvoiceDetails(){
     
     return $this->hasMany('Modules\Maintenance\Entities\MaintenanceInvoiceDetails');
     
   }



   /*
   *
   *  Approval Type    -- 3 - Unapproval,  2 - Approval
   *
   */
   public function getApprovalTypeAttribute(){

       switch($this->maintenance_invoice_approval_status){

        case 3:  $type = 'Unapproval';
                 break;

        case 2:  $type = 'Approval';
                 break;                 
       }

       return $type;

   }
    /*
   *
   *  Payment Method    -- 1 - Cash,  2 - Cheque
   *
   */
   public function getPaymentMethodAttribute(){


       switch($this->maintenance_invoice_payment_method){

        case 1:  $method = 'Cash';
                 break;

        case 2:  $method = 'Cheque';
                 break;                 
       }

       return $method;

   }
   /*
   *
   *  SendForApproval
   *
   */
   public function getSendForApprovalAttribute(){

     if($this->maintenance_invoice_status == 1 && ($this->maintenance_invoice_approval_status == 0 || $this->maintenance_invoice_approval_status == 5 ))
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
    if($this->maintenance_invoice_status == 2 && $this->maintenance_invoice_approval_status == 4)
        return true;
    else 
        return false;

   }

	/*
    * Maintenance Invoice Status
    */
	public function getMaintenanceInvoiceApprovalStatusNameAttribute(){  
		
		switch($this->maintenance_invoice_approval_status){
       case '0' : return 'label-primary | Draft';
		  case '1' : return 'label-primary | Draft';
		  case '2' : return 'label-warning | Pending For Approval';    
		  case '3' : return 'label-warning | Pending For Unapproval';    
		  case '4' : return 'label-success | Approved';
		  case '5' : return 'label-danger | Reject'; 
	  }
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
           
       foreach($request->except(['_token','sort','direction','page','fieldValues','curr_url','ajax','operation','logic','route']) as $key => $val){
  
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
                                
                                 $curr_logic = ($i == 0)? 'and' : $next_logic;

                                 $next_logic =  $request->logic[$keyName]; $i++;

                                 $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic); 

                               }

                              }

                                                   
                            $generalSearch = collect($generalSearch)->groupBy('field');                         
          
                  }else{ 
                  $key = $val;                  
                  $val = $request->fieldValue;
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
                        return $query->where($method[1],'ilike', '%'.$val.'%');
                    });

                }else{

                  if( $key == 'status') {
                  if($val == 6 )
                    $query->where('maintenance_invoice_approval_status',4)->where('maintenance_invoice_status',3); 
                  elseif($val == 1) // Unapprove
                    $query->where('maintenance_invoice_approval_status',$val)->where('maintenance_invoice_status',4);
                  else
                    $query->where('maintenance_invoice_approval_status',$val)->Where('maintenance_invoice_status','!=', 3); 
                  }else{
                
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
  * Invoice Approval
  *
  */
   public function scopeApproval($query){

    return  $query->whereIn('maintenance_invoice_approval_status',[2,3]);

   }
}
