<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ViewLandlordRenewalUser extends Model
{
	use Sortable;
	protected $guarded 	= [];
	protected $table 	= 'view_landlord_renewal_users';
	public $sortable 	= ['old_contract','new_contract','landlord_contract_valid_from_date', 'landlord_contract_valid_to_date', 'landlord_contract_amt','building_name','vendor_name'];
	protected $dates 	= ['landlord_contract_valid_from_date','landlord_contract_valid_to_date'];

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

		foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','fc_att','termination_date']) as $key => $val){         

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
    * Old Landlord Contract
    *
    */
    public function oldLandlordContract(){

      return $this->belongsTo('Modules\Sales\Entities\LandlordContract','old_contract_id','id');
    }
    /*
    *
    * New Landlord Contract
    *
    */
    public function newLandlordContract(){

      return $this->belongsTo('Modules\Sales\Entities\LandlordContract','new_contract_id','id');
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
