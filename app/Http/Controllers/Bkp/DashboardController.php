<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sales\Entities\TenantContract;
use Modules\BackOffice\Entities\ViewTenantContract;
use Modules\BackOffice\Entities\ViewRenewalUser;
use Modules\BackOffice\Entities\ViewTenantTermination;
use Modules\Maintenance\Entities\ViewComplaintAssigned;
use Modules\Maintenance\Entities\ViewComplaintReview;
use Modules\Maintenance\Entities\ViewComplaintSubassigned;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Entities\Renewal;
use Modules\BackOffice\Entities\Termination;
use Modules\Sales\Entities\Sales;
use Modules\Sales\Entities\ViewTenantStage;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Entities\Key;
use Modules\BackOffice\Entities\ViewDueRenewal;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Entities\Unit;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Sales\Entities\LandlordContract;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\AreBuildingAssign;
use Modules\Sales\Entities\ViewEnquiry;
use Modules\Masters\Entities\EnquirySource;
use Carbon\Carbon;
use App\User;
use DB;

//new updation(26-12-2020)-starts
use Modules\Masters\Entities\Building;
//new updation(26-12-2020)-starts

class DashboardController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
		$this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
	}

	public function totalReceivables(Request $request){
		$days = $request->days;
		$days = $days-1;
		$nextDays = Carbon::today()->addDays($days);
		$today = date('Y-m').'-01';
		
		$receivableSum = ViewTenantContract::receivables($request)->areFilter()->where('tenant_contract_status',1)
			->select(DB::raw("SUM(extract(month from age(CAST('$nextDays' AS DATE),
				(CASE WHEN tenant_contract_last_paid_date  is NOT NULL THEN tenant_contract_last_paid_date
			     WHEN tenant_contract_effective_date  is NOT NULL THEN tenant_contract_effective_date
			END)
			)) * tenant_contract_rent) as recievable"))->first();
		
		//$sumOfReceiptAmount = numberFormat($receivableSum->recievable);
		$sumOfReceiptAmount = round($receivableSum->recievable);
		$totalReceivableAmt = ['amount' => $sumOfReceiptAmount];
		return json_encode(array($totalReceivableAmt));
	}
	
	public function receivables(Request $request){
$is_tenant_contract = true; // TO identify in view load from tenant-contract index method
$days = $request->days;
$days = $days-1;
$nextDays = Carbon::today()->addDays($days);
$today = date('Y-m-d');
$tenantContracts = ViewTenantContract::receivables($request)->areFilter()->where('tenant_contract_status',1)
->filter($request)                      
->sortable()
->paginate($this->noOfRecord);  
//dd($tenantContracts);    

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
return view('backoffice::TenantContract.receivable_contract_list_ajax',compact('tenantContracts','is_tenant_contract','request','is_tenant_contract','nextDays'));


return view('backoffice::TenantContract.receivable_contract_list',compact('tenantContracts','fields','request','serach_url','operations','nextDays','is_tenant_contract'));
}




	/*
	*contracts Expiring
	*/
	public function contractsExpiring(Request $request){
		$expdays = $request->expdays;
		$tenantContracts = ViewTenantContract::tenantContract()->areFilter()->expiringContracts($request)->count();
		$expiringContractCount =   ['count' => $tenantContracts];
		return json_encode(array($expiringContractCount));
	}
	public function UnregisteredContracts(Request $request){
		
		$tenantContracts = TenantContract::filter($request) 
		->unregisteredContracts()
		->sortable()
		->paginate($this->noOfRecord);
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

		$serach_url  = route('tenant-contract.index');

		$request->flash(); 

		if(isset($request->ajax))
			return view('backoffice::TenantContract.contract_list_ajax',compact('tenantContracts','is_tenant_contract','request'));


		return view('backoffice::TenantContract.contract_list',compact('tenantContracts','fields','is_tenant_contract','request','serach_url','operations'));
	}
	

	
	
	
	
	
	
	//Cases Completed – completed the job and awaiting closure -
	public function completedCasesCount(){
		$completedCasesCount = ComplaintEnquiry::whereDoesntHave('complaintTicketsAll', function ($query){
			$query->where('ticket_status','<',4)
			->where('work_flow_processes_code', '<', 706);
		})
		->whereHas('complaintTicketsAll', function ($query){
			$query->assignedUsers()
			->whereHas('complaintProcessAll', function ($query){
				$query->complaintUsers();                       
			}) ;                       
		})                        
		->where('complaint_status',1)->count();
		return $completedCasesCount;
	}

	



/*
*
*
*  CEO  Dashboard Data
*
*
**/
public function  ceoDashboard(Request $request,$dashboard = false){

	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}


	/******    Won Deals – Current month   ************************/
	if($count_type == 'wonDealsCurrentMonth' || $dashboard == true){

		$wonDealsCurrentMonth = \Modules\Sales\Entities\ViewTenantStage::
		where('work_flow_processes_code', '=',108)
		->where('sale_work_flow_processes_code','=',108)
		->where('status','=',1)
		->where('sales_enquiry_direct_contract','=',1)	
		->select("sales_enquiry_id")	     
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name","employee_name","tenant_contract_duration","unit_type_name","tenant_name","unit_usage")			     
		->when(( $dashboard == true),function($query)use($count){
			$query->whereYear('sales_created_at', '=', date('Y'))
			->whereMonth('sales_created_at', '=',date('m'));
		})
		->get()->count();    

	}


	


	/******    Won Deals – Last month   ************************/
	if($count_type == 'wonDealsLastMonth' || $dashboard == true){


		$lastMonth =  date("m",strtotime('-1 month')); 

		$wonDealsLastMonth = \Modules\Sales\Entities\ViewTenantStage::
		where('work_flow_processes_code', '=',108)
		->where('sale_work_flow_processes_code','=',108)
		->where('status','=',1)
		->where('sales_enquiry_direct_contract','=',1)	
		->select("sales_enquiry_id")	     
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name","employee_name","tenant_contract_duration","unit_type_name","tenant_name","unit_usage")			     
		->when(( $dashboard == true),function($query)use($count,$lastMonth){
			$query->whereYear('sales_created_at', '=', date('Y'))
			->whereMonth('sales_created_at', '=',$lastMonth);
		})
		->get()->count();    

	}


	/******    Approvals pending with Sales Manager   ************************/
	if($count_type == 'approvalsPendingSalesManager' || $dashboard == true){


		$approvalsPendingSalesManager = \Modules\Sales\Entities\ViewTenantStage::
		where('work_flow_processes_code', '=',105)
		->where('sale_work_flow_processes_code','=',105)
		->where('status','=',1)
		->select("sales_enquiry_id")	     
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","employee_name","tenant_contract_duration")
		->get()->count();
	}


	/*****  Approvals pending with Back Office Manager *******************/
	if($count_type == 'approvalsPendingBackOfficeManager' || $dashboard == true){

		$approvalsPendingBackOfficeManager = \Modules\BackOffice\Entities\ViewTenantContract::revoke()
		->get()->count();   
	}

	/*****  Legal Cases *******************/
	if($count_type == 'legalCases' || $dashboard == true){

$user  = \Auth::user();
$roles = $user->getRoles();
$rolesNames = $user->getRoleNames()->toArray();
$legalCases = Legal::filter($request)
->whereHas('legalNotes', function ($query) {
$query->where('legal_notes_status','!=',0);
})->legalUsers()                
->where('legal.work_flow_processes_code', '=', 803)->count();


}


	/*****  Maintenance *******************/
	if($count_type == 'maintenance' || $dashboard == true){

		$maintenance = \Modules\Maintenance\Entities\ComplaintEnquiry::
		where('complaint_status','!=',2)
		->whereDate('created_at', '<=', Carbon::now()->subDays(7)->toDateString())
		->get()->count();                     

	}

	/*****  Expired contracts *******************/
if($count_type == 'expiredContracts' || $dashboard == true){

$expiredContracts = \Modules\BackOffice\Entities\ViewTenantContract::
tenantContract()->directIndirect($request)->where('tenant_contract_status', 1)
->whereDate('tenant_contract_valid_to_date', '<', Carbon::now()->toDateString())
->select('tenant_contract_no')
->get()
->count();

}

	/************  Receivables As on a specific date ARE wise ************/
	if($count_type == 'receivables' || $dashboard == true){


$areUser = \Modules\Masters\Entities\AreBuildingAssign::   with('areUser.employee')
                             ->whereHas('buildingNamesExist')
                             ->first()->user_id;

                             

$nextDays = Carbon::today();
$receivable_date = date('Y-m-d');
if(isset($request->count_val)){
$areUser = $request->count_val;
$receivable_date = $request->receivable_date;
}

$today = date('Y-m-d');
$receivableSum = \Modules\BackOffice\Entities\ViewTenantContract::
whereDate('tenant_contract_effective_date','<=',$today)
->whereDate('tenant_contract_valid_to_date','>=',$today)
->whereHas('building', function ($query)use($areUser){      
$query->whereHas('areBuildings', function ($query)use($areUser){
$query->where('user_id','=', $areUser);
});
})
->where('tenant_contract_status',1)
->select(DB::raw("SUM(extract(month from age(CAST('$nextDays' AS DATE),
(CASE WHEN tenant_contract_last_paid_date  is NOT NULL THEN tenant_contract_last_paid_date
    WHEN tenant_contract_effective_date  is NOT NULL THEN tenant_contract_effective_date
END)
)) * tenant_contract_rent) as recievable"))->first();

//$receivables = numberFormat($receivableSum->recievable);
$receivables = round($receivableSum->recievable);



if(!$dashboard)
return ['count' => $receivables, 'href' => route('receivables').'?areUser_id='.$areUser.'&receivable_date='.$receivable_date ];

}





	/*******************  Vacancy Loss    **************************/
	if($count_type == 'vacancyLoss' || $dashboard == true){
		$toDateVal   = date('Y-m-d 23:59:59');
		
			
		$year 			= date('Y');
		$fromDateVal 	= $year.'-01-01 00:00:00';
		$vacantLoss = DB::select('select public."vacancy_loss_date_sum"(?)',[$fromDateVal]);		
		$vacantVal =  isset($vacantLoss[0]->vacancy_loss_date_sum)?$vacantLoss[0]->vacancy_loss_date_sum:0;
		$vacantRent = numberFormat($vacantVal);
		//dd($vacantLoss);
		//$vacantRent = round($vacantRent);
		if(!$dashboard)
			return ['count' => $vacantRent, 'href' => route('vacancy-loss-list',[$count])];
	}





	/*******************  Vacant units  **************************/
	if($count_type == 'vacantUnits' || $dashboard == true){

		$days = [30,60,90];

		foreach ($days as $day) {
////////////comprehensive  /////////////////////////
			$comprehensive[$day] = 
			Unit::where('unit_vaccant_status',0)
			->whereHas('building',function($query) {
				$query->where('management_id',1);
			})
			->orWhere(function($query)use($day) {		             
				$query->where('unit_vaccant_status',0)
				->whereHas('tenantContract', function($query) use($day){
					$query->whereDate('tenant_contract_valid_to_date', '<=', Carbon::now()->subDays($day)->toDateString())
					->where('tenant_contract_status',0)
					->doesntHave('tenantContractOld');
				});
			})
			->count();	

/////////////////normal //////////////////////////////////
			$normal[$day] = 
			Unit::where('unit_vaccant_status',0)
			->whereHas('building',function($query) {
				$query->where('management_id',2);
			})
			->orWhere(function($query)use($day) {
				$query->where('unit_vaccant_status',0)
				->whereHas('tenantContract', function($query) use($day){
					$query->whereDate('tenant_contract_valid_to_date', '<=', Carbon::now()->subDays($day)->toDateString())
					->where('tenant_contract_status',0)
					->doesntHave('tenantContractOld');
				});
			})->count();

		}


		$vacantUnits['comprehensive']  = $comprehensive;
		$vacantUnits['normal']  = $normal;	 

	}




	return [
	'wonDealsCurrentMonth' => $wonDealsCurrentMonth,
	'wonDealsLastMonth' => $wonDealsLastMonth,
	'approvalsPendingSalesManager' => $approvalsPendingSalesManager,
	'approvalsPendingBackOfficeManager' =>     	
	$approvalsPendingBackOfficeManager,
	'legalCases' => $legalCases,
	'maintenance' => $maintenance,
	'expiredContracts' => $expiredContracts,
	'receivables' => $receivables,
	'vacancyLoss' => $vacantRent,
	'vacantUnits' => $vacantUnits,
	];



}




/*
*
*
*  Sales Person   Dashboard Data
*
*
**/
public function  salesPersonDashboard(Request $request,$dashboard = false){


	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}

	/*************        unattenedEnquiry        ****************************/
	if($count_type == 'unattenedEnquiry' || $dashboard == true){

		$unattened_list = \Modules\Sales\Entities\ViewTenantStage::
		where('sale_work_flow_processes_code', '=', 102)
		->where('work_flow_processes_code', '=', 102)
		->where('status', '=', 1)
		->select('sales_enquiry_id')		     
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no","employee_name")
		->when(($count > 0),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays($count)->toDateString());
		})
		->when(( $dashboard == true),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays(3)->toDateString());
		})->salesUsers()
		->get()->count();




		if(isset($request->count_val)){         	
			$count_query = 'count_by_day='.$count;
		}		      

		if(!$dashboard)
			return ['count' => $unattened_list, 'href' => route('leadAssign.assignedList').'?'.$count_query ];

	}

//**************  inprogressEnquiry          ***************************//
	if($count_type == 'inprogressEnquiry' || $dashboard == true){

		$firstCall = $request->firstCall;
		$operator = '';

		if($dashboard == false && isset($request->count_val) ){
			$count = explode('_', $count);
			$operator = $count[0];
 	$count = $count[1];   // dd($operator);
 }

 $inprogress_list = \Modules\Sales\Entities\ViewTenantStage::
 where('sale_work_flow_processes_code', '=', 103)
 ->where('work_flow_processes_code', '=', 103)
 ->where('status', '=', 1)
 ->select('sales_enquiry_id')		     
 ->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_notes","sales_mobile_no","employee_name","inprogress_days")
 ->when(($count > 0),function($query)use($count,$operator){
 	$query->whereDate('sales_created_at', $operator, Carbon::now()->subDays($count)->toDateString());
 })
 ->when(($firstCall == 'self'),function($query){
 	$query->firstCallSelf();
 })
 ->when(($firstCall == 'other'),function($query)use($count){
 	$query->firstCallOther();
 })			      
 ->when(( $dashboard == true ),function($query)use($count){
 	$query->whereDate('sales_created_at', '>=', Carbon::now()->subDays(7)->toDateString())
 	->firstCallSelf();
                        //->where('enquiry_owner',\Auth::user()->id);
 })			      
 ->salesUsers()
 ->get()->count();


 if(isset($request->count_val)){         	
 	$count_query = 'count_within='.$operator.'_'.$count;
 }





 if(!$dashboard)
 	return ['count' => $inprogress_list, 'href' => route('inprogressList').'?'.$count_query .'&firstCall='.$firstCall];

}

//************          preapprovalEnquiry       ***********************/
if($count_type == 'preapprovalEnquiry' || $dashboard == true){

	$preapprovalEnquiry = \Modules\Sales\Entities\ViewTenantStage::
	where('work_flow_processes_code', '=',105)
	->where('sale_work_flow_processes_code','=',105)
	->where('status','=',1)
	->select('sales_enquiry_id')		     
	->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","employee_name","tenant_contract_duration")			     
	->salesUsers()
	->get()->count();



	if(isset($request->count_val)){         	
		$count_query = 'count_by_day='.$count;
	}		      

	if(!$dashboard)
		return ['count' => $preapprovalEnquiry, 'href' => route('preliminaryApprovalList').'?'.$count_query ];

}


