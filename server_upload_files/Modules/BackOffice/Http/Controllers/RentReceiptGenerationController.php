<?php

namespace Modules\BackOffice\Http\Controllers;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Sales\Entities\Tenant;
use Modules\Sales\Entities\TenantContract;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Entities\ReceiptGenerationDim;
use Modules\BackOffice\Entities\ViewReceipt;
use Modules\BackOffice\Entities\DimDetail;
use Modules\BackOffice\Entities\Pdc;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Bank;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\AccountParams;
use Modules\BackOffice\Events\ReceiptApprove;
use Modules\BackOffice\Events\ReceiptReject;
use Spatie\Activitylog\Models\Activity;
use Session;
use DB;
use App\User;
use Carbon\Carbon;
use Dynamics;
use App\Setting;

class RentReceiptGenerationController extends Controller
{
    use ValidatesRequests;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function __construct()
    {
        $this->middleware('auth');  
        $this->middleware('permission:receipt_approval_list', ['only' => ['receiptsRequestForApprovalTabViewList']]);

        
        //$this->middleware('permission:list_tenant_receipt', ['only' => ['receiptsTabViewList']]);
        $this->middleware('permission:list_tenant_receipt', ['only' => ['add','store']]);
        $this->middleware('permission:edit_tenant_receipt', ['only' => ['edit','update']]); 
        $this->middleware('permission:delete_tenant_receipt', ['only' => ['destroy']]);
        $this->middleware('permission:view_tenant_receipt', ['only' => ['show']]);  
        $this->middleware('permission:post_tenant_receipt', ['only' => ['receiptAsPosted']]);   
        $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value;   
    }
    public function index($request =null)
    {
        
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    // 1 - general receipt, 0 - tenant receipt, 2- deposit receipt
    public function create()
    {
		
        $receipt_list      = array();
		
		// 1 - cheque, 2 - cash - receipt No sequence
		$cashReceiptNo    = $this->receiptGenerateCode('receipt_cash');	
        $chequeReceiptNo  = $this->receiptGenerateCode('receipt_cheque');	
        $yearCheque    = prefixData('receipt_cheque')->configuration_year;	
        $isYearChequeCorrect = (date('y') == $yearCheque)?true:false;	
        $yearCash   = prefixData('receipt_cash')->configuration_year;	
        $isYearCashCorrect = (date('y') == $yearCash)?true:false;	
        	
        $dimList           = DimDetail::all();
		
        $bankMaster = Bank::Where('bank_status', 1)->where('accounts_bank',1)->orderBy('bank_name', 'ASC')->get();     
        return view('backoffice::Receipt.rent_payment_receipt_create',compact('receipt_list','cashReceiptNo','chequeReceiptNo','bankMaster','dimList','isYearChequeCorrect','isYearCashCorrect'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'receipts_generation_receipt_no' => 'required',
            'receipts_generation_receipt_date'=> 'required',
            'tenant_contract_no' => 'required',                    
            'receipts_generation_payment_method'   => 'required',
            'receipts_generation_amt'   => 'required',
            
         ]);
         // 1 - cheque, 2 - cash - receipt No sequence
        // 1 - cheque, 2 - cash - receipt No sequence        	
        if($request->receipts_generation_payment_method == 1){	
            $receiptId  = $this->receiptGenerateCode('receipt_cheque');	
            $configIncKey = 'receipt_cheque';
		}
		if($request->receipts_generation_payment_method == 2){	
            $receiptId    = $this->receiptGenerateCode('receipt_cash');	
            $configIncKey = 'receipt_cash';
		}
        if($request->receipts_generation_payment_method == 3){  
            $receiptId  = $this->receiptGenerateCode('receipt_cheque'); 
            $configIncKey = 'receipt_cheque';
        }
		
        $dimList           = DimDetail::all();
		
		//Requirement change due to that make it Zero	
		$rentIndexNo = 0;
		
        $user = \Auth::user();
		$permissionExist = $user->hasPermissionTo('request_approval_receipt');
		if($permissionExist){ // As Permission for selfApprove
			$receipts_generation_approval_status = 3;
			$receipts_generation_status = 1;
		}else{
			$receipts_generation_approval_status = 1;
			$receipts_generation_status = 0;
		}
		
        $receiptId = ReceiptsGeneration::create(['tenant_contract_id' => $request->tenant_contract_no,
                     'receipts_generation_receipt_no' =>$receiptId ,
                     'receipts_generation_receipt_date' => $request->receipts_generation_receipt_date,
                     'receipts_generation_payment_method' => $request->receipts_generation_payment_method, //1 - cheque, 2 - cash
                     'bank_id' => $request->bank_id,
                     'receipts_generation_amt' => replaceCommaWithDot($request->receipts_generation_amt),
                     'receipts_generation_cheque_no' => $request->pdc_bank_id.'__'.$request->cheque_no,                
                     'receipts_generation_description' =>$request->receipts_generation_description, 
                     'receipts_generation_remark' => $request->receipts_generation_remark, // Active
                     'receipts_generation_status' => 0 ,
                     'receipts_generation_eff_from' => $request->receipts_generation_eff_from,
                     'receipts_generation_eff_to' => $request->receipts_generation_eff_to,
                     'receipts_generation_index' => $rentIndexNo,
                     'receipts_generation_status' => $receipts_generation_status,
                     //0- InActive(default), 1 - Active , 2 - Cancel, 3 - Posted
                     'receipts_generation_approval_status' => $receipts_generation_approval_status,
                     // 1 - unapproval(default), 2 -Pending, 3 - Approved, 4 - Reject
                     'receipts_generation_type' => 0, //1 - general receipt, 0 - tenant receipt, 2- deposit receipt
                     'finance_dim' => null,
                     'receipts_generation_is_bounce_normal'=>isset($request->pay_type)?$request->pay_type:1,
                     'receipts_generation_is_pdc_bounce_id'=>(isset($request->pay_type) && $request->pay_type == 3)
                         ? (isset($request->pdc_replace_cheque) ? $request->pdc_replace_cheque : null)
                         : (isset($request->pdc_bounce_cheque) ? $request->pdc_bounce_cheque : null),
                     'created_by' => \Auth::user()->id
                 ]);

        // If Replace: update PDC cancel reason and remark at save time so PDC page reflects it immediately
        if (isset($request->pay_type) && $request->pay_type == 3 && !empty($request->pdc_replace_cheque)) {
            $pdcToReplace = Pdc::where('id', $request->pdc_replace_cheque)->first();
            $chequeNo = $pdcToReplace ? $pdcToReplace->pdc_check_no : '';
            Pdc::where('id', $request->pdc_replace_cheque)->update([
                'pdc_cancel_reason' => 3,
                'pdc_remark'        => 'Received cash payment for cheque no ' . $chequeNo,
            ]);
        }

		//Update next increment value
		$this->incrementSequenceNo($configIncKey);

        session()->flash('success', 'Saved Successfully');
        return redirect()->route('receiptsTabViewListtab','rent');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        clearNotification('Modules\BackOffice\Notifications\ReceiptNotification',$id);
        readNotification('Modules\BackOffice\Notifications\ReceiptNotification',$id);
        $bankMaster = Bank::Where('bank_status', 1)->where('accounts_bank',1)->orderBy('bank_name', 'ASC')->get(); 
        $dimList = DimDetail::all();
		// Distribution Dimension details
        $acc_parameter  = AccountParams::where('acc_params_tran_desc','Nor_Mgt_receipts')->first();
        $rentReceiptInfo = ReceiptsGeneration::where('id', $id)->first();

        // print_r($rentReceiptInfo->count());exit();

        return view('backoffice::Receipt.rent_payment_receipt_view',compact('rentReceiptInfo','bankMaster','dimList','acc_parameter'));
    }

    /**
     * Tenant receipt edit
     * @param int $id
     * @return Response
     */
   public function edit($id)
    {
       
        $rentReceiptInfo = ReceiptsGeneration::where('id', $id)->first();
        $dimList = DimDetail::all();
        
        $bankMaster = Bank::Where('bank_status', 1)->where('accounts_bank',1)->orderBy('bank_name', 'ASC')->get();    
        $contract_bounce_pdc  = Pdc::where('tenant_contract_id',$rentReceiptInfo->tenant_contract_id)->where('pdc_cancel_reason',1)->get();
        $contract_replace_pdc = Pdc::where('tenant_contract_id',$rentReceiptInfo->tenant_contract_id)->whereNull('pdc_receipt_no')->get();

		// 1 - cheque, 2 - cash - receipt No sequence
		$cashReceiptNo    = $this->receiptGenerateCode('receipt_cash');
		$chequeReceiptNo  = $this->receiptGenerateCode('receipt_cheque');

		$yearCheque    = prefixData('receipt_cheque')->configuration_year;
        $isYearChequeCorrect = (date('y') == $yearCheque)?true:false;
        $yearCash   = prefixData('receipt_cash')->configuration_year;
        $isYearCashCorrect = (date('y') == $yearCash)?true:false;

        return view('backoffice::Receipt.rent_payment_receipt_create',compact('rentReceiptInfo','cashReceiptNo','bankMaster','dimList','chequeReceiptNo','cashReceiptNo','contract_bounce_pdc','contract_replace_pdc','isYearChequeCorrect','isYearCashCorrect'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'receipts_generation_receipt_no' => 'required',
            'receipts_generation_receipt_date'=> 'required',
            'tenant_contract_no' => 'required',  
            'receipts_generation_amt'   => 'required',
            
         ]);
		 
		 $rentReceiptInfo = ReceiptsGeneration::where('id', $id)->first();
		 
		$dimList           = DimDetail::all();	
        
        $receiptId = ReceiptsGeneration::where('id',$id)->update(['tenant_contract_id' => $request->tenant_contract_no,
                     'receipts_generation_receipt_no' =>$request->receipts_generation_receipt_no ,
                     'receipts_generation_receipt_date' => $request->receipts_generation_receipt_date,
                     'bank_id' => $request->bank_id,
                     'receipts_generation_amt' => replaceCommaWithDot($request->receipts_generation_amt),
                     'receipts_generation_cheque_no' => $request->pdc_bank_id.'__'.$request->cheque_no,                
                     'receipts_generation_description' =>$request->receipts_generation_description, 
                     'receipts_generation_remark' => $request->receipts_generation_remark, // Active
                     //'receipts_generation_status' => 0 ,
                     //0- InActive(default), 1 - Active , 2 - Cancel, 3 - Posted
                     //'receipts_generation_approval_status' => 1,
                     // 1 - unapproval(default), 2 -Pending, 3 - Approved, 4 - Reject
                     'receipts_generation_eff_from' => $request->receipts_generation_eff_from,
                     'receipts_generation_eff_to' => $request->receipts_generation_eff_to,
                     'receipts_generation_type' => 0, //1 - general receipt, 0 - tenant receipt, 2- deposit receipt
                     'finance_dim' => $request->finance_dim,
                     'receipts_generation_is_bounce_normal'=>isset($request->pay_type)?$request->pay_type:1,
                     'receipts_generation_is_pdc_bounce_id'=>(isset($request->pay_type) && $request->pay_type == 3)
                         ? (isset($request->pdc_replace_cheque) ? $request->pdc_replace_cheque : null)
                         : (isset($request->pdc_bounce_cheque) ? $request->pdc_bounce_cheque : null),
                     'created_by' => \Auth::user()->id
                 ]);

        // If Replace: update PDC cancel reason and remark at save time so PDC page reflects it immediately
        if (isset($request->pay_type) && $request->pay_type == 3 && !empty($request->pdc_replace_cheque)) {
            $pdcToUpdate = Pdc::where('id', $request->pdc_replace_cheque)->first();
            $chequeNo = $pdcToUpdate ? $pdcToUpdate->pdc_check_no : '';
            Pdc::where('id', $request->pdc_replace_cheque)->update([
                'pdc_cancel_reason' => 3,
                'pdc_remark'        => 'Received cash payment for cheque no ' . $chequeNo,
            ]);
        }

        session()->flash('success', 'Saved Successfully');
        return redirect()->route('receiptsTabViewListtab','rent');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(ReceiptsGeneration $receiptsgeneration, $id)
    {
        
        $rentReceiptInfo = ReceiptsGeneration::where('id', $id)->first();   
       
        if($rentReceiptInfo->receipts_generation_type){

            ReceiptGenerationDim::where('receipts_generation_id',$id)->delete();
        }

        try {
                        
            ReceiptsGeneration::where('id',$id)->delete();
            session()->flash('success', 'Receipt Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }
        activity('Deleted Receipt')
          ->performedOn($rentReceiptInfo)
          ->causedBy(\Auth::user()->id)
          ->withProperties($rentReceiptInfo)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        
		if($rentReceiptInfo->receipts_generation_type == 1) // General
            return redirect()->route('receiptsTabViewListtab','general');
        elseif($rentReceiptInfo->receipts_generation_type == 2) // Deposit
            return redirect()->route('receiptsTabViewListtab','deposit');
        else //Rent
            return redirect()->route('receiptsTabViewListtab','rent');
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
    /*
    *
    *  ContractSearchByMobile
    *
    */
    public function receiptContractDetails(Request $request){

        $building = array();
        $bul = array();
        $occupants = array();
        $unit = array();
        $buildingDetail = array();
        $buildingLocation = array();
        $mobile = $request->mobile;
        $receiptEff =array();
        $receiptRent =array();
        $tenant =array();
        $tenantContract = array();
        $resident_card_id = $request->resident_card_id;
        $building_id = $request->building_id;
        $unit_id = $request->unit_id;

        if($building_id !="" || $unit_id !=""){
          $tenantContract =  TenantContract::with(['Unit','building','tenant'])
                              ->has('Unit')                               
                              ->has('tenant')                               
                              ->when($unit_id, function ($query, $unit_id) {
                                   $query->where('unit_id',$unit_id);
                                return $query;
                              })->when($building_id, function ($query, $building_id) {
                                   $query->where('building_id',$building_id);
                                return $query;
                              })
							  ->orderBy('tenant_contract_status','DESC')
                              ->orderBy('tenant_contract_no','DESC')
							  ->get(); 
           
            $unit = $tenantContract->pluck('Unit');
            $unit =  $unit->unique('id');
            $sorted = $unit->sortBy('unit_no');
            $unit =  $sorted->values()->all();
         //   $unit = array_unique($unit->toArray(), SORT_REGULAR);
          //  $unit = $unit->toJson();
              if(count($unit) == 1){
              $unit = $unit[0];
             }
          
          
           
            $building = $tenantContract->pluck('building'); 
          //  $building = array_unique($building->toArray(), SORT_REGULAR);
            if(count($building) == 1)
             $building = $building[0]; 
          

           if(count($tenantContract) == 1){
            $tenantContract = $tenantContract[0];

            $tenant = $tenantContract->tenant;

         $receiptEff = $this->receiptEffectiveDateCalculation($tenantContract->id, $tenantContract->tenant_contract_effective_date, $tenantContract->tenant_contract_valid_to_date, $tenantContract->tenant_contract_payment_type );
            // Check contract validation end or not
          if(isset($receiptEff['eff_from']) && isset($receiptEff['eff_to']))
          $receiptRent = $this->contractRentCountCalculation($receiptEff['eff_from'], $receiptEff['eff_to'], $tenantContract->tenant_contract_rent );
  
        }

         if(empty($tenantContract)){ return 0; }

         return json_encode(array($tenantContract,$tenant,$building,$unit,$receiptEff,$receiptRent));
         

        } 

        
        if($mobile !=""){
          $tenant =  Tenant::active()->when($mobile, function ($query, $mobile) {
                            return $query->where('tenant_contact_no', $mobile);
                          })->first();
        }
        if($resident_card_id != ""){
          $tenant =  Tenant::active()->
                          when($resident_card_id, function ($query, $resident_card_id) {
                            return $query->where('resident_id', $resident_card_id);
                          })->first();
        }
        if(empty($tenant)){ return 0; }
        if(count($tenant->tenantContractsActive) > 1){

            return json_encode(array($tenant->tenantContractsActive,$tenant));
        }
        
        if(!empty($tenant)){

        
            foreach($tenant->tenantContractsActive as $contracts){
                $tenantContract = $contracts;
                $building = array('id'=> $contracts->building_id ,'building_name'=>$contracts->building->building_name,'building_code'=>$contracts->building->building_code);
                $unit = array('id'=> $contracts->unit_id ,'unit_no'=>$contracts->unit->unit_no,'unit_code'=>$contracts->unit->unit_code);
                $receiptEff = $this->receiptEffectiveDateCalculation($contracts->id, $contracts->tenant_contract_effective_date, $contracts->tenant_contract_valid_to_date, $contracts->tenant_contract_payment_type );
                // Check contract validation end or not
                if(isset($receiptEff['eff_from']) && isset($receiptEff['eff_to']))
                    $receiptRent = $this->contractRentCountCalculation($receiptEff['eff_from'], $receiptEff['eff_to'], $contracts->tenant_contract_rent );
            
            }

          
          if(isset($tenantContract)== 0){return 0;}  
	 
          return json_encode(array($tenantContract,$tenant,$building,$unit,$receiptEff,$receiptRent ));
         
         }else{
         
            return 0;
         
         }
        

       
        //return $tenant; 

    }
    /*
    *
    * get tenant and contract details by building , unit
    *
    *
    */
public function receiptContractDetailsByBuildingUnit(Request $request){

      $contractId = $request->contractId;
      $receiptRent = array();
      $contract   = TenantContract::where('id',$contractId)->first();

      $tenant = $contract->tenant;
      $buildingDetails = $contract->building;
      $unitDetails = $contract->unit;
      $receiptEff = $this->receiptEffectiveDateCalculation($contractId, $contract->tenant_contract_effective_date, $contract->tenant_contract_valid_to_date, $contract->tenant_contract_payment_type );
            // Check contract validation end or not
        if(isset($receiptEff['eff_from']) && isset($receiptEff['eff_to']))
        $receiptRent = $this->contractRentCountCalculation($receiptEff['eff_from'], $receiptEff['eff_to'], $contract->tenant_contract_rent );
      // $occupant = $contract->occupant;
      return json_encode(array($contract, $tenant,$buildingDetails,$unitDetails,$receiptEff ,$receiptRent));

    }
    /*  
        List view - tab

    */
    // 1 - general receipt, 0 - tenant receipt, 2- deposit receipt
     public function receiptsTabViewList(Request $request, $tab = 'rent')
    {

        $search_fields = [
        'receipts_generation_receipt_no' => 'Receipt No',
        'receipts_generation_eff_from' => 'Effective From',
        'receipts_generation_eff_to' => 'Effective To',
        'building_name' => 'Building Name',
        'building_code' => 'Building Code',
        'tenant_name' => 'Tenant Name',
        'tenant_code' => 'Tenant Code',
        'tenant_contract_no' => 'Contract No',   
        'receipts_generation_approval_status' => 'Status',    
        'receipts_generation_payment_method' => 'Payment Method',  
        'receipts_generation_amt' => 'Amount',  
        ];


        $operations = [
            '=' => ' Is Equal To '  ,
            '!=' => ' Is Not Equal To '  ,
            '>' => ' Is Greater Than '  ,
            '>=' => ' Is Greater Than Or Equal To '  ,
            '<' => ' Is Less Than '  ,
            '<=' => ' Is Less Than Or Equal To'  ,
            'ilike' => ' Like '  ,
            'ilike%...%' => ' Like%...% ',
        ];  

        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();
 
    $request->flash();     
    $route   =   $request->url();     
   // print_r($route);exit;
    if(!isset($request->are_id) || $request->are_id == ''){
       $receiptslist = ViewReceipt::whereNull('deleted_at')->filter($request)->type($tab)
                                        ->sortable()
                                        ->paginate($this->noOfRecord);  
    $are_id = ''; 
    $date = date('Y-m-d');
    }
    else{
        $headUser = $request->are_id;
        $date = isset($request->date) ?$request->date : date('Y-m-d');
        $firstOfMonth = date('Y-m-01',strtotime($request->date));
    
        $collectionMTD = ReceiptsGeneration::where('receipts_generation_type',0)
                ->where('deleted_at',null)
                ->whereDate('receipts_generation_receipt_date','>=', $firstOfMonth)
                ->whereDate('receipts_generation_receipt_date','<=', $date)
                ->whereHas('tenantContractInfo',function($query) use($headUser) {
                        $query->whereHas('building', function ($query) use($headUser){                       
                            $query->whereHas('areBuildings', function ($query) use($headUser){
                                $query->whereHas('areUser', function ($query) use($headUser){
                                    $query->where('user_id',$headUser);
                                
                                }); 
                            
                            });
                        });
                        
                    })
                ->pluck('id')->toArray();
            // print_r($collectionMTD);exit;
        $receiptslist = ViewReceipt::whereNull('deleted_at')->filter($request)->type($tab)
                                        ->sortable()->whereIn('id',$collectionMTD)
                                        ->paginate($this->noOfRecord);
        $are_id =  $headUser; 
        // $date = date('Y-m-d');
    }
   
                                     
    //dd($receiptslist);
    if(isset($request->ajax))
    return view('backoffice::Receipt.receipt_list_ajax',compact('receiptslist','tab','request','route','are_id','date')); 

    return view('backoffice::Receipt.receipt_list',compact('receiptslist','tab','search_fields','operations','route','are_id','date'));
       

    }
    /*
        Add Deposit receipt

    */
    public function addDepositReceipt()
    {
		$dimList        = DimDetail::all();
        $acc_code_dr    = null;
        $acc_code_cr    = null;

        $cashReceiptNo    = $this->receiptGenerateCode('receipt_cash');	
        $chequeReceiptNo  = $this->receiptGenerateCode('receipt_cheque');	
        $yearCheque    = prefixData('receipt_cheque')->configuration_year;	
        $isYearChequeCorrect = (date('y') == $yearCheque)?true:false;	
        $yearCash   = prefixData('receipt_cash')->configuration_year;	
        $isYearCashCorrect = (date('y') == $yearCash)?true:false;
        
        $bankMaster = Bank::Where('bank_status', 1)->where('accounts_bank',1)->orderBy('bank_name', 'ASC')->get();

        // Distribution Dimension details
        $acc_parameter  = AccountParams::where('acc_params_tran_desc','comp_mgt_deposit_receipts')->first();

            // Debit Amount
        if(isset($acc_parameter->acc_params_dr_acc))
          $acc_code_dr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_dr_acc)->first();
        
            // Credit Amount
        if(isset($acc_parameter->acc_params_cr_acc))
          $acc_code_cr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_cr_acc)->first();

        
       return view('backoffice::Receipt.deposit_receipt_form',compact('cashReceiptNo','chequeReceiptNo','bankMaster','dimList','acc_parameter','acc_code_dr','acc_code_cr','isYearChequeCorrect','isYearCashCorrect'));
    }
    /*
        Add Deposit receipt Action

    */
    public function addDepositReceiptAction(Request $request)
    {
		
        $this->validate($request, [
            'receipts_generation_receipt_no' => 'required',
            'receipts_generation_receipt_date'=> 'required',
            'tenant_contract_no' => 'required',                    
            'receipts_generation_payment_method'   => 'required',
            'receipts_generation_amt'   => 'required',
            
         ]);
         
		  // 1 - cheque, 2 - cash - receipt No sequence        	
        if($request->receipts_generation_payment_method == 1){	
            $receiptId  = $this->receiptGenerateCode('receipt_cheque');	
            $configIncKey = 'receipt_cheque';	
        }	
        if($request->receipts_generation_payment_method == 2){	
            $receiptId    = $this->receiptGenerateCode('receipt_cash');	
            $configIncKey = 'receipt_cash';	
        } 	

        if($request->receipts_generation_payment_method == 3){  
            $receiptId  = $this->receiptGenerateCode('receipt_cheque'); 
            $configIncKey = 'receipt_cheque';   
        }



        $dimList           = DimDetail::all();
		
        $user = \Auth::user();
		$permissionExist = $user->hasPermissionTo('request_approval_receipt');
		if($permissionExist){
			$receipts_generation_approval_status = 3;
			$receipts_generation_status = 1;
		}else{
			$receipts_generation_approval_status = 1;
			$receipts_generation_status = 0;
		}
		
		//Requirement Change right now not using	
		$depostIndexNo = 0;
		
        $depositReceiptId = ReceiptsGeneration::create([
                    'tenant_contract_id' => $request->tenant_contract_no,
                     'receipts_generation_receipt_no' => $receiptId,
                     'receipts_generation_receipt_date' => $request->receipts_generation_receipt_date,
                     'receipts_generation_payment_method' => $request->receipts_generation_payment_method, //1 - cheque, 2 - cash
                     'bank_id' => $request->bank_id,
                     'receipts_generation_amt' => replaceCommaWithDot($request->receipts_generation_amt),
                     'receipts_generation_cheque_no' => $request->pdc_bank_id.'__'.$request->cheque_no,                
                     'receipts_generation_description' =>$request->receipts_generation_description, 
                     'receipts_generation_remark' => $request->receipts_generation_remark, // Active
                     'receipts_generation_status' =>$receipts_generation_status ,
                     //0- InActive(default), 1 - Active , 2 - Cancel, 3 - Posted
                     'receipts_generation_approval_status' => $receipts_generation_approval_status,
                     // 1 - unapproval(default), 2 -Pending, 3 - Approved, 4 - Reject
                     'receipts_generation_type' => 2, //1 - general receipt, 0 - tenant receipt, 2- deposit receipt
                     'receipts_generation_index' => $depostIndexNo,
                     'finance_dim' => ($request->finance_dim=='HO')?AX_DIVISION_HO:AX_DIVISION_PLMS,
                     'receipts_generation_is_bounce_normal'=>isset($request->pay_type)?$request->pay_type:1,
                     'receipts_generation_is_pdc_bounce_id'=>isset($request->pdc_bounce_cheque)?$request->pdc_bounce_cheque:null,
                     'created_by' => \Auth::user()->id,
                 ]);   
               
                 // Debit Insertation
                 ReceiptGenerationDim::create([
                       'receipts_generation_id' =>$depositReceiptId->id,
                        'account_code' => $request->acc_dr_first,
                        'description'=>$request->acc_dr_desc,
                        'dim_type' =>trim($request->acc_dr_type),
                        'debit_amount'=>isset($request->dr_amt_first)?replaceCommaWithDot($request->dr_amt_first):0,
                        'credit_amount'=>isset($request->cr_amt_first)?replaceCommaWithDot($request->cr_amt_first):0,
                        'narration' =>$request->dr_narration_first,
                        'ac_codes_id' =>isset($request->acc_code_dr_id)?$request->acc_code_dr_id:0,
                        'created_by' => \Auth::user()->id,
                       ]);
                 // Credit Insertation
                 ReceiptGenerationDim::create([
                       'receipts_generation_id' =>$depositReceiptId->id,
                        'account_code' =>$request->acc_cr_second,
                        'description'=>$request->acc_cr_desc_second,
                        'dim_type' =>trim($request->acc_cr_type_second),
                        'debit_amount'=>isset($request->dr_amt_second)?replaceCommaWithDot($request->dr_amt_second):0,
                        'credit_amount'=>isset($request->cr_amt_second)?replaceCommaWithDot($request->cr_amt_second):0,
                        'narration' =>$request->cr_narration_second,
                        'ac_codes_id' =>isset($request->acc_code_cr_id)?$request->acc_code_cr_id:0,
                        'created_by' => \Auth::user()->id,
                        ]);
        
		//Update next increment value   	
        $this->incrementSequenceNo($configIncKey);  
		
        session()->flash('success', 'Saved Successfully');
        return redirect()->route('receiptsTabViewListtab','deposit');

    }
    /**
     * Deposit receipt update
     * @param int $id
     * @return Response
     */
    public function updateDepositReceipt($id)
    {
         $nowUrl = url()->previous();
        Session::put('nowUrl', $nowUrl);
        
        $depositReceiptInfo = ReceiptsGeneration::where('id', $id)->first();
        $dimList = DimDetail::all();
        
        $bankMaster = Bank::Where('bank_status', 1)->where('accounts_bank',1)->orderBy('bank_name', 'ASC')->get();

        // Distribution Dimension details
        $acc_parameter  = AccountParams::where('acc_params_tran_desc','comp_mgt_deposit_receipts')->first();

            // Debit Amount
        if(isset($acc_parameter->acc_params_dr_acc))
          $acc_code_dr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_dr_acc)->first();
        else
            $acc_code_dr = null;
            // Credit Amount
        if(isset($acc_parameter->acc_params_cr_acc))
          $acc_code_cr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_cr_acc)->first();
        else
            $acc_code_cr = null;

        $yearCheque    = prefixData('receipt_cheque')->configuration_year;	
        $isYearChequeCorrect = (date('y') == $yearCheque)?true:false;	
        $yearCash   = prefixData('receipt_cash')->configuration_year;	
        $isYearCashCorrect = (date('y') == $yearCash)?true:false;
        
        $bankMaster = Bank::Where('bank_status', 1)->where('accounts_bank',1)->orderBy('bank_name', 'ASC')->get();     
        return view('backoffice::Receipt.deposit_receipt_form',compact('isYearChequeCorrect','isYearCashCorrect','depositReceiptInfo','dimList','acc_parameter','acc_code_dr','acc_code_cr','bankMaster','nowUrl'));
    }
    /**
     * Deposit receipt update Action
     * @param int $id
     * @return Response
     */
    public function updateDepositReceiptAction(Request $request,$id)
    {
        
        $this->validate($request, [
            'receipts_generation_receipt_no' => 'required',
            'receipts_generation_receipt_date'=> 'required',
            'tenant_contract_no' => 'required', 
            'receipts_generation_amt'   => 'required',
            
         ]);
         // 1 - cheque, 2 - cash - receipt No sequence
        $rentIndexCheque   = ReceiptsGeneration::withTrashed()->where('receipts_generation_payment_method',1)->orderBy('receipts_generation_index', 'DESC')->first();
        $rentIndexCash     = ReceiptsGeneration::withTrashed()->where('receipts_generation_payment_method',2)->orderBy('receipts_generation_index', 'DESC')->first();

        $prefix_cash       = prefixData('receipt_cash')->configuration_value;
        $prefix_cheque     = prefixData('receipt_cheque')->configuration_value;
        $dimList           = DimDetail::all();
		$rentReceiptInfo = ReceiptsGeneration::where('id', $id)->first();
        if($request->receipts_generation_payment_method == $rentReceiptInfo->receipts_generation_payment_method){
            $receiptId      = $rentReceiptInfo->receipts_generation_receipt_no;
            $depostIndexNo = $rentReceiptInfo->receipts_generation_index;
        }
        else{
        if($request->receipts_generation_payment_method == 1){
            if(!empty($rentIndexCheque->receipts_generation_index)){
                $receiptId  = $prefix_cheque.substr(date('Y'),-2).str_pad($rentIndexCheque->receipts_generation_index +1 ,5,'0',STR_PAD_LEFT);
                $depostIndexNo = $rentIndexCheque->receipts_generation_index +1;
            }
            else{
               
                $receiptId  = $prefix_cheque.substr(date('Y'),-2).str_pad(1,5,'0',STR_PAD_LEFT);
                $depostIndexNo = 1;
            }
        }
        if($request->receipts_generation_payment_method == 2){
            if(!empty($rentIndexCash->receipts_generation_index)){
                $receiptId    = $prefix_cash.substr(date('Y'),-2).str_pad($rentIndexCash->receipts_generation_index +1 ,5,'0',STR_PAD_LEFT);
                $depostIndexNo = $rentIndexCash->receipts_generation_index +1;
            }
            else{
                $receiptId    = $prefix_cash.substr(date('Y'),-2).str_pad(1,5,'0',STR_PAD_LEFT);
                $depostIndexNo  = 1;
            }
        } 
		}
        $previous_url = $request->previous_url;
        $depositReceiptId = ReceiptsGeneration::where('id',$id)->update([
                    'tenant_contract_id' => $request->tenant_contract_no,
                     'receipts_generation_receipt_date' => $request->receipts_generation_receipt_date,
                     'bank_id' => $request->bank_id,
                     'receipts_generation_amt' => replaceCommaWithDot($request->receipts_generation_amt),
                     'receipts_generation_cheque_no' => $request->pdc_bank_id.'__'.$request->cheque_no,                
                     'receipts_generation_description' =>$request->receipts_generation_description, 
                     'receipts_generation_remark' => $request->receipts_generation_remark, // Active
                     //'receipts_generation_status' => 0 ,
                     //0- InActive(default), 1 - Active , 2 - Cancel, 3 - Posted
                     //'receipts_generation_approval_status' => 1,
                     // 1 - unapproval(default), 2 -Pending, 3 - Approved, 4 - Reject
                     'receipts_generation_type' => 2, //1 - general receipt, 0 - tenant receipt, 2- deposit receipt
                     'finance_dim' => ($request->finance_dim=='HO')?AX_DIVISION_HO:AX_DIVISION_PLMS,
                     'receipts_generation_is_bounce_normal'=>isset($request->pay_type)?$request->pay_type:1,
                     'receipts_generation_is_pdc_bounce_id'=>isset($request->pdc_bounce_cheque)?$request->pdc_bounce_cheque:null,
                     'created_by' => \Auth::user()->id,
                 ]);   


        session()->flash('success', 'Update Successfully');
        return redirect()->route('receiptsTabViewListtab','deposit');
    }    
    /*
        Add General receipt Action

    */
   public function addGeneralReceipt()
    {
       
        $generalIndex           = ReceiptsGeneration::withTrashed()->where('receipts_generation_type',1)->orderBy('id', 'DESC')->first();
        
        $cashReceiptNo    = $this->receiptGenerateCode('receipt_cash');	
        $chequeReceiptNo  = $this->receiptGenerateCode('receipt_cheque');	
        $yearCheque    = prefixData('receipt_cheque')->configuration_year;	
        $isYearChequeCorrect = (date('y') == $yearCheque)?true:false;	
        $yearCash   = prefixData('receipt_cash')->configuration_year;	
        $isYearCashCorrect = (date('y') == $yearCash)?true:false;

        $bankMaster = Bank::Where('bank_status', 1)->orderBy('bank_name', 'ASC')->get();         
        $dimList = DimDetail::all();  
        return view('backoffice::Receipt.general_receipt_form',compact('cashReceiptNo','chequeReceiptNo','bankMaster','dimList','isYearChequeCorrect','isYearCashCorrect'));
    }
    /*
        Add General receipt Action

    */
    public function addGeneralReceiptAction(Request $request)
    {
        $this->validate($request, [
            'receipts_generation_receipt_no' => 'required',
            'receipts_generation_receipt_date'=> 'required',
            'tenant_contract_no' => 'required',                    
            'receipts_generation_payment_method'   => 'required',
            'receipts_generation_amt'   => 'required',
            
         ]);
        
		// 1 - cheque, 2 - cash - receipt No sequence        	
        if($request->receipts_generation_payment_method == 1){	
            $receiptId  = $this->receiptGenerateCode('receipt_cheque');	
            $configIncKey = 'receipt_cheque';	
        }	
        if($request->receipts_generation_payment_method == 2){	
            $receiptId    = $this->receiptGenerateCode('receipt_cash');	
            $configIncKey = 'receipt_cash';	
        } 	
        if($request->receipts_generation_payment_method == 3){  
            $receiptId    = $this->receiptGenerateCode('receipt_cheque'); 
            $configIncKey = 'receipt_cheque'; 
        }   

		// Change requirement not using any where	
        $generalIndexNo = 0;
	
        $user = \Auth::user();
        $permissionExist = $user->hasPermissionTo('request_approval_receipt');
        if($permissionExist){
            $receipts_generation_approval_status = 3;
            $receipts_generation_status = 1;
        }else{
            $receipts_generation_approval_status = 1;
            $receipts_generation_status = 0;
        }
        
        $generalReceiptId = ReceiptsGeneration::create([
                    'tenant_contract_id' => $request->tenant_contract_no,
                     'receipts_generation_receipt_no' =>$receiptId,
                     'receipts_generation_receipt_date' => $request->receipts_generation_receipt_date,
                     'receipts_generation_payment_method' => $request->receipts_generation_payment_method, //1 - cheque, 2 - cash
                     'bank_id' => $request->bank_id,
                     'receipts_generation_amt' => replaceCommaWithDot($request->receipts_generation_amt),
                     'receipts_generation_cheque_no' => $request->pdc_bank_id.'__'.$request->cheque_no,                
                     'receipts_generation_description' =>$request->receipts_generation_description, 
                     'receipts_generation_remark' => $request->receipts_generation_remark, // Active
                     'receipts_generation_status' => $receipts_generation_status ,
                     'receipts_generation_index' => $generalIndexNo,
                     //0- InActive(default), 1 - Active , 2 - Cancel, 3 - Posted
                     'receipts_generation_approval_status' => $receipts_generation_approval_status,
                     // 1 - unapproval(default), 2 -Pending, 3 - Approved, 4 - Reject
                     'receipts_generation_type' => 1, //1 - general receipt, 0 - tenant receipt, 2- deposit receipt
                     'finance_dim' => $request->finance_dim,
                     'general_receipt_narration'=>$request->general_narration ,
                     'created_by' => \Auth::user()->id,
                     
                 ]);   
        
        if(count($request->account_code) > 0){ 
            foreach ($request->account_code as $key => $value) {
              $value = explode('-',trim($value));
              $value = $value[0]; 
              $generalReceiptId->getReceiptsDimensionAccount()->create([
                'receipts_generation_id' =>$generalReceiptId->id,
                'account_code' => $value, 
                'description'=>isset($request->acc_dr_desc[$key])? $request->acc_dr_desc[$key] : null,
                'dim_type' =>trim($request->acc_code_type[$key]),
                'debit_amount'=>isset($request->dr_amt[$key])? replaceCommaWithDot($request->dr_amt[$key]) : 0,
                'credit_amount'=>isset($request->cr_amt[$key])?replaceCommaWithDot($request->cr_amt[$key]):0,
                'narration' =>isset($request->narration[$key])?$request->narration[$key]:0,
                'ac_codes_id' =>isset($request->acc_code_id[$key])?$request->acc_code_id[$key]:0,
                'created_by' => \Auth::user()->id,
                ]);
            }

          }
        
		//Update next increment value   	
        $this->incrementSequenceNo($configIncKey);

        session()->flash('success', 'Saved Successfully');
        return redirect()->route('receiptsTabViewListtab','general');
    }
    /**
     * General receipt update
     * @param int $id
     * @return Response
     */
    public function updateGeneralReceipt($id)
    {
        
        $generalReceiptInfo = ReceiptsGeneration::where('id', $id)->first();
        $dimList = DimDetail::all();
        $acc_code_dr 	= null;
		$acc_code_cr	= null;
        $bankMaster = Bank::Where('bank_status', 1)->where('accounts_bank',1)->orderBy('bank_name', 'ASC')->get();

        // Distribution Dimension details
        $acc_parameter  = AccountParams::where('acc_params_tran_desc','comp_mgt_deposit_receipts')->first();

            // Debit Amount
        if(isset($acc_parameter->acc_params_dr_acc))
          $acc_code_dr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_dr_acc)->first();
        
            // Credit Amount
        if(isset($acc_parameter->acc_params_cr_acc))
          $acc_code_cr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_cr_acc)->first();
        
