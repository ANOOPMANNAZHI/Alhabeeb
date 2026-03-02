<?php

namespace Modules\Masters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Masters\Entities\AreBuildingAssign;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\PreferredBuilding;
use Modules\Masters\Entities\employee;
use App\User;
use DB;
use Session;
use URL;
use Route;

class AreBuildingAssignController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('permission:are_building_assign_edit', ['only' => ['edit','update']]); 
    $this->middleware('permission:are_building_assign_list', ['only' => ['index']]); 
    $this->noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
  }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request,$result = array())
    {

      $name = Route::currentRouteName();
      $enquiry_fields = [
      'username' => 'ARE',
      'building_name' => 'Building',
      ];

      
      $operations = [
      'ilike' => ' Is Equal To '  ,
      '!=' => ' Is Not Equal To '  ,
      'ilike%...%' => ' Like%...% ',
      ];
      $result = array();

      if(isset($request)){

        $areBuilding =   new AreBuildingAssignController;       
        $result =     $areBuilding->areBuildingAssignSearch($request); 
        $request->flash(); 

      }

      $areBuildingAssigns = AreBuildingAssign::whereHas('buildingNamesExist')
                                               ->closure($result)->sortable()
                                               ->paginate($this->noOfRecord);
//dd($areBuildingAssign);
      if(isset($request->route))
        $route   =  $request->route;

      if(isset($request->ajax))
        return view('masters::AreBuildingAssign.are_building_assign_list_ajax',compact('areBuildingAssigns','request','route'));

      return view('masters::AreBuildingAssign.are_building_assign_list',compact('areBuildingAssigns','request','enquiry_fields','operations','name'));


        /*$areBuildingAssign =  AreBuildingAssign::get();
        dd($areBuildingAssign);
        return view('masters::AreBuildingAssign.view',compact('areBuildingAssign'));*/
      }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      $assignBuilding=PreferredBuilding::where('assign_to',null)->get()->pluck('building_id');
     // dd($assignBuilding);
      $buildings = Building::whereNotIn('id',$assignBuilding)->where('building_status',1)->orderBy('building_name', 'ASC')->get();
      return view('masters::AreBuildingAssign.add_edit',compact('buildings'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {

     $this->validate($request, [
      'user_id' => 'required',                    
      'assign_from'   => 'required|date',
      'building_id'   => 'required',
      ]);
     $areBuildingAssign = AreBuildingAssign::create([              
      'user_id' => $request['user_id'],
      'assign_from' => $request['assign_from'],
      ]); 

     $request->building_id  = explode(',',$request->building_id);
     $areBuildingAssign->buildingNames()->sync($request->building_id); 

     for($i =0; $i<count($request->building_id);$i++){
      PreferredBuilding::where('are_building_id',$areBuildingAssign->id)->update(['assign_from'=>$request['assign_from']]);
    }


    session()->flash('success', 'Buildings Assigned Successfully');
    return redirect()->route('areBuildingAssign.index');
  }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
      $buildings = Building::where('building_status',1)->get();
      $areBuildingAssign =  AreBuildingAssign::where('id',$id)->first();
      //dd($areBuildingAssign);
      return view('masters::AreBuildingAssign.view',compact('areBuildingAssign','buildings'));
    }
    /**
   * Group View.
   * By
   * Jackson
   */
  public function groupView($id)
  {
    $buildings = Building::where('building_status',1)->get();
    $areBuildingAssign =  AreBuildingAssign::where('id',$id)->first();
    return view('masters::AreBuildingAssign.group_view',compact('areBuildingAssign','buildings'));
  }
    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(AreBuildingAssign $areBuildingAssign)
    {
       $assignBuilding=PreferredBuilding::where('assign_to',null)->get()->pluck('building_id');


       $buildings = Building::whereNotIn('id',$assignBuilding)->where('building_status',1)->orderBy('building_name', 'ASC')->get();
       $buildingAll = Building::where('building_status',1)->orderBy('building_name', 'ASC')->get();

      // $areBuildingAssign =  AreBuildingAssign::where('id',$areBuildingAssign->id)->first();
      return view('masters::AreBuildingAssign.add_edit',compact('areBuildingAssign','buildings','buildingAll'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,AreBuildingAssign $areBuildingAssign)
    {
      $this->validate($request, [
      'user_id' => 'required',                    
      'assign_from'   => 'required|date',
      /*'building_id'   => 'required',*/
      ]);
     $request->building_id  = explode(',',$request->building_id);

     //dd(!array_filter($request->building_id));
     $oldBuilding = PreferredBuilding::where('are_building_id',$areBuildingAssign->id)->where('assign_to',null)->pluck('building_id');
     $existing_building=$oldBuilding->toArray();

//dd($existing_building);

//remove
     $notInCollectionForTo= array_diff($existing_building,$request->building_id);

     $notInCollectionForToIndex=array_values($notInCollectionForTo);
     //dd($notInCollectionForToIndex);
     for($i =0; $i<count($notInCollectionForToIndex);$i++){
      PreferredBuilding::where('building_id',$notInCollectionForToIndex[$i])->where('are_building_id',$areBuildingAssign->id)->update(['assign_to'=>date('Y-m-d')]);
    }
    //dd($notInCollectionForTo);
    if(!array_filter($request->building_id) == false){
       //add
      $notInCollectionForNew=array_diff($request->building_id,$existing_building);
      $notInCollectionForNewIndex=array_values($notInCollectionForNew);
//dd($notInCollectionForNewIndex);
      for($j =0; $j<count($notInCollectionForNewIndex);$j++){
        $preferredBuilding = array('are_building_id' => $areBuildingAssign->id,
          'building_id' => $notInCollectionForNewIndex[$j],
          'assign_from' =>$request['assign_from'],);
        DB::table('preferred_buildings')->insert($preferredBuilding);
      }

            AreBuildingAssign::where('id',$areBuildingAssign->id)->update([
            'assign_from' => $request['assign_from'],
            ]);

    }else{
   
            }

    session()->flash('success', 'Buildings Edited Successfully');
    return redirect()->route('areBuildingAssign.index');
  }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
     /**
    *
    * ARE Autocomplete
    * 
    **/
     public function areAutocompleteCode(Request $request){
       $key = $request->term;
       $buildingUser=AreBuildingAssign::whereHas('buildingNamesExist')->get()->pluck('user_id');
//dd($buildingUser);
       $are =   User::
                    join('employees', 'employees.id', '=', 'users.user_type_id')
                    ->whereNotIn('users.id',$buildingUser)
                    ->role(['are','are_team_lead'])
                    ->where('user_type_status',TRUE)
                    ->where('username', 'ILIKE', '%'.$key.'%')
                    ->select('users.id AS ids',"employees.employee_name as value")
                    ->get();

       return $are ;

     } 
   /*
   *
   *Advance search
   *
   */
   public function areBuildingAssignSearch(Request $request){

    $closure = array();
    $closure_or = array();

    $user_q = array();
    $user_or = array(); 
    $building_q = array();
    $building_or = array();  

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
        }if($value == 'username'){

          if($key != 0 && $request->logic[$key -1 ] == 'or' )
            $user_or[] = array( $value , $operation ,$fieldValue);
          else
            $user_q[] = array( $value , $operation ,$fieldValue);

        }elseif($value == 'building_name'){

          if($key != 0 && $request->logic[$key -1 ] == 'or' )
            $building_or[] = array( $value , $operation ,$fieldValue);
          else
            $building_q[] = array( $value , $operation ,$fieldValue);

        }else{                
          $fieldValue = $request->fieldValue[$key];
          $operation = $request->operation[$key];
        }

        if($value != 'username' && $value != 'building_name' &&  (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
         if($key != 0 && $request->logic[$key -1 ] == 'or' )
           $closure_or[] = array( $value , $operation ,$fieldValue);
         else
           $closure[] = array( $value , $operation ,$fieldValue);
       }

     }


   }


   if($request->ajax != true){
     if(count($user_or) == 0  && count($user_q) == 0 && count($building_or) == 0  && count($building_q) == 0 && count($closure) == 0  )
      $closure[] = array( 'id' , '=' ,0);
  }

}

}

