@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->
<link rel="stylesheet" href="{{ asset('public/css/tokenize2.min.css')}}">



@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Add General Receipt</div>
  </div>
  {{ Breadcrumbs::render('addGeneralReceipt') }}
</div>
</div>
<form action="{{isset($generalReceiptInfo)?route('updateGeneralReceiptAction',$generalReceiptInfo->id):route('addGeneralReceiptAction')}}" method="POST" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator" id="receipt_generation_form" autocomplete="off">
    {{csrf_field()}}
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
                        <input type="text" name="mob_no" id="mob_no" required placeholder="Enter Mobile No" class="form-control" value="{{isset($generalReceiptInfo)?$generalReceiptInfo->tenantContractInfo->tenant->tenant_contact_no:''}}">
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Resident ID</label>
                    <div class="p-relative">
                        <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                        <input type="text" name="resident_card_id" id="resident_card_id" class="form-control" value="{{isset($generalReceiptInfo)?$generalReceiptInfo->tenantContractInfo->tenant->resident_id:''}}" placeholder="Enter Resident ID">
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
                    <label for="tenant_contract_no">Agreement No :  <small class="textRed">*</small></label>
                    <div class="p-relative">
                        <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>

                        <select class="form-control tenant_contract_no" name="tenant_contract_no" id="tenant_contract_no" >
                            @if(isset($generalReceiptInfo))
                            <option value="{{$generalReceiptInfo->tenant_contract_id}}">{{$generalReceiptInfo->tenantContractInfo->tenant_contract_no}}</option> 
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
                        <input type="date" name="tenant_contract_date" id="tenant_contract_date" class="form-control" value="{{isset($generalReceiptInfo)?  old('tenant_contract_date',$generalReceiptInfo->tenantContractInfo->created_at->format('Y-m-d')): old('tenant_contract_date','')}}" readonly>
                        <input type="hidden" name="tenantContract" value="" id="tenantContract">    
                        
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                   <label for="building_name">Building Name :  <small class="textRed">*</small></label>
                   <div class="p-relative">
                    <i class="fa fa-building icn-add" aria-hidden="true"></i>
                    <input type="text" name="building_name" id="building_name" class="form-control" value="{{ isset($generalReceiptInfo)?  old('building_name',$generalReceiptInfo->tenantContractInfo->building->building_name): old('building_name','')}}" placeholder="Enter Building Name">
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="building_name">Building Code:  <small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-building icn-add" aria-hidden="true"></i>
                    <input type="text" name="building_code" id="building_code" class="form-control" value="{{ isset($generalReceiptInfo)?  old('building_code',$generalReceiptInfo->tenantContractInfo->building->building_code): old('building_code','')}}" readonly placeholder="Enter Building Code">
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="unit_name">Unit No:  <small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                    <span id="unit_id"><input type="text" name="unit_name" id="unit_name" class="form-control" value="{{ isset($generalReceiptInfo)?  old('unit_name',$generalReceiptInfo->tenantContractInfo->unit->unit_no): old('unit_name','')}}" readonly placeholder="Unit No">
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="unit_code">Unit Code :  <small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                    <input type="text" name="unit_code" id="unit_code" class="form-control" value="{{ isset($generalReceiptInfo)?  old('unit_code',$generalReceiptInfo->tenantContractInfo->unit->unit_code): old('unit_code','')}}" readonly placeholder="Unit Code">
                </div>
            </div>
        </div>


        <div class="col-sm-6">
            <div class="form-group">
                <label for="unit_code">Tenant Name </label>
                <div class="p-relative">
                    <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
                    <input type="text" name="tenant_name" id="tenant_name" class="form-control" value="{{isset($generalReceiptInfo)?$generalReceiptInfo->tenantContractInfo->tenant->tenant_name:''}}" readonly placeholder="Tenant Name ">
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
             <label for="tenant_code">Tenant Code : </label>
             <div class="p-relative">
                <i class="fa fa-rebel icn-add" aria-hidden="true"></i>
                <input type="text" name="tenant_code" id="tenant_code" class="form-control" value="{{isset($generalReceiptInfo)?$generalReceiptInfo->tenantContractInfo->tenant->tenant_code:''}}" readonly placeholder="Tenant Code">
            </div>
        </div>
    </div>
