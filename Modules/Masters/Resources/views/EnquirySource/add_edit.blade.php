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
              <div class="page-title">Enquiry Source</div>
          </div>
           {{ (isset($enquirySource))?   Breadcrumbs::render('enquirySource.edit',$enquirySource,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('enquirySource.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($enquirySource)? route('enquirySource.store'): route('enquirySource.update',$enquirySource->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data"  >
{{csrf_field()}} @if(isset($enquirySource)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)?$previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Enquiry Source Name<small class="textRed">*</small></label>
                 <div class="p-relative">
					 <i class="fa fa-envelope icn-add" aria-hidden="true"></i>
						<input  type="text" class="form-control" id="simpleFormEmail"  placeholder="Enter Enquiry Source Name" name="enquiry_sources_name" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($enquirySource)?  old('enquiry_sources_name',$enquirySource->enquiry_sources_name): old('enquiry_sources_name')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
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
