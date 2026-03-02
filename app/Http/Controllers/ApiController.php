<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Hweekttp\Response; 
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Sales\Entities\Sales;
use Modules\Masters\Entities\UnitType;
use Modules\Sales\Entities\Tenant;
use Modules\Sales\Entities\ViewEnquiry;
use Modules\Sales\Entities\viewLandlordEnquiryList;
use Modules\Masters\Entities\Vendor;
use Modules\General\Entities\WorkFlowProcess;
use Modules\Sales\Entities\SalesNote;
use Carbon\Carbon;
use Session;
use URL;
use Route;
use DB;
use Modules\General\Http\Controllers\GeneralController as General;
use Illuminate\Support\Facades\Mail;
use Modules\Sales\Events\NewEnquiry;
use Modules\Sales\Events\UpdateEnquiry;
use Modules\Sales\Events\ReminderEnquiry;
use Modules\Sales\Emails\SalesEnquiryEmail;

class ApiController  
{
	 use ValidatesRequests;

  public function apiSalesEnquiry(Request $request)
{
    try {
        // Validate the request
        $this->validate($request, [
            'sales_mobile_no' => 'required|regex:/^((\+)?(\d{2,2}))?(\d{13}){1}?$/',
            'sales_enquiry_name' => 'required|max:75',
            'sales_email' => 'nullable|email',
            'year' => 'required',
            'month' => 'required'
        ]);

        // Get the prefix for the enquiry number
        $prefix = prefixData('tenant_enquiry_no_prefix')->configuration_value;

        // Get the latest sales enquiry to generate the next enquiry code
        $salesLatest = SalesEnquiry::where('sales_type', 1)
            ->where('sales_enquiry_direct_contract', 1)
            ->orderBy('id', 'desc')
            ->first();

        if (!empty($salesLatest)) {
            $nextCode = $prefix . str_pad($salesLatest->enquiry_index + 1, 4, '0', STR_PAD_LEFT);
            $index = $salesLatest->enquiry_index + 1;
        } else {
            $nextCode = $prefix . str_pad(1, 4, '0', STR_PAD_LEFT);
            $index = 1;
        }

        // Create new SalesEnquiry
        $salesEnquiry = new SalesEnquiry;
        $salesEnquiry->sales_enquiry_no = $nextCode;
        $salesEnquiry->sales_type = 1;
        $salesEnquiry->sales_mobile_no = $request->sales_mobile_no;
        $salesEnquiry->sales_enquiry_name = $request->sales_enquiry_name;
        $salesEnquiry->sales_email = $request->sales_email;
        $salesEnquiry->sales_region = $request->sales_region;
        $salesEnquiry->enquiry_index = $index;
        $salesEnquiry->sales_enquiry_direct_contract = 1;
        $processFlow = 101;
        $salesEnquiry->work_flow_processes_code = $processFlow;
        $salesEnquiry->sales_no_of_unit = $request->sales_no_of_unit;
        $salesEnquiry->tenant_type_id = $request->tenant_type;
        $salesEnquiry->sales_mode_id = $request->source;
        $sales_move_in_date = $request->year . '-' . $request->month . '-1';
        $salesEnquiry->sales_move_in_date = $sales_move_in_date;
        $salesEnquiry->sales_note = $request->sales_note;
        $salesEnquiry->created_by = 1;
        $salesEnquiry->save();

        // Sync price ranges if available
        if ($request->price_ranges) {
            if (count($request->price_ranges) > 0) {
                $request->price_ranges = array_where($request->price_ranges, function ($value) {
                    return $value > 0;
                });
                $salesEnquiry->priceRanges()->sync($request->price_ranges);
            }
        }

        // Sync unit types if available
        $salesEnquiry->unitTypes()->sync($request->unit_types);

        // Sync locations if available
        if (isset($request->locations) && count($request->locations) > 0) {
            $salesEnquiry->locations()->sync($request->locations);
        }

        // Create Sales record
        $price_range_id = (isset($request->price_ranges) && count($request->price_ranges) > 0) ? $request->price_ranges[0] : null;
        $location_id = (isset($request->locations) && count($request->locations) > 0) ? $request->locations[0] : null;

        $sales = Sales::create([
            'sales_enquiry_id' => $salesEnquiry->id,
            'sales_type' => $salesEnquiry->sales_type,
            'work_flow_processes_code' => $processFlow,
            'created_by' => 1,
        ]);

        // Process assignment
        $general = new General;
        $processAssign = $general->roleUsersFromProcess($processFlow, $location_id, $price_range_id);
        $users = [];

        if ($processAssign == false) {
            $res = $general->workFlowProcess($processFlow);
            $sales->salesUsers()->attach($res->default_role, ['user_id' => $res->default_user_id]);
            $sales->save();

            if (empty($res->default_user_id)) {
                $users_list = \App\User::role($res->default_role)->get()->pluck('id');
                $users = $users_list->toArray();
            } else {
                $users[] = $res->default_user_id;
            }
        } else {
            foreach ($processAssign->assign as $val) {
                $user_id = $val->user_id ?? null;
                $sales->salesUsers()->attach($val->role_id, ['user_id' => $user_id]);
                $sales->save();

                if (empty($user_id)) {
                    $users_list = \App\User::role($val->role_id)->get()->pluck('id');
                    $users_list = $users_list->toArray();
                    $users_list = array_flatten($users_list);
                    $users = count($users) > 0 ? array_merge($users, $users_list) : $users_list;
                } else {
                    $users[] = $user_id;
                }
            }
        }

        $users = array_flatten($users);
        $users_notify = \App\User::whereIn('id', $users)->get();

        // Return success response
        return response()->json([
            'status' => true,
            'message' => 'Enquiry Saved'
        ]);
    } catch (\Exception $e) {
        // Catch any exception and return JSON error response with exception message
        return response()->json([
            'status' => false,
            'message' => $e->getMessage() // Return the exception message here
        ], 500);
    }
}



