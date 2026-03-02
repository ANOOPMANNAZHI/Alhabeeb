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
            <div class="page-title">Create Contract</div>
        </div>
        {{Breadcrumbs::render('landlordContract.create')}}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
 <form action="{{route('landlordContractAction')}}" autocomplete="off" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
    {{csrf_field()}}
    <!--<div class="align-right-text">

                <div class="col">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i>Save</button>
                    <button type="reset" class="btn btn-primary"><i class="fa fa-times" aria-hidden="true"></i>
Cancel</button>
                    
        </div> 
    </div> -->
    <div class="w-100"></div>
<div class="sub-head">Agreement Details</div>
<div class="dataSearchBox sub-box ">
    
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group ">
                    <label for="landlord_contract_no">Agreement No <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                    <div class="p-relative">
                    <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                   <input type="text"  class="form-control" id="landlord_contract_no" name="landlord_contract_no" value="{{$nextAgree}}" required readonly>
               </div>
               </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Agreement Date <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input disabled="" type="text" class="form-control" id="simpleFormEmail" placeholder="{{today()->format('d/m/Y') }}" value="{{ isset($enquiry)? $enquiry->created_at->format('d/m/Y') : today()->format('d/m/Y') }}">
                </div>
                </div>
            </div>
            
        </div>
</div>

<div class="sub-head">Landlord Detalis 
    <a href="#" class="create_landlord" data-toggle="modal" data-target="#myModal"  data-backdrop="static" data-keyboard="false">Create Landlord</a> 
    <span id="create_build_span" style="display:none">
    ||
    <a href="#" class="create_building" data-toggle="modal" data-target="#myModal1"  data-backdrop="static" data-keyboard="false">Create Building</a>
    </span>
