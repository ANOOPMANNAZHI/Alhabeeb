<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Sales\Entities\Tenant;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\employee;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\BuildingType;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\ManagementType;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\Masters\Entities\Vendor;
use Modules\Sales\Entities\LandlordContract;
use Auth;
use DB;
use PDF;
use config;
use JasperPHP;
use Modules\BackOffice\Exports\MonthlyTenancyReportExport;
use Modules\BackOffice\Exports\TenancyDetailsReportExport;
use Modules\BackOffice\Exports\MeraRentReceiptReportExport;
use Modules\BackOffice\Exports\TenantReceivableV2Export;
use Modules\BackOffice\Exports\LegalReceivableV2Export;
use Modules\BackOffice\Exports\NormalManagementV2Export;
use Modules\BackOffice\Exports\NormalManagementMonthSheet;
use Modules\BackOffice\Exports\NormalManagementConsolidateSheet;
use Carbon\Carbon;
use ZipArchive;

class BackOfficeReportController extends Controller
{
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
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
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
    public function edit($id)
    {
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
    /*
    *
    *New Tenant For period starts
    *
    */
    public function showTenantContractReport(){
		//$domain=  $_SERVER['HTTP_HOST'];
     // dd($domain);
      return view('backoffice::reports.tenant_contract_report');
    } 
    public function tenantContractReportPdf(Request $request){
     $db = config('report.database');
     $username = Auth::user()->username;
     $Date1 =  $request['start_date'];
     $Date2 =  $request['end_date'];
    // $logo = 'public/img/logo-1.png';
	 $logo = getLogoPath();
     $jasper = new JasperPHP;
     //dd($end_date);
     if(isset($request->download_type)){

      if($request->download_type == 'pdf'){

// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/new_tenant_for_period.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/new_tenant_for_period.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

//$file= base_path(). "/vendor/cossou/jasperphp/examples/new_tenant_for_period.pdf";
//return response()->file($file); 

$file= getReportUrl()."vendor/cossou/jasperphp/examples/new_tenant_for_period.pdf";
       // dd($file);
return redirect()->away($file);
print_r($r);  
}else{
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/new_tenant_for_period_excel.jrxml'))->execute();
print_r($a);*/ 
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/new_tenant_for_period_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/new_tenant_for_period_excel.xlsx";
    return redirect()->away($file1);  
print_r($r); 
} 
} 
}

 /*
    *
    *New Tenant For period ends
    *
    */
 /*
 *
 *Corporate Tenant starts
 *
 */
 public function showCorporateTenantReport(){
  return view('backoffice::reports.corporate_tenant');
} 
public function tenantReportAutocompleteCode(Request $request){
  $key = $request->term;

  $tenant =   Tenant::active()
  ->whereHas('tenantContracts', function ($query)use($request) {
    $query->where('tenant_contract_status',1);
  })->where('tenant_name', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(tenant_name) as value"))
  ->get();
  return $tenant ;
}
public function tenantCodeReportAutocompleteCode(Request $request){
  $key = $request->term;

  $tenant =   Tenant::active()
  ->whereHas('tenantContracts', function ($query)use($request) {
    $query->where('tenant_contract_status',1);
  })->where('tenant_code', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(tenant_code) as value"))
  ->get();
  return $tenant ;
}
public function corporateTenantReportPdf(Request $request){
 $db = config('report.database');
 $username = Auth::user()->username;
 $tenant_name =  $request['tenant_name'];
 $tenant_code =  $request['tenant_code'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);

 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){

// Compile a JRXML to Jasper
    /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/corporate_tenant_municipal_flats.jrxml'))->execute();
    print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
    $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/corporate_tenant_municipal_flats.jrxml'),false,array('pdf'),array("tenant_name" => $tenant_name,"tenant_code" => $tenant_code,"username" => $username,"logo" => $logo),$db)->execute();

    //$file= base_path(). "/vendor/cossou/jasperphp/examples/corporate_tenant_municipal_flats.pdf";
    //return response()->file($file); 
	
	$file= getReportUrl()."vendor/cossou/jasperphp/examples/corporate_tenant_municipal_flats.pdf";
       // dd($file);
return redirect()->away($file);
    print_r($r); 
  }
  else{
// Compile a JRXML to Jasper
    /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/corporate_tenant_municipal_flats_excel.jrxml'))->execute();
    print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
    $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/corporate_tenant_municipal_flats_excel.jrxml'),false,array('xlsx'),array("tenant_name" => $tenant_name,"tenant_code" => $tenant_code,"username" => $username,"logo" => $logo),$db)->execute();




    $file1= getReportUrl()."vendor/cossou/jasperphp/examples/corporate_tenant_municipal_flats_excel.xlsx";
    return redirect()->away($file1);
   // print_r($r); 
  } 
}  
}
/*
 *
 *Corporate Tenant ends
 *
 */

/*
 *
 *Report on Contract Expiry starts
 *
 */
public function showTenantContractExpiryReport(){
  return view('backoffice::reports.tenant_contract_expiry');
}
public function tenantContractExpiryReportPdf(Request $request){
 $db = config('report.database');
 $user = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $building_name =  $request['building_name'];
 $are_name =  $request['are'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);

 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){

    if(!empty($Date1) && !empty($Date2) && empty($are_name) && empty($building_name)){ //Mandotory
          // Compile a JRXML to Jasper
      /*  $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/contract_expiry_date.jrxml'))->execute();
       print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/contract_expiry_date.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"are_name" => $are_name,"building_name" => $building_name,"logo" => $logo),$db)->execute();

       $file= getReportUrl()."vendor/cossou/jasperphp/examples/contract_expiry_date.pdf";
       return redirect()->away($file);

       print_r($r);
     }else{
          // Compile a JRXML to Jasper
       /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/contract_expiry_compo.jrxml'))->execute();
       print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/contract_expiry_compo.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"are_name" => $are_name,"building_name" => $building_name,"logo" => $logo),$db)->execute();

       $file= getReportUrl(). "vendor/cossou/jasperphp/examples/contract_expiry_compo.pdf";
       return redirect()->away($file);
       print_r($r);
     }  
   }
   else{
      if(!empty($Date1) && !empty($Date2) && empty($are_name) && empty($building_name)){ //Mandotory
       // Compile a JRXML to Jasper
    /*  $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/contract_expiry_excel_date.jrxml'))->execute();
     print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
     $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/contract_expiry_excel_date.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"are_name" => $are_name,"building_name" => $building_name,"logo" => $logo),$db)->execute();

     $file1= getReportUrl()."vendor/cossou/jasperphp/examples/contract_expiry_excel_date.xlsx";

     
     return redirect()->away($file1);
}else{ //Mandotory
       // Compile a JRXML to Jasper
      /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/contract_expiry_excel_combo.jrxml'))->execute();
     print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
     $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/contract_expiry_excel_combo.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"are_name" => $are_name,"building_name" => $building_name,"logo" => $logo),$db)->execute();

     $file1= getReportUrl()."vendor/cossou/jasperphp/examples/contract_expiry_excel_combo.xlsx";

     
     return redirect()->away($file1);
   }
 }
}
}


public function buildingNameReportAutocompleteCode(Request $request){
  $key = $request->term;

  $building =   Building::active()
  ->whereHas('tenantContract', function ($query)use($request) {
    $query->where('tenant_contract_status',1);
  })->where('building_name', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(building_name) as value"))
  ->get();
  return $building ;
}
public function areReportAutocompleteCode(Request $request){
  $key = $request->term;
  $employee = employee::whereHas('user', function ($query) use ($request){
    $query->active()->whereHas('Are', function ($query) use ($request){
      $query->whereHas('buildingNamesExist');
    });
  })->where('employee_name', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(employee_name) as value"))
  ->get();
  return $employee ;

}
/*
 *
 *Report on Contract Expiry ends
 *
 */

 /*
 *
 *Tenant Contract Renewal Status Report starts
 *
 */
 public function showTenantContractRenewalReport(){
  return view('backoffice::reports.tenant_contract_renewal_report');
}
public function tenantContractRenewalReportPdf(Request $request){
 $db = config('report.database');
 $user = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($logo);

 // print_r($Date1);exit();

 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){

// Compile a JRXML to Jasper
$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/tenant_contract_renewal_status.jrxml'))->execute();
var_dump($a);
// print_r($a); 
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/tenant_contract_renewal_status.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/tenant_contract_renewal_status.pdf";
return redirect()->away($file);
print_r($r);    
}else{
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/tenant_contract_renewal_status_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/tenant_contract_renewal_status_excel.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/tenant_contract_renewal_status_excel.xlsx";
return redirect()->away($file1);
}
}
}

 /*
 *
 *Tenant Contract Renewal Status Report ends
 *
 */

 /*
 *
 *Early Termination Report starts
 *
 */
 public function showEarlyTerminationReport(){
  return view('backoffice::reports.tenant_early_termination_report');
}
public function tenantEarlyTerminationReportPdf(Request $request){
 $db = config('report.database');
 $user = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){

// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/early_termination.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/early_termination.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

//$file= base_path(). "/vendor/cossou/jasperphp/examples/early_termination.pdf";
//return response()->file($file); 

$file= getReportUrl()."vendor/cossou/jasperphp/examples/early_termination.pdf";
       // dd($file);
return redirect()->away($file);
print_r($r); 
}else{
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/early_termination_excel.jrxml'))->execute();
print_r($a);*/ 
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/early_termination_excel.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();


$file1= getReportUrl()."vendor/cossou/jasperphp/examples/early_termination_excel.xlsx";


return redirect()->away($file1);


print_r($r); 
} 
}  
}
 /*
 *
 *Early Termination Report ends
 *
 */

 /*
 *
 * Termination Report starts
 *
 */
 public function showTerminationReport(){
  return view('backoffice::reports.termination_report');
}
public function tenantTerminationReportPdf(Request $request){
 $db = config('report.database');
 $user = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/termination.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/termination.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();



$file= getReportUrl()."vendor/cossou/jasperphp/examples/termination.pdf";
return redirect()->away($file);
 //print_r($r);  
}else{
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/termination_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/termination_excel.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/termination_excel.xlsx";


return redirect()->away($file1);

}
}
}
 /*
 *
 * Termination Report ends
 *
 */

 /*
 *
 * Termination Report starts
 *
 */
 public function showEmployeeTenantContractReport(){
  $employeeList     = Employee::active()->whereHas('user',function ($query){
    $query->role(['sales_person','sales_coordinator']);
  })->orderBy('id', 'DESC')->get();
  return view('backoffice::reports.employee_tenant_contract_report',compact('employeeList'));
}
public function employeeTenantContractReportPdf(Request $request){
    $user = Auth::user()->username;
    $startDate = $request['start_date'];
    $endDate = $request['end_date'];
    $employee_id = $request['tenant_marketing_executive'];
    $employeeName = '';

    if (!empty($employee_id)) {
        $employeeName = Employee::where('id', $employee_id)->first()->employee_name;
    }

    $logoPath = public_path('img/logo_pdf.jpg');
    $logo = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));

    // Query matching the Jasper report SQL
    $query = "
        SELECT DISTINCT vt.id, vt.building_name, b.building_no, u.unit_no, vt.tenant_name,
            vt.tenant_contract_start_date AS startdate,
            vt.tenant_contract_valid_to_date AS enddate,
            vt.tenant_contract_rent,
            vt.tenant_contract_duration,
            vt.employee_name,
            Deposit_Amt(vt.id) AS deposit_amt,
            COALESCE(c.nofpdc, 0) AS nofpdc,
            (EXTRACT(YEAR FROM AGE(vt.tenant_contract_valid_to_date, vt.tenant_contract_start_date)) * 12
             + EXTRACT(MONTH FROM AGE(vt.tenant_contract_valid_to_date, vt.tenant_contract_start_date)) + 1)::integer AS contract_period_months,
            COALESCE(mt.management_types_name, '-') AS management_type
        FROM view_tenant_stage vt
        LEFT JOIN tenant_contracts tc ON tc.tenant_contract_no = vt.tenant_contract_no
        LEFT JOIN buildings b ON b.building_name = vt.building_name
        LEFT JOIN units u ON u.unit_code = vt.unit_code
        LEFT JOIN management_types mt ON mt.id = b.management_id
        LEFT JOIN (
            SELECT vt.id AS contractid, COUNT(p.tenant_contract_id) AS nofpdc
            FROM pdc p
            LEFT JOIN tenant_contracts vt ON vt.id = p.tenant_contract_id
            GROUP BY vt.id
        ) c ON vt.id = c.contractid
        WHERE vt.work_flow_processes_code = '108'
          AND vt.sale_work_flow_processes_code = '108'
          AND vt.sales_enquiry_direct_contract = '1'
          AND vt.status = '1'
          AND vt.tenant_contract_start_date BETWEEN ? AND ?
    ";

    $params = [$startDate, $endDate];

    if (!empty($employeeName)) {
        $query .= " AND vt.employee_name = ?";
        $params[] = $employeeName;
    }

    $query .= " ORDER BY vt.building_name, u.unit_no";

    $contracts = collect(DB::select($query, $params));
    $groupedContracts = $contracts->groupBy('employee_name');

    $data = compact('groupedContracts', 'user', 'logo', 'startDate', 'endDate', 'employeeName');

    if ($request->download_type == 'pdf') {
        $pdf = \PDF::loadView('backoffice::Reports.employee_tenant_contract_report_pdf', $data)
                  ->setPaper('a4', 'landscape');
        return $pdf->download('employee_tenant_contract_report.pdf');
    } else {
        return response()->view('backoffice::Reports.employee_tenant_contract_report_excel', $data, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="employee_tenant_contract_report.xlsx"',
        ]);
    }
}
 /*
 *
 * Termination Report ends
 *
 */

 /*
 *
 * Report on Deposit for rent / E,W starts
 *
 */
public function showDepositRentReport(){
  $buildings =   Building::active()
  ->whereHas('tenantContract', function ($query) {
    $query->where('tenant_contract_status',1)->whereHas('receiptGenerationList');
  })->orderBy('building_name','asc')->get();
  return view('backoffice::reports.deposit_rent_report',compact('buildings'));
}
public function depositRentReportPdf(Request $request){
 $db = config('report.database');
 $username = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $building =  $request['building_name'];
 if(!empty($building)){
  $building_name = Building::where('id',$building)->first()->building_name;
}
else{
  $building_name = '';
}
$logo = getLogoPath();
$jasper = new JasperPHP;
     //dd($tenant_code);
if(isset($request->download_type)){

  if($request->download_type == 'pdf'){

    if(!empty($Date1) && !empty($Date2) && empty($building_name)){ //Mandotory

// Compile a JRXML to Jasper
   /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_ew_deposit_amt.jrxml'))->execute();
   print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
   $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/rent_ew_deposit_amt.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

   $file= getReportUrl()."vendor/cossou/jasperphp/examples/rent_ew_deposit_amt.pdf";
   return redirect()->away($file);  
   print_r($r);
 }else{
    // Compile a JRXML to Jasper
    /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_ew_deposit_amt_comp.jrxml'))->execute();
   print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
   $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/rent_ew_deposit_amt_comp.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

   $file= getReportUrl()."vendor/cossou/jasperphp/examples/rent_ew_deposit_amt_comp.pdf";
   return redirect()->away($file);  
   print_r($r);
 }  
}else{

  if(!empty($Date1) && !empty($Date2) && empty($building_name)){ //Mandotory
// Compile a JRXML to Jasper
  /*  $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_ew_deposit_amt_excel.jrxml'))->execute();
    print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
  $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/rent_ew_deposit_amt_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

  $file1= getReportUrl()."vendor/cossou/jasperphp/examples/rent_ew_deposit_amt_excel.xlsx";


  return redirect()->away($file1);
}else{
  // Compile a JRXML to Jasper
  /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_ew_deposit_amt_excel_compo.jrxml'))->execute();
  print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
  $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/rent_ew_deposit_amt_excel_compo.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

  $file1= getReportUrl()."vendor/cossou/jasperphp/examples/rent_ew_deposit_amt_excel_compo.xlsx";


  return redirect()->away($file1);
}

}
}
}



public function buildingsCodeReportAutocompleteCode(Request $request){
  $key = $request->term;

  $building =   Building::active()
  ->whereHas('tenantContract', function ($query)use($request) {
    $query->where('tenant_contract_status',1)->whereHas('receiptGenerationList');
  })->where('building_code', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(building_code) as value"))
  ->get();
  return $building ;
}
 /*
 *
 * Report on Deposit for rent / E,W ends
 *
 */

 /*
 *
 * Rent Receipt starts
 *
 */
  public function showRentReceiptReport(){
   $buildings =  $building =   Building::active()
  ->where('building_name', 'NOT ILIKE', '%-MERA')
  ->whereHas('tenantContract', function ($query){
    $query->where('tenant_contract_status',1);
  })->orderBy('building_name','asc')
  ->get();
  $managementTypes = \Modules\Masters\Entities\ManagementType::active()->get();
  return view('backoffice::reports.rent_receipt_report',compact('buildings','managementTypes'));
}

public function rentReceiptReportPdf(Request $request){
 $db = config('report.database');
 $username = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $building =  $request['building_name'];
 if(!empty($building)){
  $building_name = Building::where('id',$building)->first()->building_name;
}
else{
  $building_name = '';
}
 $building_code =  isset($request->building_code) ? $request->building_code : null;
 $management_type =  isset($request->management_type) ? $request->management_type : '';
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){
    if(!empty($Date1) && !empty($Date2) && empty($building_name) && empty($building_code)){ //Mandotory

// Compile a JRXML to Jasper
    /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_receipt.jrxml'))->execute();
    print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
    $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/rent_receipt.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"building_code" => $building_code,"management_type" => $management_type,"logo" => $logo),$db)->execute();

    $file= getReportUrl()."vendor/cossou/jasperphp/examples/rent_receipt.pdf";
    return redirect()->away($file);
    print_r($r);
  } else{

// Compile a JRXML to Jasper
    /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_receipt_compo.jrxml'))->execute();
    print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
    $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/rent_receipt_compo.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"building_code" => $building_code,"management_type" => $management_type,"logo" => $logo),$db)->execute();

    $file= getReportUrl()."vendor/cossou/jasperphp/examples/rent_receipt_compo.pdf";
    return redirect()->away($file);
    print_r($r);
  }
}else{
     if(!empty($Date1) && !empty($Date2) && empty($building_name) && empty($building_code)){ //Mandotory
// Compile a JRXML to Jasper
   /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_receipt_excel.jrxml'))->execute();
   print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
   $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/rent_receipt_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"building_code" => $building_code,"management_type" => $management_type,"logo" => $logo),$db)->execute();

   $file1= getReportUrl()."vendor/cossou/jasperphp/examples/rent_receipt_excel.xlsx";


   return redirect()->away($file1);
 }
 else{
    // Compile a JRXML to Jasper
  /*  $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_receipt_excel_compo.jrxml'))->execute();
  print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
  $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/rent_receipt_excel_compo.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"building_code" => $building_code,"management_type" => $management_type,"logo" => $logo),$db)->execute();

  $file1= getReportUrl()."vendor/cossou/jasperphp/examples/rent_receipt_excel_compo.xlsx";


  return redirect()->away($file1);
}
}
}
}
public function rentBuildingAutocompleteCode(Request $request){
  $key = $request->term;

  $building =  $building =   Building::active()
  ->whereHas('tenantContract', function ($query)use($request) {
    $query->where('tenant_contract_status',1)->whereHas('rentReceiptGenerationList');
  })->where('building_name', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(building_name) as value"))
  ->get();
  return $building ;
}
public function rentBuildingCodeAutocompleteCode(Request $request){
  $key = $request->term;

  $building =  $building =   Building::active()
  ->whereHas('tenantContract', function ($query)use($request) {
    $query->where('tenant_contract_status',1)->whereHas('rentReceiptGenerationList');
  })->where('building_code', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(building_code) as value"))
  ->get();
  return $building ;
}

/*
 *
 * Rent Receipt ends
 *
 */

/*
 *
 * General Receipt starts
 *
 */
public function showGeneralReceiptReport(){
  // $buildings = Building::active()
  // ->whereHas('tenantContract', function ($query) {
  //   $query->where('tenant_contract_status',1)->whereHas('generalReceiptGenerationList');
  // })->orderBy('building_name','asc')
  // ->get();

  $buildings =  $building =   Building::active()
  ->whereHas('tenantContract', function ($query){
    $query->where('tenant_contract_status',1);
  })->orderBy('building_name','asc')
  ->get();
 
  return view('backoffice::reports.general_receipt_report',compact('buildings'));
}
public function generalReceiptReportPdf(Request $request){


 $db = config('report.database');
 $username = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $building =  $request['building_name'];
// print_r(json_encode($building));exit();

 if(!empty($building)){
  $building_name = Building::where('id',$building)->first()->building_name;
}
else{
  $building_name = '';
}
 $building_code =  $request['building_code'];
 $acc_code_val =  $request['acc_code_val'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
 if(isset($request->download_type)){

    if($request->download_type == 'pdf'){

      if(!empty($Date1) && !empty($Date2) && empty($building_name) && empty($building_code) && empty($acc_code_val)){ //Mandotory

          $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/general_receipt_date.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"building_code" => $building_code,"acc_code_val" => (int)$acc_code_val,"logo" => $logo),$db)->execute();
          $file= getReportUrl()."vendor/cossou/jasperphp/examples/general_receipt_date.pdf";
          return redirect()->away($file);
          print_r($r);
        }
        else{

          // $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/tenant_contract_renewal_status.jrxml'))->execute();
          // var_dump($a);


            $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/general_receipt_compo.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"building_code" => $building_code,"acc_code_val" => (int)$acc_code_val,"logo" => $logo),$db)->execute();
            $file= getReportUrl()."vendor/cossou/jasperphp/examples/general_receipt_compo.pdf";
            return redirect()->away($file);
            print_r($r);
      }
    }
    else{

       if(!empty($Date1) && !empty($Date2) && empty($building_name) && empty($building_code) && empty($acc_code_val)){ //Mandotory

            $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/general_receipt_excel_date.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"building_code" => $building_code,"acc_code_val" => (int)$acc_code_val,"logo" => $logo),$db)->execute();

            $file1= getReportUrl()."vendor/cossou/jasperphp/examples/general_receipt_excel_date.xlsx";


            return redirect()->away($file1);
          }
          else{
             
            $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/general_receipt_excel_compo.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"building_code" => $building_code,"acc_code_val" => (int)$acc_code_val,"logo" => $logo),$db)->execute();

            $file1= getReportUrl()."vendor/cossou/jasperphp/examples/general_receipt_excel_compo.xlsx";


            return redirect()->away($file1);
          }
    }
  }  
}

