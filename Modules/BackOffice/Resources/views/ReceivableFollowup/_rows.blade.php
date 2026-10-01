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
<div class="rf-strip" data-summary="{{ $count }} {{ $count === 1 ? 'agreement' : 'agreements' }} with a balance, total {{ number_format($totals['balance'], 3) }}">
  <div class="rf-stats">
    <span>As on <strong>{{ $asOnText }}</strong></span>
    <span><strong>{{ number_format($count) }}</strong> {{ $count === 1 ? 'agreement' : 'agreements' }}</span>
    <span>Balance <strong>{{ number_format($totals['balance'], 3) }}</strong></span>
  </div>
  <span>Figures as of {{ \Carbon\Carbon::parse($computedAt)->format('d/m/Y H:i') }} · <button type="button" class="rf-link" id="rf-refresh">Refresh</button></span>
</div>
<div class="table-responsive">
<table class="rf-table">
  <caption class="rf-sr">Agreements with a balance as on {{ $asOnText }}</caption>
  <thead><tr>
    {!! $th('tenant', 'Tenant', 'asc') !!}
    {!! $th('contract', 'Agreement', 'asc') !!}
    {!! $th('building', 'Building', 'asc') !!}
    <th scope="col">Unit No</th>
    {!! $th('last_received', 'Last Received', 'desc') !!}
    {!! $th('due', 'Total Due', 'desc', true) !!}
    {!! $th('received', 'Amount Received', 'desc', true) !!}
    {!! $th('balance', 'Balance', 'desc', true) !!}
    <th scope="col">Last Follow-up</th>
    <th scope="col">Promised Date</th>
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
    <tr id="rf-row-{{ $r->id }}">
      <td>{{ $r->tenant }}<span class="rf-sub">{{ $r->contact_person }}@if($r->contact_person && $r->phone) · @endif{{ $r->phone }}</span></td>
      <td>{{ $r->contract }}<span class="rf-sub">{{ $fmtDate($r->from) }} – {{ $fmtDate($r->to) }}</span></td>
      <td>{{ $r->building }}</td>
      <td>{{ $r->unit }}</td>
      <td>{{ $fmtDate($r->last_received) }}</td>
      <td class="num">{{ number_format($r->due, 3) }}</td>
      <td class="num">{{ number_format($r->received, 3) }}</td>
      <td class="num rf-bal">{{ number_format($r->balance, 3) }}</td>
      <td id="rf-last-{{ $r->id }}">@if($last){{ $last->created_at->format('d/m/Y H:i') }}<span class="rf-sub">{{ $last->creatorName() }}</span>@else<span class="rf-sub">Not followed up</span>@endif</td>
      <td id="rf-promise-{{ $r->id }}" class="{{ $overdue ? 'rf-overdue' : '' }}">@if($promise){{ $promise->promised_date->format('d/m/Y') }}@if($overdue)<span class="rf-flag">Overdue</span>@endif @else — @endif</td>
      <td class="rf-actions">
        @can('add_receivable_followup')
        <button type="button" class="rf-btn rf-btn-primary rf-followup" data-url="{{ route('receivable-followup.form', $r->id) }}?as_on={{ $asOn }}" aria-label="Follow-up {{ $r->contract }}">Follow-up</button>
        @endcan
        <button type="button" class="rf-btn rf-view" data-url="{{ route('receivable-followup.timeline', $r->id) }}?as_on={{ $asOn }}" aria-label="View history of {{ $r->contract }}">View</button>
      </td>
    </tr>
  @empty
    <tr><td colspan="11" class="rf-empty"><strong>No agreements with a balance for these filters.</strong>Try another month, building or management type, or clear the search.</td></tr>
  @endforelse
  </tbody>
  @if($count)
  <tfoot><tr class="rf-totals">
    <td colspan="5">Total · {{ number_format($count) }} {{ $count === 1 ? 'agreement' : 'agreements' }}</td>
    <td class="num">{{ number_format($totals['due'], 3) }}</td>
    <td class="num">{{ number_format($totals['received'], 3) }}</td>
    <td class="num">{{ number_format($totals['balance'], 3) }}</td>
    <td colspan="3"></td>
  </tr></tfoot>
  @endif
</table>
</div>
<div style="margin-top:12px">{{ $rows->links() }}</div>
