@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

@section('content')
@php $LC = \Modules\Masters\Services\LegalCase::class; @endphp
<div class="lg">
  <div class="lg-crumbs">{{ Breadcrumbs::render('handedOverLegalCases') }}</div>
  <header class="lg-page-head">
    <div>
      <h1 class="lg-page-head__title">Handed-over cases</h1>
      <div class="lg-page-head__sub">Cases PLM handed to the landlord, the government or MERA <span class="sep">/</span> <span class="tnum">{{ $cases->total() }}</span> cases</div>
    </div>
  </header>
  <section class="lg-card" aria-label="Handed-over cases">
    <form method="GET" action="{{ route('handedOverLegalCases') }}" class="lg-filters" aria-label="Filter handed-over cases">
      <div class="lg-field"><label for="ho_to" class="lg-label">Handed to</label>
        <select id="ho_to" name="handled_by" class="lg-select"><option value="">All</option>
          @foreach([2, 3, 4] as $k)<option value="{{ $k }}" @if((int) $request->handled_by === $k) selected @endif>{{ $LC::HANDLED_BY[$k] }}</option>@endforeach</select></div>
      <div class="lg-field lg-field--wide" style="max-width:420px;flex-basis:320px;"><label for="ho_search" class="lg-label">Search</label>
        <input type="search" id="ho_search" name="search" class="lg-input" value="{{ $request->search }}" placeholder="Agreement, tenant, building, handed to or reference"></div>
      <div class="lg-filters__actions">
        <a href="{{ route('handedOverLegalCases') }}" class="lg-btn lg-btn--ghost">Reset</a>
        <button type="submit" class="lg-btn lg-btn--primary">Search</button>
      </div>
    </form>
    <div class="lg-table-wrap">
      <table class="lg-table">
        <thead>
          <tr><th scope="col">Agreement</th><th scope="col">Tenant</th><th scope="col">Building</th><th scope="col">Unit</th>
            <th scope="col">Handed to</th><th scope="col">Name</th><th scope="col">Reference</th><th scope="col">Handover date</th><th scope="col"><span class="sr-only">Actions</span></th></tr>
        </thead>
        <tbody>
          @forelse($cases as $case)
          <tr>
            <td><a class="lg-row-link" href="{{ route('legal.case', $case->id) }}">{{ $case->tenantContract->tenant_contract_no ?? '' }}</a><div class="muted" style="font-size:12px;">#{{ $case->id }}</div></td>
            <td class="strong">{{ $case->tenantContract->tenant->tenant_name ?? '' }}</td>
            <td>{{ $case->building->building_name ?? '' }}</td>
            <td>{{ $case->unit->unit_code ?? '' }}</td>
            <td><span class="lg-badge lg-badge--neutral">{{ $LC::HANDLED_BY[$case->handled_by] ?? '' }}</span></td>
            <td>{{ $case->handed_to_name }}</td>
            <td>{{ $case->handover_ref }}</td>
            <td class="tnum">{{ $case->handover_date ? \Carbon\Carbon::parse($case->handover_date)->format('d/m/Y') : '' }}</td>
            <td class="actions"><a href="{{ route('legal.case', $case->id) }}" class="lg-btn lg-btn--secondary lg-btn--sm">Open</a></td>
          </tr>
          @empty
          <tr><td colspan="9"><div class="lg-empty"><div class="lg-empty__title">No handed-over cases</div>Nothing matches the filters.</div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($cases->hasPages())<div class="lg-pager"><span>Page {{ $cases->currentPage() }} of {{ $cases->lastPage() }}</span>{{ $cases->links() }}</div>@endif
  </section>
</div>
@endsection
