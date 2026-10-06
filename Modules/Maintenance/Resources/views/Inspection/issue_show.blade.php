@extends('layouts.plms-app')
@section('css')
@include('maintenance::Inspection._styles')
<style>
  .ins-issue { display:grid; grid-template-columns:minmax(0, 1.6fr) minmax(0, 1fr); gap:24px; align-items:start; }
  .ins-steps { display:flex; gap:4px; margin:16px 0 4px; }
  .ins-steps span { flex:1; height:6px; border-radius:3px; background:#e3e9ef; }
  .ins-steps span.done { background:var(--ins-blue); }
  .ins-steplabels { display:flex; justify-content:space-between; font-size:12px; color:var(--ins-muted); }
  .ins-details { display:grid; grid-template-columns:1fr 1fr; gap:12px 24px; }
  .ins-details small { display:block; text-transform:uppercase; letter-spacing:.04em; font-size:11px; }
  .ins-photos { display:flex; flex-wrap:wrap; gap:12px; }
  .ins-photos a { display:block; text-decoration:none; }
  .ins-photos img { width:160px; height:120px; object-fit:cover; border-radius:8px; border:1px solid var(--ins-line); display:block; }
  .ins-photos span { display:block; font-size:12px; color:var(--ins-muted); margin-top:4px; }
  .ins-timeline { list-style:none; margin:0; padding:0; }
  .ins-timeline li { position:relative; padding:0 0 20px 24px; border-left:2px solid var(--ins-line); margin-left:6px; }
  .ins-timeline li:last-child { border-left-color:transparent; padding-bottom:0; }
  .ins-timeline li:before { content:""; position:absolute; left:-7px; top:2px; width:12px; height:12px; border-radius:50%; background:#fff; border:2px solid var(--ins-blue); }
  .ins-timeline li.k-resolved:before, .ins-timeline li.k-verified:before { border-color:var(--ins-ok); background:var(--ins-ok); }
  .ins-timeline li.k-sent_back:before { border-color:var(--ins-bad); }
  .ins-timeline .when { font-size:12px; color:var(--ins-muted); }
  .ins-timeline p { margin:4px 0 0; white-space:pre-line; }
  .ins-timeline .ins-photos img { width:120px; height:90px; }
  @media (max-width: 991px) { .ins-issue { grid-template-columns:1fr; } }
  @media (max-width: 575px) { .ins-details { grid-template-columns:1fr; } .ins-photos img { width:140px; height:105px; } }
</style>
@endsection
@section('content')
@php
  use Modules\Maintenance\Services\Inspection as I;
  $n = function ($id) use ($names) { return isset($names[$id]) ? $names[$id] : ''; };
  $late = $issue->status < I::RESOLVED && $issue->target_date < date('Y-m-d');
  $before = $photos->where('kind', 1); $after = $photos->where('kind', 3);
  $kinds = ['raised' => 'Raised', 'progress' => 'Progress update', 'plan' => 'Action plan', 'followup' => 'Inspection follow-up', 'resolved' => 'Resolved',
    'verified' => 'Verified & closed', 'sent_back' => 'Sent back', 'complaint' => 'Complaint ticket'];
@endphp
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Building Inspection</div></div>{{ Breadcrumbs::render('inspection.issues.show', $issue) }}</div></div>
<div class="ins">
  @include('maintenance::Inspection._nav', ['active' => 'issues'])
  <div class="ins-head">
    <div><small>{{ $issue->issue_no }} · {{ I::SOURCES[$issue->source] }}</small>
      <h1 style="margin-top:4px">{{ $issue->description }}</h1>
      <p class="sub">{{ $issue->building_name }}@if($issue->area) · {{ $issue->area }}@endif · {{ $issue->unit_no ? 'Unit ' . $issue->unit_no : 'Common area' }}</p></div>
    <div class="ins-tools"><span class="ins-chip p{{ $issue->priority }}">{{ I::PRIORITIES[$issue->priority] }}</span><span class="ins-chip s{{ $issue->status }}">{{ I::ISSUE_STATUSES[$issue->status] }}</span>
      @if($late)<span class="ins-chip late">{{ I::days($issue->target_date) }} days overdue</span>@endif</div>
  </div>

  <div class="ins-issue">
    <div>
      <section class="ins-card"><div class="ins-card-body">
        <div class="ins-steps" aria-hidden="true">@foreach(I::ISSUE_STATUSES as $k => $label)<span class="{{ $k <= $issue->status ? 'done' : '' }}"></span>@endforeach</div>
        <div class="ins-steplabels" aria-hidden="true">@foreach(I::ISSUE_STATUSES as $label)<span>{{ $label }}</span>@endforeach</div>
        <div class="ins-details" style="margin-top:24px">
          <div><small>Category</small>{{ $issue->category }}</div>
          <div><small>Responsible executive</small>{{ $n($issue->executive_id) }}</div>
          <div><small>Raised</small>{{ date('d M Y H:i', strtotime($issue->raised_at)) }} · {{ $n($issue->raised_by) }}</div>
          <div><small>Target date</small>{{ date('d M Y', strtotime($issue->target_date)) }}</div>
          <div><small>Inspection visit</small>@if($visit)<a href="{{ route('inspection.visits.show', $visit->id) }}">{{ $visit->visit_no }}</a>@else — @endif</div>
          <div><small>Complaint ticket</small>{{ $issue->complaint_no ? $issue->complaint_no . '-SUB1' : 'None' }}</div>
          @if($issue->resolved_at)<div><small>Resolved</small>{{ date('d M Y H:i', strtotime($issue->resolved_at)) }} · {{ $n($issue->resolved_by) }}</div>@endif
          @if($issue->closed_at)<div><small>Verified & closed</small>{{ date('d M Y H:i', strtotime($issue->closed_at)) }} · {{ $n($issue->closed_by) }}</div>@endif
        </div>
      </div></section>

      <section class="ins-card"><div class="ins-card-head"><h2>Photos</h2></div><div class="ins-card-body">
        <div class="ins-photos">
          @forelse($photos as $ph)
          <a href="{{ I::photoUrl($ph->path) }}" target="_blank" rel="noopener"><img src="{{ I::photoUrl($ph->thumb_path) }}" alt="{{ I::PHOTO_KINDS[$ph->kind] }} photo"><span>{{ I::PHOTO_KINDS[$ph->kind] }} · {{ date('d M', strtotime($ph->created_at)) }}</span></a>
          @empty
          <p class="muted">No photos.</p>
          @endforelse
        </div>
      </div></section>

      <section class="ins-card" aria-labelledby="plan-title">
        <div class="ins-card-head"><h2 id="plan-title">Action plan</h2>@if(!$canWork && $issue->status != I::CLOSED)<small>Kept by {{ $n($issue->executive_id) }}</small>@endif</div>
        <div class="ins-card-body">
          <form method="post" action="{{ route('inspection.issues.plan', $issue->id) }}" id="plan-form">
            @csrf
            <fieldset @if(!$canWork) disabled @endif style="border:0;padding:0;margin:0">
            <div class="ins-form">
              <div class="ins-field full"><label for="ap-action">Action to be taken <span class="req">*</span></label>
                <textarea name="action_plan" id="ap-action" required maxlength="2000" placeholder="e.g. Replace the pump seal; contractor to attend">{{ old('action_plan', $issue->action_plan) }}</textarea></div>
              <div class="ins-field"><label for="ap-party">Responsible party <span class="req">*</span></label>
                <select name="responsible_party" id="ap-party" required><option value="">Select</option>@foreach(I::PARTIES as $p)<option {{ old('responsible_party', $issue->responsible_party) === $p ? 'selected' : '' }}>{{ $p }}</option>@endforeach</select></div>
              <div class="ins-field"><label for="ap-cost">Estimated cost (OMR) <span class="req">*</span></label>
                <input type="number" name="estimated_cost" id="ap-cost" min="0" step="0.001" required value="{{ old('estimated_cost', $issue->estimated_cost !== null ? number_format($issue->estimated_cost, 3, '.', '') : '') }}"></div>
              <div class="ins-field"><label for="ap-approval">Landlord approval needed <span class="req">*</span></label>
                <select name="landlord_approval" id="ap-approval" required>@php $la = old('landlord_approval', $issue->landlord_approval === null ? '' : (int) $issue->landlord_approval); @endphp
                  <option value="">Select</option><option value="0" {{ (string) $la === '0' ? 'selected' : '' }}>No</option><option value="1" {{ (string) $la === '1' ? 'selected' : '' }}>Yes</option></select></div>
              <div class="ins-field"><label for="ap-target">Target date <span class="req">*</span></label>
                <input type="date" name="target_date" id="ap-target" required value="{{ old('target_date', $issue->target_date) }}"></div>
            </div>
            @if($canWork)<div style="margin-top:16px"><button type="submit" class="ins-btn primary">Save action plan</button></div>@endif
            </fieldset>
          </form>
        </div>
      </section>
    </div>

    <div>
      @if($canVerify)
      <section class="ins-card" style="border-color:var(--ins-blue-100)"><div class="ins-card-head"><h2>Verify the rectification</h2></div><div class="ins-card-body">
        <p class="muted" style="margin-bottom:12px">Check the after photo{{ $after->count() > 1 ? 's' : '' }} before closing.</p>
        @if($after->count())<div class="ins-photos" style="margin-bottom:16px">@foreach($after as $ph)<a href="{{ I::photoUrl($ph->path) }}" target="_blank" rel="noopener"><img src="{{ I::photoUrl($ph->thumb_path) }}" alt="After photo"></a>@endforeach</div>@endif
        <form method="post" action="{{ route('inspection.issues.verify', $issue->id) }}" id="verify-form">
          @csrf
          <div class="ins-field"><label for="vf-note">Note</label><textarea name="note" id="vf-note" maxlength="2000" placeholder="Required when sending back">{{ old('note') }}</textarea></div>
          <div class="ins-tools" style="margin-top:12px">
            <button type="submit" name="decision" value="close" class="ins-btn primary"><i class="fa fa-shield" aria-hidden="true"></i> Verify &amp; close</button>
            <button type="submit" name="decision" value="send_back" class="ins-btn danger js-sendback">Send back</button>
          </div>
        </form>
      </div></section>
      @elseif($issue->status == I::RESOLVED)
      <div class="ins-alert info">Resolved — waiting for verification by a supervisor or the maintenance manager.@if(Auth::user()->can('inspection_verify')) You cannot verify an issue you raised or own.@endif</div>
      @endif

      @if($canWork && $issue->status != I::RESOLVED)
      <section class="ins-card"><div class="ins-card-head"><h2>Resolve this issue</h2></div><div class="ins-card-body">
        <form method="post" action="{{ route('inspection.issues.resolve', $issue->id) }}" enctype="multipart/form-data" id="resolve-form">
          @csrf
          <div class="ins-field"><label for="rs-photo">After photo <span class="req">*</span></label>
            <input type="file" name="photo" id="rs-photo" accept="image/*" capture="environment" required></div>
          <div class="ins-field" style="margin-top:12px"><label for="rs-note">Note</label><textarea name="note" id="rs-note" maxlength="2000" placeholder="What was done">{{ old('note') }}</textarea></div>
          <button type="submit" class="ins-btn primary" style="margin-top:12px"><i class="fa fa-check" aria-hidden="true"></i> Mark resolved</button>
        </form>
      </div></section>
      @endif

      @if($canComplaint)
      <section class="ins-card"><div class="ins-card-head"><h2>Complaint ticket</h2></div><div class="ins-card-body">
        <p class="muted" style="margin-bottom:12px">Optional. Sends this issue to the maintenance team as a complaint ticket ({{ $issue->category }}).</p>
        <button type="button" class="ins-btn" data-toggle="modal" data-target="#ins-complaint-modal"><i class="fa fa-ticket" aria-hidden="true"></i> Create complaint ticket</button>
      </div></section>
      @endif

      <section class="ins-card" aria-labelledby="tl-title"><div class="ins-card-head"><h2 id="tl-title">Progress timeline</h2></div><div class="ins-card-body">
        <ol class="ins-timeline">
          @foreach($updates as $u)
          <li class="k-{{ $u->kind }}"><div class="when">{{ date('d M Y H:i', strtotime($u->created_at)) }} · {{ $n($u->created_by) }}</div>
            <b>{{ isset($kinds[$u->kind]) ? $kinds[$u->kind] : ucfirst($u->kind) }}@if($u->condition) — {{ I::CONDITIONS[$u->condition] }}@endif</b>
            @if($u->note)<p>{{ $u->note }}</p>@endif
            @if(isset($updatePhotos[$u->id]))<div class="ins-photos" style="margin-top:8px">@foreach($updatePhotos[$u->id] as $ph)<a href="{{ I::photoUrl($ph->path) }}" target="_blank" rel="noopener"><img src="{{ I::photoUrl($ph->thumb_path) }}" alt="{{ I::PHOTO_KINDS[$ph->kind] }} photo"></a>@endforeach</div>@endif
          </li>
          @endforeach
        </ol>
        @if($canWork)
        <form method="post" action="{{ route('inspection.issues.progress', $issue->id) }}" enctype="multipart/form-data" id="progress-form" style="margin-top:24px;border-top:1px solid var(--ins-line);padding-top:16px">
          @csrf
          <div class="ins-field"><label for="pg-note">Progress update <span class="req">*</span></label>
            <textarea name="note" id="pg-note" required maxlength="2000" placeholder="What has changed?"></textarea></div>
          <div class="ins-field" style="margin-top:12px"><label for="pg-photo">Photo (optional)</label><input type="file" name="photo" id="pg-photo" accept="image/*" capture="environment"></div>
          <button type="submit" class="ins-btn" style="margin-top:12px">Add update</button>
        </form>
        @endif
      </div></section>
    </div>
  </div>

  @if($canComplaint)
  <div class="modal fade" id="ins-complaint-modal" tabindex="-1" role="dialog" aria-labelledby="ins-complaint-title" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
      <form method="post" action="{{ route('inspection.issues.complaint', $issue->id) }}" id="complaint-form">
        @csrf
        <div class="modal-header"><h2 class="modal-title" id="ins-complaint-title">Create complaint ticket</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        <div class="modal-body">
          <p class="muted" style="margin-bottom:16px">{{ $issue->building_name }} · {{ $issue->unit_no ? 'Unit ' . $issue->unit_no : 'Common area' }} · {{ $issue->category }} · {{ (int) $issue->priority <= 2 ? 'High' : 'Normal' }} priority. The ticket goes into the normal complaint workflow; the tenant is not notified.</p>
          <div class="ins-form">
            <div class="ins-field"><label for="cp-name">Contact name <span class="req">*</span></label><input type="text" name="complainer_name" id="cp-name" required maxlength="100" value="{{ old('complainer_name', $me ? $me->name : '') }}"></div>
            <div class="ins-field"><label for="cp-mob">Contact mobile <span class="req">*</span></label><input type="text" name="complaint_mob_no" id="cp-mob" required maxlength="20" value="{{ old('complaint_mob_no', $me ? $me->employee_contact_no : '') }}"></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="ins-btn" data-dismiss="modal">Close</button><button type="submit" class="ins-btn primary">Create ticket</button></div>
      </form>
    </div></div>
  </div>
  @endif
</div>
@endsection
@section('scripts')
<script>
$(function () {
  $('#plan-form, #resolve-form, #progress-form, #complaint-form').on('submit', function (e) {
    var ok = true;
    $(this).find('[required]').each(function () {
      var bad = this.type === 'file' ? !this.files.length : !$.trim($(this).val());
      $(this).closest('.ins-field').toggleClass('has-error', bad);
      if (bad) { ok = false; }
    });
    if (!ok) { e.preventDefault(); } else { $(this).find('[type=submit]').prop('disabled', true); }
  });
  var decision = null;
  $('#verify-form [type=submit]').on('click', function () { decision = this.value; });
  $('#verify-form').on('submit', function (e) {
    if (decision === 'send_back' && !$.trim($('#vf-note').val())) {
      e.preventDefault(); $('#vf-note').focus().closest('.ins-field').addClass('has-error');
      return;
    }
    // keep the clicked button's value: disabling it would drop it from the post
    $('<input type="hidden" name="decision">').val(decision || 'close').appendTo(this);
    $(this).find('[type=submit]').prop('disabled', true);
  });
});
</script>
@endsection
