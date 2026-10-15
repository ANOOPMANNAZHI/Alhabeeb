{{-- Rows of referback_cases_list (redesign: shared _list_row; the search script swaps this tbody). --}}
@php Session::put('current', 'closedLegalCase.index'); @endphp
@forelse ($closedLegalCases as $c)
  @include('masters::Legal._list_row', ['c' => $c, 'kind' => 'referback', 'viewRoute' => 'referBackCasesShow'])
@empty
  <tr><td colspan="10"><div class="lg-empty"><div class="lg-empty__title">No cases</div>Nothing matches the filters.</div></td></tr>
@endforelse
@if(isset($request->ajax))
  <tr>
    <td colspan="10" id="pagination_ajax">
      {{ $closedLegalCases->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links() }}
      <div class="pagination_info">@include('includes.pagination_info', ['paginator' => $closedLegalCases])</div>
    </td>
  </tr>
@endif
