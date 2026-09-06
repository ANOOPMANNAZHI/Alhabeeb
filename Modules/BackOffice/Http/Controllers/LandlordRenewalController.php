<?php

namespace Modules\BackOffice\Http\Controllers;

use Modules\BackOffice\Entities\RenewalOrTermination;
use Modules\BackOffice\Entities\Renewal;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Entities\ViewLandlordRenewalUser;
use Modules\BackOffice\Entities\RenewalNote;
use Modules\Sales\Entities\LandlordContract;
use Modules\Masters\Entities\Vendor;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\ManagementType;
use Modules\Masters\Entities\Employee;
use Modules\Masters\Entities\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Setting;
use Carbon\Carbon;
use DB;
use Session;
use URL;
use Route;
use App\User;
use Modules\BackOffice\Events\LandlordRenewalApprove;
use Modules\BackOffice\Events\LandlordRenewalReject;
use Modules\BackOffice\Events\LandlordDueForRenewal;
use Modules\General\Http\Controllers\GeneralController as General;

class LandlordRenewalController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');  
    $this->middleware('permission:renewal_due_list', ['only' => ['landlordRenewal']]);
    $this->middleware('permission:landlord_renewal_request_add', ['only' => ['create','store']]);
    $this->middleware('permission:landlord_renewal_request_edit', ['only' => ['edit','update']]); 
    $this->middleware('permission:landlord_renewal_request_delete', ['only' => ['destroy']]); 
    $this->middleware('permission:landlord_renewal_contract_list', ['only' => ['renewalContract','landlordNewContractShow']]);
    $this->middleware('permission:landlord_renewal_contract_add', ['only' => ['renewalContractStore']]);
    $this->middleware('permission:landlord_renewal_contract_edit', ['only' => ['renewalContractEdit','renewalContractUpdate']]);
    $this->middleware('permission:landlord_renewal_contract_approval_list', ['only' => ['landlordContractApproval']]);  

    $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value;     
  }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {   
      $name = Route::currentRouteName();

      $enquiry_fields = [
      'landlord_contract_no' => 'Contract No',
      'buildingInfo__building_name' => 'Building',
      'vendorInfo__vendor_name' => 'Landlord',
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
      
      $request->flash(); 
      
      //Get Configuration Month from Settings
      $config_data = Setting::where('configuration_settings','renewal_notification')
                              ->first()->configuration_value;

      $current = Carbon::now(); 
      $futureDate = $current->addMonths($config_data)->format('Y-m-d');

      $renewalDues = LandlordContract::filter($request)
                                      ->where('landlord_contract_status',1)
                                      ->where('landlord_contract_duration',2)
                                      ->where('landlord_contract_valid_to_date','<=',now()->addDays(90)->endOfDay())
                                      ->sortable()
                                      ->paginate($this->noOfRecord);
// ----------------------------------------Uncomment if any error is coming------------------------------------------------------------------
//      // $renewalDues = LandlordContract::filter($request)
//      //                                  ->where('landlord_contract_status',1)
//      //                                  ->where('landlord_renewal_termination_status',0)
//      //                                  ->where('landlord_contract_duration',2)
//      //                                  ->where('landlord_contract_valid_to_date','<=',now()->addDays(90)->endOfDay())
//      //                                  ->sortable()
//      //                                  ->paginate($this->noOfRecord);

// ------------------------------------------------------------------------------------------
      
     $quick_url =    $route   =  route('landlordRenewal.index');
      if(isset($request->ajax))
        return view('backoffice::RenewalOrTermination.landlord_renewal_due_list_ajax',compact('renewalDues','request','route'));

      return view('backoffice::RenewalOrTermination.landlord_renewal_due_list',compact('renewalDues','request','enquiry_fields','operations','name','quick_url'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($landlord_contract_id)
    {
      $renewalDue =  LandlordContract::where('id',$landlord_contract_id)->first();
      //dd($renewalDue);
      return view('backoffice::RenewalOrTermination.landlord_renewal_due_view',compact('renewalDue'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(RenewalOrTermination $landlordRenewal)
    {

    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,RenewalOrTermination $landlordRenewal)
    {

    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(RenewalOrTermination $landlordRenewal)
    {

    }
    /*
    *
    * Accept or terminate / Reject
    *
    *
    */
    public function landlordRenewalStage(Request $request)
    {  
     $currentUrl =  Session::get('current'); 
     $process_flow =$request['process_flow'];
     $landlord_contract_id = $request['landlord_contract_id'];
     $landlord_old_contract_id = $request['landlord_old_contract_id'];
     $status = $request['status'];
     //dd($status);
     $action_key = $request['action_key'];
        //dd($action_key);
     $general =  new General;
      $next_process_id = $process_flow;//401,601
      //dd($next_process_id);
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);
      if(($next_process_id==401)||($next_process_id==601)){
        if($action_key == 'TMT'){
          $type='Terminated';
          $terminations = new Termination; 
          $terminations->contract_id     = $landlord_contract_id;
          $terminations->termination_type  = 2;
          $terminations->work_flow_processes_code = $next_process_id;
          $terminations->created_by           = \Auth::user()->id;
          $terminations->save();
          // Update status to Landlord Contract
          LandlordContract::where('id',$landlord_contract_id)->update(['landlord_renewal_termination_status'=>$status]);
          if($processAssign != false) {

            foreach($processAssign->assign as $val){
             $terminations->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
             $terminations->save();
           }
           $terminations->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
           $terminations->save();

         }else{

          $previousProcess = $general->getPreviousOrder($next_process_id);

          if($previousProcess !=0){

            $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 

            foreach($previousAssign->assign as $val){
              $terminations->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
              $terminations->save();
            }
            $terminations->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
            $terminations->save();

          }else{

            $workFlowProcess = $general->workFlowProcess($next_process_id);
            $terminations->terminationUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
            $terminations->save();


          }
        }
      }
      else{
  //insert Renewal
        //termination Update
        $previousProcess = Termination::where('contract_id',$landlord_contract_id)->where('termination_type',2)->orderBy('id','desc')->latest()->first();//dd($previousFlow);
        if($previousProcess)$previousProcess->terminationUser()->update(['status' => 0]);
        //renewal Update
        $previousProces = Renewal::where('work_flow_processes_code',$next_process_id)->where('old_contract_id',$landlord_contract_id)->where('renewal_type',2)->orderBy('id','desc')->latest()->first();//dd($previousFlow);
        if($previousProces)$previousProces->renewalUser()->update(['status' => 0]);

        $type='Approved';
        $renewal = new Renewal; 
        $renewal->renewal_type = 2;
        $renewal->work_flow_processes_code = $next_process_id;
        $renewal->old_contract_id = $landlord_contract_id;
        $renewal->created_by           = \Auth::user()->id;
        $renewal->save();
          // Update status to Landlord Contract
        LandlordContract::where('id',$landlord_contract_id)->update(['landlord_renewal_termination_status'=>$status]);
        if($processAssign != false) {

          foreach($processAssign->assign as $val){
           $renewal->renewalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
           $renewal->save();
         }
         $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
         $renewal->save();

       }
       else{

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
          $renewal->renewalUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->user_id]);
          $renewal->save();


        }
      }
    }
  }
  session()->flash('success', 'Landlord Renewal '.$type.' Successfully');
  if(empty($currentUrl)){
    return response()->JSON(route('landlordRenewal.index'));
  }else{
    return response()->JSON(route($currentUrl));
  }
   
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
  $name = Route::currentRouteName();
  $enquiry_fields = [
    'old_contract' => 'Old Contract No',
    'new_contract' => 'Contract No',
    'building_name' => 'Building',
    'vendor_name' => 'Landlord',
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

  $roles = \Auth::user()->getRoles();
  $rolesNames = \Auth::user()->getRoleNames()->toArray();
 
  $request->flash(); 
  
 $contractRenewals = ViewLandlordRenewalUser::select('new_contract','old_contract','renewal_id','new_contract_id','old_contract_id','building_name','vendor_name','landlord_contract_no','landlord_contract_valid_from_date','landlord_contract_valid_to_date','landlord_contract_amt')
            ->groupBy('new_contract','old_contract','renewal_id','new_contract_id','old_contract_id','building_name','vendor_name','landlord_contract_no','landlord_contract_valid_from_date','landlord_contract_valid_to_date','landlord_contract_amt')->whereIn('landlord_renewal_termination_status',[1,2,3,4])->where('work_flow_processes_code', '=', 401)->where('user_status', '=', 1)->filter($request);
    if (in_array('super_admin', $rolesNames) === false) {
              
              $contractRenewals->renewalUsers();                       
                        
      }
  $contractRenewals = $contractRenewals->sortable()->paginate($this->noOfRecord);
  
  $quick_url = $route = route('renewalContract');

  //dd($contractRenewals);
  if(isset($request->ajax))

    return view('backoffice::RenewalOrTermination.landlord_renewal_contract_list_ajax',compact('contractRenewals','request','route'));

    return view('backoffice::RenewalOrTermination.landlord_renewal_contract_list',compact('contractRenewals','request','enquiry_fields','operations','name','quick_url'));
}
    /**
     * get all requested data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getContractData()
    {


      $contractData['building_id'] = request('building_id');
      $contractData['vendor_id'] = request('vendor_id');
      $contractData['sale_enquiry_id'] = request('sale_enquiry_id');
      $contractData['landlord_contract_name'] = request('vendor_name'); 
      $contractData['landlord_contract_no'] = request('landlord_contract_no'); 
      $contractData['landlord_contract_old_no'] = request('landlord_contract_old_no'); 
      $contractData['landlord_contract_address'] = request('landlord_contract_address');       
      $contractData['landlord_contract_duration'] = request('landlord_duration'); 
      $contractData['landlord_contract_duration_type'] = 1;
      $contractData['landlord_contract_management_fee'] = request('landlord_contract_management_fee');
      $contractData['landlord_contract_percentage'] = request('landlord_contract_percentage');
      $contractData['landlord_contract_cleaning_charge'] = request('landlord_contract_cleaning_charge');
      $contractData['landlord_contract_agreement_amt'] = null;
      $contractData['landlord_contract_amt'] = request('landlord_contract_amt');
      $contractData['management_id'] = request('landlord_contract_payment_type');
      $contractData['landlord_free_lease_period'] = request('landlord_free_lease_period');
      $contractData['landlord_duration'] = null;
      $contractData['landlord_marketing_executive'] = request('landlord_marketing_executive');
      $contractData['user_id'] = \Auth::user()->id;
      $contractData['landlord_contract_valid_from_date'] = request('landlord_contract_valid_from_date');
      $contractData['landlord_contract_valid_to_date'] = request('landlord_contract_valid_to_date');
      $contractData['landlord_contract_payment_type'] = request('landlord_contract_payment_type');
      $contractData['landlord_contract_status'] = 0;
      $contractData['landlord_contract_note'] = request('landlord_contract_note');
      $contractData['start_date'] = request('start_date');
      $contractData['close_activity'] = request('close_activity');
      $contractData['termination_notification'] = request('termination_notification');


      return $contractData;
    }
    /*
    *
    *
    *Renewal Contract Edit
    *
    */
    public function renewalContractEdit($id,$status)
    {   

      $landlordContract = LandlordContract::where('id',$id)->first();

      $vendorsList     = Vendor::latest()->get();
      $buildingList     = Building::latest()->get();
      $managementType   = ManagementType::latest()->get();
//dd($managementType);
      $employeeList     = Employee::orderBy('id', 'DESC')->get();

      $paymentmethodList  = PaymentMethod::latest()->get();
      $landlordContract = LandlordContract::where('id',$id)->first();
//dd($landlordContract);

      return view('backoffice::RenewalOrTermination.landlord_renew_contract_edit',compact('landlordContract','vendorsList','buildingList','managementType','paymentmethodList','employeeList','status'));
    }
    /*
    *
    *
    *Renewal Contract Update
    *
    */
    public function renewalContractUpdate(Request $request)
    {
      $this->validate($request, [
        'building_id'           => 'required',
        'vendor_id'             => 'required',
        ]);
      $data = $this->getContractData();
      $data['updated_by'] = \Auth::user()->id; 
        //dd($request['landlord_contract_id']);
      $status = $request['status'];
      $landlordContract = LandlordContract::where('id',$request['landlord_contract_id'])->update($data);

      session()->flash('success', 'Renewal contract successfully updated');
      /****** Activity log *****/
      activity('Landlord Renewal Contract Updated')
      ->causedBy(\Auth::user()->id)
      ->withProperties($landlordContract)
      ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
      if($status == 1){
        return redirect()->route('renewalContract');
      }else {
        return redirect()->route('landlordContractApproval');
      }

    }
    /*
    *
    * New Contract Details View
    *
    *
    */
    public function landlordNewContractShow($id,$req_id,$status)
    {

      $renewContract = LandlordContract::where('id',$id)->first();
      $requestDetails = RenewalOrTermination::where('id',$req_id)->first();

      return view('backoffice::RenewalOrTermination.landlord_renew_contract_view',compact('renewContract','req_id','status','requestDetails'));
    } 
    /*
    *
    *
    * Renewal Contract Approval Stage List
    *
    *
    */
    public function landlordContractApproval(Request $request)
    {  
     $name = Route::currentRouteName();
     $enquiry_fields = [
     'old_contract' => 'Old Contract No',
     'new_contract' => 'Contract No',
     'building_name' => 'Building',
     'vendor_name' => 'Landlord',
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
     $roles = \Auth::user()->getRoles();
     $rolesNames = \Auth::user()->getRoleNames()->toArray();
   
     $request->flash();
   
   
     $quick_url =   $route = route('landlordContractApproval');

     $contractApprovals = ViewLandlordRenewalUser::select('renewal_id','new_contract','old_contract','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','vendor_name','id','landlord_contract_no','landlord_contract_valid_from_date','landlord_contract_valid_to_date','landlord_contract_amt','landlord_contract_status','landlord_renewal_termination_status','old_contract_id','new_contract_id')
            ->groupBy('renewal_id','new_contract','old_contract','renewal_type','work_flow_processes_code','status','renewal_notes','building_name','vendor_name','id','landlord_contract_no','landlord_contract_valid_from_date','landlord_contract_valid_to_date','landlord_contract_amt','landlord_contract_status','landlord_renewal_termination_status','old_contract_id','new_contract_id')
            ->where('landlord_renewal_termination_status',3)->where('work_flow_processes_code', '=', 402)->where('status',1)->where('user_status', '=', 1)->renewalUsers()->filter($request);

    /* if (in_array('super_admin', $rolesNames) === false) {
             
              $contractApprovals->where(function ($query) use($roles){
                        $query->where('user_id',null)->whereIn('role_id', $roles);
                        })->orWhere(function ($query) use($roles){
                            $query->where('user_id','>',0)
                            ->whereIn('role_id', $roles)
                            ->where('user_id','=', \Auth::user()->id)
                            ->where('work_flow_processes_code',402);
                          });                      
                       
     } */            
     $contractApprovals = $contractApprovals->sortable()->paginate($this->noOfRecord);
     
    //  dd($contractApprovals);
   


    if(isset($request->ajax))
    return view('backoffice::RenewalOrTermination.landlord_renewal_approval_list_ajax',compact('contractApprovals','request','route'));

    return view('backoffice::RenewalOrTermination.landlord_renewal_approval_list',compact('contractApprovals','request','enquiry_fields','operations','name','quick_url'));
  }
  /*****************************Reshma*****************************************/
        /*
    *
    *
    * Renewal Due
    *
    *
    */
        public function renewalDue() 
        {
          $name = Route::currentRouteName();
          $enquiry_fields = [
          'landlord_contract_no' => 'Contract No',
          'building_name' => 'Building',
          'vendor_name' => 'Landlord',
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
          $result = array();

          if(isset($request)){

            $renewalDue =   new LandlordRenewalController;       
            $result =     $renewalDue->landlordDueSearch($request); 
            $request->flash(); 

          }

          $renewalDues = LandlordContract::closure($result)->sortable()->paginate(10);
          if(isset($request->route))
            $route   =  $request->route;

          if(isset($request->ajax))

            return view('backoffice::RenewalOrTermination.landlord_renewal_due_list_ajax',compact('renewalDues','request','route'));

          return view('backoffice::RenewalOrTermination.landlord_renewal_due_list',compact('renewalDues','request','enquiry_fields','operations','name'));
        }
    //Advance search
        public function landlordDueSearch(Request $request){

          $closure = array();
          $closure_or = array();
          $building_q = array();
          $building_or = array(); 
          $vendor_q = array();
          $vendor_or = array();  

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
              }if($value == 'building_name'){

                if($key != 0 && $request->logic[$key -1 ] == 'or' )
                  $building_or[] = array( $value , $operation ,$fieldValue);
                else
                  $building_q[] = array( $value , $operation ,$fieldValue);

              }elseif($value == 'vendor_name'){

                if($key != 0 && $request->logic[$key -1 ] == 'or' )
                  $vendor_or[] = array( $value , $operation ,$fieldValue);
                else
                  $vendor_q[] = array( $value , $operation ,$fieldValue);

              }
              else{                
                $fieldValue = $request->fieldValue[$key];
                $operation = $request->operation[$key];
              }

              if($value != 'building_name' && $value != 'vendor_name' &&  (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
               if($key != 0 && $request->logic[$key -1 ] == 'or' )
                 $closure_or[] = array( $value , $operation ,$fieldValue);
               else
                 $closure[] = array( $value , $operation ,$fieldValue);
             }

           }


         }


         if($request->ajax != true){
           if(count($building_or) == 0 && count($closure) == 0 &&  count($building_q) == 0 &&  count($vendor_or) == 0 &&  count($vendor_q) == 0)
            $closure[] = array( 'id' , '=' ,0);
        }

      }

    }

    $landlord_contract_old_no = (isset($request->landlord_contract_old_no)) ? $request->landlord_contract_old_no : null;
    $landlord_contract_no = (isset($request->landlord_contract_no)) ? $request->landlord_contract_no : null;
    $landlord_contract_valid_from_date = (isset($request->landlord_contract_valid_from_date)) ? $request->landlord_contract_valid_from_date : null;
    $landlord_contract_valid_to_date = (isset($request->landlord_contract_valid_to_date)) ? $request->landlord_contract_valid_to_date : null;
    
    $building_id = (isset($request->building_id)) ? $request->building_id : null;    
    $vendor_id = (isset($request->vendor_id)) ? $request->vendor_id : null;    
    //$landlord_contract_name = (isset($request->landlord_contract_name)) ? $request->landlord_contract_name : null; 
    $landlord_contract_amt = (isset($request->landlord_contract_amt)) ? $request->landlord_contract_amt : null;    

    $qiuck_search = array($landlord_contract_old_no,$landlord_contract_no , $building_id, $vendor_id,$landlord_contract_amt,$landlord_contract_valid_from_date,$landlord_contract_valid_to_date);
    if($request->ajax != true){
  //$qiuck_search = array();
    }
    $result = array(
      $closure,
      $closure_or, $building_q,$building_or,$vendor_q,$vendor_or,$qiuck_search ); 
   //dd($vendor_q);
    return $result;    


  }
       /*
    * Enquiry Search Form
    * 
    *
    */
       public function renewalDueFilter(){


        $enquiry_fields = [
        'landlord_contract_no' => 'Contract No',
        'building_name' => 'Building',
        'vendor_name' => 'Landlord',
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

        return view('backoffice::RenewalOrTermination.landlord_due_filter',compact('enquiry_fields','operations'));


      }
          //Advance search-Landlord Renewal Due
      public function dueSearch(Request $request,$result = array())
      {
        $name = Route::currentRouteName();
        $enquiry_fields = [
        'landlord_contract_no' => 'Contract No',
        'building_name' => 'Building',
        'vendor_name' => 'Landlord',
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
        $result = array();

        if(isset($request)){

          $renewalDue =   new LandlordRenewalController;       
          $result =     $renewalDue->landlordDueSearch($request); 
          $request->flash(); 

        }
        $current = Carbon::now(); 
        $futureDate = $current->addMonths(3)->format('Y-m-d H:i:s');
        $renewalDues = LandlordContract::closure($result)->whereDate('landlord_contract_valid_to_date','<',$futureDate)->where('landlord_contract_status',1)->where('landlord_renewal_termination_status',0)->where('landlord_contract_duration',2)->sortable()->paginate(10);
        if(isset($request->route))
          $route   =  $request->route;
        if(isset($request->ajax))

          return view('backoffice::RenewalOrTermination.landlord_renewal_due_list_ajax',compact('renewalDues','request','route'));

        return view('backoffice::RenewalOrTermination.landlord_renewal_due_list',compact('renewalDues','request','enquiry_fields','operations','name'));
      }

          //Advance search-Landlord Renewal Contract
      public function renewalContractSearch(Request $request,$result = array())
      {
        $name = Route::currentRouteName();
        $enquiry_fields = [
        'landlord_contract_no' => 'Contract No',
        'building_name' => 'Building',
        'vendor_name' => 'Landlord',
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

        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
        $result = array();

        if(isset($request)){

          $renewalDue =   new LandlordRenewalController;       
          $result =     $renewalDue->landlordDueSearch($request); 
          $request->flash(); 

        }
        /*      $contractRenewals = LandlordContract::closure($result)->where('landlord_contract_status',1)->where('landlord_renewal_termination_status','!=',0)->where('landlord_renewal_termination_status','!=',5)->where('landlord_duration',2)->sortable()->paginate(10);*/
      //dd($contractRenewals);
        if(isset($request->route))
          $route   =  $request->route;
        $contractRenewal = Renewal::whereHas('oldLandlordContract', function ($query) use($rolesNames,$result){
          $query->where('landlord_contract_status',1)
          ->whereIn('landlord_renewal_termination_status',[1,2,3,4])->closure($result)
          ->sortable();

        })->whereHas('renewalUsers', function ($query) {
         $query->where('status','=',1);
       })                  
        ->where('work_flow_processes_code', '=', 401);

        if (in_array('super_admin', $rolesNames) === false) {
          $contractRenewal->whereHas('renewalUsers', function ($query) use($roles) {
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
        $contractRenewals = $contractRenewal->paginate(10);
//dd($contractRenewals);
        if(isset($request->ajax))

          return view('backoffice::RenewalOrTermination.landlord_renewal_contract_list_ajax',compact('contractRenewals','request','route'));

        return view('backoffice::RenewalOrTermination.landlord_renewal_contract_list',compact('contractRenewals','request','enquiry_fields','operations','name'));
      }
      public function renewalContractFilter(){


        $enquiry_fields = [
        'landlord_contract_no' => 'Contract No',
        'building_name' => 'Building',
        'vendor_name' => 'Landlord',
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

        return view('backoffice::RenewalOrTermination.landlord_due_filter',compact('enquiry_fields','operations'));


      }
      public function getLandlordRenewalContract($landlord_contract_id){

        clearNotification('Modules\BackOffice\Notifications\LandlordRenewalNotification',$landlord_contract_id);
        readNotification('Modules\BackOffice\Notifications\LandlordRenewalNotification',$landlord_contract_id); 
        
        $landlordContract =  Renewal::where('new_contract_id',$landlord_contract_id)
                                     ->where('renewal_type',2)->first();

        $notes = Renewal::where('new_contract_id',$landlord_contract_id)
                         ->where('renewal_type',2)
                         ->get();

        $renewalNotes = RenewalNote::whereHas('renewal',function($query)use($landlord_contract_id) {
          $query->where('new_contract_id', '=', $landlord_contract_id)
                ->where('renewal_type',2);                      
        })->get();

       //dd($renewalNotes);
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
        return view('backoffice::RenewalOrTermination.landlord_renewal_contract_view',compact('landlordContract','notes','renewalNotes','rolesNames'));
      }
        /*
    *
    *
    *Renewal Contract Edit
    *
    */
        public function renewalContractCreate($id,$status)
        {  
          $landlordContractLatest = LandlordContract::orderBy('id','desc')->limit(1)->first();
          $prefix       = prefixData('landlord_agreement_prefix')->configuration_value;
        //  if(!empty($landlordContractLatest))
       //     $nextAgree = $prefix.str_pad($landlordContractLatest->id+1,4,'0',STR_PAD_LEFT);
       //   else
       //     $nextAgree = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
          $year    = prefixData('landlord_agreement_prefix')->configuration_year;
          $isYearCorrect = (date('y') == $year)?true:false;

          $generateCode = $this->landlordContractCode();
          $nextAgree = $generateCode['code']; 
          $landlordContract = LandlordContract::where('id',$id)->first();
          $startDate = date('Y-m-d',strtotime($landlordContract->landlord_contract_valid_to_date. ' +1 day'));
          $landlordContract->landlord_contract_valid_from_date = $startDate;//dd($landlordContract);
          $vendorsList     = Vendor::latest()->get();
          $buildingList     = Building::latest()->get();
          $managementType   = ManagementType::latest()->get();
          $employeeList     = Employee::orderBy('id', 'DESC')->get();
          $paymentmethodList  = PaymentMethod::latest()->get();
//dd($landlordContract);
          return view('backoffice::RenewalOrTermination.landlord_renew_contract_add',compact('landlordContract','vendorsList','buildingList','managementType','paymentmethodList','employeeList','status','nextAgree','isYearCorrect'));
        }
    /*
    *
    *
    *Renewal Contract Add
    *
    */
    public function renewalContractAdd(Request $request){
      $this->validate($request, [                    
        'duration_type'   => 'required',
        'landlord_contract_valid_from_date'   => 'required|date',
        'landlord_contract_payment_type' => 'required', 
        'landlord_marketing_executive'=>'required',
        'management_id'=>'required',
        ]);
         $generateCode = $this->landlordContractCode();
         $nextAgree = $generateCode['code'];
  

      $old_landlord_contract = LandlordContract::where('id',$request->landlord_contract_id)->first();
      
      $landlord_old_contract_id=$request->landlord_contract_id;

      $LandlordNewContract =   LandlordContract::create([
        'building_id' =>$request->building_id,
        'vendor_id' =>$request->vendor_id,
        'sale_enquiry_id' =>$request->sale_enquiry_id,
        'landlord_contract_name' =>$request->landlord_contract_name,
        'landlord_contract_old_no' =>$request->landlord_contract_old_no,
        'landlord_contract_no' =>$nextAgree,
        'landlord_contract_payment_type' =>$request->landlord_contract_payment_type,
        'management_id' =>$request->management_id,
        'start_date' => $request->start_date,
        'landlord_free_lease_period' => $request->landlord_free_lease_period,
        'landlord_contract_duration' =>$request->duration_type,
        'landlord_duration' =>$request->duration_type,
        'close_activity' => $request->close_activity,
        'landlord_contract_valid_from_date' => $request->landlord_contract_valid_from_date,
        'landlord_contract_valid_to_date' => $request->landlord_contract_valid_to_date,
        'landlord_marketing_executive' =>$request->landlord_marketing_executive,
        'landlord_contract_note' => $request->landlord_contract_note,
        'landlord_renewal_termination_status' => 2,
        'user_id' => \Auth::user()->id,
        'created_by' => \Auth::user()->id,
        'landlord_contract_duration_type'     => 1, 
        'landlord_contract_management_fee'    => $request['landlord_contract_management_fee'],
        'landlord_contract_percentage'        => $request['landlord_contract_percentage'],
        'cleaning_charge_method'               => $request['cleaning_charge_method'],
        'landlord_contract_cleaning_charge'    => $request['landlord_contract_cleaning_charge'],
        'landlord_contract_amt'               => replaceCommaWithDot($request['landlord_contract_amt']),
        'landlord_contract_agreement_amt'     => null,        
        'landlord_contract_status'            => 0, //0 - Pending in Normal Flow , 1 - Approved , 2 - Unapproved Direct , 3- Pending Approval in Direct Contract         
        'management_method'               => $request['management_method'],
        'management_fee_type'             => $request['management_fee_type'],
        'landlord_indirect_direct_status' =>$old_landlord_contract->landlord_indirect_direct_status , 
        ]);

      /*Renewal::where('old_contract_id',$landlord_old_contract_id)
      ->update(['new_contract_id'=>$LandlordNewContract->id,
      'updated_by' => \Auth::user()->id]);*/
       Setting::where('configuration_settings','landlord_agreement_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);

        //insert Renewal
      $general =  new General;
      $next_process_id = 401;
      $PreviousRenewal = Renewal::where('work_flow_processes_code',$next_process_id)
                                 ->orderBy('id','desc')->limit(1)->first();
      $PreviousRenewal->renewalUser()->update(['status' => 0]);
      //dd($next_process_id);
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);

      $renewal = new Renewal;
      $renewal->old_contract_id     = $landlord_old_contract_id; 
      $renewal->new_contract_id     = $LandlordNewContract->id; 
      $renewal->renewal_type     = 2;
      $renewal->work_flow_processes_code = $next_process_id;
      $renewal->created_by           = \Auth::user()->id;
      $renewal->save();
          // Update status to Landlord Contract
      LandlordContract::where('id',$landlord_old_contract_id)
      ->update(['landlord_renewal_termination_status'=>2,
        'updated_by' => \Auth::user()->id,]);
      if($processAssign != false) {

        foreach($processAssign->assign as $val){
         $renewal->renewalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
         $renewal->save();
       }
       $renewal->renewalUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
       $renewal->save();

     }
     else{

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
        $renewal->renewalUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->user_id]);
        $renewal->save();


      }
    }

    session()->flash('success', 'Landlord Contract Renewed Successfully');
    return redirect()->route('renewalContract');
  }
    /*
    *
    *
    *Renewal Contract Edit
    *
    */
    public function renewalNewContractEdit($landlord_contract_id){
      $landlordContract = LandlordContract::where('id',$landlord_contract_id)
                                          ->first();
          $year    = prefixData('landlord_agreement_prefix')->configuration_year;

          $isYearCorrect = (date('y') == $year)?true:false;
      $renewal = Renewal::where('new_contract_id',$landlord_contract_id)
                         ->where('renewal_type',2)->first();
      //dd($renewal);
      $vendorsList     = Vendor::latest()->get();
      $buildingList     = Building::latest()->get();
      $managementType   = ManagementType::latest()->get();
      $employeeList     = Employee::orderBy('id', 'DESC')->get();
      $paymentmethodList  = PaymentMethod::latest()->get();

      return view('backoffice::RenewalOrTermination.landlord_renewal_contract_update',compact('landlordContract','vendorsList','buildingList','managementType','paymentmethodList','employeeList','renewal','isYearCorrect'));
    }
    /*
    *
    *
    *Renewal Contract Update
    *
    */
    public function renewalNewContractUpdate(Request $request){
      $this->validate($request, [                    
      //  'landlord_contract_duration_type'   => 'required',
        'landlord_contract_valid_from_date'   => 'required|date',
        'landlord_contract_payment_type' => 'required', 
        'landlord_marketing_executive'=>'required',
        'management_id'=>'required',
        ]);
      $landlord_contract_id=$request->landlord_contract_id;
      

      LandlordContract::where('id',$landlord_contract_id)
      ->update([
          'landlord_contract_duration'          => $request['duration_type'],
          'landlord_contract_duration_type'     => 1, //1 - Month, 2 - Year, 3 - Day
          'landlord_contract_management_fee'    => $request['landlord_contract_management_fee'],
          'landlord_contract_percentage'        => $request['landlord_contract_percentage'],
          'cleaning_charge_method'               => $request['cleaning_charge_method'],
          'landlord_contract_cleaning_charge'    => $request['landlord_contract_cleaning_charge'],
          'landlord_contract_amt'               => replaceCommaWithDot($request['landlord_contract_amt']),
          'landlord_contract_agreement_amt'     => null,
          'management_id'                       =>  $request['management_id'],
          'landlord_free_lease_period'          => $request['landlord_free_lease_period'],
          'landlord_marketing_executive'        => $request['landlord_marketing_executive'],
          'landlord_duration'                   => $request['duration_type'],

          'landlord_contract_valid_from_date'   => $request['landlord_contract_valid_from_date'],
          'landlord_contract_valid_to_date'     => ($request['duration_type'] == 1)? NULL : $request['landlord_contract_valid_to_date'],
          'landlord_contract_payment_type'      => $request['landlord_contract_payment_type'],
          'landlord_contract_status'            => 0,
          'landlord_contract_note'              => $request['landlord_contract_note'],
          'start_date'                          => $request['start_date'],
          'management_method'                   => $request['management_method'],
          'management_fee_type'                 => $request['management_fee_type'],
          'close_activity'                      => $request['close_activity'],
          'updated_by'                          => \Auth::user()->id,
      ]);
      session()->flash('success', 'Landlord Contract Updated Successfully');
      return redirect()->route('renewalContract');
    }
/*
*
*
*Landlord contract sent for approval
*
*/
public function landlordSentForApproval($old_id,$new_id){
 //insert Renewal
  $general =  new General;
  $next_process_id = 402;
      //dd($next_process_id);
  $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);

       // Update status to Renewal under approval-3
  LandlordContract::where('id',$new_id)
  ->update(['landlord_renewal_termination_status'=>3,
    'updated_by' => \Auth::user()->id,]);


   // Process Flow Insertion
  $PreviousRenewal = Renewal::where('work_flow_processes_code',$next_process_id)->where('old_contract_id',$old_id)->where('new_contract_id',$new_id)->where('renewal_type',2)->orderBy('id','desc')->limit(1)->first();
//dd( $PreviousRenewal);
  if(!empty($PreviousRenewal)) $PreviousRenewal->renewalUser()->update(['status' => 0]);

  $renewal = new Renewal;
  $renewal->old_contract_id     = $old_id; 
  $renewal->new_contract_id     = $new_id; 
  $renewal->renewal_type     = 2;
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

 }
 else{

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
    $renewal->renewalUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->user_id]);
    $renewal->save();


  }
}
session()->flash('success', 'Landlord Contract Approved Successfully');
return redirect()->route('renewalContract');

}
/*
*
*
*Landlord Contract Approval View
*
*/
public function getLandlordApprovalContract($landlord_contract_id){
  clearNotification('Modules\BackOffice\Notifications\LandlordRenewalNotification',$landlord_contract_id);
  readNotification('Modules\BackOffice\Notifications\LandlordRenewalNotification',$landlord_contract_id); 
  $landlordApprove =  Renewal::where('new_contract_id',$landlord_contract_id)->first();
        //dd($landlordApprove);
  $renewalNotes = RenewalNote::whereHas('renewal', function ($query) use($landlord_contract_id) {
    $query->where('new_contract_id', '=', $landlord_contract_id);                      
  })->get();
       //dd($renewalNotes);
  $rolesNames = \Auth::user()->getRoleNames()->toArray();
  return view('backoffice::RenewalOrTermination.landlord_approval_contract_view',compact('landlordApprove','renewalNotes','rolesNames'));
}
  //Advance search-Landlord Renewal Approval
public function approvalSearch(Request $request,$result = array())
{
  $name = Route::currentRouteName();
  $enquiry_fields = [
  'landlord_contract_no' => 'Contract No',
  'building_name' => 'Building',
  'vendor_name' => 'Landlord',
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
  $roles = \Auth::user()->getRoles();
  $rolesNames = \Auth::user()->getRoleNames()->toArray();

  $result = array();
  if(isset($request)){


    $result =     $this->landlordDueSearch($request);   
    $request->flash();
  }
  if(isset($request->route))
    $route   =  $request->route;

  $contractApproval = Renewal::whereHas('newLandlordContract', function ($query) use($rolesNames,$result){
    $query->where('landlord_contract_status',0)
    ->where('landlord_renewal_termination_status',3)->closure($result)
    ->sortable();

  })->whereHas('renewalUsers', function ($query) {
    $query->where('status','=',1);
  })                   
  ->where('work_flow_processes_code', '=', 402);

  if (in_array('super_admin', $rolesNames) === false) {
    $contractApproval->whereHas('renewalUsers', function ($query) use($roles) {
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
  $contractApprovals = $contractApproval->paginate(10);
      //dd($contractApprovals);
  if(isset($request->route))
    $route   =  $request->route;
  if(isset($request->ajax))

    return view('backoffice::RenewalOrTermination.landlord_renewal_approval_list_ajax',compact('contractApprovals','request','route'));

  return view('backoffice::RenewalOrTermination.landlord_renewal_approval_list',compact('contractApprovals','request','enquiry_fields','operations','name'));
}
/*
    * Enquiry Search Form-Approval
    * 
    *
    */
public function renewalApprovalFilter(){


  $enquiry_fields = [
  'landlord_contract_no' => 'Contract No',
  'building_name' => 'Building',
  'vendor_name' => 'Landlord',
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

  return view('backoffice::RenewalOrTermination.landlord_due_filter',compact('enquiry_fields','operations'));


}
      /*
    *
    *
    * Renewal Contract Approval Stage List
    *
    *
    */
public function landlordContractRenewed(Request $request) 
{   
    $name = Route::currentRouteName();
    $enquiry_fields = [
      'old_contract' => 'Old Contract No',
      'new_contract' => 'Contract No',
      'building_name' => 'Building',
      'vendor_name' => 'Landlord',
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
    $roles = \Auth::user()->getRoles();
    $rolesNames = \Auth::user()->getRoleNames()->toArray();

    $result = array();
    if(isset($request)){
      $result =     $this->landlordDueSearch($request);   
      $request->flash();
    }

    $quick_url =    $route   =  route('landlordContractRenewed');

    $contractRenewals = ViewLandlordRenewalUser::select('new_contract','old_contract','renewal_id','new_contract_id','old_contract_id','building_name','vendor_name','landlord_contract_no','landlord_contract_valid_from_date','landlord_contract_valid_to_date','landlord_contract_amt')
            ->groupBy('new_contract','old_contract','renewal_id','new_contract_id','old_contract_id','building_name','vendor_name','landlord_contract_no','landlord_contract_valid_from_date','landlord_contract_valid_to_date','landlord_contract_amt')->where('landlord_contract_status',2)->
              where('landlord_renewal_termination_status',5)->where('work_flow_processes_code', '=', 403)->where('user_status',1)->filter($request);

    if (in_array('super_admin', $rolesNames) === false) {

      $contractRenewals->where(function ($query) use($roles){
                $query->where('user_id',null)->whereIn('role_id', $roles);
                })->orWhere(function ($query) use($roles){
                    $query->where('user_id','>',0)
                    ->whereIn('role_id', $roles)
                    ->where('user_id','=', \Auth::user()->id)
                    ->where('work_flow_processes_code',403);
                  });                       
              
    }     

    $contractRenewals = $contractRenewals->sortable()->paginate($this->noOfRecord);


    if(isset($request->ajax))
      return view('backoffice::RenewalOrTermination.landlord_contract_renewed_list_ajax',compact('contractRenewals','request','route'));

    return view('backoffice::RenewalOrTermination.landlord_contract_renewed_list',compact('contractRenewals','request','enquiry_fields','operations','name','quick_url'));
}
    //landlord renewed Contract grid show
    public function getLandlordRenewedContract($landlord_contract_id)
    {

      clearNotification('Modules\BackOffice\Notifications\LandlordRenewalNotification',$landlord_contract_id);
      readNotification('Modules\BackOffice\Notifications\LandlordRenewalNotification',$landlord_contract_id); 
      $renewedContract =  Renewal::where('new_contract_id',$landlord_contract_id)->first();
        //dd($renewedContract);
      $notes = Renewal::where('new_contract_id',$landlord_contract_id)->get();
        //dd($notes);
      return view('backoffice::RenewalOrTermination.landlord_renewed_contract_view',compact('renewedContract','notes'));
    }
    //approve reject modal
    public function landlordRenewalApproveReject(Request $request){
      $new_contract_id = $request->landlord_contract_id;
      $landlord_old_contract_id = $request->landlord_old_contract_id;
      $status = $request->status;
      $process_flow = $request->process_flow;
      $action_key = $request->action_key;

      return view('backoffice::RenewalOrTermination.landlord_renewal_approval_accept_reject_modal',compact('new_contract_id','status','process_flow','action_key'));
    }
     /*
    *
    *Renewal Approve Reject Store
    *
    */
     public function landlordRenewalApproveRejectStore(Request $request)
     {

      $new_contract_id = $request->new_contract_id;
      $newContractDetail = LandlordContract::where('id',$new_contract_id)->first();
      $action_key = $request->action_key;
      //dd($action_key);
      $oldContractId = LandlordContract::where('id',$new_contract_id)->first()->landlord_contract_old_no;

      $oldContract = LandlordContract::where('landlord_contract_no',$oldContractId)->first()->id;
      $status = $request->status;
      $stage = $request->process_flow;
      $renewal_notes = $request->renewal_notes;


      // Process Flow Insertion
      $general =  new General;
      $next_process_id = $general->nextProcessFromAction($stage,$action_key);
      //dd($next_process_id);
      if($status != 4) 
      {
        $PreviousRenewal = Renewal::where('work_flow_processes_code',$next_process_id)->where('renewal_type',2)->where('old_contract_id',$oldContract)->where('new_contract_id',$new_contract_id)->orderBy('id','desc')->limit(1)->first();

        if(!empty($PreviousRenewal))
          $PreviousRenewal->renewalUser()->update(['status' => 0]);
      }
      // current stage update 
      $stageRenewal = Renewal::where('work_flow_processes_code',$stage)->where('renewal_type',2)->where('old_contract_id',$oldContract)->where('new_contract_id',$new_contract_id)->orderBy('id','desc')->limit(1)->first();
//dd($stageRenewal);
      if(!empty($stageRenewal))
        $stageRenewal->renewalUser()->update(['status' => 0]);


      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);

      $renewal = new Renewal; 
      $renewal->old_contract_id     = $oldContract; 
      $renewal->new_contract_id     = $new_contract_id; 
      $renewal->renewal_type     = 2;
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
    $landlordContract =  LandlordContract::where('id',$new_contract_id)->update(['landlord_renewal_termination_status'=>$status]);
    /*Notification Of users*/
    $users = User::role(['backoffice_executive'])->get(); 
    $users = array_flatten($users); 

    if($status == 4){

      $OldLandlordContract =  LandlordContract::where('id',$oldContract)->update(['landlord_renewal_termination_status'=>$status]);
      //Notification
      $newContractDetail->href = url('renewalContract/'.$newContractDetail->id);
      event(new LandlordRenewalReject($newContractDetail,$users));

      session()->flash('success', 'Landlord Renewal Contract Rejected Successfully');
    }else{

      $currentDate = date('Y-m-d');
    
      if ($currentDate >= date_format($newContractDetail->start_date,"Y-m-d")) 
      {
        $OldLandlordContract =  LandlordContract::where('id',$oldContract)->update(['landlord_renewal_termination_status'=>6,'landlord_contract_status'=>0]);
         $newLandlordContractcrt =  LandlordContract::where('id',$newContractDetail->id)->update(['landlord_renewal_termination_status'=>0,'landlord_contract_status'=>1]);

      }else
      {
        $OldLandlordContract =  LandlordContract::where('id',$oldContract)->update(['landlord_renewal_termination_status'=>6]);
      }

       //Notification
      $newContractDetail->href = url('contractRenewed/'.$newContractDetail->id.'/landlordRenewal');
      event(new LandlordRenewalApprove($newContractDetail,$users)); 


      session()->flash('success', 'Landlord Renewal Contract Approve Successfully');
    }
    return redirect()->route('landlordContractApproval');

    
  }
  public function renewedContractFilter(){


    $enquiry_fields = [
    'landlord_contract_no' => 'Contract No',
    'building_name' => 'Building',
    'vendor_name' => 'Landlord',
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

    return view('backoffice::RenewalOrTermination.landlord_due_filter',compact('enquiry_fields','operations'));


  }
  //Advance search-Landlord Renewed Contract
  public function renewedContractSearch(Request $request,$result = array())
  {
    $name = Route::currentRouteName();
    $enquiry_fields = [
    'landlord_contract_no' => 'Contract No',
    'building_name' => 'Building',
    'vendor_name' => 'Landlord',
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
    $roles = \Auth::user()->getRoles();
    $rolesNames = \Auth::user()->getRoleNames()->toArray();

    $result = array();
    if(isset($request)){


      $result =     $this->landlordDueSearch($request);   
      $request->flash();
    }
    if(isset($request->route))
      $route   =  $request->route;

    $contractRenewed = Renewal::whereHas('newLandlordContract', function ($query) use($rolesNames,$result){
      $query->where('landlord_contract_status',0)
      ->where('landlord_renewal_termination_status',5)->closure($result)
      ->sortable();

    })->whereHas('renewalUsers', function ($query) {
      $query->where('status','=',1);
    })                   
    ->where('work_flow_processes_code', '=', 403);

    if (in_array('super_admin', $rolesNames) === false) {
      $contractRenewed->whereHas('renewalUsers', function ($query) use($roles) {
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
    $contractRenewals = $contractRenewed->paginate(10);
     // dd($contractRenewals);
    if(isset($request->route))
      $route   =  $request->route;
    if(isset($request->ajax))

      return view('backoffice::RenewalOrTermination.landlord_contract_renewed_list_ajax',compact('contractRenewals','request','route'));

    return view('backoffice::RenewalOrTermination.landlord_contract_renewed_list',compact('contractRenewals','request','enquiry_fields','operations','name'));
  }
  /*
    *
    * Add Landlord Renewal Note
    *
    */
  public function landlordRenewalNoteStore(Request $request) {
      //dd($request['renewal_id']);
    $this->validate($request, [                  
      'renewal_note' => 'required|max:250' ,        
      ]);  
    $url = $request['current_url'];
    $notes = RenewalNote::create([
      'renewal_id' => $request['renewal_id'],
      'renewal_type' => 2,
      'user_id' => \Auth::user()->id,
      'renewal_notes_note' => $request['renewal_note'],
      'created_by' => \Auth::user()->id,
      ]);
    session()->flash('success', 'Renewal Notes Created Successfully');
    return redirect($url);

  }
   public function landlordContractCode(){

      $prefix  = prefixData('landlord_agreement_prefix')->configuration_value.prefixData('landlord_agreement_prefix')->configuration_year;
     
      $incVal  = prefixData('landlord_agreement_prefix')->configuration_increment_value;
     
      $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);
     
      return array('code'=>$nextCode,'inc'=>$incVal);

    }


}
