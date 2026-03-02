<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\UnitType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class UnitTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');    
        $this->middleware('permission:add_unit_type', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_unit_type', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_unit_type', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_unit_type', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_unit_type', ['only' => ['index','show']]);  
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
        
        $unitTypes = UnitType::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%')
                                        ;
                                    })->orderBy('id','ASC')
                               ->sortable()->paginate($noOfRecord);
        $fields = [
           'unit_types_name' => 'Name', 
         ];
      
       $request->flash();

        
        return view('masters::UnitType.list',compact('unitTypes','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::UnitType.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'unit_types_name'    => 'required|unique:unit_types',                  
                   
         ]);
        $unit_type =  UnitType::create([
          'unit_types_name' => $request['unit_types_name'],
          'unit_types_desc' => $request['unit_types_desc'],
          'created_by' => \Auth::user()->id,
        ]);
        // Log
        activity('Add Unit Type')
          ->performedOn($unit_type)
          ->causedBy(\Auth::user()->id)
          ->withProperties($unit_type)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);


        session()->flash('success', 'Unit Type Created Successfully');
        return redirect()->route('unitType.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $unitType = UnitType::where('id',$id)->first();
        return view('masters::UnitType.view',compact('unitType'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request, UnitType $unitType)
    {
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'unitType.index';
		$previousUrl = route($backUrlBreadCrumb, $backIdBreadCrumb);
        return view('masters::UnitType.add_edit',compact('unitType','backIdBreadCrumb','backUrlBreadCrumb','previousUrl'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,UnitType $unitType)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
        $this->validate($request, [
            'unit_types_name'    => 'required|unique:unit_types,unit_types_name,'.$unitType->id,                  
                   
         ]);
         $unitType->update([
          'unit_types_name' => $request['unit_types_name'],
          'unit_types_desc' => $request['unit_types_desc'],
          'updated_by' => \Auth::user()->id,
        ]);
        
        // Log
         activity('Update Unit Type')
          ->performedOn($unitType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($unitType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Unit Type Updated Successfully');
        
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(UnitType $unitType)
    {
        try {
                                
            $unitType->delete();

            session()->flash('success', 'Unit Type Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
        // Log
        activity('Deleted Unit Type')
          ->performedOn($unitType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($unitType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        return redirect()->route('unitType.index');
    }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,UnitType $unitType)
    {
        $status = UnitType::find($id);
        if($status['unit_types_status']==1) {
            $status->unit_types_status = 0;
        } else {
            $status->unit_types_status = 1;
        } 
        $status->save();   
        
        activity('Change Unit Type Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);  
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('unitType.index');
    }
}
