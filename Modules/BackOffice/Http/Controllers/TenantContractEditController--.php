<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Modules\BackOffice\Entities\TenantContractEdit;
use Modules\Sales\Entities\LandlordContract;
use Modules\BackOffice\Entities\TenantContractRevokeTemp;
use Modules\BackOffice\Entities\TenantContractRevokeNote;	
use Modules\BackOffice\Entities\TenantContractComment;  
use Modules\BackOffice\Entities\DiscussionForums;  
use Modules\BackOffice\Entities\DiscussionCategory;  
use Modules\BackOffice\Entities\ViewTenantContract;  
use Modules\Masters\Entities\VacantVacancyLoss;

use Modules\Masters\Entities\Bank;
use Modules\Masters\Entities\Employee;
use Modules\Masters\Entities\Legal;
use Modules\Sales\Entities\Tenant;
use Modules\Sales\Entities\TenantContract;
use Modules\Sales\Entities\TenantDocument;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Sales\Entities\Sales;
use Modules\Sales\Entities\SalesNote;
use Modules\Sales\Entities\SalesActivity;
use Modules\Sales\Entities\SalesUsers;
use Spatie\Permission\Models\Role;
use App\User;
use DB;
use Illuminate\Support\Facades\Mail;
use Modules\Masters\Events\LegalApprove;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Nationality;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Occupant;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\TenantType;
use Modules\Masters\Entities\TenantDocs;
use Modules\Sales\Http\Controllers\TenantContractController as Contract ;
use Modules\General\Http\Controllers\GeneralController as General ;
use Modules\Sales\Events\RevokeCreate;
use Modules\Sales\Events\RevokeEnquiry;
use Modules\Masters\Emails\LegalEmail;
use Dynamics;
use App\Setting;

