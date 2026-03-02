                 @php 


                  $curr_loop =  ($maintenancePaymentApprovals->currentPage() == 1)? 1 :   ( (($maintenancePaymentApprovals->currentPage() - 1) * $maintenancePaymentApprovals->perPage()) + 1 );

                 if(\Request::input('curr_url'))
                 $curr_url =  \Request::input('curr_url');
                 else
                 $curr_url =  url()->current();               
                 
                 @endphp    
                 @forelse ($maintenancePaymentApprovals as $maintenancePaymentApproval)                         
                 <tr>
                   <td>{{ $curr_loop + $loop->index }}</td>
                   <td>  @can('maintenance_payment_approval_view')<a class="no-link" href="{{route('maintenancePaymentApprovalShow',$maintenancePaymentApproval->id)}}">{{$maintenancePaymentApproval->maintenance_payment_no}}</a>@endcan</td>  

                   <td> @can('maintenance_payment_approval_view')<a class="no-link" href="{{route('maintenancePaymentApprovalShow',$maintenancePaymentApproval->id)}}">{{$maintenancePaymentApproval->maintenance_payment_date->format('d/m/Y')}}</a>@endcan</td>

                   <td>@can('maintenance_payment_approval_view') <a class="no-link" href="{{route('maintenancePaymentApprovalShow',$maintenancePaymentApproval->id)}}">{{$maintenancePaymentApproval->vendor->vendor_name}}</a> @endcan </td>

                   <td> @can('maintenance_payment_approval_view') <a class="no-link" href="{{route('maintenancePaymentApprovalShow',$maintenancePaymentApproval->id)}}">{{$maintenancePaymentApproval->vendor->vendor_code}}</a> @endcan </td>

                   <td>  @can('maintenance_payment_approval_view')<a class="no-link" href="{{route('maintenancePaymentApprovalShow',$maintenancePaymentApproval->id)}}">{{$maintenancePaymentApproval->maintenance_payment_method_name}}</a>@endcan</td>

                   <td> @can('maintenance_payment_approval_view')<a class="no-link" href="{{route('maintenancePaymentApprovalShow',$maintenancePaymentApproval->id)}}">{{$maintenancePaymentApproval->bankInfo->bank_name}}</a>@endcan</td>

                   <td>  @can('maintenance_payment_approval_view')<a class="no-link" href="{{route('maintenancePaymentApprovalShow',$maintenancePaymentApproval->id)}}">{{numberFormat($maintenancePaymentApproval->maintenance_payment_amount)}}</a>@endcan</td>   
                   <td> @can('maintenance_payment_approval_view')<a class="no-link" href="{{route('maintenancePaymentApprovalShow',$maintenancePaymentApproval->id)}}">@php $status = explode('|',$maintenancePaymentApproval->MaintenancePaymentApprovalStatusName) @endphp
               <span class="label {{reset($status)}} label-mini"> {{end($status)}}</span></a>@endcan</td>

                   <td>
                    @can('maintenance_payment_approval_approve')
                    @if($maintenancePaymentApproval->maintenance_payment_status ==1 && $maintenancePaymentApproval->maintenance_payment_approval_status == 2)
                     <a href="{{ route('maintenancePaymentAction',[$maintenancePaymentApproval->id,2,4]) }}" title="Approve" class="btn btn-tbl-general btn-xs approve">
                        <i class="fa fa-check"></i>
                    </a>
                    @endif
                    @endcan 

                    @can('maintenance_payment_approval_unapprove')
                    @if($maintenancePaymentApproval->maintenance_payment_status ==1 && $maintenancePaymentApproval->maintenance_payment_approval_status == 3)
                    <a href="{{ route('maintenancePaymentAction',[$maintenancePaymentApproval->id,4,3]) }}" title="UnApprove" class="btn btn-tbl-general btn-xs unapprove">
                        <i class="fa fa-reply"></i>
                    </a>
                    @endif
                    @endcan
                    @can('maintenance_payment_approval_reject')
                    @if($maintenancePaymentApproval->maintenance_payment_status ==1 && $maintenancePaymentApproval->maintenance_payment_approval_status == 2)
                     <a href="{{ route('maintenancePaymentAction',[$maintenancePaymentApproval->id,1,5]) }}" title="Reject" class="btn btn-tbl-reject btn-xs  reject">
                        <i class="fa fa-exclamation-triangle"></i>
                    </a>
                     @endif
                    @endcan
                     @can('maintenance_payment_approval_view') 
                     <a href="{{route('maintenancePaymentApprovalShow',$maintenancePaymentApproval->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a>
                    @endcan     
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
                 {{$maintenancePaymentApprovals->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}} 

                 <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $maintenancePaymentApprovals])         
                               </div>
                </td> 
               </tr>
               @endif 
