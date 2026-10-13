@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
@php $LS = \Modules\Masters\Services\LegalSettlement::class; $f = $filters; @endphp
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left"><div class="page-title">Legal Settlements</div></div>
    {{ Breadcrumbs::render('legal.reports.settlements') }}
  </div>
</div>
<div class="card card-box">
  <div class="card-body">
    <form method="GET" action="{{ route('legal.reports.settlements') }}" class="row">
      <div class="col-sm-2"><label for="ls_status">Status</label><select id="ls_status" name="status" class="form-control"><option value="">All</option>
        @foreach($LS::STATUS as $k => $v)<option value="{{ $k }}" @if(isset($f['status']) && $f['status'] !== '' && (string) $f['status'] === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="col-sm-2"><label for="ls_type">Type</label><select id="ls_type" name="type" class="form-control"><option value="">All</option>
        @foreach($LS::TYPES as $k => $v)<option value="{{ $k }}" @if((string) ($f['type'] ?? '') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
      <div class="col-sm-2"><label for="ls_lawyer">Lawyer</label><select id="ls_lawyer" name="lawyer" class="form-control"><option value="">All</option>
        @foreach($lawyers as $id => $nm)<option value="{{ $id }}" @if((string) ($f['lawyer'] ?? '') === (string) $id) selected @endif>{{ $nm }}</option>@endforeach</select></div>
      <div class="col-sm-2"><label for="ls_from">Date from</label><input type="date" id="ls_from" name="from" class="form-control" value="{{ $f['from'] ?? '' }}"></div>
      <div class="col-sm-2"><label for="ls_to">to</label><input type="date" id="ls_to" name="to" class="form-control" value="{{ $f['to'] ?? '' }}"></div>
      <div class="col-sm-12" style="padding-top:10px;">
        <button type="submit" class="btn btn-primary">Show</button>
        <button type="submit" name="export" value="pdf" class="btn btn-default">PDF</button>
        <button type="submit" name="export" value="excel" class="btn btn-default">Excel</button>
        <a href="{{ route('legal.reports.settlements') }}" class="btn btn-default">Reset</a>
      </div>
    </form>
  </div>
</div>
<div class="card card-box">
  <div class="card-body" style="overflow-x:auto;">
    @include('masters::Legal.reports.settlements_tables')
  </div>
</div>
@endsection
