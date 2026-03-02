<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ViewReceipt extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table = 'view_receipt';
	public $sortable = ['id','receipts_generation_receipt_no', 'receipts_generation_receipt_date', 'receipts_generation_amt','tenant_contract_no','receipts_generation_payment_method', 'receipts_generation_eff_from','receipts_generation_eff_to','tenant_name','tenant_code','building_name','building_no','unit_no'];
	protected $dates = ['receipts_generation_eff_from','receipts_generation_eff_to','receipts_generation_receipt_date'];
	
	public function getReceiptsGenerationPaymentMethodNameAttribute(){    

			switch($this->receipts_generation_payment_method){
			  case '1' : return 'cheque';
			  case '2' : return 'Cash';        
		  }
	} 
	/*
    * Receipt Type
    */
	public function getReceiptsGenerationTypeNameAttribute(){  
	
		switch($this->receipts_generation_type){
		  case '0' : return 'Tenant Receipt';
		  case '1' : return 'General Receipt';
		  case '2' : return 'Deposit Receipt';        
	  }
	}
	/*
    * Receipt Type
    */
	public function getReceiptsGenerationApprovalStatusNameAttribute(){  
		
		switch($this->receipts_generation_approval_status){
		  case '1' : return 'label-primary | Draft';
		  case '2' : return 'label-warning | Pending';    
		  case '3' : return 'label-success | Approved';
		  case '4' : return 'label-danger | Reject';     
		  case '5' : return 'label-warning | Pending For Draft'; 
	  }
	}
	 /*
   *   Scope Type 
   *
   **/
   public function scopeType($query,$type){
     
      switch($type){

        case 'deposit' : $query->where('receipts_generation_type', '=',2);
                         break;

        case 'general' : $query->where('receipts_generation_type', '=',1);
                         break; 
        case 'rent' : 
        default:

                        $query->where('receipts_generation_type', '=',0);
                         break;
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
     
    
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','tab','date','are_id']) as $key => $val){         

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
                   else{
                     
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
