{{-- Rows of lawyer_approval_list (redesign: shared _list_row; the search script swaps this tbody). --}}
@php Session::put('current', 'lawyerApproval.index'); @endphp
@forelse ($lawyerApprovals as $c)
  @include('masters::Legal._list_row', ['c' => $c, 'kind' => 'lawyer', 'viewUrl' => route('lawyerApprovalShow', $c->id)])
@empty
  <tr><td colspan="10"><div class="lg-empty"><div class="lg-empty__title">No cases</div>Nothing matches the filters.</div></td></tr>
@endforelse
@if(isset($request->ajax))
  <tr>
    <td colspan="10" id="pagination_ajax">
      {{ $lawyerApprovals->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links() }}
      <div class="pagination_info">@include('includes.pagination_info', ['paginator' => $lawyerApprovals])</div>
    </td>
  </tr>
@endif
