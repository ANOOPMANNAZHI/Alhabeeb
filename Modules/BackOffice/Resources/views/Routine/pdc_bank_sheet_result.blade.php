@extends('layouts.plms-app')

@section('content')
@include('backoffice::Routine._bank_sheet_styles')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">PDC Bulk Posting — Bank Sheet Posting Result</div>
    </div>
    {{ Breadcrumbs::render('pdcBankSheetResult', $upload) }}
  </div>
</div>

<div class="bsr">

<div class="card card-box">
  <div class="card-body">
    <div class="row">
      <div class="col-md-4 col-sm-12">
        <dl>
          <dt>Sheet</dt>
          <dd><i class="fa fa-file-excel-o" aria-hidden="true"></i> {{ $upload->original_name }}</dd>
          <dt>Posted by</dt>
          <dd>{{ $upload->username ?: '-' }} <span class="bsr-sub" style="display:inline">on {{ \Carbon\Carbon::parse($upload->created_at)->format('d/m/Y H:i') }}</span></dd>
        </dl>
      </div>
      <div class="col-md-4 col-sm-6">
        <dl>
          <dt>Deposit bank</dt>
          <dd>{{ $upload->bank_name ? $upload->bank_name . ' - ' . $upload->bank_code : '-' }}</dd>
          <dt>Deposit date</dt>
          <dd>{{ $upload->pdc_deposit_date ? \Carbon\Carbon::parse($upload->pdc_deposit_date)->format('d/m/Y') : '-' }}</dd>
        </dl>
      </div>
      <div class="col-md-4 col-sm-6">
        <dl>
          <dt>Outcome</dt>
          <dd>
            <span class="bsr-pill bsr-pill-ok"><i class="fa fa-check-circle" aria-hidden="true"></i>{{ $upload->rows_posted }} cheque(s) posted</span>
            <span class="bsr-pill bsr-pill-ok"><i class="fa fa-file-text-o" aria-hidden="true"></i>{{ count($receipts) }} receipt(s)</span>
            @if(count($notPosted) > 0)
            <span class="bsr-pill bsr-pill-bad"><i class="fa fa-ban" aria-hidden="true"></i>{{ count($notPosted) }} not posted</span>
            @endif
          </dd>
        </dl>
      </div>
    </div>

    <div class="bsr-toolbar">
      <nav class="bsr-toolbar-nav" aria-label="Bank sheet posting">
        <a href="{{ route('pdcPosting') }}" class="bsr-btn"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to PDC Posting</a>
        <a href="{{ route('pdcBankSheetDay', substr($upload->created_at, 0, 10)) }}" class="bsr-btn"><i class="fa fa-calendar" aria-hidden="true"></i> All postings that day</a>
        <a href="{{ route('pdcBankSheetUploads') }}" class="bsr-btn"><i class="fa fa-history" aria-hidden="true"></i> Posting history</a>
      </nav>
      <a href="{{ route('pdcBankSheetResultExcel', $upload->id) }}" class="bsr-btn bsr-btn-primary"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Download Excel</a>
    </div>
  </div>
</div>

@include('backoffice::Routine._bank_sheet_receipts', ['receipts' => $receipts, 'notPosted' => $notPosted])

</div>
@endsection