</div>
</div>
<div class="sub-head">Payment Details</div>
<div class="dataSearchBox">    
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="receipts_generation_receipt_no">Receipt No :  </label>
                <div class="p-relative">
                    <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>                          
                    <input type="text" name="receipts_generation_receipt_no" id="receipts_generation_receipt_no" value="{{isset($generalReceiptInfo)?$generalReceiptInfo->receipts_generation_receipt_no :$chequeReceiptNo}}" class="form-control" readonly="" name="receipts_generation_receipt_no">
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="receipts_generation_receipt_no">Receipt Date :  </label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>     
                    <input type="date" name="receipts_generation_receipt_date" value="{{isset($generalReceiptInfo)?$generalReceiptInfo->receipts_generation_receipt_date->format('Y-m-d'):date('Y-m-d')}}"   id="" class="form-control">
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
              <label for="receipts_generation_payment_method">Payment Method<small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
               
               <div class="form-control" > 	
                <input type="radio" class="payment_method" name="receipts_generation_payment_method" 	
                {{!isset($generalReceiptInfo->receipts_generation_payment_method)?'':'disabled'}}	
                {{!isset($generalReceiptInfo->receipts_generation_payment_method)?'':(($generalReceiptInfo->receipts_generation_payment_method==1)?'checked':'checked')}} required value="1">Cheque	
                	
                <input type="radio" class="payment_method" name="receipts_generation_payment_method"  	
                  {{!isset($generalReceiptInfo->receipts_generation_payment_method)?'':'disabled'}}	
                    {{!isset($generalReceiptInfo->receipts_generation_payment_method)?'':(( $generalReceiptInfo->receipts_generation_payment_method==2)?'checked':'')}} required value="2">	
                  Cash 
              </div>
          </div>
          <label id="receipts_generation_payment_method-error" class="error" for="receipts_generation_payment_method"></label>
          
      </div>
  </div>
  
  <div class="col-sm-6">
    <div class="form-group">
        <label for="bank_id">Bank : <small class="textRed">*</small> </label>
        <div class="p-relative">
            <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>     
            <select name="bank_id" class="form-control" id="bank_id" required>
                <option value="">Select Bank</option>
                @foreach($bankMaster as $name)
                <option value="{{$name->id}}"  {{ isset($generalReceiptInfo)? ((old('bank_id',$generalReceiptInfo->bank_id) == $name->id)? 'selected' : '') : ''}} data-foo="{{$name->bank_code}}">{{$name->bank_name}}</option>
                @endforeach
            </select>      
            
        </div>
    </div>
</div>
<div class="col-sm-6">
                        <div class="form-group">
                            <label for="cheque_no">Cheque No :</label>
                            <div class="p-relative">
                                <!--<i class="fa fa-money icn-add cash-icon-fa" aria-hidden="true"></i>-->
                <span id="bank_code" class="cheq-code">
                                @php
                                $explArray = array();
                                if(isset($generalReceiptInfo)){
                                $explArray = explode('__',$generalReceiptInfo->receipts_generation_cheque_no);
                                }
                                @endphp
                                
                                <select name="pdc_bank_id"  id="pdc_bank_id" >
                                    <option value="">Code</option>
                                    @foreach($bankMaster as $name)
                                      <option value="{{$name->bank_code}}"  {{ isset($generalReceiptInfo)? ((old('pdc_bank_id',$name->bank_code) == $explArray[0])? 'selected' : '') : ''}} data-foo="{{$name->bank_code}}">{{$name->bank_code}}</option>
                                    @endforeach
                                </select> 
                                    
                            </span></b>
                                             
                            <span>
                             @if(count($explArray) == 1)	
                                <input type="text"  name="cheque_no" id="cheque_no" class="form-control cheq-no-box"  value="{{ isset($generalReceiptInfo)?  old('cheque_no',$generalReceiptInfo->receipts_generation_cheque_no): old('cheque_no','')}}" placeholder="Cheque No" {{(isset($generalReceiptInfo->receipts_generation_payment_method)?(($generalReceiptInfo->receipts_generation_payment_method==1)?'':'readonly'):'')}} >	
                             @else 	
                                <input type="text"  name="cheque_no" id="cheque_no" class="form-control cheq-no-box"  value="{{ isset($generalReceiptInfo)?  old('cheque_no',$explArray[1]): old('cheque_no','')}}" placeholder="Cheque No" {{(isset($generalReceiptInfo->receipts_generation_payment_method)?(($generalReceiptInfo->receipts_generation_payment_method==1)?'':'readonly'):'')}} >	
                              	
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
            <input type="text" class="form-control" name="receipts_generation_amt" onkeyup="FormatCurrency(this)" id="receipts_generation_amt" class="form-control allownumericwithdecimal read text-right" value="{{ isset($generalReceiptInfo)?  old('receipts_generation_amt',numberFormat($generalReceiptInfo->receipts_generation_amt)): old('receipts_generation_amt','')}}"  data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" placeholder="Enter Amount" >
        </div>
    </div>
