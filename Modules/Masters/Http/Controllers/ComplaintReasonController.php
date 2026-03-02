<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\ComplaintReason;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class ComplaintReasonController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');         
        
        $this->middleware('permission:add_complaint_reason', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_complaint_reason', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_complaint_reason', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_complaint_reason', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_complaint_reason', ['only' => ['index','show']]);    
        
    }
    
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue;

        $complaints = ComplaintReason::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%')
                                        ;
                                       })->sortable()->paginate(10);
         $fields = [
           'complaint_reason_name' => 'Complaint Reason Name',                 
        ];                               
            
             $request->flash();                           
        return view('masters::ComplaintReason.list',compact('complaints','fields'));
        
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::ComplaintReason.add_edit');
        
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'complaint_reason_name' => 'required|unique:complaint_reason'                   
        ]);

        $complaintReason =  ComplaintReason::create([
          'complaint_reason_name' => $request['complaint_reason_name'],
          'complaint_reason_desc' => $request['complaint_reason_desc'],
          'created_by' => \Auth::user()->id,
        ]);
        
        
        //Log
        activity('Add Complaint Reason')
          ->performedOn($complaintReason)
          ->causedBy(\Auth::user()->id)
          ->withProperties($complaintReason)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Complaint reason Created Successfully');
        return redirect()->route('complaintReason.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $complaint = ComplaintReason::where('id',$id)->first();
        return view('masters::ComplaintReason.view',compact('complaint'));
      
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request, ComplaintReason $complaintReason)
    {
        $previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'complaintReason.index';
		
        return view('masters::ComplaintReason.add_edit',compact('complaintReason','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
       
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,ComplaintReason $complaintReason)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
        $this->validate($request, [
            'complaint_reason_name'    => 'required|unique:complaint_reason,complaint_reason_name,'.$complaintReason->id                     
              
         ]);

         $complaintReason->update([
          'complaint_reason_name' => $request['complaint_reason_name'],
          'complaint_reason_desc' => $request['complaint_reason_desc'],
          'updated_by' => \Auth::user()->id,
        ]);
        
        activity('Update Complaint Reason')
          ->performedOn($complaintReason)
          ->causedBy(\Auth::user()->id)
          ->withProperties($complaintReason)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Complaint Reason Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(ComplaintReason $complaintReason)
    {

        try {
                                
            $complaintReason->delete();
            session()->flash('success', 'Complaint Reason Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        } 

        // Log
         activity('Deleted Complaint Reason')
          ->performedOn($complaintReason)
          ->causedBy(\Auth::user()->id)
          ->withProperties($complaintReason)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        return redirect()->route('complaintReason.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,ComplaintReason $complaintReason)
    {
        $status = ComplaintReason::find($id);
        if($status['complaint_reason_status']==1) {
            $status->complaint_reason_status = 0;
        } else {
            $status->complaint_reason_status = 1;
        } 
        $status->save();     
        
        //Log
        activity('Change Complaint Reason')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('complaintReason.index');
    }
}