public function generalBuildingAutocompleteCode(Request $request){
  $key = $request->term;

  $building = Building::active()
  ->whereHas('tenantContract', function ($query)use($request) {
    $query->where('tenant_contract_status',1)->whereHas('generalReceiptGenerationList');
  })->where('building_name', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(building_name) as value"))
  ->get();
  return $building ;
}
public function generalBuildingCodeAutocompleteCode(Request $request){
  $key = $request->term;

  $building = Building::active()
  ->whereHas('tenantContract', function ($query)use($request) {
    $query->where('tenant_contract_status',1)->whereHas('generalReceiptGenerationList');
  })->where('building_code', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(building_code) as value"))
  ->get();
  return $building ;
}
public function accountCodeReceiptAutocompleteCode(Request $request){
  $key = $request->term;

  $accountCodes = AccountCodes::where('acc_code_val', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(acc_code_val) as value"))
  ->get();
  return $accountCodes ;
}

/*
 *
 * General Receipt ends
 *
 */

/*
 *
 *Payment History starts
 *
 */
public function showPaymentHistoryReport(){
  $buildings =  $building =   Building::active()
  ->whereHas('tenantContract', function ($query){
    $query->where('tenant_contract_status',1)->whereHas('rentReceiptGenerationList');
  })->orderBy('building_name','asc')
  ->get();
  return view('backoffice::reports.payment_history_report',compact('buildings'));
}
public function paymentHistoryReportPdf(Request $request){
 $db = config('report.database');
 $user = Auth::user()->username;

 $building =  $request['building_id'];
 $unit = $request['u_id'];
 $tenant = $request['t_id'];

 // print($unit);exit();


 if(!empty($building)){
  $building_name = Building::where('id',$building)->first()->building_name;
}
else{
  $building_name = '';
}


if(!empty($unit)){
  $unit_name = Unit::where('id',$unit)->first()->unit_no;
}
else{
  $unit_name = '';
}

if(!empty($tenant)){
  $tenant_name = Tenant::where('id',$tenant)->first()->tenant_name;
}
else{
  $tenant_name = '';
}




 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){
    if(!empty($building_name)){
      // Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/tenant_payment_history.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/tenant_payment_history.jrxml'),false,array('pdf'),array("user" => $user,"building_name" => $building_name,"logo" => $logo,"unit_id"=>$unit_name,"tenant_id"=> $tenant_name),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/tenant_payment_history.pdf";
return redirect()->away($file);
print_r($r);

}else{
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/tenant_payment_history_all.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/tenant_payment_history_all.jrxml'),false,array('pdf'),array("user" => $user,"building_name" => $building_name,"logo" => $logo,"unit_id"=>$unit_name,"tenant_id"=> $tenant_name),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/tenant_payment_history_all.pdf";
return redirect()->away($file);
print_r($r);
}  
}else{
    if(!empty($building_name)){
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/tenant_payment_history_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/tenant_payment_history_excel.jrxml'),false,array('xlsx'),array("user" => $user,"building_name" => $building_name,"logo" => $logo,"unit_id"=>$unit_name,"tenant_id"=> $tenant_name),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/tenant_payment_history_excel.xlsx";


return redirect()->away($file1);
}else{
  // Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/tenant_payment_history_all_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/tenant_payment_history_all_excel.jrxml'),false,array('xlsx'),array("user" => $user,"building_name" => $building_name,"logo" => $logo,"unit_id"=>$unit_name,"tenant_id"=> $tenant_name),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/tenant_payment_history_all_excel.xlsx";


return redirect()->away($file1);
}
}
}  
}

/*
 *
 *Payment History ends
 *
 */

/*
 *
 *Rent Amount collected through Legal Case starts
 *
 */
public function showLegalRentAmountReport(){
  return view('backoffice::reports.legal_rent_amount_report');
}
public function legalRentAmountReportPdf(Request $request){
 $db = config('report.database');
 $username = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){

// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_amount_collected_through_legalcase.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/rent_amount_collected_through_legalcase.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

//$file= base_path(). "/vendor/cossou/jasperphp/examples/rent_amount_collected_through_legalcase.pdf";
//return response()->file($file); 

$file= getReportUrl()."vendor/cossou/jasperphp/examples/rent_amount_collected_through_legalcase.pdf";
       // dd($file);
return redirect()->away($file);
print_r($r);
}else{
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_amount_collected_through_legalcase_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/rent_amount_collected_through_legalcase_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/rent_amount_collected_through_legalcase_excel.xlsx";


return redirect()->away($file1);
}
}    
}

/*
 *
 *Rent Amount collected through Legal Case ends
 *
 */

/*
 *
 *Tenancy Details by Building Wise starts
 *
 */
public function showtenancyDetailsReport(){
  $buildingTypes =  BuildingType::active()->get();
  return view('backoffice::reports.tenancy_details_report',compact('buildingTypes'));
}

public function tenancyDetailsReportPdf(Request $request){
  $user = Auth::user()->username;

  $building_name = $request['building_name'];
  $building_code = $request['building_code'];
  $building_type = $request['building_type'];
  $employee_name = $request['are'];
  $tenant_name   = $request['tenant_name'];

  if(!isset($request->download_type)){
    return;
  }

  // Filter type 5 = Tenant Name (detailed tenant report, PDF only)
  if($request->filter_type == 5){
    $tenant = DB::selectOne("
      SELECT tt.tenant_types_name, t.tenant_name, t.resident_id, t.passport_no,
             n.nationality, t.tenant_employer_name,
             CASE t.tenant_gender WHEN 0 THEN 'Male' ELSE 'Female' END as tenant_gender,
             t.designation, l.locations_name, t.tenant_contact_address,
             t.tenant_secondary_address, t.tenant_post_box, t.tenant_pc,
             t.tenant_contact_no, t.tenant_residence_tel, t.tenant_personal_email,
             t.tenant_ice_contact_no, bk.bank_name, t.tenant_acc_no, t.gsm_no
      FROM tenant t
      LEFT JOIN tenant_types tt ON t.tenant_type_id = tt.id
      LEFT JOIN nationalities n ON n.nationalityid = t.nationalities_id
      LEFT JOIN locations l ON l.id = t.location_id
      LEFT JOIN bank bk ON bk.id = t.bank_id
      WHERE t.tenant_name = ?
    ", [$tenant_name]);

    $contracts = DB::select("
      SELECT tc.tenant_contract_no, tc.tenant_contract_muncipality_agr_no,
             tc.tenant_contract_value, b.building_name, u.unit_no,
             tc.tenant_contract_rent, tc.tenant_contract_status,
             CASE WHEN tc.tenant_contract_status = 1 THEN 'Active' ELSE 'Inactive' END as contract_status
      FROM tenant t
      LEFT JOIN tenant_contracts tc ON t.id = tc.tenant_id
      LEFT JOIN buildings b ON b.id = tc.building_id
      LEFT JOIN units u ON u.id = tc.unit_id
      WHERE t.tenant_name = ?
    ", [$tenant_name]);

    $logoPath = public_path('img/logo_pdf.jpg');
    $logo = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));

    $data = compact('tenant', 'contracts', 'user', 'logo');
    $pdf = \PDF::loadView('backoffice::Reports.tenancy_detailed_report_pdf', $data)
              ->setPaper('a4', 'portrait');
    return $pdf->download('tenancy_detailed_report.pdf');
  }

  // Filter types 1-4: Building-wise tenancy details
  $filterType = $request->filter_type;

  // Determine WHERE clause based on filter type
  switch($filterType){
    case 1: $whereClause = 'building_name = ?';     $filterParam = $building_name; $filterLabel = 'Building Name'; break;
    case 2: $whereClause = 'building_code = ?';      $filterParam = $building_code; $filterLabel = 'Building Code'; break;
    case 3: $whereClause = 'building_types_name = ?'; $filterParam = $building_type; $filterLabel = 'Building Type'; break;
    case 4: $whereClause = 'employee_name = ?';      $filterParam = $employee_name; $filterLabel = 'ARE'; break;
    default: return;
  }

  $rows = DB::select("
    SELECT * FROM (
      SELECT DISTINCT tc.unit_usage, u.unit_vaccant_status, b.building_name, b.building_code,
             u.unit_no, ut.unit_types_name, tc.tenant_contract_no, tc.tenant_contract_rent,
             tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
             null::date AS termination_date, t.tenant_name,
             CASE WHEN u.unit_vaccant_status = 0 THEN 'Yes' ELSE 'No' END AS vaccant_status,
             pm.payment_method_code, tc.tenant_contract_last_paid_date,
             t.tenant_contact_no, bt.building_types_name, e.employee_name,
             'active' AS row_flag
      FROM units u
      LEFT JOIN buildings b ON b.id = u.building_id
      LEFT JOIN building_types bt ON bt.id = b.building_type_id
      LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
      LEFT JOIN tenant_contracts tc ON tc.unit_id = u.id
      LEFT JOIN tenant t ON t.id = tc.tenant_id
      LEFT JOIN (
        SELECT pb.building_id, employee_name FROM employees emp
        LEFT JOIN users us ON emp.id = us.user_type_id
        LEFT JOIN are_buildings are ON us.id = are.user_id
        LEFT JOIN preferred_buildings pb ON are.id = pb.are_building_id
        WHERE pb.assign_to IS NULL
      ) e ON e.building_id = b.id
      LEFT JOIN payment_method pm ON tc.tenant_contract_payment_type = pm.payment_method_index
      WHERE (tc.work_flow_processes_code = '104' OR u.unit_vaccant_status IN (1,2))
        AND tc.tenant_contract_status = '1' AND u.unit_status = '1' AND now() >= u.created_at

      UNION

      -- Occupied units with no currently-active contract (e.g. mid-renewal: previous
      -- contract expired, replacement contract not yet approved/started). Without this
      -- branch such units are silently dropped from the report entirely.
      SELECT * FROM (
        SELECT DISTINCT ON (u.id) tc.unit_usage, u.unit_vaccant_status, b.building_name, b.building_code,
               u.unit_no, ut.unit_types_name, tc.tenant_contract_no, tc.tenant_contract_rent,
               tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
               null::date AS termination_date, t.tenant_name,
               CASE WHEN u.unit_vaccant_status = 0 THEN 'Yes' ELSE 'No' END AS vaccant_status,
               pm.payment_method_code, tc.tenant_contract_last_paid_date,
               t.tenant_contact_no, bt.building_types_name, e.employee_name,
               'pending' AS row_flag
        FROM units u
        LEFT JOIN buildings b ON b.id = u.building_id
        LEFT JOIN building_types bt ON bt.id = b.building_type_id
        LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
        LEFT JOIN tenant_contracts tc ON tc.unit_id = u.id
        LEFT JOIN tenant t ON t.id = tc.tenant_id
        LEFT JOIN (
          SELECT pb.building_id, employee_name FROM employees emp
          LEFT JOIN users us ON emp.id = us.user_type_id
          LEFT JOIN are_buildings are ON us.id = are.user_id
          LEFT JOIN preferred_buildings pb ON are.id = pb.are_building_id
          WHERE pb.assign_to IS NULL
        ) e ON e.building_id = b.id
        LEFT JOIN payment_method pm ON tc.tenant_contract_payment_type = pm.payment_method_index
        WHERE u.unit_vaccant_status IN (1,2) AND u.unit_status = '1' AND now() >= u.created_at
          AND NOT EXISTS (
            SELECT 1 FROM tenant_contracts tcx
            WHERE tcx.unit_id = u.id AND tcx.tenant_contract_status = '1'
          )
        ORDER BY u.id, tc.tenant_contract_effective_date DESC NULLS LAST, tc.id DESC
      ) occ_no_active

      UNION

      SELECT DISTINCT tc.unit_usage, u.unit_vaccant_status, b.building_name, b.building_code,
             u.unit_no, ut.unit_types_name, tc.tenant_contract_no, u.unit_base_rent::float,
             tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
             null::date AS termination_date, t.tenant_name,
             CASE WHEN u.unit_vaccant_status = 0 THEN 'Yes' ELSE 'No' END AS vaccant_status,
             pm.payment_method_code, tc.tenant_contract_last_paid_date,
             t.tenant_contact_no, bt.building_types_name, e.employee_name,
             'vacant' AS row_flag
      FROM units u
      LEFT JOIN buildings b ON b.id = u.building_id
      LEFT JOIN building_types bt ON bt.id = b.building_type_id
      LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
      LEFT JOIN tenant_contracts tc ON tc.unit_id = u.id
      LEFT JOIN tenant t ON t.id = tc.tenant_id
      LEFT JOIN (
        SELECT pb.building_id, employee_name FROM employees emp
        LEFT JOIN users us ON emp.id = us.user_type_id
        LEFT JOIN are_buildings are ON us.id = are.user_id
        LEFT JOIN preferred_buildings pb ON are.id = pb.are_building_id
        WHERE pb.assign_to IS NULL
      ) e ON e.building_id = b.id
      LEFT JOIN payment_method pm ON tc.tenant_contract_payment_type = pm.payment_method_index
      WHERE u.unit_vaccant_status = '0' AND tc.tenant_contract_no IS NULL
        AND u.unit_status = '1' AND now() >= u.created_at

      UNION

      SELECT DISTINCT tc.unit_usage, u.unit_vaccant_status, b.building_name, b.building_code,
             u.unit_no, ut.unit_types_name, a.tenant_contract_no, tc.tenant_contract_rent,
             tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
             a.terminationdate AS termination_date, t.tenant_name,
             CASE WHEN u.unit_vaccant_status = 0 THEN 'Yes' ELSE 'No' END AS vaccant_status,
             pm.payment_method_code, tc.tenant_contract_last_paid_date,
             t.tenant_contact_no, bt.building_types_name, e.employee_name,
             'vacant' AS row_flag
      FROM units u
      LEFT JOIN buildings b ON b.id = u.building_id
      LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
      LEFT JOIN building_types bt ON bt.id = b.building_type_id
      LEFT JOIN (
        SELECT pb.building_id, employee_name FROM employees emp
        LEFT JOIN users us ON emp.id = us.user_type_id
        LEFT JOIN are_buildings are ON us.id = are.user_id
        LEFT JOIN preferred_buildings pb ON are.id = pb.are_building_id
        WHERE pb.assign_to IS NULL
      ) e ON e.building_id = b.id
      LEFT JOIN (
        SELECT DISTINCT u.unit_code, MAX(tenant_contract_no) AS tenant_contract_no,
               MAX(tr.termination_date) AS terminationdate,
               MAX(tc.tenant_contract_valid_from_date) AS contract_start_date
        FROM units u
        LEFT JOIN tenant_contracts tc ON tc.unit_id = u.id
        LEFT JOIN termination tr ON tr.contract_id = tc.id
        WHERE tenant_contract_status = '0' AND u.unit_vaccant_status = '0' AND u.unit_status = '1'
        GROUP BY unit_code
      ) a ON a.unit_code = u.unit_code
      LEFT JOIN tenant_contracts tc ON tc.tenant_contract_no = a.tenant_contract_no
      LEFT JOIN payment_method pm ON tc.tenant_contract_payment_type = pm.payment_method_index
      LEFT JOIN tenant t ON t.id = tc.tenant_id
      WHERE (tc.work_flow_processes_code = '104' OR u.unit_vaccant_status = '0')
        AND tc.tenant_contract_status = '0' AND u.unit_status = '1' AND now() >= u.created_at
      ORDER BY unit_no
    ) x
    WHERE {$whereClause}
    ORDER BY building_name, unit_no
  ", [$filterParam]);

  $collection   = collect($rows);
  $totalUnits   = $collection->count();
  $occupiedCount = $collection->where('unit_vaccant_status', '!=', 0)->count();
  $vacantCount  = $collection->where('unit_vaccant_status', 0)->count();
  $totalRent    = $collection->where('unit_vaccant_status', '!=', 0)->sum(function($row){
    return (float)$row->tenant_contract_rent;
  });

  $data = compact('rows', 'user', 'totalUnits', 'occupiedCount', 'vacantCount', 'totalRent', 'filterLabel');
  $data['filterValue'] = $filterParam;

  if($request->download_type == 'pdf'){
    $logoPath = public_path('img/logo_pdf.jpg');
    $data['logo'] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
    $pdf = \PDF::loadView('backoffice::Reports.tenancy_details_report_pdf', $data)
              ->setPaper('a4', 'landscape');
    return $pdf->download('tenancy_details_report.pdf');
  } else {
    return \Excel::download(new TenancyDetailsReportExport($data), 'tenancy_details_report.xlsx');
  }
}

/*
 *
 *Tenancy Details by Building Wise ends
 *
 */

/*
 *
 *Tenancy Details MERA starts
 *
 */

public function showtenancyDetailsMeraReport(){
  $buildings = Building::active()->where('building_name', 'ILIKE', '%-MERA')->orderBy('building_name','asc')->get();
  return view('backoffice::Reports.tenancy_details_mera_report', compact('buildings'));
}

public function tenancyDetailsMeraReportDownload(Request $request){
  $user = Auth::user()->username;
  $buildingIds = $request->building_ids;
  $downloadType = $request->download_type;

  if(!$buildingIds || !$downloadType){
    return;
  }

  $logoPath = public_path('img/logo_pdf.jpg');
  $logo = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));

  $files = [];
  $tempDir = storage_path('app/temp/tenancy_details_mera_' . time());
  if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
  }

  foreach ($buildingIds as $buildingId) {
    $building = Building::find($buildingId);
    if(!$building){
      continue;
    }
    $buildingName = $building->building_name;

    $rows = DB::select("
      SELECT * FROM (
        SELECT DISTINCT tc.unit_usage, u.unit_vaccant_status, b.building_name, b.building_code,
               u.unit_no, ut.unit_types_name, tc.tenant_contract_no, tc.tenant_contract_rent,
               tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
               null::date AS termination_date, t.tenant_name,
               CASE WHEN u.unit_vaccant_status = 0 THEN 'Yes' ELSE 'No' END AS vaccant_status,
               pm.payment_method_code, tc.tenant_contract_last_paid_date,
               t.tenant_contact_no, bt.building_types_name, e.employee_name,
               'active' AS row_flag
        FROM units u
        LEFT JOIN buildings b ON b.id = u.building_id
        LEFT JOIN building_types bt ON bt.id = b.building_type_id
        LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
        LEFT JOIN tenant_contracts tc ON tc.unit_id = u.id
        LEFT JOIN tenant t ON t.id = tc.tenant_id
        LEFT JOIN (
          SELECT pb.building_id, employee_name FROM employees emp
          LEFT JOIN users us ON emp.id = us.user_type_id
          LEFT JOIN are_buildings are ON us.id = are.user_id
          LEFT JOIN preferred_buildings pb ON are.id = pb.are_building_id
          WHERE pb.assign_to IS NULL
        ) e ON e.building_id = b.id
        LEFT JOIN payment_method pm ON tc.tenant_contract_payment_type = pm.payment_method_index
        WHERE (tc.work_flow_processes_code = '104' OR u.unit_vaccant_status IN (1,2))
          AND tc.tenant_contract_status = '1' AND u.unit_status = '1' AND now() >= u.created_at

        UNION

        -- Occupied units with no currently-active contract (e.g. mid-renewal: previous
        -- contract expired, replacement contract not yet approved/started). Without this
        -- branch such units are silently dropped from the report entirely.
        SELECT * FROM (
          SELECT DISTINCT ON (u.id) tc.unit_usage, u.unit_vaccant_status, b.building_name, b.building_code,
                 u.unit_no, ut.unit_types_name, tc.tenant_contract_no, tc.tenant_contract_rent,
                 tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
                 null::date AS termination_date, t.tenant_name,
                 CASE WHEN u.unit_vaccant_status = 0 THEN 'Yes' ELSE 'No' END AS vaccant_status,
                 pm.payment_method_code, tc.tenant_contract_last_paid_date,
                 t.tenant_contact_no, bt.building_types_name, e.employee_name,
                 'pending' AS row_flag
          FROM units u
          LEFT JOIN buildings b ON b.id = u.building_id
          LEFT JOIN building_types bt ON bt.id = b.building_type_id
          LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
          LEFT JOIN tenant_contracts tc ON tc.unit_id = u.id
          LEFT JOIN tenant t ON t.id = tc.tenant_id
          LEFT JOIN (
            SELECT pb.building_id, employee_name FROM employees emp
            LEFT JOIN users us ON emp.id = us.user_type_id
            LEFT JOIN are_buildings are ON us.id = are.user_id
            LEFT JOIN preferred_buildings pb ON are.id = pb.are_building_id
            WHERE pb.assign_to IS NULL
          ) e ON e.building_id = b.id
          LEFT JOIN payment_method pm ON tc.tenant_contract_payment_type = pm.payment_method_index
          WHERE u.unit_vaccant_status IN (1,2) AND u.unit_status = '1' AND now() >= u.created_at
            AND NOT EXISTS (
              SELECT 1 FROM tenant_contracts tcx
              WHERE tcx.unit_id = u.id AND tcx.tenant_contract_status = '1'
            )
          ORDER BY u.id, tc.tenant_contract_effective_date DESC NULLS LAST, tc.id DESC
        ) occ_no_active

        UNION

        SELECT DISTINCT tc.unit_usage, u.unit_vaccant_status, b.building_name, b.building_code,
               u.unit_no, ut.unit_types_name, tc.tenant_contract_no, u.unit_base_rent::float,
               tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
               null::date AS termination_date, t.tenant_name,
               CASE WHEN u.unit_vaccant_status = 0 THEN 'Yes' ELSE 'No' END AS vaccant_status,
               pm.payment_method_code, tc.tenant_contract_last_paid_date,
               t.tenant_contact_no, bt.building_types_name, e.employee_name,
               'vacant' AS row_flag
        FROM units u
        LEFT JOIN buildings b ON b.id = u.building_id
        LEFT JOIN building_types bt ON bt.id = b.building_type_id
        LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
        LEFT JOIN tenant_contracts tc ON tc.unit_id = u.id
        LEFT JOIN tenant t ON t.id = tc.tenant_id
        LEFT JOIN (
          SELECT pb.building_id, employee_name FROM employees emp
          LEFT JOIN users us ON emp.id = us.user_type_id
          LEFT JOIN are_buildings are ON us.id = are.user_id
          LEFT JOIN preferred_buildings pb ON are.id = pb.are_building_id
          WHERE pb.assign_to IS NULL
        ) e ON e.building_id = b.id
        LEFT JOIN payment_method pm ON tc.tenant_contract_payment_type = pm.payment_method_index
        WHERE u.unit_vaccant_status = '0' AND tc.tenant_contract_no IS NULL
          AND u.unit_status = '1' AND now() >= u.created_at

        UNION

        SELECT DISTINCT tc.unit_usage, u.unit_vaccant_status, b.building_name, b.building_code,
               u.unit_no, ut.unit_types_name, a.tenant_contract_no, tc.tenant_contract_rent,
               tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
               a.terminationdate AS termination_date, t.tenant_name,
               CASE WHEN u.unit_vaccant_status = 0 THEN 'Yes' ELSE 'No' END AS vaccant_status,
               pm.payment_method_code, tc.tenant_contract_last_paid_date,
               t.tenant_contact_no, bt.building_types_name, e.employee_name,
               'vacant' AS row_flag
        FROM units u
        LEFT JOIN buildings b ON b.id = u.building_id
        LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
        LEFT JOIN building_types bt ON bt.id = b.building_type_id
        LEFT JOIN (
          SELECT pb.building_id, employee_name FROM employees emp
          LEFT JOIN users us ON emp.id = us.user_type_id
          LEFT JOIN are_buildings are ON us.id = are.user_id
          LEFT JOIN preferred_buildings pb ON are.id = pb.are_building_id
          WHERE pb.assign_to IS NULL
        ) e ON e.building_id = b.id
        LEFT JOIN (
          SELECT DISTINCT u.unit_code, MAX(tenant_contract_no) AS tenant_contract_no,
                 MAX(tr.termination_date) AS terminationdate,
                 MAX(tc.tenant_contract_valid_from_date) AS contract_start_date
          FROM units u
          LEFT JOIN tenant_contracts tc ON tc.unit_id = u.id
          LEFT JOIN termination tr ON tr.contract_id = tc.id
          WHERE tenant_contract_status = '0' AND u.unit_vaccant_status = '0' AND u.unit_status = '1'
          GROUP BY unit_code
        ) a ON a.unit_code = u.unit_code
        LEFT JOIN tenant_contracts tc ON tc.tenant_contract_no = a.tenant_contract_no
        LEFT JOIN payment_method pm ON tc.tenant_contract_payment_type = pm.payment_method_index
        LEFT JOIN tenant t ON t.id = tc.tenant_id
        WHERE (tc.work_flow_processes_code = '104' OR u.unit_vaccant_status = '0')
          AND tc.tenant_contract_status = '0' AND u.unit_status = '1' AND now() >= u.created_at
        ORDER BY unit_no
      ) x
      WHERE building_name = ?
      ORDER BY building_name, unit_no
    ", [$buildingName]);

    $collection    = collect($rows);
    $totalUnits    = $collection->count();
    $occupiedCount = $collection->where('unit_vaccant_status', '!=', 0)->count();
    $vacantCount   = $collection->where('unit_vaccant_status', 0)->count();
    $totalRent     = $collection->where('unit_vaccant_status', '!=', 0)->sum(function($row){
      return (float)$row->tenant_contract_rent;
    });

    $data = [
      'rows'          => $rows,
      'user'          => $user,
      'totalUnits'    => $totalUnits,
      'occupiedCount' => $occupiedCount,
      'vacantCount'   => $vacantCount,
      'totalRent'     => $totalRent,
      'filterLabel'   => 'Building Name',
      'filterValue'   => $buildingName,
    ];

    $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $buildingName);

    if($downloadType == 'pdf'){
      $data['logo'] = $logo;
      $fileName = 'tenancy_details_' . $safeName . '.pdf';
      $pdf = \PDF::loadView('backoffice::Reports.tenancy_details_report_pdf', $data)
                ->setPaper('a4', 'landscape');
      $filePath = $tempDir . '/' . $fileName;
      $pdf->save($filePath);
    } else {
      $fileName = 'tenancy_details_' . $safeName . '.xlsx';
      $filePath = $tempDir . '/' . $fileName;
      $content = \Excel::raw(new TenancyDetailsReportExport($data), \Maatwebsite\Excel\Excel::XLSX);
      file_put_contents($filePath, $content);
    }

    $files[] = ['path' => $filePath, 'name' => $fileName];
  }

  if(empty($files)){
    return;
  }

  // If only 1 building, return file directly
  if (count($files) === 1) {
    $file = $files[0];
    return response()->download($file['path'], $file['name'])->deleteFileAfterSend(true);
  }

  // Multiple buildings: create ZIP
  $zipFileName = 'Tenancy_Details_MERA_' . time() . '.zip';
  $zipPath = $tempDir . '/' . $zipFileName;
  $zip = new ZipArchive();
  if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
    foreach ($files as $file) {
      $zip->addFile($file['path'], $file['name']);
    }
    $zip->close();
  }

  // Cleanup individual files after zipping
  foreach ($files as $file) {
    if (file_exists($file['path'])) {
      unlink($file['path']);
    }
  }

  return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
}

