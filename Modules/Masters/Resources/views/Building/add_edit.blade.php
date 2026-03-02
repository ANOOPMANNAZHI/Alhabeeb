@extends('layouts.plms-app')
@section('css')

<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">

@endsection
@section('content')
	<!-- start widget --> 
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Building</div>
          </div>
           {{ (isset($building))?   Breadcrumbs::render('building.edit',$building,$backUrlBreadCrumb,$backIdBreadCrumb ) :  Breadcrumbs::render('building.create') }}         
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form autocomplete="off" action="{{ !isset($building)? route('building.store'): route('building.update',$building->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data">
{{csrf_field()}} @if(isset($building)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)? $previousUrl:''}}" >
 
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_code">Building Code </label>
                 <div class="p-relative">
                 	 <i class="icon icon-building" aria-hidden="true"></i>
                <input disabled type="text" class="form-control" id="building_code"  name="building_code" required  value="{{isset($building)?  $building->building_code : $nextCode }}">
            </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_name">Building Name<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="icon icon-building" aria-hidden="true"></i>
                <input type="text" class="form-control" id="building_name" autocomplete="off" name="building_name" required  value="{{ isset($building)? $building->building_name : old('building_name', isset($building)?  $building->building_name : '' )}}" placeholder="Enter Building Name">
				</div>
            </div>
          </div> 
          <div class="w-100"></div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_prefix">Building Prefix<small class="textRed">*</small> </label>
                <div class="p-relative">
                   <i class="icon icon-building" aria-hidden="true"></i>
                <input type="text" class="form-control" autocomplete="off" id="building_prefix"  name="building_prefix" required  value="{{ old('building_prefix', isset($building)?  $building->building_prefix : '' )}}" placeholder="Enter Building Prefix" maxlength="4">
            </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_year">Building Year</label>
                <div class="p-relative">
                   <i class="fa fa-calendar icn-add" aria-hidden="true"></i>
                <input type="year" class="form-control date-own" id="datepicker" name="building_year" >
            </div>
            </div>
          </div>
           <div class="w-100"></div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_maintenance_info"> Building Maintenance Info</label>
                 <div class="p-relative">
                   <i class="icon icon-building" aria-hidden="true"></i>
                <select class="form-control" id="building_maintenance_info"  name="building_maintenance_info" >
                  <option value="">Select Building Maintenance Info</option>
                   <option  {{(old('building_maintenance_info', isset($building)?  $building->building_maintenance_info : '') == 0) ? 'selected' : '' }} value="0">Managed by us</option>
                   <option  {{(old('building_maintenance_info', isset($building)?  $building->building_maintenance_info : '') == 1) ? 'selected' : '' }} value="1">Not Managed by us</option>                
                </select> 
                </div>               
            </div>
          </div>         
       


          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_name"> Landlord Name<small class="textRed">*</small> </label>
                 <div class="p-relative">
                 	 <i class="icon icon-landlord" aria-hidden="true"></i>
                   <input type="text" placeholder="Enter Landlord Name" name="vendor_name" class="form-control vendor_name" id="vendor_name" required value="{{ isset($building)?  old('vendor_name',$building->vendor->vendor_name): old('vendor_name','')}}">

                    <input type="hidden" name="vendor_id" id="vendor_id" value="{{ isset($building)?  old('vendor_id',$building->vendor_id): old('vendor_id','')}}">

                <!-- <select class="form-control" id="vendor_id"  name="vendor_id" required>
                  <option value="">Select Landlord Name</option>
                  @foreach($vendors as $vendor)
                   <option  {{(old('vendor_id', isset($building)?  $building->vendor_id : 0) == $vendor->id) ? 'selected' : '' }} value="{{$vendor->id}}">{{$vendor->vendor_name}}</option>
                  @endforeach                  
                </select>  --> 
                </div>              
            </div>
          </div>
          <div class="w-100"></div>

          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_no">Building No.<small class="textRed">*</small> </label>
                <div class="p-relative">
                	 <i class="icon icon-building" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="building_no"  name="building_no" required  value="{{ old('building_no', isset($building)?  $building->building_no : '' )}}" placeholder="Enter Building No" pattern="^[a-zA-Z0-9_]*$" maxlength="25">
            </div>
            </div>
          </div>  
          
           <div class="col-sm-6">
            <div class="form-group">
                <label for="building_pc">Way No<small class="textRed">*</small> </label>
                 <div class="p-relative">
                  <i class="fa fa-university icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" autocomplete="off" id="building_pc"  name="building_pc" required  value="{{ old('building_pc', isset($building)?  $building->building_pc : '' )}}" placeholder="Enter Way No" pattern="^[a-zA-Z0-9_]*$" maxlength="25">
            </div>
            </div>
          </div>
          <div class="w-100"></div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="block_number">Block Number</label>
                <div class="p-relative">
                   <i class="fa fa-hospital-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" autocomplete="off" id="block_number"  name="block_number"   value="{{ old('block_number', isset($building)?  $building->block_number : '' )}}" placeholder="Enter Block Number" pattern="^[a-zA-Z0-9_]*$" maxlength="25">
            </div>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
                <label for="plot_no">Plot No</label>
                <div class="p-relative">
                   <i class="fa fa-building-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" autocomplete="off" id="plot_no"  name="plot_no"   value="{{ old('plot_no', isset($building)?  $building->plot_no : '' )}}" placeholder="Enter Plot No" pattern="^[a-zA-Z0-9_]*$" maxlength="25">
            </div>
            </div>
          </div>            
           <div class="w-100"></div>
           <div class="col-sm-6">
            <div class="form-group">
                <label for="landmark">Landmark</label>
                <div class="p-relative">
                   <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" autocomplete="off" id="landmark"  name="landmark"  value="{{ old('landmark', isset($building)?  $building->landmark : '' )}}" placeholder="Enter Landmark">
            </div>
            </div>
          </div> 
          <div class="col-sm-6">
            <div class="form-group">
                <label for="location_id">Location<small class="textRed">*</small> </label>
                  <div class="p-relative">
                      <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                      <input type="text" placeholder="Enter Location" required name="location_name" class="form-control location_name" id="location_name" value="{{ isset($building)?  old('location_name',$building->location->locations_name): old('location_name','')}}">

                    <input type="hidden" name="location_id" id="location_id" value="{{ isset($building)?  old('location_id',$building->location_id): old('location_id','')}}">
                <!-- <select class="form-control" id="location_id"  name="location_id" required>
                  <option value="">Select Location</option>
                  @foreach($location as $val)
                   <option  {{(old('location_id', isset($building)?  $building->location_id : '') == $val->id) ? 'selected' : '' }} value="{{$val->id}}">{{$val->locations_name}}</option>
                  @endforeach                  
                </select>  -->
                </div>               
            </div>
          </div>
          <div class="w-100"></div>
           <div class="col-sm-6">
            <div class="form-group">
                <label for="building_type_id"> Building Type<small class="textRed">*</small> </label>
                 <div class="p-relative">
                   <i class="icon icon-building" aria-hidden="true"></i>
                <select class="form-control" id="building_type_id"  name="building_type_id" required>
                  <option value="">Select Building Type</option>
                  @foreach($buildingTypes as $buildingType)
                   <option  {{(old('building_type_id', isset($building)?  $building->building_type_id : 0) == $buildingType->id) ? 'selected' : '' }} value="{{$buildingType->id}}">{{$buildingType->building_types_name}}</option>
                  @endforeach                  
                </select> 
                </div>               
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_no_floor">Number Of Floors<small class="textRed">*</small></label>
                 <div class="p-relative">
                  <i class="fa fa-sort-numeric-asc icn-add" aria-hidden="true"></i>
                <input type="number" class="form-control" id="building_no_floor" autocomplete="off"  name="building_no_floor" required  value="{{ old('building_no_floor', isset($building)?  $building->building_no_floor : '' )}}" placeholder="Enter Number Of Floors" min="1">
            </div>
            </div>
          </div>

           
         
          <div class="col-sm-6">
            <div class="form-group">
                <label for="watchman_no">Watchman No</label>
                <div class="p-relative">
                   <i class="fa fa-clock-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="watchman_no"  name="watchman_no"   value="{{ old('watchman_no', isset($building)?  $building->watchman_no : '' )}}" placeholder="Enter Watchman No" pattern="^[a-zA-Z0-9_]*$" maxlength="25">
            </div>
            </div>
          </div>
         
          <div class="col-sm-6">
            <div class="form-group">
                <label for="management_id"> Management Type<small class="textRed">*</small>  </label>
                 <div class="p-relative">
                   <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                <select class="form-control" id="management_id"  name="management_id" required>
                  <option value="">Select Management</option>
                  @foreach($managementTypes as $managementType)
                   <option  {{(old('management_id', isset($building)?  $building->management_id : 0) == $managementType->id) ? 'selected' : '' }} value="{{$managementType->id}}">{{$managementType->management_types_name}}</option>
                  @endforeach                  
                </select> 
                </div>               
            </div>
          </div> 
          <div class="col-sm-6">
            <div class="form-group">
                <label for="build_up_area">Management Date</label>
                <div class="p-relative">
                   <i class="fa fa-creative-commons icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="management_date"  name="management_date"   value="{{ old('management_date', isset($building)?  $building->management_date : '' )}}" placeholder="Enter Date">
            </div>
            </div>
          </div> 
          <div class="w-100"></div>
          <!-- <div class="col-sm-6">
            <div class="form-group">
                <label for="user_id">Are</label>
                <div class="p-relative">
                   <i class="fa fa-sort-numeric-asc icn-add" aria-hidden="true"></i>
                <select class="form-control" id="user_id"  name="user_id">
                  <option value="">Select Are</option>
                  @foreach($ares as $are)
                   <option  {{(old('user_id', isset($building)?  $building->user_id : 0) == $are->id) ? 'selected' : '' }} value="{{$are->id}}">{{$are->username}}</option>
                  @endforeach                  
                </select>
            </div>
            </div>
          </div> -->
           <div class="col-sm-6">
            <div class="form-group">
                <label for="build_up_area">Build-up Area</label>
                <div class="p-relative">
                   <i class="fa fa-creative-commons icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="build_up_area"  name="build_up_area"   value="{{ old('build_up_area', isset($building)?  $building->build_up_area : '' )}}" placeholder="Enter Build-Up Area" pattern="^[a-zA-Z0-9_]*$" maxlength="25">
            </div>
            </div>
          </div>                    
          
          <div class="col-sm-6">
            <div class="form-group">
                <label for="google_location">Google Location<small class="textRed">*</small> </label>
                 <div class="p-relative">
                    <i class="fa fa-location-arrow icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="google_location"  name="google_location" required  value="{{ old('google_location', isset($building)?  $building->google_location : '' )}}" placeholder="Enter Location" >
                <input type="hidden" value="{{ old('building_geo_lat', isset($building)?  $building->building_geo_lat :'21.4735329')}}" name="building_geo_lat" required id="building_geo_lat" class="geoLocations">
                <input type="hidden" value="{{ old('building_geo_long', isset($building)?  $building->building_geo_long :'55.975413')}}" name="building_geo_long" id="building_geo_long" >
            </div>
            </div>
          </div>   

          <div class="w-100"></div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="buiding_address">Building Address<small class="textRed">*</small> </label>
                 <div class="p-relative">
                   <i class="icon icon-building" aria-hidden="true"></i>
                <textarea  class="form-control" id="building_address"  name="building_address" required placeholder="Enter Building Address"   >{{ old('building_address', isset($building)?  $building->building_address : '' )}}</textarea>
            </div>
            </div>
          </div>
          
          <div class="col-sm-6">
            <div class="form-group">
                <label for="db_number">Tel DB No.</label>
                <div class="p-relative">
                   <i class="fa fa-compass icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="db_number"  name="db_number"   value="{{ old('db_number', isset($building)?  $building->db_number : '' )}}" placeholder="Enter DB No" pattern="^[ A-Za-z0-9_@./#&+-]*$" maxlength="25">
            </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_note">Note<small class="textRed">*</small> </label>
                  <div class="p-relative">
                  		<i class="fa fa-sticky-note-o icn-add" required aria-hidden="true"></i>
                <textarea required class="form-control" id="building_note"  name="building_note" placeholder="Enter Note "   maxlength="200">{{ old('building_note', isset($building)?  $building->building_note : '' )}}</textarea>
            </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="management_id"> Division<small class="textRed">*</small>  </label>
                 <div class="p-relative">
                   <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                <select class="form-control" id="ax_division"  name="ax_division" required>
                  <option {{(old('ax_division', isset($building)?  $building->ax_division :0) == '02') ? 'selected' : '' }} value="02">PLM</option>
                 
                   <option  {{(old('ax_division', isset($building)?  $building->ax_division :0) == '01') ? 'selected' : '' }} value="01">HO</option>
                                 
                </select> 
                </div>               
            </div>
          </div> 
         <!-- <div class="col-sm-6">
            <div class="form-group">
                <label for="building_status">Status<small class="textRed">*</small></label>
                  <div class="p-relative">
                      <i class="fa fa-anchor icn-add" aria-hidden="true"></i>
                <select class="form-control" id="building_status"  name="building_status" required>
                <option value="">Select </option>                  
                <option  {{(old('building_status', isset($building)?  $building->building_status : '') == 1) ? 'selected' : '' }} value="1">Active</option>
                <option  {{(old('building_status', isset($building)?  $building->building_status : -1) == 0) ? 'selected' : '' }} value="0">Inactive</option>                                
                </select>  
                </div>              
            </div>
          </div>
       -->
           
      </div>
    
</div>
<div class="clearfix"></div>
<div class="sub-head">Building Meter Details</div>
<div class="dataSearchBox ">
        <div class="row">
          <div class="col-sm-2">
              <div class="form-group">
                <label for="building_meter_category">Category</label>
              </div>
          </div>
          <div class="col-sm-2">
              <div class="form-group">
                <label for="electricity_acc_no">Ele A/c No </label>
              </div>
          </div>
          <div class="col-sm-2">
              <div class="form-group">
                <label for="electricity_met_no">Ele Met No </label>
              </div>
          </div>
          <div class="col-sm-2">
              <div class="form-group">
                <label for="water_acc_no">Water A/c No </label>
              </div>
          </div>
          <div class="col-sm-2">
              <div class="form-group">
                <label for="water_met_no">Water Met No </label>
              </div>
          </div>
           <div class="w-100"></div>
        </div>  
        <div class="field_wrapper_ele">
              
         <div class="row ele">
          <div class="col-sm-2">
            <div class="form-group">
                <div class="p-relative">
                      <i class="fa fa-camera-retro icn-add" aria-hidden="true"></i>
                <select class="form-control" id="building_meter_category"  name="building_meter_category[]" >
                <option value="">Select Category</option>                  
                <option value="0">Common Area</option>
                <option value="1">Lift</option> 
                <option value="2">Others</option>                               
                </select>  
                </div>              
            </div>
          </div>
           <div class="col-sm-2">
            <div class="form-group">
                <div class="p-relative">
                   <i class="icon icon-electrical" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="electricity_acc_no"  name="electricity_acc_no[]"   value="{{ old('electricity_acc_no', isset($building)?  $building->electricity_acc_no : '' )}}" placeholder="Ele A/c No">
            </div>
            </div>
          </div>
         
          <div class="col-sm-2">
            <div class="form-group">
                
                <div class="p-relative">
                   <i class="icon icon-electrical" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="electricity_met_no"  name="electricity_met_no[]"   value="{{ old('electricity_met_no', isset($building)?  $building->electricity_met_no : '' )}}" placeholder="Ele Met No">
            </div>
            </div>
          </div>
          
          <div class="col-sm-2">
            <div class="form-group">
                
                <div class="p-relative">
                   <i class="icon icon-plumbing" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="water_acc_no"  name="water_acc_no[]"   value="{{ old('water_acc_no', isset($building)?  $building->water_acc_no : '' )}}" placeholder="Water A/c No">
            </div>
            </div>
          </div>
         
          <div class="col-sm-2">
            <div class="form-group">
                
                <div class="p-relative">
                   <i class="icon icon-plumbing" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="water_met_no"  name="water_met_no[]"   value="{{ old('water_met_no', isset($building)?  $building->water_met_no : '' )}}" placeholder="Water Met No">
            </div>
            </div>
          </div>
          <div class="col-sm-2">
               
                <button type="button" class="btn btn-primary add_button_ele">Add</button>
                <button title="Delete" type="button" class="btn btn-warning remove_button_ele" style="display:none;"><i class="fa fa-trash-o "></i></button>
          </div>

         </div>  

         @if(!empty($building->buildingEleWaterReading))  
         
          @foreach ($building->buildingEleWaterReading  as $reading) 
          <div class="row ele">
          <div class="col-sm-2">
            <div class="form-group">
               
                  <div class="p-relative">
                      <i class="fa fa-camera-retro icn-add" aria-hidden="true"></i>
                <select class="form-control margin-top-8" id="building_meter_category"  name="building_meter_category[]" >
                <option value="">Select Category</option>                  
                <option {{(old('building_meter_category', isset($reading)?  $reading->building_meter_category : '') === "0") ? 'selected' : '' }} value="0">Common Area</option>
                <option {{(old('building_meter_category', isset($reading)?  $reading->building_meter_category : '') === "1") ? 'selected' : '' }} value="1">Lift</option> 
                <option {{(old('building_meter_category', isset($reading)?  $reading->building_meter_category : '') === "2") ? 'selected' : '' }} value="2">Others</option>                               
                </select>  
                </div>              
            </div>
          </div>
           <div class="col-sm-2">
            <div class="form-group">
                
                <div class="p-relative">
                   <i class="icon icon-electrical" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="electricity_acc_no"  name="electricity_acc_no[]"   value="{{ old('electricity_acc_no', isset($building)?  $reading->electricity_acc_no : '' )}}" placeholder="Enter Electricity A/c No">
            </div>
            </div>
          </div>
         
          <div class="col-sm-2">
            <div class="form-group">
                
                <div class="p-relative">
                   <i class="icon icon-electrical" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="electricity_met_no"  name="electricity_met_no[]"   value="{{ old('electricity_met_no', isset($building)?  $reading->electricity_met_no : '' )}}" placeholder="Enter Electricity Met. No">
            </div>
            </div>
          </div>
          <div class="col-sm-2">
            <div class="form-group">
               
                <div class="p-relative">
                   <i class="icon icon-plumbing" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="water_acc_no"  name="water_acc_no[]"   value="{{ old('water_acc_no', isset($building)?  $reading->water_acc_no : '' )}}" placeholder="Enter Water A/c. No">
            </div>
            </div>
          </div>
         
          <div class="col-sm-2">
            <div class="form-group">
                
                <div class="p-relative">
                   <i class="icon icon-plumbing" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="water_met_no"  name="water_met_no[]"   value="{{ old('water_met_no', isset($building)?  $reading->water_met_no : '' )}}" placeholder="Enter Water Met. No">
            </div>
            </div>
          </div>
          <div class="col-sm-2">
                <div class="dataSearchLabel w-100" style="margin-top: 2px"></div>
                 <input type="hidden" class="reading_id" name="reading_id" id="reading_id" value="{{$reading->id}}"> 
                <button type="button" class="btn btn-warning remove_button_ele_old"><i class="fa fa-trash-o "></i></button>
          </div>
        <div class="w-100"></div>
        </div> 
          @endforeach    
          @endif
          
        
        <div class="w-100"></div>
        </div>

        
        
</div>
<div class="clearfix"></div>
<div class="sub-head">Image Upload (Max : {{$upload_size/1000000}} MB)</div>
<div class="dataSearchBox ">
        <div class="row">
        <div class="col-sm-5">
              <div class="form-group">
                <label for="building_img_category">Image Category</label>
              </div>
          </div>
          <div class="col-sm-5">
              <div class="form-group">
                <label for="building_img_name">Image</label>
              </div>
          </div>
        </div>
        <div class="field_wrapper">
          <div class="row" id="1">
          <div class="col-sm-5">
            <div class="form-group">
                
                  <div class="p-relative">
                      <i class="fa fa-camera-retro icn-add" aria-hidden="true"></i>
                <select class="form-control margin-top-8" id="building_img_category"  name="building_img_category[1]" >
                <option value="">Select Category</option>                  
                <option value="0">Common Area</option>
                <option value="1">Lift</option> 
                <option value="2">Others</option>                               
                </select>  
                </div>              
            </div>
          </div>
           <div class="col-sm-5">
            <div class="form-group">
                
                <div class="p-relative">
                    <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
                <input type="file" class="form-control upload"  id="building_img_name[1]"  name="building_img_name[1]">
            </div>
            </div>
          </div>
          <div class="col-sm-2">
               
                <button type="button" class="btn btn-primary add_button">Add</button>
                <button title="Delete" type="button" class="btn btn-warning remove_button"  style="display:none;"><i class="fa fa-trash-o "></i></button>
          </div>
        <div class="w-100"></div>
        </div>

         @if(!empty($building->buildingImage))  
          <div id="aniimated-thumbnials" class="list-unstyled  clearfix">
          
          @foreach ($building->buildingImage  as $image) 
              <div class="balance m-b-20 rows"> 
               
                <img class="img-fluid img-thumbnail" src="{{asset('storage/app/'.$image->building_path_thumbnail)}}" alt="" title ="{{$image->BuildingImgCategoryName}}"> 
                <input type="hidden" class="img_path_id" name="img_path_id" id="img_path_id" value="{{$image->id}}"> 
               <button type="button" title="Delete" class="btn btn-warning remove_img_button img-closed"><!-- Remove&times; --><i class="fa fa-trash-o "></i></button>
             </div>
                <!--  <div class="col-sm-2">
                    <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
                    <button type="button" class="btn btn-danger remove_button">Remove</button>
              </div> -->
          @endforeach    
          </div>
          @endif
        </div>    
        
</div>

<div class="clearfix"></div>
<div class="sub-head">Docs Upload (Max : {{$upload_size/1000000}} MB)</div>
<div class="dataSearchBox ">
         <div class="row">
        <div class="col-sm-5">
              <div class="form-group">
               <label for="building_doc_category">Doc Category</label>
              </div>
          </div>
          <div class="col-sm-5">
              <div class="form-group">
                <label for="building_doc_path_name">Docs</label>
              </div>
          </div>
        </div>
        

        <div class="field_wrapper_docs">
          <div class="row" id="1">
          <div class="col-sm-5">
            <div class="form-group">
                
                  <div class="p-relative">
                      <i class="fa fa-anchor icn-add" aria-hidden="true"></i>
                <select class="form-control margin-top-8" id="building_doc_category[1]"  name="building_doc_category[1]" >
                <option value="">Select </option>                  
                <option value="0">Mulkia</option>
                <option value="1">Krooki</option> 
                <option value="2">Waqala (POA)</option>
                <option value="3">Landlord ID</option>
                <option value="4">CR (if Landlord is a company)</option>
                <option value="5">Drawings</option>
                <option value="6">Building Permit</option>
                <option value="7">Civil Defense Certificate</option>
                <option value="9">AMC</option>
                <option value="10">Insurance</option>
                <option value="8">Others</option>                               
                </select>  
                </div>              
            </div>
          </div> 
           <div class="col-sm-5">
            <div class="form-group">
                
                <div class="p-relative">
                    <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
                <input type="file" class="form-control upload_doc"  id="building_doc_path_name[1]"  name="building_doc_path_name[1]">
            </div>
            </div>
          </div>
          <div class="col-sm-2">
               
                <button type="button" class="btn btn-primary add_docs_button">Add</button>
                <button title="Delete" type="button" class="btn btn-warning remove_doc_button" style="display:none;"><i class="fa fa-trash-o "></i></button>
            </div>
        <div class="w-100"></div>
        </div>
          @if(!empty($building->buildingDocs)) 

          
          @foreach ($building->buildingDocs  as $doc) 
             <div class="row ro ">
              <div class="col-sm-4">
            {{$doc->BuildingDocCategoryName }}
          </div>

                 <div class="col-sm-5">
                  <div class="form-group">
                     <a target="_blank" href="{{asset('storage/app/'.$doc->building_doc_path_name)}}">
                        {{$doc->building_doc_name}}  </a>
                     
                  </div>
                </div>
                <div class="col-sm-2">
                      <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
                      <input type="hidden" class="doc_id" name="doc_id" id="doc_id" value="{{$doc->id}}">
                      <button type="button" title="Delete" class="btn btn-warning remove_old_docs_button img-closed"><i class="fa fa-trash-o "></i></button>
                      <button title="Delete" type="button" class="btn btn-warning remove_doc_button" style="display:none;"><i class="fa fa-trash-o "></i></button>
                  </div>
              <div class="w-100"></div>
              </div>
          @endforeach    
      
          @endif
        </div>    
        
</div>    
  <button type="submit" class="btn btn-primary">Save</button>

</form>
    
</div>
</div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js"></script>
<script>
  $(document).on("change",".upload",function(){
            
            fileUpload($(this));
  });  
  $(document).on("change",".upload_doc",function(){
            
            fileUploadDoc($(this));
  }); 
  $(document).ready(function() {
    $('.date-own').datepicker({
      format: 'yyyy'
    });



  $("#form_sample_2").validate();

  $.validator.addMethod('filesize', function (value, element, param) {
		return this.optional(element) || (element.files[0].size <= param)
		}, 'File size must be less than {{$upload_size/1000000}}MB');

    $('#vendor_name').autocomplete({
      source : '{!!URL::route('landlordAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
         if (ui.item == null || ui.item == undefined) {
            $("#vendor_name").val('');
            $('#vendor_name-error').show();
         }else {
          $('#vendor_id').val(ui.item.ids);
          
         }
       
      }
    });
    $('#location_name').autocomplete({
      source : '{!!URL::route('locationsAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        if (ui.item == null || ui.item == undefined) {
            $("#location_name").val('');
            $('#location_name-error').show();
        }else {
           $('#location_id').val(ui.item.ids);
          
       }         
      }
    });
    /*******************************************************************/
  });

  function fileUpload(file){
   
    var currentRowId    = parseInt(file.closest('.increment').attr('id'));
    var lastRowId       = parseInt($(".increment").first().attr("id"));
      
    $('.upload').each(function() {
        $(this).rules("add", 
            {
                extension:"Jpeg|Jpg|PNG",
                filesize: {{$upload_size}},
                messages: {
                   extension: "Support Only Following File type : Jpeg|Jpg|PNG",
                   filesize: "File Must Be Less Than {{$upload_size/1000000}}MB",
                }
            });
      });
     $(".upload").valid();
     $.validator.addMethod('filesize', function(value, element, param) {
      // param = size (in bytes) 
      // element = element to validate (<input>)
      // value = value of the element (file name)
      return this.optional(element) || (element.files[0].size <= param) 
      });
     
   }

   function fileUploadDoc(file){
   
    var currentRowId    = parseInt(file.closest('.increment').attr('id'));
    var lastRowId       = parseInt($(".increment").first().attr("id"));
      
    $('.upload_doc').each(function() {
        $(this).rules("add", 
            {
                extension:"Pdf|Doc|Docx|Jpeg|Jpg",
                filesize: {{$upload_size}},
                messages: {
                   extension: "Support Only Following File type : Pdf|Doc|Docx|Jpeg|Jpg",
                   filesize: "File Must Be Less Than {{$upload_size/1000000}}MB",
                }
            });
      });
     $(".upload_doc").valid();
     $.validator.addMethod('filesize', function(value, element, param) {
      // param = size (in bytes) 
      // element = element to validate (<input>)
      // value = value of the element (file name)
      return this.optional(element) || (element.files[0].size <= param) 
      });
     
   }
