{{-- Due for Renewal / Rent revisal tab bar in the Receipts tab style (spec 2026-10-15); shown only to users who can revise. --}}
@can('rent_revisal')
<style>
  /* pinned to the Receipts tab look: Due for Renewal loads custom.css, which would otherwise blank the active tab */
  .rr-tabs .custom-tab .nav-tabs > li > a{background:#fff;color:#093875;border-radius:15px;padding:7px 31px;font-size:15px;box-shadow:0 -2px 3px rgba(0,0,0,.2)}
  .rr-tabs .custom-tab .nav-tabs > li > a.active{background:#ff9800;color:#fff}
</style>
<div class="row rr-tabs">
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box" style="margin-bottom:0">
      <header class="panel-heading panel-heading-gray custom-tab">
        <ul class="nav nav-tabs">
          <li class="nav-item"><a href="{{ route('tenantRenewal.index') }}" class="{{ Route::currentRouteName() === 'rentRevisal.index' ? '' : 'active' }}">Due for Renewal</a></li>
          <li class="nav-item"><a href="{{ route('rentRevisal.index') }}" class="{{ Route::currentRouteName() === 'rentRevisal.index' ? 'active' : '' }}">Rent revisal</a></li>
        </ul>
      </header>
    </div>
  </div>
</div>
@endcan
