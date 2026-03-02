<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\Bank;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class BankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->middleware('permission:add_bank', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_bank', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_bank', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_bank', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_bank', ['only' => ['index','show']]);           
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
        $banks = Bank::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate($noOfRecord);
                                       
        $fields = [
           'bank_code' => 'Code',                 
           'bank_name' => 'Name',                 
           'bank_branch' => 'Branch',  
		   'bank_branch' => 'Branch',  		   
        ];
        $request->flash();    
                                       
        return view('masters::Bank.list',compact('banks','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        
        return view('masters::Bank.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'bank_code'    => 'required|unique:bank',                    
            'bank_name'   => 'required',
            'bank_branch'   => 'required'
         ]);
        $bank =  Bank::create([
          'bank_code' => $request['bank_code'],
          'bank_name' => $request['bank_name'],
          'bank_branch' => $request['bank_branch'],
		  'bank_chequebook_id' => $request['bank_chequebook_id'],
          'dim1' => 'DIVISION',
		  'dim1Value' => $request['dim1Value'],
          'dim2' => 'BUILDING',
		  'bank_remark' => $request['bank_remark'],
		  'accounts_bank' => $request['accounts_bank'],
          'created_by' => \Auth::user()->id,
        ]);
        
          // Log insertion
        activity('Add Bank')
          ->performedOn($bank)
          ->causedBy(\Auth::user()->id)
          ->withProperties($bank)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Bank Created Successfully');
        return redirect()->route('bank.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $bank = Bank::where('id',$id)->first();
        return view('masters::Bank.view',compact('bank'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request, Bank $bank)
    {
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'bank.index';
		
        return view('masters::Bank.add_edit',compact('bank','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Bank $bank)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
        $this->validate($request, [
            'bank_code'    => 'required|unique:bank,bank_code,'.$bank->id,                   
            'bank_name'   => 'required',
            'bank_branch'   => 'required'   
         ]);
         $bank->update([
          'bank_code' => $request['bank_code'],
          'bank_name' => $request['bank_name'],
          'bank_branch' => $request['bank_branch'],
		  'bank_chequebook_id' => $request['bank_chequebook_id'],
          'dim1Value' => $request['dim1Value'],
          'bank_remark' => $request['bank_remark'],
		  'accounts_bank' => $request['accounts_bank'],
          'updated_by' => \Auth::user()->id,
        ]);
        
         // Log insertion
        activity('Update Bank')
          ->performedOn($bank)
          ->causedBy(\Auth::user()->id)
          ->withProperties($bank)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', 'Bank Updated Successfully');
        return redirect()->route('bank.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Bank $bank)
    {
       try {
                        
            $bank->delete();

            session()->flash('success', 'Bank Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please Delete Related Records Before');
        }
		activity('Deleted Bank')
          ->performedOn($bank)
          ->causedBy(\Auth::user()->id)
          ->withProperties($bank)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        return redirect()->route('bank.index');
    }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,Bank $bank)
    {
        $status = bank::find($id);
        if($status['bank_status']==1) {
            $status->bank_status = 0;
        } else {
            $status->bank_status = 1;
        } 
        $status->save();   
         //Log   
        activity('Change Bank Status')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);  
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('bank.index');
    }
}
