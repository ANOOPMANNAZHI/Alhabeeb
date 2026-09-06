@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Create Contract</div>
        </div>
        {{Breadcrumbs::render('landlordRenewalNewContract',$landlordContract->id,$status)}}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
 <form action="{{route('landlordRenewalContractStore')}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
  {{csrf_field()}} 
  <input type="hidden" name="status" value="{{$status}}">
   <!--  <div class="row align-right">

                <div class="col">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i>Save</button>
                    <button type="reset" class="btn btn-primary"><i class="fa fa-times" aria-hidden="true"></i>
Cancel</button>
                    
        </div>
    </div> -->
<div class="sub-head">Aggreement Details</div>
<div class="dataSearchBox sub-box ">
    
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group ">
                    <label for="landlord_contract_old_no">Old Agreement No <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                   <input type="text"  class="form-control" id="landlord_contract_old_no" name="landlord_contract_old_no" value="{{$landlordContract->landlord_contract_no}}" required readonly>
               </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Old Agreement Date <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input disabled="" type="text" class="form-control" id="simpleFormEmail" placeholder="{{today()->format('d/m/Y') }}" value="{{ isset($landlordContract)? $landlordContract->created_at->format('d/m/Y') : '' }}">
                </div>
                </div>
            </div>
            <div class="w-100"></div>
            <div class="col-sm-6">
                <div class="form-group ">
                    <label for="landlord_contract_no">Agreement No <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                   <input type="text"  class="form-control" id="landlord_contract_no" name="landlord_contract_no" value="{{$nextAgree}}" required readonly>
               </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Agreement Date <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="date" class="form-control" id="simpleFormEmail" placeholder="{{today()->format('d/m/Y') }}" value="{{ isset($landlordContract)? $landlordContract->created_at->format('d/m/Y') : '' }}">
                </div>
                </div>
            </div>
        </div>
</div>


<div class="dataSearchBox sub-box ">
    
        <div class="row">
            <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_id">Landlord Code</label>
                 <div class="p-relative">
                    <i class="icon icon-landlord" aria-hidden="true"></i>
                    <div class="p-relative">
                    <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                <select class="form-control" id="vendor_code" name="vendor_id" required="">
                    <option value="">Select Landlord Code</option>
                    @foreach($vendorsList as $vendor)
                        <option {{isset($landlordContract)?(($landlordContract->vendor_id==$vendor->id)?'selected':''):''}} value={{$vendor->id}}>{{$vendor->vendor_code}}</option>
                    @endforeach
                </select>
            </div>
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_name">Landlord Name</label>
                 <div class="p-relative">
                    <i class="icon icon-landlord" aria-hidden="true"></i>
                <input type="text" class="form-control" id="vendor_name" name="vendor_name" value="{{$landlordContract->vendorInfo->vendor_name}}" placeholder="Enter Landlord Name" readonly>
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="building_id">Build Code</label>
                <div class="p-relative">
                    <i class="icon icon-building" aria-hidden="true"></i>
                <select class="form-control" id="building_id" name="building_id" required="">
                    <option value="">Select Building Code</option>
                    @foreach($buildingList as $build)
                        <option {{isset($landlordContract)?(($landlordContract->building_id== $build->id)?'selected':''):''}} value={{$build->id}}>{{$build->building_code}}</option>
                    @endforeach
                </select>
               </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="building_name">Building Name</label>
                <div class="p-relative">
                    <i class="icon icon-building" aria-hidden="true"></i>
                <input type="text" class="form-control" id="building_name" name="building_name" value="{{$landlordContract->buildingInfo->building_name}}" placeholder="Enter Building Name" readonly>
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_address">Landlord Contract Address</label>
                <div class="p-relative">
                    <i class="icon icon-landlord" aria-hidden="true"></i>
                <textarea type="text" class="form-control" placeholder="Enter Contract Address" name="landlord_contract_address">{{$landlordContract->landlord_contract_address}}</textarea>
            </div>
            </div>
        </div>
        </div>
    
