<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');        
        $this->middleware('permission:add_country', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_country', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_country', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_country', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_country', ['only' => ['index','show']]);  
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

        $countries = Country::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%')
                                        ;
                                       })->sortable()->paginate($noOfRecord);
        $fields = [
           'countries_code' => 'Code',                 
           'countries_name' => 'Name',                 
        ];
        $request->flash();                                 
                                       
        return view('masters::Country.list',compact('countries','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::Country.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'countries_code'    => 'required|unique:countries',                    
            'countries_name'   => 'required'
         ]);
        $country =  Country::create([
          'countries_code' => $request['countries_code'],
          'countries_name' => $request['countries_name'],
          'created_by' => \Auth::user()->id,
        ]);
         //Log
        activity('Add Country')
          ->performedOn($country)
          ->causedBy(\Auth::user()->id)
          ->withProperties($country)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Country Created Successfully');
        return redirect()->route('country.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $country = Country::where('id',$id)->first();
        return view('masters::Country.view',compact('country'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,Country $country)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'country.index';
		
        return view('masters::Country.add_edit',compact('country','backIdBreadCrumb','backUrlBreadCrumb','previousUrl'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Country $country)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'countries_code'    => 'required|unique:countries,countries_code,'.$country->id ,                   
            'countries_name'   => 'required'     
         ]);
         $country->update([
          'countries_code' => $request['countries_code'],
          'countries_name' => $request['countries_name'],
          'updated_by' => \Auth::user()->id,
        ]);
        //Log
        activity('Update Country')
          ->performedOn($country)
          ->causedBy(\Auth::user()->id)
          ->withProperties($country)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        
        session()->flash('success', 'Country Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Country $country)
    {
        try {
                                
            $country->delete();

            session()->flash('success', 'Country Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
		//Log
        activity('Deleted Country')
          ->performedOn($country)
          ->causedBy(\Auth::user()->id)
          ->withProperties($country)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        return redirect()->route('country.index');
    }
    
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,Country $country)
    {
        $status = country::find($id);
        if($status['countries_status']==1) {
            $status->countries_status = 0;
        } else {
            $status->countries_status = 1;
        } 
        $status->save();     
        
         //log
        activity('Change Country Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('country.index');
    }
}