//******************   approvedEnquiry  ***************************//
if($count_type == 'approvedEnquiry' || $dashboard == true){

	$approvedEnquiry = \Modules\Sales\Entities\ViewTenantStage::
	where('work_flow_processes_code', '=', 106)
	->where('tennat_contract_direct_indirect_status', '=', 0)
	->select('sales_enquiry_id')		     
	->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name")			     
	->salesUsers()
	->get()->count();
//dd($approvedEnquiry);
	if(isset($request->count_val)){         	
		$count_query = 'count_by_day='.$count;
	}		      

	if(!$dashboard)
		return ['count' => $approvedEnquiry, 'href' => route('finalDocumentationList')];

}



/***************** wonEnquiry     **************************/

if($count_type == 'wonEnquiry' || $dashboard == true){ 


	$won_list = \Modules\Sales\Entities\ViewTenantStage::
	where('work_flow_processes_code', '=',108)
	->where('sale_work_flow_processes_code','=',108)
	->where('status','=',1)
	->where('sales_enquiry_direct_contract','=',1)
	->select("sales_enquiry_id")
	->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name","employee_name","tenant_contract_duration","unit_type_name","tenant_name","unit_usage")
	->when(($count > 0),function($query)use($count){
		$query->whereMonth('sales_created_at', '=', date($count));
	})
	->whereYear('sales_created_at', '=', date('Y'))

	->when(( $dashboard == true),function($query)use($count){
		$query->whereMonth('sales_created_at', '=',date('m'));
	})->salesUsers()
	->get()->count();	


	if(isset($request->count_val))        	
		$count_query = 'count_by_month='.$count;


	if(!$dashboard)
		return ['count' => $won_list, 'href' => route('wonList').'?'.$count_query ];

}



/********  vacantUnits   ****************/

if($count_type == 'vacantUnits' || $dashboard == true){


$vacantUnits =DB::table('units')
    ->leftJoin('buildings','units.building_id','=','buildings.id')
    ->select (DB::raw('units.unit_id'))
    ->where('unit_vaccant_status',0)->where('unit_status',1)->where('building_status',1)
   /* ->get();*/
->count();

$count_query = 'unit_vaccant_status='.$count.'&unit_status=1'.$count.'&building_status=1';


if(!$dashboard)
return ['count' => $vacantUnits, 'href' => route('unit.index') ];

    }





/*****************   partial_pdc     **********************/
if($count_type == 'partial_pdc' || $dashboard == true){ 

	$user = \Auth::user();
	$roles = \Auth::user()->getRoles();
	$userId = $user->id;
	
	/*
	$partial_pdc =  
	\Modules\BackOffice\Entities\ViewTenantContract::whereIn('pdc_check',[2])
	->where('tenant_contract_status',1)
	->get()->count();
	*/
	$partial_pdc =  
	\Modules\BackOffice\Entities\ViewTenantContract::
	when(($user->hasAnyRole(['sales_person'])),  function($query)use($userId) {
			$query->where('assigned_person',$userId);
	})->whereIn('pdc_check',[2])
	->where('tenant_contract_status',1)
	->get()->count();

	if(!$dashboard)
		return ['count' => $partial_pdc, 'href' => route('tenant-contract.index').'?'.$count_query ];

}




return [
'unattened_list' => $unattened_list,	           
'inprogress_list' => $inprogress_list,	           
'preapprovalEnquiry' => $preapprovalEnquiry,	           
'approvedEnquiry' => $approvedEnquiry,	           
'wonEnquiry' => $won_list,	           
'vacantUnits' => $vacantUnits,	           
'partial_pdc' => $partial_pdc,	           
];

}





/*
*
*
*  Sales Coordinator   Dashboard Data
*
*
**/
public function  salesCoordinatorDashboard(Request $request,$dashboard = false){


	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}
	/*******  unassignedEnquiry     **************/

	if($count_type == 'unassignedEnquiry' || $dashboard == true){

		$unassigned_list = \Modules\Sales\Entities\ViewTenantStage::
		where('sale_work_flow_processes_code', '=', 101)
		->where('status', '=', 1)
		->select('sales_enquiry_id')		     
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no")
		->when(($count > 0),function($query)use($count){
			$query->whereDate('created_at', '<=', Carbon::now()->subDays($count)->toDateString());
		})
		->when(( $dashboard == true),function($query)use($count){
			$query->whereDate('created_at', '<=', Carbon::now()->subDays(1)->toDateString());
		})->salesUsers()
		->get()->count();

		if(isset($request->count_val)){         	
			$count_query = 'count_by_day='.$count;
		}		      

		if(!$dashboard)
			return ['count' => $unassigned_list, 'href' => route('leadAssign.index').'?'.$count_query ];

	}

	/*************   assignedEnquiry     *****************/
	if($count_type == 'assignedEnquiry' || $dashboard == true){ 

		$assigned_lists = \Modules\Sales\Entities\ViewTenantStage::
		where('sale_work_flow_processes_code', '=', 102)
		->where('work_flow_processes_code', '=', 102)
		->where('status', '=', 1)
		->select("sales_enquiry_id")
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no","employee_name")
		->when(($count > 0),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays($count)->toDateString());
		})
		->when(( $dashboard == true),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays(3)->toDateString());
		})
		->salesUsers()
		->get()->count();	


		if(isset($request->count_val))        	
			$count_query = 'count_by_assignday='.$count;


		if(!$dashboard)
			return ['count' => $assigned_lists, 'href' => route('leadAssign.assignedList').'?'.$count_query ];


	}

	/************    inprogressEnquiry     ************************/

	if($count_type == 'inprogressEnquiry' || $dashboard == true){ 

		$inprogress_list = \Modules\Sales\Entities\ViewTenantStage::
		where('sale_work_flow_processes_code', '=', 103)
		->where('work_flow_processes_code', '=', 103)
		->where('status', '=', 1)
		->select("sales_enquiry_id")
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_notes","sales_mobile_no","employee_name","inprogress_days")
		->when(($count > 0),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays($count)->toDateString());
		})
		->when(( $dashboard == true),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays(5)->toDateString());
		})
		->salesUsers()
		->get()->count();	


		if(isset($request->count_val))        	
			$count_query = 'count_by_assignday='.$count;


		if(!$dashboard)
			return ['count' => $inprogress_list, 'href' => route('inprogressList').'?'.$count_query ];

	}



	/***************** wonEnquiry     **************************/

	if($count_type == 'wonEnquiry' || $dashboard == true){ 


		$won_list = \Modules\Sales\Entities\ViewTenantStage::
		where('work_flow_processes_code', '=',108)
		->where('sale_work_flow_processes_code','=',108)
		->where('status','=',1)
		->where('sales_enquiry_direct_contract','=',1)
		->select("sales_enquiry_id")
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name","employee_name","tenant_contract_duration","unit_type_name","tenant_name","unit_usage")
		->when(($count > 0),function($query)use($count){
			$query->whereMonth('sales_created_at', '=', date($count));
		})
		->whereYear('sales_created_at', '=', date('Y'))

		->when(( $dashboard == true),function($query)use($count){
			$query->whereMonth('sales_created_at', '=',date('m'));
		})
					    // ->salesUsers()
		->get()->count();	


		if(isset($request->count_val))        	
			$count_query = 'count_by_month='.$count;


		if(!$dashboard)
			return ['count' => $won_list, 'href' => route('wonList').'?'.$count_query ];

	}

	/*********** allOpenEnquiry     ***************/
	if($count_type == 'allOpenEnquiry' || $dashboard == true){ 


		$allOpenEnquiry = \Modules\Sales\Entities\ViewEnquiry::
		where('sales_enquiry_direct_contract', '=',1)
		->where('sales_type', '=', 1)
		->where('work_flow_processes_code', '!=', 108)
		->where('work_flow_processes_code', '!=', 109)
        ->select("sales_enquiry_no")
		->groupBy("sale_enquiry_id","sales_enquiry_no","created_at","cust_no","sales_note","cust","fc_att","enquiry_flow")
		->get()
		->count();	


		if(isset($request->count_val))        	
			$count_query = 'count_by_month='.$count;


		if(!$dashboard)
			return ['count' => $allOpenEnquiry, 'href' => route('tenantEnquiries') ];

	}

	/********  vacantUnits   ****************/

	if($count_type == 'vacantUnits' || $dashboard == true){ 


		$vacantUnits =
		\Modules\Masters\Entities\Unit::where('unit_vaccant_status',0)
		->where('unit_status',1)
		->get()
		->count();

		$count_query = 'unit_vaccant_status='.$count;


		if(!$dashboard)
			return ['count' => $vacantUnits, 'href' => route('unit.index') ];

	}


	/************    selfAssignedEnquiry     ****************/

	if($count_type == 'selfAssignedEnquiry' || $dashboard == true){ 


		$selfAssignedEnquiry = \Modules\Sales\Entities\ViewTenantStage::
		where('sale_work_flow_processes_code', '=', 103)
		->where('work_flow_processes_code', '=', 103)
		->where('status', '=', 1)
		->select("sales_enquiry_id")
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_notes","sales_mobile_no","employee_name","inprogress_days")
		->firstCallSelf()
		->when(($count > 0),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays($count)->toDateString());
		})
		->when(( $dashboard == true),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays(1)->toDateString());
		})				        
		->get()->count();	

		$count_query = 'firstCall=self';

		if(isset($request->count_val))        	
			$count_query =  $count_query.'&count_by_assignday='.$count;


		if(!$dashboard)
			return ['count' => $selfAssignedEnquiry, 'href' => route('inprogressList').'?'.$count_query ];

	}
	/***********     preapproval     ******************/

	if($count_type == 'preapproval' || $dashboard == true){ 



		$preapproval =  
		\Modules\Sales\Entities\ViewTenantStage::where('work_flow_processes_code', '=',105)
		->where('sale_work_flow_processes_code','=',105)
		->where('status','=',1)
		->select("sales_enquiry_id")
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","employee_name","tenant_contract_duration")
		->salesUsers()
		->get()->count();


		if(!$dashboard)
			return ['count' => $preapproval, 'href' => route('preliminaryApprovalList') ];

	}



	/*************   lostEnquiry     **********************/ 

	if($count_type == 'lostEnquiry' || $dashboard == true){ 


		$lostEnquiry = \Modules\Sales\Entities\ViewTenantStage::
		where('work_flow_processes_code', '=',109)
		->where('sale_work_flow_processes_code','=',109)
		->where('status','=',1)
		->select("sales_enquiry_id")
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name","employee_name","tenant_contract_duration","unit_type","tenant_name","unit_usage","cust","cust_no","sales_notes")
		->when(($count > 0),function($query)use($count){
			$query->whereMonth('sales_created_at', '=', date($count));
		})
		->whereYear('sales_created_at', '=', date('Y'))

		->when(( $dashboard == true),function($query)use($count){
			$query->whereMonth('sales_created_at', '=',date('m'));
		})
		->get()->count();	



		if(isset($request->count_val))        	
			$count_query = 'count_by_month='.$count;


		if(!$dashboard)
			return ['count' => $lostEnquiry, 'href' => route('closedList').'?'.$count_query ];

	}


	/*****************   partial_pdc     **********************/
	if($count_type == 'partial_pdc' || $dashboard == true){ 


		$partial_pdc =  
		\Modules\BackOffice\Entities\ViewTenantContract::where('pdc_check',2)
		->where('tenant_contract_status',1)
		->get()->count();


		if(!$dashboard)
			return ['count' => $partial_pdc, 'href' => route('tenant-contract.index').'?'.$count_query ];

	}


	return ['unassigned_list' => $unassigned_list,
	'assigned_list'=>$assigned_lists,
	'inprogress_list'=>$inprogress_list,
	'wonEnquiry'=>$won_list,
	'allOpenEnquiry'=>$allOpenEnquiry,
	'vacantUnits'=>$vacantUnits,
	'selfAssignedEnquiry'=>$selfAssignedEnquiry,
	'preapproval'=>$preapproval,
	'lostEnquiry'=>$lostEnquiry,
	'partial_pdc'=>$partial_pdc,
	];



}


	//Keys pending with Maintenance Engineer
public function maintenanceEngineerCount(){
	$userId = User::role(['maintenance_engineer'])->pluck('id');
	$maintenanceEngineerCount = key::where('status',1)->whereIn('user_id',$userId)->count();
	return $maintenanceEngineerCount;
}

	//Assigned Complaints – Open – More than 24 hrs./48 hrs./All. 
public function openAssignedComplaintCount(){
		$dt = Carbon::now(); // Produces something like "2020-01-27 16:02:47.063656 Asia/Muscat (+04:00)"
		$dt->subHour(24); 
		// dd($dt);
		$userId = User::role(['maintenance_engineer'])->pluck('id');

		$openAssignedComplaints = ViewComplaintAssigned::where('work_flow_processes_code', '=', 702)->where('process_workflow', '=', 702)->whereIn('ticket_status',[0,1,3])->whereIn('assigned_to',$userId)->where('created_at','<=',$dt)->select(DB::raw('MAX(id) as complaint_checklist_id'),'id','complaint_enquiries_id','complaint_ticket_no','work_id','assigned_to','building_id','building_name', 'unit_code','assigned_to','concat','vendors','ticket_status','created_at')->groupBy('complaint_enquiries_id','complaint_ticket_no','id','work_id','assigned_to','building_id','building_name', 'unit_code','assigned_to','concat','vendors','ticket_status','created_at')->get()->count();
		return $openAssignedComplaints;
	}
	//Assigned Complaints – Open – More than 24 hrs./48 hrs./All. 
	public function openAssignedComplaint(Request $request){
		if($request->hours == 'all')
		{
			$openAssignedComplaints = ViewComplaintAssigned::where('work_flow_processes_code', '=', 702)->where('process_workflow', '=', 702)->whereIn('ticket_status',[0,1,3])->allAssignedComplaint($request)->select('id')->groupBy('id')->distinct()->get()->count();
		}else{
			$hours = $request->hours;
		$dt = Carbon::now(); // Produces something like "2020-01-27 16:02:47.063656 Asia/Muscat (+04:00)"
		$dt->subHour($hours); 
		$userId = User::role(['maintenance_engineer'])->pluck('id');

		$openAssignedComplaints = ViewComplaintAssigned::where('work_flow_processes_code', '=', 702)->where('process_workflow', '=', 702)->whereIn('ticket_status',[0,1,3])->assignedComplaint($request)->select('id')->groupBy('id')->distinct()->get()->count();
	}


	$openAssignedComplaints =   ['count' => $openAssignedComplaints];
	return json_encode(array($openAssignedComplaints));
}
	//Assigned Tickets – Open – Excluding cases of sub-contractor – More than 24 hrs./48 hrs./All -

public function openAssignedComplaintSupervisor(Request $request){
if($request->time == 'all')
{
$openAssignedComplaints =ViewComplaintAssigned::where('work_flow_processes_code', '=', 702)->where('process_workflow', '=', 702)->whereIn('ticket_status',[0,1,3])->filter($request)->assignedComplaint($request)->allAssignedComplaintSupervisor($request)->assignedComplaintSubcontractor($request)->assignedTo($request)->category($request)->vipOpenAssigned($request)->select(DB::raw('MAX(id) as complaint_checklist_id'),'id','complaint_enquiries_id','complaint_ticket_no','work_id','assigned_to','building_id','building_name', 'unit_code','assigned_to','concat','vendors','ticket_status','work_flow_processes_code','assigned_to_type')->groupBy('complaint_enquiries_id','complaint_ticket_no','id','work_id','assigned_to','building_id','building_name', 'unit_code','assigned_to','concat','vendors','ticket_status','work_flow_processes_code','assigned_to_type')->get()->count();

}else{
$openAssignedComplaints = ViewComplaintAssigned::where('work_flow_processes_code', '=', 702)->where('process_workflow', '=', 702)->whereIn('ticket_status',[0,1,3])->filter($request)->assignedComplaint($request)->assignedComplaintSupervisor($request)->assignedComplaintSubcontractor($request)->assignedTo($request)->category($request)->vipOpenAssigned($request)->select(DB::raw('MAX(id) as complaint_checklist_id'),'id','complaint_enquiries_id','complaint_ticket_no','work_id','assigned_to','building_id','building_name', 'unit_code','assigned_to','concat','vendors','ticket_status','work_flow_processes_code','assigned_to_type')->groupBy('complaint_enquiries_id','complaint_ticket_no','id','work_id','assigned_to','building_id','building_name', 'unit_code','assigned_to','concat','vendors','ticket_status','work_flow_processes_code','assigned_to_type')->get()->count();
}


$openAssignedComplaints =   ['count' => $openAssignedComplaints];
return json_encode(array($openAssignedComplaints));

}


	//Ticket with Sub contractors – Open cases – more than 1 day/3 days/5 days/All -
