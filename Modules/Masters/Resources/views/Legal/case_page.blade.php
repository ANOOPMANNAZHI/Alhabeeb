@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

@section('content')
@php
    $LC = \Modules\Masters\Services\LegalCase::class;
    $contract = $legal->tenantContract;
    $code = (int) $legal->work_flow_processes_code;
    $state = $legal->closed_at ? 'Closed' : ($code === 801 ? 'Waiting for PLM approval' : ($code === 802 ? 'Waiting for lawyer approval' : ($code === 803 ? 'Active' : 'Referred back')));
    $stateTone = $legal->closed_at ? 'neutral' : ($code === 803 ? 'success' : ($code === 804 ? 'danger' : 'warning'));
    $currentOrder = $current ? (int) $current->sort_order : 0;
    $fmtDate = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-'; };
@endphp
<div class="lg">
  <div class="lg-crumbs">{{ Breadcrumbs::render('legal.case', $legal) }}</div>

  @php
    $lgModals = array_keys(array_filter(['lcpStageModal' => $canStage, 'lcsEnterModal' => $canEnterSettlement,
        'lcsVerifyModal' => $canVerify, 'lcsCloseModal' => $canClose, 'lcsReopenModal' => $canReopen]));
    $lgOrphanError = old('_modal') && !in_array(old('_modal'), $lgModals, true)
        && (session('modal_error') || $errors->getBag('modal')->any());
  @endphp
  @if($lgOrphanError)
    <div class="lg-alert lg-alert--danger" role="alert"><div>{{ session('modal_error') ?: implode(' ', $errors->getBag('modal')->all()) }}</div></div>
  @endif
  <header class="lg-page-head">
    <div>
      <h1 class="lg-page-head__title">{{ $contract->tenant_contract_no ?? '' }} &middot; {{ $contract->tenant->tenant_name ?? '' }}</h1>
      <div class="lg-page-head__sub">
        <span>{{ $legal->building->building_name ?? '' }}</span><span class="sep">/</span>
        <span>Unit {{ $legal->unit->unit_code ?? '' }}</span><span class="sep">/</span>
        <span>Case #{{ $legal->id }}</span>
        <span class="lg-badge lg-badge--{{ $stateTone }}"><span class="lg-dot"></span>{{ $state }}</span>
      </div>
    </div>
    <div class="lg-page-head__actions">
      @if($code === 801 && !$legal->closed_at)
        <a class="lg-btn lg-btn--secondary" href="{{ route('plmsApprovalShow', $legal->id) }}">PLM approval</a>
      @elseif($code === 802 && !$legal->closed_at)
        <a class="lg-btn lg-btn--secondary" href="{{ route('lawyerApprovalShow', $legal->id) }}">Lawyer approval</a>
      @elseif($code === 803)
        <a class="lg-btn lg-btn--secondary" href="{{ route('activeCasesShow', $legal->id) }}">Contract &amp; notes</a>
      @endif
      @if($canStage)
        <button type="button" class="lg-btn lg-btn--primary" data-toggle="modal" data-target="#lcpStageModal"><i class="fa fa-flag-o" aria-hidden="true"></i> Update stage</button>
      @endif
    </div>
  </header>

  <section class="lg-card" aria-labelledby="lcp_overview">
    <div class="lg-card__head"><h2 class="lg-card__title" id="lcp_overview">Overview</h2></div>
    <div class="lg-card__body">
      @include('masters::Legal._case_summary', ['hideOutstanding' => true])
      <dl class="lg-kv" style="margin-top:16px;">
        <div><dt>Court case no.</dt><dd>{{ $legal->court_case_no ?: '-' }}</dd></div>
        <div><dt>Court / committee</dt><dd>{{ $legal->court_name ?: '-' }}</dd></div>
        <div><dt>Filed on</dt><dd>{{ $fmtDate($legal->filed_date) }}</dd></div>
        <div><dt>Judgment date</dt><dd>{{ $fmtDate($legal->judgment_date) }}
          @if($legal->eviction_ordered !== null)<span class="muted" style="font-weight:400;"> &middot; eviction {{ $legal->eviction_ordered ? 'ordered' : 'not ordered' }}</span>@endif</dd></div>
      </dl>
    </div>
    <div class="lg-kpis">
      <div class="lg-kpi"><div class="lg-kpi__label">Claim amount</div><div class="lg-kpi__value">{{ $legal->claim_amount !== null ? numberFormat($legal->claim_amount) : '-' }}</div></div>
      <div class="lg-kpi"><div class="lg-kpi__label">Rent outstanding today</div><div class="lg-kpi__value">{{ numberFormat($outstanding) }}</div><div class="lg-kpi__hint">net of receipts and waivers</div></div>
      <div class="lg-kpi"><div class="lg-kpi__label">Judgment amount</div><div class="lg-kpi__value">{{ $legal->judgment_amount !== null ? numberFormat($legal->judgment_amount) : '-' }}</div></div>
      <div class="lg-kpi"><div class="lg-kpi__label">Next hearing</div><div class="lg-kpi__value">{{ $fmtDate($legal->next_hearing_date) }}</div>
        @if($legal->next_hearing_date)<div class="lg-kpi__hint">{{ \Carbon\Carbon::parse($legal->next_hearing_date)->diffForHumans() }}</div>@endif</div>
    </div>
  </section>

  <section class="lg-card" aria-labelledby="lcp_stages">
    <div class="lg-card__head">
      <h2 class="lg-card__title" id="lcp_stages">Stages @if($current)<span class="lg-count">at {{ $current->name }}</span>@endif</h2>
    </div>
    @if($current && $current->code === 'eviction' && $contract && (int) $contract->tenant_contract_status === 1)
      <div style="padding:12px 16px 0;"><div class="lg-alert lg-alert--warning" style="margin:0;">
        <div>The unit is handed back but the contract is still active. Start the tenant termination so the unit becomes vacant.
          <a href="{{ route('tenantTermination.create') }}">Start termination</a></div></div></div>
    @endif
    <ol class="lg-stepper">
      @forelse($stages as $i => $s)
        @php $cls = $current && $s->id === $current->id ? 'current' : ((int) $s->sort_order < $currentOrder ? 'done' : 'todo'); @endphp
        <li class="lg-step lg-step--{{ $cls }}" @if($cls === 'current') aria-current="step" @endif>
          <span class="lg-step__dot" aria-hidden="true"></span><span class="lg-step__name">{{ $s->name }}</span>
        </li>
      @empty
        <li class="muted">No stages set up.</li>
      @endforelse
    </ol>
    @if(!$current && $code === 803 && !$legal->closed_at)
      <div class="lg-card__foot">Stage not set yet{{ $canStage ? ' - use Update stage.' : '.' }}</div>
    @endif
  </section>

  @include('masters::Legal._settlement')

  <div class="lg-grid">
    <section class="lg-card" aria-label="Case timeline">
      @include('masters::Legal._timeline')
    </section>
    <aside>
      @if($canComment)
      <section class="lg-card" aria-labelledby="lcp_comment_title">
        <div class="lg-card__head"><h2 class="lg-card__title" id="lcp_comment_title">Add comment</h2></div>
        <div class="lg-card__body">
          <form method="POST" action="{{ route('legal.case.comment', $legal->id) }}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="lg-field">
              <label for="lcp_comment" class="lg-label">Comment</label>
              <textarea id="lcp_comment" name="comment" class="lg-textarea" rows="4" maxlength="2000" required>{{ old('comment') }}</textarea>
              <div class="lg-hint">What happened and what is next.</div>
            </div>
            <div class="lg-field">
              <label for="lcp_comment_files" class="lg-label">Attach files</label>
              <input type="file" id="lcp_comment_files" name="files[]" class="lg-input" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
              <div class="lg-hint">PDF, Word or images, up to 10 MB each.</div>
            </div>
            <button type="submit" class="lg-btn lg-btn--primary">Add comment</button>
          </form>
        </div>
      </section>
      @endif
      @if(isset($docs[0]) && count($docs[0]))
      <section class="lg-card" aria-labelledby="lcp_docs_title">
        <div class="lg-card__head"><h2 class="lg-card__title" id="lcp_docs_title">Earlier documents</h2></div>
        <div class="lg-card__body lg-timeline__files" style="margin:0;">
          @foreach($docs[0] as $doc)
            <a class="lg-file" target="_blank" rel="noopener" href="{{ asset('storage/app/' . $doc->legal_documents_file_name) }}"><i class="fa fa-paperclip" aria-hidden="true"></i> {{ $doc->legal_documents_name }}</a>
          @endforeach
        </div>
      </section>
      @endif
      @if(count($related))
      <section class="lg-card" aria-labelledby="lcp_related_title">
        <div class="lg-card__head"><h2 class="lg-card__title" id="lcp_related_title">Other cases on this contract</h2></div>
        <div class="lg-card__body">
          @foreach($related as $r)
            <div style="display:flex;justify-content:space-between;gap:8px;padding:4px 0;">
              <a href="{{ $LC::screenUrl($r) }}">Case #{{ $r->id }}</a>
              <span class="lg-badge lg-badge--{{ $r->closed_at ? 'neutral' : 'info' }}">{{ $r->closed_at ? 'Closed ' . \Carbon\Carbon::parse($r->closed_at)->format('d/m/Y') : 'Open' }}</span>
            </div>
          @endforeach
        </div>
      </section>
      @endif
    </aside>
  </div>

