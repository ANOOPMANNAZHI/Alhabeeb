{{-- Makes a <select data-searchable> typeable: a search box + filtered list (same look as the building
     search on Normal Management V2). The real <select> stays in the form, hidden, and keeps the chosen value,
     so the form posts exactly as before. Include once, after the select. --}}
<style>
  .sd-wrap { position: relative; }
  .sd-wrap .sd-input { padding-left: 34px; cursor: pointer; background: #fff; }
  .sd-wrap .sd-input:focus { cursor: text; }
  .sd-list { display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ced4da;
    border-top: none; border-radius: 0 0 4px 4px; max-height: 240px; overflow-y: auto; z-index: 1050; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
  .sd-list.open { display: block; }
  .sd-item { padding: 8px 12px; cursor: pointer; font-size: 13px; color: #333; }
  .sd-item:hover, .sd-item.sd-highlighted { background: #e8f0fe; color: #1F4E79; }
  .sd-item.sd-selected { font-weight: 600; }
  .sd-item.sd-hidden { display: none; }
  .sd-no-results { padding: 8px 12px; color: #999; font-size: 13px; font-style: italic; display: none; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var selects = document.querySelectorAll('select[data-searchable]');
  Array.prototype.forEach.call(selects, function (sel) {
    var wrap = sel.parentNode;
    wrap.classList.add('sd-wrap');
    var input = document.createElement('input');
    input.type = 'text'; input.className = 'form-control sd-input'; input.autocomplete = 'off';
    input.placeholder = sel.getAttribute('data-searchable') || 'Search…';
    input.setAttribute('aria-label', input.placeholder);
    var list = document.createElement('div'); list.className = 'sd-list';
    Array.prototype.forEach.call(sel.options, function (o) {
      var it = document.createElement('div'); it.className = 'sd-item'; it.textContent = o.text; it.setAttribute('data-value', o.value);
      list.appendChild(it);
    });
    var none = document.createElement('div'); none.className = 'sd-no-results'; none.textContent = 'No matches'; list.appendChild(none);
    sel.style.display = 'none';
    sel.parentNode.insertBefore(input, sel);
    sel.parentNode.insertBefore(list, sel.nextSibling);
    var items = list.querySelectorAll('.sd-item');

    function label() { return sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : ''; }
    function reset() {
      Array.prototype.forEach.call(items, function (i) { i.classList.remove('sd-hidden', 'sd-highlighted'); i.classList.toggle('sd-selected', i.getAttribute('data-value') === sel.value); });
      none.style.display = 'none';
    }
    function close() { list.classList.remove('open'); input.value = label(); reset(); }
    function choose(it) {
      sel.value = it.getAttribute('data-value');
      sel.dispatchEvent(new Event('change', { bubbles: true }));
      list.classList.remove('open'); input.value = label(); reset();
    }
    input.value = label();
    input.addEventListener('focus', function () { input.value = ''; reset(); list.classList.add('open'); });
    input.addEventListener('input', function () {
      var q = input.value.toLowerCase(), shown = 0;
      Array.prototype.forEach.call(items, function (i) {
        var hit = i.textContent.toLowerCase().indexOf(q) > -1;
        i.classList.toggle('sd-hidden', !hit); i.classList.remove('sd-highlighted'); if (hit) shown++;
      });
      none.style.display = shown ? 'none' : 'block';
      list.classList.add('open');
    });
    list.addEventListener('mousedown', function (e) {   // mousedown: runs before the input loses focus
      var it = e.target.closest ? e.target.closest('.sd-item') : null;
      if (it) { e.preventDefault(); choose(it); input.blur(); }
    });
    input.addEventListener('blur', function () { setTimeout(close, 120); });
    input.addEventListener('keydown', function (e) {
      var vis = Array.prototype.filter.call(items, function (i) { return !i.classList.contains('sd-hidden'); });
      var cur = list.querySelector('.sd-highlighted'), idx = vis.indexOf(cur);
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault(); list.classList.add('open');
        var next = vis[e.key === 'ArrowDown' ? Math.min(idx + 1, vis.length - 1) : Math.max(idx - 1, 0)];
        if (cur) cur.classList.remove('sd-highlighted');
        if (next) { next.classList.add('sd-highlighted'); next.scrollIntoView({ block: 'nearest' }); }
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (cur) choose(cur); else if (vis.length === 1) choose(vis[0]);
        input.blur();
      } else if (e.key === 'Escape') { input.blur(); }
    });
  });
});
</script>
