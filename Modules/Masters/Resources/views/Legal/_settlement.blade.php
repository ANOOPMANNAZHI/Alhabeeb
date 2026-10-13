{{-- Settlement section of the legal case page (phase 3). Inside a .lg page. --}}
@php
    $LS = \Modules\Masters\Services\LegalSettlement::class;
    $sStatus = $settlement ? (int) $settlement->status : null;
    $badge = [0 => 'warning', 1 => 'info', 2 => 'success', 3 => 'danger', 4 => 'warning', 5 => 'neutral'];
    $rowBadge = ['paid' => 'success', 'cheque held' => 'info', 'partly paid' => 'warning', 'overdue' => 'danger', 'due' => 'neutral'];
    $d = function ($v) { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '-'; };
    $editRows = old('due_date') ? collect(old('due_date'))->map(function ($dd, $k) { return (object) ['due_date' => $dd, 'amount' => old('due_amount')[$k] ?? '']; })
        : $pendingRows;
@endphp

<section class="lg-card" aria-labelledby="lcs_title">
  <div class="lg-card__head">
    <h2 class="lg-card__title" id="lcs_title">Settlement
      @if($settlement)<span class="lg-badge lg-badge--{{ $badge[$sStatus] }}"><span class="lg-dot"></span>{{ $LS::STATUS[$sStatus] }}</span>@endif
    </h2>
    <div class="lg-page-head__actions">
      @if($canReopen)
        <button type="button" class="lg-btn lg-btn--warning" data-toggle="modal" data-target="#lcsReopenModal"><i class="fa fa-repeat" aria-hidden="true"></i> Reopen as new case</button>
      @endif
      @if($canClose)
        <button type="button" class="lg-btn lg-btn--danger" data-toggle="modal" data-target="#lcsCloseModal">Close case</button>
      @endif
      @if($canEnterSettlement)
        <button type="button" class="lg-btn lg-btn--primary" data-toggle="modal" data-target="#lcsEnterModal">{{ $sStatus === 4 ? 'Correct settlement' : 'Record settlement' }}</button>
      @endif
      @if($canVerify)
        <button type="button" class="lg-btn lg-btn--primary" data-toggle="modal" data-target="#lcsVerifyModal">Verify settlement</button>
      @endif
    </div>
  </div>

  @if(!$settlement)
    <div class="lg-empty">
      <div class="lg-empty__title">No settlement yet</div>
      @if($canEnterSettlement)Record one when the tenant agrees to pay (out of court) or the court decides the amount (from Judgment on).@endif
    </div>
  @else
    <div class="lg-card__body">
      @if($sStatus === 4)
        <div class="lg-alert lg-alert--warning"><div><span class="lg-alert__title">Sent back by PLM:</span> {{ $settlement->sendback_reason }}</div></div>
      @elseif($sStatus === 0)
        <div class="lg-alert lg-alert--info"><div>Waiting for PLM to verify.</div></div>
      @elseif($sStatus === 3)
        <div class="lg-alert lg-alert--danger"><div><span class="lg-alert__title">Broken on {{ $d($settlement->broken_at) }}.</span> The case is active again; the lawyer can move it to any stage and record a new settlement.</div></div>
      @endif
      <dl class="lg-kv">
        <div><dt>Type</dt><dd>{{ $LS::TYPES[$settlement->settlement_type] }}</dd></div>
        <div><dt>{{ (int) $settlement->settlement_type === 2 ? 'Judgment date' : 'Agreed on' }}</dt><dd>{{ $d($settlement->agreed_date) }}</dd></div>
        <div><dt>Outstanding when entered</dt><dd class="tnum">{{ numberFormat($settlement->outstanding_at_entry) }}</dd></div>
        <div><dt>Settlement amount</dt><dd class="tnum">{{ numberFormat($settlement->amount) }}</dd></div>
        <div><dt>Waiver</dt><dd class="tnum">{{ numberFormat($settlement->waiver_amount) }}@if($settlement->waiver_cancelled_at) <span class="lg-badge lg-badge--danger">cancelled</span>@endif</dd></div>
        <div><dt>Fees recovered</dt><dd class="tnum">{{ numberFormat($settlement->fees_recovered) }}</dd></div>
        @if($settlement->waiver_reason)<div style="grid-column:1/-1;"><dt>Waiver reason</dt><dd style="font-weight:400;">{{ $settlement->waiver_reason }}</dd></div>@endif
      </dl>
    </div>
    <div class="lg-kpis">
      <div class="lg-kpi"><div class="lg-kpi__label">Total to collect</div><div class="lg-kpi__value">{{ numberFormat($settlement->settled_total) }}</div></div>
      @if($schedule)
      <div class="lg-kpi lg-kpi--good"><div class="lg-kpi__label">Collected</div><div class="lg-kpi__value">{{ numberFormat($schedule['collected']) }}</div></div>
      <div class="lg-kpi"><div class="lg-kpi__label">Cheques held</div><div class="lg-kpi__value">{{ numberFormat($schedule['held']) }}</div><div class="lg-kpi__hint">paid once the receipt is posted</div></div>
      <div class="lg-kpi {{ $schedule['balance'] > 0 ? '' : 'lg-kpi--good' }}"><div class="lg-kpi__label">Balance</div><div class="lg-kpi__value">{{ numberFormat($schedule['balance']) }}</div></div>
      @endif
    </div>
    <div class="lg-table-wrap" style="border-top:1px solid var(--lg-border);">
      <table class="lg-table">
        <caption class="sr-only">Installments</caption>
        <thead><tr><th scope="col">#</th><th scope="col">Due date</th><th scope="col" class="num">Amount</th>
          @if($schedule)<th scope="col" class="num">Paid</th><th scope="col" class="num">Cheque held</th><th scope="col">Status</th>@endif</tr></thead>
        <tbody>
          @if($schedule)
            @foreach($schedule['rows'] as $i => $r)
              <tr><td>{{ $i + 1 }}</td><td>{{ $d($r->due_date) }}</td><td class="num">{{ numberFormat($r->amount) }}</td>
                <td class="num">{{ numberFormat($r->paid) }}</td><td class="num">{{ $r->held > 0 ? numberFormat($r->held) : '' }}</td>
                <td><span class="lg-badge lg-badge--{{ $rowBadge[$r->status] }}">{{ ucfirst($r->status) }}@if($r->days_overdue) &middot; {{ $r->days_overdue }} days @endif</span></td></tr>
            @endforeach
          @else
            @foreach($pendingRows as $i => $r)
              <tr><td>{{ $i + 1 }}</td><td>{{ $d($r->due_date) }}</td><td class="num">{{ numberFormat($r->amount) }}</td></tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
    @if($schedule && count($schedule['cheques']))
      <div class="lg-card__foot">Cheques held:
        @foreach($schedule['cheques'] as $c) <span class="tnum">{{ $c->pdc_check_no }} ({{ $d($c->pdc_check_date) }}, {{ numberFormat($c->pdc_amt) }})</span>@if(!$loop->last), @endif @endforeach</div>
    @endif
  @endif
  @if(count($settlementHistory))
    <div class="lg-card__foot">Earlier settlements:
      @foreach($settlementHistory as $h) {{ $LS::TYPES[$h->settlement_type] }} <span class="tnum">{{ numberFormat($h->settled_total) }}</span> ({{ $LS::STATUS[$h->status] }}, {{ $d($h->agreed_date) }})@if(!$loop->last); @endif @endforeach</div>
  @endif
