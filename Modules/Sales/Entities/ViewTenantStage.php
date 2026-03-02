<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;

class ViewTenantStage extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table   = 'view_tenant_stage';
	public $sortable   = ['sales_enquiry_no','tenant_contract_no', 'tenant_contract_start_date', 'tenant_contract_valid_to_date','tenant_contract_rent','sales_enquiry_name','created_at','invoice_check','tenant_name','building_name','unit_code','unit_type','locations_name','sales_note','sales_created_at','inprogress_days','tenant_contract_duration'];
	protected $dates   = ['created_at','tenant_contract_start_date','tenant_contract_valid_to_date'];


  /*
  *
  *  count_by_day   Scope 
  *
  **/ 
  public function scopeCountByDay($query, $request){

    if(isset($request->count_by_day)){

    $query->whereDate('sales_created_at', '<=', Carbon::now()->subDays($request->count_by_day)->toDateString());

    }

    return $query;

  }
  /*
  *
  *  count_by_Month   Scope 
  *
  **/ 
  public function scopeCountByMonth($query, $request){

    if(isset($request->count_by_month)){

    $query->whereMonth('sales_created_at', '=', $request->count_by_month)
          ->whereYear('sales_created_at', '=', date('Y'));

    }

    return $query;

  }
  /*
  *
  *  count_by_assignday   Scope 
  *
  **/ 
  public function scopeCountByAssignday($query, $request){

    if(isset($request->count_by_assignday)){

    $query->whereDate('sales_created_at', '<=', Carbon::now()->subDays($request->count_by_assignday)->toDateString());

    }

    return $query;

  }


   /*
    *
    * Latest Sales 
    */
   public function sales(){
    return $this->hasMany('Modules\Sales\Entities\Sales','sales_enquiry_id','sales_enquiry_id');   
   } 




  /***  FirstCall  Self ****/
  public function scopeFirstCallSelf($query){

   $query->whereHas('sales',function($query){
      $query->when(\Auth::user()->hasRole('sales_person'),function($query){
            $query->where('work_flow_processes_code',102);
      })->when(\Auth::user()->hasRole('sales_person'),function($query){
             $query->whereIn('work_flow_processes_code',[102,101]);
          })->where('updated_by',\Auth::user()->id);
       });

  return $query;

  }




  /***  FirstCall  Other****/
  public function scopeFirstCallOther($query){

   $query->whereHas('sales',function($query){

    $query->when(\Auth::user()->hasRole('sales_person'),function($query){
            $query->where('work_flow_processes_code',102);
    })->when(\Auth::user()->hasRole('sales_person'),function($query){
             $query->whereIn('work_flow_processes_code',[102,101]);
          })->where('updated_by','!=',\Auth::user()->id);
       });

       return $query;

  }


  /**** firstCall ***/
  public function scopeFirstCall($query,$request){

      if(isset($request->firstCall)){

        switch($request->firstCall){
          case 'self' :  return $query->firstCallSelf();
          case 'other' :  return $query->firstCallOther();

        }
      }

  }

/**** Unassigned More than One Week ***/
  public function scopeIsSalesHead($query){

      $roles = \Auth::user()->getRoleNames()->toArray();
 
      if(in_array('sales_head',$roles)){

        return $query->whereDate('sales_created_at', '<=', Carbon::now()->subDays(7));
      }
      return $query;
  }


  /*
  *
  *  count_by_day   Scope 
  *
  **/ 
  public function scopeEnquiryOwner($query, $request){

    if(isset($request->count_within)){
     
      $count_within = explode('_', $request->count_within);
      $operator = $count_within[0];
      $count = $count_within[1]; 
   //dd($count);

     $query->when(($count > 0),function($query)use($count,$operator){
        $query->whereDate('sales_created_at', $operator, Carbon::now()->subDays($count)->toDateString());
            });


    }

    if(isset($request->enquiry_owner_filter)){

    $enquiry_owner = $request->enquiry_owner_filter;

    $query->when(($enquiry_owner == 'self'),function($query){
                     $query->where('enquiry_owner',\Auth::user()->id);
            })
            ->when(($enquiry_owner == 'other'),function($query){
                     $query->where('enquiry_owner','!=',\Auth::user()->id);
            });

    }

    return $query;

  }

  /****   scopeSalesUsers    **********/
  public function scopeSalesUsers($query){    

      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray();
	if(in_array('sales_head', $rolesNames) === false){
      $query->when( (\Auth::user()->hasRole(['super_admin','ceo']) == false), function($query)use($roles) {
          $query->where(function ($query) use($roles){
                   $query->where(function ($query) use($roles){
                        $query->where('user_id',null)
                              ->whereIn('role_id', $roles)->where('status','=',1);
                      })
                      ->orWhere(function ($query) use($roles){
                        $query->where('user_id','>',0)
                              ->whereIn('role_id', $roles)
                              ->where('user_id','=', \Auth::user()->id)->where('status','=',1);
                      });  
             });
           });
               
       
         return $query;

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
           //,'salesNotes__sales_notes'
        foreach($request->except(['_token','firstCall','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','fc_att','count_by_day','count_by_assignday','count_by_month','enquiry_owner_filter','count_within','type']) as $key => $val){

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

}
