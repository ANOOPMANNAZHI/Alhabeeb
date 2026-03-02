@extends('layouts.plms-app')



@section('content')



<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">{{ (isset($landlordPayment))? 'Edit' : 'Add'}} Landlord Payment</div>
    </div>
    {{ (isset($landlordPayment))?   Breadcrumbs::render('landlordPayment.edit',$landlordPayment) :  Breadcrumbs::render('landlordPayment.create') }} 



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
          {{-- @if(isset($landlordPayment)) --}}
          <div class="tab-pane active" id="maintenance">
            <form method="post" autocomplete="off" id="payment-form" action="{{isset($landlordPayment)? route( 'landlordPayment.update',$landlordPayment->id) : route( 'landlordPayment.store')}}" data-toggle="validator">
              @csrf  @if(isset($landlordPayment)){{method_field('PUT')}}@endif
              <div class="row">
                <div class="col">
                  <!-- starts -->
                  <div class="dataSearchBox">
                    <div class="card-body row">

                      <div class="col-sm-6">
                        <div class="form-group">
                          <label for="landlord_payment_no">Payment No<small class="textRed">*</small></label>
                          <div class="p-relative">
                           <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
                           <input required type="text" class="form-control" id="landlord_payment_no" readonly name="landlord_payment_no" value="{{ old('landlord_payment_no', isset($landlordPayment)? $landlordPayment->landlord_payment_no : $nextCode )}}"  placeholder="Enter Payment No">
                         </div>
                       </div>
                     </div>

                     <div class="col-sm-6">
                      <div class="form-group">
                        <label for="landlord_payment_date">Payment Date<small class="textRed">*</small></label>
                        <div class="p-relative">
                         <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                         <input required type="date" class="form-control" id="landlord_payment_date"  name="landlord_payment_date" value="{{ old('landlord_payment_date', isset($landlordPayment)? $landlordPayment->landlord_payment_date->format('Y-m-d') : date('Y-m-d',strtotime(today())) )}}"  placeholder="Enter Payment Date">
                       </div>
                     </div>
                   </div>

                 </div>
               </div>
               <!--ends -->
               <!-- starts -->
               <div class="sub-head">Landlord Detail</div>
               <div class="dataSearchBox">
                <div class="card-body row">

                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="vendor_name">Landlord Name<small class="textRed">*</small></label>
                      <div class="p-relative">
                       <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
                       <input required type="text" class="form-control" id="vendor_name"  name="vendor_name" value="{{ old('vendor_name', isset($landlordPayment)? $landlordPayment->landlordContract->vendorInfo->vendor_name : '' )}}"  placeholder="Enter Landlord Name">
                       <input required type="hidden"  id="vendor_id"  name="vendor_id" value="{{ old('vendor_name', isset($landlordPayment)? $landlordPayment->landlordContract->vendorInfo->id : '' )}}">
                     </div>
                   </div>
                 </div>

                 <div class="col-sm-6">
                  <div class="form-group">
                    <label for="vendor_code">Landlord Code<small class="textRed">*</small></label>
                    <div class="p-relative">
                     <i class="fa fa-rebel icn-add" aria-hidden="true"></i>
                     <input  type="text" class="form-control" id="vendor_code"  name="vendor_code" value="{{ old('vendor_code', isset($landlordPayment)? $landlordPayment->landlordContract->vendorInfo->vendor_code : '' )}}"  placeholder="Enter Landlord Code" readonly>
                   </div>
                 </div>
               </div>

               <div class="col-sm-6">
                <div class="form-group">
                  <label for="landlord_contract_no">Agreement No<small class="textRed">*</small></label>
                  <div class="p-relative">
                   <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                   <input type="text" class="form-control" id="landlord_contract_no"  name="landlord_contract_no" value="{{ old('landlord_contract_no', isset($landlordPayment)? $landlordPayment->landlordContract->landlord_contract_no : '' )}}"  placeholder="Enter Agreement No">
                   <input required type="hidden"  id="landlord_contract_id"  name="landlord_contract_id" value="{{ old('landlord_contract_no', isset($landlordPayment)? $landlordPayment->landlordContract->id : '' )}}">
                 </div>
               </div>
             </div>

           </div>
         </div>
         <!--ends -->
         <!-- starts -->
         <div class="sub-head">Invoice Detail</div>
         <div class="dataSearchBox">
          <div class="card-body row">

            <div class="col-sm-6">
              <div class="form-group">
                <label for="landlord_invoice_voucher_no">Invoice No<small class="textRed">*</small></label>
                <div class="p-relative">
                 <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
                 <input  required type="text" class="form-control read" id="landlord_invoice_voucher_no"  name="landlord_invoice_voucher_no"   placeholder="Enter Invoice No" value="{{ old('landlord_invoice_voucher_no', isset($landlordPayment)? $landlordPayment->landlordInvoice->landlord_invoice_voucher_no : '' )}}">
                 <input type="hidden" name="landlord_invoice_id" id="landlord_invoice_id" value="{{ old('landlord_invoice_id', isset($landlordPayment)? $landlordPayment->landlord_invoice_id : '' )}}">
               </div>
             </div>
           </div>

           <div class="col-sm-6">
            <div class="form-group">
              <label for="landlord_payment_currency_id">Currency<small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="fa fa-money icn-add" aria-hidden="true"></i>
               <select class="form-control" id="landlord_payment_currency_id"  name="landlord_payment_currency_id" required>
                @foreach($currencies as $currency)
                <option  {{(old('landlord_payment_currency_id', isset($landlordPayment)?  $landlordPayment->landlord_payment_currency_id : 1) == $currency->id) ? 'selected' : '' }} value="{{$currency->id}}">{{$currency->currency_code}}</option>
                @endforeach                  
              </select> 
            </div>
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
            <label for="landlord_payment_method">Payment Method<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>

             <select class="form-control" id="landlord_payment_method"  name="landlord_payment_method" required>
              <option  {{(old('landlord_payment_method', isset($landlordPayment)?  $landlordPayment->landlord_payment_method : 1) == $currency->id) ? 'selected' : '' }} value="1">Cheque</option>
              <option  {{(old('landlord_payment_method', isset($landlordPayment)?  $landlordPayment->landlord_payment_method : 1) == $currency->id) ? 'selected' : '' }} value="2">Bank Transfer</option>					             
            </select> 

          </div>
        </div>
      </div>
      <div class="col-sm-6">
      <div class="form-group">
        <label for="bank_id">Bank<small class="textRed">*</small></label>
        <div class="p-relative">
         <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
         <select class="form-control" id="bank_id"  name="bank_id" required>
          <option value="">Select Bank</option>
          @foreach($banks as $bank)
          <option  {{(old('bank_id', isset($landlordPayment)?  $landlordPayment->bank_id : 0) == $bank->id) ? 'selected' : '' }} value="{{$bank->id}}">{{$bank->bank_name}}</option>
          @endforeach                  
        </select>
      </div>
    </div>
  </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="landlord_payment_cheque_no">Cheque No</label>
          <div class="p-relative">
           <i class="fa fa-money icn-add" aria-hidden="true"></i>
           <input type="text" class="form-control" id="landlord_payment_cheque_no"  name="landlord_payment_cheque_no"   placeholder="Enter Cheque No" value="{{ old('landlord_payment_cheque_no', isset($landlordPayment)? $landlordPayment->landlord_payment_cheque_no : '' )}}">
         </div>
       </div>
     </div>
     
  <div class="col-sm-6">
    <div class="form-group">
      <label for="landlord_payment_invoice_amt">Invoice Amount<small class="textRed">*</small></label>
      <div class="p-relative">
       <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
       <input required  type="text" class="form-control read text-right
       " id="landlord_payment_invoice_amt"  name="landlord_payment_invoice_amt" value="{{ old('landlord_payment_invoice_amt', isset($landlordPayment)? numberFormat($landlordPayment->landlord_payment_invoice_amt) : '' )}}" onkeyup="FormatCurrency(this)"  placeholder="Enter Invoice Amount" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
     </div>
   </div>
 </div>

 <div class="col-sm-6">
  <div class="form-group">
    <label for="landlord_payment_balance_amt">Balance Amount<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-money icn-add" aria-hidden="true"></i>
     <input  required type="text" class="form-control text-right
     " id="landlord_payment_balance_amt"  name="landlord_payment_balance_amt" value="{{ old('landlord_payment_balance_amt', isset($landlordPayment)? numberFormat($landlordPayment->landlord_payment_balance_amt) : '' )}}"  placeholder="Enter Balance Amount" readonly>

     <input  required type="hidden" class="form-control text-right
     " id="landlord_payment_balance_amt_data"  name="landlord_payment_balance_amt_data" value="{{ old('landlord_payment_balance_amt', isset($landlordPayment)? numberFormat($landlordPayment->landlord_payment_balance_amt) : '' )}}"  placeholder="Enter Balance Amount" readonly>
     <div class="error1" style="display:none">Amount cannot have value greater than Balance Amount</div>
   </div>
 </div>
