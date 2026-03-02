<?php

namespace Modules\Sales\Http\Controllers;

use Modules\Sales\Http\Controllers\SalesEnquiryController;

use Modules\Sales\Entities\Sales;
use Modules\Sales\Entities\SalesUsers;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Sales\Entities\SalesNote;
use Modules\Sales\Entities\SalesActivity;
use Modules\General\Entities\WorkFlowProcess;
use Modules\Masters\Entities\Employee;
use Modules\Sales\Entities\TenantContract;
use Modules\Sales\Entities\ViewTenantStage;
use Modules\Sales\Entities\Tenant;
use Spatie\Permission\Models\Role; 
use Modules\Masters\Entities\Unit;
use App\User;
use DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\General\Http\Controllers\GeneralController as General ;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

use Modules\Sales\Events\AssignedEnquiry;
use Modules\Sales\Events\ReminderAssignEnquiry;
use Modules\Sales\Events\StageEnquiry;
use Modules\Sales\Emails\SalesAssignEmail;
use Modules\Sales\Emails\SalesRejectEmail;
use Dynamics;

class TenantStageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function __construct()
    {
      $this->middleware('auth'); 
      $this->middleware('permission:unassigned_list', ['only' => ['leadAssign']]);
      $this->middleware('permission:assigned_list', ['only' => ['assignedList']]);
      $this->middleware('permission:inprogress_list', ['only' => ['inprogressList']]);
      $this->middleware('permission:preliminary_list', ['only' => ['documentationList']]); 
     // $this->middleware('permission:preliminary_approval_list', ['only' => ['preliminaryApprovalList']]);
      $this->middleware('permission:final_documentation_list', ['only' => ['finalDocumentationList']]); 
      $this->middleware('permission:final_approval_list', ['only' => ['finalApprovalList']]);  
      $this->middleware('permission:won_list', ['only' => ['wonList']]);
      $this->middleware('permission:closed_list', ['only' => ['closedList']]);
      $this->middleware('permission:assign_tenant_enquiry', ['only' => ['assignModal']]);
      $this->middleware('permission:reassign_tenant', ['only' => ['reAssign']]);
      $this->middleware('permission:group_assign', ['only' => ['groupAssignModal']]);

      $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request,$result = array())
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
      ->salesUsers()
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
      
            $unassigned_lists = $unassigned_list->orderBy($request->sort,$request->direction)->paginate($this->noOfRecord);
 
        }
        else{
            $unassigned_lists = $unassigned_list->sortable()->paginate($this->noOfRecord);
        }
        $quick_url = route('leadAssign.tenantUnassignedSearch');
        return view('sales::TenantSales.tenant_unassigned_list',compact('unassigned_lists','enquiry_fields','operations','quick_url'));
      }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {

      return view('sales::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request,SalesEnquiry $salesEnquiry)
    {

       //dd(\Auth::user()->default_role);
      $action_key = $request['action_key']; 
      $workflow_id = $request['workflow_id'];
      $roleid = $request['role'];
      $userid = $request['user_id'];
      $redirectPage = $request['redirectPage'];
        //dd($userid);
      /* Get Next Proess from action*/
      $general =  new General;
      $next_process_id = $general->nextProcessFromAction($workflow_id,$action_key);

      $enquiry_id =  $request['enquiryId'];
      $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
      $location = $enquiryDetails->locations()->first();
      $location_id = $location->id;
      $priceRange = $enquiryDetails->priceRanges()->first();
      $pricerange_id = $priceRange->id;

      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id,$pricerange_id,$tenant_status=null);

      $countSales = Sales::where('sales_enquiry_id',$enquiry_id)->count();
      if($countSales == 1) SalesEnquiry::where('id', $enquiry_id)->update([
        'enquiry_owner' => \Auth::user()->id,
        ]);

        $sales = Sales::create([
          'sales_enquiry_id' =>$enquiry_id,
          'sales_type' => $enquiryDetails->sales_type,
          'work_flow_processes_code' => $next_process_id,
          'created_by' => \Auth::user()->id,
          ]);
      $sales->salesUsers()->attach($roleid,['user_id'=>$userid]);
      $sales->salesUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);


      $enquiryDetails->update([
        'work_flow_processes_code' => $next_process_id,
        'assigned_person' => $userid,
        'updated_by' => \Auth::user()->id,
        ]);

      /****** Activity log *****/

      activity('Enquiry Assign')
      ->performedOn($sales)
      ->causedBy(\Auth::user()->id)
      ->withProperties($sales)
      ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);


      //Notification 
      clearNotification('Modules\Sales\Notifications\EnquiryNotification',$enquiry_id,true);
      $users_notify =  \App\User::whereIn('id',[$userid])->get(); 
      $usersList =  \App\User::whereIn('id',[$userid])->first(); 
      $mobile = $usersList->employee->employee_contact_no ?? $usersList->employee->employee_secondary_no;

      $msg = "Tenant Enquiry Assigned !";
      $params = 'optional data';
      
      $enquiryDetails->text = "Enquiry - ".$enquiryDetails->sales_enquiry_no."  Assigned ";  
      $enquiryDetails->href = url('tenantNextstage/'.$enquiryDetails->id.'/'.$next_process_id); 
