<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\BackOffice\Entities\ViewTenantTermination;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Entities\TerminationDocument;
use Modules\BackOffice\Entities\TerminationChecklist;
use Modules\Sales\Entities\TenantContract;
use Modules\Sales\Entities\Tenant;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\UnitType;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\Work;
use Modules\Masters\Entities\SubWork;
use Modules\BackOffice\Entities\Invoice;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Entities\TenantInvoiceDimension;
use Modules\BackOffice\Entities\Renewal;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\AccountParams;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Entities\TenantLandlordTerminationRenewalComment;
use Modules\Masters\Entities\VacantVacancyLoss;
use Session;
use DB;
use Image;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use App\User;
use Spatie\Permission\Models\Role; 
use Modules\General\Http\Controllers\GeneralController as General;
use Modules\BackOffice\Http\Controllers\TenantRenewalController as ContractRenewal ;
use Modules\BackOffice\Events\TenantTermination;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TerminationTenantEmail;
use Modules\BackOffice\Emails\InspectionSendEmail;
use Modules\BackOffice\Events\TenantTerminationReferBack;

class TenantTerminationController extends Controller
{
  public function __construct()
  {
   $this->middleware('auth');  
   $this->middleware('permission:open_for_termination_list', ['only' => ['index']]);
   $this->middleware('permission:termination_approval_list', ['only' => ['tenantTerminationApproval','tenantTerminationApprovalView']]);
   $this->middleware('permission:tenant_early_termination', ['only' => ['edit','update','store','create']]); 
   $this->middleware('permission:handover_unassigned_list', ['only' => ['handoverUnassigned','handoverUnassignedView']]);
   $this->middleware('permission:handover_assigned_list', ['only' => ['handoverAssigned','handoverAssignedView']]);
   $this->middleware('permission:takenover_termination_list', ['only' => ['takeoverForTermination','takeoverForTerminationView']]);
   $this->middleware('permission:terminated_contract_list', ['only' => ['tenantTerminatedContract','tenantTerminatedContractView']]);
   /*$this->middleware('permission:termination_handover', ['only' => ['tenantTerminationStage']]);*/
   $this->middleware('permission:handover_assign', ['only' => ['groupAssignModal','storeGroupAssign']]);
   $this->middleware('permission:termination_inspection_create', ['only' => ['handoverAssignedInspection','handoverAssignedInspectionEdit','terminationInspectionStore','terminationInspectionUpdate']]);  

   $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value;    
 }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
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

      //dd($rolesNames);

      $request->flash();
      

      $quick_url = $route = route('tenantTermination.index');

       $tenantTerminations = ViewTenantTermination::
        where('work_flow_processes_code', '=', 501)
      ->where('termination_type', '=', 1)
      ->where('tenant_contract_status',1)
      ->where('termination_type_status','!=',4)
      ->where('tenant_renewal_termination_status',7)       
      ->filterUsers()
      ->filter($request)
    //  ->distinct()
      ->sortable()->paginate($this->noOfRecord); 
    // dd($tenantTerminations);

//       $tenantTermination = Termination::whereHas('tenantContract', function ($query) use($rolesNames,$request){
//         $query->where('tenant_contract_status',1)
//         ->where('tenant_renewal_termination_status',7)->areFilter()
//         ->filter($request)
//         ->sortable();
//       })->whereHas('terminationUsers', function ($query) {
//         $query->where('status','=',1);
//       })                             
//       ->where('work_flow_processes_code', '=', 501)
//       ->where('termination_type', '=', 1);

//       if (in_array('super_admin', $rolesNames) === false) {

//         if (in_array(['are','are_team_lead'], $rolesNames) != true) {

//           $tenantTermination->whereHas('terminationUsers', function ($query) use($roles) {
//             $query->where(function ($query) use($roles){
//               $query->where('user_id',null)
//               ->whereIn('role_id', $roles);
//             })
//             ->orWhere(function ($query) use($roles){
//               $query->where('user_id','>',0)
//               ->whereIn('role_id', $roles)
//               ->where('user_id','=', \Auth::user()->id);
//             })                      
//             ->where('status','=',1);                       
//           });
//         }
//       }
//       $tenantTerminations = $tenantTermination->sortable()->paginate($this->noOfRecord);
// //dd($tenantTerminations);

      if(isset($request->ajax))
       return view('backoffice::Termination.tenant_termination_open_list_ajax',compact('tenantTerminations','request','route'));

