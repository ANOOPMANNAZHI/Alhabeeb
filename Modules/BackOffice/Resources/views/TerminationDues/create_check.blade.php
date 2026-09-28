@extends('layouts.plms-app')

@section('css')
<style>
  .tdc { --tdc-ink:#0f172a; --tdc-muted:#475569; --tdc-line:#e2e8f0; --tdc-fill:#f1f5f9; }
  .tdc .tdc-form { display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end; }
  .tdc .tdc-form label { display:block; font-size:14px; font-weight:600; margin-bottom:8px; color:var(--tdc-ink); }
  .tdc .tdc-form input { height:40px; min-width:240px; padding:0 12px; border:1px solid #94a3b8; border-radius:6px; }
  .tdc .tdc-btn { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 16px; border:1px solid #94a3b8; border-radius:6px;
                  background:#fff; color:var(--tdc-ink); font-size:14px; font-weight:600; text-decoration:none; cursor:pointer; }
  .tdc .tdc-btn:hover { background:var(--tdc-fill); text-decoration:none; color:var(--tdc-ink); }
  .tdc .tdc-btn:focus-visible { outline:2px solid #2f4fd6; outline-offset:2px; }
  .tdc .tdc-btn-primary { background:#16a34a; border-color:#16a34a; color:#fff; }
  .tdc .tdc-btn-primary:hover { background:#15803d; border-color:#15803d; color:#fff; }
  .tdc .tdc-hint { font-size:13px; color:var(--tdc-muted); margin:8px 0 0; }
  .tdc .tdc-msg { padding:12px 16px; border-radius:6px; margin:16px 0 0; font-size:14px; }
  .tdc .tdc-msg-info { background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; }
  .tdc .tdc-msg-bad  { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }
  .tdc .tdc-id { display:flex; flex-wrap:wrap; gap:8px 32px; margin:0 0 16px; }
  .tdc .tdc-id div span { display:block; font-size:12px; color:var(--tdc-muted); }
  .tdc .tdc-id div strong { font-size:15px; }
  .tdc table { width:100%; border-collapse:collapse; }
  .tdc th, .tdc td { padding:10px 12px; border-bottom:1px solid var(--tdc-line); font-size:14px; }
  .tdc th { font-size:12px; font-weight:600; color:var(--tdc-muted); text-align:left; background:var(--tdc-fill); }
  .tdc .num { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
  .tdc tr.tdc-total td { font-weight:700; }
  .tdc .tdc-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; justify-content:flex-end; margin-top:16px; }
</style>
@endsection

@section('content')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">Create Dues for a Contract</div></div>
    {{ Breadcrumbs::render('termination-dues.create-check') }}
  </div>
</div>

<div class="tdc">
<div class="card card-box">
  <div class="card-body">
    <form method="GET" action="{{ route('termination-dues.create-check') }}" class="tdc-form">
      <div>
        <label for="contract_no">Contract No.</label>
        <input type="text" id="contract_no" name="contract_no" value="{{ $contractNo }}" placeholder="e.g. TAG2201001" required autofocus>
      </div>
      <button type="submit" class="tdc-btn"><i class="fa fa-search" aria-hidden="true"></i> Check</button>
      <a href="{{ route('termination-dues.index') }}" class="tdc-btn"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to Termination Dues</a>
    </form>
    <p class="tdc-hint">For a terminated contract that has no dues record yet (for example, terminated before Termination Dues existed). Uses the same calculation as the backfill; nothing is saved until you press Create.</p>

    @if($assessment && !$assessment['ok'])
      <div class="tdc-msg tdc-msg-info" role="status">
        {{ $assessment['reason'] }}
        @if($assessment['dues'])
          <a href="{{ route('termination-dues.show', $assessment['dues']->id) }}"><strong>Open the dues record</strong></a>
        @endif
      </div>
    @endif

    @if($preview && $preview['outcome'] === 'error')
      <div class="tdc-msg tdc-msg-bad" role="alert">{{ $preview['message'] }}</div>
    @elseif($preview && in_array($preview['outcome'], ['nothing_owed', 'settled']))
      <div class="tdc-msg tdc-msg-info" role="status">{{ $preview['message'] }}</div>
    @endif
  </div>
</div>

@if($preview && $preview['outcome'] === 'preview')
  @php
    $c = $assessment['contract'];
    $r = $preview['result'];
    $fmt = function ($v) { return number_format((float) $v, 3); };
  @endphp
  <div class="card card-box">
    <div class="card-body">
      <div class="tdc-id">
        <div><span>Contract</span><strong>{{ $c->tenant_contract_no }}</strong></div>
        <div><span>Tenant</span><strong>{{ optional($c->tenant)->tenant_name ?: '-' }}</strong></div>
        <div><span>Building / Unit</span><strong>{{ optional($c->building)->building_name ?: '-' }}{{ $c->Unit ? ' / ' . $c->Unit->unit_no : '' }}</strong></div>
        <div><span>Terminated on</span><strong>{{ $assessment['final']->termination_date ? \Carbon\Carbon::parse($assessment['final']->termination_date)->format('d/m/Y') : '-' }}</strong></div>
      </div>

      <table>
        <thead>
          <tr><th>Item</th><th class="num">Owed</th><th class="num">Deposit</th><th class="num">Receipts</th><th class="num">Waived</th><th class="num">Balance</th></tr>
        </thead>
        <tbody>
          @foreach($r['lines'] as $l)
          <tr>
            <td>{{ $l['description'] }}</td>
            <td class="num">{{ $fmt($l['owed']) }}</td>
            <td class="num">{{ $fmt($l['deposit']) }}</td>
            <td class="num">{{ $fmt($l['receipts']) }}</td>
            <td class="num">{{ $fmt($l['waived']) }}</td>
            <td class="num">{{ $fmt($l['balance']) }}</td>
          </tr>
          @endforeach
          <tr class="tdc-total">
            <td>Total</td>
            <td class="num">{{ $fmt($r['total']['owed']) }}</td>
            <td class="num" colspan="3"></td>
            <td class="num">{{ $fmt($r['total']['balance']) }}</td>
          </tr>
        </tbody>
      </table>

      <form method="POST" action="{{ route('termination-dues.create-for-contract') }}" class="tdc-actions">
        @csrf
        <input type="hidden" name="contract_no" value="{{ $c->tenant_contract_no }}">
        <span class="tdc-hint" style="margin:0">After this, rent receipts on {{ $c->tenant_contract_no }} are allowed up to the open rent.</span>
        <button type="submit" class="tdc-btn tdc-btn-primary"><i class="fa fa-check" aria-hidden="true"></i> Create dues record</button>
      </form>
    </div>
  </div>
@endif
</div>
@endsection
