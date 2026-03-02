<?php

namespace Modules\Masters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Vendor;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\ManagementType;
use Auth;
use PDF;
use config;
use DB;
//use JasperPHP\JasperPHP as JasperPHP;
use JasperPHP;

class MasterReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('masters::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::create');
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
        return view('masters::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('masters::edit');
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
    *Building Details starts
    *
    */
    public function showBuildingDetails(){
        return view('masters::reports.building_report');
    } 
  public function showBuildingDetailsReport(Request $request){
      $db = config('report.database');
   
      $user = Auth::user()->username;
     
      $vendor_name =  $request['vendor_name'];
      $locations_name =  $request['location'];
      $management_types_name =  $request['management_type'];
      $logo = getLogoPath();
   // dd($logo);
      $jasper = new JasperPHP;
     //dd($jasper);
      if(isset($request->download_type)){

        if($request->download_type == 'pdf'){
              if(empty($vendor_name) && empty($locations_name) && empty($management_types_name)){ //Mandotory
// Compile a JRXML to Jasper
               
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/building_details_all.jrxml'))->execute();
print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)

$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/building_details_all.jrxml'),false,array('pdf'),array("user" => $user,"vendor_name" => $vendor_name,"locations_name" => $locations_name,"management_types_name" => $management_types_name,"logo" => $logo),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/building_details_all.pdf";
return redirect()->away($file);

     // print_r($r);
}else{
      // Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/building_details.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)

$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/building_details.jrxml'),false,array('pdf'),array("user" => $user,"vendor_name" => $vendor_name,"locations_name" => $locations_name,"management_types_name" => $management_types_name,"logo" => $logo),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/building_details.pdf";
return redirect()->away($file);

     // print_r($r);
}
}else{

   if(empty($vendor_name) && empty($locations_name) && empty($management_types_name)){ //Mandotory
        // Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/building_details_excel_all.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)

$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/building_details_excel_all.jrxml'),false,array('xlsx'),array("user" => $user,"vendor_name" => $vendor_name,"locations_name" => $locations_name,"management_types_name" => $management_types_name,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/building_details_excel_all.xlsx";
return redirect()->away($file1);
}else{
  // Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/building_details_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)

$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/building_details_excel.jrxml'),false,array('xlsx'),array("user" => $user,"vendor_name" => $vendor_name,"locations_name" => $locations_name,"management_types_name" => $management_types_name,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/building_details_excel.xlsx";
return redirect()->away($file1);
}

}  
}
}


public function landlordReportAutocompleteCode(Request $request){
    $key = $request->term;

    $vendor =   Vendor::active()->where('vendor_type_id',2)->where('vendor_name', 'ILIKE', '%'.$key.'%')
    ->select('id AS ids',DB::raw("CONCAT(vendor_name) as value"),'vendor_code AS code')
    ->get();
    return $vendor ;
}
public function locationReportAutocompleteCode(Request $request){
    $key = $request->term;

    $location =   Location::active()->where('locations_name', 'ILIKE', '%'.$key.'%')
    ->select('id AS ids',DB::raw("CONCAT(locations_name) as value"),'locations_code AS code')
    ->get();
    return $location ;
}
    /*
    *
    *Building Details starts
    *
    */

    /*
    *
    *Building -Unit Details starts
    *
    */

    public function showBuildingUnitDetails(){
        return view('masters::reports.building_unit_report');
    } 
    public function showBuildingUnitDetailsReport(Request $request){
       $db = config('report.database');
       $user = Auth::user()->username;
       $building_name =  $request['building_name'];
       $building_code =  $request['building_code'];
       $management_types_name =  $request['management_type'];
       $logo = getLogoPath();
       $SUBREPORT_DIR = base_path('/vendor/cossou/jasperphp/examples/');
       $jasper = new JasperPHP;


     //dd($jasper);
       if(isset($request->download_type)){

        if($request->download_type == 'pdf'){
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R12_Building-Unitt_Details.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)

   
     if($management_types_name == "all"){

      $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R12_Building-Unitt_Details_all.jrxml'),false,array('pdf'),array("user" => $user,"building_name" => $building_name,"building_code" => $building_code,"management_types_name" => $management_types_name,"logo" => $logo,"SUBREPORT_DIR"=>$SUBREPORT_DIR),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R12_Building-Unitt_Details.pdf";
        //return response()->file($file); 
        $file= getReportUrl()."vendor/cossou/jasperphp/examples/R12_Building-Unitt_Details.pdf";
               // dd($file);
        return redirect()->away($file);
        print_r($r);  

     }else{

      $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R12_Building-Unitt_Details.jrxml'),false,array('pdf'),array("user" => $user,"building_name" => $building_name,"building_code" => $building_code,"management_types_name" => $management_types_name,"logo" => $logo,"SUBREPORT_DIR"=>$SUBREPORT_DIR),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R12_Building-Unitt_Details.pdf";
        //return response()->file($file); 
        $file= getReportUrl()."vendor/cossou/jasperphp/examples/R12_Building-Unitt_Details.pdf";
               // dd($file);
        return redirect()->away($file);
        print_r($r);  

     }



}else{
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R12_Building-Unitt_Details_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)

$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R12_Building-Unitt_Details_excel.jrxml'),false,array('xlsx'),array("user" => $user,"building_name" => $building_name,"building_code" => $building_code,"management_types_name" => $management_types_name,"logo" => $logo,"SUBREPORT_DIR"=>$SUBREPORT_DIR),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/R12_Building-Unitt_Details_excel.xlsx";

     
return redirect()->away($file1);
} 

} 
}
public function buildingReportAutocompleteCode(Request $request){
    $key = $request->term;

    $building =   Building::active()->whereHas('landlordContract')->where('building_name', 'ILIKE', '%'.$key.'%')
    ->select('id AS ids',DB::raw("CONCAT(building_name) as value"),'building_code AS code')
    ->get();
    return $building ;
}
public function buildingCodeReportAutocompleteCode(Request $request){
    $key = $request->term;

    $building =   Building::active()->where('building_code', 'ILIKE', '%'.$key.'%')
    ->select('id AS ids',DB::raw("CONCAT(building_code) as value"))
    ->get();
    return $building ;
}
   /*
    *
    *Building -Unit Details ends
    *
    */

   public function showFurnishedUnitReport(){
    return view('masters::reports.furnished_unit');
} 
public function furnishedUnitReportPdf(Request $request){
   $db = config('report.database');
   $user = Auth::user()->username;
   $building_name =  $request['building_name'];
   $as_on_date = date('Y-m-d');
   $logo = getLogoPath();
   $jasper = new JasperPHP;
     //dd($jasper);
   if(isset($request->download_type)){

    if($request->download_type == 'pdf'){

// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/furnished_units_date.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)

$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/furnished_units_date.jrxml'),false,array('pdf'),array("user" => $user,"as_on_date" =>$as_on_date,"building_name" => $building_name,"logo" => $logo),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/furnished_units_date.pdf";
  return redirect()->away($file);
     // print_r($r);  
}
else{
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/furnished_units_excel_date.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)

$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/furnished_units_excel_date.jrxml'),false,array('xlsx'),array("user" => $user,"as_on_date" =>$as_on_date,"building_name" => $building_name,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/furnished_units_excel_date.xlsx";


return redirect()->away($file1);
     // print_r($r);
}  
}
}



