<?php

namespace Modules\Masters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\AuthController as Controller;
use Modules\Masters\Entities\Vendor;

use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\VendorType;
use Modules\Masters\Entities\Bank;
use Dynamics;
class VendorController extends Controller
{
	 public function __construct()
    {
        $this->middleware('auth');  
        $this->middleware('permission:add_vendor', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_vendor', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_vendor', ['only' => ['destroy']]);   
        $this->middleware('permission:change_status_vendor', ['only' => ['changeStatus']]);         
        $this->middleware('permission:view_vendor', ['only' => ['index','show']]);                  
    }
    
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue;  
        
         
		$vendors = Vendor::when($fieldValue, function ($query) use($fieldValue,$fieldName){

                        if($fieldName == 'vendor_types_name'){
                            $query->whereHas('vendorType', function ($query) use($fieldValue,$fieldName) {
                            $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                              });
                        }else $query->where($fieldName,'ilike', '%'.$fieldValue.'%');

                         return $query;
                       })->sortable()->orderBy('id', 'desc')->paginate(15);  
                                       
       $fields = [
           'vendor_name' => 'Name',  
           'vendor_code' => 'Vendor Code',             
           'vendor_types_name' => 'Vendor Type',             
        ];
        $request->flash();  
        
                                             
     return view('masters::Vendors.list',compact('vendors','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {

       $location =  Location::active()->get();
       $vendor_type =  VendorType::active()->get();
       $bank =  Bank::get();

       $vendorLandlordIndex = Vendor::where('vendor_type_id', 2)->orderBy('id', 'DESC')->first();
       
       $vendorContractorIndex = Vendor::where('vendor_type_id', 1)->orderBy('id', 'DESC')->first();
		
       return view('masters::Vendors.add_edit',compact('location','vendor_type','bank'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {

        $vendorLandlordIndex = Vendor::where('vendor_type_id', 2)->orderBy('id', 'DESC')->first();
       
		$vendorContractorIndex = Vendor::where('vendor_type_id', 1)->orderBy('id', 'DESC')->first();

        $landlord_prefix  = prefixData('landlord_prefix')->configuration_value;  
		$contractor_prefix  = prefixData('contractor_prefix')->configuration_value; 
       /*
       if($request->vendor_type == 1){  // contractor
		   
		   if(!empty($vendorContractorIndex)){
			   		   
            
              $nextCode = $contractor_prefix.str_pad($vendorContractorIndex->vendor_index + 1,4,'0',STR_PAD_LEFT);
			  $vendorIndex = $vendorContractorIndex->vendor_index + 1;
		   }
		   else{
			   $nextCode = $contractor_prefix.str_pad(1,4,'0',STR_PAD_LEFT);
			   $vendorIndex = 1;
		   }
			
              
        }elseif($request->vendor_type == 2){ // Landlord
              
           if(!empty($vendorLandlordIndex)){
			   		   
           
              $nextCode = $landlord_prefix.str_pad($vendorLandlordIndex->vendor_index + 1,4,'0',STR_PAD_LEFT);
			  $vendorIndex = $vendorLandlordIndex->vendor_index + 1;
		   }
		   else{
			   $nextCode = $landlord_prefix.str_pad(1,4,'0',STR_PAD_LEFT);
			   $vendorIndex = 1;
		   }
              
        }
       */
        $this->validate($request, [
		  'vendor_code'=>'required', 
          'vendor_name' => 'required|max:75',           
          'vendor_contact_address' => 'required|max:250',           
          'vendor_pc' => 'required',           
          'location_id' => 'required',      
          'vendor_contact_person' => 'required' ,        
          'vendor_contact_email' => 'required|email' ,        
          'vendor_type' => 'required' ,               
          'vendor_status' => 'required',
       ]);   

		
       $data = Vendor::create([
         'vendor_code' => $request->vendor_code,
         'vendor_name' => $request->vendor_name,
         'vendor_type_id' => $request->vendor_type,
         'vendor_contact_address' => $request->vendor_contact_address,
         'vendor_secondary_address' => $request->vendor_secondary_address,
         'vendor_pc' => $request->vendor_pc,
         'location_id' => $request->location_id,
         'vendor_contact_no' => $request->vendor_contact_no,
         'vendor_contact_person' => $request->vendor_contact_person,
         'vendor_contact_email' => $request->vendor_contact_email,
         'vendor_fax_no' => $request->vendor_fax_no,
         'vendor_acc_no' => $request->vendor_acc_no,
         'vendor_status' => $request->vendor_status,
         'bank_id' => $request->bank_id,
         'created_by' =>  \Auth::user()->id        
        ]);  
		
		if($request->vendor_type == 1){	
			$vendorInfo  = Vendor::where('id',$data->id)->first();
			if( AX_ENABLE_DISABLE ==1){
			// Isvendor Exit or not in AX
			if(Dynamics::VendorIsExitAxPushData('isVendorExistFunc',$vendorInfo)==false){
						// Update to AX
				if(Dynamics::VendorAxPushData('AXVendor', $vendorInfo)=='Error')
					return Redirect::back()->withMessage('error', 'Microsoft Dynamics API Service Error');
			}
			}
		}
	  // Log
	  $logName = ($request->vendor_type == 1)?'Add Contractor':'Add Landlord';
	  activity($logName)
          ->performedOn($data)
          ->causedBy(\Auth::user()->id)
          ->withProperties($data)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
      session()->flash('success', ' Vendor Added ');
      return redirect()->route('vendors.index');     
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show(Vendor $vendor)
    {
         return view('masters::Vendors.view',compact('vendor'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,Vendor $vendor)
    {   
		$back = null;
	   if($request->back)
	        $back = $request->back;
	   $previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'vendors.index';
		     
       $location =  Location::active()->get();
       $vendor_type =  VendorType::active()->get();
       $bank =  Bank::get();

       return view('masters::Vendors.add_edit',compact('location','vendor_type','bank','vendor','back','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Vendor $vendor)
    {
		
		if(isset($request->backurl))
			$url = $request->backurl;
			
         $this->validate($request, [
		  'vendor_code' => 'required',   
          'vendor_name' => 'required|max:75',           
          'vendor_contact_address' => 'required|max:250',           
          'vendor_pc' => 'required',           
          'location_id' => 'required',      
          'vendor_contact_person' => 'required' ,        
          'vendor_contact_email' => 'required|email' ,        
          'vendor_type' => 'required' ,               
          'vendor_status' => 'required',
       ]);  
		
        $vendor->update([       
		 'vendor_code' => $request->vendor_code,
         'vendor_name' => $request->vendor_name,
         'vendor_type_id' => $request->vendor_type,
         'vendor_contact_address' => $request->vendor_contact_address,
         'vendor_secondary_address' => $request->vendor_secondary_address,
         'vendor_pc' => $request->vendor_pc,
         'location_id' => $request->location_id,
         'vendor_contact_no' => $request->vendor_contact_no,
         'vendor_contact_person' => $request->vendor_contact_person,
         'vendor_contact_email' => $request->vendor_contact_email,
         'vendor_fax_no' => $request->vendor_fax_no,
         'vendor_acc_no' => $request->vendor_acc_no,
         'vendor_status' => $request->vendor_status,
         'bank_id' => $request->bank_id,  
         'updated_by' =>  \Auth::user()->id,      
        ]);  

		// log
	  $logName = ($request->vendor_type == 1)?'Update Contractor':'Update Landlord';
	  activity($logName)
          ->performedOn($vendor)
          ->causedBy(\Auth::user()->id)
          ->withProperties($vendor)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
      session()->flash('success', ' Vendor Updated ');
		
      return redirect($url);   

    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
    
     /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Vendor $vendor)
    {
         
        if($vendor->vendor_status == 1) {
            $vendor->vendor_status = 0;
        } else {
            $vendor->vendor_status = 1;
        } 
        $vendor->save();   
        // Log
        $logName = ($vendor->vendor_type == 1)?'Change Contractor Status':'Change Landlord Status';
        
        activity($logName)
         ->performedOn($vendor)
          ->causedBy(\Auth::user()->id)
          ->withProperties($vendor)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
  
        session()->flash('success', 'Status Changed Successfully');
       return redirect()->route('vendors.index');   
    }
	
	/*
    *
    *Vendor Code exist
    */
     public function checkVendorCodeExist(Request $request){
      $vendor_code = $request->vendor_code;
      $vendor_id = $request->vendor_id;
      if(empty($vendor_id)){
        $contact_exist = Vendor::where('vendor_code',$vendor_code)->get();
        $count = count($contact_exist);
        return $count;
      }else{
        $contact_exist = Vendor::where('vendor_code',$vendor_code)->where('id',$vendor_id)->get();
        $contact_count = count($contact_exist);
       // dd($contact_count);
        if($contact_count > 0){
          $count = 0;
        }else{
          $current_contact = Vendor::where('vendor_code',$vendor_code)->get();
          $count = count($current_contact);
        }
        return $count;
      }
    }

    
    
}
