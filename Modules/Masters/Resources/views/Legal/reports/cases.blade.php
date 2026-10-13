@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
@php $LC = \Modules\Masters\Services\LegalCase::class; $f = $filters; @endphp
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left"><div class="page-title">Legal Cases Summary</div></div>
    {{ Breadcrumbs::render('legal.reports.cases') }}
  </div>
</div>
<div class="card card-box">
  <div class="card-body">
    <form method="GET" action="{{ route('legal.reports.cases') }}" class="row">
      <div class="col-sm-2"><label for="lr_status">Cases</label><select id="lr_status" name="status" class="form-control">
        @foreach(['open' => 'Open', 'closed' => 'Closed', 'all' => 'All'] as $k => $v)<option value="{{ $k }}" @if(($f['status'] ?? 'open') === $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="col-sm-2"><label for="lr_handled">Handled by</label><select id="lr_handled" name="handled_by" class="form-control"><option value="">All</option>
        @foreach($LC::HANDLED_BY as $k => $v)<option value="{{ $k }}" @if((string) ($f['handled_by'] ?? '') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="col-sm-2"><label for="lr_type">Type</label><select id="lr_type" name="case_type" class="form-control"><option value="">All</option>
        @foreach($LC::TYPES as $k => $v)<option value="{{ $k }}" @if((string) ($f['case_type'] ?? '') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="col-sm-2"><label for="lr_prio">Priority</label><select id="lr_prio" name="priority" class="form-control"><option value="">All</option>
        @foreach($LC::PRIORITIES as $k => $v)<option value="{{ $k }}" @if((string) ($f['priority'] ?? '') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="col-sm-2"><label for="lr_stage">Stage</label><select id="lr_stage" name="stage_id" class="form-control"><option value="">All</option>
        <option value="none" @if(($f['stage_id'] ?? '') === 'none') selected @endif>Stage not set</option>
        @foreach($stages as $s)<option value="{{ $s->id }}" @if((string) ($f['stage_id'] ?? '') === (string) $s->id) selected @endif>{{ $s->name }}</option>@endforeach</select></div>
      <div class="col-sm-2"><label for="lr_lawyer">Lawyer</label><select id="lr_lawyer" name="lawyer" class="form-control"><option value="">All</option>
        @foreach($lawyers as $id => $nm)<option value="{{ $id }}" @if((string) ($f['lawyer'] ?? '') === (string) $id) selected @endif>{{ $nm }}</option>@endforeach</select></div>
      <div class="col-sm-2"><label for="lr_from">Moved to legal from</label><input type="date" id="lr_from" name="from" class="form-control" value="{{ $f['from'] ?? '' }}"></div>
      <div class="col-sm-2"><label for="lr_to">to</label><input type="date" id="lr_to" name="to" class="form-control" value="{{ $f['to'] ?? '' }}"></div>
      <div class="col-sm-8" style="padding-top:28px;">
        <button type="submit" class="btn btn-primary">Show</button>
        <button type="submit" name="export" value="pdf" class="btn btn-default">PDF</button>
        <button type="submit" name="export" value="excel" class="btn btn-default">Excel</button>
        <a href="{{ route('legal.reports.cases') }}" class="btn btn-default">Reset</a>
      </div>
    </form>
  </div>
</div>
<div class="card card-box">
  <div class="card-body" style="overflow-x:auto;">
    @include('masters::Legal.reports.cases_tables')
  </div>
</div>
@endsection
