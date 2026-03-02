                 @php 


                  $curr_loop =  ($landlordPayments->currentPage() == 1)? 1 :   ($landlordPayments->currentPage() * $landlordPayments->perPage());

                 if(\Request::input('curr_url'))
                 $curr_url =  \Request::input('curr_url');
                 else
                 $curr_url =  url()->current();               
                 
                 @endphp     
                 @forelse ($landlordPayments as $landlordPayment)                          
                 <tr>
                 <td>{{ $curr_loop + $loop->index }}</td>
                   <td>  @can('landlord_payment_view')<a class="no-link" href="{{route('landlordPayment.show',$landlordPayment->id)}}">{{$landlordPayment->landlord_payment_no}}</a>@endcan</td>  

                   <td> @can('landlord_payment_view')<a class="no-link" href="{{route('landlordPayment.show',$landlordPayment->id)}}">{{$landlordPayment->landlord_payment_date->format('d/m/Y')}}</a>@endcan</td>

                   <td>@can('landlord_payment_view') <a class="no-link" href="{{route('landlordPayment.show',$landlordPayment->id)}}">{{$landlordPayment->landlordContract->vendorInfo->vendor_name}}</a> @endcan </td>

                   <td> @can('landlord_payment_view') <a class="no-link" href="{{route('landlordPayment.show',$landlordPayment->id)}}">{{$landlordPayment->landlordContract->vendorInfo->vendor_code}}</a> @endcan </td>

                   <td>  @can('landlord_payment_view')<a class="no-link" href="{{route('landlordPayment.show',$landlordPayment->id)}}">{{$landlordPayment->landlordContract->landlord_contract_no}}</a>@endcan</td>

                   <td>  @can('landlord_payment_view')<a class="no-link" href="{{route('landlordPayment.show',$landlordPayment->id)}}">{{$landlordPayment->landlordContract->buildingInfo->building_name}}</a>@endcan</td>

                   <td> @can('landlord_payment_view')<a class="no-link" href="{{route('landlordPayment.show',$landlordPayment->id)}}">{{$landlordPayment->landlordInvoice->landlord_invoice_voucher_no}}</a>@endcan</td>


                   <td>  @can('landlord_payment_view')<a class="no-link" href="{{route('landlordPayment.show',$landlordPayment->id)}}">{{isset($landlordPayment->landlord_payment_amount)?numberFormat($landlordPayment->landlord_payment_amount):''}}</a>@endcan</td>    
                 

                    <td>  
						@can('landlord_payment_view')
						<a class="no-link" href="{{route('landlordPayment.show',$landlordPayment->id)}}">
							
						 @if($landlordPayment->landlord_payment_status == 3 )
						  <span class="label label-info label-mini">Post</span>
						  @else
						   @php $status = explode('|',$landlordPayment->LandlordPaymentApprovalStatusName) @endphp
						   <span class="label {{reset($status)}} label-mini"> {{end($status)}}</span></a></td>
						 @endif
						</a>
						@endcan
                    </td>  
                    <td>
                     @can('landlord_payment_view') 
                     <a href="{{route('landlordPayment.show',$landlordPayment->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a>
                    @endcan
                    {{-- @can('landlord_payment_edit') 
                     @if(($landlordPayment->landlord_payment_status == 1 && $landlordPayment->landlord_payment_approval_status == 2) || ($landlordPayment->landlord_payment_status == 1 && $landlordPayment->landlord_payment_approval_status == 5) || ($landlordPayment->landlord_payment_status == 4 && $landlordPayment->landlord_payment_approval_status == 3) || ($landlordPayment->landlord_payment_status == 1 && $landlordPayment->landlord_payment_approval_status == 0)) --}}
                    <!-- <a title="Edit" href="{{route('landlordPayment.edit',$landlordPayment->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                      <i class="fa fa-pencil"></i>
                    </a>   -->  
                    {{-- @endif                                          
                    @endcan --}}
                      
                    @if(auth()->user()->can('landlord_payment_edit') && $landlordPayment->landlord_payment_status != 3 && in_array($landlordPayment->landlord_payment_approval_status,[0,1]))
                    <a title="Edit" href="{{route('landlordPayment.edit',$landlordPayment->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                      <i class="fa fa-pencil"></i>
                    </a>    
                    @endif    
                    <!-- Approval premission user - To approve    -->                                      
                    @if (Auth::user()->hasPermissionTo('landlord_payment_approval_approve')) 
                    @if($landlordPayment->landlord_payment_approval_status ==1 && $landlordPayment->landlord_payment_status ==4)
                        <a href="{{ route('landlordPaymentAction',[$landlordPayment->id,2,4]) }}" title="Approve" class="btn btn-tbl-general btn-xs approve">
                        <i class="fa fa-check "></i>
                        </a>
                    @endif
                    @endif
                    <!-- Approval premission user - To Unapprove    -->
                    @if (Auth::user()->hasPermissionTo('landlord_payment_approval_unapprove')) 
                     @if($landlordPayment->landlord_payment_status ==2 && $landlordPayment->landlord_payment_approval_status == 4)

                        <a href="{{ route('landlordPaymentAction',[$landlordPayment->id,4,1]) }}" title="UnApprove" class="btn btn-tbl-general btn-xs unapprove">
                          <i class="fa fa-reply"></i>
                        </a>
                    @endif
  
                    @endif
                    @if(auth()->user()->can('sent_for_approval')  && !auth()->user()->can('landlord_payment_approval_approve') && in_array($landlordPayment->landlord_payment_status,[1,4]) && in_array($landlordPayment->landlord_payment_approval_status,[0,1,5]))
                    <a href="{{ route('landlordPaymentAction',[$landlordPayment->id,1,2]) }}" title="Sent For Approval" class="btn btn-tbl-general btn-xs approve">
                      <i class="fa fa-hand-o-right"></i>
                    </a>
                    @endif
                  
                    @if (!Auth::user()->hasPermissionTo('landlord_payment_approval_approve'))
                   
                    @if(auth()->user()->can('sent_for_unapproval') && $landlordPayment->landlord_payment_status == 2 && $landlordPayment->landlord_payment_approval_status == 4)
                    <a href="{{ route('landlordPaymentAction',[$landlordPayment->id,1,3]) }}" title="Sent For UnApproval" class="btn btn-tbl-general btn-xs approve">
                      <i class="fa fa-hand-o-left"></i>
                    </a>
                    @endif
                    @endif
                    @can('landlord_payment_post')
                    @if($landlordPayment->landlord_payment_status == 2 && $landlordPayment->landlord_payment_approval_status == 4) 
                    <a href="{{ route('landlordPaymentPost',[$landlordPayment->id,3]) }}" title="Post" class="btn btn-tbl-violet btn-xs post_type">
                      <i class="fa fa-pie-chart"></i>
                    </a>
                    @endif
                    @endcan

                   @if (Auth::user()->hasPermissionTo('landlord_payment_approval_approve'))
                   
                    @if(auth()->user()->can('landlord_payment_cancel') && in_array($landlordPayment->landlord_payment_status,[1,4]) && in_array($landlordPayment->landlord_payment_approval_status,[0,1,5]))
                    <button type="button" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id="{{ $landlordPayment->id }}" title="Delete">
                      <i class="fa fa-trash-o"></i>
                    </button>
                    @endif
                   @endif
                  @if (!Auth::user()->hasPermissionTo('landlord_payment_approval_approve'))
                   
                    @if( auth()->user()->can('landlord_payment_cancel')  && in_array($landlordPayment->landlord_payment_approval_status,[0,1,5]))
                    <button type="button" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id="{{ $landlordPayment->id }}" title="Delete">
                      <i class="fa fa-trash-o"></i>
                    </button>
                    @endif
                   
                     @endif     
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
               {{$landlordPayments->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}

                <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $landlordPayments])         
                               </div>
             </td>
           </tr>
           @endif 
