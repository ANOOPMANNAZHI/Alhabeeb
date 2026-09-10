<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Masters\Entities\Work;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Vendor;
use Modules\Masters\Entities\Unit;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Maintenance\Entities\ComplaintServiceReport;
use App\User;
use Auth;
use DB;
use PDF;
use config;
use JasperPHP;

class MaintenanceReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('maintenance::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('maintenance::create');
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
        return view('maintenance::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('maintenance::edit');
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
 *Maintenance  Report -  Percentage of complaints/tickets category wise starts
 *
 */
public function showMaintenanceReport(){
    return view('maintenance::reports.maintenance_report');
}
public function maintenanceReportPdf(Request $request){
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
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Percentage_of_complaints_category_wise.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Percentage_of_complaints_category_wise.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

//$file= base_path(). "/vendor/cossou/jasperphp/examples/Percentage_of_complaints_category_wise.pdf";
//return response()->file($file); 

$file= getReportUrl()."vendor/cossou/jasperphp/examples/Percentage_of_complaints_category_wise.pdf";
       // dd($file);
return redirect()->away($file);
print_r($r);
}else{
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Percentage_of_complaints_category_wise_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Percentage_of_complaints_category_wise_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

 $file1= getReportUrl()."vendor/cossou/jasperphp/examples/Percentage_of_complaints_category_wise_excel.xlsx";
    return redirect()->away($file1);
print_r($r);
}
}    
}
/*
 *
 *Maintenance  Report -  Percentage of complaints/tickets category wise ends
 *
 */

/*
 *
 *Maintenance  Report -  Percentage of complaints/tickets category wise starts
 *
 */
public function showComplaintStatusReport(){
    return view('maintenance::reports.complaint_status_report');
}
public function complaintStatusReportPdf(Request $request){
 $db = config('report.database');
 $username = Auth::user()->username;
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
 if(isset($request->download_type)){

    if($request->download_type == 'pdf'){
     //dd($tenant_code);

// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/complaint_status_percentage_wise.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/complaint_status_percentage_wise.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

//$file= base_path(). "/vendor/cossou/jasperphp/examples/complaint_status_percentage_wise.pdf";
//return response()->file($file); 

$file= getReportUrl()."vendor/cossou/jasperphp/examples/complaint_status_percentage_wise.pdf";
       // dd($file);
return redirect()->away($file);
print_r($r);    
}else{
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/complaint_status_percentage_wise_excel.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/complaint_status_percentage_wise_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

 $file1= getReportUrl()."vendor/cossou/jasperphp/examples/complaint_status_percentage_wise_excel.xlsx";


    return redirect()->away($file1);
}
}
}
/*
 *
 *Maintenance  Report -  Percentage of complaints/tickets category wise ends
 *
 */

/*
 *
 *Maintenance  Report - (Open Tickets/ Closed Tickets) starts
 *
 */
public function showComplaintTicketReport(){
    $works = Work::active()->get();
    $buildings = Building::active()->whereHas('complaintEnquiry')->get();
    $vendors = Vendor::active()->where('vendor_type_id',1)->get();
    $technicians = User::role('technician')->where('user_type_status',TRUE)->get();

    return view('maintenance::reports.complaint_ticket_report',compact('works','buildings','vendors','technicians'));
}
public function unitReportAutocompleteCode(Request $request){
    $key = $request->term;

    $unit =   Unit::active()->where('unit_code', 'ILIKE', '%'.$key.'%')
    ->select('id AS ids',DB::raw("CONCAT(unit_code) as value"),'unit_type_id AS code')
    ->get();
    return $unit ;
}
public function complaintTicketReportPdf(Request $request){
 $db = config('report.database');
 $username = Auth::user()->username;
 $filter_type =  $request['filter_type'];
 $Date1 =  $request['start_date'];
 $Date2 =  $request['end_date'];
 $category =  $request['category'];
 $building_name =  $request['building_name'];
 $supervisor =  $request['Contractor'];
 $unit_code =  $request['unit_code'];
 $assignedto =  $request['Technician'];
 $logo = getLogoPath();
 $jasper = new JasperPHP;
     //dd($tenant_code);
 if(isset($request->download_type)){

    if($request->download_type == 'pdf'){
     switch ($request['filter_type']) {
        case '1':
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24Open_Tickets.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24Open_Tickets.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R24Open_Tickets.pdf";
        //return response()->file($file);

$file= getReportUrl()."vendor/cossou/jasperphp/examples/R24Open_Tickets.pdf";
       // dd($file);
return redirect()->away($file);		
        print_r($r); 
        break;
        case '2': 
          /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTickets.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTickets.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R24ClosedTickets.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/R24ClosedTickets.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break; 
        case '3': 
         /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/complaint_registratioc_categorywise.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/complaint_registratioc_categorywise.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"category" => $category,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/complaint_registratioc_categorywise.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/complaint_registratioc_categorywise.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break;
        case '4': 
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_buildingwise.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_buildingwise.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/complaint_registration_buildingwise.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/complaint_registration_buildingwise.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break;
        case '5': 
       /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_contractorwise.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_contractorwise.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"supervisor" => $supervisor,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/complaint_registration_contractorwise.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/complaint_registration_contractorwise.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break;
        case '6': 
         /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_technicianwise.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_technicianwise.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"assignedto" => $assignedto,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/complaint_registration_technicianwise.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/complaint_registration_technicianwise.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break;  
        case '7': 
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegistration-VIP.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegistration-VIP.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R24ComplaintRegistration-VIP.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/R24ComplaintRegistration-VIP.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break;
        case '8': 
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24OpenTickets_Contractorwise.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24OpenTickets_Contractorwise.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"supervisor" => $supervisor,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R24OpenTickets_Contractorwise.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/R24OpenTickets_Contractorwise.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break;
        case '9':
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24OpenTickets_Technicianwise.jrxml'))->execute();
print_r($a);  */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24OpenTickets_Technicianwise.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"assignedto" => $assignedto,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R24OpenTickets_Technicianwise.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/R24OpenTickets_Technicianwise.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break; 
        case '10': 
        /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegnAlHabibMaintained.jrxml'))->execute();
print_r($a);  */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegnAlHabibMaintained.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R24ComplaintRegnAlHabibMaintained.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/R24ComplaintRegnAlHabibMaintained.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break; 
        case '11': 
         /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegnLandlordMaintained.jrxml'))->execute();
print_r($a); */ 
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegnLandlordMaintained.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R24ComplaintRegnLandlordMaintained.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/R24ComplaintRegnLandlordMaintained.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break;  
        case '12':
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Totalnoofcomplaintsregistered.jrxml'))->execute();
print_r($a); */  
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Totalnoofcomplaintsregistered.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/Totalnoofcomplaintsregistered.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/Totalnoofcomplaintsregistered.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break;  
        case '13': 
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin24hrs.jrxml'))->execute();
print_r($a);*/
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin24hrs.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

       // $file= base_path(). "/vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin24hrs.pdf";
       // return response()->file($file); 
	   
	   $file= getReportUrl()."vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin24hrs.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break; 
        case '14': 
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin48hrs.jrxml'))->execute();
print_r($a);*/
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin48hrs.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin48hrs.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin48hrs.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break; 
        case '15': 
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsmorethan48hrs.jrxml'))->execute();
print_r($a);*/
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsmorethan48hrs.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R24ClosedTicketsmorethan48hrs.pdf";
        //return response()->file($file);

$file= getReportUrl()."vendor/cossou/jasperphp/examples/R24ClosedTicketsmorethan48hrs.pdf";
       // dd($file);
return redirect()->away($file);		
        print_r($r); 
        break; 
        case '16':
         /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegistration_Unitwise.jrxml'))->execute();
print_r($a);*/ 
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegistration_Unitwise.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"unit_code" => $unit_code,"logo" => $logo),$db)->execute();

        //$file= base_path(). "/vendor/cossou/jasperphp/examples/R24ComplaintRegistration_Unitwise.pdf";
        //return response()->file($file); 
		
		$file= getReportUrl()."vendor/cossou/jasperphp/examples/R24ComplaintRegistration_Unitwise.pdf";
       // dd($file);
return redirect()->away($file);
        print_r($r); 
        break; 
    }
}else{
   switch ($request['filter_type']) {
        case '1':
      /*  $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24Open_Tickets_excel.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24Open_Tickets_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24Open_Tickets_excel.xlsx";
        return redirect()->away($file1);
        break;
        case '2': 
          /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTickets_excel.jrxml'))->execute();
print_r($a);*/ 
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTickets_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24ClosedTickets_excel.xlsx";
    return redirect()->away($file1);
        break; 
        case '3': 
         /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/complaint_registratioc_categorywise_excel.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/complaint_registratioc_categorywise_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"category" => $category,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/complaint_registratioc_categorywise_excel.xlsx";
    return redirect()->away($file1);
        break;
        case '4': 
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_buildingwise_excel.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_buildingwise_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"building_name" => $building_name,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/complaint_registration_buildingwise_excel.xlsx";
    return redirect()->away($file1); 
        break;
        case '5': 
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_contractorwise_excel.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_contractorwise_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"supervisor" => $supervisor,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/complaint_registration_contractorwise_excel.xlsx";
    return redirect()->away($file1);
        break;
        case '6': 
         /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_technicianwise_excel.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/complaint_registration_technicianwise_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"assignedto" => $assignedto,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/complaint_registration_technicianwise_excel.xlsx";
    return redirect()->away($file1);
        break;  
        case '7': 
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegistrationVIP_excel.jrxml'))->execute();
print_r($a); */

        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegistrationVIP_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24ComplaintRegistrationVIP_excel.xlsx";
    return redirect()->away($file1); 
        break;
        case '8': 
       /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24OpenTickets_Contractorwise_excel.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24OpenTickets_Contractorwise_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"supervisor" => $supervisor,"logo" => $logo),$db)->execute();

       $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24OpenTickets_Contractorwise_excel.xlsx";
    return redirect()->away($file1); 
        break;
        case '9':
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24OpenTickets_Technicianwise_excel.jrxml'))->execute();
print_r($a); */ 
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24OpenTickets_Technicianwise_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"assignedto" => $assignedto,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24OpenTickets_Technicianwise_excel.xlsx";
    return redirect()->away($file1); 
        break; 
        case '10': 
        /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegnAlHabibMaintained_excel.jrxml'))->execute();
