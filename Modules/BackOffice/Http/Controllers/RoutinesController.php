<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Masters\Entities\Bank;
use Modules\Masters\Entities\Building;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Entities\Invoice;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Entities\ReceiptGenerationDim;
use Modules\BackOffice\Entities\LandlordInvioceDistributionBreakup;
use Modules\BackOffice\Entities\LandlordInvoice;
use Modules\Sales\Entities\TenantContract;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\AccountParams;
use Modules\Masters\Entities\Legal;
use App\User;
use Illuminate\Support\Facades\Mail;
use Modules\Masters\Emails\LegalEmail;
use Dynamics;
use App\Setting;
use DB;


class RoutinesController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');  
    $this->middleware('permission:pdc_posting_list', ['only' => ['pdcBulkPosting']]);
    $this->middleware('permission:rental_income_list', ['only' => ['rentalIncomePosting','rentalIncomePostingList']]);
    $this->middleware('permission:tenant_receipt_list', ['only' => ['tenantReceiptPosting','tenantReceiptPostingList']]);
    $this->middleware('permission:cost_recognition_list', ['only' => ['costRecognition','costRecognitionList']]);
	$this->noOfRecord  = 3000;
	
  }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
      $pdc=Pdc::where('id',0)->orderBy('id','asc')->paginate(10);
      $banks=Bank::Where('bank_status', 1)->where('accounts_bank',1)->orderBy('bank_name', 'ASC')->get(); 

      return view('backoffice::Routine.pdc_posting_list',compact('banks','pdc'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      return view('backoffice::create');
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
    public function show()
    {
      return view('backoffice::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
      return view('backoffice::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {

    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
    public function pdcBulkPosting(Request $request){
        
      $total_amt = 0;
      $fromDate=$request->fromdate;
      $toDate=$request->todate;
      $pdc_check_no=$request->pdc_check_no;
      $building_name = ($request->building_name)?$request->building_name:null;
      $pdc_checkno =($request->pdc_checkno)?$request->pdc_checkno:null;
      $bank_name =($request->bank_name)?$request->bank_name:null;
      $custom_direction = $request->direction;
      $total_amt  =  intVal($request->total_amt);

      $request->flash();
      $route   =  $request->url();

      if(isset($request->sort) && $request->sort == 'building_name'){

          $custom_direction      = $request->direction??'ASC';
          $building_name         = 'building_name';
          $field_name            = 'building_name'; 
          
      }
      elseif(isset($request->sort) && $request->sort == 'bank_name'){

          $custom_direction      = $request->direction??'ASC';
          $bank_name             = 'bank_name';
          $field_name            = 'bank_name'; 
      }
      else{
          $custom_direction      = $request->direction??'ASC';
          $pdc_checkno           = 'pdc_check_no';
          $field_name            = 'pdc_check_no'; 
      }
      
      if(!empty($fromDate) && !empty($toDate) && empty($pdc_check_no)){ 

        $pdc= Pdc::join('tenant_contracts','tenant_contracts.id','=','pdc.tenant_contract_id')->join('buildings', 'buildings.id', '=', 'tenant_contracts.building_id')
          ->join('bank', 'bank.id', '=', 'pdc.bank_id')->whereBetween('pdc_check_date', [$fromDate, $toDate])
                ->where('pdc_clear_date', null)
                ->where('pdc_is_posted',0)
				->whereIn('pdc_type',[1,2])
                ->where('pdc_cancel_reason',0)->sortable()
                ->when($building_name, function ($query) use($building_name,$custom_direction) { 
                    return $query->orderBy($building_name,$custom_direction);
                })
                ->when($bank_name, function ($query) use($bank_name,$custom_direction) { 
                    return $query->orderBy($bank_name,$custom_direction);
                })
                ->when($pdc_checkno, function ($query) use($pdc_checkno,$custom_direction) { 
                    return $query->orderBy($pdc_checkno,$custom_direction);
                })->select('pdc.*')->paginate($this->noOfRecord);    
              
      }elseif (!empty($fromDate) && !empty($toDate) && !empty($pdc_check_no)) {

        $pdc = Pdc::join('tenant_contracts','tenant_contracts.id','=','pdc.tenant_contract_id')->join('buildings', 'buildings.id', '=', 'tenant_contracts.building_id')->whereBetween('pdc_check_date', [$fromDate, $toDate])
                   ->where('pdc_clear_date', null)
                   ->where('pdc_check_no',intval($pdc_check_no))
                   ->where('pdc_cancel_reason',0)
				   ->whereIn('pdc_type',[1,2])
                   ->where('pdc_is_posted',0)
                    ->when($building_name, function ($query) use($building_name,$custom_direction) { 
                    return $query->orderBy($building_name,$custom_direction);
                })
                ->when($pdc_checkno, function ($query) use($pdc_checkno,$custom_direction) { 
                    return $query->orderBy($pdc_checkno,$custom_direction);
                })->select('pdc.*')->paginate($this->noOfRecord);  

      }elseif(!empty($pdc_check_no) && empty($fromDate) && empty($toDate)) {

        $pdc = Pdc::join('tenant_contracts','tenant_contracts.id','=','pdc.tenant_contract_id')->join('buildings', 'buildings.id', '=', 'tenant_contracts.building_id')
				   ->where('pdc_check_no',intval($pdc_check_no))
                   ->where('pdc_is_posted',0)
				   ->whereIn('pdc_type',[1,2])
                   ->where('pdc_cancel_reason',0)
				   ->where('pdc_clear_date', null)
                   ->when($building_name, function ($query) use($building_name,$custom_direction) { 
                    return $query->orderBy($building_name,$custom_direction);
                })
          ->when($pdc_checkno, function ($query) use($pdc_checkno,$custom_direction) { 
                    return $query->orderBy($pdc_checkno,$custom_direction);
                })->select('pdc.*')->paginate($this->noOfRecord);  

      }else {
        $pdc = Pdc::join('tenant_contracts','tenant_contracts.id','=','pdc.tenant_contract_id')->join('buildings', 'buildings.id', '=', 'tenant_contracts.building_id')->where('pdc.id',0)
          ->whereIn('pdc_type',[1,2])
		  ->when($building_name, function ($query) use($building_name,$custom_direction) { 
                    return $query->orderBy($building_name,$custom_direction);
                })
          ->when($pdc_checkno, function ($query) use($pdc_checkno,$custom_direction) { 
                    return $query->orderBy($pdc_checkno,$custom_direction);
                })->select('pdc.*')->paginate($this->noOfRecord);  
      }
      $banks = Bank::active()->where('accounts_bank',1)->orderBy('bank_name', 'ASC')->get(); 
      $totalamt = 0;
      if(count($pdc)>0){
        foreach ($pdc as $pdcamt) {
          $total = $pdcamt->pdc_amt;
          $totalamt+= $total;
        }
        $totalamt = $totalamt + $total_amt;
      }
      //$request->flash(); 
		  $route   =  $request->url();
  
      if(isset($request->ajax))
        return view('backoffice::Routine.pdc_posting_list_ajax',compact('banks','pdc','totalamt','request','fromDate','toDate','pdc_check_no','route','custom_direction','field_name')); 
  
      return view('backoffice::Routine.pdc_posting_list',compact('banks','pdc','totalamt','request','fromDate','toDate','pdc_check_no','route','custom_direction','field_name'));
    }


    public function pdcPost(Request $request){
      

      $this->validate($request, [                    
        'pdc_deposit_date'   => 'required|date',
        'bank_id'   => 'required',
        
      ]);
	  
	  try{
		  $pdcArr           = $request->pdc_id;
      $contract_id      = $request->contract_id;
      $pdc_deposit_date = $request->pdc_deposit_date;
      $row_collection   = $request->row_collection; 
      $search_count     = $request->search_count;
      $selectedBank     = $request->bank_id;
	  $totalPdc         = count($pdcArr);
	  $overeffdatearray = array();	  

      foreach($pdcArr as $key=>$pdc){

        $contract_id  = $request->{'contract_id_'.$pdc};
        $pdc_type     = $request->{'pdc_type_'.$pdc};
        $contractInfo = TenantContract::where('id', $contract_id)->first();
        $overcontractend=0;
		
        $pdcData  = Pdc::find($pdc);
        $selectBankInfo = Bank::where('id',$selectedBank)->first();
        $pdcBankInfo    = Bank::where('id',$pdcData->bank_id)->first();
        $bank_id  = $selectBankInfo->id;
        //Notification Starts
        $legal = Legal::where('tenant_contract_id',$contractInfo->tenant_contract_id)->first();
         if($contractInfo->status == 5){
            $legalUsers = User::role(['are','backoffice_manager','legal_advisor'])->get(); 
            $legalUsers = array_flatten($legalUsers);
			$legal = Legal::where('tenant_contract_id',$contractInfo->id)->first();
			if($legal!=null){
			$legal->subject = "PDC Posted !"; 
            $legal->textContent = "PDC Posted having Cheque No: ".$pdcData->pdc_check_no." which is under Legal";
            foreach($legalUsers as $legalUser){
              $mobile = $legalUser->employee->employee_contact_no ?? $legalUser->employee->employee_secondary_no;

              $msg = "PDC Posted";
              $params = 'optional data';
                if(!empty($legalUser->email)){
				  Mail::to($legalUser->email)->send(new LegalEmail($legal,$legalUser)); //Email Notification
				}else{
				   sendSms($mobile,$msg,$params); //SMS Notification
				}

			}
			}

            
 }
 //Notification Ends
// 1 - cheque, 2 - cash - receipt No sequence
$rentIndexCheque   = ReceiptsGeneration::withTrashed()->where('receipts_generation_payment_method',1)->orderBy('id', 'DESC')->first();
$prefix_cheque  = prefixData('receipt_cheque')->configuration_value;

$receiptNo    = $this->receiptGenerateCode('receipt_cheque'); 
$configIncKey = 'receipt_cheque';

$indexNo = 0;

// Rent PDC
if($pdc_type == 1){

    $pdc_type = 0;
   // $receiptEff = $this->receiptEffectiveDateCalculation($contractInfo->id, $contractInfo->tenant_contract_effective_date, $contractInfo->tenant_contract_valid_to_date, $contractInfo->tenant_contract_payment_type );
   $receiptEff = $this->receiptEffectiveDateCalculation($contractInfo->id, $contractInfo->tenant_contract_effective_date, $contractInfo->tenant_contract_valid_to_date,$contractInfo->tenant_contract_rent,$request->{'pdc_amt_'.$pdc} );
   if(isset($receiptEff['msg_receipt'])){
	    $overcontractend=1;
		array_push($overeffdatearray,$contractInfo->tenant_contract_no);
   // session()->flash('success', $contractInfo->tenant_contract_no .' '. $receiptEff['msg_receipt']);
   // return redirect()->route('pdcPosting');

    }
	
   if($overcontractend==0){
    Pdc::where('id',$pdc)->update(['pdc_is_posted'=>1,'pdc_deposit_date'=>$pdc_deposit_date  ,'pdc_posted_date'=>date("Y-m-d"),'pdc_posted_by' => \Auth::user()->id]);
          
	 // $receipts_generation_remark = 'Rent :'.$contractInfo->building->building_name.'-'.$contractInfo->Unit->unit_no.'/'.$receiptEff['eff_from'].'/'.$receiptEff['eff_to'].'/'.numberFormat($contractInfo->tenant_contract_rent);
    $receipts_generation_remark = 'RENT FOR UNIT NO. '.$contractInfo->Unit->unit_no.','.$contractInfo->building->building_name.' FOR '.$receiptEff['eff_from'].' TO '.$receiptEff['eff_to'].'@'.$contractInfo->tenant_contract_rent.'/-PM';
   }
}elseif($pdc_type == 3){
	$pdc_type = 1;
  //$receipts_generation_remark = 'Deposit :'.$contractInfo->building->building_name.'-'.$contractInfo->Unit->unit_no.'/'.$contractInfo->tenant->tenant_name.'@'.numberFormat($request->{'pdc_amt_'.$pdc});
	$receipts_generation_remark = 'UNIT NO. '.$contractInfo->Unit->unit_no.','.$contractInfo->building->building_name;	
	Pdc::where('id',$pdc)->update(['pdc_is_posted'=>1,'pdc_deposit_date'=>$pdc_deposit_date  ,'pdc_posted_date'=>date("Y-m-d"),'pdc_posted_by' => \Auth::user()->id]);
}
else{ // Deposit PDC
  $pdc_type = 2;
  //$receipts_generation_remark = 'Deposit :'.$contractInfo->building->building_name.'-'.$contractInfo->Unit->unit_no.'/'.$contractInfo->tenant->tenant_name.'@'.numberFormat($request->{'pdc_amt_'.$pdc});
	$receipts_generation_remark = 'DEPOSIT FOR UNIT NO. '.$contractInfo->Unit->unit_no.' '.$contractInfo->building->building_name;	
	Pdc::where('id',$pdc)->update(['pdc_is_posted'=>1,'pdc_deposit_date'=>$pdc_deposit_date  ,'pdc_posted_date'=>date("Y-m-d"),'pdc_posted_by' => \Auth::user()->id]);
            
}
$user = \Auth::user();
$permissionExist = $user->hasPermissionTo('request_approval_receipt');
if($permissionExist){ // As Permission for selfApprove
  $receipts_generation_approval_status = 3;
  $receipts_generation_status = 1;
}else{
  $receipts_generation_approval_status = 1;
  $receipts_generation_status = 0;
} 
if(isset($receiptEff['eff_from']) && isset($receiptEff['eff_to'])){
  
  if($contractInfo->tenant_contract_payment_type==1){

      $pay_term = 1;
  }
  elseif($contractInfo->tenant_contract_payment_type==2){

      $pay_term = 2;
  }
  elseif($contractInfo->tenant_contract_payment_type==3){

      $pay_term = 3;
  }
  elseif($contractInfo->tenant_contract_payment_type==4){

      $pay_term = 6;
  }
  elseif($contractInfo->tenant_contract_payment_type==5){

      $pay_term = 12;
  }
 // $receiptRent = $pay_term*$contractInfo->tenant_contract_rent;
  $receiptRent = $request->{'pdc_amt_'.$pdc};
  
  $receipts_generation_eff_from = $receiptEff['eff_from'];
  $receipts_generation_eff_to = $receiptEff['eff_to']; 
  $receipts_generation_amt = $receiptRent;
  
}else{

  $receipts_generation_eff_from = null;
  $receipts_generation_eff_to =null;
  $receipts_generation_amt = $request->{'pdc_amt_'.$pdc} ;
}
if($overcontractend==0){      
$receiptId =  ReceiptsGeneration::create([
        'tenant_contract_id' =>$contract_id,
        'receipts_generation_payment_method' => 1, // cheque
        'receipts_generation_receipt_no' => $receiptNo,
        'receipts_generation_receipt_date' => $pdc_deposit_date,
        'bank_id' => $bank_id,
        'receipts_generation_amt' => $receipts_generation_amt,
        'receipts_generation_cheque_no' => $pdcBankInfo->bank_code.'__'.$request->{'pdc_check_no'.'_'.$pdc},
        'receipts_generation_remark' => $receipts_generation_remark,
        'receipts_generation_description'=>$receipts_generation_remark,
        'receipts_generation_posted_by' => \Auth::user()->id,
        'receipts_generation_posted_date' => date("Y-m-d"),
        'receipts_generation_status' =>  $receipts_generation_status,  //0- InActive(default), 1 - Active , 2 - Cancel, 3 - Posted
        'receipts_generation_eff_from' => $receipts_generation_eff_from,
        'receipts_generation_eff_to' => $receipts_generation_eff_to,
        'receipts_generation_index' => $indexNo,
        'receipts_generation_approval_status' =>$receipts_generation_approval_status, //1 - unapproval(default), 2 -Pending, 3 - Approved, 4 - Reject
        'receipts_generation_type' => $pdc_type, // 1 - general receipt, 0 - tenant receipt, 2- deposit receipt
        'created_by' => \Auth::user()->id
  ]); 
}
  // Deposit Receeipt
  if($pdc_type == 2){

    // Distribution Dimension details
    $acc_parameter  = AccountParams::where('acc_params_tran_desc','comp_mgt_deposit_receipts')->first();

    // Debit Amount - Bank
    if(isset($acc_parameter->acc_params_dr_acc))
    $acc_code_dr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_dr_acc)->first();

    // Credit Amount - Ledger
    if(isset($acc_parameter->acc_params_cr_acc))
    $acc_code_cr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_cr_acc)->first();

     // Debit Insertation
    ReceiptGenerationDim::create([
      'receipts_generation_id' =>$receiptId->id,
      'account_code' => null,
      'description'=>'',
      'dim_type' =>isset($acc_parameter->acc_params_cr_type)?$acc_parameter->acc_params_dr_type:'',
      'debit_amount'=>$receipts_generation_amt,
      'credit_amount'=>0,
      'narration' =>'',
      'ac_codes_id' => 0,
      'created_by' => \Auth::user()->id,
    ]);
    // Credit Insertation
    ReceiptGenerationDim::create([
      'receipts_generation_id' =>$receiptId->id,
      'account_code' =>isset($acc_parameter->acc_params_cr_acc)?$acc_parameter->acc_params_cr_acc:0,
      'description'=>$acc_code_cr->acc_code_desc,
      'dim_type' =>isset($acc_parameter->acc_params_cr_type)?$acc_parameter->acc_params_cr_type:'',
      'debit_amount'=>0,
      'credit_amount'=>$receipts_generation_amt,
      'narration' =>'',
      'ac_codes_id' =>isset($acc_code_cr->id)?$acc_code_cr->id:0,
      'created_by' => \Auth::user()->id,
    ]);
	}
	if($overcontractend==0){
	Pdc::where('id',$pdc)->update(['pdc_receipt_no'=>$receiptNo ]);
  
	 //Update next increment value     
	$this->incrementSequenceNo($configIncKey);
	}
	}


      session()->flash('success', $totalPdc.' PDC Posted Successfully');
	   if(count($overeffdatearray)>0){
        $allconteffover=implode(",", $overeffdatearray);
        session()->flash('success', $allconteffover .' Rent Receipt Generated Completely Till Your Contract End Date');
        if($totalPdc>0){
          $totalPdc=$totalPdc-count($overeffdatearray);
          session()->flash('success',$totalPdc.' - PDC Posted Successfully. For '. $allconteffover .' - Already Rent Receipt Generated Completely Till Contract End Date');
        }
      }
      return redirect()->route('pdcPosting');
		  
	  }
	  catch(\Exception $e){
		  return $e;
	  }

      
    }

    public function rentalIncomePosting(){
      $invoices=Invoice::where('id',0)->orderBy('id','asc')->paginate(10);
      $buildings = Building::active()->get();
      return view('backoffice::Routine.rental_income_posting_list',compact('invoices','buildings'));
    }


    public function rentalIncomePostingList(Request $request){

		  $buildings = Building::where('management_id','=',1)->active()->get();
		  $fromDate=$request->fromdate;
		  $toDate=$request->todate;
		  $building_id=$request->building_id;

			//dd($building_id);
		  if(!empty($fromDate) && !empty($toDate) && empty($building_id)){

			$invoices=Invoice::whereBetween('tenant_invoice_date', [$fromDate, $toDate])
			->where('tenant_invoice_status',1)
			->with('tenantContractInfo.unit')
			->whereHas('tenantContractInfo.unit')
			->get()
			->sortBy('tenantContractInfo.unit.unit_code');

		  }elseif (!empty($fromDate) && !empty($toDate) && !empty($building_id)) {

			$invoices = Invoice::whereBetween('tenant_invoice_date', [$fromDate, $toDate])
			->where('tenant_invoice_status',1)
			->whereHas('tenantContractInfo', function ($query)use($building_id) {
			  $query->whereIn('building_id', $building_id);
			})->with('tenantContractInfo.unit')
			->whereHas('tenantContractInfo.unit')
			->get()
			->sortBy('tenantContractInfo.unit.unit_code');

		  }elseif(!empty($building_id) && empty($fromDate) && empty($toDate)) {

			$invoices = Invoice::where('tenant_invoice_status',1)
			->whereHas('tenantContractInfo', function ($query)use($building_id) {
			  $query->whereIn('building_id', $building_id)->where('tenant_contract_status', 1);
    	})->with('tenantContractInfo.unit')
			->whereHas('tenantContractInfo.unit')
			->get()
			->sortBy('tenantContractInfo.unit.unit_code');

		  }
		  else{

			$invoices = Invoice::where('id',0)->orderBy('id','asc')->get();
		  }
		   // dd($invoices);
		  $totalamt = 0;
		  foreach ($invoices as $invoiceamt) {
			$total = $invoiceamt->tenant_invoice_amt;
			$totalamt+= $total;
		  }
		  $request->flash();
		  return view('backoffice::Routine.rental_income_posting_list',compact('invoices','buildings','totalamt','request','fromDate','toDate','building_id'));

}

    public function rentalPost(Request $request){
       $rentalArr = $request->id;
	  
	  foreach($rentalArr as $item){
		$invoiceInfo 	= Invoice::where('id',$item)->first();
		if(empty($invoiceInfo->tenantContractInfo->tenant->tenant_code))
			return redirect()->back()->with('error', 'Tenant Does Not Exist');
		$isExist	=	Dynamics::TenantIsExitAxPushData('AXIsTenantExist', $invoiceInfo->tenantContractInfo->tenant);
		
		if($isExist==false){
			return redirect()->back()->with('error', 'Customer Or Tenant Does Not Exist In AX');
		}
		$invoiceHeader 	= array(
					'InvoiceId'=> $invoiceInfo->tenant_invoice_no,
					'custAccount'=>$invoiceInfo->tenantContractInfo->tenant->tenant_code,
					'InvoiceAccount' =>$invoiceInfo->tenantContractInfo->tenant->tenant_code,
					'InvoiceDate'=>$invoiceInfo->tenant_invoice_date->format('Y-m-d'),
					'currency'=>CURRENCY,
					'DataAreaId' =>DATA_AREA_ID,
					'company' =>COMPANY,
		);
		
		$response	=	Dynamics::TenantInvoiceAxHeaderPushData('AXTenantInvoiceHeader', $invoiceHeader);
		if($response=='Error'){
			
				return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
				
		}	
		
		if(count($invoiceInfo->tenantInvoiceDimensionInfo)>0){	
			$invoiceLineItem = [];
			foreach($invoiceInfo->tenantInvoiceDimensionInfo as $key=>$item){
				$invoiceLineItem[] = array(
							'recid'=>$response,
							'Amount'=>$invoiceInfo->tenant_invoice_amt ,
							'unitprice'=> $invoiceInfo->tenant_invoice_amt,
							'quantity'=>"1",
							'account'=>strval($item->acc_code_no),
							'InvoiceDate' =>$invoiceInfo->tenant_invoice_date->format('Y-m-d'),
							'Description' =>$invoiceInfo->tenant_invoice_desc ,
							'currency'=> CURRENCY,
							'dimension1'=> 'Building',
							'dimension1value'=>isset($invoiceInfo->tenantContractInfo->building)?$invoiceInfo->tenantContractInfo->building->building_code:'000',
							'dimension2'=> 'Division',
							'dimension2value'=>isset($invoiceInfo->tenantContractInfo->building->ax_division)?$invoiceInfo->tenantContractInfo->building->ax_division:'02',
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
			$result = Dynamics::TenantInvoiceAxLineItemPushData('AXTenantInvoiceLineItem', $invoiceLineItem);
			//dd($invoiceLineItem);
			if(count($invoiceLineItem) >0 && $result =='Error'){
					return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
			}
			Invoice::where('id',$invoiceInfo->id)->update(['tenant_invoice_status'=>3,'tenant_invoice_posted_date'=>date("Y-m-d"),'ax_batch_id'=>$response, 'ax_invoice_no'=>$invoiceInfo->tenant_invoice_no,'tenant_invoice_posted_by' => \Auth::user()->id]);
		}
	  } 
	
      session()->flash('success', 'Rental Income Posted Successfully');
	  return redirect('pdcPosting/rentalIncomePosting');
    }
    public function tenantReceiptPosting(){
      $receipts=ReceiptsGeneration::where('id',0)->orderBy('id','asc')->paginate(10);
      return view('backoffice::Routine.receipt_posting_list',compact('receipts'));
    }

    public function tenantReceiptPostingList(Request $request){
      $fromDate=$request->fromdate;
      $toDate=$request->todate;
      if(!empty($fromDate) && !empty($toDate)){

    $receipts=ReceiptsGeneration::
            whereBetween('receipts_generation_receipt_date', [$fromDate, $toDate])
            ->where('receipts_generation_status',1)
            ->where('receipts_generation_approval_status',3)
            ->orderBy('id','asc')->get(); 

      }else {
        $receipts = ReceiptsGeneration::where('id',0)
                                        ->orderBy('id','asc')
                                        ->get();
      }
      $totalamt = 0;
      foreach ($receipts as $receiptamt) {
        $total = $receiptamt->receipts_generation_amt;
        $totalamt+= $total;
      }
      $request->flash(); 
      return view('backoffice::Routine.receipt_posting_list',compact('receipts','totalamt','request','fromDate','toDate'));
    }


    public function receiptPost(Request $request){
      $rentalArr = $request->id;
	
	  foreach($rentalArr as $item){
		$receiptInfo = ReceiptsGeneration::where('id',$item)->first();
		$management_type = $receiptInfo->tenantContractInfo->building->management_id;
		$bank_cheque_no  = isset($receiptInfo->bankInfo->receipts_generation_cheque_no)?$receiptInfo->bankInfo->receipts_generation_cheque_no:'';	
		$bank_checkbkId  = isset($receiptInfo->bankInfo->bank_chequebook_id)?$receiptInfo->bankInfo->bank_chequebook_id:'';		
		$bank_code = isset($receiptInfo->bankInfo->bank_code)?$receiptInfo->bankInfo->bank_code:'';		
		$bankDim1Value  = isset($receiptInfo->bankInfo->dim1Value)?$receiptInfo->bankInfo->dim1Value:'02';	
		$receipt_cheque_nums='';
		
		$p_method = ($receiptInfo->receipts_generation_payment_method==2)?'Cash':'Cheque';

		$ref_no = isset($receiptInfo->receipts_generation_cheque_no)?$receiptInfo->receipts_generation_cheque_no:'';
     // print_r(json_encode($receiptInfo->receipts_generation_is_pdc_bounce_id));exit();

        if($receiptInfo->receipts_generation_payment_method == 3){
            $p_method = 'Cheque';
            // $ref_no = isset($receiptInfo->receipts_generation_cheque_no)?$receiptInfo->receipts_generation_cheque_no:'';
            
            // if($receiptInfo->receipts_generation_cheque_no =="" || $receiptInfo->receipts_generation_cheque_no =="__"  ){$receipt_cheque_nums='';}else{$receipt_cheque_nums=$receiptInfo->receipts_generation_cheque_no;} 
        } 
		
        if($receiptInfo->receipts_generation_payment_method==1){
             if($receiptInfo->receipts_generation_cheque_no =="" || $receiptInfo->receipts_generation_cheque_no =="__"  ){$receipt_cheque_nums='';}else{$receipt_cheque_nums=$receiptInfo->receipts_generation_cheque_no;}	
        }
		
		// 1 -Comprehensive, 2 - Normal, 3 - Commission 
		if($receiptInfo->receipts_generation_type > 0){ // 1 - general receipt, 2- deposit receipt
			
			$ledgerHeader = array(
				'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
				'DataAreaId'=>DATA_AREA_ID,
				'company' =>COMPANY,
			);
            
			$response	=	Dynamics::LedgerAxHeaderPushData('AXTenantReceiptHeader',$ledgerHeader);
			if($response=='Error'){
				return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
			}
		
			//dd($receiptInfo->getReceiptsDimensionAccount);
			if(count($receiptInfo->getReceiptsDimensionAccount)>0){
				    
				foreach($receiptInfo->getReceiptsDimensionAccount as $key=>$item){
					
					
						$receiptLineItem = (array) null;
						$receiptLineItem[] = array(
								'JournalNum'=> $response,
								'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
								'PaymentDate'=> $receiptInfo->receipts_generation_receipt_date->format('Y-m-d'),
								'Description'=> $receiptInfo->receipts_generation_remark,
								'Account'=>($item->account_code===null)?trim($bank_checkbkId):trim($item->account_code),
								'currency'=> CURRENCY,
								'accountType'=> trim($item->dim_type),
								'paymentMethod'=>$p_method,
								'checkBookid'=>'',
								'documentNo'=> $receipt_cheque_nums,
								'DebitCredit'=>(empty($item->credit_amount))?'Debit':'Credit',
								'voucher'=>$receiptInfo->receipts_generation_receipt_no,
								'AmountCredit'=>(!empty($item->credit_amount))?$item->credit_amount:0,
								'AmountDebit'=>(!empty($item->debit_amount))?$item->debit_amount:0,
								'Remarks'=>$receiptInfo->receipts_generation_description,
								'Invoice'=>'',
								'dimension1'=> 'Building',
								'dimension1value'=>($item->account_code)?$receiptInfo->tenantContractInfo->building->building_code:'000',
								'dimension2'=> 'Division',
								'dimension2value'=>($item->account_code)?$receiptInfo->tenantContractInfo->building->ax_division:(($bankDim1Value)?$bankDim1Value:'01'),
								'dimension3'=> 'Employee',
								'dimension3value'=>'00000',
								'dimension4'=> 'Location',
								'dimension4value'=>'00',
								'dimension5'=> 'Projects',
								'dimension5value'=>'00',
								'DataAreaId'=>DATA_AREA_ID,
								'company'=>COMPANY,
							);
				
					if(count($receiptLineItem) >0 && $result = Dynamics::LedgerAxLineItemPushData('AXGeneralLedgerLineItem', $receiptLineItem)=='Error'){
						return 'Error';
					}
			
				}
				// General Receipt need bank Line item recent change by client
        if($receiptInfo->receipts_generation_type ==1){
					$receiptLineItemBank = (array) null;
                        $receiptLineItemBank[] = array(
                                'JournalNum'=> $response,
                                'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
                                'PaymentDate'=> $receiptInfo->receipts_generation_receipt_date->format('Y-m-d'),
                                'Description'=> $receiptInfo->receipts_generation_remark,
                                'Account'=>trim($bank_checkbkId),
                                'currency'=> CURRENCY,
                                'accountType'=> AX_BANK,
                                'paymentMethod'=>$p_method,
								                'checkBookid'=>isset($bank_cheque_no)?$bank_cheque_no:'',
                                'documentNo'=>  $receipt_cheque_nums,
                                'DebitCredit'=>'Debit',
                                'voucher'=>$receiptInfo->receipts_generation_receipt_no,
                                'AmountCredit'=>0,
                                'AmountDebit'=>$receiptInfo->receipts_generation_amt,
                                'Remarks'=>$receiptInfo->receipts_generation_description,
                                'Invoice'=>'',
                                'dimension1'=> 'Building',
                                'dimension1value'=>'000',
                                'dimension2'=> 'Division',
                                'dimension2value'=>($bankDim1Value)?$bankDim1Value:'01',
                                'dimension3'=> 'Employee',
                                'dimension3value'=>'00000',
                                'dimension4'=> 'Location',
                                'dimension4value'=>'00',
                                'dimension5'=> 'Projects',
                                'dimension5value'=>'00',
                                'DataAreaId'=>DATA_AREA_ID,
                                'company'=>COMPANY,
                            );

                   
            if(count($receiptLineItemBank) >0 && $result = Dynamics::LedgerAxLineItemPushData('AXGeneralLedgerLineItem', $receiptLineItemBank)=='Error'){
                        return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
            }
         }
				//dd($receiptLineItem);
			} 
		} 
		else{ 
			//Comprehensive Building
			if($management_type == 1){
							
				$response	=	Dynamics::ReceiptPaymentJournalAxHeaderPushData('AXTenantReceiptHeader');
				if($response=='Error'){
					return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
				}
				$receiptItemsCr = (array) null;
				$receiptItemsCr = array(
								'JournalNum'=> $response,
								'JournalName'=> AR_PAYMENT_JOURNAL_NAME,
								'PaymentDate'=> $receiptInfo->receipts_generation_receipt_date->format('Y-m-d'),
								'Description'=> $receiptInfo->receipts_generation_remark,
								'CustAccount'=> $receiptInfo->tenantContractInfo->tenant->tenant_code ,
								'currency'=> CURRENCY,
								'accountType'=> 'Cust',
								'paymentMethod'=>$p_method,
								'checkBookid'=>isset($bank_cheque_no)?$bank_cheque_no:'',
								'documentNo'=>  $receiptInfo->receipts_generation_cheque_no,
								'DebitCredit'=>'Credit',
								'voucher'=>$receiptInfo->receipts_generation_receipt_no,
								'Amount'=>$receiptInfo->receipts_generation_amt,
								'Remarks'=>$receiptInfo->receipts_generation_description,
								'Invoice'=>'',
								'dimension1'=> 'Building',
								'dimension1value'=>isset($receiptInfo->tenantContractInfo->building->building_code)?$receiptInfo->tenantContractInfo->building->building_code:'000',
								'dimension2'=> 'Division',
								'dimension2value'=>isset($receiptInfo->tenantContractInfo->building->ax_division)?$receiptInfo->tenantContractInfo->building->ax_division:'02',
								'dimension3'=> 'Employee',
								'dimension3value'=>'00000',
								'dimension4'=> 'Location',
								'dimension4value'=>'00',
								'dimension5'=> 'Projects',
								'dimension5value'=>'00',
								'DataAreaId'=>DATA_AREA_ID,
								'company'=>COMPANY,
							);
				
				$result = Dynamics::ReceiptPaymentJournalAxLineItemPushData('AXTenantReceiptLineItem', $receiptItemsCr);
				if(count($receiptItemsCr) >0 && $result =='Error'){
						//return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
						return 'Error';
				}
				$receiptItemsDr = (array) null;
				$receiptItemsDr = array(
									'JournalNum'=> $response,
									'JournalName'=> AR_PAYMENT_JOURNAL_NAME,
									'PaymentDate'=> $receiptInfo->receipts_generation_receipt_date->format('Y-m-d'),
									'Description'=> $receiptInfo->receipts_generation_remark,
									'CustAccount'=> $bank_checkbkId,
									'currency'=> CURRENCY,
									'accountType'=> AX_BANK,
									'paymentMethod'=>$p_method,
									'checkBookid'=>isset($bank_cheque_no)?$bank_cheque_no:'',
									'documentNo'=>  $receiptInfo->receipts_generation_cheque_no,
									'DebitCredit'=>'Debit',
									'voucher'=>$receiptInfo->receipts_generation_receipt_no,
									'Amount'=>$receiptInfo->receipts_generation_amt,
									'Remarks'=>$receiptInfo->receipts_generation_description,
									'Invoice'=>'',
									'dimension1'=> 'Building',
									'dimension1value'=>'000',
									'dimension2'=> 'Division',
									'dimension2value'=>$bankDim1Value,
									'dimension3'=> 'Employee',
									'dimension3value'=>'00000',
									'dimension4'=> 'Location',
									'dimension4value'=>'00',
									'dimension5'=> 'Projects',
									'dimension5value'=>'00',
									'DataAreaId'=>DATA_AREA_ID,
									'company'=>COMPANY,
				
								);
				
				$resultDr = Dynamics::ReceiptPaymentJournalAxLineItemPushData('AXTenantReceiptLineItem', $receiptItemsDr);
				
				if(count($receiptItemsCr) >0 && $resultDr =='Error'){
						//return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
						return 'Error';
				}
			}
			// Rent Receipt as Normal Or Commission
			else{
				
				$ledgerHeader = array(
				'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
				'DataAreaId'=>DATA_AREA_ID,
				'company' =>COMPANY,
				);
			
				$response	=	Dynamics::LedgerAxHeaderPushData('AXTenantReceiptHeader',$ledgerHeader);
				if($response=='Error'){
					//return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
					return 'Error';
				}
			
				// Distribution Dimension details
				$acc_parameter  = AccountParams::where('acc_params_tran_desc','Nor_Mgt_receipts')->first();
				$receiptLineItem = (array) null;
				$receiptLineItem[] = array(
						'JournalNum'=> $response,
						'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
						'PaymentDate'=> $receiptInfo->receipts_generation_receipt_date->format('Y-m-d'),
						'Description'=>$receiptInfo->receipts_generation_remark,
						'Account'=> $bank_checkbkId,
						'currency'=> CURRENCY,
						'accountType'=> AX_BANK,
						'paymentMethod'=>$p_method,
						'checkBookid'=>isset($bank_cheque_no)?$bank_cheque_no:'',
						'documentNo'=>  $receipt_cheque_nums,
						'DebitCredit'=>'Debit',
						'voucher'=>$receiptInfo->receipts_generation_receipt_no,
						'AmountCredit'=>0,
						'AmountDebit'=>$receiptInfo->receipts_generation_amt,
						'Remarks'=> $receiptInfo->receipts_generation_description,
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
						'DataAreaId'=>DATA_AREA_ID,
						'company'=>COMPANY,
					);
				$receiptLineItem[] = array(
						'JournalNum'=> $response,
						'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
						'PaymentDate'=> $receiptInfo->receipts_generation_receipt_date->format('Y-m-d'),
						'Description'=> $receiptInfo->receipts_generation_remark,
						'Account'=> $acc_parameter->acc_params_cr_acc,
						'currency'=> CURRENCY,
						'accountType'=> AX_GL,
						'paymentMethod'=>$p_method,
						'checkBookid'=>isset($bank_cheque_no)?$bank_cheque_no:'',
						'documentNo'=>  $receipt_cheque_nums,
						'DebitCredit'=>'Credit',
						'voucher'=>$receiptInfo->receipts_generation_receipt_no,
						'AmountCredit'=>$receiptInfo->receipts_generation_amt,
						'AmountDebit'=>0,
						'Remarks'=>$receiptInfo->receipts_generation_remark,
						'Invoice'=>'',
						'dimension1'=> 'Building',
						'dimension1value'=>isset($receiptInfo->tenantContractInfo->building->building_code)?$receiptInfo->tenantContractInfo->building->building_code:'000',
						'dimension2'=> 'Division',
						'dimension2value'=>isset($receiptInfo->tenantContractInfo->building->ax_division)?$receiptInfo->tenantContractInfo->building->ax_division:'02',
						'dimension3'=> 'Employee',
						'dimension3value'=>'00000',
						'dimension4'=> 'Location',
						'dimension4value'=>'00',
						'dimension5'=> 'Projects',
						'dimension5value'=>'00',
						'DataAreaId'=>DATA_AREA_ID,
						'company'=>COMPANY,
					);
				
				$result = Dynamics::LedgerAxLineItemPushData('AXGeneralLedgerLineItem', $receiptLineItem);
				if(count($receiptLineItem) >0 && $result=='Error'){
					//return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
					return 'Error';
				}
				
			}
			
			
		}	
			
		
		 ReceiptsGeneration::where('id',$receiptInfo->id)->update([
                            'receipts_generation_status'=> 3,
                            'receipts_generation_approval_status'=> 6,
							'receipt_journal_no'=>$response,
							'receipts_generation_posted_date'=>date("Y-m-d"),
							'receipts_generation_posted_by' => \Auth::user()->id
                        ]);
						
						
        if($receiptInfo->receipts_generation_type == 0){
            TenantContract::where('id',$receiptInfo->tenant_contract_id)->update([
                        'tenant_contract_last_paid_date' => $receiptInfo->receipts_generation_eff_to,
                        'tenant_contract_last_paid_amt' => $receiptInfo->receipts_generation_amt
                        ]);   
        }

        // 2 - Only For Bounce PDC Settlement, 2 - 'pdc_settlement'- Settled status
                if($receiptInfo->receipts_generation_is_bounce_normal == 2){
                   
                    Pdc::where('id',$receiptInfo->receipts_generation_is_pdc_bounce_id)->update(['pdc_receipt_no'=>$receiptInfo->receipts_generation_receipt_no, 'pdc_settlement'=>2]);
                }   
        
	  }
      

      //Notification starts
  foreach ($rentalArr as $key => $rentalAr) {
    $receiptGeneration = ReceiptsGeneration::find($rentalAr);
    $tenantContract = TenantContract::where('id',$receiptGeneration->tenant_contract_id)->first();
    $legal = Legal::where('tenant_contract_id',$receiptGeneration->tenant_contract_id)->first();

    if($tenantContract->status == 5){
      $legalUsers = User::role(['are','backoffice_manager','legal_advisor'])->get(); 
      $legalUsers = array_flatten($legalUsers);

      $legal->subject = "Tenant Receipt Posted !"; 
      $legal->textContent = "Tenant Receipt Posted having Receipt No: ".$receiptGeneration->receipts_generation_receipt_no." which is under Legal";
      foreach($legalUsers as $legalUser){
        $mobile = $legalUser->employee->employee_contact_no ?? $legalUser->employee->employee_secondary_no;

        $msg = "Tenant Receipt Posted";
        $params = 'optional data';
        if(!empty($legalUser->email)){
      Mail::to($legalUser->email)->send(new LegalEmail($legal,$legalUser)); //Email Notification
    }else{
       sendSms($mobile,$msg,$params); //SMS Notification
     }

   }
  //Notifications ends
 }
 
}
      ///session()->flash('success', 'Tenant Receipt Posted Successfully');
      //return redirect()->route('tenantReceiptPosting');
	  return 'Tenant Receipt Posted Successfully';
    }
    public function costRecognition(){
      $invoices=LandlordInvoice::where('id',0)->orderBy('id','asc')->paginate(10);
      return view('backoffice::Routine.cost_recognition_list',compact('invoices'));
    }

    public function costRecognitionList(Request $request){

      $fromDate=$request->fromdate;
      $toDate=$request->todate;

      if(!empty($fromDate) && !empty($toDate)){

      $invoices=LandlordInvoice::with(['landlordInvoiceDistributionBreakup' => function ($query) use($fromDate, $toDate) {
         $query->whereBetween('date', [$fromDate, $toDate])
             ->where('is_posted','!=',1);
        }])->orderBy('id','asc')->get();  

      }else {
        $invoices = LandlordInvoice::where('id',0)
                                     ->orderBy('id','asc')->get();
      }
	
      $totalamt = 0;
      foreach ($invoices as $invoice) {
		  foreach($invoice->landlordInvoiceDistributionBreakup as $itm ){
          if($itm->credit_amount>0){
             $total = $itm->credit_amount;
             $totalamt+= $total;
          }
        }
      }
      $request->flash(); 
      return view('backoffice::Routine.cost_recognition_list',compact('invoices','totalamt','request','fromDate','toDate'));
    }

    public function costPost(Request $request){
       
		$invoiceArr = $request->checkItem;
		// Cost Recognition
    
		foreach($invoiceArr as $item){
			$landlordInvoiceItemDr = array();
			$landlordInvoiceItemCr = array();
			
			$distributionLine = explode(',',$item);
			$landlordDistributionBreakupCr = LandlordInvioceDistributionBreakup::where('id',$distributionLine[0])->first();
			$landlordDistributionBreakupDr = LandlordInvioceDistributionBreakup::where('landlord_invoice_id',$distributionLine[1])->where('date',$landlordDistributionBreakupCr->date)->where('debit_amount','!=','0.0')->first();
			
			$distribution_lineCrLine_1 = ($landlordDistributionBreakupCr->debit_amount>0)?$landlordDistributionBreakupCr->debit_amount:0;
			$distribution_lineCrLine_2 = ($landlordDistributionBreakupCr->credit_amount>0)?$landlordDistributionBreakupCr->credit_amount:0;
			
			$distribution_lineDrLine_1 = ($landlordDistributionBreakupDr->debit_amount>0)?$landlordDistributionBreakupDr->debit_amount:0;
			$distribution_lineDrLine_2 = ($landlordDistributionBreakupDr->credit_amount>0)?$landlordDistributionBreakupDr->credit_amount:0;


			$landlordInvoice = LandlordInvoice::where('id',$landlordDistributionBreakupCr->landlord_invoice_id)->first();
			$ledgerHeader = array(
				'JournalName'=> COST_RECG_JNAME,
				'DataAreaId'=>DATA_AREA_ID,
				'company' =>COMPANY,
			);
	
			$response		=	Dynamics::LedgerAxHeaderPushData('AXTenantReceiptHeader', $ledgerHeader);

			if($response=='Error'){
				return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
			}
			
			$accountType 		= AX_GL;
			
			$monthly_cost_date 	= $landlordDistributionBreakupCr->date;
			$account_code1		= $landlordDistributionBreakupCr->account_code;
			$debit_amount1 		= $distribution_lineCrLine_1;
			$credit_type1 		= $landlordDistributionBreakupCr->account_type;
			$credit_amount1 	= $distribution_lineCrLine_2;
			$credit				= 'Credit';


			$account_code2 		= $landlordDistributionBreakupDr->account_code;
			$debit_amount2 		= $distribution_lineDrLine_1;
			$debit_type1		= $landlordDistributionBreakupDr->account_type;
			$credit_amount2 	= $distribution_lineDrLine_2;
			$debit				= 'Debit';

			//$landlordInvoice->landlord_invoice_voucher_no
			$landlordInvoiceItemDr[] = array(
					'JournalNum'=> $response,
					'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
					'PaymentDate'=> $monthly_cost_date,
					'Description'=> $landlordInvoice->landlord_invoice_desc,
					'Account'=> $account_code1,
					'currency'=> CURRENCY,
					'accountType'=>trim($debit_type1),
					'paymentMethod'=>'',
					'checkBookid'=>'',
					'documentNo'=> '',
					'DebitCredit'=>$credit,
					'voucher'=>'',
					'AmountCredit'=>number_format( (float) $credit_amount1, 3, '.', ''),
					'AmountDebit'=>number_format( (float) $debit_amount1, 3, '.', ''),
					'Remarks'=>$landlordInvoice->landlord_invoice_desc,
					'Invoice'=>$landlordInvoice->landlord_given_invoice_no,
					'dimension1'=> 'Building',
					'dimension1value'=>isset($landlordInvoice->landlordContractInfo->buildingInfo->building_code)?$landlordInvoice->landlordContractInfo->buildingInfo->building_code:'000',
					'dimension2'=> 'Division',
					'dimension2value'=>isset($landlordInvoice->landlordContractInfo->buildingInfo->ax_division)?$landlordInvoice->landlordContractInfo->buildingInfo->ax_division:'02',
					'dimension3'=> 'Employee',
					'dimension3value'=>'00000',
					'dimension4'=> 'Location',
					'dimension4value'=>'00',
					'dimension5'=> 'Projects',
					'dimension5value'=>'00',
					'DataAreaId'=>DATA_AREA_ID,
					'company'=>COMPANY,
				);
			
			$result = Dynamics::LedgerAxLineItemPushData('AXGeneralLedgerLineItem', $landlordInvoiceItemDr);
			if(count($landlordInvoiceItemDr) >0 && $result=='Error'){
				return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
			}
			//$landlordInvoice->landlord_invoice_voucher_no
			$landlordInvoiceItemCr[] = array(
					'JournalNum'=> $response,
					'JournalName'=> COST_RECG_JNAME,
					'PaymentDate'=> $monthly_cost_date,
					'Description'=> $landlordInvoice->landlord_invoice_desc,
					'Account'=> $account_code2,
					'currency'=> CURRENCY,
					'accountType'=>trim($credit_type1),
					'paymentMethod'=>'',
					'checkBookid'=>'',
					'documentNo'=> '',
					'DebitCredit'=>$debit,
					'voucher'=>'',
					'AmountCredit'=>number_format((float) $credit_amount2, 3, '.', ''),
					'AmountDebit'=>number_format((float) $debit_amount2, 3, '.', ''),
					'Remarks'=>$landlordInvoice->landlord_invoice_desc,
					'Invoice'=>$landlordInvoice->landlord_given_invoice_no,
					'dimension1'=> 'Building',
					'dimension1value'=>isset($landlordInvoice->landlordContractInfo->buildingInfo->building_code)?$landlordInvoice->landlordContractInfo->buildingInfo->building_code:'000',
					'dimension2'=> 'Division',
					'dimension2value'=>isset($landlordInvoice->landlordContractInfo->buildingInfo->ax_division)?$landlordInvoice->landlordContractInfo->buildingInfo->ax_division:'02',
					'dimension3'=> 'Employee',
					'dimension3value'=>'00000',
					'dimension4'=> 'Location',
					'dimension4value'=>'00',
					'dimension5'=> 'Projects',
					'dimension5value'=>'00',
					'DataAreaId'=>DATA_AREA_ID,
					'company'=>COMPANY,
				);
				
				$result = Dynamics::LedgerAxLineItemPushData('AXGeneralLedgerLineItem', $landlordInvoiceItemCr);
				if(count($landlordInvoiceItemCr) >0 && $result=='Error'){
					return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
				}
				
				LandlordInvioceDistributionBreakup::where('id',$landlordDistributionBreakupCr->id)->update(['is_posted'=>1,'ax_batch_id'=>$result,'posted_date'=>date("Y-m-d"),'posted_by' => \Auth::user()->id]);
				LandlordInvioceDistributionBreakup::where('id',$landlordDistributionBreakupDr->id)->update(['is_posted'=>1,'ax_batch_id'=>$result,'posted_date'=>date("Y-m-d"),'posted_by' => \Auth::user()->id]);				//echo "<pre>";
				//print_r(Dynamics::LedgerAxLineItemPushData('AXGeneralLedgerLineItem', $landlordInvoiceItem)); exit;
		}
		session()->flash('success', 'Landlord Invoice Posted Successfully');
		return redirect()->route('costRecognition');
    }
    /*

      PDC posting and Receipt Generation

    */
    public function pdcPostingReceiptGeneration(Request $request){

        $rowCount = $request->search_count;

    }
    /*
        Rent receipt effective from and to date

    

    public function receiptEffectiveDateCalculation($contractId, $contract_start, $contract_end, $tenant_contract_payment_type){

        $lastReceipt = ReceiptsGeneration::where('tenant_contract_id', $contractId)->where('receipts_generation_type',0)->where('receipts_generation_status','!=',2)->orderBy('id', 'DESC')->first();
        if($tenant_contract_payment_type == 4 )
            $tenant_contract_payment_type = 6;
        elseif($tenant_contract_payment_type == 5)
            $tenant_contract_payment_type = 12;

  
        if(isset($lastReceipt->receipts_generation_eff_to)){
            if($contract_end > $lastReceipt->receipts_generation_eff_to){

                $lastReceiptToDate  =   $lastReceipt->receipts_generation_eff_to;
                $eff_from   = date('Y-m-d',strtotime("+1 day", strtotime($lastReceiptToDate)));
                $eff_to = $eff_from;
                for($i=0; $i<$tenant_contract_payment_type;$i++ ){
    
                    $dateMonth  =   date('m', strtotime($eff_to));
                    $dateYear   =   date('Y', strtotime($eff_to));

                    $dateDays   =   cal_days_in_month(CAL_GREGORIAN,$dateMonth,$dateYear );
                    $eff_to  =   date('Y-m-d', strtotime("+$dateDays day", strtotime($eff_to)));
                    
                }
                
                
                $eff_to     = date('Y-m-d',strtotime($eff_to)-1);
                if($contract_end > $eff_to){
                    return(array('eff_from'=>$eff_from , 'eff_to'=>$eff_to));
                }
                else{
                    $eff_to = $contract_end ;
                    return(array('eff_from'=>$eff_from , 'eff_to'=> date('Y-m-d',strtotime($contract_end))));
                }

            }
            else{
                 return(array('msg_receipt'=>'Rent Receipt Generated Completely Till Your Contract End Date'));

            }
        }
        else{
             
            $eff_from       =   $contract_start;
			$eff_to 		= 	$eff_from;
            for($i=0; $i<$tenant_contract_payment_type;$i++ ){
    
                $dateMonth  =   date('m', strtotime($eff_to));
                $dateYear   =   date('Y', strtotime($eff_to));

                $dateDays   =   cal_days_in_month(CAL_GREGORIAN,$dateMonth,$dateYear );
                $eff_to  =   date('Y-m-d', strtotime("+$dateDays day", strtotime($eff_to)));
                    
            }
            $eff_from   = date('Y-m-d',strtotime($contract_start));
            $eff_to     = date('Y-m-d',strtotime($eff_to)-1);
            if($contract_end > $eff_to){
                    return(array('eff_from'=>$eff_from , 'eff_to'=>$eff_to));
            }
            else{
                $eff_to = $contract_end ;
                return(array('eff_from'=>$eff_from , 'eff_to'=> date('Y-m-d',strtotime($contract_end))));
            }
        }

    }
    
   */
   public function receiptEffectiveDateCalculation($contractId, $contract_start, $contract_end,$rent_amount,$pdc_amount){

        $lastReceipt = ReceiptsGeneration::where('tenant_contract_id', $contractId)->where('receipts_generation_type',0)->where('receipts_generation_status','!=',2)->orderBy('id', 'DESC')->first();

       $fullmonths=0;
       $reminader=0;
       $fullmonths= intval($pdc_amount/$rent_amount);
       $reminader= fmod($pdc_amount,$rent_amount);
       $remainingdays= round(30*$reminader/$rent_amount);
  
        if(isset($lastReceipt->receipts_generation_eff_to)){
            if($contract_end > $lastReceipt->receipts_generation_eff_to){

                $lastReceiptToDate  =   $lastReceipt->receipts_generation_eff_to;
                $eff_from   = date('Y-m-d',strtotime("+1 day", strtotime($lastReceiptToDate)));
                $eff_to = $eff_from;
                if( $fullmonths>0)
                {
                $eff_to = date('Y-m-d', strtotime("+".$fullmonths." months", strtotime($eff_from)));
                $eff_to     = date('Y-m-d',strtotime($eff_to)-1);
                }
                if($reminader>0){
                $eff_to= date('Y-m-d', strtotime("+".$remainingdays." days", strtotime($eff_to)));
                }
                

                if($contract_end > $eff_to){
                    return(array('eff_from'=>$eff_from , 'eff_to'=>$eff_to));
                }
                else{
                    $eff_to = $contract_end ;
                    return(array('eff_from'=>$eff_from , 'eff_to'=> date('Y-m-d',strtotime($contract_end))));
                }

            }
            else{
                 return(array('msg_receipt'=>'Rent Receipt Generated Completely Till Your Contract End Date'));

            }
        }
        else{
             
            $eff_from = $contract_start;
			      $eff_to = $eff_from;
                if( $fullmonths>0)
                {
                $eff_to = date('Y-m-d', strtotime("+".$fullmonths." months", strtotime($eff_from)));
                $eff_to     = date('Y-m-d',strtotime($eff_to)-1);
                }
                if($reminader>0){
                $eff_to= date('Y-m-d', strtotime("+".$remainingdays." days", strtotime($eff_to)));
                }
            if($contract_end > $eff_to){
                    return(array('eff_from'=>$eff_from , 'eff_to'=>$eff_to));
            }
            else{
                $eff_to = $contract_end ;
                return(array('eff_from'=>$eff_from , 'eff_to'=> date('Y-m-d',strtotime($contract_end))));
            }
        }

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
    $sumOfStartDays = 0;
    $sumOfEndDays = 0;
    $sumOfSameStartEnd = 0;
    $monthIsOne = 1;
    $sumOfMonthRent = 0;
    
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
        if($explodeStartDtValue[2] <= $noOfDaysStartMonth){
          
          $startDays = $noOfDaysStartMonth - $explodeStartDtValue[2] + 1;
          
          $sumOfStartDays  = ($rent/$noOfDaysStartMonth) * $startDays;
        
        }
        if($explodeEndDtValue[2] <= $noOfDaysEnd){
          
          $endDays = $explodeEndDtValue[2];
          $sumOfEndDays  = ($rent/$noOfDaysStartMonth) * $endDays;
          
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
    return array('rent'=>number_format((float)$sumOfMonthRent, 3, '.', ''));

  }  
   /**
    *
    * Building Autocomplete
    *
    **/
    public function rentalBuildingAutocomplete(Request $request){
     $key = $request->search;
     $buildings =   Building::where('building_name', 'ILIKE', '%'.$key.'%')
     ->where('management_id','=',1)
     ->select('id AS value','building_name AS text')
     ->orderBy('building_name','asc')
     ->get();

     return $buildings ;


   }
   /*  
  *   Receipt no generation For cash &  
  *   Cheque  
  */  
  public function receiptGenerateCode($configKey){  
    $prefix     = prefixData($configKey)->configuration_value.prefixData($configKey)->configuration_year; 
      
    $inc_value  = prefixData($configKey)->configuration_increment_value;  
      
    $receiptNo  = $prefix.str_pad($inc_value,5,'0',STR_PAD_LEFT); 
      
    return $receiptNo;  
  } 
  /*  
  *   configuartion increment sequence value update 
  */  
  public function incrementSequenceNo($configKey){  
    Setting::where('configuration_settings',$configKey)->update(['configuration_increment_value'=> DB::raw('configuration_increment_value + 1') 
    ]); 
  }
  }
