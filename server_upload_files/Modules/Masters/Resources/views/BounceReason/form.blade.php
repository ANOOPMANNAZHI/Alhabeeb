@extends('layouts.plms-app')

@php
  $submitted = old('_submitted');
  $active = $submitted ? (bool) old('is_active') : ($reason ? $reason->is_active : true);
  $fallback = $submitted ? (bool) old('is_fallback') : ($reason ? $reason->is_fallback : false);
@endphp

@section('css')
@include('masters::BounceReason._styles')
@endsection

@section('content')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">{{ $reason ? 'Edit Bounce Reason' : 'Add Bounce Reason' }}</div></div>
    @if($reason)
      {{ Breadcrumbs::render('bounce-reason.edit', $reason) }}
    @else
      {{ Breadcrumbs::render('bounce-reason.create') }}
    @endif
  </div>
</div>

<div class="br">
<div class="card card-box">
  <div class="card-body">
    <form method="POST" class="br-form" action="{{ $reason ? route('bounce-reason.update', $reason->id) : route('bounce-reason.store') }}">
      @csrf
      @if($reason) @method('PUT') @endif
      <input type="hidden" name="_submitted" value="1">

      @if($reason)
        <div class="br-field"><label>Code</label><div>{{ $reason->id }} <span class="br-help" style="display:inline">(stored on the PDC; cannot change)</span></div></div>
      @endif

      <div class="br-field">
        <label for="br-name">Reason</label>
        <input type="text" id="br-name" name="name" maxlength="100" required class="br-input" value="{{ old('name', $reason ? $reason->name : '') }}">
        @if($errors->has('name'))<div class="br-error">{{ $errors->first('name') }}</div>@endif
      </div>

      <div class="br-field">
        <label for="br-words">Bank sheet words</label>
        <textarea id="br-words" name="bank_keywords" maxlength="500" class="br-input" placeholder="e.g. account closed, frozen, dormant, 012">{{ old('bank_keywords', $reason ? $reason->bank_keywords : '') }}</textarea>
        <div class="br-help">Words or codes from the bank's rejection text, separated by commas (not case-sensitive). When a rejected cheque's text contains one,
          the bank sheet pre-selects this reason. If several reasons match, the one with the longest matching word wins.</div>
        @if($errors->has('bank_keywords'))<div class="br-error">{{ $errors->first('bank_keywords') }}</div>@endif
      </div>

      <div class="br-field">
        <label for="br-order">Order</label>
        <input type="number" id="br-order" name="sort_order" min="0" max="9999" class="br-input" style="max-width:140px" value="{{ old('sort_order', $reason ? $reason->sort_order : 50) }}">
        <div class="br-help">Position in the dropdowns (lower first).</div>
        @if($errors->has('sort_order'))<div class="br-error">{{ $errors->first('sort_order') }}</div>@endif
      </div>

      <label class="br-switch"><input type="checkbox" name="is_active" value="1" {{ $active ? 'checked' : '' }}>
        <div>Active<span>Off: no longer offered when bouncing a PDC. PDCs that already have it keep showing it.</span></div></label>
      <label class="br-switch"><input type="checkbox" name="is_fallback" value="1" {{ $fallback ? 'checked' : '' }}>
        <div>Use when the bank's text matches no reason<span>Only one reason can have this; ticking it here clears it from the others. Leave all unticked to make the user pick.</span></div></label>

      <div class="br-footer">
        <button type="submit" class="br-btn br-btn-primary"><i class="fa fa-check" aria-hidden="true"></i> Save reason</button>
        <a href="{{ route('bounce-reason.index') }}" class="br-btn">Cancel</a>
      </div>
    </form>
  </div>
</div>
</div>
@endsection
