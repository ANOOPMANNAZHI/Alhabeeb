<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Masters\Entities\Employee;
use Modules\BackOffice\Entities\LandlordInvoice;
use Modules\BackOffice\Entities\LandlordInvoiceDimension;
use Modules\BackOffice\Entities\AccountParams;
use Modules\BackOffice\Entities\DimDetail;
use Modules\Masters\Entities\Vendor;
use Modules\Sales\Entities\LandlordContract;
use Dynamics;
use Carbon\Carbon;
use App\Setting;

class LandlordInvoiceController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');  
    $this->middleware('permission:view_landlord_invoice_approval', ['only' => ['index']]);
    $this->middleware('permission:add_landlord_invoice', ['only' => ['create','store']]);
    $this->middleware('permission:edit_landlord_invoice', ['only' => ['edit','update']]);
    $this->middleware('permission:view_landlord_invoice|view_landlord_invoice_approval', ['only' => ['show']]);

    $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
  }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
       $landlordInvoices =  LandlordInvoice::filter($request)->approval()
                                           ->sortable()                             
                                           ->paginate($this->noOfRecord);


      $enquiry_fields = [
      'landlord_invoice_voucher_no' => 'Voucher No',
      'landlordContractInfo__landlord_contract_no' => 'Agreement No',
      'landlord_given_invoice_no' => 'Ref No.',
      'landlordContractInfo__landlord_contract_amt' => 'Contract Amt',
      
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

      if(isset($request->ajax))
      return view('backoffice::LandlordInvoice.invoice_list_ajax',compact('landlordInvoices'));                           
       
      return view('backoffice::LandlordInvoice.invoice_list',compact('landlordInvoices','enquiry_fields','operations'));
    }


    /*
    *
    *
    */
    public function landlordInvoiceFilter(Request $request){

     if(isset($request->search_form)){

       $enquiry_fields = [
      'landlord_invoice_voucher_no' => 'Voucher No',
      'landlordContractInfo__landlord_contract_no' => 'Agreement No',
      'landlord_given_invoice_no' => 'Ref No.',
      'landlordContractInfo__landlord_contract_amt' => 'Contract Amt',
      
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

      return view('maintenance::enquiry_filter',compact('enquiry_fields','operations'));
     }

    $landlordInvoices =  LandlordInvoice::filter($request)
                                         ->approval()
                                         ->sortable()
                                         ->paginate($this->noOfRecord);
    $request->flash();
    $ajax = true;

    return view('backoffice::LandlordInvoice.invoice_list_ajax',compact('landlordInvoices','ajax'));

   }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(LandlordContract $landlordContract)
    {  
        if(!empty($landlordContract->landlordInvoice)){
            return redirect()->route('landlordInvoiceShow',$landlordContract->id);
        }

     //   $nextCode = $this->landlordInvoiceCode(); 
          $year    = prefixData('maintenance_invoice_prefix')->configuration_year;
          $isYearCorrect = (date('y') == $year)?true:false;
          $generateCode = $this->landlordInvoiceCode();
          $nextCode = $generateCode['code'];
        // $comprehensive_pay 
        $distribution_details =  AccountParams::where('acc_params_tran_desc','=',AX_VENDOR_COMP_ACC_PARAM)->first(); 
		// Cost Recognition
		$distribution_breakup_details =  AccountParams::where('acc_params_tran_desc','=',AX_VENDOR_COST_RECOG_ACC_PARAM)->first(); 
		
		//Distribution Breakup Details
        $from_date = Carbon::parse($landlordContract->landlord_contract_valid_from_date);
        $to_date = Carbon::parse($landlordContract->landlord_contract_valid_to_date);
        $months =  $from_date->diffInMonths($to_date); //dd($months);
        $months =  ($months > 0)? $months+1 : 1;
        $permonth_amt = $landlordContract->landlord_contract_amt / $months;
         // 2 - Landlords
        $vendors     = Vendor::where('vendor_type_id',2)->latest()->get();
        // Users with role technichian 
        $technicians       = \App\User::whereHas('roles', function($q){
              $q->where('name', 'technician');
        })->get();
        
		$distbkparry = $this->lcontractInvoiceCalculation($from_date, $to_date , $permonth_amt);         
        return view('backoffice::LandlordInvoice.add_invoice',compact('landlordContract','nextCode','distribution_details','distribution_breakup_details','from_date','to_date','permonth_amt','vendors','technicians','isYearCorrect','months','distbkparry'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request,LandlordContract $landlordContract)
    {
       
   //     $nextCode = $this->landlordInvoiceCode();  
       $generateCode = $this->landlordInvoiceCode();
       $nextCode = $generateCode['code'];
        // $comprehensive_pay 
        $distribution_details =  AccountParams::where('acc_params_tran_desc','=',AX_VENDOR_COMP_ACC_PARAM)->first(); 
		// Cost Recognition
		$distribution_breakup_details =  AccountParams::where('acc_params_tran_desc','=',AX_VENDOR_COST_RECOG_ACC_PARAM)->first(); 
		
        $user = \Auth::user();

		$landlordInvoice =    $landlordContract->landlordInvoice()->create([
               'landlord_invoice_voucher_no' => $nextCode,
               'landlord_invoice_voucher_date' => $request->landlord_invoice_voucher_date,
               'landlord_invoice_amt' => $landlordContract->landlord_contract_amt,
               'landlord_invoice_doc_type' => 'Invoice',
               'landlord_given_invoice_no' => $request->landlord_given_invoice_no,
               'landlord_invoice_desc' => $request->landlord_invoice_desc,
               'created_by' => \Auth::user()->id,
               'landlord_invoice_status' => ($user->can('landlord_invoice_approval'))? 2 : 1,
               'landlord_invoice_approval_status' => ($user->can('landlord_invoice_approval'))? 4 : 0,
        ]);
 
//Insert Into Dimensions 
        $landlordInvoice->landlordInvoiceDimension()->createMany([
            [
            'dim1able_type' => 'Modules\BackOffice\Entities\DimDetail',
            'dim1able_id' => $request->dim1_hidden,
            'dim2able_type' => 'Modules\Masters\Entities\Building',
            'dim2able_id' => $landlordContract->building_id,
            'ac_codes_id' => isset($distribution_details->creditAccountInfo->id)?$distribution_details->creditAccountInfo->id:0,
            'description' => isset($distribution_details->creditAccountInfo->acc_code_desc)?$distribution_details->creditAccountInfo->acc_code_desc:$distribution_details->acc_params_cr_type,
            'type' => $distribution_details->acc_params_cr_type,
            'credit_amount' => $landlordContract->landlord_contract_amt
            
            ],[
            'dim1able_type' => 'Modules\BackOffice\Entities\DimDetail',
            'dim1able_id' => $request->dim2_hidden,
            'dim2able_type' => 'Modules\Masters\Entities\Building',
            'dim2able_id' => $landlordContract->building_id,
            'ac_codes_id' => isset($distribution_details->debitAccountInfo->id)?$distribution_details->debitAccountInfo->id:0,
            'description' => isset($distribution_details->debitAccountInfo->acc_code_desc)?$distribution_details->debitAccountInfo->acc_code_desc:acc_params_dr_type,
            'type' => $distribution_details->acc_params_dr_type,
            'debit_amount' => $landlordContract->landlord_contract_amt             
            ]
           ]
          );
//Insert Into landlord_invioce_distribution_break_up      
        // Cost Recognition
        $distribution_breakup_details =  AccountParams::where('acc_params_tran_desc','=',AX_VENDOR_COST_RECOG_ACC_PARAM)->first();

        $from_date =Carbon::parse($landlordContract->landlord_contract_valid_from_date);
         $to_date =Carbon::parse($landlordContract->landlord_contract_valid_to_date);
         $months =   $from_date->diffInMonths($to_date);
         $months =  ($months > 0)? $months+1 : 1;
         $permonth_amt = $landlordContract->landlord_contract_amt / $months;
         $distbkparry = $this->lcontractInvoiceCalculation($from_date, $to_date , $permonth_amt);
        foreach ($distbkparry as $distbkp){
			
			$landlordInvoice->landlordInvoiceDistributionBreakup()->createMany([
				[   
					'account_type' => $distribution_breakup_details->acc_params_cr_type,
					 'account_code' => $distribution_breakup_details->acc_params_cr_acc,
					 'credit_amount' => $distbkp['rent'],
					 'date' => $distbkp['inv_date'],
				],
				[
					'account_type' => $distribution_breakup_details->acc_params_dr_type,
					'account_code' => $distribution_breakup_details->acc_params_dr_acc,
					'debit_amount' => $distbkp['rent'],
					'date' =>  $distbkp['inv_date'],
				]                     
		   ]);    
        
       }  
 
		Setting::where('configuration_settings','maintenance_invoice_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);


        session()->flash('success', 'Invoice Created');

        return redirect()->back();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show(LandlordContract $landlordContract)
    {
	
         // $comprehensive_pay 
        $distribution_details =  AccountParams::where('acc_params_tran_desc','=',AX_VENDOR_COMP_ACC_PARAM)->first(); 
		// Cost Recognition
		$distribution_breakup_details =  AccountParams::where('acc_params_tran_desc','=',AX_VENDOR_COST_RECOG_ACC_PARAM)->first(); 
		
         $landlordInvoice = $landlordContract->landlordInvoice;
		 $dstbrkup = $landlordInvoice->landlordInvoiceDistributionBreakup->sortBy('date');

       return view('backoffice::LandlordInvoice.view_invoice',compact('landlordContract','landlordInvoice','distribution_details','distribution_breakup_details','dstbrkup'));
       
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit(LandlordContract $landlordContract)
    {
          // $comprehensive_pay 
         $distribution_details =  AccountParams::where('acc_params_tran_desc','=',AX_VENDOR_COMP_ACC_PARAM)->first(); 
		// Cost Recognition
		 $distribution_breakup_details =  AccountParams::where('acc_params_tran_desc','=',AX_VENDOR_COST_RECOG_ACC_PARAM)->first(); 
		
          $dim1 = DimDetail::get();   
          $landlordInvoice = $landlordContract->landlordInvoice;


         $from_date =Carbon::parse($landlordContract->landlord_contract_valid_from_date);
         $to_date =Carbon::parse($landlordContract->landlord_contract_valid_to_date);
         $months =   $from_date->diffInMonths($to_date);
         $months =  ($months > 0)? $months+1 : 1;
         $permonth_amt = $landlordContract->landlord_contract_amt / $months;

       //   dd($landlordInvoice);
	    $year    = prefixData('maintenance_invoice_prefix')->configuration_year;
          $isYearCorrect = (date('y') == $year)?true:false;

		$distbkparry = $this->lcontractInvoiceCalculation($from_date, $to_date , $permonth_amt);   		

         return view('backoffice::LandlordInvoice.add_invoice',compact('landlordContract','landlordInvoice','distribution_details','distribution_breakup_details','dim1','from_date','to_date','permonth_amt','isYearCorrect','distbkparry'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, LandlordContract $landlordContract)
    {      
      
     $landlordContract->landlordInvoice()->update([
               'landlord_invoice_voucher_date' => $request->landlord_invoice_voucher_date,
               'landlord_given_invoice_no' => $request->landlord_given_invoice_no,
               'landlord_invoice_desc' => $request->landlord_invoice_desc,
               'updated_by' => \Auth::user()->id,
        ]);

       $landlordInvoice = $landlordContract->landlordInvoice;      
       
       $landlordInvoice->landlordInvoiceDimension->get(0)->update([
           'dim1able_id' => $request->dim1[0]            
            ]);
       $landlordInvoice->landlordInvoiceDimension->get(1)->update([
         'dim1able_id' => $request->dim1[1]            
            ]); 

       session()->flash('success', 'Invoice Saved');
       return redirect()->route('landlordInvoiceShow',$landlordContract->id);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(LandlordContract $landlordContract)
    {
         $landlordContract->landlordInvoice()->delete();
          session()->flash('success', 'Invoice Deleted');
          return redirect()->route('landlord-contract.index');
    }




/*
*  maintenance_invoice_no
*

    public function landlordInvoiceCode(){

      $invoiceLatest = LandlordInvoice::orderBy('id', 'desc')->withTrashed()->first();
      $prefix  = prefixData('landlord_invoice_prefix')->configuration_value;
     	
      if(!empty($invoiceLatest))
        $nextCode = $prefix.str_pad($invoiceLatest->id+1,4,'0',STR_PAD_LEFT);
      else
        $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
	
       return $nextCode;

    }
*/
	public function lcontractInvoiceCalculation($date1, $date2, $rent){

      $res        = array();
      $lastMonth  = array();
      $inc        = 0;
      $date1      = date("Y-m-d", strtotime($date1));
      $date2      = date("Y-m-d", strtotime($date2));

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
      $sumOfStartDays = 0;
      $sumOfEndDays = 0;
      $sumOfSameStartEnd = 0;
      $monthIsOne = 1;
      $sumOfMonthRent = 0;

      $startDate->setTimestamp(strtotime($date1));
      $endDate->setTimestamp(strtotime($date2));
      
      // First and last date eg)01-01-2019 and 31-01-2019
      if($firstDate === strtotime($date1) && $lastDate === strtotime($date2) && $explodeStartDtValue[1] == $explodeEndDtValue[1] && $explodeStartDtValue[0] == $explodeEndDtValue[0]){
        
        

        $sumOfMonthRent         = round($monthIsOne * $rent);
        $res[$inc]['inv_date']  = date('Y-m-d',strtotime($date1));
        $res[$inc]['month']     = date('m/Y',$firstDate);
        $res[$inc]['rent']      = number_format((float)$sumOfMonthRent, 3, '.', '');
        $inc++;
  
      }
      else{

        
          // First and last date eg)01-01-2019 and 10-01-2019 Both are same month and Year.
        if ($startDate->format('Y-m') === $endDate->format('Y-m')) {
          
          $startDays = $explodeEndDtValue[2] - $explodeStartDtValue[2] + 1;
          $sumOfStartDays  = round(($rent/30) * $startDays);
       
          $res[$inc]['inv_date']  = date('Y-m-d',strtotime($date1));
          $res[$inc]['month']   = $startDate->format('m/Y');
          $res[$inc]['rent']    = number_format((float)$sumOfStartDays, 3, '.', '');
          $inc++;
          
        }
        else{

            //echo $explodeStartDtValue[2]; exit;
             //Consider No of days for every month is 30.
            // First date eg)15-01-2019 remain days in that month is 17.
          if($explodeStartDtValue[2] <= $noOfDaysStartMonth){
            
            if($explodeStartDtValue[2] >= 30) 
                $startDays = 30;  
            else  
                $startDays = 30 - $explodeStartDtValue[2] + 1;
            //If start from month first day consider as full month rent
            if($explodeStartDtValue[2]==1)
                $sumOfStartDays     = round($rent);
            else
                $sumOfStartDays     = round(($rent/30) * $startDays);
            
            $res[$inc]['inv_date']  = date('Y-m-d',strtotime($date1));
            $res[$inc]['month']     = $explodeStartDtValue[1].'/'.$explodeStartDtValue[0];
            $res[$inc]['rent']      = number_format((float)$sumOfStartDays, 3, '.', '');
            
            $inc++;
            
          }
            // Last date eg)15-01-2019 no of days in that month is 15.

          if($explodeEndDtValue[2] <= $noOfDaysEnd){
           
            if($noOfDaysEnd == $explodeEndDtValue[2])
                  $sumOfEndDays = round($rent);
            else
                  $sumOfEndDays = round(($rent/30) * $explodeEndDtValue[2]);
            
            $lastMonth['inv_date']  = $explodeEndDtValue[0].'-'.$explodeEndDtValue[1].'-01';
            $lastMonth['month']     = $explodeEndDtValue[1].'/'.$explodeEndDtValue[0];
            $lastMonth['rent']      = number_format((float)$sumOfEndDays, 3, '.', '');
            
          }
        }

      }
        //$next_month_ts = strtotime($date1.' +1 month');
        //$prev_month_ts = strtotime($date2.' -1 month');
      $date = new \DateTime($date1);

      $nextStartDt  =  $date->modify('first day of next month')->format('Y-m-d');
      //$nextStartDt  = date('2021-01-02', strtotime('first day of next month'));

      $prevEndDt    = date('Y-m-d', strtotime($date2.' last day of previous month'));
      
      $startDateNew   =   new \DateTime($nextStartDt);
      $endDateNew     =   new \DateTime($prevEndDt);

      $explodeNextStartDt  = explode("-",$nextStartDt);
      $explodePrevEndDt    = explode("-",$prevEndDt);
      
      
      if ((strtotime($prevEndDt)) >= (strtotime($nextStartDt))){
        
        $interval   = $endDateNew->diff($startDateNew);

        $monthIsOne = $interval->format('%m') + 1;
        if($interval->y > 0){
           $totMonth =  $interval->y * 12;
           $monthIsOne = $monthIsOne + $totMonth;
          
        }
        
        $month      = date('m',strtotime($nextStartDt));
        $year       = date('Y',strtotime($nextStartDt)); 


        // To solve extra invoice or to avoid last month repeated invoice
        // Case of between first month and last month only one month $monthIsOne =1
       
        if($explodeStartDtValue[1] == $explodeEndDtValue[1] 
          && $monthIsOne > 1 && $explodeStartDtValue[2] !=1)
        {
        $monthIsOne--;
        }
        for($i=0; $i<$monthIsOne; $i++){
         
         if($month > 12){
            $month = '01';
            $year++;
            
 
            $res[$inc]['inv_date']  = $year.'-'.$month.'-'.'01';
            $res[$inc]['month']     = $month.'/'.$year;
            $res[$inc]['rent']      = number_format(round($rent), 3, '.', '');
            
          
          }
          else{

           
            $res[$inc]['inv_date']  = $year.'-'.$month.'-'.'01';
            $res[$inc]['month']     = $month.'/'.$year;
            $res[$inc]['rent']      = number_format(round($rent), 3, '.', '');

          }
        if($month < 9)
          $month = '0'.($month+1);
        else
          $month = $month+1;

        $inc++;
      }
      
      $sumOfMonthRent = $monthIsOne * $rent;

      $sumOfMonthRent = round($sumOfMonthRent + $sumOfStartDays + $sumOfEndDays); 

      

    }
    
       array_push($res, $lastMonth);
    //dd($res);
    return array_filter($res);
   }
   
	public function landlordInvoiceCode(){

       $prefix  = prefixData('maintenance_invoice_prefix')->configuration_value.prefixData('maintenance_invoice_prefix')->configuration_year;

    $incVal  = prefixData('maintenance_invoice_prefix')->configuration_increment_value;

    $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);

    return array('code'=>$nextCode,'inc'=>$incVal);

    }


    /*
    *  action
    *
    */
    public function action(LandlordContract $landlordContract,$action){

    $msg = '';
    
      switch($action){

        case 'send-for-approval' :  $landlordContract->landlordInvoice()->update([
                                  'landlord_invoice_approval_status' => 2 
                                ]);
                                $msg = "Send for Approval" ;
                                break;  

         case 'send-for-unapproval' :  $landlordContract->landlordInvoice()->update([
                                  'landlord_invoice_approval_status' => 3 
                                ]);
                                $msg = "Send for Unapproval" ;
                                break;  

	    case 'post' : 
			
			$response	=	Dynamics::LandlordInvoiceRegisterAxHeaderPushData('AXLandlordInvoiceHeader');
			
			if($response=='Error'){
				
					return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
					
			}	
			
			if(count($landlordContract->landlordInvoice->landlordInvoiceDimension)>0){	
			foreach($landlordContract->landlordInvoice->landlordInvoiceDimension as $key=>$item){
				$invoiceLineItem = (array) null;
				$invoiceLineItem[] = array(
							'JournalName'=>LANDLORD_INV_JOURNAL_NAME,
							'JournalNum'=>$response,
							'PaymentDate'=>$landlordContract->landlordInvoice->landlord_invoice_voucher_date->format('Y-m-d'),
							'vendAccount'=>($item->type=='VENDOR')?$landlordContract->vendorName->vendor_code:$item->accountCode->acc_code_val,
							'currency'=> CURRENCY,
							'accountType'=>trim($item->type),
							'paymentMethod'=>'',
							'Description'=>$landlordContract->landlordInvoice->landlord_invoice_desc,
							'documentNo'=>$landlordContract->landlordInvoice->landlord_given_invoice_no,
							'checkBookid' =>'',
							'AmountCredit' =>floatval($item->credit_amount),
							'AmountDebit'=>floatval($item->debit_amount),
							'voucher'=>$landlordContract->landlordInvoice->landlord_invoice_voucher_no,
							'Invoice'=>$landlordContract->landlordInvoice->landlord_invoice_voucher_no,
							'Remarks'=>$landlordContract->landlordInvoice->landlord_invoice_desc,
							'dimension1'=> 'Building',
							'dimension1value'=>isset($landlordContract->buildingInfo->building_code)?$landlordContract->buildingInfo->building_code:'000',
							'dimension2'=> 'Division',
							'dimension2value'=>isset($landlordContract->buildingInfo->ax_division)?$landlordContract->buildingInfo->ax_division:'02',
							'dimension3'=> 'Employee',
							'dimension3value'=>'00000',
							'dimension4'=> 'Location',
							'dimension4value'=>'00',
							'dimension5'=> 'Projects',
							'dimension5value'=>'00',
							'DataAreaId'=>DATA_AREA_ID,
							'company'=>COMPANY,
						);
					$result = Dynamics::LandlordInvoiceRegisterAxLineItemPushData('AXLandlordLineItem', $invoiceLineItem);
					if(count($invoiceLineItem) >0 && $result =='Error'){
							return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
					}
				}
	 
				
				
			}
	
			$landlordContract->landlordInvoice()->update([
										'landlord_invoice_approval_status' => 4 ,
										'landlord_invoice_status' => 3,
										'ax_batch_id'=>$response,
										'ax_invoice_no'=>'$landlordContract->landlordInvoice->landlord_invoice_voucher_no',
										'landlord_invoice_posted_by'=>\Auth::user()->id,
										'landlord_invoice_posted_date'=>date('Y-m-d')
                                ]);
			$msg = "Invoice Posted" ;
			break;  


         case 'approve' :   $landlordContract->landlordInvoice()->update([
                          'landlord_invoice_approval_status' => 4,
                          'landlord_invoice_status' => 2
                        ]);
                         $msg = "Invoice Approved" ;
                      break;

         case 'unapprove' :   $landlordContract->landlordInvoice()->update([
                          'landlord_invoice_approval_status' => 1,
                          'landlord_invoice_status' => 4
                        ]);
                        $msg = "Invoice Unapproved" ;
                        break; 

         case 'reject' :   $msg = ($landlordContract->landlordInvoice->landlord_invoice_approval_status == 2) ?  'Invoice Approval Rejected' :  'Invoice Unapproval Rejected';

                        $landlordContract->landlordInvoice()->update([
                          'landlord_invoice_approval_status' => 5                         
                        ]);
                        break; 
            

      }

      session()->flash('success', $msg);
      return redirect()->back();      

    }



}
