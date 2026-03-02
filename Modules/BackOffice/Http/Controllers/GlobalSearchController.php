<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Modules\BackOffice\Entities\TenantContractEdit;
use Modules\Sales\Entities\LandlordContract;
use Modules\BackOffice\Entities\TenantContractRevokeTemp;
use Modules\BackOffice\Entities\TenantContractRevokeNote;	
use Modules\BackOffice\Entities\TenantContractComment;  
use Modules\BackOffice\Entities\DiscussionForums;  
use Modules\BackOffice\Entities\DiscussionCategory;  
use Modules\BackOffice\Entities\ViewTenantContract;  
use Modules\Masters\Entities\VacantVacancyLoss;

use Modules\Masters\Entities\Bank;
use Modules\Masters\Entities\Employee;
use Modules\Masters\Entities\Legal;
use Modules\Sales\Entities\Tenant;
use Modules\Sales\Entities\TenantContract;
use Modules\Sales\Entities\TenantDocument;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Sales\Entities\Sales;
use Modules\Sales\Entities\SalesNote;
use Modules\Sales\Entities\SalesActivity;
use Modules\Sales\Entities\SalesUsers;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use App\User;
use DB;
use Illuminate\Support\Facades\Mail;
use Modules\Masters\Events\LegalApprove;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Nationality;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Occupant;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\TenantType;
use Modules\Masters\Entities\TenantDocs;
use Modules\Sales\Http\Controllers\TenantContractController as Contract ;
use Modules\General\Http\Controllers\GeneralController as General ;
use Modules\Sales\Events\RevokeCreate;
use Modules\Sales\Events\RevokeEnquiry;
use Modules\Masters\Emails\LegalEmail;
use Modules\Sales\Entities\TenantSequence;
use Dynamics;
use App\Setting;