     return view('backoffice::Termination.tenant_termination_open_list',compact('tenantTerminations','enquiry_fields','operations','quick_url'));

   }



    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    { 
      /*$tenantContract = TenantContract::where('id',146)->first();*/
      return view('backoffice::Termination.early_tenant_termination_create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
      $this->validate($request, [
        'contract_id' => 'required',                    
        ]);
      $contractId = $request->contract_id;
	  $unit_id = $request->unit_id;
      // Outstanding Creation 
      $tenantContract = TenantContract::where('id',$contractId)->first();
      $today      = date('Y-m-d');
      $osAmount   = 0 ;
	  $terminationDate      = $request['termination_date'];
      // Outstanding Creation 
      $sumOfReceipt   = ReceiptsGeneration::where('receipts_generation_type',0)->where('tenant_contract_id',$contractId)->where('receipts_generation_approval_status',3)->sum('receipts_generation_amt');
      //$remainAmt = contractRentCountCalculation($tenantContract->tenant_contract_effective_date,$today, $tenantContract->tenant_contract_rent );
	  $remainAmt = totalContractRentCountCalculation($contractId,$terminationDate);
      // Minus from Rent receipt
      $osAmount = $remainAmt - $sumOfReceipt;
      $tenant_contract_last_paid_date = $tenantContract->tenant_contract_last_paid_date;
      if(empty($tenant_contract_last_paid_date)){
        $tenant_contract_last_paid_date = $tenantContract->tenant_contract_effective_date;
        $paymentTerm = $tenantContract->tenant_contract_payment_type;
        switch($paymentTerm){
          case 1:
          $month = 1;
          break;
          case 2:
          $month = 2;
          break;
          case 3:
          $month = 3;
          break;
          case 4:
          $month = 6;
          break;
          case 5:
          $month = 12;
          break;
        }
        $futurePaymentDate = $tenant_contract_last_paid_date->addMonths($month)->format('Y-m-d');
        if($futurePaymentDate < date('Y-m-d')){
          $os = 'Yes' ;
        }else{
          $os = 'No' ;
        }

      }
      else{

        if($tenant_contract_last_paid_date < date('Y-m-d H:i:s')){
          $os = 'Yes' ;
        }else{
          $os = 'No' ;
        }

      }
      $currentUrl =  Session::get('current');
      $general =  new General;
      $next_process_id = 501;
      $termination_remark = $request['termination_remark'];
      $termination_takenover_date = $request['termination_takenover_date'];
      $termination_date = $request['termination_date'];
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);
      
      $termination = new Termination; 
      $termination->contract_id     = $contractId;
      $termination->termination_type  = 1;
      $termination->termination_type_status  = 1;
      $termination->work_flow_processes_code = $next_process_id;
      $termination->termination_takenover_date = $termination_takenover_date;
      $termination->termination_date = $termination_date;
      $termination->termination_remark = $termination_remark;
      $termination->os = $os;
      $termination->created_by           = \Auth::user()->id;
      $termination->save();
      TenantContract::where('id',$contractId)->update(['tenant_renewal_termination_status'=>7,'tenant_contract_os'=>$osAmount]);


      if($processAssign != false) {

        foreach($processAssign->assign as $val){
         $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
         $termination->save();
       }
        //$termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
        //$termination->save();

     }else{

      $previousProcess = $general->getPreviousOrder($next_process_id);

      if($previousProcess !=0){

        $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

        foreach($previousAssign->assign as $val){
          $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
          $termination->save();
        }
          //$termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
          //$termination->save();

      }else{

        $workFlowProcess = $general->workFlowProcess($next_process_id);
        $termination->terminationUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
        $termination->save();


      }
    }
    $termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
    $termination->save();
	Unit::where('id',$unit_id)->update(['unit_vaccant_status'=>2]);
	
    session()->flash('success', 'Termination request Created Successfully');
    return redirect()->route('tenantTermination.index');
  }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {

     $tenantContract = TenantContract::with('terminationContract')->where('id',$id)->first();
     $termination = Termination::where('id',$tenantContract->terminationContract->id)->orderBy('id','desc')->first();
     clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$tenantContract->terminationContract->id);
     readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$tenantContract->terminationContract->id);

     $today      = date('Y-m-d');
     $osAmount   = 0 ;
	 $terminationDate = $termination->termination_date;
     // Outstanding Creation 
     $sumOfReceipt = ReceiptsGeneration::where('receipts_generation_type',0)->where('tenant_contract_id',$id)->where('receipts_generation_approval_status',3)->sum('receipts_generation_amt');
    // $remainAmt = contractRentCountCalculation($tenantContract->tenant_contract_effective_date,$today, $tenantContract->tenant_contract_rent );
     $remainAmt = totalContractRentCountCalculation($id,$terminationDate);
	 // Minus from Rent receipt
     $osAmount = $remainAmt - $sumOfReceipt;
     TenantContract::where('id',$id)->update(['tenant_contract_os'=>$osAmount]);

      // Previous Contracts related to this unit and Tenant
     $backHistory        = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->get();

     $remainingInvoices = Invoice::where('tenant_contract_id',$id)->whereDate('tenant_invoice_date','>',$termination->termination_date)->get();
     $otherDuesCollections = ReceiptsGeneration::where('tenant_contract_id',$id)->where('receipts_generation_status',3)->get();

     $openTerminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','TerminationDocument')->get();
     $comments = TenantLandlordTerminationRenewalComment::where('contract_id',$termination->contract_id)->get(); 

     $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
     $today = strtotime(date('Y-m-d'));

     $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds 
		
     return view('backoffice::Termination.tenant_termination_open_view',compact('tenantContract','remainingInvoices','otherDuesCollections','termination','backHistory','openTerminationDocument','comments','remainingDays'));
   }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($terminationId)
    {
    	$tenantTermination = Termination::where('id',$terminationId)->first();
     return view('backoffice::Termination.early_tenant_termination_create',compact('tenantTermination'));
   }

   public function cancelTermination(Request $request){

    $contracts = TenantContract::where('id',$request->contract_id)->first();

    $tenantTermination = Termination::where('contract_id',$request->contract_id)->update(['termination_type_status'=>0]);

    TenantContract::where('id',$request->contract_id)->update(['tenant_renewal_termination_status'=>0]);
    Termination::where('contract_id',$request->contract_id)->update(['contract_id'=>0]);

    $unitUpdate = Unit::where('id',$contracts->unit_id)->update(['unit_vaccant_status'=>1]);

    return json_encode("Status Changed");

   }

   public function cancelTerminationHandover(Request $request){

   
    $contracts = TenantContract::where('id',$request->contract_id)->first();
    $tenantTermination = Termination::where('contract_id',$request->contract_id)->update(['termination_type_status'=>0]);

    TenantContract::where('id',$request->contract_id)->update(['tenant_renewal_termination_status'=>0]);

    Termination::where('contract_id',$request->contract_id)->update(['contract_id'=>0]);

    $unitUpdate = Unit::where('id',$contracts->unit_id)->update(['unit_vaccant_status'=>1]);




   

    // $tenantTermination = Termination::where('contract_id',$request->contract_id)->update(['termination_type_status'=>0]);

    // TenantContract::where('id',$request->contract_id)->update(['tenant_renewal_termination_status'=>0]);
    // Termination::where('contract_id',$request->contract_id)->update(['contract_id'=>0]);

    // $unitUpdate = Unit::where('id',$contracts->unit_id)->update(['unit_vaccant_status'=>1]);



    return json_encode("Status Changed");



   }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Termination $tenantTermination)
    {
      $this->validate($request, [
        'contract_id' => 'required',                    
        ]);
      $oldContract = $tenantTermination->contract_id;
      TenantContract::where('id',$oldContract)->update(['tenant_renewal_termination_status'=>0]);

      $contractId = $request->contract_id;
      $termination_remark = $request['termination_remark'];
      $termination_takenover_date = $request['termination_takenover_date'];
      $termination_date = $request['termination_date'];
      $tenantTermination->update(['contract_id'=>$contractId,'termination_remark'=>$termination_remark,'termination_takenover_date'=>$termination_takenover_date,'termination_date'=>$termination_date]);

      TenantContract::where('id',$contractId)->update(['tenant_renewal_termination_status'=>7]);

      session()->flash('success', 'Termination request Updated Successfully');
      return redirect()->route('tenantTermination.index');

    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Termination $tenantTermination)
    {

    }
    /*
    *
    *  termination Stage
    *
    *
    */
    public function tenantTerminationStage($terminationId,$contractId,$stage,$action_key)
    {   
      $os = 'No' ;
      $AssignedTo;
      $today      = date('Y-m-d');
      $currentUrl =  Session::get('current');
      $general =  new General;
      $next_process_id = $general->nextProcessFromAction($stage,$action_key);
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);


      $tenantContract = TenantContract::where('id',$contractId)->first();

      $sumOfReceipt = ReceiptsGeneration::where('receipts_generation_type',0)->where('tenant_contract_id',$contractId)->where('receipts_generation_approval_status',3)->sum('receipts_generation_amt');
      // Till Now Amount
      //$remainAmt = contractRentCountCalculation($tenantContract->tenant_contract_effective_date,$today, $tenantContract->tenant_contract_rent );
      $terminationDate = Termination::where('id',$terminationId)->first()->termination_date;
      $remainAmt = totalContractRentCountCalculation($contractId,$terminationDate);

      // Minus from Rent receipt
      $osAmount = $remainAmt - $sumOfReceipt;
      if(isset($osAmount))
        $contractUpdate = TenantContract::where('id',$contractId)->update(['tenant_contract_os'=>$osAmount]);

      $previousFlow = Termination::where('work_flow_processes_code',$stage)->where('contract_id',$contractId)->where('termination_type',1)->orderBy('id','desc')->latest()->first();
      if($previousFlow)$previousFlow->terminationUser()->update(['status' => 0]);

        $previousProcess = Termination::where('work_flow_processes_code',$next_process_id)->where('contract_id',$contractId)->where('termination_type',1)->orderBy('id','desc')->latest()->first();//dd($previousFlow);
        if($previousProcess)$previousProcess->terminationUser()->update(['status' => 0]);
        // Outstanding Creation 
        $tenantContract = TenantContract::where('id',$contractId)->first();
        $tenant_contract_last_paid_date = $tenantContract->tenant_contract_last_paid_date;
        if(empty($tenant_contract_last_paid_date)){
          $tenant_contract_last_paid_date = $tenantContract->tenant_contract_effective_date;
          $paymentTerm = $tenantContract->tenant_contract_payment_type;
          switch($paymentTerm){
            case 1:
            $month = 1;
            break;
            case 2:
            $month = 2;
            break;
            case 3:
            $month = 3;
            break;
            case 4:
            $month = 6;
            break;
            case 5:
            $month = 12;
            break;
          }
          $futurePaymentDate = $tenant_contract_last_paid_date->addMonths($month)->format('Y-m-d');
          if($futurePaymentDate < date('Y-m-d')){
            $os = 'Yes' ;
          }else{
            $os = 'No' ;
          }

        }
        //old stage termination details restore
        $terminationTypeStatus = $previousFlow->termination_type_status;
        $termination_remark = $previousFlow->termination_remark;
        $termination_takenover_date = $previousFlow->termination_takenover_date;
        //$termination_date = $previousFlow->termination_date;
        $termination_date = $previousFlow->termination_date;
        $termination_main_key_status = $previousFlow->termination_main_key_status;
        $elec_acc = $previousFlow->termination_electricity_acc_no;
        $elec_cls_read = $previousFlow->termination_electricity_close_reading;
        $elec_amt = $previousFlow->termination_electricity_amount;
        $water_acc = $previousFlow->termination_electricity_amount;
        $water_cls_read = $previousFlow->termination_water_close_reading;
        $water_amt = $previousFlow->termination_water_amount;
        $total = $previousFlow->termination_total_amount;
        $elec_water_total = $previousFlow->termination_total_elec_water_amount;
        $maintenance_due = $previousFlow->termination_discount_maintenance_due;
        $net = $previousFlow->termination_net_amount;
        $notes = $previousFlow->termination_notes;
        $termination_tenant_signature = $previousFlow->termination_tenant_signature;
        $termination_tenant_review_status = $previousFlow->termination_review_status;
        $AssignedTo = $previousFlow->assigned_to;

        $termination = new Termination; 
        $termination->contract_id     = $contractId;
        $termination->termination_type  = 1;
        $termination->work_flow_processes_code = $next_process_id;
        $termination->termination_type_status = $terminationTypeStatus;
        $termination->termination_takenover_date = $termination_takenover_date;
        $termination->termination_date = $termination_date;
        $termination->termination_remark = $termination_remark;
        $termination->termination_main_key_status 			= $termination_main_key_status;
        $termination->termination_electricity_acc_no 		= $elec_acc;
        $termination->termination_electricity_close_reading = $elec_cls_read;
        $termination->termination_electricity_amount 		= $elec_amt;
        $termination->termination_water_acc_no 				= $water_acc;
        $termination->termination_water_close_reading 		= $water_cls_read;
        $termination->termination_water_amount 				= $water_amt;
        $termination->termination_total_amount 				= $total;
        $termination->termination_total_elec_water_amount 	= $elec_water_total;
        $termination->termination_discount_maintenance_due 	= $maintenance_due;
        $termination->termination_net_amount 				= $net;
        $termination->termination_notes 					= $notes;
        $termination->termination_tenant_signature 			= $termination_tenant_signature;
		    //$termination->termination_review_status 			= $termination_tenant_review_status;
		if($action_key == 'RESUB'){ // Resubmit Status to identify
			$termination->is_resubmit 						= 2;
		}
		
    $termination->os                            = $os;
    if($next_process_id == 503)
    {
      $AssignedTo = $previousProcess->assigned_to;
      $technician = User::where('id',$AssignedTo)->first();
    }

    if(!empty($AssignedTo))
      $termination->assigned_to = $AssignedTo;

    $termination->created_by           = \Auth::user()->id;
    $termination->save();
    if($processAssign != false) {

      foreach($processAssign->assign as $val){
       $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
       $termination->save();
     }
          //$termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
          //$termination->save();

   }else{

    $previousProcess = $general->getPreviousOrder($next_process_id);

    if($previousProcess !=0){

      $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

      foreach($previousAssign->assign as $val){
        $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
        $termination->save();
      }
            //$termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
            //$termination->save();

    }else{

      $workFlowProcess = $general->workFlowProcess($next_process_id);
      $termination->terminationUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
      $termination->save();


    }
  }
  $termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
  $termination->save();

        // if terminated update status
  if($next_process_id == 505){

    $contracts = TenantContract::where('id',$termination->contract_id)->first();
    $contractUpdate = TenantContract::where('id',$termination->contract_id)->update(['tenant_contract_status'=>0,'tenant_renewal_termination_status'=>8]);
    $unitUpdate = Unit::where('id',$contracts->unit_id)->update(['unit_vaccant_status'=>0]);

          //Remaining pdc and invoices cancel    
    $today  = date('Y-m-d');
   // $remainingPdc = Pdc::whereIn('tenant_contract_id',[$termination->contract_id])->whereDate('pdc_check_date','>=',$termination->termination_date)->pluck('id');
    //if(count($remainingPdc)>0)
    //  $pdcUpdate = Pdc::whereIn('id',$remainingPdc)->update(['pdc_cancel_date'=>$today,'pdc_cancel_reason'=>4,'pdc_cancel_by'=>\Auth::user()->id]);
              //Invoice
    $remainingInvoice = Invoice::whereIn('tenant_contract_id',[$termination->contract_id])->whereDate('tenant_invoice_date','>=',$termination->termination_date)->pluck('id');
    if(count($remainingInvoice)>0)
      $invoiceUpdate = Invoice::whereIn('id',$remainingInvoice)->update(['tenant_invoice_cancelled_date'=>$today,'tenant_invoice_status'=>2]);          
	
	$unitInfo   = Unit::where('id',$contracts->unit_id)->first();   
    $building   = Building::where('id',$unitInfo->building_id)->first();
    $unit_type  = UnitType::where('id',$unitInfo->unit_type_id)->first();
    $location   = Location::where('id',$building->location_id)->first();
	//Insertation Vacany Table only Vacant From 
    VacantVacancyLoss::create([  
        'building_id'=> $building->id,
        'unit_id'=> $unitInfo->id,
        'building_name'=> $building->building_name,
        'building_no'=> $building->building_no,
        'unit_no'=> $unitInfo->unit_no,
        'unit_type'=> $unit_type->unit_types_name,
        'vacant_from'=> $termination->termination_date,
        'vacant_to'=>null,
        'location_id'=>$building->location_id,
        'location_name'=>$location->locations_name,
        'vacant_days'=>null,
        'rent_per_month'=>$contracts->tenant_contract_rent,
        'vacany_loss'=>null,
        'status'=>1,
        'unit_type_id' => $request->unit_type_id, 
        'created_by' => \Auth::user()->id,
    ]);
  }
  /* Notification */
  $process = $general->workFlowProcessNames($next_process_id);

  clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);
  readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);  
  $termination->text = "Tenant Termination - ".$process->work_flow_processes_name;

  switch($next_process_id){
    case "502":
    $users = User::role(['maintenance_supervisor'])->get(); 
    $users = array_flatten($users);
    $termination->text = "Tenant Termination HandOver Assigned ";
    $termination->href = url("handoverUnassigned/".$termination->id."/handoverUnassignedView/");
    $currentUrl = 'tenantTermination.index';
    break;
    case "503":
    $users = User::role(['maintenance_supervisor'])->get(); 
    $users = array_flatten($users);
    $termination->href = url("handoverAssigned/".$termination->id."/handoverAssignedView/");
    $termination->text = "Tenant Termination - ".$process->work_flow_processes_name ." Resubmit";
    $currentUrl = 'takeoverForTermination';
    event(new TenantTermination($termination,$technician));
    break;
    case "504":
    $users = User::role(['backoffice_executive'])->get(); 
    $users = array_flatten($users);
    $termination->href = url("takeoverForTermination/".$termination->id."/takeoverForTerminationView/");
    $currentUrl = 'handoverAssigned';
    break;
    case "505":
    $users = User::role(['backoffice_executive'])->get(); 
    $users = array_flatten($users);
    $termination->href = url("tenantTerminatedContract/".$termination->id."/tenantTerminatedContractView/");
    $currentUrl = 'takeoverForTermination';
    break;
  }
  event(new TenantTermination($termination,$users));
  /* End */
  session()->flash('success', 'Termination Successfully Moved to '.$process->work_flow_processes_name);
  return redirect()->route($currentUrl);

}
    /*
    *
    *
    * Update Termination Extra added fields of dates and remarks 
    *
    */
    public function terminationUpdateExtraFields(Request $request)
    {
      $terminationId = $request['termination_id'];
      $termination_takenover_date = $request['termination_takenover_date'];
      $termination_date = $request['termination_date'];
      $termination_remark = $request['termination_remark'];

      if(!empty($termination_takenover_date)){
        Termination::where('id',$terminationId)->update(['termination_takenover_date'=>$termination_takenover_date]);
      }
      if(!empty($termination_date)){
        Termination::where('id',$terminationId)->update(['termination_date'=>$termination_date]);
      }
      if(!empty($termination_remark)){
        Termination::where('id',$terminationId)->update(['termination_remark'=>$termination_remark]);
      }
      return  $terminationId;
      
    }
    /**
    *
    * Agreement Autocomplete
    *
    **/
    public function agreementAutocomplete(Request $request)
    {

      $key = $request->term;
      $agreement =  TenantContract::active()->areFilter()->whereNotIn('tenant_renewal_termination_status',[1,7])->where('tenant_contract_no', 'ILIKE', '%'.$key.'%')
      ->select('id AS ids',DB::raw("tenant_contract_no as value"))->limit(10)
      ->get();

      return $agreement ;

    }
    /*
    *
    *
    *Agreement Details
    *
    *
    */
    public function agreementDetail(Request $request)
    {
      $contractNo =$request->contractNo;
      $building_id =$request->building_id;
      $unit_id =$request->unit_id;
      if(!empty($contractNo)){
        $exist = Termination::where('contract_id',$contractNo)->orderBy('id','desc')->latest()->first();
      }else{
        $exist = [];
      }
      
      if(empty($exist)){
        if($building_id != "" && $unit_id != ""){
          $tenantContract = TenantContract::where('building_id',$building_id)->where('unit_id',$unit_id)->active()->whereNotIn('tenant_renewal_termination_status',[1,7])->first();
        }elseif ($building_id == "" && $unit_id != "") {
         $tenantContract = TenantContract::where('unit_id',$unit_id)->active()->whereNotIn('tenant_renewal_termination_status',[1,7])->first();
       }
       else{
        $tenantContract = TenantContract::with('terminationContract')->where('id',$contractNo)->whereNotIn('tenant_renewal_termination_status',[1,7])->first();
      }
    }else{
      if($building_id != "" && $unit_id != ""){
        $tenantContract = TenantContract::where('building_id',$building_id)->where('unit_id',$unit_id)->active()->whereNotIn('tenant_renewal_termination_status',[1])->first();
      }elseif ($building_id == "" && $unit_id != "") {
       $tenantContract = TenantContract::where('unit_id',$unit_id)->active()->whereNotIn('tenant_renewal_termination_status',[1])->first();
     }
     else{
      $tenantContract = TenantContract::with('terminationContract')->where('id',$contractNo)->whereNotIn('tenant_renewal_termination_status',[1])->first();
    }
  }
      //dd($tenantContract);
  $termination = Termination::where('contract_id',$tenantContract->id)->orderBy('id','desc')->latest()->first();
      //dd($termination);
  return view('backoffice::Termination.early_tenant_termination_ajax',compact('tenantContract','termination'));
}
    /**
    *
    * building Autocomplete
    *
    **/
   public function allBuildingAutocomplete(Request $request)
    {
       $key = $request->term;
      $building =  Building::areFilter()->whereHas('tenantContract', function ($query){
        $query->active()->whereNotIn('tenant_renewal_termination_status',[1,7]);       
      })->where('building_name', 'ILIKE', '%'.$key.'%')
      ->select('id AS ids',DB::raw("CONCAT(building_name,'-',building_code) as value"))
      ->get();
      return $building ;

    }
    /*
    *
    *
    *Unit Details
    *
    *
    */
    public function occupiedUnitDetail(Request $request)
    {

      $units = array();
      $building_id =$request->building_id;
      $tenantContract = TenantContract::active()->areFilter()->where('building_id',$building_id)->whereNotIn('tenant_renewal_termination_status',[1,7])->pluck('unit_id');
      $units = Unit::where('building_id',$building_id)->whereIn('id',$tenantContract)->where('unit_vaccant_status',1)->orderBy('unit_no','ASC')->get();
     // dd($units);
      return json_encode(array($units));
      
    }
     /*
    *
    *
    *Teanant Contract Details BY Tenant id
    *
    *
    */
    public function getTenantContractByTenantId(Request $request)
    {

      $tenantContracts = array();
      $tenant_id =$request->tenant_id;
      $tenantContracts = TenantContract::active()->areFilter()->where('tenant_id',$tenant_id)->whereNotIn('tenant_renewal_termination_status',[1,7])->get();
     // dd($tenantContracts);
      return json_encode(array($tenantContracts));
      
    }
    /*
    *
    *
    * Agreement Details against building unit
    *
    *
    */
    public function agreementDetailAgainstBulUnit(Request $request)
    {
     $contractNo =$request->contractNo;
      $building_id =$request->building_id;
      $unit_id =$request->unit_id;
      if($building_id != "" && $unit_id != ""){
        $tenantContract = TenantContract::where('building_id',$building_id)->where('unit_id',$unit_id)->active()->whereNotIn('tenant_renewal_termination_status',[1,7])->first();
      }elseif ($building_id == "" && $unit_id != "") {
        $tenantContract = TenantContract::where('unit_id',$unit_id)->active()->whereNotIn('tenant_renewal_termination_status',[1,7])->first();
      }
      else{
        $tenantContract = TenantContract::where('id',$contractNo)->whereNotIn('tenant_renewal_termination_status',[1,7])->first();
      }
      
      return json_encode(array($tenantContract->building,$tenantContract->unit,$tenantContract->tenant,$tenantContract));
    }

    /**
    *
    * Unit Autocomplete
    *
    **/
    public function unitAutocomplete(Request $request)
    {

      $key = $request->term;
      $unit =  Unit::whereHas('tenantContract', function ($query){
        $query->whereNotIn('tenant_renewal_termination_status',[1,7]);       
      })->where('unit_code', 'ILIKE', '%'.$key.'%')
      ->select('id AS ids',DB::raw("CONCAT(unit_code) as value"))
      ->get();
      return $unit;
    } 

    /*
    *
    *
    * Tenant Autocomplete
    *
    *
    */
    public function tenantAutocomplete(Request $request)
    {

      $key = $request->term;
      $tenant =  Tenant::active()->whereHas('tenantContract', function ($query){
        $query->active()->whereNotIn('tenant_renewal_termination_status',[1,7]);       
      })->where('tenant_name', 'ILIKE', '%'.$key.'%')
      ->select('id AS ids',DB::raw("CONCAT(tenant_name) as value"))
      ->get();
      return $tenant;

    }
    /*
    *
    *
    * Get building by Tenant
    *
    *
    */
    public function getBuildingByTenantId(Request $request)
    {
      $tenant_id =$request->tenant_id;
      if($tenant_id != ""){
        $building = Building::active()->whereHas('tenantContract', function ($query)use($tenant_id) {
         $query->where('tenant_id',$tenant_id)->whereNotIn('tenant_renewal_termination_status',[1,7]);     
         return $query;
       })->get();
      }
      //dd($building);
      return json_encode($building);
    }

    /*
    *
    *
    * tenantTerminationOpenStatus
    *
    */
    public function tenantTerminationOpenStatus($terminationId,$status)
    {


      $termination = Termination::where('id',$terminationId)->update(['termination_type_status'=>$status]);
      $termination = Termination::where('id',$terminationId)->first();
      switch($status){
        case "2":
        /* Notification */

        $users = User::role(['backoffice_manager'])->get();
        $users = array_flatten($users);              
        clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);
        $termination->href =  url('tenantTerminationApproval/'.$termination->id."/tenantTerminationApprovalView");    
        $termination->text = "Tenant Termination Send For Approval Request"; 
        event(new TenantTermination($termination,$users));
        /* End */
        $msg = 'Termination Approval Send Successfully';
        session()->flash('success', $msg);
        return redirect()->route('tenantTermination.index');
        break;
        case "3":
        /* Notification */
        $users = User::role(['backoffice_executive'])->get(); 
        $users = array_flatten($users);              
        clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);
        $termination->href =  url('tenantTermination/'.$termination->contract_id);    
        $termination->text = "Tenant Termination Approved"; 
        event(new TenantTermination($termination,$users));
        /* End */
        $msg = 'Termination  Approval Approved';
        session()->flash('success', $msg);
        return redirect()->route('tenantTerminationApproval');
        break;
        case "4":
        /* Notification */
        $users = User::role(['backoffice_executive'])->get(); 
        $users = array_flatten($users);              
        clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);
        $termination->href =  url('tenantTermination/'.$termination->contract_id);    
        $termination->text = "Tenant Termination Rejected"; 
        event(new TenantTermination($termination,$users));
        /* End */
        $msg = 'Termination  Approval Rejected';
        session()->flash('success', $msg);
        return redirect()->route('tenantTerminationApproval');
        break;
      }
      
      
    }
    
    /*
    *
    * Tenant Termination Approval
    *
    *
    */
    public function tenantTerminationApproval(Request $request)
    {
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


      $quick_url =   $route   =  route('tenantTerminationApproval');


   $tenantTerminations =     ViewTenantTermination::
                                    where('work_flow_processes_code', '=', 501)
                                  ->where('termination_type', '=', 1)
                                  ->where('tenant_contract_status',1)
                                  ->where('termination_type_status',2)       
                                  ->filterUsers()->filter($request)->orderBy('id')
                                //  ->distinct()
                                  ->sortable()->paginate($this->noOfRecord); 


/*
      $tenantTermination = Termination::whereHas('tenantContract', function ($query) use($rolesNames,$request){
        $query->where('tenant_contract_status',1)
        ->filter($request)
        ->sortable();

      })->whereHas('terminationUsers', function ($query) {
        $query->where('status','=',1);
      })                             
      ->where('work_flow_processes_code', '=', 501)
      ->where('termination_type_status', '=', 2)
      ->where('termination_type', '=', 1);

      if (in_array('super_admin', $rolesNames) === false) {
        $tenantTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
        });
      }
      $tenantTerminations = $tenantTermination->sortable()->paginate($this->noOfRecord);
*/
      if(isset($request->ajax))
       return view('backoffice::Termination.tenant_termination_approval_list_ajax',compact('tenantTerminations','request','route'));

     return view('backoffice::Termination.tenant_termination_approval_list',compact('tenantTerminations','enquiry_fields','operations','quick_url'));
   }
    /*
    *
    *
    *
    *
    *
    */
    public function tenantTerminationApprovalView($terminationId)
    {
      clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
      readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
      $termination = Termination::where('id',$terminationId)->first();
      $tenantContract = TenantContract::with('terminationContract')->where('id',$termination->contract_id)->first();

      $remainingInvoices = Invoice::where('tenant_contract_id',$termination->contract_id)->whereDate('tenant_invoice_date','>',$termination->termination_date)->get();

      $otherDuesCollections = ReceiptsGeneration::where('tenant_contract_id',$terminationId)->where('receipts_generation_status',3)->get();

      $openTerminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','TerminationDocument')->get();

      $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
     $today = strtotime(date('Y-m-d'));

     $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds
	  $terminationDate      = $termination->termination_date;
     $osAmount   = 0 ;
      // Outstanding Creation
      $sumOfReceipt   = ReceiptsGeneration::where('receipts_generation_type',0)->where('tenant_contract_id',$termination->contract_id)->where('receipts_generation_approval_status',3)->sum('receipts_generation_amt');
         $remainAmt = totalContractRentCountCalculation($termination->contract_id,$terminationDate);
      // Minus from Rent receipt
      $osAmount = $remainAmt - $sumOfReceipt;

      TenantContract::where('id',$termination->contract_id)->update(['tenant_contract_os'=>$osAmount]);



      return view('backoffice::Termination.tenant_termination_approval_view',compact('tenantContract','termination','remainingInvoices','otherDuesCollections','openTerminationDocument','remainingDays'));
    }
    /*
    *
    * Tenant Termination Handover Unassigned
    *
    *
    */
    public function handoverUnassigned(Request $request)
    {
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


      $quick_url =   $route   =  route('handoverUnassigned');



   $tenantTerminations =     ViewTenantTermination::
                                    where('work_flow_processes_code', '=', 502)
                                  ->where('termination_type', '=', 1)
                                  ->where('tenant_contract_status',1)   
                                  ->filterUsers()->filter($request)
                                  ->sortable()->paginate($this->noOfRecord); 

/*
      $tenantTermination = Termination::whereHas('tenantContract', function ($query) use($rolesNames,$request){
        $query->where('tenant_contract_status',1)
        ->filter($request)
        ->sortable();

      })->whereHas('terminationUsers', function ($query) {
        $query->where('status','=',1);
      })                             
      ->where('work_flow_processes_code', '=', 502)
      ->where('termination_type', '=', 1);

      if (in_array('super_admin', $rolesNames) === false) {
        $tenantTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
        });
      }
      $tenantTerminations = $tenantTermination->paginate($this->noOfRecord);
      */
      if(isset($request->ajax))
       return view('backoffice::Termination.tenant_termination_handover_unassigned_list_ajax',compact('tenantTerminations','request','route'));

     return view('backoffice::Termination.tenant_termination_handover_unassigned_list',compact('tenantTerminations','enquiry_fields','operations','quick_url'));
   }
    /*
    *
    *
    * Group Assign Modal
    *
    */
    public function groupAssignModal(Request $request)
    {
      $terminationArr = $request->id;
      $workflow = $request->workflow;
      $action = $request->action;
      //dd($action);
      $general          = new General;
      $next_process_id = $general->nextProcessFromAction($workflow,$action);

      $user = array();
      $users = array();

      foreach($terminationArr as $termination){

        $processAssign    = $general->roleUsersFromProcess($next_process_id,$location_id=0,$pricerange_id=0,$tenant_status=0);
        if($processAssign == false) {

          $previousProcess = $general->getPreviousOrder($next_process_id);

          if($previousProcess !=0){

            $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 
            $user_ids =   $previousAssign->assign->where('user_id','>',0)->pluck('user_id');
            $roles = $previousAssign->assign->where('user_id',null); 
            $roles = $roles->pluck('role_id');
            if(count($roles) > 0){

              $default_user =  User::role($roles)->pluck('id');
              array_push($users,$default_user);

            }
            array_push($user,$users);

          }else{
            $workFlowProcess = $general->workFlowProcess($next_process_id);
            $default_user = $workFlowProcess->default_user;        
            array_push($user,$default_user);
          }

        }else {

          $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
          $roles = $processAssign->assign->where('user_id',null); 
          $roles = $roles->pluck('role_id');
          if(count($roles) > 0){


            $default_user =  User::role($roles)->pluck('id');
            array_push($users,$default_user);

          }
          array_push($user,$users);

        }
      }

      $flattenedUsers = array_flatten($user);
      $users = array_unique($flattenedUsers,SORT_REGULAR);
      //$role = array_diff($role, (is_array(1) ? 1 : array(1)));
      $useres = User::whereIn('id', $users)->get();
      
      return view('backoffice::Termination.tenant_termination_assign_modal',compact('useres','terminationArr','next_process_id','workflow'));
    }
    /*
    *
    *
    * Sub Assign
    *
    */
    public function storeGroupAssign(Request $request)
    {
      $os = 'No';
      $route = Session::get('current');//dd($route);
      $role_id = $request['role'];
      $user_id = $request['user_id'];
      $workflow_id = $request['workflow_id'];
      $next_process_id = $request['next_process_id'];

      $terminationArr = $request['termination_id'];//dd($workflow_id);

      $general          = new General; 
      $processAssign    = $general->roleUsersFromProcess($next_process_id,$location_id=0,$pricerange_id=0,$tenant_status=0);
      
      foreach ($terminationArr as $termination) {


        $tenantTermination = Termination::where('id',$termination)->first();
        // Outstanding Creation 
        $tenantContract = TenantContract::where('id',$tenantTermination->contract_id)->first();
        $tenant_contract_last_paid_date = $tenantContract->tenant_contract_last_paid_date;
        if(empty($tenant_contract_last_paid_date)){
         $tenant_contract_last_paid_date = $tenantContract->tenant_contract_effective_date;
         $paymentTerm = $tenantContract->tenant_contract_payment_type;
         switch($paymentTerm){
           case 1:
           $month = 1;
           break;
           case 2:
           $month = 2;
           break;
           case 3:
           $month = 3;
           break;
           case 4:
           $month = 6;
           break;
           case 5:
           $month = 12;
           break;
         }
         $futurePaymentDate = $tenant_contract_last_paid_date->addMonths($month)->format('Y-m-d');
         if($futurePaymentDate < date('Y-m-d')){
           $os = 'Yes' ;
         }else{
           $os = 'No' ;
         }

       }
       $previousFlow = Termination::where('work_flow_processes_code',$workflow_id)->where('contract_id',$tenantTermination->contract_id)->where('termination_type',1)->orderBy('id','desc')->latest()->first();

       if($previousFlow)$previousFlow->terminationUser()->update(['status' => 0]);

       $previousProcess = Termination::where('work_flow_processes_code',$next_process_id)->where('contract_id',$tenantTermination->contract_id)->where('termination_type',1)->orderBy('id','desc')->latest()->first();
       if($previousProcess)$previousProcess->terminationUser()->update(['status' => 0]);
        //old Data Update 
       $terminationTypeStatus = $tenantTermination->termination_type_status;
       $termination_remark = $tenantTermination->termination_remark;
       $termination_takenover_date = $tenantTermination->termination_takenover_date;
       $termination_date = $tenantTermination->termination_date;
       $termination_main_key_status = $tenantTermination->termination_main_key_status;
       $elec_acc = $tenantTermination->termination_electricity_acc_no;
       $elec_cls_read = $tenantTermination->termination_electricity_close_reading;
       $elec_amt = $tenantTermination->termination_electricity_amount;
       $water_acc = $tenantTermination->termination_electricity_amount;
       $water_cls_read = $tenantTermination->termination_water_close_reading;
       $water_amt = $tenantTermination->termination_water_amount;
       $total = $tenantTermination->termination_total_amount;
       $elec_water_total = $tenantTermination->termination_total_elec_water_amount;
       $maintenance_due = $tenantTermination->termination_discount_maintenance_due;
       $net = $tenantTermination->termination_net_amount;
       $notes = $tenantTermination->termination_notes;
       $termination_tenant_signature = $tenantTermination->termination_tenant_signature;
       $termination_tenant_review_status = $tenantTermination->termination_review_status;


       $terminationTypeStatus = $previousFlow->termination_type_status;
       $termination_remark = $previousFlow->termination_remark;
       $termination_takenover_date = $previousFlow->termination_takenover_date;
       $termination_date = $previousFlow->termination_date;

       $terminationProcess = new Termination; 
       $terminationProcess->contract_id     = $tenantTermination->contract_id;
       $terminationProcess->termination_type  = 1;
       $terminationProcess->assigned_to  = $user_id;
       $terminationProcess->work_flow_processes_code = $next_process_id;
       $terminationProcess->termination_type_status = $terminationTypeStatus;
       $terminationProcess->termination_takenover_date = $termination_takenover_date;
       $terminationProcess->termination_date = $termination_date;
       $terminationProcess->termination_remark = $termination_remark;
       $terminationProcess->termination_main_key_status       = $termination_main_key_status;
       $terminationProcess->termination_electricity_acc_no    = $elec_acc;
       $terminationProcess->termination_electricity_close_reading = $elec_cls_read;
       $terminationProcess->termination_electricity_amount    = $elec_amt;
       $terminationProcess->termination_water_acc_no        = $water_acc;
       $terminationProcess->termination_water_close_reading     = $water_cls_read;
       $terminationProcess->termination_water_amount        = $water_amt;
       $terminationProcess->termination_total_amount        = $total;
       $terminationProcess->termination_total_elec_water_amount   = $elec_water_total;
       $terminationProcess->termination_discount_maintenance_due  = $maintenance_due;
       $terminationProcess->termination_net_amount        = $net;
       $terminationProcess->termination_notes           = $notes;
       $terminationProcess->termination_tenant_signature      = $termination_tenant_signature;
       $terminationProcess->termination_review_status       = $termination_tenant_review_status;
       $terminationProcess->os = $os;
       $terminationProcess->created_by           = \Auth::user()->id;
       $terminationProcess->save();
       if(empty($user_id)){
        if($processAssign == false) {

          $processAssign = $general->getPreviousOrder($next_process_id);
          if($processAssign !=0) {
            $previousAssign = $general->roleUsersFromProcess($processAssign,$location_id=0,$pricerange_id=0,$tenant_status=0);
            foreach($previousAssign->assign as $val){
             $terminationProcess->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
           }
         }else{
          $res =    $general->workFlowProcess($next_process_id);
          $terminationProcess->terminationUsers()->attach($res->default_role, ['user_id' => $res->default_user_id]);

        }
      }else{
        foreach($processAssign->assign as $val){
         $terminationProcess->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);

       }
     }
   }else{
    $terminationProcess->terminationUsers()->attach($role_id,['user_id'=>$user_id]);

  }        
  $terminationProcess->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
  $terminationProcess->save(); 

  /* Notification */
  $users_notify =  \App\User::whereIn('id',[$user_id])->get();              
  clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$tenantTermination->id);

  $tenantTermination->text = "Handover Assigned ";
  $tenantTermination->href = url("handoverAssigned/".$tenantTermination->id."/handoverAssignedInspection/"); 
  event(new TenantTermination($tenantTermination,$users_notify));
  /* End */ 
  /****** Activity log *****/

          /*activity('Group Assign')
            ->performedOn($complaintProcess)
            ->causedBy(\Auth::user()->id)
            ->withProperties($complaintProcess)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);*/


          }
          session()->flash('success', 'Handover Assigned Successfully');
          switch($workflow_id){
            case 502:
            $route = 'handoverUnassigned';
            break;
            case 503 :
            $route = 'handoverAssigned';
            break;
          }
          return redirect()->route($route);

        }
    /*
    *
    * handoverUnassignedView
    *
    *
    */
    public function handoverUnassignedView($terminationId)
    {
      clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
      readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);  
      $termination = Termination::where('id',$terminationId)->first();
      $tenantContract = TenantContract::where('id',$termination->contract_id)->first();

      $openTerminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','TerminationDocument')->get();

      $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
     $today = strtotime(date('Y-m-d'));

     $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds 

     $outstandingOs = $this->outstandingOs($termination->contract_id);


      return view('backoffice::Termination.tenant_termination_handover_unassigned_view',compact('tenantContract','termination','openTerminationDocument','remainingDays','outstandingOs'));
    }
    /*
    *
    * Tenant Termination Handover Unassigned
    *
    *
    */
    public function handoverAssigned(Request $request)
    {

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
      //dd($rolesNames);

      $request->flash();
      

      $quick_url =  $route   =  route('handoverAssigned');
      /*->where('assigned_to',\Auth::user()->id)*/

      $tenantTerminations =     ViewTenantTermination::
                                    where('work_flow_processes_code', '=', 503)
                                  ->where('termination_type', '=', 1)
                                  ->where('tenant_contract_status',1)
                                  ->where('termination_refer_back', '=', 0)      
                                  ->filterUsers()->filter($request)
                                //  ->distinct()
                                  ->sortable()->paginate($this->noOfRecord); 
  /*

      $tenantTermination = Termination::whereHas('tenantContract', function ($query) use($rolesNames,$request){
        $query->where('tenant_contract_status',1)
        ->filter($request)
        ->sortable();

      })->whereHas('terminationUsers', function ($query) {
        $query->where('status','=',1);
      })                             
      ->where('work_flow_processes_code', '=', 503)
      ->where('termination_refer_back', '=', 0);

      if ((in_array('super_admin', $rolesNames) === false) && (in_array('maintenance_supervisor', $rolesNames) === false)) {
        $tenantTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
        });
      }
      $tenantTerminations = $tenantTermination->sortable()->paginate($this->noOfRecord);
      */
     // dd($tenantTerminations);
      if(isset($request->ajax))
       return view('backoffice::Termination.tenant_termination_handover_assigned_list_ajax',compact('tenantTerminations','request','route'));

     return view('backoffice::Termination.tenant_termination_handover_assigned_list',compact('tenantTerminations','enquiry_fields','operations','quick_url'));
   }
   public function handoverAssignedSalesPersonView(Request $request)
    {

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
      //dd($rolesNames);

      $request->flash();
      

      $quick_url =  $route   =  route('handoverAssignedSalesPersonView');
      /*->where('assigned_to',\Auth::user()->id)*/

      $tenantTerminations =     ViewTenantTermination::
                                    where('work_flow_processes_code', '=', 503)
                                  ->where('termination_type', '=', 1)
                                  ->where('tenant_contract_status',1)
                                  ->where('termination_refer_back', '=', 0)      
                                  ->FilterWithoutUsers()->filter($request)
                                //  ->distinct()
                                  ->sortable()->paginate($this->noOfRecord); 
  
      if(isset($request->ajax))
       return view('backoffice::Termination.tenant_termination_handover_assigned_list_salesPerson_ajax',compact('tenantTerminations','request','route'));

     return view('backoffice::Termination.tenant_termination_handover_assigned_list_salesPerson',compact('tenantTerminations','enquiry_fields','operations','quick_url'));
   }
    /*
    *
    * handoverAssignedView
    *
    *
    */
    public function handoverAssignedView($terminationId)
    {
     clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
     readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
     $termination  = Termination::where('id',$terminationId)->first();
     $tenantContract = TenantContract::where('id',$termination->contract_id)->first();
      	$groupedWork = $tenantContract->terminationChecklist->groupBy('work_id');//dd($groupedWork);
      	Session::put('termination_Id', $terminationId);
      	$terminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','!=','TerminationDocument')->get();
        $openTerminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','TerminationDocument')->get();

        $backHistory        = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->get();
		$preBackHistory        = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->where('id','!=',$termination->contract_id)->get();

		$depositeCheque = array();
		$outstandingOs = 0;
		$totalOutstanding = 0;
      foreach($preBackHistory as $prev_contract){

        if($prev_contract->id != $termination->contract_id){
		  
          $pdcDepositCheque = Pdc::where('tenant_contract_id','=',$prev_contract->id)->where('pdc_type','=',2)->orderBy('id','desc')->first();
          if(isset($pdcDepositCheque->pdc_check_no)>0 && empty($depositeCheque)){

            $depositeCheque = $pdcDepositCheque;
          }
		  
        }
		
		//  $outstandingOs += $this->outstandingOs($prev_contract->id);	
	//	$outstandingOs += $this->outstandingOs($prev_contract->id);
	$totalOutstanding += $this->outstandingOsAmount($prev_contract->id);
      }
        $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
		$terminationDate      = $termination->termination_date;
      $osAmount   = 0 ;
      // Outstanding Creation
      $sumOfReceipt   = ReceiptsGeneration::where('receipts_generation_type',0)->where('tenant_contract_id',$termination->contract_id)->where('receipts_generation_approval_status',3)->sum('receipts_generation_amt');
         $remainAmt = totalContractRentCountCalculation($termination->contract_id,$terminationDate);
      // Minus from Rent receipt
      $osAmount = $remainAmt - $sumOfReceipt;
      $outstandingOs = $totalOutstanding + $osAmount;
     $today = strtotime(date('Y-m-d'));

     $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds

	  $tenancyStartDt = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->select('tenant_contract_start_date')->orderBy('id','asc')->first();
     

        return view('backoffice::Termination.tenant_termination_handover_assigned_view',compact('tenantContract','termination','groupedWork','terminationDocument','openTerminationDocument','backHistory','remainingDays','outstandingOs','depositeCheque','tenancyStartDt'));
      }

      public function handoverAssignedViewSalesPerson($terminationId)
    {
     clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
     readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
     $termination  = Termination::where('id',$terminationId)->first();
     $tenantContract = TenantContract::where('id',$termination->contract_id)->first();
        $groupedWork = $tenantContract->terminationChecklist->groupBy('work_id');//dd($groupedWork);
        Session::put('termination_Id', $terminationId);
        $terminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','!=','TerminationDocument')->get();
        $openTerminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','TerminationDocument')->get();

        $backHistory        = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->get();
    $preBackHistory        = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->where('id','!=',$termination->contract_id)->get();

    $depositeCheque = array();
    $outstandingOs = 0;
    $totalOutstanding = 0;
      foreach($preBackHistory as $prev_contract){

        if($prev_contract->id != $termination->contract_id){
      
          $pdcDepositCheque = Pdc::where('tenant_contract_id','=',$prev_contract->id)->where('pdc_type','=',2)->orderBy('id','desc')->first();
          if(isset($pdcDepositCheque->pdc_check_no)>0 && empty($depositeCheque)){

            $depositeCheque = $pdcDepositCheque;
          }
      
        }
    
    //  $outstandingOs += $this->outstandingOs($prev_contract->id); 
  //  $outstandingOs += $this->outstandingOs($prev_contract->id);
  $totalOutstanding += $this->outstandingOsAmount($prev_contract->id);
      }
        $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
    $terminationDate      = $termination->termination_date;
      $osAmount   = 0 ;
      // Outstanding Creation
      $sumOfReceipt   = ReceiptsGeneration::where('receipts_generation_type',0)->where('tenant_contract_id',$termination->contract_id)->where('receipts_generation_approval_status',3)->sum('receipts_generation_amt');
         $remainAmt = totalContractRentCountCalculation($termination->contract_id,$terminationDate);
      // Minus from Rent receipt
      $osAmount = $remainAmt - $sumOfReceipt;
      $outstandingOs = $totalOutstanding + $osAmount;
     $today = strtotime(date('Y-m-d'));

     $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds

    $tenancyStartDt = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->select('tenant_contract_start_date')->orderBy('id','asc')->first();
     

        return view('backoffice::Termination.tenant_termination_handover_assigned_view_salesperson',compact('tenantContract','termination','groupedWork','terminationDocument','openTerminationDocument','backHistory','remainingDays','outstandingOs','depositeCheque','tenancyStartDt'));
      }
    /*
    *
    *
    * handoverAssignedInspection 
    *
    */
    public function handoverAssignedInspection($terminationId)
    {
    	clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
     readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
     $termination  = Termination::where('id',$terminationId)->first();
     $tenantContract = TenantContract::where('id',$termination->contract_id)->first(); 
     $works = Work::with('subWork')->get();
     $terminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','!=','TerminationDocument')->get();

     $openTerminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','TerminationDocument')->get();

     $groupedWork = $tenantContract->terminationChecklist->groupBy('work_id');
     $backHistory        = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->get();
	$preBackHistory        = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->where('id','!=',$termination->contract_id)->get();

	$depositeCheque = array();
	$outstandingOs = 0;
	$totalOutstanding = 0;
      foreach($preBackHistory as $prev_contract){

        if($prev_contract->id != $termination->contract_id){

          $pdcDepositCheque = Pdc::where('tenant_contract_id','=',$prev_contract->id)->where('pdc_type','=',2)->orderBy('id','desc')->first();
          if(isset($pdcDepositCheque->pdc_check_no)>0 && empty($depositeCheque)){

            $depositeCheque = $pdcDepositCheque;
          }
        }
		//$outstandingOs += $this->outstandingOs($prev_contract->id);
$totalOutstanding += $this->outstandingOsAmount($prev_contract->id);		
      }
	   $terminationDate      = $termination->termination_date;
      $osAmount   = 0 ;
      // Outstanding Creation
      $sumOfReceipt   = ReceiptsGeneration::where('receipts_generation_type',0)->where('tenant_contract_id',$termination->contract_id)->where('receipts_generation_approval_status',3)->sum('receipts_generation_amt');
         $remainAmt = totalContractRentCountCalculation($termination->contract_id,$terminationDate);
      // Minus from Rent receipt
      $osAmount = $remainAmt - $sumOfReceipt;
      $outstandingOs = $totalOutstanding + $osAmount;

	  $tenancyStartDt = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->select('tenant_contract_start_date')->orderBy('id','asc')->first();
	$totalOtherAmt = 0;
    if(!empty($tenantContract->terminationChecklistOther)){
      foreach($tenantContract->terminationChecklistOther as $other){
        $total = $other->termination_amount;
        $totalOtherAmt+= $total;
      }
    }

     return view('backoffice::Termination.tenant_termination_handover_assigned_inspection',compact('tenantContract','works','termination','terminationDocument','groupedWork','backHistory','openTerminationDocument','outstandingOs','tenancyStartDt','depositeCheque','totalOtherAmt'));

   }
    /*
    *
    *
    * handoverAssignedInspectionEdit 
    *
    */
    public function handoverAssignedInspectionEdit($terminationId)
    {
    	$plucked;
      $termination  = Termination::where('id',$terminationId)->first();
      $tenantContract = TenantContract::where('id',$termination->contract_id)->first(); 
      $works = Work::with('subWork')->get();
      $terminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','!=','TerminationDocument')->get();

      $openTerminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','TerminationDocument')->get();

      $groupedWork = $tenantContract->terminationChecklist->groupBy('work_id');
      $arry = $tenantContract->terminationChecklist->pluck('sub_work_id')->toArray();
      $otherArray= $tenantContract->terminationChecklistOther->pluck('termination_other_work')->toArray();
      //dd($otherArray);
      // Previous Contracts related to this unit and Tenant
      $backHistory        = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->get();
     $preBackHistory        = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->where('id','!=',$termination->contract_id)->get();
	 //dd($backHistory);
	 $tenancyStartDt = TenantContract::where('tenant_id',$tenantContract->tenant_id)->where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->select('tenant_contract_start_date')->orderBy('id','asc')->first();
	 $outstandingOs = $this->outstandingOs($termination->contract_id);
	 $totalOutstanding = 0;
	 $depositeCheque = array();
      foreach($preBackHistory as $prev_contract){

        if($prev_contract->id != $termination->contract_id){

          $pdcDepositCheque = Pdc::where('tenant_contract_id','=',$prev_contract->id)->where('pdc_type','=',2)->orderBy('id','desc')->first();
          if(isset($pdcDepositCheque->pdc_check_no)>0 && empty($depositeCheque)){

            $depositeCheque = $pdcDepositCheque;
          }
        }
		$totalOutstanding += $this->outstandingOsAmount($prev_contract->id);
      }
	  $terminationDate      = $termination->termination_date;
      $osAmount   = 0 ;
      // Outstanding Creation
      $sumOfReceipt   = ReceiptsGeneration::where('receipts_generation_type',0)->where('tenant_contract_id',$termination->contract_id)->where('receipts_generation_approval_status',3)->sum('receipts_generation_amt');
         $remainAmt = totalContractRentCountCalculation($termination->contract_id,$terminationDate);
      // Minus from Rent receipt
      $osAmount = $remainAmt - $sumOfReceipt;
      $outstandingOs = $totalOutstanding + $osAmount;
       $totalOtherAmt = 0;
    if(!empty($tenantContract->terminationChecklistOther)){
      foreach($tenantContract->terminationChecklistOther as $other){
        $total = $other->termination_amount;
        $totalOtherAmt+= $total;
      }
    }
      return view('backoffice::Termination.tenant_termination_handover_assigned_inspection_edit',compact('tenantContract','works','termination','terminationDocument','groupedWork','arry','otherArray','backHistory','openTerminationDocument','outstandingOs','tenancyStartDt','depositeCheque','totalOtherAmt'));

    }
    /*
    *
    * multiple Image uploaed against service report 
    *
    *
    */
    public function imageUpload(Request $request)
    {
      //dd($request->termination_doc_type);

      $this->validate($request, [
        //'report_image_file_name'    => 'required|mimes:png,jpeg,jpg|max:2000',   
        'report_image_file_name'    => 'required|max:2000',             
        ]);

      $url = url()->previous();
      $images = $request->file('report_image_file_name');
      $termination_id = $request->termination_id;
      $work_flow_processes_code = $request->work_flow_processes;
      $termination_doc_type = $request->termination_doc_type;
      $termination_contract = $request->termination_contract;
      if(!empty($images)):
        $files = $request->file('report_image_file_name');
      foreach($files as $key => $file):
        $path = public_path('img');


      $imageName = time().'.'.$request['report_image_file_name'][$key]->getClientOriginalExtension();


      $large_img = Image::make($request->file('report_image_file_name')[$key]->getRealPath());
      $large_img->resize(800, 500);
      $large_img->save($path.'/'.$imageName,100);
      $img_path =    Storage::putFile('public/TenantTerminationImages', new File($path.'/'.$imageName), 'public');

      $thumb_img = Image::make($request->file('report_image_file_name')[$key]->getRealPath());
      $thumb_img->resize(150, 100);
      $thumb_img->save($path.'/'.$imageName,100);           
      $thumb_path = Storage::putFile('public/TenantTerminationImages', new File($path.'/'.$imageName), 'public');


      $image = TerminationDocument::create(['termination_id'=>$termination_id,
       'termination_contract'=>$termination_contract,
       'user_id'=>\Auth::user()->id,
       'termination_doc'=>$img_path,
       'termination_doc_name'=>$imageName,
       'image_path_thumbnail'=>$thumb_path,
       'termination_doc_type'=>$termination_doc_type[$key],
       'work_flow_processes_code'=>$work_flow_processes_code,
       'created_by' => \Auth::user()->id]);
      endforeach;
      endif;
      session()->flash('success', 'Image Uploaded Successfully');

      return redirect($url); 

    }
    /*
    *
    * Inspection Store
    *
    *
    */
    public function terminationInspectionStore(Request $request)
    {
   
    	$this->validate($request, [
          /*'addinspection' => 'required',
          'total' => 'required', */
          'notes' => 'required',                    
          ]);
      $work_id = $request['work_id'];
      $count = $request['count'];
      $other_count = $request['other_count'];


      $next_process_id = $request['work_flow_processes_code'];
      $contract_id = $request['contract_id'];
      $termination_main_key_status = $request['termination_main_key_status'];
      $terminationId = $request['terminationId'];
      $elec_acc = $request['elec_acc'];
      $elec_cls_read = $request['elec_cls_read'];
      $elec_amt = replaceCommaWithDot($request['elec_amt']);
      $water_acc = $request['water_acc'];
      $water_cls_read = $request['water_cls_read'];
      $water_amt = replaceCommaWithDot($request['water_amt']);

      $total = replaceCommaWithDot($request['total']);
      $elec_water_total = replaceCommaWithDot($request['elec_water_total']);
      $maintenance_due = replaceCommaWithDot($request['maintenance_due']);
      $net = replaceCommaWithDot($request['net']);
      $notes = $request['notes'];
      $general = new General;
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);

      $previousProcess = Termination::where('work_flow_processes_code',$next_process_id)->where('id',$terminationId)->where('termination_type',1)->where('contract_id',$contract_id)->orderBy('id','desc')->latest()->first();
      if($previousProcess)$previousProcess->terminationUser()->update(['status' => 0]);

      $terminationTypeStatus = $previousProcess->termination_type_status;
      $termination_remark = $previousProcess->termination_remark;
      $termination_takenover_date = $previousProcess->termination_takenover_date;
      $termination_date = $previousProcess->termination_date;
      $assigned_to  = $previousProcess->assigned_to;
        //dd($subWorks);
        // Outstanding Creation 
      $tenantContract = TenantContract::where('id',$contract_id)->first();
      $tenant_contract_last_paid_date = $tenantContract->tenant_contract_last_paid_date;
      if(empty($tenant_contract_last_paid_date)){
       $tenant_contract_last_paid_date = $tenantContract->tenant_contract_effective_date;
       $paymentTerm = $tenantContract->tenant_contract_payment_type;
       switch($paymentTerm){
         case 1:
         $month = 1;
         break;
         case 2:
         $month = 2;
         break;
         case 3:
         $month = 3;
         break;
         case 4:
         $month = 6;
         break;
         case 5:
         $month = 12;
         break;
       }
       $futurePaymentDate = $tenant_contract_last_paid_date->addMonths($month)->format('Y-m-d');

       if($futurePaymentDate < date('Y-m-d')){
         $os = 'Yes' ;
       }else{
         $os = 'No' ;
       }

     }

     $termination = new Termination; 
     $termination->contract_id     = $contract_id;
     $termination->termination_type  = 1;
     $termination->assigned_to  = $assigned_to;
     $termination->work_flow_processes_code 				= $next_process_id;
     $termination->termination_main_key_status 			= $termination_main_key_status;
     $termination->termination_electricity_acc_no 		= $elec_acc;
     $termination->termination_electricity_close_reading = $elec_cls_read;
     $termination->termination_electricity_amount 		= $elec_amt;
     $termination->termination_water_acc_no 				= $water_acc;
     $termination->termination_water_close_reading 		= $water_cls_read;
     $termination->termination_water_amount 				= $water_amt;
     $termination->termination_total_amount 				= $total;
     $termination->termination_total_elec_water_amount 	= $elec_water_total;
     $termination->termination_discount_maintenance_due 	= $maintenance_due;
     $termination->termination_net_amount 				= $net;
     $termination->termination_notes 					= $notes;
     $termination->termination_type_status 				= $terminationTypeStatus;
     $termination->termination_takenover_date = $termination_takenover_date;
     $termination->termination_date = $termination_date;
     $termination->termination_remark = $termination_remark;
     $termination->os = $os ?? '';
     $termination->created_by           = \Auth::user()->id;
     $termination->save();

     for($i = 0;$i<=$count;$i++){
       $subWorks = $request['addinspection'.$i];
       $work = SubWork::where('id',$subWorks)->first();        	
       $quantity = $request['quantity_'.$i];
       $amount = replaceCommaWithDot($request['amount_'.$i]);
       if(!empty($subWorks)){
        $terminationChecklist = new TerminationChecklist;
        $terminationChecklist->termination_id     = $termination->id;
        $terminationChecklist->termination_contract_id     = $contract_id;	
        $terminationChecklist->work_id     = $work->works_id;
        $terminationChecklist->sub_work_id     = $subWorks;      		
        $terminationChecklist->termination_amount     = $amount;
        $terminationChecklist->termination_quantity     = $quantity;
        $terminationChecklist->work_flow_processes_code     = $next_process_id;
        $terminationChecklist->save();
      }

    }
    for($j = 0;$j<=$other_count;$j++){

      $addinspectionQuantity = $request['addinspectionQuantity_'.$j];
       $addinspectionAmount = replaceCommaWithDot($request['addinspectionAmount_'.$j]);
      $addinspectionOther = $request['addinspectionOther_'.$j];
            //dd($addinspectionAmount);

      if(!empty($addinspectionOther)){
        $ChecklistOther = new TerminationChecklist;
        $ChecklistOther->termination_id     = $termination->id;
        $ChecklistOther->termination_contract_id     = $contract_id;
        $ChecklistOther->termination_other_work     = $addinspectionOther;
        $ChecklistOther->termination_amount     = $addinspectionAmount;
        $ChecklistOther->termination_quantity     = $addinspectionQuantity;
        $ChecklistOther->work_flow_processes_code     = $next_process_id;
        $ChecklistOther->save();
      }
    }






    if($processAssign != false) {

      foreach($processAssign->assign as $val){
       $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
       $termination->save();
     }
          //$termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
          //$termination->save();

   }else{

    $previousProcess = $general->getPreviousOrder($next_process_id);

    if($previousProcess !=0){

      $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

      foreach($previousAssign->assign as $val){
        $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
        $termination->save();
      }
            //$termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
            //$termination->save();

    }else{

      $workFlowProcess = $general->workFlowProcess($next_process_id);
      $termination->terminationUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
      $termination->save();


    }
  }
  $termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
  $termination->save();

  session()->flash('success', 'Termination Inspection Created Successfully  ');
  return redirect()->route('handoverAssigned');
}
    /*
    *
    *
    *Inspection Update 
    *
    *
    */
    public function terminationInspectionUpdate(Request $request,$id)
    {
    	$this->validate($request, [
          /*'addinspection' => 'required',
          'total' => 'required', */
          'notes' => 'required',                    
          ]);

      	//dd($request['test_sign']);
     $count = $request['count'];
     $other_count = $request['other_count'];

        //dd($subWorks = $request['addinspection4']);
     $next_process_id = $request['work_flow_processes_code'];
      	//$contract_id = $request['contract_id'];
     $termination_main_key_status = $request['termination_main_key_status'];
     $terminationId = $request['terminationId'];
     $elec_acc = $request['elec_acc'];
     $elec_cls_read = $request['elec_cls_read'];
     $elec_amt = replaceCommaWithDot($request['elec_amt']);
     $water_acc = $request['water_acc'];
     $water_cls_read = $request['water_cls_read'];
     $water_amt = replaceCommaWithDot($request['water_amt']);

     $total = replaceCommaWithDot($request['total']);
     $elec_water_total = replaceCommaWithDot($request['elec_water_total']);
     $maintenance_due = replaceCommaWithDot($request['maintenance_due']);
     $net = replaceCommaWithDot($request['net']);
     $notes = $request['notes'];
     $general = new General;
     $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);

      	//$previousProcess = Termination::where('work_flow_processes_code',$next_process_id)->where('id',$terminationId)->where('termination_type',1)->where('contract_id',$contract_id)->orderBy('id','desc')->latest()->first();
        //if($previousProcess)$previousProcess->terminationUser()->update(['status' => 0]);


     $terminationDetails = Termination::find($id);
         // Outstanding Creation 
     $tenantContract = TenantContract::where('id',$terminationDetails->contract_id)->first();
     $tenant_contract_last_paid_date = $tenantContract->tenant_contract_last_paid_date;
     if(empty($tenant_contract_last_paid_date)){
       $tenant_contract_last_paid_date = $tenantContract->tenant_contract_effective_date;
       $paymentTerm = $tenantContract->tenant_contract_payment_type;
       switch($paymentTerm){
         case 1:
         $month = 1;
         break;
         case 2:
         $month = 2;
         break;
         case 3:
         $month = 3;
         break;
         case 4:
         $month = 6;
         break;
         case 5:
         $month = 12;
         break;
       }
       $futurePaymentDate = $tenant_contract_last_paid_date->addMonths($month)->format('Y-m-d');
       if($futurePaymentDate < date('Y-m-d')){
         $os = 'Yes' ;
       }else{
         $os = 'No' ;
       }

     }
     $terminationDetails->termination_main_key_status 			= $termination_main_key_status;
     $terminationDetails->termination_electricity_acc_no 		= $elec_acc;
     $terminationDetails->termination_electricity_close_reading = $elec_cls_read;
     $terminationDetails->termination_electricity_amount 		= $elec_amt;
     $terminationDetails->termination_water_acc_no 				= $water_acc;
     $terminationDetails->termination_water_close_reading 		= $water_cls_read;
     $terminationDetails->termination_water_amount 				= $water_amt;
     $terminationDetails->termination_total_amount 				= $total;
     $terminationDetails->termination_total_elec_water_amount 	= $elec_water_total;
     $terminationDetails->termination_discount_maintenance_due 	= $maintenance_due;
     $terminationDetails->termination_net_amount 				= $net;
     $terminationDetails->termination_notes 					= $notes;
     $terminationDetails->os 					= $os;
     $terminationDetails->updated_by           = \Auth::user()->id;
     $terminationDetails->save();

       	//delete checklist above update 
     TerminationChecklist::where('termination_contract_id',$terminationDetails->contract_id)->delete();
     for($i = 0;$i<=$count;$i++){
       $subWorks = $request['addinspection'.$i];
       $work = SubWork::where('id',$subWorks)->first();        	
       $quantity = $request['quantity_'.$i];
       $amount = replaceCommaWithDot($request['amount_'.$i]);
       if(!empty($subWorks)){
        $terminationChecklist = new TerminationChecklist;
        $terminationChecklist->termination_id     = $id;
        $terminationChecklist->termination_contract_id     = $terminationDetails->contract_id;	
        $terminationChecklist->work_id     = $work->works_id;
        $terminationChecklist->sub_work_id     = $subWorks;      		
        $terminationChecklist->termination_amount     = $amount;
        $terminationChecklist->termination_quantity     = $quantity;
        $terminationChecklist->work_flow_processes_code     = $next_process_id;
        $terminationChecklist->save();
      }

    }


    for($j = 0;$j<=$other_count;$j++){

      $addinspectionQuantity = $request['addinspectionQuantity_'.$j];
      $addinspectionAmount = replaceCommaWithDot($request['addinspectionAmount_'.$j]);
      $addinspectionOther = $request['addinspectionOther_'.$j];
            //dd($addinspectionAmount);

      if(!empty($addinspectionOther)){
       $ChecklistOther = new TerminationChecklist;
       $ChecklistOther->termination_id     = $id;
       $ChecklistOther->termination_contract_id     = $terminationDetails->contract_id;
       $ChecklistOther->termination_other_work     = $addinspectionOther;
       $ChecklistOther->termination_amount     = $addinspectionAmount;
       $ChecklistOther->termination_quantity     = $addinspectionQuantity;
       $ChecklistOther->work_flow_processes_code     = $next_process_id;
       $ChecklistOther->save();
     }
   }


   session()->flash('success', 'Termination Inspection Updated Successfully  ');
   return redirect()->route('handoverAssigned');
 }
    /*
    *
    *
    * Termination Signature Store
    *
    *
    */
    public function terminationSignatureStore(Request $request)
    {

     $termination_Id = Session::get('termination_Id');
     $url = route('handoverAssigned');
     $img = $request['saveSignature']; 
     $testSignature = $request['testSignature']; 

     if($testSignature != null)
     {
       $png_url = time().".png";
       $path = public_path('img').'/' . $png_url;

       Image::make(file_get_contents($img))->save($path,100); 
       $img_path_ar =    Storage::putFile('public/TerminationTenantSignature', new File($path), 'public');

       Termination::where('id',$termination_Id)->update(['termination_tenant_signature'=>$img_path_ar,'termination_review_status'=>1]);
     }
	    //session()->flash('success', 'Termination Inspection Created Successfully  ');
     return $url;
   }
    /*
    *
    *
    * Tenant Termination Send for Review
    *
    *
    */
    public function tenantTerminationReview($termination_id)
    {
    	$url = url()->previous();
    	Termination::where('id',$termination_id)->update(['termination_review_status'=>2]);
    	return redirect($url);
    }
    /*
    *
    * Takeover For Termination
    *
    *
    */
    public function takeoverForTermination(Request $request)
    {
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

      
      $quick_url =  $route   = route('takeoverForTermination');



   $tenantTerminations =     ViewTenantTermination::
                                    where('work_flow_processes_code', '=', 504)
                                  ->where('termination_type', '=', 1)
                                  ->where('tenant_contract_status',1)   
                                  ->filterUsers()->filter($request)
                                //  ->distinct()
                                  ->sortable()->paginate($this->noOfRecord); 


/*

      $tenantTermination = Termination::whereHas('tenantContract', function ($query) use($rolesNames,$request){
        $query->where('tenant_contract_status',1)
        ->filter($request)
        ->sortable();

      })->whereHas('terminationUsers', function ($query) {
        $query->where('status','=',1);
      })                             
      ->where('work_flow_processes_code', '=', 504);

      if (in_array('super_admin', $rolesNames) === false) {
        $tenantTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
        });
      }
      $tenantTerminations = $tenantTermination->sortable()->paginate($this->noOfRecord);
      */
      if(isset($request->ajax))
       return view('backoffice::Termination.tenant_termination_takenover_list_ajax',compact('tenantTerminations','request','route'));

     return view('backoffice::Termination.tenant_termination_takenover_list',compact('tenantTerminations','enquiry_fields','operations','quick_url'));
   }
   public function signature()
   {

    return view('backoffice::Termination.tenant_termination_signature');
  }
    /*
    *
    * Take Over For  Termination View
    *
    *
    */
    public function takeoverForTerminationView($terminationId)
    {
    	clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
     readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
     $termination = Termination::where('id',$terminationId)->first();
     $tenantContract = TenantContract::where('id',$termination->contract_id)->first();
     $terminationNotes = Termination::where('contract_id',$termination->contract_id)->where('termination_notes','!=',null)->select('id','termination_notes','work_flow_processes_code','created_by','created_at')->get();
     $groupedWork = $tenantContract->terminationChecklist->groupBy('work_id');
     $terminationDocument = TerminationDocument::where('termination_contract',$termination->contract_id)->where('termination_doc_type','!=','TerminationDocument')->get();

     $openTerminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','TerminationDocument')->get();

     $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
     $today = strtotime(date('Y-m-d'));

     $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds 

     $outstandingOs = $this->outstandingOs($termination->contract_id);
     return view('backoffice::Termination.tenant_termination_takenover_view',compact('tenantContract','termination','groupedWork','terminationDocument','terminationNotes','openTerminationDocument','remainingDays','outstandingOs'));
   }
    /*
    *
    * Terminated Contract   
    *
    *
    */
    public function tenantTerminatedContract(Request $request)
    {
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

      $quick_url =  $route   =  route('tenantTerminatedContract');


      $tenantTerminations =     ViewTenantTermination::
                                    where('work_flow_processes_code', '=', 505)
                                  ->where('tenant_renewal_termination_status',8)
                                  ->where('termination_type', '=', 1)
                                  ->where('tenant_contract_status',0) 
                                 ->filterUsers()->filter($request)
                                //  ->distinct()
                                  ->sortable()->paginate($this->noOfRecord); 
//dd($tenantTerminations);

/*

      $tenantTermination = Termination::whereHas('tenantContract', function ($query) use($rolesNames,$request){
        $query->where('tenant_contract_status',0)
        ->where('tenant_renewal_termination_status',8)
        ->filter($request)
        ->sortable();

      })->whereHas('terminationUsers', function ($query) {
        $query->where('status','=',1);
      })->filter($request)                             
      ->where('work_flow_processes_code', '=', 505);

      if (in_array('super_admin', $rolesNames) === false) {
        $tenantTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
        });
      }
      $tenantTerminations = $tenantTermination->sortable()->paginate($this->noOfRecord);
      */
      if(isset($request->ajax))
       return view('backoffice::Termination.tenant_termination_terminated_contracts_list_ajax',compact('tenantTerminations','request','route'));

     return view('backoffice::Termination.tenant_termination_terminated_contracts_list',compact('tenantTerminations','enquiry_fields','operations','quick_url'));
   }
    /*
    *
    * tenantTerminatedContract  View
    *
    *
    */
    public function tenantTerminatedContractView($terminationId)
    {
    	clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
     readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
     $termination = Termination::where('id',$terminationId)->first();
     $tenantContract = TenantContract::where('id',$termination->contract_id)->first();
     $terminationNotes = Termination::where('contract_id',$termination->contract_id)->where('termination_notes','!=',null)->select('id','termination_notes','work_flow_processes_code','created_by','created_at')->get();

     $openTerminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','TerminationDocument')->get();

     $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
     $today = strtotime(date('Y-m-d'));

     $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds 


     return view('backoffice::Termination.tenant_termination_terminated_contract_view',compact('tenantContract','termination','terminationNotes','openTerminationDocument','remainingDays'));
   }
    /*
  *  
  * Complaint assign Reminder
  *
  */
    public function inspectionReminder($terminationId)
    {

      $user = [];
      $users = [];
      $termination = Termination::where('id',$terminationId)->first();
      $tenantContract = TenantContract::where('id',$termination->contract_id)->first();
      $next_process_id = $termination->work_flow_processes_code;
    //dd($termination->assigned_to);
      $general = new General;
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);
      if(empty($termination->assigned_to)){
        if($processAssign == false) {

          $previousProcess = $general->getPreviousOrder($next_process_id);

          if($previousProcess !=0){

            $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 
            $user_ids =   $previousAssign->assign->where('user_id','>',0)->pluck('user_id');
            $roles = $previousAssign->assign->where('user_id',null); 
            $roles = $roles->pluck('role_id');
            if(count($roles) > 0){

              $default_user =  User::role($roles)->pluck('id');
              array_push($users,$default_user);

            }
            array_push($user,$users);

          }else{
            $workFlowProcess = $general->workFlowProcess($next_process_id);
            $default_user = $workFlowProcess->default_user;        
            array_push($user,$default_user);
          }

        }else {

          $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
          $roles = $processAssign->assign->where('user_id',null); 
          $roles = $roles->pluck('role_id');
          if(count($roles) > 0){


            $default_user =  User::role($roles)->pluck('id');
            array_push($users,$default_user);

          }
          array_push($user,$users);

        }
      }else{
        $user[] = $termination->assigned_to;
      }


      
      $user = array_flatten($user);      
      $users_notify =  \App\User::whereIn('id',$user)->get();
      $roles = Role::whereIn('id',$users_notify->pluck('default_role'))->first()->name;
      if(count($users_notify) == 1){
        $roles = \App\User::whereIn('id',$user)->first()->username;
      }
      $termination->text = "Handover Assigned Reminder ";
      $termination->href = url("handoverAssigned/".$termination->id."/handoverAssignedInspection/"); 
      event(new TenantTermination($termination,$users_notify));

      session()->flash('success', ' Notification Reminder Send To '.ucwords(str_replace('_', ' ',$roles)));

      return redirect()->route('handoverAssigned');       
    }
  /*
  *
  *
  * Termination Penalty
  *
  */
  public function tenantTerminationPenalty(Request $request)
  {
  	$terminationId = $request->terminationId;
  	$termination = Termination::where('id',$terminationId)->first();
  	$tenantContract = TenantContract::where('id',$termination->contract_id)->first();
  	return view('backoffice::Termination.tenant_termination_penalty_modal',compact('tenantContract','termination'));
  }
  /*
  *
  *
  * Penalty 
  *
  *
  */
  public function tenantTerminationPenaltyStore(Request $request)
  {
  	$this->validate($request, [
     'tenant_penalty_start_date' => 'required',
     'tenant_penalty_valid_to_date' => 'required',                    
     ]);
  	$invoice_amount = $request->invoice_amount;
  	$tenant_penalty_start_date = $request->tenant_penalty_start_date;
  	$tenant_penalty_valid_to_date = $request->tenant_penalty_valid_to_date;
  	$tenant_contract_id = $request->tenant_contract_id;
  	$tenant_penalty_invoice_amt = $request->tenant_penalty_invoice_amt;

  	TenantContract::where('id',$tenant_contract_id)->update(['tenant_penalty_start_date'=>$tenant_penalty_start_date,'tenant_penalty_valid_to_date'=>$tenant_penalty_valid_to_date,'tenant_penalty_invoice_amt'=>$tenant_penalty_invoice_amt]);

   $tenantInvoiceLatest = Invoice::orderBy('id', 'desc')->first();

   $prefix  = prefixData('tenant_invoice_prefix')->configuration_value;

   if(!empty($tenantInvoiceLatest))
    $invoiceNo = str_pad($tenantInvoiceLatest->id+1,4,'0',STR_PAD_LEFT);
  else
    $invoiceNo = str_pad(1,4,'0',STR_PAD_LEFT);

  	//invoice created for penalty
  $acc_parameter  = AccountParams::where('acc_params_tran_desc','customer_rent_invoice')->first();

    // Debit Amount
  if($acc_parameter->acc_params_dr_acc)
    $acc_code_dr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_dr_acc)->first();

    // Credit Amount
  if($acc_parameter->acc_params_cr_acc)
    $acc_code_cr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_cr_acc)->first();

  $invoice = New Invoice;
  $invoice->tenant_contract_id = $tenant_contract_id;
  $invoice->tenant_invoice_type = 1;
  $invoice->tenant_invoice_no =$prefix.$invoiceNo;
  $invoice->tenant_invoice_date = date('Y-m-d');
  $invoice->tenant_invoice_amt = $tenant_penalty_invoice_amt;
  $invoice->tenant_invoice_desc = 'Penalty Amount';
  $invoice->created_by = \Auth::user()->id;
  $invoice->save() ;
    // Debit Insertation
  TenantInvoiceDimension::create(['invoice_id' => $invoice->id,
    'dim1' =>($acc_code_dr->dim1)?$acc_code_dr->dim1:'',
    'dim2' =>($acc_code_dr->dim2)?$acc_code_dr->dim2:'',
    'dim3' =>($acc_code_dr->dim3)?$acc_code_dr->dim3:'',
    'dim4' =>($acc_code_dr->dim4)?$acc_code_dr->dim4:'',
    'dim5' =>($acc_code_dr->dim5)?$acc_code_dr->dim5:'',
    'debit_amount' =>$tenant_penalty_invoice_amt,
    'credit_amount' =>0,
    'ac_codes_id' =>$acc_parameter->id,
    'acc_code_no' =>$acc_parameter->acc_params_dr_acc,
    'acc_code_desc'=>$acc_code_dr->acc_code_desc,
    'dimension_type' =>($acc_parameter->acc_params_dr_type)?trim($acc_parameter->acc_params_dr_type):'',
    'created_by' => \Auth::user()->id,

    ]);

    // Credit Insertation
  TenantInvoiceDimension::create(['invoice_id' => $invoice->id,
    'dim1' =>($acc_code_cr->dim1)?$acc_code_cr->dim1:'',
    'dim2' =>($acc_code_cr->dim2)?$acc_code_cr->dim2:'',
    'dim3' =>($acc_code_cr->dim3)?$acc_code_cr->dim3:'',
    'dim4' =>($acc_code_cr->dim4)?$acc_code_cr->dim4:'',
    'dim5' =>($acc_code_cr->dim5)?$acc_code_cr->dim5:'',
    'debit_amount' =>0,
    'credit_amount' =>$tenant_penalty_invoice_amt,
    'ac_codes_id' =>$acc_parameter->id,
    'acc_code_no' =>$acc_parameter->acc_params_cr_acc,
    'acc_code_desc'=>$acc_code_cr->acc_code_desc,
    'dimension_type' =>($acc_parameter->acc_params_cr_type)?trim($acc_parameter->acc_params_cr_type):'',
    'created_by' => \Auth::user()->id,

    ]);

  session()->flash('success', ' Penalty Created Successfully');
  return redirect()->route('tenantTerminatedContract');

}
  /*
  *
  *
  * Tremination Email
  *
  */
  public function terminationEmail($termination_id)
  {
  	$termination = Termination::where('id',$termination_id)->first();
  	$tenantContract = TenantContract::where('id',$termination->contract_id)->first();
  	$groupedWork = $tenantContract->terminationChecklist->groupBy('work_id');
  	$tenantDetail = Tenant::where('id',$tenantContract->tenant_id)->first();
  	$tenantEmail = $tenantDetail->tenant_contact_email;
  	if($tenantEmail == "")$tenantEmail = $tenantDetail->tenant_personal_email;

  	$termination->tenantName = $tenantDetail->tenant_name;
  	if(!empty($tenantEmail)){

      Mail::to($tenantEmail)->send(new TerminationTenantEmail($termination,$tenantContract,$tenantContract->terminationChecklistOther,$groupedWork));
      session()->flash('success', ' Mail Send Successfully');
      return redirect()->route('handoverAssigned');
    }else{
      session()->flash('success', ' Tenant Email Id Not Found');
      return redirect()->route('handoverAssigned');
    }

  }
  /*
    *
    *
    * Termination Request Serach 
    *
    *
    */
  public function tenantTerminationRequestSearch(Request $request)
  {

    $enquiry_fields = [
    'tenant_contract_no' => 'Contract No',
    'building' => 'Building',
    'unit' => 'Unit',
         /*'startDate' => 'Start Date',     
         'endDate' => 'End Date',*/ 
         'tenant' => 'Tenant',     
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

         $result = array();
         if(isset($request)){

          $contractRenewal = new ContractRenewal ;       
          $result =     $contractRenewal->renewalSearch($request);   
          $request->flash();
        }
        if(isset($request->route))
          $route   =  $request->route;

        $tenantTermination = Termination::whereHas('tenantContract', function ($query) use($rolesNames,$result){
          $query->where('tenant_contract_status',1)
          ->where('tenant_renewal_termination_status',7)
          ->closure($result)
          ->sortable();

        })->whereHas('terminationUsers', function ($query) {
          $query->where('status','=',1);
        })                             
        ->where('work_flow_processes_code', '=', 501)
        ->where('termination_type', '=', 1);

        if (in_array('super_admin', $rolesNames) === false) {
          $tenantTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
          });
        }
        $tenantTerminations = $tenantTermination->paginate($this->noOfRecord);
        if(isset($request->ajax))
         return view('backoffice::Termination.tenant_termination_open_list_ajax',compact('tenantTerminations','request','route'));

       return view('backoffice::Termination.tenant_termination_open_list',compact('tenantTerminations','enquiry_fields','operations'));

     }
