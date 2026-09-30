@extends('layouts.plms-app')
@section('css')
@include('backoffice::MessageTemplate._styles')
<script src="{{ asset('public/js/tinymce.js') }}"></script>
@endsection
@section('content')
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">New send</div></div>{{ Breadcrumbs::render('bulk-send.create') }}</div></div>
<div class="mt"><div class="card card-box"><div class="card-body">
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach</div>@endif
<form method="post" action="{{ route('bulk-send.review') }}" id="bs-form">
  @csrf
  <div class="mt-section"><h4>1. Message</h4>
    <div class="mt-field"><label>Bulk template</label>
      <select name="template_id" id="bs-template" class="mt-input">
        <option value="">— write a one-off message —</option>
        @foreach($templates as $t)
          <option value="{{ $t->id }}" data-audience="{{ $t->audience }}" data-sms="{{ $t->sms_body ? 1 : 0 }}" data-email="{{ ($t->email_subject && $t->email_body) ? 1 : 0 }}" {{ old('template_id') == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ \Modules\BackOffice\Services\MessagePlaceholders::AUDIENCES[$t->audience] }})</option>
        @endforeach
      </select>
      <div class="mt-help">Create Bulk templates on Mail → Templates.</div>
    </div>
    <div id="bs-oneoff">
      <div class="mt-field"><label>Email subject</label><input name="email_subject" id="bs-subject" class="mt-input" maxlength="255" value="{{ old('email_subject') }}">
        <select class="mt-input mt-ph" data-target="#bs-subject"></select></div>
      <div class="mt-field"><label>Email body</label><textarea name="email_body" id="bs-body">{{ old('email_body') }}</textarea></div>
      <div class="mt-field"><label>SMS text</label><textarea name="sms_body" id="bs-sms" class="mt-input" rows="3" maxlength="1000">{{ old('sms_body') }}</textarea>
        <select class="mt-input mt-ph" data-target="#bs-sms"></select></div>
    </div>
    <div class="mt-field"><label>Send by</label>
      <label><input type="checkbox" name="send_email" value="1" id="bs-email" {{ (session()->hasOldInput() ? old('send_email') : true) ? 'checked' : '' }}> Email</label>
      <label><input type="checkbox" name="send_sms" value="1" id="bs-send-sms" {{ old('send_sms') ? 'checked' : '' }}> Also send SMS</label>
      <div class="mt-help" id="bs-sms-help"></div>
    </div>
  </div>

  <div class="mt-section"><h4>2. Recipients</h4>
    <div class="mt-field">
      @foreach(\Modules\BackOffice\Entities\BulkSend::AUDIENCE_LABELS as $value => $label)
        <label><input type="radio" name="audience" value="{{ $value }}" {{ old('audience', 'tenant_active') === $value ? 'checked' : '' }}> {{ $label }}</label>
      @endforeach
    </div>
    <div id="bs-place">
      <div class="mt-field"><label>Locations</label>
        <select name="locations[]" id="bs-locations" class="mt-input" multiple size="6">
          @foreach($locations as $l)<option value="{{ $l->id }}" {{ in_array($l->id, (array) old('locations', [])) ? 'selected' : '' }}>{{ $l->locations_name }}</option>@endforeach
        </select><div class="mt-help">None selected = all locations.</div></div>
      <div class="mt-field"><label>Buildings (optional)</label>
        <select name="buildings[]" id="bs-buildings" class="mt-input" multiple size="8"></select>
        <div class="mt-help">None selected = all buildings of the chosen locations.</div></div>
    </div>
    <div id="bs-roles-box" style="display:none">
      <div class="mt-field"><label>Roles</label>
        <select name="roles[]" class="mt-input" multiple size="8">
          @foreach($roles as $r)<option value="{{ $r }}" {{ in_array($r, (array) old('roles', [])) ? 'selected' : '' }}>{{ $r }}</option>@endforeach
        </select><div class="mt-help">None selected = all staff.</div></div>
    </div>
    <p class="mt-count" id="bs-count"></p>
  </div>

  <div class="mt-footer"><button type="submit" class="mt-btn mt-btn-primary">Review</button> <a href="{{ route('bulk-send.index') }}" class="mt-btn">Back</a></div>
