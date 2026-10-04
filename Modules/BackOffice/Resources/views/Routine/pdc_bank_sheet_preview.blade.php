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
  .bsp-pill-acct { background:#fef3c7; color:#92400e; margin-top:4px; }
  /* Result tabs: Auto-matched / Cannot post / Rejected by bank */
  .bsp .bsp-tabs-card { overflow:hidden; }
  .bsp .bsp-tablist { display:flex; gap:4px; padding:8px 16px 0; background:#f8fafc; border-bottom:1px solid #e2e8f0; overflow-x:auto; }
  .bsp .bsp-tab { display:inline-flex; align-items:center; gap:8px; min-height:44px; padding:0 16px; margin-bottom:-1px;
                  border:0; border-bottom:3px solid transparent; border-radius:6px 6px 0 0; background:transparent;
                  color:#475569; font-size:14px; font-weight:600; white-space:nowrap; cursor:pointer; transition:background .15s, color .15s; }
  .bsp .bsp-tab:hover { background:#f1f5f9; color:#0f172a; }
  .bsp .bsp-tab[aria-selected="true"] { background:#fff; color:#0f172a; border-bottom-color:#2f4fd6; }
  .bsp .bsp-tab:focus-visible { outline:2px solid #2f4fd6; outline-offset:-2px; }
  .bsp .bsp-tab-ok i { color:#166534; }
  .bsp .bsp-tab-bad i { color:#991b1b; }
  .bsp .bsp-tab-count { min-width:24px; padding:2px 8px; border-radius:12px; font-size:12px; line-height:18px; text-align:center; font-variant-numeric:tabular-nums; }
  .bsp .bsp-tab-ok .bsp-tab-count { background:#dcfce7; color:#166534; }
  .bsp .bsp-tab-bad .bsp-tab-count { background:#fee2e2; color:#991b1b; }
  .bsp .bsp-panel { padding:16px 24px 24px; }
  .bsp .bsp-panel[hidden] { display:none; }
  .bsp .bsp-panel:focus-visible { outline:2px solid #2f4fd6; outline-offset:-2px; }
  @media (max-width: 575px) { .bsp .bsp-panel { padding:16px; } .bsp .bsp-tablist { padding:8px 8px 0; } }
  @media (prefers-reduced-motion: reduce) { .bsp .bsp-tab { transition:none; } }
  /* Rejected by the bank: own-styled controls (the theme's .btn sizing clips labels) */
  .bsp .bsp-bounce-reason { width:100%; height:36px; padding:0 8px; border:1px solid #94a3b8; border-radius:6px; margin-bottom:8px; }
  .bsp .bsp-bounce-btn { display:inline-flex; align-items:center; height:36px; padding:0 16px; border:1px solid #dc2626; border-radius:6px;
                         background:#dc2626; color:#fff; font-size:14px; font-weight:600; white-space:nowrap; cursor:pointer; }
  .bsp .bsp-bounce-btn:hover { background:#b91c1c; border-color:#b91c1c; }
  .bsp .bsp-bounce-btn:focus-visible { outline:2px solid #2f4fd6; outline-offset:2px; }
  .bsp .bsp-bounce-btn[disabled] { opacity:.6; cursor:not-allowed; }

  /* Bank codes on the sheet that no PLMS bank carries yet */
  .bsp .bsp-codes { border-left:4px solid #b45309; }
  .bsp .bsp-codes p { margin:0 0 12px; color:#475569; font-size:13px; }
  .bsp .bsp-code-row { display:flex; flex-wrap:wrap; align-items:center; gap:8px 16px; padding:10px 0; border-top:1px solid #e2e8f0; }
  .bsp .bsp-code-row:first-of-type { border-top:0; }
  .bsp .bsp-code-what { flex:1 1 260px; }
  .bsp .bsp-code-no { display:inline-block; min-width:36px; padding:2px 8px; margin-right:6px; border-radius:6px; background:#fef3c7; color:#92400e; font-weight:700; text-align:center; }
  .bsp .bsp-code-row select { height:36px; min-width:240px; padding:0 8px; border:1px solid #94a3b8; border-radius:6px; }
  .bsp .bsp-code-btn { display:inline-flex; align-items:center; height:36px; padding:0 16px; border:1px solid #2f4fd6; border-radius:6px;
                       background:#2f4fd6; color:#fff; font-size:14px; font-weight:600; white-space:nowrap; cursor:pointer; }
  .bsp .bsp-code-btn:hover { background:#2440b3; }
  .bsp .bsp-code-btn:focus-visible, .bsp .bsp-code-row select:focus-visible { outline:2px solid #2f4fd6; outline-offset:2px; }

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
  // Pay Account cell: the sheet's account (bank's leading zeros dropped), any different PLMS account under it
  $accCell = function ($r, $pdcs) {
    $sheet = \Modules\BackOffice\Services\BankSheetPdcMatcher::normaliseAccountNo(isset($r['row']['account_no']) ? $r['row']['account_no'] : null);
    $html  = $sheet !== null ? e($sheet) : '-';
    $seen  = [];
    foreach ($pdcs as $p) {
      $own = \Modules\BackOffice\Services\BankSheetPdcMatcher::normaliseAccountNo(isset($p->pdc_account_no) ? $p->pdc_account_no : null);
      if ($own !== null && $own !== $sheet && !isset($seen[$own])) {
        $seen[$own] = true;
        $html .= '<span class="bsp-sub">PLMS: ' . e($own) . '</span>';
      }
    }
    if (!empty($r['account_mismatch'])) {
      $html .= '<span class="bsp-pill bsp-pill-acct"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>Account differs</span>';
    } elseif ($r['status'] === 'matched' && $r['reason'] === 'Picked by account number') {
      $html .= '<span class="bsp-sub">picked by account</span>';
    }
    return $html;
  };
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
<div class="alert alert-danger" id="bsp-post-error" role="alert" style="display:none"></div>
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
              · <a href="#bsp-tabs" class="bsp-goto-tab" data-tab="rejected">{{ $counts['rejected'] }} rejected by bank (see below)</a>
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

{{-- ── Bank codes not on any PLMS bank: add them to the right bank, then the sheet is matched again ── --}}
@if(count($unknownCodes) > 0)
<div class="card card-box bsp-codes" id="bsp-codes">
  <div class="card-body">
    <div class="bsp-head">
      <h4><i class="fa fa-university" style="color:#b45309" aria-hidden="true"></i> Bank codes not set up in PLMS
        <span class="bsp-pill bsp-pill-choice">{{ count($unknownCodes) }}</span></h4>
    </div>
    <p>The bank's sheet uses these clearing codes, but no bank in the Bank master has them, so their cheques cannot be posted.
      @if($canEditBank)Add each code to its bank: it is kept next to the codes the bank already has, and the sheet is matched again.
      @else Ask someone who can edit the Bank master to add them.@endif</p>
    @foreach($unknownCodes as $u)
      @php
        $suggested = $u['suggested_bank_id'] ? $codeBanks->firstWhere('id', $u['suggested_bank_id']) : null;
      @endphp
      <form method="post" action="{{ route('pdcBankSheetClearingCode', $stored) }}" class="bsp-code-row">
        @csrf
        <input type="hidden" name="code" value="{{ $u['code'] }}">
        <div class="bsp-code-what">
          <span class="bsp-code-no">{{ $u['code'] }}</span><strong>{{ $u['sheet_name'] ?: 'No bank name' }}</strong>
          <span class="bsp-sub">{{ $u['rows'] }} {{ $u['rows'] === 1 ? 'cheque' : 'cheques' }} on this sheet
            @if($suggested) · the same {{ $u['rows'] === 1 ? 'cheque is' : 'cheques are' }} in PLMS under {{ $suggested->bank_name }}@endif</span>
        </div>
        @if($canEditBank)
          <label for="bsp-code-bank-{{ $u['code'] }}" class="sr-only">Bank for code {{ $u['code'] }}</label>
          <select id="bsp-code-bank-{{ $u['code'] }}" name="bank_id" required>
            <option value="">Choose the bank</option>
            @foreach($codeBanks as $b)
              <option value="{{ $b->id }}" {{ $suggested && $suggested->id === $b->id ? 'selected' : '' }}>{{ $b->bank_name }} - {{ $b->bank_code }}{{ $b->bank_clearing_codes ? ' (codes ' . $b->bank_clearing_codes . ')' : '' }}</option>
            @endforeach
          </select>
          <button type="submit" class="bsp-code-btn"><i class="fa fa-plus" aria-hidden="true" style="margin-right:6px"></i>Add code {{ $u['code'] }}</button>
        @endif
      </form>
    @endforeach
  </div>
</div>
@endif

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
            <td>
              {{ $r['row']['bank_name'] }}
              @php $sheetAcc = \Modules\BackOffice\Services\BankSheetPdcMatcher::normaliseAccountNo(isset($r['row']['account_no']) ? $r['row']['account_no'] : null); @endphp
              @if($sheetAcc !== null)<span class="bsp-sub">Acct {{ $sheetAcc }}</span>@endif
            </td>
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
                <span class="bsp-sub">{{ $p->tenant_contract_no }} · {{ $p->building_name }} / {{ $p->unit_no }} · {{ $p->pdc_type_name }} · {{ numberFormat($p->pdc_amt) }} · chq {{ $fmtDate($p->pdc_check_date) }} · {{ $p->bank_name }}@if(!empty($p->pdc_account_no)) · acct {{ \Modules\BackOffice\Services\BankSheetPdcMatcher::normaliseAccountNo($p->pdc_account_no) }}@endif</span>
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
            <th>PLMS Bank</th>
            <th>Pay Account</th>
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
            <td>{{ $first->bank_name }}</td>
            <td>{!! $accCell($r, $r['pdcs']) !!}</td>
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

{{-- ── 2–4. Results as tabs: Auto-matched / Cannot post / Rejected by bank ── --}}
@php
  $bspTabs = [
      'matched'  => ['Auto-matched', 'fa-check-circle', 'ok', ($filtering['matched'] ? $shown['matched'] . ' of ' : '') . $counts['matched']],
      'cannot'   => ['Cannot post', 'fa-ban', 'bad', ($filtering['cannot'] ? $shown['cannot'] . ' of ' : '') . $counts['cannot']],
      'rejected' => ['Rejected by bank', 'fa-times-circle', 'bad', count($rejected)],
  ];
@endphp
<input type="hidden" name="tab" id="bsp-tab-input" form="bsp-filter-form" value="{{ $activeTab }}">
<div class="row" id="bsp-tabs"><div class="col-md-12 col-sm-12">
<div class="card card-box bsp-tabs-card">
  <div class="bsp-tablist" role="tablist" aria-label="Cheques on the sheet">
    @foreach($bspTabs as $key => $t)
    <button type="button" role="tab" class="bsp-tab bsp-tab-{{ $t[2] }}" id="bsp-tab-{{ $key }}" data-tab="{{ $key }}"
            aria-controls="bsp-panel-{{ $key }}" aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}" tabindex="{{ $activeTab === $key ? '0' : '-1' }}">
      <i class="fa {{ $t[1] }}" aria-hidden="true"></i>
      <span>{{ $t[0] }}</span>
      <span class="bsp-tab-count">{{ $t[3] }}</span>
    </button>
    @endforeach
  </div>

<div class="bsp-panel" role="tabpanel" id="bsp-panel-matched" aria-labelledby="bsp-tab-matched" tabindex="0" {{ $activeTab === 'matched' ? '' : 'hidden' }}>
    <div class="bsp-head">
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
            <th>Pay Account</th>
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
            @php $tenantCount = count(array_unique(array_map(function ($p) { return $p->tenant_id; }, $r['pdcs']))); @endphp
            <td>
              @if($tenantCount > 1)
                {{-- one cheque paying for several tenants: one name per PDC line, next to its contract --}}
                @foreach($r['pdcs'] as $p)
                  {{ $p->tenant_name }}@if(!$loop->last)<br>@endif
                @endforeach
              @else
                {{ $first->tenant_name }}
              @endif
            </td>
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
            <td>{!! $accCell($r, $r['pdcs']) !!}</td>
            <td>
              @if(count($r['pdcs']) > 1)
                <span class="btn-circle btn-info btn-sm m-b-10 status" title="{{ $tenantCount > 1 ? $r['reason'] : 'One cheque across ' . count($r['pdcs']) . ' PDC rows' }}"><b>{{ count($r['pdcs']) }} split</b></span>
                @if($tenantCount > 1)<div style="font-size:12px;color:#475569;margin-top:4px">{{ $tenantCount }} tenants</div>@endif
              @else
                1
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="12" align="center"><p>{{ $filtering['matched'] ? 'No matched cheque fits these filters.' : 'No cheque on the sheet matched an unposted PDC.' }}</p></td></tr>
          @endforelse
        </tbody>
      </table>
    </div></div>
    <div id="pagination_m"><div class="text-center">{{ $matched->links() }}</div></div>
</div>

<div class="bsp-panel" role="tabpanel" id="bsp-panel-cannot" aria-labelledby="bsp-tab-cannot" tabindex="0" {{ $activeTab === 'cannot' ? '' : 'hidden' }}>
    <div class="bsp-head">
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
            <th>Building / Unit</th>
            <th>Tenant</th>
            <th>Pay Account</th>
            <th width="14%">Status</th>
            <th>Why</th>
          </tr>
          <tr class="bsp-filters">
            <td></td>
            <td>{!! $fin('c', 'cheque') !!}</td>
            <td>{!! $fin('c', 'bank') !!}</td>
            <td>{!! $fin('c', 'date', 'dd/mm/yyyy') !!}</td>
            <td>{!! $fin('c', 'amount') !!}</td>
            <td>{!! $fin('c', 'building') !!}</td>
            <td>{!! $fin('c', 'tenant') !!}</td>
            <td></td>
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
            {{-- PLMS PDCs behind this row, for reference only (never posted from here) --}}
            <td>
              @forelse($r['related'] as $p)
                {{ $p->building_name }} / {{ $p->unit_no }}
                @if($r['related_kind'] === 'other_bank')<span class="bsp-sub">same cheque no. on {{ $p->bank_name }}</span>@elseif(!$loop->last)<br>@endif
              @empty
                -
              @endforelse
            </td>
            <td>
              @forelse($r['related'] as $p)
                {{ $p->tenant_name }}
                <span class="bsp-sub">{{ $p->tenant_contract_no }}</span>
              @empty
                -
              @endforelse
            </td>
            <td>{!! $accCell($r, $r['related']) !!}</td>
            <td class="bsp-status"><span class="btn-circle {{ $l[0] }} btn-sm m-b-10 status"><b>{{ $l[1] }}</b></span></td>
            <td>{{ $r['reason'] }}</td>
          </tr>
          @empty
          <tr><td colspan="10" align="center"><p>{{ $filtering['cannot'] ? 'Nothing here fits these filters.' : 'None — every accepted cheque on the sheet was matched.' }}</p></td></tr>
          @endforelse
        </tbody>
      </table>
    </div></div>
    <div id="pagination_c"><div class="text-center">{{ $cannot->links() }}</div></div>
</div>

{{-- Rejected by the bank: mark the PDC bounced --}}
@php
  $bounceReasons = \Modules\BackOffice\Services\BankSheetRejectedCheques::reasons();
  $plmsCell = function ($group) {
      $first = $group[0];
      $amount = 0; $receipts = [];
      foreach ($group as $p) { $amount += $p->pdc_amt; if ($p->pdc_receipt_no) { $receipts[] = $p->pdc_receipt_no; } }
      return e($first->tenant_name) . '<span class="bsp-sub">' . e($first->tenant_contract_no) . ' · ' . e($first->building_name) . ' / ' . e($first->unit_no)
           . ' · PDC ' . numberFormat($amount) . ' on ' . e(\Carbon\Carbon::parse($first->pdc_check_date)->format('d/m/Y'))
           . (count($group) > 1 ? ' (rent + VAT)' : '') . ($receipts ? ' · receipt ' . e(implode(', ', $receipts)) . ' will be cancelled' : '') . '</span>';
  };
@endphp
<div class="bsp-panel" role="tabpanel" id="bsp-panel-rejected" aria-labelledby="bsp-tab-rejected" tabindex="0" {{ $activeTab === 'rejected' ? '' : 'hidden' }}>
    <div class="bsp-head">
      <span class="bsp-hint">Not posted. Mark the PDC bounced here: the same as the PDC bounce screen (receipt cancelled if any, ARE notified).</span>
      @if(count($rejected) > 0)
      <div class="bsp-bulk" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:flex-end;margin-left:auto">
        <span class="bsp-sub" id="bsp-bulk-status" role="status" aria-live="polite" style="display:inline"></span>
        <button type="button" class="bsp-bounce-btn" id="bsp-bounce-selected" disabled>
          <i class="fa fa-times-circle" aria-hidden="true" style="margin-right:6px"></i>Bounce selected cheques (<span id="bsp-bounce-count">0</span>)
        </button>
      </div>
      @endif
    </div>
    <div class="table-wrap"><div class="table-responsive">
      <table class="table display product-overview mb-30">
        <thead>
          <tr>
            <th width="3%"><input type="checkbox" id="bsp-bounce-all" aria-label="Select all rejected cheques that can be bounced" title="Select all"></th>
            <th width="5%">Sheet Row</th>
            <th width="10%">Cheque No</th>
            <th width="14%">Bank (sheet)</th>
            <th width="9%">Cheque Date</th>
            <th class="num" width="8%">Amount</th>
            <th width="14%">Bank's reason</th>
            <th>PLMS PDC</th>
            <th width="22%">Mark as bounced</th>
          </tr>
        </thead>
        <tbody>
          @if(count($rejected) === 0)
          <tr><td colspan="9" align="center"><p>None — the bank accepted every cheque on this sheet.</p></td></tr>
          @endif
          @foreach($rejected as $x)
          @php $suggested = \Modules\BackOffice\Services\BankSheetRejectedCheques::bounceReasonFor($x['row']['reject_reason']); @endphp
          @foreach(($x['groups'] ?: [null]) as $group)
          @php
            // tick box / button unless every PDC of this contract is already bounced
            $canBounce = $group !== null && \Modules\BackOffice\Services\BankSheetRejectedCheques::canBounce($x, $group);
          @endphp
          <tr>
            <td>
              @if($canBounce)
                {{-- same cheque no. and amount on several contracts ("choose"): never ticked by select all, only one is right --}}
                <input type="checkbox" class="bsp-bounce-pick" data-choose="{{ $x['status'] === 'choose' ? 1 : 0 }}"
                       aria-label="Select cheque {{ $x['row']['cheque_no'] }}{{ $x['status'] === 'choose' ? ' (' . $group[0]->tenant_contract_no . ')' : '' }} to bounce">
              @endif
            </td>
            @if($loop->first)
            <td rowspan="{{ max(count($x['groups']), 1) }}">{{ $x['row']['row_no'] }}</td>
            <td rowspan="{{ max(count($x['groups']), 1) }}"><b>{{ $x['row']['cheque_no'] }}</b></td>
            <td rowspan="{{ max(count($x['groups']), 1) }}">{{ $x['row']['bank_name'] }}</td>
            <td rowspan="{{ max(count($x['groups']), 1) }}">{{ $fmtDate($x['row']['cheque_date']) }}</td>
            <td rowspan="{{ max(count($x['groups']), 1) }}" class="num">{{ numberFormat($x['row']['amount']) }}</td>
            <td rowspan="{{ max(count($x['groups']), 1) }}">{{ $x['row']['reject_reason'] ?: '-' }}</td>
            @endif
            @if($group === null)
              <td>-</td>
              <td><span class="bsp-sub" style="display:inline">Not found in PLMS (no open or bounced PDC with this cheque no.)</span></td>
            @else
              <td>{!! $plmsCell($group) !!}@if($x['status'] === 'choose')<span class="bsp-sub"><b>@if(!empty($x['amount_match']))Same cheque no. and amount on {{ count($x['groups']) }} contracts: tick and bounce only the right one.@else Same cheque no. on {{ count($x['groups']) }} contracts (PDCs dated up to 6 months before the deposit date), none for the bank amount {{ number_format((float) $x['row']['amount'], 3) }}: check the cheque before bouncing.@endif</b></span>@endif</td>
              <td class="bsp-bounce-cell">
                @php $bounced = true; foreach ($group as $p) { if ($p->pdc_cancel_reason !== \Modules\BackOffice\Services\BankSheetRejectedCheques::BOUNCED) { $bounced = false; } } @endphp
                @if($bounced)
                  <span class="bsp-pill bsp-pill-cannot"><i class="fa fa-check" aria-hidden="true"></i>Already bounced{{ $group[0]->bounce_reason_name ? ': ' . $group[0]->bounce_reason_name : '' }}</span>
                @else
                  <select class="bsp-bounce-reason" aria-label="Bounce reason for cheque {{ $x['row']['cheque_no'] }}">
                    <option value="">Select reason</option>
                    @foreach($bounceReasons as $code => $label)
                    <option value="{{ $code }}" {{ $suggested === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                  <button type="button" class="bsp-bounce-btn"
                          data-ids="{{ implode(',', array_map(function ($p) { return $p->id; }, array_filter($group, function ($p) { return $p->pdc_cancel_reason !== \Modules\BackOffice\Services\BankSheetRejectedCheques::BOUNCED; }))) }}"
                          data-cheque="{{ $x['row']['cheque_no'] }}" data-remark="{{ $x['row']['reject_reason'] }}">Mark as bounced</button>
                  <span class="bsp-sub bsp-bounce-msg"></span>
                @endif
              </td>
            @endif
          </tr>
          @endforeach
          @endforeach
        </tbody>
      </table>
    </div></div>
</div>

</div>{{-- /card --}}
</div></div>{{-- /#bsp-tabs --}}

</div>
@endsection

@section('scripts')
<script>
// Result tabs (WAI-ARIA tabs pattern): click / arrow keys switch, the tab is kept in the URL
// (?tab=) and in the filter form, so paging, filtering and Back return to the same tab.
(function () {
  var $tabs = $('.bsp-tab');
  function activate(key, focus) {
    $tabs.each(function () {
      var on = $(this).data('tab') === key;
      $(this).attr({'aria-selected': on ? 'true' : 'false', tabindex: on ? '0' : '-1'});
      $('#' + $(this).attr('aria-controls')).prop('hidden', !on);
      if (on && focus) { this.focus(); }
    });
    $('#bsp-tab-input').val(key);
    if (window.history && window.history.replaceState) {
      var url = new URL(window.location.href);
      url.searchParams.set('tab', key);
      window.history.replaceState(null, '', url.toString());
    }
  }
  $tabs.on('click', function () { activate($(this).data('tab'), false); });
  $tabs.on('keydown', function (e) {
    var i = $tabs.index(this), n = $tabs.length, j = null;
    if (e.key === 'ArrowRight') { j = (i + 1) % n; }
    else if (e.key === 'ArrowLeft') { j = (i - 1 + n) % n; }
    else if (e.key === 'Home') { j = 0; }
    else if (e.key === 'End') { j = n - 1; }
    if (j !== null) { e.preventDefault(); activate($tabs.eq(j).data('tab'), true); }
  });
  $(document).on('click', '.bsp-goto-tab', function (e) {
    e.preventDefault();
    activate($(this).data('tab'), true);
    document.getElementById('bsp-tabs').scrollIntoView({block: 'start'});
  });
})();

// Rejected by the bank: mark the PDC(s) of one cheque bounced through the PDC bounce action.
// Returns a promise resolved with 'ok', 'skipped' (no reason chosen) or 'failed'.
function bspBounceCell($cell) {
  var d = $.Deferred();
  var $btn = $cell.find('.bsp-bounce-btn');
  var reason = $cell.find('.bsp-bounce-reason').val();
  var $msg = $cell.find('.bsp-bounce-msg');
  if (!reason) { $msg.text('Select the bounce reason first.'); return d.resolve('skipped').promise(); }
  var ids = String($btn.data('ids')).split(',').filter(function (v) { return v !== ''; });
  $btn.prop('disabled', true).text('Marking...');
  $cell.find('.bsp-bounce-reason').prop('disabled', true);
  var $pick = $cell.closest('tr').find('.bsp-bounce-pick').prop('disabled', true);
  var token = $('meta[name="csrf-token"]').attr('content');
  (function next(i) {
    if (i >= ids.length) {
      $cell.html('<span class="bsp-pill bsp-pill-cannot"><i class="fa fa-check" aria-hidden="true"></i>Marked bounced: ' +
        $('<div>').text($cell.find('option:selected').text() || '').html() + '</span>');
      $pick.prop('checked', false);
      d.resolve('ok');
      return;
    }
    $.ajax({method: 'POST', url: @json(route('tenantAjaxPdcBounceSearch')), data: {id: ids[i], pdc_bounce_reason: reason, bounce_remark: $btn.data('remark') || '', _token: token}, dataType: 'json'})
      .done(function () { next(i + 1); })
      .fail(function (x) {
        // the bounce action saves first and answers with an empty body when the building has no ARE assigned
        if (x.status === 200) { next(i + 1); return; }
        $btn.prop('disabled', false).text('Mark as bounced');
        $cell.find('.bsp-bounce-reason').prop('disabled', false);
        $pick.prop('disabled', false);
        $msg.text('Error ' + x.status + ' while marking cheque ' + $btn.data('cheque') + ' bounced. It may already be saved (the notification can fail after saving): check the PDC before trying again.');
        d.resolve('failed');
      });
  })(0);
  return d.promise();
}

$(document).on('click', '.bsp-bounce-cell .bsp-bounce-btn', function () {
  bspBounceCell($(this).closest('.bsp-bounce-cell')).always(bspBulkRefresh);
});

// Bulk: tick cheques (or "select all") and bounce them one after another.
function bspBulkRefresh() {
  var $picks = $('.bsp-bounce-pick:enabled');
  var n = $picks.filter(':checked').length;
  $('#bsp-bounce-count').text(n);
  $('#bsp-bounce-selected').prop('disabled', n === 0);
  var $auto = $picks.filter('[data-choose="0"]');
  $('#bsp-bounce-all').prop('checked', $auto.length > 0 && $auto.filter(':checked').length === $auto.length)
                      .prop('disabled', $picks.length === 0);
}
$(document).on('change', '.bsp-bounce-pick', bspBulkRefresh);
$(document).on('change', '#bsp-bounce-all', function () {
  // same cheque no. on several contracts (data-choose=1): never ticked automatically
  $('.bsp-bounce-pick:enabled[data-choose="0"]').prop('checked', this.checked);
  bspBulkRefresh();
});
$(document).on('click', '#bsp-bounce-selected', function () {
  var $cells = $('.bsp-bounce-pick:enabled:checked').closest('tr').find('.bsp-bounce-cell');
  if (!$cells.length) { return; }
  if (!confirm('Mark ' + $cells.length + ' cheque(s) as bounced?\n\nEach one is bounced like the PDC bounce screen: receipt cancelled if any, ARE notified, the bank\'s reason saved as remarks.')) { return; }
  var $status = $('#bsp-bulk-status'), $go = $(this).prop('disabled', true), $all = $('#bsp-bounce-all').prop('disabled', true);
  var done = {ok: 0, skipped: 0, failed: 0}, total = $cells.length;
  (function next(i) {
    if (i >= total) {
      $status.text(done.ok + ' marked bounced' + (done.skipped ? ', ' + done.skipped + ' skipped (no reason selected)' : '')
        + (done.failed ? ', ' + done.failed + ' failed (see the message on the row)' : '') + '.');
      $all.prop('disabled', false);
      bspBulkRefresh();
      return;
    }
    $status.text('Marking ' + (i + 1) + ' of ' + total + '...');
    bspBounceCell($cells.eq(i)).always(function (r) { done[r] = (done[r] || 0) + 1; next(i + 1); });
  })(0);
});
$(bspBulkRefresh);

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

  // Post over AJAX so a server error or a dropped connection is shown instead of an endless spinner.
  var postBtnHtml = $('#bsp-post-btn').html();
  function postError(html, allowRetry) {
    $('#bsp-post-error').html(html).show();
    $('html, body').animate({ scrollTop: $('#bsp-post-error').offset().top - 80 }, 200);
    if (allowRetry) {
      $('#bsp-post-btn').html(postBtnHtml).prop('disabled', count === 0);
    } else {
      $('#bsp-post-btn').html('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Check history');
    }
  }
  $('#bsp-post-form').on('submit', function (e) {
    e.preventDefault();
    if (count === 0) { return; }
    if (!confirm('Post ' + count + ' cheque(s) to ' + $('#bsp-bank option:selected').text() + ' on ' + $('#bsp-date').val() + '?\n\nThis generates receipts and cannot be undone.')) { return; }
    $('#bsp-post-error').hide().empty();
    $('#bsp-post-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Posting…');
    var historyLink = ' <a href="{{ route('pdcBankSheetUploads') }}">Open Posting history</a> or <a href="' + window.location.href + '">reload this page</a> before posting again.';
    $.ajax({ method: 'POST', url: this.action, data: $(this).serialize(), dataType: 'json', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .done(function (res) {
        try {
          if (res && res.ok && res.redirect) {
            $('#bsp-post-btn').html('<i class="fa fa-check" aria-hidden="true"></i> Posted — opening result…');
            window.location.href = res.redirect;
          } else {
            postError($('<div>').text((res && res.message) || 'Posting failed.').html(), true);
          }
        } catch (err) {
          postError('Unexpected reply from the server (' + $('<div>').text(err.message).html() + ').' + historyLink, false);
        }
      })
      .fail(function (xhr, status) {
        var res = xhr.responseJSON, msg;
        if (xhr.status === 422 && res) {
          // our own error, or Laravel validation (errors: {field: [msg]})
          msg = res.message || 'Posting failed.';
          if (res.errors) { $.each(res.errors, function (k, v) { msg = v[0]; return false; }); }
          postError($('<div>').text(msg).html(), true);
        } else if (xhr.status === 419) {
          postError('Your session expired. <a href="' + window.location.href + '">Reload the page</a> and post again.', false);
        } else if (status === 'parsererror') {
          postError('The server sent back a page instead of a result (you may have been logged out).' + historyLink, false);
        } else if (status === 'timeout' || xhr.status === 0 || xhr.status === 504 || xhr.status === 502) {
          postError('The server took too long to answer (' + (xhr.status || status) + '). The posting may still finish in the background.' + historyLink, false);
        } else {
          postError('Posting failed with a server error (' + xhr.status + ' ' + $('<div>').text(xhr.statusText || status).html() + '). Some cheques may already be posted.' + historyLink, false);
        }
      });
  });
});
</script>
@endsection
