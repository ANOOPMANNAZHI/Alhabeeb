{{-- Form behind the header Mark / Unmark button (_landlord_deposit_button) --}}
@can('mark_receipt_landlord_deposit')
@php $paidToLandlord = (bool) $receipt->receipts_generation_paid_to_landlord; @endphp
<form id="landlordDepositForm" action="{{ route('receiptLandlordDeposit', $receipt->id) }}" method="POST" style="display:none"
      onsubmit="return confirm('{{ $paidToLandlord ? 'Remove the direct deposit to landlord mark from this receipt?' : 'Mark this receipt as paid directly to the landlord?' }}')">
    {{ csrf_field() }}
    <input type="hidden" name="paid_to_landlord" value="{{ $paidToLandlord ? 0 : 1 }}">
</form>
@endcan