public function openAssignedComplaintSubcontractor(Request $request){
	if($request->sub_open_days == 'all')
	{
		$openAssignedComplaints = ViewComplaintAssigned::where('work_flow_processes_code', '=', 702)->where('process_workflow', '=', 702)->whereIn('ticket_status',[0,1,3])->filter($request)->allAssignedComplaintSubcontractor($request)->select(DB::raw('MAX(id) as complaint_checklist_id'),'id','complaint_enquiries_id','complaint_ticket_no','work_id','assigned_to','building_id','building_name', 'unit_code','assigned_to','concat','vendors','ticket_status','work_flow_processes_code','assigned_to_type')->groupBy('complaint_enquiries_id','complaint_ticket_no','id','work_id','assigned_to','building_id','building_name', 'unit_code','concat','vendors','ticket_status','work_flow_processes_code','assigned_to_type')->get()->count();
	}else{
		$openAssignedComplaints = ViewComplaintAssigned::where('work_flow_processes_code', '=', 702)->where('process_workflow', '=', 702)->whereIn('ticket_status',[0,1,3])->filter($request)->assignedComplaintSubcontractor($request)->select(DB::raw('MAX(id) as complaint_checklist_id'),'id','complaint_enquiries_id','complaint_ticket_no','work_id','assigned_to','building_id','building_name', 'unit_code','assigned_to','concat','vendors','ticket_status','work_flow_processes_code','assigned_to_type')->groupBy('complaint_enquiries_id','complaint_ticket_no','id','work_id','assigned_to','building_id','building_name', 'unit_code','concat','vendors','ticket_status','work_flow_processes_code','assigned_to_type')->get()->count();
	}


	$openAssignedComplaints =   ['count' => $openAssignedComplaints];
	return json_encode(array($openAssignedComplaints));

}

/*
*Pending approvals from HOD
*/

