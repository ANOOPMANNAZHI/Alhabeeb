<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\BackOffice\Entities\LandlordPayment;
use Modules\BackOffice\Entities\LandlordInvoice;
use Modules\BackOffice\Entities\RequestHistory;
use Modules\Masters\Entities\Bank;
use Modules\Masters\Entities\Vendor;
use Modules\Masters\Entities\Currency;
use Modules\Sales\Entities\LandlordContract;
use App\Setting;
use DB;
use Session;
use URL;
use Route;
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Modules\BackOffice\Events\LandlordPaymentApprove;
use Modules\BackOffice\Events\LandlordPaymentReject;
use Dynamics;

class LandlordPaymentController extends Controller
{
	  public function __construct()
	  {
		$this->middleware('auth');  
		$this->middleware('permission:landlord_payment_add', ['only' => ['create','store']]);
		$this->middleware('permission:landlord_payment_edit', ['only' => ['edit','update']]); 
		$this->middleware('permission:landlord_payment_view', ['only' => ['index','show']]); 

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
		  'landlord_payment_no' => 'Payment No',
		  'landlordContract__vendorInfo__vendor_name' => 'Landlord',
		  'landlordContract__landlord_contract_no' => 'Agreement No',
		  'landlordInvoice__landlord_invoice_voucher_no' => 'Invoice No',
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

		  $landlordPayments = LandlordPayment::orderBy('id','desc')->filter($request)
											  ->sortable()
											  ->paginate($this->noOfRecord);

											 // dd($landlordPayments);
		  
		  $route   =  $request->url();
		  if(isset($request->ajax))
			return view('backoffice::Transaction.landlord_payment_list_ajax',compact('landlordPayments','request','route'));

		  return view('backoffice::Transaction.landlord_payment_list',compact('landlordPayments','request','enquiry_fields','operations','name','route'));
		}

		/**
		 * Show the form for creating a new resource.
		 * @return Response
		 */
		public function create()
		{
		  $year    = prefixData('landlord_payment_prefix')->configuration_year;

      	  $isYearCorrect = (date('y') == $year)?true:false;

		  $generateCode = $this->landlordPaymentCode();
		  $nextCode = $generateCode['code'];
		  
		  $banks = Bank::active()->where('accounts_bank',1)->get(); 
		  $currencies = Currency::active()->get(); 
		  return view('backoffice::Transaction.add_landlord_payment',compact('nextCode','banks','currencies','isYearCorrect'));
		}

