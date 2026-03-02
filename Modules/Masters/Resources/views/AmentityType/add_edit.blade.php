@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection 
@section('content')
	<!-- start widget -->
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Amenity Type</div>
          </div>
          {{ (isset($amentityType))?   Breadcrumbs::render('amentityType.edit',$amentityType,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('amentityType.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($amentityType)? route('amentityType.store'): route('amentityType.update',$amentityType->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($amentityType)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)?$previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group ">
                <label for="simpleFormName">Name<small class="textRed">*</small></label>
                 <div class="p-relative">
					 <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control " id="simpleFormName"  placeholder="Enter Amenity Type Name" name="amentity_types_name" required  value="{{ isset($amentityType)?  old('amentity_types_name',$amentityType->amentity_types_name): old('amentity_types_name')}}" data-rule-maxlength="25" data-msg-maxlength="Only allowes 25 Characters">
                </div>
            </div>
          </div>
          <div class="w-100"></div>
         <div class="col-sm-12">
            <div class="form-group">
                <label for="simpleFormDesc">Description</label>
                <div class="p-relative">
					 <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <textarea name="amentity_types_desc" id="simpleFormDesc"  class="form-control" placeholder="Enter Amenity Type Description">{{ isset($amentityType)?  old('amentity_types_desc',$amentityType->amentity_types_desc): old('amentity_types_desc')}}</textarea>
                <!-- <input type="text" class="form-control" id="simpleFormEmail"  placeholder="Enter Amentity Type Description" name="amentity_types_desc" value="{{ isset($amentityType)?  old('amentity_types_desc',$amentityType->amentity_types_desc): old('amentity_types_desc')}}"> -->
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
