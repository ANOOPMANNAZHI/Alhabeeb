@forelse($receipts as $rec)
<tr>
    <td>{{ $rec->receipt_no }}</td>
    <td>{{ \Carbon\Carbon::parse($rec->receipt_date)->format('d/m/Y') }}</td>
    <td>{{ optional($rec->depositRefund)->deposit_refund_no }}</td>
    <td>{{ $rec->tenant_name }}</td>
    <td>{{ $rec->building_name }}</td>
    <td>{{ $rec->unit_code }}</td>
    <td class="num">{{ number_format($rec->deposit_amount, 3) }}</td>
    <td class="num">{{ number_format($rec->deduction_total + $rec->retained_amount, 3) }}</td>
    <td class="num">{{ number_format($rec->net_refund, 3) }}</td>
    <td>
        <a title="View Receipt" href="{{ route('depositRefundReceiptShow', $rec->id) }}" class="btn btn-tbl-edit btn-xs">
            <i class="fa fa-eye"></i>
        </a>
        <a title="Print Receipt" href="{{ route('depositRefundReceiptView', $rec->id) }}" target="_blank" class="btn btn-tbl-print btn-xs">
            <i class="fa fa-print"></i>
        </a>
        @if(optional($rec->depositRefund)->id)
        <a title="Open Refund" href="{{ route('depositRefund.show', $rec->depositRefund->id) }}" class="btn btn-tbl-edit btn-xs">
            <i class="fa fa-external-link"></i>
        </a>
        @endif
    </td>
</tr>
@empty
<tr><td colspan="10" align="center"><p>No Record</p></td></tr>
@endforelse

@if(isset($request) && $request->ajax())
<tr>
    <td colspan="10" id="pagination_ajax">
        {{ $receipts->withPath($route)->appends(request()->except(['page', 'ajax', '_token']))->links() }}
        <div class="pagination_info">
            @include('includes.pagination_info', ['paginator' => $receipts])
        </div>
    </td>
</tr>
@endif
