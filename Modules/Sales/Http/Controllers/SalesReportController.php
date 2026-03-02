<?php

namespace Modules\Sales\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Sales\Entities\Tenant;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\employee;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\BuildingType;
use Modules\Masters\Entities\ManagementType;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\Masters\Entities\EnquirySource;
use Modules\Masters\Entities\UnitType;
use Modules\Masters\Entities\Location;
use App\User;
use Auth;
use DB;
use PDF;
use config;
use JasperPHP;

class SalesReportController extends Controller
{
	public function index()
    {
      return view('backoffice::index');
    }

	public function showSalesEnquiries(){

		$enquirySources = EnquirySource::all();
		$unitTypes = UnitType::all();
		$locations = Location::all();
		$salesUsers =  User::where('user_type','employee')->where('default_role',2)->whereHas('employee', function ($query){
          $query->active();                      
        })->get();

        


 	 	return view('sales::reports.sales_enquiry_report',compact('enquirySources','unitTypes','locations','salesUsers'));
    } 
    public function salesEnquiryReportPdf(Request $request){
		 $db = config('report.database');
		 $username   = Auth::user()->username;
		 $Date1      =  $request['start_date'];
		 $Date2      =  $request['end_date'];
		 $UnitType   =  $request['unit_type'];
		 $Location   =  $request['location'];
		 $Source     =  $request['source'];
		 $AssignedTo =  $request['assignedto'];
//print_r(json_encode($Date1));exit();
		
 
		 $logo = getLogoPath();
		 $jasper = new JasperPHP;
		     //dd($tenant_code);
		 if(isset($request->download_type)){

		  if($request->download_type == 'pdf'){
		    if(!empty($Date1) && !empty($Date2) && empty($UnitType) && empty($Location)&& empty($Source)&& empty($AssignedTo)){ //Mandotory

		// Compile a JRXML to Jasper
		    /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_receipt.jrxml'))->execute();
		    print_r($a); */
		// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
		    $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/enquiryReportNew.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"unittype" => $UnitType,"location" => $Location,"source" => $Source,"assignedto" => $AssignedTo,"logo" => $logo),$db)->execute();

		    $file= getReportUrl()."vendor/cossou/jasperphp/examples/enquiryReportNew.pdf";
		    return redirect()->away($file);
		    print_r($r);
		  } else{

		// Compile a JRXML to Jasper
		    /*$a=JasperPHP::compile(base_path('/vendor/cossou/jasperphp/examples/rent_receipt_compo.jrxml'))->execute();
		    print_r($a); */
		// Process a Jasper file to PDF and RTF (you can use directly the .jrxml)
		    $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/enquiryReportNew_Compo.jrxml'),false,array('pdf'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"unittype" => $UnitType,"location" => $Location,"source" => $Source,"assignedto" => $AssignedTo,"logo" => $logo),$db)->execute();

		    $file= getReportUrl()."vendor/cossou/jasperphp/examples/enquiryReportNew_Compo.pdf";
		    return redirect()->away($file);
		    print_r($r);
		  }
		}else{
		     if(!empty($Date1) && !empty($Date2) && empty($UnitType) && empty($Location)&& empty($Source)&& empty($AssignedTo)){ //Mandotory

		   $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/enquiryReportNew.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"unittype" => $UnitType,"location" => $Location,"source" => $Source,"assignedto" => $AssignedTo,"logo" => $logo),$db)->execute();

		   $file1= getReportUrl()."vendor/cossou/jasperphp/examples/enquiryReportNew.xlsx";


		   return redirect()->away($file1);
		 }
		 else{

		  $r=JasperPHP::process(base_path('/vendor/cossou/jasperphp/examples/enquiryReportNew_Compo.jrxml'),false,array('xlsx'),array("username" => $username,"Date1" => $Date1,"Date2" => $Date2,"unittype" => $UnitType,"location" => $Location,"source" => $Source,"assignedto" => $AssignedTo,"logo" => $logo),$db)->execute();

		  $file1= getReportUrl()."vendor/cossou/jasperphp/examples/enquiryReportNew_Compo.xlsx";


		  return redirect()->away($file1);
		}
		}
		}
		}

}