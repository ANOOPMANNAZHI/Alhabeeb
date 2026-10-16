{{-- ARE sends a referred-back case for verification again. Loaded into #myModal (.lg .lg-modal). --}}
<div class="modal-dialog" role="document" style="max-width:520px;">
  <div class="modal-content">
    <form action="{{ route('areStageStore') }}" autocomplete="off" method="POST" id="renewal_note_modal" enctype="multipart/form-data" data-toggle="validator">
      {{ csrf_field() }}
      <input type="hidden" name="legal_id" value="{{ $legal_id }}">
      <input type="hidden" name="process_flow" value="{{ $process_flow }}">
      <input type="hidden" name="are_status" value="{{ $are_status }}">
      <div class="modal-header">
        <h2 class="modal-title">Send for verification</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <div class="lg-field" style="margin:0;">
          <label for="are_note" class="lg-label">Note</label>
          <textarea class="lg-textarea" id="are_note" name="are_note" maxlength="200" data-rule-maxlength="200" data-msg-maxlength="Only allows 200 characters" required></textarea>
          <div class="lg-hint">What was corrected. Up to 200 characters.</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="lg-btn lg-btn--secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" name="submit" value="submit" class="lg-btn lg-btn--primary">Send</button>
      </div>
    </form>
  </div>
</div>
