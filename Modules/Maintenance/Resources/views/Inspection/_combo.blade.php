{{-- Searchable <select class="ins-searchable">: a type-to-search box with its own list right under it
     (no jQuery UI menu - that one is mispositioned inside Bootstrap modals). The real select stays
     in the form, hidden, and gets the value + a change event. --}}
<style>
  .ins-combo { position:relative; }
  .ins-combo input.ins-combo-input { width:100%; min-height:40px; border:1px solid #cfd8e1; border-radius:8px; padding:8px 36px 8px 12px; background:#fff; color:#172d45; }
  .ins-combo:after { content:"\f002"; font-family:FontAwesome; position:absolute; right:12px; top:20px; transform:translateY(-50%); color:#8a99a8; pointer-events:none; }
  .ins-combo-list { position:absolute; left:0; right:0; top:100%; margin:4px 0 0; padding:4px 0; list-style:none; background:#fff; border:1px solid #cfd8e1;
    border-radius:8px; box-shadow:0 8px 24px rgba(23,45,69,.14); max-height:280px; overflow-y:auto; z-index:20; }
  .ins-combo-list li { padding:8px 12px; cursor:pointer; }
  .ins-combo-list li.active, .ins-combo-list li:hover { background:#eef5fc; color:#00487f; }
  .ins-combo-list li.empty { color:#5b6b7b; cursor:default; background:none; }
</style>
<script>
(function ($) {
  var uid = 0;
  window.insSearchable = function (root) {
    $(root || document).find('select.ins-searchable').each(function () {
      var $sel = $(this);
      if ($sel.data('insCombo')) { return; }
      var id = 'ins-combo-' + (++uid);
      var items = $sel.find('option').filter(function () { return this.value !== ''; })
        .map(function () { return { label: $(this).text(), id: this.value }; }).get();
      var $wrap = $('<div class="ins-combo"></div>');
      var $in = $('<input type="text" class="ins-combo-input" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false">')
        .attr({ placeholder: $sel.data('placeholder') || 'Type to search', 'aria-controls': id + '-list' });
      if ($sel.attr('id')) { $in.attr('id', $sel.attr('id') + '-search'); $('label[for="' + $sel.attr('id') + '"]').attr('for', $sel.attr('id') + '-search'); }
      var $list = $('<ul class="ins-combo-list" role="listbox" hidden></ul>').attr('id', id + '-list');
      $sel.after($wrap).hide().attr('tabindex', '-1');
      $wrap.append($in, $list);
      $sel.data('insCombo', $in);

      var shown = [], active = -1;
      function label() { return $sel.val() ? $sel.find('option:selected').text() : ''; }
      function close() { $list.prop('hidden', true); $in.attr('aria-expanded', 'false'); active = -1; }
      function render() {
        var t = $.trim($in.val()).toLowerCase();
        if (t === label().toLowerCase()) { t = ''; } // reopening on the chosen building shows the full list
        shown = $.grep(items, function (i) { return !t || i.label.toLowerCase().indexOf(t) !== -1; }).slice(0, 500);
        $list.empty();
        $.each(shown, function (k, i) { $list.append($('<li role="option"></li>').attr('id', id + '-' + k).text(i.label).data('k', k)); });
        if (!shown.length) { $list.append('<li class="empty">No building matches</li>'); }
        active = -1;
        $list.prop('hidden', false); $in.attr('aria-expanded', 'true');
      }
      function pick(k) {
        var i = shown[k];
        if (!i) { return; }
        $sel.val(i.id).trigger('change');
        $in.val(i.label);
        close();
      }
      function move(d) {
        if ($list.prop('hidden')) { render(); }
        if (!shown.length) { return; }
        active = (active + d + shown.length) % shown.length;
        var $li = $list.children().removeClass('active').eq(active).addClass('active');
        $in.attr('aria-activedescendant', $li.attr('id'));
        var li = $li[0];
        if (li.offsetTop < $list[0].scrollTop) { $list[0].scrollTop = li.offsetTop; }
        else if (li.offsetTop + li.offsetHeight > $list[0].scrollTop + $list[0].clientHeight) { $list[0].scrollTop = li.offsetTop + li.offsetHeight - $list[0].clientHeight; }
      }

      $in.val(label());
      $sel.on('ins:sync', function () { $in.val(label()); });
      $in.on('focus click', render).on('input', render);
      $in.on('keydown', function (e) {
        if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
        else if (e.key === 'Enter') { if (!$list.prop('hidden')) { e.preventDefault(); pick(active >= 0 ? active : (shown.length === 1 ? 0 : -1)); } }
        else if (e.key === 'Escape') { if (!$list.prop('hidden')) { e.stopPropagation(); close(); } }
        else if (e.key === 'Tab') { close(); }
      });
      // mousedown so the pick happens before the input loses focus
      $list.on('mousedown', 'li[role=option]', function (e) { e.preventDefault(); pick($(this).data('k')); });
      $in.on('blur', function () {
        setTimeout(function () {
          close();
          var match = $.grep(items, function (i) { return i.label === $in.val(); })[0];
          if (!match) { if ($sel.val()) { $sel.val('').trigger('change'); } $in.val(''); }
          else if ($sel.val() !== match.id) { $sel.val(match.id).trigger('change'); }
        }, 150);
      });
    });
  };
  $(function () { window.insSearchable(); });
})(jQuery);
</script>
