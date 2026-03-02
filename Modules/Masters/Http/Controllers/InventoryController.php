<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:add_inventory', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_inventory', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_inventory', ['only' => ['destroy']]);          
        $this->middleware('permission:view_inventory', ['only' => ['index','show']]);            
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue;        
        
        $inventories = Inventory::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate(20);
                                       
        $fields = [
           'inventories_name' => 'Name',  
           'inventories_brand_name' => 'Brand Name',  
        ];
        $request->flash();    
                                               
        return view('masters::Inventory.list',compact('inventories','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::Inventory.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'inventories_name'    => 'required|unique:inventories',                    
         ]);
        $inventory =  Inventory::create([
          'inventories_name' => $request['inventories_name'],
          'inventories_desc' => $request['inventories_desc'],
          'inventories_brand_name' => $request['inventories_brand_name'],
          'created_by' => \Auth::user()->id,
        ]);
        
        //Log
        activity('Add Inventory')
          ->performedOn($inventory)
          ->causedBy(\Auth::user()->id)
          ->withProperties($inventory)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Inventory Created Successfully');
        return redirect()->route('inventory.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $inventory = Inventory::where('id',$id)->first();
        return view('masters::Inventory.view',compact('inventory'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,Inventory $inventory)
    {
		$previousUrl = url()->previous();
        $backIdBreadCrumb  = ($request->backid)?$request->backid:null;
        $backUrlBreadCrumb = ($request->backurl)?$request->backurl:'inventory.index';
		//dd($backUrlBreadCrumb);
		
        return view('masters::Inventory.add_edit',compact('inventory','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Inventory $inventory)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'inventories_name'    => 'required|unique:inventories,inventories_name,'.$inventory->id, 
         ]);
         $inventory->update([
          'inventories_name' => $request['inventories_name'],
          'inventories_desc' => $request['inventories_desc'],
          'inventories_brand_name' => $request['inventories_brand_name'],
          'updated_by' => \Auth::user()->id,
        ]);
        
         // Log
        activity('Update Inventory')
          ->performedOn($inventory)
          ->causedBy(\Auth::user()->id)
          ->withProperties($inventory)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Inventory Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Inventory $inventory)
    {
        try {
                                
            $inventory->delete();

            session()->flash('success', 'Inventory Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }
		// Log
        activity('Deleted Inventory')
          ->performedOn($inventory)
          ->causedBy(\Auth::user()->id)
          ->withProperties($inventory)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        return redirect()->route('inventory.index');
    }
}
