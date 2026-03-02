 							@forelse ($tenantRenewals as $tenantRenewal)
                               
                                <tr>
                                       
                                   <td>
											@if($tenantRenewal->old_contract)
												<a class="no-link" href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View">
												{{$tenantRenewal->old_contract}} </a>
											@else 
												<a class="no-link"href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View Previous Contract" >{{$tenantRenewal->oldTenantContract->tenant_contract_no}} </a> 
											@endif
									</td> 
									<td>

											@if($tenantRenewal->new_contract)
											<a class="no-link" href="{{route('newContractShow',[$tenantRenewal->id,$tenantRenewal->work_flow_processes_code])}}" title="View">
											{{$tenantRenewal->new_contract}}
											</a> 
											{{-- @else <a class="no-link"href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View Previous Contract" > {{$tenantRenewal->oldTenantContract->tenant_contract_old_no}} 
											</a> --}}
											@endif
										
									</td> 
                                    <td>
                                    	
                                    		@if($tenantRenewal->new_contract_id)
                                    		<a class="no-link" href="{{route('newContractShow',[$tenantRenewal->newTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" title="View">
											{{$tenantRenewal->newTenantContract->building->building_name}} 
											</a>
											@else
											<a class="no-link"href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View Previous Contract" > {{$tenantRenewal->oldTenantContract->building->building_name}}
											</a>
											@endif
										
                                    </td>  
                                    <td>
                                    	
											@if($tenantRenewal->new_contract_id)
											<a class="no-link" href="{{route('newContractShow',[$tenantRenewal->newTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" title="View">
											{{$tenantRenewal->newTenantContract->unit->unit_no}} </a>
											@else 
											<a class="no-link"href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View Previous Contract" >{{$tenantRenewal->oldTenantContract->unit->unit_no}}</a> @endif
										
                                    </td>  
                                    <td>
                                    	
											@if($tenantRenewal->new_contract_id)
											<a class="no-link" href="{{route('newContractShow',[$tenantRenewal->newTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" title="View">
											{{$tenantRenewal->newTenantContract->tenant_contract_muncipality_agr_no}} </a>
											@else 
											<a class="no-link"href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View Previous Contract" > {{$tenantRenewal->oldTenantContract->tenant_contract_muncipality_agr_no}} </a>
											@endif
										
                                    </td>
                                    <td>
                                    	
											@if($tenantRenewal->new_contract_id)
											<a class="no-link" href="{{route('newContractShow',[$tenantRenewal->newTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" title="View">
											{{$tenantRenewal->newTenantContract->tenant_contract_start_date->format('d/m/Y')}} </a>
											@else 
											<a class="no-link"href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View Previous Contract" >
											{{$tenantRenewal->oldTenantContract->tenant_contract_start_date->format('d/m/Y')}} </a> 
											@endif
										
                                    </td>        
								
                                    <td>
												
										    
											@if($tenantRenewal->new_contract_id)
											<a class="no-link" href="{{route('newContractShow',[$tenantRenewal->newTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" title="View">
											{{$tenantRenewal->newTenantContract->tenant_contract_valid_to_date->format('d/m/Y')}} </a> 
											@else 
											<a class="no-link"href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View Previous Contract" >
											{{$tenantRenewal->oldTenantContract->tenant_contract_valid_to_date->format('d/m/Y')}} </a>
											@endif
																					
									</td>
									<td>

											@if($tenantRenewal->new_contract_id)
											<a class="no-link" href="{{route('newContractShow',[$tenantRenewal->newTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" title="View">
											{{numberFormat($tenantRenewal->newTenantContract->tenant_contract_rent)}} </a>@else 
											<a class="no-link"href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View Previous Contract" >
											{{numberFormat($tenantRenewal->oldTenantContract->tenant_contract_rent)}} </a>@endif
										
									</td>
                                    <td>

											@if($tenantRenewal->new_contract_id)
											<a class="no-link" href="{{route('newContractShow',[$tenantRenewal->newTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" title="View">
											{{$tenantRenewal->newTenantContract->tenant->tenant_name}} </a>@else 
											<a class="no-link"href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View Previous" >
											{{$tenantRenewal->oldTenantContract->tenant->tenant_name}} </a>@endif
										
									</td>
									{{-- <td>
										<span class="label @if($tenantRenewal->new_contract_id)
											{{$tenantRenewal->newTenantContract->tenant_renewal_termination_status_class}} @else {{$tenantRenewal->oldTenantContract->tenant_renewal_termination_status_class}} @endif	 label-mini">
											@if($tenantRenewal->new_contract_id)
											{{$tenantRenewal->newTenantContract->TenantRenewalTerminationStatusName}} @else {{$tenantRenewal->oldTenantContract->TenantRenewalTerminationStatusName}} @endif
										</span>
									</td> --}}
                                    <td>
                                	@if($tenantRenewal->new_contract_id)
                                    	@can('renewal_contract_edit')
				                        <a  href="{{route('newContract.edit',$tenantRenewal->newTenantContract->id)}}" class="btn btn-tbl-edit btn-xs"  title="Edit">
				                            <i class="fa fa-pencil"></i>
				                        </a>
				                        @endcan
				                        <a href="{{route('newContractShow',[$tenantRenewal->newTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" title="View" class="btn btn-tbl-view btn-xs">
				                            <i class="fa fa-eye "></i>
				                        </a>
				                        @can('renewal_send_approval')
				                        <a href="{{route('sendApprovalFromRenewal',[$tenantRenewal->newTenantContract->id,303,5,$tenantRenewal->oldTenantContract->id])}}" title="Approve" class="btn btn-tbl-general btn-xs">
							                 <i class="fa fa-thumbs-up"></i>
							             </a>
							            @endcan
							            @if(Gate::check('pdc_generation') || Gate::check('pdc_generation_view'))
								        <a  href="{{route('pdcGeneration',['tenantRenewal','contract',$tenantRenewal->newTenantContract->id])}}" class="btn btn-tbl-view btn-xs" title="PDC Generation">
								          <i class="fa fa-book"></i>
								        </a> 
								        @else
								        @can('pdc_view')
								        <a  href="{{route('pdcGeneration',$tenantRenewal->newTenantContract->id)}}" class="btn btn-tbl-view btn-xs" title="PDC View">
								          <i class="fa fa-book"></i>
								        </a> 
								        @endcan 
								        @endif 
								       <!--  {{-- 
								        @if(Gate::check('invoice_generation') || Gate::check('invoice_generation_view'))
								        <a  href="{{route('invoice.show',$tenantRenewal->newTenantContract->id)}}" class="btn btn-tbl-view btn-xs" title="{{($contract->invoice_check==1)?'View Invoice':'Generate Invoice'}}">
								          <i class="fa fa-files-o"></i>
								        </a> 
								        @endif 
								    	--}}--> 
							            <!-- 
				                        <a href="{{route('newContractShow',[$tenantRenewal->newTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" title="View" class="btn btn-tbl-view btn-xs">
				                            <i class="fa fa-eye "></i>
				                        </a>
				                        -->
			                        @else
			                        	<a href="{{route('previousContractViewAndAction',$tenantRenewal->oldTenantContract->id)}}" title="View" class="btn btn-tbl-general btn-xs">
				                            <i class="fa fa-eye"></i>
				                        </a>
				                        
			                        @endif
				                        
				                                             
			                        </td>
                                </tr>
                                
                                @empty
                                <tr>
                                    <td colspan="11" align="center">
                                    <p>No Record</p>
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

				                        if(!empty($request->tenant_contract_old_no))
				                        $tenantRenewals->appends(['tenant_contract_old_no' => $request->tenant_contract_old_no]);
				                        
				                        if(!empty($request->building_id))
				                        $tenantRenewals->appends(['building_id' => $request->building_id]);
				                        
				                        if(!empty($request->unit_id))
				                        $tenantRenewals->appends(['unit_id' => $request->unit_id]);

				                        if(!empty($request->tenant_contract_start_date))
				                        $tenantRenewals->appends(['tenant_contract_start_date' => $request->tenant_contract_start_date]);

				                        if(!empty($request->tenant_contract_valid_to_date))
				                        $tenantRenewals->appends(['tenant_contract_valid_to_date' => $request->tenant_contract_valid_to_date]);
				                        
				                        if(!empty($request->tenant_id))
                        				$tenantRenewals->appends(['tenant_id' => $request->tenant_id]);
										
										if(!empty($request->tenant_contract_rent))
                        				$tenantRenewals->appends(['tenant_contract_rent' => $request->tenant_contract_rent]);
										
										
										
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

								        <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $tenantRenewals])         
                               </div>
								        
								        </td>                           
                  
                                 </tr>
                                 @endif

                                
                                
                                
                                
                                