public function furnishedBuildingReportAutocompleteCode(Request $request){
    $key = $request->term;

    $building =   Building::active()->whereHas('landlordContract', function ($query)use($request) {
      $query->where('landlord_contract_status',1);
  })->where('building_name', 'ILIKE', '%'.$key.'%')
    ->select('id AS ids',DB::raw("CONCAT(building_name) as value"))
    ->get();
    return $building ;
}

 /*
 *
 *Report on Vacany loss starts
 *
 */
 public function showVacancyLossReport(){
    $managementTypes = ManagementType::active()->get();
    return view('masters::reports.vacancy_loss_report',compact('managementTypes'));
}
public function vacancyLossReportPdf(Request $request){
   $db = config('report.database');
   $user = Auth::user()->username;
   $Date1 =  $request['start_date'];
   $Date2 =  $request['end_date'];
   $mngnt_type =  $request['management_types_name'];
   $logo = getLogoPath();
   $jasper = new JasperPHP;
     //dd($tenant_code);
   if(isset($request->download_type)){

    if($request->download_type == 'pdf'){

       if(!empty($Date1) &&  !empty($Date2) && empty($mngnt_type)){ //Mandotory
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/vacancy_loss.jrxml'))->execute();
print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
   //    $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/vacancy_loss.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"mngnt_type" => $mngnt_type,"logo" => $logo),$db)->execute();

       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/vacancy_loss.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"mngnt_type" => $mngnt_type,"logo" => $logo),$db)->execute();
       $file= getReportUrl()."vendor/cossou/jasperphp/examples/vacancy_loss.pdf";
       return redirect()->away($file);
       print_r($r);
     }
       else{
        // Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/vacancy_loss_compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/vacancy_loss_compo.jrxml'),false,array('pdf'),array("user" => $user,"Date1" => $Date1,"Date2" => $Date2,"mngnt_type" => $mngnt_type,"logo" => $logo),$db)->execute();
     
        $file= getReportUrl()."vendor/cossou/jasperphp/examples/vacancy_loss_compo.pdf";
        return redirect()->away($file);
       print_r($r);
       }
   }else{

    if(!empty($Date2) && empty($mngnt_type)){ //Mandotory
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/vacancy_loss_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/vacancy_loss_excel.jrxml'),false,array('xlsx'),array("user" => $user,"Date2" => $Date2,"mngnt_type" => $mngnt_type,"logo" => $logo),$db)->execute();

       $file1= getReportUrl()."vendor/cossou/jasperphp/examples/vacancy_loss_excel.xlsx";
    return redirect()->away($file1);
  }else{
    /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/vacancy_loss_excel_compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
       $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/vacancy_loss_excel_compo.jrxml'),false,array('xlsx'),array("user" => $user,"Date2" => $Date2,"mngnt_type" => $mngnt_type,"logo" => $logo),$db)->execute();

       $file1= getReportUrl()."vendor/cossou/jasperphp/examples/vacancy_loss_excel_compo.xlsx";
    return redirect()->away($file1);
   }
  }
}    
}
/*
 *
 *Report on Vacany loss ends
 *
 */

