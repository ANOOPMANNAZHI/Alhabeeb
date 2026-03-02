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
      <div class="page-title">Monthly Tenancy Details</div>
    </div>
    {{ Breadcrumbs::render('showMonthlyTenancyReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
        @can('view_monthly_tenancy_report')
      <form action="{{route('monthlyTenancyReportPdf')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <div class="dataSearchBox ">

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="as_on_date">As On Date<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="as_on_date" name="as_on_date" value="{{ date('Y-m-d') }}" required>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="location_id">Location</label>
                <div class="p-relative">
                  <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="location_id" name="location_id">
                    <option value="">All</option>
                    @foreach($locations as $location)
                      <option value="{{$location->id}}">{{$location->locations_name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="management_id">Management Type</label>
                <div class="p-relative">
                  <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="management_id" name="management_id">
                    <option value="">All</option>
                    @foreach($managementTypes as $managementType)
                      <option value="{{$managementType->id}}">{{$managementType->management_types_name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="download_type">Download Type<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-download icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="download_type" name="download_type" required>
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
