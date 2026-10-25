{{-- Receipt view: "Paid directly to landlord" badge with who/when. The Mark / Unmark button is in the header (_landlord_deposit_button). --}}
@if($receipt->receipts_generation_paid_to_landlord)
<div class="col-lg-12 p-t-20">
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;padding:10px 14px;border-radius:4px;border:1px solid #f0c36d;background:#fffaf0">
        <span style="display:inline-block;padding:4px 10px;border-radius:4px;background:#b45309;color:#fff;font-weight:600;font-size:13px">
            <i class="fa fa-university" aria-hidden="true"></i> Paid directly to landlord
        </span>
        <span class="text-muted" style="font-size:13px">
            @if($receipt->receipts_generation_landlord_marked_at)
                Marked by {{ optional($receipt->landlordMarkedBy)->username ?: 'user #'.$receipt->receipts_generation_landlord_marked_by }}
                on {{ $receipt->receipts_generation_landlord_marked_at->format('d/m/Y H:i') }}
            @endif
        </span>
    </div>
</div>
@endif
