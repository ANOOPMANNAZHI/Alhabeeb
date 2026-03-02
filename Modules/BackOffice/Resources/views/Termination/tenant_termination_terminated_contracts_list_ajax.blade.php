              @forelse ($tenantTerminations as $tenantTermination)
               @php
               $current = 'tenantTerminatedContract';
               Session::put('current', $current); 
               @endphp            
               <tr>
                 <td><a class="no-link" href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}">{{$tenantTermination->tenant_contract_no}}</a></td>  

                 <td><a class="no-link" href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}">{{$tenantTermination->building_name}}</a></td>

                 <td><a class="no-link" href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}">{{$tenantTermination->unit_no}}</a></td>

                 <td><a class="no-link" href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}">{{$tenantTermination->tenant_name}}</a></td>

                 <td><a class="no-link" href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}">{{date('d/m/Y',strtotime($tenantTermination->tenant_contract_start_date))}}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}">{{($tenantTermination->tenant_contract_valid_to_date)? date('d/m/Y',strtotime($tenantTermination->tenant_contract_valid_to_date)):''}}</a></td>
                 <td><a class="no-link" href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}">{{($tenantTermination->tenant_contract_last_paid_date)?date('d/m/Y',strtotime($tenantTermination->tenant_contract_last_paid_date)):''}}</a></td>
                 <td><a class="no-link" href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}">{{date('d/m/Y',strtotime($tenantTermination->termination_date))}}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}">{{numberFormat($tenantTermination->tenant_contract_rent)}}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}">{{$tenantTermination->tenant_contract_muncipality_agr_no}}</a></td>
                 <td>
                   <span class="label {{  ($tenantTermination->termination_type_status == 1)? 'label-success': terminationTypeStatusClass($tenantTermination->termination_type_status)}} label-mini">@if($tenantTermination->termination_type_status == 3){{'Premature'}} @else {{$tenantTermination->TerminationTypeStatusName}} @endif</span> 
                 </td>    
                 <td>
                  @can('terminated_contract_list')
                  <a href="{{route('tenantTerminatedContractView',$tenantTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                    <i class="fa fa-eye "></i>
                  </a> 
                  @endcan
                  @if($tenantTermination->termination_type_status == 3 && empty($tenantTermination->tenant_penalty_invoice_amt))
                  <button title="Penalty" type="button" class="btn btn-tbl-general btn-xs Penalty" data-toggle="modal" data-target="#myModal" data-id="{{$tenantTermination->id}}"  data-backdrop="static" data-keyboard="false">
                  <i class="fa fa-gavel"></i>
                  </button>
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
            
                {{$tenantTerminations->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links()}} 

                <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $tenantTerminations])         
                               </div>

           </td>                           

         </tr>
        @endif 
