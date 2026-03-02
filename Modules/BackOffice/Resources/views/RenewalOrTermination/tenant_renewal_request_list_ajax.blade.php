 							@forelse ($tenantRenewals as $tenantRenewal)
                               
                                <tr>
                                     
                                    <td>
										{{$loop->iteration}}
									</td>                                   
                                    <td>
										<a  class="no-link" title="Contract" href="{{route('tenantRenewal.show',$tenantRenewal->renewalOrTermination->id)}}" >
											{{$tenantRenewal->tenantContract->tenant_contract_no}}
										</a>
									</td>
                                    <td>
										<a  class="no-link" title="Note" href="{{route('tenantRenewal.show',$tenantRenewal->renewalOrTermination->id)}}" >
											{{$tenantRenewal->renewalOrTermination->renewal_or_termination_request_note}}
										</a>
									</td>
                                    <td>
				                        @can('renewal_request_view')
				                        <a href="{{route('tenantRenewal.show',$tenantRenewal->renewalOrTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
				                            <i class="fa fa-list "></i>
				                        </a>
				                        @endcan
				                        @can('renewal_request_edit') 
				                        <a title="Edit" href="{{route('tenantRenewal.edit',$tenantRenewal->renewalOrTermination->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
				                            <i class="fa fa-pencil"></i>
				                        </a>                                                   
				                        @endcan
				                        @can('renewal_request_delete')
				                        <a href="{{route('tenantRenewal.destroy',$tenantRenewal->renewalOrTermination->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
				                            <i class="fa fa-trash-o "></i>
				                        </a>
				                        @endcan                       
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
										$tenantRenewals->withPath($route);
																		
										if(!empty($request->contract_no))
										$tenantRenewals->appends(['contract_no' => $request->contract_no]);
										
										if(!empty($request->note))
										$tenantRenewals->appends(['note' => $request->note]);
										
										
										
										
										$fieldName =  app('request')->input('fieldName');
										
										if(!empty($fieldName)){
											$fieldName =  app('request')->input('fieldName');
											$operation =  app('request')->input('operation');
											$fieldValue =  app('request')->input('fieldValue');
											$logic =  app('request')->input('logic');
										$tenantRenewals->appends(['fieldName' => $fieldName,
										             'operation' => $operation,
										             'fieldValue' => $fieldValue,
										             'logic' => $logic,
										     ]);	
										}
																		
								        @endphp   
								        
								        {{$tenantRenewals->links()}}
								        
								        </td>                           
                  
                                 </tr>
                                 @endif

                                
                                
                                
                                
                                
