@php $count = 1; @endphp
                                @forelse ($finalDocumentationLists as $approval_list)
                                @php
                                  $current = 'finalDocumentationList';
                                  Session::put('current', $current); 
                                  $val = ''; $bul =''; $dat = '';                                
                                @endphp
                                <tr>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" >{{$approval_list->salesEnquiry->sales_enquiry_no}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" >{{$approval_list->salesEnquiry->created_at->format('d/m/Y')}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" >{{$approval_list->salesEnquiry->sales_enquiry_name}}</a></td>
                                     <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" >@foreach($approval_list->salesEnquiry->tenantContracts as $contract)
                                      @php 
                                      $bul .= $contract->building->building_name.', ';
                                      @endphp
                                      
                                   @endforeach{{rtrim($bul," ,")}}</a></td>
                                      <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" >@foreach($approval_list->salesEnquiry->tenantContracts as $contract)
                                      @php 
                                      $val .= $contract->unit->unit_code.', '; 
                                      @endphp
                                      
                                    @endforeach{{rtrim($val," ,")}}</a></td>
                                       <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" >{{$approval_list->salesEnquiry->tenantContracts->implode('tenant_contract_duration',', ')?? 'NA'}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" >@foreach($approval_list->salesEnquiry->tenantContracts as $contract)
                                      @php 
                                      $dat .= $contract->tenant_contract_start_date->format('d/m/Y').', '; 
                                      @endphp
                                      
                                    @endforeach
                                    {{rtrim($dat," ,")}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" >@foreach($approval_list->salesEnquiry->tenantContracts as $contract)
                                      {{number_format($contract->tenant_contract_rent,3)}}
                                    @endforeach</a></td>
                                    <td align="center">
                                        <a  title="Documentation" href="{{route('leadAssign.tenantPendingStageInfo',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" class="btn btn-tbl-view btn-xs ">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @php $count++; @endphp 
                                @empty
                                <tr>
                                    <td colspan="9" align="center">
                                    <p>No Record</p>
                                   </td>
                                </tr>
                                @endforelse
                                
                                
                                
                                     @if(isset($request->ajax))	
                                 
                                <tr>
									
					            			
                   							          
                                  <td colspan="5" id="pagination_ajax"> 
										@php										 
										
										if(isset($request->ajax))	
										$finalDocumentationLists->withPath($route);
																		
										if(!empty($request->customer_name))
										$finalDocumentationLists->appends(['customer_name' => $request->customer_name]);
										
										if(!empty($request->sales_enquiry_no))
										$finalDocumentationLists->appends(['sales_enquiry_no' => $request->sales_enquiry_no]);
										
										if(!empty($request->phone))
										$finalDocumentationLists->appends(['building_name_select' => $request->building_name_select]);
										
										if(!empty($request->stages))
										$finalDocumentationLists->appends(['unit_select' => $request->unit_select]);
										
										if(!empty($request->type))
										$finalDocumentationLists->appends(['start_dt' => $request->start_dt]);	
										
										if(!empty($request->sales_building_name))
										$finalDocumentationLists->appends(['rent' => $request->rent]);
										
										if(!empty($request->created_at))
										$finalDocumentationLists->appends(['rent' => $request->created_at]);
										
										if(!empty($request->duration))
											$finalDocumentationLists->appends(['duration' => $request->duration]);
											
										if(!empty($request->assigned_person))
											$finalDocumentationLists->appends(['duration' => $request->assigned_person]);
										
										
										$fieldName =  app('request')->input('fieldName');
										
										if(!empty($fieldName)){
											$fieldName =  app('request')->input('fieldName');
											$operation =  app('request')->input('operation');
											$fieldValue =  app('request')->input('fieldValue');
											$logic =  app('request')->input('logic');
										$finalDocumentationLists->appends(['fieldName' => $fieldName,
										             'operation' => $operation,
										             'fieldValue' => $fieldValue,
										             'logic' => $logic,
										     ]);	
										}
																		
								        @endphp   
								        
								        {{$finalDocumentationLists->links()}}
								        
								        </td>                           
                  
                                 </tr>
                                 @endif
