@extends('layouts.plms-app')
@section('css')
@include('backoffice::MessageTemplate._styles')
@endsection
@section('content')
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Review send</div></div>{{ Breadcrumbs::render('bulk-send.create') }}</div></div>
<div class="mt"><div class="card card-box"><div class="card-body">
<form method="post" action="{{ route('bulk-send.store') }}" id="bs-store">
  @csrf
  <input type="hidden" name="template_id" value="{{ $in['template_id'] }}">
  @if(!$in['template_id'])
    <input type="hidden" name="email_subject" value="{{ $in['email_subject'] }}">
    <input type="hidden" name="email_body" value="{{ $in['email_body'] }}">
    <input type="hidden" name="sms_body" value="{{ $in['sms_body'] }}">
  @endif
  <input type="hidden" name="audience" value="{{ $in['audience'] }}">
  @foreach(['locations', 'buildings', 'roles'] as $f)
    @foreach((array) ($in['filters'][$f] ?? []) as $v)<input type="hidden" name="{{ $f }}[]" value="{{ $v }}">@endforeach
  @endforeach
  @if($in['send_email'])<input type="hidden" name="send_email" value="1">@endif
  @if($in['send_sms'])<input type="hidden" name="send_sms" value="1">@endif
  <div id="bs-hidden"></div>

  <div class="mt-section">
    <p><strong>{{ $template ? $template->name : 'One-off message' }}</strong> to <strong>{{ $audienceLabel }}</strong>.</p>
    <p id="bs-summary"></p>
  </div>
  <div class="mt-section"><h4>Preview (first person)</h4>
    <div class="mt-help">Subject: <span id="bs-pv-subject"></span></div>
    <div id="bs-pv-html" style="border:1px solid #ddd;padding:10px;max-height:320px;overflow:auto"></div>
    @if($in['send_sms'])<div class="mt-help">SMS: <span id="bs-pv-sms"></span></div>@endif
  </div>
  <div class="mt-section"><h4>People</h4>
    <input type="text" id="bs-filter" class="mt-input" placeholder="Search the list">
    <input type="text" id="bs-add" class="mt-input" placeholder="Add person: type a name"><div id="bs-add-results"></div>
    <table class="table table-striped"><thead><tr><th></th><th>Name</th><th>Email</th><th>Mobile</th></tr></thead><tbody id="bs-rows"></tbody></table>
    <div id="bs-pager"></div>
  </div>
  <div class="mt-footer">
    <button type="submit" class="mt-btn mt-btn-primary">Start sending</button>
    <a href="{{ route('bulk-send.create') }}" class="mt-btn">Back</a>
  </div>
</form>
</div></div></div>
@endsection
@section('scripts')
<script>
(function () {
  var people = @json($list);
  var sendEmail = {{ $in['send_email'] ? 'true' : 'false' }};
  var sendSms = {{ $in['send_sms'] ? 'true' : 'false' }};
  var perMinute = {{ (int) $perMinute }};
  var excluded = {}, added = [], page = 0, PER = 50, filter = '';
  var token = $('meta[name=csrf-token]').attr('content');

  function included() { return people.filter(function (p) { return !excluded[p.key]; }); }
  function esc(s) {
    return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function summary() {
    var inc = included(), e = 0, m = 0;
    inc.forEach(function (p) { if (p.email) { e++; } if (p.mobile) { m++; } });
    var parts = [inc.length + ' people'];
    if (sendEmail) { parts.push('email to ' + e); }
    if (sendSms) { parts.push('SMS to ' + m); }
    parts.push('about ' + Math.max(1, Math.ceil(inc.length / perMinute)) + ' minute(s) at ' + perMinute + '/min');
    $('#bs-summary').text(parts.join(', '));
    return { total: inc.length, email: e, mobile: m };
  }

  function render() {
    var list = people.filter(function (p) { return !filter || String(p.name || '').toLowerCase().indexOf(filter) !== -1; });
    var $b = $('#bs-rows').empty();
    list.slice(page * PER, page * PER + PER).forEach(function (p) {
      $b.append('<tr><td><input type="checkbox" class="bs-tick" data-key="' + esc(p.key) + '" ' + (excluded[p.key] ? '' : 'checked') + '></td><td>' + esc(p.name) + '</td><td>' + esc(p.email) + '</td><td>' + esc(p.mobile) + '</td></tr>');
    });
    var pages = Math.max(1, Math.ceil(list.length / PER));
    $('#bs-pager').html('Page ' + (page + 1) + ' of ' + pages + ' <button type="button" class="mt-btn mt-btn-sm" id="bs-prev">‹</button> <button type="button" class="mt-btn mt-btn-sm" id="bs-next">›</button>');
    $('#bs-prev').prop('disabled', page === 0).on('click', function () { page--; render(); });
    $('#bs-next').prop('disabled', page >= pages - 1).on('click', function () { page++; render(); });
    summary();
  }

  $(document).on('change', '.bs-tick', function () { var k = $(this).attr('data-key'); if (this.checked) { delete excluded[k]; } else { excluded[k] = true; } summary(); });
  $('#bs-filter').on('input', function () { filter = $.trim(this.value).toLowerCase(); page = 0; render(); });

  var t;
  $('#bs-add').on('input', function () {
    var q = $.trim(this.value); clearTimeout(t);
    if (q.length < 2) { $('#bs-add-results').empty(); return; }
    t = setTimeout(function () {
      $.get('{{ route('bulk-send.search') }}', { audience: '{{ $in['audience'] }}', q: q }).done(function (hits) {
        var $r = $('#bs-add-results').empty();
        hits.forEach(function (h) {
          if (people.some(function (p) { return p.key === h.key; })) { return; }
          $('<button type="button" class="mt-btn mt-btn-sm">').text('+ ' + h.name).on('click', function () {
            people.unshift(h); added.push(h.key); $(this).remove(); render();
          }).appendTo($r);
        });
      });
    }, 300);
  });

  function preview() {
    var first = included()[0]; if (!first) { return; }
    var data = $('#bs-store').serializeArray().filter(function (f) { return f.name !== '_token'; });
    data.push({ name: 'key', value: first.key }, { name: '_token', value: token });
    $.post('{{ route('bulk-send.preview') }}', $.param(data)).done(function (o) {
      $('#bs-pv-subject').text(o.subject || '(no email)');
      $('#bs-pv-html').html(o.html || '');
      $('#bs-pv-sms').text(o.sms || '');
    });
  }

  $('#bs-store').on('submit', function () {
    var s = summary();
    if (!s.total) { alert('Nobody to send to.'); return false; }
    var msg = 'Start sending to ' + s.total + ' people?';
    if (sendEmail) { msg += '\nEmails: ' + s.email; }
    if (sendSms) { msg += '\nSMS: ' + s.mobile + ' (SMS costs money)'; }
    if (!confirm(msg)) { return false; }
    var $h = $('#bs-hidden').empty();
    Object.keys(excluded).forEach(function (k) { $h.append($('<input type="hidden" name="exclude[]">').val(k)); });
    added.forEach(function (k) { if (!excluded[k]) { $h.append($('<input type="hidden" name="add[]">').val(k)); } });
    return true;
  });

  render(); preview();
})();
</script>
@endsection
