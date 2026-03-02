<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\InvoiceType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class InvoiceTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:add_invoice_type', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_invoice_type', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_invoice_type', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_invoice_type', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_invoice_type', ['only' => ['index','show']]);                       
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue;
        
        $invoiceTypes = InvoiceType::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate(20);
                                       
        $fields = [
           'invoice_types_name' => 'Name',  
        ];
        $request->flash();                                
                                       
        return view('masters::InvoiceType.list',compact('invoiceTypes','fields'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::InvoiceType.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'invoice_types_name'    => 'required|unique:invoice_types',                    
         ]);
        $invoiceType =  InvoiceType::create([
          'invoice_types_name' => $request['invoice_types_name'],
          'invoice_types_desc' => $request['invoice_types_desc'],
          'created_by' => \Auth::user()->id,
        ]);
        // Log
        activity('Add Invoice Types')
          ->performedOn($invoiceType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($invoiceType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        session()->flash('success', 'Invoice Type Created Successfully');
        return redirect()->route('invoiceType.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $invoiceType = InvoiceType::where('id',$id)->first();
        return view('masters::InvoiceType.view',compact('invoiceType'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,InvoiceType $invoiceType)
    {
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'invoiceType.index';

		$previousUrl = route($backUrlBreadCrumb, $backIdBreadCrumb);
        return view('masters::InvoiceType.add_edit',compact('invoiceType','previousUrl','backUrlBreadCrumb','backIdBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,InvoiceType $invoiceType)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
            'invoice_types_name'    => 'required|unique:invoice_types,invoice_types_name,'.$invoiceType->id, 
         ]);
         $invoiceType->update([
          'invoice_types_name' => $request['invoice_types_name'],
          'invoice_types_desc' => $request['invoice_types_desc'],
          'updated_by' => \Auth::user()->id,
        ]);
        // Log
        activity('Update Invoice Types')
          ->performedOn($invoiceType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($invoiceType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Invoice Type Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(InvoiceType $invoiceType)
    {
        try {
                                
            $invoiceType->delete();

            session()->flash('success', 'Invoice Type Deleted Successfully');
        }   
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }
        // Log
		activity('Deleted Invoice Type')
          ->performedOn($invoiceType)
          ->causedBy(\Auth::user()->id)
          ->withProperties($invoiceType)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        return redirect()->route('invoiceType.index');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,InvoiceType $invoiceType)
    {
        $status = invoiceType::find($id);
        if($status['invoice_types_status']==1) {
            $status->invoice_types_status = 0;
        } else {
            $status->invoice_types_status = 1;
        } 
        $status->save();     
        //Log
        activity('Change Invoice Type Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('invoiceType.index');
    }
}
