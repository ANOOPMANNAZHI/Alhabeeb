@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
  .onb-list td.num, .onb-list th.num { text-align:right; white-space:nowrap; }
  .onb-list .onb-sub { display:block; font-size:12px; color:#6b7280; }
  .onb-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; margin-bottom:16px; }
  .onb-filters .form-group { margin:0; min-width:180px; }
  .onb-filters label { font-size:12px; color:#6b7280; margin-bottom:4px; display:block; }
  .onb-filters .form-control { height:40px; }
  .onb-status { display:inline-block; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:600; text-transform:uppercase; }
  .onb-status-open { background:#fef3c7; color:#92400e; } .onb-status-completed { background:#dbeafe; color:#1e40af; }
  .onb-status-verified { background:#dcfce7; color:#166534; } .onb-status-cancelled { background:#f1f5f9; color:#475569; }
</style>
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Onboarding</div>
        </div>
        {{ Breadcrumbs::render('tenant-onboarding.index') }}
    </div>
</div>

@include('includes.success')
@include('includes.error')

<div class="row">
  <div class="col-md-12 col-sm-12">
    <div class="card card-box onb-list">
      <div class="card-body">
        <form method="get" action="{{ route('tenant-onboarding.index') }}" class="onb-filters">
          <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control">
              @foreach($statuses as $k => $label)<option value="{{ $k }}" {{ $status === $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="building_id">Building</label>
            <select name="building_id" id="building_id" class="form-control">
              <option value="">All</option>
              @foreach($buildings as $b)<option value="{{ $b->id }}" {{ (string) request('building_id') === (string) $b->id ? 'selected' : '' }}>{{ $b->building_name }}</option>@endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="user_id">ARE</label>
            <select name="user_id" id="user_id" class="form-control">
              <option value="">All</option>
              @foreach($ares as $u)<option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>{{ optional($u->employee)->employee_name ?: $u->username }}</option>@endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="q">Contract / tenant</label>
            <input type="text" name="q" id="q" class="form-control" value="{{ request('q') }}" placeholder="Search">
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-primary" style="height:40px">Filter</button>
            <a href="{{ route('tenant-onboarding.index') }}" class="btn btn-default" style="height:40px">Reset</a>
          </div>
        </form>

        <h4>
          <div id="pagination_info">@include('includes.pagination_info', ['paginator' => $onboardings])</div>
          <div class="clr"></div>
        </h4>
        <div class="table-responsive">
          <table class="table display product-overview mb-30">
            <thead>
              <tr>
                <th>Contract No</th><th>Tenant</th><th>Building</th><th>Unit</th><th>Contract start</th><th>ARE</th><th>Started</th><th>Completed</th><th>Status</th><th width="8%">Action</th>
              </tr>
            </thead>
            <tbody>
            @forelse($onboardings as $o)
              @php $c = $o->tenantContract; $url = route('tenant-onboarding.show', $o->id); @endphp
              <tr>
                <td><a class="no-link" href="{{ $url }}">{{ optional($c)->tenant_contract_no ?: '#' . $o->tenant_contract_id }}</a></td>
                <td><a class="no-link" href="{{ $url }}">{{ optional(optional($c)->tenant)->tenant_name }}<span class="onb-sub">{{ optional(optional($c)->tenant)->tenant_contact_no }}</span></a></td>
                <td>{{ optional(optional($c)->building)->building_name }}</td>
                <td>{{ optional(optional($c)->unit)->unit_no ?: optional(optional($c)->unit)->unit_code }}</td>
                <td>{{ optional(optional($c)->tenant_contract_start_date)->format('d/m/Y') ?: '-' }}</td>
                <td>{{ optional(optional($o->assignedUser)->employee)->employee_name ?: (optional($o->assignedUser)->username ?: '-') }}</td>
                <td>{{ optional($o->started_at)->format('d/m/Y') ?: '-' }}</td>
                <td>{{ optional($o->completed_at)->format('d/m/Y') ?: '-' }}</td>
                <td><span class="onb-status onb-status-{{ $o->status }}">{{ $o->status }}</span></td>
                <td>
                  <a title="View" href="{{ $url }}" class="btn btn-tbl-view btn-xs"><i class="fa fa-eye"></i></a>
                  @if($o->status !== 'open')<a title="PDF" href="{{ route('tenant-onboarding.pdf', $o->id) }}" target="_blank" rel="noopener" class="btn btn-tbl-view btn-xs"><i class="fa fa-print"></i></a>@endif
                </td>
              </tr>
            @empty
              <tr><td colspan="10" align="center"><p>No Record</p></td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
        <div id="pagination"><div class="text-center">{{ $onboardings->links() }}</div></div>
      </div>
    </div>
  </div>
</div>
@endsection
