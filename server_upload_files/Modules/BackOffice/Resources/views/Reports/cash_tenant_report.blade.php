@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
{{-- Styles live here, not in @section('css'): the plms-app layout only yields 'content' and 'scripts'. --}}
<style>
    /* ---- header bar: caption on the left, exports on the right ---- */
    .ctr-head {
        display: table;
        width: 100%;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid #e4e9f0;
    }
    .ctr-head-left, .ctr-head-right { display: table-cell; vertical-align: middle; }
    .ctr-head-right { text-align: right; white-space: nowrap; }

    .ctr-caption { margin: 0; color: #5b6b7f; font-size: 13px; line-height: 1.6; }
    .ctr-count {
        display: inline-block;
        margin-left: 6px;
        padding: 2px 10px;
        border-radius: 11px;
        background: #eef3fb;
        color: #1f3864;
        font-weight: 600;
    }

    .ctr-head-right .btn {
        margin-left: 10px;
        padding: 7px 16px;
        font-size: 13px;
        border-radius: 4px;
    }
    .ctr-head-right .btn i { margin-right: 6px; }

    /* ---- table ---- */
    #ctr_table { margin-bottom: 0; }
    #ctr_table th {
        white-space: nowrap;
        padding: 12px 14px;
        background: #f7f9fc;
        border-bottom: 2px solid #dfe6ef;
        font-size: 13px;
        color: #33475b;
    }
    #ctr_table td {
        padding: 11px 14px;
        font-size: 13px;
        vertical-align: middle;
    }
    #ctr_table td.num, #ctr_table th.num { text-align: right; }
    #ctr_table tbody tr:hover { background: #f9fbfe; }

    /* ---- filter row under the headings ---- */
    #ctr_table tr.ctr-filters th {
        padding: 8px 10px;
        background: #fff;
        border-bottom: 1px solid #e4e9f0;
    }
    #ctr_table tr.ctr-filters input,
    #ctr_table tr.ctr-filters select {
        width: 100%;
        min-width: 80px;
        padding: 5px 8px;
        border: 1px solid #cfd9e6;
        border-radius: 3px;
        font-size: 12px;
        font-weight: normal;
    }
    #ctr_table tr.ctr-filters .ctr-nofilter { color: #c3ccd8; font-size: 11px; font-weight: normal; }

    .ctr-pagination { padding-top: 16px; }
</style>

<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left">
      <div class="page-title">Cash Tenant Report</div>
    </div>
    {{ Breadcrumbs::render('showCashTenantReport') }}
  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box">
      <div class="card-body">

        <div class="ctr-head">
          <div class="ctr-head-left">
            <p class="ctr-caption">
              Active tenant contracts with no post dated cheque on record, i.e. tenants paying by cash.
              <span class="ctr-count">{{ $total }} contract{{ $total == 1 ? '' : 's' }}</span>
            </p>
          </div>
          <div class="ctr-head-right">
            {{-- request()->query() carries the current column filters, so an
                 export always matches what is on screen. --}}
            <a href="{{ route('cashTenantReportDownload', array_merge(request()->query(), ['download_type' => 'excel'])) }}"
               class="btn btn-success btn-sm" title="Download these {{ $total }} rows as Excel">
              <i class="fa fa-file-excel-o"></i> Excel
            </a>
            <a href="{{ route('cashTenantReportDownload', array_merge(request()->query(), ['download_type' => 'pdf'])) }}"
               class="btn btn-danger btn-sm" title="Download these {{ $total }} rows as PDF">
              <i class="fa fa-file-pdf-o"></i> PDF
            </a>
          </div>
        </div>

        {{-- Filters submit as plain GET, so the pagination links and the export
             buttons above inherit them without any extra wiring. --}}
        <form method="GET" action="{{ route('showCashTenantReport') }}" id="ctr_filter_form">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="ctr_table">
            <thead>
              <tr>
                <th width="4%">Sl No</th>
                <th>Contract No</th>
                <th>Tenant</th>
                <th>Building</th>
                <th>Unit No</th>
                <th>Unit Type</th>
                <th width="9%">Start</th>
                <th width="9%">End</th>
                <th class="num" width="8%">Rent</th>
                <th width="9%">Payment Term</th>
                <th>Mobile No</th>
              </tr>
              <tr class="ctr-filters">
                <th></th>
                <th><input type="text" name="f_contract_no" value="{{ isset($filters['f_contract_no']) ? $filters['f_contract_no'] : '' }}" placeholder="Contract"></th>
                <th><input type="text" name="f_tenant" value="{{ isset($filters['f_tenant']) ? $filters['f_tenant'] : '' }}" placeholder="Tenant"></th>
                <th><input type="text" name="f_building" value="{{ isset($filters['f_building']) ? $filters['f_building'] : '' }}" placeholder="Building"></th>
                <th><input type="text" name="f_unit" value="{{ isset($filters['f_unit']) ? $filters['f_unit'] : '' }}" placeholder="Unit"></th>
                <th>
                  <select name="f_unit_type">
                    <option value="">All</option>
                    @foreach($unitTypes as $ut)
                    <option value="{{ $ut->id }}" {{ (string) (isset($filters['f_unit_type']) ? $filters['f_unit_type'] : '') === (string) $ut->id ? 'selected' : '' }}>{{ $ut->unit_types_name }}</option>
                    @endforeach
                  </select>
                </th>
                <th><span class="ctr-nofilter">&mdash;</span></th>
                <th><span class="ctr-nofilter">&mdash;</span></th>
                <th><span class="ctr-nofilter">&mdash;</span></th>
                <th>
                  <select name="f_payment_term">
                    <option value="">All</option>
                    @foreach($paymentTerms as $value => $label)
                    <option value="{{ $value }}" {{ (string) (isset($filters['f_payment_term']) ? $filters['f_payment_term'] : '') === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </th>
                <th><input type="text" name="f_mobile" value="{{ isset($filters['f_mobile']) ? $filters['f_mobile'] : '' }}" placeholder="Mobile"></th>
              </tr>
            </thead>
            <tbody id="ctr_body">
              @include('backoffice::Reports.cash_tenant_report_ajax')
            </tbody>
          </table>
        </div>
        </form>

        <div class="ctr-pagination" id="ctr_pagination">
          {{ $rows->links() }}
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
$(function () {
    var form = document.getElementById('ctr_filter_form');
    var timer = null;

    // Dropdowns apply at once; typing settles first so one keystroke is not
    // one request.
    $('#ctr_filter_form select').on('change', function () { form.submit(); });

    $('#ctr_filter_form input[type=text]').on('keyup', function (e) {
        if (e.key === 'Enter') { clearTimeout(timer); form.submit(); return; }
        clearTimeout(timer);
        timer = setTimeout(function () { form.submit(); }, 600);
    });
});
</script>
@endsection
