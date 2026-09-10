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
      <div class="page-title">Tenant Receivable v2 as on (Date)</div>
    </div>
    {{ Breadcrumbs::render('showtenantReceivablesReportV2') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
          
      <form action="{{route('tenantReceivablesReportPdfV2')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        <div class="dataSearchBox ">
            {{csrf_field()}}
          <div class="row">
          <div class="col-sm-6">
              <div class="form-group">
                <label for="filter_type">Filter </label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="filter_type"  name="filter_type">
                  <option value="">Select Filter</option>
                  <option value="1">Building Name</option>
                  <option value="5">Building No</option>
                  <option value="2">Tenant Name</option>
                  <option value="3">Management</option>
                  <option value="6">ARE</option>
                </select>
              </div>
            </div>
          </div>
            <div class="col-sm-6 val1">
              <div class="form-group">
                <label for="simpleFormEmail">Value</label>
                <div class="p-relative">
                  <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                  <input  type="text" class="form-control" id="building_name"  placeholder="Enter Building Name" name="building_name">
                  <input  type="text" class="form-control" id="building_no"  placeholder="Enter Building No" name="building_no" style="display: none;">
                  <input  type="text" class="form-control" id="tenant_name"  placeholder="Enter Tenant Name" name="tenant_name" style="display: none;">
                  <select class="form-control" id="management_type"  name="management_type" style="display: none;">
                  <option value="">Select Type</option>
                  <option value="">All Management</option>
                  <option value="Comprehensive">Comprehensive</option>
                  <option value="Normal">Normal</option>
                  <option value="Commission">Commission</option>
                </select>
                <input  type="text" class="form-control" id="are"  placeholder="Enter ARE" name="are" style="display: none;">
                </div>
              </div>
            </div>
            <div class="w-100"></div>
             <div class="col-sm-6 val2">
              <div class="form-group">
                <label for="simpleFormEmail">As on Date<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                 <input  type="date" class="form-control" id="end_date"  placeholder="Enter Received Date" name="end_date" required>
                </div>
              </div>
            </div>
            <div class="col-sm-6 val2">
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
            <div class="col">
              <div class="w-100"></div>
              <button type="submit" name="search" class="btn btn-primary">Generate</button>
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
    $('#management_type').hide();
    $('#building_no').hide();
    $('#tenant_name').hide();
    $('#are').hide();
    $('.val1').show();

   $(document).on('change',"#filter_type", function(){
    var type = $("#filter_type").val();
    if(type == 1){
      $('#management_type').hide();
      $('#building_no').hide();
      $('#tenant_name').hide();
      $('#are').hide();
      $('.val1').show();
      $('#building_name').show();
      $('#building_name').val('');
      $('#location').val('');
      $('#management_type').val('');
      $('#tenant_name').val('');
      $('#building_no').val('');
      $('#are').val('');
      $('#building_name').prop('required',true);
      $('#management_type').prop('required', false);
      $('#tenant_name').prop('required', false);
      $('#building_no').prop('required', false);
      $('#are').prop('required', false);

    }else if(type == 2){
      $('#management_type').hide();
      $('#building_no').hide();
      $('#building_name').hide();
      $('#are').hide();
      $('.val1').show();
      $('#tenant_name').show();
      $('#tenant_name').val('');
      $('#management_type').val('');
      $('#building_name').val('')
      $('#building_no').val('')
      $('#are').val('')
      $('#tenant_name').prop('required',true);
      $('#management_type').prop('required', false);
      $('#building_name').prop('required', false);
      $('#building_no').prop('required', false);
      $('#are').prop('required', false);
    }else if(type == 3){
      $('#management_type').show();
      $('#building_name').hide();
      $('#tenant_name').hide();
      $('#building_no').hide();
      $('#are').hide();
      $('.val1').show();
      $('#tenant_name').val('');
      $('#management_type').val('');
      $('#building_name').val('')
      $('#building_no').val('')
      $('#are').val('')
      $('#tenant_name').prop('required',false);
      $('#management_type').prop('required', true);
      $('#building_name').prop('required', false);
      $('#building_no').prop('required', false);
      $('#are').prop('required', false);
    }
    else if(type == 5){
      $('#building_no').show();
      $('#management_type').hide();
      $('#building_name').hide();
      $('#tenant_name').hide();
      $('#are').hide();
      $('#tenant_name').val('');
      $('.val1').show();
      $('#management_type').val('');
      $('#building_name').val('')
      $('#received_date').val('')
      $('#building_no').val('')
      $('#are').val('')
      $('#tenant_name').prop('required',false);
      $('#management_type').prop('required', false);
      $('#building_name').prop('required', false);
      $('#building_no').prop('required', true);
      $('#are').prop('required', false);
    }else if(type == 6){
      $('#building_no').hide();
      $('#management_type').hide();
      $('#building_name').hide();
      $('#tenant_name').hide();
      $('#are').show();
      $('#tenant_name').val('');
      $('.val1').show();
      $('#management_type').val('');
      $('#building_name').val('')
      $('#received_date').val('')
      $('#building_no').val('')
      $('#are').val('')
      $('#tenant_name').prop('required',false);
      $('#management_type').prop('required', false);
      $('#building_name').prop('required', false);
      $('#building_no').prop('required', false);
      $('#are').prop('required', true);
    }else{
      $('#building_no').hide();
      $('#management_type').hide();
      $('#are').hide();
      $('#building_name').show();
      $('#tenant_name').hide();
      $('#tenant_name').val('');
      $('#are').val('');
      $('.val1').show();
      $('#management_type').val('');
      $('#building_name').val('')
      $('#received_date').val('')
      $('#building_no').val('')
      $('#tenant_name').prop('required',false);
      $('#management_type').prop('required', false);
      $('#building_name').prop('required', false);
      $('#building_no').prop('required', false);
      $('#are').prop('required', false);
    }
  });

 $('#tenant_name').autocomplete({
  source : '{!!URL::route('tenantReportAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#tenant_name").val('');
      $('#tenant_name-error').show();
    }
  }
});

 $('#building_name').autocomplete({
  source : '{!!URL::route('buildingNameReportAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#building_name").val('');
      $('#building_name-error').show();
    }
  }
});

 $('#building_no').autocomplete({
  source : '{!!URL::route('buildingNoReportAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#building_no").val('');
      $('#building_no-error').show();
    }
  }
});

 $('#are').autocomplete({
  source : '{!!URL::route('areReportAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#are").val('');
      $('#are-error').show();
    }
  }
});
 });
</script>
@endsection
