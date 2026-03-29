<table>
    <tr>
        <td colspan="13" style="font-size:18px; font-weight:bold; text-align:center;">MERA Rent Receipt</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight:bold;">Building: {{ $buildingName }}</td>
        <td colspan="3" style="font-weight:bold;">Period: {{ $period }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="font-weight:bold;">User: {{ $user }}</td>
        <td colspan="2" style="font-weight:bold;">Date: {{ date('d/m/Y H:i') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Sl No</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Doc No</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Doc Date</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Unit No</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Rent P.M</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Tenant Code</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Tenant Name</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Payment Method</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Cheque No</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Period From</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Period To</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Amount</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Unit Type</th>
    </tr>
    @php $totalAmount = 0; @endphp
    @foreach($rows as $index => $row)
    @php $totalAmount += $row->amount; @endphp
    <tr>
        <td style="text-align:center;">{{ $index + 1 }}</td>
        <td style="text-align:center;">{{ $row->doc_no }}</td>
        <td style="text-align:center;">{{ $row->doc_date ? \Carbon\Carbon::parse($row->doc_date)->format('d/m/Y') : '' }}</td>
        <td style="text-align:center;">{{ $row->unit_no }}</td>
        <td style="text-align:right;">{{ number_format($row->rent, 2) }}</td>
        <td style="text-align:center;">{{ $row->tenant_code }}</td>
        <td>{{ $row->tenant_name }}</td>
        <td style="text-align:center;">{{ $row->payment_method }}</td>
        <td style="text-align:center;">{{ $row->cheque_no }}</td>
        <td style="text-align:center;">{{ $row->eff_from ? \Carbon\Carbon::parse($row->eff_from)->format('d/m/Y') : '' }}</td>
        <td style="text-align:center;">{{ $row->eff_to ? \Carbon\Carbon::parse($row->eff_to)->format('d/m/Y') : '' }}</td>
        <td style="text-align:right;">{{ number_format($row->amount, 2) }}</td>
        <td style="text-align:center;">{{ $row->unit_type }}</td>
    </tr>
    @endforeach
    <tr>
        <td colspan="11" style="text-align:right; font-weight:bold; background-color:#E8E8E8;">Total Amount :</td>
        <td style="text-align:right; font-weight:bold; background-color:#E8E8E8;">{{ number_format($totalAmount, 2) }}</td>
        <td style="background-color:#E8E8E8;"></td>
    </tr>
</table>
