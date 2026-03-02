                 @php 


                  $curr_loop =  ($depositRefunds->currentPage() == 1)? 1 :   ( (($depositRefunds->currentPage() - 1) * $depositRefunds->perPage()) + 1 );

                 if(\Request::input('curr_url'))
                 $curr_url =  \Request::input('curr_url');
                 else
                 $curr_url =  url()->current();                
                 
                 @endphp 
                 @forelse ($depositRefunds as $depositRefund)                           
                 <tr>
                   <td>{{ $curr_loop + $loop->index }}</td>
                   <td>  @can('deposit_refund_view')<a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{$depositRefund->deposit_refund_no}}</a>@endcan</td>  

                   <td> @can('deposit_refund_view')<a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{$depositRefund->deposit_refund_date->format('d/m/Y')}}</a>@endcan</td>

                   <td>@can('deposit_refund_view') <a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{$depositRefund->receiptGeneration->receipts_generation_receipt_no}}</a> @endcan </td>

                   <td> @can('deposit_refund_view') <a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{$depositRefund->tenantContract->building->building_name}}</a> @endcan </td>

                   <td>  @can('deposit_refund_view')<a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{$depositRefund->tenantContract->building->building_code}}</a>@endcan</td>

                   <td> @can('deposit_refund_view')<a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{$depositRefund->tenantContract->unit->unit_code}}</a>@endcan</td>


                   <td>  @can('deposit_refund_view')<a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{$depositRefund->tenantContract->tenant->tenant_name}}</a>@endcan</td>    


                   <td>  @can('deposit_refund_view')<a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{$depositRefund->tenantContract->tenant->tenant_code}}</a>@endcan</td> 

                   <td>  @can('deposit_refund_view')<a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{$depositRefund->tenantContract->tenant_contract_no}}</a>@endcan</td> 
                   
                   <td>  @can('deposit_refund_view')<a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{$depositRefund->deposit_refund_payment_method_name}}</a>@endcan</td> 

                   <td>  @can('deposit_refund_view')<a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">{{numberFormat($depositRefund->deposit_refund_amt)}}</a>@endcan</td>

                   <td>  
					   @can('deposit_refund_view')<a class="no-link" href="{{route('depositRefund.show',$depositRefund->id)}}">
                 @if($depositRefund->deposit_refund_status == 3 )
                <span class="label label-info label-mini">Post</span>
                @else
                 @php $status = explode('|',$depositRefund->DepositRefundApprovalStatusName) @endphp
                 <span class="label {{reset($status)}} label-mini"> {{end($status)}}</span></a></td>
                @endif     
             </a>@endcan
					</td>
                    <td>
                     @can('deposit_refund_view') 
                     <a href="{{route('depositRefund.show',$depositRefund->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a>
                    @endcan
                    
                    @can('deposit_refund_edit') 
                    @if(in_array($depositRefund->deposit_refund_approval_status,[0,1,5]) )
                    <a title="Edit" href="{{route('depositRefund.edit',$depositRefund->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                      <i class="fa fa-pencil"></i>
                    </a>    
                    @endif                                          
                    @endcan  
                 
                    @if (Auth::user()->hasPermissionTo('deposit_refund_sent_for_approval'))
                        @if(Auth::user()->hasPermissionTo('deposit_refund_approval_approve') && in_array($depositRefund->deposit_refund_status,[1,4]) && in_array($depositRefund->deposit_refund_approval_status,[0,1,5]))
                      <a href="{{ route('depositRefundAction',[$depositRefund->id,2,4]) }}" title="Approve" class="btn btn-tbl-general btn-xs unapprove">
                          <i class="fa fa-check"></i>
                      </a>
                      @elseif(Auth::user()->hasPermissionTo('deposit_refund_sent_for_approval') && in_array($depositRefund->deposit_refund_approval_status,[0,1,5])  && $depositRefund->deposit_refund_status == 1)
                      <a href="{{ route('depositRefundAction',[$depositRefund->id,1,2]) }}" title="Sent For Approval" class="btn btn-tbl-general btn-xs approve">
                        <i class="fa fa-hand-o-right"></i>
                      </a>
                      @endif
					@if(Auth::user()->hasPermissionTo('deposit_refund_approval_unapprove'))
                      @if($depositRefund->deposit_refund_status ==2 && $depositRefund->deposit_refund_approval_status == 4)
                       <a href="{{ route('depositRefundAction',[$depositRefund->id,4,1]) }}" title="Unapprove" class="btn btn-tbl-general btn-xs approve">
                          <i class="fa fa-reply"></i>
                      </a>
                      @endif
                   
                   @else
                    @if(Auth::user()->hasPermissionTo('deposit_refund_sent_for_unapproval') && $depositRefund->deposit_refund_status == 2 && $depositRefund->deposit_refund_approval_status == 4)
                    <a href="{{ route('depositRefundAction',[$depositRefund->id,1,3]) }}" title="Sent For Draft" class="btn btn-tbl-general btn-xs approve">
                      <i class="fa fa-hand-o-left"></i>
                    </a>
                    @endif
                    @endif
                    @endif

                    @if (Auth::user()->hasPermissionTo('deposit_refund_approval_approve'))
                    @can('deposit_refund_cancel')
                    @if(in_array($depositRefund->deposit_refund_approval_status,[0,1,5]))
                    <button type="button" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id="{{ $depositRefund->id }}" title="Delete" data-backdrop="static" data-keyboard="false">
                      <i class="fa fa-trash-o"></i>
                    </button>
                    @endif
                    @endcan
                    @endif

                    @if (!Auth::user()->hasPermissionTo('deposit_refund_approval_approve'))
                    @can('deposit_refund_cancel')
                    @if(in_array($depositRefund->deposit_refund_approval_status,[0,1,5]))
                    <button type="button" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id="{{ $depositRefund->id }}" title="Delete" data-backdrop="static" data-keyboard="false">
                      <i class="fa fa-trash-o"></i>
                    </button>
                    @endif
                    @endcan 
                    @endif    
                    @can('deposit_refund_post')
                    @if($depositRefund->deposit_refund_status == 2 && $depositRefund->deposit_refund_approval_status == 4) 
                    <a href="{{ route('depositRefundPost',[$depositRefund->id,3]) }}" title="Post" class="btn btn-tbl-violet btn-xs post_type">
                      <i class="fa fa-pie-chart"></i>
                    </a>
                    @endif
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
               {{$depositRefunds->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}} 

                <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $depositRefunds])         
                               </div>
             </td>
           </tr>
           @endif 
