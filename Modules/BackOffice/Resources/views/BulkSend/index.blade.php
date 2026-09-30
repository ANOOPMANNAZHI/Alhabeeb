@extends('layouts.plms-app')
@section('css')
@include('backoffice::MessageTemplate._styles')
@endsection
@section('content')
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Send</div></div>{{ Breadcrumbs::render('bulk-send.index') }}</div></div>
<div class="mt"><div class="card card-box"><div class="card-body">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
  @if(Route::has('bulk-send.create'))
  <p><a class="mt-btn mt-btn-primary" href="{{ route('bulk-send.create') }}"><i class="fa fa-paper-plane"></i> New send</a></p>
  @endif
  <table class="table table-striped">
    <thead><tr><th>#</th><th>Started</th><th>By</th><th>Message</th><th>To</th><th>Channels</th><th>Status</th><th>Sent / Failed / Skipped / Pending</th><th></th></tr></thead>
    <tbody>
    @forelse($sends as $s)
      @php $pending = $s->status === 'cancelled' ? 0 : $s->total - $s->sent - $s->failed - $s->skipped; @endphp
      <tr>
        <td><a href="{{ route('bulk-send.show', $s->id) }}">{{ $s->id }}</a></td>
        <td>{{ $s->created_at ? $s->created_at->format('d/m/Y H:i') : '' }}</td>
        <td>{{ $s->creator ? $s->creator->username : '' }}</td>
        <td>{{ $s->template ? $s->template->name : 'One-off' }}<div class="mt-help">{{ $s->email_subject }}</div></td>
        <td>{{ \Modules\BackOffice\Entities\BulkSend::AUDIENCE_LABELS[$s->audience] ?? $s->audience }}</td>
        <td>{{ $s->send_email ? 'Email' : '' }}{{ $s->send_email && $s->send_sms ? ' + ' : '' }}{{ $s->send_sms ? 'SMS' : '' }}</td>
        <td><span class="mt-badge {{ $s->status === 'running' ? 'mt-badge-on' : '' }}">{{ ucfirst($s->status) }}</span>@if($s->note)<div class="mt-help">{{ $s->note }}</div>@endif</td>
        <td>{{ $s->sent }} / {{ $s->failed }} / {{ $s->skipped }} / {{ max(0, $pending) }}</td>
        <td class="mt-actions">
          @if($s->status === 'running')
            <form method="post" action="{{ route('bulk-send.pause', $s->id) }}" style="display:inline">@csrf<button class="mt-btn mt-btn-sm">Pause</button></form>
          @endif
          @if($s->status === 'paused')
            <form method="post" action="{{ route('bulk-send.resume', $s->id) }}" style="display:inline">@csrf<button class="mt-btn mt-btn-sm">Resume</button></form>
          @endif
          @if(in_array($s->status, ['running', 'paused']))
            <form method="post" action="{{ route('bulk-send.cancel', $s->id) }}" style="display:inline" onsubmit="return confirm('Cancel send #{{ $s->id }}? Messages not sent yet will not be sent.')">@csrf<button class="mt-btn mt-btn-sm mt-btn-danger">Cancel</button></form>
          @endif
          @if(in_array($s->status, ['done', 'cancelled']) && $s->failed > 0 && !$s->resent_send_id)
            <form method="post" action="{{ route('bulk-send.resend-failed', $s->id) }}" style="display:inline" onsubmit="if (!confirm('Resend the failed messages of send #{{ $s->id }}?')) { return false; } $(this).find('button').prop('disabled', true); return true;">@csrf<button class="mt-btn mt-btn-sm">Resend to failed</button></form>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="9" class="mt-empty">No sends yet.</td></tr>
    @endforelse
    </tbody>
  </table>
  {{ $sends->links() }}
</div></div></div>
@endsection
