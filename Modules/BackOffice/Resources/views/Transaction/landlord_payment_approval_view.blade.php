@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Landlord Payment Approval View</div>
    </div>

    {{ Breadcrumbs::render('landlordPaymentApprovalShow',$landlordPaymentApproval) }} 

  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
     <div class="row">
      <div class="col-sm-12">
        @can('landlord_payment_approval_approve')
        @if($landlordPaymentApproval->landlord_payment_status ==1 && $landlordPaymentApproval->landlord_payment_approval_status == 2)
        <a href="{{ route('landlordPaymentAction',[$landlordPaymentApproval->id,2,4]) }}" title="Approve" class="btn btn-circle btn-success align-right">
          Approve
        </a>
        @endif
        @endcan 

        @can('landlord_payment_approval_unapprove')
        @if($landlordPaymentApproval->landlord_payment_status ==1 && $landlordPaymentApproval->landlord_payment_approval_status == 3)
        <a href="{{ route('landlordPaymentAction',[$landlordPaymentApproval->id,4,3]) }}" title="UnApprove" class="btn btn-circle btn-warning align-right">
          UnApprove
        </a>
        @endif
        @endcan

        @can('landlord_payment_approval_reject')
        @if($landlordPaymentApproval->landlord_payment_status ==1 && $landlordPaymentApproval->landlord_payment_approval_status == 2)
        <a href="{{ route('landlordPaymentAction',[$landlordPaymentApproval->id,1,5]) }}" title="Reject" class="btn btn-circle btn-danger align-right">
          Reject
        </a>
        @endif
        @endcan

      </div>
    </div>
    <div class="dataSearchBox">
      <div class="card-body row">
        @if(isset($landlordPaymentApproval->landlord_payment_no))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Payment No :  </b><span>{{$landlordPaymentApproval->landlord_payment_no}}</span></h5>
          </div>
        </div>
        @endif
        @if(isset($landlordPaymentApproval->landlord_payment_date)) 
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Payment Date  :  </b><span>{{$landlordPaymentApproval->landlord_payment_date->format('d/m/Y')}}</span></h5>
          </div>
        </div>
        @endif
      </div>
    </div>
    <div class="sub-head">Landlord Detail</div>
    <div class="dataSearchBox">    
      <div class="card-body row">

        @if(isset($landlordPaymentApproval->landlord_contract_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Name :  </b><span>{{$landlordPaymentApproval->landlordContract->vendorInfo->vendor_name}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->landlord_contract_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Code :  </b><span>{{$landlordPaymentApproval->landlordContract->vendorInfo->vendor_code}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->landlord_contract_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Agreement No :  </b><span>{{$landlordPaymentApproval->landlordContract->landlord_contract_no}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->landlord_contract_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Building Name :  </b><span>{{$landlordPaymentApproval->landlordContract->buildingInfo->building_name}}</span></h5>
          </div>
        </div> 
        @endif
      </div>
    </div>
    <div class="sub-head">Payment Detail</div>
    <div class="dataSearchBox">    
      <div class="card-body row">

        @if(isset($landlordPaymentApproval->landlord_invoice_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Invoice No :  </b><span>{{$landlordPaymentApproval->landlordInvoice->landlord_invoice_voucher_no}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->landlord_payment_currency_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Currency ID  :  </b><span>{{$landlordPaymentApproval->currency->currency_name}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->landlord_payment_method))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Payment Method :  </b><span>{{$landlordPaymentApproval->landlord_payment_method_name}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->landlord_payment_cheque_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Cheque Number :  </b><span>{{$landlordPaymentApproval->landlord_payment_cheque_no}}</span></h5>
        </div>
      </div> 
      @endif
        @if(isset($landlordPaymentApproval->bank_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Bank :  </b><span>{{$landlordPaymentApproval->bankInfo->bank_name}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->landlord_payment_invoice_amt))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Invoice Amount :  </b><span>{{numberFormat($landlordPaymentApproval->landlord_payment_invoice_amt)}} OMR</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->landlord_payment_balance_amt))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Balance Amount :  </b><span>{{numberFormat($landlordPaymentApproval->landlord_payment_balance_amt)}} OMR</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->landlord_payment_amount))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Amount :  </b><span>{{numberFormat($landlordPaymentApproval->landlord_payment_amount)}} OMR</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->landlord_payment_comment))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Comment :  </b><span>{{$landlordPaymentApproval->landlord_payment_comment}}</span></h5>
          </div>
        </div> 
        @endif
      </div>
    </div>
    @if(isset($landlordPaymentApproval->ax_batch_id) || isset($landlordPaymentApproval->ax_payment_no))
    <div class="dataSearchBox">    
      <div class="card-body row">

        @if(isset($landlordPaymentApproval->ax_batch_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>AX Batch ID :  </b><span>{{$landlordPaymentApproval->ax_batch_id}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($landlordPaymentApproval->ax_payment_no))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>AX Payment No :  </b><span>{{$landlordPaymentApproval->ax_payment_no}}</span></h5>
          </div>
        </div> 
        @endif
      </div>
    </div>
    @endif
  </div>    
</div> 
@endsection
@section('scripts')
@endsection
