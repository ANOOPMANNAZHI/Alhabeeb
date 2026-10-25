{{-- Tenant vacating after the renewal was accepted (304 / 301), before a renewal contract exists.
     Shown only at those stages; TenantRenewalController@renewalVacate checks again. --}}
@php
  list($vacateStage, $vacateError) = \Modules\BackOffice\Http\Controllers\TenantRenewalController::renewalVacateCheck($tenantContract);
@endphp
@if(!$vacateError)
<button type="button" data-toggle="modal" data-target="#renewalVacateModal" data-backdrop="static" data-keyboard="false"
        class="btn btn-circle btn-danger align-right" title="Tenant decided to vacate: stop the renewal and start the termination">
  Tenant Vacating
</button>

@push('renewal_vacate_modal')
<div class="modal" id="renewalVacateModal">
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">Confirm tenant vacating</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <form action="{{ route('tenantRenewalVacate', $tenantContract->id) }}" autocomplete="off" method="POST" id="renewal_vacate_form">
      {{csrf_field()}}
      <div class="modal-body">
        <p style="font-size:15px;margin:0 0 8px">Stop the renewal of contract {{ $tenantContract->tenant_contract_no }} and mark the tenant as vacating?</p>
        <p style="font-size:13px;color:#6b7280;margin:0 0 12px">
          The renewal leaves {{ (int) $vacateStage === 301 ? 'Contract Renewal' : 'Contract Under Renewal' }}, an Open For Termination
          request is created for {{ date('d/m/Y', strtotime($tenantContract->tenant_contract_valid_to_date)) }} (contract end) and the unit is marked as vacating.
        </p>
        <label for="renewal_vacate_note" style="font-size:13px">Reason (optional)</label>
        <textarea class="form-control" id="renewal_vacate_note" name="renewal_notes" maxlength="200" rows="2" placeholder="e.g. Tenant informed on call, moving out"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger" id="renewal_vacate_submit">Yes, vacating</button>
      </div>
    </form>
    </div>
</div>
</div>
<script>
  // one click only: stop a double submit
  document.getElementById('renewal_vacate_form').addEventListener('submit', function () {
    var b = document.getElementById('renewal_vacate_submit');
    b.disabled = true; b.textContent = 'Please wait...';
  });
</script>
@endpush
@endif
