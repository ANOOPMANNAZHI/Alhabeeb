@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->
<link rel="stylesheet" href="{{ asset('public/css/tokenize2.min.css')}}">



@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Add Rent Receipt</div>
    </div>
   {{ Breadcrumbs::render('addRentReceipt') }} 
  </div>
</div>
<form action="{{isset($rentReceiptInfo)?route('rentReceiptGeneration.update',$rentReceiptInfo->id):route('rentReceiptGeneration.store')}}" method="POST" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator" id="receipt_generation_form">
    {{csrf_field()}} 
    @if(isset($rentReceiptInfo)){{method_field('PUT')}}@endif
<div class="row">
  <div class="col-sm-12">
  

      
         <!--Agreement Section starts -->
         <div class="card card-box salesSearchBox">
         
                <h4>
                   <button type="button" title="Scan Qr Code" class="btn btn-circle btn-primary align-right scan" data-id="" datas-id="Tenant" data-toggle="modal" data-target="#myModal" data-placement="top" >Scan QrCode</button> 
                </h4>
            
            <div class="dataSearchBox">
                <div class="row">
                    <div class="w-100"></div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="simpleFormEmail">Mobile No<small class="textRed">*</small></label>
                                    <div class="p-relative">
                                        <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                                            <input type="text" name="mob_no" id="mob_no" required placeholder="Enter Mobile No" class="form-control" value="{{isset($rentReceiptInfo)?$rentReceiptInfo->tenantContractInfo->tenant->tenant_contact_no:''}}">
                                    </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="simpleFormEmail">Resident ID</label>
                                <div class="p-relative">
                                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                                    <input type="text" name="resident_card_id" id="resident_card_id" class="form-control" value="{{isset($rentReceiptInfo)?$rentReceiptInfo->tenantContractInfo->tenant->resident_id:''}}" placeholder="Enter Resident ID">
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <div class="sub-head">Tenant Details</div>
            <div class="dataSearchBox">    
                <div class="row">
                     <div class="col-sm-6">
                        <div class="form-group">
                             <label for="building_name">Building Name :  <small class="textRed">*</small></label>
                             <div class="p-relative">
                                <i class="fa fa-building icn-add" aria-hidden="true"></i>
                                <input type="text" name="building_name" id="building_name" class="form-control" value="{{ isset($rentReceiptInfo)?  old('building_name',$rentReceiptInfo->tenantContractInfo->building->building_name): old('building_name','')}}" placeholder="Enter Building Name">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="building_name">Building Code:  <small class="textRed">*</small></label>
                                <div class="p-relative">
                            <i class="fa fa-building icn-add" aria-hidden="true"></i> 
                          
                                <input type="text" name="building_code" id="building_code" class="form-control" value="{{ isset($rentReceiptInfo)?  old('building_code',$rentReceiptInfo->tenantContractInfo->building->building_code): old('building_code','')}}" readonly placeholder="Enter Building Code">
                                </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="unit_name">Unit No:  <small class="textRed">*</small></label>
                             <div class="p-relative">
                                <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                                <span id="unit_id"><input type="text" name="unit_name" id="unit_name" class="form-control" value="{{ isset($rentReceiptInfo)?  old('unit_name',$rentReceiptInfo->tenantContractInfo->unit->unit_no): old('unit_name','')}}" readonly placeholder="Unit No">
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="unit_code">Unit Code :  <small class="textRed">*</small></label>
                            <div class="p-relative">
                                <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                                <input type="text" name="unit_code" id="unit_code" class="form-control" value="{{ isset($rentReceiptInfo)?  old('unit_code',$rentReceiptInfo->tenantContractInfo->unit->unit_code): old('unit_code','')}}" readonly placeholder="Unit Code">
                            </div>
                        </div>
                    </div>
					<div class="col-sm-6">
                        <div class="form-group">
                            <label for="tenant_contract_no">Agreement No :  <small class="textRed">*</small></label>
                                 <div class="p-relative">
                                    <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>

                      <select class="form-control tenant_contract_no" name="tenant_contract_no" id="tenant_contract_no" >
                        @if(isset($rentReceiptInfo))
                            <option value="{{$rentReceiptInfo->tenant_contract_id}}">{{$rentReceiptInfo->tenantContractInfo->tenant_contract_no}}</option> 
                        @else
                            <option value="">Enter Mobile No Or Resident ID </option> 
                        
                        @endif                   
                    </select>  
                        </div>
                            
                        </div>
                    </div>


                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="tenant_contract_no">Agreement Date  :  <small class="textRed">*</small></label>
                                <div class="p-relative">
                                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="date" name="tenant_contract_date" id="tenant_contract_date" class="form-control" value="{{isset($rentReceiptInfo)?  old('tenant_contract_date',$rentReceiptInfo->tenantContractInfo->created_at->format('Y-m-d')): old('tenant_contract_date','')}}" readonly>
                                <input type="hidden" name="tenantContract" value="" id="tenantContract">    
                            
                        </div>
                    </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="tenant_contract_date">Agreement From :</label>
               <div class="p-relative">
                            <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                             <input type="date" name="tenant_contract_effective_date" id="tenant_contract_effective_date" class="form-control" readonly value="{{isset($rentReceiptInfo)?  old('tenant_contract_date',$rentReceiptInfo->tenantContractInfo->tenant_contract_effective_date->format('Y-m-d')): old('tenant_contract_effective_date','')}}">
                            
                           </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="tenant_contract_date">Agreement To: </label>
               <div class="p-relative">
                            <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                             <input type="date" name="tenant_contract_valid_to_date" id="tenant_contract_valid_to_date" class="form-control" readonly value="{{isset($rentReceiptInfo)?  old('tenant_contract_date',$rentReceiptInfo->tenantContractInfo->tenant_contract_valid_to_date->format('Y-m-d')): old('tenant_contract_valid_to_date','')}}">
                               
                           </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="unit_code">Tenant Name </label>
                            <div class="p-relative">
                                <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
                                <input type="text" name="tenant_name" id="tenant_name" class="form-control" value="{{isset($rentReceiptInfo)?$rentReceiptInfo->tenantContractInfo->tenant->tenant_name:''}}" readonly placeholder="Tenant Name ">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                           <label for="tenant_code">Tenant Code : </label>
                           <div class="p-relative">
                                <i class="fa fa-rebel icn-add" aria-hidden="true"></i>
                           <input type="text" name="tenant_code" id="tenant_code" class="form-control" value="{{isset($rentReceiptInfo)?$rentReceiptInfo->tenantContractInfo->tenant->tenant_code:''}}" readonly placeholder="Tenant Code">
                        </div>
                    </div>
                </div>
             </div>
            </div>
            <div class="sub-head">Payment Details &nbsp;&nbsp;&nbsp;  
                <a id="pdcLink" style="display: none;" target="_blank" href="">PDC Details | </a> 
                <a id="receiptLink" style="display: none;" target="_blank" href="">Payment Details</a>
            </div>
            <div class="dataSearchBox">    
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_receipt_no">Receipt No :  </label>
                            <div class="p-relative">
                                <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
                            <input type="text" name="receipts_generation_receipt_no" id="receipts_generation_receipt_no" class="form-control" readonly name="receipts_generation_receipt_no" value="{{isset($rentReceiptInfo)?$rentReceiptInfo->receipts_generation_receipt_no:$chequeReceiptNo}}">
                             </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_receipt_date">Receipt Date:  </label>
                            <div class="p-relative">
                                <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                            <input type="date" name="receipts_generation_receipt_date" id="" class="form-control" value="{{isset($rentReceiptInfo)?$rentReceiptInfo->receipts_generation_receipt_date->format('Y-m-d'):date('Y-m-d')}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_eff_from">Date Eff From :  </label>
                             <div class="p-relative">
                                <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                            <input type="date" name="receipts_generation_eff_from" id="receipts_generation_eff_from" class="form-control" readonly=""  value="{{isset($rentReceiptInfo)?$rentReceiptInfo->receipts_generation_eff_from->format('Y-m-d'):''}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_eff_to">Date Eff To:  </label>
                             <div class="p-relative">
                                <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                            <input type="date" name="receipts_generation_eff_to" id="receipts_generation_eff_to" class="form-control" value="{{isset($rentReceiptInfo)?$rentReceiptInfo->receipts_generation_eff_to->format('Y-m-d'):''}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                          <label for="receipts_generation_payment_method">Payment Method<small class="textRed">*</small></label>
                          <div class="p-relative">
                             <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
                            
                            <div class="form-control" > 
							<input type="radio" class="payment_method" name="receipts_generation_payment_method" {{!isset($rentReceiptInfo->receipts_generation_payment_method)?'checked':(($rentReceiptInfo->receipts_generation_payment_method==1)?'checked':'')}} required value="1" {{!isset($rentReceiptInfo->receipts_generation_payment_method)?'':'disabled'}}>Cheque	
                            <input type="radio" class="payment_method" name="receipts_generation_payment_method"  {{!isset($rentReceiptInfo->receipts_generation_payment_method)?'':(( $rentReceiptInfo->receipts_generation_payment_method==2)?'checked':'')}} required value="2" {{!isset($rentReceiptInfo->receipts_generation_payment_method)?'':'disabled'}}>
                                Cash
                            <input type="radio" class="payment_method" name="receipts_generation_payment_method"  {{!isset($rentReceiptInfo->receipts_generation_payment_method)?'':(( $rentReceiptInfo->receipts_generation_payment_method==3)?'checked':'')}} required value="3" {{!isset($rentReceiptInfo->receipts_generation_payment_method)?'':'disabled'}}>
                                Bank Transfer
                            </div>
                          </div>
                           <label id="receipts_generation_payment_method-error" class="error" for="receipts_generation_payment_method"></label>
                        </div>
                      </div>
                                     
                   
                    <!-- ---------  If cash selected Type div enable ----   -->
                    <div class="col-sm-6 type_div" {{!isset($rentReceiptInfo->receipts_generation_is_bounce_normal)?'style=display:none':(($rentReceiptInfo->receipts_generation_is_bounce_normal==2)?'style=display:block':'style=display:none')}} id ="type_div">
                        <div class="form-group">
                         <label for="pay_type">Type: <small class="textRed">*</small> </label>
                         <div class="p-relative">
                                <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
                    <div class="form-control" >
                    <!-- 1 - Normal, 2- Bounce -->                                   
                      <input type="radio" class="pay_type" name="pay_type" {{!isset($rentReceiptInfo->receipts_generation_is_bounce_normal)?'checked':(($rentReceiptInfo->receipts_generation_is_bounce_normal==1)?'checked':'')}}  value="1">
                      Normal
                     
                      <input type="radio" class="pay_type" name="pay_type"  {{!isset($rentReceiptInfo->receipts_generation_is_bounce_normal)?'':(( $rentReceiptInfo->receipts_generation_is_bounce_normal==2)?'checked':'')}}  value="2">
                      Bounce
                      <label id="pay_type-error" class="error" for="pay_type"></label>
                        </div>       
                         </div>
                    </div> </div>
                    <!-- ---------  If Bounce radio selected PDC Of that contract should list ----   -->
                    <div class="col-sm-6" {{!isset($rentReceiptInfo->receipts_generation_is_bounce_normal)?'style=display:none':(($rentReceiptInfo->receipts_generation_is_bounce_normal==2)?'style=display:block':'style=display:none')}} id ="pdc_bounce_div"> 
                        <div class="form-group">
                            
                            <label for="pay_type">Bounce Cheque No:  <small class="textRed">*</small> </label>
                                <div class="p-relative">
                                <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
                                <select name="pdc_bounce_cheque" class="form-control" id="pdc_bounce_cheque"  >
                                    

                                @if(isset($rentReceiptInfo->receipts_generation_is_bounce_normal) && $rentReceiptInfo->receipts_generation_is_bounce_normal==2)
                                    @foreach($contract_bounce_pdc as $bounce)

                                    <option value="{{$bounce->id}}" {{($bounce->id==$rentReceiptInfo->receipts_generation_is_pdc_bounce_id)?'SELECTED':''}}>{{$bounce->pdc_check_no}}
                                    </option>

                                    @endforeach
                                @endif
                              </select>   
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="bank_id">Bank <small class="textRed">*</small>: </label>
                                <div class="p-relative">
                                    <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>    
                                    <select name="bank_id" class="form-control" id="bank_id"  required>
                                    <option value="">Select Bank</option>
                                    @foreach($bankMaster as $name)
                                      <option value="{{$name->id}}"  {{ isset($rentReceiptInfo)? ((old('bank_id',$rentReceiptInfo->bank_id) == $name->id)? 'selected' : '') : ''}} data-foo="{{$name->bank_code}}">{{$name->bank_name}}</option>
                                    @endforeach
                              </select>      
                             </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="cheque_no" id="lbl_cheque">Cheque No :</label>
                            <div class="p-relative">
                                <!--<i class="fa fa-money icn-add cash-icon-fa" aria-hidden="true"></i>-->
								<span id="bank_code" class="cheq-code">
                                @php
                                $explArray = array();
                                if(isset($rentReceiptInfo)){
                                $explArray = explode('__',$rentReceiptInfo->receipts_generation_cheque_no);
                                }
                                @endphp
                                
                                <select name="pdc_bank_id"  id="pdc_bank_id" >
                                    <option value="">Code</option>
                                    @foreach($bankMaster as $name)
                                      <option value="{{$name->bank_code}}"  {{ isset($rentReceiptInfo)? ((old('pdc_bank_id',$name->bank_code) == $explArray[0])? 'selected' : '') : ''}} data-foo="{{$name->bank_code}}">{{$name->bank_code}}</option>
                                    @endforeach
                                </select> 
                                    
                            </span></b>
                                             
                            <span>
                             @if(count($explArray) == 1)
                                <input type="text"  name="cheque_no" id="cheque_no" class="form-control cheq-no-box"  value="{{ isset($rentReceiptInfo)?  old('cheque_no',$rentReceiptInfo->receipts_generation_cheque_no): old('cheque_no','')}}" placeholder="Cheque No" >
                             @else 
                                <input type="text"  name="cheque_no" id="cheque_no" class="form-control cheq-no-box"  value="{{ isset($rentReceiptInfo)?  old('cheque_no',$explArray[1]): old('cheque_no','')}}" placeholder="Cheque No" >
                              
                             @endif    
                         </span></h5>
                                </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_amt">Amount : </label>
                            <div class="p-relative">
                                <i class="fa fa-money icn-add" aria-hidden="true"></i> 
                            
                            <input type="text" class="form-control" name="receipts_generation_amt" onkeyup="FormatCurrency(this)" id="receipts_generation_amt" class="form-control allownumericwithdecimal read text-right" value="{{ isset($rentReceiptInfo)?  old('receipts_generation_amt',numberFormat($rentReceiptInfo->receipts_generation_amt)): old('receipts_generation_amt','')}}"  data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" placeholder="Enter Amount" >
                        </div>
                    </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_amt">Comment :  </label>
                            <div class="p-relative">
                            <i class="fa fa-comment icn-add" aria-hidden="true"></i> 

                            <span><textarea class="form-control" name="receipts_generation_description" id="receipts_generation_description" rows="3" cols="60" placeholder="Enter Comment" >{{ isset($rentReceiptInfo)?  old('receipts_generation_description',$rentReceiptInfo->receipts_generation_description): old('receipts_generation_description','')}}</textarea></span>
                            </div>
                            </div>
                    </div>                    
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_amt">Remark :  </label>
                            <div class="p-relative">
                            <i class="fa fa-comment icn-add" aria-hidden="true"></i><span>
							<textarea class="form-control" name="receipts_generation_remark" id="receipts_generation_remark" rows="3" cols="60" readonly="" placeholder="Enter Remark" >{{ isset($rentReceiptInfo)?  old('receipts_generation_remark',$rentReceiptInfo->receipts_generation_remark): old('receipts_generation_remark','')}}</textarea>	
							</span>
                        </div>
                        </div>
                    </div>
            </div>
            </div>
            <div class="sub-head">Financial Documentation</div>
            <div class="dataSearchBox">    
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                          <label for="finance_dim">Division:  </label>
                           <div class="p-relative">
                            <i class="fa fa-id-badge icn-add" aria-hidden="true"></i><span>
							<input type="text" readonly name="finance_dim_txt" id="finance_dim_txt" value="{{ isset($rentReceiptInfo)?  old('building_name',($rentReceiptInfo->tenantContractInfo->building->ax_division=='01')?'HO':'PLM'): old('building_name','')}}" class="form-control" placeholder="Division"></h5>
							<input type="hidden" readonly name="finance_dim" id="finance_dim" value="{{ isset($rentReceiptInfo)?  old('building_name',$rentReceiptInfo->tenantContractInfo->building->id): old('building_name','')}}" class="form-control" >
                        <!-- {{--<select name="finance_dim" required class="form-control" id="ax_division">
                            @foreach($dimList as $dim)
                                <option {{isset($rentReceiptInfo)? ((old('finance_dim',$rentReceiptInfo->finance_dim) == $dim->id)? 'selected' : '') : ''}} value="{{$dim->id}}">{{$dim->dim_value}}</option>
                            @endforeach
                        </select> --}}  -->
                        </div>
                        </div>       
                        </div>
                   
                    <div class="col-sm-6">
                        <div class="form-group">
                          <label for="building_dim">Building :  </label>
                           <div class="p-relative">
                            <i class="fa fa-building icn-add" aria-hidden="true"></i> 
                          
                          <input type="text" readonly name="building_dim" id="building_dim" value="{{ isset($rentReceiptInfo)?  old('building_name',$rentReceiptInfo->tenantContractInfo->building->building_name): old('building_name','')}}" class="form-control" placeholder="Building"><span></span></h5>
                        </div>
                        </div>
                    </div>
                    </div>
            </div>

            <div class="clearfix">
           
              <div class="w-100"></div>
                  <button type="submit" class="btn btn-primary" >SAVE</button>
          
           </div>
 
    </form>
