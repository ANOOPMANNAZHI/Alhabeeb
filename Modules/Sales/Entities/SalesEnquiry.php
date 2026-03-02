<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;
class SalesEnquiry extends Model
{
	
	use Sortable;


	protected $guarded = [];

	protected $dates = ['sales_move_in_date','created_at'];

	public $sortable = ['id','sales_enquiry_no','sales_enquiry_name','sales_email','work_flow_processes_code','sales_mobile_no',
	'sales_company_name','sales_mode_id','created_at','sales_note','assigned_person'];



	/*
	*  unitTypes
	*/
	public function unitTypes() {
		return $this->belongsToMany('\Modules\Masters\Entities\UnitType', 'preferred_unit_types','sale_enquiry_id','unit_type_id');
	}
	/*
	*  Location
	*/
	public function locations() {

		return $this->belongsToMany('\Modules\Masters\Entities\Location', 'preferred_locations','sale_enquiry_id','location_id');

	}
	
	/*
	*  Location
	*/
	public function locationsOne() {

		return $this->hasOne('\Modules\Masters\Entities\Location','\Modules\Masters\Entities\PreferredLocation','location_id','id','sale_enquiry_id')->latest();

	}
	 /*
	*  building
	*/
	public function building() {

		return $this->belongsToMany('\Modules\Masters\Entities\building','\Modules\Masters\Entities\PreferredLocation','location_id','id','sale_enquiry_id')->latest();

	}

	/*
	*  Price Ranges
	*/
	public function priceRanges() {
		return $this->belongsToMany('\Modules\Masters\Entities\PriceRange', 'preferred_price_ranges','sale_enquiry_id','price_range_id');
	}


	/*
	*  Tenant Enquiry
	*
	*/
	public function scopeTenant($query){

		return $query->where('sales_type', 1);
	}



	/*
	*  Landlord Enquiry
	*
	*/
	public function scopeLandlord($query){

		return $query->where('sales_type', 2);
	}


	/*
	*  Enquiry list for SalesPerson 
	*   
	*/
	public function scopeSalesPerson($query){

		$user = \Auth::user();
		$role_count = count($user->roles);

		if( $role_count == 1 && $user->hasRole('sales_person') ){

			$query->whereHas('salesEnquiry', function ($query) use($user){										
				$query->whereHas('salesUser', function ($query) use($user){
					return $query->where('user_id',$user->id);
				})->where('work_flow_processes_code',102);
				return $query;
			});
		}

		return  $query;

	}

    /*
    *  First Call Attend
    *
    **/
    public function scopeFcAtt($query,$request){

    	if(isset($request->fc_att)){
    		$work_flow_processes_code = $request->fc_att;

    		$query->when($work_flow_processes_code, function ($query, $work_flow_processes_code) {
    			if($work_flow_processes_code <=102)
    				$query->where('work_flow_processes_code', '<=' ,$work_flow_processes_code);
    			else
    				$query->where('work_flow_processes_code', '>=' ,$work_flow_processes_code);


    			return $query;                     
    		});
    	}

    	return $query;
    }



    /*
    *
    * Latest Sales Notes 
    */
    public function salesNotes(){

    	return $this->hasOne('Modules\Sales\Entities\Sales')
    	->latest()
    	->whereNotNull('sales_notes');  	
    }

   /*
    *
    * Latest Sales 
    */
   public function sales(){

   	return $this->hasOne('Modules\Sales\Entities\Sales')
   	->latest();  	
   }





    /*
	*
	* enquirySource
	*/
	public function enquirySource(){

		return $this->belongsTo('\Modules\Masters\Entities\EnquirySource','sales_mode_id');

	}

    /*
	*
	* tenantType
	*/
	public function tenantType(){

		return $this->belongsTo('\Modules\Masters\Entities\TenantType');

	}

    /*
	*
	* buildingType
	*/
	public function buildingType(){

		return $this->belongsTo('\Modules\Masters\Entities\BuildingType');

	}


