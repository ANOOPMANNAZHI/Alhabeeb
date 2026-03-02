<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\BuildingType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class BuildingTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:add_building_type', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_building_type', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_building_type', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_building_type', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_building_type', ['only' => ['index','show']]);          
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
        $buildingTypes = BuildingType::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%')
                                        ;
                                       })
                                       ->sortable()->paginate($noOfRecord);
        
          $fields = [
           'building_types_name' => 'Name',                 
      ];
      
       $request->flash();
      
      
        return view('masters::BuildingType.list',compact('buildingTypes','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::BuildingType.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'building_types_name'    => 'required|unique:building_types',                    
                   
         ]);
        $building_type =  BuildingType::create([
          'building_types_name' => $request['building_types_name'],
          'building_types_desc' => $request['building_types_desc'],
          'created_by' => \Auth::user()->id,
        ]);
        
        //  Log 
        activity('Add Building Type')
			->performedOn($building_type)
			->causedBy(\Auth::user()->id)
			->withProperties($building_type)
			->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Building Type Created Successfully');
        return redirect()->route('buildingType.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
		
        $buildingType = BuildingType::where('id',$id)->first();
		if(empty($buildingType))
			return redirect()->route('buildingType.index');
        return view('masters::BuildingType.view',compact('buildingType'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request, BuildingType $buildingType)
    {
        $previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'buildingType.index';
        return view('masters::BuildingType.add_edit',compact('buildingType','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,BuildingType $buildingType)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'building_types_name'    => 'required|unique:building_types,building_types_name,'.$buildingType->id                     
                   
         ]);
         $buildingType->update([
          'building_types_name' => $request['building_types_name'],
          'building_types_desc' => $request['building_types_desc'],
          'updated_by' => \Auth::user()->id,
        ]);
        
        activity('Update Building Type')
          ->performedOn($buildingType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($buildingType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Building Type Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(BuildingType $buildingType)
    {
        try {
                                
            $buildingType->delete();

            session()->flash('success', 'Building Type Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        } 
        activity('Deleted Building Type')
          ->performedOn($buildingType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($buildingType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        return redirect()->route('buildingType.index');
    }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,BuildingType $buildingType)
    {
        $status = BuildingType::find($id);
        if($status['building_types_status']==1) {
            $status->building_types_status = 0;
        } else {
            $status->building_types_status = 1;
        } 
        $status->save();   
        activity('Change Building Type')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
                      
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('buildingType.index');
    }
}
