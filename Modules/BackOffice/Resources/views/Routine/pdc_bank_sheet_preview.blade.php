@extends('layouts.plms-app')

@section('css')
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css') }}">
<style>
  .bsp td.num, .bsp th.num { text-align:right; white-space:nowrap; }
  .bsp .bsp-sub { display:block; font-size:12px; color:#6b7280; }
  .bsp .card-box { margin-bottom:20px; }
  .bsp .card-body h4 { margin:0 0 10px; }

  /* action bar: deposit details + post, stays visible while paging through the tables */
  .bsp-bar { position:sticky; top:0; z-index:20; }
  .bsp-bar .card-body { padding:12px 16px; }
  .bsp-bar .form-group { margin:0; }
  .bsp-bar label { font-size:12px; color:#6b7280; margin-bottom:2px; display:block; }
  .bsp-bar .bsp-file { font-weight:600; }
  .bsp-bar .bsp-actions { text-align:right; white-space:nowrap; }
  @media (max-width: 991px) { .bsp-bar .bsp-actions { text-align:left; margin-top:10px; } }

  /* summary pills: label + icon so status never relies on colour alone */
  .bsp-pill { display:inline-block; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; margin-right:6px; line-height:18px; }
  .bsp-pill i { margin-right:4px; }
  .bsp-pill-choice  { background:#fef3c7; color:#92400e; }
  .bsp-pill-matched { background:#dcfce7; color:#166534; }
  .bsp-pill-cannot  { background:#fee2e2; color:#991b1b; }
  .bsp-pill-vat { background:#e0e7ff; color:#3730a3; }

  /* section headings */
  .bsp .bsp-head { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin:0 0 10px; }
  .bsp .bsp-head h4 { margin:0; font-size:16px; }
  .bsp .bsp-head .bsp-hint { color:#6b7280; font-size:12px; }
  .bsp .bsp-head .bsp-clear { margin-left:auto; font-size:12px; }

  /* per-column filter row (same as the other list pages) */
  .bsp thead tr.bsp-filters td { padding:4px 6px; }
  .bsp thead tr.bsp-filters input, .bsp thead tr.bsp-filters select { width:100%; min-width:60px; height:28px; padding:2px 6px; font-size:12px; border:1px solid #d1d5db; border-radius:3px; }

  /* candidate picker inside the ambiguous table */
  .bsp-cand { display:block; padding:6px 8px; margin:0 0 4px; border:1px solid #e5e7eb; border-radius:6px; cursor:pointer; font-weight:normal; }
  .bsp-cand:hover { background:#f9fafb; }
  .bsp-cand.is-on { border-color:#2563eb; background:#eff6ff; }
  .bsp-cand input { margin:0 8px 0 0; vertical-align:middle; }
  .bsp-cand b { font-weight:600; }

  .bsp tr.is-off td { opacity:.45; }
  .bsp .bsp-status { white-space:nowrap; }
</style>
@endsection

@section('content')
@php
  // filter input helper: section prefix (a/m/c), field, optional placeholder
  $fin = function ($p, $f, $ph = '') use ($filters) {
    $sec = ['a' => 'ambiguous', 'm' => 'matched', 'c' => 'cannot'][$p];
    $v = isset($filters[$sec][$f]) ? $filters[$sec][$f] : '';
    return '<input autocomplete="off" type="text" class="bsp-filter" form="bsp-filter-form" name="' . $p . '[' . $f . ']" value="' . e($v) . '" placeholder="' . e($ph) . '" aria-label="Filter ' . e($f) . '">';
  };
  $fmtDate = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-'; };
  $filtering = ['ambiguous' => count($filters['ambiguous']) > 0, 'matched' => count($filters['matched']) > 0, 'cannot' => count($filters['cannot']) > 0];
  $statusLabels = [
    'not_found'       => ['btn-default', 'Not in PLMS'],
    'amount_mismatch' => ['btn-warning', 'Amount mismatch'],
    'date_mismatch'   => ['btn-warning', 'Date mismatch'],
    'unknown_bank'    => ['btn-default', 'Unknown bank'],
  ];
@endphp
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">PDC Bulk Posting — Bank Sheet Review</div>
    </div>
    {{ Breadcrumbs::render('pdcBankSheetPreview') }}
  </div>
</div>

<div class="bsp">

{{-- ── Action bar ─────────────────────────────────────────────── --}}
<form method="post" action="{{ route('pdcBankSheetPost', $stored) }}" id="bsp-post-form" autocomplete="off">
  @csrf
  <div class="card card-box bsp-bar">
    <div class="card-body">
      <div class="row">
        <div class="col-md-4 col-sm-12">
          <span class="bsp-file"><i class="fa fa-file-excel-o" aria-hidden="true"></i> {{ $fileName }}</span>
          <span class="bsp-sub">
            {{ $counts['accepted'] }} accepted cheques on sheet
            @if($counts['rejected'] > 0)
              · {{ $counts['rejected'] }} rejected by bank not shown
            @endif
          </span>
          <div style="margin-top:6px">
            <span class="bsp-pill bsp-pill-choice"><i class="fa fa-question-circle" aria-hidden="true"></i>{{ $counts['ambiguous'] }} need choice</span>
            @if($counts['vat'] > 0)
            <span class="bsp-pill bsp-pill-vat"><i class="fa fa-percent" aria-hidden="true"></i>{{ $counts['vat'] }} rent + VAT to check</span>
            @endif
            <span class="bsp-pill bsp-pill-matched"><i class="fa fa-check-circle" aria-hidden="true"></i>{{ $counts['matched'] }} matched</span>
            <span class="bsp-pill bsp-pill-cannot"><i class="fa fa-ban" aria-hidden="true"></i>{{ $counts['cannot'] }} cannot post</span>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="form-group">
            <label for="bsp-bank">Deposit bank <span class="textRed">*</span></label>
            <select id="bsp-bank" name="bank_id" class="form-control" required>
              <option value="">Select</option>
              @foreach($banks as $b)
              <option value="{{ $b->id }}" {{ old('bank_id', $defaultBankId) == $b->id ? 'selected' : '' }}>{{ $b->bank_name }} - {{ $b->bank_code }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-2 col-sm-6">
          <div class="form-group">
            <label for="bsp-date">Deposit date <span class="textRed">*</span></label>
            <input id="bsp-date" type="date" name="pdc_deposit_date" class="form-control" max="{{ $today }}" value="{{ old('pdc_deposit_date', $defaultDate) }}" required>
          </div>
        </div>
        <div class="col-md-3 col-sm-12 bsp-actions">
          <label>&nbsp;</label>
          <a href="{{ route('pdcPosting') }}" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary" id="bsp-post-btn" {{ $toPost > 0 ? '' : 'disabled' }}>
            <i class="fa fa-paper-plane" aria-hidden="true"></i> Post <span id="bsp-post-count">{{ $toPost }}</span> cheque(s)
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

{{-- Column filters (inputs reference this form via form=""); ticks and choices live in the session so they are unaffected --}}
<form method="get" action="{{ route('pdcBankSheetPreview', $stored) }}" id="bsp-filter-form" autocomplete="off"></form>

{{-- ── 1. Needs your choice ───────────────────────────────────── --}}
@if($counts['ambiguous'] > 0)
<div class="row"><div class="col-md-12 col-sm-12">
<div class="card card-box">
  <div class="card-body">
    <div class="bsp-head">
      <h4><i class="fa fa-question-circle" style="color:#92400e" aria-hidden="true"></i> Needs your choice <span class="bsp-pill bsp-pill-choice">{{ $filtering['ambiguous'] ? $shown['ambiguous'] . ' of ' : '' }}{{ $counts['ambiguous'] }}</span></h4>
      <span class="bsp-hint">More than one unposted PDC fits this cheque. Pick the right tenant, or leave it as <b>Skip</b> and post it manually later.</span>
      @if($filtering['ambiguous'])<a href="#" class="bsp-clear" data-prefix="a">Clear filters</a>@endif
    </div>
    <div class="table-wrap"><div class="table-responsive">
      <table class="table display product-overview mb-30">
        <thead>
          <tr>
            <th width="5%">Sl No.</th>
            <th width="10%">Cheque No</th>
            <th width="14%">Bank (sheet)</th>
            <th width="9%">Cheque Date</th>
            <th class="num" width="9%">Amount</th>
            <th>Choose the PLMS record</th>
          </tr>
          <tr class="bsp-filters">
            <td></td>
            <td>{!! $fin('a', 'cheque') !!}</td>
            <td>{!! $fin('a', 'bank') !!}</td>
            <td>{!! $fin('a', 'date', 'dd/mm/yyyy') !!}</td>
            <td>{!! $fin('a', 'amount') !!}</td>
            <td>{!! $fin('a', 'plms', 'Tenant / agreement / building / unit') !!}</td>
          </tr>
        </thead>
        <tbody>
          @forelse($ambiguous as $r)
          @php $rowNo = $r['row']['row_no']; $cur = isset($selection['choice'][$rowNo]) ? $selection['choice'][$rowNo] : ''; @endphp
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td><b>{{ $r['row']['cheque_no'] }}</b></td>
            <td>{{ $r['row']['bank_name'] }}</td>
            <td>{{ $fmtDate($r['row']['cheque_date']) }}</td>
            <td class="num"><b>{{ numberFormat($r['row']['amount']) }}</b></td>
            <td>
              <label class="bsp-cand {{ $cur === '' ? 'is-on' : '' }}">
                <input type="radio" name="choice_{{ $rowNo }}" value="" class="bsp-choice" data-row="{{ $rowNo }}" {{ $cur === '' ? 'checked' : '' }}>
                <b>Skip</b> <span class="bsp-sub" style="display:inline">— post manually later</span>
              </label>
              @foreach($r['candidates'] as $group)
              @php $ids = implode(',', array_column($group, 'id')); $first = $group[0]; @endphp
              <label class="bsp-cand {{ $cur === $ids ? 'is-on' : '' }}">
                <input type="radio" name="choice_{{ $rowNo }}" value="{{ $ids }}" class="bsp-choice" data-row="{{ $rowNo }}" {{ $cur === $ids ? 'checked' : '' }}>
                <b>{{ $first->tenant_name }}</b>
                @if(count($group) > 1)
                  · {{ count($group) }} rows (split cheque)
                @endif
                @foreach($group as $p)
                <span class="bsp-sub">{{ $p->tenant_contract_no }} · {{ $p->building_name }} / {{ $p->unit_no }} · {{ $p->pdc_type_name }} · {{ numberFormat($p->pdc_amt) }} · chq {{ $fmtDate($p->pdc_check_date) }} · {{ $p->bank_name }}</span>
                @endforeach
              </label>
              @endforeach
            </td>
          </tr>
          @empty
          <tr><td colspan="6" align="center"><p>No cheque matches these filters.</p></td></tr>
          @endforelse
        </tbody>
      </table>
    </div></div>
  </div>
</div>
</div></div>
@endif

{{-- ── 1b. Rent + VAT by difference (tick to post) ─────────────── --}}
@if(count($vatRows) > 0)
<div class="row"><div class="col-md-12 col-sm-12">
<div class="card card-box">
  <div class="card-body">
    <div class="bsp-head">
      <h4><i class="fa fa-percent" style="color:#3730a3" aria-hidden="true"></i> Rent + VAT (check) <span class="bsp-pill bsp-pill-vat">{{ $counts['vat'] }}</span></h4>
      <span class="bsp-hint">Bank amount is the rent plus about 5% VAT. Tick to post the rent receipt and a VAT General Receipt for the extra.</span>
    </div>
    <div class="table-wrap"><div class="table-responsive">
      <table class="table display product-overview mb-30">
        <thead>
          <tr>
            <th width="4%"></th>
            <th width="10%">Cheque No</th>
            <th width="9%">Cheque Date</th>
            <th class="num" width="9%">Bank Amount</th>
            <th>Tenant</th>
            <th>Agr No</th>
            <th>Building / Unit</th>
            <th class="num">Rent</th>
            <th class="num">VAT 5%</th>
            <th class="num">Actual Extra</th>
            <th class="num">Difference</th>
          </tr>
        </thead>
        <tbody>
          @foreach($vatRows as $r)
          @php
            $rowNo = $r['row']['row_no'];
            $rentPdcs = array_values(array_filter($r['pdcs'], function ($p) { return (int) $p->pdc_type !== 3; }));
            $first = $rentPdcs[0];
            $rentSum = 0; foreach ($rentPdcs as $p) { $rentSum += $p->pdc_amt; }
            $on = !empty($selection['vat'][$rowNo]);
          @endphp
          <tr class="{{ $on ? '' : 'is-off' }}">
            <td><input type="checkbox" class="bsp-vat-tick" data-row="{{ $rowNo }}" {{ $on ? 'checked' : '' }} aria-label="Post cheque {{ $r['row']['cheque_no'] }} with VAT"></td>
            <td><b>{{ $r['row']['cheque_no'] }}</b></td>
            <td>{{ $fmtDate($r['row']['cheque_date']) }}</td>
            <td class="num"><b>{{ numberFormat($r['row']['amount']) }}</b></td>
            <td>{{ $first->tenant_name }}</td>
            <td>{{ $first->tenant_contract_no }}</td>
            <td>{{ $first->building_name }} / {{ $first->unit_no }}</td>
            <td class="num">{{ numberFormat($rentSum) }}</td>
            <td class="num">{{ numberFormat($r['expected_vat']) }}</td>
            <td class="num"><b>{{ numberFormat($r['vat_amount']) }}</b></td>
            <td class="num">{{ numberFormat($r['vat_amount'] - $r['expected_vat']) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div></div>
  </div>
</div>
</div></div>
@endif

{{-- ── 2. Auto-matched ───────────────────────────────────────── --}}
<div class="row"><div class="col-md-12 col-sm-12">
<div class="card card-box">
  <div class="card-body">
    <div class="bsp-head">
      <h4><i class="fa fa-check-circle" style="color:#166534" aria-hidden="true"></i> Auto-matched <span class="bsp-pill bsp-pill-matched">{{ $filtering['matched'] ? $shown['matched'] . ' of ' : '' }}{{ $counts['matched'] }}</span></h4>
      <span class="bsp-hint">These will be posted. Untick any you want to hold back — your ticks are kept while you filter and page.</span>
      @if($filtering['matched'])<a href="#" class="bsp-clear" data-prefix="m">Clear filters</a>@endif
    </div>
    <h4>
      <div id="pagination_info_m">@include('includes.pagination_info', ['paginator' => $matched])</div>
      <div class="clr"></div>
    </h4>
    <div class="table-wrap"><div class="table-responsive">
      <table class="table display product-overview mb-30">
        <thead>
          <tr>
            <th width="4%"><input type="checkbox" id="bsp-check-page" title="Tick / untick this page" aria-label="Tick or untick every row on this page"></th>
            <th width="5%">Sl No.</th>
            <th width="10%">Cheque No</th>
            <th width="9%">Cheque Date</th>
            <th class="num" width="9%">Amount</th>
            <th>Tenant</th>
            <th>Agr No</th>
            <th>Building / Unit</th>
            <th width="8%">Type</th>
            <th>PLMS Bank</th>
            <th width="7%">Rows</th>
          </tr>
          <tr class="bsp-filters">
            <td></td>
            <td></td>
            <td>{!! $fin('m', 'cheque') !!}</td>
            <td>{!! $fin('m', 'date', 'dd/mm/yyyy') !!}</td>
            <td>{!! $fin('m', 'amount') !!}</td>
            <td>{!! $fin('m', 'tenant') !!}</td>
            <td>{!! $fin('m', 'agr') !!}</td>
            <td>{!! $fin('m', 'building') !!}</td>
            <td>{!! $fin('m', 'type') !!}</td>
            <td>{!! $fin('m', 'pbank') !!}</td>
            <td></td>
          </tr>
        </thead>
        <tbody>
          @forelse($matched as $r)
          @php $rowNo = $r['row']['row_no']; $first = $r['pdcs'][0]; $on = !isset($selection['unticked'][$rowNo]); @endphp
          <tr class="{{ $on ? '' : 'is-off' }}" id="bsp-row-{{ $rowNo }}">
            <td><input type="checkbox" class="bsp-tick" data-row="{{ $rowNo }}" {{ $on ? 'checked' : '' }} aria-label="Post cheque {{ $r['row']['cheque_no'] }}"></td>
            <td>{{ $matched->perPage() * ($matched->currentPage() - 1) + $loop->iteration }}</td>
            <td><b>{{ $r['row']['cheque_no'] }}</b></td>
            <td>{{ $fmtDate($r['row']['cheque_date']) }}</td>
            <td class="num"><b>{{ numberFormat($r['row']['amount']) }}</b></td>
            <td>{{ $first->tenant_name }}</td>
            <td>
              @foreach($r['pdcs'] as $p)
                {{ $p->tenant_contract_no }}@if(!$loop->last)<br>@endif
              @endforeach
            </td>
            <td>
              @foreach($r['pdcs'] as $p)
                {{ $p->building_name }} / {{ $p->unit_no }}@if(!$loop->last)<br>@endif
              @endforeach
            </td>
            <td>
              {{ $first->pdc_type_name }}
              @if(count(array_filter($r['pdcs'], function ($p) { return (int) $p->pdc_type === 3; })) > 0)
                <span class="bsp-pill bsp-pill-vat">+ VAT</span>
              @endif
            </td>
            <td>{{ $first->bank_name }}</td>
            <td>
              @if(count($r['pdcs']) > 1)
                <span class="btn-circle btn-info btn-sm m-b-10 status" title="One cheque across {{ count($r['pdcs']) }} PDC rows"><b>{{ count($r['pdcs']) }} split</b></span>
              @else
                1
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="11" align="center"><p>{{ $filtering['matched'] ? 'No matched cheque fits these filters.' : 'No cheque on the sheet matched an unposted PDC.' }}</p></td></tr>
          @endforelse
        </tbody>
      </table>
    </div></div>
    <div id="pagination_m"><div class="text-center">{{ $matched->links() }}</div></div>
  </div>
</div>
</div></div>

{{-- ── 3. Cannot post ────────────────────────────────────────── --}}
<div class="row"><div class="col-md-12 col-sm-12">
<div class="card card-box">
  <div class="card-body">
    <div class="bsp-head">
      <h4><i class="fa fa-ban" style="color:#991b1b" aria-hidden="true"></i> Cannot post <span class="bsp-pill bsp-pill-cannot">{{ $filtering['cannot'] ? $shown['cannot'] . ' of ' : '' }}{{ $counts['cannot'] }}</span></h4>
      <span class="bsp-hint">Accepted by the bank but no clean PDC match. Nothing happens to these — post them on the manual screen.</span>
      @if($filtering['cannot'])<a href="#" class="bsp-clear" data-prefix="c">Clear filters</a>@endif
    </div>
    <h4>
      <div id="pagination_info_c">@include('includes.pagination_info', ['paginator' => $cannot])</div>
      <div class="clr"></div>
    </h4>
    <div class="table-wrap"><div class="table-responsive">
      <table class="table display product-overview mb-30">
        <thead>
          <tr>
            <th width="5%">Sl No.</th>
            <th width="10%">Cheque No</th>
            <th width="16%">Bank (sheet)</th>
            <th width="9%">Cheque Date</th>
            <th class="num" width="9%">Amount</th>
            <th width="14%">Status</th>
            <th>Why</th>
          </tr>
          <tr class="bsp-filters">
            <td></td>
            <td>{!! $fin('c', 'cheque') !!}</td>
            <td>{!! $fin('c', 'bank') !!}</td>
            <td>{!! $fin('c', 'date', 'dd/mm/yyyy') !!}</td>
            <td>{!! $fin('c', 'amount') !!}</td>
            <td>
              <select name="c[status]" class="bsp-filter" form="bsp-filter-form" aria-label="Filter status">
                <option value="">All</option>
                @foreach($statusLabels as $k => $l)
                <option value="{{ $k }}" {{ (isset($filters['cannot']['status']) ? $filters['cannot']['status'] : '') === $k ? 'selected' : '' }}>{{ $l[1] }}</option>
                @endforeach
              </select>
            </td>
            <td>{!! $fin('c', 'why') !!}</td>
          </tr>
        </thead>
        <tbody>
          @forelse($cannot as $r)
          @php $l = isset($statusLabels[$r['status']]) ? $statusLabels[$r['status']] : ['btn-default', $r['status']]; @endphp
          <tr>
            <td>{{ $cannot->perPage() * ($cannot->currentPage() - 1) + $loop->iteration }}</td>
            <td><b>{{ $r['row']['cheque_no'] }}</b></td>
            <td>{{ $r['row']['bank_name'] }}</td>
            <td>{{ $fmtDate($r['row']['cheque_date']) }}</td>
            <td class="num">{{ numberFormat($r['row']['amount']) }}</td>
            <td class="bsp-status"><span class="btn-circle {{ $l[0] }} btn-sm m-b-10 status"><b>{{ $l[1] }}</b></span></td>
            <td>{{ $r['reason'] }}</td>
          </tr>
          @empty
          <tr><td colspan="7" align="center"><p>{{ $filtering['cannot'] ? 'Nothing here fits these filters.' : 'None — every accepted cheque on the sheet was matched.' }}</p></td></tr>
          @endforelse
        </tbody>
      </table>
    </div></div>
    <div id="pagination_c"><div class="text-center">{{ $cannot->links() }}</div></div>
  </div>
</div>
</div></div>

</div>
@endsection

@section('scripts')
<script>
$(function () {
  var selectUrl = '{{ route('pdcBankSheetSelect', $stored) }}';
  var token = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
  var count = {{ (int) $toPost }};

  function setCount(n) {
    count = n < 0 ? 0 : n;
    $('#bsp-post-count').text(count);
    $('#bsp-post-btn').prop('disabled', count === 0);
  }

  // Remember a tick / choice on the server so it survives paging; revert the control if that fails.
  function remember(row, kind, value, onFail) {
    $.post(selectUrl, { _token: token, row: row, kind: kind, value: value })
      .fail(function () {
        onFail();
        alert('Could not save your selection — please try again.');
      });
  }

  $(document).on('change', '.bsp-tick', function () {
    var $cb = $(this), row = $cb.data('row'), on = $cb.is(':checked');
    $cb.closest('tr').toggleClass('is-off', !on);
    setCount(count + (on ? 1 : -1));
    remember(row, 'tick', on ? '1' : '0', function () {
      $cb.prop('checked', !on).closest('tr').toggleClass('is-off', on);
      setCount(count + (on ? -1 : 1));
    });
  });

  $(document).on('change', '.bsp-vat-tick', function () {
    var $cb = $(this), row = $cb.data('row'), on = $cb.is(':checked');
    $cb.closest('tr').toggleClass('is-off', !on);
    setCount(count + (on ? 1 : -1));
    remember(row, 'vat', on ? '1' : '0', function () {
      $cb.prop('checked', !on).closest('tr').toggleClass('is-off', on);
      setCount(count + (on ? -1 : 1));
    });
  });

  $('#bsp-check-page').on('change', function () {
    var on = this.checked;
    $('.bsp-tick').each(function () {
      if (this.checked !== on) { $(this).prop('checked', on).trigger('change'); }
    });
  });

  $(document).on('change', '.bsp-choice', function () {
    var $r = $(this), row = $r.data('row'), value = $r.val();
    var $group = $('.bsp-choice[data-row="' + row + '"]');
    var was = $group.filter('.was-on').val();
    if (was === undefined) { was = ''; }
    $group.closest('.bsp-cand').removeClass('is-on');
    $r.closest('.bsp-cand').addClass('is-on');
    // count moves only when going from Skip to a choice or back
    if (was === '' && value !== '') { setCount(count + 1); }
    if (was !== '' && value === '') { setCount(count - 1); }
    $group.removeClass('was-on'); $r.addClass('was-on');
    remember(row, 'choice', value, function () {
      $group.filter('[value="' + was + '"]').prop('checked', true).trigger('change');
    });
  });
  $('.bsp-choice:checked').addClass('was-on');

  // Column filters: submit the GET form (without empty fields) after typing pauses.
  var filterTimer = 0;
  function applyFilters() {
    var $f = $('#bsp-filter-form');
    $f.find('.bsp-filter').each(function () { $(this).prop('disabled', $.trim(this.value) === ''); });
    $f.submit();
  }
  $(document).on('input', 'input.bsp-filter', function () {
    window.clearTimeout(filterTimer);
    filterTimer = window.setTimeout(applyFilters, 500);
  });
  $(document).on('change', 'select.bsp-filter', applyFilters);
  $(document).on('keydown', '.bsp-filter', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); window.clearTimeout(filterTimer); applyFilters(); }
  });
  $('.bsp-clear').on('click', function (e) {
    e.preventDefault();
    var p = $(this).data('prefix');
    $('.bsp-filter[name^="' + p + '["]').val('');
    applyFilters();
  });

  $('#bsp-post-form').on('submit', function () {
    if (count === 0) { return false; }
    if (!confirm('Post ' + count + ' cheque(s) to ' + $('#bsp-bank option:selected').text() + ' on ' + $('#bsp-date').val() + '?\n\nThis generates receipts and cannot be undone.')) { return false; }
    $('#bsp-post-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Posting…');
    return true;
  });
});
</script>
@endsection
