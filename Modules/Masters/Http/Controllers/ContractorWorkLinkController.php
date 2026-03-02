<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\ContractorWorkLink;
use Modules\Masters\Entities\Vendor;
use Modules\Masters\Entities\Work;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class ContractorWorkLinkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');       
        $this->middleware('permission:add_worklink', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_worklink', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_worklink', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_worklink', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_worklink', ['only' => ['index','show']]);       
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue; 
        
        $workLinks = ContractorWorkLink::when($fieldValue, function ($query) use($fieldValue,$fieldName){

            if($fieldName == 'vendor_name'){
                $query->whereHas('vendor', function ($query) use($fieldValue,$fieldName) {
                $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                  });
            }elseif($fieldName == 'works_code'){
                $query->whereHas('work', function ($query) use($fieldValue,$fieldName) {
                $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                  });
            }
            return $query;
           })->sortable()->paginate(20);
                                       
        $fields = [
           'vendor_name' => 'Vendor',                 
           'works_code' => 'Work',                 
        ];
        $request->flash();                               
      
        return view('masters::ContractorWorkLink.list',compact('workLinks','fields'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $vendors = Vendor::get();
        $works = Work::get();
        return view('masters::ContractorWorkLink.add_edit',compact('vendors','works'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'vendor_id'    => 'required',                    
            'work_id'   => 'required'
         ]);
        $workLink =  ContractorWorkLink::create([
          'vendor_id' => $request['vendor_id'],
          'work_id' => $request['work_id'],
          'created_by' => \Auth::user()->id,
        ]);
        
        activity('Add Contractor Work Links')
          ->performedOn($workLink)
          ->causedBy(\Auth::user()->id)
          ->withProperties($workLink)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Contractor Work Link Created Successfully');
        return redirect()->route('workLink.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $workLink = ContractorWorkLink::where('id',$id)->first();
        return view('masters::ContractorWorkLink.view',compact('workLink'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request, ContractorWorkLink $workLink)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'workLink.index';
		
        $vendors = Vendor::get();
        $works = Work::get();
        return view('masters::ContractorWorkLink.add_edit',compact('workLink','vendors','works','previousUrl','backUrlBreadCrumb','backIdBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,ContractorWorkLink $workLink)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
        $this->validate($request, [
            'vendor_id'    => 'required',                    
            'work_id'   => 'required'    
         ]);
         $workLink->update([
          'vendor_id' => $request['vendor_id'],
          'work_id' => $request['work_id'],
          'updated_by' => \Auth::user()->id,
        ]);
        
        activity('Update Contractor Work Links')
          ->performedOn($workLink)
          ->causedBy(\Auth::user()->id)
          ->withProperties($workLink)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'ContractorWorkLink Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(ContractorWorkLink $workLink)
    {
        try {
                                
            $workLink->delete();

            session()->flash('success', 'Contractor Work Link Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        } 

		activity('Deleted Contractor Work Links')
          ->performedOn($workLink)
          ->causedBy(\Auth::user()->id)
          ->withProperties($workLink)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        return redirect()->route('workLink.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,ContractorWorkLink $workLink)
    {
        $status = ContractorWorkLink::find($id);
        if($status['contractor_work_status']==1) {
            $status->contractor_work_status = 0;
        } else {
            $status->contractor_work_status = 1;
        } 
        $status->save();  
        activity('Change Contractor Work Links')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);   
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('workLink.index');
    }
}
