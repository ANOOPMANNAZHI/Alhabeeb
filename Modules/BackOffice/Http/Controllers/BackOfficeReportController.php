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
use Auth;
use DB;
use PDF;
use config;
use JasperPHP;
use Modules\BackOffice\Exports\MonthlyTenancyReportExport;
use Modules\BackOffice\Exports\TenancyDetailsReportExport;
use Modules\BackOffice\Exports\MeraRentReceiptReportExport;
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
             t.tenant_contact_no, bt.building_types_name, e.employee_name
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

      SELECT DISTINCT tc.unit_usage, u.unit_vaccant_status, b.building_name, b.building_code,
             u.unit_no, ut.unit_types_name, tc.tenant_contract_no, u.unit_base_rent::float,
             tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date,
             null::date AS termination_date, t.tenant_name,
             CASE WHEN u.unit_vaccant_status = 0 THEN 'Yes' ELSE 'No' END AS vaccant_status,
             pm.payment_method_code, tc.tenant_contract_last_paid_date,
             t.tenant_contact_no, bt.building_types_name, e.employee_name
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
             t.tenant_contact_no, bt.building_types_name, e.employee_name
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
  $totalRent    = $collection->sum(function($row){
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


}