</div>
<div class="col-sm-6">
    <div class="form-group">
        <label for="receipts_generation_amt">Comment :  </label> <div class="p-relative">
            <i class="fa fa-comment icn-add" aria-hidden="true"></i><span><textarea class="form-control" name="receipts_generation_description" id="receipts_generation_description" rows="3" cols="60" placeholder="Enter Comment" >{{ isset($generalReceiptInfo)?  old('receipts_generation_description',$generalReceiptInfo->receipts_generation_description): old('receipts_generation_description','')}}</textarea></span>
        </div>
    </div>
</div>                    
<div class="col-sm-6">
    <div class="form-group">
        <label for="receipts_generation_amt">Remark :  </label>
        <div class="p-relative">
            <i class="fa fa-comment icn-add" aria-hidden="true"></i><span>

                <textarea class="form-control" name="receipts_generation_remark" id="receipts_generation_remark" rows="3" cols="60" readonly="" placeholder="Enter Remark" >{{ isset($generalReceiptInfo)?  old('receipts_generation_remark',$generalReceiptInfo->receipts_generation_remark): old('receipts_generation_remark','')}}</textarea>  
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
                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="ax_division"  name="finance_dim" required>
                  <option value="">Select Division</option>
                  @foreach($dimList as $dim)
                   <option  {{(old('building_type_id', isset($generalReceiptInfo)?  $generalReceiptInfo->finance_dim : 0) == $dim->id) ? 'selected' : '' }} value="{{$dim->id}}">{{$dim->dim_value}}</option>
                  @endforeach                  
                </select>
            </div>
            
        </div>
    </div>

    <div class="col-sm-6">
        <div class="form-group">
            <label for="building_dim">Building :  </label>
            <div class="p-relative">
                <i class="fa fa-building icn-add" aria-hidden="true"></i>
                <input type="text" name="building_dim" id="building_dim" value="{{ isset($generalReceiptInfo)?  old('building_name',$generalReceiptInfo->tenantContractInfo->building->building_name): old('building_name','')}}"  class="form-control"  placeholder="Building">
            </div>
        </div>
    </div>
</div>
</div>
<div class="sub-head">Narration</div>
<div class="dataSearchBox">    
    <div class="row">
     
        <div class="col-lg-12">
            <textarea name="general_narration" class="form-control" placeholder="Enter Narration">{{ isset($generalReceiptInfo)?$generalReceiptInfo->general_receipt_narration:''}}</textarea><span></span>
            
        </div>
    </div>
    
