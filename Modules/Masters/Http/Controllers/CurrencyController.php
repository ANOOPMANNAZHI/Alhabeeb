<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');     
        $this->middleware('permission:add_currency', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_currency', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_currency', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_currency', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_currency', ['only' => ['index','show']]);     
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
        
        $currencys = Currency::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%')
                                        ;
                                       })->sortable()->paginate($noOfRecord);                                       
        $fields = [
           'currency_code' => 'Code',                 
           'currency_name' => 'Name',                 
        ];
        $request->flash();
                                               
        return view('masters::Currency.list',compact('currencys','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::Currency.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'currency_code'    => 'required|unique:currency',                    
            'currency_name'   => 'required'
         ]);
        $currency =  Currency::create([
          'currency_code' => $request['currency_code'],
          'currency_name' => $request['currency_name'],
          'created_by' => \Auth::user()->id,
        ]);
        //Log
        activity('Add Currency')
          ->performedOn($currency)
          ->causedBy(\Auth::user()->id)
          ->withProperties($currency)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Currency Created Successfully');
        return redirect()->route('currency.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $currency = Currency::where('id',$id)->first();
        return view('masters::Currency.view',compact('currency'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,Currency $currency)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'currency.index';
		
        return view('masters::Currency.add_edit',compact('currency','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Currency $currency)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'currency_code'    => 'required|unique:currency,currency_code,'.$currency->id ,                    
            'currency_name'   => 'required'     
         ]);
         $currency->update([
          'currency_code' => $request['currency_code'],
          'currency_name' => $request['currency_name'],
          'updated_by' => \Auth::user()->id,
        ]);
        //Log
        activity('Update Currency')
          ->performedOn($currency)
          ->causedBy(\Auth::user()->id)
          ->withProperties($currency)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Currency Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Currency $currency)
    {
        try {
                                
            $currency->delete();

            session()->flash('success', 'Currency Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
		
		activity('Deleted Currency')
          ->performedOn($currency)
          ->causedBy(\Auth::user()->id)
          ->withProperties($currency)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        return redirect()->route('currency.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,Currency $currency)
    {
        $status = currency::find($id);
        if($status['currency_status']==1) {
            $status->currency_status = 0;
        } else {
            $status->currency_status = 1;
        } 
        $status->save(); 
        
        activity('Change Currency')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
 
     
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('currency.index');
    }
}
