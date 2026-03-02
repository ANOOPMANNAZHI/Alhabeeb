<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class viewLandlordEnquiryList extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table   = 'view_landlord_enquiry_list';
	public $sortable   = ['sales_enquiry_no', 'cust', 'cust_no','loc','building_name','unit_code','unit_type','created_at','enquiry_flow'];
	protected $dates   = ['created_at'];
	
	 /*
   *
   *  Filter 
   *
   */
   /*
   *
   *  Filter 
   *
   */
   public function scopeFilter($query, $request){

      if(isset($request)){     

      $fieldNameFlag = false;  
      $generalSearch = array();   
           //,'salesNotes__sales_notes'
        foreach($request->except(['_token','sort','list_by_week','direction','page','curr_url','fieldValues','ajax','operation','logic','route','params','from','to','not_won_loss']) as $key => $val){

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
                                 // $key =  key(next($request->fieldName));
                                //  dd($key);
                                  if ($i == 0)
                                    $curr_logic = 'and';
                                  else
                                    $curr_logic = $next_logic;

                                  $next_logic =  $request->logic[$keyName]; $i++;

                     if($valueName == 'sales_move_in_date')
                     $fieldValue = \Carbon\Carbon::parse($fieldValue)->startOfMonth();

                                  $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic); 

                               }

                              }

                        // dd($generalSearch);                              
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

                              	if($subSearchVal['field'] == 'created_at')
                                   $query->whereDate($subSearchVal['field'],$subSearchVal['operation'] ,$subSearchVal['fieldValue']);
                                elseif($subSearchVal['operation'] ==  'ilike%...%' )
                                  $query->where($subSearchVal['field'],'ilike','%'.$subSearchVal['fieldValue'].'%');
                                else
                                  $query->where($subSearchVal['field'],$subSearchVal['operation'],$subSearchVal['fieldValue']); 

                              }else{

                              	if($subSearchVal['field'] == 'created_at')
                                   $query->orWhereDate($subSearchVal['field'],$subSearchVal['operation'] ,$subSearchVal['fieldValue']);
                                elseif($subSearchVal['operation'] ==  'ilike%...%')
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

                    $query->whereHas($method[0], function ($query) use($method,$val,$key){
                       if(count($method)>2){
                         $query->whereHas($method[1], function ($query) use($method,$val){
                           return $query->where($method[2],'ilike', '%'.$val.'%');
                         }); 
                       }else{
                       	 
                          if($key == 'salesNotes__sales_notes')
                            $query->salesNotes($val);                           
                          else
                          	$query->where($method[1],'ilike', '%'.$val.'%');

                          return $query;
                       }                                        
                       
                    });

                                  

                }else{

                   if(strpos($key, 'status') !== false) 
                      $query->where($key,$val); 
                   elseif($key == 'work_flow_processes_code' && (in_array($val, ['RJCT','104'])) ){
	                 
	                   switch($val){

	                   	case 'RJCT':  $query->whereHas('sales',function($query){
		                                   $query->where('refer_back',1)
		                                         ->where('work_flow_processes_code',104)
		                                         ->latestSales(); 
		                                   return $query;
		                   	           });
	                   	               break;

	                   	case  '104' :  $query->whereHas('sales',function($query){
		                                   $query->where('refer_back',0)
		                                         ->where('work_flow_processes_code','=',104)
		                                         ->latestSales();   
		                                   return $query;
		                   	           })->where('work_flow_processes_code','=',104);
	                   	               break; 
	                   }

                   }else
                     $query->where($key,'ilike','%'.$val.'%'); 
                }

               }                   
               
             }
        }
          
      }
      return $query;

   }
	/*
    * Status
    *
    */
    public function enquiryFlowStatus(){
       
        return $this->belongsTo('\Modules\General\Entities\WorkFlowProcess','enquiry_flow','work_flow_processes_code');
    }
    /*
     *  Enquiry Status Class
     */
    public function getEnquiryStatusClassAttribute()
    {

    	$code =  $this->enquiry_flow; 

    	switch($code){

    		 case 101:
    		 case 201:
    		           $class = 'label-danger';
    		            break;

    		 case 102:    		 
    		 case 202: $class = 'label-info';
    		            break; 
             case 103: 
             case 203:  $class = 'label-warning';
    		            break; 

    		 case 104: 
    		            $class = 'label-primary';
    		            break; 

    		 case 105:  $class = 'label-primary';
    		            break;         
    		            
    		 case 106:  $class = 'label-primary';
    		            break;
    		            
    		 case 107:  $class = 'label-primary';
    		            break; 

    		 case 108: 
    		 case 204:  $class = 'label-success';
    		            break;                                      
             
             default : $class = 'label-danger';
    		            break;
    		  

    	}


    	return $class;

    	
    }
      /*
* Scope week
*
*
*/
public function scopeWeek($query,$request){
  
  if(isset($request->list_by_week)){
    $query->whereDate('created_at', '>=', \Carbon\Carbon::now()->subDays(7)->toDateString());
    return $query;
  }
  return $query;
}
/*
* Dashboad Not Loss or Won in landlord enquiry
*
*
*/
public function scopeDasboardCall($query,$request){

  // 204 - Landlord-Won. 205- Landlord-Loss 
  if(isset($request->not_won_loss)){
    $query->where('enquiry_flow', '<', 204);
    return $query;
  }
  return $query;
}
    
}
