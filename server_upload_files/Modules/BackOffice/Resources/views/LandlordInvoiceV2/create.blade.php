@extends('layouts.plms-app')
@section('css')
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css') }}">
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<link href="{{ asset('public/css/formlayout.css') }}" rel="stylesheet" type="text/css" />
<style>
    /* Page tokens: trust blue on neutral, tabular numerals, 44px tap targets. */
    #liv2_form {
        --liv2-blue: #2563eb;
        --liv2-blue-soft: #eff6ff;
        --liv2-ink: #1f2937;
        --liv2-muted: #6b7280;
        --liv2-line: #e5e7eb;
        --liv2-bg: #f9fafb;
        --liv2-danger: #dc2626;
        --liv2-radius: 6px;
        --liv2-tap: 44px;
        color: var(--liv2-ink);
    }
    #liv2_form .liv2-section-head {
        display: flex; align-items: center; gap: 10px;
        font-size: 15px; font-weight: 700; color: var(--liv2-ink);
        border-left: 4px solid var(--liv2-blue);
        padding: 8px 14px; margin: 24px 0 12px;
        background: var(--liv2-blue-soft); border-radius: var(--liv2-radius);
    }
    #liv2_form .liv2-section-head:first-child { margin-top: 0; }
    #liv2_form .liv2-section-head .liv2-step-no {
        display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px; border-radius: 50%;
        background: var(--liv2-blue); color: #fff; font-size: 12px; font-weight: 700;
    }
    #liv2_form .liv2-badge {
        margin-left: auto; font-size: 11px; font-weight: 600; letter-spacing: .03em;
        padding: 3px 8px; border-radius: 999px; background: var(--liv2-blue); color: #fff;
    }
    #liv2_form .liv2-badge-muted { background: #e5e7eb; color: var(--liv2-muted); }
    #liv2_form .liv2-hint { display: block; margin-top: 4px; font-size: 12px; color: var(--liv2-muted); }
    #liv2_form .liv2-hidden { display: none; }
    #liv2_form .form-control, #liv2_form select.form-control { min-height: var(--liv2-tap); }
    #liv2_form .form-control:focus { border-color: var(--liv2-blue); box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
    #liv2_form .form-group.has-error .help-block { color: var(--liv2-danger); font-size: 12px; margin: 4px 0 0; }
    #liv2_form .liv2-alert {
        border: 1px solid #fecaca; background: #fef2f2; color: #991b1b;
        border-radius: var(--liv2-radius); padding: 10px 14px; margin-bottom: 12px; font-size: 13px;
    }
    #liv2_form .liv2-alert ul { margin: 4px 0 0 18px; padding: 0; }

    /* Loading state on cascading selects / panels */
    #liv2_form .liv2-loading { position: relative; }
    #liv2_form .liv2-loading::after {
        content: ""; position: absolute; inset: 0; background: rgba(255,255,255,.6);
        border-radius: var(--liv2-radius); z-index: 2;
    }
    #liv2_form .liv2-loading::before {
        content: ""; position: absolute; top: 50%; left: 50%; width: 22px; height: 22px; margin: -11px 0 0 -11px;
        border: 3px solid var(--liv2-line); border-top-color: var(--liv2-blue); border-radius: 50%;
        animation: liv2spin .8s linear infinite; z-index: 3;
    }
    @keyframes liv2spin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { #liv2_form .liv2-loading::before { animation: none; } }

    /* Overview stat tiles */
    #liv2_form .liv2-stat-tile {
        border: 1px solid var(--liv2-line); border-radius: 8px; padding: 12px 16px; margin-bottom: 15px; background: #fff;
    }
    #liv2_form .liv2-stat-label {
        display: block; font-size: 11px; color: var(--liv2-muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px;
    }
    #liv2_form .liv2-stat-value { display: block; font-size: 19px; font-weight: 700; color: var(--liv2-ink); font-variant-numeric: tabular-nums; }
    #liv2_form .liv2-stat-tile.liv2-stat-accent .liv2-stat-value { color: var(--liv2-blue); }
    #liv2_form .liv2-kv { margin: 0 0 10px; font-size: 14px; }
    #liv2_form .liv2-kv b { color: var(--liv2-muted); font-weight: 600; margin-right: 6px; }

    /* Lines tables */
    #liv2_form .liv2-table { margin-bottom: 8px; }
    #liv2_form .liv2-table thead th {
        background: var(--liv2-bg); font-size: 12px; text-transform: uppercase; letter-spacing: .03em;
        color: var(--liv2-muted); border-bottom: 2px solid var(--liv2-line); vertical-align: middle;
    }
    #liv2_form .liv2-table td { vertical-align: middle; }
    #liv2_form .liv2-table .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    #liv2_form .liv2-table tfoot td { font-weight: 700; background: var(--liv2-bg); }
    #liv2_form .liv2-row .form-control { margin: 0; }
    #liv2_form .liv2-row.has-error .liv2-head-search { border-color: var(--liv2-danger); }
    #liv2_form .liv2-calc-tag { font-size: 12px; color: var(--liv2-muted); }
    #liv2_form .liv2-remove {
        width: var(--liv2-tap); height: var(--liv2-tap); padding: 0; line-height: 1; font-size: 20px;
        color: var(--liv2-muted); background: none; border: 0; border-radius: var(--liv2-radius);
    }
    #liv2_form .liv2-remove:hover, #liv2_form .liv2-remove:focus { color: var(--liv2-danger); background: #fef2f2; outline: none; }
    #liv2_form .liv2-add {
        min-height: var(--liv2-tap); border: 1px dashed var(--liv2-blue); color: var(--liv2-blue); background: #fff;
        border-radius: var(--liv2-radius); padding: 0 16px; font-weight: 600;
    }
    #liv2_form .liv2-add:hover, #liv2_form .liv2-add:focus { background: var(--liv2-blue-soft); }
    #liv2_form .liv2-empty {
        border: 1px dashed var(--liv2-line); border-radius: var(--liv2-radius); padding: 14px; text-align: center;
        color: var(--liv2-muted); font-size: 13px; margin-bottom: 8px;
    }
    #liv2_form .liv2-lines-wait { color: var(--liv2-muted); font-size: 13px; padding: 12px 0; }

    /* Sticky totals */
    #liv2_form .liv2-summary {
        position: sticky; bottom: 0; z-index: 5; margin-top: 24px;
        background: #fff; border-top: 2px solid var(--liv2-line); box-shadow: 0 -6px 16px rgba(0,0,0,.06);
        padding: 14px 16px; display: flex; flex-wrap: wrap; gap: 16px 32px; align-items: center;
    }
    #liv2_form .liv2-summary-col { min-width: 150px; }
    #liv2_form .liv2-summary-col .liv2-stat-label { margin-bottom: 2px; }
    #liv2_form .liv2-summary-val { font-size: 15px; font-weight: 600; font-variant-numeric: tabular-nums; }
    #liv2_form .liv2-summary-sub { font-size: 12px; color: var(--liv2-muted); font-variant-numeric: tabular-nums; }
    #liv2_form .liv2-grand { font-size: 20px; font-weight: 700; color: var(--liv2-blue); font-variant-numeric: tabular-nums; }
    #liv2_form .liv2-summary-actions { margin-left: auto; text-align: right; }
    #liv2_form .liv2-summary-note { display: block; font-size: 12px; color: var(--liv2-muted); margin-top: 6px; }
    #liv2_form #liv2_save { min-height: var(--liv2-tap); min-width: 160px; font-weight: 700; }
    .ui-autocomplete { z-index: 1060; max-height: 260px; overflow-y: auto; overflow-x: hidden; }
