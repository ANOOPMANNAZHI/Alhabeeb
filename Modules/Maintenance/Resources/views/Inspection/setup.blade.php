@extends('layouts.plms-app')
@section('css')
@include('maintenance::Inspection._styles')
<style>
  .ins-assignbar { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; padding:16px 24px; border-bottom:1px solid var(--ins-line); background:#fafcfd; }
  .ins-assignbar .ins-field { min-width:220px; }
  .ins-execs { display:flex; flex-wrap:wrap; gap:8px; padding:16px 24px; border-bottom:1px solid var(--ins-line); }
  .ins-execs button { border:1px solid var(--ins-line); background:#fff; border-radius:16px; padding:4px 12px; font-size:13px; cursor:pointer; }
  .ins-execs button.on { background:var(--ins-blue); border-color:var(--ins-blue); color:#fff; }
  .ins-area { border-bottom:1px solid var(--ins-line); padding:16px 24px; }
  .ins-area:last-child { border-bottom:0; }
  .ins-area .items { margin:12px 0 0; padding:0; list-style:none; }
  .ins-area .items li { display:flex; gap:8px; align-items:center; padding:6px 0; border-top:1px dashed #eef2f6; }
  .ins-inline-form { display:flex; gap:8px; align-items:center; flex-wrap:wrap; flex:1; }
  .ins-inline-form input[type=text] { flex:1; min-width:180px; height:34px; border:1px solid #cfd8e1; border-radius:8px; padding:0 10px; }
  .ins-inline-form label { margin:0; font-size:13px; display:flex; gap:4px; align-items:center; color:var(--ins-muted); }
  .ins-off { opacity:.55; }
</style>
@endsection
@section('content')
@php use Modules\Maintenance\Services\Inspection as I; $unassigned = $buildings->whereStrict('user_id', null)->count(); @endphp
<div class="page-bar"><div class="page-title-breadcrumb">{{ Breadcrumbs::render('inspection.setup') }}</div></div>
<div class="ins">
  <div class="ins-head"><div><h1>Setup</h1><p class="sub">Who inspects which building, and what is checked on every visit.</p></div></div>
  <div class="ins-tabs" style="margin-bottom:16px">
    <a href="{{ route('inspection.setup') }}" class="{{ $tab === 'buildings' ? 'active' : '' }}">Building assignment</a>
    <a href="{{ route('inspection.setup', ['tab' => 'checklist']) }}" class="{{ $tab === 'checklist' ? 'active' : '' }}">Areas &amp; checklist</a>
  </div>

  @if($tab === 'buildings')
  <section class="ins-card">
    @if(!$executives)
    <div class="ins-alert warn" style="margin:16px 24px">No active employees found. Activate the employee and their login, then assign buildings here.</div>
    @endif
    <form method="post" action="{{ route('inspection.setup.assign') }}" id="assign-form">
      @csrf
      <div class="ins-assignbar">
        <div class="ins-field"><label for="as-user">Assign ticked buildings to</label>
          <select name="user_id" id="as-user" required><option value="">Select an executive</option>@foreach($executives as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach<option value="none">Nobody (unassign)</option></select></div>
        <button type="submit" class="ins-btn primary" id="as-btn" disabled>Assign <span id="as-count">0</span> building(s)</button>
        <span class="help">{{ $buildings->count() }} active buildings · {{ $unassigned }} without an executive</span>
      </div>
      <div class="ins-execs" role="group" aria-label="Filter by executive">
        <button type="button" class="on" data-exec="">All ({{ $buildings->count() }})</button>
        <button type="button" data-exec="none">Unassigned ({{ $unassigned }})</button>
        @foreach($executives as $id => $name)<button type="button" data-exec="{{ $id }}">{{ $name }} ({{ isset($counts[$id]) ? $counts[$id] : 0 }})</button>@endforeach
      </div>
      <div class="ins-filters" style="border-bottom:1px solid var(--ins-line)"><label style="flex:1">Search<input type="search" id="as-q" placeholder="Building name or code"></label></div>
      <div class="ins-table-wrap"><table class="ins-table" id="as-table">
        <thead><tr><th style="width:40px"><input type="checkbox" id="as-all" aria-label="Tick all shown buildings"></th><th>Building</th><th>Code</th><th>Executive</th><th>Since</th></tr></thead>
        <tbody>
          @foreach($buildings as $b)
          <tr data-exec="{{ $b->user_id ?: 'none' }}" data-q="{{ strtolower($b->building_name . ' ' . $b->building_code) }}">
            <td><input type="checkbox" name="building_ids[]" value="{{ $b->id }}" class="as-chk" aria-label="{{ $b->building_name }}"></td>
            <td><b>{{ $b->building_name }}</b></td><td>{{ $b->building_code }}</td>
            <td>{!! $b->user_id ? e(isset($names[$b->user_id]) ? $names[$b->user_id] : '') : '<span class="orange">Not assigned</span>' !!}</td>
            <td>{{ $b->assigned_from ? date('d M Y', strtotime($b->assigned_from)) : '—' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table></div>
    </form>
  </section>
  @else
  <section class="ins-card">
    <div class="ins-card-head"><div><h2>Areas &amp; checklist</h2><p class="small">Inactive areas and lines are not shown on new inspections. Past inspections keep what was checked.</p></div></div>
    @foreach($areas as $a)
    <div class="ins-area {{ $a->status ? '' : 'ins-off' }}">
      <form method="post" action="{{ route('inspection.setup.area') }}" class="ins-inline-form">@csrf
        <input type="hidden" name="id" value="{{ $a->id }}">
        <input type="text" name="name" value="{{ $a->name }}" required maxlength="100" aria-label="Area name" style="font-weight:600">
        <label><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" {{ $a->status ? 'checked' : '' }}> Active</label>
        <button class="ins-btn sm" type="submit">Save</button>
      </form>
      <ul class="items">
        @foreach(isset($items[$a->id]) ? $items[$a->id] : [] as $it)
        <li class="{{ $it->status ? '' : 'ins-off' }}"><form method="post" action="{{ route('inspection.setup.item') }}" class="ins-inline-form">@csrf
          <input type="hidden" name="id" value="{{ $it->id }}"><input type="hidden" name="area_id" value="{{ $a->id }}">
          <input type="text" name="name" value="{{ $it->name }}" required maxlength="200" aria-label="Checklist line">
          <label><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" {{ $it->status ? 'checked' : '' }}> Active</label>
          <button class="ins-btn sm" type="submit">Save</button></form></li>
        @endforeach
        <li><form method="post" action="{{ route('inspection.setup.item') }}" class="ins-inline-form">@csrf
          <input type="hidden" name="area_id" value="{{ $a->id }}">
          <input type="text" name="name" required maxlength="200" placeholder="New checklist line for {{ $a->name }}" aria-label="New checklist line for {{ $a->name }}">
          <button class="ins-btn sm primary" type="submit">Add line</button></form></li>
      </ul>
    </div>
    @endforeach
    <div class="ins-area"><form method="post" action="{{ route('inspection.setup.area') }}" class="ins-inline-form">@csrf
      <input type="text" name="name" required maxlength="100" placeholder="New area, e.g. Swimming Pool" aria-label="New area">
      <button class="ins-btn sm primary" type="submit">Add area</button></form></div>
  </section>
  @endif
</div>
@endsection
@section('scripts')
<script>
$(function () {
  var execFilter = '';
  function apply() {
    var q = $.trim($('#as-q').val() || '').toLowerCase();
    $('#as-table tbody tr').each(function () {
      var $r = $(this), show = (!execFilter || String($r.data('exec')) === execFilter) && (!q || String($r.data('q')).indexOf(q) !== -1);
      $r.toggle(show);
      if (!show) { $r.find('.as-chk').prop('checked', false); }
    });
    count();
  }
  function count() {
    var n = $('.as-chk:checked').length;
    $('#as-count').text(n); $('#as-btn').prop('disabled', n === 0 || !$('#as-user').val());
  }
  $('.ins-execs button').on('click', function () {
    $('.ins-execs button').removeClass('on'); $(this).addClass('on');
    execFilter = String($(this).data('exec')); $('#as-all').prop('checked', false); apply();
  });
  $('#as-q').on('input', apply);
  $('#as-user').on('change', count);
  $('#as-all').on('change', function () { $('#as-table tbody tr:visible .as-chk').prop('checked', this.checked); count(); });
  $(document).on('change', '.as-chk', count);
});
</script>
@endsection
