<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralLedger extends Model
{   
   use Sortable , SoftDeletes; 


    protected $guarded = [];

    protected $dates = ['general_ledger_cancelled_date','general_ledger_posted_date','doc_date'];

    public $sortable = ['id','general_ledger_type','voucher_no','jv_refer_no' ,'doc_date','amount','maintenance_invoice_approval_status'];
  

    /*
    *
    *Ledger Dimension
    *
    *
    */
    public function generalLedgerDim(){

      return $this->hasMany('Modules\BackOffice\Entities\GeneralLedgerDim','general_ledger_id');
    } 
	 /*
    *
    * general_ledger_approval_status 
    *
    *
    */
	public function getGeneralLedgerApprovalStatusNameAttribute()
    {     
        switch($this->general_ledger_approval_status){
          case '0' : return 'label-primary | Draft';// Default
          case '1' : return 'label-primary | Draft';  // Unapproval
          case '2' : return 'label-warning | Pending For Approval';        
          case '3' : return 'label-warning | Pending For Draft';        
          case '4' : return 'label-success | Approved';        
          case '5' : return 'label-danger  | Reject';        
        }
       
    }
   /*
    *
    * general_ledger_status
    *
    *
    */
	public function getGeneralLedgerStatusNameAttribute()
    {     
        switch($this->general_ledger_status){ 
          case '1' : return 'label-warning | Active'; // Default
          case '2' : return 'label-success | Approved';        
          case '3' : return 'label-success | Posted';        
          case '4' : return 'label-primary | Draft';        
            
        }
       
    }

   /*
   *  pending for approval/ unapproval
   *
   */
   public function scopeApproval($query){
          
     return  $query->whereIn('general_ledger_approval_status',[2,3]);

   }

   /*
   *  ledger_type
   *
   */
   public function getLedgerTypeAttribute(){

   	  switch($this->general_ledger_type){

   	  	case 1 :  $type =  'General Ledger';
   	  			  break;	
   	  	case 2 :  $type =  'Bank Payment';
   	  	          break;
   	  	case 3 :  $type =  'Landlord Invoice';
   	  	          break;
   	  	case 4 :  $type =  'Bank Receipt';
   	  	          break;

   	  }

   	  return $type;

   }
 


   /*
    *
    *Bank
    *
    *
    */
    public function bank(){

      return $this->belongsTo('Modules\Masters\Entities\Bank','bank_id');
    } 
    /*
    *
    *Maintenance
    *
    *
    */
    public function maintenance(){

      return $this->belongsTo('Modules\Maintenance\Entities\MaintenanceInvoice','maintenance_id')->withTrashed();
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
                        return $query->where($method[1],'ilike', '%'.$val.'%');
                    });

                }else{
                  if( $key == 'status') {
                    if($val == 6 )
                      $query->where('general_ledger_approval_status',4)->where('general_ledger_status',3); 
                    elseif($val == 1) 
                      $query->where('general_ledger_approval_status',$val)->where('general_ledger_status',4);
                    else
                      $query->where('general_ledger_approval_status',$val)->Where('general_ledger_status', 2);   
                  
                  }else
          
                    $query->where($key,'ilike','%'.$val.'%'); 
                  /*  
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
                  */

                   } 
                   }
               }
             }
          
      }
      return $query;

   }

  

    
}
