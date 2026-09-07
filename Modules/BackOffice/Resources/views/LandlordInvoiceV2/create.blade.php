@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
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
<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice Type<small class="textRed">*</small></label>
                <select class="form-control" id="invoice_type" name="invoice_type" required>
                    <option value="">Select Invoice Type</option>
                    <option value="tax_invoice">Tax Invoice</option>
                    <option value="other_deductions">Other Deductions</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice No.</label>
                <input type="text" class="form-control" value="(auto-generated on save)" disabled>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice Date<small class="textRed">*</small></label>
                <input type="date" class="form-control" id="invoice_date" name="invoice_date" required value="{{ old('invoice_date', date('Y-m-d')) }}">
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Vendor<small class="textRed">*</small></label>
                <select class="form-control" id="vendor_id" name="vendor_id" required>
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $v)
                    <option value="{{ $v->id }}">{{ $v->vendor_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Contract<small class="textRed">*</small></label>
                <select class="form-control" id="landlord_contract_id" name="landlord_contract_id" required disabled>
                    <option value="">Select Vendor First</option>
                </select>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Month<small class="textRed">*</small></label>
                <select class="form-control" id="period_month" name="period_month" required>
                    <option value="">Month</option>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Year<small class="textRed">*</small></label>
                <input type="number" class="form-control" id="period_year" name="period_year" required value="{{ date('Y') }}">
            </div>
        </div>
    </div>
</div>

<h3>Vendor / Building Details</h3>
<div class="dataSearchBox">
    <div class="row">
        <div class="col-lg-6 p-t-20"><h5 class="details"><b>Vendor Name: </b><span id="disp_vendor_name">-</span></h5></div>
        <div class="col-lg-6 p-t-20"><h5 class="details"><b>Building Name: </b><span id="disp_building_name">-</span></h5></div>
        <div class="col-lg-6 p-t-20"><h5 class="details"><b>Vendor Address: </b><span id="disp_vendor_address">-</span></h5></div>
        <div class="col-lg-6 p-t-20"><h5 class="details"><b>VATIN No: </b><span id="disp_vatin_no">-</span></h5></div>
    </div>
</div>

<h3>Invoice Lines</h3>
<div class="dataSearchBox">
    <div class="row">
        <table class="table" id="liv2_lines_table">
            <thead>
                <tr><th>Description</th><th>Amount (OMR)</th></tr>
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
    var contractsUrl = '{{ route("landlordInvoiceV2ContractsByVendor") }}';
    var detailsUrlBase = '{{ url("landlord-invoice-v2-contract-details") }}';
    var previewUrl = '{{ route("landlordInvoiceV2CalculationPreview") }}';

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
            var rows = '';
            $.each(res.lines, function (i, line) {
                rows += '<tr>' +
                    '<td><input type="hidden" name="lines[' + i + '][description]" value="' + line.description + '">' + line.description + '</td>' +
                    '<td><input type="number" step="0.001" class="form-control" name="lines[' + i + '][amount]" value="' + line.amount + '"></td>' +
                    '</tr>';
            });
            $('#liv2_lines_body').html(rows);
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
            var opts = '<option value="">Select Contract</option>';
            $.each(contracts, function (i, c) {
                opts += '<option value="' + c.id + '">' + c.label + '</option>';
            });
            $contract.html(opts).prop('disabled', false);
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
    });

    $('#invoice_type, #period_month, #period_year').on('change', maybeLoadPreview);
});
</script>
@endsection
