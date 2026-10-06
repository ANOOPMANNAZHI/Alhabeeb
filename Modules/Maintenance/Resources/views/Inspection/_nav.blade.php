{{-- Sub-navigation shared by the Building Inspection screens; $active = dashboard|visits|issues|reports|setup --}}
@php $u = Auth::user(); @endphp
<nav class="ins-tabs" aria-label="Building inspection">
  <a href="{{ route('inspection.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}" @if($active === 'dashboard') aria-current="page" @endif>Dashboard</a>
  <a href="{{ route('inspection.visits') }}" class="{{ $active === 'visits' ? 'active' : '' }}" @if($active === 'visits') aria-current="page" @endif>Visits</a>
  <a href="{{ route('inspection.issues') }}" class="{{ $active === 'issues' ? 'active' : '' }}" @if($active === 'issues') aria-current="page" @endif>Issue Register</a>
  @can('inspection_reports')
  <a href="{{ route('inspection.reports') }}" class="{{ $active === 'reports' ? 'active' : '' }}" @if($active === 'reports') aria-current="page" @endif>Reports</a>
  @endcan
  @can('inspection_masters')
  <a href="{{ route('inspection.setup') }}" class="{{ $active === 'setup' ? 'active' : '' }}" @if($active === 'setup') aria-current="page" @endif>Setup</a>
  @endcan
</nav>
