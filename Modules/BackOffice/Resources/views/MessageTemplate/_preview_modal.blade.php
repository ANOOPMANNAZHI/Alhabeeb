<div class="modal fade" id="mt-preview-modal" tabindex="-1" role="dialog" aria-labelledby="mt-preview-title">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="mt-preview-title">Preview with sample values</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="mt-pv-warn" id="mt-pv-warn"></div>
        <div class="mt-pv-head">Email</div>
        <div id="mt-pv-email"></div>
        <div class="mt-pv-head">SMS</div>
        <div id="mt-pv-sms"></div>
      </div>
    </div>
  </div>
</div>
<script>
  // Fills the preview modal from message-templates.preview (JSON). data: {id} or the editor's fields.
  function mtPreview(data) {
    data._token = $('meta[name="csrf-token"]').attr('content');
    $.post(@json(route('message-templates.preview')), data).done(function (r) {
      var esc = function (s) { return $('<div>').text(s).html(); };
      if (r.unknown && r.unknown.length) {
        $('#mt-pv-warn').html('No value for: <strong>' + r.unknown.map(function (k) { return esc('{' + k + '}'); }).join(', ') +
          '</strong>. These are shown as typed. Check the spelling or pick from the placeholder list.').show();
      } else {
        $('#mt-pv-warn').hide();
      }
      if (r.subject !== null || r.html !== null) {
        $('#mt-pv-email').html('<div class="mt-pv-subject"></div><div class="mt-pv-body"></div>');
        $('#mt-pv-email .mt-pv-subject').text('Subject: ' + (r.subject || ''));
        $('#mt-pv-email .mt-pv-body').html(r.html || '');
      } else {
        $('#mt-pv-email').html('<div class="mt-pv-none">No email version.</div>');
      }
      if (r.sms !== null) {
        var p = r.sms_parts;
        $('#mt-pv-sms').html('<div class="mt-pv-sms"></div><div class="mt-count"></div>');
        $('#mt-pv-sms .mt-pv-sms').text(r.sms);
        $('#mt-pv-sms .mt-count').text(p.chars + ' characters' + (p.unicode ? ' · Arabic/Unicode' : '') + ' · ' + p.parts + ' SMS');
      } else {
        $('#mt-pv-sms').html('<div class="mt-pv-none">No SMS version.</div>');
      }
      $('#mt-preview-modal').modal('show');
    }).fail(function () {
      alert('Preview failed. Please try again.');
    });
  }
</script>
