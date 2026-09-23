@extends('layouts.plms-app')

@section('content')
@include('backoffice::Routine._bank_sheet_styles')
@php
  $uploadNames = [];
  foreach ($uploads as $u) { $uploadNames[$u->id] = $u->original_name; }
  $dayLabel = \Carbon\Carbon::parse($date)->format('d/m/Y');
@endphp
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">PDC Bulk Posting — Bank Sheet Postings on {{ $dayLabel }}</div>
    </div>
    {{ Breadcrumbs::render('pdcBankSheetDay', $date) }}
  </div>
</div>

<div class="bsr">

<div class="card card-box">
  <div class="card-body">
    <div class="bsr-head">
      <h4>Uploads posted on {{ $dayLabel }}</h4>
      <span class="bsr-right">
        <a href="{{ route('pdcBankSheetUploads') }}" class="btn btn-outline-secondary btn-sm">Posting history</a>
      </span>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th width="6%">Upload</th>
            <th>Time</th>
            <th>Posted By</th>
            <th>Sheet</th>
            <th>Deposit Bank</th>
            <th>Deposit Date</th>
            <th class="num">Cheques Posted</th>
          </tr>
        </thead>
        <tbody>
          @forelse($uploads as $u)
          <tr>
            <td><a href="{{ route('pdcBankSheetResult', $u->id) }}">#{{ $u->id }}</a></td>
            <td>{{ \Carbon\Carbon::parse($u->created_at)->format('H:i') }}</td>
            <td>{{ $u->username ?: '-' }}</td>
            <td>{{ $u->original_name }}</td>
            <td>{{ $u->bank_name ? $u->bank_name . ' - ' . $u->bank_code : '-' }}</td>
            <td>{{ $u->pdc_deposit_date ? \Carbon\Carbon::parse($u->pdc_deposit_date)->format('d/m/Y') : '-' }}</td>
            <td class="num">{{ $u->rows_posted }}</td>
          </tr>
          @empty
          <tr><td colspan="7" align="center">No bank sheet was posted on {{ $dayLabel }}.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@if(count($uploads) > 0)
@include('backoffice::Routine._bank_sheet_receipts', ['receipts' => $receipts, 'notPosted' => $notPosted, 'uploadNames' => $uploadNames])
@endif

</div>
@endsection