    /*
    *
    *  sales_region_name
    */
    public function getSalesRegionNameAttribute()
    {

    	/*if($value == 1)
    		$name = 'Local';
    	else
    		$name = 'International';

    		return $name;*/
    		switch($this->sales_region){
    			case '1' : return 'Local';
    			case '2' : return 'International';        
    		}
    	}
	   /*
    *
    * Sales Enquiry
    */
	   public function salesEnquiry(){

	   	return $this->hasMany('Modules\Sales\Entities\Sales','sales_enquiry_id','id');
	   }
	 /*
    * 
    *  salesuser
    *
    */
	 public function salesUser() {

	 	return $this->hasMany('Modules\Sales\Entities\SalesUsers','sales_id');
	 }
    /*
    * Status
    *
    */
    public function enquiryStatus(){

    	return $this->belongsTo('\Modules\General\Entities\WorkFlowProcess','work_flow_processes_code','work_flow_processes_code');
    }
    /*
    *
    * Work Flow Process
    */
    public function workFlowProcess(){

    	return $this->belongsTo('Modules\General\Entities\WorkFlowProcess','work_flow_processes_code','work_flow_processes_code');
    }
	 /*
    *
    * Landlord
    */
	 public function landlordContract(){

	 	return $this->hasOne('Modules\Sales\Entities\LandlordContract','sale_enquiry_id');
	 }
	 public function salesEnquiryUser() {

	 	return $this->belongsTo('App\User','created_by');
	 }


	 public function userSearch() {

	 	return $this->belongsTo('App\User','enquiry_owner');
	 }


