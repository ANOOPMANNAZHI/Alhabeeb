<table>
    <tr>
        <td colspan="6" style="font-size:16px; font-weight:bold; text-align:center;">Expense Details - Detailed Report</td>
    </tr>
    <tr>
        <td colspan="6" style="font-size:12px; font-weight:bold; text-align:center;">Date From: {{ date('d/m/Y', strtotime($date1)) }}   Date To: {{ date('d/m/Y', strtotime($date2)) }}</td>
    </tr>
    <tr>
        <td colspan="4" style="font-weight:bold;">
            @if($buildingName) Building: {{ $buildingName }} @endif
        </td>
        <td style="font-weight:bold;">User: {{ $user }}</td>
        <td style="font-weight:bold;">Date: {{ date('d/m/Y H:i') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Invoice/Voucher No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Description</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Date</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Reference No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Type</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Amount</th>
    </tr>
    @php $grandTotal = 0; @endphp
    @foreach($grouped as $buildingLabel => $accGroups)
        @if($showBuildingBanner)
            <tr>
                <td colspan="6" style="font-weight:bold; background-color:#46465F; color:#FFFFFF;">Building : {{ $buildingLabel }}</td>
            </tr>
        @endif
        @php $buildingTotal = 0; @endphp
        @foreach($accGroups as $accCode => $group)
            <tr>
                <td colspan="6" style="font-weight:bold; background-color:#A0C9F2;">{{ $accCode }} - {{ $group['desc'] }}</td>
            </tr>
            @php $subTotal = 0; @endphp
            @foreach($group['rows'] as $row)
                @php
                    $subTotal += (float)$row->debit_amt;
                    $expenseTypeLabel = $row->source === 'GL' ? 'General Ledger' : ($row->expense_type ?: '-');
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $row->maintenance_invoice_no }}</td>
                    <td>{{ $row->maintenance_invoice_desc }}</td>
                    <td style="text-align:center;">{{ $row->maintenance_invoice_date ? date('d/m/Y', strtotime($row->maintenance_invoice_date)) : '' }}</td>
                    <td style="text-align:center;">{{ $row->maintenance_invoice_refer_no }}</td>
                    <td style="text-align:center;">{{ $expenseTypeLabel }}</td>
                    <td style="text-align:right;">{{ number_format((float)$row->debit_amt, 2) }}</td>
                </tr>
            @endforeach
            @php $buildingTotal += $subTotal; @endphp
            <tr>
                <td colspan="5" style="text-align:right; font-weight:bold; background-color:#E8F4FC;">Sub Total :</td>
                <td style="text-align:right; font-weight:bold; background-color:#E8F4FC;">{{ number_format($subTotal, 2) }}</td>
            </tr>
        @endforeach
        @php $grandTotal += $buildingTotal; @endphp
        @if($showBuildingBanner)
            <tr>
                <td colspan="5" style="text-align:right; font-weight:bold; background-color:#D9E8F5;">Building Total :</td>
                <td style="text-align:right; font-weight:bold; background-color:#D9E8F5;">{{ number_format($buildingTotal, 2) }}</td>
            </tr>
        @endif
    @endforeach
    <tr>
        <td colspan="5" style="text-align:right; font-weight:bold; background-color:#C5DCED;">Grand Total :</td>
        <td style="text-align:right; font-weight:bold; background-color:#C5DCED;">{{ number_format($grandTotal, 2) }}</td>
    </tr>
</table>
