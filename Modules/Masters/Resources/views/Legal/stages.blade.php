@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
@php $LC = \Modules\Masters\Services\LegalCase::class; @endphp
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left"><div class="page-title">Legal Stages</div></div>
    {{ Breadcrumbs::render('legal.stages') }}
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card card-box">
      <div class="card-body">
        <p class="text-muted">The lawyer moves a case forward through these stages in this order. A stage can repeat (another hearing) but a case cannot go back to an earlier stage. "Closed" is always last and is set when the case is closed.</p>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr><th>#</th><th>Stage</th><th>Applies to</th><th>Open cases</th><th>Status</th><th>Order</th><th></th></tr>
            </thead>
            <tbody>
              @foreach($stages as $i => $s)
              <tr @if(!$s->status) style="opacity:.55" @endif>
                <td>{{ $i + 1 }}</td>
                <td>{{ $s->name }}@if($s->code) <small class="text-muted">({{ $s->code }})</small>@endif</td>
                <td>{{ $LC::APPLIES_TO[$s->applies_to] ?? '' }}</td>
                <td>{{ $s->open_cases }}</td>
                <td>{{ $s->status ? 'Active' : 'Inactive' }}</td>
                <td style="white-space:nowrap;">
                  @unless($s->is_final)
                  <form method="POST" action="{{ route('legal.stages.move', $s->id) }}" style="display:inline;">{{ csrf_field() }}
                    <input type="hidden" name="dir" value="up"><button class="btn btn-xs btn-default" title="Move up" aria-label="Move {{ $s->name }} up">&uarr;</button></form>
                  <form method="POST" action="{{ route('legal.stages.move', $s->id) }}" style="display:inline;">{{ csrf_field() }}
                    <input type="hidden" name="dir" value="down"><button class="btn btn-xs btn-default" title="Move down" aria-label="Move {{ $s->name }} down">&darr;</button></form>
                  @endunless
                </td>
                <td><button type="button" class="btn btn-xs btn-primary lcs-edit" data-id="{{ $s->id }}" data-name="{{ $s->name }}"
                      data-applies="{{ $s->applies_to }}" data-status="{{ (int) $s->status }}" data-final="{{ (int) $s->is_final }}">Edit</button></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card card-box">
      <div class="card-head"><div class="col"><h4 id="lcs_title">Add stage</h4></div></div>
      <div class="card-body">
        <form method="POST" action="{{ route('legal.stages.store') }}">
          {{ csrf_field() }}
          <input type="hidden" name="id" id="lcs_id" value="">
          <div class="form-group">
            <label for="lcs_name">Stage name</label>
            <input type="text" id="lcs_name" name="name" class="form-control" maxlength="100" required>
          </div>
          <div class="form-group">
            <label for="lcs_applies">Applies to</label>
            <select id="lcs_applies" name="applies_to" class="form-control">
              @foreach($LC::APPLIES_TO as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </select>
          </div>
          <div class="form-group" id="lcs_status_group" style="display:none;">
            <label for="lcs_status">Status</label>
            <select id="lcs_status" name="status" class="form-control">
              <option value="1">Active</option><option value="0">Inactive</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-default" id="lcs_reset">New stage</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $('.lcs-edit').on('click', function () {
    var b = $(this);
    $('#lcs_title').text('Edit stage');
    $('#lcs_id').val(b.data('id'));
    $('#lcs_name').val(b.data('name')).focus();
    $('#lcs_applies').val(String(b.data('applies')));
    $('#lcs_status').val(String(b.data('status')));
    $('#lcs_status_group').toggle(b.data('final') != 1);
  });
  $('#lcs_reset').on('click', function () {
    $('#lcs_title').text('Add stage');
    $('#lcs_id').val(''); $('#lcs_name').val('').focus(); $('#lcs_applies').val('0'); $('#lcs_status_group').hide();
  });
</script>
@endsection