<!--Payment ends -->
<div class="clearfix"></div>

<!--Remaining Invoices ends -->
</div>
</div>

<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script><!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script src="{{ asset('public/js/tokenize2.min.js') }}" ></script>
<script type="text/javascript">

$(document).ready(function() {
	@if(!isset($rentReceiptInfo) && $isYearChequeCorrect == false)
        alert("Current Year Is Not Match With the Sequence Year");
    @elseif(!isset($rentReceiptInfo) && $isYearCashCorrect == false)
        alert("Current Year Is Not Match With the Sequence Year");
    @endif
	
	jQuery.validator.addMethod("lessThanEqual", 
            function(value, element, params) {

                if (!/Invalid|NaN/.test(new Date(value))) {
                    return new Date(value) <= new Date($(params).val());
                }

                return isNaN(value) && isNaN($(params).val()) 
                    || (Number(value) <= Number($(params).val())); 
            },'Must be less than or equal to Tenant Contract End date .');
	$("#receipt_generation_form").validate({
        rules: {
        receipts_generation_eff_to: { lessThanEqual: "#tenant_contract_valid_to_date" 
        }
    }});
});
 function ajaxCall(mobilenumber = '',resident_card_id = '',building_id = '',unit_id = '')    {
    
    if(unit_id != '' || building_id != '')
    eventEl = '';
   
   if(resident_card_id != '')
    eventEl = 'resident_card_id';
   else if(mobilenumber != '')
     eventEl = 'mob_no';
   

     $.ajax({
        method: "POST",
        url: "{{route('receiptContractDetails')}}",
        data: { mobile: mobilenumber, resident_card_id:resident_card_id,
              building_id : building_id,unit_id : unit_id,
          "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
              
            //alert(result[1].tenant_contract_no);                           
            if(data != 0){
              var result = $.parseJSON(data);
//alert('dfssssssssssss');


                  if(result[1]){
                         if(result[1].resident_id != null){
                             $('#resident_card_id').val(result[1].resident_id);
                             $('#lbl_name').html('Resident ID');
                        }
                        else{
                            $('#resident_card_id').val(result[1].com_reg_no);
                             $('#lbl_name').html('Commercial Reg. No');
                        }
                      $('#mob_no').val(result[1].tenant_contact_no);
                  }


                  if(result[0].length > 1){  
 
                    var validator = $( "#receipt_generation_form" ).validate()
                    validator.resetForm();

                   

                    $('#tenant_contract_no').html('<option value="">Select Agreement No</option>');
                        $.each(result[0], function(key, value) {
                            $('#tenant_contract_no').append('<option value="'+ value['id'] +'">'+ value['tenant_contract_no'] +'</option>');
                        });
                    $('#building_code').val(result[2][0].building_code);     
                    $('#ax_division').val(result[2][0].ax_division);

                  }
                  else if(result[0].tenant_contract_no){

                    
                    var validator = $( "#receipt_generation_form" ).validate()
                    validator.resetForm();
                    if(result[1]){ 
                            if(result[1].resident_id != null){
                                 $('#resident_card_id').val(result[1].resident_id);
                                 $('#lbl_name').html('Resident ID');
                            }
                            else{
                                $('#resident_card_id').val(result[1].com_reg_no);
                                 $('#lbl_name').html('Commercial Reg. No');
                            }
                            $('#mob_no').val(result[1].tenant_contact_no);
                        }

                    if(result[4].msg_receipt != undefined){

                        clearInputData();
                        return false;
                    }
                   // $('#tenant_contract_no').html('<option value="">Select Agreement No</option>');
                                          
                     
                        $('#tenant_contract_no').html('<option value="'+ result[0].id +'">'+ result[0].tenant_contract_no +'</option>');
                        var agreeId =    result[0].id;
                        var agreeDt = new Date(result[0].created_at);
                        
                        var day = ("0" + agreeDt.getDate()).slice(-2);
                        var month = ("0" + (agreeDt.getMonth() + 1)).slice(-2);
                        var agreeDtVal = agreeDt.getFullYear()+"-"+(month)+"-"+(day) ;
						
						var agreeEff = new Date(result[0].tenant_contract_effective_date);
                        var agreeTo = new Date(result[0].tenant_contract_valid_to_date);
                        
                        var day = ("0" + agreeEff.getDate()).slice(-2);
                        var month = ("0" + (agreeEff.getMonth() + 1)).slice(-2);
                        var agreeEffDt = agreeEff.getFullYear()+"-"+(month)+"-"+(day) ;

                        var day = ("0" + agreeTo.getDate()).slice(-2);
                        var month = ("0" + (agreeTo.getMonth() + 1)).slice(-2);
                        var agreeToDt = agreeTo.getFullYear()+"-"+(month)+"-"+(day) ;


                        $('#tenant_contract_date').val(agreeDtVal);
						$('#tenant_contract_effective_date').val(agreeEffDt); 
                        $('#tenant_contract_valid_to_date').val(agreeToDt); 
                        $('#tenantContract').val(result[0].id);
                        if(result[2]){
						var fin_dim = result[2].ax_division;
						var fin_dimm = '';
						if(fin_dim == '02') fin_dimm = 'PLM';
						else    fin_dimm = 'HO';
                        $('#building_name').val(result[2].building_name);
                        
                        $('#ax_division').val(result[2].ax_division);
                        $('#building_dim').val(result[2].building_name);
						$('#finance_dim_txt').val(fin_dimm);
                        $('#building_code').val(result[2].building_code);
                          }
                        if(result[3]){
                          if(unit_id == ''){  
                                $('#unit_id').html('');
                                $('#unit_id').append('<input type="text" name="unit_name" id="unit_name" class="form-controll">'); 
                                $('#unit_name').val(result[3].unit_no);
                            }
                        $('#unit_code').val(result[3].unit_code);
                         }
                        if(result[1]){
                        $('#tenant_name').val(result[1].tenant_name);
                        $('#tenant_code').val(result[1].tenant_code);
                           }
                        if(result[4]){
                        $('#receipts_generation_eff_from').val(result[4].eff_from);
                        $('#receipts_generation_eff_to').val(result[4].eff_to);
                        }

						var term = '';
                        if(result[0].tenant_contract_payment_type==1){

                            term = 'Monthly';
                            var pay_term = 1;
                        }
                        else if(result[0].tenant_contract_payment_type==2){

                            term = 'BiMonthly';
                            var pay_term = 2;
                        }
                        else if(result[0].tenant_contract_payment_type==3){

                            term = 'Quaterly';
                            var pay_term = 3;
                        }
                        else if(result[0].tenant_contract_payment_type==6){

                            term = 'Half-Yearly';
                            var pay_term = 6;
                        }
                        else if(result[0].tenant_contract_payment_type==12){

                            term = 'Yearly';
                            var pay_term = 12;
                        }

                        var amtTerm = parseInt(pay_term) * parseFloat(result[0].tenant_contract_rent);
             
                        $("#receipts_generation_amt").val(formatNumber(amtTerm.toFixed(3)));
                        var eff_from = new Date(result[4].eff_from);

                        var day = ("0" + eff_from.getDate()).slice(-2);
                        var month = ("0" + (eff_from.getMonth() + 1)).slice(-2);
                        var eff_fromVal = (day)+"-"+(month)+"-"+eff_from.getFullYear() ;

                        var eff_to = new Date(result[4].eff_to);

                        var day = ("0" + eff_to.getDate()).slice(-2);
                        var month = ("0" + (eff_to.getMonth() + 1)).slice(-2);
                        var eff_toVal = (day)+"-"+(month)+"-"+eff_to.getFullYear() ;

                       // $('#receipts_generation_remark').val('Rent :'+result[2].building_name+'/'+result[3].unit_no+'/'+eff_fromVal+'/'+eff_toVal+'@'+formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3))+'/-PM');
                        $('#receipts_generation_remark').val('RENT FOR UNIT NO. '+result[3].unit_no+','+result[2].building_name+' FOR '+eff_fromVal+' TO '+eff_toVal+' @ '+parseFloat(result[0].tenant_contract_rent)+'/-PM');
                        if(result[1].length >= 1){
                         if(result[1].resident_id != null){
                             $('#resident_card_id').val(result[1].resident_id);
                             $('#lbl_name').html('Resident ID');
                            }
                            else{
                                 $('#resident_card_id').val(result[1].com_reg_no);
                                 $('#lbl_name').html('Commercial Reg. No');
                            }
                          $('#mob_no').val(result[1].tenant_contact_no);
                       }

                       // $('#pdcLink').attr('href','{{url("tenant-pdc-view/tenant-contract")}}/'+agreeId); 
					   $('#pdcLink').attr('href','{{url("/")}}'+'/pdc/'+agreeId+'/edit');
                        $('#receiptLink').attr('href','{{url("/")}}'+'/receiptsAgreementViewList/rentReceiptGeneration/'+agreeId+'/rent');
                        $('#pdcLink').show();
                        $('#receiptLink').show();
                        $('.payment_method').prop('checked', false);
                  }

                  if(result[3].length > 1  && unit_id == ''){

                      $('#unit_id').html('');
                      var unit_name_txt = '<select class="form-control" name="unit_name" id="unit_name" class="form-controll"><option value="">'+ 'Select Unit' +'</option>';
                          $.each(result[3], function(key, value) {                            
                            unit_name_txt +=  '<option value="'+ value.id +'" '+ '>'+ value.unit_no +'</option>';
                            });
                          unit_name_txt +=  '</select>';
                      $('#unit_id').html(unit_name_txt);    

                  }else if(result[3]){
                     if(unit_id == ''){  
                        $('#unit_id').html('');
                        $('#unit_id').append('<input type="text"  class="form-control"  name="unit_name" id="unit_name" >');
                        $('#unit_name').val(result[3].unit_no);
                     }
                        $('#unit_code').val(result[3].unit_code);
                   }

                  
                  $('#registerd_mob_no-error').hide();
                  $('.payment_method').prop('checked', false);
                           
            }else{  

                $('#mob_no').val("");
                $('#resident_card_id').val("");
                $('#tenant_contract_no').html('<option value="">'+ 'Enter Mobile No Or Resident ID ' +'</option>');
                
                
                /*$('#registerd_mob_no-error').show();*/
                var validator = $( "#receipt_generation_form" ).validate();
              //  var validator = $( "#receipt_generation_form" ).validate()
                    validator.resetForm();

                if(eventEl == 'mob_no'){    
                    validator.showErrors({
                      "mob_no": "It's Not A Registered Mobile Number,Please Try Again!"
                    });
                }
                else if(eventEl == 'resident_card_id'){
                    validator.showErrors({
                        "resident_card_id": "It's Not A Registered Resident Id,Please Try Again!"
              
                  });
                }

               $('#pdcLink').hide();
               $('#receiptLink').hide();
            }     

        }    

      });
 }


 $('#building_name').autocomplete({
      source : '{!!URL::route('receiptBuildingAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        var clearTxt = ClearInputDate('building_name');
            if (ui.item != null || ui.item != undefined) {
              // $('#building_text_id').val(ui.item.ids);
              // $('#buildings').val(ui.item.ids);
              building_id = ui.item.ids;
              ajaxCall('','',building_id);
            }
        }

  }); 

  
