{{-- Read-only dues summary for the contract chosen on this form.
     Usage: @include('backoffice::TerminationDues._summary_box', ['selector' => '#tenant_contract_no']) inside content.
     Emits both markup and script; jQuery is already loaded in the layout's <head> (public/js/all.js), so this is safe to include inline in the content section. --}}
<div id="tdue-box" class="alert" role="status" style="display:none; border:1px solid #fcd34d; background:#fffbeb; color:#0f172a; border-radius:8px; padding:16px; margin-bottom:24px">
  <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:baseline">
    <strong>Terminated contract — dues outstanding: <span id="tdue-box-balance"></span> OMR</strong>
    <a id="tdue-box-link" href="#" target="_blank" rel="noopener">Open termination dues</a>
  </div>
  <div id="tdue-box-meta" style="font-size:14px; color:#475569; margin:8px 0"></div>
  <table style="width:100%; font-size:14px; font-variant-numeric:tabular-nums">
    <thead><tr><th style="text-align:left; padding:4px 8px">Category</th><th style="text-align:left; padding:4px 8px">Team</th><th style="text-align:right; padding:4px 8px">Owed</th><th style="text-align:right; padding:4px 8px">Settled</th><th style="text-align:right; padding:4px 8px">Balance</th></tr></thead>
    <tbody id="tdue-box-rows"></tbody>
  </table>
</div>
<script>
(function () {
  var selector = @json($selector);
  var url = @json(route('terminationDuesSummary'));
  var lastId = null;
  function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }
  function render(d) {
    var $box = $('#tdue-box');
    if (!d || !d.found) { $box.hide(); return; }
    $('#tdue-box-balance').text(d.balance_fmt);
    $('#tdue-box-link').attr('href', d.url);
    $('#tdue-box-meta').text('Terminated ' + (d.termination_date || '') + ' · status ' + String(d.status).replace('_', ' ') + '. Collect only what is listed here; do not create receipts for money not yet received.');
    var rows = '';
    $.each(d.categories || [], function (_, c) {
      rows += '<tr><td style="padding:4px 8px">' + esc(c.label) + '</td><td style="padding:4px 8px">' + (c.owner_team === 'maintenance' ? 'Maintenance' : 'Back Office') + '</td>'
            + '<td style="text-align:right; padding:4px 8px">' + esc(c.owed_fmt) + '</td><td style="text-align:right; padding:4px 8px">' + esc(c.settled_fmt) + '</td>'
            + '<td style="text-align:right; padding:4px 8px; font-weight:600">' + esc(c.balance_fmt) + '</td></tr>';
    });
    $('#tdue-box-rows').html(rows);
    $box.show();
  }
  function refresh() {
    var id = $(selector).val();
    if (!id || id === lastId) { if (!id) { $('#tdue-box').hide(); lastId = null; } return; }
    lastId = id;
    $.getJSON(url, { contract_id: id }).done(render).fail(function () { $('#tdue-box').hide(); });
  }
  $(document).on('change', selector, refresh);
  // Hidden inputs do not fire change when set by script: poll cheaply.
  setInterval(refresh, 1500);
  $(function () {
    var pre = @json(request()->get('contract_id'));
    if (pre && !$(selector).val()) {
      // Collect links from the dues page: fetch the summary straight away even before the agreement is picked.
      $.getJSON(url, { contract_id: pre }).done(render);
    }
    refresh();
  });
})();
</script>
