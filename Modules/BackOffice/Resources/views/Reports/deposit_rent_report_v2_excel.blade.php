<table>
    <tr>
        <td colspan="13" style="font-size:16px; font-weight:bold; text-align:center;">Deposit for rent/E&amp;W</td>
    </tr>
    <tr>
        <td colspan="13" style="font-size:12px; font-weight:bold; text-align:center;">For the Period {{ date('d/m/Y', strtotime($date1)) }} to {{ date('d/m/Y', strtotime($date2)) }}</td>
    </tr>
    <tr>
        <td colspan="5" style="font-weight:bold;">
            @if($buildingName) Building Name : {{ $buildingName }} @endif
        </td>
        <td colspan="2" style="font-weight:bold;">User: {{ $username }}</td>
        <td colspan="2" style="font-weight:bold;">Report Date: {{ date('d/m/Y H:i') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Sl No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Building Name</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Building No.</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Unit No.</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Tenant Name</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">By</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Agreement No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Payment No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Deposit Amount</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FF6B6B; text-align:center;">Refund Amount</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FF6B6B; text-align:center;">Refund Date</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FF6B6B; text-align:center;">Refund Payment No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Net Amount</th>
    </tr>
    @php $sn = 0; $grandTotal = 0; $grandRefund = 0; @endphp
    @foreach($rows as $row)
        @php
            $sn++;
            $amount = (float)$row->receipts_generation_amt;
            $refundAmt = (float)($row->refund_amt ?? 0);
            $netAmount = $amount - $refundAmt;
            $grandTotal += $amount;
            $grandRefund += $refundAmt;
        @endphp
        <tr>
            <td style="text-align:center;">{{ $sn }}</td>
            <td>{{ $row->building_name }}</td>
            <td>{{ $row->building_no }}</td>
            <td>{{ $row->unit_no }}</td>
            <td>{{ $row->tenant_name }}</td>
            <td>{{ $row->by }}</td>
            <td>{{ $row->agreementno }}</td>
            <td>{{ $row->receipts_generation_receipt_no }}</td>
            <td style="text-align:right;">{{ number_format($amount, 2) }}</td>
            <td style="text-align:right; color:#C80000;">{{ number_format($refundAmt, 2) }}</td>
            <td style="color:#C80000;">{{ $row->refund_date ? date('d/m/Y', strtotime($row->refund_date)) : '-' }}</td>
            <td style="color:#C80000;">{{ $row->refund_no ?: '-' }}</td>
            <td style="text-align:right;">{{ number_format($netAmount, 2) }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="8" style="text-align:right; font-weight:bold; background-color:#045DC2; color:#FFFFFF;">Total:</td>
        <td style="text-align:right; font-weight:bold; background-color:#045DC2; color:#FFFFFF;">{{ number_format($grandTotal, 2) }}</td>
        <td style="text-align:right; font-weight:bold; background-color:#045DC2; color:#FF6B6B;">{{ number_format($grandRefund, 2) }}</td>
        <td style="background-color:#045DC2;"></td>
        <td style="background-color:#045DC2;"></td>
        <td style="text-align:right; font-weight:bold; background-color:#045DC2; color:#FFFFFF;">{{ number_format($grandTotal - $grandRefund, 2) }}</td>
    </tr>
</table>