public function pendingApprovals(Request $request){
	
	$renewalDays = $request->renewal_days;
	$nextDays = Carbon::today()->subDays($renewalDays);
	$tenantRenewal = ViewRenewalUser::areFilter()
	->select('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status')
	->groupBy('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status')
	->where('work_flow_processes_code', '=', 302)
	->where('status',1)
	->where('user_status',1)
	->where('created_at','<=',$nextDays)->renewalUsers()
	->get()
	->count();

	$tenantRenewalCount =   ['count' => $tenantRenewal];
	return json_encode(array($tenantRenewalCount));

}

	/*
	*Contracts expired – Under Penalty (After 30 days)
	*/
	public function underPenaltyCount(){
		$underPenalty = 30;
		$today = date('Y-m-d');
		$nextDays = Carbon::today()->subDays($underPenalty);
		$underPenaltyCount = ViewTenantContract::areFilter()->where('tenant_penalty_invoice_amt','>',0)->whereDate('tenant_contract_valid_to_date','<',$nextDays)->count();
		return $underPenaltyCount;
	}
	
	
	public function averageReceivableUnit(Request $request){
$is_tenant_contract = true; // TO identify in view load from tenant-contract index method
$nextDays = Carbon::today();
$today = date('Y-m-d');
$tenantContracts = ViewTenantContract::averageReceivables($request)
->where(function ($query) {
$query->where('unit_is_legal',2)
            ->orWhereNull('unit_is_legal');
})
->areFilter()->where('tenant_contract_status',1)
->filter($request)                      
->sortable()
->paginate($this->noOfRecord);  
//dd($tenantContracts);    

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
return view('backoffice::TenantContract.receivable_contract_list_ajax',compact('tenantContracts','request','is_tenant_contract','nextDays'));

$title = 'Average receivables';
$breadcrumb = 'average_receivables';


return view('backoffice::TenantContract.receivable_contract_list',compact('tenantContracts','fields','request','serach_url','operations','title','nextDays','breadcrumb','is_tenant_contract','nextDays'));
}


	
	//Ticket waiting landlord approval –Open cases more than 7 days/15 days/All
	public function complaintReviewCount(Request $request){
		if($request->review_days == 'all')
		{
			$ComplaintReview = ViewComplaintReview::landlordReview($request)->whereIn('complaint_assign_status',[2,5,6])->select('service_report_id','complaint_enquiries_id', 'complainer_name', 
				'complaint_no', 'complaint_mob_no', 'complaint_date', 'service_report_no', 
				'complaint_assign_status', 'building_name', 'unit_code', 'locations_name')
			->groupBy('service_report_no','service_report_id','complaint_enquiries_id', 'complainer_name', 
				'complaint_no', 'complaint_mob_no', 'complaint_date','complaint_assign_status', 'building_name', 'unit_code', 'locations_name')->get()->count();

		}else{
			$ComplaintReview = ViewComplaintReview::landlordReview($request)->whereIn('complaint_assign_status',[2,5,6])->select('service_report_id','complaint_enquiries_id', 'complainer_name', 
				'complaint_no', 'complaint_mob_no', 'complaint_date', 'service_report_no', 
				'complaint_assign_status', 'building_name', 'unit_code', 'locations_name')
			->groupBy('service_report_no','service_report_id','complaint_enquiries_id', 'complainer_name', 
				'complaint_no', 'complaint_mob_no', 'complaint_date','complaint_assign_status', 'building_name', 'unit_code', 'locations_name')->get()->count();
		}


		$ComplaintReview =   ['count' => $ComplaintReview];
		return json_encode(array($ComplaintReview));

	}

	/*
*
*Maintenance Supervisor starts
*
*/
public function  maintenanceSupervisorDashboard(Request $request,$dashboard = false){
	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}

	/***********     assignedComplaintCount     ******************/

	if($count_type == 'assignedComplaintCount' || $dashboard == true){ 



		$user = \Auth::user();
		$roles = \Auth::user()->getRoles();
		$assignedComplaintCount = ComplaintEnquiry::when( (!$user->hasAnyRole(['super_admin','maintenance_coordinator','call_center','are'])),  function($query)use($roles){

			$query->whereHas('complaintTicketsAll', function ($query){   
				$query->whereHas('complaintProcessAll', function ($query){
					$query->complaintUsers();                       
				})->assignedUsers();                                   
			});   

		})->where('complaint_status','!=',2)->count();


		if(!$dashboard)
			return ['count' => $assignedComplaintCount, 'href' => route('complaint.index') ];

	}
	/*****Complaints Assigned to technician pending for more than 24 hours***/

	if($count_type == 'assignedTechnicianCount' || $dashboard == true){ 


		$dt = Carbon::now(); 
		$dt->subHour(24);
		$assignedTechnicianCount = ViewComplaintSubassigned::where('work_flow_processes_code', '=', 703)->where('process_workflow', '=', 703)->whereIn('ticket_status',[0,1,3])->where('created_at','<=',$dt)->where('complaint_status','!=',2)->complaintUsers()
		->select('complaint_enquiries_id','complainer_name','complaint_no','building_name','complaint_date','complaint_mob_no','sub_assigned_to','ticket_status','assigned_name','sub_assigned_name','unit_code','assigned_to','sub_assigned_to')
		->groupBy('view_complaint_subassigned.complaint_enquiries_id','complainer_name','complaint_no','building_name','complaint_date','complaint_mob_no','sub_assigned_to','ticket_status','assigned_name','sub_assigned_name','unit_code','assigned_to','sub_assigned_to')
		->assignedUsers()->get()->count();


		if(!$dashboard)
			return ['count' => $assignedTechnicianCount];

	}
	return [
	'assignedComplaintCount'=>$assignedComplaintCount,
	'assignedTechnicianCount'=>$assignedTechnicianCount
	];

}
	/*
*
*Maintenance Supervisor ends
*
*/
/*
*
*Take Over Supervisor/Coordinator starts
*
*/
public function  takeOverSupervisorDashboard(Request $request,$dashboard = false){
	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}

	/***********     Handover Unassigned Count    ******************/

	if($count_type == 'handoverUnassignedCount' || $dashboard == true){ 



		$handoverUnassignedCount = ViewTenantTermination::
		where('work_flow_processes_code', '=', 502)
		->where('termination_type', '=', 1)
		->where('tenant_contract_status',1)   
		->filterUsers()->get()->count();


		if(!$dashboard)
			return ['count' => $handoverUnassignedCount];

	}
	/***********     Assigned    ******************/

	if($count_type == 'handoverAssignedCount' || $dashboard == true){ 



		$handoverAssignedCount = ViewTenantTermination::
		where('work_flow_processes_code', '=', 503)
		->where('termination_type', '=', 1)
		->where('tenant_contract_status',1)
		->where('termination_refer_back', '=', 0)      
		->filterUsers()->get()->count();


		if(!$dashboard)
			return ['count' => $handoverAssignedCount];

	}
	/***********     Keys pending with Takeover Team    ******************/

	if($count_type == 'takeoverTeamCount' || $dashboard == true){ 



		$userId = User::role(['take_over_supervisor','take_over_executive'])->pluck('id');
		$takeoverTeamCount = key::where('status',1)->whereIn('user_id',$userId)->count();


		if(!$dashboard)
			return ['count' => $takeoverTeamCount];

	}
	return [
	'handoverUnassignedCount'=>$handoverUnassignedCount,
	'handoverAssignedCount'=>$handoverAssignedCount,
	'takeoverTeamCount'=>$takeoverTeamCount
	];

}
	/*
*
*Take Over Supervisor/Coordinator ends
*
*/
/*
*
*ARE starts
*
*/
public function  areDashboard(Request $request,$dashboard = false){
	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}

	/***********     bouncedChequeCount     ******************/

	if($count_type == 'bouncedChequeCount' || $dashboard == true){ 



		$bouncedChequeCount = Pdc::areFilter()->bouncedCheques()->count();


		if(!$dashboard)
			return ['count' => $bouncedChequeCount ];

	}
	/***********     vaccatingUnitsCount     ******************/

	if($count_type == 'vaccatingUnitsCount' || $dashboard == true){ 



		$vaccatingUnitsCount = Unit::are()->vaccatingUnit()->count();


		if(!$dashboard)
			return ['count' => $vaccatingUnitsCount ];

	}
	/*********Maintenance pending Cases (Not tickets) – More than 1 week ******/

	if($count_type == 'maintenancePendingCount' || $dashboard == true){ 



		$oneWeek = 7;
		$nextExpDays = Carbon::today()->subDays($oneWeek);
		$maintenancePendingCount = ComplaintEnquiry::areFilter()->whereDate('created_at','<=',$nextExpDays)->where('complaint_status','!=',2)->count();


		if(!$dashboard)
			return ['count' => $maintenancePendingCount ];

	}
	/*********Contracts expired– Within grace period (30 days)******/

	if($count_type == 'gracePeriodCount' || $dashboard == true){							 
		$gracePeriod = 30;
		$today = date('Y-m-d');

		$nextDays = Carbon::today()->subDays($gracePeriod);

		$gracePeriodCount = ViewTenantContract::where('tenant_contract_status','=',1)->areDashloadFilter()->directIndirect($request)->whereDate('tenant_contract_valid_to_date','>=',$nextDays)->whereDate('tenant_contract_valid_to_date','<=',$today)->count();


		if(!$dashboard)
			return ['count' => $gracePeriodCount ];

	}

	/*********Contracts expired – Under Penalty (After 30 days)******/

	if($count_type == 'underPenaltyCount' || $dashboard == true){



$underPenalty = 30;
$today = date('Y-m-d');
$nextDays = Carbon::today()->subDays($underPenalty);
$underPenaltyCount = ViewTenantContract::tenantContract()->areFilter()->directIndirect($request)->whereDate('tenant_contract_valid_to_date','<',$nextDays)->count();


if(!$dashboard)
return ['count' => $underPenaltyCount ];

}

	/*********Not Registered in  municipality******/

	if($count_type == 'notRegisteredInMunicipalityCount' || $dashboard == true){ 



		$user = \Auth::user();
		$roles = $user->getRoles();
		$rolesNames = $user->getRoleNames()->toArray();
		if($user->hasRole('are_team_lead')){
			$days = 10;
		}else{
			$days = 5;
		}
		//$nextDays = Carbon::today()->subDays($days);
		//dd($nextDays);
		$notRegisteredInMunicipalityCount = ViewRenewalUser::areFilter()
		->select('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
		->groupBy('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
		->where('work_flow_processes_code', '=', 303)
		->where('tenant_contract_status', 0)
		->where('tenant_renewal_termination_status',5)
		->where('user_status',1)
		//->whereDate('created_at','<=',$nextDays)
		->where('tenant_contract_is_reg_municipality','!=',1)
		->whereDate('created_at', '<=', Carbon::now()->subDays($days)->toDateString())
		->get()->count();


		if(!$dashboard)
			return ['count' => $notRegisteredInMunicipalityCount ];

	}
	$averageReceivables = 0;
	/*********Avg Receivables******/
	
	if($count_type == 'averageReceivables' || $dashboard == true){ 
		
		
		$tenantContracts = ViewTenantContract::
		whereDate('tenant_contract_valid_to_date','>=',$today)
		->where('status','!=',5)->where('tenant_contract_status',1)
		->select(DB::raw("SUM((extract(month from age(now(),
			(CASE 
				WHEN tenant_contract_last_paid_date  > now()  THEN now()
				WHEN tenant_contract_last_paid_date  is NOT NULL THEN tenant_contract_last_paid_date
			     WHEN tenant_contract_effective_date  is NOT NULL THEN tenant_contract_effective_date
			END)))) * tenant_contract_rent)					
			as recievable"),DB::raw("count(id) as avgcnt"))
		->areFilter()
		->where(function ($query) {
			$query->where('unit_is_legal',2)
            ->orWhereNull('unit_is_legal');
		})
		->first();
		 
		if($tenantContracts->recievable == 0){
				$averageReceivables = 0 ;
		}
		else{

				//$averageReceivables = floatval($tenantContracts->recievable)/intval($tenantContracts->avgcnt);
				$averageReceivableAmt = floatval($tenantContracts->recievable)/intval($tenantContracts->avgcnt);
				$averageReceivables = round($averageReceivableAmt);
		}
		
		if(!$dashboard)
			return ['count' => $averageReceivables ];

	}
	/*********Referred back******/

	if($count_type == 'referBackContractsCount' || $dashboard == true){ 



		$referBackContractsCount = ViewDueRenewal::areFilter()->where('tenant_contract_status',1)->where('tenant_renewal_termination_status',2)->where('status',1)->where('refer_back_status',1)->get()->count();


		if(!$dashboard)
			return ['count' => $referBackContractsCount ];

	}
	return [
	'bouncedChequeCount'=>$bouncedChequeCount,
	'vaccatingUnitsCount'=>$vaccatingUnitsCount,
	'maintenancePendingCount'=>$maintenancePendingCount,
	'gracePeriodCount'=>$gracePeriodCount,
	'underPenaltyCount'=>$underPenaltyCount,
	'notRegisteredInMunicipalityCount'=>$notRegisteredInMunicipalityCount,
	'averageReceivables'=>$averageReceivables,
	'referBackContractsCount'=>$referBackContractsCount
	];

}
	/*
*
*ARE ends
*
*/
	/*
*
*Maintenance Engineer starts
*
*/
public function  maintenanceEngineerDashboard(Request $request,$dashboard = false){
	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}

	/***********     vipOpenAssignedCount     ******************/

	if($count_type == 'vipOpenAssignedCount' || $dashboard == true){ 



		$vipOpenAssignedCount = ViewComplaintAssigned::where('work_flow_processes_code', '=', 702)->where('process_workflow', '=', 702)->whereIn('ticket_status',[0,1,3])->assignedUsers()->where('complaint_status','!=',2)->has('vipTenant')->select('id','complaint_enquiries_id','complaint_ticket_no')->groupBy('id','complaint_enquiries_id','complaint_ticket_no')->get()->count();


		if(!$dashboard)
			return ['count' => $vipOpenAssignedCount];

	}
	/***********     Landlord Approval Pending    ******************/

	if($count_type == 'landlordApprovalPendingCount' || $dashboard == true){



$landlordApprovalPendingCount = ViewComplaintReview::where('complaint_assign_status',5)->assignedEngineer()->select('service_report_id','complaint_enquiries_id', 'complainer_name',
'complaint_no', 'complaint_mob_no', 'complaint_date', 'service_report_no',
'complaint_assign_status', 'building_name', 'unit_code', 'locations_name','assigned_to','assigned_to_type')
->groupBy('service_report_no','service_report_id','complaint_enquiries_id', 'complainer_name',
'complaint_no', 'complaint_mob_no', 'complaint_date','complaint_assign_status', 'building_name', 'unit_code', 'locations_name','assigned_to','assigned_to_type')->get()->count();


if(!$dashboard)
return ['count' => $landlordApprovalPendingCount];

}


	/***********     Keys pending with Maintenance Engineer   ******************/

	if($count_type == 'maintenanceEngineerCount' || $dashboard == true){ 



		$userId = User::role(['maintenance_engineer'])->pluck('id');
		$maintenanceEngineerCount = key::where('status',1)->whereIn('user_id',$userId)->count();


		if(!$dashboard)
			return ['count' => $maintenanceEngineerCount];

	}
	/***********     Keys pending with Takeover Team    ******************/

	if($count_type == 'takeoverTeamCount' || $dashboard == true){ 



		$userId = User::role(['take_over_supervisor','take_over_executive'])->pluck('id');
		$takeoverTeamCount = key::where('status',1)->whereIn('user_id',$userId)->count();


		if(!$dashboard)
			return ['count' => $takeoverTeamCount];

	}

	return [
	'vipOpenAssignedCount'=>$vipOpenAssignedCount,
	'landlordApprovalPendingCount'=>$landlordApprovalPendingCount,
	'maintenanceEngineerCount'=>$maintenanceEngineerCount,
	'takeoverTeamCount'=>$takeoverTeamCount
	];

}
	/*
*
*Maintenance Engineer ends
*
*/
/*
*
*Maintanance Co-Ordinator starts
*
*/
public function  maintenanceCoordinatorDashboard(Request $request,$dashboard = false){
	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}

	/***********     unAssignedCaseCount     ******************/

	if($count_type == 'unAssignedCaseCount' || $dashboard == true){ 



		$unAssignedCaseCount = ComplaintEnquiry:: 
		whereHas('ComplaintProcess', function ($query){
			$query->where('work_flow_processes_code', '=', 701)
			->complaintUsers();
		})
		->whereHas('complaintTicketsAll', function ($query){
			$query->where('work_flow_processes_code', '=', 701);                     
		})->count();


		if(!$dashboard)
			return ['count' => $unAssignedCaseCount];

	}
	/***********     vipOpenCount     ******************/

	if($count_type == 'vipOpenCount' || $dashboard == true){ 



		$user = \Auth::user();
		$vipOpenCount =  ComplaintEnquiry::when( (!$user->hasAnyRole(['super_admin','maintenance_coordinator','call_center'])),  function($query){
			$query->whereHas('complaintTicketsAll', function ($query){   
				$query->whereHas('complaintProcessAll', function ($query){
					$query->complaintUsers();                       
				})->assignedUsers();                                   
			});                                  
		})->where('complaint_status','!=',2)->has('vipTenant')->count();


		if(!$dashboard)
			return ['count' => $vipOpenCount];

	}
	/***** Cases Maintained by Landlord (Based on details in Building Master)***/

	if($count_type == 'landlordCasesCount' || $dashboard == true){ 



		$user = \Auth::user();
		$landlordCasesCount =  ComplaintEnquiry::when( (!$user->hasAnyRole(['super_admin','maintenance_coordinator','call_center'])),  function($query){
			$query->whereHas('complaintTicketsAll', function ($query){   
				$query->whereHas('complaintProcessAll', function ($query){
					$query->complaintUsers();                       
				})->assignedUsers();                                   
			});                                  
		})->where('complaint_status','!=',2)->has('landlordBuilding')->count();


		if(!$dashboard)
			return ['count' => $landlordCasesCount];

	}
	/***** Cases Completed – completed the job and awaiting closure***/

	if($count_type == 'completedCasesCount' || $dashboard == true){ 



		$completedCasesCount = ComplaintEnquiry::whereDoesntHave('complaintTicketsAll', function ($query){
			$query->where('ticket_status','<',4)
			->where('work_flow_processes_code', '<', 706);
		})
		->whereHas('complaintTicketsAll', function ($query){
			$query->assignedUsers()
			->whereHas('complaintProcessAll', function ($query){
				$query->complaintUsers();                       
			}) ;                       
		})                        
		->where('complaint_status',1)->count();


		if(!$dashboard)
			return ['count' => $completedCasesCount];

	}
	return [
	'unAssignedCaseCount'=>$unAssignedCaseCount,
	'vipOpenCount'=>$vipOpenCount,
	'landlordCasesCount'=>$landlordCasesCount,
	'completedCasesCount'=>$completedCasesCount
	];

}
	/*
*
*Maintanance Co-Ordinator ends
*
*/
/*
*
*BackOffice Executive starts
*
*/
public function  backOfficeExecutiveDashboard(Request $request,$dashboard = false){
	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}

	/***********     unregisteredContractsCount     ******************/

	if($count_type == 'unregisteredContractsCount' || $dashboard == true){



$unregisteredContractsCount = ViewTenantContract::tenantContract()->where('tenant_contract_is_reg_municipality',0)->directIndirect($request)->get()->count();


if(!$dashboard)
return ['count' => $unregisteredContractsCount];

}

	/***********     finalDocumentationCount     ******************/

	if($count_type == 'finalDocumentationCount' || $dashboard == true){ 



		$finalDocumentationCount = \Modules\Sales\Entities\ViewTenantStage::
		where('work_flow_processes_code', '=', 106)
		->where('sale_work_flow_processes_code', '=', 106)
		->where('tennat_contract_direct_indirect_status', '=', 0)
		->select('sales_enquiry_id')		     
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name")			     
		->salesUsers()
		->get()->count();
	//dd($finalDocumentationCount);


		if(!$dashboard)
			return ['count' => $finalDocumentationCount];

	}
	/***********     renewalsCount     ******************/

	if($count_type == 'renewalsCount' || $dashboard == true){ 



		$roles = \Auth::user()->getRoles();
		$rolesNames = \Auth::user()->getRoleNames()->toArray();
		$tenantRenewal = ViewRenewalUser::select('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
		->groupBy('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
		->where('work_flow_processes_code', '=', 301)
		->where('status',1)
		->filter($request);
		if (in_array('super_admin', $rolesNames) === false) {
			
			$tenantRenewal->where(function ($query) use($roles){
				$query->where('user_id',null)->whereIn('role_id', $roles);
			})->orWhere(function ($query) use($roles){
				$query->where('user_id','>',0)
				->whereIn('role_id', $roles)
				->where('user_id','=', \Auth::user()->id);
			})->where('status',1)->where('work_flow_processes_code',301);                       
			
		}
		$renewalsCount = $tenantRenewal->get()->count();


		if(!$dashboard)
			return ['count' => $renewalsCount];

	}
	/***********     openTerminationCount     ******************/

	if($count_type == 'openTerminationCount' || $dashboard == true){ 



		$openTerminationCount = ViewTenantTermination::
		where('work_flow_processes_code', '=', 501)
		->where('termination_type', '=', 1)
		->where('tenant_contract_status',1)
		->where('tenant_renewal_termination_status',7)       
		->filterUsers()->count();


		if(!$dashboard)
			return ['count' => $openTerminationCount];

	}
	/***********     terminatedCount     ******************/

	if($count_type == 'terminatedCount' || $dashboard == true){ 



		$terminatedCount =     ViewTenantTermination::
		where('work_flow_processes_code', '=', 504)
		->where('termination_type', '=', 1)
		->where('tenant_contract_status',1)   
		->filterUsers()->count();


		if(!$dashboard)
			return ['count' => $terminatedCount];

	}
	/***********     activeCasesCount     ******************/

	if($count_type == 'activeCasesCount' || $dashboard == true){ 



		$activeCasesCount = Legal::where('legal.work_flow_processes_code', '=', 803)->count();


		if(!$dashboard)
			return ['count' => $activeCasesCount];

	}
	/***********     gracePeriodCount     ******************/

	if($count_type == 'gracePeriodCount' || $dashboard == true){



$gracePeriod = 30;
$today = date('Y-m-d');
$nextDays = Carbon::today()->subDays($gracePeriod);
$gracePeriodCount = ViewTenantContract::tenantContract()->areFilter()->directIndirect($request)->whereDate('tenant_contract_valid_to_date','>=',$nextDays)->whereDate('tenant_contract_valid_to_date','<=',$today)->count();


if(!$dashboard)
return ['count' => $gracePeriodCount];

}
	/***********     underPenaltyCount     ******************/

	if($count_type == 'underPenaltyCount' || $dashboard == true){



$underPenalty = 30;
$today = date('Y-m-d');
$nextDays = Carbon::today()->subDays($underPenalty);
$underPenaltyCount = ViewTenantContract::tenantContract()->areFilter()->directIndirect($request)->whereDate('tenant_contract_valid_to_date','<',$nextDays)->count();


if(!$dashboard)
return ['count' => $underPenaltyCount];

}

	/***********     referBackContractsCount     ******************/

	if($count_type == 'referBackContractsCount' || $dashboard == true){ 



		$referBackContractsCount = ViewDueRenewal::areFilter()->where('tenant_contract_status',1)->where('tenant_renewal_termination_status',2)->where('status',1)->where('refer_back_status',1)->get()->count();


		if(!$dashboard)
			return ['count' => $referBackContractsCount];

	}
	return [
	'unregisteredContractsCount'=>$unregisteredContractsCount,
	'finalDocumentationCount'=>$finalDocumentationCount,
	'renewalsCount'=>$renewalsCount,
	'openTerminationCount'=>$openTerminationCount,
	'terminatedCount'=>$terminatedCount,
	'activeCasesCount'=>$activeCasesCount,
	'gracePeriodCount'=>$gracePeriodCount,
	'underPenaltyCount'=>$underPenaltyCount,
	'referBackContractsCount'=>$referBackContractsCount
	];

}
	/*
*
*BackOffice Executive ends
*
*/
/*
/*
*
*Legal Advisor starts
*
*/
public function  legalAdvisorDashboard(Request $request,$dashboard = false){
	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}

	/***********     openCasesCount     ******************/

	if($count_type == 'openCasesCount' || $dashboard == true){ 


		$user =  \Auth::user();

		$roles = $user->getRoles();
		$rolesNames = $user->getRoleNames()->toArray();
		$openCasesCount = Legal::filter($request)
		->whereHas('legalUsers', function ($query)use($roles,$user) {                          
			$query->when(!($user->hasRole('super_admin')) ,  function($query)use($roles){
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
		->where('legal.work_flow_processes_code', '=', 802)->count();
//dd($openCasesCount);

		if(!$dashboard)
			return ['count' => $openCasesCount ];

	}
	/***********     closedCasesCount     ******************/

	if($count_type == 'closedCasesCount' || $dashboard == true){ 



		$closedCasesCount = Legal::whereHas('legalNotes',function($query){
			$query->where('legal_notes_status',0);
		})->count();


		if(!$dashboard)
			return ['count' => $closedCasesCount ];

	}
	/***********     referBackCasesCount     ******************/

	if($count_type == 'referBackCasesCount' || $dashboard == true){ 



		$referBackCasesCount = Legal::where('work_flow_processes_code',804)->where('are_status',1)->count();


		if(!$dashboard)
			return ['count' => $referBackCasesCount ];

	}
	return [
	'openCasesCount'=>$openCasesCount,
	'closedCasesCount'=>$closedCasesCount,
	'referBackCasesCount'=>$referBackCasesCount
	];

}
	/*
*
*Legal Advisor ends
*
*/
/*
*
*Call Center starts
*
*/
public function callCenterDashboard(Request $request,$dashboard = false){
	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}

	/***********     enquiryTodayCount Tenant     ******************/

	if($count_type == 'enquiryTodayCount' || $dashboard == true){ 



		$enquiryTodayCount = SalesEnquiry::whereDate('created_at', Carbon::today())
		->tenant()
		->where('sales_enquiry_direct_contract',1)->count();


		if(!$dashboard)
			return ['count' => $enquiryTodayCount];

	}/***********     Calls received this week  Tenant ******************/

	if($count_type == 'enquiryWeekCount' || $dashboard == true){ 

		$today = date('Y-m-d');
		$week = Carbon::today()->subDays(6);
		$week = date("Y-m-d", strtotime($week) );

		$enquiryWeekCount = 
		SalesEnquiry::
		whereDate('created_at', '>=', Carbon::now()->subDays(7)->toDateString())
		->where('sales_enquiry_direct_contract',1)
		->tenant()
		->count();


		if(!$dashboard)
			return ['count' => $enquiryWeekCount];

	}
	/***********     Calls received Today  Landlord ******************/

	if($count_type == 'landlordEnquiryCount' || $dashboard == true){ 

		$landlordEnquiryCount =  SalesEnquiry::whereDate('created_at', Carbon::today())->landlord()->with('enquiryStatus')->landlordContractNotDirect()->count();


		if(!$dashboard)
			return ['count' => $landlordEnquiryCount];

	}
	/***********     Calls received this week  Landlord ******************/

	if($count_type == 'landlordEnquiryWeekCount' || $dashboard == true){ 

		$today = date('Y-m-d');
		$week = Carbon::today()->subDays(6);
		$week = date("Y-m-d", strtotime($week) );

		$landlordEnquiryWeekCount = SalesEnquiry::
		whereDate('created_at', '>=', Carbon::now()->subDays(7)->toDateString())
		->landlord()
		->with('enquiryStatus')->landlordContractNotDirect()->count();


		if(!$dashboard)
			return ['count' => $landlordEnquiryWeekCount];

	}
	/***********     Complaint received Today ******************/

	if($count_type == 'complaintEnquiryCount' || $dashboard == true){ 

		$complaintEnquiryCount = ComplaintEnquiry::whereDate('created_at', Carbon::today())->count();


		if(!$dashboard)
			return ['count' => $complaintEnquiryCount];

	}
	/***********     Complaint received This week ******************/

	if($count_type == 'complaintEnquiryWeekCount' || $dashboard == true){ 

		$today = date('Y-m-d');
		$week = Carbon::today()->subDays(6);
		$week = date("Y-m-d", strtotime($week) );

		$complaintEnquiryWeekCount = ComplaintEnquiry::
		whereDate('created_at', '>=', Carbon::now()->subDays(7)->toDateString())
		->count();


		if(!$dashboard)
			return ['count' => $complaintEnquiryWeekCount];

	}
	return [
	'enquiryTodayCount'=>$enquiryTodayCount,
	'enquiryWeekCount'=>$enquiryWeekCount,
	'landlordEnquiryCount'=>$landlordEnquiryCount,
	'landlordEnquiryWeekCount'=>$landlordEnquiryWeekCount,
	'complaintEnquiryCount'=>$complaintEnquiryCount,
	'complaintEnquiryWeekCount'=>$complaintEnquiryWeekCount
	];

}
	/*
*
*Call Center ends
*
*/
/*
*
*ARE Team Lead starts
*
*/
public function  areTeamLeadDashboard(Request $request,$dashboard = false){
	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}


	/*********Contracts expired– Within grace period (30 days)******/

	if($count_type == 'gracePeriodCount' || $dashboard == true){



$gracePeriod = 30;
$today = date('Y-m-d');
$nextDays = Carbon::today()->subDays($gracePeriod);
$gracePeriodCount = ViewTenantContract::tenantContract()->areFilter()->directIndirect($request)->whereDate('tenant_contract_valid_to_date','>=',$nextDays)->whereDate('tenant_contract_valid_to_date','<=',$today)->count();


if(!$dashboard)
return ['count' => $gracePeriodCount ];

}

	/*********Contracts expired – Under Penalty (After 30 days)******/


if($count_type == 'underPenaltyCount' || $dashboard == true){



$underPenalty = 30;
$today = date('Y-m-d');
$nextDays = Carbon::today()->subDays($underPenalty);
$underPenaltyCount = ViewTenantContract::tenantContract()->areFilter()->directIndirect($request)->whereDate('tenant_contract_valid_to_date','<',$nextDays)->count();


if(!$dashboard)
return ['count' => $underPenaltyCount ];

}
	/*********Not Registered in  municipality******/

	if($count_type == 'notRegisteredInMunicipalityCount' || $dashboard == true){ 



		$user = \Auth::user();
		$roles = $user->getRoles();
		$rolesNames = $user->getRoleNames()->toArray();
		if($user->hasRole('are_team_lead')){
			$days = 10;
		}else{
			$days = 5;
		}
		$nextDays = Carbon::today()->subDays($days);
		$notRegisteredInMunicipalityCount = ViewRenewalUser::areFilter()->select('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
		->groupBy('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')->where('work_flow_processes_code', '=', 303)->where('tenant_contract_status', 0)->where('tenant_renewal_termination_status',5)->where('user_status',1)->whereDate('created_at','<=',$nextDays)->where('tenant_contract_is_reg_municipality','!=',1)->get()->count();


		if(!$dashboard)
			return ['count' => $notRegisteredInMunicipalityCount ];

	}
	return [
	'gracePeriodCount'=>$gracePeriodCount,
	'underPenaltyCount'=>$underPenaltyCount,
	'notRegisteredInMunicipalityCount'=>$notRegisteredInMunicipalityCount
	];

}
/*
*
*
*  Sales Manager Or Sales Head   Dashboard Data
*
*
**/
/*
*
*
*  Sales Manager Or Sales Head   Dashboard Data
*
*
**/
public function  salesHeadDashboard(Request $request,$dashboard = false){

	$count_type = $count_query = '';
	$count = 0;
	//echo "<pre>";print_r($request->all());exit;
	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        
		
	}
	if($count_type == 'unassignedEnquiry' || $dashboard == true){
	
		$unassigned_list = \Modules\Sales\Entities\ViewTenantStage::
		where('sale_work_flow_processes_code', '=', 101)
		->where('status', '=', 1)
		->select('sales_enquiry_id')		     
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no")
		->when(($count > 0),function($query)use($count){
			$query->whereDate('created_at', '<=', Carbon::now()->subDays($count)->toDateString());
		})
		->when(( $dashboard == true),function($query)use($count){
			$query->whereDate('created_at', '<=', Carbon::now()->subDays(1)->toDateString());
		})
		//->salesUsers()
		->get()->count();

		if(isset($request->count_val)){
			($count == 'all')? $count = '':'';
			$count_query = 'count_by_day='.$count;
		}		      

		if(!$dashboard)
			return ['count' => $unassigned_list, 'href' => 'unassignedEnquiry'.'?'.$count_query ];

	} 

	/*************   assignedEnquiry     *****************/
	if($count_type == 'assignedEnquiry' || $dashboard == true){ 

		$assigned_lists = \Modules\Sales\Entities\ViewTenantStage::
		where('sale_work_flow_processes_code', '=', 102)
		->where('work_flow_processes_code', '=', 102)
		->where('status', '=', 1)
		->select("sales_enquiry_id")
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no","employee_name")
		->when(($count > 0),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays($count)->toDateString());
		})
		->when(( $dashboard == true),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays(3)->toDateString());
		})
		//->salesUsers()
		->get()->count();	
		
		//echo "<pre>";print_r($assigned_lists);exit;

		if(isset($request->count_val)){
			($count == 'all')? $count = '':'';
			$count_query = 'count_by_assignday='.$count;
		} 
			

		
		
		if(!$dashboard)
			return ['count' => $assigned_lists, 'href' => route('leadAssign.assignedList').'?'.$count_query ];


	} 
	if($count_type == 'wonLastDays' || $dashboard == true){
		$toDate   = date('Y-m-d');
		
		$wonLastDays = Sales::whereHas('salesEnquiry', function ($query){
			$query->where('work_flow_processes_code', '=', 108);                   
		})->where('sales.sales_type', '=', 1)
		->where('sales.work_flow_processes_code', '=', 108)
		->when(($count > 0),function($query)use($count){
			
			$query->whereDate('created_at', '>=', Carbon::now()->subDays($count)->toDateString());
		
		})
		->when(( $dashboard == true),function($query)use($count){
			if($count == 'YTD'){

				$year 	= date('Y');
				$fromDate = $year.'-01-01';
				$query->whereDate('created_at', '>=', $fromDate);
			}	
			else{
				$count = 30;
			$query->whereDate('created_at', '>=', Carbon::now()->subDays(30)->toDateString());
			}
		})->salesUsers()->get()->count();	



		if(isset($request->count_val)){         	
			$count_query = 'count_by_day='.$count;
		}	
		if(!$dashboard){

			return ['count' => $wonLastDays, 'href' => route('wonList').'?count_by_day='.$count ];
		}
	}	
	// Not in Won & Loss
	if($count_type == 'notInWonLoss' || $dashboard == true){

		$notInWonLoss = SalesEnquiry::where(function($query){
			$query->where('work_flow_processes_code','>=',201);
			$query->where('work_flow_processes_code','<=',203);
		})->where('sales_type','=',2)->count(); 	

		if(isset($request->count_val)){         	
			$count_query = 'count_by_day='.$count;
		}	
		if(!$dashboard)
			return ['count' => $notInWonLoss, 'href' => route('leadAssign.assignedList').'?'.$count_query ];	
	}

	// Unassigned Morethan One Week
	if($count_type == 'unassignedMoreOneWeek' || $dashboard == true){
		
		$unassignedMoreOneWeek = ViewTenantStage::where('sale_work_flow_processes_code', '=', 101)->where('status', '=', 1)->select("sales_enquiry_id","sales_enquiry_no","sales_enquiry_name","sales_mobile_no","created_at","sales_enquiry_name","work_flow_processes_code as sales_work_flow", DB::raw("string_agg(distinct unit_type, ',' ORDER BY unit_type) AS unit_type","sales_note"), DB::raw("string_agg(distinct cast(loc as text), ',') AS loc"),"sales_note" )->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no")
      ->salesUsers()
	  ->isSalesHead()->get()->count();

		if(isset($request->count_val)){         	
			$count_query = 'count_by_day='.$count;
		}	
		if(!$dashboard)
			return ['count' => $unassignedMoreOneWeek, 'href' => route('leadAssign.assignedList').'?'.$count_query ];	
	}

	if($count_type == 'vacancyLoss' || $dashboard == true){

		$toDateVal   = date('Y-m-d 23:59:59');
		if($count === 'YTD'){
			
			$year 			= date('Y');
			$fromDateVal 	= $year.'-01-01 00:00:00';
		}	
		else{
			$year 	= date('Y');
			$month 	= date('m');
			$fromDateVal = $year.'-'.$month.'-01 00:00:00';
			
		}
		$fromDate   =   $fromDateVal;
		$toDate     =   $toDateVal;	
		$vacantVal = 0;
		$vacany_loss = 0;
		$vacantLoss = DB::table('vacant_vacany_loss')
				->join('buildings', 'buildings.id', '=', 'vacant_vacany_loss.building_id')
				->where(function ($query) use ($fromDate, $toDate) {

				    $query->where(function ($q) use ($fromDate, $toDate) {
				        $q->where('vacant_from', '>=', $fromDate)
				           ->where('vacant_from', '<', $toDate);

				    })->orWhere(function ($q) use ($fromDate, $toDate) {
				        $q->where('vacant_from', '<=', $fromDate)
				           ->where('vacant_to', '>', $toDate);

				    })->orWhere(function ($q) use ($fromDate, $toDate) {
				        $q->where('vacant_to', '>', $fromDate)
				           ->where('vacant_to', '<=', $toDate);

				    })->orWhere(function ($q) use ($fromDate, $toDate) {
				        $q->where('vacant_from', '>=', $fromDate)
				           ->where('vacant_to', '<=', $toDate);
				    });				
				})->where('management_id','=',1)->select('vacant_from','vacant_to','rent_per_month','status')
				->get();
		//dd($vacantLoss);
		foreach($vacantLoss as $vac){
			if(($vac->vacant_from >= $fromDate && $vac->vacant_to <= $toDate) || ($vac->vacant_from < $fromDate && $vac->vacant_to > $toDate)){
				if($vac->vacant_from < $fromDate && $vac->vacant_to > $toDate){
					$start  = $fromDate;
		        	$end    = date('Y-m-d',strtotime('+1 day',strtotime($toDate)));
				}
				else{
					$start  = $vac->vacant_from;
		        	$end    = date('Y-m-d',strtotime('+1 day',strtotime($vac->vacant_to)));

				}
				
		        $vacantDays =dateDifference($start,$end,'%a');

		        $vacany_loss = 0;
		        if($vac->rent_per_month > 0 && $vacantDays > 0){
		         
		          $vacany_loss = rentLossCalculationWithDays($vacantDays,$vac->rent_per_month);
		          
		        }
		        
			}
			elseif($vac->vacant_from <= $fromDate && $vac->vacant_to >= $fromDate && $vac->vacant_to <= $toDate){

				$start  = $fromDate;
		        $end    = date('Y-m-d',strtotime('+1 day',strtotime($vac->vacant_to)));
		        $vacantDays =dateDifference($start,$end,'%a');
		        $vacany_loss = 0;
		        if($vac->rent_per_month > 0 && $vacantDays > 0){
		         
		          $vacany_loss = rentLossCalculationWithDays($vacantDays,$vac->rent_per_month);
		        }

			}
			elseif($vac->vacant_from >= $fromDate && $vac->vacant_to <= $fromDate && $vac->vacant_to >= $toDate){
				$start  = $vac->vacant_from;
		        $end    = date('Y-m-d',strtotime('+1 day',strtotime($toDate)));
		        $vacantDays =dateDifference($start,$end,'%a');
		        $vacany_loss = 0;
		        if($vac->rent_per_month > 0 && $vacantDays > 0){
		         
		          $vacany_loss = rentLossCalculationWithDays($vacantDays,$vac->rent_per_month);
		        }
				
			}
			$vacantVal += $vacany_loss;
		}
		//dd($vacantVal);
		//$vacantRent = numberFormat($vacantVal);
		$vacantRent = round($vacantVal);
		if(!$dashboard)
			return ['count' => $vacantRent, 'href' => route('vacancy-loss-list',[$count])];
	}
	/********** SalesPerson Performance Analysis **********/
	$sales_person_user_list = User::role("sales_person")->get();
	//dd($sales_person_user_list);
	foreach($sales_person_user_list as $key=>$list){

		if($list->employee->id) 
			$userList[] = $list->employee->employee_name;
		
		$userstageList['assigned'][$key] = $units = DB::table('sales')
		->join('sales_users', 'sales_users.sales_id', '=', 'sales.id')
		->join('sales_enquiries', 'sales_enquiries.id', '=', 'sales.sales_enquiry_id')
		->where('sales.sales_type','=',1)->where('sales.work_flow_processes_code','=',102)->where('sales_enquiry_direct_contract','=',1)->where('user_id','=',$list->id)
		->get()->count();
		$userstageList['inprogess'][$key] = $units = DB::table('sales')
		->join('sales_users', 'sales_users.sales_id', '=', 'sales.id')
		->join('sales_enquiries', 'sales_enquiries.id', '=', 'sales.sales_enquiry_id')
		->where('sales.sales_type','=',1)->where('sales.work_flow_processes_code','=',103)->where('sales_enquiry_direct_contract','=',1)->where('user_id','=',$list->id)
		->get()->count();
		$userstageList['prel_doc'][$key] = $units = DB::table('sales')
		->join('sales_users', 'sales_users.sales_id', '=', 'sales.id')
		->join('sales_enquiries', 'sales_enquiries.id', '=', 'sales.sales_enquiry_id')
		->where('sales.sales_type','=',1)->where('sales.work_flow_processes_code','=',104)->where('sales_enquiry_direct_contract','=',1)->where('user_id','=',$list->id)
		->get()->count();
		$userstageList['final_doc'][$key] = $units = DB::table('sales')
		->join('sales_users', 'sales_users.sales_id', '=', 'sales.id')
		->join('sales_enquiries', 'sales_enquiries.id', '=', 'sales.sales_enquiry_id')
		->where('sales.sales_type','=',1)->where('sales.work_flow_processes_code','=',106)->where('sales_enquiry_direct_contract','=',1)->where('assigned_person','=',$list->id)
		->get()->count();	
	}

	$sale['name']= $userList;
	$sale['value']= $userstageList;

	/********** SalesPerson Performance Analysis End**********/	

	/********** Unit Vacant Graph **********/	

	$thirtyDays = Carbon::today()->subDays(30);
	$sixetyDays = Carbon::today()->subDays(60);
	$ninetyDays = Carbon::today()->subDays(90);

