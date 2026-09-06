@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<style>
  #ltir-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 9999;
    align-items: center;
    justify-content: center;
  }
  #ltir-overlay.active { display: flex; }
  #ltir-card {
    background: #fff;
    border-radius: 10px;
    padding: 36px 44px;
    min-width: 380px;
    max-width: 480px;
    width: 90%;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.22);
  }
  #ltir-card h5 { color: #1F4E79; font-weight: 700; font-size: 16px; margin-bottom: 6px; }
  #ltir-msg { color: #555; font-size: 13px; margin-bottom: 18px; min-height: 18px; }
  #ltir-track { background: #e2eaf3; border-radius: 20px; height: 18px; overflow: hidden; margin-bottom: 10px; }
  #ltir-bar  { height: 100%; width: 0%; background: linear-gradient(90deg,#1F4E79,#2E75B6); border-radius: 20px; transition: width .4s ease; }
  #ltir-pct  { font-size: 20px; font-weight: 700; color: #1F4E79; }
  #ltir-note { font-size: 11px; color: #999; margin-top: 14px; }
</style>
@endsection
@section('content')

<div id="ltir-overlay">
  <div id="ltir-card">
    <h5>Generating Invoice</h5>
    <div id="ltir-msg">Preparing…</div>
    <div id="ltir-track"><div id="ltir-bar"></div></div>
    <div id="ltir-pct">0%</div>
    <div id="ltir-note">Please keep this tab open until the download starts.</div>
  </div>
</div>

<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left">
      <div class="page-title">Landlord Tax Invoice Report</div>
    </div>
    {{ Breadcrumbs::render('showLandlordTaxInvoiceReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      @can('view_landlord_tax_invoice_report')
      <form id="ltir_form" class="form-horizontal" autocomplete="off">
        <div class="dataSearchBox">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="from_date">From Date<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="from_date" name="from_date" required>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="to_date">To Date<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="to_date" name="to_date" required>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="vendor_name">Landlord<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-user icn-add" aria-hidden="true"></i>
                  <input type="text" class="form-control" id="vendor_name" placeholder="Enter Landlord Name" required autocomplete="off">
                  <input type="hidden" id="vendor_id" name="vendor_id">
                </div>
              </div>
            </div>
            <div class="w-100"></div>
            <div class="col">
              <div class="w-100"></div>
              <button type="submit" id="ltir_submit" class="btn btn-primary">Generate</button>
            </div>
          </div>
        </div>
      </form>
      @endcan
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function () {
    $.validator.addMethod("greaterThan",
      function (value, element, params) {
        if (!/Invalid|NaN/.test(new Date(value))) {
          return new Date(value) > new Date($(params).val());
        }
        return isNaN(value) && isNaN($(params).val())
          || (Number(value) > Number($(params).val()));
      }, 'Must be greater than From Date.');
    $('#ltir_form').validate({
      rules: { to_date: { greaterThan: '#from_date' } }
    });

    $('#vendor_name').autocomplete({
      source: '{!!URL::route('landlordAutocompleteCode')!!}',
      minLength: 2,
      autoFocus: true,
      change: function (e, ui) {
        if (ui.item == null || ui.item == undefined) {
          $('#vendor_name').val('');
          $('#vendor_id').val('');
        } else {
          $('#vendor_id').val(ui.item.ids);
        }
      }
    });

    var $overlay = $('#ltir-overlay');
    var $bar     = $('#ltir-bar');
    var $msg     = $('#ltir-msg');
    var $pct     = $('#ltir-pct');
    var streamUrl    = '{{ route("landlordTaxInvoiceReportStream") }}';
    var downloadBase = '{{ url("landlordTaxInvoiceReportDownload") }}';

    function setProgress(p, m) {
      $bar.css('width', p + '%');
      $pct.text(p + '%');
      if (m) $msg.text(m);
    }

    $('#ltir_form').on('submit', function (e) {
      e.preventDefault();

      if (!$(this).valid()) return;
      if (!$('#vendor_id').val()) {
        alert('Please select a landlord from the suggestions list.');
        return;
      }

      var params = new URLSearchParams({
        from_date: $('#from_date').val(),
        to_date:   $('#to_date').val(),
        vendor_id: $('#vendor_id').val()
      });

      setProgress(0, 'Preparing…');
      $overlay.addClass('active');

      var evtSource = new EventSource(streamUrl + '?' + params.toString());

      evtSource.onmessage = function (e) {
        try {
          var data = JSON.parse(e.data);
          setProgress(data.pct || 0, data.msg || '');
          if (data.error) {
            evtSource.close();
            $overlay.removeClass('active');
            alert(data.msg || 'An error occurred while generating the invoice.');
            return;
          }
          if (data.done) {
            evtSource.close();
            window.location.href = downloadBase + '/' + data.token;
            setTimeout(function () { $overlay.removeClass('active'); }, 1200);
          }
        } catch (err) {}
      };

      evtSource.onerror = function () {
        evtSource.close();
        $overlay.removeClass('active');
        alert('An error occurred while generating the invoice. Please try again.');
      };
    });
  });
</script>
@endsection