/*
*
*
*Supervisor note adding
*
*/
public function supervisorNoteStore(Request $request){
  $url = $request['current_url'];
  $termination_id = $request->termination_id;
  $supervisor_comment = $request->supervisor_comment;
  Termination::where('id',$termination_id)->update(['supervisor_comment'=>$supervisor_comment]);
  session()->flash('success', 'Comment Added');
  return redirect($url);
}

/*
*
*
*Send mail to tenant from Take Over Supervisor
*
*/
public function inspectionSendMail($termination_id){
  $termination  = Termination::where('id',$termination_id)->first();
  $tenantContract = TenantContract::where('id',$termination->contract_id)->first();
  //dd($tenantContract);
  $groupedWork = $tenantContract->terminationChecklist->groupBy('work_id');
  $terminationDocument = TerminationDocument::where('work_flow_processes_code',$termination->work_flow_processes_code)->where('termination_contract',$termination->contract_id)->where('termination_doc_type','!=','TerminationDocument')->get();
  $tenant_email = $tenantContract->tenant->tenant_contact_email ?? $tenantContract->tenant->tenant_personal_email;
 //dd($tenant_email);
  if(!empty($tenant_email)){
    //Mail::to($tenant_email)->send(new InspectionSendEmail($tenantContract));
   Mail::to($tenant_email)->send(new InspectionSendEmail($termination,$tenantContract,$tenantContract->terminationChecklistOther,$groupedWork));
   session()->flash('success', ' Mail Send Successfully');
   return redirect()->route('handoverAssigned');
 }
}
/*
  *
  * Termination Resubmit Modal Comment
  *
  */
