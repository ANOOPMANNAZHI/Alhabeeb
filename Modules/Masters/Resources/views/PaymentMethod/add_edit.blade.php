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
              <div class="page-title">Payment Method</div>
          </div>
          {{ (isset($paymentMethod))?   Breadcrumbs::render('paymentMethod.edit',$paymentMethod,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('paymentMethod.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($paymentMethod)? route('paymentMethod.store'): route('paymentMethod.update',$paymentMethod->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data"  >
{{csrf_field()}} @if(isset($paymentMethod)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)?$previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormCode">Code<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormCode"  placeholder="Enter Payment Method Code" name="payment_method_code" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($paymentMethod)?  old('payment_method_code',$paymentMethod->payment_method_code): old('payment_method_code')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>
          <div class="w-100"></div>
         <div class="col-sm-12">
            <div class="form-group">
                <label for="simpleFormEmail">Description</label>
                 <div class="p-relative">
                <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
               <textarea name="payment_method_desc" class="form-control" placeholder="Enter Payment Method Description">{{ isset($paymentMethod)?  old('payment_method_desc',$paymentMethod->payment_method_desc): old('payment_method_desc')}}</textarea>
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
