@extends('layouts.plms-app')

@section('content')
@include('backoffice::Routine._bank_sheet_styles')
<style>
  .bsr .bsr-filter .form-group { margin:0 0 10px; }
  .bsr .bsr-filter label { font-size:12px; color:#6b7280; margin-bottom:2px; display:block; }
  .bsr .bsr-status-pills a { text-decoration:none; }
  .bsr .bsr-status-pills a.bsr-on { outline:2px solid #1F4E79; outline-offset:1px; }
</style>
@php
  $pillClass = [
    'not_found' => 'bsr-pill-off', 'unknown_bank' => 'bsr-pill-off', 'amount_mismatch' => 'bsr-pill-warn',
    'date_mismatch' => 'bsr-pill-warn', 'skipped' => 'bsr-pill-warn', 'failed' => 'bsr-pill-bad',
  ];
  $label = function ($s) { return \Modules\BackOffice\Services\BankSheetResultRows::rowStatus($s); };
  $fmtDate = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-'; };
  $query = array_filter($filters, function ($v) { return $v !== null && $v !== ''; });
  $allCount = array_sum($statusCounts);
@endphp
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">PDC Bulk Posting — Pending Cheques</div>
    </div>
    {{ Breadcrumbs::render('pdcBankSheetPending') }}
  </div>
</div>

<div class="bsr">

<div class="card card-box bsr-filter">
  <div class="card-body">
    <p class="bsr-sub" style="margin:0 0 12px">
      Cheques from the uploaded bank sheets that are not posted yet: one line per cheque, from its latest upload.
      A cheque leaves this list by itself once it is posted (bank sheet or PDC Posting screen). Cheques rejected by the bank are not shown.
    </p>
    <form method="get" action="{{ route('pdcBankSheetPending') }}" class="row" autocomplete="off">
      <div class="col-md-2 col-sm-6">
        <div class="form-group">
          <label for="bsr-from">Cheque date from</label>
          <input id="bsr-from" type="date" name="from" class="form-control" value="{{ $filters['from'] }}">
        </div>
      </div>
      <div class="col-md-2 col-sm-6">
        <div class="form-group">
          <label for="bsr-to">Cheque date to</label>
          <input id="bsr-to" type="date" name="to" class="form-control" value="{{ $filters['to'] }}">
        </div>
      </div>
      <div class="col-md-2 col-sm-6">
        <div class="form-group">
          <label for="bsr-status">Reason</label>
          <select id="bsr-status" name="status" class="form-control">
            <option value="">All</option>
            @foreach($statuses as $s)
            <option value="{{ $s }}" {{ $filters['status'] === $s ? 'selected' : '' }}>{{ $label($s) }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-md-2 col-sm-6">
        <div class="form-group">
          <label for="bsr-cheque">Cheque no</label>
          <input id="bsr-cheque" type="text" name="cheque" class="form-control" value="{{ $filters['cheque'] }}">
        </div>
      </div>
      <div class="col-md-2 col-sm-6">
        <div class="form-group">
          <label for="bsr-bank">Bank</label>
          <input id="bsr-bank" type="text" name="bank" class="form-control" value="{{ $filters['bank'] }}" placeholder="e.g. Muscat">
        </div>
      </div>
      <div class="col-md-2 col-sm-6">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary">Show</button>
        <a href="{{ route('pdcBankSheetPending') }}" class="btn btn-default">Reset</a>
      </div>
    </form>

    <div class="bsr-toolbar">
      <div class="bsr-status-pills">
        @php $base = array_diff_key($query, ['status' => 1]); @endphp
        <a href="{{ route('pdcBankSheetPending', $base) }}" class="bsr-pill bsr-pill-off {{ $filters['status'] === '' ? 'bsr-on' : '' }}">All: {{ $allCount }}</a>
        @foreach($statusCounts as $s => $n)
          @if($n > 0)
          <a href="{{ route('pdcBankSheetPending', array_merge($base, ['status' => $s])) }}" class="bsr-pill {{ $pillClass[$s] }} {{ $filters['status'] === $s ? 'bsr-on' : '' }}">{{ $label($s) }}: {{ $n }}</a>
          @endif
        @endforeach
      </div>
      <div class="bsr-toolbar-nav">
        <a href="{{ route('pdcBankSheetUploads') }}" class="bsr-btn"><i class="fa fa-arrow-left" aria-hidden="true"></i> Posting history</a>
        <a href="{{ route('pdcBankSheetPendingExcel', $query) }}" class="bsr-btn bsr-btn-primary"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Download Excel</a>
      </div>
    </div>
  </div>
</div>

<div class="card card-box">
  <div class="card-body">
    <div class="bsr-head">
      <h4>{{ $pending->total() }} pending cheque(s)</h4>
      <span class="bsr-pill bsr-pill-off">Total {{ number_format($total, 3) }}</span>
      <span class="bsr-right" style="white-space:nowrap">@include('includes.pagination_info', ['paginator' => $pending])</span>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered table-hover" style="margin:0">
        <thead>
          <tr>
            <th width="4%">#</th>
            <th>Cheque No</th>
            <th>Bank (sheet)</th>
            <th>Cheque Date</th>
            <th class="num">Amount</th>
            <th>Status</th>
            <th>Reason</th>
            <th>Last Upload</th>
            <th class="num">Times Uploaded</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pending as $r)
          <tr>
            <td>{{ $pending->perPage() * ($pending->currentPage() - 1) + $loop->iteration }}</td>
            <td><b>{{ $r->cheque_no }}</b></td>
            <td>{{ $r->bank_name ?: '-' }}</td>
            <td>{{ $fmtDate($r->cheque_date) }}</td>
            <td class="num">{{ number_format($r->amount, 3) }}</td>
            <td><span class="bsr-pill {{ isset($pillClass[$r->status]) ? $pillClass[$r->status] : 'bsr-pill-off' }}">{{ $label($r->status) }}</span></td>
            <td>{{ $r->reason ?: '-' }}</td>
            <td><a href="{{ route('pdcBankSheetResult', $r->upload_id) }}">#{{ $r->upload_id }}</a><span class="bsr-sub">{{ $fmtDate($r->uploaded_at) }}</span></td>
            <td class="num">{{ $r->times }}</td>
          </tr>
          @empty
          <tr><td colspan="9" align="center">{{ count($query) ? 'No pending cheques fit these filters.' : 'No pending cheques — every uploaded cheque is posted.' }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="text-center">{{ $pending->links() }}</div>
  </div>
</div>

</div>
@endsection
