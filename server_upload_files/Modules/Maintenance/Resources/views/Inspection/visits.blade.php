@extends('layouts.plms-app')
@section('css')
@include('maintenance::Inspection._styles')
@endsection
@section('content')
@php use Modules\Maintenance\Services\Inspection as I; $canCreate = Auth::user()->can('inspection_visit_create'); @endphp
<div class="page-bar"><div class="page-title-breadcrumb">{{ Breadcrumbs::render('inspection.visits') }}</div></div>
<div class="ins">
  <div class="ins-head">
    <div>
      <h1>{{ $tab === 'mine' ? 'My schedule' : 'Inspection visits' }}</h1>
      <p class="sub">{{ $tab === 'mine' ? 'Your scheduled visits, and this month\'s completed and cancelled ones.' : 'Monthly inspections, follow-ups and additional visits.' }}</p>
    </div>
    <div class="ins-tools">
      @if($canCreate)<button type="button" class="ins-btn primary" data-toggle="modal" data-target="#ins-visit-modal"><i class="fa fa-plus" aria-hidden="true"></i> New visit</button>@endif
    </div>
  </div>

  @if($canExecute)
  <div class="ins-tabs" role="tablist" style="margin-bottom:16px">
    <a href="{{ route('inspection.visits', ['tab' => 'mine']) }}" class="{{ $tab === 'mine' ? 'active' : '' }}">My schedule</a>
    <a href="{{ route('inspection.visits', ['tab' => 'all']) }}" class="{{ $tab === 'all' ? 'active' : '' }}">{{ $seesAll ? 'All visits' : 'My visits by month' }}</a>
  </div>
  @endif

  <section class="ins-card">
    @if($tab === 'all')
    <form class="ins-filters" method="get" action="{{ route('inspection.visits') }}">
      <input type="hidden" name="tab" value="all">
      <label>Month<select name="month" onchange="this.form.submit()">@foreach($months as $k => $label)<option value="{{ $k }}" {{ $k === $month ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></label>
      @if($seesAll)
      <label>Executive<select name="executive" onchange="this.form.submit()"><option value="">All executives</option>@foreach($executives as $id => $name)<option value="{{ $id }}" {{ (string) $f['executive'] === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></label>
      @endif
      <label>Building<select name="building" onchange="this.form.submit()"><option value="">All buildings</option>@foreach($buildings as $id => $name)<option value="{{ $id }}" {{ (string) $f['building'] === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></label>
      <label>Type<select name="type" onchange="this.form.submit()"><option value="">All types</option>@foreach(I::VISIT_TYPES as $k => $label)<option value="{{ $k }}" {{ (string) $f['type'] === (string) $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></label>
      <label>Status<select name="status" onchange="this.form.submit()"><option value="">All statuses</option>@foreach(I::VISIT_STATUSES as $k => $label)<option value="{{ $k }}" {{ (string) $f['status'] === (string) $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></label>
      <noscript><button class="ins-btn sm">Apply</button></noscript>
    </form>
    @endif
    <div class="ins-table-wrap">
      <table class="ins-table">
        <thead><tr><th>Building</th><th>Visit</th><th>Planned</th>@if($tab === 'all')<th>Executive</th>@endif<th>Status</th><th class="num">Issues found</th><th><span class="ins-sr">Actions</span></th></tr></thead>
        <tbody>
          @forelse($visits as $v)
          @php $late = $v->status == I::SCHEDULED && $v->planned_date < date('Y-m-d'); $own = (int) $v->executive_id === (int) Auth::id(); @endphp
          <tr>
            <td><b>{{ $v->building_name }}</b><br><small>{{ $v->building_code }}</small></td>
            <td>{{ I::VISIT_TYPES[$v->visit_type] }}<br><small>{{ $v->visit_no }}@if($v->purpose) · {{ \Illuminate\Support\Str::limit($v->purpose, 60) }}@endif</small></td>
            <td class="ins-nw">{{ date('d M Y', strtotime($v->planned_date)) }}@if($late)<br><small class="orange">{{ I::days($v->planned_date) }} days late</small>@endif</td>
            @if($tab === 'all')<td>{{ isset($names[$v->executive_id]) ? $names[$v->executive_id] : '' }}</td>@endif
            <td><span class="ins-chip v{{ $v->status }}">{{ I::VISIT_STATUSES[$v->status] }}</span>
              @if($v->status == I::COMPLETED)<br><small>{{ date('d M Y', strtotime($v->completed_at)) }}</small>@endif
              @if($v->status == I::CANCELLED)<br><small title="{{ $v->cancel_reason }}">{{ \Illuminate\Support\Str::limit($v->cancel_reason, 40) }}</small>@endif</td>
            <td class="num">{{ $v->status == I::COMPLETED ? (isset($issueCounts[$v->id]) ? $issueCounts[$v->id] : 0) : '—' }}</td>
            <td class="num">
              @if($v->status == I::SCHEDULED)
                @if($own && $canExecute)<a class="ins-btn primary sm" href="{{ route('inspection.visits.inspect', $v->id) }}">{{ $v->started_at ? 'Continue inspection' : 'Start inspection' }}</a>@endif
                @if($own || $seesAll)<button type="button" class="ins-btn sm danger js-cancel" data-url="{{ route('inspection.visits.cancel', $v->id) }}" data-label="{{ $v->visit_no }} · {{ $v->building_name }}">Cancel</button>@endif
              @elseif($v->status == I::COMPLETED)
                <a class="ins-btn sm" href="{{ route('inspection.visits.show', $v->id) }}">View</a>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="7" class="ins-empty">
            {{ $tab === 'mine' ? 'You have no scheduled visits.' : 'No visits for these filters.' }}
            @if($canCreate)<br><button type="button" class="ins-link" data-toggle="modal" data-target="#ins-visit-modal" style="margin-top:8px">Schedule a visit</button>@endif
          </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="ins-card-foot"><span>{{ $visits->count() }} visit{{ $visits->count() === 1 ? '' : 's' }}</span>
      @if($tab === 'all')<span>{{ $visits->where('status', I::COMPLETED)->count() }} completed · {{ $visits->where('status', I::SCHEDULED)->count() }} scheduled · {{ $visits->where('status', I::CANCELLED)->count() }} cancelled</span>@endif</div>
  </section>

  @if($canCreate)
  <div class="modal fade" id="ins-visit-modal" tabindex="-1" role="dialog" aria-labelledby="ins-visit-title" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
      <form method="post" action="{{ route('inspection.visits.store') }}" id="ins-visit-form" novalidate>
        @csrf
        <div class="modal-header"><h2 class="modal-title" id="ins-visit-title">New visit</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        <div class="modal-body">
          @if($errors->any() && old('visit_type'))<div class="ins-alert bad" role="alert">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
          @if(!$buildings)<div class="ins-alert warn">No buildings are assigned to you yet. Ask your manager to assign your buildings in Building Inspection → Setup.</div>@endif
          <div class="ins-form">
            <div class="ins-field full"><label for="iv-building">Building <span class="req">*</span></label>
              <select name="building_id" id="iv-building" class="ins-searchable" data-placeholder="Type a building name" required>
                <option value="">Select a building</option>
                @foreach($buildings as $id => $name)<option value="{{ $id }}" data-exec="{{ isset($buildingExec[$id]) ? $buildingExec[$id] : '' }}" {{ (string) old('building_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach
              </select></div>
            <div class="ins-field"><label for="iv-type">Visit type <span class="req">*</span></label>
              <select name="visit_type" id="iv-type" required>@foreach(I::VISIT_TYPES as $k => $label)<option value="{{ $k }}" {{ (string) old('visit_type', 1) === (string) $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
            <div class="ins-field"><label for="iv-date">Planned visit date <span class="req">*</span></label>
              <input type="date" name="planned_date" id="iv-date" value="{{ old('planned_date', date('Y-m-d')) }}" required></div>
            <div class="ins-field full"><label for="iv-exec">Assigned executive</label>
              @if($seesAll)
                <select name="executive_id" id="iv-exec" required><option value="">Select an executive</option>
                  @foreach($executives as $id => $name)<option value="{{ $id }}" {{ (string) old('executive_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select>
                <span class="help" id="iv-exec-help">Filled with the building's executive; you can choose another.</span>
              @else
                <input type="text" id="iv-exec" value="{{ \Modules\Maintenance\Services\Inspection::userName(Auth::id()) }}" readonly>
                <span class="help">You will carry out this visit.</span>
              @endif
            </div>
            <div class="ins-field full"><label for="iv-purpose">Purpose / notes</label>
              <textarea name="purpose" id="iv-purpose" maxlength="2000" placeholder="e.g. Follow-up on the booster pump repair">{{ old('purpose') }}</textarea></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="ins-btn" data-dismiss="modal">Close</button><button type="submit" class="ins-btn primary">Create visit</button></div>
      </form>
    </div></div>
  </div>
  @endif

  <div class="modal fade" id="ins-cancel-modal" tabindex="-1" role="dialog" aria-labelledby="ins-cancel-title" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
      <form method="post" id="ins-cancel-form">
        @csrf
        <div class="modal-header"><h2 class="modal-title" id="ins-cancel-title">Cancel visit</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        <div class="modal-body">
          <p style="margin-bottom:16px"><b id="ins-cancel-label"></b></p>
          <div class="ins-field"><label for="ic-reason">Reason for cancelling <span class="req">*</span></label>
            <textarea name="cancel_reason" id="ic-reason" required minlength="10" maxlength="1000" placeholder="e.g. Building access refused by the watchman; rescheduled for next week"></textarea>
            <span class="help">At least 10 characters. The reason is kept with the visit.</span></div>
        </div>
        <div class="modal-footer"><button type="button" class="ins-btn" data-dismiss="modal">Keep visit</button><button type="submit" class="ins-btn danger">Cancel visit</button></div>
      </form>
    </div></div>
  </div>
</div>
@endsection
@section('scripts')
@include('maintenance::Inspection._combo')
<script>
$(function () {
  $('#iv-building').on('change', function () {
    var exec = $(this).find('option:selected').data('exec');
    var $e = $('select#iv-exec');
    if ($e.length) {
      if (exec) { $e.val(String(exec)); }
      $('#iv-exec-help').text(exec ? 'Filled with the building\'s executive; you can choose another.' : 'This building has no executive assigned. Choose one.');
    }
  });
  $('.js-cancel').on('click', function () {
    $('#ins-cancel-form').attr('action', $(this).data('url'));
    $('#ins-cancel-label').text($(this).data('label'));
    $('#ic-reason').val('');
    $('#ins-cancel-modal').modal('show');
  });
  $('#ins-cancel-form').on('submit', function (e) {
    if ($.trim($('#ic-reason').val()).length < 10) { e.preventDefault(); $('#ic-reason').focus().closest('.ins-field').addClass('has-error'); }
  });
  $('#ins-visit-form').on('submit', function (e) {
    var ok = true;
    $(this).find('[required]').each(function () {
      var bad = !$.trim($(this).val());
      $(this).closest('.ins-field').toggleClass('has-error', bad);
      if (bad) { ok = false; }
    });
    if (!ok) { e.preventDefault(); } else { $(this).find('[type=submit]').prop('disabled', true); }
  });
  @if($errors->any() && old('visit_type'))
  $('#ins-visit-modal').modal('show');
  @endif
});
</script>
@endsection
