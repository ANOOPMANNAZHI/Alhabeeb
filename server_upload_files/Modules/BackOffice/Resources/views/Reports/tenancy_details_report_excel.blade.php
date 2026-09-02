<table>
    <tr>
        <td colspan="15" style="font-size:18px; font-weight:bold; text-align:center;">Tenancy Details by Building Wise</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight:bold;">{{ $filterLabel }}: {{ $filterValue }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="font-weight:bold;">User: {{ $user }}</td>
        <td colspan="2" style="font-weight:bold;">Date: {{ date('d/m/Y H:i') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">S#</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Building Code</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Building Name</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Unit No</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Unit Type</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Vacant Y/N</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Terminated Date</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Tenant Name</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Contact No</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Contract From</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Contract To</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Rent (Per Month)</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Payment Mode</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Rent Received Till</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Agreement Type</th>
    </tr>
    @php $sn = 0; @endphp
    @foreach($rows as $row)
        @php
            $sn++;
            $rowColor = $row->unit_vaccant_status == 0 ? '#FF8987' : ((($row->row_flag ?? null) == 'pending') ? '#FFD966' : null);
            $rowStyle = $rowColor ? "background-color:{$rowColor};" : '';
        @endphp
        <tr>
            <td style="text-align:center; {{ $rowStyle }}">{{ $sn }}</td>
            <td style="{{ $rowStyle }}">{{ $row->building_code }}</td>
            <td style="{{ $rowStyle }}">{{ $row->building_name }}</td>
            <td style="text-align:center; {{ $rowStyle }}">{{ $row->unit_no }}</td>
            <td style="{{ $rowStyle }}">{{ $row->unit_types_name }}</td>
            <td style="text-align:center; {{ $rowStyle }}">{{ $row->vaccant_status }}</td>
            <td style="text-align:center; {{ $rowStyle }}">{{ $row->termination_date ? date('d/m/Y', strtotime($row->termination_date)) : '' }}</td>
            <td style="{{ $rowStyle }}">{{ $row->tenant_name }}</td>
            <td style="text-align:center; {{ $rowStyle }}">{{ $row->unit_vaccant_status != 0 ? $row->tenant_contact_no : '' }}</td>
            <td style="text-align:center; {{ $rowStyle }}">{{ $row->unit_vaccant_status != 0 && $row->tenant_contract_start_date ? date('d/m/Y', strtotime($row->tenant_contract_start_date)) : '' }}</td>
            <td style="text-align:center; {{ $rowStyle }}">{{ $row->unit_vaccant_status != 0 && $row->tenant_contract_valid_to_date ? date('d/m/Y', strtotime($row->tenant_contract_valid_to_date)) : '' }}</td>
            <td style="text-align:right; {{ $rowStyle }}">{{ $row->unit_vaccant_status != 0 ? number_format((float)$row->tenant_contract_rent, 2) : '' }}</td>
            <td style="text-align:center; {{ $rowStyle }}">{{ $row->unit_vaccant_status != 0 ? $row->payment_method_code : '' }}</td>
            <td style="text-align:center; {{ $rowStyle }}">{{ $row->unit_vaccant_status != 0 && $row->tenant_contract_last_paid_date ? date('d/m/Y', strtotime($row->tenant_contract_last_paid_date)) : '' }}</td>
            <td style="text-align:center; {{ $rowStyle }}">{{ $row->unit_usage }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="4" style="text-align:right; font-weight:bold; background-color:#E8E8E8;">Summary :</td>
        <td style="text-align:center; font-weight:bold; background-color:#E8E8E8;">Total: {{ $totalUnits }}</td>
        <td style="text-align:center; font-weight:bold; background-color:#E8E8E8;" colspan="2">Occupied: {{ $occupiedCount }}</td>
        <td style="text-align:center; font-weight:bold; background-color:#E8E8E8;" colspan="2">Vacant: {{ $vacantCount }}</td>
        <td colspan="2" style="background-color:#E8E8E8;"></td>
        <td style="text-align:right; font-weight:bold; background-color:#E8E8E8;" colspan="2">Total Rent: {{ number_format($totalRent, 2) }}</td>
        <td colspan="2" style="background-color:#E8E8E8;"></td>
    </tr>
    <tr></tr>
    <tr>
        <td style="background-color:#FF8987;"></td>
        <td colspan="2">Vacant unit</td>
        <td style="background-color:#FFD966;"></td>
        <td colspan="4">Occupied, contract pending / not yet active (e.g. renewal in progress)</td>
    </tr>
</table>
