                 @forelse ($ComplaintEnquiries as $complaintEnquiry)
                                @php
                                  $current = 'complaint.index';
                                  Session::put('current', $current); 
                                                      
                                @endphp                                
                                <tr>
			                        <td><a class="no-link" href="{{route('complaint.show',$complaintEnquiry->id)}}">{{$complaintEnquiry->complaint_no}}</a></td>  
			                        <td><a class="no-link" href="{{route('complaint.show',$complaintEnquiry->id)}}">
{{ \Carbon\Carbon::createFromTimeStamp(strtotime($complaintEnquiry->created_at))->diffForHumans() }}
			                        	 
			                        </a></td>
			                        <td><a class="no-link" href="{{route('complaint.show',$complaintEnquiry->id)}}">{{$complaintEnquiry->complainer_name}}</a></td>
			                        <td><a class="no-link" href="{{route('complaint.show',$complaintEnquiry->id)}}">{{$complaintEnquiry->complaint_mob_no}}</a></td>
			                        <td><a class="no-link" href="{{route('complaint.show',$complaintEnquiry->id)}}">{{$complaintEnquiry->building->building_name}}</a></td>
			                        <td>@if(isset($complaintEnquiry->unit_id))<a class="no-link" href="{{route('complaint.show',$complaintEnquiry->id)}}">{{$complaintEnquiry->Unit->unit_no}}</a>@endif</td>  
			                        <td><a class="no-link" href="{{route('complaint.show',$complaintEnquiry->id)}}">{{$complaintEnquiry->location->locations_name}}</a></td>

			                        <td>		                        	
									<a class="no-link" href="{{route('complaint.show',$complaintEnquiry->id)}}">{{$complaintEnquiry->priority_status_name}}</a>
					                </td>
					               
			                          
			                        <td>		                        	
									<span class="label {{$complaintEnquiry->complaint_status_class}} label-mini">{{$complaintEnquiry->ComplaintStatusName}}</span>
					                </td>
					                <td>
			                        @can('complaint_enquiries_view')
			                        <a href="{{route('complaint.show',$complaintEnquiry->id)}}" title="View" class="btn btn-tbl-view btn-xs">
			                            <i class="fa fa-eye "></i>
			                        </a>
			                        @endcan
			                        
			                        @if(auth()->user()->hasRole(['maintenance_coordinator','super_admin']) && auth()->user()->can('complaint_enquiries_edit') &&     $complaintEnquiry->complaint_status == 0)
			                        <a title="Edit" href="{{route('complaint.edit',$complaintEnquiry->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
			                            <i class="fa fa-pencil"></i>
			                        </a>  
			                        @endif                                                
			                        
			                        {{-- @can('complaint_enquiries_destroy')
			                        @if($complaintEnquiry->complaint_status == 0) --}}
			                       <!--  <a href="{{route('complaint.destroy',$complaintEnquiry->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
			                            <i class="fa fa-trash-o "></i>
			                        </a> -->
			                        {{-- @endif
			                        @endcan --}}
			                        @role('call_center')
			                        @if($complaintEnquiry->complaintTickets->count()>0)
									<a title="Reminder" href="{{route('complaint.reminder',$complaintEnquiry->id)}}" class="btn btn-tbl-general btn-xs">
										  <i class="fa fa-bell"></i>
									</a>
									@endif
								   	@endrole                       
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
										@php
										
										$fieldName =  app('request')->input('fieldName');
										
										if(!empty($fieldName)){
											$fieldName =  app('request')->input('fieldName');
											$operation =  app('request')->input('operation');
											$fieldValue =  app('request')->input('fieldValue');
											$logic =  app('request')->input('logic');
										$ComplaintEnquiries->appends(['fieldName' => $fieldName,
										             'operation' => $operation,
										             'fieldValue' => $fieldValue,
										             'logic' => $logic,
										     ]);	
										}
																		
								        @endphp   
								        
								        {{$ComplaintEnquiries->appends(\Request::except(['page','_token','route','ajax']))->links()}}  

								         <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $ComplaintEnquiries])         
                               </div>
								        
								        </td>                           
                  
                                 </tr>
                                 @endif