</div>

<div class="col-sm-6">
  <div class="form-group">
    <label for="landlord_payment_amount">Amount<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
     <input  required type="text" class="form-control text-right" id="landlord_payment_amount"  name="landlord_payment_amount" value="{{ old('landlord_payment_amount', isset($landlordPayment)? numberFormat($landlordPayment->landlord_payment_amount) : '' )}}"  placeholder="Enter Amount"  maxlength="10" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="FormatCurrency(this)">
     <div class="error" style="display:none">Amount cannot have value greater than Invoice Amount</div>
     <div class="error2" style="display:none">Invoice Payment Completed</div>
   </div>
 </div>
</div>

<div class="col-sm-6">
  <div class="form-group">
    <label for="landlord_payment_comment">Comment</label>
    <div class="p-relative">
     <textarea  class="form-control" id="landlord_payment_comment"  name="landlord_payment_comment" maxlength="200">{{ old('landlord_payment_comment', isset($landlordPayment)? $landlordPayment->landlord_payment_comment : '' )}}</textarea>
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
         <input  type="text" class="form-control" id="ax_batch_id" name="ax_batch_id" value="{{ old('ax_batch_id', isset($landlordPayment)? $landlordPayment->ax_batch_id : '' )}}"  placeholder="Enter AX Batch ID" readonly>
       </div>
     </div>
   </div> 

   <div class="col-sm-6">
    <div class="form-group">
      <label for="ax_payment_no">AX Payment No</label>
      <div class="p-relative">
       <i class="fa fa-rebel icn-add" aria-hidden="true"></i>
       <input type="text" class="form-control" id="ax_payment_no"  name="ax_payment_no" value="{{ old('ax_payment_no', isset($landlordPayment)? $landlordPayment->ax_payment_no : '' )}}"  placeholder="Enter AX Payment No" readonly>
       <input type="hidden" name="sumPaymentAmt" id="sumPaymentAmt" value="{{ old('landlord_payment_invoice_amt', isset($landlordPayment)? $sumPaymentAmt->total : '' )}}">
     </div>
   </div>
 </div>
 
