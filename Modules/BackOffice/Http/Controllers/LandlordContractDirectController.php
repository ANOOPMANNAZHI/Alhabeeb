<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Sales\Entities\LandlordContract;
use Modules\Masters\Entities\Vendor;
use Modules\Masters\Entities\ManagementType;
use Modules\Masters\Entities\PaymentMethod;
use Modules\Masters\Entities\Employee;
use Modules\Masters\Entities\Building;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Sales\Entities\Sales;
use Modules\Sales\Entities\SalesUsers;
use App\User;
use Modules\General\Http\Controllers\GeneralController as General ;
use App\Setting;
use Exception;

class LandlordContractDirectController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function __construct()
    {
        $this->middleware('auth');    
        $this->middleware('permission:landlord_contract_direct_list', ['only' => ['index','show']]);   
        $this->middleware('permission:edit_landlord_contract_direct', ['only' => ['edit','update']]);
        $this->middleware('permission:add_landlord_contract_direct', ['only' => ['create','store']]);
		
		$this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value;    
        
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {		 
      // echo "<pre>";print_r($request->expiredContracts);exit;
				if(isset($request->expiredContracts) && $request->expiredContracts){
          $today = date('Y-m-d');

          $landlordContracts = LandlordContract::filter($request)->where('landlord_contract_status','=',1)
          ->whereDate('landlord_contract_valid_to_date','<=',$today)
          ->where('management_id','=',1)
          ->where('landlord_renewal_termination_status', '!=',6)
          ->sortable()->paginate($this->noOfRecord);
          $expiredContracts = true;
        }
        else{
           $landlordContracts = LandlordContract::filter($request)->whereIn('landlord_contract_status',[1,2,3])
    ->where('landlord_renewal_termination_status', '!=',6)->expiringExpired($request)->sortable()->paginate($this->noOfRecord);
      $expiredContracts = false;
        }
       
            
        //dd($tenantContracts);                         
        $enquiry_fields = [
           'landlord_contract_no' => 'Agreement No',                 
           'buildingInfo__building_name'      => 'Building Name',
           'vendorName__vendor_name'      => 'Landlord Name',
           'managementTypeInfo__management_types_name'      => 'Management Type',           
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
       $request->flash();  


       $quick_url = $route = route('landlord-contract.index'); 
         
        if(isset($request->ajax))
			return view('backoffice::LandlordContract.landlord_contract_list_ajax',compact('landlordContracts','request','route','expiredContracts'));	
			
		    
       return view('backoffice::LandlordContract.landlord_contract_list',compact('landlordContracts','enquiry_fields','operations','quick_url','expiredContracts'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $vendorsList      = Vendor::latest()->get();
        $buildingList     = Building::latest()->get();
        $managementType   = ManagementType::latest()->get();
        $contractLatest   = LandlordContract::orderBy('id', 'DESC')->first();
      
$salesTeam = User::active()->role(['sales_coordinator'])->orderBy('id', 'DESC')->get(); 

//dd($salesTeam);
        $paymentmethodList  = PaymentMethod::orderBy('payment_method_index', 'ASC')->get(); 
      //  $landlord_agree_prefix  = prefixData('landlord_agreement_prefix')->configuration_value; 
      //  if(!empty($contractLatest)){

        //    $nextAgree  = $landlord_agree_prefix.str_pad($contractLatest->id+1,4,'0',STR_PAD_LEFT);
       // }
       // else{
       //     $nextAgree  = $landlord_agree_prefix.str_pad(1,4,'0',STR_PAD_LEFT); 
       // }
       $year    = prefixData('landlord_payment_prefix')->configuration_year;
          $isYearCorrect = (date('y') == $year)?true:false;

      $generateCode = $this->landlordContractCode();
      $nextAgree = $generateCode['code'];


        return view('backoffice::LandlordContract.add_contract_direct',compact('paymentmethodList','vendorsList','buildingList','managementType','nextAgree','salesTeam'));
        
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'building_id'           => 'required',
            'vendor_id'             => 'required',
        
        ]);
        
        /*
        *
        * Sales Enquiry & Sales & Sales Users Add 
        *
        */
        $vendorId = $request['vendor_id'];
        $vendorDetails = Vendor::where('id','=',$vendorId)->first();
        $processFlow = 203;
        // Direct contract Next Index and Nextcode
        $salesLatest = SalesEnquiry::latest()->where('sales_type',2)->where('sales_enquiry_direct_contract',2)->first();
        $prefix       = prefixData('landlord_enquiry_no_prefix')->configuration_value;
        if(!empty($salesLatest)){
            $nextCode 	= 'DLLENQ'.str_pad($salesLatest->enquiry_index+1,4,'0',STR_PAD_LEFT);
			$index 		= $salesLatest->enquiry_index+1;
        }
        else{
            $nextCode 	= 'DLLENQ'.str_pad(1,4,'0',STR_PAD_LEFT);    
            $index 		= 1;
        }
		$contractLatest   = LandlordContract::orderBy('id', 'DESC')->first();
		//$landlord_agree_prefix  = prefixData('landlord_agreement_prefix')->configuration_value; 
       // if(!empty($contractLatest)){

       //     $nextAgree  = $landlord_agree_prefix.str_pad($contractLatest->id+1,4,'0',STR_PAD_LEFT);
       // }
       // else{
///$nextAgree  = $landlord_agree_prefix.str_pad(1,4,'0',STR_PAD_LEFT); 
       // }
	     $generateCode = $this->landlordContractCode();
         $nextAgree = $generateCode['code'];
        /**************************Sales Enquiry *******************************/
        $salesEnquiry = new SalesEnquiry;
        $salesEnquiry->sales_enquiry_no = $nextCode;
        $salesEnquiry->sales_type = 2; 
        $salesEnquiry->sales_mobile_no = $vendorDetails->vendor_contact_no;
        $salesEnquiry->sales_enquiry_name = $vendorDetails->vendor_name;
        $salesEnquiry->work_flow_processes_code = $processFlow; 
        $salesEnquiry->created_by = \Auth::user()->id; 
        $salesEnquiry->enquiry_index = $index ;
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
        $building_id = $request['building_id'] ;
        $management_id  = $request['management_id'];
        
        $landLord  = LandlordContract::create([
          'building_id'                         => $building_id,
          'vendor_id'                           => $request['vendor_id'],
          'sale_enquiry_id'                     => $salesEnquiry->id,  
          'landlord_contract_name'              => $vendorDetails->vendor_name, 
          'landlord_contract_no'                => $nextAgree,
          'landlord_contract_address'           => null,
          'landlord_contract_duration'          => $request['duration_type'],
          'landlord_contract_duration_type'     => 1, //1 - Month, 2 - Year, 3 - Day
          'landlord_contract_management_fee'    => $request['landlord_contract_management_fee'],
          'landlord_contract_percentage'        => $request['landlord_contract_percentage'],
          'landlord_contract_amt'               => replaceCommaWithDot($request['landlord_contract_amt']),
          'landlord_contract_agreement_amt'     => null,
          'management_id'                       => $management_id,
          'landlord_free_lease_period'          => $request['landlord_free_lease_period'],
          'landlord_duration'                   => $request['duration_type'],
          'landlord_marketing_executive'        => $request['landlord_marketing_executive'],
          'user_id'                             => \Auth::user()->id,
          'landlord_contract_valid_from_date'   => $request['landlord_contract_valid_from_date'],
          'landlord_contract_valid_to_date'     => $request['landlord_contract_valid_to_date'],
          'landlord_contract_payment_type'      => $request['landlord_contract_payment_type'],
          'landlord_contract_status'            => 2, //0 - Pending in Normal Flow , 1 - Approved , 2 - Unapproved Direct , 3- Pending Approval in Direct Contract
          'landlord_contract_note'              => $request['landlord_contract_note'],
          'start_date'                          => $request['start_date'],
          'close_activity'                      => $request['close_activity'],
          'management_method'           		=> $request['management_method'],
          'management_fee_type'         		=> $request['management_fee_type'],
          'landlord_indirect_direct_status' 	=> 1 , 
          'created_by'                          => \Auth::user()->id,
          'created_at'                          => $request['agreement_date'],

        ]);
        
        $buildingData = Building::find($building_id);
        $buildingData->management_id = $management_id;
        //$buildingData->building_status = 1;
        $buildingData->save();
        Setting::where('configuration_settings','landlord_agreement_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);
        //Log
        activity('Create Landlord Contract Direct')
          ->performedOn($landLord)
          ->causedBy(\Auth::user()->id)
          ->withProperties($landLord)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Landlord contract created successfully');
        return redirect()->route('landlord-contract.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
      try{
          $landlordContractInfo = LandlordContract::where('id',$id)->first();

          if(!isset($landlordContractInfo->sale_enquiry_id)){ return redirect()->back();}
          $salesNotes = Sales::where('sales_enquiry_id',$landlordContractInfo->sale_enquiry_id)->get();

          $notesArray = array();
              foreach($salesNotes as $key=>$note){

                if(isset($note->sales_notes)){
                  $noted_user = Sales::where('id','>',$note->id)->limit(1)->first();
                  if(isset($noted_user->createdBy->employee->employee_name)){
                    $notesArray[$key]['employee_name'] = $noted_user->createdBy->employee->employee_name;
                    $notesArray[$key]['sales_notes']  = $note->sales_notes;
                    $notesArray[$key]['created_at']   = $note->created_at->format('d/m/Y h:m A');
                    $notesArray[$key]['stage']   = $note->workFlowProcess->work_flow_processes_name;
                  }
                }
              }
              //print_r(json_encode($landlordContractInfo));exit();
              return view('backoffice::LandlordContract.view',compact('landlordContractInfo','notesArray'));
      } 
      catch (\Exception $e) {

        return $e->getMessage();
        
      }

        
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(LandlordContract $landlordContract)
    {
      try{
        $vendorsList     = Vendor::latest()->get();
        $buildingList     = Building::latest()->get();
        $managementType   = ManagementType::latest()->get();
     
        $salesTeam = User::active()->role(['sales_person','sales_coordinator','ceo','are','sales_head'])->orderBy('id', 'DESC')->get(); 

        $paymentmethodList  = PaymentMethod::orderBy('payment_method_index', 'ASC')->get(); 
        $year    = prefixData('landlord_agreement_prefix')->configuration_year;
        $isYearCorrect = (date('y') == $year)?true:false;
        return view('backoffice::LandlordContract.edit_contract',compact(['landlordContract','salesTeam','paymentmethodList','managementType','buildingList','vendorsList','isYearCorrect']));
      }
      catch (\Exception $e) {

        return $e->getMessage();
        
      }
        
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,LandlordContract $landlordContract)
    {
        $this->validate($request, [
            'building_id'           => 'required',
            'vendor_id'             => 'required',
        ]);

        $building_id = $request['building_id'] ;
        $management_id  = $request['management_id'];
        $landlordContract->update([
          'building_id'                         => $building_id,
          'vendor_id'                           => $request['vendor_id'],
          'sale_enquiry_id'                     => $request['sale_enquiry_id'],  
          'landlord_contract_name'              => $request['vendor_name'], 
          'landlord_contract_no'                => $request['landlord_contract_no'],
          'landlord_contract_address'           => null,
          'landlord_contract_duration'          => $request['duration_type'],
          'landlord_contract_duration_type'     => 1, //1 - Month, 2 - Year, 3 - Day
          'landlord_contract_management_fee'    => $request['landlord_contract_management_fee'],
          'landlord_contract_percentage'        => $request['landlord_contract_percentage'],
          'landlord_contract_amt'               => replaceCommaWithDot($request['landlord_contract_amt']),
          'landlord_contract_agreement_amt'     => null,
          'management_id'                       => $management_id,
          'landlord_free_lease_period'          => $request['landlord_free_lease_period'],
          'landlord_marketing_executive'        => $request['landlord_marketing_executive'],
          'landlord_duration'                   => $request['duration_type'],
          'user_id'                             => \Auth::user()->id,
          'landlord_contract_valid_from_date'   => $request['landlord_contract_valid_from_date'],
          'landlord_contract_valid_to_date'     => $request['landlord_contract_valid_to_date'],
          'landlord_contract_payment_type'      => $request['landlord_contract_payment_type'],
          //'landlord_contract_status'            => 0,
          'landlord_contract_note'              => $request['landlord_contract_note'],
          'start_date'                          => $request['start_date'],
          'management_method'                   => $request['management_method'],
          'management_fee_type'                 => $request['management_fee_type'],
          'close_activity'                      => $request['close_activity'],
          'created_by'                          => \Auth::user()->id,

        ]);
        $buildingData = Building::find($building_id);
        $buildingData->management_id = $management_id;
        $buildingData->save();
        //Log
        activity('Update Landlord Contract')
          ->performedOn($landlordContract)
          ->causedBy(\Auth::user()->id)
          ->withProperties($landlordContract)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Landlord contract successfully updated');
        return redirect()->route('landlord-contract.index');
    }

    public function closeLandlordContract(Request $request){

      LandlordContract::where('id',$request['contract_id'])->update(['end_date'=>$request['end_date'],'landlord_contract_duration'=>0]);

      echo json_encode(LandlordContract::where('id',$request['contract_id'])->first());
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
     * Landlord Direct Contract Send for Approval
     * 
     */
    public function landlordSendForApproval($contract_id,$redirectPage)
	{
		LandlordContract::where('id',$contract_id)->update(['landlord_contract_status'=>3]);
		$landlordInfo = LandlordContract::where('id',$contract_id)->first();
		SalesEnquiry::where('id',$landlordInfo->sale_enquiry_id)->update(['work_flow_processes_code'=>203]);
		
		if($redirectPage=='view')
		  return redirect(url('landlord-contract/'.$contract_id));
		else
		  return redirect()->route('landlord-contract.index');
	}
	public function landlordContractCode(){

      $prefix  = prefixData('landlord_agreement_prefix')->configuration_value.prefixData('landlord_agreement_prefix')->configuration_year;
     
      $incVal  = prefixData('landlord_agreement_prefix')->configuration_increment_value;
     
      $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);
     
      return array('code'=>$nextCode,'inc'=>$incVal);

    }
}
