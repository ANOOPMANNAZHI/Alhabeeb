@extends('layouts.plms-app')
@section('css')
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<style>
  .ac_codes_id{
    width:100px;
  }
</style>
@endsection 


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">{{ (isset($maintenanceInvoice))? 'Edit' : 'Add'  }} Maintenance Invoice</div>
    </div>

    {{ (isset($maintenanceInvoice))?  
    Breadcrumbs::render('maintenanceInvoice.edit',$maintenanceInvoice) :  Breadcrumbs::render('maintenanceInvoice.create') }}

  </div>
</div>

<form action="{{ !isset($maintenanceInvoice)? route('maintenanceInvoice.store'): route('maintenanceInvoice.update',$maintenanceInvoice->id)}}" method="POST" id="form_sample_2" class="form-horizontal" data-toggle="validator">

  {{csrf_field()}} @if(isset($maintenanceInvoice)){{method_field('PUT')}}@endif

  <div class="row">
    <div class="col">  

      <div class="card card-box salesSearchBox">


        <div class="dataSearchBox ">    
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="simpleFormEmail">Invoice No.:<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                  <input  type="text" class="form-control"  disabled name="maintenance_invoice_no" value="{{ isset($maintenanceInvoice)? $maintenanceInvoice->maintenance_invoice_no : $nextCode}}" >
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Invoice Date :<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input required type="date" class="form-control " id="maintenance_invoice_date" name="maintenance_invoice_date" value="{{ old('maintenance_invoice_date', isset($maintenanceInvoice)? $maintenanceInvoice->maintenance_invoice_date->format('Y-m-d') : date('Y-m-d',strtotime(today())
                  ) )}}"  placeholder="Enter Invoice Date" >
                </div>
              </div> 
            </div>

            <div class="w-100"></div> 

          </div>

        </div>

<!-- 
  <input  type="text" class="form-control" id="simpleFormEmail"  placeholder="Enter Work Code" name="works_code" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($work)?  old('works_code',$work->works_code): old('works_code')}}" data-rule-maxlength="10" data-msg-maxlength="Maximum 10 Characters Allowed"> -->
  <div class="sub-head">Contractor Details</div>

  <div class="dataSearchBox ">    
    <div class="row">

     <div class="col-sm-6">
      <div class="form-group">
        <label for="simpleFormEmail">Vendor Name:<small class="textRed">*</small></label>
        <div class="p-relative">
          <i class="icon icon-contracter" aria-hidden="true"></i>
          <select name="vendor_id" id="vendor_id" required class="form-control">
            <option value="" >Select Contractor</option>
            @foreach($vendors as $vendor)
            <option  {{(old('vendor_id', isset($maintenanceInvoice)? $maintenanceInvoice->vendor_id: '' ) == $vendor->id)? 'selected' : '' }}  data_code="{{$vendor->vendor_code}}" value="{{$vendor->id}}" >{{$vendor->vendor_name}}</option>
            @endforeach
          </select> 
        </div>
      </div>
    </div>

    <div class="col-sm-6">
     <div class="form-group">
      <label for="simpleFormEmail">Vendor Code:<small class="textRed">*</small></label>
      <div class="p-relative">
        <i class="icon icon-contracter" aria-hidden="true"></i>
        <input  type="text" class="form-control" id="contractor_code"  disabled name="contractor_code" value="{{ old('vendor_id', isset($maintenanceInvoice)? $maintenanceInvoice->vendor->vendor_code: '' )}}" placeholder="Enter Contractor Code">
      </div>
    </div>
  </div>

  <div class="col-sm-12">
   <div class="form-group">
    <label for="simpleFormEmail">Description:<small class="textRed">*</small></label>
    <div class="p-relative">
      <i class="fa fa-snowflake-o icn-add" aria-hidden="true"></i>
      <textarea  class="form-control" name="maintenance_invoice_desc" >{{ old('maintenance_invoice_desc', isset($maintenanceInvoice)? $maintenanceInvoice->maintenance_invoice_desc: '' )}}</textarea>                 
    </div>
  </div>
</div>        
</div>
</div>

<div class="sub-head">Invoice Details</div>

