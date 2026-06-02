<table>
    <tr>
        <td colspan="19" style="font-size:16px; font-weight:bold; text-align:center;">Rent Receivable as on Date</td>
    </tr>
    <tr>
        <td colspan="19" style="font-size:13px; font-weight:bold; text-align:center;">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td colspan="10" style="font-weight:bold;">
            @if($filters['building_name']) Building Name: {{ $filters['building_name'] }} @endif
            @if($filters['building_no']) Building No: {{ $filters['building_no'] }} @endif
            @if($filters['tenant_name']) Tenant: {{ $filters['tenant_name'] }} @endif
            @if($filters['management_type']) Management: {{ $filters['management_type'] }} @endif
            @if($filters['are']) ARE: {{ $filters['are'] }} @endif
        </td>
        <td colspan="5"></td>
        <td colspan="2" style="font-weight:bold;">User: {{ $user }}</td>
        <td style="font-weight:bold;">Date: {{ date('d/m/Y H:i') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Sl No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Building Name</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Tenant Name</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Agreement No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Unit No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Rent P.M</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">From</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">To</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Payment Mode</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Last Recv Date</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Total Due</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Paid Till</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Amt Received</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Net Amt Due</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Contact No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">PDC Status</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Cheque Closed</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Contact Name</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Management Type</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">ARE Name</th>
    </tr>
    @php
        $sl = 0;
        $grandTotalDue = 0;
        $grandAmtReceived = 0;
        $grandNetAmt = 0;
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->buildingname][] = $row;
        }
    @endphp
    @foreach($grouped as $buildingName => $buildingRows)
    @php
        $bldgTotalDue = 0;
        $bldgAmtReceived = 0;
        $bldgNetAmt = 0;
    @endphp
    @foreach($buildingRows as $row)
    @php
        $sl++;
        $bldgTotalDue    += $row->totaldue ?? 0;
        $bldgAmtReceived += $row->amount_received ?? 0;
        $bldgNetAmt      += $row->netamtdue ?? 0;
        $grandTotalDue    += $row->totaldue ?? 0;
        $grandAmtReceived += $row->amount_received ?? 0;
        $grandNetAmt      += $row->netamtdue ?? 0;
    @endphp
    <tr>
        <td style="text-align:center;">{{ $sl }}</td>
        <td>{{ $row->buildingname }}</td>
        <td>{{ $row->tenant_name }}</td>
        <td style="text-align:center;">{{ $row->contract_no }}</td>
        <td style="text-align:center;">{{ $row->unit_code }}</td>
        <td style="text-align:right;">{{ number_format($row->rentper_month, 3) }}</td>
        <td style="text-align:center;">{{ $row->start_date ? \Carbon\Carbon::parse($row->start_date)->format('d/m/Y') : '' }}</td>
        <td style="text-align:center;">{{ $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('d/m/Y') : '' }}</td>
        <td style="text-align:center;">{{ $row->paymentmode }}</td>
        <td style="text-align:center;">{{ $row->receipt_date ? \Carbon\Carbon::parse($row->receipt_date)->format('d/m/Y') : '' }}</td>
        <td style="text-align:right;">{{ number_format($row->totaldue, 3) }}</td>
        <td style="text-align:center;">{{ $row->lastpaid_till ? \Carbon\Carbon::parse($row->lastpaid_till)->format('d/m/Y') : '' }}</td>
        <td style="text-align:right;">{{ number_format($row->amount_received ?? 0, 3) }}</td>
        <td style="text-align:right;">{{ number_format($row->netamtdue, 3) }}</td>
        <td style="text-align:center;">{{ $row->contact_no }}</td>
        <td style="text-align:center;">{{ $row->pdc }}</td>
        <td style="text-align:center;">{{ $row->pdc_closed }}</td>
        <td>{{ $row->contact_person }}</td>
        <td style="text-align:center;">{{ $row->management_type }}</td>
        <td>{{ $row->employee_name }}</td>
    </tr>
    @endforeach
    <tr>
        <td colspan="10" style="font-weight:bold; text-align:right; background-color:#E8F4FC;">Total :</td>
        <td style="font-weight:bold; text-align:right; background-color:#E8F4FC;">{{ number_format($bldgTotalDue, 3) }}</td>
        <td style="background-color:#E8F4FC;"></td>
        <td style="font-weight:bold; text-align:right; background-color:#E8F4FC;">{{ number_format($bldgAmtReceived, 3) }}</td>
        <td style="font-weight:bold; text-align:right; background-color:#E8F4FC;">{{ number_format($bldgNetAmt, 3) }}</td>
        <td colspan="6" style="background-color:#E8F4FC;"></td>
    </tr>
    @endforeach
    <tr>
        <td colspan="10" style="font-weight:bold; text-align:right; background-color:#C5DCED;">Grand Total :</td>
        <td style="font-weight:bold; text-align:right; background-color:#C5DCED;">{{ number_format($grandTotalDue, 3) }}</td>
        <td style="background-color:#C5DCED;"></td>
        <td style="font-weight:bold; text-align:right; background-color:#C5DCED;">{{ number_format($grandAmtReceived, 3) }}</td>
        <td style="font-weight:bold; text-align:right; background-color:#C5DCED;">{{ number_format($grandNetAmt, 3) }}</td>
        <td colspan="6" style="background-color:#C5DCED;"></td>
    </tr>
</table>
