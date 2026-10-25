@extends('layouts.plms-app')
@section('css')
@include('backoffice::BulkSend._styles')
<script src="{{ asset('public/js/tinymce.js') }}"></script>
@endsection
@section('content')
@php
  $bsTplLabels = \Modules\BackOffice\Services\MessagePlaceholders::AUDIENCES;
  $bsOldTemplate = (string) old('template_id', '');
  $bsOldLocations = array_map('strval', (array) old('locations', []));
  $bsOldRoles = array_map('strval', (array) old('roles', []));
  // Errors next to their section: who gets it -> Recipients, everything else -> Message.
  $bsRecipientKeys = ['audience', 'recipients', 'roles', 'locations', 'buildings'];
  $bsErrMessage = [];
  $bsErrRecipients = [];
  foreach ($errors->getMessages() as $key => $messages) {
      foreach ($messages as $m) {
          if (in_array(explode('.', $key)[0], $bsRecipientKeys, true)) { $bsErrRecipients[] = $m; } else { $bsErrMessage[] = $m; }
      }
  }
@endphp
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">New send</div></div>{{ Breadcrumbs::render('bulk-send.create') }}</div></div>
<div class="mt bs"><div class="card card-box"><div class="card-body">
@include('backoffice::BulkSend._stepper', ['step' => 1, 'sticky' => true])

@if($errors->any())
  <div class="bs-errors" id="bs-errors" role="alert" tabindex="-1">
    <strong><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Please fix {{ count($bsErrMessage) + count($bsErrRecipients) === 1 ? 'this' : 'these' }} before Review:</strong>
    <ul>
      @foreach($bsErrMessage as $m)<li><a href="#bs-sec-message">Message</a>: {{ $m }}</li>@endforeach
      @foreach($bsErrRecipients as $m)<li><a href="#bs-sec-recipients">Recipients</a>: {{ $m }}</li>@endforeach
    </ul>
  </div>
@endif

