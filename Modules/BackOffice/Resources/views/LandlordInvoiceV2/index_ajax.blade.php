@forelse($landlordInvoicesV2 as $inv)
<tr>
    <td>{{ $inv->invoice_no }}</td>
    <td>{{ $inv->invoice_type_label }}</td>
    <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d/m/Y') }}</td>
    <td>{{ $inv->vendor_name }}</td>
    <td>{{ $inv->building_name }}</td>
    <td>{{ number_format($inv->grand_total, 3) }}</td>
    <td>
        @if($inv->status == 'voided')
            <span class="btn-circle btn-danger btn-sm m-b-10"><b>Voided</b></span>
        @elseif($inv->isPosted())
            <span class="btn-circle btn-info btn-sm m-b-10" title="AX Journal {{ $inv->ax_batch_id }}"><b>Posted</b></span>
        @elseif($inv->status == 'posting')
            <span class="btn-circle btn-warning btn-sm m-b-10"><b>Posting…</b></span>
        @else
            <span class="btn-circle btn-success btn-sm m-b-10"><b>Active</b></span>
        @endif
    </td>
    <td>
        <a title="Print" href="{{ route('landlordInvoiceV2Print', $inv) }}" target="_blank" class="btn btn-tbl-print btn-xs">
            <i class="fa fa-print"></i>
        </a>
        @if($inv->status == 'active')
            <a title="Edit" href="{{ route('landlord-invoice-v2.edit', $inv) }}" class="btn btn-tbl-edit btn-xs">
                <i class="fa fa-pencil"></i>
            </a>
            <form action="{{ route('landlord-invoice-v2.destroy', $inv) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Void this invoice? The invoice number will be permanently reserved.');">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <button type="submit" title="Void" class="btn btn-tbl-delete btn-xs">
                    <i class="fa fa-ban"></i>
                </button>
            </form>
            @if(auth()->user()->can('post_landlord_invoice_v2'))
            <form action="{{ route('landlordInvoiceV2Post', $inv) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Post invoice {{ $inv->invoice_no }} to Microsoft Dynamics AX? This cannot be undone.');">
                {{ csrf_field() }}
                <button type="submit" title="Post to AX" class="btn btn-tbl-edit btn-xs" style="background:#0288d1;color:#fff;">
                    <i class="fa fa-upload"></i>
                </button>
            </form>
            @endif
        @endif
    </td>
</tr>
@empty
<tr><td colspan="8" align="center"><p>No Record</p></td></tr>
@endforelse

@if(isset($request->ajax))
<tr>
    <td colspan="8" id="pagination_ajax">
        {{ $landlordInvoicesV2->withPath($route)->appends(request()->except(['page', 'ajax', '_token']))->links() }}
        <div class="pagination_info">
            @include('includes.pagination_info', ['paginator' => $landlordInvoicesV2])
        </div>
    </td>
</tr>
@endif
