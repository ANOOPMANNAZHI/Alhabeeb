<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\BackOffice\Entities\DepositRefund;
use Modules\Sales\Entities\TenantContract;
use Modules\Masters\Entities\Building;
use Modules\BackOffice\Entities\RequestHistory;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Entities\DimDetail;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\AccountParams;
use Modules\BackOffice\Entities\DepositRefundDimension;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Bank;
use Modules\Sales\Entities\Tenant;
use Modules\Masters\Entities\Legal;
use App\Setting;
use DB;
use Session;
use URL;
use Route;
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Events\DepositRefundApprove;
use Modules\BackOffice\Events\DepositRefundReject;
use Modules\Masters\Emails\LegalEmail;
use Dynamics;
use Modules\BackOffice\Entities\DepositRefundReceipt;
use Modules\BackOffice\Services\DepositRefundReceiptIssuer;
//use App\Setting;

class DepositRefundController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');  
    $this->middleware('permission:deposit_refund_add', ['only' => ['create','store']]);
    $this->middleware('permission:deposit_refund_edit', ['only' => ['edit','update']]); 
    $this->middleware('permission:deposit_refund_view', ['only' => ['index','show']]); 

    $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value;            

  }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request,$result = array())
    {
      $name = Route::currentRouteName();

      $enquiry_fields = [
      'deposit_refund_no' => 'Refund No',
      'receiptGeneration__receipts_generation_receipt_no' => 'Deposit Receipt No',
      'tenantContract__tenant_contract_no' => 'Agreement No',
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

      $depositRefunds = DepositRefund::filter($request)
                                      ->sortable()
                                      ->paginate($this->noOfRecord);  
       
      $route   =  $request->url();

      if(isset($request->ajax))
        return view('backoffice::Transaction.deposit_refund_list_ajax',compact('depositRefunds','request','route'));

      return view('backoffice::Transaction.deposit_refund_list',compact('depositRefunds','request','enquiry_fields','operations','name','route'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
     public function create()
    {
      $depositLatest = DepositRefund::withTrashed()->orderBy('id','desc')->limit(1)->first();
     // $prefix       = prefixData('deposit_refund_prefix')->configuration_value;
      $banks = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();  
      // Distribution Dimension details
      $acc_parameter  = AccountParams::where('acc_params_tran_desc','Deposit_refund')->first();
      
   //   if(!empty($depositLatest))
   //     $nextCode = $prefix.str_pad($depositLatest->id+1,4,'0',STR_PAD_LEFT);
   //   else
    //    $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
        $year    = prefixData('general_ledger_prefix')->configuration_year;
        $isYearCorrect = (date('y') == $year)?true:false;

        $generateCode = $this->depositRefundCode();
        $nextCode = $generateCode['code'];
         
      $accountCodes = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_dr_acc)->first();
      
      
     return view('backoffice::Transaction.add_deposit_refund',compact('nextCode','acc_parameter','accountCodes','banks','isYearCorrect'));
   }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
      //dd($request->all());
      $user = \Auth::user();
      $permissionExist = $user->hasPermissionTo('deposit_refund_approval_approve');
      if($permissionExist){
        $deposit_refund_approval_status = 4;
        $deposit_refund_status = 2;
      }else{
        $deposit_refund_approval_status = 0;
        $deposit_refund_status = 1;
      }
      $refundLatest = DepositRefund::withTrashed()->orderBy('id','desc')->limit(1)->first();
//      $prefix       = prefixData('deposit_refund_prefix')->configuration_value;
    //  if(!empty($refundLatest))
    //    $nextCode = $prefix.str_pad($refundLatest->id+1,4,'0',STR_PAD_LEFT);
   //   else
   //     $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
        $generateCode = $this->depositRefundCode();
        $nextCode = $generateCode['code'];
      $this->validate($request, [
        'deposit_refund_no' => 'required',                    
        'deposit_refund_date'   => 'required|date|after:yesterday',
        'tenant_contract_id'   => 'required',
        'receipts_generation_id'   => 'required',
        'deposit_refund_payment_method'   => 'required',
        'deposit_refund_amt'   => 'required',
        'deposit_refund_valid_from'   => 'required',
        'deposit_refund_valid_to'   => 'required',
        ]);
      if($request['deposit_refund_payment_method'] == 1){
        $cheque_no = $request['deposit_refund_cheque_no'];
      }else{
        $cheque_no = NULL;
      }
          
		$depositRefund = DepositRefund::create([              
			'deposit_refund_no' => $nextCode,
			'deposit_refund_date' => $request['deposit_refund_date'],
			'tenant_contract_id' => $request['tenant_contract_id'],
			'receipts_generation_id' => $request['receipts_generation_id'],
			'deposit_refund_payment_method' => $request['deposit_refund_payment_method'],
			'deposit_refund_cheque_no' => $cheque_no,
			'bank_id' => $request['bank_id'],
			'deposit_refund_amt' => replaceCommaWithDot($request['deposit_refund_amt']),
			'deposit_refund_valid_from' => $request['deposit_refund_valid_from'],
			'deposit_refund_valid_to' => $request['deposit_refund_valid_to'],
			'deposit_refund_comment' => $request['deposit_refund_comment'],
			'ax_batch_id' => $request['ax_batch_id'],
			'ax_invoice_no' => $request['ax_invoice_no'],
			'dim1_type' =>'Modules\BackOffice\Entities\DimDetail',
			'dim1_id' => ($request['financial_dimension']=='HO')?AX_DIVISION_HO:AX_DIVISION_PLMS, 
			'dim2_type' =>'Modules\Masters\Entities\Building',
			'dim2_id' =>  $request['building_id'], 
			'deposit_refund_status' => $deposit_refund_status,
			'deposit_refund_approval_status' => $deposit_refund_approval_status,
			'created_by' => \Auth::user()->id
        ]); 
	$bankinfo = Bank::Where('id', $request['bank_id'])->first();

    if(count($request->account_id) > 0){

           foreach ($request->account_id as $key => $value) {
              
                $acc_code = isset($bankinfo->bank_chequebook_id)?$bankinfo->bank_chequebook_id:0;
                $acc_desc = null;
                $account_code  =   AccountCodes::find($value);    
                if(isset($account_code->acc_code_val)){
                    $acc_code = $account_code->acc_code_val;
                    $acc_desc = $account_code->acc_code_desc;
                }

                $depositRefund->depositRefundDimension()->create([                  
                  'ac_codes_id' =>($value==0)?null:$value,
                  'account_code' =>$acc_code, 
                  'description' => $acc_desc,
                  'type' => $request['acc_type'][$key],
                  'debit_amount' => isset($request->debit_amt[$key])? replaceCommaWithDot($request->debit_amt[$key]): 0.0,
                  'credit_amount' => isset($request->credit_amt[$key])? replaceCommaWithDot($request->credit_amt[$key]): 0.0,
                  'dim1_type' =>  NULL,
                  'dim1_id' => NULL, 
                  'dim2_type' => NULL,
                  'dim2_id' => NULL, 
                  ]); 
              }
               
          
      }
       Setting::where('configuration_settings','general_ledger_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);

      // Customer receipt for whatever was withheld from the deposit.
      // Issues nothing when the refund has no deduction. Never posts to AX.
      $receipt = (new DepositRefundReceiptIssuer())->issueFor(
          $depositRefund->fresh('depositRefundDimension'), \Auth::user()->id
      );

      session()->flash('success', $receipt
          ? 'Deposit Refund Created Successfully. Deduction receipt ' . $receipt->receipt_no . ' issued.'
          : 'Deposit Refund Created Successfully');
      return redirect()->route('depositRefund.index');
    }

    /**
     * Issue the customer deduction receipt by hand, for a refund that
     * qualifies but has none yet (for example one saved before this existed).
     */
    public function generateDepositReceipt(DepositRefund $depositRefund)
    {
        $issuer = new DepositRefundReceiptIssuer();

        if ($issuer->existingFor($depositRefund)) {
            session()->flash('error', 'A deduction receipt already exists for this refund.');
            return redirect()->route('depositRefund.show', $depositRefund->id);
        }

        $receipt = $issuer->issueFor($depositRefund, \Auth::user()->id);

        if (!$receipt) {
            session()->flash('error', 'Nothing was deducted from this deposit, so there is no receipt to issue.');
            return redirect()->route('depositRefund.show', $depositRefund->id);
        }

        session()->flash('success', 'Deduction receipt ' . $receipt->receipt_no . ' issued.');
        return redirect()->route('depositRefund.show', $depositRefund->id);
    }

    /**
     * The printable customer receipt.
     */
    public function viewDepositReceipt(DepositRefundReceipt $depositRefundReceipt)
    {
        $depositRefundReceipt->load(['lines', 'depositRefund', 'createdBy']);

        return view('backoffice::Transaction.deposit_refund_receipt_print', [
            'receipt' => $depositRefundReceipt,
        ]);
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show(DepositRefund $depositRefund)
    {
      clearNotification('Modules\BackOffice\Notifications\DepositRefundNotification',$depositRefund->id);
      readNotification('Modules\BackOffice\Notifications\DepositRefundNotification',$depositRefund->id);
      $creditAmount = DepositRefundDimension::where('deposit_refund_id',$depositRefund->id)->sum('credit_amount');
      $debitAmount = DepositRefundDimension::where('deposit_refund_id',$depositRefund->id)->sum('debit_amount');

      // Customer receipt for the amounts withheld from the deposit.
      // Shown only when something was actually deducted.
      $issuer = new DepositRefundReceiptIssuer();
      $depositReceipt  = $issuer->existingFor($depositRefund);
      $canIssueReceipt = !$depositReceipt && $issuer->qualifies($depositRefund);

      return view('backoffice::Transaction.deposit_refund_view',compact('depositRefund','creditAmount','debitAmount','depositReceipt','canIssueReceipt'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(DepositRefund $depositRefund)
    {
      $accountCodes = AccountCodes::get();

      $first_account_code = $accountCodes->first();

      $first_account_dim1 = $first_account_code->dim1;
      $first_account_dim2 = $first_account_code->dim2;
	  $year    = prefixData('general_ledger_prefix')->configuration_year;
      $isYearCorrect = (date('y') == $year)?true:false;

      // Distribution Dimension details
      $acc_parameter  = AccountParams::where('acc_params_tran_desc','Deposit_refund')->first();

     $units = Unit::whereHas('tenantContract.receiptGeneration')->where('building_id',$depositRefund->tenantContract->building_id)->where('unit_status',1)->get();
     $creditAmount = DepositRefundDimension::where('deposit_refund_id',$depositRefund->id)->sum('credit_amount');
     $debitAmount = DepositRefundDimension::where('deposit_refund_id',$depositRefund->id)->sum('debit_amount');

     $accountCodes = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_dr_acc)->first();

     $banks = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();
     return view('backoffice::Transaction.add_deposit_refund',compact('depositRefund','accountCodes','acc_parameter','units','creditAmount','debitAmount','banks','isYearCorrect'));
   }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,DepositRefund $depositRefund)
    {
      $this->validate($request, [                   
        'deposit_refund_date'   => 'required|date',
        'tenant_contract_id'   => 'required',
        'receipts_generation_id'   => 'required',
        'deposit_refund_payment_method'   => 'required',
        'deposit_refund_amt'   => 'required',
        'deposit_refund_valid_from'   => 'required',
        'deposit_refund_valid_to'   => 'required',
        ]);
      if($request['deposit_refund_payment_method'] == 1){
        $cheque_no = $request['deposit_refund_cheque_no'];
      }else{
        $cheque_no = NULL;
      }
      $depositRefund->update([
        'deposit_refund_date' => $request['deposit_refund_date'],
        'tenant_contract_id' => $request['tenant_contract_id'],
        'receipts_generation_id' => $request['receipts_generation_id'],
        'deposit_refund_payment_method' => $request['deposit_refund_payment_method'],
        'deposit_refund_cheque_no' => $cheque_no,
        'bank_id' => $request['bank_id'],
        'deposit_refund_amt' => replaceCommaWithDot($request['deposit_refund_amt']),
        'deposit_refund_valid_from' => $request['deposit_refund_valid_from'],
        'deposit_refund_valid_to' => $request['deposit_refund_valid_to'],
        'deposit_refund_comment' => $request['deposit_refund_comment'],
        'ax_batch_id' => $request['ax_batch_id'],
        'ax_invoice_no' => $request['ax_invoice_no'],
        'dim1_type' =>'Modules\BackOffice\Entities\DimDetail',
        'dim1_id' => $request['building_id'], 
        'dim2_type' =>'Modules\Masters\Entities\Building',
        'dim2_id' =>  $request['building_id'], 
        'updated_by' => \Auth::user()->id,
        ]);
	
	  $bankinfo = Bank::Where('id', $request['bank_id'])->first();
      if(count($request->account_id) > 0){  
       
        $depositRefund->depositRefundDimension()->delete();

        foreach ($request->account_id as $key => $value) { 

          $acc_code = isset($bankinfo->bank_chequebook_id)?$bankinfo->bank_chequebook_id:0;
          $acc_desc = null;
          $account_code  =   AccountCodes::find($value);    
          if(isset($account_code->acc_code_val)){
              $acc_code = $account_code->acc_code_val;
              $acc_desc = $account_code->acc_code_desc;
          }           
          $depositRefund->depositRefundDimension()->create([
                'ac_codes_id' =>($value==0)?null:$value,
                'account_code' =>$acc_code, 
                'description' => $acc_desc,
                'type' => $request['acc_type'][$key],
                'debit_amount' => isset($request->debit_amt[$key])? replaceCommaWithDot($request->debit_amt[$key]): 0.0,
                'credit_amount' => isset($request->credit_amt[$key])? replaceCommaWithDot($request->credit_amt[$key]): 0.0,
                'dim1_type' =>  NULL,
                'dim1_id' => NULL, 
                'dim2_type' => NULL,
                'dim2_id' => NULL, 
           ]);

        }
      }
      session()->flash('success', 'Deposit Refund Updated Successfully');
      return redirect()->route('depositRefund.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
    public function buildingDepositRefundAutocompleteCode(Request $request){
      $key = $request->term;

      $building =   Building::active()->whereHas('tenantContract.receiptGeneration')->where('building_status',1)->where('building_name', 'ILIKE', '%'.$key.'%')
      ->select('id AS ids',DB::raw("CONCAT(building_name) as value"),'building_code AS code')
      ->get();
      return $building ;
    }
    public function getUnitAutocompleteCode(Request $request){
      $key = $request->term;

      $unit =   Unit::active()->whereHas('tenantContract.receiptGeneration')->where('unit_code', 'ILIKE', '%'.$key.'%')
      ->select('id AS ids',DB::raw("CONCAT(unit_code) as value"),'unit_no AS code')
      ->get();
      //dd($unit);
      return $unit ;
    }
    public function unitDetailsByBuildingId(Request $request){
      $id = $request->input('id'); 
    //  $units = Unit::whereHas('tenantContract.receiptGeneration')->where('building_id',$id)->where('unit_status',1)->get();
	 $units = Unit::where('building_id',$id)->where('unit_status',1)->get();
      return json_encode($units);
    }
    public function buildingDetailsByUnitId(Request $request){
      $unit = $request->input('id'); 
      $buildings = Building::whereHas('unitInfo', function ($query)use($unit) {
          $query->where('id','=', $unit);
      })->first();
      //dd($buildings);
      return json_encode($buildings);
    }
    public function getTenantDetailsByBuildingUnit(Request $request){
      $building = $request->building;
      $unit = $request->unit;
      $tenants = Tenant::whereHas('tenantContracts', function ($query)use($building,$unit) {
          $query->where('building_id','=', $building)->where('unit_id','=', $unit);
      })->get();
      return json_encode($tenants);
    }
    public function getTenantDetailsByUnitId(Request $request){
      $unit = $request->unit;
      $tenants = Tenant::whereHas('tenantContracts', function ($query)use($unit) {
          $query->where('unit_id','=', $unit);
      })->get();
      return json_encode($tenants);
    }
    public function getUnitDetailsByTenantId(Request $request){
      $tenant = $request->id;
      //dd($tenant);
      $units = Unit::whereHas('tenantContract', function ($query)use($tenant) {
          $query->where('tenant_id','=', $tenant);
      })->get();
      return json_encode($units);
    }
    public function getReceiptDetailsByBuildingUnitTenant(Request $request){
      $building = $request->building;
      $unit = $request->unit;
      $tenant = $request->tenant;
     // dd($tenant);
      $receipts = ReceiptsGeneration::whereHas('tenantContractInfo', function ($query)use($building,$unit,$tenant) {
          $query->where('building_id','=', $building)->where('unit_id','=', $unit)->where('tenant_id',$tenant);
      })->where('receipts_generation_type',2)->get();
      //dd($receipts);
      return json_encode($receipts);
    }
    public function getReceiptDetailsByUnitTenant(Request $request){
      $unit = $request->unit;
      $tenant = $request->tenant;
      $building = Unit::where('id',$unit)->first()->building_id;
     // dd($building);
      $receipts = ReceiptsGeneration::whereHas('tenantContractInfo', function ($query)use($building,$unit,$tenant) {
          $query->where('building_id','=', $building)->where('unit_id','=', $unit)->where('tenant_id',$tenant);
      })->where('receipts_generation_type',2)->get();
      //dd($receipts);
      return json_encode($receipts);
    }
    public function getReceiptDetails(Request $request){
       $receiptNo = $request->receiptNo;
       $receiptsGeneration = ReceiptsGeneration::where('id',$receiptNo)->first();
       $contract = $receiptsGeneration->tenantContractInfo;

       $buildings = Building::where('id',$contract->building_id)->first();
       $units = Unit::where('id',$contract->unit_id)->first();
       $tenants = Tenant::where('id',$contract->tenant_id)->first();
       return json_encode(array($contract,$receiptsGeneration,$buildings,$units,$tenants));
    }
    public function tenantDetailsByBuildingUnit(Request $request){

      $building = $request->building;
      $unit = $request->unit;
      $contract =  TenantContract::active()->where('building_id',$building)->where('unit_id',$unit)->first();
      //dd($contract);
      $tenant = $contract->tenant;
      $unit = $contract->unit;
      $receiptGeneration = $contract->receiptGeneration;
      $buildings = $contract->building;
      //dd($buildings);
      return json_encode(array($contract,$tenant,$unit,$receiptGeneration,$buildings));

    }
    public function tenantDepositRefundAutocompleteCode(Request $request){
      $key = $request->term;

      $tenant =   Tenant::whereHas('tenantContract.receiptGeneration')->where('tenant_name', 'ILIKE', '%'.$key.'%')
      ->select('id AS ids',DB::raw("CONCAT(tenant_name) as value"),'tenant_code AS code')
      ->get();
      //dd($tenant);
      return $tenant ;
    }
    public function buildingDetailsByTenantId(Request $request){
      $id = $request->input('id'); 
      $building = Building::active()->whereHas('tenantContract.receiptGeneration')->whereHas('tenantContract', function ($query)use($id) {
                                 $query->where('tenant_id',$id);     
                                 return $query;
  })->get();


      //whereHas('tenantContract.tenant_id',$id)->get();
      //dd($building);

 


      return json_encode($building);
    }
  //Advance search
    public function depositRefundSearch(Request $request){

      $closure = array();
      $closure_or = array();

      $contract_q = array();
      $contract_or = array();
      $receipt_q = array();
      $receipt_or = array();

      if(isset($request->fieldName)){
       if(count($request->fieldName) > 0){

        foreach ($request->fieldName as $key => $value) {

          if( !empty($request->fieldValue[$key]) && !empty($request->fieldValue[$key]) && !empty($value) ) {

           $operation = $request->operation[$key];
           $fieldValue = $request->fieldValue[$key];

           if($request->operation[$key] == 'ilike%...%' ){
            $fieldValue = '%'.$request->fieldValue[$key].'%';
            $operation = 'ilike';
          }if($value == 'tenant_contract_no'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $contract_or[] = array( $value , $operation ,$fieldValue);
            else
              $contract_q[] = array( $value , $operation ,$fieldValue);

          }elseif($value == 'receipts_generation_receipt_no'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $receipt_or[] = array( $value , $operation ,$fieldValue);
            else
              $receipt_q[] = array( $value , $operation ,$fieldValue);

          }else{                
            $fieldValue = $request->fieldValue[$key];
            $operation = $request->operation[$key];
          }

          if($value != 'tenant_contract_no' && $value != 'receipts_generation_receipt_no' &&  (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
           if($key != 0 && $request->logic[$key -1 ] == 'or' )
             $closure_or[] = array( $value , $operation ,$fieldValue);
           else
             $closure[] = array( $value , $operation ,$fieldValue);
         }

       }


     }


     if($request->ajax != true){
       if(count($contract_or) == 0  && count($closure) == 0 &&  count($contract_q) == 0 &&  count($receipt_q) == 0 &&  count($receipt_or) == 0 )
        $closure[] = array( 'id' , '=' ,0);
    }

  }

}

$deposit_refund_no = (isset($request->deposit_refund_no)) ? $request->deposit_refund_no : null;
$deposit_refund_date = (isset($request->deposit_refund_date)) ? $request->deposit_refund_date : null;


$receipts_generation_id = (isset($request->receipts_generation_id)) ? $request->receipts_generation_id : null;    
$building_id = (isset($request->building_id)) ? $request->building_id : null;    
$building_code = (isset($request->building_code)) ? $request->building_code : null;    
$unit_id = (isset($request->unit_id)) ? $request->unit_id : null;    
$tenant_name = (isset($request->tenant_name)) ? $request->tenant_name : null;    
$tenant_code = (isset($request->tenant_code)) ? $request->tenant_code : null;    
$tenant_contract_id = (isset($request->tenant_contract_id)) ? $request->tenant_contract_id : null;    
$deposit_refund_payment_method = (isset($request->deposit_refund_payment_method)) ? $request->deposit_refund_payment_method : null;    
$deposit_refund_amt = (isset($request->deposit_refund_amt)) ? $request->deposit_refund_amt : null;    
$deposit_refund_approval_status = (isset($request->deposit_refund_approval_status)) ? $request->deposit_refund_approval_status : null;    


$qiuck_search = array($deposit_refund_no, $receipts_generation_id , $building_id, $building_code,$deposit_refund_date,$unit_id,$tenant_name,$tenant_code,$tenant_contract_id,$deposit_refund_payment_method,$deposit_refund_amt,$deposit_refund_approval_status);
if($request->ajax != true){
}
$result = array(
  $closure,
  $closure_or,$contract_q,$contract_or,$receipt_q,$receipt_or,$qiuck_search ); 
return $result;    


}
public function enquiryFilter(){
 $enquiry_fields = [
 'deposit_refund_no' => 'Refund No',
 'receipts_generation_receipt_no' => 'Deposit Receipt No',
 'tenant_contract_no' => 'Agreement No',
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
public function depositSearch(Request $request,$result = array()){
  $name = Route::currentRouteName();
  $enquiry_fields = [
  'deposit_refund_no' => 'Refund No',
  'receipts_generation_receipt_no' => 'Deposit Receipt No',
  'tenant_contract_no' => 'Agreement No',
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

    $depositRefund =   new DepositRefundController;       
    $result =     $depositRefund->depositRefundSearch($request); 
    $request->flash(); 

  }

  $depositRefunds = DepositRefund::closure($result)->sortable()->paginate(10);
   // dd($depositRefunds);
  if(isset($request->route))
    $route   =  $request->route;
  if(isset($request->ajax))
    return view('backoffice::Transaction.deposit_refund_list_ajax',compact('depositRefunds','request','route'));

  return view('backoffice::Transaction.deposit_refund_list',compact('depositRefunds','request','enquiry_fields','operations','name'));
}
public function cancelDepositRefund(Request $request){
 $id =$request['id'];
 return view('backoffice::Transaction.deposit_refund_cancel_modal',compact('id'));

}
public function cancelDepositRefundStore(Request $request,DepositRefund $depositRefund){

  $deposit_refund_id = $request['id'];
  $entity_desc = $request['entity_desc'];

  $depositRefund = DepositRefund::where('id',$deposit_refund_id)->update([
    'deposit_refund_cancelled_by' => \Auth::user()->id,
    'deposit_refund_cancelled_date' => date('Y-m-d'),
    'updated_by' => \Auth::user()->id]);

  RequestHistory::create([
    'requestable_id' => $deposit_refund_id,
    'requestable_type' => 'Modules\BackOffice\Entities\DepositRefund',
    'request_desc' => $entity_desc,
    'action' => 6,
    'created_by' => \Auth::user()->id]);

  DepositRefund::where('id',$deposit_refund_id)->delete();

  session()->flash('success', 'Deposit Refund Cancelled Successfully');
  return redirect()->route('depositRefund.index');

}
public function depositRefundAction($deposit_id,$status,$stage){
 
 $depositRefund = DepositRefund::where('id',$deposit_id)->first();
 DepositRefund::where('id',$deposit_id)->update([
  'deposit_refund_approval_status' => $stage,
  'deposit_refund_status' => $status,
  'updated_by' => \Auth::user()->id]);

 RequestHistory::create([
  'requestable_id' => $deposit_id,
  'requestable_type' => 'use Modules\BackOffice\Entities\DepositRefund',
  'action' => $stage,
  'created_by' => \Auth::user()->id]);

 $users = User::role(['accountant','finance_manager'])->get(); 
 $users = array_flatten($users);

 if($status == 1 && $stage == 2){
  session()->flash('success', 'Deposit Refund Sent For Approval Successfully');
  return redirect()->route('depositRefund.index');
}elseif($status == 2 && $stage == 4){
  if ($depositRefund->deposit_refund_approval_status == 2) {
    $depositRefund->href = url('depositRefund/'.$depositRefund->id);
    event(new DepositRefundApprove($depositRefund,$users)); 
 }
  session()->flash('success', 'Deposit Refund Approved Successfully');
  return redirect()->back()->with('success', 'Deposit Refund Approved Successfully');

}elseif($status == 1 && $stage == 5){

  $depositRefund->href = url('depositRefund/'.$depositRefund->id);
  event(new DepositRefundReject($depositRefund,$users));

  session()->flash('success', 'Deposit Refund Rejected');
  return redirect()->route('depositRefundApproval');
}elseif($status == 1 && $stage == 3){
  session()->flash('success', 'Deposit Refund Sent For UnApprove');
  return redirect()->route('depositRefund.index');
}elseif($status == 4 && $stage == 3){
  return redirect()->back()->with('success', 'Deposit Refund UnApproved');
}
else{
  return redirect()->back()->with('success', 'Deposit Refund');
}

}
public function depositRefundPost($deposit_id,$status){
	
  $ledgerHeader = array(
				'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
				'DataAreaId'=>DATA_AREA_ID,
				'company' =>COMPANY,
			);
		
	$response	=	Dynamics::LedgerAxHeaderPushData('AXGeneralLedgerHeader', $ledgerHeader);
	
	$depositRefundInfo = DepositRefund::where('id',$deposit_id)->first();	
	
	if($response=='Error'){
		return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
	}
	$accountType = AX_GL;
	
	if(count($depositRefundInfo->depositRefundDimension)>0){
			
		foreach($depositRefundInfo->depositRefundDimension as $key=>$item){
	
			if($item->type=='BANK'){
				
				$dimension1value 	= '000';
				$dimension2value	= '01';
			}
			else{
				$dimension1value 	= isset($depositRefundInfo->tenantContract->building->building_code)?$depositRefundInfo->tenantContract->building->building_code:'000';
				$dimension2value	= isset($depositRefundInfo->tenantContract->building->ax_division)?$depositRefundInfo->tenantContract->building->ax_division:'02';
			}
			$ledgerLineItem[] = array(
					'JournalNum'=> $response,
					'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
					'PaymentDate'=> $depositRefundInfo->deposit_refund_date->format('Y-m-d'),
					'Description'=> $depositRefundInfo->deposit_refund_comment,
					'Account'=> $item->account_code,
					'currency'=> CURRENCY,
					'accountType'=>$item->type,
					'paymentMethod'=>'',
					'checkBookid'=>'',
					'documentNo'=> '',
					'DebitCredit'=>($item->credit_amount>0)?'Credit':'Debit',
					'voucher'=>$depositRefundInfo->deposit_refund_no,
					'AmountCredit'=>($item->credit_amount>0)?$item->credit_amount:0,
					'AmountDebit'=>($item->debit_amount> 0)?$item->debit_amount:0,
					'Remarks'=>'',
					'Invoice'=>$depositRefundInfo->receiptGeneration->receipts_generation_receipt_no,
					'dimension1'=> 'Building',
					'dimension1value'=>$dimension1value,
					'dimension2'=> 'Division',
					'dimension2value'=>$dimension2value,
					'dimension3'=> 'Employee',
					'dimension3value'=>'00000',
					'dimension4'=> 'Location',
					'dimension4value'=>'00',
					'dimension5'=> 'Projects',
					'dimension5value'=>'00',
					'DataAreaId'=>DATA_AREA_ID,
					'company'=>COMPANY,
				);
				
			
		}
		//dd($ledgerLineItem);
		$result = Dynamics::LedgerAxLineItemPushData('AXGeneralLedgerLineItem', $ledgerLineItem);
		if(count($ledgerLineItem) >0 && $result=='Error'){
		 	return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
		}
		//dd($ledgerLineItem);
	 }
  DepositRefund::where('id',$deposit_id)->update([
    'deposit_refund_status' => $status,
    'deposit_refund_posted_by' => \Auth::user()->id,
    'deposit_refund_posted_date' => date('Y-m-d'),
	'ax_batch_id'=>$response,
	'ax_invoice_no'=>$depositRefundInfo->deposit_refund_no,
    'updated_by' => \Auth::user()->id]);

  RequestHistory::create([
    'requestable_id' => $deposit_id,
    'requestable_type' => 'Modules\BackOffice\Entities\DepositRefund',
    'action' => 7,
    'created_by' => \Auth::user()->id]);

  $depositRefund = DepositRefund::find($deposit_id);
  $tenantContract = TenantContract::find($depositRefund->tenant_contract_id);
  $legal = Legal::where('tenant_contract_id',$tenantContract->id)->first();
  if($tenantContract->status == 5){
    $legalUsers = User::role(['are','backoffice_manager','legal_advisor'])->get(); 
    $legalUsers = array_flatten($legalUsers);

   $legal->subject = "Deposit Refund Posted !"; 
  $legal->textContent = "Deposit Refund Posted which is under Legal";
  foreach($legalUsers as $legalUser){
    $mobile = $legalUser->employee->employee_contact_no ?? $legalUser->employee->employee_secondary_no;

    $msg = "Deposit Refund Posted";
    $params = 'optional data';
    if(!empty($legalUser->email)){
      Mail::to($legalUser->email)->send(new LegalEmail($legal,$legalUser)); //Email Notification
    }else{
       sendSms($mobile,$msg,$params); //SMS Notification
     }

   }
  //Notifications ends
  }

  session()->flash('success', 'Deposit Refund Posted Successfully');
  return redirect()->route('depositRefund.index');
}

public function depositRefundApproval(Request $request,$result = array()){

  $name = Route::currentRouteName();

  $enquiry_fields = [
  'deposit_refund_no' => 'Refund No',
  'receiptGeneration__receipts_generation_receipt_no' => 'Deposit Receipt No',
  'tenantContract__tenant_contract_no' => 'Agreement No',
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

  $depositRefundApprovals = DepositRefund::whereIn('deposit_refund_status',[1,4])
                                          ->whereIn('deposit_refund_approval_status',[2,3])
                                          ->filter($request)
                                          ->sortable()
                                          ->paginate($this->noOfRecord);
    //dd($depositRefundApprovals);
  
  $route   =  $request->url();
  
  if(isset($request->ajax))
    return view('backoffice::Transaction.deposit_refund_approval_list_ajax',compact('depositRefundApprovals','request','route'));

  return view('backoffice::Transaction.deposit_refund_approval_list',compact('depositRefundApprovals','request','enquiry_fields','operations','name','route'));
}




public function depositRefundApprovalShow($id){
  $depositRefundApproval =  DepositRefund::where('id',$id)->first();
  $creditAmount = DepositRefundDimension::where('deposit_refund_id',$depositRefundApproval->id)->sum('credit_amount');
  $debitAmount = DepositRefundDimension::where('deposit_refund_id',$depositRefundApproval->id)->sum('debit_amount');
  return view('backoffice::Transaction.deposit_refund_approval_view',compact('depositRefundApproval','creditAmount','debitAmount'));

}
public function depositRefundApprovalSearch(Request $request,$result = array()){
 $name = Route::currentRouteName();
 $enquiry_fields = [
 'deposit_refund_no' => 'Refund No',
 'receipts_generation_receipt_no' => 'Deposit Receipt No',
 'tenant_contract_no' => 'Agreement No',
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

  $depositRefundApproval =   new DepositRefundController;       
  $result =     $depositRefundApproval->depositRefundSearch($request); 
  $request->flash(); 

}

$depositRefundApprovals = DepositRefund::whereIn('deposit_refund_status',[1,4])->whereIn('deposit_refund_approval_status',[2,3])->closure($result)->sortable()->paginate(10);
    //dd($depositRefundApprovals);
if(isset($request->route))
  $route   =  $request->route;
if(isset($request->ajax))
  return view('backoffice::Transaction.deposit_refund_approval_list_ajax',compact('depositRefundApprovals','request','route'));

return view('backoffice::Transaction.deposit_refund_approval_list',compact('depositRefundApprovals','request','enquiry_fields','operations','name'));
}
/*
*Deposit Refund
*
*/
public function depositReceiptAutocompleteCode(Request $request){
  $key = $request->term;
  //dd($key);
  $depositReceipt = ReceiptsGeneration::with(['tenantContractInfo','tenantContractInfo.building','tenantContractInfo.Unit'])
                               //->has('tenantContractInfo')
                             ->whereHas('tenantContractInfo', function ($query)use($key) {
                                 $query->has('building');
                                 $query->has('Unit');         
                                 return $query;
  })
->where('receipts_generation_type',2)
->where('receipts_generation_receipt_no', 'ILIKE', '%'.$key.'%')
//->select('id AS ids',DB::raw("CONCAT(receipts_generation_receipt_no,building_name) as value"),'tenant_contract_id AS code')
  ->get();
  //dd($depositReceipt);
  $deposit = array();
  $i = 0;
  foreach($depositReceipt as $receipt){
    $tenant_contract_id = $receipt->tenant_contract_id;
    $receiptNo = $receipt->receipts_generation_receipt_no;
    $tenant = $receipt->tenantContractInfo->tenant->tenant_name;
    $building = $receipt->tenantContractInfo->building->building_name;
    $unit = $receipt->tenantContractInfo->Unit->unit_code;
    $deposit_val = $receiptNo .'-'. $tenant .'-'. $building .'-'.$unit;
    $deposit[$i]['ids'] = $receipt->id;
    $deposit[$i]['value'] = $deposit_val;
    $deposit[$i]['code'] = $tenant_contract_id;
    $deposit[$i]['building_name'] = $building;
    $deposit[$i]['building_code'] = $receipt->tenantContractInfo->building->building_code;
    $deposit[$i]['building_id'] = $receipt->tenantContractInfo->building->id;
    $deposit[$i]['unit_code'] = $receipt->tenantContractInfo->Unit->unit_code;
    $deposit[$i]['unit_no'] = $receipt->tenantContractInfo->Unit->unit_no;
    $deposit[$i]['tenant_name'] = $tenant;
    $deposit[$i]['tenant_code'] = $receipt->tenantContractInfo->tenant->tenant_code;
    $deposit[$i]['tenant_contract_no'] = $receipt->tenantContractInfo->tenant_contract_no;
    $deposit[$i]['receipts_generation_amt'] = $receipt->receipts_generation_amt;
    $deposit[$i]['tenant_contract_start_date'] = $receipt->tenantContractInfo->tenant_contract_start_date->format('Y-m-d');
    $deposit[$i]['tenant_contract_start_date1'] = $receipt->tenantContractInfo->tenant_contract_start_date;
    //dd($receipt->tenantContractInfo->tenant_contract_start_date->format('d/m/Y'));
    $deposit[$i]['tenant_contract_valid_to_date'] = $receipt->tenantContractInfo->tenant_contract_valid_to_date->format('Y-m-d');
    $deposit[$i]['tenant_contract_valid_to_date1'] = $receipt->tenantContractInfo->tenant_contract_valid_to_date;

    $i++;
  }
  //dd($deposit);
  return $deposit;
}
/*
*Deposit Refund - Building
*
*/
public function depositReceiptBuildingAutocompleteCode(Request $request){
  $key = $request->term;
  //dd($key);
$depositReceipt = Building::with(['tenantContract','tenantContract.receiptGeneration','tenantContract.Unit'])->whereHas('tenantContract', function ($query)use($key) {
                                 $query->has('receiptGeneration');
                                 $query->has('Unit');         
                                 return $query;
  })->get();
//dd($depositReceipt);
  $deposit = array();
  $i = 0;
  foreach($depositReceipt as $receipt){
    $tenant_contract_id = $receipt->tenant_contract_id;
    $receiptNo = $receipt->receipts_generation_receipt_no;
    $tenant = $receipt->tenantContract->tenant->tenant_name;
    $building = $receipt->building_name;
    $unit = $receipt->tenantContract->Unit->unit_code;
    $deposit_val =$building;
    $deposit[$i]['ids'] = $receipt->id;
    $deposit[$i]['value'] = $deposit_val;
    $deposit[$i]['code'] = $tenant_contract_id;
    $deposit[$i]['receipts_generation_receipt_no'] = $receiptNo .'-'. $tenant .'-'. $building .'-'.$unit;
    //$deposit[$i]['building_name'] = $building;
    $deposit[$i]['building_code'] = $receipt->building_code;
    $deposit[$i]['building_id'] = $receipt->id;
    $deposit[$i]['unit_code'] = $receipt->tenantContract->Unit->unit_code;
    $deposit[$i]['unit_no'] = $receipt->tenantContract->Unit->unit_no;
    $deposit[$i]['tenant_name'] = $tenant;
    $deposit[$i]['tenant_code'] = $receipt->tenantContract->tenant->tenant_code;
    $deposit[$i]['tenant_contract_no'] = $receipt->tenantContract->tenant_contract_no;
    $deposit[$i]['receipts_generation_amt'] = $receipt->tenantContract->receiptGeneration->receipts_generation_amt ?? '';
    $deposit[$i]['tenant_contract_start_date'] = $receipt->tenantContract->tenant_contract_start_date->format('Y-m-d');
    $deposit[$i]['tenant_contract_start_date1'] = $receipt->tenantContract->tenant_contract_start_date;
    //dd($receipt->tenantContractInfo->tenant_contract_start_date->format('d/m/Y'));
    $deposit[$i]['tenant_contract_valid_to_date'] = $receipt->tenantContract->tenant_contract_valid_to_date->format('Y-m-d');
    $deposit[$i]['tenant_contract_valid_to_date1'] = $receipt->tenantContract->tenant_contract_valid_to_date;

    $i++;
  }
  //dd($deposit);
  return $deposit;
}
/*
*Deposit Refund - Tenant
*
*/
public function depositReceiptTenantAutocompleteCode(Request $request){
  $key = $request->term;
  //dd($key);
  $depositReceipt = ReceiptsGeneration::with(['tenantContractInfo','tenantContractInfo.building','tenantContractInfo.Unit'])
                               //->has('tenantContractInfo')
                             ->whereHas('tenantContractInfo', function ($query)use($key) {
                                 $query->has('building');
                                 $query->has('Unit');         
                                 return $query;
  })
->where('receipts_generation_type',2)
//->select('id AS ids',DB::raw("CONCAT(receipts_generation_receipt_no,building_name) as value"),'tenant_contract_id AS code')
  ->get();
  //dd($depositReceipt);
  $deposit = array();
  $i = 0;
  foreach($depositReceipt as $receipt){
    $tenant_contract_id = $receipt->tenant_contract_id;
    $receiptNo = $receipt->receipts_generation_receipt_no;
    $tenant = $receipt->tenantContractInfo->tenant->tenant_name;
    $building = $receipt->tenantContractInfo->building->building_name;
    $unit = $receipt->tenantContractInfo->Unit->unit_code;
    $deposit_val =$tenant;
    $deposit[$i]['ids'] = $receipt->id;
    $deposit[$i]['value'] = $deposit_val;
    $deposit[$i]['code'] = $tenant_contract_id;
    $deposit[$i]['receipts_generation_receipt_no'] = $receiptNo .'-'. $tenant .'-'. $building .'-'.$unit;
    $deposit[$i]['building_name'] = $building;
    $deposit[$i]['building_code'] = $receipt->tenantContractInfo->building->building_code;
    $deposit[$i]['building_id'] = $receipt->tenantContractInfo->building->id;
    $deposit[$i]['unit_code'] = $receipt->tenantContractInfo->Unit->unit_code;
    $deposit[$i]['unit_no'] = $receipt->tenantContractInfo->Unit->unit_no;
    //$deposit[$i]['tenant_name'] = $tenant;
    $deposit[$i]['tenant_code'] = $receipt->tenantContractInfo->tenant->tenant_code;
    $deposit[$i]['tenant_contract_no'] = $receipt->tenantContractInfo->tenant_contract_no;
    $deposit[$i]['receipts_generation_amt'] = $receipt->receipts_generation_amt;
    $deposit[$i]['tenant_contract_start_date'] = $receipt->tenantContractInfo->tenant_contract_start_date->format('Y-m-d');
    $deposit[$i]['tenant_contract_start_date1'] = $receipt->tenantContractInfo->tenant_contract_start_date;
    //dd($receipt->tenantContractInfo->tenant_contract_start_date->format('d/m/Y'));
    $deposit[$i]['tenant_contract_valid_to_date'] = $receipt->tenantContractInfo->tenant_contract_valid_to_date->format('Y-m-d');
    $deposit[$i]['tenant_contract_valid_to_date1'] = $receipt->tenantContractInfo->tenant_contract_valid_to_date;

    $i++;
  }
  //dd($deposit);
  return $deposit;
}/*
*Deposit Refund - UNit
*
*/
public function depositReceiptUnitAutocompleteCode(Request $request){
  $key = $request->term;
  //dd($key);
  $depositReceipt = ReceiptsGeneration::with(['tenantContractInfo','tenantContractInfo.building','tenantContractInfo.Unit'])
                               //->has('tenantContractInfo')
                             ->whereHas('tenantContractInfo', function ($query)use($key) {
                                 $query->has('building');
                                 $query->has('Unit');         
                                 return $query;
  })
->where('receipts_generation_type',2)
//->select('id AS ids',DB::raw("CONCAT(receipts_generation_receipt_no,building_name) as value"),'tenant_contract_id AS code')
  ->get();
  //dd($depositReceipt);
  $deposit = array();
  $i = 0;
  foreach($depositReceipt as $receipt){
    $tenant_contract_id = $receipt->tenant_contract_id;
    $receiptNo = $receipt->receipts_generation_receipt_no;
    $tenant = $receipt->tenantContractInfo->tenant->tenant_name;
    $building = $receipt->tenantContractInfo->building->building_name;
    $unit = $receipt->tenantContractInfo->Unit->unit_code;
    $deposit_val =$tenant;
    $deposit[$i]['ids'] = $receipt->id;
    $deposit[$i]['value'] = $unit;
    $deposit[$i]['code'] = $tenant_contract_id;
    $deposit[$i]['receipts_generation_receipt_no'] = $receiptNo .'-'. $tenant .'-'. $building .'-'.$unit;
    $deposit[$i]['building_name'] = $building;
    $deposit[$i]['building_code'] = $receipt->tenantContractInfo->building->building_code;
    $deposit[$i]['building_id'] = $receipt->tenantContractInfo->building->id;
   // $deposit[$i]['unit_code'] = $receipt->tenantContractInfo->Unit->unit_code;
    $deposit[$i]['unit_no'] = $receipt->tenantContractInfo->Unit->unit_no;
    $deposit[$i]['tenant_name'] = $tenant;
    $deposit[$i]['tenant_code'] = $receipt->tenantContractInfo->tenant->tenant_code;
    $deposit[$i]['tenant_contract_no'] = $receipt->tenantContractInfo->tenant_contract_no;
    $deposit[$i]['receipts_generation_amt'] = $receipt->receipts_generation_amt;
    $deposit[$i]['tenant_contract_start_date'] = $receipt->tenantContractInfo->tenant_contract_start_date->format('Y-m-d');
    $deposit[$i]['tenant_contract_start_date1'] = $receipt->tenantContractInfo->tenant_contract_start_date;
    //dd($receipt->tenantContractInfo->tenant_contract_start_date->format('d/m/Y'));
    $deposit[$i]['tenant_contract_valid_to_date'] = $receipt->tenantContractInfo->tenant_contract_valid_to_date->format('Y-m-d');
    $deposit[$i]['tenant_contract_valid_to_date1'] = $receipt->tenantContractInfo->tenant_contract_valid_to_date;

    $i++;
  }
  //dd($deposit);
  return $deposit;
}
 public function receiptDetailsByTenantContract(Request $request){

      $tenant_contract_id = $request->tenant_contract_id;
      $contract =  TenantContract::active()->where('id',$tenant_contract_id)->first();
     // dd($contract);
      $tenant = $contract->tenant;
      $unit = $contract->unit;
      $receiptGeneration = $contract->receiptGeneration;
      $building = $contract->building;
      //dd($receiptGeneration);
      return json_encode(array($contract,$tenant,$unit,$receiptGeneration,$building));

    }
	public function ajaxBankInfo(Request $request){
		$bank_id = $request->id;
		$banks = Bank::Where('id', $bank_id)->orderBy('bank_name', 'ASC')->first();
		return $banks?$banks:null;
	}
	public function depositRefundCode(){

      $prefix  = prefixData('general_ledger_prefix')->configuration_value.prefixData('general_ledger_prefix')->configuration_year;
     
      $incVal  = prefixData('general_ledger_prefix')->configuration_increment_value;
     
      $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);
     
      return array('code'=>$nextCode,'inc'=>$incVal);

    }

}
