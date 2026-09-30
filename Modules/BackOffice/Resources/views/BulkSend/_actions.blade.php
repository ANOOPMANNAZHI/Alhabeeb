{{-- Pause / Resume / Cancel / Resend to failed for one send ($s), under the same conditions as before.
     data-confirm opens the in-page dialog (see _dialog); every button is disabled once its form is sent. --}}
<div class="mt-actions">
  @if($s->status === 'running')
    <form method="post" action="{{ route('bulk-send.pause', $s->id) }}" class="bs-action">@csrf<button type="submit" class="mt-btn mt-btn-sm"><i class="fa fa-pause" aria-hidden="true"></i> Pause</button></form>
  @endif
  @if($s->status === 'paused')
    <form method="post" action="{{ route('bulk-send.resume', $s->id) }}" class="bs-action">@csrf<button type="submit" class="mt-btn mt-btn-sm"><i class="fa fa-play" aria-hidden="true"></i> Resume</button></form>
  @endif
  @if(in_array($s->status, ['running', 'paused']))
    <form method="post" action="{{ route('bulk-send.cancel', $s->id) }}" class="bs-action" data-confirm="Cancel send #{{ $s->id }}? Messages not sent yet will not be sent."
          data-confirm-title="Cancel this send?" data-confirm-ok="Cancel send" data-confirm-danger="1">@csrf<button type="submit" class="mt-btn mt-btn-sm mt-btn-danger"><i class="fa fa-ban" aria-hidden="true"></i> Cancel</button></form>
  @endif
  @if(in_array($s->status, ['done', 'cancelled']) && $s->failed > 0 && !$s->resent_send_id)
    <form method="post" action="{{ route('bulk-send.resend-failed', $s->id) }}" class="bs-action" data-confirm="Resend the failed messages of send #{{ $s->id }}?"
          data-confirm-title="Resend to failed?" data-confirm-ok="Resend {{ (int) $s->failed }}">@csrf<button type="submit" class="mt-btn mt-btn-sm"><i class="fa fa-repeat" aria-hidden="true"></i> Resend to failed</button></form>
  @endif
</div>
