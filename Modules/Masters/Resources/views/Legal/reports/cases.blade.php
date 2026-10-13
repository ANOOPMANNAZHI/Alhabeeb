@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

@section('content')
@php
    $LC = \Modules\Masters\Services\LegalCase::class;
    $f = $filters;
    $open = $rows->filter(function ($r) { return !$r->closed_at; });
    $high = $open->where('priority', 1)->count();
    $hearings = $open->filter(function ($r) { return $r->next_hearing_date && $r->next_hearing_date >= date('Y-m-d') && $r->next_hearing_date <= date('Y-m-d', strtotime('+14 days')); })->count();
    $noStage = $open->filter(function ($r) { return (int) $r->work_flow_processes_code === 803 && !$r->stage_id; })->count();
@endphp
<div class="lg">
  <div class="lg-crumbs">{{ Breadcrumbs::render('legal.reports.cases') }}</div>
  <header class="lg-page-head">
    <div>
      <h1 class="lg-page-head__title">Legal cases summary</h1>
      <div class="lg-page-head__sub">Cases by stage, priority and time in legal, with what is still owed</div>
    </div>
  </header>

  <section class="lg-card" aria-label="Report filters">
    <form method="GET" action="{{ route('legal.reports.cases') }}" class="lg-filters" style="border-bottom:0;border-radius:var(--lg-radius);">
      <div class="lg-field"><label for="lr_status" class="lg-label">Cases</label><select id="lr_status" name="status" class="lg-select">
        @foreach(['open' => 'Open', 'closed' => 'Closed', 'all' => 'All'] as $k => $v)<option value="{{ $k }}" @if(($f['status'] ?? 'open') === $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="lg-field"><label for="lr_handled" class="lg-label">Handled by</label><select id="lr_handled" name="handled_by" class="lg-select"><option value="">All</option>
        @foreach($LC::HANDLED_BY as $k => $v)<option value="{{ $k }}" @if((string) ($f['handled_by'] ?? '') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="lg-field"><label for="lr_type" class="lg-label">Type</label><select id="lr_type" name="case_type" class="lg-select"><option value="">All</option>
        @foreach($LC::TYPES as $k => $v)<option value="{{ $k }}" @if((string) ($f['case_type'] ?? '') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="lg-field"><label for="lr_prio" class="lg-label">Priority</label><select id="lr_prio" name="priority" class="lg-select"><option value="">All</option>
        @foreach($LC::PRIORITIES as $k => $v)<option value="{{ $k }}" @if((string) ($f['priority'] ?? '') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="lg-field"><label for="lr_stage" class="lg-label">Stage</label><select id="lr_stage" name="stage_id" class="lg-select"><option value="">All</option>
        <option value="none" @if(($f['stage_id'] ?? '') === 'none') selected @endif>Stage not set</option>
        @foreach($stages as $s)<option value="{{ $s->id }}" @if((string) ($f['stage_id'] ?? '') === (string) $s->id) selected @endif>{{ $s->name }}</option>@endforeach</select></div>
      <div class="lg-field"><label for="lr_lawyer" class="lg-label">Lawyer</label><select id="lr_lawyer" name="lawyer" class="lg-select"><option value="">All</option>
        @foreach($lawyers as $id => $nm)<option value="{{ $id }}" @if((string) ($f['lawyer'] ?? '') === (string) $id) selected @endif>{{ $nm }}</option>@endforeach</select></div>
      <div class="lg-field"><label for="lr_from" class="lg-label">Moved to legal from</label><input type="date" id="lr_from" name="from" class="lg-input" value="{{ $f['from'] ?? '' }}"></div>
      <div class="lg-field"><label for="lr_to" class="lg-label">To</label><input type="date" id="lr_to" name="to" class="lg-input" value="{{ $f['to'] ?? '' }}"></div>
      <div class="lg-filters__actions">
        <a href="{{ route('legal.reports.cases') }}" class="lg-btn lg-btn--ghost">Reset</a>
        <button type="submit" name="export" value="excel" class="lg-btn lg-btn--secondary"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel</button>
        <button type="submit" name="export" value="pdf" class="lg-btn lg-btn--secondary"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</button>
        <button type="submit" class="lg-btn lg-btn--primary">Show</button>
      </div>
    </form>
  </section>

  <div class="lg-kpis lg-kpis--card">
    <div class="lg-kpi"><div class="lg-kpi__label">Cases shown</div><div class="lg-kpi__value">{{ count($rows) }}</div></div>
    <div class="lg-kpi"><div class="lg-kpi__label">Open</div><div class="lg-kpi__value">{{ $summary['open'] }}</div></div>
    <div class="lg-kpi lg-kpi--bad"><div class="lg-kpi__label">Outstanding (open)</div><div class="lg-kpi__value">{{ numberFormat($summary['outstanding']) }}</div></div>
    <div class="lg-kpi"><div class="lg-kpi__label">High priority</div><div class="lg-kpi__value">{{ $high }}</div></div>
    <div class="lg-kpi"><div class="lg-kpi__label">Hearings in 14 days</div><div class="lg-kpi__value">{{ $hearings }}</div></div>
    <div class="lg-kpi"><div class="lg-kpi__label">Active, stage not set</div><div class="lg-kpi__value">{{ $noStage }}</div></div>
  </div>

  <section class="lg-card" aria-label="Report">
    <div class="lg-card__body lg-table-wrap" style="padding-top:0;">
      @include('masters::Legal.reports.cases_tables')
    </div>
  </section>
</div>
@endsection
