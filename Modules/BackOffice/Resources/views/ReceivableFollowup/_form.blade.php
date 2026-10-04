<div class="modal-dialog modal-lg" role="document"><div class="modal-content">
  <div class="modal-header">
    <h4 class="modal-title" id="rf-modal-title">Follow-up</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  </div>
  <div class="modal-body">
    @include('backoffice::ReceivableFollowup._header')
    @if($recent->isNotEmpty())
      <section class="rf-recent" aria-labelledby="rf-recent-title">
        <h5 id="rf-recent-title">Recent follow-ups</h5>
        <ol>
          @foreach($recent as $f)
            <li>
              <div class="rf-when"><strong>{{ $f->created_at->format('d/m/Y H:i') }}</strong> · {{ $f->creatorName() }}@if($f->promised_date) · next follow-up {{ $f->promised_date->format('d/m/Y') }}@endif</div>
              <div class="rf-comment">{{ $f->comment }}</div>
            </li>
          @endforeach
        </ol>
      </section>
    @endif
    <form id="rf-form" action="{{ route('receivable-followup.store', $contract->id) }}" method="post" novalidate>
      @csrf
      <input type="hidden" name="as_on" value="{{ $asOn }}">
      <div class="rf-error alert alert-danger" role="alert" hidden></div>
      <div class="form-group">
        <label for="rf-comment">Comment <span class="rf-req" aria-hidden="true">*</span><span class="rf-sr">(required)</span>
          <span class="rf-count" id="rf-comment-count" aria-live="polite">0 / 1000</span></label>
        <textarea id="rf-comment" name="comment" class="form-control" rows="4" maxlength="1000" required aria-describedby="rf-comment-help rf-comment-error"></textarea>
        <span class="rf-help" id="rf-comment-help">What the tenant said, e.g. "Will pay after salary on the 5th".</span>
        <span class="rf-field-error" id="rf-comment-error" hidden></span>
      </div>
      <div class="form-group" style="max-width:260px">
        <label for="rf-promised">Next Follow-up Date</label>
        <input type="date" id="rf-promised" name="promised_date" class="form-control" min="{{ date('Y-m-d') }}" aria-describedby="rf-promised-help rf-promised_date-error">
        <span class="rf-help" id="rf-promised-help">Optional. When to follow up with the tenant again.</span>
        <span class="rf-field-error" id="rf-promised_date-error" hidden></span>
      </div>
      <div class="rf-foot">
        <button type="button" class="rf-btn" data-dismiss="modal">Cancel</button>
        <button type="submit" class="rf-btn rf-btn-primary">Save follow-up</button>
      </div>
    </form>
  </div>
</div></div>