/********************************Add Image multiple*************************************/    
    var maxField = 40; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
     //New input field html 
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(wrapper).on('click', '.add_button',function(){
      var lastRowId   = parseInt($(".field_wrapper .row").first().attr("id"));
      var values = $("input[name='building_img_name["+lastRowId+"]']")
              .map(function(){
                if($(this).val())return $(this).val();}).get();
            
      var len = $("input[name='building_img_name["+lastRowId+"]']").length;
      if (($( ".report_image_file_name" ).is( ".building_img_name.form-control.error" )) || ( values.length != len ) || ($(".upload").valid() != 1 ) || ($("select[name='building_img_category["+lastRowId+"]']").val() =='')) {
        
              alert("Select Image Category Or Please Upload ");
          
      }else{
        //Check maximum number of input fields
          if(x < maxField){ 
              x++; //Increment field counter
              var lastRowId       = lastRowId + 1;
              var fieldHTML = '<div class="row rows" id="'+lastRowId+'" ><div class="col-sm-5"><div class="form-group"><div class="p-relative"><i class="fa fa-camera-retro icn-add" aria-hidden="true"></i><select class="form-control margin-top-8" id="building_img_category"  name="building_img_category['+lastRowId+']" ><option value="">Select Category</option><option value="0">Commone Area</option><option value="1">Lift</option><option value="2">Others</option></select></div></div>     </div><div class="col-sm-5"><div class="form-group"><div class="p-relative"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" class="form-control upload"  id="building_img_name"  name="building_img_name['+lastRowId+']" ></div></div></div><div class="col-sm-2"><button type="button" class="btn btn-primary add_button">Add</button><button title="Delete" type="button" class="btn btn-warning remove_button"  style="display:none;"><i class="fa fa-trash-o "></i></button></div></div>';
              $(wrapper).prepend(fieldHTML); //Add field html
              $('.field_wrapper .row:not(:first)').find('.add_button').remove();
              $('.field_wrapper .row:not(:first)').find('.remove_button').show();
          }
      }
        
    });
    
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_img_button', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        var img_path_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Sure want to Delete this Image')) {
             $.ajax({
              headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              url: '{{ url('destroyImage') }}' + '/' + img_path_id,
              type: "DELETE",
              data: {  "_method": 'DELETE', 'img_path_id': img_path_id }
              });
              $(this).closest('.rows').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          }
        x--; //Decrement field counter
    });
    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();
        
             $(this).closest('.row').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          
        x--; //Decrement field counter

    });

