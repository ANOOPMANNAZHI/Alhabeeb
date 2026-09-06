@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Edit Contract Detail</div>
        </div>
        {{Breadcrumbs::render('landlord-contract-edit',$landlordContract->id)}}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
 <form autocomplete="off" action="{{route('landlord-contract.update',$landlordContract->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
  {{csrf_field()}} @if(isset($landlordContract)){{method_field('PUT')}}@endif
  <!--  <div class="row align-right">

                <div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i>Save</button>
                    <button type="reset" class="btn btn-primary"><i class="fa fa-times" aria-hidden="true"></i>
Cancel</button>
                    
        </div>
    </div> -->
    <div class="clearfix"></div>
    <div class="w-100"></div>
<div class="sub-head">Agreement Details</div>
<div class="dataSearchBox sub-box ">
    
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group ">
                    <label for="landlord_contract_no">Agreement No <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                   <input type="text"  class="form-control" id="landlord_contract_no" name="landlord_contract_no" value="{{$landlordContract->landlord_contract_no}}" required readonly>
               </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Agreement Date <small class="textRed">*</small></label>
                     <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input disabled="" type="text" class="form-control" id="simpleFormEmail" placeholder="{{today()->format('d/m/Y') }}" value="{{ isset($landlordContract)? $landlordContract->created_at->format('d/m/Y') : '' }}">
                </div>
                </div>
            </div>
            
        </div>
</div>

<div class="sub-head">Landlord Detalis </div>
<div class="dataSearchBox sub-box ">
    
        <div class="row">
            <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_id">Landlord Name<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                    <input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="Enter Landlord Name" required value="{{isset($landlordContract)?($landlordContract->vendorInfo->vendor_name.'-'.$landlordContract->vendorInfo->vendor_code):''}}">

                    <input type="hidden" class="form-control" id="vendor_code" name="vendor_id" placeholder="Enter Landlord Code"  readonly value="{{isset($landlordContract)?($landlordContract->vendor_id):''}}">
                <!-- <select class="form-control" id="vendor_code" name="vendor_id" required="">
                    <option value="">Select Landlord Code</option>
                    @foreach($vendorsList as $vendor)
                        <option {{isset($landlordContract)?(($landlordContract->vendor_id==$vendor->id)?'selected':''):''}} value={{$vendor->id}}>{{$vendor->vendor_code}}</option>
                    @endforeach
                </select> -->
            </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label for="building_id">Building Name<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                    <input type="text" placeholder="Enter Building Name" required name="building_name" class="form-control building_name building" id="building_name" value="{{isset($landlordContract)?($landlordContract->buildingInfo->building_name.'-'.$landlordContract->buildingInfo->building_code):''}}">

                    <input type="hidden" name="building_id" id="building_id" value="{{isset($landlordContract)?($landlordContract->building_id):''}} ">

                <!-- <select class="form-control" id="building_id" name="building_id" required="">
                    <option value="">Select Building Code</option>
                    @foreach($buildingList as $build)
                        <option {{isset($landlordContract)?(($landlordContract->building_id== $build->id)?'selected':''):''}} value={{$build->id}}>{{$build->building_code}}</option>
                    @endforeach
                </select> -->
               </div>
            </div>
        </div>
       <!--  <div class="col-sm-6">
            <div class="form-group">
                <label for="building_name">Building Name</label>
                 <div class="p-relative">
                    <i class="fa fa-building-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="building_name" name="building_name" value="{{$landlordContract->buildingInfo->building_name}}" placeholder="Enter Building Name" readonly>
            </div>
            </div>
        </div> -->
        <div class="w-100"></div>
            
        </div>
    
