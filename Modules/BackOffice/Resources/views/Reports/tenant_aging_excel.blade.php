@php
    $fmt   = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : ''; };
    $nCols = 6 + count($buckets); // Sl, Building, Tenant, Contract, Unit, Balance + buckets
    $keys  = array_keys($buckets);
@endphp
<table>
    <tr>
        <td colspan="{{ $nCols }}" style="font-size:16px; font-weight:bold; text-align:center;">Tenant Aging Report as on Date</td>
    </tr>
    <tr>
        <td colspan="{{ $nCols }}" style="font-size:13px; font-weight:bold; text-align:center;">{{ $fmt($date) }}</td>
    </tr>
    <tr>
        <td colspan="{{ $nCols - 3 }}" style="font-weight:bold;">
            @if($filters['building_name']) Building Name: {{ $filters['building_name'] }} @endif
            @if($filters['building_no']) Building No: {{ $filters['building_no'] }} @endif
            @if($filters['tenant_name']) Tenant: {{ $filters['tenant_name'] }} @endif
            @if($filters['management_type']) Management: {{ $filters['management_type'] }} @endif
            @if($filters['are']) ARE: {{ $filters['are'] }} @endif
            @if(!empty($filters['pdc'])) PDC: {{ $filters['pdc'] }} @endif
        </td>
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
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">Balance as on {{ $fmt($date) }}</th>
        @foreach($buckets as $b)
        <th style="font-weight:bold; background-color:#045DC2; color:#FFFFFF; text-align:center;">{{ $b['label'] }} ({{ $b['from'] ? $fmt($b['from']) . ' - ' : 'up to ' }}{{ $fmt($b['to']) }})</th>
        @endforeach
    </tr>
    @php
        $sl = 0;
        $grand = array_fill_keys($keys, 0);
        $grandBal = 0;
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->buildingname][] = $row;
        }
    @endphp
    @foreach($grouped as $buildingName => $buildingRows)
    @php
        $bldg = array_fill_keys($keys, 0);
        $bldgBal = 0;
    @endphp
    @foreach($buildingRows as $row)
    @php
        $sl++;
        $bldgBal  += $row->netamtdue ?? 0;
        $grandBal += $row->netamtdue ?? 0;
        foreach ($keys as $k) {
            $bldg[$k]  += $row->aging[$k];
            $grand[$k] += $row->aging[$k];
        }
    @endphp
    <tr>
        <td style="text-align:center;">{{ $sl }}</td>
        <td>{{ $row->buildingname }}</td>
        <td>{{ $row->tenant_name }}</td>
        <td style="text-align:center;">{{ $row->contract_no }}</td>
        <td style="text-align:center;">{{ $row->unit_code }}</td>
        <td style="text-align:right;">{{ number_format($row->netamtdue ?? 0, 3) }}</td>
        @foreach($keys as $k)
        <td style="text-align:right;">{{ number_format($row->aging[$k], 3) }}</td>
        @endforeach
    </tr>
    @endforeach
    <tr>
        <td colspan="5" style="font-weight:bold; text-align:right; background-color:#E8F4FC;">Total :</td>
        <td style="font-weight:bold; text-align:right; background-color:#E8F4FC;">{{ number_format($bldgBal, 3) }}</td>
        @foreach($keys as $k)
        <td style="font-weight:bold; text-align:right; background-color:#E8F4FC;">{{ number_format($bldg[$k], 3) }}</td>
        @endforeach
    </tr>
    @endforeach
    <tr>
        <td colspan="5" style="font-weight:bold; text-align:right; background-color:#C5DCED;">Grand Total :</td>
        <td style="font-weight:bold; text-align:right; background-color:#C5DCED;">{{ number_format($grandBal, 3) }}</td>
        @foreach($keys as $k)
        <td style="font-weight:bold; text-align:right; background-color:#C5DCED;">{{ number_format($grand[$k], 3) }}</td>
        @endforeach
    </tr>
</table>
