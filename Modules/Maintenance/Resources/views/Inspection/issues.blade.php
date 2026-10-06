@extends('layouts.plms-app')
@section('css')
@include('maintenance::Inspection._styles')
@endsection
@section('content')
@php use Modules\Maintenance\Services\Inspection as I; $canCreate = Auth::user()->can('inspection_issue_create'); $today = date('Y-m-d'); @endphp
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Building Inspection</div></div>{{ Breadcrumbs::render('inspection.issues') }}</div></div>
<div class="ins">
  <div class="ins-head">
    <div><h1>Issue register</h1><p class="sub">Every finding, from inspection to verified closure.</p></div>
    <div class="ins-tools">@if($canCreate)<button type="button" class="ins-btn primary" data-toggle="modal" data-target="#ins-issue-modal"><i class="fa fa-plus" aria-hidden="true"></i> New issue</button>@endif</div>
  </div>

  <div class="ins-kpis">
    <a class="ins-kpi" href="{{ route('inspection.issues') }}"><div class="k-label">Open issues</div><div class="value">{{ $counts->active }}</div><div class="foot">Not yet verified and closed</div></a>
    <a class="ins-kpi {{ $counts->critical ? 'alert' : '' }}" href="{{ route('inspection.issues', ['priority' => 1]) }}"><div class="k-label">Critical</div><div class="value">{{ $counts->critical }}</div><div class="foot">Target: 1 day</div></a>
    <a class="ins-kpi {{ $counts->overdue ? 'alert' : '' }}" href="{{ route('inspection.issues', ['overdue' => 1]) }}"><div class="k-label">Overdue</div><div class="value">{{ $counts->overdue }}</div><div class="foot">Past the target date, not resolved</div></a>
    <a class="ins-kpi" href="{{ route('inspection.issues', ['status' => 3]) }}"><div class="k-label">Awaiting verification</div><div class="value">{{ $counts->awaiting }}</div><div class="foot">Resolved by the executive</div></a>
  </div>

  <section class="ins-card">
    <form class="ins-filters" method="get" action="{{ route('inspection.issues') }}">
      <label>Search<input type="search" name="q" value="{{ $f['q'] }}" placeholder="Issue no, description, unit, complaint"></label>
      @if($seesAll)<label>Executive<select name="executive"><option value="">All executives</option>@foreach($executives as $id => $name)<option value="{{ $id }}" {{ (string) $f['executive'] === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></label>@endif
      <label>Building<select name="building"><option value="">All buildings</option>@foreach($buildings as $id => $name)<option value="{{ $id }}" {{ (string) $f['building'] === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></label>
      <label>Priority<select name="priority"><option value="">All priorities</option>@foreach(I::PRIORITIES as $k => $label)<option value="{{ $k }}" {{ (string) $f['priority'] === (string) $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></label>
      <label>Status<select name="status"><option value="active" {{ $f['status'] === 'active' ? 'selected' : '' }}>All not closed</option><option value="" {{ $f['status'] === '' || $f['status'] === null ? 'selected' : '' }}>All statuses</option>@foreach(I::ISSUE_STATUSES as $k => $label)<option value="{{ $k }}" {{ (string) $f['status'] === (string) $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></label>
      <label>Category<select name="category"><option value="">All categories</option>@foreach($categories as $id => $name)<option value="{{ $id }}" {{ (string) $f['category'] === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></label>
      <label class="check"><input type="checkbox" name="overdue" value="1" {{ $f['overdue'] ? 'checked' : '' }}> Overdue only</label>
      <button class="ins-btn sm primary" type="submit">Apply</button>
      <a class="ins-btn sm" href="{{ route('inspection.issues') }}">Reset</a>
    </form>
    <div class="ins-table-wrap">
      <table class="ins-table">
        <thead><tr><th>Issue</th><th>Building / Unit</th><th>Category</th><th>Priority</th><th>Status</th><th>Executive</th><th>Raised</th><th>Target</th><th class="num">Days open</th><th>Complaint</th></tr></thead>
        <tbody>
          @forelse($issues as $i)
          @php $late = $i->status < I::RESOLVED && $i->target_date < $today; @endphp
          <tr class="rowlink {{ $late ? 'overdue' : '' }}" data-href="{{ route('inspection.issues.show', $i->id) }}">
            <td><a href="{{ route('inspection.issues.show', $i->id) }}"><b>{{ $i->issue_no }}</b></a><br><small>{{ \Illuminate\Support\Str::limit($i->description, 80) }}</small></td>
            <td>{{ $i->building_name }}<br><small>{{ $i->unit_no ? 'Unit ' . $i->unit_no : ($i->area ?: 'Common area') }}</small></td>
            <td>{{ $i->category }}</td>
            <td><span class="ins-chip p{{ $i->priority }}">{{ I::PRIORITIES[$i->priority] }}</span></td>
            <td><span class="ins-chip s{{ $i->status }}">{{ I::ISSUE_STATUSES[$i->status] }}</span></td>
            <td>{{ isset($names[$i->executive_id]) ? $names[$i->executive_id] : '' }}<br><small>{{ I::SOURCES[$i->source] }}</small></td>
            <td class="ins-nw">{{ date('d M Y', strtotime($i->raised_at)) }}</td>
            <td class="ins-nw">{{ date('d M Y', strtotime($i->target_date)) }}@if($late)<br><small class="orange">{{ I::days($i->target_date) }}d overdue</small>@endif</td>
            <td class="num">{{ I::days($i->raised_at, $i->closed_at) }}</td>
            <td>{{ $i->complaint_no ?: '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="10" class="ins-empty">No issues match these filters.@if($canCreate)<br><button type="button" class="ins-link" data-toggle="modal" data-target="#ins-issue-modal" style="margin-top:8px">Record an issue</button>@endif</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="ins-card-foot"><span>{{ $issues->total() }} issue{{ $issues->total() === 1 ? '' : 's' }}</span><span>Targets: Critical 1 day · High 7 · Medium 15 · Low 30</span></div>
    @if($issues->hasPages())<div style="padding:0 24px 16px">{{ $issues->links() }}</div>@endif
  </section>

  @if($canCreate)
  <div class="modal fade" id="ins-issue-modal" tabindex="-1" role="dialog" aria-labelledby="ins-issue-title" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
      <form method="post" action="{{ route('inspection.issues.store') }}" enctype="multipart/form-data" id="ins-issue-form" novalidate>
        @csrf
        <div class="modal-header"><h2 class="modal-title" id="ins-issue-title">New issue</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        <div class="modal-body">
          <p class="ins-alert info">Record an issue without an inspection visit. {{ $seesAll ? 'You can choose any building.' : 'Only your buildings are listed.' }}</p>
          @if($errors->any() && old('description') !== null)<div class="ins-alert bad" role="alert">@foreach($errors->all() as $e){{ $e }}<br>@endforeach The photo has to be attached again.</div>@endif
          <div class="ins-form">
            <div class="ins-field full"><label for="ni-building">Building <span class="req">*</span></label>
              <select name="building_id" id="ni-building" class="ins-searchable" data-placeholder="Type a building name" required>
                <option value="">Select a building</option>
                @foreach($buildings as $id => $name)<option value="{{ $id }}" data-exec="{{ isset($buildingExec[$id]) ? $buildingExec[$id] : '' }}" {{ (string) old('building_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach
              </select></div>
            @if($seesAll)
            <div class="ins-field"><label for="ni-exec">Responsible executive <span class="req">*</span></label>
              <select name="executive_id" id="ni-exec" required><option value="">Select an executive</option>
                @foreach($executives as $id => $name)<option value="{{ $id }}" {{ (string) old('executive_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></div>
            @endif
            <div class="ins-field"><label for="ni-area">Area</label>
              <select name="area_id" id="ni-area"><option value="">—</option>@foreach($areas as $id => $name)<option value="{{ $id }}" {{ (string) old('area_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></div>
            <div class="ins-field"><label for="ni-cat">Category <span class="req">*</span></label>
              <select name="work_id" id="ni-cat" required><option value="">Select</option>@foreach($categories as $id => $name)<option value="{{ $id }}" {{ (string) old('work_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></div>
            <div class="ins-field"><label for="ni-pri">Priority <span class="req">*</span></label>
              <select name="priority" id="ni-pri" required>@foreach(I::PRIORITIES as $k => $label)<option value="{{ $k }}" data-days="{{ I::TARGET_DAYS[$k] }}" {{ (string) old('priority', 3) === (string) $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
              <span class="help" id="ni-target"></span></div>
            <div class="ins-field"><label for="ni-unit">Unit no (optional)</label>
              <select name="unit_id" id="ni-unit" data-old="{{ old('unit_id') }}"><option value="">Common area</option></select></div>
            <div class="ins-field full"><label for="ni-desc">Issue description <span class="req">*</span></label>
              <textarea name="description" id="ni-desc" required maxlength="2000" placeholder="Describe the issue and its exact location">{{ old('description') }}</textarea></div>
            <div class="ins-field full"><label for="ni-photo">Before photo <span class="req">*</span></label>
              <input type="file" name="photo" id="ni-photo" accept="image/*" capture="environment" required></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="ins-btn" data-dismiss="modal">Close</button><button type="submit" class="ins-btn primary">Create issue</button></div>
      </form>
    </div></div>
  </div>
  @endif
</div>
@endsection
@section('scripts')
@include('maintenance::Inspection._combo')
<script>
$(function () {
  $('tr.rowlink').on('click', function (e) { if (!$(e.target).closest('a,button').length) { window.location = $(this).data('href'); } });
  var unitsUrl = @json(route('inspection.issues.units', 0));
  function loadUnits(id) {
    var $u = $('#ni-unit').html('<option value="">Common area</option>');
    if (!id) { return; }
    $.getJSON(unitsUrl.replace(/0$/, id), function (rows) {
      $.each(rows, function (i, r) { $u.append($('<option></option>').val(r.id).text(r.unit_no)); });
      if ($u.data('old')) { $u.val(String($u.data('old'))); $u.data('old', ''); }
    });
  }
  $('#ni-building').on('change', function () {
    var exec = $(this).find('option:selected').data('exec');
    if (exec) { $('#ni-exec').val(String(exec)); }
    loadUnits(this.value);
  });
  function target() {
    var d = new Date(); d.setDate(d.getDate() + Number($('#ni-pri option:selected').data('days')));
    $('#ni-target').text('Target date: ' + d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }));
  }
  $('#ni-pri').on('change', target); target();
  if ($('#ni-building').val()) { loadUnits($('#ni-building').val()); }
  $('#ins-issue-form').on('submit', function (e) {
    var ok = true;
    $(this).find('[required]').each(function () {
      var bad = this.type === 'file' ? !this.files.length : !$.trim($(this).val());
      $(this).closest('.ins-field').toggleClass('has-error', bad);
      if (bad) { ok = false; }
    });
    if (!ok) { e.preventDefault(); } else { $(this).find('[type=submit]').prop('disabled', true); }
  });
  @if($errors->any() && old('description') !== null)
  $('#ins-issue-modal').modal('show');
  @endif
});
</script>
@endsection