class GlobalSearchController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function __construct()
    {
      $this->middleware('auth');    
      //  $this->middleware('permission:tenant_contract_direct_list', ['only' => ['index','show']]);   
      $this->middleware('permission:edit_tenant_contract_direct', ['only' => ['edit','update']]);
      $this->middleware('permission:add_tenant_contract_direct', ['only' => ['create','store']]);   
      $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
       
    }
    /*
    public function index(Request $request)
    {

      return view('backoffice::index');
    }
    */
    public function contractGlobleSearch(Request $request){
      
      if(isset($request->fieldName) && isset($request->fieldValue)){

        $fieldName       = $request->fieldName;
        $fieldValue      = $request->fieldValue;
        $tab             = isset($request->tab)?$request->tab:'sales';
        $searchResult    = array();

        switch ($fieldName) {
          case $fieldName== 'building_code' || $fieldName=='building_name' || $fieldName=='unit_no' || $fieldName=='tenant_name'|| $fieldName=='resident_id' || $fieldName=='tenant_contact_no' || $fieldName=='locations_name' || $fieldName=='tenant_employer_name' || $fieldName=='tenant_contact_email' || $fieldName=='unit_types_name' || $fieldName=='tenant_contract_muncipality_agr_no' :
                    
            if($tab=='sales'){
              
              $searchResult  = DB::table('tenant_contracts as tc')->select(
                                  'se.id as salesEnquiryId',
                                  'se.sales_enquiry_no',
                                  'se.created_at',
                                  'se.sales_enquiry_name',
                                  'se.sales_size',
                                  'se.enquiry_owner',
                                  'ut.unit_types_name',
                                  'se.work_flow_processes_code',
                                  'wfp.work_flow_processes_name'
                              )
                              ->leftjoin('buildings as b', 'b.id', '=', 'tc.building_id')
                              ->leftjoin('locations as l', 'l.id', '=', 'b.location_id')
                              ->leftjoin('tenant as t', 't.id', '=', 'tc.tenant_id')
                              ->leftjoin('units as u', 'u.id', '=', 'tc.unit_id')
                              ->leftjoin('sales_enquiries as se', 'se.id', '=', 'tc.sale_enquiry_id')
                              ->leftjoin('preferred_unit_types as put', 'put.sale_enquiry_id', '=', 'se.id')
                              ->leftjoin('unit_types as ut', 'put.unit_type_id', '=', 'ut.id')
                              ->leftjoin('work_flow_processes as wfp', 'wfp.work_flow_processes_code', '=', 'se.work_flow_processes_code')
                              ->where('se.sales_type', 1)->where($fieldName, 'ilike', '%'.$fieldValue.'%')->where('se.created_at','>',Carbon::now()->subYear()->format('Y-m-d'))->paginate($this->noOfRecord);
            }
            elseif($tab=='backoffice'){

              // tenant_contract_is_reg_municipality - 1 registered, 0 - not 
              $searchResult  = DB::table('tenant_contracts as tc')->select(
                                  'tc.id as contractId',
                                  'tc.tenant_contract_no',
                                  'tc.tenant_contract_duration_countdown',
                                  'tc.tenant_contract_valid_to_date',
                                  'tc.tenant_contract_status',
                                  'tc.tenant_contract_last_paid_date',
                                  'tc.tenant_contract_os',
                                  'tc.tenant_contract_is_reg_municipality',
                                  't.tenant_name',
                                  'b.building_name',
                                  'u.unit_no',
                                  'ut.unit_types_name',
                                  'emp.employee_name'                                  
                              )
                              ->join('buildings as b', 'b.id', '=', 'tc.building_id')
                              ->join('locations as l', 'l.id', '=', 'b.location_id')
                              ->join('tenant as t', 't.id', '=', 'tc.tenant_id')
                              ->join('units as u', 'u.id', '=', 'tc.unit_id')
                              ->join('unit_types as ut', 'ut.id', '=', 'u.unit_type_id')
                              ->leftjoin('preferred_buildings as pb', 'pb.building_id', '=', 'tc.building_id')
                              ->leftjoin('are_buildings as ab', 'ab.id', '=', 'pb.are_building_id')
                              ->leftjoin('users as us', function($join) {
                                  $join->on( 'us.id', '=', 'ab.user_id')
                                  ->where('us.user_type', 'employee');
                              }) 
                              ->leftjoin('employees as emp','emp.id', '=', 'us.user_type_id')
                              ->where($fieldName, 'ilike', '%'.$fieldValue.'%')->where('tc.tenant_contract_effective_date','>',Carbon::now()->subYear()->format('Y-m-d'))->paginate($this->noOfRecord);
                    //dd($searchResult);
            }      
            elseif($tab=='maintenance'){

              // tenant_contract_is_reg_municipality - 1 registered, 0 - not 
              $searchResult  = DB::table('complaint_enquiries as ce')->select(
                                  'ce.id as complainttId',
                                  'tc.id  AS contractId',
                                  'ce.complaint_no',
                                  'ce.complaint_date',
                                  'ce.complaint_status',
                                  DB::raw("string_agg(cc.complaint_ticket_no, ',' ORDER BY cc.id) AS complaint_ticket_no"),
                                  DB::raw("string_agg(distinct t.tenant_name, ', ') AS tenant_name"),
                                  'b.building_name',
                                  DB::raw("string_agg(distinct u.unit_no, ', ') AS unit_no"),
                                  DB::raw("string_agg(distinct csr.service_report_no, ',') AS service_report_no"),
                                  'mi.maintenance_invoice_no',
                                  'emp.employee_name'                                  
                              )
                              ->join('buildings as b', 'b.id', '=', 'ce.building_id')
                              ->join('locations as l', 'l.id', '=', 'b.location_id')
                              ->leftjoin('tenant as t', 't.id', '=', 'ce.tenant_id')
                              ->leftjoin('units as u', 'u.id', '=', 'ce.unit_id')
                              ->leftjoin('unit_types as ut', 'ut.id', '=', 'u.unit_type_id')
                              ->leftjoin('tenant_contracts as tc', function($join) {
                                  $join->on('tc.unit_id', '=', 'u.id')
                                  ->where('tc.tenant_contract_status', 1);
                              })
                              ->leftjoin('complaint_checklists as cc', 'cc.complaint_enquiries_id', '=', 'ce.id')
                              ->leftjoin('complaint_service_report_checklist as csrc', 'csrc.checklist_id', '=', 'cc.id')
                              ->leftjoin('complaint_service_report as csr', 'csr.id', '=', 'csrc.complaint_service_report_id')
                              ->leftjoin('maintenance_invoice_details as mid', 'mid.service_report_id', '=', 'csr.id')
                              ->leftjoin('maintenance_invoices as mi', 'mi.id', '=', 'mid.maintenance_invoice_id')
                              ->leftjoin('users as us', function($join) {
                                  $join->on( 'us.id', '=', 'mid.technician_id')
                                  ->where('us.user_type', 'employee');
                              }) 
                              ->leftjoin('employees as emp','emp.id', '=', 'us.user_type_id')
                              ->where($fieldName, 'ilike', '%'.$fieldValue.'%')->where('ce.created_at','>',Carbon::now()->subYear()->format('Y-m-d'))->groupBy('ce.id','b.id','tc.id','mi.id','emp.id')->paginate($this->noOfRecord);
                  //dd($searchResult);
            }        
            elseif($tab=='inspection'){

              $latestPosts = DB::table('termination')
                   ->select('contract_id', DB::raw('MAX(id) as id'))
                   ->whereIn('work_flow_processes_code', [503,504])
                   ->groupBy('contract_id');

              // tenant_contract_is_reg_municipality - 1 registered, 0 - not 

              $searchResult  = DB::table('tenant_contracts as tc')->select(
                                  'tc.id as contractId',
                                  'tc.tenant_contract_no',
                                  'tc.tenant_contract_duration_countdown',
                                  'tc.tenant_contract_valid_to_date',
                                  'tc.tenant_contract_status',
                                  'tc.tenant_contract_last_paid_date',
                                  'tc.tenant_contract_os',
                                  'tc.tenant_renewal_termination_status',
                                  'ter.termination_takenover_date',
                                  'ter.work_flow_processes_code',
                                  't.tenant_name',
                                  'b.building_name',
                                  'u.unit_no'   
                              )
                              ->join('buildings as b', 'b.id', '=', 'tc.building_id')
                              ->join('locations as l', 'l.id', '=', 'b.location_id')
                              ->join('tenant as t', 't.id', '=', 'tc.tenant_id')
                              ->join('units as u', 'u.id', '=', 'tc.unit_id')
                              ->join('unit_types as ut', 'ut.id', '=', 'u.unit_type_id')
                              ->join('termination as ter', 'ter.contract_id', '=', 'tc.id')
                              ->joinSub($latestPosts, 'latest_posts', function ($join) {
                                  $join->on('latest_posts.id', '=', 'ter.id');
                              })                              
                              ->where($fieldName, 'ilike', '%'.$fieldValue.'%')->groupBy('tc.id','t.id','b.id','u.id','ter.id')->paginate($this->noOfRecord);
                  //dd($searchResult);
            }       
            elseif($tab=='lease'){

              // tenant_contract_is_reg_municipality - 1 registered, 0 - not 
              $searchResult  = DB::table('landlord_contract as lc')->select(
                                  'lc.id as lcontractId',
                                  'lc.landlord_contract_no',
                                  'v.vendor_name',
                                  'b.building_name',
                                  'm.management_types_name',
                                  DB::raw("(DATE_PART('year', lc.landlord_contract_valid_to_date::date) - DATE_PART('year', lc.landlord_contract_valid_from_date::date)) * 12 +
                                  (DATE_PART('month', lc.landlord_contract_valid_to_date::date) - DATE_PART('month', lc.landlord_contract_valid_from_date::date))+1 as month_duration"),
                                  'lc.landlord_contract_valid_to_date',
                                  'lc.landlord_contract_amt',
                                  DB::raw("string_agg(distinct li.landlord_invoice_voucher_no, ',') AS landlord_invoice_voucher_no")
                              )
                              ->join('buildings as b', 'b.id', '=', 'lc.building_id')
                              ->join('locations as l', 'l.id', '=', 'b.location_id')
                              ->join('vendors as v', function($join) {
                                  $join->on(  'v.id', '=', 'lc.vendor_id')
                                  ->where('v.vendor_type_id', 2);
                              }) 
                              ->join('management_types as m', 'm.id', '=', 'lc.management_id')
                              ->leftjoin('landlord_invoice as li', 'li.landlord_contract_id', '=', 'lc.id')
                              ->leftjoin('units as u', 'u.building_id', '=', 'b.id')
                              ->leftjoin('unit_types as ut', 'ut.id', '=', 'u.unit_type_id')
                              ->leftjoin('tenant_contracts as tc', 'tc.building_id', '=', 'b.id')
                              ->leftjoin('tenant as t', 't.id', '=', 'tc.tenant_id')
                              ->where($fieldName, 'ilike', '%'.$fieldValue.'%')->groupBy('lc.id','v.id','b.id','m.id')->paginate($this->noOfRecord);
                 // dd($searchResult);
            }     
           
            break;

          case $fieldName=='complaint_no' || $fieldName=='service_report_no':

            if($tab=='sales'){
   
              $searchResult  = DB::table('complaint_enquiries as ce')->select(
                                  'se.id as salesEnquiryId',
                                  'se.sales_enquiry_no',
                                  'se.created_at',
                                  'se.sales_enquiry_name',
                                  'se.sales_size',
                                  'se.enquiry_owner',
                                  'ut.unit_types_name',
                                  'se.work_flow_processes_code',
                                  'wfp.work_flow_processes_name'                        
                              )
                              ->join('buildings as b', 'b.id', '=', 'ce.building_id')
                              ->join('tenant_contracts as tc', 'tc.building_id', '=', 'b.id')
                              ->join('sales_enquiries as se', 'se.id', '=', 'tc.sale_enquiry_id')
                              ->leftjoin('complaint_checklists as cc', 'cc.complaint_enquiries_id', '=', 'ce.id')
                              ->leftjoin('complaint_service_report_checklist as csrc', 'csrc.checklist_id', '=', 'cc.id')
                              ->leftjoin('complaint_service_report as csr', 'csr.id', '=', 'csrc.complaint_service_report_id')
                              ->leftjoin('preferred_unit_types as put', 'put.sale_enquiry_id', '=', 'se.id')
                              ->leftjoin('unit_types as ut', 'put.unit_type_id', '=', 'ut.id')
                              ->leftjoin('work_flow_processes as wfp', 'wfp.work_flow_processes_code', '=', 'se.work_flow_processes_code')
                              ->where('se.sales_type', 1)->where($fieldName, 'ilike', '%'.$fieldValue.'%')->where('se.created_at','>',Carbon::now()->subYear()->format('Y-m-d'))->groupBy('se.id','b.id','tc.id','ut.id','wfp.id')->paginate($this->noOfRecord);
            }
            elseif($tab=='backoffice'){

              // tenant_contract_is_reg_municipality - 1 registered, 0 - not 
              $searchResult  = DB::table('complaint_enquiries as ce')->select(
                                  'tc.id as contractId',
                                  'tc.tenant_contract_no',
                                  'tc.tenant_contract_duration_countdown',
                                  'tc.tenant_contract_valid_to_date',
                                  'tc.tenant_contract_status',
                                  'tc.tenant_contract_last_paid_date',
                                  'tc.tenant_contract_os',
                                  'tc.tenant_contract_is_reg_municipality',
                                  't.tenant_name',
                                  'b.building_name',
                                  'u.unit_no',
                                  'ut.unit_types_name',
                                  'emp.employee_name'                                  
                              )
                              ->join('tenant_contracts as tc', 'tc.building_id', '=', 'ce.building_id')
                              ->join('buildings as b', 'b.id', '=', 'ce.building_id')
                              ->leftjoin('complaint_checklists as cc', 'cc.complaint_enquiries_id', '=', 'ce.id')
                              ->leftjoin('complaint_service_report_checklist as csrc', 'csrc.checklist_id', '=', 'cc.id')
                              ->leftjoin('complaint_service_report as csr', 'csr.id', '=', 'csrc.complaint_service_report_id')
                              ->leftjoin('locations as l', 'l.id', '=', 'b.location_id')
                              ->leftjoin('tenant as t', 't.id', '=', 'tc.tenant_id')
                              ->leftjoin('units as u', 'u.id', '=', 'tc.unit_id')
                              ->leftjoin('unit_types as ut', 'ut.id', '=', 'u.unit_type_id')
                              ->leftjoin('preferred_buildings as pb', 'pb.building_id', '=', 'tc.building_id')
                              ->leftjoin('are_buildings as ab', 'ab.id', '=', 'pb.are_building_id')
                              ->leftjoin('users as us', function($join) {
                                  $join->on( 'us.id', '=', 'ab.user_id')
                                  ->where('us.user_type', 'employee');
                              }) 
                              ->leftjoin('employees as emp','emp.id', '=', 'us.user_type_id')
                              ->where($fieldName, 'ilike', '%'.$fieldValue.'%')->where('tc.tenant_contract_effective_date','>',Carbon::now()->subYear()->format('Y-m-d'))->groupBy('ce.id','b.id','tc.id','t.id','u.id','ut.id','emp.id')->paginate($this->noOfRecord);
                    //dd($searchResult);
            }      
            elseif($tab=='maintenance'){

              // tenant_contract_is_reg_municipality - 1 registered, 0 - not 
              $searchResult  = DB::table('complaint_enquiries as ce')->select(
                                  'ce.id as complainttId',
                                  'tc.id  AS contractId',
                                  'ce.complaint_no',
                                  'ce.complaint_date',
                                  'ce.complaint_status',
                                  DB::raw("string_agg(cc.complaint_ticket_no, ',' ORDER BY cc.id) AS complaint_ticket_no"),
                                  DB::raw("string_agg(distinct t.tenant_name, ', ') AS tenant_name"),
                                  'b.building_name',
                                  DB::raw("string_agg(distinct u.unit_no, ', ') AS unit_no"),
                                  DB::raw("string_agg(distinct csr.service_report_no, ',') AS service_report_no"),
                                  'mi.maintenance_invoice_no',
                                  'emp.employee_name'                                      
                              )
                              ->join('buildings as b', 'b.id', '=', 'ce.building_id')
                              ->join('locations as l', 'l.id', '=', 'b.location_id')
                              ->leftjoin('tenant as t', 't.id', '=', 'ce.tenant_id')
                              ->leftjoin('units as u', 'u.id', '=', 'ce.unit_id')
                              ->leftjoin('tenant_contracts as tc', 'tc.unit_id', '=', 'u.id')
                              ->leftjoin('complaint_checklists as cc', 'cc.complaint_enquiries_id', '=', 'ce.id')
                              ->leftjoin('complaint_service_report_checklist as csrc', 'csrc.checklist_id', '=', 'cc.id')
                              ->leftjoin('complaint_service_report as csr', 'csr.id', '=', 'csrc.complaint_service_report_id')
                              ->leftjoin('maintenance_invoice_details as mid', 'mid.service_report_id', '=', 'csr.id')
                              ->leftjoin('maintenance_invoices as mi', 'mi.id', '=', 'mid.maintenance_invoice_id')
                              ->leftjoin('users as us', function($join) {
                                  $join->on( 'us.id', '=', 'mid.technician_id')
                                  ->where('us.user_type', 'employee');
                              }) 
                              ->leftjoin('employees as emp','emp.id', '=', 'us.user_type_id')
                              ->where($fieldName, 'ilike', '%'.$fieldValue.'%')->where('ce.created_at','>',Carbon::now()->subYear()->format('Y-m-d'))->groupBy('ce.id','b.id','tc.id','mi.id','emp.id')->paginate($this->noOfRecord);
                  //dd($searchResult);
            }        
            elseif($tab=='inspection'){

              $latestPosts = DB::table('termination')
                   ->select('contract_id', DB::raw('MAX(id) as id'))
                   ->whereIn('work_flow_processes_code', [503,504])
                   ->groupBy('contract_id');

              // tenant_contract_is_reg_municipality - 1 registered, 0 - not 

              $searchResult  = DB::table('complaint_enquiries as ce')->select(
                                  'tc.id as contractId',
                                  'tc.tenant_contract_no',
                                  'tc.tenant_contract_duration_countdown',
                                  'tc.tenant_contract_valid_to_date',
                                  'tc.tenant_contract_status',
                                  'tc.tenant_contract_last_paid_date',
                                  'tc.tenant_contract_os',
                                  'tc.tenant_renewal_termination_status',
                                  'ter.termination_takenover_date',
                                  'ter.work_flow_processes_code',
                                  't.tenant_name',
                                  'b.building_name',
                                  'u.unit_no'   
                              )
                              ->join('tenant_contracts as tc', 'tc.building_id', '=', 'ce.building_id')
                              ->leftjoin('complaint_checklists as cc', 'cc.complaint_enquiries_id', '=', 'ce.id')
                              ->leftjoin('complaint_service_report_checklist as csrc', 'csrc.checklist_id', '=', 'cc.id')
                              ->leftjoin('complaint_service_report as csr', 'csr.id', '=', 'csrc.complaint_service_report_id')
                              ->join('buildings as b', 'b.id', '=', 'tc.building_id')
                              ->join('locations as l', 'l.id', '=', 'b.location_id')
                              ->join('tenant as t', 't.id', '=', 'tc.tenant_id')
                              ->join('units as u', 'u.id', '=', 'tc.unit_id')
                              ->join('termination as ter', 'ter.contract_id', '=', 'tc.id')
                              ->joinSub($latestPosts, 'latest_posts', function ($join) {
                                  $join->on('latest_posts.id', '=', 'ter.id');
                              })                              
                              ->where($fieldName, 'ilike', '%'.$fieldValue.'%')->groupBy('tc.id','t.id','b.id','u.id','ter.id')->paginate($this->noOfRecord);
                  //dd($searchResult);
            }       
            elseif($tab=='lease'){

              // tenant_contract_is_reg_municipality - 1 registered, 0 - not 
              $searchResult  = DB::table('complaint_enquiries as ce')->select(
                                   'lc.id as lcontractId',
                                  'lc.landlord_contract_no',
                                  'v.vendor_name',
                                  'b.building_name',
                                  'm.management_types_name',
                                  DB::raw("(DATE_PART('year', lc.landlord_contract_valid_to_date::date) - DATE_PART('year', lc.landlord_contract_valid_from_date::date)) * 12 +
                                  (DATE_PART('month', lc.landlord_contract_valid_to_date::date) - DATE_PART('month', lc.landlord_contract_valid_from_date::date))+1 as month_duration"),
                                  'lc.landlord_contract_valid_to_date',
                                  'lc.landlord_contract_amt',
                                  DB::raw("string_agg(distinct li.landlord_invoice_voucher_no, ',') AS landlord_invoice_voucher_no")
                              )
                              ->join('landlord_contract as lc', 'lc.building_id', '=', 'ce.building_id')
                              ->join('buildings as b', 'b.id', '=', 'lc.building_id')
                              ->join('locations as l', 'l.id', '=', 'b.location_id')
                              ->join('vendors as v', function($join) {
                                  $join->on(  'v.id', '=', 'lc.vendor_id')
                                  ->where('v.vendor_type_id', 2);
                              }) 
                              ->join('management_types as m', 'm.id', '=', 'lc.management_id')
                              ->leftjoin('complaint_checklists as cc', 'cc.complaint_enquiries_id', '=', 'ce.id')
                              ->leftjoin('complaint_service_report_checklist as csrc', 'csrc.checklist_id', '=', 'cc.id')
                              ->leftjoin('complaint_service_report as csr', 'csr.id', '=', 'csrc.complaint_service_report_id')
                              ->leftjoin('landlord_invoice as li', 'li.landlord_contract_id', '=', 'lc.id')
                              ->leftjoin('units as u', 'u.building_id', '=', 'b.id')
                              ->leftjoin('tenant_contracts as tc', 'tc.building_id', '=', 'b.id')
                              ->leftjoin('tenant as t', 't.id', '=', 'tc.tenant_id')
                              ->where($fieldName, 'ilike', '%'.$fieldValue.'%')->groupBy('lc.id','v.id','b.id','m.id')->paginate($this->noOfRecord);
                  //dd($searchResult);
            }     
            break;

          default:
            // code...
            break;
        }
        $url['sales'] = route('contractGlobleSearch', ['fieldName' => $fieldName, 'fieldValue'=>$fieldValue,'tab'=>'sales','limit'=>0,'submit'=>'submit']);
        $url['backoffice'] = route('contractGlobleSearch', ['fieldName' => $fieldName, 'fieldValue'=>$fieldValue,'tab'=>'backoffice','limit'=>0,'submit'=>'submit']);
        $url['maintenance'] = route('contractGlobleSearch', ['fieldName' => $fieldName, 'fieldValue'=>$fieldValue,'tab'=>'maintenance','limit'=>0,'submit'=>'submit']);
        $url['inspection'] = route('contractGlobleSearch', ['fieldName' => $fieldName, 'fieldValue'=>$fieldValue,'tab'=>'inspection','limit'=>0,'submit'=>'submit']);
        $url['lease'] = route('contractGlobleSearch', ['fieldName' => $fieldName, 'fieldValue'=>$fieldValue,'tab'=>'lease','limit'=>0,'submit'=>'submit']);

        return view('backoffice::GlobalSearch.index',compact('searchResult','url','tab','request'));
      }
      return view('backoffice::GlobalSearch.index');
    }    
   
}
