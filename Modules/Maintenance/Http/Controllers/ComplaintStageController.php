<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Sales\Entities\Tenant;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Maintenance\Entities\ComplaintProcess;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Entities\ViewComplaintAssigned;
use Modules\Maintenance\Entities\ViewComplaintSubassigned;
use Modules\Maintenance\Entities\ViewComplaintReview;
use Modules\Maintenance\Entities\ComplaintServiceReportChecklist;
use Modules\Maintenance\Entities\ComplaintServiceReport;
use Modules\Maintenance\Entities\ComplaintServiceReportImage;
use Modules\Maintenance\Entities\ComplaintServiceReportInv;
use Modules\Maintenance\Entities\ComplaintServiceReportNote;
use Modules\Masters\Entities\Employee;
use Modules\Masters\Entities\Inventory;
use Spatie\Permission\Models\Role; 
use Modules\Masters\Entities\Vendor;
use App\User;
use Session;
use URL;
use Route;
use DB;
use Image;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Modules\General\Http\Controllers\GeneralController as General ;
use Modules\Maintenance\Events\ComplaintAssigned;
use Modules\Maintenance\Events\ComplaintReminder;
use Illuminate\Support\Facades\Mail;
use Modules\Maintenance\Emails\ComplaintCloseEmail;
use Modules\Maintenance\Emails\ComplaintStageEmail;