</style>
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class="pull-left">
            <div class="page-title">Add Landlord Invoice</div>
        </div>
        {{ Breadcrumbs::render('landlord-invoice-v2.create') }}
    </div>
</div>

<form action="{{ route('landlord-invoice-v2.store') }}" method="POST" id="liv2_form" class="form-horizontal" autocomplete="off">
{{ csrf_field() }}
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">

@php
    $lineErrors = collect($errors->keys())->filter(function ($k) {
        return strpos($k, 'tax_lines.') === 0 || strpos($k, 'od_lines.') === 0;
    })->map(function ($k) use ($errors) { return $errors->first($k); })->unique()->values();
@endphp
@if($errors->has('lines') || $lineErrors->count())
<div class="liv2-alert" role="alert" tabindex="-1" id="liv2_line_errors">
    <strong>Please fix the invoice lines.</strong>
    <ul>
        @if($errors->has('lines'))<li>{{ $errors->first('lines') }}</li>@endif
        @foreach($lineErrors as $msg)<li>{{ $msg }}</li>@endforeach
    </ul>
</div>
@endif

<div class="liv2-section-head"><span class="liv2-step-no">1</span> Invoice details</div>
<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group {{ $errors->has('invoice_date') ? 'has-error' : '' }}">
                <label for="invoice_date">Invoice Date<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="date" class="form-control" id="invoice_date" name="invoice_date" required value="{{ old('invoice_date', date('Y-m-d')) }}">
                </div>
                @if($errors->has('invoice_date'))<span class="help-block">{{ $errors->first('invoice_date') }}</span>@endif
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group {{ $errors->has('period_month') ? 'has-error' : '' }}">
                <label for="period_month">Period Month<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar icn-add" aria-hidden="true"></i>
                    <select class="form-control" id="period_month" name="period_month" required>
                        @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ (int) old('period_month', date('n')) === $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endforeach
                    </select>
                </div>
                @if($errors->has('period_month'))<span class="help-block">{{ $errors->first('period_month') }}</span>@endif
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group {{ $errors->has('period_year') ? 'has-error' : '' }}">
                <label for="period_year">Period Year<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="period_year" name="period_year" required min="2000" max="2100" value="{{ old('period_year', date('Y')) }}">
                </div>
                @if($errors->has('period_year'))<span class="help-block">{{ $errors->first('period_year') }}</span>@endif
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-4">
            <div class="form-group {{ $errors->has('building_id') ? 'has-error' : '' }}">
                <label for="building_id">Building<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="icon icon-building" aria-hidden="true"></i>
                    <select class="form-control" id="building_id" name="building_id" required>
                        <option value="">Select Building</option>
                        @foreach($buildings as $b)
                        <option value="{{ $b->id }}" {{ (int) old('building_id') === (int) $b->id ? 'selected' : '' }}>{{ $b->building_name }}</option>
                        @endforeach
                    </select>
                </div>
                <span class="liv2-hint">Only buildings with an approved landlord contract are listed.</span>
                @if($errors->has('building_id'))<span class="help-block">{{ $errors->first('building_id') }}</span>@endif
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group {{ $errors->has('vendor_id') ? 'has-error' : '' }}">
                <label for="vendor_id">Landlord<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="icon icon-landlord" aria-hidden="true"></i>
                    <select class="form-control" id="vendor_id" name="vendor_id" required disabled>
                        <option value="">Select Building First</option>
                    </select>
                </div>
                <span class="liv2-hint" id="vendor_hint">Filled from the building; picked for you when there is only one.</span>
                @if($errors->has('vendor_id'))<span class="help-block">{{ $errors->first('vendor_id') }}</span>@endif
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group {{ $errors->has('landlord_contract_id') ? 'has-error' : '' }}">
                <label for="landlord_contract_id">Contract<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                    <select class="form-control" id="landlord_contract_id" name="landlord_contract_id" required disabled>
                        <option value="">Select Landlord First</option>
                    </select>
                </div>
                <span class="liv2-hint" id="contract_hint">Invoice numbers are generated on save.</span>
                @if($errors->has('landlord_contract_id'))<span class="help-block">{{ $errors->first('landlord_contract_id') }}</span>@endif
            </div>
        </div>
    </div>
