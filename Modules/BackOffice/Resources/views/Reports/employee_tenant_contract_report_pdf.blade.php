<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report on flats Rented - Employee wise</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15px;
        }
        body {
            font-family: sans-serif;
            font-size: 9px;
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
            height: 50px;
        }
        .report-title {
            font-size: 16px;
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
        .sub-header {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 8px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }
        .data-table th {
            background-color: #1D80CC;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            vertical-align: middle;
            padding: 5px 3px;
            border: 0.5px solid #000000;
        }
        .data-table td {
            font-size: 8px;
            vertical-align: middle;
            padding: 3px 4px;
            border: 0.5px solid #000000;
        }
        .data-table td.center {
            text-align: center;
        }
        .data-table td.right {
            text-align: right;
        }
        .summary-row td {
            background-color: #E8E8E8;
            font-weight: bold;
            font-size: 9px;
        }
        .employee-header {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
            padding: 6px;
            margin-top: 10px;
            margin-bottom: 2px;
            border: 0.5px solid #ccc;
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
                <div class="report-title">Report on flats Rented - Employee wise</div>
            </td>
            <td style="width:25%;" class="meta-info">
                Report date : {{ date('F d, Y H:i') }}<br>
                User ID : {{ $user }}
            </td>
        </tr>
    </table>

    <div class="sub-header">
        Period : {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @if($employeeName)
            <br>Employee : {{ $employeeName }}
        @endif
    </div>

    @foreach($groupedContracts as $empName => $contracts)
        @if(!$employeeName)
            <div class="employee-header">{{ $empName }}</div>
        @endif

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:3%;">Sl</th>
                    <th style="width:14%;">Building</th>
                    <th style="width:5%;">Bldg No</th>
                    <th style="width:5%;">Unit No</th>
                    <th style="width:13%;">Tenant Name</th>
                    <th style="width:8%;">Start Date</th>
                    <th style="width:8%;">End Date</th>
                    <th style="width:8%;">Contract Period</th>
                    <th style="width:8%;">Rent P/M</th>
                    <th style="width:7%;">Deposit</th>
                    <th style="width:5%;">PDC</th>
                    <th style="width:10%;">Management Type</th>
                </tr>
            </thead>
            <tbody>
                @php $slNo = 1; $totalRent = 0; @endphp
                @foreach($contracts as $row)
                    @php
                        $totalRent += $row->tenant_contract_rent;
                    @endphp
                    <tr>
                        <td class="center">{{ $slNo++ }}</td>
                        <td>{{ $row->building_name }}</td>
                        <td class="center">{{ $row->building_no }}</td>
                        <td class="center">{{ $row->unit_no }}</td>
                        <td>{{ $row->tenant_name }}</td>
                        <td class="center">{{ \Carbon\Carbon::parse($row->startdate)->format('d/m/Y') }}</td>
                        <td class="center">{{ \Carbon\Carbon::parse($row->enddate)->format('d/m/Y') }}</td>
                        <td class="center">{{ $row->contract_period_months }} Months</td>
                        <td class="right">{{ number_format($row->tenant_contract_rent, 3) }}</td>
                        <td class="right">{{ $row->deposit_amt ? number_format($row->deposit_amt, 0) : '-' }}</td>
                        <td class="center">{{ $row->nofpdc ?? 0 }}</td>
                        <td>{{ $row->management_type }}</td>
                    </tr>
                @endforeach
                <tr class="summary-row">
                    <td colspan="8" class="right">Total ({{ $contracts->count() }} units)</td>
                    <td class="right">{{ number_format($totalRent, 3) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <div class="page-number"></div>
</body>
</html>
