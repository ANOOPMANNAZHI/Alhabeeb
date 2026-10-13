{{-- Legal case summary: handled by, type, priority, lawyer, outstanding, last lawyer rejection.
     Needs $legal; optional $outstanding (system rent outstanding), $defaultHandledBy, $timeline. --}}
@php
    $handled = $legal->handled_by ?: \Modules\Masters\Services\LegalCase::HANDLED_ALHABIB;
    $lastReject = isset($timeline) ? collect($timeline)->firstWhere('kind', 'lawyer_rejected') : null;
    $lastApprove = isset($timeline) ? collect($timeline)->first(function ($t) { return in_array($t->kind, ['plm_approved', 'lawyer_approved']); }) : null;
    $showReject = $lastReject && (!$lastApprove || $lastReject->id > $lastApprove->id);
@endphp
<style>
  .lc-summary { display:flex; flex-wrap:wrap; gap:8px 28px; padding:14px 20px; border-bottom:1px solid #eef1f5; }
  .lc-summary .lc-item { min-width:150px; }
  .lc-summary .lc-label { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#7a8699; }
  .lc-summary .lc-value { font-size:14px; font-weight:600; color:#1f2d3d; }
  .lc-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:600; }
  .lc-p1 { background:#fde8e8; color:#b42318; } .lc-p2 { background:#fff4e0; color:#a15c07; } .lc-p3 { background:#e8f3ff; color:#1d5fa8; }
  .lc-alert { margin:12px 20px 0; padding:10px 14px; border-radius:4px; background:#fff4f2; border-left:4px solid #d9534f; color:#7a271a; }
  .lc-info { margin:12px 20px 0; padding:10px 14px; border-radius:4px; background:#f0f6ff; border-left:4px solid #3b82c4; color:#1d3f66; }
</style>
@if($showReject)
  <div class="lc-alert"><b>Rejected by lawyer</b> on {{ \Carbon\Carbon::parse($lastReject->created_at)->format('d/m/Y') }}: {{ $lastReject->note }}</div>
@endif
@if($legal->parent_legal_id)
  <div class="lc-info">Reopened from case #{{ $legal->parent_legal_id }}.</div>
@endif
@if($legal->closed_at)
  <div class="lc-info"><b>Closed</b> on {{ \Carbon\Carbon::parse($legal->closed_at)->format('d/m/Y') }} &mdash; {{ \Modules\Masters\Services\LegalCase::reasonLabel($legal->close_reason) }}
    @if($legal->close_reason === 'handed_over')
      to {{ \Modules\Masters\Services\LegalCase::HANDLED_BY[$handled] }}: {{ $legal->handed_to_name }}@if($legal->handover_ref) (ref {{ $legal->handover_ref }})@endif
    @endif
  </div>
@endif
<div class="lc-summary">
  <div class="lc-item"><div class="lc-label">Handled by</div><div class="lc-value">{{ \Modules\Masters\Services\LegalCase::HANDLED_BY[$handled] }}
    @if(isset($defaultHandledBy) && !$legal->closed_at && (int) $defaultHandledBy !== (int) $handled)
      <small class="text-muted">(building default: {{ \Modules\Masters\Services\LegalCase::HANDLED_BY[$defaultHandledBy] }})</small>
    @endif
  </div></div>
  <div class="lc-item"><div class="lc-label">Case type</div><div class="lc-value">{{ $legal->case_type ? \Modules\Masters\Services\LegalCase::TYPES[$legal->case_type] : '-' }}</div></div>
  <div class="lc-item"><div class="lc-label">Priority</div><div class="lc-value">
    @if($legal->priority)<span class="lc-badge lc-p{{ $legal->priority }}">{{ \Modules\Masters\Services\LegalCase::PRIORITIES[$legal->priority] }}</span>@else - @endif
  </div></div>
  <div class="lc-item"><div class="lc-label">Lawyer</div><div class="lc-value">{{ $legal->lawyer ? $legal->lawyer->username : '-' }}</div></div>
  @if(isset($outstanding))
  <div class="lc-item"><div class="lc-label">Rent outstanding today</div><div class="lc-value">{{ numberFormat($outstanding) }}</div></div>
  @endif
</div>