</div>

{{-- Step 2: contract & building context, shown once a contract is chosen --}}
<div id="liv2_context" class="liv2-hidden">
<div class="liv2-section-head"><span class="liv2-step-no">2</span> Contract &amp; building</div>
<div class="dashboardtab1">
<div class="card card-box">
<div class="card-body" id="liv2_context_body">
<header class="panel-heading custom-tab">
    <ul class="nav nav-tabs">
        <li class="nav-item"><a href="#liv2_tab_details" data-toggle="tab" class="active">Contract &amp; Building Details</a></li>
        <li class="nav-item"><a href="#liv2_tab_overview" data-toggle="tab">Overview</a></li>
    </ul>
</header>
<div class="panel-body tab-color">
<div class="tab-content">
    <div class="tab-pane active" id="liv2_tab_details">
        <div class="row">
            <div class="col-lg-6 p-t-20"><p class="liv2-kv"><b>Landlord</b><span id="disp_vendor_name">-</span></p></div>
            <div class="col-lg-6 p-t-20"><p class="liv2-kv"><b>Building</b><span id="disp_building_name">-</span></p></div>
            <div class="col-lg-6"><p class="liv2-kv"><b>Address</b><span id="disp_vendor_address">-</span></p></div>
            <div class="col-lg-6"><p class="liv2-kv"><b>VATIN No</b><span id="disp_vatin_no">-</span></p></div>
        </div>
    </div>
    <div class="tab-pane" id="liv2_tab_overview">
        <div class="row">
            @php
                $tiles = [
                    ['income', 'Income as per Rental Agreement', true],
                    ['collection', 'Selected Month Collection', true],
                    ['expenses', 'Total Expenses for the Month', false],
                    ['transfer', 'Amount Transfer to Landlord', true],
                    ['total_units', 'Total Number of Units', true],
                    ['occupancy_level', 'Total Building Occupancy Level', true],
                    ['new_leased_res', 'New Leased Residential Units', false],
                    ['new_leased_com', 'New Leased Commercial Units', false],
                    ['occupied_res', 'Total Occupied Residential Units', false],
                    ['occupied_com', 'Total Occupied Commercial Units', false],
                    ['vacant_res', 'Vacant Residential Units', false],
                    ['vacant_com', 'Vacant Commercial Units', false],
                    ['evac_res', 'Residential Units Under Evacuation', false],
                    ['evac_com', 'Commercial Units Under Evacuation', false],
                ];
            @endphp
            @foreach($tiles as $t)
            <div class="col-sm-6 col-md-3">
                <div class="liv2-stat-tile {{ $t[2] ? 'liv2-stat-accent' : '' }}">
                    <span class="liv2-stat-label">{{ $t[1] }}</span>
                    <span class="liv2-stat-value" id="disp_ov_{{ $t[0] }}">-</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>
