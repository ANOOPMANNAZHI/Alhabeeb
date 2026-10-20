{{-- Bulk actions on Active cases (user, 20/10/2026), grouped in one menu:
       Assign lawyer (plms_approve) - only cases with no lawyer change, others are skipped;
       Close cases (legal_case_close) - old cases finished outside the system (Closed / Withdrawn + note);
       Hand over (plms_approve) - handled by MERA / Government (from the building) or the picked value, closed as handed over.
     Needs $lgBulk (['assign', 'close', 'handover' => bool]), $bulkCases (id => ['u' => 1 when no lawyer, 'h' => handled by
     the building suggests], for every case matching the filter bar) and $lcLawyers. The selection is kept in JS, so it
     survives paging and the column filters that swap the rows by ajax. --}}
@php
  $LC = \Modules\Masters\Services\LegalCase::class;
  $bulkUnassigned = count(array_filter($bulkCases, function ($c) { return $c['u']; }));
@endphp
<div class="lg-bulk" id="lg_bulk">
  <span class="lg-bulk__count"><b id="lg_bulk_n">0</b> selected</span>
  @if(count($bulkCases))
  <button type="button" class="lg-btn lg-btn--ghost lg-btn--sm" id="lg_bulk_all">Select all {{ count($bulkCases) }} matching</button>
  @endif
  @if($lgBulk['assign'] && $bulkUnassigned)
  <button type="button" class="lg-btn lg-btn--ghost lg-btn--sm" id="lg_bulk_all_un">Select all {{ $bulkUnassigned }} with no lawyer</button>
  @endif
  <button type="button" class="lg-btn lg-btn--ghost lg-btn--sm" id="lg_bulk_clear" hidden>Clear</button>
  <div class="dropdown lg-bulk__menu">
    <button type="button" class="lg-btn lg-btn--primary lg-btn--sm dropdown-toggle" id="lg_bulk_open" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disabled>
      Bulk actions</button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="lg_bulk_open">
      @if($lgBulk['assign'])
      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#lg_bulk_assign_modal"><i class="fa fa-gavel" aria-hidden="true"></i> Assign lawyer</a>
      @endif
      @if($lgBulk['handover'])
      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#lg_bulk_handover_modal"><i class="fa fa-exchange" aria-hidden="true"></i> Change handled by (hand over)</a>
      @endif
      @if($lgBulk['close'])
      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#lg_bulk_close_modal"><i class="fa fa-times-circle" aria-hidden="true"></i> Close cases</a>
      @endif
    </div>
  </div>
</div>

@if($lgBulk['assign'])
<div class="modal lg-modal" id="lg_bulk_assign_modal" tabindex="-1" role="dialog" aria-labelledby="lg_bulk_assign_title">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form class="modal-content lg-bulk-form" method="POST" action="{{ route('activeCasesAssignLawyer') }}" data-only-unassigned="1">
      {{ csrf_field() }}
      <div class="modal-header">
        <h4 class="modal-title" id="lg_bulk_assign_title">Assign lawyer</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <div class="lg-field">
          <label for="lg_bulk_lawyer" class="lg-label">Lawyer</label>
          <select id="lg_bulk_lawyer" name="lawyer_user_id" class="lg-select" required>
            <option value="">Select a lawyer</option>
            @foreach($lcLawyers as $id => $nm)<option value="{{ $id }}">{{ $nm }}</option>@endforeach
          </select>
        </div>
        <p class="lg-bulk__confirm" id="lg_bulk_assign_text"></p>
        <p class="muted lg-bulk__hint">Only cases with no lawyer are changed. Each case's timeline records the assignment. No message is sent.</p>
        <div class="lg-bulk-ids"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="lg-btn lg-btn--ghost" data-dismiss="modal">Cancel</button>
        <button type="submit" class="lg-btn lg-btn--primary" id="lg_bulk_assign_submit" disabled>Assign</button>
      </div>
    </form>
  </div>
</div>
@endif