</div>
<div class="sub-head">Distribution Details</div>
<div class="clearfix" style="padding: 10px 20px"></div>         
<div class="row">
    <div class="col-sm-12">
      <div class="table-wrap">
        <div class="table-responsive">
          
            <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                      <th>
                        <a id="add_details" class="add_details">
                          <h5><strong><i class="fa fa-plus" aria-hidden="true" style="color:#093879;"></i></strong></h5>
                      </a>
                  </th>
                  <th>Account Code</th>
                  <th>Type</th>
                  <th>Dr. Amt</th>
                  <th>Cr. Amt</th>
                  <th>Narration</th>
              </tr> 
          </thead>
          <tbody id="acc_line_list">
            @if(isset($generalReceiptInfo))
            @foreach($generalReceiptInfo->getReceiptsDimensionAccount as $key=>$acc_code_item) 
            <tr id="first_row{{$key+1}}" >      
              <td>
                  
              </td>              
              <td>
                
                <input type="hidden" name="acc_code_id[]" value="{{isset($generalReceiptInfo)?$acc_code_item->ac_codes_id:''}}" class="acc_code_id">
                
                <input type="text" class="max-wid acc_code" name="account_code[]" value="{{isset($generalReceiptInfo)?$acc_code_item->account_code.'-'.$acc_code_item->description:''}}" >

                <input type="hidden" readonly class="max-wid acc_code_desc" name="account_code_desc[]"  value="{{isset($generalReceiptInfo)?$acc_code_item->description:''}}" name="acc_cr_second" >


            </td>

            
            @if($acc_parameter->acc_params_dr_acc == $acc_code_item->acc_code_val )
            <td><input type="text" readonly class="max-wid acc_code_type" name="acc_code_type[]" value="{{isset($generalReceiptInfo)?$acc_code_item->dim_type:''}}"></td>
            @elseif($acc_parameter->acc_params_cr_acc == $acc_code_item->acc_code_val)
            <td><input type="text" readonly class="max-wid acc_code_type" name="acc_code_type[]"  value="{{isset($generalReceiptInfo)?$acc_code_item->dim_type:$acc_parameter->acc_params_cr_type}}"></td>
            @else
            <td>  <input type="text" readonly="" class="max-wid acc_code_type" name="acc_code_type[]" value={{$acc_code_item->dim_type}}>    </td> 
            @endif
            
            <td><input type="text" class="max-wid allownumericwithdecimal dr_amt read text-right" name="dr_amt[]" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" value="{{isset($generalReceiptInfo)?$acc_code_item->debit_amount:''}}"  onkeyup="return calculateAmt(this); FormatCurrency(this)" id="dr_amt_id" readonly></td>
            <td>
                <input type="text" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" class="max-wid allownumericwithdecimal cr_amt read text-right" name="cr_amt[]" value="{{isset($generalReceiptInfo)?$acc_code_item->credit_amount:''}}" onkeyup="return calculateAmt(this); FormatCurrency(this)" id="cr_amt_id" >
            </td>
            <td><input type="text" class="max-wid narration" name="narration[]" value="{{isset($generalReceiptInfo)?$acc_code_item->narration:null}}"></td>
        </tr>
        @endforeach
        @else
        
        <tr id="first_row1" >      
          <td>

              
            <a class="remove_details"><i class="fa fa-minus" aria-hidden="true" style="color:#093879;"></i>
            </a>
            
        </td>              
        <td>
            
            <input type="hidden" name="acc_code_id[]" value="" class="acc_code_id">
            
            <input type="text" class="max-wid acc_code" name="account_code[]" value="" >

            <input type="hidden" readonly class="max-wid acc_code_desc" name="acc_dr_desc[]"  value="" name="acc_cr_second" >

        </td>
        <td>  <input type="text" readonly="" class="max-wid acc_code_type" name="acc_code_type[]" >    </td> 
        
        
        <td><input type="text" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="return calculateAmt(this); FormatCurrency(this)" class="max-wid allownumericwithdecimal dr_amt read text-right" name="dr_amt[]" value="0"  id="dr_amt_id" ></td>
        <td>
            <input type="text" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="return calculateAmt(this); FormatCurrency(this)" class="max-wid allownumericwithdecimal cr_amt read text-right" name="cr_amt[]" value="0"   id="cr_amt_id" >
        </td>
        <td><input type="text" class="max-wid narration" name="narration[]" value=""></td>
    </tr>

    
    @endif
    
</tbody>
<tfoot>
  <tr>
   
    <td align="right" colspan="3"><b>Total :</b> </td>
    <td > <input type="text" class="max-wid read text-right" readonly name="total_dr"  value="{{ (isset($generalReceiptInfo)?  number_format((float)$generalReceiptInfo->getReceiptsDimensionAccount->sum('debit_amount'),3,'.', ''): '0.000'  ) }}"  id="total_dr" ></td>

    <td > <input type="text" class="max-wid read text-right" readonly name="total_cr"  value="{{ (isset($generalReceiptInfo)?  number_format((float)$generalReceiptInfo->getReceiptsDimensionAccount->sum('credit_amount'),3,'.', ''): '0.000'  ) }}"   id="total_cr" ></td>
    <td></td>
</tr>
</tfoot>
</table>
</div>
</div>
</div>
</div>


<div class="row">
    <div class="col">
      <div class="w-100"></div>
      <button type="submit" class="btn btn-primary">SAVE</button>
  </div>
