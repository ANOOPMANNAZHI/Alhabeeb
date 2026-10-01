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
  window.rfReload = load;
  load();
})();
</script>
@endsection
