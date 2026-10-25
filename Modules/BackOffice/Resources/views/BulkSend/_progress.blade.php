{{-- Progress bar + one counts line for one send ($s). Pending is 0 once a send is cancelled.
     Visible: "66 / 111 · 6 failed · 9 skipped" (pending implied, zero counts left out); screen readers get the full sentence. --}}
@php
  $bsTotal = max(0, (int) $s->total);
  $bsDone = (int) $s->sent + (int) $s->failed + (int) $s->skipped;
  $bsPending = $s->status === 'cancelled' ? 0 : max(0, $bsTotal - $bsDone);
  $bsPct = function ($n) use ($bsTotal) { return $bsTotal > 0 ? round(min(100, $n * 100 / $bsTotal), 2) : 0; };
@endphp
<div class="bs-progress" role="progressbar" aria-valuemin="0" aria-valuenow="{{ $bsDone }}" aria-valuemax="{{ $bsTotal }}" aria-label="{{ $bsDone }} of {{ $bsTotal }} processed">
  <span class="bs-p-sent" style="width:{{ $bsPct((int) $s->sent) }}%"></span><span class="bs-p-failed" style="width:{{ $bsPct((int) $s->failed) }}%"></span><span class="bs-p-skipped" style="width:{{ $bsPct((int) $s->skipped) }}%"></span>
</div>
<div class="bs-progress-text">
  <span aria-hidden="true">{{ $bsDone }} / {{ $bsTotal }}@if((int) $s->failed) · <span class="bs-c-failed">{{ (int) $s->failed }} failed</span>@endif @if((int) $s->skipped)· <span class="bs-c-skipped">{{ (int) $s->skipped }} skipped</span>@endif</span>
  <span class="bs-sr">{{ $bsDone }} of {{ $bsTotal }} processed: {{ (int) $s->sent }} sent, {{ (int) $s->failed }} failed, {{ (int) $s->skipped }} skipped, {{ $bsPending }} pending</span>
</div>