</div>
</div>
</div>
<!--Payment ends -->
<div class="clearfix"></div>

<!--Remaining Invoices ends -->
</div>
</div>
</div>
</form>
<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script><!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script src="{{ asset('public/js/tokenize2.min.js') }}" ></script>


<script type="text/javascript">

    $(document).ready(function() {
		@if(!isset($generalReceiptInfo) && $isYearChequeCorrect == false)	
            alert("Current Year Is Not Match With the Sequence Year");	
        @elseif(!isset($generalReceiptInfo) && $isYearCashCorrect == false)	
            alert("Current Year Is Not Match With the Sequence Year");	
        @endif
		
        $("input[name^='account_code']").autocomplete({
            source: function( request, response ) {
                $.ajax({
                   url: '{!!URL::route('accountCodeAutocompleteWithoutDesc')!!}',
                   data: {
                      search: request.term,request:1
                  },
                  success: function( data ) {
                      response( data );
                  }
              });
            },
            change: function (event, ui) {
                var rowId = $(this).closest('tr').prop('id');
                if(ui.item == null  || ui.item == undefined){
                    
                    $(this).val("");
                    $("#"+rowId+" .acc_code").val('');
                    $("#"+rowId+" .acc_code_id").val('');
                    $("#"+rowId+" .acc_code_desc").val('');
                    
                }
                else{
                    
                    $(this).val(ui.item.label); // display the selected 
                    var acc_code_id = ui.item.ids; // selected value
                    $("#"+rowId+" .acc_code_id").val(ui.item.ids);
                    
                    // AJAX
                    $.ajax({
                       url: '{!!URL::route('accountCodeWithParamInfo')!!}',
                       data: {acc_code_id:acc_code_id,request:2},
                       success:function(response){
                           
                          var len = response.length;
                          
                          if(len > 0){
                             var type = response[0];
                             var desc = response[1];
                             
                             $("#"+rowId+" .acc_code_desc").val(desc);
                             
                         }
                         else{
                            $(this).val('');
                            $("#"+rowId+" .acc_code_id").val('');
                        }
                    }
                    
                });
                }    
                return false;
            },
            minlenght:2,
            autoFocus:true
            
        });
        $("#receipt_generation_form").validate({

            submitHandler: function(form) {
              var lengLineitem = $('#acc_line_list tr').length;  
              for(var i =1 ;i<=lengLineitem;i++){
                    if($("#first_row"+i).find(".acc_code").val() ==''){

                        alert("Account Code is missing");
                        return false;
                    }
              }
              var head_amt = parseFloat($('#receipts_generation_amt').val().replace(/,/g, '') );
                  //alert(head_amt);
                  var total_dr = parseFloat($('#total_dr').val().replace(/,/g, ''));
                  var total_cr = parseFloat($('#total_cr').val().replace(/,/g, ''));
                  var tally    = total_cr - total_dr;
                  if(tally > 0 ){ 
                      if(head_amt == tally){
                          $('.save-contract').prop('disabled', true);
                          form.submit();
                      }
                      else{

                        alert("Header Amount Should Be Tally with Credit - Debit.");
                    }
                }
                else{
                  alert("Please Make Total Credit Should Be Greater Than Total Debit.");
              }
          }, 
      })
 
    });

    function ajaxCall(mobilenumber = '',resident_card_id = '',building_id = '',unit_id = ''){
       var multiple_contract = false;   
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
        building_id : building_id,unit_id:unit_id,
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
          
            //alert(result[1].tenant_contract_no);                           
            if(data != 0){
              var result = $.parseJSON(data);
              
              if(result[0].tenant_contract_no){
               var validator = $( "#receipt_generation_form" ).validate()    
               validator.resetForm();
               if(result[1].resident_id != null){
                $('#resident_card_id').val(result[1].resident_id);
                $("#resident").html("Resident ID");
            }else{
                $('#resident_card_id').val(result[1].com_reg_no);
                $("#resident").html("Commercial Reg. No");
            }
            $('#mob_no').val(result[1].tenant_contact_no);
            
                   // $('#tenant_contract_no').html('<option value="">Select Agreement No</option>');
                   
                   
                   $('#tenant_contract_no').html('<option value="'+ result[0].id +'">'+ result[0].tenant_contract_no +'</option>');
                   
                   var agreeDt = new Date(result[0].created_at);
                   
                   var day = ("0" + agreeDt.getDate()).slice(-2);
                   var month = ("0" + (agreeDt.getMonth() + 1)).slice(-2);
                   var agreeDtVal = agreeDt.getFullYear()+"-"+(month)+"-"+(day) ;
                   
                   $('#tenant_contract_date').val(agreeDtVal);
                   $('#tenantContract').val(result[0].id);
                   $('#building_name').val(result[2].building_name);
                   $('#building_dim').val(result[2].building_name);
                   $('#building_code').val(result[2].building_code);
                   if(result[2].ax_division=='01'){
                      var div = 'HO';
                      var divVal = '01';
                  }else{
                      var div = 'PLM';
                      var divVal = '02';
                  }
                  $("#ax_division").empty();
                  $("#ax_division").append('<option value="'+divVal +'">'+ div +'</option>');

                    //$('#ax_division').val(result[2].ax_division);    
                    if(unit_id == ''){  
                        $('#unit_id').html('');
                        $('#unit_id').append('<input type="text" name="unit_name" id="unit_name" class="form-control">'); 
                        $('#unit_name').val(result[3].unit_no);
                    }

                    $('#unit_code').val(result[3].unit_code);
                    $('#tenant_name').val(result[1].tenant_name);
                    $('#tenant_code').val(result[1].tenant_code);
                    
                    $('#receipts_generation_remark').val('General :'+result[2].building_name+'/'+result[3].unit_no+'/'+result[1].tenant_name);
                }
                else if(result[0].length > 1){
                   var validator = $( "#receipt_generation_form" ).validate()
                   validator.resetForm();
                   if(result[1].resident_id != null){
                    $('#resident_card_id').val(result[1].resident_id);
                    $("#resident").html("Resident ID");
                }else{
                    $('#resident_card_id').val(result[1].com_reg_no);
                    $("#resident").html("Commercial Reg. No");
                }
                $('#mob_no').val(result[1].tenant_contact_no);

                $('#tenant_contract_no').html('<option value="">Select Agreement No</option>');
                $.each(result[0], function(key, value) {
                    $('#tenant_contract_no').append('<option value="'+ value['id'] +'">'+ value['tenant_contract_no'] +'</option>');
                });
                $('#ax_division').val(result[2][0].ax_division);
                var multiple_contract = true;       
            }


            if(multiple_contract == true && result[3].length > 1  && unit_id == ''){

              $('#unit_id').html('');
              var unit_name_txt = '<select name="unit_name" class="form-control" id="unit_name"><option value="">'+ 'Select Unit' +'</option>';
              $.each(result[3], function(key, value) {                            
                unit_name_txt +=  '<option value="'+ value.id +'" '+ '>'+ value.unit_no +'</option>';
            });
              unit_name_txt +=  '</select>';
              $('#unit_id').html(unit_name_txt);    

          }else if(result[3]){
           if(unit_id == ''){  
            $('#unit_id').html('');
            $('#unit_id').html('<input type="text" name="unit_name" id="unit_name" class="form-control">');
            $('#unit_name').val(result[3].unit_no);
        }
        $('#unit_code').val(result[3].unit_code);
    }



    $('#registerd_mob_no-error').hide();
    
            }else{//alert(data);  

                $('#mob_no').val("");
                $('#resident_card_id').val("");
                $('#tenant_contract_no').html('<option value="">'+ 'Enter Mobile No Or Resident ID ' +'</option>');
                
                
                /*$('#registerd_mob_no-error').show();*/
                var validator = $( "#receipt_generation_form" ).validate();
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

                
            }     

        }    

    });

}



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
        $('#receipts_generation_amt').val(''); 
        $('#tenant_contract_date').val('');
        $('#tenantContract').val(''); 
        $('#building_name').val(''); 
        $('#building_dim').val(''); 
        $('#building_dim').val('');
        $('#building_code').val(''); 
        $('#unit_name').val(''); 
        $('#unit_code').val(''); 
        $('#tenant_name').val(''); 
        $('#tenant_code').val(''); 
        $('#tenant_contract_no').prop('selectedIndex',0);
        $('#cheque_no').val('');
        $("#cheque_no").prop("disabled", true); 
        $('#ax_division').val(''); 

        (eventEl == 'resident_card_id')?mobilenumber='':resident_card_id='';

        ajaxCall(mobilenumber,resident_card_id);
    }

});




