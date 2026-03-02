@extends('layouts.plms-app')

@section('content')
	<!-- start widget --> 
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Vendor</div>
          </div>

           {{ (isset($vendor))?   Breadcrumbs::render('vendors.edit',$vendor,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('vendors.create') }}
         
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($vendor)? route('vendors.store'): route('vendors.update',$vendor->id)}}" autocomplete="off" method="POST" id="form_sample_2" class="form-horizontal" >
{{csrf_field()}} @if(isset($vendor)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)? $previousUrl:''}}" >
<input type="hidden" name="vendor_id" id="vendor_id" value="{{ old('id', isset($vendor)? $vendor->id : '' )}}">


<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
	 <div class="row">
	 @if(empty($vendor->id))
		<div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_contact_no">Vendor Type <small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                <select class="form-control" id="vendor_type"  name="vendor_type" required>
                  <option value="">Select Vendor Type</option>
                  @foreach($vendor_type as $val)
                   <option  {{(old('vendor_type', isset($vendor)?  $vendor->vendor_type_id : 0) == $val->id) ? 'selected' : '' }} value="{{$val->id}}">{{$val->vendor_types_name}}</option>
                  @endforeach                  
                </select>  
                </div>              
            </div>
          </div>  
         @else
         
			<input type="hidden" name="vendor_type" value="{{$vendor->vendor_type_id}}" />
         @endif  
       
          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_code">Vendor Code </label>
                 <div class="p-relative">
                <i class="fa fa-user  icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="vendor_code"  name="vendor_code" required  value="{{ old('vendor_code', isset($vendor)?  $vendor->vendor_code : '' )}}">
              </div>
              <div class="error1" style="display:none">Vendor Code Exists !</div>
            </div>
          </div>


          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_name">Name <small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="vendor_name"  name="vendor_name" required  value="{{ old('vendor_name', isset($vendor)?  $vendor->vendor_name : '' )}}" {{isset($vendor)? (($vendor->vendor_name =='inhouse')? 'readonly': ''):''}} placeholder="Enter Vendor Name">
              </div>
            </div>
          </div>
		      <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_contact_person">Contact Person <small class="textRed">*</small></label>
                 <div class="p-relative">
               <i class="fa fa-id-card icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="vendor_contact_person"  name="vendor_contact_person" required  value="{{ old('vendor_contact_person', isset($vendor)?  $vendor->vendor_contact_person : '' )}}" placeholder="Enter Contact Person">
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_contact_no">Contact Number <small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
                <input type="text" onkeypress="return isNumber(event)"   required class="form-control mob_validation_13 mob_oman_13" id="vendor_contact_no"  name="vendor_contact_no"    value="{{ old('vendor_contact_no', isset($vendor)?  $vendor->vendor_contact_no : '00968' )}}" placeholder="Enter Contact Number">
              </div>
            </div>
          </div>

           <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_contact_email">Contact Email<small class="textRed">*</small> </label>
                 <div class="p-relative">
                <i class="fa fa-envelope-o icn-add" aria-hidden="true"></i>
                <input type="email" class="form-control" id="vendor_contact_email"  name="vendor_contact_email" required  value="{{ old('vendor_contact_email', isset($vendor)?  $vendor->vendor_contact_email : '' )}}" placeholder="Enter Contact Email">
              </div>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_fax_no">Fax No.</label>
                 <div class="p-relative">
                <i class="fa fa-fax icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="vendor_fax_no"  name="vendor_fax_no" value="{{ old('vendor_fax_no', isset($vendor)?  $vendor->vendor_fax_no : '' )}}" placeholder="Enter Fax No." pattern="^[a-zA-Z0-9_]*$" maxlength="50">
              </div>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_status">Status<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-anchor icn-add" aria-hidden="true"></i>
                <select class="form-control" id="vendor_status"  name="vendor_status" required>
                <option value="">Select </option>                  
                <option  {{(old('vendor_status', isset($vendor)?  $vendor->vendor_status : '') == 1) ? 'selected' : '' }} value="1">Active</option>
                <option  {{(old('vendor_status', isset($vendor)?  $vendor->vendor_status : -1) == 0) ? 'selected' : '' }} value="0">Inactive</option>                                
                </select>
                </div>                
            </div>
          </div>          
         
       <div class="w-100"></div>                  
      </div>
