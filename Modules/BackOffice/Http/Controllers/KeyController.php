<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\BackOffice\Entities\Key;
use Modules\Sales\Entities\Tenant;
use Modules\Masters\Entities\Vendor;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\KeyHandoverEmail;
use Modules\BackOffice\Emails\KeyHandoverEmailLandlord;
use DB;

class KeyController extends Controller
{   
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
        $enquiry_fields = [
           'building__building_name' => 'Building',
           'Unit__unit_code' => 'Unit',
        ];

        $operations = [
           '=' => ' Is equal to '  ,
           '!=' => ' Is not equal to '  ,
           '>' => ' Is greater than '  ,
           '>=' => ' Is greater than or equal to '  ,
           '<' => ' Is less than '  ,
           '<=' => ' Is less than or equal to'  ,
           'ilike' => ' Like '  ,
           'ilike%...%' => ' Like%...% '  ,
        ]; 
        
        $request->flash();         
        $quick_url =  $route   = route('keyManagement.index');
        $user = \Auth::user();  

        $keys = Key::/*when(!($user->hasRole('super_admin')),function($query)use($user){
                      $query->where('user_id',$user->id);
                     })*/
                      takenOverTeam($request)
                     ->maintenanceEngineer($request)
					 ->facilityManager()
                    ->where('status',1)
                    ->filter($request)->sortable()->paginate($this->noOfRecord);

         
          // if (in_array('super_admin', $rolesNames) === true) {
          //   $keys = Key::where('status',1)->closure($result)->paginate(10);
          // }
          // else{
          //   $keys = Key::where('user_id',\Auth::user()->id)->closure($result)->where('status',1)->paginate(10);

          // }
          //return view('backoffice::Key.rent_payment_receipt_create');
		  //dd($keys);
          if(isset($request->ajax))
           return view('backoffice::Key.key_list_ajax',compact('keys','request','route'));
          else 
          return view('backoffice::Key.key_list',compact('keys','enquiry_fields','operations','quick_url'));
   
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('backoffice::Key.key_accept');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
      //Key Accept 
      $unit_id = $request['unit_id'];
      $exist = Key::where('unit_id',$unit_id)->orderBy('id','DESC')->first();
      if(!empty($exist)){
        $exist->update(['status'=>0]);
      }
      $Key = new Key; 
      $Key->user_id     = \Auth::user()->id;
      $Key->building_id  = $request['building_id'];
      $Key->unit_id = $unit_id;
      $Key->created_by           = \Auth::user()->id;
      $Key->save();
      return redirect()->route('keyManagement.index'); 

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('backoffice::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('backoffice::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
    /*
    *  Search Form
    * 
    *
    */
    public function keySearchFilter(){


      $enquiry_fields = [
           'building' => 'Building',
           'unit' => 'Unit',
      ];

      $operations = [
       '=' => ' Is equal to '  ,
       '!=' => ' Is not equal to '  ,
       '>' => ' Is greater than '  ,
       '>=' => ' Is greater than or equal to '  ,
       '<' => ' Is less than '  ,
       '<=' => ' Is less than or equal to'  ,
       'ilike' => ' Like '  ,
       'ilike%...%' => ' Like%...% '  ,
      ];

     return view('backoffice::Key.key_filter',compact('enquiry_fields','operations'));

    }
    /*
    *
    * Common Search Results Renewal
    *
    */
    public function keySearch(Request $request){

    $closure = array();
    $closure_or = array();
    $building = array();
    $building_or = array(); 
    $unit = array();
    $unit_or = array();

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
                    }elseif($value == 'building'){

                     if($key != 0 && $request->logic[$key -1 ] == 'or' )
                       $building_or[] = array( $value , $operation ,$fieldValue);
                     else
                       $building[] = array( $value , $operation ,$fieldValue);

                   }elseif($value == 'unit'){

                    if($key != 0 && $request->logic[$key -1 ] == 'or' )
                      $unit_or[] = array( $value , $operation ,$fieldValue);
                    else
                      $unit[] = array( $value , $operation ,$fieldValue);

                  }else{                
                    $fieldValue = $request->fieldValue[$key];
                    $operation = $request->operation[$key];
                  }

                  if($value != 'building' &&  $value != 'unit'  &&   (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
                   if($key != 0 && $request->logic[$key -1 ] == 'or' )
                     $closure_or[] = array( $value , $operation ,$fieldValue);
                   else
                     $closure[] = array( $value , $operation ,$fieldValue);
                 }

               }


            }


