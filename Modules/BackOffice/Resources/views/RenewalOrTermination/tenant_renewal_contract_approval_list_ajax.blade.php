 							@forelse ($tenantRenewals as $tenantRenewal)
                               
                                <tr>
                                       
                                   <!--  <td>
                                    	{{--<a  class="no-link" title="Contract" href="{{route('newContractApprovalShow',[$tenantRenewal->newTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" >
											{{$tenantRenewal->newTenantContract->tenant_contract_old_no}}
										</a>  --}}
									</td>  -->
									<td>
                                    	<a  class="no-link" title="Contract" href="{{route('newContractApprovalShow',[$tenantRenewal->oldTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" >
											{{$tenantRenewal->oldTenantContract->tenant_contract_no}}
										</a>
									</td>  
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('newContractApprovalShow',[$tenantRenewal->oldTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" >
											{{$tenantRenewal->oldTenantContract->building->building_name}}
										</a>
                                    </td>  
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('newContractApprovalShow',[$tenantRenewal->oldTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" >
											{{$tenantRenewal->oldTenantContract->unit->unit_no}}
										</a>
                                    </td>  
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('newContractApprovalShow',[$tenantRenewal->oldTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" >
											{{$tenantRenewal->oldTenantContract->tenant_contract_muncipality_agr_no}}
										</a>
                                    </td>
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('newContractApprovalShow',[$tenantRenewal->oldTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" >
											{{$tenantRenewal->oldTenantContract->tenant_contract_start_date->format('d/m/Y')}}
										</a>
                                    </td>                                    
                                    <td>

										<a  class="no-link" title="Contract" href="{{route('newContractApprovalShow',[$tenantRenewal->oldTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" >
											{{$tenantRenewal->oldTenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}
										</a>
									</td>
									<td>
										<a  class="no-link" title="Contract" href="{{route('newContractApprovalShow',[$tenantRenewal->oldTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" >
											{{numberFormat($tenantRenewal->oldTenantContract->tenant_contract_rent)}}
										</a>
									</td>
                                    <td>
										<a  class="no-link" title="Contract" href="{{route('newContractApprovalShow',[$tenantRenewal->oldTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" >
											{{$tenantRenewal->oldTenantContract->tenant->tenant_name}}
										</a>
									</td>
                                    <td>
                                    	@if($tenantRenewal->oldTenantContract->tenant_contract_no)
                                    	@can('renewal_contract_approve')
                                    	<button title="Approve" type="button" class="btn btn-tbl-general btn-xs Approve" data-toggle="modal" data-target="#myModal" data-old-id="" data-new-id="{{$tenantRenewal->oldTenantContract->id}}" data-id="5" datas-id="301" data-act-key="ACPT" data-backdrop="static" data-keyboard="false">
							                <i class="fa fa-check"></i>
							            </button>
							            @endcan
							            @can('renewal_contract_reject')
							            <button title="Reject" type="button" class="btn btn-tbl-delete btn-xs Reject" data-toggle="modal" data-target="#myModal" data-old-id="" data-new-id="{{$tenantRenewal->oldTenantContract->id}}" data-id="4" datas-id="300" data-act-key="RJCT" data-backdrop="static" data-keyboard="false">
							                <i class="fa fa-cog "></i>
							            </button>
							            @endcan
				                        <a href="{{route('newContractApprovalShow',[$tenantRenewal->oldTenantContract->id,$tenantRenewal->work_flow_processes_code])}}" title="View" class="btn btn-tbl-view btn-xs">
				                            <i class="fa fa-eye "></i>
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
								       {{$tenantRenewals->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links()}}

								       <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $tenantRenewals])         
                               </div>
								        
								   </td>                           
                  
                                 </tr>
                                 @endif

                                
                                
                                
                                
                                
