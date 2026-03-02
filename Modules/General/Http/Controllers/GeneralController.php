<?php

namespace Modules\General\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\General\Entities\Action;
use Modules\General\Entities\ProcessActionLink;
use Modules\General\Entities\ProcessAssign;
use Modules\General\Entities\WorkFlowProcess;
use Modules\General\Entities\WorkFlow;

class GeneralController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('general::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('general::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('general::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('general::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
    /*
    *
    *
    * Get next process from action
    *
    */
    public function nextProcessFromAction($work_flow,$action_key) {

        $action_id = Action::where('action_key','=',$action_key)->first();
        $nextProcess = ProcessActionLink::where('work_flow_processes_code','=',$work_flow)
            ->where('actions_id','=',$action_id->id)->first();
        return  $nextProcess->next_processes_code; 
    }
    /*
    *
    *
    * Get roles & users based on process
    *
    */
    public function roleUsersFromProcess($process,$location_id = null ,$price_range_id = null,$tenant_status_id = null) {
        
        $results = ProcessAssign::with('assign','processAssignRoles')
                ->where('work_flow_processes_code','=',$process)
                ->when($location_id, function ($query, $location_id) {
                    return $query->where('location_id', $location_id);
                }) 
                ->when($price_range_id, function ($query, $price_range_id) {
                    return $query->where('price_range_id', $price_range_id);
                })
               ->when($tenant_status_id, function ($query, $tenant_status_id) {
                    return $query->where('tenant_status_id', $tenant_status_id);
                })
                ->first(); 

       if(!empty($results))
        return  $results;           



                            $results = ProcessAssign::with('assign','processAssignRoles')
                                    ->where('work_flow_processes_code','=',$process)
                                    ->when($location_id, function ($query, $location_id) {
                                        return $query->where('location_id', $location_id);
                                    })                                    
                                    ->first();

                            if(!empty($results))
                            return  $results;          


                           $results = ProcessAssign::with('assign','processAssignRoles')
                                    ->where('work_flow_processes_code','=',$process)
                                    ->when($price_range_id, function ($query, $price_range_id) {
                                        return $query->where('price_range_id', $price_range_id);
                                    })                                  
                                    ->first(); 

                            if(!empty($results))
                            return  $results; 

                            $results = ProcessAssign::with('assign','processAssignRoles')
                                       ->where('work_flow_processes_code','=',$process)
                                       ->when($tenant_status_id, function ($query, $tenant_status_id) {
                                            return $query->where('tenant_status_id', $tenant_status_id);
                                        })
                                        ->first(); 

                            if(!empty($results))
                            return  $results; 

                            $results = ProcessAssign::with('assign','processAssignRoles')
                                       ->where('work_flow_processes_code','=',$process)
                                       ->first(); 

                            if(!empty($results))
                            return  $results; 


                            return false;      

       
        
    }
    /*
    *
    *
    * Get Previous Order
    *
    */
    public function getPreviousProcessOrder($process) {

        $current_process = WorkFlowProcess::where('work_flow_processes_code','=',$process)
                        ->select('work_flows_id','process_assign_order')->first();
        //$assign_order = $current_process->process_assign_order - 1;

        $previous_order = WorkFlowProcess::where('work_flows_id','=',$current_process->work_flows_id)
                                         ->select('work_flow_processes_code')->first();  
         return $previous_order->work_flow_processes_code;
    }
    /*
    *
    *
    * Get Previous Order
    *
    */
    public function getPreviousOrder($process) {
       //dd($process);
        $current_process = WorkFlowProcess::where('work_flow_processes_code','=',$process)
                        ->select('work_flows_id','process_assign_order')->first();
        $assign_order = $current_process->process_assign_order - 1;
        if($assign_order > 0){
            $previous_order = WorkFlowProcess::where('work_flows_id','=',$current_process->work_flows_id)
                ->where('process_assign_order',$assign_order)
                ->select('work_flow_processes_code')->latest()->orderBy('id', 'desc')->first();
                $previousAssign = $this->roleUsersFromProcess($previous_order->work_flow_processes_code,$location_id=null,$pricerange_id=null,$tenant_status=0);
                 if($previousAssign) {
                    return $previous_order->work_flow_processes_code;
                }else{
                    return $this->getPreviousOrder($previous_order->work_flow_processes_code);
                }
            } else {
                return 0;
            }
         
       
        
    }
    
    
    /*
    *  WorkFlowProcess  - Default User /Role
    *
    */
    public function  workFlowProcess($process){

       $process =  WorkFlow::whereHas('workFlowProcess', function ($query) use($process) {
                            $query->where('work_flow_processes_code',$process);
                      })
                      ->first();


        return   $process;            



    }
     
     /*
    *  WorkFlowProcess Names
    *
    */
    public function  workFlowProcessNames($process){

       $process =  WorkFlowProcess::where('work_flow_processes_code',$process)->first();

       return   $process;            


     }




    
    



}