print_r($a);  */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegnAlHabibMaintained_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24ComplaintRegnAlHabibMaintained_excel.xlsx";
    return redirect()->away($file1); 
        break; 
        case '11': 
        /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegnLandlordMaintained_excel.jrxml'))->execute();
print_r($a); */ 
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegnLandlordMaintained_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24ComplaintRegnLandlordMaintained_excel.xlsx";
    return redirect()->away($file1); 
        break;  
        case '12':
      /*  $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Totalnoofcomplaintsregistered_excel.jrxml'))->execute();
print_r($a);   */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Totalnoofcomplaintsregistered_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/Totalnoofcomplaintsregistered_excel.xlsx";
    return redirect()->away($file1); 
        break;  
        case '13': 
       /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin24hrs_excel.jrxml'))->execute();
print_r($a);*/
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin24hrs_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

       $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin24hrs_excel.xlsx";
    return redirect()->away($file1); 
        break; 
        case '14': 
       /* $a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin48hrs_excel.jrxml'))->execute();
print_r($a);*/
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin48hrs_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24ClosedTicketsWithin48hrs_excel.xlsx";
    return redirect()->away($file1); 
        break; 
        case '15': 
        /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsmorethan48hrs_excel.jrxml'))->execute();
print_r($a);*/
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ClosedTicketsmorethan48hrs_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24ClosedTicketsmorethan48hrs_excel.xlsx";
    return redirect()->away($file1); 
        break; 
        case '16':
         /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegistration_Unitwise_excel.jrxml'))->execute();
print_r($a); */
        $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/R24ComplaintRegistration_Unitwise_excel.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"unit_code" => $unit_code,"logo" => $logo),$db)->execute();

        $file1= getReportUrl()."vendor/cossou/jasperphp/examples/R24ComplaintRegistration_Unitwise_excel.xlsx";
    return redirect()->away($file1); 
        break; 
    }

}
}
}
/*
 *
 *Maintenance  Report - (Open Tickets/ Closed Tickets) ends
 *
 */
 