$(document).on('change',"#unit_name",function(event){
    var unit_id = $(this).val();
    ajaxCall('','','',unit_id);
});

$("#receipts_generation_eff_from, #receipts_generation_eff_to").on('change',function(event){
    
	var eff_toVal_change = changeDate($('#receipts_generation_eff_to').val());
	var eff_fromVal_change = changeDate($('#receipts_generation_eff_from').val());
    var amtTerm_change = $('#receipts_generation_amt').val();
    var building_change = $('#building_name').val();
    var unit_change = $('#unit_name option:selected').text(); 
	if(!unit_change)
        var unit_change = $('#unit_name').val();
    var term = $('#receipts_generation_remark').val().split('@');
    var leng = term.length-1;
	 var amtss = term[leng].split('/');
    var leng1 = amtss.length-1;
   // $('#receipts_generation_remark').val('Rent :'+building_change+'/'+unit_change+'/'+eff_fromVal_change+'/'+eff_toVal_change+'@'+amtss[leng1-1]+'/-PM');
   $('#receipts_generation_remark').val('RENT FOR UNIT NO. '+unit_change+','+building_change+' FOR '+eff_fromVal_change+' TO '+eff_toVal_change+'@'+amtss[leng1-1]+'/-PM');
});

$("#mob_no, #resident_card_id").on('change',function(event){
    event.preventDefault();  
    var eventEl      = $(this).attr('id');
    var mobilenumber = $('#mob_no').val();
    var resident_card_id = $('#resident_card_id').val();
    var mobile_size = mobilenumber.length;
    
    var selected = "";
    var uselected = "";
    var oselected = "";
    if(eventEl == 'resident_card_id' || eventEl == 'mob_no'){

        $('#mob_no').val('');
        $('#resident_card_id').val('');      
        $("#receipts_generation_amt").val(''); 
        $('#tenant_contract_date').val('');
		$('#tenant_contract_effective_date').val(''); 
        $('#tenant_contract_valid_to_date').val(''); 
        $('#tenantContract').val(''); 
        $('#building_name').val(''); 
        $('#building_dim').val(''); 
        $('#finance_dim_txt').val('');
		$('#finance_dim').val('');
        $('#building_code').val(''); 
        $('#unit_name').val(''); 
        $('#unit_code').val(''); 
        $('#tenant_name').val(''); 
        $('#tenant_code').val(''); 
        $('#tenant_contract_no').prop('selectedIndex',0);
        $('#ax_division').prop('selectedIndex',1);
        
        $('#bank_id').prop('selectedIndex',0);
        $('#bank_code').text('');
        $('#cheque_no').val('');
        $('#receipts_generation_eff_from').val('');
        $('#receipts_generation_eff_to').val('');
        $('#receipts_generation_remark').val('')
        $("#cheque_no").prop("disabled", true);  
        $('.type_div').hide();
        $("#pdc_bounce_div").hide();
        $('.payment_method').prop('checked', false);
		$('#receipts_generation_remark').val('');
		$('#receipts_generation_amt').val('');
        (eventEl == 'resident_card_id')?mobilenumber='':resident_card_id='';
        
        ajaxCall(mobilenumber,resident_card_id);
     
    }

});
$(document).on('change','#tenant_contract_no',function(){  
    var contractId   = $(this).val();
    //var mobilenumber = $('#mob_no').val();
    //var resident_card_id = $('#resident_card_id').val();
    //var mobile_size = mobilenumber.length;
    if(contractId){
    $.ajax({
        method: "POST",
        url: "{{route('receiptContractDetailsByBuildingUnit')}}",
        data: {contractId:contractId, "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
            if(data != 0){
                var result = $.parseJSON(data);
				if(result[4].msg_receipt != undefined){

					$('#mob_no').val('');
                    $('#resident_card_id').val('');
                    $("#receipts_generation_amt").val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)));
                    $('#tenant_contract_date').val('');
					$('#tenant_contract_effective_date').val(''); 
                    $('#tenant_contract_valid_to_date').val(''); 
                    $('#tenantContract').val('');
                    $('#building_name').val('');
                    $('#ax_division').prop('selectedIndex',1);
                    $('#building_dim').val('');
                    $('#building_code').val('');
					$('#finance_dim_txt').val('');
					$('#finance_dim').val('');
                    $('#unit_id').html('');
                    $('#unit_id').append('<input type="text" name="unit_name" id="unit_name" class="form-control">'); 
                    $('#unit_name').val('');
                    $('#tenant_name').val('');
                    $('#tenant_code').val('');
                    $('#unit_code').val('');
                    $('#receipts_generation_eff_from').val('');
                    $('#receipts_generation_eff_to').val('');
                    $('#pdcLink').hide();
                    $('#receiptLink').hide();
                    $('.type_div').hide();
                    $("#pdc_bounce_div").hide();
                    $('.pay_type').prop('checked', false)
					$('#receipts_generation_remark').val('');
					$('#receipts_generation_amt').val('');
                    $('.payment_method').prop('checked', false);
                    alert(result[4].msg_receipt);
					return false;
				}
				
                var agreeDt = new Date(result[0].created_at);
				var fin_dim = result[2].ax_division;
				var fin_dimm = '';
				if(fin_dim == '02') fin_dimm = 'PLM';
				else    fin_dimm = 'HO';
				var day = ("0" + agreeDt.getDate()).slice(-2);
                var month = ("0" + (agreeDt.getMonth() + 1)).slice(-2);
                var agreeDtVal = agreeDt.getFullYear()+"-"+(month)+"-"+(day) ;
				
				var agreeEff = new Date(result[0].tenant_contract_effective_date);
                var agreeTo = new Date(result[0].tenant_contract_valid_to_date);
                        
				var day = ("0" + agreeEff.getDate()).slice(-2);
				var month = ("0" + (agreeEff.getMonth() + 1)).slice(-2);
				var agreeEffDt = agreeEff.getFullYear()+"-"+(month)+"-"+(day) ;

				var day = ("0" + agreeTo.getDate()).slice(-2);
				var month = ("0" + (agreeTo.getMonth() + 1)).slice(-2);
				var agreeToDt = agreeTo.getFullYear()+"-"+(month)+"-"+(day) ;
                
                $('#tenant_contract_date').val(agreeDtVal);
				$('#tenant_contract_effective_date').val(agreeEffDt); 
                $('#tenant_contract_valid_to_date').val(agreeToDt); 
                $('#tenantContract').val(result[0].id); 
                $('#building_name').val(result[2].building_name);
                $('#building_dim').val(result[2].building_name);
				$('#finance_dim_txt').val(fin_dimm);
				$('#finance_dim').val(result[2].ax_division);
                $('#building_code').val(result[2].building_code);
                $('#ax_division').val(result[2].ax_division);
                $('#unit_id').html('');
                $('#unit_id').append('<input type="text" name="unit_name" id="unit_name" class="form-control">'); 
                $('#unit_name').val(result[3].unit_no);
                $('#unit_code').val(result[3].unit_code);
                $('#tenant_name').val(result[1].tenant_name);
                $('#mob_no').val(result[1].tenant_contact_no);
                $('#resident_card_id').val(result[1].resident_id);
                $('#tenant_code').val(result[1].tenant_code);
                $('#receipts_generation_eff_from').val(result[4].eff_from);
                $('#receipts_generation_eff_to').val(result[4].eff_to);
                var term = '';
				//table payment_method - column - payment_method_index
                if(result[0].tenant_contract_payment_type==1){

                    term = 'Monthly';
                    var pay_term = 1;
                }
                else if(result[0].tenant_contract_payment_type==2){

                    term = 'BiMonthly';
                    var pay_term = 2;
                }
                else if(result[0].tenant_contract_payment_type==3){

                    term = 'Quaterly';
                    var pay_term = 3;
                }
                else if(result[0].tenant_contract_payment_type==4){

                    term = 'Half-Yearly';
                    var pay_term = 6;
                }
                else if(result[0].tenant_contract_payment_type==5){

                    term = 'Yearly';
                    var pay_term = 12;
                }
                var amtTerm = parseInt(pay_term) * parseFloat(result[0].tenant_contract_rent);
                $("#receipts_generation_amt").val(amtTerm.toFixed(3));
                var eff_from = new Date(result[4].eff_from);

                var day = ("0" + eff_from.getDate()).slice(-2);
                var month = ("0" + (eff_from.getMonth() + 1)).slice(-2);
                var eff_fromVal = (day)+"-"+(month)+"-"+eff_from.getFullYear() ;

                var eff_to = new Date(result[4].eff_to);

                var day = ("0" + eff_to.getDate()).slice(-2);
                var month = ("0" + (eff_to.getMonth() + 1)).slice(-2);
                var eff_toVal = (day)+"-"+(month)+"-"+eff_to.getFullYear() ;

                //$('#receipts_generation_remark').val('Rent :'+result[2].building_name+'/'+result[3].unit_no+'/'+eff_fromVal+'/'+eff_toVal+'@'+formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3))+'/-PM');
                $('#receipts_generation_remark').val('RENT FOR UNIT NO. '+result[3].unit_no+','+result[2].building_name+' FOR '+eff_fromVal+' TO '+eff_toVal+'@'+parseFloat(result[0].tenant_contract_rent)+'/-PM');
				$('#pdcLink').attr('href','{{url("/")}}'+'/pdc/'+contractId+'/edit');
                $('#receiptLink').attr('href','{{url("/")}}'+'/receiptsAgreementViewList/rentReceiptGeneration/'+contractId+'/rent');
				$('#pdcLink').show();
				$('#receiptLink').show();
                $('.type_div').hide();
                $("#pdc_bounce_div").hide();    
                $('.pay_type').prop('checked', false)
                $('.payment_method').prop('checked', false);
            }
        }
    });
    }
    else{
        $("#receipts_generation_amt").val(''); 
        $('#tenant_contract_date').attr('readonly',false);
        $('#tenant_contract_date').val('yyyy/mm/dd');
        $('#tenant_contract_date').attr('readonly',true);
        $('#tenantContract').val(''); 
		$('#tenant_contract_effective_date').val(''); 
        $('#tenant_contract_valid_to_date').val(''); 
        $('#building_name').val(''); 
        $('#building_dim').val(''); 
        $('#building_code').val(''); 
        $('#unit_name').val(''); 
        $('#unit_code').val(''); 
        $('#tenant_name').val(''); 
        $('#tenant_code').val(''); 
        $('#tenant_contract_no').prop('selectedIndex',0);
        $('#ax_division').prop('selectedIndex',1);
		$('#finance_dim').val(''); 
		$('#finance_dim_text').val(''); 
        $('#bank_id').prop('selectedIndex',0);
        $('#bank_code').text('');
        $('#cheque_no').val('');
        $('#receipts_generation_remark').val('')
        $('#receipts_generation_eff_from').val('');
		$('#receipts_generation_eff_to').val('');
        $("#cheque_no").prop("disabled", true);  
        $('#pdcLink').hide();
        $('#receiptLink').hide();
        $('.type_div').hide();
        $("#pdc_bounce_div").hide();
        $('.pay_type').prop('checked', false);
        $('.payment_method').prop('checked', false);
		$('#receipts_generation_amt').val('');
		$('#receipts_generation_remark').val('');
    }
});

