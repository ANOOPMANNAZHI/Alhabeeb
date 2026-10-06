@extends('layouts.plms-app')
@section('css')
@include('maintenance::Inspection._styles')
<style>
  .iss { max-width:1280px; }
  .iss .ico { width:16px; text-align:center; color:var(--ins-muted); margin-right:4px; }
  .iss-hero { background:#fff; border:1px solid var(--ins-line); border-radius:12px; padding:24px; margin-bottom:16px; }
  .iss-hero .meta { display:flex; flex-wrap:wrap; gap:8px 16px; align-items:center; font-size:13px; color:var(--ins-muted); }
  .iss-hero .chips { display:flex; flex-wrap:wrap; gap:8px; margin-left:auto; }
  .iss-hero h1 { margin:12px 0 8px; line-height:1.3; }
  .iss-hero .where { display:flex; flex-wrap:wrap; gap:4px 16px; color:var(--ins-muted); }

  .iss-steps { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); margin:24px 0 0; padding:0; list-style:none; counter-reset:step; }
  .iss-steps li { position:relative; padding-top:28px; font-size:13px; }
  .iss-steps li:before { content:""; position:absolute; top:8px; left:0; right:0; height:4px; background:var(--ins-line); }
  .iss-steps li:first-child:before { left:12px; border-radius:2px 0 0 2px; }
  .iss-steps li:last-child:before { right:0; border-radius:0 2px 2px 0; }
  .iss-steps li:after { content:""; position:absolute; top:2px; left:0; width:16px; height:16px; border-radius:50%; background:#fff; border:3px solid var(--ins-line); box-sizing:border-box; }
  .iss-steps li.done:before, .iss-steps li.current:before { background:var(--ins-blue); }
  .iss-steps li.done:after { background:var(--ins-blue); border-color:var(--ins-blue); }
  .iss-steps li.current:after { border-color:var(--ins-blue); box-shadow:0 0 0 4px var(--ins-blue-100); }
  .iss-steps li.current:before { background:linear-gradient(90deg, var(--ins-blue) 0, var(--ins-blue) 16px, var(--ins-line) 16px); }
  .iss-steps b { display:block; font-weight:600; color:var(--ins-text); }
  .iss-steps li.todo b { color:var(--ins-muted); font-weight:500; }
  .iss-steps span { color:var(--ins-muted); font-size:12px; }

  .iss-facts { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:1px; background:var(--ins-line); border:1px solid var(--ins-line); border-radius:10px; overflow:hidden; margin-top:24px; }
  .iss-facts > div { background:#fafcfd; padding:12px 16px; min-width:0; }
  .iss-facts small { display:block; font-size:11px; text-transform:uppercase; letter-spacing:.04em; margin-bottom:2px; }
  .iss-facts b { font-weight:600; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .iss-facts .bad { color:var(--ins-bad); } .iss-facts .warn { color:#c2410c; }

  .iss-next { border-radius:12px; padding:20px 24px; margin-bottom:24px; border:1px solid var(--ins-blue-100); background:var(--ins-blue-50); }
  .iss-next.ok { background:var(--ins-ok-50); border-color:#bfe8cf; }
  .iss-next.wait { background:#fafcfd; border-color:var(--ins-line); }
  .iss-next .eyebrow { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--ins-blue-700); }
  .iss-next.ok .eyebrow { color:var(--ins-ok); } .iss-next.wait .eyebrow { color:var(--ins-muted); }
  .iss-next h2 { margin:4px 0 4px; }
  .iss-next form { margin-top:16px; }
  .iss-next .row2 { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.4fr) auto; gap:16px; align-items:end; }

  .iss-grid { display:grid; grid-template-columns:minmax(0, 1.65fr) minmax(0, 1fr); gap:24px; align-items:start; }
  .iss-grid > div > .ins-card:last-child { margin-bottom:0; }

  .iss-dl { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px 24px; margin:0; }
  .iss-dl .full { grid-column:1 / -1; }
  .iss-dl dt { font-size:12px; font-weight:500; color:var(--ins-muted); margin-bottom:2px; }
  .iss-dl dd { margin:0; font-weight:500; white-space:pre-line; }
  .iss-plan-meta { display:flex; flex-wrap:wrap; justify-content:space-between; gap:12px; align-items:center; padding:12px 24px; border-top:1px solid var(--ins-line); font-size:13px; color:var(--ins-muted); }
  .iss-plan-meta .acts { display:flex; gap:8px; flex-wrap:wrap; }
  .iss .is-changed input, .iss .is-changed select, .iss .is-changed textarea { border-color:var(--ins-accent); background:#f3fbfe; }
  .iss .chg-tag { display:none; font-size:11px; font-weight:600; color:#036b8f; margin-left:6px; }
  .iss .is-changed .chg-tag { display:inline; }

  .iss-history { border-top:1px solid var(--ins-line); background:#fafcfd; }
  .iss-history h3 { padding:16px 24px 0; }
  .iss-set { padding:12px 24px 16px; border-bottom:1px solid #eef2f6; }
  .iss-set:last-child { border-bottom:0; }
  .iss-set .who { font-size:13px; } .iss-set .who b { font-weight:600; }
  .iss-set .why { font-size:13px; color:var(--ins-muted); margin-top:2px; font-style:italic; }
  .iss-set table { width:100%; margin-top:8px; border-collapse:collapse; font-size:13px; }
  .iss-set th { text-align:left; font-weight:500; color:var(--ins-muted); font-size:12px; padding:4px 8px 4px 0; width:32%; vertical-align:top; }
  .iss-set td { padding:4px 8px 4px 0; vertical-align:top; white-space:pre-line; }
  .iss-set .old { color:var(--ins-muted); text-decoration:line-through; text-decoration-color:#c4ced8; }
  .iss-set .arrow { color:var(--ins-muted); padding:4px 8px; width:20px; }
  .iss-set .new { font-weight:600; }

  .iss-feed { list-style:none; margin:0; padding:0; }
  .iss-feed li { display:grid; grid-template-columns:32px minmax(0,1fr); gap:12px; padding:16px 24px; border-top:1px solid #eef2f6; }
  .iss-feed .dot { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:var(--ins-blue-50); color:var(--ins-blue); font-size:14px; }
  .iss-feed .k-resolved .dot, .iss-feed .k-verified .dot { background:var(--ins-ok-50); color:var(--ins-ok); }
  .iss-feed .k-sent_back .dot { background:var(--ins-bad-50); color:var(--ins-bad); }
  .iss-feed .k-plan .dot { background:#e0f4fb; color:#036b8f; }
  .iss-feed .head { display:flex; flex-wrap:wrap; gap:4px 8px; align-items:baseline; }
  .iss-feed .head b { font-weight:600; }
  .iss-feed .head span { font-size:12px; color:var(--ins-muted); }
  .iss-feed p { margin:4px 0 0; white-space:pre-line; }
  .iss-compose { padding:16px 24px 20px; }
  .iss-compose .row2 { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:12px; align-items:end; margin-top:12px; }

  .iss-photos h3 { font-size:13px !important; font-weight:600; margin:0 0 8px; color:var(--ins-muted); }
  .iss-photos .grid { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:8px; margin-bottom:16px; }
  .iss-photos .grid:last-child { margin-bottom:0; }
  .iss-photos a { display:block; border-radius:8px; overflow:hidden; border:1px solid var(--ins-line); aspect-ratio:4/3; background:var(--ins-bg); }
  .iss-photos img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .2s; }
  .iss-photos a:hover img { transform:scale(1.04); }
  .iss-thumbs { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
  .iss-thumbs a { width:96px; aspect-ratio:4/3; border-radius:6px; overflow:hidden; border:1px solid var(--ins-line); display:block; }
  .iss-thumbs img { width:100%; height:100%; object-fit:cover; }

  .iss-side-dl { margin:0; }
  .iss-side-dl > div { display:flex; justify-content:space-between; gap:16px; padding:10px 0; border-top:1px solid #eef2f6; font-size:13px; }
  .iss-side-dl > div:first-child { border-top:0; padding-top:0; }
  .iss-side-dl dt { color:var(--ins-muted); font-weight:400; }
  .iss-side-dl dd { margin:0; text-align:right; font-weight:500; }

  @media (max-width: 991px) {
    .iss-grid { grid-template-columns:1fr; }
    .iss-facts { grid-template-columns:repeat(2, minmax(0,1fr)); }
    .iss-next .row2 { grid-template-columns:1fr; }
  }
  @media (max-width: 575px) {
    .iss-hero, .iss-next { padding:16px; }
    .iss-hero .chips { margin-left:0; width:100%; }
    .iss-steps li { font-size:12px; } .iss-steps span { display:none; }
    .iss-dl { grid-template-columns:1fr; }
    .iss-feed li, .iss-set, .iss-compose, .iss-plan-meta { padding-left:16px; padding-right:16px; }
    .iss-history h3 { padding-left:16px; }
    .iss-compose .row2 { grid-template-columns:1fr; }
    .iss .ins-btn { min-height:44px; }
    .iss .ins-field input:not([type=file]), .iss .ins-field select { min-height:44px; }
    .iss-next .ins-btn, .iss-compose .ins-btn { width:100%; }
  }
  @media (prefers-reduced-motion: reduce) { .iss-photos img { transition:none; } }
</style>
@endsection
@section('content')
@php
  use Modules\Maintenance\Services\Inspection as I;
  $n = function ($id) use ($names) { return isset($names[$id]) ? $names[$id] : ''; };
  $today = date('Y-m-d');
  $late = $issue->status < I::RESOLVED && $issue->target_date < $today;
  $daysLeft = (strtotime($issue->target_date) - strtotime($today)) / 86400;
  $byKind = $photos->groupBy('kind');
  $after = isset($byKind[3]) ? $byKind[3] : collect();
  $started = $updates->first(function ($u) { return in_array($u->kind, ['plan', 'progress', 'followup']); });
  $stepDates = [1 => $issue->raised_at, 2 => $started ? $started->created_at : null, 3 => $issue->resolved_at, 4 => $issue->closed_at];
  $hasPlan = $issue->action_plan !== null && $issue->action_plan !== '';
  $editOpen = $canWork && (!$hasPlan || $errors->has('action_plan') || $errors->has('responsible_party') || $errors->has('estimated_cost') || $errors->has('target_date') || $errors->has('landlord_approval'));
  $updatesById = $updates->keyBy('id');
  $kinds = ['raised' => ['Issue raised', 'fa-flag'], 'progress' => ['Progress update', 'fa-comment'], 'plan' => ['Action plan', 'fa-wrench'],
    'followup' => ['Inspection follow-up', 'fa-search'], 'resolved' => ['Marked resolved', 'fa-check'], 'verified' => ['Verified & closed', 'fa-shield'],
    'sent_back' => ['Sent back', 'fa-undo'], 'complaint' => ['Complaint ticket', 'fa-ticket']];
  $la = old('landlord_approval', $issue->landlord_approval === null ? '' : (int) $issue->landlord_approval);
@endphp
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Building Inspection</div></div>{{ Breadcrumbs::render('inspection.issues.show', $issue) }}</div></div>
<div class="ins iss">

  {{-- Header: what, where, status --}}
  <header class="iss-hero">
    <div class="meta">
      <span><b style="color:var(--ins-text)">{{ $issue->issue_no }}</b></span>
      <span><i class="fa {{ $issue->source == 1 ? 'fa-search' : 'fa-pencil' }} ico" aria-hidden="true"></i>{{ $issue->source == 1 ? 'Found on inspection' : 'Recorded manually' }}@if($visit) · <a href="{{ route('inspection.visits.show', $visit->id) }}">{{ $visit->visit_no }}</a>@endif</span>
      <span>Raised {{ date('d M Y', strtotime($issue->raised_at)) }} by {{ $n($issue->raised_by) }}</span>
      <div class="chips"><span class="ins-chip p{{ $issue->priority }}">{{ I::PRIORITIES[$issue->priority] }} priority</span><span class="ins-chip s{{ $issue->status }}">{{ I::ISSUE_STATUSES[$issue->status] }}</span></div>
    </div>
    <h1>{{ $issue->description }}</h1>
    <div class="where">
      <span><i class="fa fa-building-o ico" aria-hidden="true"></i>{{ $issue->building_name }}</span>
      @if($issue->area)<span><i class="fa fa-map-marker ico" aria-hidden="true"></i>{{ $issue->area }}</span>@endif
      <span><i class="fa fa-home ico" aria-hidden="true"></i>{{ $issue->unit_no ? 'Unit ' . $issue->unit_no : 'Common area' }}</span>
    </div>

    <ol class="iss-steps" aria-label="Status: step {{ $issue->status }} of 4, {{ I::ISSUE_STATUSES[$issue->status] }}">
      @foreach(I::ISSUE_STATUSES as $k => $label)
      @php $state = $k < $issue->status || $issue->status == I::CLOSED ? 'done' : ($k == $issue->status ? 'current' : 'todo'); @endphp
      <li class="{{ $state }}" @if($state === 'current') aria-current="step" @endif><b>{{ $label }}</b><span>{{ $stepDates[$k] && $state !== 'todo' ? date('d M Y', strtotime($stepDates[$k])) : ' ' }}</span></li>
      @endforeach
    </ol>

    <div class="iss-facts">
      <div><small>Target date</small><b class="{{ $late ? 'bad' : ($issue->status < I::RESOLVED && $daysLeft <= 1 ? 'warn' : '') }}">{{ date('d M Y', strtotime($issue->target_date)) }}</b>
        <span class="small">@if($issue->status >= I::RESOLVED) Work done @elseif($late) {{ I::days($issue->target_date) }} days overdue @elseif($daysLeft == 0) Due today @else {{ (int) $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }} left @endif</span></div>
      <div><small>Responsible executive</small><b>{{ $n($issue->executive_id) ?: '—' }}</b><span class="small">{{ $issue->responsible_party ?: 'Party not set' }}</span></div>
      <div><small>Category</small><b>{{ $issue->category }}</b><span class="small">{{ $issue->complaint_no ? 'Ticket ' . $issue->complaint_no . '-SUB1' : 'No complaint ticket' }}</span></div>
      <div><small>Estimated cost</small><b>{{ $issue->estimated_cost !== null ? 'OMR ' . number_format($issue->estimated_cost, 3) : '—' }}</b>
        <span class="small">{{ $issue->landlord_approval === null ? 'Approval not set' : ($issue->landlord_approval ? 'Landlord approval needed' : 'No landlord approval') }}</span></div>
    </div>
  </header>

  {{-- Next step: the one thing to do now --}}
  @if($canVerify)
  <section class="iss-next" aria-labelledby="next-title">
    <div class="eyebrow">Next step</div>
    <h2 id="next-title">Verify the rectification</h2>
    <p class="muted">Resolved {{ date('d M Y H:i', strtotime($issue->resolved_at)) }} by {{ $n($issue->resolved_by) }}. Check the after photo{{ $after->count() > 1 ? 's' : '' }}, then close or send back.</p>
    @if($after->count())<div class="iss-thumbs">@foreach($after as $ph)<a href="{{ I::photoUrl($ph->path) }}" target="_blank" rel="noopener"><img src="{{ I::photoUrl($ph->thumb_path) }}" alt="After photo {{ $loop->iteration }}"></a>@endforeach</div>@endif
    <form method="post" action="{{ route('inspection.issues.verify', $issue->id) }}" id="verify-form">
      @csrf
      <div class="row2" style="grid-template-columns:minmax(0,1fr) auto">
        <div class="ins-field {{ $errors->has('note') ? 'has-error' : '' }}"><label for="vf-note">Note <span class="help">(required to send back)</span></label>
          <textarea name="note" id="vf-note" maxlength="2000" rows="2" placeholder="e.g. Leak still visible at the joint">{{ old('note') }}</textarea>
          @if($errors->has('note'))<span class="err">{{ $errors->first('note') }}</span>@endif</div>
        <div class="ins-tools">
          <button type="submit" name="decision" value="send_back" class="ins-btn danger"><i class="fa fa-undo" aria-hidden="true"></i> Send back</button>
          <button type="submit" name="decision" value="close" class="ins-btn primary"><i class="fa fa-shield" aria-hidden="true"></i> Verify &amp; close</button>
        </div>
      </div>
    </form>
  </section>
  @elseif($issue->status == I::CLOSED)
  <section class="iss-next ok"><div class="eyebrow">Closed</div><h2>Verified and closed</h2>
    <p class="muted">{{ date('d M Y H:i', strtotime($issue->closed_at)) }} by {{ $n($issue->closed_by) }} · open for {{ I::days($issue->raised_at, $issue->closed_at) }} days.</p></section>
  @elseif($issue->status == I::RESOLVED)
  <section class="iss-next wait"><div class="eyebrow">Waiting</div><h2>Waiting for verification</h2>
    <p class="muted">Resolved {{ date('d M Y', strtotime($issue->resolved_at)) }} by {{ $n($issue->resolved_by) }}. A supervisor or the maintenance manager checks the after photo and closes it.@if(Auth::user()->can('inspection_verify')) You can't verify an issue you raised or own.@endif</p></section>
  @elseif($canWork)
  <section class="iss-next" aria-labelledby="next-title">
    <div class="eyebrow">Next step</div>
    <h2 id="next-title">{{ $hasPlan ? 'Fix it and mark it resolved' : 'Plan the action, then resolve' }}</h2>
    <p class="muted">{{ $hasPlan ? 'When the work is done, attach an after photo. A supervisor verifies it before the issue is closed.' : 'Add the action plan below. When the work is done, attach an after photo here.' }}</p>
    <form method="post" action="{{ route('inspection.issues.resolve', $issue->id) }}" enctype="multipart/form-data" id="resolve-form">
      @csrf
      <div class="row2">
        <div class="ins-field {{ $errors->has('photo') ? 'has-error' : '' }}"><label for="rs-photo">After photo <span class="req">*</span></label>
          <input type="file" name="photo" id="rs-photo" accept="image/*" capture="environment" required>
          @if($errors->has('photo'))<span class="err">{{ $errors->first('photo') }}</span>@endif</div>
        <div class="ins-field"><label for="rs-note">What was done</label><textarea name="note" id="rs-note" rows="2" maxlength="2000" placeholder="e.g. Gasket replaced, no leak after 30 minutes">{{ old('note') }}</textarea></div>
        <button type="submit" class="ins-btn primary"><i class="fa fa-check" aria-hidden="true"></i> Mark resolved</button>
      </div>
    </form>
  </section>
  @else
  <section class="iss-next wait"><div class="eyebrow">In hand</div><h2>{{ $n($issue->executive_id) }} is working on this</h2>
    <p class="muted">Target {{ date('d M Y', strtotime($issue->target_date)) }}. The responsible executive updates the plan and marks it resolved.</p></section>
  @endif

  <div class="iss-grid">
    <div>
      {{-- Action plan: summary, edit form, change history --}}
      <section class="ins-card" aria-labelledby="plan-title" id="plan">
        <div class="ins-card-head"><h2 id="plan-title">Action plan</h2>
          @if($hasPlan && $canWork)<button type="button" class="ins-btn sm js-plan-edit" aria-controls="plan-form" aria-expanded="{{ $editOpen ? 'true' : 'false' }}" @if($editOpen) hidden @endif><i class="fa fa-pencil" aria-hidden="true"></i> Edit</button>@endif</div>

        @if($hasPlan)
        <div class="ins-card-body js-plan-view" @if($editOpen) hidden @endif>
          <dl class="iss-dl">
            <div class="full"><dt>Action to be taken</dt><dd>{{ $issue->action_plan }}</dd></div>
            <div><dt>Responsible party</dt><dd>{{ $issue->responsible_party }}</dd></div>
            <div><dt>Estimated cost</dt><dd>OMR {{ number_format($issue->estimated_cost, 3) }}</dd></div>
            <div><dt>Landlord approval</dt><dd>{{ $issue->landlord_approval ? 'Needed' : 'Not needed' }}</dd></div>
            <div><dt>Target date</dt><dd>{{ date('d M Y', strtotime($issue->target_date)) }}</dd></div>
          </dl>
        </div>
        @elseif(!$canWork)
        <div class="ins-card-body"><p class="muted">No action plan yet. {{ $n($issue->executive_id) }} adds it.</p></div>
        @endif

        @if($canWork)
        <form method="post" action="{{ route('inspection.issues.plan', $issue->id) }}" id="plan-form" class="ins-card-body" @if(!$editOpen) hidden @endif novalidate>
          @csrf
          @if(!$hasPlan)<p class="ins-alert info" style="margin-top:0">Plan how this will be fixed. Later changes are recorded with who changed what and when.</p>@endif
          <div class="ins-form">
            <div class="ins-field full {{ $errors->has('action_plan') ? 'has-error' : '' }}"><label for="ap-action">Action to be taken <span class="req">*</span><span class="chg-tag">Changed</span></label>
              <textarea name="action_plan" id="ap-action" required maxlength="2000" data-orig="{{ $issue->action_plan }}" placeholder="e.g. Replace the pump seal; contractor to attend">{{ old('action_plan', $issue->action_plan) }}</textarea>
              @if($errors->has('action_plan'))<span class="err">{{ $errors->first('action_plan') }}</span>@endif</div>
            <div class="ins-field {{ $errors->has('responsible_party') ? 'has-error' : '' }}"><label for="ap-party">Responsible party <span class="req">*</span><span class="chg-tag">Changed</span></label>
              <select name="responsible_party" id="ap-party" required data-orig="{{ $issue->responsible_party }}"><option value="">Select</option>@foreach(I::PARTIES as $p)<option {{ old('responsible_party', $issue->responsible_party) === $p ? 'selected' : '' }}>{{ $p }}</option>@endforeach</select>
              @if($errors->has('responsible_party'))<span class="err">{{ $errors->first('responsible_party') }}</span>@endif</div>
            <div class="ins-field {{ $errors->has('estimated_cost') ? 'has-error' : '' }}"><label for="ap-cost">Estimated cost (OMR) <span class="req">*</span><span class="chg-tag">Changed</span></label>
              <input type="number" name="estimated_cost" id="ap-cost" min="0" step="0.001" required inputmode="decimal" data-orig="{{ $issue->estimated_cost !== null ? number_format($issue->estimated_cost, 3, '.', '') : '' }}" value="{{ old('estimated_cost', $issue->estimated_cost !== null ? number_format($issue->estimated_cost, 3, '.', '') : '') }}">
              @if($errors->has('estimated_cost'))<span class="err">{{ $errors->first('estimated_cost') }}</span>@endif</div>
            <div class="ins-field {{ $errors->has('landlord_approval') ? 'has-error' : '' }}"><label for="ap-approval">Landlord approval needed <span class="req">*</span><span class="chg-tag">Changed</span></label>
              <select name="landlord_approval" id="ap-approval" required data-orig="{{ $issue->landlord_approval === null ? '' : (int) $issue->landlord_approval }}">
                <option value="">Select</option><option value="0" {{ (string) $la === '0' ? 'selected' : '' }}>No</option><option value="1" {{ (string) $la === '1' ? 'selected' : '' }}>Yes</option></select>
              @if($errors->has('landlord_approval'))<span class="err">{{ $errors->first('landlord_approval') }}</span>@endif</div>
            <div class="ins-field {{ $errors->has('target_date') ? 'has-error' : '' }}"><label for="ap-target">Target date <span class="req">*</span><span class="chg-tag">Changed</span></label>
              <input type="date" name="target_date" id="ap-target" required data-orig="{{ $issue->target_date }}" value="{{ old('target_date', $issue->target_date) }}">
              @if($errors->has('target_date'))<span class="err">{{ $errors->first('target_date') }}</span>@endif</div>
            @if($hasPlan)
            <div class="ins-field full"><label for="ap-reason">Reason for the change</label>
              <input type="text" name="change_reason" id="ap-reason" maxlength="1000" value="{{ old('change_reason') }}" placeholder="e.g. Contractor quote received; landlord asked to wait">
              <span class="help">Optional. Kept in the change history with the fields you changed.</span></div>
            @endif
          </div>
          <div class="ins-tools" style="margin-top:16px">
            <button type="submit" class="ins-btn primary" id="plan-save">{{ $hasPlan ? 'Save changes' : 'Save action plan' }}</button>
            @if($hasPlan)<button type="button" class="ins-btn js-plan-cancel">Cancel</button><span class="help" id="plan-changed" aria-live="polite"></span>@endif
          </div>
        </form>
        @endif

        @if($hasPlan || count($changeSets))
        <div class="iss-plan-meta">
          <span>@if($lastPlan)Last changed {{ date('d M Y, H:i', strtotime($lastPlan->created_at)) }} by {{ $n($lastPlan->created_by) }}@endif</span>
          @if(count($changeSets))
          <div class="acts"><button type="button" class="ins-link js-history" aria-controls="plan-history" aria-expanded="false"><i class="fa fa-history" aria-hidden="true"></i> Change history ({{ count($changeSets) }})</button></div>
          @endif
        </div>
        @endif
        @if(count($changeSets))
        <div class="iss-history" id="plan-history" hidden>
          <h3>Change history</h3>
          @foreach($changeSets as $set)
          @php $first = $set->first(); $u = $first->update_id && isset($updatesById[$first->update_id]) ? $updatesById[$first->update_id] : null;
               $reason = $u && strpos($u->note, "\nReason: ") !== false ? substr($u->note, strpos($u->note, "\nReason: ") + 9) : null;
               $isAdd = $u && $u->note === 'Action plan added.'; @endphp
          <div class="iss-set">
            <div class="who"><b>{{ $n($first->changed_by) }}</b> · {{ date('d M Y, H:i', strtotime($first->changed_at)) }} ·
              {{ $isAdd ? 'added the plan' : ($u && $u->kind === 'followup' ? 'inspection follow-up (worsened)' : 'changed ' . $set->count() . ' field' . ($set->count() === 1 ? '' : 's')) }}</div>
            @if($reason)<div class="why">“{{ $reason }}”</div>@endif
            <table>
              <caption class="ins-sr">Changes on {{ date('d M Y H:i', strtotime($first->changed_at)) }}</caption>
              <tbody>
                @foreach($set->sortBy(function ($c) { $i = array_search($c->field, array_keys(I::TRACKED)); return $i === false ? 99 : $i; }) as $c)
                <tr><th scope="row">{{ isset(I::TRACKED[$c->field]) ? I::TRACKED[$c->field] : $c->field }}</th>
                  @if($isAdd || $c->old_value === null)<td colspan="3" class="new">{{ I::display($c->field, $c->new_value) }}</td>
                  @else<td class="old"><span class="ins-sr">From </span>{{ I::display($c->field, $c->old_value) }}</td><td class="arrow" aria-hidden="true">→</td><td class="new"><span class="ins-sr">to </span>{{ I::display($c->field, $c->new_value) }}</td>@endif</tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endforeach
        </div>
        @endif
      </section>

      {{-- Activity --}}
      <section class="ins-card" aria-labelledby="act-title">
        <div class="ins-card-head"><h2 id="act-title">Activity</h2><span class="small">{{ $updates->count() }} entr{{ $updates->count() === 1 ? 'y' : 'ies' }}</span></div>
        @if($canWork)
        <form method="post" action="{{ route('inspection.issues.progress', $issue->id) }}" enctype="multipart/form-data" id="progress-form" class="iss-compose">
          @csrf
          <div class="ins-field {{ $errors->has('note') && !$canVerify ? 'has-error' : '' }}"><label for="pg-note">Add a progress update</label>
            <textarea name="note" id="pg-note" required rows="2" maxlength="2000" placeholder="What has changed? e.g. Contractor on site, part ordered"></textarea></div>
          <div class="row2">
            <div class="ins-field"><label for="pg-photo">Photo (optional)</label><input type="file" name="photo" id="pg-photo" accept="image/*" capture="environment"></div>
            <button type="submit" class="ins-btn">Post update</button>
          </div>
        </form>
        @endif
        <ol class="iss-feed">
          @foreach($updates->reverse() as $u)
          @php $k = isset($kinds[$u->kind]) ? $kinds[$u->kind] : [ucfirst($u->kind), 'fa-circle']; @endphp
          <li class="k-{{ $u->kind }}"><span class="dot" aria-hidden="true"><i class="fa {{ $k[1] }}"></i></span>
            <div><div class="head"><b>{{ $k[0] }}@if($u->condition) — {{ I::CONDITIONS[$u->condition] }}@endif</b><span>{{ $n($u->created_by) }} · {{ date('d M Y, H:i', strtotime($u->created_at)) }}</span></div>
              @if($u->note)<p>{{ $u->note }}</p>@endif
              @if(isset($updatePhotos[$u->id]))<div class="iss-thumbs">@foreach($updatePhotos[$u->id] as $ph)<a href="{{ I::photoUrl($ph->path) }}" target="_blank" rel="noopener"><img src="{{ I::photoUrl($ph->thumb_path) }}" alt="{{ I::PHOTO_KINDS[$ph->kind] }} photo, {{ date('d M', strtotime($ph->created_at)) }}" loading="lazy"></a>@endforeach</div>@endif
            </div></li>
          @endforeach
        </ol>
      </section>
    </div>

    <div>
      <section class="ins-card iss-photos" aria-labelledby="ph-title">
        <div class="ins-card-head"><h2 id="ph-title">Photos</h2><span class="small">{{ $photos->count() }}</span></div>
        <div class="ins-card-body">
          @foreach(I::PHOTO_KINDS as $kind => $label)
            @if(isset($byKind[$kind]))
            <h3>{{ $label }} ({{ $byKind[$kind]->count() }})</h3>
            <div class="grid">@foreach($byKind[$kind] as $ph)<a href="{{ I::photoUrl($ph->path) }}" target="_blank" rel="noopener" title="{{ $label }} · {{ date('d M Y H:i', strtotime($ph->created_at)) }}"><img src="{{ I::photoUrl($ph->thumb_path) }}" alt="{{ $label }} photo, {{ date('d M', strtotime($ph->created_at)) }}" loading="lazy"></a>@endforeach</div>
            @endif
          @endforeach
          @if(!$photos->count())<p class="muted">No photos yet.</p>@endif
        </div>
      </section>

      <section class="ins-card" aria-labelledby="dt-title">
        <div class="ins-card-head"><h2 id="dt-title">Details</h2></div>
        <div class="ins-card-body">
          <dl class="iss-side-dl">
            <div><dt>Building</dt><dd>{{ $issue->building_name }}</dd></div>
            <div><dt>Location</dt><dd>{{ $issue->unit_no ? 'Unit ' . $issue->unit_no : 'Common area' }}@if($issue->area) · {{ $issue->area }}@endif</dd></div>
            <div><dt>Raised</dt><dd>{{ date('d M Y, H:i', strtotime($issue->raised_at)) }}</dd></div>
            <div><dt>Raised by</dt><dd>{{ $n($issue->raised_by) }}</dd></div>
            <div><dt>Days open</dt><dd>{{ I::days($issue->raised_at, $issue->closed_at) }}</dd></div>
            @if($issue->resolved_at)<div><dt>Resolved</dt><dd>{{ date('d M Y', strtotime($issue->resolved_at)) }} · {{ $n($issue->resolved_by) }}</dd></div>@endif
            @if($issue->closed_at)<div><dt>Closed</dt><dd>{{ date('d M Y', strtotime($issue->closed_at)) }} · {{ $n($issue->closed_by) }}</dd></div>@endif
            <div><dt>Complaint ticket</dt><dd>{{ $issue->complaint_no ? $issue->complaint_no . '-SUB1' : 'None' }}</dd></div>
          </dl>
          @if($canComplaint)
          <button type="button" class="ins-btn" style="width:100%;margin-top:16px" data-toggle="modal" data-target="#ins-complaint-modal"><i class="fa fa-ticket" aria-hidden="true"></i> Create complaint ticket</button>
          <p class="help" style="margin-top:8px">Optional — sends this issue to the maintenance team ({{ $issue->category }}).</p>
          @endif
        </div>
      </section>
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
            <div class="ins-field"><label for="cp-mob">Contact mobile <span class="req">*</span></label><input type="tel" name="complaint_mob_no" id="cp-mob" required maxlength="20" value="{{ old('complaint_mob_no', $me ? $me->employee_contact_no : '') }}"></div>
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
  // required fields: mark and stop, otherwise disable the button against double posts
  $('#plan-form, #resolve-form, #progress-form, #complaint-form').on('submit', function (e) {
    var ok = true, first = null;
    $(this).find('[required]').each(function () {
      var bad = this.type === 'file' ? !this.files.length : !$.trim($(this).val());
      $(this).closest('.ins-field').toggleClass('has-error', bad);
      if (bad) { ok = false; first = first || this; }
    });
    if (!ok) { e.preventDefault(); first.focus(); return; }
    $(this).find('[type=submit]').prop('disabled', true);
  });
  $(document).on('blur change', '#plan-form [required], #resolve-form [required]', function () {
    var bad = this.type === 'file' ? !this.files.length : !$.trim($(this).val());
    if (!bad) { $(this).closest('.ins-field').removeClass('has-error'); }
  });

  // action plan: view <-> edit, highlight what changed
  var $form = $('#plan-form'), $view = $('.js-plan-view'), $edit = $('.js-plan-edit');
  function norm(el) {
    var v = $.trim($(el).val() || '');
    if (el.id === 'ap-cost' && v !== '') { v = Number(v).toFixed(3); }
    return v.replace(/\r\n/g, '\n');
  }
  function diff() {
    var n = 0;
    $form.find('[data-orig]').each(function () {
      var changed = norm(this) !== String($(this).data('orig') === undefined ? '' : $(this).attr('data-orig')).replace(/\r\n/g, '\n').trim();
      $(this).closest('.ins-field').toggleClass('is-changed', changed && $view.length > 0);
      if (changed) { n++; }
    });
    if ($view.length) { $('#plan-changed').text(n ? n + ' field' + (n === 1 ? '' : 's') + ' changed' : 'No changes yet'); }
  }
  $form.on('input change', diff);
  $edit.on('click', function () {
    $view.prop('hidden', true); $form.prop('hidden', false); $edit.prop('hidden', true).attr('aria-expanded', 'true');
    diff(); $('#ap-action').focus();
  });
  $('.js-plan-cancel').on('click', function () {
    $form[0].reset(); $form.find('.ins-field').removeClass('has-error is-changed');
    $form.prop('hidden', true); $view.prop('hidden', false); $edit.prop('hidden', false).attr('aria-expanded', 'false').focus();
  });
  $('.js-history').on('click', function () {
    var open = $(this).attr('aria-expanded') === 'true';
    $(this).attr('aria-expanded', open ? 'false' : 'true');
    $('#plan-history').prop('hidden', open);
  });
  if (location.hash === '#history') { $('.js-history').trigger('click'); }

  // verify: keep the clicked decision (a disabled button is not posted)
  var decision = null;
  $('#verify-form [type=submit]').on('click', function () { decision = this.value; });
  $('#verify-form').on('submit', function (e) {
    if (decision === 'send_back' && !$.trim($('#vf-note').val())) {
      e.preventDefault(); $('#vf-note').focus().closest('.ins-field').addClass('has-error');
      return;
    }
    $('<input type="hidden" name="decision">').val(decision || 'close').appendTo(this);
    $(this).find('[type=submit]').prop('disabled', true);
  });
});
</script>
@endsection