/*
    *
    *Building -Unit Details starts
    *
    */

    public function showServiceReport(){
	//$buildings = Building::active()->get();
	//$complaintnos = ComplaintEnquiry::orderBy('complaint_no','asc')->get();
	return view('maintenance::reports.service_report');
	} 	
    public function serviceReportPdf(Request $request){
       $db = config('report.database'); 
		$Date1 =  $request['start_date'];
	    $Date2 =  $request['end_date'];		   
       $building_name =  $request['building_name'];
       $complaint_no =  $request['complaint_no'];
       $logo = getLogoPath();
       $sign = getSignaturePath();
	   $address = getAddressPath();
       $jasper = new JasperPHP;
     //dd($jasper);
       if(isset($request->download_type)){

  if($request->download_type == 'pdf'){

        if(!empty($Date1) && !empty($Date2) && empty($building_name) && empty($complaint_no)){	
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Service_Report_date.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)

$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Service_Report_date.jrxml'),false,array('pdf'),array("complaint_no" => $complaint_no,"building_name" => $building_name,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo,"address" => $address),$db)->execute(); 
$file2= getReportUrl()."vendor/cossou/jasperphp/examples/Service_Report_date.pdf";
       // dd($file);
return redirect()->away($file2);
}
else{
// Compile a JRXML to Jasper
/*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/Service_Report_compo.jrxml'))->execute();
print_r($a); */
// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)

$r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/Service_Report_compo.jrxml'),false,array('pdf'),array("complaint_no" => $complaint_no,"building_name" => $building_name,"Date1" => $Date1,"Date2" => $Date2,"logo" => $logo,"address" => $address),$db)->execute(); 
$file= getReportUrl()."vendor/cossou/jasperphp/examples/Service_Report_compo.pdf";
       // dd($file);
return redirect()->away($file);
print_r($r);
} 
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

/*
 *
 * Service Report v2 starts
 *
 */
public function showServiceReportV2(){
    return view('maintenance::reports.service_report_v2');
}

/**
 * Same data/filter logic as Service_Report_date.jrxml / Service_Report_compo.jrxml
 * (the v1 Jasper reports): one row per item line of a completed
 * (service_report_status = '1', complaint_status = '2') service report, in
 * the given date range and optionally narrowed to a building or complaint no.
 */
private function serviceReportV2Query($date1, $date2, $buildingName = '', $complaintNo = '')
{
    $where  = ["cc.service_report_status = '1'", "ce.complaint_status = '2'"];
    $params = [];

    if (!empty($date1) && !empty($date2)) {
        $where[]  = 'j.complaint_date BETWEEN ? AND ?';
        $params[] = $date1;
        $params[] = $date2;
    }

    if (!empty($buildingName) || !empty($complaintNo)) {
        $where[]  = '(b.building_name = ? OR ce.complaint_no = ?)';
        $params[] = $buildingName;
        $params[] = $complaintNo;
    }

    $whereSql = implode(' AND ', $where);

    $sql = "
        SELECT DISTINCT cs.service_report_no,
               j.complaint_date,
               ce.complaint_no,
               e.employee_name AS technician,
               b.building_name,
               u.unit_no,
               ce.complainer_name,
               ce.complaint_mob_no,
               split_part(cs.tenant_signature, '/', 3) AS tenant_signature,
               cs.complaint_assign_note,
               inv.inventories_name,
               ci.quantity AS qty,
               ci.material_charge,
               ci.labour_charge,
               ci.total_charge,
               cs.created_at
        FROM complaint_service_report cs
        LEFT JOIN complaint_service_report_checklist csr ON csr.complaint_service_report_id = cs.id
        LEFT JOIN complaint_checklists cc ON cc.id = csr.checklist_id
        LEFT JOIN complaint_enquiries ce ON ce.id = cc.complaint_enquiries_id
        LEFT JOIN users us ON us.id = cc.sub_assigned_to
        LEFT JOIN employees e ON e.id = us.user_type_id
        LEFT JOIN buildings b ON b.id = ce.building_id
        LEFT JOIN units u ON u.id = ce.unit_id
        LEFT JOIN complaint_service_report_inv ci ON ci.complaint_service_report_id = cs.id
        LEFT JOIN inventories inv ON inv.id = ci.inventory_id
        LEFT JOIN (
            SELECT cs.service_report_no, MAX(ce.complaint_date) AS complaint_date
            FROM complaint_service_report cs
            LEFT JOIN complaint_service_report_checklist csr ON csr.complaint_service_report_id = cs.id
            LEFT JOIN complaint_checklists cc ON cc.id = csr.checklist_id
            LEFT JOIN complaint_enquiries ce ON ce.id = cc.complaint_enquiries_id
            GROUP BY cs.service_report_no
        ) j ON j.service_report_no = cs.service_report_no
        WHERE {$whereSql}
        ORDER BY ce.complaint_no DESC
    ";

    return DB::select($sql, $params);
}

public function serviceReportPdfV2(Request $request){
    $date1        = $request['start_date'];
    $date2        = $request['end_date'];
    $buildingName = $request['building_name'] ?? '';
    $complaintNo  = $request['complaint_no'] ?? '';
    $downloadType = $request['download_type'];

    if ($downloadType != 'pdf') {
        return;
    }

    $rows = $this->serviceReportV2Query($date1, $date2, $buildingName, $complaintNo);

    // Group item lines by service_report_no, same as the jrxml's Group1/Group2
    // (one printed report per service_report_no, with a sub/grand total per group).
    $reports = [];
    foreach ($rows as $row) {
        $key = $row->service_report_no;
        if (!isset($reports[$key])) {
            $reports[$key] = [
                'header' => $row,
                'items'  => [],
            ];
        }
        $reports[$key]['items'][] = $row;
    }

    // logo-1.png has a corrupted iCCP color profile that makes GD (used
    // internally by dompdf to rasterize embedded images) emit a libpng
    // warning Laravel's error handler turns into a fatal exception -
    // logo-1-v2.png is the same image with that broken chunk stripped.
    $logoPath    = public_path('img/logo-1-v2.png');
    $addressPath = public_path('img/address.png');
    $logo        = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    $address     = file_exists($addressPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($addressPath)) : null;

    foreach ($reports as &$report) {
        $signatureFile = $report['header']->tenant_signature;
        $signaturePath = $signatureFile ? storage_path('app/public/TenantSignature/' . $signatureFile) : null;
        $report['signature'] = ($signaturePath && file_exists($signaturePath))
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath))
            : null;
    }
    unset($report);

    $data = [
        'reports' => $reports,
        'logo'    => $logo,
        'address' => $address,
    ];

    $pdf = \PDF::loadView('maintenance::reports.service_report_v2_pdf', $data)->setPaper('a3', 'portrait');

    return $pdf->stream('service_report_v2.pdf');
}
/*
 *
 * Service Report v2 ends
 *
 */

/*
 *
 * Service Report ends
 *
 */

/*
 *
 * Maintenance Invoice Report v2 starts
 *
 */
public function showMaintenanceInvoiceReportV2(){
    $buildings = Building::active()->orderBy('building_name', 'asc')->get();
    return view('maintenance::reports.maintenance_invoice_report_v2', compact('buildings'));
}

/**
 * One row per maintenance invoice line item (maintenance_invoice_details),
 * joined to its invoice, vendor, accounting code, and building - the same
 * source data as the Expense Details v2 report, but exported as a flat
 * per-line list (Invoice No / Vendor / Refer No / Service Report No /
 * Material / Labour / Amount / Lineitem Desc / Building Name) rather than
 * grouped totals. Expense type uses the same signal as Expense Details v2:
 * mid.service_report_id IS NOT NULL means the line came from a technician
 * service report (In-house); otherwise it's a directly-entered Subcontractor
 * invoice line.
 */
private function maintenanceInvoiceReportV2Query($date1, $date2, $buildingName = '', $expenseType = '')
{
    $where  = ['mi.deleted_at IS NULL', 'mi.maintenance_invoice_date BETWEEN ? AND ?'];
    $params = [$date1, $date2];

    if ($buildingName !== '') {
        $where[]  = 'b.building_name = ?';
        $params[] = $buildingName;
    }

    if ($expenseType === 'inhouse') {
        $where[] = 'mid.service_report_id IS NOT NULL';
    } elseif ($expenseType === 'subcontractor') {
        $where[] = 'mid.service_report_id IS NULL';
    }

    $whereSql = implode(' AND ', $where);

    $sql = "
        SELECT mi.maintenance_invoice_no AS invoice_no,
               mi.maintenance_invoice_date AS invoice_date,
               v.vendor_name AS vendor,
               mi.maintenance_invoice_desc AS description,
               mi.maintenance_invoice_refer_no AS refer_no,
               mid.invoice_desc AS service_report_no,
               mid.material_charge,
               mid.labour_charge,
               mid.debit_amt AS amount,
               ac.acc_code_desc AS lineitem_desc,
               b.building_name,
               CASE WHEN mid.service_report_id IS NOT NULL THEN 'In-house' ELSE 'Subcontractor' END AS expense_type
        FROM maintenance_invoices mi
        INNER JOIN maintenance_invoice_details mid ON mid.maintenance_invoice_id = mi.id
        LEFT JOIN vendors v ON v.id = mi.vendor_id
        LEFT JOIN acc_codes ac ON ac.id = mid.ac_codes_id
        LEFT JOIN buildings b ON b.id = mid.building_id
        WHERE {$whereSql}
        ORDER BY mi.maintenance_invoice_no, mi.maintenance_invoice_date
    ";

    return DB::select($sql, $params);
}

