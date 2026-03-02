<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Masters\Entities\PaymentMethod;
use Modules\Masters\Entities\BuildingAmentity;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\AmentityType;
use Modules\Sales\Entities\LandlordContract;
use Modules\Maintenance\Entities\AmcContract;
use Modules\Maintenance\Entities\AmcTask;
use Modules\Maintenance\Entities\AmcContractAmenities;
use Modules\Maintenance\Entities\AmcSchedule;
use Modules\Maintenance\Entities\AmcScheduleAmenity;
use Modules\Masters\Entities\Vendor;
use DB;
use Session;
use URL;
use Route;

class AmcContractController extends Controller
{
  public function __construct()
    {
        $this->middleware('auth');  
        $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value;


    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
      session()->forget('oldBuilding');

      $name = Route::currentRouteName();

      $enquiry_fields = [
      'amc_contract_no' => 'Contract No',
      'vendor__vendor_name' => 'Contractor',
      'building__building_name' => 'Building',
      'paymentMethod__payment_method_code' => 'Frequency',
      ];

      
      $operations = [
      'ilike' => ' Is Equal To '  ,
      '!=' => ' Is Not Equal To '  ,
      '>' => ' Is Greater Than '  ,
      '>=' => ' Is Greater Than Or Equal To '  ,
      '<' => ' Is Less Than '  ,
      '<=' => ' Is Less Than Or Equal To'  ,
      'ilike%...%' => ' Like%...% ',
      ];
      
      $request->flash();        

      $amcContracts = AmcContract::filter($request)
                                   ->sortable()->paginate($this->noOfRecord);

      
      $route   =  $request->url();

      if(isset($request->ajax))
        return view('maintenance::Amc.amc_contract_list_ajax',compact('amcContracts','request','route'));

      return view('maintenance::Amc.amc_contract_list',compact('amcContracts','request','enquiry_fields','operations','name'));

    /* $amcContracts = AmcContract::paginate(20);
    return view('maintenance::Amc.amc_contract_list',compact('amcContracts'));*/
  }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      session()->forget('oldBuilding');
     /* $amcLatest = AmcContract::latest()->first();*/
      $amcLatest = AmcContract::orderBy('id','desc')->limit(1)->first();
      $prefix       = prefixData('amc_prefix')->configuration_value;
      if(!empty($amcLatest))
        $nextCode = $prefix.str_pad($amcLatest->id+1,4,'0',STR_PAD_LEFT);
      else
        $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
      $paymentMethods = PaymentMethod::active()->get(); 
      return view('maintenance::Amc.add_amc_contract',compact('paymentMethods','nextCode'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
      //dd($request->all());
      $amcLatest = AmcContract::orderBy('id','desc')->limit(1)->first();
      $prefix       = prefixData('amc_prefix')->configuration_value;
      if(!empty($amcLatest))
        $nextCode = $prefix.str_pad($amcLatest->id+1,4,'0',STR_PAD_LEFT);
      else
      $nextCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);  
      $this->validate($request, [
        'amc_contract_no' => 'required',                    
        'vendor_id'   => 'required',
        'building_id'   => 'required',
        'payment_method_id' => 'required', 
        /*'amentity_types_id' => 'required', */
        ]);

        //Amc Contract Insert
      $amcContract = AmcContract::create([              
        'amc_contract_no' => $nextCode,
        'amc_contract_period_from' => $request['amc_contract_period_from'],
        'amc_contract_period_to' => $request['amc_contract_period_to'],
        'vendor_id' => $request['vendor_id'],
        'building_id' => $request['building_id'],
        'amc_contract_cost' => replaceCommaWithDot($request['amc_contract_cost']),
        'payment_method_id' => $request['payment_method_id'],
        'created_by' => \Auth::user()->id
        ]); 

        //Amc Contract Amenities Insert
      for($i =0; $i<count($request['amentity_types_tech_id']);$i++){
        $amc_contract_amenities = AmcContractAmenities::create([
          'amc_contract_id'=>$amcContract->id,
          'amenities_type_id' =>$request['amentity_types_tech_id'][$i],
          ]);
          BuildingAmentity::where('amentity_type_id',$request['amentity_types_tech_id'][$i])->where('landlord_building_id',$request['building_id'])->update(['amc_contract_no'=>$request['amc_contract_no']]);
      }

      session()->flash('success', 'AMC Contract Created Successfully');
      return redirect()->route('amcContract.index');
    }

