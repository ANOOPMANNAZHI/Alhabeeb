@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<style>
  .building-checkbox-list {
    max-height: 220px;
    overflow-y: auto;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 8px;
    background: #fff;
  }
  .building-checkbox-list label {
    display: block;
    padding: 4px 6px;
    margin: 0;
    cursor: pointer;
    font-weight: normal;
  }
  .building-checkbox-list label:hover {
    background: #f0f0f0;
  }
  .building-checkbox-list input[type="checkbox"] {
    margin-right: 8px;
  }
</style>
@endsection
@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Tenancy Details MERA</div>
    </div>
    {{ Breadcrumbs::render('showtenancyDetailsMeraReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
        @can('view_tenancy_details_mera_report')
      <form action="{{route('tenancyDetailsMeraReportDownload')}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <div class="dataSearchBox ">

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>Buildings<small class="textRed">*</small></label>
                <div style="margin-bottom:6px;">
                  <button type="button" id="select_all_btn" class="btn btn-sm btn-default">Select All</button>
                  <button type="button" id="deselect_all_btn" class="btn btn-sm btn-default">Deselect All</button>
                  <span id="selected_count" style="margin-left:10px; font-size:12px; color:#888;">0 selected</span>
                </div>
                <div class="building-checkbox-list">
                  @foreach($buildings as $building)
                  <label>
                    <input type="checkbox" name="building_ids[]" value="{{ $building->id }}" class="building-chk">
                    {{ $building->building_name }} ({{ $building->building_code }})
                  </label>
                  @endforeach
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
@section('scripts')
<script>
$(document).ready(function() {
    function updateCount() {
        var count = $('.building-chk:checked').length;
        $('#selected_count').text(count + ' selected');
    }

    $('#select_all_btn').on('click', function() {
        $('.building-chk').prop('checked', true);
        updateCount();
    });

    $('#deselect_all_btn').on('click', function() {
        $('.building-chk').prop('checked', false);
        updateCount();
    });

    $('.building-chk').on('change', function() {
        updateCount();
    });

    $('#form_sample_2').on('submit', function(e) {
        if ($('.building-chk:checked').length === 0) {
            alert('Please select at least one building.');
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection
