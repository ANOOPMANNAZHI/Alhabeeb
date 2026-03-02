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
              <div class="page-title">Work Flow Categories</div>
          </div>
          {{ (isset($workFlowCategory))?   Breadcrumbs::render('workFlowCategory.edit',$workFlowCategory) :  Breadcrumbs::render('workFlowCategory.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($workFlowCategory)? route('workFlowCategory.store'): route('workFlowCategory.update',$workFlowCategory->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data"  >
{{csrf_field()}} @if(isset($workFlowCategory)){{method_field('PUT')}}@endif

<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormName">Name<small class="textRed">*</small></label>
                 <div class="p-relative">
                      <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormName"  placeholder="Enter Category Type" name="assign_field_name" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($workFlowCategory)?  old('building_types_name',$workFlowCategory->assign_field_name): old('assign_field_name')}}" data-rule-maxlength="25" data-msg-maxlength="Only allowes 25 Characters">
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label>Price Range<small class="textRed">*</small></label>
                 <div class="p-relative">
                      <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <select class="form-control" name="price_range_id" required>
                    <option value="">Select Price Range</option>
                    @foreach($prices as $price)
                    <option {{ isset($workFlowCategory)? ((old('price_range_id',$workFlowCategory->price_range_id) == $price->id)? 'selected' : '') : ''}} value="{{$price->id}}" >{{$price->price_ranges_name}}</option>
                    @endforeach
                </select>
              </div>
            </div> 
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Location<small class="textRed">*</small></label>
                 <div class="p-relative">
                      <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                <select class="form-control" name="location_id" required>
                    <option value="">Select Location</option>
                    @foreach($locations as $location)
                    <option {{ isset($workFlowCategory)? ((old('location_id',$workFlowCategory->location_id) == $location->id)? 'selected' : '') : ''}} value="{{$location->id}}" >{{$location->locations_name}}</option>
                    @endforeach
                </select>
              </div>
            </div> 
        </div>
        <!-- <div class="w-100"></div>
         <div class="col-sm-12">
            <div class="form-group">
                <label for="simpleFormDesc">Description</label>
               <textarea name="building_types_desc" id="simpleFormDesc" class="form-control" placeholder="Enter Building Type Description">{{ isset($buildingType)?  old('building_types_desc',$buildingType->building_types_desc): old('building_types_desc')}}</textarea>
                
            </div>
        </div> -->
         
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