</form>
</div></div></div>
@endsection
@section('scripts')
<script>
(function () {
  var buildings = @json($buildings);
  var placeholders = @json($placeholders);
  var oldBuildings = @json(array_map('intval', (array) old('buildings', [])));
  var token = $('meta[name=csrf-token]').attr('content');

  function audience() { return $('input[name=audience]:checked').val(); }
  function tplAudience(a) { return a === 'landlord' ? 'landlord' : (a === 'staff' ? 'staff' : 'tenant'); }

  function fillBuildings() {
    var locs = ($('#bs-locations').val() || []).map(Number);
    var keep = ($('#bs-buildings').val() || []).map(Number).concat(oldBuildings);
    var $b = $('#bs-buildings').empty();
    buildings.forEach(function (b) {
      if (!locs.length || locs.indexOf(Number(b.location_id)) !== -1) {
        $b.append($('<option>').val(b.id).text(b.building_name).prop('selected', keep.indexOf(Number(b.id)) !== -1));
      }
    });
    oldBuildings = [];
  }

  function fillPlaceholders() {
    var list = placeholders[tplAudience(audience())] || {};
    $('.mt-ph').each(function () {
      var $s = $(this).empty().append($('<option>').val('').text('Insert placeholder…'));
      Object.keys(list).forEach(function (name) { if (!list[name].block) { $s.append($('<option>').val(name).text(list[name].label + '  {' + name + '}')); } });
    });
  }

  function syncTemplate() {
    var $o = $('#bs-template option:selected');
    var oneOff = !$o.val();
    $('#bs-oneoff').toggle(oneOff);
    var hasSms = oneOff ? $.trim($('#bs-sms').val()) !== '' : $o.data('sms') == 1;
    $('#bs-send-sms').prop('disabled', !hasSms);
    if (!hasSms) { $('#bs-send-sms').prop('checked', false); }
    $('#bs-sms-help').text(hasSms ? 'SMS is off unless you tick it.' : 'This message has no SMS text.');
  }

  function syncAudience() {
    var staff = audience() === 'staff';
    $('#bs-place').toggle(!staff);
    $('#bs-roles-box').toggle(staff);
    fillPlaceholders();
    count();
  }

  var timer;
  function count() {
    clearTimeout(timer);
    timer = setTimeout(function () {
      $('#bs-count').text('Counting…');
      $.post('{{ route('bulk-send.count') }}', $('#bs-form').serialize() + '&_token=' + token).done(function (c) {
        $('#bs-count').text(c.total + ' people — ' + c.email + ' with email, ' + c.mobile + ' with mobile');
      });
    }, 300);
  }

  $('#bs-locations').on('change', function () { fillBuildings(); count(); });
  $('#bs-buildings, select[name="roles[]"]').on('change', count);
  $('input[name=audience]').on('change', syncAudience);
  $('#bs-template').on('change', syncTemplate);
  $('#bs-sms').on('input', syncTemplate);
  $('.mt-ph').on('change', function () {
    var name = $(this).val(); if (!name) { return; }
    var el = $($(this).data('target'))[0]; var v = el.value, s = el.selectionStart || v.length;
    el.value = v.slice(0, s) + '{' + name + '}' + v.slice(el.selectionEnd || s); $(this).val(''); syncTemplate();
  });

  tinymce.init({ selector: '#bs-body', menubar: false, statusbar: false, branding: false, height: 300,
    plugins: 'link lists', toolbar: 'undo redo | bold italic underline | bullist numlist | link | placeholders',
    setup: function (editor) {
      editor.ui.registry.addMenuButton('placeholders', { text: 'Placeholder', fetch: function (cb) {
        var list = placeholders[tplAudience(audience())] || {};
        cb(Object.keys(list).filter(function (n) { return !list[n].block; }).map(function (n) {
          return { type: 'menuitem', text: list[n].label + '  {' + n + '}', onAction: function () { editor.insertContent('{' + n + '}'); } };
        }));
      } });
    } });
  $('#bs-form').on('submit', function () { tinymce.triggerSave(); });

  fillBuildings(); syncTemplate(); syncAudience();
})();
</script>
@endsection
