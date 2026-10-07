@extends('layouts.plms-app')
@section('css')
@include('maintenance::Inspection._styles')
<style>
  .ins-item { display:flex; gap:12px; padding:12px 24px; border-bottom:1px solid #eef2f6; text-decoration:none; color:inherit; }
  .ins-item:hover { background:var(--ins-blue-50); text-decoration:none; color:inherit; }
  .ins-item:last-child { border-bottom:0; }
  .ins-item .grow { flex:1; min-width:0; }
  .ins-item h3 { font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .ins-item > .ins-chip { align-self:flex-start; }
  .ins-item .age { font-size:12px; font-weight:600; color:#c2410c; white-space:nowrap; }
</style>
@endsection
@section('content')
@php use Modules\Maintenance\Services\Inspection as I; $tot = isset($compliance['totals'][date('Y-m')]) ? $compliance['totals'][date('Y-m')] : [0, 0]; $ok = $tot[0] ? round($tot[1] / $tot[0] * 100) : 0; @endphp
<div class="page-bar"><div class="page-title-breadcrumb">{{ Breadcrumbs::render('inspection.dashboard') }}</div></div>
<div class="ins">
  <div class="ins-head">
    <div><h1>Inspection overview</h1>
      <p class="sub">{{ date('F Y') }} · {{ $myBuildings === null ? 'All executives' : 'Your ' . $myBuildings . ' assigned building' . ($myBuildings === 1 ? '' : 's') }}</p></div>
    <div class="ins-tools">
      @can('inspection_visit_create')<a class="ins-btn" href="{{ route('inspection.visits', ['tab' => 'all']) }}">Visits</a>@endcan
      <a class="ins-btn primary" href="{{ route('inspection.issues') }}">Issue register</a>
    </div>
  </div>

  @if($unassigned)
  <div class="ins-alert warn">{{ $unassigned }} active building{{ $unassigned === 1 ? ' has' : 's have' }} nobody assigned for inspections.@can('inspection_masters') <a href="{{ route('inspection.setup') }}">Assign executives</a>@endcan</div>
  @endif

  <div class="ins-kpis">
    <a class="ins-kpi" href="{{ route('inspection.visits', ['tab' => 'all', 'month' => $month]) }}"><div class="k-label">Visits this month</div><div class="value">{{ $visits->planned }}</div>
      <div class="foot">{{ $visits->done }} completed · {{ $visits->pending }} to do{{ $visits->late ? ' · ' . $visits->late . ' past planned date' : '' }}</div></a>
    <a class="ins-kpi" href="{{ route('inspection.issues') }}"><div class="k-label">Open issues</div><div class="value">{{ $issues->open }}</div><div class="foot">{{ $issues->awaiting }} awaiting verification</div></a>
    <a class="ins-kpi {{ $issues->critical ? 'alert' : '' }}" href="{{ route('inspection.issues', ['priority' => 1]) }}"><div class="k-label">Critical open</div><div class="value">{{ $issues->critical }}</div><div class="foot">Target: within 1 day</div></a>
    <a class="ins-kpi {{ $issues->overdue ? 'alert' : '' }}" href="{{ route('inspection.issues', ['overdue' => 1]) }}"><div class="k-label">Overdue issues</div><div class="value">{{ $issues->overdue }}</div><div class="foot">Past the target date</div></a>
  </div>

  <div class="ins-grid2" style="margin-bottom:24px">
    <section class="ins-card">
      <div class="ins-card-head"><div><h2>Visit compliance</h2><p class="small">{{ date('F Y') }} · monthly inspections</p></div><span class="ins-chip">{{ $ok }}% overall</span></div>
      <div class="ins-table-wrap"><table class="ins-table"><thead><tr><th>Executive</th><th class="num">Due</th><th class="num">Done</th><th>Completion</th></tr></thead><tbody>
        @forelse($compliance['rows'] as $e => $m)
        @php $v = isset($m[date('Y-m')]) ? $m[date('Y-m')] : [0, 0]; $pc = $v[0] ? round($v[1] / $v[0] * 100) : 0; $nm = isset($names[$e]) ? $names[$e] : ''; @endphp
        <tr><td><div class="ins-person"><span class="ins-avatar" aria-hidden="true">{{ strtoupper(substr($nm, 0, 2)) }}</span>{{ $nm }}</div></td>
          <td class="num">{{ $v[0] }}</td><td class="num">{{ $v[1] }}</td>
          <td><div class="ins-progress"><div class="bar"><i style="width:{{ $pc }}%"></i></div><b class="small">{{ $pc }}%</b></div></td></tr>
        @empty
        <tr><td colspan="4" class="ins-empty">No monthly inspections scheduled for {{ date('F') }} yet.</td></tr>
        @endforelse
      </tbody></table></div>
    </section>
    <section class="ins-card">
      <div class="ins-card-head"><div><h2>Needs attention</h2><p class="small">Overdue issues · priority, then oldest target</p></div><span class="ins-chip late">{{ $issues->overdue }} overdue</span></div>
      @forelse($attention as $i)
      <a class="ins-item" href="{{ route('inspection.issues.show', $i->id) }}"><div class="grow"><h3>{{ $i->description }}</h3><small>{{ $i->building_name }} · {{ $i->issue_no }}</small>
        <div style="margin-top:4px"><span class="ins-chip p{{ $i->priority }}">{{ I::PRIORITIES[$i->priority] }}</span></div></div>
        <span class="age">{{ I::days($i->target_date) }}d overdue</span></a>
      @empty
      <div class="ins-empty">No overdue issues.</div>
      @endforelse
    </section>
  </div>

  @if(count($awaiting))
  <section class="ins-card">
    <div class="ins-card-head"><div><h2>Awaiting your verification</h2><p class="small">Resolved by the executive with an after photo</p></div><a class="ins-link" href="{{ route('inspection.issues', ['status' => 3]) }}">View all</a></div>
    @foreach($awaiting as $i)
    <a class="ins-item" href="{{ route('inspection.issues.show', $i->id) }}"><div class="grow"><h3>{{ $i->description }}</h3><small>{{ $i->building_name }} · {{ $i->issue_no }} · resolved {{ date('d M', strtotime($i->resolved_at)) }}</small></div>
      <span class="ins-chip p{{ $i->priority }}">{{ I::PRIORITIES[$i->priority] }}</span></a>
    @endforeach
  </section>
  @endif

  <section class="ins-card">
    <div class="ins-card-head"><div><h2>Upcoming visits</h2><p class="small">Scheduled up to {{ date('d M', strtotime('+14 days')) }}, including late ones</p></div><a class="ins-link" href="{{ route('inspection.visits', ['tab' => Auth::user()->can('inspection_execute') ? 'mine' : 'all']) }}">All visits</a></div>
    <div class="ins-table-wrap"><table class="ins-table"><thead><tr><th>Building</th><th>Visit</th><th>Executive</th><th>Planned</th><th><span class="ins-sr">Action</span></th></tr></thead><tbody>
      @forelse($upcoming as $v)
      <tr><td><b>{{ $v->building_name }}</b><br><small>{{ $v->building_code }}</small></td><td>{{ I::VISIT_TYPES[$v->visit_type] }}<br><small>{{ $v->visit_no }}</small></td>
        <td>{{ isset($names[$v->executive_id]) ? $names[$v->executive_id] : '' }}</td>
        <td class="ins-nw">{{ date('d M Y', strtotime($v->planned_date)) }}@if($v->planned_date < date('Y-m-d'))<br><small class="orange">{{ I::days($v->planned_date) }} days late</small>@endif</td>
        <td class="num">@if((int) $v->executive_id === (int) Auth::id() && Auth::user()->can('inspection_execute'))<a class="ins-btn sm primary" href="{{ route('inspection.visits.inspect', $v->id) }}">Start inspection</a>@endif</td></tr>
      @empty
      <tr><td colspan="5" class="ins-empty">No visits scheduled for the next two weeks.</td></tr>
      @endforelse
    </tbody></table></div>
  </section>
</div>
@endsection
