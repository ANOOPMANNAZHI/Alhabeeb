@extends('layouts.plms-app')
@section('css')
@include('backoffice::BulkSend._styles')
@endsection
@section('content')
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Review send</div></div>{{ Breadcrumbs::render('bulk-send.create') }}</div></div>
<div class="mt bs"><div class="card card-box"><div class="card-body">
@include('backoffice::BulkSend._stepper', ['step' => 3])
<form method="post" action="{{ route('bulk-send.store') }}" id="bs-store">
  @csrf
  <input type="hidden" name="send_token" value="{{ $sendToken }}">
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

  <div class="bs-review-head">
    <h2>{{ $template ? $template->name : 'One-off message' }}</h2>
    <span class="bs-chips">
      <span class="bs-chip">{{ $audienceLabel }}</span>
      @if($in['send_email'])<span class="bs-chip">Email</span>@endif
      @if($in['send_sms'])<span class="bs-chip">SMS</span>@endif
    </span>
  </div>

  <div class="bs-tiles" aria-live="polite">
    <div class="bs-tile"><div class="bs-tile-label">People</div><div class="bs-tile-value" id="bs-t-total">…</div></div>
    @if($in['send_email'])
      <div class="bs-tile"><div class="bs-tile-label">Emails</div><div class="bs-tile-value" id="bs-t-email">…</div></div>
    @endif
    @if($in['send_sms'])
      <div class="bs-tile"><div class="bs-tile-label">SMS</div><div class="bs-tile-value" id="bs-t-sms">…</div><div class="bs-tile-note">SMS costs money</div></div>
    @endif
    <div class="bs-tile"><div class="bs-tile-label">No email</div><div class="bs-tile-value" id="bs-t-noemail">…</div></div>
    <div class="bs-tile"><div class="bs-tile-label">Time needed</div><div class="bs-tile-value" id="bs-t-time">…</div><div class="bs-tile-note">at {{ (int) $perMinute }} a minute</div></div>
  </div>

  <section class="bs-sec" aria-labelledby="bs-h-preview">
    <div class="bs-sec-head"><h2 id="bs-h-preview">Preview</h2> <span class="mt-help" id="bs-pv-for"></span></div>
    <div class="bs-preview">
      <div>
        @if($in['send_email'])
          <div class="bs-mail">
            <div class="bs-mail-head">
              <div><strong>Subject:</strong> <span id="bs-pv-subject">…</span></div>
              <div><strong>To:</strong> <span id="bs-pv-to">…</span></div>
            </div>
            {{-- Renderer output shown in a sandboxed frame (no scripts run). --}}
            <iframe id="bs-pv-frame" sandbox="" title="Email body preview"></iframe>
          </div>
        @else
          <p class="mt-help">Email is off for this send.</p>
        @endif
      </div>
      @if($in['send_sms'])
        <div>
          <h3 class="bs-sms-label">SMS</h3>
          <div class="bs-sms-bubble" id="bs-pv-sms">…</div>
          <div class="mt-count" id="bs-pv-sms-count"></div>
        </div>
      @endif
    </div>
    <p class="mt-error" id="bs-pv-error" role="alert" hidden></p>
  </section>

  <section class="bs-sec" aria-labelledby="bs-h-people">
    <div class="bs-sec-head"><h2 id="bs-h-people">People</h2></div>
    <div class="bs-people-tools">
      <div><label for="bs-filter">Search the list</label><input type="search" id="bs-filter" class="mt-input" autocomplete="off" placeholder="Name, email or mobile"></div>
      <div><label for="bs-add">Add a person</label><input type="search" id="bs-add" class="mt-input" autocomplete="off" placeholder="Type at least 2 letters of a name">
        <div class="bs-add-results" id="bs-add-results" aria-live="polite"></div></div>
    </div>
    <div class="bs-tabs" role="group" aria-label="Show">
      <button type="button" class="bs-tab" data-tab="all" aria-pressed="true">All <span class="bs-tab-n" id="bs-n-all">0</span></button>
      <button type="button" class="bs-tab" data-tab="excluded" aria-pressed="false">Excluded <span class="bs-tab-n" id="bs-n-excluded">0</span></button>
      <button type="button" class="bs-tab" data-tab="noemail" aria-pressed="false">No email <span class="bs-tab-n" id="bs-n-noemail">0</span></button>
    </div>
    <label class="bs-check"><input type="checkbox" id="bs-page-all"> Select all on this page</label>
    <div class="bs-table-wrap">
      <table>
        <thead><tr><th scope="col"><span class="bs-sr">Include</span></th><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Mobile</th></tr></thead>
        <tbody id="bs-rows"></tbody>
      </table>
    </div>
    <div class="bs-pager">
      <span id="bs-pager-text" aria-live="polite"></span>
      <button type="button" class="mt-btn mt-btn-sm" id="bs-prev" aria-label="Previous page"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
      <button type="button" class="mt-btn mt-btn-sm" id="bs-next" aria-label="Next page"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
    </div>
  </section>

  <div class="bs-actionbar">
    <p id="bs-summary" aria-live="polite"></p>
    <p class="mt-error" id="bs-store-error" role="alert" hidden style="margin:0"></p>
    <div class="bs-actionbar-btns">
      <a href="{{ route('bulk-send.create') }}" class="mt-btn"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
      <button type="submit" class="mt-btn mt-btn-primary" id="bs-start"><i class="fa fa-paper-plane" aria-hidden="true"></i> Start sending</button>
    </div>
  </div>