</div>
<div class="clearfix"></div>
  <h4>Contact Details</h4>
    <div class="dataSearchBox">
      <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_contact_address">Contact Address<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                <textarea  class="form-control" id="vendor_contact_address"  name="vendor_contact_address" required placeholder="Enter Contact Address"   >{{ old('vendor_contact_address', isset($vendor)?  $vendor->vendor_contact_address : '' )}}</textarea>
              </div>
            </div>
          </div> 


          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_secondary_address">Contact Secondary Address </label>
                 <div class="p-relative">
               <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                <textarea  class="form-control" id="vendor_secondary_address"  name="vendor_secondary_address" placeholder="Enter Secondary Address"   >{{ old('vendor_secondary_address', isset($vendor)?  $vendor->vendor_secondary_address : '' )}}</textarea>
              </div>
            </div>
          </div>
          <div class="w-100"></div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_pc">PostCode <small class="textRed">*</small></label>
                 <div class="p-relative">
               <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="vendor_pc"  name="vendor_pc" required  value="{{ old('vendor_pc', isset($vendor)?  $vendor->vendor_pc : '' )}}" placeholder="Enter PostCode"  >
              </div>
            </div>
          </div>
           <div class="col-sm-6">
            <div class="form-group">
                <label for="location_id">Location<small class="textRed">*</small> </label>
                 <div class="p-relative">
                <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                <select class="form-control" id="location_id"  name="location_id" required>
                  <option value="">Select Location</option>
                  @foreach($location as $val)
                   <option  {{(old('location_id', isset($vendor)?  $vendor->location_id : 0) == $val->id) ? 'selected' : '' }} value="{{$val->id}}">{{$val->locations_name}}</option>
                  @endforeach                  
                </select> 
                </div>               
            </div>
          </div>
          <div class="w-100"></div>

      </div>
    </div>
    <div class="clearfix"></div>
  <h4>Bank Details</h4>
    <div class="dataSearchBox">
      <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="bank_id">Bank<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-university icn-add" aria-hidden="true"></i>
                <select class="form-control" id="bank_id"  name="bank_id" required>
                  <option value="">Select Bank</option>
                  @foreach($bank as $val)
                   <option  {{(old('bank_id', isset($vendor)?  $vendor->bank_id : 0) == $val->id) ? 'selected' : '' }} value="{{$val->id}}">{{$val->bank_name}}</option>
                  @endforeach                  
                </select> 
                </div>               
            </div>
          </div>   

          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_acc_no">Account No.<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-credit-card icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="vendor_acc_no"  name="vendor_acc_no" required  value="{{ old('vendor_acc_no', isset($vendor)?  $vendor->vendor_acc_no : '' )}}" placeholder="Enter Account No." pattern="^[a-zA-Z0-9_]*$" maxlength="50">
              </div>
            </div>
          </div>
          <div class="w-100"></div>

      </div>
    </div>
      
        <div class="w-100"></div>
         <!-- <button type="submit" class="btn btn-primary">Save</button>-->
      <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
<div class="clearfix"></div>
</form>
    
</div>
</div>
</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {
    $("#form_sample_2").validate()
	
     $("#vendor_code").keyup(function(){
      var vendor_code = $("#vendor_code").val();
      var vendor_id = $("#vendor_id").val();
      if($("#vendor_code").val().length >= 3 ){
        $.ajax({
        method: "POST",
        url: "{{route('checkVendorCodeExist')}}",
        data: { vendor_code: vendor_code, vendor_id: vendor_id,
          "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){
           //alert(data);
           if(data > 0){
          $(".error1").css("display","block").css("color","red");
          $("#submitBtn").prop('disabled',true);
          }else{
           $(".error1").css("display","none");
           $("#submitBtn").prop('disabled',false);
         }

         }
       });
    }
    });
  });
</script>
@endsection