<form method="post" action="{{ route('bulk-send.review') }}" id="bs-form" class="bs-compose">
  @csrf
  <div class="bs-main">
    {{-- ① Message --}}
    <section class="bs-sec" id="bs-sec-message" aria-labelledby="bs-h-message">
      <div class="bs-sec-head"><h2 id="bs-h-message" tabindex="-1">Message</h2></div>
      @if($bsErrMessage)
        <div class="bs-sec-error" id="bs-err-message"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>
          <div>@foreach($bsErrMessage as $m)<div>{{ $m }}</div>@endforeach</div></div>
      @endif

      <fieldset>
        <legend>Choose a Bulk template or write a one-off message</legend>
        <div class="bs-choices">
          @foreach($templates as $t)
            <label class="bs-choice" data-audience="{{ $t->audience }}">
              <input type="radio" name="template_id" value="{{ $t->id }}" data-sms="{{ $t->sms_body ? 1 : 0 }}" data-email="{{ ($t->email_subject && $t->email_body) ? 1 : 0 }}" {{ $bsOldTemplate === (string) $t->id ? 'checked' : '' }}>
              @php
                $bsTplAud = isset($bsTplLabels[$t->audience]) ? $bsTplLabels[$t->audience] : $t->audience;
                $bsTplCh = implode(' + ', array_filter([($t->email_subject && $t->email_body) ? 'Email' : null, $t->sms_body ? 'SMS' : null]));
              @endphp
              <span class="bs-choice-body">
                <span class="bs-choice-title">{{ $t->name }}</span>
                <span class="bs-choice-meta">{{ $bsTplAud }}@if($bsTplCh) · {{ $bsTplCh }}@endif</span>
                <span class="bs-choice-note" hidden>(for {{ $bsTplAud }} only)</span>
              </span>
            </label>
          @endforeach
          <label class="bs-choice bs-choice-new">
            <input type="radio" name="template_id" value="" {{ $bsOldTemplate === '' ? 'checked' : '' }}>
            <span class="bs-choice-body">
              <span class="bs-choice-title">Write a one-off message</span>
              <span class="bs-choice-meta">Your own subject, body and SMS text for this send only</span>
            </span>
          </label>
        </div>
        <p class="mt-help" style="margin:8px 0 0">Create Bulk templates on Mail → Templates.</p>
      </fieldset>

      <div id="bs-oneoff" class="bs-oneoff">
        <div class="mt-field"><label for="bs-subject">Email subject</label>
          <div class="mt-row">
            <input name="email_subject" id="bs-subject" class="mt-input" maxlength="255" value="{{ old('email_subject') }}">
            <select class="mt-input mt-ph" data-target="#bs-subject" aria-label="Insert placeholder into the subject"></select>
          </div>
        </div>
        <div class="mt-field"><label for="bs-body">Email body</label><textarea name="email_body" id="bs-body">{{ old('email_body') }}</textarea></div>
        <div class="mt-field">
          <div class="mt-row" style="margin-bottom:6px">
            <label for="bs-sms" style="flex:1;margin:0">SMS text</label>
            <select class="mt-input mt-ph" data-target="#bs-sms" aria-label="Insert placeholder into the SMS"></select>
          </div>
          <textarea name="sms_body" id="bs-sms" class="mt-input" rows="3" maxlength="1000">{{ old('sms_body') }}</textarea>
          <div class="mt-count" id="bs-sms-count" aria-live="polite"></div>
        </div>
      </div>

      <fieldset>
        <legend>Send by</legend>
        <div class="bs-toggles">
          <label class="bs-toggle"><input type="checkbox" name="send_email" value="1" id="bs-email" {{ (session()->hasOldInput() ? old('send_email') : true) ? 'checked' : '' }}>
            <span class="bs-track" aria-hidden="true"></span><span class="bs-toggle-text">Email</span></label>
          <label class="bs-toggle"><input type="checkbox" name="send_sms" value="1" id="bs-send-sms" {{ old('send_sms') ? 'checked' : '' }} aria-describedby="bs-sms-help">
            <span class="bs-track" aria-hidden="true"></span><span class="bs-toggle-text">SMS (costs money)</span></label>
        </div>
        <p class="mt-help" id="bs-sms-help" style="margin:8px 0 0"></p>
        <p class="mt-help" id="bs-email-help" style="margin:4px 0 0" hidden></p>
      </fieldset>

      <button type="button" class="mt-btn" id="bs-next">Next: Recipients <i class="fa fa-arrow-down" aria-hidden="true"></i></button>
    </section>

    {{-- ② Recipients --}}
    <section class="bs-sec" id="bs-sec-recipients" aria-labelledby="bs-h-recipients">
      <div class="bs-sec-head"><h2 id="bs-h-recipients" tabindex="-1">Recipients</h2></div>
      @if($bsErrRecipients)
        <div class="bs-sec-error" id="bs-err-recipients"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>
          <div>@foreach($bsErrRecipients as $m)<div>{{ $m }}</div>@endforeach</div></div>
      @endif

      <fieldset>
        <legend>Who gets it</legend>
        <div class="bs-choices">
          @foreach(\Modules\BackOffice\Entities\BulkSend::AUDIENCE_LABELS as $value => $label)
            <label class="bs-choice">
              <input type="radio" name="audience" value="{{ $value }}" {{ old('audience', 'tenant_active') === $value ? 'checked' : '' }}>
              <span class="bs-choice-body">
                <span class="bs-choice-title">{{ $label }}</span>
                <span class="bs-choice-meta"><span class="bs-group-n" data-count-for="{{ $value }}">…</span> people</span>
              </span>
            </label>
          @endforeach
        </div>
        <p class="mt-help" style="margin:8px 0 0">Counts refresh every 5 minutes; the Summary is always exact.</p>
      </fieldset>

      <div id="bs-place">
        <p class="mt-help" style="margin:0 0 16px">Numbers next to each location/building count each person once, by their latest contract; ticking still includes everyone with a contract there.</p>
        <fieldset class="bs-pick" id="bs-loc">
          <legend>Locations</legend>
          <div class="bs-pick-head">
            <label for="bs-loc-q" class="bs-sr">Search locations</label>
            <input type="search" id="bs-loc-q" class="mt-input bs-pick-q" data-list="#bs-loc-list" autocomplete="off" placeholder="Search locations">
            <div class="bs-pick-tools">
              <button type="button" class="bs-link bs-pick-all" data-list="#bs-loc-list">Select all</button>
              <span class="bs-pick-sep" aria-hidden="true">·</span>
              <button type="button" class="bs-link bs-pick-none" data-list="#bs-loc-list">Clear</button>
              <span class="bs-pick-status" id="bs-loc-status" aria-live="polite"></span>
            </div>
          </div>
          <div class="bs-pick-list" id="bs-loc-list" data-none="None ticked = all locations">
            @foreach($locations as $l)
              <label class="bs-pick-item" data-name="{{ mb_strtolower($l->locations_name, 'UTF-8') }}">
                <input type="checkbox" name="locations[]" value="{{ $l->id }}" {{ in_array((string) $l->id, $bsOldLocations, true) ? 'checked' : '' }}>
                <span class="bs-pick-name">{{ $l->locations_name }}</span>
                <span class="bs-pick-n" data-location="{{ $l->id }}"></span>
              </label>
            @endforeach
            <p class="bs-pick-empty" hidden>No location matches the search.</p>
          </div>
        </fieldset>

        <fieldset class="bs-pick" id="bs-bld">
          <legend>Buildings (optional)</legend>
          <div class="bs-pick-head">
            <label for="bs-bld-q" class="bs-sr">Search buildings</label>
            <input type="search" id="bs-bld-q" class="mt-input bs-pick-q" data-list="#bs-bld-list" autocomplete="off" placeholder="Search buildings">
            <div class="bs-pick-tools">
              <button type="button" class="bs-link bs-pick-all" data-list="#bs-bld-list">Select all</button>
              <span class="bs-pick-sep" aria-hidden="true">·</span>
              <button type="button" class="bs-link bs-pick-none" data-list="#bs-bld-list">Clear</button>
              <span class="bs-pick-status" id="bs-bld-status" aria-live="polite"></span>
            </div>
          </div>
          <div class="bs-pick-list" id="bs-bld-list" data-none="None ticked = all buildings of the chosen locations"></div>
        </fieldset>
      </div>

      <fieldset id="bs-roles-box" hidden>
        <legend>Roles</legend>
        <div class="bs-pick-list bs-roles">
          @foreach($roles as $r)
            <label class="bs-pick-item"><input type="checkbox" name="roles[]" value="{{ $r }}" {{ in_array((string) $r, $bsOldRoles, true) ? 'checked' : '' }}>
              <span class="bs-pick-name">{{ $r }}</span> <span class="bs-pick-n" data-role="{{ $r }}"></span></label>
          @endforeach
        </div>
        <p class="mt-help" id="bs-roles-status" style="margin:8px 0 0" aria-live="polite">None ticked = all staff.</p>
      </fieldset>
    </section>
  </div>

  {{-- Summary --}}
  <aside class="bs-side" aria-labelledby="bs-h-summary">
    <div class="bs-summary" id="bs-summary-card" data-per-minute="{{ (int) $perMinute }}">
      <h2 id="bs-h-summary">Summary</h2>
      <dl>
        <div class="bs-summary-total"><dt>People</dt><dd id="bs-s-total">…</dd></div>
        <div><dt>With email</dt><dd id="bs-s-email">…</dd></div>
        <div id="bs-s-mobile-row" hidden><dt>With mobile (SMS)</dt><dd id="bs-s-mobile">…</dd></div>
        <div><dt>No email</dt><dd id="bs-s-noemail">…</dd></div>
        <div><dt>Time needed</dt><dd id="bs-s-time">…</dd></div>
      </dl>
      <p class="mt-help" style="margin:0 0 16px">Estimate at {{ (int) $perMinute }} messages a minute.</p>
      <p class="bs-sr" id="bs-s-live" aria-live="polite"></p>
      <button type="submit" class="mt-btn mt-btn-primary bs-btn-block" id="bs-review-btn">Review <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
      <a href="{{ route('bulk-send.index') }}" class="mt-btn bs-btn-block">Back</a>
    </div>
  </aside>
