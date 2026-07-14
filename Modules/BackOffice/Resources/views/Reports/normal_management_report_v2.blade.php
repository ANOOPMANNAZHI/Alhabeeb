@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<style>
  /* ── Progress overlay ─────────────────────────────────────── */
  #nmr-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 9999;
    align-items: center;
    justify-content: center;
  }
  #nmr-overlay.active { display: flex; }
  #nmr-card {
    background: #fff;
    border-radius: 10px;
    padding: 36px 44px;
    min-width: 380px;
    max-width: 480px;
    width: 90%;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.22);
  }
  #nmr-card h5 { color: #1F4E79; font-weight: 700; font-size: 16px; margin-bottom: 6px; }
  #nmr-msg { color: #555; font-size: 13px; margin-bottom: 18px; min-height: 18px; }
  #nmr-track { background: #e2eaf3; border-radius: 20px; height: 18px; overflow: hidden; margin-bottom: 10px; }
  #nmr-bar  { height: 100%; width: 0%; background: linear-gradient(90deg,#1F4E79,#2E75B6); border-radius: 20px; transition: width .4s ease; }
  #nmr-pct  { font-size: 20px; font-weight: 700; color: #1F4E79; }
  #nmr-note { font-size: 11px; color: #999; margin-top: 14px; }

  /* ── Searchable building dropdown ─────────────────────────── */
  .sd-wrap { position: relative; }
  .sd-wrap .sd-input { padding-left: 34px; cursor: pointer; background: #fff; }
  .sd-wrap .icn-add  { pointer-events: none; }
  .sd-list {
    display: none;
    position: absolute;
    top: 100%; left: 0; right: 0;
    background: #fff;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 4px 4px;
    max-height: 240px;
    overflow-y: auto;
    z-index: 1050;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  .sd-list.open { display: block; }
  .sd-item {
    padding: 8px 12px;
    cursor: pointer;
    font-size: 13px;
    color: #333;
  }
  .sd-item:hover, .sd-item.sd-highlighted { background: #e8f0fe; color: #1F4E79; }
  .sd-item.sd-hidden { display: none; }
  .sd-no-results { padding: 8px 12px; color: #999; font-size: 13px; font-style: italic; }
</style>
@endsection

@section('content')

{{-- ── Progress overlay ──────────────────────────────────────────────────── --}}
<div id="nmr-overlay">
  <div id="nmr-card">
    <h5>Generating Report</h5>
    <div id="nmr-msg">Preparing…</div>
    <div id="nmr-track"><div id="nmr-bar"></div></div>
    <div id="nmr-pct">0%</div>
    <div id="nmr-note">Please keep this tab open until the download starts.</div>
  </div>
</div>

<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">Normal Management Report v2</div></div>
    {{ Breadcrumbs::render('showNormalManagementReportV2') }}
  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      <form action="{{route('normalManagementReportV2Generate')}}" target="_blank" method="POST"
            id="form_normal_mgmt_v2" class="form-horizontal" enctype="multipart/form-data">
        <div class="dataSearchBox">
          {{csrf_field()}}
          <div class="row">

            {{-- ── Building (searchable dropdown) ──────────────────── --}}
            <div class="col-sm-6">
              <div class="form-group">
                <label>Building <small class="textRed">*</small></label>
                <div class="p-relative sd-wrap" id="building-wrap">
                  <i class="fa fa-building icn-add" aria-hidden="true"></i>
                  <input type="text" class="form-control sd-input" id="building_search"
                         placeholder="Search building…" autocomplete="off" readonly>
                  <input type="hidden" id="building_id" name="building_id" value="all">
                  <div class="sd-list" id="building-list">
                    <div class="sd-item" data-value="all">All Buildings</div>
                    @foreach($buildings as $b)
                      <div class="sd-item" data-value="{{ $b->id }}">{{ $b->building_name }}</div>
                    @endforeach
                    <div class="sd-no-results" style="display:none">No buildings found</div>
                  </div>
                </div>
              </div>
            </div>

            {{-- ── Month ─────────────────────────────────────────────── --}}
            <div class="col-sm-6">
              <div class="form-group">
                <label for="month">Month <small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="month" name="month" required>
                    <option value="all">All Months</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="w-100"></div>

            {{-- ── Year ──────────────────────────────────────────────── --}}
            <div class="col-sm-6">
              <div class="form-group">
                <label for="year">Year <small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar icn-add" aria-hidden="true"></i>
                  <input type="number" class="form-control" id="year" name="year"
                         value="{{ date('Y') }}" min="2015" max="2035" required>
                </div>
              </div>
            </div>

            <div class="w-100"></div>

            <div class="col">
              <button type="submit" id="btn-generate" class="btn btn-primary">Generate</button>
            </div>

          </div>
        </div>
        <div class="clearfix"></div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
$(function () {

  /* ── Searchable building dropdown ──────────────────────────────────── */
  var $wrap   = $('#building-wrap');
  var $input  = $('#building_search');
  var $hidden = $('#building_id');
  var $list   = $('#building-list');
  var $items  = $list.find('.sd-item');
  var $noRes  = $list.find('.sd-no-results');

  // Set initial display label
  $input.val('All Buildings').prop('readonly', false);

  function openList() { $list.addClass('open'); }
  function closeList() {
    $list.removeClass('open');
    // Restore label of currently selected item
    var label = $list.find('.sd-item[data-value="' + $hidden.val() + '"]').text() || 'All Buildings';
    $input.val(label);
    // Reset filter
    $items.removeClass('sd-hidden');
    $noRes.hide();
  }

  $input.on('focus click', function () {
    $(this).val('').prop('readonly', false);
    $items.removeClass('sd-hidden');
    $noRes.hide();
    openList();
  });

  $input.on('input', function () {
    var q = $(this).val().toLowerCase();
    var visible = 0;
    $items.each(function () {
      var match = $(this).text().toLowerCase().indexOf(q) > -1;
      $(this).toggleClass('sd-hidden', !match);
      if (match) visible++;
    });
    $noRes.toggle(visible === 0);
    openList();
  });

  $list.on('click', '.sd-item', function () {
    $hidden.val($(this).data('value'));
    $input.val($(this).text());
    $list.removeClass('open');
    $items.removeClass('sd-hidden');
    $noRes.hide();
  });

  // Close when clicking outside
  $(document).on('click', function (e) {
    if (!$wrap.is(e.target) && $wrap.has(e.target).length === 0) {
      closeList();
    }
  });

  // Keyboard navigation
  $input.on('keydown', function (e) {
    var $visible = $items.not('.sd-hidden');
    var $hi = $list.find('.sd-highlighted');
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      var $next = $hi.length ? $hi.removeClass('sd-highlighted').nextAll('.sd-item:not(.sd-hidden)').first() : $visible.first();
      $next.addClass('sd-highlighted');
      if ($next.length) $list.scrollTop($next.position().top + $list.scrollTop() - 80);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      var $prev = $hi.length ? $hi.removeClass('sd-highlighted').prevAll('.sd-item:not(.sd-hidden)').first() : $visible.last();
      $prev.addClass('sd-highlighted');
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if ($hi.length) $hi.trigger('click');
    } else if (e.key === 'Escape') {
      closeList();
    }
  });

  /* ── Progress overlay + SSE generate ──────────────────────────────── */
  var $overlay = $('#nmr-overlay');
  var $bar     = $('#nmr-bar');
  var $msg     = $('#nmr-msg');
  var $pct     = $('#nmr-pct');
  var streamUrl    = '{{ route("normalManagementReportV2Stream") }}';
  var downloadBase = '{{ url("normalManagementReportV2Download") }}';

  function setProgress(p, m) {
    $bar.css('width', p + '%');
    $pct.text(p + '%');
    if (m) $msg.text(m);
  }

  $('#form_normal_mgmt_v2').on('submit', function (e) {
    e.preventDefault();

    var buildingId = $hidden.val();
    var month      = $('#month').val();
    var year       = $('#year').val();

    if (!buildingId) { alert('Please select a building.'); return; }

    setProgress(0, 'Preparing…');
    $overlay.addClass('active');

    var params = new URLSearchParams({ building_id: buildingId, month: month, year: year });
    var evtSource = new EventSource(streamUrl + '?' + params.toString());

    evtSource.onmessage = function (e) {
      try {
        var data = JSON.parse(e.data);
        setProgress(data.pct || 0, data.msg || '');
        if (data.done) {
          evtSource.close();
          window.location.href = downloadBase + '/' + data.token;
          setTimeout(function () { $overlay.removeClass('active'); }, 1200);
        }
      } catch (err) {}
    };

    evtSource.onerror = function () {
      evtSource.close();
      $overlay.removeClass('active');
      alert('An error occurred while generating the report. Please try again.');
    };
  });

});
</script>
@endsection
