@extends('layouts.plms-app')



@section('content')



<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">{{ (isset($maintenancePayment))? 'Edit' : 'Add'}} Maintenance Payment</div>
    </div>
    {{ (isset($maintenancePayment))?   Breadcrumbs::render('maintenancePayment.edit',$maintenancePayment) :  Breadcrumbs::render('maintenancePayment.create') }} 



  </div>
</div>


<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->



<style>
  body {counter-reset:section 0 sec 0;}
  .counters:before
  {
    counter-increment:section;
    content:counter(section);
  }
  .countn:before
  {
    counter-increment:sec;
    content:counter(sec);
  }
  
  input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
  }
</style>

<div class="row">  

  <!-- activities -->
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
      <div class="panel-body">
        <div class="tab-content">                                     
          {{-- @if(isset($maintenancePayment)) --}}
          <div class="tab-pane active" id="maintenance">
            <form method="post" autocomplete="off" id="payment-form" action="{{isset($maintenancePayment)? route( 'maintenancePayment.update',$maintenancePayment->id) : route( 'maintenancePayment.store')}}" data-toggle="validator">
              @csrf  @if(isset($maintenancePayment)){{method_field('PUT')}}@endif
              <div class="row">
                <div class="col">
                  <!-- starts -->
                  <div class="dataSearchBox">
                    <div class="card-body row">

                     <div class="col-sm-6">
                      <div class="form-group">
                        <label for="maintenance_payment_no">Payment No<small class="textRed">*</small></label>
                        <div class="p-relative">
                         <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
                         <input required type="text" class="form-control" id="maintenance_payment_no" readonly name="maintenance_payment_no" value="{{ old('maintenance_payment_no', isset($maintenancePayment->id)? $maintenancePayment->maintenance_payment_no : $nextCode )}}"  placeholder="Enter Payment No">
                       </div>
                     </div>
                   </div>

                   <div class="col-sm-6">
                    <div class="form-group">
                      <label for="maintenance_payment_date">Payment Date<small class="textRed">*</small></label>
                      <div class="p-relative">
                       <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                       <input required type="date" class="form-control" id="maintenance_payment_date"  name="maintenance_payment_date" value="{{ old('maintenance_payment_date', isset($maintenancePayment->id)? $maintenancePayment->maintenance_payment_date->format('Y-m-d') : date('Y-m-d',strtotime(today())
                       ) )}}"  placeholder="Enter Payment Date">
                     </div>
                   </div>
                 </div>

               </div>
             </div>
             <!--ends -->
             <!-- starts -->
             <div class="sub-head">Contractor Detail</div>
             <div class="dataSearchBox">
              <div class="card-body row">

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="vendor_name">Contractor Name<small class="textRed">*</small></label>
                    <div class="p-relative">
                     <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
                     <input required type="text" class="form-control" id="vendor_name"  name="vendor_name" value="{{ old('vendor_name', isset($maintenancePayment)? $maintenancePayment->vendor->vendor_name : '' )}}"  placeholder="Enter Contractor Name">
                     <input required type="hidden"  id="vendor_id"  name="vendor_id" value="{{ old('vendor_name', isset($maintenancePayment)? $maintenancePayment->vendor->id : '' )}}">
                   </div>
                 </div>
               </div>

               <div class="col-sm-6">
                <div class="form-group">
                  <label for="vendor_code">Contractor Code<small class="textRed">*</small></label>
                  <div class="p-relative">
                   <i class="fa fa-rebel icn-add" aria-hidden="true"></i>
                   <input  type="text" class="form-control" id="vendor_code"  name="vendor_code" value="{{ old('vendor_code', isset($maintenancePayment)? $maintenancePayment->vendor->vendor_code : '' )}}"  placeholder="Enter Contractor Code" readonly>
                 </div>
               </div>
             </div> 

         </div>
       </div>
       <!--ends -->
       <!-- starts -->
       <div class="sub-head">Payment Detail</div>
       <div class="dataSearchBox">
        <div class="card-body row">
        <div class="col-sm-6">
          <div class="form-group">
            <label for="maintenance_payment_method">Payment Method<small class="textRed">*</small></label>
            <div class="p-relative">
              <div class="form-control" ><input type="radio"  required {{ (old('maintenance_payment_method', isset($maintenancePayment)?  $maintenancePayment->maintenance_payment_method : -1) == 1 )? 'checked' : 'checked' }}  name="maintenance_payment_method" class="payment_method" value="1"> Cash
                <input type="radio"  required  {{ (old('maintenance_payment_method', isset($maintenancePayment)?  $maintenancePayment->maintenance_payment_method : -1) == 2 )? 'checked' : '' }}  name="maintenance_payment_method" class="payment_method" value="2"> Cheque 
              </div>
            </div>
          </div>
        </div>
		<div class="col-sm-6">
            <div class="form-group">
              <label for="bank_id">Bank</label>
              <div class="p-relative">
               <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
               <select class="form-control" id="bank_id"  name="bank_id" >
                <option value="">Select Bank</option>
                @foreach($banks as $bank)
                <option  {{(old('bank_id', isset($maintenancePayment)?  $maintenancePayment->bank_id : 0) == $bank->id) ? 'selected' : '' }} value="{{$bank->id}}">{{$bank->bank_name}}</option>
                @endforeach                  
              </select> 
            </div>
          </div>
        </div>
		<div class="col-sm-6">
		  <div class="form-group">
			<label for="maintenance_payment_doc_no">Cheque No</label>
			<div class="p-relative">
			 <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
			 <input type="text" class="form-control" id="cheque_no"  name="maintenance_payment_doc_no" value="{{ old('maintenance_payment_doc_no', isset($maintenancePayment)? $maintenancePayment->maintenance_payment_doc_no : '' )}}"  placeholder="Enter Cheque No" {{ !isset($maintenancePayment)? 'disabled="disabled"':''}} >
		   </div>
		 </div>
	   </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label for="maintenance_payment_amount">Amount<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="fa fa-money icn-add" aria-hidden="true"></i>
             <input  type="text" class="form-control text-right" id="maintenance_payment_amount"  name="maintenance_payment_amount" value="{{ old('maintenance_payment_amount', isset($maintenancePayment)? numberFormat($maintenancePayment->maintenance_payment_amount) : '' )}}"  placeholder="Enter Amount" required data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="FormatCurrency(this)"> 
           </div>
         </div>
       </div>

       <div class="col-sm-6">
        <div class="form-group">
          <label for="maintenance_payment_comment">Comment</label>
          <div class="p-relative">
           <textarea  class="form-control" id="maintenance_payment_comment"  name="maintenance_payment_comment" >{{ old('maintenance_payment_comment', isset($maintenancePayment)? $maintenancePayment->maintenance_payment_comment : '' )}}</textarea>
         </div>
       </div>
     </div>

   </div>
 </div>
 <!--ends -->
 <!-- starts -->
 <div class="dataSearchBox">
  <div class="card-body row">

    <div class="col-sm-6">
      <div class="form-group">
        <label for="ax_batch_id">AX Batch ID</label>
        <div class="p-relative">
         <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
         <input  type="text" class="form-control" id="ax_batch_id" name="ax_batch_id" value="{{ old('ax_batch_id', isset($maintenancePayment)? $maintenancePayment->ax_batch_id : '' )}}"  placeholder="Enter AX Batch ID" readonly>
       </div>
     </div>
   </div>

   <div class="col-sm-6">
    <div class="form-group">
      <label for="ax_payment_no">AX Payment No </label>
      <div class="p-relative">
       <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
       <input type="text" class="form-control" id="ax_payment_no"  name="ax_payment_no" value="{{ old('ax_payment_no', isset($maintenancePayment)? $maintenancePayment->ax_payment_no : '' )}}"  placeholder="Enter AX Payment No" readonly>
     </div>
   </div>
 </div>

