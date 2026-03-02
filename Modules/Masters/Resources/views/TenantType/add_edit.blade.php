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
              <div class="page-title">Tenant Type</div>
          </div>
         

          {{ (isset($tenantType))?   Breadcrumbs::render('tenantType.edit',$tenantType,$backUrlBreadCrumb,$backIdBreadCrumb ) :  Breadcrumbs::render('tenantType.create') }}  
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($tenantType)? route('tenantType.store'): route('tenantType.update',$tenantType->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($tenantType)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)?$previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Name<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormEmail"  placeholder="Enter Tenant Type Name" name="tenant_types_name" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($tenantType)?  old('tenant_types_name',$tenantType->tenant_types_name): old('tenant_types_name')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>
         <div class="w-100"></div>
         <div class="col-sm-12">
            <div class="form-group">
                <label for="simpleFormDesc">Description</label>
                 <div class="p-relative">
                <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
               <textarea name="tenant_types_desc" class="form-control" placeholder="Enter Tenant Type Description">{{ isset($tenantType)?  old('tenant_types_desc',$tenantType->tenant_types_desc): old('tenant_types_desc')}}</textarea>
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
