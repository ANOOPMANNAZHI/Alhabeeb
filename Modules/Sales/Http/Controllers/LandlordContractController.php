<?php
namespace Modules\Sales\Http\Controllers;

use Modules\Sales\Entities\Sales;
use Modules\Sales\Entities\SalesUsers;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Sales\Entities\LandlordContract;
use Modules\Sales\Entities\viewLandlordEnquiryList;
use Modules\Masters\Entities\Bank;
use Modules\Masters\Entities\ManagementType;
use Modules\Masters\Entities\Employee;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\VendorType;
use Modules\Masters\Entities\Vendor;
use Modules\Masters\Entities\PaymentMethod;
use Modules\Masters\Entities\BuildingType;
use Modules\Masters\Entities\BuildingImage;
use Modules\Masters\Entities\BuildingDocs;
use Modules\Sales\Entities\SalesNote;
use Modules\Masters\Entities\BuildingEleWaterReading;
use App\User;
use DB;
use Image;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Validation;
use App\Http\Controllers\Controller;
use Modules\General\Http\Controllers\GeneralController as General ;
use Modules\Sales\Http\Controllers\SalesEnquiryController;
use Dynamics;
use App\Setting;

class LandlordContractController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
     public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:landlorddocumentation_approve_list', ['only' => ['contractApprovalList','contractApprovalListPendingInfo']]);
        $this->middleware('permission:landlordpendingapproval_list', ['only' => ['contractApprovalListPendingInfo','landlordPendingApproval']]);
        $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 

    } 
    public function index(Request $request,$result = array())
    {
        $enquiry_fields = [
           'sales_enquiry_no' => 'Enquiry No',
           'sales_enquiry_name' => 'Customer Name',
           'sales_email' => 'Email',
           'sales_building_name' => 'Building Name',
           'sales_mobile_no' => 'Mobile No',
           'sales_company_name' => 'Company Name',
           'created_at' => 'Enquiry Date'
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

        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();

        $contracts = viewLandlordEnquiryList::where('sales_enquiry_direct_contract', '=',1)->where('sales_type', '=', 2)->where('work_flow_processes_code', '=', 202)->where('sales_enquiry_direct_contract', '=', 1)->select("sales_enquiry_no","sale_enquiry_id","created_at","cust","cust_no","enquiry_flow", DB::raw("string_agg(distinct loc, ',') AS loc"),"sales_note","cust","enquiry_flow","landlord_contract_no","building","contact_email","landlord_contract_id" )->groupBy("sale_enquiry_id","sales_enquiry_no","created_at","cust_no","sales_note","cust","enquiry_flow","landlord_contract_id","landlord_contract_no","building","contact_email"); 
        /*
        Sales::whereHas('salesEnquiry', function ($query) use($request){
                $query->where('work_flow_processes_code', '=', 202)
                      ->filter($request); 
            })->where('sales.sales_type', '=', 2)
            ->where('sales.work_flow_processes_code', '=', 202)
            ->whereHas('salesUsers', function ($query) {
              $query->where('status','=',1);
            });
        */    
      if (in_array('super_admin', $rolesNames) === false) {

           
                $contracts->where(function ($query) use($roles){
                        $query->where('user_id',null)
                              ->whereIn('role_id', $roles);
                      })
                      ->orWhere(function ($query) use($roles){
                        $query->where('user_id','>',0)
                              ->whereIn('role_id', $roles)
                              ->where('user_id','=', \Auth::user()->id);
                      })                      
                      ->where('status','=',1)->where('work_flow_processes_code', '=', 202);                       
          
        }
    $contract = $contracts->filter($request)->sortable()->paginate($this->noOfRecord);        

    $quick_url =   route('landlordContract.index');

	  //dd($contract);
    if($request->ajax)
      return view('sales::LandlordSales.landlord_contract_list_ajax',compact('contract'));

    return view('sales::LandlordSales.landlord_contract_list',compact('contract','enquiry_fields','operations','quick_url'));
    }

   /*
    *
    * Create contract in Draft mode
    *
    *
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
    public function show($id)
    {
      clearNotification('Modules\Sales\Notifications\EnquiryNotification',$id);
        $roles = \Auth::user()->getRoles();
		
		$sales_enquiry  = salesEnquiry::where('id',$id)->first();
		$allNotes = Sales::with('workFlowProcess')->where('sales_enquiry_id','=',$id)
		  ->where('sales_type', '=', 2)->where('sales_notes', '!=', null)->get();
		  
		$salesNotes = SalesNote::whereHas('sales', function ($query) use($id) {
			$query->where('sales_enquiry_id', '=', $id);                      
		  })->get();
        $work_flow_processes_code     = $sales_enquiry->work_flow_processes_code;
        
        
        $landlordContractInfo = LandlordContract::where('sale_enquiry_id', $id)->first(); 

        //dd($sales_enquiry);
        return view('sales::LandlordSales.landlord_contract_info',compact('sales_enquiry','salesNotes','allNotes','landlordContractInfo','id'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(LandlordContract $landlordContract)
    {
      $year    = prefixData('landlord_agreement_prefix')->configuration_year;
      $isYearCorrect = (date('y') == $year)?true:false;

      $vendorsList     = Vendor::latest()->get();
      $buildingList     = Building::latest()->get();
      $managementType   = ManagementType::latest()->get();
     
       $salesTeam = User::active()->role(['sales_person','sales_coordinator','are','sales_head'])->with('employee')->get()->sortBy('employee.employee_name');

      $paymentmethodList  = PaymentMethod::latest()->get(); 
      
      return view('sales::LandlordSales.edit_contract',compact(['landlordContract','salesTeam','paymentmethodList','managementType','buildingList','vendorsList','isYearCorrect']));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, LandlordContract $landlordContract)
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
          'landlord_contract_amt'               => $request['landlord_contract_amt'],
          'landlord_contract_agreement_amt'     => null,
          'management_id'                       => $management_id,
          'landlord_free_lease_period'          => $request['landlord_free_lease_period'],
          'landlord_duration'                   => $request['duration_type'],
          'landlord_marketing_executive'        => $request['landlord_marketing_executive'],
          'user_id'                             => \Auth::user()->id,
          'landlord_contract_valid_from_date'   => $request['landlord_contract_valid_from_date'],
          'landlord_contract_valid_to_date'     => $request['landlord_contract_valid_to_date'],
          'landlord_contract_payment_type'      => $request['landlord_contract_payment_type'],
          'landlord_contract_status'            => 0,
          'landlord_contract_note'              => $request['landlord_contract_note'],
          'start_date'                          => $request['start_date'],
          'management_method' 					=> $request['management_method'],
          'management_fee_type' 				=> $request['management_fee_type'],
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
        return redirect()->route('landlordContract.index');

    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }

    public function contractGeneration($id)
    {
          
      $vendorsList      = Vendor::latest()->get();
      $buildingList     = Building::latest()->get();
      $managementType   = ManagementType::latest()->get();
      $sale_enquiry_id  = $id;
      $contractLatest   = LandlordContract::orderBy('id', 'DESC')->first();
      $employeeList     =  User::active()->role(['sales_person','sales_coordinator','are','sales_head'])->with('employee')->get()->sortBy('employee.employee_name'); 
      clearNotification('Modules\Sales\Notifications\EnquiryNotification',$id);
      $LandlordContractInfo = LandlordContract::where('sale_enquiry_id', $id)->get();

      $paymentmethodList  = PaymentMethod::latest()->get(); 
      $landlord_agree_prefix  = prefixData('landlord_agreement_prefix')->configuration_value; 
     // if(!empty($contractLatest)){

      //      $nextAgree  = $landlord_agree_prefix.str_pad($contractLatest->id+1,4,'0',STR_PAD_LEFT);
      //}
     // else{
     //       $nextAgree  = $landlord_agree_prefix.str_pad(1,4,'0',STR_PAD_LEFT); 
     // }
       $year    = prefixData('landlord_agreement_prefix')->configuration_year;
        $isYearCorrect = (date('y') == $year)?true:false;

      $generateCode = $this->landlordContractCode();
      $nextAgree = $generateCode['code'];

      return view('sales::LandlordSales.contract_in_draft',compact('paymentmethodList','sale_enquiry_id','vendorsList','buildingList','managementType','nextAgree','employeeList','isYearCorrect'));
    }
    public function landlordContractAction(Request $request){

        $this->validate($request, [
            'building_id'           => 'required',
            'vendor_id'             => 'required',
            'sale_enquiry_id'       => 'required',
        
        ]);

	 $building_id = $request['building_id'] ;
	 $management_id  = $request['management_id'];
	 $contractLatest   = LandlordContract::orderBy('id', 'DESC')->first();
	// $landlord_agree_prefix  = prefixData('landlord_agreement_prefix')->configuration_value; 
   //  if(!empty($contractLatest)){

     //      $nextAgree  = $landlord_agree_prefix.str_pad($contractLatest->id+1,4,'0',STR_PAD_LEFT);
    // }
    // else{
    //       $nextAgree  = $landlord_agree_prefix.str_pad(1,4,'0',STR_PAD_LEFT); 
    // }
	 $generateCode = $this->landlordContractCode();
      $nextAgree = $generateCode['code'];
     $landLord  = LandlordContract::create([
          'building_id'                         => $building_id,
          'vendor_id'                           => $request['vendor_id'],
          'sale_enquiry_id'                     => $request['sale_enquiry_id'],  
          'landlord_contract_name'              => $request['vendor_name'], 
          'landlord_contract_no'                => $nextAgree,
          'landlord_contract_address'           => null,
          'landlord_contract_duration'          => $request['duration_type'],
          'landlord_contract_duration_type'     => 1, //1 - Month, 2 - Year, 3 - Day
          'landlord_contract_management_fee'    => $request['landlord_contract_management_fee'],
          'landlord_contract_percentage'        => $request['landlord_contract_percentage'],
          'landlord_contract_amt'               => $request['landlord_contract_amt'],
          'landlord_contract_agreement_amt'     => null,
          'management_id'                       => $management_id,
          'landlord_free_lease_period'          => $request['landlord_free_lease_period'],
          'landlord_duration'                   => $request['duration_type'],
          'landlord_marketing_executive'        => $request['landlord_marketing_executive'],
          'user_id'                             => \Auth::user()->id,
          'landlord_contract_valid_from_date'   => $request['landlord_contract_valid_from_date'],
          'landlord_contract_valid_to_date'     => $request['landlord_contract_valid_to_date'],
          'landlord_contract_payment_type'      => $request['landlord_contract_payment_type'],
          'landlord_contract_status'            => 0,
          'landlord_contract_note'              => $request['landlord_contract_note'],
          'start_date'                          => $request['start_date'],
          'close_activity'                      => $request['close_activity'],
          'management_method' 					=> $request['management_method'],
          'management_fee_type' 				=> $request['management_fee_type'],
          'created_by'                          => \Auth::user()->id,

        ]);
        
        $buildingData = Building::find($building_id);
		$buildingData->management_id = $management_id;
		$buildingData->building_status = 1;
		$buildingData->save();
        Setting::where('configuration_settings','landlord_agreement_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);

		//Log
		activity('Create Landlord Contract')
          ->performedOn($landLord)
          ->causedBy(\Auth::user()->id)
          ->withProperties($landLord)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Landlord contract created successfully');
        return redirect()->route('landlordContract.index');


    }
     /**
     * Landlord Name
     * @return value
     */
    public function vendorNameAjax(Request $request){

       $id = $request->input('id'); 

       $list = Vendor::select('id','vendor_name')->where('id',$id)->first();
       return $list->vendor_name;

    }
     /**
     * Building Name
     * @return value
     */
    public function buildingNameAjax(Request $request){

       $id = $request->input('id'); 

       $list = Building::select('id','building_name')->where('id',$id)->first();
       return $list->building_name;

    } 
   
    /**
     * Landlord pop-up
     * @return value
     */
    public function landlordPopup(){

      $location     = Location::orderBy('id', 'DESC')->get(); 
      $bank         = Bank::orderBy('id', 'DESC')->get(); 
      $vendorType   = VendorType::where('id', 2)->get();
      
     // $vendorIndex = Vendor::where('vendor_type_id', 2)->orderBy('id', 'DESC')->first();
      
     // $landlord_prefix  = prefixData('landlord_prefix')->configuration_value;
      
    //  if(!empty($vendorIndex)){
           
    //        $nextCode = $landlord_prefix.str_pad($vendorIndex->vendor_index + 1,4,'0',STR_PAD_LEFT);
             
    //  }
    //  else{
           
    //        $nextCode = $landlord_prefix.str_pad(1,4,'0',STR_PAD_LEFT); 
    //  }
        return view('sales::LandlordSales.create_landlord', compact(['location','bank','vendorType']));
    }
    /**
     * Landlord Pop-up Action
     * @return value
     */
    public function landlordPopupAction(Request $request){

		
      //  $vendorIndex = Vendor::where('vendor_type_id', 2)->orderBy('id', 'DESC')->first();
       // $landlord_prefix  = prefixData('landlord_prefix')->configuration_value;

		//if(!empty($vendorIndex->vendor_index)){
      //      $nextCode = $landlord_prefix.str_pad($vendorIndex->vendor_index + 1,4,'0',STR_PAD_LEFT);
       //     $vendorIndex = $vendorIndex->vendor_index + 1;
       // }else{
      //      $nextCode = $landlord_prefix.str_pad(1,4,'0',STR_PAD_LEFT);
		//	$vendorIndex = 1;
		//}


        $rules = array(
          'vendor_name' => 'required|max:30',           
          'vendor_contact_address' => 'required|max:250',           
          'vendor_pc' => 'required',           
          'location_id' => 'required',
          'vendor_contact_no' => 'required|regex:/[0-9]{10}/',      
          'vendor_contact_person' => 'required' ,        
          'vendor_contact_email' => 'required|email' ,        
          'vendor_type' => 'required' ,        
          'vendor_fax_no' => 'required' ,        
          'bank_id' => 'required' ,        
          'vendor_status' => 'required',
       );   

  
        $venderIns = Vendor::create([
         'vendor_code' => $request->vendor_codes,
         'vendor_name' => $request->vendor_name,
         'vendor_type_id' => $request->vendor_type_id,
         'vendor_contact_address' => $request->vendor_contact_address,
         'vendor_secondary_address' => $request->vendor_secondary_address,
         'vendor_pc' => $request->vendor_pc,
         'location_id' => $request->location_id,
         'vendor_contact_no' => $request->vendor_contact_no,
         'vendor_contact_person' => $request->vendor_contact_person,
         'vendor_contact_email' => $request->vendor_contact_email,
         'vendor_fax_no' => $request->vendor_fax_no,
         'vendor_acc_no' => $request->vendor_acc_no,
         'vendor_status' => 1,
         'bank_id' => $request->bank_id,
         'created_by' =>  \Auth::user()->id,
        ]);  

        if($venderIns->id){
			
			//Log
			activity('Create Landlord profilr Info')
			  ->performedOn($venderIns)
			  ->causedBy(\Auth::user()->id)
			  ->withProperties($venderIns)
			  ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
           // $res = array($venderIns->id =>$nextCode,'vendor_name'=>$request->vendor_name);
			$res = array($venderIns->id =>$request->vendor_codes,'vendor_name'=>$request->vendor_name);

            return json_encode($res);
        }
        else{

            return false;
        }
    }

    /**
     * Building pop-up
     * @return value
     */
    public function buildingPopup(){

      $location     = Location::orderBy('id', 'DESC')->get(); 
      $bank         = Bank::orderBy('id', 'DESC')->get(); 
      $vendorType   = VendorType::where('id', 2)->get();
      $vendors      = Vendor::orderBy('id', 'DESC')->get(); 
      $buildingTypes= BuildingType::active()->orderBy('id', 'DESC')->get(); 
      $managementTypes= ManagementType::orderBy('id', 'DESC')->get(); 
      $buildingLatest = Building::orderBy('id', 'DESC')->first();
	  $building_prefix  = prefixData('building_prefix')->configuration_value;
	  $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
      if(!empty($buildingLatest)){
           
            $nextCode = $building_prefix.str_pad($buildingLatest->id+1,4,'0',STR_PAD_LEFT);
             
      }
      else{
           
            $nextCode = $building_prefix.str_pad(1,4,'0',STR_PAD_LEFT); 
      }
      return view('sales::LandlordSales.create_building', compact('nextCode','location','bank','vendorType','vendors','buildingTypes','managementTypes','upload_size'));
    }
    /**
     * Building Pop-up Action
     * @return value
     */
    public function buildingPopupAction(Request $request){

    
        $buildingLatest = Building::orderBy('id', 'DESC')->first();
		$building_prefix  = prefixData('building_prefix')->configuration_value;
		 
        if(!empty($buildingLatest))
            $nextCode = $building_prefix.str_pad($buildingLatest->id+1,4,'0',STR_PAD_LEFT);
        else
            $nextCode = $building_prefix.str_pad(1,4,'0',STR_PAD_LEFT); 

          
        $this->validate($request, [
          'building_name' => 'required|max:30',           
          'vendor_id' => 'required',           
          'building_no' => 'required', 
          'building_type_id' => 'required', 
          'management_id' => 'required', 
          'building_address' => 'required|max:250', 
          'building_pc' => 'required', 
          'location_id' => 'required',           
          'google_location' => 'required', 
          'building_note' => 'required', 
       ]);


       $build = Building::create([
            'building_code' => $nextCode,
            'building_name' => $request->building_name,
            'vendor_id' => $request->vendor_id,
            'building_no' => $request->building_no,
            'building_prefix' => $request->building_prefix,
            'building_type_id' => $request->building_type_id,
            'management_id' => $request->management_id,
            'building_address' => $request->building_address,
            'building_pc' => $request->building_pc,
            'location_id' => $request->location_id,
            'building_note' => $request->building_note,
            'building_status' => 0,
            'building_geo_long' => $request->building_geo_long,
            'building_geo_lat' => $request->building_geo_lat,
            'google_location' => $request->google_location,
            'building_no_floor' => $request->building_no_floor,
            'plot_no' => $request->plot_no,
            'block_number' => $request->block_number,
            'build_up_area' => $request->build_up_area,
            'landmark' => $request->landmark,
            'db_number' => $request->db_number,
            'building_year' => $request->building_year,
            'watchman_no' => $request->watchman_no,
            'management_date' => $request->management_date,
			'ax_division' => $request->ax_division,
            'building_maintenance_info' => $request->building_maintenance_info,
            'created_by' => \Auth::user()->id,
           ]);
		if( AX_ENABLE_DISABLE == 1 && isset($build->id)){	
			$buildingInfo  = Building::where('id',$build->id)->first();
			
			if(Dynamics::BuildingAxPushData('AXBuilding', $buildingInfo)=='Error'){
				Building::destroy($building->id);
				return Redirect::back()->withMessage('error', 'Microsoft Dynamics API Service Error');
			}
		
		}
          /* Ele Meter reading*/
          if(!empty($request->building_meter_category)):
            $building_meter_category = $request->building_meter_category;
            foreach($building_meter_category as $key => $category):
		
				if(isset($request['building_meter_category'][$key])){
                $readings=BuildingEleWaterReading::create(['building_id'=>$build->id,
                                  'building_meter_category'=>$request['building_meter_category'][$key],
                                  'electricity_acc_no'=>$request['electricity_acc_no'][$key],
                                  'electricity_met_no'=>$request['electricity_met_no'][$key],
                                  'water_acc_no'=>$request['water_acc_no'][$key],
                                  'water_met_no'=>$request['water_met_no'][$key],
                                  'created_by' => \Auth::user()->id]);
				}
            endforeach;
          endif;
          /* Building Image*/
          if(!empty($request->file('building_img_name'))):
            $files = $request->file('building_img_name');
            foreach($files as $key => $file):
               if(isset($request['building_img_category'][$key])){
                $path = public_path('img');
   

                $imageName = time().'.'.$request['building_img_name'][$key]->getClientOriginalExtension();
             

                $large_img = Image::make($request->file('building_img_name')[$key]->getRealPath());
                $large_img->resize(800, 500);
                $large_img->save($path.'/'.$imageName,100);
                $img_path =    Storage::putFile('public/BuildingImages', new File($path.'/'.$imageName), 'public');

                $thumb_img = Image::make($request->file('building_img_name')[$key]->getRealPath());
                $thumb_img->resize(150, 100);
                $thumb_img->save($path.'/'.$imageName,100);           
                $thumb_path = Storage::putFile('public/BuildingImages', new File($path.'/'.$imageName), 'public');


                $docs=BuildingImage::create(['building_id'=>$build->id,
                                  'building_path_file_name'=>$img_path,
                                  'building_img_category'=>$request['building_img_category'][$key],
                                  'building_path_thumbnail'=>$thumb_path,
                                  'created_by' => \Auth::user()->id]);
              }
            endforeach;
          endif;
          /* Building Docs*/
          if(!empty($request->file('building_doc_path_name'))):
            $files = $request->file('building_doc_path_name');
            foreach($files as $key => $file):
				if(isset($request['building_doc_category'][$key])){
                $uniqueFileName = $file->getClientOriginalName() ;
                $doc_path = Storage::putFile('public/BuildingDocs',$request['building_doc_path_name'][$key]);

                $docs=BuildingDocs::create(['building_id'=>$build->id,
                                  'building_doc_category'=>$request['building_doc_category'][$key],
                                  'building_doc_path_name'=>$doc_path,
                                  'building_doc_name'=>$uniqueFileName,
                                  'created_by' => \Auth::user()->id]);
                }
            endforeach;
          endif;


        if($build->id){
			 activity('Add Building')
				  ->performedOn($build)
				  ->causedBy(\Auth::user()->id)
				  ->withProperties($build)
				  ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
            $res = array($build->id =>$nextCode,'building_name'=>$request->building_name, 'management_id'=>$request->management_id);
            return json_encode($res);
        }
        else{

            return false;
        }
    }
    
     /**
     * Landlord contract Approval list
     * @return value
     */
    public function contractApprovalList(Request $request){

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
     
        $pendingPage = false;	
        
        $request->flash();    
        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
	
  
        $landlordContracts =   LandlordContract::whereHas('salesEnquiry',function($query){
                                 $query->where('work_flow_processes_code', '=', 203);
                                  })
                                 ->whereHas('landlordSale',function($query)use($roles,$rolesNames){
                                     $query->salesUsers();
                                  })
                                 ->filter($request)
                                 ->paginate($this->noOfRecord);


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
      $approveList = $approveLists->sortable()->paginate(10); */
         
      $quick_url =   $route  = route('contractApprovalList');          
          
      if(isset($request->ajax)) 			
			return view('sales::LandlordSales.landlord_approve_list_ajax',compact('landlordContracts','request','route'));
        
      return view('sales::LandlordSales.landlord_approve_list',compact('landlordContracts','enquiry_fields','operations','quick_url'));
       
    }
    /**
     * Landlord contract Approval list
     * @return value
     */
    public function contractApprovalListInfo($id,$stage){

        clearNotification('Modules\Sales\Notifications\EnquiryNotification',$id);
        $roles = \Auth::user()->getRoles();
        
        $sales_enquiry  = salesEnquiry::where('id',$id)->first();
        $work_flow_processes_code     = $sales_enquiry->work_flow_processes_code;
        
        $landlordContracts = LandlordContract::where('sale_enquiry_id','=',$id)
                            ->where('landlord_contract_status','=',0)->get();
        $direct_indirect_status = $landlordContracts->pluck('landlord_indirect_direct_status')->contains(1);
        /*
        $contract = Sales::whereHas('salesEnquiry', function ($query) use($work_flow_processes_code){
                        $query->where('work_flow_processes_code', '=', $work_flow_processes_code);                  
                })     
                ->salesUsers()->where('sales_type', '=', 2)
                ->where('work_flow_processes_code', '=', $work_flow_processes_code)
                ->where('sales_enquiry_id', '=', $id)->first();
        dd($contract);        
        */ 
		$salesNoteList = Sales::where('sales_enquiry_id',$id)->where('sales_notes','!=','null')->where('sales_notes','!=','null')->get();
        //dd($salesNoteList);
        
        $landlordContractInfo = LandlordContract::where('sale_enquiry_id', $id)->first(); 
 
        
        return view('sales::LandlordSales.landlord_contract_approve_info',compact('sales_enquiry','landlordContractInfo','id','direct_indirect_status','salesNoteList'));

    }
    
    /**
     * Landlord contract Approval Pending list
     * @return value
     */
    public function contractApprovalListPendingInfo($id,$stage){

      
        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
        
        $sales_enquiry  = salesEnquiry::where('id',$id)->first();
        $work_flow_processes_code     = $sales_enquiry->work_flow_processes_code;
        
        $landlordContracts = LandlordContract::where('sale_enquiry_id','=',$id)
                            ->where('landlord_contract_status','=',0)->get();
        $direct_indirect_status = $landlordContracts->pluck('landlord_indirect_direct_status')->contains(1);
        
        $contract = Sales::whereHas('salesEnquiry', function ($query) use($work_flow_processes_code,$rolesNames,$roles){
                    $query->where('work_flow_processes_code', '=', $work_flow_processes_code)
                         ->when( (in_array('super_admin', $rolesNames) === false), function($query)use($roles) { 
                           $query->whereHas('sales.salesUsers', function ($query) use($roles) {
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
                        });                  
                })                  
                ->where('sales_type', '=', 2)
                ->where('work_flow_processes_code', '=', $work_flow_processes_code)
                 ->where('sales_enquiry_id', '=', $id)
                ->paginate(10)->first();
                
       //dd($contract);
        $salesNoteList = Sales::where('sales_enquiry_id',$contract->sales_enquiry_id)->where('sales_notes','!=','null')->get();
        //dd($salesNoteList);
       $allNotes = Sales::with('workFlowProcess')->where('sales_enquiry_id','=',$id)
      ->where('sales_type', '=', 2)->where('sales_notes', '!=', null)->get();
        $landlordContractInfo = LandlordContract::where('sale_enquiry_id', $id)->first(); 
 
        
        return view('sales::LandlordSales.landlord_contract_approve_pending_info',compact('sales_enquiry','landlordContractInfo','id','direct_indirect_status','salesNoteList','allNotes'));

    }
    /**
     * Landlord contract Approval list
     * @return value
     */
    public function contractApprovalWon(Request $request){

        $enquiry_fields = [
           'sales_enquiry_no' => 'Enquiry No',
           'sales_enquiry_name' => 'Customer Name',
           'sales_email' => 'Email',
           'landlordContract__buildingInfo__building_name' => 'Building Name',
           'sales_mobile_no' => 'Mobile No',
           'sales_company_name' => 'Company Name',
           'created_at' => 'Enquiry Date'
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

        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
        $approveList = Sales::whereHas('salesEnquiry', function ($query) use($request,$roles,$rolesNames){
                $query->where('work_flow_processes_code', '=', 204)
                ->where('sales_enquiry_direct_contract', '=', 1)
                ->filter($request)
                ->whereHas('sales.salesUsers', function ($query) use($roles,$rolesNames){
              $query->when( (in_array('super_admin', $rolesNames) === false), function($query)use($roles) {
                 $query->where(function ($query) use($roles){
                        $query->where('user_id',null)
                              ->whereIn('role_id', $roles);
                      })
                      ->orWhere(function ($query) use($roles){
                        $query->where('user_id','>',0)
                              ->whereIn('role_id', $roles)
                              ->where('user_id','=', \Auth::user()->id);
                      }); 
                 
              })->where('status','=',1);
            });                                 
            })->where('sales.sales_type', '=', 2)
            ->where('sales.work_flow_processes_code', '=', 204)
           ->sortable()
            ->paginate($this->noOfRecord);

   /*     if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
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
            });//dd($leads);
        }
        $approveList = $approveLists->sortable()->paginate($this->noOfRecord);
        */      
      $quick_url =  $route   = route('contractApprovalWon');          
          
      if(isset($request->ajax)) 			
			return view('sales::LandlordSales.landlord_won_list_ajax',compact('approveList','request','route'));	

      
      return view('sales::LandlordSales.landlord_won_list',compact('approveList','enquiry_fields','operations','quick_url'));
       
    }
    /**
     * Landlord contract Approval list
     * @return value
     */
    public function contractApprovalLoss(Request $request){

        $enquiry_fields = [
           'sales_enquiry_no' => 'Enquiry No',
           'sales_enquiry_name' => 'Customer Name',
           'sales_email' => 'Email',
           'sales_building_name' => 'Building Name',
           'sales_mobile_no' => 'Mobile No',
           'sales_company_name' => 'Company Name',
           'created_at' => 'Enquiry Date'
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

        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
        $approveList = Sales::whereHas('salesEnquiry', function ($query) use($request){
                $query->where('work_flow_processes_code', '=', 205)
                      ->filter($request);                          
            })->where('sales.sales_type', '=', 2)
           // ->where('sales.work_flow_processes_code', '=', 205)
            ->salesUsers()
            ->sortable()
            ->paginate($this->noOfRecord);

/*
        if (in_array('super_admin', $rolesNames) === false) {
          //dd(88);
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
            });//dd($leads);
        }
        $approveList = $approveLists->sortable()->paginate(10);
*/


        $previous = Sales::whereHas('salesEnquiry', function ($query) {
                $query->where('work_flow_processes_code', '=', 205);                      
            })->orderBy('created_at', 'desc')->skip(1)->take(1)->first();
            
          //dd($approveList);  
      
      $quick_url = $route = route('contractApprovalLoss');
          
          
      if(isset($request->ajax)) 			
			return view('sales::LandlordSales.landlord_loss_list_ajax',compact('approveList','previous','request','route'));	                

      return view('sales::LandlordSales.landlord_loss_list',compact('approveList','previous','enquiry_fields','operations','quick_url'));
       
    }
    
    
    
     
    public function landlordContractSearch(Request $request){
		
		$SalesEnquiry = 	 new SalesEnquiryController;     
	 
	   $result = array();	
	 
		if(isset($request->fieldName)){
			 if(count($request->fieldName) > 0){			   
			   $result  = 	$SalesEnquiry->enquirySearch($request);
			  }
        }elseif($request->ajax == true){ 			 
			 $result  =  $SalesEnquiry->enquirySearch($request);
			 $route   =  $request->route;
		}else
        return redirect()->route('landlordLead.index');    
         
       $request->flash(); 
     
        if(isset($request->route))
          $route   =  $request->route;
       
       list($contract ,$enquiry_fields,$operations) = $this->index($request,$result);    
      
       if(isset($request->ajax))
       return view('sales::LandlordSales.landlord_contract_list_ajax',compact('contract','route'));
       else  
      return view('sales::LandlordSales.landlord_contract_list',compact('contract','enquiry_fields','operations'));     
      
	
	}
  /**
    *
    * Landlord Autocomplete
    *
    **/
    public function landlordAutocomplete(Request $request){

       $key = $request->term;
       
       $landLord =   Vendor::where('vendor_status',1)->where('vendor_type_id',2)->where('vendor_name', 'ILIKE', '%'.$key.'%')
                               ->select('id AS ids','vendor_name as value')->limit(10)
                               ->get();

       return $landLord ;


    }  
    /**
    *
    * Landlord Autocomplete
    *
    **/
    public function landlordAutocompleteCode(Request $request){

       $key = $request->term;
       
       $landLord =   Vendor::where('vendor_status',1)->where('vendor_name', 'ILIKE', '%'.$key.'%')
                               ->select('id AS ids',DB::raw("CONCAT(vendor_name,'-',vendor_code) as value"))->limit(10)
                               ->get();

       return $landLord ;


    }  
  /**
   * Building Name
   * @return value
   */
  public function buildingNameCodeAjax(Request $request){

     $id = $request->input('id'); 

     $list = Building::select('id','building_name','building_code','management_id')->where('id',$id)->first();
     return $list->building_name.'-'.$list->building_code;

  } 
  /**
   * vendor Name
   * @return value
   */
  public function landlordNameCodeAjax(Request $request){

     $id = $request->input('id'); 

     $list = Building::select('id','building_name','building_code')->where('id',$id)->first();
     return $list->building_name.'-'.$list->building_code;

  } 
  /**
     * Landlord Name
     * @return value
     */
    public function vendorNameAjaxCode(Request $request){

       $id = $request->input('id'); 

       $list = Vendor::select('id','vendor_name','vendor_code')->where('id',$id)->first();
       return $list->vendor_name.'-'.$list->vendor_code;

    }
    /**
     * Landlord Enjquiry View
     * @return Null
     */
    public function landlordEnquiryView($id){
		
	  $enquiry  = salesEnquiry::where('id',$id)->first();
      $allNotes = Sales::with('workFlowProcess')->where('sales_enquiry_id','=',$id)
      ->where('sales_type', '=', 2)->where('sales_notes', '!=', null)->get();
      
      $salesNotes = SalesNote::whereHas('sales', function ($query) use($id) {
        $query->where('sales_enquiry_id', '=', $id);                      
      })->get();
  
     
      return view('sales::LandlordSales.view_sales_enquiry',compact('enquiry','allNotes','salesNotes'));
	}
	public function landlordContractCode(){

      $prefix  = prefixData('landlord_agreement_prefix')->configuration_value.prefixData('landlord_agreement_prefix')->configuration_year;
     
      $incVal  = prefixData('landlord_agreement_prefix')->configuration_increment_value;
     
      $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);
     
      return array('code'=>$nextCode,'inc'=>$incVal);

    }

}
