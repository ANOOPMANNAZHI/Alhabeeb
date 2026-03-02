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
      <div class="page-title">Unit Take Over Status(From------To------) </div>
    </div>
    {{ Breadcrumbs::render('showUnitTakeoverReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      @can('view_unit_takeover_report')
      <form action="{{route('unitTakeoverReportPdf')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} 
        <div class="dataSearchBox ">

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="start_date">From Date<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="start_date" placeholder="Enter start date" name="start_date" required>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="tenant_contract_valid_to_date">To Date<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="end_date" placeholder="Enter Valid To" name="end_date" required>
                </div>
              </div>
            </div> 

            <div class="col-sm-6">
              <div class="form-group">
                <label for="filter_type">Filter</label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="filter_type"  name="filter_type">
                  <option value="">Select Filter</option>                
                  <option value="">All Buildings</option>                
                  <option value="1">Building Name</option>               
                  <option value="2">Hand Over Date</option>               
                </select> 
              </div>               
            </div>
          </div> 
          <div class="col-sm-6">
            <div class="form-group">
              <label for="simpleFormEmail">Value</label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="building_name"  placeholder="Enter Building Name" name="building_name">
                <input type="date" class="form-control" id="handover_date" placeholder="Enter Valid To" name="handover_date" style="display: none;">
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

    $.validator.addMethod("greaterThan", 
      function(value, element, params) {

        if (!/Invalid|NaN/.test(new Date(value))) {
          return new Date(value) > new Date($(params).val());
        }

        return isNaN(value) && isNaN($(params).val()) 
        || (Number(value) > Number($(params).val())); 
      },'Must be greater than End Date.');
    $("#form_sample_2").validate({
      rules: {
        end_date: { greaterThan: "#start_date" ,

      }


    }
  });


    $(document).on('change',"#filter_type", function(){
      var type = $("#filter_type").val();
      if(type == 1){
        $('#building_name').prop('readonly', false);
        $('#building_name').prop('required',true);
        $('#handover_date').prop('required',false);
        $('#handover_date').val('');
        $('#handover_date').hide();
       $('#building_name').show();

      }else if(type ==2){
       $('#handover_date').prop('required', true);
       $('#building_name').prop('required',false);
       $('#building_name').val('');
       $('#handover_date').show();
       $('#building_name').hide();
     }else{
       $('#building_name').prop('readonly', true);
       $('#building_name').prop('required',false);
       $('#handover_date').prop('required',false);
       $('#handover_date').val('');
       $('#building_name').val('');
       $('#handover_date').hide();
       $('#building_name').show();
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
