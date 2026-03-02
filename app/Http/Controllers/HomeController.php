<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Menu\Entities\Menu;
 use Spatie\Permission\Models\Role;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Sales\Entities\Sales;
use Modules\Masters\Entities\Employee;
use Modules\General\Entities\WorkFlowProcess;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Masters\Entities\Legal;
use Illuminate\Support\Facades\Hash;
use Modules\Sales\Entities\TenantContract;
use Modules\BackOffice\Entities\Renewal;
use Carbon\Carbon;
use App\Http\Controllers\DashboardController as Dashboard;
use DB;
//use Illuminate\Notifications\Notification;
use Illuminate\Notifications;
use Modules\BackOffice\Http\Controllers\TenantRenewalController as TenantRenewal ;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }    
    
    /*
    *  NoFound 404
    *
    */
    public function notFound(){
    	return view('errors.404');
    }



    /*
    *   Notification Test
    */
    public function notificationTest(){

         $notifications =  \DB::table('notifications')
     //    Notifications::
                    ->whereNull('read_at')
 	                //  ->where('type','Modules\BackOffice\Notifications\TenantTerminationNotification')
 	                  //->where('data->id','314')
 	                 // ->whereRaw('JSON_CONTAINS(data->"id" like ?, 314)')
 	                  ->where('data','ilike', '%"id":4,%')
 	              //    ->where('data','->id', 11)
 	               //   ->slelect('')
 	                 // ->where('options->language', 'en')
 	               //   ->update(['read_at'=>NOW()]);
 	                  ->get();

//$notifications::update('read_at',NOW());



//$query->whereRaw('LOWER(data->"$.id") like ?', ['314']);
 
 	       dd($notifications);           


    }
    
   
    
    public function filterMenu($menugroup){				
	 
		 $menugroup = $menugroup->filter(function ($value, $key) {
			 		 
			$res = array(); 			
			    if($value->menutype == 1){
					$flag_val = false;
										
					 if(count($value->allChildMenu) > 0)
					 {								 
							 if($value->allChildMenu->count() > 0)
					          {								  
								 $res =   $this->filterMenu($value->allChildMenu);	
								 
								 if(count($res) > 0)
								 $flag_val = true;	
								 else
								 $flag_val = false;								 								 
							  }								  
						 
					//	if($flag_val)
					//	$value->allChildMenu =  $res;						 
													 									
					 }			
					
				}else
				$flag_val = true;				
				
				if($flag_val)
				return true;
				
				
			if(count($res) > 0)
			$value->allChildMenu =  $res;	
				 					
			}); 
								
			return $menugroup->flatten();    
	
	
	
	
	/*	                    
     $menugroup = $menugroup->filter(function ($value, $key) {
			    
			    if($value->menutype == 1){
					$flag_val = false;
					
					 if(count($value->allChildMenu) > 0)
					 {	
							 $checkSub = $value->allChildMenu()->where('menutype', 1)->get();
							 if($value->allChildMenu->count() > 0)
					          {								  
								 $res =   $this->filterMenu($value->allChildMenu);									
								 
								 if(count($res) > 0)
								 $flag_val = true;	
								 else
								 $flag_val = false;								 								 
							  }	
							  
							$check = $value->where('menutype', 2)
						                 ->where('parent_menu',$value->id)->get();						                 
						                 
						//    $value->allChildMenu = [$res,$check]  ;           
							 
							if(count($check) > 0){
							$flag_val = true;	
							//$value->allChildMenu[] =  $check;
						}							
						//if($flag_val)
						//$value->allChildMenu[] =  $res;								 									
					 }			
					
				}else
				$flag_val = true;				
				
				if($flag_val)
				return true;
				 					
			}); 					
			return $menugroup; 	*/			
	}
	
	
	
	
	
	
	 public function subMenuFilter($menugroup){
		
		 foreach($menugroup->allChildMenu as $key => $value){
			 
			      if($value->allChildMenu->count() ==  0){	
					 $menugroup->allChildMenu->forget($key);
					}else{							
					$res =  $this->subMenuFilter($value);	print_r($res); echo '<br><br>111111111<br><br><br>';
					if(empty($res))	
					$menugroup->allChildMenu->forget($key); 
					else
					$value->allChildMenu = $res;
											
							}			 
			 }
			 
			 
			 return $menugroup;
		
		}
		
		
		
		
    
    
	
	
	
	public function filterMenuTest($menugroup){
		
		/*
		foreach($menugroup as $key => $value){			 
			 
			 if($value->menutype == 1){				 
				 
				      if($value->allChildMenu->count() ==  0){						  
						 $menugroup->allChildMenu->forget($key); 			  
						  
						}else{							
						 $res =   $this->subMenuFilter($value);	
						
						if(empty($res))	
						$menugroup->allChildMenu->forget($key);		
						else
							$value->allChildMenu = $res;	
									
							}
							
				 }			 
			 }  		   
		   
		   return $menugroup;
		  */ 
		
		/*
		$filtered = $menugroup->reject(function ($value, $key) {
			
			   if($value->menutype == 1){
				   echo count($value->allChildMenu).'<br>';
				    if(count($value->allChildMenu) == 0)
				    return true;
				    else{
					$res_filter = $this->filterMenuTest($value->allChildMenu);
					
					$value->allChildMenu =  $res_filter;
					
						}
				    					    
				   
				}			
          
        });
        
        
        return $filtered;

		        
		   
		   
		 foreach($menugroup as $key => $value){
			 
			 
			 if($value->menutype == 1){
				 
				 
				      if($value->allChildMenu->count() ==  0){						  
						 unset($menugroup[$key]);  			  
						  
						}else{							
						$this->filterMenuTest($value->allChildMenu);	
						
							}
				 }			 
			 }  
		   
		   
		   return $menugroup;
		   */  
		   
		   
		          
        $menugroup = $menugroup->filter(function ($value, $key) {
			//echo  $key;  
			$res = array(); 
			
			    if($value->menutype == 1){
					$flag_val = false;
					
					 if($value->allChildMenu->count() > 0)
					 {	   							  
						 $res =   $this->filterMenuTest($value->allChildMenu);	
						// echo $res;								
						 
						 if(count($res) > 0)
						 $flag_val = true;	
						 else
						 $flag_val = false;	
						 
					//	if($flag_val)
					//	$value->allChildMenu =  $res;	
												 									
					 }			
					
				}else
				$flag_val = true;
				
				
		//	if(count($res) > 0)
		//	$key->allChildMenu() =  $res;	
								
				
				if($flag_val)
				return true;			
				 					
			}); 
			
			
								
			return $menugroup;  					
	}
	
	
	
	
	public  function menuTest(){
		
		
		$res =  \Auth::user()->getAllPermissions()->pluck('menu_id')->toArray();
      $res = array_unique($res); 
      
      $menugroup =  Menu::with('allChildMenu')                          
                           ->where(function ($query)  {
									$query->where('menutype', 1)
									      ->where('parent_menu',0)   
										  ->has('allChildMenu', '>=', 1);										  
									return $query;
							})
							->orWhere(function ($query)use($res) {
									$query->where('menutype', 2)
										  ->whereIn('id', $res)
										  ->where('parent_menu',0)  ;										  
									return $query;
							}) 
						   ->orderBy('menu_order', 'asc')
                           ->get();
       
			
		$menugroup = 	$this->filterMenuTest($menugroup);
		
		
		 return $menugroup;


	}
	
	
	
	public function menuTestSubmenu($val,$checkPath){
		  $flag = false;
		 
		      if($val->menutype == 1  && (count($val->allChildMenu) > 0) ){
				  
				  if($checkPath)
				  $flag = menuSelectionCheck($val,$checkPath);
				  
				   if($flag == true) echo 'open';
				   echo  '&nbsp;&nbsp;&nbsp;'.$val->menu_name. '<br>';
				   
				   $menuSub =  $val->allChildMenu->sortBy('menu_order');
				    
				   foreach($menuSub as $menuSub_val){
					   
					   if($menuSub_val->menutype == 2){						   
						   echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$menuSub_val->menu_name. '<br>';
					   }else{						   
						   
						   if($menuSub_val->menutype == 1  && (count($menuSub_val->allChildMenu) > 0) ){						   
						   $this->menuTestSubmenu($menuSub_val,$checkPath);
					        }
						   
						   }					   
					 } 
				    
				 }else{
					 
					  echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$val->menu_name. '<br>';
					 
					 }
		 
		}
		
		
		
		
		
		
		
    		
	
	public function filterAgain($menu){
		
			foreach($menu->allChildMenu as $key => $value){
				
				 if($value->menu_type == 1){
			 
			      if($value->allChildMenu->count() ==  0){	
					 $menu->allChildMenu->forget($key);
					}else{							
					$res =  (new self)->filterAgain($value);	
					if(empty($res))	
					$menu->allChildMenu->forget($key); 
					else
					$menu->allChildMenu[$key] = $res;											
				 }
			    }			 
			 }
			 
			 if($menu->menu_type == 1 && $menu->allChildMenu->count() == 0)
			 return null;
			 
			 
			 return $menu;
			
			}
			
			


    public  static function createRole(){  
		
		$menu = (new self)->menuTest();	
		
		/*
		foreach($menu as $key => $value){
			 
			  if($value->menu_type == 1){
			      if($value->allChildMenu->count() ==  0){	
					 $menu->forget($key);
					}else{							
					$res =  (new self)->filterAgain($value);	
					if(empty($res))	
					$menu->forget($key); 
					else
					$menu->allChildMenu[$key] = $res;					
				     }											
				 }			 
			 }
		*/
		
		// dd($menu->all());
		
		$checkPath = false;	
		
		
		foreach($menu as $val){
			
			 $checkPath = menuSelectionCheck($val,'leadAssignedList');	 
			
			
			 echo '&nbsp;&nbsp;';   (new self)->menuTestSubmenu($val,'leadAssignedList');	
			
			
			}      
      
    }
    
    
    
    
    
    
    
    
    


    /*
    * Dashboard
    *
    */
    public function dashboard(Request $request,$role = null){
         $dashboard =  new Dashboard;
		 if($role == null) 
			$default_role =  \Auth::user()->default_role_name; 
		 else
			$default_role =  $role;


		$today = date('Y-m-d');
		$week = Carbon::today()->subDays(6);
		$week = date("Y-m-d", strtotime($week) );
       

        $date = new Carbon;  
        $date->subWeek(); 
       
       //modification for auto renewal 
	   $tenantRenewal =  new TenantRenewal;
        $tenantRenewal->cronRenewalContract();
        // $this->info('Tenant Renewal Activation');

        $employees = Employee::take(10)->get();
		
        $date_thirty = Carbon::today()->subDays(30);
        
        $enquiries_unattened =  Sales::whereDoesntHave('salesActivity')
                             ->whereIn('work_flow_processes_code',[102,202])
                             ->whereDate('created_at','>=',$date_thirty)
                             ->whereHas('salesUsers', function ($query) {
                                    $query->whereIn('role_id', \Auth::user()->getRoles()) 
                                      ->where(function ($query) {
                                        $query->where('user_id', null)
                                              ->orWhere('user_id', \Auth::user()->id);
                                    });
                                })
                             ->whereHas('salesEnquiry', function ($query) {
                                    $query->whereIn('work_flow_processes_code',[102,202]);
                                })
                             ->count();

		$enquiries_progress = Sales::whereIn('work_flow_processes_code',[103,202])
                             ->whereDate('created_at','>=',$date_thirty)
                             ->whereHas('salesUsers', function ($query) {
                                    $query->whereIn('role_id', \Auth::user()->getRoles())
                                      ->where(function ($query) {
                                        $query->where('user_id',  null)
                                              ->orWhere('user_id', \Auth::user()->id);
                                    });
                                })
                             ->whereHas('salesEnquiry', function ($query) {
                                    $query->whereIn('work_flow_processes_code',[103,202]);
                                })
                             ->count();                                    
			
		 $enquiries_unassigned_doc =  Sales::whereIn('work_flow_processes_code',[104,204])
								 ->whereDate('created_at','>=',$date_thirty)
								  ->whereHas('salesUsers', function ($query) {
                                    $query->whereIn('role_id', \Auth::user()->getRoles())
                                      ->where(function ($query) {
                                        $query->where('user_id',  null)
                                              ->orWhere('user_id', \Auth::user()->id);
                                    });
                                  })  
								 ->whereHas('salesEnquiry', function ($query) {
										$query->whereIn('work_flow_processes_code',[104,204]);
									})
								 ->count();
		
		$date_ninety = Carbon::today()->subDays(90);
		$enquiries_unassigned =  Sales::whereIn('work_flow_processes_code',[101])
                             ->whereDate('created_at','>=',$date_ninety)
                             ->whereHas('salesUsers', function ($query) {
                                    $query->whereIn('role_id', \Auth::user()->getRoles()) 
                                      ->where(function ($query) {
                                        $query->where('user_id', null)
                                              ->orWhere('user_id', \Auth::user()->id);
                                    });
                                })
                             ->whereHas('salesEnquiry', function ($query) {
                                    $query->whereIn('work_flow_processes_code',[101]);
                                })
                             ->count();		
                             
                      //  dd($enquiries_unassigned);	
          //Assigned Enquiry
        $enquiries_assigned =  Sales::whereIn('work_flow_processes_code',[102,202])
                             ->whereDate('created_at','>=',$date_ninety)
                             ->where('created_by',\Auth::user()->id)
                             ->whereHas('salesEnquiry', function ($query) {
                                    $query->whereIn('work_flow_processes_code',[102,202]);
                                })
                             ->count();
                             
         //In Progress
        $enquiries_inprogress =  Sales::whereIn('work_flow_processes_code',[102,202])
                                 ->whereDate('created_at','>=',$date_ninety)
                                 ->where('created_by',\Auth::user()->id)
                                 ->whereHas('salesEnquiry', function ($query) {
                                        $query->whereIn('work_flow_processes_code',[103,203]);
                                    })
                                 ->count(); 		 
		 // Current month won
		$roles = \Auth::user()->getRoles();
		$enquiries_current_won = Sales::whereHas('salesEnquiry', function ($query){
						$query->where('work_flow_processes_code', '=', 108);                   
					})->where('sales.sales_type', '=', 1)
					  ->where('sales.work_flow_processes_code', '=', 108)
					  ->whereMonth('sales.created_at', '=',date('m'))
					  ->whereYear('created_at', '=', date('Y'))
					  ->whereHas('salesUsers', function ($query) use($roles) {
							$query->where(function ($query) use($roles){
								$query->where('user_id',null)
								  ->whereIn('role_id', $roles);
								})
							->orWhere(function ($query) use($roles){
								$query->where('user_id','>',0)
									  ->whereIn('role_id', $roles)
									  ->where('user_id','=', \Auth::user()->id);
								})                      
							->where('status','=',1);                       
						})->count();
		   
			 //Open Enquiry  109 108  
			
			$enquiries_open =  SalesEnquiry::whereNotIn('work_flow_processes_code',[109,108,209,208])
									->whereDate('created_at','>=',$date_ninety) 
                             ->tenant() 
                             ->count(); 

			$enquiriesLandlord =  SalesEnquiry::landlord()->take(5)->get();
			
			$roles =  Role::count();
			$employee =  employee::count();
			$workFlowProcess =  WorkFlowProcess::count();
            $param = 'callcenter';


            /****  salesCoordinatorDashboard   *********/
           
           if(\Auth::user()->hasRole('sales_coordinator'))
            $salesCoordinator = $dashboard->salesCoordinatorDashboard($request,true);



              if(\Auth::user()->hasRole('sales_person'))
            $salesPerson = $dashboard->salesPersonDashboard($request,true);
            /*****salesCoordinatorDashboard End  ********/


             /****** CEO   ***********/
           if(\Auth::user()->hasRole('ceo')){

         //  	dd(\Auth::user()->roles()->get());
//dd(\Auth::user()->id);

            $ceo = $dashboard->ceoDashboard($request,true);

//dd($ceo);
            $areBuildingAssigns = \Modules\Masters\Entities\AreBuildingAssign::  				with('areUser.employee')
                             ->whereHas('buildingNamesExist')
                             ->get();

            }


              /****  Maintenance Supervisor Dashboard   *********/
           
           if(\Auth::user()->hasRole('maintenance_supervisor'))
            $maintenanceSupervisor = $dashboard->maintenanceSupervisorDashboard($request,true);


            /*****Maintenance Supervisor Dashboard End  ********/

            /****  Take Over Supervisor/Coordinator Dashboard   *********/
           
           if(\Auth::user()->hasRole('take_over_supervisor'))
            $takeOverSupervisor = $dashboard->takeOverSupervisorDashboard($request,true);

            /*****Take Over Supervisor/Coordinator Dashboard End  ********/

            /****  ARE Dashboard   *********/
           
           if(\Auth::user()->hasRole('are'))
            $are = $dashboard->areDashboard($request,true);
        
            /*****ARE Dashboard End  ********/

            /****  Maintenance Engineer Dashboard   *********/
           
           if(\Auth::user()->hasRole('maintenance_engineer'))
            $maintenanceEngineer = $dashboard->maintenanceEngineerDashboard($request,true);

            /*****Maintenance Engineer Dashboard End  ********/

            /****  Maintenance Co-Ordinator Dashboard   *********/
           
           if(\Auth::user()->hasRole('maintenance_coordinator'))
            $maintenanceCoordinator = $dashboard->maintenanceCoordinatorDashboard($request,true);

            /*****Maintenance Co-Ordinator Dashboard End  ********/
            /****  Backoffice Executive Dashboard   *********/
           
           if(\Auth::user()->hasRole('backoffice_executive'))
            $backofficeExecutive = $dashboard->backofficeExecutiveDashboard($request,true);

            /*****Backoffice Executive Dashboard End  ********/ 
            /****  Legal Advisor Dashboard   *********/
           
           if(\Auth::user()->hasRole('legal_advisor'))
            $legalAdvisor = $dashboard->legalAdvisorDashboard($request,true);

            /***** Legal Advisor Dashboard End  ********/
            /**** Call Center Dashboard   *********/
           
           if(\Auth::user()->hasRole('call_center'))
            $callCenter = $dashboard->callCenterDashboard($request,true);

            /*****Call Center Dashboard End  ********/
            /**** ARE Team Leader Dashboard   *********/
           
           if(\Auth::user()->hasRole('are_team_lead'))
            $areTeamLead = $dashboard->areTeamLeadDashboard($request,true);

            /*****ARE Team Leader Dashboard End  ********/

         	/**** Sales Head Dashboard   *********/
           
            if(\Auth::user()->hasRole('sales_head'))
            $salesHead = $dashboard->salesHeadDashboard($request,true);
                 /*****Sales Head Dashboard End  ********/
			     /****** Facility Manager   ***********/
           if(\Auth::user()->hasRole('facility_manager'))
            $facilityManager = $dashboard->facilityManagerDashboard($request,true);
        /****** Facility Manager   ends***********/
  /**** Backoffice Manager Dashboard   *********/
           
           if(\Auth::user()->hasRole('backoffice_manager'))
            $backofficeManager = $dashboard->backofficeManagerDashboard($request,true);


            /*****Backoffice Manager Dashboard End  ********/

               /****** #region MD Dashboard-Starts (25-12-2020)*********/
           
            if(\Auth::user()->hasRole('md'))

            $managingDirector = $dashboard->mdDashboard($request,true);

        	$areBuildingAssigns = \Modules\Masters\Entities\AreBuildingAssign::with('areUser.employee')
                             ->whereHas('buildingNamesExist')
                             ->get();

      
            

			return view('dashboard',compact('enquiries_unassigned','enquiries_assigned','enquiries_inprogress' ,'enquiries_unattened',
				 'enquiries_progress', 'enquiries_open','enquiriesLandlord','default_role',
				 'roles','employee','workFlowProcess','enquiries_unassigned_doc','employees','enquiries_current_won','today','param','week'))
		  	      ->with([
		  	     	'salesCoordinator' =>  isset($salesCoordinator)? $salesCoordinator:null,
		  	     	'salesPerson'  =>   isset($salesPerson)? $salesPerson:null,
		  	     	'maintenanceSupervisor'  =>   isset($maintenanceSupervisor)? $maintenanceSupervisor:null,
		  	     	'takeOverSupervisor'  =>   isset($takeOverSupervisor)? $takeOverSupervisor:null,
		  	     	'are'  =>   isset($are)? $are:null,
		  	     	'maintenanceEngineer'  =>   isset($maintenanceEngineer)? $maintenanceEngineer:null,
		  	     	'maintenanceCoordinator'  =>   isset($maintenanceCoordinator)? $maintenanceCoordinator:null,
		  	     	'backofficeExecutive'  =>   isset($backofficeExecutive)? $backofficeExecutive:null,
		  	     	'legalAdvisor'  =>   isset($legalAdvisor)? $legalAdvisor:null,
		  	     	'callCenter'  =>   isset($callCenter)? $callCenter:null,
		  	     	'areTeamLead'  =>   isset($areTeamLead)? $areTeamLead:null,
		  	     	'salesHead'  =>   isset($salesHead)? $salesHead:null,
		  	     	'ceo'  =>   isset($ceo)? $ceo:null,
		  	     	'areUsers'  =>   isset($areBuildingAssigns)? $areBuildingAssigns:null,
					'facilityManager'  =>   isset($facilityManager)? $facilityManager:null,
		  	     	'backofficeManager'  =>   isset($backofficeManager)? $backofficeManager:null,
		  	     	'managingDirector'  =>   isset($managingDirector)? $managingDirector:null
		  	  ]);

		/***** #endregion MD Dashboard-Ends (25-12-2020) ********/


        
    }


    /*
    * menuListing
    *
    */
     public static function menuListing(){

    // \Auth::user()->assignRole('super_admin');
    // $roles =    \Auth::user()->getRoles();
    //$users = \App\User::role(1)->get();
   //  dd($roles);
      if(!\Auth::user())
       return array();
   
   
      $res =  \Auth::user()->getAllPermissions()->pluck('menu_id')->toArray();
      $res = array_unique($res); 
      
      $menugroup =  Menu::with('allChildMenu')                          
                           ->where(function ($query)  {
									$query->where('menutype', 1)
									      ->where('parent_menu',0)   
										  ->has('allChildMenu', '>=', 1);										  
									return $query;
							})
							->orWhere(function ($query)use($res) {
									$query->where('menutype', 2)
										  ->whereIn('id', $res)
										  ->where('parent_menu',0)  ;										  
									return $query;
							}) 
						   ->orderBy('menu_order', 'asc')
                           ->get();
       
			
		$sideMenu = 	(new self)->filterMenu($menugroup);	
		
		$sideMenu = $sideMenu->sortBy('menu_order');
		
		return $sideMenu; 

    }



    /*
    *  Change Password
    *
    */
    public function changePassword(){ 
          return view('changePassword');
    }


    /*
    *  Update Password
    *
    */
    public function updatePassword(Request $request){

        $this->validate($request, [
            'password' => 'required|string|min:6|confirmed',                     
         ]);
 
        $user =  \Auth::user();
        $user->password = Hash::make($request['password']);
        $user->save();
        /****** Activity log *****/
          activity('Password Changed')
            ->performedOn($user)
            ->causedBy(\Auth::user()->id)
            ->withProperties($user)
            ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        session()->flash('success', ' Password Changed ');
        return redirect()->route('dashboard');

    }
	/*
    *  View Profile
    *
    */
    public function profileView($id = null){

        $user =  \Auth::user();
       // dd($user);   
        return view('profile.view_profile',compact('user'));

    }
	/*
    * Ajax count 
    *
    *
    */
     public function dashboardAjaxCount(Request $request){


     $enitity    = $request->enitity;
     $days       = $request->days;

     switch ($enitity) {

        case 'unattend':
             if($days=='all'){
                $date_ninety = '';
             }
             else{
                $date_ninety = Carbon::today()->subDays($days);
             }
             $enquiries_unattened =  Sales::whereDoesntHave('salesActivity')
                             ->whereIn('work_flow_processes_code',[102,202])
                             ->when($date_ninety, function ($query, $date_ninety) {
                                    return $query->whereDate('created_at','>=',$date_ninety);
                                })->whereHas('salesUsers', function ($query) {
                                    $query->whereIn('role_id', \Auth::user()->getRoles()) 
                                      ->where(function ($query) {
                                        $query->where('user_id', null)
                                              ->orWhere('user_id', \Auth::user()->id);
                                    });
                                })
                             ->whereHas('salesEnquiry', function ($query) {
                                    $query->whereIn('work_flow_processes_code',[102,202]);
                                })
                             ->count();
             return $enquiries_unattened;

             break;

        case 'open_document':
             if($days=='all'){
                $date_ninety = '';
             }
             else{
                $date_ninety = Carbon::today()->subDays($days);
             }

             $enquiries_open_doc = Sales::whereIn('work_flow_processes_code',[104,204])
                            ->when($date_ninety, function ($query, $date_ninety) {
                                return $query->whereDate('created_at','>=',$date_ninety);
                            })->where('created_by',\Auth::user()->id)      
                            ->whereHas('salesEnquiry', function ($query) {
                                    $query->whereIn('work_flow_processes_code',[104,204]);
                            })
                            ->count();

            
             return $enquiries_open_doc;

             break;
         
         case 'in_progress':
         
            if($days=='all'){
                $date_count = '';
            }
            else{
                $date_count = Carbon::today()->subDays($days);
            }
           
            $enquiries_in_progress = Sales::whereIn('work_flow_processes_code',[103,202])
                             ->when($date_count, function ($query, $date_count) {
                                return $query->whereDate('created_at','>=',$date_count);
                             })->whereHas('salesUsers', function ($query) {
                                    $query->whereIn('role_id', \Auth::user()->getRoles())
                                      ->where(function ($query) {
                                        $query->where('user_id',  null)
                                              ->orWhere('user_id', \Auth::user()->id);
                                    });
                                })
                             ->whereHas('salesEnquiry', function ($query) {
                                    $query->whereIn('work_flow_processes_code',[103,202]);
                                })
                             ->count();

            
             return $enquiries_in_progress;

             break;  
         
         default:
             # code...
             break;
     }
       
    }



    public  function menuHomesearch(Request $request){
		
		
	  $res =  \Auth::user()->getAllPermissions()->pluck('menu_id')->toArray();
      $res = array_unique($res); 

      $key = $request->term;
      
      $menuList =  Menu::with('menuGroupName')->where(function ($query)use($res) {
									$query->where('menutype', 2)
										  ->whereIn('id', $res);					  
									return $query;
							}) 
                           ->where('menu_name','ilike','%'.$key.'%')->where('status',1)
						   ->orderBy('menu_order', 'asc')						 
                           ->get();


      $menuList = $menuList->map(function ($item, $key) {
      $parent_menu = '';
	      if($item->menuGroupName){
	       $parent_menu = 	(!in_array($item->menuGroupName->menu_name, ['Masters','Operations']) && !empty($item->menuGroupName->menu_name) )? $item->menuGroupName->menu_name.' - ' : '' ;
	      } 
      
      return [ 'ids' => route($item['route_name']) , 'value' =>    $parent_menu.$item['menu_name']
      	        ];
      });                     
       
	  return $menuList;	 	
	  	 


	}

	// Vacancy Loss Grid List
	public  function vacancyLossList($status){

		$toDateVal   = date('Y-m-d 23:59:59');

		if($status === 'YTD'){
			$year 			= date('Y');
			$fromDateVal 	= $year.'-01-01 00:00:00';
		}	
		else{
			$year 	= date('Y');
			$month 	= date('m');
			$fromDateVal = $year.'-'.$month.'-01 00:00:00';
			
		}
		$fromDate   =   $fromDateVal;
		$toDate     =   $toDateVal;	

		$vacantRent = 0;
		$prevRent	= 0;
		$k 	= 0;
		$loop = 0;
		$listArr = array();
		// management_id - comprehensive
		$vacantLoss = DB::table('vacant_vacany_loss')
				->join('buildings', 'buildings.id', '=', 'vacant_vacany_loss.building_id')
				->where(function ($query) use ($fromDate, $toDate) {

				    $query->where(function ($q) use ($fromDate, $toDate) {
				        $q->where('vacant_from', '>=', $fromDate)
				           ->where('vacant_from', '<', $toDate);

				    })->orWhere(function ($q) use ($fromDate, $toDate) {
				        $q->where('vacant_from', '<=', $fromDate)
				           ->where('vacant_to', '>', $toDate);

				    })->orWhere(function ($q) use ($fromDate, $toDate) {
				        $q->where('vacant_to', '>', $fromDate)
				           ->where('vacant_to', '<=', $toDate);

				    })->orWhere(function ($q) use ($fromDate, $toDate) {
				        $q->where('vacant_from', '>=', $fromDate)
				           ->where('vacant_to', '<=', $toDate);
				    });				
				})->where('management_id','=',1)->select('vacant_vacany_loss.building_name','unit_id','unit_no','vacant_vacany_loss.building_no','vacant_to','rent_per_month','rent_per_month','status','vacant_from','vacant_to','status')
				->orderBy('unit_id','desc')
				->get();
		//dd($vacantLoss);
		$vaccanyList =array();
		$previous_unit_id = null;
		$vacantVal  = 0;
		$loop = 0;
		$current_loop = 0;
		$totalvacantDays = 0;
		foreach($vacantLoss as $vac){
			
			if(($vac->vacant_from >= $fromDate && $vac->vacant_to <= $toDate) || ($vac->vacant_from < $fromDate && $vac->vacant_to > $toDate)){
				if($vac->vacant_from < $fromDate && $vac->vacant_to > $toDate){
					$start  = $fromDate;
		        	$end    = date('Y-m-d',strtotime('+1 day',strtotime($toDate)));
				}
				else{
					$start  = $vac->vacant_from;
		        	$end    = date('Y-m-d',strtotime('+1 day',strtotime($vac->vacant_to)));

				}
				
		        $vacantDays =dateDifference($start,$end,'%a');

		        $vacany_loss = 0;
		        if($vac->rent_per_month > 0 && $vacantDays > 0){
		         
		          $vacany_loss = rentLossCalculationWithDays($vacantDays,$vac->rent_per_month);
		          
		        }
		        
			}
			elseif($vac->vacant_from <= $fromDate && $vac->vacant_to >= $fromDate && $vac->vacant_to <= $toDate){

				$start  = $fromDate;
		        $end    = date('Y-m-d',strtotime('+1 day',strtotime($vac->vacant_to)));
		        $vacantDays =dateDifference($start,$end,'%a');
		        $vacany_loss = 0;
		        if($vac->rent_per_month > 0 && $vacantDays > 0){
		         
		          $vacany_loss = rentLossCalculationWithDays($vacantDays,$vac->rent_per_month);
		        }

			}
			elseif($vac->vacant_from >= $fromDate && $vac->vacant_to <= $fromDate && $vac->vacant_to >= $toDate){
				$start  = $vac->vacant_from;
		        $end    = date('Y-m-d',strtotime('+1 day',strtotime($toDate)));
		        $vacantDays =dateDifference($start,$end,'%a');
		        $vacany_loss = 0;
		        if($vac->rent_per_month > 0 && $vacantDays > 0){
		         
		          $vacany_loss = rentLossCalculationWithDays($vacantDays,$vac->rent_per_month);
		        }
				
			}
			// Previous unit_id is same
			if($previous_unit_id == $vac->unit_id){
				$vacantVal += $vacany_loss;
				$totalvacantDays += $vacantDays;
				$vaccanyList[$current_loop]['vacant_loss'] = $vacantVal;
				$vaccanyList[$current_loop]['no_days'] = $totalvacantDays;
			}
			else{
				$totalvacantDays = 0;
				$vacantVal = 0;
				$totalvacantDays = $vacantDays;
				$vaccanyList[$loop]['building_name'] = $vac->building_name;
				$vaccanyList[$loop]['unit_no'] = $vac->unit_no;
				$vaccanyList[$loop]['rent_per_month'] =$vac->rent_per_month ;
				$vaccanyList[$loop]['vacant_loss'] = $vacany_loss;
				$vaccanyList[$loop]['no_days'] = $vacantDays;
				$vaccanyList[$loop]['vacant_since'] = ($vac->vacant_to)?$vac->vacant_to:$vac->vacant_from;
				$previous_unit_id = $vac->unit_id;
				$current_loop = $loop;
				$vacantVal = $vacany_loss;
				$loop++;
			}
		}

		//dd($vaccanyList);
		
		return view('dashboard.vacancy_loss_list',compact('vaccanyList'));
	}
}
