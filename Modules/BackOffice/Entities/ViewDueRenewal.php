<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ViewDueRenewal extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table = 'view_renewal_due_approval';
	public $sortable = ['id','tenant_contract_start_date', 'tenant_contract_valid_to_date', 'tenant_contract_rent', 'email_count', 'building_name','tenant_name','category','tenant_contract_no'];
	protected $dates = ['tenant_contract_start_date','tenant_contract_valid_to_date'];

	 /*
    *
    * Tenant 
    */
    public function tenant(){

      return $this->belongsTo('Modules\Sales\Entities\Tenant','tenant_id');
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

			foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','fc_att','new_contract_no','tenant_contract_old_no','termination_date','referback']) as $key => $val){         

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
		return $query;

	} 

/*
    *
    * Tenant Contract Building
    */
public function building(){

	return $this->belongsTo('Modules\Masters\Entities\Building','building_id');
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
  *  Refer back
  *
  */
 public function scopeReferBack($query,$request){    
 	if(!empty($request->referback)){
 		$query->where(function ($query){
 			$query->where('refer_back_status',1);
 			return $query;
 		});
 	}
 	return $query;
 }
 /*
 *Scope Backoffice Filter
 *
 */
 public function scopeBackOfficeExecutiveFilter($query){

   	$user = \Auth::user();
   	$role_count = count($user->roles);
   	$headUser = \Auth::user()->id;

   	if($role_count == 1 && $user->hasRole('backoffice_executive')){ 
   		$query->where(function ($query){
   		$query->where('refer_back_status',1);       
   			return $query;   
   		}); 
   	}
}


   /*
    *
    * Discussion Form
    */
    public function discussion(){

      return $this->hasMany('Modules\BackOffice\Entities\DiscussionForums','tenant_contract_id')->latest();
    }



}