</div>
</div>
<!--ends -->
</div>
</div>
<button type="submit" class="btn btn-primary submitBtn" id="submitBtn">{{ (isset($landlordPayment))? 'Save' : 'Save'}}</button>
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
  source : '{!!URL::route('landlordTypeAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#vendor_name").val('');
      $("#landlord_contract_no").val('');      
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
//Autocomplete for contract No
$('#landlord_contract_no').autocomplete({
     // source : '{!!URL::route('buildingAutocompleteCode')!!}',
     source: function(request, response) {
      $.getJSON("{!!URL::route('agreementAutocompleteCode')!!}", { vendor: $('#vendor_id').val(),contract:$('#landlord_contract_no').val() }, 
        response);
    },
    minlenght:2,
    autoFocus:true,
    change:function(e,ui){
      if (ui.item == null || ui.item == undefined) {
        $("#landlord_contract_no").val('');  
        $('#landlord_invoice_voucher_no').val(''); 
        $('#landlord_payment_invoice_amt').val(''); 
        $('#landlord_invoice_id').val(''); 
        $('#landlord_payment_balance_amt').val('');
        $(".read").attr('readonly',false);
        var status = '';
        $('#landlord_contract_no-error').show();
      }else {
        $('#landlord_contract_id').val(ui.item.ids); 

        $.ajax({
          method: "POST",
          url: "{{route('invoiceDetailsByContractNo')}}",
          data: { 'contract_id': ui.item.ids, 
          "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){
           var results = $.parseJSON(data);
        //alert(results);
        if(results.length > 0){

          $('#landlord_invoice_voucher_no').val(results[1]); 
          if(results[2]!=null){
            $('#landlord_payment_invoice_amt').val(parseFloat(results[2]));
          }
          if(results[3]!=null){
            $('#landlord_invoice_id').val(results[3]);
          }
          if(results[4].total!=null){
            var landlord_payment_invoice_amt = $('#landlord_payment_invoice_amt').val();
            var landlord_payment_amount = $('#landlord_payment_amount').val();
            var landlord_invoice_id = $('#landlord_invoice_id').val();
            $('#sumPaymentAmt').val(results[4].total); 
            $(".read").attr('readonly',true);

            var sumPaymentAmt = results[4].total;

            var landlord_payment_balance_amt = parseFloat(results[2]) - sumPaymentAmt;
             //alert(landlord_payment_balance_amt);
             $('#landlord_payment_balance_amt').val(landlord_payment_balance_amt.toFixed(3));
             $('#landlord_payment_balance_amt_data').val(landlord_payment_balance_amt.toFixed(3));


             if(landlord_payment_balance_amt == 0){

              $(".error2").css("display","block").css("color","red");
              $("#submitBtn").prop('disabled',true);
            }
            else {
              $(".error2").css("display","none");
              $("#submitBtn").prop('disabled',false);        
            }
          }
          if(results[4].total==null){
           var landlord_payment_invoice_amt = $('#landlord_payment_invoice_amt').val();
           var landlord_payment_amount = $('#landlord_payment_amount').val();
           var landlord_invoice_id = $('#landlord_invoice_id').val();
           var tot = 0.000;
           $('#sumPaymentAmt').val(tot); 
           $(".read").attr('readonly',true);
           
           var sumPaymentAmt = tot;

           var landlord_payment_balance_amt = parseFloat(results[2]) - sumPaymentAmt;
             //alert(landlord_payment_balance_amt);
             $('#landlord_payment_balance_amt').val(landlord_payment_balance_amt.toFixed(3));
             $('#landlord_payment_balance_amt_data').val(landlord_payment_balance_amt.toFixed(3));


             if(landlord_payment_balance_amt == 0){

              $(".error2").css("display","block").css("color","red");
              $("#submitBtn").prop('disabled',true);
            }
            else {
              $(".error2").css("display","none");
              $("#submitBtn").prop('disabled',false);        
            }
          }
          $(".read").attr('readonly',true);
          var status = results[0];
        }
        else{

          $('#landlord_invoice_voucher_no').val('');
          $('#landlord_payment_invoice_amt').val('');
          $('#landlord_invoice_id').val('');
          $('#landlord_payment_balance_amt').val('');
          $(".read").attr('readonly',false);
          var status = '';
        }
      },

    }); 


      }

    }


  });
