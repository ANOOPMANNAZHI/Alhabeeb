<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\BackOffice\Entities\MaintenancePayment;
use Modules\BackOffice\Entities\RequestHistory;
use Modules\Masters\Entities\Bank;
use Modules\Masters\Entities\Vendor;
use App\Setting;
use DB;
use Session;
use URL;
use Route;
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Modules\BackOffice\Events\MaintenancePaymentApprove;
use Modules\BackOffice\Events\MaintenancePaymentReject;
use Dynamics;

class MaintenancePaymentController extends Controller
{
 public function __construct()
 {
  $this->middleware('auth');  
  $this->middleware('permission:maintenance_payment_add', ['only' => ['create','store']]);
  $this->middleware('permission:maintenance_payment_edit', ['only' => ['edit','update']]); 
  $this->middleware('permission:maintenance_payment_view', ['only' => ['index','show']]);       
  $this->middleware('permission:maintenance_payment_approval_view', ['only' => ['maintenancePaymentApprovalShow']]);  

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
      'maintenance_payment_no' => 'Payment No',
      'vendor__vendor_name' => 'Contractor',
      'bankInfo__bank_name' => 'Bank',
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

      $maintenancePayments = MaintenancePayment::filter($request)
                                                 ->sortable()
                                                 ->paginate($this->noOfRecord);
//dd($maintenancePayments);
     
      $route   =  $request->url();

      if(isset($request->ajax))
        return view('backoffice::Transaction.maintenance_payment_list_ajax',compact('maintenancePayments','request','route'));

      return view('backoffice::Transaction.maintenance_payment_list',compact('maintenancePayments','request','enquiry_fields','operations','name','route'));

    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      $maintenanceLatest = MaintenancePayment::orderBy('id','desc')->limit(1)->first();
      //$prefix       = prefixData('maintenance_payment_prefix')->configuration_value;
     // if(!empty($maintenanceLatest))
     //   $nextCode = $prefix.str_pad($maintenanceLatest->id+1,4,'0',STR_PAD_LEFT);
    //  else
     //   $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
 $year    = prefixData('landlord_payment_prefix')->configuration_year;
          $isYearCorrect = (date('y') == $year)?true:false;

      $generateCode = $this->maintenancePaymentCode();
      $nextCode = $generateCode['code'];
      $banks = Bank::active()->where('accounts_bank',1)->get(); 

      return view('backoffice::Transaction.add_maintenance_payment',compact('nextCode','banks','isYearCorrect'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
      $user = \Auth::user();
      $permissionExist = $user->hasPermissionTo('maintenance_payment_approval_approve');
      if($permissionExist){
        $maintenance_payment_approval_status = 4;
        $maintenance_payment_status = 2;
      }else{
        $maintenance_payment_approval_status = 0;
        $maintenance_payment_status = 1;
      }
      $maintenanceLatest = MaintenancePayment::orderBy('id','desc')->limit(1)->first();
      //$prefix       = prefixData('maintenance_payment_prefix')->configuration_value;
     // if(!empty($maintenanceLatest))
     //   $nextCode = $prefix.str_pad($maintenanceLatest->id+1,4,'0',STR_PAD_LEFT);
     // else
     //   $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
        $generateCode = $this->maintenancePaymentCode();
        $nextCode = $generateCode['code'];
      $this->validate($request, [
        'maintenance_payment_no' => 'required',                    
        'maintenance_payment_date'   => 'required|date|after:yesterday',
        'vendor_id'   => 'required',
        'bank_id'   => 'required',
        'maintenance_payment_method' => 'required', 
        ]);

      $maintenancePayment = MaintenancePayment::create([              
        'maintenance_payment_no' => $nextCode,
        'maintenance_payment_date' => $request['maintenance_payment_date'],
        'vendor_id' => $request['vendor_id'],
        'maintenance_payment_doc_no' => $request['maintenance_payment_doc_no'],
        'bank_id' => $request['bank_id'],
        'maintenance_payment_method' => $request['maintenance_payment_method'],
        'maintenance_payment_amount' => replaceCommaWithDot($request['maintenance_payment_amount']),
        'maintenance_payment_comment' => $request['maintenance_payment_comment'],
        'maintenance_payment_approval_status' => $maintenance_payment_approval_status,
        'maintenance_payment_status' => $maintenance_payment_status,
        'ax_batch_id' => $request['ax_batch_id'],
        'ax_payment_no' => $request['ax_payment_no'],
        'created_by' => \Auth::user()->id
        ]); 
       Setting::where('configuration_settings','landlord_payment_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);

      session()->flash('success', 'Maintenance Payment Created Successfully');
      return redirect()->route('maintenancePayment.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show(MaintenancePayment $maintenancePayment)
    {
     clearNotification('Modules\BackOffice\Notifications\MaintenancePaymentNotification',$maintenancePayment->id);
     readNotification('Modules\BackOffice\Notifications\MaintenancePaymentNotification',$maintenancePayment->id);

     return view('backoffice::Transaction.maintenance_payment_view',compact('maintenancePayment'));
   }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
      $maintenancePayment = MaintenancePayment::find($id);
      $banks = Bank::active()->where('accounts_bank',1)->get(); 
	  $year    = prefixData('landlord_payment_prefix')->configuration_year;
      $isYearCorrect = (date('y') == $year)?true:false;
      return view('backoffice::Transaction.add_maintenance_payment',compact('maintenancePayment','banks','isYearCorrect'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,MaintenancePayment $maintenancePayment)
    {
      $user = \Auth::user();
      $permissionExist = $user->hasPermissionTo('maintenance_payment_approval_approve');
      if($permissionExist){
        $maintenance_payment_approval_status = 4;
        $maintenance_payment_status = 2;
      }else{
        $maintenance_payment_approval_status = 0;
        $maintenance_payment_status = 1;
      }
      $current = Session::get('current');
      $this->validate($request, [
        'maintenance_payment_no' => 'required',                    
        'maintenance_payment_date'   => 'required|date',
        'vendor_id'   => 'required',
        'bank_id'   => 'required',
        'maintenance_payment_method' => 'required', 
        ]);
      $maintenancePayment->update([
        'maintenance_payment_date' => $request['maintenance_payment_date'],
        'vendor_id' => $request['vendor_id'],
        'maintenance_payment_doc_no' => $request['maintenance_payment_doc_no'],
        'bank_id' => $request['bank_id'],
        'maintenance_payment_method' => $request['maintenance_payment_method'],
        'maintenance_payment_amount' => replaceCommaWithDot($request['maintenance_payment_amount']),
        'maintenance_payment_comment' => $request['maintenance_payment_comment'],
        'ax_batch_id' => $request['ax_batch_id'],
        'ax_payment_no' => $request['ax_payment_no'],
        'updated_by' => \Auth::user()->id,
        ]);
      session()->flash('success', 'Maintenance Payment Updated Successfully');
      return redirect()->route('maintenancePayment.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(MaintenancePayment $maintenancePayment)
    {
      $maintenancePayment->delete();
      session()->flash('success', 'Maintenance Payment Deleted');
      return redirect()->route('maintenancePayment.index');
    }
     /**
    *
    * contractor Autocomplete
    *
    **/
     public function contractorTypeAutocompleteCode(Request $request){

       $key = $request->term;

       $contractor =   Vendor::where('vendor_status',1)->where('vendor_type_id',1)->where('vendor_name', 'ILIKE', '%'.$key.'%')
       ->select('id AS ids',DB::raw("CONCAT(vendor_name) as value"),'vendor_code AS code')
       ->get();
       return $contractor ;


     } 
   //Advance search
     public function maintenancePaymentSearch(Request $request){

      $closure = array();
      $closure_or = array();

      $vendor_q = array();
      $vendor_or = array();
      $bank_q = array();
      $bank_or = array();

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
          }if($value == 'vendor_name'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $vendor_or[] = array( $value , $operation ,$fieldValue);
            else
              $vendor_q[] = array( $value , $operation ,$fieldValue);

          }elseif($value == 'bank_name'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $bank_or[] = array( $value , $operation ,$fieldValue);
            else
              $bank_q[] = array( $value , $operation ,$fieldValue);

          }else{                
            $fieldValue = $request->fieldValue[$key];
            $operation = $request->operation[$key];
          }

          if($value != 'vendor_name' && $value != 'bank_name' &&  (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
           if($key != 0 && $request->logic[$key -1 ] == 'or' )
             $closure_or[] = array( $value , $operation ,$fieldValue);
           else
             $closure[] = array( $value , $operation ,$fieldValue);
         }

       }


     }


     if($request->ajax != true){
       if(count($vendor_or) == 0 && count($bank_or) == 0  && count($vendor_q) == 0 && count($closure) == 0 &&  count($bank_q) == 0 )
        $closure[] = array( 'id' , '=' ,0);
    }

  }

}

$maintenance_payment_no = (isset($request->maintenance_payment_no)) ? $request->maintenance_payment_no : null;
$maintenance_payment_date = (isset($request->maintenance_payment_date)) ? $request->maintenance_payment_date : null;


$vendor_id = (isset($request->vendor_id)) ? $request->vendor_id : null;    
$bank_id = (isset($request->bank_id)) ? $request->bank_id : null;    
$maintenance_payment_method = (isset($request->maintenance_payment_method)) ? $request->maintenance_payment_method : null;    
$maintenance_payment_amount = (isset($request->maintenance_payment_amount)) ? $request->maintenance_payment_amount : null;    
$vendor_code = (isset($request->vendor_code)) ? $request->vendor_code : null;    
$maintenance_payment_approval_status = (isset($request->maintenance_payment_approval_status)) ? $request->maintenance_payment_approval_status : null;    

$qiuck_search = array($maintenance_payment_no, $vendor_id , $bank_id, $maintenance_payment_method,$maintenance_payment_date,$maintenance_payment_amount,$vendor_code,$maintenance_payment_approval_status);
if($request->ajax != true){
  //$qiuck_search = array();
}
$result = array(
  $closure,
  $closure_or,      
  $vendor_q, $vendor_or, $bank_q,$bank_or,$qiuck_search ); 
   //dd($vendor_q);
return $result;    


}
public function paymentSearch(Request $request,$result = array())
{
  $name = Route::currentRouteName();
  $enquiry_fields = [
  'maintenance_payment_no' => 'Payment No',
  'vendor_name' => 'Contractor',
  'bank_name' => 'Bank',
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

    $maintenancePayment =   new MaintenancePaymentController;       
    $result =     $maintenancePayment->maintenancePaymentSearch($request); 
    $request->flash(); 

  }

  $maintenancePayments = MaintenancePayment::closure($result)->sortable()->paginate(10);
//dd($maintenancePayments);
  if(isset($request->route))
    $route   =  $request->route;

  if(isset($request->ajax))
    return view('backoffice::Transaction.maintenance_payment_list_ajax',compact('maintenancePayments','request','route'));

  return view('backoffice::Transaction.maintenance_payment_list',compact('maintenancePayments','request','enquiry_fields','operations','name'));

}
/*
    * Enquiry Search Form
    * 
    *
    */
public function enquiryFilter(){


  $enquiry_fields = [
  'maintenance_payment_no' => 'Payment No',
  'vendor_name' => 'Contractor',
  'bank_name' => 'Bank',
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

  return view('backoffice::Transaction.transaction_filter',compact('enquiry_fields','operations'));


}
public function cancelMaintenancePayment(Request $request){
 $id =$request['id'];
 return view('backoffice::Transaction.maintenance_payment_cancel_modal',compact('id'));

}
public function cancelMaintenancePaymentStore(Request $request,MaintenancePayment $maintenancePayment){

  $maintenance_payment_id = $request['id'];

  $maintenancePayment = MaintenancePayment::where('id',$maintenance_payment_id)->update([
    'maintenance_payment_reason_cancel' => $request['maintenance_payment_reason_cancel'],
    'maintenance_payment_cancel_by' => \Auth::user()->id,
    'maintenance_payment_cancel_date' => date('Y-m-d'),
    'updated_by' => \Auth::user()->id]);

   RequestHistory::create([
  'requestable_id' => $maintenance_payment_id,
  'requestable_type' => 'Modules\BackOffice\Entities\MaintenancePayment',
  'action' => 6,
  'created_by' => \Auth::user()->id]);

  MaintenancePayment::where('id',$maintenance_payment_id)->delete();

  session()->flash('success', 'Maintenance Payment Cancelled Successfully');
  return redirect()->route('maintenancePayment.index');

}
public function maintenancePaymentAction($maintenance_id,$status,$stage){

 $maintenancePayment = MaintenancePayment::where('id',$maintenance_id)->first();

 MaintenancePayment::where('id',$maintenance_id)->update([
  'maintenance_payment_approval_status' => $stage,
  'maintenance_payment_status' => $status,
  'updated_by' => \Auth::user()->id]);

 RequestHistory::create([
  'requestable_id' => $maintenance_id,
  'requestable_type' => 'Modules\BackOffice\Entities\MaintenancePayment',
  'action' => $stage,
  'created_by' => \Auth::user()->id]);

  $user = \Auth::user();

  $users = User::role(['accountant','finance_manager'])->get(); 
  $users = array_flatten($users);

if($status == 1 && $stage == 2){

  session()->flash('success', 'Maintenance Payment Sent For Approval Successfully');
  return redirect()->route('maintenancePayment.index');

}elseif($status == 2 && $stage == 4){
  // Check status is Pending If not Direct approve have permission
  if($maintenancePayment->maintenance_payment_approval_status == 2){
    $maintenancePayment->href = url('maintenancePayment/'.$maintenancePayment->id);
    event(new MaintenancePaymentApprove($maintenancePayment,$users));
  }
 
 return redirect()->back()->with('success', 'Maintenance Payment Approved Successfully');
}elseif($status == 1 && $stage == 5){

 $maintenancePayment->href = url('maintenancePayment/'.$maintenancePayment->id);
 event(new MaintenancePaymentReject($maintenancePayment,$users)); 

 session()->flash('success', 'Maintenance Payment Rejected');
 return redirect()->route('maintenancePaymentApproval');
}elseif($status == 1 && $stage == 3){
  session()->flash('success', 'Maintenance Payment Sent For UnApprove');
  return redirect()->route('maintenancePayment.index');
}elseif($status == 4 && $stage == 3){
  session()->flash('success', 'Maintenance Payment UnApproved');
  return redirect()->route('maintenancePaymentApproval');
}
else{
 session()->flash('success', 'Maintenance Payment');
 return redirect()->back()->with('success', 'Maintenance Payment UnApproved');
 
}

}
public function maintenancePaymentPost($maintenance_id,$status){
	$response	=	Dynamics::PaymentJournalAxHeaderPushData('AXMaintenancePayment');

	if($response=='Error'){
		return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
	}
	$maintenanceInfo = MaintenancePayment::where('id',$maintenance_id)->first();
	// dd($maintenanceInfo->bankInfo);
	$maintenanceLineItem[] = array(
		'JournalNum'=> $response,
		'JournalName'=> PAYMENT_JOURNAL_NAME, // PLM-PAY
		'PaymentDate'=> $maintenanceInfo->maintenance_payment_date->format('Y-m-d'),
		'Description'=> $maintenanceInfo->maintenance_payment_comment,
		'vendAccount'=> $maintenanceInfo->vendor->vendor_code,
		'currency'=> CURRENCY,
		'accountType'=> 'VENDOR',
		'paymentMethod'=>'',
		'checkBookid'=>isset($maintenanceInfo->maintenance_payment_doc_no)?$maintenanceInfo->maintenance_payment_doc_no:'',
		'documentNo'=> '',
		'DebitCredit'=>'Debit',
		'voucher' => $maintenanceInfo->maintenance_payment_no,		
		'Amount'=>$maintenanceInfo->maintenance_payment_amount ,
		'Remarks'=>$maintenanceInfo->maintenance_payment_comment,
		'Invoice'=>'',
		'dimension1'=> 'Building',
		'dimension1value'=>'000',
		'dimension2'=> 'Division',
		'dimension2value'=>'02',
		'dimension3'=> 'Employee',
		'dimension3value'=>'00000',
		'dimension4'=> 'Location',
		'dimension4value'=>'00',
		'dimension5'=> 'Projects',
		'dimension5value'=>'00',
		'DataAreaId'=>'HAB',
		'company'=>'HAB'
	);
	$maintenanceLineItem[] = array(
		'JournalNum'=> $response,
		'JournalName'=> PAYMENT_JOURNAL_NAME, // PLM-PAY
		'PaymentDate'=> $maintenanceInfo->maintenance_payment_date->format('Y-m-d'),
		'Description'=> $maintenanceInfo->maintenance_payment_comment,
		'vendAccount'=> $maintenanceInfo->bankInfo->bank_chequebook_id,
		'currency'=> CURRENCY,
		'accountType'=> 'BANK',
		'paymentMethod'=>($maintenanceInfo->maintenance_payment_method==1)?'Cash':'Cheque',
		'checkBookid'=>'',
		'documentNo'=> '',
		'DebitCredit'=>'Credit',
		'voucher' => $maintenanceInfo->maintenance_payment_no,		
		'Amount'=>$maintenanceInfo->maintenance_payment_amount ,
		'Remarks'=>$maintenanceInfo->maintenance_payment_comment,
		'Invoice'=>'',
		'dimension1'=> 'Building',
		'dimension1value'=>'000',
		'dimension2'=> 'Division',
		'dimension2value'=>AX_DIVISION_HO,
		'dimension3'=> 'Employee',
		'dimension3value'=>'00000',
		'dimension4'=> 'Location',
		'dimension4value'=>'00',
		'dimension5'=> 'Projects',
		'dimension5value'=>'00',
		'DataAreaId'=>'HAB',
		'company'=>'HAB'
	);
	//dd($maintenanceLineItem);
	$result = Dynamics::PaymentJournalAxLineItemPushData('AXPaymentLineItem', $maintenanceLineItem);
	
	if(count($maintenanceLineItem) >0 && $result =='Error'){
			return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
	}	
	
	MaintenancePayment::where('id',$maintenance_id)->update([
		'maintenance_payment_status' => $status,
		'maintenance_payment_posted_by' => \Auth::user()->id,
		'maintenance_payment_posted_date' => date('Y-m-d'),
		'ax_batch_id'=>$response,
		'ax_payment_no'=>$maintenanceInfo->maintenance_payment_no,
		'updated_by' => \Auth::user()->id]);

   RequestHistory::create([
  'requestable_id' => $maintenance_id,
  'requestable_type' => 'Modules\BackOffice\Entities\MaintenancePayment',
  'action' => 7,
  'created_by' => \Auth::user()->id]);

  session()->flash('success', 'Maintenance Payment Posted Successfully');
  return redirect()->route('maintenancePayment.index');
}

public function maintenancePaymentApproval(Request $request,$result = array()){
  $name = Route::currentRouteName();

  $enquiry_fields = [
  'maintenance_payment_no' => 'Payment No',
  'vendor__vendor_name' => 'Contractor',
  'bankInfo__bank_name' => 'Bank',
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

  $maintenancePaymentApprovals = MaintenancePayment::
                                        whereIn('maintenance_payment_status',[1,4])
                                       ->whereIn('maintenance_payment_approval_status',[2,3])
                                       ->filter($request)
                                       ->sortable()
                                       ->paginate($this->noOfRecord);
//dd($maintenancePaymentApprovals);
  
  $route   =  $request->url();

  if(isset($request->ajax))
    return view('backoffice::Transaction.maintenance_payment_approval_list_ajax',compact('maintenancePaymentApprovals','request','route'));

  return view('backoffice::Transaction.maintenance_payment_approval_list',compact('maintenancePaymentApprovals','request','enquiry_fields','operations','name','route'));

}
public function paymentApprovalSearch(Request $request,$result = array()){
  $name = Route::currentRouteName();
  $enquiry_fields = [
  'maintenance_payment_no' => 'Payment No',
  'vendor_name' => 'Contractor',
  'bank_name' => 'Bank',
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

    $maintenancePaymentApproval =   new MaintenancePaymentController;       
    $result =     $maintenancePaymentApproval->maintenancePaymentSearch($request); 
    $request->flash(); 

  }

  $maintenancePaymentApprovals = MaintenancePayment::whereIn('maintenance_payment_status',[1,4])->whereIn('maintenance_payment_approval_status',[2,3])->closure($result)->sortable()->paginate(10);
//dd($maintenancePaymentApprovals);
  if(isset($request->route))
    $route   =  $request->route;

  if(isset($request->ajax))
    return view('backoffice::Transaction.maintenance_payment_approval_list_ajax',compact('maintenancePaymentApprovals','request','route'));

  return view('backoffice::Transaction.maintenance_payment_approval_list',compact('maintenancePaymentApprovals','request','enquiry_fields','operations','name'));

}
public function maintenancePaymentApprovalShow($id){

 $maintenancePaymentApproval =  MaintenancePayment::where('id',$id)->first();
   //dd($maintenancePaymentApproval);
 return view('backoffice::Transaction.maintenance_payment_approval_view',compact('maintenancePaymentApproval'));

}
public function maintenancePaymentCode(){

      $prefix  = prefixData('landlord_payment_prefix')->configuration_value.prefixData('landlord_payment_prefix')->configuration_year;
     
      $incVal  = prefixData('landlord_payment_prefix')->configuration_increment_value;
     
      $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);
     
      return array('code'=>$nextCode,'inc'=>$incVal);

    }
}
