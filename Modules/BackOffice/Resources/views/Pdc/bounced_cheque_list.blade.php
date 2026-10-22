@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
<style>
  .lg .bc-sub2 { display: block; color: var(--lg-muted); font-size: 12px; margin-top: 2px; white-space: nowrap; }
  .lg .bc-sub2 a { color: inherit; text-decoration: none; }
  .lg .bc-sub2 a:hover { color: var(--lg-primary); text-decoration: underline; }
  .lg .bc-tenant { min-width: 190px; }
  .lg .bc-wrap { min-width: 150px; }
  .lg .bc-reason { min-width: 130px; max-width: 200px; white-space: normal; }
  .lg .lg-table tbody tr[data-href] { cursor: pointer; }
</style>
@endsection

@section('content')
@php
  $fmtDate = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : ''; };
  $ageOf = function ($p) use ($today) { return $p->pdc_check_date ? \Carbon\Carbon::parse($p->pdc_check_date)->diffInDays($today, false) : null; };
  $total = $pdc->sum(function ($p) { return (float) $p->pdc_amt; });
  $contracts = $pdc->pluck('tenant_contract_id')->unique()->count();
  $over30 = $pdc->filter(function ($p) use ($ageOf) { return $ageOf($p) > 30; })->count();
  $cols = $showAre ? 10 : 9;
  $canOpenPdc = \Auth::user()->can('pdc_generation');
