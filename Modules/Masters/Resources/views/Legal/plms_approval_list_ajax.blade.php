{{-- Rows of plms_approval_list (redesign: shared _list_row; the search script swaps this tbody). --}}
@php Session::put('current', 'plmsApproval.index'); @endphp
@forelse ($plmsApprovals as $c)
  @include('masters::Legal._list_row', ['c' => $c, 'kind' => 'plm', 'viewUrl' => route('plmsApprovalShow', $c->id)])
@empty
  <tr><td colspan="10"><div class="lg-empty"><div class="lg-empty__title">No cases</div>Nothing matches the filters.</div></td></tr>
@endforelse
@if(isset($request->ajax))
  <tr>
    <td colspan="10" id="pagination_ajax">
      {{ $plmsApprovals->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links() }}
      <div class="pagination_info">@include('includes.pagination_info', ['paginator' => $plmsApprovals])</div>
    </td>
  </tr>
@endif
