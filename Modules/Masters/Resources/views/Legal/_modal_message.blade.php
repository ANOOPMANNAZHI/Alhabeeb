{{-- A message in #myModal when a legal action cannot run (the case moved on, no permission). --}}
<div class="modal-dialog" role="document" style="max-width:480px;">
  <div class="modal-content">
    <div class="modal-header">
      <h2 class="modal-title">{{ $title }}</h2>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body">
      <div class="lg-alert lg-alert--warning" style="margin:0;"><div>{{ $message }}</div></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="lg-btn lg-btn--secondary" onclick="location.reload()">Refresh page</button>
      <button type="button" class="lg-btn lg-btn--primary" data-dismiss="modal">OK</button>
    </div>
  </div>
</div>
