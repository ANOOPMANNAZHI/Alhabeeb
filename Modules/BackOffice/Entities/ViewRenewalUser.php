<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;

class ViewRenewalUser extends Model
{
	use Sortable;
	protected $guarded 	= [];
	protected $table 	= 'view_renewal_users';
	public $sortable 	= ['renewal_id','old_contract','new_contract','tenant_contract_start_date', 'tenant_contract_valid_to_date', 'tenant_contract_rent','building_name','tenant_name','tenant_contract_muncipality_agr_no','unit_code'];
	protected $dates 	= ['tenant_contract_start_date','tenant_contract_valid_to_date'];

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
				$request->merge(['contract_no' => $request->contract_no ]);


			if($request->route()->getName() == 'renewalContractApproval' )
				$request->merge(['contract_no' => $request->contract_no ]);


		// $request->request->remove('tenant_contract_old_no');        

		// dd($request);

			foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','fc_att','termination_date','renewal_days','no_days','expdays','not_registered','type']) as $key => $val){         

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

						if(in_array($key,['new_contract_no','tenant_contract_old_no']) == true){

							if(isset($request->tenant_contract_old_no)){
								$old_contract_no = $request->tenant_contract_old_no;
								$query->whereHas('oldTenantContract',function($query) use($old_contract_no){
									$query->where('tenant_contracts.tenant_contract_no','ilike','%'.$old_contract_no.'%');

								});

							}
							if(isset($request->new_contract_no)){
								$new_contract_no = $request->new_contract_no;
								$query->whereHas('newTenantContract',function($query) use($new_contract_no){

									$query->where('tenant_contract_no','ilike','%'.$new_contract_no.'%');

								});

							}
						}
						else{

							if(in_array($key,['contract_no','tenant_contract_old_no'])  !== false)
								$key = 'tenant_contract_no';
							$query->where($key,'ilike','%'.$val.'%'); 
						}

					}                   

				}
			}

		}
		return $query;

	} 
	/*
    *
    * Old Tenant Contract
    *
    */
	public function oldTenantContract(){

		return $this->belongsTo('Modules\Sales\Entities\TenantContract','old_contract_id','id');
	}
    /*
    *
    * New Tenant Contract
    *
    */
    public function newTenantContract(){

    	return $this->belongsTo('Modules\Sales\Entities\TenantContract','new_contract_id','id');
    }
/*
*
*Pending approvals from HOD
*
*/
public function scopePendingApprovals($query,$request){  
	$renewalDays = $request->renewal_days;
	$nextDays = Carbon::today()->subDays($renewalDays);
	if(!empty($request->renewal_days)){
		$query->where(function ($query)use($nextDays){
			$query->whereDate('created_at','<=',$nextDays);
			return $query;
		});	
	}
	return $query;
}
 /*
  *  Scope are
  *
  */
  public function scopeAreFilter($query){

    $user = \Auth::user();
    $role_count = count($user->roles);
    $headUser = \Auth::user()->id;

    if($role_count >= 1 && $user->hasRole('are')){ 
       $query->whereHas('oldTenantContract', function ($query){       
       $query->whereHas('building', function ($query){       
           $query->whereHas('areBuildings', function ($query){
             $query->where('user_id','=', \Auth::user()->id);
             return $query;
         });   
           return $query;   
       }); 
   }); 
   }
   elseif($role_count == 1 && $user->hasRole('are_team_lead')){
      $query->whereHas('oldTenantContract', function ($query)use($headUser){
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
      });

  }

  return $query;
}

/*
*
*Contracts renewed in system – But not registered -after 5 days
*
*/
public function scopeNotRegisteredInMunicipality($query,$request){  
   
    if(isset($request->not_registered)){
    	$query->where('tenant_contract_is_reg_municipality','!=',1);
        return $query;
    }



	$noDays = $request->no_days;
	$nextDays = Carbon::today()->subDays($noDays);
	if(!empty($request->no_days)){
		$query->where(function ($query)use($noDays){
		  $query->whereDate('created_at', '<=', Carbon::now()->subDays($noDays)->toDateString())
			   ->where('tenant_contract_is_reg_municipality','!=',1);
			return $query;
		});	
	}
	return $query;
}
/*
*
*Renewal users 
*
*/
	public function scopeRenewalUsers($query){  
		
		$roles = \Auth::user()->getRoles();

		if(!\Auth::user()->hasRole('super_admin'))  {  

         $query->where(function ($query) use($roles){
        
		  $query->where(function ($query) use($roles){
			       $query->where('user_id',null)
			             ->whereIn('role_id', $roles);
			      })
			      ->orWhere(function ($query) use($roles){
			        $query->where('user_id','>',0)
			              ->whereIn('role_id', $roles)
			              ->where('user_id','=', \Auth::user()->id);
			      });

			})->where('status',1)
	          ->where('user_status',1);	
		}

		return $query;
	}
}
