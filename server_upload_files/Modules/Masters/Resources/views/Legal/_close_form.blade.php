{{-- Close a case before the lawyer accepted it (PLM / lawyer approval), or drop a case referred back to ARE.
     Loaded into #myModal; a reason is required (user, 18/10/2026). --}}
@php $drop = (int) $legal->work_flow_processes_code === 804; @endphp
<div class="modal-dialog" role="document" style="max-width:520px;">
  <div class="modal-content">
    <form action="{{ route('legalCaseClose') }}" method="POST" autocomplete="off">
      {{ csrf_field() }}
      <input type="hidden" name="legal_id" value="{{ $legal->id }}">
      <div class="modal-header">
        <h2 class="modal-title">{{ $drop ? 'Drop case' : 'Close case' }}</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <p style="margin-top:0;">Case #{{ $legal->id }} · {{ $legal->tenantContract->tenant_contract_no ?? '' }} · {{ $legal->tenantContract->tenant->tenant_name ?? '' }}</p>
        <div class="lg-field">
          <label for="lc_close_note" class="lg-label">{{ $drop ? 'Why is the case dropped?' : 'Reason for closing' }}</label>
          <textarea id="lc_close_note" name="legal_note" class="lg-textarea" rows="3" maxlength="1000" required></textarea>
          <div class="lg-hint">Kept in the case timeline.</div>
        </div>
        <div class="lg-alert lg-alert--info" style="margin:12px 0 0;"><div>
          @if($otherOpen)
            The contract has another open legal case (#{{ $otherOpen->id }}), so it stays in legal.
          @else
            The contract goes back to normal and shows in the receivables again.
          @endif
        </div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="lg-btn lg-btn--secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="lg-btn lg-btn--danger-solid">{{ $drop ? 'Drop case' : 'Close case' }}</button>
      </div>
    </form>
  </div>
</div>
