@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Receipt</div>
    </div>
    {{ Breadcrumbs::render('receiptsTabViewList') }} 
    
  </div>
</div>

<div class="clearfix"></div>

<div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
            
<div class="panel-body">
        <div class="tab-pane   active" id="rent">
            <div class="clearfix"></div>
            <div class="table-wrap">
                    <div style="overflow-x:auto;">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                        <thead>
                            <tr>                                
                                <th title="Serial no">Sl No.</th>
                                <th title="Serial no">Type</th>
                                <th title="Receipt No">Rec No</th>
                                <th title="Building Name">Bldg. Name</th>
                                <th title="Building Code">Bldg. Code</th>
                                <th title="Tenant Name">Tenant Name</th>
                                <th title="Tenant Code">Tenant Code</th>
                                <th title="Agreement No">Agre. No</th>
                                <th title="Payment Method">Pay Method</th>
                                <th title="Amount">Amount</th>
                                <th title="Status">Status</th>
                            </tr>
                        </thead>
                        <tbody>

    @forelse($receiptslist as $receipt)                                      
    <tr>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receiptslist->perPage()*($receiptslist->currentPage()-1)+$count}} </a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$type[$receipt->receipts_generation_type]}}</a></td>
     <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->receipts_generation_receipt_no}}</a></td>
     
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->building_name}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->building_code}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->tenant_name}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->tenant_code}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->tenant_contract_no}}</a></td>
      
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{$receipt->ReceiptsGenerationPaymentMethodName}}</a></td>
      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">{{numberFormat($receipt->receipts_generation_amt)}} OMR</a></td>

      <td><a class="no-link" href="{{route('rentReceiptGeneration.show',$receipt->id)}}">
      @if($receipt->receipts_generation_status == 3 )
          <span class="label label-info label-mini">Post</span>
      {{-- PDC Bounce case - Generated receipt should change to cancel status  --}}
      @elseif($receipt->receipts_generation_status == 2)
          <span class="label label-event label-mini">Cancel</span> 
      @else
       @php $status = explode('|',$receipt->ReceiptsGenerationApprovalStatusName) @endphp
       <span class="label {{reset($status)}} label-mini"> {{end($status)}}</span></a>
     </td>
      @endif     
  </tr>
  @php $count++; @endphp 
  @empty
  <tr>
        <td colspan="13" align="center">
            <p>No Record</p>
        </td>
  </tr>
  @endforelse   



                                
                        </tbody>
                        </table>
                        
                    </div>
                </div>
                <div id="pagination">
                    <div class="text-center">  

                     {{$receiptslist->appends(\Request::except(['page','_token']))->links()}}         
                
                   </div>
             
                </div>


</div>
</div>
</div>
<!--Payment ends -->
<div class="clearfix"></div>

<!--Remaining Invoices ends -->
</div>
</div>

@endsection
