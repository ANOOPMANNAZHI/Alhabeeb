<?php

namespace Modules\Masters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\AuthController as Controller;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\UnitType;
use Modules\Masters\Entities\UnitTypeCount;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\Vendor;
use Modules\Masters\Entities\BuildingType;
use Modules\Masters\Entities\ManagementType;
use Modules\Masters\Entities\BuildingAmentity;
use Modules\Masters\Entities\BuildingInsurance;
use Modules\Masters\Entities\BuildingImage;
use Modules\Masters\Entities\BuildingDocs;
use Modules\Masters\Entities\BuildingEleWaterReading;
use Modules\Masters\Entities\AreBuildingAssign; 
use Modules\Masters\Entities\PreferredBuilding;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Employee;
use App\User;
use Image;
use DB;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Modules\Sales\Entities\LandlordContract;
use Dynamics;
use Exception;


class BuildingController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');  
    $this->middleware('permission:add_building', ['only' => ['create','store']]);  
    $this->middleware('permission:edit_building', ['only' => ['edit','update']]);  
    $this->middleware('permission:delete_building', ['only' => ['destroy']]);   
    $this->middleware('permission:change_status_building', ['only' => ['changeStatus']]);         
    $this->middleware('permission:view_building', ['only' => ['index','show']]);                  
  }
  
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		$noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
		$buildings =  Building::where('building_status',1)->areFilter()->filter($request)
		->sortable()->paginate($noOfRecord);

    $building_lists =  Building::areFilter()->filter($request)
    ->sortable()->get();

      
		$fields = [
		'building_name' => 'Name',  
		'building_code' => 'Code',             
		'vendor__vendor_name' => 'Vendor Name',             

		];
		$request->flash();  
