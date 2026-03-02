<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Entities\Invoice;
use Modules\BackOffice\Entities\Pdc;
use Modules\Sales\Entities\LandlordContract;
use Modules\Sales\Entities\TenantContract;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Vendor;
use Modules\BackOffice\Entities\ViewLandlordTermination;
use Session;
use Route;
use DB;
use App\User;
use Modules\General\Http\Controllers\GeneralController as General;
use Modules\BackOffice\Http\Controllers\LandlordRenewalController as SearchController ;
use Modules\BackOffice\Events\TenantTermination;
use Exception;


class LandlordTerminationController extends Controller
{
    public function __construct()
  {
      $this->middleware('auth');  
      $this->middleware('permission:landlord_open_termination_list', ['only' => ['index']]);
      $this->middleware('permission:lc_termination_verification_list', ['only' => ['LCTerminationVerify','LCTerminationVerifyView']]);
      $this->middleware('permission:landlord_early_termination', ['only' => ['edit','update','store','create']]); 
      $this->middleware('permission:lc_approval_termination_list', ['only' => ['LCTerminationApproval','LCTerminationApprovalView']]);
      $this->middleware('permission:landlord_terminated_contract_list', ['only' => ['TerminatedContractLandlord','TerminatedContractLandlordView']]);

       $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
                      
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $name = Route::currentRouteName();
        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();

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
      
        $request->flash(); 

       
       
      $quick_url =   $route   =  route('landlordTermination.index');

 
    $landlordTerminations =    ViewLandlordTermination::
                                     filterUsers()
                                     ->filter($request)
                                     ->where('landlord_renewal_termination_status',7)
                                     ->where('landlord_contract_status',1)
                                     ->where('work_flow_processes_code', '=', 601)
                                     ->where('termination_type', '=', 2)
                                     ->sortable()
                                     ->paginate($this->noOfRecord);

 
                                     /*
  $landlordTerminations = Termination::whereHas('landlordContract', function ($query) use($rolesNames,$request){
                                $query->where('landlord_renewal_termination_status',7)
                                      ->where('landlord_contract_status',1)
                                      ->filter($request);               
                            })->whereHas('terminationUsers', function ($query) use($rolesNames,$roles) {
                                $query->when((in_array('super_admin', $rolesNames) === false),function($query)use($roles){
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
                            })
                          ->where('work_flow_processes_code', '=', 601)
                          ->where('termination_type', '=', 2)
                          ->sortable()->paginate($this->noOfRecord);

               */     



/*
      $landlordTermination = Termination::whereHas('landlordContract', function ($query) use($rolesNames,$result){
                                $query->where('landlord_renewal_termination_status',7)
                                ->where('landlord_contract_status',1)
                                ->closure($result)
                                ->sortable();
                                           
                            })->whereHas('terminationUsers', function ($query) {
                                $query->where('status','=',1);
                            })                             
                        ->where('work_flow_processes_code', '=', 601)
                        ->where('termination_type', '=', 2);

                    if (in_array('super_admin', $rolesNames) === false) {
                          $landlordTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
         $landlordTerminations = $landlordTermination->sortable()->paginate(10);*/
       if(isset($request->ajax))
       return view('backoffice::Termination.landlord_termination_open_list_ajax',compact('landlordTerminations','request','route'));

        return view('backoffice::Termination.landlord_termination_open_list',compact('landlordTerminations','enquiry_fields','operations','quick_url'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        //$landlordContract = LandlordContract::where('id',$contract)->first();
        return view('backoffice::Termination.add_landlord_termination_open');
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
          'end_date' => 'required',                    
      ]);
      $currentUrl =  Session::get('current');
      $general =  new General;
      $next_process_id = 601;
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);
      $contractId = $request->contract_id;
      $termination = new Termination; 
      $termination->contract_id     = $contractId;
      $termination->termination_type  = 2;
      $termination->termination_type_status  = 1;
      $termination->work_flow_processes_code = $next_process_id;
      $termination->created_by           = \Auth::user()->id;
      $termination->save();
      LandlordContract::where('id',$contractId)->update(['landlord_renewal_termination_status'=>7,'end_date'=>$request->end_date]);
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
          

        }else{

          $workFlowProcess = $general->workFlowProcess($next_process_id);
          $termination->terminationUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
          $termination->save();
                  
          
        }
      }
        $termination->terminationUsers()->attach(\Auth::user()->default_role,['user_id'=>\Auth::user()->id]);  
          $termination->save();
        
        session()->flash('success', 'Termination request Created Successfully');
        return redirect()->route('landlordTermination.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($terminationId)
    {
        $termination = Termination::with('landlordContract')->where('id',$terminationId)->first();

        clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
        readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
      return view('backoffice::Termination.landlord_termination_open_view',compact('termination'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($terminationId)
    {
        $landlordTermination = Termination::where('id',$terminationId)->first();
        return view('backoffice::Termination.add_landlord_termination_open',compact('landlordTermination'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Termination $landlordTermination)
    {
      $this->validate($request, [
          'contract_id' => 'required',                    
      ]);
      $oldContract = $landlordTermination->contract_id;
      LandlordContract::where('id',$oldContract)->update(['landlord_renewal_termination_status'=>0]);

      $contractId = $request->contract_id;
      $landlordTermination->update(['contract_id'=>$contractId]);

      LandlordContract::where('id',$contractId)->update(['landlord_renewal_termination_status'=>7]);

      if($request->end_date != null){
        LandlordContract::where('id',$contractId)->update(['end_date'=>$request->end_date]);
      }

      session()->flash('success', 'Termination request Updated Successfully');
      return redirect()->route('landlordTermination.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
    /**
    *
    * Agreement Autocomplete
    *
    **/
    public function agreementAutocomplete(Request $request)
    {

      $key = $request->term;
      $agreement =  LandlordContract::active()->whereNotIn('landlord_renewal_termination_status',[1,7])->where('landlord_contract_no', 'ILIKE', '%'.$key.'%')
        ->select('id AS ids',DB::raw("landlord_contract_no as value"))
     ->get();

      return $agreement ;

    }
    /**
    *
    * building Autocomplete
    *
    **/
    public function buildingAutocomplete(Request $request)
    {
      $contracts = LandlordContract::whereNotIn('landlord_renewal_termination_status',[1,7])->pluck('building_id');
      $key = $request->term;
      $building =  Building::active()->whereIn('id',$contracts)->where('building_name', 'ILIKE', '%'.$key.'%')
                    ->select('id AS ids',DB::raw("CONCAT(building_name,'-',building_code) as value"))
     ->get();

      return $building ;

    }
    /*
    *
    *
    *landlordVendorAutocomplete
    *
    */
    public function landlordVendorAutocomplete(Request $request)
    {
      $contracts = LandlordContract::whereNotIn('landlord_renewal_termination_status',[1,7])->pluck('vendor_id');
      $key = $request->term;
      $vendor =  Vendor::active()->whereIn('id',$contracts)->where('vendor_name', 'ILIKE', '%'.$key.'%')
                    ->select('id AS ids',DB::raw("CONCAT(vendor_name,'-',vendor_code) as value"))
     ->get();

      return $vendor ;

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
      $vendor_id =$request->vendor_id;
      $unit_id =$request->unit_id;
      $landlordContract = LandlordContract::when($building_id, function ($query, $building_id) {
                            return $query->where('building_id',$building_id)
                                  ->whereNotIn('landlord_renewal_termination_status',[1,7]);
                          })->when($contractNo, function ($query, $contractNo) {
                            return $query->where('id',$contractNo)
                                    ->whereNotIn('landlord_renewal_termination_status',[1,7]);
                          })->when($vendor_id, function ($query, $vendor_id) {
                            return $query->where('vendor_id',$vendor_id)
                                    ->whereNotIn('landlord_renewal_termination_status',[1,7]);
                          })->active()->first();

      if($building_id != "" && $contractNo == "" && $vendor_id == ""){
        $landlordContract = LandlordContract::where('building_id',$building_id)->active()->first();
      }elseif($building_id == "" && $contractNo != "" && $vendor_id == ""){
        $landlordContract = LandlordContract::where('id',$contractNo)->first();
      }else{
        $landlordContract = LandlordContract::where('vendor_id',$vendor_id)->first();
      }

      //print_r(json_encode($landlordContract));exit();
      //dd($landlordContract);
      return view('backoffice::Termination.early_landlord_termination_ajax',compact('landlordContract'));
    }
    /*
    *
    *  termination Stage
    *
    *
    */
    public function landlordTerminationStage($terminationId,$contractId,$stage,$action_key)
    {   

      $currentUrl =  Session::get('current');
      $general =  new General;
      $next_process_id = $general->nextProcessFromAction($stage,$action_key);
      $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);

      $previousFlow = Termination::where('work_flow_processes_code',$stage)->where('contract_id',$contractId)->where('termination_type',2)->orderBy('id','desc')->latest()->first();
        if($previousFlow)$previousFlow->terminationUser()->update(['status' => 0]);

        $previousProcess = Termination::where('work_flow_processes_code',$next_process_id)->where('contract_id',$contractId)->where('termination_type',2)->orderBy('id','desc')->latest()->first();//dd($previousFlow);
        if($previousProcess)$previousProcess->terminationUser()->update(['status' => 0]);
        
          
        $termination = new Termination; 
        $termination->contract_id     = $contractId;
        $termination->termination_type  = 2;
        $termination->work_flow_processes_code = $next_process_id;
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

        /* Notification */
          $process = $general->workFlowProcessNames($next_process_id);
                        
          clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);
          readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$termination->id);  
          $termination->text = "Tenant Termination - ".$process->work_flow_processes_name;

        session()->flash('success', 'Termination Successfully Moved to '.$process->work_flow_processes_name);
        return redirect()->route($currentUrl);

    }
    /*
    *
    * LCtermination Varify
    *
    *
    */
    public function LCTerminationVerify(Request $request)
    {
        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
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
        
      $request->flash(); 
        
      $quick_url =  $route   =  route('LCTerminationVerify');




 
    $landlordTerminations =    ViewLandlordTermination::
                                     filterUsers()
                                     ->filter($request)
                                     ->where('landlord_renewal_termination_status',7)
                                     ->where('landlord_contract_status',1)
                                     ->where('work_flow_processes_code', '=', 602)
                                     ->where('termination_type', '=', 2)
                                     ->whereIn('termination_type_status',[0,2,4])
                                     ->sortable()
                                     ->paginate($this->noOfRecord);
/*

      $landlordTerminations = Termination::whereHas('landlordContract', function ($query) use($rolesNames,$request){
                                $query->where('landlord_renewal_termination_status',7)
                                ->where('landlord_contract_status',1)
                                ->filter($request);                                
                                           
                            })->whereHas('terminationUsers', function ($query) use($rolesNames,$roles) {
                               $query->when( (in_array('super_admin', $rolesNames) === false), function ($query)use($roles){
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
                            })                             
                        ->where('work_flow_processes_code',602)
                        ->where('termination_type', '=', 2)
                        ->whereIn('termination_type_status',[0,2,4])
                        ->sortable()
                        ->paginate($this->noOfRecord);
  */           

/*

      $landlordTermination = Termination::whereHas('landlordContract', function ($query) use($rolesNames,$result){
                                $query->where('landlord_renewal_termination_status',7)
                                ->where('landlord_contract_status',1)
                                ->closure($result)
                                ->sortable();
                                           
                            })->whereHas('terminationUsers', function ($query) {
                                $query->where('status','=',1);
                            })                             
                        ->where('work_flow_processes_code',602)
                        ->where('termination_type', '=', 2)
                        ->whereIn('termination_type_status',[0,2,4]);

                    if (in_array('super_admin', $rolesNames) === false) {
                          $landlordTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
         $landlordTerminations = $landlordTermination->paginate(10);*/
       if(isset($request->ajax))
       return view('backoffice::Termination.landlord_termination_verification_list_ajax',compact('landlordTerminations','request','route'));

        return view('backoffice::Termination.landlord_termination_verification_list',compact('landlordTerminations','enquiry_fields','operations','quick_url'));

    }
    /*
    *
    *
    * tenantTerminationOpenStatus
    *
    */
    public function landlordTerminationChangeStatus($terminationId,$status)
    {
      
        if($status == 3) {
            $terminatedDetails = Termination::where('id',$terminationId)->first();
            $next_process_id = 603;
            $route = 'LCTerminationApproval';
            $msg = 'Termination Approved Successfully ';

            $previousFlow = Termination::where('work_flow_processes_code',$next_process_id)->where('contract_id',$terminatedDetails->contract_id)->where('termination_type',2)->orderBy('id','desc')->latest()->first();
            if($previousFlow)$previousFlow->terminationUser()->update(['status' => 0]);

            $general =  new General;
            $processAssign = $general->roleUsersFromProcess($next_process_id,$location_id=null,$pricerange_id=null,$tenant_status=null);
            $terminations = new Termination; 
            $terminations->contract_id     = $terminatedDetails->contract_id;
            $terminations->termination_type  = 2;
            $terminations->work_flow_processes_code = $next_process_id;
            $terminations->termination_type_status = $status;
            $terminations->created_by           = \Auth::user()->id;
            $terminations->save();
            

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
            // Terminated After Update Contract
              $contractBuilding = LandlordContract::where('id',$terminatedDetails->contract_id)->first();

              $tenantContractsId = TenantContract::where('building_id',$contractBuilding->building_id)->pluck('id');
              $contractUpdate = TenantContract::whereIn('id',$tenantContractsId)->update(['tenant_contract_status'=>0,'tenant_renewal_termination_status'=>8]);
              $buildingUpdate = Building::where('id',$contractBuilding->building_id)->update(['building_status'=>0]);
              $unitUpdate = Unit::where('building_id',$contractBuilding->building_id)->update(['unit_vaccant_status'=>0]);
              $landlordUpdate =  LandlordContract::where('id',$terminatedDetails->contract_id)->update(['landlord_contract_status'=>0,'landlord_renewal_termination_status'=>8]);  
              //Remaining pdc and invoices cancel    
              $today  = date('Y-m-d');
              $remainingPdc = Pdc::whereIn('tenant_contract_id',$tenantContractsId)->whereDate('pdc_check_date','>=',$today)->pluck('id');
              
              $pdcUpdate = Pdc::whereIn('id',$remainingPdc)->update(['pdc_cancel_date'=>$today,'pdc_cancel_reason'=>4,'pdc_cancel_by'=>\Auth::user()->id]);
              //Invoice
              $remainingInvoice = Invoice::whereIn('tenant_contract_id',$tenantContractsId)->whereDate('tenant_invoice_date','>=',$today)->pluck('id');
              $invoiceUpdate = Invoice::whereIn('id',$remainingInvoice)->update(['tenant_invoice_cancelled_date'=>$today,'tenant_invoice_status'=>2]);
              /** End **/
            /* Notification */
            $users = User::role(['backoffice_executive'])->get(); 
            $users = array_flatten($users); 

            clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminations->id);
            $terminations->href =  url('TerminatedContractLandlord/'.$terminations->id).'/TerminatedContractLandlordView';    
            $terminations->text = "Landlord Termination Approved"; 
            event(new TenantTermination($terminations,$users));
        }else{
            if($status == 2){
              $route = 'LCTerminationVerify';$msg = 'Send Approval Successfully ';
              /* Notification */
              $users = User::role(['backoffice_manager'])->get(); 
              $users = array_flatten($users);     
              $terminationDetails = Termination::where('id',$terminationId)->first();         
              clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationDetails->id);
              $terminationDetails->href =  url('LCTerminationApproval/'.$terminationId.'/LCTerminationApprovalView');    
              $terminationDetails->text = "Landlord Termination Approval Request"; 
              event(new TenantTermination($terminationDetails,$users));
            }
            if($status == 4){
              $route = 'LCTerminationApproval';$msg = 'Send Rejected Successfully ';
              /* Notification */
              $users = User::role(['backoffice_executive'])->get(); 
              $users = array_flatten($users);   
              $terminationDetails = Termination::where('id',$terminationId)->first();           
              clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationDetails->id);
              $terminationDetails->href =  url('landlordTermination/'.$terminationId);    
              $terminationDetails->text = "Landlord Termination Rejected"; 
              event(new TenantTermination($terminationDetails,$users));
            }
            
            $termination = Termination::where('id',$terminationId)->update(['termination_type_status'=>$status]);
        }
        
        session()->flash('success', $msg);
        return redirect()->route($route);
      
      
    }
    /*
    *
    * LCTerminationVerifyView
    *
    *
    */
    public function LCTerminationVerifyView($terminationId)
    {
        clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
        readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);  
        $termination = Termination::where('id',$terminationId)->first();
        $landlordContract = LandlordContract::where('id',$termination->contract_id)->first();
        $tenantContracts = TenantContract::where('building_id',$landlordContract->building_id)->get();
        return view('backoffice::Termination.landlord_termination_verification_view',compact('landlordContract','termination','tenantContracts'));
    }
    /*
    *
    * LCTermination Approval
    *
    *
    */
    public function LCTerminationApproval(Request $request)
    {
        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
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
               
        $request->flash();         
        $quick_url = $route   =  route('LCTerminationApproval');


$landlordTerminations =    ViewLandlordTermination::
                                     filterUsers()
                                     ->filter($request)
                                     ->where('landlord_renewal_termination_status',7)
                                     ->where('landlord_contract_status',1)
                                     ->where('work_flow_processes_code', '=', 602)
                                     ->where('termination_type', '=', 2)
                                     ->whereIn('termination_type_status', [2,3])
                                     ->sortable()
                                     ->paginate($this->noOfRecord);
        
/*
$landlordTerminations = Termination::whereHas('landlordContract', function ($query) use($request){
                          $query->where('landlord_renewal_termination_status',7)
                                ->where('landlord_contract_status',1)
                                ->filter($request); 
                            })->whereHas('terminationUsers', function ($query)use($roles,$rolesNames) {
                             
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
                            })                             
                        ->where('work_flow_processes_code', '=', 602)
                        ->where('termination_type', '=', 2)
                        ->whereIn('termination_type_status', [2,3])
                        ->sortable()
                        ->paginate($this->noOfRecord);

 */                 




/*
        $landlordTermination = Termination::whereHas('landlordContract', function ($query) use($rolesNames,$result){
                                $query->where('landlord_renewal_termination_status',7)
                                ->where('landlord_contract_status',1)
                                ->closure($result)
                                ->sortable();
                                           
                            })->whereHas('terminationUsers', function ($query) {
                                $query->where('status','=',1);
                            })                             
                        ->where('work_flow_processes_code', '=', 602)
                        ->where('termination_type', '=', 2)
                        ->whereIn('termination_type_status', [2,3]);

                    if (in_array('super_admin', $rolesNames) === false) {
                          $landlordTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
         $landlordTerminations = $landlordTermination->paginate(10);*/

       if(isset($request->ajax))
       return view('backoffice::Termination.landlord_termination_approval_list_ajax',compact('landlordTerminations','request','route'));

        return view('backoffice::Termination.landlord_termination_approval_list',compact('landlordTerminations','enquiry_fields','operations','quick_url'));

    }
    /*
    *
    * LCTermination Approval View
    *
    *
    */
    public function LCTerminationApprovalView($terminationId)
    {
        clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
    //    readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);   
        $termination = Termination::where('id',$terminationId)->first();
        $landlordContract = LandlordContract::where('id',$termination->contract_id)->first();
        $tenantContracts = TenantContract::where('building_id',$landlordContract->building_id)->get();
        return view('backoffice::Termination.landlord_termination_approval_view',compact('landlordContract','termination','tenantContracts'));
    }
    /*
    *
    * Terminated Contract Landlord
    *
    *
    */
    public function TerminatedContractLandlord(Request $request)
    {
        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
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
        
        $request->flash();          

        
       $quick_url =  $route   =  route('TerminatedContractLandlord');



       $landlordTerminations =    ViewLandlordTermination::
                                     filterUsers()
                                     ->filter($request)
                                     ->where('landlord_renewal_termination_status',8)
                                     ->where('landlord_contract_status',0)
                                     ->where('work_flow_processes_code', '=', 603)
                                     ->where('termination_type', '=', 2)
                                     ->sortable()
                                     ->paginate($this->noOfRecord);

      // print_r(json_encode($landlordTerminations));exit();
/*

       $landlordTerminations = Termination::whereHas('landlordContract', function ($query) use($request){
                                $query->where('landlord_renewal_termination_status',8)
                                ->where('landlord_contract_status',0)
                                ->filter($request); 
                            })->whereHas('terminationUsers', function ($query) use($rolesNames,$roles){
                               $query->when( (in_array('super_admin', $rolesNames) === false),function($query) use($roles){
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
                            })                             
                        ->where('work_flow_processes_code', '=', 603)
                        ->where('termination_type', '=', 2)
                        ->sortable()
                        ->paginate($this->noOfRecord);

     */               


/*
        $landlordTermination = Termination::whereHas('landlordContract', function ($query) use($rolesNames,$result){
                                $query->where('landlord_renewal_termination_status',8)
                                ->where('landlord_contract_status',0)
                                ->closure($result)
                                ->sortable();
                                           
                            })->whereHas('terminationUsers', function ($query) {
                                $query->where('status','=',1);
                            })                             
                        ->where('work_flow_processes_code', '=', 603)
                        ->where('termination_type', '=', 2);

                    if (in_array('super_admin', $rolesNames) === false) {
                          $landlordTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
         $landlordTerminations = $landlordTermination->paginate(10);*/
       if(isset($request->ajax))
       return view('backoffice::Termination.landlord_termination_terminated_contracts_list_ajax',compact('landlordTerminations','request','route'));

        return view('backoffice::Termination.landlord_termination_terminated_contracts_list',compact('landlordTerminations','enquiry_fields','operations','quick_url'));

    }
    /*
    *
    * LCTermination Approval View
    *
    *
    */
    public function TerminatedContractLandlordView($terminationId)
    {
      try {

         clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);
        readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification',$terminationId);  
        $termination = Termination::where('id',$terminationId)->first();
        $landlordContract = LandlordContract::where('id',$termination->contract_id)->first();
        return view('backoffice::Termination.landlord_termination_terminated_contracts_view',compact('landlordContract','termination'));
        
      } catch (Exception $e) {

        print_r($e);exit();
        
      }
       
    }
    /*
    *
    *
    * Search Termination 
    */
    public function terminationRequestSearch(Request $request,$result = array())
      {
        $name = Route::currentRouteName();
        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();

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

          $landlordTermination =   new SearchController;       
          $result =     $landlordTermination->landlordDueSearch($request); 
          $request->flash(); 

        }

        if(isset($request->route))
          $route   =  $request->route;

        $landlordTermination = Termination::whereHas('landlordContract', function ($query) use($rolesNames,$result){
                                $query->where('landlord_renewal_termination_status',7)
                                ->where('landlord_contract_status',1)
                                ->closure($result)
                                ->sortable();
                                           
                            })->whereHas('terminationUsers', function ($query) {
                                $query->where('status','=',1);
                            })                             
                        ->where('work_flow_processes_code', '=', 601)
                        ->where('termination_type', '=', 2);

                    if (in_array('super_admin', $rolesNames) === false) {
                          $landlordTermination->whereHas('terminationUsers', function ($query) use($roles) {
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
        $landlordTerminations = $landlordTermination->paginate(10);

       if(isset($request->ajax))
       return view('backoffice::Termination.landlord_termination_open_list_ajax',compact('landlordTerminations','request','route'));

        return view('backoffice::Termination.landlord_termination_open_list',compact('landlordTerminations','enquiry_fields','operations'));
      }
      /*
      *
      *
      * Tenant Contract By Landlord
      *
      *
      */
      public function tenantContractByLandlord($contract_id,$terminationid,$flag)
      {
        $tenantContract = TenantContract::where('id',$contract_id)->first();
        $termination = Termination::where('id',$terminationid)->first();
        return view('backoffice::Termination.landlord_termination_tenant_contract_view',compact('tenantContract','termination','flag'));
      }
      /*
      *
      *
      * Tenant Contract  Pdc By Landlord 
      *
      *
      */
      public function LandlordByTenantPdcView($contract_id,$terminationid,$flag)
      {
        $termination = Termination::where('id',$terminationid)->first();
        $tenantContract = TenantContract::where('id',$contract_id)->first();
        $remainingPdcs = Pdc::where('tenant_contract_id',$contract_id)->get();
        
        return view('backoffice::Termination.landlord_termination_tenant_contract_pdc_view',compact('tenantContract','termination','remainingPdcs','flag'));
      }
      /*
      *
      *
      * Tenant Contract  Invoice By Landlord 
      *
      *
      */
      public function LandlordByTenantInvoiceView($contract_id,$terminationid,$flag)
      {
        
        $termination = Termination::where('id',$terminationid)->first();
        $remainingInvoices = Invoice::where('tenant_contract_id',$contract_id)->get();
//dd($remainingInvoices);
        $tenantContract = TenantContract::where('id',$contract_id)->first();
        
        return view('backoffice::Termination.landlord_termination_tenant_contract_invoice_view',compact('tenantContract','termination','remainingInvoices','flag'));
      }
  }

