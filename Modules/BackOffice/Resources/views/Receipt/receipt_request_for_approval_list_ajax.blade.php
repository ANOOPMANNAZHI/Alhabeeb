@php $count = 1; @endphp
@forelse($receiptslist as $receipt)                                      
  <tr>
                                    
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receiptslist->perPage()*($receiptslist->currentPage()-1)+$count}}</a></td>
      
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->receipts_generation_receipt_no}}</a></td>
      @if($tab=='rent')
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->receipts_generation_eff_from->format('d/m/Y')}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->receipts_generation_eff_to->format('d/m/Y')}}</a></td>
      @endif
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->building_name}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->building_code}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->tenant_name}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->tenant_code}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->tenant_contract_no}}</a></td>
      
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->ReceiptsGenerationPaymentMethodName}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{number_format($receipt->receipts_generation_amt,3)}} OMR</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">
		@php $status = explode('|',$receipt->ReceiptsGenerationApprovalStatusName) @endphp
        
        <span class="label {{reset($status)}} label-mini"> {{end($status)}}</span>
        </a>
      </td>
    
      <td>
     
      <form action="{{route('receiptApprovalStatus')}}" method="POST">
       <a title="View" href="{{route('rentReceiptGeneration.show',$receipt->id)}}" class="btn btn-tbl-view btn-xs ">
              <i class="fa fa-eye"></i>
      </a>
      {{csrf_field()}}
      <input type="hidden" name="receipt_id" value="{{$receipt->id}}"> 
      @can('request_approval_receipt')
      <button title="Approve" type="submit" class="btn btn-tbl-general closed" value="3"   name="approve_btn" >
      <i class="fa fa-check "></i>
     </button>
     <button title="Reject" type="submit" class="btn btn-tbl-reject btn-xs assignLead " name="reject_btn" value="4">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
     </button>
     @endcan
	  <a title="Print" href="{{route('printPreview',$receipt->id)}}" class="btn btn-tbl-print btn-xs btnprn">
              <i class="fa fa-print"></i>
       </a>
      </form>  
      </td>
  </tr>

  @php $count++; @endphp 
  @empty
  <tr>
        <td colspan="12" align="center">
            <p>No Record</p>
        </td>
  </tr>
  @endforelse   
  @if(isset($request->ajax))    
    <tr>
      <td colspan="12" id="pagination_ajax"> 
          @php 
            if(!empty($request->receipt_no))
            $receiptslist->appends(['tenant_contract_no' => $request->receipt_no]);
            
            if(!empty($request->tenant_name))
            $receiptslist->appends(['tenant_name' => $request->receipt_dt]);
            
            if(!empty($request->bldg_name))
            $receiptslist->appends(['building_name' => $request->bldg_name]);

            if(!empty($request->bldg_code))
            $receiptslist->appends(['building_code' => $request->bldg_code]);
            
            if(!empty($request->tenant_name))
            $receiptslist->appends(['tenant_name' => $request->tenant_name]);
            
            if(!empty($request->tenant_code))
              $receiptslist->appends(['tenant_code' => $request->tenant_code]);
            
            if(!empty($request->payment_type))
            $receiptslist->appends(['contract_no' => $request->payment_type]);
            
            if(!empty($request->invoice_check))
            $receiptslist->appends(['invoice_check' => $request->amount]);
            
            if(!empty($request->payment_type))
            $receiptslist->appends(['payment_type' => $request->status]);
            
            
            if(!empty($request->tenant_contract_valid_to_date))
            $receiptslist->appends(['amount' => $request->amount]);

            if(!empty($request->status))
            $receiptslist->appends(['status' => $request->status]);
            
          @endphp           
                       
           {{$receiptslist->links()}}

           <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $receiptslist])         
                               </div>
        
	</td>                           

 </tr>            
  @endif
