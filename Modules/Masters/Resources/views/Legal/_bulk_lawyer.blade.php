{{-- Bulk "Assign lawyer" on Active cases (user, 20/10/2026): PLM approvers tick unassigned cases (or all of them
     across pages) and give them one lawyer. Needs $unassignedIds (ids matching the filter bar) and $lcLawyers.
     The selection is kept in JS, so it survives paging / column filters that swap the rows by ajax. --}}
<div class="lg-bulk" id="lg_bulk">
  <span class="lg-bulk__count"><b id="lg_bulk_n">0</b> selected</span>
  @if(count($unassignedIds))
  <button type="button" class="lg-btn lg-btn--ghost lg-btn--sm" id="lg_bulk_all">Select all {{ count($unassignedIds) }} unassigned</button>
  @else
  <span class="muted">No unassigned cases match the filters.</span>
  @endif
  <button type="button" class="lg-btn lg-btn--ghost lg-btn--sm" id="lg_bulk_clear" hidden>Clear</button>
  <button type="button" class="lg-btn lg-btn--primary lg-btn--sm" id="lg_bulk_open" disabled data-toggle="modal" data-target="#lg_bulk_modal">
    <i class="fa fa-gavel" aria-hidden="true"></i> Assign lawyer</button>
</div>

<div class="modal lg-modal" id="lg_bulk_modal" tabindex="-1" role="dialog" aria-labelledby="lg_bulk_title">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form class="modal-content" method="POST" action="{{ route('activeCasesAssignLawyer') }}" id="lg_bulk_form">
      {{ csrf_field() }}
      <div class="modal-header">
        <h4 class="modal-title" id="lg_bulk_title">Assign lawyer</h4>
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
        <p class="lg-bulk__confirm" id="lg_bulk_confirm"></p>
        <p class="muted" style="font-size:12px;margin:0">Only cases with no lawyer are changed. Each case's timeline records the assignment. No message is sent.</p>
        <div id="lg_bulk_ids"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="lg-btn lg-btn--ghost" data-dismiss="modal">Cancel</button>
        <button type="submit" class="lg-btn lg-btn--primary" id="lg_bulk_submit" disabled>Assign</button>
      </div>
    </form>
  </div>
</div>

<style>
  .lg .lg-bulk { display:flex; flex-wrap:wrap; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid var(--lg-border); background:var(--lg-subtle); font-size:13px; }
  .lg .lg-bulk__count b { font-variant-numeric:tabular-nums; }
  .lg .lg-bulk #lg_bulk_open { margin-left:auto; }
  .lg .lg-table .lg-bulk-col { width:36px; text-align:center; }
  .lg .lg-table .lg-bulk-col input { width:16px; height:16px; cursor:pointer; }
  .lg-modal .lg-bulk__confirm { margin:12px 0 6px; font-size:14px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var all = @json($unassignedIds);
  var picked = {};   // id -> true
  function count() { return Object.keys(picked).length; }
  function sync() {
    var boxes = document.querySelectorAll('.lg-bulk-chk');
    var onPage = 0, pickedOnPage = 0;
    for (var i = 0; i < boxes.length; i++) {
      boxes[i].checked = !!picked[boxes[i].value];
      onPage++; if (boxes[i].checked) pickedOnPage++;
    }
    var head = document.getElementById('lg_bulk_page');
    if (head) { head.checked = onPage > 0 && pickedOnPage === onPage; head.indeterminate = pickedOnPage > 0 && pickedOnPage < onPage; head.disabled = onPage === 0; }
    var n = count();
    document.getElementById('lg_bulk_n').textContent = n;
    document.getElementById('lg_bulk_open').disabled = n === 0;
    document.getElementById('lg_bulk_clear').hidden = n === 0;
  }
  document.addEventListener('change', function (e) {
    var t = e.target;
    if (t.classList && t.classList.contains('lg-bulk-chk')) {
      if (t.checked) picked[t.value] = true; else delete picked[t.value];
      sync();
    } else if (t.id === 'lg_bulk_page') {
      var boxes = document.querySelectorAll('.lg-bulk-chk');
      for (var i = 0; i < boxes.length; i++) { if (t.checked) picked[boxes[i].value] = true; else delete picked[boxes[i].value]; }
      sync();
    } else if (t.id === 'lg_bulk_lawyer') {
      confirmText();
    }
  });
  var allBtn = document.getElementById('lg_bulk_all');
  if (allBtn) allBtn.addEventListener('click', function () { for (var i = 0; i < all.length; i++) picked[all[i]] = true; sync(); });
  document.getElementById('lg_bulk_clear').addEventListener('click', function () { picked = {}; sync(); });

  function confirmText() {
    var sel = document.getElementById('lg_bulk_lawyer');
    var n = count();
    var name = sel.value ? sel.options[sel.selectedIndex].text : '';
    document.getElementById('lg_bulk_confirm').textContent = name
      ? 'Assign ' + n + ' case' + (n === 1 ? '' : 's') + ' to ' + name + '?'
      : n + ' case' + (n === 1 ? '' : 's') + ' selected. Pick the lawyer.';
    document.getElementById('lg_bulk_submit').disabled = !sel.value || n === 0;
  }
  $('#lg_bulk_modal').on('show.bs.modal', confirmText);
  document.getElementById('lg_bulk_form').addEventListener('submit', function () {
    var box = document.getElementById('lg_bulk_ids');
    box.innerHTML = '';
    Object.keys(picked).forEach(function (id) {
      var i = document.createElement('input'); i.type = 'hidden'; i.name = 'ids[]'; i.value = id; box.appendChild(i);
    });
    var b = document.getElementById('lg_bulk_submit'); b.disabled = true; b.textContent = 'Assigning...';
  });
  // rows are swapped by ajax (paging, column filters): re-tick what was picked
  var body = document.getElementById('enquiry-search');
  if (body && window.MutationObserver) new MutationObserver(sync).observe(body, { childList: true });
  sync();
});
</script>
