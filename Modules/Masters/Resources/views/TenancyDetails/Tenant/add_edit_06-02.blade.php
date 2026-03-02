@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/css/bootstrap-select.min.css">

@section('content')
	<!-- start widget -->
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Tenant</div>
          </div>
         {{ (isset($tenant))?   Breadcrumbs::render('tenants.edit',$tenant) :  Breadcrumbs::render('tenants.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($tenant)? route('tenants.store'): route('tenants.update',$tenant->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($tenant)){{method_field('PUT')}}@endif

<!-- <div class="sub-head">Building Type Details</div> -->
<h4>Tenant Details</h4>
<div class="dataSearchBox ">
    
        <div class="row">

          
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormCode">Name<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormCode"  placeholder="Enter Tenant Name" name="tenant_name" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($tenant)?  old('tenant_name',$tenant->tenant_name): old('tenant_name')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="resident_id">Resident Id</label>
                 <div class="p-relative">
                <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="resident_id"  placeholder="Enter Resident Id" name="resident_id" value="{{ isset($tenant)?  old('resident_id',$tenant->resident_id): old('resident_id')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>
          <div class="w-100"></div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Mobile No<small class="textRed">*</small></label>
               <!--  <textarea name="building_type_desc" class="form-control" placeholder="Enter Building Type Description"></textarea> -->
                <div class="p-relative">
                <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="simpleFormEmail" required  placeholder="Enter Mobile No" name="tenant_contact_no" value="{{ isset($tenant)?  old('tenant_contact_no',$tenant->tenant_contact_no): old('tenant_contact_no')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
        </div>
         
      
       <div class="col-sm-6">
          <div class="form-group">
              <label for="tenant_contact_email">Email</label>
               <div class="p-relative">
                <i class="fa fa-envelope-o icn-add" aria-hidden="true"></i>
              <input  type="text" class="form-control" id="tenant_contact_email"  placeholder="Enter Tenant Email" name="tenant_contact_email"  patten="[ A-Za-z_@./#&+-]+" value="{{ isset($tenant)?  old('tenant_contact_email',$tenant->tenant_contact_email): old('tenant_contact_email')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
            </div>
          </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_type_id">Tenant Type </label>
                 <div class="p-relative">
                <i class="fa fa-university icn-add" aria-hidden="true"></i>
                  <select class="form-control"  id="tenant_type_id"  name="tenant_type_id" >
                  <option value=""> Select Tenant Type </option>
                    @foreach($tenantTypes as $tenantType)
                     <option  {{(old('tenant_type_id', isset($tenant)?  $tenant->tenant_type_id : 0) == $tenantType->id) ? 'selected' : '' }} value="{{$tenantType->id}}">{{$tenantType->tenant_types_name}}</option>
                    @endforeach                  
                </select> 
                </div>               
            </div>
          </div>
        <div class="col-sm-6">
          <div class="form-group">
              <label for="com_reg_no">Commercial Reg. No</label>
               <div class="p-relative">
                <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
              <input  type="text" class="form-control" id="com_reg_no"  placeholder="Enter Commercial Reg No" name="com_reg_no" value="{{ isset($tenant)?  old('com_reg_no',$tenant->com_reg_no): old('com_reg_no')}}">
            </div>
          </div>
        </div>
         <div class="w-100"></div>
        <div class="col-sm-6">
              <div class="form-group">
                  <label>Nationality<small class="textRed">*</small></label>
                  <div class="p-relative">
                  <i class="fa fa-money icn-add" aria-hidden="true"></i>
                  <select required class="form-control" name="nationality" id="nationality">
                      <option value="">Select Nationality</option> 
                      @foreach($nationalities as $nationality)
                      <option {{ isset($tenant)? ((old('nationality',$tenant->nationalities_id) == $nationality->nationalityid)? 'selected' : '') : ''}} value="{{$nationality->nationalityid}}" >{{$nationality->nationality}}</option>
                      @endforeach
                  </select>
                  </div>
              </div> 
          </div>

          <div class="col-sm-6">
                <div class="form-group">
                    <label for="tenant_company_name">Company Name</label>
                     <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="phone" class="form-control" id="tenant_company_name" placeholder="Enter Company Name" name="tenant_company_name" value="{{ isset($tenant)?  old('tenant_company_name',$tenant->tenant_company_name): old('tenant_company_name','')}}">
                </div>
                </div>
            </div>
          <div class="w-100"></div>
       </div>
        

     <!--  <div class="w-100"></div>
          <button type="submit" class="btn btn-primary">SAVE</button> -->
           
      </div>
    <div class="clearfix"></div>
    <h4>Contact Details</h4>
    <div class="dataSearchBox">
      <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contact_address">Contact Address</label>
                 <div class="p-relative">
                <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                <textarea name="tenant_contact_address" class="form-control" placeholder="Enter Contact Address ">{{ isset($tenant)?  old('tenant_contact_address',$tenant->tenant_contact_address): old('tenant_contact_address')}}</textarea>
                </div>
            </div>
        </div> 
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contact_address">Secondary Address</label>
                 <div class="p-relative">
                <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                <textarea name="tenant_secondary_address" class="form-control" placeholder="Enter Secondary Address ">{{ isset($tenant)?  old('tenant_secondary_address',$tenant->tenant_secondary_address): old('tenant_secondary_address')}}</textarea>
                </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
          <div class="form-group">
              <label for="simpleFormCode">Postal Code</label>
               <div class="p-relative">
                <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
              <input  type="text" class="form-control" id="simpleFormCode"  placeholder="Enter Postal Code" name="tenant_pc" patten="[ A-Za-z_@./#&+-]+" value="{{ isset($tenant)?  old('tenant_pc',$tenant->tenant_pc): old('tenant_pc')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
            </div>
          </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="location_id">Location<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="location_id"  name="location_id" required>
                  <option value=""> Select Location </option>
                    @foreach($locations as $location)
                     <option  {{(old('location_id', isset($tenant)?  $tenant->location_id : 0) == $location->id) ? 'selected' : '' }} value="{{$location->id}}">{{$location->locations_name}}</option>
                    @endforeach                  
                </select> 
                </div>               
            </div>
          </div>
          <div class="w-100"></div>
          <div class="col-sm-6">
          <div class="form-group">
              <label for="tenant_contact_person">Contact Person</label>
               <div class="p-relative">
                <i class="fa fa-id-card icn-add" aria-hidden="true"></i>
              <input  type="text" class="form-control" id="tenant_contact_person"  placeholder="Enter Contact Person" name="tenant_contact_person" value="{{ isset($tenant)?  old('tenant_contact_person',$tenant->tenant_contact_person): old('tenant_contact_person')}}">
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
              <label for="tenant_fax_no">Fax No</label>
               <div class="p-relative">
                <i class="fa fa-fax icn-add" aria-hidden="true"></i>
              <input  type="text" class="form-control" id="tenant_fax_no"  placeholder="Enter Fax No" name="tenant_fax_no" value="{{ isset($tenant)?  old('tenant_fax_no',$tenant->tenant_fax_no): old('tenant_fax_no')}}">
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
                <label for="unit_id">Bank</label>
                 <div class="p-relative">
                <i class="fa fa-university icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="bank_id"  name="bank_id">
                  <option value=""> Select Bank </option>
                    @foreach($banks as $bank)
                     <option  {{(old('bank_id', isset($tenant)?  $tenant->bank_id : 0) == $bank->id) ? 'selected' : '' }} value="{{$bank->id}}">{{$bank->bank_name}}</option>
                    @endforeach                  
                </select>  
                </div>              
            </div>
          </div>
          <div class="col-sm-6">
          <div class="form-group">
              <label for="tenant_acc_no">Account No</label>
               <div class="p-relative">
                <i class="fa fa-credit-card icn-add" aria-hidden="true"></i>
              <input  type="text" class="form-control" id="tenant_acc_no"  placeholder="Enter Account No" name="tenant_acc_no" value="{{ isset($tenant)?  old('tenant_acc_no',$tenant->tenant_acc_no): old('tenant_acc_no')}}">
            </div>
          </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
          <div class="form-group">
              <label for="gsm_no">GSM No</label>
               <div class="p-relative">
                <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
              <input  type="text" class="form-control" id="gsm_no"  placeholder="Enter GSM No" name="gsm_no" value="{{ isset($tenant)?  old('gsm_no',$tenant->gsm_no): old('gsm_no')}}">
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
              <label for="passport_no">Passport No</label>
               <div class="p-relative">
                <i class="fa fa-id-card icn-add" aria-hidden="true"></i>
              <input  type="text" class="form-control" id="passport_no"  placeholder="Enter Passport No" name="passport_no" value="{{ isset($tenant)?  old('passport_no',$tenant->passport_no): old('passport_no')}}">
            </div>
          </div>
        </div>
        <div class="w-100"></div>
        <!-- <div class="col-sm-6">
              <div class="form-group">
                  <label>Nationality<small class="textRed">*</small></label>
                  <div class="p-relative">
                  <i class="fa fa-money icn-add" aria-hidden="true"></i>
                  <select class="selectpicker form-control" id="select-country" name="nation" data-live-search="true">
                <option data-tokens="china">China</option>
                <option data-tokens="malayasia">Malayasia</option>
                <option data-tokens="singapore">Singapore</option>
                </select>
                  </div>
              </div> 
          </div> -->



      </div>
    </div>
    <div class="row">
        <div class="col">
             <div class="w-100"></div>
           <button type="submit" class="btn btn-primary">Save</button>
        </div>
        </div> 
    
</div>


<div class="clearfix"></div>
</form>
    
</div>
</div>
</div>
@endsection
@section('scripts')
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/js/bootstrap-select.min.js"></script>

<script>
  $(document).ready(function() {
    $("#form_sample_2").validate();
    $('.selectpicker').selectpicker();
  });

  

</script>
@endsection
