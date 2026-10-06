@extends('layouts.plms-app')
@section('css')
@include('maintenance::Inspection._styles')
<style>
  .ins-legend { display:flex; flex-wrap:wrap; gap:16px; font-size:12px; color:var(--ins-muted); margin-bottom:16px; }
  .ins-legend i { display:inline-block; width:10px; height:10px; border-radius:2px; margin-right:6px; vertical-align:-1px; }
  .ins-bars { display:grid; grid-template-columns:80px 1fr 48px; gap:8px 12px; align-items:center; }
  .ins-bar { height:14px; background:#eef2f6; border-radius:4px; overflow:hidden; display:flex; }
  .ins-bar span { display:block; height:100%; }
  .ins-exec { margin-bottom:16px; }
  .ins-exec:last-child { margin-bottom:0; }
  .ins-table.compact th, .ins-table.compact td { padding:8px 8px; }
  .ins-table.compact th { letter-spacing:0; }
  .ins-matrix td.n { text-align:center; }
  .ins-matrix td.n b { display:inline-block; min-width:26px; padding:1px 6px; border-radius:6px; background:var(--ins-blue-100); color:var(--ins-blue-700); font-weight:600; }
  .ins-matrix td.z { text-align:center; color:#c4ced8; }
  .ins-matrix th:not(:first-child) { text-align:center; }
</style>
@endsection
@section('content')
@php
  use Modules\Maintenance\Services\Inspection as I;
  use Modules\Maintenance\Services\InspectionReports as R;
  $nm = function ($id) use ($names) { return isset($names[$id]) ? $names[$id] : 'User ' . $id; };
  $monthColors = ['#bedbea', '#5b9bd0', '#005baa', '#00487f', '#00aee6', '#7cc8e8'];
  $ageColors = ['#00aee6', '#005baa', '#f26522', '#9b2c0e'];
  $agingMax = 1; foreach ($aging as $row) { $agingMax = max($agingMax, array_sum($row)); }
  $exportQuery = request()->query();
@endphp
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Building Inspection</div></div>{{ Breadcrumbs::render('inspection.reports') }}</div></div>
<div class="ins">
  <div class="ins-head">
    <div><h1>Inspection reports</h1><p class="sub">{{ $period }} · closed means verified by a supervisor</p></div>
    <div class="ins-tools"><a class="ins-btn" href="{{ route('inspection.reports.export', $exportQuery) }}"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export to Excel</a></div>
  </div>

  <section class="ins-card">
    <form class="ins-filters" method="get" action="{{ route('inspection.reports') }}" style="border-bottom:0">
      <label>From<select name="from">@foreach($months as $k => $label)<option value="{{ $k }}" {{ $k === $from ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></label>
      <label>To<select name="to">@foreach($months as $k => $label)<option value="{{ $k }}" {{ $k === $to ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></label>
      <label>Executive<select name="executive"><option value="">All executives</option>@foreach($executives as $id => $name)<option value="{{ $id }}" {{ (string) $executive === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></label>
      <label>Building<select name="building"><option value="">All buildings</option>@foreach($buildings as $id => $name)<option value="{{ $id }}" {{ (string) $building === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></label>
      <button class="ins-btn sm primary" type="submit">Apply</button>
    </form>
  </section>

  <div class="ins-grid2" style="margin-bottom:24px">
    <section class="ins-card">
      <div class="ins-card-head"><div><h2>Visit compliance by executive</h2><p class="small">Completed visits as a percentage of monthly visits due</p></div></div>
      <div class="ins-card-body">
        @if(count($compliance['rows']))
        <div class="ins-legend">@foreach(array_values($compliance['months']) as $k => $label)<span><i style="background:{{ $monthColors[$k % count($monthColors)] }}"></i>{{ $label }}</span>@endforeach</div>
        @foreach($compliance['rows'] as $e => $months_)
        <div class="ins-exec"><b class="small" style="color:var(--ins-text)">{{ $nm($e) }}</b>
          <div class="ins-bars" style="margin-top:8px">
            @foreach($compliance['months'] as $m => $label)
            @php $v = isset($months_[$m]) ? $months_[$m] : [0, 0]; $pc = $v[0] ? round($v[1] / $v[0] * 100) : null; $k = array_search($m, array_keys($compliance['months'])); @endphp
            <small>{{ $label }}</small>
            <div class="ins-bar" title="{{ $v[1] }} of {{ $v[0] }} completed"><span style="width:{{ (int) $pc }}%;background:{{ $monthColors[$k % count($monthColors)] }}"></span></div>
            <b class="small" style="color:var(--ins-text)">{{ $pc === null ? '—' : $pc . '%' }}</b>
            @endforeach
          </div></div>
        @endforeach
        @else
        <p class="ins-empty">No monthly inspections were scheduled in this period.</p>
        @endif
      </div>
    </section>

    <section class="ins-card">
      <div class="ins-card-head"><div><h2>Open issue aging by priority</h2><p class="small">Days since raised · includes resolved, awaiting verification · as on {{ date('d M Y') }}</p></div></div>
      <div class="ins-card-body">
        <div class="ins-legend">@foreach(array_keys(R::AGE_BUCKETS) as $k => $b)<span><i style="background:{{ $ageColors[$k] }}"></i>{{ $b }}</span>@endforeach</div>
        <div class="ins-bars">
          @foreach($aging as $p => $row)
          <span>{{ I::PRIORITIES[$p] }}</span>
          <div class="ins-bar">@foreach(array_values($row) as $k => $cnt)@if($cnt)<span title="{{ $cnt }} issue{{ $cnt === 1 ? '' : 's' }}, {{ array_keys(R::AGE_BUCKETS)[$k] }}" style="width:{{ $cnt / $agingMax * 100 }}%;background:{{ $ageColors[$k] }}"></span>@endif @endforeach</div>
          <b>{{ array_sum($row) }}</b>
          @endforeach
        </div>
        <div class="ins-table-wrap" style="margin-top:16px"><table class="ins-table compact"><thead><tr><th>Priority</th>@foreach(array_keys(R::AGE_BUCKETS) as $b)<th class="num">{{ $b }}</th>@endforeach<th class="num">Total</th></tr></thead><tbody>
          @foreach($aging as $p => $row)<tr><td>{{ I::PRIORITIES[$p] }}</td>@foreach($row as $cnt)<td class="num">{{ $cnt ?: '—' }}</td>@endforeach<td class="num"><b>{{ array_sum($row) }}</b></td></tr>@endforeach
        </tbody></table></div>
      </div>
    </section>
  </div>

  <section class="ins-card">
    <div class="ins-card-head"><div><h2>Issues by building and category</h2><p class="small">All issues raised in {{ $period }}, including verified closures</p></div></div>
    <div class="ins-table-wrap"><table class="ins-table ins-matrix"><thead><tr><th>Building</th>@foreach($matrix['categories'] as $c)<th>{{ $c }}</th>@endforeach<th>Total</th></tr></thead><tbody>
      @forelse($matrix['rows'] as $b => $cats)
      <tr><td><b>{{ $b }}</b></td>@foreach($matrix['categories'] as $id => $c)@if(!empty($cats[$id]))<td class="n"><b>{{ $cats[$id] }}</b></td>@else<td class="z">—</td>@endif @endforeach<td class="n"><b style="background:transparent;color:var(--ins-text)">{{ array_sum($cats) }}</b></td></tr>
      @empty
      <tr><td colspan="2" class="ins-empty">No issues raised in this period.</td></tr>
      @endforelse
    </tbody></table></div>
  </section>

  <section class="ins-card">
    <div class="ins-card-head"><div><h2>Executive summary</h2><p class="small">{{ $period }} · closed means supervisor verified</p></div></div>
    <div class="ins-table-wrap"><table class="ins-table"><thead><tr><th>Executive</th><th class="num">Visits done</th><th class="num">Issues raised</th><th class="num">Issues closed</th><th class="num">Avg. days to close</th></tr></thead><tbody>
      @forelse($summary as $e => $v)
      <tr><td><div class="ins-person"><span class="ins-avatar" aria-hidden="true">{{ strtoupper(substr($nm($e), 0, 2)) }}</span>{{ $nm($e) }}</div></td>
        <td class="num">{{ $v[0] }}</td><td class="num">{{ $v[1] }}</td><td class="num">{{ $v[2] }}</td><td class="num">{{ $v[3] === null ? '—' : $v[3] . ' days' }}</td></tr>
      @empty
      <tr><td colspan="5" class="ins-empty">No inspection activity in this period.</td></tr>
      @endforelse
    </tbody></table></div>
  </section>
</div>
@endsection
