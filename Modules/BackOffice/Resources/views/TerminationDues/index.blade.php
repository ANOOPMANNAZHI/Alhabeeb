@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
  /* Tokens (8px grid, neutral surfaces, one accent for the active tab) */
  .tdue { --tdue-ink:#0f172a; --tdue-muted:#475569; --tdue-line:#e2e8f0; --tdue-fill:#f1f5f9; --tdue-primary:#2f4fd6; --tdue-accent:#FF9800; --tdue-danger:#dc2626; --tdue-success:#16a34a; --tdue-warn:#d97706; }
  .tdue .tdue-tabs { display:flex; flex-wrap:wrap; gap:8px; margin:0 0 16px; padding:0; list-style:none; }
  .tdue .tdue-tabs a { display:inline-flex; align-items:center; gap:8px; min-height:40px; padding:0 16px; border:1px solid var(--tdue-line); border-radius:8px; color:var(--tdue-ink); text-decoration:none; }
  .tdue .tdue-tabs a:hover, .tdue .tdue-tabs a:focus-visible { background:#fff3e0; color:#e65100; outline:2px solid var(--tdue-accent); outline-offset:2px; }
  .tdue .tdue-tabs a.active { background:var(--tdue-accent); border-color:var(--tdue-accent); color:#fff; }
  .tdue .tdue-count { min-width:24px; padding:2px 8px; border-radius:999px; background:var(--tdue-fill); color:var(--tdue-muted); font-size:12px; font-weight:600; text-align:center; }
  .tdue .tdue-tabs a.active .tdue-count { background:#fff; color:#e65100; }
  .tdue .tdue-filters { display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end; margin-bottom:24px; }
  .tdue .tdue-filters label { display:block; font-size:14px; font-weight:500; margin-bottom:8px; color:var(--tdue-ink); }
  .tdue .tdue-filters .form-control { height:40px; min-width:200px; }
  .tdue table.product-overview th { font-size:12px; font-weight:500; text-transform:uppercase; letter-spacing:.04em; color:var(--tdue-muted); white-space:nowrap; }
  .tdue table.product-overview td { padding:12px 16px; vertical-align:middle; }
  .tdue td.num, .tdue th.num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
  .tdue td.num.bal { font-weight:600; }
  .tdue .tdue-status { display:inline-block; min-width:80px; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.03em; text-align:center; }
  .tdue .tdue-status-open { background:#fee2e2; color:#991b1b; }
  .tdue .tdue-status-partial { background:#fef3c7; color:#92400e; }
  .tdue .tdue-status-settled { background:#dcfce7; color:#166534; }
  .tdue .tdue-status-written_off { background:var(--tdue-fill); color:var(--tdue-muted); }
  .tdue .tdue-sub { display:block; font-size:12px; color:var(--tdue-muted); }
  .tdue .tdue-empty { padding:32px 16px; text-align:center; color:var(--tdue-muted); }
  .tdue .tdue-overdue { color:var(--tdue-danger); }
  @media (max-width:600px){ .tdue .tdue-filters .form-control{ min-width:100%; } }
</style>
@endsection

@section('content')
@php $teamLabels = ['backoffice' => 'Back Office', 'maintenance' => 'Maintenance', 'all' => 'All teams']; @endphp
<div class="row page-titles">
  <div class="col-md-6 align-self-center"><h3 class="text-themecolor">Termination Dues</h3></div>
  <div class="col-md-6 align-self-center text-right">{{ Breadcrumbs::render('termination-dues.index') }}</div>
</div>

<div class="card tdue">
  <div class="card-body">
    <ul class="tdue-tabs" role="tablist">
      @foreach(array_merge($teams, count($teams) > 1 ? ['all'] : []) as $t)
        <li><a href="{{ route('termination-dues.index', array_merge(request()->except('page'), ['team' => $t])) }}" class="{{ $tab === $t ? 'active' : '' }}" @if($tab === $t) aria-current="page" @endif>
          {{ $teamLabels[$t] }} <span class="tdue-count">{{ $counts[$t] }}</span></a></li>
      @endforeach
    </ul>

    <form method="get" class="tdue-filters" action="{{ route('termination-dues.index') }}">
      <input type="hidden" name="team" value="{{ $tab }}">
      <div>
        <label for="tdue-status">Show</label>
        <select id="tdue-status" name="status" class="form-control">
          @foreach(['outstanding' => 'With balance', 'open' => 'Open (nothing paid)', 'partial' => 'Partially paid', 'settled' => 'Settled', 'written_off' => 'Written off', 'all' => 'Everything'] as $k => $label)
            <option value="{{ $k }}" {{ $status === $k ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="tdue-q">Contract, tenant or mobile</label>
        <input id="tdue-q" type="search" name="q" class="form-control" value="{{ $q }}" placeholder="TAG25… or name">
      </div>
      <div><button type="submit" class="btn btn-primary" style="height:40px">Apply filters</button></div>
    </form>

    <div class="table-responsive">
      <table class="table product-overview">
        <thead><tr>
          <th>Contract</th><th>Tenant</th><th>Building / unit</th><th>Terminated</th>
          <th class="num">Owed</th><th class="num">Settled</th><th class="num">Balance{{ $tab !== 'all' ? ' (' . $teamLabels[$tab] . ')' : '' }}</th>
          <th>Status</th><th>Last follow-up</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($dues as $d)
          @php
            $c = $d->tenantContract;
            $days = $d->termination_date ? $d->termination_date->diffInDays(now()) : null;
            $promiseOverdue = $d->next_promise_date && $d->next_promise_date->isPast() && $d->{$balanceColumn} > 0;
          @endphp
          <tr>
            <td><a href="{{ route('termination-dues.show', $d->id) }}">{{ optional($c)->tenant_contract_no ?: '#' . $d->tenant_contract_id }}</a></td>
            <td>{{ optional(optional($c)->tenant)->tenant_name }}<span class="tdue-sub">{{ optional(optional($c)->tenant)->tenant_contact_no }}</span></td>
            <td>{{ optional(optional($c)->building)->building_name }}<span class="tdue-sub">{{ optional(optional($c)->unit)->unit_code }}</span></td>
            <td>{{ $d->termination_date ? $d->termination_date->format('d/m/Y') : '—' }}@if($days !== null)<span class="tdue-sub">{{ $days }} days ago</span>@endif</td>
            <td class="num">{{ numberFormat($d->total_owed) }}</td>
            <td class="num">{{ numberFormat($d->total_settled) }}</td>
            <td class="num bal">{{ numberFormat($d->{$balanceColumn}) }}</td>
            <td><span class="tdue-status tdue-status-{{ $d->status }}">{{ str_replace('_', ' ', $d->status) }}</span></td>
            <td>{{ $d->last_followup_at ? $d->last_followup_at->format('d/m/Y') : '—' }}
              @if($d->next_promise_date)<span class="tdue-sub {{ $promiseOverdue ? 'tdue-overdue' : '' }}">promised {{ $d->next_promise_date->format('d/m/Y') }}</span>@endif</td>
            <td><a class="btn btn-sm btn-outline-secondary" href="{{ route('termination-dues.show', $d->id) }}">Open</a></td>
          </tr>
        @empty
          <tr><td colspan="10" class="tdue-empty">No termination dues match these filters.<br><small>Dues appear here automatically when a termination completes with an unpaid balance.</small></td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    {{ $dues->links() }}
  </div>
</div>
@endsection
