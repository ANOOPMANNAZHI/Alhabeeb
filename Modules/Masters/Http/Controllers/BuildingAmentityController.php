<?php

namespace Modules\Masters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\AuthController as Controller;
use Carbon\Carbon;
use Session;
use URL;
use Route;
use Modules\Masters\Entities\BuildingAmentity;
use Modules\Masters\Entities\AmentityType;
use Modules\Masters\Entities\Building;

class BuildingAmentityController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');  
        $this->middleware('permission:add_building_amentity', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_building_amentity', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_building_amentity', ['only' => ['destroy']]);                
        $this->middleware('permission:view_building_amentity', ['only' => ['index','show']]);                  
    }
    
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		$fieldName  = $request->fieldName;
        $fieldValue = $request->fieldValue; 
        
        $noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
		
        $building_amentities = BuildingAmentity::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                                       })->sortable()->paginate($noOfRecord);
                                       
        $fields = [
           'amc_contract_no' => 'AMC Contract No',  
                 
        ];
        $request->flash();   
                                     
        return view('masters::BuildingAmentity.list',compact('building_amentities','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(Request $request)
    {
	 
	   $backIdBreadCrumb = ($request->id)?$request->id:Session::forget('url');
	   $backUrlBreadCrumb = ($request->id)?$request->backurl:'building-amentity.index';
	    
	   $previousUrl = ($request->id)?route($backUrlBreadCrumb,$request->id).'?tab=amentity':'building-amentity';     
       $amentityTypes =  AmentityType::active()->get();
       $buildings = Building::active()->get();
       return view('masters::BuildingAmentity.add_edit',compact('amentityTypes','buildings','backUrlBreadCrumb','backIdBreadCrumb','previousUrl'));
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
	
		Session::forget('url');
        $this->validate($request, [
          'amentity_type_id' => 'required',           
          'landlord_building_id' => 'required',                      
             
       ]);   


        $buildingAmentity = BuildingAmentity::create([
            'amentity_type_id' => $request->amentity_type_id,
            'landlord_building_id' => $request->landlord_building_id,
            'amc_contract_no' => $request->amc_contract_no,
            'building_amentity_utilities_remark' => $request->building_amentity_utilities_remark, 
            'created_by' =>  \Auth::user()->id,           
        ]);
        
       
        // Log insertion
        activity('Add Building Amentity')
          ->performedOn($buildingAmentity)
          ->causedBy(\Auth::user()->id)
          ->withProperties($buildingAmentity)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', ' Building Amentity Added ');
        return redirect($url);  
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show(Request $request,BuildingAmentity $building_amentity)
    {
      $url = url()->previous();
    
    $backIdBreadCrumb  = ($request->backid)?$request->backid:null;
    $backUrlBreadCrumb = ($request->backurl)?$request->backurl:'building-amentity';
     
    $previousUrl   = $url;
		
         return view('masters::BuildingAmentity.view',compact('building_amentity','previousUrl','backUrlBreadCrumb','backIdBreadCrumb'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request, BuildingAmentity $building_amentity)
    {	
			
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'building-amentity.index';
	   
		$previousUrl = ($request->backid)?route($backUrlBreadCrumb,$request->backid).'?tab=amentity':'building-amentity';     
		
		$amentityTypes = AmentityType::active()->get();
		$buildings     = Building::active()->get();
        return view('masters::BuildingAmentity.add_edit',compact('amentityTypes','buildings','building_amentity','previousUrl','backUrlBreadCrumb','backIdBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,BuildingAmentity $building_amentity)
    {
	
		if(isset($request->backurl))
			$url = $request->backurl;
			
         $this->validate($request, [
          'amentity_type_id' => 'required',           
          'landlord_building_id' => 'required',           
                     
       ]);   


      $building_amentity ->update([
            'amentity_type_id' => $request->amentity_type_id,
            'landlord_building_id' => $request->landlord_building_id,
            'building_amentity_utilities_remark' => $request->building_amentity_utilities_remark, 
            'updated_by' =>  \Auth::user()->id,           
        ]);
       // Log insertion
       activity('Update Building Amentity')
          ->performedOn($building_amentity)
          ->causedBy(\Auth::user()->id)
          ->withProperties($building_amentity)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
      session()->flash('success', ' Building Amentity Updated ');
      return redirect($url);

    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Request $request, BuildingAmentity $buildingAmentity)
    {
       $url = url()->previous();

      $buildingAmentity->delete();
    
      session()->flash('success', 'Amenity Deleted Successfully');
      return redirect($url);
    }
     /**
     * Check Unique Amentity Already added for this building
     * @return Response
     */
    public function buildingAmentityUnique(Request $request)
    {
		$amentity_id 			= $request->amentity_id;
		$landlord_building_id 	= $request->landlord_building_id;
		
		$isExist = BuildingAmentity::where('amentity_type_id',$amentity_id)->where('landlord_building_id',$landlord_building_id)->count();
		
		return $isExist?1:'';
		
    }
}
