{{-- Progress bar + counts for one send ($s). Pending is 0 once a send is cancelled. --}}
@php
  $bsTotal = max(0, (int) $s->total);
  $bsDone = (int) $s->sent + (int) $s->failed + (int) $s->skipped;
  $bsPending = $s->status === 'cancelled' ? 0 : max(0, $bsTotal - $bsDone);
  $bsPct = function ($n) use ($bsTotal) { return $bsTotal > 0 ? round(min(100, $n * 100 / $bsTotal), 2) : 0; };
@endphp
<div class="bs-progress" role="progressbar" aria-valuemin="0" aria-valuenow="{{ $bsDone }}" aria-valuemax="{{ $bsTotal }}" aria-label="{{ $bsDone }} of {{ $bsTotal }} processed">
  <span class="bs-p-sent" style="width:{{ $bsPct((int) $s->sent) }}%"></span><span class="bs-p-failed" style="width:{{ $bsPct((int) $s->failed) }}%"></span><span class="bs-p-skipped" style="width:{{ $bsPct((int) $s->skipped) }}%"></span>
</div>
<div class="bs-progress-text">{{ $bsDone }} of {{ $bsTotal }} processed</div>
<div class="bs-counts">
  <span class="bs-c-sent"><i class="fa fa-check" aria-hidden="true"></i> {{ (int) $s->sent }} sent</span>
  <span class="bs-c-failed"><i class="fa fa-times" aria-hidden="true"></i> {{ (int) $s->failed }} failed</span>
  <span class="bs-c-skipped"><i class="fa fa-minus" aria-hidden="true"></i> {{ (int) $s->skipped }} skipped</span>
  <span class="bs-c-pending"><i class="fa fa-clock-o" aria-hidden="true"></i> {{ $bsPending }} pending</span>
</div>