public function maintenanceInvoiceReportPdfV2(Request $request){
    $user         = Auth::user()->username;
    $date1        = $request['start_date'];
    $date2        = $request['end_date'];
    $buildingId   = $request['building_name'] ?? '';
    $expenseType  = $request['expense_type'] ?? '';
    $downloadType = $request['download_type'];

    $buildingName = '';
    if (!empty($buildingId)) {
        $building = Building::find($buildingId);
        $buildingName = $building ? $building->building_name : '';
    }

    $rows = $this->maintenanceInvoiceReportV2Query($date1, $date2, $buildingName, $expenseType);

    $data = [
        'rows'         => $rows,
        'date1'        => $date1,
        'date2'        => $date2,
        'user'         => $user,
        'buildingName' => $buildingName,
    ];

    if ($downloadType == 'pdf') {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        try {
            require_once base_path('vendor/setasign/fpdf/fpdf.php');

            $filename = 'maintenance_invoice_report_v2_' . $date2 . '.pdf';

            $pdf = new \Modules\Maintenance\Pdf\MaintenanceInvoiceReportV2Pdf('L', 'mm', 'A3');
            $pdf->AliasNbPages();
            $pdf->SetAutoPageBreak(false);
            $pdf->SetMargins(10, 10, 10);

            $marginLeft  = 10;
            $pageW       = 420;
            $usableW     = $pageW - 20; // 400
            $pageBreakY  = 275;

            $logoPath = public_path('img/logo_pdf.jpg');

            $drawHeader = function() use ($pdf, $marginLeft, $usableW, $date1, $date2, $buildingName, $user, $logoPath) {
                $pdf->AddPage();

                if (file_exists($logoPath)) {
                    $pdf->Image($logoPath, $marginLeft, 8, 32);
                }

                $pdf->SetXY($marginLeft, 10);
                $pdf->SetFont('Arial', 'B', 18);
                $pdf->Cell($usableW, 10, 'Maintenance Invoice Report', 0, 1, 'C');

                $pdf->SetXY($marginLeft, 8);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell($usableW - 40, 5, '', 0, 0);
                $pdf->Cell(20, 5, 'Report date :', 0, 0, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(20, 5, date('d/m/Y H:i'), 0, 1, 'L');

                $pdf->SetXY($marginLeft, 13);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell($usableW - 40, 5, '', 0, 0);
                $pdf->Cell(20, 5, 'User ID :', 0, 0, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(20, 5, $user, 0, 1, 'L');

                $pdf->SetXY($marginLeft, 28);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(30, 6, 'Date From :', 0, 0, 'L');
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(0, 6, date('d/m/Y', strtotime($date1)), 0, 1, 'L');

                $pdf->SetX($marginLeft);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(30, 6, 'Date To :', 0, 0, 'L');
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(0, 6, date('d/m/Y', strtotime($date2)), 0, 1, 'L');

                if ($buildingName !== '') {
                    $pdf->SetX($marginLeft);
                    $pdf->SetFont('Arial', 'B', 9);
                    $pdf->Cell(30, 6, 'Building Name :', 0, 0, 'L');
                    $pdf->SetFont('Arial', '', 9);
                    $pdf->Cell(0, 6, $buildingName, 0, 1, 'L');
                }

                $pdf->Ln(2);
            };

            $fit = function($text, $w) use ($pdf) {
                $text = (string)$text;
                while ($pdf->GetStringWidth($text) > $w - 2 && strlen($text) > 1) {
                    $text = substr($text, 0, -1);
                }
                return $text;
            };

            // word-wrap helper: returns wrapped lines that fit within $w at the pdf's
            // current font. A single "word" wider than the column (e.g. a long
            // invoice/reference number with no spaces) is hard-split so it never
            // bleeds into the next column - FPDF does not clip overflowing text.
            $wrap = function($text, $w) use ($pdf) {
                $text = (string)$text;
                $words = preg_split('/\s+/', trim($text));
                $lines = [];
                $line = '';
                foreach ($words as $word) {
                    while ($pdf->GetStringWidth($word) > $w - 2) {
                        $chunkLen = strlen($word);
                        while ($chunkLen > 1 && $pdf->GetStringWidth(substr($word, 0, $chunkLen)) > $w - 2) {
                            $chunkLen--;
                        }
                        if ($line !== '') {
                            $lines[] = $line;
                            $line = '';
                        }
                        $lines[] = substr($word, 0, $chunkLen);
                        $word = substr($word, $chunkLen);
                    }
                    $test = $line === '' ? $word : $line . ' ' . $word;
                    if ($pdf->GetStringWidth($test) > $w - 2 && $line !== '') {
                        $lines[] = $line;
                        $line = $word;
                    } else {
                        $line = $test;
                    }
                }
                if ($line !== '' || empty($lines)) $lines[] = $line;
                return $lines;
            };

            $lineH = 5;
            $headerRowH = 9;

            // Widened to comfortably fit the largest realistic single-token values
            // (long invoice/reference/service-report numbers) at the larger font
            // size below, using more of the A3 landscape usable width (400mm).
            $cols = [
                'Sl' => 8, 'Invoice' => 28, 'Date' => 24, 'Vendor' => 48, 'Description' => 62,
                'RefNo' => 30, 'ServiceReport' => 26, 'Material' => 26, 'Labour' => 26,
                'Amount' => 28, 'Lineitem' => 50, 'Building' => 44,
            ];

            $drawHeaderRow = function() use ($pdf, $marginLeft, $cols, $headerRowH) {
                $pdf->SetX($marginLeft);
                $pdf->SetFillColor(4, 93, 194);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell($cols['Sl'], $headerRowH, 'Sl', 1, 0, 'C', true);
                $pdf->Cell($cols['Invoice'], $headerRowH, 'Invoice No', 1, 0, 'L', true);
                $pdf->Cell($cols['Date'], $headerRowH, 'Date', 1, 0, 'C', true);
                $pdf->Cell($cols['Vendor'], $headerRowH, 'Vendor', 1, 0, 'L', true);
                $pdf->Cell($cols['Description'], $headerRowH, 'Description', 1, 0, 'L', true);
                $pdf->Cell($cols['RefNo'], $headerRowH, 'Refer No', 1, 0, 'L', true);
                $pdf->Cell($cols['ServiceReport'], $headerRowH, 'Service Report No', 1, 0, 'L', true);
                $pdf->Cell($cols['Material'], $headerRowH, 'Material', 1, 0, 'R', true);
                $pdf->Cell($cols['Labour'], $headerRowH, 'Labour', 1, 0, 'R', true);
                $pdf->Cell($cols['Amount'], $headerRowH, 'Amount', 1, 0, 'R', true);
                $pdf->Cell($cols['Lineitem'], $headerRowH, 'Lineitem Desc', 1, 0, 'L', true);
                $pdf->Cell($cols['Building'], $headerRowH, 'Building Name', 1, 1, 'L', true);
                $pdf->SetTextColor(0, 0, 0);
            };

            $drawHeader();
            $drawHeaderRow();

            $pdf->SetFont('Arial', '', 9);
            $sl = 1;
            $grandMaterial = 0;
            $grandLabour   = 0;
            $grandAmount   = 0;

            foreach ($rows as $row) {
                $pdf->SetFont('Arial', '', 9);

                // wrap the free-text columns instead of truncating, so nothing is cut off
                $vendorLines   = $wrap($row->vendor, $cols['Vendor']);
                $descLines     = $wrap($row->description, $cols['Description']);
                $refNoLines    = $wrap($row->refer_no, $cols['RefNo']);
                $srLines       = $wrap($row->service_report_no, $cols['ServiceReport']);
                $lineitemLines = $wrap($row->lineitem_desc, $cols['Lineitem']);
                $buildingLines = $wrap($row->building_name, $cols['Building']);

                $maxLines = max(1, count($vendorLines), count($descLines), count($refNoLines), count($srLines), count($lineitemLines), count($buildingLines));
                $rowH = $maxLines * $lineH;

                if ($pdf->GetY() + $rowH > $pageBreakY) {
                    $drawHeader();
                    $drawHeaderRow();
                    $pdf->SetFont('Arial', '', 9);
                }

                $grandMaterial += (float)$row->material_charge;
                $grandLabour   += (float)$row->labour_charge;
                $grandAmount   += (float)$row->amount;

                $x = $marginLeft;
                $y = $pdf->GetY();

                $drawWrappedCell = function($x, $y, $w, $rowH, array $lines) use ($pdf, $lineH) {
                    $pdf->Rect($x, $y, $w, $rowH);
                    $ty = $y;
                    foreach ($lines as $line) {
                        $pdf->SetXY($x + 1, $ty);
                        $pdf->Cell($w - 2, $lineH, $line, 0, 0, 'L');
                        $ty += $lineH;
                    }
                };

                $drawCenteredCell = function($x, $y, $w, $rowH, $text, $align = 'L') use ($pdf, $lineH) {
                    $pdf->Rect($x, $y, $w, $rowH);
                    $pdf->SetXY($x + 1, $y + ($rowH - $lineH) / 2);
                    $pdf->Cell($w - 2, $lineH, $text, 0, 0, $align);
                };

                $drawCenteredCell($x, $y, $cols['Sl'], $rowH, $sl, 'C'); $x += $cols['Sl'];
                $drawCenteredCell($x, $y, $cols['Invoice'], $rowH, $fit($row->invoice_no, $cols['Invoice'])); $x += $cols['Invoice'];
                $drawCenteredCell($x, $y, $cols['Date'], $rowH, $row->invoice_date ? date('d/m/Y', strtotime($row->invoice_date)) : '', 'C'); $x += $cols['Date'];
                $drawWrappedCell($x, $y, $cols['Vendor'], $rowH, $vendorLines); $x += $cols['Vendor'];
                $drawWrappedCell($x, $y, $cols['Description'], $rowH, $descLines); $x += $cols['Description'];
                $drawWrappedCell($x, $y, $cols['RefNo'], $rowH, $refNoLines); $x += $cols['RefNo'];
                $drawWrappedCell($x, $y, $cols['ServiceReport'], $rowH, $srLines); $x += $cols['ServiceReport'];
                $drawCenteredCell($x, $y, $cols['Material'], $rowH, number_format((float)$row->material_charge, 3), 'R'); $x += $cols['Material'];
                $drawCenteredCell($x, $y, $cols['Labour'], $rowH, number_format((float)$row->labour_charge, 3), 'R'); $x += $cols['Labour'];
                $drawCenteredCell($x, $y, $cols['Amount'], $rowH, number_format((float)$row->amount, 3), 'R'); $x += $cols['Amount'];
                $drawWrappedCell($x, $y, $cols['Lineitem'], $rowH, $lineitemLines); $x += $cols['Lineitem'];
                $drawWrappedCell($x, $y, $cols['Building'], $rowH, $buildingLines);

                $pdf->SetXY($marginLeft, $y + $rowH);
                $sl++;
            }

            if ($pdf->GetY() > $pageBreakY - $headerRowH) {
                $drawHeader();
                $drawHeaderRow();
                $pdf->SetFont('Arial', '', 9);
            }

            $pdf->SetX($marginLeft);
            $pdf->SetFont('Arial', 'B', 9);
            $labelW = $cols['Sl'] + $cols['Invoice'] + $cols['Date'] + $cols['Vendor'] + $cols['Description'] + $cols['RefNo'] + $cols['ServiceReport'];
            $pdf->Cell($labelW, $headerRowH, 'Grand Total', 1, 0, 'R', true);
            $pdf->SetFillColor(222, 235, 250);
            $pdf->Cell($cols['Material'], $headerRowH, number_format($grandMaterial, 3), 1, 0, 'R', true);
            $pdf->Cell($cols['Labour'], $headerRowH, number_format($grandLabour, 3), 1, 0, 'R', true);
            $pdf->Cell($cols['Amount'], $headerRowH, number_format($grandAmount, 3), 1, 0, 'R', true);
            $pdf->Cell($cols['Lineitem'] + $cols['Building'], $headerRowH, '', 1, 1, 'L', true);

            $content = $pdf->Output('S');
            return response($content, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            return response('PDF Error: ' . $e->getMessage(), 500);
        }
    } else {
        $filename = 'maintenance_invoice_report_v2_' . $date2 . '.xlsx';
        return \Excel::download(
            new \Modules\Maintenance\Exports\MaintenanceInvoiceReportV2Export($data),
            $filename
        );
    }
}

/**
 * SSE progress endpoint for the Generate button - same pattern as Normal
 * Management Report v2 (progress overlay + EventSource): emits {pct, msg}
 * events while building the selected PDF/Excel report AND, whenever any
 * in-house lines match the filter, the per-building service reports ZIP -
 * then a final {done:true, reportToken, zipToken?} event the page uses to
 * fetch both files (one or two downloads) from maintenanceInvoiceReportV2Download.
 */
public function maintenanceInvoiceReportV2Stream(Request $request)
{
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

    $user         = Auth::user()->username;
    $date1        = $request->input('start_date');
    $date2        = $request->input('end_date');
    $buildingId   = $request->input('building_name', '');
    $expenseType  = $request->input('expense_type', '');
    $downloadType = $request->input('download_type', 'pdf');

    $buildingName = '';
    if (!empty($buildingId)) {
        $building = Building::find($buildingId);
        $buildingName = $building ? $building->building_name : '';
    }

    $send(['pct' => 2, 'msg' => 'Preparing…']);

    $tempDir = storage_path('app/temp/maintenance_invoice_v2_' . uniqid());
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    $rows = $this->maintenanceInvoiceReportV2Query($date1, $date2, $buildingName, $expenseType);
    $send(['pct' => 6, 'msg' => 'Building report — ' . count($rows) . ' rows']);

    if ($downloadType === 'excel') {
        $data = [
            'rows'         => $rows,
            'date1'        => $date1,
            'date2'        => $date2,
            'user'         => $user,
            'buildingName' => $buildingName,
        ];
        $content = \Excel::raw(
            new \Modules\Maintenance\Exports\MaintenanceInvoiceReportV2Export($data),
            \Maatwebsite\Excel\Excel::XLSX
        );
        $reportFileName = 'maintenance_invoice_report_v2_' . $date2 . '.xlsx';
        $reportFilePath = $tempDir . '/' . $reportFileName;
        file_put_contents($reportFilePath, $content);
        $send(['pct' => 45, 'msg' => 'Excel report ready']);
    } else {
        require_once base_path('vendor/setasign/fpdf/fpdf.php');

        $reportFileName = 'maintenance_invoice_report_v2_' . $date2 . '.pdf';
        $reportFilePath = $tempDir . '/' . $reportFileName;

        $pdf = new \Modules\Maintenance\Pdf\MaintenanceInvoiceReportV2Pdf('L', 'mm', 'A3');
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(10, 10, 10);

        $marginLeft  = 10;
        $pageW       = 420;
        $usableW     = $pageW - 20;
        $pageBreakY  = 275;

        $logoPath = public_path('img/logo_pdf.jpg');

        $drawHeader = function() use ($pdf, $marginLeft, $usableW, $date1, $date2, $buildingName, $user, $logoPath) {
            $pdf->AddPage();
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, $marginLeft, 8, 32);
            }
            $pdf->SetXY($marginLeft, 10);
            $pdf->SetFont('Arial', 'B', 18);
            $pdf->Cell($usableW, 10, 'Maintenance Invoice Report', 0, 1, 'C');

            $pdf->SetXY($marginLeft, 8);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell($usableW - 40, 5, '', 0, 0);
            $pdf->Cell(20, 5, 'Report date :', 0, 0, 'L');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(20, 5, date('d/m/Y H:i'), 0, 1, 'L');

            $pdf->SetXY($marginLeft, 13);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell($usableW - 40, 5, '', 0, 0);
            $pdf->Cell(20, 5, 'User ID :', 0, 0, 'L');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(20, 5, $user, 0, 1, 'L');

            $pdf->SetXY($marginLeft, 28);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(30, 6, 'Date From :', 0, 0, 'L');
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 6, date('d/m/Y', strtotime($date1)), 0, 1, 'L');

            $pdf->SetX($marginLeft);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(30, 6, 'Date To :', 0, 0, 'L');
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 6, date('d/m/Y', strtotime($date2)), 0, 1, 'L');

            if ($buildingName !== '') {
                $pdf->SetX($marginLeft);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(30, 6, 'Building Name :', 0, 0, 'L');
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(0, 6, $buildingName, 0, 1, 'L');
            }
            $pdf->Ln(2);
        };

        $fit = function($text, $w) use ($pdf) {
            $text = (string)$text;
            while ($pdf->GetStringWidth($text) > $w - 2 && strlen($text) > 1) {
                $text = substr($text, 0, -1);
            }
            return $text;
        };

        $wrap = function($text, $w) use ($pdf) {
            $text = (string)$text;
            $words = preg_split('/\s+/', trim($text));
            $lines = [];
            $line = '';
            foreach ($words as $word) {
                while ($pdf->GetStringWidth($word) > $w - 2) {
                    $chunkLen = strlen($word);
                    while ($chunkLen > 1 && $pdf->GetStringWidth(substr($word, 0, $chunkLen)) > $w - 2) {
                        $chunkLen--;
                    }
                    if ($line !== '') {
                        $lines[] = $line;
                        $line = '';
                    }
                    $lines[] = substr($word, 0, $chunkLen);
                    $word = substr($word, $chunkLen);
                }
                $test = $line === '' ? $word : $line . ' ' . $word;
                if ($pdf->GetStringWidth($test) > $w - 2 && $line !== '') {
                    $lines[] = $line;
                    $line = $word;
                } else {
                    $line = $test;
                }
            }
            if ($line !== '' || empty($lines)) $lines[] = $line;
            return $lines;
        };

        $lineH = 5;
        $headerRowH = 9;
        $cols = [
            'Sl' => 8, 'Invoice' => 28, 'Date' => 24, 'Vendor' => 48, 'Description' => 62,
            'RefNo' => 30, 'ServiceReport' => 26, 'Material' => 26, 'Labour' => 26,
            'Amount' => 28, 'Lineitem' => 50, 'Building' => 44,
        ];

        $drawHeaderRow = function() use ($pdf, $marginLeft, $cols, $headerRowH) {
            $pdf->SetX($marginLeft);
            $pdf->SetFillColor(4, 93, 194);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell($cols['Sl'], $headerRowH, 'Sl', 1, 0, 'C', true);
            $pdf->Cell($cols['Invoice'], $headerRowH, 'Invoice No', 1, 0, 'L', true);
            $pdf->Cell($cols['Date'], $headerRowH, 'Date', 1, 0, 'C', true);
            $pdf->Cell($cols['Vendor'], $headerRowH, 'Vendor', 1, 0, 'L', true);
            $pdf->Cell($cols['Description'], $headerRowH, 'Description', 1, 0, 'L', true);
            $pdf->Cell($cols['RefNo'], $headerRowH, 'Refer No', 1, 0, 'L', true);
            $pdf->Cell($cols['ServiceReport'], $headerRowH, 'Service Report No', 1, 0, 'L', true);
            $pdf->Cell($cols['Material'], $headerRowH, 'Material', 1, 0, 'R', true);
            $pdf->Cell($cols['Labour'], $headerRowH, 'Labour', 1, 0, 'R', true);
            $pdf->Cell($cols['Amount'], $headerRowH, 'Amount', 1, 0, 'R', true);
            $pdf->Cell($cols['Lineitem'], $headerRowH, 'Lineitem Desc', 1, 0, 'L', true);
            $pdf->Cell($cols['Building'], $headerRowH, 'Building Name', 1, 1, 'L', true);
            $pdf->SetTextColor(0, 0, 0);
        };

        $drawHeader();
        $drawHeaderRow();

        $pdf->SetFont('Arial', '', 9);
        $sl = 1;
        $grandMaterial = 0;
        $grandLabour   = 0;
        $grandAmount   = 0;
        $total = count($rows);

        foreach ($rows as $i => $row) {
            $pdf->SetFont('Arial', '', 9);

            $vendorLines   = $wrap($row->vendor, $cols['Vendor']);
            $descLines     = $wrap($row->description, $cols['Description']);
            $refNoLines    = $wrap($row->refer_no, $cols['RefNo']);
            $srLines       = $wrap($row->service_report_no, $cols['ServiceReport']);
            $lineitemLines = $wrap($row->lineitem_desc, $cols['Lineitem']);
            $buildingLines = $wrap($row->building_name, $cols['Building']);

            $maxLines = max(1, count($vendorLines), count($descLines), count($refNoLines), count($srLines), count($lineitemLines), count($buildingLines));
            $rowH = $maxLines * $lineH;

            if ($pdf->GetY() + $rowH > $pageBreakY) {
                $drawHeader();
                $drawHeaderRow();
                $pdf->SetFont('Arial', '', 9);
            }

            $grandMaterial += (float)$row->material_charge;
            $grandLabour   += (float)$row->labour_charge;
            $grandAmount   += (float)$row->amount;

            $x = $marginLeft;
            $y = $pdf->GetY();

            $drawWrappedCell = function($x, $y, $w, $rowH, array $lines) use ($pdf, $lineH) {
                $pdf->Rect($x, $y, $w, $rowH);
                $ty = $y;
                foreach ($lines as $line) {
                    $pdf->SetXY($x + 1, $ty);
                    $pdf->Cell($w - 2, $lineH, $line, 0, 0, 'L');
                    $ty += $lineH;
                }
            };

            $drawCenteredCell = function($x, $y, $w, $rowH, $text, $align = 'L') use ($pdf, $lineH) {
                $pdf->Rect($x, $y, $w, $rowH);
                $pdf->SetXY($x + 1, $y + ($rowH - $lineH) / 2);
                $pdf->Cell($w - 2, $lineH, $text, 0, 0, $align);
            };

            $drawCenteredCell($x, $y, $cols['Sl'], $rowH, $sl, 'C'); $x += $cols['Sl'];
            $drawCenteredCell($x, $y, $cols['Invoice'], $rowH, $fit($row->invoice_no, $cols['Invoice'])); $x += $cols['Invoice'];
            $drawCenteredCell($x, $y, $cols['Date'], $rowH, $row->invoice_date ? date('d/m/Y', strtotime($row->invoice_date)) : '', 'C'); $x += $cols['Date'];
            $drawWrappedCell($x, $y, $cols['Vendor'], $rowH, $vendorLines); $x += $cols['Vendor'];
            $drawWrappedCell($x, $y, $cols['Description'], $rowH, $descLines); $x += $cols['Description'];
            $drawWrappedCell($x, $y, $cols['RefNo'], $rowH, $refNoLines); $x += $cols['RefNo'];
            $drawWrappedCell($x, $y, $cols['ServiceReport'], $rowH, $srLines); $x += $cols['ServiceReport'];
            $drawCenteredCell($x, $y, $cols['Material'], $rowH, number_format((float)$row->material_charge, 3), 'R'); $x += $cols['Material'];
            $drawCenteredCell($x, $y, $cols['Labour'], $rowH, number_format((float)$row->labour_charge, 3), 'R'); $x += $cols['Labour'];
            $drawCenteredCell($x, $y, $cols['Amount'], $rowH, number_format((float)$row->amount, 3), 'R'); $x += $cols['Amount'];
            $drawWrappedCell($x, $y, $cols['Lineitem'], $rowH, $lineitemLines); $x += $cols['Lineitem'];
            $drawWrappedCell($x, $y, $cols['Building'], $rowH, $buildingLines);

            $pdf->SetXY($marginLeft, $y + $rowH);
            $sl++;

            if ($total > 0 && $i % 50 === 0) {
                $pct = 6 + (int) round((($i + 1) / $total) * 39);
                $send(['pct' => $pct, 'msg' => 'Building report — row ' . ($i + 1) . ' / ' . $total]);
            }
        }

        if ($pdf->GetY() > $pageBreakY - $headerRowH) {
            $drawHeader();
            $drawHeaderRow();
            $pdf->SetFont('Arial', '', 9);
        }

        $pdf->SetX($marginLeft);
        $pdf->SetFont('Arial', 'B', 9);
        $labelW = $cols['Sl'] + $cols['Invoice'] + $cols['Date'] + $cols['Vendor'] + $cols['Description'] + $cols['RefNo'] + $cols['ServiceReport'];
        $pdf->Cell($labelW, $headerRowH, 'Grand Total', 1, 0, 'R', true);
        $pdf->SetFillColor(222, 235, 250);
        $pdf->Cell($cols['Material'], $headerRowH, number_format($grandMaterial, 3), 1, 0, 'R', true);
        $pdf->Cell($cols['Labour'], $headerRowH, number_format($grandLabour, 3), 1, 0, 'R', true);
        $pdf->Cell($cols['Amount'], $headerRowH, number_format($grandAmount, 3), 1, 0, 'R', true);
        $pdf->Cell($cols['Lineitem'] + $cols['Building'], $headerRowH, '', 1, 1, 'L', true);

        $pdf->Output('F', $reportFilePath);
        $send(['pct' => 45, 'msg' => 'PDF report ready']);
    }

    // ── In-house service reports, one combined PDF per building ────────────
    $send(['pct' => 48, 'msg' => 'Checking in-house service reports…']);
    $refs = $this->inHouseServiceReportRefs($date1, $date2, $buildingName);

    $zipFilePath = null;
    $zipFileName = null;

    if (!empty($refs)) {
        $byBuilding = [];
        foreach ($refs as $ref) {
            $b = $ref->building_name ?: 'Unassigned';
            $byBuilding[$b][] = $ref->service_report_no;
        }

        $logoPath2    = public_path('img/logo-1-v2.png');
        $addressPath2 = public_path('img/address.png');
        $logo2        = file_exists($logoPath2) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath2)) : null;
        $address2     = file_exists($addressPath2) ? 'data:image/png;base64,' . base64_encode(file_get_contents($addressPath2)) : null;

        $totalB = count($byBuilding);
        $files  = [];
        $bi     = 0;

        foreach ($byBuilding as $building => $serviceReportNos) {
            $bi++;
            $pct = 50 + (int) round(($bi / $totalB) * 45);
            $send(['pct' => $pct, 'msg' => 'Service reports — ' . $building . ' (' . $bi . ' / ' . $totalB . ')']);

            $srRows = $this->serviceReportDetailsByNumbers($serviceReportNos);
            if (empty($srRows)) {
                continue;
            }

            $reports = [];
            foreach ($srRows as $row) {
                $key = $row->service_report_no;
                if (!isset($reports[$key])) {
                    $reports[$key] = ['header' => $row, 'items' => []];
                }
                $reports[$key]['items'][] = $row;
            }

            foreach ($reports as &$report) {
                $signatureFile = $report['header']->tenant_signature;
                $signaturePath = $signatureFile ? storage_path('app/public/TenantSignature/' . $signatureFile) : null;
                $report['signature'] = ($signaturePath && file_exists($signaturePath))
                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath))
                    : null;
            }
            unset($report);

            $pdfData = ['reports' => $reports, 'logo' => $logo2, 'address' => $address2];
            $buildingPdf = \PDF::loadView('maintenance::reports.service_report_v2_pdf', $pdfData)->setPaper('a3', 'portrait');

            $safeBuilding = preg_replace('/[^A-Za-z0-9_\-]/', '_', $building);
            $fileName = $safeBuilding . '.pdf';
            $filePath = $tempDir . '/' . $fileName;
            $buildingPdf->save($filePath);

            $files[] = ['path' => $filePath, 'name' => $fileName];
        }

        if (count($files) === 1) {
            $zipFilePath = $files[0]['path'];
            $zipFileName = $files[0]['name'];
        } elseif (count($files) > 1) {
            $send(['pct' => 96, 'msg' => 'Creating service reports ZIP…']);
            $zipFileName = 'Maintenance_Service_Reports_' . $date2 . '.zip';
            $zipFilePath = $tempDir . '/' . $zipFileName;
            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath, \ZipArchive::CREATE) === true) {
                foreach ($files as $file) {
                    $zip->addFile($file['path'], $file['name']);
                }
                $zip->close();
            }
            foreach ($files as $file) {
                if (file_exists($file['path']) && $file['path'] !== $zipFilePath) {
                    unlink($file['path']);
                }
            }
        }
    }

    $reportToken = uniqid('mirv2r_', true);
    cache()->put('mirv2_dl_' . $reportToken, ['path' => $reportFilePath, 'name' => $reportFileName], now()->addMinutes(5));

    $payload = ['pct' => 100, 'msg' => 'Complete! Starting download…', 'done' => true, 'reportToken' => $reportToken];

    if ($zipFilePath) {
        $zipToken = uniqid('mirv2z_', true);
        cache()->put('mirv2_dl_' . $zipToken, ['path' => $zipFilePath, 'name' => $zipFileName], now()->addMinutes(5));
        $payload['zipToken'] = $zipToken;
    }

    $send($payload);
    exit;
}

