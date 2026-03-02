                @php 


                   $curr_loop =  ($depositRefundApprovals->currentPage() == 1)? 1 :   ( (($depositRefundApprovals->currentPage() - 1) * $depositRefundApprovals->perPage()) + 1 );

                 if(\Request::input('curr_url'))
                 $curr_url =  \Request::input('curr_url');
                 else
                 $curr_url =  url()->current();               
                 
                 @endphp  
                 @forelse ($depositRefundApprovals as $depositRefundApproval)                             
                 <tr>
                   <td>{{ $curr_loop + $loop->index }}</td>
                   <td>  @can('deposit_refund_approval_view')<a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{$depositRefundApproval->deposit_refund_no}}</a>@endcan</td>  

                   <td> @can('deposit_refund_approval_view')<a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{$depositRefundApproval->deposit_refund_date->format('d/m/Y')}}</a>@endcan</td>

                   <td>@can('deposit_refund_approval_view') <a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{$depositRefundApproval->receiptGeneration->receipts_generation_receipt_no}}</a> @endcan </td>

                   <td> @can('deposit_refund_approval_view') <a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{$depositRefundApproval->tenantContract->building->building_name}}</a> @endcan </td>

                   <td>  @can('deposit_refund_approval_view')<a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{$depositRefundApproval->tenantContract->building->building_code}}</a>@endcan</td>

                   <td> @can('deposit_refund_approval_view')<a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{$depositRefundApproval->tenantContract->unit->unit_code}}</a>@endcan</td>


                   <td>  @can('deposit_refund_approval_view')<a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{$depositRefundApproval->tenantContract->tenant->tenant_name}}</a>@endcan</td>    


                   <td>  @can('deposit_refund_approval_view')<a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{$depositRefundApproval->tenantContract->tenant->tenant_code}}</a>@endcan</td> 

                   <td>  @can('deposit_refund_approval_view')<a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{$depositRefundApproval->tenantContract->tenant_contract_no}}</a>@endcan</td> 
                   
                   <td>  @can('deposit_refund_approval_view')<a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{$depositRefundApproval->deposit_refund_payment_method_name}}</a>@endcan</td> 

                   <td>  @can('deposit_refund_approval_view')<a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">{{numberFormat($depositRefundApproval->deposit_refund_amt)}}</a>@endcan</td> 

                   <td>  @can('deposit_refund_approval_view')<a class="no-link" href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}">
                   @php $status = explode('|',$depositRefundApproval->DepositRefundApprovalStatusName) @endphp
                 <span class="label {{reset($status)}} label-mini"> {{end($status)}}</span></a>@endcan</td>    

                   <td>
                    @can('deposit_refund_approval_approve')
                    @if($depositRefundApproval->deposit_refund_status ==1 && $depositRefundApproval->deposit_refund_approval_status == 2)
                    <a href="{{ route('depositRefundAction',[$depositRefundApproval->id,2,4]) }}" title="Approve" class="btn btn-tbl-general btn-xs approve">
                      <i class="fa fa-check"></i>
                    </a>
                    @endif
                    @endcan 

                    @can('deposit_refund_approval_unapprove')
                    @if($depositRefundApproval->deposit_refund_status ==1 && $depositRefundApproval->deposit_refund_approval_status == 3)
                    <a href="{{ route('depositRefundAction',[$depositRefundApproval->id,4,1]) }}" title="UnApprove" class="btn btn-tbl-general btn-xs unapprove">
                      <i class="fa fa-reply"></i>
                    </a>
                    @endif
                    @endcan

                    @can('deposit_refund_approval_reject')
                    @if($depositRefundApproval->deposit_refund_status ==1 && $depositRefundApproval->deposit_refund_approval_status == 2)
                    <a href="{{ route('depositRefundAction',[$depositRefundApproval->id,1,5]) }}" title="Reject" class="btn btn-tbl-reject btn-xs  reject">
                      <i class="fa fa-exclamation-triangle"></i>
                    </a>
                    @endif
                    @endcan
                    @can('deposit_refund_approval_view') 
                    <a href="{{route('depositRefundApprovalShow',$depositRefundApproval->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a> 
                    @endcan  
                  </td>
                </tr>

                @empty 
                <tr>
                  <td colspan="15" align="center">
                   <p>No Record</p>
                 </td>
               </tr>
               @endforelse




               @if(isset($request->ajax))	

               <tr>
                <td colspan="5" id="pagination_ajax">                 
               {{$depositRefundApprovals->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}

                <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $depositRefundApprovals])         
                               </div> 
             </td>
           </tr>
           @endif 
