<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Sales\Entities\TenantContract;
use Modules\Sales\Entities\LandlordContract;
use Modules\Sales\Entities\Tenant;
use Modules\BackOffice\Entities\RenewalOrTermination;
use Modules\BackOffice\Entities\Renewal;
use Modules\BackOffice\Entities\RenewalNote;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Entities\ContractRenewalType;
use Modules\BackOffice\Entities\ViewDueRenewal;
use Modules\BackOffice\Entities\ViewRenewalUser;
use Modules\Masters\Entities\Bank;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Location;
use Modules\BackOffice\Entities\Pdc;
use Modules\Masters\Entities\Occupant;
use Modules\Masters\Entities\Employee;
use Modules\Sales\Entities\TenantDocument;
use App\Http\Controllers\Controller;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Masters\Entities\VacantVacancyLoss;
use App\Setting;
use DB;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use Modules\BackOffice\Http\Controllers\InvoiceController as Invoices;
use Modules\BackOffice\Entities\AccountParams;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\Invoice;
use Modules\BackOffice\Entities\TenantInvoiceDimension;
use Modules\BackOffice\Events\TenantRenewalApprove;
use Modules\BackOffice\Events\TenantRenewalReject;
use Modules\Sales\Http\Controllers\TenantContractController as Contract ;
use Modules\General\Http\Controllers\GeneralController as General;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\RenewalFormPdfEmail;
use Modules\BackOffice\Entities\ReceiptsGeneration;

