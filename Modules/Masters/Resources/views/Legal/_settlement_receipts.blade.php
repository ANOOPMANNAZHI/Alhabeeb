{{-- Receipts on the contract since the agreement / judgment date (LegalSettlement::receipts), and which of them count
     towards the settlement. Needs $settlementReceipts, $settlement. Optional $receiptsInModal (bordered box). --}}
@php
    $counted = $settlementReceipts->where('counts', true);
    $notCounted = $settlementReceipts->where('counts', false);
    $since = \Carbon\Carbon::parse($settlement->agreed_date)->format('d/m/Y');
@endphp
<div @if(!empty($receiptsInModal)) style="border:1px solid var(--lg-border);border-radius:var(--lg-radius-sm);margin-bottom:12px;" @endif>
  <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:4px 12px;padding:10px 12px;{{ empty($receiptsInModal) ? 'border-top:1px solid var(--lg-border);' : '' }}">
    <span class="lg-card__title" style="font-size:13px !important;">Receipts since {{ $since }} <span class="lg-count">{{ count($settlementReceipts) }}</span></span>
    <span class="tnum" style="font-size:13px;">Counted <b>{{ numberFormat($counted->sum('amount')) }}</b>
      @if($notCounted->count())<span class="muted"> &middot; not counted {{ numberFormat($notCounted->sum('amount')) }}</span>@endif</span>
  </div>
  @if(count($settlementReceipts))
  <div class="lg-table-wrap">
    <table class="lg-table">
      <caption class="sr-only">Receipts since the agreement date</caption>
      <thead><tr><th scope="col">Receipt no.</th><th scope="col">Date</th><th scope="col">Type</th><th scope="col">Paid by</th>
        <th scope="col" class="num">Amount</th><th scope="col">Status</th><th scope="col">Counts</th></tr></thead>
      <tbody>
        @foreach($settlementReceipts as $r)
        <tr @if(!$r->counts) style="opacity:.75" @endif>
          <td class="strong">{{ $r->receipt_no }}</td>
          <td class="tnum">{{ \Carbon\Carbon::parse($r->receipt_date)->format('d/m/Y') }}</td>
          <td>{{ $r->type_label }}</td>
          <td>{{ $r->method_label }}@if($r->cheque_no && (int) $r->method === 1)<div class="muted" style="font-size:12px;">{{ $r->cheque_no }}</div>@endif</td>
          <td class="num">{{ numberFormat($r->amount) }}</td>
          <td><span class="lg-badge lg-badge--{{ $r->state[1] }}">{{ $r->state[0] }}</span></td>
          <td>@if($r->counts)<span class="lg-badge lg-badge--success">Yes</span>@else<span class="lg-badge lg-badge--neutral">No</span>@endif</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if($notCounted->count())
  <div class="lg-hint" style="padding:8px 12px;margin:0;">Only approved or posted receipts count. Waiting, rejected or cancelled receipts are shown for reference.</div>
  @endif
  @else
  <div class="lg-empty" style="padding:16px;">No rent or general receipts on this contract since {{ $since }}.</div>
  @endif
</div>
