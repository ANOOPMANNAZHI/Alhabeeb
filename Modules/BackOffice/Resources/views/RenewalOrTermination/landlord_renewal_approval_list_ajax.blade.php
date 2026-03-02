               @forelse ($contractApprovals as $contractApproval)
               @php
               $current = 'landlordRenewal.index';
               Session::put('current', $current); 
               @endphp                 
               <tr>
                 <td>@can('landlord_renewal_contract_approval_view')
                 <a class="no-link" href="{{route('landlordApprovalContract',$contractApproval->new_contract_id)}}">{{$contractApproval->old_contract}}</a>
                 @endcan
                 </td>

                 <td>@can('landlord_renewal_contract_approval_view')
                 <a class="no-link" href="{{route('landlordApprovalContract',$contractApproval->new_contract_id)}}">{{$contractApproval->new_contract}}</a>@endcan</td>  


                 <td>@can('landlord_renewal_contract_approval_view')
                 <a class="no-link" href="{{route('landlordApprovalContract',$contractApproval->new_contract_id)}}">{{$contractApproval->building_name}}</a>@endcan</td>

                 <td>@can('landlord_renewal_contract_approval_view')
                 <a class="no-link" href="{{route('landlordApprovalContract',$contractApproval->new_contract_id)}}">{{$contractApproval->vendor_name}}</a>@endcan</td>

                 <td>@can('landlord_renewal_contract_approval_view')
                 <a class="no-link" href="{{route('landlordApprovalContract',$contractApproval->new_contract_id)}}">{{$contractApproval->landlord_contract_valid_from_date->format('d/m/Y')}}</a>@endcan</td>  

                 <td>@can('landlord_renewal_contract_approval_view')
                 <a class="no-link" href="{{route('landlordApprovalContract',$contractApproval->new_contract_id)}}">{{ (!empty($contractApproval->landlord_contract_valid_to_date))? $contractApproval->landlord_contract_valid_to_date->format('d/m/Y'):''}}</a>@endcan</td>   

                 <td>@can('landlord_renewal_contract_approval_view')
                 <a class="no-link" href="{{route('landlordApprovalContract',$contractApproval->new_contract_id)}}">{{numberFormat($contractApproval->landlord_contract_amt)}}</a>@endcan</td>   
                 <td>
                 @can('landlord_renewal_contract_approve')
                   <button type="button" class="btn btn-tbl-general btn-xs accept" data-id="5" datas-id="ACPT" data-flow-id="402" data-oid="{{$contractApproval->old_contract_id}}" data-nid="{{$contractApproval->new_contract_id}}" data-toggle="modal" data-target="#myModal" title="Approve" data-backdrop="static" data-keyboard="false"><i class="fa fa-check "></i></button>
                   @endcan
                   @can('landlord_renewal_contract_reject')
                   <button type="button" class="btn btn-tbl-reject btn-xs terminate" data-id="4" datas-id="RJCT" data-flow-id="402" data-oid="{{$contractApproval->old_contract_id}}" data-nid="{{$contractApproval->new_contract_id}}" data-toggle="modal" data-target="#myModal" title="Reject" data-backdrop="static" data-keyboard="false"><i class="fa fa-exclamation-triangle"></i></button>
                   @endcan
                   @can('landlord_renewal_contract_approval_view')
                   <a href="{{route('landlordApprovalContract',$contractApproval->new_contract_id)}}" title="View" class="btn btn-tbl-view btn-xs">
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
             <td colspan="5" id="pagination_ajax">
            {{$contractApprovals->withPath($route)->appends(\Request::except(['page','ajax','_token','route']))->links()}}

             <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $contractApprovals])         
                               </div>
             </td>
        </tr>
        @endif 
