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

  <div class="bs-head">
    <div>
      <h2>Bulk sends</h2>
      @if($bsRunning)
        <p class="bs-refresh" id="bs-refresh" data-auto-refresh="1"><i class="fa fa-refresh" aria-hidden="true"></i>
          Updated <time id="bs-refresh-time">{{ now()->format('H:i:s') }}</time> · auto-refresh on</p>
      @endif
    </div>
    @if(Route::has('bulk-send.create') && $sends->count())
      <a class="mt-btn mt-btn-primary" href="{{ route('bulk-send.create') }}"><i class="fa fa-paper-plane" aria-hidden="true"></i> New send</a>
    @endif
  </div>

  @if($sends->count())
    <ul class="bs-list">
      @foreach($sends as $s)
        <li class="bs-send">
          <div>
            <h3 class="bs-send-title">
              <a href="{{ route('bulk-send.show', $s->id) }}"><span class="bs-send-id">#{{ $s->id }}</span> {{ $s->template ? $s->template->name : 'One-off message' }}</a>
              @include('backoffice::BulkSend._badge', ['st' => $s->status])
            </h3>
            @if($s->email_subject)<p class="bs-subject">{{ $s->email_subject }}</p>@endif
            <div class="bs-chips">
              <span class="bs-chip"><i class="fa fa-users" aria-hidden="true"></i> {{ isset($bsAudiences[$s->audience]) ? $bsAudiences[$s->audience] : $s->audience }}</span>
              @if($s->send_email)<span class="bs-chip"><i class="fa fa-envelope-o" aria-hidden="true"></i> Email</span>@endif
              @if($s->send_sms)<span class="bs-chip"><i class="fa fa-mobile" aria-hidden="true"></i> SMS</span>@endif
            </div>
            <div class="bs-meta">Started {{ $s->created_at ? $s->created_at->format('d/m/Y H:i') : '' }}@if($s->creator) by {{ $s->creator->username }}@endif</div>
          </div>
          <div>
            @include('backoffice::BulkSend._progress', ['s' => $s])
            @if($s->note)<p class="bs-warn"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span>{{ $s->note }}</span></p>@endif
          </div>
          @include('backoffice::BulkSend._actions', ['s' => $s])
        </li>
      @endforeach
    </ul>
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
