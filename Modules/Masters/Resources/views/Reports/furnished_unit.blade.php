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
      <div class="page-title">Report on units Rented as Furnished units</div>
    </div>
    {{ Breadcrumbs::render('showFurnishedUnitReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
       @can('view_furnished_unit_report') 
      <form action="{{route('furnishedUnitReportPdf')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator" autocomplete="off">
        {{csrf_field()}} 
        <div class="dataSearchBox">
          <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label for="simpleFormEmail">Building Name<small class="textRed">*</small>  </label>
              <div class="p-relative">
                <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="building_name"  placeholder="Enter Building Name" name="building_name" required>
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
  $( document ).ready(function() {


    //AutoComplete For Building Code
 /*********************************************************************************/ 
 $('#building_name').autocomplete({
  source : '{!!URL::route('furnishedBuildingReportAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#building_name").val('');
      $('#building_name-error').show();
    }
  }
});

}); 
</script>
@endsection

furnishedBuildingReportAutocompleteCode