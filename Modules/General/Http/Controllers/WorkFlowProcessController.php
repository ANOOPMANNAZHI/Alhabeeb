<?php

namespace Modules\General\Http\Controllers;

use Modules\General\Entities\WorkFlow;
use Modules\General\Entities\WorkFlowProcess;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class WorkFlowProcessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:add_workflow_process', ['only' => ['create','store']]);   
        $this->middleware('permission:edit_workflow_process', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_workflow_process', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_workflow_process', ['only' => ['changeStatus']]);  
        $this->middleware('permission:workflow_process_list', ['only' => ['index','show']]);  
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {

        $workFlowProcessList = WorkFlowProcess::sortable()->get(); 
        return view('general::WorkFlowProcess.list',compact('workFlowProcessList'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        // work-flow 
        $workFlowList = WorkFlow::where('work_flows_status','=', 1)->get();
        
        return view('general::WorkFlowProcess.add_edit',compact('workFlowList'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {

     
        $this->validate($request, [
            'work_flow_processes_code'   => 'required|unique:work_flow_processes',
            'work_flow_processes_name'   => 'required',
            'work_flows_id'              => 'required',
        ]);
     
        $workFlowProcess = WorkFlowProcess::where('work_flows_id',$request['work_flows_id'])->orderBy('id', 'DESC')->first();
     
        if(isset($workFlowProcess)){  
            $next_order = $workFlowProcess->process_assign_order + 1;
        }
        else{
            $next_order = 1;
        }

        
        $workFlow   =  WorkFlowProcess::create([
          'work_flow_processes_code' => $request['work_flow_processes_code'],
          'work_flow_processes_name' => $request['work_flow_processes_name'],
          'work_flows_id'            => $request['work_flows_id'],
          'process_assign_order'     => $next_order,
          'process_assign_level'     => $request['process_assign_level'],
          'created_by' => \Auth::user()->id,
        ]);
        /****** Activity log *****/
          activity('WorkFlowProcess Created')
            ->performedOn($workFlow)
            ->causedBy(\Auth::user()->id)
            ->withProperties($workFlow)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Work-flow Stage Created Successfully');
        return redirect()->route('workFlowProcess.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $workFlowProcess = WorkFlowProcess::where('id',$id)->first();
        return view('general::WorkFlowProcess.view',compact('workFlowProcess'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(WorkFlowProcess $workFlowProcess)
    {
         $workFlowList = WorkFlow::where('work_flows_status','=', 1)->get();
         return view('general::WorkFlowProcess.add_edit',compact(['workFlowProcess','workFlowList']));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,WorkFlowProcess $workFlowProcess)
    {

        $this->validate($request, [
            'work_flow_processes_code'   => 'required|unique:work_flow_processes,work_flow_processes_code,'.$workFlowProcess->id,
            'work_flow_processes_name'   => 'required',
            'work_flows_id'              => 'required',
        ]);

        $workFlowProcess->update([
          'work_flow_processes_code' => $request['work_flow_processes_code'],  
          'work_flow_processes_name' => $request['work_flow_processes_name'],
          'work_flows_id'            => $request['work_flows_id'],
          'process_assign_level'     => $request['process_assign_level'],
          'updated_by' => \Auth::user()->id,
        ]);
        /****** Activity log *****/
          activity('WorkFlowProcess Updated')
            ->performedOn($workFlowProcess)
            ->causedBy(\Auth::user()->id)
            ->withProperties($workFlowProcess)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Work-Flow Updated Successfully');
        return redirect()->route('workFlowProcess.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(WorkFlowProcess $workFlowProcess)
    {
        $workFlowProcess->delete();
        /****** Activity log *****/
          activity('WorkFlowProcess Deleted')
            ->performedOn($workFlowProcess)
            ->causedBy(\Auth::user()->id)
            ->withProperties($workFlowProcess)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Work-Flow Stage Deleted Successfully');
        return redirect()->route('workFlowProcess.index');
    }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id, WorkFlowProcess $workFlowProcess)
    {
        $status = WorkFlowProcess::find($id);
        if($status['work_flow_processes_status']==1) {
            $status->work_flow_processes_status = 0;
        } else {
            $status->work_flow_processes_status = 1;
        } 
        $status->save(); 
        /****** Activity log *****/
          activity('WorkFlowProcess Status Changed')
            ->performedOn($workFlowProcess)
            ->causedBy(\Auth::user()->id)
            ->withProperties($workFlowProcess)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('workFlowProcess.index');
    }
}
