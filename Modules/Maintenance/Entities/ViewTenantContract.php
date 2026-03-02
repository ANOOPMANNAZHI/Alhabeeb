<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ViewTenantContract extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table   = 'view_tenant_contract';
	public $sortable   = ['id','tenant_contract_no', 'tenant_contract_start_date', 'tenant_contract_valid_to_date','tenant_contract_rent','email_count', 'tenant_contract_status','tenant_renewal_termination_status','tenant_contract_is_reg_municipality','pdc_check','invoice_check','tenant_name','building_name','unit_code'];
	protected $dates   = ['tenant_contract_valid_to_date','tenant_contract_start_date'];
	
	/*
  *  Scope are
  *
  */
  public function scopeAreFilter($query){

    $user = \Auth::user();
    $role_count = count($user->roles);
    $headUser = \Auth::user()->id;

    if($role_count == 1 && $user->hasRole('are')){ 
       $query->whereHas('building', function ($query){       
           $query->whereHas('areBuildings', function ($query){
             $query->where('user_id','=', \Auth::user()->id);
             return $query;
           });   
          return $query;   
        }); 
    }
    elseif($role_count == 1 && $user->hasRole('are_team_lead')){
      $query->whereHas('building', function ($query)use($headUser){
          $query->whereHas('areBuildings', function ($query)use($headUser){
            $query->whereHas('user', function ($query)use($headUser){
            $query->whereHas('employee', function ($query)use($headUser){
              $query->where('head_user','=', $headUser);
            });
            });
            $query->orWhere('user_id','=', \Auth::user()->id);
            return $query;
          });
      });

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

      if($request->route()->getName() == 'tenantRenewalContract' )
         $request->merge(['contract_no' => $request->tenant_contract_old_no ]);
      

      if($request->route()->getName() == 'renewalContractApproval' )
        $request->merge(['contract_no' => $request->new_contract_no ]);



       // $request->request->remove('tenant_contract_old_no');        
     
    

     // dd($request);
    
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','fc_att','new_contract_no','tenant_contract_old_no','termination_date']) as $key => $val){         

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



                               if( !empty($request->fieldValue[$keyName]) && !empty($request->fieldValue[$keyName]) && !empty($valueName) && $valueName  != 'tenant_contract_status') {
                                 
                                  $curr_logic =  ($i == 0)? 'and' : $next_logic;
                                  $next_logic =  $request->logic[$keyName]; $i++;


                                  if($request->route()->getName() == 'tenantRenewalContract' && $valueName == 'tenant_contract_no' )
                                    continue;

                                  if( (in_array($request->route()->getName(),['renewalContractApproval','tenantRenewedContract']) !== false) && $valueName == 'tenant_contract_old_no' )
                                    continue;                                

                                  if($request->route()->getName() == 'tenantRenewalContract' && $valueName == 'tenant_contract_old_no' )
                                     $valueName = 'tenant_contract_no';


                                 
                                  if($request->operation[$keyName] == 'ilike%...%' ){
                                    $fieldValue = '%'.$request->fieldValue[$keyName].'%';
                                    $operation = 'ilike';
                                  }else{
                                  $operation = $request->operation[$keyName];
                                  $fieldValue = $request->fieldValue[$keyName];
                                  }
                                 // $key =  key(next($request->fieldName));
                                //  dd($key);
                                  

                                  $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic); 

                               }elseif($valueName  == 'tenant_contract_status'){
                               
                                  $curr_logic =  ($i == 0)? 'and' : $next_logic; 
                                  $next_logic =  $request->logic[$keyName]; $i++;

                                  $operation = ($request->operation[$keyName] == '!=') ? '!=' : '=';
                                  $fieldValue = $request->fieldValue[$keyName];

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
                      if(in_array($key,['contract_no','tenant_contract_old_no'])  !== false)
                       $key = 'tenant_contract_no';

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
    *
    * Tenant Contract Building
    */
    public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building','building_id');
    }

}