class ComplaintStageController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth'); 
    $this->middleware('permission:complaint_unassigned_list', ['only' => ['complaintStage']]); 
    $this->middleware('permission:complaint_assigned_list', ['only' => ['complaintAssignedList']]);
    $this->middleware('permission:complaint_subassigned_list', ['only' => ['complaintSubAssignedList']]);
    $this->middleware('permission:review_list', ['only' => ['complaintReviewList']]);
    $this->middleware('permission:complaint_closed_list', ['only' => ['complaintClosedList']]);
    $this->middleware('permission:completely_closed_list', ['only' => ['complaintCompletelyClosedList']]);


    $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 

  }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request,$result = array())
    {
      $name = Route::currentRouteName();

      $enquiry_fields = [
      'complaint_no' => 'Complaint No',
      'complainer_name' => 'Complaint Name',
      'complaint_mob_no' => 'Complaint Mobile No',
      'location__locations_name' => 'Location',
      'unit__unit_code' => 'Unit',
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


      $request->flash();

      $user = \Auth::user();
      $roles = $user->getRoles();
      $rolesNames = $user->getRoleNames()->toArray();

      $ComplaintEnquiries = ComplaintEnquiry:: 
      whereHas('ComplaintProcess', function ($query)use($result,$rolesNames,$roles,$user) {

       $query->where('work_flow_processes_code', '=', 701)
       ->complaintUsers();


     })
      ->whereHas('complaintTicketsAll', function ($query)use($result) {
        $query->where('work_flow_processes_code', '=', 701);                     
      })
      ->filter($request)->sortable()
      ->orderBy('id','asc')
      ->paginate($this->noOfRecord);


/*




        $unassigned_list = ComplaintEnquiry::
                 whereHas('ComplaintProcess', function ($query)use($result,$rolesNames,$roles) {
                            $query->where('work_flow_processes_code', '=', 701);
                               if (in_array('super_admin', $rolesNames) === false) {

                                $query->whereHas('complaintUsers', function ($query) use($roles) {
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
                              }else{
                                $query->whereHas('complaintUsers', function ($query) {
                                  $query->where('status','=',1);
                                });
                              }
                                                  
                            })->whereHas('complaintTicketsAll', function ($query)use($result) {
                            $query->where('work_flow_processes_code', '=', 701);                     
                            })
                            ->closure($result)->sortable();*/

      /*  if (in_array('super_admin', $rolesNames) === false) {

          $unassigned_list->whereHas('complaintUsers', function ($query) use($roles) {
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
          }*/

     //   $ComplaintEnquiries = $unassigned_list->orderBy('id','asc')->paginate(10);
        //dd($ComplaintEnquiries);

          $route   =  $request->url();          
          
          if(isset($request->ajax)) 
            return view('maintenance::ComplaintStages.complaint_unassigned_list_ajax',compact('ComplaintEnquiries','request','route'));

          return view('maintenance::ComplaintStages.complaint_unassigned_list',compact('ComplaintEnquiries','request','enquiry_fields','operations','name'));

        }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      return view('maintenance::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     * Service Report Invemtoty item and quantity Store
     */
    public function store(Request $request)
    {
      $nowUrl = url()->previous();
      Session::put('nowUrl', $nowUrl);
      $this->validate($request, [
        'item' => 'required',                    
        'quantity'   => 'required',
        'checklist'   => 'required'
        ]);
      $material_charge = isset($request['material_charge'])?$request['material_charge']:0;
      $labour_charge  = isset($request['labour_charge'])?$request['labour_charge']:0;
      $tax_percentage = (float)prefixData('tax_percentage')->configuration_value;
      $tax_amount     = round(($material_charge + $labour_charge) * $tax_percentage / 100, 3);
      $total_charge     = $material_charge + $labour_charge + $tax_amount;

      //get servicelist checklist id by checklist_id
      $cmp_checklist = ComplaintServiceReportChecklist::where('checklist_id','=',$request['checklist'])->first();


      $checklist = ComplaintServiceReportInv::create([
        'complaint_service_report_id'=>$request['complaint_service_report_id'],
        'inventory_id' =>$request['item'],
        'quantity' =>$request['quantity'],
        'material_charge' =>$material_charge,
        'labour_charge' =>$labour_charge,
        'tax_percentage' =>$tax_percentage,
        'tax_amount' =>$tax_amount,
        'total_charge' =>$total_charge,
        'checklist_id' => $request['checklist'],
        'service_report_checklist_id' => $cmp_checklist['id'],
        ]);

      session()->flash('success', 'Items Added Successfully');
      return redirect($nowUrl);

    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
      return view('maintenance::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
      $nowUrl = url()->previous();
      Session::put('nowUrl', $nowUrl);
      $inventory_items = Inventory::orderBy('inventories_name')->get(); //$inventory_items = Inventory::get();

      $reportInventory = ComplaintServiceReportInv::where('id',$id)->first();
      $taxPercentage = prefixData('tax_percentage')->configuration_value;
      return view('maintenance::ComplaintStages.service_report_item_edit_modal',compact('inventory_items','nowUrl','reportInventory','taxPercentage'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,$id)
    {

      $this->validate($request, [
        'item' => 'required',                    
        'quantity'   => 'required',
      ]);
      $nowUrl = $request['url'];
      $material_charge = isset($request['material_charge'])?$request['material_charge']:0;
      $labour_charge  = isset($request['labour_charge'])?$request['labour_charge']:0;
      $tax_percentage = (float)prefixData('tax_percentage')->configuration_value;
      $tax_amount     = round(($material_charge + $labour_charge) * $tax_percentage / 100, 3);
      $total_charge     = $material_charge + $labour_charge + $tax_amount;
      $checklist = ComplaintServiceReportInv::where('id',$id)->update([
        'inventory_id' =>$request['item'],
        'quantity' =>$request['quantity'],
        'material_charge' =>$request['material_charge'],
        'labour_charge' =>$request['labour_charge'],
        'tax_percentage' =>$tax_percentage,
        'tax_amount' =>$tax_amount,
        'total_charge' =>$total_charge
        ]);
      session()->flash('success', 'Items Updated Successfully');
      return redirect($nowUrl);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
      $nowUrl = url()->previous();
      Session::put('nowUrl', $nowUrl);
      $service_report_inventory = ComplaintServiceReportInv::where('id',$id)->first();
      try {

        $service_report_inventory->delete();

        session()->flash('success', 'Item Deleted Successfully');
      }
      catch (\Exception $e) {
        session()->flash('error', 'Please delete related records before');
      }
      return redirect($nowUrl);
    }
    /*
    *
    *
    * Un assigned View
    *
    *
    */
    public function unAssignedView($id)
    {
     clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$id);
     $ComplaintEnquiries = ComplaintEnquiry::where('id',$id)->first();
     $tickets = ComplaintChecklist::where('complaint_enquiries_id',$id)->where('ticket_status','!=',4)->get();
      //dd($tickets);
     return view('maintenance::ComplaintStages.complaint_unassigned_view',compact('ComplaintEnquiries','tickets'));
   }
    /*
    *
    *
    * Group Assign Modal
    *
    */
    public function groupAssignModal(Request $request)
    {
      $ticketArr = $request->id;
      $complaint_id = $request->complaint_id;
      $next_process_id = $request->workflow;
      $complaintDetail =  ComplaintEnquiry::where('id',$complaint_id)->first(); 
      $tenant_status = $complaintDetail->tenant_status;
      if($tenant_status == ""){$tenant_status = 0;}
      $general          = new General;
      $role = array();

      foreach($ticketArr as $ticket){

        $processAssign    = $general->roleUsersFromProcess($next_process_id,$location_id=0,$pricerange_id=0,$tenant_status);
        if($processAssign == false) {

          $previousProcess = $general->getPreviousOrder($next_process_id);

          if($previousProcess !=0){

            $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status); 
            $user_ids =   $previousAssign->assign->where('user_id','>',0)->pluck('user_id');
            $roles = $previousAssign->assign->where('user_id',null); 
            $roles = (array)$roles->pluck('role_id');
            if(count($user_ids) > 0){


              $default_role = (array)User::whereIn('id',$user_ids)->pluck('default_role'); 
              array_push($roles,$default_role);

            }
            array_push($role,$roles);

          }else{
            $workFlowProcess = $general->workFlowProcess($next_process_id);
            $roles = $workFlowProcess->default_role;        
            array_push($role,$roles);
          }

        }else {

          $user_ids =   $processAssign->assign->where('user_id','>',0)->pluck('user_id');
          $roles = $processAssign->assign->where('user_id',null); 
          $roles = (array)$roles->pluck('role_id');
          if(count($user_ids) > 0){


            $default_role = (array)User::whereIn('id',$user_ids)->pluck('default_role'); 
            array_push($roles,$default_role);

          }
          array_push($role,$roles);

        }
      }

      $flattenedRoles = array_flatten($role);
      $role = array_unique($flattenedRoles,SORT_REGULAR);
      $role = array_diff($role, (is_array(1) ? 1 : array(1)));
      $rolees = Role::whereIn('id', $role)->get();

      return view('maintenance::ComplaintStages.complaint_assign_modal',compact('rolees','ticketArr','next_process_id','complaint_id'));
    }
    /*
    *
    *
    * UsersBy Role
    *
    *
    */
    public function getUserByRole(Request $request)
    {
      $id = $request->input('id');
      $next_process_id = $request->input('workflow_id');
      $complaint_id = $request->input('complaint_id');
      $ticket_id = $request->input('ticket_id');
      $complaintDetail =  ComplaintEnquiry::where('id',$complaint_id)->first(); 
      $assignedTo = 0;
      $ticketInfo = ComplaintChecklist::where('id', $ticket_id)->first(); 
      
      
      if(isset($ticketInfo)) 
        $assignedTo = $ticketInfo->assigned_to;
      
      $tenant_status = $complaintDetail->tenant_status;
      if($tenant_status == ""){$tenant_status = 0;}
      $general          = new General;
      $role = array();
      $processAssign    = $general->roleUsersFromProcess($next_process_id,$location_id=0,$pricerange_id=0,$tenant_status);
      if($processAssign == false) {

        $previousProcess = $general->getPreviousOrder($next_process_id);

        if($previousProcess !=0){

          $previousAssign = $general->roleUsersFromProcess($previousProcess,$location_id=0,$pricerange_id=0,$tenant_status);
            //$user_ids =   $previousAssign->assign->where('user_id','>',0)->pluck('user_id');
          $user_ids =   $previousAssign->assign->where('user_id','>',0)->where('user_id','!=',$assignedTo )->where('role_id',$id)->pluck('user_id');

          if(count($user_ids) == 0){
            $user_ids = User::role($id)->where('id','!=',$assignedTo )->pluck('id');
          }

        }else{
          $workFlowProcess = $general->workFlowProcess($next_process_id);
          $user_ids = $workFlowProcess->default_user;        
          if(empty($user_ids)){
            $user_ids = User::role($id)->where('id','!=',$assignedTo )->pluck('id'); 
          }
        }

      }else {

        $user_ids =   $processAssign->assign->where('user_id','>',0)->where('user_id','!=',$assignedTo )->where('role_id',$id)->pluck('user_id');

        if(count($user_ids) == 0){
          $user_ids = User::role($id)->where('id','!=',$assignedTo )->pluck('id'); 
        }

      }
      $flattenedusers = array_flatten($user_ids);
      $users = array_unique($flattenedusers,SORT_REGULAR);
      $users =  array_diff($users, (is_array(1) ? 1 : array(1)));


      $usersList =\App\User::active()->whereIn('id',$users)
      ->where('user_type','employee')
      ->whereHas('employee', function ($query){
        $query->active();                      
      })                             
      ->get();       

      $usersList->each(function ($item, $key) {
        $item->username = $item->employee->employee_name;
      });

      return json_encode($usersList);

    }
    /*
    *
    *
    *
    *
    */
    public function getSubContractor(Request $request)
    {
     $complaint_id = $request->input('complaint_id');
     $ticket_id = $request->input('ticket_id');
     $assignedTo = 0;
     $ticketInfo = ComplaintChecklist::where('id', $ticket_id)->first(); 


     if(isset($ticketInfo)) 
      $assignedTo = $ticketInfo->assigned_to;

     $SubContractor = Vendor::where('vendor_type_id',1)->where('vendor_status',1)->where('id','!=',$assignedTo )->get();
    return json_encode($SubContractor);
  }
    /*
    *
    *
    * Assign Store
    *
    */
    public function storeGroupAssign(Request $request)
    {
      $url = Session::get('current');
      $nowUrl = url()->previous();
      Session::put('nowUrl', $nowUrl);
      $role_id = $request['role'];
      $user_id = $request['user_id'];
      $userId = $user_id;
      $workflow_id = $request['next_process_id'];
      $contractor_type = $request['contractor_type'];
      $complaint_id = $request['complaint_id'];
      $complaint =  ComplaintEnquiry::where('id',$complaint_id)->first(); 
      $ticketArr = $request['ticketId'];
      $general          = new General;
      //dd($workflow_id);
      $processAssign    = $general->roleUsersFromProcess($workflow_id,$location_id=0,$pricerange_id=0,$complaint->tenant_status);
      foreach ($ticketArr as $ticket) {

        $checklist = ComplaintChecklist::where('id', $ticket)->first(); 
        
        $previousFlow = ComplaintProcess::where('work_flow_processes_code',$workflow_id)->where('complaint_checklists_id',$ticket)->orderBy('id','desc')->latest()->first();

        if($previousFlow)$previousFlow->complaintUser()->update(['status' => 0]);
        
        ComplaintChecklist::where('id', $ticket)->update([
          'work_flow_processes_code' => $workflow_id,
          'assigned_to' => $userId,
          'assigned_to_type' => $contractor_type,
          'updated_by' => \Auth::user()->id,
          ]);



        $complaintProcess = ComplaintProcess::create([
          'complaint_checklists_id' =>$ticket,
          'complaint_enquiries_id' => $complaint_id,
          'work_flow_processes_code' => $workflow_id,
          'assigned_to' => $userId,
          'assigned_to_type' => $contractor_type,
          'created_by' => \Auth::user()->id,
          ]);

        if($contractor_type == 1){$role_id = \Auth::user()->default_role;$user_id = \Auth::user()->id;}
        if($processAssign) {
          foreach($processAssign->assign as $val){
           $complaintProcess->complaintUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
         }
       }
        //$processAssign->assign()->attach($role_id,['user_id'=>$user_id]);
       $complaintProcess->complaintUsers()->attach($role_id,['user_id'=>$user_id]);
       $complaintProcess->complaintUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);         


        //Notification
        //clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaint_id);
       $users_notify =  \App\User::whereIn('id',[$userId])->get(); 
       $complaint->text = "Complaint - ".$complaint->complaint_no." Ticket-".$checklist->complaint_ticket_no."  Assigned ";
       $complaint->href = url('AssignedList/'.$ticket);     
       event(new ComplaintAssigned($complaint,$users_notify)); 

//dd($users_notify);
        //Notification starts
       $complaint->subject = "Complaint Assigned";
       $complaint->textContent = " Please attend " .$checklist->work->works_code." - ". $checklist->checklist_desc. " Building Name: " .$checklist->complaintEnquiry->building->building_name;
       if(isset($checklist->complaintEnquiry->unit->unit_no)){
            $complaint->textContent .= " Flat No: ".$checklist->complaintEnquiry->unit->unit_no;
       }

       $complaint->textContent .= " GSM Number:" .$checklist->complaintEnquiry->complaint_mob_no." Comp No- ".$complaint->complaint_no." Date & Time: ".$complaint->complaint_date;

       foreach ($users_notify as $key => $user) {
        $user_email = $user->email;
        $user_name = $user->employee->employee_name;
        $mobile = $user->employee->employee_contact_no ?? $user->employee->employee_secondary_no;
        $msg = "Complaint Assigned";
        $params = 'optional data';
        if(!empty($user_email)){
      Mail::to($user_email)->send(new ComplaintStageEmail($complaint,$user_name)); //Email Notification
    }else{
     // sendSms($mobile,$msg,$params); //SMS Notification
    }
	  sendSms($mobile,$complaint->textContent,$params); //SMS Notification
  }
        //Notification ends
  /****** Activity log *****/

          /*activity('Group Assign')
            ->performedOn($complaintProcess)
            ->causedBy(\Auth::user()->id)
            ->withProperties($complaintProcess)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);*/         
          }
          session()->flash('success', 'Ticket Assigned Successfully');
          /*return redirect()->route('complaintUnassigned.view',$complaint_id);*/
          return redirect($nowUrl);
        }
    /*
    *
    *
    * Complaint assigned List
    *
    */
    public function assignedList(Request $request)
    {
      $name = Route::currentRouteName();

      $enquiry_fields = [
      'complaintEnquiry__complaint_no' => 'Complaint No',
      'complaintEnquiry__complainer_name' => 'Complaint Name',
      'complaintEnquiry__complaint_mob_no' => 'Complaint Mobile No',
      'complaintEnquiry__location__locations_name' => 'Location',
      'complaintEnquiry__Unit__unit_code' => 'Unit',
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

      $request->flash();

      $user = \Auth::user();
      $roles = $user->getRoles();
      $rolesNames = $user->getRoleNames()->toArray();
      $ComplaintEnquiries = ViewComplaintAssigned::where('work_flow_processes_code', '=', 702)->where('process_workflow', '=', 702)->whereIn('ticket_status',[0,1,3])->filter($request)->assignedComplaint($request)->assignedComplaintSupervisor($request)->assignedComplaintSubcontractor($request)->assignedTo($request)->category($request)->vipOpenAssigned($request)->select(DB::raw('MAX(id) as complaint_checklist_id'),'id','complaint_enquiries_id','complaint_ticket_no','work_id','assigned_to','building_id','building_name', 'unit_code','assigned_to','concat','vendors','ticket_status','work_flow_processes_code','assigned_to_type')->groupBy('complaint_enquiries_id','complaint_ticket_no','id','work_id','assigned_to','building_id','building_name', 'unit_code','assigned_to','concat','vendors','ticket_status','work_flow_processes_code','assigned_to_type')->sortable()->paginate($this->noOfRecord );

      $route   =  $request->url();

       //dd($ComplaintEnquiries);
      if(isset($request->ajax))       
        return view('maintenance::ComplaintStages.complaint_assigned_list_ajax',compact('ComplaintEnquiries','request','route'));

      return view('maintenance::ComplaintStages.complaint_assigned_list',compact('ComplaintEnquiries','request','enquiry_fields','operations','name'));
    }
    /*
    *
    *
    * Assigned View
    *
    *
    */
    public function AssignedView(Request $request,$id)
    {
	   $ServiceReportNotes = array();
       $ticket = ComplaintChecklist::where('id',$id)
      ->where('work_flow_processes_code',702)->first(); echo "<br>";

      if(!$ticket){
        $request->session()->reflash();
        return redirect()->route('complaintAssignedList');
      }

      $service_report =  ComplaintServiceReportChecklist::where('checklist_id',$id)
      ->pluck('id')
      ->first();

     
      ///// Added on 04/02/2020 to fix issue 16
      ////When notes are added while generating service reports for complains assigned to sub-contractors, the notes are not appearing in the screen.

       $ticket_cl = array(); 
       $ticket_cl =  ComplaintServiceReportChecklist::where('checklist_id',$id)->first(); 
     
       if($ticket_cl!=null)
       $ServiceReportNotes = ComplaintServiceReportNote::where('complaint_service_report_id',$ticket_cl->complaint_service_report_id)->get();

      //////
      
      clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$ticket->complaint_enquiries_id);
      readNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$ticket->complaint_enquiries_id);
      return view('maintenance::ComplaintStages.complaint_assigned_view',compact('ticket','service_report','ServiceReportNotes'));


    }
    /*
    *
    *
    *  Sub assign Modal
    *
    */
    public function subAssignModal(Request $request)
    {

      $tickets = $request->input('id');
      $workflow = $request->input('workflow');
      $complaint_id = $request->input('complaint_id');
      //dd($workflow);

      $rolesNames = \Auth::user()->getRoleNames()->toArray();
      $log_id = \Auth::user()->id;
      //if (in_array('technical_head', $rolesNames) === false) {

      $users = User::role('technician')->get();

      /*}else{
        $users = User::role('technician')
              ->whereHas('employee', function ($query)use($log_id) {
                $query->where('head_user', '=', $log_id)
                ->active();                    
                })->get();
              }*/
      //dd($users);
              return view('maintenance::ComplaintStages.complaint_subassign_modal',compact('users','tickets','workflow','complaint_id'));
            }
    /*
    *
    *
    * Sub Assign
    *
    */
    public function storeGroupSubAssign(Request $request)
    {
    //  $route = Session::get('current');//dd($route);
      $role_id = $request['role'];
      $user_id = $request['user_id'];
      $workflow_id = 703;
      $complaint_id = $request['complaint_id'];
      $complaint =  ComplaintEnquiry::where('id',$complaint_id)->first(); 
      $ticketArr = $request['ticketId'];
      $tenant_status = $complaint->tenant_status;
      if($tenant_status == ""){$tenant_status = 0;}
      $general          = new General; 
      $processAssign    = $general->roleUsersFromProcess($workflow_id,$location_id=0,$pricerange_id=0,$tenant_status);
      
      foreach ($ticketArr as $ticket) {

        $checklist = ComplaintChecklist::where('id', $ticket)->first(); 
        

        $previousFlow = ComplaintProcess::where('work_flow_processes_code',$workflow_id)->where('complaint_checklists_id',$ticket)->orderBy('id','desc')->latest()->first();
        if($previousFlow)$previousFlow->complaintUser()->update(['status' => 0]);
        
        $complaintProcess = new ComplaintProcess; 
        $complaintProcess->complaint_checklists_id     = $ticket;
        $complaintProcess->complaint_enquiries_id           = $complaint_id;
        $complaintProcess->work_flow_processes_code = $workflow_id;
        $complaintProcess->sub_assigned_to =  $user_id;
        $complaintProcess->created_by           = \Auth::user()->id;
        
        $complaintProcess->save();

       /* $complaintProcess = ComplaintProcess::create([
          'complaint_checklists_id' =>$ticket,
          'complaint_enquiries_id' => $complaint_id,
          'work_flow_processes_code' => $workflow_id,
          'sub_assigned_to' => $user_id,
          'created_by' => \Auth::user()->id,
          ]);*/
          if($processAssign == false) {

            $processAssign = $general->getPreviousOrder($workflow_id);
            if($processAssign !=0) {
              $previousAssign = $general->roleUsersFromProcess($processAssign,$location_id=0,$pricerange_id=0,$tenant_status);
              foreach($previousAssign->assign as $val){
                if(\Auth::user()->default_role != $val->role_id){
                 $complaintProcess->complaintUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
               }
             }
           }else{
            $res =    $general->workFlowProcess($workflow_id);
            $complaintProcess->complaintUsers()->attach($res->default_role, ['user_id' => $res->default_user_id]);

          }
        }
        $complaintProcess->complaintUsers()->attach($role_id,['user_id'=>$user_id]);
        $complaintProcess->complaintUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);         
        
        ComplaintChecklist::where('id', $ticket)->update([
          'work_flow_processes_code' => $workflow_id,
          'sub_assigned_to' => $user_id,
          'updated_by' => \Auth::user()->id,
          ]);
        //Notification
        //clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaint_id);
        $users_notify =  \App\User::whereIn('id',[$user_id])->get(); 
        $complaint->text = "Complaint - ".$complaint->complaint_no." Ticket-".$checklist->complaint_ticket_no."  SubAssigned ";
        
        $complaint->href = url('SubAssignedList/'.$complaint->id.'/'.$user_id);     
        event(new ComplaintAssigned($complaint,$users_notify)); 

                        //Notification starts
       $complaint->subject = "Complaint Ticket SubAssigned";
       $complaint->textContent = " Please attend " .$checklist->work->works_code." - ". $checklist->checklist_desc. " Building Name: " .$checklist->complaintEnquiry->building->building_name;
       
       if(isset($checklist->complaintEnquiry->unit->unit_no)){
            $complaint->textContent .= " Flat No: ".$checklist->complaintEnquiry->unit->unit_no;
       }

       $complaint->textContent .=  " GSM Number:" .$checklist->complaintEnquiry->complaint_mob_no." Comp No- ".$complaint->complaint_no." Date & Time: ".$complaint->complaint_date;

       foreach ($users_notify as $key => $user) {
        $user_email = $user->email;
        $user_name = $user->employee->employee_name;
        $mobile = $user->employee->employee_contact_no ?? $user->employee->employee_secondary_no;
        $msg = "Complaint Ticket SubAssigned";
        $params = 'optional data';
        if(!empty($user_email)){
      Mail::to($user_email)->send(new ComplaintStageEmail($complaint,$user_name)); //Email Notification
    }else{
      //sendSms($mobile,$msg,$params); //SMS Notification
    }
	sendSms($mobile,$complaint->textContent,$params); //SMS Notification
  }
        //Notification ends
        /****** Activity log *****/

          /*activity('Group Assign')
            ->performedOn($complaintProcess)
            ->causedBy(\Auth::user()->id)
            ->withProperties($complaintProcess)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);*/


          }
          session()->flash('success', 'Ticket Assigned Successfully');

          if ($request->session()->has('current')) {
            $route = Session::get('current');
            return redirect()->route($route);
          }

          return redirect()->back();


        }
    /*
    *
    *
    *  Sub assign Modal
    *
    */
    public function subReAssignModal(Request $request)
    {
      $route = session::get('current');
      $sub_assign = $request->input('assign_id');
      $workflow = $request->input('workflow');
      $complaint_id = $request->input('complaint_id');
      $tickets = ComplaintChecklist::where('complaint_enquiries_id',$complaint_id)->whereIn('sub_assigned_to',$sub_assign)->where('work_flow_processes_code', '=', 703)
          //->where('ticket_status', '<', 2)          
      ->whereIn('ticket_status', [0,1,3])
      ->pluck('id');

      $rolesNames = \Auth::user()->getRoleNames()->toArray();
      $log_id = \Auth::user()->id;
      if (in_array('maintenance_supervisor', $rolesNames) === false) {

        if(count($sub_assign)>1){ 
          $individualAssignUser = $sub_assign[0];
          $users = User::role('technician')->where('id','!=',$individualAssignUser)
          ->get();
        }
        else{
          $users = User::role('technician')
              /*->whereHas('employee', function ($query)use($log_id) {
                $query->where('head_user', '=', $log_id)
                ->active();                    
              })*/->get();
            }

          }else{

            if(count($sub_assign)==1){ 

              $individualAssignUser = $sub_assign[0];

              $users = User::role('technician')->where('id','!=',$individualAssignUser )->get();
            }
            else{
              $users = User::role('technician')
              /*->whereHas('employee', function ($query)use($log_id) {
                $query->where('head_user', '=', $log_id)
                ->active();                    
              })*/->get();
            }
          }
      //dd($users);
          return view('maintenance::ComplaintStages.complaint_subassign_modal',compact('users','tickets','workflow','complaint_id'));
        }
    /*
    *
    * Contractor Service Report
    *
    *
    */
    public function checkInContractor($id)
    {
      $name = Route::currentRouteName();
      $url = url()->previous();
      $latest = ComplaintServiceReportChecklist::where('checklist_id',$id)->first();
      $checkList = ComplaintChecklist::where('id',$id)->get();
      $work_flow = $checkList->pluck('work_flow_processes_code');
      $tick_desc = $checkList->pluck('checklist_desc')->toArray();

      $notes = implode ('' , $tick_desc );

      $prefix      = prefixData('service_report_prefix')->configuration_value;
      $reportLatest = ComplaintServiceReport::latest()->first();
      if(!empty($reportLatest))
        $nextCode = $prefix.str_pad($reportLatest->id+1,4,'0',STR_PAD_LEFT);
      else
        $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT); 

      if($latest == null){
        $serviceReport = ComplaintServiceReport::create([
          'user_id' =>\Auth::user()->id,
          'service_report_no' => $nextCode,
          'complaint_assign_note'=> $notes,
          'complaint_assign_status'=> 1,
          'created_by' => \Auth::user()->id,
          ]);
        ComplaintServiceReportChecklist::create([
          'checklist_id' =>$id,
          'complaint_service_report_id' => $serviceReport->id,
          ]);
        ComplaintChecklist::where('id',$id)->update([
          'service_report_status'=>1,
          'ticket_status'=>1
          ]);
      }
      
      return redirect($url);

    }
    /*
    *
    * Contractor Service Report
    *
    *
    */
    public function contractorServiceReport($id)
    {
      $name = Route::currentRouteName();
      $latest = ComplaintServiceReportChecklist::where('checklist_id',$id)->first();
      $checkList = ComplaintChecklist::where('id',$id)->get();
      $work_flow = $checkList->pluck('work_flow_processes_code');
      $tick_desc = $checkList->pluck('checklist_desc')->toArray();

      $notes = implode ('' , $tick_desc );

      $prefix      = prefixData('service_report_prefix')->configuration_value;
      $reportLatest = ComplaintServiceReport::latest()->first();
      if(!empty($reportLatest))
        $nextCode = $prefix.str_pad($reportLatest->id+1,4,'0',STR_PAD_LEFT);
      else
        $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT); 

      if($latest == null){
        $serviceReport = ComplaintServiceReport::create([
          'user_id' =>\Auth::user()->id,
          'service_report_no' => $nextCode,
          'complaint_assign_note'=> $notes,
          'complaint_assign_status'=> 1,
          'created_by' => \Auth::user()->id,
          ]);
        ComplaintServiceReportChecklist::create([
          'checklist_id' =>$id,
          'complaint_service_report_id' => $serviceReport->id,
          ]);
        ComplaintChecklist::where('id',$id)->update([
          'service_report_status'=>1,
          'ticket_status'=>1
          ]);
      }
      $inventory_items = Inventory::orderBy('inventories_name')->get(); //$inventory_items = Inventory::get(); 

      $ticket =  ComplaintServiceReportChecklist::where('checklist_id',$id)->first();

      $complaintServiceReportInv = ComplaintServiceReportInv::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();

      $ServiceReportNotes = ComplaintServiceReportNote::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();
      $images 			 = ComplaintServiceReportImage::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();
      
      $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
      $supported_image = array('pdf','docx','doc');
      return view('maintenance::ComplaintStages.subcontractor_service_report',compact('supported_image','ticket','complaintServiceReportInv','inventory_items','ServiceReportNotes','work_flow','upload_size','images','checkList'));

    }
    /*
    *
    *
    * Service Report Note
    *
    *
    */
    public function storeServiceReportNote(Request $request)
    {
      $nowUrl = url()->previous();
      Session::put('nowUrl', $nowUrl);
      $this->validate($request, [
        'report_notes' => 'required',                    
        ]);
      $report_id = $request['service_report_id'];
      $serviceReport = ComplaintServiceReport::where('id',$report_id)->first();
      $checklist = ComplaintServiceReportNote::create([
        'complaint_service_report_id'=>$report_id,
        'stage' =>$serviceReport->complaint_assign_status,
        'desc' =>$request['report_notes'],
        'created_by' => \Auth::user()->id,

        ]);
      session()->flash('success', 'Note Added Successfully');
      return redirect($nowUrl);
    }
    /*
    *
    * Service Report Status Update Get Modal
    *
    *
    */
    public function ServiceReportStatusUpdate(Request $request)
    {
      $service_report_id = $request->input('id');
      $complaint_id = $request->input('complaint_id');
      $symbol = $request->input('symbol');
      $nowUrl = url()->previous(); 
      $current = Session::get('current');
      Session::put('nowUrl', $nowUrl);
      $tickets = ComplaintServiceReportChecklist::where('complaint_service_report_id',$service_report_id)->pluck('checklist_id');
      $serviceReport = ComplaintServiceReport::where('id',$service_report_id)->first();
      return view('maintenance::ComplaintStages.service_report_update_status_modal',compact('serviceReport','nowUrl','complaint_id','tickets','symbol'));

    }
    /*
    *
    * Service Report Status Update Get Modal
    *
    *
    */
    public function storeServiceReportStatusUpdate(Request $request,$id)
    {

      $nowUrl = $request['url'];
      $current = Session::get('current');//dd($current);
      $status =$request['status']; 
      $complaint_checklist_id = $request['complaint_checklist_id'];
      $work_flow = ComplaintChecklist::whereIn('id',$complaint_checklist_id)->first();
      $work_flow_processes_code = $work_flow->work_flow_processes_code;
      if($status == 4 ){$work_flow_processes_code = 705;
        ComplaintEnquiry::where('id',$work_flow->complaint_enquiries_id)->update([
          'complaint_status' => 1,
          ]);
      }
      if($status != null){
        ComplaintServiceReport::where('id',$id)->update([
          'complaint_assign_status' => $status,
          ]);
        if($request['note'] != ""){
          $checklist = ComplaintServiceReportNote::create([
            'complaint_service_report_id'=>$id,
            'stage' =>$status,
            'desc' =>$request['note'],
            'created_by' => \Auth::user()->id,

            ]);
        }

        ComplaintChecklist::whereIn('id',$complaint_checklist_id)->update([
          'ticket_status' => $status,
          'work_flow_processes_code' => $work_flow_processes_code,
          ]);
        $notes = 'Status';
      }else{

        ComplaintServiceReport::where('id',$id)->update([
          'complaint_assign_note' => $request['note'],
          ]);
        $notes = 'Note';
      }
      $complaint_id = $request['complaint_id'];
      $complaintDetail = ComplaintEnquiry::where('id',$complaint_id)->first();
      $serviceReport = ComplaintServiceReport::where('id',$id)->first();

      // attended Status To sent notification
      if($status == 2){$s='Attended';$nowUrl = 'complaintStage/SubAssignedList';}elseif($status == 4){ $s = 'Closed';}
      if(($status == 2)|| ($status == 4)) {


        clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaint_id);
        readNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaint_id);
        /*$this->assignNotify($complaint_id,$serviceReport->service_report_no,$id,$s);*/
        $process =   ComplaintProcess::where('complaint_enquiries_id',$complaint_id)->latest()->first();     
        $complaintUsers =  $process->complaintUser; 
        
        
        $users = array();     
        
        foreach($complaintUsers as $val){

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
      $users_notify =  \App\User::whereIn('id',$users)->get();    
      clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaint_id);
      $complaintDetail =  ComplaintEnquiry::find($complaint_id);    
      $complaintDetail->href = url('ReviewList/'.$serviceReport->id); 
      $complaintDetail->text = "Complaint - ".$complaintDetail->complaint_no." WorkOrder-". $serviceReport->service_report_no."  Status Change To  ".$s; 
      event(new ComplaintAssigned($complaintDetail,$users_notify)); 
    }
    session()->flash('success', $notes.' Update Successfully');
    if($status == 4){
      return redirect()->route($current);   
    }else{
      return redirect($nowUrl);
    }


  }
    /*
    *
    *
    * Complaint Sub Assigned List
    *
    */
    public function subAssignedList(Request $request,$result = array())
    {

      $name = Route::currentRouteName();

      $enquiry_fields = [
      'complaint_no' => 'Complaint No',
      'complainer_name' => 'Complaint Name',
      'complaint_mob_no' => 'Complaint Mobile No',
      'location__locations_name' => 'Location',
      'unit__unit_code' => 'Unit',

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

      $request->flash();

      $user = \Auth::user();

        $roles = $user->getRoles();//dd($rolesNames);
        $rolesNames = $user->getRoleNames()->toArray();

        $ComplaintEnquiries = ViewComplaintSubassigned::subAssigned($request)->where('work_flow_processes_code', '=', 703)->where('process_workflow', '=', 703)->whereIn('ticket_status',[0,1,3])->filter($request)->complaintUsers()
        ->select('complaint_enquiries_id','complainer_name','complaint_no','building_name','complaint_date','complaint_mob_no','sub_assigned_to','ticket_status','assigned_name','sub_assigned_name','unit_code','assigned_to','sub_assigned_to')
        ->groupBy('view_complaint_subassigned.complaint_enquiries_id','complainer_name','complaint_no','building_name','complaint_date','complaint_mob_no','sub_assigned_to','ticket_status','assigned_name','sub_assigned_name','unit_code','assigned_to','sub_assigned_to')
        ->assignedUsers()->additionalColum($request)->sortable()->paginate($this->noOfRecord);


        
      //dd($ComplaintEnquiries);

        $route   =  $request->url();          

        if(isset($request->ajax))       
          return view('maintenance::ComplaintStages.complaint_sub_assigned_list_ajax',compact('ComplaintEnquiries','request','route'));

        return view('maintenance::ComplaintStages.complaint_sub_assigned_list',compact('ComplaintEnquiries','request','enquiry_fields','operations','name'));
      }
    /*
    *
    *
    * Close Modal
    *
    */
    public function closeModal(Request $request)
    {

      $nowUrl = url()->previous();
      Session::put('closeUrl', $nowUrl);
      $stage_id                  = $request->stage_id;
      $complaint_id              = $request->complaint_id;
      $assigned_id               = $request->assigned_id;
      $complaint_checklist_id    = $request->complaint_checklist_id;
      //dd($assigned_id);where('work_flow_processes_code', '=', 703)
      if(empty($complaint_checklist_id)){
        $complaint_checklist_id = ComplaintChecklist::where('complaint_enquiries_id',$complaint_id)->where('sub_assigned_to','=',$assigned_id)->pluck('id');

      }
      //dd($complaint_checklist_id);
      
      return view('maintenance::ComplaintStages.ticket_close_modal',compact('stage_id','complaint_id','complaint_checklist_id'));
      
    }
     /*
    *
    *
    * Close Modal Action
    *
    */
     public function ticketCloseModalAction(Request $request){

      $url                       = Session::get('closeUrl')?Session::get('closeUrl'):'complaintSubAssignedList';
      $stage_id                  = $request->stage_id;
      $complaint_id              = $request->complaint_id;
      $complaint_checklist_id    = $request->complaint_checklist_id; 
      $complaint_processes_note  = $request->complaint_processes_note;
     // dd($complaint_checklist_id);
      foreach($complaint_checklist_id as $ticket){


       $ticketDetails =    ComplaintChecklist::where('id',$ticket)->first();


       $current_ticket =  ComplaintProcess::where([
        ['complaint_checklists_id','=', $ticket],
        ['work_flow_processes_code' ,'=', $ticketDetails->work_flow_processes_code]
        ])->latest()
       ->first();

       if(!$current_ticket){

        $complaintProcess = ComplaintProcess::create([
          'complaint_checklists_id'=> $ticket,
          'complaint_enquiries_id' => $complaint_id ,
          'work_flow_processes_code' => $ticketDetails->work_flow_processes_code,
          'complaint_processes_note' => $complaint_processes_note,
          'created_by' =>\Auth::user()->id,
          'updated_by' =>\Auth::user()->id,
          ]);

      }else{

       $current_ticket->update([
        'complaint_processes_note' => $complaint_processes_note,
        'updated_by' =>\Auth::user()->id,
        ]);
     }


     $complaintProcess = ComplaintProcess::create([
      'complaint_checklists_id'=> $ticket,
      'complaint_enquiries_id' => $complaint_id ,
      'work_flow_processes_code' => 705,
            //  'complaint_processes_note' => $complaint_processes_note,
      'created_by' =>\Auth::user()->id,
      ]);

     ComplaintChecklist::where('id',$ticket)->update([
      'work_flow_processes_code' =>705,
      'ticket_status' =>4,
      'updated_by' =>\Auth::user()->id,
      ]);
     $Report_id = ComplaintServiceReportChecklist::where('checklist_id',$ticket)->first();
     if($Report_id != ""){
      $upd = ComplaintServiceReport::where('id',$Report_id->complaint_service_report_id)->update(['complaint_assign_status'=>4]);
    }

  }

  ComplaintEnquiry::where('id',$complaint_id)->update([
    'complaint_status' =>1,
    'updated_by' =>\Auth::user()->id,
    ]);

  return redirect($url);
}
     /*
    *
    *
    * Close Modal
    *
    */
     public function closeComplaintModal(Request $request)
     {
      $nowUrl = url()->previous();
      Session::put('closeUrl', $nowUrl);
      $stage_id                  = $request->stage_id;
      $complaint_id              = $request->complaint_id;
      $complaint_checklist_id    = $request->complaint_checklist_id;

      return view('maintenance::ComplaintStages.complaint_close_modal',compact('stage_id','complaint_id','complaint_checklist_id'));
      
    }
     /*
    *
    *
    * Close Modal Action
    *
    */
     public function complaintCloseModalAction(Request $request){

      $url = Session::get('closeUrl');
      $complaint_id              = $request->complaint_id;
      $complaintDetails = ComplaintEnquiry::where('id',$complaint_id)->first();
      $tenantDetail = Tenant::where('id',$complaintDetails->tenant_id)->first();
      $complaint_processes_note  = $request->complaint_processes_note;
      $tickets = ComplaintChecklist::where('complaint_enquiries_id',$complaint_id)->get();
      foreach($tickets as $ticket){

        $previous = ComplaintProcess::where('complaint_checklists_id',$ticket->id)->orderBy('id','DESC')->latest()->first();
        if($previous)$previous->complaintUser()->update(['status' => 0]);

        $current_ticket =  ComplaintProcess::where([
          ['complaint_checklists_id','=', $ticket->id],
          ['work_flow_processes_code' ,'=', $ticket->work_flow_processes_code]
          ])->latest()
        ->first();

        if(!$current_ticket){
          $complaintProcess = ComplaintProcess::create([
            'complaint_checklists_id'=> $ticket->id,
            'complaint_enquiries_id' => $complaint_id ,
            'work_flow_processes_code' => $ticket->work_flow_processes_code,
            'complaint_processes_note' => $complaint_processes_note,
            'created_by' =>\Auth::user()->id,
            'updated_by' =>\Auth::user()->id,
            ]);

        }else{ 
         $current_ticket->update([
          'complaint_processes_note' => $complaint_processes_note,
          'updated_by' =>\Auth::user()->id,
          ]);
       }

       $complaintProcess = ComplaintProcess::create([
        'complaint_checklists_id'=> $ticket->id,
        'complaint_enquiries_id' => $complaint_id ,
        'work_flow_processes_code' => 706,
        'complaint_processes_note' => '',
        'created_by' =>\Auth::user()->id,
        ]);
       $complaintProcess->complaintUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);
       ComplaintChecklist::where('id',$ticket->id)->update([
        'work_flow_processes_code' =>706,
        'ticket_status' =>4,
        'updated_by' =>\Auth::user()->id,
        ]);
     }

     ComplaintEnquiry::where('id',$complaint_id)->update([
      'complaint_status' =>2,
      'updated_by' =>\Auth::user()->id,
      ]);
     clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaint_id);
     readNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaint_id);
     $this->closeNotify($complaint_id);
      //Email Notification
	  if(isset( $tenantDetail->tenant_contact_email) || isset( $tenantDetail->tenant_personal_email)){
		$tenantEmail = $tenantDetail->tenant_contact_email ?? $tenantDetail->tenant_personal_email;
		if($tenantEmail == "")$tenantEmail = $tenantDetail->tenant_personal_email;
		Mail::to($tenantEmail)->send(new ComplaintCloseEmail($tenantDetail));
	  }
    
    return redirect($url);
  }
    /*
    *
    *
    * Sub Assigned View
    *
    *
    */
    public function SubAssignedview($id,$sub_assign)
    {   

      clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$id);
      readNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$id);
      $ComplaintEnquiries = ComplaintEnquiry::where('id',$id)->first();
      /*->where('service_report_status',$status)*/
      $user = \Auth::user();
      $roles = $user->getRoles();
      $rolesNames = $user->getRoleNames()->toArray();

      $tickets = ComplaintChecklist::where('complaint_enquiries_id',$id)
      ->where('sub_assigned_to',$sub_assign)
      ->where('work_flow_processes_code', '=', 703)
      ->whereHas('complaintProcessAll', function ($query)use($roles,$user) {
        $query->whereHas('complaintUsers', function ($query) use($roles,$user){
                  // $query->where('status','=',1)
          $query->when( !($user->hasRole('super_admin')), function($query)use($roles){
            $query->where(function ($query) use($roles){ 
             $query->where('user_id',null)
             ->whereIn('role_id', $roles);
           })->orWhere(function ($query) use($roles){
            $query->where('user_id','>',0)
            ->whereIn('role_id', $roles)
            ->where('user_id','=', \Auth::user()->id);
          });          

         });

        });
      })
      ->whereIn('ticket_status', [0,1,3])
      /*->where('ticket_status', '<', 2)  */        
      ->get();
    //  dd($tickets);

      if($tickets->count() == 0) 
        return redirect()->route('complaintSubAssignedList');

      $tick_desc = $tickets->pluck('checklist_desc')->toArray();
      
      $notes = implode ('' , $tick_desc );
      $checklists = $tickets->pluck('id');
      $service_report =  ComplaintServiceReportChecklist::whereIn('checklist_id',$checklists)->pluck('id')->first(); 
      
      $assigned = ComplaintChecklist::whereIn('id',$checklists)->first();
      return view('maintenance::ComplaintStages.complaint_subassigned_view',compact('ComplaintEnquiries','tickets','checklists','assigned','notes','service_report'));
    }
    /*
    *
    * Technician Checkin
    *
    *
    */
    public function checkIn($id,$sub_assign)
    {
      $url = url()->previous();
      
      $tickets = ComplaintChecklist::where('complaint_enquiries_id',$id)
      ->where('sub_assigned_to',$sub_assign)
      ->where('work_flow_processes_code', '=', 703)
      ->whereIn('ticket_status', [0,1,3])->get();

      $work_flow = $tickets->pluck('work_flow_processes_code');
      $tick_desc = $tickets->pluck('checklist_desc')->toArray();
      
      $tickets = $tickets->pluck('id');
      $notes = implode ('' , $tick_desc );
      $name = Route::currentRouteName();
      $prefix      = prefixData('service_report_prefix')->configuration_value;
      $reportLatest = ComplaintServiceReport::latest()->first();
      if(!empty($reportLatest))
        $nextCode = $prefix.str_pad($reportLatest->id+1,4,'0',STR_PAD_LEFT);
      else
        $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);

      $latest = ComplaintServiceReportChecklist::whereIn('checklist_id',$tickets)->first();
      
      if($latest == null){
        $serviceReport = ComplaintServiceReport::create([
          'user_id' =>\Auth::user()->id,
          'service_report_no' => $nextCode,
          'complaint_assign_note'=> $notes,
          'complaint_assign_status'=> 1,
          'created_by' => \Auth::user()->id,
          ]);
        foreach($tickets as $checklistId){

          ComplaintServiceReportChecklist::create([
            'checklist_id' =>$checklistId,
            'complaint_service_report_id' => $serviceReport->id,
            ]);
          // Update service report generated status
          ComplaintChecklist::where('id',$checklistId)->update([
            'service_report_status'=>1,
            'ticket_status'=>1
            ]);
        }
      }

      return redirect($url);
      

    }
    /*
    *
    * Technician Service Report
    *
    *
    */
    public function technicianServiceReport($id,$sub_assign)
    {

      $tickets = ComplaintChecklist::where('complaint_enquiries_id',$id)
      ->where('sub_assigned_to',$sub_assign)
      ->where('work_flow_processes_code', '=', 703)
      ->whereIn('ticket_status', [0,1,3])
      ->get();

      if(count($tickets) == 0)                            
       return redirect()->route('complaintSubAssignedList');

     $work_flow = $tickets->pluck('work_flow_processes_code');
     $tick_desc = $tickets->pluck('checklist_desc')->toArray();

     $tickets = $tickets->pluck('id');
     $notes = implode ('' , $tick_desc );
     $name = Route::currentRouteName();
     $prefix      = prefixData('service_report_prefix')->configuration_value;
     $reportLatest = ComplaintServiceReport::latest()->first();
     if(!empty($reportLatest))
      $nextCode = $prefix.str_pad($reportLatest->id+1,4,'0',STR_PAD_LEFT);
    else
      $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);

    $latest = ComplaintServiceReportChecklist::whereIn('checklist_id',$tickets)->first();

    if($latest == null){
      $serviceReport = ComplaintServiceReport::create([
        'user_id' =>\Auth::user()->id,
        'service_report_no' => $nextCode,
        'complaint_assign_note'=> $notes,
        'complaint_assign_status'=> 1,
        'created_by' => \Auth::user()->id,
        ]);
      foreach($tickets as $checklistId){

        ComplaintServiceReportChecklist::create([
          'checklist_id' =>$checklistId,
          'complaint_service_report_id' => $serviceReport->id,
          ]);
          // Update service report generated status
        ComplaintChecklist::where('id',$checklistId)->update([
          'service_report_status'=>1,
          'ticket_status'=>1
          ]);
      }
    }

    $inventory_items = Inventory::orderBy('inventories_name')->get(); //$inventory_items = Inventory::get(); 

    $ticket =  ComplaintServiceReportChecklist::whereIn('checklist_id',$tickets)->first(); 
    $checklists = ComplaintChecklist::whereIn('id',$tickets)->get();

    $complaintServiceReportInv = ComplaintServiceReportInv::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();

    $ServiceReportNotes = ComplaintServiceReportNote::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();
    Session::put('tickets', $tickets);
    $images = ComplaintServiceReportImage::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();
    $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
    $supported_image = array('pdf','docx','doc');
    return view('maintenance::ComplaintStages.technician_service_report',compact('ticket','complaintServiceReportInv','inventory_items','ServiceReportNotes','checklists','tickets','work_flow','images','supported_image','upload_size'));

  }
    /*
    *
    * Technician Service Report closed Grid
    *
    *
    */
    public function technicianServiceReportClosed($id,$sub_assign = null)
    {
      
      // In csubcontractor case
      if($sub_assign == 0){
         
          $tickets = ComplaintChecklist::where('complaint_enquiries_id',$id)
          ->whereNull('sub_assigned_to')
          ->where('ticket_status', 4)
          ->where(function ($query){
           $query->where('work_flow_processes_code', '=', 705)
           ->orWhere('work_flow_processes_code', '=', 706);
         })
          ->get();
      }
      else{
          $tickets = ComplaintChecklist::where('complaint_enquiries_id',$id)
          ->when($sub_assign,function($query)use($sub_assign){
           $query->where('sub_assigned_to',$sub_assign);
          })                                   
          ->where('ticket_status', 4)
          ->where(function ($query){
           $query->where('work_flow_processes_code', '=', 705)
           ->orWhere('work_flow_processes_code', '=', 706);
         })
          ->get();
       }

     
      $work_flow = $tickets->pluck('work_flow_processes_code');
      $tick_desc = $tickets->pluck('checklist_desc')->toArray();
      
      $tickets = $tickets->pluck('id');
      $notes = implode ('' , $tick_desc );
      $name = Route::currentRouteName();
      $prefix      = prefixData('service_report_prefix')->configuration_value;
      $reportLatest = ComplaintServiceReport::latest()->first();
      if(!empty($reportLatest))
        $nextCode = $prefix.str_pad($reportLatest->id+1,4,'0',STR_PAD_LEFT);
      else
        $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);

      $latest = ComplaintServiceReportChecklist::whereIn('checklist_id',$tickets)->first();
      
      if($latest == null){
        $serviceReport = ComplaintServiceReport::create([
          'user_id' =>\Auth::user()->id,
          'service_report_no' => $nextCode,
          'complaint_assign_note'=> $notes,
          'complaint_assign_status'=> 1,
          'created_by' => \Auth::user()->id,
          ]);
        foreach($tickets as $checklistId){

          ComplaintServiceReportChecklist::create([
            'checklist_id' =>$checklistId,
            'complaint_service_report_id' => $serviceReport->id,
            ]);
          // Update service report generated status
          ComplaintChecklist::where('id',$checklistId)->update([
            'service_report_status'=>1,
            'ticket_status'=>1
            ]);
        }
      }

     // $inventory_items = Inventory::get(); 
	    $inventory_items = Inventory::orderBy('inventories_name')->get();

      $ticket =  ComplaintServiceReportChecklist::whereIn('checklist_id',$tickets)->first(); 
      
      $checklists = ComplaintChecklist::whereIn('id',$tickets)->get();
     
      if(count($checklists)>0){
        $complaintServiceReportInv = ComplaintServiceReportInv::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();

        $ServiceReportNotes = ComplaintServiceReportNote::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();
        Session::put('tickets', $tickets);
        $images = ComplaintServiceReportImage::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();
      }
      $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
      $supported_image = array('pdf','docx','doc');
      $taxPercentage = prefixData('tax_percentage')->configuration_value;

      return view('maintenance::ComplaintStages.technician_service_report_closed',compact('ticket','complaintServiceReportInv','inventory_items','ServiceReportNotes','checklists','tickets','work_flow','images','upload_size','supported_image','taxPercentage'));

    }
    /*
    *
    * Technician Service Report View
    *
    *
    */
    public function ServiceReportView($id,$sub_assign)
    {

      $tickets = ComplaintChecklist::where('complaint_enquiries_id',$id)->where('sub_assigned_to',$sub_assign)->where('work_flow_processes_code', '=', 703)->whereIn('ticket_status', [0,1,3])->get();
      $work_flow = $tickets->pluck('work_flow_processes_code');
      $tick_desc = $tickets->pluck('checklist_desc')->toArray();
      
      $tickets = $tickets->pluck('id');
      $notes = implode ('' , $tick_desc );
      $name = Route::currentRouteName();
      $prefix      = prefixData('service_report_prefix')->configuration_value;
      $reportLatest = ComplaintServiceReport::latest()->first();
      if(!empty($reportLatest))
        $nextCode = $prefix.str_pad($reportLatest->id+1,4,'0',STR_PAD_LEFT);
      else
        $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);

      $latest = ComplaintServiceReportChecklist::whereIn('checklist_id',$tickets)->first();
      /*
      if($latest == null){
        $serviceReport = ComplaintServiceReport::create([
          'user_id' =>\Auth::user()->id,
          'service_report_no' => $nextCode,
          'complaint_assign_note'=> $notes,
          'complaint_assign_status'=> 1,
          'created_by' => \Auth::user()->id,
        ]);
        foreach($tickets as $checklistId){

          ComplaintServiceReportChecklist::create([
            'checklist_id' =>$checklistId,
            'complaint_service_report_id' => $serviceReport->id,
          ]);
          // Update service report generated status
          ComplaintChecklist::where('id',$checklistId)->update([
            'service_report_status'=>1,
            'ticket_status'=>1
          ]);
        }
      }
    */
      $inventory_items = Inventory::orderBy('inventories_name')->get(); //$inventory_items = Inventory::get(); 

      $ticket =  ComplaintServiceReportChecklist::whereIn('checklist_id',$tickets)->first(); 
      $checklists = ComplaintChecklist::whereIn('id',$tickets)->get();

      $complaintServiceReportInv = ComplaintServiceReportInv::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();

      $ServiceReportNotes = ComplaintServiceReportNote::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();
      
      $images = ComplaintServiceReportImage::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();
      $supported_image = array('pdf','docx','doc');
      
      return view('maintenance::ComplaintStages.service_report_view',compact('ticket','complaintServiceReportInv','inventory_items','ServiceReportNotes','checklists','tickets','work_flow','images','supported_image'));

    }
    /*
    *
    * Update  service report via Button
    *
    *
    */
    public function serviceReportStatusButtonUpdate($id,$status)
    {

      $url = url()->previous();
      $route = 'complaintSubAssignedList';
      $report_checklists = ComplaintServiceReportChecklist::where('complaint_service_report_id',$id)->get();
      $tickets = $report_checklists->pluck('checklist_id');
      $checklists = ComplaintChecklist::whereIn('id',$tickets)->get();
      $complaint_idd = ComplaintChecklist::whereIn('id',$tickets)->first();

      if(empty($complaint_idd->sub_assigned_to)){ 
        $route = 'complaintAssignedList';
      }

      ComplaintServiceReport::where('id',$id)->update([
        'complaint_assign_status' => $status,
        ]);
      
      ComplaintChecklist::whereIn('id',$tickets)->update([
        'ticket_status' => $status,
        ]);

      if($status == 2){

        $userId   = $checklists->pluck('assigned_to')[0]; 
        $userList= User::role(['maintenance_coordinator'])->pluck('id');
        
        $userList_maint_coordinator = \App\User::whereIn('id',$userList)->get();
        $userList_maint_supervisor = \App\User::where('id',$userId)->get();
      
        $complaint_idd = ComplaintChecklist::whereIn('id',$tickets)->first();
        $complaint_enquiries_id = $complaint_idd->complaint_enquiries_id;
        $complaintEnqu = ComplaintEnquiry::where('id',$complaint_enquiries_id)->first();
        $complaint_ticket_no = $checklists->pluck('complaint_ticket_no')->toArray();

        $TicketNO = implode ('' , $complaint_ticket_no );

        
        clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaintEnqu->id);
    
        $complaintEnqu->text = "Complaint - ".$complaintEnqu->complaint_no." Tickets-". $TicketNO." To Attended "; 
        $complaintEnqu->href =  route('complaintReview.view',$id);
        event(new ComplaintAssigned($complaintEnqu,$userList_maint_coordinator));
        
        $complaintEnqu_supervisor = ComplaintEnquiry::where('id',$complaint_enquiries_id)->first();
         $complaintEnqu_supervisor->text = "Complaint - ".$complaintEnqu->complaint_no." Tickets-". $TicketNO." To Attended "; 
        event(new ComplaintAssigned($complaintEnqu_supervisor,$userList_maint_supervisor)); 
        
      }
      if($status == 4){

        ComplaintChecklist::whereIn('id',$tickets)->update([
          'ticket_status'=>4,
          'work_flow_processes_code'=>705,
          ]);
        $complaint_idd = ComplaintChecklist::whereIn('id',$tickets)->first();
        $complaint_enquiries_id = $complaint_idd->complaint_enquiries_id;
        $complaintEnqu = ComplaintEnquiry::where('id',$complaint_enquiries_id)->first();
        if(empty($complaint_idd->sub_assigned_to)){ 
          $route = 'complaintAssignedList';
        }
        ComplaintEnquiry::where('id',$complaint_enquiries_id)->update([
          'complaint_status' => 1,
          ]);
        $complaint_ticket_no = $checklists->pluck('complaint_ticket_no')->toArray();

        $TicketNO = implode ('' , $complaint_ticket_no );
        /* Notification */
        $users = User::role(['maintenance_coordinator','maintenance_supervisor'])->get(); 
        $users = array_flatten($users);              
        clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaintEnqu->id);

        $complaintEnqu->text = "Complaint - ".$complaintEnqu->complaint_no." Tickets-". $TicketNO." To Closed "; 
        event(new ComplaintAssigned($complaintEnqu,$users));

      }

      switch($status){
        case "2":
        return redirect()->route($route);
        break;
        case "3":
        return redirect($url);
        break;
        case "4":
        return redirect()->route($route);
        break;

      }
      

    }
    /*
    *
    *
    * After getting Signature to update status and ticket closed
    *
    *
    */
    public function generateServiceReport(Request $request)
    {


      $tickets = Session::get('tickets');
      $url = route('complaintSubAssignedList');

      $filename = "yourimage.png"; // The filename - this could be specified by the user as another form field
      $filetype = "png"; // The file image type
      //$tickets = $request['assigned_ticket'];
      $checklists = ComplaintChecklist::whereIn('id',$tickets)->get();
      $complaint =  $checklists->pluck('complaint_enquiries_id');
      $complaintEnqu = ComplaintEnquiry::whereIn('id',$complaint)->first();
      $complaint_ticket_no = $checklists->pluck('complaint_ticket_no')->toArray();

      $TicketNO = implode ('' , $complaint_ticket_no );

      $tenantId = $complaintEnqu->tenant_id;
      $complainer_name = $complaintEnqu->complainer_name;
      $img = $request['saveSignature']; 
      $testSignature = $request['testSignature']; 
      if($testSignature != null)
      {
        $png_url = time().".png";
        $path = public_path('img').'/' . $png_url;

        Image::make(file_get_contents($img))->save($path,100); 
        $img_path_ar =    Storage::putFile('public/TenantSignature', new File($path), 'public');  
        $complaint_report_id = ComplaintServiceReportChecklist::whereIn('checklist_id',$tickets)->first();

        // Update Closed status for Each ticket against service report
        foreach($tickets as $assigned_ticket){
          ComplaintChecklist::where('id',$assigned_ticket)->update([
            'ticket_status'=>4,
            'work_flow_processes_code'=>705
            ]);
          
        }
        // Update completed status for service report
        ComplaintServiceReport::where('id',$complaint_report_id->complaint_service_report_id)->update([
          'complaint_assign_status'=>3,
          'tenant_signature'=>$img_path_ar,
          'tenant_id'=>$tenantId
          ]);
        $complaint_idd = ComplaintChecklist::whereIn('id',$tickets)->first();
        $complaint_enquiries_id = $complaint_idd->complaint_enquiries_id;
        
        ComplaintEnquiry::where('id',$complaint_enquiries_id)->update([
          'complaint_status' => 1,
          ]);
        /* Notification */
        $users = User::role(['maintenance_coordinator'])->get(); 
        $users = array_flatten($users);              
        clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaintEnqu->id);

        $complaintEnqu->text = "Complaint - ".$complaintEnqu->complaint_no." Tickets-". $TicketNO." To Closed "; 
        event(new ComplaintAssigned($complaintEnqu,$users)); 

         $complaintEnqu->subject = "Complaint Completed";
       
       $complaintEnqu->textContent ="Your complaint No ".$complaintEnqu->complaint_no." is successfully attended by Al Habib Maintenance Team. Thanks for your kind co-operation."; 

       $tenant_email = $complaintEnqu->tenant->tenant_personal_email ?? $complaintEnqu->tenant->tenant_contact_email;

        $user_name = $complaintEnqu->tenant->tenant_name;
        $mobile = $complaintEnqu->tenant->tenant_contact_no ?? $complaintEnqu->tenant->tenant_residence_tel;

        $msg = "Complaint Completed";
        $params = 'optional data';
        if(!empty($tenant_email)){
      Mail::to($tenant_email)->send(new ComplaintStageEmail($complaintEnqu,$user_name)); //Email Notification
    }else{
      sendSms($mobile,$msg,$params); //SMS Notification
    }
      }
      
      return $url;
      

    }
    /*
    *
    *
    * Review List
    *
    *
    */
    public function reviewList(Request $request)
    {

      $name = Route::currentRouteName();

      $enquiry_fields = [
      'complaint_no' => 'Complaint No',
      'complainer_name' => 'Complaint Name',
      'complaint_mob_no' => 'Complaint Mobile No',
      'location__locations_name' => 'Location',
      'unit__unit_code' => 'Unit',

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

      $request->flash();
      $user = \Auth::user();
      $roles = $user->getRoles();
      $rolesNames = $user->getRoleNames()->toArray();

      $ComplaintEnquiries = ViewComplaintReview::whereIn('complaint_assign_status',[2,5,6])->landlordApprovalPending($request)->landlordReview($request)->additionalColum($request)->select('service_report_id','complaint_enquiries_id', 'complainer_name', 
       'complaint_no', 'complaint_mob_no', 'complaint_date', 'service_report_no', 
       'complaint_assign_status', 'building_name', 'unit_code', 'locations_name','assigned_to','assigned_to_type')
      ->groupBy('service_report_no','service_report_id','complaint_enquiries_id', 'complainer_name', 
       'complaint_no', 'complaint_mob_no', 'complaint_date','complaint_assign_status', 'building_name', 'unit_code', 'locations_name','assigned_to','assigned_to_type')
      ->filter($request)->sortable()->paginate($this->noOfRecord);
	 //  dd($ComplaintEnquiries);


      $route   =  $request->url();           

      if(isset($request->ajax))       
        return view('maintenance::ComplaintStages.complaint_review_list_ajax',compact('ComplaintEnquiries','request','route'));

      return view('maintenance::ComplaintStages.complaint_review_list',compact('ComplaintEnquiries','request','enquiry_fields','operations','name'));

    }
    /*
    *
    * Review View Page
    *
    */
    public function reviewView($id)
    {
      /*$lists = ComplaintChecklist::where('complaint_enquiries_id',$id)->where('work_flow_processes_code', '=', 703)->groupBy('sub_assigned_to','id')->get();
      $tickets = $lists->pluck('id');*/

      $ticket =  ComplaintServiceReportChecklist::where('complaint_service_report_id',$id)->first();
      $tickets =  ComplaintServiceReportChecklist::where('complaint_service_report_id',$id)->pluck('checklist_id');

      $checklists = ComplaintChecklist::whereIn('id',$tickets)->get();
      $complaint_enquiries_id = $checklists->pluck('complaint_enquiries_id');
      $complaintEnquiry = ComplaintEnquiry::whereIn('id',$complaint_enquiries_id)->first();
      $workOrderDetail = ComplaintServiceReport::where('id',$ticket->complaint_service_report_id)->first();

      $ServiceReportNotes = ComplaintServiceReportNote::where('complaint_service_report_id',$ticket->complaint_service_report_id)->get();
     
      readNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaintEnquiry->id);
      return view('maintenance::ComplaintStages.complaint_review_view',compact('ServiceReportNotes','checklists','ticket','workOrderDetail'));

    }
    /*
    *
    *
    * Closed List
    *
    *
    */
    public function closedList(Request $request,$result = array())
    {

      $name = Route::currentRouteName();

      $enquiry_fields = [
      'complaint_no' => 'Complaint No',
      'complainer_name' => 'Complaint Name',
      'complaint_mob_no' => 'Complaint Mobile No',
      'location__locations_name' => 'Location',
      'unit__unit_code' => 'Unit',

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

      $request->flash();

      $user = \Auth::user(); 
      $roles = $user->getRoles();
      $rolesNames = $user->getRoleNames()->toArray();


      $ComplaintEnquiries = ComplaintEnquiry::whereDoesntHave('complaintTicketsAll', function ($query)use($result,$user,$roles) {
        $query->where('ticket_status','<',4)
        ->where('work_flow_processes_code', '<', 706);
      })
      ->whereHas('complaintTicketsAll', function ($query){
        $query->assignedUsers()
        ->whereHas('complaintProcessAll', function ($query){
          $query->complaintUsers();                       
        }) ;                       
      })                        
      ->where('complaint_status',1)
      ->filter($request)
      ->orderBy('updated_at','DESC')
      ->sortable()->paginate($this->noOfRecord);

        /*if (in_array('super_admin', $rolesNames) === false) {

          $closed_list->whereHas('complaintUsers', function ($query) use($roles) {
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
          }*/
    //  $ComplaintEnquiries = $closed_list->paginate(10);      
          $route   =  $request->url();          
          
          if(isset($request->ajax))       
            return view('maintenance::ComplaintStages.complaint_closed_list_ajax',compact('ComplaintEnquiries','request','route'));

          return view('maintenance::ComplaintStages.complaint_closed_list',compact('ComplaintEnquiries','request','enquiry_fields','operations','name'));

        }
    /*
    *
    *
    * Closed View
    *
    *
    */
    public function closedView($id)
    {
      clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$id);
      readNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$id);
      $ComplaintEnquiries = ComplaintEnquiry::where('id',$id)->first();
      $tickets = ComplaintChecklist::where('complaint_enquiries_id',$id)->get();
      return view('maintenance::ComplaintStages.complaint_closed_view',compact('ComplaintEnquiries','tickets'));
    }
    /*
    *
    *
    * Completely Closed List
    *
    *
    */
    public function completelyClosedList(Request $request,$result = array())
    {

      $name = Route::currentRouteName();
      $enquiry_fields = [
      'complaint_no' => 'Complaint No',
      'complainer_name' => 'Complaint Name',
      'complaint_mob_no' => 'Complaint Mobile No',
      'location__locations_name' => 'Location',
      'unit__unit_code' => 'Unit',

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

      $request->flash();
      $user  = \Auth::user();
      $roles = $user->getRoles();
      $rolesNames = $user->getRoleNames()->toArray();

      $ComplaintEnquiries = ComplaintEnquiry::whereDoesntHave('complaintTicketsAll', function ($query)use($result,$roles,$user) {
        $query->where('ticket_status','<',4)
        ->where('work_flow_processes_code', '=', 706);
      })
      ->whereHas('complaintTicketsAll', function ($query){   
       $query->whereHas('complaintProcessAll', function ($query){
         $query->complaintUsers();                       
       })->assignedUsers();                                   
     })                                   
      ->where('complaint_status',2)
      ->orderBy('updated_at','DESC')
      ->filter($request)
      ->sortable()
      ->paginate($this->noOfRecord);

       /* if (in_array('super_admin', $rolesNames) === false) {

          $completely_closed_list->whereHas('complaintUsers', function ($query) use($roles) {
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
          }*/
   //   $ComplaintEnquiries = $completely_closed_list->paginate(10);


          $route   =  $request->url();
          
          
          if(isset($request->ajax))       
            return view('maintenance::ComplaintStages.complaint_completely_closed_list_ajax',compact('ComplaintEnquiries','request','route'));

          return view('maintenance::ComplaintStages.complaint_completely_closed_list',compact('ComplaintEnquiries','request','enquiry_fields','operations','name'));

        }
    /*
    *
    *
    * Completely Closed View
    *
    *
    */
    public function completelyClosedView($id)
    {
      clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$id);
      readNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$id);
      $ComplaintEnquiries = ComplaintEnquiry::where('id',$id)->first();
      $tickets = ComplaintChecklist::where('complaint_enquiries_id',$id)->get();
      return view('maintenance::ComplaintStages.complaint_completely_closed_view',compact('ComplaintEnquiries','tickets'));
    }
    /*
    * Assign Notify
    *  
    */
    public function closeNotify($complaint_id){

      $process =   ComplaintProcess::where('complaint_enquiries_id',$complaint_id)->latest()->first();     
      $complaintUsers =  $process->complaintUser; 


      $users = array();     

      foreach($complaintUsers as $val){

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
     //Sub Assign Users  Select  
     $ticket_list =   ComplaintEnquiry::with(['complaintTicketsAll'])
                     ->whereHas('complaintTicketsAll',function($query){
                        $query->where('assigned_to_type',0)
                              ->where('assigned_to','>',0);
                     })
                     ->where('id',$complaint_id)
                     ->first();

    if(!empty($ticket_list)){

      $ticket_list_users = $ticket_list
                           ->complaintTicketsAll
                           ->pluck('assigned_to');

       $ticket_list_users = $ticket_list_users->toArray();  
       $ticket_list_users = array_flatten($ticket_list_users);    
       $users = array_merge($users,$ticket_list_users);

    }


             
    $users_notify =  \App\User::role(['maintenance_coordinator'])
                              ->orWhereIn('id',$users)
                              ->get(); 
    clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$complaint_id);
    $complaintDetail =  ComplaintEnquiry::find($complaint_id);   
    $complaintDetail->href = url('CompletelyClosedList/'.$complaint_id); 
    $complaintDetail->text = "Complaint - ".$complaintDetail->complaint_no."  Closed  "; 
    event(new ComplaintAssigned($complaintDetail,$users_notify));        
    
  }
  /*
  *  
  * Complaint assign Reminder
  *
  */
  public function assignReminder($id)
  {


    $complaintChecklist = ComplaintChecklist::where('id',$id)->first();
    $complaintEnquiry = ComplaintEnquiry::where('id',$complaintChecklist->complaint_enquiries_id)->first();
    $process =   ComplaintProcess::where('complaint_enquiries_id',$complaintChecklist->complaint_enquiries_id)->latest()->first();   
    $complaintUsers =  $process->complaintUser;
   // dd($complaintUsers);
    /*
    $users = array();     
        
      foreach($complaintUsers as $val){
      
        if(empty($val->user_id)){
          
           $users_list = \App\User::role($val->role_id)->get()->pluck('id');         
           $users_list = $users_list->toArray();  
           
           if(count($users) > 0){          
            $users = $users->merge($users_list);
           }else           
            $users[] = $users_list;   
          
        }else 
         $users[] = $val->user_id;
      
      }
      
      $users = array_flatten($users);    */  
      $users_notify =  \App\User::where('id',$complaintChecklist->assigned_to)->get();
      $roles = Role::whereIn('id',$users_notify->pluck('default_role'))->first()->name;
      $complaintEnquiry->text = " Complaint Reminder -  Sub Assign  ".$complaintEnquiry->complainer_name. " [".$complaintEnquiry->complaint_no."] Ticket-". $complaintChecklist->complaint_ticket_no;   
      $complaintEnquiry->href = url('AssignedList/'.$complaintChecklist->id);
      event(new ComplaintReminder($complaintEnquiry,$users_notify));       
      session()->flash('success', ' Notification Send To '.ucwords(str_replace('_', ' ',$roles)));

      return redirect()->route('complaintAssignedList');       
    }
  /*
  *
  * Closed Detial view for service report
  *
  *
  */
  public function closedDetailedView($id)
  {
    $serviceReport =  ComplaintServiceReportChecklist::where('checklist_id',$id)->first();
    
    $reportDetail = ComplaintServiceReport::where('id',$serviceReport->complaint_service_report_id)->first();
    $ServiceReportNotes = ComplaintServiceReportNote::where('complaint_service_report_id',$serviceReport->complaint_service_report_id)->get();
    $complaintServiceReportInv = ComplaintServiceReportInv::where('complaint_service_report_id',$serviceReport->complaint_service_report_id)->get();
    $images = ComplaintServiceReportImage::where('complaint_service_report_id',$serviceReport->complaint_service_report_id)->get();
    $supported_image = array('pdf','docx','doc');
    return view('maintenance::ComplaintStages.complaint_closed_detail_view_modal',compact('supported_image','ServiceReportNotes','complaintServiceReportInv','images','reportDetail'));

  }
  /*
  *
  * Send Landlord Approval OR update Status from service report
  *
  *
  */
  public function ServiceReportStatusLandlord($id,$status)
  {
    $tickets = ComplaintServiceReportChecklist::where('complaint_service_report_id',$id)->pluck('checklist_id');//dd($status);
    ComplaintServiceReport::where('id',$id)->update([
      'complaint_assign_status' => $status,
      'landlord_approval_date' => date("Y-m-d H:i:s"),
      ]);
    if($status == 1){
      ComplaintChecklist::whereIn('id',$tickets)->update([
        'ticket_status' => $status,
        ]);

    }
    return redirect()->route('complaintReviewList');

  }
  public function signature()
  {

    return view('maintenance::signature');
  }
  /*
    *
    *
    * Complaint Unassigned Index
    *
    */
  public function complaintUnassignedSearch(Request $request,$result = array())
  {
    $name = Route::currentRouteName();
    $enquiry_fields = [
    'complaint_no' => 'Complaint No',
    'complainer_name' => 'Complaint Name',
    'complaint_mob_no' => 'Complaint Mobile No',
    'location' => 'Location',
    'unit' => 'Unit',
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

      $ComplaintEnquiry =   new MaintenanceController;      
      $result =     $ComplaintEnquiry->enquirySearch($request);   


    }
    $request->flash();      
    $ComplaintEnquiries = ComplaintEnquiry::closure($result)->sortable()->paginate(10);
    if(isset($request->route))
      $route   =  $request->route;


    if(isset($request->ajax))       
      return view('maintenance::ComplaintStages.complaint_unassigned_list_ajax',compact('ComplaintEnquiries','request','route'));

    return view('maintenance::ComplaintStages.complaint_unassigned_list',compact('ComplaintEnquiries','request','enquiry_fields','operations','name'));
  }
    /*
    *
    * multiple Image uploaed against service report 
    *
    *
    */
    public function reportImageUpload(Request $request)
    {

      $this->validate($request, [
        //'report_image_file_name'    => 'required|mimes:png,jpeg,jpg|max:2000',   
        'report_image_file_name'    => 'required|max:2000',             
        ]);

      $url = url()->previous();
      $images = $request->file('report_image_file_name');
      $serviceReport_id = $request->serviceReport_id;
      $report_stage = $request->report_stage;

      if(!empty($images)):
        $files = $request->file('report_image_file_name');
      foreach($files as $key => $file):

       $path = public_path('img');

     $imageName = time().'.'.$request['report_image_file_name'][$key]->getClientOriginalExtension();

     if($request['report_image_file_name'][$key]->getClientOriginalExtension() === 'pdf' || $request['report_image_file_name'][$key]->getClientOriginalExtension() === 'doc' ||
       $request['report_image_file_name'][$key]->getClientOriginalExtension() === 'docx' ){

      $thumb_path = Storage::putFile('public/MaintenanceServiceReportImages', $request['report_image_file_name'][$key]);
    $img_path 	= $thumb_path;
  }
  else{

    $large_img = Image::make($request->file('report_image_file_name')[$key]->getRealPath());
    $large_img->resize(800, 500);
    $large_img->save($path.'/'.$imageName,100);
    $img_path =    Storage::putFile('public/MaintenanceServiceReportImages', new File($path.'/'.$imageName), 'public');

    $thumb_img = Image::make($request->file('report_image_file_name')[$key]->getRealPath());
    $thumb_img->resize(150, 100);
    $thumb_img->save($path.'/'.$imageName,100);           
    $thumb_path = Storage::putFile('public/MaintenanceServiceReportImages', new File($path.'/'.$imageName), 'public');
  }

  $image = ComplaintServiceReportImage::create(['complaint_service_report_id'=>$serviceReport_id,
    'image_path_file_name'=>$img_path,
    'image_path_thumbnail'=>$thumb_path,
    'stage'=>$report_stage,
    'created_by' => \Auth::user()->id]);
  endforeach;
  endif;
  session()->flash('success', 'Image Uploaded Successfully');

  return redirect($url); 

}
    /*
    *
    *
    * Service Report Image delete
    *
    */
    public function destroyImage($id)
    {
      $ComplaintServiceReportImage = ComplaintServiceReportImage::find($id);
      
      Storage::delete($ComplaintServiceReportImage->image_path_file_name);
      Storage::delete($ComplaintServiceReportImage->image_path_thumbnail);
      $ComplaintServiceReportImage->delete();
    }

    public function ServiceReportStatusLandlordModal(Request $request){
      $complaint_service_report_id = $request->complaint_service_report_id;
      $status = $request->status;

      return view('maintenance::ComplaintStages.service_report_status_modal',compact('complaint_service_report_id','status'));

    }
    public function ServiceReportStatusLandlordModalAction(Request $request){
      $complaint_service_report_id                  = $request->complaint_service_report_id;
      $stage              = $request->status;
      $desc              = $request->desc;

      $tickets = ComplaintServiceReportChecklist::where('complaint_service_report_id',$complaint_service_report_id)->pluck('checklist_id');//dd($status);
      ComplaintServiceReport::where('id',$complaint_service_report_id)->update([
        'complaint_assign_status' => $stage,
        ]);

      $complaintServiceReportNote = ComplaintServiceReportNote::create([
        'complaint_service_report_id'=>$complaint_service_report_id,
        'stage' =>$stage,
        'desc' =>$desc,
        'created_by' => \Auth::user()->id,
        ]);

      if($stage == 1){
        ComplaintChecklist::whereIn('id',$tickets)->update([
          'ticket_status' => $stage,
          ]);
      }
      return redirect()->route('complaintReviewList');
    }
  }
