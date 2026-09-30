{{-- ① Message ② Recipients ③ Review. $step = current step (1-3). Steps 1-2 link to their section on the compose page. --}}
@php $bsSteps = [1 => ['Message', '#bs-sec-message'], 2 => ['Recipients', '#bs-sec-recipients'], 3 => ['Review', null]]; @endphp
<ol class="bs-stepper" aria-label="Steps" id="bs-stepper">
  @foreach($bsSteps as $n => $st)
    @php $done = $n < $step; @endphp
    <li class="{{ $done ? 'is-done' : '' }}" data-step="{{ $n }}" @if($n === $step) aria-current="step" @endif>
      @if($step < 3 && $st[1])<a href="{{ $st[1] }}">@endif
        <span class="bs-dot" aria-hidden="true">@if($done)<i class="fa fa-check"></i>@else{{ $n }}@endif</span>
        <span>{{ $st[0] }}@if($done)<span class="bs-sr"> (done)</span>@endif</span>
      @if($step < 3 && $st[1])</a>@endif
    </li>
  @endforeach
</ol>
