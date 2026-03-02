<?php

namespace Modules\BackOffice\Http\Controllers;
use Modules\Sales\Entities\TenantContract;
use Modules\Sales\Entities\Tenant;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Unit;
use Modules\BackOffice\Entities\Invoice;
use Modules\BackOffice\Entities\TenantInvoiceDimension;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\AccountParams;
use Modules\BackOffice\Entities\DimDetail;
use Modules\Masters\Entities\Legal;
use DB;
use Session;
use URL;
use Route;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\Masters\Emails\LegalEmail;
use Dynamics;
use Modules\BackOffice\Entities\InvoiceSequenceConfig;

class InvoiceController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');    
    $this->middleware('permission:invoice_generation', ['only' => ['show']]);
    $this->middleware('permission:invoice_generation_view', ['only' => ['tenantRentInvoiceDetails']]);
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
    public function show($contract_id)
    {
		
      $tenantContract = TenantContract::where('id',$contract_id)->orderBy('id', 'desc')->first();
      $acc_code_dr    = null;
	  $acc_code_cr 	= null;

	  
      if(empty($tenantContract->invoice_check) && count($tenantContract->tenantInvoiceList) ==0){

        $invoiceList    = $this->contractInvoiceCalculation($tenantContract->tenant_contract_effective_date, $tenantContract->tenant_contract_valid_to_date, $tenantContract->tenant_contract_rent);

        // print_r(json_encode($invoiceList));exit();

       // dd($invoiceList);

        $acc_parameter  = AccountParams::where('acc_params_tran_desc','customer_rent_invoice')->first();

            // Debit Amount
        //if($acc_parameter->acc_params_dr_acc)
        //  $acc_code_dr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_dr_acc)->first();
        
            // Credit Amount
        if($acc_parameter->acc_params_cr_acc)
          $acc_code_cr    = AccountCodes::where('acc_code_val',$acc_parameter->acc_params_cr_acc)->first();
        
         // Agreement Invoice insertion as loop 
        foreach($invoiceList as $loopItem){
         
          $invoiceId = Invoice::create(['tenant_contract_id' => $contract_id,
                                     'tenant_invoice_type' => 1, // Rent
                                     'tenant_invoice_no' => $loopItem['inv_no'],
                                     'tenant_invoice_date' => $loopItem['inv_date'],
                                     'tenant_invoice_amt' => $loopItem['rent'],
                                     'ax_batch_id' => null,
                                     'ax_invoice_no' => null,
                                     'tenant_invoice_posted_date' => null,
                                     'tenant_invoice_posted_by' => null,
                                     'tenant_invoice_cancelled_date' => null,
                                     'tenant_invoice_desc' => $tenantContract->building->building_code.'@'.$tenantContract->unit->unit_no.'-'.$loopItem['month'],  
                                     'tenant_invoice_status' => 1, // Active
                                     'created_by' => \Auth::user()->id,
                                     
                                     ]);        
       
      
                 //$invoiceNo = str_pad($invoiceId->id,4,'0',STR_PAD_LEFT);
                // $invoiceId->update(['tenant_invoice_no' => $prefix.$invoiceNo]);
                 //$invoiceId->save();


        		if($acc_code_dr !=null){
        			// Debit Insertation
        			TenantInvoiceDimension::create(['invoice_id' => $invoiceId->id,
        				'dim1' =>($acc_code_dr->dim1)?$acc_code_dr->dim1:'',
        				'dim2' =>($acc_code_dr->dim2)?$acc_code_dr->dim2:'',
        				'dim3' =>($acc_code_dr->dim3)?$acc_code_dr->dim3:'',
        				'dim4' =>($acc_code_dr->dim4)?$acc_code_dr->dim4:'',
        				'dim5' =>($acc_code_dr->dim5)?$acc_code_dr->dim5:'',
        				'debit_amount' =>$loopItem['rent'],
        				'credit_amount' =>0,
        				'ac_codes_id' =>$acc_parameter->id,
        				'acc_code_no' =>$acc_parameter->acc_params_dr_acc,
        				'acc_code_desc'=>$acc_code_dr->acc_code_desc,
        				'dimension_type' =>($acc_parameter->acc_params_dr_type)?trim($acc_parameter->acc_params_dr_type):'',
        				'created_by' => \Auth::user()->id,
        			]);
        		}
		
        		if($acc_code_cr !=null){
                            // Credit Insertation
        			TenantInvoiceDimension::create(['invoice_id' => $invoiceId->id,
        				'dim1' =>($acc_code_cr->dim1)?$acc_code_cr->dim1:'',
        				'dim2' =>($acc_code_cr->dim2)?$acc_code_cr->dim2:'',
        				'dim3' =>($acc_code_cr->dim3)?$acc_code_cr->dim3:'',
        				'dim4' =>($acc_code_cr->dim4)?$acc_code_cr->dim4:'',
        				'dim5' =>($acc_code_cr->dim5)?$acc_code_cr->dim5:'',
        				'debit_amount' =>0,
        				'credit_amount' =>$loopItem['rent'],
        				'ac_codes_id' =>$acc_parameter->id,
        				'acc_code_no' =>$acc_parameter->acc_params_cr_acc,
        				'acc_code_desc'=>$acc_code_cr->acc_code_desc,
        				'dimension_type' =>($acc_parameter->acc_params_cr_type)?trim($acc_parameter->acc_params_cr_type):'',
        				'created_by' => \Auth::user()->id,
        			]);
        		}
        }



        TenantContract::where('id',$contract_id)->where('tenant_contract_status', 1)->update(['invoice_check' => 1]);
      }

      $invoiceList = Invoice::where('tenant_contract_id',$contract_id)->orderBy('tenant_invoice_date','ASC')->get();

     
        // If contract is approved
       if($tenantContract->tenant_contract_status == 1){
         
         return view('backoffice::Invoice.contract_invoices',compact('tenantContract','invoiceList'));
       }
       else{
             // Contract in-progress
         return redirect()->route('tenant-contract.index');
       }
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

    public function tenantRentInvoiceDetails($invoice_id){
        //dd($invoice_id);
      $invoiceDetails = Invoice::where('id',$invoice_id)->orderBy('id', 'desc')->first();
      $acc_parameter  = AccountParams::where('acc_params_tran_desc','customer_rent_invoice')->first();

      $division    = DimDetail::where('dim_name','DIVISION')->where('dim_code',$acc_parameter->acc_params_divison)->first();
      
      
      return view('backoffice::Invoice.invoice_view',compact('invoiceDetails','division','acc_parameter'));

    }
	// Invoice generation
    public function contractInvoiceCalculation($date1, $date2, $rent){

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

      //echo "First Date".$firstDate ."--".strtotime($date1); 
      //echo "Last Date".$lastDate ."--".strtotime($date2); 

      // exit;

     // print_r("First Date".$firstDate ."--".strtotime($date1));exit();
      $sumOfStartDays = 0;
      $sumOfEndDays = 0;
      $sumOfSameStartEnd = 0;
      $monthIsOne = 1;
      $sumOfMonthRent = 0;

      $startDate->setTimestamp(strtotime($date1));
      $endDate->setTimestamp(strtotime($date2));
      
      // First and last date eg)01-01-2019 and 31-01-2019
      if($firstDate === strtotime($date1) && $lastDate === strtotime($date2) && $explodeStartDtValue[1] == $explodeEndDtValue[1] && $explodeStartDtValue[0] == $explodeEndDtValue[0]){
        
        $findSequence = $this->invoiceSequenceNoExist(date('y',strtotime($date1)));  
        if(isset($findSequence->year)){

          $yearSquenceNo = $findSequence;

        }  
        else{
          $yearSquenceNo = $this->generateNewYearInvoiceSequenceNo(date('y',strtotime($date1)));  
        }

        $sumOfMonthRent         = round($monthIsOne * $rent);
        $res[$inc]['inv_no']    = trim($yearSquenceNo->prefix_value).$yearSquenceNo->year.str_pad($yearSquenceNo->start_sequence,5,'0',STR_PAD_LEFT);
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
          $findSequence = $this->invoiceSequenceNoExist(date('y',strtotime($date1)));  
          if(isset($findSequence->year)){

            $yearSquenceNo = $findSequence;

          }  
          else{
            $yearSquenceNo = $this->generateNewYearInvoiceSequenceNo(date('y',strtotime($date1)));  
          }

          $res[$inc]['inv_no']    = trim($yearSquenceNo->prefix_value).$yearSquenceNo->year.str_pad($yearSquenceNo->start_sequence,5,'0',STR_PAD_LEFT);
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
              
                $startDays = 1;
              
            else  
                $startDays = 30 - $explodeStartDtValue[2] + 1;
			
            //If start from month first day consider as full month rent
            if($explodeStartDtValue[2]==1)
                $sumOfStartDays     = round($rent);
            else
                $sumOfStartDays     = round(($rent/30) * $startDays);

            $findSequence = $this->invoiceSequenceNoExist(date('y',strtotime($date1)));  
            if(isset($findSequence->year)){

              $yearSquenceNo = $findSequence;

            }  
            else{
              $yearSquenceNo = $this->generateNewYearInvoiceSequenceNo(date('y',strtotime($date1)));  
            }
            
            $res[$inc]['inv_no']    = trim($yearSquenceNo->prefix_value).$yearSquenceNo->year.str_pad($yearSquenceNo->start_sequence,5,'0',STR_PAD_LEFT);
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
      
	    $explodeNextStartDt  = explode("-",$nextStartDt);
      $explodePrevEndDt    = explode("-",$prevEndDt);
	  
      $nextStartDt_noOfDaysEnd    = date('t', strtotime($nextStartDt)); 
      if($nextStartDt_noOfDaysEnd > 30){
        $startDateNew   =   new \DateTime($explodeNextStartDt[0].'-'.$explodeNextStartDt[1].'-02');
      }else{
        $startDateNew   =   new \DateTime($nextStartDt);
      }

      $endDateNew     =   new \DateTime($explodePrevEndDt[0].'-'.$explodePrevEndDt[1].'-30');

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
        // $monthIsOne--;
        // print_r(json_encode($monthIsOne));exit();
        }
        for($i=0; $i<$monthIsOne; $i++){
         
         if($month > 12){
            $month = '01';
            $year++;
            
            $findSequence = $this->invoiceSequenceNoExist(substr($year, -2));  
            
            if(isset($findSequence->year)){

              $yearSquenceNo = $findSequence;

            }  
            else{
              $yearSquenceNo = $this->generateNewYearInvoiceSequenceNo(substr($year, -2));  
            }
            
            $res[$inc]['inv_no']    = trim($yearSquenceNo->prefix_value).$yearSquenceNo->year.str_pad($yearSquenceNo->start_sequence,5,'0',STR_PAD_LEFT);
            $res[$inc]['inv_date']  = $year.'-'.$month.'-'.'01';
            $res[$inc]['month']     = $month.'/'.$year;
            $res[$inc]['rent']      = number_format(round($rent), 3, '.', '');
            
          
          }
          else{

            $findSequence = $this->invoiceSequenceNoExist(substr($year, -2));  
            if(isset($findSequence->year)){

              $yearSquenceNo = $findSequence;

            }  
            else{
              $yearSquenceNo = $this->generateNewYearInvoiceSequenceNo(substr($year, -2));  
            }
            
            $res[$inc]['inv_no']    = trim($yearSquenceNo->prefix_value).$yearSquenceNo->year.str_pad($yearSquenceNo->start_sequence,5,'0',STR_PAD_LEFT);
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
    
    if(sizeof($lastMonth)>0){
      
      $findSequence = $this->invoiceSequenceNoExist(date('y',strtotime($lastMonth['inv_date'])));  
      
      if(isset($findSequence->year)){

        $yearSquenceNo = $findSequence;

      }  
      else{
        $yearSquenceNo = $this->generateNewYearInvoiceSequenceNo(substr($lastMonth['inv_date'], -2));  
      }
      
      $lastMonth['inv_no']    = trim($yearSquenceNo->prefix_value).$yearSquenceNo->year.str_pad($yearSquenceNo->start_sequence,5,'0',STR_PAD_LEFT);
      
    }
    array_push($res, $lastMonth);
    
    //dd($res);
    return array_filter($res);
   }
   public function tenantInvoicePosting($invoice_id){
    $nowUrl = url()->previous();
	$invoiceInfo 	= Invoice::where('id',$invoice_id)->first();
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
		
		foreach($invoiceInfo->tenantInvoiceDimensionInfo as $key=>$item){
			$invoiceLineItem[] = array(
						'recid'=>$response,
						'Amount'=>$invoiceInfo->tenant_invoice_amt ,
						'unitprice'=> $invoiceInfo->tenant_invoice_amt,
						'quantity'=>"1",
						'account'=>strval($item->acc_code_no),
						'InvoiceDate' =>$invoiceInfo->tenant_invoice_date->format('Y-m-d'),
						'Description' =>$invoiceInfo->tenant_invoice_desc,
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
		if(count($invoiceLineItem) >0 && $result=='Error'){
		
				return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
		}
		$invoiceLineItem = (array) null;
	}
    Invoice::where('id',$invoice_id)->update(['tenant_invoice_status'=>3,'ax_batch_id'=>$response,'ax_invoice_no'=>$invoiceInfo->tenant_invoice_no,'tenant_invoice_posted_date'=>date("Y-m-d"),'tenant_invoice_posted_by' => \Auth::user()->id]);

     //Notifications starts
     $invoice = Invoice::find($invoice_id);
    $tenantContract = TenantContract::where('id',$invoice->tenant_contract_id)->first();
    $legal = Legal::where('tenant_contract_id',$invoice->tenant_contract_id)->first();

          if($tenantContract->status == 5){
            $legalUsers = User::role(['are','backoffice_manager','legal_advisor'])->get(); 
            $legalUsers = array_flatten($legalUsers);

            $legal->subject = "Rental Income Posted !"; 
            $legal->textContent = "Rental Income Posted having Invoice No: ".$invoice->tenant_invoice_no." which is under Legal";
            foreach($legalUsers as $legalUser){
              $mobile = $legalUser->employee->employee_contact_no ?? $legalUser->employee->employee_secondary_no;

              $msg = "Rental Income Posted";
              $params = 'optional data';
              if(!empty($legalUser->email)){
      Mail::to($legalUser->email)->send(new LegalEmail($legal,$legalUser)); //Email Notification
    }else{
       sendSms($mobile,$msg,$params); //SMS Notification
     }

   }
 }

  //Notifications ends

    session()->flash('success', 'Rental Income Posted Successfully');
    return redirect($nowUrl);

    
  }
  /*  
  *   configuartion increment sequence value update 
  */  
  public function invoiceSequenceNoExist($year){  

    $yearSquenceNo = InvoiceSequenceConfig::where('year',$year)->first();
    //if the sequence exist  inrement sequence no
    if(isset($yearSquenceNo->start_sequence)){ 
      InvoiceSequenceConfig::where('year',$year)->update(['start_sequence'=> DB::raw('start_sequence + 1') 
      ]); 
    }

    return $yearSquenceNo;
  }
  /*  
  *   Generate new year sequence no
  */  
  public function generateNewYearInvoiceSequenceNo($year){  

    $yearSquenceNo = InvoiceSequenceConfig::create([
        'year'=> $year,
        'start_sequence'=>1,
        'prefix_value'=>INV_PREFIX
    ]); 
    
    return $yearSquenceNo;
  }
}
