{{-- Searchable <select class="ins-searchable">: a type-to-search box (jQuery UI autocomplete) over the real select,
     which stays in the form and gets the value + a change event. --}}
<style>
  .ins-combo { position:relative; }
  .ins-combo input.ins-combo-input { padding-right:32px !important; }
  .ins-combo:after { content:"\f002"; font-family:FontAwesome; position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#8a99a8; pointer-events:none; }
</style>
<script>
(function ($) {
  window.insSearchable = function (root) {
    $(root || document).find('select.ins-searchable').each(function () {
      var $sel = $(this);
      if ($sel.data('insCombo')) { return; }
      var items = $sel.find('option').filter(function () { return this.value !== ''; })
        .map(function () { return { label: $(this).text(), value: $(this).text(), id: this.value }; }).get();
      var $wrap = $('<div class="ins-combo"></div>');
      var $in = $('<input type="text" class="ins-combo-input" autocomplete="off">')
        .attr('placeholder', $sel.data('placeholder') || 'Type to search')
        .attr('aria-label', $sel.attr('aria-label') || $('label[for="' + $sel.attr('id') + '"]').text() || 'Search');
      $sel.after($wrap).hide().attr('tabindex', '-1');
      $wrap.append($in);
      $sel.data('insCombo', $in);
      function sync() { $in.val($sel.val() ? $sel.find('option:selected').text() : ''); }
      sync();
      $sel.on('ins:sync', sync);
      $in.autocomplete({
        minLength: 0, delay: 0, appendTo: $sel.closest('.modal').length ? $sel.closest('.modal') : null,
        source: function (req, res) {
          var t = $.trim(req.term).toLowerCase();
          res($.grep(items, function (i) { return !t || i.label.toLowerCase().indexOf(t) !== -1; }).slice(0, 80));
        },
        select: function (e, ui) { $sel.val(ui.item.id).trigger('change'); $in.val(ui.item.label); return false; },
        change: function () {
          var match = $.grep(items, function (i) { return i.label === $in.val(); })[0];
          if (!match) { $sel.val('').trigger('change'); $in.val(''); }
        }
      }).on('focus click', function () { $(this).autocomplete('search', ''); });
    });
  };
  $(function () { window.insSearchable(); });
})(jQuery);
</script>
