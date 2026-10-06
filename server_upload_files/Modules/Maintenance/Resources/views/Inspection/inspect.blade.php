@extends('layouts.plms-app')
@section('css')
@include('maintenance::Inspection._styles')
<style>
  .ins-pending { padding:16px 24px; border-bottom:1px solid var(--ins-line); }
  .ins-pending:last-child { border-bottom:0; }
  .ins-pending .top { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-bottom:4px; }
  .ins-pending h3 { margin:4px 0 12px; }
  .ins-check { padding:12px 24px; border-bottom:1px solid #eef2f6; }
  .ins-check:last-child { border-bottom:0; }
  .ins-check .row1 { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
  .ins-seg { display:inline-flex; border:1px solid #cfd8e1; border-radius:8px; overflow:hidden; flex:none; }
  .ins-seg label { margin:0; position:relative; }
  .ins-seg input { position:absolute; opacity:0; width:1px; height:1px; }
  .ins-seg span { display:inline-block; min-width:64px; padding:8px 12px; text-align:center; font-weight:500; cursor:pointer; background:#fff; border-left:1px solid #cfd8e1; user-select:none; }
  .ins-seg label:first-child span { border-left:0; }
  .ins-seg input:focus-visible + span { outline:2px solid var(--ins-accent); outline-offset:-2px; }
  .ins-seg input.ok:checked + span { background:var(--ins-ok); color:#fff; }
  .ins-seg input.issue:checked + span { background:var(--ins-orange); color:#fff; }
  .ins-seg input.na:checked + span { background:#6b7785; color:#fff; }
  .ins-seg input.c1:checked + span { background:var(--ins-blue); color:#fff; }
  .ins-seg input.c2:checked + span { background:var(--ins-ok); color:#fff; }
  .ins-seg input.c3:checked + span { background:var(--ins-bad); color:#fff; }
  .ins-inline { margin-top:12px; padding:16px; border-radius:8px; background:#fff8f3; border:1px solid #fbd9c4; }
  .ins-areacount { font-size:13px; color:var(--ins-muted); }
  .ins-areacount.done { color:var(--ins-ok); font-weight:500; }
  .ins-photo-preview { display:flex; gap:8px; margin-top:8px; }
  .ins-photo-preview img { width:96px; height:72px; object-fit:cover; border-radius:6px; border:1px solid var(--ins-line); }
  .ins-sticky { position:sticky; bottom:0; z-index:5; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;
    background:#fff; border:1px solid var(--ins-line); border-radius:10px; padding:12px 24px; box-shadow:0 -4px 16px rgba(23,45,69,.08); margin-bottom:24px; }
  .ins-errors { margin:0; padding-left:18px; }
  @media (max-width:575px) {
    .ins-check, .ins-pending { padding-left:16px; padding-right:16px; }
    .ins-seg { width:100%; } .ins-seg label { flex:1; } .ins-seg span { width:100%; min-width:0; }
    .ins-sticky { padding:12px 16px; }
    .ins-sticky .ins-btn { width:100%; }
  }
</style>
@endsection
@section('content')
@php use Modules\Maintenance\Services\Inspection as I; $totalItems = 0; foreach ($areas as $a) { $totalItems += count($items[$a->id]); } @endphp
<div class="page-bar"><div class="page-title-breadcrumb">{{ Breadcrumbs::render('inspection.visits.inspect', $visit) }}</div></div>
<div class="ins">
  <div class="ins-head">
    <div><h1>{{ $visit->building_name }}</h1><p class="sub">{{ I::VISIT_TYPES[$visit->visit_type] }} · {{ $visit->visit_no }}</p></div>
    <div class="ins-tools"><a href="{{ route('inspection.visits', ['tab' => 'mine']) }}" class="ins-btn">Back to my schedule</a></div>
  </div>

  @if($errors->any())
  <div class="ins-alert bad" role="alert" id="ins-server-errors"><b>Please complete the following. Photos have to be attached again.</b>
    <ul class="ins-errors">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif
  <div class="ins-alert bad" role="alert" id="ins-client-errors" hidden></div>

  <form method="post" action="{{ route('inspection.visits.submit', $visit->id) }}" enctype="multipart/form-data" id="ins-form" novalidate>
  @csrf
  <input type="hidden" name="latitude" id="ins-lat"><input type="hidden" name="longitude" id="ins-lng">

  <section class="ins-card">
    <div class="ins-card-head"><h2>Building and visit</h2><small id="ins-gps">Location not captured</small></div>
    <div class="ins-card-body">
      <div class="ins-meta">
        <div><small>Building code</small><b>{{ $visit->building_code ?: '—' }}</b></div>
        <div><small>Location</small><b>{{ $visit->location_name ?: '—' }}</b></div>
        <div><small>Floors</small><b>{{ $visit->building_no_floor ?: '—' }}</b></div>
        <div><small>Watchman</small><b>{{ $visit->watchman_no ?: '—' }}</b></div>
        <div><small>Executive</small><b>{{ $executiveName }}</b></div>
        <div><small>Planned date</small><b>{{ date('d M Y', strtotime($visit->planned_date)) }}</b></div>
        <div><small>Visit month</small><b>{{ date('F Y', strtotime($visit->visit_month)) }}</b></div>
        <div><small>Last inspection</small><b>{{ $lastVisit ? date('d M Y', strtotime($lastVisit->completed_at)) : 'None yet' }}</b></div>
      </div>
      @if($visit->building_address)<p class="muted" style="margin-top:16px">{{ $visit->building_address }}</p>@endif
      @if($visit->purpose)<div class="ins-alert info" style="margin:16px 0 0"><b>Purpose:</b> {{ $visit->purpose }}</div>@endif
    </div>
  </section>

  <section class="ins-card" aria-labelledby="ins-pending-title">
    <div class="ins-card-head"><div><h2 id="ins-pending-title">Pending issues in this building</h2><p class="small">Update the current condition of every open issue.</p></div>
      <span class="ins-chip {{ count($pending) ? 'late' : 'v2' }}">{{ count($pending) }} open</span></div>
    @forelse($pending as $p)
    @php $cond = old('followup.' . $p->id . '.condition'); @endphp
    <div class="ins-pending" data-pending="{{ $p->id }}" data-no="{{ $p->issue_no }}">
      <div class="top"><small>{{ $p->issue_no }} · raised {{ date('d M Y', strtotime($p->raised_at)) }} · target {{ date('d M Y', strtotime($p->target_date)) }}</small>
        <span class="ins-chip p{{ $p->priority }}">{{ I::PRIORITIES[$p->priority] }}</span><span class="ins-chip s{{ $p->status }}">{{ I::ISSUE_STATUSES[$p->status] }}</span>
        @if($p->target_date < date('Y-m-d'))<span class="ins-chip late">{{ I::days($p->target_date) }}d overdue</span>@endif</div>
      <h3>{{ $p->description }}</h3>
      <p class="small" style="margin:-8px 0 12px">{{ $p->category }}@if($p->area) · {{ $p->area }}@endif @if($p->unit_no) · Unit {{ $p->unit_no }}@endif
        · <a href="{{ route('inspection.issues.show', $p->id) }}" target="_blank" rel="noopener">Open issue</a></p>
      <div class="ins-form">
        <div class="ins-field full"><span id="cond-l-{{ $p->id }}">Current condition <span class="req">*</span></span>
          <div class="ins-seg" role="radiogroup" aria-labelledby="cond-l-{{ $p->id }}">
            @foreach(I::CONDITIONS as $k => $label)
            <label><input type="radio" class="c{{ $k }} js-cond" name="followup[{{ $p->id }}][condition]" value="{{ $k }}" {{ (string) $cond === (string) $k ? 'checked' : '' }}><span>{{ $label }}</span></label>
            @endforeach
          </div>
          <span class="help js-worse-help" @if((string) $cond !== '3') hidden @endif>Priority goes up one level and the target date is brought forward if needed.</span></div>
        <div class="ins-field"><label for="fu-note-{{ $p->id }}">Update note <span class="req">*</span></label>
          <textarea id="fu-note-{{ $p->id }}" name="followup[{{ $p->id }}][note]" placeholder="What did you find?">{{ old('followup.' . $p->id . '.note') }}</textarea></div>
        <div class="ins-field"><label for="fu-photo-{{ $p->id }}"><span class="js-photo-label">{{ (string) $cond === '2' ? 'After photo' : 'Photo (optional)' }}</span> <span class="req js-photo-req" @if((string) $cond !== '2') hidden @endif>*</span></label>
          <input type="file" id="fu-photo-{{ $p->id }}" name="followup_photo[{{ $p->id }}]" accept="image/*" capture="environment" class="js-photo">
          <div class="ins-photo-preview"></div></div>
      </div>
    </div>
    @empty
    <div class="ins-empty">No open issues in this building.</div>
    @endforelse
  </section>

  @foreach($areas as $n => $a)
  <section class="ins-card js-area" aria-labelledby="area-{{ $a->id }}">
    <div class="ins-card-head"><h2 id="area-{{ $a->id }}"><span class="muted" style="font-size:13px;margin-right:8px">{{ str_pad($n + 1, 2, '0', STR_PAD_LEFT) }}</span>{{ $a->name }}</h2>
      <div class="ins-tools"><span class="ins-areacount js-areacount">0 / {{ count($items[$a->id]) }} checked</span>
        <button type="button" class="ins-btn sm js-rest-ok">Mark rest OK</button></div></div>
    @foreach($items[$a->id] as $it)
    @php $r = old('results.' . $it->id); $o = 'issue.' . $it->id . '.'; @endphp
    <div class="ins-check js-item" data-item="{{ $it->id }}" data-label="{{ $a->name }} – {{ $it->name }}">
      <div class="row1"><span id="item-l-{{ $it->id }}">{{ $it->name }}</span>
        <div class="ins-seg" role="radiogroup" aria-labelledby="item-l-{{ $it->id }}">
          @foreach([1 => 'ok', 2 => 'issue', 3 => 'na'] as $k => $cls)
          <label><input type="radio" class="{{ $cls }} js-result" name="results[{{ $it->id }}]" value="{{ $k }}" {{ (string) $r === (string) $k ? 'checked' : '' }}><span>{{ I::RESULTS[$k] }}</span></label>
          @endforeach
        </div></div>
      <div class="ins-inline js-issue" @if((string) $r !== '2') hidden @endif>
        <div class="ins-form">
          <div class="ins-field full"><label for="is-d-{{ $it->id }}">Issue description <span class="req">*</span></label>
            <textarea id="is-d-{{ $it->id }}" name="issue[{{ $it->id }}][description]" placeholder="Describe the finding and its exact location">{{ old($o . 'description') }}</textarea></div>
          <div class="ins-field"><label for="is-c-{{ $it->id }}">Category <span class="req">*</span></label>
            <select id="is-c-{{ $it->id }}" name="issue[{{ $it->id }}][work_id]"><option value="">Select</option>
              @foreach($categories as $id => $name)<option value="{{ $id }}" {{ (string) old($o . 'work_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></div>
          <div class="ins-field"><label for="is-p-{{ $it->id }}">Priority <span class="req">*</span></label>
            <select id="is-p-{{ $it->id }}" name="issue[{{ $it->id }}][priority]">
              @foreach(I::PRIORITIES as $k => $label)<option value="{{ $k }}" {{ (string) old($o . 'priority', 3) === (string) $k ? 'selected' : '' }}>{{ $label }} — {{ I::TARGET_DAYS[$k] }} day{{ I::TARGET_DAYS[$k] > 1 ? 's' : '' }}</option>@endforeach</select></div>
          <div class="ins-field"><label for="is-u-{{ $it->id }}">Unit no (optional)</label>
            <select id="is-u-{{ $it->id }}" name="issue[{{ $it->id }}][unit_id]"><option value="">Common area</option>
              @foreach($units as $id => $no)<option value="{{ $id }}" {{ (string) old($o . 'unit_id') === (string) $id ? 'selected' : '' }}>{{ $no }}</option>@endforeach</select></div>
          <div class="ins-field"><label for="is-f-{{ $it->id }}">Before photo <span class="req">*</span></label>
            <input type="file" id="is-f-{{ $it->id }}" name="issue_photo[{{ $it->id }}]" accept="image/*" capture="environment" class="js-photo">
            <div class="ins-photo-preview"></div></div>
        </div>
      </div>
    </div>
    @endforeach
  </section>
  @endforeach

  <section class="ins-card">
    <div class="ins-card-head"><h2><label for="ins-remarks" style="margin:0">General remarks</label></h2></div>
    <div class="ins-card-body"><div class="ins-field"><textarea id="ins-remarks" name="general_remarks" maxlength="4000" placeholder="Any other observations from this visit">{{ old('general_remarks') }}</textarea></div></div>
  </section>

  <div class="ins-sticky">
    <div><b id="ins-progress">0 of {{ $totalItems }} lines checked</b><div class="help" id="ins-progress-sub">0 new issues</div></div>
    <button type="submit" class="ins-btn primary" id="ins-submit"><i class="fa fa-check" aria-hidden="true"></i> Submit inspection</button>
  </div>
  </form>
</div>
@endsection
@section('scripts')
<script>
$(function () {
  var $form = $('#ins-form'), total = {{ $totalItems }}, dirty = false, submitting = false;

  function refresh() {
    var checked = 0, issues = 0;
    $('.js-area').each(function () {
      var $a = $(this), n = $a.find('.js-item').length, c = $a.find('.js-result:checked').length;
      checked += c;
      $a.find('.js-areacount').text(c + ' / ' + n + ' checked').toggleClass('done', c === n);
    });
    issues = $('.js-result.issue:checked').length;
    $('#ins-progress').text(checked + ' of ' + total + ' lines checked');
    $('#ins-progress-sub').text(issues + ' new issue' + (issues === 1 ? '' : 's'));
  }
  $form.on('change', '.js-result', function () {
    $(this).closest('.js-item').find('.js-issue').prop('hidden', this.value !== '2');
    refresh();
  });
  $form.on('change', '.js-cond', function () {
    var $p = $(this).closest('.ins-pending'), resolved = this.value === '2';
    $p.find('.js-photo-label').text(resolved ? 'After photo' : 'Photo (optional)');
    $p.find('.js-photo-req').prop('hidden', !resolved);
    $p.find('.js-worse-help').prop('hidden', this.value !== '3');
  });
  $('.js-rest-ok').on('click', function () {
    $(this).closest('.js-area').find('.js-item').each(function () {
      if (!$(this).find('.js-result:checked').length) { $(this).find('.js-result.ok').prop('checked', true).trigger('change'); }
    });
    dirty = true;
  });
  $form.on('input change', function () { dirty = true; });
  window.addEventListener('beforeunload', function (e) { if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; } });

  // photos: preview, and shrink large phone photos before upload (max 1600px JPEG) where the browser allows it
  $form.on('change', '.js-photo', function () {
    var input = this, file = input.files && input.files[0], $pv = $(input).siblings('.ins-photo-preview').empty();
    if (!file) { return; }
    if (!/^image\//.test(file.type)) { alert('Choose an image file (JPG or PNG).'); input.value = ''; return; }
    var url = URL.createObjectURL(file);
    $pv.append($('<img alt="Selected photo preview">').attr('src', url));
    if (file.size < 600 * 1024 || typeof DataTransfer === 'undefined' || !window.HTMLCanvasElement) { return; }
    var img = new Image();
    img.onload = function () {
      var scale = Math.min(1, 1600 / Math.max(img.width, img.height)), c = document.createElement('canvas');
      c.width = Math.round(img.width * scale); c.height = Math.round(img.height * scale);
      c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
      c.toBlob(function (blob) {
        if (!blob || blob.size >= file.size) { return; }
        try {
          var dt = new DataTransfer();
          dt.items.add(new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }));
          input.files = dt.files;
        } catch (err) { /* keep the original file */ }
      }, 'image/jpeg', 0.82);
    };
    img.src = url;
  });

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function (pos) {
      $('#ins-lat').val(pos.coords.latitude.toFixed(7)); $('#ins-lng').val(pos.coords.longitude.toFixed(7));
      $('#ins-gps').text('Location captured · ' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4));
    }, function () { $('#ins-gps').text('Location not shared'); }, { enableHighAccuracy: true, timeout: 15000 });
  }

  $form.on('submit', function (e) {
    var errors = [], missing = 0, firstBad = null;
    $('.ins-field').removeClass('has-error');
    function bad($el, msg) { errors.push(msg); $el.closest('.ins-field').addClass('has-error'); if (!firstBad) { firstBad = $el; } }
    $('.ins-pending').each(function () {
      var $p = $(this), no = $p.data('no'), cond = $p.find('.js-cond:checked').val(), $note = $p.find('textarea');
      if (!cond) { bad($p.find('.ins-seg'), no + ': select the current condition.'); }
      if (!$.trim($note.val())) { bad($note, no + ': add an update note.'); }
      if (cond === '2' && !$p.find('.js-photo')[0].files.length) { bad($p.find('.js-photo'), no + ': attach an after photo to mark it resolved.'); }
    });
    $('.js-item').each(function () {
      var $i = $(this), label = $i.data('label'), r = $i.find('.js-result:checked').val();
      if (!r) { missing++; if (!firstBad) { firstBad = $i; } return; }
      if (r !== '2') { return; }
      var $d = $i.find('textarea'), $c = $i.find('select[name$="[work_id]"]'), $f = $i.find('.js-photo');
      if (!$.trim($d.val())) { bad($d, label + ': add an issue description.'); }
      if (!$c.val()) { bad($c, label + ': select a category.'); }
      if (!$f[0].files.length) { bad($f, label + ': attach a before photo.'); }
    });
    if (missing) { errors.unshift('Mark every checklist line: ' + missing + ' still need OK, Issue or N/A.'); }
    if (errors.length) {
      e.preventDefault();
      var $box = $('#ins-client-errors').empty().prop('hidden', false).append('<b>Please complete the following:</b>');
      var $ul = $('<ul class="ins-errors"></ul>').appendTo($box);
      $.each(errors.slice(0, 12), function (i, m) { $ul.append($('<li></li>').text(m)); });
      if (errors.length > 12) { $ul.append($('<li></li>').text('… and ' + (errors.length - 12) + ' more.')); }
      $('html, body').animate({ scrollTop: (firstBad || $box).offset().top - 120 }, 200);
      return;
    }
    submitting = true;
    $('#ins-submit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Submitting…');
  });
  refresh();
});
</script>
@endsection
