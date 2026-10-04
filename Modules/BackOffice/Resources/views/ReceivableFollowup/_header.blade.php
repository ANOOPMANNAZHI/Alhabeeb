{{-- Agreement summary at the top of the follow-up form and the timeline. --}}
<dl class="rf-head">
  <div><dt>Tenant</dt><dd>{{ optional($contract->tenant)->tenant_name }}@if(optional($contract->tenant)->tenant_contact_no)<span class="rf-help">{{ $contract->tenant->tenant_contact_no }}</span>@endif</dd></div>
  <div><dt>Agreement</dt><dd>{{ $contract->tenant_contract_no }}</dd></div>
  <div><dt>Building / Unit</dt><dd>{{ optional($contract->building)->building_name }} / {{ optional($contract->Unit)->unit_no }}</dd></div>
  <div><dt>Balance</dt><dd class="rf-bal">{{ $balance !== null ? \Modules\BackOffice\Entities\ReceivableFollowup::amount($balance) : '—' }}</dd></div>
  <div><dt>Rent</dt><dd>{{ $contract->tenant_contract_rent !== null ? \Modules\BackOffice\Entities\ReceivableFollowup::amount($contract->tenant_contract_rent) : '—' }}</dd></div>
  <div><dt>Paid till</dt><dd>@if($paidTill){{ $paidTill }}@if($shortPaid)<span class="rf-help">short paid</span>@endif @elseif($balance !== null)<span class="rf-unpaid">Never paid</span>@else — @endif</dd></div>
  <div><dt>Last payment</dt><dd>{{ $lastPayment ?: '—' }}</dd></div>
  <div><dt>Next follow-up date</dt><dd>{{ $lastPromise ?: '—' }}</dd></div>
</dl>
