@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
{{-- Styles live here, not in @section('css'): the plms-app layout only yields 'content' and 'scripts'. --}}
<style>
    #ctr_table th { white-space: nowrap; }
    #ctr_table td.num, #ctr_table th.num { text-align: right; }
    .ctr-loading { opacity: .5; }
    .ctr-actions { float: right; }
    .ctr-actions .btn { margin-left: 6px; }
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

        <div class="ctr-actions">
          {{-- Downloads cover every matching contract, not just this page. --}}
          <a href="{{ route('cashTenantReportDownload', ['download_type' => 'excel']) }}"
             class="btn btn-success btn-sm" title="Download all {{ $total }} rows as Excel">
            <i class="fa fa-file-excel-o"></i> Excel
          </a>
          <a href="{{ route('cashTenantReportDownload', ['download_type' => 'pdf']) }}"
             class="btn btn-danger btn-sm" title="Download all {{ $total }} rows as PDF">
            <i class="fa fa-file-pdf-o"></i> PDF
          </a>
        </div>

        <p class="text-muted" style="margin-bottom:8px">
          Active tenant contracts with no post dated cheque on record, i.e. tenants paying by cash.
          <b>{{ $total }}</b> contract(s).
        </p>

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
            </thead>
            <tbody id="ctr_body">
              @include('backoffice::Reports.cash_tenant_report_ajax')
            </tbody>
          </table>
        </div>

        <div id="ctr_pagination">
          {{ $rows->links() }}
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
