@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
@php
    $LC = \Modules\Masters\Services\LegalCase::class;
    $contract = $legal->tenantContract;
    $code = (int) $legal->work_flow_processes_code;
    $state = $legal->closed_at ? 'Closed' : ($code === 801 ? 'Waiting for PLM approval' : ($code === 802 ? 'Waiting for lawyer approval' : ($code === 803 ? 'Active' : 'Referred back')));
    $currentOrder = $current ? (int) $current->sort_order : 0;
    $fmtDate = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-'; };
@endphp
<style>
  .lcp-head { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; padding:16px 20px; border-bottom:1px solid #eef1f5; }
  .lcp-head h3 { margin:0; font-size:20px; color:#1f2d3d; }
  .lcp-head .lcp-sub { color:#5b6b7f; font-size:13px; margin-top:4px; }
  .lcp-state { display:inline-block; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; background:#e8f3ff; color:#1d5fa8; }
  .lcp-state.closed { background:#eceff3; color:#4a5565; }
  .lcp-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:1px; background:#eef1f5; border-top:1px solid #eef1f5; }
  .lcp-grid > div { background:#fff; padding:12px 20px; }
  .lcp-grid .k { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#7a8699; }
  .lcp-grid .v { font-size:15px; font-weight:600; color:#1f2d3d; margin-top:2px; }
  .lcp-steps { display:flex; flex-wrap:wrap; gap:6px; padding:16px 20px; }
  .lcp-step { flex:1 1 120px; min-width:110px; padding:8px 10px; border-radius:4px; font-size:12px; border:1px solid #dfe5ec; color:#7a8699; background:#fafbfc; }
  .lcp-step.done { border-color:#b8d4f0; color:#1d5fa8; background:#f0f6ff; }
  .lcp-step.current { border-color:#1d5fa8; background:#1d5fa8; color:#fff; font-weight:600; }
  .lcp-step .n { display:block; font-size:10px; opacity:.8; }
  .lcp-section-title { font-size:15px; font-weight:600; color:#1f2d3d; padding:14px 20px 0; }
  .lcp-prompt { margin:12px 20px 0; padding:10px 14px; border-radius:4px; background:#fff8e6; border-left:4px solid #e0a100; color:#5c4300; }
  .lcp-hidden { display:none; }
  @media (max-width: 600px) { .lcp-head, .lcp-grid > div, .lcp-steps { padding-left:12px; padding-right:12px; } }
</style>

<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left"><div class="page-title">Legal Case</div></div>
    {{ Breadcrumbs::render('legal.case', $legal) }}
  </div>
</div>

<div class="card card-box">
  <div class="lcp-head">
    <div>
      <h3>{{ $contract->tenant_contract_no ?? '' }} &middot; {{ $contract->tenant->tenant_name ?? '' }}</h3>
      <div class="lcp-sub">{{ $legal->building->building_name ?? '' }} &middot; Unit {{ $legal->unit->unit_code ?? '' }} &middot; Case #{{ $legal->id }}
        &nbsp;<span class="lcp-state {{ $legal->closed_at ? 'closed' : '' }}">{{ $state }}</span></div>
    </div>
    <div>
      @if($canStage)
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#lcpStageModal">Update stage</button>
      @endif
      @if($code === 801 && !$legal->closed_at)
        <a class="btn btn-default" href="{{ route('plmsApprovalShow', $legal->id) }}">PLM approval</a>
      @elseif($code === 802 && !$legal->closed_at)
        <a class="btn btn-default" href="{{ route('lawyerApprovalShow', $legal->id) }}">Lawyer approval</a>
      @elseif($code === 803)
        <a class="btn btn-default" href="{{ route('activeCasesShow', $legal->id) }}">Contract &amp; notes</a>
      @endif
    </div>
  </div>

  @include('masters::Legal._case_summary')

  <div class="lcp-grid">
    <div><div class="k">Claim amount</div><div class="v">{{ $legal->claim_amount !== null ? numberFormat($legal->claim_amount) : '-' }}</div></div>
    <div><div class="k">Judgment amount</div><div class="v">{{ $legal->judgment_amount !== null ? numberFormat($legal->judgment_amount) : '-' }}</div></div>
    <div><div class="k">Court case no.</div><div class="v">{{ $legal->court_case_no ?: '-' }}</div></div>
    <div><div class="k">Court</div><div class="v">{{ $legal->court_name ?: '-' }}</div></div>
    <div><div class="k">Filed on</div><div class="v">{{ $fmtDate($legal->filed_date) }}</div></div>
    <div><div class="k">Next hearing</div><div class="v">{{ $fmtDate($legal->next_hearing_date) }}</div></div>
    <div><div class="k">Judgment date</div><div class="v">{{ $fmtDate($legal->judgment_date) }}
      @if($legal->eviction_ordered !== null)<small style="font-weight:400;color:#5b6b7f;"> &middot; eviction {{ $legal->eviction_ordered ? 'ordered' : 'not ordered' }}</small>@endif
    </div></div>
  </div>

  @if($current && $current->code === 'eviction' && $contract && (int) $contract->tenant_contract_status === 1)
    <div class="lcp-prompt">The unit is handed back but the contract is still active. Start the tenant termination so the unit becomes vacant.
      <a href="{{ route('tenantTermination.create') }}">Start termination</a></div>
  @endif

  <div class="lcp-section-title">Stages</div>
  <div class="lcp-steps">
    @forelse($stages as $i => $s)
      @php $cls = $current && $s->id === $current->id ? 'current' : ((int) $s->sort_order < $currentOrder ? 'done' : ''); @endphp
      <div class="lcp-step {{ $cls }}"><span class="n">{{ $i + 1 }}</span>{{ $s->name }}</div>
    @empty
      <span class="text-muted">No stages set up.</span>
    @endforelse
  </div>
  @if(!$current && $code === 803 && !$legal->closed_at)
    <p class="text-muted" style="padding:0 20px;">Stage not set yet{{ $canStage ? ' - use Update stage.' : '.' }}</p>
  @endif
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card card-box">
      @include('masters::Legal._timeline')
    </div>
  </div>
  <div class="col-lg-4">
    @if($canComment)
    <div class="card card-box">
      <div class="card-head"><div class="col"><h4>Add comment</h4></div></div>
      <div class="card-body">
        <form method="POST" action="{{ route('legal.case.comment', $legal->id) }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="form-group">
            <label for="lcp_comment" class="sr-only">Comment</label>
            <textarea id="lcp_comment" name="comment" class="form-control" rows="4" maxlength="2000" required placeholder="What happened, what is next">{{ old('comment') }}</textarea>
          </div>
          <div class="form-group">
            <label for="lcp_comment_files" style="font-size:12px;color:#5b6b7f;">Attach files (PDF, Word, images; up to 10 MB each)</label>
            <input type="file" id="lcp_comment_files" name="files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
          </div>
          <button type="submit" class="btn btn-primary">Add comment</button>
        </form>
      </div>
    </div>
    @endif
    @if(isset($docs[0]) && count($docs[0]))
    <div class="card card-box">
      <div class="card-head"><div class="col"><h4>Earlier documents</h4></div></div>
      <div class="card-body">
        @foreach($docs[0] as $doc)
          <div><a target="_blank" href="{{ asset('storage/app/' . $doc->legal_documents_file_name) }}"><i class="fa fa-paperclip" aria-hidden="true"></i> {{ $doc->legal_documents_name }}</a></div>
        @endforeach
      </div>
    </div>
    @endif
    @if(count($related))
    <div class="card card-box">
      <div class="card-head"><div class="col"><h4>Other cases on this contract</h4></div></div>
      <div class="card-body">
        @foreach($related as $r)
          <div><a href="{{ route('legal.case', $r->id) }}">Case #{{ $r->id }}</a>
            <small class="text-muted">{{ $r->closed_at ? 'closed ' . \Carbon\Carbon::parse($r->closed_at)->format('d/m/Y') : 'open' }}</small></div>
        @endforeach
      </div>
    </div>
    @endif
  </div>
</div>

@if($canStage)
<div class="modal fade" id="lcpStageModal" tabindex="-1" role="dialog" aria-labelledby="lcpStageTitle">
  <div class="modal-dialog" role="document" style="max-width:620px;">
    <div class="modal-content">
      <form method="POST" action="{{ route('legal.case.stage', $legal->id) }}" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="modal-header">
          <h4 class="modal-title" id="lcpStageTitle">Update stage</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-7">
              <div class="form-group">
                <label for="lcp_stage">Stage</label>
                <select id="lcp_stage" name="stage_id" class="form-control" required>
                  @foreach($stages as $s)
                    @if((int) $s->sort_order >= $currentOrder)
                      <option value="{{ $s->id }}" data-code="{{ $s->code }}" @if(old('stage_id') == $s->id) selected @endif>{{ $s->name }}{{ $current && $s->id === $current->id ? ' (current)' : '' }}</option>
                    @endif
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-sm-5">
              <div class="form-group">
                <label for="lcp_event_date">Date</label>
                <input type="date" id="lcp_event_date" name="event_date" class="form-control" max="{{ date('Y-m-d') }}" value="{{ old('event_date', date('Y-m-d')) }}" required>
              </div>
            </div>

            <div class="col-sm-6 lcp-f lcp-f-filed lcp-hidden">
              <div class="form-group">
                <label for="lcp_case_no">Court case no.</label>
                <input type="text" id="lcp_case_no" name="court_case_no" class="form-control" maxlength="100" value="{{ old('court_case_no', $legal->court_case_no) }}">
              </div>
            </div>
            <div class="col-sm-6 lcp-f lcp-f-filed lcp-hidden">
              <div class="form-group">
                <label for="lcp_court">Court / committee</label>
                <input type="text" id="lcp_court" name="court_name" class="form-control" maxlength="150" value="{{ old('court_name', $legal->court_name) }}">
              </div>
            </div>
            <div class="col-sm-6 lcp-f lcp-f-filed lcp-hidden">
              <div class="form-group">
                <label for="lcp_claim">Claim amount</label>
                <input type="number" step="0.001" min="0" id="lcp_claim" name="claim_amount" class="form-control" value="{{ old('claim_amount', $legal->claim_amount !== null ? $legal->claim_amount : round($outstanding, 3)) }}">
              </div>
            </div>

            <div class="col-sm-6 lcp-f lcp-f-judgment lcp-hidden">
              <div class="form-group">
                <label for="lcp_judgment">Amount awarded</label>
                <input type="number" step="0.001" min="0" id="lcp_judgment" name="judgment_amount" class="form-control" value="{{ old('judgment_amount', $legal->judgment_amount) }}">
              </div>
            </div>
            @if((int) $legal->case_type === 1)
            <div class="col-sm-6 lcp-f lcp-f-judgment lcp-hidden">
              <div class="form-group">
                <label for="lcp_eviction">Eviction ordered?</label>
                <select id="lcp_eviction" name="eviction_ordered" class="form-control">
                  <option value="">Select</option>
                  <option value="1" @if(old('eviction_ordered') === '1') selected @endif>Yes</option>
                  <option value="0" @if(old('eviction_ordered') === '0') selected @endif>No</option>
                </select>
              </div>
            </div>
            @endif

            <div class="col-sm-12 lcp-f lcp-f-eviction lcp-hidden">
              <p class="text-muted">The case becomes a recovery case (vacated / terminated). Start the tenant termination for the unit afterwards.</p>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="lcp_next">Next hearing date (optional)</label>
                <input type="date" id="lcp_next" name="next_hearing_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('next_hearing_date') }}">
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="lcp_note">Note</label>
                <textarea id="lcp_note" name="note" class="form-control" rows="3" maxlength="2000" required>{{ old('note') }}</textarea>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="lcp_stage_files">Attach files (notice, filing receipt, judgment copy...)</label>
                <input type="file" id="lcp_stage_files" name="files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save stage</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endsection

@section('scripts')
@if($canStage)
<script>
  (function () {
    function stageFields() {
      var code = $('#lcp_stage option:selected').data('code') || '';
      $('.lcp-f').addClass('lcp-hidden');
      $('.lcp-f-' + code).removeClass('lcp-hidden');
      $('#lcp_case_no').prop('required', code === 'filed');
      $('#lcp_judgment, #lcp_eviction').prop('required', code === 'judgment');
    }
    $('#lcp_stage').on('change', stageFields);
    stageFields();
    @if($errors->any() || session('error'))
      $('#lcpStageModal').modal('show');
    @endif
  })();
</script>
@endif
@endsection
