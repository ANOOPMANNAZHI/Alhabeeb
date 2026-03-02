@php $count = 1; @endphp
	
     @forelse($landlordContracts as $contract) 
     <tr>
          <td><a class="no-link" href="{{route('landlord-contract.show',$contract->id)}}">{{$contract->landlord_contract_no}}</a></td>
          <td><a class="no-link" href="{{route('landlord-contract.show',$contract->id)}}">{{$contract->created_at->format('d/m/Y')}}</a></td>
          <td><a class="no-link" href="{{route('landlord-contract.show',$contract->id)}}">{{$contract->vendorInfo->vendor_name}}</a></td>
          <td><a class="no-link" href="{{route('landlord-contract.show',$contract->id)}}">{{$contract->managementTypeInfo->management_types_name}}</a></td>
          <td><a class="no-link" href="{{route('landlord-contract.show',$contract->id)}}">{{$contract->buildinginfo->building_name}}</a></td>
          <td><a class="no-link" href="{{route('landlord-contract.show',$contract->id)}}">{{(isset($contract->landlord_contract_duration)?(($contract->landlord_contract_duration==1)?'Open':'Close'):'')}}</a></td>
		  <td><a class="no-link" href="{{route('landlord-contract.show',$contract->id)}}">@if($contract->landlord_contract_valid_to_date){{$contract->landlord_contract_valid_to_date->format('d/m/Y')}}@endif</a></td>
		  <td>
				
				@if($contract->landlord_contract_status == 1)
				<span class=" btn-circle btn-success btn-sm m-b-10 status"><b>{{$contract->landlord_contract_status_name}}</b></span>
				@else 
				<span  class=" btn-circle btn-danger btn-sm m-b-10"><b>{{$contract->landlord_contract_status_name}}</b></span>
				@endif
			  
		   </td>
		   <td>
			  @if($contract->landlord_contract_status==2)
			   @if(isset($contract->salesEnquiry->work_flow_processes_code) && $contract->salesEnquiry->work_flow_processes_code != 204) 
			   @can('edit_landlord_contract_direct')
				<a title="Edit" href="{{route('landlord-contract.edit',$contract->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
					<i class="fa fa-pencil"></i>
				</a>
				@endcan
			  @endif
			  @endif 	
			  @can('view_landlord_contract_direct') 
				<a title="View" href="{{route('landlord-contract.show',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="View">
					<i class="fa fa-eye"></i>
				</a>  
				                                    
			  @endcan
			  @if($contract->landlord_contract_status == 2)
				<a href="{{route('landlordSendForApproval',[$contract->id,'list'])}}" class="btn btn-tbl-view btn-xs" title="Send For Approval">
					<i class="fa fa-check"></i>
				</a>       
			  @endif 
			  {{-- @can('generate_landlord_invoice') --}}
			  @if($contract->landlord_contract_status == 1 &&  $contract->management_id == 1)
			   @if(auth()->user()->hasAnyPermission(['view_landlord_invoice','add_landlord_invoice']))
			   <a  href="{{route(!empty($contract->landlordInvoice)? 'landlordInvoiceShow': 'landlordInvoiceGenerate',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="{{ !empty($contract->landlordInvoice)? 'View Invoice'  :'Generate Invoice'}}">
					<i class="fa fa-files-o"></i>
				</a>   
			   @endif
			  @endif
			  {{-- @endcan --}}
			  
									
			</td>
	  </tr>
	  @php $count++; @endphp
	   @empty
				  <tr>
						<td colspan="9" align="center">
							<p>No Record</p>
						</td>
				  </tr>
				 @endforelse
          
      @if(isset($request->ajax))	
        <tr>
		  <td colspan="8" id="pagination_ajax"> 
					             
				   {{$landlordContracts->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links()}}

				    <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $landlordContracts])         
                               </div>
				
				</td>                           

		 </tr>
		 @endif
