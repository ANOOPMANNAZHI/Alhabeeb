<?php

namespace Modules\BackOffice\Http\Controllers;

use DB;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Modules\BackOffice\Entities\GeneralLedger;
use Modules\Masters\Entities\Bank;
//use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Vendor;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\AccountParams;
use Modules\BackOffice\Entities\DimDetail;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Unit;
use Modules\Sales\Entities\LandlordContract;
use Dynamics;
use App\Setting;
class GeneralLedgerController extends Controller
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
        $generalLedgers = GeneralLedger::filter($request)
                                        ->sortable()
                                        ->paginate($this->noOfRecord);
      $ledger_types = [1 => 'General Ledger', 2 => 'Bank Payment' , 3 => 'Landlord Invoice', 4 => 'Bank Receipt'];

      $enquiry_fields = [
      'voucher_no' => 'Voucher No',
      'jv_refer_no' => 'JV Ref No',
      'bank__bank_name' => 'Bank',     
      'amount' => 'Amount',
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

     $route =   $request->url();


      if(isset($request->ajax)){
         $ajax = true;
      return view('backoffice::GeneralLedger.generalLedger_list_ajax',compact('generalLedgers','ajax','route'));
      }
   

        return view('backoffice::GeneralLedger.generalLedger_list',compact('generalLedgers','ledger_types','enquiry_fields','operations','route'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $banks = Bank::active()->where('accounts_bank',1)->get();

      $generateCode = $this->generalLedgerCode();
      $nextCode = $generateCode['code'];
      $accountCodes = AccountCodes::get();
          
      $first_account_code = $accountCodes->first();
     
      $first_account_dim1 = $first_account_code->dim1;
      $first_account_dim2 = $first_account_code->dim2;

      if($first_account_dim1 == 'DIVISION')
         $dim1 = DimDetail::get();   

		$year    = prefixData('general_ledger_prefix')->configuration_year;
		$isYearCorrect = (date('y') == $year)?true:false;

 
      if($first_account_dim2 == 'BUILDING')
         $dim2 = Building::active()->get();

    $ledger_types = [1 => 'General Ledger', 2 => 'Bank Payment' , 3 => 'Landlord Invoice', 4 => 'Bank Receipt'];

    return view('backoffice::GeneralLedger.add_generalLedger',compact('nextCode','ledger_types','banks','dim1','dim2','isYearCorrect'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
     
       $generateCode = $this->generalLedgerCode();
       $nextCode = $generateCode['code'];    
		$sumDr = 0;
      $sumCr = 0;

      if(count($request->account_id) > 0){
          foreach ($request->account_id as $key => $value) {
            
            $sumDr += isset($request->debit_amt[$key])? replaceCommaWithDot($request->debit_amt[$key]): 0.0;
            $sumCr += isset($request->credit_amt[$key])? replaceCommaWithDot($request->credit_amt[$key]): 0.0;
          }
          if($sumDr != $sumCr){
            //session()->flash('error', 'Credit And Debit Should Be Tally');
           // return redirect()->route('generalLedger.index');
          }
      }
       $user = \Auth::user();
      
       $generalLedger =  GeneralLedger::create([
              'voucher_no' => $nextCode,
              'general_ledger_type' => $request->general_ledger_type,
              'jv_refer_no' => $request->jv_refer_no,
              'doc_date' => $request->doc_date,
            //  'jv_amount' => $request->jv_amount,
              'general_ledger_desc' => $request->general_ledger_desc,
              //1- General Ledger, 2 -Bank Payment , 3- Landlord Invoice, 4 - Bank Receipt
              //'bank_id' => (in_array($request->general_ledger_type, [2,4]))? $request->bank_id: NULL,
              'bank_id' => (isset($request->general_ledger_type))? $request->bank_id: NULL,
              'amount' => replaceCommaWithDot($request->amount),
              'agreement_no' => $request->agreement_no,            
              'created_by' => \Auth::user()->id,
              'general_ledger_status' => ($user->can('ledger_approval'))? 2 : 1,
              'general_ledger_approval_status' => ($user->can('ledger_approval'))? 4 : 0,
         ]);

		Setting::where('configuration_settings','general_ledger_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
		]);
      if(count($request->account_id) > 0){

           foreach ($request->account_id as $key => $value) {
              
              if(!empty($value)){

                $account_code  =   AccountCodes::find($value);    

                $generalLedger->generalLedgerDim()->create([
                  
                  'account_id' => $value,
                  'description' => $account_code->acc_code_desc,
                  'building_id' => $request->building_id[$key],
                  'unit_id' => $request->unit_id[$key],
                  'jv_desc' => isset($request->jv_desc[$key])? $request->jv_desc[$key]: NULL,
                  'debit_amt' => isset($request->debit_amt[$key])? replaceCommaWithDot($request->debit_amt[$key]): 0.0,
                  'credit_amt' => isset($request->credit_amt[$key])? replaceCommaWithDot($request->credit_amt[$key]): 0.0,
                  'recovery' => isset($request->recovery[$key])? $request->recovery[$key]: 0,
                  'dim1able_type' => isset($request->dim1[$key])? 'Modules\BackOffice\Entities\DimDetail' : NULL,
                  'dim1able_id' => isset($request->dim1[$key])? $request->dim1[$key] : NULL, 
                  'dim2able_type' => isset($request->dim2[$key])? 'Modules\Masters\Entities\Building': NULL,
                  'dim2able_id' => isset($request->dim2[$key])? $request->dim2[$key] : NULL              

               ]);

              }
               
          }
      }
	 
     session()->flash('success', 'General Ledger');
     return redirect()->route('generalLedger.index');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show(GeneralLedger $generalLedger)
    {
        return view('backoffice::GeneralLedger.view_general_ledger',compact('generalLedger'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit(GeneralLedger $generalLedger)
    {
      $banks = Bank::active()->where('accounts_bank',1)->get();

     
      $accountCodes = AccountCodes::get();
      $buildings = Building::active()->get();
      $units = Unit::active()->where('building_id',$buildings->first()->id)->get();      
      $first_account_code = $accountCodes->first();
     
      $first_account_dim1 = $first_account_code->dim1;
      $first_account_dim2 = $first_account_code->dim2;

      if($first_account_dim1 == 'DIVISION')
         $dim1 = DimDetail::get();   

 
      if($first_account_dim2 == 'BUILDING')
         $dim2 = Building::active()->get();
 
 // General auto generate code year checking alert		
	  $year    = prefixData('general_ledger_prefix')->configuration_year;
      $isYearCorrect = (date('y') == $year)?true:false;
	  
    $ledger_types = [1 => 'General Ledger', 2 => 'Bank Payment' , 3 => 'Landlord Invoice', 4 => 'Bank Receipt'];

    return view('backoffice::GeneralLedger.add_generalLedger',compact('generalLedger','ledger_types','banks' ,
         'accountCodes','buildings','units' ,'dim1','dim2','isYearCorrect'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, GeneralLedger $generalLedger)
    {
		$sumDr = 0;
		$sumCr = 0;

		if(count($request->account_id) > 0){
          foreach ($request->account_id as $key => $value) {
            
            $sumDr += isset($request->debit_amt[$key])? replaceCommaWithDot($request->debit_amt[$key]): 0.0;
            $sumCr += isset($request->credit_amt[$key])? replaceCommaWithDot($request->credit_amt[$key]): 0.0;
          }
          if($sumDr != $sumCr){
           // session()->flash('error', 'Credit And Debit Should Be Tally');
           // return redirect()->route('generalLedger.edit',$generalLedger->id);
          }
		}
       $user = \Auth::user();
        $generalLedger->update([              
              'general_ledger_type' => $request->general_ledger_type,
              'jv_refer_no' => $request->jv_refer_no,
              'doc_date' => $request->doc_date,
            //  'jv_amount' => $request->jv_amount,
              'general_ledger_desc' => $request->general_ledger_desc,
              // 'bank_id' => (in_array($request->general_ledger_type, [2,4]))? $request->bank_id: NULL,
              'bank_id' => (isset($request->general_ledger_type))? $request->bank_id: NULL,
              'amount' => replaceCommaWithDot($request->amount),
              'agreement_no' => $request->agreement_no,            
              'updated_by' => \Auth::user()->id,
            //  'general_ledger_status' => ($user->can('ledger_approval'))? 2 : 1,
           //   'general_ledger_approval_status' => ($user->can('ledger_approval'))? 4 : 0,
         ]);


         $generalLedger->generalLedgerDim()->delete();

         if(count($request->account_id) > 0){

           foreach ($request->account_id as $key => $value) {
              
              if(!empty($value)){

                $account_code  =   AccountCodes::find($value);    

                $generalLedger->generalLedgerDim()->create([
                  
                  'account_id' => $value,
                  'description' => $account_code->acc_code_desc,
                  'building_id' => $request->building_id[$key],
                  'unit_id' => isset($request->unit_id[$key])?$request->unit_id[$key]: NULL,
                  'jv_desc' => isset($request->jv_desc[$key])? $request->jv_desc[$key]: NULL,
                  'debit_amt' => isset($request->debit_amt[$key])? replaceCommaWithDot($request->debit_amt[$key]): 0.0,
                  'credit_amt' => isset($request->credit_amt[$key])? replaceCommaWithDot($request->credit_amt[$key]): 0.0,
                  'recovery' => isset($request->recovery[$key])? $request->recovery[$key]: 0,
                  'dim1able_type' => isset($request->dim1[$key])? 'Modules\BackOffice\Entities\DimDetail' : NULL,
                  'dim1able_id' => isset($request->dim1[$key])? $request->dim1[$key] : NULL, 
                  'dim2able_type' => isset($request->dim2[$key])? 'Modules\Masters\Entities\Building': NULL,
                  'dim2able_id' => isset($request->dim2[$key])? $request->dim2[$key] : NULL              

               ]);

              }               
            }
          }

     session()->flash('success', 'General Ledger Updated');
     return redirect()->route('generalLedger.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(GeneralLedger $generalLedger)
    {
       // $generalLedger->generalLedgerDim()->delete();
        $generalLedger->delete();
        session()->flash('success', 'General Ledger Deleted');
        return redirect()->route('generalLedger.index');
    }
/*
*  maintenance_invoice_no
*
*/
    public function generalLedgerCode(){

      $prefix  = prefixData('general_ledger_prefix')->configuration_value.prefixData('general_ledger_prefix')->configuration_year;
      
      $incVal  = prefixData('general_ledger_prefix')->configuration_increment_value;
      
      $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);
      
      return array('code'=>$nextCode,'inc'=>$incVal);

    }
    /*
    *  action
    *
    */
    public function action(GeneralLedger $generalLedger,$action){

    $msg = '';
    
      switch($action){

        case 'send-for-approval' :  $generalLedger->update([
                                  'general_ledger_approval_status' => 2 
                                ]);
                                $msg = "Send for Approval" ;
                                break;  

        case 'send-for-unapproval' :  $generalLedger->update([
                                  'general_ledger_approval_status' => 3 
                                ]);
                                $msg = "Send for Unapproval" ;
                                break;  

	case 'post' : 
		$amountDr = 0;
		$amountCr = 0;
		$ledgerHeader = array(
				'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
				'DataAreaId'=>DATA_AREA_ID,
				'company' =>COMPANY,
			);
	 //Validate
      $summCredit = 0;
      $summDebit = 0;
	  $difftotval = 0;
      $totAmount = number_format($generalLedger->amount,3);
      foreach($generalLedger->generalLedgerDim as $key=>$item){
           $summCredit = $summCredit+$item->credit_amt ;
           $summDebit  = $summDebit+$item->debit_amt;
      
        }
      $difftotval =  bcsub($summDebit,$summCredit,3);
      if($generalLedger->general_ledger_type == 1 || $generalLedger->general_ledger_type == 3){

       
        if($difftotval!=0)
        {
          session()->flash('error', 'Total Debit should Equal to Total Credit.');
          return redirect()->back();  
        }
      }else{
        if($totAmount !=  number_format($difftotval,3))
        {
		
        session()->flash('error', 'Bank Amount should tally with the Difference of Debit – Credit.');
          return redirect()->back();  
        }
      }
      //Posting error check area Anoop starts here.......................................

     // print_r($ledgerHeader);exit();
			//dd($generalLedger->generalLedgerDim);

      //$response = 'noerror';
			$response	=	Dynamics::LedgerAxHeaderPushData('AXGeneralLedgerHeader', $ledgerHeader);
			
			if($response=='Error'){
				return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
			}
			if($generalLedger->general_ledger_type == 1 || $generalLedger->general_ledger_type == 3){ // General Ledger, Landlord Invoice
				$accountType = AX_GL;
			}
			else{ // Bank payment, Bank receipt
				$accountType = AX_GL;
				$bank_checkbbok_id = isset($generalLedger->bank->bank_chequebook_id)?$generalLedger->bank->bank_chequebook_id:'';
				$bank_division = isset($generalLedger->bank->dim1Value)?$generalLedger->bank->dim1Value:'';
				if(!isset($bank_checkbbok_id)){
					session()->flash('success', 'Bank Parameter Missing');
					return redirect()->back();  
				}
				if($generalLedger->general_ledger_type == 2 ){ // Bank Payment  will be credit
					
					$transaction = 'Credit';
					$amountDr = 0;
					$amountCr = $generalLedger->amount;
				}
				else{// Bank Payment  will be debit
					
					$transaction = 'Debit';
					
					$amountDr = $generalLedger->amount;
					$amountCr = 0;
				}
				//dd($item->->accountCode->acc_code_val);
				$ledgerLineItem[] = array(
							'JournalNum'=> $response,
							'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
							'PaymentDate'=> $generalLedger->doc_date->format('Y-m-d'),
							'Description'=> $generalLedger->general_ledger_desc,
							'Account'=> $bank_checkbbok_id,
							'currency'=> CURRENCY,
							'accountType'=> AX_BANK,
							'paymentMethod'=>'',
							'checkBookid'=>'',
							'documentNo'=> '',
							'DebitCredit'=>$transaction,
							'voucher'=>$generalLedger->voucher_no,
							'AmountCredit'=>$amountCr,
							'AmountDebit'=>$amountDr,
							'Remarks'=>$generalLedger->general_ledger_desc,
							'Invoice'=>$generalLedger->jv_refer_no,
							'dimension1'=>'Building',
							'dimension1value'=>'000',
							'dimension2'=> 'Division',
							'dimension2value'=>$bank_division,
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
			if(count($generalLedger->generalLedgerDim)>0){
				
				foreach($generalLedger->generalLedgerDim as $key=>$item){
					
					
					$ledgerLineItem[] = array(
							'JournalNum'=> $response,
							'JournalName'=> GENERAL_LEDGER_JOURNAL_NAME,
							'PaymentDate'=> $generalLedger->doc_date->format('Y-m-d'),
							'Description'=> $generalLedger->general_ledger_desc,
							'Account'=> $item->accountCode->acc_code_val,
							'currency'=> CURRENCY,
							'accountType'=> $accountType,
							'paymentMethod'=>'',
							'checkBookid'=>'',
							'documentNo'=> '',
							'DebitCredit'=>($item->credit_amt>0)?'Credit':'Debit',
							'voucher'=>$generalLedger->voucher_no,
							'AmountCredit'=>($item->credit_amt >0 )?$item->credit_amt:0,
							'AmountDebit'=>($item->debit_amt>0)?$item->debit_amt:0,
							'Remarks'=>$generalLedger->general_ledger_desc,
							'Invoice'=>$generalLedger->jv_refer_no,
							'dimension1'=> 'Building',
							'dimension1value'=>isset($item->building->building_code)?$item->building->building_code:'000',
							'dimension2'=> 'Division',
							'dimension2value'=>($item->dim1able_id ==1)?AX_DIVISION_HO:AX_DIVISION_PLMS,
							'dimension3'=> 'Employee',
							'dimension3value'=>'00000',
							'dimension4'=> 'Location',
							'dimension4value'=>'00',
							'dimension5'=> 'Projects',
							'dimension5value'=>'00',
							'DataAreaId'=>DATA_AREA_ID,
							'company'=>COMPANY,
						);
						
						//if(!empty($item->credit_amt) && $generalLedger->general_ledger_type == 2){
						//	$amountCr += $item->credit_amt;
						//}
						//elseif($generalLedger->general_ledger_type == 4){ // Bank Receipt
						//	$amountDr += $item->debit_amt;
						//}
						//dd($result );
				}
			}
			
		// Only for General Ledger AND Landlord Invoice
		//if($generalLedger->general_ledger_type = 2 || $generalLedger->general_ledger_type = 4){
		//	$ledgerLineItem[0]['AmountCredit']= $amountCr;
		//	$ledgerLineItem[0]['AmountDebit'] = $amountDr;
		//}
		//print_r(json_encode($ledgerLineItem));exit();
		$result = Dynamics::LedgerAxLineItemPushData('AXGeneralLedgerLineItem', $ledgerLineItem);
				
		if(count($ledgerLineItem) >0 && $result=='Error'){
			return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
		}
		$generalLedger->update([
                                  'general_ledger_approval_status' => 4 ,
                                  'general_ledger_status' => 3 ,
								  'ax_batch_id'=> $result,
								  'ax_batch_no'=> $generalLedger->voucher_no,		
                                  'general_ledger_posted_by' => \Auth::user()->id,
                                  'general_ledger_posted_date' => now()
                                ]);
                                $msg = "Ledger Posted" ;
                                break;  

        case 'approve' :   $generalLedger->update([
                              'general_ledger_approval_status' => 4,
                              'general_ledger_status' => 2
                            ]);
                             $msg = "Ledger Approved" ;
                          break;

        case 'unapprove' :   $generalLedger->update([
                                  'general_ledger_approval_status' => 1,
                                  'general_ledger_status' => 4
                                ]);
                            $msg = "Ledger Unapproved" ;
                            break; 

        case 'reject' :   $msg = ($generalLedger->general_ledger_approval_status == 2) ?  'Ledger Approval Rejected' :  'Ledger Unapproval Rejected';

                        $generalLedger->update([
                          'general_ledger_approval_status' => 5                         
                        ]);
                        break; 
            

      }

      session()->flash('success', $msg);
      return redirect()->back();      

    }


    /*
    *
    *  generalLedgerApproval
    *
    */
    public function generalLedgerApproval(Request $request){

     $enquiry_fields = [
      'maintenance_invoice_no' => 'Invoice No',
      'vendor__vendor_name' => 'Contractor Name',
      'vendor__vendor_code' => 'Contractor Code',
      'maintenance_invoice_refer_amt' => 'Amount',
      'maintenance_invoice_date' => 'Invoice Date',
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

        $generalLedgers = GeneralLedger::approval()->filter($request)
                                         ->sortable()->latest() 
                                         ->paginate($this->noOfRecord);

     $ledger_types = [1 => 'General Ledger', 2 => 'Bank Payment' , 3 => 'Landlord Invoice', 4 => 'Bank Receipt'];

     $route = $request->url();

     if(isset($request->ajax)){
         $ajax = true;
      return view('backoffice::GeneralLedger.generalLedger_list_ajax',compact('generalLedgers','ajax','route'));
      }

      return view('backoffice::GeneralLedger.generalLedger_list',compact('generalLedgers','ledger_types','enquiry_fields','operations','route'));
       
    }


    /*
    * Filter
    *
    */
    public function generalLedgerFilter(Request $request){


      if(isset($request->search_form)){

      $enquiry_fields = [
      'voucher_no' => 'Voucher No',
      'jv_refer_no' => 'JV Ref No',
      'bank.bank_name' => 'Bank',     
      'amount' => 'Amount',
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
      return view('maintenance::enquiry_filter',compact('enquiry_fields','operations'));
     }


       $generalLedgers =  GeneralLedger::filter($request)->paginate(10);
      
       $ajax = true;

       return view('backoffice::GeneralLedger.generalLedger_list_ajax',compact('generalLedgers','ajax'));

    }

    /*
    * generalLedgerDistribution
    *
    *
    */
    public function generalLedgerDistribution(Request $request){

      $ledger_type = $request->ledger_type;
      $agreement_no = $request->agreement_no;

      if($ledger_type == 3 && $agreement_no != ''){

           
        $landlordContract = LandlordContract::has('buildingInfo')
                                             ->where('landlord_contract_no',$agreement_no)
                                             ->first();

          if($landlordContract) {                                   

            $management_type = $landlordContract->management_id;

            $buildings = Building::where('id',$landlordContract->building_id)->first();             
            $units = Unit::where('building_id',$buildings->id)->get();

            $dim1 = DimDetail::get();
            $dim2 = $buildings;

            return view('backoffice::GeneralLedger.common_distribution',compact('buildings','units','dim1','dim2'));
          }
          else 
            return 'Invaild Agreement';

       }else{
 

        $accountCodes = AccountCodes::get();
     
        $first_account_code = $accountCodes->first();
       
        $first_account_dim1 = $first_account_code->dim1;
        $first_account_dim2 = $first_account_code->dim2;

        if($first_account_dim1 == 'DIVISION')
           $dim1 = DimDetail::get();   

   
        if($first_account_dim2 == 'BUILDING')
           $dim2 = Building::active()->get();
           
           return view('backoffice::GeneralLedger.common_distribution',compact('dim1','dim2'));

       }
		
     


    }

	/**
    *
    * Account code Autocomplete
    *
    **/
    public function accountCodeAutocomplete(Request $request){

       $key = $request->term;
       $accountCodes = AccountCodes::get();
       $acc_codes =   AccountCodes::where('acc_code_val', 'ILIKE', '%'.$key.'%')->orWhere('acc_code_desc', 'ILIKE', '%'.$key.'%')
                               ->select('id AS ids',DB::raw("CONCAT(acc_code_val,'-',acc_code_desc) as value"))
                               ->get();

       return $acc_codes ;


    }
    /**
    *
    * Account code Autocomplete without Description
    *
    **/
    public function accountCodeAutocompleteWithoutDesc(Request $request){

       $key = $request->search;
       $accountCodes = AccountCodes::get();
       $acc_codes =   AccountCodes::where('acc_code_val', 'ILIKE', '%'.$key.'%')
       ->orWhere('acc_code_desc', 'ILIKE', '%'.$key.'%')
                               ->select('id AS ids',DB::raw("CONCAT(acc_code_val,'-',acc_code_desc) as value"))->take(25)->get();

       return $acc_codes ;


    }
   /**
    *
    * Account code with param info
    *
    **/
    public function accountCodeWithParamInfo(Request $request){

       $account_id = $request->acc_code_id;
      
       $acc_codes =   AccountCodes::where('id',$account_id)->first();
       $type ='';
       if(isset($acc_codes->acc_code_val)){
          
          $paramData  = AccountParams::where('acc_params_dr_acc',$acc_codes->acc_code_val)->first();

          if(isset($paramData->id)) $type = $paramData->acc_params_dr_type; 
       
           if(empty($paramData->id)){

              $paramData  = AccountParams::where('acc_params_cr_acc',$acc_codes->acc_code_val)->first();
              if(isset($paramData->id)) $type = $paramData->acc_params_cr_type; 
           }
       }
       return array($type , $acc_codes->acc_code_desc);


    }
    /**
    *
    * Landlord contract No
    *
    **/
    public function landlordAgreementAutocomplete(Request $request){

       $key          = $request->term;
       $agreementNo  =   LandlordContract::where('landlord_contract_no', 'ILIKE', '%'.$key.'%')->select('id AS ids','landlord_contract_no as value')->get();

       return $agreementNo ;


    }

	/**
  *
  * Building Autocomplete
  *
  **/
public function buildingAutocompleteInGl(Request $request){

 $key = $request->term;
 
 $building =   Building::active()->where('building_name', 'ILIKE', '%'.$key.'%')
 ->select('building_name AS value','id AS ids')
 ->get();

 return $building ;


}
/*
*
*
* Building By Unit
*
*/
public function buildingByUnitGL(Request $request){

  $id = $request->input('id');
  
  $units['buildUnit'] = Unit::where('building_id',$id)->where('unit_vaccant_status',1)->orderBy('unit_no','asc')->get();
  
  return json_encode($units);

  }
}