</div>
<div class="sub-head">Payment Information</div>
<div class="dataSearchBox sub-box ">
    
        <div class="row">
            <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Payment Term<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-credit-card icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="landlord_contract_payment_type" name="landlord_contract_payment_type" required="">
                    <option value="">Select Payment Term</option>
                    @foreach($paymentmethodList as $method)
                        <option {{isset($landlordContract)?(($landlordContract->landlord_contract_payment_type== $method->id)?'selected':''):''}} value={{$method->id}}>{{$method->payment_method_code}}</option>
                    @endforeach
                </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Management Type<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="management_id" name="management_id" required="">
                    <option value="">Select Management Type</option>
                    @foreach($managementType as $manage)
                        <option value={{$manage->id}} {{isset($landlordContract)?(($landlordContract->management_id== $manage->id)?'selected':''):''}}>{{$manage->management_types_name}}</option>
                    @endforeach
                </select>
            </div>
            </div> 
        </div>
        <div class="w-100"></div>
        
        <div class="col-sm-6" id="comprehensive_field" @if($landlordContract->management_id !=1 ) style="display:none;" @endif>
            <div class="form-group">
                <label for="landlord_contract_amt">Amount<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
						<input {{ isset($landlordContract->landlord_contract_amt)? (($landlordContract->management_id == 1)?'':'disabled'):'disabled' }} type="text" class="form-control" id="landlord_contract_amt"  placeholder="Enter Amount" name="landlord_contract_amt" value="{{ isset($landlordContract->landlord_contract_amt)?$landlordContract->landlord_contract_amt:'' }}"  maxlength="12"  data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values" required >
                 </div>                
            </div>
        </div>
        
        
         <div class="col-sm-6 normal_commission_field" @if($landlordContract->management_id==1 ) style="display:none;"  @endif>
            <div class="form-group">
                <label>Management Fees Type</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="management_fee_type" name="management_fee_type" {{ isset($landlordContract->management_fee_type)?'':'DISABLED'}} >
                    <option value="">Select Management Fees Type</option>
                    <option value=1 {{ isset($landlordContract->management_fee_type)? (($landlordContract->management_fee_type==1)?'SELECTED':''):'' }} >Management Fees</option>
                    <option value=2 {{ isset($landlordContract->management_fee_type)? (($landlordContract->management_fee_type==2)?'SELECTED':''):'' }} >Maintenance</option>
                    <option value=3 {{ isset($landlordContract->management_fee_type)? (($landlordContract->management_fee_type==3)?'SELECTED':''):'' }}>Legal</option>
                    <option value=4 {{ isset($landlordContract->management_fee_type)? (($landlordContract->management_fee_type==4)?'SELECTED':''):'' }}>Caretakers Fee</option>
                 </select>
            </div>
            </div> 
        </div>

        <div class="col-sm-6 normal_commission_field" @if($landlordContract->management_id==1) style="display:none;"  @endif>
            <div class="form-group">
                <label for="management_method">Management Fees</label>
                 <div class="p-relative">
                  
						<input type="radio" name="management_method" value ="1" {{ isset($landlordContract->management_method)? (($landlordContract->management_method==1)?'CHECKED':''):'DISABLED' }} class="management_method"> Percentage
						<input type="radio" name="management_method" value ="2" {{ isset($landlordContract->management_method)? (($landlordContract->management_method==2)?'CHECKED':''):'DISABLED' }} class="management_method"> Amount
            </div>
            </div>
        </div>
       <div class="w-100"></div>
       <div class="col-sm-6 percentage_field_value" @if($landlordContract->management_id==1 ) style="display:none;"  @endif>
            <div class="form-group">
                <label for="simpleFormEmail">Management Value</label>
                 <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
					<input type="number" class="form-control" {{ isset($landlordContract->landlord_contract_management_fee)?'':'DISABLED'}}  id="landlord_contract_management_fee" name="landlord_contract_management_fee" value="{{isset($landlordContract->landlord_contract_management_fee)?$landlordContract->landlord_contract_management_fee:''}}" placeholder="Enter fees type" min="1" max="100">
            </div>
            </div>
        </div>
        
       <div class="col-sm-6 percentage_type" @if($landlordContract->management_method==2 || $landlordContract->management_id==1) style="display:none;" @endif>
            <div class="form-group percentage_type">
                <label for="landlord_contract_percentage">Percentage type</label>
                 <div class="p-relative">
                    <i class="fa fa-percent icn-add" aria-hidden="true"></i>
					<select class="form-control" id="landlord_contract_percentage" name="landlord_contract_percentage">
						<option value="">Select Percentage type</option>
						<option value=1 {{ isset($landlordContract->landlord_contract_percentage)? (($landlordContract->landlord_contract_percentage==1)?'SELECTED':''):'' }}  >Rent Income</option>
						<option value=2 {{ isset($landlordContract->landlord_contract_percentage)? (($landlordContract->landlord_contract_percentage==2)?'SELECTED':''):'' }} >Rent Collection</option>
					</select>
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cleaning_charge_method">Cleaning Charges</label>
                 <div class="p-relative">
                 <label for="cleaning_charge_method_one">
                        <input type="radio" name="cleaning_charge_method" value="1" {{ isset($landlordContract->cleaning_charge_method)? (($landlordContract->cleaning_charge_method==1)?'CHECKED':''):'CHECKED' }} class="cleaning_charge_method"> Percentage
                </label>
                <label for="cleaning_charge_method_two">
                    <input type="radio" name="cleaning_charge_method" value="2" {{ isset($landlordContract->cleaning_charge_method)? (($landlordContract->cleaning_charge_method==2)?'CHECKED':''):'' }} class="cleaning_charge_method"> Amount
                </label>
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                 <label for="landlord_contract_cleaning_charge_label" id="landlord_contract_cleaning_charge_label">Cleaning Value</label>
                 <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_cleaning_charge" name="landlord_contract_cleaning_charge" value="{{ isset($landlordContract->landlord_contract_cleaning_charge)?$landlordContract->landlord_contract_cleaning_charge:'' }}" placeholder="Enter Cleaning Value" min="0" max="999999999">
            </div>
            </div>
        </div>


        <div class="w-100"></div>
            
        </div>
    