public function maintenanceInvoiceReportV2Download(string $token)
{
    $info = cache()->get('mirv2_dl_' . $token);
    abort_if(!$info || !file_exists($info['path']), 404, 'File not found or expired.');
    return response()->download($info['path'], $info['name'])->deleteFileAfterSend(true);
}

/**
 * The distinct (service_report_no, building_name) pairs behind the
 * In-house maintenance invoice lines in the given filter window - the
 * only lines that have a service report to bundle (Subcontractor lines
 * never have mid.service_report_id set).
 */
private function inHouseServiceReportRefs($date1, $date2, $buildingName = '')
{
    $where  = ['mi.deleted_at IS NULL', 'mi.maintenance_invoice_date BETWEEN ? AND ?', 'mid.service_report_id IS NOT NULL'];
    $params = [$date1, $date2];

    if ($buildingName !== '') {
        $where[]  = 'b.building_name = ?';
        $params[] = $buildingName;
    }

    $whereSql = implode(' AND ', $where);

    $sql = "
        SELECT DISTINCT mid.invoice_desc AS service_report_no, b.building_name
        FROM maintenance_invoices mi
        INNER JOIN maintenance_invoice_details mid ON mid.maintenance_invoice_id = mi.id
        LEFT JOIN buildings b ON b.id = mid.building_id
        WHERE {$whereSql}
    ";

    return DB::select($sql, $params);
}

