<?php

namespace Modules\Masters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\AuthController as Controller;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\VacantVacancyLoss;
use Modules\Masters\Entities\UnitTypeCount;
use Modules\Masters\Entities\HomeUtility;
use Modules\Masters\Entities\UnitUtility;
use Modules\Masters\Entities\employee;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\UnitType;
use Modules\Masters\Entities\Location;
use Modules\Sales\Entities\Tenant;	
use Modules\Sales\Entities\TenantContract;
use Modules\Sales\Entities\LandlordContract;
use Exception;

class UnitController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');  
        $this->middleware('permission:add_unit', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_unit', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_unit', ['only' => ['destroy']]);   
        $this->middleware('permission:change_status_unit', ['only' => ['changeStatus']]);         
    //    $this->middleware('permission:view_unit', ['only' => ['index','show']]);                  
    }
    
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
	   $noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
     $units = Unit::are()->filter($request)
     ->vaccatingUnits($request)
	 ->vacantUnitsByDays($request)
                    ->orderBy('unit_type_id','asc')
                    ->sortable()
                    ->paginate($noOfRecord); 

                    //dd($units);
     $fields = [
           'unit_code' => 'Unit Code',  
           'building__building_name' => 'Building Name',  
           'unit__unit_types_name' => 'Unit Types',    
                 
        ];
     $request->flash();

     $unitTypes = UnitType::active()->orderBy('id','ASC')->get();              
                                             
     return view('masters::Unit.list',compact('units','fields','unitTypes','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        //$unitLatest = Unit::latest()->first();
		//$unit_prefix  = prefixData('unit_prefix')->configuration_value;
		
       // if(!empty($unitLatest))
       //     $nextCode = $unit_prefix.str_pad($unitLatest->id+1,4,'0',STR_PAD_LEFT);
       // else
       //     $nextCode = $unit_prefix.str_pad(1,4,'0',STR_PAD_LEFT); 

        $building = Building::active()->get();
        $unitTypes = UnitType::active()->orderBy('id','ASC')->get();
        return view('masters::Unit.add_edit',compact('building','unitTypes'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {

        //$unitLatest = Unit::latest()->first();
		//$unit_prefix  = prefixData('unit_prefix')->configuration_value;
		
       // if(!empty($unitLatest))
        //    $nextCode = $unit_prefix.str_pad($unitLatest->id+1,4,'0',STR_PAD_LEFT);
        //else
		//	$nextCode = $unit_prefix.str_pad(1,4,'0',STR_PAD_LEFT);
		
       $this->validate($request, [
		  'unit_code' =>'required|unique:units',
          'building_id' => 'required',    
          'unit_type_id' => 'required',        
          'unit_type_id' => 'required',           
          'unit_electric_meter' => 'required',           
          'unit_electric_consumer_no' => 'required', 
          'unit_vaccant_status' => 'required',           
          'unit_is_furnished' => 'required',
          'unit_status' => 'required',
       ]);  


         if($request->unit_is_legal != 1)
            $request->unit_legal_date = null;

         $unit	= Unit::create([               
               'unit_code' => $request->unit_code,            
               'building_id' => $request->building_id,            
               'unit_type_id' => $request->unit_type_id,            
               'unit_toilets' => $request->unit_toilets,  
               'unit_no' => $request->unit_no,           
               'unit_floor' => $request->unit_floor,            
               'unit_electric_meter' => $request->unit_electric_meter,            
               'unit_electric_consumer_no' => $request->unit_electric_consumer_no,            
               'unit_water_meter' => $request->unit_water_meter,
               'unit_water_consumer_no' => $request->unit_water_consumer_no,            
               'unit_vaccant_status' => $request->unit_vaccant_status,            
               'unit_is_legal' => $request->unit_is_legal,            
               'unit_legal_date' => $request->unit_legal_date,            
               'unit_is_furnished' => $request->unit_is_furnished, 
               'unit_base_rent' =>replaceCommaWithDot($request->unit_base_rent), 
               'unit_note' => $request->unit_note,     
               'unit_no' =>$request->unit_no,  
               'unit_is_service' =>$request->unit_is_service,  
               'unit_status' => $request->unit_status, 
               'floor_area' => $request->floor_area,            
               'created_by' => \Auth::user()->id,     
         ]);
		$building = Building::where('id',$request->building_id)->first();
    $unit_type = UnitType::where('id',$request->unit_type_id)->first();
    $landlordInfo = LandlordContract::where('building_id',$request->building_id)->first();
   
    $location    = Location::where('id',$building->location_id)->first();
    
    //Insertation Vacany Table only Vacant From 
    VacantVacancyLoss::create([  
        'building_id'=> $request->building_id,
        'unit_id'=> $unit->id,
        'building_name'=> $building->building_name,
        'building_no'=> $building->building_no,
        'unit_no'=> $request->unit_no,
        'unit_type'=> $unit_type->unit_types_name,
        'vacant_from'=> $landlordInfo->landlord_contract_valid_from_date,
        'vacant_to'=>null,
        'location_id'=>$building->location_id,
        'location_name'=>$location->locations_name,
        'vacant_days'=>null,
        'rent_per_month'=>isset($request->unit_base_rent)?replaceCommaWithDot($request->unit_base_rent):0,
        'vacany_loss'=>null,
        'status'=>1,
        'unit_type_id' => $request->unit_type_id, 
        'created_by' => \Auth::user()->id,
    ]);
		 activity('Add Unit')
          ->performedOn($unit)
          ->causedBy(\Auth::user()->id)
          ->withProperties($unit)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
      session()->flash('success', 'Unit Added ');
      return redirect()->route('unit.index'); 

    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show(Request $request,Unit $unit)
    {
		 $cat = $request->cat;
		 $unit_utiltiy 	= UnitUtility::where('unit_id',$unit->id)->paginate(15);
		 $utility 		= HomeUtility::active()->get();
	
		 return view('masters::Unit.view',compact('unit','unit_utiltiy','utility','cat'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,Unit $unit)
    {
		$previousUrl = url()->previous();
		
		$backIdBreadCrumb  = ($request->backid)?$request->backid:null;
		$backUrlBreadCrumb = ($request->backurl)?$request->backurl:'unit.index';
		
        $building = Building::active()->get();
        $unitTypes = array();
       
        $availDataTypeCount  = UnitTypeCount::select('unit_type_id','unittype_count','unit_types_name')->join('unit_types', 'unit_types.id', '=', 'unit_type_count.unit_type_id')->where('building_id',$unit->building_id)->orderBy('unit_types.id', 'ASC')->get();
		$index = 0;
		 
		// UnitType Show only Available Unittype- 2BHK, 3BHK
		foreach($availDataTypeCount as $item){
		  
						
		  $countType =  Unit::where('building_id',$unit->building_id)->where('unit_type_id',$item->unit_type_id)->count();
		  
		  if($unit->unit_type_id==$item->unit_type_id){
			$countType = $countType-1;
			
		  }
			
		  if($countType < $item->unittype_count){
			  
			  $unitTypes[$index] = $item ;
			  $index = $index+1;
		  }
		   
	   }
		 
        return view('masters::Unit.add_edit',compact('building','unitTypes','unit','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Unit $unit)
    {
		if(isset($request->backurl))
			$url = $request->backurl;
        $this->validate($request, [
		  'unit_code' =>'required|unique:units,unit_code,'.$unit->id,
          'building_id' => 'required',           
          'unit_type_id' => 'required',           
          'unit_floor' => 'required',           
          'unit_electric_meter' => 'required',           
          'unit_electric_consumer_no' => 'required',           
          'unit_vaccant_status' => 'required',           
          'unit_is_furnished' => 'required',
          'unit_status' => 'required',
       ]); 


      if($request->unit_is_legal != 1)
            $request->unit_legal_date = null;

         $unit->update([
		   'unit_code' => $request->unit_code,   
           'building_id' => $request->building_id,            
           'unit_type_id' => $request->unit_type_id,            
           'unit_toilets' => $request->unit_toilets,            
           'unit_floor' => $request->unit_floor,            
           'unit_electric_meter' => $request->unit_electric_meter,            
           'unit_electric_consumer_no' => $request->unit_electric_consumer_no,            
           'unit_water_meter' => $request->unit_water_meter,
           'unit_water_consumer_no' => $request->unit_water_consumer_no,            
           'unit_vaccant_status' => $request->unit_vaccant_status,            
           'unit_is_legal' => $request->unit_is_legal,            
           'unit_legal_date' => $request->unit_legal_date,            
           'unit_is_furnished' => $request->unit_is_furnished,  
           'unit_base_rent' =>replaceCommaWithDot($request->unit_base_rent),   
           'unit_no' =>$request->unit_no,  
           'unit_note' => $request->unit_note,   
           'unit_is_service' =>$request->unit_is_service,         
           'unit_status' => $request->unit_status,  
           'floor_area' => $request->floor_area,          
           'updated_by' => \Auth::user()->id,     
         ]);
		
		activity('Update Unit')
          ->performedOn($unit)
          ->causedBy(\Auth::user()->id)
          ->withProperties($unit)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
         
          session()->flash('success', 'Unit Updated ');
			return redirect($url);
      
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
    
      /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Unit $unit)
    {        
      
        if($unit->unit_status == 1) {
            $unit->unit_status = 0;
        } else {
            $unit->unit_status = 1;
        } 
        $unit->save();     
        // Log
        activity('Change unit Status')
         ->performedOn($unit)
          ->causedBy(\Auth::user()->id)
          ->withProperties($unit)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Status Changed Successfully');
       return redirect()->route('unit.index');   
    }
    
    public function ajaxFloorList(Request $request){

	   
       $id = $request->input('id'); 
	  
       $list['building']  = Building::select('id','building_no_floor','building_prefix')->where('id',$id)->orderBy('id', 'DESC')->get();
       $list['unit_type'] = array();
       $availDataTypeCount  = UnitTypeCount::select('unit_type_id','unittype_count','unit_types_name')->join('unit_types', 'unit_types.id', '=', 'unit_type_count.unit_type_id')->where('building_id',$id)->orderBy('unit_types.id', 'ASC')->get();
	   $index = 0;
	   foreach($availDataTypeCount as $item){
		   			
		  $countType =  Unit::where('building_id',$id)->where('unit_type_id',$item->unit_type_id)->count();
		 
		  if($countType < $item->unittype_count){
			  
			  $list['unit_type'][$index] = $item ;
			  $index = $index+1;
		  }
		   
	   }
	   	
       return $list;

    }  
    public function  utilitiesUpdate(Request $request){

	   $category 	= $request->category;
	   

       $utility  	= HomeUtility::Where('category',$category )->get();
        
       foreach($utility->toArray() as $item){
		    $id = 'count-'.$item['id'];
		    $utilityCount = $request->$id;
		  
		    if($id){
				$utilityId  = $item['id'];
				$unit 		= $request->unit;
				UnitUtility::updateOrCreate(['unit_id' => $unit,'home_utility_id'=>$utilityId], 
					['utiltity_count' =>$utilityCount]);
		    }
	   }
	   return redirect()->route('unit.show',[$unit,'cat'=>$category]);  
	 
    }
    /*
    *
    *
    * QR Code Generator
    *
    */ 
    public function qrCodeGenerator($unit_id)
    {
      $qrCode = \QrCode::format('png')->size(400)->generate($unit_id); 
      return response($qrCode)->header('Content-type','image/png');
    }
    /*
    *
    *
    * Qr Code Scanner
    *
    */
    public function qrCodeScanner(Request $request)
    {
      try{

        $unit_id = $request->input('unit_id');
        $type = $request->input('type');
        $contract = TenantContract::where('unit_id',$unit_id)->active()->first();
        
        
        $unit = Unit::where('id',$unit_id)->first();
        $building = Building::where('id',$unit->building_id)->active()->first();
        if($type == 'key'){
          return view('backoffice::Key.key_accept_ajax',compact('unit','contract'));
        }else{
          return json_encode(array($unit,$contract,$building,$contract->tenant));
        }

      }
      catch(exception $e){
        return json_encode($e->getmessages());
      }
      
      
    } 



    /*
    * unitFilter
    *
    */
    public function unitFilter(Request $request){
	   /*	$noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
        $units = Unit::are()->filter($request)                    
                    ->sortable()
                    ->paginate($noOfRecord); 
		$ajax = true;
		$request->flash(); */
   // $management_id = 1;
    $management_id = 1;
    $urlExp = explode("/",$request->input('curr_url'));
    //echo "<pre>";print_r($request->all());exit;
    if(end($urlExp) == 'normalUnits'){
      $management_id = 2;
    }
    else if(end($urlExp) == 'comprehensiveUnits'){
      $management_id = 1;
    }

    if($request->unit_type_id != ''){
      $unitTypeId = $request->unit_type_id;
    }
    (!isset($unitTypeId) ? $unitTypeId = '' : '');

    if($request->unit_vaccant_status != ''){
      $unit_vaccant_status = $request->unit_vaccant_status;
    }
    (!isset($unit_vaccant_status) ? $unit_vaccant_status = '' : '');
    //print_r($management_id);exit;
    $noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
    $units  = Unit::whereHas('building',function($query) use($management_id) {
        $query->where('management_id',$management_id)
        ->where('building_status',1)
        //-> where('building_status',1)
        ;
      })
     ->when($unitTypeId != '',function($q) use($unitTypeId){
        $q->where('unit_type_id',$unitTypeId);
     })
     ->where('unit_status',1)
     ->when($unit_vaccant_status != '',function($q) use($unit_vaccant_status){
        $q->where('unit_vaccant_status',$unit_vaccant_status);
     })
     // ->filter($request)
      ->orderBy('unit_type_id','asc')
                      ->sortable()
                      ->paginate($noOfRecord);
      /*$units = Unit::are()->filter($request)
       ->vaccatingUnits($request)
      ->vacantUnitsByDays($request)
     ->whereHas('building',function($query) use($management_id) {
          $query-> where ('building_status',1)
            ->where('management_id',$management_id);
        })
            ->where('unit_status',1)
            ->orderBy('unit_type_id','asc')
                      ->sortable()
                      ->paginate($noOfRecord);  */
    $ajax = true;
    $request->flash();

		return view('masters::Unit.list_ajax',compact('units','ajax'));       

    }
    public function viewExistingUnit(Request $request){
          $building_id = $request->building_id;
          $units = Unit::where('building_id',$building_id)->get();
          return view('masters::Unit.view_existing_unit_modal',compact('units'));
    }
}
 

      


               
                                             
     
  