</div>

<div class="sub-head">Contract Duration</div>
<div class="dataSearchBox sub-box ">
  
        <div class="row">
             <div class="col-sm-6">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                 <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" name="start_date" class="form-control" id="start_date" placeholder="Enter Start Date" value="{{ isset($landlordContract->start_date)?$landlordContract->start_date->format('Y-m-d'):'' }}" min="1">
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_free_lease_period">Free Lease Period</label>
                 <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="text" name="landlord_free_lease_period" class="form-control" id="landlord_free_lease_period" placeholder="Enter Free Lease Period" value="{{ isset($landlordContract->landlord_free_lease_period)?$landlordContract->landlord_free_lease_period:'' }}">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_duration">Duration Type<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
					<select class="form-control" name="duration_type" id="duration_type" required="">
						<option value="">Select Duration Type </option>
						<option value="1" {{ isset($landlordContract->landlord_contract_duration)? (($landlordContract->landlord_contract_duration==1)?'SELECTED':''):'' }}>Open</option>
						<option value="2" {{ isset($landlordContract->landlord_contract_duration)? (($landlordContract->landlord_contract_duration==2)?'SELECTED':''):'' }}>Close</option>
					</select>
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="close_activity">Close Activity</label>
                 <div class="p-relative">
                    <i class="fa fa-window-close-o icn-add" aria-hidden="true"></i>
                <input type="text" name="close_activity" class="form-control" id="close_activity" placeholder="Enter Close Activity" value="{{ isset($landlordContract->close_activity)?$landlordContract->close_activity:'' }}">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_valid_from_date">Valid From<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="landlord_contract_valid_from_date" name="landlord_contract_valid_from_date" placeholder="Enter Valid From" value="{{ isset($landlordContract->landlord_contract_valid_from_date)?$landlordContract->landlord_contract_valid_from_date->format('Y-m-d'):'' }}">
            </div>
            </div>
        </div>
       
        <div class="col-sm-6" id="landlord_contract_valid_to_date_div"  @if($landlordContract->landlord_contract_duration==1) style="display:none;" @endif>
            <div class="form-group">
                <label for="landlord_contract_valid_to_date">Valid To<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" name="landlord_contract_valid_to_date" class="form-control" id="landlord_contract_valid_to_date" placeholder="Enter Valid To" value="{{ isset($landlordContract->landlord_contract_valid_to_date)?$landlordContract->landlord_contract_valid_to_date->format('Y-m-d'):'' }}" >
            </div>
            </div>
        </div>
        
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_marketing_executive">Marketing Executive Name</label>
                 <div class="p-relative">
                    <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                
                 <select class="form-control" name="landlord_marketing_executive">
                    <option value="">Select Marketing Executive</option>
                    @foreach($salesTeam as $sal) 
                        <option value="{{$sal->employee->id}}" {{ isset($landlordContract->landlord_marketing_executive)?(($sal->employee->id==$landlordContract->landlord_marketing_executive)?"SELECTED":''):''}}>{{$sal->employee->employee_name.'('.$sal->employee->employee_code.')'}}</option>
                    @endforeach
                </select>
                </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-12">
            <div class="form-group">
                <label for="landlord_contract_note">Remarks </label>
                 <div class="p-relative">
                    <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="landlord_contract_note" name="landlord_contract_note" placeholder="Enter Remarks" value="{{ isset($landlordContract->landlord_contract_note)?$landlordContract->landlord_contract_note:'' }}">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
            
        </div>
   
