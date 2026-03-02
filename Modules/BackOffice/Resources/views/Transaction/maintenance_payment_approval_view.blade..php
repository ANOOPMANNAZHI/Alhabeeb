@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Maintenance Payment View</div>
    </div>

    {{ Breadcrumbs::render('maintenancePayment.show',$maintenancePayment) }} 

  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
         <div class="row">
      <div class="col-sm-12">
        @can('plms_refer_back')
        <button type="button" class="btn btn-circle btn-warning align-right referback" data-id="{{$maintenancePayment->id}}"  data-key="RFRBK" data-flow-id="801"  data-status="0" data-toggle="modal" data-target="#myModal">Post</button>
        @endcan

        @can('plms_approve')
        <button type="button" class="btn btn-circle btn-primary align-right approve" data-id="{{$maintenancePayment->id}}"  data-key="APRV" data-flow-id="801" data-status="0" data-toggle="modal" data-target="#myModal">Sent for Approval</button>
      </div>
            @endcan
    </div>
      <div class="dataSearchBox">
        <div class="card-body row">
          @if(isset($maintenancePayment->maintenance_payment_no))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Agreement No :  </b><span>{{$maintenancePayment->maintenance_payment_no}}</span></h5>
            </div>
          </div>
          @endif
          @if(isset($maintenancePayment->maintenance_payment_date)) 
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Agreement Date  :  </b><span>{{$maintenancePayment->maintenance_payment_date->format('d/m/Y')}}</span></h5>
            </div>
          </div>
          @endif
        </div>
      </div>
      <div class="sub-head">Contractor Details</div>
      <div class="dataSearchBox">    
        <div class="card-body row">

          @if(isset($maintenancePayment->vendor_id))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Contractor Name :  </b><span>{{$maintenancePayment->vendor->vendor_name}}</span></h5>
           </div>
         </div> 
         @endif
         @if(isset($maintenancePayment->vendor_id))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Contractor Code :  </b><span>{{$maintenancePayment->vendor->vendor_code}}</span></h5>
           </div>
         </div> 
         @endif
         @if(isset($maintenancePayment->maintenance_payment_doc_no))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Doc.No :  </b><span>{{$maintenancePayment->maintenance_payment_doc_no}}</span></h5>
           </div>
         </div> 
         @endif
       </div>
     </div>
           <div class="sub-head">Payment Details</div>
      <div class="dataSearchBox">    
        <div class="card-body row">

          @if(isset($maintenancePayment->bank_id))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Bank :  </b><span>{{$maintenancePayment->bankInfo->bank_name}}</span></h5>
           </div>
         </div> 
         @endif
         @if(isset($maintenancePayment->maintenance_payment_method))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Payment Method :  </b><span>{{$maintenancePayment->maintenance_payment_method}}</span></h5>
           </div>
         </div> 
         @endif
         @if(isset($maintenancePayment->maintenance_payment_amount))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Amount :  </b><span>{{$maintenancePayment->maintenance_payment_amount}}</span></h5>
           </div>
         </div> 
         @endif
          @if(isset($maintenancePayment->maintenance_payment_comment))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Comment :  </b><span>{{$maintenancePayment->maintenance_payment_comment}}</span></h5>
           </div>
         </div> 
         @endif
       </div>
     </div>
      <div class="dataSearchBox">    
        <div class="card-body row">

          @if(isset($maintenancePayment->ax_batch_id))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Contractor Name :  </b><span>{{$maintenancePayment->ax_batch_id}}</span></h5>
           </div>
         </div> 
         @endif
         @if(isset($maintenancePayment->ax_payment_no))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Contractor Code :  </b><span>{{$maintenancePayment->ax_payment_no}}</span></h5>
           </div>
         </div> 
         @endif
       </div>
     </div>
   </div>    
 </div> 
 @endsection
 @section('scripts')
 @endsection
