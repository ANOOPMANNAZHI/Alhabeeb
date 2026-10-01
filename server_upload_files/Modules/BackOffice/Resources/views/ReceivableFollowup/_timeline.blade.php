@php $labels = ['kept' => 'Kept', 'broken' => 'Broken', 'upcoming' => 'Upcoming']; @endphp
<div class="modal-dialog modal-lg" role="document"><div class="modal-content">
  <div class="modal-header">
    <h4 class="modal-title" id="rf-modal-title">Follow-up history</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  </div>
  <div class="modal-body">
    @include('backoffice::ReceivableFollowup._header')
    @if($followups->isEmpty())
      <p class="rf-empty-tl">No follow-ups yet.</p>
    @else
      <ol class="rf-tl" aria-label="Follow-ups, newest first">
        @foreach($followups as $f)
          @php $st = $statusFor($f); @endphp
          <li>
            <div class="rf-when"><strong>{{ $f->created_at->format('d/m/Y H:i') }}</strong> · {{ $f->creatorName() }}@if($f->balance_at_followup !== null) · balance {{ number_format($f->balance_at_followup, 3) }}@endif</div>
            <div class="rf-comment">{{ $f->comment }}</div>
            @if($f->promised_date)
              <div class="rf-promise">Promised {{ $f->promised_date->format('d/m/Y') }}<span class="rf-badge {{ $st }}">{{ $labels[$st] }}</span></div>
            @endif
          </li>
        @endforeach
      </ol>
    @endif
    <div class="rf-foot"><button type="button" class="rf-btn" data-dismiss="modal">Close</button></div>
  </div>
</div></div>
