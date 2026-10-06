{{-- Receipt view: "Direct deposit to landlord" status and Mark / Unmark (flag only — amounts, status and posting are untouched) --}}
@php $paidToLandlord = (bool) $receipt->receipts_generation_paid_to_landlord; @endphp
@if($paidToLandlord || auth()->user()->can('mark_receipt_landlord_deposit'))
<div class="col-lg-12 p-t-20">
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;padding:10px 14px;border-radius:4px;border:1px solid {{ $paidToLandlord ? '#f0c36d' : '#e2e8f0' }};background:{{ $paidToLandlord ? '#fffaf0' : '#f8fafc' }}">
        @if($paidToLandlord)
            <span style="display:inline-block;padding:4px 10px;border-radius:4px;background:#b45309;color:#fff;font-weight:600;font-size:13px">
                <i class="fa fa-university" aria-hidden="true"></i> Paid directly to landlord
            </span>
            <span class="text-muted" style="font-size:13px">
                @if($receipt->receipts_generation_landlord_marked_at)
                    Marked by {{ optional($receipt->landlordMarkedBy)->username ?: 'user #'.$receipt->receipts_generation_landlord_marked_by }}
                    on {{ $receipt->receipts_generation_landlord_marked_at->format('d/m/Y H:i') }}
                @endif
            </span>
        @else
            <span class="text-muted" style="font-size:13px">Not marked as direct deposit to landlord.</span>
        @endif
        @can('mark_receipt_landlord_deposit')
            <form action="{{ route('receiptLandlordDeposit', $receipt->id) }}" method="POST" style="margin:0 0 0 auto"
                  onsubmit="return confirm('{{ $paidToLandlord ? 'Remove the direct deposit to landlord mark from this receipt?' : 'Mark this receipt as paid directly to the landlord?' }}')">
                {{ csrf_field() }}
                <input type="hidden" name="paid_to_landlord" value="{{ $paidToLandlord ? 0 : 1 }}">
                <button type="submit" style="width:auto;min-width:0;max-width:none;height:auto;white-space:nowrap;padding:6px 14px;border-radius:4px;font-size:13px;font-weight:600;cursor:pointer;{{ $paidToLandlord ? 'background:#fff;color:#334155;border:1px solid #94a3b8' : 'background:#b45309;color:#fff;border:1px solid #b45309' }}">
                    {{ $paidToLandlord ? 'Unmark direct deposit to landlord' : 'Mark as direct deposit to landlord' }}
                </button>
            </form>
        @endcan
    </div>
</div>
@endif
