<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\PriceRange;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class PriceRangeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  
        $this->middleware('permission:add_price_range', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_price_range', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_price_range', ['only' => ['destroy']]);   
        $this->middleware('permission:change_status_price_range', ['only' => ['changeStatus']]);         
        $this->middleware('permission:view_price_range', ['only' => ['index','show']]);                  
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
        $priceRanges = PriceRange::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate($noOfRecord);
                                       
        $fields = [
           'price_ranges_name' => 'Name',  
           'price_ranges_from' => 'From',  
           'price_ranges_to' => 'To',  
        ];
        $request->flash();  
        
                                               
        return view('masters::PriceRange.list',compact('priceRanges','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::PriceRange.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
           
            'price_ranges_from'    => 'required',
            'price_ranges_to'    => 'required',                   
         ]);
        $price_ranges_name = $request['price_ranges_from'].'-'.$request['price_ranges_to'];
       // dd($price_ranges_name);
        $priceRange =  PriceRange::create([
          'price_ranges_name' => $price_ranges_name,
          'price_ranges_from' => $request['price_ranges_from'],
          'price_ranges_to' => $request['price_ranges_to'],
          'created_by' => \Auth::user()->id,
        ]);
        //Log
        activity('Add Price Range')
          ->performedOn($priceRange)
          ->causedBy(\Auth::user()->id)
          ->withProperties($priceRange)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Price Range Created Successfully');
        return redirect()->route('priceRange.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $priceRange = PriceRange::where('id',$id)->first();
        return view('masters::PriceRange.view',compact('priceRange'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,PriceRange $priceRange)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'priceRange.index';
        return view('masters::PriceRange.add_edit',compact('priceRange','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,PriceRange $priceRange)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
           
            'price_ranges_from'    => 'required',
            'price_ranges_to'    => 'required',                   
         ]);
        $price_ranges_name = $request['price_ranges_from'].'-'.$request['price_ranges_to'];
         $priceRange->update([
          'price_ranges_name' => $price_ranges_name,
          'price_ranges_from' => $request['price_ranges_from'],
          'price_ranges_to' => $request['price_ranges_to'],
          'updated_by' => \Auth::user()->id,
        ]);
        
        //Log
        activity('Update Price Range')
          ->performedOn($priceRange)
          ->causedBy(\Auth::user()->id)
          ->withProperties($priceRange)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Price Range Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(PriceRange $priceRange)
    {
        try {
                                
            $priceRange->delete();

            session()->flash('success', 'Price Range Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
        //Log
        activity('Deleted Price Range')
          ->performedOn($priceRange)
          ->causedBy(\Auth::user()->id)
          ->withProperties($priceRange)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        
        return redirect()->route('priceRange.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,PriceRange $priceRange)
    {
        $status = priceRange::find($id);
        if($status['price_ranges_status']==1) {
            $status->price_ranges_status = 0;
        } else {
            $status->price_ranges_status = 1;
        } 
        $status->save();   
        
        //Log
        activity('Change Price Range Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
 
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('priceRange.index');
    }
}
