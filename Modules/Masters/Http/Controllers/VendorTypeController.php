<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\VendorType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class VendorTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:add_vendor_type', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_vendor_type', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_vendor_type', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_vendor_type', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_vendor_type', ['only' => ['index','show']]);              
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue;
        
        
        $vendorTypes = VendorType::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate(10);
                                       
        $fields = [
           'vendor_types_name' => 'Name',  
        ];
        $request->flash();                                          
                                       
        return view('masters::VendorType.list',compact('vendorTypes','fields'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::VendorType.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'vendor_types_name'    => 'required|unique:vendor_types',                    
         ]);
        $vendorType =  VendorType::create([
          'vendor_types_name' => $request['vendor_types_name'],
          'created_by' => \Auth::user()->id,
        ]);
         // log
        activity('Add Vendor Type')
          ->performedOn($vendorType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($vendorType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Vendor Type Created Successfully');
        return redirect()->route('vendorType.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $vendorType = VendorType::where('id',$id)->first();
        return view('masters::VendorType.view',compact('vendorType'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,VendorType $vendorType)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'vendorType.index';
        return view('masters::VendorType.add_edit',compact('vendorType','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,VendorType $vendorType)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'vendor_types_name'    => 'required|unique:vendor_types,vendor_types_name,'.$vendorType->id, 
         ]);
         $vendorType->update([
          'vendor_types_name' => $request['vendor_types_name'],
          'updated_by' => \Auth::user()->id,
        ]);
        // Log
        activity('Update Vendor Type')
          ->performedOn($vendorType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($vendorType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Vendor Type Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(VendorType $vendorType)
    {
        try {
                                
            $vendorType->delete();

            session()->flash('success', 'Vendor Type Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }
        // Log
        activity('Deleted Vendor Type')
          ->performedOn($vendorType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($vendorType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        
        return redirect()->route('vendorType.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,VendorType $vendorType)
    {
        $status = vendorType::find($id);
        if($status['vendor_types_status']==1) {
            $status->vendor_types_status = 0;
        } else {
            $status->vendor_types_status = 1;
        } 
        $status->save();     
        //Log
         activity('Change Vendor Type Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('vendorType.index');
    }
}
