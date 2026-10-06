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
      <div class="page-title">Cash Receipts</div>
    </div>
    {{ Breadcrumbs::render('showCashReceiptReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      @can('view_cash_receipt_report')
      <form action="{{route('cashReceiptReportPdf')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" data-toggle="validator">
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
                <label for="end_date">End Date<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="end_date" placeholder="Enter end date" name="end_date" required>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="receipt_type">Receipt Type</label>
                <div class="p-relative">
                 <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="receipt_type" name="receipt_type">
                  <option value="" selected>All (Rent, Deposit, General)</option>
                  <option value="0">Rent</option>
                  <option value="2">Deposit</option>
                  <option value="1">General</option>
                 </select>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="building_name">Building</label>
                <div class="p-relative">
                  <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                  <select class="form-control" name="building_name" id="building_name">
                    <option value="">All Buildings</option>
                    @foreach($buildings as $building)
                    <option value="{{$building->id}}">{{$building->building_name.' ('.$building->building_code.')'}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="management_type">Management Type</label>
                <div class="p-relative">
                 <i class="fa fa-building icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="management_type" name="management_type">
                  <option value="">All Management Types</option>
                  @foreach($managementTypes as $mt)
                    <option value="{{ $mt->management_types_name }}">{{ $mt->management_types_name }}</option>
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
            <p class="text-muted" style="font-size:13px">Cash receipts only, of every type. Cancelled and deleted receipts are left out. General receipts with no contract have no building, so they appear only when no building is chosen.</p>
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
    $.validator.addMethod("notBefore",
      function(value, element, params) {
        if (!value || !$(params).val()) { return true; }
        return new Date(value) >= new Date($(params).val());
      },'End Date must be on or after the Start Date.');
    $("#form_sample_2").validate({
      rules: {
        end_date: { notBefore: "#start_date" }
      }
    });
  });
</script>
@endsection