class TenantContractEditController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function __construct()
    {
      $this->middleware('auth');    
    //  $this->middleware('permission:tenant_contract_direct_list', ['only' => ['index','show']]);   
      $this->middleware('permission:edit_tenant_contract_direct', ['only' => ['edit','update']]);
      $this->middleware('permission:add_tenant_contract_direct', ['only' => ['create','store']]);   
      $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
        /*
        $this->middleware('permission:add_complaint_reason', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_complaint_reason', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_complaint_reason', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_complaint_reason', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_complaint_reason', ['only' => ['index','show']]);    
        */
      }
      public function index(Request $request)
      {

     $is_tenant_contract = true; // TO identify in view load from tenant-contract index method
     
     
     $tenantContracts = ViewTenantContract::tenantContract()->areFilter()
		->directIndirect($request)
		->unregisteredContracts($request)
		->filter($request)
		->expiringContracts($request)
		->gracePeriod($request)
		->underPenalty($request)
		->tenantVacatting($request)
		->expiredContracts($request) 
		->sortable()->paginate($this->noOfRecord);
                               
    // dd($tenantContracts);
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

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      $tenantContractLatest = TenantContract::orderBy('id', 'desc')->first();
     // $prefix  = prefixData('tenant_agreement_prefix')->configuration_value;
     // if(!empty($tenantContractLatest))
     //   $contractCode = $prefix.str_pad($tenantContractLatest->id+1,4,'0',STR_PAD_LEFT);
     // else
     //   $contractCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
       $year    = prefixData('tenant_agreement_prefix')->configuration_year;
          $isYearCorrect = (date('y') == $year)?true:false;

      $generateCode = $this->tenantContractCode();
      $contractCode = $generateCode['code'];
      $stage = 107;
      $tenants = Tenant::get();
      $buildings = Building::where('building_status',1)->get();
      $units = Unit::where('unit_status',1)->get(); 
      $nationalities = Nationality::get();
      $banks = Bank::get();
      $locations = Location::get(); 
      $occupants = Occupant::get();

      $employeeList     = Employee::whereHas('user',function ($query){
        $query->role(['sales_person','sales_coordinator']);
      })->orderBy('id', 'DESC')->get();
      // MB to Kb -> 1 * 1000000
      $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
      return view('backoffice::TenantContract.tenant_contract_creation',compact('tenants','buildings','units','nationalities','stage','contractCode','banks','locations','occupants','employeeList','upload_size','isYearCorrect'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {//dd($request->all());
      $this->validate($request, [                    
        'tenant_contract_rent'   => 'required',
        'tenant_contract_start_date'   => 'required|date',
        'tenant_contract_effective_date'   => 'required|date',
        'tenant_contract_valid_to_date'   => 'required|date',
        'tenant_contract_payment_type' => 'required', 
        'tenant_contract_registered_in'=>'required',
        ]);
        /*
        *
        * Sales Enquiry & Sales & Sales Users Add 
        *
        */
        $tenantId = $request['tenant_id']; 
        $tenantDetails = Tenant::where('id','=',$tenantId)->first();//dd($tenantDetails);
        $processFlow = 107;
        $salesLatest = SalesEnquiry::orderBy('id','DESC')->where('sales_type',1)->where('sales_enquiry_direct_contract',2)->first();
        $prefix       = prefixData('tenant_enquiry_no_prefix')->configuration_value;
        if(!empty($salesLatest)){
          $nextCode 	= 'DTTENQ'.str_pad($salesLatest->enquiry_index+1,4,'0',STR_PAD_LEFT);
          $index 		= $salesLatest->enquiry_index+1;
        }
        else{
          $nextCode 	= 'DTTENQ'.str_pad(1,4,'0',STR_PAD_LEFT);    
          $index 		= 1;
        }

        /**************************Sales Enquiry *******************************/
        $salesEnquiry = new SalesEnquiry;
        $salesEnquiry->sales_enquiry_no = $nextCode;
        $salesEnquiry->sales_type = 1; 
        $salesEnquiry->sales_mobile_no = isset($tenantDetails->tenant_contact_no)?$tenantDetails->tenant_contact_no:$request['occupant_primary_contact_no'];
        $salesEnquiry->sales_enquiry_name = $request['tenant_name'];
        $salesEnquiry->sales_email = $request['tenant_contact_email'];   
        $salesEnquiry->sales_move_in_date = date("Y").'-'.date('m').'-1';
        $salesEnquiry->work_flow_processes_code = $processFlow; 
        $salesEnquiry->created_by = \Auth::user()->id; 
        $salesEnquiry->enquiry_index = $index;
        $salesEnquiry->sales_enquiry_direct_contract = 2; // Direct Contract
        $salesEnquiry->save();
        
        $sales =   Sales::create([
          'sales_enquiry_id' =>$salesEnquiry->id,
          'sales_type' => $salesEnquiry->sales_type,
          'work_flow_processes_code' => $processFlow,
          'created_by' => \Auth::user()->id,
          ]);
        $general =  new General;
        $processAssign = $general->roleUsersFromProcess($processFlow,$location_id=null,$pricerange_id=null,$tenant_status=null);
        if($processAssign == false){

          $res =    $general->workFlowProcess($processFlow);

          $sales->salesUsers()->attach($res->default_role, ['user_id' => $res->default_user_id]);
          $sales->save();

        }else{

         foreach($processAssign->assign as $val){
           $user_id = $val->user_id;

           if(empty($val->user_id))
             $user_id = null;

           $sales->salesUsers()->attach($val->role_id, ['user_id' => $user_id]);
           $sales->salesUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
           $sales->save();      
           
         } 
       }
       /***********************Contract *********************************/

       $contract =  new Contract;
       $tenantData = $contract->getTenantData();
       
       Tenant::where('id','=',$tenantId)->update(['tenant_name'=>$request['tenant_name']]);
       
       if(!empty($request['occupant_id'])){
        Occupant::where('id','=',$request['occupant_id'])->update([
          'occupant_name' => $request['occupant_name'],
          'occupant_primary_contact_no' =>$request['occupant_primary_contact_no'],
          'occupant_email' => $request['occupant_email'],
          'created_by' => \Auth::user()->id,
          ]);
        

      }else if($request['occupant_name']){
       $occupant_id = Occupant::create([
        'occupant_name' => $request['occupant_name'],
        'occupant_primary_contact_no' =>$request['occupant_primary_contact_no'],
        'occupant_email' => $request['occupant_email'],
        'created_by' => \Auth::user()->id,
        ]);
       $request['occupant_id'] = $occupant_id->id;
     }
     $tenantContractLatest = TenantContract::orderBy('id', 'desc')->first();
    // $prefixAgree  = prefixData('tenant_agreement_prefix')->configuration_value;
     //if(!empty($tenantContractLatest))
      //$contractCode = $prefixAgree.str_pad($tenantContractLatest->id+1,4,'0',STR_PAD_LEFT);
   // else
     // $contractCode = $prefixAgree.str_pad(1,4,'0',STR_PAD_LEFT);
    $generateCode = $this->tenantContractCode();
    $contractCode = $generateCode['code'];
    $yearToMonth = ($request['yeartxt'] > 0)? $request['yeartxt']*12:0;
    $durationInMonth = $yearToMonth + $request['monthtxt'];

    if($request['pdc_check'] == 2){
      $partial_comment = $request['partial_comment'];
    }else{
      $partial_comment = '';
    }

    
    $data = $contract->getContractData();
    $data['tenant_contract_no'] =  $contractCode ;
    $data['sale_enquiry_id'] = $salesEnquiry->id;
    $data['tenant_id'] = $tenantId;
        //$data['work_flow_processes_code'] = $processFlow;
    $data['occupant_id'] = $request['occupant_id'];
    $data['unit_usage'] = $request['unit_usage'];
    $data['tenant_contract_duration'] = $durationInMonth;
    $data['tenant_marketing_executive'] =$request['tenant_marketing_executive'];		
    $data['tenant_contract_duration_countdown'] = $request['yeartxt'].'-'.$request['monthtxt'].'-'.$request['daytxt'];		
    $data['tenant_contract_value'] = replaceCommaWithDot($request['tenant_contract_value']);		
    $data['tenant_contract_vacant_since'] = $request['tenant_contract_vacant_since'];
    $data['tenant_contract_rent_paid_prev_tenant'] = replaceCommaWithDot($request['tenant_contract_rent_paid_prev_tenant']);
    $data['deposit_check'] = $request['deposit_check'];		
    $data['pdc_check'] = $request['pdc_check'];		
    $data['tenant_contract_is_reg_municipality'] = $request['tenant_contract_is_reg_municipality'];
    $data['tennat_contract_direct_indirect_status'] = 1;
    $data['partial_comment'] = $partial_comment;
    $data['created_by'] = \Auth::user()->id;
	$data['tenant_contract_guarantee_cheque_details'] = $request['tenant_contract_guarantee_cheque_details'];
    $tenantContract = TenantContract::create($data);
    $documents = $request->file('tenant_document_file_name');
    if(!empty($documents)) {
      foreach($documents as $document){
        $uniqueFileName = $document->getClientOriginalName();
        $img_path =    Storage::putFile('public/Document', $document);
        $tenantDocument =  TenantDocument::create([
          'tenant_documents_name' =>  $uniqueFileName,
          'tenant_documents_file_name' => $img_path,
          'tenant_documents_status' => 2,
          'tenant_contract_id'=>$tenantContract->id,
          'created_by' => \Auth::user()->id,
          ]);
      }
    }
	$tenantContract->Unit->update(['unit_vaccant_status'=>'1','unit_status'=>0]);
	Setting::where('configuration_settings','tenant_agreement_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);


    session()->flash('success', 'Tenant Contract Created Successfully');
    return redirect()->route('tenant-contract.index');
  }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    { 
      $tenantContract = TenantContract::where('id',$id)->first();
      $contract       = TenantContract::where('unit_id',$tenantContract->unit_id)
      ->where('work_flow_processes_code',108)
      ->where('tenant_contract_status',0)
      ->orderBy('id', 'desc')->first();
      
      if(isset($contract->tenant_contract_valid_to_date) || isset($contract->tenant_contract_rent))     {
        $vaccant_date               =   date('Y-m-d', strtotime($contract->tenant_contract_valid_to_date.' +1 day'));
        $last_rent                  =   $contract->tenant_contract_rent;
        $tenantContract->vaccant_date   =   $vaccant_date;
        $tenantContract->last_rent      =   $last_rent;
      }
      $revokeNote 	= TenantContractRevokeNote::where('tenant_contracts_id',$id)->get();
	   $salesNotes = Sales::with('salesNotesInfo')->where('sales_enquiry_id',$tenantContract->sale_enquiry_id)->get();
    
    $notesArray = array();

    $k =0;
    foreach($salesNotes as $key=>$note_sale){
      foreach($note_sale->salesNotesInfo as $key=>$note){
       if(isset($note->sales_notes_note)){
       $notesArray[$k]['employee_name'] = $note->createdBy->employee->employee_name;
       $notesArray[$k]['sales_notes']  = $note->sales_notes_note;
       $notesArray[$k]['created_at']   = $note->created_at->format('d/m/Y h:m A');
       $notesArray[$k]['stage']       = $note_sale->workFlowProcess->work_flow_processes_name;
       $k++;
       } 
      }
    }
      $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
      $today = strtotime(date('Y-m-d'));

      $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds 




     readNotification('Modules\Sales\Notifications\EnquiryNotification',$tenantContract->sale_enquiry_id);	
     clearNotification('Modules\Sales\Notifications\EnquiryNotification',$tenantContract->sale_enquiry_id);     
     
     return view('backoffice::TenantContract.view',compact('tenantContract','revokeNote','remainingDays','notesArray'));
   }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(TenantContract $tenantContract)
    {
	  $year    = prefixData('tenant_agreement_prefix')->configuration_year;
      $isYearCorrect = (date('y') == $year)?true:false;
      $stage = 107;
      $tenants = Tenant::get();
      $buildings = Building::where('building_status',1)->get();
		$units = Unit::where('unit_status',1)->where('unit_vaccant_status',0)->get();
      $nationalities = Nationality::get();
      $banks = Bank::get();
      $locations = Location::get(); 
      $occupants = Occupant::get();
	  $contractCode = null;
      $rent = $this->contractRentCountCalculation($tenantContract->tenant_contract_start_date,$tenantContract->tenant_contract_valid_to_date, $tenantContract->tenant_contract_rent);
		// Valid Start date with Landlord contract valid from and Valid To
      $landlordContractInfo = LandlordContract::where('building_id',$tenantContract->building_id)->orderBy('id','desc')->first();
      
      if(isset($landlordContractInfo->landlord_contract_valid_from_date))
        $fromContract = $landlordContractInfo->landlord_contract_valid_from_date->format('Y-m-d');
      else
        $fromContract   = null;

      $pastTwoMonthDt = date('Y-m-d', strtotime("-3 month", strtotime(date('Y-m-d'))));
      
      if($fromContract < $pastTwoMonthDt){
        $fromContract = $pastTwoMonthDt;
      }
      if(isset($landlordContractInfo->landlord_contract_valid_to_date))
       $toContract   =  $landlordContractInfo->landlord_contract_valid_to_date->format('Y-m-d');
     else
       $toContract   = null;
     // MB to Kb -> 1 * 1000000
     $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
     $duration = $this->dateDuration($tenantContract->tenant_contract_start_date,$tenantContract->tenant_contract_valid_to_date);
     $employeeList     = Employee::whereHas('user',function ($query){
        $query->role(['sales_person','sales_coordinator']);
      })->orderBy('id', 'DESC')->get();
     // dd($tenantContract);
     return view('backoffice::TenantContract.tenant_contract_creation',compact('tenants','buildings','units','nationalities','stage','contractCode','banks','locations','occupants','tenantContract','employeeList','rent','duration','fromContract','toContract','upload_size','landlordContractInfo','isYearCorrect'));
   }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,TenantContractEdit $tenantContract)
    {   
      $this->validate($request, [                    
        'tenant_contract_rent'   => 'required',
        'tenant_contract_start_date'   => 'required|date',
        'tenant_contract_effective_date'   => 'required|date',
        'tenant_contract_valid_to_date'   => 'required|date',
        'tenant_contract_payment_type' => 'required', 
        'tenant_contract_registered_in'=>'required',

        ]);
      $tenantContractData = TenantContract::where('id','=',$tenantContract->id)->first();
      $tenantContractData->Unit->update(['unit_vaccant_status'=>'0','unit_status'=>1]);

      $contract =  new Contract;
        //$tenantData = $contract->getTenantData();
      $tenantId = $request['tenant_id'];
        //Tenant::where('id','=',$tenantId)->update($tenantData);

      if(!empty($request['occupant_id'])){
        Occupant::where('id','=',$request['occupant_id'])->update([
          'occupant_name' => $request['occupant_name'],
          'occupant_primary_contact_no' =>$request['occupant_primary_contact_no'],
          'occupant_email' => $request['occupant_email'],
          'updated_by' => \Auth::user()->id,
          ]);
            //$occupant_id = $request['occupant_id'];
      }else if($request['occupant_name']){
       $occupant_id = Occupant::create([
        'occupant_name' => $request['occupant_name'],
        'occupant_primary_contact_no' =>$request['occupant_primary_contact_no'],
        'occupant_email' => $request['occupant_email'],
        'created_by' => \Auth::user()->id,
        ]);
       $request['occupant_id'] = $occupant_id;
     }
     
     $yearToMonth = ($request['yeartxt'] > 0)? $request['yeartxt']*12:0;
     $durationInMonth = $yearToMonth + $request['monthtxt'];

     if($request['pdc_check'] == 2){
      $partial_comment = $request['partial_comment'];
    }else{
      $partial_comment = '';
    }

    $data = $contract->getContractData();
    $data['tenant_id'] = $tenantId;
    $data['occupant_id'] = $request['occupant_id'];
    $data['unit_usage'] = $request['unit_usage'];
    $data['status'] = $request['Status'];
    $data['tenant_contract_duration'] 	= 	$durationInMonth;
    $data['tenant_marketing_executive'] =	$request['tenant_marketing_executive'];
    $data['tenant_contract_duration_countdown'] = $request['yeartxt'].'-'.$request['monthtxt'].'-'.$request['daytxt'];
    $data['tenant_contract_value'] = replaceCommaWithDot($request['tenant_contract_value']);
    $data['deposit_check'] = $request['deposit_check'];
    $data['pdc_check'] = $request['pdc_check'];
    $data['tenant_contract_is_reg_municipality'] = $request['tenant_contract_is_reg_municipality'];
    $data['status'] = $request['Status'];
    $data['partial_comment'] = $partial_comment;
    $data['updated_by'] = \Auth::user()->id;
	$data['tenant_contract_guarantee_cheque_details'] = $request['tenant_contract_guarantee_cheque_details'];

    $tenantContract->update($data);
	//occupied
    $tenantContract->Unit->update(['unit_vaccant_status'=>'1','unit_status'=>0]);
    $documents = $request->file('tenant_document_file_name');
    if(!empty($documents)) {
      foreach($documents as $document){
        $uniqueFileName = $document->getClientOriginalName();
        $img_path =    Storage::putFile('public/Document', $document);
        $tenantDocument =  TenantDocument::create([
          'tenant_documents_name' =>  $uniqueFileName,
          'tenant_documents_file_name' => $img_path,
          'tenant_documents_status' => 2,
          'tenant_contract_id'=>$tenantContract->id,
          'created_by' => \Auth::user()->id,
          ]);
      }
    }
    session()->flash('success', 'Tenant Contract Updated Successfully');
    return redirect()->route('tenant-contract.index');
  }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
      $TenantDocument = TenantDocument::find($id);
      
      Storage::delete($TenantDocument->tenant_documents_file_name);
      $TenantDocument->delete();
    }
     /**
     * Pdc List.
     * @return Response
     */
     public function tenantPdcView($id){


      $tenantContract = TenantContract:: where('id',$id)->first();
        //dd($tenantContract);
      return view('backoffice::TenantContract.pdc_acceptanace', compact('tenantContract'));
    }

     /**
     * Add Pdc.
     * @return Response
     */
     public function addTenantPdf($id){
      $bankList   =   Bank::all();
      
       // $tenantContract = TenantContract:: where('id',$id)->first();
        //dd($tenantContract);
      return view('backoffice::TenantContract.add_tenant_pdc', compact(['bankList']));
    }
    /**
    * Get Unit Type From Unit
    * @return Response
    */
    public function getUnitTypeByUnit(Request $request){

      $contract           =   array();
      if(!empty($request->input('id'))){
        $id                 =   $request->input('id'); 
        
        $contract           =   TenantContract::where('unit_id',$id)->where('work_flow_processes_code',108)->where('tenant_contract_status',0)->latest()->first();
        $Unit               =   Unit::with('unit')->where('id',$id)->first();
        
        if(isset($contract->tenant_contract_valid_to_date) || isset($contract->tenant_contract_rent)){
          $vaccant_date       =   date('Y-m-d', strtotime($contract->tenant_contract_valid_to_date.' +1 day'));
          $last_rent          =   $contract->tenant_contract_rent;
          $Unit->unit->vaccant_date =   $vaccant_date;
          $Unit->unit->last_rent    =   $last_rent;
        }
        return json_encode($Unit->unit);
      }
      else
        return null;
      
      
    }
    /**
    * Get Unit Type From Unit
    * @return Response
    */
    public function getOccupant(Request $request){
      $id = $request->input('id'); 
      $Occupant   =   Occupant::where('id',$id)->first();
      return json_encode($Occupant);
    }
    /*
    *
    *
    * Tenant Contract Revoke 
    *
    *
    */
    public function tenantContractRevoke($id){

      $tenantContract = TenantContract::where('id',$id)->first();
      $stage = 107;
	  $contractCode = null;
      $tenants = Tenant::get();
      $buildings = Building::where('building_status',1)->get();
      $units = Unit::where('unit_status',1)->get();
      $nationalities = Nationality::get();
      $banks = Bank::get();
      $locations = Location::get(); 
      $occupants = Occupant::get();
      $rent = $this->contractRentCountCalculation($tenantContract->tenant_contract_start_date,$tenantContract->tenant_contract_valid_to_date, $tenantContract->tenant_contract_rent);
      
      $duration = $this->dateDuration($tenantContract->tenant_contract_start_date,$tenantContract->tenant_contract_valid_to_date);
      $employeeList     = Employee::whereHas('user',function ($query){
        $query->role('sales_person');
      })->orderBy('id', 'DESC')->get();
      $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
      return view('backoffice::TenantContract.tenant_contract_revoke',compact('tenants','buildings','units','nationalities','stage','contractCode','banks','locations','occupants','tenantContract','employeeList','rent','duration','upload_size'));
    }
    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function storeTenantContractRevoke(Request $request,TenantContract $tenantContract){
//dd($request->all());
      $this->validate($request, [                    
        'tenant_contract_payment_type' => 'required', 
        'tenant_contract_muncipality_agr_no'=>'required',
        'tenant_contract_registered_in'=>'required',

        ]);
      $tenantData = TenantContract::where('id',$tenantContract->id)->first();
      if($request['pdc_check'] == 2){
		  $partial_comment = $request['partial_comment'];
		}else{
		  $partial_comment = '';
		}
      TenantContractRevokeTemp::create([
       'tenant_contracts_id' => $tenantContract->id,		
       'tenant_id' => $tenantContract->tenant_id,
       'occupant_id' => $tenantContract->occupant_id,
       'tenant_contract_electric_water' => $tenantContract->tenant_contract_electric_water,
       
       'tenant_contract_muncipality_agr_no' => $tenantContract->tenant_contract_muncipality_agr_no,
       'tenant_contract_registered_date' => $tenantContract->tenant_contract_registered_date,
       'tenant_contract_registered_in' => $tenantContract->tenant_contract_registered_in,
       'tenant_contract_payment_type' => $tenantContract->tenant_contract_payment_type,
       'tenant_contract_note' => $tenantContract->tenant_contract_note,
       'tenant_contract_deposit_amt' => replaceCommaWithDot($tenantContract->tenant_contract_deposit_amt),
       'tenant_contract_guarantee_cheque_details' => $tenantContract->tenant_contract_guarantee_cheque_details,
       'tenant_contract_receipt_no' => $tenantContract->tenant_contract_receipt_no,
       'tenant_contract_receipt_date' => $tenantContract->tenant_contract_receipt_date,
       'tenant_contract_receipt_amt' => replaceCommaWithDot($tenantContract->tenant_contract_receipt_amt),
	   'partial_comment'=>$tenantContract->partial_comment,
       'created_by' => \Auth::user()->id
       
       ]);
      if(isset($request['occupant_name']) && !isset($request['occupant_id']) ){
		   // Occupant Insert 
       $occupantId =  Occupant::create([
         'occupant_name' => $request['occupant_name'],
         'occupant_primary_contact_no' => $request['occupant_primary_contact_no'],
         'occupant_email' => $request['occupant_email'],
         'updated_by' => \Auth::user()->id,
         ]);
       
       $request['occupant_id'] = $occupantId->id;
     }
     
		$data = array();
		$data['created_at'] = $request['tenant_contract_date'];
		$data['tenant_id'] = $request['tenant_id'];
		$data['occupant_id'] = isset($occupantId->id)?$occupantId->id:$request['occupant_id'];
		$data['tenant_contract_electric_water'] = $request['tenant_contract_electric_water'];
		$data['tenant_contract_payment_type'] = $request['tenant_contract_payment_type'];
		$data['tenant_contract_note'] = $request['tenant_contract_note'];
		$data['tenant_contract_deposit_amt'] = replaceCommaWithDot($request['tenant_contract_deposit_amt']);
		$data['tenant_contract_muncipality_agr_no'] = $request['tenant_contract_muncipality_agr_no'];
    $data['tenant_contract_registered_date'] = $request['tenant_contract_registered_date'];
		$data['tenant_contract_registered_in'] = $request['tenant_contract_registered_in'];
    $data['tenant_contract_receipt_no'] = $request['tenant_contract_receipt_no']; 
		$data['tenant_contract_receipt_date'] = $request['tenant_contract_receipt_date'];
		$data['tenant_contract_receipt_amt'] = replaceCommaWithDot($request['tenant_contract_receipt_amt']);
		$data['pdc_check'] = $request['pdc_check'];
		$data['deposit_check'] = $request['deposit_check'];
		$data['partial_comment'] = $request['partial_comment'];
		$data['tenant_contract_is_reg_municipality'] = $request['tenant_contract_is_reg_municipality'];
		$data['tennat_contract_direct_indirect_status'] = 1;
		$data['tenant_contract_is_revoke'] = 1; // Pending status
		$data['updated_by'] = \Auth::user()->id;
		$tenantContract->update($data);
		$processFlow = $request['workflow_id'];
      
      $documents = $request->file('tenant_document_file_name');
      if(!empty($documents)) {
        foreach($documents as $document){
          $uniqueFileName = $document->getClientOriginalName();
          $img_path =    Storage::putFile('public/Document', $document);
          $tenantDocument =  TenantDocument::create([
            'tenant_documents_name' =>  $uniqueFileName,
            'tenant_documents_file_name' => $img_path,
            'tenant_documents_status' => 2,
            'tenant_contract_id'=>$tenantContract->id,
            'created_by' => \Auth::user()->id,
            'temp' => 2
            ]);
        }
      }

            //Notification starts
      if($tenantData->status == 5){
        $legal = Legal::where('tenant_contract_id',$tenantData->id)->first();
        $legalUsers = User::role(['are','backoffice_manager','legal_advisor'])->get(); 
        $legalUsers = array_flatten($legalUsers);

        $legal->textContent = "Tenant Contract Revoked";
        foreach($legalUsers as $legalUser){
          $mobile = $legalUser->employee->employee_contact_no ?? $legalUser->employee->employee_secondary_no;

          $msg = "Tenant Contract Revoked !";
          $params = 'optional data';
          if(!empty($legalUser->email)){
      Mail::to($legalUser->email)->send(new LegalEmail($legal,$legalUser)); //Email Notification
    }else{
       sendSms($mobile,$msg,$params); //SMS Notification
     }

   }
 }
	 $userLists = User::role(['backoffice_manager','backoffice_executive'])->where('id','!=',\Auth::user()->id)->get(); 

    $userLists = array_flatten($userLists);
    $msg = "Tenant Contract Revoke -".$tenantContract->tenant_contract_no. " By ".\Auth::user()->employee->employee_name;
    $tenantData->text = $msg;
    $tenantData->href = url('tenantContractApprovedRevoke/'.$tenantContract->sale_enquiry_id.'/108/tenantRevokeProcess/');
          
    event(new RevokeCreate($tenantData,$userLists)); 
    //Notification ends
     session()->flash('success', 'Tenant Contract Revoked Successfully');
     return redirect()->route('tenant-contract.index');
    }
    /*
     * 
     * Pending Revoke Tenant contract
     * 
     * 
     */ 
    public function tenantContractPendingRevoke(Request $request){ 


      $pendingPage 	= 	true;
      $title 			=  "Tenant Direct/Revoke Contract Pending List";
      $breadcrumb 	= "tenantContractPendingRevoke";   
      
      
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

      $request->flash();
      
      $tenantContracts = ViewTenantContract::where('is_direct_contract_pending','=',2)->filter($request)->sortable()->paginate($this->noOfRecord);
      
      $fields = [
      'tenant_contract_no' => 'Agreement No',                 
      'building_name'      => 'Building Name',
      ];

       //$request->flash();   
      $route =  \Request::route()->getName();
      $serach_url  = $route; 

      if(isset($request->ajax))
       return view('backoffice::TenantContract.contract_list_ajax',compact('tenantContracts','fields','request','pendingPage','title','route','breadcrumb'));			
     

     
     return view('backoffice::TenantContract.contract_list',compact('tenantContracts','fields','request','pendingPage','pendingPage','title','route','breadcrumb','serach_url','operations'));
   }
	/*
     * 
     * Pending Revoke Tenant contract
     * 
     * 
     */ 
  public function tenantContractApprovedRevoke(Request $request){ 

    $pendingPage = false;
    $title      =  "Tenant Direct/Revoke Contract Approve";
    $breadcrumb = "tenantContractApprovedRevoke";
            
    $tenantContracts = ViewTenantContract::where('is_direct_contract_pending','=',2)->orWhere(function($query) {
      $query->where('tenant_contract_is_revoke','=', 1);
    })->filter($request)->sortable()->paginate($this->noOfRecord);
    
    // dd($tenantContracts);



    $fields = [
    'tenant_contract_no' => 'Agreement No',                 
    'building__building_name'      => 'Building Name',
    ];
    $request->flash();   
    $route =  \Request::route()->getName(); 
    $serach_url  = $route; 

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


    if(isset($request->ajax)){ 			

     return view('backoffice::TenantContract.contract_list_ajax',compact('tenantContracts','fields','request','title','route','pendingPage','breadcrumb'));	
     
   }

   
   return view('backoffice::TenantContract.contract_list',compact('tenantContracts','fields','request','pendingPage','title','route','breadcrumb','serach_url','operations'));
 }
	/*
    *
    * Revoke Pending and Approval
    *
    */
  public function revokeProcess($view,$id,$stage) {

    readNotification('Modules\Sales\Notifications\EnquiryNotification',$id);	
    clearNotification('Modules\Sales\Notifications\EnquiryNotification',$id);          
    
    $actionBtn = False;
    /*$Onwer = '';
    $details = Sales::whereHas('salesEnquiry', function ($query) use($stage,$id) {
      $query->where('work_flow_processes_code', '=', $stage)
      ->with('workFlowProcess')
      ->where('sales_enquiry_id','=',$id);                      
    })->with('createdBy','salesUser')     
    ->where('sales_type', '=', 1)
    ->where('work_flow_processes_code', '=', $stage)
    ->first();
	*/
	$details = salesEnquiry::where('work_flow_processes_code', '=', $stage)
      ->with('workFlowProcess')
      ->where('id','=',$id)
      ->where('work_flow_processes_code', '=', $stage)
      ->first();
	  
    $allNotes = Sales::with('workFlowProcess')->where('sales_enquiry_id','=',$id)
    ->where('sales_type', '=', 1)->where('sales_notes', '!=', null)->get();
    
    $salesNotes = SalesNote::whereHas('sales', function ($query) use($id) {
      $query->where('sales_enquiry_id', '=', $id);                      
    })->get();
    $tenantContracts = TenantContract::where('sale_enquiry_id','=',$id)
    ->where('tenant_contract_status','=',0)->get();
    
    
    
    $salesActivities = SalesActivity::whereHas('sales', function ($query) use($id) {
      $query->where('sales_enquiry_id', '=', $id);                      
    })->where('sales_activities_status','<',3)
    ->get();
    $salesActivitieslatest = SalesActivity::whereHas('sales', function ($query) use($id) {
      $query->where('sales_enquiry_id', '=', $id);                      
    })->where('sales_activities_status','<',3)->orderBy('sales_activities_due_date', 'ASC')->latest()->first();

    $salesClosedActivities = SalesActivity::whereHas('sales', function ($query) use($id) {
      $query->where('sales_enquiry_id', '=', $id);                      
    })->where('sales_activities_status','=',3)
    ->get();
    $tenant = Tenant::whereHas('tenantContract', function ($query) use($id) {
      $query->where('sale_enquiry_id','=',$id);                      
    })->where('tenant_status','=',0)->first();    
    
    
       // Disable action buttons
    if($view === 'tenantContractApprovedRevoke'){
     $actionBtn = True;
     $title = "Direct/Revoke Contract Approve";
   }
   else{
     $title = "Direct/Revoke Contract Pending";
   }
   
   if($details){    
    switch ($stage) {
      case "108":
      /* Active Contracts */     
      $tenantContracts = TenantContract::where('sale_enquiry_id','=',$id)
      ->where('tenant_contract_status','=',1)->first();
      $direct_indirect_status = $tenantContracts->tennat_contract_direct_indirect_status;            
      $revokeNote 	= TenantContractRevokeNote::where('tenant_contracts_id',$tenantContracts->id)->get();   
	  clearNotification('Modules\Sales\Notifications\TenantContractNotification',$tenantContracts->id);	  
      $routes = $view;       
      $ar_status = false;       	
      return view('backoffice::TenantContract.tenant_revoke_approval',compact('details','ar_status','title','salesNotes','salesActivities','salesClosedActivities','tenantContracts','tenant','routes','allNotes','actionBtn','direct_indirect_status','revokeNote'));
      break;
      case "107":
      /* Active Contracts */     
      $tenantContracts = TenantContract::where('sale_enquiry_id','=',$id)
      ->where('tennat_contract_direct_indirect_status','=',1)->first();
      $direct_indirect_status = $tenantContracts->tennat_contract_direct_indirect_status;  
      $revokeNote 	= TenantContractRevokeNote::where('tenant_contracts_id',$tenantContracts->id)->get();          
      
      $routes = $view;   
      $ar_status = false;
      return view('backoffice::TenantContract.tenant_revoke_approval',compact('details','ar_status','title','salesNotes','salesActivities','salesClosedActivities','tenantContracts','tenant','routes','allNotes','actionBtn','direct_indirect_status','revokeNote'));
      break; 
      
    }
  }else {
    return back();
  }
  
  
}

    /*
    *
    * Accept / Reject
    *
    *
    */
    public function noteModal(Request $request) {

      $tenant_contract_id 	= $request['tenant_contract_id'];
      $enquiryid 			= $request['enquiryid'];
      $action_key 			= $request['action_key'];
      
      if($action_key == 'RJCT'){
      	
      	return view('backoffice::TenantContract.backoffice_revoke_reject_note_modal',compact('tenant_contract_id','enquiryid','action_key'));
      	
      }else {

      	return view('backoffice::TenantContract.backoffice_revoke_accept_note_modal',compact('tenant_contract_id','enquiryid','action_key'));
      }
      

    }
    public function approvalAcceptReject(Request $request) {


      $action_key 			= $request['action_key']; 
      $tenant_contract_id		= $request['tenant_contract_id']; 
      $sales_lead_note_name 	= $request['sales_lead_note_name'];
      $enquiryid 				= $request['enquiryid'];
      $sendNotifyUserId	 = null;
      if(isset($action_key)){

        switch($action_key){

         case 'RJCT' :
         if(isset($sales_lead_note_name)){
           TenantContractRevokeNote::create([
            'tenant_contracts_id' => $tenant_contract_id,
            'tenant_contract_revoke_note_desc' => $sales_lead_note_name,
            'tenant_contract_revoke_note_status' => 1,
            'created_by' => \Auth::user()->id,
            ]);
         }	
         
        $tenantDataBeforeUpdate = TenantContract::where('id',$tenant_contract_id)->first();
        $tenantData = TenantContractRevokeTemp::where('tenant_contracts_id',$tenant_contract_id)->first();
         
        $salesEnquiry = SalesEnquiry::where('id',$enquiryid)->first();
        if(isset($tenantData->tenant_id)){
				$data = array();
				$data['tenant_id'] = $tenantData->tenant_id;
				$data['occupant_id'] = $tenantData->occupant_id;
				$data['tenant_contract_electric_water'] = $tenantData->tenant_contract_electric_water;
				$data['tenant_contract_muncipality_agr_no'] = $tenantData->tenant_contract_muncipality_agr_no;
				$data['tenant_contract_registered_in'] = $tenantData->tenant_contract_registered_in;
				$data['tenant_contract_payment_type'] = $tenantData->tenant_contract_payment_type;
				$data['tenant_contract_note'] = $tenantData->tenant_contract_note; 
				$data['tenant_contract_deposit_amt'] = replaceCommaWithDot($tenantData->tenant_contract_deposit_amt);
				$data['tenant_contract_guarantee_cheque_details'] = $tenantData->tenant_contract_guarantee_cheque_details;
				$data['tenant_contract_receipt_no'] = $tenantData->tenant_contract_receipt_no;
				$data['tenant_contract_receipt_date'] = $tenantData->tenant_contract_receipt_date;
				$data['tenant_contract_receipt_amt'] = $tenantData->tenant_contract_receipt_amt;			
				$data['tenant_contract_is_revoke'] = 0; // Reject the revoke
				$data['partial_comment'] = $tenantData->partial_comment;	
				$data['updated_by'] = \Auth::user()->id;

				$sendNotifyUserId = $tenantData->created_by;

				TenantContract::where('id','=',$tenant_contract_id)->update($data);

				/***** Activity Log *****/
				activity("Revoke Reject")
				->performedOn($tenantDataBeforeUpdate)
				->causedBy(\Auth::user()->id)
				->withProperties($tenantDataBeforeUpdate)
				->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

				TenantContractRevokeTemp::where('tenant_contracts_id',$tenant_contract_id)->delete();
            }
            
            //Back to Normal
			TenantContract::where('id',$tenant_contract_id)->update(['is_direct_contract_pending'=>1,'work_flow_processes_code'=>null]);
            $backoffice_executive = User::role(['backoffice_executive'])->get();
            $usr = array_flatten($backoffice_executive);
            $msg = "Rejected";
            $salesEnquiry->text = "Direct Contract ".$tenantDataBeforeUpdate->tenant_contact_no." Rejected";
            $salesEnquiry->href = url('tenant-contract/'.$tenant_contract_id);
            
            event(new RevokeEnquiry($salesEnquiry,$usr)); 
            
            session()->flash('success', 'Revoke Is Rejected Successfully');
            return redirect()->route('tenantContractApprovedRevoke');
            
            break;
            
            case 'ACPT' :
            
            TenantContract::where('id',$tenant_contract_id)->update(['tenant_contract_is_revoke'=>2,
																	'is_direct_contract_pending'=>1]);
            $tenantData = TenantContract::where('id',$tenant_contract_id)->first();
			$tenantInfo  = Tenant::where('id',$tenantData->tenant_id)->first();
		
		/***** Activity Log *****/
            activity("Revoke Approved")
            ->performedOn($tenantData)
            ->causedBy(\Auth::user()->id)
            ->withProperties($tenantData)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
            
            session()->flash('success', 'Revoke Is Accepted Successfully');
            return redirect()->route('tenantContractApprovedRevoke');
            
            break;
          }
        }
        return redirect()->route('tenantContractApprovedRevoke');
      }
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

            $startDays = $explodeEndDtValue[2] - $explodeStartDtValue[2] + 1;
            $sumOfStartDays  = ($rent/$noOfDaysStartMonth) * $startDays;
            
          }
          else{

        //echo $explodeStartDtValue[2]; exit;
            if($explodeStartDtValue[2] < $noOfDaysStartMonth){

              $startDays = $noOfDaysStartMonth - $explodeStartDtValue[2] + 1;
              
              $sumOfStartDays  = ($rent/$noOfDaysStartMonth) * $startDays;
              
            }
            if($explodeEndDtValue[2] < $noOfDaysEnd){

              $endDays = $explodeEndDtValue[2];
              $sumOfEndDays  = ($rent/$noOfDaysEnd) * $endDays;
              
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
        return number_format((float)$sumOfMonthRent, 3, '.', '');

      }  
      public function dateDuration($date1, $date2){

        $date1 = date("Y-m-d", strtotime($date1));
        $date2 = date("Y-m-d", strtotime($date2));

        $date1 = new \DateTime($date1);
        $date2 = $date1->diff(new \DateTime($date2 ));

        return $date2->y.'-'.$date2->m.'-'.$date2->d;
      }
  /*
    *
    *Tenant contract chang status by Are
    *
    */
  public function tenantContractChangeStatus(Request $request){
    $tenant_contract_id = $request->tenant_contract_id;
    $tenantContractInfo = TenantContract::where('id',$tenant_contract_id)->first();
     // dd($tenant_contract_id);
    return view('backoffice::TenantContract.tenant_contract_comment_model',compact('tenant_contract_id','tenantContractInfo'));
  }
    /*
    *
    *Tenant contract chang status store by Are
    *
    */
    public function tenantContractChangeStatusStore(Request $request){
      $tenant_contract_id = $request->tenant_contract_id;
      $tenant_contract_status = $request->tenant_contract_status;
      $tenant_contract_comment = $request->tenant_contract_comment;
      
      $tenantContractComment = TenantContractComment::create([ 
       'tenant_contract_id' => $tenant_contract_id,
       'status' => $tenant_contract_status,
       'comment' => $tenant_contract_comment,
       'created_by' => \Auth::user()->id
       ]);

      TenantContract::where('id',$tenant_contract_id)->update(['status'=>$tenant_contract_status]);

      $tenantContract=TenantContract::where('id',$tenant_contract_id)->first();

      if($tenant_contract_status == 5){
       $legalCase=Legal::create([ 
        'building_id' => $tenantContract->building_id,
        'unit_id' => $tenantContract->unit_id,
        'tenant_id'=> $tenantContract->tenant_id,
        'tenant_contract_id' => $tenant_contract_id,
        'note' => $tenant_contract_comment,
        'work_flow_processes_code' => 801,
        'created_by' => \Auth::user()->id
        ]); 

			/* $legalCase->legalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
			$legalCase->save();*/

			$next_process_id=801;
			$general =  new General;
			$processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);
			if($processAssign != false) {

				foreach($processAssign->assign as $val){
          $legalCase->legalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
          $legalCase->save();
        }
        $legalCase->legalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
        $legalCase->save();

      }else{

        $previousProcess = $general->getPreviousOrder($next_process_id);
        if($previousProcess !=0){

          $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

          foreach($previousAssign->assign as $val){
            $legalCase->legalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
            $legalCase->save();
          }
          $legalCase->legalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
          $legalCase->save();

        }else{

          $workFlowProcess = $general->workFlowProcess($next_process_id);
          $legalCase->legalUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
          $legalCase->legalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]); 
          $legalCase->save();


        }
      }


      $plmusers = User::role(['backoffice_manager'])->get(); 
      $plmusers = array_flatten($plmusers); 
      $legalCase->href = url('plmsApproval/'.$legalCase->id);
      $legalCase->content = "Received";
      $legalCase->textContent = "Legal Case Received";


      event(new LegalApprove($legalCase,$plmusers));

      foreach($plmusers as $plmuser){
        $mobile = $plmuser->employee->employee_contact_no ?? $plmuser->employee->employee_secondary_no;

        $msg = "New Legal Case has been Received !";
        $params = 'optional data';
        if(!empty($plmuser->email)){
      Mail::to($plmuser->email)->send(new LegalEmail($legalCase,$plmuser)); //Email Notification
    }else{
       sendSms($mobile,$msg,$params); //SMS Notification
     }

   } 
 }

 session()->flash('success', 'Tenant Contract Status Changed Successfully');
 return redirect()->route('tenant-contract.index');
}
  /*
     * 
     * 
     * Tenant Direct Contract Send for Approval
     * 
     */
  public function tenantSendForApproval($contract_id,$redirectPage)
  {
    TenantContract::where('id',$contract_id)->update(['work_flow_processes_code'=>107, 'is_direct_contract_pending'=> 2]);
    if($redirectPage=='view')
      return redirect(url('tenant-contract/'.$contract_id));
    else
      return redirect()->route('tenant-contract.index');
  }


  /*
  *  DiscussionForum
  *
  */
  public function discussionForum(Request $request){

   $tenant_contract_id = $request->tenant_contract_id;
   $discussionForums =   DiscussionForums::where('tenant_contract_id',$tenant_contract_id)
   ->latest()
   ->get(); 
   $discussionCategories = DiscussionCategory::get();


   return view('backoffice::DiscussionForm.addform',compact('discussionCategories','discussionForums','tenant_contract_id'));


 }



 public function discussionForumStore(Request $request, TenantContract $tenant_contract){

  $tenant_contract->discussion()->create([
   'discussion_category_id' =>  $request->discussion_category_id,
   'discussion' => $request->discussion,
   'commented_by' =>  \Auth::id()
   ]);

  session()->flash('success', 'Discussion Saved  Successfully');
  return redirect()->route('tenant-contract.index');


}
public function tenantPopup(){

 $tenantTypes = TenantType::get();
 $locations = Location::get();
 $banks = Bank::get();
 $nationalities = Nationality::get();
 $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;

 return view('backoffice::TenantContract.tenantPopup', compact(['tenantTypes','locations','banks','nationalities','upload_size']));
}
public function tenantPopupAction(Request $request){
  $this->validate($request, [                  
    'tenant_name'   => 'required',
    'tenant_contact_no'   => 'required|unique:tenant',
/*            'location_id' => 'required' ,
'nationality'=>'required' ,*/
]);
  $tenantLatest = Tenant::latest()->first();
  $prefix  = prefixData('tenant_prefix')->configuration_value; 
  if(!empty($tenantLatest))
    $nextCode = $prefix.str_pad($tenantLatest->id+1,4,'0',STR_PAD_LEFT);
  else
    $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
  $tenant = Tenant::create([
    'tenant_name' => $request['tenant_name'],
    'tenant_code' => $nextCode,
    'resident_id' => $request['resident_id'],
    'tenant_type_id' => $request['tenant_type_id'],
    'tenant_contact_address' => $request['tenant_contact_address'],
    'tenant_secondary_address' => $request['tenant_secondary_address'],
    'tenant_pc' => $request['tenant_pc'],
    'location_id' => $request['location_id'],
    'tenant_contact_no' => $request['tenant_contact_no'],
    'tenant_contact_email' => $request['tenant_contact_email'],
    'tenant_fax_no' => $request['tenant_fax_no'],
    'tenant_acc_no' => $request['tenant_acc_no'],
    'tenant_contact_person' => $request['tenant_contact_person'],
    'bank_id' => $request['bank_id'],
    'gsm_no' => $request['gsm_no'],
    'passport_no' => $request['passport_no'],
    'com_reg_no' => $request['com_reg_no'],
    'tenant_company_name' => $request['tenant_company_name'],
    'nationalities_id' => $request['nationality'],

    'tenant_resident_exp_date' => $request['tenant_resident_exp_date'],
    'tenant_gender' => $request['tenant_gender'],
    'tenant_date_of_birth' => $request['tenant_date_of_birth'],
    'tenant_employer_name' => $request['tenant_employer_name'],
    'designation' => $request['designation'],
    'tenant_residence_tel' => $request['tenant_residence_tel'],
    'tenant_personal_email' => $request['tenant_personal_email'],
    'tenant_ice_name' => $request['tenant_ice_name'],
    'tenant_ice_contact_no' => $request['tenant_ice_contact_no'],
    'tenant_post_box' => $request['tenant_post_box'],

    'created_by' => \Auth::user()->id,
    ]);
  if(!empty($request->file('tenant_doc_path_name'))):
    $files = $request->file('tenant_doc_path_name');
  foreach($files as $key => $file):

    $uniqueFileName = $file->getClientOriginalName() ;
  $doc_path = Storage::putFile('public/TenantDocs',$request['tenant_doc_path_name'][$key]);

  $docs=TenantDocs::create(['tenant_id'=>$tenant->id,
    'tenant_doc_category'=>$request['tenant_doc_category'][$key],
    'tenant_doc_path_name'=>$doc_path,
    'tenant_doc_name'=>$uniqueFileName,
    'created_by' => \Auth::user()->id]);
  endforeach;
  endif;

  if($tenant->id){
    $res = array($tenant->id =>$nextCode,'tenant_name'=>$request->tenant_name);
    return json_encode($res);
  }else{
    return false;
  }
}
    /*
     *
     * Tenant Name
     * @return value
     */
 
   public function tenantNameAjaxCode(Request $request){

     $id = $request->input('id');
     $list = Tenant::where('id',$id)->first();
     return json_encode($list);

   }

   /*
  *
  * Direct Accept / Reject
  *
  *
  */
  public function noteDirectModal(Request $request) {

        $tenant_contract_id   = $request['tenant_contract_id'];
        $enquiryid      = $request['enquiryid'];
        $action_key       = $request['action_key'];
        
        if($action_key == 'RJCT'){
          
          return view('backoffice::TenantContract.backoffice_direct_reject_note_modal',compact('tenant_contract_id','enquiryid','action_key'));
          
        }else {

          return view('backoffice::TenantContract.backoffice_direct_accept_note_modal',compact('tenant_contract_id','enquiryid','action_key'));
        }
        

  }
  public function directContractApprovalAcceptReject(Request $request) {

        $action_key       = $request['action_key']; 
        $tenant_contract_id   = $request['tenant_contract_id']; 
        $sales_lead_note_name   = $request['sales_lead_note_name'];
        $enquiryid        = $request['enquiryid'];
        
        $sale_id = Sales::where('sales_enquiry_id','=',$enquiryid)->where('work_flow_processes_code','=',107)->first();  


        $contract  = TenantContract::where('id',$tenant_contract_id)->first();
        
        if(isset($action_key)){

          switch($action_key){

            case 'RJCT' :
			if(isset($sale_id->id)){
              SalesNote::create([
                'sales_id' => $sale_id->id,
                'user_id' => \Auth::user()->id,
                'sales_notes_note' => $sales_lead_note_name,
                'created_by' => \Auth::user()->id,
                ]); 
			}
              // Back to Normal Status
              TenantContract::where('id',$tenant_contract_id)->update(['is_direct_contract_pending'=>1,'work_flow_processes_code'=>null]);
              //Notification
            $salesEnquiry = SalesEnquiry::where('id',$enquiryid)->first();
            $backoffice_executive = User::role(['backoffice_executive'])->get();
            $usr = array_flatten($backoffice_executive);
           
            //$usr =  \App\User::where('id',$sendNotifyUserId)->get();
            $salesEnquiry->text = "Contract ".$contract->tenant_contact_no." Rejected";
            $salesEnquiry->href = url('tenant-contract/'.$tenant_contract_id);
           
            event(new RevokeEnquiry($salesEnquiry,$usr));
			  session()->flash('success', 'Direct Contract Is Rejected Successfully');
              return redirect()->route('tenantContractApprovedRevoke');

              break;

          case 'ACPT' :

            $sales_id = Sales::create([
              'sales_enquiry_id' =>$enquiryid,
              'sales_type' => 1,
              'work_flow_processes_code' => 108,
              'created_by' => \Auth::user()->id,
              ]);
            SalesNote::create([
              'sales_id' => $sales_id->id,
              'user_id' => \Auth::user()->id,
              'sales_notes_note' => $sales_lead_note_name,
              'created_by' => \Auth::user()->id,
            ]); 
            
            $contract->update([
              'is_direct_contract_pending'=>1,
              'tenant_contract_status'=>1,
              'work_flow_processes_code'=>108]);

            SalesEnquiry::where('id',$enquiryid)->update(['work_flow_processes_code'=>108]);
			$contract->Unit->update(['unit_vaccant_status'=>'1','unit_status'=>1]);
			$tenantData = TenantContract::where('sale_enquiry_id','=',$enquiryid)->first();
			if(!empty($contract->tenant)){
				$contract->tenant->update(['tenant_status'=>1]);
			  }
			$vacanyData = VacantVacancyLoss::where('unit_id','=',$tenantData->unit_id)->where('status','=',1)->first();
			if(isset($vacanyData) >0){
				$start = $vacanyData->vacant_from;
				$end = $tenantData->tenant_contract_effective_date;
				$validToDt = date('Y-m-d',strtotime('-1 day',strtotime($tenantData->tenant_contract_effective_date)));
				$vacantDays =dateDifference($start,$end,'%a');
				$vacany_loss = 0;

				if($vacanyData->rent_per_month > 0 && $vacantDays > 0){
					$vacany_loss = rentLossCalculationWithDays($vacantDays,$vacanyData->rent_per_month);
				}
				VacantVacancyLoss::where('unit_id','=',$tenantData->unit_id)->where('status','=',1)->update(['vacant_to'=>$validToDt,'vacant_days'=>$vacantDays,'status'=>2,'vacany_loss'=>$vacany_loss]);
			
			}
			
			$tenantInfo  = Tenant::where('id',$tenantData->tenant_id)->first();
			// Isvendor Exit or not in AX
			if( AX_ENABLE_DISABLE ==1){ 
			if(Dynamics::TenantIsExitAxPushData('isTenantExistFunc',$tenantInfo)==false){
				// Update to AX
				if(Dynamics::TenantAxPushData('AXTenant', $tenantInfo)=='Error'){
			
					return redirect()->back()->withMessage('error', 'Microsoft Dynamics API Service Error');
												
				}
			}
            }
            session()->flash('success', 'Direct Contract Is Accepted Successfully');
            return redirect()->route('tenantContractApprovedRevoke');
                  break;
      }
    }
    return redirect()->route('tenantContractApprovedRevoke');
  }
  /*
    *
    *
    * Building By Unit
    *
    */
    public function directContractBuildingByUnit(Request $request){

        $id = $request->input('id');
        $landlordContractInfo = LandlordContract::active()->where('building_id',$id)->orderBy('id','desc')->first();
       
         if(!empty($landlordContractInfo->landlord_contract_valid_from_date)){
      $fromContract = $landlordContractInfo->landlord_contract_valid_from_date->format('Y-m-d');
      //dd($fromContract);
    }
    else{
    
      $fromContract = $landlordContractInfo->start_date->format('Y-m-d');
    }
    //**** *******//
    $today = date('Y-m-d');
    $month = dateDifference($today,$fromContract);
  //dd($month);
    if($month >=3){
      $pastTwoMonthDt = date('Y-m-d', strtotime("-3 month", strtotime(date('Y-m-d'))));
      $fromContract = $pastTwoMonthDt;
    }else{
       $fromContract = $fromContract;
    }
   
    if(!empty($landlordContractInfo->landlord_contract_valid_to_date))
      $toContract   =  $landlordContractInfo->landlord_contract_valid_to_date->format('Y-m-d');
    else
      $toContract   = null;
      
        $sales_enquiry_id = $request->input('sales_enquiry_id');  
        if(!empty($sales_enquiry_id)){
          $createdUnit = TenantContract::where('sale_enquiry_id',$sales_enquiry_id)
                ->pluck('unit_id');
          $units['buildUnit'] = Unit::where('building_id',$id)->where('unit_vaccant_status',0)
                ->when($createdUnit, function ($query, $createdUnit) {
                    return $query->whereNotIn('id', $createdUnit);
                })->orderBy('unit_no','asc')->get();
        }else{
          $createdUnit = TenantContract::where('building_id',$id)->where('tenant_renewal_termination_status','!=', 8)->pluck('unit_id');
          $units['buildUnit'] = Unit::where('building_id',$id)->where('unit_vaccant_status',0)->when($createdUnit, function ($query, $createdUnit) {
                    return $query->whereNotIn('id', $createdUnit);
                })->orderBy('unit_no','asc')->get();
        }
        $units['contractValid'] =  array($fromContract, $toContract);
  
       return json_encode($units);

    }
	
