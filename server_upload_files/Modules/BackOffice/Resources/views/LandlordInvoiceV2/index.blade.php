@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
    /* Same tokens as the create screen: trust blue on neutral, tabular numerals. */
    .liv2-list { --liv2-blue: #2563eb; --liv2-blue-soft: #eff6ff; --liv2-ink: #1f2937; --liv2-muted: #6b7280; --liv2-line: #e5e7eb; --liv2-bg: #f9fafb; }
    .liv2-list .nav-tabs .nav-item a { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; color: var(--liv2-ink); transition: background-color .15s, color .15s; }
    /* custom.css paints .dashboardtab tab links white at the same specificity as the
       global a.active rule, so the active tab lost its colour; these win on specificity. */
    .liv2-list .nav-tabs .nav-item a:hover, .liv2-list .nav-tabs .nav-item a:focus { background-color: #fff3e0; color: #e65100; }
    .liv2-list .nav-tabs .nav-item a.active,
    .liv2-list .nav-tabs .nav-item a.active:hover,
    .liv2-list .nav-tabs .nav-item a.active:focus { background-color: #FF9800; color: #fff; cursor: default; }
    .liv2-list .nav-tabs .nav-item a.active .liv2-tab-count { background: #fff; color: #e65100; }
    .liv2-list .liv2-tab-count {
        display: inline-block; min-width: 22px; padding: 1px 7px; border-radius: 999px; font-size: 11px; font-weight: 700;
        background: var(--liv2-line); color: var(--liv2-muted); line-height: 18px; text-align: center;
    }
    .liv2-list .liv2-tab-hint { color: var(--liv2-muted); font-size: 13px; margin: 6px 0 12px; }
    .liv2-list table.product-overview thead th { white-space: nowrap; }
    .liv2-list table.product-overview td.num, .liv2-list table.product-overview th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .liv2-list table.product-overview tbody tr:hover td { background: var(--liv2-blue-soft); }
    .liv2-list .search_fields { min-height: 34px; }
    .liv2-list .liv2-status {
        display: inline-block; min-width: 72px; padding: 5px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;
        letter-spacing: .03em; text-transform: uppercase; text-align: center; line-height: 1.2;
    }
    .liv2-list .liv2-status-active  { background: #dcfce7; color: #166534; }
    .liv2-list .liv2-status-posted  { background: #dbeafe; color: #1e40af; }
    .liv2-list .liv2-status-posting { background: #fef3c7; color: #92400e; }
    .liv2-list .liv2-status-voided  { background: #fee2e2; color: #991b1b; }
    .liv2-list .liv2-actions { white-space: nowrap; }
    .liv2-list .liv2-actions .btn, .liv2-list .liv2-actions form { display: inline-block; vertical-align: middle; margin: 0 2px 0 0; }
    .liv2-list .liv2-actions .btn-xs { min-width: 30px; min-height: 30px; line-height: 20px; }
    .liv2-list .liv2-btn-ax { background: #0288d1; color: #fff; }
    .liv2-list .liv2-empty { padding: 28px 12px; text-align: center; color: var(--liv2-muted); }
    .liv2-list .liv2-empty .btn { margin-top: 10px; }
</style>
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class="pull-left">
            <div class="page-title">Landlord Invoice v2</div>
        </div>
        {{ Breadcrumbs::render('landlord-invoice-v2.index') }}
    </div>
</div>

@php
    $tabs = [
        'tax' => ['label' => 'Tax Invoice',      'type' => 'tax_invoice',      'hint' => 'Management fees, cleaning and repair &amp; maintenance charges billed to the landlord, with 5% VAT.'],
        'od'  => ['label' => 'Other Deductions', 'type' => 'other_deductions', 'hint' => 'Municipal tax, utilities, AMC and sub-contractor charges deducted from the landlord, no VAT.'],
    ];
@endphp

<div class="col-md-12 col-sm-12 dashboardtab liv2-list">
    <div class="panel tab-border card-box">
        <header class="panel-heading panel-heading-gray custom-tab">
            <ul class="nav nav-tabs" role="tablist">
                @foreach($tabs as $key => $t)
                <li class="nav-item">
                    <a href="{{ route('landlord-invoice-v2.index', ['tab' => $key]) }}" class="{{ $tab === $key ? 'active' : '' }}" role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}">
                        {{ $t['label'] }} <span class="liv2-tab-count">{{ isset($tabCounts[$t['type']]) ? $tabCounts[$t['type']] : 0 }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </header>
        <div class="panel-body">
            <div class="tab-content">
                <h4>
                    <div id="pagination_info">
                        @include('includes.pagination_info', ['paginator' => $landlordInvoicesV2])
                    </div>
                    <a href="{{ route('landlord-invoice-v2.create') }}" class="btn btn-circle btn-primary align-right">Add</a>
                    <div class="clr"></div>
                </h4>
                <p class="liv2-tab-hint">{!! $tabs[$tab]['hint'] !!}</p>

                <div class="tab-pane active" id="liv2_tab_{{ $tab }}">
                    <div class="clearfix"></div>
                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="table display product-overview mb-30" id="liv2_list_table">
                                <thead>
                                    <tr>
                                        <th title="Serial no">Sl No.</th>
                                        <th title="Invoice No">@sortablelink('invoice_no', 'Invoice No.', [], ['class' => 'sort_url'])</th>
                                        <th title="Invoice Date">@sortablelink('invoice_date', 'Inv. Date', [], ['class' => 'sort_url'])</th>
                                        <th title="Invoice period">@sortablelink('period_year', 'Period', [], ['class' => 'sort_url'])</th>
                                        <th title="Landlord">@sortablelink('vendor_name', 'Landlord', [], ['class' => 'sort_url'])</th>
                                        <th title="Building">@sortablelink('building_name', 'Building', [], ['class' => 'sort_url'])</th>
                                        <th class="num" title="Subtotal">@sortablelink('subtotal', 'Subtotal', [], ['class' => 'sort_url'])</th>
                                        @if($tab === 'tax')
                                        <th class="num" title="VAT 5%">@sortablelink('vat_total', 'VAT', [], ['class' => 'sort_url'])</th>
                                        @endif
                                        <th class="num" title="Grand total">@sortablelink('grand_total', 'Total', [], ['class' => 'sort_url'])</th>
                                        <th title="Status">@sortablelink('status', 'Status', [], ['class' => 'sort_url'])</th>
                                        <th width="12%" title="Action">Action</th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td><input autocomplete="off" type="text" name="invoice_no" class="search_fields mob" id="liv2_invoice_no" value="{{ request('invoice_no') }}" aria-label="Filter by invoice no"></td>
                                        <td><input autocomplete="off" type="date" name="invoice_date" class="search_fields" id="liv2_invoice_date" value="{{ request('invoice_date') }}" style="width:120px" aria-label="Filter by invoice date"></td>
                                        <td><input autocomplete="off" type="month" name="period" class="search_fields" id="liv2_period" value="{{ request('period') }}" style="width:130px" aria-label="Filter by period"></td>
                                        <td><input autocomplete="off" type="text" name="vendor_name" class="search_fields mob" id="liv2_vendor_name" value="{{ request('vendor_name') }}" aria-label="Filter by landlord"></td>
                                        <td><input autocomplete="off" type="text" name="building_name" class="search_fields mob" id="liv2_building_name" value="{{ request('building_name') }}" aria-label="Filter by building"></td>
                                        <td></td>
                                        @if($tab === 'tax')<td></td>@endif
                                        <td></td>
                                        <td>
                                            <select name="status" id="liv2_status" class="search_fields" style="width:90px" aria-label="Filter by status">
                                                <option value="">Select</option>
                                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                                                <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                                            </select>
                                        </td>
                                        <td></td>
                                    </tr>
                                </thead>
                                <tbody id="liv2-search">
                                    @include('backoffice::LandlordInvoiceV2.index_ajax')
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="pagination">
                        <div class="text-center">
                            {{ $landlordInvoicesV2->appends(request()->except(['page']))->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    var quickUrl = '{{ $route }}';
    var tab = '{{ $tab }}';
    var colspan = {{ $tab === 'tax' ? 11 : 10 }};
    var timer = null;

    function currentFilters() {
        return {
            tab: tab,
            invoice_no:    $('#liv2_invoice_no').val(),
            invoice_date:  $('#liv2_invoice_date').val(),
            period:        $('#liv2_period').val(),
            vendor_name:   $('#liv2_vendor_name').val(),
            building_name: $('#liv2_building_name').val(),
            status:        $('#liv2_status').val()
        };
    }

    function load() {
        var filters = currentFilters();
        $.ajax({
            method: 'GET',
            url: quickUrl,
            data: $.extend({ ajax: true }, filters),
            beforeSend: function () {
                $('#liv2-search').html('<tr><td colspan="' + colspan + '" align="center"><img src="{{ url("/") }}/public/img/pre-loader.gif" width="75" height="75" alt="Loading"></td></tr>');
            },
            success: function (data) {
                var temp = $(data);
                var paginateInfo = temp.find('.pagination_info').clone();
                temp.find('.pagination_info').remove();
                var paginate = temp.find('#pagination_ajax').clone();
                temp.find('#pagination_ajax').remove();

                $('#liv2-search').html(temp);
                $('#pagination').html(paginate);
                $('#pagination_info').html(paginateInfo);

                var href_txt = $.param(filters);
                $('.sort_url').each(function (i, n) {
                    var href = $(n).attr('href');
                    var hashes = href.slice(href.indexOf('sort'));
                    href = href.split('?')[0];
                    $(n).attr('href', href + '?' + href_txt + '&' + hashes);
                });
            }
        });
    }

    // Selects/dates reload at once; text fields settle for 350ms so one keystroke is not one request.
    $(document).on('change', '#liv2_invoice_date, #liv2_period, #liv2_status', load);
    $(document).on('keyup paste', '#liv2_invoice_no, #liv2_vendor_name, #liv2_building_name', function () {
        clearTimeout(timer);
        timer = setTimeout(load, 350);
    });
});
</script>
@endsection