</section>

{{-- installment rows editor, shared by the lawyer and PLM forms --}}
@php
  $instEditor = function ($prefix, $rows) {
      return view('masters::Legal._installment_rows', ['prefix' => $prefix, 'rows' => $rows])->render();
  };
  $fromOld = old('_modal');
@endphp

@if($canEnterSettlement)
<div class="modal fade lg-modal" id="lcsEnterModal" tabindex="-1" role="dialog" aria-labelledby="lcsEnterTitle">
  <div class="modal-dialog" role="document" style="max-width:680px;">
    <div class="modal-content">
      <form method="POST" action="{{ route('legal.case.settlement', $legal->id) }}" enctype="multipart/form-data">
        {{ csrf_field() }}<input type="hidden" name="_modal" value="lcsEnterModal">
        <div class="modal-header"><h2 class="modal-title" id="lcsEnterTitle">{{ $sStatus === 4 ? 'Correct settlement' : 'Record settlement' }}</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
        <div class="modal-body">
          <div class="lg-form-grid">
            <div class="lg-field"><label for="lcs_type" class="lg-label">Type</label>
              <select id="lcs_type" name="settlement_type" class="lg-select" required>
                <option value="1" @if(old('settlement_type', $settlement->settlement_type ?? 1) == 1) selected @endif>Out of court (tenant agreed)</option>
                <option value="2" @if(old('settlement_type', $settlement->settlement_type ?? 1) == 2) selected @endif @if(!$judgmentReached) disabled @endif>Court decided{{ $judgmentReached ? '' : ' (needs Judgment stage)' }}</option>
              </select></div>
            <div class="lg-field"><label for="lcs_date" class="lg-label">Agreement / judgment date</label>
              <input type="date" id="lcs_date" name="agreed_date" class="lg-input" max="{{ date('Y-m-d') }}" required
                value="{{ old('agreed_date', $settlement && $sStatus === 4 ? substr($settlement->agreed_date, 0, 10) : ($legal->judgment_date ? substr($legal->judgment_date, 0, 10) : date('Y-m-d'))) }}"></div>
            <div class="lg-field"><label for="lcs_amount" class="lg-label">Settlement amount (tenant pays)</label>
              <input type="number" step="0.001" min="0.001" id="lcs_amount" name="amount" class="lg-input tnum lcs-total" data-target="lcs_enter" required
                value="{{ old('amount', $settlement && $sStatus === 4 ? $settlement->amount : ($legal->judgment_amount ?? '')) }}">
              <div class="lg-hint">Rent outstanding today: <b class="tnum">{{ numberFormat($outstanding) }}</b></div></div>
            <div class="lg-field span-all"><span class="lg-label">Installments <span class="muted" style="font-weight:400;">(one row for a single payment)</span></span>
              {!! $instEditor('lcs_enter', $fromOld === 'lcsEnterModal' ? $editRows : ($sStatus === 4 ? $pendingRows : collect())) !!}</div>
            <div class="lg-field span-all"><label for="lcs_note" class="lg-label">Note</label>
              <textarea id="lcs_note" name="note" class="lg-textarea" rows="3" maxlength="2000" required>{{ old('note', $sStatus === 4 ? $settlement->note : '') }}</textarea>
              <div class="lg-hint">Terms, who signed, court order details.</div></div>
            <div class="lg-field span-all"><label for="lcs_files" class="lg-label">Attach agreement / judgment copy</label>
              <input type="file" id="lcs_files" name="files[]" class="lg-input" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="lg-btn lg-btn--secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="lg-btn lg-btn--primary">Send to PLM</button></div>
      </form>
    </div>
  </div>