/**
 * Same join structure as serviceReportV2Query(), but looked up by exact
 * service_report_no values instead of a date range - these numbers are
 * already known (from the invoice lines), so this fetches precisely the
 * reports being bundled rather than re-deriving them from a date filter.
 */
private function serviceReportDetailsByNumbers(array $serviceReportNos)
{
    if (empty($serviceReportNos)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($serviceReportNos), '?'));

    $sql = "
        SELECT cs.service_report_no,
               j.complaint_date,
               ce.complaint_no,
               e.employee_name AS technician,
               b.building_name,
               u.unit_no,
               ce.complainer_name,
               ce.complaint_mob_no,
               split_part(cs.tenant_signature, '/', 3) AS tenant_signature,
               cs.complaint_assign_note,
               inv.inventories_name,
               ci.quantity AS qty,
               ci.material_charge,
               ci.labour_charge,
               ci.total_charge,
               cs.created_at
        FROM complaint_service_report cs
        LEFT JOIN complaint_service_report_checklist csr ON csr.complaint_service_report_id = cs.id
        LEFT JOIN complaint_checklists cc ON cc.id = csr.checklist_id
        LEFT JOIN complaint_enquiries ce ON ce.id = cc.complaint_enquiries_id
        LEFT JOIN users us ON us.id = cc.sub_assigned_to
        LEFT JOIN employees e ON e.id = us.user_type_id
        LEFT JOIN buildings b ON b.id = ce.building_id
        LEFT JOIN units u ON u.id = ce.unit_id
        LEFT JOIN complaint_service_report_inv ci ON ci.complaint_service_report_id = cs.id
        LEFT JOIN inventories inv ON inv.id = ci.inventory_id
        LEFT JOIN (
            SELECT cs.service_report_no, MAX(ce.complaint_date) AS complaint_date
            FROM complaint_service_report cs
            LEFT JOIN complaint_service_report_checklist csr ON csr.complaint_service_report_id = cs.id
            LEFT JOIN complaint_checklists cc ON cc.id = csr.checklist_id
            LEFT JOIN complaint_enquiries ce ON ce.id = cc.complaint_enquiries_id
            GROUP BY cs.service_report_no
        ) j ON j.service_report_no = cs.service_report_no
        WHERE cs.service_report_no IN ({$placeholders})
        ORDER BY ce.complaint_no DESC
    ";

    return DB::select($sql, $serviceReportNos);
}