/*
    *
    *Tenant contract Add Municipality
    *
    */
  public function tenantContractAddMunicipality(Request $request){
    $tenant_contract_id = $request->tenant_contract_id;
    $tenantContractInfo = TenantContract::where('id',$tenant_contract_id)->first();
    return view('backoffice::TenantContract.tenant_contract_municipality_modal',compact('tenant_contract_id','tenantContractInfo'));
  }

  public function tenantContractAddMunicipalityStore(Request $request){

    $this->validate($request, [        
        'tenant_contract_muncipality_agr_no'=>'required',
        'tenant_contract_registered_in'=>'required',
        ]);
      $tenant_contract_id = $request->tenant_contract_id;
      $tenant_contract_muncipality_agr_no = $request->tenant_contract_muncipality_agr_no;
      $tenant_contract_registered_in = $request->tenant_contract_registered_in;
      $tenant_contract_registered_date = $request->tenant_contract_registered_date;
      $tenant_contract_note = $request->tenant_contract_note;
     

      TenantContract::where('id',$tenant_contract_id)->update(['tenant_contract_muncipality_agr_no'=>$tenant_contract_muncipality_agr_no,'tenant_contract_registered_in'=>$tenant_contract_registered_in,'tenant_contract_registered_date'=>$tenant_contract_registered_date,'tenant_contract_note'=>$tenant_contract_note,'tenant_contract_is_reg_municipality'=>1]);

      session()->flash('success', 'Tenant Contract Municipality Changed Successfully');
      return redirect()->route('tenant-contract.index');
    }
	public function tenantContractCode(){

      $prefix  = prefixData('tenant_agreement_prefix')->configuration_value.prefixData('tenant_agreement_prefix')->configuration_year;
     
      $incVal  = prefixData('tenant_agreement_prefix')->configuration_increment_value;
     
      $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);
     
      return array('code'=>$nextCode,'inc'=>$incVal);

    }


 }
