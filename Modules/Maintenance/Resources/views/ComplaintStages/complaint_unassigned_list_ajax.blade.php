                 @forelse ($ComplaintEnquiries as $complaintEnquiry)
                                @php
                                  $current = 'complaintStage.index';
                                  Session::put('current', $current); 
                                                      
                                @endphp                                
                                <tr>
			                        <td><a class="no-link" href="{{route('complaintUnassigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->complaint_no}}</a></td>  
			                        <td><a class="no-link" href="{{route('complaintUnassigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->complaint_date->format('d/m/Y')}}</a></td>
			                        <td><a class="no-link" href="{{route('complaintUnassigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->complainer_name}}</a></td>
			                        <td><a class="no-link" href="{{route('complaintUnassigned.view',$complaintEnquiry->id)}}">{!! substr($complaintEnquiry->complaint_mob_no, 5) !!}</a></td>
			                        <td><a class="no-link" href="{{route('complaintUnassigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->building->building_name}}</a></td>
			                        
			                        <td>
			                        @if($complaintEnquiry->unit_id)<a class="no-link" href="{{route('complaintUnassigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->Unit->unit_code}}</a>
			                        @endif</td>   
			                        <td><a class="no-link" href="{{route('complaintUnassigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->location->locations_name}}</a></td> 
			                        
			                        <td><a class="no-link" href="{{route('complaintUnassigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->priority_status_name}}</a>
					                </td>
					               

					                <td>		                        	
									<span class="label {{$complaintEnquiry->tenant_status_class}} label-mini">{{$complaintEnquiry->TenantStatusName}}</span>
					                </td>
					                <td>
			                        @can('complaint_unassigned_view')
			                        <a href="{{route('complaintUnassigned.view',$complaintEnquiry->id)}}" title="View" class="btn btn-tbl-view btn-xs">
			                            <i class="fa fa-eye "></i>
			                        </a>
			                        @endcan
			                        @can('complaint_enquiries_edit') 
			                        @if($complaintEnquiry->complaintTickets->count() == 0)
			                        <a title="Edit" href="{{route('complaint.edit',$complaintEnquiry->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
			                            <i class="fa fa-pencil"></i>
			                        </a>   
			                        @endif                                                
			                        @endcan
			                        @can('close')
			                        <button title="Close" type="button" class="btn btn-tbl-delete btn-xs complaint_close_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$complaintEnquiry->id}}" datas-id="" data-backdrop="static" data-keyboard="false">
			                          <i class="fa fa-times-circle "></i>
			                        </button>
			                     	@endcan
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
                                    <td colspan="9" align="center">
                                    <p>No Record</p>
                                   </td>
                                </tr>
                                @endforelse
                                
                                
                                
                                
                                 @if(isset($request->ajax))	                                 
                                <tr>		          
                                  <td colspan="9" id="pagination_ajax"> 								{{$ComplaintEnquiries->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}

                                  <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $ComplaintEnquiries])         
                               </div>
								  </td>
                                 </tr>
                                 @endif