public function terminateResubmitOrTerminateModal(Request $request) {

  $workflow_id    = $request['workflow_id'];
  $contractId     = $request['contractId'];
  $terminatId     = $request['terminatedId'];
  $action_key     = $request['action_key'];

  return view('backoffice::Termination.tenant_termination_resubmit_or_terminate_modal',compact('workflow_id','contractId','terminatId','action_key'));

}

/*
*
* Termination Resubmit Modal Comment Action
*
*/

public function terminateResubmitOrTerminateModalAction(Request $request)
{   
  $contractId     = $request['contractId'];
  $terminationId  = $request['terminatId'];
  $stage          = $request['workflow_id'];
  $action_key      = $request['action_key'];
  $comment        = $request['comment'];

  $currentUrl     = url()->previous();

  $general        = new General;
  $next_process_id= $general->nextProcessFromAction($stage,$action_key);

  $processAssign  = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);

  $tenantContract = TenantContract::where('id',$contractId)->first();

  $previousFlow   = Termination::where('work_flow_processes_code',$stage)->where('contract_id',$contractId)->where('termination_type',1)->orderBy('id','desc')->latest()->first();
  if($previousFlow)
    $previousFlow->terminationUser()->update(['status' => 0]);

  $previousProcess= Termination::where('work_flow_processes_code',$next_process_id)->where('contract_id',$contractId)->where('termination_type',1)->orderBy('id','desc')->latest()->first();
    //dd($previousProcess);
  if($previousProcess)$previousProcess->terminationUser()->update(['status' => 0]);


    //old stage termination details restore
  $terminationTypeStatus = $previousFlow->termination_type_status;
  $termination_remark = $previousFlow->termination_remark;
  $termination_takenover_date = $previousFlow->termination_takenover_date;
    //$termination_date = $previousFlow->termination_date;
  //$termination_date = date('Y-m-d');
  $termination_date = $previousFlow->termination_date;
  $termination_main_key_status = $previousFlow->termination_main_key_status;
  $elec_acc = $previousFlow->termination_electricity_acc_no;
  $elec_cls_read = $previousFlow->termination_electricity_close_reading;
  $elec_amt = $previousFlow->termination_electricity_amount;
  $water_acc = $previousFlow->termination_electricity_amount;
  $water_cls_read = $previousFlow->termination_water_close_reading;
  $water_amt = $previousFlow->termination_water_amount;
  $total = $previousFlow->termination_total_amount;
  $elec_water_total = $previousFlow->termination_total_elec_water_amount;
  $maintenance_due = $previousFlow->termination_discount_maintenance_due;
  $net = $previousFlow->termination_net_amount;
  $notes = $comment;
  $termination_tenant_signature = $previousFlow->termination_tenant_signature;
  $termination_tenant_review_status = $previousFlow->termination_review_status;
  $os  = $previousFlow->os;

  $termination = new Termination; 
  $termination->contract_id     = $contractId;
  $termination->termination_type  = 1;
  $termination->work_flow_processes_code = $next_process_id;
  $termination->termination_type_status = $terminationTypeStatus;
  $termination->termination_takenover_date = $termination_takenover_date;
  $termination->termination_date = $termination_date;
  $termination->termination_remark = $termination_remark;
  $termination->termination_main_key_status       = $termination_main_key_status;
  $termination->termination_electricity_acc_no    = $elec_acc;
  $termination->termination_electricity_close_reading = $elec_cls_read;
  $termination->termination_electricity_amount    = $elec_amt;
  $termination->termination_water_acc_no        = $water_acc;
  $termination->termination_water_close_reading     = $water_cls_read;
  $termination->termination_water_amount        = $water_amt;
  $termination->termination_total_amount        = $total;
  $termination->termination_total_elec_water_amount   = $elec_water_total;
  $termination->termination_discount_maintenance_due  = $maintenance_due;
  $termination->termination_net_amount        = $net;
  $termination->termination_notes           = $notes;
  $termination->termination_tenant_signature      = $termination_tenant_signature;


    if($action_key == 'RESUB'){ // Resubmit Status to identify
      $termination->is_resubmit             = 2;
    }
    
    $termination->os                            = $os;
    if($next_process_id == 503)
    {
      $AssignedTo = $previousProcess->assigned_to;
      $technician = User::where('id',$AssignedTo)->first();
    }

    if(!empty($AssignedTo))
      $termination->assigned_to = $AssignedTo;

    $termination->created_by           = \Auth::user()->id;
    $termination->save();
    if($processAssign != false) {

      foreach($processAssign->assign as $val){
       $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
       $termination->save();
     }

   }else{

    $previousProcess = $general->getPreviousOrder($next_process_id);

    if($previousProcess !=0){

      $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

      foreach($previousAssign->assign as $val){
        $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
        $termination->save();
      }
      
    }else{

      $workFlowProcess = $general->workFlowProcess($next_process_id);
      $termination->terminationUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
      $termination->save();

      
    }
  }
  $termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
  $termination->save();

    // if terminated update status
  if($next_process_id == 505){

    $contracts = TenantContract::where('id',$termination->contract_id)->first();

    $contractUpdate = TenantContract::where('id',$termination->contract_id)->update(['tenant_contract_status'=>0,'tenant_renewal_termination_status'=>8]);

    $unitUpdate = Unit::where('id',$contracts->unit_id)->update(['unit_vaccant_status'=>0]);

      //Remaining pdc and invoices cancel    
    $today  = date('Y-m-d');
    //$remainingPdc = Pdc::whereIn('tenant_contract_id',[$termination->contract_id])->whereDate('pdc_check_date','>=',$termination->termination_date)->pluck('id');
   // if(count($remainingPdc)>0)
     // $pdcUpdate = Pdc::whereIn('id',$remainingPdc)->update(['pdc_cancel_date'=>$today,'pdc_cancel_reason'=>4,'pdc_cancel_by'=>\Auth::user()->id]);
        //Invoice
    $remainingInvoice = Invoice::whereIn('tenant_contract_id',[$termination->contract_id])->whereDate('tenant_invoice_date','>=',$termination->termination_date)->pluck('id');
    if(count($remainingInvoice)>0)
      $invoiceUpdate = Invoice::whereIn('id',$remainingInvoice)->update(['tenant_invoice_cancelled_date'=>$today,'tenant_invoice_status'=>2]);   

		$unitInfo   = Unit::where('id',$contracts->unit_id)->first();   
    $building   = Building::where('id',$unitInfo->building_id)->first();
    $unit_type  = UnitType::where('id',$unitInfo->unit_type_id)->first();
    $location   = Location::where('id',$building->location_id)->first();

    //Insertation Vacany Table only Vacant From 
    VacantVacancyLoss::create([  
        'building_id'=> $building->id,
        'unit_id'=> $unitInfo->id,
        'building_name'=> $building->building_name,
        'building_no'=> $building->building_no,
        'unit_no'=> $unitInfo->unit_no,
        'unit_type'=> $unit_type->unit_types_name,
        'vacant_from'=> $termination->termination_date,
        'vacant_to'=>null,
        'location_id'=>$building->location_id,
        'location_name'=>$location->locations_name,
        'vacant_days'=>null,
        'rent_per_month'=>$contracts->tenant_contract_rent,
        'vacany_loss'=>null,
        'status'=>1,
        'unit_type_id' => $request->unit_type_id, 
        'created_by' => \Auth::user()->id,
    ]);

  }
  /* Notification */
  $process = $general->workFlowProcessNames($next_process_id);

  clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);
  readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);  
  $termination->text = "Tenant Termination - ".$process->work_flow_processes_name;

  switch($next_process_id){
    case "503":
    $users = User::role(['maintenance_supervisor'])->get(); 
    $users = array_flatten($users);
    $termination->href = url("handoverAssigned/".$termination->id."/handoverAssignedView/");
    $termination->text = "Tenant Termination - ".$process->work_flow_processes_name ." Resubmit";
    $currentUrl = 'takeoverForTermination';
    event(new TenantTermination($termination,$technician));
    break;
    case "504":
    $users = User::role(['backoffice_executive'])->get(); 
    $users = array_flatten($users);
    $termination->href = url("takeoverForTermination/".$termination->id."/takeoverForTerminationView/");
    $currentUrl = 'handoverAssigned';
    break;
    case "505":
    $users = User::role(['backoffice_executive'])->get(); 
    $users = array_flatten($users);
    $termination->href = url("tenantTerminatedContract/".$termination->id."/tenantTerminatedContractView/");
    $currentUrl = 'takeoverForTermination';
    break;
  }
  event(new TenantTermination($termination,$users));
  /* End */
  session()->flash('success', 'Termination Successfully Moved to '.$process->work_flow_processes_name);
  return redirect()->route($currentUrl);

}
public function terminationDocumentStore(Request $request){

  $this->validate($request, [
        //'report_image_file_name'    => 'required|mimes:png,jpeg,jpg|max:2000',   
    'report_image_file_name'    => 'required|max:2000',             
    ]);
  $url = url()->previous();
  $termination_id = $request->termination_id;
  $work_flow_processes_code = $request->work_flow_processes;
  $termination_doc_type = $request->termination_doc_type;
  $termination_contract = $request->termination_contract;
  $documents = $request->file('report_image_file_name');
  if(!empty($documents)) {
    foreach($documents as $key=>$document){
      $imageName = time().'.'.$document->getClientOriginalName();
      $img_path =    Storage::putFile('public/TenantTerminationImages', $document);
      $terminationDocuments =  TerminationDocument::
      create(['termination_id'=>$termination_id,
       'termination_contract'=>$termination_contract,
       'user_id'=>\Auth::user()->id,
       'termination_doc'=>$img_path,
       'termination_doc_name'=>$imageName,
       'image_path_thumbnail'=>'',
       'termination_doc_type'=>$termination_doc_type[$key],
       'work_flow_processes_code'=>$work_flow_processes_code,
       'created_by' => \Auth::user()->id]);
    }
  }
  session()->flash('success', 'Termination Document Uploaded Successfully');
  return redirect($url);

}
public function terminationReferBack(Request $request){
  $termination_id = $request->termination_id;
  $contract_id = $request->contract_id;
  return view('backoffice::Termination.tenant_termination_referback_modal',compact('termination_id','contract_id'));
}
public function terminationReferBackStore(Request $request)
{
  $termination_id = $request->termination_id;
  $termination_notes = $request->termination_notes;
  $contractId = $request->contract_id;
  $stage = 304;
  $status = 2;
  $oldId = 0;
  $nowUrl = url()->previous();
  $are = [];

	// Select Building ARE
	$contract =
	TenantContract::where('id',$contractId)
		   ->whereHas('building', function ($query){       
			   $query->whereHas('areBuildings', function ($query){
				 $query->where('user_id','!=', 0);
				 return $query;
			   });   
			  return $query;   
			})->first();

	 if(!empty($contract))
	 $are = $contract->building->areBuildings->pluck('user_id');

	// Select Users for notification  ARE & BOE
	$users = User::role(['backoffice_executive'])
	->when((count($are)>0), function($query)use($are){
	   $query->orWhereIn('id',$are);
	})
	->get();

	$users = array_flatten($users);  

  Termination::where('id',$termination_id)->update(['termination_refer_back'=>1,'termination_notes'=>$termination_notes]);

  $termination = Termination::find($termination_id);

$contractRenewal = new ContractRenewal ; 
$contractRenewal->tenantRenewalStage($contractId,$stage,$status,$oldId); 

//Refer back status update
 Renewal::where('old_contract_id',$contractId)->update([
                  'refer_back_status' => 1
                ]);

$termination->href = url('tenantContractUnderRenewal/'.$contractId);
//dd($termination);
event(new TenantTerminationReferBack($termination,$users)); 


  session()->flash('success', 'The status has been Refer Back for Renewal');
   return redirect($nowUrl);
}
/*
    *
    * OPen for Termination Approve Or Reject Note
    *
  */
  public function terminateApproveOrRejectModal(Request $request)
  {
        $terminatId     = $request['terminatedId'];
        $action_key     = $request['action_key'];
        $box_title      = ($request['action_key']==3)?'Approve':'Reject';
        return view('backoffice::Termination.tenant_termination_approve_reject_modal',compact('terminatId','action_key','box_title'));

  }
  /*
    *
    * OPen for Termination Approve Or Reject Note Action
    *
  */
  public function terminateApproveOrRejectModalAction(Request $request)
  {
      $terminatId     = $request['terminationId'];
      $status         = $request['status'];
      $note           = $request['note'];
     
      
      $termination = Termination::where('id',$terminatId)->orderBy('id','desc')->latest()->first();
      switch($status){
        case 3: // Approve
          Termination::where('id',$terminatId)->update(['termination_type_status'=>$status]);
          $comment              = new TenantLandlordTerminationRenewalComment; 
          $comment->stage       = $termination->work_flow_processes_code;
          $comment->entity_id   = $termination->id;
          $comment->note        = $note;
          $comment->entity_name = 1; // Tenant Termination
          $comment->contract_id = $termination->contract_id; 
          $comment->created_by   = \Auth::user()->id;
          $comment->save();

          /* Notification */
          $users = User::role(['backoffice_executive'])->get(); 
          $users = array_flatten($users);              
          clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);
          $termination->href =  url('tenantTermination/'.$termination->contract_id);    
          $termination->text = "Tenant Termination Approved"; 
          event(new TenantTermination($termination,$users));
          /* End */
          $msg = 'Termination  Approval Approved';
          session()->flash('success', $msg);
          return redirect()->route('tenantTerminationApproval');
          break;

          case 4:   // Reject

          Termination::where('id',$terminatId)->update(['termination_type_status'=>$status]);

          $contracts = TenantContract::where('id',$termination->contract_id)->first();
          TenantContract::where('id',$termination->contract_id)->update(['tenant_renewal_termination_status'=>0]);
          Termination::where('id',$terminatId)->update(['contract_id'=>0]);

          $unitUpdate = Unit::where('id',$contracts->unit_id)->update(['unit_vaccant_status'=>1]);

          

          $comment              = new TenantLandlordTerminationRenewalComment; 
          $comment->stage       = $termination->work_flow_processes_code;
          $comment->entity_id   = $termination->id;
          $comment->note        = $note;
          $comment->entity_name = 1; // Tenant Termination
          $comment->contract_id = $termination->contract_id;
          $comment->created_by   = \Auth::user()->id;
          $comment->save();
          /* Notification */
          $users = User::role(['backoffice_executive'])->get(); 
          $users = array_flatten($users);              
          clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);
          $termination->href =  url('tenantTermination/'.$termination->contract_id);    
          $termination->text = "Tenant Termination Rejected"; 
          event(new TenantTermination($termination,$users));
          /* End */
          $msg = 'Termination  Approval Rejected';
          session()->flash('success', $msg);
          return redirect()->route('tenantTerminationApproval');
      }

  }



  /***  OutStanding OS    *****/
  public function outstandingOs($contractId){

      $tenantContract = TenantContract::where('id',$contractId)->first();

      $today      = date('Y-m-d');

      $sumOfReceipt   = ReceiptsGeneration::where('receipts_generation_type',0)
            ->where('tenant_contract_id',$contractId)
            ->where('receipts_generation_approval_status',3)
            ->sum('receipts_generation_amt');

     // $remainAmt = contractRentCountCalculation($tenantContract->tenant_contract_effective_date,$today, $tenantContract->tenant_contract_rent );
	 
            $termination = Termination::where('id',$tenantContract->terminationContract->id)->orderBy('id','desc')->first();
            $terminationDate = $termination->termination_date;
            $remainAmt = totalContractRentCountCalculation($contractId,$terminationDate);


      // Minus from Rent receipt
      $osAmount = $remainAmt - $sumOfReceipt;

     return $osAmount;

  }
public function outstandingOsAmount($contractId){

  $sumOfReceipt   = ReceiptsGeneration::where('receipts_generation_type',0)
            ->where('tenant_contract_id',$contractId)
            ->where('receipts_generation_approval_status',3)
            ->sum('receipts_generation_amt');
            $tenantContract = TenantContract::where('id',$contractId)->first();
            $rentAmount = $tenantContract->tenant_contract_duration * $tenantContract->tenant_contract_rent;
            $osAmount = $rentAmount - $sumOfReceipt;
            return $osAmount;

}


}