</div>
<div class="dataSearchBox sub-box ">
    
        <div class="row">
            <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_id">Landlord Name <small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="icon icon-landlord" aria-hidden="true"></i>
                    <input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="Enter Landlord Name" required >
                <!-- <select class="form-control" id="vendor_code" name="vendor_id" required="">
                    <option value="">Select Landlord Code</option>
                    @foreach($vendorsList as $vendor)
                        <option value={{$vendor->id}}>{{$vendor->vendor_code}}</option>
                    @endforeach
                </select> -->
            </div>
                </div>
               <input type="hidden" class="form-control" id="vendor_code" name="vendor_id" placeholder="Enter Landlord Code"  readonly>
            </div>
       
        <!-- <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_name">Landlord Name</label>
                 <div class="p-relative">
                    <i class="fa  fa-user-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="vendor_name" name="vendor_name" value="" placeholder="Enter Landlord Name" readonly>
            </div>
            </div>
        </div> -->
        
        <div class="col-sm-6">
            <div class="form-group">
                <label for="building_id">Building Name <small class="textRed">*</small></label>
               <div class="p-relative">
                    <i class="icon icon-building" aria-hidden="true"></i>
                    <input type="text" placeholder="Enter Building Name" required name="building_name" class="form-control building_name building" id="building_name" value="">

                    <input type="hidden" name="building_id" id="building_id" value="">
                <!-- <select class="form-control" id="building_id" name="building_id" required="">
                    <option value="">Select Building Code</option>
                    @foreach($buildingList as $build)
                        <option value={{$build->id}}>{{$build->building_code}}</option>
                    @endforeach
                </select> -->
               </div>
            </div>
        </div>
         </div>
        <!-- <div class="col-sm-6">
            <div class="form-group">
                <label for="building_name">Building Name</label>
                 <div class="p-relative">
                    <i class="fa fa-building-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="building_name" name="building_name" value="" placeholder="Enter Building Name" readonly>
            </div>
            </div>
        </div> -->
        <div class="w-100"></div>
  
    
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
                        <option value={{$method->id}}>{{$method->payment_method_code}}</option>
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
                        <option value={{$manage->id}}>{{$manage->management_types_name}}</option>
                    @endforeach
                </select>
            </div>
            </div> 
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6" id="comprehensive_field" style="display:none;">
            <div class="form-group">
                <label for="landlord_contract_amt">Amount<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="landlord_contract_amt"  placeholder="Enter Amount" name="landlord_contract_amt" value="{{ isset($data)?  old('vendor_contact_no',$data->vendor_contact_no): old('vendor_contact_no')}}"  maxlength="12"  data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values"  >
                  </div>               
            </div>
        </div>
         <div class="col-sm-6 normal_commission_field"  style="display:none;">
            <div class="form-group">
                <label>Management Fees Type<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="management_fee_type" name="management_fee_type" >
                    <option value="">Select Management Fees Type</option>
                    <option value=1 >Management Fees</option>
                    <option value=2 >Maintenance</option>
                    <option value=3 >Legal</option>
                    <option value=4 >Caretakers Fee</option>
                 </select>
            </div>
            </div> 
        </div>
        <div class="col-sm-6 normal_commission_field" style="display:none;">
            <div class="form-group">
                <label for="management_method">Management Fees<small class="textRed">*</small></label>
                 <div class="p-relative">
                 <label for="management_method_one">
						<input type="radio" name="management_method" id="management_method_one" value ="1" class="management_method" checked> Percentage
				</label>
				<label for="management_method_two">		
					<input type="radio" name="management_method" id="management_method_two"  value ="2" class="management_method"> Amount
				</label>
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6 percentage_field_value" style="display:none;">
            <div class="form-group">
                 <label for="landlord_contract_management_fee_label" id="landlord_contract_management_fee_label" >Management Value<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
					<input type="number" class="form-control" id="landlord_contract_management_fee" name="landlord_contract_management_fee" placeholder="Enter Management Value" min="1" max="100" required>
            </div>
            </div>
        </div>
        
       <div class="col-sm-6 percentage_type" style="display:none;" >
            <div class="form-group" >
                <label for="landlord_contract_percentage">Percentage Type<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-percent icn-add" aria-hidden="true"></i>
					<select class="form-control" id="landlord_contract_percentage" name="landlord_contract_percentage" required>
						<option value="">Select Percentage Type</option>
						<option value=1 >Rent Income</option>
						<option value=2 >Rent Collection</option>
					</select>
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
                <input type="date" name="start_date" class="form-control" id="start_date" placeholder="Enter Start Date" min="1">
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_free_lease_period">Free Lease Period</label>
                 <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="text" name="landlord_free_lease_period" class="form-control" id="landlord_free_lease_period" placeholder="Enter Free Lease Period">
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
						<option value="">Select Duration</option>
						<option value="1">Open</option>
						<option value="2">Close</option>
					</select>
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="close_activity">Close Activity</label>
                 <div class="p-relative">
                    <i class="fa fa-window-close-o icn-add" aria-hidden="true"></i>
                <input type="text" name="close_activity" class="form-control" id="close_activity" placeholder="Enter Close Activity">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_valid_from_date">Valid From<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="landlord_contract_valid_from_date" name="landlord_contract_valid_from_date" placeholder="Enter Valid From">
            </div>
            </div>
        </div>
        <div class="col-sm-6" id="landlord_contract_valid_to_date_div">
            <div class="form-group">
                <label for="landlord_contract_valid_to_date">Valid To<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" name="landlord_contract_valid_to_date" class="form-control" id="landlord_contract_valid_to_date" placeholder="Enter Valid To">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_marketing_executive">Marketing Executive Name</label>
                 <div class="p-relative">
                    <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                 <select class="form-control" name="landlord_marketing_executive" >
                    <option value="">Select Marketing Executive</option>
                    @foreach($employeeList as $emp)
                        <option value={{$emp->employee->id}}>{{$emp->employee->employee_name.'('.$emp->employee->employee_code.')'}}</option>
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
                <input type="text" class="form-control" id="landlord_contract_note" name="landlord_contract_note" placeholder="Enter Remarks">
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
                <div class="col-md-12">
                
                </div>
                <div class="col-md-12 align-left-text">
                    <input type="hidden" name="sale_enquiry_id" value="{{$sale_enquiry_id}}">
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
<div class="modal" id="myModal1">
    
</div>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>
$(document).ready(function() {
@if($isYearCorrect == false)
        alert("Current Year Is Not Match With the Sequence Year");
        @endif
	
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
					$("#duration_type option:eq(1)").attr("disabled", "true");
                    $('#duration_type').val('');
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
					$("#duration_type option:eq(1)").removeAttr("disabled");
                    $('#duration_type').val('');
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
			$("#duration_type option:eq(1)").attr("disabled", "true");
            $('#duration_type').val('');
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
			$("#duration_type option:eq(1)").removeAttr("disabled");
            $('#duration_type').val('');
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
/************************************************************/ 
$('#vendor_name').autocomplete({
      source : '{!!URL::route('landlordAutocompleteCode')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        if (ui.item == null || ui.item == undefined) {
                $("#vendor_name").val('');
                $('#create_build_span').hide();
                $('#vendor_name-error').show();
            }else {
				
                $('#vendor_code').val(ui.item.ids);       
                $('#create_build_span').show();       
           }
        
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
            },'Must be greater than Valid To.');
			$("#form_sample_2").validate({
					rules: {
						landlord_contract_valid_to_date: { greaterThan: "#landlord_contract_valid_from_date" ,
						
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

        var sales_id = $("#sales_id").val();
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('buildingPopup')}}", // This is the url we gave in the route
            data: {'sales_id' : sales_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal1").html(response); 
                $("#vendors_name_popup").val($('#vendor_name').val());
                $("#vendor_id").val($('#vendor_code').val());
                
            },
        });
        return true;
   
        
         
    });
    

</script>

@endsection