<div class="dataSearchBox ">    
  <div class="row">

  <div class="col-sm-6">
     <div class="form-group">
      <label for="maintenance_invoice_refer_no">Ref No.:<small class="textRed">*</small></label>
      <div class="p-relative">
        <i class="fa fa-hashtag icn-add" aria-hidden="true"></i>
        <input  type="text" class="form-control" id="maintenance_invoice_refer_no"  required name="maintenance_invoice_refer_no" value="{{ old('maintenance_invoice_refer_no', isset($maintenanceInvoice)? $maintenanceInvoice->maintenance_invoice_refer_no: '' )}}" placeholder="Enter Ref No">
      </div>
    </div>
  </div>
  <div class="col-sm-6">
   <div class="form-group">
    <label for="maintenance_invoice_refer_amt">Amount:<small class="textRed">*</small></label>
      <div class="p-relative">
      <i class="fa fa-money icn-add" aria-hidden="true"></i>
      <input  type="text" class="form-control allownumericwithdecimal text-right" id="maintenance_invoice_refer_amt"  required name="maintenance_invoice_refer_amt" value="{{ old('maintenance_invoice_refer_amt', isset($maintenanceInvoice)? numberFormat($maintenanceInvoice->maintenance_invoice_refer_amt): '' )}}" placeholder="Enter Amount" readonly>
    </div>
  </div>
  </div>
<!--
<div class="col-sm-6">
 <div class="form-group">
  <label for="maintenance_invoice_payment_method">Payment Method:<small class="textRed">*</small></label>
  <div class="p-relative">
    <i class="fa fa-gg-circle icn-add" aria-hidden="true"></i>
    <select required name="maintenance_invoice_payment_method" id="maintenance_invoice_payment_method" class="form-control">
      <option value="">Select Payment Method</option>
      <option {{(old('maintenance_invoice_payment_method',isset($maintenanceInvoice)? $maintenanceInvoice->maintenance_invoice_payment_method: '') == 1)? 'selected="selected"' : ''}} value="1">Cash</option>
      <option {{(old('maintenance_invoice_payment_method',isset($maintenanceInvoice)? $maintenanceInvoice->maintenance_invoice_payment_method: '') == 2)? 'selected="selected"' : ''}}  value="2">Cheque</option>
    </select>
  </div>
</div>
</div>
-->
<div class="col-sm-6">
 <div class="form-group">
  <label for="maintenance_invoice_comment">Comments:</label>
  <div class="p-relative">
    <i class="fa fa-keyboard-o icn-add" aria-hidden="true"></i>
    <input  type="text" class="form-control" id="maintenance_invoice_comment" name="maintenance_invoice_comment" value="{{ old('maintenance_invoice_comment', isset($maintenanceInvoice)? $maintenanceInvoice->maintenance_invoice_comment: '' )}}" placeholder="Enter Comment">
  </div>
</div>
</div>
</div>
</div>


<div class="dataSearchBox ">    
  <div class="row">

    <div class="col-sm-6">
     <div class="form-group">
      <label for="ax_batch_id">AX-Batch ID:</label>
      <div class="p-relative">
        <i class="fa fa-wrench icn-add" aria-hidden="true"></i>
        <input disabled  type="text" class="form-control" id="ax_batch_id"  name="ax_batch_id" value="{{ old('ax_batch_id', isset($maintenanceInvoice)? $maintenanceInvoice->ax_batch_id: '' )}}" >
      </div>
    </div>
  </div>

  <div class="col-sm-6">
   <div class="form-group">
    <label for="ax_invoice_no">AX-Invoice No:</label>
    <div class="p-relative">
      <i class="fa fa-tags icn-add" aria-hidden="true"></i>
      <input  disabled type="text" class="form-control" id="ax_invoice_no"   name="ax_invoice_no" value="{{ old('ax_invoice_no', isset($maintenanceInvoice)? $maintenanceInvoice->ax_invoice_no: '' )}}" >
    </div>
  </div>
</div>


</div>
</div>




