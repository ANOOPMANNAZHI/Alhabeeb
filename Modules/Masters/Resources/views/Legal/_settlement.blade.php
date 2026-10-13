{{-- Settlement section of the legal case page (phase 3). --}}
@php
    $LS = \Modules\Masters\Services\LegalSettlement::class;
    $sStatus = $settlement ? (int) $settlement->status : null;
    $badge = [0 => 'lcs-amber', 1 => 'lcs-blue', 2 => 'lcs-green', 3 => 'lcs-red', 4 => 'lcs-amber', 5 => 'lcs-grey'];
    $rowBadge = ['paid' => 'lcs-green', 'cheque held' => 'lcs-blue', 'partly paid' => 'lcs-amber', 'overdue' => 'lcs-red', 'due' => 'lcs-grey'];
    $d = function ($v) { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '-'; };
    $editRows = old('due_date') ? collect(old('due_date'))->map(function ($dd, $k) { return (object) ['due_date' => $dd, 'amount' => old('due_amount')[$k] ?? '']; })
        : $pendingRows;
@endphp
<style>
  .lcs-badge { display:inline-block; padding:2px 9px; border-radius:10px; font-size:12px; font-weight:600; }
  .lcs-green { background:#e6f4ea; color:#1e6b34; } .lcs-blue { background:#e8f3ff; color:#1d5fa8; } .lcs-amber { background:#fff4e0; color:#a15c07; }
  .lcs-red { background:#fde8e8; color:#b42318; } .lcs-grey { background:#eceff3; color:#4a5565; }
  .lcs-actions { display:flex; flex-wrap:wrap; gap:8px; }
  .lcs-note { margin:0 0 12px; padding:10px 14px; border-radius:4px; background:#fff8e6; border-left:4px solid #e0a100; color:#5c4300; }
  .lcs-rows td, .lcs-rows th { padding:6px 8px !important; }
  .lcs-inst input { min-width:0; }
</style>

<div class="card card-box">
  <div class="card-head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
    <div class="col" style="flex:1 1 auto;"><h4>Settlement
      @if($settlement)<span class="lcs-badge {{ $badge[$sStatus] }}">{{ $LS::STATUS[$sStatus] }}</span>@endif</h4></div>
    <div class="lcs-actions" style="padding-right:16px;">
      @if($canEnterSettlement)
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#lcsEnterModal">{{ $sStatus === 4 ? 'Correct settlement' : 'Record settlement' }}</button>
      @endif
      @if($canVerify)
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#lcsVerifyModal">Verify settlement</button>
      @endif
      @if($canClose)
        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#lcsCloseModal">Close case</button>
      @endif
      @if($canReopen)
        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#lcsReopenModal">Reopen as new case</button>
      @endif
    </div>
  </div>
  <div class="card-body">
    @if(!$settlement)
      <p class="text-muted" style="margin:0;">No settlement yet.
        @if($canEnterSettlement) Record one when the tenant agrees to pay (out of court) or the court decides the amount (at Judgment).@endif</p>
    @else
      @if($sStatus === 4)
        <div class="lcs-note"><b>Sent back by PLM:</b> {{ $settlement->sendback_reason }}</div>
      @elseif($sStatus === 0)
        <div class="lcs-note">Waiting for PLM to verify.</div>
      @elseif($sStatus === 3)
        <div class="lcs-note">Broken on {{ $d($settlement->broken_at) }}. The case is active again; the lawyer can move it to any stage and record a new settlement.</div>
      @endif
      <div class="lcp-grid" style="margin:0 -20px 14px;">
        <div><div class="k">Type</div><div class="v">{{ $LS::TYPES[$settlement->settlement_type] }}</div></div>
        <div><div class="k">{{ (int) $settlement->settlement_type === 2 ? 'Judgment date' : 'Agreed on' }}</div><div class="v">{{ $d($settlement->agreed_date) }}</div></div>
        <div><div class="k">Outstanding when entered</div><div class="v">{{ numberFormat($settlement->outstanding_at_entry) }}</div></div>
        <div><div class="k">Settlement amount</div><div class="v">{{ numberFormat($settlement->amount) }}</div></div>
        <div><div class="k">Waiver</div><div class="v">{{ numberFormat($settlement->waiver_amount) }}@if($settlement->waiver_cancelled_at) <small class="text-danger">(cancelled)</small>@endif</div></div>
        <div><div class="k">Fees recovered</div><div class="v">{{ numberFormat($settlement->fees_recovered) }}</div></div>
        <div><div class="k">Total to collect</div><div class="v">{{ numberFormat($settlement->settled_total) }}</div></div>
        @if($schedule)
        <div><div class="k">Collected</div><div class="v">{{ numberFormat($schedule['collected']) }}</div></div>
        <div><div class="k">Cheques held</div><div class="v">{{ numberFormat($schedule['held']) }}</div></div>
        <div><div class="k">Balance</div><div class="v">{{ numberFormat($schedule['balance']) }}</div></div>
        @endif
      </div>
      @if($settlement->waiver_reason)<p><b>Waiver reason:</b> {{ $settlement->waiver_reason }}</p>@endif
      <div class="table-responsive">
        <table class="table lcs-rows">
          <thead><tr><th>#</th><th>Due date</th><th class="text-right">Amount</th>@if($schedule)<th class="text-right">Paid</th><th class="text-right">Cheque held</th><th>Status</th>@endif</tr></thead>
          <tbody>
            @if($schedule)
              @foreach($schedule['rows'] as $i => $r)
                <tr><td>{{ $i + 1 }}</td><td>{{ $d($r->due_date) }}</td><td class="text-right">{{ numberFormat($r->amount) }}</td>
                  <td class="text-right">{{ numberFormat($r->paid) }}</td><td class="text-right">{{ $r->held > 0 ? numberFormat($r->held) : '' }}</td>
                  <td><span class="lcs-badge {{ $rowBadge[$r->status] }}">{{ ucfirst($r->status) }}@if($r->days_overdue) &middot; {{ $r->days_overdue }} days @endif</span></td></tr>
              @endforeach
            @else
              @foreach($pendingRows as $i => $r)
                <tr><td>{{ $i + 1 }}</td><td>{{ $d($r->due_date) }}</td><td class="text-right">{{ numberFormat($r->amount) }}</td></tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>
      @if($schedule && count($schedule['cheques']))
        <p class="text-muted" style="font-size:12px;">Cheques held (count as paid once their receipt is posted):
          @foreach($schedule['cheques'] as $c) {{ $c->pdc_check_no }} ({{ $d($c->pdc_check_date) }}, {{ numberFormat($c->pdc_amt) }})@if(!$loop->last), @endif @endforeach</p>
      @endif
    @endif
    @if(count($settlementHistory))
      <p class="text-muted" style="font-size:12px;margin:8px 0 0;">Earlier settlements:
        @foreach($settlementHistory as $h) {{ $LS::TYPES[$h->settlement_type] }} {{ numberFormat($h->settled_total) }} ({{ $LS::STATUS[$h->status] }}, {{ $d($h->agreed_date) }})@if(!$loop->last); @endif @endforeach</p>
    @endif
  </div>
</div>

{{-- installment rows editor, shared by the lawyer and PLM forms --}}
@php
  $instEditor = function ($prefix, $rows) {
      return view('masters::Legal._installment_rows', ['prefix' => $prefix, 'rows' => $rows])->render();
  };
  $fromOld = old('_modal');
@endphp

@if($canEnterSettlement)
<div class="modal fade" id="lcsEnterModal" tabindex="-1" role="dialog" aria-labelledby="lcsEnterTitle">
  <div class="modal-dialog" role="document" style="max-width:680px;">
    <div class="modal-content">
      <form method="POST" action="{{ route('legal.case.settlement', $legal->id) }}" enctype="multipart/form-data">
        {{ csrf_field() }}<input type="hidden" name="_modal" value="lcsEnterModal">
        <div class="modal-header"><h4 class="modal-title" id="lcsEnterTitle">{{ $sStatus === 4 ? 'Correct settlement' : 'Record settlement' }}</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-6"><div class="form-group"><label for="lcs_type">Type</label>
              <select id="lcs_type" name="settlement_type" class="form-control" required>
                <option value="1" @if(old('settlement_type', $settlement->settlement_type ?? 1) == 1) selected @endif>Out of court (tenant agreed)</option>
                <option value="2" @if(old('settlement_type', $settlement->settlement_type ?? 1) == 2) selected @endif @if(!$judgmentReached) disabled @endif>Court decided{{ $judgmentReached ? '' : ' (needs Judgment stage)' }}</option>
              </select></div></div>
            <div class="col-sm-6"><div class="form-group"><label for="lcs_date">Agreement / judgment date</label>
              <input type="date" id="lcs_date" name="agreed_date" class="form-control" max="{{ date('Y-m-d') }}" required
                value="{{ old('agreed_date', $settlement && $sStatus === 4 ? substr($settlement->agreed_date, 0, 10) : ($legal->judgment_date ? substr($legal->judgment_date, 0, 10) : date('Y-m-d'))) }}"></div></div>
            <div class="col-sm-6"><div class="form-group"><label for="lcs_amount">Settlement amount (tenant pays)</label>
              <input type="number" step="0.001" min="0.001" id="lcs_amount" name="amount" class="form-control lcs-total" data-target="lcs_enter" required
                value="{{ old('amount', $settlement && $sStatus === 4 ? $settlement->amount : ($legal->judgment_amount ?? '')) }}"></div></div>
            <div class="col-sm-6"><p class="text-muted" style="margin-top:30px;">Rent outstanding today: <b>{{ numberFormat($outstanding) }}</b></p></div>
            <div class="col-sm-12"><label>Installments (one row for a single payment)</label>{!! $instEditor('lcs_enter', $fromOld === 'lcsEnterModal' ? $editRows : ($sStatus === 4 ? $pendingRows : collect())) !!}</div>
            <div class="col-sm-12"><div class="form-group"><label for="lcs_note">Note (terms, who signed, court order details)</label>
              <textarea id="lcs_note" name="note" class="form-control" rows="3" maxlength="2000" required>{{ old('note', $sStatus === 4 ? $settlement->note : '') }}</textarea></div></div>
            <div class="col-sm-12"><div class="form-group"><label for="lcs_files">Attach agreement / judgment copy</label>
              <input type="file" id="lcs_files" name="files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"></div></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Send to PLM</button></div>
      </form>
    </div>
  </div>
</div>
@endif

@if($canVerify)
@php $suggestWaiver = max(0, round((float) $settlement->outstanding_at_entry - (float) $settlement->amount, 3)); @endphp
<div class="modal fade" id="lcsVerifyModal" tabindex="-1" role="dialog" aria-labelledby="lcsVerifyTitle">
  <div class="modal-dialog" role="document" style="max-width:680px;">
    <div class="modal-content">
      <form method="POST" action="{{ route('legal.case.settlement.verify', $legal->id) }}">
        {{ csrf_field() }}<input type="hidden" name="_modal" value="lcsVerifyModal">
        <div class="modal-header"><h4 class="modal-title" id="lcsVerifyTitle">Verify settlement</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
        <div class="modal-body">
          <p>{{ $LS::TYPES[$settlement->settlement_type] }}: <b>{{ numberFormat($settlement->amount) }}</b> on {{ $d($settlement->agreed_date) }}.
            Outstanding when entered: <b>{{ numberFormat($settlement->outstanding_at_entry) }}</b>.</p>
          <div class="row">
            <div class="col-sm-4"><div class="form-group"><label for="lcs_waiver">Waiver amount</label>
              <input type="number" step="0.001" min="0" id="lcs_waiver" name="waiver_amount" class="form-control" value="{{ old('waiver_amount', $suggestWaiver) }}"></div></div>
            <div class="col-sm-8"><div class="form-group"><label for="lcs_waiver_reason">Waiver reason (needed when there is a waiver)</label>
              <input type="text" id="lcs_waiver_reason" name="waiver_reason" class="form-control" maxlength="500" value="{{ old('waiver_reason') }}"></div></div>
            <div class="col-sm-4"><div class="form-group"><label for="lcs_fees">Fees recovered from tenant</label>
              <input type="number" step="0.001" min="0" id="lcs_fees" name="fees_recovered" class="form-control" value="{{ old('fees_recovered', 0) }}"></div></div>
            <div class="col-sm-8"><p class="text-muted" style="margin-top:30px;">Total to collect = settlement amount + fees = <b id="lcs_verify_total_label">{{ numberFormat($settlement->amount) }}</b></p></div>
            <div class="col-sm-12"><label>Installments (you can change dates and amounts; they must add up to the total)</label>{!! $instEditor('lcs_verify', $fromOld === 'lcsVerifyModal' ? $editRows : $pendingRows) !!}</div>
            <div class="col-sm-12"><div class="form-group"><label for="lcs_sendback">Reason to send back (only for Send back)</label>
              <input type="text" id="lcs_sendback" name="sendback_reason" class="form-control" maxlength="1000"></div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="decision" value="sendback" class="btn btn-warning" formnovalidate onclick="if (!document.getElementById('lcs_sendback').value.trim()) { alert('Enter the reason to send back.'); return false; }">Send back</button>
          <button type="submit" name="decision" value="verify" class="btn btn-primary">Verify</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@if($canClose)
@php $completed = $sStatus === 2; $verified = in_array($sStatus, [1, 2, 3], true); @endphp
<div class="modal fade" id="lcsCloseModal" tabindex="-1" role="dialog" aria-labelledby="lcsCloseTitle">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('legal.case.close', $legal->id) }}">
        {{ csrf_field() }}<input type="hidden" name="_modal" value="lcsCloseModal">
        <div class="modal-header"><h4 class="modal-title" id="lcsCloseTitle">Close case</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
        <div class="modal-body">
          <div class="form-group"><label for="lcs_close_reason">Reason</label>
            <select id="lcs_close_reason" name="close_reason" class="form-control" required>
              <option value="paid_in_full" @if(!$completed) disabled @endif>Settled - paid in full{{ $completed ? '' : ' (settlement not fully collected)' }}</option>
              <option value="written_off" @if(!$verified) disabled @endif @if($verified && !$completed) selected @endif>Settled - balance written off{{ $verified ? '' : ' (needs a verified settlement)' }}</option>
              <option value="withdrawn" @if($verified || $sStatus === 0) disabled @endif @if(!$verified && $sStatus !== 0) selected @endif>Withdrawn{{ ($verified || $sStatus === 0) ? ' (case has a settlement)' : '' }}</option>
            </select></div>
          <div class="form-group"><label for="lcs_close_note">Note (needed unless paid in full)</label>
            <textarea id="lcs_close_note" name="close_note" class="form-control" rows="3" maxlength="1000"></textarea></div>
          <p class="text-muted" style="font-size:12px;">Closing takes the contract out of legal. The case can be reopened later as a new linked case.</p>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Close case</button></div>
      </form>
    </div>
  </div>
</div>
@endif

@if($canReopen)
<div class="modal fade" id="lcsReopenModal" tabindex="-1" role="dialog" aria-labelledby="lcsReopenTitle">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('legal.case.reopen', $legal->id) }}">
        {{ csrf_field() }}<input type="hidden" name="_modal" value="lcsReopenModal">
        <div class="modal-header"><h4 class="modal-title" id="lcsReopenTitle">Reopen as new case</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
        <div class="modal-body">
          <p>A new case is opened for this contract, linked to case #{{ $legal->id }}, and goes to PLM approval. The contract moves back into legal.</p>
          <div class="form-group"><label for="lcs_reopen_reason">Reason</label>
            <textarea id="lcs_reopen_reason" name="reopen_reason" class="form-control" rows="3" maxlength="1000" required placeholder="e.g. settlement cheque 000123 bounced"></textarea></div>
          @if($settlement && (float) $settlement->waiver_amount > 0 && !$settlement->waiver_cancelled_at)
          <div class="checkbox"><label><input type="checkbox" name="waiver_no_longer_applies" value="1" checked>
            The waiver of {{ numberFormat($settlement->waiver_amount) }} no longer applies (tenant broke the deal)</label></div>
          @endif
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Reopen</button></div>
      </form>
    </div>
  </div>
</div>
@endif
