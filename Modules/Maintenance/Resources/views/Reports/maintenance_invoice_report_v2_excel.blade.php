<table>
    <tr>
        <td colspan="11" style="font-size:16px; font-weight:bold; text-align:center;">Maintenance Invoice Report</td>
    </tr>
    <tr>
        <td colspan="11" style="font-size:12px; font-weight:bold; text-align:center;">Date From: {{ date('d/m/Y', strtotime($date1)) }}   Date To: {{ date('d/m/Y', strtotime($date2)) }}</td>
    </tr>
    <tr>
        <td colspan="9" style="font-weight:bold;">
            @if($buildingName) Building: {{ $buildingName }} @endif
        </td>
        <td colspan="2" style="font-weight:bold;">User: {{ $user }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Invoice No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Date</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Vendor</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Description</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Refer No</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Service Report No.</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Material</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Labour</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Amount</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Lineitem Desc</th>
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Building Name</th>
    </tr>
    @php $grandMaterial = 0; $grandLabour = 0; $grandAmount = 0; @endphp
    @foreach($rows as $row)
        @php
            $grandMaterial += (float)$row->material_charge;
            $grandLabour   += (float)$row->labour_charge;
            $grandAmount   += (float)$row->amount;
        @endphp
        <tr>
            <td>{{ $row->invoice_no }}</td>
            <td style="text-align:center;">{{ $row->invoice_date ? date('d/m/Y', strtotime($row->invoice_date)) : '' }}</td>
            <td>{{ $row->vendor }}</td>
            <td>{{ $row->description }}</td>
            <td>{{ $row->refer_no }}</td>
            <td>{{ $row->service_report_no }}</td>
            <td style="text-align:right;">{{ number_format((float)$row->material_charge, 3) }}</td>
            <td style="text-align:right;">{{ number_format((float)$row->labour_charge, 3) }}</td>
            <td style="text-align:right;">{{ number_format((float)$row->amount, 3) }}</td>
            <td>{{ $row->lineitem_desc }}</td>
            <td>{{ $row->building_name }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="6" style="text-align:right; font-weight:bold; background-color:#C5DCED;">Grand Total :</td>
        <td style="text-align:right; font-weight:bold; background-color:#C5DCED;">{{ number_format($grandMaterial, 3) }}</td>
        <td style="text-align:right; font-weight:bold; background-color:#C5DCED;">{{ number_format($grandLabour, 3) }}</td>
        <td style="text-align:right; font-weight:bold; background-color:#C5DCED;">{{ number_format($grandAmount, 3) }}</td>
        <td colspan="2" style="background-color:#C5DCED;"></td>
    </tr>
</table>