<div class="sub-head ">Dimension Details</div>
<div class="clearfix" style="padding: 10px 20px"></div>
<div class="row">
  <div class="col-sm-12">

    <div class="table-wrap ">    
      <div class="table-responsive"> <div>
        <table class="table display product-overview mb-30">
         <thead>
          <tr>
            <th>
              <a href='#' id="add_details" class=" add_details">
                <i class="fa fa-plus" aria-hidden="true"></i>
              </a>
            </th>
            <th>Account Code</th>
            <th>Bldg</th>
            <th>Unit</th>
            <th>Inv.Desc</th>
            <th>Mat.Charges</th>
            <th>Labour Charges</th>
            <th>Debit Amount</th>
            <th>Credit Amount</th>
            <th>Recovery</th>
            <!--<th>Technician Name</th> -->
            <th>Dim1</th>
            <th>Dim2</th>                       

          </tr>

        </thead>
        <tbody id="tbody_row">
         @php if(is_array(old('ac_codes_id')) || isset($maintenanceInvoice) ){


         $ac_codes_id = old('ac_codes_id',isset($maintenanceInvoice)? $maintenanceInvoice->maintenanceInvoiceDetails->pluck('ac_codes_id'): '');
         $i = 0;

         foreach($ac_codes_id as $key => $val){   
         $loop_flag = ($i == 0)?  true: false;  
         $i++; 
         @endphp 
         <tr id="first_row{{$key+1}}">
           <td>
            <a   class=" remove_details"><i class="fa fa-minus" aria-hidden="true"></i>
            </a>
          </td>
          <td>
            @php $curr_ac_code = old('ac_codes_id.'.$key,isset($maintenanceInvoice)? $maintenanceInvoice->maintenanceInvoiceDetails()->skip($key)->first()->ac_codes_id: '');                         
            $curr_ac_code  = $accountCodes->where('id',$curr_ac_code)->first();

            $cur_Invoice =  isset($maintenanceInvoice)? $maintenanceInvoice->maintenanceInvoiceDetails()->skip($key)->first(): '' ;
            @endphp 
            
            <input type="text" class="account_code ac_codes_id"  name="account_code[{{$key}}]" value="{{isset($maintenanceInvoice)?$cur_Invoice->accountCode->acc_code_val.' - '.$cur_Invoice->description :''}}" >
            <input type="hidden" class="account_id type"  name="ac_codes_id[{{$key}}]" value="{{isset($maintenanceInvoice)?$curr_ac_code->id:''}}">

          </td>
          <td>
             <input type="text" class="building_name"  name="building_name[]" value="{{isset($maintenanceInvoice)?$cur_Invoice->building->building_name:''}}" id="" >
              <input type="hidden" class="building"  name="building[{{$key}}]" value="{{old('building.'.$key,isset($maintenanceInvoice)?$cur_Invoice->building_id:'')}}" >
          </td>
          <td>

            <select  name="unit[{{$key}}]" class="unit_id unit">
              @php
              $cur_buiding = old('building.'.$key, isset($maintenanceInvoice)? $cur_Invoice->building_id : '');
              $cur_buiding = $buildings->where('id',$cur_buiding)->first(); 
              $curr_unit = $cur_buiding->unit;
              @endphp 
              <option value="">Select Unit</option>
              @foreach($curr_unit as $unit)
              <option {{ (old('unit.'.$key,isset($maintenanceInvoice)? $cur_Invoice->unit_id : '') == $unit->id)? 'selected':'' }}  value="{{$unit->id}}">{{$unit->unit_code}}</option>
              @endforeach
            </select>
          </td>                    

          <td>
            <input type="text" class="inv_desc txt_box"  name="invoice_desc[{{$key}}]" value="{{old('invoice_desc.'.$key,isset($maintenanceInvoice)? $cur_Invoice->invoice_desc : '')}}">
          </td>
          <td>
            <input type="text" class=" material_charge" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" name="material_charge[{{$key}}]" value="{{old('material_charge.'.$key,isset($maintenanceInvoice)? numberFormat($cur_Invoice->material_charge) : '')}}">
          </td> 
          <td>
            <input type="text" class=" labour_charge" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" name="labour_charge[{{$key}}]" value="{{old('labour_charge.'.$key,isset($maintenanceInvoice)? numberFormat($cur_Invoice->labour_charge) : '')}}">
          </td> 

          <td>
            <input type="text" class=" debit_amt" onkeyup="FormatCurrency(this)" name="debit_amt[{{$key}}]" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" value="{{old('debit_amt.'.$key,isset($maintenanceInvoice)? numberFormat($cur_Invoice->debit_amt) : '')}}" data_name="debit_amt" readonly>
          </td>

          <td>
            <input type="text" class=" credit_amt" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" name="credit_amt[{{$key}}]" value="{{old('credit_amt.'.$key,isset($maintenanceInvoice)? numberFormat($cur_Invoice->credit_amt) : '')}}" data_name="credit_amt">
          </td> 
          <input type="hidden" name="technician_id[{{$key}}]" value="">
         <td>
           <select required name="technician_recovery[{{$key}}]" class=" technician_recovery">                          
            <option  {{ (old('technician_recovery.'.$key,isset($maintenanceInvoice)? $cur_Invoice->technician_recovery : '') == 1)? 'selected':'' }}   value="1">Yes</option>
            <option  {{ (old('technician_recovery.'.$key ,isset($maintenanceInvoice)? $cur_Invoice->technician_recovery : '') == 0)? 'selected':'' }}  value="0">No</option>                          
          </select>
        </td> 


        <!--{{--<td>
          <select name="technician_id[{{$key}}]" class=" technician_id">
            <option value="">Select Technician</option>
            @foreach($technicians as $technician)
            <option  {{ (old('technician_id.'.$key ,isset($maintenanceInvoice)? $cur_Invoice->technician_id : '') == $technician->id)? 'selected':'' }}  value="{{$technician->id}}">{{$technician->employee->employee_name}}</option>
            @endforeach
          </select>
        </td>--}}-->

        <td>
          <select required name="dim1[{{$key}}]" class=" dim1">                         
            @foreach($dim1 as $dim1_val)
            <option {{ (old('dim1.'.$key ,isset($maintenanceInvoice)? $cur_Invoice->dim1able_id : '') == $dim1_val->id)? 'selected':'' }}    value="{{$dim1_val->id}}">{{$dim1_val->dim_value}}</option>
            @endforeach
          </select>
        </td>

        <td>
        <input type="text" class="dim2_input"  name="dim2_input[]" value="{{isset($cur_Invoice->building_id)?$cur_Invoice->building->building_name:''}}" id="" >
                        <input type="hidden" class="dim2"  name="dim2[{{$key}}]" value="{{isset($cur_Invoice->building_id)?$cur_Invoice->building_id:''}}" >



        </td>


      </tr>
      @php                    

    }
  }else{ @endphp 

  <tr id="first_row1">
   <td>
    <a   class="remove_details"><i class="fa fa-minus" aria-hidden="true"></i>
    </a>
  </td>
  <td>
    <input type="text" class="account_code ac_codes_id"  name="account_code[]" value="{{old('account_code[]')}}" required="">
    <input type="hidden" class="account_id"  name="ac_codes_id[]" value="{{old('ac_codes_id[]')}}" >

  </td>

  <td>
    <input type="text" class="building_name"  name="building_name[]" value="{{old('building_name[]')}}"  required="">
    <input type="hidden" class="building_id"  name="building[]" value="{{old('building[]')}}" id="" >

  </td>

  <td>
    <select  name="unit[]" class="unit_id unit">
      <option value="">Select Building First</option>
    </select>
    
  </td>                    

  <td>
    <input type="text" class="inv_desc "  name="invoice_desc[]" value="">
  </td>
  <td>
    <input type="text" class=" material_charge" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" name="material_charge[]" value="" data_name="material_charge">
  </td> 
  <td>
    <input type="text" class=" labour_charge" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values"  name="labour_charge[]" value="" data_name="labour_charge">
  </td> 

  <td>
    <input type="text" class=" debit_amt" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values"  name="debit_amt[]" value="" data_name="debit_amt" readonly>
  </td>

  <td>
    <input type="text" class=" credit_amt" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values"  name="credit_amt[]" value="" data_name="credit_amt">
  </td> 

  <td>
   <select required name="technician_recovery[]" class=" technician_recovery">  
    <option value="0" selected>No</option>   
    <option value="1">Yes</option>	
  </select>
</td>

<input type="hidden" name="technician_id[]" value="">
{{--<td>
  <select name="technician_id[]" class=" technician_id">
    <option value="">Select Technician</option>
    @foreach($technicians as $technician)
    <option value="{{$technician->id}}">{{$technician->employee->employee_name}}</option>
    @endforeach
  </select>
</td>--}}
<td>
  <input type="text" class="dim2_input"  readonly name="dim2_input[]" value="{{old('dim2_input[]')}}">
  <input type="hidden" class="dim2"  name="dim2[]" value="{{old('dim2[]')}}" >
  
</td>
<td>
  
  <select required name="dim1[]" class="dim1">
                          @foreach($dim1 as $dim1_val)
                          <option value="{{$dim1_val->id}}">{{$dim1_val->dim_value}}</option>
                          @endforeach
                        </select>
 
</td>

</tr>

@php 
}
@endphp 

