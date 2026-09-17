@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
  .tdue { --tdue-ink:#0f172a; --tdue-muted:#475569; --tdue-line:#e2e8f0; --tdue-fill:#f1f5f9; --tdue-accent:#FF9800; --tdue-danger:#dc2626; --tdue-success:#16a34a; }
  /* Header: tenant, mobile, balance — same shape as the termination screens */
  .tdue .tdue-head { display:flex; flex-wrap:wrap; gap:16px 24px; align-items:flex-start; justify-content:space-between; margin-bottom:16px; }
  .tdue .tdue-head .tdue-title { font-size:22px; font-weight:800; margin:0; line-height:1.3; }
  .tdue .tdue-head .tdue-sub { color:var(--tdue-muted); font-size:14px; margin-top:4px; }
  .tdue .tdue-head .tdue-bal { margin-top:8px; font-size:16px; font-weight:700; color:var(--tdue-danger); font-variant-numeric:tabular-nums; }
  .tdue .tdue-head .tdue-bal.zero { color:var(--tdue-success); }
  .tdue .tdue-head .tdue-bal small { display:block; font-size:12px; font-weight:500; color:var(--tdue-muted); margin-top:2px; }
  /* Identity strip: building / unit / contract */
  .tdue .tdue-id { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:16px; margin-bottom:16px; }
  .tdue .tdue-id-tile { border:1px solid var(--tdue-line); border-radius:8px; padding:16px; background:#fff; min-width:0; }
  .tdue .tdue-id-tile .k { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--tdue-muted); margin-bottom:8px; }
  .tdue .tdue-id-tile .big { display:block; font-size:20px; line-height:1.3; font-weight:800; color:var(--tdue-ink); word-break:break-word; }
  .tdue .tdue-id-tile .meta { display:block; font-size:13px; color:var(--tdue-muted); margin-top:4px; word-break:break-word; }
  .tdue .tdue-id-tile.primary { border-color:var(--tdue-accent); background:#fff7ed; }
  @media (max-width: 900px) { .tdue .tdue-id { grid-template-columns:1fr; } }
  /* Record details as label/value pairs */
  .tdue table.tdue-details { width:100%; margin:0 0 8px; border-collapse:collapse; }
  .tdue table.tdue-details th, .tdue table.tdue-details td { padding:8px 12px; border-bottom:1px solid var(--tdue-line); vertical-align:top; font-size:14px; }
  .tdue table.tdue-details th { width:18%; font-size:12px; font-weight:600; color:var(--tdue-muted); text-align:left; white-space:nowrap; background:var(--tdue-fill); }
  .tdue table.tdue-details td { width:32%; font-weight:600; word-break:break-word; }
  .tdue table.tdue-details tr:last-child th, .tdue table.tdue-details tr:last-child td { border-bottom:0; }
  @media (max-width: 900px) {
    .tdue table.tdue-details, .tdue table.tdue-details tbody, .tdue table.tdue-details tr, .tdue table.tdue-details th, .tdue table.tdue-details td { display:block; width:auto; }
    .tdue table.tdue-details th { border-bottom:0; padding-bottom:2px; background:transparent; }
  }
  .tdue h4 { font-size:20px; font-weight:600; margin:32px 0 16px; }
  .tdue table.product-overview th { font-size:12px; font-weight:500; text-transform:uppercase; letter-spacing:.04em; color:var(--tdue-muted); white-space:nowrap; }
  .tdue table.product-overview td { padding:12px 16px; vertical-align:middle; }
  .tdue td.num, .tdue th.num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
  .tdue tr.team-row td { background:var(--tdue-fill); font-weight:600; }
  .tdue tr.total-row td { border-top:2px solid var(--tdue-ink); font-weight:600; }
  .tdue .tdue-status { display:inline-block; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:600; text-transform:uppercase; }
  .tdue .tdue-status-open { background:#fee2e2; color:#991b1b; } .tdue .tdue-status-partial { background:#fef3c7; color:#92400e; }
  .tdue .tdue-status-settled { background:#dcfce7; color:#166534; } .tdue .tdue-status-written_off { background:var(--tdue-fill); color:var(--tdue-muted); }
  .tdue .tdue-actions { display:flex; flex-wrap:wrap; gap:8px; }
  .tdue .tdue-actions .btn { height:40px; display:inline-flex; align-items:center; }
  .tdue .tdue-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px; }
  .tdue .tdue-panel { border:1px solid var(--tdue-line); border-radius:8px; padding:24px; }
  .tdue .tdue-panel h5 { font-size:16px; font-weight:600; margin:0 0 16px; }
  .tdue form .form-group { margin-bottom:16px; } .tdue form label { font-size:14px; font-weight:500; margin-bottom:8px; }
  .tdue form .form-control { height:40px; } .tdue form textarea.form-control { height:auto; }
  .tdue .tdue-log { list-style:none; margin:0; padding:0; } .tdue .tdue-log li { padding:12px 0; border-bottom:1px solid var(--tdue-line); }
  .tdue .tdue-log .who { font-size:12px; color:var(--tdue-muted); }
  .tdue .tdue-inline { display:inline; }
  .tdue .tdue-muted { color:var(--tdue-muted); font-size:14px; }
  .tdue .btn:focus-visible, .tdue a:focus-visible { outline:2px solid var(--tdue-accent); outline-offset:2px; }
</style>
@endsection

@section('content')
@php
  $c = $dues->tenantContract;
  $teamLabels = ['backoffice' => 'Back Office', 'maintenance' => 'Maintenance'];
  $canTeam = function ($team) use ($teams) { return in_array($team, $teams, true); };
  $byTeam = ['backoffice' => [], 'maintenance' => []];
  foreach ($r['lines'] as $l) { $byTeam[isset($byTeam[$l['owner_team']]) ? $l['owner_team'] : 'backoffice'][] = $l; }
  $kindLabel = ['rent_receipt' => 'Rent receipt', 'general_receipt_line' => 'General receipt', 'deposit_deduction' => 'Deposit deduction'];
  $lineById = $dues->lines->keyBy('id');
@endphp
<div class="row page-titles">
  <div class="col-md-6 align-self-center"><h3 class="text-themecolor">Termination dues · {{ optional($c)->tenant_contract_no }}</h3></div>
  <div class="col-md-6 align-self-center text-right">{{ Breadcrumbs::render('termination-dues.show', $dues) }}</div>
</div>

<div class="card tdue"><div class="card-body">
  @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif

  @php
    $tenant = optional(optional($c)->tenant);
    $bldg = optional(optional($c)->building);
    $unit = optional(optional($c)->unit);
    $zero = $r['total']['balance'] <= 0.005;
    $lastFu = $dues->followups->first();
  @endphp
  <div class="tdue-head">
    <div>
      <h3 class="tdue-title">{{ $tenant->tenant_name ?: 'Tenant NA' }}</h3>
      <div class="tdue-sub">{{ $tenant->tenant_contact_no ? 'Mobile ' . $tenant->tenant_contact_no : 'No mobile on file' }}</div>
      <div class="tdue-bal {{ $zero ? 'zero' : '' }}">
        {{ $zero ? 'Nothing outstanding' : 'Outstanding ' . numberFormat($r['total']['balance']) . ' OMR' }}
        <small>Back Office {{ numberFormat($r['teams']['backoffice']['balance']) }} · Maintenance {{ numberFormat($r['teams']['maintenance']['balance']) }}</small>
      </div>
    </div>
    <div class="tdue-actions">
      <a class="btn btn-primary" href="{{ route('rentReceiptGeneration.create') }}?contract_id={{ $dues->tenant_contract_id }}">Collect rent</a>
      <a class="btn btn-outline-secondary" href="{{ route('addGeneralReceipt') }}?contract_id={{ $dues->tenant_contract_id }}">Collect other charges</a>
      <a class="btn btn-outline-secondary" href="{{ route('depositRefund.create') }}?contract_id={{ $dues->tenant_contract_id }}">Deposit refund</a>
    </div>
  </div>

  <div class="tdue-id">
    <div class="tdue-id-tile primary">
      <span class="k">Building</span>
      <span class="big">{{ $bldg->building_name ?: '—' }}</span>
      <span class="meta">{{ optional($bldg->location)->locations_name ?? 'Location NA' }}{{ $bldg->building_address ? ' · Way ' . $bldg->building_address : '' }}</span>
    </div>
    <div class="tdue-id-tile">
      <span class="k">Unit</span>
      <span class="big">{{ $unit->unit_no ?: ($unit->unit_code ?: '—') }}</span>
      <span class="meta">{{ $unit->unit_code ?: '' }}{{ !empty($unit->vacant_status_name) ? ' · ' . $unit->vacant_status_name : '' }}</span>
    </div>
    <div class="tdue-id-tile">
      <span class="k">Contract</span>
      <span class="big">{{ optional($c)->tenant_contract_no ?: '#' . $dues->tenant_contract_id }}</span>
      <span class="meta">Terminated {{ $dues->termination_date ? $dues->termination_date->format('d/m/Y') : '—' }}{{ $dues->termination_date ? ' · ' . $dues->termination_date->diffInDays(now()) . ' days ago' : '' }}</span>
    </div>
  </div>

  <table class="tdue-details">
    <tr>
      <th>Dues status</th><td><span class="tdue-status tdue-status-{{ $r['status'] }}">{{ str_replace('_', ' ', $r['status']) }}</span></td>
      <th>Total owed</th><td>{{ numberFormat($r['total']['owed']) }} OMR</td>
    </tr>
    <tr>
      <th>Settled so far</th><td>{{ numberFormat($r['total']['settled']) }} OMR{{ $r['total']['waived'] > 0 ? ' · waived ' . numberFormat($r['total']['waived']) : '' }}</td>
      <th>Last follow-up</th><td>{{ $lastFu ? $lastFu->followup_date->format('d/m/Y') . ' · ' . ($methods[$lastFu->method] ?? $lastFu->method) : 'None yet' }}{{ $lastFu && $lastFu->promise_date ? ' · promised ' . $lastFu->promise_date->format('d/m/Y') : '' }}</td>
    </tr>
  </table>

  <h4>Settlement</h4>
  <div class="table-responsive"><table class="table product-overview">
    <thead><tr><th>Line</th><th class="num">Owed</th><th class="num">Deposit deduction</th><th class="num">Receipts</th><th class="num">Waived</th><th class="num">Balance</th></tr></thead>
    <tbody>
    @foreach(['backoffice', 'maintenance'] as $team)
      @if(count($byTeam[$team]))
        <tr class="team-row"><td>{{ $teamLabels[$team] }}</td>
          <td class="num">{{ numberFormat($r['teams'][$team]['owed']) }}</td><td class="num" colspan="2">{{ numberFormat($r['teams'][$team]['settled']) }} settled</td><td></td>
          <td class="num">{{ numberFormat($r['teams'][$team]['balance']) }}</td></tr>
        @foreach($byTeam[$team] as $l)
          @if(!empty($l['credit']))
          <tr class="credit-row"><td>&nbsp;&nbsp;<span class="tdue-muted">{{ $l['description'] }} · {{ \Modules\BackOffice\Services\TerminationDuesCategory::label($l['category']) }} · credit</span></td>
            <td class="num tdue-muted">{{ numberFormat($l['owed']) }}</td><td class="num tdue-muted">—</td><td class="num tdue-muted">—</td>
            <td class="num tdue-muted">—</td><td class="num tdue-muted">—</td></tr>
          @else
          <tr><td>&nbsp;&nbsp;{{ $l['description'] }} <span class="tdue-muted">· {{ \Modules\BackOffice\Services\TerminationDuesCategory::label($l['category']) }}</span></td>
            <td class="num">{{ numberFormat($l['owed']) }}</td><td class="num">{{ numberFormat($l['deposit']) }}</td><td class="num">{{ numberFormat($l['receipts']) }}</td>
            <td class="num">{{ numberFormat($l['waived']) }}</td><td class="num">{{ numberFormat($l['balance']) }}</td></tr>
          @endif
        @endforeach
      @endif
    @endforeach
    <tr class="total-row"><td>Total</td><td class="num">{{ numberFormat($r['total']['owed']) }}</td><td class="num" colspan="2">{{ numberFormat($r['total']['settled']) }}</td>
      <td class="num">{{ numberFormat($r['total']['waived']) }}</td><td class="num">{{ numberFormat($r['total']['balance']) }}</td></tr>
    </tbody></table></div>
  @foreach($r['over_collected'] as $cat => $amt)
    <p class="tdue-muted">Over-collected on {{ \Modules\BackOffice\Services\TerminationDuesCategory::label($cat) }}: {{ numberFormat($amt) }} — finance may need to refund or re-assign.</p>
  @endforeach

  <h4>What has been received</h4>
  <div class="table-responsive"><table class="table product-overview">
    <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>Description</th><th>Account</th><th class="num">Amount</th><th>Applied to</th></tr></thead>
    <tbody>
    @forelse($r['sources'] as $s)
      @php $applied = array_values(array_filter($r['allocations'], function ($a) use ($s) { return $a['source_key'] === $s['key']; })); @endphp
      <tr><td>{{ $s['date'] ? date('d/m/Y', strtotime($s['date'])) : '—' }}</td><td>{{ $kindLabel[$s['kind']] }}</td>
        <td><a href="{{ $s['url'] }}">{{ $s['ref'] }}</a></td><td>{{ $s['description'] }}</td><td>{{ $s['account_code'] }}</td>
        <td class="num">{{ numberFormat($s['amount']) }}</td>
        <td>@forelse($applied as $a){{ optional($lineById->get($a['line_id']))->description }} ({{ numberFormat($a['amount']) }}){{ $a['manual'] ? ' · manual' : '' }}@if(!$loop->last), @endif
            @empty <span class="tdue-muted">unallocated</span> @endforelse</td></tr>
    @empty
      <tr><td colspan="7" class="tdue-muted">Nothing received yet.</td></tr>
    @endforelse
    </tbody></table></div>
  @if(count($r['pending']))
    <p class="tdue-muted">Awaiting approval (not counted yet): @foreach($r['pending'] as $p)<a href="{{ url('rentReceiptGeneration/' . $p->id) }}">{{ $p->receipts_generation_receipt_no }}</a> {{ numberFormat($p->receipts_generation_amt) }}@if(!$loop->last), @endif @endforeach</p>
  @endif

  @if(count($r['unallocated']))
    <h4>Needs your decision</h4>
    <p class="tdue-muted">These payments could not be matched to a category by account code. Assign each to the line it settles.</p>
    @foreach($r['unallocated'] as $s)
      <form method="post" action="{{ route('termination-dues.allocate', $dues->id) }}" class="form-inline" style="gap:8px; margin-bottom:8px; flex-wrap:wrap">
        @csrf
        <input type="hidden" name="source_type" value="{{ $s['kind'] }}"><input type="hidden" name="source_id" value="{{ $s['id'] }}">
        <span><a href="{{ $s['url'] }}">{{ $s['ref'] }}</a> · {{ $s['description'] ?: $kindLabel[$s['kind']] }} · acc {{ $s['account_code'] ?: '—' }} · <strong>{{ numberFormat($s['amount']) }}</strong></span>
        <label class="sr-only" for="line-{{ $s['id'] }}">Assign to line</label>
        <select id="line-{{ $s['id'] }}" name="line_id" class="form-control" required>
          @foreach($dues->lines as $line)@if($canTeam($line->owner_team) && empty($r['lines'][$line->id]['credit']))<option value="{{ $line->id }}">{{ $line->description }} ({{ $teamLabels[$line->owner_team] }})</option>@endif @endforeach
        </select>
        <input type="number" step="0.001" min="0.001" name="amount" class="form-control" value="{{ $s['amount'] }}" aria-label="Amount to apply" required>
        <input type="text" name="remark" class="form-control" placeholder="Why (optional)" aria-label="Remark">
        <button type="submit" class="btn btn-outline-primary">Assign</button>
      </form>
    @endforeach
  @endif

  <div class="tdue-grid" style="margin-top:32px">
    <section class="tdue-panel">
      <h5>Follow-up log</h5>
      <form method="post" action="{{ route('termination-dues.followup', $dues->id) }}">
        @csrf
        <div class="form-group"><label for="fu-team">Team</label>
          <select id="fu-team" name="owner_team" class="form-control" required>@foreach($teams as $t)<option value="{{ $t }}">{{ $teamLabels[$t] }}</option>@endforeach</select></div>
        <div class="form-row">
          <div class="form-group col-sm-6"><label for="fu-date">Date</label><input id="fu-date" type="date" name="followup_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
          <div class="form-group col-sm-6"><label for="fu-method">How</label>
            <select id="fu-method" name="method" class="form-control" required>@foreach($methods as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
        </div>
        <div class="form-group"><label for="fu-note">What was said</label><textarea id="fu-note" name="note" class="form-control" rows="2" maxlength="2000"></textarea></div>
        <div class="form-group"><label for="fu-promise">Tenant promised to pay by</label><input id="fu-promise" type="date" name="promise_date" class="form-control"></div>
        <button type="submit" class="btn btn-primary">Record follow-up</button>
      </form>
      <ul class="tdue-log" style="margin-top:24px">
        @forelse($dues->followups as $f)
          <li><strong>{{ $f->followup_date->format('d/m/Y') }}</strong> · {{ $methods[$f->method] ?? $f->method }} · {{ $teamLabels[$f->owner_team] ?? $f->owner_team }}
            @if($f->promise_date)<span class="tdue-muted"> · promised {{ $f->promise_date->format('d/m/Y') }}</span>@endif
            <div>{{ $f->note }}</div>
            <div class="who">{{ optional($f->creator)->username }} · {{ $f->created_at->format('d/m/Y H:i') }}</div></li>
        @empty
          <li class="tdue-muted">No follow-ups recorded yet.</li>
        @endforelse
      </ul>
    </section>

    <section class="tdue-panel">
      <h5>Manual decisions</h5>
      <ul class="tdue-log">
        @forelse($manual as $m)
          <li>{{ $m->source_type === 'waiver' ? 'Waived' : 'Assigned ' . ($kindLabel[$m->source_type] ?? $m->source_type) . ' #' . $m->source_id }} {{ numberFormat($m->amount) }} → {{ optional($lineById->get($m->termination_dues_line_id))->description }}
            @if($m->remark)<div class="tdue-muted">{{ $m->remark }}</div>@endif
            <div class="who">{{ optional($m->creator)->username }} · {{ $m->created_at->format('d/m/Y') }}
              @if($canTeam(optional($lineById->get($m->termination_dues_line_id))->owner_team))
              <form method="post" action="{{ route('termination-dues.allocation.destroy', [$dues->id, $m->id]) }}" class="tdue-inline" onsubmit="return confirm('Remove this decision? The balance will be recalculated.');">
                @csrf @method('DELETE')<button type="submit" class="btn btn-link btn-sm">Remove</button></form>
              @endif</div></li>
        @empty
          <li class="tdue-muted">None.</li>
        @endforelse
      </ul>
      <h5 style="margin-top:24px">Waive an amount</h5>
      <form method="post" action="{{ route('termination-dues.waive', $dues->id) }}">
        @csrf
        <div class="form-group"><label for="wv-line">Line</label>
          <select id="wv-line" name="line_id" class="form-control" required>@foreach($dues->lines as $line)@if($canTeam($line->owner_team) && $r['lines'][$line->id]['balance'] > 0)<option value="{{ $line->id }}">{{ $line->description }} — balance {{ numberFormat($r['lines'][$line->id]['balance']) }}</option>@endif @endforeach</select></div>
        <div class="form-group"><label for="wv-amt">Amount</label><input id="wv-amt" type="number" step="0.001" min="0.001" name="amount" class="form-control" required></div>
        <div class="form-group"><label for="wv-remark">Approval / reason</label><input id="wv-remark" type="text" name="remark" class="form-control" maxlength="1000" required placeholder="e.g. approved by Finance Manager on 17/09"></div>
        <button type="submit" class="btn btn-outline-danger">Waive</button>
      </form>
    </section>
  </div>
</div></div>
@endsection
