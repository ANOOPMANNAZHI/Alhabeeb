<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');   
        $this->middleware('permission:add_payment_method', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_payment_method', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_payment_method', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_payment_method', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_payment_method', ['only' => ['index','show']]);        
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
        
        $paymentMethods = PaymentMethod::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate($noOfRecord);
        
         $fields = [
           'payment_method_code' => 'Code',  
        ];
        $request->flash();                                
                                       
        return view('masters::PaymentMethod.list',compact('paymentMethods','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::PaymentMethod.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'payment_method_code'    => 'required|unique:payment_method',                    
         ]);
        $paymentMethod =  PaymentMethod::create([
          'payment_method_code' => $request['payment_method_code'],
          'payment_method_desc' => $request['payment_method_desc'],
          'created_by' => \Auth::user()->id,
        ]);
        //Log
        activity('Add Payment Method')
          ->performedOn($paymentMethod)
          ->causedBy(\Auth::user()->id)
          ->withProperties($paymentMethod)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Payment Method Created Successfully');
        return redirect()->route('paymentMethod.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $paymentMethod = PaymentMethod::where('id',$id)->first();
        return view('masters::PaymentMethod.view',compact('paymentMethod'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,PaymentMethod $paymentMethod)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'paymentMethod.index';
		
        return view('masters::PaymentMethod.add_edit',compact('paymentMethod','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,PaymentMethod $paymentMethod)
    {
		
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'payment_method_code'    => 'required|unique:payment_method,payment_method_code,'.$paymentMethod->id, 
         ]);
         $paymentMethod->update([
          'payment_method_code' => $request['payment_method_code'],
          'payment_method_desc' => $request['payment_method_desc'],
          'updated_by' => \Auth::user()->id,
        ]);
        
        activity('Update Payment Method')
          ->performedOn($paymentMethod)
          ->causedBy(\Auth::user()->id)
          ->withProperties($paymentMethod)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
          
        session()->flash('success', 'Payment Method Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        try {
                                
            $paymentMethod->delete();

            session()->flash('success', 'Payment Method Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
        
        //Log
        activity('Deleted Payment Method')
          ->performedOn($paymentMethod)
          ->causedBy(\Auth::user()->id)
          ->withProperties($paymentMethod)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        
        return redirect()->route('paymentMethod.index');
    }
    
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,PaymentMethod $paymentMethod)
    {
        $status = paymentMethod::find($id);
        if($status['payment_method_status']==1) {
            $status->payment_method_status = 0;
        } else {
            $status->payment_method_status = 1;
        } 
        $status->save();   
        //Log  
        activity('Change Payment Method Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('paymentMethod.index');
    }
}