@if($lgBulk['handover'])
<div class="modal lg-modal" id="lg_bulk_handover_modal" tabindex="-1" role="dialog" aria-labelledby="lg_bulk_handover_title">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form class="modal-content lg-bulk-form" method="POST" action="{{ route('activeCasesHandOver') }}">
      {{ csrf_field() }}
      <div class="modal-header">
        <h4 class="modal-title" id="lg_bulk_handover_title">Change handled by (hand over)</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <p class="lg-bulk__hint muted" style="margin-bottom:10px">Cases in a MERA building go to MERA and in a Government building to Government.
          Other buildings get:</p>
        <div class="lg-field">
          <label for="lg_bulk_handled" class="lg-label">Handled by (other buildings)</label>
          <select id="lg_bulk_handled" name="handled_by" class="lg-select" required>
            @foreach([2, 3, 4] as $h)<option value="{{ $h }}">{{ $LC::HANDLED_BY[$h] }}</option>@endforeach
          </select>
        </div>
        <div class="lg-bulk__grid">
          <div class="lg-field"><label for="lg_bulk_to" class="lg-label">Handed to (name)</label>
            <input type="text" id="lg_bulk_to" name="handed_to_name" class="lg-input" maxlength="150" placeholder="Blank = MERA / Government / Landlord"></div>
          <div class="lg-field"><label for="lg_bulk_date" class="lg-label">Handover date</label>
            <input type="date" id="lg_bulk_date" name="handover_date" class="lg-input" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"></div>
          <div class="lg-field"><label for="lg_bulk_ref" class="lg-label">Reference (optional)</label>
            <input type="text" id="lg_bulk_ref" name="handover_ref" class="lg-input" maxlength="100"></div>
        </div>
        <div class="lg-field" style="margin-top:10px">
          <label for="lg_bulk_hnote" class="lg-label">Note (optional)</label>
          <textarea id="lg_bulk_hnote" name="legal_note" class="lg-textarea" rows="2" maxlength="1000"></textarea>
        </div>
        <p class="lg-bulk__confirm" id="lg_bulk_handover_text"></p>
        <p class="muted lg-bulk__hint">Same as the PLM hand over: the case closes on our side as handed over and moves to Handed-over Cases;
          the contract stays in legal. Only active cases handled by Al Habib change. No message is sent.</p>
        <div class="lg-bulk-ids"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="lg-btn lg-btn--ghost" data-dismiss="modal">Cancel</button>
        <button type="submit" class="lg-btn lg-btn--primary" id="lg_bulk_handover_submit" disabled>Hand over</button>
      </div>
    </form>
  </div>
</div>
@endif

@if($lgBulk['close'])
<div class="modal lg-modal" id="lg_bulk_close_modal" tabindex="-1" role="dialog" aria-labelledby="lg_bulk_close_title">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form class="modal-content lg-bulk-form" method="POST" action="{{ route('activeCasesClose') }}">
      {{ csrf_field() }}
      <div class="modal-header">
        <h4 class="modal-title" id="lg_bulk_close_title">Close cases</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <div class="lg-field">
          <label for="lg_bulk_reason" class="lg-label">Reason</label>
          <select id="lg_bulk_reason" name="close_reason" class="lg-select" required>
            <option value="closed">Closed</option>
            <option value="withdrawn">Withdrawn</option>
          </select>
        </div>
        <div class="lg-field" style="margin-top:10px">
          <label for="lg_bulk_note" class="lg-label">Note</label>
          <textarea id="lg_bulk_note" name="close_note" class="lg-textarea" rows="3" maxlength="1000" required placeholder="e.g. Finished outside the system before PLMS"></textarea>
        </div>
        <p class="lg-bulk__confirm" id="lg_bulk_close_text"></p>
        <p class="muted lg-bulk__hint">Each case is closed the same way as from its case page: final stage, timeline entry, contract leaves legal.
          Cases with a settlement in progress, or handled by someone other than Al Habib, are skipped. No message is sent.</p>
        <div class="lg-bulk-ids"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="lg-btn lg-btn--ghost" data-dismiss="modal">Cancel</button>
        <button type="submit" class="lg-btn lg-btn--danger" id="lg_bulk_close_submit" disabled>Close cases</button>
      </div>
    </form>
  </div>
</div>
@endif

