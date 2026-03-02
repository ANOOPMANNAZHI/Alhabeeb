              @forelse ($dueForRenewals as $dueForRenewal)
                               @php $tenantEmail = $dueForRenewal->tenant_contact_email;
                                if($tenantEmail == "")$tenantEmail = $dueForRenewal->tenant_personal_email;
                                $count = $dueForRenewal->email_count;
                               @endphp
                  <tr >
                                       
                   <td>
                   <a  class="no-link" title="Contract" href="{{route('tenantContract.underRenewalView',$dueForRenewal->id)}}" >
                      {{$dueForRenewal->tenant_contract_no}}
                    </a>
                  </td>  
                                    <td>
                                      <a  class="no-link" title="Contract" href="{{route('tenantContract.underRenewalView',$dueForRenewal->id)}}" >
                      {{$dueForRenewal->building_name}}
                    </a>
                                    </td>  
                                    <td>
                                      <a  class="no-link" title="Contract" href="{{route('tenantContract.underRenewalView',$dueForRenewal->id)}}" >
                      {{$dueForRenewal->unit_no}}
                    </a>
                                    </td>  
                                    <td>
                                      <a  class="no-link" title="Contract" href="{{route('tenantContract.underRenewalView',$dueForRenewal->id)}}" >
                      {{$dueForRenewal->tenant_contract_start_date->format('d/m/Y')}}
                    </a>
                                    </td>                                    
                                    <td>
                    <a  class="no-link" title="Contract" href="{{route('tenantContract.underRenewalView',$dueForRenewal->id)}}" >
                      {{$dueForRenewal->tenant_contract_valid_to_date->format('d/m/Y')}}
                    </a>
                  </td>
                  <td>
                    <a  class="no-link" title="Contract" href="{{route('tenantContract.underRenewalView',$dueForRenewal->id)}}" >
                      {{numberFormat($dueForRenewal->tenant_contract_rent)}}
                    </a>
                  </td>
                                    <td>
                    <a  class="no-link" title="Contract" href="{{route('tenantContract.underRenewalView',$dueForRenewal->id)}}" >
                      {{$dueForRenewal->tenant_name}}
                    </a>
                  </td>
                 

                  <td>
                    @if(isset($dueForRenewal->category))
                    <a  class="no-link" title="Contract" href="{{route('tenantContract.underRenewalView',$dueForRenewal->id)}}" >
                      {{$dueForRenewal->category}}
                    </a>
                    @endif
                  </td>
                   <td>
                                     
                    @can('contract_under_renewal_view')
                    <a href="{{route('tenantContract.underRenewalView',$dueForRenewal->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                        <i class="fa fa-eye "></i>
                    </a>
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

                                
                                
                                
                                
                                
