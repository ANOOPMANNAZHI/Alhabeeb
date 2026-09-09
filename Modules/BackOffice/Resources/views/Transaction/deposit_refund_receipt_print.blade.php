<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Deposit Refund Receipt {{ $receipt->receipt_no }}</title>
<style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 13px; color: #222; margin: 28px; }
    .head { text-align: center; margin-bottom: 22px; }
    .head h1 { font-size: 18px; margin: 0 0 4px; letter-spacing: .04em; }
    .head .sub { font-size: 12px; color: #666; }
    table.fields { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    table.fields td { padding: 4px 6px; vertical-align: top; }
    table.fields td.label { color: #666; width: 16%; }
    table.lines { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.lines th, table.lines td { border: 1px solid #bbb; padding: 7px 8px; }
    table.lines th { background: #f1f1f1; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: .03em; }
    table.lines td.num, table.lines th.num { text-align: right; }
    tr.total td { font-weight: 700; background: #fafafa; }
    tr.net td { font-weight: 700; background: #eef7f2; font-size: 14px; }
    .note { margin-top: 22px; font-size: 11px; color: #666; }
    .sign { margin-top: 46px; width: 100%; }
    .sign td { width: 50%; padding-top: 26px; border-top: 1px solid #999; font-size: 12px; color: #555; }
    .noprint { margin-bottom: 14px; }
    @media print { .noprint { display: none; } body { margin: 0; } }
</style>
</head>
<body>

<div class="noprint">
    <button onclick="window.print()">Print</button>
</div>

<div class="head">
    <h1>Deposit Refund Receipt</h1>
    <div class="sub">Statement of amounts deducted from the security deposit</div>
</div>

<table class="fields">
    <tr>
        <td class="label">Receipt No</td>
        <td><b>{{ $receipt->receipt_no }}</b></td>
        <td class="label">Date</td>
        <td>{{ \Carbon\Carbon::parse($receipt->receipt_date)->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td class="label">Tenant</td>
        <td>{{ $receipt->tenant_name }} @if($receipt->tenant_code) ({{ $receipt->tenant_code }}) @endif</td>
        <td class="label">Refund No</td>
        <td>{{ optional($receipt->depositRefund)->deposit_refund_no }}</td>
    </tr>
    <tr>
        <td class="label">Building</td>
        <td>{{ $receipt->building_name }}</td>
        <td class="label">Unit</td>
        <td>{{ $receipt->unit_code }}</td>
    </tr>
    @if($receipt->deposit_receipt_no)
    <tr>
        <td class="label">Deposit Receipt</td>
        <td colspan="3">{{ $receipt->deposit_receipt_no }}</td>
    </tr>
    @endif
</table>

<table class="lines">
    <thead>
        <tr>
            <th width="6%">#</th>
            <th>Description</th>
            <th class="num" width="22%">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <td><b>Security deposit held</b></td>
            <td class="num"><b>{{ number_format($receipt->deposit_amount, 3) }}</b></td>
        </tr>

        @foreach($receipt->lines as $line)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                {{ $line->description }}
                @if($line->line_type === 'retained')
                    <span style="color:#666"> (deposit balance retained)</span>
                @endif
            </td>
            <td class="num">{{ number_format($line->amount, 3) }}</td>
        </tr>
        @endforeach

        <tr class="total">
            <td></td>
            <td>Total deducted</td>
            <td class="num">{{ number_format($receipt->deduction_total + $receipt->retained_amount, 3) }}</td>
        </tr>
        <tr class="net">
            <td></td>
            <td>Net amount refunded</td>
            <td class="num">{{ number_format($receipt->net_refund, 3) }}</td>
        </tr>
    </tbody>
</table>

<div class="note">
    This receipt records the amounts deducted from the security deposit and the net amount refunded.
</div>

<table class="sign">
    <tr>
        <td>Received by (Tenant)</td>
        <td>For the Company</td>
    </tr>
</table>

</body>
</html>
