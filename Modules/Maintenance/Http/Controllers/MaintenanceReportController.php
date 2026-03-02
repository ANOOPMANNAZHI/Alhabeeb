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
 * Service Report ends
 *
 */
}