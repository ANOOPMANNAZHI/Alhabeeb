@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<style>
    .liv2-section-head {
        font-size: 15px;
        font-weight: 700;
        color: #222;
        border-left: 4px solid #18c98e;
        padding: 6px 12px;
        margin: 20px 0 10px;
        background: #f7fdfb;
        border-radius: 4px;
    }
    .liv2-section-head:first-child { margin-top: 0; }
    .liv2-stat-tile {
        border: 1px solid #ebebeb;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 15px;
        background: #fff;
    }
    .liv2-stat-label {
        display: block;
        font-size: 11px;
        color: #8c8c8c;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 4px;
    }
    .liv2-stat-value {
        display: block;
        font-size: 19px;
        font-weight: 700;
        color: #222;
    }
    .liv2-stat-tile.liv2-stat-accent .liv2-stat-value { color: #18c98e; }
    .liv2-tabs.nav-tabs { border-bottom: 2px solid #ebebeb; margin-bottom: 15px; }
    .liv2-tabs.nav-tabs > li > a {
        border: none;
        color: #8c8c8c;
        font-weight: 600;
        padding: 10px 18px;
    }
    .liv2-tabs.nav-tabs > li > a.active,
    .liv2-tabs.nav-tabs > li > a:hover {
        color: #18c98e;
        border: none;
        border-bottom: 2px solid #18c98e;
        background: transparent;
    }
    #liv2_lines_table thead th {
        background: #f7fdfb;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #666;
    }
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

<form action="{{ route('landlord-invoice-v2.store') }}" method="POST" id="liv2_form" class="form-horizontal" data-toggle="validator">
{{ csrf_field() }}
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">

<div class="liv2-section-head">Invoice Details</div>
<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label>Invoice Type<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                    <select class="form-control" id="invoice_type" name="invoice_type" required>
                        <option value="">Select Invoice Type</option>
                        <option value="tax_invoice">Tax Invoice</option>
                        <option value="other_deductions">Other Deductions</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Invoice No.</label>
                <div class="p-relative">
                    <i class="fa fa-hashtag icn-add" aria-hidden="true"></i>
                    <input type="text" class="form-control" value="(auto-generated on save)" disabled>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Invoice Date<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="date" class="form-control" id="invoice_date" name="invoice_date" required value="{{ old('invoice_date', date('Y-m-d')) }}">
                </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Vendor<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="icon icon-landlord" aria-hidden="true"></i>
                    <select class="form-control" id="vendor_id" name="vendor_id" required>
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $v)
                        <option value="{{ $v->id }}">{{ $v->vendor_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Contract<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="icon icon-building" aria-hidden="true"></i>
                    <select class="form-control" id="landlord_contract_id" name="landlord_contract_id" required disabled>
                        <option value="">Select Vendor First</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Month<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar icn-add" aria-hidden="true"></i>
                    <select class="form-control" id="period_month" name="period_month" required>
                        <option value="">Month</option>
                        @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Year<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="period_year" name="period_year" required value="{{ date('Y') }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="dataSearchBox">
    <ul class="nav nav-tabs liv2-tabs">
        <li class="nav-item"><a href="#liv2_tab_details" data-toggle="tab" class="active">Contract &amp; Building Details</a></li>
        <li class="nav-item"><a href="#liv2_tab_overview" data-toggle="tab">Overview</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="liv2_tab_details">
            <div class="row">
                <div class="col-lg-6 p-t-20"><h5 class="details"><b>Vendor Name: </b><span id="disp_vendor_name">-</span></h5></div>
                <div class="col-lg-6 p-t-20"><h5 class="details"><b>Building Name: </b><span id="disp_building_name">-</span></h5></div>
                <div class="col-lg-6 p-t-20"><h5 class="details"><b>Vendor Address: </b><span id="disp_vendor_address">-</span></h5></div>
                <div class="col-lg-6 p-t-20"><h5 class="details"><b>VATIN No: </b><span id="disp_vatin_no">-</span></h5></div>
            </div>
        </div>
        <div class="tab-pane" id="liv2_tab_overview">
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile liv2-stat-accent">
                        <span class="liv2-stat-label">Income as per Rental Agreement</span>
                        <span class="liv2-stat-value" id="disp_ov_income">-</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile liv2-stat-accent">
                        <span class="liv2-stat-label">Selected Month Collection</span>
                        <span class="liv2-stat-value" id="disp_ov_collection">-</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile">
                        <span class="liv2-stat-label">Total Expenses for the Month</span>
                        <span class="liv2-stat-value" id="disp_ov_expenses">-</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile liv2-stat-accent">
                        <span class="liv2-stat-label">Amount Transfer to Landlord</span>
                        <span class="liv2-stat-value" id="disp_ov_transfer">-</span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile liv2-stat-accent">
                        <span class="liv2-stat-label">Total Number of Units</span>
                        <span class="liv2-stat-value" id="disp_ov_total_units">-</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile liv2-stat-accent">
                        <span class="liv2-stat-label">Total Building Occupancy Level</span>
                        <span class="liv2-stat-value" id="disp_ov_occupancy_level">-</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile">
                        <span class="liv2-stat-label">New Leased Residential Units</span>
                        <span class="liv2-stat-value" id="disp_ov_new_leased_res">-</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile">
                        <span class="liv2-stat-label">New Leased Commercial Units</span>
                        <span class="liv2-stat-value" id="disp_ov_new_leased_com">-</span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile">
                        <span class="liv2-stat-label">Total Occupied Residential Units</span>
                        <span class="liv2-stat-value" id="disp_ov_occupied_res">-</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile">
                        <span class="liv2-stat-label">Total Occupied Commercial Units</span>
                        <span class="liv2-stat-value" id="disp_ov_occupied_com">-</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile">
                        <span class="liv2-stat-label">Vacant Residential Units</span>
                        <span class="liv2-stat-value" id="disp_ov_vacant_res">-</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile">
                        <span class="liv2-stat-label">Vacant Commercial Units</span>
                        <span class="liv2-stat-value" id="disp_ov_vacant_com">-</span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile">
                        <span class="liv2-stat-label">Residential Units Under Evacuation</span>
                        <span class="liv2-stat-value" id="disp_ov_evac_res">-</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="liv2-stat-tile">
                        <span class="liv2-stat-label">Commercial Units Under Evacuation</span>
                        <span class="liv2-stat-value" id="disp_ov_evac_com">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="liv2-section-head">Invoice Lines</div>
<div class="dataSearchBox">
    <div class="row">
        <table class="table" id="liv2_lines_table">
            <thead>
                <tr><th>Description</th><th style="width:220px">Amount (OMR)</th></tr>
            </thead>
            <tbody id="liv2_lines_body">
                <tr><td colspan="2">Select vendor, contract, invoice type and month to load calculated lines.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-12">
            <button type="submit" class="btn btn-primary">SAVE</button>
        </div>
    </div>
</div>

</div>
</div>
</div>
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#vendor_id').select2({
        placeholder: 'Select Vendor',
        allowClear: true,
        width: '100%'
    });

    var contractsUrl = '{{ route("landlordInvoiceV2ContractsByVendor") }}';
    var detailsUrlBase = '{{ url("landlord-invoice-v2-contract-details") }}';
    var previewUrl = '{{ route("landlordInvoiceV2CalculationPreview") }}';
    var overviewUrl = '{{ route("landlordInvoiceV2OverviewPreview") }}';

    function maybeLoadPreview() {
        var contractId = $('#landlord_contract_id').val();
        var invoiceType = $('#invoice_type').val();
        var month = $('#period_month').val();
        var year = $('#period_year').val();
        if (!contractId || !invoiceType || !month || !year) return;

        $.getJSON(previewUrl, {
            landlord_contract_id: contractId,
            invoice_type: invoiceType,
            period_month: month,
            period_year: year
        }, function (res) {
            var $body = $('#liv2_lines_body');
            $body.empty();
            $.each(res.lines, function (i, line) {
                var $hiddenDesc = $('<input>').attr('type', 'hidden').attr('name', 'lines[' + i + '][description]').val(line.description);
                var $descTd = $('<td>').append($hiddenDesc).append(document.createTextNode(line.description));
                var $amountInput = $('<input>').attr('type', 'number').attr('step', '0.001').addClass('form-control').attr('name', 'lines[' + i + '][amount]').val(line.amount);
                var $amountTd = $('<td>').append($amountInput);
                var $row = $('<tr>').append($descTd).append($amountTd);
                $body.append($row);
            });
        });
    }

    function maybeLoadOverview() {
        var contractId = $('#landlord_contract_id').val();
        var month = $('#period_month').val();
        var year = $('#period_year').val();
        if (!contractId || !month || !year) return;

        $.getJSON(overviewUrl, {
            landlord_contract_id: contractId,
            period_month: month,
            period_year: year
        }, function (res) {
            $('#disp_ov_income').text(res.income);
            $('#disp_ov_collection').text(res.collection);
            $('#disp_ov_expenses').text(res.total_expenses);
            $('#disp_ov_transfer').text(res.transfer_to_landlord);
            $('#disp_ov_total_units').text(res.total_units);
            $('#disp_ov_occupancy_level').text(res.occupancy_level + '%');
            $('#disp_ov_new_leased_res').text(res.new_leased_residential);
            $('#disp_ov_new_leased_com').text(res.new_leased_commercial);
            $('#disp_ov_occupied_res').text(res.occupied_residential);
            $('#disp_ov_occupied_com').text(res.occupied_commercial);
            $('#disp_ov_vacant_res').text(res.vacant_residential);
            $('#disp_ov_vacant_com').text(res.vacant_commercial);
            $('#disp_ov_evac_res').text(res.evacuation_residential);
            $('#disp_ov_evac_com').text(res.evacuation_commercial);
        });
    }

    $('#vendor_id').on('change', function () {
        var vendorId = $(this).val();
        var $contract = $('#landlord_contract_id');
        $contract.prop('disabled', true).html('<option value="">Loading...</option>');
        $('#disp_vendor_name, #disp_building_name, #disp_vendor_address, #disp_vatin_no').text('-');

        if (!vendorId) {
            $contract.html('<option value="">Select Vendor First</option>');
            return;
        }

        $.getJSON(contractsUrl, { vendor_id: vendorId }, function (contracts) {
            $contract.empty();
            $contract.append($('<option>').val('').text('Select Contract'));
            $.each(contracts, function (i, c) {
                $contract.append($('<option>').val(c.id).text(c.label));
            });
            $contract.prop('disabled', false);
        });
    });

    $('#landlord_contract_id').on('change', function () {
        var contractId = $(this).val();
        if (!contractId) return;

        $.getJSON(detailsUrlBase + '/' + contractId, function (details) {
            $('#disp_vendor_name').text(details.vendor_name || '-');
            $('#disp_building_name').text(details.building_name || '-');
            $('#disp_vendor_address').text(details.vendor_address || '-');
            $('#disp_vatin_no').text(details.vatin_no || '-');
        });

        maybeLoadPreview();
        maybeLoadOverview();
    });

    $('#invoice_type, #period_month, #period_year').on('change', maybeLoadPreview);
    $('#period_month, #period_year').on('change', maybeLoadOverview);
});
</script>
@endsection
