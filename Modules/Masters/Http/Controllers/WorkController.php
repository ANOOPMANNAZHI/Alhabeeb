<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\Work;
use Modules\Masters\Entities\Acc_codes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class WorkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');    
        $this->middleware('permission:add_work', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_work', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_work', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_work', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_work', ['only' => ['index','show']]);      
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
        $works = Work::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%')
                                        ;
                                       })->sortable()->paginate($noOfRecord);                                        
        $fields = [
           'works_code' => 'Code',                 
        ];        
        $request->flash(); 
                                         
        return view('masters::Work.list',compact('works','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $acc_codes = Acc_codes::get();
        return view('masters::Work.add_edit',compact('acc_codes'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'works_code'    => 'required|unique:works',                    
            'works_type'   => 'required'
         ]);
        $work =  Work::create([
          'works_code' => $request['works_code'],
          'works_desc' => $request['works_desc'],
          'works_type' => $request['works_type'],
          'acc_code_id'=> $request['acc_code'],
          'created_by' => \Auth::user()->id,
        ]);
        // Log
        activity('Add Work')
          ->performedOn($work)
          ->causedBy(\Auth::user()->id)
          ->withProperties($work)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Work Created Successfully');
        return redirect()->route('work.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
		
        $work = Work::join('acc_codes', 'acc_codes.id', '=', 'works.acc_code_id')->where('works.id',$id)->first();
        return view('masters::Work.view',compact('work'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,Work $work)
    {
		$previousUrl = url()->previous();
		
        $acc_codes = Acc_codes::get();

		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'work.index';
		
        return view('masters::Work.add_edit',compact('work','previousUrl','backIdBreadCrumb','backUrlBreadCrumb','acc_codes'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Work $work)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
        $this->validate($request, [
            'works_code'    => 'required|unique:works,works_code,'.$work->id,                   
            'works_type'   => 'required'     
         ]);
         $work->update([
          'works_code' => $request['works_code'],
          'works_desc' => $request['works_desc'],
          'works_type' => $request['works_type'],
          'acc_code_id'=> $request['acc_code'],
          'updated_by' => \Auth::user()->id,
        ]);
        // Log
        activity('Update Work')
          ->performedOn($work)
          ->causedBy(\Auth::user()->id)
          ->withProperties($work)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Work Updated Successfully');
        
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Work $work)
    {
        try {
                                
            $work->delete();

            session()->flash('success', 'Work Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
        // Log
        activity('Deleted Work')
          ->performedOn($work)
          ->causedBy(\Auth::user()->id)
          ->withProperties($work)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        return redirect()->route('work.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,Work $work)
    {
        $status = work::find($id);
        if($status['works_status']==1) {
            $status->works_status = 0;
        } else {
            $status->works_status = 1;
        } 
        $status->save();     
        // log
         activity('Change Bank Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('work.index');
    }
}