class TenantRenewalController extends Controller 
{
    public function __construct()
    {
        $this->middleware('auth');  
        $this->middleware('permission:renewal_due_list', ['only' => ['tenantRenewal']]);
        $this->middleware('permission:contract_under_renewal_view', ['only' => ['contractUnderRenewal']]);        
        $this->middleware('permission:renewal_contract_add', ['only' => ['renewalNewContract','store']]);
        $this->middleware('permission:renewal_contract_edit', ['only' => ['edit','update']]); 
        $this->middleware('permission:renewal_contract_list', ['only' => ['renewalContract','newContractShow']]);
        $this->middleware('permission:renewal_contract_approval_list', ['only' => ['renewalContractApprovalStage']]);      
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

      
      
      $route   =  $request->route;
      $request->flash();
     
      //Get Configuration Month from Settings
      $config_data = Setting::where('configuration_settings','renewal_notification')
                              ->first()->configuration_value;
     //  dd($config_data);
// 0-default -Normal
// 1-Requested
// 2-renewal Under Renewal
// 3-renewal Under Approval
// 4-renewal Rejected
// 5-RenewedContract
// 6-old contract of corresponding renewed contract( renewal process ends it will 5, After cron run it will change to 6)
// 7-Termination Request
// 8- Terminated
// 9- Approved

      

                          
      $current = Carbon::now(); 
	    $futureDate = $current->addMonths($config_data)->format('Y-m-d H:i:s');
      
	  $dueForRenewal = ViewDueRenewal::select('id','tenant_contract_no','building_name','unit_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_name','tenant_contact_email','tenant_personal_email')
            ->groupBy('id','tenant_contract_no','building_name','unit_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_name','tenant_contact_email','tenant_personal_email')
            ->areFilter()->filter($request)->whereDate('tenant_contract_valid_to_date','<',$futureDate)
                                        ->where('tenant_contract_status',1)
                                        ->where(function ($query) {
                                          $query->where('tenant_renewal_termination_status',0)
                                                ->where('tenant_renewal_termination_status','!=',2)
                                                ->orWhere('tenant_renewal_termination_status',4)
                        ->orWhere('tenant_renewal_termination_status',1)
												->orWhere('tenant_renewal_termination_status',5)
                        ->orWhere('tenant_renewal_termination_status',6);
                                        });

        $dueForRenewals = $dueForRenewal->sortable()->paginate($this->noOfRecord);
      //dd($dueForRenewals);

      $quick_url = route('tenantRenewal.index');                              
    

      if(isset($request->ajax))
      return view('backoffice::RenewalOrTermination.tenant_renewal_due_list_ajax',compact('dueForRenewals','request','route'));

      return view('backoffice::RenewalOrTermination.tenant_renewal_due_list',compact('dueForRenewals','enquiry_fields','operations','quick_url'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {   
        //
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {

      
        $tenantContractLatest = TenantContract::orderBy('id','desc')->limit(1)->first();
        $prefix  = prefixData('tenant_prefix')->configuration_value; 
        $prefix  = prefixData('tenant_agreement_prefix')->configuration_value;   
               
        $generateCode = $this->tenantContractCode();
        $contractCode = $generateCode['code'];
	  
        $this->validate($request, [                    
            'tenant_contract_rent'   => 'required',
            'tenant_contract_start_date'   => 'required|date',
            'tenant_contract_effective_date'   => 'required|date',
            'tenant_contract_valid_to_date'   => 'required|date',
            'tenant_contract_payment_type' => 'required', 
            'tenant_contract_registered_in'=>'required',
         ]);
        $tenantId = $request['tenant_id'];     
        $occupant_id = $request['occupant_id'];       
        if(!empty($request['occupant_name']) && empty($request['occupant_id'])){

            $occupant =  Occupant::create([
              'occupant_name' => $request['occupant_name'],
              'occupant_primary_contact_no' =>$request['occupant_primary_contact_no'],
              'occupant_email' => $request['occupant_email'],
              'created_by' => \Auth::user()->id,
            ]);
            
            $occupant_id = $occupant->id;

        }
        // Process Flow Insertion
        $oldContractId = $request['tenant_contract_id'];
        $next_process_id = 301;
        $PreviousRenewal = Renewal::where('work_flow_processes_code',$next_process_id)
                                 ->where('renewal_type',1)
                                 ->where('old_contract_id',$oldContractId)
                                 ->orderBy('id','desc')->limit(1)->first();
        $PreviousRenewal->renewalUser()->update(['status' => 0]);

		$yearToMonth = ($request['yeartxt'] > 0)? $request['yeartxt']*12:0;
		$durationInMonth = $yearToMonth + $request['monthtxt'];
		
		$contract =  new Contract;
       
        $ContractStatus = $request['Status'];
        $data = $contract->getContractData();
        $data['tenant_contract_old_no'] = $request['tenant_contract_old_no'];
        /*$data['occupant_id'] = $occupant_id;*/
        $data['tenant_contract_penalty'] = $request['tenant_contract_penalty'];
        $data['tenant_contract_no'] = $contractCode;
        $data['tenant_id'] = $tenantId;
        $data['occupant_id'] = $request['occupant_id'];
        $data['unit_usage'] = $request['unit_usage'];
        $data['tenant_marketing_executive'] =$request['tenant_marketing_executive'];
        $data['tenant_contract_duration_countdown'] = $request['yeartxt'].'-'.$request['monthtxt'].'-'.$request['daytxt'];
		$data['tenant_contract_duration'] = $durationInMonth;
        $data['tenant_contract_value'] = replaceCommaWithSpace($request['tenant_contract_value']);
        /*$data['tennat_contract_direct_indirect_status'] = 1;*/
        $data['tenant_renewal_termination_status'] = 9;

         if($request['pdc_check'] == 2){
          $partial_comment = $request['partial_comment'];
        }else{
          $partial_comment = '';
        }



        $data['pdc_check'] = $request['pdc_check'];
        $data['partial_comment'] = $request['partial_comment'];
        $data['deposit_check'] = $request['deposit_check'];
       // $data['tenant_contract_is_reg_municipality'] = $request['tenant_contract_is_reg_municipality'];
	   if($data['tenant_contract_muncipality_agr_no'] =="" or $data['tenant_contract_muncipality_agr_no'] =="NULL" or $data['tenant_contract_muncipality_agr_no'] =="null"){$data['tenant_contract_is_reg_municipality'] = 0;}else{
          $data['tenant_contract_is_reg_municipality'] = 1;
        }
        //$data['pdc_partially_check'] = $request['pdc_partially_check'];
        $data['created_by'] = \Auth::user()->id;


          $muncipality_document = $request->file('muncipality_agreement');

        if(isset($muncipality_document)){

          $uniqueFileName_muncipality = $muncipality_document->getClientOriginalName();
          $img_path_muncipality =    Storage::putFile('public/Document', $muncipality_document);

          $data['tenant_muncipality_agreement'] = $img_path_muncipality;

        }

        


        $tenantContract = TenantContract::create($data);
		
		Setting::where('configuration_settings','tenant_agreement_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);
		
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
        /*$OldContractUpdate = TenantContract::where('id',$oldContractId)->update(['tenant_renewal_termination_status'=>2]);*/
        $contract_status = DB::table('tenant_contract_status')->insert(['tenant_status_id' => 1,'tenant_contract_id' => $tenantContract->id]);
        // Process Flow Insertion
        
      	$general =  new General;
      	$processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);

        //  Status  Previous Stage    inactive         
        Renewal::where('old_contract_id',$oldContractId)->update([
                  'status' => 0
                ]);


        $renewal = new Renewal;
        $renewal->old_contract_id     = $oldContractId; 
        $renewal->new_contract_id     = $tenantContract->id; 
        $renewal->renewal_type     = 1;
        $renewal->work_flow_processes_code = $next_process_id;
        $renewal->created_by           = \Auth::user()->id;
        $renewal->save();

        if($processAssign != false) {

          foreach($processAssign->assign as $val){
             $renewal->renewalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
             $renewal->save();
          }
          $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
          $renewal->save();
      
        }else{

          $previousProcess = $general->getPreviousOrder($next_process_id);

          if($previousProcess !=0){

            $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

            foreach($previousAssign->assign as $val){
              $renewal->renewalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
              $renewal->save();
            }
            $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
            $renewal->save();

          }else{

            $workFlowProcess = $general->workFlowProcess($next_process_id);
            $renewal->renewalUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
            $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]); 
            $renewal->save();
                    
            
          }
        }

        /****** Activity log *****/

          activity(' Tenant Renewal Contract Created')
            ->performedOn($tenantContract)
            ->causedBy(\Auth::user()->id)
            ->withProperties($tenantContract)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Renewal  Contract Created Successfully');
        return redirect()->route('tenantRenewalContract');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
      $tenantContract = TenantContract::where('id',$id)->first();
	  $remainingDays	= 0 ;
       $renewalNotes = RenewalNote::whereHas('renewal', function ($query) use($id) {
                $query->where('old_contract_id', '=', $id)
                ->where('renewal_type',1);                      
            })->get();

        $stageNotes = Renewal::where('old_contract_id', '=', $id)
                               ->where('renewal_type',1)
                               ->where('renewal_notes','!=','')->get();


        $arNotes =  $renewalNotes->where('contract_renewal_type_id','>',0); 
        $renewalNotes =  $renewalNotes->where('contract_renewal_type_id','<=',0);


        $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
     $today = strtotime(date('Y-m-d'));

     $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds   

        $termination = Termination::where('contract_id',$tenantContract->id)->where('work_flow_processes_code',503)->where('termination_refer_back',1)->first();


      return view('backoffice::RenewalOrTermination.tenant_renewal_due_view',compact('tenantContract','arNotes','renewalNotes','stageNotes','remainingDays','termination'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        $banks = Bank::get();
        $locations = Location::get();
        $tenantContract  = TenantContract::with('tenantDocument')->where('id',$id)->first();
        $employeeList     = Employee::whereHas('user',function ($query){
            $query->role('sales_person');
        })->orderBy('id', 'DESC')->get();//dd($tenantContract->tenantDocument);
        /*$TenantDocument = TenantDocument::where('tenant_contract_id',$id)->get();*/
          $landloard = LandlordContract::where('building_id',$tenantContract->building_id)
                                      ->active()->first();

        $landlord_contract_valid_to_date =  date('Y-m-d',strtotime($landloard->landlord_contract_valid_to_date)) ;  

        $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;  
		
		$year    = prefixData('tenant_agreement_prefix')->configuration_year;
        $isYearCorrect = (date('y') == $year)?true:false;


        return view('backoffice::RenewalOrTermination.tenant_renew_contract_edit',compact('tenantContract','banks','locations','employeeList','upload_size','landlord_contract_valid_to_date','isYearCorrect'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,$id)
    {
       	$this->validate($request, [                    
            'tenant_contract_rent'   => 'required',
            'tenant_contract_start_date'   => 'required|date',
            'tenant_contract_effective_date'   => 'required|date',
            'tenant_contract_valid_to_date'   => 'required|date',
            'tenant_contract_payment_type' => 'required', 
            'tenant_contract_registered_in'=>'required',

     	]);
      $occupant_id = '';
      $contract =  new Contract;
      $data = $contract->getContractData();
      $data['tenant_contract_penalty'] = $request['tenant_contract_penalty'];
      $data['tenant_marketing_executive'] =$request['tenant_marketing_executive'];
      $data['tenant_contract_duration_countdown'] = $request['yeartxt'].'-'.$request['monthtxt'].'-'.$request['daytxt'];
	  $data['tenant_contract_value'] = replaceCommaWithSpace($request['tenant_contract_value']);
	  $data['status'] = $request['Status'];


     if($request['pdc_check'] == 2){
          $partial_comment = $request['partial_comment'];
        }else{
          $partial_comment = '';
        }

    $data['pdc_check'] = $request['pdc_check'];
    $data['partial_comment'] = $request['partial_comment'];

   
    $data['deposit_check'] = $request['deposit_check'];
   // $data['tenant_contract_is_reg_municipality'] = $request['tenant_contract_is_reg_municipality'];
    if($data['tenant_contract_muncipality_agr_no'] =="" or $data['tenant_contract_muncipality_agr_no'] =="NULL" or $data['tenant_contract_muncipality_agr_no'] =="null"){$data['tenant_contract_is_reg_municipality'] = 0;}else{
          $data['tenant_contract_is_reg_municipality'] = 1;
        }
    //$data['pdc_partially_check'] = $request['pdc_partially_check'];
	  $data['updated_by'] = \Auth::user()->id;

    $tenantContract =  TenantContract::where('id',$id)->update($data);
      $documents = $request->file('tenant_document_file_name');
        if(!empty($documents)) {
            foreach($documents as $document){
            $uniqueFileName = $document->getClientOriginalName();
            $img_path =    Storage::putFile('public/Document', $document);
            $tenantDocument =  TenantDocument::create([
                'tenant_documents_name' =>  $uniqueFileName,
                'tenant_documents_file_name' => $img_path,
                'tenant_documents_status' => 2,
                'tenant_contract_id'=>$id,
                'created_by' => \Auth::user()->id,
              ]);
            }
        }
        /****** Activity log *****/

          activity('Tenant Renewal Contract Updated')
            ->causedBy(\Auth::user()->id)
            ->withProperties($tenantContract)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Tenant Contract Updated Successfully');
      	return redirect()->route('tenantRenewalContract');
        
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(RenewalOrTermination $tenantRenewal)
    {
        //
    }


    public function tenantRenewalStageDue(Request $request,$contractId,$stage,$status,$oldId){     

         if($stage == 304){

           $renewal_new = Renewal::where('old_contract_id',$contractId)
                                 ->where('work_flow_processes_code',300)
                                 ->where('renewal_type',1)
                                 ->orderBy('id','desc')->first();

            if(empty($renewal_new)){
                
                  Renewal::create([
                    'old_contract_id' =>  $contractId,
                    'work_flow_processes_code' =>  300,
                    'renewal_notes' =>  $request->renewal_notes,
                    'renewal_type' =>  1 ,
                    'created_by' =>  \Auth::user()->id
                  ]);
             }

         }


         return $this->tenantRenewalStage($contractId,$stage,$status,$oldId);

    }




    /*
    *
    * Accept or terminate / Reject
    *
    *
    */
    public function tenantRenewalStage($contractId,$stage,$status,$oldId)
    {   
    // dd($stage);
	    $oldContractId = $oldId;//dd($contractId);
      $next_process_id = $stage;
      $general =  new General;
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);
      $contractDetails = TenantContract::where('id',$contractId)->first();
	  $unit_id = $contractDetails->unit->id;
     /* $oldContract = TenantContract::where('id',$contractId)->first()->tenant_contract_old_no;

      if(!empty($oldContract)){
      	$oldContractId = TenantContract::where('tenant_contract_old_no',$oldContract)->first()->id;
      }
      */
      if($stage == 501){
        // Update status to Tenanct Contract
        TenantContract::where('id',$contractId)->update(['tenant_renewal_termination_status'=>$status]);
        $previousProcess = Termination::where('work_flow_processes_code',$next_process_id)->where('contract_id',$contractId)->where('termination_type',1)->orderBy('id','desc')->latest()->first();//dd($previousFlow);
        if($previousProcess)$previousProcess->terminationUser()->update(['status' => 0]);
        $termination = new Termination; 
        $termination->contract_id     = $contractId;
        $termination->termination_type  = 1;
        $termination->work_flow_processes_code = $next_process_id;
        $termination->termination_takenover_date = $contractDetails->tenant_contract_valid_to_date;
        $termination->termination_date = $contractDetails->tenant_contract_valid_to_date;
        $termination->created_by           = \Auth::user()->id;
        $termination->save();
        if($processAssign != false) {

          foreach($processAssign->assign as $val){
             $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
             $termination->save();
          }
          $termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
          $termination->save();
      
        }else{

          $previousProcess = $general->getPreviousOrder($next_process_id);

          if($previousProcess !=0){

            $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

            foreach($previousAssign->assign as $val){
              $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
              $termination->save();
            }
            $termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
            $termination->save();

          }else{

            $workFlowProcess = $general->workFlowProcess($next_process_id);
            $termination->terminationUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
            $termination->save();
                    
            
          }
        }
      }else{

        
         if($next_process_id == 304){

           $renewal_new = Renewal::where('old_contract_id',$contractId)->where('renewal_type',1)
                                 ->where('work_flow_processes_code',300)
                                 ->orderBy('id','desc')->first();

            if(empty($renewal_new)){
                
                  Renewal::create([
                    'old_contract_id' =>  $contractId,
                    'work_flow_processes_code' =>  300,
                    'renewal_type' =>  1 ,
                    'created_by' =>  \Auth::user()->id
                  ]);
             }

         }


          //  Status  Previous Stage    inactive 
        $old_contractId = ($oldContractId != 0 && $status== 5)? $oldContractId : $contractId;
        Renewal::where('old_contract_id',$old_contractId)->update([
          'status' => 0
        ]);


        $prev_stage_renewal  = Renewal::where('old_contract_id',$old_contractId)
                              ->where('renewal_type',1)
                              ->latest()->first();

        $prev_stage =  $prev_stage_renewal->work_flow_processes_code;        

      	$renewal = new Renewal;
        if($oldContractId != 0 && $status== 5) {
          $renewal->old_contract_id     = $oldContractId;
        }else{$renewal->old_contract_id     = $contractId;}
       
        if($status== 5)$renewal->new_contract_id     = $contractId;
         
        $renewal->renewal_type     = 1;
        $renewal->work_flow_processes_code = $next_process_id;
        $renewal->created_by           = \Auth::user()->id;
        $renewal->save();
        // Update status to Tenanct Contract
        if($oldContractId != 0 && $status== 3) {/*TenantContract::where('id',$oldContractId)->update(['tenant_renewal_termination_status'=>$status]);*/}else{TenantContract::where('id',$contractId)->update(['tenant_renewal_termination_status'=>$status]);}

      
        // Process Flow Insertion
        $PreviousRenewal = Renewal::where('work_flow_processes_code',$next_process_id)
                                    ->where('renewal_type',1)
                                    ->orderBy('id','desc')->limit(1)->first();

        if(!empty($PreviousRenewal)) $PreviousRenewal->renewalUser()->update(['status' => 0]);
       

        if($processAssign != false) {

          foreach($processAssign->assign as $val){
             $renewal->renewalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
             $renewal->save();
          }
          $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
          $renewal->save();
      
        }else{

          $previousProcess = $general->getPreviousOrder($next_process_id);

          if($previousProcess !=0){

            $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

            foreach($previousAssign->assign as $val){
              $renewal->renewalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
              $renewal->save();
            }
            $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
            $renewal->save();

          }else{

            $workFlowProcess = $general->workFlowProcess($next_process_id);
            $renewal->renewalUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
            $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]); 
            $renewal->save();
                    
            
          }
        }
      }
  		if($oldId == -1){
        session()->flash('success', 'Renewal request Created Successfully');
        return redirect()->route('tenantTermination.index');
        
      }


      if($stage == 501){
		Unit::where('id',$unit_id)->update(['unit_vaccant_status'=>2]);
        session()->flash('success', 'Renewal Vacated Successfully');
        return redirect()->route('tenantRenewal.index');
      }else{
       
       // session()->flash('success', 'Renewal request Created Successfully');
      }

     //dd($prev_stage);
      switch($prev_stage){

//tenantRenewal.index     Due for renewal     300
//  Contract  Under tenantRenewal  .   Contract under renewal   304    
//  tenantRenewal/renewalContractApproval   ---   renewalContractApproval  302 
//  tenantRenewalContract  ---    tenantRenewal/contract   301 
//renewedContract  ---  tenantRenewedContract   303

        case 300 : //if( \Auth::user()->hasAnyRole([ 'super_admin','are']) )         
                  // return redirect()->route('tenantRenewalContract'); 
                   session()->flash('success', 'Renewal request Created Successfully');  
                   return redirect()->route('tenantContract.underRenewal');   

        case 304 : 
        return redirect()->route('tenantContract.underRenewal');

        case 302 : 
        if($stage == 301){
          session()->flash('success', 'Tenant Renewal Approved Successfully'); 
        }else{
           session()->flash('success', 'Tenant Renewal Rejected Successfully');
        }
        return redirect()->route('renewalContractApproval');

        case 301 :  
        session()->flash('success', 'Send for Renewal Contract Successfully'); 
        return redirect()->route('tenantRenewalContract');

        case 303 : 
         session()->flash('success', 'Renewed Successfully'); 
         return redirect()->route('tenantRenewedContract');
                     
 
        default :   return redirect()->back();


      }



    	// if($stage == 302){

    	// 	return redirect()->route('tenantRenewalContract');
    	// }else

      
  		

    }
    /*
    *  contractUnderRenewal   List   
    *
    */
    public function contractUnderRenewal(Request $request){


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

      
      
      $route   =  $request->route;
      $request->flash(); 
     
     
      $dueForRenewals = ViewDueRenewal::areFilter()->backOfficeExecutiveFilter()->filter($request)->referBack($request)->where('tenant_contract_status',1)->where('tenant_renewal_termination_status',2)->where('status',1)->sortable()->paginate($this->noOfRecord);

      $quick_url = route('tenantContract.underRenewal'); 
      
      if(isset($request->ajax))
      return view('backoffice::RenewalOrTermination.tenant_contract_underRenewal_list_ajax',compact('dueForRenewals','request','route'));

      return view('backoffice::RenewalOrTermination.tenant_contract_underRenewal_list',compact('dueForRenewals','enquiry_fields','operations','quick_url'));

    }


    /*
    *
    *  TenantContractRenewalType
    */
    public function tenantContractRenewalType(Request $request ,TenantContract $tenant_contract){

      $renewal_types = ContractRenewalType::where('id','<>',1)->get();
      $type = $request->type;

      return view('backoffice::RenewalOrTermination.contractType',compact('renewal_types','tenant_contract','type'));
      
    }

    /*
    *  tenantContractRenewalTypeStore  
    *
    */
    public function tenantContractRenewalTypeStore( Request $request,TenantContract $tenant_contract){


      $renewal_id  = Renewal::where('old_contract_id',$tenant_contract->id)->latest()->first()->id;  
          
         RenewalNote::create([
              'renewal_id' => $renewal_id,
              'renewal_type' => 1,
              'user_id' => \Auth::user()->id,
              'renewal_notes_note' => $request['notes'],
              'created_by' => \Auth::user()->id,
              'contract_renewal_type_id' =>  $request['contract_renewal_type_id'],
          ]);

      
      if($request['contract_renewal_type_id'] == 1)   ///  Normal Renewal 
       return redirect()->route('tenantRenewalStage',[$tenant_contract->id,301,1,0]);
     
      
       return redirect()->route('tenantRenewalStage',[$tenant_contract->id,302,3,0]); 


    }


    /*
    *
    * Add Renewal Note
    *
    */
    public function storeRenewalNote(Request $request) {
      
      $this->validate($request, [                  
          'renewal_note' => 'required|max:250' ,        
      ]);  
      $url = $request['current_url'];
      $notes = RenewalNote::create([
          'renewal_id' => $request['renewal_id'],
          'renewal_type' => 1,
          'user_id' => \Auth::user()->id,
          'renewal_notes_note' => $request['renewal_note'],
          'created_by' => \Auth::user()->id,
      ]);
      /****** Activity log *****/

          activity('Added Renewal Note')
            ->performedOn($notes)
            ->causedBy(\Auth::user()->id)
            ->withProperties($notes)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

       
      session()->flash('success', 'Renewal Notes Created Successfully');
      return redirect($url);

    }
    /*
    *
    *
    * Renewal Contract
    *
    *
    */
    public function renewalContract(Request $request) 
    {
        
      $enquiry_fields = [
           'old_contract' => 'Old Contract No',
           'new_contract' => 'New Contract No',
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



          //  Status  Previous Stage    inactive 
       // $old_contractId = ($oldContractId != 0 && $status== 5)? $oldContractId : $contractId;
       //  Renewal::
       // // where('old_contract_id',$old_contractId)
       //  where('status',1)
       //  ->update([
       //    'status' => 0
       //  ]);

                 
     
      $request->flash();
      $quick_url =  $route   =  route('tenantRenewalContract');

      //$tenantRenewal = Renewal::
         // whereHas('oldTenantContract', function ($query) use($rolesNames,$request){
           //                  $query->where('tenant_contract_status',1)
         //                  //  ->whereIn('tenant_renewal_termination_status',[1,2,3,4])
           //                  ->whereIn('tenant_renewal_termination_status',[9])
         //                    ->filter($request)
         //                    ->sortable();
                                         
          //              })->whereHas('renewalUsers', function ($query) {
            //            $query->where('status','=',1);
            //        })->newContract($request)                  
          //    ->

      $tenantRenewal = ViewRenewalUser::select('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_no','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
            ->groupBy('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_no','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
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
        $tenantRenewals = $tenantRenewal->sortable()->paginate($this->noOfRecord);
        
        //dd($tenantRenewals);
        if(isset($request->ajax))
          return view('backoffice::RenewalOrTermination.tenant_renewal_contract_list_ajax',compact('tenantRenewals','request','route'));

      return view('backoffice::RenewalOrTermination.tenant_renewal_contract_list',compact('tenantRenewals','enquiry_fields','operations','quick_url'));
    
      
    }
    /*
    *
    *
    * Renewal New Contract
    *
    */
    public function renewalNewContract($id) 
    { 
        $tenantContractLatest = TenantContract::orderBy('id','desc')->limit(1)->first();
        $prefix  = prefixData('tenant_agreement_prefix')->configuration_value;
        
    		$renewal_data = null;
    		
    		$year    = prefixData('tenant_agreement_prefix')->configuration_year;
           // dd( $year);
    		$isYearCorrect = (date('y') == $year)?true:false;

    		$generateCode = $this->tenantContractCode();
    		$ContractCode = $generateCode['code'];
		
        $tenantContract = TenantContract::where('id',$id)->first();//dd($tenantContract);
        //$startDate = $tenantContract->tenant_contract_start_date;
        $startDate = date('Y-m-d', strtotime($tenantContract->tenant_contract_valid_to_date . ' +1 day'));

        $endDate = date('Y-m-d', strtotime($tenantContract->tenant_contract_valid_to_date . ' +1 year'));


        $tenantContract->tenant_contract_start_date = $startDate;
     	  $employeeList     = Employee::whereHas('user',function ($query){
                                          $query->role('sales_person');
                                      })->orderBy('id', 'DESC')->get();        
        $banks = Bank::get();
        $locations = Location::get();

        $landloard = LandlordContract::where('building_id',$tenantContract->building_id)
                                      ->active()->first();

        //$landlord_contract_valid_to_date =  date('Y-m-d',strtotime($landloard->landlord_contract_valid_to_date)) ;  


        //----------------valid to date checking   

     //     $start_from_date = date_create($endDate);
     //     $valid_to_date = date_create($landlord_contract_valid_to_date);
      //   $interval_valid = date_diff($valid_to_date, $start_from_date); 
       // $interval_valid = date_diff($start_from_date, $valid_to_date); 
     // echo $endDate .'<br>'.$landlord_contract_valid_to_date; 
         //   print_r($interval_valid); exit;
       // if($interval_valid->invert == 0 && $interval_valid->days > 0)
       //  $contract_valid_to_date = $landlord_contract_valid_to_date;
       // else      


        $contract_valid_to_date = $endDate;


      //----------------------------------------------------
        $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;  



      if(count($tenantContract->tenantRenewalForm) > 0){
        $form_count = count($tenantContract->tenantRenewalForm);
        $renewal_data = $tenantContract->tenantRenewalForm[$form_count-1];
        
        $interval = contractDurationCalculation($renewal_data->tenant_contract_start_date, $renewal_data->tenant_contract_valid_to_date );
      
        $rent = $renewal_data->tenant_contract_rent;
        $contract_amount = rentCalcualtionWithDuration($interval, $rent);
        
      }else{

        $interval = contractDurationCalculation($startDate,$contract_valid_to_date );
      
        $rent =  $tenantContract->tenant_contract_rent;
        $contract_amount = rentCalcualtionWithDuration($interval, $rent);
      
      }   

      return view('backoffice::RenewalOrTermination.tenant_renew_contract_create',compact('tenantContract','banks','locations','ContractCode','employeeList','upload_size','renewal_data','contract_amount','interval','contract_valid_to_date','startDate','isYearCorrect'));
    }
    /*
    *
    *
    * sendApprovalFromRenewal
    *
    *
    */
    public function sendApprovalFromRenewal($newContract,$next_process_id,$status,$oldContract)
    {
      
      $general =  new General;
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);

      
      // Process Flow updation
      $PreviousRenewal = Renewal::where('work_flow_processes_code',$next_process_id)
          ->where('old_contract_id',$oldContract)
          ->where('new_contract_id',$newContract)
          ->where('renewal_type',1)->orderBy('id','desc')->limit(1)->first();

      if(!empty($PreviousRenewal)) 
        $PreviousRenewal->renewalUser()->update(['status' => 0]);


       Renewal::where('old_contract_id',$oldContract)->update(['status' => 0]);
    
     // Update status to Tenanct Contract
      $newContractInfo = TenantContract::where('id','=',$newContract)->first();
    
      if($newContractInfo->tenant_contract_effective_date <= date('Y-m-d')){
         $newContractPara = array();
         $newContractPara['work_flow_processes_code']=108;
         // Renewed status and Active Contract
         $newContractPara['tenant_renewal_termination_status']= 6;
         $newContractPara['tenant_contract_status']=1;
         TenantContract::where('id',$oldContract)->update([
            'tenant_contract_status'=>0
         ]); 
      }
      else{
        
        $newContractPara['work_flow_processes_code']=108;
        $newContractPara['tenant_renewal_termination_status']=$status;
          
      }
      TenantContract::where('id',$newContract)->update($newContractPara);
	  
     // Process Flow Insertion
      $renewal = new Renewal;
      $renewal->old_contract_id     = $oldContract;
      $renewal->new_contract_id     = $newContract;       
      $renewal->renewal_type     = 1;
      $renewal->work_flow_processes_code = $next_process_id;
      $renewal->created_by           = \Auth::user()->id;
      $renewal->save();
      if($processAssign != false) {

        foreach($processAssign->assign as $val){
           $renewal->renewalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
           $renewal->save();
        }
        $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
        $renewal->save();
    
      }else{

        $previousProcess = $general->getPreviousOrder($next_process_id);

        if($previousProcess !=0){

          $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

          foreach($previousAssign->assign as $val){
            $renewal->renewalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
            $renewal->save();
          }
          $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
          $renewal->save();

        }else{

          $workFlowProcess = $general->workFlowProcess($next_process_id);
          $renewal->renewalUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
          $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]); 
          $renewal->save();
                  
          
        }
      }
      session()->flash('success', 'Renewed Contract Successfully');
      return redirect()->route('tenantRenewalContract');
    }
    /*
    *
    * New Contract Details View
    *
    *
    */
    public function newContractShow($id,$stage)
    {
    	clearNotification('Modules\BackOffice\Notifications\TenantRenewalNotification',$id);
    	readNotification('Modules\BackOffice\Notifications\TenantRenewalNotification',$id); 
      	$tenantContract = TenantContract::with('renewalContract')->where('id',$id)->first();
       	$oldContractId = $tenantContract->tenant_contract_old_no;
 
      	if(!empty($oldContractId)){
          $oldContractDetail = TenantContract::with('renewalContract')->where('tenant_contract_no',$oldContractId)->first();
      		
      	}
        $allNotes = Renewal::where('new_contract_id',$id)->where('renewal_type',1)->get();
        
        $renewalNotes = RenewalNote::whereHas('renewal', function ($query) use($id) {
                $query->where('new_contract_id', '=', $id)
                      ->where('renewal_type',1);                      
            })->get();
       
      	$roles =  \Auth::user()->getRoleNames()->toArray();
//------------------------------------
       $renewalOldNotes = RenewalNote::whereHas('renewal', function ($query) use($oldContractDetail) {
                $query->where('old_contract_id', '=', $oldContractDetail->id);                      
            })->get();

        $stageNotes = Renewal::where('old_contract_id', '=', $oldContractDetail->id)
                               ->where('renewal_notes','!=','')->get();


        $arNotes =  $renewalOldNotes->where('contract_renewal_type_id','>',0); 
        $renewalNotes =  $renewalOldNotes->where('contract_renewal_type_id','<=',0);  

     $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
     $today = strtotime(date('Y-m-d'));

     $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds    



      	return view('backoffice::RenewalOrTermination.tenant_renew_contract_view',compact('tenantContract','oldContractDetail','allNotes','stage','renewalNotes','roles','arNotes','remainingDays'));

      	/*return view('backoffice::RenewalOrTermination.tenant_renewal_contract_approval_view',compact('tenantContract','stage'));*/

    } 
    /*
    *
    *
    * Renewal Contract Approval Stage List
    *
    *
    */
    public function renewalContractApprovalStage(Request $request) 
    {    
        
        $enquiry_fields = [
           'tenant_contract_old_no' => 'Old Contract No',
           'contract_no' => 'New Contract No',
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
      $quick_url =  $route  =  route('renewalContractApproval');

      $tenantRenewal = ViewRenewalUser::areFilter()->pendingApprovals($request)->select('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_no','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status')
            ->groupBy('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_no','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status')
			->where('work_flow_processes_code', '=', 302)->where('status',1)->where('user_status',1)
			->renewalUsers()->filter($request);
             
            
     $tenantRenewals = $tenantRenewal->sortable()->paginate($this->noOfRecord);

       if(isset($request->ajax)){
       return view('backoffice::RenewalOrTermination.tenant_renewal_contract_approval_list_ajax',compact('tenantRenewals','request','route'));
       }
       

        return view('backoffice::RenewalOrTermination.tenant_renewal_contract_approval_list',compact('tenantRenewals','enquiry_fields','operations','quick_url'));
    }
    /*
    *
    * New Contract Details View
    *
    *
    */
    public function newContractApprovalShow($id,$stage)
    {
      clearNotification('Modules\BackOffice\Notifications\TenantRenewalNotification',$id);
      readNotification('Modules\BackOffice\Notifications\TenantRenewalNotification',$id); 
        $tenantContract = TenantContract::where('id',$id)->first();
        $oldContractId = $tenantContract->tenant_contract_old_no;

        if(!empty($oldContractId)){
          $oldContractDetail = TenantContract::with('renewalContract')->where('tenant_contract_no',$oldContractId)->first();
          
        }                      

        $renewalNotes = RenewalNote::whereHas('renewal', function ($query) use($id) {
                $query->where('old_contract_id', '=', $id)->where('renewal_type',1);                      
            })->get();

        $stageNotes = Renewal::where('old_contract_id', '=', $id)->where('renewal_type',1)
                               ->where('renewal_notes','!=','')->get();

		$getLastRenewalId = Renewal::where('old_contract_id', '=', $id)->where('renewal_type',1)->where('work_flow_processes_code','=',$stage)->orderBy('id', 'desc')->limit(1)->first();
		
        $arNotes =  $renewalNotes->where('contract_renewal_type_id','>',0); 
        $renewalNotes =  $renewalNotes->where('contract_renewal_type_id','<=',0);     
      

        $roles =  \Auth::user()->getRoleNames()->toArray();

        $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
        $today = strtotime(date('Y-m-d'));

        $datediff = $validTo - $today;
        $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds 

        return view('backoffice::RenewalOrTermination.tenant_renewal_contract_approval_view',compact('getLastRenewalId','tenantContract','stage','renewalNotes','roles','arNotes','stageNotes','remainingDays'));

    }


    public function testRenewal(){
      $today = date('Y-m-d');
      $affectedTenActive = TenantContract::where('tenant_contract_effective_date','<=',$today)
        ->where('tenant_contract_valid_to_date','>',$today)
        ->where('work_flow_processes_code',108)->where('tenant_contract_status',0)
        ->where('tenant_renewal_termination_status',5)
        ->get();

      print_r(json_encode($affectedTenActive));

    }
    /*
    *
    *
    * Renew Contract Cron
    *
    */
    public function cronRenewalContract() {
	  
		
	  $today = date('Y-m-d');
	  
      $myfile = fopen(storage_path('logs/cronLog.txt'), 'a') or die('Unable to open file!');
      $txt = "\n[".date("Y-m-d,h:m:s")."] Last Update\n";
      fwrite($myfile, $txt);
      fclose($myfile);
     	  
      $units = new Unit;
	  // Vacany Loss Table Update
      $vacanyDataList = VacantVacancyLoss::where('status','=',1)->where('vacant_from','<=',date('Y-m-d'))->get();
	  
      foreach($vacanyDataList as $lst){

        $start  = $lst->vacant_from;
        $end    = date('Y-m-d',strtotime('+1 day',strtotime(date('Y-m-d'))));

        $vacantDays =dateDifference($start,$end,'%a');
        $validToDt = date('Y-m-d');
        $vacany_loss = 0;
        if($lst->rent_per_month > 0 && $vacantDays > 0){
         
          $vacany_loss = rentLossCalculationWithDays($vacantDays,$lst->rent_per_month);
        }
        VacantVacancyLoss::where('unit_id','=',$lst->unit_id)->where('status','=',1)->update(['vacant_to'=>$validToDt,'vacant_days'=>$vacantDays,'vacany_loss'=>$vacany_loss]);
      }
	 // Manually Terminate by backoffice team
	/*
      $affectedTenInactive = TenantContract::whereDate('tenant_contract_valid_to_date','<=',$today)
                  ->update(['tenant_contract_status' => 0,'tenant_renewal_termination_status'=>5]);
	 
      $contracts_vaccants = TenantContract::with('Unit')->whereDate('tenant_contract_valid_to_date',$today)->where('tenant_contract_status',0)->get();
     
      foreach($contracts_vaccants as $contract) {
        $contract->Unit->update(['unit_vaccant_status'=>'0']);
		SalesEnquiry::where('id',$contract->sale_enquiry_id)->update(['work_flow_processes_code'=>108]);
      }
     */ 
	$contracts_occupied = array();
	$affectedTenActive = TenantContract::where('tenant_contract_effective_date','<=',$today)
	->where('tenant_contract_valid_to_date','>',$today)
	->where('work_flow_processes_code',108)->where('tenant_contract_status',0)
	->where('tenant_renewal_termination_status',5)
	->get();
         
    foreach($affectedTenActive as $contracts){
        TenantContract::where('tenant_contract_no',$contracts->tenant_contract_old_no)->update(['tenant_contract_status' => 0 ]);
        TenantContract::where('id',$contracts->id)->update(['tenant_contract_status' => 1,'tenant_renewal_termination_status'=>6]);
    }
      $contracts_occupied = TenantContract::with('Unit')
                  ->where('tenant_contract_effective_date','<=',$today)
				  ->where('tenant_contract_valid_to_date','>',$today)
				  ->where('tenant_renewal_termination_status',5)
                  ->where('work_flow_processes_code',108)
                  ->where('tenant_contract_status',1)->get();

      /* 
      *
      *
      *Update Units occupied Status
      *
      */
	
	  if(count($contracts_occupied)>0){
		  foreach($contracts_occupied as $contract) {
			  
			 Unit::where('id',$contract->unit_id)->update(['unit_vaccant_status'=>'1']);
		  }
	  }
	    
      // LandlordContract::whereDate('landlord_contract_valid_to_date',$today)
				  //  ->update(['landlord_contract_status' => 0,'landlord_renewal_termination_status'=>5]);
				
      LandlordContract::whereHas('salesEnquiry', function ($query){
                $query->where('work_flow_processes_code', '=', 204);                     
            })->whereDate('landlord_contract_valid_from_date','<=',$today)
			->whereDate('landlord_contract_valid_to_date','>',$today)
            ->update(['landlord_contract_status' => 1,'landlord_renewal_termination_status'=>6]); 
      $contracts_building = LandlordContract::with('Unit')
                ->whereHas('salesEnquiry', function ($query){
                  $query->where('work_flow_processes_code', '=', 204);                     
                })->whereDate('landlord_contract_valid_to_date',$today)
                ->where('landlord_contract_status',0)->get();

      /* 
      *
      *
      *Update Building Inactive Status
      *
      */
      foreach($contracts_building as $contract) {
        $contract->building->update(['building_status'=>'0']);
      }
      /****** Activity log *****/

           


    }
    /*
    *
    *
    * Renewal Request Serach 
    *
    *
    */
    public function tenantRenewalRequestSearch(Request $request){

      $enquiry_fields = [
           'tenant_contract_no' => 'Contract No',
           'building' => 'Building',
           'unit' => 'Unit',
           /*'startDate' => 'Start Date',     
           'endDate' => 'End Date',*/ 
           'tenant' => 'Tenant',
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
	 
	    $result = array();	
	
  		if(isset($request)){
			   
  			   $result  = 	$this->renewalSearch($request);

      }    
      $request->flash(); 
     
      if(isset($request->route))
        $route   =  $request->route;
    
      $current = Carbon::now(); 
      $futureDate = $current->addMonths(3)->format('Y-m-d H:i:s');
      $dueForRenewals = TenantContract::closure($result)->whereDate('tenant_contract_valid_to_date','<',$futureDate)->where('tenant_contract_status',1)->where('tenant_renewal_termination_status',0)->sortable()->paginate(10);     
       
       
      if(isset($request->ajax))
       return view('backoffice::RenewalOrTermination.tenant_renewal_due_list_ajax',compact('dueForRenewals','request','route'));
       else 
      return view('backoffice::RenewalOrTermination.tenant_renewal_due_list',compact('dueForRenewals','enquiry_fields','operations'));
	
	}
	/*
	*
	* Common Search Results Renewal
	*
	*/
    public function renewalSearch(Request $request){
		 
	    $closure = array();
	    $closure_or = array();
      $building = array();
      $building_or = array(); 
      $unit = array();
      $unit_or = array();
      $tenant = array();
      $tenant_or = array();   
		//dd($request->fieldName);
		if(isset($request->fieldName)){
         if(count($request->fieldName) > 0){
            foreach ($request->fieldName as $key => $value) {
				
			 	if( !empty($request->fieldValue[$key]) && !empty($request->fieldValue[$key]) && !empty($value) ) {
				
             $operation = $request->operation[$key];
             $fieldValue = $request->fieldValue[$key];
             
              if($request->operation[$key] == 'ilike%...%' ){
                $fieldValue = '%'.$request->fieldValue[$key].'%';
                $operation = 'ilike';
              }elseif($value == 'building'){
			   
      			    if($key != 0 && $request->logic[$key -1 ] == 'or' )
      			    $building_or[] = array( $value , $operation ,$fieldValue);
      			    else
      			    $building[] = array( $value , $operation ,$fieldValue);
      			
      			  }elseif($value == 'unit'){
         
                if($key != 0 && $request->logic[$key -1 ] == 'or' )
                $unit_or[] = array( $value , $operation ,$fieldValue);
                else
                $unit[] = array( $value , $operation ,$fieldValue);
            
              }elseif($value == 'tenant'){
         
                if($key != 0 && $request->logic[$key -1 ] == 'or' )
                $tenant_or[] = array( $value , $operation ,$fieldValue);
                else
                $tenant[] = array( $value , $operation ,$fieldValue);
            
              }else{                
                $fieldValue = $request->fieldValue[$key];
                $operation = $request->operation[$key];
              }
         
             if($value != 'building' &&  $value != 'unit' &&  $value != 'tenant' &&   (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
      				 if($key != 0 && $request->logic[$key -1 ] == 'or' )
      				 $closure_or[] = array( $value , $operation ,$fieldValue);
      				 else
      				 $closure[] = array( $value , $operation ,$fieldValue);
      				 }
             
             }
            
            
            }
            
            
          if($request->ajax != true){
           if(count($building_or) == 0 && count($closure) == 0 &&  count($building) == 0 && count($unit) == 0 && count($unit_or) == 0  && count($tenant) == 0  && count($tenant_or) == 0 )
            $closure[] = array( 'id' , '=' ,0);
		  }

         }

     }
   //  dd($closure_date);
     
   
    $contract_no = (isset($request->contract_no)) ? $request->contract_no : null;
    $tenant_contract_old_no = (isset($request->tenant_contract_old_no)) ? $request->tenant_contract_old_no : null;
    $building_id = (isset($request->building_id)) ? $request->building_id : null;
    $unit_id = (isset($request->unit_id)) ? $request->unit_id : null;
    $tenant_contract_start_date = (isset($request->tenant_contract_start_date)) ? $request->tenant_contract_start_date : null;
    $tenant_contract_valid_to_date = (isset($request->tenant_contract_valid_to_date)) ? $request->tenant_contract_valid_to_date : null; 
    $tenant_id = (isset($request->tenant_id)) ? $request->tenant_id : null;    
    $tenant_contract_rent = (isset($request->tenant_contract_rent)) ? $request->tenant_contract_rent : null;  
    $tenant_contract_muncipality_agr_no = (isset($request->tenant_contract_muncipality_agr_no)) ? $request->tenant_contract_muncipality_agr_no : null; 

    $locationId = (isset($request->locationId)) ? $request->locationId : null; 
    $tenant_contact_no = (isset($request->tenant_contact_no)) ? $request->tenant_contact_no : null; 
    $way_no = (isset($request->way_no)) ? $request->way_no : null; 

    $qiuck_search = array($contract_no,$tenant_contract_old_no, $building_id , $unit_id, $tenant_contract_start_date, $tenant_contract_valid_to_date,$tenant_id,$tenant_contract_rent,$tenant_contract_muncipality_agr_no,$locationId,$tenant_contact_no,$way_no);
    if($request->ajax != true){
       
      $qiuck_search = array();
    }
     $result = array(
       $closure,$closure_or,$building, $building_or,$unit, $unit_or, $tenant, $tenant_or,$qiuck_search
     ); 
     //dd($result);
     
     return $result;    
     
     
	}
  /*
  *  Search Form
  * 
  *
  */
  public function tenantSearchFilter(){


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
       'ilike%...%' => ' Like%...% '  ,
      ];

     return view('backoffice::RenewalOrTermination.tenant_filter',compact('enquiry_fields','operations'));

    }
    /*
    *
    *Renewal Approve Reject 
    *
    */
    public function renewalApproveReject(Request $request)
    {
  		$contract_id = $request->new_contract_id;
  		$status = $request->status;
  		$stage = $request->stage;
      $action_key = $request->action_key;

		  return view('backoffice::RenewalOrTermination.tenant_renew_approval_accept_reject_modal',compact('contract_id','status','stage','action_key'));
    }
    /*
    *  Renewal Approve Reject Store Note 
    *
    */
    public function renewalApproveRejectStore(Request $request)
    {
    
     $tenantContract =  TenantContract::where('id',$request->contract_id)->first();

    $renewal =     Renewal::where('old_contract_id',$tenantContract->id)
                            ->where('renewal_type',1)->latest()->first(); 
    $renewal->renewal_notes = $request->renewal_notes;
    $renewal->updated_by           = \Auth::user()->id;
    $renewal->save();

    if($request->stage == 300)
      $status  = 1;

    if($request->stage == 301)
      $status  = 9;

    return  $this->tenantRenewalStage($tenantContract->id,$request->stage,$status,0);


    }
    /*
    *
    *Renewal Approve Reject Store   BK
    *
    */
    public function renewalApproveRejectStoreBK(Request $request)
    {

      $new_contract_id = $request->new_contract_id;
      $newContractDetail = TenantContract::where('id',$new_contract_id)->first();
      $action_key = $request->action_key;
      $oldContractId = TenantContract::where('id',$new_contract_id)->first()->tenant_contract_old_no;

      $oldContract = TenantContract::where('tenant_contract_no',$oldContractId)->first()->id;
      $status = $request->status;
      $stage = $request->stage;
      $renewal_notes = $request->renewal_notes;

     
      // Process Flow Insertion
      $general =  new General;
      $next_process_id = $general->nextProcessFromAction($stage,$action_key);
      if($status != 4) 
      { 
        $PreviousRenewal = Renewal::where('work_flow_processes_code',$next_process_id)
                                    ->where('renewal_type',1)
                                    ->where('old_contract_id',$oldContract)
                                    ->where('new_contract_id',$new_contract_id)
                                    ->orderBy('id','desc')->limit(1)->first();
      
        if(!empty($PreviousRenewal))
        $PreviousRenewal->renewalUser()->update(['status' => 0]);

       $stageRenewal = Renewal::where('work_flow_processes_code',$stage)
                               ->where('renewal_type',1)
                               ->where('old_contract_id',$oldContract)
                               ->where('new_contract_id',$new_contract_id)
                               ->orderBy('id','desc')->limit(1)->first();
      //dd($stageRenewal);
      if(!empty($stageRenewal))
      $stageRenewal->renewalUser()->update(['status' => 0]);

      }
      // current stage update 
      $stageRenewal = Renewal::where('work_flow_processes_code',$stage)->where('renewal_type',1)->where('old_contract_id',$oldContract)->where('new_contract_id',$new_contract_id)->orderBy('id','desc')->limit(1)->first();
      
      if(!empty($stageRenewal))
      $stageRenewal->renewalUser()->update(['status' => 0]);

      /*$PreviousRenewal = Renewal::where('work_flow_processes_code',$next_process_id)->where('renewal_type',1)->where('old_contract_id',$oldContract)->where('new_contract_id',$new_contract_id)->orderBy('id','desc')->limit(1)->first();
      
        if(!empty($PreviousRenewal))
        $PreviousRenewal->renewalUser()->update(['status' => 0]);*/


      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);

      $renewal = new Renewal; 
      $renewal->old_contract_id     = $oldContract; 
      $renewal->new_contract_id     = $new_contract_id; 
      $renewal->renewal_type     = 1;
      $renewal->renewal_notes = $renewal_notes;
      if($status == 4){$renewal->work_flow_processes_code = $stage;}else{ $renewal->work_flow_processes_code = $next_process_id;}
      $renewal->created_by           = \Auth::user()->id;
      $renewal->save();

      if($processAssign != false) {

        foreach($processAssign->assign as $val){
           $renewal->renewalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
           $renewal->save();
        }
        $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
        $renewal->save();
    
      }else{

        $previousProcess = $general->getPreviousOrder($next_process_id);

        if($previousProcess !=0){

          $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

          foreach($previousAssign->assign as $val){
            $renewal->renewalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
            $renewal->save();
          }
          $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
          $renewal->save();

        }else{

          $workFlowProcess = $general->workFlowProcess($next_process_id);
          $renewal->renewalUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
          $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]); 
          $renewal->save();
                  
          
        }
      }
      $tenantContract =  TenantContract::where('id',$new_contract_id)->update(['tenant_renewal_termination_status'=>$status]);
      /*Notification Of users*/
      $users = User::role(['backoffice_executive'])->get(); 
      $users = array_flatten($users); 

      if($status == 4){

        $OldtenantContract =  TenantContract::where('id',$oldContract)->update(['tenant_renewal_termination_status'=>$status]);
        //Notification
        $newContractDetail->href = url('contract/'.$newContractDetail->id.'/'.$next_process_id.'/newContractShow/');
        event(new TenantRenewalReject($newContractDetail,$users)); 
        session()->flash('success', 'Tenant Renewal Contract Rejected Successfully');
      }else{

        $OldtenantContract =  TenantContract::where('id',$oldContract)->update(['tenant_renewal_termination_status'=>6,'work_flow_processes_code'=>108]);
        
        //Notification
        $newContractDetail->href = url('renewedContract/'.$newContractDetail->id);
        event(new TenantRenewalApprove($newContractDetail,$users)); 
        session()->flash('success', 'Tenant Renewal Contract Approve Successfully');
      }
      return redirect()->route('renewalContractApproval');

    
    }
    /*
    *
    *
    * renewedContract list
    *
    */
    public function renewedContract(Request $request)
    {
   // echo "ST";exit();
      $enquiry_fields = [
           'old_contract' => 'Old Contract No',
           'new_contract' => 'New Contract No',
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
      
      $quick_url = $route = route('tenantRenewedContract');

      if(isset($request->type)){
        $rentReductionCount =  DB::table('renewals')
         ->Join('renewal_notes','renewals.id','=','renewal_notes.renewal_id')
        ->Join('contract_renewal_types','renewal_notes.contract_renewal_type_id','=','contract_renewal_types.id')
        ->where('contract_renewal_types.id','3')
        ->select('renewal_notes.contract_renewal_type_id','contract_renewal_types.type','renewals.id')->pluck('renewals.id')->toArray();
        $tenantRenewals = ViewRenewalUser::notRegisteredInMunicipality($request)->select('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_no','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
            ->groupBy('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_no','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')->filter($request)->whereIn('renewal_id',$rentReductionCount)->areFilter()->sortable()->paginate($this->noOfRecord);

            foreach( $tenantRenewals as &$eachRen){
              if($eachRen->new_contract_id == ''){
               $eachRen->new_contract_id = $eachRen->old_contract_id; 
              }

            }
        // echo "<pre>";print_r($tenantRenewal);exit;
      }
      else{
        $tenantRenewals = ViewRenewalUser::notRegisteredInMunicipality($request)->select('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_no','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')
            ->groupBy('renewal_id','new_contract_id','old_contract_id','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','tenant_name','unit_no','id','tenant_contract_no','tenant_contract_start_date','tenant_contract_valid_to_date','tenant_contract_rent','tenant_contract_status','tenant_contract_muncipality_agr_no','tenant_renewal_termination_status','tenant_contract_old_no','new_contract','old_contract')->where('work_flow_processes_code', '=', 303)->where('tenant_contract_status', 0)->where('tenant_renewal_termination_status',5)->where('user_status',1)->filter($request)->areFilter()->sortable()->paginate($this->noOfRecord);
      }
      // echo "<pre>";print_r($tenantRenewal);exit;
      // echo "<pre>";print_r($tenantRenewal);exit;
              /*if (in_array('super_admin', $rolesNames) === false) {
               
                                $tenantRenewal->where(function ($query) use($roles){
                                        $query->where('user_id',null)
                                              ->whereIn('role_id', $roles);
                                      })
                                      ->orWhere(function ($query) use($roles){
                                        $query->where('user_id','>',0)
                                              ->whereIn('role_id', $roles)
                                              ->where('user_id','=', \Auth::user()->id);
                                      })                      
                                      ->where('status','=',1);                       
                          
          }*/
      /*
      $tenantRenewal = Renewal::
      whereHas('newTenantContract', function ($query) use($rolesNames,$request){
                       $query->where('tenant_contract_status',0)
                            ->where('tenant_renewal_termination_status',5)
                            ->filter($request)
                            ->sortable();                                         
                      })->whereHas('renewalUsers', function ($query) {
                        $query->where('status','=',1);
                    })->oldContract($request)                   
              ->where('work_flow_processes_code', '=', 303);

                    if (in_array('super_admin', $rolesNames) === false) {
                          $tenantRenewal->whereHas('renewalUsers', function ($query) use($roles) {
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
    */
    // $tenantRenewals = $tenantRenewal->paginate($this->noOfRecord);
    // echo "<pre>";print_r($tenantRenewals[0]->newTenantContract);exit;

    if(isset($request->ajax))
    return view('backoffice::RenewalOrTermination.tenant_renewed_contract_list_ajax',compact('tenantRenewals','request','route'));

    return view('backoffice::RenewalOrTermination.tenant_renewed_contract_list',compact('tenantRenewals','enquiry_fields','operations','quick_url'));
    }
    /*
    *
    *
    * renewedContractShow
    *
    */
    public function renewedContractShow($id)
    {
      clearNotification('Modules\BackOffice\Notifications\TenantRenewalNotification',$id);
      readNotification('Modules\BackOffice\Notifications\TenantRenewalNotification',$id); 
      $tenantContract = TenantContract::where('id',$id)->first();
      $oldContractId = $tenantContract->tenant_contract_old_no;
      //dd($oldContractId);

      
	  $remainingDays	= 0 ;
      if(!empty($oldContractId)){
        $oldContractDetail = TenantContract::where('tenant_contract_no',$oldContractId)->first();
            }
        $allNotes = Renewal::where('new_contract_id',$id)->get();


        $renewalNotes = RenewalNote::whereHas('renewal', function ($query) use($oldContractDetail) {
                                      $query->where('old_contract_id', '=', $oldContractDetail->id)
                                      ->where('renewal_type',1);                     
                                    })->get();

        $stageNotes = Renewal::where('new_contract_id', '=', $id)->where('renewal_type',1)
                             ->where('renewal_notes','!=','')
                             ->get();

        $arNotes =  $renewalNotes->where('contract_renewal_type_id','>',0); 
        $renewalNotes =  $renewalNotes->where('contract_renewal_type_id','<=',0);        

        $arNotes = (empty($arNotes)) ?  [] : $arNotes;   
        $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
        $today = strtotime(date('Y-m-d'));

        $datediff = $validTo - $today;
        $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds    

      return view('backoffice::RenewalOrTermination.tenant_renewed_contract_view',compact('tenantContract','allNotes','arNotes','remainingDays'));
    }
    /*
    *  renewedContractEdit
    *
    */
    public function renewedContractEdit(TenantContract $tenant_contract){         

      $allNotes = Renewal::where('new_contract_id',$tenant_contract->id)->get();
	  $remainingDays =0;
      $tenantContract = $tenant_contract;

        $renewalNotes = RenewalNote::whereHas('renewal', function ($query) use($tenant_contract) {
                                      $query->where('old_contract_id', '=', $tenant_contract->id);                     
                                    })->get();

        $stageNotes = Renewal::where('new_contract_id', '=', $tenant_contract->id)
                              ->where('renewal_type',1)
                               ->where('renewal_notes','!=','')->get();

        $arNotes =  $renewalNotes->where('contract_renewal_type_id','>',0); 
        $renewalNotes =  $renewalNotes->where('contract_renewal_type_id','<=',0);        

        $arNotes = (empty($arNotes)) ?  [] : $arNotes;


      return view('backoffice::RenewalOrTermination.tenant_renewed_contract_view',compact('remainingDays','tenantContract','allNotes','arNotes'));
    }
    /*
    *  renewedContractSave
    *
    */
    public function renewedContractSave(Request $request,TenantContract $tenant_contract){

      $tenant_contract->tenant_contract_is_reg_municipality = $request->registered_in_municipality;
      $tenant_contract->save();


    $renewal =     Renewal::where('new_contract_id',$tenant_contract->id)->latest()->first(); 
    $renewal->renewal_notes = $request->renewal_notes;
    $renewal->updated_by           = \Auth::user()->id;
    $renewal->save();
      
      if($request->registered_in_municipality == 1)
      session()->flash('success', 'Tenant Renewal Contract Registered in Municipality');
       
      return redirect()->route('tenantRenewedContract');
    }


    /*
    *
    *  Template /PDF   Model  
    *
    */
    public function renewalTemplatePdf(Request $request){
      
      $renewal_data = NULL;
      $tenantContract = TenantContract::where('id',$request->tenant_contract_id)
                                       ->first();

      if(count($tenantContract->tenantRenewalForm) > 0){
        $form_count = count($tenantContract->tenantRenewalForm);
        $renewal_data = $tenantContract->tenantRenewalForm[$form_count-1];
      }   

      $landloard = LandlordContract::where('building_id',$tenantContract->building_id)
                                      ->active()->first();

      if(isset($landloard->landlord_contract_valid_to_date))
      $landlord_contract_valid_to_date =  date('Y-m-d',strtotime($landloard->landlord_contract_valid_to_date)) ;                               
     else
		  $landlord_contract_valid_to_date = null; 
      $start_date =  date('Y-m-d',strtotime($tenantContract->tenant_contract_valid_to_date. ' +1 day')) ;
      $areEmail= null;
      if(isset($tenantContract->building->buildingAssignToAre->buildingAssignToName->areUser)){

        $areEmail =$tenantContract->building->buildingAssignToAre->buildingAssignToName->areUser->email;
      }                               
       
      //dd($tenantContract->building);
      return view('backoffice::RenewalOrTermination.renewalTemplatePdfModel',compact('tenantContract','renewal_data','landlord_contract_valid_to_date','start_date','areEmail'));                                  
   }


    /*
    *   Template /PDF   Model 
    *
    */
    public function tenantRenewalTemplateStore(Request $request,TenantContract $tenant_contract){

        $tenant_contract->tenantRenewalForm()->create([
            'tenant_contract_rent' => replaceCommaWithDot($request->tenant_contract_rent),
            'tenant_contract_start_date' => $request->tenant_contract_start_date,
            'tenant_contract_valid_to_date' => $request->tenant_contract_valid_to_date,
            'tenant_contract_payment_type' => $request->tenant_contract_payment_type
        ]);

       return $this->generatePDF($tenant_contract);
      // return  redirect()->route('tenantRenewal.index');
    }
    /*
    *
    *
    * generatePDF
    *
    */
    public function generatePDF(TenantContract $tenant_contract)
    {
      $areEmail= null;
      if(isset($tenant_contract->building->buildingAssignToAre->buildingAssignToName->areUser)){

        $areEmail =$tenant_contract->building->buildingAssignToAre->buildingAssignToName->areUser->email;
      }
      //multiple pdf generation
      $tenantContract = $tenant_contract;
      $userDetails = User::where('id',\Auth::user()->id)->first();
      $tenantContract->username = $userDetails->username;
      $tenantContract->email = $userDetails->email;
      $contractDate = $tenantContract->tenant_contract_valid_to_date->format('d/m/Y');
      $data['tenantContract'] = $tenantContract;
      
      $pdf = \PDF::loadView('backoffice::RenewalOrTermination.renewal_contract_form_pdf',$data)->setPaper('a3');
      //->setOption('footer-line',true)
           //->setOption('footer-center',utf8_decode('Page [page] of [topage]'))
      /*->setOption('header-html',view('request.request-header'),true)
      ->setOption('footer-html',view('request.request-footer'),true);*/

       //PDF Name generator
       $prefix       = 'REN';
       $random = str_random(10);
       $nextCode=$prefix.$random;

       $pdfName=$nextCode.'.pdf';
        //Email Notification
        $tenantDetail = Tenant::where('id',$tenantContract->tenant_id)->first();
        $tenantEmail = $tenantDetail->tenant_contact_email;
        $tenantDetail->pdf = $pdf;
        $tenantDetail->pdfname = $pdfName; 
        $tenantDetail->todate = $contractDate;
        $tenantDetail->building = $tenantContract->building->building_name;
        $tenantDetail->unitno = $tenantContract->Unit->unit_no;
        
        if($tenantEmail == "")$tenantEmail = $tenantDetail->tenant_personal_email;

        if(!empty($tenantEmail)){

          Mail::to($tenantEmail)
                ->cc([$userDetails->email,$areEmail])
                ->send(new RenewalFormPdfEmail($tenantDetail));
          TenantContract::where('id',$tenantContract->id)->update(['email_count'=>$tenantContract->email_count+1]);
          session()->flash('success', ' Mail Send Successfully');
          return redirect()->route('tenantRenewal.index');
        }else{
          return  $pdf->download($pdfName);
        }

               
    }
	/**
     * 
     * @param int $id
     * @return Response
     */
    public function pdcGeneration($segment1,$segment2,$contract_id)
    {
        $tenantContract = TenantContract::where('id',$contract_id)->first();
        $params = array($segment1,$segment2,$contract_id);
        $pdc_transaction_date =null;
    
        if($segment1=='tenantRenewal')
            $breadcrumbParent = 'tenantRenewalContract'; // Breadcrum Key name
        else
            $breadcrumbParent = 'renewedContract'; // Breadcrum Key name

        // In Renewed Contract no sales enquiryID And Workflow only 108. 9- Approved, 5 - Renew contract
        if(in_array($tenantContract->tenant_renewal_termination_status,[9,5])){

            $tenantPdcLatest = Pdc::orderBy('id', 'desc')->first();
            
            $tenantPdcInfo  = Pdc::where('tenant_contract_id', $contract_id)->orderBy('id', 'asc')->get();
            if(count($tenantPdcInfo) > 0)
                $pdc_transaction_date = $tenantPdcInfo[0]->pdc_transaction_date;

            $bankMaster = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();

            $prefix  = prefixData('tenant_pdc_prefix')->configuration_value;
            
            if(!empty($tenantPdcLatest))
                $pdcTransacNo = $prefix.str_pad($tenantPdcLatest->id+1,4,'0',STR_PAD_LEFT);
            else
                $pdcTransacNo = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);

            return view('backoffice::RenewalOrTermination.contract_pdc',compact('tenantContract','pdcTransacNo','bankMaster','tenantPdcInfo','pdc_transaction_date','breadcrumbParent','params'));

        }
        else{
           // Contract in-progress
           return redirect()->back();
       }


   }
   public function invoiceGeneration($segment1,$segment2,$contract_id){
    $params = array($segment1,$segment2,$contract_id);
    $tenantContract = TenantContract::where('id',$contract_id)->orderBy('id', 'desc')->first();
      $acc_code_dr    = null;
    $acc_code_cr  = null;

    if($segment1=='tenantRenewal')
            $breadcrumbParent = 'tenantRenewalContract'; // Breadcrum Key name
        else
            $breadcrumbParent = 'renewedContract'; // Breadcrum Key name


      if($tenantContract->invoice_check==0){
        $invoice =  new Invoices;

        $invoiceList    = $invoice->contractInvoiceCalculation($tenantContract->tenant_contract_effective_date, $tenantContract->tenant_contract_valid_to_date, $tenantContract->tenant_contract_rent);

       // dd($invoiceList);

        $acc_parameter  = AccountParams::where('acc_params_tran_desc','customer_rent_invoice')->first();

            // Debit Amount
        //if($acc_parameter->acc_params_dr_acc)
        //  $acc_code_dr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_dr_acc)->first();
       
            // Credit Amount
        if($acc_parameter->acc_params_cr_acc)
          $acc_code_cr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_cr_acc)->first();
       
            // Agreement Invoice insertion as loop
    //$prefix  = prefixData('tenant_invoice_prefix')->configuration_value;
        foreach($invoiceList as $loopItem){

         
         $invoiceId = Invoice::create(['tenant_contract_id' => $contract_id,
                                     'tenant_invoice_type' => 1, // Rent
                                     'tenant_invoice_no' => $loopItem['inv_no'],
                                     'tenant_invoice_date' => $loopItem['inv_date'],
                                     'tenant_invoice_amt' => $loopItem['rent'],
                                     'ax_batch_id' => null,
                                     'ax_invoice_no' => null,
                                     'tenant_invoice_posted_date' => null,
                                     'tenant_invoice_posted_by' => null,
                                     'tenant_invoice_cancelled_date' => null,
                                     'tenant_invoice_desc' => $tenantContract->building->building_code.'@'.$tenantContract->unit->unit_no.'-'.$loopItem['month'],  
                                     'tenant_invoice_status' => 1, // Active
                                     'created_by' => \Auth::user()->id,
                                     
                                     ]);        
     
     
               //  $invoiceNo = str_pad($invoiceId->id,4,'0',STR_PAD_LEFT);
                // $invoiceId->update(['tenant_invoice_no' => $prefix.$invoiceNo]);
                 //$invoiceId->save();


    if($acc_code_dr !=null){
      // Debit Insertation
      TenantInvoiceDimension::create(['invoice_id' => $invoiceId->id,
        'dim1' =>($acc_code_dr->dim1)?$acc_code_dr->dim1:'',
        'dim2' =>($acc_code_dr->dim2)?$acc_code_dr->dim2:'',
        'dim3' =>($acc_code_dr->dim3)?$acc_code_dr->dim3:'',
        'dim4' =>($acc_code_dr->dim4)?$acc_code_dr->dim4:'',
        'dim5' =>($acc_code_dr->dim5)?$acc_code_dr->dim5:'',
        'debit_amount' =>$loopItem['rent'],
        'credit_amount' =>0,
        'ac_codes_id' =>$acc_parameter->id,
        'acc_code_no' =>$acc_parameter->acc_params_dr_acc,
        'acc_code_desc'=>$acc_code_dr->acc_code_desc,
        'dimension_type' =>($acc_parameter->acc_params_dr_type)?trim($acc_parameter->acc_params_dr_type):'',
        'created_by' => \Auth::user()->id,
      ]);
    }
   
    if($acc_code_cr !=null){
                    // Credit Insertation
      TenantInvoiceDimension::create(['invoice_id' => $invoiceId->id,
        'dim1' =>($acc_code_cr->dim1)?$acc_code_cr->dim1:'',
        'dim2' =>($acc_code_cr->dim2)?$acc_code_cr->dim2:'',
        'dim3' =>($acc_code_cr->dim3)?$acc_code_cr->dim3:'',
        'dim4' =>($acc_code_cr->dim4)?$acc_code_cr->dim4:'',
        'dim5' =>($acc_code_cr->dim5)?$acc_code_cr->dim5:'',
        'debit_amount' =>0,
        'credit_amount' =>$loopItem['rent'],
        'ac_codes_id' =>$acc_parameter->id,
        'acc_code_no' =>$acc_parameter->acc_params_cr_acc,
        'acc_code_desc'=>$acc_code_cr->acc_code_desc,
        'dimension_type' =>($acc_parameter->acc_params_cr_type)?trim($acc_parameter->acc_params_cr_type):'',
        'created_by' => \Auth::user()->id,
      ]);
    }
       }



       TenantContract::where('id',$contract_id)->update(['invoice_check' => 1]);
     }

     $invoiceList = Invoice::where('tenant_contract_id',$contract_id)->get();

      return view('backoffice::RenewalOrTermination.contract_invoices',compact('tenantContract','invoiceList','breadcrumbParent','params'));
   }
   /**
     * Before Renew Contract create to view Previous Contract.
     */
    public function previousContractViewAndAction($id)
    {
      $tenantContract = TenantContract::where('id',$id)->first();
      $remainingDays  = 0 ;
      $renewalNotes = RenewalNote::whereHas('renewal', function ($query) use($id) {
                $query->where('old_contract_id', '=', $id)
                ->where('renewal_type',1);                      
            })->get();

      $stageNotes = Renewal::where('old_contract_id', '=', $id)
                               ->where('renewal_type',1)
                               ->where('renewal_notes','!=','')->get();


        $arNotes =  $renewalNotes->where('contract_renewal_type_id','>',0); 
        $renewalNotes =  $renewalNotes->where('contract_renewal_type_id','<=',0);


        $validTo =  strtotime($tenantContract->tenant_contract_valid_to_date); 
     $today = strtotime(date('Y-m-d'));

     $datediff = $validTo - $today;
     $remainingDays = $datediff / 86400;    // 24 * 60 * 60 = 86400 seconds   



      return view('backoffice::RenewalOrTermination.tenant_previous_contract_view',compact('tenantContract','arNotes','renewalNotes','stageNotes','remainingDays'));
    }
	public function tenantContractCode(){

      $prefix  = prefixData('tenant_agreement_prefix')->configuration_value.prefixData('tenant_agreement_prefix')->configuration_year;
     
      $incVal  = prefixData('tenant_agreement_prefix')->configuration_increment_value;
     
      $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);
     
      return array('code'=>$nextCode,'inc'=>$incVal);

    }

}
