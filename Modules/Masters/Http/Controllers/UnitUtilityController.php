<?php

namespace Modules\Masters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\AuthController as Controller;
use Modules\Masters\Entities\UnitUtility;
use Modules\Masters\Entities\HomeUtility;
use Modules\Masters\Entities\Unit;
use Carbon\Carbon;
use Session;
use URL;
use Route;

class UnitUtilityController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');  
        $this->middleware('permission:add_unit_utility', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_unit_utility', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_unit_utility', ['only' => ['destroy']]);                
        $this->middleware('permission:view_unit_utility', ['only' => ['index','show']]);                  
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {

        $fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue; 
        
        $unit_utilities = UnitUtility::when($fieldValue, function ($query) use($fieldValue,$fieldName){

            if($fieldName == 'home_utilities_code'){
                $query->whereHas('homeUtility', function ($query) use($fieldValue,$fieldName) {
                $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                  });
            }elseif($fieldName == 'unit_code'){
                $query->whereHas('unit', function ($query) use($fieldValue,$fieldName) {
                $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                  });
            }else 
             $query->where($fieldName,'ilike', '%'.$fieldValue.'%');

            return $query;
            
            })->sortable()->paginate(15);
                                       
         $fields = [
           'amc_contract_no' => 'AMC Contract No',  
           'home_utilities_code' => 'Home Utilities',  
           'unit_code' => 'Unit',  
                 
        ];
        $request->flash();                                  
        return view('masters::UnitUtilities.list',compact('unit_utilities','fields'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {      
		$previousUrl = route('unit-utility.index');
		
        $homeUtilities =  HomeUtility::active()->get();
        $units = Unit::active()->get();

        return view('masters::UnitUtilities.add_edit',compact('homeUtilities','units','previousUrl'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
       if(isset($request->backurl))
			$url = $request->backurl;
			
       $this->validate($request, [
          'home_utility_id' => 'required',           
          'unit_id' => 'required',           
          'amc_contract_no' => 'required',      
       ]);   


       $unitUtilities = UnitUtility::create([
            'home_utility_id' => $request->home_utility_id,
            'unit_id' => $request->unit_id,
            'unit_utilities_remark' => $request->unit_utilities_remark,
            'amc_contract_no' => $request->amc_contract_no, 
            'created_by' =>  \Auth::user()->id,           
        ]);

	  $unitUtilities = activity('Add Unit Utility')
          ->performedOn($unitUtilities)
          ->causedBy(\Auth::user()->id)
          ->withProperties($unitUtilities)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
      session()->flash('success', ' Unit Utility Added ');
      return redirect($url);


    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show(UnitUtility $unit_utility)
    {
       return view('masters::UnitUtilities.view',compact('unit_utility'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,UnitUtility $unit_utility)
    {
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'unit-utility.index';
		$previousUrl = route($backUrlBreadCrumb, $backIdBreadCrumb);
        $homeUtilities =  HomeUtility::active()->get();
        $units = Unit::active()->get();

        return view('masters::UnitUtilities.add_edit',compact('homeUtilities','units','unit_utility','previousUrl','backIdBreadCrumb', 'backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,UnitUtility $unit_utility)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
			
        $this->validate($request, [
          'home_utility_id' => 'required',           
          'unit_id' => 'required',           
          'amc_contract_no' => 'required',      
       ]);   

        $unit_utility->update([
            'home_utility_id' => $request->home_utility_id,
            'unit_id' => $request->unit_id,
            'amc_contract_no' => $request->amc_contract_no,   
            'unit_utilities_remark' => $request->unit_utilities_remark,
            'updated_by' =>  \Auth::user()->id,               
        ]);

		activity('Update Unit Utility')
          ->performedOn($unit_utility)
          ->causedBy(\Auth::user()->id)
          ->withProperties($unit_utility)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
      session()->flash('success', ' Unit Utility Updated ');
      return redirect($url); 
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
}
