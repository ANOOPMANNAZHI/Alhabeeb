@extends('layouts.plms-app')



@section('content')



<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">{{ (isset($depositRefund))? 'Edit' : 'Add'}} Deposit Refund</div>
    </div>
    {{ (isset($depositRefund))?   Breadcrumbs::render('depositRefund.edit',$depositRefund) :  Breadcrumbs::render('depositRefund.create') }} 



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
  .dimdetail{
    padding: 10px 0px 14px 15px;
  }
  .dimtablepad{
    padding: 0px 15px 0px 15px;
  }
  .txt{
    height: 30px;
    background-color: white;
  }
  .desc{
    background-color: white;
  }
  .ac_codes_id{
    width: 100%;
  }
</style>
<form method="post" autocomplete="off" id="payment-form" action="{{isset($depositRefund)? route( 'depositRefund.update',$depositRefund->id) : route( 'depositRefund.store')}}" data-toggle="validator">
  @csrf  @if(isset($depositRefund)){{method_field('PUT')}}@endif
  @if($errors->any())
  <div class="alert alert-danger">
    <ul style="margin-bottom:0;">
      @foreach($errors->all() as $error)
      <li>{{$error}}</li>
      @endforeach
    </ul>
  </div>
  @endif
  <div class="row">

    <!-- activities -->
    <div class="col-md-12 col-sm-12 dashboardtab">
      <div class="panel tab-border card-box">
        <div class="panel-body">
          <div class="tab-content">                                     
            {{-- @if(isset($depositRefund)) --}}
            <div class="tab-pane active" id="maintenance">

              <div class="row">
                <div class="col formpad">
                  <!-- starts -->
                  <div class="dataSearchBox">
                    <div class="card-body row">

                      <div class="col-sm-6">
                        <div class="form-group">
                          <label for="deposit_refund_no">Refund No<small class="textRed">*</small></label>
                          <div class="p-relative">
                           <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
                           <input required type="text" class="form-control" id="deposit_refund_no" readonly name="deposit_refund_no" value="{{ old('landlord_payment_no', isset($depositRefund)? $depositRefund->deposit_refund_no : $nextCode )}}"  placeholder="Enter Deposit Refund No">
                         </div>
                       </div>
                     </div>
                     <div class="col-sm-6">
                      <div class="form-group">
                        <label for="deposit_refund_date">Refund Date<small class="textRed">*</small></label>
                        <div class="p-relative">
                         <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                         <input required type="date" class="form-control" id="deposit_refund_date"  name="deposit_refund_date" value="{{ old('deposit_refund_date', isset($depositRefund)? $depositRefund->deposit_refund_date->format('Y-m-d') :  date('Y-m-d',strtotime(today())) )}}"  placeholder="Enter Deposit Refund Date" >
                       </div>
                     </div>
                   </div>
                 </div>
               </div>
               <!--ends -->

               <!-- starts -->
               <div class="sub-head">Tenant Details</div>
               <div class="dataSearchBox">
                <div class="card-body row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="building_name">Building Name<small class="textRed">*</small></label>
                      <div class="p-relative">
                       <i class="fa fa-building icn-add" aria-hidden="true"></i>
                       <input required type="text" class="form-control building-name2" id="building_name"  name="building_name" value="{{ old('building_name', isset($depositRefund)? $depositRefund->tenantContract->building->building_name : '' )}}"  placeholder="Enter Building Name">
                       <select class="form-control building_id building-name1" id="building_id" name="building_code" style="display: none;">
                         <option value="">Select Building </option>
                       </select>
                       <input  type="hidden"  id="buildings_id" name="building_id" value="{{ old('building_name', isset($depositRefund)? $depositRefund->tenantContract->building->id : '' )}}">
                     </div>
                   </div>
                 </div>
                 <div class="col-sm-6">
                  <div class="form-group">
                    <label for="building_code">Building Code<small class="textRed">*</small></label>
                    <div class="p-relative">
                     <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                     <input  type="text" class="form-control" id="building_code"  name="building_code" value="{{ old('building_code', isset($depositRefund)? $depositRefund->tenantContract->building->building_code : '' )}}"  placeholder="Enter Building Code" readonly>
                   </div>
                 </div>
               </div>
               <div class="col-sm-6">
                 <div class="form-group">
                  <label for="unit_code">Unit Code<small class="textRed">*</small></label>
                  <div class="p-relative">
                   <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                   <select class="form-control unit_id unit-code1" id="unit_id" name="unit_code" style="display: none;">
                    <option value="">Select Unit </option>
                  </select>
                  <select class="form-control units_id units-code1" id="units_id" name="unit_code" style="display: none;">
                    <option value="">Select Unit </option>
                  </select>
                  <input  type="text" class="form-control unit-code2" id="unit_code"  name="unit_code" value="{{ old('unit_code', isset($depositRefund)? $depositRefund->tenantContract->unit->unit_code : '' )}}"  placeholder="Enter Unit Code">

                  <input  type="hidden"  id="unit_ids" name="unit_id" value="{{ old('building_name', isset($depositRefund)? $depositRefund->tenantContract->unit->unit_code : '' )}}">
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="unit_code">Unit No<small class="textRed">*</small></label>
                <div class="p-relative">
                 <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                 <input  type="text" class="form-control" id="unit_no"  name="unit_no" value="{{ old('unit_no', isset($depositRefund)? $depositRefund->tenantContract->unit->unit_no : '' )}}"  placeholder="Enter Unit Code" readonly>
               </div>
             </div>
           </div>
           <div class="col-sm-6">
            <div class="form-group">
              <label for="tenant_name">Tenant Name<small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
               <input  type="text" class="form-control tenant_name" id="tenant_name"  name="tenant_name" value="{{ old('tenant_name', isset($depositRefund)? $depositRefund->tenantContract->tenant->tenant_name : '' )}}"  placeholder="Enter Tenant Name">

               <input  type="hidden"  id="tenant_id" name="tenant_id" value="{{ old('building_name', isset($depositRefund)? $depositRefund->tenantContract->tenant->id : '' )}}">

               <select class="form-control tenants_id tenants_id"  id="tenants_id" name="tenant_id" style="display: none;">
                <option value="">Select Tenant </option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label for="tenant_code">Tenant Code<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="fa fa-rebel icn-add" aria-hidden="true"></i>
             <input  type="text" class="form-control" id="tenant_code"  name="tenant_code" value="{{ old('tenant_code', isset($depositRefund)? $depositRefund->tenantContract->tenant->tenant_code : '' )}}"  placeholder="Enter Tenant Code" readonly>
           </div>
         </div>
       </div> 
       <div class="col-sm-6">
        <div class="form-group">
          <label for="tenant_contract_no">Agreement No<small class="textRed">*</small></label>
          <div class="p-relative">
           <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
           <input type="text" class="form-control" id="tenant_contract_no"  name="tenant_contract_no" value="{{ old('tenant_contract_no', isset($depositRefund)? $depositRefund->tenantContract->tenant_contract_no : '' )}}"  placeholder="Enter Agreement No" readonly>
           <input  type="hidden"  id="tenant_contract_id"  name="tenant_contract_id" value="{{ old('tenant_contract_no', isset($depositRefund)? $depositRefund->tenantContract->id : '' )}}">
         </div>
       </div>
     </div> 

     <div class="col-sm-6">
      <div class="form-group">
        <label for="deposit_refund_valid_from">Valid From <small class="textRed">*</small></label>
        <div class="p-relative">
         <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
         <input required  type="text" class="form-control read" id="deposit_refund_valid_from1"  name="deposit_refund_valid_from1" value="{{ old('deposit_refund_valid_from', isset($depositRefund)? $depositRefund->deposit_refund_valid_from->format('d/m/Y') : '' )}}"  placeholder="Enter Valid From " readonly>

         <input type="hidden" name="deposit_refund_valid_from" id="deposit_refund_valid_from" value="{{ old('deposit_refund_valid_from', isset($depositRefund)? $depositRefund->deposit_refund_valid_from : '' )}}">
       </div>
     </div>
   </div> 

   <div class="col-sm-6">
    <div class="form-group">
      <label for="deposit_refund_valid_to">Valid To <small class="textRed">*</small></label>
      <div class="p-relative">
       <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
       <input required  type="text" class="form-control read" id="deposit_refund_valid_to1"  name="deposit_refund_valid_to1" value="{{ old('deposit_refund_valid_to', isset($depositRefund)? $depositRefund->deposit_refund_valid_to->format('d/m/Y') : '' )}}"  placeholder="Enter Valid To" readonly>

       <input type="hidden" name="deposit_refund_valid_to" id="deposit_refund_valid_to" value="{{ old('deposit_refund_valid_to', isset($depositRefund)? $depositRefund->deposit_refund_valid_to : '' )}}">
     </div>
   </div>
 </div>       