/*
 *
 *Report on Vacant Unit starts
 *
 */
public function showVacantUnitReport(){
    $managementTypes = ManagementType::active()->get();
    return view('masters::reports.vacant_unit_report',compact('managementTypes'));
}
public function vacantUnitReportPdf(Request $request){
   $db = config('report.database');
   $user = Auth::user()->username;
   $Date1 =  $request['start_date'];
   $Date2 =  $request['end_date'];
   $mngnt_type =  $request['management_types_name'];
   //dd($mngnt_type);
   $logo = getLogoPath();
   $jasper = new JasperPHP;
     //dd($tenant_code);

   if(isset($request->download_type)){

    if($request->download_type == 'pdf'){

       if(!empty($Date2) && empty($mngnt_type)){ //Mandotory

      /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/vacant_unit.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/vacant_unit.jrxml'),false,array('pdf'),array("user" => $user,"Date2" => $Date2,"logo" => $logo,"mngnt_type" => $mngnt_type),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/vacant_unit.pdf";
return redirect()->away($file);
print_r($r);
}else{
  /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/vacant_unit_compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/vacant_unit_compo.jrxml'),false,array('pdf'),array("user" => $user,"Date2" => $Date2,"logo" => $logo,"mngnt_type" => $mngnt_type),$db)->execute();

$file= getReportUrl()."vendor/cossou/jasperphp/examples/vacant_unit_compo.pdf";
return redirect()->away($file);
print_r($r);
}
 
   }else{

     if(!empty($Date2) && empty($mngnt_type)){ //Mandotory

   /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/vacant_unit_excel.jrxml'))->execute();
print_r($a);*/
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/vacant_unit_excel.jrxml'),false,array('xlsx'),array("user" => $user,"Date2" => $Date2,"mngnt_type" => $mngnt_type,"logo" => $logo),$db)->execute();

 $file1= getReportUrl()."vendor/cossou/jasperphp/examples/vacant_unit_excel.xlsx";


    return redirect()->away($file1);
  }else{
    /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/vacant_unit_excel_compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/vacant_unit_excel_compo.jrxml'),false,array('xlsx'),array("user" => $user,"Date2" => $Date2,"mngnt_type" => $mngnt_type,"logo" => $logo),$db)->execute();

 $file1= getReportUrl()."vendor/cossou/jasperphp/examples/vacant_unit_excel_compo.xlsx";


    return redirect()->away($file1);
  }
}

   }
}   
/*
 *
 *Report on Vacant Unit ends
 *
 */

/*
 *
 *Tenant Details - Building Wise starts
 *
 */
public function showTenantDetailsReport(){
    return view('masters::reports.tenant_details_report');
}
public function tenantDetailsReportPdf(Request $request){
   $db = config('report.database');
   $username = Auth::user()->username;
   $building_name =  $request['building_name'];
   $employee_name =  $request['are'];
   $building_code =  $request['building_code'];
   $logo = getLogoPath();
   $jasper = new JasperPHP;
     //dd($tenant_code);
   if(isset($request->download_type)){

    if($request->download_type == 'pdf'){
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/tenant_details_building_wise.jrxml'))->execute();
print_r($a);*/ 
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/tenant_details_building_wise.jrxml'),false,array('pdf'),array("building_name" => $building_name,"building_code" => $building_code,"employee_name" => $employee_name,"username" => $username,"logo" => $logo),$db)->execute();

//$file= base_path(). "/vendor/cossou/jasperphp/examples/tenant_details_building_wise.pdf";
//return response()->file($file); 

$file= getReportUrl()."vendor/cossou/jasperphp/examples/tenant_details_building_wise.pdf";
       // dd($file);
return redirect()->away($file);
print_r($r);    
}else{
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/tenant_details_building_wise_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/tenant_details_building_wise_excel.jrxml'),false,array('xlsx'),array("building_name" => $building_name,"building_code" => $building_code,"employee_name" => $employee_name,"username" => $username,"logo" => $logo),$db)->execute();

$file1= getReportUrl()."vendor/cossou/jasperphp/examples/tenant_details_building_wise_excel.xlsx";
    return redirect()->away($file1);
}
}
}

public function buildingCodesReportAutocompleteCode(Request $request){
    $key = $request->term;

    $building =   Building::active()
    ->whereHas('tenantContract', function ($query)use($request) {
      $query->where('tenant_contract_status',1);
  })->where('building_code', 'ILIKE', '%'.$key.'%')
    ->select('id AS ids',DB::raw("CONCAT(building_code) as value"))
    ->get();
    return $building ;
}
/*
 *
 *Tenant Details - Building Wise ends
 *
 */


}