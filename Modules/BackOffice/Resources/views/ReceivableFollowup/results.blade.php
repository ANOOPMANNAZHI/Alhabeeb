@extends('layouts.plms-app')
@section('css')
@include('backoffice::ReceivableFollowup._styles')
<style>
  .rf .rf-res-form { display:flex; flex-wrap:wrap; gap:12px 16px; align-items:flex-end; margin:0 0 16px; }
  .rf .rf-res-form label { display:block; font-size:13px; font-weight:600; margin:0 0 6px; }
  .rf .rf-res-form .form-control { font-size:14px; min-width:160px; }
  .rf .rf-res-form .rf-btn { min-height:36px; padding:6px 16px; }
  .rf .rf-res-note { font-size:13px; color:var(--rf-muted); margin:0 0 12px; }
  .rf .rf-table td.num, .rf .rf-table th.num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
  .rf .rf-table tfoot td { font-weight:700; border-top:2px solid var(--rf-line); }
  .rf .rf-table tr.is-picked td { background:#e8f2fb; }
  .rf .rf-rc { display:block; font-size:13px; color:var(--rf-muted); white-space:nowrap; }
  .rf .rf-res-h { font-size:17px; font-weight:600; line-height:1.4; margin:24px 0 8px; }
  .rf-badge.open { background:var(--rf-info-bg); color:var(--rf-info); }
  .rf-badge.none { background:var(--rf-fill); color:var(--rf-muted); }
</style>
@endsection
@section('content')
@php
  $amt = function ($v) { return \Modules\BackOffice\Entities\ReceivableFollowup::amount($v); };
  $d = function ($v) { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '—'; };
  $link = function ($uid) use ($from, $to) { return route('receivable-followup.results', ['from' => $from, 'to' => $to, 'user' => $uid]); };
@endphp
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Follow-up Results</div></div>{{ Breadcrumbs::render('receivable-followup.results') }}</div></div>
<div class="rf"><div class="card card-box"><div class="card-body">
  <form class="rf-res-form" method="get" action="{{ route('receivable-followup.results') }}">
    <div><label for="rf-from">Follow-ups from</label><input type="date" id="rf-from" name="from" class="form-control" value="{{ $from }}" max="{{ $today }}"></div>
    <div><label for="rf-to">To</label><input type="date" id="rf-to" name="to" class="form-control" value="{{ $to }}" max="{{ $today }}"></div>
    <div><button type="submit" class="rf-btn rf-btn-primary">Show</button></div>
    <div><a class="rf-btn" href="{{ route('receivable-followup.index') }}">Back to Follow-up</a></div>
  </form>
  <p class="rf-res-note">A payment counts for a follow-up when the tenant paid rent within {{ $windowDays }} days after it, or before the next follow-up on that agreement if that comes first.
    Only cash, bank transfer and new cheque receipts count as the follow-up's result; cheques from PDCs we already held are shown apart under <strong>PDC cleared</strong>.
    Amounts in OMR.</p>

  <div class="table-responsive">
  <table class="rf-table">
    <caption class="rf-sr">Follow-up results per CA, {{ $d($from) }} to {{ $d($to) }}</caption>
    <thead><tr>
      <th scope="col">CA</th>
      <th scope="col" class="num">Follow-ups</th>
      <th scope="col" class="num">Agreements</th>
      <th scope="col" class="num" title="Agreements where the tenant paid after the follow-up">Paid after follow-up</th>
      <th scope="col" class="num">Success %</th>
      <th scope="col" class="num" title="Rent received after the follow-ups (cash, transfer, new cheque)">Amount collected</th>
      <th scope="col" class="num" title="Follow-ups where the tenant paid the full balance shown at follow-up time">Fully cleared</th>
      <th scope="col" class="num" title="PDC cheques that cleared in the same period (not counted above)">PDC cleared</th>
      <th scope="col" class="num" title="Follow-ups whose {{ $windowDays }} days are not over yet">Still open</th>
      <th scope="col">Last follow-up</th>
    </tr></thead>
    <tbody>
    @forelse($summary as $s)
      <tr class="{{ $user === $s['user_id'] ? 'is-picked' : '' }}">
        <td><a href="{{ $link($s['user_id']) }}#rf-detail">{{ $s['name'] }}</a></td>
        <td class="num">{{ number_format($s['followups']) }}</td>
        <td class="num">{{ number_format($s['agreements']) }}</td>
        <td class="num">{{ number_format($s['paid_agreements']) }}</td>
        <td class="num">{{ $s['success'] }}%</td>
        <td class="num">{{ $amt($s['paid']) }}</td>
        <td class="num">{{ number_format($s['cleared']) }}</td>
        <td class="num">{{ $amt($s['pdc']) }}</td>
        <td class="num">{{ number_format($s['open']) }}</td>
        <td>{{ $s['last_at'] ? \Carbon\Carbon::parse($s['last_at'])->format('d/m/Y H:i') : '—' }}</td>
      </tr>
    @empty
      <tr><td colspan="10" class="rf-empty">No follow-ups between {{ $d($from) }} and {{ $d($to) }}.</td></tr>
    @endforelse
    </tbody>
    @if(count($summary) > 1)
    <tfoot><tr>
      <td>Total</td>
      <td class="num">{{ number_format($totals['followups']) }}</td>
      <td class="num">{{ number_format($totals['agreements']) }}</td>
      <td class="num">{{ number_format($totals['paid_agreements']) }}</td>
      <td class="num">{{ $totals['agreements'] ? round(100 * $totals['paid_agreements'] / $totals['agreements']) : 0 }}%</td>
      <td class="num">{{ $amt($totals['paid']) }}</td>
      <td class="num">{{ number_format($totals['cleared']) }}</td>
      <td class="num">{{ $amt($totals['pdc']) }}</td>
      <td class="num">{{ number_format($totals['open']) }}</td>
      <td></td>
    </tr></tfoot>
    @endif
  </table>
  </div>

  @if($user)
  <div class="rf-res-h" id="rf-detail" role="heading" aria-level="2">{{ $userName }} — {{ count($detail) }} {{ count($detail) === 1 ? 'follow-up' : 'follow-ups' }}</div>
  <div class="table-responsive">
  <table class="rf-table">
    <caption class="rf-sr">Follow-ups by {{ $userName }} and the payments after them</caption>
    <thead><tr>
      <th scope="col">Followed up</th>
      <th scope="col">Tenant</th>
      <th scope="col">Agreement</th>
      <th scope="col">Building / Unit</th>
      <th scope="col" class="num">Balance then</th>
      <th scope="col" class="num">Paid after</th>
      <th scope="col">Receipts</th>
      <th scope="col">Result</th>
      <th scope="col">Counted till</th>
    </tr></thead>
    <tbody>
    @forelse($detail as $f)
      <tr>
        <td>{{ \Carbon\Carbon::parse($f->at)->format('d/m/Y H:i') }}</td>
        <td>{{ $f->tenant }}</td>
        <td>{{ $f->contract_no }}</td>
        <td>{{ $f->place }}</td>
        <td class="num">{{ $f->balance !== null ? $amt($f->balance) : '—' }}</td>
        <td class="num">{{ $f->paid > 0 ? $amt($f->paid) : '—' }}</td>
        <td>
          @foreach($f->receipts as $r)
            <span class="rf-rc">{{ $r['no'] }} · {{ $d($r['date']) }} · {{ $amt($r['amount']) }}@if($r['pdc']) (PDC)@endif</span>
          @endforeach
          @if(!$f->receipts)—@endif
        </td>
        <td>
          @if($f->cleared)<span class="rf-badge kept">Cleared</span>
          @elseif($f->paid > 0)<span class="rf-badge upcoming">Part paid</span>
          @elseif($f->open)<span class="rf-badge open">Open</span>
          @else<span class="rf-badge broken">Not paid</span>@endif
        </td>
        <td>{{ $d($f->window_end) }}@if($f->closed_by_next)<span class="rf-rc">next follow-up</span>@endif</td>
      </tr>
    @empty
      <tr><td colspan="9" class="rf-empty">No follow-ups by {{ $userName }} in this period.</td></tr>
    @endforelse
    </tbody>
  </table>
  </div>
  @endif
</div></div></div>
@endsection
