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
      <div class="page-title">Expenses Details</div>
    </div>
    {{ Breadcrumbs::render('showPaymentHistoryReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      @can('view_payment_history_report') 
      <form action="{{route('expenseDetailsReportPdf')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <div class="dataSearchBox ">

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="start_date">Start Date<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="start_date" placeholder="Enter start date" name="start_date" required>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="tenant_contract_valid_to_date">Valid To<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="end_date" placeholder="Enter Valid To" name="end_date" required>
                </div>
              </div>
            </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="simpleFormEmail">Building Name<small class="textRed">*</small></label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                 <select class="form-control" name="building_name" id="building_name" class="building_name">
                    <option value="">All Buildings</option>
                    @foreach($buildings as $building)
                    <option value={{$building->id}}>{{$building->building_name.'('.$building->building_code.')'}}</option>
                    @endforeach
                  </select>
              </div>
            </div>
          </div> 
          <div class="col-sm-6">
              <div class="form-group">
                <label for="report_type">Report Type<small class="textRed">*</small>  </label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="report_type"  name="report_type" required>
                  <option value="">Select Report Type</option>                
                  <option value="1">Summary Report</option>                
                  <option value="2">Detailed Report</option>               
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
@endsection
