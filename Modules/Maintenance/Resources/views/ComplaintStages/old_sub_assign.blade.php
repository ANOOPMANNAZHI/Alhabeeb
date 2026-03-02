                 @forelse ($ComplaintEnquiries as $complaintEnquiry)
                                @php
                                  $current = 'complaintSubAssignedList';
                                  Session::put('current', $current); 
                                  $tick = $complaintEnquiry->complaintTicketsAll->pluck('id');
                                  $work_flow = $complaintEnquiry->complaintTicketsAll->pluck('work_flow_processes_code');                   
                                @endphp                                
                                <tr>
                                	<td>
                                		
			                          <input type = "checkbox" id = "switch-2" class = "mdl-switch__input sub_chk" name="groupAssign[]" value="{{$tick}}" datas-id="{{$work_flow}}" data-id="{{$complaintEnquiry->id}}">
		                          	
			                        </td>
                                	<td><a class="no-link" href="{{route('complaintSubAssigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->complaint_no}}</a>
			                        </td>
 			                        <td><a class="no-link" href="{{route('complaintSubAssigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->complaint_date->format('d/m/Y')}}</a></td>  
			                        <td><a class="no-link" href="{{route('complaintSubAssigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->complainer_name}}</a></td>
			                        <td><a class="no-link" href="{{route('complaintSubAssigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->complaint_mob_no}}</a></td>
			                        <td><a class="no-link" href="{{route('complaintSubAssigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->building->building_name}}</a></td>
			                        <td><a class="no-link" href="{{route('complaintSubAssigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->location->locations_name}}</a></td>
			                        <td><a class="no-link" href="{{route('complaintSubAssigned.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->Unit->unit_code}}</a></td>    
			                        <td>		                        	
									<span class="label {{$complaintEnquiry->complaint_status_class}} label-mini">{{$complaintEnquiry->ComplaintStatusName}}</span>
					                </td>
					                <td>
			                        
			                        <a href="{{route('complaintSubAssigned.view',$complaintEnquiry->id)}}" title="View" class="btn btn-tbl-view btn-xs">
			                            <i class="fa fa-eye "></i>
			                        </a>
			                        
			                        @can('complaint_enquiries_edit') 
			                        @if($complaintEnquiry->complaint_status == 0)
			                        <a title="Edit" href="{{route('complaint.edit',$complaintEnquiry->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
			                            <i class="fa fa-pencil"></i>
			                        </a>   
			                        @endif                                                
			                        @endcan
			                     	
									<button title="Close" type="button" class="btn btn-tbl-delete btn-xs close_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$complaintEnquiry->id}}" datas-id= "" dataa_id = "">
			                          <i class="fa fa-times-circle "></i>
			                        </button>	                        
			                   		<button type="button" class="btn btn btn-tbl-info btn-xs SubReAssign" title="Reassign" data-toggle="modal" data-target="#myModal"  datas-id="{{$tick}}" datass-id="{{$work_flow}}" data-id="{{$complaintEnquiry->id}}"><i class="fa fa-user "></i></button>

			                   		<a title="Service Report" href="{{route('technicianServiceReport',$complaintEnquiry->id)}}" class="btn btn-tbl-general btn-xs">
										  <i class="fa fa-cog"></i>
									</a>
                  
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
										
										if(isset($request->ajax))	
										$ComplaintEnquiries->withPath($route);
																		
										if(!empty($request->complainer_name))
		                                $ComplaintEnquiries->appends(['complainer_name' => $request->complainer_name]);
		                            
		                                if(!empty($request->complaint_no))
		                                $ComplaintEnquiries->appends(['complaint_no' => $request->complaint_no]);
		                                
		                                if(!empty($request->complaint_mob_no))
		                                $ComplaintEnquiries->appends(['complaint_mob_no' => $request->complaint_mob_no]);
		                                
		                                if(!empty($request->building_id))
		                                $ComplaintEnquiries->appends(['building_id' => $request->building_id]);

		                                if(!empty($request->complaint_date))
		                                $ComplaintEnquiries->appends(['complaint_date' => $request->complaint_date]);

		                                if(!empty($request->unit))
		                                $ComplaintEnquiries->appends(['unit' => $request->unit]);

		                                if(!empty($request->location))
		                                $ComplaintEnquiries->appends(['location' => $request->location]);
		                                
		                                 
		                                if(!empty($request->status))
		                                $ComplaintEnquiries->appends(['status' => $request->status]); 
										
										
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
								        
								        {{$ComplaintEnquiries->links()}}
								        
								        </td>                           
                  
                                 </tr>
                                 @endif