/*
 *
 *Tenancy Details MERA ends
 *
 */

public function tenancyBuildingReportAutocompleteCode(Request $request){
  $key = $request->term;

  $building =   Building::active()->where('building_name', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(building_name) as value"))
  ->get();
  return $building ;
}
public function tenancyBuildingCodeAutocompleteCode(Request $request){
  $key = $request->term;

  $building =   Building::active()->where('building_code', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(building_code) as value"))
  ->get();
  return $building ;
}
public function buildingTypeAutocompleteCode(Request $request){
  $key = $request->term;

  $building =   BuildingType::active()
  ->whereHas('buildingInfo', function ($query)use($request) {
    $query->active();
  })->where('building_types_name', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(building_types_name) as value"))
  ->get();
  return $building ;
}
public function tenantNameReportAutocompleteCode(Request $request){
  $key = $request->term;

  $tenant =   Tenant::active()->whereHas('tenantContracts')->where('tenant_name', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(tenant_name) as value"))
  ->get();
  return $tenant ;
}

/*
 *
 *Tenancy Details by Building Wise ends
 *
 */

/*
 *
 *Tenant receivable as on (Date Range) starts
 *
 */
public function showtenantReceivablesReport(){
  $managementTypes = ManagementType::active()->get();
  return view('backoffice::reports.tenant_receivables_report',compact('managementTypes'));
}
public function tenantReceivablesReportPdf(Request $request){
 // dd($request->all());
  $db = config('report.database');
  $user = Auth::user()->username;
  $buildingname =  $request['building_name'];
  $buildingno =  $request['building_no'];
  $tenantname =  $request['tenant_name'];
  $managementtypesname =  $request['management_type'];
  $employee_name =  $request['are'];
  //$Date1 =  $request['start_date'];
  $Date2 =  $request['end_date'];
  $logo = getLogoPath();
  $jasper = new JasperPHP;
    // dd($date1);
  if(isset($request->download_type)){

    if($request->download_type == 'pdf'){

    if( !empty($Date2) && empty($buildingname) && empty($buildingno) && empty($tenantname) && empty($managementtypesname) && empty($employee_name)){ //Mandotory
     // Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_date.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_date.jrxml'),false,array('pdf'),array("user" => $user,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_date.pdf";
return redirect()->away($file);
print_r($r);
}
else{
/*      // Compile a JRXML to Jasper
$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_Combo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_Combo.jrxml'),false,array('pdf'),array("user" => $user,"buildingname" => $buildingname,"buildingno" => $buildingno,"tenantname" => $tenantname,"managementtypesname" => $managementtypesname,"employeename" => $employee_name,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_Combo.pdf";
return redirect()->away($file);
print_r($r);
}  

}else{

  if( !empty($Date2) && empty($buildingname) && empty($buildingno) && empty($tenantname) && empty($managementtypesname) && empty($employee_name)){
   //dd(4);
// Compile a JRXML to Jasper
    /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_excel_date.jrxml'))->execute();
    print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_excel_date.jrxml'),false,array('xlsx'),array("user" => $user,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_excel_date.xlsx";
return redirect()->away($file1);
}else{
// Compile a JRXML to Jasper
 /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_excel_combo.jrxml'))->execute();
  print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_excel_combo.jrxml'),false,array('xlsx'),array("user" => $user,"buildingname" => $buildingname,"buildingno" => $buildingno,"tenantname" => $tenantname,"managementtypesname" => $managementtypesname,"employeename" => $employee_name,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/R19RentReceivableasonDate_excel_combo.xlsx";
return redirect()->away($file1);
}
}  
}  
}

/*
 *
 *Tenant receivable as on (Date Range) starts
 *
 */

/*
 *
 *Tenant receivable v2 starts
 *
 */
public function showtenantReceivablesReportV2(){
  $managementTypes = ManagementType::active()->get();
  return view('backoffice::reports.tenant_receivables_report_v2', compact('managementTypes'));
}

public function tenantReceivablesReportPdfV2(Request $request){
  $user         = Auth::user()->username;
  $buildingname = $request['building_name'] ?? '';
  $buildingno   = $request['building_no'] ?? '';
  $tenantname   = $request['tenant_name'] ?? '';
  $managetype   = $request['management_type'] ?? '';
  $are          = $request['are'] ?? '';
  $date2        = $request['end_date'];
  $downloadType = $request['download_type'];

  $logoPath   = public_path('img/logo_pdf.jpg');
  $logo       = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));

  $dateOnly = !empty($date2) && empty($buildingname) && empty($buildingno) && empty($tenantname) && empty($managetype) && empty($are);

  if ($dateOnly) {
    $rows = DB::select("SELECT * FROM tenantrentreceivable_v2(?::date)", [$date2]);
  } else {
    $rows = DB::select(
      "SELECT * FROM tenantrentreceivablecompo_v2(?::date,?,?,?,?,?) ORDER BY buildingname, unit_code",
      [$date2, $tenantname, $buildingname, $buildingno, $managetype, $are]
    );
  }

  $data = [
    'rows'    => $rows,
    'date'    => $date2,
    'user'    => $user,
    'logo'    => $logo,
    'filters' => [
      'building_name'   => $buildingname,
      'building_no'     => $buildingno,
      'tenant_name'     => $tenantname,
      'management_type' => $managetype,
      'are'             => $are,
    ],
  ];

  if ($downloadType == 'pdf') {
    ini_set('memory_limit', '256M');
    set_time_limit(300);
    try {
      $fmtDate = function($d) { return $d ? date('d/m/Y', strtotime($d)) : ''; };

      require_once base_path('vendor/setasign/fpdf/fpdf.php');

      // Column widths in mm (A3 landscape = 420mm, margins 10 each = 400mm usable)
      // Arial 6pt ≈ 1.3mm per char. ContName/AREName need extra space for long names.
      $cols = [
        'Sl'       => 7,
        'Tenant'   => 38,
        'AgrmtNo'  => 26,
        'Unit'     => 14,
        'RentPM'   => 18,
        'From'     => 18,
        'To'       => 18,
        'PayMode'  => 20,
        'LastRecv' => 18,
        'TotalDue' => 20,
        'PaidTill' => 18,
        'AmtRecvd' => 20,
        'NetAmt'   => 20,
        'Contact'  => 20,
        'PDC'      => 10,
        'ChqClose' => 13,
        'ContName' => 34,
        'MgmtType' => 20,
        'AREName'  => 28,
      ];
      $totalW = array_sum($cols); // 390

      // Truncate text to fit cell (Arial 6pt ≈ 1.35mm/char)
      $fit = function($text, $w) {
        $max = (int)($w / 1.35);
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max - 1) . '~' : $text;
      };

      $pdf = new \FPDF('L', 'mm', 'A3');
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetMargins(10, 10, 10);
      $pdf->AddPage();
      $pdf->SetFont('Arial', '', 8);

      // --- Header ---
      $pdf->SetFont('Arial', 'B', 14);
      $pdf->Cell($totalW, 7, 'Rent Receivable as on Date', 0, 1, 'C');
      $pdf->SetFont('Arial', 'B', 11);
      $pdf->Cell($totalW, 6, date('d/m/Y', strtotime($date2)), 0, 1, 'C');
      $pdf->SetFont('Arial', '', 7);
      $pdf->Cell($totalW, 5, 'Generated: ' . date('d/m/Y H:i') . '   User: ' . $user, 0, 1, 'R');

      $filters = [];
      if ($buildingname) $filters[] = 'Building: ' . $buildingname;
      if ($buildingno)   $filters[] = 'Bldg No: '  . $buildingno;
      if ($tenantname)   $filters[] = 'Tenant: '   . $tenantname;
      if ($managetype)   $filters[] = 'Mgmt: '     . $managetype;
      if ($are)          $filters[] = 'ARE: '      . $are;
      if ($filters) {
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($totalW, 5, implode('   ', $filters), 0, 1, 'L');
      }
      $pdf->Ln(1);

      // --- Table header ---
      $pdf->SetFillColor(4, 93, 194);
      $pdf->SetTextColor(255, 255, 255);
      $pdf->SetFont('Arial', 'B', 6);
      $pdf->SetLineWidth(0.2);
      foreach ($cols as $label => $w) {
        $pdf->Cell($w, 6, $label, 1, 0, 'C', true);
      }
      $pdf->Ln();
      $pdf->SetTextColor(0, 0, 0);

      // --- Group rows by building ---
      $grouped = [];
      foreach ($rows as $row) {
        $grouped[$row->buildingname][] = $row;
      }
      unset($rows);

      $sl = 0;
      $grandTotal = $grandAmt = $grandNet = 0;
      $even = false;

      foreach ($grouped as $buildingName => $buildingRows) {
        // Building header row
        $pdf->SetFillColor(160, 201, 242);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($totalW, 5, $fit($buildingName, $totalW), 1, 1, 'L', true);

        $bTotal = $bAmt = $bNet = 0;
        $pdf->SetFont('Arial', '', 6);

        foreach ($buildingRows as $row) {
          $sl++;
          $bTotal += $row->totaldue        ?? 0;
          $bAmt   += $row->amount_received ?? 0;
          $bNet   += $row->netamtdue       ?? 0;

          $even = !$even;
          if ($even) {
            $pdf->SetFillColor(220, 235, 245);
          } else {
            $pdf->SetFillColor(255, 255, 255);
          }

          $cells = [
            'Sl'       => [$sl,                                                          'C'],
            'Tenant'   => [$fit($row->tenant_name    ?? '', $cols['Tenant']),             'L'],
            'AgrmtNo'  => [$row->contract_no    ?? '',                                   'C'],
            'Unit'     => [$row->unit_code       ?? '',                                   'C'],
            'RentPM'   => [number_format($row->rentper_month ?? 0, 3),                   'R'],
            'From'     => [$fmtDate($row->start_date),                                   'C'],
            'To'       => [$fmtDate($row->end_date),                                     'C'],
            'PayMode'  => [$fit($row->paymentmode ?? '', $cols['PayMode']),               'C'],
            'LastRecv' => [$fmtDate($row->receipt_date),                                 'C'],
            'TotalDue' => [number_format($row->totaldue        ?? 0, 3),                 'R'],
            'PaidTill' => [$fmtDate($row->lastpaid_till),                                    'C'],
            'AmtRecvd' => [number_format($row->amount_received ?? 0, 3),                   'R'],
            'NetAmt'   => [number_format($row->netamtdue       ?? 0, 3),                   'R'],
            'Contact'  => [$row->contact_no     ?? '',                                     'C'],
            'PDC'      => [$row->pdc             ?? '',                                     'C'],
            'ChqClose' => [$row->pdc_closed      ?? '',                                     'C'],
            'ContName' => [$fit($row->contact_person  ?? '', $cols['ContName']),            'L'],
            'MgmtType' => [$fit($row->management_type ?? '', $cols['MgmtType']),            'C'],
            'AREName'  => [$fit($row->employee_name   ?? '', $cols['AREName']),             'L'],
          ];
          foreach ($cells as $key => [$val, $align]) {
            $pdf->Cell($cols[$key], 5, $val, 1, 0, $align, true);
          }
          $pdf->Ln();
        }

        // Building subtotal
        $grandTotal += $bTotal;
        $grandAmt   += $bAmt;
        $grandNet   += $bNet;

        $subW = array_sum(array_slice(array_values($cols), 0, 9));
        $pdf->SetFillColor(232, 244, 252);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell($subW, 5, 'Total :', 1, 0, 'R', true);
        $pdf->Cell($cols['TotalDue'], 5, number_format($bTotal, 3), 1, 0, 'R', true);
        $pdf->Cell($cols['PaidTill'], 5, '', 1, 0, 'C', true);
        $pdf->Cell($cols['AmtRecvd'], 5, number_format($bAmt, 3), 1, 0, 'R', true);
        $pdf->Cell($cols['NetAmt'],   5, number_format($bNet, 3),  1, 0, 'R', true);
        $remW = array_sum(array_slice(array_values($cols), 13));
        $pdf->Cell($remW, 5, '', 1, 1, 'C', true);

        unset($buildingRows);
      }

      // Grand total
      $subW = array_sum(array_slice(array_values($cols), 0, 9));
      $pdf->SetFillColor(197, 220, 237);
      $pdf->SetFont('Arial', 'B', 7);
      $pdf->Cell($subW, 6, 'Grand Total :', 1, 0, 'R', true);
      $pdf->Cell($cols['TotalDue'], 6, number_format($grandTotal, 3), 1, 0, 'R', true);
      $pdf->Cell($cols['PaidTill'], 6, '', 1, 0, 'C', true);
      $pdf->Cell($cols['AmtRecvd'], 6, number_format($grandAmt, 3), 1, 0, 'R', true);
      $pdf->Cell($cols['NetAmt'],   6, number_format($grandNet, 3),  1, 0, 'R', true);
      $remW = array_sum(array_slice(array_values($cols), 13));
      $pdf->Cell($remW, 6, '', 1, 1, 'C', true);

      $content = $pdf->Output('S');
      return response($content, 200, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="tenant_receivable_v2_' . $date2 . '.pdf"',
      ]);
    } catch (\Throwable $e) {
      return response('PDF Error: ' . $e->getMessage(), 500);
    }
  } else {
    return \Excel::download(
      new TenantReceivableV2Export($data),
      'tenant_receivable_v2_' . $date2 . '.xlsx'
    );
  }
}
/*
 *
 *Tenant receivable v2 ends
 *
 */

/*
 *
 *Legal Receivable v2 starts
 *
 */
public function showLegalReceivablesReportV2(){
  $managementTypes = ManagementType::active()->get();
  return view('backoffice::Reports.legal_receivables_report_v2', compact('managementTypes'));
}

public function legalReceivablesReportPdfV2(Request $request){
  $user         = Auth::user()->username;
  $buildingname = $request['building_name'] ?? '';
  $buildingno   = $request['building_no'] ?? '';
  $tenantname   = $request['tenant_name'] ?? '';
  $managetype   = $request['management_type'] ?? '';
  $are          = $request['are'] ?? '';
  $date2        = $request['end_date'];
  $downloadType = $request['download_type'];

  $logoPath   = public_path('img/logo_pdf.jpg');
  $logo       = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));

  $dateOnly = !empty($date2) && empty($buildingname) && empty($buildingno) && empty($tenantname) && empty($managetype) && empty($are);

  if ($dateOnly) {
    $rows = DB::select("SELECT * FROM legalrentreceivable_v2(?::date)", [$date2]);
  } else {
    $rows = DB::select(
      "SELECT * FROM legalrentreceivablecompo_v2(?::date,?,?,?,?,?) ORDER BY buildingname, unit_code",
      [$date2, $tenantname, $buildingname, $buildingno, $managetype, $are]
    );
  }

  $data = [
    'rows'    => $rows,
    'date'    => $date2,
    'user'    => $user,
    'logo'    => $logo,
    'filters' => [
      'building_name'   => $buildingname,
      'building_no'     => $buildingno,
      'tenant_name'     => $tenantname,
      'management_type' => $managetype,
      'are'             => $are,
    ],
  ];

  if ($downloadType == 'pdf') {
    ini_set('memory_limit', '256M');
    set_time_limit(300);
    try {
      $fmtDate = function($d) { return $d ? date('d/m/Y', strtotime($d)) : ''; };

      require_once base_path('vendor/setasign/fpdf/fpdf.php');

      $cols = [
        'Sl'       => 7,
        'Tenant'   => 38,
        'AgrmtNo'  => 26,
        'Unit'     => 14,
        'RentPM'   => 18,
        'From'     => 18,
        'To'       => 18,
        'PayMode'  => 20,
        'LastRecv' => 18,
        'TotalDue' => 20,
        'PaidTill' => 18,
        'AmtRecvd' => 20,
        'NetAmt'   => 20,
        'Contact'  => 20,
        'PDC'      => 10,
        'ChqClose' => 13,
        'ContName' => 34,
        'MgmtType' => 20,
        'AREName'  => 28,
      ];
      $totalW = array_sum($cols);

      $fit = function($text, $w) {
        $max = (int)($w / 1.35);
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max - 1) . '~' : $text;
      };

      $pdf = new \FPDF('L', 'mm', 'A3');
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetMargins(10, 10, 10);
      $pdf->AddPage();
      $pdf->SetFont('Arial', '', 8);

      $pdf->SetFont('Arial', 'B', 14);
      $pdf->Cell($totalW, 7, 'Legal Rent Receivable as on Date', 0, 1, 'C');
      $pdf->SetFont('Arial', 'B', 11);
      $pdf->Cell($totalW, 6, date('d/m/Y', strtotime($date2)), 0, 1, 'C');
      $pdf->SetFont('Arial', '', 7);
      $pdf->Cell($totalW, 5, 'Generated: ' . date('d/m/Y H:i') . '   User: ' . $user, 0, 1, 'R');

      $filters = [];
      if ($buildingname) $filters[] = 'Building: ' . $buildingname;
      if ($buildingno)   $filters[] = 'Bldg No: '  . $buildingno;
      if ($tenantname)   $filters[] = 'Tenant: '   . $tenantname;
      if ($managetype)   $filters[] = 'Mgmt: '     . $managetype;
      if ($are)          $filters[] = 'ARE: '      . $are;
      if ($filters) {
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($totalW, 5, implode('   ', $filters), 0, 1, 'L');
      }
      $pdf->Ln(1);

      $pdf->SetFillColor(4, 93, 194);
      $pdf->SetTextColor(255, 255, 255);
      $pdf->SetFont('Arial', 'B', 6);
      $pdf->SetLineWidth(0.2);
      foreach ($cols as $label => $w) {
        $pdf->Cell($w, 6, $label, 1, 0, 'C', true);
      }
      $pdf->Ln();
      $pdf->SetTextColor(0, 0, 0);

      $grouped = [];
      foreach ($rows as $row) {
        $grouped[$row->buildingname][] = $row;
      }
      unset($rows);

      $sl = 0;
      $grandTotal = $grandAmt = $grandNet = 0;
      $even = false;

      foreach ($grouped as $buildingName => $buildingRows) {
        $pdf->SetFillColor(160, 201, 242);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($totalW, 5, $fit($buildingName, $totalW), 1, 1, 'L', true);

        $bTotal = $bAmt = $bNet = 0;
        $pdf->SetFont('Arial', '', 6);

        foreach ($buildingRows as $row) {
          $sl++;
          $bTotal += $row->totaldue        ?? 0;
          $bAmt   += $row->amount_received ?? 0;
          $bNet   += $row->netamtdue       ?? 0;

          $even = !$even;
          if ($even) {
            $pdf->SetFillColor(220, 235, 245);
          } else {
            $pdf->SetFillColor(255, 255, 255);
          }

          $cells = [
            'Sl'       => [$sl,                                                          'C'],
            'Tenant'   => [$fit($row->tenant_name    ?? '', $cols['Tenant']),             'L'],
            'AgrmtNo'  => [$row->contract_no    ?? '',                                   'C'],
            'Unit'     => [$row->unit_code       ?? '',                                   'C'],
            'RentPM'   => [number_format($row->rentper_month ?? 0, 3),                   'R'],
            'From'     => [$fmtDate($row->start_date),                                   'C'],
            'To'       => [$fmtDate($row->end_date),                                     'C'],
            'PayMode'  => [$fit($row->paymentmode ?? '', $cols['PayMode']),               'C'],
            'LastRecv' => [$fmtDate($row->receipt_date),                                 'C'],
            'TotalDue' => [number_format($row->totaldue        ?? 0, 3),                 'R'],
            'PaidTill' => [$fmtDate($row->lastpaid_till),                                'C'],
            'AmtRecvd' => [number_format($row->amount_received ?? 0, 3),                 'R'],
            'NetAmt'   => [number_format($row->netamtdue       ?? 0, 3),                 'R'],
            'Contact'  => [$row->contact_no     ?? '',                                   'C'],
            'PDC'      => [$row->pdc             ?? '',                                   'C'],
            'ChqClose' => [$row->pdc_closed      ?? '',                                   'C'],
            'ContName' => [$fit($row->contact_person  ?? '', $cols['ContName']),          'L'],
            'MgmtType' => [$fit($row->management_type ?? '', $cols['MgmtType']),          'C'],
            'AREName'  => [$fit($row->employee_name   ?? '', $cols['AREName']),           'L'],
          ];
          foreach ($cells as $key => [$val, $align]) {
            $pdf->Cell($cols[$key], 5, $val, 1, 0, $align, true);
          }
          $pdf->Ln();
        }

        $grandTotal += $bTotal;
        $grandAmt   += $bAmt;
        $grandNet   += $bNet;

        $subW = array_sum(array_slice(array_values($cols), 0, 9));
        $pdf->SetFillColor(232, 244, 252);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell($subW, 5, 'Total :', 1, 0, 'R', true);
        $pdf->Cell($cols['TotalDue'], 5, number_format($bTotal, 3), 1, 0, 'R', true);
        $pdf->Cell($cols['PaidTill'], 5, '', 1, 0, 'C', true);
        $pdf->Cell($cols['AmtRecvd'], 5, number_format($bAmt, 3), 1, 0, 'R', true);
        $pdf->Cell($cols['NetAmt'],   5, number_format($bNet, 3),  1, 0, 'R', true);
        $remW = array_sum(array_slice(array_values($cols), 13));
        $pdf->Cell($remW, 5, '', 1, 1, 'C', true);

        unset($buildingRows);
      }

      $subW = array_sum(array_slice(array_values($cols), 0, 9));
      $pdf->SetFillColor(197, 220, 237);
      $pdf->SetFont('Arial', 'B', 7);
      $pdf->Cell($subW, 6, 'Grand Total :', 1, 0, 'R', true);
      $pdf->Cell($cols['TotalDue'], 6, number_format($grandTotal, 3), 1, 0, 'R', true);
      $pdf->Cell($cols['PaidTill'], 6, '', 1, 0, 'C', true);
      $pdf->Cell($cols['AmtRecvd'], 6, number_format($grandAmt, 3), 1, 0, 'R', true);
      $pdf->Cell($cols['NetAmt'],   6, number_format($grandNet, 3),  1, 0, 'R', true);
      $remW = array_sum(array_slice(array_values($cols), 13));
      $pdf->Cell($remW, 6, '', 1, 1, 'C', true);

      $content = $pdf->Output('S');
      return response($content, 200, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="legal_receivable_v2_' . $date2 . '.pdf"',
      ]);
    } catch (\Throwable $e) {
      return response('PDF Error: ' . $e->getMessage(), 500);
    }
  } else {
    return \Excel::download(
      new LegalReceivableV2Export($data),
      'legal_receivable_v2_' . $date2 . '.xlsx'
    );
  }
}
/*
 *
 *Legal Receivable v2 ends
 *
 */

/*
 *
 *Cheque Return starts
 *
 */
public function showchequeReturnReport(){
  $buildings = Building::active()->get();
  $employees = employee::whereHas('user', function ($query){
    $query->active()->whereHas('Are', function ($query){
      $query->whereHas('buildingNamesExist');
    });
  })->get();
  return view('backoffice::reports.cheque_return_report',compact('buildings','employees'));
}