</form>
</div></div></div>
@endsection
@section('scripts')
@include('backoffice::BulkSend._dialog')
<script>
(function () {
  var people = @json($list);
  var sendEmail = {{ $in['send_email'] ? 'true' : 'false' }};
  var sendSms = {{ $in['send_sms'] ? 'true' : 'false' }};
  var perMinute = {{ (int) $perMinute }};
  var audience = @json($in['audience']);
  var excluded = {}, added = [], page = 0, PER = 50, filter = '', tab = 'all';
  var token = $('meta[name=csrf-token]').attr('content');

  function included() { return people.filter(function (p) { return !excluded[p.key]; }); }
  function minutes(n) { return n ? 'about ' + Math.max(1, Math.ceil(n / perMinute)) + ' min' : '—'; }

  function summary() {
    var inc = included(), e = 0, m = 0;
    inc.forEach(function (p) { if (p.email) { e++; } if (p.mobile) { m++; } });
    $('#bs-t-total').text(inc.length);
    $('#bs-t-email').text(e);
    $('#bs-t-sms').text(m);
    $('#bs-t-noemail').text(inc.length - e);
    $('#bs-t-time').text(minutes(inc.length));
    var parts = [inc.length + ' people'];
    if (sendEmail) { parts.push(e + ' emails'); }
    if (sendSms) { parts.push(m + ' SMS'); }
    parts.push(minutes(inc.length));
    $('#bs-summary').text(parts.join(' · '));
    $('#bs-n-all').text(people.length);
    $('#bs-n-excluded').text(people.length - inc.length);
    $('#bs-n-noemail').text(people.filter(function (p) { return !p.email; }).length);
    return { total: inc.length, email: e, mobile: m };
  }

  function visible() {
    return people.filter(function (p) {
      if (tab === 'excluded' && !excluded[p.key]) { return false; }
      if (tab === 'noemail' && p.email) { return false; }
      if (!filter) { return true; }
      return [p.name, p.email, p.mobile].some(function (v) { return String(v || '').toLowerCase().indexOf(filter) !== -1; });
    });
  }

  function missing(icon, text) {
    return $('<span class="bs-badge bs-badge-none">').append($('<i aria-hidden="true">').addClass('fa ' + icon)).append(document.createTextNode(' ' + text));
  }

  function render() {
    var list = visible();
    var pages = Math.max(1, Math.ceil(list.length / PER));
    if (page > pages - 1) { page = pages - 1; }
    var rows = list.slice(page * PER, page * PER + PER);
    var $b = $('#bs-rows').empty();
    rows.forEach(function (p) {
      var off = !!excluded[p.key];
      var $name = $('<td>').text(p.name);
      if (added.indexOf(p.key) !== -1) { $name.append(' ').append($('<span class="bs-chip">').text('Added')); }
      $('<tr>').toggleClass('bs-row-off', off)
        .append($('<td>').append($('<input type="checkbox" class="bs-row-check">').attr('data-key', p.key).prop('checked', !off).attr('aria-label', 'Include ' + p.name)))
        .append($name)
        .append($('<td>').append(p.email ? document.createTextNode(p.email) : missing('fa-envelope-o', 'No email')))
        .append($('<td>').append(p.mobile ? document.createTextNode(p.mobile) : missing('fa-mobile', 'No mobile')))
        .appendTo($b);
    });
    if (!rows.length) { $('<tr>').append($('<td colspan="4" class="mt-empty">').text('Nobody here.')).appendTo($b); }
    var on = rows.filter(function (p) { return !excluded[p.key]; }).length;
    $('#bs-page-all').prop('disabled', !rows.length).prop('checked', rows.length > 0 && on === rows.length).prop('indeterminate', on > 0 && on < rows.length);
    $('#bs-pager-text').text(list.length ? 'Showing ' + (page * PER + 1) + '–' + (page * PER + rows.length) + ' of ' + list.length + ' · page ' + (page + 1) + ' of ' + pages : '');
    $('#bs-prev').prop('disabled', page === 0);
    $('#bs-next').prop('disabled', page >= pages - 1);
    summary();
  }

  $(document).on('change', '.bs-row-check', function () {
    var k = $(this).attr('data-key');
    if (this.checked) { delete excluded[k]; } else { excluded[k] = true; }
    $(this).closest('tr').toggleClass('bs-row-off', !this.checked);
    var $checks = $('#bs-rows .bs-row-check'), on = $checks.filter(':checked').length;
    $('#bs-page-all').prop('checked', on === $checks.length).prop('indeterminate', on > 0 && on < $checks.length);
    summary();
  });
  $('#bs-page-all').on('change', function () {
    var on = this.checked;
    $('#bs-rows .bs-row-check').each(function () { var k = $(this).attr('data-key'); if (on) { delete excluded[k]; } else { excluded[k] = true; } });
    render();
  });
  $('.bs-tab[data-tab]').on('click', function () {
    tab = $(this).attr('data-tab'); page = 0;
    $('.bs-tab[data-tab]').attr('aria-pressed', 'false');
    $(this).attr('aria-pressed', 'true');
    render();
  });
  $('#bs-prev').on('click', function () { page--; render(); });
  $('#bs-next').on('click', function () { page++; render(); });
  $('#bs-filter').on('input', function () { filter = $.trim(this.value).toLowerCase(); page = 0; render(); });
  // Enter in a search box must not start the send.
  $('#bs-filter, #bs-add').on('keydown', function (e) { if (e.key === 'Enter' || e.keyCode === 13) { e.preventDefault(); } });

  var t;
  $('#bs-add').on('input', function () {
    var q = $.trim(this.value); clearTimeout(t);
    if (q.length < 2) { $('#bs-add-results').empty(); return; }
    t = setTimeout(function () {
      $.get(@json(route('bulk-send.search')), { audience: audience, q: q }).done(function (hits) {
        var $r = $('#bs-add-results').empty(), shown = 0;
        hits.forEach(function (h) {
          if (people.some(function (p) { return p.key === h.key; })) { return; }
          shown++;
          $('<button type="button" class="mt-btn mt-btn-sm">').append('<i class="fa fa-plus" aria-hidden="true"></i> ').append(document.createTextNode(h.name)).on('click', function () {
            people.unshift(h); added.push(h.key); $(this).remove(); tab = 'all'; page = 0;
            $('.bs-tab[data-tab]').attr('aria-pressed', 'false').filter('[data-tab=all]').attr('aria-pressed', 'true');
            render();
          }).appendTo($r);
        });
        if (!shown) { $('<span class="mt-help">').text('Nobody else found.').appendTo($r); }
      });
    }, 300);
  });

  // Same rules as MessageTemplateRenderer::smsParts.
  function smsParts(text) {
    var chars = Array.from(text).length, unicode = /[^\x00-\x7F]/.test(text);
    if (!chars) { return { chars: 0, unicode: unicode, parts: 0 }; }
    var single = unicode ? 70 : 160, multi = unicode ? 67 : 153;
    return { chars: chars, unicode: unicode, parts: chars <= single ? 1 : Math.ceil(chars / multi) };
  }

  function preview() {
    var first = included()[0];
    if (!first) { $('#bs-pv-for').text(''); $('#bs-pv-subject, #bs-pv-to, #bs-pv-sms').text('No one to preview'); $('#bs-pv-frame').attr('srcdoc', ''); return; }
    var data = $('#bs-store').serializeArray().filter(function (f) { return f.name !== '_token'; });
    data.push({ name: 'key', value: first.key }, { name: '_token', value: token });
    $('#bs-pv-for').text('for ' + first.name);
    $.post(@json(route('bulk-send.preview')), $.param(data)).done(function (o) {
      $('#bs-pv-subject').text(o.subject || '(no email)');
      $('#bs-pv-to').text(first.email ? first.name + ' <' + first.email + '>' : first.name + ' (no email)');
      $('#bs-pv-frame').attr('srcdoc', '<!doctype html><meta charset="utf-8"><body style="font-family:Arial,sans-serif;font-size:14px;color:#0f172a">' + (o.html || '') + '</body>');
      var sms = o.sms || '', p = smsParts(sms);
      $('#bs-pv-sms').text(sms || '(no SMS text)');
      $('#bs-pv-sms-count').text(p.chars ? p.chars + ' characters' + (p.unicode ? ' · Arabic/Unicode' : '') + ' · ' + p.parts + ' SMS each' : '');
    }).fail(function (x) {
      var msg = x.responseJSON && x.responseJSON.error ? x.responseJSON.error : 'Preview failed. You can still review the list.';
      $('#bs-pv-error').text(msg).prop('hidden', false);
    });
  }

  var submitted = false;
  function start() {
    var $h = $('#bs-hidden').empty();
    Object.keys(excluded).forEach(function (k) { $h.append($('<input type="hidden" name="exclude[]">').val(k)); });
    added.forEach(function (k) { if (!excluded[k]) { $h.append($('<input type="hidden" name="add[]">').val(k)); } });
    submitted = true;
    $('#bs-start').prop('disabled', true).attr('aria-busy', 'true').text('Starting…');
    document.getElementById('bs-store').submit();
  }
  $('#bs-store').on('submit', function (e) {
    e.preventDefault();
    if (submitted) { return; }
    var s = summary();
    if (!s.total) { $('#bs-store-error').text('Nobody to send to. Tick at least one person.').prop('hidden', false); return; }
    $('#bs-store-error').prop('hidden', true);
    var rows = [['People', String(s.total)]];
    if (sendEmail) { rows.push(['Emails', String(s.email)]); }
    if (sendSms) { rows.push(['SMS', String(s.mobile)]); }
    rows.push(['Time needed', minutes(s.total)]);
    window.bsDialog({ title: 'Start sending to ' + s.total + ' people?', rows: rows, ok: 'Start sending',
      warn: sendSms ? 'SMS costs money: ' + s.mobile + ' SMS will be sent.' : '', onOk: start });
  });

  render(); preview();
})();
</script>
@endsection
