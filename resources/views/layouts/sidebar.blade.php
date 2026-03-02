	<!-- start sidebar menu -->
	@auth
 			<div class="sidebar-container">
 				<div class="sidemenu-container navbar-collapse collapse fixed-menu">
	                <div id="remove-scroll">
	                    <ul class="sidemenu page-header-fixed p-t-20" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">


	                        <li class="sidebar-toggler-wrapper hide">
	                            <div class="sidebar-toggler">
	                                <span></span>
	                            </div>
	                        </li>
	                        <li class="sidebar-user-panel">
	                            <div class="user-panel">
	                                <div class="row">
                                            <div class="sidebar-userpic">
                                            	@auth
												@if(Auth::user()->user_type == 'employee')
												<img alt="" class="img-responsive " src="{{(Auth::user()->employee->employee_picture != '')?  asset("storage/app/".Auth::user()->employee->employee_picture) : asset('public/img/user_default.jpeg') }}" />
												@else
												<img alt="" class="img-responsive " src="{{asset('public/img/user_default.jpeg')}}" />
												@endif  
												@endauth
                                          </div>
                                        </div>
                                        @auth
                                        <div class="profile-usertitle">
                                            <div class="sidebar-userpic-name"> {{ Auth::user()->employee->employee_name?ucwords(Auth::user()->employee->employee_name):ucwords(Auth::user()->username)}} </div>
                                            <div class="profile-usertitle-job"> {{ucwords(str_replace('_', ' ',Auth::user()->default_role_name))}} </div>
                                        </div>
                                        <div class="sidebar-userpic-btn">
									        <a class="tooltips" href="{{ route('profileView') }}" data-placement="top" data-original-title="Profile">
									        	<i class="material-icons">person_outline</i>
									        </a>
									        
									        <a  href="{{ route('logout') }}"  onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();"  class="tooltips"   data-placement="top" data-original-title="Logout">
									        	<i class="material-icons">input</i>
									        </a>
									    </div>
									    @endauth
	                            </div>
	                        </li>
	                       @auth
	                       @php  
	                        
	                        $path = explode('/',Request::path());  
	                        $search_url = $menu_flag = false;	

	                       	if(count($path)  >= 2){	                   			
	                   			
	                   			if(is_numeric($path[1]) || ($path[1] == 'create'))
	                            $search_url = false;
	                            else{
	                            	$search_url = true;
	                            	$path[0] = $path[1];
	                       		}
	                           	

	                   			
	                   			if($path[0]== 'tenantNextstage'){
	                   				switch ($path[2]) {
	                   					case "101":
		                   					$path[0] = 'leadAssign';
		                   					break;
	                   					case "102":
		                   					$path[0] = 'leadAssignedList';
		                   					break;
	                   					case "103":
		                   					$path[0] = 'inprogressList';
		                   					break;
	                   					case "104":
		                   					$path[0] = 'documentationList';
		                   					break;
	                   					case "105":
		                   					$path[0] = 'preliminaryApprovalList';
		                   					break;
	                   					case "106":
		                   					$path[0] = 'finalDocumentationList';
		                   					break;
	                   					case "107":
		                   					$path[0] = 'finalApproval';
		                   					break;
	                   					case "108":
		                   					$path[0] = 'wonList';
		                   					break;
	                   					case "109":
		                   					$path[0] = 'closedList';
		                   					break;
	                   				}
	                   			}
	                   			if($path[0]== 'landlordNextstage'){
	                   				switch ($path[2]) {
	                   					case "201":
		                   					$path[0] = 'landlordLead';
		                   					break;
	                   					case "205":
		                   					$path[0] = 'landlordContractLoss';
		                   					break;
	                   				}
	                   			}
	                   			if($path[0]== 'landlordcontractApprovalInfo'){
	                   				switch ($path[2]) {
	                   					case "203":
		                   					$path[0] = 'landlordcontractApprovalList';
		                   					break;
	                   					case "204":
		                   					$path[0] = 'landlordContractWon';
		                   					break;
	                   				}
	                   			}
	                   			if($path[0]== 'contractGeneration'){
                   					$path[0] = 'landlordContract';
	                   			}
	                   			
	                   		}else if(count($path) > 1){
						    
						       $path[0] = $path[0];
							   if($path[1] != 'create' && !is_numeric($path[1]))
							    $search_url = true; 
							    
							  if($path[1] == 'landlord')  
							   $path[0] = 'landlord';
						  		
							
							}else{
	                   			$path[0] = $path[0];
	                   			$search_url == false;
	                   		}



							if($search_url == true)
                            $search_key = $path[1];
                            else
                            $search_key = $path[0];
                            
                            if($search_key == 'submenu') 
                           $search_key =   $path[0] = 'menu';
                              
                            if($search_key == 'setPermission') 
                             $search_key = $path[0] = 'role';
                              
                            if($path[0]== 'contractCreation'){
                   				$path[0] = 'finalDocumentationList';
                   				$search_key = $path[0];
                   			}
                            if($path[0]=='pdc' && $path[2]=='edit' ){
                            	$path[0] = 'tenant-contract';
                   				$search_key = $path[0];
                            }
                            if($path[0]=='invoice' ){
                            	$path[0] = 'tenant-contract';
                   				$search_key = $path[0];
                            }
                            if(count($path) > 1){
	                            if($path[1]=='depositReceiptCreation' || $path[1]=='generalReceiptCreation'){
	                            	$path[0] = 'rentReceiptGeneration';
	                   				$search_key = $path[0];
	                            }
	                           
	                            if($path[0]=='tenant-rent-invoice-details' && $path[1]=='tenant-rent-invoice-details'){
									$path[0] = 'tenant-contract';
	                   				$search_key = $path[0];
								}
								if($path[0]=='receiptsRequestApproval' || $path[1]=='rent'){
	                            	$path[0] = 'receiptsRequestApproval';
	                   				$search_key = $path[0];
	                            }
								if($path[0]=='receiptsRequestApproval' || $path[1]=='deposit'){
	                            	$path[0] = 'receiptsRequestApproval';
	                   				$search_key = $path[0];
	                            }
	                            if($path[0]=='receiptsRequestApproval' || $path[1]=='general'){
	                            	$path[0] = 'receiptsRequestApproval';
	                   				$search_key = $path[0];
	                            }
								
							}
                        	@endphp    
                                    
							
	                       @foreach($sideMenu as $k=>$val) 							      
		                        @if($k == 1 && $path[0]== 'dashboard')
									@php	$flag_open = true; @endphp
								@else
									@php	$flag_open = false; @endphp
								@endif
		                        @include('layouts.menu_sub',['val' => $val])
		                        
		                        @php    $menu_flag = false;	  @endphp 
	                       
	                       @endforeach      


	                    @endauth
	                    </ul>
	                </div>
                </div>
            </div>
            <!-- end sidebar menu --> 
 @endauth