</div>
</div>
<!--ends -->

</div>
</div>
<button type="submit" class="btn btn-primary submitBtn">{{ (isset($maintenancePayment))? 'Edit' : 'Save'}}</button> 
</form>
</div>
{{-- @endif --}}
</div>
</div>
</div>
</div>
</div>           

</div>
<div class="modal" id="myModal">

</div>
<form id="delete-form" action="" method="POST">
  {{ method_field('DELETE') }}  {{csrf_field()}}
  <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>
/************************************************************/

/************************************************************/
 $("#payment-form").validate({
    submitHandler: function(form) {
        $('.submitBtn').prop('disabled', true);
        form.submit();
      }
  });
//AutoComplete For Vendor Name
/************************************************************/ 
$('#vendor_name').autocomplete({
  source : '{!!URL::route('contractorTypeAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#vendor_name").val('');
      $('#create_build_span').hide();
      $('#vendor_name-error').show();
    }else {

      $('#vendor_id').val(ui.item.ids);       
      $('#vendor_code').val(ui.item.code);       
      $('#create_build_span').show();       
    }

  }
});
/************************************************************/

/************************************************************/
$(document).ready(function(){

 @if($isYearCorrect == false)
      alert("Current Year Is Not Match With the Sequence Year");
  @endif

  var dtToday = new Date();

  var month = dtToday.getMonth() + 1;
  var day = dtToday.getDate();
  var year = dtToday.getFullYear();
  if(month < 10)
    month = '0' + month.toString();
  if(day < 10)
    day = '0' + day.toString();

  var maxDate = year + '-' + month + '-' + day;
  @if(!isset($maintenancePayment))
  $('#maintenance_payment_date').attr('min', maxDate);
  @else
    $('#maintenance_payment_date').attr('min', "{{$maintenancePayment->maintenance_payment_date->format('Y-m-d')}}");
    
  @endif
});

$(document).on('click','.payment_method',function(){  
    
    var payment_method = $(this).val(); 
    if(payment_method == 2){ // Cheque
            //$('#bank_id').prop("disabled", false);
            $("#cheque_no").prop("disabled", false);
            $('#bank_id').attr("required", true);
            $('#cheque_no').attr("required", true);
    }
    else if(payment_method == 1){ // Cash
            $('#bank_id').prop('selectedIndex',0);
            $('#cheque_no').val('');
            //$('#bank_id').prop("disabled", true);
            $("#cheque_no").prop("disabled", true);
            $('#bank_id').removeAttr('required');
            $('#cheque_no').removeAttr('required');    
    }
         
});

/************************************************************/
</script>
@endsection

