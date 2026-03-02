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
      <div class="page-title">Cheque Return Statement </div>
    </div>
    {{ Breadcrumbs::render('showchequeReturnReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
        @can('view_cheque_return_report')  
      <form action="{{route('chequeReturnReportPdf')}}" method="POST" target="_blank" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} 
        <div class="dataSearchBox ">

          <div class="row">
            <div class="col-sm-6 val2">
            <div class="form-group">
              <label for="simpleFormEmail">From Date<small class="textRed">*</small></label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="date" class="form-control" id="start_date"  placeholder="Enter Date" name="start_date" required>

              </div>
            </div>
          </div>  
          <div class="col-sm-6 val2">
            <div class="form-group">
              <label for="simpleFormEmail">To Date<small class="textRed">*</small></label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="date" class="form-control" id="end_date"  placeholder="Enter Date" name="end_date" required>
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
                  <option value="2">ARE</option>                
                </select> 
              </div>               
            </div>
          </div> 
          <div class="col-sm-6 val1">
            <div class="form-group">
              <label for="simpleFormEmail">Value</label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <select class="form-control building_name" id="building_name"  name="building_name">
                    <option value="">Select Building</option>
                    <option value="">All Buildings</option>
                    @foreach($buildings as $building)
                    <option value="{{$building->id}}">{{$building->building_name}}</option>
                    @endforeach                  
                  </select> 

                   <select class="form-control are" id="are"  name="are"  style="display: none;">
                    <option value="">Select ARE</option>
                    <option value="">All ARE</option>
                    @foreach($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->employee_name}}</option>
                    @endforeach                  
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

    /*******************On chANGE******************/
    $(document).on('change',"#filter_type", function(){
      var type = $("#filter_type").val();
      if(type == 1){
        $('#are').hide();
        $('#building_name').show();
        $('#are').val('');
        $('#building_name').val('');
        $('#building_name').prop('required',true);
        $('#are').prop('required', false);

      }else if(type == 2){
        $('#building_name').hide();
        $('#are').show();
        $('#building_name').val('');
        $('#are').val('');
        $('#are').prop('required',true);
        $('#building_name').prop('required', false);
      }else{
        $('#building_name').show();
        $('#are').hide();
        $('#building_name').val('');
        $('#are').val('');
        $('#are').prop('required',false);
        $('#building_name').prop('required', false);
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

  //AutoComplete For ARE Name
  /*********************************************************************************/ 
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
