<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\TenantType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class TenantTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:add_tenant_type', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_tenant_type', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_tenant_type', ['only' => ['destroy']]);   
        $this->middleware('permission:change_status_tenant_type', ['only' => ['changeStatus']]);              
        $this->middleware('permission:view_tenant_type', ['only' => ['index','show']]);               
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue; 
        
        $noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
        $tenantTypes = TenantType::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate($noOfRecord);
                                       
         $fields = [
           'tenant_types_name' => 'Tenant Type',  
                 
        ];
        $request->flash();   
                                               
        return view('masters::TenantType.list',compact('tenantTypes','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::TenantType.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tenant_types_name'    => 'required|unique:tenant_types',                    
         ]);
        $tenantType =  TenantType::create([
          'tenant_types_name' => $request['tenant_types_name'],
          'tenant_types_desc' => $request['tenant_types_desc'],
          'created_by' => \Auth::user()->id,
        ]);
        //Log
        activity('Add Tenant Type')
          ->performedOn($tenantType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($tenantType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Tenant Type Created Successfully');
        return redirect()->route('tenantType.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $tenantType = TenantType::where('id',$id)->first();
        return view('masters::TenantType.view',compact('tenantType'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,TenantType $tenantType)
    {
		
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'tenantType.index'; 
		
        return view('masters::TenantType.add_edit',compact('tenantType','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,TenantType $tenantType)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'tenant_types_name'    => 'required|unique:tenant_types,tenant_types_name,'.$tenantType->id,                         
         ]);
         $tenantType->update([
          'tenant_types_name' => $request['tenant_types_name'],
          'tenant_types_desc' => $request['tenant_types_desc'],
          'updated_by' => \Auth::user()->id,
        ]);
        //Log
         activity('Update Tenant Type')
          ->performedOn($tenantType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($tenantType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Tenant Type Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(TenantType $tenantType)
    {
        try {
                                
            $tenantType->delete();

            session()->flash('success', 'Tenant Type Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
        // Log
        activity('Deleted Tenant Type')
          ->performedOn($tenantType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($tenantType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        return redirect()->route('tenantType.index');
    }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,TenantType $tenantType)
    {
        $status = tenantType::find($id);
        if($status['tenant_types_status']==1) {
            $status->tenant_types_status = 0;
        } else {
            $status->tenant_types_status = 1;
        } 
        $status->save();     
        activity('Change Tenant Type Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('tenantType.index');
    }
}
