 							@forelse ($dueForRenewals as $dueForRenewal)
                               @php
                            
                                $tenantEmail = $dueForRenewal->tenant_contact_email;
                                if($tenantEmail == "")
                                $tenantEmail = $dueForRenewal->tenant_personal_email;

                                $count = $dueForRenewal->email_count;
                               @endphp
                                <tr @if($count >0) bgcolor ="#90ee90" @endif>
                                       
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('tenantRenewal.show',$dueForRenewal->id)}}" >
											{{$dueForRenewal->tenant_contract_no}}
										</a>
									</td>  
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('tenantRenewal.show',$dueForRenewal->id)}}" >
											{{$dueForRenewal->building_name}}
										</a>
                                    </td>  
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('tenantRenewal.show',$dueForRenewal->id)}}" >
											{{$dueForRenewal->unit_no}}
										</a>
                                    </td>  
                                    <td>
                                    	<a  class="no-link" title="Contract" href="{{route('tenantRenewal.show',$dueForRenewal->id)}}" >
											{{$dueForRenewal->tenant_contract_start_date->format('d/m/Y')}}
										</a>
                                    </td>                                    
                                    <td>
										<a  class="no-link" title="Contract" href="{{route('tenantRenewal.show',$dueForRenewal->id)}}" >
											{{$dueForRenewal->tenant_contract_valid_to_date->format('d/m/Y')}}
										</a>
									</td>
									<td>
										<a  class="no-link" title="Contract" href="{{route('tenantRenewal.show',$dueForRenewal->id)}}" >
											{{numberFormat($dueForRenewal->tenant_contract_rent)}}
										</a>
									</td>
                                    <td>
										<a  class="no-link" title="Contract" href="{{route('tenantRenewal.show',$dueForRenewal->id)}}" >
											{{$dueForRenewal->tenant_name}}
										</a>
									</td>
									<td><a  class="no-link" title="Contract" href="{{route('tenantRenewal.show',$dueForRenewal->id)}}" >
											{{$dueForRenewal->email_count}}
										</a></td>

									<td>
										@if(count($dueForRenewal->discussion) > 0)
										<a  class="no-link" title="Contract" href="{{route('tenantRenewal.show',$dueForRenewal->id)}}" >
											{{$dueForRenewal->discussion[0]->category->category}}
										</a>
										@endif
									</td>
                                    <td>
                                     {{-- 	@can('renewal_due_accept')
				                        <a  href="{{route('tenantRenewalStage',[$dueForRenewal->id,304,2,0])}}" class="btn btn-tbl-general btn-xs" title="Accept">
				                            <i class="fa fa-check"></i>
				                        </a>
				                        @endcan
				                        @can('renewal_due_terminate')
				                        <a href="{{route('tenantRenewalStage',[$dueForRenewal->id,501,7,0])}}" title="Terminate" class="btn btn-tbl-violet btn-xs">
				                            <i class="fa fa-life-ring"></i>
				                        </a>
				                        @endcan   --}}
				                        @can('renewal_due_list')
				                        <a href="{{route('tenantRenewal.show',$dueForRenewal->id)}}" title="View" class="btn btn-tbl-view btn-xs">
				                            <i class="fa fa-eye "></i>
				                        </a>
				                        @endcan
				                        @can('send_email')
                                  @if(!empty($tenantEmail))
	                                  <button title="Email" type="button"   class="btn btn btn-tbl-general btn-xs accept  emailTemplate" data-toggle="modal" data-target="#myModal" data-tenant_contract_id="{{$dueForRenewal->id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-envelope "></i></button>  
                                  @else
	                                    <button title="PDF" type="button"  class="btn btn btn-tbl-general btn-xs accept pdfTemplate" data-toggle="modal" data-target="#myModal" data-tenant_contract_id="{{$dueForRenewal->id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-file "></i></button>
                                  @endif 

				                      {{--  @if(!empty($tenantEmail))
				                        <a href="{{route('generatePdfEmail',$dueForRenewal->id)}}" title="Email" class="btn btn-tbl-user btn-xs">
				                            <i class="fa fa-envelope "></i>
				                        </a>
				                        @else
				                        <a href="{{route('generatePdfEmail',$dueForRenewal->id)}}" title="PDF" class="btn btn-tbl-user btn-xs">
				                            <i class="fa fa-file "></i>
				                        </a>
				                        @endif   --}}
				                        @endcan
				                                             
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
								 {{$dueForRenewals->withPath($route)->appends(\Request::except(['page','ajax','_token','route']))->links()}}
								         <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $dueForRenewals])         
                               </div>
								 </td>                           
                  
                                 </tr>
                              @endif

                                
                                
                                
                                
                                