<style>
  .lg .lg-bulk { display:flex; flex-wrap:wrap; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid var(--lg-border); background:var(--lg-subtle); font-size:13px; }
  .lg .lg-bulk__count b { font-variant-numeric:tabular-nums; }
  .lg .lg-bulk__menu { margin-left:auto; }
  .lg .lg-bulk__menu .dropdown-item { font-size:13px; padding:8px 14px; }
  .lg .lg-bulk__menu .dropdown-item i { width:16px; color:var(--lg-muted); }
  .lg .lg-table .lg-bulk-col { width:36px; text-align:center; }
  .lg .lg-table .lg-bulk-col input { width:16px; height:16px; cursor:pointer; }
  .lg-modal .lg-bulk__confirm { margin:12px 0 6px; font-size:14px; }
  .lg-modal .lg-bulk__hint { font-size:12px; margin:0; }
  .lg-modal .lg-bulk__grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px; }
  .lg-modal .lg-bulk__grid .lg-field:first-child { grid-column:1 / -1; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var all = @json((object) $bulkCases);   // id -> {u: 1 when no lawyer, h: handled by the building suggests}
  var picked = {};                          // same shape, for the ticked cases
  var HANDLED = @json($LC::HANDLED_BY);
  function n() { return Object.keys(picked).length; }
  function nUnassigned() { var k = 0; for (var id in picked) if (picked[id].u) k++; return k; }
  function plural(k) { return k + ' case' + (k === 1 ? '' : 's'); }
  function el(id) { return document.getElementById(id); }

  function sync() {
    var boxes = document.querySelectorAll('.lg-bulk-chk'), onPage = 0, on = 0;
    for (var i = 0; i < boxes.length; i++) {
      boxes[i].checked = picked.hasOwnProperty(boxes[i].value);
      onPage++; if (boxes[i].checked) on++;
    }
    var head = el('lg_bulk_page');
    if (head) { head.checked = onPage > 0 && on === onPage; head.indeterminate = on > 0 && on < onPage; head.disabled = onPage === 0; }
    el('lg_bulk_n').textContent = n();
    el('lg_bulk_open').disabled = n() === 0;
    el('lg_bulk_clear').hidden = n() === 0;
  }
  function pick(box, on) {
    if (on) picked[box.value] = {u: box.getAttribute('data-unassigned') === '1' ? 1 : 0, h: parseInt(box.getAttribute('data-handled'), 10) || 1};
    else delete picked[box.value];
  }

  document.addEventListener('change', function (e) {
    var t = e.target;
    if (t.classList && t.classList.contains('lg-bulk-chk')) { pick(t, t.checked); sync(); }
    else if (t.id === 'lg_bulk_page') {
      var boxes = document.querySelectorAll('.lg-bulk-chk');
      for (var i = 0; i < boxes.length; i++) pick(boxes[i], t.checked);
      sync();
    } else if (t.id === 'lg_bulk_lawyer') { assignText(); }
    else if (t.id === 'lg_bulk_handled' || t.id === 'lg_bulk_date') { handoverText(); }
  });
  document.addEventListener('input', function (e) { if (e.target.id === 'lg_bulk_note') closeText(); });
  if (el('lg_bulk_all')) el('lg_bulk_all').addEventListener('click', function () { for (var id in all) picked[id] = all[id]; sync(); });
  if (el('lg_bulk_all_un')) el('lg_bulk_all_un').addEventListener('click', function () { for (var id in all) if (all[id].u) picked[id] = all[id]; sync(); });
  el('lg_bulk_clear').addEventListener('click', function () { picked = {}; sync(); });

  function assignText() {
    var sel = el('lg_bulk_lawyer'); if (!sel) return;
    var k = nUnassigned(), skip = n() - k;
    var name = sel.value ? sel.options[sel.selectedIndex].text : '';
    el('lg_bulk_assign_text').textContent = (name ? 'Assign ' + plural(k) + ' to ' + name + '?' : plural(k) + ' with no lawyer selected. Pick the lawyer.')
      + (skip ? ' ' + plural(skip) + ' already have a lawyer and will be skipped.' : '');
    el('lg_bulk_assign_submit').disabled = !sel.value || k === 0;
  }
  function closeText() {
    var note = el('lg_bulk_note'); if (!note) return;
    el('lg_bulk_close_text').textContent = 'Close ' + plural(n()) + '? This cannot be undone in bulk; a closed case can only be reopened one by one.';
    el('lg_bulk_close_submit').disabled = n() === 0 || note.value.trim() === '';
  }
  function handoverText() {
    var sel = el('lg_bulk_handled'); if (!sel) return;
    var counts = {};
    for (var id in picked) { var h = picked[id].h > 1 ? picked[id].h : parseInt(sel.value, 10); counts[h] = (counts[h] || 0) + 1; }
    var parts = [];
    for (var h in counts) parts.push(counts[h] + ' to ' + HANDLED[h]);
    el('lg_bulk_handover_text').textContent = 'Hand over ' + plural(n()) + (parts.length ? ': ' + parts.join(', ') : '') + '?';
    el('lg_bulk_handover_submit').disabled = n() === 0 || !el('lg_bulk_date').value;
  }
  $('#lg_bulk_handover_modal').on('show.bs.modal', handoverText);
  $('#lg_bulk_assign_modal').on('show.bs.modal', assignText);
  $('#lg_bulk_close_modal').on('show.bs.modal', closeText);

  var forms = document.querySelectorAll('.lg-bulk-form');
  for (var f = 0; f < forms.length; f++) forms[f].addEventListener('submit', function (e) {
    var form = e.target, box = form.querySelector('.lg-bulk-ids'), only = form.getAttribute('data-only-unassigned') === '1';
    box.innerHTML = '';
    for (var id in picked) {
      if (only && !picked[id]) continue;
      var i = document.createElement('input'); i.type = 'hidden'; i.name = 'ids[]'; i.value = id; box.appendChild(i);
    }
    var b = form.querySelector('button[type=submit]'); b.disabled = true; b.textContent = 'Please wait...';
  });

  // rows are swapped by ajax (paging, column filters): re-tick what was picked
  var body = el('enquiry-search');
  if (body && window.MutationObserver) new MutationObserver(sync).observe(body, { childList: true });
  sync();
});
</script>
