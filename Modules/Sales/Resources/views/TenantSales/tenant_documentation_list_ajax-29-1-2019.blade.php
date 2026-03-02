	@php $count = 1; @endphp
                                @forelse ($documntation_lists as $documntation_list)
                                @php
                                  $current = 'documentationList';
                                  Session::put('current', $current);                                 
                                @endphp
                                <tr>
									<td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->salesEnquiry->id,$documntation_list->salesEnquiry->work_flow_processes_code])}}">{{$documntation_lists->perPage()*($documntation_lists->currentPage()-1)+$count}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->salesEnquiry->id,$documntation_list->salesEnquiry->work_flow_processes_code])}}">{{$documntation_list->salesEnquiry->sales_enquiry_name}}</a></td>
                                   
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->salesEnquiry->id,$documntation_list->salesEnquiry->work_flow_processes_code])}}">{{$documntation_list->salesEnquiry->sales_email}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->salesEnquiry->id,$documntation_list->salesEnquiry->work_flow_processes_code])}}">{{$documntation_list->salesEnquiry->sales_mobile_no}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->salesEnquiry->id,$documntation_list->salesEnquiry->work_flow_processes_code])}}">{{$documntation_list->salesEnquiry->enquirySource->enquiry_sources_name}}</a></td>
                                    <td>
                                    @can('close_tenant_enquiry')
                                        <button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id = "CL" title="Close" datas-id ="{{$documntation_list->salesEnquiry->work_flow_processes_code}}" datas-enid="{{$documntation_list->salesEnquiry->id}}" >
                                            <i class="fa fa-times-circle"></i></button>
                                    @endcan
                                       <!--  <input type="hidden" name="enquiryid" value="{{$documntation_list->salesEnquiry->id}}" id="enquiryid">
                                        <input type="hidden" name="workflow_id" value="{{$documntation_list->salesEnquiry->work_flow_processes_code}}" id="workflow_id"> -->
                                    @can('edit_enquiry')
                                         <a title="Edit" href="{{route('enquiry.edit',$documntation_list->salesEnquiry->id)}}" class="btn btn-tbl-edit btn-xs" >
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan                                    
                                        <a  title="Documentation" href="{{route('leadAssign.nextStage',[$documntation_list->salesEnquiry->id,$documntation_list->salesEnquiry->work_flow_processes_code])}}" class="btn btn-tbl-view btn-xs ">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @can('reassign_tenant')
                                        <button title="Re-Assign" type="button" class="btn btn-tbl-user btn-xs assignLead" data-toggle="modal" data-target="#myModal" data-id = "{{$documntation_list->salesEnquiry->id}}" datas-id= "{{$documntation_list->salesEnquiry->work_flow_processes_code}}">
                                            <i class="fa fa-user" aria-hidden="true"></i>
                                        </button>
                                    @endcan

                                        
                                        
                                        
                                    </td>
                                </tr>
                                 @php $count++; @endphp 
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
										$documntation_lists->withPath($route);
																		
										if(!empty($request->customer_name))
										$documntation_lists->appends(['customer_name' => $request->customer_name]);
										
										if(!empty($request->email))
										$documntation_lists->appends(['email' => $request->email]);
										
										if(!empty($request->phone))
										$documntation_lists->appends(['phone' => $request->phone]);
										
										if(!empty($request->stages))
										$documntation_lists->appends(['stages' => $request->stages]);
										
										if(!empty($request->type))
										$documntation_lists->appends(['type' => $request->type]);	
										
										if(!empty($request->sales_building_name))
										$documntation_lists->appends(['sales_building_name' => $request->sales_building_name]);
										
										
										$fieldName =  app('request')->input('fieldName');
										
										if(!empty($fieldName)){
											$fieldName =  app('request')->input('fieldName');
											$operation =  app('request')->input('operation');
											$fieldValue =  app('request')->input('fieldValue');
											$logic =  app('request')->input('logic');
										$documntation_lists->appends(['fieldName' => $fieldName,
										             'operation' => $operation,
										             'fieldValue' => $fieldValue,
										             'logic' => $logic,
										     ]);	
										}
																		
								        @endphp   
								        
								        {{$documntation_lists->links()}}
								        
								        </td>                           
                  
                                 </tr>
                                 @endif
