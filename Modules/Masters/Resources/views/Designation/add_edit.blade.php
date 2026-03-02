@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection 
@section('content')
  <!-- start widget -->

   <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Designation</div>
          </div>
          {{ (isset($designation))?   Breadcrumbs::render('designation.edit',$designation,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('designation.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($designation)? route('designation.store'): route('designation.update',$designation->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($designation)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)?$previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormCode">Designation Code<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormCode"  placeholder="Enter Designation Code" name="designation_code" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($designation)?  old('designation_code',$designation->designation_code): old('designation_code')}}" data-rule-maxlength="10" data-msg-maxlength="Maximum 10 Characters Allowed">
              </div>
            </div>
          </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormName">Designation name<small class="textRed">*</small></label>
               <!--  <textarea name="building_type_desc" class="form-control" placeholder="Enter Building Type Description"></textarea> -->
                <div class="p-relative">
                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="simpleFormName"  placeholder="Enter Designation Name" name="designation_name" required value="{{ isset($designation)?  old('designation_name',$designation->designation_name): old('designation_name')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
        </div>
         
       <div class="w-100">
         
       </div>
        
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
