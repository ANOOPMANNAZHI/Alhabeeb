            
                 @php 

                  $curr_loop =  ($landlordInvoices->currentPage() == 1)? 1 :   ( ( ($landlordInvoices->currentPage() - 1 ) * $landlordInvoices->perPage() ) + 1 );

                 if(\Request::input('curr_url'))
                 $curr_url =  \Request::input('curr_url');
                 else
                 $curr_url =  url()->current();               
                 
                 @endphp                        
                 @forelse ($landlordInvoices as $landlordInvoice)
                 <tr>
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">{{ $curr_loop + $loop->index }}  
                    </a></td> 
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">{{$landlordInvoice->landlord_invoice_voucher_no}}</a></td>  
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">{{\Carbon\Carbon::parse($landlordInvoice->landlord_invoice_voucher_date)->format('d-m-Y')}}</a></td>
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">{{$landlordInvoice->landlordInfo->vendor_name}}</a></td>
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">{{$landlordInvoice->landlordInfo->vendor_code}}</a></td>
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">{{$landlordInvoice->landlordContractInfo->landlord_contract_no}}</a></td>     
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">{{$landlordInvoice->landlordContractInfo->paymentMethodInfo->payment_method_code}}</a></td>
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">Invoice</a></td> 
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">{{$landlordInvoice->landlord_given_invoice_no}}</a></td> 
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">{{$landlordInvoice->landlordContractInfo->landlord_contract_amt}}</a></td> 
                    
                    <td><a class="no-link" href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}">{{ ($landlordInvoice->landlord_invoice_approval_status == 2)? 'Approval': 'Unapproval' }}</a></td>                     
                  
                    <td>
                    @can('view_landlord_invoice_approval')
                 		<a href="{{route('landlordInvoiceApprovalShow',$landlordInvoice->landlord_contract_id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a>
                    @endcan 

                    {{--/***************** Approve Request ************/ --}}    
                    @if($landlordInvoice->landlord_invoice_approval_status == 2 && auth()->user()->can('landlord_invoice_approval'))              
                    <a href="{{ route('landlordInvoice.action',[$landlordInvoice->landlord_contract_id,'approve'])}}" title="Approve" class="btn btn-tbl-general btn-xs confirm">
                     <i class="fa fa-check"></i>
                    </a>
                    @endif 
                    @if($landlordInvoice->landlord_invoice_approval_status == 2 && auth()->user()->can('landlord_invoice_reject'))              
                    <a href="{{route('landlordInvoice.action',[$landlordInvoice->landlord_contract_id,'reject'])}}" title="Reject" class="btn btn-tbl-reject btn-xs confirm">
                     <i class="fa fa-exclamation-triangle"></i>
                    </a>
                    @endif
                    {{--/***************** End ************/ --}}
                    {{--/***************** Unapprove Request ************/ --}}
                    @if($landlordInvoice->landlord_invoice_approval_status == 3 && auth()->user()->can('landlord_invoice_approval'))              
                    <a href="{{ route('landlordInvoice.action',[$landlordInvoice->landlord_contract_id,'unapprove'])}}" title="Approve" class="btn btn-tbl-general btn-xs confirm">
                     <i class="fa fa-check"></i>
                    </a>
                    @endif 
                    @if($landlordInvoice->landlord_invoice_approval_status == 3 && auth()->user()->can('landlord_invoice_reject'))              
                    <a href="{{route('landlordInvoice.action',[$landlordInvoice->landlord_contract_id,'reject'])}}" title="Reject" class="btn btn-tbl-reject btn-xs confirm">
                     <i class="fa fa-exclamation-triangle"></i>
                    </a>
                    @endif
                    {{--/***************** End ************/ --}}
                 	</td>
                 </tr>

                 @empty 
                 <tr>
                 	<td colspan="12" align="center">
                 		<p>No Record</p>
                 	</td>
                 </tr>
                @endforelse 




                         @if(isset($ajax))   
                                <tr>                                   
                                            
                                                      
                                  <td colspan="6" id="pagination_ajax"> 
                                        @php 
                                        $landlordInvoices->withPath(url()->current());
                             
                                        @endphp   
                                        
                                        {{$landlordInvoices->appends(\Request::except(['page','_token','ajax']))->links()}}

                                         <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $landlordInvoices])         
                               </div>
                                        
                                        </td>                           
                  
                                 </tr>
                                 @endif




               
