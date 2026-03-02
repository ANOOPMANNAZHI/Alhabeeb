<table>
    <tr>
        <td colspan="8" style="font-size:18px; font-weight:bold; text-align:center;">Monthly Tenancy Details</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold;">As On Date: {{ $as_on_date }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="font-weight:bold;">User: {{ $user }}</td>
        <td style="font-weight:bold;">Date: {{ date('d/m/Y H:i') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">S#</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Building Name</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Location</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Management Type</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Total Units</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Occupied</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Vacant</th>
        <th style="font-weight:bold; text-align:center; background-color:#1D80CC; color:#FFFFFF;">Occupancy %</th>
    </tr>
    @php $sn = 0; @endphp
    @foreach($groupedRows as $locationName => $group)
        @foreach($group['rows'] as $row)
        @php $sn++; @endphp
        <tr>
            <td style="text-align:center;">{{ $sn }}</td>
            <td>{{ $row->building_name }}</td>
            <td>{{ $row->locations_name }}</td>
            <td>{{ $row->management_types_name }}</td>
            <td style="text-align:center;">{{ $row->total_units }}</td>
            <td style="text-align:center;">{{ $row->occupied }}</td>
            <td style="text-align:center;">{{ $row->vacant }}</td>
            <td style="text-align:center;">{{ number_format($row->occupancy_pct, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4" style="text-align:right; font-weight:bold; background-color:#D6EAF8;">{{ $locationName }} Subtotal :</td>
            <td style="text-align:center; font-weight:bold; background-color:#D6EAF8;">{{ $group['totalUnits'] }}</td>
            <td style="text-align:center; font-weight:bold; background-color:#D6EAF8;">{{ $group['totalOccupied'] }}</td>
            <td style="text-align:center; font-weight:bold; background-color:#D6EAF8;">{{ $group['totalVacant'] }}</td>
            <td style="text-align:center; font-weight:bold; background-color:#D6EAF8;">{{ number_format($group['occupancyPct'], 2) }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="4" style="text-align:right; font-weight:bold; background-color:#E8E8E8;">Grand Total :</td>
        <td style="text-align:center; font-weight:bold; background-color:#E8E8E8;">{{ $totalUnits }}</td>
        <td style="text-align:center; font-weight:bold; background-color:#E8E8E8;">{{ $totalOccupied }}</td>
        <td style="text-align:center; font-weight:bold; background-color:#E8E8E8;">{{ $totalVacant }}</td>
        <td style="text-align:center; font-weight:bold; background-color:#E8E8E8;">{{ number_format($totalOccupancyPct, 2) }}</td>
    </tr>
</table>
