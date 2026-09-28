@extends('layouts.plms-app')

@section('css')
<style>
  .tdb { --tdb-ink:#0f172a; --tdb-muted:#475569; --tdb-line:#e2e8f0; --tdb-fill:#f1f5f9; }
  .tdb p { color:var(--tdb-muted); font-size:14px; max-width:75ch; }
  .tdb .tdb-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-top:16px; }
  .tdb .tdb-btn { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 16px; border:1px solid #94a3b8; border-radius:6px;
                  background:#fff; color:var(--tdb-ink); font-size:14px; font-weight:600; text-decoration:none; cursor:pointer; white-space:nowrap; }
  .tdb .tdb-btn:hover { background:var(--tdb-fill); color:var(--tdb-ink); text-decoration:none; }
  .tdb .tdb-btn:focus-visible { outline:2px solid #2f4fd6; outline-offset:2px; }
  .tdb .tdb-btn[disabled] { opacity:.5; cursor:not-allowed; }
  .tdb .tdb-btn-primary { background:#16a34a; border-color:#16a34a; color:#fff; }
  .tdb .tdb-btn-primary:hover { background:#15803d; border-color:#15803d; color:#fff; }
  .tdb .tdb-progress { height:8px; background:var(--tdb-fill); border-radius:4px; overflow:hidden; margin-top:16px; display:none; }
  .tdb .tdb-progress div { height:100%; width:0; background:#16a34a; transition:width .2s; }
  .tdb .tdb-status { margin-top:8px; font-size:14px; color:var(--tdb-muted); min-height:20px; }
  .tdb .tdb-counts { display:flex; flex-wrap:wrap; gap:16px; margin:0 0 16px; }
  .tdb .tdb-count { border:1px solid var(--tdb-line); border-radius:8px; padding:12px 16px; min-width:150px; }
  .tdb .tdb-count span { display:block; font-size:12px; color:var(--tdb-muted); }
  .tdb .tdb-count strong { font-size:22px; }
  .tdb table { width:100%; border-collapse:collapse; }
  .tdb th, .tdb td { padding:10px 12px; border-bottom:1px solid var(--tdb-line); font-size:14px; }
  .tdb th { font-size:12px; font-weight:600; color:var(--tdb-muted); text-align:left; background:var(--tdb-fill); }
  .tdb .num { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
  .tdb .tdb-fail { color:#991b1b; }
  .tdb .tdb-done { padding:12px 16px; border-radius:6px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; font-weight:600; margin-bottom:16px; display:none; }
  @media (prefers-reduced-motion: reduce) { .tdb .tdb-progress div { transition:none; } }
</style>
@endsection

@section('content')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">Backfill Termination Dues</div></div>
    {{ Breadcrumbs::render('termination-dues.backfill') }}
  </div>
</div>

<div class="tdb">
<div class="card card-box">
  <div class="card-body">
    <p>One-time process for contracts terminated before Termination Dues existed: every terminated contract with an approved tenant
       termination and <strong>no dues record yet ({{ number_format($remaining) }} contract(s))</strong> is checked with the same calculation
       as the backfill command. <strong>Preview</strong> saves nothing. <strong>Create</strong> saves a dues record for each contract that still
       owes something; rent receipts on those contracts then work. Contracts that already have a record are never touched, so it is safe to run again.</p>
    <div class="tdb-actions">
      <button type="button" class="tdb-btn" id="tdb-preview" {{ $remaining ? '' : 'disabled' }}><i class="fa fa-search" aria-hidden="true"></i> Preview</button>
      <button type="button" class="tdb-btn tdb-btn-primary" id="tdb-apply" disabled><i class="fa fa-check" aria-hidden="true"></i> Create dues records</button>
      <a href="{{ route('termination-dues.index') }}" class="tdb-btn"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to Termination Dues</a>
    </div>
    <div class="tdb-progress" id="tdb-progress" aria-hidden="true"><div></div></div>
    <div class="tdb-status" id="tdb-status" role="status" aria-live="polite"></div>
  </div>
</div>

<div class="card card-box" id="tdb-result" style="display:none">
  <div class="card-body">
    <div class="tdb-done" id="tdb-done"></div>
    <div class="tdb-counts">
      <div class="tdb-count"><span id="tdb-create-label">Would create</span><strong id="c-create">0</strong></div>
      <div class="tdb-count"><span>Already settled</span><strong id="c-settled">0</strong></div>
      <div class="tdb-count"><span>Nothing owed</span><strong id="c-nothing">0</strong></div>
      <div class="tdb-count"><span>Failed</span><strong id="c-failed">0</strong></div>
    </div>
    <div id="tdb-failures" style="display:none">
      <h4 style="font-size:16px">Failed <small>(nothing saved for these; send the message to support)</small></h4>
      <table><thead><tr><th>Contract</th><th>Reason</th></tr></thead><tbody id="tdb-fail-rows"></tbody></table>
      <br>
    </div>
    <table>
      <thead><tr><th>Contract</th><th>Tenant</th><th>Building / Unit</th><th>Terminated</th><th class="num">Owed</th><th class="num">Balance</th></tr></thead>
      <tbody id="tdb-rows"></tbody>
      <tfoot><tr><th colspan="4">Total</th><th class="num" id="t-owed">0.000</th><th class="num" id="t-balance">0.000</th></tr></tfoot>
    </table>
  </div>
</div>
</div>
@endsection

@section('scripts')
<script>
(function () {
  var total = {{ (int) $remaining }};
  var url = @json(route('termination-dues.backfill.batch'));
  var token = $('meta[name="csrf-token"]').attr('content');
  var busy = false;

  function fmt(n) { return Number(n).toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3}); }
  function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }

  function reset(apply) {
    $('#tdb-rows, #tdb-fail-rows').empty();
    $('#c-create, #c-settled, #c-nothing, #c-failed').text('0');
    $('#t-owed, #t-balance').text('0.000');
    $('#tdb-failures, #tdb-done').hide();
    $('#tdb-create-label').text(apply ? 'Created' : 'Would create');
    $('#tdb-result').show();
    $('#tdb-progress').show().find('div').css('width', '0');
  }

  function run(apply) {
    if (busy) { return; }
    busy = true;
    $('#tdb-preview, #tdb-apply').prop('disabled', true);
    reset(apply);
    var counts = {create: 0, settled: 0, nothing_owed: 0, failed: 0}, done = 0, owed = 0, balance = 0;

    function step(after) {
      $('#tdb-status').text((apply ? 'Creating' : 'Checking') + '... ' + done + ' of ' + total + ' contracts');
      $.ajax({method: 'POST', url: url, data: {after: after, apply: apply ? 1 : 0, _token: token}, dataType: 'json'})
        .done(function (r) {
          $.each(r.counts, function (k, v) { counts[k] += v; });
          done += r.counts.create + r.counts.settled + r.counts.nothing_owed + r.counts.failed;
          $.each(r.rows, function (i, row) {
            owed += Number(row.owed); balance += Number(row.balance);
            var no = row.url ? '<a href="' + esc(row.url) + '">' + esc(row.contract_no) + '</a>' : esc(row.contract_no);
            $('#tdb-rows').append('<tr><td>' + no + '</td><td>' + esc(row.tenant) + '</td><td>' + esc(row.unit) + '</td><td>' + esc(row.termination_date) +
              '</td><td class="num">' + fmt(row.owed) + '</td><td class="num">' + fmt(row.balance) + '</td></tr>');
          });
          $.each(r.failures, function (i, f) {
            $('#tdb-failures').show();
            $('#tdb-fail-rows').append('<tr><td>' + esc(f.contract_no) + '</td><td class="tdb-fail">' + esc(f.message) + '</td></tr>');
          });
          $('#c-create').text(counts.create); $('#c-settled').text(counts.settled);
          $('#c-nothing').text(counts.nothing_owed); $('#c-failed').text(counts.failed);
          $('#t-owed').text(fmt(owed)); $('#t-balance').text(fmt(balance));
          $('#tdb-progress div').css('width', (total ? Math.min(100, done * 100 / total) : 100) + '%');
          if (r.done) {
            busy = false;
            $('#tdb-progress div').css('width', '100%');
            if (apply) {
              $('#tdb-status').text('');
              $('#tdb-done').text('Done: ' + counts.create + ' dues record(s) created. Rent receipts on these contracts now work.').show();
              $('#tdb-preview').prop('disabled', false);
            } else {
              $('#tdb-status').text('Preview complete: nothing has been saved.');
              $('#tdb-preview').prop('disabled', false);
              $('#tdb-apply').prop('disabled', counts.create === 0)
                .html('<i class="fa fa-check" aria-hidden="true"></i> Create ' + counts.create + ' dues record(s)');
            }
          } else {
            step(r.last_contract_id);
          }
        })
        .fail(function (x) {
          busy = false;
          $('#tdb-preview').prop('disabled', false);
          $('#tdb-status').text('Stopped: the server returned an error (' + x.status + '). Records created so far are kept; run again to continue.');
        });
    }
    step(0);
  }

  $('#tdb-preview').on('click', function () { run(false); });
  $('#tdb-apply').on('click', function () { run(true); });
})();
</script>
@endsection
