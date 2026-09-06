@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<style type="text/css">
    input[type=date]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        display: none;
    }
</style>
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Landlord Contract Renewal {{ (isset($landlordContract))? 'Edit' : 'Create'}}</div>
        </div>
         {{ Breadcrumbs::render('landlordRenewalEdit',$landlordContract) }} 
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
           <form action="{{route( 'landlordRenewalNewContractUpdate',$landlordContract->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
               {{csrf_field()}}
               <input type="hidden" name="landlord_contract_id" value="{{$landlordContract->id}}">

   <!--  <div class="row align-right">

                <div class="col">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i>Save</button>
                    <button type="reset" class="btn btn-primary"><i class="fa fa-times" aria-hidden="true"></i>
Cancel</button>
                    
        </div>
    </div> -->
    <div class="sub-head">Agreement Details</div>
    <div class="dataSearchBox sub-box ">
        
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group ">
                    <label for="landlord_contract_no">Agreement No <small class="textRed">*</small></label>
                    <div class="p-relative">
                        <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                        <input type="text"  class="form-control" id="landlord_contract_no" name="landlord_contract_no" value="{{ old('amc_contract_no', isset($landlordContract)? $landlordContract->landlord_contract_no : '' )}}" required readonly>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group ">
                    <label for="landlord_contract_old_no">Old Agreement No <small class="textRed">*</small></label>
                    <div class="p-relative">
                        <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                        <input type="text"  class="form-control" id="landlord_contract_old_no" name="landlord_contract_old_no" value="{{$renewal->oldLandlordContract->landlord_contract_no}}" required readonly>
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

    <div class="sub-head">Landlord Details</div>
    <div class="dataSearchBox sub-box ">
        
        <div class="row">
          <!--   <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_id">Landlord Code</label>
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
        </div> -->
        <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_name">Landlord Name</label>
                <div class="p-relative">
                    <i class="icon icon-landlord" aria-hidden="true"></i>
                    <input type="text" class="form-control" id="vendor_name" name="vendor_name" value="{{$landlordContract->vendorInfo->vendor_name}}" placeholder="Enter Landlord Name" readonly>
                </div>
            </div>
        </div>
        <!-- <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="building_id">Build Code</label>
                <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                <select class="form-control" id="building_id" name="building_id" required="">
                    <option value="">Select Building Code</option>
                    @foreach($buildingList as $build)
                        <option {{isset($landlordContract)?(($landlordContract->building_id== $build->id)?'selected':''):''}} value={{$build->id}}>{{$build->building_code}}</option>
                    @endforeach
                </select>
            </div>
               
            </div>
        </div> -->
        <div class="col-sm-6">
            <div class="form-group">
                <label for="building_name">Building Name</label>
                <div class="p-relative">
                    <i class="icon icon-building" aria-hidden="true"></i>
                    <input type="text" class="form-control" id="building_name" name="building_name" value="{{$landlordContract->buildingInfo->building_name}}" placeholder="Enter Building Name" readonly>
                </div>
            </div>
        </div>
        <!-- <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_address">Landlord Contract Address</label>
                <div class="p-relative">
                    <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                <textarea type="text" class="form-control" placeholder="Enter Contract Address" name="landlord_contract_address">{{$landlordContract->landlord_contract_address}}</textarea>
            </div>
            </div>
        </div> -->
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
        <!-- <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_amt">Amount<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="landlord_contract_amt"  placeholder="Enter Amount" name="landlord_contract_amt" value="{{ isset($landlordContract->landlord_contract_amt)?$landlordContract->landlord_contract_amt:'' }}"  maxlength="12"  data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values" required >
                  </div>               
            </div>
        </div> -->


        <div class="col-sm-6">
            <div class="form-group">
                <label>Management Type<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <select class="form-control" id="management_id" name="management_id" required="">
                        <option value="">Select Management Type</option>
                        @foreach($managementType as $manage)
                        <option {{ (old('management_id',$landlordContract->management_id) == $manage->id) ? 'selected':'' }} value={{$manage->id}}>{{$manage->management_types_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div> 
        </div>
        <!-- <div class="col-sm-6">
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
                <label for="landlord_contract_percentage">Percentage<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-percent icn-add" aria-hidden="true"></i>
                <input type="phone" class="form-control" id="landlord_contract_percentage" value="{{ isset($landlordContract->landlord_contract_percentage)?$landlordContract->landlord_contract_percentage:'' }}" id="landlord_contract_management_fee" name="landlord_contract_percentage" placeholder="Enter Percentage">
            </div>
            </div>
        </div> -->




        <div class="col-sm-6" id="comprehensive_field"  {{ (old('management_id', (isset($landlordContract->management_id)? $landlordContract->management_id : 0) ) != 1)? 'style=display:none;': '' }} >
            <div class="form-group">
                <label for="landlord_contract_amt">Amount<small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                        <input {{ isset($landlordContract->landlord_contract_amt)? (($landlordContract->management_id == 1)?'':'disabled'):'disabled' }} type="text" class="form-control" id="landlord_contract_amt"  placeholder="Enter Amount" name="landlord_contract_amt" value="{{ isset($landlordContract->landlord_contract_amt)?$landlordContract->landlord_contract_amt:'' }}"  maxlength="12"  data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values" required >
                 </div>                
            </div>
        </div>
 
        
         <div class="col-sm-6 normal_commission_field" {{ (old('management_id', (isset($landlordContract->management_id)? $landlordContract->management_id : 0) ) == 1)? 'style=display:none;': '' }}        >
            <div class="form-group">
                <label>Management Fees Type</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="management_fee_type" name="management_fee_type" >
                    <option value="">Select Management Fees Type</option>
                    <option value=1 {{ isset($landlordContract->management_fee_type)? (($landlordContract->management_fee_type==1)?'SELECTED':''):'' }} >Management Fees</option>
                    <option value=2 {{ isset($landlordContract->management_fee_type)? (($landlordContract->management_fee_type==2)?'SELECTED':''):'' }} >Maintenance</option>
                    <option value=3 {{ isset($landlordContract->management_fee_type)? (($landlordContract->management_fee_type==3)?'SELECTED':''):'' }}>Legal</option>
                    <option value=4 {{ isset($landlordContract->management_fee_type)? (($landlordContract->management_fee_type==4)?'SELECTED':''):'' }}>Caretakers Fee</option>
                 </select>
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



        <div class="col-sm-6 normal_commission_field"  {{ (old('management_id', (isset($landlordContract->management_id)? $landlordContract->management_id : 0) ) == 1)? 'style=display:none;': '' }}        >
            <div class="form-group">
                <label for="management_method">Management Fees</label>
                 <div class="p-relative">                  
                        <input type="radio"  id="management_method_1" name="management_method" value ="1" {{ (old('management_method',$landlordContract->management_method) == 1)? 'CHECKED':'' }} class="management_method"> Percentage
                        <input type="radio"  id="management_method_2" name="management_method" value ="2" {{ (old('management_method',$landlordContract->management_method) == 2)? 'CHECKED':'' }} class="management_method"> Amount
            </div>
            </div>
        </div>
       <div class="w-100"></div>
       <div class="col-sm-6 percentage_field_value" {{ (old('management_id', (isset($landlordContract->management_id)? $landlordContract->management_id : 0) ) == 1)? 'style=display:none;': '' }}        >
            <div class="form-group">
                <label for="simpleFormEmail">Management Value</label>
                 <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_management_fee" name="landlord_contract_management_fee" value="{{old('landlord_contract_management_fee', $landlordContract->landlord_contract_management_fee) }}" placeholder="Enter fees type" min="1" max="100">
            </div>
            </div>
        </div>
 
         
       <div class="col-sm-6 percentage_type"  
         {{ (old('management_id',$landlordContract->management_id) == 1 ) ? 'style=display:none;': '' }} 
         {{  (old('management_method',$landlordContract->management_method) == 2)  ?  'style=display:none;': ''}} >
            <div class="form-group percentage_type">
                <label for="landlord_contract_percentage">Percentage type</label>
                 <div class="p-relative">
                    <i class="fa fa-percent icn-add" aria-hidden="true"></i>
                    <select class="form-control" id="landlord_contract_percentage" name="landlord_contract_percentage">
                        <option value="">Select Percentage type</option>
                        <option value=1  {{ (old('landlord_contract_percentage',$landlordContract->landlord_contract_percentage) == 1)? 'SELECTED':'' }}   >Rent Income</option>
                        <option value=2   {{ (old('landlord_contract_percentage',$landlordContract->landlord_contract_percentage) == 2)? 'SELECTED':'' }} >Rent Collection</option>
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
                <input type="date"  name="start_date" class="form-control" id="start_date" placeholder="Enter Start Date" value="{{ isset($landlordContract->start_date)?$landlordContract->start_date->format('Y-m-d'):'' }}" min="1">
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
       <!--  <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_duration">Duration in Month</label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="number" name="landlord_duration" class="form-control" id="landlord_duration" placeholder="Enter Duration" value="{{ isset($landlordContract->landlord_contract_duration)?$landlordContract->landlord_contract_duration:'' }}">
            </div>
            </div>
        </div> -->
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
                <label for="landlord_contract_valid_from_date">Valid From<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="date" class="form-control" id="landlord_contract_valid_from_date" name="landlord_contract_valid_from_date" placeholder="Enter Valid From" value="{{ isset($landlordContract->landlord_contract_valid_from_date)?$landlordContract->landlord_contract_valid_from_date->format('Y-m-d'):'' }}" required>
                </div>
            </div>
        </div>
        <div class="col-sm-6" id="landlord_contract_valid_to_date_div"   {{ (old('duration_type', (isset($landlordContract->landlord_contract_duration)? $landlordContract->landlord_contract_duration : 0) ) ==1)? 'style=display:none;': '' }}   >
            <div class="form-group">
                <label for="landlord_contract_valid_to_date">Valid To<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="date" name="landlord_contract_valid_to_date" class="form-control" id="landlord_contract_valid_to_date" placeholder="Enter Valid To" value="{{ isset($landlordContract->landlord_contract_valid_to_date)?$landlordContract->landlord_contract_valid_to_date->format('Y-m-d'):'' }}" required>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_marketing_executive">Marketing Executive Name<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-user-o icn-add" aria-hidden="true"></i>
                    
                    <select class="form-control" name="landlord_marketing_executive" required="">
                        <option value="">Select Marketing Executive</option>
                        @foreach($employeeList as $emp)
                        <option {{isset($landlordContract)?(($landlordContract->landlord_marketing_executive == $emp->id)?'selected':''):''}} value={{$emp->id}}>{{$emp->employee_name.'('.$emp->employee_code.')'}}</option>
                        @endforeach
                    </select>
                </div>
                
            </div>
        </div>
        <!-- <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Termination notification</label>
                <div class="p-relative">
                    <i class="fa fa-times-circle-o icn-add" aria-hidden="true"></i>
                <input type="number" name="termination_notification" class="form-control" id="simpleFormEmail" value="{{ isset($landlordContract->termination_notification)?$landlordContract->termination_notification:'' }}"  placeholder="Enter No. of days" min="1">
            </div>
            </div>
        </div> -->
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
        <div class="row align-right">
            <div class="col-md-6">
                
            </div>
            <div class="col-md-6 align-right">
               <input type="hidden" name="sale_enquiry_id" value="{{$landlordContract->sale_enquiry_id}}">
               <button type="submit" class="btn btn-primary">Save</button>
               <!-- <button type="reset" value="Reset" class="btn btn-primary"><i class="fa fa-times" aria-hidden="true"></i>Cancel</button> -->
               
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
<script>
     $(document).ready(function() {
@if($isYearCorrect == false)
        alert("Current Year Is Not Match With the Sequence Year");
        @endif

var current_management_method  =   $('.management_method').val();
//alert(current_management_method);
//$("#management_method_"+current_management_method).trigger("click");

$("#management_method_"+current_management_method +' input:radio').trigger("click");


        $.validator.addMethod("greaterThan", 
    function(value, element, params) {
      
        if (!/Invalid|NaN/.test(new Date(value))) {
            return new Date(value) > new Date($(params).val());
        }

        return isNaN(value) && isNaN($(params).val()) 
            || (Number(value) > Number($(params).val())); 
    },'Must be greater than From Date.');
    $("#form_sample_2").validate({
            rules: {
                landlord_contract_valid_to_date: { greaterThan: "#landlord_contract_valid_from_date" ,
                
            }
              

        }
    });

       $("#form_sample_2").validate();
       $("#form_sample_2").on('submit',function(e){
          var landlord_contract_valid_to_date = new Date($("#landlord_contract_valid_to_date").val());
          var today = new Date();
          if(landlord_contract_valid_to_date < today){
            var validator = $( "#form_sample_2" ).validate();
            validator.showErrors({
              "landlord_contract_valid_to_date": "Valid To Date Must be Greater Today"
          });
            e.preventDefault();
            return false;
        }

    });



 


$(".management_method").on('click', function(e) {   
        alert('dgdgdfg');
        var management_method = $(this).val();

        if(current_management_method != management_method){
             $("#landlord_contract_management_fee").val('');
             current_management_method =  management_method;
        }


    
        if(management_method == 1){
            $("#landlord_contract_management_fee").attr('required','required');
            $("#landlord_contract_percentage").attr('required','required');         
            $(".percentage_field_value").show();
            $(".percentage_type").show();
            $("#landlord_contract_management_fee_label").html('Management Value<small class="textRed">*</small>');
            $("#landlord_contract_management_fee").attr('placeholder','Enter Management Value');
          //  $("#landlord_contract_management_fee").val('');
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
          //  $("#landlord_contract_management_fee").val('');
            $("#landlord_contract_management_fee").attr("max",999999999);
            $("#landlord_contract_management_fee-error").hide();
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

       
   });
     /************************************************************/ 
  

    
</script>

@endsection