/**
 * Bundles every in-house service report matching the filter into a ZIP -
 * one combined multi-page PDF per building (same layout as Service
 * Report v2), so downloading "In-house" from the Maintenance Invoice
 * Report v2 screen gets you the underlying certificates grouped by
 * building instead of just the invoice-line totals.
 */
/**
 * Fired alongside the main PDF/Excel download (see the report search
 * form's JS) so a single Generate click produces both the invoice report
 * and this ZIP of the underlying in-house service reports, grouped by
 * building, for the same date/building filter.
 */
public function maintenanceInvoiceServiceReportsZipDownload(Request $request){
    $date1      = $request['start_date'];
    $date2      = $request['end_date'];
    $buildingId = $request['building_name'] ?? '';

    $buildingName = '';
    if (!empty($buildingId)) {
        $building = Building::find($buildingId);
        $buildingName = $building ? $building->building_name : '';
    }

    return $this->maintenanceInvoiceServiceReportsZip($date1, $date2, $buildingName);
}

private function maintenanceInvoiceServiceReportsZip($date1, $date2, $buildingName = '')
{
    ini_set('memory_limit', '512M');
    set_time_limit(300);

    $refs = $this->inHouseServiceReportRefs($date1, $date2, $buildingName);

    if (empty($refs)) {
        return response('No in-house service reports found for the selected filters.', 404);
    }

    $byBuilding = [];
    foreach ($refs as $ref) {
        $building = $ref->building_name ?: 'Unassigned';
        $byBuilding[$building][] = $ref->service_report_no;
    }

    $logoPath    = public_path('img/logo-1-v2.png');
    $addressPath = public_path('img/address.png');
    $logo        = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    $address     = file_exists($addressPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($addressPath)) : null;

    $tempDir = storage_path('app/temp/maintenance_service_reports_' . time());
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    $files = [];

    foreach ($byBuilding as $building => $serviceReportNos) {
        $rows = $this->serviceReportDetailsByNumbers($serviceReportNos);
        if (empty($rows)) {
            continue;
        }

        $reports = [];
        foreach ($rows as $row) {
            $key = $row->service_report_no;
            if (!isset($reports[$key])) {
                $reports[$key] = ['header' => $row, 'items' => []];
            }
            $reports[$key]['items'][] = $row;
        }

        foreach ($reports as &$report) {
            $signatureFile = $report['header']->tenant_signature;
            $signaturePath = $signatureFile ? storage_path('app/public/TenantSignature/' . $signatureFile) : null;
            $report['signature'] = ($signaturePath && file_exists($signaturePath))
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath))
                : null;
        }
        unset($report);

        $pdfData = ['reports' => $reports, 'logo' => $logo, 'address' => $address];
        $pdf = \PDF::loadView('maintenance::reports.service_report_v2_pdf', $pdfData)->setPaper('a3', 'portrait');

        $safeBuilding = preg_replace('/[^A-Za-z0-9_\-]/', '_', $building);
        $fileName = $safeBuilding . '.pdf';
        $filePath = $tempDir . '/' . $fileName;
        $pdf->save($filePath);

        $files[] = ['path' => $filePath, 'name' => $fileName];
    }

    if (empty($files)) {
        return response('No in-house service reports found for the selected filters.', 404);
    }

    if (count($files) === 1) {
        $file = $files[0];
        return response()->download($file['path'], $file['name'])->deleteFileAfterSend(true);
    }

    $zipFileName = 'Maintenance_Service_Reports_' . $date2 . '.zip';
    $zipPath = $tempDir . '/' . $zipFileName;
    $zip = new \ZipArchive();
    if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
        foreach ($files as $file) {
            $zip->addFile($file['path'], $file['name']);
        }
        $zip->close();
    }

    foreach ($files as $file) {
        if (file_exists($file['path'])) {
            unlink($file['path']);
        }
    }

    return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
}
/*
 *
 * Maintenance Invoice Report v2 ends
 *
 */
}