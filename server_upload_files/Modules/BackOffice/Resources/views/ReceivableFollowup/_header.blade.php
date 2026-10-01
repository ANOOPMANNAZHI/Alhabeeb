{{-- Agreement summary at the top of the follow-up form and the timeline. --}}
<dl class="rf-head">
  <div><dt>Tenant</dt><dd>{{ optional($contract->tenant)->tenant_name }}@if(optional($contract->tenant)->tenant_contact_no)<span class="rf-help">{{ $contract->tenant->tenant_contact_no }}</span>@endif</dd></div>
  <div><dt>Agreement</dt><dd>{{ $contract->tenant_contract_no }}</dd></div>
  <div><dt>Building / Unit</dt><dd>{{ optional($contract->building)->building_name }} / {{ optional($contract->Unit)->unit_no }}</dd></div>
  <div><dt>Balance</dt><dd class="rf-bal">{{ $balance !== null ? number_format($balance, 3) : '—' }}</dd></div>
  <div><dt>Not paid from</dt><dd class="rf-unpaid">{{ $notPaidFrom ?: '—' }}@if($paidTill)<span class="rf-help">paid till {{ $paidTill }}</span>@endif</dd></div>
  <div><dt>Last payment</dt><dd>{{ $lastPayment ?: '—' }}</dd></div>
  <div><dt>Last promised date</dt><dd>{{ $lastPromise ?: '—' }}</dd></div>
</dl>
