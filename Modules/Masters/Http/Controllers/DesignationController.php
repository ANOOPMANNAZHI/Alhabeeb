<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\Designation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class DesignationController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:add_designation', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_designation', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_designation', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_designation', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_designation', ['only' => ['index','show']]);           
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
         
        $designations = Designation::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate($noOfRecord);
                                       
        $fields = [
           'designation_code' => 'Code',                 
           'designation_name' => 'Name',
        ];
        $request->flash();    
                                               
        return view('masters::Designation.list',compact('designations','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::Designation.add_edit');
        
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'designation_code' => 'required|unique:designation',                    
        ]);

        $designation =  Designation::create([
          'designation_code' => $request['designation_code'],
          'designation_name' => $request['designation_name'],
          'created_by' => \Auth::user()->id,
        ]);
        
        activity('Add Designation')
          ->performedOn($designation)
          ->causedBy(\Auth::user()->id)
          ->withProperties($designation)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Designation Created Successfully');
        return redirect()->route('designation.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $designation = designation::where('id',$id)->first();
        return view('masters::Designation.view',compact('designation'));
      
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,Designation $designation)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'designation.index'; 
        
        return view('masters::Designation.add_edit',compact('designation','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
       
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Designation $designation)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'designation_code'    => 'required|unique:designation,designation_code,'.$designation->id      
        ]);
      
         $designation->update([
          'designation_code' => $request['designation_code'],
          'designation_name' => $request['designation_name'],
          'updated_by' => \Auth::user()->id
        ]);
        
         activity('Update Designation')
          ->performedOn($designation)
          ->causedBy(\Auth::user()->id)
          ->withProperties($designation)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Designation Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Designation $designation)
    {
        try {
                                
            $designation->delete();
            session()->flash('success', 'Designation deleted successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
        activity('Deleted Designation')
          ->performedOn($designation)
          ->causedBy(\Auth::user()->id)
          ->withProperties($designation)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        return redirect()->route('designation.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,Designation $designation)
    {
        $status = designation::find($id);
        if($status['designation_status']==1) {
            $status->designation_status = 0;
        } else {
            $status->designation_status = 1;
        } 
        $status->save();   
        // Log
        activity('Change Designation')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
  
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('designation.index');
    }
}
