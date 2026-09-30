{{-- In-page confirm dialog (replaces the browser's confirm box). Include in @section('scripts').
     bsDialog({title, text, rows: [[label, value]], warn, ok, danger, onOk}) — all text is set with .text(), never as HTML.
     Forms with class "bs-action" and a data-confirm attribute ask first; every bs-action form disables its button once sent. --}}
<div class="mt bs">
  <div class="bs-dialog-backdrop" id="bs-dialog" hidden>
    <div class="bs-dialog" role="dialog" aria-modal="true" aria-labelledby="bs-dialog-title" aria-describedby="bs-dialog-body" tabindex="-1">
      <h2 id="bs-dialog-title"></h2>
      <div id="bs-dialog-body"></div>
      <div class="bs-dialog-actions">
        <button type="button" class="mt-btn" id="bs-dialog-cancel">Go back</button>
        <button type="button" class="mt-btn mt-btn-primary" id="bs-dialog-ok"></button>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  var $wrap = $('#bs-dialog'), $box = $wrap.find('.bs-dialog'), $ok = $('#bs-dialog-ok'), $cancel = $('#bs-dialog-cancel');
  var lastFocus = null, onOk = null;

  function close() {
    $wrap.prop('hidden', true);
    $('body').removeClass('bs-dialog-open');
    $(document).off('keydown.bsdialog');
    onOk = null;
    if (lastFocus && document.body.contains(lastFocus)) { lastFocus.focus(); }
  }

  window.bsDialog = function (o) {
    lastFocus = document.activeElement;
    onOk = o.onOk || null;
    $('#bs-dialog-title').text(o.title || 'Are you sure?');
    var $b = $('#bs-dialog-body').empty();
    if (o.text) { $('<p>').text(o.text).appendTo($b); }
    if (o.rows && o.rows.length) {
      var $ul = $('<ul>').appendTo($b);
      o.rows.forEach(function (r) { $('<li>').append($('<span>').text(r[0])).append($('<strong>').text(r[1])).appendTo($ul); });
    }
    if (o.warn) {
      $('<p class="bs-dialog-warn">').append('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>').append($('<span>').text(o.warn)).appendTo($b);
    }
    $ok.text(o.ok || 'Yes').toggleClass('mt-btn-primary', !o.danger).toggleClass('mt-btn-danger', !!o.danger);
    $wrap.prop('hidden', false);
    $('body').addClass('bs-dialog-open');
    $cancel.focus(); // the safe choice gets focus first
    $(document).on('keydown.bsdialog', function (e) {
      if (e.key === 'Escape' || e.keyCode === 27) { e.preventDefault(); close(); return; }
      if (e.key === 'Tab' || e.keyCode === 9) { // keep focus inside the dialog
        var f = $box.find('button:visible'), first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        else if (!$.contains($box[0], document.activeElement)) { e.preventDefault(); first.focus(); }
      }
    });
  };
  window.bsDialogClose = close;

  $cancel.on('click', close);
  $wrap.on('mousedown', function (e) { if (e.target === this) { close(); } });
  $ok.on('click', function () { var fn = onOk; lastFocus = null; close(); if (fn) { fn(); } });

  function lock(form) {
    $(form).find('button[type=submit]').prop('disabled', true).attr('aria-disabled', 'true');
  }
  $(document).on('submit', 'form.bs-action', function (e) {
    var form = this, $f = $(form);
    if ($f.data('sent')) { e.preventDefault(); return; }
    if ($f.attr('data-confirm') && !$f.data('confirmed')) {
      e.preventDefault();
      window.bsDialog({ title: $f.attr('data-confirm-title'), text: $f.attr('data-confirm'), ok: $f.attr('data-confirm-ok'),
        danger: $f.attr('data-confirm-danger') === '1', onOk: function () { $f.data('confirmed', true).data('sent', true); lock(form); form.submit(); } });
      return;
    }
    $f.data('sent', true);
    setTimeout(function () { lock(form); }, 0); // after the browser has taken the submit
  });
})();
</script>
