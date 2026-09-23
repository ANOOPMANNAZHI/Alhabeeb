@extends('layouts.plms-app')

@section('content')
@include('backoffice::Routine._bank_sheet_styles')
<style>
  .bsr .bsr-day h4 { font-size:16px; }
  .bsr .bsr-filter .form-group { margin:0; }
  .bsr .bsr-filter label { font-size:12px; color:#6b7280; margin-bottom:2px; display:block; }
</style>
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">PDC Bulk Posting — Bank Sheet Posting History</div>
    </div>
    {{ Breadcrumbs::render('pdcBankSheetUploads') }}
  </div>
</div>

<div class="bsr">

<div class="card card-box bsr-filter">
  <div class="card-body">
    <form method="get" action="{{ route('pdcBankSheetUploads') }}" class="row" autocomplete="off">
      <div class="col-md-3 col-sm-6">
        <div class="form-group">
          <label for="bsr-from">Posted from</label>
          <input id="bsr-from" type="date" name="from" class="form-control" value="{{ $from }}">
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="form-group">
          <label for="bsr-to">Posted to</label>
          <input id="bsr-to" type="date" name="to" class="form-control" value="{{ $to }}">
        </div>
      </div>
      <div class="col-md-6 col-sm-12">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary">Show</button>
        <a href="{{ route('pdcBankSheetUploads') }}" class="btn btn-default">Reset</a>
        <a href="{{ route('pdcPosting') }}" class="btn btn-outline-secondary">Back to PDC Posting</a>
      </div>
    </form>
  </div>
</div>

@forelse($days as $d)
@php $dayLabel = \Carbon\Carbon::parse($d->day)->format('d/m/Y (l)'); @endphp
<div class="card card-box bsr-day">
  <div class="card-body">
    <div class="bsr-head">
      <h4><i class="fa fa-calendar" aria-hidden="true"></i> {{ $dayLabel }}</h4>
      <span class="bsr-pill bsr-pill-off">{{ $d->uploads }} upload(s)</span>
      <span class="bsr-pill bsr-pill-ok"><i class="fa fa-check-circle" aria-hidden="true"></i>{{ (int) $d->cheques }} cheque(s) posted</span>
      <span class="bsr-pill bsr-pill-ok"><i class="fa fa-file-text-o" aria-hidden="true"></i>{{ $totalsByDay[$d->day]['receipts'] }} receipt(s) · {{ number_format($totalsByDay[$d->day]['amount'], 3) }}</span>
      <span class="bsr-right">
        <a href="{{ route('pdcBankSheetDay', $d->day) }}" class="btn btn-primary btn-sm">View day's receipts</a>
      </span>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered table-hover" style="margin:0">
        <thead>
          <tr>
            <th width="6%">Upload</th>
            <th width="8%">Time</th>
            <th>Posted By</th>
            <th>Sheet</th>
            <th>Deposit Bank</th>
            <th>Deposit Date</th>
            <th class="num">Cheques Posted</th>
            <th class="num">Receipts</th>
            <th class="num">Amount</th>
          </tr>
        </thead>
        <tbody>
          @foreach($uploadsByDay[$d->day] as $u)
          <tr>
            <td><a href="{{ route('pdcBankSheetResult', $u->id) }}">#{{ $u->id }}</a></td>
            <td>{{ \Carbon\Carbon::parse($u->created_at)->format('H:i') }}</td>
            <td>{{ $u->username ?: '-' }}</td>
            <td>{{ $u->original_name }}</td>
            <td>{{ $u->bank_name ? $u->bank_name . ' - ' . $u->bank_code : '-' }}</td>
            <td>{{ $u->pdc_deposit_date ? \Carbon\Carbon::parse($u->pdc_deposit_date)->format('d/m/Y') : '-' }}</td>
            <td class="num">{{ $u->rows_posted }}</td>
            <td class="num">{{ $u->receipts }}</td>
            <td class="num">{{ number_format($u->amount, 3) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@empty
<div class="card card-box">
  <div class="card-body" style="text-align:center">
    No bank sheet postings{{ ($from || $to) ? ' in this date range' : ' yet' }}.
  </div>
</div>
@endforelse

{{ $days->links() }}

</div>
@endsection