@if($canStage)
<div class="modal fade lg-modal" id="lcpStageModal" tabindex="-1" role="dialog" aria-labelledby="lcpStageTitle">
  <div class="modal-dialog" role="document" style="max-width:620px;">
    <div class="modal-content">
      <form method="POST" action="{{ route('legal.case.stage', $legal->id) }}" enctype="multipart/form-data">
        {{ csrf_field() }}<input type="hidden" name="_modal" value="lcpStageModal">
        <div class="modal-header">
          <h2 class="modal-title" id="lcpStageTitle">Update stage</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
          @include('masters::Legal._modal_errors', ['modalId' => 'lcpStageModal'])
          <div class="lg-form-grid">
            <div class="lg-field span-2">
              <label for="lcp_stage" class="lg-label">Stage</label>
              <select id="lcp_stage" name="stage_id" class="lg-select" required>
                @foreach($stages as $s)
                  @if((int) $s->sort_order >= $currentOrder)
                    <option value="{{ $s->id }}" data-code="{{ $s->code }}" @if(old('stage_id') == $s->id) selected @endif>{{ $s->name }}{{ $current && $s->id === $current->id ? ' (current)' : '' }}</option>
                  @endif
                @endforeach
              </select>
              <div class="lg-hint">A stage can repeat (another hearing) but cannot go back.</div>
            </div>
            <div class="lg-field">
              <label for="lcp_event_date" class="lg-label">Date</label>
              <input type="date" id="lcp_event_date" name="event_date" class="lg-input" max="{{ date('Y-m-d') }}" value="{{ old('event_date', date('Y-m-d')) }}" required>
            </div>

            <div class="lg-field lcp-f lcp-f-filed lcp-hidden">
              <label for="lcp_case_no" class="lg-label">Court case no.</label>
              <input type="text" id="lcp_case_no" name="court_case_no" class="lg-input" maxlength="100" value="{{ old('court_case_no', $legal->court_case_no) }}">
            </div>
            <div class="lg-field lcp-f lcp-f-filed lcp-hidden">
              <label for="lcp_court" class="lg-label">Court / committee</label>
              <input type="text" id="lcp_court" name="court_name" class="lg-input" maxlength="150" value="{{ old('court_name', $legal->court_name) }}">
            </div>
            <div class="lg-field lcp-f lcp-f-filed lcp-hidden">
              <label for="lcp_claim" class="lg-label">Claim amount</label>
              <input type="number" step="0.001" min="0" id="lcp_claim" name="claim_amount" class="lg-input tnum" value="{{ old('claim_amount', $legal->claim_amount !== null ? $legal->claim_amount : round($outstanding, 3)) }}">
              <div class="lg-hint">Defaults to today's outstanding.</div>
            </div>

            <div class="lg-field lcp-f lcp-f-judgment lcp-hidden">
              <label for="lcp_judgment" class="lg-label">Amount awarded</label>
              <input type="number" step="0.001" min="0" id="lcp_judgment" name="judgment_amount" class="lg-input tnum" value="{{ old('judgment_amount', $legal->judgment_amount) }}">
            </div>
            @if((int) $legal->case_type === 1)
            <div class="lg-field lcp-f lcp-f-judgment lcp-hidden">
              <label for="lcp_eviction" class="lg-label">Eviction ordered?</label>
              <select id="lcp_eviction" name="eviction_ordered" class="lg-select">
                <option value="">Select</option>
                <option value="1" @if(old('eviction_ordered') === '1') selected @endif>Yes</option>
                <option value="0" @if(old('eviction_ordered') === '0') selected @endif>No</option>
              </select>
            </div>
            @endif

            <div class="span-all lcp-f lcp-f-eviction lcp-hidden">
              <div class="lg-alert lg-alert--info"><div>The case becomes a recovery case (vacated / terminated). Start the tenant termination for the unit afterwards.</div></div>
            </div>

            <div class="lg-field">
              <label for="lcp_next" class="lg-label">Next hearing (optional)</label>
              <input type="date" id="lcp_next" name="next_hearing_date" class="lg-input" min="{{ date('Y-m-d') }}" value="{{ old('next_hearing_date') }}">
            </div>
            <div class="lg-field span-all">
              <label for="lcp_note" class="lg-label">Note</label>
              <textarea id="lcp_note" name="note" class="lg-textarea" rows="3" maxlength="2000" required>{{ old('note') }}</textarea>
            </div>
            <div class="lg-field span-all">
              <label for="lcp_stage_files" class="lg-label">Attach files</label>
              <input type="file" id="lcp_stage_files" name="files[]" class="lg-input" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
              <div class="lg-hint">Notice, filing receipt, judgment copy...</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="lg-btn lg-btn--secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="lg-btn lg-btn--primary">Save stage</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
