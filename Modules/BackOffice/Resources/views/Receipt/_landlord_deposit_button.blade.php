{{-- Receipt view header, next to Print: Mark / Unmark "Direct deposit to landlord" (flag only — amounts, status and posting are untouched).
     The button sits inside the header's own form, so it submits #landlordDepositForm (_landlord_deposit_form) through the form attribute. --}}
@can('mark_receipt_landlord_deposit')
@php $paidToLandlord = (bool) $receipt->receipts_generation_paid_to_landlord; @endphp
<button type="submit" form="landlordDepositForm" class="btn btn-circle align-right" title="{{ $paidToLandlord ? 'Unmark direct deposit to landlord' : 'Mark as direct deposit to landlord' }}"
        style="margin-right:5px;width:auto;white-space:nowrap;{{ $paidToLandlord ? 'background:#fff;color:#334155;border:1px solid #94a3b8' : 'background:#b45309;color:#fff;border:1px solid #b45309' }}">
    {{ $paidToLandlord ? 'Unmark Direct Deposit to Landlord' : 'Mark Direct Deposit to Landlord' }}
</button>
@endcan
