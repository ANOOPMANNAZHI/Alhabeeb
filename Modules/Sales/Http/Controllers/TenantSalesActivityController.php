<?php

namespace Modules\Sales\Http\Controllers;

use Modules\Sales\Entities\SalesActivity;
use Modules\Sales\Entities\Sales;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class TenantSalesActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('sales::index');
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
    public function store(Request $request)
    {
        $this->validate($request, [
            'sales_activities_name' => 'required',                    
            'sales_activity_type'   => 'required',
            'sales_activities_note'   => 'required',
            'sales_activities_due_date'   => 'required|date',
         ]);
        //dd($request['sales_activity_type']);
        $salesActivity =  SalesActivity::create([
          'sales_id' => $request['sales_id'],
          'user_id' => \Auth::user()->id,
          'sales_activities_name' => $request['sales_activities_name'],
          'sales_activities_note' => $request['sales_activities_note'],
          'sales_activities_summary_note' => $request['sales_activities_summary_note'],
          'sales_activities_due_date' => $request['sales_activities_due_date'],
          'sales_activity_type' =>  $request['sales_activity_type'],
          'sales_activities_time' =>  $request['sales_activities_time'],          
          'sales_activities_status' => 1,
          'created_by' => \Auth::user()->id,
        ]);
        $enquiryDetails = Sales::where('id','=',$request['sales_id'])->first();
        $stage = $request['stage'];
        session()->flash('success', 'Sales Activity Created Successfully');
        return redirect()->route('leadAssign.nextStage',['id'=>$enquiryDetails->sales_enquiry_id,'stage'=>$stage]);
        
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show(SalesActivity $salesActivity)
    {
        return view('sales::TenantSales.tenant_view_activity_modal',compact('salesActivity'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(SalesActivity $salesActivity)
    {
        //dd($salesActivity);
        return view('sales::TenantSales.tenant_add_activity_modal',compact('salesActivity'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,SalesActivity $salesActivity)
    {
        $this->validate($request, [
            'sales_activities_name' => 'required',                    
            'sales_activity_type'   => 'required',
            'sales_activities_note'   => 'required',
            'sales_activities_due_date'   => 'required|date',
         ]);
         $salesActivity->update([
          'sales_id' => $request['sales_id'],
          'user_id' => \Auth::user()->id,
          'sales_activities_name' => $request['sales_activities_name'],
          'sales_activities_note' => $request['sales_activities_note'],
          'sales_activities_summary_note' => $request['sales_activities_summary_note'],
          'sales_activities_due_date' => $request['sales_activities_due_date'],
          'sales_activity_type' =>  $request['sales_activity_type'],
          'sales_activities_time' =>  $request['sales_activities_time'],
          'sales_activities_status' => 1,
          'updated_by' => \Auth::user()->id,
        ]);
        $enquiryDetails = Sales::where('id','=',$request['sales_id'])->first();
        $stage = $request['stage'];
        session()->flash('success', 'Sales Activity Updated Successfully');
        return redirect()->route('leadAssign.nextStage',['id'=>$enquiryDetails->sales_enquiry_id,'stage'=>$stage]);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(SalesActivity $salesActivity)
    {
        $salesActivity->update([
          'sales_activities_status' => 3,
          'updated_by' => \Auth::user()->id,
        ]);
        $enquiryDetails = Sales::where('id','=',$salesActivity->sales_id)->first();
        $stage = $enquiryDetails->work_flow_processes_code;

        session()->flash('success', 'Sales Activity Closed Successfully');
        return redirect()->route('leadAssign.nextStage',['id'=>$enquiryDetails->sales_enquiry_id,'stage'=>$stage]);
    }
    /*
    *
    * modal sales activity
    *
    */
    public function salesActivity($id) {

        $sales_id = $id;
        $enquiry = Sales::where('id','=',$sales_id)->first();
        $enquiry_id = $enquiry->sales_enquiry_id;
        $stage = $enquiry->work_flow_processes_code;
        //dd($stage);
        return view('sales::TenantSales.tenant_add_activity_modal',compact('sales_id','enquiry_id','stage'));

    }
}
