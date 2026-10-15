{{-- Due for Renewal / Rent revisal tab bar (spec 2026-10-15); shown only to users who can revise. --}}
@can('rent_revisal')
<ul class="nav nav-tabs" style="margin-bottom:15px">
  <li class="nav-item"><a class="nav-link {{ Route::currentRouteName() === 'rentRevisal.index' ? '' : 'active' }}" href="{{ route('tenantRenewal.index') }}">Due for Renewal</a></li>
  <li class="nav-item"><a class="nav-link {{ Route::currentRouteName() === 'rentRevisal.index' ? 'active' : '' }}" href="{{ route('rentRevisal.index') }}">Rent revisal</a></li>
</ul>
@endcan
