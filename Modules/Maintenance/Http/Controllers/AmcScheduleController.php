<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Masters\Entities\PaymentMethod;
use Modules\Maintenance\Entities\AmcContract;
use Modules\Maintenance\Entities\AmcContractAmenities;
use Modules\Maintenance\Entities\AmcSchedule;
use Modules\Maintenance\Entities\AmcScheduleAmenity;
use Modules\Maintenance\Entities\AmcTask;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\AmentityType;
use Modules\Masters\Entities\BuildingAmentity;
use Modules\Masters\Entities\Vendor;
use App\User;
use DB;
use Session;
use URL;
use Route;


class AmcScheduleController extends Controller
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
    public function index(Request $request,$result = array())
    {
      session()->forget('oldBuilding');

     $name = Route::currentRouteName();

     $enquiry_fields = [
     'amcContract__amc_contract_no' => 'Contract No',
     'vendor__vendor_name' => 'Contractor/Technician',
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
    

    $amcSchedules = AmcSchedule::filter($request)->sortable()->paginate($this->noOfRecord);
     
    $route   =  $request->url();

    if(isset($request->ajax))
      return view('maintenance::Amc.amc_schedule_list_ajax',compact('amcSchedules','request','route'));

    return view('maintenance::Amc.amc_schedule_list',compact('amcSchedules','request','enquiry_fields','operations','name'));
      //$amcSchedules = AmcSchedule::paginate(20);
      //dd($amcSchedules);
      //return view('maintenance::Amc.amc_schedule_list',compact('amcSchedules'));
    /* return view('maintenance::Amc.amc_schedule_list');*/
  }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      session()->forget('oldBuilding');
           
      $paymentMethods = PaymentMethod::active()->get(); 
      $technicians = User::role('technician')->where('user_type_status',TRUE)->get();
     return view('maintenance::Amc.add_amc_schedule',compact('paymentMethods','technicians'));
   }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
      //dd(count($request['amentity_types_idd']));

      if($request['contract_type']=='technician'){
        $this->validate($request, [                   
          'building_text_id'   => 'required',
          'amc_schedule_period_from_text'   => 'required|date',
          'amc_schedule_period_to_text'   => 'required|date',
          'payment_method_id_text' => 'required', 
          'amc_schedule_description_text'   => 'required', 
          ]);
        $user_id = $request['vendor_name'];  
        $user = User::findOrFail($user_id);
        $employee = $user->employee()->first();
      
        $no_of_times = AmcSchedule::getFrequencyTimes($request['frequency_type'],$request['no_days']);
//dd($request['amc_schedule_period_from_text']);
        //Amc Schedule Insert-Technician
        $amcSchedule = AmcSchedule::create([              
          'user_id' => $user_id,
          'building_id' => $request['building_text_id'],
          'unit_id' => $request['unit_text_id'],
          'amc_schedule_period_from' => $request['amc_schedule_period_from_text'],
          'amc_schedule_period_to' => $request['amc_schedule_period_to_text'],
          'payment_method_id' => $request['payment_method_id_text'],
          'amc_schedule_description' => $request['amc_schedule_description_text'],
          'amc_contract_type'=> 'in-house',
          'amc_contractor_engineer'=>$employee->employee_name,
          'created_by' => \Auth::user()->id
          ]); 
        //Amc Schedule Amenities Insert-Technician
        if(isset($request['amentity_types_tech_id'])){
        for($i =0; $i<count($request['amentity_types_tech_id']);$i++){
          $amc_schedule_amenities = AmcScheduleAmenity::create([
            'amc_schedule_id'=>$amcSchedule->id,
            'amenities_type_id' =>$request['amentity_types_tech_id'][$i],
            ]);
        }
      }
      //Amc Task  Insert-Technician
      if(isset($request['amentity_types_idd'])){
        for($j =0; $j<count($request['amentity_types_idd']);$j++){
          $amc_schedule_task = AmcTask::create([
            'amc_schedule_id'=>$amcSchedule->id,
            'amenities_type_id' =>$request['amentity_types_idd'][$j],
            'amc_schedule_from_date' =>$request['amc_schedule_from_date'][$j],
            'amc_schedule_to_date' =>$request['amc_schedule_to_date'][$j],
            'building_id'=>$request['building_text_id'],
            'created_by' => \Auth::user()->id
            ]);
        }
      }
     }
      elseif($request['contract_type']=='sub_contractor'){
       $this->validate($request, [                   
        'building_id'   => 'required',
        'amc_schedule_period_from'   => 'required|date',
        'amc_schedule_period_to'   => 'required|date',
        'frequency' => 'required', 
        'amc_schedule_description'   => 'required', 
        ]);
        $vendorId = $request['vendor_code'];
        $vendor = Vendor::findOrFail($vendorId);
        //Amc Schedule Insert-Sub Contractor
       $amcSchedule = AmcSchedule::create([              
        'amc_contract_id' => $request['amc_contract_num'],
        'building_id' => $request['building_id'],
        'unit_id' => $request['unit_id'],
        'vendor_id' => $request['vendor_code'],
        'amc_schedule_period_from' => $request['amc_schedule_period_from'],
        'amc_schedule_period_to' => $request['amc_schedule_period_to'],
        'payment_method_id' => $request['frequency'],
        'amc_schedule_description' => $request['amc_schedule_description'],
        'amc_contract_type'=> 'sub-contractor',
        'amc_contractor_engineer'=>$vendor->vendor_name,
        'created_by' => \Auth::user()->id
        ]); 
        //Amc Schedule Amenities Insert-Sub Contractor
       $amc_contract_no = AmcContract::where('id',$amcSchedule->amc_contract_id)->first()->amc_contract_no;
       $AmcContractAmenities = AmcContractAmenities::where('amc_contract_id',$request['amc_contract_num'])->get();
       foreach ($AmcContractAmenities as  $Amenities) {
        $amc_schedule_amenities = AmcScheduleAmenity::create([
          'amc_schedule_id'=>$amcSchedule->id,
          'amenities_type_id' =>$Amenities->amenities_type_id,
          ]);
        BuildingAmentity::where('amentity_type_id',$Amenities->amenities_type_id)->where('landlord_building_id',$request['building_id'])->update(['amc_contract_no'=>$amc_contract_no]);
      }



        //Amc Schedule Amenities Insert-Subcontractor
      for($j =0; $j<count($request['amentity_types_idd_sub']);$j++){
        $amc_schedule_task = AmcTask::create([
          'amc_schedule_id'=>$amcSchedule->id,
          'amenities_type_id' =>$request['amentity_types_idd_sub'][$j],
          'amc_schedule_from_date' =>$request['amc_schedule_period_from_text_sub'][$j],
          'amc_schedule_to_date' =>$request['amc_schedule_period_to_text_sub'][$j],
          'amc_contract_id' => $request['amc_contract_num'],
          'building_id' => $request['building_id'],
          'created_by' => \Auth::user()->id
          ]);
      }
    }
    session()->flash('success', 'AMC Schedule Created Successfully');
    return redirect()->route('amcSchedule.index');

  }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($amc_schedule_id,Request $request,$result = array())
    {
      $amcSchedule =  AmcSchedule::where('id',$amc_schedule_id)->first();
      //$amcScheduleAmenities = AmcScheduleAmenity::where('amc_schedule_id',$amcSchedule->id)->paginate(10);
      $amcScheduleTasks = AmcTask::where('amc_schedule_id',$amcSchedule->id)->paginate(10);
      return view('maintenance::Amc.amc_schedule_view',compact('amcSchedule','amcScheduleTasks'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($amc_schedule_id)
    {
session()->forget('oldBuilding');
      $paymentMethods = PaymentMethod::active()->get(); 
      $technicians = User::role('technician')->where('user_type_status',TRUE)->get();
      $amcSchedule =  AmcSchedule::where('id',$amc_schedule_id)->first();
      $amcScheduleAmenities = AmcScheduleAmenity::where('amc_schedule_id',$amcSchedule->id)->paginate(10);
      $amcTasks = AmcTask::where('amc_schedule_id',$amcSchedule->id)->paginate(10);
      $fromDate = strtotime($amcSchedule->amc_schedule_period_from);
      $toDate = strtotime($amcSchedule->amc_schedule_period_to);
      $datediff = $toDate - $fromDate; 
      $days = intval($datediff/(60*60*24));
      $units = Unit::where('building_id',$amcSchedule->building_id)->get();
      
      if(count($amcSchedule->taskStatus) == 0){
        $amcSchedule->taskStatus = null;
      }
      Session::put('oldBuilding',$amcSchedule->building_id);
      return view('maintenance::Amc.add_amc_schedule',compact('amcSchedule','amcScheduleAmenities','amcTasks','paymentMethods','technicians' ,'days','units'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,AmcSchedule $amcSchedule)
    {
      
      if($request['contract_types']=='technician'){

        $user_id = $request['vendor_name'];  
        $user = User::findOrFail($user_id);
        $employee = $user->employee()->first();

        $amcSchedule->update([              
          'user_id' => $request['vendor_name'],
          'building_id' => $request['building_text_id'],
          'unit_id' => $request['unit_text_id'],
          'amc_schedule_period_from' => $request['amc_schedule_period_from_text'],
          'amc_schedule_period_to' => $request['amc_schedule_period_to_text'],
          'payment_method_id' => $request['payment_method_id_text'],
          'amc_schedule_description' => $request['amc_schedule_description_text'],
          'amc_contract_type'=> 'in-house',
          'amc_contractor_engineer'=>$employee->employee_name,
          'updated_by' => \Auth::user()->id
          ]);
        AmcScheduleAmenity::where('amc_schedule_id',$amcSchedule->id)->delete();
        AmcTask::where('amc_schedule_id',$amcSchedule->id)->delete();

       // dd($amcSchedule->id);
        for($i =0; $i<count($request['amentity_types_tech_id']);$i++){
          $amc_schedule_amenities = AmcScheduleAmenity::create([
            'amc_schedule_id'=>$amcSchedule->id,
            'amenities_type_id' =>$request['amentity_types_tech_id'][$i],
            ]);
        }
      //Amc Task  Insert-Technician
        for($j =0; $j < count($request['amentity_types_idd']);$j++){
          $amc_schedule_task = AmcTask::create([
            'amc_schedule_id'=>$amcSchedule->id,
            'amenities_type_id' =>$request['amentity_types_idd'][$j],
            'amc_schedule_from_date' =>$request['amc_schedule_from_date'][$j],
            'amc_schedule_to_date' =>$request['amc_schedule_to_date'][$j],
            'building_id'=>$request['building_text_id'],
            'created_by' => \Auth::user()->id
            ]);
        }

      }
      elseif($request['contract_types']=='sub_contractor'){
        $vendorId = $request['vendor_code'];
        $vendor = Vendor::findOrFail($vendorId);

        $amcSchedule->update([              
          'amc_contract_id' => $request['amc_contract_num'],
          'building_id' => $request['building_id'],
          'unit_id' => $request['unit_id'],
          'vendor_id' => $request['vendor_code'],
          'amc_schedule_period_from' => $request['amc_schedule_period_from'],
          'amc_schedule_period_to' => $request['amc_schedule_period_to'],
          'payment_method_id' => $request['frequency'],
          'amc_schedule_description' => $request['amc_schedule_description'],
          'amc_contract_type'=> 'sub-contractor',
          'amc_contractor_engineer'=>$vendor->vendor_name,
          'updated_by' => \Auth::user()->id
          ]);
        AmcTask::where('amc_schedule_id',$amcSchedule->id)->delete();
       //Amc Schedule Amenities Insert-Subcontractor
        for($j =0; $j<count($request['amentity_types_idd_sub']);$j++){
          $amc_schedule_task = AmcTask::create([
            'amc_schedule_id'=>$amcSchedule->id,
            'amenities_type_id' =>$request['amentity_types_idd_sub'][$j],
            'amc_schedule_from_date' =>$request['amc_schedule_period_from_text_sub'][$j],
            'amc_schedule_to_date' =>$request['amc_schedule_period_to_text_sub'][$j],
            'amc_contract_id' => $request['amc_contract_num'],
            'building_id' => $request['building_id'],
            'created_by' => \Auth::user()->id,
            'updated_by' => \Auth::user()->id,
            ]);
        }

      }
      session()->forget('oldBuilding');
      session()->flash('success', 'AMC Schedule Updated Successfully');
      return redirect()->route('amcSchedule.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
      $nowUrl = url()->previous();
      AmcScheduleAmenity::where('id',$id)->delete();
      session()->flash('success', 'AMC Schedule Deleted Successfully');
      return redirect($nowUrl);
    }
      /**
    *
    * Contract No Autocomplete
    *
    **/
      public function amcContractNoAutocompleteCode(Request $request){

       $key = $request->term;
       $scheduleContract = AmcSchedule::whereNull('unit_id')->whereNotNull('amc_contract_id')->where('amc_schedule_status',0)->pluck('amc_contract_id');
      
       $amccontractno =   AmcContract::whereNotIn('id',$scheduleContract)->where('amc_contract_status',0)->where('amc_contract_no', 'ILIKE', '%'.$key.'%')
       ->select('id AS ids','building_id AS building','vendor_id AS vendor','payment_method_id AS frequency','amc_contract_period_from AS from','amc_contract_period_to AS to','amc_contract_no as value')
       ->get();


       return $amccontractno ;


     } 
     /*
    *
    * get contractor and building by contract no
    *
    *
    */
     public function amcDetailsByContractNo(Request $request){

      $contract_no = $request->contract_no; 
      $contract =  AmcContract::where('id',$contract_no)->first();
      $building = $contract->building;
      $vendor = $contract->vendor;
      $paymentMethod = $contract->paymentMethod;
      return json_encode(array($contract,$building,$vendor,$paymentMethod));

    }
     /*
    *
    *
    * Building By Unit
    *
    */
     public function unitByBuilding(Request $request){

       $id = $request->input('id'); 
       $units = Unit::where('building_id',$id)->where('unit_status',1)->get();
       return json_encode($units);

     }

      /**
    *
    * Building Autocomplete-Technician
    *
    **/
      public function amcBuildingAutocomplete(Request $request){

        $buil = array();
       $key = $request->term;
       $building =   Building::Active()->where('building_maintenance_info',0)->where('building_name', 'ILIKE', '%'.$key.'%')
       ->select('id AS ids',DB::raw("CONCAT(building_name,'-',building_code) as value"),'management_id')->get();
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
     /*
    *
    *
    * amenities by Contract NO
    *
    */
     public function amenitiesByContractNo(Request $request){

       $id = $request->input('id'); 
       $amenity = AmcContractAmenities::with('amenityType')->where('amc_contract_id',$id)->get(); 
        //dd($amenity );    
       return json_encode($amenity);

     }
     //Advance search
     public function amcScheduleSearch(Request $request){

      $closure = array();
      $closure_or = array();
      
      $vendor_q = array();
      $vendor_or = array();
      $building_q = array();
      $building_or = array();
      $paymentMethod_q = array();
      $paymentMethod_or = array();  
      $contract_q = array();  
      $contract_or = array();  
      
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
          }elseif($value == 'vendor_name'){

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

          }elseif($value == 'amc_contract_no'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $contract_or[] = array( $value , $operation ,$fieldValue);
            else
              $contract_q[] = array( $value , $operation ,$fieldValue);

          }else{                
            $fieldValue = $request->fieldValue[$key];
            $operation = $request->operation[$key];
          }

          if($value != 'vendor_name' && $value != 'building_name' && $value != 'payment_method_code' && $value != 'amc_contract_no' && (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
           if($key != 0 && $request->logic[$key -1 ] == 'or' )
             $closure_or[] = array( $value , $operation ,$fieldValue);
           else
             $closure[] = array( $value , $operation ,$fieldValue);
         }

       }


     }


     if($request->ajax != true){
       if(count($contract_or) == 0 &&count($vendor_or) == 0 && count($building_or) == 0 && count($paymentMethod_or) == 0 && count($contract_q) == 0 && count($vendor_q) == 0 && count($closure) == 0 &&  count($building_q) == 0 &&  count($paymentMethod_q) == 0 )
        $closure[] = array( 'id' , '=' ,0);
    }

  }

}

$amc_contract_id = (isset($request->amc_contract_id)) ? $request->amc_contract_id : null;
$amc_schedule_period_from = (isset($request->amc_schedule_period_from)) ? $request->amc_schedule_period_from : null;
$amc_schedule_period_to = (isset($request->amc_schedule_period_to)) ? $request->amc_schedule_period_to : null;

$vendor_id = (isset($request->vendor_id)) ? $request->vendor_id : null;    
$building_id = (isset($request->building_id)) ? $request->building_id : null;    
$payment_method_id = (isset($request->payment_method_id)) ? $request->payment_method_id : null;    

$qiuck_search = array($amc_contract_id, $vendor_id , $building_id, $payment_method_id,$amc_schedule_period_from,$amc_schedule_period_to);
$result = array(
  $closure,
  $closure_or,      
  $vendor_q, $vendor_or,$contract_q, $contract_or, $building_q,$building_or,$paymentMethod_q,$paymentMethod_or,$qiuck_search ); 
   //dd($vendor_q);
return $result;    


}
    //Advance search
public function scheduleSearch(Request $request,$result = array())
{
  $name = Route::currentRouteName();
  $enquiry_fields = [
  'amc_contract_no' => 'Contract No',
  'vendor_name' => 'Contractor/Technician',
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

    $amcSchedule =   new AmcScheduleController;       
    $result =     $amcSchedule->amcScheduleSearch($request);   

  }

  $amcSchedules = AmcSchedule::closure($result)->sortable()->paginate(10);
  if(isset($request->route))
    $route   =  $request->route;



  if(isset($request->ajax))
    return view('maintenance::Amc.amc_schedule_list_ajax',compact('amcSchedules','request','route'));

  return view('maintenance::Amc.amc_schedule_list',compact('amcSchedules','request','enquiry_fields','operations','name'));
}
  // process amc building amenity
public function processAmcAmenity(Request $request){

  $endDates =array();
  $amenity_types =array();
  $effectiveDates =array();
  $no = $request->no;
  $amentity_types_id = $request->amentity_types_id;
  $amc_contract_num = $request->amc_contract_num;

  $amc_schedule_period_from_text = $request->amc_schedule_period_from_text;
    $from = $request->amc_schedule_period_from_text;//from
  $amc_schedule_period_to_text = $request->amc_schedule_period_to_text;
  $to = $request->amc_schedule_period_to_text;//to
  $frequency_type = $request->frequency_type;
  $no_days = $request->no_days; 
  //dd($amentity_types_id);
  $amenitys = AmcContractAmenities::where('amc_contract_id',$amc_contract_num)->get();  //dd($amenity);
  foreach ($amenitys as $key => $amenity) {
    $amenity_types [] = $amenity->amenityType->amentity_types_name; 
  }//dd($amenity_types);
  //$amenity_name = $amenity->amentity_types_name;
  $no_of_times = AmcSchedule::getFrequencyTimes($frequency_type,$no_days);
  $date_diff=intval(floor($no_days/$no_of_times[0]));
  $Numtimes = $no_of_times[0];
  
  $effectiveDates[] = $amc_schedule_period_from_text; 
  for($j =1; $j<=$Numtimes-1;$j++){

    $effectiveDate = date('Y-m-d', strtotime("+".$no_of_times[1]." months", strtotime($amc_schedule_period_from_text)));
    $effectiveDates[] = $effectiveDate; 
    $amc_schedule_period_from_text = $effectiveDate;
    $time=strtotime($effectiveDate);
    $month=date("F",$time);
    //$first_day_this_month[] = date('Y-t-m', strtotime($effectiveDate));
    $endDates[] = date('Y-m-d', strtotime('-1 day', strtotime($effectiveDate)));

    

  }
  $endDates[] = $amc_schedule_period_to_text; 

  return view('maintenance::Amc.amc_process_amenity',compact('no','amentity_types_id','amenitys','amc_schedule_period_from_text','amc_schedule_period_to_text','Numtimes','effectiveDates','endDates','from','to'));
}
/*
*
*
* Process Aminity Technicaian
*
*/
public function processAmcAmenityTechnician(Request $request){

  $endDates =array();
  $amenity_types =array();
  $effectiveDates =array();
  $no = $request->no;
  $amentity_types_id = $request->amentity_types_id;
  $amc_contract_num = $request->amc_contract_num;

  $amc_schedule_period_from_text = $request->amc_schedule_period_from_text;
  $from = $request->amc_schedule_period_from_text;//from
  $amc_schedule_period_to_text = $request->amc_schedule_period_to_text;
   $to = $request->amc_schedule_period_to_text;//to
  $frequency_type = $request->frequency_type;
  $no_days = $request->no_days;
  //dd($amentity_types_id);
  $amenitys = AmentityType::whereIn('id',$amentity_types_id)->get();  //
  
  //$amenity_name = $amenity->amentity_types_name;
  $no_of_times = AmcSchedule::getFrequencyTimes($frequency_type,$no_days);
  $date_diff=intval(floor($no_days/$no_of_times[0]));
  $Numtimes = $no_of_times[0];
  
  $effectiveDates[] = $amc_schedule_period_from_text; 
  for($j =1; $j<=$Numtimes-1;$j++){

    $effectiveDate = date('Y-m-d', strtotime("+".$no_of_times[1]." months", strtotime($amc_schedule_period_from_text)));
    $effectiveDates[] = $effectiveDate;

    $amc_schedule_period_from_text = $effectiveDate;
    $time=strtotime($effectiveDate);
    $month=date("F",$time);
    //$first_day_this_month[] = date('Y-t-m', strtotime($effectiveDate));
    $endDates[] = date('Y-m-d', strtotime('-1 day', strtotime($effectiveDate)));

    

  }
  $endDates[] = $amc_schedule_period_to_text; 
  return view('maintenance::Amc.amc_process_amenity_technician',compact('no','amentity_types_id','amenitys','amc_schedule_period_from_text','amc_schedule_period_to_text','Numtimes','effectiveDates','endDates','from','to'));
}
/*
    *
    *
    * Schedule Amenity Edit 
    *
    */
public function amenityEdit(Request $request){

  $amentity_types_id = $request->amentity_types_id;
  $amc_schedule_period_from = $request->amc_schedule_period_from;
  $amc_schedule_period_to = $request->amc_schedule_period_to;
  $from = $request->from;
  $to = $request->to;
  $no = $request->no;
  $tr_num = $request->tr_num;
      //$amenity = AmentityType::get();
  $amenity = AmentityType::where('id',$amentity_types_id)->first(); 
      //$works_code = $amenity->works_code;
  return view('maintenance::Amc.process_amenity_edit',compact('amenity','amentity_types_id','amc_schedule_period_from','amc_schedule_period_to','no','tr_num','from','to'));
}
/*
    *
    *
    * Schedule Amenity Edit-Subcontractor 
    *
    */
public function amenityEditSub(Request $request){

  $amentity_types_id = $request->amentity_types_id;
  $amc_schedule_period_from = $request->amc_schedule_period_from;
  $amc_schedule_period_to = $request->amc_schedule_period_to;
  $from = $request->from;
  $to = $request->to;
  $no = $request->no;
  $tr_num = $request->tr_num;
      //$amenity = AmentityType::get();
  $amenity = AmentityType::where('id',$amentity_types_id)->first(); 
      //$works_code = $amenity->works_code;
  return view('maintenance::Amc.process_amenity_edit_sub',compact('amenity','amentity_types_id','amc_schedule_period_from','amc_schedule_period_to','no','tr_num','from','to'));
}
    /*
    *
    *
    * Schedule Amenity updated
    *
    */
    public function updateScheduleAmenity(Request $request){
      $result = array();
      $result['amentity_types_id'] = $request->amentity_types_id;
      $result['amc_schedule_period_from_text'] = $request->amc_schedule_period_from_text;
      $result['amc_schedule_period_to_text'] = $request->amc_schedule_period_to_text;
      $amenity = AmentityType::where('id',$request->amentity_types_id)->first(); 
      $result['amentity_types_name'] = $amenity->amentity_types_name;
      $result['no'] = $request->no;
      $result['from'] = $request->from;
      $result['to'] = $request->to;
      return $result;
    }
    /*
    *
    *
    * Schedule Amenity Edit 
    *
    */
    public function addTask(Request $request){

      $amentity_types_id = $request->amentity_types_id;
      $amenities =  AmentityType::whereIn('id',$amentity_types_id)->get(); 
      $rowcCount = $request->rowcCount;
      //$building = BuildingAmentity::where('landlord_building_id',$building_text_id)->get(); 
      return view('maintenance::Amc.add_amenity',compact('amenities','rowcCount'));
    }
    /*
    *
    *
    * Schedule Amenity Edit-subcontractor 
    *
    */
    public function addTaskSubContractor(Request $request){

      $amc_contract_num = $request->amc_contract_num;
      $rowcCount = $request->rowcCount;
      $amenities = AmcContractAmenities::where('amc_contract_id',$amc_contract_num)->get(); 
      return view('maintenance::Amc.add_amenity_subcontractor',compact('amenities','rowcCount'));
    }
        /*
    *
    *
    * Schedule Amenity Add-for schedule view 
    *
    */
        public function addTaskAmenity(Request $request){
          $nowUrl = url()->previous();
          Session::put('nowUrl', $nowUrl);
          $building_text_id = $request->building_text_id;
          $amc_schedule_id = $request->amc_schedule_id;
          $amc_schedule_period_from = $request->amc_schedule_period_from;
          $amc_schedule_period_to = $request->amc_schedule_period_to;
          $amenities = AmcScheduleAmenity::where('amc_schedule_id',$amc_schedule_id)->get();
          $building = BuildingAmentity::where('landlord_building_id',$building_text_id)->get(); 
          return view('maintenance::Amc.add_amenity_model',compact('amenities','amc_schedule_id','nowUrl','amc_schedule_period_from','amc_schedule_period_to'));
        } 
     /*
    *
    * Schedule Amenity edit-for schedule view 
    *
    *
    */
     public function editTaskAmenity(Request $request)
     {
      $amc_schedule_id = $request->input('amc_schedule_id');
      $amc_task_id = $request->input('amc_task_id');
      $amenities_type_id = $request->input('amenities_type_id');
       $amc_schedule_period_from = $request->amc_schedule_period_from;
          $amc_schedule_period_to = $request->amc_schedule_period_to;
     // $building_text_id = $request->building_text_id;
      $nowUrl = url()->previous();
      Session::put('nowUrl', $nowUrl);
      //$building = BuildingAmentity::where('landlord_building_id',$building_text_id)->get(); 
      $amcTask=AmcTask::where('id',$amc_task_id)->first();
      //dd($amcTask);
      return view('maintenance::Amc.update_amenity_model',compact('amcTask','nowUrl','amenities_type_id','amc_schedule_id','amc_schedule_period_from','amc_schedule_period_to'));

    }
    /*
    *
    * Cancelling Schedule 
    *
    *
    */
    public function cancelSchedule(Request $request)
    {
      $amc_schedule_id = $request->input('amc_schedule_id');
      $AmcSchedule = AmcSchedule::where('id',$amc_schedule_id)->first();
      $building_id = $AmcSchedule->building_id;
      $typeId = AmcScheduleAmenity::where('amc_schedule_id',$amc_schedule_id)->pluck('amenities_type_id');
      //update Building aminity Contract 
      BuildingAmentity::whereIn('amentity_type_id',$typeId)->where('landlord_building_id',$building_id)->update(['amc_contract_no'=>null]);
      
      AmcSchedule::where('id',$amc_schedule_id)
      ->update(['amc_schedule_status' => 2,
        'updated_by' => \Auth::user()->id
        ]);
      AmcTask::where('amc_schedule_id',$amc_schedule_id)
      ->update(['amc_schedule_task_status' => 1,
        'updated_by' => \Auth::user()->id
        ]);
      session()->flash('success', 'AMC Schedule Cancelled Successfully');
      return redirect()->route('amcSchedule.index');
    }
    /*
    *
    * Add Extra Amenity Based Subcontractor
    *
    *
    */
    public function addAmenitySubcontractorForm(Request $request)
    {
      $amentity_types_id = $request->amentity_types_id;
      $no = $request->no+1;
      $amc_schedule_period_from_text = $request->amc_schedule_period_from_text;
      $amc_schedule_period_to_text = $request->amc_schedule_period_to_text; 
      $from = $request->from; 
      $to = $request->to; 
      $amenity = AmcContractAmenities::where('amenities_type_id',$amentity_types_id)->first();
      return view('maintenance::Amc.add_amc_task_subcontractor',compact('no','amentity_types_id','amenity','amc_schedule_period_from_text','amc_schedule_period_to_text','from','to'));
    }
    /*
    *
    * Add Extra Amenity Based Technician
    *
    *
    */
    public function addAmenityTechnicianForm(Request $request)
    {
      $amentity_types_id = $request->amentity_types_id;
      $no = $request->no+1;

      $amc_schedule_period_from_text = $request->amc_schedule_period_from_text; 
      $amc_schedule_period_to_text =  $request->amc_schedule_period_to_text;
      $from = $request->from; 
      $to = $request->to;
      $amenity = AmentityType::where('id',$amentity_types_id)->first();
      return view('maintenance::Amc.add_amc_task_technician',compact('no','amentity_types_id','amenity','amc_schedule_period_from_text','amc_schedule_period_to_text','from','to'));
    }
       /*
    * Enquiry Search Form
    * 
    *
    */
    public function enquiryFilter(){


      $enquiry_fields = [
     'amc_contract_no' => 'Contract No',
     'vendor_name' => 'Contractor/Technician',
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

     return view('maintenance::Amc.schedule_filter',compact('enquiry_fields','operations'));
      

    }
    //get building unit list
    public function getBuildingUnits(Request $request){
     $building_id = $request->building_id;
     $data['building_units']=Unit::where('building_id',$building_id)->get();
     return view('maintenance::Amc.building_unit_list',$data);


   }
   /*
   *
   * Unit Exist 
   *
   *
   */
   public function unitExist(Request $request){

    $amentity_types_id = $request->amentity_types_id;
    $building_id = $request->building_id;
    $unit_id = $request->unit_id;
    if(isset($unit_id)){
      $scheduled = AmcSchedule::where('building_id',$building_id)->where('amc_schedule_status',0)->where('unit_id',$unit_id)->pluck('id');
      $scheduledAmenity = AmcScheduleAmenity::whereIn('amc_schedule_id',$scheduled)->where('amenities_type_id',$amentity_types_id)->get();
      $scheduledCount = count($scheduledAmenity);
    }else{
      $scheduledCount = 0;
    }
    
    return $scheduledCount;
    
  }

  }