</div>
<div class="clearfix"></div>
    <div class="row">

        <div class="w-100"></div>
        <div class="col mt-3">
            <div class="row">
                <div class="col-md-6">
                
                </div>
                <div class="col-md-6 align-right-text">
                     <input type="hidden" name="sale_enquiry_id" value="{{$landlordContract->sale_enquiry_id}}">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <!--<button type="reset" value="Reset" class="btn btn-primary"><i class="fa fa-times" aria-hidden="true"></i>Cancel</button> -->
                    
                </div>
            </div>
        </div>
 </form>
    </div>
 <div class="clearfix"></div>
                                            
</div>
</div>
</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>        <!-- The Modal -->
<div class="modal" id="myModal">
    
</div>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>
$(document).ready(function() {
    /************************************************************/
	@if($isYearCorrect == false)
      alert("Current Year Is Not Match With the Sequence Year");
     @endif
    $('#vendor_name').autocomplete({
      source : '{!!URL::route('landlordAutocompleteCode')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        if (ui.item == null || ui.item == undefined) {
                $("#vendor_name").val('');
                $('#vendor_name-error').show();
            }else {
                $('#vendor_code').val(ui.item.ids);              
           }
        
        }
    });
/************************************************************/
    $('#building_name').autocomplete({
     // source : '{!!URL::route('buildingAutocompleteCode')!!}',
      source: function(request, response) {
        $.getJSON("{!!URL::route('buildingAutocompleteCode')!!}", { landlord: $('#vendor_code').val(),building:$('#building_name').val() }, 
                  response);
      },
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        if (ui.item == null || ui.item == undefined) {
                $("#building_name").val('');
                $('#management_id').val('');   
                $('#building_name-error').show();
            }else {
                $('#building_id').val(ui.item.ids);   
                $('#management_id').val(ui.item.management_id);  
                
                if(ui.item.management_id == 1){
                    
                    $("#comprehensive_field input,.normal_commission_field select, .normal_commission_field radio").val('');
                    $(".percentage_field_value input,.percentage_type select").val('');
                    $(".normal_commission_field").hide();
                    $("#management_fee_type").removeAttr('required');
                    $(".percentage_field_value").hide();
                    $(".percentage_type").hide();
                    $(".percentage_field_value").hide();
                    $("#landlord_contract_amt").attr('required','required');
                    $("#comprehensive_field").show();
                    
                }   
                else{
                    $("#comprehensive_field input,.normal_commission_field select radio").val('');
                    $("#comprehensive_field input,.normal_commission_field select, .normal_commission_field radio").val('');
                    $(".percentage_field_value input,.percentage_type select").val('');
                    $("#landlord_contract_amt").removeAttr('required');
                    $("#management_method_one").prop('checked',true);
                    $("#management_fee_type").attr('required','required');
                    $(".percentage_field_value").show();
                    $(".percentage_type").show();
                    $("#comprehensive_field").hide();
                    $(".normal_commission_field").show();
                }         
           }
        
        }
        
        
    });
    $('#management_id').on('change', function(e) {  
        
        var management_id = $(this).val();
        
        if(management_id == 1){
            $("#comprehensive_field input,.normal_commission_field select").val('');
            $(".percentage_field_value input,.percentage_type select").val('');
        
            $("#management_fee_type").removeAttr('required');
            $(".percentage_field_value").hide();
            $(".percentage_type").hide();
            $(".normal_commission_field").hide();
            $(".percentage_type").hide();
            $(".percentage_field_value").hide();
            $("#landlord_contract_amt").attr('required','required');
            $("#comprehensive_field").show();
        }   
        else{
            $("#comprehensive_field input,.normal_commission_field select, .normal_commission_field radio").val('');
            $(".percentage_field_value input,.percentage_type select").val('');
        
            $("#landlord_contract_amt").removeAttr('required');
            $("#management_fee_type").attr('required','required');
        
            $("#comprehensive_field").hide();
            $(".percentage_type").hide();
            $(".percentage_field_value").hide();
            $(".normal_commission_field").show();
            $(".percentage_field_value").show();
            $(".percentage_type").show();
        }       
    });
