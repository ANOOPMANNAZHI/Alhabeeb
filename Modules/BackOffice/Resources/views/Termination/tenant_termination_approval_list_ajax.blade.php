                @forelse ($tenantTerminations as $tenantTermination)
               @php
               $current = 'tenantTerminationApproval';
               Session::put('current', $current); 
               @endphp               
               <tr>
                 <td><a class="no-link" href="{{route('tenantTerminationApprovalView',$tenantTermination->id)}}">{{$tenantTermination->tenant_contract_no}}</a></td>  

                 <td><a class="no-link" href="{{route('tenantTerminationApprovalView',$tenantTermination->id)}}">{{$tenantTermination->building_name}}</a></td>

                 <td><a class="no-link" href="{{route('tenantTerminationApprovalView',$tenantTermination->id)}}">{{$tenantTermination->unit_no}}</a></td>

                 <td><a class="no-link" href="{{route('tenantTerminationApprovalView',$tenantTermination->id)}}">{{$tenantTermination->tenant_name}}</a></td>

                 <td><a class="no-link" href="{{route('tenantTerminationApprovalView',$tenantTermination->id)}}">{{date('d/m/Y',strtotime($tenantTermination->tenant_contract_start_date))}}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTerminationApprovalView',$tenantTermination->id)}}">{{date('d/m/Y',strtotime($tenantTermination->tenant_contract_valid_to_date))}}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTerminationApprovalView',$tenantTermination->id)}}">{{numberFormat($tenantTermination->tenant_contract_rent)}}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTerminationApprovalView',$tenantTermination->id)}}">{{$tenantTermination->tenant_contract_muncipality_agr_no}}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTerminationApprovalView',$tenantTermination->id)}}">{{$tenantTermination->os}}</a></td>                    
                 
                 <td>
                  @can('termination_approval_list')
                  <a href="{{route('tenantTerminationApprovalView',$tenantTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                    <i class="fa fa-eye "></i>
                  </a>
                  @endcan
                  @can('tenant_termination_approve')
                  <a href="#" title="Approve" class="btn btn-tbl-general btn-xs approval_note" data-id = "approve" id="{{$tenantTermination->id}}" data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-check"></i>
                  </a>
                  @endcan
                  @can('tenant_termination_reject')
                  <a href="#" title="Reject" class="btn btn-tbl-delete btn-xs reject_note" data-id = "RJCT" id="{{$tenantTermination->id}}"  data-toggle="modal" data-target="#myModal">
                      <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
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



             <td colspan="5" id="pagination_ajax"> 
               
            
                {{$tenantTerminations->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links()}}

                 <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $tenantTerminations])         
                               </div>

           </td>                           

         </tr>
        @endif 
