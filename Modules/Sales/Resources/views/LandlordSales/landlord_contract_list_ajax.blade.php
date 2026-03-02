 @php 


 $curr_loop =  ($contract->currentPage() == 1)? 1 :   ( (($contract->currentPage() - 1) * $contract->perPage()) + 1 );

 if(\Request::input('curr_url'))
 $curr_url =  \Request::input('curr_url');
 else
 $curr_url =  url()->current();                

 @endphp 
 @forelse ($contract as $list)
 <tr>
                                    <!--<td>
                                    <input type = "checkbox" id = "switch-2" 
                                    class = "mdl-switch__input sub_chk" name="groupAssign[]" value="{{$list->id}}"></td> -->
                                    <!-- <td>{{ $curr_loop + $loop->index }}</td> -->
                                    <td>
                                        @if(!empty($list->landlord_contract_no))
                                   
                                        <a class="no-link" href="{{route('landlordContract.show',$list->sale_enquiry_id)}}" >
                                            {{$list->sales_enquiry_no}}
                                        </a>
                                  
                                        @else
                                        @can('landlordcontract_add')
                                        <a class="no-link" href="{{route('contractGeneration',$list->sale_enquiry_id)}}" >
                                            {{$list->sales_enquiry_no}}
                                        </a>
                                        @endcan
                                        @endif

                                    </td> 
                                    <td>
                                    	@if(!empty($list->landlord_contract_no))
                                    	
                                    	<a class="no-link" href="{{route('landlordContract.show',$list->sale_enquiry_id)}}">
                                    		{{$list->cust}}
                                    	</a>
                                    	
                                    	@else
                                    	@can('landlordcontract_add')
                                    	<a class="no-link" href="{{route('contractGeneration',$list->sale_enquiry_id)}}" >
                                    		{{$list->cust}}
                                    	</a>
                                    	@endcan
                                    	@endif

                                    </td>
                                    <td>
                                    	@if(!empty($list->landlord_contract_no))
                                    
                                    	<a class="no-link" href="{{route('landlordContract.show',$list->sale_enquiry_id)}}">
                                    		{{$list->building}}
                                    	</a>
                          
                                    	@else
                                    	@can('landlordcontract_add')
                                    	<a class="no-link" href="{{route('contractGeneration',$list->sale_enquiry_id)}}" >
                                    		{{$list->building}}
                                    	</a>
                                    	@endcan
                                    	@endif		
                                    </td>
                                    <td>
                                    	@if(!empty($list->landlord_contract_no))
                                    
                                    	<a class="no-link" href="{{route('landlordContract.show',$list->sale_enquiry_id)}}">
                                    		{{$list->contact_email}}
                                    	</a>
                                    
                                    	@else
                                    	@can('landlordcontract_add')
                                    	<a class="no-link" href="{{route('contractGeneration',$list->sale_enquiry_id)}}" >
                                    		{{$list->sales_email}}
                                    	</a>
                                    	@endcan
                                    	@endif		

                                    </td>
                                    <td>
                                    	@if(!empty($list->landlord_contract_no))
                                    	
                                    	<a class="no-link" href="{{route('landlordContract.show',$list->sale_enquiry_id)}}">
                                    		{{$list->cust_no}}
                                    	</a>
                                  
                                    	@else
                                    	@can('landlordcontract_add')
                                    	<a class="no-link" href="{{route('contractGeneration',$list->sale_enquiry_id)}}" >	
                                    		{{$list->cust_no}}
                                    	</a>
                                    	@endcan
                                    	@endif 
                                    </td>
                                    <td>
										
                                    	@if(!empty($list->landlord_contract_no))
                                    	<a title="View" href="{{route('landlordContract.show', $list->sale_enquiry_id)}}" class="btn btn-tbl-view btn-xs">
                                    		<i class="fa fa-eye"></i>
                                    	</a>
                                    	@else
											<a title="View Enquiry Details" href="{{route('landlordEnquiryView', $list->sale_enquiry_id)}}" class="btn btn-tbl-view btn-xs">
												<i class="fa fa-eye"></i>
											</a>
                                    	
                                    	@endif
                                    	
                                    	@can('close_landlord_enquiry')
                                    	<button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id = "CL" title="Close" data-backdrop="static" data-keyboard="false">
                                    		<i class="fa fa-times-circle"></i>
                                    	</button>

                                    	<input type="hidden" name="enquiryid" value="{{$list->sale_enquiry_id}}" id="enquiryid">
                                    	<input type="hidden" name="workflow_id" value="{{$list->enquiry_flow}}" id="workflow_id">
                                    	@endcan

                                    	@if(!empty($list->landlord_contract_id))
                                    	@can('landlordcontract_add')
                                    	<a href="{{route('landlordContract.edit',$list->landlord_contract_id)}}" class="btn btn-tbl-edit btn-xs" title="Edit Contract">
                                    		<i class="fa fa-pencil" aria-hidden="true"></i>
                                    	</a>
                                    	@endcan
                                    	@else
                                    	@can('landlordcontract_add')
                                    	<a href="{{route('contractGeneration',$list->sale_enquiry_id)}}" class="btn btn-tbl-general btn-xs" title="Create Contract">
                                    		<i class="fa fa-user-plus" aria-hidden="true"></i>
                                    	</a>
                                    	@endcan
                                    	@endif 

                                    </td>
                                </tr>
                                
                                @empty
                                <tr>
                                	<td colspan="6" align="center">
                                		<p>No Record</p>
                                	</td>
                                </tr>
                                @endforelse
                                
                                
                                @if(isset($request->ajax))	
                                <tr>
                                	<td colspan="6" id="pagination_ajax">
                                	
                                	{{$contract->withPath($route)->appends(\Request::except(['page','_token']))->links()}}

                                     <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $contract])         
                               </div>
                                </td>
                                </tr>
                            @endif
