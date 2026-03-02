                 @forelse ($ComplaintEnquiries as $complaintChecklist)
                                @php
                                  $current = 'complaintSubAssignedList';
                                  Session::put('current', $current); 
                                                  
                                @endphp                                
                                <tr>
                                	<td>
                                	@can('sub_reassign')  	
			                          <input type = "checkbox" id = "switch-2" class = "mdl-switch__input sub_chk" name="groupAssign[]" value="" datas-id="" data-id="{{$complaintChecklist->complaint_enquiries_id}}" datassign-id="{{$complaintChecklist->sub_assigned_to}}">
		                          	@endcan
			                        </td>
                                	<td><a class="no-link" href="{{route('complaintSubAssigned.view',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}">{{$complaintChecklist->complaint_no}}</a>
			                        </td>
 			                        <td><a class="no-link" href="{{route('complaintSubAssigned.view',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}">{{$complaintChecklist->complaint_date->format('d/m/Y')}}</a></td>  
			                        <td><a class="no-link" href="{{route('complaintSubAssigned.view',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}">{{$complaintChecklist->complainer_name}}</a></td>
			                        <td><a class="no-link" href="{{route('complaintSubAssigned.view',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}">{!! substr($complaintChecklist->complaint_mob_no, 5) !!}</a></td>
			                        <td><a class="no-link" href="{{route('complaintSubAssigned.view',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}">{{$complaintChecklist->building_name}}</a></td>
			                        
			                        <td>
			                        @if(isset($complaintChecklist->unit_code))<a class="no-link" href="{{route('complaintSubAssigned.view',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}">{{$complaintChecklist->unit_code}}</a>
			                        @endif</td>   
                   
                                    <td><a class="no-link" href="{{route('complaintSubAssigned.view',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}">{{$complaintChecklist->assigned_name ?? ''}}</a></td>   

                                    <td><a class="no-link" href="{{route('complaintSubAssigned.view',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}">{{$complaintChecklist->sub_assigned_name?? ''}}</a></td>   


			                       


			                        <td>		                        	
									<span class="label {{$complaintChecklist->ticket_status_class}} label-mini">
										<!-- {{$complaintChecklist->complaintEnquiry->ComplaintStatusName}} -->
										{{$complaintChecklist->ticket_status_name}}
									</span>
					                </td>
					                <td>
			                        @can('complaint_subassigned_view')
			                        <a href="{{route('complaintSubAssigned.view',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}" title="View" class="btn btn-tbl-view btn-xs">
			                            <i class="fa fa-eye "></i>
			                        </a>
			                        @endcan
			                         @if(auth()->user()->hasRole(['maintenance_coordinator','super_admin']) && auth()->user()->can('complaint_enquiries_edit') && $complaintChecklist->complaintTickets->count() == $complaintChecklist->complaintTicketsAll->count())			                       
			                        <a title="Edit" href="{{route('complaint.edit',$complaintChecklist->complaint_enquiries_id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
			                            <i class="fa fa-pencil"></i>
			                        </a>   
			                        @endif
			                     	@can('close')
									<button title="Close" type="button" class="btn btn-tbl-delete btn-xs close_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$complaintChecklist->complaint_enquiries_id}}" datas-id="" dataa_id = "" datassign-id="{{$complaintChecklist->sub_assigned_to}}" data-backdrop="static" data-keyboard="false">
			                          <i class="fa fa-times-circle "></i>
			                        </button>	 
			                        @endcan
			                        @can('sub_reassign')                       
			                   		<button type="button" class="btn btn btn-tbl-info btn-xs SubReAssign" title="Reassign" data-toggle="modal" data-target="#myModal"  datas-id="" datass-id="" data-id="{{$complaintChecklist->complaint_enquiries_id}}" datassign-id="{{$complaintChecklist->sub_assigned_to}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-user "></i></button>
			                   		@endcan

			                   		@if(auth()->user()->can('generate_service_report') && $complaintChecklist->ticket_status > 0)
			                   		<a title="Service Report" href="{{route('technicianServiceReport',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}" class="btn btn-tbl-general btn-xs">
										  <i class="fa fa-cog"></i>
									</a>
                  					@endif
                  					@if(auth()->user()->can('service_report_view') && !auth()->user()->can('generate_service_report') )
			                   		<a title="Service Report View" href="{{route('technicianServiceReportView',[$complaintChecklist->complaint_enquiries_id,$complaintChecklist->sub_assigned_to])}}" class="btn btn-tbl-general btn-xs">
										  <i class="fa fa-cog"></i>
									</a>
                  					@endif
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
                                  <td colspan="5" id="pagination_ajax">
								   {{$ComplaintEnquiries->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}	

								    <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $ComplaintEnquiries])         
                               </div>							        
								  </td>
                                 </tr>
                                 @endif


