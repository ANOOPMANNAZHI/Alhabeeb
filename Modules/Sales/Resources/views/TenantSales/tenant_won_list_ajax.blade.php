
@forelse ($lists as $approval_list)
<tr>
    @php 
      if(isset($approval_list->salesEnquiry->tenantContract->tenant_contract_duration_countdown)){
        $duration = $approval_list->salesEnquiry->tenantContract->tenant_contract_duration_countdown;
        $count = explode('-',$duration); 
      }
    @endphp  
    <td><a class="no-link" title="View" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">
      {{$approval_list->tenant_name}}</a></td>
    <td><a class="no-link" title="View" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">{{$approval_list->agre_build}}</a></td>
    <td><a class="no-link" title="View" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">{{$approval_list->agre_unit}}</a></td>
    <td><a class="no-link" title="View" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">{{$approval_list->unit_type_name}}</a></td>
    <td><a class="no-link" title="View" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">
      @if(isset($approval_list->agre_duration))
      {{$approval_list->agre_duration}}
      
      @endif</a>
    </td>
   <td align="center"><a class="no-link" title="View" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">
           {{$approval_list->unit_usage}}
        </a>
    </td>
   
    <td align="center"><a  title="View" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">
            <i class="fa fa-eye"></i>
        </a>
    </td>
                                         
</tr>
@empty
<tr>
    <td colspan="8" align="center">
      <p>No Record</p>
    </td>
</tr>
@endforelse
                                
@if(isset($request->ajax))	
  <tr>
	  <td colspan="5" id="pagination_ajax"> 										  
								        
			{{$lists->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}
					<div class="pagination_info">
          @include('includes.pagination_info',['paginator' => $lists])         
        </div>			        
		</td>                           
  </tr>
@endif
