<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tenancy Detailed Report</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20px;
        }
        body {
            font-family: sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            margin-bottom: 10px;
        }
        .header td {
            vertical-align: top;
            border: none;
        }
        .logo {
            height: 55px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            padding-top: 5px;
        }
        .meta-info {
            text-align: right;
            font-size: 8px;
            color: #1D80CC;
            font-weight: bold;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1D80CC;
            margin-top: 15px;
            margin-bottom: 8px;
            border-bottom: 1px solid #1D80CC;
            padding-bottom: 3px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .info-table td {
            padding: 3px 5px;
            font-size: 9px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            color: #333;
            width: 20%;
        }
        .info-table .value {
            width: 30%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .data-table th {
            background-color: #1D80CC;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 9px;
            text-align: center;
            vertical-align: middle;
            padding: 5px 3px;
            border: 0.5px solid #000000;
        }
        .data-table td {
            font-size: 9px;
            vertical-align: middle;
            padding: 4px 4px;
            border: 0.5px solid #000000;
        }
        .data-table td.center {
            text-align: center;
        }
        .data-table td.right {
            text-align: right;
        }
        .row-even td {
            background-color: #F2F2F2;
        }
        .page-number {
            text-align: center;
            font-size: 9px;
        }
        .page-number:after {
            content: "Page " counter(page);
        }
    </style>
</head>
<body>
    <table class="header" style="border:none;">
        <tr>
            <td style="width:25%;">
                <img src="{{ $logo }}" class="logo" alt="Logo">
            </td>
            <td style="width:50%;">
                <div class="report-title">Tenancy Detailed Report</div>
            </td>
            <td style="width:25%;" class="meta-info">
                Report date : {{ date('F d, Y H:i') }}<br>
                User ID : {{ $user }}
            </td>
        </tr>
    </table>

    @if($tenant)
    {{-- Tenant Information Section --}}
    <div class="section-title">Tenant Information</div>
    <table class="info-table">
        <tr>
            <td class="label">Tenant Type</td>
            <td class="value">{{ $tenant->tenant_types_name }}</td>
            <td class="label">Tenant Name</td>
            <td class="value">{{ $tenant->tenant_name }}</td>
        </tr>
        <tr>
            <td class="label">Resident ID</td>
            <td class="value">{{ $tenant->resident_id }}</td>
            <td class="label">Passport No</td>
            <td class="value">{{ $tenant->passport_no }}</td>
        </tr>
        <tr>
            <td class="label">Nationality</td>
            <td class="value">{{ $tenant->nationality }}</td>
            <td class="label">Gender</td>
            <td class="value">{{ $tenant->tenant_gender }}</td>
        </tr>
        <tr>
            <td class="label">Employer Name</td>
            <td class="value">{{ $tenant->tenant_employer_name }}</td>
            <td class="label">Designation</td>
            <td class="value">{{ $tenant->designation }}</td>
        </tr>
        <tr>
            <td class="label">Office Location</td>
            <td class="value" colspan="3">{{ $tenant->locations_name }}</td>
        </tr>
    </table>

    {{-- Contact Details Section --}}
    <div class="section-title">Contact Details</div>
    <table class="info-table">
        <tr>
            <td class="label">Address</td>
            <td class="value" colspan="3">{{ $tenant->tenant_contact_address }}</td>
        </tr>
        <tr>
            <td class="label">Secondary Address</td>
            <td class="value" colspan="3">{{ $tenant->tenant_secondary_address }}</td>
        </tr>
        <tr>
            <td class="label">Postal Box</td>
            <td class="value">{{ $tenant->tenant_post_box }}</td>
            <td class="label">Postal Code</td>
            <td class="value">{{ $tenant->tenant_pc }}</td>
        </tr>
        <tr>
            <td class="label">Mobile</td>
            <td class="value">{{ $tenant->gsm_no }}</td>
            <td class="label">Office No</td>
            <td class="value">{{ $tenant->tenant_contact_no }}</td>
        </tr>
        <tr>
            <td class="label">Residence Tel</td>
            <td class="value">{{ $tenant->tenant_residence_tel }}</td>
            <td class="label">Email</td>
            <td class="value">{{ $tenant->tenant_personal_email }}</td>
        </tr>
        <tr>
            <td class="label">ICE Contact</td>
            <td class="value" colspan="3">{{ $tenant->tenant_ice_contact_no }}</td>
        </tr>
    </table>

    {{-- Bank Details Section --}}
    <div class="section-title">Bank Details</div>
    <table class="info-table">
        <tr>
            <td class="label">Bank Name</td>
            <td class="value">{{ $tenant->bank_name }}</td>
            <td class="label">Account No</td>
            <td class="value">{{ $tenant->tenant_acc_no }}</td>
        </tr>
    </table>
    @else
    <p>No tenant information found for the selected name.</p>
    @endif

    {{-- Contracts Table Section --}}
    <div class="section-title">Contracts</div>
    @if(count($contracts) > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%;">S#</th>
                <th style="width:14%;">Contract No</th>
                <th style="width:14%;">Municipality Agr No</th>
                <th style="width:14%;">Agreement Amount</th>
                <th style="width:16%;">Building</th>
                <th style="width:10%;">Unit</th>
                <th style="width:14%;">Rent</th>
                <th style="width:13%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contracts as $index => $contract)
            <tr @if($index % 2 == 1) class="row-even" @endif>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $contract->tenant_contract_no }}</td>
                <td>{{ $contract->tenant_contract_muncipality_agr_no }}</td>
                <td class="right">{{ number_format((float)$contract->tenant_contract_value, 2) }}</td>
                <td>{{ $contract->building_name }}</td>
                <td class="center">{{ $contract->unit_no }}</td>
                <td class="right">{{ number_format((float)$contract->tenant_contract_rent, 2) }}</td>
                <td class="center">{{ $contract->contract_status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No contracts found for this tenant.</p>
    @endif

    <div class="page-number" style="margin-top:10px;"></div>
</body>
</html>