</div>
</div>
<!--ends -->

<!-- starts -->
<div class="dataSearchBox">
  <div class="card-body row">

    <div class="col-sm-12">
      <div class="form-group">
        <label for="receipts_generation_receipt_no">Deposit Receipt No<small class="textRed">*</small></label>
        <div class="p-relative">
         <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
         <input  required type="text" class="form-control receipts_generation_receipt_no" id="receipts_generation_receipt_no"  name="receipts_generation_receipt_no"   placeholder="Enter Deposit Receipt No" value="{{ old('receipts_generation_receipt_no', isset($depositRefund)? $depositRefund->receiptGeneration->receipts_generation_receipt_no .'-'. $depositRefund->receiptGeneration->tenantContractInfo->tenant->tenant_name .'-'.$depositRefund->receiptGeneration->tenantContractInfo->building->building_name .'-'. $depositRefund->receiptGeneration->tenantContractInfo->Unit->unit_code : '' )}}">

         <input type="hidden" name="receipts_generation_id" id="receipts_generation_id" value="{{ old('receipts_generation_id', isset($depositRefund)? $depositRefund->receipts_generation_id : '' )}}">

         <select class="form-control receipts_generation_receipt" id="receipts_generation_receipt" name="receipts_generation_receipt_no" style="display: none;">
                <option value="">Select Tenant </option>
              </select>
       </div>
       <label id="receipts_generation_receipt_no-error" class="error" for="receipts_generation_receipt_no"></label>
     </div>
   </div> 
 </div>
</div>
<!--ends -->


