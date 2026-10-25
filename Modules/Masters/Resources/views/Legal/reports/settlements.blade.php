@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

@section('content')
@php $LS = \Modules\Masters\Services\LegalSettlement::class; $f = $filters; @endphp
<div class="lg">
  <div class="lg-crumbs">{{ Breadcrumbs::render('legal.reports.settlements') }}</div>
  <header class="lg-page-head">
    <div>
      <h1 class="lg-page-head__title">Legal settlements</h1>
      <div class="lg-page-head__sub">What was agreed, what came in, what is late</div>
    </div>
  </header>

  <section class="lg-card" aria-label="Report filters">
    <form method="GET" action="{{ route('legal.reports.settlements') }}" class="lg-filters" style="border-bottom:0;border-radius:var(--lg-radius);">
      <div class="lg-field"><label for="ls_status" class="lg-label">Status</label><select id="ls_status" name="status" class="lg-select"><option value="">All</option>
        @foreach($LS::STATUS as $k => $v)<option value="{{ $k }}" @if(isset($f['status']) && $f['status'] !== '' && (string) $f['status'] === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="lg-field"><label for="ls_type" class="lg-label">Type</label><select id="ls_type" name="type" class="lg-select"><option value="">All</option>
        @foreach($LS::TYPES as $k => $v)<option value="{{ $k }}" @if((string) ($f['type'] ?? '') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="lg-field"><label for="ls_lawyer" class="lg-label">Lawyer</label><select id="ls_lawyer" name="lawyer" class="lg-select"><option value="">All</option>
        @foreach($lawyers as $id => $nm)<option value="{{ $id }}" @if((string) ($f['lawyer'] ?? '') === (string) $id) selected @endif>{{ $nm }}</option>@endforeach</select></div>
      <div class="lg-field"><label for="ls_from" class="lg-label">Date from</label><input type="date" id="ls_from" name="from" class="lg-input" value="{{ $f['from'] ?? '' }}"></div>
      <div class="lg-field"><label for="ls_to" class="lg-label">To</label><input type="date" id="ls_to" name="to" class="lg-input" value="{{ $f['to'] ?? '' }}"></div>
      <div class="lg-filters__actions">
        <a href="{{ route('legal.reports.settlements') }}" class="lg-btn lg-btn--ghost">Reset</a>
        <button type="submit" name="export" value="excel" class="lg-btn lg-btn--secondary"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel</button>
        <button type="submit" name="export" value="pdf" class="lg-btn lg-btn--secondary"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</button>
        <button type="submit" class="lg-btn lg-btn--primary">Show</button>
      </div>
    </form>
  </section>

  <div class="lg-kpis lg-kpis--card">
    <div class="lg-kpi"><div class="lg-kpi__label">Settlements</div><div class="lg-kpi__value">{{ $totals['count'] }}</div></div>
    <div class="lg-kpi"><div class="lg-kpi__label">Total to collect</div><div class="lg-kpi__value">{{ numberFormat($totals['settled_total']) }}</div></div>
    <div class="lg-kpi lg-kpi--good"><div class="lg-kpi__label">Collected</div><div class="lg-kpi__value">{{ numberFormat($totals['collected']) }}</div></div>
    <div class="lg-kpi"><div class="lg-kpi__label">Cheques held</div><div class="lg-kpi__value">{{ numberFormat($totals['held']) }}</div></div>
    <div class="lg-kpi"><div class="lg-kpi__label">Balance</div><div class="lg-kpi__value">{{ numberFormat($totals['balance']) }}</div></div>
    <div class="lg-kpi {{ $totals['overdue'] > 0 ? 'lg-kpi--bad' : '' }}"><div class="lg-kpi__label">Overdue</div><div class="lg-kpi__value">{{ numberFormat($totals['overdue']) }}</div></div>
    <div class="lg-kpi"><div class="lg-kpi__label">Live waivers</div><div class="lg-kpi__value">{{ numberFormat($totals['waiver_amount']) }}</div></div>
  </div>

  <section class="lg-card" aria-label="Settlements">
    <div class="lg-table-wrap">
      @include('masters::Legal.reports.settlements_tables')
    </div>
  </section>
</div>
@endsection