$('#building_name').autocomplete({
  source : '{!!URL::route('complaintBuildingAutocomplete')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    var clearTxt = ClearInputDate('building_name');
    if (ui.item != null || ui.item != undefined) {
         // $('#building_text_id').val(ui.item.ids);
          //$('#buildings').val(ui.item.ids);
          building_id = ui.item.ids;
          ajaxCall('','',building_id);
      }
  }

}); 


$(document).on('change',"#unit_name",function(event){
    var unit_id = $(this).val();
    ajaxCall('','','',unit_id);
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
                    
                    var agreeDt = new Date(result[0].created_at);

                    var day = ("0" + agreeDt.getDate()).slice(-2);
                    var month = ("0" + (agreeDt.getMonth() + 1)).slice(-2);
                    var agreeDtVal = agreeDt.getFullYear()+"-"+(month)+"-"+(day) ;
                
                $('#tenant_contract_date').val(agreeDtVal);
                $('#tenantContract').val(result[0].id); 
                $('#building_name').val(result[2].building_name);
                $('#building_dim').val(result[2].building_name);
                $('#building_code').val(result[2].building_code);
                if(result[2].ax_division=='02') //PLM
                  var ax_div = '2';
                else
                  var ax_div ='1' ;
                $('#ax_division').val(ax_div);
                $('#unit_id').html('');
                $('#unit_id').append('<input type="text" class="form-control" name="unit_name" id="unit_name" class="form-control">'); 
                $('#unit_name').val(result[3].unit_no);

                $('#unit_code').val(result[3].unit_code);
                $('#mob_no').val(result[1].tenant_contact_no);
                $('#resident_card_id').val(result[1].resident_id);
                $('#tenant_name').val(result[1].tenant_name);
                $('#tenant_code').val(result[1].tenant_code);
            }
        }
    });
    }
    else{
        ClearInputDate('tenant_contract_no')      
    }
});

