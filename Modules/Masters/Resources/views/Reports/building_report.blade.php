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
      <div class="page-title">Building Details</div>
    </div>
    {{ Breadcrumbs::render('showBuildingDetails') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
         @can('view_building_detail') 
      <form action="{{route('showBuildingDetailsReport')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} 
        <div class="dataSearchBox ">
          
          <div class="row">
          <div class="col-sm-6">
              <div class="form-group">
                <label for="filter_type">Filter<small class="textRed">*</small>  </label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="filter_type"  name="filter_type" required>
                  <option value="">Select Filter</option>                
                  <option value="1">Landlord</option>                
                  <option value="2">Location</option>                
                  <option value="3">Management</option>                
                </select> 
              </div>               
            </div>
          </div> 
            <div class="col-sm-6">
              <div class="form-group">
                <label for="simpleFormEmail">Value<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                  <input  type="text" class="form-control" id="vendor_name"  placeholder="Enter Landlord" name="vendor_name">
                  <input  type="text" class="form-control" id="location"  placeholder="Enter Location" name="location">
                  <select class="form-control" id="management_type"  name="management_type">
                  <option value="">Select Type</option>                
                  <option value="">All Management</option>                
                  <option value="Comprehensive">Comprehensive</option>                
                  <option value="Normal">Normal</option>                
                  <option value="Commission">Commission</option>                
                </select> 
                </div>
              </div>
            </div>  
            <div class="col-sm-6">
              <div class="form-group">
                <label for="download_type">Download Type<small class="textRed">*</small>  </label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="download_type"  name="download_type" required>
                  <option value="">Select Download Type</option>                
                  <option value="pdf" selected>PDF</option>                
                  <option value="excel">Excel</option>               
                </select> 
              </div>               
            </div>
          </div>
          <div class="w-100"></div>
            <div class="w-100"></div>
            <div class="col">
              <div class="w-100"></div>
              <button type="submit" name="search" class="btn btn-primary">Search</button>
            </div>
            
          </div>
          
        </div>
        <div class="clearfix"></div>
      </form>
      @endcan 
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
   $(document).ready(function() {
    $('#management_type').hide();
    $('#location').hide();

   $(document).on('change',"#filter_type", function(){
    var type = $("#filter_type").val();
    if(type == 1){
      $('#management_type').hide();
      $('#location').hide();
      $('#vendor_name').show();
      $('#vendor_name').val('');
      $('#location').val('');
      $('#management_type').val('');
      $('#vendor_name').prop('required',true);
      $('#management_type').prop('required', false);
      $('#location').prop('required', false);

    }else if(type == 2){
      $('#management_type').hide();
      $('#vendor_name').hide();
      $('#location').show();
      $('#location').val('');
      $('#management_type').val('');
      $('#vendor_name').val('')
      $('#location').prop('required',true);
      $('#management_type').prop('required', false);
      $('#vendor_name').prop('required', false);
    }else{
      $('#location').hide();
      $('#vendor_name').hide();
      $('#management_type').show();
      $('#management_type').val('');
      $('#vendor_name').val('');
      $('#location').val('');
      $('#management_type').prop('required',true);
      $('#location').prop('required', false);
      $('#vendor_name').prop('required', false);
    }
  });

       //AutoComplete For Landlord/Vendor Name
 /*********************************************************************************/ 
 $('#vendor_name').autocomplete({
  source : '{!!URL::route('landlordReportAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#vendor_name").val('');
      $('#vendor_name-error').show();
    }
  }
});
       //AutoComplete For Location Name
 /*********************************************************************************/ 
 $('#location').autocomplete({
  source : '{!!URL::route('locationReportAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#location").val('');
      $('#location-error').show();
    }
  }
});
 });
</script>
@endsection
