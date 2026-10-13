{{-- PLM approve / hand over, PLM refer back, lawyer accept / reject. Loaded into #myModal (.lg .lg-modal). --}}
@php
    $LC = \Modules\Masters\Services\LegalCase::class;
    $plmApprove = $process_flow == 801 && $action_key == 'APRV';
    $lawyerReject = $process_flow == 801 && $action_key == 'RFRBK' && $are_status == 1;
    $title = $plmApprove ? 'PLM approval' : ($lawyerReject ? 'Reject case' : ($action_key == 'RFRBK' ? 'Refer back' : 'Accept case'));
    $noteLabel = $lawyerReject ? 'Reason for rejecting' : ($action_key == 'RFRBK' ? 'Reason for referring back' : 'Note');
    $noteHint = $lawyerReject ? 'Sent to PLM with the case.' : ($action_key == 'RFRBK' ? 'Shown to the person who gets the case back.' : 'Kept in the case timeline.');
    $handled = (int) ($legal->handled_by ?: $defaultHandledBy);
@endphp
<div class="modal-dialog" role="document" style="max-width:{{ $plmApprove ? '660px' : '520px' }};">
  <div class="modal-content">
    <form action="{{ route('plmsApproveReferBackStore') }}" autocomplete="off" method="POST" id="renewal_note_modal" enctype="multipart/form-data" data-toggle="validator" onsubmit="return checkBeforeSubmit()">
      {{ csrf_field() }}
      <input type="hidden" name="legal_id" value="{{ $legal_id }}">
      <input type="hidden" name="process_flow" value="{{ $process_flow }}">
      <input type="hidden" name="action_key" value="{{ $action_key }}">
      <input type="hidden" name="are_status" value="{{ $are_status }}">
      <div class="modal-header">
        <h2 class="modal-title">{{ $title }}</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        @if($plmApprove)
        <div class="lg-kpis lg-kpis--card">
          <div class="lg-kpi"><div class="lg-kpi__label">Rent outstanding today</div><div class="lg-kpi__value lg-kpi__value--sm">{{ numberFormat($outstanding) }}</div></div>
          <div class="lg-kpi"><div class="lg-kpi__label">Building default</div><div class="lg-kpi__value lg-kpi__value--sm">{{ $LC::HANDLED_BY[(int) $defaultHandledBy] }}</div></div>
        </div>
        <div class="lg-form-grid">
          <div class="lg-field">
            <label for="lc_handled_by" class="lg-label">Handled by</label>
            <select class="lg-select" id="lc_handled_by" name="handled_by">
              @foreach($LC::HANDLED_BY as $k => $v)
                <option value="{{ $k }}" @if($k === $handled) selected @endif>{{ $v }}@if($k === (int) $defaultHandledBy) (building default)@endif</option>
              @endforeach
            </select>
          </div>
          <div class="lg-field lc-alhabib" @if($handled !== 1) style="display:none" @endif>
            <label for="lc_case_type" class="lg-label">Case type</label>
            <select class="lg-select" id="lc_case_type" name="case_type" required>
              @foreach($LC::TYPES as $k => $v)
                <option value="{{ $k }}" @if($k === (int) $legal->case_type) selected @endif>{{ $v }}</option>
              @endforeach
            </select>
          </div>
          <div class="lg-field lc-alhabib" @if($handled !== 1) style="display:none" @endif>
            <label for="lc_priority" class="lg-label">Priority</label>
            <select class="lg-select" id="lc_priority" name="priority" required>
              <option value="">Select priority</option>
              @foreach($LC::PRIORITIES as $k => $v)
                <option value="{{ $k }}" @if($k === (int) $legal->priority) selected @endif>{{ $v }}</option>
              @endforeach
            </select>
          </div>
          <div class="lg-field lc-alhabib" @if($handled !== 1) style="display:none" @endif>
            <label for="lc_lawyer" class="lg-label">Lawyer</label>
            <select class="lg-select" id="lc_lawyer" name="lawyer_user_id" required>
              <option value="">Select lawyer</option>
              @foreach($lawyers as $id => $name)
                <option value="{{ $id }}" @if((int) $id === (int) $legal->lawyer_user_id || count($lawyers) === 1) selected @endif>{{ $name }}</option>
              @endforeach
            </select>
            @if(!count($lawyers))<div class="lg-hint" style="color:var(--lg-danger);">No active user has the legal_advisor role.</div>@endif
          </div>
          <div class="span-all lc-handover" @if($handled === 1) style="display:none" @endif>
            <div class="lg-alert lg-alert--info"><div>The case is handed over and closed on our side. The contract stays in legal.</div></div>
          </div>
          <div class="lg-field span-2 lc-handover" @if($handled === 1) style="display:none" @endif>
            <label for="lc_handed_to" class="lg-label">Handed to</label>
            <input type="text" class="lg-input" id="lc_handed_to" name="handed_to_name" maxlength="150" value="{{ $legal->handed_to_name }}">
            <div class="lg-hint">Office or lawyer name.</div>
          </div>
          <div class="lg-field lc-handover" @if($handled === 1) style="display:none" @endif>
            <label for="lc_handover_ref" class="lg-label">Reference / letter no.</label>
            <input type="text" class="lg-input" id="lc_handover_ref" name="handover_ref" maxlength="100" value="{{ $legal->handover_ref }}">
          </div>
          <div class="lg-field lc-handover" @if($handled === 1) style="display:none" @endif>
            <label for="lc_handover_date" class="lg-label">Handover date</label>
            <input type="date" class="lg-input" id="lc_handover_date" name="handover_date" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
          </div>
        </div>
        @endif
        <div class="lg-field" style="margin-bottom:0;">
          <label for="legal_note" class="lg-label">{{ $noteLabel }}</label>
          <textarea class="lg-textarea" id="legal_note" name="legal_note" maxlength="1000" data-rule-maxlength="1000" data-msg-maxlength="Only allows 1000 characters" required></textarea>
          <div class="lg-hint">{{ $noteHint }}</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="lg-btn lg-btn--secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" name="submit" value="submit" id="lc_submit" class="lg-btn {{ $lawyerReject || $action_key == 'RFRBK' ? 'lg-btn--warning' : 'lg-btn--primary' }}">{{ $lawyerReject ? 'Reject' : ($action_key == 'RFRBK' ? 'Refer back' : ($plmApprove ? ($handled === 1 ? 'Approve' : 'Hand over & close') : 'Accept')) }}</button>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
  var wasSubmitted = false;
  function checkBeforeSubmit() {
    if (!wasSubmitted) {
      wasSubmitted = true;
      return wasSubmitted;
    }
    return false;
  }
  @if($plmApprove)
  (function () {
    function toggleHandledBy() {
      var alHabib = $('#lc_handled_by').val() === '1';
      $('.lc-alhabib').toggle(alHabib).find('select').prop('required', alHabib);
      $('.lc-handover').toggle(!alHabib);
      $('#lc_handed_to, #lc_handover_date').prop('required', !alHabib);
      $('#lc_submit').text(alHabib ? 'Approve' : 'Hand over & close');
    }
    $('#lc_handled_by').on('change', toggleHandledBy);
    toggleHandledBy();
  })();
  @endif
</script>
