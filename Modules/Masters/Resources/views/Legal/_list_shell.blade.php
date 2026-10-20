{{-- Shared legal list page (design-system/plms-legal). Keeps the hooks legal_case_search_js / legal_search_js rely on:
     .search_fields inputs and their ids, .sort_url links, #enquiry-search, #pagination, #pagination_info, #url_route, #show.
     Params: $lgTitle, $lgSub, $lgCrumb, $lgPaginator, $lgRows (rows view), $lgSearchRoute; optional $lgStatusColumn (legal status
     column), $lgActiveCols (stage + lawyer + next hearing columns), $lgFiltersView (filter bar above the table),
     $lgBulk (['assign' => bool, 'close' => bool, 'handover' => bool]: checkbox column + Bulk actions bar, view masters::Legal._bulk_actions). --}}
@php $lgStatusColumn = !empty($lgStatusColumn); $lgActiveCols = !empty($lgActiveCols); $lgBulkOn = !empty($lgBulk) && (!empty($lgBulk['assign']) || !empty($lgBulk['close'])); @endphp
<div class="lg">
  <div class="lg-crumbs">{{ Breadcrumbs::render($lgCrumb) }}</div>

  <header class="lg-page-head">
    <div>
      <h1 class="lg-page-head__title">{{ $lgTitle }}</h1>
      <div class="lg-page-head__sub">{{ $lgSub }} <span class="sep">/</span> <span class="tnum">{{ $lgPaginator->total() }}</span> case{{ $lgPaginator->total() == 1 ? '' : 's' }}</div>
    </div>
    <div class="lg-page-head__actions">
      <a class="lg-btn lg-btn--secondary" href="#show" id="enquiry_div" data-toggle="collapse" role="button" aria-expanded="{{ old('fieldName') ? 'true' : 'false' }}" aria-controls="show">
        <i class="fa fa-sliders" aria-hidden="true"></i> <span id="enquirySearch">Advanced search</span></a>
    </div>
  </header>

  <div class="collapse @if(old('fieldName')) show @endif" id="show">
    <section class="lg-card lg-adv-search"><div class="lg-card__body">@include('masters::Legal.legal_search')</div></section>
  </div>

  <section class="lg-card" aria-label="{{ $lgTitle }}">
    @if(!empty($lgFiltersView))@include($lgFiltersView)@endif
    @if($lgBulkOn)@include('masters::Legal._bulk_actions')@endif
    <div class="lg-table-wrap">
      <table class="lg-table" id="dtBasicExample">
        <thead>
          <tr>
            @if($lgBulkOn)<th scope="col" class="lg-bulk-col"><input type="checkbox" id="lg_bulk_page" aria-label="Select the cases on this page" title="Select the cases on this page"></th>@endif
            <th scope="col">@sortablelink('tenantContract.tenant_contract_no','Agreement',[],[ 'class' => 'sort_url'])</th>
            <th scope="col">@sortablelink('tenant.tenant_name','Tenant',[],[ 'class' => 'sort_url'])</th>
            <th scope="col">@sortablelink('building.building_name','Building',[],[ 'class' => 'sort_url'])</th>
            <th scope="col">@sortablelink('unit.unit_code','Unit',[],[ 'class' => 'sort_url'])</th>
            <th scope="col">@sortablelink('tenantContract.tenant_contract_valid_to_date','Valid to',[],[ 'class' => 'sort_url'])</th>
            <th scope="col">@sortablelink('tenantContract.tenant_contract_status','Contract',[],[ 'class' => 'sort_url'])</th>
            <th scope="col" class="num">@sortablelink('tenantContract.tenant_contract_rent','Rent',[],[ 'class' => 'sort_url'])</th>
            <th scope="col">@sortablelink('priority','Priority',[],[ 'class' => 'sort_url'])</th>
            @if($lgStatusColumn)<th scope="col">@sortablelink('workFlowProcess.work_flow_processes_name','Legal status',[],[ 'class' => 'sort_url'])</th>@endif
            @if($lgActiveCols)<th scope="col">Stage</th><th scope="col">Lawyer</th><th scope="col">@sortablelink('next_hearing_date','Next hearing',[],[ 'class' => 'sort_url'])</th>@endif
            <th scope="col" class="num"><span class="sr-only">Actions</span></th>
          </tr>
          <tr class="lg-search-row">
            @if($lgBulkOn)<td></td>@endif
            <td><input autocomplete="off" type="text" name="tenant_contract_no" class="search_fields mob" id="tenant_contract_no" aria-label="Filter agreement" placeholder="Filter" value="{{old('tenantContract__tenant_contract_no')}}"></td>
            <td><input autocomplete="off" type="text" name="tenant_id" class="search_fields mob" id="tenant_id" aria-label="Filter tenant" placeholder="Filter" value="{{old('tenantContract__tenant__tenant_name')}}"></td>
            <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id" aria-label="Filter building" placeholder="Filter" value="{{old('building__building_name')}}"></td>
            <td><input autocomplete="off" type="text" name="unit_id" class="search_fields mob" id="unit_id" aria-label="Filter unit" placeholder="Filter" value="{{old('unit__unit_code')}}"></td>
            <td><input autocomplete="off" type="date" name="tenant_contract_valid_to_date" class="search_fields mob" id="tenant_contract_valid_to_date" aria-label="Filter valid to" value="{{old('tenantContract__tenant_contract_valid_to_date')}}"></td>
            <td>
              <select name="tenant_contract_status" class="searchFields search_fields mob" id="tenant_contract_status" aria-label="Filter contract status">
                <option value="">All</option>
                <option {{ (old('tenantContract__tenant_contract_status') != '') ? ((old('tenantContract__tenant_contract_status') == 0) ? 'selected' : '') : '' }} value="0">Inactive</option>
                <option {{ (old('tenantContract__tenant_contract_status') == 1) ? 'selected' : '' }} value="1">Active</option>
              </select>
            </td>
            <td><input autocomplete="off" type="text" name="tenant_contract_rent" class="search_fields mob" id="tenant_contract_rent" aria-label="Filter rent" placeholder="Filter" value="{{old('tenantContract__tenant_contract_rent')}}"></td>
            <td></td>
            @if($lgStatusColumn)<td><input autocomplete="off" type="text" name="work_flow_processes_id" class="search_fields mob" id="work_flow_processes_id" aria-label="Filter legal status" placeholder="Filter" value="{{old('workFlowProcess__work_flow_processes_name')}}"></td>@endif
            @if($lgActiveCols)<td></td><td></td><td></td>@endif
            <td><input autocomplete="off" type="hidden" name="url_route" id="url_route" value="{{ $lgSearchRoute }}"></td>
          </tr>
        </thead>
        <tbody id="enquiry-search">
          @include($lgRows)
        </tbody>
      </table>
    </div>
    <div class="lg-pager">
      <div id="pagination_info">@include('includes.pagination_info', ['paginator' => $lgPaginator])</div>
      <div id="pagination">{{ $lgPaginator->appends(\Request::except(['page','_token']))->links() }}</div>
    </div>
  </section>
</div>
<script>
  // "+N more" on a grouped row shows / hides the other cases of that tenant + unit (rows are swapped by ajax, so delegate).
  document.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('.lg-group-toggle') : null;
    if (!btn) return;
    var open = btn.getAttribute('aria-expanded') !== 'true';
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    var rows = document.querySelectorAll('tr[data-group-of="' + btn.getAttribute('data-group') + '"]');
    for (var i = 0; i < rows.length; i++) rows[i].hidden = !open;
  });
</script>
