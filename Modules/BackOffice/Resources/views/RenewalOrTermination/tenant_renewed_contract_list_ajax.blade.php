 							@forelse ($tenantRenewals as $tenantRenewal)
                               
                                <tr>
                                       
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('renewedContractShow',$tenantRenewal->newTenantContract->id)}}" >
											{{$tenantRenewal->newTenantContract->tenant_contract_old_no}}
										</a>
									</td> 
									<td>
                                    	<a  class="no-link" title="Contract" href="{{route('renewedContractShow',$tenantRenewal->newTenantContract->id)}}" >
											{{$tenantRenewal->newTenantContract->tenant_contract_no}}
										</a>
									</td>  
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('renewedContractShow',$tenantRenewal->newTenantContract->id)}}" >
											{{$tenantRenewal->newTenantContract->building->building_name}}
										</a>
                                    </td>  
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('renewedContractShow',$tenantRenewal->newTenantContract->id)}}" >
											{{$tenantRenewal->newTenantContract->unit->unit_no}}
										</a>
                                    </td>  
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('renewedContractShow',$tenantRenewal->newTenantContract->id)}}" >
											{{$tenantRenewal->newTenantContract->tenant_contract_muncipality_agr_no}}
										</a>
                                    </td>
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('renewedContractShow',$tenantRenewal->newTenantContract->id)}}">
											{{$tenantRenewal->newTenantContract->tenant_contract_start_date->format('d/m/Y')}}
										</a>
                                    </td>                                    
                                    <td>

										<a  class="no-link" title="Contract" href="{{route('renewedContractShow',$tenantRenewal->newTenantContract->id)}}" >
											{{$tenantRenewal->newTenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}
										</a>
									</td>
									<td>
										<a  class="no-link" title="Contract" href="{{route('renewedContractShow',$tenantRenewal->newTenantContract->id)}}" >
											{{numberFormat($tenantRenewal->newTenantContract->tenant_contract_rent)}}
										</a>
									</td>
                                    <td>
										<a  class="no-link" title="Contract" href="{{route('renewedContractShow',$tenantRenewal->newTenantContract->id)}}" >
											{{$tenantRenewal->newTenantContract->tenant->tenant_name}}
										</a>
									</td>
                                    <td>
										<a href="{{route('renewedContractShow',$tenantRenewal->newTenantContract->id)}}" title="View" class="btn btn-tbl-view btn-xs">
				                            <i class="fa fa-eye "></i>
				                        </a>
				                        @if($tenantRenewal->newTenantContract->tenant_contract_is_reg_municipality == 0)
										<a href="{{route('renewedContract.edit',$tenantRenewal->newTenantContract->id)}}" title="View" class="btn btn-tbl-edit btn-xs">
				                            <i class="fa fa-pencil"></i>
				                        </a>
				                        @endif
										 @if(Gate::check('pdc_generation') || Gate::check('pdc_generation_view'))
								        <a  href="{{route('pdcGeneration',['renewedContract','renewedContract',$tenantRenewal->newTenantContract->id])}}" class="btn btn-tbl-view btn-xs" title="PDC Generation">
								          <i class="fa fa-book"></i>
								        </a> 
								        @else
								        @can('pdc_view')
								        <a  href="{{route('pdcGeneration',$tenantRenewal->newTenantContract->id)}}" class="btn btn-tbl-view btn-xs" title="PDC View">
								          <i class="fa fa-book"></i>
								        </a> 
								        @endcan 
								        @endif   
										@if(Gate::check('invoice_generation') || Gate::check('invoice_generation_view'))
 									<a  href="{{route('invoiceGeneration',['renewedContract','renewedContract',$tenantRenewal->newTenantContract->id])}}" class="btn btn-tbl-view btn-xs" title="{{($tenantRenewal->newTenantContract->invoice_check==1)?'View Invoice':'Generate Invoice'}}">
 										<i class="fa fa-files-o"></i>
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
	         <td colspan="10" id="pagination_ajax">
			  {{$tenantRenewals->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links()}}

			   <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $tenantRenewals])         
                               </div>
			 </td>
	        </tr>
        @endif

                                
                                
                                
                                
                                
