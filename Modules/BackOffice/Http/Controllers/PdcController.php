<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\Sales\Entities\TenantContract;
use Modules\Masters\Entities\Bank;
use Modules\Sales\Entities\Tenant;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Unit;
use Modules\Sales\Entities\SalesEnquiry;
use DB;
use Session;
use URL;
use Route;
use App\User;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\ChequeBounceEmail;
use Modules\BackOffice\Emails\ChequeBounceAreEmail;
use Modules\BackOffice\Events\ChequeBounceAre;
use Modules\BackOffice\Services\PdcBounceNotifier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PdcController extends Controller
{
    /** Tenant bounce email switched off 2026-09-30 (SMS still sent); set true to send it again. */
    const TENANT_BOUNCE_EMAIL = false;

    public function __construct()
    {

		$this->middleware('auth');    
		$this->middleware('permission:pdc_generation', ['only' => ['edit']]);   
		$this->middleware('permission:pdc_view', ['only' => ['pdcView']]);
		$this->noOfRecord  = 250;
  }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('backoffice::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Respons
e     */
    public function create()
    {
        return view('backoffice::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $existRows      = $request->existRow;
        $payment_term   = $request->paymentTerm;
        $contractId     = $request->tenantContract;
        $arr = array(); 
    
        if(isset($existRows)){
            $tenantPdcLatest = Pdc::orderBy('id', 'desc')->first();
            $prefix  = prefixData('tenant_pdc_prefix')->configuration_value;

            if(!empty($tenantPdcLatest))
                $pdcTransacNo = $prefix.str_pad($tenantPdcLatest->id+1,4,'0',STR_PAD_LEFT);
            else
                $pdcTransacNo = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);

        //dd($request);
            for($i= 0 ;$i<=$existRows;$i++){

                if(isset($request->{'pdc_check_no_'.$i}))
                    $arr[] = $request->{'pdc_check_no_'.$i};

            }
			/*
            if(count($arr) > 0){

                $value = count($arr);
                $temp_value = count(array_unique($arr));
                if($temp_value != $value){
                    session()->flash('error', 'Duplicate Check No');
                    return redirect()->route('pdc.edit', $contractId);
                }
            }
			*/
            $pdc_reference = null;
            
            for($i= 0 ;$i<=$existRows;$i++){
                $cancel = null;
				/*
                if(empty($request->{'pdc_check_no_'.$i}) || empty($request->{'pdc_check_date'.'_'.$i}) || empty($request->{'pdc_recieve_date_'.'_'.$i}) || empty($request->{'pdc_amt'.'_'.$i}) || empty($request->{'bank_id'.'_'.$i}) || empty($request->{'pdc_stage'.'_'.$i}) || empty($request->{'pdc_recieve_date'.'_'.$i})){
                      
                      session()->flash('error', 'Empty Fields');
                      return redirect()->route('pdc.edit', $contractId);
                }
				*/
                // Locked row (2: bounced / exchanged through the Cheque Bounce popup): leave it as it is.
                // It used to fall through to create() below and came back as a new open PDC on every Save.
                if($request->{'pdc_is_saved_'.$i} == 2 && $request->{'pdc_check_id_'.$i}){
                    continue;
                }
                if($request->{'pdc_is_saved_'.$i} == 1 && $request->{'pdc_check_no_'.$i} > 0){

                    if($request->{'pdc_reference_'.$i} >0 ){
                        $pdc_reference = $request->{'pdc_reference_'.$i};
                        $cancel = date('Y-m-d');

                    }
                    if(isset($request->{'pdc_cancel_reason_'.$i})){
                        $cancel = date('Y-m-d');
                    }
                    $cancelReasonBefore = Pdc::where('id', $request->{'pdc_check_id_'.$i})->value('pdc_cancel_reason');
                    Pdc::where('id','=',$request->{'pdc_check_id_'.$i})->update([
                        'pdc_check_no' => $request->{'pdc_check_no_'.$i},
                        'pdc_check_date' => $request->{'pdc_check_date'.'_'.$i},
                        'pdc_recieve_date' => $request->{'pdc_recieve_date_'.$i},
                        'pdc_period' => $i+1,
                        'pdc_amt' => replaceCommaWithDot($request->{'pdc_amt'.'_'.$i}),
                        'bank_id' => $request->{'bank_id'.'_'.$i}, 
                        'pdc_stage' => $request->{'pdc_stage_'.$i}, 
                        'pdc_recieve_date' => $request->{'pdc_recieve_date_'.$i},
                        'pdc_reference' => $pdc_reference,
                        'pdc_deposit_date' => $request->{'pdc_deposit_date_'.$i},
                        'pdc_clear_date' => $request->{'pdc_clear_date_'.$i},
                        'pdc_cancel_date' => $cancel,
                        'pdc_cancel_reason' => isset($request->{'pdc_cancel_reason_'.$i})?$request->{'pdc_cancel_reason_'.$i}:0, // '1 - Bounce , 2 - Exchange , 3 - Other'
                        'pdc_bounce_reason' => $request->{'pdc_bounce_reason_'.$i},
                         // codes: Masters -> Bounce Reason (pdc_bounce_reasons.id)
                        'pdc_is_saved'=> 1,     
                        'pdc_type' =>$request->{'pdc_type_'.$i},
                        'pdc_remark' =>$request->{'pdc_remark_'.$i},
                        'pdc_cheque_acknowledge' =>$request->{'pdc_cheque_acknowledge_'.$i},
                        // blank keeps what is stored: the renewal PDC screen has no account field, and a grid opened before a bank-sheet post would otherwise erase the account that post filled in
                        'pdc_account_no' => $this->keptAccountNo($request->{'pdc_account_no_'.$i}, $request->{'pdc_check_id_'.$i}),
                        'pdc_from_date' =>$request->{'pdc_from_date_'.$i},
                        'pdc_to_date' =>$request->{'pdc_to_date_'.$i},
                        'created_by' => \Auth::user()->id
                        ]); 

                    /* Check whether it is bounce*/

                    $bounce = $request->{'pdc_cancel_reason_'.$i};
                    // Bounced rows are re-posted on every grid save: notify only when it newly became a bounce.
                    if(PdcBounceNotifier::isNewBounce($cancelReasonBefore, $bounce)){
                        $pdc_id = $request->{'pdc_check_id_'.$i};
                        $pdcData = Pdc::where('id',$pdc_id)->first();

                        $this->notifyTenantBounce($pdcData);
                        $this->notifyBounceCopies($pdcData);

                        /*-----------Are Email Starts--------------------*/
                        if(count($pdcData->tenantContractInfo->building->buildingAssignTo) > 0)
                            foreach($pdcData->tenantContractInfo->building->buildingAssignTo as $assign)
                                
                              $are_email = $assign->buildingAssignToName->areUser->employee->user->email??'';

                          $user = $assign->buildingAssignToName->areUser;
                          $users = User::where('id',$user->id)->get();
                          $pdcData->href = "pdc/".$pdcData->tenantContractInfo->id."/edit";

                          Mail::to($are_email)->send(new ChequeBounceAreEmail($pdcData));

                          event(new ChequeBounceAre($pdcData,$users)); 
                          /*-----------Are Email Ends--------------------*/
                      }


                  }
                  elseif($request->{'pdc_check_no_'.$i} > 0){
                   // if($i==1)     dd($request->{'pdc_amt'.'_'.$i});
                    Pdc::create(['tenant_contract_id' => $contractId,
                                     'pdc_transaction_no' => $pdcTransacNo, // Rent
                                     'pdc_transaction_date' => $request->pdc_transaction_date,
                                     'pdc_check_no' => $request->{'pdc_check_no_'.$i},
                                     'pdc_check_date' => $request->{'pdc_check_date'.'_'.$i},
                                     'pdc_recieve_date' => $request->{'pdc_recieve_date'.'_'.$i},
                                     'pdc_period' => $i+1,
                                     'pdc_amt' => replaceCommaWithDot($request->{'pdc_amt'.'_'.$i}),

                                     'bank_id' => $request->{'bank_id'.'_'.$i}, 
                                     'pdc_stage' => $request->{'pdc_stage'.'_'.$i}, // Active
                                     'pdc_type' => $request->{'pdc_type_'.$i},
                                     'pdc_remark' => $request->{'pdc_remark_'.$i},
                                     'pdc_cheque_acknowledge' => $request->{'pdc_cheque_acknowledge_'.$i},
                                     'pdc_account_no' => $this->accountNo($request->{'pdc_account_no_'.$i}),
                                     'pdc_from_date' => $request->{'pdc_from_date_'.$i},
                                     'pdc_to_date' => $request->{'pdc_to_date_'.$i},
                                     'pdc_is_saved' => 1,
                                     'created_by' => \Auth::user()->id
                                     ]);   

                }  


            }
            if(isset($request->deleteRow)){

                $deleteArr = explode(',', $request->deleteRow);
                foreach($deleteArr as $itemDelete){
                    if($itemDelete)
                     $deletedRows = Pdc::where('id', $itemDelete)->delete();  
             }
         }
         $pdcCount = Pdc::where('tenant_contract_id',$contractId)->count();
         if($pdcCount)
            TenantContract::where('id','=',$contractId)->update(['pdc_check' =>1]);
        else
            TenantContract::where('id','=',$contractId)->update(['pdc_check' =>0]);
        session()->flash('success', 'Saved Successfully');
        return redirect()->route('pdc.edit', $contractId);
    }
    return redirect()->route('tenant-contract.index');
}

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    /** Optional drawer account: digits only, as typed (leading zeros kept); null when blank. */
    private function accountNo($value)
    {
        $digits = substr(preg_replace('/\D/', '', (string) $value), 0, 20);
        return $digits === '' ? null : $digits;
    }

    /** Account for an existing PDC: the typed one, or the stored one when the field is blank/absent. */
    private function keptAccountNo($value, $pdcId)
    {
        $typed = $this->accountNo($value);
        return $typed !== null ? $typed : Pdc::where('id', $pdcId)->value('pdc_account_no');
    }

    /**
     * Ids of the bounced PDCs that were deposited again: a later, not cancelled PDC of the same
     * contract with the same cheque no, bank, cheque date and amount. Their Redeposit button is hidden.
     */
    public static function redepositedIds($pdcs)
    {
        $key = function ($p) {
            return ltrim((string) $p->pdc_check_no, '0') . '|' . (int) $p->bank_id . '|'
                . substr((string) $p->pdc_check_date, 0, 10) . '|' . number_format((float) $p->pdc_amt, 3, '.', '');
        };
        $out = [];
        foreach ($pdcs as $b) {
            if ((int) $b->pdc_cancel_reason !== 1) {
                continue;
            }
            foreach ($pdcs as $p) {
                if ($p->id > $b->id && (int) $p->pdc_cancel_reason === 0 && $key($p) === $key($b)) {
                    $out[] = (int) $b->id;
                    break;
                }
            }
        }
        return $out;
    }

    /**
     * Bounced PDCs already paid by a receipt made against the bounce, as [pdc id => receipt no].
     * Counts any receipt linked to the cheque that is not cancelled (status 2) or rejected (approval 4).
     * Their Redeposit button is hidden.
     */
    public static function bounceReceiptNos($pdcs)
    {
        $bounced = [];
        foreach ($pdcs as $p) {
            if ((int) $p->pdc_cancel_reason === 1) {
                $bounced[(int) $p->id] = $p;
            }
        }
        if (!$bounced) {
            return [];
        }
        $out = ReceiptsGeneration::whereIn('receipts_generation_is_pdc_bounce_id', array_keys($bounced))
            ->where('receipts_generation_status', '!=', 2)
            ->where('receipts_generation_approval_status', '!=', 4)
            ->orderBy('id')
            ->pluck('receipts_generation_receipt_no', 'receipts_generation_is_pdc_bounce_id')
            ->all();
        foreach ($bounced as $id => $p) {
            if (!isset($out[$id]) && (int) $p->pdc_settlement === 2) {
                $out[$id] = (string) $p->pdc_receipt_no;
            }
        }
        return $out;
    }

    public function show($id)
    {

        return view('backoffice::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($contract_id)
    {
		clearNotification('Modules\BackOffice\Notifications\ChequeBounceNotification',$contract_id);	  
        $tenantContract = TenantContract::where('id',$contract_id)->first();
        $salesEnquiry = SalesEnquiry::where('id',$tenantContract->sale_enquiry_id)->first();
		$pdc_transaction_date =null;
		
        // In Renewed Contract no sales enquiryID And Workflow only 108. 9- Approved, 5 - Renew contract
        if(in_array($tenantContract->tenant_renewal_termination_status,[9,5])){
            $tenantPdcLatest = Pdc::orderBy('id', 'desc')->first();
            
            $tenantPdcInfo  = Pdc::where('tenant_contract_id', $contract_id)->orderBy('id', 'asc')->get();
            if(count($tenantPdcInfo) > 0)
                $pdc_transaction_date = $tenantPdcInfo[0]->pdc_transaction_date;

            $bankMaster = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();

            $prefix  = prefixData('tenant_pdc_prefix')->configuration_value;
            
            if(!empty($tenantPdcLatest))
                $pdcTransacNo = $prefix.str_pad($tenantPdcLatest->id+1,4,'0',STR_PAD_LEFT);
            else
                $pdcTransacNo = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);

            $redeposited = self::redepositedIds($tenantPdcInfo);
            $bounceReceipts = self::bounceReceiptNos($tenantPdcInfo);
            return view('backoffice::Pdc.contract_pdc',compact('tenantContract','pdcTransacNo','bankMaster','tenantPdcInfo','pdc_transaction_date','redeposited','bounceReceipts'));

        }
        elseif($tenantContract->work_flow_processes_code >=106 || $salesEnquiry->work_flow_processes_code >=106){
            $tenantPdcLatest = Pdc::orderBy('id', 'desc')->first();
            
            $tenantPdcInfo  = Pdc::where('tenant_contract_id', $contract_id)->orderBy('id', 'asc')->get();
            if(count($tenantPdcInfo) > 0)
                $pdc_transaction_date = $tenantPdcInfo[0]->pdc_transaction_date;

            $bankMaster = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();

            $prefix  = prefixData('tenant_pdc_prefix')->configuration_value;
            
            if(!empty($tenantPdcLatest))
                $pdcTransacNo = $prefix.str_pad($tenantPdcLatest->id+1,4,'0',STR_PAD_LEFT);
            else
                $pdcTransacNo = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);

            $redeposited = self::redepositedIds($tenantPdcInfo);
            $bounceReceipts = self::bounceReceiptNos($tenantPdcInfo);
            return view('backoffice::Pdc.contract_pdc',compact('tenantContract','pdcTransacNo','bankMaster','tenantPdcInfo','pdc_transaction_date','redeposited','bounceReceipts'));
        }
        else{
           // Contract in-progress
           return redirect()->route('tenant-contract.index');
       }

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

    public function tenantPdcBounce(Request $request){


       // $pdc            = array();


        $fromDate       = $request->fromdate;
        $toDate         = $request->todate;

        $fromcheque_no  = $request->fromcheque_no;
        $tocheque_no    = $request->tocheque_no;

        $bank_id   = $request->bank_id;

        if(isset($fromDate) && isset($toDate)){

           $pdc = Pdc::whereBetween('pdc_check_date', [$fromDate, $toDate])
           ->where('pdc_cancel_reason',0)
           ->where('pdc_clear_date', null)
           ->orderBy('id','asc')
           ->paginate($this->noOfRecord);   

       }
       elseif(isset($fromcheque_no) && isset($tocheque_no)){


                  // $pdc = Pdc::where('pdc_check_no','>=',$fromcheque_no)->where('pdc_check_no','<=',$tocheque_no)->where('pdc_cancel_reason',0)->orderBy('id','asc')->paginate(20);
           $pdc = Pdc::whereBetween('pdc_check_no', [$fromcheque_no, $tocheque_no])
           ->where('pdc_clear_date', null)
           ->where('pdc_cancel_reason',0)
           ->orderBy('id','asc')
           ->paginate($this->noOfRecord);

       }
       elseif(isset($bank_id)){

        $pdc = Pdc::where('bank_id',$bank_id)
        ->where('pdc_is_posted',0)
        ->where('pdc_clear_date', null)
        ->where('pdc_cancel_reason',0)
        ->orderBy('id','asc')->paginate($this->noOfRecord);


    }else{
        $pdc = Pdc::where('id',0)
        ->orderBy('id','asc')
        ->paginate($this->noOfRecord);
    }

	$route   =  $request->url();
       // dd($pdc);
    $banks = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();

    return view('backoffice::Pdc.pdc_cheque_bounce',compact('banks','pdc','request','route'));
}
   /*
    Ajax popup bounce reason

   */
    public function  tenantAjaxPdcBounceSearch(Request $request){

        $pdc_id             = $request->id;
        $pdc_bounce_reason  = $request->pdc_bounce_reason;

        if(!$pdc_id)
            response()->json(['error'=>'Something Error in Server']);

        $isPosted           = Pdc::where('id',$pdc_id)->first();

        // Optional bounce remark (e.g. the bank's rejection reason from the bank sheet):
        // appended to the PDC remarks and shown in the ARE email / notification.
        $bounceRemark = trim((string) $request->input('bounce_remark'));
        $remarkUpdate = [];
        if ($bounceRemark !== '') {
            $existingRemark = trim((string) $isPosted->pdc_remark);
            $remarkUpdate['pdc_remark'] = ($existingRemark !== '' ? $existingRemark . ' | ' : '') . 'Bounced: ' . $bounceRemark;
        }
        $isPosted->pdc_bounce_reason = $pdc_bounce_reason; // so the email / notification can name the reason
        $isPosted->bounce_remark     = $bounceRemark !== '' ? $bounceRemark : null;



        if(empty($isPosted->pdc_is_posted)){ // Delete status - 2

            Pdc::where('id',$pdc_id)->update($remarkUpdate + ['pdc_cancel_date'=>date('Y-m-d')    ,'pdc_cancel_reason'=>1 ,'pdc_bounce_reason'=>$pdc_bounce_reason,'pdc_is_saved'=>2,'pdc_cancel_by' => \Auth::user()->id]);

            ReceiptsGeneration::where('receipts_generation_receipt_no',$isPosted->pdc_receipt_no)->update(['receipts_generation_cancel_date'=>date('Y-m-d'),'receipts_generation_status'=>2,'receipts_generation_reason_cancel'=>'PDC Bounce','receipts_generation_cancel_by' => \Auth::user()->id]);

            \Modules\BackOffice\Services\TerminationDuesService::touchContract($isPosted->tenant_contract_id);

            $this->notifyTenantBounce($isPosted);
            $this->notifyBounceCopies($isPosted);
            /*-----------Are Email Starts--------------------*/
            if(count($isPosted->tenantContractInfo->building->buildingAssignTo) > 0)
                foreach($isPosted->tenantContractInfo->building->buildingAssignTo as $assign)

                  $are_email = $assign->buildingAssignToName->areUser->employee->user->email??'';
				if(isset($assign)){
				  $user = $assign->buildingAssignToName->areUser;
				  $users = User::where('id',$user->id)->get();

				  $isPosted->href = url("pdc/".$isPosted->tenantContractInfo->id."/edit");
					$isPosted->id =$isPosted->tenantContractInfo->id;
				  Mail::to($are_email)->send(new ChequeBounceAreEmail($isPosted));

				  event(new ChequeBounceAre($isPosted,$users)); 
					/*-----------Are Email Ends--------------------*/
				}
              return response()->json(['success'=>'Data is successfully added']);
          }
          else{

            Pdc::where('id',$pdc_id)->update($remarkUpdate + ['pdc_cancel_date'=>date('Y-m-d')    ,'pdc_cancel_reason'=>1 ,'pdc_bounce_reason'=>$pdc_bounce_reason,'pdc_cancel_by' => \Auth::user()->id]);

            ReceiptsGeneration::where('receipts_generation_receipt_no',$isPosted->pdc_receipt_no)->update(['receipts_generation_cancel_date'=>date('Y-m-d'),'receipts_generation_status'=>2,'receipts_generation_reason_cancel'=>'PDC Bounce','receipts_generation_cancel_by' => \Auth::user()->id]);

            \Modules\BackOffice\Services\TerminationDuesService::touchContract($isPosted->tenant_contract_id);

            $this->notifyTenantBounce($isPosted);
            $this->notifyBounceCopies($isPosted);
     
            /*-----------Are Email Starts--------------------*/
            if(count($isPosted->tenantContractInfo->building->buildingAssignTo) > 0)
                foreach($isPosted->tenantContractInfo->building->buildingAssignTo as $assign)

					$are_email = $assign->buildingAssignToName->areUser->employee->user->email??'';
					if(isset($assign->buildingAssignToName->areUser)){
						$user = $assign->buildingAssignToName->areUser;
						$users = User::where('id',$user->id)->get(); 
						$isPosted->href = url("pdc/".$isPosted->tenantContractInfo->id."/edit");
						$isPosted->id =$isPosted->tenantContractInfo->id;
						Mail::to($are_email)->send(new ChequeBounceAreEmail($isPosted));
						event(new ChequeBounceAre($isPosted,$users)); 
              /*-----------Are Email Ends--------------------*/
					}
              return response()->json(['success'=>'Data is successfully added']);
          }

      }
   /*
    Ajax popup bounce reason

   */
    public function tenantAjaxPdcExchange(Request $request){

        $tenant_contract    = $request->tenant_contract;
        $pdc_check_no       = $request->pdc_check_no;
        $pdc_check_date     = $request->pdc_check_date;
        $pdc_amt            = replaceCommaWithDot($request->pdc_amt);
        $pdc_stage          = $request->pdc_stage;
        $bank_id            = $request->bank_id;
        $pdc_recieve_date   = $request->pdc_recieve_date;
        $pdc_reference      = $request->pdc_reference;

        if(!$tenant_contract)
            return response()->json(['error'=>'Something Error in Server']);


        $isPosted           = Pdc::where('id',$pdc_reference)->first(); 

        $pdcCount           = Pdc::where('tenant_contract_id',$tenant_contract)->count();

        Pdc::create(['tenant_contract_id' => $tenant_contract,
                 'pdc_transaction_no' => $isPosted->pdc_transaction_no, // Rent
                 'pdc_transaction_date' => $isPosted->pdc_transaction_date,
                 'pdc_check_no' => $pdc_check_no,
                 'pdc_check_date' => $pdc_check_date,
                 'pdc_recieve_date' => $pdc_recieve_date,
                 'pdc_period' => $pdcCount+1,
                 'pdc_amt' => $pdc_amt,
                 'bank_id' => $bank_id, 
                 'pdc_stage' =>$pdc_stage, // Active
                 'pdc_type' => 1, // 1 -  Rent, 2 - Deposit
                 'pdc_is_saved' => 1, //1- Saved
                 'created_by' => \Auth::user()->id
                 ]);   

        if(empty($isPosted->pdc_is_posted)){ // Delete status - 2

           Pdc::where('id',$isPosted->id)->update(['pdc_cancel_date'=>date('Y-m-d'),'pdc_cancel_reason'=>2,'pdc_is_saved'=>2,'pdc_cancel_by' => \Auth::user()->id]);

           ReceiptsGeneration::where('receipts_generation_receipt_no',$isPosted->pdc_receipt_no)->update(['receipts_generation_cancel_date'=>date('Y-m-d'),'receipts_generation_status'=>2,'receipts_generation_reason_cancel'=>'PDC Exchange','receipts_generation_cancel_by' => \Auth::user()->id]);

           \Modules\BackOffice\Services\TerminationDuesService::touchContract($isPosted->tenant_contract_id);

           return response()->json(['success'=>'Data is successfully added']);
       }
       else{

        Pdc::where('id',$isPosted->id)->update(['pdc_cancel_date'=>date('Y-m-d'),'pdc_cancel_reason'=>2,'pdc_cancel_by' => \Auth::user()->id]);

        ReceiptsGeneration::where('receipts_generation_receipt_no',$isPosted->pdc_receipt_no)->update(['receipts_generation_cancel_date'=>date('Y-m-d'),'receipts_generation_status'=>2,'receipts_generation_reason_cancel'=>'PDC Exchange','receipts_generation_cancel_by' => \Auth::user()->id]);

        \Modules\BackOffice\Services\TerminationDuesService::touchContract($isPosted->tenant_contract_id);

        return response()->json(['success'=>'Data is successfully added']);
    }


}
public function pdcView($contract_id) 
{
  $tenantContract = TenantContract::where('id',$contract_id)->first();
  $pdc_transaction_no = null;
  $pdc_transaction_date = null;
        // If contract is approved
  if($tenantContract->work_flow_processes_code >=106){
    $tenantPdcLatest = Pdc::orderBy('id', 'desc')->first();

    $tenantPdcInfo  = Pdc::where('tenant_contract_id', $contract_id)->orderBy('id', 'asc')->get();
    if(count($tenantPdcInfo) > 0){
        $pdc_transaction_date = $tenantPdcInfo[0]->pdc_transaction_date;
        $pdc_transaction_no = $tenantPdcInfo[0]->pdc_transaction_no;
    }
    $bankMaster = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();

    return view('backoffice::Pdc.contract_pdc_view',compact('tenantContract','bankMaster','tenantPdcInfo','pdc_transaction_no','pdc_transaction_date'));

}
else{
           // Contract in-progress
   return redirect()->route('tenant-contract.index');
}

}
public function getPdcExchangeDetails(Request $request){
  $pdc_id = $request->input('pdc_id');
      //dd($pdc_id);
  $nowUrl = url()->previous();
  Session::put('nowUrl', $nowUrl);
  $pdc=Pdc::where('id',$pdc_id)->first();
     // dd($pdc);
  $banks = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();
  return view('backoffice::Pdc.pdc_exchange_view',compact('pdc','nowUrl','banks'));
}
//get Bounced Cheques
public function getBouncedCheques(Request $request){
    return self::bouncedListView(Pdc::areFilter()->openBounces(), $request, 'Bounced cheques',
        'Open bounces on active contracts: not settled, no bounce receipt, not redeposited');
}

/**
 * Bounced cheques list (ARE / back office manager card, MD month-to-date): summary, filters
 * (search, building, ARE, age since the cheque date) and one row per cheque, oldest first.
 */
public static function bouncedListView($query, Request $request, $title, $subtitle){
    $all = $query->with(['tenantContractInfo.building', 'tenantContractInfo.Unit', 'tenantContractInfo.tenant', 'bankInfo'])
        ->orderBy('pdc_check_date')->orderBy('id')->get();

    $buildingIds = $all->pluck('tenantContractInfo.building_id')->filter()->unique()->values()->all();
    $areNames = [];
    if ($buildingIds) {
        $rows = DB::table('preferred_buildings as pb')
            ->join('are_buildings as ab', 'ab.id', '=', 'pb.are_building_id')
            ->join('users as u', 'u.id', '=', 'ab.user_id')
            ->join('employees as e', 'e.id', '=', 'u.user_type_id')
            ->whereNull('pb.assign_to')->whereIn('pb.building_id', $buildingIds)
            ->select('pb.building_id', 'e.employee_name')->distinct()->orderBy('e.employee_name')->get();
        foreach ($rows as $r) {
            $areNames[$r->building_id][] = $r->employee_name;
        }
    }

    $today = Carbon::today();
    $ages = ['0-7' => [0, 7], '8-30' => [8, 30], '31-90' => [31, 90], '90+' => [91, PHP_INT_MAX]];
    $search = trim((string) $request->search);
    $pdc = $all->filter(function ($p) use ($request, $search, $areNames, $ages, $today) {
        $c = $p->tenantContractInfo;
        if ($request->building && (int) $c->building_id !== (int) $request->building) {
            return false;
        }
        if ($request->are && !in_array($request->are, isset($areNames[$c->building_id]) ? $areNames[$c->building_id] : [], true)) {
            return false;
        }
        if ($request->age && isset($ages[$request->age])) {
            $d = $p->pdc_check_date ? Carbon::parse($p->pdc_check_date)->diffInDays($today, false) : 0;
            if ($d < $ages[$request->age][0] || $d > $ages[$request->age][1]) {
                return false;
            }
        }
        if ($search !== '') {
            $hay = implode(' ', [$c->tenant->tenant_name ?? '', $c->tenant->tenant_contact_no ?? '', $c->tenant->gsm_no ?? '',
                $c->building->building_name ?? '', $c->Unit->unit_no ?? '', $c->tenant_contract_no, $p->pdc_check_no]);
            if (stripos($hay, $search) === false) {
                return false;
            }
        }
        return true;
    })->values();

    $buildings = $all->pluck('tenantContractInfo.building')->filter()->unique('id')->sortBy('building_name')->values();
    $ares = collect($areNames)->flatten()->unique()->sort()->values();
    $showAre = !(count(\Auth::user()->roles) == 1 && \Auth::user()->hasRole('are'));
    $isFiltered = $request->search || $request->building || $request->are || $request->age;
    $newTenantContracts = self::firstContractIds($all->pluck('tenant_contract_id')->unique()->values()->all());

    return view('backoffice::Pdc.bounced_cheque_list', compact('pdc', 'all', 'areNames', 'buildings', 'ares', 'ages',
        'showAre', 'isFiltered', 'title', 'subtitle', 'request', 'today', 'newTenantContracts'));
}

/**
 * Contracts (of the given ids) that are the tenant's first contract: no earlier contract for the
 * tenant, nor for another tenant record with the same resident ID or mobile (duplicate tenant
 * records). Renewals always have an earlier contract, so they are never first. Returns [id => true].
 */
public static function firstContractIds(array $contractIds){
    if (!$contractIds) {
        return [];
    }
    $rows = DB::table('tenant_contracts as tc')
        ->join('tenant as t', 't.id', '=', 'tc.tenant_id')
        ->whereIn('tc.id', $contractIds)
        ->whereNotExists(function ($q) {
            $q->select(DB::raw(1))->from('tenant_contracts as o')
              ->join('tenant as t2', 't2.id', '=', 'o.tenant_id')
              ->whereColumn('o.id', '<>', 'tc.id')
              ->whereColumn('o.tenant_contract_effective_date', '<', 'tc.tenant_contract_effective_date')
              ->where(function ($w) {
                  $w->whereColumn('o.tenant_id', 'tc.tenant_id')
                    ->orWhereRaw("(nullif(trim(t2.resident_id), '') is not null and trim(t2.resident_id) = trim(t.resident_id))")
                    ->orWhereRaw("(nullif(trim(t2.tenant_contact_no), '') is not null and trim(t2.tenant_contact_no) = trim(t.tenant_contact_no))");
              });
        })
        ->pluck('tc.id')->all();
    return array_fill_keys($rows, true);
}


public function printPreviewstage($contract_id,$stage){

        $tenantContract = TenantContract::where('id',$contract_id)->first();
       $pdc_transaction_no = null;
       $pdc_transaction_date = null;
            // If contract is approved
        $tenantPdcLatest = Pdc::orderBy('id', 'desc')->first();
        
        $tenantPdcInfo  = Pdc::where('tenant_contract_id', $contract_id)->orderBy('id', 'asc')->get();
        if(count($tenantPdcInfo) > 0){
            $pdc_transaction_date = $tenantPdcInfo[0]->pdc_transaction_date;
            $pdc_transaction_no = $tenantPdcInfo[0]->pdc_transaction_no;
        }


        if($stage != 0){

            $tenantPdcInfo  = Pdc::where('tenant_contract_id', $contract_id)->where('pdc_stage',$stage)->orderBy('id', 'asc')->get();
            if(count($tenantPdcInfo) > 0){
                $pdc_transaction_date = $tenantPdcInfo[0]->pdc_transaction_date;
                $pdc_transaction_no = $tenantPdcInfo[0]->pdc_transaction_no;
            }

        }

        $bankMaster = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();
        $tenant_bank = Bank::where('id',$tenantPdcInfo[0]->bank_id)->get();

    return view('backoffice::Pdc.print_view',compact('tenantContract','bankMaster','tenantPdcInfo','pdc_transaction_no','pdc_transaction_date','tenant_bank','stage'));
}


public function printPreview($contract_id){

       //$contract_id = $request->input('id');

       //  clearNotification('Modules\BackOffice\Notifications\ChequeBounceNotification',$contract_id);      
       //  $tenantContract = TenantContract::where('id',$contract_id)->first();
       //  $salesEnquiry = SalesEnquiry::where('id',$tenantContract->sale_enquiry_id)->first();
       //  $pdc_transaction_date =null;
        
       //  // In Renewed Contract no sales enquiryID And Workflow only 108. 9- Approved, 5 - Renew contract
       //  if(in_array($tenantContract->tenant_renewal_termination_status,[9,5])){
       //      $tenantPdcLatest = Pdc::orderBy('id', 'desc')->first();
            
       //      $tenantPdcInfo  = Pdc::where('tenant_contract_id', $contract_id)->orderBy('id', 'asc')->get();
       //      if(count($tenantPdcInfo) > 0)
       //          $pdc_transaction_date = $tenantPdcInfo[0]->pdc_transaction_date;

       //      $bankMaster = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();

       //      $prefix  = prefixData('tenant_pdc_prefix')->configuration_value;
            
       //      if(!empty($tenantPdcLatest))
       //          $pdcTransacNo = $prefix.str_pad($tenantPdcLatest->id+1,4,'0',STR_PAD_LEFT);
       //      else
       //          $pdcTransacNo = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);

       //      return view('backoffice::Pdc.print_view',compact('tenantContract','pdcTransacNo','bankMaster','tenantPdcInfo','pdc_transaction_date'));

       //  }
       //  elseif($tenantContract->work_flow_processes_code >=106 || $salesEnquiry->work_flow_processes_code >=106){
       //      $tenantPdcLatest = Pdc::orderBy('id', 'desc')->first();
            
       //      $tenantPdcInfo  = Pdc::where('tenant_contract_id', $contract_id)->orderBy('id', 'asc')->get();
       //      if(count($tenantPdcInfo) > 0)
       //          $pdc_transaction_date = $tenantPdcInfo[0]->pdc_transaction_date;

       //      $bankMaster = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();

       //      $prefix  = prefixData('tenant_pdc_prefix')->configuration_value;
            
       //      if(!empty($tenantPdcLatest))
       //          $pdcTransacNo = $prefix.str_pad($tenantPdcLatest->id+1,4,'0',STR_PAD_LEFT);
       //      else
       //          $pdcTransacNo = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);

       //      return view('backoffice::Pdc.print_view',compact('tenantContract','pdcTransacNo','bankMaster','tenantPdcInfo','pdc_transaction_date'));
       //  }
       //  else{
       //     // Contract in-progress
       //     return redirect()->route('tenant-contract.index');
       // }

       // return view('backoffice::edit');



       $tenantContract = TenantContract::where('id',$contract_id)->first();
       $pdc_transaction_no = null;
       $pdc_transaction_date = null;
            // If contract is approved
        $tenantPdcLatest = Pdc::orderBy('id', 'desc')->first();
        
        $tenantPdcInfo  = Pdc::where('tenant_contract_id', $contract_id)->orderBy('id', 'asc')->get();
        if(count($tenantPdcInfo) > 0){
            $pdc_transaction_date = $tenantPdcInfo[0]->pdc_transaction_date;
            $pdc_transaction_no = $tenantPdcInfo[0]->pdc_transaction_no;
        }


        if(isset($stage)){

            $tenantPdcInfo  = Pdc::where('tenant_contract_id', $contract_id)->where('pdc_stage',$stage)->orderBy('id', 'asc')->get();
            if(count($tenantPdcInfo) > 0){
                $pdc_transaction_date = $tenantPdcInfo[0]->pdc_transaction_date;
                $pdc_transaction_no = $tenantPdcInfo[0]->pdc_transaction_no;
            }

        }

        $bankMaster = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();
        $tenant_bank = Bank::where('id',$tenantPdcInfo[0]->bank_id)->get();

    return view('backoffice::Pdc.print_view',compact('tenantContract','bankMaster','tenantPdcInfo','pdc_transaction_no','pdc_transaction_date','tenant_bank'));
  }

  /**
   * Tell the tenant their cheque bounced: email (contact, else personal) and SMS
   * to the contact number. Failures are logged only, so the bounce itself never fails.
   */
  private function notifyTenantBounce(Pdc $pdc)
  {
      $tenant = $pdc->tenantContractInfo->tenant ?? null;
      if (!$tenant) {
          return;
      }

      $email = $tenant->tenant_contact_email ?: $tenant->tenant_personal_email;
      if (self::TENANT_BOUNCE_EMAIL && !empty($email)) {
          try {
              Mail::to($email)->send(new ChequeBounceEmail($pdc));
          } catch (\Throwable $e) {
              Log::error('Tenant bounce email failed for PDC #' . $pdc->id . ': ' . $e->getMessage(), ['email' => $email]);
          }
      }

      if (!empty($tenant->tenant_contact_no)) {
          try {
              $msg = "Dear Tenant, your cheque {$pdc->pdc_check_no} for " . number_format($pdc->pdc_amt, 3)
                   . ' dated ' . Carbon::parse($pdc->pdc_check_date)->format('d-m-Y') . ' was returned. Please pay immediately. - Al Habib';
              if (!sendSms($tenant->tenant_contact_no, $msg, [])) {
                  Log::warning('Tenant bounce SMS not sent for PDC #' . $pdc->id . ', mobile: ' . $tenant->tenant_contact_no);
              }
          } catch (\Throwable $e) {
              Log::error('Tenant bounce SMS failed for PDC #' . $pdc->id . ': ' . $e->getMessage());
          }
      }
  }

  /**
   * Copy of the ARE bounce email to the addresses in Settings -> Cheque Bounce
   * Copy Emails (comma separated), sent even when the building has no ARE.
   * Failures are logged only.
   */
  private function notifyBounceCopies(Pdc $pdc)
  {
      $setting = prefixData('cheque_bounce_copy_emails');
      $list = $setting ? preg_split('/[\s,;]+/', (string) $setting->configuration_value) : [];
      foreach (array_unique(array_filter($list)) as $email) {
          if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              continue;
          }
          try {
              Mail::to($email)->send(new ChequeBounceAreEmail($pdc, 'Team'));
          } catch (\Throwable $e) {
              Log::error('Bounce copy email failed for PDC #' . $pdc->id . ': ' . $e->getMessage(), ['email' => $email]);
          }
      }
  }

  /**
   * Send SMS reminder to tenants 3 days before PDC cheque date
   */
  public function pdcExpiryReminderSms()
  {
      // TODO: Restore time check after testing
      if (Carbon::now()->hour != 10 || Carbon::now()->minute != 0) {
          return;
      }

      Log::info('pdcExpiryReminderSms: Started at ' . Carbon::now()->toDateTimeString());

      try {
          $reminderDate = Carbon::now()->addDays(5)->format('Y-m-d');

          // Get rent PDCs (pdc_type=1) with check_date = 5 days from now
          // that are not cancelled (pdc_is_saved != 2), not cleared, not bounced
          $pdcs = Pdc::where('pdc_type', 1)
              ->whereDate('pdc_check_date', $reminderDate)
              ->where(function($q) {
                  $q->whereNull('pdc_cancel_date')
                    ->whereNull('pdc_clear_date');
              })
              ->where(function($q) {
                  $q->where('pdc_is_saved', '!=', 2)
                    ->orWhereNull('pdc_is_saved');
              })
              ->with('tenantContractInfo.tenant')
              ->get();

          Log::info('pdcExpiryReminderSms: Found ' . $pdcs->count() . ' PDCs for date ' . $reminderDate);

          $sentCount = 0;
          $failCount = 0;

          foreach ($pdcs as $pdc) {
              // System template pdc_due_reminder (SMS); every attempt is in Mail > Sent log
              $status = \Modules\BackOffice\Services\TenantMessages::pdcDueReminder($pdc);
              if ($status === \Modules\BackOffice\Entities\MessageLog::SENT) {
                  $sentCount++;
              } else {
                  $failCount++;
                  Log::warning('pdcExpiryReminderSms: PDC #' . $pdc->id . ' not sent (' . ($status ?: 'SMS switched off') . ')');
              }
          }

          Log::info("pdcExpiryReminderSms: Completed. Sent: {$sentCount}, Failed: {$failCount}");

      } catch (\Exception $e) {
          Log::error('pdcExpiryReminderSms: Exception - ' . $e->getMessage(), [
              'trace' => $e->getTraceAsString()
          ]);
      }
  }

}