/************************************************************/

/************************************************************/
$(document).ready(function(){

	@if($isYearCorrect == false)
      alert("Current Year Is Not Match With the Sequence Year");
	@endif
	/*
	var dtToday = new Date();

	var month = dtToday.getMonth() + 1;
	var day = dtToday.getDate();
	var year = dtToday.getFullYear();
	if(month < 10)
	month = '0' + month.toString();
	if(day < 10)
	day = '0' + day.toString();

	var maxDate = year + '-' + month + '-' + day;
	@if(!isset($landlordPayment))
	$('#landlord_payment_date').attr('min', maxDate);
	@endif
	*/
});
/************************************************************/
$("#landlord_payment_amount").focusout(function(){

  if($("#landlord_payment_balance_amt_data").val().replace(/,/g, '') > 0 ){
    if(parseFloat($("#landlord_payment_balance_amt_data").val().replace(/,/g, '')) < parseFloat($("#landlord_payment_amount").val().replace(/,/g, '')))
    {
      $(".error1").css("display","block").css("color","red");
      $("#submitBtn").prop('disabled',true);
    }
    else {
      $(".error1").css("display","none");
      $("#submitBtn").prop('disabled',false);        
    }

  }else{
    if(parseFloat($("#landlord_payment_invoice_amt").val()) < parseFloat($("#landlord_payment_amount").val()))
    {
      $(".error").css("display","block").css("color","red");
      $("#submitBtn").prop('disabled',true);
    }
    else {
      $(".error").css("display","none");
      $("#submitBtn").prop('disabled',false);        
    }

  }


});
/************************************************************/
$(document).ready(function(){
 var landlord_payment_invoice_amt = $('#landlord_payment_invoice_amt').val().replace(/,/g, '');
 //alert(landlord_payment_invoice_amt);
 var sumPaymentAmt = $('#sumPaymentAmt').val().replace(/,/g, '');
 var landlord_payment_balance_amt = parseFloat(landlord_payment_invoice_amt) - parseFloat(sumPaymentAmt);
 if (isNaN(landlord_payment_balance_amt)){
  $('#landlord_payment_balance_amt').val(0.000);
}else{
 $('#landlord_payment_balance_amt').val(formatNumber(landlord_payment_balance_amt.toFixed(3)));
}
});


