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
              <div class="page-title"> Complaint Reason</div>
          </div>
          {{ (isset($complaintReason))?   Breadcrumbs::render('complaintReason.edit',$complaintReason,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('complaintReason.create') }}
      </div>
  </div>
<div class="row">

<div class="col">

<div class="card card-box salesSearchBox">

<form action="{{ !isset($complaintReason)? route('complaintReason.store'): route('complaintReason.update',$complaintReason->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($complaintReason)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)? $previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">

          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormName">Complaint Name<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormName"  placeholder="Enter complaint Name" name="complaint_reason_name" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($complaintReason)?  old('complaint_reason_name',$complaintReason->complaint_reason_name): old('complaint_reason_name')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormDesc">Complaint Description</label>
               <!--  <textarea name="building_type_desc" class="form-control" placeholder="Enter Building Type Description"></textarea> -->
                <div class="p-relative">
                <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="simpleFormDesc"  placeholder="Enter complaint Description" name="complaint_reason_desc" value="{{ isset($complaintReason)?  old('complaint_reason_desc',$complaintReason->complaint_reason_desc): old('complaint_reason_desc')}}">
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
