<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Tenancy Details</title>
    <style>
        @page {
            size: A4 landscape;
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
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            padding-top: 5px;
        }
        .meta-info {
            text-align: right;
            font-size: 9px;
            color: #1D80CC;
            font-weight: bold;
        }
        .as-on-date {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 10px;
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
            font-size: 10px;
            text-align: center;
            vertical-align: middle;
            padding: 6px 4px;
            border: 0.5px solid #000000;
        }
        .data-table td {
            font-size: 9px;
            vertical-align: middle;
            padding: 4px 5px;
            border: 0.5px solid #000000;
        }
        .data-table td.center {
            text-align: center;
        }
        .summary-row td {
            background-color: #E8E8E8;
            font-weight: bold;
            font-size: 10px;
        }
        .location-subtotal-row td {
            background-color: #D6EAF8;
            font-weight: bold;
            font-size: 9px;
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
                <div class="report-title">Monthly Tenancy Details</div>
            </td>
            <td style="width:25%;" class="meta-info">
                Report date : {{ date('F d, Y H:i') }}<br>
                User ID : {{ $user }}
            </td>
        </tr>
    </table>

    <div class="as-on-date">As On Date : {{ $as_on_date }}</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%;">S#</th>
                <th style="width:22%;">Building Name</th>
                <th style="width:16%;">Location</th>
                <th style="width:15%;">Management Type</th>
                <th style="width:10%;">Total Units</th>
                <th style="width:10%;">Occupied</th>
                <th style="width:10%;">Vacant</th>
                <th style="width:12%;">Occupancy %</th>
            </tr>
        </thead>
        <tbody>
            @php $sn = 0; @endphp
            @foreach($groupedRows as $locationName => $group)
                @foreach($group['rows'] as $row)
                @php $sn++; @endphp
                <tr>
                    <td class="center">{{ $sn }}</td>
                    <td>{{ $row->building_name }}</td>
                    <td>{{ $row->locations_name }}</td>
                    <td>{{ $row->management_types_name }}</td>
                    <td class="center">{{ $row->total_units }}</td>
                    <td class="center">{{ $row->occupied }}</td>
                    <td class="center">{{ $row->vacant }}</td>
                    <td class="center">{{ number_format($row->occupancy_pct, 2) }}</td>
                </tr>
                @endforeach
                <tr class="location-subtotal-row">
                    <td colspan="4" style="text-align:right; padding-right:10px;">{{ $locationName }} Subtotal :</td>
                    <td class="center">{{ $group['totalUnits'] }}</td>
                    <td class="center">{{ $group['totalOccupied'] }}</td>
                    <td class="center">{{ $group['totalVacant'] }}</td>
                    <td class="center">{{ number_format($group['occupancyPct'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="summary-row">
                <td colspan="4" style="text-align:right; padding-right:10px;">Grand Total :</td>
                <td class="center">{{ $totalUnits }}</td>
                <td class="center">{{ $totalOccupied }}</td>
                <td class="center">{{ $totalVacant }}</td>
                <td class="center">{{ number_format($totalOccupancyPct, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="page-number" style="margin-top:10px;"></div>
</body>
</html>
