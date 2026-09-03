@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Deposit Refund View</div>
    </div>

    {{ Breadcrumbs::render('depositRefund.show',$depositRefund) }} 

  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
     <div class="row">
      <div class="col-sm-12">
 
       @if(auth()->user()->can('deposit_refund_edit') && in_array($depositRefund->deposit_refund_approval_status,[0,1,5]) == true && $depositRefund->deposit_refund_status != 3) 
       <a title="Edit" href="{{route('depositRefund.edit',$depositRefund->id)}}" class="btn btn-circle btn-primary align-right" title="Edit">
         Edit
       </a>    
       @endif                                          
     
      @if (Auth::user()->hasPermissionTo('deposit_refund_approval_approve') && in_array($depositRefund->deposit_refund_status,[1,4]) && in_array($depositRefund->deposit_refund_approval_status ,[0,1,5]))
     
        <a href="{{ route('depositRefundAction',[$depositRefund->id,2,4]) }}" title="Approve" class="btn btn-circle btn-success align-right">
          Approve
        </a>
     
      @elseif(Auth::user()->hasPermissionTo('sent_for_approval') &&
        in_array($depositRefund->deposit_refund_approval_status ,[0,1,5]))
       <a href="{{ route('depositRefundAction',[$depositRefund->id,1,2]) }}" title="Sent For Approval" class="btn btn-circle btn-success align-right">
         Send for Approval
       </a>
       @endif
      
      @if(Auth::user()->hasPermissionTo('deposit_refund_approval_approve') && in_array($depositRefund->deposit_refund_status,[2]) && in_array($depositRefund->deposit_refund_approval_status ,[4]))
         <a href="{{ route('depositRefundAction',[$depositRefund->id,4,1]) }}" title="UnApprove" class="btn btn-circle btn-success align-right">
          UnApprove
        </a>
     
      @elseif (Auth::user()->hasPermissionTo('sent_for_unapproval') && in_array($depositRefund->deposit_refund_approval_status ,[4]) && in_array($depositRefund->deposit_refund_status ,[2]))
       <a href="{{ route('depositRefundAction',[$depositRefund->id,1,3]) }}" title="Sent For UnApproval" class="btn btn-circle btn-warning align-right">
         Send for UnApproval
       </a>
       @endif

       {{-- @if(auth()->user()->can('deposit_refund_post') && $depositRefund->deposit_refund_status != 3) --}}
       @can('deposit_refund_post')
       @if($depositRefund->deposit_refund_status == 2 && $depositRefund->deposit_refund_approval_status == 4)   
       
       <a href="{{ route('depositRefundPost',[$depositRefund->id,3]) }}" title="Post" class="btn btn-circle btn-info align-right post_type">
         Post
       </a>
       @endif
       @endcan
       {{-- @endif --}}
       @can('deposit_refund_cancel')
       @if(in_array($depositRefund->deposit_refund_approval_status,[0,1,5]))
      <button type="button" class="btn btn-circle btn-danger closed align-right" data-toggle="modal" data-target="#myModal" data-id="{{ $depositRefund->id }}" title="Delete" data-backdrop="static" data-keyboard="false">
        Delete
      </button>
      @endif
      @endcan
      @if (!Auth::user()->hasPermissionTo('deposit_refund_approval_approve'))
      @can('deposit_refund_cancel')
      @if(in_array($depositRefund->deposit_refund_approval_status,[0,1,5]))
      <button type="button" class="btn btn-circle btn-danger closed align-right" data-toggle="modal" data-target="#myModal" data-id="{{ $depositRefund->id }}" title="Delete" data-backdrop="static" data-keyboard="false">
        Cancel
      </button>
      @endif
      @endcan 
      @endif 
    </div>
  </div>
  <div class="dataSearchBox">
    <div class="card-body row">
      @if(isset($depositRefund->deposit_refund_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Payment No :  </b><span>{{$depositRefund->deposit_refund_no}}</span></h5>
        </div>
      </div>
      @endif
      @if(isset($depositRefund->deposit_refund_date)) 
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Payment Date  :  </b><span>{{$depositRefund->deposit_refund_date->format('d/m/Y')}}</span></h5>
        </div>
      </div>
      @endif
    </div>
  </div>
  <div class="sub-head">Tenant Detail</div>
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($depositRefund->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Building Name :  </b><span>{{$depositRefund->tenantContract->building->building_name}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Building Code :  </b><span>{{$depositRefund->tenantContract->building->building_code}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Unit No :  </b><span>{{$depositRefund->tenantContract->unit->unit_no}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Unit Code :  </b><span>{{$depositRefund->tenantContract->unit->unit_code}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Tenant Name :  </b><span>{{$depositRefund->tenantContract->tenant->tenant_name}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Tenant Code :  </b><span>{{$depositRefund->tenantContract->tenant->tenant_code}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->tenant_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Agreement No :  </b><span>{{$depositRefund->tenantContract->tenant_contract_no}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->deposit_refund_valid_from))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Valid From :  </b><span>{{$depositRefund->deposit_refund_valid_from->format('d/m/Y')}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->deposit_refund_valid_to))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Valid To :  </b><span>{{$depositRefund->deposit_refund_valid_to->format('d/m/Y')}}</span></h5>
        </div>
      </div> 
      @endif
    </div>
  </div>
  <div class="sub-head">Payment Detail</div>
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($depositRefund->receipts_generation_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Deposit Receipt No :  </b><span>{{$depositRefund->receiptGeneration->receipts_generation_receipt_no}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->deposit_refund_payment_method))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Payment Method  :  </b><span>{{$depositRefund->deposit_refund_payment_method_name}}</span></h5>
        </div>
      </div> 
      @endif
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Bank :  </b><span>{{$depositRefund->bankInfo->bank_name}}</span></h5>
        </div>
      </div> 
      @if(isset($depositRefund->deposit_refund_cheque_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Cheque No :  </b><span>{{$depositRefund->deposit_refund_cheque_no}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->deposit_refund_amt))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Refund Amount :  </b><span>{{numberFormat($depositRefund->deposit_refund_amt)}} OMR</span></h5>
          <h5 class="details"><b>Original Deposit Amount :  </b><span>{{numberFormat($depositRefund->receiptGeneration->receipts_generation_amt)}} OMR</span></h5>
@if(count($depositRefund->depositRefundDeduction) > 0)
          <table class="table" style="max-width:500px;">
            <thead>
              <tr><th>Reason</th><th>Description</th><th class="text-right">Amount</th></tr>
            </thead>
            <tbody>
@foreach($depositRefund->depositRefundDeduction as $deduction)
              <tr>
                <td>{{$deduction->deduction_reason}}</td>
                <td>{{$deduction->description}}</td>
                <td class="text-right">{{numberFormat($deduction->amount)}}</td>
              </tr>
@endforeach
            </tbody>
          </table>
@endif
        </div>
      </div>
      @endif

      @if(isset($depositRefund->deposit_refund_comment))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Comment :  </b><span>{{$depositRefund->deposit_refund_comment}}</span></h5>
        </div>
      </div> 
      @endif
    </div>
  </div>
  @if(isset($depositRefund->ax_batch_id) || isset($depositRefund->ax_invoice_no))
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($depositRefund->ax_batch_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>AX Batch ID :  </b><span>{{$depositRefund->ax_batch_id}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->ax_invoice_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>AX Invoice No :  </b><span>{{$depositRefund->ax_invoice_no}}</span></h5>
        </div>
      </div> 
      @endif
    </div>
  </div>
  @endif
  <div class="sub-head">Financial Dimension</div>
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($depositRefund->dim1_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Division :  </b><span>{{($depositRefund->dim2->ax_division=='01')?'HO':'PLM'}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($depositRefund->dim2_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Building :  </b><span>{{$depositRefund->dim2->building_name}}</span></h5>
        </div>
      </div> 
      @endif
    </div>
  </div>


  <div class="sub-head">Distribution Detail</div>

  <div class="dataSearchBox ">    
    <div class="row"> 
      <table class="table" >
       <thead>
        <tr>
         <th>Account Code</th>
         <th>Type</th>
         <th class="text-right">Debit Amount</th>
         <th class="text-right">Credit Amount</th>
                         
       </tr>
       
     </thead>

     <tbody>
      @foreach($depositRefund->depositRefundDimension as  $depositRefundDimensions) 
      <tr>
       <td>{{ ($depositRefundDimensions->account_code ==0)?$depositRefund->bankInfo->bank_code:$depositRefundDimensions->account_code.'-'.$depositRefundDimensions->description}}</td>
       <td>{{ $depositRefundDimensions->type??''}}</td>
       <td class="text-right">{{ numberFormat($depositRefundDimensions->debit_amount)}}</td>
       <td class="text-right">{{ numberFormat($depositRefundDimensions->credit_amount)}}</td>
       
     </tr>
     @endforeach 
     <tr>
       <td></td>
       <td> Total : </td>
       <td class="text-right">{{numberFormat($debitAmount)}}</td>
       <td class="text-right">{{numberFormat($creditAmount)}}</td>
             
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
	 $(document).on('click','.post_type',function(){  

        if (confirm('Do you want to Post this Refund ?')) {
             return true;
        } else {
            return false;
        }
    });

  });
</script>
@endsection
