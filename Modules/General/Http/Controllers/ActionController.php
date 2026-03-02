<?php

namespace Modules\General\Http\Controllers;

use Modules\General\Entities\Action;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class ActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:add_action', ['only' => ['create','store']]);   
        $this->middleware('permission:edit_action', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_action', ['only' => ['destroy']]);    
        $this->middleware('permission:action_list', ['only' => ['index','show']]); 
        $this->middleware('permission:change_status_action', ['only' => ['changeStatus']]); 
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        
        $actions = Action::get();
        return view('general::Action.list',compact('actions'));

    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('general::Action.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'action_name' => 'required',
            'action_key' => 'required|unique:actions'                    
        ]);
       
        $action =  Action::create([
          'action_name' => $request['action_name'],
          'action_key'  => $request['action_key'],
          'created_by' => \Auth::user()->id,
        ]);
        /****** Activity log *****/
          activity('Action Created')
            ->performedOn($action)
            ->causedBy(\Auth::user()->id)
            ->withProperties($action)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Action Created Successfully');
        return redirect()->route('action.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $actions = Action::where('id',$id)->first();
        return view('general::Action.view',compact('actions'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Action $action)
    {
        return view('general::Action.add_edit',compact('action'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, Action $action)
    {
         $this->validate($request, [
            'action_name' => 'required',
            'action_key' => 'required|unique:actions,action_key,'.$action->id                    
        ]);
        

        $action->update([
          'action_name' => $request['action_name'],
          'action_key'  => $request['action_key'],
          'updated_by' => \Auth::user()->id,
        ]);
        /****** Activity log *****/
          activity('Action Updated')
            ->performedOn($action)
            ->causedBy(\Auth::user()->id)
            ->withProperties($action)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Action Updated Successfully');
        return redirect()->route('action.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
   public function destroy(Action $action)
    {
        $action->delete();
        /****** Activity log *****/
          activity('Action Deleted')
            ->performedOn($action)
            ->causedBy(\Auth::user()->id)
            ->withProperties($action)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
            
        session()->flash('success', 'Action Deleted Successfully');
        return redirect()->route('action.index');
    }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id, Action $action)
    {
        $status = Action::find($id);
        if($status['action_status']==1) {
            $status->action_status = 0;
        } else {
            $status->action_status = 1;
        } 
        $status->save();
        /****** Activity log *****/
          activity('Action Status Changed')
            ->performedOn($status)
            ->causedBy(\Auth::user()->id)
            ->withProperties($status)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);   

        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('action.index');
    }
}