		/**
		 * Store a newly created resource in storage.
		 * @param  Request $request
		 * @return Response
		 */
		public function store(Request $request)
		{
		 $user = \Auth::user();
		 $permissionExist = $user->hasPermissionTo('landlord_payment_approval_approve');
		 if($permissionExist){
		  $landlord_payment_approval_status = 4;
		  $landlord_payment_status = 2;
		}else{
		  $landlord_payment_approval_status = 0;
		  $landlord_payment_status = 1;
		}
		$generateCode = $this->landlordPaymentCode();
		$nextCode = $generateCode['code'];

		$this->validate($request, [
		  'landlord_payment_no' => 'required',                    
		  'landlord_payment_date'   => 'required|date',
		  'landlord_contract_id'   => 'required',
		  'landlord_invoice_id'   => 'required',
		  'landlord_payment_invoice_amt'   => 'required',
		  'landlord_payment_balance_amt'   => 'required',
		  'landlord_payment_amount'   => 'required',
		  'landlord_payment_method'   => 'required',
		  'landlord_payment_currency_id'   => 'required',
		  'bank_id'   => 'required',
		  ]);

		$invoiceId = $request['landlord_invoice_id'];
		
		$landlordPayment = LandlordPayment::create([              
		  'landlord_payment_no' => $nextCode,
		  'landlord_payment_date' => $request['landlord_payment_date'],
		  'landlord_contract_id' => $request['landlord_contract_id'],
		  'landlord_invoice_id' => $invoiceId,
		  'landlord_payment_invoice_amt' => replaceCommaWithDot($request['landlord_payment_invoice_amt']),
		  'landlord_payment_balance_amt' => replaceCommaWithDot($request['landlord_payment_balance_amt']),
		  'landlord_payment_amount' => replaceCommaWithDot($request['landlord_payment_amount']),
		  'landlord_payment_method' => $request['landlord_payment_method'],
		  'landlord_payment_currency_id' => $request['landlord_payment_currency_id'],
		  'bank_id' => $request['bank_id'],
		  'landlord_payment_cheque_no' => $request['landlord_payment_cheque_no'],      
		  'landlord_payment_comment' => $request['landlord_payment_comment'],
		  'ax_batch_id' => $request['ax_batch_id'],
		  'ax_payment_no' => $request['ax_payment_no'],
		  'landlord_payment_status' => $landlord_payment_status,
		  'landlord_payment_approval_status' => $landlord_payment_approval_status,
		  'created_by' => \Auth::user()->id
		  ]); 

		$sumOfPaid = $this->invoicePostBalanceAmount($invoiceId);

		LandlordPayment::where('id',$landlordPayment->id)->update([
					'landlord_payment_balance_amt'=>$sumOfPaid->total
			]);

		Setting::where('configuration_settings','landlord_payment_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);
		session()->flash('success', 'Landlord Payment Created Successfully');
		return redirect()->route('landlordPayment.index');
	  }

		/**
		 * Show the specified resource.
		 * @return Response
		 */
		public function show(LandlordPayment $landlordPayment)
		{
		  clearNotification('Modules\BackOffice\Notifications\LandlordPaymentNotification',$landlordPayment->id);
		  readNotification('Modules\BackOffice\Notifications\LandlordPaymentNotification',$landlordPayment->id);
		  return view('backoffice::Transaction.landlord_payment_view',compact('landlordPayment'));
		}

		/**
		 * Show the form for editing the specified resource.
		 * @return Response
		 */
		public function edit($id)
		{
		  $landlordPayment = LandlordPayment::find($id);
		  $banks = Bank::active()->where('accounts_bank',1)->get(); 
		  $currencies = Currency::active()->get(); 
		  $invoiceId = $landlordPayment->landlord_invoice_id;
		  $year    = prefixData('landlord_payment_prefix')->configuration_year;

      	  $isYearCorrect = (date('y') == $year)?true:false;
		  $sumPaymentAmt = LandlordPayment::where('landlord_invoice_id',$invoiceId)->select(DB::raw('sum(cast(landlord_payment_amount as double precision)) AS total'))->first();

		  return view('backoffice::Transaction.add_landlord_payment',compact('landlordPayment','banks','currencies','sumPaymentAmt','isYearCorrect'));
		}

		/**
		 * Update the specified resource in storage.
		 * @param  Request $request
		 * @return Response
		 */
		public function update(Request $request,LandlordPayment $landlordPayment)
		{
		 $user = \Auth::user();
		 $permissionExist = $user->hasPermissionTo('landlord_payment_approval_approve');
		 if($permissionExist){
		  $landlord_payment_approval_status = 4;
		  $landlord_payment_status = 2;
		}else{
		  $landlord_payment_approval_status = 0;
		  $landlord_payment_status = 1;
		}
		$this->validate($request, [                   
		  'landlord_payment_date'   => 'required|date',
		  'landlord_contract_id'   => 'required',
		  'landlord_invoice_id'   => 'required',
		  'landlord_payment_invoice_amt'   => 'required',
		  'landlord_payment_balance_amt'   => 'required',
		  'landlord_payment_amount'   => 'required',
		  'landlord_payment_method'   => 'required',
		  'landlord_payment_currency_id'   => 'required',
		  'bank_id'   => 'required',
		  ]);

		$invoiceId = $landlordPayment->landlord_invoice_id;
		$sumOfPaid = $this->invoicePostBalanceAmount($invoiceId)->total;
		$currAmt = replaceCommaWithDot($request['landlord_payment_amount']);
		$current_bal_amt = abs($sumOfPaid - $landlordPayment->landlord_payment_amount) + $currAmt;
		
		$current_bal_amt = $landlordPayment->landlord_payment_invoice_amt - abs($current_bal_amt);
		
		$landlordPayment->update([              
		  'landlord_payment_date' => $request['landlord_payment_date'],     
		  'landlord_payment_amount' => $currAmt,
		  'landlord_payment_method' => $request['landlord_payment_method'],
		  'landlord_payment_currency_id' => $request['landlord_payment_currency_id'],
		  'bank_id' => $request['bank_id'],
		  'landlord_payment_cheque_no' => $request['landlord_payment_cheque_no'],      
		  'landlord_payment_balance_amt' => abs($current_bal_amt),
		  'landlord_payment_comment' => $request['landlord_payment_comment'],
		  'ax_batch_id' => $request['ax_batch_id'],
		  'ax_payment_no' => $request['ax_payment_no'],
		  'landlord_payment_status' => $landlord_payment_status,
		  'landlord_payment_approval_status' => $landlord_payment_approval_status,
		  'created_by' => \Auth::user()->id
		  ]); 

		session()->flash('success', 'Landlord Payment Updated Successfully');
		return redirect()->route('landlordPayment.index');

	  }

		/**
		 * Remove the specified resource from storage.
		 * @return Response
		 */
		public function destroy()
		{
		}
		public function landlordTypeAutocompleteCode(Request $request){
		  $key = $request->term;

		  $landlord =   Vendor::whereHas('landlordContract.landlordInvoice')->where('vendor_status',1)->where('vendor_type_id',2)->where('vendor_name', 'ILIKE', '%'.$key.'%')
		  ->select('id AS ids',DB::raw("CONCAT(vendor_name) as value"),'vendor_code AS code')
		  ->get();
		  return $landlord ;

		}
		public function agreementAutocompleteCode(Request $request){

		 $key = $request->contract;
		 $vendor_id = $request->vendor;
		 $contract =   LandlordContract::whereHas('landlordInvoice', function ( $query ) {
	        $query->where('is_invoice_payment_status',1); 
	    })
		->where('vendor_id',$vendor_id)->where('landlord_contract_no', 'ILIKE', '%'.$key.'%')
		 ->select('id AS ids',DB::raw("CONCAT(landlord_contract_no) as value"))
		 ->get();
		 return $contract ;
	   }
	   public function invoiceDetailsByContractNo(Request $request){
		$contract_id = $request->contract_id; 
		$invoice =  LandlordInvoice::where('landlord_contract_id',$contract_id)->first();
		$landlord_invoice_id = $invoice->id;		
		
		$sumPaymentAmt = LandlordPayment::where('landlord_invoice_id',$landlord_invoice_id)->select(DB::raw('sum(cast(landlord_payment_amount as double precision)) AS total'))->first();

		
		
		$landlord_invoice_voucher_no = $invoice->landlord_invoice_voucher_no;
		$landlord_invoice_amt = $invoice->landlord_invoice_amt;


		return json_encode(array($invoice,$landlord_invoice_voucher_no,$landlord_invoice_amt,$landlord_invoice_id, $sumPaymentAmt));
	  }
	  //Advance search
	  public function landlordPaymentSearch(Request $request){

		$closure = array();
		$closure_or = array();

		$vendor_q = array();
		$vendor_or = array();
		$contract_q = array();
		$contract_or = array();
		$invoice_q = array();
		$invoice_or = array();

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

			}elseif($value == 'landlord_contract_no'){

			  if($key != 0 && $request->logic[$key -1 ] == 'or' )
				$contract_or[] = array( $value , $operation ,$fieldValue);
			  else
				$contract_q[] = array( $value , $operation ,$fieldValue);

			}elseif($value == 'landlord_invoice_voucher_no'){

			  if($key != 0 && $request->logic[$key -1 ] == 'or' )
				$invoice_or[] = array( $value , $operation ,$fieldValue);
			  else
				$invoice_q[] = array( $value , $operation ,$fieldValue);

			}else{                
			  $fieldValue = $request->fieldValue[$key];
			  $operation = $request->operation[$key];
			}

			if($value != 'vendor_name' && $value != 'landlord_contract_no' && $value != 'landlord_invoice_voucher_no' &&  (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
			 if($key != 0 && $request->logic[$key -1 ] == 'or' )
			   $closure_or[] = array( $value , $operation ,$fieldValue);
			 else
			   $closure[] = array( $value , $operation ,$fieldValue);
		   }

		 }


	   }


