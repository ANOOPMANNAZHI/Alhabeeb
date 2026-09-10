

@php $count = 1; @endphp
@forelse ($tenantContracts as $contract)

@php

$editFlag = false;
if(isset($title))
$curr_url =  route('leadAssign.revokeProcess',[$route,$contract->sale_enquiry_id,$contract->work_flow_processes_code]);
elseif($contract->work_flow_processes_code <= 107 && $contract->tennat_contract_direct_indirect_status == 1 && auth()->user()->can('edit_tenant_contract_direct')
&& $contract->tenant_contract_status != 1) { 
$curr_url = route('tenant-contract.edit',$contract->id);
$editFlag = true;
}else
$curr_url = route('tenant-contract.show',$contract->id);

@endphp 

<!-- assigned_person -->



<tr>                   
  <td>      
	<input type="hidden" name="occuipied_units" id="occuipied_units" value="{{getOccupiedUnits($contract->id)}}">  
   <a class="no-link" @if($editFlag) title="Edit"  @endif   href="{{url($curr_url)}}">{{$contract->tenant_contract_no}}</a>
 </td>
 <td>                     
   <a class="no-link" @if($editFlag) title="Edit"  @endif href="{{url($curr_url)}}">{{$contract->tenant_name}}</a>

 </td>
 <td>                        
  <a class="no-link" @if($editFlag) title="Edit"  @endif href="{{url($curr_url)}}">{{$contract->building_name}}</a>
</td>
<td>
  <a class="no-link" @if($editFlag) title="Edit"@endif href="{{url($curr_url)}}">{{$contract->unit_no}}</a>
</td>

<td>
  <a class="no-link" @if($editFlag) title="Edit"@endif href="{{url($curr_url)}}">{{$contract->unit_types_name}}</a>
</td>
<td>
  <a class="no-link" @if($editFlag) title="Edit"@endif  href="{{url($curr_url)}}">@if($contract->tenant_contract_start_date){{$contract->tenant_contract_start_date->format('d/m/Y')}}@endif</a>                       
</td>
<td>
  <a class="no-link" @if($editFlag) title="Edit"@endif   href="{{url($curr_url)}}">@if($contract->tenant_contract_valid_to_date){{$contract->tenant_contract_valid_to_date->format('d/m/Y')}}@endif</a>                        
</td>

<td>
  <a class="no-link"  @if($editFlag) title="Edit"@endif href="{{url($curr_url)}}">@if($contract->tenant_contract_rent){{numberFormat($contract->tenant_contract_rent)}}@endif</a>                      
</td>
<td>
  <a class="no-link"  @if($editFlag) title="Edit" @endif href="{{url($curr_url)}}">
   @if($contract->tenant_contract_is_reg_municipality==1)
   Yes
   @else
   No
   @endif
 </a>                    
</td>
<td>
  <a class="no-link"  @if($editFlag) title="Edit" @endif href="{{url($curr_url)}}">
    @if($contract->pdc_check==1)
    Yes
    @else
    No
    @endif
  </a>                       
</td>
<td>
  <a class="no-link"  @if($editFlag) title="Edit" @endif href="{{url($curr_url)}}">

    @if($contract->invoice_check==1)
    Yes
    @else
    No
    @endif                         
  </a>                        
</td>
<td>                     
   <a class="no-link" @if($editFlag) title="Edit"  @endif href="{{url($curr_url)}}">{{$contract->tenant_contact_no}}</a>
   
 </td>
@if(!isset($is_tenant_contract))
<td>                     
 <a class="no-link"  @if($editFlag) title="Edit" @endif href="{{url($curr_url)}}" >
  @if($contract->work_flow_processes_code == 107 && $contract->tennat_contract_direct_indirect_status == 1 )
  Direct
  @elseif($contract->tenant_contract_is_revoke == 1)
  Revoke
  @endif
