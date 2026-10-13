{{-- Legal case summary: alerts (lawyer rejection, reopened, closed) and the who / what / priority facts.
     Needs $legal; optional $outstanding (system rent outstanding), $defaultHandledBy, $timeline. Inside a .lg page. --}}
@php
    $LC = \Modules\Masters\Services\LegalCase::class;
    $handled = $legal->handled_by ?: $LC::HANDLED_ALHABIB;
    $lastReject = isset($timeline) ? collect($timeline)->firstWhere('kind', 'lawyer_rejected') : null;
    $lastApprove = isset($timeline) ? collect($timeline)->first(function ($t) { return in_array($t->kind, ['plm_approved', 'lawyer_approved']); }) : null;
    $showReject = $lastReject && (!$lastApprove || $lastReject->id > $lastApprove->id);
@endphp
@if($showReject)
  <div class="lg-alert lg-alert--danger" role="alert">
    <div><span class="lg-alert__title">Rejected by lawyer</span> on {{ \Carbon\Carbon::parse($lastReject->created_at)->format('d/m/Y') }}: {{ $lastReject->note }}</div>
  </div>
@endif
@if($legal->parent_legal_id)
  <div class="lg-alert lg-alert--info"><div>Reopened from <a href="{{ route('legal.case', $legal->parent_legal_id) }}">case #{{ $legal->parent_legal_id }}</a>.</div></div>
@endif
@if($legal->closed_at)
  <div class="lg-alert lg-alert--info">
    <div><span class="lg-alert__title">Closed</span> on {{ \Carbon\Carbon::parse($legal->closed_at)->format('d/m/Y') }} &mdash; {{ $LC::reasonLabel($legal->close_reason) }}
      @if($legal->close_reason === 'handed_over')
        to {{ $LC::HANDLED_BY[$handled] }}: {{ $legal->handed_to_name }}@if($legal->handover_ref) (ref {{ $legal->handover_ref }})@endif
      @endif
    </div>
  </div>
@endif
<dl class="lg-kv">
  <div><dt>Handled by</dt><dd>{{ $LC::HANDLED_BY[$handled] }}
    @if(isset($defaultHandledBy) && !$legal->closed_at && (int) $defaultHandledBy !== (int) $handled)
      <span class="muted" style="font-weight:400;">(building default: {{ $LC::HANDLED_BY[$defaultHandledBy] }})</span>
    @endif</dd></div>
  <div><dt>Case type</dt><dd>{{ $legal->case_type ? $LC::TYPES[$legal->case_type] : '-' }}</dd></div>
  <div><dt>Priority</dt><dd>@if($legal->priority)<span class="lg-badge lg-prio--{{ $legal->priority }}"><span class="lg-dot"></span>{{ $LC::PRIORITIES[$legal->priority] }}</span>@else - @endif</dd></div>
  <div><dt>Lawyer</dt><dd>{{ $legal->lawyer ? $legal->lawyer->username : 'Not assigned' }}</dd></div>
  @if(isset($outstanding) && empty($hideOutstanding))
  <div><dt>Rent outstanding today</dt><dd class="tnum">{{ numberFormat($outstanding) }}</dd></div>
  @endif
</dl>