/*******************  Vacant units  **************************/
	if($count_type == 'vacantUnits' || $dashboard == true){

		$days = [30,60,90];

		foreach ($days as $day) {
////////////comprehensive  /////////////////////////
			$comprehensive[$day] = 
			Unit::where('unit_vaccant_status',0)
			->whereHas('building',function($query) {
				$query->where('management_id',1);
			})
			->orWhere(function($query)use($day) {		             
				$query->where('unit_vaccant_status',0)
				->whereHas('tenantContract', function($query) use($day){
					$query->whereDate('tenant_contract_valid_to_date', '<=', Carbon::now()->subDays($day)->toDateString())
					->where('tenant_contract_status',0)
					->doesntHave('tenantContractOld');
				});
			})
			->count();	

/////////////////normal //////////////////////////////////
			$normal[$day] = 
			Unit::where('unit_vaccant_status',0)
			->whereHas('building',function($query) {
				$query->where('management_id',2);
			})
			->orWhere(function($query)use($day) {
				$query->where('unit_vaccant_status',0)
				->whereHas('tenantContract', function($query) use($day){
					$query->whereDate('tenant_contract_valid_to_date', '<=', Carbon::now()->subDays($day)->toDateString())
					->where('tenant_contract_status',0)
					->doesntHave('tenantContractOld');
				});
			})->count();

		}


		$vacantUnits['comprehensive']  = $comprehensive;
		$vacantUnits['normal']  = $normal;	 

	}


	/********** Unit Vacant Graph End**********/
	// Pending Approval
	if($count_type == 'pendingApproval' || $dashboard == true){
		
		$pendingApproval = ViewTenantStage::where('work_flow_processes_code', '=',105)->where('sale_work_flow_processes_code','=',105)->where('status','=',1)->select("sales_enquiry_id")->groupBy("sales_enquiry_id")
		->salesUsers()->get()->count();
		
		if(isset($request->count_val)){         	
			$count_query = 'count_by_day='.$count;
		}	
		if(!$dashboard)
			return ['count' => $pendingApproval, 'href' => route('leadAssign.assignedList').'?'.$count_query ];	

	}

	/*********Avg Response Time******/

	if($count_type == 'averageResponseTime' || $dashboard == true){ 
		$averageResponseTime = 0 ;
		$SalesEnquiries = SalesEnquiry::where('sales_type',1)->whereHas('salesFirstCall')->get();
		$TotalEnquiries = $SalesEnquiries->count();
		$totalTime = 0;
		foreach ($SalesEnquiries as $key=>$SalesEnquiry) {
			$SalesEnquiryId = $SalesEnquiry->id;
			$Sales = Sales::where('sales_enquiry_id',$SalesEnquiryId)->where('work_flow_processes_code',102)->first();
			$firstCallTime = $Sales->created_at;
			$SalesEnquiryTime = $SalesEnquiry->created_at;
			$timeDifference = strtotime($firstCallTime) - strtotime($SalesEnquiryTime);

			$totalTime+= $timeDifference;
			$sumOfTotalTime=$totalTime;
			$time[] = $SalesEnquiry->created_at.'-'.$Sales->created_at.'---'.$sumOfTotalTime;
			
		
		}
		if(isset($sumOfTotalTime)){
			$hrs = $sumOfTotalTime/3600;
			$averageResponseTime = $hrs/$TotalEnquiries;
	
		}
if(!$dashboard)
			return ['count' => numberFormat($averageResponseTime)];	

	}

	return [   'wonLastDays'=>$wonLastDays,
	'notInWonLoss'=>$notInWonLoss,
	'unassignedMoreOneWeek'=>$unassignedMoreOneWeek,
	'vacancyLoss'=> $vacantRent,
	'pendingApproval'=>$pendingApproval,
	'vacantUnits'=>$vacantUnits,
	'sales_person_list'=>$sale,
	'averageResponseTime'=>round($averageResponseTime)
	];

}
/*
 // Date Rent calculation

*/
public function contractRentCountCalculation($date1, $date2, $rent){

	$date1 = date("Y-m-d", strtotime($date1));
	$date2 = date("Y-m-d", strtotime($date2));
      // First day of the month.
	$firstDate  = strtotime(date('Y-m-01', strtotime($date1)));

    // Last day of the month.
	$lastDate   = strtotime(date('Y-m-t', strtotime($date2)));

	$startDate  = new \DateTime($date1);
	$endDate    = new \DateTime($date2);

	$noOfDaysStartMonth = date('t', strtotime($date1)); 
	$noOfDaysEnd    = date('t', strtotime($date2)); 

	$explodeStartDtValue  = explode("-",$date1);
	$explodeEndDtValue    = explode("-",$date2);

    //echo "First Date".$firstDate ."--".strtotime($date1); 
    //echo "Last Date".$lastDate ."--".strtotime($date2); 

    // exit;
	$sumOfStartDays = 0;
	$sumOfEndDays = 0;
	$sumOfSameStartEnd = 0;
	$monthIsOne = 1;
	$sumOfMonthRent = 0;

	$startDate->setTimestamp(strtotime($date1));
	$endDate->setTimestamp(strtotime($date2));

	if($firstDate === strtotime($date1) && $lastDate === strtotime($date2)){

		$sumOfMonthRent = $monthIsOne * $rent;
	}
	else{

		if ($startDate->format('Y-m') === $endDate->format('Y-m')) {

			$startDays = $explodeEndDtValue[2] - $explodeStartDtValue[2] +1;

			$sumOfStartDays  = ($rent/30) * $startDays;

		}
		else{

        //echo $explodeStartDtValue[2]; exit;
			if($explodeStartDtValue[2] <= $noOfDaysStartMonth ){
              // No of days is same consider as One Month
				if($explodeStartDtValue[2] == '01'){
					$sumOfStartDays  = $rent;
				}
				else{
					$startDays = $noOfDaysStartMonth - $explodeStartDtValue[2] + 1;
					$sumOfStartDays  = ($rent/30) * $startDays;
				}
			}
			if($explodeEndDtValue[2] <= $noOfDaysEnd){
            	// No of days is same consider as One Month
				if($noOfDaysEnd == $explodeEndDtValue[2]){
					$sumOfStartDays  = $rent;
				}
				else{
					$endDays = $explodeEndDtValue[2];
					$sumOfEndDays  = ($rent/30) * $endDays;
				}
              // echo 	 $sumOfEndDays; exit; 
			}
		}
	} 
    //$next_month_ts = strtotime($date1.' +1 month');
    //$prev_month_ts = strtotime($date2.' -1 month');

	$nextStartDt  = date('Y-m-d', strtotime(date('m', strtotime($date1.' +1 month')).'/01/'.date('Y')));
	$prevEndDt    = date('Y-m-d', strtotime($date2.' last day of previous month'));

	$startDateNew   =   new \DateTime($nextStartDt);
	$endDateNew     =   new \DateTime($prevEndDt);

	if ((strtotime($prevEndDt)) > (strtotime($nextStartDt))){

		$interval   = $endDateNew->diff($startDateNew);
		$monthIsOne = $interval->format('%m') + 1;

		$sumOfMonthRent = $monthIsOne * $rent;

		$sumOfMonthRent = $sumOfMonthRent + $sumOfStartDays + $sumOfEndDays; 

	}
	else{

		$sumOfMonthRent = $sumOfMonthRent + $sumOfStartDays + $sumOfEndDays;

	}
	return (float)$sumOfMonthRent;

}