<!-- starts -->
<div class="sub-head">Payment Details</div>
<div class="dataSearchBox">
  <div class="card-body row">
   <div class="col-sm-6">
    <div class="form-group">
      <label for="deposit_refund_payment_method">Payment Method<small class="textRed">*</small></label>
      <div class="p-relative">
        <div class="form-control" ><input type="radio" id='chkYes'  required {{ (old('deposit_refund_payment_method', isset($depositRefund)?  $depositRefund->deposit_refund_payment_method : -1) == 1 )? 'checked' : 'checked' }}  name="deposit_refund_payment_method" value="1">Cheque  
          <input type="radio" id='chkNo'  required  {{ (old('deposit_refund_payment_method', isset($depositRefund)?  $depositRefund->deposit_refund_payment_method : -1) == 2 )? 'checked' : '' }}  name="deposit_refund_payment_method" value="2"> Cash 
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6" id="dvPinNo"  @if(!isset($depositRefund)) style="display: none" @endif>
    <div class="form-group">
      <label for="deposit_refund_cheque_no">Cheque No<small class="textRed">*</small></label>
      <div class="p-relative">
       <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
       <input type="text" class="form-control" id="deposit_refund_cheque_no"  name="deposit_refund_cheque_no" value="{{ old('deposit_refund_cheque_no', isset($depositRefund)? $depositRefund->deposit_refund_cheque_no : '' )}}"  placeholder="Enter Cheque No" >
     </div>
   </div>
 </div>  
 <div class="col-sm-6" >
  <div class="form-group">
    <label for="bank_id">Bank<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
     <select class="form-control" id="bank_id"  name="bank_id" required>
      <option value=''>Select Bank</option>
      @foreach($banks as $bank)
      <option  {{(old('bank_id', isset($depositRefund)?  $depositRefund->bank_id : 0) == $bank->id) ? 'selected' : '' }} value="{{$bank->id}}">{{$bank->bank_name}}</option>
      @endforeach       
     

    </select>

  </div>
</div>
</div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="deposit_amount_original">Original Deposit Amount</label>
    <div class="p-relative">
     <i class="fa fa-money icn-add" aria-hidden="true"></i>
     <input type="text" class="form-control text-right" id="deposit_amount_original" name="deposit_amount_original" value="{{ old('deposit_amount_original', isset($depositRefund) ? numberFormat($depositRefund->receiptGeneration->receipts_generation_amt) : '' )}}" readonly>
   </div>
 </div>
</div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="deposit_refund_amt">Refund Amount (Net of Deductions)<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-money icn-add" aria-hidden="true"></i>
     <input required  type="text" class="form-control allownumericwithdecimal text-right" id="deposit_refund_amt"  name="deposit_refund_amt" value="{{ old('deposit_refund_amt', isset($depositRefund)? numberFormat($depositRefund->deposit_refund_amt) : '' )}}"  placeholder="Enter Refund Amount" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" readonly>
     <input type="hidden" name="deposit_refund_amount" id="deposit_refund_amount">
     <div class="error" style="display:none">Should Not Be greater than Refund Amount</div>
   </div>
 </div>
</div>



<div class="col-sm-6">
  <div class="form-group">
    <label for="deposit_refund_comment">Comment</label>
    <div class="p-relative">
      <i class="fa fa-comment icn-add" aria-hidden="true"></i>
      <textarea  class="form-control" id="deposit_refund_comment"  name="deposit_refund_comment" readonly>{{ old('deposit_refund_comment', isset($depositRefund)? $depositRefund->deposit_refund_comment : '' )}}</textarea>
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
         <input  type="text" class="form-control" id="ax_batch_id" name="ax_batch_id" value="{{ old('ax_batch_id', isset($depositRefund)? $depositRefund->ax_batch_id : '' )}}"  placeholder="Enter AX Batch ID" readonly>
       </div>
     </div>
   </div>

   <div class="col-sm-6">
    <div class="form-group">
      <label for="ax_invoice_no">AX Batch No</label>
      <div class="p-relative">
       <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
       <input type="text" class="form-control" id="ax_invoice_no"  name="ax_invoice_no" value="{{ old('ax_invoice_no', isset($depositRefund)? $depositRefund->ax_invoice_no : '' )}}"  placeholder="Enter AX Batch No" readonly>
     </div>
   </div>
 </div>
</div>
</div>
<!--ends -->
<!-- starts -->
<div class="sub-head">Financial Dimension</div>
<div class="dataSearchBox">
  <div class="card-body row">
    <div class="col-sm-6">
      <div class="form-group">
        <label for="financial_dimension">Division</label>
        <div class="p-relative">
         <i class="fa fa-rebel icn-add" aria-hidden="true"></i>
          <input  type="text" class="form-control" id="financial_dimension"  name="financial_dimension" value="{{ old('building_name', isset($depositRefund)? $depositRefund->tenantContract->building->building_name : '' )}}"  placeholder="Division" readonly>
        
      </div>
    </div>
  </div> 
  <div class="col-sm-6">
    <div class="form-group">
      <label for="financial_building_code">Building</label>
      <div class="p-relative">
       <i class="fa fa-building icn-add" aria-hidden="true"></i>
       <input  type="text" class="form-control" id="financial_building_code"  name="financial_building_code" value="{{ old('building_name', isset($depositRefund)? $depositRefund->tenantContract->building->building_name : '' )}}"  placeholder="Building" readonly>
     </div>
   </div>
 </div>
</div>
</div> 
<!--ends -->




</div>
</div>
</div>
{{-- @endif --}}
</div>
</div>

<div class="sub-head">Deductions</div>
<div class="row">
  <div class="col">
    <div class="dimtablepad">
      <div class="table-responsive">
        <table class="table" id="deduction_table" style="border: 1px solid #ccc;">
          <thead>
            <tr>
              <th><a href='#' id="add_deduction" class="add_deduction"><i class="fa fa-plus" aria-hidden="true"></i></a></th>
              <th>Reason</th>
              <th>Description</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody id="deduction_line_list">
