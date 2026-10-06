{{-- "Direct deposit to landlord": the tenant paid the landlord directly; information only, posting is unchanged --}}
@php $paidToLandlord = old('paid_to_landlord', (isset($receipt) && $receipt) ? (int) $receipt->receipts_generation_paid_to_landlord : 0); @endphp
<div class="col-sm-6">
    <div class="form-group">
        <label>&nbsp;</label>
        <div class="checkbox" style="padding:8px 12px;border:1px solid #f0c36d;background:#fffaf0;border-radius:4px">
            <input type="hidden" name="paid_to_landlord" value="0">
            <label for="paid_to_landlord" style="margin:0;cursor:pointer;font-weight:600">
                <input type="checkbox" name="paid_to_landlord" id="paid_to_landlord" value="1" {{ $paidToLandlord ? 'checked' : '' }}>
                Direct deposit to landlord
            </label>
            <div class="text-muted" style="font-size:12px;margin-top:2px">Tick when the tenant paid the landlord directly (cash, transfer or a cheque in the landlord's name).</div>
        </div>
    </div>
</div>
