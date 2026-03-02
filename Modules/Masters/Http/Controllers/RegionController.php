<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\Region;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class RegionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:add_region', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_region', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_region', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_region', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_region', ['only' => ['index','show']]);    



    }

     public function index(Request $request)
    {
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue;
        
        $noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
        $locations = Region::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%')
                                        ;
                                       })->sortable()->paginate($noOfRecord);
                                       
        $fields = [
           'region_code' => 'Code',                 
           'region_name' => 'Name',                 
        ];
        $request->flash();                                        
        return view('masters::Region.list',compact('locations','fields','request'));
    }


    
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::Region.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'locations_code'    => 'required|unique:locations',                    
            'locations_name'   => 'required'
         ]);
        $location =  Location::create([
          'locations_code' => $request['locations_code'],
          'locations_name' => $request['locations_name'],
          'created_by' => \Auth::user()->id,
        ]);
        //Log
        activity('Add Location')
          ->performedOn($location)
          ->causedBy(\Auth::user()->id)
          ->withProperties($location)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Location Created Successfully');
        return redirect()->route('location.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $location = Location::where('id',$id)->first();
        return view('masters::Location.view',compact('location'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request, Location $location)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'location.index';
		
        return view('masters::Location.add_edit',compact('location', 'previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Location $location)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'locations_code'    => 'required|unique:locations,locations_code,'.$location->id,                    
            'locations_name'   => 'required'     
         ]);
         $location->update([
          'locations_code' => $request['locations_code'],
          'locations_name' => $request['locations_name'],
          'updated_by' => \Auth::user()->id,
        ]);
        //Log
        activity('Update Location')
          ->performedOn($location)
          ->causedBy(\Auth::user()->id)
          ->withProperties($location)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Location Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Request $request,Location $Location)
    {
        try{
		  $Location->delete();
		}
		catch (\Illuminate\Database\QueryException $e){
		  $request->session()->flash('error', 'Unable to delete location due to foreign key contraint.');
		  return redirect()->route('location.index');
		}
		// lOG
		activity('Deleted Location')
          ->performedOn($Location)
          ->causedBy(\Auth::user()->id)
          ->withProperties($Location)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Location Deleted Successfully');
        return redirect()->route('location.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,Location $location)
    {
        $status = location::find($id);
        if($status['locations_status']==1) {
            $status->locations_status = 0;
        } else {
            $status->locations_status = 1;
        } 
        $status->save();     
          //Log
        activity('Change Location Status
        ')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('location.index');
    }

    /**
    *
    * Location Autocomplete
    *
    **/
    public function locationAutocomplete(Request $request){

       $key = $request->search;

       $locations =   Location::where('locations_name', 'ILIKE', '%'.$key.'%')
							   ->where('enquiry_status','1')
                               ->select('locations_name AS text','id AS value')
                               ->get();

       return $locations ;


    }
    /**
    *
    * Location Autocomplete
    *
    **/
    public function locationsAutocomplete(Request $request){

       $key = $request->term;

       $locations =   Location::where('locations_name', 'ILIKE', '%'.$key.'%')
                               ->select('locations_name AS value','id AS ids')
                               ->get();

       return $locations ;


    }

}