@if(old('deduction_reason'))
@foreach(old('deduction_reason') as $key => $oldReason)
            <tr id="deduction_row{{$key+1}}">
              <td class="minus">
                @if($key >= 1)
                <a href="#" class="remove_deduction"><i class="fa fa-minus" aria-hidden="true"></i></a>
                @endif
              </td>
              <td>
                <select class="form-control" name="deduction_reason[]">
                  <option value="">Select Reason</option>
                  @foreach(['Cleaning', 'Damage', 'Unpaid Utility', 'Other'] as $reason)
                  <option value="{{$reason}}" {{ $oldReason == $reason ? 'selected' : '' }}>{{$reason}}</option>
                  @endforeach
                </select>
              </td>
              <td>
                <input type="text" class="form-control" name="deduction_description[]" value="{{ old('deduction_description')[$key] ?? '' }}" placeholder="Description">
              </td>
              <td>
                <input type="text" onkeyup="FormatCurrency(this); recalcNetRefundAmount();" name="deduction_amount[]" value="{{ old('deduction_amount')[$key] ?? '0.000' }}" class="deduction_amount allownumericwithdecimal" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
              </td>
            </tr>
@endforeach
@elseif(isset($depositRefund) && count($depositRefund->depositRefundDeduction) > 0)
@foreach($depositRefund->depositRefundDeduction as $key => $deduction)
            <tr id="deduction_row{{$key+1}}">
              <td class="minus">
                @if($key >= 1)
                <a href="#" class="remove_deduction"><i class="fa fa-minus" aria-hidden="true"></i></a>
                @endif
              </td>
              <td>
                <select class="form-control" name="deduction_reason[]">
                  <option value="">Select Reason</option>
                  @foreach(['Cleaning', 'Damage', 'Unpaid Utility', 'Other'] as $reason)
                  <option value="{{$reason}}" {{ $deduction->deduction_reason == $reason ? 'selected' : '' }}>{{$reason}}</option>
                  @endforeach
                </select>
              </td>
              <td>
                <input type="text" class="form-control" name="deduction_description[]" value="{{$deduction->description}}" placeholder="Description">
              </td>
              <td>
                <input type="text" onkeyup="FormatCurrency(this); recalcNetRefundAmount();" name="deduction_amount[]" value="{{numberFormat($deduction->amount)}}" class="deduction_amount allownumericwithdecimal" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
              </td>
            </tr>
@endforeach
@else
            <tr id="deduction_row1">
              <td class="minus"></td>
              <td>
                <select class="form-control" name="deduction_reason[]">
                  <option value="">Select Reason</option>
                  @foreach(['Cleaning', 'Damage', 'Unpaid Utility', 'Other'] as $reason)
                  <option value="{{$reason}}">{{$reason}}</option>
                  @endforeach
                </select>
              </td>
              <td>
                <input type="text" class="form-control" name="deduction_description[]" value="" placeholder="Description">
              </td>
              <td>
                <input type="text" onkeyup="FormatCurrency(this); recalcNetRefundAmount();" name="deduction_amount[]" value="0.000" class="deduction_amount allownumericwithdecimal" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
              </td>
            </tr>
@endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!--ends -->
<!--bbbbbbbbbbbbbbbb -->
<div class="sub-head dimdetail">Dimension Details</div>
<div class="row">
  <div class="col">
    <div class="dimtablepad">
      <div class="dimtablepad">
        <!--starts -->
        <div class="">    
          <div class="row tablepad">
            <div class="table-responsive">
              <table class="table" id="meme" style="border: 1px solid #ccc;">
               <thead>
                <tr>
                  <th>
                    <a  href='#'   id="add_details" class=" add_details"><i class="fa fa-plus" aria-hidden="true"></i></a>
                  </th> 
                  <th>Account</th>
                  <th>Type</th>
                  <th>Dr.Amt</th>
                  <th>Cr.Amt</th>
                 </tr>

              </thead>
              <tbody id="acc_line_list">

  @if(isset($depositRefund) && count($depositRefund->depositRefundDimension)>0)

  @foreach($depositRefund->depositRefundDimension as  $key=>$dimDetails)

  <tr  id="first_row{{$key+1}}" >
  <td class="minus">
    @if($key>=2)
    <a href="#"  class="remove_details"><i class="fa fa-minus" aria-hidden="true"></i></a>
    @endif
  </td>
  <td>
    <input type="text" class="account_code type"  name="account_code[]" value="{{($dimDetails->account_code==0)?$depositRefund->bankInfo->bank_code:$dimDetails->account_code.'-'.$dimDetails->description}}" {{($key<2)?'':''}}>
    <input type="hidden" class="account_id type"  name="account_id[]" value="{{$dimDetails->ac_codes_id}}">
  </td>
  <td>
    <input type="text"  name="acc_type[]" value="{{$dimDetails->type}}" {{($key<2)?'':''}}>
    
  </td>                  
  <td>
    <input type="text" onkeyup="FormatCurrency(this)" name="debit_amt[]" value="{{$dimDetails->debit_amount}}" id="debit_amount_first" class="debit_amount allownumericwithdecimal" data_name="debit_amount" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" {{($key<2)?'':''}}>
  </td>
  <td>
    <input type="text" onkeyup="FormatCurrency(this)" class="credit_amount allownumericwithdecimal"  id="credit_amount_first"  name="credit_amt[]" value="{{$dimDetails->credit_amount}}" data_name="credit_amount" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" {{($key<2)?'':''}}>
  </td> 
