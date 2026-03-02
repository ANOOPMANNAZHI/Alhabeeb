<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\Reason;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class ReasonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');    
        $this->middleware('permission:add_reason', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_reason', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_reason', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_reason', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_reason', ['only' => ['index','show']]);      
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue;      
        
        
        $reasons = Reason::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%')
                                        ;
                                       })
                          ->sortable()->paginate(10);
                          
         $fields = [
           'reasons_code' => 'Code',                 
        ];        
        $request->flash(); 
        
                          
        return view('masters::Reason.list',compact('reasons','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::Reason.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'reasons_code'    => 'required|unique:reasons',                    
         ]);
        $reason =  Reason::create([
          'reasons_code' => $request['reasons_code'],
          'reasons_desc' => $request['reasons_desc'],
          'created_by' => \Auth::user()->id,
        ]);
        
        //Log
         activity('Add Reason')
          ->performedOn($reason)
          ->causedBy(\Auth::user()->id)
          ->withProperties($reason)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Reason Created Successfully');
        return redirect()->route('reason.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $reason = Reason::where('id',$id)->first();
        return view('masters::Reason.view',compact('reason'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,Reason $reason)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'reason.index'; 
        return view('masters::Reason.add_edit',compact('reason','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Reason $reason)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'reasons_code'    => 'required|unique:reasons,reasons_code,'.$reason->id,                        
         ]);
         $reason->update([
          'reasons_code' => $request['reasons_code'],
          'reasons_desc' => $request['reasons_desc'],
          'updated_by' => \Auth::user()->id,
        ]);
        // Log
        activity('Update Reason')
          ->performedOn($reason)
          ->causedBy(\Auth::user()->id)
          ->withProperties($reason)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Reason Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Reason $reason)
    {
        try {
                                
            $reason->delete();

            session()->flash('success', 'Reason Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
		// Log
		activity('Deleted Reason')
          ->performedOn($reason)
          ->causedBy(\Auth::user()->id)
          ->withProperties($reason)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        return redirect()->route('reason.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,Reason $reason)
    {
        $status = reason::find($id);
        if($status['reasons_status']==1) {
            $status->reasons_status = 0;
        } else {
            $status->reasons_status = 1;
        } 
        $status->save();     
        // Log
        activity('Change Reason Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('reason.index');
    }
}