    /*
     *  Enquiry Status Class
     */
    public function getEnquiryStatusClassAttribute()
    {

    	$code =  $this->work_flow_processes_code; 

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
	* Where Query 
	*
	*/
	public function scopeWhereClosure($query, $closure){

		return $query->where($closure);
	}
	
	/*
	 * OrWhere Query 
	 */
	public function scopeOrWhereClosure($query, $closure){

		return $query->orWhere($closure);
	}
	/*
	 * OrWhere Query 
	 */
	public function scopeClosure($query, $result = array()){

		$closure =  $closure_or = $closure_year = $closure_or_year = $closure_date = $location =  $location_or = $enquiry_owner = $enquiry_owner_or = $assigned_person =  $sales_building = $sales_building_or = array();

		// $customer_name, $email , $phone  , $status
		$quick_search_flag = false;
		$qiuck_search  = array();
		
		if(count($result) > 0){
			list($closure, $closure_or,  $closure_year,$closure_or_year,$closure_date, $location,$location_or,$qiuck_search,$enquiry_owner_or,$enquiry_owner,$sales_building_or,$sales_building) =  $result;

			if(count($qiuck_search) > 0)
				$quick_search_flag = true; 
		}
		

		$query->when($closure, function ($query) use($closure){
			return $query->where($closure)
			;
		})
		->when($closure_or, function ($query) use($closure_or){
			return $query->orwhere($closure_or)
			;
		})  
		->when($closure_year, function ($query) use($closure_year){
			foreach($closure_year as $year_val){
				if($year_val[1] == '>' || $year_val[1] == '<'){
					$query->whereYear('sales_move_in_date',$year_val[1],$year_val[2]);
					$query->orWhereYear('sales_move_in_date','=',$year_val[2]);
				}else
				$query->whereYear('sales_move_in_date','=',$year_val[2]);

				$query->whereMonth('sales_move_in_date',$year_val[1],$year_val[3]);
			}
			return $query;
		})
		->when($closure_date, function ($query) use($closure_date){  
			foreach($closure_date as $date_val){
				$query->whereDate($date_val[0],$date_val[1],$date_val[2]);
			}
			return $query;                                       
		})
		->when($location, function ($query) use($location){ 				 
			$query->whereHas('locations', function ($query) use($location){
				foreach($location as $location_date_val){							 
					$query->where('locations_name', $location_date_val[1],$location_date_val[2]);
				}	
			});				
			return $query;                                       
		}) 
		->when($location_or , function ($query) use($location_or){ 				 
			$query->orwhereHas('locations', function ($query) use($location_or){
				foreach($location_or as $location_or_val){							
					$query->orWhere('locations_name', $location_or_val[1],$location_or_val[2]);
				}	
			});				
			return $query;                                       
		})
		->when($enquiry_owner, function ($query) use($enquiry_owner){ 				 
			$query->whereHas('userSearch', function ($query) use($enquiry_owner){
				foreach($enquiry_owner as $username){							 
					$query->where('username', $username[1],$username[2]);
				}	
			});				
			return $query;                                       
		}) 
		->when($enquiry_owner_or , function ($query) use($enquiry_owner_or){ 				 
			$query->orwhereHas('userSearch', function ($query) use($enquiry_owner_or){
				foreach($enquiry_owner_or as $username){							
					$query->orWhere('username', $username[1],$username[2]);
				}	
			});				
			return $query;                                       
		})
		->when($sales_building, function ($query) use($sales_building){ 				 
			$query->whereHas('landlordContract', function ($query) use($sales_building){
				$query->whereHas('buildingInfo', function ($query) use($sales_building){

					foreach($sales_building as $building_name){							 
						$query->where('building_name', $building_name[1],$building_name[2]);
					}	

				});				
			});				
			return $query;                                       
		}) 

		->when($sales_building_or , function ($query) use($sales_building_or){ 				 
			$query->orwhereHas('landlordContract', function ($query) use($sales_building_or){
				$query->orwhereHas('buildingInfo', function ($query) use($sales_building_or){

					foreach($sales_building_or as $building_name){							
						$query->orWhere('building_name', $building_name[1],$building_name[2]);
					}	

				});				
			});				
			return $query;                                       
		})


		->when($quick_search_flag , function ($query) use($qiuck_search){ 	
			 	//dd($sales_building_name);
			list($customer_name, $email , $phone  , $status,$sales_building_name, $sales_enquiry_no, $sales_note, $created_at,$assigned_person,$building_name_select,$unit_select,$duration, $start_dt,$rent,$location_quick,$unitTypes) = $qiuck_search;

			$query->when($customer_name, function ($query, $customer_name) {
				return $query->where('sales_enquiry_name','ilike', '%'.$customer_name.'%');
			})
			->when($email, function ($query, $email) {
				return $query->where('sales_email', 'ilike',  '%'.$email.'%');
			})
			->when($sales_enquiry_no, function ($query, $sales_enquiry_no) {
				return $query->where('sales_enquiry_no', 'ilike',  '%'.$sales_enquiry_no.'%');
			})
			->when($phone, function ($query, $phone) {
				return $query->where('sales_mobile_no', 'ilike',  '%'.$phone.'%');
			})  
			->when($status, function ($query, $status) {
				return $query->where('work_flow_processes_code', $status);
			})

			->when($sales_note, function ($query, $sales_note) {
				return $query->where('sales_note','ilike', '%'.$sales_note.'%');
			})
			->when($created_at, function ($query) use($created_at){  

				return $query->whereDate('created_at','=', $created_at);

			})
			->when($sales_building_name, function ($query) use($sales_building_name){

				$query->whereHas('landlordContract', function ($query) use($sales_building_name){

					$query->whereHas('buildingInfo', function ($query) use($sales_building_name){
						return $query->where('building_name','ilike', '%'.$sales_building_name.'%');
					});

				});

			})
			->when($assigned_person , function ($query) use($assigned_person){

				$query->whereHas('assignedPersonTest', function ($query) use($assigned_person){
					return $query->where('users.username','ilike', '%'.$assigned_person.'%');
				});
				return $query;                     
			})->when($building_name_select, function ($query) use($building_name_select){

				$query->whereHas('tenantContracts', function ($query) use($building_name_select){

					$query->whereHas('building', function ($query) use($building_name_select){
						return $query->where('building_name','ilike', '%'.$building_name_select.'%');
					});

				});

			})->when($unit_select, function ($query) use($unit_select){

				$query->whereHas('tenantContracts', function ($query) use($unit_select){

					$query->whereHas('unit', function ($query) use($unit_select){
						return $query->where('unit_code','ilike', '%'.$unit_select.'%');
					});

				});

			})
			->when($duration, function ($query) use($duration){

				$query->whereHas('tenantContracts', function ($query) use($duration){


					return $query->where('tenant_contract_duration','ilike', '%'.$duration.'%');


				});

			})
			->when($start_dt, function ($query) use($start_dt){

				$query->whereHas('tenantContracts', function ($query) use($start_dt){


					return $query->whereDate('tenant_contract_start_date','=', $start_dt);


				});

			})
			->when($location_quick, function ($query) use($location_quick){

				$query->whereHas('locations', function ($query) use($location_quick){


					return $query->where('locations_name','ilike', '%'.$location_quick.'%');


				});

			})
			->when($unitTypes, function ($query) use($unitTypes){

				$query->whereHas('unitTypes', function ($query) use($unitTypes){


					return $query->where('unit_types_name','ilike', '%'.$unitTypes.'%');


				});

			})
			->when($rent, function ($query) use($rent){

				$query->whereHas('tenantContracts', function ($query) use($rent){


					return $query->where('tenant_contract_rent','ilike', '%'.$rent.'%');


				});

			});

			return $query;                                       
		});			   			   

		return $query; 
	}

    /*
	*  Tenant Contract
	*/
	public function tenantContracts() {
		return $this->hasMany('\Modules\Sales\Entities\TenantContract','sale_enquiry_id');
	}
	
	 /*
	*  Tenant Contract
	*/
	public function tenantContract() {
		return $this->hasOne('\Modules\Sales\Entities\TenantContract','sale_enquiry_id');
	}
	/*
    * 
    *  Enquiry Owner
    *
    */
	public function enquiryOwner() {

		return $this->belongsTo('App\User','enquiry_owner');
	}
    /*
    * 
    *  Assigned Person
    *
    */
    
    public function assignedPerson() {

    	return $this->belongsTo('App\User','assigned_person');
    }
    public function assignedPersonTest() {

    	return $this->belongsTo('App\User','assigned_person');
    }




    public function scopeTenantContractNotDirect($query){

    	$query->whereDoesntHave('tenantContracts', function($query){

    		$query->where('tennat_contract_direct_indirect_status',1);
    	});
    	return $query;
    }

    public function scopeLandlordContractNotDirect($query){

    	$query->whereDoesntHave('landlordContract', function($query){

    		$query->where('landlord_indirect_direct_status',1);
    	});
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
           //,'salesNotes__sales_notes'
   		foreach($request->except(['_token','list_by_week','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','fc_att','params','from','to']) as $key => $val){

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
   						$query->where('sales_enquiries.'.$key,'ilike','%'.$val.'%'); 
   					}

   				}                   

   			}
   		}

   	}
   	return $query;

   }

/*
* Latest Sales
*
*
*/
public function scopeLatestSales($query){

	$query->whereIn('id',function($query){
		$query->select(\DB::raw('MAX(id)'))
		->from('sales')               
		->groupBy('sales_enquiry_id');
	});
	return $query;

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
    *
    *  sales_region_name
    */
    public function getSalesTypeNameAttribute()
    {
    		switch($this->sales_type){
    			case '1' : return 'Tenant';
    			case '2' : return 'Landlord';        
    		}
    	}
	 /*
    *
    * First Call  
    */
    public function salesFirstCall(){

    	return $this->hasOne('Modules\Sales\Entities\Sales')
    	->where('work_flow_processes_code',102); 	
    }
}