/************************************************************/
 
    jQuery.validator.addMethod("greaterThan", 
            function(value, element, params) {

                if (!/Invalid|NaN/.test(new Date(value))) {
                    return new Date(value) > new Date($(params).val());
                }

                return isNaN(value) && isNaN($(params).val()) 
                    || (Number(value) > Number($(params).val())); 
            },'Must be greater than {0}.');
    $("#form_sample_2").validate({
        rules: {
        landlord_contract_valid_to_date: { greaterThan: "#landlord_contract_valid_from_date" 
        }
    }
    });
});
    
    $('#vendor_code').on('change', function(e) {       
            var vend_code = $(this).find(':selected').attr('value');
            if(vend_code=='')
                 $("#vendor_name").val(''); 
            else    
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url:"{{url('/vendorNameAjax')}}", 
                data:{'id' : vend_code,'_token':"{{ csrf_token() }}"}, 
                success: function(response){ // What to do if we succeed
                    if(response) 
                        $("#vendor_name").val(response); 
                    else
                        $("#vendor_name").val(''); 
                },

            });
        return false;
            
    });

    // Building Name 

    $('#building_id').on('change', function(e) { 

            var build_code = $(this).find(':selected').attr('value');
            if(build_code=='')
                 $("#building_name").val(''); 
            else    
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url:"{{url('/buildingNameAjax')}}", 
                data:{'id' : build_code,'_token':"{{ csrf_token() }}"}, 
                success: function(response){ // What to do if we succeed
                    if(response) 
                        $("#building_name").val(response); 
                    else
                        $("#building_name").val(''); 
                },

            });
        return false;
            
    });
	$('#management_id').on('change', function(e) {  
		
		var management_id = $(this).val();
		if(management_id == 1){
			$("#comprehensive_field input,.normal_commission_field select").val('');
			$(".percentage_field_value input,.percentage_type select").val('');
		
			$("#management_fee_type").removeAttr('required');
			$(".percentage_field_value").hide();
			$(".percentage_type").hide();
			$(".normal_commission_field").hide();
			$(".percentage_type").hide();
			$(".percentage_field_value").hide();
			$("#landlord_contract_amt").attr('required','required').prop('disabled', false);
			$("#comprehensive_field").show();
		}   
		else{
			$("#comprehensive_field input,.normal_commission_field select, .normal_commission_field radio").val('');
			$(".percentage_field_value input,.percentage_type select").val('');
		
			$("#landlord_contract_amt").removeAttr('required');
			$("#management_fee_type").attr('required','required');
		
			$("#comprehensive_field").hide();
			$(".percentage_type").hide();
			$(".percentage_field_value").hide();
			$(".normal_commission_field").show();
			$(".percentage_field_value").show();
			$(".percentage_type").show();
		}      
	});
	$('#duration_type').on('change', function(e) {  
			
		var duration_type = $(this).val();
		if(duration_type == 2){
			
			$("#landlord_contract_valid_to_date").val('');
			$("#landlord_contract_valid_to_date_div").show();
			$("#landlord_contract_valid_to_date").prop('disabled', false);
		}
		else{
			$("#landlord_contract_valid_to_date").val('');
			$("#landlord_contract_valid_to_date_div").hide();
			
		}
		 
	});
	$(".management_method").on('click', function(e) {   
		
		var management_method = $(this).val();
	
		if(management_method == 1){
			$("#landlord_contract_management_fee").attr('required','required');
			$("#landlord_contract_percentage").attr('required','required');			
			$(".percentage_field_value").show();
			$(".percentage_type").show();
			$("#landlord_contract_management_fee_label").html('Management Value<small class="textRed">*</small>');
			$("#landlord_contract_management_fee").attr('placeholder','Enter Management Value');
			$("#landlord_contract_management_fee").val('');
			$("#landlord_contract_management_fee").attr("max",100);
			$("#landlord_contract_management_fee-error").hide();
		}
		else{
			$("#landlord_contract_percentage").attr('required','required');
			$(".landlord_contract_percentage").removeAttr('required');
			$(".percentage_type").hide();
			$(".percentage_field_value").show();
			$("#landlord_contract_management_fee_label").html('Management Amount<small class="textRed">*</small>');
			$("#landlord_contract_management_fee").attr('placeholder','Enter Management Amount');
			$("#landlord_contract_management_fee").val('');
			$("#landlord_contract_management_fee").attr("max",999999999);
			$("#landlord_contract_management_fee-error").hide();
		}
	});
    $(".cleaning_charge_method").on('click', function(e) {
        var cleaning_charge_method = $(this).val();
        if (cleaning_charge_method == 1) {
            $("#landlord_contract_cleaning_charge_label").html('Cleaning Value');
            $("#landlord_contract_cleaning_charge").attr('placeholder', 'Enter Cleaning Value');
            $("#landlord_contract_cleaning_charge").attr("max", 100);
        } else {
            $("#landlord_contract_cleaning_charge_label").html('Cleaning Amount');
            $("#landlord_contract_cleaning_charge").attr('placeholder', 'Enter Cleaning Amount');
            $("#landlord_contract_cleaning_charge").attr("max", 999999999);
        }
    });
    // Create Landlord
    $('.create_landlord').on('click', function(e) {        

        // var action_key = $(this).attr('data-id');
        // var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        // var workflow_id = $("#workflow_id").val();
        
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('landlordPopup')}}", // This is the url we gave in the route
            data: {'sales_id' : sales_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
   
        
         
    });
    // create building
    $('.create_building').on('click', function(e) {        

        // var action_key = $(this).attr('data-id');
        // var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        // var workflow_id = $("#workflow_id").val();
        
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('buildingPopup')}}", // This is the url we gave in the route
            data: {'sales_id' : sales_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
   
        
         
    });
    


</script>

@endsection
