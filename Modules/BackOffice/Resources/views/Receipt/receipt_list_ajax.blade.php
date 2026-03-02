@php $count = 1; @endphp
  @forelse($receiptslist as $receipt)      
	@php
		$p_method = $receipt->ReceiptsGenerationPaymentMethodName;
		if($receipt->ReceiptsGenerationPaymentMethodName == ''){
			$p_method = 'Bank Transfer';
		}
		
	@endphp

    <tr>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receiptslist->perPage()*($receiptslist->currentPage()-1)+$count}} </a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->receipts_generation_receipt_no}}</a></td>
      <td>
        @if(isset($receipt->receipts_generation_receipt_date))<a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->receipts_generation_receipt_date->format('d/m/Y')}} </a>@endif</td>
       <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->tenant_name}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->tenant_code}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->tenant_contract_no}}</a></td>
       <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->unit_no}}</a></td>
       <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->building_name}}</a></td>
       @if($tab=='rent')
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->receipts_generation_eff_from->format('d/m/Y')}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->receipts_generation_eff_to->format('d/m/Y')}}</a></td>
      @endif
      
      <!-- <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->building_code}}</a></td> -->

      
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$p_method}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{numberFormat($receipt->receipts_generation_amt)}}</a></td>

      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">
      @if($receipt->receipts_generation_status == 3 )
          <span class="label label-info label-mini"  style="min-width:70px !important;padding:8px 2px">Post</span>
      {{-- PDC Bounce case - Generated receipt should change to cancel status  --}}
      @elseif($receipt->receipts_generation_status == 2)
          <span class="label label-event label-mini"  style="min-width:70px !important;padding:8px 2px">Cancel</span> 
      @else
       @php $status = explode('|',$receipt->ReceiptsGenerationApprovalStatusName) @endphp
       <span class="label {{reset($status)}} label-mini"  style="min-width:70px !important;padding:8px 2px"> {{end($status)}}</span></a></td>
      @endif
      <td>
      @can('edit_tenant_receipt')
		  @if(($receipt->receipts_generation_approval_status == 1 || $receipt->receipts_generation_approval_status == 4 ) && $receipt->receipts_generation_status != 2)  
			  @if($receipt->receipts_generation_type === 0)
			  <a class="btn btn-tbl-edit btn-xs" title="Edit" href="{{route('rentReceiptGeneration.edit',$receipt->id)}}">
				<i class="fa fa-pencil"></i>
			  </a>  
			  @elseif($receipt->receipts_generation_type === 1)
			  <a class="btn btn-tbl-edit btn-xs" title="Edit" href="{{route('updateGeneralReceipt',$receipt->id)}}">
				<i class="fa fa-pencil"></i>
			  </a>
			  @elseif($receipt->receipts_generation_type === 2)
			  <a class="btn btn-tbl-edit btn-xs" title="Edit" href="{{route('updateDepositReceipt',$receipt->id)}}">
				<i class="fa fa-pencil"></i>
			  </a>
			  @endif
		  @endif
      @endcan
      @can('view_tenant_receipt')
      <a title="View Receipt" href="{{route('rentReceiptGeneration.show',$receipt->id)}}" class="btn btn-tbl-view btn-xs ">
              <i class="fa fa-eye"></i>
       </a>
       @endcan
	   <a title="Print" href="{{route('printPreview',$receipt->id)}}" class="btn btn-tbl-print btn-xs btnprn">
              <i class="fa fa-print"></i>
       </a>
       {{-- <!-- 
            1 - Unapproved , 4 - rejected, 3 -Approved
            2 - $receipt->receipts_generation_status - PDC Cancel 
        --> --}}
      
       @if(auth()->user()->can('request_approval_receipt') && in_array($receipt->receipts_generation_approval_status , [1,3]) && $receipt->receipts_generation_status != 2) 
          <form action="{{route('receiptApprovalStatus')}}" method="POST" onclick="return confirm('Do you want to Continue?')">
          {{csrf_field()}}
          <input type="hidden" name="receipt_id" value="{{$receipt->id}}"> 
          @can('request_approval_receipt')
            <button title="{{($receipt->receipts_generation_approval_status==1)?'Approve':'UnApprove'}}" type="submit" class="btn btn-tbl-general closed" value="{{($receipt->receipts_generation_approval_status==1)?3:1}}"   name="approve_btn" >
              @if($receipt->receipts_generation_approval_status==1)
              <i class="fa fa-reply"></i>
              @else
              <i class="fa fa-check"></i>
              @endif
            </button>
          @endcan
        </form>  

       @else
       @can('send_for_approval_receipt')
       @if(($receipt->receipts_generation_approval_status == 1 || $receipt->receipts_generation_approval_status == 4) && $receipt->receipts_generation_status === 0)
       <a   href="{{route('receiptApprovalStatus')}}"  title="Send For Approval" id="{{$receipt->id}}" class="btn btn-tbl-general btn-xs send_request_receipt">
       <i class="fa fa-hand-o-right" aria-hidden="true"></i> 
       </a>
       @endif
       @endcan
       {{-- <!-- 
            3 - Approved 
        --> --}}
       @can('send_for_unapproval_receipt')
       @if($receipt->receipts_generation_approval_status == 3 &&  ($receipt->receipts_generation_status == 1 || $receipt->receipts_generation_status === 0))
       <a href="{{route('receiptApprovalStatus')}}" title="Request For Draft" id="{{$receipt->id}}" class="btn btn-tbl-general btn-xs send_request_unapprove">
       <i class="fa fa-hand-o-left" aria-hidden="true"></i> 
       </a>
       @endcan 
       @endif
       @endif
       @can('delete_tenant_receipt')
       @if($receipt->receipts_generation_approval_status == 1  && $receipt->receipts_generation_status === 0) 
        <a href="{{route('rentReceiptGeneration.destroy',$receipt->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
        </a> 
       @endif
       @endcan 
       @can('post_tenant_receipt')
       @if($receipt->receipts_generation_approval_status == 3 && $receipt->receipts_generation_status != 3) 
       <a href="{{route('receiptAsPosted')}}" title="Post" class="btn btn-tbl-violet btn-xs post_type" id="{{$receipt->id}}">
                <i class="fa fa-pie-chart" aria-hidden="true"></i>
       </a>
       @endif  
       @endcan               
      </td>
  </tr>
  @php $count++; @endphp 
  @empty
  <tr>
        <td colspan="13" align="center">
            <p>No Record</p>
        </td>
  </tr>
  @endforelse   
  @if(isset($request->ajax))    
    <tr>
      <td colspan="12" id="pagination_ajax">
        {{$receiptslist->withPath($route)->appends(\Request::except(['ajax','page','_token']))->links()}}

          <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $receiptslist])         
                               </div>
      </td>
     </tr>            
  @endif
  <script>
$(document).ready(function() {
  // ---- Print Function
  $('.btnprn').printPage();
});
</script>
