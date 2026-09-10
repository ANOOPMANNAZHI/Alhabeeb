<table>
    <tr>
        <td colspan="3" style="font-size:16px; font-weight:bold; text-align:center;">Expense Details - Summary Report</td>
    </tr>
    <tr>
        <td colspan="3" style="font-size:12px; font-weight:bold; text-align:center;">Date From: {{ date('d/m/Y', strtotime($date1)) }}   Date To: {{ date('d/m/Y', strtotime($date2)) }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">
            @if($buildingName) Building: {{ $buildingName }} @endif
        </td>
        <td style="font-weight:bold;">User: {{ $user }}</td>
        <td style="font-weight:bold;">Date: {{ date('d/m/Y H:i') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Accounting Code</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Description</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Amount</th>
    </tr>
    @php $grandTotal = 0; @endphp
    @foreach($grouped as $buildingLabel => $accGroups)
        @if($showBuildingBanner)
            <tr>
                <td colspan="3" style="font-weight:bold; background-color:#46465F; color:#FFFFFF;">Building : {{ $buildingLabel }}</td>
            </tr>
        @endif
        @php $buildingTotal = 0; @endphp
        @foreach($accGroups as $accCode => $group)
            @php
                $subTotal = 0;
                foreach ($group['rows'] as $row) { $subTotal += (float)$row->debit_amt; }
                $buildingTotal += $subTotal;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $accCode }}</td>
                <td>{{ $group['desc'] }}</td>
                <td style="text-align:right;">{{ number_format($subTotal, 2) }}</td>
            </tr>
        @endforeach
        @php $grandTotal += $buildingTotal; @endphp
        @if($showBuildingBanner)
            <tr>
                <td colspan="2" style="text-align:right; font-weight:bold; background-color:#D9E8F5;">Building Total :</td>
                <td style="text-align:right; font-weight:bold; background-color:#D9E8F5;">{{ number_format($buildingTotal, 2) }}</td>
            </tr>
        @endif
    @endforeach
    <tr>
        <td colspan="2" style="text-align:right; font-weight:bold; background-color:#C5DCED;">Grand Total :</td>
        <td style="text-align:right; font-weight:bold; background-color:#C5DCED;">{{ number_format($grandTotal, 2) }}</td>
    </tr>
</table>