$user_id = (isset($request->user_id)) ? $request->user_id : null;
$building_id = (isset($request->building_id)) ? $request->building_id : null;
$assign_from = (isset($request->assign_from)) ? $request->assign_from : null;

$qiuck_search = array($user_id,$building_id, $assign_from);
if($request->ajax != true){
  //$qiuck_search = array();
}
$result = array(
  $closure,
  $closure_or,      
  $user_q, $user_or,$building_q, $building_or,$qiuck_search ); 
   //dd($vendor_q);
return $result;    


}
public function areBuildingSearch(Request $request,$result = array())
    {

      $name = Route::currentRouteName();
      $enquiry_fields = [
      'username' => 'ARE',
      'building_name' => 'Building',
      ];

      
      $operations = [
      'ilike' => ' Is Equal To '  ,
      '!=' => ' Is Not Equal To '  ,
      'ilike%...%' => ' Like%...% ',
      ];
      $result = array();

      if(isset($request)){

        $areBuilding =   new AreBuildingAssignController;       
        $result =     $areBuilding->areBuildingAssignSearch($request); 
        $request->flash(); 

      }

      $areBuildingAssigns = AreBuildingAssign::whereHas('buildingNamesExist')->closure($result)->sortable()->paginate(10);
//dd($areBuildingAssign);
      if(isset($request->route))
        $route   =  $request->route;

      if(isset($request->ajax))
        return view('masters::AreBuildingAssign.are_building_assign_list_ajax',compact('areBuildingAssigns','request','route'));

      return view('masters::AreBuildingAssign.are_building_assign_list',compact('areBuildingAssigns','request','enquiry_fields','operations','name'));

      }
      public function enquiryFilter()
    {

      $enquiry_fields = [
      'username' => 'ARE',
       'building_name' => 'Building',
      ];

      
      $operations = [
      'ilike' => ' Is Equal To '  ,
      '!=' => ' Is Not Equal To '  ,
      'ilike%...%' => ' Like%...% ',
      ];
 return view('masters::AreBuildingAssign.are_assign_filter',compact('enquiry_fields','operations'));
      }
   /*
* ARE TEAM Lead List
* By
* Jackson
*/
public function groupAreList(){
  
  $user = \Auth::user();
  $roles = $user->getRoles();
  $rolesNames = $user->getRoleNames()->toArray();
  $userId = $user->id;
  if(in_array('are_team_lead', $rolesNames)){
     $subAreArr = employee::where('head_user','=',$userId)->pluck('id');
      $subAreArr = user::whereIn('user_type_id',$subAreArr)->where('user_type','employee')->pluck('id');

     $subAreArr = $subAreArr->push($userId);
     $areBuildingAssigns = AreBuildingAssign::whereHas('buildingNamesExist')->whereIn('user_id',$subAreArr)->sortable()->paginate($this->noOfRecord);
    
   return view('masters::AreBuildingAssign.are_building_group_list',compact('areBuildingAssigns'));
  }
  else{
    return redirect()->route('home');
  }
}
}