/*****************************************************************************************/  
/********************************Add Image multiple*************************************/    
    var maxField = 40; //Input fields increment limitation
    var addDocButton = $('.add_docs_button'); //Add button selector
    var wrapperDoc = $('.field_wrapper_docs'); //Input field wrapper
     
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
   $(wrapperDoc).on('click', '.add_docs_button', function(e){
      var lastRowId   = parseInt($(".field_wrapper_docs .row").first().attr("id"));
      var values = $("input[name='building_doc_path_name["+lastRowId+"]']")
              .map(function(){
                if($(this).val())return $(this).val();}).get();
              
      var len = $("input[name='building_doc_path_name["+lastRowId+"]']").length;
      if (values.length != len || ($(".upload_doc").valid() != 1 ) || ($("select[name='building_doc_category["+lastRowId+"]']").val() =='')) {
         
          alert("Select Doc Category Or Please Upload ");
      }else{
        //Check maximum number of input fields
          if(x < maxField){ 
             
              x++; //Increment field counter
              var lastRowId       = lastRowId + 1;
              var fieldDocHTML = '<div class="row ro"><div class="col-sm-5"><div class="form-group"><div class="p-relative"><i class="fa fa-anchor icn-add" aria-hidden="true"></i><select class="form-control margin-top-8" id="building_doc_category"  name="building_doc_category['+lastRowId+']" ><option value="">Select </option> <option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="0">Mulkia</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : -1) == 0) ? 'selected' : '' }} value="1">Krooki</option> <option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="2">Waqala (POA)</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="3">Landlord ID</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="4">CR (if Landlord is a company)</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="5">Drawings</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="6">Building Permit</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="7">Civil Defense Certificate</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="9">AMC</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="10">Insurance</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="8">Others</option>      </select></div> </div></div><div class="col-sm-5 "><div class="form-group"><div class="p-relative"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" class="form-control upload_doc"  id="building_doc_path_name"  name="building_doc_path_name['+lastRowId+']"></div></div></div><div class="col-sm-2"><div class="dataSearchLabel w-100" style="margin-top: 2px"></div> <button type="button" class="btn btn-primary add_docs_button">Add</button><button title="Delete" type="button" class="btn btn-warning remove_doc_button" style="display:none;"><i class="fa fa-trash-o "></i></button></div></div>';


              $(wrapperDoc).prepend(fieldDocHTML); //Add field html
              $('.field_wrapper_docs .row:not(:first)').find('.add_docs_button').remove();
              $('.field_wrapper_docs .row:not(:first)').find('.remove_doc_button').show();
          }
      }
    });
    //Once remove button is clicked
    $(wrapperDoc).on('click', '.remove_old_docs_button', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();
        var doc_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Sure want to Delete this Docs')) {
             $.ajax({
              headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              url: '{{ url('building') }}' + '/' + doc_id,
              type: "DELETE",
              data: {  "_method": 'DELETE', 'doc_id': doc_id }
              });
             $(this).closest('.ro').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          }
        x--; //Decrement field counter

    });
    $(wrapperDoc).on('click', '.remove_doc_button', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();
        
             $(this).closest('.row').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          
        x--; //Decrement field counter

    });