    public function apiSalesEnquiryold(Request $request){

  $this->validate($request, [
    'sales_mobile_no' => 'required|regex:/^((\+)?(\d{2,2}))?(\d{13}){1}?$/',    
    'sales_enquiry_name' => 'required|max:75',
    'sales_email' => 'nullable|email', 
   // 'unit_type_id' => 'required',
    'year' => 'required',
    'month' => 'required'
   ]); 


  $prefix  = prefixData('tenant_enquiry_no_prefix')->configuration_value;
  $salesLatest = SalesEnquiry::where('sales_type',1)
            ->where('sales_enquiry_direct_contract',1)
            ->orderBy('id', 'desc')->first();




   if(!empty($salesLatest)){
      $nextCode   = $prefix.str_pad($salesLatest->enquiry_index+1,4,'0',STR_PAD_LEFT);
      $index    = $salesLatest->enquiry_index+1;
    }
    else{
      $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);    
      $index = 1;
    }

$salesEnquiry = new SalesEnquiry;


  $salesEnquiry->sales_enquiry_no = $nextCode;
  $salesEnquiry->sales_type = 1; 
  $salesEnquiry->sales_mobile_no = $request->sales_mobile_no;

  $salesEnquiry->sales_enquiry_name = $request->sales_enquiry_name;
  $salesEnquiry->sales_email = $request->sales_email;
  $salesEnquiry->sales_region = $request->sales_region; 
  $salesEnquiry->enquiry_index = $index ;  

 $salesEnquiry->sales_enquiry_direct_contract = 1; // Normal Contract 
 $processFlow = 101;
 $salesEnquiry->work_flow_processes_code = $processFlow;   
 

  $salesEnquiry->sales_no_of_unit = $request->sales_no_of_unit;        

 $salesEnquiry->tenant_type_id = $request->tenant_type;
 $salesEnquiry->sales_mode_id = $request->source;
 $sales_move_in_date = $request->year.'-'.$request->month.'-1';
  $salesEnquiry->sales_move_in_date = $sales_move_in_date; 
   $salesEnquiry->sales_note = $request->sales_note; 
   $salesEnquiry->created_by = 1; 

   $salesEnquiry->save();

    if($request->price_ranges ){
      if(count($request->price_ranges) > 0) {
        $request->price_ranges = array_where($request->price_ranges, function ($value, $key) {
         if($value > 0)
          return true;
      });
        $salesEnquiry->priceRanges()->sync($request->price_ranges);
      }
    }


      $salesEnquiry->unitTypes()->sync($request->unit_types); 

    if(isset($request->locations)  && count($request->locations) > 0) {      
      $salesEnquiry->locations()->sync($request->locations); 
    }




      $price_range_id = (isset($request->price_ranges) && count($request->price_ranges) > 0)?  $request->price_ranges[0] : null;
       $location_id = ( isset($request->locations) && count($request->locations) > 0)? $request->locations[0] : null;  


        $sales =   Sales::create([
      'sales_enquiry_id' =>$salesEnquiry->id,
      'sales_type' => $salesEnquiry->sales_type,
      'work_flow_processes_code' => $processFlow,
      'created_by' => 1,
      ]);

     $general =  new General;
     $processAssign = $general->roleUsersFromProcess($processFlow,$location_id,$price_range_id);

     $users = array();

     if($processAssign == false){

      $res =    $general->workFlowProcess($processFlow);

      $sales->salesUsers()->attach($res->default_role, ['user_id' => $res->default_user_id]);
      $sales->save();

      if(empty($res->default_user_id)){      
        $users_list = \App\User::role($res->default_role)->get()->pluck('id');       
        $users = $users_list->toArray();        
      }else 
      $users[] = $res->default_user_id;



    }else{

     foreach($processAssign->assign as $val){
       $user_id = $val->user_id;

       if(empty($val->user_id))
         $user_id = null;

       $sales->salesUsers()->attach($val->role_id, ['user_id' => $user_id]);
       $sales->save();

       if(empty($user_id)){      
        $users_list = \App\User::role($val->role_id)->get()->pluck('id');          
        $users_list = $users_list->toArray(); 
        $users_list = array_flatten($users_list);   
        if(count($users) > 0){
          $users = array_merge($users,$users_list);             
        }else          
        $users = $users_list;       
      }else 
      $users[] = $user_id;      

    } 
  }
  $users = array_flatten($users);      
  $users_notify =  \App\User::whereIn('id',$users)->get();

 return response()->json(['status'=> true,  
                         'message'=>'Enquiry Saved'
                       ]);   

    }



}