</tbody>
<tfoot>
  <tr>
     <td><button class="btn btn-info add_details mr-2">Add more rows <i class="fa fa-plus" aria-hidden="true"></i></button></td>
    <td colspan="6" ><span class="pull-right" >Total : </span></td>
    <td><input type="text" id="debit_amt_total" class="debit_amt_total desc"  name="debit_amt_total" value="{{isset($maintenanceInvoice)? numberFormat($debitAmount->debit): 0}}" disabled></td>                      
    <td  colspan="4" ><span class="pull-left"><input type="text" class="credit_amt_total desc" id="credit_amt_total"  name="credit_amt_total" value="{{isset($maintenanceInvoice)? numberFormat($creditAmount->credit): 0}}" disabled></span></td>
  </tr>
</tfoot>
</table></div>
</div>
</div>
</div>
</div>


<div class="clearfix" style="padding: 10px 20px"></div>    
<div class="row">
  <div class="col-sm-12">
    <button type="submit" class="btn btn-primary save_invoice">SAVE</button>
  </div>
</div>



<div class="clearfix"></div>

</div>
</div>
</div>
</form>


@endsection


@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>

  $(document).ready(function() {
    
	@if($isYearCorrect == false)
      alert("Current Year Is Not Match With the Sequence Year");
    @endif
    
    $("input[name^='account_code']").autocomplete({
      source : '{!!URL::route('expenseAccountCodeAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        var parentId = $(this).parent().parent().attr('id');
        if (ui.item == null || ui.item == undefined) {
			$(this).val("");
            $("#"+parentId).find('.account_id').val('');
            $("#"+parentId).find(".inv_desc").val('');
        }
        else{
            $("#"+parentId).find('.account_id').val(ui.item.ids);
            var desc = ui.item.value;
            desc = desc.substring(desc.indexOf('-')+1)
            //$("#"+parentId).find(".inv_desc").val(desc);
            
        }
      }
     
    });
    var buildingOption = {
      source : '{!!URL::route('buildingAutocompleteCodeForDim')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        var parentId = $(this).parent().parent().attr('id');
        if (ui.item == null || ui.item == undefined) {
                $("#"+parentId).find('.building_id').val('');
                    $("#"+parentId).find('.building_name').val('');
                    $("#"+parentId).find('.dim2_input').val('');
                    $("#"+parentId).find('.dim2').val('');
                    $("#"+parentId).find('.unit_id').html('<option value="">No Available Units</option>');
                    $('.building_name-error').show();
                }else {            
                    $("#"+parentId).find('.building_id').val(ui.item.ids);
					 $("#"+parentId).find('.building').val(ui.item.ids);
                    var build_name = ui.item.value;
                    var res = build_name.split("-");
                    $("#"+parentId).find('.dim2_input').val(res[0]);
                    
                    if(res[1]=='01') var dim2Val =1; else var dim2Val =2;

                    var selectedValue = $("#"+parentId).find('.dim1').val(dim2Val);
                   $("#"+parentId).find("option[value = '" + selectedValue + "']").attr("selected", "selected");


                    $("#"+parentId).find('.dim2').val(ui.item.ids);
                    
                    var id = ui.item.ids;
                    $.ajax({
                          type: "POST",
                          url: "{{url('/tenantContract/buildingByUnitWithoutCheckVaccant')}}",
                          data: {"id":id,"_token": "{{ csrf_token() }}"},
                          cache: false,
                          dataType: "json",
                          success: function(data)
                          {
                           
                           if(data['buildUnit'].length > 0){
                              $("#"+parentId).find('.unit_id').empty();
                              $("#"+parentId).find('.unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
                                $.each(data['buildUnit'], function(key, value) {
                                    $("#"+parentId).find('.unit_id').append('<option value="'+ value['id'] +'">'+ value['unit_code'] +'</option>');
                                });
                            }
                            else{
                                $("#"+parentId).find('.unit_id').html('<option value="">No Available Units</option>');
                            }
                          } 
                      });
                  
               }
            
          }
        }; 

    $(".building_name").autocomplete(buildingOption);

    $("#form_sample_2").validate({
        submitHandler: function(form) {
          $('.save_invoice').prop('disabled', true);
          form.submit();
        }
    })



    /*******************************************************************************/
    function amtCal(className){

      var total = 0;
      $('.'+className).each(function() {
      var this_val = ($(this).val() !=  '')? ($(this).val().replace(/,/g, '')) : 0;
      total = parseFloat(total) + parseFloat(this_val);    
      });
      $('#'+className+'_total').val(formatNumber(total.toFixed(3)));  

      var debit_amt_total =  $('#debit_amt_total').val().replace(/,/g, '');
      var credit_amt_total =  $('#credit_amt_total').val().replace(/,/g, '');
      if(debit_amt_total - credit_amt_total >0){
      var maintenance_invoice_refer_amt = formatNumber(parseFloat(debit_amt_total - credit_amt_total).toFixed(3));
      $('#maintenance_invoice_refer_amt').val(maintenance_invoice_refer_amt);
      }else{
      $('#maintenance_invoice_refer_amt').val(0);
      }

    }

    /*******************************************************************************/


    $(document).on("keyup",'.debit_amt,.credit_amt',function (event) {

      var className =   $(this).attr('data_name');
      var parentId   = $(this).parent().parent().attr('id');
      var credit_value  = parseFloat($("#"+parentId).find('.credit_amt').val().replace(/,/g, ''));
      var debit_value = parseFloat($("#"+parentId).find('.debit_amt').val().replace(/,/g, ''));

      if(className == 'debit_amt' &&  debit_value > 0 ){

        $("#"+parentId).find('.credit_amt').val(0);
      }
      else if(className == 'credit_amt' &&  credit_value > 0 ){
        $("#"+parentId).find('.debit_amt').val(0);
        $("#"+parentId).find('.material_charge').val(0);
        $("#"+parentId).find('.labour_charge').val(0);
        $("#"+parentId).find('.technician_recovery').val(0);
      }else if(className == 'credit_amt' &&  credit_value <= 0 ){
        $("#"+parentId).find('.technician_recovery').val(0);
      }

      amtCal('debit_amt');
      amtCal('credit_amt');

	});

    /*******************************************************************************/
    $(document).on("keyup",'.material_charge,.labour_charge',function (event) {
      var tableRow = $(this).closest("tr");
      var material = Number(tableRow.find(".material_charge").val().replace(/,/g, '')); 
      var labour = Number(tableRow.find(".labour_charge").val().replace(/,/g, '')); 
      var total = material + labour; 
      tableRow.find(".debit_amt").val(isNaN(total) ? 0 :formatNumber(total.toFixed(3)));
      amtCal('debit_amt');

    });
    /*******************************************************************************/


 

//Vendor Code 
$('#vendor_id').change(function() {   

 if($(this).val() != ''){
   var vendor_code =  $('option:selected', this).attr('data_code');

   $('#contractor_code').val(vendor_code);
 }  

});

// List Unit on change Building 
$(document).on("change",'.building',function(event){

  var building_id = $(this).val();
  unit =  $( event.target ).closest('tr').find(".unit");
//alert(unit);
if(building_id != ''){

 $.ajax({
   type: "GET",
   url: "{{route('buildingUnit')}}/"+building_id,        
   success: function(data){          
    var result = $.parseJSON(data);
    var selected = "";
    unit.empty();
    unit.append('<option value="">'+ 'Select Unit' +'</option>')
    $.each(result, function(key, value) { 
      unit.append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_code'] +'</option>');
    });
  }
}); 


}    
dim2 =  $( event.target ).closest('tr').find(".dim2");
var buil_val = $('option:selected',this).attr('value');
dim2.val(buil_val);

})


$('.add_details').click(function(event){ 

  event.preventDefault();

  var $tr = $('#tbody_row tr[id^="first_row"]:last');
  for($i=1;$i <= $('#tbody_row tr').length;$i++){

    if($("#first_row"+$i).find('.account_code').val() =='' ||
      $("#first_row"+$i).find('.building_name').val() ==''){
      alert("Please Fill All the Account Code and Bld Code");
      return false;
    }
  }

  var num = parseInt( $tr.prop("id").match(/\d+/g), 10 ) +1;

  var first_row = $tr.clone().prop('id', 'first_row'+num );
  $(first_row).find("input:text").val("").end();
  $(first_row).find(".unit_id").html('<option>Select Building</option>').end();

  $tr.find('select').val(function(index, value) {
    return $('#tbody_row').find("tr:last").find('select').eq(index).val();
  });

  $('#tbody_row').find("tr:last").after(first_row);




  $('tr').each(function(rowIndex){
    /// find each input with a name attribute inside each row
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
  });

  amtCal('debit_amt');
  amtCal('credit_amt');
  $("input[name^='account_code']").autocomplete({
      source : '{!!URL::route('expenseAccountCodeAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        var parentId = $(this).parent().parent().attr('id');
        if (ui.item == null || ui.item == undefined) {
			$(this).val("");
            $("#"+parentId).find('.account_id').val('');
            $("#"+parentId).find(".inv_desc").val('');
        }
        else{
            $("#"+parentId).find('.account_id').val(ui.item.ids);
            var desc = ui.item.value;
            desc = desc.substring(desc.indexOf('-')+1)
            //$("#"+parentId).find(".inv_desc").val(desc);
            
        }
      }
     
    });
    var buildingOption = {
      source : '{!!URL::route('buildingAutocompleteCodeForDim')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        var parentId = $(this).parent().parent().attr('id');
        if (ui.item == null || ui.item == undefined) {
                $("#"+parentId).find('.building_id').val('');
                    $("#"+parentId).find('.building_name').val('');
                    $("#"+parentId).find('.dim2_input').val('');
                    $("#"+parentId).find('.dim2').val('');
                    $("#"+parentId).find('.unit_id').html('<option value="">No Available Units</option>');
                    $('.building_name-error').show();
        }else {
                    $("#"+parentId).find('.building_id').val(ui.item.ids);
					          $("#"+parentId).find('.building').val(ui.item.ids);
                    $("#"+parentId).find('.dim2_input').val(ui.item.value);
                    $("#"+parentId).find('.dim2').val(ui.item.ids);
                    
					if(!($("#"+parentId).find('.dim1').val() ==1 ||  $("#"+parentId).find('.dim1').val() ==2)){
                    var build_name = ui.item.value;
                    var res = build_name.split("-");
                   // alert('Hi');
                    $("#"+parentId).find('.dim2_input').val(res[0]);
                    
                   if(res[1]=='01') var dim2Val =1; else var dim2Val =2;

                   var selectedValue = $("#"+parentId).find('.dim1').val(dim2Val);
                   $("#"+parentId).find("option[value = '" + selectedValue + "']").attr("selected", "selected");


                    }
                    var id = ui.item.ids;
                    $.ajax({
                          type: "POST",
                          url: "{{url('/tenantContract/buildingByUnitWithoutCheckVaccant')}}",
                          data: {"id":id,"_token": "{{ csrf_token() }}"},
                          cache: false,
                          dataType: "json",
                          success: function(data)
                          {
                           
                           if(data['buildUnit'].length > 0){
                              $("#"+parentId).find('.unit_id').empty();
                              $("#"+parentId).find('.unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
                                $.each(data['buildUnit'], function(key, value) {
                                    $("#"+parentId).find('.unit_id').append('<option value="'+ value['id'] +'">'+ value['unit_code'] +'</option>');
                                });
                                if(data['buildDimCode'].ax_division=='01') var dim2Val =1; else var dim2Val =2;
                                var selectedValue = $("#"+parentId).find('.dim1').val(dim2Val);
                   $("#"+parentId).find("option[value = '" + selectedValue + "']").attr("selected", "selected");
                            }
                            else{
                                $("#"+parentId).find('.unit_id').html('<option value="">No Available Units</option>');
                            }
                          } 
                      });
                  
               }
            
          }
        }; 

    $(".building_name").autocomplete(buildingOption);

    $("#form_sample_2").validate({
        submitHandler: function(form) {
          $('.save_invoice').prop('disabled', true);
          form.submit();
        }
    })
});

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
amtCal('debit_amt');
amtCal('credit_amt');
});





});
</script>
@endsection