</div>
@endif

@if($canVerify)
@php $suggestWaiver = max(0, round((float) $settlement->outstanding_at_entry - (float) $settlement->amount, 3)); @endphp
<div class="modal fade lg-modal" id="lcsVerifyModal" tabindex="-1" role="dialog" aria-labelledby="lcsVerifyTitle">
  <div class="modal-dialog" role="document" style="max-width:700px;">
    <div class="modal-content">
      <form method="POST" action="{{ route('legal.case.settlement.verify', $legal->id) }}">
        {{ csrf_field() }}<input type="hidden" name="_modal" value="lcsVerifyModal">
        <div class="modal-header"><h2 class="modal-title" id="lcsVerifyTitle">Verify settlement</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
        <div class="modal-body">
          <div class="lg-kpis lg-kpis--card">
            <div class="lg-kpi"><div class="lg-kpi__label">{{ $LS::TYPES[$settlement->settlement_type] }}</div><div class="lg-kpi__value lg-kpi__value--sm">{{ $d($settlement->agreed_date) }}</div></div>
            <div class="lg-kpi"><div class="lg-kpi__label">Settlement amount</div><div class="lg-kpi__value lg-kpi__value--sm">{{ numberFormat($settlement->amount) }}</div></div>
            <div class="lg-kpi"><div class="lg-kpi__label">Outstanding when entered</div><div class="lg-kpi__value lg-kpi__value--sm">{{ numberFormat($settlement->outstanding_at_entry) }}</div></div>
          </div>
          <div class="lg-form-grid">
            <div class="lg-field"><label for="lcs_waiver" class="lg-label">Waiver amount</label>
              <input type="number" step="0.001" min="0" id="lcs_waiver" name="waiver_amount" class="lg-input tnum" value="{{ old('waiver_amount', $suggestWaiver) }}">
              <div class="lg-hint">Leaves every receivable once verified.</div></div>
            <div class="lg-field span-2"><label for="lcs_waiver_reason" class="lg-label">Waiver reason</label>
              <input type="text" id="lcs_waiver_reason" name="waiver_reason" class="lg-input" maxlength="500" value="{{ old('waiver_reason') }}">
              <div class="lg-hint">Needed when there is a waiver.</div></div>
            <div class="lg-field"><label for="lcs_fees" class="lg-label">Fees recovered from tenant</label>
              <input type="number" step="0.001" min="0" id="lcs_fees" name="fees_recovered" class="lg-input tnum" value="{{ old('fees_recovered', 0) }}"></div>
            <div class="lg-field span-2"><span class="lg-label">Total to collect</span>
              <div style="min-height:36px;display:flex;align-items:center;">settlement amount + fees = <b id="lcs_verify_total_label" class="tnum" style="margin-left:6px;">{{ numberFormat($settlement->amount) }}</b></div></div>
            <div class="lg-field span-all"><span class="lg-label">Installments <span class="muted" style="font-weight:400;">(change dates and amounts; they must add up to the total)</span></span>
              {!! $instEditor('lcs_verify', $fromOld === 'lcsVerifyModal' ? $editRows : $pendingRows) !!}</div>
            <div class="lg-field span-all"><label for="lcs_sendback" class="lg-label">Reason to send back</label>
              <input type="text" id="lcs_sendback" name="sendback_reason" class="lg-input" maxlength="1000">
              <div class="lg-hint">Only for Send back.</div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="decision" value="sendback" class="lg-btn lg-btn--warning" formnovalidate onclick="if (!document.getElementById('lcs_sendback').value.trim()) { alert('Enter the reason to send back.'); return false; }">Send back</button>
          <button type="submit" name="decision" value="verify" class="lg-btn lg-btn--primary">Verify</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@if($canClose)
