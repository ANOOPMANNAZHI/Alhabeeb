@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
  /* Vacating-unit inspection: trust blue on neutral, tabular numerals, 44px targets. */
  .insp { --i-blue:#2563eb; --i-blue-soft:#eff6ff; --i-ink:#1f2937; --i-muted:#6b7280; --i-line:#e5e7eb; --i-bg:#f9fafb; --i-danger:#dc2626; --i-ok:#16a34a; --i-warn:#d97706; --i-radius:8px; --i-tap:44px; color:var(--i-ink); }
  .insp .card-box { border-radius: var(--i-radius); }
  .insp .card-box:hover { transform:none; }

  /* Snapshot */
  .insp .i-snap { display:grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px 24px; padding: 6px 0 4px; }
  .insp .i-snap-item .i-lbl { display:block; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--i-muted); margin-bottom:2px; }
  .insp .i-snap-item .i-val { font-size:14px; font-weight:600; word-break:break-word; }
  .insp .i-pill { display:inline-block; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
  .insp .i-pill-ok { background:transparent; border:0; color:#166534; padding-left:0; padding-right:0; } .insp .i-pill-warn { background:transparent; border:0; color:#991b1b; padding-left:0; padding-right:0; }
  .insp .i-head { display:flex; flex-wrap:wrap; align-items:center; gap:10px; padding:14px 18px 6px; }
  .insp .i-head .i-title { font-size:18px; font-weight:800; margin:0; }
  .insp .i-head .i-sub { color:var(--i-muted); font-size:13px; }
  .insp .i-head .i-actions { margin-left:auto; display:flex; gap:8px; }
  .insp details.i-more { border-top:1px solid var(--i-line); margin-top:12px; }
  .insp details.i-more > summary { list-style:none; cursor:pointer; padding:12px 0 4px; font-weight:600; color:var(--i-blue); font-size:13px; display:flex; align-items:center; gap:6px; min-height:var(--i-tap); }
  .insp details.i-more > summary::-webkit-details-marker { display:none; }
  .insp details.i-more > summary::before { content:"▸"; font-size:12px; transition:transform .15s; }
  .insp details.i-more[open] > summary::before { transform:rotate(90deg); }
  .insp .i-more table th { background:var(--i-bg); font-size:12px; text-transform:uppercase; letter-spacing:.03em; color:var(--i-muted); }

  /* Tabs */
  .insp .i-tabs { border-bottom:2px solid var(--i-line); margin:0 0 16px; flex-wrap:wrap; gap:4px; }
  .insp .i-tabs .nav-item { margin-bottom:-2px; }
  .insp .i-tabs .i-tab { display:inline-flex; align-items:center; gap:8px; min-height:var(--i-tap); padding:8px 16px; border:0; border-bottom:3px solid transparent; border-radius:8px 8px 0 0; color:var(--i-muted); font-weight:600; font-size:14px; background:transparent; white-space:nowrap; }
  .insp .i-tabs .i-tab:hover, .insp .i-tabs .i-tab:focus { color:var(--i-blue); background:var(--i-blue-soft); text-decoration:none; }
  .insp .i-tabs .i-tab.active { color:var(--i-blue); border-bottom-color:var(--i-blue); background:#fff; }
  .insp .i-tab-no { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:var(--i-line); color:var(--i-muted); font-size:12px; font-weight:700; }
  .insp .i-tab.active .i-tab-no, .insp .i-tab.done .i-tab-no { background:var(--i-blue); color:#fff; }
  .insp .i-tab-badge { display:inline-block; min-width:20px; padding:1px 7px; border-radius:999px; background:var(--i-blue); color:#fff; font-size:11px; font-weight:700; line-height:18px; text-align:center; }
  .insp .i-tab-badge-muted { background:var(--i-line); color:var(--i-muted); }
  .insp .i-tab-dot { width:8px; height:8px; border-radius:50%; background:var(--i-danger); display:none; }
  .insp .i-tab-dot.show { display:inline-block; }
  .insp .i-pane { display:none; }
  .insp .i-pane.active { display:block; }
  .insp .i-pane-nav { display:flex; justify-content:space-between; gap:10px; margin-top:16px; }
  .insp .i-pane-nav .btn { min-height:var(--i-tap); display:inline-flex; align-items:center; gap:6px; }

  /* Sections */
  .insp .i-section-head { display:flex; align-items:center; gap:10px; font-size:15px; font-weight:700; border-left:4px solid var(--i-blue); padding:8px 14px; margin:22px 0 12px; background:var(--i-blue-soft); border-radius:6px; }
  .insp .i-section-head:first-child { margin-top:0; }
  .insp .i-step-no { display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; background:var(--i-blue); color:#fff; font-size:12px; font-weight:700; }
  .insp .i-section-head .i-hint { margin-left:auto; font-size:12px; font-weight:500; color:var(--i-muted); }
  .insp .i-field label { display:block; font-size:12px; font-weight:600; color:var(--i-muted); margin-bottom:4px; }
  .insp .form-control { min-height:var(--i-tap); border-radius:6px; }
  .insp .form-control:focus { border-color:var(--i-blue); box-shadow:0 0 0 3px rgba(37,99,235,.15); }
  .insp .form-control[readonly] { background:var(--i-bg); color:var(--i-muted); }
  .insp .i-help { font-size:12px; color:var(--i-muted); margin-top:4px; }
  .insp .num { text-align:right; font-variant-numeric:tabular-nums; }
  .insp .i-error { color:var(--i-danger); font-size:12px; margin-top:4px; }
  .insp .form-group.has-error .form-control { border-color:var(--i-danger); }
  .insp label.error { color:var(--i-danger); font-size:12px; font-weight:500; display:block; margin-top:4px; }

  /* Checklist */
  .insp .i-pills { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px; }
  .insp .i-pill-btn { display:inline-flex; align-items:center; gap:8px; min-height:40px; padding:6px 14px; border-radius:999px; border:1px solid var(--i-line); background:#fff; color:var(--i-muted); font-weight:600; font-size:13px; cursor:pointer; }
  .insp .i-pill-btn:hover { border-color:var(--i-blue); color:var(--i-blue); }
  .insp .i-pill-btn.active { background:var(--i-blue); border-color:var(--i-blue); color:#fff; }
  .insp .i-pill-btn:focus-visible { box-shadow:0 0 0 3px rgba(37,99,235,.3); outline:none; }
  .insp .i-pill-n { display:inline-block; min-width:20px; padding:0 6px; border-radius:999px; background:var(--i-line); color:var(--i-muted); font-size:11px; line-height:18px; text-align:center; }
  .insp .i-pill-btn.active .i-pill-n { background:rgba(255,255,255,.25); color:#fff; }
  .insp .i-pill-btn.has .i-pill-n { background:var(--i-blue); color:#fff; }
  .insp .i-pill-btn.active.has .i-pill-n { background:#fff; color:var(--i-blue); }
  .insp .i-cat.cat-hide { display:none; }
  .insp .i-toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:12px; }
  .insp .i-toolbar input { max-width:320px; }
  .insp .i-toolbar .i-count { font-size:13px; color:var(--i-muted); }
  .insp .i-toolbar .i-count b { color:var(--i-ink); }
  .insp .i-cat { border:1px solid var(--i-line); border-radius:var(--i-radius); margin-bottom:14px; overflow:hidden; }
  .insp .i-cat-head { display:flex; align-items:center; gap:10px; padding:10px 14px; background:var(--i-bg); font-weight:700; }
  .insp .i-cat-head .i-cat-sum { margin-left:auto; font-size:12px; font-weight:600; color:var(--i-muted); font-variant-numeric:tabular-nums; }
  .insp .i-cat-head .i-cat-sum.active { color:var(--i-blue); }
  .insp table.i-check { margin:0; }
  .insp table.i-check th { font-size:12px; text-transform:uppercase; letter-spacing:.03em; color:var(--i-muted); border-top:0; padding:8px 12px; }
  .insp table.i-check td { vertical-align:middle; padding:6px 12px; }
  .insp table.i-check tr.picked td { background:#f5f9ff; }
  .insp table.i-check tr.hide { display:none; }
  .insp .i-tick { position:relative; display:inline-flex; align-items:center; justify-content:center; width:var(--i-tap); height:var(--i-tap); cursor:pointer; margin:0; }
  .insp .i-tick input { position:absolute; opacity:0; width:0; height:0; }
  .insp .i-tick .i-box { width:22px; height:22px; border:2px solid #cbd5e1; border-radius:6px; background:#fff; transition:all .15s; }
  .insp .i-tick input:checked + .i-box { background:var(--i-blue); border-color:var(--i-blue); }
  .insp .i-tick input:checked + .i-box::after { content:""; position:absolute; left:15px; top:11px; width:7px; height:13px; border:solid #fff; border-width:0 3px 3px 0; transform:rotate(45deg); }
  .insp .i-tick input:focus-visible + .i-box { box-shadow:0 0 0 3px rgba(37,99,235,.3); }
  .insp .i-item { font-weight:600; }
  .insp .i-desc { min-height:38px !important; }
  .insp table.i-check .form-control { min-height:38px; }
  .insp .i-empty-cat { padding:12px 14px; color:var(--i-muted); font-size:13px; }

  /* Switch */
  .insp .i-field label.i-switch, .insp .i-switch { position:relative; display:inline-flex; align-items:center; gap:12px; cursor:pointer; min-height:var(--i-tap); font-size:14px; font-weight:600; color:var(--i-ink); margin:0; }
  .insp .i-switch input { position:absolute; opacity:0; width:0; height:0; }
  .insp .i-switch .i-track { display:inline-block; flex:0 0 46px; width:46px; height:26px; border-radius:999px; background:#cbd5e1; position:relative; transition:background .15s; }
  .insp .i-switch .i-track::after { content:""; position:absolute; top:3px; left:3px; width:20px; height:20px; border-radius:50%; background:#fff; transition:left .15s; box-shadow:0 1px 2px rgba(0,0,0,.2); }
  .insp .i-switch input:checked + .i-track { background:var(--i-ok); }
  .insp .i-switch input:checked + .i-track::after { left:23px; }
  .insp .i-switch input:focus-visible + .i-track { box-shadow:0 0 0 3px rgba(37,99,235,.3); }

  /* Sticky summary */
  .insp .i-summary { position:fixed; bottom:0; left:0; right:0; z-index:1020; background:#fff; border-top:2px solid var(--i-line); box-shadow:0 -6px 16px rgba(0,0,0,.06); padding:12px 16px; display:flex; flex-wrap:wrap; gap:12px 28px; align-items:center; }
  .insp .i-summary .i-lbl { display:block; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--i-muted); }
  .insp .i-summary .i-sum-val { font-size:15px; font-weight:600; font-variant-numeric:tabular-nums; }
  .insp .i-summary .i-net { font-size:22px; font-weight:800; color:var(--i-blue); font-variant-numeric:tabular-nums; }
  .insp .i-summary .i-discount { width:130px; min-height:38px; }
  .insp .i-summary .i-sum-actions { margin-left:auto; text-align:right; }
  .insp .i-summary .i-sum-note { display:block; font-size:12px; color:var(--i-muted); margin-top:4px; }
  .insp #insp_save { min-height:var(--i-tap); min-width:170px; font-weight:700; }
  .insp.has-fixed-summary { padding-bottom:140px; }
  .insp .i-summary { box-sizing:border-box; }

  /* Photos (modal) */
  #insp_photo_modal .modal-content { border-radius:10px; }
  #insp_photo_modal .modal-header { align-items:center; }
  #insp_photo_modal .modal-title .i-sub { display:block; font-size:12px; color:#6b7280; font-weight:400; margin-top:2px; }
  #insp_photo_modal .dataSearchBox { padding:0; }
  .modal { z-index:1050; } .modal-backdrop { z-index:1040; }
  .insp .i-photo-form { display:grid; grid-template-columns: 1fr 1.4fr auto; gap:12px; align-items:end; }
  @media (max-width: 640px) { .insp .i-photo-form { grid-template-columns:1fr; } .insp .i-summary .i-sum-actions { margin-left:0; width:100%; } }
  .insp .i-photos { display:flex; flex-wrap:wrap; gap:12px; }
  .insp .i-photo { width:150px; border:1px solid var(--i-line); border-radius:var(--i-radius); overflow:hidden; background:#fff; }
  .insp .i-photo img { width:100%; height:100px; object-fit:cover; display:block; background:var(--i-bg); }
  .insp .i-photo .i-photo-meta { padding:6px 8px; font-size:12px; }
  .insp .i-photo .i-photo-meta .i-cat-tag { color:var(--i-muted); display:block; }
  .insp .i-photo .i-photo-meta a { word-break:break-all; }
  .insp .i-alert { border:1px solid #fecaca; background:#fef2f2; color:#991b1b; border-radius:6px; padding:10px 14px; margin-bottom:12px; font-size:13px; }
</style>
@endsection

@section('content')
@php
  $tc   = $tenantContract;
  $hasInspection = count($tc->terminationChecklist) > 0;
  $fmtDate = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : 'NA'; };
  $duration = null;
  if ($tc->tenant_contract_duration_countdown) {
      $p = explode('-', $tc->tenant_contract_duration_countdown);
      $duration = (isset($p[0]) ? $p[0] . ' Year ' : '') . (isset($p[1]) ? $p[1] . ' Month ' : '') . (isset($p[2]) ? $p[2] . ' Days' : '');
  }
@endphp
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left">
      <div class="page-title">Vacating Unit Inspection Details</div>
    </div>
    {{ Breadcrumbs::render('handoverAssignedInspection', $termination) }}
  </div>
</div>

<div class="row insp">
<div class="col-sm-12">


  {{-- ── Contract snapshot ─────────────────────────────────────────── --}}
  <div class="card card-box">
    <div class="i-head">
      <div>
        <h3 class="i-title">{{ $tc->tenant_contract_no }} <small class="i-sub">· {{ $tc->tenant->tenant_name }}</small></h3>
        <div class="i-sub">{{ $tc->building->building_name }} · Unit {{ $tc->unit->unit_no }} · {{ optional($tc->unit->unit)->unit_types_name }}</div>
      </div>
      <div class="i-actions">
        <button type="button" class="btn btn-circle btn-primary" data-toggle="modal" data-target="#insp_photo_modal"><i class="fa fa-camera" aria-hidden="true"></i> Photos <span class="i-tab-badge i-tab-badge-muted" id="insp_photo_count">{{ count($terminationDocument) }}</span></button>
        @if($outstandingOs > 0)
          <span class="i-pill i-pill-warn" title="Outstanding rent {{ numberFormat($outstandingOs) }} OMR">Outstanding {{ numberFormat($outstandingOs) }} OMR</span>
        @else
          <span class="i-pill i-pill-ok">No outstanding</span>
        @endif
        @if($termination->termination_review_status == 2)
        @can('termination_taken_over')
        <a href="{{ route('tenantTerminationStage', [$termination->id, $termination->tenantContract->id, $termination->work_flow_processes_code, 'ACPT']) }}" class="btn btn-circle btn-primary" title="TakenOver">TakeOver</a>
        @endcan
        @endif
      </div>
    </div>
    <div class="card-body">
      <div class="i-snap">
        <div class="i-snap-item"><span class="i-lbl">Mobile</span><span class="i-val">{{ $tc->tenant->tenant_contact_no ?: 'NA' }}</span></div>
        <div class="i-snap-item"><span class="i-lbl">Building No</span><span class="i-val">{{ $tc->building->building_no ?: 'NA' }}</span></div>
        <div class="i-snap-item"><span class="i-lbl">Location</span><span class="i-val">{{ optional(optional($tc->building)->location)->locations_name ?? 'NA' }}</span></div>
        <div class="i-snap-item"><span class="i-lbl">Way No</span><span class="i-val">{{ $tc->building->building_address ?? 'NA' }}</span></div>
        <div class="i-snap-item"><span class="i-lbl">Tenancy start</span><span class="i-val">{{ $fmtDate(optional($tenancyStartDt)->tenant_contract_start_date) }}</span></div>
        <div class="i-snap-item"><span class="i-lbl">Tenancy end</span><span class="i-val">{{ $fmtDate($tc->tenant_contract_valid_to_date) }}</span></div>
        <div class="i-snap-item"><span class="i-lbl">Last paid up to</span><span class="i-val">{{ $fmtDate($tc->tenant_contract_last_paid_date) }}</span></div>
        @if($duration)<div class="i-snap-item"><span class="i-lbl">Total duration</span><span class="i-val">{{ $duration }}</span></div>@endif
        <div class="i-snap-item"><span class="i-lbl">Termination date</span><span class="i-val">{{ $fmtDate($termination->termination_date) }}</span></div>
        <div class="i-snap-item"><span class="i-lbl">Taken over date</span><span class="i-val">{{ $fmtDate($termination->termination_takenover_date) }}</span></div>
        <div class="i-snap-item"><span class="i-lbl">Prev. deposit cheque</span><span class="i-val">{{ !empty($depositeCheque) ? $depositeCheque->pdc_check_no . ' · ' . numberFormat($depositeCheque->pdc_amt) . ' OMR' : 'N/A' }}</span></div>
        <div class="i-snap-item"><span class="i-lbl">Remark</span><span class="i-val">{{ $termination->termination_remark ?? 'NA' }}</span></div>
      </div>

      <details class="i-more">
        <summary>Previous contracts &amp; open-for-termination documents</summary>
        <div class="table-responsive" style="margin-top:8px">
          <table class="table">
            <thead><tr><th>Agreement No</th><th>Name</th><th>Building</th><th>Unit</th><th>Start</th><th>End</th><th class="num">Rent</th><th class="num">OS</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($backHistory as $contract)
              <tr>
                <td>{{ $contract->tenant_contract_no }}</td>
                <td>{{ $contract->tenant->tenant_name }}</td>
                <td>{{ $contract->building->building_name }}</td>
                <td>{{ $contract->unit->unit_no }}</td>
                <td>{{ $contract->tenant_contract_start_date->format('d/m/Y') }}</td>
                <td>{{ $contract->tenant_contract_valid_to_date->format('d/m/Y') }}</td>
                <td class="num">{{ numberFormat($contract->tenant_contract_rent) }}</td>
                <td class="num">{{ numberFormat($contract->tenant_contract_os) }}</td>
                <td style="white-space:nowrap">
                  <a target="_blank" href="{{ route('tenant-contract.show', $contract->id) }}" class="btn btn-tbl-view btn-xs" title="View"><i class="fa fa-eye"></i></a>
                  @if($contract->invoice_check != null)
                  <a target="_blank" href="{{ route('invoice.show', $contract->id) }}" class="btn btn-tbl-view btn-xs" title="Invoice"><i class="fa fa-files-o"></i></a>
                  @endif
                  <a target="_blank" href="{{ route('pdcView', $contract->id) }}" class="btn btn-tbl-view btn-xs" title="PDC"><i class="fa fa-book"></i></a>
                  <a target="_blank" href="{{ route('rentReceiptFromTermination', [$contract->id]) }}" class="btn btn-tbl-view btn-xs" title="Receipt"><i class="fa fa-files-o"></i></a>
                </td>
              </tr>
            @empty
              <tr><td colspan="9" align="center">No Record</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th style="width:60px">#</th><th>Open-for-termination document</th></tr></thead>
            <tbody>
            @forelse($openTerminationDocument as $document)
              <tr><td>{{ $loop->iteration }}</td><td><a href="{{ asset('storage/app/' . $document->termination_doc) }}" target="_blank">{{ $document->termination_doc_name }}</a></td></tr>
            @empty
              <tr><td colspan="2" align="center">No Record</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </details>
    </div>
  </div>

  <ul class="nav nav-tabs i-tabs" role="tablist" id="insp_tabs">
    @if($hasInspection)
    <li class="nav-item"><a href="#pane_details" class="i-tab" role="tab"><i class="fa fa-check-square-o" aria-hidden="true"></i> Inspection details</a></li>
    @else
    <li class="nav-item"><a href="#pane_readings" class="i-tab" role="tab"><span class="i-tab-no">1</span> Meter readings</a></li>
    <li class="nav-item"><a href="#pane_checklist" class="i-tab" role="tab"><span class="i-tab-no">2</span> Checklist <span class="i-tab-badge" id="tab_badge_checklist" hidden>0</span></a></li>
    <li class="nav-item"><a href="#pane_other" class="i-tab" role="tab"><span class="i-tab-no">3</span> Other charges <span class="i-tab-badge" id="tab_badge_other" hidden>0</span></a></li>
    <li class="nav-item"><a href="#pane_notes" class="i-tab" role="tab"><span class="i-tab-no">4</span> Handover &amp; notes <span class="i-tab-dot" id="tab_dot_notes" title="Notes required"></span></a></li>
    @endif
  </ul>

<div class="insp-panes">

@if($hasInspection)
  {{-- ── Read-only: inspection already recorded ──────────────────── --}}
  @php $inspection = \Modules\BackOffice\Entities\Termination::where('contract_id', $termination->contract_id)->whereNotNull('termination_total_amount')->orderBy('id', 'desc')->first() ?: $termination; @endphp
  <div class="i-pane" id="pane_details">
  @include('backoffice::Termination._inspection_details')
  </div>{{-- /pane_details --}}
@else
  {{-- ── Inspection form ───────────────────────────────────────────── --}}
  <form id="save_inspection" method="POST" action="{{ route('terminationInspectionStore') }}" class="form-horizontal" autocomplete="off">
    {{ csrf_field() }}
    <input type="hidden" name="terminationId" value="{{ $termination->id }}">
    <input type="hidden" name="work_flow_processes_code" value="{{ $termination->work_flow_processes_code }}">
    <input type="hidden" name="contract_id" value="{{ $termination->contract_id }}">
    <input type="hidden" name="other_count" value="3">

    <div class="card card-box salesSearchBox">
      @if($errors->any())
      <div class="i-alert" role="alert" tabindex="-1" id="insp_errors">
        <strong>Please fix the following:</strong>
        <ul style="margin:4px 0 0 18px;padding:0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
      @endif

      <div class="i-pane" id="pane_readings">
      {{-- 1. Meter readings --}}
      <div class="i-section-head"><span class="i-step-no">1</span> Meter readings <span class="i-hint">Leave blank if not applicable</span></div>
      <div class="dataSearchBox">
        <div class="row">
          <div class="col-md-6">
            <div class="i-cat">
              <div class="i-cat-head"><i class="fa fa-bolt" aria-hidden="true"></i> Electricity</div>
              <div style="padding:12px 14px" class="row">
                <div class="col-sm-4 i-field"><label for="elec_acc">Account No</label><input type="text" id="elec_acc" name="elec_acc" class="form-control" readonly value="{{ old('elec_acc', $tc->unit->unit_electric_consumer_no ?? '') }}"></div>
                <div class="col-sm-4 i-field"><label for="elec_cls_read">Closing reading</label><input type="text" id="elec_cls_read" name="elec_cls_read" class="form-control" value="{{ old('elec_cls_read') }}"></div>
                <div class="col-sm-4 i-field"><label for="elec_amt">Amount (OMR)</label><input type="text" inputmode="decimal" id="elec_amt" name="elec_amt" class="form-control num i-money i-elec-water" value="{{ old('elec_amt') }}" placeholder="0.000"></div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="i-cat">
              <div class="i-cat-head"><i class="fa fa-tint" aria-hidden="true"></i> Water</div>
              <div style="padding:12px 14px" class="row">
                <div class="col-sm-4 i-field"><label for="water_acc">Account No</label><input type="text" id="water_acc" name="water_acc" class="form-control" readonly value="{{ old('water_acc', $tc->unit->unit_water_consumer_no ?? '') }}"></div>
                <div class="col-sm-4 i-field"><label for="water_cls_read">Closing reading</label><input type="text" id="water_cls_read" name="water_cls_read" class="form-control" value="{{ old('water_cls_read') }}"></div>
                <div class="col-sm-4 i-field"><label for="water_amt">Amount (OMR)</label><input type="text" inputmode="decimal" id="water_amt" name="water_amt" class="form-control num i-money i-elec-water" value="{{ old('water_amt') }}" placeholder="0.000"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="i-pane-nav"><span></span><a href="#pane_checklist" class="btn btn-primary i-goto">Next: Checklist <i class="fa fa-arrow-right" aria-hidden="true"></i></a></div>
      </div>{{-- /pane_readings --}}

      <div class="i-pane" id="pane_checklist">
      {{-- 2. Damage & repair checklist --}}
      <div class="i-section-head"><span class="i-step-no">2</span> Damage &amp; repair checklist <span class="i-hint">Typing a quantity or amount ticks the item automatically</span></div>
      <div class="dataSearchBox">
        <div class="i-toolbar">
          <input type="search" id="insp_filter" class="form-control" placeholder="Filter items, e.g. tap, socket, door…" aria-label="Filter checklist items">
          <span class="i-count"><b id="insp_picked">0</b> items selected · <b id="insp_works_total">0.000</b> OMR</span>
        </div>
        <div class="i-pills" id="insp_pills" role="tablist" aria-label="Checklist categories">
          <button type="button" class="i-pill-btn" data-cat="all">All <span class="i-pill-n" data-pill-n="all">0</span></button>
          @foreach($works as $work)
            @if(count($work->subWork) > 0)
            <button type="button" class="i-pill-btn" data-cat="{{ $work->id }}">{{ $work->works_code }} <span class="i-pill-n" data-pill-n="{{ $work->id }}">0</span></button>
            @endif
          @endforeach
        </div>
        @php $i = 0; @endphp
        @foreach($works as $work)
          @if(count($work->subWork) > 0)
          <div class="i-cat" data-cat="{{ $work->id }}">
            <div class="i-cat-head">{{ $work->works_code }} <span class="i-cat-sum" data-cat-sum="{{ $work->id }}">0 selected</span></div>
            <div class="table-responsive">
              <table class="table i-check">
                <thead><tr><th style="width:56px"><span class="sr-only">Select</span></th><th>Item</th><th style="width:120px">Qty</th><th class="num" style="width:160px">Amount (OMR)</th></tr></thead>
                <tbody>
                @foreach($work->subWork as $subWork)
                  <tr class="i-row" data-name="{{ strtolower($subWork->sub_work) }}">
                    <td>
                      <label class="i-tick" for="insp_chk_{{ $i }}">
                        <input type="checkbox" id="insp_chk_{{ $i }}" name="addinspection{{ $i }}" value="{{ $subWork->id }}" class="i-chk" {{ old('addinspection' . $i) ? 'checked' : '' }}>
                        <span class="i-box"></span>
                        <span class="sr-only">Select {{ $subWork->sub_work }}</span>
                      </label>
                    </td>
                    <td class="i-item">{{ $subWork->sub_work }}</td>
                    <td><input type="text" inputmode="decimal" name="quantity_{{ $i }}" class="form-control num i-qty" value="{{ old('quantity_' . $i) }}" aria-label="Quantity for {{ $subWork->sub_work }}"></td>
                    <td><input type="text" inputmode="decimal" name="amount_{{ $i }}" class="form-control num i-money i-amt" value="{{ old('amount_' . $i) }}" placeholder="0.000" aria-label="Amount for {{ $subWork->sub_work }}"></td>
                  </tr>
                  @php $i++; @endphp
                @endforeach
                </tbody>
              </table>
            </div>
            <div class="i-empty-cat" style="display:none">No items match the filter.</div>
          </div>
          @endif
        @endforeach
        <input type="hidden" name="count" value="{{ max($i - 1, 0) }}">
      </div>
      <div class="i-pane-nav"><a href="#pane_readings" class="btn btn-default i-goto"><i class="fa fa-arrow-left" aria-hidden="true"></i> Meter readings</a><a href="#pane_other" class="btn btn-primary i-goto">Next: Other charges <i class="fa fa-arrow-right" aria-hidden="true"></i></a></div>
      </div>{{-- /pane_checklist --}}

      <div class="i-pane" id="pane_other">
      {{-- 3. Other charges --}}
      <div class="i-section-head"><span class="i-step-no">3</span> Other charges <span class="i-hint">Rent, municipal tax or anything not in the checklist</span></div>
      <div class="dataSearchBox">
        <div class="i-cat">
          <div class="table-responsive">
            <table class="table i-check" id="insp_other_table">
              <thead><tr><th style="width:56px"><span class="sr-only">Select</span></th><th>Charge</th><th style="width:120px">Qty</th><th class="num" style="width:160px">Amount (OMR)</th></tr></thead>
              <tbody>
              @foreach([['Others', true], ['Rent', false], ['Muncipal Tax', false], ['Any Other Charges', true]] as $j => $other)
                <tr class="i-row i-other-row">
                  <td>
                    <label class="i-tick" for="insp_other_{{ $j }}">
                      <input type="checkbox" id="insp_other_{{ $j }}" name="addinspectionOther_{{ $j }}" value="{{ old('addinspectionOther_' . $j, $other[0]) }}" class="i-chk i-other-chk" {{ old('addinspectionOther_' . $j) ? 'checked' : '' }}>
                      <span class="i-box"></span>
                      <span class="sr-only">Select {{ $other[0] }}</span>
                    </label>
                  </td>
                  <td class="i-item">
                    @if($other[1])
                      <input type="text" class="form-control i-desc i-other-desc" value="{{ old('addinspectionOther_' . $j, $other[0]) }}" data-default="{{ $other[0] }}" aria-label="Description" placeholder="{{ $other[0] }} — describe">
                    @else
                      {{ $other[0] === 'Muncipal Tax' ? 'Municipal Tax' : $other[0] }}
                    @endif
                  </td>
                  <td><input type="text" inputmode="decimal" name="addinspectionQuantity_{{ $j }}" class="form-control num i-qty" value="{{ old('addinspectionQuantity_' . $j) }}" aria-label="Quantity"></td>
                  <td><input type="text" inputmode="decimal" name="addinspectionAmount_{{ $j }}" class="form-control num i-money i-other-amt" value="{{ old('addinspectionAmount_' . $j) }}" placeholder="0.000" aria-label="Amount"></td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="i-pane-nav"><a href="#pane_checklist" class="btn btn-default i-goto"><i class="fa fa-arrow-left" aria-hidden="true"></i> Checklist</a><a href="#pane_notes" class="btn btn-primary i-goto">Next: Handover &amp; notes <i class="fa fa-arrow-right" aria-hidden="true"></i></a></div>
      </div>{{-- /pane_other --}}

      <div class="i-pane" id="pane_notes">
      {{-- 4. Key & notes --}}
      <div class="i-section-head"><span class="i-step-no">4</span> Handover &amp; notes</div>
      <div class="dataSearchBox">
        <div class="row">
          <div class="col-md-4 i-field">
            <label>Main door key</label>
            <label class="i-switch" for="switch-2">
              <input type="checkbox" id="switch-2" name="termination_main_key_status" value="1" {{ old('termination_main_key_status') ? 'checked' : '' }}>
              <span class="i-track"></span>
              <span id="insp_key_text">Not received</span>
            </label>
          </div>
          <div class="col-md-8 i-field {{ $errors->has('notes') ? 'has-error' : '' }}">
            <label for="notes">Inspection notes <small class="textRed">*</small></label>
            <textarea id="notes" name="notes" class="form-control" rows="3" required placeholder="Condition of the unit, items handed over, anything the tenant should know…">{{ old('notes') }}</textarea>
            @if($errors->has('notes'))<div class="i-error">{{ $errors->first('notes') }}</div>@endif
          </div>
        </div>
      </div>
      <div class="i-pane-nav"><a href="#pane_other" class="btn btn-default i-goto"><i class="fa fa-arrow-left" aria-hidden="true"></i> Other charges</a><button type="button" class="btn btn-default" data-toggle="modal" data-target="#insp_photo_modal">Add photos <i class="fa fa-camera" aria-hidden="true"></i></button></div>
      </div>{{-- /pane_notes --}}
    </div>{{-- /card --}}

      {{-- Sticky summary --}}
      <div class="i-summary">
        <div><span class="i-lbl">Checklist</span><span class="i-sum-val" id="sum_works">0.000</span><input type="hidden" name="total" id="total" value="0.000"></div>
        <div><span class="i-lbl">Other charges</span><span class="i-sum-val" id="sum_others">0.000</span><input type="hidden" name="total_others" id="total_others" value="0.000"></div>
        <div><span class="i-lbl">Electricity + water</span><span class="i-sum-val" id="sum_ew">0.000</span><input type="hidden" name="elec_water_total" id="elec_water_total" value="0.000"></div>
        <div class="i-field" style="margin:0"><label for="maintenance_due" style="margin-bottom:2px">Discount (OMR)</label><input type="text" inputmode="decimal" id="maintenance_due" name="maintenance_due" class="form-control num i-money i-discount" value="{{ old('maintenance_due') }}" placeholder="0.000"></div>
        <div><span class="i-lbl">Net amount</span><span class="i-net" id="sum_net">0.000</span><input type="hidden" name="net" id="net" value="0.000"></div>
        <div class="i-sum-actions">
          <button type="submit" class="btn btn-primary" id="insp_save">SAVE INSPECTION</button>
          <span class="i-sum-note" id="insp_note">Net = checklist + other charges + electricity/water − discount · saves all tabs</span>
        </div>
      </div>
  </form>
@endif

</div>{{-- /insp-panes --}}
{{-- ── Photo upload modal ─────────────────────────────────────────── --}}
<div class="modal fade" id="insp_photo_modal" tabindex="-1" role="dialog" aria-labelledby="insp_photo_title" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content insp">
      <div class="modal-header">
        <h5 class="modal-title" id="insp_photo_title"><i class="fa fa-camera" aria-hidden="true"></i> Inspection photos <small class="i-sub">JPG / PNG · uploads immediately, page reloads</small></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
    <div class="dataSearchBox">
      <form autocomplete="off" action="{{ route('imageUpload') }}" method="POST" id="img_form" enctype="multipart/form-data">
        {{ csrf_field() }}
        <input type="hidden" name="termination_id" value="{{ $termination->id }}">
        <input type="hidden" name="termination_contract" value="{{ $termination->contract_id }}">
        <input type="hidden" name="work_flow_processes" value="{{ $termination->work_flow_processes_code }}">
        <div class="i-photo-form">
          <div class="i-field">
            <label for="termination_doc_type">Category <small class="textRed">*</small></label>
            <select name="termination_doc_type[]" id="termination_doc_type" class="form-control" required>
              <option value="">Select category</option>
              @foreach(['Electricity', 'Plumbing', 'Carpentry', 'Others'] as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
            </select>
          </div>
          <div class="i-field">
            <label for="report_image_file_name">Photo <small class="textRed">*</small></label>
            <input type="file" name="report_image_file_name[]" id="report_image_file_name" class="form-control" required accept=".jpg,.jpeg,.png" data-rule-extension="jpg|jpeg|png">
          </div>
          <div><button type="submit" class="btn btn-primary" style="min-height:44px" id="img_upload_btn"><i class="fa fa-upload" aria-hidden="true"></i> Upload</button></div>
        </div>
      </form>

      <div style="margin-top:16px">
        @if(count($terminationDocument))
        <div class="i-photos">
          @foreach($terminationDocument as $document)
          <div class="i-photo">
            <a href="{{ asset('storage/app/' . $document->termination_doc) }}" target="_blank">
              <img src="{{ asset('storage/app/' . ($document->image_path_thumbnail ?: $document->termination_doc)) }}" alt="{{ $document->termination_doc_type }} photo" loading="lazy">
            </a>
            <div class="i-photo-meta">
              <span class="i-cat-tag">{{ $document->termination_doc_type }}</span>
              <a href="{{ asset('storage/app/' . $document->termination_doc) }}" target="_blank">{{ $document->termination_doc_name }}</a>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <p class="text-muted" style="margin:0">No photos uploaded yet.</p>
        @endif
      </div>
    </div>
      </div>
    </div>
  </div>
</div>

</div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
  function num(v) { v = String(v == null ? '' : v).replace(/,/g, '').trim(); var n = parseFloat(v); return isNaN(n) ? 0 : n; }
  function fmt(n) { return (Math.round(n * 1000) / 1000).toFixed(3); }

  // ---- tabs (own implementation: the panes span two <form>s) ------------
  var $tabs = $('#insp_tabs .i-tab'), $panes = $('.insp-panes .i-pane');
  function showTab(id, focusPane) {
    if (!$(id).length) { id = $tabs.first().attr('href'); }
    $tabs.removeClass('active').attr('aria-selected', 'false').filter('[href="' + id + '"]').addClass('active').attr('aria-selected', 'true');
    $panes.removeClass('active').filter(id).addClass('active');
    if (typeof placeSummary === 'function') { placeSummary(); }
    try { window.localStorage.setItem('insp_tab_' + {{ (int) $termination->id }}, id); } catch (e) {}
    if (focusPane) {
      var $first = $(id).find('input:visible, textarea:visible, select:visible').filter(':not([readonly])').first();
      if ($first.length) { $first.focus(); }
      window.scrollTo(0, Math.max(0, $('#insp_tabs').offset().top - 70));
    }
  }
  $('#insp_tabs').on('click', '.i-tab', function (e) { e.preventDefault(); showTab($(this).attr('href')); });
  $('.insp-panes').on('click', '.i-goto', function (e) { e.preventDefault(); showTab($(this).attr('href'), true); });
  var startTab = window.location.hash;
  if (!startTab) { try { startTab = window.localStorage.getItem('insp_tab_' + {{ (int) $termination->id }}); } catch (e) {} }
  @if($errors->any()) startTab = '#pane_notes'; @endif
  @if(session('success') && count($terminationDocument)) $('#insp_photo_modal').modal('show'); @endif
  showTab(startTab || $tabs.first().attr('href'));

  // Fixed save bar: align it with the content column (the layout has a left sidebar).
  function placeSummary() {
    var $s = $('.i-summary'); if (!$s.length) return;
    var $col = $('.row.insp'), off = $col.offset();
    $s.css({ left: off.left, right: 'auto', width: $col.outerWidth() });
    $col.addClass('has-fixed-summary').css('padding-bottom', ($s.outerHeight() + 24) + 'px');
  }
  placeSummary(); $(window).on('resize', placeSummary); setTimeout(placeSummary, 300);

  // ---- checklist behaviour ---------------------------------------------
  function rowOf(el) { return $(el).closest('tr.i-row'); }
  function syncRow($row) {
    var checked = $row.find('.i-chk').prop('checked');
    $row.toggleClass('picked', checked);
  }
  // Typing a qty/amount ticks the row; clearing both un-ticks it.
  $('#save_inspection').on('input', '.i-qty, .i-amt, .i-other-amt', function () {
    var $row = rowOf(this);
    var has = num($row.find('.i-qty').val()) > 0 || num($row.find('.i-amt, .i-other-amt').val()) > 0;
    $row.find('.i-chk').prop('checked', has);
    syncRow($row); recalc();
  });
  $('#save_inspection').on('change', '.i-chk', function () {
    var $row = rowOf(this);
    syncRow($row);
    if (this.checked && !num($row.find('.i-amt, .i-other-amt').val())) { $row.find('.i-amt, .i-other-amt').focus(); }
    recalc();
  });
  // Editable "other" descriptions post as the checkbox value (what store() reads).
  $('#save_inspection').on('input', '.i-other-desc', function () {
    var v = $.trim($(this).val()) || $(this).data('default');
    rowOf(this).find('.i-other-chk').val(v);
  });
  // Money fields: 3 dp on blur.
  $('#save_inspection').on('blur', '.i-money', function () { if ($(this).val() !== '') { $(this).val(fmt(num($(this).val()))); } });
  $('#save_inspection').on('input', '.i-elec-water, .i-discount', recalc);

  // Category pills: show one category at a time ("All" shows everything).
  var activeCat = 'all';
  function showCat(cat) {
    activeCat = cat;
    $('#insp_pills .i-pill-btn').removeClass('active').filter('[data-cat="' + cat + '"]').addClass('active');
    $('.i-cat[data-cat]').each(function () { $(this).toggleClass('cat-hide', cat !== 'all' && String($(this).data('cat')) !== String(cat)); });
    try { window.localStorage.setItem('insp_cat_' + {{ (int) $termination->id }}, cat); } catch (e) {}
  }
  $('#insp_pills').on('click', '.i-pill-btn', function () { showCat($(this).data('cat')); });
  var firstCat = String($('#insp_pills .i-pill-btn').not('[data-cat="all"]').first().data('cat') || 'all');
  var startCat = firstCat;
  try { startCat = window.localStorage.getItem('insp_cat_' + {{ (int) $termination->id }}) || firstCat; } catch (e) {}
  showCat($('#insp_pills .i-pill-btn[data-cat="' + startCat + '"]').length ? startCat : firstCat);

  // Live filter over checklist items.
  $('#insp_filter').on('input', function () {
    var q = $.trim($(this).val()).toLowerCase();
    if (q && activeCat !== 'all') { showCat('all'); }
    $('.i-cat[data-cat]').each(function () {
      var shown = 0;
      $(this).find('tr.i-row').each(function () {
        var match = !q || $(this).data('name').indexOf(q) >= 0 || $(this).hasClass('picked');
        $(this).toggleClass('hide', !match); if (match) shown++;
      });
      $(this).find('.i-empty-cat').toggle(shown === 0);
    });
  });

  // ---- totals ----------------------------------------------------------
  function recalc() {
    var works = 0, picked = 0;
    $('.i-cat[data-cat]').each(function () {
      var catSum = 0, catN = 0;
      $(this).find('tr.i-row').each(function () {
        if ($(this).find('.i-chk').prop('checked')) { catN++; catSum += num($(this).find('.i-amt').val()); }
      });
      works += catSum; picked += catN;
      $(this).find('.i-cat-sum').text(catN ? catN + ' selected · ' + fmt(catSum) : '0 selected').toggleClass('active', catN > 0);
      $('#insp_pills [data-pill-n="' + $(this).data('cat') + '"]').text(catN).closest('.i-pill-btn').toggleClass('has', catN > 0);
    });
    var others = 0;
    $('tr.i-other-row').each(function () { if ($(this).find('.i-chk').prop('checked')) { others += num($(this).find('.i-other-amt').val()); } });
    var ew = num($('#elec_amt').val()) + num($('#water_amt').val());
    var discount = num($('#maintenance_due').val());
    var net = works + others + ew - discount;

    $('#insp_picked').text(picked); $('#insp_works_total').text(fmt(works));
    var otherN = $('tr.i-other-row .i-chk:checked').length;
    $('#tab_badge_checklist').text(picked).prop('hidden', picked === 0);
    $('#insp_pills [data-pill-n="all"]').text(picked).closest('.i-pill-btn').toggleClass('has', picked > 0);
    $('#tab_badge_other').text(otherN).prop('hidden', otherN === 0);
    $('#insp_tabs .i-tab[href="#pane_readings"]').toggleClass('done', ew > 0);
    $('#insp_tabs .i-tab[href="#pane_checklist"]').toggleClass('done', picked > 0);
    $('#insp_tabs .i-tab[href="#pane_other"]').toggleClass('done', otherN > 0);
    $('#sum_works').text(fmt(works)); $('#total').val(fmt(works));
    $('#sum_others').text(fmt(others)); $('#total_others').val(fmt(others));
    $('#sum_ew').text(fmt(ew)); $('#elec_water_total').val(fmt(ew));
    $('#sum_net').text(fmt(net)); $('#net').val(fmt(net));
    $('#insp_note').text(net < 0 ? 'Discount is larger than the charges — net is negative.' : 'Net = checklist + other charges + electricity/water − discount');
  }

  // ---- key switch label ------------------------------------------------
  $('#switch-2').on('change', function () { $('#insp_key_text').text(this.checked ? 'Received' : 'Not received'); }).trigger('change');

  // ---- submit ----------------------------------------------------------
  $('#save_inspection').on('submit', function (e) {
    var $notes = $('#notes');
    if (!$.trim($notes.val())) {
      e.preventDefault();
      showTab('#pane_notes');
      $('#tab_dot_notes').addClass('show');
      $notes.closest('.i-field').addClass('has-error');
      if (!$notes.next('.i-error').length) { $notes.after('<div class="i-error">Inspection notes are required.</div>'); }
      $notes.focus();
      return false;
    }
    recalc();
    $('#insp_save').prop('disabled', true).text('SAVING…');
  });
  $('#notes').on('input', function () { $(this).closest('.i-field').removeClass('has-error'); $(this).next('.i-error').remove(); var ok = $.trim($(this).val()) !== ''; $('#tab_dot_notes').toggleClass('show', false); $('#insp_tabs .i-tab[href="#pane_notes"]').toggleClass('done', ok); }).trigger('input');

  $('#img_form').on('submit', function () { $('#img_upload_btn').prop('disabled', true).text('Uploading…'); });

  // Initial state (also restores rows after a validation redirect).
  $('#save_inspection tr.i-row').each(function () { syncRow($(this)); });
  recalc();
  var $err = $('#insp_errors'); if ($err.length) { $err.focus(); }
});
</script>
@endsection
