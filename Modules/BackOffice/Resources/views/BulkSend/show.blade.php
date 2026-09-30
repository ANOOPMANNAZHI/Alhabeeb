@extends('layouts.plms-app')
@section('css')
@include('backoffice::MessageTemplate._styles')
@endsection
@section('content')
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Send #{{ $send->id }}</div></div>{{ Breadcrumbs::render('bulk-send.show', $send) }}</div></div>
<div class="mt"><div class="card card-box"><div class="card-body">
  <p><strong>{{ $send->template ? $send->template->name : 'One-off message' }}</strong> — {{ \Modules\BackOffice\Entities\BulkSend::AUDIENCE_LABELS[$send->audience] ?? $send->audience }},
     {{ ucfirst($send->status) }}@if($send->note) ({{ $send->note }})@endif.
     Sent {{ $send->sent }}, failed {{ $send->failed }}, skipped {{ $send->skipped }} of {{ $send->total }}.</p>
  <p class="mt-help">Subject: {{ $send->email_subject }}</p>
  <form method="get" action="{{ route('bulk-send.show', $send->id) }}">
    <select name="status" class="mt-input" onchange="this.form.submit()">
      <option value="">All</option>
      @foreach(['pending', 'sent', 'failed', 'skipped', 'cancelled'] as $st)
        <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
      @endforeach
    </select>
  </form>
  <table class="table table-striped">
    <thead><tr><th>Name</th><th>Email</th><th>Mobile</th><th>Status</th><th>Note</th><th>When</th></tr></thead>
    <tbody>
    @forelse($recipients as $r)
      <tr><td>{{ $r->name }}</td><td>{{ $r->email }}</td><td>{{ $r->mobile }}</td><td>{{ ucfirst($r->status) }}</td><td>{{ $r->note }}</td><td>{{ $r->processed_at ? $r->processed_at->format('d/m/Y H:i') : '' }}</td></tr>
    @empty
      <tr><td colspan="6" class="mt-empty">Nobody here.</td></tr>
    @endforelse
    </tbody>
  </table>
  {{ $recipients->links() }}
  @if(Route::has('message-log.index'))
  <p><a href="{{ route('message-log.index') }}">Open the Sent log</a></p>
  @endif
</div></div></div>
@endsection