@endphp
<div class="lg">
  <div class="lg-crumbs">{{ Breadcrumbs::render('tenantPdcBounce') }}</div>
  <header class="lg-page-head">
    <div>
      <h1 class="lg-page-head__title">{{ $title }}</h1>
      <div class="lg-page-head__sub">{{ $subtitle }} <span class="sep">/</span>
        <span class="tnum">@if($isFiltered){{ $pdc->count() }} of {{ $all->count() }}@else{{ $all->count() }}@endif cheques</span></div>
    </div>
  </header>

  <div class="lg-kpis lg-kpis--card" aria-label="Summary">
    <div class="lg-kpi"><div class="lg-kpi__label">Cheques</div><div class="lg-kpi__value">{{ $pdc->count() }}</div></div>
    <div class="lg-kpi {{ $total > 0 ? 'lg-kpi--bad' : '' }}"><div class="lg-kpi__label">Amount (OMR)</div><div class="lg-kpi__value">{{ numberFormat($total) }}</div></div>
    <div class="lg-kpi"><div class="lg-kpi__label">Contracts</div><div class="lg-kpi__value">{{ $contracts }}</div></div>
    <div class="lg-kpi {{ $over30 ? 'lg-kpi--bad' : '' }}"><div class="lg-kpi__label">Over 30 days</div><div class="lg-kpi__value">{{ $over30 }}</div>
      <div class="lg-kpi__hint">since the cheque date</div></div>
  </div>

  <section class="lg-card" aria-label="{{ $title }}">
    <form method="GET" action="{{ url()->current() }}" class="lg-filters" aria-label="Filter bounced cheques">
      <div class="lg-field lg-field--wide"><label for="bc_search" class="lg-label">Search</label>
        <input type="search" id="bc_search" name="search" class="lg-input" value="{{ $request->search }}" placeholder="Tenant, mobile, unit, contract or cheque no"></div>
      <div class="lg-field"><label for="bc_building" class="lg-label">Building</label>
        <select id="bc_building" name="building" class="lg-select"><option value="">All</option>
          @foreach($buildings as $b)<option value="{{ $b->id }}" @if((int) $request->building === (int) $b->id) selected @endif>{{ $b->building_name }}</option>@endforeach
        </select></div>
      @if($showAre)
      <div class="lg-field"><label for="bc_are" class="lg-label">ARE</label>
        <select id="bc_are" name="are" class="lg-select"><option value="">All</option>
          @foreach($ares as $a)<option value="{{ $a }}" @if($request->are === $a) selected @endif>{{ $a }}</option>@endforeach
        </select></div>
      @endif
      <div class="lg-field"><label for="bc_age" class="lg-label">Age</label>
        <select id="bc_age" name="age" class="lg-select"><option value="">Any</option>
          @foreach(array_keys($ages) as $k)<option value="{{ $k }}" @if($request->age === $k) selected @endif>{{ $k }} days</option>@endforeach
        </select></div>
      <div class="lg-filters__actions">
        @if($isFiltered)<a href="{{ url()->current() }}" class="lg-btn lg-btn--ghost">Reset</a>@endif
        <button type="submit" class="lg-btn lg-btn--primary">Search</button>
      </div>
    </form>

    <div class="lg-table-wrap">
      <table class="lg-table">
        <thead>
          <tr>
            <th scope="col">Tenant</th>
            <th scope="col">Building / unit</th>
            <th scope="col">Contract</th>
            <th scope="col">Cheque</th>
            <th scope="col">Cheque date</th>
            <th scope="col">Age</th>
            <th scope="col">Reason</th>
            <th scope="col" class="num">Amount</th>
            <th scope="col" class="num">Rent pm</th>
            @if($showAre)<th scope="col">ARE</th>@endif
          </tr>
        </thead>
        <tbody>
          @forelse($pdc as $p)
          @php
            $c = $p->tenantContractInfo;
            $t = $c->tenant;
            $mobile = trim((string) ($t->tenant_contact_no ?? '')) ?: trim((string) ($t->gsm_no ?? ''));
            $age = $ageOf($p);
            $tone = $age === null || $age < 0 ? 'neutral' : ($age > 30 ? 'danger' : ($age > 7 ? 'warning' : 'neutral'));
            $pdcUrl = $canOpenPdc ? route('pdc.edit', $c->id) : null;
          @endphp
          <tr @if($pdcUrl) data-href="{{ $pdcUrl }}" title="Open the PDC screen of {{ $c->tenant_contract_no }}" @endif>
            <td class="strong bc-tenant">{{ $t->tenant_name ?? '' }}
              <span class="bc-sub2">@if($mobile)<a href="tel:{{ preg_replace('/\s+/', '', $mobile) }}">{{ $mobile }}</a>@else N/A @endif</span></td>
            <td class="bc-wrap">{{ $c->building->building_name ?? '' }}<span class="bc-sub2">Unit {{ $c->Unit->unit_no ?? '' }}</span></td>
            <td class="tnum">@if($pdcUrl)<a class="lg-row-link" href="{{ $pdcUrl }}">{{ $c->tenant_contract_no }}</a>@else{{ $c->tenant_contract_no }}@endif
              <span class="bc-sub2">{{ $fmtDate($c->tenant_contract_effective_date) }} – {{ $fmtDate($c->tenant_contract_valid_to_date) }}</span></td>
            <td class="tnum">{{ $p->pdc_check_no }}<span class="bc-sub2">{{ $p->bankInfo->bank_name ?? '' }}</span></td>
            <td class="tnum">{{ $fmtDate($p->pdc_check_date) }}
              @if($p->pdc_cancel_date)<span class="bc-sub2">Bounced {{ $fmtDate($p->pdc_cancel_date) }}</span>@endif</td>
            <td><span class="lg-badge lg-badge--{{ $tone }}">@if($age === null)–@elseif($age < 0)Not due @else{{ $age }} {{ $age == 1 ? 'day' : 'days' }}@endif</span></td>
            <td class="bc-reason">{{ $p->pdc_cancel_reason_name }}</td>
            <td class="num strong">{{ numberFormat($p->pdc_amt) }}@if((int) $p->pdc_type !== 1)<span class="bc-sub2">{{ $p->pdc_type_name }}</span>@endif</td>
            <td class="num">{{ numberFormat($c->tenant_contract_rent) }}</td>
            @if($showAre)<td class="bc-wrap">{{ implode(', ', $areNames[$c->building_id] ?? []) }}</td>@endif
          </tr>
          @empty
          <tr><td colspan="{{ $cols }}"><div class="lg-empty">
            @if($isFiltered)
              <div class="lg-empty__title">No cheques match these filters</div>
              <a href="{{ url()->current() }}">Reset the filters</a> to see all {{ $all->count() }}.
            @else
              <div class="lg-empty__title">No bounced cheques to follow up</div>
              Every bounced cheque on your buildings is settled, receipted or redeposited.
            @endif
          </div></td></tr>
          @endforelse
        </tbody>
        @if($pdc->count())
        <tfoot>
          <tr><td colspan="7">Total, {{ $pdc->count() }} {{ $pdc->count() == 1 ? 'cheque' : 'cheques' }}</td>
            <td class="num">{{ numberFormat($total) }}</td><td colspan="{{ $cols - 8 }}"></td></tr>
        </tfoot>
        @endif
      </table>
    </div>
  </section>
</div>
@endsection
@section('scripts')
<script>
  // whole row opens the PDC screen; links inside the row (contract no, phone) keep their own action
  $(document).on('click', '.lg-table tbody tr[data-href]', function (e) {
    if ($(e.target).closest('a, button, input, select').length || window.getSelection().toString()) return;
    if (e.ctrlKey || e.metaKey) { window.open($(this).data('href'), '_blank'); return; }
    window.location.href = $(this).data('href');
  });
</script>
@endsection
