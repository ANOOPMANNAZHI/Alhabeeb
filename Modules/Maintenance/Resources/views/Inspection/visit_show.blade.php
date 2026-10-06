@extends('layouts.plms-app')
@section('css')
@include('maintenance::Inspection._styles')
<style>
  .ins-res { display:inline-block; min-width:44px; text-align:center; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:600; }
  .ins-res.r1 { background:var(--ins-ok-50); color:var(--ins-ok); } .ins-res.r2 { background:#fff0e6; color:#c2410c; } .ins-res.r3 { background:#eef2f6; color:#6b7785; }
</style>
@endsection
@section('content')
@php use Modules\Maintenance\Services\Inspection as I; $n = function ($id) use ($names) { return isset($names[$id]) ? $names[$id] : ''; }; @endphp
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Building Inspection</div></div>{{ Breadcrumbs::render('inspection.visits.show', $visit) }}</div></div>
<div class="ins">
  <div class="ins-head">
    <div><h1>{{ $visit->building_name }}</h1><p class="sub">{{ I::VISIT_TYPES[$visit->visit_type] }} · {{ $visit->visit_no }}</p></div>
    <span class="ins-chip v{{ $visit->status }}" style="font-size:14px;padding:4px 12px">{{ I::VISIT_STATUSES[$visit->status] }}</span>
  </div>
  <section class="ins-card"><div class="ins-card-body">
    <div class="ins-meta">
      <div><small>Executive</small><b>{{ $n($visit->executive_id) }}</b></div>
      <div><small>Planned date</small><b>{{ date('d M Y', strtotime($visit->planned_date)) }}</b></div>
      <div><small>{{ $visit->status == I::CANCELLED ? 'Cancelled' : 'Completed' }}</small><b>{{ $visit->status == I::COMPLETED ? date('d M Y H:i', strtotime($visit->completed_at)) : ($visit->cancelled_at ? date('d M Y H:i', strtotime($visit->cancelled_at)) . ' · ' . $n($visit->cancelled_by) : '—') }}</b></div>
      <div><small>Scheduled by</small><b>{{ $n($visit->created_by) }}</b></div>
    </div>
    @if($visit->purpose)<p style="margin-top:16px"><b>Purpose:</b> {{ $visit->purpose }}</p>@endif
    @if($visit->cancel_reason)<div class="ins-alert warn" style="margin:16px 0 0"><b>Cancel reason:</b> {{ $visit->cancel_reason }}</div>@endif
    @if($visit->general_remarks)<p style="margin-top:16px"><b>General remarks:</b> {!! nl2br(e($visit->general_remarks)) !!}</p>@endif
    @if($visit->latitude)<p class="small" style="margin-top:8px"><a href="https://www.google.com/maps?q={{ $visit->latitude }},{{ $visit->longitude }}" target="_blank" rel="noopener">Submitted at {{ round($visit->latitude, 5) }}, {{ round($visit->longitude, 5) }}</a></p>@endif
  </div></section>

  @if(count($followups))
  <section class="ins-card"><div class="ins-card-head"><h2>Pending issues followed up</h2></div>
    <div class="ins-table-wrap"><table class="ins-table"><thead><tr><th>Issue</th><th>Condition</th><th>Note</th></tr></thead><tbody>
      @foreach($followups as $f)
      <tr><td><a href="{{ route('inspection.issues.show', $f->issue_id) }}">{{ $f->issue_no }}</a><br><small>{{ $f->description }}</small></td>
        <td>{{ I::CONDITIONS[$f->condition] }}</td><td>{{ $f->note }}</td></tr>
      @endforeach
    </tbody></table></div></section>
  @endif

  @if(count($results))
  <section class="ins-card"><div class="ins-card-head"><h2>Checklist</h2>
    @php $all = $results->flatten(1); @endphp
    <span class="small">{{ $all->where('result', 1)->count() }} OK · {{ $all->where('result', 2)->count() }} issues · {{ $all->where('result', 3)->count() }} N/A</span></div>
    <div class="ins-table-wrap"><table class="ins-table"><thead><tr><th>Area</th><th>Checklist line</th><th>Result</th><th>Issue raised</th></tr></thead><tbody>
      @foreach($results as $area => $rows)
        @foreach($rows as $k => $r)
        <tr><td>{{ $k === 0 ? $area : '' }}</td><td>{{ $r->item }}</td><td><span class="ins-res r{{ $r->result }}">{{ I::RESULTS[$r->result] }}</span></td>
          <td>@if($r->issue_id)<a href="{{ route('inspection.issues.show', $r->issue_id) }}">{{ $r->issue_no }}</a> <span class="ins-chip p{{ $r->priority }}">{{ I::PRIORITIES[$r->priority] }}</span><br><small>{{ $r->description }}</small>@else — @endif</td></tr>
        @endforeach
      @endforeach
    </tbody></table></div></section>
  @endif
</div>
@endsection
