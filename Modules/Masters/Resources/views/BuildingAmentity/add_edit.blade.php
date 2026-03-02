@extends('layouts.plms-app')

@section('content')
<!-- start widget --> 
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Building Amenity</div>
    </div>
    
    {{ (isset($building_amentity))?   Breadcrumbs::render('building-amentity.edit',$building_amentity,$backUrlBreadCrumb,$backIdBreadCrumb ) :  Breadcrumbs::render('building-amentity.create',$backUrlBreadCrumb,$backIdBreadCrumb ) }}
    
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      <form action="{{ !isset($building_amentity)? route('building-amentity.store'): route('building-amentity.update',$building_amentity->id)}}" autocomplete="off" method="POST" id="form_sample_2" class="form-horizontal" >
        {{csrf_field()}} @if(isset($building_amentity)){{method_field('PUT')}}@endif
        <input type="hidden" name="backurl" value="{{(isset($previousUrl))?$previousUrl:''}}" >
        <!-- <div class="sub-head">Building Type Details</div> -->
        <div class="dataSearchBox ">
          
          <div class="row">
            

            <div class="col-sm-6">
              <div class="form-group">
                <label for="vendor_name">Amenity Type <small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-assistive-listening-systems icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="amentity_type_id"  name="amentity_type_id" required>
                    <option value="">Select Amenity Type </option>
                    @foreach($amentityTypes as $amentityType)
                    <option  {{(old('amentity_type_id', isset($building_amentity)?  $building_amentity->amentity_type_id : 0) == $amentityType->id) ? 'selected' : '' }} value="{{$amentityType->id}}">{{$amentityType->amentity_types_name}}</option>
                    @endforeach                  
                  </select>           
                </div> 
              </div>
            </div>          

            <div class="col-sm-6">
              <div class="form-group">
                <label for="vendor_name">Building <small class="textRed">*</small> </label>
                <div class="p-relative">
                  <i class="icon icon-building" aria-hidden="true"></i>
                  <select class="form-control landlord_buildings_id" id="landlord_buildings_id"  name="landlord_buildings_id" required>
                    <option value="">Select Building</option>
                    @foreach($buildings as $building)
                    <option  {{(old('landlord_building_id', isset($building_amentity)?  $building_amentity->landlord_building_id : $backIdBreadCrumb) == $building->id) ? 'selected' : '' }} value="{{$building->id}}">{{$building->building_name}}</option>
                    @endforeach                  
                  </select> 
                  <input type="hidden" name="landlord_building_id" value="{{old('landlord_building_id', isset($building_amentity)?  $building_amentity->landlord_building_id : $backIdBreadCrumb) }}" id="landlord_building_id">
                </div>
              </div>
            </div>
            @if(isset($building_amentity))
            <div class="col-sm-6">
              <div class="form-group">
                <label for="amc_contract_no">AMC Contract No</label>
                <div class="p-relative">
                  <i class="icon icon-contract" aria-hidden="true"></i>
                  <input type="text" class="form-control" autocomplete="off" id="amc_contract_no"  name="amc_contract_no"   value="{{ old('amc_contract_no', isset($building_amentity)?  $building_amentity->amc_contract_no : '' )}}" placeholder="Enter AMC Contract No">
                </div>
              </div>
            </div>
            @endif

            <div class="col-sm-12">
              <div class="form-group">
                <label for="building_amentity_utilities_remark">Remark </label>
                <div class="p-relative">
                  <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                  <textarea  class="form-control" autocomplete="off" id="building_amentity_utilities_remark"  name="building_amentity_utilities_remark" placeholder="Enter Remark"   >{{ old('building_amentity_utilities_remark', isset($building_amentity)?  $building_amentity->building_amentity_utilities_remark : '' )}}</textarea>
                </div>
              </div>
            </div> 

            <input type="hidden" name="backUrlBreadCrumb" id="backUrlBreadCrumb" value="{{$backUrlBreadCrumb}}">
            
            
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
    
    
    $(document).on('change',"#amentity_type_id, #landlord_buildings_id", function()
    {
      
      var amentity_id			= $('#amentity_type_id').val();
      var landlord_building_id 	= $('#landlord_buildings_id').val();
      
      
      if(landlord_building_id && amentity_id){
        $.ajax
        ({
          type: "POST",
          url: "{{route('buildingAmentityUnique')}}",
          data: {"landlord_building_id":landlord_building_id, "amentity_id":amentity_id ,"_token": "{{ csrf_token() }}"},
          cache: false,
          success: function(data)
          {
            if(data){
             $("#amentity_type_id").prop('selectedIndex',0);
             alert("Selected Amenity Type already exist in this Building");
             return false;
           }
           else{
            return true;
          }
        } 
      });

      }
      
      else{
        return true;
      }
    });
    /***...............*/
    var backUrlBreadCrumb =  $('#backUrlBreadCrumb').val();
    if(backUrlBreadCrumb == 'building.show'){
      $("#landlord_buildings_id").prop('disabled',true);
    }else{
      $("#landlord_buildings_id").prop('disabled',false);
    }
    $('#landlord_buildings_id').change(function(){
     var landlord_buildings_id = $("#landlord_buildings_id").val();
     //alert(landlord_buildings_id);
     $("#landlord_building_id").val(landlord_buildings_id);
   });
    
  });
</script>
@endsection
