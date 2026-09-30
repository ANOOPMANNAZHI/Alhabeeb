@extends('layouts.plms-app')

@section('css')
@include('backoffice::MessageTemplate._styles')
<style>
  .mt .ml-status-sent { background:#dcfce7; color:#166534; }
  .mt .ml-status-failed { background:#fee2e2; color:#991b1b; }
  .mt .ml-note { display:block; font-size:12px; color:#991b1b; margin-top:4px; max-width:260px; }
  .mt .ml-sub { display:block; font-size:12px; color:#475569; }
  .mt input[type=date].mt-input { width:160px; }
  #ml-modal dl { display:grid; grid-template-columns:120px 1fr; gap:6px 12px; margin:0 0 12px; font-size:14px; }
  #ml-modal dt { color:#475569; font-weight:600; }
  #ml-modal dd { margin:0; word-break:break-word; }
  #ml-modal .ml-frame { display:block; width:100%; height:380px; border:1px solid #e2e8f0; border-radius:6px; background:#fff; }
  #ml-modal .ml-sms { background:#dcfce7; border-radius:14px 14px 14px 4px; padding:10px 14px; max-width:340px; white-space:pre-wrap; word-wrap:break-word; }
</style>
@endsection

@section('content')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">Sent log</div></div>
    {{ Breadcrumbs::render('message-log.index') }}
  </div>
</div>

<div class="mt">
<div class="card card-box">
  <div class="card-body">
    @if(!empty($filters['bulk_send']))
      <p class="mt-help" style="margin:0 0 12px"><i class="fa fa-filter" aria-hidden="true"></i>
        Showing only the messages of <a href="{{ route('bulk-send.show', $filters['bulk_send']) }}">send #{{ $filters['bulk_send'] }}</a>.
        <a href="{{ route('message-log.index') }}">Show all messages</a></p>
    @endif
    <form method="GET" action="{{ route('message-log.index') }}" class="mt-filters" style="margin-bottom:16px" role="search">
      @if(!empty($filters['bulk_send']))<input type="hidden" name="bulk_send" value="{{ $filters['bulk_send'] }}">@endif
      <div><label for="ml-from">From</label><input type="date" id="ml-from" name="from" class="mt-input" value="{{ $filters['from'] }}"></div>
      <div><label for="ml-to">To</label><input type="date" id="ml-to" name="to" class="mt-input" value="{{ $filters['to'] }}"></div>
      <div>
        <label for="ml-template">Template</label>
        <select id="ml-template" name="template" class="mt-input">
          <option value="">All</option>
          @foreach($templates as $key => $name)
            <option value="{{ $key }}" {{ $filters['template'] === $key ? 'selected' : '' }}>{{ $name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="ml-channel">Channel</label>
        <select id="ml-channel" name="channel" class="mt-input">
          <option value="">All</option>
          <option value="email" {{ $filters['channel'] === 'email' ? 'selected' : '' }}>Email</option>
          <option value="sms" {{ $filters['channel'] === 'sms' ? 'selected' : '' }}>SMS</option>
        </select>
      </div>
      <div>
        <label for="ml-status">Status</label>
        <select id="ml-status" name="status" class="mt-input">
          <option value="">All</option>
          @foreach($statuses as $value => $label)
            <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div><label for="ml-q">Search</label><input type="text" id="ml-q" name="q" class="mt-input" value="{{ $filters['q'] }}" placeholder="Recipient or subject"></div>
      <div class="mt-row">
        <button type="submit" class="mt-btn"><i class="fa fa-search" aria-hidden="true"></i> Filter</button>
        <a href="{{ route('message-log.index') }}" class="mt-btn">Clear</a>
      </div>
    </form>

    @if($logs->count())
      <div style="overflow-x:auto">
      <table>
        <thead>
          <tr><th>When</th><th>Template</th><th>Channel</th><th>Recipient</th><th class="mt-hide-sm">Subject / text</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          @foreach($logs as $log)
            <tr>
              <td style="white-space:nowrap">{{ optional($log->created_at)->format('d/m/Y H:i') }}</td>
              <td>{{ $log->template_label }}<span class="mt-key">{{ $log->template_key }}</span></td>
              <td style="white-space:nowrap">
                @if($log->channel === 'sms')<i class="fa fa-mobile" aria-hidden="true"></i> SMS @else<i class="fa fa-envelope-o" aria-hidden="true"></i> Email @endif
              </td>
              <td>{{ $log->recipient_name }}<span class="ml-sub">{{ $log->recipient ?: '—' }}</span></td>
              <td class="mt-hide-sm">{{ \Illuminate\Support\Str::limit($log->channel === 'sms' ? $log->body : $log->subject, 60) }}</td>
              <td>
                <span class="mt-badge ml-status-{{ $log->status }}" @if($log->note) title="{{ $log->note }}" @endif>{{ isset($statuses[$log->status]) ? $statuses[$log->status] : $log->status }}</span>
                @if($log->status === 'failed' && $log->note)<span class="ml-note">{{ \Illuminate\Support\Str::limit($log->note, 120) }}</span>@endif
              </td>
              <td>
                @if($log->body)
                  <button type="button" class="mt-btn mt-btn-sm ml-view" data-id="{{ $log->id }}"><i class="fa fa-eye" aria-hidden="true"></i> View</button>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      </div>
      <div style="margin-top:16px">{{ $logs->links() }}</div>
    @else
      <div class="mt-empty">
        <strong>Nothing was sent in this period.</strong>
        Widen the dates or clear the filters.
      </div>
    @endif
  </div>
</div>
</div>

<div class="modal fade" id="ml-modal" tabindex="-1" role="dialog" aria-labelledby="ml-modal-title">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="ml-modal-title">Sent message</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body"><dl id="ml-meta"></dl><div id="ml-content"></div></div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(function () {
    var showUrl = @json(route('message-log.show', '__ID__'));
    $('.ml-view').on('click', function () {
      $.get(showUrl.replace('__ID__', $(this).data('id'))).done(function (r) {
        var $meta = $('#ml-meta').empty();
        var rows = [['When', r.created], ['Template', r.template], ['To', (r.recipient_name || '') + (r.recipient ? ' <' + r.recipient + '>' : '')],
                    ['CC', r.cc], ['Subject', r.subject], ['Status', r.status_label], ['Note', r.note]];
        $.each(rows, function (i, row) {
          if (row[1]) { $meta.append($('<dt>').text(row[0])).append($('<dd>').text(row[1])); }
        });
        if (r.channel === 'sms') {
          $('#ml-content').html('<div class="ml-sms"></div>').find('.ml-sms').text(r.body || '');
        } else {
          // Sandboxed frame (no scripts) for the stored email body.
          $('#ml-content').html('<iframe class="ml-frame" sandbox="" title="Email body"></iframe>')
            .find('iframe').attr('srcdoc', '<!doctype html><meta charset="utf-8"><body style="font-family:Arial,sans-serif;font-size:14px;color:#005aab">' + (r.body || '') + '</body>');
        }
        $('#ml-modal').modal('show');
      }).fail(function () { alert('Could not load this message. Please try again.'); });
    });
  });
</script>
@endsection
