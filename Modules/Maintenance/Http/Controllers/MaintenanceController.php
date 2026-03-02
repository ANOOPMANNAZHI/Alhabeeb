<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Sales\Entities\Tenant;
use Modules\Sales\Entities\TenantContract;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Maintenance\Entities\ComplaintProcess;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Entities\ComplaintUser;
use Modules\Maintenance\Entities\ComplaintServiceReportChecklist;
use Modules\Maintenance\Entities\ComplaintServiceReport;
use Modules\Maintenance\Entities\ComplaintServiceReportInv;
use Modules\Maintenance\Entities\ComplaintServiceReportNote;
use Modules\Masters\Entities\Work;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Occupant;
use Session;
use URL;
use Route;
use DB;
use Spatie\Permission\Models\Role;
use Modules\General\Http\Controllers\GeneralController as General ;

use Modules\Maintenance\Events\NewComplaintEnquiry ;
use Modules\Maintenance\Events\ComplaintReminder ;
use Illuminate\Support\Facades\Mail;
use Modules\Maintenance\Emails\ComplaintRegisterEmail;

class MaintenanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  
        $this->middleware('permission:complaint_enquiries_add', ['only' => ['create','store']]);  
        $this->middleware('permission:complaint_enquiries_edit', ['only' => ['edit','update']]);  
        $this->middleware('permission:complaint_enquiries_destroy', ['only' => ['destroy']]);           
        /*$this->middleware('permission:complaint_enquiries_view', ['only' => ['index','show']]);*/
        $this->middleware('permission:complaint_enquiries_view', ['only' => ['show']]);


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
           'complaint_no' => 'Complaint No',
           'complainer_name' => 'Complaint Name',
           'complaint_mob_no' => 'Complaint Mobile No',
           'location__locations_name' => 'Location',
           'unit__unit_code' => 'Unit',
      ];
           
      
      $operations = [
           '=' => ' Is Equal To '  ,
           '!=' => ' Is Not Equal To '  ,
           '>' => ' Is Greater Than '  ,
           '>=' => ' Is Greater Than Or Equal To '  ,
           '<' => ' Is Less Than '  ,
           '<=' => ' Is Less Than Or Equal To'  ,
           'ilike' => ' Like '  ,
           'ilike%...%' => ' Like%...% ',
       ];
     

      $user = \Auth::user();
      $roles = \Auth::user()->getRoles();
      
      $request->flash();
      

      $ComplaintEnquiries = ComplaintEnquiry::areFilter()
      ->maintenanceFilter()->filter($request)
      ->vipOpen($request)
      ->maintainedLandlord($request)
      ->week($request)
      ->maintenancePending($request)
      ->notClosed($request)
	  ->notClosedForCeo($request)
      ->facilityManager() 
     
                               ->when( (!$user->hasAnyRole(['super_admin','maintenance_coordinator','call_center','are','facility_manager','ceo','md'])),  function($query)use($roles){

                                   $query->whereHas('complaintTicketsAll', function ($query){   
                                         $query->whereHas('complaintProcessAll', function ($query){
                                             $query->complaintUsers();                       
                                         })->assignedUsers();                                   
                                      });   
                                                                      
                                     })
                                   ->sortable()->paginate($this->noOfRecord);
      if(isset($request->route))
        $route   =  $request->route;
          
          
          
      if(isset($request->ajax))
      return view('maintenance::complaint_enquiry_list_ajax',compact('ComplaintEnquiries','request','route'));

      return view('maintenance::complaint_enquiry_list',compact('ComplaintEnquiries','request','enquiry_fields','operations','name'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {

        return view('maintenance::add_complaint_enquiry');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
   // dd($request->works_id);
        $this->validate($request, [
            'complaint_mob_no' => 'required',                    
            'complainer_name'   => 'required',
            'complaint_no'   => 'required',
            'complaint_date'   => 'required|date', 
            'buildings' => 'required',
          //  'unit_id' => 'required',                   
          //  'tenant_name'   => 'required',
            'location_name'   => 'required', 
         ]);

        $users = array();
        $work_flow_processes_code = 701;
        $tenant = $request['tenant_id'];
        $tenantDetail = Tenant::where('id',$tenant)->first();
        $data = $this->getComplaintData();
        if($request['tenantStatus'] == ""){$request['tenantStatus'] = 0;}
        $data['tenant_status'] = $request['tenantStatus'];
        $data['created_by'] = \Auth::user()->id;
        $general =  new General;
        $processAssign = $general->roleUsersFromProcess($work_flow_processes_code,$location_id=null,$pricerange_id=null,$tenant_status=$request['tenantStatus']);
        //dd($processAssign);
        $complaint =  ComplaintEnquiry::create($data);

        $complaint->preferredTime()->sync($request->preferred_time);
       
      //  for($i =0; $i<count($request['works_id']);$i++){
$i = 1;
       foreach($request['works_id']  as  $key=>$val){

            $checklist = ComplaintChecklist::create([
                'complaint_enquiries_id'=>$complaint->id,
                'work_id' =>$val,
                'checklist_desc' =>$request['checklist_des'][$key],
                'complaint_ticket_no' =>$complaint->complaint_no.'-SUB'.$i,
                'work_flow_processes_code' => $work_flow_processes_code,
                'created_by' =>\Auth::user()->id,

            ]); $i++;
            $complaintProcess = ComplaintProcess::create([
              'complaint_checklists_id'=>$checklist->id,
              'complaint_enquiries_id'=>$complaint->id,
              'work_flow_processes_code' => $work_flow_processes_code,
              'created_by' =>\Auth::user()->id,
            ]);
            if($processAssign == false){
             
                $res =    $general->workFlowProcess($work_flow_processes_code);
                $complaintProcess->complaintUsers()->attach($res->default_role, ['user_id' => $res->default_user_id]);
                $complaintProcess->save();

                if(empty($res->default_user_id)){      
                  $users_list = \App\User::role($res->default_role)->get()->pluck('id');        
                  $users = $users_list->toArray();       
                }else 
                    $users[] = $res->default_user_id;

            }else{
              
              foreach($processAssign->assign as $val){
                  $user_id = $val->user_id;

                  if(empty($val->user_id))
                     $user_id = null;

                  $complaintProcess->complaintUsers()->attach($val->role_id, ['user_id' => $user_id]);
                  $complaintProcess->save();
                  if(empty($user_id)){      
                    $users_list = \App\User::role($val->role_id)->get()->pluck('id');         
                    $users_list = $users_list->toArray();  
                    $users_list = array_flatten($users_list);   
                      if(count($users) > 0){
                        $users = array_merge($users,$users_list);             
                      }else           
                        $users = $users_list;       
                  }else 
                   $users[] = $user_id; 

              } 
            }
        }
        $facility_manager = \App\User::role(['facility_manager'])
                                     ->get()->pluck('id');    
		$facility_manager = $facility_manager->toArray();  
		$facility_manager = array_flatten($facility_manager);  

      $users = array_merge($users,$facility_manager); 

        
        $users = array_flatten($users); 
        $users_notify =  \App\User::whereIn('id',$users)->get();
        $complaint->href = url('complaintStage/'.$complaint->id.'/UnAssignedList');
        event(new NewComplaintEnquiry($complaint,$users_notify));

        if($request->cmp_type == 1){
        //Email Notification
        $tenantEmail = $tenantDetail->tenant_contact_email;
        if($tenantEmail == "")$tenantEmail = $tenantDetail->tenant_personal_email;
        if(!empty($tenantEmail)){
          Mail::to($tenantEmail)->send(new ComplaintRegisterEmail($complaint));
        }
      }



        session()->flash('success', 'Complaint Created Successfully');
        return redirect()->route('complaint.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
      clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$id);
      $complaintEnquiry =  ComplaintEnquiry::where('id',$id)->first();
     
      $roles  = \Auth::user()->getRoles();
      $userId = \Auth::user()->id;
      $complaint_id = $complaintEnquiry->id;
     
      if(count($roles)== 1 && $roles[0] == 17){ // Technician
        $sub_complaints = ComplaintChecklist::with('complaintServiceReport')->
            where('complaint_enquiries_id',$complaint_id)->
            where('sub_assigned_to', $userId)
            ->orderBy('id','asc')->get();
      }
      else{

          $sub_complaints = ComplaintChecklist::with('complaintServiceReport')->where('complaint_enquiries_id',$complaint_id)->orderBy('id','asc')->get();
           
      }
    
      return view('maintenance::complaint_enquiry_view',compact('complaintEnquiry','sub_complaints'));

    }
    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
      
      $url = url()->previous();
      Session::put('url', $url);
      $complaintEnquiry =  ComplaintEnquiry::where('id',$id)->first();
      $sub_complaints = ComplaintChecklist::where('complaint_enquiries_id',$id)->paginate(10);
      $com_unit = Unit::where('id',$complaintEnquiry->unit_id)->get();
      //dd($sub_complaints);
      return view('maintenance::add_complaint_enquiry',compact('complaintEnquiry','url','sub_complaints','com_unit'));
      
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,ComplaintEnquiry $complaintEnquiry,$id)
    {
      //dd($request->all());
      $current = Session::get('current');
      $this->validate($request, [
            'complaint_mob_no' => 'required',                    
            'complainer_name'   => 'required',
            'complaint_no'   => 'required',
            'complaint_date'   => 'required|date', 
            'building_id' => 'required',
         //   'unit_id' => 'required',                   
        //    'tenant_name'   => 'required',
            'location_name'   => 'required', 
            'work_id'   => 'required', 
      ]);/*dd($complaintEnquiry);*/
      if($request['tenantStatus'] == ""){$request['tenantStatus'] = 0;}
      $complaintEnquiry = ComplaintEnquiry::where('id',$id)->first();
      $work_flow_processes_code = 701;
      $general =  new General;
      $processAssign = $general->roleUsersFromProcess($work_flow_processes_code,$location_id=null,$pricerange_id=null,$tenant_status=$request['tenantStatus']);
    //  $tenant = $request['tenant_id'];
     // $tenantDetail = Tenant::where('id',$tenant)->first();
      $data = $this->getComplaintData($id);

      $data['tenant_status'] =$request['tenantStatus'];
      $data['updated_by'] = \Auth::user()->id;
      $complaint =  $complaintEnquiry->update($data);

     
      foreach($request['work_id']  as  $key=>$val){
     $complaintChecklist = ComplaintChecklist::where('id',$request['complaint_checklist_id'][$key])->update([
          'work_id' =>$val,
          'checklist_desc' =>$request['checklist_desc'][$key],
          'updated_by' =>\Auth::user()->id,
      ]);
   }

      $complaintEnquiry->preferredTime()->sync($request->preferred_time);

      session()->flash('success', 'Complaint Updated Successfully');
      return redirect()->route($current);
      
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
      clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$id);
      readNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification',$id);
      $process = ComplaintProcess::where('complaint_enquiries_id',$id)->pluck('id');
      $tickets = ComplaintChecklist::where('complaint_enquiries_id',$id)->pluck('id');
      $reports = ComplaintServiceReportChecklist::whereIn('checklist_id',$tickets)->pluck('complaint_service_report_id');
      ComplaintServiceReportInv::whereIn('complaint_service_report_id',$reports)->delete();
      ComplaintServiceReportNote::whereIn('complaint_service_report_id',$reports)->delete();
      ComplaintServiceReportChecklist::whereIn('complaint_service_report_id',$reports)->delete();
      ComplaintServiceReport::whereIn('id',$reports)->delete();
      ComplaintUser::whereIn('complaint_processes_id',$process)->delete();
      ComplaintProcess::where('complaint_enquiries_id',$id)->delete();
      ComplaintChecklist::where('complaint_enquiries_id',$id)->delete();
      ComplaintEnquiry::where('id',$id)->delete();
      session()->flash('success', 'Complaint Deleted Successfully');
      return redirect()->route('complaint.index');
    }
    /*
    *
    *
    * Complaint Search Index
    *
    */
    public function complaintSearch(Request $request,$result = array())
    {
      $name = Route::currentRouteName();
      $enquiry_fields = [
           'complaint_no' => 'Complaint No',
           'complainer_name' => 'Complaint Name',
           'complaint_mob_no' => 'Complaint Mobile No',
           'location__locations_name' => 'Location',
           'unit__unit_code' => 'Unit',
      ];
           
      
      $operations = [
           '=' => ' Is Equal To '  ,
           '!=' => ' Is Not Equal To '  ,
           '>' => ' Is Greater Than '  ,
           '>=' => ' Is Greater Than Or Equal To '  ,
           '<' => ' Is Less Than '  ,
           '<=' => ' Is Less Than Or Equal To'  ,
           'ilike' => ' Like '  ,
           'ilike%...%' => ' Like%...% ',
      ];
    //  $result = array();
      // if(isset($request)){
          
      //     $ComplaintEnquiry =   new MaintenanceController;       
      //     $result =     $ComplaintEnquiry->enquirySearch($request);   
         
        
      // }
      $request->flash();      
      $ComplaintEnquiries = ComplaintEnquiry::filter($request)
                                          //closure($result)
                                          ->sortable()->paginate(10);
      if(isset($request->route))
        $route   =  $request->route;
          
          
      if(isset($request->ajax))       
      return view('maintenance::complaint_enquiry_list_ajax',compact('ComplaintEnquiries','request','route'));

      return view('maintenance::complaint_enquiry_list',compact('ComplaintEnquiries','request','enquiry_fields','operations','name'));
    }

    /*
    *  Combination Search  
    *
    *
    */
    public function combinationSearchMaintenance(Request $request){

      $fieldName =  $request->fieldName;
      $fieldValue =  $request->fieldValue;

         switch($fieldName){

          case 'tenant_contact_no': 
          case 'resident_id':   
              $tenant =  Tenant::with(['tenantContractsActive','location','tenantContractsActive.building','tenantContractsActive.occupant'])
                              ->active()
                              ->when(!empty($fieldName), function ($query) use($fieldName, $fieldValue) {
                                    return $query->where($fieldName, $fieldValue);
                              })
                              ->has('tenantContractsActive')
                              ->first();

              if(!$tenant)
              return response(''); 

              $building = collect();
              foreach($tenant->tenantContractsActive as $tenantContract){
                $building[] = $tenantContract->building;
              }
              return response (['tenant' =>$tenant, 'building' => $building]);
              break;

          case 'building_id': 

             $buildings =     Building::active()
                                ->with(['location'])
                                ->when(($request->cmp_type == 1), function($query){
                                   $query->whereHas('tenantContract',function($query){
                                       $query->active();
                                   });
                                })                                         
                                ->where('building_name', 'ILIKE', '%'.$fieldValue.'%')
                               // ->select("id",DB::raw("CONCAT(building_name,'-',building_code) as name"))
                                ->get();

             return $buildings; 
              break;

        case 'unit_id':  
          // SEARCH  building unit 
			$searchByBuilding = ($request->searchByBuilding)? true : false;

			$units =   Unit::active()
                   ->when(($searchByBuilding),function($query)use($fieldValue){
                     $query->where('building_id',$fieldValue);
                   })  
                   ->when(($searchByBuilding == false),function($query)use($fieldValue){
                     $query->where('unit_code', 'ILIKE', '%'.$fieldValue.'%');
                   })  
                   ->when(($request->cmp_type == 1), function($query){
                     $query->with(['tenantContract','tenantContract.tenant','tenantContract.occupant','building','building.location'])
                          ->has('tenantContract')
                          ->whereIn('unit_vaccant_status',[1,2]);
                   })
                   ->when(($request->cmp_type == 2), function($query){
                     $query->where('unit_vaccant_status',0)
                           ->whereHas('building',function($query){
                                       $query->active();
                                   })
                           ->with(['building','building.location']);    
                   })
                   ->when($request->registerd_mob_no,function($query) use($request){
                      $query->whereHas('tenantContract.tenant',function($query)use($request){
                        $query->where('tenant_contact_no', 'ILIKE', '%'.$request->registerd_mob_no.'%');
                      });
                   })
                   ->when($request->resident_card_id,function($query) use($request){
                      $query->whereHas('tenantContract.tenant',function($query)use($request){
                        $query->where('resident_id', 'ILIKE', '%'.$request->resident_card_id.'%');
                      });
                   })
				   ->orderBy('unit_no','asc')
                   ->get();


          return  response (['units' =>$units]);   
          break;  


         case 'unit_id_search':    
          
          $units =   Unit::active()                             
                     ->when(($request->cmp_type == 1), function($query){
                       $query->with(['tenantContract','tenantContract.tenant','tenantContract.tenant.location','tenantContract.occupant','tenantContract.building','tenantContract.building.location'])
                            ->has('tenantContract')
                            ->whereIn('unit_vaccant_status',[1,2]);
                     })
                     ->when(($request->cmp_type == 2), function($query){
						 
                       $query->where('unit_vaccant_status',0)
                             ->whereHas('building',function($query){
                                       $query->active();
                                   })
                             ->with(['building','building.location']);  
                     })
                     ->find($fieldValue);

           if($request->cmp_type == 1)
           return  response (['tenantContract' =>$units->tenantContract]);   

           if($request->cmp_type == 2)
           return  response (['unit' =>$units]);   


           break;  


         }

    }

    
    /*
    *
    *  ContractSearchByMobile
    *
    */
    public function contractSearchByMobile(Request $request){

        $building = array();
        $bul = array();
        $occupants = array();
        $unit = array();
        $buildingDetail = array();
        $buildingLocation = array();
        $mobile = $request->mobile;
        $resident_card_id = $request->resident_card_id;
        
        if($mobile !=""){
          $tenant =  Tenant::active()->when($mobile, function ($query, $mobile) {
                            return $query->where('tenant_contact_no', $mobile);
                          })->first();
        }
        if($resident_card_id != ""){
          $tenant =  Tenant::active()->
                          when($resident_card_id, function ($query, $resident_card_id) {
                            return $query->where('resident_id', $resident_card_id);
                          })->first();
        }
        
        
        if(!empty($tenant)){
          $bul = $tenant->tenantContractsActive->pluck('building_id')->toArray();
            foreach($tenant->tenantContractsActive as $contracts){

            $building[] = array('id'=> $contracts->building_id ,'name'=>$contracts->building->building_name.'-'.$contracts->building->building_code);
            $unit[] = array('id'=> $contracts->unit_id ,'name'=>$contracts->unit->unit_code);
                if($contracts->occupant_id){
                    $occupants[] = array('id'=> $contracts->occupant_id ,'name'=>$contracts->occupant->occupant_name);
                }
           
            }
          $bul = array_unique($bul,SORT_REGULAR);
          if(count($bul) == 1 ){
            $buildingDetail = $tenant->tenantContract->building;
            $buildingLocation = $tenant->tenantContract->building->location;
          }
            $result = array_unique($building,SORT_REGULAR);
            $occupant_result = array_unique($occupants,SORT_REGULAR);
            $unit_result = array_unique($unit,SORT_REGULAR);
            return json_encode(array($tenant,$result,$tenant->location,$occupant_result,$unit_result,$buildingDetail,$buildingLocation));
         }else{
            return 0;
         }
        

       
        //return $tenant; 

    }
    /*
    *
    *  contractSearchByOccupant
    *
    */
    public function contractSearchByOccupant(Request $request){

        $occupant_no = $request->occupant_no;
        $occupant_id = $request->occupant_id;
        $occupant =  Occupant::
                          when($occupant_no, function ($query, $occupant_no) {
                            return $query->where('occupant_primary_contact_no',$occupant_no);
                          })->when($occupant_id, function ($query, $occupant_id) {
                            return $query->where('id',$occupant_id);
                          })
                          ->first();

        return json_encode(array($occupant,$occupant->tenantContract));
    }
    /**
     * get all requested data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getComplaintData($id=null)
    {
    		$prefix = prefixData('complaint_prefix')->configuration_value;
    		$complaintLatest = ComplaintEnquiry::orderBy('id', 'desc')->first();
    		if(!empty($complaintLatest))
    			$nextcomplaintCode = $prefix.str_pad($complaintLatest->id+1,4,'0',STR_PAD_LEFT);
    		else
    			$nextcomplaintCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);  
        if(empty($id)){
            $complaintData['complaint_date'] = request('complaint_date');
            $complaintData['complaint_no'] = $nextcomplaintCode;
        }    
        
        $complaintData['building_id'] = request('buildings');
        $complaintData['complainer_name'] = request('complainer_name'); 
        $complaintData['location_id'] = request('tenant_location_id'); 
            
        $complaintData['complaint_mob_no'] = request('complaint_mob_no'); 
        $complaintData['way_no'] = request('way_no');

        if(request('cmp_type') == 1 || request('cmp_type') == 2)
        $complaintData['unit_id'] = request('unit');  
        
        if(request('cmp_type') == 1){
        $complaintData['occupant_id'] = request('occupant_name');
        $complaintData['tenant_id'] = request('tenant_id');
        }

        $complaintData['priority_status'] = request('priority_status');
        $complaintData['complainer_category'] = request('cmp_type');

        

        return $complaintData;
    }
    /*
    *
    *
    * Sub complaints add form 
    *
    */
    public function addSubComplaint(Request $request){
        $work_id = $request->work;
        $checklist_desc = $request->checklist_desc;
        $works = Work::where('id',$work_id)->first(); 
        $works_code = $works->works_code;
        $no = $request->no;
        return view('sales::complaints_sub_category',compact('no','work_id','works_code','checklist_desc'));
    }
    /*
    *
    * get tenant and contract details by building , unit
    *
    *
    */
    public function contractDetailsByBuildingUnit(Request $request){

      $building = $request->building;
      $unit = $request->unit;
      $contract =  TenantContract::active()->where('building_id',$building)->where('unit_id',$unit)->first();
      //dd($contract);
      $tenant = $contract->tenant;

      $location = $contract->tenant->location;
      $buildingDetails = $contract->building;
      $buildingLocation = $contract->building->location;
      $occupant = $contract->occupant;
      return json_encode(array($contract,$tenant,$location,$buildingDetails,$buildingLocation,$occupant));

    }
    /*
    * Enquiry Search Form
    * 
    *
    */
    public function enquiryFilter(){


      $enquiry_fields = [
           'complaint_no' => 'Complaint No',
           'complainer_name' => 'Complaint Name',
           'complaint_mob_no' => 'Complaint Mobile No',
           'location__locations_name' => 'Location',
           'unit__unit_code' => 'Unit',
      ];

      $operations = [
           '=' => ' Is Equal To '  ,
           '!=' => ' Is Not Equal To '  ,
           '>' => ' Is Greater Than '  ,
           '>=' => ' Is Greater Than Or Equal To '  ,
           '<' => ' Is Less Than '  ,
           '<=' => ' Is Less Than Or Equal To'  ,
           'ilike' => ' Like '  ,
           'ilike%...%' => ' Like%...% ',
      ];

     return view('maintenance::enquiry_filter',compact('enquiry_fields','operations'));

    }
    /**
    *
    * Building Autocomplete
    *
    **/
    public function buildingAutocomplete(Request $request){

      $key = $request->term;
      $building = TenantContract::select([
            'tenant_contracts.building_id',
        ])->whereHas('building', function ($query)use($key) {
        $query->where('building_name', 'ILIKE', '%'.$key.'%');
        
      })->withCount([
            'building as value' => function($query) {
                $query->select(DB::raw("CONCAT(building_name,'-',building_code) as value"));
            },
            'building as ids' => function($query) {
                $query->select('id AS ids');
            }
        ])->active()->groupBy('building_id')->get();
      

      return $building ;

    }
    /*
    *
    *
    * Complaint Ticket Edit 
    *
    */
    public function ticketEdit(Request $request){

      $work_id = $request->work_id;
      $no = $request->no;
      $checklist_desc = $request->desc;
      $works = Work::get();
      $workss = Work::where('id',$work_id)->first(); 
      $works_code = $workss->works_code;
      return view('sales::complaints_sub_category_edit',compact('works','work_id','works_code','checklist_desc','no'));
    }
    /*
    *
    *
    * Sub complaints updated
    *
    */
    public function updateSubComplaint(Request $request){
        $result = array();
        $result['work_id'] = $request->work;
        $result['checklist_desc'] = $request->checklist_desc;
        $works = Work::where('id',$request->work)->first(); 
        $result['works_code'] = $works->works_code;
        $result['no'] = $request->no;
        return $result;
    }
    /*
    *
    * Search 
    *
    */
    public function enquirySearch(Request $request){
    
      $closure = array();
      $closure_or = array();
      
      $locationq = array();
      $location_or = array();
      $unitq = array();
      $unit_or = array();  
      
     //dd($request->operation); 
		if(isset($request->fieldName)){
		 if(count($request->fieldName) > 0){

			foreach ($request->fieldName as $key => $value) {
		
			  if( !empty($request->fieldValue[$key]) && !empty($request->fieldValue[$key]) && !empty($value) ) {
			  
				   $operation = $request->operation[$key];
				   $fieldValue = $request->fieldValue[$key];
				   
					if($request->operation[$key] == 'ilike%...%' ){
					  $fieldValue = '%'.$request->fieldValue[$key].'%';
					  $operation = 'ilike';
					}
					if($value == 'location'){
			   
					  if($key != 0 && $request->logic[$key -1 ] == 'or' )
					  $location_or[] = array( $value , $operation ,$fieldValue);
					  else
					  $locationq[] = array( $value , $operation ,$fieldValue);

					}elseif($value == 'unit'){
			   
					  if($key != 0 && $request->logic[$key -1 ] == 'or' )
					  $unit_or[] = array( $value , $operation ,$fieldValue);
					  else
					  $unitq[] = array( $value , $operation ,$fieldValue);

					}else{                
					  $fieldValue = $request->fieldValue[$key];
					  $operation = $request->operation[$key];
					}
			   
				   if($value != 'location' && $value != 'unit' &&  (array_search($request->operation[$key],['>','<','>=','<=']) === FALSE ) ) {
					   if($key != 0 && $request->logic[$key -1 ] == 'or' )
					   $closure_or[] = array( $value , $operation ,$fieldValue);
					   else
					   $closure[] = array( $value , $operation ,$fieldValue);
					}
				   
			   }
				  
				  
			}
			 
		   //dd($locationq);   
		  /*if($request->ajax != true){
		   if(count($location_or) == 0 && count($unit_or) == 0 && count($unitq) == 0 && count($closure) == 0 &&  count($locationq) == 0 )
			$closure[] = array( 'id' , '=' ,0);
		  }*/

		 }

		}
   
    $ticket_no = (isset($request->ticket_no)) ? $request->ticket_no : null;
    $complaint_no = (isset($request->complaint_no)) ? $request->complaint_no : null;
    $complainer_name = (isset($request->complainer_name)) ? $request->complainer_name : null;
    $complaint_mob_no = (isset($request->complaint_mob_no)) ? $request->complaint_mob_no : null;
    $building_id = (isset($request->building_id)) ? $request->building_id : null;    
    $complaint_date = (isset($request->complaint_date)) ? $request->complaint_date : null;
    $unit = (isset($request->unit)) ? $request->unit : null;
    $location = (isset($request->location)) ? $request->location : null;
    $status = (isset($request->status)) ? $request->status : null;
    $tenant_status = (isset($request->tenant_status)) ? $request->tenant_status : null;
    $category = (isset($request->category)) ? $request->category : null;

    $qiuck_search = array($ticket_no,$complaint_no, $complainer_name , $complaint_mob_no, $building_id, $complaint_date, $unit, $location,$status,$category,$tenant_status);
    //dd($category);
    if($request->ajax != true){
       
      $qiuck_search = array();
    }
    $result = array(
      $closure,
      $closure_or,      
      $locationq, $location_or, $unitq,$unit_or,$qiuck_search );

   
   return $result;    
     
     
  }
  /*
  *  
  * Enquiry Reminder
  *
   
   */
  public function enquiryReminder($id)
  {
    /*$users = array();     
    $next_process_id = 701;
    
    $tenant_status = $complaintEnquiry->tenant_status;
    $general =  new General;
    $processAssign    = $general->roleUsersFromProcess($next_process_id,$location_id=0,$pricerange_id=0,$tenant_status);*/
    
    $complaintEnquiry = ComplaintEnquiry::where('id',$id)->first();
    $process =   ComplaintProcess::where('complaint_enquiries_id',$id)->latest()->first(); 
       
    $complaintUsers =  $process->complaintUser;
    
    $users = array();     
     
      foreach($complaintUsers as $val){
      $user_id = $val->user_id;
        if(empty($val->user_id)){
          
           $users_list = \App\User::role($val->role_id)->get()->pluck('id');         
           $users_list = $users_list->toArray();  
           
           if(count($users) > 0){          
            $users = $users->merge($users_list);
           }else           
            $users[] = $users_list;   
          
        }else 
         $users[] = $val->user_id;
      
      }
      
    $users = array_flatten($users);      
    $users_notify =  \App\User::whereIn('id',$users)->get();
    $roles = Role::whereIn('id',$users_notify->pluck('default_role'))->first()->name;
    $complaintEnquiry->text = " Complaint Reminder -  Assign  ".$complaintEnquiry->complainer_name. " [".$complaintEnquiry->complaint_no."]";   
    $complaintEnquiry->href = url('complaint/'.$complaintEnquiry->id);
    event(new ComplaintReminder($complaintEnquiry,$users_notify)); 
      
      
    session()->flash('success', ' Notification Send To '.ucwords(str_replace('_', ' ',$roles)));

    return redirect()->route('complaint.index');       
   }
   /*
   *
   *
   * Checklist Edit
   *
   */
   public function checklistEdit(Request $request)
   {
      $url = url()->previous();
      Session::put('url', $url);
      $checklist_id = $request->checklist_id;
      $complaintChecklist = ComplaintChecklist::where("id",$checklist_id)->first();
      $works = Work::get();
      return view('maintenance::complaints_checklist_edit_modal',compact('complaintChecklist','works','url'));

   }
   /*
   *
   *
   * Checklist Update
   *
   *
   */

   public function checklistUpdate(Request $request,ComplaintChecklist $complaintChecklist)
   {
      $this->validate($request, [
        'work_id'   => 'required', 
      ]);
      $url = $request['url'];//dd($url);
      $complaint_id = $complaintChecklist->complaint_enquiries_id;
      $complaintChecklist->update([
          'work_id' =>$request['work_id'],
          'checklist_desc' =>$request['checklist_desc'],
          'updated_by' =>\Auth::user()->id,

      ]);
      session()->flash('success', ' Ticket Update Successfully');
      return redirect($url);
      //return redirect()->route('complaint.show',$complaint_id);

   }
   /*
    *
    *
    * Building By Unit
    *
    */
    public function buildingByUnitOccuiped(Request $request){

      $units = array();
      $buildingLocation = array();
      $buildingDetails = array();
      $occupants = array();
      $occupant_result = array();
       $id = $request->input('id'); 
       //$id = $request->building;
       $tenant = $request->input('tenant');
       if(!empty($tenant)){
        $contracts = TenantContract::with('Unit')->where('building_id',$id)
                    ->when($tenant, function ($query, $tenant) {
                      return $query->where('tenant_id', $tenant);
                    })->get();
         foreach($contracts as $contract){
          $units[] = array('id'=> $contract->unit_id ,'unit_code'=>$contract->Unit->unit_code);
          $buildingDetails = $contract->building;
          $buildingLocation = $contract->building->location;
            if($contract->occupant_id){
                $occupants[] = array('id'=> $contract->occupant_id ,'name'=>$contract->occupant->occupant_name);
            }
         }
        $occupant_result = array_unique($occupants,SORT_REGULAR);

       }else{
        $units = Unit::where('building_id',$id)->where('unit_vaccant_status',1)->get();
        $buildingDetails = Building::with('location')->where('id',$id)->first();
        $buildingLocation = $buildingDetails->location;
      
       }
       
        //dd($contracts);
      //$units = Unit::where('building_id',$id)->where('unit_vaccant_status',1)->get();
      
    
      return json_encode(array($units,$buildingLocation,$buildingDetails,$occupants));
      //return json_encode($units);

    }
    /*
    *
    *
    * Building Details
    *
    */
    public function getBuildingDetail(Request $request){

      $buildingLocation = array();
       $id = $request->input('id'); 
       $building = Building::with('buildingType')->where('id',$id)->first();
       $buildingLocation = isset($building->location)?$building->location:null;
       //dd($id);
      //$buildingTypeName = $building->buildingType->building_types_name;
       return json_encode(array($building,$buildingLocation));

    }
}
