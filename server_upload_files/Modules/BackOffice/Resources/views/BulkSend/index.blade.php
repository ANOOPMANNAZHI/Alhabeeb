@extends('layouts.plms-app')
@section('css')
@include('backoffice::BulkSend._styles')
@endsection
@section('content')
@php
  $bsRunning = $sends->contains(function ($s) { return $s->status === 'running'; });
  $bsAudiences = \Modules\BackOffice\Entities\BulkSend::AUDIENCE_LABELS;
@endphp
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Send</div></div>{{ Breadcrumbs::render('bulk-send.index') }}</div></div>
<div class="mt bs"><div class="card card-box"><div class="card-body">
  @if(session('success'))<div class="alert alert-success" role="status"><i class="fa fa-check-circle" aria-hidden="true"></i> {{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> {{ session('error') }}</div>@endif

  <h2 class="bs-sr">Bulk sends</h2>
  @if($bsRunning || (Route::has('bulk-send.create') && $sends->count()))
    <div class="bs-head bs-head-end">
      @if($bsRunning)
        <p class="bs-refresh" id="bs-refresh" data-auto-refresh="1"><i class="fa fa-refresh" aria-hidden="true"></i>
          Updated <time id="bs-refresh-time">{{ now()->format('H:i:s') }}</time> · auto-refresh on</p>
      @endif
      @if(Route::has('bulk-send.create') && $sends->count())
        <a class="mt-btn mt-btn-primary" href="{{ route('bulk-send.create') }}"><i class="fa fa-paper-plane" aria-hidden="true"></i> New send</a>
      @endif
    </div>
  @endif

  @if($sends->count())
    <div class="bs-table-wrap">
      <table class="bs-sends">
        <thead><tr>
          <th scope="col" class="bs-col-id">#</th>
          <th scope="col">Message</th>
          <th scope="col">To</th>
          <th scope="col" class="bs-col-status">Status</th>
          <th scope="col" class="bs-col-progress">Progress</th>
          <th scope="col" class="bs-col-started">Started</th>
          <th scope="col" class="bs-col-actions"><span class="bs-sr">Actions</span></th>
        </tr></thead>
        <tbody>
        @foreach($sends as $s)
          <tr class="bs-send">
            <td class="bs-col-id">#{{ $s->id }}</td>
            <td>
              <a class="bs-send-name" href="{{ route('bulk-send.show', $s->id) }}">{{ $s->template ? $s->template->name : 'One-off message' }}</a>
              @if($s->email_subject)<span class="bs-sub">{{ $s->email_subject }}</span>@endif
              @if($s->note)<p class="bs-warn"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span>{{ $s->note }}</span></p>@endif
            </td>
            <td>
              {{ isset($bsAudiences[$s->audience]) ? $bsAudiences[$s->audience] : $s->audience }}
              <span class="bs-sub">{{ implode(' + ', array_filter([$s->send_email ? 'Email' : null, $s->send_sms ? 'SMS' : null])) }}</span>
            </td>
            <td class="bs-col-status">@include('backoffice::BulkSend._badge', ['st' => $s->status])</td>
            <td class="bs-col-progress">@include('backoffice::BulkSend._progress', ['s' => $s])</td>
            <td class="bs-col-started">{{ $s->created_at ? $s->created_at->format('d/m/Y H:i') : '' }}@if($s->creator)<br>by {{ $s->creator->username }}@endif</td>
            <td class="bs-col-actions">@include('backoffice::BulkSend._actions', ['s' => $s])</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    <div style="margin-top:16px">{{ $sends->links() }}</div>
  @else
    <div class="bs-empty">
      <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
      <strong>No sends yet.</strong>
      @if(Route::has('bulk-send.create'))
        <a class="mt-btn mt-btn-primary" href="{{ route('bulk-send.create') }}"><i class="fa fa-paper-plane" aria-hidden="true"></i> New send</a>
      @endif
    </div>
  @endif
</div></div></div>
@endsection
@section('scripts')
@include('backoffice::BulkSend._dialog')
@if($bsRunning)
<script>
(function () {
  // Reload every 30 s while a send is running and this tab is visible (never while a dialog is open).
  var EVERY = 30000, loaded = Date.now();
  function due() { return Date.now() - loaded >= EVERY && document.visibilityState === 'visible' && $('#bs-dialog').prop('hidden'); }
  setInterval(function () { if (due()) { window.location.reload(); } }, 5000);
  document.addEventListener('visibilitychange', function () { if (due()) { window.location.reload(); } });
})();
</script>
@endif
@endsection
