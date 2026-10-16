{{-- Stage notes: the note each workflow step left on the contract's legal rows (cases before the timeline). Needs $notes. --}}
@php $stageNotes = collect($notes)->filter(function ($n) { return $n->note != ''; }); @endphp
<section class="lg-card" aria-labelledby="lg_stage_notes">
  <div class="lg-card__head"><h2 class="lg-card__title" id="lg_stage_notes">Stage notes <span class="lg-count">{{ $stageNotes->count() }}</span></h2></div>
  @if($stageNotes->count())
  <div class="lg-table-wrap">
    <table class="lg-table">
      <thead><tr><th scope="col">Note</th><th scope="col">Step</th><th scope="col">By</th><th scope="col">Date</th></tr></thead>
      <tbody>
        @foreach($stageNotes as $note)
        <tr>
          <td style="white-space:normal;min-width:220px;">{{ $note->note }}</td>
          <td>{{ $note->workFlowProcess->work_flow_processes_name ?? '' }}</td>
          <td>{{ $note->createdBy->username ?? '' }}</td>
          <td class="tnum" style="white-space:nowrap;">{{ $note->created_at ? $note->created_at->format('d/m/Y h:i A') : '' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div class="lg-empty">No stage notes.</div>
  @endif
</section>
