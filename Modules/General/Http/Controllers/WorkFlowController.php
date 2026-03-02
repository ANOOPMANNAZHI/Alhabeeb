<?php

namespace Modules\General\Http\Controllers;

use Modules\General\Entities\WorkFlow;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class WorkFlowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:add_workflow', ['only' => ['create','store']]);   
        $this->middleware('permission:edit_workflow', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_workflow', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_workflow', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_workflow', ['only' => ['index','show']]);  
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
      
        $workFlows = WorkFlow::sortable()->get(); ;

        return view('general::WorkFlow.list',compact('workFlows'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $roles    = Role::get();
        return view('general::WorkFlow.add_edit',compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'work_flows_name' => 'required|unique:work_flows' ,                  
            'role' => 'required'                   
        ]);

         if(empty($request['user_id']))
             $request['user_id'] = null; 
        

        $workFlow =  WorkFlow::create([
          'work_flows_name' => $request['work_flows_name'],
          'default_role' => $request['role'],
          'default_user_id' => $request['user_id'],
          'created_by' => \Auth::user()->id,
        ]);
        /****** Activity log *****/
          activity('WorkFlow Created')
            ->performedOn($workFlow)
            ->causedBy(\Auth::user()->id)
            ->withProperties($workFlow)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Work-flow Created Successfully');
        return redirect()->route('workFlow.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $workFlow = WorkFlow::where('id',$id)->first();
        return view('general::WorkFlow.view',compact('workFlow'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(WorkFlow $workFlow)
    {
        //dd($workFlow)
      $roles    = Role::get();
          
      $usersList = \App\User::role($workFlow->default_role)
                               ->where('user_type','employee')
                               ->has('employee')                             
                               ->get();

      $usersList->each(function ($item, $key) {
          $item->username = $item->employee->employee_name;
      });

     return view('general::WorkFlow.add_edit',compact('workFlow','roles','usersList'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,WorkFlow $workFlow)
    {
        $this->validate($request, [
            'work_flows_name' => 'required|unique:work_flows,work_flows_name,'.$workFlow->id ,
             'role' => 'required'                     
              
         ]);

        if(empty($request['user_id']))
             $request['user_id'] = null; 

         $workFlow->update([
          'work_flows_name' => $request['work_flows_name'],
          'default_role' => $request['role'],
          'default_user_id' => $request['user_id'],
          'updated_by' => \Auth::user()->id,
        ]);
         /****** Activity log *****/
          activity('WorkFlow Updated')
            ->performedOn($workFlow)
            ->causedBy(\Auth::user()->id)
            ->withProperties($workFlow)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Work-Flow Updated Successfully');
        return redirect()->route('workFlow.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(WorkFlow $workFlow)
    {
        $workFlow->delete();
        /****** Activity log *****/
          activity('WorkFlow Deleted')
            ->performedOn($workFlow)
            ->causedBy(\Auth::user()->id)
            ->withProperties($workFlow)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Work-Flow Deleted Successfully');
        return redirect()->route('workFlow.index');
    }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id, WorkFlow $workFlow)
    {
        $status = WorkFlow::find($id);
        if($status['work_flows_status']==1) {
            $status->work_flows_status = 0;
        } else {
            $status->work_flows_status = 1;
        } 
        $status->save();  
        /****** Activity log *****/
          activity('WorkFlow Status Changed')
            ->performedOn($workFlow)
            ->causedBy(\Auth::user()->id)
            ->withProperties($workFlow)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);  
             
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('workFlow.index');
    }
}
