 @forelse ($lists as $approval_list)
 <tr>

    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}">
      {{$approval_list->cust}}</a></td>
    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}">
      {{$approval_list->cust_no}}</a></td>                                    
    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}">{{$approval_list->sales_notes??''}}</a></td>
    
    <td align="center">
        @can('reopen_tenant_enquiry')    
        <button title="Re-Open" type="button" class="btn btn-tbl-general btn-xs reopen" data-toggle="modal" data-target="#myModal" data-id = "{{$approval_list->sales_enquiry_id}}" datas-id= "{{$approval_list->sales_work_flow}}" data-backdrop="static" data-keyboard="false">
            <i class="fa fa-repeat" aria-hidden="true"></i>
        </button>
        @endcan
        <a  title="View" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">
            <i class="fa fa-eye"></i>
        </a>

    </td>
</tr>

@empty
<tr>
    <td colspan="4" align="center">
        <p>No Record</p>
    </td>
</tr>
@endforelse
@if(isset($request->ajax))	
<tr>
   <td colspan="3" id="pagination_ajax"> 

     {{$lists->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}
     <div class="pagination_info">
      @include('includes.pagination_info',['paginator' => $lists])         
  </div>
</td>       

</tr>
@endif