/************************************************************/
$("#landlord_payment_amount").on("focusout",function(){
 @if(isset($landlordPayment))
    var curr_amt ="{{$landlordPayment->landlord_payment_amount}}";
 @else
    var curr_amt = 0;
 @endif
 var landlord_payment_invoice_amt = $('#landlord_payment_invoice_amt').val().replace(/,/g, '') ;
 var landlord_payment_amount = $('#landlord_payment_amount').val().replace(/,/g, '');

 var landlord_invoice_id = $('#landlord_invoice_id').val();

 $.ajax({
  method: "POST",
  url: "{{route('invoiceBalanceAmount')}}",
  data: { 'landlord_invoice_id': landlord_invoice_id, 
  "_token" : $('meta[name="csrf-token"]').attr('content')},
  success: function(data){
    var results = $.parseJSON(data);
           // alert(results.total);
           $('#sumPaymentAmt').val(results.total); 
           $(".read").attr('readonly',true);
           var status = results[0];

           var sumPaymentAmt = $('#sumPaymentAmt').val();
           var balanceAmt = $('#landlord_payment_balance_amt_data').val().replace(/,/g, '');
           //alert(balanceAmt);

           var landlord_payment_balance_amt = parseFloat(balanceAmt - landlord_payment_amount);
          landlord_payment_balance_amt = parseFloat(curr_amt) + landlord_payment_balance_amt;
           $('#landlord_payment_balance_amt').val(formatNumber(landlord_payment_balance_amt.toFixed(3)));


           if(landlord_payment_balance_amt < 0){

            $(".error2").css("display","block").css("color","red");
            $("#submitBtn").prop('disabled',true);
          }
          else {
            $(".error2").css("display","none");
            $("#submitBtn").prop('disabled',false);        
          }
        }
      }); 
});
/************************************************************/

</script>
@endsection



@endsection