public function chequeReturnReportPdf(Request $request){


 $db = config('report.database');
 $username = Auth::user()->username;
 $building =  $request['building_name'];
 if(!empty($building)){
  $building_name = Building::where('id',$building)->first()->building_name;
 }
 else{
  $building_name = '';
 }
 $employee =  $request['are'];
 if(!empty($employee)){
  $employee_name = Employee::where('id',$employee)->first()->employee_name;
 }
 else{
  $employee_name = '';
 }
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){

    if(!empty($Date1) && !empty($Date2) && empty($building_name) && empty($employee_name)){ //Mandotory

// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/cheque_return_statement.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/cheque_return_statement.jrxml'),false,array('pdf'),array("username" => $username,"building_name" => $building_name,"employee_name" => $employee_name,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();
$file= getReportUrl()."vendor/cossou/jasperphp/examples/cheque_return_statement.pdf";
return redirect()->away($file);
print_r($r);
}else{
  // Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/cheque_return_statement_compo.jrxml'))->execute();
print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/cheque_return_statement_compo.jrxml'),false,array('pdf'),array("username" => $username,"building_name" => $building_name,"employee_name" => $employee_name,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();
$file= getReportUrl()."vendor/cossou/jasperphp/examples/cheque_return_statement_compo.pdf";
return redirect()->away($file);
print_r($r);
}  
}else{

   if(!empty($Date1) && !empty($Date2) && empty($building_name) && empty($employee_name)){ //Mandotory
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/cheque_return_statement_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/cheque_return_statement_excel.jrxml'),false,array('xlsx'),array("username" => $username,"building_name" => $building_name,"employee_name" => $employee_name,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();
$file1= getReportUrl()."vendor/cossou/jasperphp/examples/cheque_return_statement_excel.xlsx";
    return redirect()->away($file1);
  }else{
   /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/cheque_return_statement_excel_compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/cheque_return_statement_excel_compo.jrxml'),false,array('xlsx'),array("username" => $username,"building_name" => $building_name,"employee_name" => $employee_name,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();
$file1= getReportUrl()."vendor/cossou/jasperphp/examples/cheque_return_statement_excel_compo.xlsx";
    return redirect()->away($file1);
  }
}
}  
}

/*
 *
 *Cheque Return  ends
 *
 */

/*
 *
 *Unit Take Over Status(From------To------) starts
 *
 */
public function showUnitTakeoverReport(){
  return view('backoffice::reports.unit_takeover_report');
}
public function unitTakeoverReportPdf(Request $request){
 $db = config('report.database');
 $username = Auth::user()->username;
 $building_name =  $request['building_name'];
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $Date3 =  $request['handover_date'];

 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);

 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){

    if(!empty($Date1) && !empty($Date2) && empty($building_name) && empty($Date3)){ //Mandotory
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_date.jrxml'))->execute();
print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_date.jrxml'),false,array('pdf'),array("username" => $username,"building_name" => $building_name,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/unit_take_over_status_date.pdf";
return redirect()->away($file);
print_r($r);  
}
else if(!empty($Date1) && !empty($Date2) && empty(!$building_name) && empty($Date3)){
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_compo.jrxml'),false,array('pdf'),array("username" => $username,"building_name" => $building_name,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/unit_take_over_status_compo.pdf";
return redirect()->away($file);
print_r($r);
}
else{
  // Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_compo.jrxml'),false,array('pdf'),array("username" => $username,"building_name" => $building_name,"Date1" => $Date1,"Date2" => $Date2,"Date3" => $Date3,"logo" => $logo),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/unit_take_over_status_compo.pdf";
return redirect()->away($file);
print_r($r);
}
}else{

  if(!empty($Date1) && !empty($Date2) && empty($building_name) && empty($Date3)){ //Mandotory
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_excel_date.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_excel_date.jrxml'),false,array('xlsx'),array("username" => $username,"building_name" => $building_name,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/unit_take_over_status_excel_date.xlsx";
return redirect()->away($file1);
}else if(!empty($Date1) && !empty($Date2) && empty(!$building_name) && empty($Date3)){
  /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_excel_compo.jrxml'))->execute();
    print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
    $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_excel_compo.jrxml'),false,array('xlsx'),array("username" => $username,"building_name" => $building_name,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

    $file1= getReportUrl()."vendor/cossou/jasperphp/examples/unit_take_over_status_excel_compo.xlsx";
    return redirect()->away($file1);
  }
else{
    /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_excel_compo.jrxml'))->execute();
    print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
    $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/unit_take_over_status_excel_compo.jrxml'),false,array('xlsx'),array("username" => $username,"building_name" => $building_name,"Date1" => $Date1,"Date2" => $Date2,"Date3" => $Date3,"logo" => $logo),$db)->execute();

    $file1= getReportUrl()."vendor/cossou/jasperphp/examples/unit_take_over_status_excel_compo.xlsx";
    return redirect()->away($file1);
  }
}
}
}
/*
 *
 *Unit Take Over Status(From------To------) ends
 *
 */
 /*
 *
 *Legal Case of (Month, year) starts
 *
 */
 public function showLegalCaseReport(){
  return view('backoffice::reports.legal_case_report');
}
public function legalCaseReportPdf(Request $request){
 $db = config('report.database');
 $user = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
 if(isset($request->download_type)){

  if($request->download_type == 'pdf'){

// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/legal_case.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/legal_case.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

//$file= base_path(). "/vendor/cossou/jasperphp/examples/legal_case.pdf";
//return response()->file($file); 


$file= getReportUrl()."vendor/cossou/jasperphp/examples/legal_case.pdf";
       // dd($file);
return redirect()->away($file);
print_r($r);  
}
else{
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/legal_case_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/legal_case_excel.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/legal_case_excel.xlsx";
    return redirect()->away($file1);  
}
}  
}
/*
 *
 *Legal Case of (Month, year) ends
 *
 */