    /**
     * Show the specified resource.

     * @return Response
     */
    public function show($amc_contract_id)
    {
      $amcContract =  AmcContract::where('id',$amc_contract_id)->first();
      $amcContractAmenities = AmcContractAmenities::where('amc_contract_id',$amcContract->id)->paginate(10);
      return view('maintenance::Amc.amc_contract_view',compact('amcContract','amcContractAmenities'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($amc_contract_id)
    {
      session()->forget('oldBuilding');
     $paymentMethods = PaymentMethod::active()->get(); 
     $nowUrl = url()->previous();
     Session::put('nowUrl', $nowUrl);
     $amcContract =  AmcContract::where('id',$amc_contract_id)->first();
     Session::put('oldBuilding',$amcContract->building_id);
     $amcContractAmenities = AmcContractAmenities::where('amc_contract_id',$amcContract->id)->paginate(10);
     //dd($amcContractAmenities);
     $building_id = $amcContract->building_id;
     $amcSelectAmenity=BuildingAmentity::where('landlord_building_id',$building_id)->get();
     return view('maintenance::Amc.add_amc_contract',compact('paymentMethods','nowUrl','amcContract','amcContractAmenities','amcSelectAmenity'));
   }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,AmcContract $amcContract)
    {
      $current = Session::get('current');
      $this->validate($request, [
        'amc_contract_no' => 'required',                    
        'amc_contract_period_from'   => 'required|date',
        'amc_contract_period_to'   => 'required|date',
        'payment_method_id' => 'required', 
        ]);

        //Amc Contract Update
      $amcContract->update([
        'vendor_id' => $request['vendor_id'],
        'building_id' => $request['building_id'],
        'amc_contract_period_from' => $request['amc_contract_period_from'],
        'amc_contract_period_to' => $request['amc_contract_period_to'],
        'amc_contract_cost' => replaceCommaWithDot($request['amc_contract_cost']),
        'payment_method_id' => $request['payment_method_id'],
        'updated_by' => \Auth::user()->id,
        ]);
//Amc Contract Amenities Insert
      if(!empty($request['amentity_types_tech_id'])){
        $oldAminity = AmcContractAmenities::where('amc_contract_id',$amcContract->id)->pluck('amenities_type_id');
        //Update before insert
        BuildingAmentity::whereIn('amentity_type_id',$oldAminity)->where('landlord_building_id',$request['building_id'])->update(['amc_contract_no'=>null]);
        
        //delete before insert
        AmcContractAmenities::where('amc_contract_id',$amcContract->id)->delete();
        

        for($i =0; $i<count($request['amentity_types_tech_id']);$i++){
          $amc_contract_amenities = AmcContractAmenities::create([
            'amc_contract_id'=>$amcContract->id,
            'amenities_type_id' =>$request['amentity_types_tech_id'][$i],
            ]);
          BuildingAmentity::where('amentity_type_id',$request['amentity_types_tech_id'][$i])->where('landlord_building_id',$request['building_id'])->update(['amc_contract_no'=>$request['amc_contract_no']]);
        }
      }
      session()->forget('oldBuilding');
      
      session()->flash('success', 'Amc Contract Updated Successfully');
      return redirect()->route($current);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
      $nowUrl = url()->previous();
      $contractAmenity=AmcContractAmenities::find($id);
      $contractAmenity->delete();
      session()->flash('success', 'AMC Contract Amenity Deleted Successfully');
       return redirect($nowUrl);
    }
    /*
    *
    * Get Building Amenity in Contract
    *
    *
    */
    public function buildingAmenityInContract(Request $request)
    {
      $amenity = array();
      $oldBuildings = "";
      $building_id = $request->building_id;
      $contractedAm = AmcContract::where('building_id',$building_id)->where('amc_contract_status',0)->pluck('id');
      $s = AmcContractAmenities::whereIn('amc_contract_id',$contractedAm)->pluck('amenities_type_id');
      $flattenedAm = array_flatten($s);
      $s = array_unique($flattenedAm,SORT_REGULAR);
      $buildingAmenity = BuildingAmentity::whereNotIn('amentity_type_id',$s)->with('amentityType')->where('landlord_building_id',$building_id)->get();
      foreach($buildingAmenity as $amenitys){

       $amenity[] = array('id'=> $amenitys->amentityType->id ,'name'=>$amenitys->amentityType->amentity_types_name);
     
      }
      $oldBuilding = Session::get('oldBuilding');
      if($oldBuilding == $building_id)
      {
        $oldBuildings = 1;
      }
      return json_encode(array($amenity,$oldBuildings));


    }
    //get building amenity list
    public function getBuildingAmenity(Request $request){

     $amenity = array();
     $oldBuildings = "";
     $building_id = $request->building_id;
     $unit_id = $request->unit_id;
     $scheduledAm = AmcSchedule::where('building_id',$building_id)->where('amc_schedule_status',0)->when($unit_id, function ($query) use($unit_id){
         $query->where('unit_id',$unit_id);
      })->pluck('id');

     $s = AmcScheduleAmenity::whereIn('amc_schedule_id',$scheduledAm)->pluck('amenities_type_id');
     $flattenedAm = array_flatten($s);
     $s = array_unique($flattenedAm,SORT_REGULAR);

     $buildingAmenity = BuildingAmentity::whereNotIn('amentity_type_id',$s)->with('amentityType')->where('landlord_building_id',$building_id)->get();

     /*$buildingAmenity = BuildingAmentity::with('amentityType')->where('landlord_building_id',$building_id)->get();*/
     foreach($buildingAmenity as $amenitys){

       $amenity[] = array('id'=> $amenitys->amentityType->id ,'name'=>$amenitys->amentityType->amentity_types_name);
     
      }
    $oldBuilding = Session::get('oldBuilding');
    if($oldBuilding == $building_id)
    {
      $oldBuildings = 1;
    }
     return json_encode(array($amenity,$oldBuildings));
     /*return view('maintenance::Amc.building_amenity_list',$data);*/


   }
  /**
  *
  * Building Autocomplete - Code
  *
  **/
    public function buildingAmcAutocompleteCode(Request $request){

     $key = $request->building;
     $vendor_id = $request->vendor;
     $buil =array();
     $building =   Building::Active()->where('building_maintenance_info',0)->where('building_name', 'ILIKE', '%'.$key.'%')
     //->where('vendor_id', $vendor_id)
     //->WhereNotIn('id',$buildingId)
     ->select('id AS ids',DB::raw("CONCAT(building_name,'-',building_code) as value"),'management_id')
     ->get();
     $building_id = $building->pluck('ids');
     $scheduledAm =BuildingAmentity::whereIn('landlord_building_id',$building_id)->get();
     foreach ($scheduledAm  as $amenity) {
        if($amenity->amc_contract_no == null){
          $buil[] = $amenity->landlord_building_id;
        }
       
     }
     $building = Building:: whereIn('id',$buil)->select('id AS ids',DB::raw("CONCAT(building_name,'-',building_code) as value"),'management_id')->get();
     return $building ;
   }
  // add amc building amenity
   public function addAmcAmenity(Request $request){
    $no = $request->no;
    $amentity_types_id = $request->amentity_types_id;
    $building_id = $request->building_id;
    $amenity = AmentityType::where('id',$amentity_types_id)->first();  
    $amenity_name = $amenity->amentity_types_name;
    $amenity_code = $amenity->amentity_types_code;
    
    return view('maintenance::Amc.amc_building_amenity',compact('no','amentity_types_id','amenity_name','amenity_code'));
  }
  /*
  *
  * Check Contract Exist
  *
  */
  public function checkContractExist(Request $request){
    
    $amentity_types_id = $request->amentity_types_id;
    $building_id = $request->building_id;
    $editStatus = $request->editStatus;

    $contracts = BuildingAmentity::where('amentity_type_id',$amentity_types_id)->where('landlord_building_id',$building_id)->first();
    $contract = $contracts->amc_contract_no;
    
    if($contract == null){
       return 0;
      if($editStatus != ""){
        return 0;
      }
      
    }else{
      if($editStatus != ""){
        return 0;
      }else{
        return 1;
      }
    }
    
  }
//Advance search
  public function amcContractSearch(Request $request){

    $closure = array();
    $closure_or = array();

    $vendor_q = array();
    $vendor_or = array();
    $building_q = array();
    $building_or = array();
    $paymentMethod_q = array();
    $paymentMethod_or = array();  

     //dd($request->fieldName);
    if(isset($request->fieldName)){
     if(count($request->fieldName) > 0){

      foreach ($request->fieldName as $key => $value) {

        if( !empty($request->fieldValue[$key]) && !empty($request->fieldValue[$key]) && !empty($value) ) {

         $operation = $request->operation[$key];
         $fieldValue = $request->fieldValue[$key];

         if($request->operation[$key] == 'ilike%...%' ){
          $fieldValue = '%'.$request->fieldValue[$key].'%';
          $operation = 'ilike';
        }if($value == 'vendor_name'){

          if($key != 0 && $request->logic[$key -1 ] == 'or' )
            $vendor_or[] = array( $value , $operation ,$fieldValue);
          else
            $vendor_q[] = array( $value , $operation ,$fieldValue);

        }elseif($value == 'building_name'){

          if($key != 0 && $request->logic[$key -1 ] == 'or' )
            $building_or[] = array( $value , $operation ,$fieldValue);
          else
            $building_q[] = array( $value , $operation ,$fieldValue);

        }elseif($value == 'payment_method_code'){

          if($key != 0 && $request->logic[$key -1 ] == 'or' )
            $paymentMethod_or[] = array( $value , $operation ,$fieldValue);
          else
            $paymentMethod_q[] = array( $value , $operation ,$fieldValue);

        }else{                
          $fieldValue = $request->fieldValue[$key];
          $operation = $request->operation[$key];
        }

        if($value != 'vendor_name' && $value != 'building_name' && $value != 'payment_method_code' &&  (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
         if($key != 0 && $request->logic[$key -1 ] == 'or' )
           $closure_or[] = array( $value , $operation ,$fieldValue);
         else
           $closure[] = array( $value , $operation ,$fieldValue);
       }

     }


   }


   if($request->ajax != true){
     if(count($vendor_or) == 0 && count($building_or) == 0 && count($paymentMethod_or) == 0 && count($vendor_q) == 0 && count($closure) == 0 &&  count($building_q) == 0 &&  count($paymentMethod_q) == 0 )
      $closure[] = array( 'id' , '=' ,0);
  }

}

}

$amc_contract_no = (isset($request->amc_contract_no)) ? $request->amc_contract_no : null;
$amc_contract_period_from = (isset($request->amc_contract_period_from)) ? $request->amc_contract_period_from : null;
$amc_contract_period_to = (isset($request->amc_contract_period_to)) ? $request->amc_contract_period_to : null;

$vendor_id = (isset($request->vendor_id)) ? $request->vendor_id : null;    
$building_id = (isset($request->building_id)) ? $request->building_id : null;    
$payment_method_id = (isset($request->payment_method_id)) ? $request->payment_method_id : null;    

$qiuck_search = array($amc_contract_no, $vendor_id , $building_id, $payment_method_id,$amc_contract_period_from,$amc_contract_period_to);
if($request->ajax != true){
  //$qiuck_search = array();
}
$result = array(
  $closure,
  $closure_or,      
  $vendor_q, $vendor_or, $building_q,$building_or,$paymentMethod_q,$paymentMethod_or,$qiuck_search ); 
   //dd($vendor_q);
return $result;    


}
  //Advance search
public function contractSearch(Request $request,$result = array())
{
  $name = Route::currentRouteName();
  $enquiry_fields = [
  'amc_contract_no' => 'Contract No',
  'vendor_name' => 'Contractor',
  'building_name' => 'Building',
  'payment_method_code' => 'Frequency',
  ];


  $operations = [
  'ilike' => ' Is Equal To '  ,
  '!=' => ' Is Not Equal To '  ,
  '>' => ' Is Greater Than '  ,
  '>=' => ' Is Greater Than Or Equal To '  ,
  '<' => ' Is Less Than '  ,
  '<=' => ' Is Less Than Or Equal To'  ,
  'ilike%...%' => ' Like%...% ',
  ];
  $result = array();

  if(isset($request)){

    $amcContract =   new AmcContractController;       
    $result =     $amcContract->amcContractSearch($request);   
    $request->flash();
  }

  $amcContracts = AmcContract::closure($result)->sortable()->paginate(10);

  if(isset($request->route))
    $route   =  $request->route;



  if(isset($request->ajax))
    return view('maintenance::Amc.amc_contract_list_ajax',compact('amcContracts','request','route'));

  return view('maintenance::Amc.amc_contract_list',compact('amcContracts','request','enquiry_fields','operations','name'));
}
 /**
    *
    * contractor Autocomplete
    *
    **/
 public function contractorAutocompleteCode(Request $request){

   $key = $request->term;

   $contractor =   Vendor::where('vendor_status',1)->where('vendor_type_id',1)->where('vendor_name', 'ILIKE', '%'.$key.'%')
   ->select('id AS ids',DB::raw("CONCAT(vendor_name,'-',vendor_code) as value"))
   ->get();

   return $contractor ;


 } 

 public function cancelContract(Request $request)
 {

  $amc_contract_id = $request->input('amc_contract_id');
  $amc_schedule_id = $request->input('amc_schedule_id');
     
  
  AmcContract::where('id',$amc_contract_id)
  ->update([
    'amc_contract_status' => 1, 
    'updated_by' => \Auth::user()->id
    ]);

  if($amc_schedule_id !='')
  {
    
    AmcTask::where('amc_schedule_id',$amc_schedule_id)
    ->update([
     'amc_schedule_task_status' =>1,
     'updated_by' => \Auth::user()->id
     ]);
    AmcSchedule::where('amc_contract_id',$amc_contract_id)
      ->update(['amc_schedule_status' => 2,
      'updated_by' => \Auth::user()->id
    ]);
      $building_id = AmcSchedule::where('id',$amc_schedule_id)->first()->building_id;
      $typeId = AmcScheduleAmenity::where('amc_schedule_id',$amc_schedule_id)->pluck('amenities_type_id');
      //update Building aminity Contract 
      BuildingAmentity::whereIn('amentity_type_id',$typeId)->where('landlord_building_id',$building_id)->update(['amc_contract_no'=>null]);
  }else{
    $building_id = AmcContract::where('id',$amc_contract_id)->first()->building_id;
      $typeId = AmcContractAmenities::where('amc_contract_id',$amc_contract_id)->pluck('amenities_type_id');
      //update Building aminity Contract 
      BuildingAmentity::whereIn('amentity_type_id',$typeId)->where('landlord_building_id',$building_id)->update(['amc_contract_no'=>null]);
  }

  session()->flash('success', 'AMC Contract Cancelled Successfully');
  return redirect()->route('amcContract.index');
  
}
   /*
    * Enquiry Search Form
    * 
    *
    */
    public function enquiryFilter(){


      $enquiry_fields = [
      'amc_contract_no' => 'Contract No',
      'vendor_name' => 'Contractor',
      'building_name' => 'Building',
      'payment_method_code' => 'Frequency',
      ];

      
      $operations = [
      'ilike' => ' Is Equal To '  ,
      '!=' => ' Is Not Equal To '  ,
      '>' => ' Is Greater Than '  ,
      '>=' => ' Is Greater Than Or Equal To '  ,
      '<' => ' Is Less Than '  ,
      '<=' => ' Is Less Than Or Equal To'  ,
      'ilike%...%' => ' Like%...% ',
      ];

     return view('maintenance::Amc.contract_filter',compact('enquiry_fields','operations'));
      

    }
}