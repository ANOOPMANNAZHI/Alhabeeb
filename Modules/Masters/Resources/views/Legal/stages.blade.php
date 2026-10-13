@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

@section('content')
@php $LC = \Modules\Masters\Services\LegalCase::class; @endphp
<div class="lg">
  <div class="lg-crumbs">{{ Breadcrumbs::render('legal.stages') }}</div>
  <header class="lg-page-head">
    <div>
      <h1 class="lg-page-head__title">Legal stages</h1>
      <div class="lg-page-head__sub">The steps a lawyer moves a case through, in order</div>
    </div>
  </header>
  <div class="lg-grid">
    <section class="lg-card" aria-label="Stages">
      <div class="lg-alert lg-alert--info" style="margin:12px 16px;"><div>A stage can repeat (another hearing) but a case cannot go back to an earlier stage. "Closed" is always last and is set when the case is closed.</div></div>
      <div class="lg-table-wrap">
        <table class="lg-table">
          <thead><tr><th scope="col">#</th><th scope="col">Stage</th><th scope="col">Applies to</th><th scope="col" class="num">Open cases</th><th scope="col">Status</th><th scope="col">Order</th><th scope="col"><span class="sr-only">Edit</span></th></tr></thead>
          <tbody>
            @foreach($stages as $i => $s)
            <tr @if(!$s->status) style="opacity:.6" @endif>
              <td class="tnum">{{ $i + 1 }}</td>
              <td class="strong">{{ $s->name }}@if($s->code) <span class="muted" style="font-weight:400;font-size:12px;">({{ $s->code }})</span>@endif</td>
              <td>{{ $LC::APPLIES_TO[$s->applies_to] ?? '' }}</td>
              <td class="num">{{ $s->open_cases }}</td>
              <td><span class="lg-badge lg-badge--{{ $s->status ? 'success' : 'neutral' }}">{{ $s->status ? 'Active' : 'Inactive' }}</span></td>
              <td style="white-space:nowrap;">
                @unless($s->is_final)
                <form method="POST" action="{{ route('legal.stages.move', $s->id) }}" style="display:inline;">{{ csrf_field() }}<input type="hidden" name="dir" value="up">
                  <button class="lg-btn lg-btn--ghost lg-btn--icon" title="Move up" aria-label="Move {{ $s->name }} up"><i class="fa fa-arrow-up" aria-hidden="true"></i></button></form>
                <form method="POST" action="{{ route('legal.stages.move', $s->id) }}" style="display:inline;">{{ csrf_field() }}<input type="hidden" name="dir" value="down">
                  <button class="lg-btn lg-btn--ghost lg-btn--icon" title="Move down" aria-label="Move {{ $s->name }} down"><i class="fa fa-arrow-down" aria-hidden="true"></i></button></form>
                @endunless
              </td>
              <td class="actions"><button type="button" class="lg-btn lg-btn--secondary lg-btn--sm lcs-edit" data-id="{{ $s->id }}" data-name="{{ $s->name }}"
                    data-applies="{{ $s->applies_to }}" data-status="{{ (int) $s->status }}" data-final="{{ (int) $s->is_final }}">Edit</button></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>
    <section class="lg-card" aria-labelledby="lcs_title">
      <div class="lg-card__head"><h2 class="lg-card__title" id="lcs_title">Add stage</h2></div>
      <div class="lg-card__body">
        <form method="POST" action="{{ route('legal.stages.store') }}">
          {{ csrf_field() }}
          <input type="hidden" name="id" id="lcs_id" value="">
          <div class="lg-field"><label for="lcs_name" class="lg-label">Stage name</label>
            <input type="text" id="lcs_name" name="name" class="lg-input" maxlength="100" required></div>
          <div class="lg-field"><label for="lcs_applies" class="lg-label">Applies to</label>
            <select id="lcs_applies" name="applies_to" class="lg-select">
              @foreach($LC::APPLIES_TO as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </select>
            <div class="lg-hint">New stages are added before "Closed".</div></div>
          <div class="lg-field" id="lcs_status_group" style="display:none;"><label for="lcs_status" class="lg-label">Status</label>
            <select id="lcs_status" name="status" class="lg-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
          <div style="display:flex;gap:8px;">
            <button type="submit" class="lg-btn lg-btn--primary">Save</button>
            <button type="button" class="lg-btn lg-btn--secondary" id="lcs_reset">New stage</button>
          </div>
        </form>
      </div>
    </section>
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
