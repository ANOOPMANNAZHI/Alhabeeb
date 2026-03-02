<?php

namespace Modules\Sales\Http\Controllers;

use Modules\Sales\Entities\Sales;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\General\Entities\WorkFlowProcess;
use Modules\Masters\Entities\Employee;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class LandloardController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');         
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $unassigned_lists = DB::table('sales_enquiries')
            ->join('work_flow_categories', function($join)
             {
               $join->on('sales_enquiries.price_range_id', '=', 'work_flow_categories.price_range_id');
               $join->on('sales_enquiries.location_id','=', 'work_flow_categories.location_id');

             })
            ->join('process_assigns', 'process_assigns.work_flow_category_id', '=', 'work_flow_categories.id')
            ->select('sales_enquiries.*')
            ->orWhere('process_assigns.user_id','=', \Auth::user()->id)
            ->where('process_assigns.work_flow_process_id', '=', 1)
            ->where('sales_enquiries.work_flow_process_id', '=', 1)
            ->where('process_assigns.role_id','=', \Auth::user()->getRoleNames())
            ->where('sales_enquiries.sales_type', '=', 2)
            ->paginate(10);
        $employees = Employee::with('user')->get();
        
        //dd($unassigned_lists);
        return view('sales::landloard_lead_assign',compact('unassigned_lists','employees'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('sales::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request,SalesEnquiry $salesEnquiry)
    {
        $thearrays = explode(",", $request['enquiryIds']);

        foreach($thearrays as $enquiry_id) {
            $enquiry_type = SalesEnquiry::where('id','=',$enquiry_id)->first();
            Sales::create([
              'user_id' => $request['user_id'],
              'role_id' => $request['role'],
              'sales_enquiry_id' =>$enquiry_id,
              'sales_type' => $enquiry_type->sales_type,
              'work_flow_process_id' => '2',
              'created_by' => \Auth::user()->id,
            ]);
            $salesEnquiry::where('id', $enquiry_id)->update([
              'work_flow_process_id' => '2',
              'updated_by' => \Auth::user()->id,
            ]);
        }   
        session()->flash('success', 'Lead Assigned Successfully');
        return redirect()->route('leadAssign.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('sales::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('sales::edit');
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

    /**
     * Search a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function search(Request $request)
    {
       $mobile_no = $request['mobile_no'];
       $name      = $request['enquiry_name'];
       if($mobile_no || $name !="") {
            $unassigned_lists = DB::table('sales_enquiries')
            ->join('work_flow_categories', function($join)
             {
               $join->on('sales_enquiries.price_range_id', '=', 'work_flow_categories.price_range_id');
               $join->on('sales_enquiries.location_id','=', 'work_flow_categories.location_id');

             })
            ->join('process_assigns', 'process_assigns.work_flow_category_id', '=', 'work_flow_categories.id')
            ->select('sales_enquiries.*')
            ->where('process_assigns.user_id','=', \Auth::user()->id)
            ->where('process_assigns.work_flow_process_id', '=', 1)
            ->where('sales_enquiries.work_flow_process_id', '=', 1)
            ->where('process_assigns.role_id','=', \Auth::user()->getRoleNames())
            ->where('sales_enquiries.sales_mobile_no', '=', $mobile_no)
            ->where('sales_enquiries.sales_enquiry_name', '=', $name)
            ->where('sales_enquiries.sales_type', '=', 2)->paginate(10);
            

            /*$unassigned_lists = SalesEnquiry::where('work_flow_process_id', '=', '1')
                        ->where('sales_mobile_no', '=', $mobile_no)
                        ->where('sales_enquiry_name', '=', $name)->paginate(10);*/
       } else {
           $unassigned_lists = DB::table('sales_enquiries')
            ->join('work_flow_categories', function($join)
             {
               $join->on('sales_enquiries.price_range_id', '=', 'work_flow_categories.price_range_id');
               $join->on('sales_enquiries.location_id','=', 'work_flow_categories.location_id');

             })
            ->join('process_assigns', 'process_assigns.work_flow_category_id', '=', 'work_flow_categories.id')
            ->select('sales_enquiries.*')
            ->where('process_assigns.user_id','=', \Auth::user()->id)
            ->where('process_assigns.work_flow_process_id', '=', 1)
            ->where('sales_enquiries.work_flow_process_id', '=', 1)
            ->where('process_assigns.role_id','=', \Auth::user()->getRoleNames())
            ->where('sales_enquiries.sales_type', '=', 2)
            ->paginate(10);
       }
       
                //dd($unassigned_lists); 
       $employees = Employee::with('user')->get();
       return view('sales::landloard_lead_assign',compact('unassigned_lists','employees'));
    }
}
