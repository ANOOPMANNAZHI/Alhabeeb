@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

@section('content')
@php
    // the contract panel works on a case; an old contract is shown through the same panel
    $panel = (object) ['id' => 'tc' . $tenantContract->id, 'tenantContract' => $tenantContract,
                       'building' => $tenantContract->building, 'unit' => $tenantContract->unit];
@endphp
<div class="lg">
  <div class="lg-crumbs">{{ Breadcrumbs::render('legaltenantContractShow', $tenantContract) }}</div>
  <header class="lg-page-head">
    <div>
      <h1 class="lg-page-head__title">{{ $tenantContract->tenant_contract_no }} &middot; {{ $tenantContract->tenant->tenant_name ?? '' }}</h1>
      <div class="lg-page-head__sub"><span>Old contract</span><span class="sep">/</span><span>{{ $tenantContract->building->building_name ?? '' }}</span>
        <span class="sep">/</span><span>Unit {{ $tenantContract->unit->unit_code ?? '' }}</span></div>
    </div>
    <div class="lg-page-head__actions">
      <a class="lg-btn lg-btn--secondary" href="{{ route('legalPdcShow', $tenantContract->id) }}">Cheques</a>
      <a class="lg-btn lg-btn--ghost" href="{{ url()->previous() }}">Back</a>
    </div>
  </header>
  @include('masters::Legal._contract_details', ['legal' => $panel])
  @if($tenantContract->tenant_contract_note)
  <section class="lg-card"><div class="lg-card__head"><h2 class="lg-card__title">Contract note</h2></div>
    <div class="lg-card__body" style="white-space:pre-line;">{{ $tenantContract->tenant_contract_note }}</div></section>
  @endif
  <section class="lg-card" aria-labelledby="ltc_docs">
    <div class="lg-card__head"><h2 class="lg-card__title" id="ltc_docs">Tenant documents</h2></div>
    @if(!empty($tenantContract->tenantDocument) && count($tenantContract->tenantDocument))
      <div class="lg-card__body lg-timeline__files" style="margin:0;">
        @foreach($tenantContract->tenantDocument as $doc)
          <a class="lg-file" href="{{ route('tenantContractDownload', [$doc->id, 'tenantContract']) }}"><i class="fa fa-paperclip" aria-hidden="true"></i> {{ $doc->tenant_documents_name }}</a>
        @endforeach
      </div>
    @else
      <div class="lg-empty">No documents.</div>
    @endif
  </section>
</div>
@endsection