</form>
</div></div></div>
@endsection
@section('scripts')
<script>
(function () {
  var buildings = @json($buildings);
  var placeholders = @json($placeholders);
  var oldBuildings = @json(array_map('intval', (array) old('buildings', [])));
  var perMinute = {{ (int) $perMinute }};
  var URLS = { count: @json(route('bulk-send.count')), audiences: @json(route('bulk-send.audience-counts')), places: @json(route('bulk-send.place-counts')) };
  var token = $('meta[name=csrf-token]').attr('content');
  var placeCache = {};

  function audience() { return $('input[name=audience]:checked').val(); }
  function tplAudience(a) { return a === 'landlord' ? 'landlord' : (a === 'staff' ? 'staff' : 'tenant'); }
  function smsOn() { return $('#bs-send-sms').prop('checked'); }

  // ---- stepper -------------------------------------------------------------
  function setStep(n) {
    $('#bs-stepper li').each(function () {
      if (Number($(this).attr('data-step')) === n) { $(this).attr('aria-current', 'step'); } else { $(this).removeAttr('aria-current'); }
    });
  }
  // Step 2 once the Recipients section reaches the top third of the screen (or the page can't scroll further).
  function stepFromScroll() {
    var top = document.getElementById('bs-sec-recipients').getBoundingClientRect().top;
    var atEnd = window.innerHeight + window.pageYOffset >= document.documentElement.scrollHeight - 2;
    setStep(top <= window.innerHeight / 3 || atEnd ? 2 : 1);
  }
  var stepTick = false;
  $(window).on('scroll resize', function () {
    if (stepTick) { return; }
    stepTick = true;
    window.requestAnimationFrame(function () { stepTick = false; stepFromScroll(); });
  });
  $('#bs-sec-message').on('focusin', function () { setStep(1); });
  $('#bs-sec-recipients').on('focusin', function () { setStep(2); });
  function goTo(id, headingId) {
    var smooth = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.getElementById(id).scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
    document.getElementById(headingId).focus({ preventScroll: true });
  }
  $('#bs-next').on('click', function () { setStep(2); goTo('bs-sec-recipients', 'bs-h-recipients'); });
  $('#bs-stepper a').on('click', function (e) {
    var id = $(this).attr('href').slice(1);
    e.preventDefault();
    if (history.replaceState) { history.replaceState(null, '', '#' + id); }
    setStep(id === 'bs-sec-recipients' ? 2 : 1);
    goTo(id, id === 'bs-sec-recipients' ? 'bs-h-recipients' : 'bs-h-message');
  });

  // ---- searchable checkbox lists ------------------------------------------
  function applySearch($list) {
    var q = $.trim($('.bs-pick-q[data-list="#' + $list.attr('id') + '"]').val() || '').toLowerCase(), shown = 0;
    $list.find('.bs-pick-item').each(function () {
      var hit = !q || String($(this).attr('data-name') || '').indexOf(q) !== -1;
      $(this).prop('hidden', !hit);
      if (hit) { shown++; }
    });
    $list.find('.bs-pick-empty').prop('hidden', shown > 0);
  }
  function pickStatus($list) {
    var n = $list.find('input:checked').length;
    $('#' + $list.attr('id').replace('-list', '-status')).text(n ? n + ' ticked' : $list.attr('data-none'));
  }
  $('.bs-pick-q').on('input', function () { applySearch($($(this).attr('data-list'))); });
  $('.bs-pick-all, .bs-pick-none').on('click', function () {
    var $list = $($(this).attr('data-list')), on = $(this).hasClass('bs-pick-all');
    $list.find('.bs-pick-item').each(function () { if (!on || !this.hidden) { $(this).find('input').prop('checked', on); } });
    $list.find('input').first().trigger('change');
    if (!$list.find('input').length) { pickStatus($list); }
  });

  function renderBuildings() {
    var locs = $('input[name="locations[]"]:checked').map(function () { return Number(this.value); }).get();
    var keep = $('input[name="buildings[]"]:checked').map(function () { return Number(this.value); }).get().concat(oldBuildings);
    oldBuildings = [];
    var $list = $('#bs-bld-list').empty();
    buildings.forEach(function (b) {
      if (locs.length && locs.indexOf(Number(b.location_id)) === -1) { return; }
      $('<label class="bs-pick-item">').attr('data-name', String(b.building_name || '').toLowerCase())
        .append($('<input type="checkbox" name="buildings[]">').val(b.id).prop('checked', keep.indexOf(Number(b.id)) !== -1))
        .append($('<span class="bs-pick-name">').text(b.building_name))
        .append($('<span class="bs-pick-n">').attr('data-building', b.id))
        .appendTo($list);
    });
    $('<p class="bs-pick-empty">').text($list.children().length ? 'No building matches the search.' : 'No buildings in the chosen locations.').appendTo($list);
    applySearch($list);
    pickStatus($list);
    showPlaceCounts();
  }

  // ---- counts per audience / place ----------------------------------------
  function setN($el, n) {
    $el.empty();
    if (n === null || n === undefined) { return; }
    $el.append(document.createTextNode(String(n))).append($('<span class="bs-sr">').text(' people'));
  }
  function showPlaceCounts() {
    var c = placeCache[audience()];
    if (!c) { return; }
    if (c.roles) {
      $('#bs-form [data-role]').each(function () { setN($(this), c.roles[$(this).attr('data-role')] || 0); });
    } else {
      $('#bs-form [data-location]').each(function () { setN($(this), c.locations[$(this).attr('data-location')] || 0); });
      $('#bs-form [data-building]').each(function () { setN($(this), c.buildings[$(this).attr('data-building')] || 0); });
    }
  }
  function loadPlaceCounts() {
    var a = audience();
    if (!a) { return; }
    if (placeCache[a]) { showPlaceCounts(); return; }
    $('#bs-form [data-location], #bs-form [data-building], #bs-form [data-role]').empty();
    $.get(URLS.places, { audience: a }).done(function (c) { placeCache[a] = c; if (audience() === a) { showPlaceCounts(); } });
  }
  $.get(URLS.audiences).done(function (c) {
    $('#bs-form [data-count-for]').each(function () { var n = c[$(this).attr('data-count-for')]; $(this).text(n === undefined ? '—' : n); });
  }).fail(function () { $('#bs-form [data-count-for]').text('—'); });

  // ---- summary --------------------------------------------------------------
  var timer, lastCount = null;
  function paintSummary() {
    var c = lastCount;
    $('#bs-s-mobile-row').prop('hidden', !smsOn());
    if (!c) { return; }
    $('#bs-s-total').text(c.total);
    $('#bs-s-email').text(c.email);
    $('#bs-s-mobile').text(c.mobile);
    $('#bs-s-noemail').text(Math.max(0, c.total - c.email));
    $('#bs-s-time').text(c.total ? 'about ' + Math.max(1, Math.ceil(c.total / perMinute)) + ' min' : '—');
    $('#bs-s-live').text(c.total + ' people, ' + c.email + ' with email' + (smsOn() ? ', ' + c.mobile + ' with mobile' : ''));
  }
  function count() {
    clearTimeout(timer);
    timer = setTimeout(function () {
      $('#bs-s-total, #bs-s-email, #bs-s-mobile, #bs-s-noemail, #bs-s-time').text('…');
      $.post(URLS.count, $('#bs-form').serialize() + '&_token=' + encodeURIComponent(token)).done(function (c) {
        lastCount = c; paintSummary();
      }).fail(function () { $('#bs-s-total, #bs-s-email, #bs-s-mobile, #bs-s-noemail, #bs-s-time').text('—'); });
    }, 300);
  }

  // ---- message --------------------------------------------------------------
  function fillPlaceholders() {
    var list = placeholders[tplAudience(audience())] || {};
    $('.mt-ph').each(function () {
      var $s = $(this).empty().append($('<option>').val('').text('Insert placeholder…'));
      Object.keys(list).forEach(function (name) { if (!list[name].block) { $s.append($('<option>').val(name).text(list[name].label + '  {' + name + '}')); } });
    });
  }
  // Same rules as MessageTemplateRenderer::smsParts.
  function smsParts(text) {
    var chars = Array.from(text).length, unicode = /[^\x00-\x7F]/.test(text);
    if (!chars) { return { chars: 0, unicode: unicode, parts: 0 }; }
    var single = unicode ? 70 : 160, multi = unicode ? 67 : 153;
    return { chars: chars, unicode: unicode, parts: chars <= single ? 1 : Math.ceil(chars / multi) };
  }
  function syncTemplate() {
    var $r = $('input[name=template_id]:checked');
    var oneOff = !$r.length || !$r.val();
    $('#bs-oneoff').prop('hidden', !oneOff);
    var hasSms = oneOff ? $.trim($('#bs-sms').val()) !== '' : $r.attr('data-sms') === '1';
    $('#bs-send-sms').prop('disabled', !hasSms);
    if (!hasSms) { $('#bs-send-sms').prop('checked', false); }
    $('#bs-sms-help').text(hasSms ? 'SMS is off unless you tick it. Each SMS costs money.' : 'SMS is not possible: this message has no SMS text.');
    var noEmail = !oneOff && $r.attr('data-email') !== '1';
    $('#bs-email-help').prop('hidden', !noEmail).text(noEmail ? 'This template has no email version.' : '');
    var p = smsParts($('#bs-sms').val() || '');
    $('#bs-sms-count').text(p.chars ? p.chars + ' characters' + (p.unicode ? ' · Arabic/Unicode (70 per SMS)' : '') + ' · ' + p.parts + ' SMS' : 'Empty — no SMS version.');
    paintSummary();
  }
  function syncFit() {
    var ta = tplAudience(audience());
    $('.bs-choice[data-audience]').each(function () {
      var a = $(this).attr('data-audience'), fit = a === 'any' || a === ta;
      $(this).toggleClass('is-unfit', !fit).find('.bs-choice-note').prop('hidden', fit);
    });
  }
  function syncAudience() {
    var staff = audience() === 'staff';
    $('#bs-place').prop('hidden', staff);
    $('#bs-roles-box').prop('hidden', !staff);
    fillPlaceholders();
    syncFit();
    loadPlaceCounts();
    count();
  }
  function rolesStatus() {
    var n = $('input[name="roles[]"]:checked').length;
    $('#bs-roles-status').text(n ? n + ' role(s) ticked' : 'None ticked = all staff.');
  }

  $(document).on('change', 'input[name="locations[]"]', function () { pickStatus($('#bs-loc-list')); renderBuildings(); count(); });
  $(document).on('change', 'input[name="buildings[]"]', function () { pickStatus($('#bs-bld-list')); count(); });
  $('input[name="roles[]"]').on('change', function () { rolesStatus(); count(); });
  $('input[name=audience]').on('change', syncAudience);
  $('input[name=template_id]').on('change', syncTemplate);
  $('#bs-sms').on('input', syncTemplate);
  $('#bs-send-sms').on('change', paintSummary);
  $('.mt-ph').on('change', function () {
    var name = $(this).val(); if (!name) { return; }
    var el = $($(this).data('target'))[0]; var v = el.value, s = el.selectionStart || v.length;
    el.value = v.slice(0, s) + '{' + name + '}' + v.slice(el.selectionEnd || s); $(this).val(''); syncTemplate();
  });

  tinymce.init({ selector: '#bs-body', menubar: false, statusbar: false, branding: false, height: 300,
    plugins: 'link lists', toolbar: 'undo redo | bold italic underline | bullist numlist | link | placeholders',
    setup: function (editor) {
      editor.ui.registry.addMenuButton('placeholders', { text: 'Placeholder', fetch: function (cb) {
        var list = placeholders[tplAudience(audience())] || {};
        cb(Object.keys(list).filter(function (n) { return !list[n].block; }).map(function (n) {
          return { type: 'menuitem', text: list[n].label + '  {' + n + '}', onAction: function () { editor.insertContent('{' + n + '}'); } };
        }));
      } });
    } });

  $('#bs-form').on('submit', function () {
    tinymce.triggerSave();
    var $b = $('#bs-review-btn');
    setTimeout(function () { $b.prop('disabled', true).attr('aria-busy', 'true').text('Opening review…'); }, 0);
  });
  // Back button (page restored from the cache): the Review button works again.
  window.addEventListener('pageshow', function () {
    $('#bs-review-btn').prop('disabled', false).removeAttr('aria-busy').empty().append(document.createTextNode('Review ')).append('<i class="fa fa-arrow-right" aria-hidden="true"></i>');
  });

  pickStatus($('#bs-loc-list')); rolesStatus(); renderBuildings(); syncTemplate(); syncAudience();
  if ($('#bs-errors').length) { $('#bs-errors').focus(); }
  // Opened as .../create#bs-sec-recipients: the browser jumps there on load, the stepper follows.
  stepFromScroll();
  $(window).on('load', stepFromScroll);
})();
</script>
@endsection