$(document).on('click','.payment_method',function(){  
    
    var payment_method = $(this).val();
 
    if(payment_method == 1){ // Cheque
            $("#cheque_no").prop("disabled", false);
            $('#cheque_no').attr("required", true);
			$('#pdc_bank_id').attr("disabled", false);
            $('#pdc_bank_id').attr("required", true);
            $('.pay_type').prop('checked', false)
            $('.type_div').hide();
            $("#pdc_bounce_cheque").val('');
            $("#pdc_bounce_div").hide();

            $("#lbl_cheque").html("Cheque No :");
             $("#cheque_no").attr("placeholder","Cheque no");

			@if(isset($chequeReceiptNo))
				$("#receipts_generation_receipt_no").val("{{$chequeReceiptNo}}")	
            @endif	
            @if(!isset($rentReceiptInfo) && $isYearChequeCorrect == false)	
                alert("Current Year Is Not Match With the Sequence Year");	
            @elseif(!isset($rentReceiptInfo) && $isYearCashCorrect == false)	
                alert("Current Year Is Not Match With the Sequence Year");	
            @endif
    }
    else if(payment_method == 2){ // Cash
            $('.type_div').show();
            $('#cheque_no').val('');
            $("#cheque_no").prop("disabled", true);
            $('#cheque_no').removeAttr('required');    
			$('#pdc_bank_id').prop('selectedIndex',0);
			$('#pdc_bank_id').prop("disabled", true);
            $('#pdc_bank_id').removeAttr('required');
            $('.pay_type').prop('checked', false)
            $("#pdc_bounce_cheque").val('');
            $("#pdc_bounce_div").hide();
            
            @if(isset($cashReceiptNo))	
                $("#receipts_generation_receipt_no").val("{{$cashReceiptNo}}");	
            @endif	
            @if(!isset($rentReceiptInfo) && $isYearChequeCorrect == false)	
                alert("Current Year Is Not Match With the Sequence Year");	
            @elseif(!isset($rentReceiptInfo) && $isYearCashCorrect == false)	
                alert("Current Year Is Not Match With the Sequence Year");	
            @endif
    }
    else if(payment_method == 3){ // Bank Transfer

            $("#cheque_no").prop("disabled", false);
            $('#cheque_no').attr("required", true);
            $('#pdc_bank_id').attr("disabled", false);
            $('#pdc_bank_id').attr("required", true);
            $('.pay_type').prop('checked', false)
             $('.type_div').show();
            $("#pdc_bounce_cheque").val('');
            $("#pdc_bounce_div").hide();

            $("#lbl_cheque").html("Transaction No :");
            $("#cheque_no").attr("placeholder","Transaction no");

            @if(isset($chequeReceiptNo))
                $("#receipts_generation_receipt_no").val("{{$chequeReceiptNo}}")    
            @endif  
            @if(!isset($rentReceiptInfo) && $isYearChequeCorrect == false)  
                alert("Current Year Is Not Match With the Sequence Year");  
            @elseif(!isset($rentReceiptInfo) && $isYearCashCorrect == false)    
                alert("Current Year Is Not Match With the Sequence Year");  
            @endif
    }
         
});
$(document).on('click','.pay_type',function(){  
    
    var cash_type = $(this).val();
    
    if(cash_type == 1){ // Normal
            $("#pdc_bounce_cheque").removeAttr('required');
            $("#pdc_bounce_div").hide();
    }
    else if(cash_type == 2){ // Bounce

        var contractId = $("#tenant_contract_no").val();
        $("#pdc_bounce_cheque").attr('required','required');
        $("#pdc_bounce_cheque-error").hide();
        $("#pdc_bounce_cheque").val('');
        if(contractId ==''){ 
            
            $("#pdc_bounce_cheque").html('<option value=""> Select the Agreement No</option>')
        }  
        else{
   
            $.ajax({
                method: "POST",
                    url: "{{route('pdcNoListWithContractNo')}}",
                    data: { contractId: contractId,
                  "_token" : $('meta[name="csrf-token"]').attr('content')},
                    success: function(data){
                        var parsed_data = JSON.parse(data);
                        $('#pdc_bounce_cheque').html(parsed_data.option_html);
                    } 
            });
            
        }
        $("#pdc_bounce_div").show();
    }
        
});