/*
*
*
*  Facility Manager Dashboard Data
*
*
**/
public function  facilityManagerDashboard(Request $request,$dashboard = false){

	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}


	/******    Taken Over not done  ************************/
	if($count_type == 'takenOverNotdone' || $dashboard == true){

		$takenOverNotdone =
		\Modules\BackOffice\Entities\ViewTenantTermination::
		whereIn('work_flow_processes_code', [501,502,503,504])
		->where('termination_type', '=', 1)
		->where('tenant_contract_status',1)
		->where('termination_refer_back', '=', 0)  
		->where(function ($query) {
			$query->whereDate('tenant_contract_valid_to_date', '<', Carbon::now()->subDays(7)->toDateString()) 
			->orWhereDate('termination_date', '<', Carbon::now()->subDays(7)->toDateString());
		})
		->whereHas('terminationUsers', function ($query) {
			$query->where('status','=',1); 
		})
		->get()->count();    

	}


	/******   VIP Calls not closed within 2 days  *********************/
	if($count_type == 'vipCallsNotClosed' || $dashboard == true){

		$vipCallsNotClosed =  ComplaintEnquiry::
		facilityManager()
		->count(); 
	}


	/****** Keys not yet handed over to Sales Team even after 6 days.**********/
	if($count_type == 'keysNotYetHandedOver' || $dashboard == true){ 


		$keysNotYetHandedOver = 
		key::where('status',1)
		->facilityManager()
		->count();                
	}



	return [
	'takenOverNotdone' => $takenOverNotdone,    	
	'vipCallsNotClosed' => $vipCallsNotClosed,    	
	'keysNotYetHandedOver' => $keysNotYetHandedOver,    	
	];



} 
/*
*
*Backoffice Manager starts
*
*/
public function  backofficeManagerDashboard(Request $request,$dashboard = false){
	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        

	}

	/***********   Approval     ******************/

	if($count_type == 'approvalCount' || $dashboard == true){ 


		$roles = \Auth::user()->getRoles();
		$rolesNames = \Auth::user()->getRoleNames()->toArray();
		$tenantRenewal = ViewRenewalUser::areFilter()->pendingApprovals($request)->select('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status')
		->groupBy('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status')->where('work_flow_processes_code', '=', 302)->where('status',1)->where('user_status',1)->renewalUsers()->filter($request);
/*
		if (in_array('super_admin', $rolesNames) === false) {

			$tenantRenewal->where(function ($query) use($roles){
				$query->where('user_id',null)
				->whereIn('role_id', $roles);
			})
			->orWhere(function ($query) use($roles){
				$query->where('user_id','>',0)
				->whereIn('role_id', $roles)
				->where('user_id','=', \Auth::user()->id);
			})->where('work_flow_processes_code', '=', 302);

		}*/


		$approvalCount = $tenantRenewal->get()->count();


		if(!$dashboard)
			return ['count' => $approvalCount ];

	}
	/***********   Number of referrals from ARE     ******************/

	if($count_type == 'plmsApprovalCount' || $dashboard == true){ 

		$roles = \Auth::user()->getRoles();
		$user = \Auth::user();
		$plmsApprovalCount = Legal::filter($request)
		->whereHas('legalUsers', function ($query) use($user,$roles) {
			$query->when( !($user->hasRole('super_admin')),function($query)use($user,$roles){
				$query->where(function ($query) use($user,$roles){
					$query->where('user_id',null)
					->whereIn('role_id', $roles)
					->where('status','=',1);
				})
				->orWhere(function ($query) use($user,$roles){
					$query->where('user_id','>',0)
					->whereIn('role_id', $roles)
					->where('user_id','=', $user->id)
					->where('status','=',1);
				});   
			})
			->where('status','=',1);
		})                  
		->where('legal.work_flow_processes_code', '=', 801)->get()->count();


		if(!$dashboard)
			return ['count' => $plmsApprovalCount ];

	}

	/***********     activeCasesCount     ******************/

	if($count_type == 'activeCasesCount' || $dashboard == true){



$activeCasesCount = Legal::filter($request)
->where('legal_is_closed',0)
->legalUsers()              
->where('legal.work_flow_processes_code', '=', 803)->get()->count();


if(!$dashboard)
return ['count' => $activeCasesCount];

}

	/***********     Cases referred back by Legal     ******************/

	if($count_type == 'referBackCount' || $dashboard == true){ 



		$referBackCount = Legal::where('are_status',1)->areLegalBuilding()
		->filter($request)
		->lawyerReferBack($request)
		->whereHas('legalUsers', function ($query) {
			$query->where('status','=',1);
		}) ->count();


		if(!$dashboard)
			return ['count' => $referBackCount];

	}
	/*********Contracts expired– Within grace period (30 days)******/

	if($count_type == 'gracePeriodCount' || $dashboard == true){



$gracePeriod = 30;
$today = date('Y-m-d');
$nextDays = Carbon::today()->subDays($gracePeriod);

$gracePeriodCount = ViewTenantContract::tenantContract()->areFilter()->directIndirect($request)->whereDate('tenant_contract_valid_to_date','>=',$nextDays)->whereDate('tenant_contract_valid_to_date','<=',$today)->count();


if(!$dashboard)
return ['count' => $gracePeriodCount ];

}

	/*********Contracts expired – Under Penalty (After 30 days)******/

	if($count_type == 'underPenaltyCount' || $dashboard == true){



$underPenalty = 30;
$today = date('Y-m-d');
$nextDays = Carbon::today()->subDays($underPenalty);
$underPenaltyCount = ViewTenantContract::tenantContract()->areFilter()->directIndirect($request)->whereDate('tenant_contract_valid_to_date','<',$nextDays)->count();


if(!$dashboard)
return ['count' => $underPenaltyCount ];

}
	/*********Contracts expiring within the coming 30******/

