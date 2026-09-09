@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
{{-- Styles live here, not in @section('css'): the plms-app layout only yields 'content' and 'scripts'. --}}
<style>
    #rcs_table th { white-space: nowrap; }
    #rcs_table td.num, #rcs_table th.num { text-align: right; }
    .rcs-loading { opacity: .5; }
</style>
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left">
      <div class="page-title">Rent Collection Summary</div>
    </div>
    {{ Breadcrumbs::render('showRentCollectionSummary') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      <form id="rcs_filter" class="form-horizontal" onsubmit="return false;">
        <div class="dataSearchBox">
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label for="building_name">Building</label>
                <div class="p-relative">
                  <i class="fa fa-search icn-add" aria-hidden="true"></i>
                  <input type="text" class="form-control" id="building_name" name="building_name" value="{{ $buildingName }}" placeholder="Search building name or code" autocomplete="off">
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="month">Month</label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="month" name="month">
                    @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(2000, $m, 1)->format('F') }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="year">Year</label>
                <div class="p-relative">
                  <i class="fa fa-calendar icn-add" aria-hidden="true"></i>
                  <input type="number" class="form-control" id="year" name="year" value="{{ $year }}" min="2015" max="2035">
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>&nbsp;</label>
                <div>
                  <button type="button" id="rcs_apply" class="btn btn-primary">APPLY</button>
                  <button type="button" id="rcs_reset" class="btn btn-default" title="Current month, all buildings">RESET</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div class="card card-box">
      <div class="card-body">
        <p class="text-muted" style="margin-bottom:8px">
          Collection = rent receipts dated in the month (excluding cancelled). Pending = monthly rent of the contracts active in the month minus the collection.
          For the current month this is the collection till today.
        </p>
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="rcs_table">
            <thead>
              <tr>
                <th width="5%">#</th>
                <th>Building</th>
                <th width="12%">Month</th>
                <th class="num" width="18%">Total Rent Collection</th>
                <th class="num" width="18%">Pending Collection</th>
              </tr>
            </thead>
            <tbody id="rcs_body">
              @include('backoffice::Reports.rent_collection_summary_ajax')
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
$(function () {
    var url = "{{ route('showRentCollectionSummary') }}";

    var timer = null;

    function load() {
        var $tbody = $('#rcs_body').addClass('rcs-loading');
        $.get(url, {
            month: $('#month').val(),
            year: $('#year').val(),
            building_name: $('#building_name').val()
        }, function (html) {
            $tbody.html(html).removeClass('rcs-loading');
        }).fail(function () {
            $tbody.removeClass('rcs-loading').html('<tr><td colspan="5" align="center" class="text-danger">Could not load data.</td></tr>');
        });
    }

    $('#rcs_apply').on('click', load);
    $('#month').on('change', load);
    $('#year').on('change', function () { if (this.value.length === 4) load(); });

    // Search as you type, settling briefly so one keystroke is not one request.
    $('#building_name').on('keyup', function (e) {
        if (e.key === 'Enter') { clearTimeout(timer); load(); return; }
        clearTimeout(timer);
        timer = setTimeout(load, 400);
    });

    $('#rcs_reset').on('click', function () {
        $('#month').val({{ (int) date('n') }});
        $('#year').val({{ (int) date('Y') }});
        $('#building_name').val('');
        load();
    });
});
</script>
@endsection
