                 @php 


                 $curr_loop =  ($landlordPaymentApprovals->currentPage() == 1)? 1 :   ( (($landlordPaymentApprovals->currentPage() - 1) * $landlordPaymentApprovals->perPage()) + 1 );

                 if(\Request::input('curr_url'))
                 $curr_url =  \Request::input('curr_url');
                 else
                 $curr_url =  url()->current();                
                 
                 @endphp
                 @forelse ($landlordPaymentApprovals as $landlordPaymentApproval)                              
                 <tr>
                   <td>{{ $curr_loop + $loop->index }}</td>
                   <td>  @can('landlord_payment_approval_view')<a class="no-link" href="{{route('landlordPaymentApprovalShow',$landlordPaymentApproval->id)}}">{{$landlordPaymentApproval->landlord_payment_no}}</a>@endcan</td>  

                   <td> @can('landlord_payment_approval_view')<a class="no-link" href="{{route('landlordPaymentApprovalShow',$landlordPaymentApproval->id)}}">{{$landlordPaymentApproval->landlord_payment_date->format('d/m/Y')}}</a>@endcan</td>

                   <td>@can('landlord_payment_approval_view') <a class="no-link" href="{{route('landlordPaymentApprovalShow',$landlordPaymentApproval->id)}}">{{$landlordPaymentApproval->landlordContract->vendorInfo->vendor_name}}</a> @endcan </td>

                   <td> @can('landlord_payment_approval_view') <a class="no-link" href="{{route('landlordPaymentApprovalShow',$landlordPaymentApproval->id)}}">{{$landlordPaymentApproval->landlordContract->vendorInfo->vendor_code}}</a> @endcan </td>


                   <td> @can('landlord_payment_approval_view')<a class="no-link" href="{{route('landlordPaymentApprovalShow',$landlordPaymentApproval->id)}}">{{$landlordPaymentApproval->landlordContract->landlord_contract_no}}</a>@endcan</td>

                   <td> @can('landlord_payment_approval_view')<a class="no-link" href="{{route('landlordPaymentApprovalShow',$landlordPaymentApproval->id)}}">{{$landlordPaymentApproval->landlordContract->buildingInfo->building_name}}</a>@endcan</td>

                   <td>  @can('landlord_payment_approval_view')<a class="no-link" href="{{route('landlordPaymentApprovalShow',$landlordPaymentApproval->id)}}">{{$landlordPaymentApproval->landlordInvoice->landlord_invoice_voucher_no}}</a>@endcan</td>

                   <td>  @can('landlord_payment_approval_view')<a class="no-link" href="{{route('landlordPaymentApprovalShow',$landlordPaymentApproval->id)}}">{{numberFormat($landlordPaymentApproval->landlord_payment_amount)}}</a>@endcan</td>  

                   <td> @can('landlord_payment_approval_view')<a class="no-link" href="{{route('landlordPaymentApprovalShow',$landlordPaymentApproval->id)}}">
                     @php $status = explode('|',$landlordPaymentApproval->LandlordPaymentApprovalStatusName) @endphp
                     <span class="label {{reset($status)}} label-mini"> {{end($status)}}</span></a>@endcan</td>

                     <td>
                      @can('landlord_payment_approval_approve')
                      @if($landlordPaymentApproval->landlord_payment_status ==1 && $landlordPaymentApproval->landlord_payment_approval_status == 2)
                      <a href="{{ route('landlordPaymentAction',[$landlordPaymentApproval->id,2,4]) }}" title="Approve" class="btn btn-tbl-general btn-xs approve">
                        <i class="fa fa-check"></i>
                      </a>
                      @endif
                      @endcan 

                      @can('landlord_payment_approval_unapprove')
                      @if($landlordPaymentApproval->landlord_payment_status ==1 && $landlordPaymentApproval->landlord_payment_approval_status == 3)
                      <a href="{{ route('landlordPaymentAction',[$landlordPaymentApproval->id,4,1]) }}" title="UnApprove" class="btn btn-tbl-general btn-xs unapprove">
                        <i class="fa fa-reply"></i>
                      </a>
                      @endif
                      @endcan

                      @can('landlord_payment_approval_reject')
                      @if($landlordPaymentApproval->landlord_payment_status ==1 && $landlordPaymentApproval->landlord_payment_approval_status == 2)
                      <a href="{{ route('landlordPaymentAction',[$landlordPaymentApproval->id,1,5]) }}" title="Reject" class="btn btn-tbl-reject btn-xs  reject">
                        <i class="fa fa-exclamation-triangle"></i>
                      </a>
                      @endif
                      @endcan
                      @can('landlord_payment_approval_view') 
                      <a href="{{route('landlordPaymentApprovalShow',$landlordPaymentApproval->id)}}" title="View" class="btn btn-tbl-view btn-xs">
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
                   {{$landlordPaymentApprovals->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}} 

                   <div class="pagination_info">
                     @include('includes.pagination_info',['paginator' => $landlordPaymentApprovals])         
                   </div>
                 </td> 
               </tr>
               @endif 
