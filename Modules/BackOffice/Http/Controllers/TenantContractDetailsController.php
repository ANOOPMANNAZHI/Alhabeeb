<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Unit;
use Modules\Sales\Entities\TenantContract;
use Modules\BackOffice\Entities\summaryRemark;
use Modules\Sales\Entities\Tenant;
use DB;
use Exception;


class TenantContractDetailsController extends Controller
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
    public function index()
    {
		try {

		  return view('backoffice::ContractDetails.contract_details_search');

		} catch (ModelNotFoundException $exception) {

			print($exception->getMessage());
			exit();
		}

        
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('backoffice::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('backoffice::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('backoffice::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
    *
    * building Autocomplete
    *
    **/
   public function allBuildingsAutocomplete(Request $request)
    {
       /*$key = $request->term;
      $building =  Building::whereHas('tenantContract')->where('building_name', 'ILIKE', '%'.$key.'%')
      ->select('id AS ids',DB::raw("CONCAT(building_name,'-',building_code) as value"))
      ->get();
      return $building ;*/
	  
	  try{
		  if(!isset($request->term))
			return 0;
	  
		$key = $request->term;
		$building =  Building::areFilter()->where('building_name', 'ILIKE', '%'.$key.'%')
		->select('id AS ids',DB::raw("CONCAT(building_name,'-',building_code) as value"))
		->get();
		return $building ;
		  
	  }catch (Exception $e) {

        print($e->getMessage());exit();
        return json_encode($e->getMessage());
      
    }
  
	  
		

    }

     /*
    *
    *
    *Unit Details
    *
    *
    */
    public function allUnitsDetail(Request $request)
    {
		
		try{
			if(!isset($request->building_id))
			return 0;
		
		$units = array();
		$building_id =$request->building_id;
		// $tenantContract = TenantContract::where('building_id',$building_id)->pluck('unit_id');
		$units = Unit::are()->where('building_id',$building_id)->orderBy('unit_no','ASC')->get();
		// dd($units);
		return json_encode(array($units));
		}
		catch (Exception $e) {

       print($e->getMessage());exit();
			return json_encode($e->getMessage());
		  
		}
		

    }
    /*
    *
    *
    *Tenant Details
    *
    *
    */
    public function allTenantsDetail(Request $request)
    {
		try{
			if(!isset($request->building_id) && !isset($request->unit_id))
			return 0;
		
			$tenants = array();
			
			$building_id =$request->building_id;
			$unit_id =$request->unit_id;
			//$tenantContract = TenantContract::where('building_id',$building_id)->where('unit_id',$unit_id)->pluck('tenant_id');
			$tenantContract = TenantContract::areFilter($request)->where('building_id',$building_id)->where('unit_id',$unit_id)->where('work_flow_processes_code',108)->pluck('tenant_id');
			$tenants = Tenant::whereIn('id',$tenantContract)->get();
			//dd($tenants);
			return json_encode(array($tenants));
		}
		catch (Exception $e) {

       print($e->getMessage());exit();
			return json_encode($e->getMessage());
		  
		}
		
      
    }

    /*
    *
    *
    *Agreement Details
    *
    *
    */
    public function contractDetails(Request $request)
    {
      if(empty($request->has('tenant_id')) && empty($request->has('building_id')) && empty($request->has('building_id'))){
        
         return 0;
      }

	  $fullContractDuration = null;


    $remark = null;

	  
      $tenant_id =$request->tenant_id;
      $building_id =$request->building_id;
      $unit_id =$request->unit_id;
      if($building_id != "" && $unit_id != "" && $tenant_id != ""){
        $tenantContract = TenantContract::areFilter($request)->where('building_id',$building_id)->where('unit_id',$unit_id)->where('tenant_id',$tenant_id)->where('work_flow_processes_code',108)->orderBy('id','desc')->first();
        $tenantContracts = TenantContract::areFilter($request)->where('building_id',$building_id)->where('unit_id',$unit_id)->where('tenant_id',$tenant_id)->where('work_flow_processes_code',108)->orderBy('tenant_contract_effective_date','ASC')->get();

      


      }else if($building_id != "" && $unit_id != ""){
          $tenantContract = TenantContract::areFilter($request)->where('building_id',$building_id)->where('unit_id',$unit_id)->where('work_flow_processes_code',108)->orderBy('id','desc')->first();
        $tenantContracts = TenantContract::areFilter($request)->where('building_id',$building_id)->where('unit_id',$unit_id)->where('work_flow_processes_code',108)->orderBy('tenant_contract_effective_date','ASC')->get();


      }else{
          $tenantContract = TenantContract::areFilter($request)->where('unit_id',$unit_id)->where('tenant_id',$tenant_id)->where('work_flow_processes_code',108)->orderBy('id','desc')->first();
        $tenantContracts = TenantContract::areFilter($request)->where('unit_id',$unit_id)->where('tenant_id',$tenant_id)->where('work_flow_processes_code',108)->orderBy('tenant_contract_effective_date','ASC')->get();
      }

      //Get Remarks
       $remark = SummaryRemark::where('BuildingId',$building_id)->where('UnitId',$unit_id)->where('TenantId',$tenant_id)->orderBy('id','desc')->first();
       
	   if(count($tenantContracts)>1)
            $fullContractDuration = contractDurationCalculation($tenantContracts[0]->tenant_contract_start_date,$tenantContract->tenant_contract_valid_to_date);
      
		return view('backoffice::ContractDetails.contract_details_ajax',compact('tenantContract','tenantContracts','fullContractDuration','remark'));
}

public function GetSummaryRemark(Request $request){

  $unit_id      = $request->unit_id;
  $tenant_id    = $request->tenant_id;
  $building_id  = $request->building_id;
  $remark       = $request->remark;

  $remarkData = SummaryRemark::where('BuildingId',$building_id)->where('UnitId',$unit_id)->where('TenantId',$tenant_id)->orderBy('id','desc')->first();

 try {

      

  if(isset($remarkData)){
    
    $updatedata = null;

    $data =  SummaryRemark::where('BuildingId',$building_id)->where('UnitId',$unit_id)->where('TenantId',$tenant_id)->orderBy('id','desc')->first();

     $data->update([

        'Remark'=> $remark,
   
     ]);

     return json_encode("Remark Saved!");
  }
  else{

       
       SummaryRemark::create([  
        'BuildingId'=> $building_id,
        'UnitId'=> $unit_id,
        'TenantId'=> $tenant_id,
        'Remark'=> $remark,
        'CreatedBy'=> \Auth::user()->id,
        'CreatedOn'=> date('Y-m-d H:i:s'),
    ]);

      return json_encode("Remark Saved!");
      
  

  }

   } catch (Exception $e) {

       
        return json_encode($e->getMessage());
      
    }
  

}

/**
    *
    * Unit Autocomplete
    *
    **/
    public function allUnitsAutocomplete(Request $request)
    {
		if(!isset($request->term))
			return 0;
	
		$key = $request->term;
		$unit =  Unit::are()->where('unit_code', 'ILIKE', '%'.$key.'%')
		->select('id AS ids',DB::raw("CONCAT(unit_code) as value"))
		->get();
		return $unit;
    }
    /**
    *
    * Tenant Autocomplete
    *
    **/
    public function allTenantsAutocomplete(Request $request)
    {
		if(!isset($request->term))
			return 0;

		$key = $request->term;
		$tenant =  Tenant::whereHas('tenantContracts', function ($query)use($request) {
		$query->areFilter($request);      
		})->where('tenant_name', 'ILIKE', '%'.$key.'%')
		->select('id AS ids',DB::raw("CONCAT(tenant_name) as value"))
		->get();

		return $tenant;
    }
    /**
    *
    * Get Building Details
    *
    **/
    public function getBuildingDetails(Request $request)
    {
		
		if(empty($request->has('unit_id'))){

			return 0;
		}

		$buildings = array();
		$tenants = array();


		$unit_id =$request->unit_id;
		$building_id = Unit::are()->where('id',$unit_id)->pluck('building_id');
		$buildings = Building::are()->where('id',$building_id)->first();
		$tenantContract = TenantContract::areFilter()->where('unit_id',$unit_id)->where('work_flow_processes_code',108)->pluck('tenant_id');
		$tenants = Tenant::whereIn('id',$tenantContract)->get();
		//dd($tenants);
		return json_encode(array($tenants,$buildings));
      
    }
    /**
    *
    * Get Tenant Details
    *
    **/
    public function getUnitDetails(Request $request)
    {

		if(empty($request->has('tenant_id'))){

			return 0;
		}
		$units = array();
		$buildings = array();
		$tenant_id =$request->tenant_id;
		$tenantContract = TenantContract::areFilter()->where('tenant_id',$tenant_id)->where('work_flow_processes_code',108)->pluck('unit_id');
		$units = Unit::are()->whereIn('id',$tenantContract)->get();




      $building_id = TenantContract::areFilter()->where('tenant_id',$tenant_id)->pluck('building_id');
      if(count($building_id) == 1){
        $buildings = Building::where('id',$building_id)->first();
    }
      
      
      
      return json_encode(array($units,$buildings));
      
    }
    /**
    *
    * Get Building Details
    *
    **/
    public function getBuildingCompleteDetails(Request $request)
    {
		
		if(empty($request->has('unit_id')) && empty($request->has('tenant_id'))){

			return 0;
		}

		$buildings = array();
		$unit_id =$request->unit_id;
		$tenant_id =$request->tenant_id;
		$building_id = TenantContract::areFilter()->where('unit_id',$unit_id)->where('tenant_id',$tenant_id)->pluck('building_id');
		$buildings = Building::are()->where('id',$building_id)->first();
		//dd($tenants);
		return json_encode(array($buildings));
      
    }
}
