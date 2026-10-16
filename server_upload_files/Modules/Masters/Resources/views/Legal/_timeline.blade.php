{{-- Legal case timeline (legal_case_updates), newest first. Needs $timeline; optional $docs (files by update id). Inside a .lg card. --}}
@php
    $LC = \Modules\Masters\Services\LegalCase::class;
    $tone = ['lawyer_rejected' => 'danger', 'plm_referback' => 'danger', 'settlement_broken' => 'danger', 'settlement_sent_back' => 'danger',
             'closed' => 'neutral', 'handed_over' => 'neutral', 'settlement_verified' => 'success', 'stage' => 'stage'];
@endphp
@if(empty($noTimelineHead))
<div class="lg-card__head"><h2 class="lg-card__title">Case timeline <span class="lg-count">{{ count($timeline) }}</span></h2></div>
@endif
@if(count($timeline))
<ol class="lg-timeline">
  @foreach($timeline as $t)
  <li class="lg-timeline__item lg-timeline__item--{{ $tone[$t->kind] ?? 'default' }}">
    <span class="lg-timeline__dot" aria-hidden="true"></span>
    <div class="lg-timeline__head">
      <span class="lg-timeline__kind">{{ $LC::KINDS[$t->kind] ?? $t->kind }}@if($t->stage_name) &middot; {{ $t->stage_name }}@endif</span>
      <span class="lg-timeline__meta">{{ $t->username }} &middot; <time datetime="{{ $t->created_at }}">{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y h:i A') }}</time></span>
    </div>
    @if($t->note)<div class="lg-timeline__note">{{ $t->note }}</div>@endif
    @if(isset($docs) && isset($docs[$t->id]))
      <div class="lg-timeline__files">
        @foreach($docs[$t->id] as $doc)
          <a class="lg-file" target="_blank" rel="noopener" href="{{ asset('storage/app/' . $doc->legal_documents_file_name) }}"><i class="fa fa-paperclip" aria-hidden="true"></i> {{ $doc->legal_documents_name }}</a>
        @endforeach
      </div>
    @endif
  </li>
  @endforeach
</ol>
@else
<div class="lg-empty"><div class="lg-empty__title">No timeline entries yet</div>Cases moved to legal before October 2026 keep their history in the stage notes.</div>
@endif
