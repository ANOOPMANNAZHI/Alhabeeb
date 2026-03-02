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
              <div class="page-title">Currency</div>
          </div>
          {{ (isset($currency))?   Breadcrumbs::render('currency.edit',$currency,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('currency.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($currency)? route('currency.store'): route('currency.update',$currency->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($currency)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)? $previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormCode">Currency Code<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormCode"  placeholder="Enter Currency Code" name="currency_code" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($currency)?  old('currency_code',$currency->currency_code): old('currency_code')}}" data-rule-maxlength="4" data-msg-maxlength="Maximum 4 Characters Allowed">
              </div>
            </div>
          </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormName">Currency Name<small class="textRed">*</small></label>
               <!--  <textarea name="building_type_desc" class="form-control" placeholder="Enter Building Type Description"></textarea> -->
                <div class="p-relative">
                <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="simpleFormName"  placeholder="Enter Currency Name" name="currency_name" value="{{ isset($currency)?  old('currency_name',$currency->currency_name): old('currency_name')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed" required>
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
