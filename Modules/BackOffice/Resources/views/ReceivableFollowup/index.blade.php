@extends('layouts.plms-app')
@section('css')
@include('backoffice::ReceivableFollowup._styles')
@endsection
@section('content')
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Receivables Follow-up</div></div>{{ Breadcrumbs::render('receivable-followup.index') }}</div></div>
<div class="rf"><div class="card card-box"><div class="card-body">
  <form id="rf-filters" class="rf-filters" onsubmit="return false" aria-label="Filters">
    <div><label for="rf-month">Month</label>
      <select id="rf-month" name="month" class="form-control">
        @foreach(range(1, 12) as $m)<option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>@endforeach
      </select></div>
    <div><label for="rf-year">Year</label>
      <select id="rf-year" name="year" class="form-control">
        @foreach($years as $y)<option value="{{ $y }}" {{ $y === $year ? 'selected' : '' }}>{{ $y }}</option>@endforeach
      </select></div>
    <div><label for="rf-building">Building</label>
      <select id="rf-building" name="building_id" class="form-control">
        <option value="">All buildings</option>
        @foreach($buildings as $b)<option value="{{ $b->id }}">{{ $b->building_name }}</option>@endforeach
      </select></div>
    <div><label for="rf-mgmt">Management type</label>
      <select id="rf-mgmt" name="management_id" class="form-control">
        <option value="">All</option>
        @foreach($management as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
      </select></div>
    <div class="rf-grow"><label for="rf-q">Search</label>
      <input type="search" id="rf-q" name="q" class="form-control" placeholder="Tenant, contract no or unit" autocomplete="off"></div>
  </form>
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
  var slowTimer, req;

  function params() {
    var p = $('#rf-filters').serializeArray();
    p.push({name: 'page', value: state.page});
    if (state.sort) { p.push({name: 'sort', value: state.sort}, {name: 'direction', value: state.direction}); }
    return $.param(p);
  }
  function load() {
    if (req) { req.abort(); }
    clearTimeout(slowTimer);
    var $rows = $('#rf-rows').attr('aria-busy', 'true');
    $rows.children('table, .table-responsive').addClass('rf-busy');
    // first open of a month runs the full calculation (about 15 seconds): say so instead of a frozen screen
    slowTimer = setTimeout(function () {
      if (!$rows.find('.rf-calc').length) {
        $rows.prepend('<p class="rf-calc" role="status"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Calculating figures for this month. This can take about 15 seconds.</p>');
      }
    }, 1200);
    req = $.get(rowsUrl + '?' + params()).done(function (html) {
      $rows.html(html);
      $('#rf-live').text($rows.find('.rf-strip').attr('data-summary') || '');
    }).fail(function (x) {
      if (x.statusText !== 'abort') {
        $rows.html('<div class="rf-empty"><strong>Could not load the list.</strong>Check your connection and <button type="button" class="rf-link" id="rf-retry">try again</button>.</div>');
      }
    }).always(function () { clearTimeout(slowTimer); $rows.attr('aria-busy', 'false').find('.rf-calc').remove(); });
  }
  var typing;
  $('#rf-filters select').on('change', function () { state.page = 1; load(); });
  $('#rf-q').on('input', function () { clearTimeout(typing); typing = setTimeout(function () { state.page = 1; load(); }, 400); });
  $(document).on('click', '#rf-retry', load);
  $(document).on('click', '#rf-rows .pagination a', function (e) {
    e.preventDefault();
    var m = /[?&]page=(\d+)/.exec($(this).attr('href'));
    state.page = m ? Number(m[1]) : 1; load();
    $('html, body').scrollTop($('#rf-filters').offset().top - 80);
  });
  $(document).on('click', '#rf-rows .rf-sort', function (e) {
    e.preventDefault();
    var s = $(this).attr('data-sort');
    state.direction = (state.sort === s && state.direction === 'desc') ? 'asc' : (state.sort === s ? 'desc' : $(this).attr('data-first'));
    state.sort = s; state.page = 1; load();
  });
  $(document).on('click', '#rf-refresh', function () {
    var $b = $(this).prop('disabled', true).text('Refreshing…');
    $.post(refreshUrl, $('#rf-filters').serialize() + '&_token=' + encodeURIComponent(token))
      .done(function () { load(); })
      .fail(function () { $b.prop('disabled', false).text('Refresh'); $('#rf-live').text('Could not refresh the figures. Please try again.'); });
  });
  // ---- Follow-up / View popups ---------------------------------------------
  var opener = null;
  function openModal(url, from) {
    opener = from;
    $('#rf-modal').html('<div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-body" role="status">Loading…</div></div></div>').modal('show');
    $.get(url).done(function (html) {
      $('#rf-modal').html(html);
      $('#rf-comment').trigger('focus');
    }).fail(function (x) {
      $('#rf-modal .modal-body').text(x.status === 403 ? 'You cannot open this agreement.' : 'Could not load. Please close and try again.');
    });
  }
  $(document).on('click', '.rf-followup, .rf-view', function () { openModal($(this).attr('data-url'), this); });
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
  load();
})();
</script>
@endsection
