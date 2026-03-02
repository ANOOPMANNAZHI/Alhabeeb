     @php $count = 1;
     @endphp
	
     @forelse($landlordContracts as $contract)

     @php 
     if(!isset($pendingPage)) 
     $view_url = route('contractApprovalListInfo',[$contract->salesEnquiry->id,$contract->salesEnquiry->work_flow_processes_code]);
     else
     $view_url = route('contractApprovalListPendingInfo',[$contract->salesEnquiry->id,$contract->salesEnquiry->work_flow_processes_code]);
     @endphp

          <tr>
                                   
          <td><a class="no-link" href="{{$view_url}}">{{$contract->salesEnquiry->sales_enquiry_no}}</a></td>
          <td><a class="no-link" href="{{$view_url}}">{{$contract->landlord_contract_no}}</a></td>
          <td><a class="no-link" href="{{$view_url}}">{{$contract->created_at->format('d/m/Y')}}</a></td>
          <td><a class="no-link" href="{{$view_url}}">{{$contract->vendorInfo->vendor_name}}</a></td>
          <td><a class="no-link" href="{{$view_url}}">{{$contract->managementTypeInfo->management_types_name}}</a></td>
          <td><a class="no-link" href="{{$view_url}}">{{$contract->buildinginfo->building_name}}</a></td>
          <td><a class="no-link" href="{{$view_url}}">{{(isset($contract->landlord_contract_duration)?(($contract->landlord_contract_duration==1)?'Open':'Perpetual'):'')}}</a></td>
		  <td><a class="no-link" href="{{$view_url}}">@if($contract->landlord_contract_valid_to_date){{$contract->landlord_contract_valid_to_date->format('d/m/Y')}}@endif</a></td>
         
                                    <td>
                                       @if(!isset($pendingPage))
                                        @can('edit_landlord_contract_direct')
                                        <a title="Edit" href="{{route('landlord-contract.edit',$contract->id)}}" class="btn btn-tbl-edit btn-xs" >
											<i class="fa fa-pencil"></i>
										</a>
										@endcan
										@endif
										@if(!isset($pendingPage))
										<a href="{{route('contractApprovalListInfo',[$contract->salesEnquiry->id,$contract->salesEnquiry->work_flow_processes_code])}}" class="btn btn-tbl-view btn-xs" title="View">
                                           <i class="fa fa-eye"></i>
                                        </a>
                                        @else
                                        <a href="{{route('contractApprovalListPendingInfo',[$contract->salesEnquiry->id,$contract->salesEnquiry->work_flow_processes_code])}}" class="btn btn-tbl-view btn-xs" title="View">
                                           <i class="fa fa-eye"></i>
                                        </a>
                                        @endif
                                        @if(!isset($pendingPage))
                                        @can('close_landlord_enquiry')
                                        <button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id = "CL" title="Close" data-backdrop="static" data-keyboard="false">
                                            <i class="fa fa-times-circle"></i>
                                        </button>
                                        <button type="submit" title="Approve" class="btn btn-tbl-general btn-xs accept" data-toggle="modal" data-target="#myModal" id="{{$contract->salesEnquiry->id}}" data-id = "ACPT" data-backdrop="static" data-keyboard="false"><i class="fa fa-check "></i></button>
                                        
                                        <input type="hidden" name="enquiryid" value="{{$contract->salesEnquiry->id}}" id="enquiryid">
                                        <input type="hidden" name="workflow_id" value="{{$contract->salesEnquiry->work_flow_processes_code}}" id="workflow_id">
                                         @endcan  
                                         @endif                                  
                                    </td>
                                </tr>
                                
                                @empty
                                <tr>
                                    <td colspan="9" align="center">
                                    <p>No Record</p>
                                   </td>
                                </tr>
                                @endforelse
                                
                                
                                  @if(isset($request->ajax))	
                                <tr>
                   							          
                                  <td colspan="5" id="pagination_ajax">
								    {{$landlordContracts->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links()}}

                    <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $landlordContracts])         
                               </div>
								  </td>                           
                  
                                 </tr>
                                 @endif
