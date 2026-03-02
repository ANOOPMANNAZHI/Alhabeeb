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
      <div class="page-title">Service Report</div>
    </div>
    {{ Breadcrumbs::render('showServiceReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
       @can('view_service_report')  
      <form action="{{route('serviceReportPdf')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator" autocomplete="off">
        {{csrf_field()}} 
        <div class="dataSearchBox">
		<div class="row">
            <div class="col-sm-6 val2">
            <div class="form-group">
              <label for="simpleFormEmail">From Date</label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="date" class="form-control" id="start_date"  placeholder="Enter Date" name="start_date" >

              </div>
            </div>
          </div>  
          <div class="col-sm-6 val2">
            <div class="form-group">
              <label for="simpleFormEmail">To Date</label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="date" class="form-control" id="end_date"  placeholder="Enter Date" name="end_date" >
              </div>
            </div>
          </div>
          <div class="w-100"></div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="filter_type">Filter</label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="filter_type"  name="filter_type">
                  <option value="">Select Filter</option>                
                  <option value="1">Building Name</option>               
                  <option value="2">Complaint No</option>				  
                </select> 
              </div>               
            </div>
          </div> 
          <div class="col-sm-6">
            <div class="form-group">
              <label for="simpleFormEmail">Value</label>
              <div class="p-relative">
                <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="building_name"  placeholder="Enter Building Name" name="building_name" >
                <input  type="text" class="form-control" id="complaint_no"  placeholder="Enter Complaint No" name="complaint_no" >				  
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
    $('#complaint_no').hide();
	$('#service_report_no').hide();

    $(document).on('change',"#filter_type", function(){
    var type = $("#filter_type").val();
    if(type == 1){
      $('#complaint_no').hide();
	  $('#service_report_no').hide();
      $('#building_name').show();
      $('#building_name').val('');
      $('#complaint_no').val('');
	  $('#service_report_no').val('');
      $('#building_name').prop('required',true);
      $('#complaint_no').prop('required', false);
	  $('#service_report_no').prop('required', false);

    }else if(type == 3){
	  $('#building_name').hide();
	  $('#service_report_no').show();
      $('#complaint_no').hide();
      $('#complaint_no').val('');
      $('#building_name').val('');
	  $('#service_report_no').val('');
      $('#complaint_no').prop('required',false);
      $('#building_name').prop('required', false);
	  $('#service_report_no').prop('required', true);
	}else{
      $('#building_name').hide();
	  $('#service_report_no').hide();
      $('#complaint_no').show();
      $('#complaint_no').val('');
      $('#building_name').val('');
	  $('#service_report_no').val('');
      $('#complaint_no').prop('required',true);
      $('#building_name').prop('required', false);
	  $('#service_report_no').prop('required', false);
    }
  });
     //AutoComplete For Building Name
     /*********************************************************************************/ 
     $('#building_name').autocomplete({
      source : '{!!URL::route('tenancyBuildingReportAutocompleteCode')!!}',
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
