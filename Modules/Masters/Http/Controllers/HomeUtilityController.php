<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\HomeUtility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class HomeUtilityController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');   
        $this->middleware('permission:add_home_utility', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_home_utility', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_home_utility', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_home_utility', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_home_utility', ['only' => ['index','show']]);        
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue; 
        
        $homeUtilities = HomeUtility::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate(20);
                                       
        $fields = [
           'home_utilities_code' => 'Code',                 
           'home_utilities_make' => 'Make',
        ];
        $request->flash();    
                             
                                                                    
        return view('masters::HomeUtility.list',compact('homeUtilities','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::HomeUtility.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'home_utilities_code'    => 'required|unique:home_utilities',                    
            'category' =>'required'
         ]);
        $homeUtility =  HomeUtility::create([
          'home_utilities_code' => $request['home_utilities_code'],
          'home_utilities_desc' => $request['home_utilities_desc'],
          'home_utilities_make' => $request['home_utilities_make'],
          'home_utilities_model' => $request['home_utilities_model'],
          'home_utilities_serial_no' => $request['home_utilities_serial_no'],
          'category' => $request['category'],
          'created_by' => \Auth::user()->id,
        ]);
        
         //Log
        activity('Add Home Utilities')
          ->performedOn($homeUtility)
          ->causedBy(\Auth::user()->id)
          ->withProperties($homeUtility)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);


        session()->flash('success', 'Home Utility Created Successfully');
        return redirect()->route('homeUtility.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
		
		
        $utility = HomeUtility::where('id',$id)->first();
        return view('masters::HomeUtility.view',compact('utility'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,HomeUtility $homeUtility)
    {
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'homeUtility.index';

		$previousUrl = route($backUrlBreadCrumb, $backIdBreadCrumb);
        return view('masters::HomeUtility.add_edit',compact('homeUtility','previousUrl','backIdBreadCrumb', 'backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,HomeUtility $homeUtility)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'home_utilities_code'    => 'required|unique:home_utilities,home_utilities_code,'.$homeUtility->id ,                    
            'category' =>'required'
         ]);
         $homeUtility->update([
          'home_utilities_code' => $request['home_utilities_code'],
          'home_utilities_desc' => $request['home_utilities_desc'],
          'home_utilities_make' => $request['home_utilities_make'],
          'home_utilities_model' => $request['home_utilities_model'],
          'home_utilities_serial_no' => $request['home_utilities_serial_no'],
          'category' => $request['category'],
          'updated_by' => \Auth::user()->id,
        ]);
        
         //Log
        activity('Update Home Utilities')
          ->performedOn($homeUtility)
          ->causedBy(\Auth::user()->id)
          ->withProperties($homeUtility)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Home Utility Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(HomeUtility $homeUtility)
    {
        try {
                                
           $homeUtility->delete();

            session()->flash('success', 'HomeUtility Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }
        // Log
        activity('Deleted Home Utilities')
          ->performedOn($homeUtility)
          ->causedBy(\Auth::user()->id)
          ->withProperties($homeUtility)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
       
        return redirect()->route('homeUtility.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,HomeUtility $homeUtility)
    {
        $status = homeUtility::find($id);
        if($status['home_utilities_status']==1) {
            $status->home_utilities_status = 0;
        } else {
            $status->home_utilities_status = 1;
        } 
        $status->save();     
         // Log
        activity('Change Home Utilities Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('homeUtility.index');
    }
}
