<?php

namespace Modules\Sales\Http\Controllers;

use Modules\Sales\Entities\TenantSequence;
use Modules\Sales\Entities\TenantContract;
use Modules\Sales\Entities\Tenant;
use Modules\Sales\Entities\Sales;
use Modules\Sales\Entities\TenantDocument;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\TenantType;
use Modules\Masters\Entities\Bank;
use Illuminate\Support\Facades\Storage;
use Modules\Masters\Entities\Nationality;
use Modules\Masters\Entities\Designation;
use Modules\Masters\Entities\TenantDocs;
use Image;
use DB;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\Sales\Emails\TenantBirthdayEmail;
use Modules\Sales\Emails\AreReminderEmail;
use Dynamics;
use Exception;

class TenantController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');    
    $this->middleware('permission:tenant_edit', ['only' => ['edit','update']]);    
    $this->middleware('permission:tenant_list', ['only' => ['index','show']]);   
    $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value;      
  }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
      $fieldName = $request->fieldName;
      $fieldValue = $request->fieldValue;
      
      $tenants =  Tenant::when($fieldValue, function ($query) use($fieldValue,$fieldName){
        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
      })->sortable()->paginate($this->noOfRecord);
      
      $fields = [
      'tenant_code' => 'Code',  
      'tenant_name' => 'Name',
      'tenant_contact_no' => 'Mobile No',              
      ];
      $request->flash();
      return view('masters::Tenant.list',compact('tenants','fields','request'));
      
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      
      $tenantTypes = TenantType::get();
      $locations = Location::get();
      $banks = Bank::get();
      $nationalities = Nationality::get();
      $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
      return view('masters::Tenant.add_edit',compact('tenantTypes','locations','banks','nationalities','upload_size'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
      $this->validate($request, [                  
        'tenant_name'   => 'required',
        'tenant_contact_no'   => 'required|unique:tenant',
/*            'location_id' => 'required' ,
'nationality'=>'required' ,*/
]);
      $tenantLatest = Tenant::latest()->first();


      $firstCharacter = substr($request['tenant_name'], 0, 1);
      $incVal = TenantSequence::where('tenant_prefix',strtoupper($firstCharacter))->first()->configuration_increment_value;      
      $nextCode = strtoupper($firstCharacter).TENANT_VAL.'-'.$incVal;
      

      TenantSequence::where('tenant_prefix',strtoupper($firstCharacter))->update(['configuration_increment_value'=> $incVal + 1
        ]);

      
      $tenant = Tenant::create([
        'tenant_name' => $request['tenant_name'],
        'tenant_code' => $nextCode,
        'resident_id' => $request['resident_id'],
        'tenant_type_id' => $request['tenant_type_id'],
        'tenant_contact_address' => $request['tenant_contact_address'],
        'tenant_secondary_address' => $request['tenant_secondary_address'],
        'tenant_pc' => $request['tenant_pc'],
        'location_id' => $request['location_id'],
        'tenant_contact_no' => $request['tenant_contact_no'],
        'tenant_contact_email' => $request['tenant_contact_email'],
        'tenant_fax_no' => $request['tenant_fax_no'],
        'tenant_acc_no' => $request['tenant_acc_no'],
        'tenant_contact_person' => $request['tenant_contact_person'],
        'bank_id' => $request['bank_id'],
        'gsm_no' => $request['gsm_no'],
        'passport_no' => $request['passport_no'],
        'com_reg_no' => $request['com_reg_no'],
        'tenant_company_name' => $request['tenant_company_name'],
        'nationalities_id' => $request['nationality'],

        'tenant_resident_exp_date' => $request['tenant_resident_exp_date'],
        'tenant_gender' => $request['tenant_gender'],
        'tenant_date_of_birth' => $request['tenant_date_of_birth'],
        'tenant_employer_name' => $request['tenant_employer_name'],
        'designation' => $request['designation'],
        'tenant_residence_tel' => $request['tenant_residence_tel'],
        'tenant_personal_email' => $request['tenant_personal_email'],
        'tenant_ice_name' => $request['tenant_ice_name'],
        'tenant_ice_contact_no' => $request['tenant_ice_contact_no'],
        'tenant_post_box' => $request['tenant_post_box'],

        'created_by' => \Auth::user()->id,
        ]);
      if(!empty($request->file('tenant_doc_path_name'))):
        $files = $request->file('tenant_doc_path_name');
      foreach($files as $key => $file):

        $uniqueFileName = $file->getClientOriginalName() ;
      $doc_path = Storage::putFile('public/TenantDocs',$request['tenant_doc_path_name'][$key]);

      $docs=TenantDocs::create(['tenant_id'=>$tenant->id,
        'tenant_doc_category'=>$request['tenant_doc_category'][$key],
        'tenant_doc_path_name'=>$doc_path,
        'tenant_doc_name'=>$uniqueFileName,
        'created_by' => \Auth::user()->id]);
      endforeach;
      endif;

      TenantSequence::where('tenant_prefix',strtoupper($firstCharacter))->update(['configuration_increment_value'=> $incVal + 1
        ]);
      
      session()->flash('success', 'Tenant Created Successfully');
      return redirect()->route('tenants.index');
      
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
      try{
        $tenant = Tenant::where('id',$id)->first();
        //dd($tenant);
        $contracts = TenantContract::where('tenant_id',$id)->paginate(15);
        $docs = TenantDocs::where('tenant_id',$tenant->id)->get();
        $doc_category = array('Resident Card','Passport','CR','Others');
        return view('masters::Tenant.view',compact('tenant','contracts','docs','doc_category'));

      }
      catch(\Exception $e){
        return $e->getMessage();
      }
      
      
         
      
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Tenant $tenant)
    {
      $previousUrl = url()->previous();
      $tenantTypes = TenantType::get();
      $locations = Location::get();
      $banks = Bank::get();
      $nationalities = Nationality::get();
      $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;
      return view('masters::Tenant.add_edit',compact('tenant','tenantTypes','locations','banks','nationalities','previousUrl','upload_size'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Tenant $tenant)
    {   
     
      $this->validate($request, [                  
        'tenant_name'   => 'required',
        'tenant_contact_no'   => 'required',

/*		        'location_id' => 'required',
'nationality'=>'required' ,*/
]);

    if($request['tenant_type_id'] ==1){
        $resident_id = $request['resident_id'];
        $com_reg_no = NULL;
    }else{
        $resident_id = NULL;
        $com_reg_no =  $request['com_reg_no'];
	}

      
      $tenant->update([
        'tenant_name' => $request['tenant_name'],
        'resident_id' => $resident_id,
        'tenant_type_id' => $request['tenant_type_id'],
        'tenant_contact_address' => $request['tenant_contact_address'],
        'tenant_secondary_address' => $request['tenant_secondary_address'],
        'tenant_pc' => $request['tenant_pc'],
        'location_id' => $request['location_id'],
        'tenant_contact_no' => $request['tenant_contact_no'],
        'tenant_contact_email' => $request['tenant_contact_email'],
        'tenant_fax_no' => $request['tenant_fax_no'],
        'tenant_acc_no' => $request['tenant_acc_no'],
        'tenant_contact_person' => $request['tenant_contact_person'],
        'bank_id' => $request['bank_id'],
        'gsm_no' => $request['gsm_no'],
        'passport_no' => $request['passport_no'],
        'com_reg_no' => $com_reg_no,
        'tenant_company_name' => $request['tenant_company_name'],
        'nationalities_id' => $request['nationality'],
        'tenant_resident_exp_date' => $request['tenant_resident_exp_date'],
        'tenant_gender' => $request['tenant_gender'],
        'tenant_date_of_birth' => $request['tenant_date_of_birth'],
        'tenant_employer_name' => $request['tenant_employer_name'],
        'designation' => $request['designation'],
        'tenant_residence_tel' => $request['tenant_residence_tel'],
        'tenant_personal_email' => $request['tenant_personal_email'],
        'tenant_ice_name' => $request['tenant_ice_name'],
        'tenant_ice_contact_no' => $request['tenant_ice_contact_no'],
        'tenant_post_box' => $request['tenant_post_box'],
        'status' => $request['Status'],
        'updated_by' => \Auth::user()->id,
        ]);
	
	   if($tenant->tenant_status==1){
		  
	   if(Dynamics::TenantIsExitAxPushData('isTenantExistFunc',$tenant)==false ){
		 	// Update to AX
			if(Dynamics::TenantAxUpdatePushData('AXTenantUpdate', $tenant)=='Error'){
				return redirect()->back()->withMessage('error', 'Microsoft Dynamics API Service Error');
											
			}
		}
	   }
      if(!empty($request->file('tenant_doc_path_name'))):
        $files = $request->file('tenant_doc_path_name');
      foreach($files as $key => $file):

        $uniqueFileName = $file->getClientOriginalName() ;
      $doc_path = Storage::putFile('public/TenantDocs',$request['tenant_doc_path_name'][$key]);

      $docs=TenantDocs::create(['tenant_id'=>$tenant->id,
        'tenant_doc_category'=>$request['tenant_doc_category'][$key],
        'tenant_doc_path_name'=>$doc_path,
        'tenant_doc_name'=>$uniqueFileName,
        'created_by' => \Auth::user()->id]);
      endforeach;
      endif;
      session()->flash('success', 'Tenant Updated Successfully');
      return redirect($request->backurl);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
      $TenantDocs = TenantDocs::find($id);
      
      Storage::delete($TenantDocs->tenant_doc_path_name);
      $TenantDocs->delete();
    }
    /*
    *
    *
    * Tenant Details
    *
    *
    */
    public function tenantDetails(Request $request) {

     $id = $request->input('id'); 
     $tenant = Tenant::where('id',$id)->first();

       //$buildingTypeName = $building->buildingType->building_types_name;
     return json_encode($tenant);
   }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id)
    {  
     $status = Tenant::where('id',$id)->first();      
     
     if($status->tenant_status == 1) {
      $status->tenant_status = 0;
    } else {
      $status->tenant_status = 1;
    } 
    $status->save();     
        // Log
    activity('Change Tenant Status')
    ->performedOn($status)
    ->causedBy(\Auth::user()->id)
    ->withProperties($status)
    ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
    
    session()->flash('success', 'Status Changed Successfully');
    return redirect()->route('tenants.index');   
  }
  
    /**
    *
    * Tenant Autocomplete
    *
    **/
    public function tenantsAutocomplete(Request $request){

     $key = $request->term;
     
     $tenants =   Tenant::where('tenant_name', 'ILIKE', '%'.$key.'%')->select('id AS ids',DB::raw("CONCAT(tenant_name,'-',tenant_code) as value"))->limit(10)->get();

     return $tenants ;


   }
    /**
    *
    * Nationality Autocomplete
    *
    **/
    public function nationalityAutocomplete(Request $request){

     $key = $request->term;
     
     $nationality =   Nationality::where('nationality', 'ILIKE', '%'.$key.'%')
     ->select('nationalityid AS ids','nationality as value')
     ->get();

     return $nationality ;


   }

     /*
    *
    *mobile no exist
    */
     public function checkMobileExist(Request $request){
      $tenant_contact_no = $request->tenant_contact_no;
      $tenant_id = $request->tenant_id;
      if(empty($tenant_id)){
        $contact_exist = Tenant::where('tenant_contact_no',$tenant_contact_no)->get();
        $count = count($contact_exist);
        return $count;
      }else{
        $contact_exist = Tenant::where('tenant_contact_no',$tenant_contact_no)->where('id',$tenant_id)->get();
        $contact_count = count($contact_exist);
       // dd($contact_count);
        if($contact_count > 0){
          $count = 0;
        }else{
          $current_contact = Tenant::where('tenant_contact_no',$tenant_contact_no)->get();
          $count = count($current_contact);
        }
        return $count;
      }
    }
    /*
    *
    *Residence ID  exist
    */
    public function checkResidenceExist(Request $request){
      $resident_id = $request->resident_id;
      //dd($resident_id);
      $tenant_id = $request->tenant_id;
      if(empty($tenant_id)){
        $resident_exist = Tenant::where('resident_id',$resident_id)->get();
        $count = count($resident_exist);
       // dd($count);
        return $count;
      }else{
        $resident_exist = Tenant::where('resident_id',$resident_id)->where('id',$tenant_id)->get();
        $resident_count = count($resident_exist);
        if($resident_count > 0){
          $count = 0;
        }else{
          $resident_contact = Tenant::where('resident_id',$resident_id)->get();
          $count = count($resident_contact);
        }
        return $count;
      }
    }
    /*
    *
    *Commercial Reg. No exist
    */
    public function checkCommercialExist(Request $request){
      $com_reg_no = $request->com_reg_no;
      $tenant_id = $request->tenant_id;
      if(empty($tenant_id)){
        $commercial_exist = Tenant::where('com_reg_no',$com_reg_no)->get();
        $count = count($commercial_exist);
        return $count;
      }else{
        $commercial_exist = Tenant::where('com_reg_no',$com_reg_no)->where('id',$tenant_id)->get();
        $commercial_count = count($commercial_exist);
        if($commercial_count > 0){
          $count = 0;
        }else{
          $commercial_contact = Tenant::where('com_reg_no',$com_reg_no)->get();
          $count = count($commercial_contact);
        }
        return $count;
      }
    }
/*
*
*Birthday Wish-Tenant
*
*
*/
    public function tenantBirthdayNotification()
    {
      $tenants = Tenant::active()->birthdays()->get();
   //    dd($tenants);
       foreach ($tenants as $key => $tenant) {
        $tenant_birthday = $tenant->tenant_date_of_birth;
        $tenant->tenant_subject = "Happy Birthday";
        $tenant->tenant_content = "Wish you a Happy Birthday!! We hope that you have a great year and accomplish all the fabulous goals you have set. May the coming years be filled with happiness, peace, and love. Have a Great day ahead.";
          $email = $tenant->tenant_personal_email ?? $tenant->tenant_contact_email;
          if(!empty($email)){
         Mail::to($email)->send(new TenantBirthdayEmail($tenant)); //Email Notification
         }
       }
    }
    /*
    *
    *ARE Reminder Residence expiry
    *
    */
    public function areReminderNotification()
    { 
      $current = Carbon::now(); 
      $futureDate = $current->addMonths(1)->format('Y-m-d');
      $today = date('Y-m-d');
     
      $tenants = Tenant::active()->leftJoin('tenant_contracts', 'tenant.id', '=', 'tenant_contracts.tenant_id')
      ->leftJoin('buildings', 'tenant_contracts.building_id', '=', 'buildings.id')
      ->leftJoin('preferred_buildings', 'buildings.id', '=', 'preferred_buildings.building_id')
      ->leftJoin('are_buildings', 'preferred_buildings.are_building_id', '=', 'are_buildings.id')
      ->leftJoin('users', 'are_buildings.user_id', '=', 'users.id')
      ->leftJoin('employees', 'users.user_type_id', '=', 'employees.id')
      ->where('preferred_buildings.assign_to','=',null)
      ->where('tenant.tenant_type_id',1)->where('tenant_contracts.tenant_contract_status',1)->whereDate('tenant.tenant_resident_exp_date','<',$today)
      ->get();  

      foreach ($tenants as $key => $tenant) {
        $tenant->tenant_subject = "Reminder";
        $tenant->tenant_content = "This is to inform you that the Residence ID Exp Date has been expired for the building ".$tenant->building_name."-".$tenant->tenant_contract_no.".";
        $email = $tenant->email;
        if(!empty($email)){
         Mail::to($email)->send(new AreReminderEmail($tenant)); //Email Notification
       }
     }
    }
  }
