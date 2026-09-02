<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tenancy Details by Building Wise</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15px;
        }
        body {
            font-family: sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            margin-bottom: 8px;
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
        .filter-info {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 8px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .data-table th {
            background-color: #1D80CC;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            vertical-align: middle;
            padding: 5px 2px;
            border: 0.5px solid #000000;
        }
        .data-table td {
            font-size: 7.5px;
            vertical-align: middle;
            padding: 3px 2px;
            border: 0.5px solid #000000;
        }
        .data-table td.center {
            text-align: center;
        }
        .data-table td.right {
            text-align: right;
        }
        .vacant-row td {
            background-color: #FF8987;
        }
        .pending-row td {
            background-color: #FFD966;
        }
        .summary-row td {
            background-color: #E8E8E8;
            font-weight: bold;
            font-size: 8px;
        }
        .page-number {
            text-align: center;
            font-size: 8px;
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
                <div class="report-title">Tenancy Details by Building Wise</div>
            </td>
            <td style="width:25%;" class="meta-info">
                Report date : {{ date('F d, Y H:i') }}<br>
                User ID : {{ $user }}
            </td>
        </tr>
    </table>

    <div class="filter-info">{{ $filterLabel }} : {{ $filterValue }}</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:3%;">S#</th>
                <th style="width:6%;">Building Code</th>
                <th style="width:9%;">Building Name</th>
                <th style="width:5%;">Unit No</th>
                <th style="width:6%;">Unit Type</th>
                <th style="width:5%;">Vacant Y/N</th>
                <th style="width:7%;">Terminated Date</th>
                <th style="width:9%;">Tenant Name</th>
                <th style="width:7%;">Contact No</th>
                <th style="width:7%;">Contract From</th>
                <th style="width:7%;">Contract To</th>
                <th style="width:7%;">Rent (Per Month)</th>
                <th style="width:6%;">Payment Mode</th>
                <th style="width:8%;">Rent Received Till</th>
                <th style="width:8%;">Agreement Type</th>
            </tr>
        </thead>
        <tbody>
            @php $sn = 0; @endphp
            @foreach($rows as $row)
                @php $sn++; @endphp
                <tr @if($row->unit_vaccant_status == 0) class="vacant-row" @elseif(($row->row_flag ?? null) == 'pending') class="pending-row" @endif>
                    <td class="center">{{ $sn }}</td>
                    <td>{{ $row->building_code }}</td>
                    <td>{{ $row->building_name }}</td>
                    <td class="center">{{ $row->unit_no }}</td>
                    <td>{{ $row->unit_types_name }}</td>
                    <td class="center">{{ $row->vaccant_status }}</td>
                    <td class="center">{{ $row->termination_date ? date('d/m/Y', strtotime($row->termination_date)) : '' }}</td>
                    <td>{{ $row->tenant_name }}</td>
                    <td class="center">{{ $row->unit_vaccant_status != 0 ? $row->tenant_contact_no : '' }}</td>
                    <td class="center">{{ $row->unit_vaccant_status != 0 && $row->tenant_contract_start_date ? date('d/m/Y', strtotime($row->tenant_contract_start_date)) : '' }}</td>
                    <td class="center">{{ $row->unit_vaccant_status != 0 && $row->tenant_contract_valid_to_date ? date('d/m/Y', strtotime($row->tenant_contract_valid_to_date)) : '' }}</td>
                    <td class="right">{{ $row->unit_vaccant_status != 0 ? number_format((float)$row->tenant_contract_rent, 2) : '' }}</td>
                    <td class="center">{{ $row->unit_vaccant_status != 0 ? $row->payment_method_code : '' }}</td>
                    <td class="center">{{ $row->unit_vaccant_status != 0 && $row->tenant_contract_last_paid_date ? date('d/m/Y', strtotime($row->tenant_contract_last_paid_date)) : '' }}</td>
                    <td class="center">{{ $row->unit_usage }}</td>
                </tr>
            @endforeach
            <tr class="summary-row">
                <td colspan="4" style="text-align:right; padding-right:5px;">Summary :</td>
                <td class="center">Total Units: {{ $totalUnits }}</td>
                <td class="center" colspan="2">Occupied: {{ $occupiedCount }}</td>
                <td class="center" colspan="2">Vacant: {{ $vacantCount }}</td>
                <td colspan="3"></td>
                <td class="right" colspan="2">Total Rent: {{ number_format($totalRent, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table style="border:none; margin-top:6px;">
        <tr>
            <td style="width:12px; height:10px; background-color:#FF8987; border:0.5px solid #000000;"></td>
            <td style="border:none; font-size:8px; padding-left:4px; padding-right:20px;">Vacant unit</td>
            <td style="width:12px; height:10px; background-color:#FFD966; border:0.5px solid #000000;"></td>
            <td style="border:none; font-size:8px; padding-left:4px;">Occupied, contract pending / not yet active (e.g. renewal in progress)</td>
        </tr>
    </table>

    <div class="page-number" style="margin-top:10px;"></div>
</body>
</html>
