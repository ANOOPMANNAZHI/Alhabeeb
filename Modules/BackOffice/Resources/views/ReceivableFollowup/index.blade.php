@extends('layouts.plms-app')
@section('css')
@include('backoffice::ReceivableFollowup._styles')
@endsection
@section('content')
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Receivables Follow-up</div></div>{{ Breadcrumbs::render('receivable-followup.index') }}</div></div>
<div class="rf"><div class="card card-box"><div class="card-body">
  {{-- summary cards: a click filters the list by that follow-up status, a second click clears it --}}
  <div class="rf-cards" role="group" aria-label="Follow-up summary">
    @foreach([
        'today'    => ["Today's follow-ups", 'fa-calendar-check-o', 'warn', 'Next follow-up date is today'],
        'overdue'  => ['Overdue follow-ups', 'fa-exclamation-circle', 'bad', 'Next follow-up date has passed'],
        'upcoming' => ['Upcoming follow-ups', 'fa-calendar', 'info', 'Next follow-up date is later'],
        'none'     => ['Not followed up', 'fa-user-times', 'muted', 'No follow-up recorded yet'],
      ] as $key => $card)
    <button type="button" class="rf-kpi rf-kpi-{{ $card[2] }}" data-status="{{ $key }}" aria-pressed="false" title="{{ $card[3] }}">
      <span class="rf-kpi-label"><i class="fa {{ $card[1] }}" aria-hidden="true"></i> {{ $card[0] }}</span>
      <span class="rf-kpi-count" data-count>—</span>
      <span class="rf-kpi-sub">Balance <span data-balance>—</span></span>
    </button>
    @endforeach
  </div>
  <div class="rf-toolbar">
    <div class="rf-search"><label for="rf-q" class="rf-sr">Search</label>
      <i class="fa fa-search" aria-hidden="true"></i>
      <input type="search" id="rf-q" name="q" class="form-control" placeholder="Search tenant, contract no or unit" autocomplete="off"></div>
    <button type="button" class="rf-btn rf-filter-btn" id="rf-filter-toggle" aria-controls="rf-filter-card" aria-expanded="false">
      <i class="fa fa-sliders" aria-hidden="true"></i> Filters <span class="rf-filter-count" id="rf-filter-count" hidden></span></button>
  </div>
  <form id="rf-filter-card" class="rf-filter-card" hidden aria-label="Filters">
    <div class="rf-filters">
    <div><label for="rf-month">Month</label>
      <select id="rf-month" name="month" class="form-control">
        @foreach(range(1, 12) as $m)<option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>@endforeach
      </select></div>
    <div><label for="rf-year">Year</label>
      <select id="rf-year" name="year" class="form-control">
        @foreach($years as $y)<option value="{{ $y }}" {{ $y === $year ? 'selected' : '' }}>{{ $y }}</option>@endforeach
      </select></div>
    <div class="rf-combo"><label for="rf-building-q">Building</label>
      <input type="text" id="rf-building-q" class="form-control" role="combobox" aria-controls="rf-building-list" aria-expanded="false"
             aria-autocomplete="list" autocomplete="off" placeholder="All buildings · type to search">
      <button type="button" class="rf-combo-clear" id="rf-building-clear" aria-label="Clear building" hidden>&times;</button>
      <ul id="rf-building-list" class="rf-combo-list" role="listbox" aria-label="Buildings" hidden></ul>
      <select id="rf-building" name="building_id" hidden aria-hidden="true" tabindex="-1">
        <option value="">All buildings</option>
        @foreach($buildings as $b)<option value="{{ $b->id }}">{{ $b->building_name }}</option>@endforeach
      </select></div>
    <div><label for="rf-mgmt">Management type</label>
      <select id="rf-mgmt" name="management_id" class="form-control">
        <option value="">All</option>
        @foreach($management as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
      </select></div>
    @if($showAre)
    <div><label for="rf-are">CA (Contract Administrator)</label>
      <select id="rf-are" name="are_id" class="form-control">
        <option value="">All CAs</option>
        @foreach($ares as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
      </select></div>
    @endif
    <div><label for="rf-tenant">Tenant</label>
      <input type="text" id="rf-tenant" name="tenant" class="form-control" autocomplete="off" placeholder="Any tenant"></div>
    <div><label for="rf-contract">Agreement</label>
      <input type="text" id="rf-contract" name="contract" class="form-control" autocomplete="off" placeholder="Any agreement no"></div>
    <div><label for="rf-unit">Unit No</label>
      <input type="text" id="rf-unit" name="unit" class="form-control" autocomplete="off" placeholder="Any unit"></div>
    <div><label for="rf-followup">Follow-up</label>
      <select id="rf-followup" name="followup" class="form-control">
        <option value="">All</option>
        @foreach($followupStatus as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
      </select></div>
    <div><label for="rf-bal-min">Balance from</label>
      <input type="number" id="rf-bal-min" name="balance_min" class="form-control" min="0" step="1" inputmode="numeric" placeholder="0"></div>
    <div><label for="rf-bal-max">Balance to</label>
      <input type="number" id="rf-bal-max" name="balance_max" class="form-control" min="0" step="1" inputmode="numeric" placeholder="Any"></div>
    <div><label for="rf-paid-to">Paid till up to</label>
      <input type="date" id="rf-paid-to" name="paid_till_to" class="form-control"></div>
    </div>
    <div class="rf-card-foot">
      <button type="button" class="rf-btn" id="rf-reset">Reset</button>
      <button type="submit" class="rf-btn rf-btn-primary" id="rf-apply">Apply filters</button>
    </div>
  </form>
  <p class="rf-applied" id="rf-applied"></p>
  <p class="rf-sr" id="rf-live" aria-live="polite"></p>
  <div id="rf-rows" aria-busy="true">
    <table class="rf-table" aria-hidden="true"><tbody>
      @for($i = 0; $i < 6; $i++)
        <tr>@for($j = 0; $j < 6; $j++)<td><div class="rf-skel" style="width:{{ [70, 55, 80, 40, 60, 50][$j] }}%"></div></td>@endfor</tr>
      @endfor
    </tbody></table>
  </div>
</div></div></div>
<div class="modal rf-dialog" id="rf-modal" tabindex="-1" role="dialog" aria-labelledby="rf-modal-title"></div>
@endsection
@section('scripts')
<script>
(function () {
  var rowsUrl = @json(route('receivable-followup.rows'));
  var refreshUrl = @json(route('receivable-followup.refresh'));
  var token = $('meta[name="csrf-token"]').attr('content');
  var state = {page: 1, sort: '', direction: ''};
  var slowTimer, retryTimer, req;
  // filter card values take effect on Apply only; these are the applied ones
  var defaults = {month: @json((string) $month), year: @json((string) $year), building_id: '', management_id: '', are_id: '',
    tenant: '', contract: '', unit: '', followup: '', balance_min: '', balance_max: '', paid_till_to: ''};
  var applied = $.extend({}, defaults);
  var fields = {month: '#rf-month', year: '#rf-year', building_id: '#rf-building', management_id: '#rf-mgmt', are_id: '#rf-are',
    tenant: '#rf-tenant', contract: '#rf-contract', unit: '#rf-unit', followup: '#rf-followup',
    balance_min: '#rf-bal-min', balance_max: '#rf-bal-max', paid_till_to: '#rf-paid-to'};

  function filterParams() {
    var p = [];
    $.each(applied, function (k, v) { p.push({name: k, value: v}); });
    p.push({name: 'q', value: $('#rf-q').val()});
    return p;
  }
  function showApplied() {
    var parts = [$('#rf-month option[value="' + applied.month + '"]').text() + ' ' + applied.year], n = 0;
    if (applied.month !== defaults.month || applied.year !== defaults.year) { n++; }
    if (applied.building_id) { n++; parts.push($('#rf-building option[value="' + applied.building_id + '"]').text()); }
    if (applied.management_id) { n++; parts.push($('#rf-mgmt option[value="' + applied.management_id + '"]').text()); }
    if (applied.are_id) { n++; parts.push('CA ' + $('#rf-are option[value="' + applied.are_id + '"]').text()); }
    if (applied.tenant) { n++; parts.push('Tenant "' + applied.tenant + '"'); }
    if (applied.contract) { n++; parts.push('Agreement "' + applied.contract + '"'); }
    if (applied.unit) { n++; parts.push('Unit "' + applied.unit + '"'); }
    if (applied.followup) { n++; parts.push($('#rf-followup option[value="' + applied.followup + '"]').text()); }
    if (applied.balance_min || applied.balance_max) { n++; parts.push('Balance ' + (applied.balance_min || '0') + ' – ' + (applied.balance_max || 'any')); }
    if (applied.paid_till_to) { n++; parts.push('Paid till up to ' + applied.paid_till_to.split('-').reverse().join('/')); }
    $('#rf-applied').text(parts.join(' · '));
    $('#rf-filter-count').text(n).prop('hidden', !n);
    $('#rf-filter-toggle').attr('aria-label', n ? 'Filters, ' + n + ' active' : 'Filters');
  }
  // ---- Building: type to search; the hidden #rf-building select keeps the chosen id ----------
  var bldAll = $('#rf-building option').filter(function () { return this.value !== ''; })
    .map(function () { return {id: this.value, name: $(this).text()}; }).get();
  var $bq = $('#rf-building-q'), $blist = $('#rf-building-list'), bActive = -1;
  function bldShowSelected() {
    var id = $('#rf-building').val();
    $bq.val(id ? $('#rf-building option[value="' + id + '"]').text() : '');
    $('#rf-building-clear').prop('hidden', !id);
  }
  function bldClose() { $blist.prop('hidden', true).empty(); $bq.attr('aria-expanded', 'false').removeAttr('aria-activedescendant'); bActive = -1; }
  function bldOpen() {
    var q = $.trim($bq.val()).toLowerCase(), n = 0;
    $blist.empty();
    $.each(bldAll, function (i, b) {
      if (q && b.name.toLowerCase().indexOf(q) === -1) { return; }
      if (n++ >= 50) { return false; } // keep the list short; typing narrows it
      $('<li role="option" class="rf-combo-opt">').attr('id', 'rf-bld-' + b.id).attr('data-id', b.id).text(b.name).appendTo($blist);
    });
    if (!n) { $('<li class="rf-combo-none" role="presentation">').text('No building matches "' + $bq.val() + '"').appendTo($blist); }
    $blist.prop('hidden', false); $bq.attr('aria-expanded', 'true'); bActive = -1;
  }
  function bldPick(id) { $('#rf-building').val(id || ''); bldShowSelected(); bldClose(); }
  function bldMove(step) {
    var $o = $blist.find('.rf-combo-opt');
    if (!$o.length) { return; }
    bActive = (bActive + step + $o.length) % $o.length;
    $o.removeClass('is-active').attr('aria-selected', 'false');
    var $a = $o.eq(bActive).addClass('is-active').attr('aria-selected', 'true');
    $bq.attr('aria-activedescendant', $a.attr('id'));
    $a[0].scrollIntoView({block: 'nearest'});
  }
  $bq.on('focus', function () { this.select(); bldOpen(); })
    .on('input', function () { $('#rf-building').val(''); $('#rf-building-clear').prop('hidden', true); bldOpen(); })
    .on('keydown', function (e) {
      if (e.key === 'ArrowDown') { e.preventDefault(); if ($blist.prop('hidden')) { bldOpen(); } bldMove(1); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); bldMove(-1); }
      else if (e.key === 'Enter' && !$blist.prop('hidden')) {
        e.preventDefault();
        var $a = $blist.find('.rf-combo-opt.is-active');
        if (!$a.length && $blist.find('.rf-combo-opt').length === 1) { $a = $blist.find('.rf-combo-opt'); }
        if ($a.length) { bldPick($a.attr('data-id')); }
      } else if (e.key === 'Escape' && !$blist.prop('hidden')) { e.stopPropagation(); bldClose(); bldShowSelected(); }
    })
    .on('blur', function () { setTimeout(function () { if (!$blist.is(':hover')) { bldClose(); bldShowSelected(); } }, 150); });
  $blist.on('mousedown', '.rf-combo-opt', function (e) { e.preventDefault(); bldPick($(this).attr('data-id')); });
  $('#rf-building-clear').on('click', function () { bldPick(''); $bq.trigger('focus'); });

  function setCard(open) {
    if (open) { $.each(fields, function (k, sel) { $(sel).val(applied[k]); }); bldShowSelected(); }
    $('#rf-filter-card').prop('hidden', !open);
    $('#rf-filter-toggle').attr('aria-expanded', open ? 'true' : 'false').toggleClass('is-open', open);
    if (open) { $('#rf-month').trigger('focus'); }
  }
  $('#rf-filter-toggle').on('click', function () { setCard($('#rf-filter-card').prop('hidden')); });
  $('#rf-filter-card').on('submit', function (e) {
    e.preventDefault();
    $.each(fields, function (k, sel) { applied[k] = String($(sel).val() || ''); });
    setCard(false); showApplied(); state.page = 1; load();
    $('#rf-filter-toggle').trigger('focus');
  });
  $('#rf-reset').on('click', function () {
    applied = $.extend({}, defaults);
    setCard(false); showApplied(); state.page = 1; load();
    $('#rf-filter-toggle').trigger('focus');
  });
  $(document).on('keydown', function (e) {
    if (e.key === 'Escape' && !$('#rf-filter-card').prop('hidden')) { setCard(false); $('#rf-filter-toggle').trigger('focus'); }
  });

  function params() {
    var p = filterParams();
    p.push({name: 'page', value: state.page});
    if (state.sort) { p.push({name: 'sort', value: state.sort}, {name: 'direction', value: state.direction}); }
    return $.param(p);
  }
  function load() {
    if (req) { req.abort(); }
    clearTimeout(slowTimer);
    clearTimeout(retryTimer);
    var $rows = $('#rf-rows').attr('aria-busy', 'true');
    $rows.children('table, .table-responsive').addClass('rf-busy');
    // first open of a month runs the full calculation (about 15 seconds): say so instead of a frozen screen
    slowTimer = setTimeout(function () {
      if (!$rows.find('.rf-calc').length) {
        $rows.prepend('<p class="rf-calc" role="status"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Calculating figures for this month. This can take about 15 seconds.</p>');
      }
    }, 1200);
    req = $.get(rowsUrl + '?' + params()).done(function (html, status, x) {
      if (x.status === 202) {
        // another request is calculating this month: show it and look again shortly
        if (!$rows.find('.rf-calc').length) {
          $rows.prepend('<p class="rf-calc" role="status"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Calculating figures for this month. This can take about 15 seconds.</p>');
        }
        clearTimeout(retryTimer);
        retryTimer = setTimeout(load, 3000);
        return;
      }
      $rows.html(html);
      updateCards();
      $('#rf-live').text($rows.find('.rf-strip').attr('data-summary') || '');
    }).fail(function (x) {
      if (x.statusText !== 'abort') {
        $rows.html('<div class="rf-empty"><strong>Could not load the list.</strong>Check your connection and <button type="button" class="rf-link" id="rf-retry">try again</button>.</div>');
      }
    }).always(function (a, b, c) {
      clearTimeout(slowTimer);
      var x = c && c.status !== undefined ? c : a;
      if (!x || x.status !== 202) { $rows.attr('aria-busy', 'false').find('.rf-calc').remove(); }
    });
  }
  // ---- summary cards -------------------------------------------------------------------
  function markCards() {
    $('.rf-kpi').each(function () {
      var on = applied.followup === $(this).attr('data-status');
      $(this).attr('aria-pressed', on ? 'true' : 'false').toggleClass('is-on', on);
    });
  }
  function updateCards() {
    var cards = $('#rf-cards-data').data('cards') || {};
    $('.rf-kpi').each(function () {
      var c = cards[$(this).attr('data-status')];
      $(this).find('[data-count]').text(c ? Number(c.count).toLocaleString('en-US') : '—');
      $(this).find('[data-balance]').text(c ? c.balance : '—');
    });
    markCards();
  }
  $(document).on('click', '.rf-kpi', function () {
    var key = $(this).attr('data-status');
    applied.followup = applied.followup === key ? '' : key;
    $('#rf-followup').val(applied.followup);
    markCards(); showApplied(); state.page = 1; load();
  });

  var typing;
  $('#rf-q').on('input', function () { clearTimeout(typing); typing = setTimeout(function () { state.page = 1; load(); }, 400); });
  $(document).on('click', '#rf-retry', load);
  $(document).on('click', '#rf-rows .pagination a', function (e) {
    e.preventDefault();
    var m = /[?&]page=(\d+)/.exec($(this).attr('href'));
    state.page = m ? Number(m[1]) : 1; load();
    $('html, body').scrollTop($('.rf-toolbar').offset().top - 80);
  });
  $(document).on('click', '#rf-rows .rf-sort', function (e) {
    e.preventDefault();
    var s = $(this).attr('data-sort');
    state.direction = (state.sort === s && state.direction === 'desc') ? 'asc' : (state.sort === s ? 'desc' : $(this).attr('data-first'));
    state.sort = s; state.page = 1; load();
  });
  $(document).on('click', '#rf-refresh', function () {
    var $b = $(this).prop('disabled', true).text('Refreshing…');
    $.post(refreshUrl, $.param(filterParams()) + '&_token=' + encodeURIComponent(token))
      .done(function () { load(); })
      .fail(function (x) {
        $b.prop('disabled', false).text('Refresh');
        $('#rf-live').text(x.status === 429 ? 'Too many refreshes. Please wait a minute and try again.' : 'Could not refresh the figures. Please try again.');
      });
  });
  // ---- Follow-up / View popups ---------------------------------------------
  var opener = null, modalSeq = 0;
  function openModal(url, from) {
    opener = from;
    var seq = ++modalSeq; // only the latest click may fill the popup
    $('#rf-modal').html('<div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-body" role="status">Loading…</div></div></div>').modal('show');
    $.get(url).done(function (html) {
      if (seq !== modalSeq) { return; }
      $('#rf-modal').html(html);
      $('#rf-comment').trigger('focus');
    }).fail(function (x) {
      if (seq !== modalSeq) { return; }
      $('#rf-modal .modal-body').text(x.status === 403 ? 'You cannot open this agreement.' : 'Could not load. Please close and try again.');
    });
  }
  $('#rf-modal').on('hide.bs.modal', function () { modalSeq++; });
  $(document).on('click', '.rf-followup', function (e) { e.stopPropagation(); openModal($(this).attr('data-url'), this); });
  // Follow-up from the history: the form replaces the history in the same popup; focus still returns to the row
  $(document).on('click', '.rf-tl-followup', function () { openModal($(this).attr('data-url'), opener); });
  // the whole row opens the history (click, or Enter / Space when the row has focus); not while selecting text
  $(document).on('click', '#rf-rows .rf-row', function (e) {
    if ($(e.target).closest('a, button, input, select, textarea').length || String(window.getSelection())) { return; }
    openModal($(this).attr('data-timeline'), this);
  });
  $(document).on('keydown', '#rf-rows .rf-row', function (e) {
    if ((e.key === 'Enter' || e.key === ' ') && e.target === this) { e.preventDefault(); openModal($(this).attr('data-timeline'), this); }
  });
  // back to the button that opened the popup
  $('#rf-modal').on('hidden.bs.modal', function () { if (opener && document.body.contains(opener)) { opener.focus(); } });
  $(document).on('input', '#rf-comment', function () { $('#rf-comment-count').text(this.value.length + ' / 1000'); });

  function fieldError(name, msg) {
    var $f = $('#rf-form [name="' + name + '"]').addClass('is-invalid').attr('aria-invalid', 'true');
    $('#rf-' + name + '-error').text(msg).prop('hidden', false);
    return $f;
  }
  $(document).on('submit', '#rf-form', function (e) {
    e.preventDefault();
    var form = this, id = form.action.split('/').pop();
    var $btn = $(form).find('button[type=submit]');
    $(form).find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
    $(form).find('.rf-field-error').prop('hidden', true).empty();
    $(form).find('.rf-error').prop('hidden', true).empty();
    if (!$.trim($('#rf-comment').val())) { fieldError('comment', 'Enter a comment about the call.').trigger('focus'); return; }
    $btn.prop('disabled', true).text('Saving…');
    $.ajax({method: 'POST', url: form.action, data: $(form).serialize(), headers: {'Accept': 'application/json'}})
      .done(function (r) {
        $('#rf-last-' + id).empty().append(document.createTextNode(r.last)).append($('<span class="rf-sub">').text(r.by));
        var $p = $('#rf-promise-' + id).empty().toggleClass('rf-overdue', !!r.overdue).text(r.promised || '—');
        if (r.overdue) { $p.append('<span class="rf-flag">Overdue</span>'); }
        var $row = $('#rf-row-' + id).removeClass('rf-row-saved');
        setTimeout(function () { $row.addClass('rf-row-saved'); }, 0);
        $('#rf-live').text('Follow-up saved.');
        $('#rf-modal').modal('hide');
      })
      .fail(function (x) {
        var r = x.responseJSON || {}, $first = null;
        if (x.status === 422 && r.errors) {
          $.each(r.errors, function (k, v) { var $f = fieldError(k, v[0]); $first = $first || $f; });
          if ($first) { $first.trigger('focus'); }
        } else {
          $(form).find('.rf-error').text(x.status === 403 ? 'You cannot add a follow-up for this agreement.' : (r.message || 'Could not save. Please try again.')).prop('hidden', false);
        }
      })
      .always(function () { $btn.prop('disabled', false).text('Save follow-up'); });
  });

  window.rfReload = load;
  showApplied();
  load();
})();
</script>
@endsection
