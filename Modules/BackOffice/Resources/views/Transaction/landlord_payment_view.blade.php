@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Landlord Payment View</div>
    </div>

    {{ Breadcrumbs::render('landlordPayment.show',$landlordPayment) }} 

  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
     <div class="row">
      <div class="col-sm-12">
       <!-- Approval premission user - To approve    -->                                      
        @if (Auth::user()->hasPermissionTo('landlord_payment_approval_approve')) 
        @if($landlordPayment->landlord_payment_approval_status ==1 && $landlordPayment->landlord_payment_status ==4)
            <a href="{{ route('landlordPaymentAction',[$landlordPayment->id,2,4]) }}" title="Approve" class="btn btn-circle btn-info  align-right approve">
            Approve
            </a>
        @endif
        @endif
        <!-- Approval premission user - To Unapprove    -->
        @if (Auth::user()->hasPermissionTo('landlord_payment_approval_unapprove')) 
         @if($landlordPayment->landlord_payment_status ==2 && $landlordPayment->landlord_payment_approval_status == 4)

            <a href="{{ route('landlordPaymentAction',[$landlordPayment->id,4,1]) }}" title="UnApprove" class="btn btn-circle btn-info  align-right  unapprove">
              UnApprove
            </a>
        @endif

        @endif 
       @can('landlord_payment_edit') 
        @if($landlordPayment->landlord_payment_status != 3 && in_array($landlordPayment->landlord_payment_status,[1,4]) && in_array($landlordPayment->landlord_payment_approval_status,[0,1,5]))
        <a title="Edit" href="{{route('landlordPayment.edit',$landlordPayment->id)}}" class="btn btn-circle btn-primary align-right" title="Edit">
         Edit
       </a>    
       @endif                                          
       @endcan 
       @can('sent_for_approval')
       @if(($landlordPayment->landlord_payment_approval_status == 0 && $landlordPayment->landlord_payment_status == 1) || ($landlordPayment->landlord_payment_status == 1 && $landlordPayment->landlord_payment_approval_status == 5) || ($landlordPayment->landlord_payment_status == 4 && $landlordPayment->landlord_payment_approval_status == 3))
       <a href="{{ route('landlordPaymentAction',[$landlordPayment->id,1,2]) }}" title="Sent For Approval" class="btn btn-circle btn-primary align-right">
         Send for Approval
       </a>
       @endif
       @endcan
       @if (!Auth::user()->hasPermissionTo('landlord_payment_approval_approve'))
       @can('sent_for_unapproval')
       @if($landlordPayment->landlord_payment_status == 2 && $landlordPayment->landlord_payment_approval_status == 4)
       <a href="{{ route('landlordPaymentAction',[$landlordPayment->id,1,3]) }}" title="Sent For UnApproval" class="btn btn-circle btn-primary align-right">
         Send for UnApproval
       </a>
       @endif
       @endcan 
       @endif
       @can('landlord_payment_post')
       @if($landlordPayment->landlord_payment_status == 2 && $landlordPayment->landlord_payment_approval_status == 4)
       <a href="{{ route('landlordPaymentPost',[$landlordPayment->id,3]) }}" title="Post" class="btn btn-circle btn-success align-right post_type">
         Post 
       </a>
       @endif
       @endcan

       @if (Auth::user()->hasPermissionTo('landlord_payment_approval_approve'))
       @can('landlord_payment_cancel')
       @if($landlordPayment->landlord_payment_status != 3 && in_array($landlordPayment->landlord_payment_status,[1,4]) && in_array($landlordPayment->landlord_payment_approval_status,[0,1,5]))
       <button type="button" class="btn btn-circle btn-danger closed align-right" data-toggle="modal" data-target="#myModal" data-id="{{ $landlordPayment->id }}" title="Delete">
        Delete
      </button>
      @endif
      @endcan
      @endif

      @if (!Auth::user()->hasPermissionTo('landlord_payment_approval_approve'))
      @can('landlord_payment_cancel')
      @if(in_array($landlordPayment->landlord_payment_status,[1,4]) && in_array($landlordPayment->landlord_payment_approval_status,[0,1,5]))
      <button type="button" class="btn btn-circle btn-custom_delete closed align-right" data-toggle="modal" data-target="#myModal" data-id="{{ $landlordPayment->id }}" title="Delete">
        Delete
      </button>
      @endif
      @endcan 
      @endif  
    </div>
  </div>
  <div class="dataSearchBox">
    <div class="card-body row">
      @if(isset($landlordPayment->landlord_payment_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Payment No :  </b><span>{{$landlordPayment->landlord_payment_no}}</span></h5>
        </div>
      </div>
      @endif
      @if(isset($landlordPayment->landlord_payment_date)) 
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Payment Date  :  </b><span>{{$landlordPayment->landlord_payment_date->format('d/m/Y')}}</span></h5>
        </div>
      </div>
      @endif
    </div>
  </div>
  <div class="sub-head">Landlord Detail</div>
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($landlordPayment->landlord_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Landlord Name :  </b><span>{{$landlordPayment->landlordContract->vendorInfo->vendor_name}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->landlord_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Landlord Code :  </b><span>{{$landlordPayment->landlordContract->vendorInfo->vendor_code}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->landlord_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Agreement No :  </b><span>{{$landlordPayment->landlordContract->landlord_contract_no}}</span></h5>
        </div>
      </div> 
      @endif
       @if(isset($landlordPayment->landlord_contract_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Building Name :  </b><span>{{$landlordPayment->landlordContract->buildingInfo->building_name}}</span></h5>
        </div>
      </div> 
      @endif
    </div>
  </div>
  <div class="sub-head">Payment Detail</div>
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($landlordPayment->landlord_invoice_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Invoice No :  </b><span>{{$landlordPayment->landlordInvoice->landlord_invoice_voucher_no}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->landlord_payment_currency_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Currency ID  :  </b><span>{{$landlordPayment->currency->currency_name}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->landlord_payment_method))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Payment Method :  </b><span>{{$landlordPayment->landlord_payment_method_name}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->landlord_payment_cheque_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Cheque Number :  </b><span>{{$landlordPayment->landlord_payment_cheque_no}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->bank_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Bank :  </b><span>{{$landlordPayment->bankInfo->bank_name}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->landlord_payment_invoice_amt))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Invoice Amount :  </b><span>{{numberFormat($landlordPayment->landlord_payment_invoice_amt)}} OMR</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->landlord_payment_balance_amt))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Balance Amount :  </b><span>{{numberFormat($landlordPayment->landlord_payment_balance_amt)}} OMR</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->landlord_payment_amount))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Amount :  </b><span>{{numberFormat($landlordPayment->landlord_payment_amount)}} OMR</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->landlord_payment_comment))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Comment :  </b><span>{{$landlordPayment->landlord_payment_comment}}</span></h5>
        </div>
      </div> 
      @endif
    </div>
  </div>
  @if(isset($landlordPayment->ax_batch_id) || isset($landlordPayment->ax_payment_no))
  <div class="dataSearchBox">    
    <div class="card-body row">

      @if(isset($landlordPayment->ax_batch_id))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>AX Batch ID :  </b><span>{{$landlordPayment->ax_batch_id}}</span></h5>
        </div>
      </div> 
      @endif
      @if(isset($landlordPayment->ax_payment_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>AX Payment No :  </b><span>{{$landlordPayment->ax_payment_no}}</span></h5>
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

        if (confirm('Do you want to Post this Payment ?')) {
             return true;
        } else {
            return false;
        }
    });
  });
</script>
@endsection
