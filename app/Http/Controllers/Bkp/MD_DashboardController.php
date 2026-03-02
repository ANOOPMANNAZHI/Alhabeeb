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
use App\User;
use DB;
use Route; 

class MD_DashboardController extends Controller
{
    
	public function buildingList()
   {
    $totalBuildingsList = Building::whereHas('building',function($query) {
$query->where('building_status',1);
})->count();

echo "<pre>";
   print_r($totalBuildingsList);
   echo "</pre>";
   die;
   }
	
	

    public function vacatedUnitsMTDCount(){


				$vacatedUnitsMTD = 
				Unit::where('unit_vaccant_status',0)
					
					->whereHas('tenantContract', function($query){
						$query
							->whereDate('tenant_contract_valid_to_date','>=', Carbon::today()->firstOfMonth())
							->whereDate('tenant_contract_valid_to_date','<=',Carbon::today())
						->where('tenant_contract_status',0)
						->doesntHave('tenantContractOld');
					})
				
				->count();	


				//old
  //   	$today = Carbon::today();//returns current day
		// $firstDay = $today->firstOfMonth(); 

		// $vacatedUnitsMTD = ViewTenantContract::tenantContract()->areFilter()
		// 	->whereDate('tenant_contract_valid_to_date','>=',$firstDay)
		// 	->whereDate('tenant_contract_valid_to_date','<=',$today)
		// 	->count();
		$vacatedUnitsMTDCount =   ['count' => $vacatedUnitsMTD];
	
		return json_encode(array($vacatedUnitsMTDCount));
	}

	public function vacatedUnitsMTDList(Request $request)
      {

	     $is_tenant_contract = true; // TO identify in view load from tenant-contract index method
	     
	     $today = Carbon::today();//returns current day
		 $firstDay = $today->firstOfMonth(); 

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
	    $units = Unit::are()->filter($request)
	     ->vaccatingUnits($request)
		 ->vacantUnitsByDays($request)
		 ->whereHas('building',function($query) {
					$query->where('management_id',1)
						-> where ('building_status',1);
				})
	  
	                    ->where('unit_status',1)
						->orderBy('unit_type_id','asc')
	                    ->sortable()
	                    ->paginate($noOfRecord);


	     $fields = [
	           'unit_code' => 'Unit Code',  
	           'building__building_name' => 'Building Name',  
	           'unit__unit_types_name' => 'Unit Types',    
	                 
	        ];
	     $request->flash();

	     $unitTypes = UnitType::active()->orderBy('id','ASC')->get();              
	     $management_id = 1;                                   
	     return view('masters::Unit.list',compact('units','fields','unitTypes','request','management_id'));
    }

    public function normalUnits(Request $request)
    {
		$noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
	    $units = Unit::are()->filter($request)
	     ->vaccatingUnits($request)
		 ->vacantUnitsByDays($request)
		 ->whereHas('building',function($query) {
					$query-> where ('building_status',1)
						->where('management_id',2);
				})
	  
	                    ->where('unit_status',1)
						->orderBy('unit_type_id','asc')
	                    ->sortable()
	                    ->paginate($noOfRecord); 


	     $fields = [
	           'unit_code' => 'Unit Code',  
	           'building__building_name' => 'Building Name',  
	           'unit__unit_types_name' => 'Unit Types',  
	                 
	        ];
	     $request->flash();

	     $unitTypes = UnitType::active()->orderBy('id','ASC')->get();              
	     $management_id = 2;                                       
		 return view('masters::Unit.list',compact('units','fields','unitTypes','request','management_id'));
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
	    ->whereDate('pdc_cancel_date','>=', Carbon::today()->firstOfMonth())
		->whereDate('pdc_cancel_date','<=',Carbon::today())
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
