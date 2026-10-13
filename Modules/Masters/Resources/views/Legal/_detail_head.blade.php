{{-- Page head of a legal case detail screen (component). Needs $legal, $crumb (+ optional $crumbParam), $screen (label);
     slot $actions = buttons on the right. --}}
@php
    $tc = $legal->tenantContract;
    $code = (int) $legal->work_flow_processes_code;
    $state = $legal->closed_at ? 'Closed' : ($code === 801 ? 'Waiting for PLM approval' : ($code === 802 ? 'Waiting for lawyer approval' : ($code === 803 ? 'Active' : 'Referred back')));
    $tone = $legal->closed_at ? 'neutral' : ($code === 803 ? 'success' : ($code === 804 ? 'danger' : 'warning'));
@endphp
<div class="lg-crumbs">{{ isset($crumbParam) ? Breadcrumbs::render($crumb, $crumbParam) : Breadcrumbs::render($crumb) }}</div>
<header class="lg-page-head">
  <div>
    <h1 class="lg-page-head__title">{{ $tc->tenant_contract_no ?? '' }} &middot; {{ $tc->tenant->tenant_name ?? '' }}</h1>
    <div class="lg-page-head__sub">
      <span>{{ $screen }}</span><span class="sep">/</span>
      <span>{{ $legal->building->building_name ?? '' }}</span><span class="sep">/</span>
      <span>Unit {{ $legal->unit->unit_code ?? '' }}</span><span class="sep">/</span>
      <span>Case #{{ $legal->id }}</span>
      <span class="lg-badge lg-badge--{{ $tone }}"><span class="lg-dot"></span>{{ $state }}</span>
    </div>
  </div>
  <div class="lg-page-head__actions">
    {{ $actions ?? '' }}
    <a class="lg-btn lg-btn--secondary" href="{{ route('legal.case', $legal->id) }}"><i class="fa fa-gavel" aria-hidden="true"></i> Case page</a>
  </div>
</header>
