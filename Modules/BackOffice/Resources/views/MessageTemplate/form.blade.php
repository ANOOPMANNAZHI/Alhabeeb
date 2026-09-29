@extends('layouts.plms-app')

@php
  $isSystem = $template && $template->isSystem();
  $audience = old('audience', $template ? $template->audience : 'tenant');
  $active = old('_submitted') ? (bool) old('is_active') : ($template ? $template->is_active : true);
@endphp

@section('css')
@include('backoffice::MessageTemplate._styles')
<script src="{{ asset('public/js/tinymce.js') }}"></script>
@endsection

@section('content')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">{{ $template ? 'Edit template' : 'New template' }}</div></div>
    @if($template)
      {{ Breadcrumbs::render('message-templates.edit', $template) }}
    @else
      {{ Breadcrumbs::render('message-templates.create') }}
    @endif
  </div>
</div>

<div class="mt">
<div class="card card-box">
  <div class="card-body">
    <form method="POST" id="mt-form" class="mt-form"
          action="{{ $template ? route('message-templates.update', $template->id) : route('message-templates.store') }}">
      @csrf
      @if($template) @method('PUT') @endif
      <input type="hidden" name="_submitted" value="1">

      <div class="mt-section">
        <h3>Details</h3>
        <div class="mt-grid">
          <div class="mt-field">
            <label for="mt-name">Name</label>
            <input type="text" id="mt-name" name="name" maxlength="150" required class="mt-input" value="{{ old('name', $template ? $template->name : '') }}">
            @if($errors->has('name'))<div class="mt-error">{{ $errors->first('name') }}</div>@endif
          </div>
          <div class="mt-field">
            <label for="mt-audience">Audience</label>
            <select id="mt-audience" name="audience" class="mt-input" {{ $isSystem ? 'disabled' : '' }}>
              @foreach($audiences as $value => $label)
                <option value="{{ $value }}" {{ $audience === $value ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @if($isSystem)<input type="hidden" name="audience" value="{{ $template->audience }}">@endif
            @if($errors->has('audience'))<div class="mt-error">{{ $errors->first('audience') }}</div>@endif
          </div>
          @if($isSystem)
            <div class="mt-field">
              <label for="mt-key">System key</label>
              <input type="text" id="mt-key" class="mt-input" value="{{ $template->key }}" readonly>
            </div>
          @endif
        </div>
        <label class="mt-switch"><input type="checkbox" name="is_active" value="1" {{ $active ? 'checked' : '' }}> Active</label>
        @if($isSystem)
          <p class="mt-help" style="margin-top:8px">System template: used by PLMS for an automatic message. It cannot be deleted; switch it off to stop using it.</p>
        @endif
      </div>

      @if($errors->has('channels'))<div class="mt-error" style="margin-bottom:16px">{{ $errors->first('channels') }}</div>@endif

      <div class="mt-section">
        <h3>Email</h3>
        <p class="mt-help">Leave the subject and body empty if this template is SMS only. Placeholders such as {tenant_name} are replaced when the message is sent.</p>
        <div class="mt-field">
          <label for="mt-subject">Subject</label>
          <div class="mt-row">
            <input type="text" id="mt-subject" name="email_subject" maxlength="255" class="mt-input" value="{{ old('email_subject', $template ? $template->email_subject : '') }}">
            <select class="mt-input mt-ph" data-target="#mt-subject" aria-label="Insert placeholder into subject"></select>
          </div>
          @if($errors->has('email_subject'))<div class="mt-error">{{ $errors->first('email_subject') }}</div>@endif
        </div>
        <div class="mt-field">
          <label for="mt-email-body">Body</label>
          <textarea id="mt-email-body" name="email_body" class="mt-input" rows="12">{{ old('email_body', $template ? $template->email_body : '') }}</textarea>
          @if($errors->has('email_body'))<div class="mt-error">{{ $errors->first('email_body') }}</div>@endif
        </div>
      </div>

      <div class="mt-section">
        <h3>SMS</h3>
        <p class="mt-help">Plain text. Leave empty if this template is email only.</p>
        <div class="mt-field">
          <div class="mt-row" style="margin-bottom:8px">
            <label for="mt-sms" style="flex:1;margin:0">Text</label>
            <select class="mt-input mt-ph" data-target="#mt-sms" aria-label="Insert placeholder into SMS"></select>
          </div>
          <textarea id="mt-sms" name="sms_body" maxlength="1000" class="mt-input" rows="4">{{ old('sms_body', $template ? $template->sms_body : '') }}</textarea>
          <div class="mt-count" id="mt-sms-count" aria-live="polite"></div>
          @if($errors->has('sms_body'))<div class="mt-error">{{ $errors->first('sms_body') }}</div>@endif
        </div>
      </div>

      <div class="mt-footer">
        <button type="submit" class="mt-btn mt-btn-primary"><i class="fa fa-check" aria-hidden="true"></i> Save template</button>
        <button type="button" class="mt-btn" id="mt-preview-btn"><i class="fa fa-eye" aria-hidden="true"></i> Preview</button>
        <a href="{{ route('message-templates.index') }}" class="mt-btn">Cancel</a>
      </div>
    </form>
  </div>
</div>
</div>
@endsection

@section('scripts')
@include('backoffice::MessageTemplate._preview_modal')
<script>
  (function () {
    var PLACEHOLDERS = @json($allPlaceholders);
    var systemKey = @json($template ? $template->key : null);

    function audience() { return $('#mt-audience').val(); }
    function current() { return PLACEHOLDERS[audience()] || PLACEHOLDERS.any; }

    function fillSelects() {
      $('.mt-ph').each(function () {
        var $s = $(this).empty().append('<option value="">Insert placeholder…</option>');
        $.each(current(), function (name, p) {
          $s.append($('<option>').val('{' + name + '}').text(p.label + '  {' + name + '}'));
        });
      });
    }

    function insertAtCursor(el, text) {
      var start = el.selectionStart || 0, end = el.selectionEnd || 0;
      el.value = el.value.substring(0, start) + text + el.value.substring(end);
      el.selectionStart = el.selectionEnd = start + text.length;
      el.focus();
      $(el).trigger('input');
    }

    // Same rules as MessageTemplateRenderer::smsParts.
    function smsParts(text) {
      var chars = Array.from(text).length, unicode = /[^\x00-\x7F]/.test(text);
      if (!chars) { return { chars: 0, unicode: unicode, parts: 0 }; }
      var single = unicode ? 70 : 160, multi = unicode ? 67 : 153;
      return { chars: chars, unicode: unicode, parts: chars <= single ? 1 : Math.ceil(chars / multi) };
    }
    function updateCount() {
      var p = smsParts($('#mt-sms').val());
      $('#mt-sms-count').text(p.chars ? p.chars + ' characters' + (p.unicode ? ' · Arabic/Unicode (70 per SMS)' : '') + ' · ' + p.parts + ' SMS' : 'Empty — no SMS version.');
    }

    $(function () {
      fillSelects();
      updateCount();
      $('#mt-audience').on('change', fillSelects);
      $('#mt-sms').on('input', updateCount);
      $('.mt-ph').on('change', function () {
        if (this.value) { insertAtCursor($($(this).data('target'))[0], this.value); }
        this.selectedIndex = 0;
      });

      tinymce.init({
        selector: '#mt-email-body',
        menubar: false, statusbar: false, branding: false,
        height: 360,
        plugins: 'link lists',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link | placeholders',
        setup: function (editor) {
          editor.ui.registry.addMenuButton('placeholders', {
            text: 'Placeholder',
            fetch: function (callback) {
              var items = [];
              $.each(current(), function (name, p) {
                items.push({ type: 'menuitem', text: p.label + '  {' + name + '}', onAction: function () { editor.insertContent('{' + name + '}'); } });
              });
              callback(items);
            }
          });
        }
      });

      $('#mt-form').on('submit', function () { tinymce.triggerSave(); });

      $('#mt-preview-btn').on('click', function () {
        tinymce.triggerSave();
        mtPreview({
          audience: audience(),
          key: systemKey,
          email_subject: $('#mt-subject').val(),
          email_body: $('#mt-email-body').val(),
          sms_body: $('#mt-sms').val()
        });
      });
    });
  })();
</script>
@endsection
