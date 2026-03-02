@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Maintenance Payment Approval View</div>
    </div>

    {{ Breadcrumbs::render('maintenancePaymentApprovalShow',$maintenancePaymentApproval) }} 

  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
     <div class="row">
      <div class="col-sm-12">
        @can('maintenance_payment_approval_approve')
        @if($maintenancePaymentApproval->maintenance_payment_status ==1 && $maintenancePaymentApproval->maintenance_payment_approval_status == 2)
        <a href="{{ route('maintenancePaymentAction',[$maintenancePaymentApproval->id,2,4]) }}" title="Approve" class="btn btn-circle btn-success align-right">
          Approve
        </a>
        @endif
        @endcan 

        @can('maintenance_payment_approval_unapprove')
        @if($maintenancePaymentApproval->maintenance_payment_status ==1 && $maintenancePaymentApproval->maintenance_payment_approval_status == 3)
        <a href="{{ route('maintenancePaymentAction',[$maintenancePaymentApproval->id,4,3]) }}" title="UnApprove" class="btn btn-circle btn-warning align-right">
          UnApprove
        </a>
        @endif
        @endcan

        @can('maintenance_payment_approval_reject')
        @if($maintenancePaymentApproval->maintenance_payment_status ==1 && $maintenancePaymentApproval->maintenance_payment_approval_status == 2)
        <a href="{{ route('maintenancePaymentAction',[$maintenancePaymentApproval->id,1,5]) }}" title="Reject" class="btn btn-circle btn-danger align-right">
          Reject
        </a>
        @endif
        @endcan  
      </div>
    </div>
    <div class="dataSearchBox">
      <div class="card-body row">
        @if(isset($maintenancePaymentApproval->maintenance_payment_no))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Payment No :  </b><span>{{$maintenancePaymentApproval->maintenance_payment_no}}</span></h5>
          </div>
        </div>
        @endif
        @if(isset($maintenancePaymentApproval->maintenance_payment_date)) 
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Payment Date  :  </b><span>{{$maintenancePaymentApproval->maintenance_payment_date->format('d/m/Y')}}</span></h5>
          </div>
        </div>
        @endif
      </div>
    </div>
    <div class="sub-head">Contractor Details</div>
    <div class="dataSearchBox">    
      <div class="card-body row">

        @if(isset($maintenancePaymentApproval->vendor_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Contractor Name :  </b><span>{{$maintenancePaymentApproval->vendor->vendor_name}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($maintenancePaymentApproval->vendor_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Contractor Code :  </b><span>{{$maintenancePaymentApproval->vendor->vendor_code}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($maintenancePaymentApproval->maintenance_payment_doc_no))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Cheque No :  </b><span>{{$maintenancePaymentApproval->maintenance_payment_doc_no}}</span></h5>
          </div>
        </div> 
        @endif
      </div>
    </div>
    <div class="sub-head">Payment Details</div>
    <div class="dataSearchBox">    
      <div class="card-body row">

        @if(isset($maintenancePaymentApproval->bank_id))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Bank :  </b><span>{{$maintenancePaymentApproval->bankInfo->bank_name}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($maintenancePaymentApproval->maintenance_payment_method))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Payment Method :  </b><span>{{$maintenancePaymentApproval->maintenance_payment_method_name}}</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($maintenancePaymentApproval->maintenance_payment_amount))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Amount :  </b><span>{{numberFormat($maintenancePaymentApproval->maintenance_payment_amount)}} OMR</span></h5>
          </div>
        </div> 
        @endif
        @if(isset($maintenancePaymentApproval->maintenance_payment_comment))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Comment :  </b><span>{{$maintenancePaymentApproval->maintenance_payment_comment}}</span></h5>
          </div>
        </div> 
        @endif
      </div>
    </div>
    <div class="dataSearchBox">    
      <div class="card-body row">

        {{--@if(isset($maintenancePaymentApproval->ax_batch_id))--}}
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>AX Batch ID :  </b><span>{{$maintenancePaymentApproval->ax_batch_id}}</span></h5>
          </div>
        </div> 
        {{--@endif
        @if(isset($maintenancePaymentApproval->ax_payment_no))--}}
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>AX Payment No :  </b><span>{{$maintenancePaymentApproval->ax_payment_no}}</span></h5>
          </div>
        </div> 
        {{--@endif--}}
      </div>
    </div>
  </div>    
</div> 
@endsection
@section('scripts')
@endsection
