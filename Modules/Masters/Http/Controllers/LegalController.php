<?php

namespace Modules\Masters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Entities\LegalUser;
use Modules\Masters\Entities\LegalNotes;
use Modules\Masters\Entities\LegalDocuments;
use Modules\BackOffice\Entities\Pdc;
use Modules\Sales\Entities\TenantContract;
use Modules\General\Entities\WorkFlowProcess;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Modules\General\Http\Controllers\GeneralController as General;
use Modules\BackOffice\Http\Controllers\TenantContractEditController as Tenant;
use App\Setting;
use DB;
use Session;
use URL;
use Route;
use App\User;
use Illuminate\Support\Facades\Mail;
use Modules\Masters\Events\LegalApprove;
use Modules\Masters\Events\LegalReferBack;
use Modules\BackOffice\Services\LegalMessages;
use Modules\Masters\Services\LegalCase;

class LegalController extends Controller
{
  public  $datas = [];
  public function __construct()
  {
    $this->middleware('auth'); 
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
      
      $request->flash(); 
      
      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray(); 

      $legalCasesBase = Legal::areLegalBuilding()
                           ->filter($request)
                           ->lawyerReferBack($request)
                           ->whereHas('legalUsers', function ($query) {
                               $query->where('status','=',1);
                             });
      $legalCases = Legal::newestMovedFirst(Legal::latestPerGroup($legalCasesBase)->sortable())->paginate($this->noOfRecord);
      Legal::attachGroupSiblings($legalCases, $legalCasesBase);
	

/*
      if (in_array('are', $rolesNames) === true) {
        $legalCases = Legal::whereHas('building', function ($query) use($rolesNames) {
          $query->whereHas('areBuildings', function ($query)use($rolesNames) {
            if (in_array('are', $rolesNames) === true) {
              $query->where('user_id','=', \Auth::user()->id);
            }
          });
        })->closure($result)->whereHas('legalUsers', function ($query) {
         $query->where('status','=',1);
       })->sortable()->paginate(10);
      }
      else{
        $legalCases = Legal::closure($result)->whereHas('legalUsers', function ($query) {
         $query->where('status','=',1);
       })->sortable()->paginate(10);
      }
*/
      
   // dd($legalCases);
      
      $quick_url =   $route   =  route('legalCase.index');

      if(isset($request->ajax))
        return view('masters::Legal.legal_case_list_ajax',compact('legalCases','request','route'));

      return view('masters::Legal.legal_case_list',compact('legalCases','request','enquiry_fields','operations','name','quick_url'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      return view('masters::create');
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
    public function show(Legal $legalCase)
    {
      clearNotification('Modules\Masters\Notifications\LegalNotification',$legalCase->id);
      readNotification('Modules\Masters\Notifications\LegalNotification',$legalCase->id); 
      $tenant_contract_id=$legalCase->tenant_contract_id;
      $notes=Legal::where('tenant_contract_id',$tenant_contract_id)->get();
      $owed = LegalCase::owed($legalCase);
      return view('masters::Legal.legal_case_view',compact('legalCase','notes','owed'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
      return view('masters::edit');
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
    public function destroy($id)
    {
      $LegalDocuments = LegalDocuments::find($id);
      Storage::delete($LegalDocuments->legal_documents_file_name);
      $LegalDocuments->delete();
    }
    //Advance search
    public function legalCaseSearch(Request $request){

      $closure = array();
      $closure_or = array();
      $building = array();
      $building_or = array(); 
      $unit = array();
      $unit_or = array();
      $tenant = array();
      $tenant_or = array(); 
      $tenantname = array();
      $tenantname_or = array();  
      $workflow = array();
      $workflow_or = array();  
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
          }elseif($value == 'building_name'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $building_or[] = array( $value , $operation ,$fieldValue);
            else
              $building[] = array( $value , $operation ,$fieldValue);

          }elseif($value == 'unit_code'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $unit_or[] = array( $value , $operation ,$fieldValue);
            else
              $unit[] = array( $value , $operation ,$fieldValue);

          }elseif($value == 'tenant_contract_no'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $tenant_or[] = array( $value , $operation ,$fieldValue);
            else
              $tenant[] = array( $value , $operation ,$fieldValue);

          }elseif($value == 'tenant_name'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $tenantname_or[] = array( $value , $operation ,$fieldValue);
            else
              $tenantname[] = array( $value , $operation ,$fieldValue);

          }else{                
            $fieldValue = $request->fieldValue[$key];
            $operation = $request->operation[$key];
          }

          if($value != 'building_name' &&  $value != 'unit_code' &&  $value != 'tenant_contract_no' &&  $value != 'tenant_name' &&   (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
           if($key != 0 && $request->logic[$key -1 ] == 'or' )
             $closure_or[] = array( $value , $operation ,$fieldValue);
           else
             $closure[] = array( $value , $operation ,$fieldValue);
         }

       }


     }


     if($request->ajax != true){
       if(count($building_or) == 0 && count($closure) == 0 &&  count($building) == 0 && count($unit) == 0 && count($unit_or) == 0  && count($tenant) == 0  && count($tenant_or) == 0 && count($tenantname) == 0  && count($tenantname_or) == 0 && count($workflow) == 0  && count($workflow_or) == 0 )
        $closure[] = array( 'id' , '=' ,0);
    }

  }

}
   //  dd($closure_date);


$tenant_contract_no = (isset($request->tenant_contract_no)) ? $request->tenant_contract_no : null;
$tenant_id = (isset($request->tenant_id)) ? $request->tenant_id : null;    
$building_id = (isset($request->building_id)) ? $request->building_id : null;
$unit_id = (isset($request->unit_id)) ? $request->unit_id : null;
$tenant_contract_valid_to_date = (isset($request->tenant_contract_valid_to_date)) ? $request->tenant_contract_valid_to_date : null; 
$tenant_contract_status = (isset($request->tenant_contract_status)) ? $request->tenant_contract_status : null;   
$tenant_contract_rent = (isset($request->tenant_contract_rent)) ? $request->tenant_contract_rent : null;  
$work_flow_processes_id = (isset($request->work_flow_processes_id)) ? $request->work_flow_processes_id : null;  

$qiuck_search = array($tenant_contract_no,$tenant_id, $building_id , $unit_id, $tenant_contract_valid_to_date,$tenant_contract_status,$tenant_contract_rent,$work_flow_processes_id);
if($request->ajax != true){

  $qiuck_search = array();
}
$result = array(
 $closure,$closure_or,$building, $building_or,$unit, $unit_or, $tenant, $tenant_or,$tenantname, $tenantname_or,$workflow, $workflow_or,$qiuck_search
 ); 
     //dd($result);

return $result;       


}
public function legalSearch(Request $request,$result = array())
{
  $name = Route::currentRouteName();
  $enquiry_fields = [
  'tenant_contract_no' => 'Agreement No',
  'tenant_name' => 'Name',
  'building_name' => 'Building',
  'unit_code' => 'Unit',
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

    $legalCase =   new LegalController;       
    $result =     $legalCase->legalCaseSearch($request); 
    $request->flash(); 

  }

  $legalCases = Legal::closure($result)->whereHas('legalUsers', function ($query) {
   $query->where('status','=',1);
 })->sortable()->paginate(10);
   // dd($legalCases);
  if(isset($request->route))
    $route   =  $request->route;

  if(isset($request->ajax))
    return view('masters::Legal.legal_case_list_ajax',compact('legalCases','request','route'));

  return view('masters::Legal.legal_case_list',compact('legalCases','request','enquiry_fields','operations','name'));
}
public function enquiryFilter(){


 $enquiry_fields = [
 'tenant_contract_no' => 'Agreement No',
 'tenant_name' => 'Name',
 'building_name' => 'Building',
 'unit_code' => 'Unit',
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

 return view('masters::Legal.legal_filter',compact('enquiry_fields','operations'));


}
public function plmsApproval(Request $request){

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

 $roles = \Auth::user()->getRoles();
 $user = \Auth::user();
 $rolesNames = \Auth::user()->getRoleNames()->toArray();

 $request->flash(); 

$user_id =  \Auth::user()->id;
$status = 801;
if(isset($request->md)){
  $roles=array(1);
  $user_id = 1;
  if(isset($request->selected_status)){
   $status = (trim($request->selected_status)=='close')?802:(($request->selected_status=='reject')?804:801);
   } 
}

$plmsApprovalsBase = Legal::filter($request)
                      ->whereHas('legalUsers', function ($query) use($user,$roles,$user_id) {
                         $query->when( !($user->hasRole('super_admin','md')),function($query)use($user,$roles,$user_id){
                             $query->where(function ($query) use($user,$roles,$user_id){
                                $query->where('user_id',null)
                                      ->whereIn('role_id', $roles)
									  ->where('status','=',1);
                                    })
                                      ->orWhere(function ($query) use($user,$roles,$user_id){
                                          $query->where('user_id','>',0)
                                                ->whereIn('role_id', $roles)
                                                ->where('user_id','=', $user_id)
												->where('status','=',1);
                                  });   
                          })
                         ->where('status','=',1);
                      })                  
                      ->whereNull('legal.closed_at')
                      ->where('legal.work_flow_processes_code', '=', $status);
$plmsApprovals = Legal::newestMovedFirst(Legal::latestPerGroup($plmsApprovalsBase)->sortable())->paginate($this->noOfRecord);
Legal::attachGroupSiblings($plmsApprovals, $plmsApprovalsBase);

/*
if (in_array('super_admin', $rolesNames) === false) {
  $plmsApproval->whereHas('legalUsers', function ($query) use($roles) {
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
$plmsApprovals = $plmsApproval->paginate(10);*/

$quick_url =  $route =  route('plmsApproval');

if(isset($request->ajax))
  return view('masters::Legal.plms_approval_list_ajax',compact('plmsApprovals','request','route'));

return view('masters::Legal.plms_approval_list',compact('plmsApprovals','request','enquiry_fields','operations','name','quick_url'));

}
public function plmsSearch(Request $request,$result = array()){
 $name = Route::currentRouteName();
 $enquiry_fields = [
 'tenant_contract_no' => 'Agreement No',
 'tenant_name' => 'Name',
 'building_name' => 'Building',
 'unit_code' => 'Unit',
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

  $plmsApproval =   new LegalController;       
  $result =     $plmsApproval->legalCaseSearch($request); 
  $request->flash(); 
}
$plmsApproval = Legal::closure($result)->whereHas('legalUsers', function ($query) {
 $query->where('status','=',1);
})                  
->where('work_flow_processes_code', '=', 801);

if (in_array('super_admin', $rolesNames) === false) {
  $plmsApproval->whereHas('legalUsers', function ($query) use($roles) {
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
$plmsApprovals = $plmsApproval->paginate(10);
if(isset($request->route))
  $route   =  $request->route;

if(isset($request->ajax))
  return view('masters::Legal.plms_approval_list_ajax',compact('plmsApprovals','request','route'));

return view('masters::Legal.plms_approval_list',compact('plmsApprovals','request','enquiry_fields','operations','name'));

}
public function plmsApprovalShow($id){
  clearNotification('Modules\Masters\Notifications\LegalNotification',$id);
  readNotification('Modules\Masters\Notifications\LegalNotification',$id);
  $plmsApproval=Legal::where('id',$id)->first();
  $tenant_contract_id=$plmsApproval->tenant_contract_id;
  $notes=Legal::where('tenant_contract_id',$tenant_contract_id)->get();
  $timeline = LegalCase::timeline($plmsApproval->id);
  $outstanding = LegalCase::outstanding($plmsApproval);
  $defaultHandledBy = LegalCase::defaultHandledBy($plmsApproval->building_id);
  $owed = LegalCase::owed($plmsApproval);
  return view('masters::Legal.plms_approval_view',compact('plmsApproval','notes','timeline','outstanding','defaultHandledBy','owed'));

}
/**
 * Why the approve / refer back / reject / accept action cannot run on this case now, or null (user, 18/10/2026).
 * The case must still be at the step the form belongs to (a second click or an old tab used to move it again),
 * and the user must hold the step's permission; at lawyer approval only the assigned lawyer or PLM may act.
 */
private function stepError($legal, $processFlow, $actionKey, $areStatus)
{
  $user = \Auth::user();
  if (!$legal) {
    return 'The legal case was not found.';
  }
  if ($legal->closed_at) {
    return 'This legal case is already closed.';
  }
  // [case code the action belongs to, permission, label]
  if ($processFlow == 801 && $actionKey == 'APRV') {
    $step = [801, 'plms_approve', 'approve'];
  } elseif ($processFlow == 801 && $actionKey == 'RFRBK' && $areStatus == 1) {
    $step = [802, 'lawyer_refer_back', 'reject'];
  } elseif ($processFlow == 801 && $actionKey == 'RFRBK') {
    $step = [801, 'plms_refer_back', 'refer back'];
  } elseif ($processFlow == 802 && $actionKey == 'APRV') {
    $step = [802, 'lawyer_approve', 'accept'];
  } else {
    return 'Unknown action.';
  }
  if ((int) $legal->work_flow_processes_code !== $step[0]) {
    return 'This case is no longer at ' . $this->stepName($step[0]) . '; it is now at ' . $this->stepName($legal->work_flow_processes_code)
      . '. Refresh the page.';
  }
  if (!$user->can($step[1])) {
    return 'You are not allowed to ' . $step[2] . ' this case.';
  }
  if ($step[0] === 802 && !$this->mayActForLawyer($legal, $user)) {
    return 'This case is assigned to another lawyer.';
  }
  return null;
}

/** At lawyer approval: the assigned lawyer, any lawyer when none is set, or PLM on the lawyer's behalf. */
private function mayActForLawyer($legal, $user)
{
  return !$legal->lawyer_user_id || (int) $legal->lawyer_user_id === (int) $user->id
    || $user->hasRole(['super_admin', 'md', 'backoffice_manager']);
}

private function stepName($code)
{
  $names = [801 => 'PLM approval', 802 => 'lawyer approval', 803 => 'the case page (active)', 804 => 'referred back to ARE'];
  return isset($names[(int) $code]) ? $names[(int) $code] : 'step ' . $code;
}

private function personName($userId)
{
  $r = DB::table('users as u')->leftJoin('employees as e', 'e.id', '=', 'u.user_type_id')->where('u.id', $userId)
    ->select(DB::raw('coalesce(e.employee_name, u.username) as name'))->first();
  return $r ? $r->name : '';
}

//approve refer back modal
public function plmsApproveReferBack(Request $request){
  $legal_id = $request->legal_id;
  $process_flow = $request->process_flow;
  $action_key = $request->action_key;
  $are_status = $request->are_status;
  
  $legal = Legal::find($legal_id);
  $error = $this->stepError($legal, $process_flow, $action_key, $are_status);
  if ($error) {
    return view('masters::Legal._modal_message', ['title' => 'Cannot continue', 'message' => $error]);
  }
  $lawyers = LegalCase::lawyers();
  $defaultHandledBy = LegalCase::defaultHandledBy($legal->building_id);
  $outstanding = LegalCase::outstanding($legal);

  return view('masters::Legal.plms_approval_model',compact('legal_id','process_flow','action_key','are_status','legal','lawyers','defaultHandledBy','outstanding'));
}
public function plmsApproveReferBackStore(Request $request)
{
  $legal_id = $request->legal_id;
  $process_flow = $request->process_flow;
  $action_key = $request->action_key;
  $legal_note = $request->legal_note;
  $are_status = $request->are_status;
  
  

  $legal = Legal::where('id',$legal_id)->first();
  // the case must still be at the step the form was opened for, and the user allowed to act on it
  $error = $this->stepError($legal, $process_flow, $action_key, $are_status);
  if ($error) {
    session()->flash('error', $error);
    return redirect()->back();
  }
  $this->validate($request, ['legal_note' => 'required|max:1000']);
  $userId = \Auth::user()->id;
  // PLM accepting / rejecting in the lawyer's place is allowed and shows in the timeline
  if ((int) $legal->work_flow_processes_code === 802 && $legal->lawyer_user_id && (int) $legal->lawyer_user_id !== (int) $userId) {
    $legal_note .= ' (by ' . $this->personName($userId) . ' for the lawyer)';
  }

  if ($process_flow == 801 && $action_key == 'APRV') {
    $handledBy = (int) $request->input('handled_by', $legal->handled_by ?: LegalCase::HANDLED_ALHABIB);
    if ($handledBy !== LegalCase::HANDLED_ALHABIB) {
      return $this->handOver($request, $legal, $handledBy);
    }
    $lawyers = LegalCase::lawyers();
    $this->validate($request, [
      'priority' => 'required|in:' . implode(',', array_keys(LegalCase::PRIORITIES)),
      'case_type' => 'required|in:' . implode(',', array_keys(LegalCase::TYPES)),
      'lawyer_user_id' => 'required|in:' . (implode(',', array_keys($lawyers)) ?: '0'),
    ], ['lawyer_user_id.in' => 'Pick an active lawyer.']);
    $this->recordHandledBy($legal, $handledBy, $userId);
    $legal->update(['priority' => (int) $request->priority, 'case_type' => (int) $request->case_type,
                    'lawyer_user_id' => (int) $request->lawyer_user_id]);
    LegalCase::log($legal->id, 'plm_approved', 'Priority ' . LegalCase::PRIORITIES[(int) $request->priority]
      . ', ' . LegalCase::TYPES[(int) $request->case_type] . ', lawyer ' . $lawyers[(int) $request->lawyer_user_id]
      . '. ' . $legal_note, $userId);
  } elseif ($process_flow == 801 && $action_key == 'RFRBK' && $are_status == 1) {
    return $this->lawyerReject($legal, $legal_note, $userId);
  } elseif ($process_flow == 801 && $action_key == 'RFRBK') {
    LegalCase::log($legal->id, 'plm_referback', $legal_note, $userId);
  } elseif ($process_flow == 802 && $action_key == 'APRV') {
    LegalCase::log($legal->id, 'lawyer_approved', $legal_note, $userId);
  }

  $general =  new General;
  $next_process_id = $general->nextProcessFromAction($process_flow,$action_key);
  /*$work_flow_process=WorkFlowProcess::where('work_flow_processes_code',$next_process_id)->first();
  $work_flow_process_id=$work_flow_process->id;*/

  // current stage update (this case's users; it used to pick the newest case with the same code)
  $legal->legalUser()->update(['status' => 0]);

  $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);
	
	$legalCase = Legal::where('id',$legal_id)->first();
	
	Legal::where('id',$request->legal_id)->update(['work_flow_processes_code'=>$next_process_id,'are_status' => $are_status,'note' => $legal_note]);
	
	// if($process_flow==801){
		// $legalCase=Legal::create([ 
			   // 'building_id' => $legal->building_id,
			   // 'unit_id' => $legal->unit_id,
			   // 'tenant_id'=> $legal->tenant_id,
			   // 'tenant_contract_id' => $legal->tenant_contract_id,
			   // 'note' => $legal_note,
			   // 'work_flow_processes_code' => $next_process_id,
			   // 'are_status' => $are_status,
			   // 'created_by' => \Auth::user()->id
			   // ]); 
	// }


			

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
	

  
/*Notification Of users*/
$users = User::role(['legal_advisor'])->get(); 
$users = array_flatten($users); 

$are = User::role(['are'])->get(); 
$are = array_flatten($are);

$legalUsers = User::role(['legal_advisor'])->get(); 
// $legalUsers = User::role(['are','legal_advisor'])->get(); 
$legalUsers = array_flatten($legalUsers);


$otherUsers = User::role(['are','backoffice_manager'])->get(); 
$otherUsers = array_flatten($otherUsers);


 $legalCase->content = "Approved";
if($process_flow==801 && $action_key=='APRV'){
  
  $legalCase->href = url('lawyerApproval/'.$legalCase->id);
  event(new LegalApprove($legalCase,$users)); 


  // Legal advisor and ARE (system template legal_case_approved)
  LegalMessages::toUsers('legal_case_approved', $legalUsers, $legalCase->tenantContract);
  //update status as leagal approved
  

  session()->flash('success', 'Legal Case Approved Successfully');
  return redirect()->route('plmsApproval');

}elseif($process_flow==801 && $action_key=='RFRBK' && $are_status==0){

  $legalCase->href = url('legalCase/'.$legalCase->id);
  event(new LegalReferBack($legalCase,$are));

  // ARE (system template legal_case_referback — arrives switched off; it was commented out before)
  LegalMessages::toRoles('legal_case_referback', ['are'], $legalCase->tenantContract);

  session()->flash('success', 'Legal Case Refer Back Successfully');
  return redirect()->route('plmsApproval');
}elseif($process_flow==802 && $action_key=='APRV'){
	Legal::where('id',$request->legal_id)->update(['legal_is_closed'=>2]);
  // Back office and ARE (system template legal_advisor_approved — arrives switched off; it was commented out before)
  LegalMessages::toUsers('legal_advisor_approved', $otherUsers, $legalCase->tenantContract);

	

  session()->flash('success', 'Lawyer Approved Legal Case Successfully');
  return redirect()->route('lawyerApproval');
}elseif($process_flow==801 && $action_key=='RFRBK' && $are_status==1){
  $legalCase->href = url('legalCase/'.$legalCase->id);
  event(new LegalReferBack($legalCase,$are));

  // ARE (system template legal_advisor_referback)
  LegalMessages::toUsers('legal_advisor_referback', $are, $legalCase->tenantContract);


  session()->flash('success', 'Lawyer Refer Back Legal Case Successfully');
  return redirect()->route('lawyerApproval');
}else{
  session()->flash('success', 'Lawyer Approved Legal Case Successfully');
  return redirect()->route('lawyerApproval');
}

}




/**
 * PLM hands a case to the landlord, the government or MERA: who / reference / date are kept,
 * the case closes on our side and the contract stays in legal (status 5), so it keeps out of
 * the normal receivables.
 */
private function handOver(Request $request, Legal $legal, $handledBy)
{
  $this->validate($request, [
    'handled_by' => 'required|in:2,3,4',
    'handed_to_name' => 'required|max:150',
    'handover_ref' => 'nullable|max:100',
    'handover_date' => 'required|date|before_or_equal:today',
  ], ['handed_to_name.required' => 'Enter who the case is handed to.',
      'handover_date.before_or_equal' => 'The handover date cannot be in the future.']);
  $userId = \Auth::user()->id;
  $note = $request->legal_note;

  DB::transaction(function () use ($request, $legal, $handledBy, $userId, $note) {
    $this->handOverRecords($legal, $handledBy, $request->handed_to_name, $request->handover_ref, $request->handover_date, $note, $userId);
  });
  clearNotification('Modules\Masters\Notifications\LegalNotification', $legal->id);

  LegalMessages::toRoles('legal_case_handed_over', ['are', 'backoffice_manager'], $legal->tenantContract,
    ['handed_to' => LegalCase::HANDLED_BY[$handledBy] . ' - ' . $request->handed_to_name]);

  session()->flash('success', 'Legal case handed over to ' . LegalCase::HANDLED_BY[$handledBy] . ' and closed.');
  return redirect()->route('plmsApproval');
}

/** What handing a case over changes: handled by + handover details + timeline, closed as handed over, process users done. */
private function handOverRecords(Legal $legal, $handledBy, $name, $ref, $date, $note, $userId)
{
  $this->recordHandledBy($legal, $handledBy, $userId);
  $legal->update(['handed_to_name' => $name, 'handover_ref' => $ref, 'handover_date' => $date, 'note' => $note]);
  LegalCase::log($legal->id, 'handed_over', 'Handed over to ' . LegalCase::HANDLED_BY[$handledBy] . ': '
    . $name . ($ref ? ' (ref ' . $ref . ')' : '') . '. ' . $note, $userId, ['event_date' => $date]);
  LegalCase::close($legal, 'handed_over', null, $userId);
  $legal->legalUser()->update(['status' => 0]);
}

/**
 * Bulk hand over from Active cases (user, 20/10/2026): cases really handled by MERA, the government or
 * the landlord. Handled by follows the building (MERA / Government ownership); cases in other buildings
 * get the value picked. Same steps as the PLM hand over; only active cases handled by Al Habib; no messages.
 */
public function bulkHandOver(Request $request)
{
  $user = \Auth::user();
  abort_unless($user->can('plms_approve'), 403);
  $this->validate($request, [
    'handled_by'     => 'required|in:2,3,4',
    'handed_to_name' => 'nullable|max:150',
    'handover_ref'   => 'nullable|max:100',
    'handover_date'  => 'required|date|before_or_equal:today',
    'legal_note'     => 'nullable|max:1000',
    'ids'            => 'required|array|max:5000',
    'ids.*'          => 'integer',
  ], ['ids.required' => 'Select at least one case.', 'handover_date.before_or_equal' => 'The handover date cannot be in the future.']);

  $ids = array_values(array_unique(array_map('intval', $request->ids)));
  $note = trim((string) $request->legal_note);
  $note = ($note !== '' ? $note . ' ' : '') . '(bulk hand over, Active cases)';
  $done = [];
  foreach (Legal::whereIn('id', $ids)->get() as $legal) {
    if (!LegalCase::isActive($legal)) {
      continue;
    }
    $handledBy = LegalCase::defaultHandledBy($legal->building_id);
    if ($handledBy === LegalCase::HANDLED_ALHABIB) {
      $handledBy = (int) $request->handled_by;
    }
    $name = trim((string) $request->handed_to_name) !== '' ? trim($request->handed_to_name) : LegalCase::HANDLED_BY[$handledBy];
    DB::transaction(function () use ($legal, $handledBy, $name, $request, $note, $user) {
      $this->handOverRecords($legal, $handledBy, $name, $request->handover_ref, $request->handover_date, $note, $user->id);
    });
    clearNotification('Modules\Masters\Notifications\LegalNotification', $legal->id);
    $done[LegalCase::HANDLED_BY[$handledBy]] = (isset($done[LegalCase::HANDLED_BY[$handledBy]]) ? $done[LegalCase::HANDLED_BY[$handledBy]] : 0) + 1;
  }

  $count = array_sum($done);
  $parts = [];
  foreach ($done as $to => $k) {
    $parts[] = $k . ' to ' . $to;
  }
  $msg = $count . ' case' . ($count == 1 ? '' : 's') . ' handed over' . ($parts ? ' (' . implode(', ', $parts) . ')' : '') . '.';
  if (count($ids) > $count) {
    $msg .= ' ' . (count($ids) - $count) . ' skipped (no longer active or not handled by Al Habib).';
  }
  return redirect()->back()->with($count ? 'success' : 'error', $msg);
}

private function recordHandledBy(Legal $legal, $handledBy, $userId)
{
  $from = (int) ($legal->handled_by ?: LegalCase::HANDLED_ALHABIB);
  if ($from !== (int) $handledBy) {
    LegalCase::log($legal->id, 'handled_by', 'Handled by changed from ' . LegalCase::HANDLED_BY[$from]
      . ' to ' . LegalCase::HANDLED_BY[$handledBy] . '.', $userId);
    $legal->update(['handled_by' => $handledBy]);
  }
}

/**
 * The lawyer rejects a case: it goes back to PLM approval (801) with the reason, instead of the
 * old refer back to ARE.
 */
private function lawyerReject(Legal $legal, $reason, $userId)
{
  $general = new General;
  DB::transaction(function () use ($legal, $reason, $userId, $general) {
    $legal->legalUser()->update(['status' => 0]);
    $legal->update(['work_flow_processes_code' => 801, 'are_status' => 0, 'note' => $reason]);

    $processAssign = $general->roleUsersFromProcess(801, $location_id = null, $pricerange_id = null, $tenant_status = null);
    if ($processAssign != false) {
      foreach ($processAssign->assign as $val) {
        $legal->legalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
      }
    } else {
      $workFlowProcess = $general->workFlowProcess(801);
      $legal->legalUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
    }
    LegalCase::log($legal->id, 'lawyer_rejected', $reason, $userId);
  });
  clearNotification('Modules\Masters\Notifications\LegalNotification', $legal->id);

  $plmUsers = array_flatten(User::role(['backoffice_manager'])->get());
  $legal->href = url('plmsApproval/' . $legal->id);
  $legal->content = 'Rejected by lawyer';
  event(new LegalReferBack($legal, $plmUsers));
  LegalMessages::toUsers('legal_lawyer_rejected', $plmUsers, $legal->tenantContract, ['reason' => $reason]);

  session()->flash('success', 'Legal case rejected and sent back to PLM.');
  return redirect()->route('lawyerApproval');
}

/** Cases handed over to the landlord, the government or MERA. */
public function handedOverCases(Request $request)
{
  abort_unless(\Auth::user()->can('handed_over_cases_list'), 403);
  $cases = Legal::with(['tenantContract.tenant', 'building', 'unit'])
    ->where('close_reason', 'handed_over')
    ->when($request->handled_by, function ($q) use ($request) {
      $q->where('handled_by', (int) $request->handled_by);
    })
    ->when(trim((string) $request->search) !== '', function ($q) use ($request) {
      $term = '%' . trim($request->search) . '%';
      $q->where(function ($w) use ($term) {
        $w->where('handed_to_name', 'ilike', $term)->orWhere('handover_ref', 'ilike', $term)
          ->orWhereHas('tenantContract', function ($c) use ($term) {
            $c->where('tenant_contract_no', 'ilike', $term)
              ->orWhereHas('tenant', function ($t) use ($term) { $t->where('tenant_name', 'ilike', $term); });
          })
          ->orWhereHas('building', function ($b) use ($term) { $b->where('building_name', 'ilike', $term); });
      });
    })
    ->orderBy('handover_date', 'desc')->orderBy('id', 'desc')
    ->paginate($this->noOfRecord)->appends($request->only('handled_by', 'search'));

  return view('masters::Legal.handed_over_cases', compact('cases', 'request'));
}

public function lawyerApproval(Request $request){


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
//$user_id =  \Auth::user()->id;
//dd($user_id);
$roles = $user->getRoles();
//dd($roles);
$rolesNames = $user->getRoleNames()->toArray();
//dd($rolesNames);

$request->flash();
$status = 803;
//print_r($user_id);exit; 
$user_id =  \Auth::user()->id;

if(isset($request->md)){
  $roles=array(1);
  $user_id = 1;
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
}


// $plmsLegalStatus = DB::table('legal as l')->select('l.tenant_contract_id')
//             ->join('tenant_contracts as tc', function($join) {
//                             $join->on('tc.id', '=', 'l.tenant_contract_id')
//                             ->where('status',5);
//                         })
//             ->where('l.work_flow_processes_code', '=', $status);
$tenantStatus = 5;
$lawyerApprovalsBase = Legal::filter($request)
                   ->where('legal_is_closed','!=',2)
                      ->whereHas('tenantContract', function ($query) use($tenantStatus){             
                         $query->where('status','=',5);
                      })
                      ->whereNull('legal.closed_at')
                      ->when($user->hasRole('legal_advisor') && !$user->hasRole(['super_admin', 'md', 'backoffice_manager']), function ($query) use ($user_id) {
                         // a lawyer sees the cases PLM assigned to them (and old cases with no lawyer)
                         $query->where(function ($q) use ($user_id) {
                           $q->whereNull('legal.lawyer_user_id')->orWhere('legal.lawyer_user_id', $user_id);
                         });
                      })
                      ->where('legal.work_flow_processes_code', '=', 802);
$lawyerApprovals = Legal::newestMovedFirst(Legal::latestPerGroup($lawyerApprovalsBase)->sortable())->paginate($this->noOfRecord);
Legal::attachGroupSiblings($lawyerApprovals, $lawyerApprovalsBase);
            
//print_r($plmsLegalStatus);exit();

//print_r(\Auth::user()->id);exit;
$lawyerApprovalss = Legal::filter($request)
						->where('legal_is_closed','!=',2)
                        ->whereHas('legalUsers', function ($query)use($roles,$user,$user_id) {                          
                           $query->when(!($user->hasRole('super_admin','md')) ,  function($query)use($roles,$user_id){
                             $query->where(function ($query) use($roles){
                                $query->where('user_id',null)
                                      ->whereIn('role_id', $roles)
									                    ->where('status','=',1);
                              })
                            ->orWhere(function ($query) use($roles,$user_id){
                                $query->where('user_id','>',0)
                                      ->whereIn('role_id', $roles)
                                      ->where('user_id','=', $user_id)
									  ->where('status','=',5);
                              });
                          })
                          ->where('status','=',1);
                          })                  
                        ->where('legal.work_flow_processes_code', '=', $status)
                        ->sortable()
                        ->paginate($this->noOfRecord);
/*
if (in_array('super_admin', $rolesNames) === false) {
  $lawyerApproval->whereHas('legalUsers', function ($query) use($roles) {
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
$lawyerApprovals = $lawyerApproval->paginate(10);*/
//dd($lawyerApprovals);

$quick_url = $route = route('lawyerApproval');

if(isset($request->ajax))
  return view('masters::Legal.lawyer_approval_list_ajax',compact('lawyerApprovals','request','route'));

return view('masters::Legal.lawyer_approval_list',compact('lawyerApprovals','request','enquiry_fields','operations','name','quick_url'));

}





public function lawyerSearch(Request $request,$result = array()){
 $name = Route::currentRouteName();
 $enquiry_fields = [
 'tenant_contract_no' => 'Agreement No',
 'tenant_name' => 'Name',
 'building_name' => 'Building',
 'unit_code' => 'Unit',
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

  $lawyerApproval =   new LegalController;       
  $result =     $lawyerApproval->legalCaseSearch($request); 
  $request->flash(); 
}
$lawyerApproval = Legal::closure($result)->whereHas('legalUsers', function ($query) {
 $query->where('status','=',1);
})                  
->where('work_flow_processes_code', '=', 802);

if (in_array('super_admin', $rolesNames) === false) {
  $lawyerApproval->whereHas('legalUsers', function ($query) use($roles) {
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
$lawyerApprovals = $lawyerApproval->paginate(10);
if(isset($request->route))
  $route   =  $request->route;

if(isset($request->ajax))
  return view('masters::Legal.lawyer_approval_list_ajax',compact('lawyerApprovals','request','route'));

return view('masters::Legal.lawyer_approval_list',compact('lawyerApprovals','request','enquiry_fields','operations','name'));

}
public function lawyerApprovalShow($id){
  clearNotification('Modules\Masters\Notifications\LegalNotification',$id);
  readNotification('Modules\Masters\Notifications\LegalNotification',$id); 
  $lawyerApproval=Legal::where('id',$id)->first();
  $tenant_contract_id=$lawyerApproval->tenant_contract_id;
  $notes=Legal::where('tenant_contract_id',$tenant_contract_id)->get();

  $currentContract=TenantContract::where('id',$tenant_contract_id)->first();
  $rent = $currentContract->tenant_contract_rent;
  $oldContracts=$this->oldContracts($currentContract->tenant_contract_no);
  $oldContractList = TenantContract::whereIn('id',$oldContracts)->get();
  $nodays = 0;
 if(!empty($oldContracts)){
  foreach ($oldContractList as $oldContractLists) {
   $fromDate =strtotime($oldContractLists->tenant_contract_start_date);
   $toDate = strtotime($oldContractLists->tenant_contract_valid_to_date);
   $datediff = $toDate - $fromDate; 
   $days = intval($datediff/86400);
   $nodays+= $days;
 }
 $currentFrom=strtotime($currentContract->tenant_contract_start_date);
 $currentTo=strtotime("now");
 $currentdatediff = $currentTo - $currentFrom; 
 $currentdays = intval($currentdatediff/86400);
 $rentedFrom = $nodays + $currentdays;
 $dmy=$this->dateMonthYear($rentedFrom);
}else{
 $currentFrom=strtotime($currentContract->tenant_contract_start_date);
  //dd($currentFrom);
 $currentTo=strtotime("now");
 $currentdatediff = $currentTo - $currentFrom; 
 $currentdays = intval($currentdatediff/86400);
 $rentedFrom = $currentdays;
 $dmy=$this->dateMonthYear($rentedFrom);
}

  // what the contract owes from receipts and termination dues; the old 'months since last paid x rent'
  // estimate ignored receipts and terminations (user, 18/10/2026)
  $owed = LegalCase::owed($lawyerApproval);
  $timeline = LegalCase::timeline($lawyerApproval->id);
  return view('masters::Legal.lawyer_approval_view',compact('lawyerApproval','notes','oldContractList','dmy','owed','timeline'));

}
public function activeCases(Request $request){

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
 
  $user  = \Auth::user();
  $roles = $user->getRoles();

  $rolesNames = $user->getRoleNames()->toArray();

  $request->flash();  

  $activeCasesBase = $this->activeCasesQuery($request);
  $activeCases = Legal::newestMovedFirst(Legal::latestPerGroup($activeCasesBase)->sortable())->paginate($this->noOfRecord);
  Legal::attachGroupSiblings($activeCases, $activeCasesBase);

  $quick_url =    $route   =  route('activeCases');

  if(isset($request->ajax))
    return view('masters::Legal.active_cases_list_ajax',compact('activeCases','request','route'));

  $lcStages = DB::table('legal_stages')->where('is_final', false)->orderBy('sort_order')->get();
  $lcLawyers = LegalCase::lawyers();
  // bulk actions: Assign lawyer (PLM approvers, cases with no lawyer) and Close (legal_case_close);
  // "select all" covers every case matching the filter bar, across all pages
  $lgBulk = ['assign' => $user->can('plms_approve'), 'close' => $user->can('legal_case_close'), 'handover' => $user->can('plms_approve')];
  $bulkCases = [];   // id => [u: 1 when no lawyer yet, h: handled by the building suggests (1 = Al Habib)]
  if ($lgBulk['assign'] || $lgBulk['close']) {
    $rows = (clone $activeCasesBase)->get(['legal.id', 'legal.lawyer_user_id', 'legal.building_id']);
    $ownership = DB::table('buildings')->whereIn('id', $rows->pluck('building_id')->unique()->all())->pluck('building_ownership_type', 'id');
    foreach ($rows as $row) {
      $bulkCases[(int) $row->id] = ['u' => $row->lawyer_user_id ? 0 : 1,
        'h' => LegalCase::handledByForOwnership(isset($ownership[$row->building_id]) ? $ownership[$row->building_id] : null)];
    }
  }
  return view('masters::Legal.active_cases_list',compact('activeCases','request','enquiry_fields','operations','name','quick_url','lcStages','lcLawyers','lgBulk','bulkCases'));

}

/** Active cases (accepted by the lawyer, open) with the list's filter bar applied. */
private function activeCasesQuery(Request $request)
{
  return Legal::filter($request)
  ->where('legal_is_closed',2)
  ->noteStatus($request)
  ->exceptEndStatus($request)
                      ->legalUsers()
                      ->where('legal.work_flow_processes_code', '=', 803)
                      ->whereNull('legal.closed_at')
                      ->when($request->lc_priority, function ($q) use ($request) { $q->where('legal.priority', (int) $request->lc_priority); })
                      ->when($request->lc_type, function ($q) use ($request) { $q->where('legal.case_type', (int) $request->lc_type); })
                      ->when($request->lc_stage, function ($q) use ($request) {
                        $request->lc_stage === 'none' ? $q->whereNull('legal.stage_id') : $q->where('legal.stage_id', (int) $request->lc_stage);
                      })
                      ->when($request->lc_lawyer, function ($q) use ($request) {
                        $request->lc_lawyer === 'none' ? $q->whereNull('legal.lawyer_user_id') : $q->where('legal.lawyer_user_id', (int) $request->lc_lawyer);
                      })
                      // building ownership (MERA / Government / Family / Individual), e.g. to hand over MERA cases in bulk
                      ->when($request->lc_ownership, function ($q) use ($request) {
                        $q->whereIn('legal.building_id', function ($b) use ($request) {
                          $b->select('id')->from('buildings')->where('building_ownership_type', (int) $request->lc_ownership);
                        });
                      })
                      ->when($request->lc_hearing, function ($q) {
                        $q->whereBetween('legal.next_hearing_date', [date('Y-m-d'), date('Y-m-d', strtotime('+7 days'))]);
                      });
}

/**
 * Bulk assign a lawyer to active cases that have none (user, 20/10/2026). PLM approvers only;
 * a case that already has a lawyer, or is no longer active, is skipped. Each case gets a timeline
 * entry; no message is sent to the lawyer.
 */
public function bulkAssignLawyer(Request $request)
{
  $user = \Auth::user();
  abort_unless($user->can('plms_approve'), 403);
  $lawyers = LegalCase::lawyers();
  $this->validate($request, [
    'lawyer_user_id' => 'required|in:' . (implode(',', array_keys($lawyers)) ?: '0'),
    'ids'            => 'required|array|max:5000',
    'ids.*'          => 'integer',
  ], ['lawyer_user_id.in' => 'Pick an active lawyer.', 'ids.required' => 'Select at least one case.']);

  $lawyerId = (int) $request->lawyer_user_id;
  $ids = array_values(array_unique(array_map('intval', $request->ids)));
  $assigned = DB::transaction(function () use ($ids, $lawyerId, $lawyers, $user) {
    $cases = Legal::whereIn('id', $ids)->where('legal_is_closed', 2)->where('work_flow_processes_code', 803)
      ->whereNull('closed_at')->whereNull('lawyer_user_id')->lockForUpdate()->pluck('id');
    if ($cases->isEmpty()) {
      return 0;
    }
    Legal::whereIn('id', $cases)->update(['lawyer_user_id' => $lawyerId]);
    foreach ($cases as $id) {
      LegalCase::log($id, 'lawyer_assigned', 'Lawyer assigned: ' . $lawyers[$lawyerId] . ' (bulk, Active cases)', $user->id);
    }
    return $cases->count();
  });

  $skipped = count($ids) - $assigned;
  $msg = $assigned . ' case' . ($assigned == 1 ? '' : 's') . ' assigned to ' . $lawyers[$lawyerId] . '.';
  if ($skipped > 0) {
    $msg .= ' ' . $skipped . ' skipped (already had a lawyer or no longer active).';
  }
  return redirect()->back()->with($assigned ? 'success' : 'error', $msg);
}

public function casesSearch(Request $request,$result = array()){
  $name = Route::currentRouteName();
  $enquiry_fields = [
  'tenant_contract_no' => 'Agreement No',
  'tenant_name' => 'Name',
  'building_name' => 'Building',
  'unit_code' => 'Unit',
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

    $activeCase =   new LegalController;       
    $result =     $activeCase->legalCaseSearch($request); 
    $request->flash(); 
  }
  $activeCase = Legal::closure($result)->whereHas('legalUsers', function ($query) {
   $query->where('status','=',1);
 })                  
  ->where('work_flow_processes_code', '=', 803);

  if (in_array('super_admin', $rolesNames) === false) {
    $activeCase->whereHas('legalUsers', function ($query) use($roles) {
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
  $activeCases = $activeCase->paginate(10);
  //dd($activeCases);
  if(isset($request->route))
    $route   =  $request->route;

  if(isset($request->ajax))
    return view('masters::Legal.active_cases_list_ajax',compact('activeCases','request','route'));

  return view('masters::Legal.active_cases_list',compact('activeCases','request','enquiry_fields','operations','name'));

}
public function activeCasesShow($id){
 $activeCase=Legal::where('id',$id)->first();
 $tenant_contract_id=$activeCase->tenant_contract_id;
 $notes=Legal::where('tenant_contract_id',$tenant_contract_id)->get();
 $legalNotes=LegalNotes::where('legal_id',$id)->orderBy('legal_notes_status','ASC')->get();

 $currentContract=TenantContract::where('id',$tenant_contract_id)->first();
 $rent = $currentContract->tenant_contract_rent; 
 //dd($rent);
 $oldContracts=$this->oldContracts($currentContract->tenant_contract_no);

 $oldContractList = TenantContract::whereIn('id',$oldContracts)->get(); 
  $nodays = 0;
 if(!empty($oldContracts)){
  foreach ($oldContractList as $oldContractLists) {
   $fromDate =strtotime($oldContractLists->tenant_contract_start_date);
   $toDate = strtotime($oldContractLists->tenant_contract_valid_to_date);
   $datediff = $toDate - $fromDate; 
   $days = intval($datediff/86400);
   $nodays+= $days;
 }
 $currentFrom=strtotime($currentContract->tenant_contract_start_date);
 $currentTo=strtotime("now");
 $currentdatediff = $currentTo - $currentFrom; 
 $currentdays = intval($currentdatediff/86400);
 $rentedFrom = $nodays + $currentdays;
 $dmy=$this->dateMonthYear($rentedFrom);
}else{
 $currentFrom=strtotime($currentContract->tenant_contract_start_date);
 $currentTo=strtotime("now");
 $currentdatediff = $currentTo - $currentFrom; 
 $currentdays = intval($currentdatediff/86400);
 $rentedFrom = $currentdays;
 $dmy=$this->dateMonthYear($rentedFrom);
}

  // what the contract owes from receipts and termination dues; the old 'months since last paid x rent'
  // estimate ignored receipts and terminations (user, 18/10/2026)
  $owed = LegalCase::owed($activeCase);
return view('masters::Legal.active_cases_view',compact('activeCase','notes','legalNotes','oldContractList','dmy','owed'));
}
public function dateMonthYear($rentedFrom){
    $years = floor($rentedFrom / 365);
    $months = floor(($rentedFrom - ($years * 365))/30);
    $days = ($rentedFrom - ($years * 365) - ($months * 30));
    $total = $years.' Years - '.$months.' Months - '.$days.' Days';
    return $total;
}
public  function oldContracts($tenant_contract_no){

  $currentContract=TenantContract::where('tenant_contract_no',$tenant_contract_no)->first();
  $oldContractId =0;	
  if(!empty($currentContract->tenant_contract_old_no))
  {
    $oldContractinfo=TenantContract::where('tenant_contract_no',$currentContract->tenant_contract_old_no)->first();
    if(isset($oldContractinfo->id)){
			
			$oldContractId =$oldContractinfo->id;
	}
	
    array_push($this->datas,$oldContractId);
    
    $oldContract=TenantContract::where('id',$oldContractId)->first();
    if(!empty($oldContract->tenant_contract_old_no))
    {
      $oldContracts = $this->oldContracts($oldContract->tenant_contract_no);
    } 
    return $this->datas;
  }else{
  
    return $this->datas;
  }
}
/**
 * Close form for a case not yet accepted by the lawyer (801 / 802) or drop of a case referred back to ARE (804).
 * Loaded into #myModal; the save needs a reason (user, 18/10/2026).
 */
public function legalCloseForm(Request $request){
  $legal = Legal::find($request->legal_id);
  $error = $this->closeError($legal);
  if ($error) {
    return view('masters::Legal._modal_message', ['title' => 'Cannot close', 'message' => $error]);
  }
  $otherOpen = Legal::where('tenant_contract_id', $legal->tenant_contract_id)->where('id', '<>', $legal->id)->whereNull('closed_at')->first();
  return view('masters::Legal._close_form', compact('legal', 'otherOpen'));
}

/** Why this user cannot close / drop the case here, or null. Accepted (803) cases close on the case page. */
private function closeError($legal)
{
  $user = \Auth::user();
  if (!$legal) {
    return 'The legal case was not found.';
  }
  if ($legal->closed_at) {
    return 'This legal case is already closed.';
  }
  $code = (int) $legal->work_flow_processes_code;
  if ($code === 803) {
    return 'The lawyer accepted this case; close it from the case page (Close case).';
  }
  if ($code === 801 && !$user->can('plms_close')) {
    return 'You are not allowed to close this case.';
  }
  if ($code === 802 && !($user->can('plms_close') || ($user->can('lawyer_close') && $this->mayActForLawyer($legal, $user)))) {
    return 'You are not allowed to close this case.';
  }
  if ($code === 804 && !($user->can('legal_are_stage') || $user->can('plms_close'))) {
    return 'You are not allowed to drop this case.';
  }
  if (!in_array($code, [801, 802, 804], true)) {
    return 'This case cannot be closed here.';
  }
  return null;
}

public function legalCaseClose(Request $request){
  $legal = Legal::where('id', $request->legal_id)->first();
  $error = $this->closeError($legal);
  if ($error) {
    session()->flash('error', $error);
    return redirect()->back();
  }
  $this->validate($request, ['legal_note' => 'required|max:1000'], ['legal_note.required' => 'Give the reason for closing.']);
  $code = (int) $legal->work_flow_processes_code;
  $userId = \Auth::user()->id;
  $note = $request->legal_note;
  if ($code === 802 && $legal->lawyer_user_id && (int) $legal->lawyer_user_id !== (int) $userId) {
    $note .= ' (by ' . $this->personName($userId) . ' for the lawyer)';
  }

  DB::transaction(function () use ($legal, $code, $note, $userId) {
    $legal->legalUser()->update(['status' => 0]);
    LegalCase::close($legal, $code === 804 ? 'dropped' : 'withdrawn', $note, $userId);
    $legal->update(['legal_is_closed' => 1]);
    // the contract leaves legal only when no other case of it is still open
    if (!Legal::where('tenant_contract_id', $legal->tenant_contract_id)->where('id', '<>', $legal->id)->whereNull('closed_at')->exists()) {
      TenantContract::where('id', $legal->tenant_contract_id)->update(['status' => 0]);
    }
  });
  clearNotification('Modules\Masters\Notifications\LegalNotification', $legal->id);

  if ($code === 802) {
    // Back office and ARE (system template legal_advisor_closed)
    LegalMessages::toUsers('legal_advisor_closed', array_flatten(User::role(['are', 'backoffice_manager'])->get()), $legal->tenantContract);
    session()->flash('success', 'Legal case closed.');
    return redirect()->route('lawyerApproval');
  }
  // ARE (system template legal_case_closed); a dropped case also tells PLM
  LegalMessages::toRoles('legal_case_closed', $code === 804 ? ['are', 'backoffice_manager'] : ['are'], $legal->tenantContract);
  session()->flash('success', $code === 804 ? 'Legal case dropped.' : 'Legal case closed.');
  return redirect()->route($code === 804 ? 'legalCase.index' : 'plmsApproval');
}

/** Why ARE cannot send this case again, or null: it must still be referred back (804) and the only open case of the contract. */
private function resendError($legal)
{
  if (!$legal) {
    return 'The legal case was not found.';
  }
  if ($legal->closed_at || (int) $legal->work_flow_processes_code !== 804) {
    return 'This case is no longer referred back to ARE. Refresh the page.';
  }
  if (!\Auth::user()->can('legal_are_stage')) {
    return 'You are not allowed to send this case.';
  }
  $other = Legal::where('tenant_contract_id', $legal->tenant_contract_id)->where('id', '<>', $legal->id)->whereNull('closed_at')->first();
  if ($other) {
    return 'The contract already has open case #' . $other->id . '. Drop this one instead of sending it again.';
  }
  return null;
}

public function legalAreStage(Request $request){
  $legal = Legal::find($request->legal_id);
  $error = $this->resendError($legal);
  if ($error) {
    return view('masters::Legal._modal_message', ['title' => 'Cannot send', 'message' => $error]);
  }
  $legal_id = $legal->id;
  $process_flow = 801;
  $are_status = 0;
  return view('masters::Legal.are_stage_model',compact('legal_id','process_flow','are_status'));
}

/**
 * ARE sends a referred-back case again: the SAME case goes back to PLM approval (801). It used to create a new
 * case row and leave this one open, so a case could be sent again and again, and some went straight to the
 * lawyer (user, 18/10/2026).
 */
public function areStageStore(Request $request)
{
  $legal = Legal::where('id', $request->legal_id)->first();
  $error = $this->resendError($legal);
  if ($error) {
    session()->flash('error', $error);
    return redirect()->back();
  }
  $this->validate($request, ['are_note' => 'required|max:200']);
  $userId = \Auth::user()->id;
  $general = new General;

  DB::transaction(function () use ($legal, $request, $userId, $general) {
    $legal->legalUser()->update(['status' => 0]);
    $legal->update(['work_flow_processes_code' => 801, 'are_status' => 0, 'note' => $request->are_note]);
    $assign = $general->roleUsersFromProcess(801, $location_id = null, $pricerange_id = null, $tenant_status = null);
    if ($assign != false) {
      foreach ($assign->assign as $val) {
        $legal->legalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
      }
    } else {
      $workFlowProcess = $general->workFlowProcess(801);
      $legal->legalUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
    }
    $legal->legalUsers()->attach(\Auth::user()->default_role, ['user_id' => $userId]);
    LegalCase::log($legal->id, 'are_resent', $request->are_note, $userId);
  });
  clearNotification('Modules\Masters\Notifications\LegalNotification', $legal->id);

  $legal->href = url('plmsApproval/' . $legal->id);
  event(new LegalApprove($legal, array_flatten(User::role(['backoffice_manager'])->get())));
  session()->flash('success', 'Legal case sent to PLM approval again.');
  return redirect()->route('legalCase.index');
}
public function legalNoteStore(Request $request){

  $url = $request['current_url'];
  // closing now goes through the case page (verified settlement or a withdrawal reason)
  if ((string) $request->legal_notes_status === '0') {
    session()->flash('error', 'To close the case use Close case on the case page: it needs a verified settlement or a withdrawal reason.');
    return redirect()->route('legal.case', $request->legal_id);
  }
  LegalNotes::create([
    'user_id' => \Auth::user()->id,
    'legal_id' =>$request->legal_id,
    'legal_notes_status' =>$request->legal_notes_status,
    'legal_notes_note' =>$request->legal_notes_note,
    'created_by' =>\Auth::user()->id,
    ]);
  if($request->legal_notes_status == 0)
  {
    Legal::where('id',$request->legal_id)->update(['legal_is_closed'=>1]);
  }
  $otherUsers = User::role(['are','backoffice_manager'])->get(); 
  $otherUsers = array_flatten($otherUsers);
  $legal      = Legal::where('id',$request->legal_id)->first();

  // Back office and ARE (system template legal_status_note)
  LegalMessages::toUsers('legal_status_note', $otherUsers, $legal->tenantContract, ['status_note' => $request->legal_notes_status_name]);

   
  session()->flash('success', 'Status Note added Successfully');
  return redirect($url);
}
public function legalDocumentStore(Request $request){
  $url = $request['current_url'];
  $documents = $request->file('legal_documents_file_name');
 // dd($documents);
  if(!empty($documents)) {
    foreach($documents as $document){
      $uniqueFileName = $document->getClientOriginalName();
      $img_path =    Storage::putFile('public/LegalDocs', $document);
      $legalDocuments =  LegalDocuments::create([
        'legal_documents_name' =>  $uniqueFileName,
        'legal_documents_file_name' => $img_path,
        'legal_id'=>$request->legal_id,
        'created_by' => \Auth::user()->id,
        ]);
    }
  }
  session()->flash('success', 'Legal Document Uploaded Successfully');
  return redirect($url);

}
public function legalPdcShow($id){
  $pdc=Pdc::where('tenant_contract_id',$id)->get();
//dd($pdc);
  return view('masters::Legal.pdc_view',compact('pdc'));
}
public function legaltenantContractShow($id){
  $tenantContract = TenantContract::where('id',$id)->first();
  $contract       = TenantContract::where('unit_id',$tenantContract->unit_id)->where('work_flow_processes_code',108)->where('tenant_contract_status',0)->orderBy('id', 'desc')->first();

  if(isset($contract->tenant_contract_valid_to_date) || isset($contract->tenant_contract_rent))     {
    $vaccant_date               =   date('Y-m-d', strtotime($contract->tenant_contract_valid_to_date.' +1 day'));
    $last_rent                  =   $contract->tenant_contract_rent;
    $tenantContract->vaccant_date   =   $vaccant_date;
    $tenantContract->last_rent      =   $last_rent;
  }
  //dd($tenantContract);
  return view('masters::Legal.legal_tenant_contract_view',compact('tenantContract'));
}
public function referBackCases(Request $request){


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
 
  $user  = \Auth::user();
  $roles = $user->getRoles();
  $rolesNames = $user->getRoleNames()->toArray();

  $request->flash();  

  // open referred-back cases only, of the ARE's own buildings (Legal Cases list rule)
  $closedLegalCasesBase = Legal::areLegalBuilding()->filter($request)
                      ->whereNull('legal.closed_at')
                      ->where('legal.work_flow_processes_code', '=', 804);
  $closedLegalCases = Legal::newestMovedFirst(Legal::latestPerGroup($closedLegalCasesBase)->sortable())->paginate($this->noOfRecord);
  Legal::attachGroupSiblings($closedLegalCases, $closedLegalCasesBase);
  $quick_url =    $route   =  route('referBackCases');

  if(isset($request->ajax))
    return view('masters::Legal.referback_cases_list_ajax',compact('closedLegalCases','request','route'));

  return view('masters::Legal.referback_cases_list',compact('closedLegalCases','request','enquiry_fields','operations','name','quick_url'));

}
public function closedLegalCases(Request $request){


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
 
  $user  = \Auth::user();
  $roles = $user->getRoles();
  $rolesNames = $user->getRoleNames()->toArray();

  $request->flash();  

  // every case closed on our side, whatever step it was at (cases closed before the lawyer accepted used to
  // show nowhere); handed-over cases have their own list (user, 18/10/2026)
  $closedLegalCasesBase = Legal::filter($request)
  ->whereNotNull('legal.closed_at')
  ->where(function ($q) { $q->whereNull('legal.close_reason')->orWhere('legal.close_reason', '<>', 'handed_over'); })
                      // ->whereHas('legalUsers', function ($query) use($roles,$user) {
                      //    $query->when( !($user->hasRole('super_admin')), function($query) use($roles){
                      //         $query->where(function ($query) use($roles){
                      //             $query->where('user_id',null)
                      //             ->whereIn('role_id', $roles);
                      //           })
                      //           ->orWhere(function ($query) use($roles){
                      //             $query->where('user_id','>',0)
                      //             ->whereIn('role_id', $roles)
                      //             ->where('user_id','=', \Auth::user()->id);
                      //           });      
                      //     })
                      //     ->where('status','=',1);
                      //    })                  
                      ;
  $closedLegalCases = Legal::newestMovedFirst(Legal::latestPerGroup($closedLegalCasesBase)->sortable())->paginate($this->noOfRecord);
  Legal::attachGroupSiblings($closedLegalCases, $closedLegalCasesBase);
  $quick_url =    $route   =  route('closedLegalCases');

  if(isset($request->ajax))
    return view('masters::Legal.closed_cases_list_ajax',compact('closedLegalCases','request','route'));

  return view('masters::Legal.closed_cases_list',compact('closedLegalCases','request','enquiry_fields','operations','name','quick_url'));

}
public function closedLegalCasesShow($id){
    $closedLegalCase=Legal::where('id',$id)->first();
    $tenant_contract_id=$closedLegalCase->tenant_contract_id;
    $notes=Legal::where('tenant_contract_id',$tenant_contract_id)->get();
    $legalNotes=LegalNotes::where('legal_id',$id)->get();
    //dd($legalNotes);


    $currentContract=TenantContract::where('id',$tenant_contract_id)->first();
    $rent = $currentContract->tenant_contract_rent;
    //dd($rent);
    $oldContracts=$this->oldContracts($currentContract->tenant_contract_no);

    $oldContractList = TenantContract::whereIn('id',$oldContracts)->get();
    $nodays = 0;
    if(!empty($oldContracts)){
    foreach ($oldContractList as $oldContractLists) {
    $fromDate =strtotime($oldContractLists->tenant_contract_start_date);
    $toDate = strtotime($oldContractLists->tenant_contract_valid_to_date);
    $datediff = $toDate - $fromDate;
    $days = intval($datediff/86400);
    $nodays+= $days;
    }
    $currentFrom=strtotime($currentContract->tenant_contract_start_date);
    $currentTo=strtotime("now");
    $currentdatediff = $currentTo - $currentFrom;
    $currentdays = intval($currentdatediff/86400);
    $rentedFrom = $nodays + $currentdays;
    $dmy=$this->dateMonthYear($rentedFrom);
    }else{
    $currentFrom=strtotime($currentContract->tenant_contract_start_date);
    $currentTo=strtotime("now");
    $currentdatediff = $currentTo - $currentFrom;
    $currentdays = intval($currentdatediff/86400);
    $rentedFrom = $currentdays;
    $dmy=$this->dateMonthYear($rentedFrom);
    }

  // what the contract owes from receipts and termination dues; the old 'months since last paid x rent'
  // estimate ignored receipts and terminations (user, 18/10/2026)
  $owed = LegalCase::owed($closedLegalCase);
    return view('masters::Legal.closed_cases_view',compact('closedLegalCase','notes','legalNotes','oldContractList','dmy','owed'));
    }
    public function referBackCasesShow($id){
    $closedLegalCase=Legal::where('id',$id)->first();
    $tenant_contract_id=$closedLegalCase->tenant_contract_id;
    $notes=Legal::where('tenant_contract_id',$tenant_contract_id)->get();
    $legalNotes=LegalNotes::where('legal_id',$id)->get();
    //dd($legalNotes);


    $currentContract=TenantContract::where('id',$tenant_contract_id)->first();
    $rent = $currentContract->tenant_contract_rent;
    //dd($rent);
    $oldContracts=$this->oldContracts($currentContract->tenant_contract_no);

    $oldContractList = TenantContract::whereIn('id',$oldContracts)->get();
    $nodays = 0;
    if(!empty($oldContracts)){
    foreach ($oldContractList as $oldContractLists) {
    $fromDate =strtotime($oldContractLists->tenant_contract_start_date);
    $toDate = strtotime($oldContractLists->tenant_contract_valid_to_date);
    $datediff = $toDate - $fromDate;
    $days = intval($datediff/86400);
    $nodays+= $days;
    }
    $currentFrom=strtotime($currentContract->tenant_contract_start_date);
    $currentTo=strtotime("now");
    $currentdatediff = $currentTo - $currentFrom;
    $currentdays = intval($currentdatediff/86400);
    $rentedFrom = $nodays + $currentdays;
    $dmy=$this->dateMonthYear($rentedFrom);
    }else{
    $currentFrom=strtotime($currentContract->tenant_contract_start_date);
    $currentTo=strtotime("now");
    $currentdatediff = $currentTo - $currentFrom;
    $currentdays = intval($currentdatediff/86400);
    $rentedFrom = $currentdays;
    $dmy=$this->dateMonthYear($rentedFrom);
    }

  // what the contract owes from receipts and termination dues; the old 'months since last paid x rent'
  // estimate ignored receipts and terminations (user, 18/10/2026)
  $owed = LegalCase::owed($closedLegalCase);
    return view('masters::Legal.referback_cases_view',compact('closedLegalCase','notes','legalNotes','oldContractList','dmy','owed'));
    }
}

