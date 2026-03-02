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
              <div class="page-title">Price Range</div>
          </div>
          {{ (isset($priceRange))?   Breadcrumbs::render('priceRange.edit',$priceRange,$backUrlBreadCrumb,$backIdBreadCrumb ) :  Breadcrumbs::render('priceRange.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($priceRange)? route('priceRange.store'): route('priceRange.update',$priceRange->id)}}" autocomplete="off" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data"  >
{{csrf_field()}} @if(isset($priceRange)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)? $previousUrl:''}}" >

<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
         <!--  <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormName">Price Range<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormName"  placeholder="Enter Price Range" name="price_ranges_name" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($priceRange)?  old('price_ranges_name',$priceRange->price_ranges_name): old('price_ranges_name')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div> -->
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormFrom">From<small class="textRed">*</small></label>
               <!--  <textarea name="building_type_desc" class="form-control" placeholder="Enter Building Type Description"></textarea> -->
                <div class="p-relative">
                <i class="fa fa-book icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="price_ranges_from_id"  placeholder="Enter Price Range From" name="price_ranges_from" onkeypress="return isNumber(event)" required  data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values" value="{{ isset($priceRange)?  old('price_ranges_from',$priceRange->price_ranges_from): old('price_ranges_from')}}">
              </div>
            </div>
        </div>
         
       
        <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormTo">To<small class="textRed">*</small></label>
               <!--  <textarea name="building_type_desc" class="form-control" placeholder="Enter Building Type Description"></textarea> -->
                <div class="p-relative">
                <i class="fa fa-clone icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="price_ranges_to_id"  placeholder="Enter Price Range To" name="price_ranges_to" required onkeypress="return isNumber(event)" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values" value="{{ isset($priceRange)?  old('price_ranges_to',$priceRange->price_ranges_to): old('price_ranges_to')}}">
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
  jQuery(document).ready(function ($) {

    $.validator.addMethod('le', function (value, element, param) {
        return this.optional(element) || parseInt(value) <= parseInt($(param).val());
    }, 'Invalid value');


    $('#form_sample_2').validate({
        errorElement: "small",
        rules: {
            bid_price: {
                required: true,
                number: true

            },
            price_ranges_from: {
                required: true,
                number: true,
                le: '#price_ranges_to_id'
            }
        },
        messages: {
            price_ranges_from: {
                le: 'Must be less than Price Range To  .'
            }
        }
    });
    
    $('[name="price_ranges_to"]').on('change blur keyup', function() {
        $('[name="price_ranges_from"]').valid();
    });
    
});
</script>
@endsection
