<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Modules\Maintenance\Entities\MaintenanceInvoice;
use Modules\Maintenance\Entities\MaintenanceInvoiceDetails;
use Modules\Masters\Entities\Vendor;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\DimDetail;
use Modules\BackOffice\Entities\AccountParams;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Unit;
use Modules\BackOffice\Entities\GeneralLedger;
use Modules\BackOffice\Entities\ExpenseHead;
use Modules\BackOffice\Http\Controllers\GeneralLedgerController;
use Modules\Sales\Entities\LandlordContract;
use Modules\Maintenance\Entities\ComplaintServiceReport;
use Illuminate\Support\Arr;
use App\Setting;
use DB;
use Dynamics;
class MaintenanceInvoiceController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');  
    // $this->middleware('permission:landlord_payment_add', ['only' => ['create','store']]);
    // $this->middleware('permission:landlord_payment_edit', ['only' => ['edit','update']]); 
    // $this->middleware('permission:landlord_payment_view', ['only' => ['index','show']]); 

    $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
  }

    /**
     * Display a listing of the resource.
     * @return Response
     */
  public function index(Request $request)
  {
    $maintenanceInvoices =  MaintenanceInvoice::filter($request)
                                                ->sortable()
                                                ->paginate($this->noOfRecord);
      $request->flash();

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

      $route =  $request->url();       
      
      if(isset($request->ajax)) 
      return view('maintenance::Invoice.invoice_list_ajax',compact('maintenanceInvoices','request','route'));

      return view('maintenance::Invoice.invoice_list',compact('maintenanceInvoices','enquiry_fields','operations','route'));
    }

    /*
    *
    *
    */
    public function maintenanceInvoiceFilter(Request $request){

     if(isset($request->search_form)){

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

      return view('maintenance::enquiry_filter',compact('enquiry_fields','operations'));
     }

     $maintenanceInvoices =  MaintenanceInvoice::filter($request)->sortable()->paginate(10);
     $request->flash();
     $ajax = true;

     return view('maintenance::Invoice.invoice_list_ajax',compact('maintenanceInvoices','ajax'));

   }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
    
      $generateCode = $this->maintenanceInvoiceCode();
      $nextCode = $generateCode['code'];
	
	  $year    = prefixData('maintenance_invoice_prefix')->configuration_year;
      $isYearCorrect = (date('y') == $year)?true:false;
	  
      $vendors = Vendor::active()->where('vendor_type_id',1)->orWhere('vendor_name','inhouse')->get();
      $accountCodes = AccountCodes::get();
      $buildings = Building::active()->get();
      $units = Unit::active()->where('building_id',$buildings->first()->id)->get();
      $technicians = \App\User::with('employee')->role('technician')->get();  //dd($technician);

      $first_account_code = $accountCodes->first();

      $first_account_dim1 = $first_account_code->dim1;
      $first_account_dim2 = $first_account_code->dim2;

      if($first_account_dim1 == 'DIVISION')
       $dim1 = DimDetail::get();   


     if($first_account_dim2 == 'BUILDING')
       $dim2 = Building::active()->get();

     $expenseAccountCodes = ExpenseHead::get();


