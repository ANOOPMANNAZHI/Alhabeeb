<?php

namespace Modules\Sales\Http\Controllers;

use Modules\Sales\Entities\Sales;
use Modules\Sales\Entities\SalesUsers;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Sales\Entities\SalesNote;
use Modules\Sales\Entities\SalesActivity;
use Modules\General\Entities\WorkFlowProcess;
use Modules\Masters\Entities\Employee;
use Modules\Masters\Entities\Building;
use Modules\Sales\Entities\TenantContract;
use Modules\Sales\Entities\Tenant;
use Spatie\Permission\Models\Role;
use Modules\Masters\Entities\Vendor;
use Modules\Sales\Entities\LandlordContract;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\General\Http\Controllers\GeneralController as General ;
use Modules\Sales\Http\Controllers\TenantStageController as TenantStage ;
use Carbon\Carbon;
use Dynamics;

class LandlordStageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function __construct()
    {
        $this->middleware('auth');  
        $this->middleware('permission:landlordlead_list', ['only' => ['landlordLead']]);
        $this->middleware('permission:landlordcontract_list', ['only' => ['landlordContract']]);
        $this->middleware('permission:contractapprovalloss_list', ['only' => ['contractApprovalLoss']]);  
        $this->middleware('permission:landlordwon_list', ['only' => ['contractApprovalWon']]);
        $this->middleware('permission:landlordpendingapproval_list', ['only' => ['landlordPendingApproval']]);
		$this->middleware('permission:landlorddocumentation_approve_list ', ['only' => ['contractApprovalList']]);
        $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value;     
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request,$ajax = false)
    {

        $enquiry_fields = [
           'sales_enquiry_no' => 'Enquiry No',
           'sales_enquiry_name' => 'Customer Name',
           'sales_email' => 'Email',
           'sales_building_name' => 'Building Name',
           'sales_mobile_no' => 'Mobile No',
           'sales_company_name' => 'Company Name',
           'created_at' => 'Enquiry Date',
           'location' => 'Region',  
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
      
       $return_flag = true;
			 
		   $request->flash();       
        
        $roles = \Auth::user()->getRoles();
        //dd((in_array('1', $roles)));
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
        $leads = Sales::whereHas('salesEnquiry', function ($query)use($request){
                $query->where('work_flow_processes_code', '=', 201)
                      ->filter($request);                                                       
            })->where('sales.sales_type', '=', 2)->where('sales.work_flow_processes_code', '=', 201)
            ->whereHas('salesUsers', function ($query) {
              $query->where('status','=',1);
            });

        if (in_array('super_admin', $rolesNames) === false) {
          
          $leads->whereHas('salesUsers', function ($query) use($roles) {
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
        
        $lead = $leads->sortable()->paginate($this->noOfRecord);   
        
        $stages = WorkFlowProcess::where('work_flows_id',1)->get();
   
        if($ajax)
          return([$lead ,$enquiry_fields,$operations,$stages]);

        $quick_url = route('landlord.landlordSearch');
        
        return view('sales::LandlordSales.landlord_lead_list',compact('lead','enquiry_fields','operations','stages','request','quick_url'));
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
    public function store(Request $request)
    {

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
    public function edit()
    {
        return view('sales::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /*
    *
    * Close Landlord
    *
    */
    public function close($id,Request $request,SalesEnquiry $salesEnquiry) {

        $action_key     =   $request['action_key']; 
        $workflow_id    =  $request['workflow_id'];

        $general =  new General;
        $next_process_id = $general->nextProcessFromAction($workflow_id,$action_key);

        $enquiryDetails = SalesEnquiry::where('id','=',$id)->first();
        $location = $enquiryDetails->locations()->first();
        $location_id = $location->id;
        $priceRange = $enquiryDetails->priceRanges()->first();
        $pricerange_id = $priceRange->id;

        /* Get Next Proess from action*/

        $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id,$pricerange_id,$tenant_status=null);
        

        $sales = new Sales; 
        $sales->sales_enquiry_id            = $id;
        $sales->sales_type                  = $enquiryDetails->sales_type;
        $sales->work_flow_processes_code    = $next_process_id;
        $sales->created_by                  = \Auth::user()->id;
        $sales->save();
       // echo'<pre>';
        foreach($processAssign->assign as $val){
           $sales->salesUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
           $sales->save();
        } 
       
        $salesEnquiry::where('id', $id)->update([
          'work_flow_processes_code' => $next_process_id,
          'updated_by' => \Auth::user()->id,
        ]);
		 activity('Landlord Lead Closed')
          ->performedOn($sales)
          ->causedBy(\Auth::user()->id)
          ->withProperties($salesEnquiry)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Lead Closed Successfully');
        return redirect()->route('landlordLead.index');
    }
    /*
    *
    * Next Stage Landord
    *
    */
    public function nextStage($id,$stage) {
      
      readNotification('Modules\Sales\Notifications\EnquiryNotification',$id);
      clearNotification('Modules\Sales\Notifications\EnquiryNotification',$id); 
        
       $details = Sales::whereHas('salesEnquiry', function ($query) use($stage,$id) {
                $query->where('work_flow_processes_code', '=', $stage)
                ->where('sales_enquiry_id','=',$id);                      
            })     
              ->where('sales_type', '=', 2)
              ->where('work_flow_processes_code', '=', $stage)
              ->first();
        //dd($details)      ;
        // If the enquiry is moved to other stage 
        if(empty($details->sales_enquiry_id))
				return redirect()->route('dashboard');
				
        $salesNoteList = Sales::where('sales_enquiry_id',$details->sales_enquiry_id)->get();
		$landlordContractInfo = null;
        
        $salesNotes = SalesNote::whereHas('sales', function ($query) use($id) {
                $query->where('sales_enquiry_id', '=', $id);                      
            })->get();

       // $tenantCcontracts = Tenant::with('tenantContract','tenantDocument')->where('tenant_status','=',0)->get();
  
        $salesActivities = SalesActivity::whereHas('sales', function ($query) use($id) {
                $query->where('sales_enquiry_id', '=', $id);                      
            })->where('sales_activities_status','<',3)
              ->get();

        $salesClosedActivities = SalesActivity::whereHas('sales', function ($query) use($id) {
                $query->where('sales_enquiry_id', '=', $id);                      
            })->where('sales_activities_status','=',3)
              ->get();
        $tenant = Tenant::where('tenant_status','=',0)->first();         
        $stage = $stage;
		
        switch ($stage) {
            case "201":
                return view('sales::LandlordSales.landlord_accept',compact('details','stage','landlordContractInfo','salesNoteList'));
                break;
                
            case "205":
				
                $landlordContractInfo = LandlordContract::where('sale_enquiry_id', $id)->first();
			
                return view('sales::LandlordSales.landlord_accept',compact('details','stage','landlordContractInfo','salesNoteList'));
                break;
            
            default:
                
                return redirect()->route('dashboard');
                break;
                        
        }
 
    }
	/*
        Landlord Accept Action
    */
    public function nextStageAction(Request $request,SalesEnquiry $salesEnquiry)
    {
        $action_key                 = $request['action_key']; 
        $work_flow_processes_code   = $request['workflow_id'];
		
        /* Get Next Proess from action*/
        $general =  new General;
        $next_process_id = $general->nextProcessFromAction($work_flow_processes_code,$action_key);
		//dd($next_process_id);
        $enquiry_id =  $request['enquiryid'];
		$note   = $request['sales_lead_note_name'];
       

        $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
        
        $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);
        
		$previous = Sales::with('salesUser')->where('work_flow_processes_code',$work_flow_processes_code)
                          ->where('sales_enquiry_id',$enquiry_id)->latest()->first();
						  
		$landLordCon = LandlordContract::where('sale_enquiry_id','=',$enquiry_id)->first();
        // $enquiryDetails->sales_enquiry_direct_contract - Direct case
        if($action_key == 'RJCT' && $work_flow_processes_code == 203 && $enquiryDetails->sales_enquiry_direct_contract == 2){

          LandlordContract::where('sale_enquiry_id','=',$enquiry_id)->update(['landlord_contract_status'=> '2']);
        }
		// TO UPDATE AXVendor	
		if(AX_ENABLE_DISABLE == 1 && $next_process_id == 204){	
			$vendorInfo  = Vendor::where('id',$landLordCon->vendor_id)->first();
			// Isvendor Exit or not in AX
			if(Dynamics::VendorIsExitAxPushData('isVendorExistFunc',$vendorInfo)==false){
						// Update to AX
				if(Dynamics::VendorAxPushData('AXVendor', $vendorInfo)=='Error')
					return Redirect::back()->withMessage('error', 'Microsoft Dynamics API Service Error');
			}
		
		}
		//dd($previous);
      	if(!empty($previous)){
            $previous->salesUser()->update(['status' => 0]);
            $previous->update(['sales_notes' =>$note]);
        }

		/*if($next_process_id == 205 || $action_key == 'RJCT' || $action_key == 'ACPT'){
          	$previous = Sales::with('salesUser')->where('work_flow_processes_code',$next_process_id)
                          ->where('sales_enquiry_id',$enquiry_id)->latest()->first();
          	
        //dd($previous);
          if(!empty($previous)){
            $previous->salesUser()->update(['status' => 0]);}
        }*/
        
       
        

        $sales = new Sales; 
        $sales->sales_enquiry_id        = $enquiry_id;
        $sales->sales_type              = $enquiryDetails->sales_type;
        $sales->work_flow_processes_code= $next_process_id;
        $sales->created_by              = \Auth::user()->id;
        //if($note){$sales->sales_notes   = $note;}
        $sales->save();


        if(!empty($processAssign)) {

          foreach($processAssign->assign as $val){
             $sales->salesUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
             $sales->save();
          }

        } else {

         // $previous_work_flow_order_code = $general->getPreviousProcessOrder($work_flow_processes_code);
          $previousSales = Sales::with('salesUser')
                ->where('work_flow_processes_code', '=', $work_flow_processes_code)
                ->where('sales_enquiry_id',$enquiry_id)->first();
          //dd($Sales->salesUser);      
          foreach($previousSales->salesUser as $val){
             $sales->salesUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
             $sales->save();
          }
          /*$sales->salesUsers()->attach(\Auth::user()->getRoles(), ['user_id' => \Auth::user()->id]);*/

        }
        $sales->salesUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
        $sales->save();
        $salesEnquiry::where('id', $enquiry_id)->update([
          'work_flow_processes_code' => $next_process_id,
          'updated_by' => \Auth::user()->id,
        ]);

        //dd($previous);
        if($next_process_id == 204){
          
          LandlordContract::where('sale_enquiry_id','=',$enquiry_id)->update(['landlord_contract_status'=> '1']);
          $landLordCon = LandlordContract::where('sale_enquiry_id','=',$enquiry_id)->first();
          Building::where('id',$landLordCon->building_id)->update(['building_status'=>1]);
          

        }
      /* Notification */
     // if($next_process_id != 202){
        $StageNotify =  new TenantStage;
        $StageNotify->notifyUsers($next_process_id,$enquiry_id);
    //  }
		//Log
		 $process = $general->workFlowProcessNames($next_process_id);

		 activity($process->work_flow_processes_name)
          ->performedOn($sales)
          ->causedBy(\Auth::user()->id)
          ->withProperties($sales)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        $stage = $next_process_id;

        switch ($work_flow_processes_code) {
            case "201":
                return redirect()->route('landlordLead.index');
                break;
            case "202":
                return redirect()->route('landlordContract.index');
                break;
            case "203":
                return redirect()->route('contractApprovalList');
                break;
            case "204":
                return redirect()->route('contractApprovalWon');
                break;
            case "205":
                return redirect()->route('contractApprovalLoss');
                break;
            
        }
    }
    /*
    *
    * Accept Action
    *
    *
    */
    public function approvalAccept(Request $request) {
      
      $workflow_id  = $request['workflow_id'];
      $sales_id     = $request['sales_id'];
      $enquiryid    = $request['enquiryid'];
      $action_key   = $request['action_key'];

      return view('sales::LandlordSales.sales_lead_landlord',compact('workflow_id','sales_id','enquiryid','action_key'));

    }
    /*
    *
    *
    * Re assign Employes
    *
    */
    public function reAssignLandlordModal($id,$workflow_id) {

       $enquiry_id = $id; 
        $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
        $general =  new General;
        $users = array();
        
        $previous = Sales::where('work_flow_processes_code','!=',$workflow_id)->where('sales_enquiry_id',$enquiry_id)->latest()->first();
        $previous->salesUser()->update(['status' => 0]);
        
        $current_stage = $general->workFlowProcessNames($previous->work_flow_processes_code);
        $current_stage = $current_stage->work_flow_processes_name;

        $previous_s = Sales::where('work_flow_processes_code',$previous->work_flow_processes_code)->where('sales_enquiry_id',$enquiry_id)->latest()->first();
        $previous_s->salesUser()->update(['status' => 0]);

        $processAssign = $general->roleUsersFromProcess($previous->work_flow_processes_code,$location_id=null,$pricerange_id=null,$tenant_status=0);

        if($processAssign) {
          
            $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
            $roles = $processAssign->assign; /*->where('user_id',null)*/
            $roles = $roles->pluck('role_id');
            
            //$re = array_search('1', $roles);unset($roles[$re]);
            if(count($user_ids) > 0){

              $flattened = array_flatten($user_ids);
              $users = array_unique($flattened,SORT_REGULAR); 
              $role = User::whereIn('id',$users)->pluck('default_role');
              $users =array_except($users, 1);
              $users =  User::whereIn('id',$users)->get();
                            
            }
            
        }else {

            $previousProcess = $general->getPreviousOrder($workflow_id);
            if($previousProcess !=0) {
              $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=null,$pricerange_id=null,$tenant_status=0);
            
              $user_ids =   $previousAssign->assign->where('user_id','>',0)->pluck('user_id');
              $roles = $previousAssign->assign; /*->where('user_id',null)*/
              $roles = $roles->pluck('role_id');
              
              //$re = array_search('1', $roles);unset($roles[$re]);
              if(count($user_ids) > 0){

                $flattened = array_flatten($user_ids);
                $users = array_unique($flattened,SORT_REGULAR); 
                $role = User::whereIn('id',$users)->pluck('default_role');
                $users =array_except($users, 1);
                $users =  User::whereIn('id',$users)->get();
                              
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
        $roles = array_except($roles, 1);
        $roles        = Role::whereIn('id', $roles)->get();
 
      return view('sales::LandlordSales.landlord_reassign_employee_modal',compact('users','roles','enquiry_id','workflow_id'));
       
    }
    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function reAssignLandlord(Request $request)
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
    
        
        SalesEnquiry::where('id', $enquiry_id)->update([
          'work_flow_processes_code' => $workflow_id,
          'updated_by' => \Auth::user()->id,
        ]);
        /****** Activity log *****/

          activity('Re Opened')
            ->performedOn($sales)
            ->causedBy(\Auth::user()->id)
            ->withProperties($sales)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Lead Re-Opened Successfully');
        
        /*$this->notifyUsers($workflow_id,$enquiry_id);*/
        //$this->assignNotify($enquiry_id,$workflow_id);
        
       return redirect()->route('contractApprovalLoss');        
       
    }
    
    
    
    
    public function landlordSearch(Request $request){
         
    $request->flash();      
     
    $quick_url = route('landlord.landlordSearch');
    $route = route('landlordLead.index');
       
    list($lead ,$enquiry_fields,$operations,$stages) = $this->index($request,true);    
      
    if(isset($request->ajax))
    return view('sales::LandlordSales.landlord_lead_list_ajax',compact('lead','request','route'));
       else  
    return view('sales::LandlordSales.landlord_lead_list',compact('lead','enquiry_fields','operations','stages','quick_url'));
	
	}
	
	
	
	
	 /*
  *
  *
  * Enquiry Landlord Serach
  *
  */
  public function landlordAjaxSearch(Request $request) {

    $customer_name = $request->customer_name;
    $email = $request->email;
    $phone = $request->phone;
    $status = $request->stages;
     
    $route = $request->route;
    
        $roles = \Auth::user()->getRoles();
        
        $request->flash(); 
        
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
        $leads = Sales::whereHas('salesEnquiry', function ($query)use($result){
                       $query->where('work_flow_processes_code', '=', 201);                                                                         
					  })
					  ->where('sales_type', '=', 2)->where('work_flow_processes_code', '=', 201)
					 ->whereHas('salesUsers', function ($query) {
					    $query->where('status','=',1);
					 })->when($customer_name, function ($query, $customer_name) {
						  return $query->where('sales_enquiry_name','ilike',$customer_name.'%');
					  })
					  ->when($email, function ($query, $email) {
						  return $query->where('sales_email', 'ilike', $email.'%');
					  })
					  ->when($phone, function ($query, $phone) {
						  return $query->where('sales_mobile_no', 'ilike', $phone.'%');
					  })  
					  ->when($status, function ($query, $status) {
						  return $query->where('work_flow_processes_code', $status);
					  });

        if (in_array('super_admin', $rolesNames) === false) {
          
          $leads->whereHas('salesUsers', function ($query) use($roles) {
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
        
        
        $lead = $leads->paginate(10); dd($lead);        
    
   
    
   return view('sales::enquiry_list_search',compact('enquiries','type','request','stages'));

  }
   
  /**
    * Landlord contract Approval list
    * @return value
   */
  public function landlordPendingApproval(Request $request){

        $enquiry_fields = [
           'landlord_contract_no' => 'Agreement No',                 
           'buildingInfo__building_name'      => 'Building Name',
           'vendorName__vendor_name'      => 'Landlord Name',
           'managementTypeInfo__management_types_name'      => 'Management Type',     
        ];
        
        $pendingPage = true;
        $title = "Landlord Documentation Pending Approval";
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
        
        $request->flash();    
        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();

        $landlordContracts =   LandlordContract::orWhere(function ($query){
												  $query->orWhere('landlord_contract_status','=',3)
                                                  ->orWhere('landlord_contract_status','=',0);
								  })
								  ->whereHas('salesEnquiry',function($query){
										$query->where('work_flow_processes_code', '=', 203);
										
                                  })
                                 ->whereHas('landlordSale',function($query)use($roles,$rolesNames){
                                     $query->whereHas('salesUsers', function ($query) use($roles,$rolesNames) {

                                    $query->when( (in_array('super_admin', $rolesNames) === false),function($query)use($roles){

                                      $query->where(function ($query) use($roles){
                                              $query->where('user_id',null)
                                                    ->whereIn('role_id', $roles);
                                                })
                                            ->orWhere(function ($query) use($roles){
                                                  $query->where('user_id','>',0)
                                                        ->whereIn('role_id', $roles)
                                                        ->where('user_id','=', \Auth::user()->id);
                                                });                                                                       
                                      })
                                      ->where('status','=',1);
                                  });
                                  })
                                 ->filter($request)->sortable()
                                 ->paginate($this->noOfRecord);

     //   dd($landlordContracts);

/*
        $approveLists = Sales::whereHas('salesEnquiry', function ($query) use($request){
                          $query->where('work_flow_processes_code', '=', 203)
                                ->filter($request);              
                      })->where('sales.sales_type', '=', 2)
                        ->where('sales.work_flow_processes_code', '=', 203)
                        ->whereHas('salesUsers', function ($query) {
                          $query->where('status','=',1);
                        });
          if (in_array('super_admin', $rolesNames) === false) {
        
              $approveLists->whereHas('salesUsers', function ($query) use($roles) {
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
        $approveList = $approveLists->sortable()->paginate(10);     */   
        
        $quick_url =   $route  = route('landlordPendingApproval');          
          
        if(isset($request->ajax)) 			
			return view('sales::LandlordSales.landlord_approve_list_ajax',compact('landlordContracts','request','route','title','pendingPage'));		
		       
        
        


        return view('sales::LandlordSales.landlord_approve_list',compact('landlordContracts','enquiry_fields','operations','title','pendingPage','quick_url'));
       
    } 
    
    
    
}
