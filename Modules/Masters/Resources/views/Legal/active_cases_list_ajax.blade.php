{{-- Rows of active_cases_list (redesign: shared _list_row; the search script swaps this tbody). --}}
@php $lcStageNames = \Illuminate\Support\Facades\DB::table('legal_stages')->pluck('name', 'id'); @endphp
@php Session::put('current', 'activeCase.index'); @endphp
@php $lgBulkLawyer = \Auth::user()->can('plms_approve'); @endphp
@forelse ($activeCases as $c)
  @include('masters::Legal._list_row', ['c' => $c, 'kind' => 'active', 'viewRoute' => 'activeCasesShow', 'lgActiveCols' => true, 'lgBulkLawyer' => $lgBulkLawyer])
@empty
  <tr><td colspan="14"><div class="lg-empty"><div class="lg-empty__title">No cases</div>Nothing matches the filters.</div></td></tr>
@endforelse
@if(isset($request->ajax))
  <tr>
    <td colspan="14" id="pagination_ajax">
      {{ $activeCases->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links() }}
      <div class="pagination_info">@include('includes.pagination_info', ['paginator' => $activeCases])</div>
    </td>
  </tr>
@endif
