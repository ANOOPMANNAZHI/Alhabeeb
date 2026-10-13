{{-- Legal case timeline (legal_case_updates), newest first. Needs $timeline. --}}
<style>
  .lc-timeline { list-style:none; margin:0; padding:6px 20px 16px; }
  .lc-timeline li { position:relative; padding:10px 0 10px 22px; border-left:2px solid #e3e8ef; margin-left:6px; }
  .lc-timeline li:before { content:''; position:absolute; left:-7px; top:14px; width:12px; height:12px; border-radius:50%; background:#fff; border:2px solid #3b82c4; }
  .lc-timeline li.lc-k-lawyer_rejected:before, .lc-timeline li.lc-k-plm_referback:before { border-color:#d9534f; }
  .lc-timeline li.lc-k-closed:before, .lc-timeline li.lc-k-handed_over:before { border-color:#5b6b7f; background:#5b6b7f; }
  .lc-timeline .lc-t-head { font-size:13px; color:#1f2d3d; font-weight:600; }
  .lc-timeline .lc-t-meta { font-size:12px; color:#7a8699; font-weight:400; margin-left:6px; }
  .lc-timeline .lc-t-note { font-size:13px; color:#3d4a5c; margin-top:2px; white-space:pre-line; }
  .lc-timeline .lc-t-files a { display:inline-block; margin:4px 12px 0 0; font-size:12px; }
  .lc-timeline li.lc-k-stage:before { background:#3b82c4; }
</style>
@if(empty($noTimelineHead))<div class="card-head"><div class="col"><h4>Case Timeline</h4></div></div>@endif
@if(count($timeline))
<ul class="lc-timeline">
  @foreach($timeline as $t)
  <li class="lc-k-{{ $t->kind }}">
    <div class="lc-t-head">{{ isset(\Modules\Masters\Services\LegalCase::KINDS[$t->kind]) ? \Modules\Masters\Services\LegalCase::KINDS[$t->kind] : $t->kind }}@if($t->stage_name) &mdash; {{ $t->stage_name }}@endif
      <span class="lc-t-meta">{{ $t->username }} &middot; {{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y h:i A') }}</span>
    </div>
    @if($t->note)<div class="lc-t-note">{{ $t->note }}</div>@endif
    @if(isset($docs) && isset($docs[$t->id]))
      <div class="lc-t-files">
        @foreach($docs[$t->id] as $doc)
          <a target="_blank" href="{{ asset('storage/app/' . $doc->legal_documents_file_name) }}"><i class="fa fa-paperclip" aria-hidden="true"></i> {{ $doc->legal_documents_name }}</a>
        @endforeach
      </div>
    @endif
  </li>
  @endforeach
</ul>
@else
<p class="text-muted" style="padding:10px 20px;">No timeline entries yet. Cases moved to legal before October 2026 show their history in the Stage Note table.</p>
@endif
