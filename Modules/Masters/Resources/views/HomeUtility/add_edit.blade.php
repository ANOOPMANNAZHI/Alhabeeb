@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection 
@section('content')
	<!-- start widget -->
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Home Amenities</div>
          </div>
          {{ (isset($homeUtility))?   Breadcrumbs::render('homeUtility.edit',$homeUtility,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('homeUtility.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($homeUtility)? route('homeUtility.store'): route('homeUtility.update',$homeUtility->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($homeUtility)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)?$previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormCode">Utility<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormCode"  placeholder="Enter Utility  Code" name="home_utilities_code" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($homeUtility)?  old('home_utilities_code',$homeUtility->home_utilities_code): old('bank_code')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>    
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormCode">Category<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-anchor icn-add" aria-hidden="true"></i>
                <select class="form-control" id="category"  name="category" required>
                <option value="">Select </option>                  
                <option  {{(old('category', isset($homeUtility)?  $homeUtility->category : '') == 1) ? 'selected' : '' }} value="1">
                Asset
                </option>
                <option  {{(old('category', isset($homeUtility)?  $homeUtility->category : '' ) == 2) ? 'selected' : '' }} value="2">
                Amenity
                </option>                                
                </select> 
                </div>               
            </div>
          </div> 
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormMake">Make</label>
                 <div class="p-relative">
                <i class="fa fa-mars icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormMake"  placeholder="Enter Utility Make" name="home_utilities_make" patten="[ A-Za-z_@./#&+-]+" value="{{ isset($homeUtility)?  old('home_utilities_make',$homeUtility->home_utilities_make): old('home_utilities_make')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>
          
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormModel">Model</label>
                 <div class="p-relative">
                <i class="fa fa-info-circle icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormModel"  placeholder="Enter Utility Model " name="home_utilities_model"  patten="[ A-Za-z_@./#&+-]+" value="{{ isset($homeUtility)?  old('home_utilities_model',$homeUtility->home_utilities_model): old('home_utilities_model')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>
    <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormNo">Serial No</label>
                 <div class="p-relative">
                <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormNo"  placeholder="Enter Serial No" name="home_utilities_serial_no"  patten="[ A-Za-z_@./#&+-]+" value="{{ isset($homeUtility)?  old('home_utilities_serial_no',$homeUtility->home_utilities_serial_no): old('home_utilities_serial_no')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>
        <div class="w-100"></div>
        <div class="col-sm-12">
            <div class="form-group">
                <label for="simpleFormDesc">Description</label>
                 <div class="p-relative">
                <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <textarea name="home_utilities_desc" class="form-control" placeholder="Enter Utility Description">{{ isset($homeUtility)?  old('home_utilities_desc',$homeUtility->home_utilities_desc): old('home_utilities_desc')}}</textarea>
                </div>
            </div>
          </div>
         <div class="w-100"></div>
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
<script>
  $(document).ready(function() {
    $("#form_sample_2").validate()
  });
</script>
@endsection
