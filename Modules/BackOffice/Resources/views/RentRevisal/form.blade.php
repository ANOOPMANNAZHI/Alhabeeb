<div class="modal-header"><h4 class="modal-title">Rent revisal · {{ $contract->tenant_contract_no }}</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<form id="rrForm" action="{{ route('rentRevisal.store', $contract->id) }}">
  {{ csrf_field() }}
  <div class="modal-body">
    <p><strong>{{ $contract->tenant->tenant_name ?? '' }}</strong><br>
      {{ $contract->building->building_name ?? '' }}, unit {{ $contract->unit->unit_no ?? '' }} · ends {{ \Carbon\Carbon::parse($contract->tenant_contract_valid_to_date)->format('d/m/Y') }}<br>
      Current rent: OMR {{ number_format((float) $contract->tenant_contract_rent, 3) }}</p>
    @if($revisal)
      <p>Revised rent: <strong>OMR {{ number_format((float) $revisal->revised_rent, 3) }}</strong></p>
      @if($revisal->note)<p>Note: {{ $revisal->note }}</p>@endif
      <p class="text-muted">Sent {{ $revisal->created_at->format('d/m/Y H:i') }} · email: {{ $revisal->email_status }}</p>
    @else
      <div class="form-group"><label for="rr_rent">New monthly rent (OMR) *</label>
        <input type="text" class="form-control" id="rr_rent" name="revised_rent" required autocomplete="off"></div>
      <div class="form-group"><label for="rr_note">Note to the tenant (optional)</label>
        <textarea class="form-control" id="rr_note" name="note" maxlength="500" rows="3"></textarea></div>
      <div class="alert alert-info small" style="margin:0">
        Preview: "On renewal, the monthly rent will be <strong>OMR <span id="rr_prev_rent">...</span></strong> (currently OMR {{ number_format((float) $contract->tenant_contract_rent, 3) }})." <span id="rr_prev_note"></span>
      </div>
    @endif
    <p class="rr-msg" style="margin-top:10px"></p>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    @unless($revisal)<button type="submit" class="btn btn-primary">Send to tenant</button>@endunless
  </div>
</form>
