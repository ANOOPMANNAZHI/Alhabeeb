<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MERA {{ $receiptTypeLabel ?? "Rent" }} Receipt</title>
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
        .sub-header {
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
            font-size: 9px;
            text-align: center;
            vertical-align: middle;
            padding: 6px 3px;
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
                <div class="report-title">MERA {{ $receiptTypeLabel ?? "Rent" }} Receipt</div>
            </td>
            <td style="width:25%;" class="meta-info">
                Report date : {{ date('F d, Y H:i') }}<br>
                User ID : {{ $user }}
            </td>
        </tr>
    </table>

    <div class="sub-header">
        Building : {{ $buildingName }}<br>
        Period : {{ $period }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:3%;">Sl No</th>
                <th style="width:8%;">Doc No</th>
                <th style="width:7%;">Doc Date</th>
                <th style="width:6%;">Unit No</th>
                <th style="width:8%;">Rent P.M</th>
                <th style="width:7%;">Tenant Code</th>
                <th style="width:14%;">Tenant Name</th>
                <th style="width:8%;">Payment Method</th>
                <th style="width:7%;">Cheque No</th>
                <th style="width:7%;">Period From</th>
                <th style="width:7%;">Period To</th>
                <th style="width:8%;">Amount</th>
                <th style="width:8%;">Unit Type</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAmount = 0; @endphp
            @foreach($rows as $index => $row)
            @php $totalAmount += $row->amount; @endphp
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center">{{ $row->doc_no }}</td>
                <td class="center">{{ $row->doc_date ? \Carbon\Carbon::parse($row->doc_date)->format('d/m/Y') : '' }}</td>
                <td class="center">{{ $row->unit_no }}</td>
                <td class="right">{{ number_format($row->rent, 2) }}</td>
                <td class="center">{{ $row->tenant_code }}</td>
                <td>{{ $row->tenant_name }}</td>
                <td class="center">{{ $row->payment_method }}</td>
                <td class="center">{{ $row->cheque_no }}</td>
                <td class="center">{{ $row->eff_from ? \Carbon\Carbon::parse($row->eff_from)->format('d/m/Y') : '' }}</td>
                <td class="center">{{ $row->eff_to ? \Carbon\Carbon::parse($row->eff_to)->format('d/m/Y') : '' }}</td>
                <td class="right">{{ number_format($row->amount, 2) }}</td>
                <td class="center">{{ $row->unit_type }}</td>
            </tr>
            @endforeach
            <tr class="summary-row">
                <td colspan="11" style="text-align:right; padding-right:10px;">Total Amount :</td>
                <td class="right">{{ number_format($totalAmount, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="page-number" style="margin-top:10px;"></div>
</body>
</html>