             if($request->ajax != true){
               if(count($building_or) == 0 && count($closure) == 0 &&  count($building) == 0 && count($unit) == 0 && count($unit_or) == 0  && count($tenant) == 0  && count($tenant_or) == 0 )
                $closure[] = array( 'id' , '=' ,0);
            }

        }

    }
   //  dd($closure_date);


        $building_id = (isset($request->building_id)) ? $request->building_id : null;
        $unit_id = (isset($request->unit_id)) ? $request->unit_id : null;
          

        $qiuck_search = array( $building_id , $unit_id);
        if($request->ajax != true){

          $qiuck_search = array();
        }
        $result = array(
         $closure,$closure_or,$building, $building_or,$unit, $unit_or,$qiuck_search
         ); 
             //dd($result);

    return $result;    


    }
    /*
    *
    *
    * Key Request Serach 
    *
    *
    */
    public function keyRequestSearch(Request $request){

        $enquiry_fields = [
           'building' => 'Building',
           'unit' => 'Unit',
        ];

        $operations = [
           '=' => ' Is equal to '  ,
           '!=' => ' Is not equal to '  ,
           '>' => ' Is greater than '  ,
           '>=' => ' Is greater than or equal to '  ,
           '<' => ' Is less than '  ,
           '<=' => ' Is less than or equal to'  ,
           'ilike' => ' Like '  ,
           'ilike%...%' => ' Like%...% '  ,
        ];

        $result = array();   

        if(isset($request)){

            $result  =  $this->keySearch($request);

          }    
          $request->flash(); 

          if(isset($request->route))
            $route   =  $request->route;
          $roles = \Auth::user()->getRoles();
          $rolesNames = \Auth::user()->getRoleNames()->toArray(); 

         
          if (in_array('super_admin', $rolesNames) === true) {
            $keys = Key::where('status',1)->closure($result)->paginate(10);
          }
          else{
            $keys = Key::where('user_id',\Auth::user()->id)->closure($result)->where('status',1)->paginate(10);

          }
      
          if(isset($request->ajax))
           return view('backoffice::Key.key_list_ajax',compact('keys','request','route'));
         else 
          return view('backoffice::Key.key_list',compact('keys','enquiry_fields','operations'));

    }
    /**
    *
    * Tenant Autocomplete
    *
    **/
    public function tenantActiveAutocomplete(Request $request){

       $key = $request->term;
       
       $tenants =   Tenant::active()->where('tenant_name', 'ILIKE', '%'.$key.'%')
                               ->select('id AS ids',DB::raw("CONCAT(tenant_name,'-',tenant_code) as value"))
                               ->get();

       return $tenants ;


    }
    /*
    *
    *
    * keyAccept Aginst Landlord or Tenant
    *
    */
    public function keyAcceptAginstLandlordTenant(Request $request)
    {
        $key = $request->input('key_id');
        $type = $request->input('type');
        $key_details = Key::where('id',$key)->first();

        return view('backoffice::Key.key_handover_landlord_or_tenant_modal',compact('key_details','type'));
    }
    public function keyAcceptAginstLandlordTenantStore(Request $request)
    {
      //Key Accept  aginst Tenant Or Landlord
      $unit_id = $request['unit_id'];
      $type = $request['type'];
      $exist = Key::where('unit_id',$unit_id)->orderBy('id','DESC')->first();
      if(!empty($exist)){
        $exist->update(['status'=>0]);
      }
      $Key = new Key; 
      if($type == "Tenant"){
		  
		$tenant_name = $request['tenant_name'];
        $explodecode=explode('-',$tenant_name);
        $arraycount=count($explodecode);
        $details = Tenant::where('tenant_code',$explodecode[$arraycount-2].'-'.$explodecode[$arraycount-1])->first();
        $Key->tenant_id     = $details->id;
	  
	  }else{$Key->landlord_id     = $request['vendor_id'];}
      
      $Key->building_id  = $request['building_id'];
      $Key->unit_id = $unit_id;
      $Key->created_by           = \Auth::user()->id;
      $Key->save();
      if($type == "Tenant"){

          $tenant_id = $request['tenant_id'];
          $tenant_name = $request['tenant_name'];
		  $explodecode = explode('-',$tenant_name);
          $arraycount = count($explodecode);
          $details = Tenant::where('tenant_code',$explodecode[$arraycount-2].'-'.$explodecode[$arraycount-1])->first();
          $tenantEmail = $details->tenant_contact_email;
          if($tenantEmail == "")$tenantEmail = $details->tenant_personal_email;

          if(!empty($tenantEmail)){

            Mail::to($tenantEmail)->send(new KeyHandoverEmail($details));
          }
      }else{
        
        $vendor_id = $request['vendor_id'];
        $vendorDetails = Vendor::where('id',$vendor_id)->first();
        $vendorEmail = $vendorDetails->vendor_contact_email;
       
        if(!empty($vendorEmail)){

          Mail::to($vendorEmail)->send(new KeyHandoverEmailLandlord($vendorDetails));
        }
      }
      return redirect()->route('keyManagement.index'); 

    }
    /*
    *
    *
    * key Scan For Payment Receipt
    *
    *
    */
    public function keyScanForPaymentReceipt(Request $request)
    {
      return view('backoffice::Key.qrcode_scan_payment_modal');
    }

}