//dd($expenseAccountCodes);
     return view('maintenance::Invoice.add_invoice',
       compact('nextCode','vendors','accountCodes','buildings','units','technicians',
        'dim1','dim2','expenseAccountCodes','isYearCorrect'));
   }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {

      $this->validate($request, [
        'maintenance_invoice_date' => 'required|date',
        'vendor_id'   => 'required',
        'maintenance_invoice_refer_no'   => 'required',        
        'maintenance_invoice_refer_amt'   => 'required',
        'ac_codes_id.*' => 'required', 
        'building.*' => 'required', 
        ]);

    try{
      $generateCode = $this->maintenanceInvoiceCode();
      $nextCode = $generateCode['code'];
      $user = \Auth::user();
      
      $maintenanceInvoice =  MaintenanceInvoice::create([
        'maintenance_invoice_no' => $nextCode,
        'maintenance_invoice_date' => $request->maintenance_invoice_date,
        'vendor_id' => $request->vendor_id,
        'maintenance_invoice_desc' => $request->maintenance_invoice_desc,
        'maintenance_invoice_refer_no' => $request->maintenance_invoice_refer_no,
        'maintenance_invoice_refer_amt' => replaceCommaWithDot($request->maintenance_invoice_refer_amt),
        'maintenance_invoice_payment_method' => isset($request->maintenance_invoice_payment_method)?$request->maintenance_invoice_payment_method:null,
        'maintenance_invoice_comment' => $request->maintenance_invoice_comment,
        'ax_batch_id' => $request->ax_batch_id,
        'ax_invoice_no' => $request->ax_invoice_no,
        'created_by' => \Auth::user()->id,
        'maintenance_invoice_status' => ($user->can('approve_maintenance_invoice'))? 2 : 1,
        'maintenance_invoice_approval_status' => ($user->can('approve_maintenance_invoice'))? 4 : 0,
        ]);
  
    Setting::where('configuration_settings','maintenance_invoice_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);
    
      $general_ledger = array();
      $ledger_building = array();
      $debit_key = array();
      $building_amt = array();
      $user = \Auth::user();

  if(count($request->ac_codes_id) > 0){  // echo 'dasdsad'     ;
  
    foreach ($request->ac_codes_id as $key => $value) {
      if(isset($value) && (isset($request->debit_amt[$key]) || isset($request->credit_amt[$key]))) {    
        
        $account_code  =   AccountCodes::find($value);  
        $code_desc =     (isset($account_code->acc_code_desc))? $account_code->acc_code_desc: '';

        if(!isset($account_code->acc_code_desc)){
          $account_code  =   AccountCodes::where('acc_code_index',$value)->get();
          $value = $account_code[0]->id;
          $code_desc = $account_code[0]->acc_code_desc;
        }

        
        
        $maintenanceInvoice->maintenanceInvoiceDetails()->create([
          'ac_codes_id' => $value,
          'description' => $code_desc,
          'building_id' => $request->building[$key],
          'unit_id' => $request->unit[$key],
          'invoice_desc' => (isset($request->invoice_desc[$key]))? $request->invoice_desc[$key]: NULL,
          'material_charge' => (isset($request->material_charge[$key]))? replaceCommaWithDot($request->material_charge[$key]) : NULL,
          'labour_charge' => (isset($request->labour_charge[$key]))? replaceCommaWithDot($request->labour_charge[$key]) : NULL,
          'debit_amt' => (isset($request->debit_amt[$key]))? replaceCommaWithDot($request->debit_amt[$key]) : NULL,
          'credit_amt' => (isset($request->credit_amt[$key]))? replaceCommaWithDot($request->credit_amt[$key]) : NULL,
          'technician_recovery' => (isset($request->technician_recovery[$key]))? $request->technician_recovery[$key]: 0,
          'technician_id' => (isset($request->technician_id[$key]))? $request->technician_id[$key]: NULL,
          'dim1able_type' => (isset($request->dim1[$key]))? 'Modules\BackOffice\Entities\DimDetail' : NULL,
          'dim1able_id' => isset($request->dim1[$key])? $request->dim1[$key] : NULL, 
          'dim2able_type' => (isset($request->dim2[$key]))? 'Modules\Masters\Entities\Building': NULL,
          'dim2able_id' => (isset($request->dim2[$key]))? $request->dim2[$key] : NULL 
          ]);
        
        
        if(isset($request->technician_recovery[$key]) && $request->technician_recovery[$key] == 1)
         $general_ledger[] =  $key;  
     }

    } 


    
  if(count($general_ledger) > 0){

    foreach($general_ledger as $ledger_key => $ledger_val){

       $ledger_build    =  $request->building[$ledger_key];
       $ledger_unit     =  isset($request->unit[$ledger_key])?$request->unit[$ledger_key]:null;
       $ledger_acc_code =  $request->ac_codes_id[$ledger_key];

      if(array_search($ledger_build, $ledger_building)!== false &&
        array_search($ledger_acc_code, $ledger_acc)!== false ){
        
        
        $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_dr'] += (float)$request->debit_amt[$ledger_key];
        $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_cr'] += (float)$request->credit_amt[$ledger_key];
        $building_amt[$ledger_build][$ledger_build.'_total'] += (float)$request->debit_amt[$ledger_key];

      }
      elseif(array_search($ledger_build, $ledger_building)!== false){

          // Check Account code exist in this building Id
          if(!in_array($ledger_acc_code, $ledger_building_arr[$ledger_build]))
            $ledger_building_arr[$ledger_build][] = $ledger_acc_code;
          $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_building'] = $ledger_build;
          $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_unit'] = $request->unit[$ledger_key];
          if(isset($building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_dr']))
            $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_dr'] += (float)$request->debit_amt[$ledger_key];
          else
            $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_dr'] = (float)$request->debit_amt[$ledger_key];
          if(isset($building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_cr']))
            $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_cr'] += (float)$request->credit_amt[$ledger_key];
          else
            $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_cr'] = (float)$request->credit_amt[$ledger_key];

          $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_dim1'] = $request->dim1[$ledger_key];
          $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_acc'] = $request->ac_codes_id[$ledger_key];
          if(isset($building_amt[$ledger_build][$ledger_build.'_total']))
            $building_amt[$ledger_build][$ledger_build.'_total'] += (float)$request->debit_amt[$ledger_key];
          else
            $building_amt[$ledger_build][$ledger_build.'_total'] = (float)$request->debit_amt[$ledger_key];

      }else{
        $ledger_building[] = $ledger_build;
        $ledger_acc[] = $ledger_acc_code;
        if(!empty($ledger_unit))
          $ledger_build_unit[] = $ledger_unit;
        else
          $ledger_unit = 0; 

        $ledger_building_arr[$ledger_build][] = $ledger_acc_code;

        $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_building'] = $ledger_build;
        $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_unit'] = $request->unit[$ledger_key];
        $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_dr'] = (float)$request->debit_amt[$ledger_key];
        $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_cr'] = (float)$request->credit_amt[$ledger_key];
        $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_dim1'] = $request->dim1[$ledger_key];
        $building_amt[$ledger_build][$ledger_acc_code.$ledger_build.'_acc'] = $request->ac_codes_id[$ledger_key];
        $building_amt[$ledger_build][$ledger_build.'_total'] = (float)$request->debit_amt[$ledger_key];

      }           
       
    }
   
    $generalLedgerController = new  GeneralLedgerController();  
    foreach($ledger_building_arr as $ledger_building_key => $ledger_building_val){
      foreach($ledger_building_val as $val){
         $nextCodeGeneralLedger = $generalLedgerController->generalLedgerCode();
        
         $management_type = LandlordContract::where('building_id',$building_amt[$ledger_building_key][$val.$ledger_building_key.'_building'])->first();

          if($management_type)                                     
            $management_type = ($management_type)? $management_type->management_id : 0;
        
         if($management_type == 1){
            $acc_params =  AccountParams::where('acc_params_tran_desc','=',AX_GEN_LDGR_VIA_MAINT_INV_COMP)->first(); 
            $account_dr_id = $acc_params->acc_params_dr_acc;

            $acc_params_cr =  AccountCodes::where('id','=',$building_amt[$ledger_building_key][$val.$ledger_building_key.'_acc'])->first(); 
            $account_cr_desc = $acc_params_cr->acc_code_desc;
            
          }else{
            $acc_params =  AccountParams::where('acc_params_tran_desc','=',AX_GEN_LDGR_VIA_MAINT_INV_NOR)->first(); 
            $account_dr_id = $acc_params->acc_params_dr_acc;      
            $acc_params_cr =  AccountCodes::where('id','=',$building_amt[$ledger_building_key][$val.$ledger_building_key.'_acc'])->first(); 
            $account_cr_desc = $acc_params_cr->acc_code_desc;    
          }


          $account_code_dr  =   AccountCodes::where('acc_code_val',$account_dr_id)->first();   
          $dimArr =array();
          if(!in_array($ledger_building_key,$debit_key)){

            $dimArr[] =array('account_id' => $account_code_dr->id,
                'description' => $account_code_dr->acc_code_desc,
                'building_id' => $building_amt[$ledger_building_key][$val.$ledger_building_key.'_building'],
                'unit_id' => $building_amt[$ledger_building_key][$val.$ledger_building_key.'_unit'],
                'jv_desc' =>  NULL,
                'debit_amt' => $building_amt[$ledger_building_key][$ledger_building_key.'_total'],
                'credit_amt' => 0.0,
                'recovery' => 1,
                'dim1able_type' => 'Modules\BackOffice\Entities\DimDetail',
                'dim1able_id' => ($building_amt[$ledger_building_key][$val.$ledger_building_key.'_dim1']=='HO')?'01':'02', 
                'dim2able_type' => 'Modules\Masters\Entities\Building',
                'dim2able_id' =>  $building_amt[$ledger_building_key][$val.$ledger_building_key.'_building'] );
          }
          $dimArr[] = array('account_id' =>$building_amt[$ledger_building_key][$val.$ledger_building_key.'_acc'],
                'description' =>$account_cr_desc,
                'building_id' => $building_amt[$ledger_building_key][$val.$ledger_building_key.'_building'],
                'unit_id' => $building_amt[$ledger_building_key][$val.$ledger_building_key.'_unit'],
                'jv_desc' =>  NULL,
                'debit_amt' => 0.0,
                'credit_amt' => $building_amt[$ledger_building_key][$val.$ledger_building_key.'_dr'],
                'recovery' => 1,
                'dim1able_type' => 'Modules\BackOffice\Entities\DimDetail',
                'dim1able_id' => ($building_amt[$ledger_building_key][$val.$ledger_building_key.'_dim1']=='HO')?'01':'02', 
                'dim2able_type' => 'Modules\Masters\Entities\Building',
                'dim2able_id' => $building_amt[$ledger_building_key][$val.$ledger_building_key.'_building'] );

          if(!in_array($ledger_building_key,$debit_key)){
            $generalLedger =  GeneralLedger::create([
              'voucher_no' => $nextCodeGeneralLedger['code'],
              'general_ledger_type' => 3,
              'jv_refer_no' => $request->maintenance_invoice_refer_no,
              'doc_date' => date('Y-m-d',strtotime(today())) ,
              //    'jv_amount' => $request->maintenance_invoice_refer_amt,
              'general_ledger_desc' => 'Recovery',
              'bank_id' =>  NULL,
              'amount' => '0.000',
              'agreement_no' => '',            
              'created_by' => \Auth::user()->id,
              'general_ledger_status' => ($user->can('ledger_approval'))? 2 : 1,
              'general_ledger_approval_status' => ($user->can('ledger_approval'))? 4 : 0,
              'maintenance_id' => $maintenanceInvoice->id
            ]);
             Setting::where('configuration_settings','general_ledger_prefix')->update(['configuration_increment_value'=> $nextCodeGeneralLedger['inc'] + 1
             ]);
             $debit_key[] = $ledger_building_key;
          }
          $generalLedger->generalLedgerDim()->createMany($dimArr);
    
      }
    } 

    session()->flash('success', 'Maintenance Invoice & GeneralLedger Added');
  }
  else
    session()->flash('success', 'Maintenance Invoice Added');

  }
  return redirect()->route('maintenanceInvoice.index');

    }
    catch (\Exception $e) {
     return $e->getMessage();
    }

      

}

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show(MaintenanceInvoice $maintenanceInvoice)
    {
 
     return view('maintenance::Invoice.view_invoice',compact('maintenanceInvoice')); 

   }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit(MaintenanceInvoice $maintenanceInvoice)
    {

      $vendors = Vendor::active()->where('vendor_type_id',1)->orWhere('vendor_name','inhouse')->get();
      $accountCodes = AccountCodes::get();
      $buildings = Building::active()->get();
      $units = Unit::active()->where('building_id',$buildings->first()->id)->get();
      $technicians = \App\User::with('employee')->role('technician')->get();  //dd($technician);

      $first_account_code = $accountCodes->first();

      $first_account_dim1 = $first_account_code->dim1;
      $first_account_dim2 = $first_account_code->dim2;

      if($first_account_dim1 == 'DIVISION')
       $dim1 = DimDetail::get();   


     if($first_account_dim2 == 'BUILDING')
       $dim2 = Building::active()->get();

     $expenseAccountCodes = ExpenseHead::get();

     $creditAmount = maintenanceInvoiceDetails::where('maintenance_invoice_id',$maintenanceInvoice->id)->select(DB::raw('sum(cast(credit_amt as double precision)) AS credit'))->first();
     //dd($creditAmount);
     $debitAmount = maintenanceInvoiceDetails::where('maintenance_invoice_id',$maintenanceInvoice->id)->select(DB::raw('sum(cast(debit_amt as double precision)) AS debit'))->first();
	// Alert user that year is not match with today year change configuration
     $year    = prefixData('maintenance_invoice_prefix')->configuration_year;
     $isYearCorrect = (date('y') == $year)?true:false;
     return view('maintenance::Invoice.add_invoice',
       compact('maintenanceInvoice','vendors','accountCodes','buildings','units','technicians',
        'dim1','dim2','expenseAccountCodes','creditAmount','debitAmount','isYearCorrect'));
   }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, MaintenanceInvoice $maintenanceInvoice)
    {
      $this->validate($request, [
        'maintenance_invoice_date' => 'required|date',
        'vendor_id'   => 'required',
        'maintenance_invoice_refer_no'   => 'required',        
        'maintenance_invoice_refer_amt'   => 'required',
        'ac_codes_id.*' => 'required', 
        'building.*' => 'required', 
        ]);

      $maintenanceInvoice->update([          
        'maintenance_invoice_date' => $request->maintenance_invoice_date,
        'vendor_id' => $request->vendor_id,
        'maintenance_invoice_desc' => $request->maintenance_invoice_desc,
        'maintenance_invoice_refer_no' => $request->maintenance_invoice_refer_no,
        'maintenance_invoice_refer_amt' => replaceCommaWithDot($request->maintenance_invoice_refer_amt),
        'maintenance_invoice_payment_method' => isset($request->maintenance_invoice_payment_method)?$request->maintenance_invoice_payment_method:null,
        'maintenance_invoice_comment' => $request->maintenance_invoice_comment,
        'ax_batch_id' => $request->ax_batch_id,
        'ax_invoice_no' => $request->ax_invoice_no,
        'updated_by' => \Auth::user()->id,
        ]);

       //  dd($request);

      $general_ledger = array();
      $ledger_building = array();
      $building_amt = array();
      $user = \Auth::user();

        if(count($request->ac_codes_id) > 0){  // echo 'dasdsad'     ;

        $maintenanceInvoice->maintenanceInvoiceDetails()->delete();

        foreach ($request->ac_codes_id as $key => $value) {

          $account_code  =   AccountCodes::find($value);           
          $maintenanceInvoice->maintenanceInvoiceDetails()->create([
            'ac_codes_id' => $value,
            'description' => $account_code->acc_code_desc,
            'building_id' => $request->building[$key],
            'unit_id' => $request->unit[$key],
            'invoice_desc' => isset($request->invoice_desc[$key])? $request->invoice_desc[$key]: NULL,
            'material_charge' => isset($request->material_charge[$key])? replaceCommaWithDot($request->material_charge[$key]) : NULL,
            'labour_charge' => isset($request->labour_charge[$key])? replaceCommaWithDot($request->labour_charge[$key]) : NULL,
            'debit_amt' => isset($request->debit_amt[$key])? replaceCommaWithDot($request->debit_amt[$key]) : NULL,
            'credit_amt' => isset($request->credit_amt[$key])? replaceCommaWithDot($request->credit_amt[$key]) : NULL,
            'technician_recovery' => isset($request->technician_recovery[$key])? $request->technician_recovery[$key]: 0,
            'technician_id' => isset($request->technician_id[$key])? $request->technician_id[$key]: NULL,
            'dim1able_type' => isset($request->dim1[$key])? 'Modules\BackOffice\Entities\DimDetail' : NULL,
            'dim1able_id' => isset($request->dim1[$key])? $request->dim1[$key] : NULL, 
            'dim2able_type' => isset($request->dim2[$key])? 'Modules\Masters\Entities\Building': NULL,
            'dim2able_id' => isset($request->dim2[$key])? $request->dim2[$key] : NULL 
            ]);

          if(isset($request->technician_recovery[$key]) && $request->technician_recovery[$key] == 1)
            $general_ledger[] =  $key;  




        }

/*
      if(count($general_ledger) > 0){

          foreach($general_ledger as $ledger_key => $ledger_val){

           $ledger_build =  $request->building[$ledger_key];


            if(array_search($ledger_build, $ledger_building)!== false){

              $building_amt[$ledger_build.'_dr'] += (float)$request->debit_amt[$ledger_key];
              $building_amt[$ledger_build.'_ar'] += (float)$request->credit_amt[$ledger_key];
             
            }else{
              $ledger_building[] = $ledger_build;
              $building_amt[$ledger_build.'_unit'] = $request->unit[$ledger_key];
              $building_amt[$ledger_build.'_dr'] = (float)$request->debit_amt[$ledger_key];
              $building_amt[$ledger_build.'_ar'] = (float)$request->credit_amt[$ledger_key];
              $building_amt[$ledger_build.'_dim1'] = $request->dim1[$ledger_key];

            }           

         }

      $generalLedgerController = new  GeneralLedgerController();
      $account_code_dr  =   AccountCodes::find(118);
      $account_code_cr  =   AccountCodes::find(82);


        foreach($ledger_building as $ledger_building_key => $ledger_building_val){

         $nextCodeGeneralLedger = $generalLedgerController->generalLedgerCode();
        
         $generalLedger =  GeneralLedger::create([
             'voucher_no' => $nextCodeGeneralLedger,
             'general_ledger_type' => 3,
             'jv_refer_no' => $request->maintenance_invoice_refer_no,
             'doc_date' => date('Y-m-d',strtotime(today())) ,
             'jv_amount' => $request->maintenance_invoice_refer_amt,
             'general_ledger_desc' => '',
              'bank_id' =>  NULL,
             'amount' => $request->maintenance_invoice_refer_no,
             'agreement_no' => '',            
             'created_by' => \Auth::user()->id,
             'general_ledger_status' => ($user->can('ledger_approval'))? 2 : 1,
             'general_ledger_approval_status' => ($user->can('ledger_approval'))? 4 : 0,
         ]);

          $generalLedger->generalLedgerDim()->createMany([
           [     'account_id' => 118,
                  'description' => $account_code_dr->acc_code_desc,
                  'building_id' => $ledger_building_val,
                  'unit_id' => $building_amt[$ledger_building_val.'_unit'],
                  'jv_desc' =>  NULL,
                  'debit_amt' => $building_amt[$ledger_building_val.'_dr'],
                  'credit_amt' => 0.0,
                  'recovery' => 1,
                  'dim1able_type' => 'Modules\BackOffice\Entities\DimDetail',
                  'dim1able_id' => $building_amt[$ledger_building_val.'_dim1'], 
                  'dim2able_type' => 'Modules\Masters\Entities\Building',
                  'dim2able_id' =>  $ledger_building_val            
            ],
            ['account_id' => 82,
             'description' => $account_code_cr->acc_code_desc,
             'building_id' => $ledger_building_val,
             'unit_id' => $building_amt[$ledger_building_val.'_unit'],
             'jv_desc' =>  NULL,
             'debit_amt' =>  0.0,
             'credit_amt' => $building_amt[$ledger_building_val.'_ar'],
             'recovery' => 1,
             'dim1able_type' =>  'Modules\BackOffice\Entities\DimDetail' ,
             'dim1able_id' =>  $building_amt[$ledger_building_val.'_dim1'], 
             'dim2able_type' => 'Modules\Masters\Entities\Building',
             'dim2able_id' => $ledger_building_val      
            ],
           ]
         );
 

        } 
              
      } */
    }


    session()->flash('success', 'Maintenance Invoice Updated');
    //return redirect()->back();
	return redirect()->route('maintenanceInvoice.index');
  }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(MaintenanceInvoice $maintenanceInvoice)
    {
      $maintenanceInvoice->delete();
      session()->flash('success', 'Maintenance Invoice Closed');
      return redirect()->route('maintenanceInvoice.index');
    }

/*
*  maintenance_invoice_no
*
*/
public function maintenanceInvoiceCode(){

    $prefix  = prefixData('maintenance_invoice_prefix')->configuration_value.prefixData('maintenance_invoice_prefix')->configuration_year;

    $incVal  = prefixData('maintenance_invoice_prefix')->configuration_increment_value;

    $nextCode = $prefix.str_pad($incVal,5,'0',STR_PAD_LEFT);

    return array('code'=>$nextCode,'inc'=>$incVal);  

}

     //get building unit list
public function buildingUnit(Building $building_id){        

 $building_units = Unit::where('building_id',$building_id->id)->select('id','unit_code')->get();

     //dd($building_units);
 return json_encode($building_units);    

}



   /*
   * sendToApproveUnapprove
   *
   **/
   public function sendToApproveUnapprove(MaintenanceInvoice $maintenanceInvoice){

     if($maintenanceInvoice->maintenance_invoice_status == 1){
        $maintenanceInvoice->maintenance_invoice_approval_status = 2; //Pending Approval
        session()->flash('success', 'Send for Approval'); 
      }
      else{
        $maintenanceInvoice->maintenance_invoice_approval_status = 3; //Pending Unapproval
        session()->flash('success', 'Send for Unapproval'); 
      }

      $maintenanceInvoice->save();

      return back();
    }

   /*
   *
   *  Pending for Approve / Pending for Unapprove
   *
   */ 
   public function approveInvoice(MaintenanceInvoice $maintenanceInvoice,$action){

    $message = '';

    switch($action){

       case 'approve' :   //Pending For Approval 
       if($maintenanceInvoice->maintenance_invoice_approval_status == 2 || $maintenanceInvoice->maintenance_invoice_approval_status == 1 ){

        $maintenanceInvoice->maintenance_invoice_approval_status = 4;
        $maintenanceInvoice->maintenance_invoice_status = 2;

        $message = 'Invoice Approved';

      }elseif($maintenanceInvoice->maintenance_invoice_approval_status == 3 || $maintenanceInvoice->maintenance_invoice_approval_status == 4){
                        //Pending For Unapproval 
        $maintenanceInvoice->maintenance_invoice_approval_status = 1;
        $maintenanceInvoice->maintenance_invoice_status = 4;
        $message = 'Invoice Unapproved';
      }

      break;

       case 'reject' : //Pending For Approval / Unapprove
       if(in_array($maintenanceInvoice->maintenance_invoice_approval_status,[2,3])){                          

        $message = ($maintenanceInvoice->maintenance_invoice_approval_status == 2)?   'Invoice Approval Rejected' :  'Invoice Unapproval Rejected';

        $maintenanceInvoice->maintenance_invoice_approval_status = 5;

      }
      break; 
      case 'post' : //Post        
    $invoiceLineItem = array();   
    /********************** AX PUSHING *****************************/
      $invoiceHeader = array(
        'JournalName'=> MAINTENANCE_INV_JOURNAL_NAME,
        'DataAreaId'=>DATA_AREA_ID,
        'company'=>COMPANY
      );
    
      $response = Dynamics::MaintenanceInvoiceAxHeaderPushData('AXMaintenanceInvoice', $invoiceHeader);
      
      if($response=='Error'){
        return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
      }
      if(!isset($maintenanceInvoice->maintenanceInvoiceDetails) || count($maintenanceInvoice->maintenanceInvoiceDetails) ==0){
        return redirect()->back()->with('error', 'Account Code Distribution Missing');
        
      }
    
      $invoiceLineItem[] = array(
            'JournalNum'=> $response,
            'JournalName'=> MAINTENANCE_INV_JOURNAL_NAME,
            'PaymentDate'=> $maintenanceInvoice->maintenance_invoice_date->format('Y-m-d'),
            'Description'=> $maintenanceInvoice->maintenance_invoice_desc,
            'vendAccount'=> $maintenanceInvoice->vendor->vendor_code,
            'currency'=> CURRENCY,
            'accountType'=> 'VENDOR',
            'paymentMethod'=>($maintenanceInvoice->maintenance_invoice_payment_method==1)?'Cash':'Cheque',
            'checkBookid'=>'',
            'documentNo'=> '',
            'AmountCredit'=>$maintenanceInvoice->maintenance_invoice_refer_amt,
			'voucher'=>$maintenanceInvoice->maintenance_invoice_no,
            'AmountDebit'=>0,
            'Remarks'=>$maintenanceInvoice->maintenance_invoice_comment,
            'Invoice'=>$maintenanceInvoice->maintenance_invoice_refer_no,
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
            'DataAreaId'=>DATA_AREA_ID,
            'company'=>COMPANY
          );  
        
      foreach($maintenanceInvoice->maintenanceInvoiceDetails as $key=>$item){
        
        if($item->debit_amt==0){
        $invoiceLineItem[] = array(
            'JournalNum'=> $response,
            'JournalName'=> MAINTENANCE_INV_JOURNAL_NAME,
            'PaymentDate'=> $maintenanceInvoice->maintenance_invoice_date->format('Y-m-d'),
            'Description'=> $maintenanceInvoice->maintenance_invoice_desc,
            'vendAccount'=> $item->accountCode->acc_code_val,
            'currency'=> CURRENCY,
            'accountType'=> 'LEDGER',
            'paymentMethod'=>($maintenanceInvoice->maintenance_invoice_payment_method==1)?'Cash':'Cheque',
            'checkBookid'=>'',
            'documentNo'=> '',
            'AmountCredit'=>($item->credit_amt==null)?0:$item->credit_amt,
			'voucher'=>$maintenanceInvoice->maintenance_invoice_no,
            'AmountDebit'=>($item->debit_amt==null)?0:$item->debit_amt,
            'Remarks'=>$maintenanceInvoice->maintenance_invoice_comment,
            'Invoice'=>$maintenanceInvoice->maintenance_invoice_refer_no,
            'dimension1'=> 'Building',
            'dimension1value'=>isset($item->dim2able_id)?$item->building->building_code:'000',
            'dimension2'=> 'Division',
            'dimension2value'=>isset($item->dim2able_id)?$item->building->ax_division:'02',
            'dimension3'=> 'Employee',
            'dimension3value'=>'00000',
            'dimension4'=> 'Location',
            'dimension4value'=>'00',
            'dimension5'=> 'Projects',
            'dimension5value'=>'00',
            'DataAreaId'=>DATA_AREA_ID,
            'company'=>COMPANY
          );
        }
        else{
          
          $invoiceLineItem[] = array(
            'JournalNum'=> $response,
            'JournalName'=> MAINTENANCE_INV_JOURNAL_NAME,
            'PaymentDate'=> $maintenanceInvoice->maintenance_invoice_date->format('Y-m-d'),
            'Description'=> $maintenanceInvoice->maintenance_invoice_desc,
            'vendAccount'=> $item->accountCode->acc_code_val,
            'currency'=> CURRENCY,
            'accountType'=> 'LEDGER',
            'paymentMethod'=>($maintenanceInvoice->maintenance_invoice_payment_method==1)?'Cash':'Cheque',
            'checkBookid'=>'',
            'documentNo'=> '',
            'AmountCredit'=>($item->credit_amt==null)?0:$item->credit_amt,
			'voucher'=>$maintenanceInvoice->maintenance_invoice_no,
            'AmountDebit'=>($item->debit_amt==null)?0:$item->debit_amt,
            'Remarks'=>$maintenanceInvoice->maintenance_invoice_comment,
            'Invoice'=>$maintenanceInvoice->maintenance_invoice_refer_no,
            'dimension1'=> 'Building',
            'dimension1value'=>isset($item->dim2able_id)?$item->building->building_code:'000',
            'dimension2'=> 'Division',
            'dimension2value'=>isset($item->dim2able_id)?$item->building->ax_division:'02',
            'dimension3'=> 'Employee',
            'dimension3value'=>'00000',
            'dimension4'=> 'Location',
            'dimension4value'=>'00',
            'dimension5'=> 'Projects',
            'dimension5value'=>'00',
            'DataAreaId'=>DATA_AREA_ID,
            'company'=>COMPANY
          );
          
        }
      }
		
      $result = Dynamics::MaintenanceInvoiceAxLineItemPushData('AXMaintenanceInvoice', $invoiceLineItem);
      if(count($invoiceLineItem) >0 && $result=='Error'){
            return redirect()->back()->with('error', 'Microsoft Dynamics API Service Error');
      }
      /********************** AX END *****************************/
    $message = 'Invoice Posted';
    $maintenanceInvoice->maintenance_invoice_status = 3;
    $maintenanceInvoice->ax_batch_id = $result;
    $maintenanceInvoice->ax_invoice_no = $maintenanceInvoice->maintenance_invoice_no;
	$maintenanceInvoice->maintenance_invoice_posted_by =\Auth::user()->id;
	$maintenanceInvoice->maintenance_invoice_posted_date = date('Y-m-d');
      break;
    }

    $maintenanceInvoice->save();    


    session()->flash('success', $message);
    return back();


  }



  public function maintenanceInvoiceApproval(Request $request){

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

    $maintenanceInvoices =  MaintenanceInvoice::approval()
                                                ->filter($request)
                                                ->sortable()
                                                ->paginate($this->noOfRecord);

    $route =  $request->url();                                           

    if(isset($request->ajax)) 
    return view('maintenance::Invoice.invoice_list_ajax',compact('maintenanceInvoices','request','route'));

    return view('maintenance::Invoice.invoice_list',compact('maintenanceInvoices','enquiry_fields','operations','route'));
  }

   /**
  *
  * Expense Account code Autocomplete
  *
  **/
  public function expenseAccountCodeAutocomplete(Request $request){

     $key = $request->term;
     $accountCodes  = ExpenseHead::get();
     $acc_codes     =   ExpenseHead::where('acc_code_val', 'ILIKE', '%'.$key.'%')->orWhere('expense_name','ILIKE', '%'.$key.'%')
                 ->select('acc_codes_id AS ids',DB::raw("CONCAT(acc_code_val,'-',expense_name) as value"))
                 ->get();

     return $acc_codes ;


  }

   /*
* Generate Maintenance Invoice
* By
* Jackson
*/
public function groupInvoiceGeneration(Request $request){

  if($request->fromdate && $request->todate){


    $serviceList = DB::table('complaint_service_report_checklist')
                    ->join('complaint_service_report', 'complaint_service_report.id', '=', 'complaint_service_report_checklist.complaint_service_report_id')
                    ->join('complaint_checklists', 'complaint_checklists.id', '=', 'complaint_service_report_checklist.checklist_id')
                    ->join('complaint_enquiries', 'complaint_enquiries.id', '=', 'complaint_checklists.complaint_enquiries_id')
                    ->join('works','works.id','=','complaint_checklists.work_id')
                    ->join('acc_codes','acc_codes.id','=','works.acc_code_id')
                    ->join('buildings', 'buildings.id', '=', 'complaint_enquiries.building_id')
                    ->leftJoin('units', 'units.id', '=', 'complaint_enquiries.unit_id')
                    ->join('complaint_service_report_inv', 'complaint_service_report_inv.service_report_checklist_id', '=', 'complaint_service_report_checklist.id')
                    ->leftJoin('vendors as assign_user', function($join){
                        $join->on('assign_user.id', '=', 'complaint_checklists.assigned_to')->where('complaint_checklists.assigned_to_type', '=', 1);
                    })
                    ->leftJoin('users as subassign_user', 'subassign_user.id', '=', 'complaint_checklists.sub_assigned_to')
                    ->leftJoin('employees as emp_subassign', 'emp_subassign.id', '=', 'subassign_user.user_type_id')
                    ->select(DB::raw('max(complaint_service_report.id) as service_id,service_report_no,assign_user.id as vendor_id,assign_user.vendor_name, emp_subassign.id as emp_id,emp_subassign.employee_name,building_name,buildings.id as building_id,units.id as unit_id,unit_code,sum(material_charge) as mat,sum(labour_charge) as lab,sum(total_charge) as tot, to_date(cast(complaint_enquiries.updated_at as TEXT),'."'YYYY/MM/DD'".') as closedDt,acc_codes.acc_code_val,acc_codes.acc_code_desc,acc_codes.id as accountcodeid'))
                    ->whereBetween('complaint_service_report.updated_at',[$request->fromdate.' 00:00:00', $request->todate.' 23:59:59'])->where('complaint_status',2)->where('is_maintenance_invoice_generate',1)->whereNotNull('complaint_service_report_inv.id')->groupBy('service_report_no','assign_user.vendor_name','emp_subassign.employee_name','building_name','unit_code','assign_user.id','emp_subassign.id','units.id','buildings.id','complaint_enquiries.updated_at','acc_codes.acc_code_val','acc_codes.acc_code_desc','acc_codes.id')
                    //->groupBy('status')
                    ->get();

               //dd($serviceList);
     return view('maintenance::Invoice.generate_invoice_search',compact('serviceList','request'));
  }
  return view('maintenance::Invoice.generate_invoice_search');
}
/*
* Post Generate Maintenance Invoice
* By
* Jackson
*/
public function maintenanceInvoiceGenerate(Request $request){
  
    if($request->vendor_id==null){
      session()->flash('error', 'Select The Line Item');
      return redirect()->route('groupInvoiceGeneration');
    }
    $vendor_sort_arr = $request->vendor_id;
    $k = 0;
   // $sumTot  = 0;
    $inhouseArr = array();
    $subContArr = array();
    $service =array();
    $sumTot =0;
    $sumTotCont = 0;
    $inhouseLoopEntry = 1; //First time entry - 1 then 2
    $inhouse_vendor = vendor::where('vendor_name',INHOUSE)->first();

    //get account code corresponding from work table


    $acc_code = AccountCodes::where('acc_code_val',ACC_CODE)->first();
    //dd($vendor_sort_arr);
    $i = 0;
    foreach($vendor_sort_arr as $vendor){
      
      $isVendor = explode('_',$vendor);
      if($isVendor[0] =='inhouse'){
        if($inhouseLoopEntry==1) {
            $keyVal = $k;
            $k++;
            $inhouseLoopEntry=2 ;
            $arr[$keyVal]['tot_sum'] = 0;
        }
      $arr[$keyVal]['vendor_type'] = $inhouse_vendor->id;
      $buildUnitArr = Arr::sort($request->{'find_comparison_'.$vendor});

      $sumTot  = 0;
      foreach($buildUnitArr as $key=>$itm){
         $inhouseVariable = $request->{'find_comparison_'.$vendor}[$key];
         if(empty($inhouseArr)){

              $arr[$keyVal]['item'][$i]['vendor_type'] = $inhouse_vendor->id;
              $arr[$keyVal]['item'][$i]['service'] = $request->{'service_'.$vendor}[$key];
              $service[] = intval($request->{'service_'.$vendor}[$key]);
              $arr[$keyVal]['item'][$i]['building'] = $request->{'building_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['unit'] = $request->{'unit_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['mat'] = $request->{'mat_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['lab'] = $request->{'lab_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['closed_dt'] = $request->{'closeDt_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['tot'] = $request->{'tot_'.$vendor}[$key];
              
              $arr[$keyVal]['item'][$i]['acc_code_id'] = $request->{'code_id_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['acc_code_desc'] = $request->{'code_desc_'.$vendor}[$key];


              $sumTot             += $request->{'tot_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['comb'] = $request->{'find_comparison_'.$vendor}[$key];
              $inhouseArr[] = $request->{'find_comparison_'.$vendor}[$key];
              

            }
            // elseif(!in_array($inhouseVariable,$inhouseArr)){
            else{
              $arr[$keyVal]['item'][$i]['vendor_type'] = $inhouse_vendor->id;
              $arr[$keyVal]['item'][$i]['service'] = $request->{'service_'.$vendor}[$key];
              $service[] = intval($request->{'service_'.$vendor}[$key]);
              $arr[$keyVal]['item'][$i]['building'] = $request->{'building_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['unit'] = $request->{'unit_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['mat'] = $request->{'mat_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['closed_dt'] = $request->{'closeDt_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['lab'] = $request->{'lab_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['tot'] = $request->{'tot_'.$vendor}[$key];
              $sumTot             += $request->{'tot_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['comb'] = $request->{'find_comparison_'.$vendor}[$key];
              $inhouseArr[] = $request->{'find_comparison_'.$vendor}[$key];

              $arr[$keyVal]['item'][$i]['acc_code_id'] = $request->{'code_id_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['acc_code_desc'] = $request->{'code_desc_'.$vendor}[$key];


              

            }
            /*
            else{
             
             
              $service[] = intval($request->{'service_'.$vendor}[$key]);
              $arr[$keyVal]['item'][$i]['mat'] = $arr[$keyVal]['item'][$i]['mat'] + $request->{'mat_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['lab'] = $arr[$keyVal]['item'][$i]['lab'] + $request->{'lab_'.$vendor}[$key];
              $arr[$keyVal]['item'][$i]['tot'] = $arr[$keyVal]['item'][$i]['tot'] + $request->{'tot_'.$vendor}[$key];
              $sumTot             += $request->{'tot_'.$vendor}[$key];
              
            }
            */
            
          }
          $arr[$keyVal]['tot_sum'] += $sumTot;
          
          $i++;
       }
       else{
        // TO avoid Unique add service Id to Vendor Id
        $vendorExpl = explode('||', $isVendor[0])[1];
        $arr[$k]['vendor_type'] = $vendorExpl;
        $buildUnitArr = Arr::sort($request->{'find_comparison_'.$vendor});
        $j = 0;
        $temp = 0;
        $sumTot  = 0;
        foreach($buildUnitArr as $j=>$itm){
          $subContVariable = $request->{'find_comparison_'.$vendor}[$j];
    
          if(empty($subContArr)){

            $arr[$k]['item'][$temp]['vendor_type'] = $vendorExpl;
            $arr[$k]['item'][$temp]['service'] = $request->{'service_'.$vendor}[$j];
            $service[] = intval($request->{'service_'.$vendor}[$j]);
            $arr[$k]['item'][$temp]['building'] = $request->{'building_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['unit'] = $request->{'unit_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['mat'] = $request->{'mat_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['closed_dt'] = $request->{'closeDt_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['lab'] = $request->{'lab_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['tot'] = $request->{'tot_'.$vendor}[$j];
            $sumTotCont             += $request->{'tot_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['comb'] = $request->{'find_comparison_'.$vendor}[$j];
            $subContArr[] = $request->{'find_comparison_'.$vendor}[$j];
           
            $arr[$k]['item'][$temp]['acc_code_id'] = $request->{'code_id_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['acc_code_desc'] = $request->{'code_desc_'.$vendor}[$j];
            
          }
          //elseif(!in_array($subContVariable,$subContArr)){
          else{  
            $arr[$k]['item'][$temp]['vendor_type'] = $vendorExpl;
            $arr[$k]['item'][$temp]['service'] = $request->{'service_'.$vendor}[$j];
            $service[] = intval($request->{'service_'.$vendor}[$j]);
            $arr[$k]['item'][$temp]['building'] = $request->{'building_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['unit'] = $request->{'unit_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['mat'] = $request->{'mat_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['closed_dt'] = $request->{'closeDt_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['lab'] = $request->{'lab_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['tot'] = $request->{'tot_'.$vendor}[$j];
            $sumTotCont             = $request->{'tot_'.$vendor}[$j];
            $arr[$k]['item'][$temp]['comb'] = $request->{'find_comparison_'.$vendor}[$j];
            $subContArr[] = $request->{'find_comparison_'.$vendor}[$j];

             $arr[$k]['item'][$temp]['acc_code_id'] = $request->{'code_id_'.$vendor}[$j];
             $arr[$k]['item'][$temp]['acc_code_desc'] = $request->{'code_desc_'.$vendor}[$j];

          }
          /*
          else{
              $service[] = intval($request->{'service_'.$vendor}[$j]);
              $arr[$k]['item'][$i-1]['mat'] = $arr[$k]['item'][$i-1]['mat'] + $request->{'mat_'.$vendor}[$j];
              $arr[$k]['item'][$i-1]['lab'] = $arr[$k]['item'][$i-1]['lab'] + $request->{'lab_'.$vendor}[$j];
              $arr[$k]['item'][$i-1]['tot'] = $arr[$k]['item'][$i-1]['tot'] + $request->{'tot_'.$vendor}[$j];
              $sumTot             += $request->{'tot_'.$vendor}[$j];
          }
          */
          $temp++;
          
        }
        $arr[$k]['tot_sum'] = $sumTotCont ;
        $k++;
      }
      
  }
  //dd($arr); 
  $user = \Auth::user();

  foreach($arr as $invoice){
      $generateCode = $this->maintenanceInvoiceCode();
      $nextCode     = $generateCode['code'];
      if(count($invoice['item'])>0){
          $lastDtKey = count($invoice['item'])-1;
          //dd($invoice['item'][$lastDtKey]['closed_dt']);
          $InvDt     = $invoice['item'][$lastDtKey]['closed_dt'];
      }
    
      $maintenanceInvoice =  MaintenanceInvoice::create([
        'maintenance_invoice_no' => $nextCode,
        'maintenance_invoice_date' => $InvDt,
        'vendor_id' => $invoice['vendor_type'],
        'maintenance_invoice_desc' => AUTO_INV_DESC,
        'maintenance_invoice_refer_no' => 'REF'.$nextCode,
        'maintenance_invoice_refer_amt' => $invoice['tot_sum'],
        'maintenance_invoice_payment_method' => 1, // Cash
        'maintenance_invoice_comment' => AUTO_INV_DESC,
        'ax_batch_id' => NULL,
        'ax_invoice_no' => NULL,
        'created_by' => \Auth::user()->id,
        'maintenance_invoice_status' => 2 , // Approved
        'maintenance_invoice_approval_status' => 4, // 1 - Approved
      ]);
      Setting::where('configuration_settings','maintenance_invoice_prefix')->update(['configuration_increment_value'=> $generateCode['inc'] + 1
        ]);
      foreach($invoice['item'] as $itm){
        $maintenanceInvoice->maintenanceInvoiceDetails()->create([
          'ac_codes_id' => $itm['acc_code_id'],
          'description' => $itm['acc_code_desc'],
          'building_id' => $itm['building'],
          'unit_id' => $itm['unit'],
          'invoice_desc' => 'SERV'.$itm['service'],
          'material_charge' => $itm['mat'],
          'labour_charge' => $itm['lab'],
          'debit_amt' => $itm['tot'],
          'credit_amt' => NULL,
          'technician_recovery' => 0,
          'technician_id' =>$itm['vendor_type'],
          'dim1able_type' => 'Modules\BackOffice\Entities\DimDetail',
          'dim1able_id' => 2, 
          'dim2able_type' => 'Modules\Masters\Entities\Building',
          'dim2able_id' => $itm['building'],
          'service_report_id' => $itm['service']

        ]);
        
      }
    } 
    foreach($service as $val){
      ComplaintServiceReport::where('id',$val)->update([
          'is_maintenance_invoice_generate'=> 2
        ]);
    }
    session()->flash('success', 'Maintenance Invoice Generated');
    return redirect()->route('groupInvoiceGeneration');
  }
  /**
  *
  * Building Autocomplete - Code
  *
  **/
  public function buildingAutocompleteCodeForDim(Request $request){

   $key = $request->term;
 // $buildingContract = LandlordContract::active()->get();
  // $buildingId  = $buildingContract->pluck('building_id');

   $building =   Building::active()->where('building_name', 'ILIKE', '%'.$key.'%')
 //  ->WhereIn('id',$buildingId)
   ->select(DB::raw("building_name as value"),'id AS ids')
   ->get();

   return $building ;

 }
}