@php $completed = $sStatus === 2; $verified = in_array($sStatus, [1, 2, 3], true); @endphp
<div class="modal fade lg-modal" id="lcsCloseModal" tabindex="-1" role="dialog" aria-labelledby="lcsCloseTitle">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('legal.case.close', $legal->id) }}">
        {{ csrf_field() }}<input type="hidden" name="_modal" value="lcsCloseModal">
        <div class="modal-header"><h2 class="modal-title" id="lcsCloseTitle">Close case</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
        <div class="modal-body">
          <div class="lg-field"><label for="lcs_close_reason" class="lg-label">Reason</label>
            <select id="lcs_close_reason" name="close_reason" class="lg-select" required>
              <option value="paid_in_full" @if(!$completed) disabled @endif>Settled - paid in full{{ $completed ? '' : ' (settlement not fully collected)' }}</option>
              <option value="written_off" @if(!$verified) disabled @endif @if($verified && !$completed) selected @endif>Settled - balance written off{{ $verified ? '' : ' (needs a verified settlement)' }}</option>
              <option value="withdrawn" @if($verified || $sStatus === 0) disabled @endif @if(!$verified && $sStatus !== 0) selected @endif>Withdrawn{{ ($verified || $sStatus === 0) ? ' (case has a settlement)' : '' }}</option>
            </select></div>
          <div class="lg-field"><label for="lcs_close_note" class="lg-label">Note</label>
            <textarea id="lcs_close_note" name="close_note" class="lg-textarea" rows="3" maxlength="1000"></textarea>
            <div class="lg-hint">Needed unless paid in full.</div></div>
          <div class="lg-alert lg-alert--info" style="margin:0;"><div>Closing takes the contract out of legal. The case can be reopened later as a new linked case.</div></div>
        </div>
        <div class="modal-footer"><button type="button" class="lg-btn lg-btn--secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="lg-btn lg-btn--danger-solid">Close case</button></div>
      </form>
    </div>
  </div>
</div>
@endif

@if($canReopen)
<div class="modal fade lg-modal" id="lcsReopenModal" tabindex="-1" role="dialog" aria-labelledby="lcsReopenTitle">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('legal.case.reopen', $legal->id) }}">
        {{ csrf_field() }}<input type="hidden" name="_modal" value="lcsReopenModal">
        <div class="modal-header"><h2 class="modal-title" id="lcsReopenTitle">Reopen as new case</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
        <div class="modal-body">
          <p>A new case is opened for this contract, linked to case #{{ $legal->id }}, and goes to PLM approval. The contract moves back into legal.</p>
          <div class="lg-field"><label for="lcs_reopen_reason" class="lg-label">Reason</label>
            <textarea id="lcs_reopen_reason" name="reopen_reason" class="lg-textarea" rows="3" maxlength="1000" required></textarea>
            <div class="lg-hint">For example: settlement cheque 000123 bounced.</div></div>
          @if($settlement && (float) $settlement->waiver_amount > 0 && !$settlement->waiver_cancelled_at)
          <label class="lg-check"><input type="checkbox" name="waiver_no_longer_applies" value="1" checked>
            <span>The waiver of <b class="tnum">{{ numberFormat($settlement->waiver_amount) }}</b> no longer applies (tenant broke the deal); the amount is owed again.</span></label>
          @endif
        </div>
        <div class="modal-footer"><button type="button" class="lg-btn lg-btn--secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="lg-btn lg-btn--warning">Reopen</button></div>
      </form>
    </div>
  </div>
</div>
@endif
