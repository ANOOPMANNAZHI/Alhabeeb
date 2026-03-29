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
use Carbon\Carbon;

class PdcController extends Controller
{
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
                if($request->{'pdc_is_saved_'.$i} == 1 && $request->{'pdc_check_no_'.$i} > 0){

                    if($request->{'pdc_reference_'.$i} >0 ){
                        $pdc_reference = $request->{'pdc_reference_'.$i};
                        $cancel = date('Y-m-d');

                    }
                    if(isset($request->{'pdc_cancel_reason_'.$i})){
                        $cancel = date('Y-m-d');
                    }
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
                         //'1 -Insufficient Funds , 2 -Signature Missing , 3 -Signature Mismatch, 4 -Word in amount and figure differ ,5 -Stop Payment , 6 -Refer to Drawer,7 -Correction , 8 -Stale Cheque (Beyond six months), 9 - Misc';
                        'pdc_is_saved'=> 1,     
                        'pdc_type' =>$request->{'pdc_type_'.$i},
                        'pdc_remark' =>$request->{'pdc_remark_'.$i},
                        'pdc_cheque_acknowledge' =>$request->{'pdc_cheque_acknowledge_'.$i},
                        'pdc_from_date' =>$request->{'pdc_from_date_'.$i},
                        'pdc_to_date' =>$request->{'pdc_to_date_'.$i},
                        'created_by' => \Auth::user()->id
                        ]); 

                    /* Check whether it is bounce*/

                    $bounce = $request->{'pdc_cancel_reason_'.$i};
                    if($bounce == 1){
                        $pdc_id = $request->{'pdc_check_id_'.$i};
                        $pdcData = Pdc::where('id',$pdc_id)->first(); 

                        /*-----------Tenant Email Starts--------------------
                        if(!empty($pdcData->tenantContractInfo->tenant->tenant_contact_email) || !empty($pdcData->tenantContractInfo->tenant->tenant_personal_email)){
                            $tenant_email = $pdcData->tenantContractInfo->tenant->tenant_contact_email ?
                            $pdcData->tenantContractInfo->tenant->tenant_contact_email:$pdcData->tenantContractInfo->tenant->tenant_personal_email ;
                            Mail::to($tenant_email)->send(new ChequeBounceEmail($pdcData));
                        }
                        -----------Tenant Email Ends--------------------*/

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

            return view('backoffice::Pdc.contract_pdc',compact('tenantContract','pdcTransacNo','bankMaster','tenantPdcInfo','pdc_transaction_date'));

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

            return view('backoffice::Pdc.contract_pdc',compact('tenantContract','pdcTransacNo','bankMaster','tenantPdcInfo','pdc_transaction_date'));
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



        if(empty($isPosted->pdc_is_posted)){ // Delete status - 2

            Pdc::where('id',$pdc_id)->update(['pdc_cancel_date'=>date('Y-m-d')    ,'pdc_cancel_reason'=>1 ,'pdc_bounce_reason'=>$pdc_bounce_reason,'pdc_is_saved'=>2,'pdc_cancel_by' => \Auth::user()->id]);

            ReceiptsGeneration::where('receipts_generation_receipt_no',$isPosted->pdc_receipt_no)->update(['receipts_generation_cancel_date'=>date('Y-m-d'),'receipts_generation_status'=>2,'receipts_generation_reason_cancel'=>'PDC Bounce','receipts_generation_cancel_by' => \Auth::user()->id]);

            /*-----------Tenant Email Starts--------------------
            if(!empty($isPosted->tenantContractInfo->tenant->tenant_personal_email) || !empty($isPosted->tenantContractInfo->tenant->tenant_contact_email)){
                $tenant_email = $isPosted->tenantContractInfo->tenant->tenant_personal_email ?
                $isPosted->tenantContractInfo->tenant->tenant_personal_email:$isPosted->tenantContractInfo->tenant->tenant_contact_email ;
                Mail::to($tenant_email)->send(new ChequeBounceEmail($isPosted));
            }*/
            /*-----------Tenant Email Ends--------------------*/
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

            Pdc::where('id',$pdc_id)->update(['pdc_cancel_date'=>date('Y-m-d')    ,'pdc_cancel_reason'=>1 ,'pdc_bounce_reason'=>$pdc_bounce_reason,'pdc_cancel_by' => \Auth::user()->id]);

            ReceiptsGeneration::where('receipts_generation_receipt_no',$isPosted->pdc_receipt_no)->update(['receipts_generation_cancel_date'=>date('Y-m-d'),'receipts_generation_status'=>2,'receipts_generation_reason_cancel'=>'PDC Bounce','receipts_generation_cancel_by' => \Auth::user()->id]);

            /*-----------Tenant Email Starts--------------------
            if(!empty($isPosted->tenantContractInfo->tenant->tenant_personal_email) || !empty($isPosted->tenantContractInfo->tenant->tenant_contact_email )){
                $tenant_email = $isPosted->tenantContractInfo->tenant->tenant_personal_email ?
                $isPosted->tenantContractInfo->tenant->tenant_personal_email:$isPosted->tenantContractInfo->tenant->tenant_contact_email ;
                Mail::to($tenant_email)->send(new ChequeBounceEmail($isPosted));
            }*/
            /*-----------Tenant Email Ends--------------------*/
     
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

           return response()->json(['success'=>'Data is successfully added']);
       }
       else{

        Pdc::where('id',$isPosted->id)->update(['pdc_cancel_date'=>date('Y-m-d'),'pdc_cancel_reason'=>2,'pdc_cancel_by' => \Auth::user()->id]);

        ReceiptsGeneration::where('receipts_generation_receipt_no',$isPosted->pdc_receipt_no)->update(['receipts_generation_cancel_date'=>date('Y-m-d'),'receipts_generation_status'=>2,'receipts_generation_reason_cancel'=>'PDC Exchange','receipts_generation_cancel_by' => \Auth::user()->id]);

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
public function getBouncedCheques(){
    $pdc= Pdc::areFilter()->bouncedCheques()->get();
    return view('backoffice::Pdc.bounced_cheque_list',compact('pdc'));
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
   * Send SMS reminder to tenants 3 days before PDC cheque date
   */
  public function pdcExpiryReminderSms()
  {
      // Run only at 8 AM to avoid duplicate SMS (command runs every minute)
      if (Carbon::now()->hour != 8) {
          return;
      }

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

      foreach ($pdcs as $pdc) {
          $tenant = $pdc->tenantContractInfo->tenant ?? null;
          if (!$tenant || empty($tenant->tenant_contact_no)) {
              continue;
          }

          $amount = number_format($pdc->pdc_amt, 2);
          $chequeDate = Carbon::parse($pdc->pdc_check_date)->format('d-m-Y');
          $chequeNo = $pdc->pdc_check_no;

          $msg = "Dear Tenant, your cheque No. {$chequeNo} of amount {$amount} is due on {$chequeDate}. Please ensure sufficient funds are available in your account to avoid penalty charges. - Al Habib";

          sendSms($tenant->tenant_contact_no, $msg, []);
      }
  }

}