</div>
<div class="sub-head">Payment Information</div>
<div class="dataSearchBox sub-box ">
    
        <div class="row">
            <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Payment Term<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
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
                <label for="landlord_contract_amt">Amount<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="landlord_contract_amt"  placeholder="Enter Amount" name="landlord_contract_amt" value="{{ isset($landlordContract->landlord_contract_amt)?$landlordContract->landlord_contract_amt:'' }}"  maxlength="12"  data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values" required >
                 </div>                
            </div>
        </div>
         <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Management fees type<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="management_id" name="management_id" required="">
                    <option value="">Select Management fees type</option>
                    @foreach($managementType as $manage)
                        <option {{isset($landlordContract)?(($landlordContract->management_id== $manage->id)?'selected':''):''}} value={{$manage->id}}>{{$manage->management_types_name}}</option>
                    @endforeach
                </select>
            </div>
            </div> 
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Management fees<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="number" class="form-control" value="{{ isset($landlordContract->landlord_contract_management_fee)?$landlordContract->landlord_contract_management_fee:'' }}" id="landlord_contract_management_fee" name="landlord_contract_management_fee" placeholder="Enter fees type" min="1">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_cleaning_charge">Cleaning Amount</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_cleaning_charge" name="landlord_contract_cleaning_charge" value="{{ isset($landlordContract->landlord_contract_cleaning_charge)?$landlordContract->landlord_contract_cleaning_charge:'' }}" placeholder="Enter Cleaning Amount" min="0" max="999999999">
                </div>
            </div>
        </div>
        <div class="w-100"></div>
       <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_percentage">Percentage<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-percent icn-add" aria-hidden="true"></i>
                <input type="phone" class="form-control" id="landlord_contract_percentage" value="{{ isset($landlordContract->landlord_contract_percentage)?$landlordContract->landlord_contract_percentage:'' }}" id="landlord_contract_management_fee" name="landlord_contract_percentage" placeholder="Enter Percentage">
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
                <input type="date" required name="start_date" class="form-control" id="start_date" placeholder="Enter Start Date" value="{{ isset($landlordContract->start_date)?$landlordContract->start_date->format('Y-m-d'):'' }}" min="1">
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
                <label for="landlord_duration">Duration in Month</label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="number" name="landlord_duration" class="form-control" id="landlord_duration" placeholder="Enter Duration" value="{{ isset($landlordContract->landlord_contract_duration)?$landlordContract->landlord_contract_duration:'' }}">
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="close_activity">Close Activity</label>
                <div class="p-relative">
                    <i class="fa fa-times-circle-o icn-add" aria-hidden="true"></i>
                <input type="text" name="close_activity" class="form-control" id="close_activity" placeholder="Enter Close Activity" value="{{ isset($landlordContract->close_activity)?$landlordContract->close_activity:'' }}">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_valid_from_date">Valid From</label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="landlord_contract_valid_from_date" name="landlord_contract_valid_from_date" placeholder="Enter Valid From" value="{{ isset($landlordContract->landlord_contract_valid_from_date)?$landlordContract->landlord_contract_valid_from_date->format('Y-m-d'):'' }}">
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_valid_to_date">Valid To</label>
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
                    <i class="fa fa-id-card icn-add" aria-hidden="true"></i>
                 <select class="form-control" name="landlord_marketing_executive" required="">
                    <option value="">Select Marketing Executive</option>
                    @foreach($employeeList as $emp)
                        <option {{isset($landlordContract)?(($landlordContract->landlord_marketing_executive == $emp->id)?'selected':''):''}} value={{$emp->id}}>{{$emp->employee_name.'('.$emp->employee_code.')'}}</option>
                    @endforeach
                </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Termination notification</label>
                <div class="p-relative">
                    <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <input type="number" name="termination_notification" class="form-control" id="simpleFormEmail" value="{{ isset($landlordContract->termination_notification)?$landlordContract->termination_notification:'' }}"  placeholder="Enter No. of days" min="1">
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
                <div class="col-md-6 align-right">
                     <input type="hidden" name="sale_enquiry_id" value="{{$landlordContract->sale_enquiry_id}}">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i>Save</button>
                    <button type="reset" value="Reset" class="btn btn-primary"><i class="fa fa-times" aria-hidden="true"></i>Cancel</button>
                    
                </div>
            </div>
        </div>
 </form>
    </div>
 <div class="clearfix"></div>
                                            
</div>
</div>
</div>
<!-- The Modal -->
<div class="modal" id="myModal">
    
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
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
    }});
    
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

});
</script>

@endsection