</a>                        
</td>
@endif
<td>

      <!-- <a href="{{route('tenantPdcView',$contract->id)}}" title="View" class="btn btn-tbl-view btn-xs">
          <i class="fa fa-list "></i>
        </a> --> 
        @if(!isset($pendingPage))
        @can('tenant_contract_revoke')
        @if($contract->work_flow_processes_code == '108' && $contract->tenant_contract_is_revoke != 1 && isset($contract->sale_enquiry_id))
        <a title="Revoke" href="{{route('tenantRevoke',$contract->id)}}" class="btn btn-tbl-general btn-xs">
          <i class="fa fa-retweet"></i>
        </a>
        @endif
        @endcan
        @endif
        @if(empty($pendingPage))
        {{-- Normally Edit is only offered on direct contracts still at stage 107.
             Holders of edit_active_tenant_contract (admin) also get it on ACTIVE
             contracts. Terminated contracts stay locked for everyone. --}}
        @if(auth()->user()->can('edit_tenant_contract_direct') || auth()->user()->can('edit_active_tenant_contract'))
        @if(
             ($contract->work_flow_processes_code <= 107   && $contract->tennat_contract_direct_indirect_status == 1 && $contract->tenant_contract_status == 0 && $contract->tenant_renewal_termination_status != 8)
             || (auth()->user()->can('edit_active_tenant_contract') && $contract->tenant_renewal_termination_status != 8)
           )
        <a title="Edit" href="{{route('tenant-contract.edit',$contract->id)}}" class="btn btn-tbl-edit btn-xs" >
          <i class="fa fa-pencil"></i>
        </a>
        @endif
        @endif
        @endif
        @if(isset($title))
        <a  href="{{route('leadAssign.revokeProcess',[$route,$contract->sale_enquiry_id,$contract->work_flow_processes_code])}}" class="btn btn-tbl-view btn-xs" title="View">
          <i class="fa fa-eye"></i>
        </a>
        @if(!$pendingPage)
        @if($contract->work_flow_processes_code == 107 && $contract->tennat_contract_direct_indirect_status == 1 &&
        $contract->is_direct_contract_pending == 2 && auth()->user()->can('tenant_direct_revoke_contract_approve_reject'))  
        {{-- <!--<button title = "Close" type="submit" class="btn btn-tbl-delete btn-xs closed_flow" data-toggle="modal" data-target="#myModal" data-id = "CL" data-enquiryid="{{$contract->sale_enquiry_id}}" data-sales_id="{{$contract->sale_enquiry_id}}" data-workflow_id="{{$contract->work_flow_processes_code }}" data-backdrop="static" data-keyboard="false"><i class="fa fa-times-circle"></i></button> -->--}}
         <button title = "Reject" type="submit" id="reject" class="btn btn-tbl-delete btn-xs reject_direct" data-toggle="modal" data-target="#myModal1" data-id = "RJCT" data-enquiryid="{{$contract->sale_enquiry_id}}" data-tenant_contract_id="{{$contract->id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-window-close-o"></i></button>
        <button title = "Accept" type="submit" id="accept" class="btn btn-tbl-general btn-xs accept_direct" data-toggle="modal" data-target="#myModal" data-id = "ACPT" data-enquiryid="{{$contract->sale_enquiry_id}}" data-tenant_contract_id="{{$contract->id}}" data-workflow_id="{{$contract->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-check"></i></button>  
        @endif 
        @if($contract->work_flow_processes_code == 108  && auth()->user()->can('tenant_direct_revoke_contract_approve_reject'))
        <button title = "Reject" type="submit" id="reject" class="btn btn-tbl-delete btn-xs reject_direct" data-toggle="modal" data-target="#myModal1" data-id = "RJCT" data-enquiryid="{{$contract->sale_enquiry_id}}" data-tenant_contract_id="{{$contract->id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-window-close-o"></i></button>

        <button title = "Accept" type="submit" id="accept" class="btn btn btn-tbl-general btn-xs accept" data-toggle="modal" data-target="#myModal" data-id = "ACPT" data-enquiryid="{{$contract->sale_enquiry_id}}" data-tenant_contract_id="{{$contract->id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-check"></i></button>
        @endif   
        @endif 
        @else
        <a  href="{{route('tenant-contract.show',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="View">
          <i class="fa fa-eye"></i>
        </a>
        @can('tenant_contract_send_for_approval') 
        @if($contract->tennat_contract_direct_indirect_status == 1 && $contract->work_flow_processes_code !=108 && $contract->is_direct_contract_pending != 2 && $contract->tenant_renewal_termination_status != 8 && $contract->contract_work_flow != 108) 
        <a title = "Send For Approval"href="{{route('tenantSendForApproval',[$contract->id,'list'])}}" class="btn btn-tbl-general btn-xs"><i class="fa fa-check"></i></a> 
        @endif
        @endcan
        @endif  

        @can('tenant_contract_status')
        @if($contract->status != 5  && $contract->contract_work_flow == 108)
        <button type="button" class="btn btn-tbl-dark-blue btn-xs changeStatus" data-id="{{$contract->id}}" data-toggle="modal" data-target="#myModal" title="Change Status" data-backdrop="static" data-keyboard="false"><i class="fa fa-window-restore" aria-hidden="true"></i></button>  
        @endif
        @endcan 

        @can('tenant_municipality_details')
        <button type="button" class="btn btn-tbl-violet btn-xs addMunicipality" data-id="{{$contract->id}}" data-toggle="modal" data-target="#myModal" title="Add Municipality" data-backdrop="static" data-keyboard="false"><i class="fa fa-life-ring"></i></button>
        @endcan


        @if($contract->tenant_contract_status == 1)  

        @if(Gate::check('invoice_generation') || Gate::check('invoice_generation_view'))
        <a  href="{{route('invoice.show',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="{{($contract->invoice_check==1 || isset($contract->tenantInvoiceList))?'View Invoice':'Generate Invoice'}}">
          <i class="fa fa-files-o"></i>
        </a> 
        @endif 
        @can('add_discussion_form')
        <button type="button" class="btn btn-tbl-violet btn-xs discussion-forum" data-id="{{$contract->id}}" data-toggle="modal" data-target="#myModal" title="Discussion" data-backdrop="static" data-keyboard="false"><i class="fa fa-commenting" aria-hidden="true"></i></button> 
        @endcan 

        @endif  

        @if($contract->work_flow_processes_code >= 106 || $contract->contract_work_flow >= 106)
        @if(!isset($pendingPage))

        @if(Gate::check('pdc_generation') || Gate::check('pdc_generation_view'))
        <a  href="{{route('pdc.edit',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="PDC Generation">
          <i class="fa fa-book"></i>
        </a> 
        @else
        @can('pdc_view')
        <a  href="{{route('pdcView',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="PDC View">
          <i class="fa fa-book"></i>
        </a> 
        @endcan 
        @endif 
        @endif 
        @endif                      
      </td>
    </tr>




    @php $count++; @endphp 
    @empty
    <tr>
      <td colspan="14" align="center">
       <p>No Record</p>
     </td>
   </tr>
   @endforelse

   @if(isset($request->ajax))	
   <tr>
    <td colspan="8" id="pagination_ajax"> 								  

      {{$tenantContracts->appends(\Request::except(['page','ajax']))->links()}}
      <div class="pagination_info">
       @include('includes.pagination_info',['paginator' => $tenantContracts])         
     </div>

   </td>                           

 </tr>
 @endif


