                 @forelse ($assigned_lists as $assigned_list)
                                @php
                                  $current = 'leadAssign.assignedList';
                                  Session::put('current', $current);                                 
                                @endphp                                
                                <tr>
									<td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->sales_enquiry_no}}</a></td>
                                    
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->created_at->format('d-m-Y')}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->sales_enquiry_name}}</td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->sales_mobile_no}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->unitTypes->implode('unit_types_name',', ')}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->locations->implode('locations_name',', ') ?? 'NA'}}</a></td>
                                    <td>
									@if(isset($assigned_list->salesEnquiry->sales_note))
										<a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">
											{{ str_limit($assigned_list->salesEnquiry->sales_note, $limit = 15, $end = '...') }}
										
										</a>
									@endif	
									</td>
                                    <td>
                                    @can('close_tenant_enquiry') 
                                       <button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id = "CL" title="Close" datas-id ="{{$assigned_list->salesEnquiry->work_flow_processes_code}}" datas-enid="{{$assigned_list->salesEnquiry->id}}" >
                                            <i class="fa fa-times-circle"></i></button>
                                    @endcan
                                    @can('edit_enquiry')
                                         <a title="Edit" href="{{route('enquiry.edit',$assigned_list->salesEnquiry->id)}}" class="btn btn-tbl-edit btn-xs" >
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                        <!-- <input type="hidden" name="enquiryid" value="{{$assigned_list->salesEnquiry->id}}" id="enquiryid">
                                        <input type="hidden" name="workflow_id" value="{{$assigned_list->salesEnquiry->work_flow_processes_code}}" id="workflow_id"> -->                                  
                                        <a  title="Inprogress" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}" class="btn btn-tbl-view btn-xs ">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @can('reassign_tenant') 
                                        <button title="Re-Assign" type="button" class="btn btn-tbl-user btn-xs assignLead" data-toggle="modal" data-target="#myModal" data-id = "{{$assigned_list->salesEnquiry->id}}" datas-id= "{{$assigned_list->salesEnquiry->work_flow_processes_code}}">
                                            <i class="fa fa-user" aria-hidden="true"></i>
                                        </button>
                                    @endcan
                                    
                                    @if($assigned_list->created_by == Auth::user()->id)
                                     <a title="Reminder" href="{{route('leadAssign.reminder',$assigned_list->salesEnquiry->id)}}" class="btn btn-tbl-general btn-xs">
										  <i class="fa fa-bell"></i>
									 </a>
									@endif
                                    
                                    
                                    
                                        <form id="close-form" action="" method="POST">
                                             {{csrf_field()}}
                                            <input type="hidden" name="action_key" value="CL">
                                            <input type="hidden" name="workflow_id" value="{{$assigned_list->salesEnquiry->work_flow_processes_code}}">
                                            <input style="display: none;" type="submit">
                                        </form>
                                        
                                        
                                    </td>
                                </tr>
                                
                                @empty
                                <tr>
                                    <td colspan="6" align="center">
                                    <p>No records</p>
                                   </td>
                                </tr>
                                @endforelse
                                
                                
                                
                                
                                 @if(isset($request->ajax))	
                                 
                                <tr>
									
					            			
                   							          
                                  <td colspan="5" id="pagination_ajax"> 
										@php										 
										
										if(isset($request->ajax))	
										$assigned_lists->withPath($route);
																		
										if(!empty($request->customer_name))
										$assigned_lists->appends(['customer_name' => $request->customer_name]);
										
										if(!empty($request->email))
										$assigned_lists->appends(['email' => $request->email]);
										
										if(!empty($request->phone))
										$assigned_lists->appends(['phone' => $request->phone]);
										
										if(!empty($request->stages))
										$assigned_lists->appends(['stages' => $request->stages]);
										
										if(!empty($request->type))
										$assigned_lists->appends(['type' => $request->type]);	
										
										if(!empty($request->sales_building_name))
										$assigned_lists->appends(['sales_building_name' => $request->sales_building_name]);
										
										
										$fieldName =  app('request')->input('fieldName');
										
										if(!empty($fieldName)){
											$fieldName =  app('request')->input('fieldName');
											$operation =  app('request')->input('operation');
											$fieldValue =  app('request')->input('fieldValue');
											$logic =  app('request')->input('logic');
										$assigned_lists->appends(['fieldName' => $fieldName,
										             'operation' => $operation,
										             'fieldValue' => $fieldValue,
										             'logic' => $logic,
										     ]);	
										}
																		
								        @endphp   
								        
								        {{$assigned_lists->links()}}
								        
								        </td>                           
                  
                                 </tr>
                                 @endif