</div>
<style>.lg .lcp-hidden { display: none; }</style>
@endsection

@section('scripts')
<script>
  (function () {
    @if($canStage)
    function stageFields() {
      var code = $('#lcp_stage option:selected').data('code') || '';
      $('.lcp-f').addClass('lcp-hidden');
      $('.lcp-f-' + code).removeClass('lcp-hidden');
      $('#lcp_case_no').prop('required', code === 'filed');
      $('#lcp_judgment, #lcp_eviction').prop('required', code === 'judgment');
    }
    $('#lcp_stage').on('change', stageFields);
    stageFields();
    @endif

    // installment editors: add / remove rows, running total against the amount to match
    function target(box) {
      if (box.attr('id') === 'lcs_enter_rows') { return parseFloat($('#lcs_amount').val()) || 0; }
      return {{ isset($settlement) && $settlement ? (float) $settlement->amount : 0 }} + (parseFloat($('#lcs_fees').val()) || 0);
    }
    function sum(box) {
      var t = 0;
      box.find('.lcs-inst-amount').each(function () { t += parseFloat($(this).val()) || 0; });
      var want = target(box), ok = Math.abs(t - want) < 0.0005;
      box.find('.lcs-inst-sum').text('Installments: ' + t.toFixed(3) + ' of ' + want.toFixed(3) + (ok ? '' : ' - must match'))
        .css('color', ok ? '#1e6b34' : '#b42318');
      $('#lcs_verify_total_label').text(target($('#lcs_verify_rows')).toFixed(3));
    }
    // a single installment row follows the amount until the user types in it (user, 18/10/2026)
    function money(v) { return v > 0.0005 ? v.toFixed(3) : ''; }
    function follow(box) {
      var rows = box.find('tbody tr');
      if (rows.length === 1 && !box.data('manual')) { rows.find('.lcs-inst-amount').val(money(target(box))); }
    }
    $(document).on('click', '.lcs-inst-add', function () {
      var box = $(this).closest('.lcs-inst'), row = box.find('tbody tr:first').clone(), t = 0;
      box.find('.lcs-inst-amount').each(function () { t += parseFloat($(this).val()) || 0; });
      row.find('input').val('');
      row.find('.lcs-inst-amount').val(money(target(box) - t)); // the new row takes what is still left
      box.find('tbody').append(row); sum(box);
    });
    $(document).on('click', '.lcs-inst-del', function () {
      var box = $(this).closest('.lcs-inst');
      if (box.find('tbody tr').length > 1) { $(this).closest('tr').remove(); } else { $(this).closest('tr').find('input').val(''); }
      sum(box);
    });
    $(document).on('input', '.lcs-inst-amount', function () { $(this).closest('.lcs-inst').data('manual', true); });
    $(document).on('input change', '.lcs-inst-amount, #lcs_amount, #lcs_fees', function () {
      $('.lcs-inst').each(function () { if (!$(this).find('.lcs-inst-amount').is(':focus')) { follow($(this)); } sum($(this)); });
    });
    $('.lcs-inst').each(function () {
      var box = $(this), one = box.find('.lcs-inst-amount');
      // rows brought back from a failed save or an earlier entry stay as they are
      if (one.length !== 1 || (one.val() !== '' && Math.abs((parseFloat(one.val()) || 0) - target(box)) >= 0.0005)) { box.data('manual', true); }
      follow(box); sum(box);
    });

    var reopen = @json(old('_modal'));
    if (reopen) { $('#' + reopen).modal('show'); }
  })();
</script>
@endsection
