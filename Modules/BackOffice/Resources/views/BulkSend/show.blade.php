@extends('layouts.plms-app')
@section('css')
@include('backoffice::BulkSend._styles')
@endsection
@section('content')
@php
  $bsAudiences = \Modules\BackOffice\Entities\BulkSend::AUDIENCE_LABELS;
  $bsTabs = ['' => 'All', 'sent' => 'Sent', 'failed' => 'Failed', 'skipped' => 'Skipped', 'pending' => 'Pending', 'cancelled' => 'Cancelled'];
  $bsTabN = $statusCounts + ['' => array_sum($statusCounts)];
  $bsStatus = in_array($status, ['pending', 'sent', 'failed', 'skipped', 'cancelled'], true) ? $status : '';
@endphp
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Send #{{ $send->id }}</div></div>{{ Breadcrumbs::render('bulk-send.show', $send) }}</div></div>
<div class="mt bs"><div class="card card-box"><div class="card-body">
  <div class="bs-head" style="align-items:flex-start;margin-bottom:0">
    <div>
      <div class="bs-summary-line">
        <h2>{{ $send->template ? $send->template->name : 'One-off message' }}</h2>
        <span class="bs-chips">
          <span class="bs-chip">{{ isset($bsAudiences[$send->audience]) ? $bsAudiences[$send->audience] : $send->audience }}</span>
          @if($send->send_email)<span class="bs-chip">Email</span>@endif
          @if($send->send_sms)<span class="bs-chip">SMS</span>@endif
        </span>
        @include('backoffice::BulkSend._badge', ['st' => $send->status])
      </div>
      <div class="bs-meta" style="margin-top:4px">
        @if($send->email_subject)Subject: {{ $send->email_subject }} · @endif
        Started {{ $send->created_at ? $send->created_at->format('d/m/Y H:i') : '' }}@if($send->creator) by {{ $send->creator->username }}@endif
        @if($send->finished_at) · Finished {{ $send->finished_at->format('d/m/Y H:i') }}@endif
      </div>
    </div>
    @include('backoffice::BulkSend._actions', ['s' => $send])
  </div>
  @if($send->note)<p class="bs-warn"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span>{{ $send->note }}</span></p>@endif

  <div class="bs-show-progress">@include('backoffice::BulkSend._progress', ['s' => $send])</div>

  <nav class="bs-tabs" aria-label="Show people by status">
    @foreach($bsTabs as $value => $label)
      <a class="bs-tab" href="{{ $value === '' ? route('bulk-send.show', $send->id) : route('bulk-send.show', [$send->id, 'status' => $value]) }}" @if($bsStatus === $value) aria-current="page" @endif>
        {{ $label }} <span class="bs-tab-n">{{ (int) $bsTabN[$value] }}</span></a>
    @endforeach
  </nav>

  <div class="bs-table-wrap">
    <table>
      <thead><tr><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Mobile</th><th scope="col">Status</th><th scope="col">Note</th><th scope="col">When</th></tr></thead>
      <tbody>
      @forelse($recipients as $r)
        <tr class="{{ $r->status === 'failed' ? 'bs-row-failed' : '' }}">
          <td>{{ $r->name }}</td>
          <td>{{ $r->email ?: '—' }}</td>
          <td>{{ $r->mobile ?: '—' }}</td>
          <td>@include('backoffice::BulkSend._badge', ['st' => $r->status])</td>
          <td>@if($r->status === 'failed' && $r->note)<span class="bs-note-failed"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> <span>{{ $r->note }}</span></span>@else<span class="bs-muted">{{ $r->note }}</span>@endif</td>
          <td style="white-space:nowrap" class="bs-muted">{{ $r->processed_at ? $r->processed_at->format('d/m/Y H:i') : '' }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="mt-empty">Nobody here.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div style="margin-top:12px">{{ $recipients->links() }}</div>

  <div class="mt-footer" style="margin-top:16px">
    <a href="{{ route('bulk-send.index') }}" class="mt-btn"><i class="fa fa-arrow-left" aria-hidden="true"></i> All sends</a>
    @if(Route::has('message-log.index'))
      @can('view_message_log')
        <a href="{{ route('message-log.index', ['bulk_send' => $send->id]) }}" class="mt-btn"><i class="fa fa-list-alt" aria-hidden="true"></i> Open in Sent log</a>
      @endcan
    @endif
  </div>
</div></div></div>
@endsection
@section('scripts')
@include('backoffice::BulkSend._dialog')
@endsection