//dd($swipe);

		return view('masters::Building.list',compact('buildings','fields','request','building_lists'));
    }


    
    /*
    * buildingFilter
    *
    */
    public function buildingFilter(Request $request){
      $buildings = Building::areFilter()->filter($request)                    
      ->sortable()
      ->paginate(15); 
      $ajax = true;
      $request->flash();
      return view('masters::Building.list_ajax',compact('buildings','ajax'));       

    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      $buildingLatest = Building::orderBy('building_code','desc')->first();
     
      $building_prefix  = prefixData('building_prefix')->configuration_value;

      // print_r(json_encode($buildingLatest));exit();

      if(!empty($buildingLatest))
        $nextCode = str_pad($buildingLatest->building_code+1,3,STR_PAD_LEFT);
      else
        $nextCode = str_pad(1,3,STR_PAD_LEFT); 

      $location =  Location::active()->get();
      $vendors =  Vendor::active()->where('vendor_type_id','=',2)->get();
      $buildingTypes =  BuildingType::active()->get();
      $managementTypes =  ManagementType::active()->get();
      $ares =  User::role('are')->get();
      $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;

      return view('masters::Building.add_edit',compact('nextCode','location','vendors','buildingTypes','managementTypes','ares','upload_size'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    { 
      //dd($request->building_meter_category);
      
      $buildingLatest = Building::orderBy('building_code','desc')->first();
      $building_prefix  = prefixData('building_prefix')->configuration_value;
      $size = prefixData('upload_size_in_mb')->configuration_value* 1024;
     
      if(!empty($buildingLatest))
        $nextCode = str_pad($buildingLatest->building_code+1,3,STR_PAD_LEFT);
      else
        $nextCode = str_pad(1,3,STR_PAD_LEFT); 
      

      $this->validate($request, [
        'building_name' => 'required|max:30',           
        'vendor_id' => 'required',           
        'building_no' => 'required', 
        'building_type_id' => 'required', 
        'management_id' => 'required', 
        'building_address' => 'required|max:250', 
        'building_pc' => 'required', 
        'location_id' => 'required',           
        'google_location' => 'required', 
        'building_note' => 'required'
      /*'building_img_name.*'    => 'mimes:jpeg,jpg,pdf,docs,doc,docx|max:'.$size ,
       // 'building_doc_path_name.*'    => 'mimes:jpeg,jpg,pdf,docs,doc,docx|max:'.$size , */
        ]);

      try {

       

        $building = Building::create([
        'building_code' => $nextCode,
        'building_name' => $request->building_name,
        'vendor_id' => $request->vendor_id,
        'building_no' => $request->building_no,
        'building_type_id' => $request->building_type_id,
        'management_id' => $request->management_id,
        'building_address' => $request->building_address,
        'building_pc' => $request->building_pc,
        'location_id' => $request->location_id,
        'building_note' => $request->building_note,
            'building_status' => 0, // 0 - inactive , 1 -active
            'building_geo_long' => $request->building_geo_long,
            'building_geo_lat' => $request->building_geo_lat,
            'google_location' => $request->google_location,
            'building_no_floor' => $request->building_no_floor,
            'building_prefix' => $request->building_prefix,
            'plot_no' => $request->plot_no,
            'block_number' => $request->block_number,
            'build_up_area' => $request->build_up_area,
            'landmark' => $request->landmark,
            'db_number' => $request->db_number,
            'building_year' => $request->building_year,
            /*'electricity_acc_no' => $request->electricity_acc_no,
            'water_acc_no' => $request->water_acc_no,
            'electricity_met_no' => $request->electricity_met_no,
            'water_met_no' => $request->water_met_no,*/
            'watchman_no' => $request->watchman_no,
            'management_date' => $request->management_date,
            'user_id' => $request->user_id,
            'building_maintenance_info' => $request->building_maintenance_info,
            'created_by' => \Auth::user()->id,
            'ax_division' => $request->ax_division,
            ]);

      } catch (\Exception $e) {

          print_r($e->getMessage()) ;exit();
      }


      
		
		if(isset($building->id)){	
			$buildingInfo  = Building::where('id',$building->id)->first();
			
			if(Dynamics::BuildingAxPushData('AXBuilding', $buildingInfo)=='Error'){
				Building::destroy($building->id);
				return Redirect::back()->withMessage('error', 'Microsoft Dynamics API Service Error');
			}
		
		}
      /* Ele Meter reading*/
      if(isset($request->building_meter_category)):
        $building_meter_category = $request->building_meter_category;

      foreach($building_meter_category as $key => $category):

        if(isset($request['building_meter_category'][$key])){
         $readings=BuildingEleWaterReading::create(['building_id'=>$building->id,
          'building_meter_category'=>$request['building_meter_category'][$key],
          'electricity_acc_no'=>$request['electricity_acc_no'][$key],
          'electricity_met_no'=>$request['electricity_met_no'][$key],
          'water_acc_no'=>$request['water_acc_no'][$key],
          'water_met_no'=>$request['water_met_no'][$key], 
          'created_by' => \Auth::user()->id]); 
		}
       endforeach;
       endif;


       if(!empty($request->file('building_img_name'))):
        $files = $request->file('building_img_name');
      foreach($files as $key => $file):
        $path = public_path('img');
      

      $imageName = time().'.'.$request['building_img_name'][$key]->getClientOriginalExtension();
      

      $large_img = Image::make($request->file('building_img_name')[$key]->getRealPath());
      $large_img->resize(800, 500);
      $large_img->save($path.'/'.$imageName,100);
      $img_path =    Storage::putFile('public/BuildingImages', new File($path.'/'.$imageName), 'public');

      $thumb_img = Image::make($request->file('building_img_name')[$key]->getRealPath());
      $thumb_img->resize(150, 100);
      $thumb_img->save($path.'/'.$imageName,100);           
      $thumb_path = Storage::putFile('public/BuildingImages', new File($path.'/'.$imageName), 'public');


      $docs=BuildingImage::create(['building_id'=>$building->id,
        'building_path_file_name'=>$img_path,
        'building_img_category'=>$request['building_img_category'][$key],
        'building_path_thumbnail'=>$thumb_path,
        'created_by' => \Auth::user()->id]);
      endforeach;
      endif;

	  if(!empty($request->file('building_doc_path_name'))):
			
		  $files = $request->file('building_doc_path_name');

		  foreach($files as $key => $file):
			if(isset($request['building_doc_category'][$key])){
			  $uniqueFileName = $file->getClientOriginalName() ;
			  $doc_path = Storage::putFile('public/BuildingDocs',$request['building_doc_path_name'][$key]);

			  $docs=BuildingDocs::create(['building_id'=>$building->id,
				'building_doc_category'=>$request['building_doc_category'][$key],
				'building_doc_path_name'=>$doc_path,
				'building_doc_name'=>$uniqueFileName,
				'created_by' => \Auth::user()->id]);
			}
		  endforeach;
      
      endif;
        //dd($request->all());
      
      // Log 
      activity('Add Building')
      ->performedOn($building)
      ->causedBy(\Auth::user()->id)
      ->withProperties($building)
      ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

      session()->flash('success', ' Building Added ');
      return redirect()->route('building.index'); 

    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show(Request $request,Building $building)
    {

      $tab = ($request->tab)?$request->tab:'unit';
      $buildingAmentities = BuildingAmentity::where('landlord_building_id',$building->id)->get();
      $buildinginsurances = BuildingInsurance::where('building_id',$building->id)->get();
      $unitype = UnitType::where('unit_types_status','=',1)->orderBy('id','ASC')->get();
      $unitypeCnt = UnitTypeCount::where('building_id',$building->id)->get();
      $images = BuildingImage::where('building_id',$building->id)->get();
      $docs = BuildingDocs::where('building_id',$building->id)->get();
      $buildingEleWaterReading = BuildingEleWaterReading::where('building_id',$building->id)->get();
      $buildingUnit = $building->unit()->get();
      //dd($buildingUnit);
      return view('masters::Building.view',compact('building','buildingUnit','buildingAmentities','buildinginsurances','tab','unitype','unitypeCnt','images','docs','buildingEleWaterReading'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,Building $building)
    {
     $previousUrl = url()->previous();
     
     $backIdBreadCrumb  = ($request->backid)?$request->backid:null;
     $backUrlBreadCrumb = ($request->backurl)?$request->backurl:'building.index';
     
     $location =  Location::active()->get();
     $vendors =  Vendor::active()->get();
     $buildingTypes =  BuildingType::active()->get();
     $managementTypes =  ManagementType::active()->get();
         //dd($building->buildingImage);
     $ares =  User::role('are')->get();
     $upload_size       = prefixData('upload_size_in_mb')->configuration_value * 1000000;

     return view('masters::Building.add_edit',compact('location','vendors','buildingTypes','managementTypes','building','ares','previousUrl','backIdBreadCrumb', 'backUrlBreadCrumb','upload_size'));
   }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Building $building)
    {
     //dd($request->all());
      if(isset($request->backurl))
       $url = $request->backurl;
     $size = prefixData('upload_size_in_mb')->configuration_value* 1024;
     
     $this->validate($request, [
      'building_name' => 'required|max:30',           
      'vendor_id' => 'required',           
      'building_no' => 'required', 
      'building_type_id' => 'required', 
      'management_id' => 'required', 
      'building_address' => 'required|max:250', 
      'building_pc' => 'required', 
      'location_id' => 'required',           
      'google_location' => 'required', 
      'building_note' => 'required', 
      'building_img_name.*'    => 'mimes:jpeg,jpg,pdf,docs,doc,docx|max:'.$size ,
      'building_doc_path_name.*'    => 'mimes:jpeg,jpg,pdf,docs,doc,docx|max:'.$size ,
      ]);


     $building->update([
      'building_name' => $request->building_name,
      'vendor_id' => $request->vendor_id,
      'building_no' => $request->building_no,
      'building_type_id' => $request->building_type_id,
      'management_id' => $request->management_id,
      'building_address' => $request->building_address,
      'building_pc' => $request->building_pc,
      'location_id' => $request->location_id,
      'building_note' => $request->building_note,
      'building_geo_long' => $request->building_geo_long,
      'building_geo_lat' => $request->building_geo_lat,
      'google_location' => $request->google_location,
      'building_no_floor' => $request->building_no_floor,
      'building_prefix' => $request->building_prefix,
      'plot_no' => $request->plot_no,
      'block_number' => $request->block_number,
      'build_up_area' => $request->build_up_area,
      'landmark' => $request->landmark,
      'db_number' => $request->db_number,
      'building_year' => $request->building_year,
            /*'electricity_acc_no' => $request->electricity_acc_no,
            'water_acc_no' => $request->water_acc_no,
            'electricity_met_no' => $request->electricity_met_no,
            'water_met_no' => $request->water_met_no,*/
            'watchman_no' => $request->watchman_no,
            'management_date' => $request->management_date,
            'user_id' => $request->user_id,
            'building_maintenance_info' => $request->building_maintenance_info,
            'updated_by' => \Auth::user()->id,
            'ax_division' => $request->ax_division,
            ]);

     /* Ele Meter reading*/
     if(!empty($request->building_meter_category)):

        BuildingEleWaterReading::where('building_id',$building->id)->delete();

      $building_meter_category = $request->building_meter_category;
     
    foreach($building_meter_category as $key => $category):
	
       // dd($request['building_meter_category'][$key]);

      if(isset($request['building_meter_category'][$key]) || $request['building_meter_category'][$key] === '0'){
       
      
         $readings=BuildingEleWaterReading::create(['building_id'=>$building->id,
        'building_meter_category'=>$request['building_meter_category'][$key],
        'electricity_acc_no'=>$request['electricity_acc_no'][$key],
        'electricity_met_no'=>$request['electricity_met_no'][$key],
        'water_acc_no'=>$request['water_acc_no'][$key],
        'water_met_no'=>$request['water_met_no'][$key],
        'created_by' => \Auth::user()->id]);
	
      }

     
    endforeach;
    endif;

    /* Multiple Docs And images */

    if(!empty($request->file('building_img_name'))):
      $files = $request->file('building_img_name');
    foreach($files as $key => $file):
      $path = public_path('img');


    $imageName = time().'.'.$request['building_img_name'][$key]->getClientOriginalExtension();
    
    
    $large_img = Image::make($request->file('building_img_name')[$key]->getRealPath());
    $large_img->resize(800, 500);
    $large_img->save($path.'/'.$imageName,100);
    $img_path =    Storage::putFile('public/BuildingImages', new File($path.'/'.$imageName), 'public');

    $thumb_img = Image::make($request->file('building_img_name')[$key]->getRealPath());
    $thumb_img->resize(150, 100);
    $thumb_img->save($path.'/'.$imageName,100);           
    $thumb_path = Storage::putFile('public/BuildingImages', new File($path.'/'.$imageName), 'public');

    $docs=BuildingImage::create(['building_id'=>$building->id,
      'building_path_file_name'=>$img_path,
      'building_img_category'=>$request['building_img_category'][$key],
      'building_path_thumbnail'=>$thumb_path,
      'created_by' => \Auth::user()->id]);
    endforeach;
    endif;

    if(!empty($request->file('building_doc_path_name'))):
      $files = $request->file('building_doc_path_name');



    foreach($files as $key => $file):
      $uniqueFileName = $file->getClientOriginalName() ;
    $doc_path = Storage::putFile('public/BuildingDocs',$request['building_doc_path_name'][$key]);

    $docs=BuildingDocs::create(['building_id'=>$building->id,
      'building_doc_category'=>$request['building_doc_category'][$key],
      'building_doc_path_name'=>$doc_path,
      'building_doc_name'=>$uniqueFileName,
      'created_by' => \Auth::user()->id]);
    endforeach;
    endif;

		// Log
    activity('Update Building')
    ->performedOn($building)
    ->causedBy(\Auth::user()->id)
    ->withProperties($building)
    ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
    session()->flash('success', ' Building Updated ');
    
    return redirect($url);


  }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {

      $BuildingDocs = BuildingDocs::find($id);
      
      Storage::delete($BuildingDocs->building_doc_path_name);
      $BuildingDocs->delete();
      
    }
    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroyImage(BuildingImage $buildingImage,$id)
    {

      $BuildingImage = BuildingImage::find($id);
      
      Storage::delete($BuildingImage->building_path_file_name);
      Storage::delete($BuildingImage->building_path_thumbnail);
      $BuildingImage->delete();
      
    }
    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroyEleReading(BuildingEleWaterReading $buildingEleWaterReading,$id)
    {

      $BuildingEleWaterReading = BuildingEleWaterReading::find($id);
      $BuildingEleWaterReading->delete();
      
    }
      /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
      public function changeStatus(Building $building)
      {
       $status = Building::where('id',$building->id);  
       if($building->building_status == 1) {
        $building->building_status = 0;
      } else {
        $building->building_status = 1;
      } 
      $building->save();    
      
         // Log   
      activity('Change Building Status')
      ->causedBy(\Auth::user()->id)
      ->withProperties($status)
      ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
      
      session()->flash('success', 'Status Changed Successfully');
      return redirect()->route('building.index');   
    }
    
    public function updateUnitTypeCount(Request $request){
//dd($request->all());
      $building_id	= $request->building_id; 
      
      $unitType  	=  UnitType::Where('unit_types_status',1 )->get();
      
      foreach($unitType->toArray() as $item){
			$id = 'count-'.$item['id'];
			$unitTypeCount = $request->$id;
		  			
			$unitId  		= $item['id'];
			$countType 		= Unit::where('building_id',$building_id)->where('unit_type_id',$unitId)->count();
				
			if(!empty($countType) && $countType > $unitTypeCount){
				
				  session()->flash('error', $item['unit_types_name'] .' Unit Type Greater Than '.$countType. ' Only Allow.');
				  return redirect()->route('building.show', $building_id);     
				   
			}
		
			// $unit 		= $request->unit;
			UnitTypeCount::updateOrCreate(['building_id' => $building_id,'unit_type_id'=>$unitId], 
			   ['unittype_count' =>$unitTypeCount,'created_by'=>\Auth::user()->id]);
     
      }
     
      session()->flash('success', 'Unit Detail Changed Successfully');
      return redirect()->route('building.show', $building_id);  
      
    }
  /**
    *
    * Building Autocomplete
    *
    **/
  public function buildingAutocomplete(Request $request){

   $key = $request->term;
   $buildingContract = LandlordContract::active()->get();
   $buildingId  = $buildingContract->pluck('building_id');

   $building =   Building::active()->where('building_name', 'ILIKE', '%'.$key.'%')
   ->WhereIn('id',$buildingId)
   ->select('building_name AS value','id AS ids')
   ->get();

   return $building ;


 }
  /**
  *
  * Building Autocomplete - Code
  *
  **/
  public function buildingAutocompleteCode(Request $request){

   $key = $request->building;
   $landlord_id = $request->landlord;
   $buildingContract = LandlordContract::active()->get();
   $buildingId  = $buildingContract->pluck('building_id');
   $building =   Building::Inactive()->where('building_name', 'ILIKE', '%'.$key.'%')
   ->where('vendor_id', $landlord_id)
   ->WhereNotIn('id',$buildingId)
   ->select('id AS ids',DB::raw("CONCAT(building_name,'-',building_code) as value"),'management_id')
   ->get();
   
   return $building ;


 }
 /**
    *
    * Ajax Building with ax-division
    *
    **/
  public function ajaxBuildingWithAxDivision(Request $request){

   $id = $request->building_id;
   
   $building = Building::where('id','=', $id)->select('building_name','ax_division')->first();

   return $building ;


 }
 
 
}
