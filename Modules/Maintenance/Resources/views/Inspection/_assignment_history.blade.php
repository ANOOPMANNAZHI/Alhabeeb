{{-- Who handled a building, newest first; dates can be corrected here (Inspection Setup -> History) --}}
<p class="small" style="margin:0 0 12px">Newest first. Correcting a date also moves the joining date of the person before or after, so the periods stay continuous.</p>
@if($rows->isEmpty())
  <div class="ins-alert warn">Nobody has been assigned to {{ $building->building_name }} yet.</div>
@else
<div class="ins-table-wrap"><table class="ins-table">
  <thead><tr><th>Executive</th><th colspan="2">From / Till</th><th>Changed by</th><th>Changed on</th></tr></thead>
  <tbody>
    @foreach($rows as $r)
    @php $by = $r->updated_by ?: $r->created_by; @endphp
    <tr>
      <td><b>{{ isset($names[$r->user_id]) ? $names[$r->user_id] : 'User #' . $r->user_id }}</b>@if($r->assigned_till === null) <span class="ins-chip s4" style="margin-left:4px">Current</span>@endif</td>
      <td colspan="2">
        <form method="post" action="{{ route('inspection.setup.history.update', $r->id) }}" class="ins-inline-form" style="flex-wrap:nowrap">
          @csrf
          <input type="date" name="assigned_from" value="{{ $r->assigned_from }}" max="{{ date('Y-m-d') }}" required aria-label="From" style="height:34px;width:150px">
          @if($r->assigned_till === null)
            <span class="small" style="display:inline-block;width:150px">— now (current)</span>
          @else
            <input type="date" name="assigned_till" value="{{ $r->assigned_till }}" max="{{ date('Y-m-d') }}" required aria-label="Till" style="height:34px;width:150px">
          @endif
          <button class="ins-btn sm" type="submit">Save</button>
        </form>
      </td>
      <td>{{ $by && isset($names[$by]) ? $names[$by] : '—' }}</td>
      <td>{{ date('d/m/Y', strtotime($r->updated_at ?: $r->created_at)) }}</td>
    </tr>
    @endforeach
  </tbody>
</table></div>
@endif
