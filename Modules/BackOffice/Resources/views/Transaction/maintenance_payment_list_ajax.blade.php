                  @php 


                  $curr_loop =  ($maintenancePayments->currentPage() == 1)? 1 :   ( (($maintenancePayments->currentPage() - 1) * $maintenancePayments->perPage()) + 1 );

                 if(\Request::input('curr_url'))
                 $curr_url =  \Request::input('curr_url');
                 else
                 $curr_url =  url()->current();                
                 
                 @endphp  
                 @forelse ($maintenancePayments as $maintenancePayment)                            
                 <tr>
                 <td>{{ $curr_loop + $loop->index }} </td>
                   <td>  @can('maintenance_payment_view')<a class="no-link" href="{{route('maintenancePayment.show',$maintenancePayment->id)}}">{{$maintenancePayment->maintenance_payment_no}}</a>@endcan</td>  

                   <td> @can('maintenance_payment_view')<a class="no-link" href="{{route('maintenancePayment.show',$maintenancePayment->id)}}">{{$maintenancePayment->maintenance_payment_date->format('d/m/Y')}}</a>@endcan</td>

                   <td>@can('maintenance_payment_view') <a class="no-link" href="{{route('maintenancePayment.show',$maintenancePayment->id)}}">{{$maintenancePayment->vendor->vendor_name}}</a> @endcan </td>

                   <td> @can('maintenance_payment_view') <a class="no-link" href="{{route('maintenancePayment.show',$maintenancePayment->id)}}">{{$maintenancePayment->vendor->vendor_code}}</a> @endcan </td>

                   <td>  @can('maintenance_payment_view')<a class="no-link" href="{{route('maintenancePayment.show',$maintenancePayment->id)}}">{{$maintenancePayment->maintenance_payment_method_name}}</a>@endcan</td>

                   <td> @can('maintenance_payment_view')<a class="no-link" href="{{route('maintenancePayment.show',$maintenancePayment->id)}}">{{$maintenancePayment->bankInfo->bank_name}}</a>@endcan</td>


                   <td>  @can('maintenance_payment_view')<a class="no-link" href="{{route('maintenancePayment.show',$maintenancePayment->id)}}">{{numberFormat($maintenancePayment->maintenance_payment_amount)}}</a>@endcan</td>

                    <td>
						@can('maintenance_payment_view')
						<a class="no-link" href="{{route('maintenancePayment.show',$maintenancePayment->id)}}">
						@if($maintenancePayment->maintenance_payment_status == 3 )
						  <span class="label label-info label-mini">Post</span>
						@else
							@php $status = explode('|',$maintenancePayment->MaintenancePaymentApprovalStatusName) @endphp
						   <span class="label {{reset($status)}} label-mini"> {{end($status)}}</span>
						@endif
						</a>	
						@endcan
                    </td>

                   <td>
                     @can('maintenance_payment_view') 
                     <a href="{{route('maintenancePayment.show',$maintenancePayment->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a>
                    @endcan
                    {{-- @can('maintenance_payment_edit') 
                    @if(($maintenancePayment->maintenance_payment_status == 1 && $maintenancePayment->maintenance_payment_approval_status == 2) || ($maintenancePayment->maintenance_payment_status == 1 && $maintenancePayment->maintenance_payment_approval_status == 5) || ($maintenancePayment->maintenance_payment_status == 4 && $maintenancePayment->maintenance_payment_approval_status == 3) || ($maintenancePayment->maintenance_payment_status == 1 && $maintenancePayment->maintenance_payment_approval_status == 0)) --}}
                    <!-- <a title="Edit" href="{{route('maintenancePayment.edit',$maintenancePayment->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                      <i class="fa fa-pencil"></i>
                    </a>  -->   
                    {{-- @endif                                          
                    @endcan --}}
                    @can('maintenance_payment_edit') 
                    @if($maintenancePayment->maintenance_payment_status != 3  && in_array($maintenancePayment->maintenance_payment_approval_status,[0,1,5]))
                    <a title="Edit" href="{{route('maintenancePayment.edit',$maintenancePayment->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                      <i class="fa fa-pencil"></i>
                    </a>    
                    @endif                                          
                    @endcan
                    @if (Auth::user()->hasPermissionTo('maintenance_payment_approval_approve')) 
                    @if($maintenancePayment->maintenance_payment_status ==2 && $maintenancePayment->maintenance_payment_approval_status == 4)
                    <a href="{{ route('maintenancePaymentAction',[$maintenancePayment->id,4,3]) }}" title="UnApprove" class="btn btn-tbl-general btn-xs unapprove">
                        <i class="fa fa-reply"></i>
                    </a>
                    @endif
                    @if($maintenancePayment->maintenance_payment_status == 4 && $maintenancePayment->maintenance_payment_approval_status == 1)
                     <a href="{{ route('maintenancePaymentAction',[$maintenancePayment->id,2,4]) }}" title="Approve" class="btn btn-tbl-general btn-xs approve">
                        <i class="fa fa-check"></i>
                    </a>
                    @endif
                @else
                    @can('sent_for_approval')
                    @if(($maintenancePayment->maintenance_payment_approval_status == 0 && $maintenancePayment->maintenance_payment_status == 1) || ($maintenancePayment->maintenance_payment_status == 1 && $maintenancePayment->maintenance_payment_approval_status == 5) || ($maintenancePayment->maintenance_payment_status == 4 && $maintenancePayment->maintenance_payment_approval_status == 3))
                    <a href="{{ route('maintenancePaymentAction',[$maintenancePayment->id,1,2]) }}" title="Sent For Approval" class="btn btn-tbl-general btn-xs approve">
                      <i class="fa fa-hand-o-right"></i>
                    </a>
                    @endif
                    @endcan
                    @if (!Auth::user()->hasPermissionTo('maintenance_payment_approval_approve'))
                    @can('sent_for_unapproval')
                    @if($maintenancePayment->maintenance_payment_status == 2 && $maintenancePayment->maintenance_payment_approval_status == 4)
                    <a href="{{ route('maintenancePaymentAction',[$maintenancePayment->id,1,3]) }}" title="Sent For UnApproval" class="btn btn-tbl-general btn-xs approve">
                      <i class="fa fa-hand-o-left"></i>
                    </a>
                    @endif
                    @endcan 
                    @endif
                     @endif
                    @can('maintenance_payment_post')
                    @if($maintenancePayment->maintenance_payment_status == 2 && $maintenancePayment->maintenance_payment_approval_status == 4) 
                    <a href="{{ route('maintenancePaymentPost',[$maintenancePayment->id,3]) }}" title="Post" class="btn btn-tbl-violet btn-xs post_type">
                      <i class="fa fa-pie-chart"></i>
                    </a>
                    @endif
                    @endcan

                   @if (Auth::user()->hasPermissionTo('maintenance_payment_approval_approve'))
                   @can('maintenance_payment_cancel')
                     @if(in_array($maintenancePayment->maintenance_payment_approval_status,[0,1,5]))
                  
                    <button type="button" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id="{{ $maintenancePayment->id }}" title="Delete" data-backdrop="static" data-keyboard="false">
                      <i class="fa fa-trash-o"></i>
                    </button>
                    @endif
                    @endcan
                   @endif

                   @if (!Auth::user()->hasPermissionTo('maintenance_payment_approval_approve'))
                    @can('maintenance_payment_cancel')
                    @if($maintenancePayment->maintenance_payment_status ==4 || ($maintenancePayment->maintenance_payment_status ==1 && $maintenancePayment->maintenance_payment_approval_status == 0 ) || ($maintenancePayment->maintenance_payment_status ==1 && $maintenancePayment->maintenance_payment_approval_status == 2 ))
                    <button type="button" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id="{{ $maintenancePayment->id }}" title="Delete" data-backdrop="static" data-keyboard="false">
                      <i class="fa fa-trash-o"></i>
                    </button>
                    @endif
                    @endcan      
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
                 @php										 

                 if(isset($request->ajax))	
                 $maintenancePayments->withPath($route);

                 if(!empty($request->maintenance_payment_no))
                 $maintenancePayments->appends(['maintenance_payment_no' => $request->maintenance_payment_no]);


                 if(!empty($request->maintenance_payment_date))
                 $maintenancePayments->appends(['maintenance_payment_date' => $request->maintenance_payment_date]); 

                 if(!empty($request->vendor_id))
                 $maintenancePayments->appends(['vendor_id' => $request->vendor_id]);

                 if(!empty($request->vendor_id))
                 $maintenancePayments->appends(['vendor_id' => $request->vendor_id]);


                 if(!empty($request->maintenance_payment_method))
                 $maintenancePayments->appends(['maintenance_payment_method' => $request->maintenance_payment_method]);

                 if(!empty($request->bank_id))
                 $maintenancePayments->appends(['bank_id' => $request->bank_id]);

                 if(!empty($request->maintenance_payment_amount))
                 $maintenancePayments->appends(['maintenance_payment_amount' => $request->maintenance_payment_amount]);										

                 $fieldName =  app('request')->input('fieldName');

                 if(!empty($fieldName)){
                 $fieldName =  app('request')->input('fieldName');
                 $operation =  app('request')->input('operation');
                 $fieldValue =  app('request')->input('fieldValue');
                 $logic =  app('request')->input('logic');
                 $maintenancePayments->appends(['fieldName' => $fieldName,
                 'operation' => $operation,
                 'fieldValue' => $fieldValue,
                 'logic' => $logic,
                 ]);	
               }

               @endphp   

               {{$maintenancePayments->links()}} 
               <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $maintenancePayments])         
                               </div>

             </td>                           

           </tr>
           @endif 
