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
              <div class="page-title">Inventory</div>
          </div>
          {{ (isset($inventory))?   Breadcrumbs::render('inventory.edit',$inventory,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('inventory.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($inventory)? route('inventory.store'): route('inventory.update',$inventory->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data"  >
{{csrf_field()}} @if(isset($inventory)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)? $previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormName">Name<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormName"  placeholder="Enter Inventory Name" name="inventories_name" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($inventory)?  old('inventories_name',$inventory->inventories_name): old('inventories_name')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormBrand">Brand Name</label>
               <!--  <textarea name="building_type_desc" class="form-control" placeholder="Enter Building Type Description"></textarea> -->
                <div class="p-relative">
                <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="simpleFormBrand"  placeholder="Enter Inventory Brand Name" name="inventories_brand_name" value="{{ isset($inventory)?  old('inventories_brand_name',$inventory->inventories_brand_name): old('inventories_brand_name')}}" data-rule-maxlength="50" data-msg-maxlength="Maximum 50 Characters Allowed">
              </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-12">
            <div class="form-group">
                <label for="simpleFormDesc">Description</label>
                 <div class="p-relative">
                <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <textarea name="inventories_desc" class="form-control" placeholder="Enter Inventory Description">{{ isset($inventory)?  old('inventories_name',$inventory->inventories_name): old('inventories_name')}}</textarea>
                </div>
            </div>
          </div>
       <div class="w-100"></div>
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