</tr>
@endforeach 
@else               
<tr id="first_row1">
  <td class="minus"></td>
	<td>
		<input type="text" class="account_code type"  name="account_code[]" value="{{$acc_parameter->acc_params_dr_acc.'-'.$accountCodes->acc_code_desc}}">
		<input type="hidden" class="account_id type"  name="account_id[]" value="{{$accountCodes->id}}">
	</td>
	<td>
		<input type="text"  name="acc_type[]" value="{{$acc_parameter->acc_params_dr_type}}" readonly>
    
	</td>                  
	<td>
		<input type="text" onkeyup="FormatCurrency(this)" name="debit_amt[]" value=0.000 id="debit_amount_first" class="debit_amount allownumericwithdecimal" data_name="debit_amount" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" >
	</td>
	<td>
		<input type="text" onkeyup="FormatCurrency(this)" class="credit_amount allownumericwithdecimal"  id="credit_amount_first"  name="credit_amt[]" value=0.000 data_name="credit_amount" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
	</td> 
</tr>
{{--<--
<tr id="first_row2">
  <td class="minus"></td>
  <td>
		<input type="text" class="account_code type"  name="account_code[]" value="" readonly id="account_code_cr">
		<input type="hidden" class="account_id type"  name="account_id[]" value=0>
	</td>
	<td>
		<input type="text"  name="acc_type[]" value="{{$acc_parameter->acc_params_cr_type}}" readonly >

	</td>                  
	<td>
		<input type="text" onkeyup="FormatCurrency(this)" name="debit_amt[]" value=0.000 id="debit_amount_second" class="debit_amount allownumericwithdecimal" data_name="debit_amount" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" readonly>
	</td>
	<td>
		<input type="text" onkeyup="FormatCurrency(this)" class="credit_amount allownumericwithdecimal" id="credit_amount_second" name="credit_amt[]" value=0.000 data_name="credit_amount" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" readonly>
	</td> 
</tr> !> --}}
@endif
  </tbody>
  <tfoot>
    <tr>
      <td colspan="3" ><span class="pull-right" >Total : </span></td>
      <td><input type="text" id="debit_amount_total" class="debit_amount_total desc"  name="debit_amount_total" value="{{isset($depositRefund)? numberFormat($debitAmount): '0.000'}}" disabled></td>                      
      <td ><span class="pull-left"><input type="text" class="credit_amount_total desc" id="credit_amount_total"  name="credit_amount_total" value="{{isset($depositRefund)? numberFormat($creditAmount): '0.000'}}" disabled></span></td>
    </tr>
  </tfoot>
</table>
</div>
</div>  
</div>
<!--ends -->

</div>

</div>
<div class="col dimdetail"><button type="submit" class="btn btn-primary submitBtn" id="submitBtn">{{ (isset($depositRefund))? 'Update' : 'Save'}}</button> </div>
</div>

</div>
<!--bbbbbbbbbbbbbbbb -->

</div>
</div>
</div>           




</form>

<form id="delete-form" action="" method="POST">
  {{ method_field('DELETE') }}  {{csrf_field()}}
  <input value="delete" style="display: none;" type="submit">
</form>
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
@include('backoffice::Transaction.deposit_refund_multisearch_js')
<script>
  /************************************************************/
  $("#payment-form").validate({
    submitHandler: function(form) {

      var debit_amt = credit_amt = 0;

      $('.debit_amount').each(function(){
        if($(this).val() != '')
          debit_amt = debit_amt + parseFloat($(this).val());
      });

      $('.credit_amount').each(function(){
        if($(this).val() != '')
          credit_amt = credit_amt + parseFloat($(this).val());
      });

      var amount = parseFloat($('#deposit_refund_amt').val());
      var total  = debit_amt - credit_amt;

      var $tr = $('tr[id^="first_row"]:last');
      for($i=1;$i <= $('#acc_line_list tr').length;$i++){

        if($("#first_row"+$i).find('.account_code').val() ==''){
          alert("Please Fill Atleast One Account Code ");
          return false;
        }
      }

		// if(total== 0  &&  debit_amt == amount && credit_amt == amount )
		if(debit_amt == credit_amt )
		{
      $('.save_form').prop('disabled', true);
      form.submit();
    }else{
      alert('Total Debit And Total Credit Should Be Tally.');
      return false;

    }
$('.submitBtn').prop('disabled', true);
   form.submit();
  }
});
//AutoComplete For Vendor Name
/************************************************************/
function recalcNetRefundAmount(){
   var original = parseFloat(($('#deposit_amount_original').val() || '0').replace(/,/g, '')) || 0;
   var totalDeductions = 0;
   $('.deduction_amount').each(function(){
     var reason = $(this).closest('tr').find('select[name="deduction_reason[]"]').val();
     if(!reason){
       return;
     }
     var val = ($(this).val() != '') ? $(this).val().replace(/,/g, '') : 0;
     totalDeductions = totalDeductions + parseFloat(val);
   });
   var net = original - totalDeductions;
   $('#deposit_refund_amt').val(formatNumber(net.toFixed(3))).trigger('change');
 }
