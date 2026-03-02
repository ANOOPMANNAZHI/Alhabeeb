<?php

namespace Modules\General\Http\Controllers;

use Modules\General\Entities\ProcessAssign;
use Modules\General\Entities\ProcessAssignUser;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\PriceRange;
use Modules\General\Entities\WorkFlow;
use Modules\General\Entities\WorkFlowProcess;
use Spatie\Permission\Models\Role;
use Modules\General\Http\Controllers\GeneralController as General ;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class ProcessAssignController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:add_workflow_assign', ['only' => ['create','store']]);   
        $this->middleware('permission:edit_workflow_assign', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_workflow_assign', ['only' => ['destroy']]);    
        $this->middleware('permission:view_workflow_assign', ['only' => ['index','show']]);  
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
       $location        = Location::orderBy('id', 'DESC')->get(); 
       $priceRange      = PriceRange::orderBy('id', 'DESC')->get(); 
       $workFlow        = WorkFlow::orderBy('id', 'DESC')->get(); 
       $workFlowProcess = WorkFlowProcess::orderBy('id', 'DESC')->get(); 
       $processAssigns  = ProcessAssign::sortable()->get(); 
       return view('general::ProcessAssign.list',compact(['location','priceRange','workFlow','workFlowProcess','processAssigns']));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
       $location        = Location::orderBy('id', 'DESC')->get(); 
       $priceRange      = PriceRange::orderBy('id', 'DESC')->get(); 
       $workFlow        = WorkFlow::orderBy('id', 'DESC')->get(); 
       $workFlowProcess = WorkFlowProcess::orderBy('id', 'DESC')->get(); 
       $roleList        = Role::get();


       return view('general::ProcessAssign.add_edit', compact(['location','priceRange','workFlow','workFlowProcess','roleList']));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'work_flows_name'    => 'required',     
            'work_flow_process_id'   => 'required'
            
         ]);

        $roleAssign = $request['row_count'];

        if(empty($roleAssign)){
            session()->flash('fail', 'Form error');
        }
           
        $processAssign =  ProcessAssign::create([
          'work_flow_processes_code' => $request['work_flow_process_id'],
          'location_id' => $request['location_id'],
          'price_range_id' => $request['price_range_id'],
          'assign_category' => $request['Category'],
          'tenant_status_id' => $request['status'],
          'process_assign_status' => 1,
          'created_by' => \Auth::user()->id,
        ]);
        

        for($i=1; $i<=$roleAssign; $i++) {
            
            $roleId    = $request['role_id'.$i];
          
            if(isset($request['user_id'.$i])){
                foreach($request['user_id'.$i] as $data){

                    $processAssign->processAssignRes()->attach($roleId,['user_id' => $data]);
                    $processAssign->save();

                }
            }
            else{

                $processAssign->processAssignRes()->attach($roleId);
                $processAssign->save();
            }
          
            
        } 
        /****** Activity log *****/
          activity('Process Assign Created')
            ->performedOn($processAssign)
            ->causedBy(\Auth::user()->id)
            ->withProperties($processAssign)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Process assign successfully created');
        return redirect()->route('processAssign.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
 
       $processAssigns  = ProcessAssign::where('id',$id)->first(); 
       
       return view('general::ProcessAssign.view',compact(['location','priceRange','workFlow','workFlowProcess','processAssigns']));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(ProcessAssign $processAssign)
    {
        $location        = Location::orderBy('id', 'DESC')->get(); 
        $priceRange      = PriceRange::orderBy('id', 'DESC')->get(); 
        $workFlow        = WorkFlow::orderBy('id', 'DESC')->get(); 
        $general =  new General;
        $WorkFlow = $general->workFlowProcess($processAssign->work_flow_processes_code);
        
        $workFlowProcess = WorkFlowProcess::orderBy('id', 'DESC')->where('work_flows_id',$WorkFlow->id)->get(); 
        $roleList        = Role::get();


        return view('general::ProcessAssign.add_edit', compact(['location','priceRange','workFlow','workFlowProcess','processAssign','roleList']));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, ProcessAssign $processAssign)
    {

        $this->validate($request, [
            'work_flows_name'    => 'required',     
            'work_flow_process_id'   => 'required'
            
        ]);
        $roleAssign = $request['row_count'];

        if(empty($roleAssign)){
            session()->flash('fail', 'Form error');
        }
           
        $processAssign->update([
          'work_flow_processes_code' => $request['work_flow_process_id'],
          'location_id' => $request['location_id'],
          'price_range_id' => $request['price_range_id'],
          'assign_category' => $request['Category'],
          'tenant_status_id' => $request['status'],
          'process_assign_status' => 1,
          'created_by' => \Auth::user()->id,
        ]);        
    
        $processAssign->processAssignRes()->detach();

        for($i=1; $i<=$roleAssign; $i++) {
            
            $roleId    = $request['role_id'.$i];
          
            if(isset($request['user_id'.$i])){
                foreach($request['user_id'.$i] as $data){

                    $processAssign->processAssignRes()->attach($roleId,['user_id' => $data]);
                    $processAssign->save();

                }
            }
            else{

               $processAssign->processAssignRes()->attach($roleId);
                    $processAssign->save();
            }
          
            
        } 
        /****** Activity log *****/
          activity('Process Assign Updated')
            ->performedOn($processAssign)
            ->causedBy(\Auth::user()->id)
            ->withProperties($processAssign)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Process Assign Updated Successfully');
        return redirect()->route('processAssign.index');

    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(ProcessAssign $processAssign)
    {
       /* $id = ProcessAssignUser::find($processAssign->process_assigns_id);
        $id->delete();
        $id = ProcessAssign::find($processAssign->id);
        $id->delete();*/
        try {
                                    
            $processAssign->assign->delete();
            $processAssign->delete();

            session()->flash('success', 'Work Flow Category Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }
        /****** Activity log *****/
          activity('Process Assign Deleted')
            ->performedOn($processAssign)
            ->causedBy(\Auth::user()->id)
            ->withProperties($processAssign)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        //session()->flash('success', 'Process Assign Deleted Successfully');
        return redirect()->route('processAssign.index');

    
    }

    public function ajaxStages(Request $request){

       $id = $request->input('id'); 

       $list = WorkFlowProcess::select('id','work_flow_processes_name','work_flow_processes_code')->where('work_flows_id',$id)->orderBy('id', 'DESC')->get();
  
       return json_encode($list);

    }  


    public function usersByRoleId(Request $request){

       $id = $request->input('id'); 
       $usersList = \App\User::active()->role($id)
                               ->where('user_type','employee')
                               ->whereHas('employee', function ($query){
                                $query->active();                      
                                })                             
                               ->get();      


       $usersList->each(function ($item, $key) {
          $item->username = $item->employee->employee_name ;
      });

      return json_encode($usersList);
    }
    /*
    *
    *
    * Stages
    *
    */
    public function processStages($id){

       

       $list = WorkFlowProcess::select('id','work_flow_processes_name','work_flow_processes_code')->where('work_flows_id',$id)->orderBy('id', 'DESC')->get();
  
       return $list;

    }




}
