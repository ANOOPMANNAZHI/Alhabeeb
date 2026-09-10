@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<style>
  /* ── Progress overlay ─────────────────────────────────────── */
  #mirv2-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 9999;
    align-items: center;
    justify-content: center;
  }
  #mirv2-overlay.active { display: flex; }
  #mirv2-card {
    background: #fff;
    border-radius: 10px;
    padding: 36px 44px;
    min-width: 380px;
    max-width: 480px;
    width: 90%;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.22);
  }
  #mirv2-card h5 { color: #1F4E79; font-weight: 700; font-size: 16px; margin-bottom: 6px; }
  #mirv2-msg { color: #555; font-size: 13px; margin-bottom: 18px; min-height: 18px; }
  #mirv2-track { background: #e2eaf3; border-radius: 20px; height: 18px; overflow: hidden; margin-bottom: 10px; }
  #mirv2-bar  { height: 100%; width: 0%; background: linear-gradient(90deg,#1F4E79,#2E75B6); border-radius: 20px; transition: width .4s ease; }
  #mirv2-pct  { font-size: 20px; font-weight: 700; color: #1F4E79; }
  #mirv2-note { font-size: 11px; color: #999; margin-top: 14px; }
</style>
@endsection
@section('content')

{{-- ── Progress overlay ──────────────────────────────────────────────────── --}}
<div id="mirv2-overlay">
  <div id="mirv2-card">
    <h5>Generating Report</h5>
    <div id="mirv2-msg">Preparing…</div>
    <div id="mirv2-track"><div id="mirv2-bar"></div></div>
    <div id="mirv2-pct">0%</div>
    <div id="mirv2-note">Please keep this tab open until the download starts.</div>
  </div>
</div>

<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Maintenance Invoice Report v2</div>
    </div>
    {{ Breadcrumbs::render('showMaintenanceInvoiceReportV2') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      @can('view_maintenance_invoice_report_v2')
      <form action="{{route('maintenanceInvoiceReportPdfV2')}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
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
                <label for="end_date">Valid To<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="end_date" placeholder="Enter Valid To" name="end_date" required>
                </div>
              </div>
            </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="building_name">Building Name<small class="textRed">*</small></label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                 <select class="form-control" name="building_name" id="building_name">
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
                <label for="expense_type">Expense Type</label>
                <div class="p-relative">
                 <i class="fa fa-filter icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="expense_type" name="expense_type">
                  <option value="">All</option>
                  <option value="inhouse">In-house</option>
                  <option value="subcontractor">Subcontractor</option>
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

    // ── Progress overlay + SSE generate ─────────────────────────────────
    // Same pattern as Normal Management Report v2: submit opens an
    // EventSource that streams {pct, msg} progress while the report (and,
    // for any in-house lines, the per-building service reports ZIP) is
    // built server-side, then triggers the download(s) once ready.
    var $overlay = $('#mirv2-overlay');
    var $bar     = $('#mirv2-bar');
    var $msg     = $('#mirv2-msg');
    var $pct     = $('#mirv2-pct');
    var streamUrl    = '{{ route("maintenanceInvoiceReportV2Stream") }}';
    var downloadBase = '{{ url("maintenanceInvoiceReportV2Download") }}';

    function setProgress(p, m) {
      $bar.css('width', p + '%');
      $pct.text(p + '%');
      if (m) $msg.text(m);
    }

    $('#form_sample_2').on('submit', function (e) {
      e.preventDefault();

      if (!$(this).valid()) {
        return;
      }

      var params = new URLSearchParams({
        start_date:    $('#start_date').val(),
        end_date:      $('#end_date').val(),
        building_name: $('#building_name').val(),
        expense_type:  $('#expense_type').val(),
        download_type: $('#download_type').val()
      });

      setProgress(0, 'Preparing…');
      $overlay.addClass('active');

      var evtSource = new EventSource(streamUrl + '?' + params.toString());

      evtSource.onmessage = function (e) {
        try {
          var data = JSON.parse(e.data);
          setProgress(data.pct || 0, data.msg || '');
          if (data.done) {
            evtSource.close();

            window.location.href = downloadBase + '/' + data.reportToken;

            if (data.zipToken) {
              setTimeout(function () {
                var iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = downloadBase + '/' + data.zipToken;
                document.body.appendChild(iframe);
              }, 700);
            }

            setTimeout(function () { $overlay.removeClass('active'); }, 1200);
          }
        } catch (err) {}
      };

      evtSource.onerror = function () {
        evtSource.close();
        $overlay.removeClass('active');
        alert('An error occurred while generating the report. Please try again.');
      };
    });

  });
</script>
@endsection
