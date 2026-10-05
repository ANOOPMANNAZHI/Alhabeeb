@php
  $fmtDate = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—'; };
  $th = function ($key, $label, $first, $num = false) use ($sort, $desc) {
      $aria = $sort === $key ? ($desc ? 'descending' : 'ascending') : 'none';
      $arrow = $sort === $key ? ($desc ? ' ▼' : ' ▲') : '';
      return '<th scope="col" aria-sort="' . $aria . '"' . ($num ? ' class="num"' : '') . '>'
          . '<button type="button" class="rf-sort" data-sort="' . $key . '" data-first="' . $first . '">' . e($label) . '<span aria-hidden="true">' . $arrow . '</span></button></th>';
  };
  $count = $rows->total();
  $asOnText = \Carbon\Carbon::parse($asOn)->format('d/m/Y');
@endphp
<div id="rf-cards-data" hidden data-cards="{{ json_encode($cards) }}"></div>
<div class="rf-strip" data-summary="{{ $count }} {{ $count === 1 ? 'agreement' : 'agreements' }} with a balance, total {{ \Modules\BackOffice\Entities\ReceivableFollowup::amount($totals['balance']) }}">
  <div class="rf-stats">
    <span>Dues up to <strong>{{ $asOnText }}</strong> ({{ $monthLabel }} − 2 months)</span>
    <span>Payments up to <strong>{{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}</strong></span>
    <span><strong>{{ number_format($count) }}</strong> {{ $count === 1 ? 'agreement' : 'agreements' }}</span>
    <span>Balance <strong>{{ \Modules\BackOffice\Entities\ReceivableFollowup::amount($totals['balance']) }}</strong></span>
  </div>
  <span>Figures as of {{ \Carbon\Carbon::parse($computedAt)->format('d/m/Y H:i') }} · <button type="button" class="rf-link" id="rf-refresh">Refresh</button></span>
</div>
<div class="table-responsive">
<table class="rf-table">
  <caption class="rf-sr">Agreements still owing on {{ $asOnText }}</caption>
  <thead><tr>
    {!! $th('tenant', 'Tenant', 'asc') !!}
    {!! $th('contract', 'Agreement', 'asc') !!}
    {!! $th('building', 'Building', 'asc') !!}
    @if($showAre){!! str_replace('<th scope="col"', '<th scope="col" title="Contract Administrator"', $th('are', 'CA', 'asc')) !!}@endif
    {!! $th('unit', 'Unit No', 'asc') !!}
    {!! $th('rent', 'Rent', 'desc', true) !!}
    {!! $th('paid_till', 'Paid Till', 'asc') !!}
    {!! $th('last_received', 'Last Payment', 'desc') !!}
    {!! $th('due', 'Total Due', 'desc', true) !!}
    {!! $th('received', 'Amount Received', 'desc', true) !!}
    {!! $th('balance', 'Balance', 'desc', true) !!}
    {!! $th('last_followup', 'Last Follow-up', 'desc') !!}
    {!! $th('next_followup', 'Next Follow-up Date', 'asc') !!}
    <th scope="col"><span class="rf-sr">Actions</span></th>
  </tr></thead>
  <tbody>
  @forelse($rows as $r)
    @php
      $fus = $followups->get($r->id, collect());
      $last = $fus->first();
      $promise = $fus->first(function ($f) { return $f->promised_date !== null; });
      $overdue = $promise && $promise->promised_date->format('Y-m-d') < $today;
    @endphp
    <tr id="rf-row-{{ $r->id }}" class="rf-row" data-timeline="{{ route('receivable-followup.timeline', $r->id) }}?as_on={{ $asOn }}" tabindex="0" aria-label="{{ $r->tenant }}, {{ $r->contract }}: open follow-up history">
      <td>{{ $r->tenant }}<span class="rf-sub">{{ $r->contact_person }}@if($r->contact_person && $r->phone) · @endif{{ $r->phone }}</span></td>
      <td>{{ $r->contract }}<span class="rf-sub">{{ $fmtDate($r->from) }} – {{ $fmtDate($r->to) }}</span></td>
      <td>{{ $r->building }}</td>
      @if($showAre)<td>{{ $r->are ?: '—' }}</td>@endif
      <td>{{ $r->unit }}</td>
      <td class="num">{{ $r->rent !== null ? \Modules\BackOffice\Entities\ReceivableFollowup::amount($r->rent) : '—' }}</td>
      <td>@if($r->paid_till){{ $fmtDate($r->paid_till) }}@if($r->not_paid_from && $r->paid_till >= $r->not_paid_from)<span class="rf-sub">short paid</span>@endif @else<span class="rf-unpaid">Never paid</span>@if($r->start)<span class="rf-sub">started {{ $fmtDate($r->start) }}</span>@endif @endif</td>
      <td>@if($r->last_received){{ $fmtDate($r->last_received) }}@if($r->last_amount !== null)<span class="rf-sub">{{ \Modules\BackOffice\Entities\ReceivableFollowup::amount($r->last_amount) }}</span>@endif @else — @endif</td>
      <td class="num">{{ \Modules\BackOffice\Entities\ReceivableFollowup::amount($r->due) }}</td>
      <td class="num">{{ \Modules\BackOffice\Entities\ReceivableFollowup::amount($r->received) }}</td>
      <td class="num rf-bal">{{ \Modules\BackOffice\Entities\ReceivableFollowup::amount($r->balance) }}</td>
      <td id="rf-last-{{ $r->id }}">@if($last){{ $last->created_at->format('d/m/Y H:i') }}<span class="rf-sub">{{ $last->creatorName() }}</span>@else<span class="rf-sub">Not followed up</span>@endif</td>
      <td id="rf-promise-{{ $r->id }}" class="{{ $overdue ? 'rf-overdue' : '' }}">@if($promise){{ $promise->promised_date->format('d/m/Y') }}@if($overdue)<span class="rf-flag">Overdue</span>@endif @else — @endif</td>
      <td class="rf-actions">
        @can('add_receivable_followup')
        <button type="button" class="rf-btn rf-btn-primary rf-followup" data-url="{{ route('receivable-followup.form', $r->id) }}?as_on={{ $asOn }}" aria-label="Follow-up {{ $r->contract }}">Follow-up</button>
        @endcan
      </td>
    </tr>
  @empty
    <tr><td colspan="{{ $showAre ? 14 : 13 }}" class="rf-empty"><strong>No agreements with a balance for these filters.</strong>Try another month, building or management type, or clear the search.</td></tr>
  @endforelse
  </tbody>
  @if($count)
  <tfoot><tr class="rf-totals">
    <td colspan="{{ $showAre ? 8 : 7 }}">Total · {{ number_format($count) }} {{ $count === 1 ? 'agreement' : 'agreements' }}</td>
    <td class="num">{{ \Modules\BackOffice\Entities\ReceivableFollowup::amount($totals['due']) }}</td>
    <td class="num">{{ \Modules\BackOffice\Entities\ReceivableFollowup::amount($totals['received']) }}</td>
    <td class="num">{{ \Modules\BackOffice\Entities\ReceivableFollowup::amount($totals['balance']) }}</td>
    <td colspan="3"></td>
  </tr></tfoot>
  @endif
</table>
</div>
<div style="margin-top:12px">{{ $rows->links() }}</div>