//dd($enquiryDetails);
      event(new AssignedEnquiry($enquiryDetails,$users_notify)); //Internal Notification
      if(!empty($usersList->email)){
      Mail::to($usersList->email)->send(new SalesAssignEmail($enquiryDetails)); //Email Notification
    }else{
       sendSms($mobile,$msg,$params); //SMS Notification
     }


     session()->flash('success', 'Lead Assigned Successfully');
     if($redirectPage=='view')
      return redirect(url('tenantNextstage/'.$enquiryDetails->id.'/'.$next_process_id));
    else
      return redirect()->route('leadAssign.index');


  }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
      return view('sales::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(SalesEnquiry $enquiry)
    {

      return view('sales::add_sales_enquiry',compact('enquiry'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {

    }
    /*
    *
    *
    * Assign Popup View
    *
    *
    */
    public function assignModal($id,$workflow_id,$redirectPage) {

      $enquiry_id = $id;
      $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
      $location = $enquiryDetails->locations()->first();
      $location_id = $location->id;
      $page       = $redirectPage;

        //$next_process_id =102;
      $priceRange = $enquiryDetails->priceRanges()->first();
      $pricerange_id = $priceRange->id;

      $general =  new General;
      $next_process_id = $general->nextProcessFromAction($workflow_id,'AS');

      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id,$pricerange_id,$tenant_status=0);
        //dd($processAssign); 

       //dd($processAssign);
      if($processAssign) {

        $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
        $roles = $processAssign->assign->where('user_id',null);
        $roles = $roles->pluck('role_id');
          //dd(count($user_ids));
        if(count($roles) > 0){

          $users =  User::role($roles)->pluck('id'); 

          $users_count = count($users, COUNT_RECURSIVE)+1;
          if(count($user_ids)>0) {
            $users = array_add($users, $users_count, $user_ids); 
          }


        }else{
          $users = $user_ids;
        } 


          //dd($users);
        $users =  User::whereIn('id',$users)->where('user_type','employee')->whereHas('employee', function ($query){
          $query->active();                      
        })->get(); 

      } else {

            //$previous_work_flow_order_code = $general->getPreviousProcessOrder($workflow_id);
            //dd($previous_work_flow_order_code);
        $previousSales = Sales::with('salesUser')
        ->where('work_flow_processes_code', '=', $workflow_id)
        ->where('sales_enquiry_id',$enquiry_id)->first();
            //dd($previousSales);
        $user_ids =   (array)$previousSales->salesUser->where('user_id','>',0)->pluck('user_id');

        $roles = $previousSales->salesUser->where('user_id',null);
        $roles = $roles->pluck('role_id');
        if(count($roles) > 0){

          $users =  (array)User::role($roles)->pluck('id'); 
          $users = array_collapse($users,$user_ids);            

        }else{
          $users = $user_ids;
        }  
            //dd($users);
        $users =  User::whereIn('id',$users)->where('user_type','employee')->whereHas('employee', function ($query){
          $query->active();                      
        })->get();

      }


      return view('sales::TenantSales.tenant_assign_employee_modal',compact('users','enquiry_id','page'));
    }
     /*
    *
    *
    * Group Assign Popup View
    * Author : Jackson
    *
    */
     public function groupAssignModal(Request $request) {


      $workflow_id  = 101; 
      $enqueryArr = $request->id;
      $user = array();
      foreach($enqueryArr as $enq){                     
        $enquiry_id     = $enq;
        $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
        $location       = $enquiryDetails->locations()->first();
        $location_id    = $location->id;

        //$next_process_id =102;
        $priceRange     = $enquiryDetails->priceRanges()->first();
        $pricerange_id  = $priceRange->id;

        $general          = new General;
        $next_process_id  = $general->nextProcessFromAction($workflow_id,'AS');

        $processAssign    = $general->roleUsersFromProcess($next_process_id,$location_id,$pricerange_id,$tenant_status=0);
        //dd($processAssign->pluck('a')->toArray()); 
        

        if($processAssign) {

          $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
          $roles = $processAssign->assign->where('user_id',null);
          $roles = $roles->pluck('role_id');
          //dd(count($user_ids));
          if(count($roles) > 0){

            $users =  User::role($roles)->pluck('id'); 

            $users_count = count($users, COUNT_RECURSIVE)+1;
            if(count($user_ids)>0) {
              $users = array_add($users, $users_count, $user_ids); 
            }


          }else{
            $users = $user_ids;
          } 
          $Fullusers = array_flatten($users); 
          array_push($user,$Fullusers); 
          //dd($users);
          /*$users =  User::whereIn('id',$users)->where('user_type','employee')->whereHas('employee')->get()->toArray(); 
          if(count($users)> 0){
          foreach($users as $key=>$list){
                
                $empInfo = Employee::where('id', '=',$list['user_type_id'])->first();
             
                $users[$key]['enquiry_id'] = $enquiry_id;
                $users[$key]['employee_name'] = $empInfo->employee_name;
                $users[$key]['employee_picture'] = $empInfo->employee_picture;
           }
         }*/
       } else {

            //$previous_work_flow_order_code = $general->getPreviousProcessOrder($workflow_id);
            //dd($previous_work_flow_order_code);
        $previousSales = Sales::with('salesUser')
        ->where('work_flow_processes_code', '=', $workflow_id)
        ->where('sales_enquiry_id',$enquiry_id)->first();
            //dd($previousSales);
        $user_ids =   (array)$previousSales->salesUser->where('user_id','>',0)->pluck('user_id');

        //$roles = $previousSales->salesUser->where('user_id',null);
        $roles = $roles->pluck('role_id');
        if(count($roles) > 0){

          $users =  (array)User::role($roles)->pluck('id'); 
          $users = array_collapse($users,$user_ids);            

        }else{
          $users = $user_ids;
        }  
        $Fullusers = array_flatten($users); 
        array_push($user,$Fullusers); 
           /* $users =  User::whereIn('id',$users)->where('user_type','employee')->whereHas('employee')->get()->toArray();
            if(count($users)> 0){
            foreach($users as $key=>$list){

                $empInfo = Employee::where('id',$list['user_type_id'])->first();
              
                $users[$key]['enquiry_id'] = $enquiry_id;
                $users[$key]['employee_name'] = $empInfo->employee_name;
                $users[$key]['employee_picture'] = $empInfo->employee_picture;
            }
          }*/
        }

      }$u = array_flatten($user); 
      $users =  User::whereIn('id',$u)->where('user_type','employee')->whereHas('employee', function ($query){
        $query->active();                      
      })->get()->toArray();
      if(count($users)> 0){
        foreach($users as $key=>$list){

          $empInfo = Employee::where('id',$list['user_type_id'])->first();

          $users[$key]['enquiry_id'] = $enquiry_id;
          $users[$key]['employee_name'] = $empInfo->employee_name;
          $users[$key]['employee_picture'] = $empInfo->employee_picture;
        }
      }
      return view('sales::TenantSales.tenant_group_assign_employee_modal',compact('users','enqueryArr'));
    }
    /**
     * Display a listing of the resource Assigned Lists.
     * @return Response
     */
    public function assignedList(Request $request)
    {
      //echo "<pre>";print_r($request->all());exit;

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
//	$result = array();

		 // if(isset($request->fieldName)){
   //       if(count($request->fieldName) > 0){	
			//  $SalesEnquiry = 	 new SalesEnquiryController;		   
		 //    $result =   	$SalesEnquiry->enquirySearch($request);		
		 //  //  dd($result)    
		 //  }
   //      }

     $request->flash();           

     $roles = \Auth::user()->getRoles();
     $rolesNames = \Auth::user()->getRoleNames()->toArray();


     if (in_array('super_admin', $rolesNames) === false) {

      $assigned_lists = ViewTenantStage::where('sale_work_flow_processes_code', '=', 102)->where('work_flow_processes_code', '=', 102)->where('status', '=', 1)->select("sales_enquiry_id","sales_enquiry_no","sales_enquiry_name","sales_mobile_no","created_at","sales_enquiry_name","work_flow_processes_code as sales_work_flow", DB::raw("string_agg(distinct unit_type, ',' ORDER BY unit_type) AS unit_type","sales_note"), DB::raw("
string_agg(distinct cast(loc as text), ',') AS loc"),"sales_note" ,"employee_name")->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no","employee_name")
    // ->salesUsers()
     ->filter($request)
     ->countByAssignday($request)
     ->countByDay($request);


     if(($request->sort=='unit_type' || $request->sort=='loc') && isset($request->direction)){
            
            $assigned_lists = $assigned_lists->orderBy($request->sort,$request->direction)->paginate($this->noOfRecord);
      }
      else{
            $assigned_lists = $assigned_lists->sortable()->paginate($this->noOfRecord);
      }

      //echo "<pre>";print_r(json_encode($assigned_lists));exit;

     }else{

      $assigned_lists = ViewTenantStage::where('sale_work_flow_processes_code', '=', 102)->where('work_flow_processes_code', '=', 102)->where('status', '=', 1)->select("sales_enquiry_id","sales_enquiry_no","sales_enquiry_name","sales_mobile_no","created_at","sales_enquiry_name","work_flow_processes_code as sales_work_flow", DB::raw("string_agg(distinct unit_type, ',' ORDER BY unit_type) AS unit_type","sales_note"), DB::raw("
string_agg(distinct cast(loc as text), ',') AS loc"),"sales_note" ,"employee_name")->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no","employee_name")
    // ->salesUsers()
     ->filter($request)
     ->countByAssignday($request)
     ->countByDay($request);
 
     if(($request->sort=='unit_type' || $request->sort=='loc') && isset($request->direction)){
            
            $assigned_lists = $assigned_lists->orderBy($request->sort,$request->direction)->paginate($this->noOfRecord);
      }
      else{
            $assigned_lists = $assigned_lists->sortable()->paginate($this->noOfRecord);
      }

     }
     
     
 
             // ->select('sales_enquiry_name','sales_company_name','sales_email','sales_mobile_no');
     /*   if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
          $assigned_list->whereHas('salesUsers', function ($query) use($roles) {
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
        $assigned_lists = $assigned_list->sortable()->paginate($this->noOfRecord);*/
        //dd($assigned_lists);
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
        'sales_referred_by' => 'Referred By'          
        ];      


        $quick_url = $route   =  route('leadAssign.assignedList');


        if(isset($request->ajax)) 			
         return view('sales::TenantSales.tenant_assigned_list_ajax',compact('assigned_lists','request','route'));	


       return view('sales::TenantSales.tenant_assigned_list',compact('assigned_lists','enquiry_fields','operations','quick_url'));
     }


    /*
    *
    * Next Stage Tenant
    *
    */
    public function nextStage($id,$stage) {

		//readNotification('Modules\Sales\Notifications\EnquiryNotification',$id);	
      clearNotification('Modules\Sales\Notifications\EnquiryNotification',$id);          

      /*$Onwer = '';*/
      $details = Sales::whereHas('salesEnquiry', function ($query) use($stage,$id) {
        $query->where('work_flow_processes_code', '=', $stage)
        ->with('workFlowProcess')
        ->where('sales_enquiry_id','=',$id);                      
      })->with('createdBy','salesUser')     
      ->where('sales_type', '=', 1)
      ->where('work_flow_processes_code', '=', $stage)
      ->first();
       /* if($stage > 102){
          $Onwer = Sales::where('work_flow_processes_code','>',101)->where('sales_enquiry_id','=',$id)->first();          
        }*/
        
        $allNotes = Sales::with('workFlowProcess')->where('sales_enquiry_id','=',$id)
        ->where('sales_type', '=', 1)->where('sales_notes', '!=', null)->get();
        //dd($allNotes);
        $salesNotes = SalesNote::whereHas('sales', function ($query) use($id) {
          $query->where('sales_enquiry_id', '=', $id);                      
        })->get();
        $tenantContracts = TenantContract::where('sale_enquiry_id','=',$id)
        ->where('tenant_contract_status','=',0)->get();
        $counts = $tenantContracts->pluck('accept_reject_status');
        $ar_status = $counts->contains(2);
        $direct_indirect_status = $tenantContracts->pluck('tennat_contract_direct_indirect_status')->contains(1);
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
        /* Active Contracts */     
        $tenantContractes = TenantContract::where('sale_enquiry_id','=',$id)
        ->where('tenant_contract_status','=',0)->get();
        $getUnits = $tenantContractes->pluck('unit_id');
        $occuipied_unit = Unit::whereIn('id',$getUnits)->where('unit_vaccant_status',1)->pluck('id');
        $occuipied_units = count($occuipied_unit);
        if($details){
          switch ($stage) {
            case "101":
            $routes = 'leadAssign.index';
            return view('sales::TenantSales.tenant_inprogress',compact('details','salesNotes','salesActivities','salesClosedActivities','tenantContracts','routes','salesActivitieslatest','allNotes'));
            break;
            case "102":
            $routes = 'leadAssign.assignedList';
            return view('sales::TenantSales.tenant_inprogress',compact('details','salesNotes','salesActivities','salesClosedActivities','tenantContracts','routes','salesActivitieslatest','allNotes'));
            break;
            case "103":
            $routes = 'inprogressList';
            return view('sales::TenantSales.tenant_inprogress',compact('details','salesNotes','salesActivities','salesClosedActivities','tenantContracts','routes','salesActivitieslatest','allNotes'));
            break;
            case "104":
            $routes = 'documentationList';
            return view('sales::TenantSales.tenant_inprogress',compact('details','salesNotes','salesActivities','salesClosedActivities','tenantContracts','routes','salesActivitieslatest','allNotes'));
            break;
            case "105":
            $routes = 'preliminaryApprovalList';
            return view('sales::TenantSales.tenant_priliminary_approval',compact('details','salesNotes','salesActivities','salesClosedActivities','tenantContracts','tenant','routes','tenantContractes','allNotes','ar_status','direct_indirect_status','occuipied_units'));
            break;
            case "106":
            $routes = 'finalDocumentationList';
            return view('sales::TenantSales.tenant_priliminary_approval',compact('details','salesNotes','salesActivities','salesClosedActivities','tenantContracts','tenant','routes','tenantContractes','allNotes','ar_status','direct_indirect_status','occuipied_units'));
            break;
            case "107":
            $routes = 'finalApprovalList';

            return view('sales::TenantSales.tenant_priliminary_approval',compact('details','salesNotes','salesActivities','salesClosedActivities','tenantContracts','tenant','routes','tenantContractes','allNotes','ar_status','direct_indirect_status','occuipied_units'));
            break;
            case "108":
            $routes = 'wonList';
            $tenantContracts = TenantContract::where('sale_enquiry_id','=',$id)->get();
            $tenant = Tenant::whereHas('tenantContract', function ($query) use($id) {
              $query->where('sale_enquiry_id','=',$id);                      
            })->where('tenant_status','=',1)->first();
            return view('sales::TenantSales.tenant_won_detail',compact('details','salesNotes','salesActivities','salesClosedActivities','tenantContracts','tenant','routes','allNotes'));
            break;
            case "109":
            $routes = 'closedList';

            return view('sales::TenantSales.tenant_close_details',compact('details','salesNotes','salesActivities','salesClosedActivities','routes','allNotes'));
            break;

          }
        }else {
          return back();
        }
        

        
        
      }


    /*
    *
    * Pending Info
    *
    */
    public function tenantPendingStageInfo($id,$stage) {

      /*$Onwer = '';*/
      $details = Sales::whereHas('salesEnquiry', function ($query) use($stage,$id) {
        $query->where('work_flow_processes_code', '=', $stage)
        ->with('workFlowProcess')
        ->where('sales_enquiry_id','=',$id);                      
      })->with('createdBy','salesUser')     
      ->where('sales_type', '=', 1)
      ->where('work_flow_processes_code', '=', $stage)
      ->first();
       /* if($stage > 102){
          $Onwer = Sales::where('work_flow_processes_code','>',101)->where('sales_enquiry_id','=',$id)->first();          
        }*/
        
        $allNotes = Sales::with('workFlowProcess')->where('sales_enquiry_id','=',$id)
        ->where('sales_type', '=', 1)->where('sales_notes', '!=', null)->get();
        //dd($allNotes);
        $salesNotes = SalesNote::whereHas('sales', function ($query) use($id) {
          $query->where('sales_enquiry_id', '=', $id);                      
        })->get();
        $tenantContracts = TenantContract::where('sale_enquiry_id','=',$id)
        ->where('tenant_contract_status','=',0)->get();
        $counts = $tenantContracts->pluck('accept_reject_status');
        $ar_status = $counts->contains(2);
        $direct_indirect_status = $tenantContracts->pluck('tennat_contract_direct_indirect_status')->contains(1);
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
        /* Active Contracts */     
        $tenantContractes = TenantContract::where('sale_enquiry_id','=',$id)
        ->where('tenant_contract_status','=',0)->get();

        if($details){
          switch ($stage) {
            case "107":
            $routes = 'finalDocPendingApprovalList';
            return view('sales::TenantSales.tenant_pending_stage_info',compact('details','salesNotes','salesActivities','salesClosedActivities','tenantContracts','tenant','routes','tenantContractes','allNotes','ar_status','direct_indirect_status'));
            break;
            default:
            return back();	
          }
        }else {
          return back();
        }
        

        
        
      }

    /*
    *
    * Move To Next Stage. Stage work flow Approval and reject
    *
    */
    public function moveNextStage(Request $request) {

      $is_revoke_func = False;
      $tennat_contract_direct_indirect_status = false;
      $action_key = $request['action_key']; 
      $workflow_id = $request['workflow_id']; 
      $sales_id = $request['sales_id']; 

      $sales_lead_note_name = $request['sales_lead_note_name'];
      /* Get Next Proess from action*/

      $general =  new General;
      $next_process_id = $general->nextProcessFromAction($workflow_id,$action_key);

      // print_r($next_process_id);
      // print_r($action_key);exit();

      $process = $general->workFlowProcessNames($next_process_id);

      $enquiryid = $request['enquiryid'];
      $countSales = Sales::where('sales_enquiry_id',$enquiryid)->count();
      if($countSales == 1) SalesEnquiry::where('id', $enquiryid)->update([
        'enquiry_owner' => \Auth::user()->id,
        ]);
        $enquiryDetails = SalesEnquiry::where('id','=',$enquiryid)->first();
     // dd($enquiryDetails)  ;
    // dd($enquiryDetails->locations()) ;  
      $location = $enquiryDetails->locations()->first();

      if($location != null)
        $location_id = $location->id;
      else 
        $location_id = null;

      $priceRange = $enquiryDetails->priceRanges()->first();

      if($priceRange != null)
        $pricerange_id = $priceRange->id;
      else 
        $pricerange_id = null;
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id,$pricerange_id,$tenant_status=null);

      $previous = Sales::with('salesUser')->where('work_flow_processes_code',$workflow_id)
      ->where('sales_enquiry_id',$enquiryid)->orderBy('created_at', 'desc')->first();

      if(!empty($previous)){
        $previous->salesUser()->update(['status' => 0]);
        if($sales_lead_note_name && $request['submit']== 'submit'){
          $previous->update(['sales_notes' => $sales_lead_note_name,'updated_by' =>  \Auth::user()->id]);
        }
      }

      //update tenant contract table

	  //making unit Occupied -Accept

      if($next_process_id == 106 && $action_key != 'RJCT') {
        if(isset($enquiryDetails)){
           $tenantData = TenantContract::where('sale_enquiry_id','=',$enquiryDetails->id)->first();
         
		        if(!empty($tenantData)){
              $tenantData->Unit->update(['unit_vaccant_status'=>'1']);
              //$tenantData->update(['work_flow_processes_code'=>'200','tenant_contract_status'=>'2']);
            }

        }


      }
      if($next_process_id == 109 && $action_key == 'CL') {
        if(isset($enquiryDetails)){
           $tenantData = TenantContract::where('sale_enquiry_id','=',$enquiryDetails->id)->first();
         
            if(!empty($tenantData)){
              
              $tenantData->update(['work_flow_processes_code'=>'200','tenant_contract_status'=>'2']);
            }

        }


      }
//making unit Vaccant -Renegotiating
      if($next_process_id == 104 && $action_key == 'RNEG') {
        if(isset($enquiryDetails)){
           $tenantData = TenantContract::where('sale_enquiry_id','=',$enquiryDetails->id)->first();
          if(!empty($tenantData)){ 
            $tenantData->Unit->update(['unit_vaccant_status'=>'0','unit_status'=>1]);
            //$tenantData->update(['work_flow_processes_code'=>'200','tenant_contract_status'=>'2']);
          }
        }
      }

      if($next_process_id == 108){

        TenantContract::where('sale_enquiry_id','=',$enquiryid)->update(['work_flow_processes_code'=> $next_process_id,'tenant_contract_status'=>1,'is_direct_contract_pending'=>1]);
		SalesEnquiry::where('id', $enquiryid)->update([
            'work_flow_processes_code' => 108
        ]);
        $unit_listActive = TenantContract::where('sale_enquiry_id','=',$enquiryid)->where('tenant_contract_status','=',1)->get();
		$tenantData = TenantContract::where('sale_enquiry_id','=',$enquiryid)->first();
		$tenantInfo  = Tenant::where('id',$tenantData->tenant_id)->first();
		if( AX_ENABLE_DISABLE ==1){ 
		// Isvendor Exit or not in AX
		if(Dynamics::TenantIsExitAxPushData('isTenantExistFunc',$tenantInfo)==false){
			// Update to AX
			if(Dynamics::TenantAxPushData('AXTenant', $tenantInfo)=='Error'){
				
			//	return redirect()->back()->withMessage('error', 'Microsoft Dynamics API Service Error');
											
			}
		}
		}
        foreach($unit_listActive as $unit){
         Tenant::where('id',$unit->tenant_id)->update(['tenant_status'=>1]);
         Unit::where('id',$unit->unit_id)->update(['unit_vaccant_status'=>1]);
         if($unit->tenant_contract_is_revoke ==1){
          TenantContract::where('sale_enquiry_id','=',$enquiryid)->where('tenant_contract_is_revoke','=',1)->update(['tenant_contract_is_revoke'=>2]);		
          $is_revoke_func = True;
        }
        elseif($unit->tennat_contract_direct_indirect_status ==1){
          $tennat_contract_direct_indirect_status = True;
        }

      }

    }
    $sales = new Sales; 
    $sales->sales_enquiry_id     = $enquiryid;
    $sales->sales_type           = $enquiryDetails->sales_type;
    $sales->work_flow_processes_code = $next_process_id;
    $sales->created_by           = \Auth::user()->id;
       // if($sales_lead_note_name && $request['submit']== 'submit'){$sales->sales_notes = $sales_lead_note_name;}
    if($action_key == 'RJCT')
      $sales->refer_back = 1;

    $sales->save();

    if($processAssign) {
      $us = $processAssign->assign->pluck('user_id');
     // dd($us);
      foreach($processAssign->assign as $val){
       $sales->salesUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
       $sales->salesUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
       $sales->save();
     }
			// closed person can also re-open the enquiry
     if($next_process_id == 109){

      $sales->salesUsers()->attach(\Auth::user()->getRoles(), ['user_id' => \Auth::user()->id]);
      $sales->salesUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
      $sales->save();
    }
  }else {
          //$previous_work_flow_order_code = $general->getPreviousProcessOrder($workflow_id);
    $previousSales = Sales::with('salesUser')
    ->where('work_flow_processes_code',$workflow_id)
    ->where('sales_enquiry_id',$enquiryid)->orderBy('created_at', 'desc')->first();
    if(!empty($previousSales)){
      $previousSales->salesUser()->update(['status' => 0]);
    } 

    $us = $previousSales->salesUser->pluck('user_id');

    foreach($previousSales->salesUser as $val){

     $sales->salesUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
     /*$sales->salesUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);*/
     $sales->save();
   }
   $sales->salesUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
   $sales->save();
			// closed person can also re-open the enquiry
   if($next_process_id == 109){

    $sales->salesUsers()->attach(\Auth::user()->getRoles(), ['user_id' => \Auth::user()->id]);
    $sales->save();
  }

} 
/* All Stages Enquiry Onwer Save*/

$enq_owner = Sales::with('salesUser')->where('sales_enquiry_id',$enquiryid)->orderBy('work_flow_processes_code', 'desc')
->offset(1)->first();
if(!empty($enq_owner)){

  foreach($enq_owner->salesUser as $val){

    $sales->salesUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
    $sales->save();

  }
}

if($action_key == 'RJCT') {
        if(isset($enquiryDetails)){
           $tenantData = TenantContract::where('sale_enquiry_id','=',$enquiryDetails->id)->first();
         
            if(!empty($tenantData)){
              
              $tenantData->update(['work_flow_processes_code'=>'200','tenant_contract_status'=>'2']);
            }

        }


      }


SalesEnquiry::where('id', $enquiryid)->update([
  'work_flow_processes_code' => $next_process_id,
  'updated_by' => \Auth::user()->id,
  ]);

/***** Activity Log *****/
activity($process->work_flow_processes_name)
->performedOn($sales)
->causedBy(\Auth::user()->id)
->withProperties($sales)
->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);


session()->flash('success', 'Sales Lead Move To '.$process->work_flow_processes_name);
$stage = $workflow_id;
clearNotification('Modules\Sales\Notifications\EnquiryNotification',$enquiryid,true);   
     //   readNotification('Modules\Sales\Notifications\EnquiryNotification',$enquiryid);

if($action_key == 'RJCT'){
  $flattenedus = array_flatten($us);
  $us = array_unique($flattenedus,SORT_REGULAR);
  $us = array_diff($us, (is_array(1) ? 1 : array(1)));
  $us = array_filter($us);  
  $usr =  \App\User::whereIn('id',$us)->get();
  $msg = "Rejected";
  $enquiryDetails->msg = $msg." ".$process->work_flow_processes_name;
  $enquiryDetails->href = url('tenantNextstage/'.$enquiryDetails->id.'/'.$next_process_id);  
  event(new StageEnquiry($enquiryDetails,$usr)); 

}
 if($action_key == 'CL'){
	 
	//unit vaccant -close
	if(!empty($enquiryDetails)){
		$tenantData = TenantContract::where('sale_enquiry_id','=',$enquiryid)->first();
		 if(!empty($tenantData)){$tenantData->Unit->update(['unit_vaccant_status'=>'0','unit_status'=>1]);}
	}
    //Notification Starts
  $userLists = User::role(['sales_coordinator','sales_person'])->get(); 
  $userLists = array_flatten($userLists);

  $enquiryDetails->subject = "Preliminary Documentation Rejected";
  $enquiryDetails->textContent = "Preliminary Documentation Rejected Enquiry No: ".$enquiryDetails->sales_enquiry_no;
  foreach($userLists as $userList){
    $mobile = $userList->employee->employee_contact_no ?? $userList->employee->employee_secondary_no;
    $userName = $userList->employee->userName;

    $msg = "Preliminary Documentation Rejected !";
    $params = 'optional data';
    if(!empty($userList->email)){
      Mail::to($userList->email)->send(new SalesRejectEmail($enquiryDetails,$userName)); //Email Notification
    }else{
       sendSms($mobile,$msg,$params); //SMS Notification
     }

   }
//Notification Ends
 }

switch ($stage) {
  case "101":
  return redirect()->route('leadAssign.index');
  break;
  case "102":
  return redirect()->route('leadAssign.assignedList');
  break;
  case "103":
  return redirect()->route('inprogressList');
  break;
  case "104":
  if($action_key != 'RJCT') 
    $this->notifyUsers($next_process_id,$enquiryid);
  return redirect()->route('documentationList');
  break;
  case "105":
  if($action_key != 'RJCT') 
    $this->notifyUsers($next_process_id,$enquiryid);
  return redirect()->route('preliminaryApprovalList');                
  break;
  case "106":
  if($action_key != 'RJCT')  
    $this->notifyUsers($next_process_id,$enquiryid);
  return redirect()->route('finalDocumentationList');
  break;
  case "107":
  if($is_revoke_func || $tennat_contract_direct_indirect_status)
   return redirect()->route('tenantContractApprovedRevoke');
 else
   return redirect()->route('finalApprovalList');
 break;
 case "108":
 return redirect()->route('wonList');
 break;
 case "109":
 if($tennat_contract_direct_indirect_status)
   return redirect()->route('tenantContractApprovedRevoke');
 else
   return redirect()->route('closedList');
 break;    
} 

}

    /*
    *
    * Inprogress List
    *
    */
    public function inprogressList(Request $request) {


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



      $request->flash();  

      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray();

      $inprogress_list =  ViewTenantStage::where('sale_work_flow_processes_code', '=', 103)
      ->where('work_flow_processes_code', '=', 103)
      ->where('status', '=', 1)
      ->select("sales_enquiry_id","sales_enquiry_no","sales_enquiry_name","sales_mobile_no","created_at","sales_enquiry_name","work_flow_processes_code as sales_work_flow",DB::raw("max(inprogress_days) AS inprogress_days"), DB::raw("string_agg(distinct unit_type, ',') AS unit_type","employee_name"), DB::raw("
        string_agg(distinct cast(loc as text), ',') AS loc"),"sales_notes","employee_name" )
      ->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_notes","sales_mobile_no","employee_name","inprogress_days")
      ->countByAssignday($request)
      ->enquiryOwner($request)     
      ->salesUsers()->firstCall($request);
    /*
        if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
          $inprogress_list->where(function ($query) use($roles){
                        $query->where('user_id',null)
                              ->whereIn('role_id', $roles);
                      })
                      ->orWhere(function ($query) use($roles){
                        $query->where('user_id','>',0)
                              ->whereIn('role_id', $roles)
                              ->where('user_id','=', \Auth::user()->id);
                      })                      
                      ->where('status','=',1)->where('sale_work_flow_processes_code', '=', 103);                       
            
        }*/
       if(($request->sort=='unit_type' || $request->sort=='loc') && isset($request->direction)){
            
            $inprogress_lists = $inprogress_list->filter($request)->orderBy($request->sort,$request->direction)->paginate($this->noOfRecord);
 
        }
        else{
          $inprogress_lists = $inprogress_list->filter($request)->sortable()->paginate($this->noOfRecord);
        }
        $enquiry_fields = [
          'sales_enquiry_no' => 'Enquiry No',
          'sales_enquiry_name' => 'Customer Name',
          'sales_email' => 'Email',
         //  'sales_building_name' => 'Building Name',
          'sales_mobile_no' => 'Mobile No',
          /*'sales_company_name' => 'Company Name',*/
          'sales_move_in_date' => 'Move In Date',   
          'created_at' => 'Enquiry Date',    
          'locations__locations_name' => 'Location',  
          'userSearch__username' => 'Enquiry Owner',  
          'sales_referred_by' => 'Referred By',  

          ];


          $quick_url =  $route = route('inprogressList');
          
          
          if(isset($request->ajax)) 			
           return view('sales::TenantSales.tenant_inprogress_list_ajax',compact('inprogress_lists','request','route'));	

        //dd($inprogress_lists);
         return view('sales::TenantSales.tenant_inprogress_list',compact('inprogress_lists','enquiry_fields','operations','request','quick_url'));

       }
    /*
    *
    * Add sales Note
    *
    */
    public function storeSalesNote(Request $request) {

      $this->validate($request, [                  
        'sales_notes' => 'required|max:250' ,        
        ]);  
      $workflowId = $request['workflowId'] ;
      $notes = SalesNote::create([
        'sales_id' => $request['sales_id'],
        'user_id' => \Auth::user()->id,
        'sales_notes_note' => $request['sales_notes'],
        'created_by' => \Auth::user()->id,
        ]);
      /****** Activity log *****/

      activity('Added Sales Note')
      ->performedOn($notes)
      ->causedBy(\Auth::user()->id)
      ->withProperties($notes)
      ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

      $enquiryDetails = Sales::where('id','=',$request['sales_id'])->first();  
      session()->flash('success', 'Sales Notes Created Successfully');
      return redirect()->route('leadAssign.nextStage',['id'=>$enquiryDetails->sales_enquiry_id,'stage'=>$workflowId]);

    }
    /*
    *
    * Documentation List
    *
    */
    public function documentationList(Request $request) {

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

     $request->flash();  

     $roles = \Auth::user()->getRoles();
     $rolesNames = \Auth::user()->getRoleNames()->toArray();
      //Assigned Person 
          $documntation_list = ViewTenantStage::where('work_flow_processes_code', '=',104)->where('sale_work_flow_processes_code','=',104)->where('status','=',1)->select("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code as sales_work_flow", DB::raw("string_agg(distinct tenant_contract_no, ',') AS agre"), DB::raw("string_agg(distinct to_char(tenant_contract_start_date,'DD/MM/YYYY'),',') AS agre_start"),DB::raw("
    string_agg(distinct cast(tenant_contract_rent as text), ',') AS agre_rent","employee_name") ,DB::raw("
    string_agg(distinct building_name, ',') As agre_build"),DB::raw("string_agg(distinct unit_code, ',') As agre_unit"),DB::raw("string_agg(distinct cast(tenant_contract_duration as text), ',') As agre_duration"),"employee_name")->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name","employee_name","tenant_contract_duration");
    

     
     if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
      $documntation_list->where(function ($query) use($roles){
          $query->where('user_id',null)
          ->whereIn('role_id', $roles);
        })
        ->orWhere(function ($query) use($roles){
          $query->where('user_id','>',0)
          ->whereIn('role_id', $roles)
          ->where('user_id','=', \Auth::user()->id);
        })                      
        ->where('status','=',1)->where('sale_work_flow_processes_code','=',104);                       
          
    }
 
    $documntation_lists = $documntation_list->filter($request)->sortable()->paginate($this->noOfRecord);

    $enquiry_fields = [
    'sales_enquiry_no' => 'Enquiry No',
    'sales_enquiry_name' => 'Customer Name',
    'sales_email' => 'Email',
    'building_name' => 'Building Name',
    'sales_mobile_no' => 'Mobile No',
    /*'sales_company_name' => 'Company Name',*/
    'sales_move_in_date' => 'Move In Date',
    'created_at' => 'Enquiry Date',
    'locations_name' => 'Location',  
    'username' => 'Enquiry Owner',
    'sales_referred_by' => 'Referred By',  
    ];                     


    $quick_url = $route   = route('documentationList');	

    if(isset($request->ajax)) 			
     return view('sales::TenantSales.tenant_documentation_list_ajax',compact('documntation_lists','request','route'));	


   return view('sales::TenantSales.tenant_documentation_list',compact('documntation_lists','enquiry_fields','request','operations','quick_url'));

 }


    /*
    *
    * Preliminary Approval List 
    *
    */
    public function preliminaryApprovalList(Request $request) {


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

      $request->flash();  

      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray();
      
      $preliminaryApprovalList = ViewTenantStage::where('work_flow_processes_code', '=',105)->where('sale_work_flow_processes_code','=',105)->where('status','=',1)->select("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code as sales_work_flow", DB::raw("string_agg(distinct tenant_contract_no, ', ') AS agre"), DB::raw("string_agg(distinct to_char(tenant_contract_start_date,'DD/MM/YYYY'),', ') AS agre_start"),DB::raw("
    string_agg(distinct round(tenant_contract_rent::numeric,3)::TEXT,', ') AS agre_rent","employee_name") ,DB::raw("
    string_agg(distinct building_name, ', ') As agre_build"),DB::raw("string_agg(distinct unit_code, ', ') As agre_unit"),DB::raw("string_agg(distinct cast(tenant_contract_duration as text), ', ') As agre_duration"),"employee_name")->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","employee_name","tenant_contract_duration")
      ->salesUsers()->filter($request);
     
	 /*if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
            $preliminaryApprovalList->where(function ($query) use($roles){
                        $query->where('user_id',null)
                              ->whereIn('role_id', $roles);
                      })
                      ->orWhere(function ($query) use($roles){
                        $query->where('user_id','>',0)
                              ->whereIn('role_id', $roles)
                              ->where('user_id','=', \Auth::user()->id);
                      })                      
                      ->where('status','=',1)->where('sale_work_flow_processes_code','=',105);                       
           
      }
		*/
      if(($request->sort=='unit_type' || $request->sort=='loc') && isset($request->direction)){
            
            $preliminaryApprovalLists = $preliminaryApprovalList->orderBy($request->sort,$request->direction)->paginate($this->noOfRecord);
 
    }
    else{
 
          $preliminaryApprovalLists = $preliminaryApprovalList->sortable()->paginate($this->noOfRecord);
    }

      $enquiry_fields = [
      'sales_enquiry_no' => 'Enquiry No',
      'sales_enquiry_name' => 'Customer Name',
      'sales_email' => 'Email',            
      'sales_mobile_no' => 'Mobile No',
      /*'sales_company_name' => 'Company Name',*/
      'sales_move_in_date' => 'Move In Date',
      'created_at' => 'Enquiry Date',
      'building_name' => 'Building Name',
      'loc' => 'Location',  
      'employee_name' => 'Enquiry Owner',
      ];       

      $quick_url = $route   =   route('preliminaryApprovalList');          

      if(isset($request->ajax)) 			
       return view('sales::TenantSales.tenant_priliminary_approval_list_ajax',compact('preliminaryApprovalLists','request','route'));	


     return view('sales::TenantSales.tenant_priliminary_approval_list',compact('preliminaryApprovalLists','request','enquiry_fields','operations','quick_url'));

   }
    /*
    *
    * Accept Action/ Reject / Close
    *
    *
    */
    public function approvalAccept(Request $request) {

      $workflow_id = $request['workflow_id'];
      $sales_id = $request['sales_id'];
      $enquiryid = $request['enquiryid'];
      $action_key = $request['action_key'];
      //dd($action_key);
      $general =  new General;
      $process = $general->workFlowProcessNames($workflow_id);
      $stage = $process->work_flow_processes_name;
      if($stage =='Unassigned' || $stage =='Assigned') {
        $stage = 'FirstCall';
      }elseif($action_key == 'CL'){
        $stage = 'Closed';
      }elseif($action_key == 'ACPT'){
        $stage = 'Accept Note';
      }

      if($action_key == 'RJCT'){
      	return view('sales::TenantSales.sales_lead_reject_note',compact('workflow_id','sales_id','enquiryid','action_key','stage'));
      }else {

      	return view('sales::TenantSales.sales_lead_note_modal',compact('workflow_id','sales_id','enquiryid','action_key','stage'));
      }
      

    }

    /*
    *
    * Preliminary Approval List 
    *
    */
    public function finalDocumentationList(Request $request) {

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

      $request->flash();  

      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray();
      $finalDocumentationList = ViewTenantStage::where('work_flow_processes_code', '=', 106)->where('tennat_contract_direct_indirect_status', '=', 0)->where('status','=',1)->select("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code as sales_work_flow", DB::raw("string_agg(distinct tenant_contract_no, ',') AS Agre"), DB::raw("string_agg(distinct to_char(tenant_contract_start_date,'DD/MM/YYYY'),',') AS Agre_start"),DB::raw("
string_agg(distinct cast(tenant_contract_rent as text), ',') AS Agre_rent") ,DB::raw("
string_agg(distinct building_name, ',') As Agre_build"),DB::raw("string_agg(distinct unit_code, ',') As Agre_unit"))->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name");
 
      if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
        
          $finalDocumentationList->where(function ($query) use($roles){
            $query->where('user_id',null)
            ->whereIn('role_id', $roles);
          })
          ->orWhere(function ($query) use($roles){
            $query->where('user_id','>',0)
            ->whereIn('role_id', $roles)
            ->where('user_id','=', \Auth::user()->id);
          })                      
          ->where('work_flow_processes_code', '=', 106)->where('status','=',1);                       
            
      }

      $finalDocumentationLists = $finalDocumentationList->filter($request)->sortable()->paginate($this->noOfRecord);

      $enquiry_fields = [
      'sales_enquiry_no' => 'Enquiry No',
      'sales_enquiry_name' => 'Customer Name',
      'sales_email' => 'Email',
      'tenantContracts__building__building_name' => 'Building Name',
      'sales_mobile_no' => 'Mobile No',
      /*'sales_company_name' => 'Company Name',*/
      'sales_move_in_date' => 'Move In Date',
      'created_at' => 'Enquiry Date', 
      'location' => 'Location',     
      'enquiry_owner' => 'Enquiry Owner',       
      ];

      


      $quick_url =  $route  = $request->url();

         //dd($finalDocumentationLists); 
      if(isset($request->ajax)) 			
       return view('sales::TenantSales.tenant_final_document_list_ajax',compact('finalDocumentationLists','request','route'));	



     return view('sales::TenantSales.tenant_final_document_list',compact('finalDocumentationLists','enquiry_fields','operations','request','quick_url'));

   }

    /*
    *
    * Final Documentation Pending Approval List
    *
    */
    public function finalDocPendingApprovalList(Request $request) {

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

      if(isset($request->fieldName)){
       if(count($request->fieldName) > 0){	
        $SalesEnquiry = 	 new SalesEnquiryController;		   
        $result = $SalesEnquiry->enquirySearch($request);
      }
    }      
    elseif(isset($request->duration) || isset($request->start_dt) || isset($request->rent) ||  isset($request->unit_select) ||
     isset($request->sales_note) || isset($request->created_at) || isset($request->assigned_person) ||  isset($request->building_name_select) ||
     isset($request->sales_mobile_no) || isset($request->sales_building_name) || isset($request->customer_name) ||  isset($request->sales_enquiry_no)
     ){		

      $SalesEnquiry = 	 new SalesEnquiryController;	
    $result  =  $SalesEnquiry->enquirySearch($request);    
  }  


  $request->flash();  

  $roles = \Auth::user()->getRoles();
  $rolesNames = \Auth::user()->getRoleNames()->toArray();
  $finalDocumentationList = Sales::whereHas('salesEnquiry', function ($query)  use($result){
   $query->where('work_flow_processes_code', '=', 107)
   ->whereHas('tenantContracts', function ($query) { 
    $query->where('tennat_contract_direct_indirect_status', '=', 0); 
  })
   ->closure($result);                  
 })->where('sales.sales_type', '=', 1)
  ->where('sales.work_flow_processes_code', '=', 107)
  ->whereHas('salesUsers', function ($query) use($roles) {
    $query->where('status','=',1);
  });
  if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
    $finalDocumentationList->whereHas('salesUsers', function ($query) use($roles) {
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

  $finalDocumentationLists = $finalDocumentationList->sortable()->paginate($this->noOfRecord);

  $enquiry_fields = [
  'sales_enquiry_no' => 'Enquiry No',
  'sales_enquiry_name' => 'Customer Name',
  'sales_email' => 'Email',
  'sales_building_name' => 'Building Name',
  'sales_mobile_no' => 'Mobile No',
  /*'sales_company_name' => 'Company Name',*/
  'sales_move_in_date' => 'Move In Date',
  'created_at' => 'Enquiry Date', 
  'location' => 'Location',     
  'enquiry_owner' => 'Enquiry Owner',       
  ];



  if(isset($request->route))
   $route   =  $request->url();


 if(isset($request->ajax)) 			
   return view('sales::TenantSales.tenant_final_document_pending_list_ajax',compact('finalDocumentationLists','request','route'));	


 return view('sales::TenantSales.tenant_final_document_pending_list',compact('finalDocumentationLists','enquiry_fields','operations','request'));

}



    /*
    *
    * Preliminary Approval List 
    *
    */
    public function finalApprovalList(Request $request) {

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

     if(isset($request->fieldName)){
       if(count($request->fieldName) > 0){	
        $SalesEnquiry = 	 new SalesEnquiryController;		   
        $result  = $SalesEnquiry->enquirySearch($request);
      }
    }      


    $request->flash();  

    $roles = \Auth::user()->getRoles();
    $rolesNames = \Auth::user()->getRoleNames()->toArray();
    $list = Sales::whereHas('salesEnquiry', function ($query)  use($result){
      $query->where('work_flow_processes_code', '=', 107) 
      ->whereHas('tenantContracts', function ($query) { 
        $query->where('tennat_contract_direct_indirect_status', '=', 0); 
      })
      ->closure($result);                       
    })->where('sales.sales_type', '=', 1)
    ->where('sales.work_flow_processes_code', '=', 107)
    ->whereHas('salesUsers', function ($query) use($roles) {
     $query->where('status','=',1);
   });
    if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
      $list->whereHas('salesUsers', function ($query) use($roles) {
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
            //dd($list);
    }

    $lists = $list->sortable()->paginate($this->noOfRecord);

    $enquiry_fields = [
    'sales_enquiry_no' => 'Enquiry No',
    'sales_enquiry_name' => 'Customer Name',
    'sales_email' => 'Email',
    'sales_building_name' => 'Building Name',
    'sales_mobile_no' => 'Mobile No',
    /*'sales_company_name' => 'Company Name',*/
    'sales_move_in_date' => 'Move In Date',   
    'created_at' => 'Enquiry Date',  'location' => 'Location',  
    'enquiry_owner' => 'Enquiry Owner',      
    ];




    if(isset($request->route))
     $route   =  $request->url();


   if(isset($request->ajax)) 			
     return view('sales::TenantSales.tenant_final_approval_list_ajax',compact('lists','request','route'));	




   return view('sales::TenantSales.tenant_final_approval_list',compact('lists','enquiry_fields','operations','request'));

 }
    /*
    *
    *  Approval List 
    *
    */
    public function wonList(Request $request) {

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


      $request->flash();  

      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray();
            $list = ViewTenantStage::where('work_flow_processes_code', '=',108)->where('sale_work_flow_processes_code','=',108)->where('status','=',1)->where('sales_enquiry_direct_contract','=',1)->select("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","tenant_name","work_flow_processes_code as sales_work_flow", DB::raw("string_agg(distinct tenant_contract_no, ',') AS agre"), DB::raw("string_agg(distinct to_char(tenant_contract_start_date,'DD/MM/YYYY'),',') AS agre_start"),DB::raw("
    string_agg(distinct cast(tenant_contract_rent as text), ',') AS agre_rent","employee_name","unit_usage") ,DB::raw("
    string_agg(distinct building_name, ',') As agre_build"),DB::raw("string_agg(distinct unit_code, ',') As agre_unit"),DB::raw("string_agg(distinct cast(tenant_contract_duration as text), ',') As agre_duration"),"employee_name","unit_type","unit_usage")->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name","employee_name","tenant_contract_duration","unit_type","tenant_name","unit_usage")->filter($request)->countByMonth($request)->salesUsers();
 
/*
      if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
        $list->whereHas('salesUsers', function ($query) use($roles) {
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
*/
      $lists = $list->sortable()->paginate($this->noOfRecord);// dd($lists);
      $enquiry_fields = [
      'sales_enquiry_no' => 'Enquiry No',
      'sales_enquiry_name' => 'Customer Name',
      'sales_email' => 'Email',             
      'sales_mobile_no' => 'Mobile No',
      /*'sales_company_name' => 'Company Name',*/
      'sales_move_in_date' => 'Move In Date',    
      'building_name' => 'Building Name',
     'tenant_name' => 'Tenant Name',
     'unit_no' => 'Unit No',
     'loc' => 'Location',  
     'employee_name' => 'Enquiry Owner',  
      ];

      
      $quick_url =     $route   =  route('wonList');          

      if(isset($request->ajax)) 			
       return view('sales::TenantSales.tenant_won_list_ajax',compact('lists','request','route'));	



     return view('sales::TenantSales.tenant_won_list',compact('lists','enquiry_fields','operations','request','quick_url'));

   }
    /**
     * Display a listing of the resource Assigned Lists.
     * @return Response
     */
    public function closedList(Request $request)
    {

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
      $remark = null; 
      $request->flash(); 	
      if(isset($request->remark)){

        $remark = $request->remark;
      }

      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray();
      
      $list = ViewTenantStage::where('work_flow_processes_code', '=',109)->where('sale_work_flow_processes_code','=',109)->where('status','=',1)->select("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","tenant_name","work_flow_processes_code as sales_work_flow", DB::raw("string_agg(distinct tenant_contract_no, ',') AS agre"), DB::raw("string_agg(distinct to_char(tenant_contract_start_date,'DD/MM/YYYY'),',') AS agre_start"),DB::raw("
    string_agg(distinct cast(tenant_contract_rent as text), ',') AS agre_rent","employee_name","unit_usage","sales_notes") ,DB::raw("
    string_agg(distinct building_name, ',') As agre_build"),DB::raw("string_agg(distinct unit_code, ',') As agre_unit"),DB::raw("string_agg(distinct cast(tenant_contract_duration as text), ',') As agre_duration"),"employee_name","unit_type","unit_usage","cust","cust_no")->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","unit_code","building_name","employee_name","tenant_contract_duration","unit_type","tenant_name","unit_usage","cust","cust_no","sales_notes")
     ->salesUsers()
     ->filter($request)->countByMonth($request);
 /*
      if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
        $list->whereHas('salesUsers', function ($query) use($roles) {
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
            //dd($leads);
      }*/

      $lists = $list->sortable()->paginate($this->noOfRecord);

      $enquiry_fields = [
      'sales_enquiry_no' => 'Enquiry No',
      'sales_enquiry_name' => 'Customer Name',
      'sales_email' => 'Email',
      'sales_building_name' => 'Building Name',
      'sales_mobile_no' => 'Mobile No',
      /*'sales_company_name' => 'Company Name',*/
      'sales_move_in_date' => 'Move In Date',
      'created_at' => 'Enquiry Date',
      'loc' => 'Location',  
      'employee_name' => 'Enquiry Owner',   

      ];


      $previous = Sales::whereHas('salesEnquiry', function ($query) {
        $query->where('work_flow_processes_code', '=', 109);                      
      })->orderBy('created_at', 'desc')->skip(1)->take(1)->first();
     // dd($lists);

      $quick_url = $route   =  route('closedList');          

      if(isset($request->ajax)) 			
       return view('sales::TenantSales.tenant_closed_lists_ajax',compact('lists','previous','request','route'));	


     return view('sales::TenantSales.tenant_closed_lists',compact('lists','previous','enquiry_fields','operations','quick_url'));
   }
    /*
    *
    *
    * Re assign Employes
    *
    */
    public function reAssignModal($id,$workflow_id, $redirect) {

        $enquiry_id = $id;//dd($id);
        $page = $redirect;
        $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
        $general =  new General;
        $users = array();
        if($workflow_id == 107 || $workflow_id == 105){
         $previousProcess = $general->getPreviousOrder($workflow_id);
         
         $workflow_id = $previousProcess;
       }
       $location = $enquiryDetails->locations()->first();

       if($location != null)
        $location_id = $location->id;
      else 
        $location_id = null;

      $priceRange = $enquiryDetails->priceRanges()->first();

      if($priceRange != null)
        $pricerange_id = $priceRange->id;
      else 
        $pricerange_id = null;

      $processAssign = $general->roleUsersFromProcess($workflow_id,$location_id,$pricerange_id,$tenant_status=0); 
        /*if($processAssign) {
          
            $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
            $roles = $processAssign->assign; 
            $roles = $roles->pluck('role_id');
            
            
            if(count($user_ids) > 0){

              $flattened = array_flatten($user_ids);
              $users = array_unique($flattened,SORT_REGULAR); 
              $role = User::whereIn('id',$users)->pluck('default_role');
              $users =  array_diff($users, (is_array(1) ? 1 : array(1)));
              $users =  User::whereIn('id',$users)->get();
                            
            }
            
        }else {

            $previousProcess = $general->getPreviousOrder($workflow_id);
            $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id,$pricerange_id,$tenant_status=0);
            
            $user_ids =   $previousAssign->assign->where('user_id','>',0)->pluck('user_id');
            $roles = $previousAssign->assign; 
            $roles = $roles->pluck('role_id');
            
            
            if(count($user_ids) > 0){

              $flattened = array_flatten($user_ids);
              $users = array_unique($flattened,SORT_REGULAR); 
              $role = User::whereIn('id',$users)->pluck('default_role');
              $users =  array_diff($users, (is_array(1) ? 1 : array(1)));
              $users =  User::whereIn('id',$users)->get();
                            
            }
          
            
           

          }*/
          if($processAssign) {

            $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
            $roles = $processAssign->assign->where('user_id',null);
            $roles = $roles->pluck('role_id');
          //dd(count($user_ids));
            if(count($roles) > 0){

              $users =  User::role($roles)->pluck('id'); 

              $users_count = count($users, COUNT_RECURSIVE)+1;
              if(count($user_ids)>0) {
                $users = array_add($users, $users_count, $user_ids); 
              }


            }else{
              $users = $user_ids;
            } 

            
          //dd($users);
            $users =  User::whereIn('id',$users)->where('user_type','employee')->whereHas('employee', function ($query){
              $query->active();                      
            })->get(); 
            
          } else {

            $previousProcess = $general->getPreviousOrder($workflow_id);
            $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id,$pricerange_id,$tenant_status=0);

            $user_ids =   $previousAssign->assign->where('user_id','>',0)->pluck('user_id');
            $roles = $previousAssign->assign->where('user_id',null); 
            $roles = $roles->pluck('role_id');
            
            
            if(count($roles) > 0){

              $users =  (array)User::role($roles)->pluck('id'); 
              $users = array_collapse($users,$user_ids);            

            }else{
              $users = $user_ids;
            } 
            
            $users =  User::active()->whereIn('id',$users)->where('user_type','employee')->whereHas('employee', function ($query){
              $query->active();                      
            })->get();

          }
        //$roles =  array_diff($roles, (is_array(1) ? 1 : array(1)));
          $roles        = Role::whereIn('id', $roles)->get();
          return view('sales::TenantSales.tenant_reassign_employee_modal',compact('users','roles','enquiry_id','workflow_id','page'));

        }
    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function reAssign(Request $request)
    {
//dd($request);
      $workflow_id = $request['workflow_id'];
      $roleid = $request['role'];
      $userid = $request['user_id'];
      $redirectPage = $request['redirectPage'];

        //dd($workflow_id);
      $enquiry_id =  $request['enquiryid'];
      $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
      $previous = Sales::where('work_flow_processes_code',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();
        //dd($previous);
      $previous->salesUser()->update(['status' => 0]);

      if($workflow_id == 109) {

        $previous = Sales::where('work_flow_processes_code','!=',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();

        $previous->salesUser()->update(['status' => 0]);
        $workflow_id = $previous->work_flow_processes_code;
        if($workflow_id == 101) $workflow_id = 102;
      }
      $sales = Sales::create([
        'sales_enquiry_id' =>$enquiry_id,
        'sales_type' => $enquiryDetails->sales_type,
        'work_flow_processes_code' => $workflow_id,
        'created_by' => \Auth::user()->id,
        ]);
      $sales->salesUsers()->attach($roleid,['user_id'=>$userid]);
        // To view the enquiry stage by assigned person
      $sales->salesUsers()->attach(\Auth::user()->getRoles(), ['user_id' => \Auth::user()->id]);


      SalesEnquiry::where('id', $enquiry_id)->update([
        'work_flow_processes_code' => $workflow_id,
        'assigned_person' => $userid,
        'updated_by' => \Auth::user()->id,
        ]);
      /****** Activity log *****/

      activity('Re Assigned')
      ->performedOn($sales)
      ->causedBy(\Auth::user()->id)
      ->withProperties($sales)
      ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

      session()->flash('success', 'Lead Re-assigned Successfully');

      clearNotification('Modules\Sales\Notifications\EnquiryNotification',$enquiry_id,true);
      $this->assignNotify($enquiry_id,$workflow_id);


      if($redirectPage=='view')
        return redirect(url('tenantNextstage/'.$enquiryDetails->id.'/'.$workflow_id));
      else
        switch ($workflow_id) {
          case "101":
          return redirect()->route('leadAssign.index');
          break;
          case "102":
          return redirect()->route('leadAssign.assignedList');
          break;
          case "103":
          return redirect()->route('inprogressList');
          break;
          case "104":
          return redirect()->route('documentationList');
          break;
          case "105":
          return redirect()->route('preliminaryApprovalList');
          break;
          case "106":
          return redirect()->route('finalDocumentationList');
          break;
          case "107":
          return redirect()->route('finalApprovalList');
          break;
          case "108":
          return redirect()->route('wonList');
          break;
          case "109":
          return redirect()->route('closedList');
          break;    
        }         

      }
    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function reAssignPrevious(Request $request)
    {
//echo 'gggggg';dd($request); 
      $workflow_id = $request['workflow_id'];
      $stage = $request['workflow_id'];
      $roleid = $request['role'];
      $userid = $request['user_id'];

        //dd($workflow_id);
      $enquiry_id =  $request['enquiryid'];
      $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
      $previous = Sales::where('work_flow_processes_code',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();

      $previous->salesUser()->update(['status' => 0]);

      /*if($workflow_id == 109) {*/

        $previousStage = Sales::where('work_flow_processes_code','!=',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();

        $previousStage->salesUser()->update(['status' => 0]);
        $workflow_id = $previousStage->work_flow_processes_code;
       /* }
        $previousSatge = Sales::where('work_flow_processes_code','!=',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();
        dd($previousSatge);*/
        $sales = Sales::create([
          'sales_enquiry_id' =>$enquiry_id,
          'sales_type' => $enquiryDetails->sales_type,
          'work_flow_processes_code' => $workflow_id,
          'created_by' => \Auth::user()->id,
          ]);
        $sales->salesUsers()->attach($roleid,['user_id'=>$userid]);
        // To view the enquiry stage by assigned person
        $sales->salesUsers()->attach(\Auth::user()->getRoles(), ['user_id' => \Auth::user()->id]);

        
        SalesEnquiry::where('id', $enquiry_id)->update([
          'work_flow_processes_code' => $workflow_id,
          'updated_by' => \Auth::user()->id,
          ]);
        /****** Activity log *****/

        activity('Re Assigned')
        ->performedOn($sales)
        ->causedBy(\Auth::user()->id)
        ->withProperties($sales)
        ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Lead Re-assigned Successfully');
        
        clearNotification('Modules\Sales\Notifications\EnquiryNotification',$enquiry_id,true); 
        $this->assignNotify($enquiry_id,$workflow_id);
        
        
        //dd($workflow_id);
        switch ($stage) {
          case "101":
          return redirect()->route('leadAssign.index');
          break;
          case "102":
          return redirect()->route('leadAssign.assignedList');
          break;
          case "103":
          return redirect()->route('inprogressList');
          break;
          case "104":
          return redirect()->route('documentationList');
          break;
          case "105":
          return redirect()->route('preliminaryApprovalList');
          break;
          case "106":
          return redirect()->route('finalDocumentationList');
          break;
          case "107":
          return redirect()->route('finalApprovalList');
          break;
          case "108":
          return redirect()->route('wonList');
          break;
          case "109":
          return redirect()->route('closedList');
          break;    
        }         

      }
    /*
    *
    *
    * Re assign Employes
    *
    */
    public function reOpenModal($id,$workflow_id) {

      $enquiry_id = $id;
      $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
      $location = $enquiryDetails->locations()->first();

      if($location != null)
        $location_id = $location->id;
      else 
        $location_id = null;

      $priceRange = $enquiryDetails->priceRanges()->first();

      if($priceRange != null)
        $pricerange_id = $priceRange->id;
      else 
        $pricerange_id = null;

      $general =  new General;
      $users = array();
      $roles = array();
      $old = Sales::where('work_flow_processes_code','=',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();
      $closedRole = User::where('id',$old->created_by)->pluck('default_role');
      array_push($roles, $closedRole);

      $previous = Sales::where('work_flow_processes_code','!=',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();
      $previous->salesUser()->update(['status' => 0]);

      $current_stage = $general->workFlowProcessNames($previous->work_flow_processes_code);
      $current_stage = $current_stage->work_flow_processes_name;
      $current_stageCode = $previous->work_flow_processes_code;

      $previous_s = Sales::where('work_flow_processes_code',$previous->work_flow_processes_code)->where('sales_enquiry_id',$enquiry_id)->latest()->first();
      $previous_s->salesUser()->update(['status' => 0]);

      $processAssign = $general->roleUsersFromProcess($previous->work_flow_processes_code,$location_id,$pricerange_id,$tenant_status=0);
      $salesUsers =  $previous_s->salesUser; 
      
      if($processAssign) {

        $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
        $roles [] = $processAssign->assign->pluck('role_id'); /*->where('user_id',null)*/


            //$re = array_search('1', $roles);unset($roles[$re]);
        if(count($user_ids) > 0){

          $flattened = array_flatten($user_ids);
          $users   = array_unique($flattened,SORT_REGULAR); 
          $role = User::whereIn('id',$users)->pluck('default_role');

          /*$users =  User::whereIn('id',$users)->get();*/

        }

      }else {

        $previousProcess = $general->getPreviousOrder($current_stageCode);
        if($previousProcess !=0) {
          $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id,$pricerange_id,$tenant_status=0);

          $user_ids =   $previousAssign->assign->where('user_id','>',0)->pluck('user_id');
          $roles [] = $previousAssign->assign->pluck('role_id');

          if(count($user_ids) > 0){

            $flattened = array_flatten($user_ids);
            $users = array_unique($flattened,SORT_REGULAR); 
            $role = User::whereIn('id',$users)->pluck('default_role');


          }
        }else{
          $res =    $general->workFlowProcess($workflow_id);
          $roles[] = $res->default_role;
          if(empty($res->default_user_id)){      
            $users_list = \App\User::role($res->default_role)->get()->pluck('id');        
            $users = $users_list->toArray();       
          }else 
          $users[] = $res->default_user_id;
        }

      }
      $flattenedRoles = array_flatten($roles);
      $roles = array_unique($flattenedRoles,SORT_REGULAR);
      $roles = array_diff($roles, (is_array(1) ? 1 : array(1)));
      $roles = Role::whereIn('id', $roles)->get();
      $users =  array_diff($users, (is_array(1) ? 1 : array(1)));
      $users =  User::active()->whereIn('id',$users)->get();

      return view('sales::TenantSales.tenant_reopen_employee_modal',compact('users','roles','enquiry_id','workflow_id','current_stage','current_stageCode'));

    }
    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function reOpen(Request $request)
    {

      $workflow_id = $request['workflow_id'];
      $roleid = $request['role'];
      $userid = $request['user_id'];


      $enquiry_id =  $request['enquiryid'];
      $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
      $previous = Sales::where('work_flow_processes_code',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();

      $previous->salesUser()->update(['status' => 0]);
      $previous_assign = Sales::where('work_flow_processes_code','!=',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();

      $previous_assign->salesUser()->update(['status' => 0]);
      $workflow_id =  $previous_assign->work_flow_processes_code;

      $previous_assign = Sales::where('work_flow_processes_code',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();

      $previous_assign->salesUser()->update(['status' => 0]);
        /*if($workflow_id == 109) {
          
          $previous = Sales::where('work_flow_processes_code','!=',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();
          
          $previous->salesUser()->update(['status' => 0]);
          $workflow_id = $previous->work_flow_processes_code;
          if($workflow_id == 101) $workflow_id = 102;
        }*/
        $sales = Sales::create([
          'sales_enquiry_id' =>$enquiry_id,
          'sales_type' => $enquiryDetails->sales_type,
          'work_flow_processes_code' => $workflow_id,
          'created_by' => \Auth::user()->id,
          ]);
        $sales->salesUsers()->attach($roleid,['user_id'=>$userid]);
        // To view the enquiry stage by assigned person
        $sales->salesUsers()->attach(\Auth::user()->getRoles(), ['user_id' => \Auth::user()->id]);

        
        $enquiryDetails->update([
          'work_flow_processes_code' => $workflow_id,
          'assigned_person' => $userid,
          'updated_by' => \Auth::user()->id,
          ]);
        /****** Activity log *****/

        activity('Re Opened')
        ->performedOn($sales)
        ->causedBy(\Auth::user()->id)
        ->withProperties($sales)
        ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        //Notification 
        clearNotification('Modules\Sales\Notifications\EnquiryNotification',$enquiry_id,true); 
        $users_notify =  \App\User::whereIn('id',[$userid])->get(); 
        $enquiryDetails->text = "Enquiry - ".$enquiryDetails->sales_enquiry_no."  Re-Opened ";  
        $enquiryDetails->href = url('tenantNextstage/'.$enquiryDetails->id.'/'.$workflow_id);   
        event(new AssignedEnquiry($enquiryDetails,$users_notify)); 

        session()->flash('success', 'Lead Re-Opened Successfully');
        return redirect()->route('closedList');        

      }
    /*
    *
    * SAles Co-ordinator Assigned List
    *
    */
    public function salesAssignedList(Request $request)
    {

     $result = array();	
     
     if(isset($request->fieldName)){
       if(count($request->fieldName) > 0){			 
         $result = 	$this->enquirySearch($request);
       }
     }

     $request->flash();      


     $roles = \Auth::user()->getRoles();
        //dd($roles);
     $assigned_lists = Sales::whereHas('salesEnquiry', function ($query) use($result){
      $query->where('work_flow_processes_code', '=', 102)
      ->closure($result)  ;                      
    })     
     ->where('sales_type', '=', 1)
     ->where('created_by', '=', \Auth::user()->id)
     ->where('work_flow_processes_code', '=', 102)->paginate($this->noOfRecord);


     $enquiry_fields = [
     'sales_enquiry_no' => 'Enquiry No',
     'sales_enquiry_name' => 'Customer Name',
     'sales_email' => 'Email',
     'sales_building_name' => 'Building Name',
     'sales_mobile_no' => 'Mobile No',
     /*'sales_company_name' => 'Company Name',*/
     'sales_move_in_date' => 'Move In Date',
     'created_at' => 'Enquiry Date','location' => 'Location',

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


     return view('sales::TenantSales.tenant_sales_assigned_list',compact('assigned_lists','enquiry_fields','operations'));


   }
    /**
     * GroupAssign Feature.
     * @param  Request $request
     * @return Response
     * Author : Jackson
     */
    public function storeGroupAssign(Request $request,SalesEnquiry $salesEnquiry)
    {
      $action_key = $request['action_key']; 
      $workflow_id = $request['workflow_id'];
      $roleid = $request['role'];
      $userid = $request['user_id'];
        //dd($userid);
      /* Get Next Proess from action*/
      $general =  new General;
      $next_process_id = $general->nextProcessFromAction($workflow_id,$action_key);

      foreach($request['enquiryId'] as $enq){

        $enquiry_id =  $enq;

        $countSales = Sales::where('sales_enquiry_id',$enquiry_id)->count();
        if($countSales == 1) SalesEnquiry::where('id', $enquiry_id)->update([
          'enquiry_owner' => \Auth::user()->id,
          ]);
          $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
        $location = $enquiryDetails->locations()->first();
        $location_id = $location->id;
        $priceRange = $enquiryDetails->priceRanges()->first();
        $pricerange_id = $priceRange->id;

        $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id,$pricerange_id,$tenant_status=null);


        $sales = Sales::create([
          'sales_enquiry_id' =>$enquiry_id,
          'sales_type' => $enquiryDetails->sales_type,
          'work_flow_processes_code' => $next_process_id,
          'created_by' => \Auth::user()->id,
          ]);
        $sales->salesUsers()->attach($roleid,['user_id'=>$userid]);
        $sales->salesUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);



        /*$this->assignNotify($enquiry_id,$next_process_id);*/


        $enquiryDetails->update([
          'work_flow_processes_code' => $next_process_id,
          'assigned_person' => $userid,
          'updated_by' => \Auth::user()->id,
          ]);

          //Notification
        clearNotification('Modules\Sales\Notifications\EnquiryNotification',$enquiry_id,true); 
        $users_notify =  \App\User::whereIn('id',[$userid])->get(); 
        $usersList =  \App\User::whereIn('id',[$userid])->first(); 
        $mobile = $usersList->employee->employee_contact_no ?? $usersList->employee->employee_secondary_no;

        $msg = "Tenant Enquiry Assigned !";
        $params = 'optional data';

        $enquiryDetails->text = "Enquiry - ".$enquiryDetails->sales_enquiry_no."  Assigned ";
        $enquiryDetails->href = url('tenantNextstage/'.$enquiryDetails->id.'/'.$next_process_id);   

        //dd($enquiryDetails);  
        event(new AssignedEnquiry($enquiryDetails,$users_notify));   //Internal Notification
        if(!empty($usersList->email)){
      Mail::to($usersList->email)->send(new SalesAssignEmail($enquiryDetails)); //Email Notification
    }else{
       sendSms($mobile,$msg,$params); //SMS Notification
     }


   }
   /****** Activity log *****/

   activity('Group Assign')
   ->performedOn($sales)
   ->causedBy(\Auth::user()->id)
   ->withProperties($sales)
   ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

   session()->flash('success', 'Lead Assigned Successfully');
   return redirect()->route('leadAssign.index');

 }


 public function tenantUnassignedSearch(Request $request){

   $SalesEnquiry = 	 new SalesEnquiryController;   


   $roles = \Auth::user()->getRoles();
   $rolesNames = \Auth::user()->getRoleNames()->toArray();
   $unassigned_list = ViewTenantStage::where('sale_work_flow_processes_code', '=', 101)->where('status', '=', 1)->select("sales_enquiry_id","sales_enquiry_no","sales_enquiry_name","sales_mobile_no","created_at","sales_enquiry_name","work_flow_processes_code as sales_work_flow", DB::raw("string_agg(distinct unit_type, ',' ORDER BY unit_type) AS unit_type","sales_note"), DB::raw("string_agg(distinct cast(loc as text), ',') AS loc"),"sales_note" )->groupBy("sales_enquiry_id","sales_enquiry_no","created_at","sales_enquiry_name","work_flow_processes_code","sales_note","sales_mobile_no")
      ->filter($request);

  /*
   if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
    $unassigned_list->where(function ($query) use($roles){
        $query->where('user_id',null)
        ->whereIn('role_id', $roles);
      })
      ->orWhere(function ($query) use($roles){
        $query->where('user_id','>',0)
        ->whereIn('role_id', $roles)
        ->where('user_id','=', \Auth::user()->id);
      })                      
      ->where('status','=',1);                       

  } */
  $unassigned_lists = $unassigned_list->sortable()->paginate($this->noOfRecord);



  $route = 'leadAssign';

  if(isset($request->ajax))
   return view('sales::TenantSales.tenant_unassigned_list_ajax',compact('unassigned_lists','request','route'));
 else 
  return view('sales::TenantSales.tenant_unassigned_list',compact('unassigned_lists','enquiry_fields','operations'));

}


    /*
     * 
     *  Reminder Assign Enquiry 
     * 
     */
    public function assignReminder(SalesEnquiry $salesEnquiry){


     $sales =   Sales::where('sales_enquiry_id',$salesEnquiry->id)->latest()->first();     
     $salesUsers =  $sales->salesUser;    
     $users = array();     

     foreach($salesUsers as $val){

      if(empty($val->user_id)){

        $users_list = \App\User::role($val->role_id)->get()->pluck('id'); 				 
        $users_list = $users_list->toArray();	
        $users_list = array_flatten($users_list);   

        if(count($users) > 0){					 
         $users = array_merge($users,$users_list); 
       }else			 		 
       $users = $users_list;		

     }else 
     $users[] = $val->user_id;

   }	

   $users = array_flatten($users);   
   $users = array_where($users, function ($value, $key) {
    if($value != \Auth::user()->id)
      return true;
  });

	//  $users = array_flatten($users);   
	//  echo \Auth::user()->id;
	 //$users =  array_except($users, [\Auth::user()->id]);    

   $users_notify =  \App\User::whereIn('id',$users)->get();      
   event(new ReminderAssignEnquiry($salesEnquiry,$users_notify)); 

   session()->flash('success', 'Notification Send');
   return redirect()->back(); 


 }



    /**
     * Stage Notification  
     * 
     **/
    public function notifyUsers($next_process_id,$enquiryid,$msg = null){

      $general =  new General;
      $enquiryDetails = SalesEnquiry::where('id','=',$enquiryid)->first();
      $location = $enquiryDetails->locations()->first();

      if($location != null)
        $location_id = $location->id;
      else 
        $location_id = null;

      $priceRange = $enquiryDetails->priceRanges()->first();

      if($priceRange != null)
        $pricerange_id = $priceRange->id;
      else 
        $pricerange_id = null;

      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id,$pricerange_id,$tenant_status=null);
      
      $users = array();     
      if($processAssign){
        $salesUsers =  $processAssign->assign;
        foreach($salesUsers as $val){

          if(empty($val->user_id)){

           $users_list = \App\User::role($val->role_id)->get()->pluck('id');         
           $users_list = $users_list->toArray();  
           $users_list = array_flatten($users_list);      

           if(count($users) > 0){          
            $users = array_merge($users,$users_list); 
          }else           
          $users = $users_list; 

        }else 
        $users[] = $val->user_id;

      }
    }else {
      $sales =   Sales::where('sales_enquiry_id',$enquiryid)->latest()->first();     
      $salesUsers =  $sales->salesUser; 
      foreach($salesUsers as $val){

        if(empty($val->user_id)){

         $users_list = \App\User::role($val->role_id)->get()->pluck('id');         
         $users_list = $users_list->toArray();  
         $users_list = array_flatten($users_list);      

         if(count($users) > 0){          
          $users = array_merge($users,$users_list); 
        }else           
        $users = $users_list; 

      }else 
      $users[] = $val->user_id;

    } 
  }

  $users = array_flatten($users);          
  $users_notify =  \App\User::whereIn('id',$users)->get();


  $enquiry =  SalesEnquiry::find($enquiryid);	 
  $processes = $general->workFlowProcessNames($next_process_id);

  $message = $processes->work_flow_processes_name;
  $enquiry->msg = $msg." ".$message;
  $enquiry->href = url('tenantNextstage/'.$enquiry->id.'/'.$next_process_id);
  if($enquiry->sales_type == 2){

   switch ($next_process_id) { 

    case "201":
    $enquiry->href = url('landlordNextstage/'.$enquiry->id.'/'.$next_process_id);
    break;
	case "202":
    $enquiry->msg = $msg." ".$message." By ".\Auth::user()->employee->employee_name;
    $enquiry->href = url('contractGeneration/'.$enquiry->id);
    break;
    case "203":
    $enquiry->href = url('landlordcontractApprovalInfo/'.$enquiry->id.'/'.$next_process_id);        
    break;
    case "205":
    $enquiry->href = url('landlordNextstage/'.$enquiry->id.'/'.$next_process_id);        
    break;
    default: return true;     

  } 
}

event(new StageEnquiry($enquiry,$users_notify));      

return true;  

}


    /*
     * Assign Notify
     *  
     */
    public function assignNotify($enquiryid,$next_process_id){

      $sales =   Sales::where('sales_enquiry_id',$enquiryid)->latest()->first();     
      $salesUsers =  $sales->salesUser; 


      $users = array();     

      foreach($salesUsers as $val){

        if(empty($val->user_id)){

          $users_list = \App\User::role($val->role_id)->get()->pluck('id'); 				 
          $users_list = $users_list->toArray();	
          $users_list = array_flatten($users_list);      

          if(count($users) > 0){	  			 
           $users = array_merge($users,$users_list); 
         }else			 		 
         $users = $users_list;	

       }else 
       $users[] = $val->user_id;

     }

     $users = array_flatten($users);   
     $users = array_diff($users, array(\Auth::user()->id));         
     $users_notify =  \App\User::whereIn('id',$users)->get();

     $usersList =  \App\User::whereIn('id',[$users])->first(); 
        $mobile = $usersList->employee->employee_contact_no ?? $usersList->employee->employee_secondary_no;

        $msg = "Tenant Enquiry ReAssigned !";
        $params = 'optional data';


     clearNotification('Modules\Sales\Notifications\EnquiryNotification',$enquiryid,true);
     $enquiryDetails =  SalesEnquiry::find($enquiryid);	 	
     $enquiryDetails->href = url('tenantNextstage/'.$enquiryDetails->id.'/'.$next_process_id);
     $enquiryDetails->text = "Enquiry - ".$enquiryDetails->sales_enquiry_no."  ReAssigned "; 
     event(new AssignedEnquiry($enquiryDetails,$users_notify));//Internal Notification
        if(!empty($usersList->email)){
      Mail::to($usersList->email)->send(new SalesAssignEmail($enquiryDetails)); //Email Notification
    }else{
       sendSms($mobile,$msg,$params); //SMS Notification
     }

   }
  /*
  *
  * usersListByRole
  *
  */
  public function usersListByRole(Request $request) {

    $id = $request->input('id');
    $workflow_id = $request->input('workflow_id'); 
    $enquiryid = $request->input('enquiryid');
    $enquiryDetails = SalesEnquiry::where('id','=',$enquiryid)->first();
    $location = $enquiryDetails->locations()->first();

    if($location != null)
      $location_id = $location->id;
    else 
      $location_id = null;

    $priceRange = $enquiryDetails->priceRanges()->first();

    if($priceRange != null)
      $pricerange_id = $priceRange->id;
    else 
      $pricerange_id = null;

    $general =  new General;
    $users = array();

    $processAssign = $general->roleUsersFromProcess($workflow_id,$location_id,$pricerange_id,$tenant_status=0); 

    if($processAssign) {

      $user_ids =   $processAssign->assign->where('user_id','>',0)->where('role_id',$id)->pluck('user_id');
      $roles = $processAssign->assign;/*->where('user_id',null)*/
      $roles = $roles->pluck('role_id');

      if(count($user_ids) > 0){

        $flattened = array_flatten($user_ids);
        $users = array_unique($flattened,SORT_REGULAR);
        $roles = User::whereIn('id',$users)->pluck('default_role');

        /*$users =  User::whereIn('id',$users)->get();*/

      }else {
        $users =  User::where('default_role',$id)->pluck('id');
      }

    }else {

      $previousProcess = $general->getPreviousOrder($workflow_id);
      if($previousProcess !=0) {
        $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id,$pricerange_id,$tenant_status=0);

        $user_ids =   $previousAssign->assign->where('user_id','>',0)->where('role_id',$id)->pluck('user_id');
        $roles  = $previousAssign->assign->pluck('role_id');

        if(count($user_ids) > 0){

          $flattened = array_flatten($user_ids);
          $users = array_unique($flattened,SORT_REGULAR); 
          $role = User::whereIn('id',$users)->pluck('default_role');


        }else {
          $users =  User::where('default_role',$id)->pluck('id');
        }
      }else{
        $res =    $general->workFlowProcess($workflow_id);
        $roles[] = $res->default_role;
        if(empty($res->default_user_id)){      
          $users_list = \App\User::role($res->default_role)->get()->pluck('id');        
          $users = $users_list->toArray();       
        }else 
        $users[] = $res->default_user_id;
      }

    }
    $flattenedusers = array_flatten($users);
    $users = array_unique($flattenedusers,SORT_REGULAR);
    $users =  array_diff($users, (is_array(1) ? 1 : array(1)));


    $usersList =\App\User::active()->whereIn('id',$users)
    ->where('user_type','employee')
    ->whereHas('employee', function ($query){
      $query->active();                      
    })                             
    ->get();       


    $usersList->each(function ($item, $key) {
      $item->username = $item->employee->employee_name ;
    });

    return json_encode($usersList);
  }
  /*
  *
  * usersListByRole
  *
  */
  public function usersByRole(Request $request) {

    $id = $request->input('id');
    $workflow_id = $request->input('workflow_id'); 
    $enquiryid = $request->input('enquiryid');
    foreach($enquiryid as $enquiry_id){
      $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
      $location = $enquiryDetails->locations()->first();

      if($location != null)
        $location_id = $location->id;
      else 
        $location_id = null;

      $priceRange = $enquiryDetails->priceRanges()->first();

      if($priceRange != null)
        $pricerange_id = $priceRange->id;
      else 
        $pricerange_id = null;

      $general =  new General;
      $users = array();

      $processAssign = $general->roleUsersFromProcess($workflow_id,$location_id,$pricerange_id,$tenant_status=0); 
      
      if($processAssign) {

        $user_ids =   $processAssign->assign->where('user_id','>',0)->where('role_id',$id)->pluck('user_id');
        $roles = $processAssign->assign;/*->where('user_id',null)*/
        $roles = $roles->pluck('role_id');

        if(count($user_ids) > 0){

          $flattened = array_flatten($user_ids);
          $users = array_unique($flattened,SORT_REGULAR);
          $roles = User::whereIn('id',$users)->pluck('default_role');

          /*$users =  User::whereIn('id',$users)->get();*/

        }else {
          $users =  User::where('default_role',$id)->pluck('id');
        }

      }else {

        $previousProcess = $general->getPreviousOrder($workflow_id);
        if($previousProcess !=0) {
          $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id,$pricerange_id,$tenant_status=0);

          $user_ids =   $previousAssign->assign->where('user_id','>',0)->where('role_id',$id)->pluck('user_id');
          $roles  = $previousAssign->assign->pluck('role_id');

          if(count($user_ids) > 0){

            $flattened = array_flatten($user_ids);
            $users = array_unique($flattened,SORT_REGULAR); 
            $role = User::whereIn('id',$users)->pluck('default_role');


          }else {
            $users =  User::where('default_role',$id)->pluck('id');
          }
        }else{
          $res =    $general->workFlowProcess($workflow_id);
          $roles[] = $res->default_role;
          if(empty($res->default_user_id)){      
            $users_list = \App\User::role($res->default_role)->get()->pluck('id');        
            $users = $users_list->toArray();       
          }else 
          $users[] = $res->default_user_id;
        }

      }
    }

    $flattenedusers = array_flatten($users);
    $users = array_unique($flattenedusers,SORT_REGULAR);
    $users =  array_diff($users, (is_array(1) ? 1 : array(1)));


    $usersList =\App\User::active()->whereIn('id',$users)
    ->where('user_type','employee')
    ->whereHas('employee', function ($query){
      $query->active();                      
    })                             
    ->get();       


    $usersList->each(function ($item, $key) {
      $item->username = $item->username ;
    });

    return json_encode($usersList);
  }
  /*
  *
  *
  * Multiple Accept- reject documentation
  *
  */
  public function acceptReject(Request $request){
    $id = $request->input('id');
    $status = $request->input('status'); 
    $enquiryid = $request->input('enqId');
    TenantContract::where('id',$id)->update(['accept_reject_status'=>$status]);
    $counts = TenantContract::pluck('accept_reject_status');
    $ar_status = $counts->contains(2); 
    return json_encode($ar_status);
  }
  /*
  *
  *
  * Group Re assign Employes
  *
  */
  public function groupReAssignModal(Request $request) {

    $workflow_id = $request->wrk_flow;
    $enqueryArr = $request->id;
    $general =  new General;
    $users = array();
    $userRoles = array();
      /*foreach($enqueryArr as $enq){                     
        $enquiry_id     = $enq;
        $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
        $location       = $enquiryDetails->locations()->first();
        $location_id    = $location->id;

        
        $priceRange     = $enquiryDetails->priceRanges()->first();
        $pricerange_id  = $priceRange->id;

        if($workflow_id == 107 || $workflow_id == 105){
         $previousProcess = $general->getPreviousOrder($workflow_id);
         
          $workflow_id = $previousProcess;
        }
        $processAssign = $general->roleUsersFromProcess($workflow_id,$location_id,$pricerange_id,$tenant_status=0); 
        if($processAssign) {
          
            $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
            $roles = $processAssign->assign; 
            $roles = $roles->pluck('role_id');
            array_push($userRoles,$roles);
            
            if(count($user_ids) > 0){

              $flattened = array_flatten($user_ids);
              $users = array_unique($flattened,SORT_REGULAR); 
              $role = User::whereIn('id',$users)->pluck('default_role');
              $users =  array_diff($users, (is_array(1) ? 1 : array(1)));
              $users =  User::whereIn('id',$users)->get();
                            
            }
            
        }else {

            $previousProcess = $general->getPreviousOrder($workflow_id);
            $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id,$pricerange_id,$tenant_status=0);
            
            $user_ids =   $previousAssign->assign->where('user_id','>',0)->pluck('user_id');
            $roles = $previousAssign->assign; 
            $roles = $roles->pluck('role_id');
            array_push($userRoles,$roles);
            
            if(count($user_ids) > 0){

              $flattened = array_flatten($user_ids);
              $users = array_unique($flattened,SORT_REGULAR); 
              $role = User::whereIn('id',$users)->pluck('default_role');
              $users =  array_diff($users, (is_array(1) ? 1 : array(1)));
              $users =  User::whereIn('id',$users)->get();
                            
            }
          
        }
      }*/
      $user = array();
      foreach($enqueryArr as $enq){                     
        $enquiry_id     = $enq;
        $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
        $location       = $enquiryDetails->locations()->first();
        $location_id    = $location->id;

        //$next_process_id =102;
        $priceRange     = $enquiryDetails->priceRanges()->first();
        $pricerange_id  = $priceRange->id;

        $general          = new General;
        

        $processAssign    = $general->roleUsersFromProcess($workflow_id,$location_id,$pricerange_id,$tenant_status=0);
        //dd($processAssign->pluck('a')->toArray()); 
        

        if($processAssign) {

          $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
          $roles = $processAssign->assign->where('user_id',null);
          $roles = $roles->pluck('role_id');
          //dd(count($user_ids));
          if(count($roles) > 0){

            $users =  User::role($roles)->pluck('id'); 

            $users_count = count($users, COUNT_RECURSIVE)+1;
            if(count($user_ids)>0) {
              $users = array_add($users, $users_count, $user_ids); 
            }


          }else{
            $users = $user_ids;
          } 
          $Fullusers = array_flatten($users); 
          array_push($user,$Fullusers); 
          //dd($users);
          /*$users =  User::whereIn('id',$users)->where('user_type','employee')->whereHas('employee')->get()->toArray(); 
          if(count($users)> 0){
          foreach($users as $key=>$list){
                
                $empInfo = Employee::where('id', '=',$list['user_type_id'])->first();
             
                $users[$key]['enquiry_id'] = $enquiry_id;
                $users[$key]['employee_name'] = $empInfo->employee_name;
                $users[$key]['employee_picture'] = $empInfo->employee_picture;
           }
         }*/
       } else {

        $previousProcess = $general->getPreviousOrder($workflow_id);
        $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id,$pricerange_id,$tenant_status=0);

        $user_ids =   $previousAssign->assign->where('user_id','>',0)->pluck('user_id');
        $roles = $previousAssign->assign->where('user_id',null); 
        $roles = $roles->pluck('role_id');


        if(count($roles) > 0){

          $users =  (array)User::role($roles)->pluck('id'); 
          $users = array_collapse($users,$user_ids);            

        }else{
          $users = $user_ids;
        }  
        $Fullusers = array_flatten($users); 
        array_push($user,$Fullusers);

            //$previous_work_flow_order_code = $general->getPreviousProcessOrder($workflow_id);
            //dd($previous_work_flow_order_code);
        $previousSales = Sales::with('salesUser')
        ->where('work_flow_processes_code', '=', $workflow_id)
        ->where('sales_enquiry_id',$enquiry_id)->first();

      }

    }$u = array_flatten($user); 
    $users =  User::active()->whereIn('id',$u)->where('user_type','employee')->whereHas('employee', function ($query){
      $query->active();                      
    })->get()->toArray();
    if(count($users)> 0){
      foreach($users as $key=>$list){

        $empInfo = Employee::where('id',$list['user_type_id'])->first();

        $users[$key]['enquiry_id'] = $enquiry_id;
        $users[$key]['employee_name'] = $empInfo->employee_name;
        $users[$key]['employee_picture'] = $empInfo->employee_picture;
      }
    }
      /*$roles =  array_diff($userRoles, (is_array(1) ? 1 : array(1)));
      $roles        = Role::whereIn('id', $userRoles)->get();*/
      return view('sales::TenantSales.tenant_group_reassign_employee_modal',compact('users','roles','enqueryArr','workflow_id'));

    }
 /**
  * Store a newly created resource in storage.
  * @param  Request $request
  * @return Response
  */
 public function storeGroupReAssign(Request $request)
 {

  $workflow_id = $request['workflow_id'];
  $stage = $workflow_id;
  $roleid = $request['role'];
  $userid = $request['user_id'];

        //dd($workflow_id);
  $enquiryArr =  $request['enquiryid'];
  foreach ($enquiryArr as $enquiry) {

    $enquiry_id = $enquiry;
    $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
    $previous = Sales::where('work_flow_processes_code',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();
          //dd($previous);
    $previous->salesUser()->update(['status' => 0]);

    if($workflow_id == 109) {

      $previous = Sales::where('work_flow_processes_code','!=',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();

      $previous->salesUser()->update(['status' => 0]);
      $workflow_id = $previous->work_flow_processes_code;
      if($workflow_id == 101) $workflow_id = 102;
    }
    $sales = Sales::create([
      'sales_enquiry_id' =>$enquiry_id,
      'sales_type' => $enquiryDetails->sales_type,
      'work_flow_processes_code' => $workflow_id,
      'created_by' => \Auth::user()->id,
      ]);
    $sales->salesUsers()->attach($roleid,['user_id'=>$userid]);
          // To view the enquiry stage by assigned person
    $sales->salesUsers()->attach(\Auth::user()->getRoles(), ['user_id' => \Auth::user()->id]);


    SalesEnquiry::where('id', $enquiry_id)->update([
      'work_flow_processes_code' => $workflow_id,
      'assigned_person' => $userid,
      'updated_by' => \Auth::user()->id,
      ]);

        //  readNotification('Modules\Sales\Notifications\EnquiryNotification',$enquiry_id);  
    clearNotification('Modules\Sales\Notifications\EnquiryNotification',$enquiry_id,true);
    $this->assignNotify($enquiry_id,$workflow_id);
  }
  /****** Activity log *****/

  activity('Re Assigned')
  ->performedOn($sales)
  ->causedBy(\Auth::user()->id)
  ->withProperties($sales)
  ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

  session()->flash('success', 'Lead Re-assigned Successfully');
       //dd($workflow_id);
  switch ($stage) {
    case "101":
    return redirect()->route('leadAssign.index');
    break;
    case "102":
    return redirect()->route('leadAssign.assignedList');
    break;
    case "103":
    return redirect()->route('inprogressList');
    break;
    case "104":
    return redirect()->route('documentationList');
    break;
    case "105":
    return redirect()->route('preliminaryApprovalList');
    break;
    case "106":
    return redirect()->route('finalDocumentationList');
    break;
    case "107":
    return redirect()->route('finalApprovalList');
    break;
    case "108":
    return redirect()->route('wonList');
    break;
    case "109":
    return redirect()->route('closedList');
    break;    
  }         

}

}
