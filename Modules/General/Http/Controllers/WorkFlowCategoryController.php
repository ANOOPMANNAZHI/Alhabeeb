<?php

namespace Modules\General\Http\Controllers;

use Modules\General\Entities\WorkFlowCategory;
use Modules\Masters\Entities\PriceRange;
use Modules\Masters\Entities\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class WorkFlowCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $lists = WorkFlowCategory:: sortable()-> paginate(10);
        return view('general::WorkFlowCategory.list',compact('lists'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $prices = PriceRange::where('price_ranges_status','=',1)->get();
        $locations = Location::where('locations_status','=',1)->get();
        return view('general::WorkFlowCategory.add_edit',compact('prices','locations'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'assign_field_name'    => 'required|unique:work_flow_categories',                    
            'price_range_id'   => 'required',
            'location_id'   => 'required'
         ]);
        $bank =  WorkFlowCategory::create([
          'assign_field_name' => $request['assign_field_name'],
          'price_range_id' => $request['price_range_id'],
          'location_id' => $request['location_id'],
          'created_by' => \Auth::user()->id,
        ]);
        session()->flash('success', 'Work Flow Category Created Successfully');
        return redirect()->route('workFlowCategory.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $workFlowCategory = WorkFlowCategory::where('id',$id)->first();
        return view('general::WorkFlowCategory.view',compact('workFlowCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(WorkFlowCategory $workFlowCategory)
    {
        $prices = PriceRange::where('price_ranges_status','=',1)->get();
        $locations = Location::where('locations_status','=',1)->get();
        return view('general::WorkFlowCategory.add_edit',compact('prices','locations','workFlowCategory'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,WorkFlowCategory $workFlowCategory)
    {
        $this->validate($request, [

            'assign_field_name' => 'required|unique:work_flow_categories,assign_field_name,'.$workFlowCategory->id,                    
            'price_range_id'   => 'required',
            'location_id'   => 'required' 
         ]);
         $workFlowCategory->update([
          'assign_field_name' => $request['assign_field_name'],
          'price_range_id' => $request['price_range_id'],
          'location_id' => $request['location_id'],
          'updated_by' => \Auth::user()->id,
        ]);
        session()->flash('success', 'Work Flow Category Updated Successfully');
        return redirect()->route('workFlowCategory.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(WorkFlowCategory $workFlowCategory)
    {
       try {
                        
            $workFlowCategory->delete();

            session()->flash('success', 'Work Flow Category Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }

        return redirect()->route('workFlowCategory.index');
    }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,WorkFlowCategory $workFlowCategory)
    {
        $status = workFlowCategory::find($id);
        if($status['assign_field_status']==1) {
            $status->assign_field_status = 0;
        } else {
            $status->assign_field_status = 1;
        } 
        $status->save();     
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('workFlowCategory.index');
    }
}