</div>

{{-- Steps 3 & 4: the two invoice line sections --}}
<div id="liv2_lines" class="liv2-hidden">
    @foreach([['tax', 3, 'Tax Invoice lines', '5% VAT', 'liv2-badge'], ['od', 4, 'Other Deductions lines', 'No VAT', 'liv2-badge liv2-badge-muted']] as $sec)
    @php list($key, $no, $title, $badge, $badgeClass) = $sec; @endphp
    <div class="liv2-section-head"><span class="liv2-step-no">{{ $no }}</span> {{ $title }} <span class="{{ $badgeClass }}">{{ $badge }}</span></div>
    <div class="dataSearchBox">
        <div class="table-responsive">
            <table class="table liv2-table" id="liv2_{{ $key }}_table">
                <thead>
                    <tr>
                        <th style="width:26%">Head</th>
                        <th>Description</th>
                        <th class="num" style="width:150px">Amount (OMR)</th>
                        @if($key === 'tax')<th class="num" style="width:110px">VAT 5%</th>@endif
                        <th class="num" style="width:130px">Total</th>
                        <th style="width:48px"><span class="sr-only">Remove</span></th>
                    </tr>
                </thead>
                <tbody id="liv2_{{ $key }}_body" data-prefix="{{ $key }}_lines" data-vat="{{ $key === 'tax' ? '1' : '0' }}"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right">Subtotal</td>
                        <td class="num" id="liv2_{{ $key }}_sub">0.000</td>
                        @if($key === 'tax')<td class="num" id="liv2_tax_vat">0.000</td>@endif
                        <td class="num" id="liv2_{{ $key }}_total">0.000</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="liv2-empty" id="liv2_{{ $key }}_empty">No lines — this invoice type will be skipped on save.</div>
        <button type="button" class="liv2-add" id="liv2_{{ $key }}_add" data-type="{{ $key }}"><i class="fa fa-plus" aria-hidden="true"></i> Add line</button>
    </div>
    @endforeach
