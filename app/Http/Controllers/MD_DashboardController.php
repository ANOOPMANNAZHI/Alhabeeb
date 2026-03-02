<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Masters\Entities\Building;
use Modules\BackOffice\Entities\ViewTenantContract;
use Modules\Masters\Entities\Unit;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\Masters\Entities\UnitType;
use Modules\Sales\Entities\ViewTenantStage;
use Modules\BackOffice\Entities\Pdc;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Entities\LegalUser;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\User;
use DB;
use Route; 

class MD_DashboardController extends Controller
{
    public function buildingList()
    {
    	$totalBuildingsList = Building::get();
		
		echo "<pre>";
	    print_r($totalBuildingsList);
	    echo "</pre>";
	    die;
    }

    public function vacatedUnitsMTDCount(){
		$lastdate = Carbon::today()->subDays(365);

		// 		$vacatedUnitsMTD = 
		// 		Unit::where('unit_vaccant_status',0)
		// 			->whereHas('tenantContract', function($query){
		// 				$query
		// 					->whereDate('tenant_contract_valid_to_date','>=',Carbon::today()->subDays(365))
		// 					->whereDate('tenant_contract_valid_to_date','<=',Carbon::today())
		// 				->where('tenant_contract_status',0)
		// 				->doesntHave('tenantContractOld');
		// 			})
				
		// 		->count();	

		// $vacatedUnitsMTDCount =   ['count' => $vacatedUnitsMTD];


        $currentYear = Carbon::today()->firstOfYear();
		$endYear = Carbon::today()->endOfYear();
		$monthsFilter = CarbonPeriod::create($currentYear,'1 month',$endYear);

		foreach($monthsFilter as $eachMonth){
			$monthsData[$eachMonth->format('m')] = $eachMonth->format('Y-m');
		}
		//echo "<pre>";print_r($monthsData);exit;
		foreach($monthsData as $month => $eachMonth){
			$endDate = Carbon::createFromFormat('m',$month);

			


			// $vacated_unit_count = DB::select("select count(distinct units.id) as count from units join tenant_contracts on tenant_contracts.unit_id = units.id
			// 	where tenant_contracts.tenant_contract_status = 0 
			// 	and units.unit_vaccant_status = 0
			// 	and (tenant_contracts.tenant_contract_valid_to_date >= '".$eachMonth."-01')
			// 	and tenant_contracts.tenant_contract_valid_to_date <= '".$endDate->endOfMonth()."'");

			$vacated_unit_count = DB::select("select count(distinct units.id) as count from units
join tenant_contracts on tenant_contracts.unit_id = units.id
join termination on termination.contract_id = tenant_contracts.id
where tenant_contracts.tenant_renewal_termination_status = 8
and (termination.termination_date >= '".$eachMonth."-01')
and (termination.termination_date <= '".$endDate->endOfMonth()."')");


			

			 $vacatedunitMonthly[$endDate->format('F')] = $vacated_unit_count[0]->count;

			 

		

		}
		$monthsFilterData_renewal_requests = array_keys($vacatedunitMonthly);


	
		return json_encode($vacatedunitMonthly);
	}

	public function vacatedUnitsMTDList(Request $request,$are='')
      {

	     $is_tenant_contract = true; // TO identify in view load from tenant-contract index method
	     
	     $today = Carbon::today();//returns current day
		 $firstDay = $today->firstOfMonth(); 

		 $tenatsId = array();
		 //print_r($are);exit;
	     if($are != ''){

			
		$vacatedUnitsMTD =	DB::select("SELECT t.id FROM public.units u 
			JOIN buildings b ON u.building_id = b.id
			JOIN preferred_buildings pb ON pb.building_id = b.id
			JOIN are_buildings ab ON pb.are_building_id = ab.id AND ab.user_id = ".$are."
			JOIN tenant_contracts t ON u.id = t.unit_id
			JOIN termination ta ON t.id = ta.contract_id
			where ta.termination_date >= '".Carbon::today()->firstOfMonth()."' AND ta.termination_date <= '".Carbon::today()->lastOfMonth()."' AND ta.work_flow_processes_code = 505
			 AND ta.termination_type = 1");
		foreach($vacatedUnitsMTD  as $eachVac){
				$tenatsId[] = $eachVac->id;
		}
		$tenatsId = array_unique($tenatsId);
		 //print_r($tenatsId);exit;
	     $tenantContracts = ViewTenantContract::
			// ->areDashboardFilter($request)
		whereIn('id',$tenatsId)
			// ->whereHas('building', function ($query)use($are){      
			// 		$query->whereHas('areBuildings', function ($query)use($are){
			// 				$query->where('user_id','=', $are);
			// 		})
			// 		->whereHas('unit',function($q){
			// 				$q->whereHas('tenantContract',function($q){
			// 					$q->whereHas('terminationContract',function($q){
			// 							$q->where('termination_date' , '>=',Carbon::today()->firstOfMonth() );
			// 						 // ->where('work_flow_processes_code' , '>=',505 );
			// 								// ->where('termination_date' , '<=',Carbon::today() );
			// 					});
			// 				});
			// 		});
			// })
			// ->select('contract_id')
			// ->whereHas('tenantContract',function($q){
			// 		// $q->where('termination_date' , '>=',Carbon::today()->firstOfMonth() )
			// 		$q->select('id');
			// 		//   ->where('termination_date' , '<=',Carbon::today() );
			// })
			->areDashboardFilter($request)
			// ->directIndirect($request)
			->unregisteredContracts($request)
			->filter($request)
			->expiringContracts($request)
			->gracePeriod($request)
			->underPenalty($request)
			->tenantVacatting($request)
			->expiredContracts($request) 
			// ->whereDate('tenant_contract_valid_to_date','>=',$firstDay)
			// ->whereDate('tenant_contract_valid_to_date','<=',$today)
			->sortable()->paginate();
			// echo "<pre>";print_r($tenantContracts);exit;
	     }
	     else{
	     		$tenantContracts = ViewTenantContract::tenantContract()->areFilter()
			->areDashboardFilter($request)
			->directIndirect($request)
			->unregisteredContracts($request)
			->filter($request)
			->expiringContracts($request)
			->gracePeriod($request)
			->underPenalty($request)
			->tenantVacatting($request)
			->expiredContracts($request) 
			->whereDate('tenant_contract_valid_to_date','>=',$firstDay)
			->whereDate('tenant_contract_valid_to_date','<=',$today)
			->sortable()->paginate();
	                               
	    
	     }
	     


	     $fields = [
	     'tenant_contract_no' => 'Agreement No',                 
	     'building__building_name'      => 'Building Name',
	     'tenant_contract_status'      => 'Status',
	     ];

	     $operations = [
	     '=' => ' Is Equal To '  ,
	     '!=' => ' Is Not Equal To '  ,
	     '>' => ' Is Greater Than '  ,
	     '>=' => ' Is Greater Than Or Equal To '  ,
	     '<' => ' Is Less Than '  ,
	     '<=' => ' Is Less Than Or Equal To'  ,
	     'ilike' => ' Like '  ,
	     'ilike%...%' => ' Like%...% '  ,
	         //  'between' => ' Between '  ,   

	     ];
	      //$request->flash(); 

	     $serach_url  = route('tenant-contract.index');

	     $request->flash(); 
	     
	     if(isset($request->ajax))
	       return view('backoffice::TenantContract.contract_list_ajax',compact('tenantContracts','is_tenant_contract','request'));

	     
	     return view('backoffice::TenantContract.contract_list',compact('tenantContracts','fields','is_tenant_contract','request','serach_url','operations'));
   }

    public function comprehensiveUnits(Request $request)
    {
		$noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
	    $unitsSel = Unit::are()->filter($request)
	     ->vaccatingUnits($request)
		 ->vacantUnitsByDays($request)
		 ->whereHas('building',function($query) {
					$query->where('management_id',1)
							-> where ('building_status',1)
						-> where ('unit_status',1);
				})
	  
	                    ->orderBy('unit_type_id','asc')
	                    ->sortable();


	     $fields = [
	           'unit_code' => 'Unit Code',  
	           'building__building_name' => 'Building Name',  
	           'unit__unit_types_name' => 'Unit Types',    
	                 
	        ];
	     $request->flash();
	    
	     $unitsTypes = $unitsSel->get(); 
     	 foreach ($unitsTypes as $value) {
		     	$unit_types_sel[] = $value->unit_type_id;
		  }	
	     
	     $units = $unitsSel->paginate($noOfRecord);

	     $unit_types_arr = array_unique($unit_types_sel);
	     $unitTypes = UnitType::whereIn('id',$unit_types_arr)->orderBy('id','ASC')->get();              
	                                             
	     return view('masters::Unit.list',compact('units','fields','unitTypes','request'));
    }

    public function normalUnits(Request $request)
    {
		$noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
	    $unitsQry = Unit::are()->filter($request)
	     ->vaccatingUnits($request)
		 ->vacantUnitsByDays($request)
		 ->whereHas('building',function($query) {
					$query->where('management_id',2)
					-> where ('building_status',1)
						-> where ('unit_status',1);
				})
	  
	                    ->orderBy('unit_type_id','asc')
	                    ->sortable(); 


	     $fields = [
	           'unit_code' => 'Unit Code',  
	           'building__building_name' => 'Building Name',  
	           'unit__unit_types_name' => 'Unit Types',    
	                 
	        ];
	     $request->flash();
	     $unitTypesSel = $unitsQry->get();
	     foreach($unitTypesSel as $eachval){
	     	$unitTypesArr[] = $eachval->unit_type_id;
	     }
	     $unitTypes = array_unique($unitTypesArr);
	     $units = $unitsQry->paginate($noOfRecord);
	     $unitTypes = UnitType::whereIn('id', $unitTypes)->orderBy('id','ASC')->get();              
	                                             
	     return view('masters::Unit.list',compact('units','fields','unitTypes','request'));
    }

    public function unassignedEnquiry(Request $request,$result = array())
    {
      $enquiry_fields = [
      'sales_enquiry_no' => 'Enquiry No',
      'sales_enquiry_name' => 'Customer Name',
      'sales_email' => 'Email',
         //  'sales_building_name' => 'Building Name',
      'sales_mobile_no' => 'Mobile No',
      /*'sales_company_name' => 'Company Name',*/
      'sales_move_in_date' => 'Move In Date',   
      'created_at' => 'Enquiry Date',    
      'loc' => 'Location',  
      'employee_name' => 'Enquiry Owner',  
      'sales_referred_by' => 'Referred By',  

      ];

      
      $operations = [
      '=' => ' Is Equal To '  ,
      '!=' => ' Is Not Equal To '  ,
      '>' => ' Is Greater Than '  ,
      '>=' => ' Is Greater Than Or Equal To '  ,
      '<' => ' Is Less Than '  ,
      '<=' => ' Is Less Than Or Equal To'  ,
      'ilike' => ' Like '  ,
      'ilike%...%' => ' Like%...% ',
      ];  


      $return_flag = true;
      $request->flash();


        

      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray();
      $unassigned_list = ViewTenantStage::where('sale_work_flow_processes_code', '=', 101)->where('status', '=', 1)->select("sales_enquiry_id","sales_enquiry_no","sales_enquiry_name","sales_mobile_no","created_at","sales_enquiry_name","work_flow_processes_code as sales_work_flow", DB::raw("string_agg(distinct unit_type, ',' ORDER BY unit_type) AS unit_type","sales_note"), DB::raw("string_agg(distinct cast(loc as text), ',') AS loc"),"sales_note" )->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no")
      // ->salesUsers()
	  ->isSalesHead()
      ->filter($request)->countByDay($request);
 

 
       /* if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
          $unassigned_list->whereHas('salesUsers', function ($query) use($roles) {
                $query->where(function ($query) use($roles){
                        $query->where('user_id',null)
                              ->whereIn('role_id', $roles);
                      })
                      ->orWhere(function ($query) use($roles){
                        $query->where('user_id','>',0)
                              ->whereIn('role_id', $roles)
                              ->where('user_id','=', \Auth::user()->id);
                      })                      
                      ->where('status','=',1);                       
            });//dd($leads);
        }
        $unassigned_lists = $unassigned_list->sortable()->paginate($this->noOfRecord);
        */
        
   //   if(isset($result)){ 
   //       if(count($result) > 0){			 
			//  return array($unassigned_lists,$enquiry_fields,$operations);
		 // }
	  // }        

            //dd($unassigned_lists);
        if(($request->sort=='unit_type' || $request->sort=='loc') && isset($request->direction)){
      
            $unassigned_lists = $unassigned_list->orderBy($request->sort,$request->direction)->paginate($this->noOfRecord);///////////Refer here(07-01-2021)/////////////////////
 
        }
        else{
            // $unassigned_lists = $unassigned_list->sortable()->paginate($this->noOfRecord);
            $unassigned_lists = $unassigned_list->sortable()->paginate();
        }
        $quick_url = route('leadAssign.tenantUnassignedSearch');
        return view('sales::TenantSales.tenant_unassigned_list',compact('unassigned_lists','enquiry_fields','operations','quick_url'));
      }

    //get Bounced Cheques MTD
	public function getBouncedChequesMTD(){
	    $pdc= Pdc::areFilter()
	    ->bouncedCheques()
	    ->whereDate('pdc_check_date','>=', Carbon::today()->firstOfMonth())
		->whereDate('pdc_check_date','<=',Carbon::today())
	    ->get();
	    return view('backoffice::Pdc.bounced_cheque_list',compact('pdc'));
	}

	public function caseStatusAccordingToLawyer(Request $request){


		 $name = Route::currentRouteName();
		 $enquiry_fields = [
		 'tenantContract__tenant_contract_no' => 'Agreement No',
		 'tenantContract__tenant__tenant_name' => 'Name',
		 'building__building_name' => 'Building',
		 'unit__unit_code' => 'Unit',
		 ];


		 $operations = [
		 'ilike' => ' Is Equal To '  ,
		 '!=' => ' Is Not Equal To '  ,
		 '>' => ' Is Greater Than '  ,
		 '>=' => ' Is Greater Than Or Equal To '  ,
		 '<' => ' Is Less Than '  ,
		 '<=' => ' Is Less Than Or Equal To'  ,
		 'ilike%...%' => ' Like%...% ',
		 ];

		$user =  \Auth::user();

		$roles = $user->getRoles();
		$rolesNames = $user->getRoleNames()->toArray();

		$request->flash(); 

		$lawyerApprovals = Legal::filter($request)
		                        ->whereHas('legalUsers', function ($query)use($roles,$user) {                          
		                           $query->when(!($user->hasRole('md')) ,  function($query)use($roles){
		                             $query->where(function ($query) use($roles){
		                                $query->where('user_id',null)
		                                      ->whereIn('role_id', $roles)
											  ->where('status','=',1);
		                              })
		                              ->orWhere(function ($query) use($roles){
		                                $query->where('user_id','>',0)
		                                      ->whereIn('role_id', $roles)
		                                      ->where('user_id','=', \Auth::user()->id)
											  ->where('status','=',1);
		                              });
		                          })
		                          ->where('status','=',1);
		                          })                  
		                        ->where('legal.work_flow_processes_code', '=', 802)
		                        ->sortable()
		                        ->paginate();

		$quick_url = $route = route('lawyerApproval');

		if(isset($request->ajax))
		  return view('masters::Legal.lawyer_approval_list_ajax',compact('lawyerApprovals','request','route'));

		return view('masters::Legal.lawyer_approval_list',compact('lawyerApprovals','request','enquiry_fields','operations','name','quick_url'));

}
  
}
