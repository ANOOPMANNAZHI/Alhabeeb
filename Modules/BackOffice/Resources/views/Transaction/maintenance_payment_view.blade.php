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
       {{--@can('maintenance_payment_edit') 
       @if(($maintenancePayment->maintenance_payment_status == 1 && $maintenancePayment->maintenance_payment_approval_status == 2) || ($maintenancePayment->maintenance_payment_status == 1 && $maintenancePayment->maintenance_payment_approval_status == 5) || ($maintenancePayment->maintenance_payment_status == 4 && $maintenancePayment->maintenance_payment_approval_status == 3) || ($maintenancePayment->maintenance_payment_status == 1 && $maintenancePayment->maintenance_payment_approval_status == 0))--}}
       <!-- <a title="Edit" href="{{route('maintenancePayment.edit',$maintenancePayment->id)}}" class="btn btn-circle btn-primary align-right" title="Edit">
        Edit
      </a>  -->   
      {{-- @endif                                          
      @endcan --}}
      @can('maintenance_payment_edit') 
      @if($maintenancePayment->maintenance_payment_status != 3 && in_array($maintenancePayment->maintenance_payment_approval_status,[0,1,5]))
      <a title="Edit" href="{{route('maintenancePayment.edit',$maintenancePayment->id)}}" class="btn btn-circle btn-primary align-right" title="Edit">
        Edit
      </a>    
      @endif                                          
      @endcan 
      @if (Auth::user()->hasPermissionTo('maintenance_payment_approval_approve')) 
        @if($maintenancePayment->maintenance_payment_status ==2 && $maintenancePayment->maintenance_payment_approval_status == 4)
        <a href="{{ route('maintenancePaymentAction',[$maintenancePayment->id,4,1]) }}" title="UnApprove" class="btn btn-circle btn-primary align-right">
           UnApprove
        </a>
        @endif
        @if($maintenancePayment->maintenance_payment_status ==4 && $maintenancePayment->maintenance_payment_approval_status == 1)
         <a href="{{ route('maintenancePaymentAction',[$maintenancePayment->id,2,4]) }}" title="Approve" class="btn btn-circle btn-primary align-right">
           Approve
        </a>
        @endif

      @else
      @can('sent_for_approval')
      @if(($maintenancePayment->maintenance_payment_approval_status == 0 && $maintenancePayment->maintenance_payment_status == 1) || ($maintenancePayment->maintenance_payment_status == 1 && $maintenancePayment->maintenance_payment_approval_status == 5) || ($maintenancePayment->maintenance_payment_status == 4 && $maintenancePayment->maintenance_payment_approval_status == 3))
      <a href="{{ route('maintenancePaymentAction',[$maintenancePayment->id,1,2]) }}" title="Sent For Approval" class="btn btn-circle btn-success align-right">
        Send for Approval
      </a>
      @endif
      @endcan
      @endif
      @if (!Auth::user()->hasPermissionTo('maintenance_payment_approval_approve'))
      @can('sent_for_unapproval')
      @if($maintenancePayment->maintenance_payment_status == 2 && $maintenancePayment->maintenance_payment_approval_status == 4)
      <a href="{{ route('maintenancePaymentAction',[$maintenancePayment->id,1,3]) }}" title="Sent For UnApproval" class="btn btn-circle btn-warning align-right">
       Send for UnApproval
     </a>
     @endif
     @endcan 
     @endif
     @can('maintenance_payment_post')
     @if($maintenancePayment->maintenance_payment_status == 2 && $maintenancePayment->maintenance_payment_approval_status == 4) 
     <a href="{{ route('maintenancePaymentPost',[$maintenancePayment->id,3]) }}" title="Post" class="btn btn-circle btn-success align-right post_type">
      Post
    </a>
    @endif
    @endcan

    @if (Auth::user()->hasPermissionTo('maintenance_payment_approval_approve'))
    @can('maintenance_payment_cancel')
    @if(in_array($maintenancePayment->maintenance_payment_status,[0,1,5]))
    <button type="button" class="btn btn-circle btn-danger closed align-right" data-toggle="modal" data-target="#myModal" data-id="{{ $maintenancePayment->id }}" title="Delete" data-backdrop="static" data-keyboard="false">
      Delete
    </button>
    @endif
    @endcan
    @endif

    @if (!Auth::user()->hasPermissionTo('maintenance_payment_approval_approve'))
    @can('maintenance_payment_cancel')
    @if($maintenancePayment->maintenance_payment_status ==4 || ($maintenancePayment->maintenance_payment_status ==1 && $maintenancePayment->maintenance_payment_approval_status == 0 ) || ($maintenancePayment->maintenance_payment_status ==1 && $maintenancePayment->maintenance_payment_approval_status == 2 ))
    <button type="button" class="btn btn-circle btn-danger closed align-right" data-toggle="modal" data-target="#myModal" data-id="{{ $maintenancePayment->id }}" title="Delete" data-backdrop="static" data-keyboard="false">
      Cancel
    </button>
    @endif
    @endcan      
    @endif
  </div>
</div>
<div class="dataSearchBox">
  <div class="card-body row">
    @if(isset($maintenancePayment->maintenance_payment_no))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Payment No :  </b><span>{{$maintenancePayment->maintenance_payment_no}}</span></h5>
      </div>
    </div>
    @endif
    @if(isset($maintenancePayment->maintenance_payment_date)) 
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Payment Date  :  </b><span>{{$maintenancePayment->maintenance_payment_date->format('d/m/Y')}}</span></h5>
      </div>
    </div>
    @endif
  </div>
</div>
<div class="sub-head">Contractor Detail</div>
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
        <h5 class="details"><b>Cheque No :  </b><span>{{$maintenancePayment->maintenance_payment_doc_no}}</span></h5>
      </div>
    </div> 
    @endif
  </div>
</div>
<div class="sub-head">Payment Detail</div>
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
        <h5 class="details"><b>Payment Method :  </b><span>{{$maintenancePayment->maintenance_payment_method_name}}</span></h5>
      </div>
    </div> 
    @endif
    @if(isset($maintenancePayment->maintenance_payment_amount))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Amount :  </b><span>{{numberFormat($maintenancePayment->maintenance_payment_amount)}} OMR</span></h5>
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
@if(isset($maintenancePayment->ax_batch_id) || isset($maintenancePayment->ax_payment_no))
<div class="dataSearchBox">    
  <div class="card-body row">

    @if(isset($maintenancePayment->ax_batch_id))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>AX Batch ID :  </b><span>{{$maintenancePayment->ax_batch_id}}</span></h5>
      </div>
    </div> 
    @endif
    @if(isset($maintenancePayment->ax_payment_no))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>AX Payment No :  </b><span>{{$maintenancePayment->ax_payment_no}}</span></h5>
      </div>
    </div> 
    @endif
  </div>
</div>
@endif
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
            url: "{{route('cancelMaintenancePayment')}}", // This is the url we gave in the route
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

        if (confirm('Do you want to Post this Payment ?')) {
             return true;
        } else {
            return false;
        }
    });
  });
  /*********************************************************************************************/
</script>
@endsection

