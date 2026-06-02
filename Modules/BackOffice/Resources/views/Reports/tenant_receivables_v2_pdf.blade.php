<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tenant Receivable v2</title>
    <style>
        @page {
            size: A3 landscape;
            margin: 15px;
        }
        body {
            font-family: sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            margin-bottom: 8px;
            border: none;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .logo { height: 50px; }
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
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .data-table th {
            background-color: #045DC2;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 7.5px;
            text-align: center;
            vertical-align: middle;
            padding: 5px 2px;
            border: 0.5px solid #000;
        }
        .data-table td {
            font-size: 7px;
            vertical-align: middle;
            padding: 2px 3px;
            border: 0.5px solid #ccc;
        }
        .data-table td.center { text-align: center; }
        .data-table td.right  { text-align: right; }
        .building-header td {
            background-color: #A0C9F2;
            font-weight: bold;
            font-size: 8px;
            padding: 4px 5px;
            border: 0.5px solid #ccc;
        }
        .building-total td {
            background-color: #E8F4FC;
            font-weight: bold;
            font-size: 7.5px;
            padding: 3px 3px;
            border: 0.5px solid #ccc;
        }
        .grand-total td {
            background-color: #C5DCED;
            font-weight: bold;
            font-size: 8px;
            padding: 4px 3px;
            border: 0.5px solid #999;
        }
        .even-row { background-color: #DCEBF5; }
    </style>
</head>
<body>

<table class="header-table">
    <tr>
        <td style="width:20%;">
            <img src="{{ $logo }}" class="logo" alt="Logo">
        </td>
        <td style="width:60%;">
            <div class="report-title">Rent Receivable as on Date</div>
            <div style="text-align:center; font-size:14px; font-weight:bold;">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
        </td>
        <td style="width:20%;" class="meta-info">
            Report Date : {{ date('F d, Y') }}<br>
            Time : {{ date('H:i') }}<br>
            User ID : {{ $user }}
        </td>
    </tr>
</table>

@if($filters['building_name'] || $filters['building_no'] || $filters['tenant_name'] || $filters['management_type'] || $filters['are'])
<div class="filter-info">
    @if($filters['building_name']) Building Name : {{ $filters['building_name'] }}&nbsp;&nbsp; @endif
    @if($filters['building_no']) Building No : {{ $filters['building_no'] }}&nbsp;&nbsp; @endif
    @if($filters['tenant_name']) Tenant : {{ $filters['tenant_name'] }}&nbsp;&nbsp; @endif
    @if($filters['management_type']) Management : {{ $filters['management_type'] }}&nbsp;&nbsp; @endif
    @if($filters['are']) ARE : {{ $filters['are'] }}&nbsp;&nbsp; @endif
</div>
@endif

<table class="data-table">
    <thead>
        <tr>
            <th style="width:3%;">Sl No</th>
            <th style="width:10%;">Tenant Name</th>
            <th style="width:8%;">Agreement No</th>
            <th style="width:5%;">Unit No</th>
            <th style="width:5%;">Rent P.M</th>
            <th style="width:5%;">From</th>
            <th style="width:5%;">To</th>
            <th style="width:6%;">Payment Mode</th>
            <th style="width:5%;">Last Recv Date</th>
            <th style="width:5%;">Total Due</th>
            <th style="width:5%;">Paid Till</th>
            <th style="width:5%;">Amt Received</th>
            <th style="width:5%;">Net Amt Due</th>
            <th style="width:6%;">Contact No</th>
            <th style="width:4%;">PDC</th>
            <th style="width:4%;">Cheque Closed</th>
            <th style="width:7%;">Contact Name</th>
            <th style="width:7%;">Mgmt Type</th>
            <th style="width:6%;">ARE Name</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sl = 0;
            $grandTotalDue = 0;
            $grandAmtReceived = 0;
            $grandNetAmt = 0;
            $currentBuilding = null;
            $bldgTotalDue = 0;
            $bldgAmtReceived = 0;
            $bldgNetAmt = 0;
            $bldgRows = [];

            // Group rows by building
            $grouped = [];
            foreach ($rows as $row) {
                $grouped[$row->buildingname][] = $row;
            }
        @endphp

        @foreach($grouped as $buildingName => $buildingRows)
        <tr class="building-header">
            <td colspan="19">{{ $buildingName }}</td>
        </tr>
        @php
            $bldgTotalDue = 0;
            $bldgAmtReceived = 0;
            $bldgNetAmt = 0;
        @endphp
        @foreach($buildingRows as $i => $row)
        @php
            $sl++;
            $bldgTotalDue    += $row->totaldue ?? 0;
            $bldgAmtReceived += $row->amount_received ?? 0;
            $bldgNetAmt      += $row->netamtdue ?? 0;
            $grandTotalDue    += $row->totaldue ?? 0;
            $grandAmtReceived += $row->amount_received ?? 0;
            $grandNetAmt      += $row->netamtdue ?? 0;
        @endphp
        <tr class="{{ $i % 2 == 1 ? 'even-row' : '' }}">
            <td class="center">{{ $sl }}</td>
            <td>{{ $row->tenant_name }}</td>
            <td class="center">{{ $row->contract_no }}</td>
            <td class="center">{{ $row->unit_code }}</td>
            <td class="right">{{ number_format($row->rentper_month, 3) }}</td>
            <td class="center">{{ $row->start_date ? \Carbon\Carbon::parse($row->start_date)->format('d/m/Y') : '' }}</td>
            <td class="center">{{ $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('d/m/Y') : '' }}</td>
            <td class="center">{{ $row->paymentmode }}</td>
            <td class="center">{{ $row->receipt_date ? \Carbon\Carbon::parse($row->receipt_date)->format('d/m/Y') : '' }}</td>
            <td class="right">{{ number_format($row->totaldue, 3) }}</td>
            <td class="center">{{ $row->lastpaid_till ? \Carbon\Carbon::parse($row->lastpaid_till)->format('d/m/Y') : '' }}</td>
            <td class="right">{{ number_format($row->amount_received ?? 0, 3) }}</td>
            <td class="right">{{ number_format($row->netamtdue, 3) }}</td>
            <td class="center">{{ $row->contact_no }}</td>
            <td class="center">{{ $row->pdc }}</td>
            <td class="center">{{ $row->pdc_closed }}</td>
            <td>{{ $row->contact_person }}</td>
            <td class="center">{{ $row->management_type }}</td>
            <td>{{ $row->employee_name }}</td>
        </tr>
        @endforeach
        <tr class="building-total">
            <td colspan="9" style="text-align:right; padding-right:6px;">Total :</td>
            <td class="right">{{ number_format($bldgTotalDue, 3) }}</td>
            <td></td>
            <td class="right">{{ number_format($bldgAmtReceived, 3) }}</td>
            <td class="right">{{ number_format($bldgNetAmt, 3) }}</td>
            <td colspan="6"></td>
        </tr>
        @endforeach

        <tr class="grand-total">
            <td colspan="9" style="text-align:right; padding-right:6px;">Grand Total :</td>
            <td class="right">{{ number_format($grandTotalDue, 3) }}</td>
            <td></td>
            <td class="right">{{ number_format($grandAmtReceived, 3) }}</td>
            <td class="right">{{ number_format($grandNetAmt, 3) }}</td>
            <td colspan="6"></td>
        </tr>
    </tbody>
</table>

</body>
</html>