$(document).ready(function() {
 @if($isYearCorrect == false)
      alert("Current Year Is Not Match With the Sequence Year");
  @endif

 $('.unit-code1').hide();
 $('.unit-code2').show(); 
 $('.building-name1').hide();
 $('.building-name2').show();

 function amtCal(className){
  var total = 0;
  $('.'+className).each(function() {
    var this_val = ($(this).val() !=  '')? ($(this).val().replace(/,/g, '')) : 0;
    total = parseFloat(total) + parseFloat(this_val);
  });

  $('#'+className+'_total').val(formatNumber(total.toFixed(3)));
}


$(document).on("keyup",'.debit_amount,.credit_amount',function (event) {

 var className 		= $(this).attr('data_name');
 var parentId 		= $(this).parent().parent().attr('id');
 var credit_value	= parseFloat($("#"+parentId).find('.credit_amount').val().replace(/,/g, ''));
 var debit_value		= parseFloat($("#"+parentId).find('.debit_amount').val().replace(/,/g, ''));

 if(className == 'debit_amount' &&  debit_value > 0 ){

   $("#"+parentId).find('.credit_amount').val(0);
 }
 else if(className == 'credit_amount' &&  credit_value > 0 ){

   $("#"+parentId).find('.debit_amount').val(0);
 }

 amtCal('debit_amount');
 amtCal('credit_amount');
 var debit_total = $('#debit_amount_total').val().replace(/,/g, '');
 var credit_total = $('#credit_amount_total').val().replace(/,/g, '');
 var deposit_refund_amt_total = debit_total-credit_total;
 $("#deposit_refund_amt").val(formatNumber(deposit_refund_amt_total.toFixed(3)));
 var deposit_refund_comment = $('#deposit_refund_comment').val();
 var replaced_comment = replaceSpecial(deposit_refund_comment);
 $('#deposit_refund_comment').val(replaced_comment+'@'+deposit_refund_amt_total.toFixed(3));
});

/************************************************************/ 
$('.add_details').click(function(event){

 event.preventDefault();
 var $tr = $('tr[id^="first_row"]:last');
 for($i=1;$i <= $('#acc_line_list tr').length;$i++){

  if($("#first_row"+$i).find('.account_code').val() ==''){
    alert("Please Fill All the Account Code ");
    return false;
  }
}

var num = parseInt( $tr.prop("id").match(/\d+/g), 10 )+1;

var first_row = $tr.clone().prop('id', 'first_row'+num );
$(first_row).find(".minus").html('<a href="#"  class="remove_details"><i class="fa fa-minus" aria-hidden="true"></i></a>');

$(first_row).find("input:text").val("").end().appendTo('tbody');
$(first_row).find(".account_code").prop("readonly", false); 
$(first_row).find(".debit_amount").prop("readonly", false); 
$(first_row).find(".credit_amount").prop("readonly", false); 

$('tr').each(function(rowIndex){
    /// find each input with a name attribute inside each row
    $(this).find('input[name]').each(function(){
      var name;
      name = $(this).attr('name');
      name = name.replace(/\[[0-9]+\]/g, '['+(rowIndex-1)+']');
      $(this).attr('name',name);
      if(name == 'account_code[]'){
        
        $("input[name^='account_code']").autocomplete({
          source : '{!!URL::route('accountCodeAutocomplete')!!}',
          minlenght:2,
          autoFocus:true,
          change:function(e,ui){
                  var parentId = $(this).parent().parent().attr('id');

                  if (ui.item == null || ui.item == undefined || parentId =='') {
            $(this).val("");
                          $("#"+parentId).find('.account_id').val('');
                          $("#"+parentId).find('.account_code').val('');
                  }
                  else{
                   
                    $("#"+parentId).find('.account_id').val(ui.item.ids);
                  }
                }
        });
      }
    });

    $(this).find('select[name]').each(function(){
      var name;
      name = $(this).attr('name');
      name = name.replace(/\[[0-9]+\]/g, '['+(rowIndex-1)+']');
      $(this).attr('name',name);
    });
  });
$(first_row).find("input[name ='acc_type[]']").val('{{AX_CODE_DEPOSIT_REFUND_DEBT_LEDGER}}');
amtCal('debit_amount');
amtCal('credit_amount');


});
/*--------------Remove Line Item ---------------*/
$(document).on("click",".remove_details",function(event) {
    
    event.preventDefault();
     var tr_count =  $(this).closest('tbody').find('tr').length;
      if(tr_count > 1)
         $(this).closest('tr').remove();
      else{

        $(this).find('input[name]').each(function(){
        var name;
        name = $(this).attr('name');
        name = name.replace(/\[[0-9]+\]/g, '['+(rowIndex-1)+']');
        $(this).attr('name',name);
       });

       $(this).find('select[name]').each(function(){
        var name;
        name = $(this).attr('name');
        name = name.replace(/\[[0-9]+\]/g, '['+(rowIndex-1)+']');
        $(this).attr('name',name);
       });

       }
  amtCal('debit_amount');
  amtCal('credit_amount');
      
});
/************ End Remove Line Item  *******/
/*--------------Add Deduction Row ---------------*/
$('.add_deduction').click(function(event){
  event.preventDefault();
  var $tr = $('tr[id^="deduction_row"]:last');
  var num = parseInt($tr.prop("id").match(/\d+/g), 10) + 1;
  var new_row = $tr.clone().prop('id', 'deduction_row' + num);
  new_row.find(".minus").html('<a href="#" class="remove_deduction"><i class="fa fa-minus" aria-hidden="true"></i></a>');
  new_row.find("select").val("");
  new_row.find("input:text").val("0.000");
  new_row.find("input[name='deduction_description[]']").val("");
  new_row.appendTo('#deduction_line_list');
});
/*--------------Remove Deduction Row ---------------*/
$(document).on("click", ".remove_deduction", function(event) {
  event.preventDefault();
  var tr_count = $(this).closest('tbody').find('tr').length;
  if (tr_count > 1) {
    $(this).closest('tr').remove();
  }
  recalcNetRefundAmount();
});
$(document).on("change", ".deduction_amount", function(){
  recalcNetRefundAmount();
});
@if(!isset($depositRefund))
if ($("#chkYes").is(":checked")) {

 $("#dvPinNo").show();
 $("#deposit_refund_cheque_no").show().prop('required',true);
} else {
  $("#deposit_refund_cheque_no").empty();
  $("#deposit_refund_cheque_no").hide().prop('required',false);
  $("#dvPinNo").hide();
}
@endif
/*******************************************************************************/
$(function() {
 $("input[name='deposit_refund_payment_method']").click(function() {
   if ($("#chkYes").is(":checked")) {
     $("#dvPinNo").show();
     $("#deposit_refund_cheque_no").val('').show().prop('required',true);
     
   } else {
    $("#deposit_refund_cheque_no").empty();
    $("#deposit_refund_cheque_no").hide().prop('required',false);
    $("#dvPinNo").hide();
  }
});
});
 //AutoComplete For Building Name
 /*********************************************************************************/ 
 $('#building_name').autocomplete({
  source : '{!!URL::route('buildingDepositRefundAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#building_name").val('');
      $('#create_build_span').hide();
      $('#building_name-error').show();
    }else {
      if(ui.item.division ==1) 
          var divCode ='HO' 
      else 
          var divCode ='PLM';
      $('#buildings_id').val(ui.item.ids);       
      $('#building_code').val(ui.item.code);       
      $('#financial_building_code').val(ui.item.value);    
      $('#financial_dimension').val(divCode);    
      $('.dim2').val(ui.item.value);       
      $('#create_build_span').show(); 
      $('.unit-code2').hide();
      $('.unit-code1').show();
      $('.units-code1').hide();

      var selected = '';

      $.ajax({
        method: "POST",
        url: "{{route('unitDetailsByBuildingId')}}",
        data: {"id":ui.item.ids,"_token": "{{ csrf_token() }}"},
        cache: false,
        dataType: "json",
        success: function(data){
          if(data.length > 0){
            if(data.length ==1){selected = "selected";}
            $('#unit_id').empty();
            $('#unit_id').append('<option value = "">'+ 'Select Unit' +'</option>')
            $.each(data, function(key, value) {

              $('#unit_id').append('<option unit_no="'+value['unit_no'] +'" value="'+ value['id'] +'" '+ selected +'>'+ value['unit_code'] +'</option>');
            });
            $('.tenant_name').hide();
            $('.tenants_id').show(); 
            if(data.length ==1) $( "#unit_id" ).trigger( "change" );
          // if(data.length ==1) $( "#tenant_id" ).trigger( "change" );
          }
          else{
            $('#unit_id').html('<option value="">No Available Units</option>');
          }

        }
      });   
    }

  }

});
 //change building
 /*******************************************************************************/
 $(document).on('change',"#building_id", function(){
  //alert($("#building_id").val());
  var id = $("#building_id").val();
  var buildings_id = $("#building_id").val();
  $("#buildings_id").val(buildings_id);
  var selected = '';
  if(id == null){id = $("#building_id").val();}
  if(id !=""){
   $.ajax({
    method: "POST",
    url: "{{route('unitDetailsByBuildingId')}}",
    data: {"id":id,"_token": "{{ csrf_token() }}"},
    cache: false,
    dataType: "json",
    success: function(data){
      if(data.length > 0){
        if(data.length ==1){selected = "selected";}
        $('#unit_id').empty();
        $('#unit_id').append('<option value = "">'+ 'Select Unit' +'</option>')
        $.each(data, function(key, value) {
          $('#unit_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_code'] +'</option>');
        });
        
      }
      else{
        $('#unit_id').html('<option value="">No Available Units</option>');
      }

    }
  }); 
 }

});

 
 //AutoComplete For Tenant Name
 /*********************************************************************************/ 
 $('#tenant_name').autocomplete({
  source : '{!!URL::route('tenantDepositRefundAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#tenant_name").val('');
      $('#create_build_span').hide();
      $('#tenant_name-error').show();
    }else {

      $('#tenant_id').val(ui.item.ids);       
      $('#tenant_code').val(ui.item.code);       
      $('.unit-code2').hide();
      $('.units-code1').show();
      $('.building-name1').show();
      $('.building-name2').hide(); 
      var selected = '';
      $.ajax({
        method: "POST",
        url: "{{route('getUnitDetailsByTenantId')}}",
        data: {"id":ui.item.ids,"_token": "{{ csrf_token() }}"},
        cache: false,
        dataType: "json",
        success: function(data){
          if(data.length > 0){
            if(data.length ==1){selected = "selected";}
            $('#units_id').empty();
            $('#units_id').append('<option value = "">'+ 'Select Unit' +'</option>')
            $.each(data, function(key, value) {

              $('#units_id').append('<option unit_no="'+value['unit_no'] +'" value="'+ value['id'] +'" '+ selected +'>'+ value['unit_code'] +'</option>');
              if(data.length ==1) $( "#units_id" ).trigger( "change" );
            });
          }
          else{
            $('#units_id').html('<option value="">No Available Units</option>');
          }

        }
      });  
    }
    amtCal('debit_amount');
    amtCal('credit_amount');
  }
});
$(document).on("change",'#deposit_refund_amt',function(event){
	if($(this).val()){
		var refund_amt = parseFloat($(this).val().replace(',',''));
  	$('#first_row1 .debit_amount').val(formatNumber(refund_amt.toFixed(3))); 
		$('#first_row1 .credit_amount').val(0); 
		$('#first_row2 .credit_amount').val(formatNumber(refund_amt.toFixed(3))); 
		$('#first_row2 .debit_amount').val('0.000'); 
		$('#debit_amount_total').val(formatNumber(refund_amt.toFixed(3))); 
		$('#credit_amount_total').val(formatNumber(refund_amt.toFixed(3))); 
	}
	else{
		
		$('#first_row1 .debit_amount').val('0.000'); 
		$('#first_row1 .credit_amount').val('0.000'); 
		$('#first_row2 .credit_amount').val('0.000'); 
		$('#first_row2 .debit_amount').val('0.000'); 
		$('#debit_amount_total').val('0.000'); 
		$('#credit_amount_total').val('0.000'); 
    $('deposit_refund_amount').val($(this).val());
	}
	amtCal('debit_amount');
  amtCal('credit_amount');
});
 /*******************************************************************************/

 $(document).on("change",'#bank_id',function(event){
	 
	if($(this).val()){
		$.ajax({
        method: "POST",
        url: "{{route('ajaxBankInfo')}}",
        data: {"id":$(this).val(),"_token": "{{ csrf_token() }}"},
        cache: false,
        dataType: "json",
        success: function(data){
          if(data.id > 0){
			$('#account_code_cr').val(data.bank_code);
          }
          else{
			
            $('#bank_id').val(0);
			alert("Bank Code Does Not Exist Please Select Existing One");
			return false;
          }

        }
      });  
		
	}
 })
 //Account Code 
 $(document).on("change",'.ac_codes_id',function(event){
  des =  $( event.target ).closest('tr').find(".description");
  var des_val = $('option:selected',this).attr('data_des');
  des.val(des_val);
})

 /*******************************************************************************/
 /*
 $("#deposit_refund_amt").focusout(function(){


  if(parseFloat($("#deposit_refund_amount").val()) < parseFloat($("#deposit_refund_amt").val()))
  {
    $(".error").css("display","block").css("color","red");
    $("#submitBtn").prop('disabled',true);
  }
  else {
    $(".error").css("display","none");
    $("#submitBtn").prop('disabled',false);        
  }

});*/
	// Account code autocomplete
	$("input[name^='account_code']").autocomplete({
    source : '{!!URL::route('accountCodeAutocomplete')!!}',
    minlenght:2,
    autoFocus:true,
    change:function(e,ui){
      var parentId = $(this).parent().parent().attr('id');
      if (ui.item == null || ui.item == undefined) {
        $("#"+parentId).find('.account_id').val('');

      }
      else{
        $("#"+parentId).find('.account_id').val(ui.item.ids);
      }
    }

  });
	
 /*******************************************************************************/
 $('#receipts_generation_receipt_no').autocomplete({
  source : '{!!URL::route('depositReceiptAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#receipts_generation_receipt_no").val('');
      $('#create_build_span').hide();
      $('#vendor_name-error').show();
    }else {
     $('.unit-code1').hide();
     $('.unit-code2').show(); 
     $('.building-name1').hide();
     $('.building-name2').show();


     $('#tenant_contract_id').val(ui.item.code);  
     $('#building_name').val(ui.item.building_name);      
     $('#financial_building_code').val(ui.item.building_name);       
     $('#building_code').val(ui.item.building_code);   
     $('#buildings_id').val(ui.item.building_id);   
     $('.dim2').val(ui.item.building_name);   
     $('#unit_code').val(ui.item.unit_code); 
     $('#unit_no').val(ui.item.unit_no);  
     $('#tenant_name').val(ui.item.tenant_name); 
     $('#tenant_code').val(ui.item.tenant_code);
     $('#tenant_contract_no').val(ui.item.tenant_contract_no);
	 $('#deposit_amount_original').val(formatNumber(parseFloat(ui.item.receipts_generation_amt).toFixed(3)));
	 recalcNetRefundAmount();

	 $('#debit_amount_first').val(formatNumber(parseFloat(ui.item.receipts_generation_amt).toFixed(3))); 
     $('#credit_amount_first').val(0); 
     $('#credit_amount_second').val(formatNumber(parseFloat(ui.item.receipts_generation_amt).toFixed(3))); 
     $('#debit_amount_second').val(0); 
     $('#receipts_generation_id').val(ui.item.ids); 
     $('#deposit_refund_comment').text('Refund of Deposit Receipt :'+ui.item.building_name+'/'+ui.item.unit_code+'/'+ui.item.tenant_name+'@'+formatNumber(parseFloat(ui.item.receipts_generation_amt).toFixed(3)));	
     $('#debit_amount_total').val(formatNumber(parseFloat(ui.item.receipts_generation_amt).toFixed(3))); 
     $('#credit_amount_total').val(formatNumber(parseFloat(ui.item.receipts_generation_amt).toFixed(3))); 

     var from = convertDate(ui.item.tenant_contract_start_date);


     var to = convertDate(ui.item.tenant_contract_valid_to_date);

     $('#deposit_refund_valid_from').val(ui.item.tenant_contract_start_date); 
     $('#deposit_refund_valid_from1').val(from); 
     $('#deposit_refund_valid_to').val(ui.item.tenant_contract_valid_to_date); 
     $('#deposit_refund_valid_to1').val(to);

     $('#create_build_span').show();     
    }

  }
  
});

});
</script>
@endsection
@endsection