/*****************************************************************************************/
    var maxField = 40; //Input fields increment limitation
    var addEleButton = $('.add_button_ele'); //Add button selector
    var wrapperEle = $('.field_wrapper_ele'); //Input field wrapper
    var fieldEleHTML = '<div class="row ele"><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-camera-retro icn-add" aria-hidden="true"></i><select class="form-control" id="building_meter_category"  name="building_meter_category[]" ><option value="">Select Category </option><option value="0">Common Area</option><option value="1">Lift</option><option value="2">Others</option></select></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-fire icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="electricity_acc_no"  name="electricity_acc_no[]"   value="{{ old('electricity_acc_no', isset($building)?  $building->electricity_acc_no : '' )}}" placeholder="Enter Electricity A/c No"></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-bandcamp icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="electricity_met_no"  name="electricity_met_no[]"   value="{{ old('electricity_met_no', isset($building)?  $building->electricity_met_no : '' )}}" placeholder="Enter Electricity Met. No"></div> </div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-tint icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="water_acc_no"  name="water_acc_no[]"   value="{{ old('water_acc_no', isset($building)?  $building->water_acc_no : '' )}}" placeholder="Enter Water A/c. No"></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-dot-circle-o icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="water_met_no"  name="water_met_no[]"   value="{{ old('water_met_no', isset($building)?  $building->water_met_no : '' )}}" placeholder="Enter Water Met. No"></div></div></div><div class="col-sm-2"><button type="button" class="btn btn-primary add_button_ele">Add</button><button title="Delete" type="button" class="btn btn-warning remove_button_ele" style="display:none;"><i class="fa fa-trash-o "></i></button></div></div>'; //New input field html 
    
      var x = 1; //Initial field counter is 1
      
    //Once add button is clicked
      $(wrapperEle).on('click', '.add_button_ele',function(){
          var values = $("select[name='building_meter_category[]']")
                  .map(function(){
                    if($(this).val())return $(this).val();}).get();

          var lastRowId   = parseInt($(".field_wrapper_ele .row").first().attr("id"));   

          var len = $("select[name='building_meter_category[]']").length;
         // alert(len);
          if (values.length != len ) {
             alert("Select Category");
            
          }else{
              lastRowId = lastRowId++; 
            //Check maximum number of input fields
              if(x < maxField){ 
                  x++; //Increment field counter
                  var fieldEleHTML = '<div class="row ele"><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-camera-retro icn-add" aria-hidden="true"></i><select class="form-control" id="building_meter_category"  name="building_meter_category[]" ><option value="">Select Category </option><option value="0">Common Area</option><option value="1">Lift</option><option value="2">Others</option></select></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-fire icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="electricity_acc_no"  name="electricity_acc_no[]"   value="{{ old('electricity_acc_no', isset($building)?  $building->electricity_acc_no : '' )}}" placeholder="Enter Electricity A/c No"></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-bandcamp icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="electricity_met_no"  name="electricity_met_no[]"   value="{{ old('electricity_met_no', isset($building)?  $building->electricity_met_no : '' )}}" placeholder="Enter Electricity Met. No"></div> </div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-tint icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="water_acc_no"  name="water_acc_no[]"   value="{{ old('water_acc_no', isset($building)?  $building->water_acc_no : '' )}}" placeholder="Enter Water A/c. No"></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-dot-circle-o icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="water_met_no"  name="water_met_no[]"   value="{{ old('water_met_no', isset($building)?  $building->water_met_no : '' )}}" placeholder="Enter Water Met. No"></div></div></div><div class="col-sm-2"><button type="button" class="btn btn-primary add_button_ele">Add</button><button title="Delete" type="button" class="btn btn-warning remove_button_ele" style="display:none;"><i class="fa fa-trash-o "></i></button></div></div>';

                  $(wrapperEle).prepend(fieldEleHTML); //Add field html

              $('.field_wrapper_ele .row:not(:first)').find('.add_button_ele').remove();
              $('.field_wrapper_ele .row:not(:first)').find('.remove_button_ele').show();
                
              }

          }
        
      });
    
    //Once remove button is clicked
    $(wrapperEle).on('click', '.remove_button_ele_old', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();
        var reading_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Sure want to Delete this Reading')) {
             $.ajax({
              headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              url: '{{ url('destroyEleReading') }}' + '/' + reading_id,
              type: "DELETE",
              data: {  "_method": 'DELETE', 'reading_id': reading_id }
              });
             $(this).closest('.ele').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          }
          /*$(this).closest('.ele').remove();*/
        x--; //Decrement field counter

    });
    $(wrapperEle).on('click', '.remove_button_ele', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();
        
             $(this).closest('.ele').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          
        x--; //Decrement field counter

    });

/*****************************************************************************************/ 

  function initAutocomplete() {
 
    var input = document.getElementById('google_location'); 
    /*var local_val = document.getElementById('google_location').value; alert(local_val);*/
    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.setComponentRestrictions({'country': 'OM'});

    autocomplete.addListener('place_changed', function() {
      
      var place = autocomplete.getPlace();

      $('#building_geo_lat').val(place.geometry.location.lat());
      $('#building_geo_long').val(place.geometry.location.lng());
          
         
    });
           
     }
$("#google_location").on('change',function(){

  if($('.geoLocations').val()  != ''){
    $('#google_location-error').hide();
  }else{
    $('#google_location-error').show();
    $('#google_location-error').attr('style','display: inline !important;');
    
  }
});
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCLp1Ma7-2RoyI9C-BKY0tiyd-eAqg68dA&libraries=places&callback=initAutocomplete"
        async defer></script>


@endsection
