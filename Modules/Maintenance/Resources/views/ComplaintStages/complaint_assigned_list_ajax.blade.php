                 @forelse ($ComplaintEnquiries as $complaintProcess)
                                @php
                                  $current = 'complaintAssignedList';
                                  Session::put('current', $current); 
                                                      
                                @endphp                                
                                <tr>
                                	<td>@can('sub_assign')
                                	@if(isset($complaintProcess->employee_name))
			                          <input type = "checkbox" id = "switch-2" class = "mdl-switch__input sub_chk" name="groupAssign[]" value="{{$complaintProcess->id}}" datas-id="{{$complaintProcess->work_flow_processes_code}}" data-id="{{$complaintProcess->complaint_enquiries_id}}">
                               
                             
		                          	@endif
		                          	@endcan
			                        </td>
			                        {{--
			                        <!-- <td><a class="no-link" href="{{route('complaintAssigned.view',$complaintProcess->complaintChecklist->id)}}">{{$complaintProcess->complaintEnquiry->complaint_no}}</a></td>  -->  
			                        --}}
			                        <td><a class="no-link" href="{{route('complaintAssigned.view',$complaintProcess->id)}}">{{$complaintProcess->complaint_ticket_no}}</a></td> 
			                        <td><a class="no-link" href="{{route('complaintAssigned.view',$complaintProcess->id)}}">{{$complaintProcess->complaintEnquiry->complaint_date->format('d/m/Y')}}</a></td>

			                        <td><a class="no-link" href="{{route('complaintAssigned.view',$complaintProcess->id)}}">{!! substr($complaintProcess->complaintEnquiry->complaint_mob_no, 5) !!}</a></td>
			                        <td><a class="no-link" href="{{route('complaintAssigned.view',$complaintProcess->id)}}">{{$complaintProcess->complaintEnquiry->building->building_name}}</a></td>
			                        <td>
			                        @if(isset($complaintProcess->complaintEnquiry->unit_id))<a class="no-link" href="{{route('complaintAssigned.view',$complaintProcess->id)}}">{{$complaintProcess->complaintEnquiry->Unit->unit_code}}</a>
			                        @endif</td> 

			                        <td><a class="no-link" href="{{route('complaintAssigned.view',$complaintProcess->id)}}">{{$complaintProcess->work->works_code}}</a></td>
			                           
			                        <!-- <td>@if($complaintProcess->assigned_to)
			                          @if(isset($complaintProcess->employee_name))
			                            <span class="label label-success label-mini">{{'Technical Head'}}</span>
			                          @else
			                             <span class="label label-danger label-mini">{{'Sub Contractor'}}</span>
			                          @endif
			                        @endif
			                        </td> -->
			                        <td><a class="no-link" href="{{route('complaintAssigned.view',$complaintProcess->id)}}">@if($complaintProcess->assigned_to)
			                          @if(isset($complaintProcess->employee_name))
			                            {{$complaintProcess->concat}}
			                          @else
			                            {{$complaintProcess->concat}}
			                          @endif
			                        @endif
			                        </a>
			                        </td>
			                        <td>		                        	
									<span class="label {{$complaintProcess->ticket_status_class}} label-mini">{{$complaintProcess->TicketStatusName}}</span>
					                </td>
			                      	
					                <td>
			                        @can('complaint_assigned_view')
			                        
			                        <a href="{{route('complaintAssigned.view',$complaintProcess->id)}}" title="View" class="btn btn-tbl-view btn-xs">
			                            <i class="fa fa-eye "></i>
			                        </a>

			                        @endcan

			                        @if(auth()->user()->hasRole(['maintenance_coordinator','super_admin']) && auth()->user()->can('complaint_enquiries_edit') && $complaintProcess->ticket_status == 0)
			                        <a title="Edit" href="{{route('complaint.edit',$complaintProcess->complaint_enquiries_id)}}" class="btn btn-tbl-edit btn-xs">
			                            <i class="fa fa-pencil"></i>
			                        </a>   
			                        @endif                                                
			                         
			                        @can('close')
			                        <button title="Close" type="button" class="btn btn-tbl-delete btn-xs close_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$complaintProcess->complaint_enquiries_id}}" datas-id= "{{$complaintProcess->id}}" dataa_id = "{{$complaintProcess->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false">
			                          <i class="fa fa-times-circle "></i>
			                        </button>
			                        @endcan
									
			                        @if($complaintProcess->assigned_to_type == 0)
			                        @can('sub_assign')
								   	<button type="button" class="btn btn btn-tbl-violet btn-xs SubAssign " title="Assign" data-toggle="modal" data-target="#myModal" datas-id="{{$complaintProcess->id}}" datass-id="{{$complaintProcess->work_flow_processes_code}}" data-id="{{$complaintProcess->complaint_enquiries_id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-user "></i></button>
		                            @endcan
		                           
		                            @can('reassign')
			                        <button type="button" class="btn btn btn-tbl-info btn-xs ReAssign " title="Reassign" data-toggle="modal" data-target="#myModal"  datas-id="{{$complaintProcess->id}}" datass-id="{{$complaintProcess->work_flow_processes_code}}" data-id="{{$complaintProcess->complaint_enquiries_id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-user "></i></button>
			                        @endcan
			                        @else
			                        @can('generate_service_report')
			                        <a title="Service Report" href="{{route('contractorServiceReport',$complaintProcess->id)}}" class="btn btn-tbl-general btn-xs">
										  <i class="fa fa-cog"></i>
									</a>
									@endcan
									@can('reassign')
			                        <button type="button" class="btn btn btn-tbl-info btn-xs ReAssign " title="Reassign" data-toggle="modal" data-target="#myModal"  datas-id="{{$complaintProcess->id}}" datass-id="{{$complaintProcess->work_flow_processes_code}}" data-id="{{$complaintProcess->complaint_enquiries_id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-user "></i></button>
			                        @endcan
								   	@endif

			                        @role('maintenance_coordinator')
			                        @if($complaintProcess->assigned_to_type == 0)
			                        @if($complaintProcess->sub_assigned_to == "")
									<a title="Reminder" href="{{route('complaint.assignreminder',$complaintProcess->id)}}" class="btn btn-tbl-general btn-xs">
										  <i class="fa fa-bell"></i>
									</a>
									@endif
									@endif
									@endcan
								                    
			                        </td>
                    			</tr>
                                
                                @empty
                                <tr>
                                    <td colspan="10" align="center">
                                    <p>No Record</p>
                                   </td>
                                </tr>
                                @endforelse
                                
                                
                                
                                
 @if(isset($request->ajax))	
 
<tr>			          
 <td colspan="9" id="pagination_ajax"> 	
 {{$ComplaintEnquiries->withPath($route)->appends(\Request::except(['page','_token','ajax']))
                      ->links()}}

                      <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $ComplaintEnquiries])         
                               </div>
        
</td>                           

 </tr>
 @endif
