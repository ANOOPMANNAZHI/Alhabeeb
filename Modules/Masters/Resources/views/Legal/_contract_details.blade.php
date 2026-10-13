{{-- Contract, tenant / unit, payment and old contracts of a legal case, in tabs. Needs $legal; optional $dmy (rented
     from), $paidDays + $overDueFrom (overdue period), $rentAmount (overdue amount), $oldContractList. Inside a .lg page. --}}
@php
    $tc = $legal->tenantContract;
    $d = function ($v) { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '-'; };
    $omr = function ($v) { return $v !== null && $v !== '' ? numberFormat($v) : '-'; };
    $tabId = 'lgc' . $legal->id;
    $duration = $tc && $tc->tenant_contract_duration_countdown ? explode('-', $tc->tenant_contract_duration_countdown) : null;
    $hasOld = isset($oldContractList);
@endphp
<section class="lg-card" aria-labelledby="{{ $tabId }}_title">
  <div class="lg-card__head"><h2 class="lg-card__title" id="{{ $tabId }}_title">Contract</h2>
    <span class="muted" style="font-size:13px;">{{ $tc->tenant_contract_no ?? '' }} @if($tc && $tc->created_at)&middot; agreement {{ $tc->created_at->format('d/m/Y') }}@endif</span></div>
  @if(isset($rentAmount) || isset($paidDays) || isset($dmy))
  <div class="lg-kpis" style="border-top:0;border-bottom:1px solid var(--lg-border);">
    @if(isset($dmy))<div class="lg-kpi"><div class="lg-kpi__label">Rented for</div><div class="lg-kpi__value lg-kpi__value--sm">{{ $dmy }}</div></div>@endif
    @if(isset($paidDays))<div class="lg-kpi lg-kpi--bad"><div class="lg-kpi__label">Overdue period</div><div class="lg-kpi__value lg-kpi__value--sm">{{ $paidDays }} days</div>
      <div class="lg-kpi__hint">{{ $overDueFrom ? \Carbon\Carbon::parse($overDueFrom)->format('d/m/Y') : '' }} to {{ date('d/m/Y') }}</div></div>@endif
    @if(isset($rentAmount))<div class="lg-kpi lg-kpi--bad"><div class="lg-kpi__label">Overdue rent (estimate)</div><div class="lg-kpi__value lg-kpi__value--sm">{{ numberFormat($rentAmount) }}</div>
      <div class="lg-kpi__hint">months unpaid x rent</div></div>@endif
  </div>
  @endif
  <ul class="nav lg-tabs" role="tablist">
    <li role="presentation"><a class="active" href="#{{ $tabId }}_contract" data-toggle="tab" role="tab" aria-controls="{{ $tabId }}_contract" aria-selected="true">Contract</a></li>
    <li role="presentation"><a href="#{{ $tabId }}_tenant" data-toggle="tab" role="tab" aria-controls="{{ $tabId }}_tenant" aria-selected="false">Tenant &amp; unit</a></li>
    <li role="presentation"><a href="#{{ $tabId }}_payment" data-toggle="tab" role="tab" aria-controls="{{ $tabId }}_payment" aria-selected="false">Payment</a></li>
    @if($hasOld)<li role="presentation"><a href="#{{ $tabId }}_old" data-toggle="tab" role="tab" aria-controls="{{ $tabId }}_old" aria-selected="false">Old contracts <span class="lg-count">{{ count($oldContractList) }}</span></a></li>@endif
  </ul>
  <div class="tab-content">
    <div class="tab-pane active lg-card__body" id="{{ $tabId }}_contract" role="tabpanel">
      @if($tc)
      <dl class="lg-kv">
        <div><dt>Start date</dt><dd>{{ $d($tc->tenant_contract_start_date) }}</dd></div>
        <div><dt>Effective date</dt><dd>{{ $d($tc->tenant_contract_effective_date) }}</dd></div>
        <div><dt>Valid to</dt><dd>{{ $d($tc->tenant_contract_valid_to_date) }}</dd></div>
        <div><dt>Duration</dt><dd>@if($duration && count($duration) >= 3){{ $duration[0] }} y {{ $duration[1] }} m {{ $duration[2] }} d @else - @endif</dd></div>
        <div><dt>Contract value</dt><dd class="tnum">{{ $omr($tc->tenant_contract_value) }}</dd></div>
        <div><dt>Rent per month</dt><dd class="tnum">{{ $omr($tc->tenant_contract_rent) }}</dd></div>
        <div><dt>Payment term</dt><dd>{{ $tc->tenant_contract_payment_type ? $tc->TenantContractPaymentName : '-' }}</dd></div>
        <div><dt>Contract status</dt><dd><span class="lg-badge lg-badge--{{ (int) $tc->tenant_contract_status === 1 ? 'success' : 'neutral' }}">{{ (int) $tc->tenant_contract_status === 1 ? 'Active' : 'Inactive' }}</span></dd></div>
        <div><dt>PDC</dt><dd>{{ $tc->pdc_check === null ? 'No' : 'Yes' }}</dd></div>
        <div><dt>Invoice generated</dt><dd>{{ $tc->invoice_check === null ? 'No' : 'Yes' }}</dd></div>
        <div><dt>Registered in municipality</dt><dd>{{ $tc->tenant_contract_is_reg_municipality === null ? 'No' : 'Yes' }}
          @if($tc->tenant_contract_muncipality_agr_no)<span class="muted" style="font-weight:400;">&middot; {{ $tc->tenant_contract_muncipality_agr_no }}</span>@endif</dd></div>
        <div><dt>Contract registered in</dt><dd>{{ $tc->tenant_contract_registered_in ?: '-' }}</dd></div>
        @if($tc->tenant_contract_electric_water)<div><dt>Deposit electric / water</dt><dd>{{ $tc->tenant_contract_electric_water }}</dd></div>@endif
        @if($tc->last_rent)<div><dt>Rent paid by previous tenant</dt><dd class="tnum">{{ $omr($tc->last_rent) }}</dd></div>@endif
      </dl>
      @else
      <div class="lg-empty">No contract linked.</div>
      @endif
    </div>
    <div class="tab-pane lg-card__body" id="{{ $tabId }}_tenant" role="tabpanel">
      <dl class="lg-kv">
        <div><dt>Tenant</dt><dd>{{ $tc->tenant->tenant_name ?? '-' }}</dd></div>
        <div><dt>Tenant code</dt><dd>{{ $tc->tenant->tenant_code ?? '-' }}</dd></div>
        <div><dt>Building</dt><dd>{{ $legal->building->building_name ?? '-' }}</dd></div>
        <div><dt>Building code</dt><dd>{{ $legal->building->building_code ?? '-' }}</dd></div>
        <div><dt>Unit no.</dt><dd>{{ $legal->unit->unit_no ?? '-' }}</dd></div>
        <div><dt>Unit code</dt><dd>{{ $legal->unit->unit_code ?? '-' }}</dd></div>
        <div><dt>Unit usage</dt><dd>{{ $tc->unit_usage ?? '-' }}</dd></div>
        @if($tc && $tc->occupant_id && $tc->occupant)
        <div><dt>Occupant</dt><dd>{{ $tc->occupant->occupant_name }}</dd></div>
        <div><dt>Occupant mobile</dt><dd>{{ $tc->occupant->occupant_primary_contact_no ?: '-' }}</dd></div>
        <div><dt>Occupant email</dt><dd>{{ $tc->occupant->occupant_email ?: '-' }}</dd></div>
        @endif
      </dl>
    </div>
    <div class="tab-pane lg-card__body" id="{{ $tabId }}_payment" role="tabpanel">
      <dl class="lg-kv">
        <div><dt>Deposit rent amount</dt><dd class="tnum">{{ $tc && $tc->tenant_contract_deposit_amt !== null ? numberFormat($tc->tenant_contract_deposit_amt) : 'NA' }}</dd></div>
        <div><dt>Guarantee cheque</dt><dd>{{ $tc->tenant_contract_guarantee_cheque_details ?? 'NA' }}</dd></div>
        <div><dt>Receipt no.</dt><dd>{{ $tc->tenant_contract_receipt_no ?? 'NA' }}</dd></div>
        <div><dt>Receipt date</dt><dd>{{ $tc && $tc->tenant_contract_receipt_date ? $tc->tenant_contract_receipt_date->format('d/m/Y') : 'NA' }}</dd></div>
        <div><dt>Receipt amount</dt><dd class="tnum">{{ $tc && $tc->tenant_contract_receipt_amt !== null ? numberFormat($tc->tenant_contract_receipt_amt) : 'NA' }}</dd></div>
      </dl>
    </div>
    @if($hasOld)
    <div class="tab-pane" id="{{ $tabId }}_old" role="tabpanel">
      <div class="lg-table-wrap">
        <table class="lg-table">
          <thead><tr><th scope="col">Old contract</th><th scope="col">From</th><th scope="col">To</th><th scope="col" class="num"><span class="sr-only">Actions</span></th></tr></thead>
          <tbody>
            @forelse($oldContractList as $oldContract)
              @if($oldContract->tenant_contract_no != '')
              <tr>
                <td class="strong">{{ $oldContract->tenant_contract_no }}</td>
                <td class="tnum">{{ $d($oldContract->tenant_contract_start_date) }}</td>
                <td class="tnum">{{ $d($oldContract->tenant_contract_valid_to_date) }}</td>
                <td class="actions">
                  <a href="{{ route('legalPdcShow', $oldContract->id) }}" class="lg-btn lg-btn--ghost lg-btn--sm">Cheques</a>
                  <a href="{{ route('legaltenantContractShow', $oldContract->id) }}" class="lg-btn lg-btn--secondary lg-btn--sm">View</a>
                </td>
              </tr>
              @endif
            @empty
              <tr><td colspan="4"><div class="lg-empty">No older contracts for this tenant and unit.</div></td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @endif
  </div>
</section>