</div>

<div class="liv2-summary" id="liv2_summary">
    <div class="liv2-summary-col">
        <span class="liv2-stat-label">Tax Invoice</span>
        <span class="liv2-summary-val" id="liv2_sum_tax_total">0.000</span>
        <span class="liv2-summary-sub">incl. VAT <span id="liv2_sum_tax_vat">0.000</span></span>
    </div>
    <div class="liv2-summary-col">
        <span class="liv2-stat-label">Other Deductions</span>
        <span class="liv2-summary-val" id="liv2_sum_od_total">0.000</span>
    </div>
    <div class="liv2-summary-col">
        <span class="liv2-stat-label">Combined total</span>
        <span class="liv2-grand" id="liv2_grand">0.000</span>
    </div>
    <div class="liv2-summary-actions">
        <button type="submit" class="btn btn-primary" id="liv2_save" disabled>SAVE INVOICES</button>
        <span class="liv2-summary-note" id="liv2_summary_note">Select a contract to load the invoice lines.</span>
    </div>
</div>

</div>
</div>
</div>
</form>
@endsection

@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }}"></script>
<script>
$(function () {
    var URLS = {
        vendors:   '{{ route("landlordInvoiceV2VendorsByBuilding") }}',
        contracts: '{{ route("landlordInvoiceV2ContractsByBuildingVendor") }}',
        details:   '{{ url("landlord-invoice-v2-contract-details") }}',
        preview:   '{{ route("landlordInvoiceV2CalculationPreview") }}',
        overview:  '{{ route("landlordInvoiceV2OverviewPreview") }}',
        heads:     '{{ route("expenseAccountCodeAutocomplete") }}'
    };
    var TYPE_PARAM = { tax: 'tax_invoice', od: 'other_deductions' };
    var VAT = 0.05;
    @php
        $oldForm = [
            'building_id'          => old('building_id'),
            'vendor_id'            => old('vendor_id'),
            'landlord_contract_id' => old('landlord_contract_id'),
            'tax_lines'            => old('tax_lines', []),
            'od_lines'             => old('od_lines', []),
        ];
    @endphp
    var oldForm = {!! json_encode($oldForm) !!};

    var $building = $('#building_id'), $vendor = $('#vendor_id'), $contract = $('#landlord_contract_id');

    function round3(n) { return Math.round((parseFloat(n) || 0) * 1000) / 1000; }
    function fmt(n) { return round3(n).toFixed(3); }
    function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }

    function setLoading($el, on) {
        $el.toggleClass('liv2-loading', !!on).attr('aria-busy', on ? 'true' : null);
    }
    function fillSelect($sel, items, placeholder) {
        $sel.empty().append($('<option>').val('').text(placeholder));
        $.each(items, function (i, it) { $sel.append($('<option>').val(it.id).text(it.label)); });
    }
    function failAlert(what) {
        var $a = $('#liv2_ajax_alert');
        if (!$a.length) {
            $a = $('<div class="liv2-alert" id="liv2_ajax_alert" role="alert"></div>').prependTo('#liv2_form .salesSearchBox');
        }
        $a.text('Could not load ' + what + '. Check your connection and try again.');
    }

    /* ---------- cascade: building -> landlord -> contract ---------- */
    function resetBelow(level) {
        if (level === 'building') {
            fillSelect($vendor, [], 'Select Building First');
            $vendor.prop('disabled', true);
        }
        fillSelect($contract, [], 'Select Landlord First');
        $contract.prop('disabled', true);
        $('#liv2_context, #liv2_lines').addClass('liv2-hidden');
        $('#liv2_tax_body, #liv2_od_body').empty();
        $('[id^="disp_"]').text('-');
        recalc();
    }

    // pre = {vendor, contract} ids to reselect when restoring after a validation error.
    function loadVendors(buildingId, pre, done) {
        pre = pre || {};
        setLoading($vendor.parent(), true);
        $.getJSON(URLS.vendors, { building_id: buildingId }, function (vendors) {
            fillSelect($vendor, vendors, vendors.length ? 'Select Landlord' : 'No landlord with an approved contract');
            $vendor.prop('disabled', !vendors.length);
            var pick = pre.vendor || (vendors.length === 1 ? vendors[0].id : '');
            if (pick) {
                $vendor.val(String(pick));
                loadContracts(buildingId, pick, pre.contract, done);
            } else if (done) { done(); }
        }).fail(function () { failAlert('landlords'); }).always(function () { setLoading($vendor.parent(), false); });
    }

    function loadContracts(buildingId, vendorId, preselect, done) {
        setLoading($contract.parent(), true);
        $.getJSON(URLS.contracts, { building_id: buildingId, vendor_id: vendorId }, function (contracts) {
            fillSelect($contract, contracts, contracts.length ? 'Select Contract' : 'No approved contract');
            $contract.prop('disabled', !contracts.length);
            var pick = preselect || (contracts.length === 1 ? contracts[0].id : '');
            if (pick) {
                $contract.val(String(pick));
                onContractSelected(!!preselect);
            }
            if (done) { done(); }
        }).fail(function () { failAlert('contracts'); }).always(function () { setLoading($contract.parent(), false); });
    }

    /* ---------- contract context ---------- */
    function onContractSelected(skipPreview) {
        var id = $contract.val();
        if (!id) { $('#liv2_context, #liv2_lines').addClass('liv2-hidden'); recalc(); return; }
        $('#liv2_context, #liv2_lines').removeClass('liv2-hidden');
        loadDetails(id);
        loadOverview();
        if (!skipPreview) { loadPreview('tax'); loadPreview('od'); }
    }

    function loadDetails(id) {
        $.getJSON(URLS.details + '/' + id, function (d) {
            $('#disp_vendor_name').text(d.vendor_name || '-');
            $('#disp_building_name').text(d.building_name || '-');
            $('#disp_vendor_address').text(d.vendor_address || '-');
            $('#disp_vatin_no').text(d.vatin_no || '-');
        }).fail(function () { failAlert('contract details'); });
    }

    function loadOverview() {
        var id = $contract.val(), m = $('#period_month').val(), y = $('#period_year').val();
        if (!id || !m || !y) return;
        setLoading($('#liv2_context_body'), true);
        $.getJSON(URLS.overview, { landlord_contract_id: id, period_month: m, period_year: y }, function (r) {
            $('#disp_ov_income').text(r.income);
            $('#disp_ov_collection').text(r.collection);
            $('#disp_ov_expenses').text(r.total_expenses);
            $('#disp_ov_transfer').text(r.transfer_to_landlord);
            $('#disp_ov_total_units').text(r.total_units);
            $('#disp_ov_occupancy_level').text(r.occupancy_level + '%');
            $('#disp_ov_new_leased_res').text(r.new_leased_residential);
            $('#disp_ov_new_leased_com').text(r.new_leased_commercial);
            $('#disp_ov_occupied_res').text(r.occupied_residential);
            $('#disp_ov_occupied_com').text(r.occupied_commercial);
            $('#disp_ov_vacant_res').text(r.vacant_residential);
            $('#disp_ov_vacant_com').text(r.vacant_commercial);
            $('#disp_ov_evac_res').text(r.evacuation_residential);
            $('#disp_ov_evac_com').text(r.evacuation_commercial);
        }).fail(function () { failAlert('the overview'); }).always(function () { setLoading($('#liv2_context_body'), false); });
    }

    /* ---------- lines ---------- */
    function loadPreview(type) {
        var id = $contract.val(), m = $('#period_month').val(), y = $('#period_year').val();
        if (!id || !m || !y) return;
        var $body = $('#liv2_' + type + '_body');
        setLoading($body.closest('.dataSearchBox'), true);
        $.getJSON(URLS.preview, { landlord_contract_id: id, invoice_type: TYPE_PARAM[type], period_month: m, period_year: y }, function (res) {
            $body.empty();
            $.each(res.lines, function (i, l) {
                addLine(type, { source: 'calc', description: l.description, amount: l.amount });
            });
            recalc();
        }).fail(function () { failAlert('the calculated lines'); }).always(function () { setLoading($body.closest('.dataSearchBox'), false); });
    }

    function addLine(type, data) {
        var $body = $('#liv2_' + type + '_body');
        var isTax = $body.data('vat') === 1 || $body.data('vat') === '1';
        var source = data.source === 'manual' ? 'manual' : 'calc';
        var $tr = $('<tr class="liv2-row">').attr('data-source', source);

        var $head = $('<td>');
        if (source === 'calc') {
            $head.append('<span class="liv2-calc-tag"><i class="fa fa-calculator" aria-hidden="true"></i> Calculated</span>');
            $head.append($('<input type="hidden" class="liv2-head-id">').val(data.acc_codes_id || ''));
        } else {
            $head.append($('<input type="text" class="form-control liv2-head-search" placeholder="Search head (code or name)" aria-label="Head">').val(data.head_label || ''));
            $head.append($('<input type="hidden" class="liv2-head-id">').val(data.acc_codes_id || ''));
            $head.append($('<input type="hidden" class="liv2-head-label">').val(data.head_label || ''));
        }
        $tr.append($head);
        $tr.append($('<td>').append($('<input type="text" class="form-control liv2-desc" aria-label="Description" required>').val(data.description || '')));
        $tr.append($('<td>').append($('<input type="number" step="0.001" min="0" class="form-control text-right liv2-amount" aria-label="Amount" required>').val(data.amount != null && data.amount !== '' ? fmt(data.amount) : '')));
        if (isTax) { $tr.append('<td class="num liv2-vat">0.000</td>'); }
        $tr.append('<td class="num liv2-total">0.000</td>');
        $tr.append('<td><button type="button" class="liv2-remove" aria-label="Remove line" title="Remove line">&times;</button></td>');
        $tr.append($('<input type="hidden" class="liv2-source">').val(source));

        $body.append($tr);
        if (source === 'manual') { bindHeadAutocomplete($tr); }
        renumber(type);
        recalc();
        if (source === 'manual' && !data.acc_codes_id) { $tr.find('.liv2-head-search').focus(); }
        return $tr;
    }

    function bindHeadAutocomplete($tr) {
        var $search = $tr.find('.liv2-head-search'), $id = $tr.find('.liv2-head-id'), $label = $tr.find('.liv2-head-label'), $desc = $tr.find('.liv2-desc');
        $search.autocomplete({
            source: URLS.heads,
            minLength: 1,
            autoFocus: true,
            select: function (e, ui) {
                $search.val(ui.item.value);
                $id.val(ui.item.ids);
                $label.val(ui.item.value);
                if (!$.trim($desc.val())) {
                    var v = ui.item.value, dash = v.indexOf('-');
                    $desc.val($.trim(dash >= 0 ? v.substring(dash + 1) : v));
                }
                $tr.removeClass('has-error');
                $tr.find('.liv2-amount').focus();
                return false;
            },
            change: function (e, ui) {
                if (!ui.item) { $search.val(''); $id.val(''); $label.val(''); }
            }
        });
    }

    // Rewrite name="prefix[i][field]" so indexes are contiguous after add/remove.
    function renumber(type) {
        var $body = $('#liv2_' + type + '_body'), prefix = $body.data('prefix');
        $body.find('tr.liv2-row').each(function (i) {
            var $r = $(this), n = function (f) { return prefix + '[' + i + '][' + f + ']'; };
            $r.find('.liv2-head-id').attr('name', n('acc_codes_id'));
            $r.find('.liv2-head-label').attr('name', n('head_label'));
            $r.find('.liv2-desc').attr('name', n('description'));
            $r.find('.liv2-amount').attr('name', n('amount'));
            $r.find('.liv2-source').attr('name', n('source'));
        });
    }

    function sectionTotals(type) {
        var $body = $('#liv2_' + type + '_body'), isTax = type === 'tax', sub = 0, vat = 0, any = false;
        $body.find('tr.liv2-row').each(function () {
            var $r = $(this), a = round3($r.find('.liv2-amount').val()), v = isTax ? round3(a * VAT) : 0;
            if (a !== 0) any = true;
            sub += a; vat += v;
            $r.find('.liv2-vat').text(fmt(v));
            $r.find('.liv2-total').text(fmt(a + v));
        });
        sub = round3(sub); vat = round3(vat);
        return { sub: sub, vat: vat, total: round3(sub + vat), rows: $body.find('tr.liv2-row').length, any: any };
    }

    function recalc() {
        var t = sectionTotals('tax'), o = sectionTotals('od');
        $('#liv2_tax_sub').text(fmt(t.sub)); $('#liv2_tax_vat').text(fmt(t.vat)); $('#liv2_tax_total').text(fmt(t.total));
        $('#liv2_od_sub').text(fmt(o.sub)); $('#liv2_od_total').text(fmt(o.total));
        $('#liv2_sum_tax_total').text(fmt(t.total)); $('#liv2_sum_tax_vat').text(fmt(t.vat));
        $('#liv2_sum_od_total').text(fmt(o.total));
        $('#liv2_grand').text(fmt(t.total + o.total));
        $('#liv2_tax_empty').toggle(t.rows === 0); $('#liv2_od_empty').toggle(o.rows === 0);

        var note, ok = t.any || o.any;
        if (!$contract.val()) { note = 'Select a contract to load the invoice lines.'; }
        else if (t.any && o.any) { note = 'Will create: Tax Invoice + Other Deductions.'; }
        else if (t.any) { note = 'Will create: Tax Invoice only (Other Deductions is empty).'; }
        else if (o.any) { note = 'Will create: Other Deductions only (Tax Invoice is empty).'; }
        else { note = 'Enter at least one line with an amount to save.'; }
        $('#liv2_summary_note').text(note);
        $('#liv2_save').prop('disabled', !ok);
    }

    /* ---------- events ---------- */
    $building.on('change', function () {
        resetBelow('building');
        if (this.value) { loadVendors(this.value); }
    });
    $vendor.on('change', function () {
        resetBelow('vendor');
        if (this.value) { loadContracts($building.val(), this.value); }
    });
    $contract.on('change', function () { onContractSelected(false); });

    $('#period_month, #period_year').on('change', function () {
        if (!$contract.val()) return;
        if ($('#liv2_lines tr.liv2-row[data-source="manual"]').length &&
            !window.confirm('Changing the period reloads the calculated lines and removes the lines you added. Continue?')) {
            return;
        }
        loadOverview(); loadPreview('tax'); loadPreview('od');
    });

    $('#liv2_tax_add, #liv2_od_add').on('click', function () { addLine($(this).data('type'), { source: 'manual' }); });

    $('#liv2_lines').on('click', '.liv2-remove', function () {
        var $body = $(this).closest('tbody');
        $(this).closest('tr').remove();
        renumber($body.attr('id') === 'liv2_tax_body' ? 'tax' : 'od');
        recalc();
    });
    $('#liv2_lines').on('input change', '.liv2-amount', recalc);

    $('#liv2_form').on('submit', function (e) {
        var $missing = $('#liv2_lines tr.liv2-row[data-source="manual"]').filter(function () {
            return !$(this).find('.liv2-head-id').val();
        });
        if ($missing.length) {
            e.preventDefault();
            $missing.addClass('has-error');
            $missing.first().find('.liv2-head-search').focus();
            window.alert('Select a head for each manually added line.');
            return false;
        }
        if ($('#liv2_save').prop('disabled')) { e.preventDefault(); return false; }
        $('#liv2_save').prop('disabled', true).text('SAVING…');
    });

    /* ---------- restore after a validation error ---------- */
    function restoreLines(type, rows) {
        var $body = $('#liv2_' + type + '_body');
        $body.empty();
        $.each(rows || {}, function (i, r) { addLine(type, r); });
    }
    if (oldForm.building_id) {
        // Reselect building → landlord → contract (skipping the calculated preview),
        // then rebuild the rows exactly as they were posted.
        $building.val(String(oldForm.building_id));
        loadVendors(oldForm.building_id, { vendor: oldForm.vendor_id, contract: oldForm.landlord_contract_id }, function () {
            restoreLines('tax', oldForm.tax_lines);
            restoreLines('od', oldForm.od_lines);
            recalc();
        });
        var $err = $('#liv2_line_errors'); if ($err.length) { $err.focus(); }
    } else {
        recalc();
    }
});
</script>
@endsection
