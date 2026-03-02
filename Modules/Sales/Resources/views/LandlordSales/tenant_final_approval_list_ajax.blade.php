@php $count = 1; @endphp
                                @forelse ($lists as $approval_list)
                                @php
                                  $current = 'finalApprovalList';
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
                                      $dat .= $contract->tenant_contract_start_date->format('d-m-Y').', '; 
                                      @endphp
                                      
                                    @endforeach
                                    {{rtrim($dat," ,")}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" >@foreach($approval_list->salesEnquiry->tenantContracts as $contract)
                                      {{number_format($contract->tenant_contract_rent,3)}}
                                    @endforeach</a></td>
                                    <td align="center">
                                    @can('close_tenant_enquiry')    
                                        <button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id = "CL" title="Close" datas-id ="{{$approval_list->salesEnquiry->work_flow_processes_code}}" datas-enid="{{$approval_list->salesEnquiry->id}}" >
                                            <i class="fa fa-times-circle"></i></button>
                                    @endcan
                                    @hasrole('super_admin')
                                     @can('edit_tenant_enquiry')
                                         <a title="Edit" href="{{route('enquiry.edit',$approval_list->salesEnquiry->id)}}" class="btn btn-tbl-edit btn-xs" >
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @endhasrole
                                        <!-- <input type="hidden" name="enquiryid" value="{{$approval_list->salesEnquiry->id}}" id="enquiryid">
                                        <input type="hidden" name="workflow_id" value="{{$approval_list->salesEnquiry->work_flow_processes_code}}" id="workflow_id"> -->                                  
                                        <a  title="Final Approval" href="{{route('leadAssign.nextStage',[$approval_list->salesEnquiry->id,$approval_list->salesEnquiry->work_flow_processes_code])}}" class="btn btn-tbl-view btn-xs ">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @can('reassign_tenant')
                                        <button title="Re-Assign" type="button" class="btn btn-tbl-user btn-xs assignLead" data-toggle="modal" data-target="#myModal" data-id = "{{$approval_list->salesEnquiry->id}}" datas-id= "{{$approval_list->salesEnquiry->work_flow_processes_code}}">
                                            <i class="fa fa-user" aria-hidden="true"></i>
                                        </button>
                                        @endcan
                                        
                                        
                                    </td>
                                </tr>
                                @php $count++; @endphp 
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
										$lists->withPath($route);
																		
										if(!empty($request->customer_name))
										$lists->appends(['customer_name' => $request->customer_name]);
										
										if(!empty($request->sales_enquiry_no))
										$lists->appends(['sales_enquiry_no' => $request->sales_enquiry_no]);
										
										if(!empty($request->phone))
										$lists->appends(['building_name_select' => $request->building_name_select]);
										
										if(!empty($request->stages))
										$lists->appends(['unit_select' => $request->unit_select]);
										
										if(!empty($request->type))
										$lists->appends(['start_dt' => $request->start_dt]);	
										
										if(!empty($request->sales_building_name))
										$lists->appends(['rent' => $request->rent]);
										
										if(!empty($request->duration))
										$lists->appends(['duration' => $request->duration]);
								
										if(!empty($request->assigned_person))
											$documntation_lists->appends(['duration' => $request->assigned_person]);
										
										$fieldName =  app('request')->input('fieldName');
										
										if(!empty($fieldName)){
											$fieldName =  app('request')->input('fieldName');
											$operation =  app('request')->input('operation');
											$fieldValue =  app('request')->input('fieldValue');
											$logic =  app('request')->input('logic');
										$lists->appends(['fieldName' => $fieldName,
										             'operation' => $operation,
										             'fieldValue' => $fieldValue,
										             'logic' => $logic,
										     ]);	
										}
																		
								        @endphp   
								        
								        {{$lists->links()}}
								        
								        </td>                           
                  
                                 </tr>
                                 @endif
