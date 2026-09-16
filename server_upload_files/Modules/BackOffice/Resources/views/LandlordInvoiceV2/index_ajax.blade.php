@php
    $isTax   = $tab === 'tax';
    $colspan = $isTax ? 11 : 10;
    $offset  = $landlordInvoicesV2->perPage() * ($landlordInvoicesV2->currentPage() - 1);
@endphp
@forelse($landlordInvoicesV2 as $i => $inv)
@php $show = route('landlord-invoice-v2.show', $inv); @endphp
<tr>
    <td><a class="no-link" href="{{ $show }}">{{ $offset + $i + 1 }}</a></td>
    <td><a class="no-link" href="{{ $show }}"><b>{{ $inv->invoice_no }}</b></a></td>
    <td><a class="no-link" href="{{ $show }}">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d/m/Y') }}</a></td>
    <td><a class="no-link" href="{{ $show }}">{{ \Carbon\Carbon::createFromDate($inv->period_year, $inv->period_month, 1)->format('M Y') }}</a></td>
    <td><a class="no-link" href="{{ $show }}">{{ $inv->vendor_name }}</a></td>
    <td><a class="no-link" href="{{ $show }}">{{ $inv->building_name }}</a></td>
    <td class="num"><a class="no-link" href="{{ $show }}">{{ number_format($inv->subtotal, 3) }}</a></td>
    @if($isTax)
    <td class="num"><a class="no-link" href="{{ $show }}">{{ number_format($inv->vat_total, 3) }}</a></td>
    @endif
    <td class="num"><a class="no-link" href="{{ $show }}"><b>{{ number_format($inv->grand_total, 3) }}</b></a></td>
    <td>
        <a class="no-link" href="{{ $show }}">
        @if($inv->status == 'voided')
            <span class="liv2-status liv2-status-voided">Voided</span>
        @elseif($inv->isPosted())
            <span class="liv2-status liv2-status-posted" title="AX Journal {{ $inv->ax_batch_id }}">Posted</span>
        @elseif($inv->status == 'posting')
            <span class="liv2-status liv2-status-posting">Posting…</span>
        @else
            <span class="liv2-status liv2-status-active">Active</span>
        @endif
        </a>
    </td>
    <td class="liv2-actions">
        <a title="View" href="{{ $show }}" class="btn btn-tbl-edit btn-xs" aria-label="View {{ $inv->invoice_no }}">
            <i class="fa fa-eye"></i>
        </a>
        <a title="Print" href="{{ route('landlordInvoiceV2Print', $inv) }}" target="_blank" class="btn btn-tbl-print btn-xs" aria-label="Print {{ $inv->invoice_no }}">
            <i class="fa fa-print"></i>
        </a>
        @if($inv->status == 'active')
            <a title="Edit" href="{{ route('landlord-invoice-v2.edit', $inv) }}" class="btn btn-tbl-edit btn-xs" aria-label="Edit {{ $inv->invoice_no }}">
                <i class="fa fa-pencil"></i>
            </a>
            <form action="{{ route('landlord-invoice-v2.destroy', $inv) }}" method="POST" onsubmit="return confirm('Void invoice {{ $inv->invoice_no }}? The invoice number will be permanently reserved.');">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <button type="submit" title="Void" class="btn btn-tbl-delete btn-xs" aria-label="Void {{ $inv->invoice_no }}">
                    <i class="fa fa-ban"></i>
                </button>
            </form>
            @if(auth()->user()->can('post_landlord_invoice_v2'))
            <form action="{{ route('landlordInvoiceV2Post', $inv) }}" method="POST" onsubmit="return confirm('Post invoice {{ $inv->invoice_no }} to Microsoft Dynamics AX? This cannot be undone.');">
                {{ csrf_field() }}
                <button type="submit" title="Post to AX" class="btn btn-xs liv2-btn-ax" aria-label="Post {{ $inv->invoice_no }} to AX">
                    <i class="fa fa-upload"></i>
                </button>
            </form>
            @endif
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="{{ $colspan }}">
        <div class="liv2-empty">
            <p>No {{ $isTax ? 'Tax Invoices' : 'Other Deductions' }} match the current filters.</p>
            <a href="{{ route('landlord-invoice-v2.create') }}" class="btn btn-circle btn-primary">Add Landlord Invoice</a>
        </div>
    </td>
</tr>
@endforelse

@if(isset($request->ajax))
<tr>
    <td colspan="{{ $colspan }}" id="pagination_ajax">
        {{ $landlordInvoicesV2->withPath($route)->appends(request()->except(['page', 'ajax', '_token']))->links() }}
        <div class="pagination_info">
            @include('includes.pagination_info', ['paginator' => $landlordInvoicesV2])
        </div>
    </td>
</tr>
@endif