$(document).on('click','.scan',function(){       

    $.ajax({
      method: 'POST', // Type of response and matches what we said in the route
      url: "{{route('keyScanForPaymentReceipt')}}", // This is the url we gave in the route
      data: {"_token": "{{ csrf_token() }}"}, // a JSON object to send back
      success: function(response){ // What to do if we succeed
          $("#myModal").html(response); 
      },
    });
    return true; 
         
});

/***************************************************************************************/
$(document).ready(function() {
   $("#myModal").on("hidden.bs.modal", function(){
        $("#myModal").html("");
        $(this).removeData('bs.modal');
    }); 


    
/***************************************************************************************/
});
/********************* convert date into dd-mm-yyyy****************************/
      function changeDate(dateString){
        var p = dateString.split(/\D/g)
        return [p[2],p[1],p[0] ].join("-")
    }
function ClearInputDate(att_el){
 
    $('#mob_no').val('');
    $('#resident_card_id').val('');
    $("#receipts_generation_amt").val();
    $('#tenant_contract_date').val('');
    $('#tenantContract').val('');
    $('#tenant_contract_effective_date').val(''); 
    $('#tenant_contract_valid_to_date').val(''); 

    if(att_el != 'building_name')
        $('#building_name').val('');

    $('#building_dim').val('');
    $('#building_code').val('');
    $('#unit_id').html('');
    $('#unit_id').html('<input type="text" name="unit_name" id="unit_name" class="form-controll">'); 
    $('#tenant_contract_no').html('<option value="">Enter Mobile No Or Resident ID </option>'); 
    $('#unit_name').val('');
    $('#tenant_name').val('');
    $('#tenant_code').val('');
    $('#unit_code').val('');
	$('#pdc_bank_id').prop('selectedIndex',0);
    $('#receipts_generation_eff_from').val('');
    $('#receipts_generation_eff_to').val('');
    $('#ax_division').prop('selectedIndex',0);
    $('#pdcLink').hide();
    $('#receiptLink').hide();
    $('.type_div').hide();
    $("#pdc_bounce_div").hide();
	$('#receipts_generation_remark').val('');
	$('#receipts_generation_amt').val('');
    $('.payment_method').prop('checked', false);
    return true;
}
</script>
@endsection
