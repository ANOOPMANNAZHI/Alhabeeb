<?php

namespace Modules\General\Http\Controllers;

use Modules\General\Entities\ProcessActionLink;
use Modules\General\Entities\WorkFlowProcess;
use Modules\General\Entities\Action;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;


class ProcessActionLinkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:add_process_action_link', ['only' => ['create','store']]);   
        $this->middleware('permission:edit_process_action_link', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_process_action_link', ['only' => ['destroy']]);    
        $this->middleware('permission:process_action_link_list', ['only' => ['index','show']]);  
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
     public function index()
    {
        $workFlowProcess   = WorkFlowProcess::get();
        $actions           = Action::get();
        $processActionLink = ProcessActionLink::sortable()->paginate(20);

        return view('general::ProcessActionLink.list',compact(['processActionLink','actions','workFlowProcess']));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        
        $workFlowProcess   = WorkFlowProcess::get();
        $actions     = Action::get();

        return view('general::ProcessActionLink.add_edit', compact(['workFlowProcess','actions']));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {

         $this->validate($request, [
            'work_flow_process_id'  => 'required',
            'actions_id'            => 'required',
            'next_process_id'       => 'required'
         ]);
         if (ProcessActionLink::where('work_flow_processes_code', $request['work_flow_process_id'])->where('actions_id', $request['actions_id'])->     where('next_processes_code' , $request['next_process_id'])->count() > 0) {

            session()->flash('error', 'Process Action Combination Already exist');
            return redirect()->route('processActionLink.create');
         }
         if ($request['actions_id'] == $request['next_process_id']) {

            session()->flash('error', 'Process Name and Next Process name cannot be same');
            return redirect()->route('processActionLink.create');
         }
        $processAction =  ProcessActionLink::create([
                'work_flow_processes_code'  => $request['work_flow_process_id'],
                'actions_id'                => $request['actions_id'],
                'next_processes_code'       => $request['next_process_id'],
                'created_by'                => \Auth::user()->id,
        ]);
        /****** Activity log *****/
          activity('Process Action Created')
            ->performedOn($processAction)
            ->causedBy(\Auth::user()->id)
            ->withProperties($processAction)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Process Action Created Successfully');
        return redirect()->route('processActionLink.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $processActionLink = ProcessActionLink::where('id',$id)->first();
        return view('general::ProcessActionLink.view',compact('processActionLink'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(ProcessActionLink $processActionLink)
    {
        $workFlowProcess   = WorkFlowProcess::get();
        $actions           = Action::get();

        return view('general::ProcessActionLink.add_edit',compact(['processActionLink','actions','workFlowProcess']));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,ProcessActionLink $processActionLink)
    {
        $this->validate($request, [
            'work_flow_process_id'  => 'required',                    
            'actions_id'            => 'required',
            'next_process_id'       => 'required'     
        ]);
        $processActionLink->update([
          'work_flow_processes_code'=> $request['work_flow_process_id'],
          'actions_id'              => $request['actions_id'],
          'next_processes_code'     => $request['next_process_id'],
          'updated_by'              => \Auth::user()->id,
        ]);
        /****** Activity log *****/
          activity('Process Action Updated')
            ->performedOn($processActionLink)
            ->causedBy(\Auth::user()->id)
            ->withProperties($processActionLink)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Process Action Successfully Updated');
        return redirect()->route('processActionLink.index');

    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(ProcessActionLink $processActionLink)
    {
        $processActionLink->delete();
        /****** Activity log *****/
          activity('Process Action Deleted')
            ->performedOn($processActionLink)
            ->causedBy(\Auth::user()->id)
            ->withProperties($processActionLink)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
            
        session()->flash('success', 'Process Action Successfully');
        return redirect()->route('processActionLink.index');
    }
}
