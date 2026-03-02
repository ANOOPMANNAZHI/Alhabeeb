<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Maintenance\Entities\AmcTask;
use Modules\Maintenance\Entities\AmcSchedule;
use Modules\Maintenance\Entities\AmcScheduleAmenity;
use Modules\Masters\Entities\BuildingAmentity;
use DB;
use Session;
use URL;
use Route;
use App\User;
use Modules\Maintenance\Events\AmcTaskReminder;
use Spatie\Activitylog\Models\Activity;

class AmcTaskController extends Controller
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
      $name = Route::currentRouteName();

      $enquiry_fields = [
      'amcContract__amc_contract_no' => 'Contract No',
      'vendor__vendor_name' => 'Contractor/Technician',
      'amenityType__amentity_types_name' => 'Amenity',
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
      
      $user = \Auth::user();
      $roles = $user->getRoles();
      $rolesNames = $user->getRoleNames()->toArray();

      $amcTasks = AmcTask::whereHas('amcSchedule', function ($query)use($request,$user) {
          $query->filter($request)
                ->when(  ($user->hasRole('technician')), function($query){
                   $query->where('user_id','=', \Auth::user()->id);
                });        
      })->amenityType($request)
      ->where('amc_schedule_task_status',0)
      ->orderBy('amc_schedule_from_date','asc')
      ->sortable()->paginate($this->noOfRecord);
      

      $route   =  $request->url();

      if(isset($request->ajax))
        return view('maintenance::Amc.amc_task_list_ajax',compact('amcTasks','request','route'));

      return view('maintenance::Amc.amc_task_list',compact('amcTasks','request','enquiry_fields','operations','name'));
         /*$amcTasks = AmcTask::paginate(20);
         return view('maintenance::Amc.amc_task_list',compact('amcTasks'));*/
       }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      return view('maintenance::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
      $nowUrl = $request['url'];
      $amc_task = AmcTask::create([
        'amc_schedule_id'=>$request['amc_schedule_id'],
        'amenities_type_id' =>$request['amentity_types_id'],
        'amc_schedule_from_date' =>$request['amc_schedule_period_from_text'],
        'amc_schedule_to_date' =>$request['amc_schedule_period_to_text'],
        'created_by' => \Auth::user()->id
        ]);
      session()->flash('success',' Am Task Added Successfully');
      return redirect($nowUrl);
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($amc_task_id)
    {
      $amcTask =  AmcTask::where('id',$amc_task_id)->first();
      clearNotification('Modules\Maintenance\Notifications\AmcNotification',$amc_task_id);
      readNotification('Modules\Maintenance\Notifications\AmcNotification',$amc_task_id);
      return view('maintenance::Amc.amc_task_view',compact('amcTask'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(AmcTask $amcTask)
    {
      return view('maintenance::Amc.add_amenity_model',compact('amcTask'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,AmcTask $amcTask)
    {
      $nowUrl = $request['url'];
      $amcTask->update([
        'amenities_type_id' =>$request['amentity_types_id'],
        'amc_schedule_from_date' =>$request['amc_schedule_period_from_text'],
        'amc_schedule_to_date' =>$request['amc_schedule_period_to_text'],
        'updated_by' => \Auth::user()->id
        ]);
      session()->flash('success',' Update Successfully');
      return redirect($nowUrl);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(AmcTask $amcTask)
    {
      clearNotification('Modules\Maintenance\Notifications\AmcNotification',$amcTask->id);
      readNotification('Modules\Maintenance\Notifications\AmcNotification',$amcTask->id);
      $nowUrl = url()->previous();
      $amcTask->delete();
      session()->flash('success', 'AMC Task Deleted Successfully');
      return redirect($nowUrl);
    }
   //Advance search
    public function amcTaskSearch(Request $request){

      $closure = array();
      $closure_or = array();
      
      $contract_no_q = array();
      $contract_no_or = array();
      $vendor_q = array();
      $vendor_or = array();
      $amenities_q = array();
      $amenities_or = array(); 
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
          }elseif($value == 'amc_contract_no'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $contract_no_or[] = array( $value , $operation ,$fieldValue);
            else
              $contract_no_q[] = array( $value , $operation ,$fieldValue);

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

          }elseif($value == 'amentity_types_name'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $amenities_or[] = array( $value , $operation ,$fieldValue);
            else
              $amenities_q[] = array( $value , $operation ,$fieldValue);

          }else{                
            $fieldValue = $request->fieldValue[$key];
            $operation = $request->operation[$key];
          }

          if($value != 'amc_contract_no' && $value != 'vendor_name' && $value != 'building_name' && $value != 'payment_method_code' && $value != 'amentity_types_name'  &&  (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
           if($key != 0 && $request->logic[$key -1 ] == 'or' )
             $closure_or[] = array( $value , $operation ,$fieldValue);
           else
             $closure[] = array( $value , $operation ,$fieldValue);
         }
//dd($vendor_q);
       }


     }


     if($request->ajax != true){
       if(count($contract_no_or) == 0 && count($vendor_or) == 0 && count($building_or) == 0 && count($paymentMethod_or) == 0 && count($amenities_or) == 0  && count($contract_no_q) == 0 && count($vendor_q) == 0 && count($closure) == 0 &&  count($amenities_q) == 0 &&count($building_q) == 0 &&  count($paymentMethod_q) == 0)
        $closure[] = array( 'id' , '=' ,0);
    }

  }

}

$amc_schedule_id = (isset($request->amc_schedule_id)) ? $request->amc_schedule_id : null;
$amc_schedule_from_date = (isset($request->amc_schedule_from_date)) ? $request->amc_schedule_from_date : null;
$amc_schedule_to_date = (isset($request->amc_schedule_to_date)) ? $request->amc_schedule_to_date : null;

$vendor_id = (isset($request->vendor_id)) ? $request->vendor_id : null;    

$amenities_types_id = (isset($request->amenities_types_id)) ? $request->amenities_types_id : null;  
$building_id = (isset($request->building_id)) ? $request->building_id : null;  
$payment_method_id = (isset($request->payment_method_id)) ? $request->payment_method_id : null;  
$amc_schedule_task_status = (isset($request->amc_schedule_task_status)) ? $request->amc_schedule_task_status : null;  

$qiuck_search = array($amc_schedule_id, $vendor_id , $amenities_types_id, $building_id,$payment_method_id,$amc_schedule_from_date,$amc_schedule_to_date,$amc_schedule_task_status);
$result = array(
  $closure,
  $closure_or, 
  $contract_no_q,     
  $contract_no_or,
  $vendor_q, $vendor_or, $amenities_q,$amenities_or,$building_q,$building_or,$paymentMethod_q,$paymentMethod_or,$qiuck_search ); 
   //dd($vendor_q);
return $result;       
}
public function TaskSearch(Request $request,$result = array())
{
  $name = Route::currentRouteName();
  $enquiry_fields = [
  'amc_contract_no' => 'Contract No',
  'vendor_name' => 'Contractor/Technician',
  'amentity_types_name' => 'Amenity',
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

    $amcTask =   new AmcTaskController;       
    $result =     $amcTask->amcTaskSearch($request);   

  }

  $roles = \Auth::user()->getRoles();
  $rolesNames = \Auth::user()->getRoleNames()->toArray();
  $amcTasks = AmcTask::whereHas('amcSchedule', function ($query)use($rolesNames) {
    if (in_array('technician', $rolesNames) === true) {
      $query->where('user_id','=', \Auth::user()->id);
    }
  })->
  closure($result)->where('amc_schedule_task_status',0)->orderBy('amc_schedule_from_date','asc')->sortable()->paginate(10);
  if(isset($request->route))
    $route   =  $request->route;



  if(isset($request->ajax))
    return view('maintenance::Amc.amc_task_list_ajax',compact('amcTasks','request','route'));

  return view('maintenance::Amc.amc_task_list',compact('amcTasks','request','enquiry_fields','operations','name'));
         /*$amcTasks = AmcTask::paginate(20);
         return view('maintenance::Amc.amc_task_list',compact('amcTasks'));*/
       }
      /*
    *
    * Schedule Amenity edit-for schedule view 
    *
    *
    */
      public function closeTask(Request $request)
      {

        $amc_task_id = $request->input('amc_task_id');
        clearNotification('Modules\Maintenance\Notifications\AmcNotification',$amc_task_id);
        readNotification('Modules\Maintenance\Notifications\AmcNotification',$amc_task_id);
        $amcTask=AmcTask::find($amc_task_id);
        $nowUrl = url()->previous();
        Session::put('nowUrl', $nowUrl);
        return view('maintenance::Amc.task_review_model',compact('amc_task_id','nowUrl','amcTask'));

      }
    /*
    * add review-close task
    *
    *
    */
    public function addReview(Request $request)
    {
      $amc_task_id = $request->input('amc_task_id');
      $nowUrl = $request['url'];
      AmcTask::where('id',$amc_task_id)
      ->update([
       'amc_schedule_task_remarks' =>$request['amc_schedule_task_remarks'],
       'amc_schedule_task_status' =>1,
       'updated_by' => \Auth::user()->id
       ]);
      $amc_schedule_id = AmcTask::where('id',$amc_task_id)->first()->amc_schedule_id;
      $TaskCount = AmcTask::where('amc_schedule_id',$amc_schedule_id)->count();

      $count = AmcTask::where('amc_schedule_id',$amc_schedule_id)->where('amc_schedule_task_status',1)->count();
      
      if($TaskCount == $count){

        $building_id = AmcSchedule::where('id',$amc_schedule_id)->first()->building_id;

        $typeId = AmcScheduleAmenity::where('amc_schedule_id',$amc_schedule_id)->pluck('amenities_type_id');

        AmcSchedule::where('id',$amc_schedule_id)
        ->update(['amc_schedule_status' => 2,
          'updated_by' => \Auth::user()->id
          ]);
        //update Building aminity Contract 
        BuildingAmentity::whereIn('amentity_type_id',$typeId)->where('landlord_building_id',$building_id)->update(['amc_contract_no'=>null]);
      }
      session()->flash('success',' Amc Task Closed Successfully');
      return redirect($nowUrl);

    }
    /*
    *
    *
    * Task remainder
    *
    *
    */
    public function taskReminder(AmcTask $task)
    {
      $nowUrl = url()->previous();
      $users = $task->amcSchedule->user_id;     
      $users_notify =  \App\User::whereIn('id',[$users])->get();
      $username = $users_notify->first()->username;
      
      $task->text = " Task Reminder ";   
      $task->href = url('amcTask/'.$task->id);
      event(new AmcTaskReminder($task,$users_notify));       
      session()->flash('success', ' Notification Send To '.$username);
      return redirect($nowUrl);/*->route('amcTask.index')*/

    }
    /*
    *
    *
    * AMC Tack Cron Notification
    *
    *
    */
    public function amcTaskNotification()
    {
      $task = array();
      $currentDate = date('Y-m-d');
      $amcTask = AmcTask::whereDate('amc_schedule_to_date','=',$currentDate)->where('amc_schedule_task_status',0)->get();

      $users = User::role('maintenance_supervisor')->get(); 
      
      if(count($amcTask) > 0){
        foreach($amcTask as $task)
        {
          $task->text = " Task Overdue Today ";   
          $task->href = url('amcTask/'.$task->id);
          event(new AmcTaskReminder($task,$users)); 
        }      
         //session()->flash('success', ' Notification Send ');
		  activity('cron')
		  ->performedOn($amcTask->first())
		  ->causedBy(\Auth::user()->id)
		  ->withProperties($amcTask->first())
		  ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
	  }
     
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
    'amentity_types_name' => 'Amenity',
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

    return view('maintenance::Amc.task_filter',compact('enquiry_fields','operations'));
    

  }
    /*
    *
    *
    * checkTask
    *
    */
    public function checkTask(Request $request)
    {
      $task_id = $request->amc_task_id;
      $task_type = $request->amc_contract_type; // i - in-house, s-sub-contractor
      $task = AmcTask::where('id',$task_id)->first();
      $fromDate = $task->amc_schedule_from_date;
      $amenity = $task->amenities_type_id;
      $previousTask = AmcTask::where('amenities_type_id',$amenity)->whereDate('amc_schedule_from_date','<',$fromDate)->where('amc_schedule_task_status',0)->whereHas('amcSchedule', function ($query)use($task_type) {
            $query->where('amc_contract_type',$task_type);
        })->pluck('id');
      return $tasksCount = count($previousTask);
      
      
    }

  }