	   if($request->ajax != true){
		 if(count($vendor_or) == 0 && count($contract_or) == 0  && count($vendor_q) == 0 && count($closure) == 0 &&  count($contract_q) == 0 &&  count($invoice_q) == 0 &&  count($invoice_or) == 0 )
		  $closure[] = array( 'id' , '=' ,0);
	  }

	}

	}

	$landlord_payment_no = (isset($request->landlord_payment_no)) ? $request->landlord_payment_no : null;
	$landlord_payment_date = (isset($request->landlord_payment_date)) ? $request->landlord_payment_date : null;


	$vendor_id = (isset($request->vendor_id)) ? $request->vendor_id : null;    
	$landlord_contract_id = (isset($request->landlord_contract_id)) ? $request->landlord_contract_id : null;    
	$landlord_invoice_id = (isset($request->landlord_invoice_id)) ? $request->landlord_invoice_id : null;    
	$landlord_payment_amount = (isset($request->landlord_payment_amount)) ? $request->landlord_payment_amount : null;    
	$vendor_code = (isset($request->vendor_code)) ? $request->vendor_code : null;    
	$landlord_payment_approval_status = (isset($request->landlord_payment_approval_status)) ? $request->landlord_payment_approval_status : null;    


	$qiuck_search = array($landlord_payment_no, $vendor_id , $landlord_contract_id, $landlord_invoice_id,$landlord_payment_date,$landlord_payment_amount,$vendor_code,$landlord_payment_approval_status);
	if($request->ajax != true){
	  //$qiuck_search = array();
	}
	$result = array(
	  $closure,
	  $closure_or,      
	  $vendor_q, $vendor_or, $contract_q,$contract_or,$invoice_q,$invoice_or,$qiuck_search ); 
	   //dd($vendor_q);
	return $result;    


	}
	public function paymentSearch(Request $request,$result = array()){
	  $name = Route::currentRouteName();
	  $enquiry_fields = [
	  'landlord_payment_no' => 'Payment No',
	  'vendor_name' => 'Landlord',
	  'landlord_contract_no' => 'Agreement No',
	  'landlord_invoice_voucher_no' => 'Invoice No',
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

		$landlordPayment =   new LandlordPaymentController;       
		$result =     $landlordPayment->landlordPaymentSearch($request); 
		$request->flash(); 

	  }

	  $landlordPayments = LandlordPayment::closure($result)->sortable()->paginate(10);
	  if(isset($request->route))
		$route   =  $request->route;

	  if(isset($request->ajax))
		return view('backoffice::Transaction.landlord_payment_list_ajax',compact('landlordPayments','request','route'));

	  return view('backoffice::Transaction.landlord_payment_list',compact('landlordPayments','request','enquiry_fields','operations','name'));
	}
	public function enquiryFilter(){
	 $enquiry_fields = [
	 'landlord_payment_no' => 'Payment No',
	 'vendor_name' => 'Landlord',
	 'landlord_contract_no' => 'Agreement No',
	 'landlord_invoice_voucher_no' => 'Invoice No',
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
	public function cancelLandlordPayment(Request $request){
	 $id =$request['id'];
	 return view('backoffice::Transaction.landlord_payment_cancel_modal',compact('id'));

	}
	public function cancelLandlordPaymentStore(Request $request,LandlordPayment $landlordPayment){

	  $landlord_payment_id = $request['id'];

	  $landlordPayment = LandlordPayment::where('id',$landlord_payment_id)->update([
		'landlord_payment_reason_cancel' => $request['landlord_payment_reason_cancel'],
		'landlord_payment_cancel_by' => \Auth::user()->id,
		'landlord_payment_cancel_date' => date('Y-m-d'),
		'updated_by' => \Auth::user()->id]);

	  RequestHistory::create([
		'requestable_id' => $landlord_payment_id,
		'requestable_type' => 'use Modules\BackOffice\Entities\LandlordPayment',
		'action' => 6,
		'created_by' => \Auth::user()->id]);


	  LandlordPayment::where('id',$landlord_payment_id)->delete();

	  session()->flash('success', 'Landlord Payment Cancelled Successfully');
	  return redirect()->route('landlordPayment.index');

	}
	public function landlordPaymentAction($landlord_id,$status,$stage){

	 LandlordPayment::where('id',$landlord_id)->update([
	  'landlord_payment_approval_status' => $stage,
	  'landlord_payment_status' => $status,
	  'updated_by' => \Auth::user()->id]);

	 RequestHistory::create([
	  'requestable_id' => $landlord_id,
	  'requestable_type' => 'use Modules\BackOffice\Entities\LandlordPayment',
	  'action' => $stage,
	  'created_by' => \Auth::user()->id]);
	 $user = \Auth::user();
	 $landlordPayment = LandlordPayment::where('id',$landlord_id)->first();
	 $users = User::role(['accountant','finance_manager'])->get(); 
	 $users = array_flatten($users);

	 if($status == 1 && $stage == 2){
	  session()->flash('success', 'Landlord Payment Sent For Approval Successfully');
	  return redirect()->route('landlordPayment.index');
	}elseif($status == 2 && $stage == 4){
	  if ($user->hasPermissionTo('landlord_payment_approval_approve') && $user->hasPermissionTo('sent_for_approval')) {
		  $landlordPayment->href = url('landlordPayment/'.$landlordPayment->id);
		  event(new LandlordPaymentApprove($landlordPayment,$users)); 
	  }

	  return redirect()->back()->with('success', 'Landlord Payment Approved Successfully');
	}elseif($status == 1 && $stage == 5){

	  $landlordPayment->href = url('landlordPayment/'.$landlordPayment->id);
	  event(new LandlordPaymentReject($landlordPayment,$users));

	  session()->flash('success', 'Landlord Payment Rejected');
	  return redirect()->route('landlordPaymentApproval');
	}elseif($status == 1 && $stage == 3){
	  session()->flash('success', 'Landlord Payment Sent For UnApprove');
	  return redirect()->route('landlordPayment.index');
	}elseif($status == 4 && $stage == 1){
	  //session()->flash('success', 'Landlord Payment UnApproved');
	  return redirect()->back()->with('success', 'Landlord Payment UnApproved');
	}
	else{
	 session()->flash('success', 'Landlord Payment');
	 return redirect()->route('landlordPaymentApproval');
	}

	}
	public function landlordPaymentPost($landlord_id,$status){
       
	  $landlordPaymentInfo 	= LandlordPayment::where('id',$landlord_id)->first();
	  $cheque_book_id = isset($landlordPaymentInfo->bankInfo->bank_chequebook_id)?$landlordPaymentInfo->bankInfo->bank_chequebook_id:'';

	  $response	=	Dynamics::PaymentJournalAxHeaderPushData('AXPaymentJournalHeader');

	  if($response=='Error'){
		
			return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
			
	  }
	  // 'Invoice'=>$landlordPaymentInfo->landlordInvoice->landlord_invoice_voucher_no,

	  $paymentLineItem[] = array(
				'JournalNum'=>$response,
				'JournalName'=>PAYMENT_JOURNAL_NAME, // PLM-PAY
				'PaymentDate'=> $landlordPaymentInfo->landlord_payment_date->format('Y-m-d'),
				'Description'=> $landlordPaymentInfo->landlord_payment_comment,
				'vendAccount'=> $landlordPaymentInfo->landlordContract->vendorinfo->vendor_code,
				'currency'=> CURRENCY,
				'accountType'=> 'VENDOR',
				'paymentMethod'=>($landlordPaymentInfo->landlordPaymentInfo==1)?'Cash':'Cheque',
				'checkBookid'=>'',
				'documentNo'=> '',
				'DebitCredit'=>'Debit',
				'voucher'=>$landlordPaymentInfo->landlord_payment_no,
				'Amount'=> $landlordPaymentInfo->landlord_payment_amount,
				'Remarks'=>$landlordPaymentInfo->maintenance_invoice_desc,
				'Invoice'=>'',
				'dimension1'=> 'Building',
				'dimension1value'=>isset($landlordPaymentInfo->landlordContract->buildingInfo->building_code)?$landlordPaymentInfo->landlordContract->buildingInfo->building_code:'000',
				'dimension2'=> 'Division',
				'dimension2value'=>isset($landlordPaymentInfo->landlordContract->buildingInfo->ax_division)?$landlordPaymentInfo->landlordContract->buildingInfo->ax_division:'000',
				'dimension3'=> 'Employee',
				'dimension3value'=>'00000',
				'dimension4'=> 'Location',
				'dimension4value'=>'00',
				'dimension5'=> 'Projects',
				'dimension5value'=>'00',
				'DataAreaId'=>DATA_AREA_ID,
				'company'=>COMPANY,
			);
	  $paymentLineItem[] = array(
				'JournalNum'=>$response,
				'JournalName'=>PAYMENT_JOURNAL_NAME,
				'PaymentDate'=> $landlordPaymentInfo->landlord_payment_date->format('Y-m-d'),
				'Description'=> $landlordPaymentInfo->landlord_payment_comment,
				'vendAccount'=> $cheque_book_id,
				'currency'=> CURRENCY,
				'accountType'=> 'BANK',
				'paymentMethod'=>($landlordPaymentInfo->landlordPaymentInfo==1)?'Cash':'Cheque',
				'checkBookid'=>'',
				'documentNo'=> '',
				'DebitCredit'=>'Credit',
				'voucher'=>$landlordPaymentInfo->landlord_payment_no,
				'Amount'=> $landlordPaymentInfo->landlord_payment_amount,
				'Remarks'=> $landlordPaymentInfo->maintenance_invoice_desc,
				'Invoice'=>'',
				'dimension1'=>'Building',
				'dimension1value'=>'000',
				'dimension2'=>'Division',
				'dimension2value'=> AX_DIVISION_HO,
				'dimension3'=>'Employee',
				'dimension3value'=>'00000',
				'dimension4'=> 'Location',
				'dimension4value'=>'00',
				'dimension5'=> 'Projects',
				'dimension5value'=>'00',
				'DataAreaId'=>DATA_AREA_ID,
				'company'=>COMPANY,
			);

	  $result = Dynamics::PaymentJournalAxLineItemPushData('AXPaymentLineItem', $paymentLineItem);
	  if(count($paymentLineItem) >0 && $result=='Error'){
			return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
	  }	
	 
	  LandlordPayment::where('id',$landlord_id)->update([
		'landlord_payment_status' => $status,
		'landlord_payment_posted_by' => \Auth::user()->id,
		'landlord_payment_posted_date' => date('Y-m-d'),
		'ax_batch_id'=> $result,
		'ax_payment_no'=>$landlordPaymentInfo->landlord_payment_no,
		'updated_by' => \Auth::user()->id]);

	  $sumOfPaid = $this->invoicePostBalanceAmount($landlordPaymentInfo->landlord_invoice_id);
	 
	  // If sum of landlord payment against a invoice is equal update 
	  // column landlord_payment_cheque_no in landlordInvoice to 2.
	  if($sumOfPaid->total == $landlordPaymentInfo->landlord_payment_invoice_amt){

	  		LandlordInvoice::where('id', $landlordPaymentInfo->landlord_invoice_id)->update([
	  			'is_invoice_payment_status'=>2,
	  			]);
	  		$check = "Inserted";
	  }
	  RequestHistory::create([
		'requestable_id' => $landlord_id,
		'requestable_type' => 'use Modules\BackOffice\Entities\LandlordPayment',
		'action' => 7,
		'created_by' => \Auth::user()->id]);

	  session()->flash('success', 'Landlord Payment Posted Successfully');
	  return redirect()->route('landlordPayment.index');
	}


	public function landlordPaymentApproval(Request $request){
	  $name = Route::currentRouteName();

	  $enquiry_fields = [
	  'landlord_payment_no' => 'Payment No',
	  'landlordContract__vendorInfo__vendor_name' => 'Landlord',
	  'landlordContract__landlord_contract_no' => 'Agreement No',
	  'landlordInvoice__landlord_invoice_voucher_no' => 'Invoice No',
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

	  $landlordPaymentApprovals = LandlordPayment::whereIn('landlord_payment_status',[1,4])
												 ->whereIn('landlord_payment_approval_status',[2,3])
												 ->filter($request)
												 ->sortable()
												 ->paginate($this->noOfRecord);
	  
	  $route   =  $request->url();

	  if(isset($request->ajax))
		return view('backoffice::Transaction.landlord_payment_approval_list_ajax',compact('landlordPaymentApprovals','request','route'));

	  return view('backoffice::Transaction.landlord_payment_approval_list',compact('landlordPaymentApprovals','request','enquiry_fields','operations','name','route'));

	}
	public function landlordPaymentApprovalShow($id){
	 $landlordPaymentApproval =  LandlordPayment::where('id',$id)->first();
	 return view('backoffice::Transaction.landlord_payment_approval_view',compact('landlordPaymentApproval'));
	}
	public function landlordPaymentApprovalSearch(Request $request,$result = array()){
		
		 $name = Route::currentRouteName();
		 $enquiry_fields = [
		 'landlord_payment_no' => 'Payment No',
		 'vendor_name' => 'Landlord',
		 'landlord_contract_no' => 'Agreement No',
		 'landlord_invoice_voucher_no' => 'Invoice No',
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

		  $landlordPaymentApproval =   new LandlordPaymentController;       
		  $result =     $landlordPaymentApproval->landlordPaymentSearch($request); 
		  $request->flash(); 

		}

		$landlordPaymentApprovals = LandlordPayment::whereIn('landlord_payment_status',[1,4])->whereIn('landlord_payment_approval_status',[2,3])->closure($result)->sortable()->paginate(10);
		if(isset($request->route))
		  $route   =  $request->route;

		if(isset($request->ajax))
		  return view('backoffice::Transaction.landlord_payment_approval_list_ajax',compact('landlordPaymentApprovals','request','route'));

		return view('backoffice::Transaction.landlord_payment_approval_list',compact('landlordPaymentApprovals','request','enquiry_fields','operations','name'));
		
	}
	public function invoiceBalanceAmount(Request $request){
		
	  $landlord_invoice_id = $request->landlord_invoice_id; 
	  $sumPaymentAmt = LandlordPayment::where('landlord_invoice_id',$landlord_invoice_id)->select(DB::raw('sum(cast(landlord_payment_amount as double precision)) AS total'))->first();

	  return json_encode($sumPaymentAmt);
	  
	}
	public function landlordPaymentCode(){

      $prefix  = prefixData('landlord_payment_prefix')->configuration_value.prefixData('landlord_payment_prefix')->configuration_year;
      
      $incVal  = prefixData('landlord_payment_prefix')->configuration_increment_value;
      
      $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);
      
      return array('code'=>$nextCode,'inc'=>$incVal);

    }
    /*
	*	Check payment complete or not as per landlord invoice 
	*
	*/
    public function invoicePostBalanceAmount($landlord_invoice_id){
	
	  if(!isset($landlord_invoice_id))
	  	return 0;

	  $landlord_invoice_id = $landlord_invoice_id; 
	  $sumPaymentAmt = LandlordPayment::where('landlord_invoice_id',$landlord_invoice_id)->select(DB::raw('sum(cast(landlord_payment_amount as double precision)) AS total'))->first();

	  return $sumPaymentAmt;
	  
	}
	
}
