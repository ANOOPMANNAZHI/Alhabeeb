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
              <div class="page-title">Reason</div>
          </div>
          {{ (isset($reason))?   Breadcrumbs::render('reason.edit',$reason,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('reason.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($reason)? route('reason.store'): route('reason.update',$reason->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($reason)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)?$previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Code<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormEmail"  placeholder="Enter Reason Code" name="reasons_code" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($reason)?  old('reasons_code',$reason->reasons_code): old('reasons_code')}}" data-rule-maxlength="10" data-msg-maxlength="Maximum 10 Characters Allowed">
              </div>
            </div>
          </div>
        <div class="w-100">
         <div class="col-sm-12">
            <div class="form-group">
                <label for="simpleFormDesc">Description</label>
                 <div class="p-relative">
                <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
               <textarea name="reasons_desc" class="form-control" placeholder="Enter Reason Description">{{ isset($reason)?  old('reasons_desc',$reason->reasons_desc): old('reasons_desc')}}</textarea>
             </div>
                
            </div>
        </div>
         
       <div class="w-100">
         
       </div>
        
        <div class="col">
          <div class="w-100"></div>
              <button type="submit" class="btn btn-primary">SAVE</button>
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
