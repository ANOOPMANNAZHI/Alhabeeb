<?php

namespace Modules\Masters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
/*use Modules\Sales\Entities\TenantContract;*/
use Modules\Masters\Entities\TenancyDetails;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Building;
use App\User;

class TenancyDetailsController extends Controller
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

      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray(); 
      $tenancyDetails = NULL;
	  $name = NULL;
      $enquiry_fields = [
      'building__building_name' => 'Building Name',
      'building__building_code' => 'Building Code',
      'building__buildingType__building_types_name' => 'Building Type',
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

      $tenancyDetails = Unit::advanceFilter($request)
                             ->are()
                             ->when( (!isset($request->fieldName)), function($query){
                              $query->where('id',0);
                             })                             
                             ->sortable()->get();
                             //->paginate($this->noOfRecord);


      $occupied = $tenancyDetails->where('unit_vaccant_status',1)->count();
      $vaccant = $tenancyDetails->where('unit_vaccant_status',0)->count();

      $sum = $tenancyDetails->map(function ($item, $key) {
          
          return ($item->tenantContract)? $item->tenantContract->tenant_contract_rent:0;

      });

      $sum = $sum->sum();
 //      $sum = 0;
    
/*
      if (in_array('are', $rolesNames) === true) {
       $tenancyDetails = Unit::whereHas('building', function ($query) use($rolesNames) {
        $query->whereHas('areBuildings', function ($query)use($rolesNames) {
          if (in_array('are', $rolesNames) === true) {
            $query->where('user_id','=', \Auth::user()->id);
          }
        });
      })->closure($result)->where('id',0)->sortable()->paginate(10);
       $occupied = $tenancyDetails->where('unit_vaccant_status',1)->count();
       $vaccant = $tenancyDetails->where('unit_vaccant_status',0)->count();

     }else
     {
      $tenancyDetails = Unit::closure($result)->where('id',0)->sortable()->paginate(10);
      $occupied = $tenancyDetails->where('unit_vaccant_status',1)->count();
      $vaccant = $tenancyDetails->where('unit_vaccant_status',0)->count();
    }*/
    //$tenancyDetails=array();
   // dd($tenancyDetails);
  

    return view('masters::TenancyDetails.tenancy_details_list',compact('tenancyDetails','request','enquiry_fields','operations','name','sum','occupied','vaccant'));
  }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      return view('masters::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
      return view('masters::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
      return view('masters::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
    //Advance search
    public function tenancyDetailsSearch(Request $request){

      $closure = array();
      $closure_or = array();
      $building = array();
      $building_or = array();  
      $code = array();
      $code_or = array(); 
      $type = array();
      $type_or = array();   
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
          }elseif($value == 'building'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $building_or[] = array( $value , $operation ,$fieldValue);
            else
              $building[] = array( $value , $operation ,$fieldValue);

          }elseif($value == 'code'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $code_or[] = array( $value , $operation ,$fieldValue);
            else
              $code[] = array( $value , $operation ,$fieldValue);

          }elseif($value == 'type'){

            if($key != 0 && $request->logic[$key -1 ] == 'or' )
              $type_or[] = array( $value , $operation ,$fieldValue);
            else
              $type[] = array( $value , $operation ,$fieldValue);

          }else{                
            $fieldValue = $request->fieldValue[$key];
            $operation = $request->operation[$key];
          }

          if($value != 'building' &&  $value != 'code' && $value != 'type' &&   (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
           if($key != 0 && $request->logic[$key -1 ] == 'or' )
             $closure_or[] = array( $value , $operation ,$fieldValue);
           else
             $closure[] = array( $value , $operation ,$fieldValue);
         }

       }


     }


     if($request->ajax != true){
       if(count($building_or) == 0 && count($closure) == 0 &&  count($building) == 0 && count($code) == 0  && count($code_or) == 0 && count($type) == 0  && count($type_or) == 0 )
        $closure[] = array( 'id' , '=' ,0);
    }

  }

}
   //  dd($closure_date);


$contract_no = (isset($request->contract_no)) ? $request->contract_no : null;
$tenant_contract_old_no = (isset($request->tenant_contract_old_no)) ? $request->tenant_contract_old_no : null;
$building_id = (isset($request->building_id)) ? $request->building_id : null;
$unit_id = (isset($request->unit_id)) ? $request->unit_id : null;
$tenant_contract_start_date = (isset($request->tenant_contract_start_date)) ? $request->tenant_contract_start_date : null;
$tenant_contract_valid_to_date = (isset($request->tenant_contract_valid_to_date)) ? $request->tenant_contract_valid_to_date : null; 
$tenant_id = (isset($request->tenant_id)) ? $request->tenant_id : null;    
$tenant_contract_rent = (isset($request->tenant_contract_rent)) ? $request->tenant_contract_rent : null;  

$qiuck_search = array($contract_no,$tenant_contract_old_no, $building_id , $unit_id, $tenant_contract_start_date, $tenant_contract_valid_to_date,$tenant_id,$tenant_contract_rent);
if($request->ajax != true){

  $qiuck_search = array();
}
$result = array(
 $closure,$closure_or,$building, $building_or,$code, $code_or,$type, $type_or,$qiuck_search
 ); 
     //dd($result);

return $result;       


}
public function TenancySearch(Request $request,$result = array())
{
  $roles = \Auth::user()->getRoles();
  $rolesNames = \Auth::user()->getRoleNames()->toArray(); 

  $enquiry_fields = [
  'building' => 'Building Name',
  'code' => 'Building Code',
  'type' => 'Building Type',
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

    $tenancyDetail =   new TenancyDetailsController;       
    $result =     $tenancyDetail->tenancyDetailsSearch($request); 
    $request->flash(); 

  }
  if (in_array('are', $rolesNames) === true) {
   $tenancyDetails = Unit::whereHas('building', function ($query) use($rolesNames) {
    $query->whereHas('areBuildings', function ($query)use($rolesNames) {
      if (in_array('are', $rolesNames) === true) {
        $query->where('user_id','=', \Auth::user()->id);
      }
    });
  })->closure($result)->sortable()->paginate(10);
   $occupied = $tenancyDetails->where('unit_vaccant_status',1)->count();
   $vaccant = $tenancyDetails->where('unit_vaccant_status',0)->count();
   $sum = 0;
   foreach ($tenancyDetails as $tenancyDetail) {
    $rent = $tenancyDetail->tenantContract->tenant_contract_rent ?? 0;
    $sum+= $rent;
  }

}else{
  $tenancyDetails = Unit::closure($result)->sortable()->paginate(10);
  $occupied = $tenancyDetails->where('unit_vaccant_status',1)->count();
  $vaccant = $tenancyDetails->where('unit_vaccant_status',0)->count();

  $sum = 0;
  foreach ($tenancyDetails as $tenancyDetail) {
    $rent = $tenancyDetail->tenantContract->tenant_contract_rent ?? 0;
    $sum+= $rent;
  }
}


   // dd($tenancyDetails);
if(isset($request->route))
  $route   =  $request->route;

if(isset($request->ajax))
  return view('masters::TenancyDetails.tenancy_details_list_ajax',compact('tenancyDetails','request','route','occupied','vaccant','sum'));

return view('masters::TenancyDetails.tenancy_details_list',compact('tenancyDetails','request','enquiry_fields','operations','name','occupied','vaccant','sum'));
}
public function enquiryFilter(){


  $enquiry_fields = [
  'building' => 'Building Name',
  'code' => 'Building Code',
  'type' => 'Building Type',
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

  return view('masters::TenancyDetails.tenancy_filter',compact('enquiry_fields','operations'));
}
}
