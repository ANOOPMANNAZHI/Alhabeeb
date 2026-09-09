@php $totalCollected = 0; $totalPending = 0; @endphp
@forelse($rows as $i => $row)
@php $totalCollected += $row->collected; $totalPending += $row->pending; @endphp
<tr>
    <td>{{ $i + 1 }}</td>
    <td>{{ $row->building_name }} <small class="text-muted">({{ $row->building_code }})</small></td>
    <td>{{ $monthLabel }}</td>
    <td class="num">{{ number_format($row->collected, 3) }}</td>
    <td class="num">{{ number_format($row->pending, 3) }}</td>
</tr>
@empty
<tr><td colspan="5" align="center"><p>No rent activity for {{ $monthLabel }}</p></td></tr>
@endforelse
@if(count($rows))
<tr style="font-weight:700;background:#f7fdfb">
    <td></td>
    <td>Total ({{ count($rows) }} buildings)</td>
    <td>{{ $monthLabel }}</td>
    <td class="num">{{ number_format($totalCollected, 3) }}</td>
    <td class="num">{{ number_format($totalPending, 3) }}</td>
</tr>
@endif