if($count_type == 'expiringContractsCount' || $dashboard == true){

$today = date('Y-m-d');
$nextExpDays = Carbon::today()->addDays(30);
$expiringContractsCount = ViewTenantContract::tenantContract()->areFilter()->directIndirect($request)->whereDate('tenant_contract_valid_to_date','<',$nextExpDays)->whereDate('tenant_contract_valid_to_date','>=',$today)->where('tenant_renewal_termination_status','!=',6)->count();


if(!$dashboard)
return ['count' => $expiringContractsCount ];

}
	/***********     unregisteredContractsCount     ******************/

	if($count_type == 'unregisteredContractsCount' || $dashboard == true){



$unregisteredContractsCount = ViewTenantContract::tenantContract()->where('tenant_contract_is_reg_municipality',0)->directIndirect($request)->get()->count();


if(!$dashboard)
return ['count' => $unregisteredContractsCount];

}
	/*********Not Registered in  municipality******/

	if($count_type == 'notRegisteredInMunicipalityCount' || $dashboard == true){ 



		$user = \Auth::user();
		$roles = $user->getRoles();
		$rolesNames = $user->getRoleNames()->toArray();
		if($user->hasRole('are_team_lead')){
			$days = 10;
		}elseif($user->hasRole('are')){
			$days = 5;
		}else{
			$days = 0;
		}
		$nextDays = Carbon::today()->subDays($days);

		$notRegisteredInMunicipalityCount = ViewRenewalUser::notRegisteredInMunicipality($request)->select('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
		->groupBy('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_code','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
		->where('work_flow_processes_code', '=', 303)
		->where('tenant_contract_status', 0)
		->where('tenant_renewal_termination_status',5)
		->where('user_status',1)
		->whereDate('created_at','<=',$nextDays)->filter($request)
		->where('tenant_contract_is_reg_municipality','!=',1)
		->areFilter()->get()->count();
		if(!$dashboard)
			return ['count' => $notRegisteredInMunicipalityCount ];

	}
	/*********Referred back******/

	if($count_type == 'referBackContractsCount' || $dashboard == true){ 



		$referBackContractsCount = ViewDueRenewal::areFilter()->where('tenant_contract_status',1)->where('tenant_renewal_termination_status',2)->where('status',1)->where('refer_back_status',1)->get()->count();


		if(!$dashboard)
			return ['count' => $referBackContractsCount ];

	}
	/***********     bouncedChequeCount     ******************/

	if($count_type == 'bouncedChequeCount' || $dashboard == true){ 



		$bouncedChequeCount = Pdc::areFilter()->bouncedCheques()->count();

		if(!$dashboard)
			return ['count' => $bouncedChequeCount ];

	}
	$sumOfReceiptAmount = 0;
	/***********   Receivables beyond 60 days    ******************/
	/*
	
	if($count_type == 'sumOfReceiptAmount' || $dashboard == true){ 

		$days = 60;
		$days = $days-1;
		$nextDays = Carbon::today()->addDays($days);
		$today = date('Y-m-d');
		$tenantContracts = ViewTenantContract::receivables($request)
		->where('tenant_contract_status',1)->where('tenant_contract_valid_to_date','<=',$nextDays)->areFilter()->get();
		
		$totalRentamt = 0;
		$sumOfReceiptAmt = 0;
		foreach ($tenantContracts as $tenantContract) {

			$fromDate = $tenantContract->tenant_contract_effective_date;
			$todate = date('Y-m-d',strtotime($tenantContract->tenant_contract_valid_to_date));

			if($todate >= $nextDays)
				$countMonth = dateDifference($fromDate , $nextDays);
			else
				$countMonth = dateDifference($fromDate , $tenantContract->tenant_contract_valid_to_date);

			$totalRent = $countMonth * $tenantContract->tenant_contract_rent;
			$totalRentamt+= $totalRent;

			$sumOfReceipt   = ReceiptsGeneration::where('receipts_generation_type',0)->where('tenant_contract_id',$tenantContract->id)->where('receipts_generation_status',3)->sum('receipts_generation_amt');
			$sumOfReceiptAmt+= $sumOfReceipt;
		}
		$sumOfReceiptAmount = numberFormat($totalRentamt-$sumOfReceiptAmt);
		

		if(!$dashboard)
			return ['count' => $sumOfReceiptAmount ];

	}
	*/
	if($count_type == 'sumOfReceiptAmount' || $dashboard == true){

$days = 60;
$days = $days-1;
$nextDays = Carbon::today()->addDays($days);
$today = date('Y-m').'-01';

$receivableSum = ViewTenantContract::receivables($request)->areFilter()->where('tenant_contract_status',1)
->select(DB::raw("SUM(extract(month from age(CAST('$nextDays' AS DATE),
(CASE WHEN tenant_contract_last_paid_date  is NOT NULL THEN tenant_contract_last_paid_date
    WHEN tenant_contract_effective_date  is NOT NULL THEN tenant_contract_effective_date
END)
)) * tenant_contract_rent) as recievable"))->first();

//$sumOfReceiptAmount = numberFormat($receivableSum->recievable);
$sumOfReceiptAmount = round($receivableSum->recievable);

if(!$dashboard)
return ['count' => $sumOfReceiptAmount ];

}

	/***Landlord management agreement expiring within 90 days + already expired***/

	if($count_type == 'landlordContractCount' || $dashboard == true){ 

		$today = date('Y-m-d');
		$nextExpDays = Carbon::today()->addDays(90);
		$landlordContractCount = LandlordContract::filter($request)
		->whereIn('landlord_contract_status',[1,2,3])
		->where('management_id',1)
		->where(function($query)use($nextExpDays,$today) {
			$query->whereDate('landlord_contract_valid_to_date','<', $nextExpDays)
			->orWhere('landlord_contract_valid_to_date','<',$today);
		})->get()->count();


//dd($landlordContractCount);
		if(!$dashboard)
			return ['count' => $landlordContractCount ];

	}

	/******    Taken Over not done  ************************/
	if($count_type == 'takenOverNotdone' || $dashboard == true){

		$takenOverNotdone =
		\Modules\BackOffice\Entities\ViewTenantTermination::
		whereIn('work_flow_processes_code', [501,502,503,504])
		->where('termination_type', '=', 1)
		->where('tenant_contract_status',1)
		->where('termination_refer_back', '=', 0)  
		->where(function ($query) {
			$query->whereDate('tenant_contract_valid_to_date', '<', Carbon::now()->subDays(7)->toDateString()) 
			->orWhereDate('termination_date', '<', Carbon::now()->subDays(7)->toDateString());
		})
		->whereHas('terminationUsers', function ($query) {
			$query->where('status','=',1); 
		})
		->get()->count();    
		
	}

	/*********Closed Cases Count******/

	if($count_type == 'closedCasesCount' || $dashboard == true){ 



		$closedCasesCount = Legal::filter($request)
		->whereHas('legalNotes',function ($query){
			$query->where('legal_notes_status',0);
		})->whereHas('legalUsers', function ($query) use($roles,$user) {
			$query->when( !($user->hasRole(['super_admin','backoffice_manager'])), function($query) use($roles){
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
		->where('legal.work_flow_processes_code', '=', 803)->count();


		if(!$dashboard)
			return ['count' => $closedCasesCount ];

	}


	
	return [
	'approvalCount'=>$approvalCount,
	'plmsApprovalCount'=>$plmsApprovalCount,
	'activeCasesCount'=>$activeCasesCount,
	'referBackCount'=>$referBackCount,
	'gracePeriodCount'=>$gracePeriodCount,
	'underPenaltyCount'=>$underPenaltyCount,
	'expiringContractsCount'=>$expiringContractsCount,
	'unregisteredContractsCount'=>$unregisteredContractsCount,
	'notRegisteredInMunicipalityCount'=>$notRegisteredInMunicipalityCount,
	'referBackContractsCount'=>$referBackContractsCount,
	'bouncedChequeCount'=>$bouncedChequeCount,
	'sumOfReceiptAmount'=>$sumOfReceiptAmount,
	'landlordContractCount'=>$landlordContractCount,
	'takenOverNotdone'=>$takenOverNotdone,
	'closedCasesCount'=>$closedCasesCount
	];

}
	/*
*
*Backoffice Manager ends
*
*/



/*****Taken Over not done    For   Facility Manager   Grid *****/
public function takenOverNotdoneForFacilityManager(Request $request){


	$enquiry_fields = [
	'tenant_contract_no' => 'Contract No',
	'building_name' => 'Building',
	'unit_code' => 'Unit',         
	'tenant_name' => 'Tenant',     
	];


	$operations = [
	'=' => ' Is equal to '  ,
	'!=' => ' Is not equal to '  ,
	'>' => ' Is greater than '  ,
	'>=' => ' Is greater than or equal to '  ,
	'<' => ' Is less than '  ,
	'<=' => ' Is less than or equal to'  ,
	'ilike' => ' Like '  ,
	'ilike%...%' => ' Like%...% ',
	];
	$roles = \Auth::user()->getRoles();
	$rolesNames = \Auth::user()->getRoleNames()->toArray();


	$request->flash();


	$quick_url =  $route   = route('takenOverNotdoneForFacilityManager');


	$tenantTerminations =
	\Modules\BackOffice\Entities\ViewTenantTermination::
	whereIn('work_flow_processes_code', [501,502,503,504])
	->where('termination_type', '=', 1)
	->where('tenant_contract_status',1)
	->where('termination_refer_back', '=', 0)  
	->where(function ($query) {
		$query->whereDate('tenant_contract_valid_to_date', '<', Carbon::now()->subDays(7)->toDateString()) 
		->orWhereDate('termination_date', '<', Carbon::now()->subDays(7)->toDateString());
	})
	->whereHas('terminationUsers', function ($query) {
		$query->where('status','=',1); 
	})->filter($request)
	->paginate($this->noOfRecord); 



	if(isset($request->ajax))
		return view('backoffice::Termination.tenant_termination_takenover_list_ajax',compact('tenantTerminations','request','route'));

	return view('backoffice::Termination.tenant_termination_takenover_list',compact('tenantTerminations','enquiry_fields','operations','quick_url'));

}  
/*
	*Tenant Vacating
	*/
	public function tenantVaccating(Request $request){
$vaccatingdays = $request->vaccatingdays;
$tenantContracts = ViewTenantContract::tenantContract()->areFilter()->directIndirect($request)->tenantVacatting($request)->count();
$tenantVaccatingCount =   ['count' => $tenantContracts];
return json_encode(array($tenantVaccatingCount));
}

/* MD Dashboard-Starts */
public function  mdDashboard(Request $request,$dashboard = false){


//new for MD-Start

	$count_type = $count_query = '';
	$count = 0;

	if(isset($request->count_type)){        
		$count_type = explode('_', $request->count_type);
		$count_type = $count_type[0];	  

		if(isset($request->count_val)) 
			$count = $request->count_val;        
	}

	if($dashboard == true){ 

		//***********  Total no of buildings under management  ***********/
		$totalBuildings = Building::where('building_status',1)->count();
		//vacant unit movement
		$vacantMovData = get_object_vars(json_decode($this->getVacantUnitMovementSchedule('M')));
		$vacantBuildingsSc = $vacantMovData['vacantBuildingsSc'];
		$vacantUnitsSc = $vacantMovData['vacantUnitsSc'];
		//
 		//***********  comprehensive units  ***********/
		$comprehensiveUnits = 
			Unit::whereHas('building',function($query) {
				$query->where('management_id',1)
				-> where('unit_status',1)
				-> where ('building_status',1);
			})->count();	

		/***********  normal units  ***********/
		$normalUnits = 
			Unit::whereHas('building',function($query) {
				$query->where('management_id',2)
				-> where('unit_status',1)
				-> where ('building_status',1);
			})->count();

		
		/*******  vacated units MTD ************/

		$today = Carbon::today();//returns current day
		$firstDay = $today->firstOfMonth(); 

		
		$vacatedUnitsMTD = ViewTenantContract::tenantContract()->areFilter()->directIndirect($request)
		->tenantVacatting($request)
		->whereDate('tenant_contract_valid_to_date','>=',$firstDay)
		->whereDate('tenant_contract_valid_to_date','<=',$today)
		->count();

		/*******   Collection MTD  *******/
		$collectionMTD = 
				ReceiptsGeneration::whereDate('receipts_generation_receipt_date','>=', Carbon::today()->firstOfMonth())
				->whereDate('receipts_generation_receipt_date','<=',Carbon::today())
				//->where('receipts_generation_status','!=',3)
				->where('receipts_generation_type',0)
				->where('deleted_at',null)
				->sum('receipts_generation_amt');
		 




		/*******  vacant comprehensive units  *******/
		$vacantComprehensiveUnits = 
			Unit::where('unit_vaccant_status',0)
			->whereHas('building',function($query) {
				$query->where('management_id',1)
				-> where ('building_status',1)
				-> where ('unit_status',1);
			})->count();

		/*******   vacant normal units   *******/
		$vacantNormalUnits = 
			Unit::where('unit_vaccant_status',0)
			->whereHas('building',function($query) {
				$query->where('management_id',2)
				-> where ('building_status',1)
				-> where ('unit_status',1);
			})->count();

	}
		/*******   bounced cheques MTD   *******/
		if($count_type == 'bouncedChequeCount' || $dashboard == true){ 

			$bouncedChequeCount = Pdc::where('pdc_cancel_reason',1)
									->whereDate('pdc_check_date','>=', Carbon::today()->firstOfMonth())
									->whereDate('pdc_check_date','<=',Carbon::today())
									->count();

			if(!$dashboard)
			return ['count' => $bouncedChequeCount ];

		}

		/*******  unassignedEnquiry     **************/

		if($count_type == 'unassignedEnquiry' || $dashboard == true){

			$unassigned_list = \Modules\Sales\Entities\ViewTenantStage::
			where('sale_work_flow_processes_code', '=', 101)			
			->where('status', '=', 1)
			->select('sales_enquiry_id')		     
			->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no")
			->when(($count > 0),function($query)use($count){
				$query->whereDate('created_at', '<=', Carbon::now()->subDays($count)->toDateString());
			})
			->when(( $dashboard == true),function($query)use($count){
				$query->whereDate('created_at', '<=', Carbon::now()->subDays(2)->toDateString());
			})
			// ->salesUsers()
			->get()->count();

			if(isset($request->count_val)){         	
				$count_query = 'count_by_day='.$count;
			}		      

			if(!$dashboard)
				return ['count' => $unassigned_list, 'href' => route('leadAssign.index').'?'.$count_query ];

		}

	// echo "<pre>";
 //    print_r($unassigned_list);
 //    echo "</pre>";
 //    die;

		/*************   assignedEnquiry     *****************/
		
		
		if($count_type == 'assignedEnquiry' || $dashboard == true){ 

		$assigned_lists = \Modules\Sales\Entities\ViewTenantStage::
		where('sale_work_flow_processes_code', '=', 102)
		->where('work_flow_processes_code', '=', 102)
		->where('status', '=', 1)
		->select("sales_enquiry_id")
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no","employee_name")
		->when(($count > 0),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays($count)->toDateString());
		})
		->when(( $dashboard == true),function($query)use($count){
			$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays(3)->toDateString());
		})
		//->salesUsers()
		->get()->count();	


		if(isset($request->count_val))        	
			$count_query = 'count_by_assignday='.$count;


		if(!$dashboard)
			return ['count' => $assigned_lists, 'href' => route('leadAssign.assignedList').'?'.$count_query ];


	}
		
		//if($count_type == 'assignedEnquiry' || $dashboard == true){ 

			//$assigned_lists = \Modules\Sales\Entities\ViewTenantStage::
			//where('sale_work_flow_processes_code', '=', 102)
			//->where('work_flow_processes_code', '=', 102)
			//->where('status', '=', 1)
			//->select("sales_enquiry_id")
			//->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no","employee_name")
			//->when(($count > 0),function($query)use($count){
				//$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays($count)->toDateString());
			//})
			//->when(( $dashboard == true),function($query)use($count){
				//$query->whereDate('sales_created_at', '<=', Carbon::now()->subDays(14)->toDateString());
			//})
			// ->salesUsers()
			//->get()->count();	


			//if(isset($request->count_val))        	
				//$count_query = 'count_by_assignday='.$count;


			//if(!$dashboard)
				//return ['count' => $assigned_lists, 'href' => route('leadAssign.assignedList').'?'.$count_query ];


		//}

		/***********   Number of referrals from ARE     ******************/
	/**********   Legal Cases movement    ****************/
	if($count_type == 'legalCaseMovement' || $dashboard == true){ 

		$tenantStatus = 5;
		if(isset($request->selected_status)){

			$status = (trim($request->selected_status)=='close')?802:(($request->selected_status=='reject')?804:801);
			if($status=='close')
				$tenantStatus = 0;
		}
		else
			$status = 801;

		$plmsApprovalCount = DB::table('legal as l')->select('l.tenant_contract_id')
							->join('tenant_contracts as tc', function($join) use($tenantStatus) {
								$join->on('tc.id', '=', 'l.tenant_contract_id')
								->where('tc.status',$tenantStatus);
							})
							->where('l.work_flow_processes_code', '=', $status)
							->count();
		if(!$dashboard){

			return ['count' => $plmsApprovalCount ];
		}


	}
	/**********   Legal Cases Status    ****************/
	if($count_type == 'legalCaseStatus' || $dashboard == true){ 

		$roles = \Auth::user()->getRoles();
		$user = \Auth::user();
		$status = 803; // Contract in Legal Case. Lawyer accept the case
		if(isset($request->selected_status)){

			switch ($request->selected_status){

				case 'won':
					$status = 803;
					break;
				case 'loss':
					$status = 801;
					break;	
				case 'reject':
					$status = 804;
					break;	
				case 'inprogress':
					$status = 802;
					break;
				default:
					$status = 803;
					break;
			}
		
		}
		$plmsLegalStatus = DB::table('legal as l')->select('l.tenant_contract_id')
							->join('tenant_contracts as tc', function($join) {
								$join->on('tc.id', '=', 'l.tenant_contract_id')
								->where('status',5);
							})
							->where('l.work_flow_processes_code', '=', $status)
							->count();
		if(!$dashboard){

			return ['count' => $plmsLegalStatus ];
		}

	}
		/***********  Maintenance Pending beyond 4 days   ************/
		
		if($count_type == 'maintenance' || $dashboard == true){

		$maintenancePendingCount = \Modules\Maintenance\Entities\ComplaintEnquiry::
		where('complaint_status','!=',2)
		->whereDate('created_at', '<=', Carbon::now()->subDays(4)->toDateString())
		->get()->count();                     

	}

		/*********   Tenant Contracts expired as on date  ******/

		if($count_type == 'gracePeriodCount' || $dashboard == true){							 
			
			$today = date('Y-m-d');

			$gracePeriodCount = \Modules\BackOffice\Entities\ViewTenantContract::
		tenantContract()->directIndirect($request)->where('tenant_contract_status',1)
			->whereDate('tenant_contract_valid_to_date', '<', Carbon::now()->toDateString())
			->select('tenant_contract_no')
			->get()
			->count();


			if(!$dashboard)
				return ['count' => $gracePeriodCount ];

		}
		
		

		/*********  Landlord Contracts expired as on date   ******/
		if($dashboard == true){							 
			
			$today = date('Y-m-d');

			$landlordContractsExpired = LandlordContract::where('landlord_contract_status','=',1)
			->whereDate('landlord_contract_valid_to_date','<=',$today)
			->where('management_id','=',1)
			->where('landlord_renewal_termination_status', '!=',6)
			->count();
			
			

			if(!$dashboard)
				return ['count' => $landlordContractsExpired ];

		}

		/************  Receivables As on a specific date ARE wise ************/

	if($count_type == 'receivables' || $dashboard == true){


		$areUser = \Modules\Masters\Entities\AreBuildingAssign::   with('areUser.employee')
		                             ->whereHas('buildingNamesExist')
		                             ->first()->user_id;

		                             

		$nextDays = Carbon::today();
		$receivable_date = date('Y-m-d');
		if(isset($request->count_val)){
		$areUser = $request->count_val;
		$receivable_date = $request->receivable_date;
		}

		$today = date('Y-m-d');
		$receivableSum = \Modules\BackOffice\Entities\ViewTenantContract::
		whereDate('tenant_contract_effective_date','<=',$today)
		->whereDate('tenant_contract_valid_to_date','>=',$today)
		->whereHas('building', function ($query)use($areUser){      
		$query->whereHas('areBuildings', function ($query)use($areUser){
		$query->where('user_id','=', $areUser);
		});
		})
		->where('tenant_contract_status',1)
		->select(DB::raw("SUM(extract(month from age(CAST('$nextDays' AS DATE),
		(CASE WHEN tenant_contract_last_paid_date  is NOT NULL THEN tenant_contract_last_paid_date
		    WHEN tenant_contract_effective_date  is NOT NULL THEN tenant_contract_effective_date
		END)
		)) * tenant_contract_rent) as recievable"))->first();

		//$receivables = numberFormat($receivableSum->recievable);
		$receivables = round($receivableSum->recievable);



		if(!$dashboard)
		return ['count' => $receivables, 'href' => route('receivables').'?areUser_id='.$areUser.'&receivable_date='.$receivable_date ];

		}

		/********** Vacated units MTD-Starts **********/	

		$thirtyDays = Carbon::today()->subDays(30);
		$sixetyDays = Carbon::today()->subDays(60);
		$ninetyDays = Carbon::today()->subDays(90);

	/*******************  Vacant units  **************************/
		


	/********** Vacated units MTD-Ends**********/

		if($count_type == 'vacantUnits' || $dashboard == true){

			$days = [30,60,90,120];

			foreach ($days as $day) {
	////////////comprehensive  /////////////////////////
				$comprehensive[$day] = 
				Unit::where('unit_vaccant_status',0)
				->whereHas('building',function($query) {
					$query->where('management_id',1);
				})
				->orWhere(function($query)use($day) {		             
					$query->where('unit_vaccant_status',0)
					->whereHas('tenantContract', function($query) use($day){
						$query->whereDate('tenant_contract_valid_to_date', '<=', Carbon::now()->subDays($day)->toDateString())
						->where('tenant_contract_status',0)
						->doesntHave('tenantContractOld');
					});
				})
				->count();	

	/////////////////normal //////////////////////////////////
				$normal[$day] = 
				Unit::where('unit_vaccant_status',0)
				->whereHas('building',function($query) {
					$query->where('management_id',2);
				})
				->orWhere(function($query)use($day) {
					$query->where('unit_vaccant_status',0)
					->whereHas('tenantContract', function($query) use($day){
						$query->whereDate('tenant_contract_valid_to_date', '<=', Carbon::now()->subDays($day)->toDateString())
						->where('tenant_contract_status',0)
						->doesntHave('tenantContractOld');
					});
				})->count();

			}


			$vacantUnits['comprehensive']  = $comprehensive;
			$vacantUnits['normal']  = $normal;	 

		}


		/******    Won Deals – MTD   ************/
	if($count_type == 'wonDealsMTD' || $dashboard == true){

		$wonDealsMTD = \Modules\Sales\Entities\ViewTenantStage::
		where('work_flow_processes_code', '=',108)
		->where('sale_work_flow_processes_code','=',108)
		->where('status','=',1)
		->where('sales_enquiry_direct_contract','=',1)	
		->select("sales_enquiry_id")	     
		->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name","employee_name","tenant_contract_duration","unit_type_name","tenant_name","unit_usage")			     
		->when(( $dashboard == true),function($query)use($count){
			$query->whereDate('tenant_contract_start_date','>=', Carbon::today()->firstOfMonth())
			->whereDate('tenant_contract_start_date','<=',Carbon::today());
		})
		->get()->count();    

	}	


	//Early Termination Requests
	 $tenantTerminations = ViewTenantTermination::
        where('work_flow_processes_code', '=', 505)
        ->where('termination_type', '=', 1)
      //->where('tenant_contract_status',1)
      ->where('tenant_renewal_termination_status',8)   
      ->where('termination_type_status','=',3) //premature
	  ->whereDate('termination_date','>=', Carbon::today()->firstOfYear())
      ->count(); 

	// return [
	// 	'normalUnits'=>$normalUnits,
	// 	'comprehensiveUnits'=>$comprehensiveUnits
	// ];

	//new for MD-Ends


		/********  vacantUnits  ****************/

		// if($count_type == 'vacantUnits' || $dashboard == true){ 

		// 	$vacantUnits =
		// 	\Modules\Masters\Entities\Unit::where('unit_vaccant_status',0)
		// 	->where('unit_status',1)
		// 	->get()
		// 	->count();

		// 	$count_query = 'unit_vaccant_status='.$count;


		// 	if(!$dashboard)
		// 		return ['count' => $vacantUnits, 'href' => route('unit.index') ];

		// }

		
		
		/********** SalesPerson Performance Analysis **********/
		$sales_person_user_list = User::role("sales_person")->get();
		//dd($sales_person_user_list);
		foreach($sales_person_user_list as $key=>$list){

			if($list->employee->id) 
				$userList[] = $list->employee->employee_name;
			
			//assigned
			$userstageList['assigned'][$key] = $units = DB::table('sales')
			->join('sales_users', 'sales_users.sales_id', '=', 'sales.id')
			->join('sales_enquiries', 'sales_enquiries.id', '=', 'sales.sales_enquiry_id')
			->where('sales.sales_type','=',1)->where('sales.work_flow_processes_code','=',102)->where('sales_enquiry_direct_contract','=',1)->where('user_id','=',$list->id)
			->get()->count();
			

			//won
			$userstageList['won'][$key] = $units = DB::table('sales')
			->join('sales_users', 'sales_users.sales_id', '=', 'sales.id')
			->join('sales_enquiries', 'sales_enquiries.id', '=', 'sales.sales_enquiry_id')
			->where('sales.sales_type','=',1)->where('sales.work_flow_processes_code','=',108)->where('sales_enquiry_direct_contract','=',1)->where('assigned_person','=',$list->id)
			->get()->count();	

			$userstageList['null_value'][$key] = 0;
		}

		$sale['name']= $userList;
		$sale['value']= $userstageList;

		/********** SalesPerson Performance Analysis End**********/	



		/**********  Total Hit ratio  **************/
		$assignedEnquiries = $units = DB::table('sales')
		->join('sales_users', 'sales_users.sales_id', '=', 'sales.id')
			->join('sales_enquiries', 'sales_enquiries.id', '=', 'sales.sales_enquiry_id')
			->where('sales.sales_type','=',1)->where('sales.work_flow_processes_code','=',102)->where('sales_enquiry_direct_contract','=',1)
			->get()->count();

		$wonEnquiries = $units = DB::table('sales')
		->join('sales_users', 'sales_users.sales_id', '=', 'sales.id')
			->join('sales_enquiries', 'sales_enquiries.id', '=', 'sales.sales_enquiry_id')
			->where('sales.sales_type','=',1)->where('sales.work_flow_processes_code','=',108)->where('sales_enquiry_direct_contract','=',1)
			->get()->count();

		$hitRatio = ($wonEnquiries/$assignedEnquiries)*100;



		/*************  No of units in each Location  *****************/
		$locations = Location::get();
		foreach($locations as $key=>$list){

			if($list->id) 
				$locationList[] = $list->locations_name;
			
			 // $buidingCount[$key] =  Building::areFilter()
     		//				->where('location_id','=',$list->id)
     		//				->get()->count();

   			//$buidingCount[$key] = Unit::whereHas('building',function($query) {
			// 	$query->where('location_id','=',$list->id);
			// })->count();

    		$unitCount['unit_count'][$key]= DB::table('buildings')
			->join('units', 'units.building_id', '=', 'buildings.id')
			->where('buildings.location_id','=',$list->id)
			->get()->count();

			$unitCount['null_value'][$key] = 0;
			
		}

		$unit['location']= $locationList;
		$unit['value']= $unitCount;

		


		/*************  Buildings per ARE  ****************/
		 $areBuildingAssigns = AreBuildingAssign::whereHas('buildingNamesExist')
		  						->get();
		 
		 foreach($areBuildingAssigns as $key=>$list){

		 	if($list->user_id) 
		 	{
		 		$areList[] = $list->areUser->employee->employee_name;
		 	}

		 	// $buildingCount['building_count'][$key] = $list->assignedBuildingNames[0]['building_name'];
		 	// $buildingCount['building_count'][$key] = $list->assignedBuildingNames->implode('building_name',' ,   ');
		 	$buildingCount['building_count'][$key] = $list->assignedBuildingNames->count();
		 	

		 	$buildingCount['null_value'][$key] = 0;

		 }

		$buildingsPerARE['are']= $areList;
		$buildingsPerARE['value']= $buildingCount;
		
		/******************Units Per ARE ***********/
		$areUnitss = AreBuildingAssign::whereHas('buildingNamesExist')
		  						->get();
		 //echo "<pre>";print_r($areUnitss);exit;
		 foreach($areUnitss as $key=>$list){

		 	if($list->user_id) 
		 	{
		 		$areList_unit[] = $list->areUser->employee->employee_name;
		 	}
		 	foreach($list->assignedBuildingNames as $assignedBuilds){
		 		$buildingIdAre[$key][] = $assignedBuilds->id;
		 	}
		 	//echo "<pre>";print_r($buildingIdAre);exit;
		 	$getUnits = Unit::where('Unit_Status','1')->whereIn('building_id',$buildingIdAre[$key])->count();
		 	$unitsCount['unit_count'][$key] = $getUnits;
		 	$unitsCount['null_value'][$key] = 0;
		 }
			//echo "<pre>";print_r($unitsCount);exit;
		 	
		$unitsPerARE['are']= $areList_unit;
		$unitsPerARE['value']= $unitsCount;



	
	/*************   Sources of enquiry  ***************/

		$enquiry_source = EnquirySource::get();
		
		foreach($enquiry_source as $key=>$list){

			if($list->id) 
				$enquirysourceList[] = $list->enquiry_sources_name;
			
		
			$enquiryCount['enquiry_source_count'][$key]  = DB::table('sales_enquiries')
            ->select('sales_enquiries.id as enquiry_id','sales_enquiries.sales_mode_id','enquiry_sources.enquiry_sources_name')
            ->join('enquiry_sources', 'enquiry_sources.id', '=', 'sales_enquiries.sales_mode_id')
            ->where('sales_enquiries.sales_mode_id','=',$list->id)

            //for current year count
            // ->whereDate('sales_enquiries.created_at','>=', Carbon::today()->firstOfYear())
            // ->whereDate('sales_enquiries.created_at','<=', Carbon::today()->LastOfYear())
            
            ->get()->count();

			$enquiryCount['null_value'][$key] = 0;
			
		}

		$enquirySource['enquiry_source']= $enquirysourceList;
		$enquirySource['value']= $enquiryCount;


		/********  Average Rent-starts  ******/
		/*********Avg Receivables******/
		$averageReceivables = 0;
		$locations = Location::get();
		foreach($locations as $key=>$list){

			if($list->id) 
				$areaList[] = $list->locations_name;
			
			$tenantContracts = ViewTenantContract::
			whereDate('tenant_contract_valid_to_date','>=',$today)
			->where('status','!=',5)->where('tenant_contract_status',1)
			->select(DB::raw("SUM((extract(month from age(now(),
				(CASE 
					WHEN tenant_contract_last_paid_date  > now()  THEN now()
					WHEN tenant_contract_last_paid_date  is NOT NULL THEN tenant_contract_last_paid_date
				     WHEN tenant_contract_effective_date  is NOT NULL THEN tenant_contract_effective_date
				END)))) * tenant_contract_rent)					
				as recievable"),DB::raw("count(id) as avgcnt"))
			
			// ->areFilter()
			->where(function ($query) {
				$query->where('unit_is_legal',2)
	            ->orWhereNull('unit_is_legal');
			})

			// ->where('building_id',$list->id)

			->whereHas('building',function($query) use ($list) {
					$query->where('location_id','=',$list->id);
				})

			->first();

			if($tenantContracts->recievable && $tenantContracts->avgcnt != 0)
			{
				$averageReceivableAmt = floatval($tenantContracts->recievable)/intval($tenantContracts->avgcnt);
				$averageReceivables = round($averageReceivableAmt);

				// $avgRent['tenantContracts'][$key] = $averageReceivables;
				$avgRent['averageReceivables'][$key] = $averageReceivables;

				$avgRent['null_value'][$key] = 0;
			}
			
		}

		$rent['area']= $areaList;
		$rent['value']= $avgRent;
 

	
		// $enquiries  = DB::table('sales_enquiries')
  //           ->select('sales_enquiries.id as enquiry_id','sales_enquiries.sales_mode_id','enquiry_sources.enquiry_sources_name')
  //           ->join('enquiry_sources', 'enquiry_sources.id', '=', 'sales_enquiries.sales_mode_id')
  //           ->get();

 	
		


		return [ 

		'vacantUnits'=>$vacantUnits,
		'sales_person_list'=>$sale,
		'normalUnits'=>$normalUnits,
		'comprehensiveUnits'=>$comprehensiveUnits,
		'totalBuildings'=>$totalBuildings,
		'bouncedChequeCount'=>$bouncedChequeCount,
		'unassigned_list'=>$unassigned_list,
		'assigned_lists'=>$assigned_lists,
		'plmsApprovalCount'=>$plmsApprovalCount,
		'plmsLegalStatus'=>$plmsLegalStatus,
		'maintenancePendingCount'=>$maintenancePendingCount,
		'gracePeriodCount'=>$gracePeriodCount,
		'landlordContractsExpired'=>$landlordContractsExpired,
		'vacatedUnitsMTD'=>$vacatedUnitsMTD,
		'receivables'=>$receivables,
		'collectionMTD'=>round($collectionMTD),
		'vacantComprehensiveUnits'=>$vacantComprehensiveUnits,
		'vacantNormalUnits'=>$vacantNormalUnits,
		'wonDealsMTD'=>$wonDealsMTD,
		'assignedEnquiries'=>$assignedEnquiries,
		'wonEnquiries'=>$wonEnquiries,
		'hitRatio'=>round($hitRatio),
		'location_list'=>$unit,
		'tenantTerminations'=>$tenantTerminations,
		'buildingsPerARE'=>$buildingsPerARE,
		'unitsPerARE'=>$unitsPerARE,
		'enquirySource'=>$enquirySource,
		'rent'=>$rent,
		'vacantUnitsSc'=>$vacantUnitsSc,
		'vacantBuildingsSc'=>$vacantBuildingsSc
		
		];

		


}
public function getVacantUnitMovementSchedule($type = null){
	   
	$now = Carbon::now();
	$currYear = $now->year;
	$currMonth = $now->month;
	$currMonth = '08';
	($type == '') ? $type = 'M' : '';

	$qryBuild = "select count(b.building_name) from landlord_contract lc
	left join buildings b on b.id = lc.building_id
	where landlord_contract_old_no is null
	and b.building_status = '1'
	and landlord_contract_valid_from_date between '";
	$qryUnit = "select count(u.unit_no) from landlord_contract lc
	left join buildings b on b.id = lc.building_id
	left join units u on b.id = u.building_id
	where landlord_contract_old_no is null
	and unit_status = '1'
	and landlord_contract_valid_from_date between '";
	if($type == 'M'){
		$fromDateQ = $currYear.'-'.$currMonth.'-'.'01';
		$toDateQ = $currYear.'-'.$currMonth.'-'.'31';
		$qryBuild .= $fromDateQ.'\''.' AND \''.$toDateQ.'\'';
		$qryUnit .= $fromDateQ.'\''.' AND \''.$toDateQ.'\'';
	}
	elseif ($type == 'Y') {
		$fromDateQ = $currYear.'-01-01';
		$toDateQ = $currYear.'-12-31';
		$qryBuild .= $fromDateQ.'\''.' AND \''.$toDateQ.'\'';
		$qryUnit .= $fromDateQ.'\''.' AND \''.$toDateQ.'\'';
	}

	$buildCount = DB::select(DB::raw($qryBuild));
	$unitCount = DB::select(DB::raw($qryUnit));
 
    return json_encode(array('vacantBuildingsSc'=>$buildCount[0]->count,'vacantUnitsSc'=>$unitCount[0]->count));

}

/* MD Dashboard-Ends */
	
}
