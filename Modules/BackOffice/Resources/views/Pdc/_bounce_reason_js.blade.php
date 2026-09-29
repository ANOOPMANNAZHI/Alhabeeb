{{-- For JavaScript that builds PDC rows: bounceReasonOptions(selected) returns the <option>s from Masters -> Bounce Reason. --}}
<script>
  var PDC_BOUNCE_REASONS = @json(\Modules\Masters\Entities\PdcBounceReason::jsList());
  function bounceReasonOptions(selected) {
    var html = '', sel = (selected === undefined || selected === null) ? '' : String(selected);
    PDC_BOUNCE_REASONS.forEach(function (r) {
      var on = sel !== '' && String(r.id) === sel;
      if (r.active || on) {
        html += '<option value="' + r.id + '"' + (on ? ' SELECTED' : '') + '>' + $('<div>').text(r.name).html() + '</option>';
      }
    });
    return html;
  }
</script>
