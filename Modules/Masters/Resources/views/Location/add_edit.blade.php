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
              <div class="page-title">Location</div>
          </div>
         {{ (isset($location))?   Breadcrumbs::render('location.edit',$location,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('location.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($location)? route('location.store'): route('location.update',$location->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($location)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)?$previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormCode">Region<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-location icn-add" aria-hidden="true"></i>
                <?php if(isset($location)){?>
                   <select class="form-control" id="locations_region" name="locations_region" required="" aria-required="true">
                       <option disabled selected>Select Region</option>
                       @foreach($regions as $item)
                         @if($item->id == $location->locations_region)
                         <option value="{{$item->id}}" selected>{{$item->region_name}}</option>
                         @else if($item->id != $location->locations_region)
                         <option value="{{$item->id}}">{{$item->region_name}}</option>
                         @endif
                       @endforeach                 
                    </select>

                <?php } ?>
                <?php if(!isset($location)){?>

                    <select class="form-control" id="locations_region" name="locations_region" required="" aria-required="true">
                       <option disabled selected>Select Region</option>
                       @foreach($regions as $item)
                       
                         <option value="{{$item->id}}">{{$item->region_name}}</option>
                       
                       @endforeach      
                       <!-- <option value="1">Al Ansab</option>
                       <option value="2">Al Khuwair</option>
                       <option value="3">Amerat</option>
                       <option value="17">Azaiba</option>
                       <option value="4">Barka</option>
                       <option value="5">Bousher</option>
                       <option value="6">Ghala</option>
                       <option value="7">Ghubra</option>
                       <option value="8">Madinat Qaboos</option>
                       <option value="9">Muna</option>
                       <option value="10">Muttrah/Muscat</option>
                       <option value="11">Qurum</option>
                       <option value="12">Rusayl</option>
                       <option value="13">Ruwi</option>
                       <option value="14">Seeb</option>
                       <option value="15">Shatti</option>
                       <option value="16">Wadi Kabir</option> -->
                                        
                    </select>
                <?php } ?>
               

                
                
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormCode">Code<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormCode"  placeholder="Enter Location Code" name="locations_code" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($location)?  old('locations_code',$location->locations_code): old('locations_code')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
              </div>
            </div>
          </div>
         <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormEmail">Name<small class="textRed">*</small></label>
               <!--  <textarea name="building_type_desc" class="form-control" placeholder="Enter Building Type Description"></textarea> -->
                <div class="p-relative">
                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="simpleFormEmail"  placeholder="Enter Location Name" name="locations_name" value="{{ isset($location)?  old('locations_name',$location->locations_name): old('locations_name')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed" required>
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
