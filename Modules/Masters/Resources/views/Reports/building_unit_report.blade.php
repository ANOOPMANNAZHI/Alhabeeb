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
      <div class="page-title">Building Unit Details</div>
    </div>
    {{ Breadcrumbs::render('showBuildingUnitDetails') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
       @can('view_building_unit_details')  
      <form action="{{route('showBuildingUnitDetailsReport')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator" autocomplete="off">
        {{csrf_field()}} 
        <div class="dataSearchBox">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="filter_type">Filter<small class="textRed">*</small>  </label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="filter_type"  name="filter_type" required>
                  <option value="">Select Filter</option>                
                  <option value="1">Building Name</option>                
                  <option value="2">Building Code</option> 
				  <option value="3">Management</option>  				  
                </select> 
              </div>               
            </div>
          </div> 
          <div class="col-sm-6">
            <div class="form-group">
              <label for="simpleFormEmail">Value</label>
              <div class="p-relative">
                <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="building_name"  placeholder="Enter Building Name" name="building_name" required>

                <input  type="text" class="form-control" id="building_code"  placeholder="Enter Building Code" name="building_code" required>
				  <select class="form-control" id="management_type"  name="management_type">
                  <option value="">Select Type</option>                
                  <option value="all">All Management</option>                
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
                  <option value="pdf">PDF</option>                
                  <option value="excel">Excel</option>               
                </select> 
              </div>               
            </div>
          </div>
          <div class="w-100"></div>
          <div class="w-100"></div> 
          <div class="col">
            <div class="w-100"></div>
            <button type="submit" name="search" class="btn btn-primary">Generate</button>
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
    $('#building_code').hide();
	$('#management_type').hide();

    $(document).on('change',"#filter_type", function(){
    var type = $("#filter_type").val();
    if(type == 1){
      $('#building_code').hide();
	  $('#management_type').hide();
      $('#building_name').show();
      $('#building_name').val('');
      $('#building_code').val('');
	  $('#management_type').val('');
      $('#building_name').prop('required',true);
      $('#building_code').prop('required', false);
	  $('#management_type').prop('required', false);

    }else if(type == 3){
	  $('#building_name').hide();
	  $('#management_type').show();
      $('#building_code').hide();
      $('#building_code').val('');
      $('#building_name').val('');
	  $('#management_type').val('');
      $('#building_code').prop('required',false);
      $('#building_name').prop('required', false);
	  $('#management_type').prop('required', true);
	}else{
      $('#building_name').hide();
	  $('#management_type').hide();
      $('#building_code').show();
      $('#building_code').val('');
      $('#building_name').val('');
	  $('#management_type').val('');
      $('#building_code').prop('required',true);
      $('#building_name').prop('required', false);
	  $('#management_type').prop('required', false);
    }
  });
    //AutoComplete For Building Name
 /*********************************************************************************/ 
 $('#building_name').autocomplete({
  source : '{!!URL::route('buildingReportAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#building_name").val('');
      $('#building_name-error').show();
    }
  }
});
   //AutoComplete For Building Code
 /*********************************************************************************/ 
 $('#building_code').autocomplete({
  source : '{!!URL::route('buildingCodeReportAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#building_code").val('');
      $('#building_code-error').show();
    }
  }
});

 });
</script>
@endsection
