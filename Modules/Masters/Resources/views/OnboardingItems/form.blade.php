@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<link href="{{ asset('public/css/formlayout.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
@php $editing = isset($item); @endphp
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Onboarding Checklist Item</div>
        </div>
        {{ $editing ? Breadcrumbs::render('onboarding-items.edit', $item) : Breadcrumbs::render('onboarding-items.create') }}
    </div>
</div>

@include('includes.error')

<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ $editing ? route('onboarding-items.update', $item) : route('onboarding-items.store') }}" autocomplete="off" method="POST" id="form_sample_2" class="form-horizontal" data-toggle="validator">
  @csrf @if($editing) @method('PUT') @endif
  <div class="dataSearchBox">
    <div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <label>Group<small class="textRed">*</small></label>
          <div class="p-relative">
            <i class="fa fa-folder-o icn-add" aria-hidden="true"></i>
            <input type="text" class="form-control" name="group_name" required maxlength="100" list="obi-groups" placeholder="e.g. Keys" value="{{ old('group_name', $editing ? $item->group_name : '') }}">
            <datalist id="obi-groups">
              @foreach(\Modules\BackOffice\Entities\OnboardingChecklistItem::select('group_name')->distinct()->orderBy('group_name')->pluck('group_name') as $g)<option value="{{ $g }}">@endforeach
            </datalist>
          </div>
        </div>
      </div>
      <div class="col-sm-8">
        <div class="form-group">
          <label>Item<small class="textRed">*</small></label>
          <div class="p-relative">
            <i class="fa fa-check-square-o icn-add" aria-hidden="true"></i>
            <input type="text" class="form-control" name="title" required maxlength="200" placeholder="What the ARE has to check or record" value="{{ old('title', $editing ? $item->title : '') }}">
          </div>
        </div>
      </div>
      <div class="w-100"></div>
      <div class="col-sm-4">
        <div class="form-group">
          <label>Input type<small class="textRed">*</small></label>
          <select name="input_type" class="form-control" required>
            @foreach(['check' => 'Checkbox', 'text' => 'Text', 'number' => 'Number', 'count' => 'Count', 'photo' => 'Photo only', 'meter' => 'Meter (account no + reading)'] as $k => $label)
              <option value="{{ $k }}" {{ old('input_type', $editing ? $item->input_type : 'check') === $k ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-sm-2">
        <div class="form-group">
          <label>Order</label>
          <input type="number" class="form-control" name="sort_order" min="0" value="{{ old('sort_order', $editing ? $item->sort_order : '') }}" placeholder="auto">
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label>&nbsp;</label>
          <div>
            <label class="mr-3"><input type="checkbox" name="required" value="1" {{ old('required', $editing ? $item->required : true) ? 'checked' : '' }}> Required to complete</label>
            <label class="mr-3"><input type="checkbox" name="needs_photo" value="1" {{ old('needs_photo', $editing ? $item->needs_photo : false) ? 'checked' : '' }}> Needs photo</label>
            @if($editing)
              <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }}> Active</label>
            @endif
          </div>
        </div>
      </div>
      <div class="w-100"></div>
      <div class="col">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('onboarding-items.index') }}" class="btn btn-default">Cancel</a>
      </div>
    </div>
  </div>
  <div class="clearfix"></div>
</form>
</div>
</div>
</div>
@endsection

@section('scripts')
<script>
  $(document).ready(function () { if ($.fn.validate) { $("#form_sample_2").validate(); } });
</script>
@endsection
