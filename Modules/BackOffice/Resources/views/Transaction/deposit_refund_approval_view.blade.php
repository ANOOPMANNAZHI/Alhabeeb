@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Deposit Refund Approval View</div>
    </div>

    {{ Breadcrumbs::render('depositRefundApproval',$depositRefundApproval) }} 

  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
     <div class="row">
      <div class="col-sm-12">
       @can('deposit_refund_approval_approve')
       @if($depositRefundApproval->deposit_refund_status ==1 && $depositRefundApproval->deposit_refund_approval_status == 2)
       <a href="{{ route('depositRefundAction',[$depositRefundApproval->id,2,4]) }}" title="Approve" class="btn btn-circle btn-success align-right">
        Approve
      </a>
      @endif
      @endcan 

      @can('deposit_refund_approval_unapprove')
      @if($depositRefundApproval->deposit_refund_status ==1 && $depositRefundApproval->deposit_refund_approval_status == 3)
      <a href="{{ route('depositRefundAction',[$depositRefundApproval->id,4,3]) }}" title="UnApprove" class="btn btn-circle btn-warning align-right">
        UnApprove
      </a>
      @endif
      @endcan

      @can('deposit_refund_approval_reject')
      @if($depositRefundApproval->deposit_refund_status ==1 && $depositRefundApproval->deposit_refund_approval_status == 2)
      <a href="{{ route('depositRefundAction',[$depositRefundApproval->id,1,5]) }}" title="Reject" class="btn btn-circle btn-danger align-right">
        Reject
      </a>
      @endif
      @endcan  

    </div>
  </div>
  <div class="dataSearchBox">
    <div class="card-body row">
      @if(isset($depositRefundApproval->deposit_refund_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Payment No :  </b><span>{{$depositRefundApproval->deposit_refund_no}}</span></h5>
        </div>
      </div>
      @endif
      @if(isset($depositRefundApproval->deposit_refund_date)) 
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Payment Date  :  </b><span>{{$depositRefundApproval->deposit_refund_date->format('d/m/Y')}}</span></h5>
        </div>
      </div>
      @endif
    </div>
  </div>
  <div class="sub-head">Tenant Detail</div>
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($depositRefundApproval->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Building Name :  </b><span>{{$depositRefundApproval->tenantContract->building->building_name}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Building Code :  </b><span>{{$depositRefundApproval->tenantContract->building->building_code}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Unit No :  </b><span>{{$depositRefundApproval->tenantContract->unit->unit_no}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Unit Code :  </b><span>{{$depositRefundApproval->tenantContract->unit->unit_code}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Tenant Name :  </b><span>{{$depositRefundApproval->tenantContract->tenant->tenant_name}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Tenant Code :  </b><span>{{$depositRefundApproval->tenantContract->tenant->tenant_code}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Agreement No :  </b><span>{{$depositRefundApproval->tenantContract->tenant_contract_no}}</span></h5>
        </div>
      </div> 
      @endif
       @if(isset($depositRefundApproval->deposit_refund_valid_from))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Valid From :  </b><span>{{$depositRefundApproval->deposit_refund_valid_from->format('d/m/Y')}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->deposit_refund_valid_to))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Valid To :  </b><span>{{$depositRefundApproval->deposit_refund_valid_to->format('d/m/Y')}}</span></h5>
        </div>
      </div> 
      @endif
    </div>
  </div>
  <div class="sub-head">Payment Detail</div>
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($depositRefundApproval->receipts_generation_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Deposit Receipt No :  </b><span>{{$depositRefundApproval->receiptGeneration->receipts_generation_receipt_no}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->deposit_refund_payment_method))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Payment Method  :  </b><span>{{$depositRefundApproval->deposit_refund_payment_method_name}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->deposit_refund_cheque_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Cheque No :  </b><span>{{$depositRefundApproval->deposit_refund_cheque_no}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->deposit_refund_amt))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Refund Amount :  </b><span>{{numberFormat($depositRefundApproval->deposit_refund_amt)}}</span></h5>
        </div>
      </div> 
      @endif
     
      @if(isset($depositRefundApproval->deposit_refund_comment))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Comment :  </b><span>{{$depositRefundApproval->deposit_refund_comment}}</span></h5>
        </div>
      </div> 
      @endif
    </div>
  </div>
  @if(isset($depositRefundApproval->ax_batch_id) || isset($depositRefundApproval->ax_invoice_no))
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($depositRefundApproval->ax_batch_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>AX Batch ID :  </b><span>{{$depositRefundApproval->ax_batch_id}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->ax_invoice_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>AX Invoice No :  </b><span>{{$depositRefundApproval->ax_invoice_no}}</span></h5>
        </div>
      </div> 
      @endif
    </div>
  </div>
<div class="sub-head">Financial Dimension</div>
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($depositRefundApproval->dim1))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Division :  </b><span>{{$depositRefundApproval->depositRefundDim->dim1->dim_value}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefundApproval->dim2))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Building :  </b><span>{{$depositRefundApproval->depositRefundDim->dim2->building_name}}</span></h5>
        </div>
      </div> 
      @endif
    </div>
  </div>
@endif

  <div class="sub-head">Distribution Detail</div>

  <div class="dataSearchBox ">    
    <div class="row">
      <table class="table" >
       <thead>
        <tr>
         <th>Account Code</th>
         <th>Description</th>
         <th>Type</th>
         <th>Debit Amount</th>
         <th>Credit Amount</th>
         <th>Dim1</th>
         <th>Dim2</th>                       
         <th></th>                       
       </tr>

     </thead>

     <tbody>
      @foreach($depositRefundApproval->depositRefundDimension as  $depositRefundDimensions) 
      <tr>
       <td>{{ $depositRefundDimensions->accountCode->acc_code_val ?? ''}}</td>
       <td>{{ $depositRefundDimensions->accountCode->acc_code_desc ?? ''}}</td>
       <td>{{ $depositRefundDimensions->type ?? ''}}</td>
       <td>{{ numberFormat($depositRefundDimensions->debit_amount) ?? ''}}</td>
       <td>{{ numberFormat($depositRefundDimensions->credit_amount) ?? ''}}</td>
       <td>{{ $depositRefundDimensions->dim1->dim_value ?? ''}}</td>
       <td>{{ $depositRefundDimensions->dim2->building_name ?? ''}}</td>
     </tr>
     @endforeach 
     <tr>
       <td></td>
       <td></td>
       <td> Total : </td>
       <td>{{$debitAmount ?? ''}} </td>
       <td>{{$creditAmount ?? ''}}</td>
       <td></td>
       <td></td>
     </tr>
   </tbody>
 </table>
</div>
</div>



</div>    
</div> 
<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {

    $('.closed').on('click', function(e) {        
      var id = $(this).attr('data-id');
      $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('cancelLandlordPayment')}}", // This is the url we gave in the route
            data: {'id':id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
             },
           });
      return true;
    });
  });
</script>
@endsection
