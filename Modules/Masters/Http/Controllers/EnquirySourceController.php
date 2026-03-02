<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\EnquirySource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class EnquirySourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');      
        $this->middleware('permission:add_enquiry_source', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_enquiry_source', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_enquiry_source', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_enquiry_source', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_enquiry_source', ['only' => ['index','show']]);     
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
        $enquirySources = EnquirySource::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate($noOfRecord);
                                       
         $fields = [
           'enquiry_sources_name' => 'Source',  
        ];
        $request->flash();     
                                               
        return view('masters::EnquirySource.list',compact('enquirySources','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::EnquirySource.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'enquiry_sources_name'    => 'required|unique:enquiry_sources',                    
         ]);
        $enquirySource =  EnquirySource::create([
          'enquiry_sources_name' => $request['enquiry_sources_name'],
          'created_by' => \Auth::user()->id,
        ]);
        // Log
        activity('Add Enquiry Sources')
          ->performedOn($enquirySource)
          ->causedBy(\Auth::user()->id)
          ->withProperties($enquirySource)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Enquiry Source Created Successfully');
        return redirect()->route('enquirySource.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $enquirySource = EnquirySource::where('id',$id)->first();
        return view('masters::EnquirySource.view',compact('enquirySource'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,EnquirySource $enquirySource)
    {
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'enquirySource.index';

		$previousUrl = route($backUrlBreadCrumb, $backIdBreadCrumb);
		
        return view('masters::EnquirySource.add_edit',compact('enquirySource','previousUrl','backUrlBreadCrumb','backIdBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,EnquirySource $enquirySource)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'enquiry_sources_name'    => 'required|unique:enquiry_sources,enquiry_sources_name,'.$enquirySource->id, 
         ]);
         $enquirySource->update([
          'enquiry_sources_name' => $request['enquiry_sources_name'],
          'updated_by' => \Auth::user()->id,
        ]);
        
        // Log
        activity('Update Enquiry Sources')
          ->performedOn($enquirySource)
          ->causedBy(\Auth::user()->id)
          ->withProperties($enquirySource)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Enquiry Source Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(EnquirySource $enquirySource)
    {
        try {
                                
           $enquirySource->delete();
           session()->flash('success', 'Enquiry Source Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
		
		 // Log
        activity('Deleted Enquiry Sources')
          ->performedOn($enquirySource)
          ->causedBy(\Auth::user()->id)
          ->withProperties($enquirySource)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        return redirect()->route('enquirySource.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,EnquirySource $enquirySource)
    {
        $status = enquirySource::find($id);
        if($status['enquiry_sources_status']==1) {
            $status->enquiry_sources_status = 0;
        } else {
            $status->enquiry_sources_status = 1;
        } 
        $status->save();     
        
        activity('Change Enquiry Sources')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('enquirySource.index');
    }
}