        $bankMaster = Bank::Where('bank_status', 1)->where('accounts_bank',1)->orderBy('bank_name', 'ASC')->get();  
		
        $yearCheque    = prefixData('receipt_cheque')->configuration_year;	
        $isYearChequeCorrect = (date('y') == $yearCheque)?true:false;	
        $yearCash   = prefixData('receipt_cash')->configuration_year;	
        $isYearCashCorrect = (date('y') == $yearCash)?true:false;	
		
        return view('backoffice::Receipt.general_receipt_form',compact('generalReceiptInfo','dimList','acc_parameter','acc_code_dr','acc_code_cr','bankMaster','isYearChequeCorrect','isYearCashCorrect'));
    }

     public function getReceiptDetailsjson(Request $request){
        $id = $request->id;
        $generalReceiptInfo = ReceiptsGeneration::where('id', $id)->first();

         $amt = numberFormat($generalReceiptInfo->receipts_generation_amt);

        echo $amt;


     }

    /**
     * General receipt update Action
     * @param int $id
     * @return Response
     */
    public function updateGeneralReceiptAction(Request $request,$id)
    {
   
        $this->validate($request, [
            'receipts_generation_receipt_no' => 'required',
            'receipts_generation_receipt_date'=> 'required',
            'tenant_contract_no' => 'required', 
            'receipts_generation_amt'   => 'required',
            
         ]);
        

        $generalReceiptInfo = ReceiptsGeneration::where('id', $id)->first();

        $dimList           = DimDetail::all();
		
        $generalReceiptId = ReceiptsGeneration::where('id',$id)->update([
                    'tenant_contract_id' => $request->tenant_contract_no,
                     'receipts_generation_receipt_no' => $request->receipts_generation_receipt_no,
                     'receipts_generation_receipt_date' => $request->receipts_generation_receipt_date,
                     'bank_id' => $request->bank_id,
                     'receipts_generation_amt' => replaceCommaWithDot($request->receipts_generation_amt),
                     'receipts_generation_cheque_no' => $request->cheque_no,                
                     'receipts_generation_description' =>$request->receipts_generation_description, 
                     'receipts_generation_remark' => $request->receipts_generation_remark, // Active
                     //'receipts_generation_status' => 0 ,
                     //0- InActive(default), 1 - Active , 2 - Cancel, 3 - Posted
                     //'receipts_generation_approval_status' => 1,
                     // 1 - unapproval(default), 2 -Pending, 3 - Approved, 4 - Reject
                     'receipts_generation_type' => 1, //1 - general receipt, 0 - tenant receipt, 2- deposit receipt
                     'finance_dim' => $request->finance_dim,
                     'general_receipt_narration'=> $request->general_narration,
                     'created_by' => \Auth::user()->id,
                 ]);   
           
            
            ReceiptGenerationDim::where('receipts_generation_id',$id)->delete();
            
            if(count($request->account_code) > 0){ 
            foreach ($request->account_code as $key => $value) {
              $value = explode('-',trim($value));
              $value = $value[0];   
              ReceiptGenerationDim::create([
                'receipts_generation_id' =>$id,
                'account_code' => $value, 
                'description'=>isset($request->account_code_desc[$key])? $request->account_code_desc[$key] : null,
                'dim_type' =>trim($request->acc_code_type[$key]),
                'debit_amount'=>isset($request->dr_amt[$key])? replaceCommaWithDot($request->dr_amt[$key]) : 0,
                'credit_amount'=>isset($request->cr_amt[$key])?replaceCommaWithDot($request->cr_amt[$key]):0,
                'narration' =>isset($request->narration[$key])?$request->narration[$key]:0,
                'ac_codes_id' =>isset($request->acc_code_id[$key])?$request->acc_code_id[$key]:0,
                'created_by' => \Auth::user()->id,
                ]);
            }

          }

              
        session()->flash('success', 'Update Successfully');
        return redirect()->route('receiptsTabViewListtab','general');
    }    

    /*  
        List Reuest for Approval - tab

    */

     public function receiptsRequestForApprovalTabViewList(Request $request,$tab = 'rent')
    {

        $search_fields = [
        'receipts_generation_receipt_no' => 'Receipt No',
        'receipts_generation_eff_from' => 'Effective From',
        'receipts_generation_eff_to' => 'Effective To',
        'building_name' => 'Building Name',
        'building_code' => 'Building Code',
        'tenant_name' => 'Tenant Name',
        'tenant_code' => 'Tenant Code',
        'tenant_contract_no' => 'Contract No',   
        'receipts_generation_approval_status' => 'Status',    
        'receipts_generation_payment_method' => 'Payment Method',  
        'receipts_generation_amt' => 'Amount',  
        ];


        $operations = [
            '=' => ' Is Equal To '  ,
            '!=' => ' Is Not Equal To '  ,
            '>' => ' Is Greater Than '  ,
            '>=' => ' Is Greater Than Or Equal To '  ,
            '<' => ' Is Less Than '  ,
            '<=' => ' Is Less Than Or Equal To'  ,
            'ilike' => ' Like '  ,
            'ilike%...%' => ' Like%...% ',
        ];  
         
$request->flash();        
        
$route   =  $request->url();

$receiptslist = ViewReceipt::filter($request)
                                    ->type($tab)
                                    ->where(function($query) { 
                                      $query->orWhere('receipts_generation_approval_status', 2)
                                            ->orWhere('receipts_generation_approval_status', 5);
                                    })->sortable()
                                     ->orderBy('id', 'desc')
                                     ->paginate($this->noOfRecord);



    if(isset($request->ajax)) 
    return view('backoffice::Receipt.receipt_request_for_approval_list_ajax',compact('receiptslist','tab','fields','request','route','search_fields','operations'));                                  

    return view('backoffice::Receipt.receipt_request_for_approval_list',compact('receiptslist','tab','search_fields','operations','route'));

        /*
        $receiptslist = array();
        $tab = $request->tab;
        switch ($tab ) {
            
            case 'rent':

				
                $receipts = ReceiptsGeneration::closure($result)->where('receipts_generation_type', 0)->where(function($q) { 
                        $q->orWhere('receipts_generation_approval_status', 2)->orWhere('receipts_generation_approval_status', 5);
                    })->sortable();
                $receiptslist = $receipts->orderBy('id', 'desc')->paginate($noOfRecord);

                if(isset($request->ajax)){          
                
                return view('backoffice::Receipt.receipt_request_for_approval_list_ajax',compact('receiptslist','tab','fields','request','route','search_fields','operations'));  
                
                }
                break;
                
            case 'deposit':

                $receipts = ReceiptsGeneration::closure($result)->where('receipts_generation_type', 2)->where(function($q) { 
                        $q->orWhere('receipts_generation_approval_status', 2)->orWhere('receipts_generation_approval_status', 5);
                    })->sortable();
                $receiptslist = $receipts->orderBy('id', 'desc')->paginate($noOfRecord);

                if(isset($request->ajax)){          
                
                return view('backoffice::Receipt.receipt_request_for_approval_list_ajax',compact('receiptslist','tab','fields','request','route','search_fields','operations'));  
                
                }
                break;
                
            case 'general':

                $receipts = ReceiptsGeneration::closure($result)->where('receipts_generation_type', 1)->where(function($q) { 
                        $q->orWhere('receipts_generation_approval_status', 2)->orWhere('receipts_generation_approval_status', 5);
                    })->sortable();
                $receiptslist = $receipts->orderBy('id', 'desc')->paginate($noOfRecord);

                if(isset($request->ajax)){          
                
                return view('backoffice::Receipt.receipt_request_for_approval_list_ajax',compact('receiptslist','tab','fields','request','route','search_fields','operations'));  
                
                }
                break;
               
            
            default:

               $tab = 'rent'; 

              $receipts = ReceiptsGeneration::closure($result)->where('receipts_generation_type', 0)->where(function($q) { 
                        $q->orWhere('receipts_generation_approval_status', 2)->orWhere('receipts_generation_approval_status', 5);
                    })->sortable();
                $receiptslist = $receipts->orderBy('id', 'desc')->paginate($noOfRecord);

                if(isset($request->ajax)){          
                
                return view('backoffice::Receipt.receipt_request_for_approval_list_ajax',compact('receiptslist','tab','fields','request','route','search_fields','operations'));  
                
                }
                break;
               
        }
        
        return view('backoffice::Receipt.receipt_request for_approval_list',compact('receiptslist','tab','search_fields','operations'));
        */
    }
    /*  
        Change Receipt Approval status

    */
     public function receiptApprovalStatus(Request $request)
    {
        
       
        $receiptId = $request->receipt_id;
        
        $processStatus = (isset($request->process_id)?$request->process_id:(isset($request->reject_btn)?$request->reject_btn:(isset($request->approve_btn)?$request->approve_btn:'')));

        if($processStatus){

            switch($processStatus){

                case 1: // Receipt status as Unapproved
                    $receiptInfo = ReceiptsGeneration::where('id',$receiptId)->first();

                    ReceiptsGeneration::where('id',$receiptId)->update([
                            'receipts_generation_approval_status'=> $processStatus,'receipts_generation_status'=> 0
                        ]);

                                    //Notification 
                    clearNotification('Modules\BackOffice\Notifications\ReceiptApprovalNotification',$receiptId);
                    $users = User::role(['accountant'])->get(); 
                    $users = array_flatten($users);

                    $receiptInfo->href = url('rentReceiptGeneration'.'/'.$receiptId);   

                    event(new ReceiptApprove($receiptInfo,$users));

                    session()->flash('success', 'Request Send Successfully');
                   return redirect()->back()->with('success', 'Request Send Successfully');
                   break;

                case 2: // Receipt status as Pending
                    $receiptInfo = ReceiptsGeneration::where('id',$receiptId)->first();

                   ReceiptsGeneration::where('id',$receiptId)->update([
                            'receipts_generation_approval_status'=> $processStatus,
                        ]); 
                    //Notification 
                    //clearNotification('Modules\BackOffice\Notifications\ReceiptApprovalNotification',$receiptId);
                    $users = User::role(['finance_manager'])->get(); 
                    $users = array_flatten($users);


                    $receiptInfo->href = url('rentReceiptGeneration'.'/'.$receiptId);   
                    $receiptInfo->text    = "Receipt - ".$receiptInfo->receipts_generation_receipt_no." received for Approval";  
                    event(new ReceiptApprove($receiptInfo,$users));
                    
                    session()->flash('success', 'Request Send Successfully');


                    if($receiptInfo->receipts_generation_type == 1)
                         return redirect()->route('receiptsTabViewListtab','general');
                    elseif($receiptInfo->receipts_generation_type == 2)
                        return redirect()->route('receiptsTabViewListtab','deposit');
                    else
                        return redirect()->route('receiptsTabViewList');
                    break;

                case 3: // Receipt status as Approve

                    $receiptInfo = ReceiptsGeneration::where('id',$receiptId)->first();

                    if($receiptInfo->receipts_generation_approval_status == 5){
                        ReceiptsGeneration::where('id',$receiptId)->update([
                            'receipts_generation_approval_status'=> 1,
                            'receipts_generation_status'=>0
                        ]);
                    }
                    else{
                    ReceiptsGeneration::where('id',$receiptId)->update([
                            'receipts_generation_approval_status'=> $processStatus,'receipts_generation_status'=>1
                        ]);
                    }
                                       
                    return redirect()->back()->with('success', 'Approved Receipt');
                    break;

                case 4: // Receipt status as Reject
                    $receiptInfo = ReceiptsGeneration::where('id',$receiptId)->first();
                    
                    if($receiptInfo->receipts_generation_approval_status == 5){
                        ReceiptsGeneration::where('id',$receiptId)->update([
                            'receipts_generation_approval_status'=> 3,
                            'receipts_generation_status'=> 1
                        ]);

                    }
                    else{
                    ReceiptsGeneration::where('id',$receiptId)->update([
                            'receipts_generation_approval_status'=> $processStatus,
                            'receipts_generation_status'=>0
                        ]);
                    }
                    
                    $users = User::role(['accountant'])->get(); 
                    $users = array_flatten($users);

                    $receiptInfo->href = url('rentReceiptGeneration'.'/'.$receiptId);   
                    $receiptInfo->text    = "Receipt - ".$receiptInfo->receipts_generation_receipt_no." is Rejected";
                         
                    event(new ReceiptReject($receiptInfo,$users));
                    
                    return redirect()->back()->with('success', 'Rejected Receipt');
                    break;
                case 5: // Pending for Unapprove

                    $receiptInfo = ReceiptsGeneration::where('id',$receiptId)->first();
                    ReceiptsGeneration::where('id',$receiptId)->update([
                            'receipts_generation_approval_status'=> $processStatus,
                        ]);

                                  //Notification 
                    clearNotification('Modules\BackOffice\Notifications\ReceiptApprovalNotification',$receiptId);
                    $users = User::role(['finance_manager'])->get(); 
                    $users = array_flatten($users);

                    $receiptInfo->href = url('rentReceiptGeneration'.'/'.$receiptId);   
                    $receiptInfo->text    = "Receipt - ".$receiptInfo->receipts_generation_receipt_no." received for Draft Request";  
                    event(new ReceiptApprove($receiptInfo,$users));

                    session()->flash('success', 'Request For Draft Receipt');
                    
                    return redirect()->route('receiptsTabViewList');
                    break;

                 default:
                     break;


            }

       }

       //return redirect()->route('receiptsTabViewList');
    }
   

     /*  
        Change Receipt AS Posted

    */
     public function receiptAsPosted(Request $request)
    {
        try {

                $receiptId = $request->receipt_id;
                $receiptInfo = ReceiptsGeneration::where('id',$receiptId)->first();
                $management_type = $receiptInfo->tenantContractInfo->building->management_id;
                $bank_cheque_no  = isset($receiptInfo->bankInfo->receipts_generation_cheque_no)?$receiptInfo->bankInfo->receipts_generation_cheque_no:'';   

                // if($bank_cheque_no == ''){
                //     $bank_cheque_no  = isset($receiptInfo->receipts_generation_cheque_no)?$receiptInfo->receipts_generation_cheque_no:'';
                // }

                   

                $bank_checkbkId  = isset($receiptInfo->bankInfo->bank_chequebook_id)?$receiptInfo->bankInfo->bank_chequebook_id:'';     
                $bank_code = isset($receiptInfo->bankInfo->bank_code)?$receiptInfo->bankInfo->bank_code:'';     
                $bankDim1Value = isset($receiptInfo->bankInfo->dim1Value)?$receiptInfo->bankInfo->dim1Value:'';
                $receipt_cheque_nums='';
                if($receiptInfo->receipts_generation_payment_method==1){
                     if($receiptInfo->receipts_generation_cheque_no =="" || $receiptInfo->receipts_generation_cheque_no =="__"  ){$receipt_cheque_nums='';}else{$receipt_cheque_nums=$receiptInfo->receipts_generation_cheque_no;}  
                }

                

                $dim1Value  = isset($receiptInfo->bankInfo->dim1Value)?$receiptInfo->bankInfo->dim1Value:'02';   

                //***************** changes for taking bank transfer ref No by anoop **********************

                $p_method = ($receiptInfo->receipts_generation_payment_method==2)?'Cash':'Cheque';

                
                $ref_no = isset($receiptInfo->receipts_generation_cheque_no)?$receiptInfo->receipts_generation_cheque_no:'';
                if($receiptInfo->receipts_generation_payment_method == 3){
                    $p_method = 'Cheque';
                   
                }   
                //************************************END CHANGES******************************************

                // 1 -Comprehensive, 2 - Normal, 3 - Commission 
                if($receiptInfo->receipts_generation_type > 0){ 

                    // 1 - general receipt, 2- deposit receipt
                    
                    $ledgerHeader = array(
                        'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
                        'DataAreaId'=>DATA_AREA_ID,
                        'company' =>COMPANY,
                    );
                    
                    $response   =   Dynamics::LedgerAxHeaderPushData('AXTenantReceiptHeader',$ledgerHeader);
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
                                        'paymentMethod'=>$p_method,//chenages here by anoop
                                        'checkBookid'=>isset($bank_cheque_no)?$bank_cheque_no:'',
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
                                return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
                            }
                    
                        }
                        // General Receipt need bank Line item recent change by client
                        if($receiptInfo->receipts_generation_type ==1){
                                    $receiptLineItemBank[] = array(
                                            'JournalNum'=> $response,
                                            'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
                                            'PaymentDate'=> $receiptInfo->receipts_generation_receipt_date->format('Y-m-d'),
                                            'Description'=> $receiptInfo->receipts_generation_remark,
                                            'Account'=>trim($bank_checkbkId),
                                            'currency'=> CURRENCY,
                                            'accountType'=> AX_BANK,
                                            'paymentMethod'=>$p_method,//changes by anoop
                                            'checkBookid'=>isset($bank_cheque_no)?$bank_cheque_no:'',
                                            'documentNo'=> $receipt_cheque_nums,
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
                                    
                        $response   =   Dynamics::ReceiptPaymentJournalAxHeaderPushData('AXTenantReceiptHeader');
                        if($response=='Error'){
                            return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
                        }
                        
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
                                            'documentNo'=> $receiptInfo->receipts_generation_cheque_no,
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

                       // print_r(json_encode($receiptItemsCr));exit();

                        if(count($receiptItemsCr) >0 && $result =='Error'){
                                return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
                        }
                        
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
                                            'documentNo'=> $receiptInfo->receipts_generation_cheque_no,
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
                                return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
                        }
                    }
                    // Rent Receipt as Normal Or Commission
                    else{
                        
                        $ledgerHeader = array(
                        'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
                        'DataAreaId'=>DATA_AREA_ID,
                        'company' =>COMPANY,
                        );
                    
                        $response   =   Dynamics::LedgerAxHeaderPushData('AXTenantReceiptHeader',$ledgerHeader);
                        if($response=='Error'){
                            return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
                        }
                    
                        // Distribution Dimension details
                        $acc_parameter  = AccountParams::where('acc_params_tran_desc','Nor_Mgt_receipts')->first();
                    
                        $receiptLineItem[] = array(
                                'JournalNum'=> $response,
                                'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
                                'PaymentDate'=> $receiptInfo->receipts_generation_receipt_date->format('Y-m-d'),
                                'Description'=>$receiptInfo->receipts_generation_remark,
                                'Account'=> $bank_checkbkId,
                                'currency'=> CURRENCY,
                                'accountType'=> AX_BANK,
                                'paymentMethod'=>$p_method,//change by anoop
                                'checkBookid'=>isset($bank_cheque_no)?$bank_cheque_no:'',
                                'documentNo'=> $receipt_cheque_nums,
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
                                'paymentMethod'=>$p_method,//change by anoop
                                'checkBookid'=>isset($bank_cheque_no)?$bank_cheque_no:'',
                                'documentNo'=> $receipt_cheque_nums,
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
                            return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
                        }
                        
                    }
                }   
                ReceiptsGeneration::where('id',$receiptId)->update([
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
                // 3 - Replace: cash received against PDC, mark as paid
                if($receiptInfo->receipts_generation_is_bounce_normal == 3){
                    $pdcRecord = Pdc::where('id', $receiptInfo->receipts_generation_is_pdc_bounce_id)->first();
                    $chequeNo = $pdcRecord ? $pdcRecord->pdc_check_no : '';
                    Pdc::where('id', $receiptInfo->receipts_generation_is_pdc_bounce_id)->update([
                        'pdc_receipt_no'    => $receiptInfo->receipts_generation_receipt_no,
                        'pdc_settlement'    => 2,
                        'pdc_cancel_reason' => 3,
                        'pdc_remark'        => 'Received cash payment for cheque no ' . $chequeNo,
                    ]);
                }
                session()->flash('success', 'Receipt Posted Successfully');
                return redirect()->route('receiptsTabViewList');

        } catch (\Exception $e) {

            return $e->getMessage();
        }
        
    } 
    
    /*
  *  Search Form Fields
  * 
  *
  */
  public function receiptSearchFilter(){


      $search_fields = [
        'receipts_generation_receipt_no' => 'Receipt No',
        'receipts_generation_receipt_date' => 'Receipt Date',
        'building_name' => 'Building Name',
        'building_code' => 'Building Code',
        'tenant_name' => 'Tenant Name',
        'tenant_code' => 'Tenant Code',
        'contract_no' => 'Contract No',   
        'receipts_generation_approval_status' => 'Status',    
        'receipts_generation_payment_method' => 'Payment Method',  
        'receipts_generation_amt' => 'Amount',  
        ];


        $operations = [
            '=' => ' Is Equal To '  ,
            '!=' => ' Is Not Equal To '  ,
            '>' => ' Is Greater Than '  ,
            '>=' => ' Is Greater Than Or Equal To '  ,
            '<' => ' Is Less Than '  ,
            '<=' => ' Is Less Than Or Equal To'  ,
            'ilike' => ' Like '  ,
            'ilike%...%' => ' Like%...% ',
        ];  

        return view('backoffice::Receipt.receipt_filter',compact('search_fields','operations'));

    }
    /*
    *
    *
    * Qr Code Scanner
    *
    */
    public function qrCodeScanner(Request $request)
    {
      $unit_id = $request->input('unit_id');
      $type = $request->input('type');
      $contract = TenantContract::where('unit_id',$unit_id)->active()->first();
      
      
      $unit = Unit::where('id',$unit_id)->first();
      $building = Building::where('id',$unit->building_id)->active()->first();
      if($type == 'key'){
        return view('backoffice::Key.key_accept_ajax',compact('unit','contract'));
      }else{
        if(isset($contract->tenant))
            $tenant = $contract->tenant;
        else
            $tenant = 0;
        return json_encode(array($unit,$contract,$building,$tenant));
      }
      
    } 
    /*
     * 
     *  Receipt add page controller to previous receipt 
     * 
     */ 
    public function receiptsAgreementViewList(Request $request,$id, $tab){

        $search_fields = [
        'receipts_generation_receipt_no' => 'Receipt No',
        'receipts_generation_receipt_date' => 'Receipt Date',
        'building_name' => 'Building Name',
        'building_code' => 'Building Code',
        'tenant_name' => 'Tenant Name',
        'tenant_code' => 'Tenant Code',
        'contract_no' => 'Contract No',   
        'receipts_generation_approval_status' => 'Status',    
        'receipts_generation_payment_method' => 'Payment Method',  
        'receipts_generation_amt' => 'Amount',  
        ];

        $route = null;
        $operations = [
            '=' => ' Is Equal To '  ,
            '!=' => ' Is Not Equal To '  ,
            '>' => ' Is Greater Than '  ,
            '>=' => ' Is Greater Than Or Equal To '  ,
            '<' => ' Is Less Than '  ,
            '<=' => ' Is Less Than Or Equal To'  ,
            'ilike' => ' Like '  ,
            'ilike%...%' => ' Like%...% ',
        ];  

        $roles = \Auth::user()->getRoles();
        $rolesNames = \Auth::user()->getRoleNames()->toArray();

        $result = array();
        if(isset($request)){
                     
            $result = $this->receiptSearch($request);   
            $request->flash();
        }
        if(isset($request->route))
            $route   =  $request->route;

        $receiptslist = array();
        $tab = $tab?$tab:'rent';
        
        
        switch ($tab ) {
            
            case 'rent':
                
                $receipts = ReceiptsGeneration::closure($result)->where('tenant_contract_id',$id)->where('receipts_generation_type', '=',0)->orderBy('id','desc')
                            ->sortable();
                $receiptslist = $receipts->paginate(10);

                if(isset($request->ajax)){          
                
                return view('backoffice::Receipt.receipt_agreement_list_ajax',compact('receiptslist','tab','id','fields','request','route','search_fields','operations'));  
                
                }
                break;
            case 'deposit':

                $receipts = ReceiptsGeneration::closure($result)->where('tenant_contract_id',$id)->where('receipts_generation_type', '=',2)->orderBy('id','desc')
                            ->sortable();
                $receiptslist = $receipts->paginate(10);

                if(isset($request->ajax)){          
                
                return view('backoffice::Receipt.receipt_agreement_list_ajax',compact('receiptslist','tab','fields','request','route','id','search_fields','operations'));  
                
                }
                break;
                
            case 'general':

                $receipts = ReceiptsGeneration::closure($result)->where('tenant_contract_id',$id)->where('receipts_generation_type','=', 1)->orderBy('id','desc')
                            ->sortable();
                $receiptslist = $receipts->paginate(10);

                if(isset($request->ajax)){          
                
                return view('backoffice::Receipt.receipt_agreement_list_ajax',compact('receiptslist','tab','fields','request','route','id','search_fields','operations'));  
                
                }
                break;
                
            
            default:
               $tab = 'rent'; 
               $receipts = ReceiptsGeneration::closure($result)->where('tenant_contract_id',$id)->where('receipts_generation_type', '=',0)->orderBy('id','desc')
                            ->sortable();
                $receiptslist = $receipts->paginate(10);

                if(isset($request->ajax)){          
                
                return view('backoffice::Receipt.receipt_agreement_list_ajax',compact('receiptslist','tab','fields','request','route','id','search_fields','operations'));  
                
                }
                break;
        }
        $contractInfo = TenantContract::where('id', $id)->first();
        return view('backoffice::Receipt.receipt_agreement_list',compact('receiptslist','contractInfo','id','tab','search_fields','operations','route'));
        
    }
    
    /*

        Quick and Advance Search 

    */

    public function receiptSearch(Request $request){

        $closure            = array();
        $closure_or         = array();
        $closure_month      = array();
        $closure_year       = array();
        $closure_or_year    = array();
        $closure_date       = array();
        $assigned_person    =array();
        $building_name_select = array();
        $duration           = array();
        $sales_enquiry_no   = array();
        $customer_name      = array();
        
        $building_name_select = $unit_select = $start_dt = $rent = $location_quick = $unitTypes = array();

        if(isset($request->fieldName)){
            if(count($request->fieldName) > 0){

                foreach ($request->fieldName as $key => $value) {

                    if( !empty($request->fieldValue[$key]) && !empty($request->fieldValue[$key]) && !empty($value) ) {

                        $operation  = $request->operation[$key];
                        $fieldValue = $request->fieldValue[$key];

                        if($request->operation[$key] == 'ilike%...%' ){
                            $fieldValue = '%'.$request->fieldValue[$key].'%';
                            $operation = 'ilike';
                        }elseif($value == 'receipts_generation_eff_from'){
                               
                            if(array_search($request->operation[$key],['=','>','<','>=','<=','between', '!=']) !== FALSE ){                    

                                $fieldDate = $request->fieldValue[$key];                   

                                $fieldDate = str_replace('/', '-', $fieldDate);
                                $created_at = Carbon::parse($fieldDate)->toDateString();                      

                                $month = Carbon::parse($fieldDate)->month;
                                $year = Carbon::parse($fieldDate)->year;

                                if($value == 'receipts_generation_eff_from')
                                    $closure_date[] = array( 'receipts_generation_eff_from' ,$request->operation[$key],$created_at);
                                elseif($key != 0 && $request->logic[$key -1 ] == 'or' )
                                    $closure_or_year[] = array($value , $request->operation[$key] ,$year,$month);
                                else
                                    $closure_year[] = array($value , $request->operation[$key] ,$year,$month);
                            }

                        }elseif($value == 'receipts_generation_eff_to'){
                               
                            if(array_search($request->operation[$key],['=','>','<','>=','<=','between', '!=']) !== FALSE ){                    

                                $fieldDate = $request->fieldValue[$key];                   

                                $fieldDate = str_replace('/', '-', $fieldDate);
                                $created_at = Carbon::parse($fieldDate)->toDateString();                      

                                $month = Carbon::parse($fieldDate)->month;
                                $year = Carbon::parse($fieldDate)->year;

                                if($value == 'receipts_generation_eff_from')
                                    $closure_date[] = array( 'receipts_generation_eff_from' ,$request->operation[$key],$created_at);
                                elseif($key != 0 && $request->logic[$key -1 ] == 'or' )
                                    $closure_or_year[] = array($value , $request->operation[$key] ,$year,$month);
                                else
                                    $closure_year[] = array($value , $request->operation[$key] ,$year,$month);
                            }

                        }
                        else{                
                            $fieldValue = $request->fieldValue[$key];
                            $operation = $request->operation[$key];
                        }

                        if($value != 'location' && $value != 'enquiry_owner' &&  $value != 'created_at' && $value != 'sales_move_in_date' && (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
                            if($key != 0 && $request->logic[$key -1 ] == 'or' )
                                $closure_or[] = array( $value , $operation ,$fieldValue);
                            else
                                $closure[] = array( $value , $operation ,$fieldValue);
                        }

                    }


                }

                if($request->ajax != true){
                    if(count($closure) == 0 &&  count($closure_year) == 0  && count($closure_date) == 0 )
                    $closure[] = array( 'id' , '=' ,0);
                }

            }

        }


        $receipts_generation_receipt_no  = (isset($request->receipts_generation_receipt_no)) ? $request->receipts_generation_receipt_no : null;
        $receipts_generation_eff_from  = (isset($request->receipts_generation_eff_from)) ? $request->receipts_generation_eff_from : null;
        $receipts_generation_eff_to  = (isset($request->receipts_generation_eff_to)) ? $request->receipts_generation_eff_to : null;
        $building_name          = (isset($request->building_name)) ? $request->building_name : null;
        $building_code          = (isset($request->building_code)) ? $request->building_code : null;
        $tenant_name            = (isset($request->tenant_name)) ? $request->tenant_name : null;
        $tenant_code            = (isset($request->tenant_code)) ? $request->tenant_code : null;
        $receipts_generation_payment_method        = (isset($request->receipts_generation_payment_method)) ? $request->receipts_generation_payment_method : null;    
        $receipts_generation_amt = (isset($request->receipts_generation_amt)) ? $request->receipts_generation_amt : null; 
        $contract_no           = (isset($request->contract_no)) ? $request->contract_no : null; 
        $receipts_generation_approval_status  = (isset($request->receipts_generation_approval_status)) ? $request->receipts_generation_approval_status : null;
        

        $quick_search          = array($receipts_generation_receipt_no, $receipts_generation_eff_from,$receipts_generation_eff_to , $building_name, $building_code, $tenant_name, $tenant_code, $receipts_generation_payment_method, $receipts_generation_payment_method,$receipts_generation_amt,$contract_no,$receipts_generation_approval_status);

        //if(count($closure)>0)
         //  dd($closure);
        $result = array(
                    $closure,
                    $closure_or,      
                    $closure_year,
                    $closure_or_year,
                    $closure_date, $quick_search); 
        //dd($result);
        return $result;   
        }
        
    /*
        Rent receipt effective from and to date

    */

    public function receiptEffectiveDateCalculation($contractId, $contract_start, $contract_end, $tenant_contract_payment_type){

        $lastReceipt = ReceiptsGeneration::where('tenant_contract_id', $contractId)->where('receipts_generation_type',0)->where('receipts_generation_status','!=',2)->orderBy('id', 'DESC')->first();
        if($tenant_contract_payment_type == 4 )
            $tenant_contract_payment_type = 6;
        elseif($tenant_contract_payment_type == 5)
            $tenant_contract_payment_type = 12;

        //receipts_generation_eff_from
        if(isset($lastReceipt->receipts_generation_eff_to)){
            if($contract_end > $lastReceipt->receipts_generation_eff_to){

                $lastReceiptToDate  =   $lastReceipt->receipts_generation_eff_to;
                $eff_from   = date('Y-m-d',strtotime("+1 day", strtotime($lastReceiptToDate)));
                $eff_to	= $eff_from;
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
			$eff_to       	=   $eff_from;
		
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
  /*
       Bounce PDC listing For settlement the Rent And Deposit Receipt
  */

  public function pdcNoListWithContractNo(Request $request){

    $contract_id        = $request->contractId;

    $pdc_list = Pdc::where('tenant_contract_id',$contract_id)->where('pdc_cancel_reason',1)->get();

    $option ='';

    if(count($pdc_list)==0){ 
            $option .= '<option value="">No PDC Bounce</option>';
    }
    else{
        $option .='<option value="">Select Bounce Cheque</option>';
        foreach($pdc_list as $list){ 
            // Remove Settled PDC From the list
            $isNotSettled = ReceiptsGeneration::where('receipts_generation_is_pdc_bounce_id','=', $list->id)->first();   
            if($isNotSettled == null) 
                $option .='<option value="'.$list->id.'">'.$list->pdc_check_no.'</option>';
        }
    }
    
    return json_encode(['option_html'=>$option]);
  }

  /*
       PDC listing without receipt for Replace option
  */
  public function pdcListWithoutReceiptByContract(Request $request)
  {
      $contract_id = $request->contractId;

      $pdc_list = Pdc::where('tenant_contract_id', $contract_id)
                     ->whereNull('pdc_receipt_no')
                     ->get();

      $option = '';
      if (count($pdc_list) == 0) {
          $option .= '<option value="">No PDC Available</option>';
      } else {
          $option .= '<option value="">Select Cheque</option>';
          foreach ($pdc_list as $list) {
              $option .= '<option value="' . $list->id . '" data-cheque="' . $list->pdc_check_no . '">' . $list->pdc_check_no . '</option>';
          }
      }

      return json_encode(['option_html' => $option]);
  }

   /***    receiptsAgreementList    of Contract    ***/
  public function receiptsAgreementList(TenantContract $tenantContract){
           
  $receiptslist =
   ReceiptsGeneration::where('tenant_contract_id',$tenantContract->id)
                      ->whereIn('receipts_generation_type', [0,2,1])
                      ->orderBy('id','desc')->paginate(15);

   $type = [0 => 'rent' , 1 => 'general' , 2 => 'deposit'];

   return view('backoffice::Receipt.receipt_list_contract',compact('receiptslist','type'));  

  }
  /***    Print View    ***/
  public function printPreview(Request $request){

    $inWords =null;
	$pdcInfo =null;
	
    $receiptId = $request->id;
    $receiptInfo = ReceiptsGeneration::with('tenantContractInfo.Unit', 'tenantContractInfo.building')->where('id',$receiptId)->first();

    $numberSplit = explode('.', $receiptInfo->receipts_generation_amt);

    $decimalPart = isset($numberSplit[1])?$numberSplit[1].'/1000':'XXX /1000';

    $inWords = numberToWords($numberSplit[0]).' And '.$decimalPart
    ;
	if(isset($receiptInfo->receipts_generation_is_pdc_bounce_id))
       $pdcInfo= Pdc::where('id',$receiptInfo->receipts_generation_is_pdc_bounce_id)->first();

    return view('backoffice::Receipt.print_view',compact('receiptInfo','inWords','pdcInfo'));
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
	/**
    *
    * Building Autocomplete
    *
    **/
 public function receiptBuildingAutocomplete(Request $request){

      $key = $request->term;
      $building = TenantContract::select([
            'tenant_contracts.building_id',
        ])->whereHas('building', function ($query)use($key) {
        $query->where('building_name', 'ILIKE', '%'.$key.'%');
       
      })->withCount([
            'building as value' => function($query) {
                $query->select(DB::raw("CONCAT(building_name,'-',building_code) as value"));
            },
            'building as ids' => function($query) {
                $query->select('id AS ids');
            }
        ])->active()->groupBy('building_id')->get();
     

      return $building ;

    }

}
