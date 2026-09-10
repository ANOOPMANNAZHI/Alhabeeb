<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tenant's Payment History - Agreement Wise</title>
    <style>
        /* dompdf: no flexbox/grid, so layout is done with tables and the box
           model only. Sizes are in pt so they render predictably in print. */
        @page { size: A4 landscape; margin: 26px 22px 34px 22px; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10pt;
            color: #1f2933;
            margin: 0;
            padding: 0;
        }

        /* ---------- masthead ---------- */
        .masthead { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .masthead td { border: none; vertical-align: middle; padding: 0; }
        .logo { height: 62px; }

        .report-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1f3864;
            text-align: center;
            letter-spacing: .3pt;
        }
        .report-subtitle {
            font-size: 9.5pt;
            color: #5b6b7f;
            text-align: center;
            padding-top: 2px;
        }
        .meta-info { text-align: right; font-size: 9pt; color: #5b6b7f; line-height: 1.5; }

        .rule { height: 3px; background: #1f3864; margin: 8px 0 12px 0; }

        /* ---------- filter strip ---------- */
        .filters { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .filters td {
            border: 1px solid #b4c6e7;
            background: #eef3fb;
            padding: 7px 10px;
            font-size: 10pt;
        }
        .filters .lbl {
            color: #5b6b7f;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: .4pt;
        }
        .filters .val { font-weight: bold; color: #1f3864; font-size: 11pt; }

        /* ---------- data table ---------- */
        table.data { width: 100%; border-collapse: collapse; }

        /* repeat the column headings on every page */
        table.data thead { display: table-header-group; }

        table.data th {
            background: #1f3864;
            color: #ffffff;
            font-size: 9.5pt;
            font-weight: bold;
            text-align: left;
            padding: 7px 6px;
            border: 1px solid #1f3864;
        }
        table.data td {
            font-size: 10pt;
            padding: 6px;
            border: 1px solid #cfd9e6;
        }
        table.data td.num, table.data th.num { text-align: right; }

        tr.zebra td { background: #f6f9fd; }

        /* ---------- one block per contract ---------- */
        tr.contract-head td {
            background: #d9e2f3;
            border-top: 2px solid #1f3864;
            border-bottom: 1px solid #8faadc;
            color: #1f3864;
            font-size: 11pt;
            font-weight: bold;
            padding: 8px 6px;
        }
        .contract-meta { font-weight: normal; font-size: 9.5pt; color: #44546a; }

        tr.subtotal td {
            background: #eaf0fa;
            border-top: 1px solid #8faadc;
            border-bottom: 2px solid #8faadc;
            font-size: 10.5pt;
            font-weight: bold;
            color: #1f3864;
            padding: 7px 6px;
        }

        tr.spacer td { border: none; height: 12px; background: #ffffff; padding: 0; }

        tr.grand td {
            background: #1f3864;
            color: #ffffff;
            font-size: 12pt;
            font-weight: bold;
            padding: 10px 6px;
            border: 1px solid #1f3864;
        }

        tr.empty td { padding: 24px; text-align: center; color: #8a94a6; font-size: 11pt; }
    </style>
</head>
<body>

<table class="masthead">
    <tr>
        <td width="18%">@isset($logo)<img src="{{ $logo }}" class="logo">@endisset</td>
        <td width="64%">
            <div class="report-title">Tenant's Payment History</div>
            <div class="report-subtitle">Agreement Wise</div>
        </td>
        <td width="18%">
            <div class="meta-info">
                {{ $generatedAt }}<br>
                {{ $user }}
            </div>
        </td>
    </tr>
</table>

<div class="rule"></div>

<table class="filters">
    <tr>
        <td width="40%">
            <div class="lbl">Building</div>
            <div class="val">{{ $buildingName }}</div>
        </td>
        <td width="20%">
            <div class="lbl">Unit</div>
            <div class="val">{{ $unitNo }}</div>
        </td>
        <td width="40%">
            <div class="lbl">Tenant</div>
            <div class="val">{{ $tenantName }}</div>
        </td>
    </tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th style="width:5%;">#</th>
            <th style="width:16%;">Receipt No</th>
            <th style="width:12%;">Receipt Date</th>
            <th style="width:12%;">Period From</th>
            <th style="width:12%;">Period To</th>
            <th style="width:13%;">Payment Method</th>
            <th style="width:13%;">Cheque No</th>
            <th class="num" style="width:17%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($groups as $group)
        <tr class="contract-head">
            <td colspan="8">
                Contract No : {{ $group['contract_no'] }}
                <span class="contract-meta">
                    @if($group['start_date'])
                        &nbsp;&bull;&nbsp; {{ \Carbon\Carbon::parse($group['start_date'])->format('d/m/Y') }}
                        to {{ $group['end_date'] ? \Carbon\Carbon::parse($group['end_date'])->format('d/m/Y') : '' }}
                    @endif
                    &nbsp;&bull;&nbsp; Rent : {{ numberFormat($group['rent']) }}
                </span>
            </td>
        </tr>
        @foreach($group['rows'] as $i => $row)
        <tr @if($i % 2 == 1) class="zebra" @endif>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row->receipt_no }}</td>
            <td>{{ $row->receipt_date ? \Carbon\Carbon::parse($row->receipt_date)->format('d/m/Y') : '' }}</td>
            <td>{{ $row->eff_from ? \Carbon\Carbon::parse($row->eff_from)->format('d/m/Y') : '' }}</td>
            <td>{{ $row->eff_to ? \Carbon\Carbon::parse($row->eff_to)->format('d/m/Y') : '' }}</td>
            <td>{{ $row->payment_method }}</td>
            <td>{{ $row->cheque_no }}</td>
            <td class="num">{{ numberFormat($row->amount) }}</td>
        </tr>
        @endforeach
        <tr class="subtotal">
            <td colspan="7">Subtotal &nbsp;&mdash;&nbsp; {{ $group['contract_no'] }} ({{ $group['count'] }} receipt{{ $group['count'] == 1 ? '' : 's' }})</td>
            <td class="num">{{ numberFormat($group['subtotal']) }}</td>
        </tr>
        <tr class="spacer"><td colspan="8"></td></tr>
        @empty
        <tr class="empty"><td colspan="8">No records found for this selection.</td></tr>
        @endforelse

        <tr class="grand">
            <td colspan="7">GRAND TOTAL &nbsp;&mdash;&nbsp; {{ $receiptCount }} receipt{{ $receiptCount == 1 ? '' : 's' }} across {{ $contractCount }} contract{{ $contractCount == 1 ? '' : 's' }}</td>
            <td class="num">{{ numberFormat($grandTotal) }}</td>
        </tr>
    </tbody>
</table>

</body>
</html>
