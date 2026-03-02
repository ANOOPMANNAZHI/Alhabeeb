<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\TenantStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class TenantStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');         
        
        $this->middleware('permission:add_tenant_status', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_tenant_status', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_tenant_status', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_tenant_status', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_tenant_status', ['only' => ['index','show']]);    
        
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue;
        
        $tenantStatuses = TenantStatus::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%')
                                        ;
                                       })
                                       ->sortable()->paginate(10);
                                       
         $fields = [
           'tenant_statuses_name' => 'Tenant Status Name',                 
      ];     
       $request->flash();                          
        return view('masters::TenantStatus.list',compact('tenantStatuses','fields'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::TenantStatus.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tenant_statuses_name'    => 'required|unique:tenant_statuses',                    
         ]);
        $tenantStatus =  TenantStatus::create([
          'tenant_statuses_name' => $request['tenant_statuses_name'],
          'tenant_statuses_desc' => $request['tenant_statuses_desc'],
          'created_by' => \Auth::user()->id,
        ]);
        //Log
         activity('Add Tenant Statuses')
          ->performedOn($tenantStatus)
          ->causedBy(\Auth::user()->id)
          ->withProperties($tenantStatus)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Tenant Status Created Successfully');
        return redirect()->route('tenantStatus.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $tenantStatus = TenantStatus::where('id',$id)->first();
        return view('masters::TenantStatus.view',compact('tenantStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,TenantStatus $tenantStatus)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'building-amentity.index'; 
        return view('masters::TenantStatus.add_edit',compact('tenantStatus','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,TenantStatus $tenantStatus)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'tenant_statuses_name'    => 'required|unique:tenant_statuses,tenant_statuses_name,'.$tenantStatus->id,                         
         ]);
         $tenantStatus->update([
          'tenant_statuses_name' => $request['tenant_statuses_name'],
          'tenant_statuses_desc' => $request['tenant_statuses_desc'],
          'updated_by' => \Auth::user()->id,
        ]);
        // Log
        activity('Update Tenant Statuses')
          ->performedOn($tenantStatus)
          ->causedBy(\Auth::user()->id)
          ->withProperties($tenantStatus)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Tenant Status Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(TenantStatus $tenantStatus)
    {
        try {
                                
            $tenantStatus->delete();

            session()->flash('success', 'Tenant Status Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }
        // Log
        activity('Deleted Tenant Status')
          ->performedOn($tenantStatus)
          ->causedBy(\Auth::user()->id)
          ->withProperties($tenantStatus)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        return redirect()->route('tenantStatus.index');
    }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,TenantStatus $tenantStatus)
    {
        $status = tenantStatus::find($id);
        if($status['tenant_statuses_status']==1) {
            $status->tenant_statuses_status = 0;
        } else {
            $status->tenant_statuses_status = 1;
        } 
        $status->save();     
        // Log
        activity('Change Tenant Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('tenantStatus.index');
    }
}
