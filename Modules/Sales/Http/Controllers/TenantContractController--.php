<?php

namespace Modules\Sales\Http\Controllers;

use App\User;
use DB;
use Modules\Sales\Entities\TenantContract;
use Modules\Sales\Entities\Tenant;
use Modules\Sales\Entities\Sales;
use Modules\Masters\Entities\TenantType;
use Modules\Sales\Entities\LandlordContract;
use Modules\Sales\Entities\TenantDocument;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Nationality;
use Modules\Masters\Entities\TenantDocs;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Occupant;
use Modules\Masters\Entities\Bank; 
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\Employee;
use Illuminate\Support\Facades\Storage;
use Image; 
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Setting;

class TenantContractController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');         
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        
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

    	//dd($request['tenant_contract_valid_to_date']);
        $tenantLatest	 = Tenant::orderBy('id','desc')->first();
        $tenantContractLatest = TenantContract::orderBy('id','desc')->latest()->first();
		$prefix 	= prefixData('tenant_prefix')->configuration_value;    
		$isExist	=	$request->isExistTenant;
		
		//if(!empty($tenantLatest))
      //    $nextCode = $prefix.str_pad($tenantLatest->id+1,4,'0',STR_PAD_LEFT);
		//else
       //   $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
          
        $prefix  = prefixData('tenant_agreement_prefix')->configuration_value;
        
     //   if(!empty($tenantContractLatest))
     //       $contractCode = $prefix.str_pad($tenantContractLatest->id+1,4,'0',STR_PAD_LEFT);
      //  else
         //   $contractCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
		$generateCode = $this->tenantContractCode();
            $contractCode = $generateCode['code'];
        $this->validate($request, [
            'building_id' => 'required',                    
            'unit_id'   => 'required',
            'tenant_document_file_name' => 'mimes:pdf,docx,jpeg,jpg,doc|max:10000',                   
            'tenant_contract_rent'   => 'required',
            'tenant_contract_start_date'   => 'required|date',
            'tenant_contract_effective_date'   => 'required|date',
            'tenant_contract_valid_to_date'   => 'required|date',
            'tenant_contract_payment_type' => 'required', 
            'mobile_no'=> (!$isExist)?'required|unique:tenant,tenant_contact_no':'required',
            'com_reg_no' => 'required_if:tenant_type_id,8',
            'resident_id' => 'required_if:tenant_type_id,7',
            'nationality_name' => 'required_if:tenant_type_id,7',
            'tenant_contract_payment_type' => 'required', 
         ]);
       
        $enquiry_id 	= $request->sales_enquiry_id;
        $tenantId		= $request->tenant_id;
        
        $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();        
        
        //empty($isExist) -  New Tenant
        
        if(empty($tenantId) && empty($isExist)){
           $firstCharacter = substr($request->tenant_name, 0, 1);
           $incVal  = prefixData('tenant_prefix')->configuration_increment_value;
           $nextCode = strtoupper($firstCharacter).TENANT_VAL.'-'.$incVal;
		   
            $tenant =  Tenant::create([
              'tenant_code' => $nextCode,
              'tenant_type_id' =>$enquiryDetails->tenant_type_id,
              'tenant_name' => $request->tenant_name,
              'tenant_contact_no'=> $request->mobile_no,
              'tenant_company_name'=>  $request->tenant_company_name, 
              'tenant_contact_person'=>  $request->tenant_contact_person, 
              'resident_id' =>$request->resident_id,
              'tenant_resident_exp_date' =>$request->tenant_resident_exp_date,
              'com_reg_no' =>$request->com_reg_no,
              'resident_id' =>$request->resident_id,
              'designation'=>  $request->designation,              
              'nationalities_id'=>$request->nationality,
              'created_by' => \Auth::user()->id,
            ]);
            $tenantId = $tenant->id;
        }
        
        $yearToMonth = ($request['yeartxt'] > 0)? $request['yeartxt']*12:0;
        $durationInMonth = $yearToMonth + $request['monthtxt'];

        if($request['pdc_check'] == 2){
          $partial_comment = $request['partial_comment'];
        }else{
          $partial_comment = '';
        }


        $tenantContract =  TenantContract::create([
          'tenant_id'=>$tenantId,
          'building_id' =>$request['building_id'],
          'unit_id' => $request['unit_id'],
          'tenant_contract_no'=>$contractCode,
          'tenant_contract_duration_type' => $request['tenant_contract_duration_type'],
          'tenant_contract_duration' => $durationInMonth,
          'tenant_contract_rent' => $request['tenant_contract_rent'],
          'tenant_contract_start_date' => $request['tenant_contract_start_date'],
          'tenant_contract_effective_date' =>  $request['tenant_contract_effective_date'],
          'tenant_contract_valid_to_date' =>  $request['tenant_contract_valid_to_date'],
          'tenant_contract_payment_type' =>  $request['tenant_contract_payment_type'],
          'sale_enquiry_id'=> $request['sales_enquiry_id'],
          'tenant_contract_note'=> $request['tenant_contract_note'],
          'work_flow_processes_code'=>$request['stage'],
          'pdc_check'=> $request['pdc_check'],
          'unit_usage'=> $request['unit_usage'],
          'tenant_marketing_executive'=>$request['tenant_marketing_executive'],
          'tenant_contract_duration_countdown' => $request['yeartxt'].'-'.$request['monthtxt'].'-'.$request['daytxt'],
          'tenant_contract_value' => $request['tenant_contract_value'],
          'deposit_check'=> $request['deposit_check'],
          'partial_comment'=> $partial_comment,
          'created_by' => \Auth::user()->id,
        ]);
		Setting::where('configuration_settings','tenant_prefix')->update(['configuration_increment_value'=> $incVal + 1
        ]);

        if(!empty($request->file('tenant_document_file_name'))):
            $files = $request->file('tenant_document_file_name');

                $uniqueFileName = $files->getClientOriginalName() ;
                $doc_path = Storage::putFile('public/Document',$files);

                $tenantDocument =  TenantDocument::create([
                  'tenant_documents_name' =>  $uniqueFileName,
                  'tenant_documents_file_name' => $doc_path,
                  'tenant_documents_status' => 1,
                  'tenant_contract_id'=>$tenantContract->id,
                  'created_by' => \Auth::user()->id,
                ]);
        endif;
		if(!empty($request->file('tenant_visitingcard_file_name'))):
            $files = $request->file('tenant_visitingcard_file_name');

                $uniqueFileName = $files->getClientOriginalName() ;
                $doc_path = Storage::putFile('public/TenantDocs',$files);

                $tenantDocument =  TenantDocs::create([
                  'tenant_id' => $tenantId,
                  'tenant_doc_category' =>  'visiting_card',
                  'tenant_doc_name' =>  $uniqueFileName,
                  'tenant_doc_path_name' => $doc_path,
                  'created_by' => \Auth::user()->id,
                ]);
        endif;

        if(!empty($request->file('tenant_other_file_name'))):
            $other_files = $request->file('tenant_other_file_name');

                $otherUniqueFileName = $other_files->getClientOriginalName() ;
                $other_doc_path = Storage::putFile('public/TenantDocs',$other_files);

                $tenantDocument =  TenantDocs::create([
                  'tenant_id' => $tenantId,
                  'tenant_doc_category' =>  'others',
                  'tenant_doc_name' =>  $otherUniqueFileName,
                  'tenant_doc_path_name' => $other_doc_path,
                  'created_by' => \Auth::user()->id,
                ]);
        endif;
        $stage = $request['stage'];
        Setting::where('configuration_settings','tenant_agreement_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);
        session()->flash('success', 'Tenant Contract Created Successfully');
        return redirect()->route('leadAssign.nextStage',['id'=>$enquiry_id,'stage'=>$stage]);
       
    }
    /**
     * Show the specified resource.
     * @return Response
     */
    public function show(TenantContract $tenantContract)
    {
	
        return view('sales::TenantSales.tenant_view_documentation_modal',compact('tenantContract'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(TenantContract $tenantContract)
    {
		$employeeList = 0;
        $tenants = Tenant::get(); 
        $enquiry_details = SalesEnquiry::where('id',$tenantContract->sale_enquiry_id)->first();
        $stage = $enquiry_details->work_flow_processes_code;
        $buildings = Building::where('building_status',1)->get();
        $units = Unit::where('unit_status',1)->get();
        $nationalities = Nationality::get();
        $building = Building::with('buildingType')->where('id',$tenantContract->building_id)->first();
		$tenantTypes = TenantType::get();
		$rent = $this->contractRentCountCalculation($tenantContract->tenant_contract_start_date,$tenantContract->tenant_contract_valid_to_date, $tenantContract->tenant_contract_rent);

        $duration = $this->dateDuration($tenantContract->tenant_contract_start_date,$tenantContract->tenant_contract_valid_to_date);
        
        $buildingTypeName = $building->buildingType->building_types_name;
       
        if($tenantContract->tenant_marketing_executive){ // In Direct Flow
			$employee     = User::role('sales_person')->with('employee')->where('id',$tenantContract->tenant_marketing_executive)->first();
		
		}	
        elseif($enquiry_details->assigned_person){ // In stage Flow
			
			$employee     = User::role('sales_person')->with('employee')->where('id',$enquiry_details->assigned_person)->first();
			
		}else{ // User need to select the maketing Executive
			$employeeList = User::role('sales_person')->with('employee')->orderBy('id', 'DESC')->get();
		}	
		// Valid Start date with Landlord contract valid from and Valid To
		$landlordContractInfo = LandlordContract::where('building_id',$tenantContract->building_id)->orderBy('id','desc')->first();
    //dd($landlordContractInfo )    ;
    $fromContract = $landlordContractInfo->landlord_contract_valid_from_date->format('Y-m-d');

    $pastTwoMonthDt = date('Y-m-d', strtotime("-2 month", strtotime(date('Y-m-d'))));
        $year    = prefixData('tenant_agreement_prefix')->configuration_year;

         $isYearCorrect = (date('y') == $year)?true:false;
        if($fromContract < $pastTwoMonthDt){
          $fromContract = $pastTwoMonthDt;
        }
		if(isset($landlordContractInfo->landlord_contract_valid_to_date))
			$toContract   =  $landlordContractInfo->landlord_contract_valid_to_date->format('Y-m-d');
		else
			$toContract   = null;
			
		$upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
        return view('sales::TenantSales.tenant_documentation_modal',compact('tenantContract','tenants','buildings','units','stage','nationalities','enquiry_details','buildingTypeName','employee','employeeList','rent','duration','fromContract','toContract','tenantTypes','upload_size','landlordContractInfo','isYearCorrect'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,TenantContract $tenantContract)
    {
      //dd($request->all());
	   // Check exist Tenant or not	
	   $isExist	=	$request->isExistTenant;
	   	
	   $this->validate($request, [
            'building_id' => 'required',                    
            'unit_id'   => 'required',
            'tenant_document_file_name' => 'mimes:pdf,docx,jpeg,jpg,doc|max:10000',                   
            'tenant_contract_rent'   => 'required',
            'tenant_contract_start_date'   => 'required|date',
            'tenant_contract_effective_date'   => 'required|date',
            'tenant_contract_valid_to_date'   => 'required|date',
            'tenant_contract_payment_type' => 'required', 
            'mobile_no'=> (!$isExist)?'required|unique:tenant,tenant_contact_no':'required',
            'com_reg_no' => 'required_if:tenant_type_id,8',
            'resident_id' => 'required_if:tenant_type_id,7',
            
            'tenant_contract_payment_type' => 'required', 
           
         ]);
		
      
       //dd( $tenant->id);
        $enquiry_id = $request->sales_enquiry_id;
        $tenantId = $request->tenant_id;
        $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();
		$tenantLatest	 = Tenant::orderBy('id','desc')->first();
        $prefix  = prefixData('tenant_prefix')->configuration_value; 
       
		//if(!empty($tenantLatest))
		//  $nextCode = $prefix.str_pad($tenantLatest->id+1,4,'0',STR_PAD_LEFT);
		//else
		//  $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
		
        if(empty($tenantId)){
			$firstCharacter = substr($request->tenant_name, 0, 1);
          $incVal  = prefixData('tenant_prefix')->configuration_increment_value;
          $nextCode = strtoupper($firstCharacter).TENANT_VAL.'-'.$incVal;


            $tenant =  Tenant::create([
              'tenant_code' => $nextCode,
              'tenant_type_id' =>$request->tenant_type_id,
              'tenant_name' => $request->tenant_name,
              'tenant_contact_no'=> $request->mobile_no,
              'tenant_company_name'=>  $request->tenant_company_name, 
              'tenant_contact_person'=>  $request->tenant_contact_person, 
              'resident_id' =>$request->resident_id,
              'tenant_resident_exp_date' =>$request->tenant_resident_exp_date,
              'com_reg_no' =>$request->com_reg_no,
              'resident_id' =>$request->resident_id,
              'designation'=>  $request->designation,              
              'nationalities_id'=>$request->nationality,
              'created_by' => \Auth::user()->id,
            ]);
            $tenantId = $tenant->id;
			Setting::where('configuration_settings','tenant_prefix')->update(['configuration_increment_value'=> $incVal + 1
        ]);
        }
        if($request['pdc_check'] == 2){
          $partial_comment = $request['partial_comment'];
        }else{
          $partial_comment = '';
        }
        $yearToMonth = ($request['yeartxt'] > 0)? $request['yeartxt']*12:0;
        $durationInMonth = $yearToMonth + $request['monthtxt'];
        $tenantContract->update([
          'tenant_id'=>$tenantId,
          'building_id' =>$request['building_id'],
          'unit_id' => $request['unit_id'],
          'tenant_contract_duration_type' => $request['tenant_contract_duration_type'],
          'tenant_contract_duration' => $durationInMonth,
          'tenant_contract_rent' => $request['tenant_contract_rent'],
          'tenant_contract_start_date' => $request['tenant_contract_start_date'],
          'tenant_contract_effective_date' =>  $request['tenant_contract_effective_date'],
          'tenant_contract_valid_to_date' =>  $request['tenant_contract_valid_to_date'],
          'tenant_contract_payment_type' =>  $request['tenant_contract_payment_type'],
          'sale_enquiry_id'=> $request['sales_enquiry_id'],
          'tenant_contract_note'=> $request['tenant_contract_note'],
          'work_flow_processes_code'=>$request['stage'],
          'pdc_check'=> $request['pdc_check'],
          'unit_usage'=> $request['unit_usage'],
          'tenant_contract_duration_countdown' => $request['yeartxt'].'-'.$request['monthtxt'].'-'.$request['daytxt'],
          'tenant_contract_value' => $request['tenant_contract_value'],
          'deposit_check'=> $request['deposit_check'],
          'tenant_marketing_executive'=>$request['tenant_marketing_executive'],
          'pdc_check' => $request['pdc_check'],
          'partial_comment'=> $partial_comment,
          'updated_by' => \Auth::user()->id, 
        ]);
       
        $documents = $request->file('tenant_document_file_name');
        if(!empty($documents)) {

              $uniqueFileName = $documents->getClientOriginalName();
              $img_path =    Storage::putFile('public/Document', $documents);
              $tenantDocument =  TenantDocument::create([
                  'tenant_documents_name' =>  $uniqueFileName,
                  'tenant_documents_file_name' => $img_path,
                  'tenant_documents_status' => 1,
                  'tenant_contract_id'=>$tenantContract->id,
                  'created_by' => \Auth::user()->id,
                ]);
            
		}
		if(!empty($request->file('tenant_visitingcard_file_name'))){
            $files = $request->file('tenant_visitingcard_file_name');

                $uniqueFileName = $files->getClientOriginalName() ;
                $doc_path = Storage::putFile('public/TenantDocs',$files);

                $tenantDocument =  TenantDocs::create([
                  'tenant_id' => $tenantId,
                  'tenant_doc_category' =>  'visiting_card',
                  'tenant_doc_name' =>  $uniqueFileName,
                  'tenant_doc_path_name' => $doc_path,
                  'created_by' => \Auth::user()->id,
                ]);
        }
        if(!empty($request->file('tenant_other_file_name'))){
            $other_files = $request->file('tenant_other_file_name');

                $otherUniqueFileName = $other_files->getClientOriginalName() ;
                $doc_path = Storage::putFile('public/TenantDocs',$other_files);

                $tenantDocument =  TenantDocs::create([
                  'tenant_id' => $tenantId,
                  'tenant_doc_category' =>  'others',
                  'tenant_doc_name' =>  $otherUniqueFileName,
                  'tenant_doc_path_name' => $doc_path,
                  'created_by' => \Auth::user()->id,
                ]);
        }
        $stage = $request['stage'];
        session()->flash('success', 'Documentation Updated Successfully');
        return redirect()->route('leadAssign.nextStage',['id'=>$enquiry_id,'stage'=>$stage]);
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
     * get all requested data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getContractData()
    {
$contractData['tenant_contract_no'] = request('tenant_contract_no');
        $contractData['building_id'] = request('building_id');
        $contractData['unit_id'] = request('unit_id');
        /*$contractData['tenant_contract_address'] = request('tenant_contract_address'); 
        $contractData['tenant_contract_no_members'] = request('tenant_contract_no_members');*/ 
        $contractData['tenant_contract_duration'] = request('tenant_contract_duration'); 
        $contractData['tenant_contract_duration_type'] = request('tenant_contract_duration_type');       
        $contractData['tenant_contract_rent'] = replaceCommaWithDot(request('tenant_contract_rent')); 
        $contractData['tenant_contract_muncipality_agr_no'] = request('tenant_contract_muncipality_agr_no');
        $contractData['tenant_contract_last_paid_date'] = request('tenant_contract_last_paid_date');
        $contractData['tenant_contract_last_paid_amt'] = replaceCommaWithDot(request('tenant_contract_last_paid_amt'));
        $contractData['tenant_contract_guarantee_cheque_details'] = replaceCommaWithDot(request('tenant_contract_guarantee_cheque_details'));
        $contractData['tenant_contract_electric_water'] = request('tenant_contract_electric_water');
        $contractData['tenant_contract_agreement_amt'] = replaceCommaWithDot(request('tenant_contract_agreement_amt'));
        $contractData['tenant_contract_start_date'] = request('tenant_contract_start_date');
        $contractData['tenant_contract_effective_date'] = request('tenant_contract_effective_date');
        /*$contractData['tenant_contract_valid_from_date'] = request('tenant_contract_valid_from_date');*/
        $contractData['tenant_contract_valid_to_date'] = request('tenant_contract_valid_to_date');
        $contractData['tenant_contract_payment_type'] = request('tenant_contract_payment_type');
        $contractData['tenant_contract_registered_in'] = request('tenant_contract_registered_in');
        $contractData['tenant_contract_deposit_amt'] = replaceCommaWithDot(request('tenant_contract_deposit_amt'));
        $contractData['tenant_contract_receipt_no'] = request('tenant_contract_receipt_no');
        $contractData['tenant_contract_receipt_amt'] = replaceCommaWithDot(request('tenant_contract_receipt_amt'));
        $contractData['tenant_contract_receipt_date'] = request('tenant_contract_receipt_date');
        $contractData['tenant_contract_note'] = request('tenant_contract_note');
        $contractData['tenant_contract_registered_date'] = request('tenant_contract_registered_date');
        $contractData['tenant_contract_receipt_amount'] = replaceCommaWithDot(request('tenant_contract_receipt_amount'));
      
        return $contractData;
    }
     /**
     * get all requested data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getTenantData()
    {

        $tenantData['tenant_name'] = request('tenant_name');
        $tenantData['tenant_contact_address'] = request('tenant_contact_address');
        $tenantData['tenant_contact_no'] = request('tenant_contact_no');
        $tenantData['tenant_contact_person'] = request('tenant_contact_person'); 
        $tenantData['tenant_contact_email'] = request('tenant_contact_email'); 
        $tenantData['tenant_secondary_address'] = request('tenant_secondary_address'); 
        $tenantData['tenant_pc'] = request('tenant_pc');       
        $tenantData['location_id'] = request('location_id'); 
        $tenantData['tenant_fax_no'] = request('tenant_fax_no');
        $tenantData['tenant_acc_no'] = request('tenant_acc_no');
        $tenantData['tenant_company_name'] = request('tenant_company_name');
        $tenantData['nationalities_id'] = request('nationality');
        $tenantData['bank_id'] = request('bank_id');

        return $tenantData;
    }
    /*
    *
    * contract Doument modal
    *
    *
    */
    public function contractDocumentation($id) {

        $sales_id = $id;
        $enquiry = Sales::where('id','=',$sales_id)->first();
        $enquiry_id = $enquiry->sales_enquiry_id;
        $enquiry_details = SalesEnquiry::where('id',$enquiry_id)->first();
        $stage = $enquiry->work_flow_processes_code;
        $tenants = Tenant::get();
        $buildings = Building::where('building_status',1)->get();
        $units = Unit::where('unit_status',1)->get();
        $nationalities = Nationality::get();
        $buildingTypeName = ''; 
		$employee = null;
		$employeeList = null;
        $tenantTypes = TenantType::get();
        if($enquiry_details->assigned_person){
			$employee     = User::where('id',$enquiry_details->assigned_person)->with('employee')->orderBy('id', 'DESC')->first();
			
		}else{
			$employeeList = User::role('sales_person')->with('employee')->orderBy('id', 'DESC')->get();
			
		}
    $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
        return view('sales::TenantSales.tenant_documentation_modal',compact('sales_id','enquiry_id','tenants','buildings','units','stage','nationalities','enquiry_details','buildingTypeName','employee','employeeList','tenantTypes','upload_size'));
    }
    /*
    *
    *
    * Contract Create/ modify  Final 
    *
    */
    public function contractCreation($id,$stage) {

		$tenantContract = TenantContract::where('id',$id)->first();
		$getBuilding = $tenantContract->building_id;
		$vaccant_units = Unit::where('building_id',$getBuilding)->where('unit_vaccant_status',0)->get();
		$employeeList = null;
		$enquiry_details = SalesEnquiry::where('id',$tenantContract->sale_enquiry_id)->first();
		$rent = $this->contractRentCountCalculation($tenantContract->tenant_contract_start_date,$tenantContract->tenant_contract_valid_to_date, $tenantContract->tenant_contract_rent);

		$duration = $this->dateDuration($tenantContract->tenant_contract_start_date,$tenantContract->tenant_contract_valid_to_date);
		$banks = Bank::get();
		$locations = Location::get();

		$landlordContractInfo = LandlordContract::where('building_id',$tenantContract->building_id)->orderBy('id','desc')->first();

		if(isset($landlordContractInfo->landlord_contract_valid_from_date))
				$fromContract = $landlordContractInfo->landlord_contract_valid_from_date->format('Y-m-d');
		  else
			  $fromContract   = null;

		$pastTwoMonthDt = date('Y-m-d', strtotime("-2 month", strtotime(date('Y-m-d'))));

		if($fromContract < $pastTwoMonthDt){
		  $fromContract = $pastTwoMonthDt;
		}
		if(isset($landlordContractInfo->landlord_contract_valid_to_date))
			$toContract   =  $landlordContractInfo->landlord_contract_valid_to_date->format('Y-m-d');
		else
			$toContract   = null;
      
     		
      if($enquiry_details->assigned_person)
			$employee     = User::where('id',$enquiry_details->assigned_person)->with('employee')->orderBy('id', 'DESC')->first();
		else
			 $employeeList     = Employee::whereHas('user',function ($query){
            $query->role('sales_person');
        })->orderBy('id', 'DESC')->get();
      $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
      return view('sales::TenantSales.tenant_contract_create',compact('tenantContract','stage','banks','locations','employee','employeeList','rent','duration','vaccant_units','fromContract','toContract','upload_size'));

    }
    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function contractStore(Request $request,TenantContract $tenantContract)
    {
	
      $tenantLatest = Tenant::orderBy('id', 'desc')->first();
      $tenantContractLatest = TenantContract::orderBy('id', 'desc')->first();
		
      $prefix  = prefixData('tenant_agreement_prefix')->configuration_value; 
	  
          
    //  if(!empty($tenantContractLatest))
    //      $contractCode = $prefix.str_pad($tenantContractLatest->id+1,4,'0',STR_PAD_LEFT);
    //  else
     //     $contractCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
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
        $enquiry_id     = $request['sales_enquiry_id'];
        $tenantId       = $request['tenant_id'];
        $workflow_id    = $request['workflow_id'];
        $documents      = $request->file('tenant_document_file_name');
        $enquiryDetails = SalesEnquiry::where('id','=',$enquiry_id)->first();        
        $occupant_id    = $request['occupant_id'];
        //dd( $img_path);
        $data = $this->getContractData();
        $tenantData = $this->getTenantData();
        if(isset($occupant_id)){
          $occupant =  Occupant::where('id',$occupant_id)->update([
              'occupant_name' => $request['occupant_name'],
              'occupant_primary_contact_no' =>$request['occupant_primary_contact_no'],
              'occupant_email' => $request['occupant_email'],
              'updated_by' => \Auth::user()->id,
            ]);

        }
        elseif(!empty($request['occupant_name'])){

          $occupant =  Occupant::create([
              'occupant_name' => $request['occupant_name'],
              'occupant_primary_contact_no' =>$request['occupant_primary_contact_no'],
              'occupant_email' => $request['occupant_email'],
              'created_by' => \Auth::user()->id,
            ]);
            
            $occupant_id = $occupant->id;
            $tenantContract->update([
              'occupant_id'=>$occupant_id,
            ]);
        }
        $yearToMonth = ($request['yeartxt'] > 0)? $request['yeartxt']*12:0;
        $durationInMonth = $yearToMonth + $request['monthtxt'];
        
        $data['tenant_id'] = $tenantId;
        $data['sale_enquiry_id'] = $request['sales_enquiry_id'];
        $data['work_flow_processes_code'] = $workflow_id;
        $data['tenant_contract_duration'] = $durationInMonth;
        $data['unit_usage'] = $request['unit_usage'];
        $data['tenant_marketing_executive']=$request['tenant_marketing_executive'];       
        $data['tenant_contract_duration_countdown'] = $request['yeartxt'].'-'.$request['monthtxt'].'-'.$request['daytxt'];
        $data['tenant_contract_value'] = str_replace(',', '', $request['tenant_contract_value']);
        $data['updated_by'] = \Auth::user()->id;
        $tenantContract->update($data);
        
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
        
		if(!empty($request->file('tenant_visitingcard_file_name'))):
            $files = $request->file('tenant_visitingcard_file_name');

                $uniqueFileName = $files->getClientOriginalName() ;
                $doc_path = Storage::putFile('public/TenantDocs',$files);

                $tenantDocument =  TenantDocs::create([
                  'tenant_id' => $tenantId,
                  'tenant_doc_category' =>  'visiting_card',
                  'tenant_doc_name' =>  $uniqueFileName,
                  'tenant_doc_path_name' => $doc_path,
                  'created_by' => \Auth::user()->id,
                ]);
        endif;

        if(!empty($request->file('tenant_other_file_name'))):
            $other_files = $request->file('tenant_other_file_name');

                $otherUniqueFileName = $other_files->getClientOriginalName() ;
                $other_doc_path = Storage::putFile('public/TenantDocs',$other_files);

                $tenantDocument =  TenantDocs::create([
                  'tenant_id' => $tenantId,
                  'tenant_doc_category' =>  'others',
                  'tenant_doc_name' =>  $otherUniqueFileName,
                  'tenant_doc_path_name' => $other_doc_path,
                  'created_by' => \Auth::user()->id,
                ]);
        endif;
        
        
        session()->flash('success', 'Tenant Contract Created Successfully');
        return redirect()->route('leadAssign.nextStage',['id'=>$enquiry_id,'stage'=>$workflow_id]);
    }
    /*
    *
    *
    * Building By Unit
    *
    */
    public function buildingByUnit(Request $request){

       
       /*$id = $request->input('id');
       $units = Unit::where('building_id',$id)->where('unit_vaccant_status',0)
                ->get();*/
        $id = $request->input('id');
        $landlordContractInfo = LandlordContract::active()->where('building_id',$id)->orderBy('id','desc')->first();
       // dd($landlordContractInfo);
        
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
    //dd($fromContract);
    //**** *******//
     //dd($fromContract);
       /* $pastTwoMonthDt = date('Y-m-d', strtotime("-3 month", strtotime(date('Y-m-d'))));

       
        
        if($fromContract < $pastTwoMonthDt){
          $fromContract = $pastTwoMonthDt;
        }*/
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

          $units['buildUnit'] = Unit::where('building_id',$id)->where('unit_vaccant_status',0)->orderBy('unit_no','asc')
                ->get();
        }
        $units['contractValid'] =  array($fromContract, $toContract);
  
       return json_encode($units);

    }
    /*
    *
    *
    * Building By Unit
    *
    */
    public function buildingByUnitOccuiped(Request $request){

       $id = $request->input('id'); 
       $units = Unit::where('building_id',$id)->where('unit_vaccant_status',1)->orderBy('unit_no', 'asc')->get();

  
       return json_encode($units);

    }
     /*
    *
    *
    * Building By Unit
    *
    */
    public function buildingByUnitWithoutCheckVaccant(Request $request){

       $id = $request->input('id'); 
       $units['buildUnit']  = Unit::where('building_id',$id)->orderBy('unit_no', 'asc')->get();
  
       return json_encode($units);

    }
    /*
    *
    *
    * Building Details
    *
    */
    public function buildingDetail(Request $request){

      $id = $request->input('id'); 
      $building = Building::with('buildingType')->where('id',$id)->first();

      $buildingTypeName = $building->buildingType->building_types_name;
       return json_encode($buildingTypeName);

    }
    /*
    *
    *
    * Unit Details
    *
    */
    public function unitDetail(Request $request){

      $id = $request->input('id'); 
      $unit = Unit::with('unit')->where('id',$id)->first();

      $unitTypeName = $unit->unit->unit_types_name;
       return json_encode($unitTypeName);

    }
    /*
    *
    *
    * Building Details
    *
    */
    public function getBuildingDetail(Request $request){

       $id = $request->input('id'); 
       $building = Building::with('buildingType')->where('id',$id)->first();
       //dd($id);
      //$buildingTypeName = $building->buildingType->building_types_name;
       return json_encode($building);

    }
    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function contractDestroy($id)
    {
      
      $TenantContract = TenantContract::find($id);
      $workflow = SalesEnquiry::where('id',$TenantContract->sale_enquiry_id)->first();
      $workflowId = $workflow->work_flow_processes_code;
      $TenantDocument = TenantDocument::where('tenant_contract_id',$TenantContract->id)->where('tenant_documents_status',1)->first();
      if($TenantDocument){
        Storage::delete($TenantDocument->tenant_documents_file_name);
        $TenantDocument->delete();
      }
     
      $TenantContract->delete();

      /*try {
                                
          Storage::delete($TenantDocument->tenant_documents_file_name);
          $TenantDocument->delete();
          $TenantContract->delete();  

          session()->flash('success', 'Document Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }*/
        // Log
        activity('Deleted Contract preliminary Document')
          ->performedOn($TenantContract)
          ->causedBy(\Auth::user()->id)
          ->withProperties($TenantContract)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        return redirect()->route('leadAssign.nextStage',['id'=>$TenantContract->sale_enquiry_id,'stage'=>$workflowId]);
    }
    /*
    *
    *
    * nationality Details
    *
    *
    */
    public function nationalityDetails(Request $request) {

       $id = $request->input('id'); 

       $Nationality = Nationality::where('nationalityid',$id)->first();

       //$buildingTypeName = $building->buildingType->building_types_name;
       return json_encode($Nationality);
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
		$sumOfStartDays = $sumOfMonthRent = 0;
		$sumOfEndDays = 0;
		$sumOfSameStartEnd = 0;
		$monthIsOne = 1;
		
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
    public function tenantContractCode(){

      $prefix  = prefixData('tenant_agreement_prefix')->configuration_value.prefixData('tenant_agreement_prefix')->configuration_year;
     
      $incVal  = prefixData('tenant_agreement_prefix')->configuration_increment_value;
     
      $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);
     
      return array('code'=>$nextCode,'inc'=>$incVal);

    }


}
