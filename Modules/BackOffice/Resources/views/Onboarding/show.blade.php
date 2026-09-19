@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
  .onb { --onb-ink:#0f172a; --onb-muted:#475569; --onb-line:#e2e8f0; --onb-fill:#f1f5f9; --onb-accent:#FF9800; }
  .onb .onb-head { display:flex; flex-wrap:wrap; gap:16px 24px; align-items:flex-start; justify-content:space-between; margin-bottom:16px; }
  .onb .onb-head .onb-title { font-size:22px; font-weight:800; margin:0; line-height:1.3; }
  .onb .onb-head .onb-sub { color:var(--onb-muted); font-size:14px; margin-top:4px; }
  .onb .onb-id { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:16px; margin-bottom:16px; }
  .onb .onb-id-tile { border:1px solid var(--onb-line); border-radius:8px; padding:16px; background:#fff; min-width:0; }
  .onb .onb-id-tile .k { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--onb-muted); margin-bottom:8px; }
  .onb .onb-id-tile .big { display:block; font-size:18px; line-height:1.3; font-weight:800; color:var(--onb-ink); word-break:break-word; }
  .onb .onb-id-tile .meta { display:block; font-size:13px; color:var(--onb-muted); margin-top:4px; }
  .onb .onb-id-tile.primary { border-color:var(--onb-accent); background:#fff7ed; }
  @media (max-width: 900px) { .onb .onb-id { grid-template-columns:1fr 1fr; } }
  .onb .onb-status { display:inline-block; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:600; text-transform:uppercase; }
  .onb .onb-status-open { background:#fef3c7; color:#92400e; } .onb .onb-status-completed { background:#dbeafe; color:#1e40af; }
  .onb .onb-status-verified { background:#dcfce7; color:#166534; } .onb .onb-status-cancelled { background:#f1f5f9; color:#475569; }
  .onb .onb-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
  .onb .onb-actions .btn { height:40px; display:inline-flex; align-items:center; }
  .onb .onb-actions form { display:inline-flex; gap:8px; align-items:center; margin:0; }
  .onb .onb-actions select.form-control { height:40px; width:auto; }
  .onb table.product-overview th { font-size:12px; font-weight:500; text-transform:uppercase; letter-spacing:.04em; color:var(--onb-muted); }
  .onb table.product-overview td { padding:10px 14px; vertical-align:top; }
  .onb tr.group-row td { background:var(--onb-fill); font-weight:600; }
  .onb .st { white-space:nowrap; font-weight:600; }
  .onb .st-done { color:#166534; } .onb .st-na { color:#475569; } .onb .st-missing { color:#991b1b; }
  .onb .onb-photos { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
  .onb .onb-photos img { width:90px; height:68px; object-fit:cover; border:1px solid var(--onb-line); border-radius:6px; }
  .onb .onb-sign img { max-height:120px; border:1px solid var(--onb-line); border-radius:6px; background:#fff; }
  .onb .onb-progress { font-size:14px; color:var(--onb-muted); }
  .onb h4 { font-size:18px; font-weight:600; margin:24px 0 12px; }
</style>
@endsection

@section('content')
@php
  $c = $onboarding->tenantContract;
  $tenant = optional($c)->tenant;
  $groups = collect($detail['items'])->groupBy('group');
  $canVerify = auth()->user()->can('onboarding_verify');
@endphp
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Onboarding</div>
        </div>
        {{ Breadcrumbs::render('tenant-onboarding.show', $onboarding) }}
    </div>
</div>

@include('includes.success')
@include('includes.error')

<div class="row">
  <div class="col-md-12 col-sm-12">
    <div class="card card-box onb">
      <div class="card-body">

        <div class="onb-head">
          <div>
            <h3 class="onb-title">{{ optional($tenant)->tenant_name ?: 'Tenant' }}</h3>
            <div class="onb-sub">{{ optional($tenant)->tenant_contact_no ?: optional($tenant)->tenant_residence_tel }} &middot; Contract {{ optional($c)->tenant_contract_no }} from {{ optional(optional($c)->tenant_contract_start_date)->format('d/m/Y') }}</div>
            <div class="onb-sub" style="margin-top:8px"><span class="onb-status onb-status-{{ $onboarding->status }}">{{ $onboarding->status }}</span> <span class="onb-progress">{{ $detail['progress']['done'] }} / {{ $detail['progress']['total'] }} items</span></div>
          </div>
          <div class="onb-actions">
            @if($onboarding->status !== 'open')
              <a href="{{ route('tenant-onboarding.pdf', $onboarding->id) }}" target="_blank" rel="noopener" class="btn btn-default"><i class="fa fa-print"></i>&nbsp; PDF</a>
            @endif
            @if($canVerify && $onboarding->status === 'completed')
              <form method="post" action="{{ route('tenant-onboarding.verify', $onboarding->id) }}">@csrf<button type="submit" class="btn btn-success"><i class="fa fa-check"></i>&nbsp; Verify</button></form>
            @endif
            @if($canVerify && $onboarding->status === 'open')
              <form method="post" action="{{ route('tenant-onboarding.reassign', $onboarding->id) }}">@csrf
                <select name="user_id" class="form-control" required>
                  <option value="">Reassign to ARE...</option>
                  @foreach($ares as $u)<option value="{{ $u->id }}" {{ (int) $onboarding->assigned_user_id === $u->id ? 'selected' : '' }}>{{ optional($u->employee)->employee_name ?: $u->username }}</option>@endforeach
                </select>
                <button type="submit" class="btn btn-primary">Reassign</button>
              </form>
            @endif
          </div>
        </div>

        <div class="onb-id">
          <div class="onb-id-tile primary"><span class="k">Building</span><span class="big">{{ optional(optional($c)->building)->building_name }}</span></div>
          <div class="onb-id-tile"><span class="k">Unit</span><span class="big">{{ optional(optional($c)->unit)->unit_no ?: optional(optional($c)->unit)->unit_code }}</span></div>
          <div class="onb-id-tile"><span class="k">ARE</span><span class="big">{{ optional(optional($onboarding->assignedUser)->employee)->employee_name ?: (optional($onboarding->assignedUser)->username ?: '-') }}</span><span class="meta">Started {{ optional($onboarding->started_at)->format('d/m/Y H:i') ?: '-' }}</span></div>
          <div class="onb-id-tile"><span class="k">Completed</span><span class="big">{{ optional($onboarding->completed_at)->format('d/m/Y H:i') ?: '-' }}</span>
            @if($onboarding->verified_at)<span class="meta">Verified {{ $onboarding->verified_at->format('d/m/Y H:i') }} by {{ optional($onboarding->verifiedBy)->username }}</span>@endif</div>
        </div>

        <h4>Checklist</h4>
        <div class="table-responsive">
          <table class="table product-overview mb-30">
            <thead><tr><th>Item</th><th style="width:12%">Status</th><th style="width:20%">Value</th><th style="width:30%">Remark / photos</th></tr></thead>
            <tbody>
            @foreach($groups as $group => $rows)
              <tr class="group-row"><td colspan="4">{{ $group }}</td></tr>
              @foreach($rows as $r)
                @php $e = $r['entry']; @endphp
                <tr>
                  <td>{{ $r['title'] }} @if($r['required'])<small class="text-muted">(required)</small>@endif</td>
                  <td class="st">
                    @if($e && $e['not_applicable'])<span class="st-na">N/A</span>
                    @elseif($e && $e['done'])<span class="st-done"><i class="fa fa-check"></i> Done</span>
                    @else<span class="{{ $r['required'] ? 'st-missing' : '' }}">Pending</span>@endif
                  </td>
                  <td>{{ $e['value'] ?? '' }}</td>
                  <td>
                    {{ $e['remark'] ?? '' }}
                    @if(count($r['photos']))
                      <div class="onb-photos">
                        @foreach($r['photos'] as $p)
                          <a href="{{ asset('storage/app/' . $p['path']) }}" target="_blank" rel="noopener"><img src="{{ asset('storage/app/' . ($p['thumb'] ?: $p['path'])) }}" alt="{{ $r['title'] }} photo" loading="lazy"></a>
                        @endforeach
                      </div>
                    @endif
                  </td>
                </tr>
              @endforeach
            @endforeach
            </tbody>
          </table>
        </div>

        @if($onboarding->tenant_remark)
          <h4>Tenant remark</h4>
          <p>{{ $onboarding->tenant_remark }}</p>
        @endif

        <h4>Tenant signature</h4>
        <div class="onb-sign">
          @if($onboarding->tenant_signature)
            <img src="{{ asset('storage/app/' . $onboarding->tenant_signature) }}" alt="Tenant signature">
          @else
            <span class="text-muted">Not signed yet</span>
          @endif
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
