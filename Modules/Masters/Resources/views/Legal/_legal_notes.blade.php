{{-- Lawyer status notes (legal_notes): list and, when $canAddNote, the form (#legal_note_form, validated by the page).
     Closing is done on the case page now, so "End" is not offered here. Needs $legal, $legalNotes. --}}
<section class="lg-card" aria-labelledby="lg_status_notes">
  <div class="lg-card__head"><h2 class="lg-card__title" id="lg_status_notes">Status notes <span class="lg-count">{{ count($legalNotes) }}</span></h2></div>
  @if(!empty($canAddNote))
  <form action="{{ route('legalNoteStore') }}" method="POST" id="legal_note_form" class="lg-filters" enctype="multipart/form-data" data-toggle="validator">
    {{ csrf_field() }}
    <input type="hidden" name="legal_id" value="{{ $legal->id }}">
    <input type="hidden" name="current_url" value="{{ url()->current() }}">
    <div class="lg-field"><label for="legal_notes_status" class="lg-label">Status</label>
      <select class="lg-select" name="legal_notes_status" id="legal_notes_status" required>
        <option value="">Select</option>
        <option value="1">Start</option>
        <option value="2">Middle</option>
      </select></div>
    <div class="lg-field lg-field--wide" style="flex:3 1 280px;max-width:none;"><label for="legal_notes_note" class="lg-label">Note</label>
      <input type="text" class="lg-input" id="legal_notes_note" name="legal_notes_note" maxlength="200" data-rule-maxlength="200" data-msg-maxlength="Only allows 200 characters" required></div>
    <div class="lg-filters__actions"><button type="submit" class="lg-btn lg-btn--primary">Add note</button></div>
  </form>
  <div class="lg-card__foot" style="border-radius:0;border-top:0;border-bottom:1px solid var(--lg-border);">To close the case use <a href="{{ route('legal.case', $legal->id) }}">Close case on the case page</a>.</div>
  @endif
  @if(count($legalNotes))
  <div class="lg-table-wrap">
    <table class="lg-table">
      <thead><tr><th scope="col">Status</th><th scope="col">Date</th><th scope="col">Note</th></tr></thead>
      <tbody>
        @foreach($legalNotes as $note)
          @if(isset($note->legal_notes_status))
          <tr><td><span class="lg-badge lg-badge--info">{{ $note->legal_notes_status_name }}</span></td>
            <td class="tnum" style="white-space:nowrap;">{{ $note->created_at ? $note->created_at->format('d/m/Y h:i A') : '' }}</td>
            <td style="white-space:normal;">{{ $note->legal_notes_note }}</td></tr>
          @endif
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div class="lg-empty">No status notes.</div>
  @endif
</section>