$(document).on('click','.payment_method',function(){  
    
    var payment_method = $(this).val();
    if(payment_method == 1){ // Cheque
     
        $("#cheque_no").prop("disabled", false);
        $('#cheque_no').attr("required", true);
		$("#pdc_bank_id").prop("disabled", false);
		$('#pdc_bank_id').attr("required", true);
        @if(isset($chequeReceiptNo))	
            $("#receipts_generation_receipt_no").val("{{$chequeReceiptNo}}");	
        @endif    	
        @if(!isset($generalReceiptInfo) && $isYearChequeCorrect == false)	
            alert("Current Year Is Not Match With the Sequence Year");	
        @elseif(!isset($generalReceiptInfo) && $isYearCashCorrect == false)	
            alert("Current Year Is Not Match With the Sequence Year");	
        @endif        
    }
    else if(payment_method == 2){ // Cash
     
        $('#cheque_no').val('');
        $("#cheque_no").prop("disabled", true);
        $('#cheque_no').removeAttr('required');  
		$('#pdc_bank_id').prop('selectedIndex',0);
		$("#pdc_bank_id").prop("disabled", true);
		$('#pdc_bank_id').removeAttr('required'); 
        @if(isset($chequeReceiptNo))	
            $("#receipts_generation_receipt_no").val("{{$cashReceiptNo}}");	
        @endif	
        @if(!isset($generalReceiptInfo) && $isYearChequeCorrect == false)	
            alert("Current Year Is Not Match With the Sequence Year");	
        @elseif(!isset($generalReceiptInfo) && $isYearCashCorrect == false)	
            alert("Current Year Is Not Match With the Sequence Year");	
        @endif
        
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

/******************** Add More Line Item in Distribution ************/
$('.add_details').click(function(event){
    
    var $tr = $('tr[id^="first_row"]:last');
    for($i=1;$i <= $('#acc_line_list tr').length;$i++){

        if($("#first_row"+$i).find('.acc_code').val() ==''){
          alert("Please Fill All the Account Code ");
          return false;
      }
  }
  var num = parseInt( $tr.prop("id").match(/\d+/g), 10 ) +1;

  var first_row = $tr.clone().prop('id', 'first_row'+num );

  $(first_row).find("input:text").val("").end().appendTo('tbody');;

  $('tr').each(function(rowIndex){
    /// find each input with a name attribute inside each row
    $(this).find('input[name]').each(function(){
      var name;
      name = $(this).attr('name');
      name = name.replace(/\[[0-9]+\]/g, '['+(rowIndex-1)+']');
      $(this).attr('name',name);
      if(name == 'account_code[]'){
        $("input[name^='account_code']").autocomplete({    
            source: function( request, response ) {
                $.ajax({
                   url: '{!!URL::route('accountCodeAutocompleteWithoutDesc')!!}',
                   data: {
                      search: request.term,request:1
                  },
                  success: function( data ) {
                      response( data );
                  }
              });
            },
            change: function (event, ui) {
                var rowId = $(this).closest('tr').prop('id');
                if(ui.item == null  || ui.item == undefined){
                 
                    $(this).val("");
                    $("#"+rowId+" .acc_code_id").val('');
                    $("#"+rowId+" .acc_code_desc").val('');
                    $("#"+rowId+" .acc_code_type").val('');
                }
                else{
                    
                    $(this).val(ui.item.label); // display the selected 
                    var acc_code_id = ui.item.ids; // selected value
                    $("#"+rowId+" .acc_code_id").val(ui.item.ids);
                    
                    // AJAX
                    $.ajax({
                       url: '{!!URL::route('accountCodeWithParamInfo')!!}',
                       data: {acc_code_id:acc_code_id,request:2},
                       success:function(response){
                        
                          var len = response.length;
                          
                          if(len > 0){
                             var type = response[0];
                             var desc = response[1];
                             
                             $("#"+rowId+" .acc_code_desc").val(desc);
                             $("#"+rowId+" .acc_code_type").val(type);
                         }
                         
                     }
                 });
                }    
                return false;
            },
            minlenght:2,
            autoFocus:true
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

  amtCal('debit_amount');
  amtCal('credit_amount');


});

/**************************************************************/
$(document).on("click",".remove_details",function() {

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


});
/***************************** End *****************************/
function amtCal(className){
    
    var total_cr = 0.000;
    var total_dr = 0.000;
    
    $('.dr_amt').each(function() {
      var this_val_dr = ($(this).val() !=  '')? ($(this).val().replace(/,/g, '')) : 0;
      total_dr = total_dr + parseFloat(this_val_dr);     
  });

    $('.cr_amt').each(function() {
      var this_val_cr = ($(this).val() !=  '')? ($(this).val().replace(/,/g, '')) : 0;
      total_cr = total_cr + parseFloat(this_val_cr);     
  });
    $('#total_cr').val(formatNumber(total_cr.toFixed(3)));   
    $('#total_dr').val(formatNumber(total_dr.toFixed(3)));  
}
/*

Line item debit - credit validate
*/
function calculateAmt(sel){
    var rowId   = $(sel).closest('tr').prop('id');
    var inputId = $(sel).attr('id');
    
    if(inputId == 'dr_amt_id'){

        $("#"+rowId).find('.cr_amt').val(0.000);
        $("#"+rowId).find('.acc_code_type').val('LEDGER');
        amtCal('dr');
    }

    if(inputId == 'cr_amt_id'){
        $("#"+rowId).find('.dr_amt').val(0.000);
        $("#"+rowId).find('.acc_code_type').val('LEDGER');
        amtCal('cr');
    }
    
}
function ClearInputDate(att_el){

    $('#tenant_contract_date').attr('readonly',false);
    $('#tenant_contract_date').val('yyyy/mm/dd');
    $('#tenant_contract_date').attr('readonly',true);
    $('#tenantContract').val(''); 
    if(att_el != 'building_name')
        $('#building_name').val(''); 
    $('#building_dim').val(''); 
    $('#building_code').val(''); 
    $('#unit_name').val(''); 
    $('#unit_code').val(''); 
    $('#tenant_name').val(''); 
    $('#tenant_code').val(''); 
    $('#unit_id').html('');
    $('#unit_id').html('<input type="text" name="unit_name" id="unit_name" class="form-control">'); 
    $('#tenant_contract_no').html('<option value="">Enter Mobile No Or Resident ID </option>'); 
    $('#tenant_contract_no').prop('selectedIndex',0);
    $('#cheque_no').val('');
    $("#cheque_no").prop("disabled", true);  
}
</script>
@endsection