public function buildingNoReportAutocompleteCode(Request $request){
  $key = $request->term;

  $building =   Building::active()
  ->whereHas('tenantContract', function ($query)use($request) {
    $query->where('tenant_contract_status',1);
  })->where('building_no', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(building_no) as value"))
  ->get();
  return $building ;
}
/*
*
*Tenant Company starts
*/
public function tenantCompanyAutocompleteCode(Request $request){
  $key = $request->term;

  $tenant =   Tenant::where('tenant_type_id',2)->active()
  ->whereHas('tenantContracts', function ($query)use($request) {
    $query->where('tenant_contract_status',1);
  })->where('tenant_name', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(tenant_name) as value"))
  ->get();
  return $tenant ;
}
/*
*
*Tenant Company starts
*/

/*
*
*Tenant Code Company starts
*/
public function tenantCodeCompanyAutocompleteCode(Request $request){
  $key = $request->term;

  $tenant =   Tenant::where('tenant_type_id',2)->active()
  ->whereHas('tenantContracts', function ($query)use($request) {
    $query->where('tenant_contract_status',1);
  })->where('tenant_code', 'ILIKE', '%'.$key.'%')
  ->select('id AS ids',DB::raw("CONCAT(tenant_code) as value"))
  ->get();
  return $tenant ;
}
/*
*
*Tenant Code Company starts
*/
/*
 *
 *Rental Income starts
 *
 */
public function showRentalIncomeReport(){
  $buildings = Building::where('management_id','=',1)->active()->orderBy('building_name','asc')->get();
  // $buildings = Building::active()->orderBy('building_name','asc')
  // ->get();
  return view('backoffice::reports.rental_income_report',compact('buildings'));
}
public function rentalIncomeReportPdf(Request $request){
 $db = config('report.database');
 $user = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $building =  $request['building_name'];
 if(!empty($building)){
  $building_name = Building::where('id',$building)->first()->building_name;
}
else{
  $building_name = '';
}
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
if(isset($request->download_type)){

    if($request->download_type == 'pdf'){

       if(!empty($Date1) && !empty($Date2) && empty($building_name)){ //Mandotory
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Rental_Income.jrxml'))->execute();
print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Rental_Income.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();
 
  $file= getReportUrl()."vendor/cossou/jasperphp/examples/Rental_Income.pdf";
return redirect()->away($file);
     //  print_r($r);
     }
       else{
        // Compile a JRXML to Jasper
/* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Rental_Income_Compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
      $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Rental_Income_Compo.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();
     
     $file= getReportUrl()."vendor/cossou/jasperphp/examples/Rental_Income_Compo.pdf";
return redirect()->away($file);
      // print_r($r);
       }
   }else{

    if(!empty($Date1) && !empty($Date2) && empty($building_name)){ //Mandotory
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Rental_Income_Excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Rental_Income_Excel.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

       $file1= getReportUrl()."vendor/cossou/jasperphp/examples/Rental_Income_Excel.xlsx";
    return redirect()->away($file1);
  }else{
      /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Rental_Income_Excel_Compo.jrxml'))->execute();
print_r($a);*/ 
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
     $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Rental_Income_Excel_Compo.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

       $file1= getReportUrl()."vendor/cossou/jasperphp/examples/Rental_Income_Excel_Compo.xlsx";
    return redirect()->away($file1);
   }
  }
}   
}

/*
 *
 *Rental Income ends
 *
 */

/*
 *
 *Expense Details starts
 *
 */
public function showExpenseDetailsReport(){
  $buildings = Building::active()->orderBy('building_name','asc')
  ->get();
  return view('backoffice::reports.expense_details_report',compact('buildings'));
}
public function expenseDetailsReportPdf(Request $request){
 $db = config('report.database');
 $user = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $building =  $request['building_name'];
 if(!empty($building)){
  $building_name = Building::where('id',$building)->first()->building_name;
}
else{
  $building_name = '';
}
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
 if(isset($request->report_type)){

  if($request->report_type == 1){

if(isset($request->download_type)){

    if($request->download_type == 'pdf'){

       if(!empty($Date1) && !empty($Date2) && empty($building_name)){ //Mandotory
// Compile a JRXML to Jasper
/* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Summary.jrxml'))->execute();
print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
      $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Summary.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();
 
  $file= getReportUrl()."vendor/cossou/jasperphp/examples/Expense_Details_Summary.pdf";
return redirect()->away($file);
      // print_r($r);
     }
       else{
        // Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Summary_Compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Summary_Compo.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();
     
     $file= getReportUrl()."vendor/cossou/jasperphp/examples/Expense_Details_Summary_Compo.pdf";
return redirect()->away($file);
      // print_r($r);
       }
   }else{

    if(!empty($Date1) && !empty($Date2) && empty($building_name)){ //Mandotory
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Summary_Excel.jrxml'))->execute();
print_r($a);*/ 
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Summary_Excel.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

       $file1= getReportUrl()."vendor/cossou/jasperphp/examples/Expense_Details_Summary_Excel.xlsx";
    return redirect()->away($file1);
  }else{
  /*  $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Summary_Excel_Compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Summary_Excel_Compo.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

       $file1= getReportUrl()."vendor/cossou/jasperphp/examples/Expense_Details_Summary_Excel_Compo.xlsx";
    return redirect()->away($file1);
   }
  }
}
}else{
  if(isset($request->download_type)){

    if($request->download_type == 'pdf'){

       if(!empty($Date1) && !empty($Date2) && empty($building_name)){ //Mandotory
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Expense_Details.jrxml'))->execute();
print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Expense_Details.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();
 
  $file= getReportUrl()."vendor/cossou/jasperphp/examples/Expense_Details.pdf";
return redirect()->away($file);
      // print_r($r);
     }
       else{
        // Compile a JRXML to Jasper
 /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
      $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Compo.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();
     
     $file= getReportUrl()."vendor/cossou/jasperphp/examples/Expense_Details_Compo.pdf";
return redirect()->away($file);
       print_r($r);
       }
   }else{

    if(!empty($Date1) && !empty($Date2) && empty($building_name)){ //Mandotory
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Excel.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

       $file1= getReportUrl()."vendor/cossou/jasperphp/examples/Expense_Details_Excel.xlsx";
    return redirect()->away($file1);
  }else{
   /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Excel_Compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Expense_Details_Excel_Compo.jrxml'),false,array('xlsx'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

       $file1= getReportUrl()."vendor/cossou/jasperphp/examples/Expense_Details_Excel_Compo.xlsx";
    return redirect()->away($file1);
   }
  }
}
}
}   
}

/*
 *
 *Expense Details ends
 *
 */

/*
 *
 * Monthly Tenancy Details starts
 *
 */

public function showMonthlyTenancyReport(){
  $locations = Location::active()->get();
  $managementTypes = ManagementType::active()->get();
  return view('backoffice::Reports.monthly_tenancy_report',compact('locations','managementTypes'));
}

public function monthlyTenancyReportPdf(Request $request){
  $user = Auth::user()->username;
  $as_on_date = $request['as_on_date'];
  $location_id = !empty($request['location_id']) ? $request['location_id'] : '0';
  $management_id = !empty($request['management_id']) ? $request['management_id'] : '0';

  $rows = DB::select("
      SELECT b.building_name, l.locations_name, m.management_types_name,
             COUNT(u.id) AS total_units,
             SUM(CASE WHEN u.unit_vaccant_status != 0 THEN 1 ELSE 0 END) AS occupied,
             SUM(CASE WHEN u.unit_vaccant_status = 0 THEN 1 ELSE 0 END) AS vacant,
             CASE WHEN COUNT(u.id) > 0 THEN
               ROUND((SUM(CASE WHEN u.unit_vaccant_status != 0 THEN 1 ELSE 0 END)::numeric / COUNT(u.id)::numeric) * 100, 2)
             ELSE 0 END AS occupancy_pct
      FROM buildings b
      JOIN locations l ON b.location_id = l.id
      JOIN management_types m ON b.management_id = m.id
      JOIN units u ON u.building_id = b.id AND u.unit_status = 1
      WHERE b.building_status = 1
        AND (? = '0' OR b.location_id = CAST(? AS INTEGER))
        AND (? = '0' OR b.management_id = CAST(? AS INTEGER))
      GROUP BY b.building_name, l.locations_name, m.management_types_name
      ORDER BY l.locations_name, b.building_name
  ", [$location_id, $location_id, $management_id, $management_id]);

  $totalUnits    = collect($rows)->sum('total_units');
  $totalOccupied = collect($rows)->sum('occupied');
  $totalVacant   = collect($rows)->sum('vacant');
  $totalOccupancyPct = $totalUnits > 0 ? round(($totalOccupied / $totalUnits) * 100, 2) : 0;

  $groupedRows = collect($rows)->groupBy('locations_name')->map(function ($locationRows, $locationName) {
      $locUnits    = $locationRows->sum('total_units');
      $locOccupied = $locationRows->sum('occupied');
      $locVacant   = $locationRows->sum('vacant');
      $locOccPct   = $locUnits > 0 ? round(($locOccupied / $locUnits) * 100, 2) : 0;
      return [
          'rows'          => $locationRows,
          'totalUnits'    => $locUnits,
          'totalOccupied' => $locOccupied,
          'totalVacant'   => $locVacant,
          'occupancyPct'  => $locOccPct,
      ];
  });

  $data = compact('groupedRows', 'user', 'as_on_date', 'totalUnits', 'totalOccupied', 'totalVacant', 'totalOccupancyPct');

  if(isset($request->download_type)){

    if($request->download_type == 'pdf'){
      $logoPath = public_path('img/logo_pdf.jpg');
      $data['logo'] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
      $pdf = \PDF::loadView('backoffice::Reports.monthly_tenancy_report_pdf', $data)
                  ->setPaper('a4', 'landscape');
      return $pdf->download('monthly_tenancy_report.pdf');
    }else{
      return \Excel::download(new MonthlyTenancyReportExport($data), 'monthly_tenancy_report.xlsx');
    }
  }
}

/*
 *
 * Monthly Tenancy Details ends
 *
 */

/*
 *
 * MERA Rent Receipt Report starts
 *
 */

public function showMeraRentReceiptReport(){
    $buildings = Building::active()->where('building_name', 'ILIKE', '%-MERA')->orderBy('building_name','asc')->get();
    return view('backoffice::Reports.mera_rent_receipt_report', compact('buildings'));
}

public function meraRentReceiptReportDownload(Request $request){
    $user = Auth::user()->username;
    $reportMonth = Carbon::createFromFormat('Y-m', $request->report_month);
    $startDate = $reportMonth->copy()->startOfMonth()->format('Y-m-d');
    $endDate = $reportMonth->copy()->endOfMonth()->format('Y-m-d');
    $period = $reportMonth->format('F Y');
    $buildingIds = $request->building_ids;
    $downloadType = $request->download_type;

    $logoPath = public_path('img/logo_pdf.jpg');
    $logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));

    $files = [];
    $tempDir = storage_path('app/temp/mera_rent_receipt_' . time());
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    foreach ($buildingIds as $buildingId) {
        $rows = DB::select("
            SELECT b.building_name, rg.receipts_generation_receipt_no AS doc_no,
                   rg.receipts_generation_receipt_date AS doc_date,
                   u.unit_no, tc.tenant_contract_rent AS rent,
                   t.tenant_code, t.tenant_name,
                   CASE WHEN rg.receipts_generation_payment_method = 1 THEN 'Cheque'
                        WHEN rg.receipts_generation_payment_method = 3 THEN 'Bank Transfer'
                        ELSE 'Cash' END AS payment_method,
                   rg.receipts_generation_cheque_no AS cheque_no,
                   rg.receipts_generation_eff_from AS eff_from,
                   rg.receipts_generation_eff_to AS eff_to,
                   rg.receipts_generation_amt AS amount,
                   ut.unit_types_name AS unit_type
            FROM receipts_generation rg
            LEFT JOIN tenant_contracts tc ON rg.tenant_contract_id = tc.id
            LEFT JOIN tenant t ON tc.tenant_id = t.id
            LEFT JOIN units u ON tc.unit_id = u.id
            LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
            LEFT JOIN buildings b ON u.building_id = b.id
            WHERE rg.receipts_generation_status <> 2
              AND rg.receipts_generation_receipt_date BETWEEN ? AND ?
              AND rg.receipts_generation_type = '0'
              AND rg.deleted_at IS NULL
              AND b.id = ?
            ORDER BY u.unit_no
        ", [$startDate, $endDate, $buildingId]);

        $buildingName = count($rows) > 0 ? $rows[0]->building_name : Building::find($buildingId)->building_name;
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $buildingName);
        $monthName = $reportMonth->format('F');
        $year = $reportMonth->format('Y');

        $data = [
            'rows' => $rows,
            'buildingName' => $buildingName,
            'period' => $period,
            'user' => $user,
            'logo' => $logoBase64,
        ];

        if ($downloadType == 'pdf') {
            $fileName = $safeName . '_' . $monthName . '_' . $year . '.pdf';
            $pdf = \PDF::loadView('backoffice::Reports.mera_rent_receipt_pdf', $data)
                        ->setPaper('a4', 'landscape');
            $filePath = $tempDir . '/' . $fileName;
            $pdf->save($filePath);
        } else {
            $fileName = $safeName . '_' . $monthName . '_' . $year . '.xlsx';
            $filePath = $tempDir . '/' . $fileName;
            $content = \Excel::raw(new MeraRentReceiptReportExport($data), \Maatwebsite\Excel\Excel::XLSX);
            file_put_contents($filePath, $content);
        }

        $files[] = ['path' => $filePath, 'name' => $fileName];
    }

    // If only 1 building, return file directly
    if (count($files) === 1) {
        $file = $files[0];
        return response()->download($file['path'], $file['name'])->deleteFileAfterSend(true);
    }

    // Multiple buildings: create ZIP
    $zipFileName = 'MERA_Rent_Receipt_' . $reportMonth->format('F_Y') . '.zip';
    $zipPath = $tempDir . '/' . $zipFileName;
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
        foreach ($files as $file) {
            $zip->addFile($file['path'], $file['name']);
        }
        $zip->close();
    }

    // Cleanup individual files after zipping
    foreach ($files as $file) {
        if (file_exists($file['path'])) {
            unlink($file['path']);
        }
    }

    return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
}

/*
 *
 * MERA Rent Receipt Report ends
 *
 */

/*
 * Leasing Consultant Performance Dashboard
 */

public function showLeasingConsultantPerformance()
{
    return view('backoffice::Reports.leasing_consultant_performance');
}

public function leasingConsultantPerformanceData(Request $request)
{
    $period = $request->get('period', 'YTD');
    $year = $request->get('year', date('Y'));

    // Determine date range
    switch ($period) {
        case 'Q1':
            $startDate = "$year-01-01";
            $endDate = "$year-03-31";
            break;
        case 'Q2':
            $startDate = "$year-04-01";
            $endDate = "$year-06-30";
            break;
        case 'Q3':
            $startDate = "$year-07-01";
            $endDate = "$year-09-30";
            break;
        case 'Q4':
            $startDate = "$year-10-01";
            $endDate = "$year-12-31";
            break;
        case 'Custom':
            $startDate = $request->get('start_date', "$year-01-01");
            $endDate = $request->get('end_date', date('Y-m-d'));
            break;
        default: // YTD
            $startDate = "$year-01-01";
            $endDate = ($year == date('Y')) ? date('Y-m-d') : "$year-12-31";
            break;
    }

    $periodLabel = $period === 'Custom'
        ? Carbon::parse($startDate)->format('d M Y') . ' - ' . Carbon::parse($endDate)->format('d M Y')
        : $period . ' ' . $year . ' (' . Carbon::parse($startDate)->format('d M') . ' - ' . Carbon::parse($endDate)->format('d M Y') . ')';

    // Base query - using view_tenant_stage with DISTINCT id (matching Jasper report)
    $contracts = DB::select("
        SELECT DISTINCT ON (vt.id)
            vt.id,
            vt.employee_name,
            vt.building_name,
            vt.tenant_contract_rent,
            vt.tenant_contract_start_date
        FROM view_tenant_stage vt
        WHERE vt.work_flow_processes_code = '108'
          AND vt.sale_work_flow_processes_code = '108'
          AND vt.sales_enquiry_direct_contract = '1'
          AND vt.status = '1'
          AND vt.tenant_contract_start_date BETWEEN ? AND ?
    ", [$startDate, $endDate]);
    $contracts = collect($contracts);

    // KPIs
    $totalUnits = $contracts->count();
    $totalRent = $contracts->sum('tenant_contract_rent');
    $avgRent = $totalUnits > 0 ? round($totalRent / $totalUnits) : 0;

    $byEmployee = $contracts->groupBy('employee_name');
    $employeeUnits = $byEmployee->map(function ($group, $name) {
        $count = $group->count();
        $total = round($group->sum('tenant_contract_rent'), 3);
        return ['name' => $name, 'count' => $count, 'total' => $total, 'avg' => $count > 0 ? round($total / $count) : 0];
    })->values();

    $topByUnits = $employeeUnits->sortByDesc('count')->first();
    $topByAmount = $employeeUnits->sortByDesc('total')->first();

    // Leaderboard - top 5 by units
    $leaderboard = $employeeUnits->sortByDesc('count')->take(5)->values();

    // Rent by employee (all)
    $rentByEmployee = $employeeUnits->sortByDesc('total')->values();

    // Rent distribution buckets
    $buckets = [
        ['bucket' => '≤100', 'min' => 0, 'max' => 100],
        ['bucket' => '101-150', 'min' => 101, 'max' => 150],
        ['bucket' => '151-200', 'min' => 151, 'max' => 200],
        ['bucket' => '201-300', 'min' => 201, 'max' => 300],
        ['bucket' => '301+', 'min' => 301, 'max' => PHP_INT_MAX],
    ];
    $rentDistribution = collect($buckets)->map(function ($b) use ($contracts) {
        return [
            'bucket' => $b['bucket'],
            'count' => $contracts->filter(function ($c) use ($b) {
                return $c->tenant_contract_rent >= $b['min'] && $c->tenant_contract_rent <= $b['max'];
            })->count(),
        ];
    });

    // Top buildings by units
    $topBuildings = $contracts->groupBy('building_name')
        ->map(function ($group, $name) {
            return ['name' => $name, 'count' => $group->count()];
        })
        ->sortByDesc('count')
        ->take(5)
        ->values();

    return response()->json([
        'kpi' => [
            'totalUnits' => $totalUnits,
            'totalRent' => round($totalRent, 3),
            'avgRent' => $avgRent,
            'topByUnitsName' => $topByUnits ? $topByUnits['name'] : null,
            'topByUnitsCount' => $topByUnits ? $topByUnits['count'] : null,
            'topByAmountName' => $topByAmount ? $topByAmount['name'] : null,
            'topByAmountValue' => $topByAmount ? round($topByAmount['total'], 3) : null,
        ],
        'leaderboard' => $leaderboard,
        'rentByEmployee' => $rentByEmployee,
        'rentDistribution' => $rentDistribution,
        'topBuildings' => $topBuildings,
        'employeeSummary' => $employeeUnits->sortByDesc('count')->values(),
        'dateRange' => $periodLabel,
    ]);
}

/*
 *
 * Normal Management Report v2
 *
 */

// ── Allowed buildings for Normal Management Report v2 (filtered by ID) ────
private static $nmrV2BuildingIds = [
    430,  // 1323-Al Falaj
    217,  // Aashirwad Bldg
    320,  // Abdul Malik-Garage Plot
    319,  // Abdul Malik-Ruwi Bldg
    321,  // Abdul Malik-Rex Villas
    466,  // Adil Taqi-3 Flats
    67,   // Al Aroos
    470,  // Al Azain Bldg
    434,  // Al Hail-01 Bldg
    437,  // Al Hail-02 Bldg
    439,  // Al Hail-03 Bldg
    116,  // Al Khuwair House
    83,   // Al Manara Bldg
    303,  // Al Mir Bldg
    293,  // Al Munther Bldg
    108,  // Al Noor Bldg
    465,  // Al Qandeel-2 Bldg
    198,  // Al Rawahi Bldg
    427,  // Al Sahwa Square
    115,  // Al Suleyf Bldg
    435,  // Al Zahra Building
    18,   // Ali Bldg
    440,  // Ali-02 Bldg
    442,  // Ali-03 Bldg
    438,  // Anood Villas
    467,  // Avenue-6 Bldg
    323,  // Bait Abdullah
    243,  // Bait Fatma
    316,  // Bait Naseeb
    201,  // Bait Rafey
    288,  // Bait Rawan
    464,  // Bank Dhofar-Ansab
    461,  // Bank Dhofar-Darsait
    463,  // Bank Dhofar-Walja
    462,  // Bank Dhofar-Wearhouse
    423,  // Baraq Al Reef
    468,  // Bustan-171 Bldg
    66,   // Butterfly Bldg
    426,  // Dar Al Zain Complex
    54,   // Darsait Villas
    88,   // Dr.Nasser Bldg
    294,  // Ewan-M1 Bldg
    295,  // Falaj New Bldg
    299,  // GDS Bldg
    306,  // GETCO Towers
    469,  // Ghala-109 Bldg
    89,   // Habib House
    220,  // Haitham Jaffer Bldg
    322,  // Iman House
    26,   // KHA-1
    53,   // Karama Bldg
    413,  // Mahmood Bldg
    448,  // Mehdi Bldg
    424,  // Oman S1
    425,  // Oman S2
    206,  // RCA-120 Bldg
    105,  // Radhiya Bldg
    107,  // SS Ppty-Wadi Kabir
    402,  // Saif Wahaibi-Darsait Res.
    403,  // Saif Wahaibi-Darsait Shops
    404,  // Saif Wahaibi-Muttrah Bldg
    405,  // Saif Wahaibi-Qurum Villas
    187,  // Sama Bldg
    209,  // Samer Bldg (Samar Bldg)
    318,  // SMC Mabella Bldg
    208,  // Stella Bldg
    133,  // Takaful-2 Bldg
    429,  // Al Themar Bldg (Al Thamer Building)
    114,  // Mazoon Bldg (Mazoun Bldg)
    245,  // Narjis Bldg (Nargis Bldg)
    90,   // QCC (QSC)
    113,  // Trust Bldg
    // ── New AL- series buildings ──────────────────────────────
    474,  // AL-123-Amerat
    477,  // AL-1431-Mumtaz
    471,  // AL-192-Muttrah
    478,  // AL-2119-Ghubra
    472,  // AL-2162-Al Khuwair
    475,  // AL-249-Ghubra
    479,  // AL-3101-H.Road
    476,  // AL-352-Azaiba Villas
    481,  // AL-3744-Ruwi
    473,  // AL-37-Ruwi (requested as AL-37-Rex Road — verify name)
    480,  // AL-57-Walja
];

public function showNormalManagementReportV2()
{
    $buildings = Building::whereIn('id', self::$nmrV2BuildingIds)
        ->active()->orderBy('building_name', 'asc')->get();
    return view('backoffice::Reports.normal_management_report_v2', compact('buildings'));
}

public function normalManagementReportV2Generate(Request $request)
{
    ini_set('memory_limit', '512M');
    set_time_limit(600);

    $user        = Auth::user()->username;
    $buildingId  = $request->input('building_id', 'all');
    $month       = $request->input('month', 'all');
    $year        = (int)$request->input('year', date('Y'));

    // Determine which buildings to process
    if ($buildingId === 'all') {
        $buildings = Building::whereIn('id', self::$nmrV2BuildingIds)
            ->active()->orderBy('building_name', 'asc')->get();
    } else {
        $buildings = Building::where('id', $buildingId)->get();
    }

    // Determine which months to populate.
    // For the current year, only populate up to the last completed month.
    if ($month === 'all') {
        $currentYear  = (int)date('Y');
        $currentMonth = (int)date('n');
        if ($year === $currentYear) {
            $lastMonth = $currentMonth - 1;
            $monthsToPopulate = $lastMonth >= 1 ? range(1, $lastMonth) : [];
        } else {
            $monthsToPopulate = range(1, 12);
        }
    } else {
        $monthsToPopulate = [(int)$month];
    }

    $tempDir = storage_path('app/temp/normal_mgmt_' . time());
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    $files = [];

    foreach ($buildings as $building) {
        $monthData = [];

        // ── Static per-building counts (queried once, not repeated each month) ──
        $totalUnits = DB::table('units')
            ->where('building_id', $building->id)
            ->where('unit_status', 1)
            ->count();

        $totalResidentialUnits = DB::table('units as u')
            ->join('unit_types as ut', 'ut.id', '=', 'u.unit_type_id')
            ->where('u.building_id', $building->id)
            ->where('u.unit_status', 1)
            ->whereRaw("(ut.unit_types_name ILIKE '%residential%' OR ut.unit_types_name ILIKE '%apart%' OR ut.unit_types_name ILIKE '%flat%' OR ut.unit_types_name ILIKE '%villa%' OR ut.unit_types_name ILIKE '%BR%' OR ut.unit_types_name ILIKE '%studio%' OR ut.unit_types_name = 'PH')")
            ->count();

        $totalCommercialUnits = DB::table('units as u')
            ->join('unit_types as ut', 'ut.id', '=', 'u.unit_type_id')
            ->where('u.building_id', $building->id)
            ->where('u.unit_status', 1)
            ->whereRaw("(ut.unit_types_name ILIKE '%commercial%' OR ut.unit_types_name ILIKE '%shop%' OR ut.unit_types_name ILIKE '%office%' OR ut.unit_types_name ILIKE '%showroom%' OR ut.unit_types_name ILIKE '%warehouse%')")
            ->count();

        // ── Batch expense data for the entire year (one query per building) ────
        $expenseRows = DB::select("
            SELECT month, expense_name, SUM(expense_amount) AS expense_amount
            FROM (
                SELECT
                    EXTRACT(MONTH FROM mi.maintenance_invoice_date)::int AS month,
                    eh.expense_name,
                    CAST(mid.debit_amt AS NUMERIC) AS expense_amount
                FROM maintenance_invoice_details mid
                JOIN maintenance_invoices mi ON mi.id = mid.maintenance_invoice_id
                JOIN acc_codes ac ON ac.id = mid.ac_codes_id
                JOIN expense_head eh ON eh.acc_codes_id = ac.id
                WHERE mid.building_id = ?
                  AND EXTRACT(YEAR FROM mi.maintenance_invoice_date) = ?
                  AND mi.maintenance_invoice_status != 2
                  AND mi.deleted_at IS NULL
                UNION ALL
                SELECT
                    EXTRACT(MONTH FROM gl.doc_date)::int AS month,
                    eh.expense_name,
                    CAST(gld.debit_amt AS NUMERIC) AS expense_amount
                FROM general_ledger_dim gld
                JOIN general_ledgers gl ON gl.id = gld.general_ledger_id
                JOIN acc_codes ac ON ac.id = gld.account_id
                JOIN expense_head eh ON eh.acc_codes_id = ac.id
                WHERE gld.building_id = ?
                  AND EXTRACT(YEAR FROM gl.doc_date) = ?
                  AND gl.general_ledger_status != 2
                  AND gl.deleted_at IS NULL
            ) src
            GROUP BY month, expense_name
            ORDER BY month, expense_name
        ", [$building->id, $year, $building->id, $year]);

        $expensesByMonth = [];
        foreach ($expenseRows as $eRow) {
            $em = (int)$eRow->month;
            if (!isset($expensesByMonth[$em])) $expensesByMonth[$em] = [];
            $expensesByMonth[$em][] = $eRow;
        }

        // ── Batch occupancy counts for all months (two queries per building) ──
        $yearStart = $year . '-01-01';
        $yearEnd   = $year . '-12-01';

        $occupiedRows = DB::select("
            SELECT
                EXTRACT(MONTH FROM (gs.m + interval '1 month - 1 day'))::int AS month,
                COUNT(DISTINCT CASE WHEN ut.unit_types_name ILIKE '%residential%' OR ut.unit_types_name ILIKE '%apart%'
                       OR ut.unit_types_name ILIKE '%flat%' OR ut.unit_types_name ILIKE '%villa%'
                       OR ut.unit_types_name ILIKE '%BR%' OR ut.unit_types_name ILIKE '%studio%'
                       OR ut.unit_types_name = 'PH' THEN u.id END
                ) AS occupied_residential,
                COUNT(DISTINCT CASE WHEN ut.unit_types_name ILIKE '%commercial%' OR ut.unit_types_name ILIKE '%shop%'
                       OR ut.unit_types_name ILIKE '%office%' OR ut.unit_types_name ILIKE '%showroom%'
                       OR ut.unit_types_name ILIKE '%warehouse%' THEN u.id END
                ) AS occupied_commercial
            FROM generate_series(?::date, ?::date, '1 month') AS gs(m)
            LEFT JOIN tenant_contracts tc ON tc.tenant_contract_status != 2
                AND tc.tenant_contract_start_date <= (gs.m + interval '1 month - 1 day')::date
                AND (tc.tenant_contract_valid_to_date >= (gs.m + interval '1 month - 1 day')::date
                     OR tc.tenant_contract_valid_to_date IS NULL)
            LEFT JOIN units u ON u.id = tc.unit_id AND u.building_id = ? AND u.unit_status = 1
            LEFT JOIN unit_types ut ON ut.id = u.unit_type_id AND u.id IS NOT NULL
            GROUP BY gs.m
            ORDER BY gs.m
        ", [$yearStart, $yearEnd, $building->id]);

        $occupiedByMonth = [];
        foreach ($occupiedRows as $oRow) {
            $occupiedByMonth[(int)$oRow->month] = [
                'occupied_residential' => (int)$oRow->occupied_residential,
                'occupied_commercial'  => (int)$oRow->occupied_commercial,
            ];
        }

        $newLeasedRows = DB::select("
            SELECT
                EXTRACT(MONTH FROM tc.tenant_contract_start_date)::int AS month,
                COUNT(DISTINCT CASE WHEN ut.unit_types_name ILIKE '%residential%' OR ut.unit_types_name ILIKE '%apart%'
                       OR ut.unit_types_name ILIKE '%flat%' OR ut.unit_types_name ILIKE '%villa%'
                       OR ut.unit_types_name ILIKE '%BR%' OR ut.unit_types_name ILIKE '%studio%'
                       OR ut.unit_types_name = 'PH' THEN u.id END
                ) AS new_leased_residential,
                COUNT(DISTINCT CASE WHEN ut.unit_types_name ILIKE '%commercial%' OR ut.unit_types_name ILIKE '%shop%'
                       OR ut.unit_types_name ILIKE '%office%' OR ut.unit_types_name ILIKE '%showroom%'
                       OR ut.unit_types_name ILIKE '%warehouse%' THEN u.id END
                ) AS new_leased_commercial
            FROM tenant_contracts tc
            JOIN units u ON u.id = tc.unit_id AND u.building_id = ? AND u.unit_status = 1
            JOIN unit_types ut ON ut.id = u.unit_type_id
            WHERE tc.tenant_contract_status != 2
              AND EXTRACT(YEAR FROM tc.tenant_contract_start_date) = ?
            GROUP BY month
            ORDER BY month
        ", [$building->id, $year]);

        $newLeasedByMonth = [];
        foreach ($newLeasedRows as $nlRow) {
            $newLeasedByMonth[(int)$nlRow->month] = [
                'new_leased_residential' => (int)$nlRow->new_leased_residential,
                'new_leased_commercial'  => (int)$nlRow->new_leased_commercial,
            ];
        }

        // ── Legal contract ID lookup (contract-level, not unit-level) ────────────
        // Primary: legal.tenant_contract_id when the building has entries in the legal table.
        // Fallback: tenant name patterns for buildings with no legal table entries.
        // This ensures only the SPECIFIC contract flagged as legal is marked red,
        // not a later active renewal contract for the same tenant.
        $hasLegalTableEntries = DB::table('legal as l')
            ->join('units as u', 'u.id', '=', 'l.unit_id')
            ->where('u.building_id', $building->id)
            ->where('l.legal_is_closed', '!=', 2)
            ->whereNotNull('l.tenant_contract_id')
            ->exists();

        if ($hasLegalTableEntries) {
            $legalRows = DB::select("
                SELECT DISTINCT l.tenant_contract_id
                FROM legal l
                JOIN units u ON u.id = l.unit_id
                WHERE u.building_id = ? AND l.legal_is_closed != 2
                  AND l.tenant_contract_id IS NOT NULL
            ", [$building->id]);
        } else {
            $legalRows = DB::select("
                SELECT DISTINCT tc.id AS tenant_contract_id
                FROM tenant_contracts tc
                JOIN tenant t ON t.id = tc.tenant_id
                JOIN units u ON u.id = tc.unit_id
                WHERE u.building_id = ? AND u.unit_status = 1
                  AND (t.tenant_name ILIKE '%-legal%' OR t.tenant_name ILIKE '%-U.legal%')
            ", [$building->id]);
        }
        $legalContractIds = [];
        foreach ($legalRows as $lr) {
            $legalContractIds[$lr->tenant_contract_id] = true;
        }

        // ── Per-month loop ────────────────────────────────────────────────────
        foreach (range(1, 12) as $m) {
            $startDate = date('Y-m-d', mktime(0, 0, 0, $m, 1, $year));
            $endDate   = date('Y-m-t', mktime(0, 0, 0, $m, 1, $year));

            if (in_array($m, $monthsToPopulate)) {
                // ── Active unit data (CTE-based — no correlated subqueries per row) ──
                $units = DB::select("
                    WITH building_units AS (
                        SELECT u.id, u.unit_no, ut.unit_types_name AS unit_type
                        FROM units u
                        LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
                        WHERE u.building_id = ? AND u.unit_status = 1
                    ),
                    active_contracts AS (
                        SELECT DISTINCT ON (tc.unit_id)
                            tc.id, tc.unit_id, tc.tenant_id, tc.tenant_contract_rent,
                            tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
                            td.termination_date AS contract_termination_date
                        FROM tenant_contracts tc
                        JOIN building_units bu ON bu.id = tc.unit_id
                        LEFT JOIN (
                            SELECT contract_id, MAX(termination_date) AS termination_date
                            FROM termination
                            WHERE termination_date IS NOT NULL
                            GROUP BY contract_id
                        ) td ON td.contract_id = tc.id
                        WHERE tc.tenant_contract_start_date <= ?::date
                          AND (
                              (tc.tenant_contract_status = 1
                               AND (tc.tenant_contract_valid_to_date >= ?::date OR tc.tenant_contract_valid_to_date IS NULL))
                              OR
                              (tc.tenant_contract_status = 0
                               AND COALESCE(td.termination_date, tc.tenant_contract_valid_to_date) >= ?::date)
                          )
                        ORDER BY tc.unit_id, tc.tenant_contract_status DESC, tc.tenant_contract_start_date DESC
                    ),
                    all_receipts AS (
                        SELECT
                            tc_r.unit_id,
                            tc_r.tenant_id,
                            rg.receipts_generation_eff_from,
                            rg.receipts_generation_eff_to,
                            rg.receipts_generation_receipt_date,
                            rg.receipts_generation_amt
                        FROM receipts_generation rg
                        JOIN tenant_contracts tc_r ON tc_r.id = rg.tenant_contract_id
                        JOIN building_units bu ON bu.id = tc_r.unit_id
                        WHERE rg.receipts_generation_status != 2
                          AND rg.deleted_at IS NULL
                          AND rg.receipts_generation_receipt_date::date <= ?::date
                    ),
                    rent_recd AS (
                        SELECT DISTINCT ON (unit_id, tenant_id)
                            unit_id, tenant_id, receipts_generation_eff_to AS rent_recd_upto
                        FROM all_receipts
                        WHERE receipts_generation_eff_to::date <= ?::date
                        ORDER BY unit_id, tenant_id, receipts_generation_eff_to DESC NULLS LAST
                    ),
                    paid_thru AS (
                        SELECT DISTINCT ON (unit_id, tenant_id)
                            unit_id, tenant_id, receipts_generation_eff_to AS paid_through
                        FROM all_receipts
                        WHERE receipts_generation_eff_from IS NOT NULL
                          AND receipts_generation_eff_from::date <= ?::date
                        ORDER BY unit_id, tenant_id, receipts_generation_eff_from DESC NULLS LAST
                    ),
                    monthly_col AS (
                        SELECT unit_id, tenant_id, SUM(receipts_generation_amt) AS collection_amount
                        FROM all_receipts
                        WHERE receipts_generation_eff_from IS NOT NULL
                          AND DATE_TRUNC('month', receipts_generation_receipt_date::date) = DATE_TRUNC('month', ?::date)
                        GROUP BY unit_id, tenant_id
                    ),
                    occ_at_start AS (
                        SELECT DISTINCT tc_oas.unit_id
                        FROM tenant_contracts tc_oas
                        JOIN building_units bu ON bu.id = tc_oas.unit_id
                        WHERE tc_oas.tenant_contract_status = 1
                          AND tc_oas.tenant_contract_start_date <= ?::date
                          AND (tc_oas.tenant_contract_valid_to_date >= ?::date OR tc_oas.tenant_contract_valid_to_date IS NULL)
                    )
                    SELECT
                        bu.id AS unit_id,
                        ac.id AS contract_id,
                        bu.unit_no,
                        bu.unit_type,
                        COALESCE(t.tenant_name, 'VACANT') AS tenant_name,
                        ac.tenant_contract_start_date AS contract_start,
                        ac.tenant_contract_valid_to_date AS contract_end,
                        ac.contract_termination_date,
                        rr.rent_recd_upto,
                        pt.paid_through,
                        COALESCE(ac.tenant_contract_rent, 0) AS rent_per_month,
                        CASE WHEN ac.id IS NOT NULL THEN COALESCE(mc.collection_amount, 0) ELSE 0 END AS collection_amount,
                        COALESCE(ac.tenant_contract_rent, 0) AS income_amount,
                        0 AS outstanding_amount,
                        CASE WHEN os.unit_id IS NOT NULL THEN 1 ELSE 0 END AS occupied_at_month_start
                    FROM building_units bu
                    LEFT JOIN active_contracts ac ON ac.unit_id = bu.id
                    LEFT JOIN tenant t ON t.id = ac.tenant_id
                    LEFT JOIN rent_recd rr ON rr.unit_id = bu.id AND rr.tenant_id = ac.tenant_id
                    LEFT JOIN paid_thru pt ON pt.unit_id = bu.id AND pt.tenant_id = ac.tenant_id
                    LEFT JOIN monthly_col mc ON mc.unit_id = bu.id AND mc.tenant_id = ac.tenant_id
                    LEFT JOIN occ_at_start os ON os.unit_id = bu.id
                    ORDER BY bu.unit_no
                ", [
                    $building->id,  // building_units: building_id
                    $endDate,       // active_contracts: start_date <=
                    $startDate,     // active_contracts status=1: valid_to >=
                    $startDate,     // active_contracts status=0: effective_end >=
                    $endDate,       // all_receipts: receipt_date <=
                    $endDate,       // rent_recd: eff_to <=
                    $endDate,       // paid_thru: eff_from <=
                    $startDate,     // monthly_col: DATE_TRUNC month
                    $startDate,     // occ_at_start: start_date <=
                    $startDate,     // occ_at_start: valid_to >=
                ]);

                // Recalculate outstanding using 30/360 day-count convention.
                // Ceiling = MAX(end of next month after eff_to, report endDate)
                // This shows next unpaid period: tenant paid through eff_to, next due is eff_to+1 day onward.
                // Uses paid_through (eff_from-based) for calculation so receipts crossing month boundary are included;
                // rent_recd_upto (eff_to-based, capped at endDate) is only for display.
                foreach ($units as &$unit) {
                    $rent  = (float)($unit->rent_per_month ?? 0);
                    $effTo = !empty($unit->paid_through) ? $unit->paid_through : null;

                    // Prorate rent/income/collection for genuinely new occupancy starting mid-month.
                    // Skip proration if a contract was already active on the first day of the month
                    // (unit was occupied at month start = renewal/continuous occupancy, not new move-in).
                    if ($rent > 0 && !empty($unit->contract_start)) {
                        $contractStartTs  = strtotime($unit->contract_start);
                        $isNewOccupancy   = empty($unit->occupied_at_month_start);
                        if ($contractStartTs > strtotime($startDate) && $contractStartTs <= strtotime($endDate) && $isNewOccupancy) {
                            $startDay   = (int)date('j', $contractStartTs);
                            $startDay30 = ($startDay >= (int)date('t', $contractStartTs)) ? 30 : min($startDay, 30);
                            $proratedDays = 30 - $startDay30 + 1;
                            $rent = round($rent * $proratedDays / 30, 2);
                            $unit->rent_per_month = $rent;
                            $unit->income_amount  = $rent;
                            // collection = actual cash received, never capped by prorated rent
                        }
                    }

                    // Prorate rent/income for contracts terminated mid-month.
                    // Outstanding = max(0, pro_rated_income - collection) — no rollover for terminated tenants.
                    if ($rent > 0 && !empty($unit->contract_termination_date)) {
                        $terminationTs = strtotime($unit->contract_termination_date);
                        $startDateTs   = strtotime($startDate);
                        $endDateTs     = strtotime($endDate);
                        if ($terminationTs >= $startDateTs && $terminationTs < $endDateTs) {
                            $termDay    = (int)date('j', $terminationTs);
                            $termDay30  = ($termDay >= (int)date('t', $terminationTs)) ? 30 : min($termDay, 30);
                            $rent = round($rent * $termDay30 / 30, 2);
                            $unit->rent_per_month = $rent;
                            $unit->income_amount  = $rent;
                            $unit->outstanding_amount = max(0, round($rent - (float)($unit->collection_amount ?? 0), 2));
                            continue;
                        }
                    }

                    if ($rent <= 0) {
                        $unit->outstanding_amount = 0;
                        continue;
                    }

                    if (empty($effTo)) {
                        // No receipts at all — full month outstanding
                        $unit->outstanding_amount = $rent;
                        continue;
                    }

                    $effToTs  = strtotime($effTo);
                    $endDateTs = strtotime($endDate);

                    // If tenant has paid through or beyond the report month end — nothing outstanding
                    if ($effToTs >= $endDateTs) {
                        $unit->outstanding_amount = 0;
                        continue;
                    }

                    // Next period end = last day of month containing (eff_to + 1 day)
                    $nextDayTs     = $effToTs + 86400;
                    $nextPeriodEnd = date('Y-m-t', mktime(0, 0, 0, (int)date('n', $nextDayTs), 1, (int)date('Y', $nextDayTs)));

                    // Ceiling = later of next period end and report end date
                    $ceilingDate = (strtotime($nextPeriodEnd) > $endDateTs) ? $nextPeriodEnd : $endDate;
                    $ceilingTs   = strtotime($ceilingDate);

                    if ($effToTs >= $ceilingTs) {
                        $unit->outstanding_amount = 0;
                        continue;
                    }

                    $effToYear   = (int)date('Y', $effToTs);
                    $effToMonth  = (int)date('n', $effToTs);
                    $effToDay    = (int)date('j', $effToTs);
                    $effToDay30  = ($effToDay >= (int)date('t', $effToTs)) ? 30 : min($effToDay, 30);

                    $ceilingYear  = (int)date('Y', $ceilingTs);
                    $ceilingMonth = (int)date('n', $ceilingTs);
                    $ceilingDay   = (int)date('j', $ceilingTs);
                    $ceilingDay30 = ($ceilingDay >= (int)date('t', $ceilingTs)) ? 30 : min($ceilingDay, 30);

                    $days = ($ceilingYear - $effToYear) * 360
                          + ($ceilingMonth - $effToMonth) * 30
                          + ($ceilingDay30 - $effToDay30);

                    $unit->outstanding_amount = $days > 0 ? round($days * $rent / 30, 2) : 0;
                }
                unset($unit);

                // Old Outstanding — CTE-based (no correlated subqueries per row)
                $oldOutstanding = DB::select("
                    WITH building_units AS (
                        SELECT u.id, u.unit_no, ut.unit_types_name AS unit_type
                        FROM units u
                        LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
                        WHERE u.building_id = ? AND u.unit_status = 1
                    ),
                    termination_dates AS (
                        SELECT contract_id, MAX(termination_date) AS termination_date
                        FROM termination
                        WHERE termination_date IS NOT NULL
                        GROUP BY contract_id
                    ),
                    expired_contracts AS (
                        SELECT DISTINCT ON (tc.unit_id, tc.tenant_id)
                            tc.id, tc.unit_id, tc.tenant_id, tc.tenant_contract_rent,
                            tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
                            COALESCE(td.termination_date, tc.tenant_contract_valid_to_date) AS effective_end_date
                        FROM tenant_contracts tc
                        JOIN building_units bu ON bu.id = tc.unit_id
                        LEFT JOIN termination_dates td ON td.contract_id = tc.id
                        WHERE tc.tenant_contract_valid_to_date IS NOT NULL
                          AND COALESCE(td.termination_date, tc.tenant_contract_valid_to_date) < ?::date
                        ORDER BY tc.unit_id, tc.tenant_id, COALESCE(td.termination_date, tc.tenant_contract_valid_to_date) DESC NULLS LAST
                    ),
                    old_receipts AS (
                        SELECT
                            tc_r.unit_id,
                            tc_r.tenant_id,
                            rg.receipts_generation_eff_from,
                            rg.receipts_generation_eff_to,
                            rg.receipts_generation_receipt_date,
                            rg.receipts_generation_amt
                        FROM receipts_generation rg
                        JOIN tenant_contracts tc_r ON tc_r.id = rg.tenant_contract_id
                        JOIN building_units bu ON bu.id = tc_r.unit_id
                        LEFT JOIN termination_dates td ON td.contract_id = tc_r.id
                        WHERE tc_r.tenant_contract_valid_to_date IS NOT NULL
                          AND COALESCE(td.termination_date, tc_r.tenant_contract_valid_to_date) < ?::date
                          AND rg.receipts_generation_status != 2
                          AND rg.deleted_at IS NULL
                          AND rg.receipts_generation_receipt_date::date <= ?::date
                    ),
                    rent_recd_old AS (
                        SELECT DISTINCT ON (unit_id, tenant_id)
                            unit_id, tenant_id, receipts_generation_eff_to AS rent_recd_upto
                        FROM old_receipts
                        WHERE receipts_generation_eff_from::date <= ?::date
                        ORDER BY unit_id, tenant_id, receipts_generation_eff_from DESC NULLS LAST
                    ),
                    paid_thru_old AS (
                        SELECT DISTINCT ON (unit_id, tenant_id)
                            unit_id, tenant_id, receipts_generation_eff_to AS paid_through
                        FROM old_receipts
                        WHERE receipts_generation_eff_from::date <= ?::date
                        ORDER BY unit_id, tenant_id, receipts_generation_eff_from DESC NULLS LAST
                    ),
                    monthly_col_old AS (
                        SELECT unit_id, tenant_id, SUM(receipts_generation_amt) AS collection_amount
                        FROM old_receipts
                        WHERE receipts_generation_eff_from IS NOT NULL
                          AND DATE_TRUNC('month', receipts_generation_receipt_date::date) = DATE_TRUNC('month', ?::date)
                        GROUP BY unit_id, tenant_id
                    )
                    SELECT
                        bu.unit_no,
                        bu.unit_type,
                        t.tenant_name,
                        ec.tenant_contract_start_date AS contract_start,
                        ec.effective_end_date AS contract_end,
                        rr.rent_recd_upto,
                        pt.paid_through,
                        COALESCE(ec.tenant_contract_rent, 0) AS rent_per_month,
                        COALESCE(mc.collection_amount, 0) AS collection_amount,
                        0 AS income_amount,
                        0 AS outstanding_amount
                    FROM building_units bu
                    JOIN expired_contracts ec ON ec.unit_id = bu.id
                    JOIN tenant t ON t.id = ec.tenant_id
                    LEFT JOIN rent_recd_old rr ON rr.unit_id = bu.id AND rr.tenant_id = ec.tenant_id
                    LEFT JOIN paid_thru_old pt ON pt.unit_id = bu.id AND pt.tenant_id = ec.tenant_id
                    LEFT JOIN monthly_col_old mc ON mc.unit_id = bu.id AND mc.tenant_id = ec.tenant_id
                    WHERE NOT EXISTS (
                        SELECT 1 FROM tenant_contracts tc2
                        WHERE tc2.unit_id = bu.id
                          AND tc2.tenant_id = ec.tenant_id
                          AND tc2.tenant_contract_start_date <= ?::date
                          AND (tc2.tenant_contract_valid_to_date >= ?::date OR tc2.tenant_contract_valid_to_date IS NULL)
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM legal l
                        WHERE l.unit_id = bu.id AND l.tenant_id = ec.tenant_id AND l.legal_is_closed != 2
                    )
                    AND NOT (
                        (t.tenant_name ILIKE '%-legal%' OR t.tenant_name ILIKE '%-U.legal%')
                        AND NOT EXISTS (
                            SELECT 1 FROM legal l3
                            WHERE l3.unit_id = bu.id
                              AND l3.tenant_id = ec.tenant_id
                              AND l3.legal_is_closed = 2
                        )
                    )
                    ORDER BY bu.unit_no
                ", [
                    $building->id,  // building_units: building_id
                    $startDate,     // expired_contracts: effective_end_date < startDate
                    $startDate,     // old_receipts: effective_end_date < startDate
                    $endDate,       // old_receipts: receipt_date <=
                    $endDate,       // rent_recd_old: eff_from <=
                    $endDate,       // paid_thru_old: eff_from <=
                    $startDate,     // monthly_col_old: DATE_TRUNC month
                    $endDate,       // NOT EXISTS: start_date <=
                    $startDate,     // NOT EXISTS: valid_to >=
                ]);

                // Calculate outstanding for old outstanding units using same 30/360 formula
                // Uses paid_through (eff_from-based) for calculation; rent_recd_upto is display only (capped at endDate)
                // Cap paid_through at contract_end if it exceeds contract end date
                foreach ($oldOutstanding as &$unit) {
                    $rent          = (float)($unit->rent_per_month ?? 0);
                    $effTo         = $unit->paid_through ?? null;
                    $contractEnd   = $unit->contract_end ?? null;
                    $contractStart = $unit->contract_start ?? null;

                    if ($rent <= 0) {
                        $unit->outstanding_amount = 0;
                        continue;
                    }

                    // Cap paid_through at contract_end if paid_through > contract_end
                    if (!empty($effTo) && !empty($contractEnd)) {
                        if (strtotime($effTo) > strtotime($contractEnd)) {
                            $effTo = $contractEnd;
                        }
                    }

                    // If no receipts at all, use day before contract_start as virtual "last paid" date
                    // This means the entire contract period is outstanding
                    if (empty($effTo) && !empty($contractStart)) {
                        $effTo = date('Y-m-d', strtotime($contractStart) - 86400);
                        // Leave $unit->rent_recd_upto as null so it shows blank in Excel
                    }

                    // Use contract_end as the ceiling for outstanding calculation
                    $ceilingDate = !empty($contractEnd) ? $contractEnd : $endDate;

                    if (empty($effTo)) {
                        $unit->outstanding_amount = 0;
                        continue;
                    }

                    $effToTs   = strtotime($effTo);
                    $ceilingTs = strtotime($ceilingDate);

                    if ($effToTs >= $ceilingTs) {
                        $unit->outstanding_amount = 0;
                        continue;
                    }

                    $effToYear   = (int)date('Y', $effToTs);
                    $effToMonth  = (int)date('n', $effToTs);
                    $effToDay    = (int)date('j', $effToTs);
                    $lastDayOfEffToMonth = (int)date('t', $effToTs);
                    $effToDay30  = ($effToDay >= $lastDayOfEffToMonth) ? 30 : min($effToDay, 30);

                    $ceilingYear  = (int)date('Y', $ceilingTs);
                    $ceilingMonth = (int)date('n', $ceilingTs);
                    $ceilingDay   = (int)date('j', $ceilingTs);
                    $lastDayOfCeilingMonth = (int)date('t', $ceilingTs);
                    $ceilingDay30 = ($ceilingDay >= $lastDayOfCeilingMonth) ? 30 : min($ceilingDay, 30);

                    $days = ($ceilingYear - $effToYear) * 360
                          + ($ceilingMonth - $effToMonth) * 30
                          + ($ceilingDay30 - $effToDay30);

                    $unit->outstanding_amount = $days > 0 ? round($days * $rent / 30, 2) : 0;
                }
                unset($unit);

                // Keep rows that have an outstanding balance OR a collection in this month
                $oldOutstanding = array_values(array_filter($oldOutstanding, function ($u) {
                    return $u->outstanding_amount > 0 || (float)($u->collection_amount ?? 0) > 0;
                }));

                // Mark legal tenants in main units array (contract-level check:
                // only the specific contract in legal is marked red, not a later renewal)
                foreach ($units as &$unit) {
                    $unit->is_legal = !empty($unit->contract_id) && isset($legalContractIds[$unit->contract_id]);
                }
                unset($unit);

                // Legal tenants with expired contracts — add as a second row (red) even if an active contract also exists
                $legalExpiredUnits = DB::select("
                    WITH building_units AS (
                        SELECT u.id, u.unit_no, ut.unit_types_name AS unit_type
                        FROM units u LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
                        WHERE u.building_id = ? AND u.unit_status = 1
                    ),
                    legal_ids AS (
                        SELECT DISTINCT l.unit_id, l.tenant_id FROM legal l
                        JOIN building_units bu ON bu.id = l.unit_id WHERE l.legal_is_closed != 2
                        UNION
                        SELECT DISTINCT tc.unit_id, tc.tenant_id FROM tenant_contracts tc
                        JOIN tenant t ON t.id = tc.tenant_id
                        JOIN building_units bu ON bu.id = tc.unit_id
                        WHERE (t.tenant_name ILIKE '%-legal%' OR t.tenant_name ILIKE '%-U.legal%')
                          AND NOT EXISTS (
                              SELECT 1 FROM legal l2
                              JOIN units u2 ON u2.id = l2.unit_id
                              WHERE u2.building_id = ? AND l2.legal_is_closed != 2
                                AND l2.tenant_contract_id IS NOT NULL
                          )
                          AND NOT EXISTS (
                              SELECT 1 FROM legal l3
                              WHERE l3.unit_id = tc.unit_id
                                AND l3.tenant_id = tc.tenant_id
                                AND l3.legal_is_closed = 2
                          )
                    ),
                    latest_expired AS (
                        -- Only truly expired contracts (end date before report month start)
                        -- so active-contract units still get the second legal-outstanding row
                        -- without duplicating what the active-contract row already covers
                        SELECT DISTINCT ON (tc.unit_id)
                            tc.unit_id, tc.tenant_id, tc.tenant_contract_rent,
                            tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date
                        FROM tenant_contracts tc
                        JOIN legal_ids li ON li.unit_id = tc.unit_id AND li.tenant_id = tc.tenant_id
                        WHERE tc.tenant_contract_valid_to_date IS NOT NULL
                          AND tc.tenant_contract_valid_to_date < ?::date
                        ORDER BY tc.unit_id, tc.tenant_contract_valid_to_date DESC NULLS LAST
                    ),
                    all_legal_receipts AS (
                        SELECT tc_r.unit_id, tc_r.tenant_id,
                               rg.receipts_generation_eff_from, rg.receipts_generation_eff_to,
                               rg.receipts_generation_receipt_date, rg.receipts_generation_amt
                        FROM receipts_generation rg
                        JOIN tenant_contracts tc_r ON tc_r.id = rg.tenant_contract_id
                        JOIN building_units bu ON bu.id = tc_r.unit_id
                        JOIN legal_ids li ON li.unit_id = tc_r.unit_id AND li.tenant_id = tc_r.tenant_id
                        WHERE rg.receipts_generation_status != 2 AND rg.deleted_at IS NULL
                    ),
                    rent_recd_l AS (
                        SELECT DISTINCT ON (unit_id, tenant_id)
                            unit_id, tenant_id, receipts_generation_eff_to AS rent_recd_upto
                        FROM all_legal_receipts
                        WHERE receipts_generation_eff_to::date <= ?::date
                        ORDER BY unit_id, tenant_id, receipts_generation_eff_to DESC NULLS LAST
                    ),
                    paid_thru_l AS (
                        SELECT DISTINCT ON (unit_id, tenant_id)
                            unit_id, tenant_id, receipts_generation_eff_to AS paid_through
                        FROM all_legal_receipts
                        WHERE receipts_generation_eff_from::date <= ?::date
                        ORDER BY unit_id, tenant_id, receipts_generation_eff_from DESC NULLS LAST
                    ),
                    monthly_col_l AS (
                        SELECT unit_id, tenant_id, SUM(receipts_generation_amt) AS collection_amount
                        FROM all_legal_receipts
                        WHERE receipts_generation_eff_from IS NOT NULL
                          AND DATE_TRUNC('month', receipts_generation_receipt_date::date) = DATE_TRUNC('month', ?::date)
                        GROUP BY unit_id, tenant_id
                    )
                    SELECT
                        bu.id AS unit_id,
                        bu.unit_no, bu.unit_type, t.tenant_name,
                        le.tenant_contract_start_date AS contract_start,
                        le.tenant_contract_valid_to_date AS contract_end,
                        rr.rent_recd_upto, pt.paid_through,
                        COALESCE(le.tenant_contract_rent, 0) AS rent_per_month,
                        COALESCE(mc.collection_amount, 0) AS collection_amount,
                        0 AS income_amount, 0 AS outstanding_amount, 0 AS occupied_at_month_start
                    FROM building_units bu
                    JOIN latest_expired le ON le.unit_id = bu.id
                    JOIN tenant t ON t.id = le.tenant_id
                    LEFT JOIN rent_recd_l rr ON rr.unit_id = bu.id AND rr.tenant_id = le.tenant_id
                    LEFT JOIN paid_thru_l pt ON pt.unit_id = bu.id AND pt.tenant_id = le.tenant_id
                    LEFT JOIN monthly_col_l mc ON mc.unit_id = bu.id AND mc.tenant_id = le.tenant_id
                    ORDER BY bu.unit_no
                ", [$building->id, $building->id, $startDate, $endDate, $endDate, $startDate]);

                foreach ($legalExpiredUnits as &$unit) {
                    $unit->is_legal = true;
                    $rent = (float)($unit->rent_per_month ?? 0);
                    $effTo = $unit->paid_through ?? null;
                    $contractStart = $unit->contract_start ?? null;
                    if ($rent <= 0) { $unit->outstanding_amount = 0; continue; }
                    if (empty($effTo) && !empty($contractStart)) {
                        $effTo = date('Y-m-d', strtotime($contractStart) - 86400);
                    }
                    if (empty($effTo)) { $unit->outstanding_amount = 0; continue; }
                    $effToTs = strtotime($effTo);
                    $endDateTs = strtotime($endDate);
                    if ($effToTs >= $endDateTs) { $unit->outstanding_amount = 0; continue; }
                    $eY=(int)date('Y',$effToTs); $eM=(int)date('n',$effToTs); $eD=(int)date('j',$effToTs);
                    $eD30 = ($eD >= (int)date('t',$effToTs)) ? 30 : min($eD, 30);
                    $cY=(int)date('Y',$endDateTs); $cM=(int)date('n',$endDateTs); $cD=(int)date('j',$endDateTs);
                    $cD30 = ($cD >= (int)date('t',$endDateTs)) ? 30 : min($cD, 30);
                    $days = ($cY-$eY)*360 + ($cM-$eM)*30 + ($cD30-$eD30);
                    $unit->outstanding_amount = $days > 0 ? round($days * $rent / 30, 2) : 0;
                }
                unset($unit);

                // Filter expired-legal units: only keep those with outstanding > 0 or collection this month
                $legalExpiredUnits = array_values(array_filter($legalExpiredUnits, function ($u) {
                    return $u->outstanding_amount > 0 || (float)($u->collection_amount ?? 0) > 0;
                }));

                // Merge legal expired units into main units and sort by unit_no
                if (!empty($legalExpiredUnits)) {
                    $units = array_merge($units, $legalExpiredUnits);
                    usort($units, function($a, $b) { return strnatcmp($a->unit_no ?? '', $b->unit_no ?? ''); });
                }

                // Expenses — use pre-fetched batch (no per-month DB hit)
                $expenses = $expensesByMonth[$m] ?? [];

                // Occupancy — use pre-fetched batch (no per-month DB hit)
                $occ      = $occupiedByMonth[$m]  ?? ['occupied_residential' => 0, 'occupied_commercial' => 0];
                $newLeased = $newLeasedByMonth[$m] ?? ['new_leased_residential' => 0, 'new_leased_commercial' => 0];
                $occupancy = [
                    'total_units'            => $totalUnits,
                    'new_leased_residential' => $newLeased['new_leased_residential'],
                    'new_leased_commercial'  => $newLeased['new_leased_commercial'],
                    'occupied_residential'   => $occ['occupied_residential'],
                    'occupied_commercial'    => $occ['occupied_commercial'],
                    'vacant_residential'     => max(0, $totalResidentialUnits - $occ['occupied_residential']),
                    'vacant_commercial'      => max(0, $totalCommercialUnits - $occ['occupied_commercial']),
                    'evacuation_residential' => 0,
                    'evacuation_commercial'  => 0,
                ];
            } else {
                $units          = [];
                $oldOutstanding = [];
                $expenses       = [];
                $occupancy      = [
                    'total_units'            => 0,
                    'new_leased_residential' => 0,
                    'new_leased_commercial'  => 0,
                    'occupied_residential'   => 0,
                    'occupied_commercial'    => 0,
                    'vacant_residential'     => 0,
                    'vacant_commercial'      => 0,
                    'evacuation_residential' => 0,
                    'evacuation_commercial'  => 0,
                ];
            }

            $monthData[$m] = [
                'units'           => $units,
                'old_outstanding' => $oldOutstanding ?? [],
                'expenses'        => $expenses,
                'occupancy'       => $occupancy,
            ];
        }

        $safeName  = preg_replace('/[^A-Za-z0-9_\-]/', '_', $building->building_name);
        $fileName  = $safeName . '_' . $year . '.xlsx';
        $filePath  = $tempDir . '/' . $safeName . '/' . $fileName;

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $content = \Excel::raw(
            new NormalManagementV2Export($monthData, $year, $building->building_name, $month),
            \Maatwebsite\Excel\Excel::XLSX
        );
        file_put_contents($filePath, $content);

        $files[] = [
            'path'    => $filePath,
            'name'    => $safeName . '/' . $fileName,
            'safeName' => $safeName,
        ];
    }

    if (count($files) === 1) {
        return response()->download($files[0]['path'], basename($files[0]['path']))->deleteFileAfterSend(true);
    }

    // Multiple buildings: create ZIP
    $zipFileName = 'Normal_Management_Report_' . $year . '.zip';
    $zipPath     = $tempDir . '/' . $zipFileName;
    $zip         = new ZipArchive();

    if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
        foreach ($files as $file) {
            $zip->addFile($file['path'], $file['name']);
        }
        $zip->close();
    }

    // Cleanup individual files
    foreach ($files as $file) {
        if (file_exists($file['path'])) {
            unlink($file['path']);
        }
        $dir = dirname($file['path']);
        if (is_dir($dir) && count(scandir($dir)) === 2) {
            rmdir($dir);
        }
    }

    return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
}

/*
 *
 * Landlord Tax Invoice Report
 *
 */

public function showLandlordTaxInvoiceReport()
{
    return view('backoffice::Reports.landlord_tax_invoice_report');
}

public function landlordTaxInvoiceReportStream(Request $request)
{
    // ── Kill all output buffering (critical for Apache + Windows / Laragon) ──
    @ini_set('output_buffering', 'off');
    @ini_set('zlib.output_compression', false);
    while (ob_get_level()) @ob_end_clean();
    ob_implicit_flush(true);

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache, no-store');
    header('X-Accel-Buffering: no');
    header('Content-Encoding: none');
    header('Connection: keep-alive');

    session()->save();

    ini_set('memory_limit', '512M');
    set_time_limit(600);

    echo ': ' . str_repeat(' ', 4096) . "\n\n";
    flush();

    $send = function (array $data): void {
        echo 'data: ' . json_encode($data) . "\n\n";
        flush();
    };

    try {
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');
        $vendorId = (int) $request->input('vendor_id');

        $vendor = Vendor::find($vendorId);
        if (!$vendor || !$fromDate || !$toDate) {
            $send(['pct' => 100, 'msg' => 'Invalid request — missing landlord or dates.', 'done' => true, 'error' => true]);
            exit;
        }

        $fromTsCheck = strtotime($fromDate);
        $toTsCheck   = strtotime($toDate);
        if ($fromTsCheck === false || $toTsCheck === false || $fromTsCheck > $toTsCheck) {
            $send(['pct' => 100, 'msg' => 'Invalid request — dates are invalid or the from-date is after the to-date.', 'done' => true, 'error' => true]);
            exit;
        }
        $spanMonths = ((int) date('Y', $toTsCheck) - (int) date('Y', $fromTsCheck)) * 12
            + ((int) date('n', $toTsCheck) - (int) date('n', $fromTsCheck));
        if ($spanMonths > 24) {
            $send(['pct' => 100, 'msg' => 'Invalid request — date range cannot span more than 24 months.', 'done' => true, 'error' => true]);
            exit;
        }

        $contracts = $this->landlordTaxInvoiceEligibleContracts($vendorId);
        if ($contracts->isEmpty()) {
            $send(['pct' => 100, 'msg' => 'No eligible (Normal-management) buildings found for this landlord.', 'done' => true, 'error' => true]);
            exit;
        }

        $totalBuildings = $contracts->count();
        $send(['pct' => 1, 'msg' => 'Preparing — ' . $totalBuildings . ' invoice' . ($totalBuildings !== 1 ? 's' : '') . ' to generate']);

        $tempDir = storage_path('app/temp/landlord_tax_invoice_' . uniqid());
        if (!file_exists($tempDir) && !mkdir($tempDir, 0755, true)) {
            $send(['pct' => 100, 'msg' => 'Failed to create temp directory', 'done' => true, 'error' => true]);
            exit;
        }
        $files = [];

        foreach ($contracts as $idx => $contract) {
            $building = $contract->buildingInfo;
            if (!$building) continue;

            $pctNow = (int) round(1 + ($idx / $totalBuildings) * 88);
            $send(['pct' => $pctNow, 'msg' => 'Processing ' . $building->building_name . ' (' . ($idx + 1) . ' / ' . $totalBuildings . ')']);

            $amounts = $this->landlordTaxInvoiceLineAmounts($building, $fromDate, $toDate, $vendorId);

            $mgmtAmount    = $amounts['management_fee'];
            $cleanAmount   = $amounts['cleaning_charge'];
            $repairAmount  = $amounts['repair_maintenance'];
            $periodLabel   = $amounts['period_label'];

            $lines = [
                ['desc' => 'MANAGEMENT FEES FOR ' . $periodLabel, 'amount' => $mgmtAmount],
                ['desc' => "CLEANING CHARGES FOR " . $periodLabel, 'amount' => $cleanAmount],
                ['desc' => 'REPAIR AND MAINTENANCE CHARGES', 'amount' => $repairAmount],
            ];
            foreach ($lines as &$line) {
                $line['qty']   = 1.000;
                $line['unit_price'] = $line['amount'];
                $line['vat']   = round($line['amount'] * 0.05, 3);
                $line['total'] = round($line['amount'] + $line['vat'], 3);
            }
            unset($line);

            $totalAmount = round(array_sum(array_column($lines, 'amount')), 3);
            $totalVat    = round(array_sum(array_column($lines, 'vat')), 3);
            $totalDue    = round(array_sum(array_column($lines, 'total')), 3);

            $data = [
                'vendor'       => $vendor,
                'building'     => $building,
                'lines'        => $lines,
                'totalAmount'  => $totalAmount,
                'totalVat'     => $totalVat,
                'totalDue'     => $totalDue,
                'invoiceDate'  => date('d.m.Y', strtotime($toDate)),
                'deliveryDate' => date('d.m.Y', strtotime($toDate)),
                'paymentDate'  => date('d.m.Y', strtotime($toDate . ' +1 month')),
                'amountInWords' => $this->landlordTaxInvoiceAmountInWords($totalDue),
            ];

            $pdf = \PDF::loadView('backoffice::Reports.landlord_tax_invoice_pdf', $data)->setPaper('a4', 'portrait');

            $safeVendor   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $vendor->vendor_name);
            $safeBuilding = preg_replace('/[^A-Za-z0-9_\-]/', '_', $building->building_name);
            $fileName     = $safeVendor . '_' . $safeBuilding . '_' . $building->id . '_TaxInvoice.pdf';
            $filePath     = $tempDir . '/' . $fileName;
            $pdf->save($filePath);

            $files[] = ['path' => $filePath, 'name' => $fileName];

            $send(['pct' => (int) round(1 + (($idx + 1) / $totalBuildings) * 88), 'msg' => 'Done: ' . $building->building_name]);
        }

        if (empty($files)) {
            $send(['pct' => 100, 'msg' => 'No invoices could be generated.', 'done' => true, 'error' => true]);
            exit;
        }

        if (count($files) === 1) {
            $finalPath = $files[0]['path'];
            $finalName = $files[0]['name'];
        } else {
            $send(['pct' => 92, 'msg' => 'Creating ZIP archive…']);
            $zipFileName = 'Landlord_Tax_Invoices_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $vendor->vendor_name) . '.zip';
            $zipPath     = $tempDir . '/' . $zipFileName;
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                foreach ($files as $f) { $zip->addFile($f['path'], $f['name']); }
                $zip->close();
            }
            foreach ($files as $f) {
                if (file_exists($f['path'])) unlink($f['path']);
            }
            $finalPath = $zipPath;
            $finalName = $zipFileName;
        }

        $token = uniqid('ltir_', true);
        cache()->put('ltir_dl_' . $token, ['path' => $finalPath, 'name' => $finalName], now()->addMinutes(5));

        $send(['pct' => 100, 'msg' => 'Complete! Starting download…', 'done' => true, 'token' => $token]);
        exit;
    } catch (\Throwable $e) {
        $send(['pct' => 100, 'msg' => 'Report failed: ' . $e->getMessage(), 'done' => true, 'error' => true]);
        exit;
    }
}

public function landlordTaxInvoiceReportDownload(string $token)
{
    $info = cache()->get('ltir_dl_' . $token);
    abort_if(!$info || !file_exists($info['path']), 404, 'File not found or expired.');
    return response()->download($info['path'], $info['name'])->deleteFileAfterSend(true);
}

/**
 * Landlord contracts for the given vendor, restricted to buildings in the
 * Normal Management Report v2 building list and excluding Comprehensive
 * management (management_id == 1), per the report's explicit scope.
 */
private function landlordTaxInvoiceEligibleContracts(int $vendorId): \Illuminate\Support\Collection
{
    return LandlordContract::with('buildingInfo')
        ->where('vendor_id', $vendorId)
        ->where('management_id', '!=', 1)
        ->whereIn('building_id', self::$nmrV2BuildingIds)
        ->get()
        ->unique('building_id')
        ->values();
}

/**
 * Every {year, month} pair the given date range touches, in order.
 * E.g. 2026-06-15..2026-07-10 returns [{2026,6}, {2026,7}].
 */
private function monthsTouchedByRange(string $fromDate, string $toDate): array
{
    $result = [];
    $cursor = new \DateTime(date('Y-m-01', strtotime($fromDate)));
    $end    = new \DateTime(date('Y-m-01', strtotime($toDate)));
    while ($cursor <= $end) {
        $result[] = ['year' => (int) $cursor->format('Y'), 'month' => (int) $cursor->format('n')];
        $cursor->modify('+1 month');
    }
    return $result;
}

/**
 * Sums Management Fee, Cleaning Charges, and Repair & Maintenance for one
 * building over the given date range, reusing buildNormalManagementMonthData()
 * per distinct year the range touches. The applicable landlord contract can
 * change month to month, so each total is accumulated per month rather than
 * assuming one contract covers the whole range. Returns
 * ['management_fee' => float, 'cleaning_charge' => float, 'repair_maintenance' => float,
 *  'period_label' => string].
 */
private function landlordTaxInvoiceLineAmounts(\Modules\Masters\Entities\Building $building, string $fromDate, string $toDate, int $vendorId): array
{
    $monthsTouched = $this->monthsTouchedByRange($fromDate, $toDate);

    $byYear = [];
    foreach ($monthsTouched as $mt) {
        $byYear[$mt['year']][] = $mt['month'];
    }

    $managementFee = 0.0;
    $totalCleaning = 0.0;
    $totalExpenses = 0.0;
    foreach ($byYear as $yr => $months) {
        $monthData = $this->buildNormalManagementMonthData($building, $yr, $months);
        foreach ($months as $m) {
            $data = $monthData[$m] ?? null;
            if (!$data) continue;

            foreach ($data['expenses'] ?? [] as $exp) {
                $totalExpenses += (float) $exp->expense_amount;
            }
            $totalCleaning += (float) ($data['cleaning_charge'] ?? 0);

            $lc = $data['landlord_contract'] ?? null;
            if ($lc === null) continue;
            // The applicable landlord contract can belong to a different vendor
            // than the one this report is being generated for (e.g. the building
            // changed landlords) — skip the management-fee accumulation for this
            // month rather than billing using another landlord's contract terms.
            if ((int) $lc->vendor_id !== (int) $vendorId) continue;

            if ((int) $lc->management_method === 2) {
                $managementFee += (float) $lc->landlord_contract_management_fee;
                continue;
            }

            // Percentage type: apply to this month's income or collection only.
            $monthIncome     = 0.0;
            $monthCollection = 0.0;
            foreach ($data['units'] ?? [] as $u) {
                $monthIncome     += (float) ($u->income_amount ?? 0);
                $monthCollection += (float) ($u->collection_amount ?? 0);
            }
            foreach ($data['old_outstanding'] ?? [] as $u) {
                $monthCollection += (float) ($u->collection_amount ?? 0);
            }
            $basis = ((int) $lc->landlord_contract_percentage === 2) ? $monthCollection : $monthIncome;
            $managementFee += round($basis * ((float) $lc->landlord_contract_management_fee / 100), 3);
        }
    }

    // Period label for the line descriptions. Amounts are summed over whole
    // calendar months touched by the range (see $monthsTouched above), so the
    // label must reflect those months rather than the exact day-range — an
    // exact-day label would misleadingly imply a shorter billing period than
    // what is actually being charged.
    $fromTs = strtotime($fromDate);
    $toTs   = strtotime($toDate);
    $isCleanCalendarMonth = date('Y-m-d', $fromTs) === date('Y-m-01', $fromTs)
        && date('Y-m-d', $toTs) === date('Y-m-t', $toTs)
        && date('Y-m', $fromTs) === date('Y-m', $toTs);
    if ($isCleanCalendarMonth || count($monthsTouched) === 1) {
        $labelTs = $isCleanCalendarMonth
            ? $fromTs
            : mktime(0, 0, 0, $monthsTouched[0]['month'], 1, $monthsTouched[0]['year']);
        $periodLabel = strtoupper(date('M', $labelTs)) . "'" . date('y', $labelTs);
    } else {
        $first   = $monthsTouched[0];
        $last    = $monthsTouched[count($monthsTouched) - 1];
        $firstTs = mktime(0, 0, 0, $first['month'], 1, $first['year']);
        $lastTs  = mktime(0, 0, 0, $last['month'], 1, $last['year']);
        $periodLabel = strtoupper(date('M', $firstTs)) . "'" . date('y', $firstTs)
            . '-' . strtoupper(date('M', $lastTs)) . "'" . date('y', $lastTs);
    }

    return [
        'management_fee'     => round($managementFee, 3),
        'cleaning_charge'    => round($totalCleaning, 3),
        'repair_maintenance' => round($totalExpenses, 3),
        'period_label'       => $periodLabel,
    ];
}

/**
 * "Omani Riyals <words> & Bzs <NNN>/1000 only" — format matches the reference
 * Tax Invoice sample; reuses the numberToWords() global helper
 * (config/function.php) also used by RentReceiptGenerationController::printPreview()
 * for a similar amount-in-words line.
 */
private function landlordTaxInvoiceAmountInWords(float $total): string
{
    $parts   = explode('.', number_format($total, 3, '.', ''));
    $whole   = (int) $parts[0];
    $baisa   = $parts[1] ?? '000';
    return 'Omani Riyals ' . ucwords(numberToWords($whole)) . ' & Bzs ' . $baisa . '/1000 only';
}

/*
 *
 * Normal Management Report v2 — SSE progress stream
 *
 */

/**
 * Builds the per-month data structure (units, old_outstanding, expenses,
 * occupancy, cleaning_charge, landlord_contract) for one building/year,
 * exactly as normalManagementReportV2Stream() computes it. Shared with
 * the Landlord Tax Invoice Report so both reuse the same figures.
 */
private function buildNormalManagementMonthData(\Modules\Masters\Entities\Building $building, int $year, array $monthsToPopulate): array
{
    $monthData = [];

    // ── Static per-building counts (queried once) ─────────────────────
    $totalUnits = DB::table('units')
        ->where('building_id', $building->id)->where('unit_status', 1)->count();

    $totalResidentialUnits = DB::table('units as u')
        ->join('unit_types as ut', 'ut.id', '=', 'u.unit_type_id')
        ->where('u.building_id', $building->id)->where('u.unit_status', 1)
        ->whereRaw("(ut.unit_types_name ILIKE '%residential%' OR ut.unit_types_name ILIKE '%apart%' OR ut.unit_types_name ILIKE '%flat%' OR ut.unit_types_name ILIKE '%villa%' OR ut.unit_types_name ILIKE '%BR%' OR ut.unit_types_name ILIKE '%studio%' OR ut.unit_types_name = 'PH')")
        ->count();

    $totalCommercialUnits = DB::table('units as u')
        ->join('unit_types as ut', 'ut.id', '=', 'u.unit_type_id')
        ->where('u.building_id', $building->id)->where('u.unit_status', 1)
        ->whereRaw("(ut.unit_types_name ILIKE '%commercial%' OR ut.unit_types_name ILIKE '%shop%' OR ut.unit_types_name ILIKE '%office%' OR ut.unit_types_name ILIKE '%showroom%' OR ut.unit_types_name ILIKE '%warehouse%')")
        ->count();

    // ── Batch expense data for the entire year ────────────────────────
    $expenseRows = DB::select("
        SELECT month, expense_name, SUM(expense_amount) AS expense_amount
        FROM (
            SELECT EXTRACT(MONTH FROM mi.maintenance_invoice_date)::int AS month,
                   eh.expense_name, CAST(mid.debit_amt AS NUMERIC) AS expense_amount
            FROM maintenance_invoice_details mid
            JOIN maintenance_invoices mi ON mi.id = mid.maintenance_invoice_id
            JOIN acc_codes ac ON ac.id = mid.ac_codes_id
            JOIN expense_head eh ON eh.acc_codes_id = ac.id
            WHERE mid.building_id = ? AND EXTRACT(YEAR FROM mi.maintenance_invoice_date) = ?
              AND mi.maintenance_invoice_status != 2 AND mi.deleted_at IS NULL
            UNION ALL
            SELECT EXTRACT(MONTH FROM gl.doc_date)::int AS month,
                   eh.expense_name, CAST(gld.debit_amt AS NUMERIC) AS expense_amount
            FROM general_ledger_dim gld
            JOIN general_ledgers gl ON gl.id = gld.general_ledger_id
            JOIN acc_codes ac ON ac.id = gld.account_id
            JOIN expense_head eh ON eh.acc_codes_id = ac.id
            WHERE gld.building_id = ? AND EXTRACT(YEAR FROM gl.doc_date) = ?
              AND gl.general_ledger_status != 2 AND gl.deleted_at IS NULL
        ) src
        GROUP BY month, expense_name ORDER BY month, expense_name
    ", [$building->id, $year, $building->id, $year]);

    $expensesByMonth = [];
    foreach ($expenseRows as $eRow) {
        $em = (int) $eRow->month;
        if (!isset($expensesByMonth[$em])) $expensesByMonth[$em] = [];
        $expensesByMonth[$em][] = $eRow;
    }

    // ── Batch landlord contract cleaning charges + management fee for the year ─
    $landlordContractRows = DB::select("
        SELECT landlord_contract_status AS status,
               landlord_contract_valid_from_date AS valid_from,
               landlord_contract_valid_to_date AS valid_to,
               landlord_contract_cleaning_charge AS cleaning_charge,
               management_method,
               landlord_contract_percentage,
               landlord_contract_management_fee,
               vendor_id
        FROM landlord_contract
        WHERE building_id = ?
        ORDER BY landlord_contract_valid_from_date
    ", [$building->id]);

    // ── Batch occupancy for all months ────────────────────────────────
    $yearStart = $year . '-01-01';
    $yearEnd   = $year . '-12-01';
    $occupiedRows = DB::select("
        SELECT EXTRACT(MONTH FROM (gs.m + interval '1 month - 1 day'))::int AS month,
            COUNT(DISTINCT CASE WHEN ut.unit_types_name ILIKE '%residential%' OR ut.unit_types_name ILIKE '%apart%'
                OR ut.unit_types_name ILIKE '%flat%' OR ut.unit_types_name ILIKE '%villa%'
                OR ut.unit_types_name ILIKE '%BR%' OR ut.unit_types_name ILIKE '%studio%'
                OR ut.unit_types_name = 'PH' THEN u.id END) AS occupied_residential,
            COUNT(DISTINCT CASE WHEN ut.unit_types_name ILIKE '%commercial%' OR ut.unit_types_name ILIKE '%shop%'
                OR ut.unit_types_name ILIKE '%office%' OR ut.unit_types_name ILIKE '%showroom%'
                OR ut.unit_types_name ILIKE '%warehouse%' THEN u.id END) AS occupied_commercial
        FROM generate_series(?::date, ?::date, '1 month') AS gs(m)
        LEFT JOIN tenant_contracts tc ON tc.tenant_contract_status != 2
            AND tc.tenant_contract_start_date <= (gs.m + interval '1 month - 1 day')::date
            AND (tc.tenant_contract_valid_to_date >= (gs.m + interval '1 month - 1 day')::date OR tc.tenant_contract_valid_to_date IS NULL)
        LEFT JOIN units u ON u.id = tc.unit_id AND u.building_id = ? AND u.unit_status = 1
        LEFT JOIN unit_types ut ON ut.id = u.unit_type_id AND u.id IS NOT NULL
        GROUP BY gs.m ORDER BY gs.m
    ", [$yearStart, $yearEnd, $building->id]);

    $occupiedByMonth = [];
    foreach ($occupiedRows as $oRow) {
        $occupiedByMonth[(int) $oRow->month] = [
            'occupied_residential' => (int) $oRow->occupied_residential,
            'occupied_commercial'  => (int) $oRow->occupied_commercial,
        ];
    }

    $newLeasedRows = DB::select("
        SELECT EXTRACT(MONTH FROM tc.tenant_contract_start_date)::int AS month,
            COUNT(DISTINCT CASE WHEN ut.unit_types_name ILIKE '%residential%' OR ut.unit_types_name ILIKE '%apart%'
                OR ut.unit_types_name ILIKE '%flat%' OR ut.unit_types_name ILIKE '%villa%'
                OR ut.unit_types_name ILIKE '%BR%' OR ut.unit_types_name ILIKE '%studio%'
                OR ut.unit_types_name = 'PH' THEN u.id END) AS new_leased_residential,
            COUNT(DISTINCT CASE WHEN ut.unit_types_name ILIKE '%commercial%' OR ut.unit_types_name ILIKE '%shop%'
                OR ut.unit_types_name ILIKE '%office%' OR ut.unit_types_name ILIKE '%showroom%'
                OR ut.unit_types_name ILIKE '%warehouse%' THEN u.id END) AS new_leased_commercial
        FROM tenant_contracts tc
        JOIN units u ON u.id = tc.unit_id AND u.building_id = ? AND u.unit_status = 1
        JOIN unit_types ut ON ut.id = u.unit_type_id
        WHERE tc.tenant_contract_status != 2 AND EXTRACT(YEAR FROM tc.tenant_contract_start_date) = ?
        GROUP BY month ORDER BY month
    ", [$building->id, $year]);

    $newLeasedByMonth = [];
    foreach ($newLeasedRows as $nlRow) {
        $newLeasedByMonth[(int) $nlRow->month] = [
            'new_leased_residential' => (int) $nlRow->new_leased_residential,
            'new_leased_commercial'  => (int) $nlRow->new_leased_commercial,
        ];
    }

    // ── Legal contract ID lookup (contract-level, not unit-level) ────────────
    // Primary: legal.tenant_contract_id when the building has entries in the legal table.
    // Fallback: tenant name patterns for buildings with no legal table entries.
    // This ensures only the SPECIFIC contract flagged as legal is marked red,
    // not a later active renewal contract for the same tenant.
    $hasLegalTableEntries = DB::table('legal as l')
        ->join('units as u', 'u.id', '=', 'l.unit_id')
        ->where('u.building_id', $building->id)
        ->where('l.legal_is_closed', '!=', 2)
        ->whereNotNull('l.tenant_contract_id')
        ->exists();

    if ($hasLegalTableEntries) {
        $legalRows = DB::select("
            SELECT DISTINCT l.tenant_contract_id
            FROM legal l
            JOIN units u ON u.id = l.unit_id
            WHERE u.building_id = ? AND l.legal_is_closed != 2
              AND l.tenant_contract_id IS NOT NULL
        ", [$building->id]);
    } else {
        $legalRows = DB::select("
            SELECT DISTINCT tc.id AS tenant_contract_id
            FROM tenant_contracts tc
            JOIN tenant t ON t.id = tc.tenant_id
            JOIN units u ON u.id = tc.unit_id
            WHERE u.building_id = ? AND u.unit_status = 1
              AND (t.tenant_name ILIKE '%-legal%' OR t.tenant_name ILIKE '%-U.legal%')
        ", [$building->id]);
    }
    $legalContractIds = [];
    foreach ($legalRows as $lr) {
        $legalContractIds[$lr->tenant_contract_id] = true;
    }

    // ── Per-month loop ────────────────────────────────────────────────
    foreach (range(1, 12) as $m) {
        $startDate = date('Y-m-d', mktime(0, 0, 0, $m, 1, $year));
        $endDate   = date('Y-m-t', mktime(0, 0, 0, $m, 1, $year));

        if (in_array($m, $monthsToPopulate)) {
            // Active unit data (CTE-based)
            $units = DB::select("
                WITH building_units AS (
                    SELECT u.id, u.unit_no, ut.unit_types_name AS unit_type
                    FROM units u LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
                    WHERE u.building_id = ? AND u.unit_status = 1
                ),
                active_contracts AS (
                    SELECT DISTINCT ON (tc.unit_id)
                        tc.id, tc.unit_id, tc.tenant_id, tc.tenant_contract_rent,
                        tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
                        td.termination_date AS contract_termination_date
                    FROM tenant_contracts tc JOIN building_units bu ON bu.id = tc.unit_id
                    LEFT JOIN (
                        SELECT contract_id, MAX(termination_date) AS termination_date
                        FROM termination
                        WHERE termination_date IS NOT NULL
                        GROUP BY contract_id
                    ) td ON td.contract_id = tc.id
                    WHERE tc.tenant_contract_start_date <= ?::date
                      AND (
                          (tc.tenant_contract_status = 1
                           AND (tc.tenant_contract_valid_to_date >= ?::date OR tc.tenant_contract_valid_to_date IS NULL))
                          OR
                          (tc.tenant_contract_status = 0
                           AND COALESCE(td.termination_date, tc.tenant_contract_valid_to_date) >= ?::date)
                      )
                    ORDER BY tc.unit_id, tc.tenant_contract_status DESC, tc.tenant_contract_start_date DESC
                ),
                all_receipts AS (
                    SELECT tc_r.unit_id, tc_r.tenant_id,
                           rg.receipts_generation_eff_from, rg.receipts_generation_eff_to,
                           rg.receipts_generation_receipt_date, rg.receipts_generation_amt
                    FROM receipts_generation rg
                    JOIN tenant_contracts tc_r ON tc_r.id = rg.tenant_contract_id
                    JOIN building_units bu ON bu.id = tc_r.unit_id
                    WHERE rg.receipts_generation_status != 2 AND rg.deleted_at IS NULL
                      AND rg.receipts_generation_receipt_date::date <= ?::date
                ),
                rent_recd AS (
                    SELECT DISTINCT ON (unit_id, tenant_id)
                        unit_id, tenant_id, receipts_generation_eff_to AS rent_recd_upto
                    FROM all_receipts WHERE receipts_generation_eff_to::date <= ?::date
                    ORDER BY unit_id, tenant_id, receipts_generation_eff_to DESC NULLS LAST
                ),
                paid_thru AS (
                    SELECT DISTINCT ON (unit_id, tenant_id)
                        unit_id, tenant_id, receipts_generation_eff_to AS paid_through
                    FROM all_receipts
                    WHERE receipts_generation_eff_from IS NOT NULL AND receipts_generation_eff_from::date <= ?::date
                    ORDER BY unit_id, tenant_id, receipts_generation_eff_from DESC NULLS LAST
                ),
                monthly_col AS (
                    SELECT unit_id, tenant_id, SUM(receipts_generation_amt) AS collection_amount
                    FROM all_receipts
                    WHERE receipts_generation_eff_from IS NOT NULL
                      AND DATE_TRUNC('month', receipts_generation_receipt_date::date) = DATE_TRUNC('month', ?::date)
                    GROUP BY unit_id, tenant_id
                ),
                occ_at_start AS (
                    SELECT DISTINCT tc_oas.unit_id
                    FROM tenant_contracts tc_oas JOIN building_units bu ON bu.id = tc_oas.unit_id
                    WHERE tc_oas.tenant_contract_status = 1
                      AND tc_oas.tenant_contract_start_date <= ?::date
                      AND (tc_oas.tenant_contract_valid_to_date >= ?::date OR tc_oas.tenant_contract_valid_to_date IS NULL)
                )
                SELECT bu.id AS unit_id, ac.id AS contract_id, bu.unit_no, bu.unit_type,
                    COALESCE(t.tenant_name, 'VACANT') AS tenant_name,
                    ac.tenant_contract_start_date AS contract_start,
                    ac.tenant_contract_valid_to_date AS contract_end,
                    ac.contract_termination_date,
                    rr.rent_recd_upto, pt.paid_through,
                    COALESCE(ac.tenant_contract_rent, 0) AS rent_per_month,
                    CASE WHEN ac.id IS NOT NULL THEN COALESCE(mc.collection_amount, 0) ELSE 0 END AS collection_amount,
                    COALESCE(ac.tenant_contract_rent, 0) AS income_amount,
                    0 AS outstanding_amount,
                    CASE WHEN os.unit_id IS NOT NULL THEN 1 ELSE 0 END AS occupied_at_month_start
                FROM building_units bu
                LEFT JOIN active_contracts ac ON ac.unit_id = bu.id
                LEFT JOIN tenant t ON t.id = ac.tenant_id
                LEFT JOIN rent_recd rr ON rr.unit_id = bu.id AND rr.tenant_id = ac.tenant_id
                LEFT JOIN paid_thru pt ON pt.unit_id = bu.id AND pt.tenant_id = ac.tenant_id
                LEFT JOIN monthly_col mc ON mc.unit_id = bu.id AND mc.tenant_id = ac.tenant_id
                LEFT JOIN occ_at_start os ON os.unit_id = bu.id
                ORDER BY bu.unit_no
            ", [$building->id, $endDate, $startDate, $startDate, $endDate, $endDate, $endDate, $startDate, $startDate, $startDate]);

            foreach ($units as &$unit) {
                $rent  = (float) ($unit->rent_per_month ?? 0);
                $effTo = !empty($unit->paid_through) ? $unit->paid_through : null;
                if ($rent > 0 && !empty($unit->contract_start)) {
                    $contractStartTs = strtotime($unit->contract_start);
                    $isNewOccupancy  = empty($unit->occupied_at_month_start);
                    if ($contractStartTs > strtotime($startDate) && $contractStartTs <= strtotime($endDate) && $isNewOccupancy) {
                        $startDay   = (int) date('j', $contractStartTs);
                        $startDay30 = ($startDay >= (int) date('t', $contractStartTs)) ? 30 : min($startDay, 30);
                        $proratedDays = 30 - $startDay30 + 1;
                        $rent = round($rent * $proratedDays / 30, 2);
                        $unit->rent_per_month = $rent;
                        $unit->income_amount  = $rent;
                    }
                }
                // Prorate rent/income for contracts terminated mid-month.
                // Outstanding = max(0, pro_rated_income - collection) — no rollover for terminated tenants.
                if ($rent > 0 && !empty($unit->contract_termination_date)) {
                    $terminationTs = strtotime($unit->contract_termination_date);
                    $startDateTs   = strtotime($startDate);
                    $endDateTs     = strtotime($endDate);
                    if ($terminationTs >= $startDateTs && $terminationTs < $endDateTs) {
                        $termDay    = (int) date('j', $terminationTs);
                        $termDay30  = ($termDay >= (int) date('t', $terminationTs)) ? 30 : min($termDay, 30);
                        $rent = round($rent * $termDay30 / 30, 2);
                        $unit->rent_per_month = $rent;
                        $unit->income_amount  = $rent;
                        $unit->outstanding_amount = max(0, round($rent - (float) ($unit->collection_amount ?? 0), 2));
                        continue;
                    }
                }
                if ($rent <= 0) { $unit->outstanding_amount = 0; continue; }
                if (empty($effTo)) { $unit->outstanding_amount = $rent; continue; }
                $effToTs   = strtotime($effTo);
                $endDateTs = strtotime($endDate);
                if ($effToTs >= $endDateTs) { $unit->outstanding_amount = 0; continue; }
                $nextDayTs     = $effToTs + 86400;
                $nextPeriodEnd = date('Y-m-t', mktime(0, 0, 0, (int) date('n', $nextDayTs), 1, (int) date('Y', $nextDayTs)));
                $ceilingDate   = (strtotime($nextPeriodEnd) > $endDateTs) ? $nextPeriodEnd : $endDate;
                $ceilingTs     = strtotime($ceilingDate);
                if ($effToTs >= $ceilingTs) { $unit->outstanding_amount = 0; continue; }
                $eY = (int) date('Y', $effToTs); $eM = (int) date('n', $effToTs); $eD = (int) date('j', $effToTs);
                $eD30 = ($eD >= (int) date('t', $effToTs)) ? 30 : min($eD, 30);
                $cY = (int) date('Y', $ceilingTs); $cM = (int) date('n', $ceilingTs); $cD = (int) date('j', $ceilingTs);
                $cD30 = ($cD >= (int) date('t', $ceilingTs)) ? 30 : min($cD, 30);
                $days = ($cY - $eY) * 360 + ($cM - $eM) * 30 + ($cD30 - $eD30);
                $unit->outstanding_amount = $days > 0 ? round($days * $rent / 30, 2) : 0;
            }
            unset($unit);

            // Old outstanding (CTE-based)
            $oldOutstanding = DB::select("
                WITH building_units AS (
                    SELECT u.id, u.unit_no, ut.unit_types_name AS unit_type
                    FROM units u LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
                    WHERE u.building_id = ? AND u.unit_status = 1
                ),
                termination_dates AS (
                    SELECT contract_id, MAX(termination_date) AS termination_date
                    FROM termination
                    WHERE termination_date IS NOT NULL
                    GROUP BY contract_id
                ),
                expired_contracts AS (
                    SELECT DISTINCT ON (tc.unit_id, tc.tenant_id)
                        tc.id, tc.unit_id, tc.tenant_id, tc.tenant_contract_rent,
                        tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
                        COALESCE(td.termination_date, tc.tenant_contract_valid_to_date) AS effective_end_date
                    FROM tenant_contracts tc JOIN building_units bu ON bu.id = tc.unit_id
                    LEFT JOIN termination_dates td ON td.contract_id = tc.id
                    WHERE tc.tenant_contract_valid_to_date IS NOT NULL
                      AND COALESCE(td.termination_date, tc.tenant_contract_valid_to_date) < ?::date
                    ORDER BY tc.unit_id, tc.tenant_id, COALESCE(td.termination_date, tc.tenant_contract_valid_to_date) DESC NULLS LAST
                ),
                old_receipts AS (
                    SELECT tc_r.unit_id, tc_r.tenant_id,
                           rg.receipts_generation_eff_from, rg.receipts_generation_eff_to,
                           rg.receipts_generation_receipt_date, rg.receipts_generation_amt
                    FROM receipts_generation rg
                    JOIN tenant_contracts tc_r ON tc_r.id = rg.tenant_contract_id
                    JOIN building_units bu ON bu.id = tc_r.unit_id
                    LEFT JOIN termination_dates td ON td.contract_id = tc_r.id
                    WHERE tc_r.tenant_contract_valid_to_date IS NOT NULL
                      AND COALESCE(td.termination_date, tc_r.tenant_contract_valid_to_date) < ?::date
                      AND rg.receipts_generation_status != 2 AND rg.deleted_at IS NULL
                      AND rg.receipts_generation_receipt_date::date <= ?::date
                ),
                rent_recd_old AS (
                    SELECT DISTINCT ON (unit_id, tenant_id)
                        unit_id, tenant_id, receipts_generation_eff_to AS rent_recd_upto
                    FROM old_receipts WHERE receipts_generation_eff_from::date <= ?::date
                    ORDER BY unit_id, tenant_id, receipts_generation_eff_from DESC NULLS LAST
                ),
                paid_thru_old AS (
                    SELECT DISTINCT ON (unit_id, tenant_id)
                        unit_id, tenant_id, receipts_generation_eff_to AS paid_through
                    FROM old_receipts WHERE receipts_generation_eff_from::date <= ?::date
                    ORDER BY unit_id, tenant_id, receipts_generation_eff_from DESC NULLS LAST
                ),
                monthly_col_old AS (
                    SELECT unit_id, tenant_id, SUM(receipts_generation_amt) AS collection_amount
                    FROM old_receipts
                    WHERE receipts_generation_eff_from IS NOT NULL
                      AND DATE_TRUNC('month', receipts_generation_receipt_date::date) = DATE_TRUNC('month', ?::date)
                    GROUP BY unit_id, tenant_id
                )
                SELECT bu.unit_no, bu.unit_type, t.tenant_name,
                    ec.tenant_contract_start_date AS contract_start,
                    ec.effective_end_date AS contract_end,
                    rr.rent_recd_upto, pt.paid_through,
                    COALESCE(ec.tenant_contract_rent, 0) AS rent_per_month,
                    COALESCE(mc.collection_amount, 0) AS collection_amount,
                    0 AS income_amount, 0 AS outstanding_amount
                FROM building_units bu
                JOIN expired_contracts ec ON ec.unit_id = bu.id
                JOIN tenant t ON t.id = ec.tenant_id
                LEFT JOIN rent_recd_old rr ON rr.unit_id = bu.id AND rr.tenant_id = ec.tenant_id
                LEFT JOIN paid_thru_old pt ON pt.unit_id = bu.id AND pt.tenant_id = ec.tenant_id
                LEFT JOIN monthly_col_old mc ON mc.unit_id = bu.id AND mc.tenant_id = ec.tenant_id
                WHERE NOT EXISTS (
                    SELECT 1 FROM tenant_contracts tc2
                    WHERE tc2.unit_id = bu.id AND tc2.tenant_id = ec.tenant_id
                      AND tc2.tenant_contract_start_date <= ?::date
                      AND (tc2.tenant_contract_valid_to_date >= ?::date OR tc2.tenant_contract_valid_to_date IS NULL)
                )
                AND NOT EXISTS (
                    SELECT 1 FROM legal l
                    WHERE l.unit_id = bu.id AND l.tenant_id = ec.tenant_id AND l.legal_is_closed != 2
                )
                AND NOT (
                    (t.tenant_name ILIKE '%-legal%' OR t.tenant_name ILIKE '%-U.legal%')
                    AND NOT EXISTS (
                        SELECT 1 FROM legal l3
                        WHERE l3.unit_id = bu.id
                          AND l3.tenant_id = ec.tenant_id
                          AND l3.legal_is_closed = 2
                    )
                )
                ORDER BY bu.unit_no
            ", [$building->id, $startDate, $startDate, $endDate, $endDate, $endDate, $startDate, $endDate, $startDate]);

            foreach ($oldOutstanding as &$unit) {
                $rent = (float) ($unit->rent_per_month ?? 0);
                $effTo = $unit->paid_through ?? null;
                $contractEnd = $unit->contract_end ?? null;
                $contractStart = $unit->contract_start ?? null;
                if ($rent <= 0) { $unit->outstanding_amount = 0; continue; }
                if (!empty($effTo) && !empty($contractEnd) && strtotime($effTo) > strtotime($contractEnd)) $effTo = $contractEnd;
                if (empty($effTo) && !empty($contractStart)) $effTo = date('Y-m-d', strtotime($contractStart) - 86400);
                $ceilingDate = !empty($contractEnd) ? $contractEnd : $endDate;
                if (empty($effTo)) { $unit->outstanding_amount = 0; continue; }
                $effToTs = strtotime($effTo); $cTs = strtotime($ceilingDate);
                if ($effToTs >= $cTs) { $unit->outstanding_amount = 0; continue; }
                $eY = (int) date('Y', $effToTs); $eM = (int) date('n', $effToTs); $eD = (int) date('j', $effToTs);
                $eD30 = ($eD >= (int) date('t', $effToTs)) ? 30 : min($eD, 30);
                $cY = (int) date('Y', $cTs); $cM = (int) date('n', $cTs); $cD = (int) date('j', $cTs);
                $cD30 = ($cD >= (int) date('t', $cTs)) ? 30 : min($cD, 30);
                $days = ($cY - $eY) * 360 + ($cM - $eM) * 30 + ($cD30 - $eD30);
                $unit->outstanding_amount = $days > 0 ? round($days * $rent / 30, 2) : 0;
            }
            unset($unit);

            $oldOutstanding = array_values(array_filter($oldOutstanding, function ($u) {
                return $u->outstanding_amount > 0 || (float) ($u->collection_amount ?? 0) > 0;
            }));

            // Mark legal tenants in main units array (contract-level check:
            // only the specific contract in legal is marked red, not a later renewal)
            foreach ($units as &$unit) {
                $unit->is_legal = !empty($unit->contract_id) && isset($legalContractIds[$unit->contract_id]);
            }
            unset($unit);

            // Legal tenants with expired contracts — add as a second row (red) even if an active contract also exists
            $legalExpiredUnits = DB::select("
                WITH building_units AS (
                    SELECT u.id, u.unit_no, ut.unit_types_name AS unit_type
                    FROM units u LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
                    WHERE u.building_id = ? AND u.unit_status = 1
                ),
                legal_ids AS (
                    SELECT DISTINCT l.unit_id, l.tenant_id FROM legal l
                    JOIN building_units bu ON bu.id = l.unit_id WHERE l.legal_is_closed != 2
                    UNION
                    SELECT DISTINCT tc.unit_id, tc.tenant_id FROM tenant_contracts tc
                    JOIN tenant t ON t.id = tc.tenant_id
                    JOIN building_units bu ON bu.id = tc.unit_id
                    WHERE (t.tenant_name ILIKE '%-legal%' OR t.tenant_name ILIKE '%-U.legal%')
                      AND NOT EXISTS (
                          SELECT 1 FROM legal l2
                          JOIN units u2 ON u2.id = l2.unit_id
                          WHERE u2.building_id = ? AND l2.legal_is_closed != 2
                            AND l2.tenant_contract_id IS NOT NULL
                      )
                      AND NOT EXISTS (
                          SELECT 1 FROM legal l3
                          WHERE l3.unit_id = tc.unit_id
                            AND l3.tenant_id = tc.tenant_id
                            AND l3.legal_is_closed = 2
                      )
                ),
                latest_expired AS (
                    -- Only truly expired contracts (end date before report month start)
                    -- so active-contract units still get the second legal-outstanding row
                    -- without duplicating what the active-contract row already covers
                    SELECT DISTINCT ON (tc.unit_id)
                        tc.unit_id, tc.tenant_id, tc.tenant_contract_rent,
                        tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date
                    FROM tenant_contracts tc
                    JOIN legal_ids li ON li.unit_id = tc.unit_id AND li.tenant_id = tc.tenant_id
                    WHERE tc.tenant_contract_valid_to_date IS NOT NULL
                      AND tc.tenant_contract_valid_to_date < ?::date
                    ORDER BY tc.unit_id, tc.tenant_contract_valid_to_date DESC NULLS LAST
                ),
                all_legal_receipts AS (
                    SELECT tc_r.unit_id, tc_r.tenant_id,
                           rg.receipts_generation_eff_from, rg.receipts_generation_eff_to,
                           rg.receipts_generation_receipt_date, rg.receipts_generation_amt
                    FROM receipts_generation rg
                    JOIN tenant_contracts tc_r ON tc_r.id = rg.tenant_contract_id
                    JOIN building_units bu ON bu.id = tc_r.unit_id
                    JOIN legal_ids li ON li.unit_id = tc_r.unit_id AND li.tenant_id = tc_r.tenant_id
                    WHERE rg.receipts_generation_status != 2 AND rg.deleted_at IS NULL
                ),
                rent_recd_l AS (
                    SELECT DISTINCT ON (unit_id, tenant_id)
                        unit_id, tenant_id, receipts_generation_eff_to AS rent_recd_upto
                    FROM all_legal_receipts
                    WHERE receipts_generation_eff_to::date <= ?::date
                    ORDER BY unit_id, tenant_id, receipts_generation_eff_to DESC NULLS LAST
                ),
                paid_thru_l AS (
                    SELECT DISTINCT ON (unit_id, tenant_id)
                        unit_id, tenant_id, receipts_generation_eff_to AS paid_through
                    FROM all_legal_receipts
                    WHERE receipts_generation_eff_from::date <= ?::date
                    ORDER BY unit_id, tenant_id, receipts_generation_eff_from DESC NULLS LAST
                ),
                monthly_col_l AS (
                    SELECT unit_id, tenant_id, SUM(receipts_generation_amt) AS collection_amount
                    FROM all_legal_receipts
                    WHERE receipts_generation_eff_from IS NOT NULL
                      AND DATE_TRUNC('month', receipts_generation_receipt_date::date) = DATE_TRUNC('month', ?::date)
                    GROUP BY unit_id, tenant_id
                )
                SELECT
                    bu.id AS unit_id,
                    bu.unit_no, bu.unit_type, t.tenant_name,
                    le.tenant_contract_start_date AS contract_start,
                    le.tenant_contract_valid_to_date AS contract_end,
                    rr.rent_recd_upto, pt.paid_through,
                    COALESCE(le.tenant_contract_rent, 0) AS rent_per_month,
                    COALESCE(mc.collection_amount, 0) AS collection_amount,
                    0 AS income_amount, 0 AS outstanding_amount, 0 AS occupied_at_month_start
                FROM building_units bu
                JOIN latest_expired le ON le.unit_id = bu.id
                JOIN tenant t ON t.id = le.tenant_id
                LEFT JOIN rent_recd_l rr ON rr.unit_id = bu.id AND rr.tenant_id = le.tenant_id
                LEFT JOIN paid_thru_l pt ON pt.unit_id = bu.id AND pt.tenant_id = le.tenant_id
                LEFT JOIN monthly_col_l mc ON mc.unit_id = bu.id AND mc.tenant_id = le.tenant_id
                ORDER BY bu.unit_no
            ", [$building->id, $building->id, $startDate, $endDate, $endDate, $startDate]);

            foreach ($legalExpiredUnits as &$unit) {
                $unit->is_legal = true;
                $rent = (float)($unit->rent_per_month ?? 0);
                $effTo = $unit->paid_through ?? null;
                $contractStart = $unit->contract_start ?? null;
                if ($rent <= 0) { $unit->outstanding_amount = 0; continue; }
                if (empty($effTo) && !empty($contractStart)) {
                    $effTo = date('Y-m-d', strtotime($contractStart) - 86400);
                }
                if (empty($effTo)) { $unit->outstanding_amount = 0; continue; }
                $effToTs = strtotime($effTo);
                $endDateTs = strtotime($endDate);
                if ($effToTs >= $endDateTs) { $unit->outstanding_amount = 0; continue; }
                $eY=(int)date('Y',$effToTs); $eM=(int)date('n',$effToTs); $eD=(int)date('j',$effToTs);
                $eD30 = ($eD >= (int)date('t',$effToTs)) ? 30 : min($eD, 30);
                $cY=(int)date('Y',$endDateTs); $cM=(int)date('n',$endDateTs); $cD=(int)date('j',$endDateTs);
                $cD30 = ($cD >= (int)date('t',$endDateTs)) ? 30 : min($cD, 30);
                $days = ($cY-$eY)*360 + ($cM-$eM)*30 + ($cD30-$eD30);
                $unit->outstanding_amount = $days > 0 ? round($days * $rent / 30, 2) : 0;
            }
            unset($unit);

            // Filter expired-legal units: only keep those with outstanding > 0 or collection this month
            $legalExpiredUnits = array_values(array_filter($legalExpiredUnits, function ($u) {
                return $u->outstanding_amount > 0 || (float)($u->collection_amount ?? 0) > 0;
            }));

            // Merge legal expired units into main units and sort by unit_no
            if (!empty($legalExpiredUnits)) {
                $units = array_merge($units, $legalExpiredUnits);
                usort($units, function($a, $b) { return strnatcmp($a->unit_no ?? '', $b->unit_no ?? ''); });
            }

            $expenses  = $expensesByMonth[$m] ?? [];
            $occ       = $occupiedByMonth[$m]  ?? ['occupied_residential' => 0, 'occupied_commercial' => 0];
            $newLeased = $newLeasedByMonth[$m]  ?? ['new_leased_residential' => 0, 'new_leased_commercial' => 0];
            $occupancy = [
                'total_units'            => $totalUnits,
                'new_leased_residential' => $newLeased['new_leased_residential'],
                'new_leased_commercial'  => $newLeased['new_leased_commercial'],
                'occupied_residential'   => $occ['occupied_residential'],
                'occupied_commercial'    => $occ['occupied_commercial'],
                'vacant_residential'     => max(0, $totalResidentialUnits - $occ['occupied_residential']),
                'vacant_commercial'      => max(0, $totalCommercialUnits - $occ['occupied_commercial']),
                'evacuation_residential' => 0,
                'evacuation_commercial'  => 0,
            ];
            // Cleaning charge: the landlord contract valid for this month (prefer status=1 if
            // more than one overlaps; otherwise the latest matching valid_from).
            $cleaningCharge = 0;
            $matched = null;
            foreach ($landlordContractRows as $lc) {
                $validFrom = $lc->valid_from;
                $validTo   = $lc->valid_to;
                if ($validFrom !== null && $validFrom > $endDate) continue;
                if ($validTo !== null && $validTo < $startDate) continue;
                // Rows are ordered by valid_from ascending; prefer an active (status=1)
                // match, otherwise keep the most recent overlapping row.
                if ($matched === null
                    || ((int)$lc->status === 1 && (int)$matched->status !== 1)
                    || (int)$lc->status === (int)$matched->status) {
                    $matched = $lc;
                }
            }
            if ($matched !== null) {
                $cleaningCharge = (float) $matched->cleaning_charge;
            }
        } else {
            $units = []; $oldOutstanding = []; $expenses = []; $cleaningCharge = 0; $matched = null;
            $occupancy = ['total_units' => 0, 'new_leased_residential' => 0, 'new_leased_commercial' => 0,
                'occupied_residential' => 0, 'occupied_commercial' => 0, 'vacant_residential' => 0,
                'vacant_commercial' => 0, 'evacuation_residential' => 0, 'evacuation_commercial' => 0];
        }

        $monthData[$m] = [
            'units'             => $units,
            'old_outstanding'   => $oldOutstanding ?? [],
            'expenses'          => $expenses,
            'occupancy'         => $occupancy,
            'cleaning_charge'   => $cleaningCharge,
            'landlord_contract' => $matched,
        ];
    }

    return $monthData;
}

public function normalManagementReportV2Stream(Request $request)
{
    // ── Kill all output buffering (critical for Apache + Windows / Laragon) ──
    @ini_set('output_buffering', 'off');
    @ini_set('zlib.output_compression', false);
    while (ob_get_level()) @ob_end_clean();
    ob_implicit_flush(true);

    // ── SSE headers ───────────────────────────────────────────────────────
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache, no-store');
    header('X-Accel-Buffering: no');   // nginx
    header('Content-Encoding: none');  // disable Apache gzip
    header('Connection: keep-alive');

    session()->save(); // release PHP session file lock

    ini_set('memory_limit', '512M');
    set_time_limit(600);

    // Send padding comment on first flush to break Apache's initial buffer hold.
    // Apache buffers until ~4KB is received; the comment is stripped by the SSE spec.
    echo ': ' . str_repeat(' ', 4096) . "\n\n";
    flush();

    $send = function (array $data): void {
        echo 'data: ' . json_encode($data) . "\n\n";
        flush();
    };

    // ── Input ─────────────────────────────────────────────────────────────
    $buildingId       = $request->input('building_id', 'all');
    $month            = $request->input('month', 'all');
    $year             = (int) $request->input('year', date('Y'));
    // For the current year, only populate up to the last completed month.
    if ($month === 'all') {
        $currentYear  = (int)date('Y');
        $currentMonth = (int)date('n');
        if ($year === $currentYear) {
            $lastMonth = $currentMonth - 1;
            $monthsToPopulate = $lastMonth >= 1 ? range(1, $lastMonth) : [];
        } else {
            $monthsToPopulate = range(1, 12);
        }
    } else {
        $monthsToPopulate = [(int) $month];
    }

    if ($buildingId === 'all') {
        $buildings = Building::whereIn('id', self::$nmrV2BuildingIds)
            ->active()->orderBy('building_name')->get();
    } else {
        $buildings = Building::where('id', $buildingId)->get();
    }

    $totalBuildings = $buildings->count();
    $send(['pct' => 1, 'msg' => 'Preparing — ' . $totalBuildings . ' building' . ($totalBuildings !== 1 ? 's' : '') . ' to process']);

    $tempDir = storage_path('app/temp/normal_mgmt_' . uniqid());
    if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);
    $files = [];

    foreach ($buildings as $idx => $building) {
        $pctNow = (int) round(1 + ($idx / $totalBuildings) * 88);
        $send(['pct' => $pctNow, 'msg' => 'Processing ' . $building->building_name . ' (' . ($idx + 1) . ' / ' . $totalBuildings . ')']);

        $monthData = $this->buildNormalManagementMonthData($building, $year, $monthsToPopulate);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $building->building_name);
        $fileName = $safeName . '_' . $year . '.xlsx';
        $filePath = $tempDir . '/' . $safeName . '/' . $fileName;
        if (!file_exists(dirname($filePath))) mkdir(dirname($filePath), 0755, true);

        $content = \Excel::raw(
            new NormalManagementV2Export($monthData, $year, $building->building_name, $month),
            \Maatwebsite\Excel\Excel::XLSX
        );
        file_put_contents($filePath, $content);
        $files[] = ['path' => $filePath, 'name' => $safeName . '/' . $fileName, 'safeName' => $safeName];

        $send(['pct' => (int) round(1 + (($idx + 1) / $totalBuildings) * 88), 'msg' => 'Done: ' . $building->building_name]);
    }

    // ── Package output ────────────────────────────────────────────────────
    if (count($files) === 1) {
        $finalPath = $files[0]['path'];
        $finalName = basename($finalPath);
    } else {
        $send(['pct' => 92, 'msg' => 'Creating ZIP archive…']);
        $zipFileName = 'Normal_Management_Report_' . $year . '.zip';
        $zipPath     = $tempDir . '/' . $zipFileName;
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            foreach ($files as $f) { $zip->addFile($f['path'], $f['name']); }
            $zip->close();
        }
        foreach ($files as $f) {
            if (file_exists($f['path'])) unlink($f['path']);
            $dir = dirname($f['path']);
            if (is_dir($dir) && count(scandir($dir)) === 2) rmdir($dir);
        }
        $finalPath = $zipPath;
        $finalName = $zipFileName;
    }

    // ── Store under a short-lived token (5 min) ───────────────────────────
    $token = uniqid('nmrd_', true);
    cache()->put('nmr_dl_' . $token, ['path' => $finalPath, 'name' => $finalName], now()->addMinutes(5));

    $send(['pct' => 100, 'msg' => 'Complete! Starting download…', 'done' => true, 'token' => $token]);
    exit;
}

public function normalManagementReportV2Download(string $token)
{
    $info = cache()->get('nmr_dl_' . $token);
    abort_if(!$info || !file_exists($info['path']), 404, 'File not found or expired.');
    return response()->download($info['path'], $info['name'])->deleteFileAfterSend(true);
}

/*
 *
 * Normal Management Report v2 ends
 *
 */


}
