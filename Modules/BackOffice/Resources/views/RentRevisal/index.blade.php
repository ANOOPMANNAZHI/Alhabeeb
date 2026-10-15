@extends('layouts.plms-app')
@section('content')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">Rent revisal</div></div>
    {{ Breadcrumbs::render('rentRevisal.index') }}
  </div>
</div>
@include('backoffice::RentRevisal._tabs')
<div class="card card-box">
  <div class="card-body">
    <p class="text-muted">Contracts ending in 90 to 120 days. Enter the revised rent once it is decided; the tenant is emailed straight away.</p>
    <form method="GET" class="form-inline" style="gap:8px;margin-bottom:12px">
      <input type="text" name="search" id="rr_search" class="form-control" placeholder="Contract, tenant or building" value="{{ $request->search }}">
      <select name="revisal" id="rr_revisal" class="form-control">
        <option value="">All</option>
        <option value="pending" {{ $request->revisal === 'pending' ? 'selected' : '' }}>Not revised</option>
        <option value="done" {{ $request->revisal === 'done' ? 'selected' : '' }}>Revised</option>
      </select>
      <button class="btn btn-primary">Filter</button>
    </form>
    <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Contract</th><th>Tenant</th><th>Building / Unit</th><th>End date</th><th>Days left</th><th class="text-right">Current rent</th><th>Revisal</th><th></th></tr></thead>
      <tbody>
      @forelse($contracts as $c)
        @php $r = $c->rentRevisal; $end = \Carbon\Carbon::parse($c->tenant_contract_valid_to_date); @endphp
        <tr id="rr-row-{{ $c->id }}">
          <td>{{ $c->tenant_contract_no }}</td>
          <td>{{ $c->tenant->tenant_name ?? '' }}</td>
          <td>{{ $c->building->building_name ?? '' }} / {{ $c->unit->unit_no ?? '' }}</td>
          <td>{{ $end->format('d/m/Y') }}</td>
          <td>{{ \Carbon\Carbon::today()->diffInDays($end, false) }}</td>
          <td class="text-right">{{ number_format((float) $c->tenant_contract_rent, 3) }}</td>
          <td>
            @if($r)
              OMR {{ number_format((float) $r->revised_rent, 3) }}
              <div class="text-muted small">{{ $r->emailed_at ? 'emailed ' . $r->emailed_at->format('d/m/Y') : 'not emailed (' . $r->email_status . ')' }} by {{ optional(optional($r->createdBy)->employee)->employee_name ?: optional($r->createdBy)->username }}</div>
            @else
              <span class="text-muted">Not revised</span>
            @endif
          </td>
          <td><button type="button" class="btn btn-sm {{ $r ? 'btn-default' : 'btn-primary' }} rr-open" data-url="{{ route('rentRevisal.form', $c->id) }}">{{ $r ? 'View' : 'Revisal' }}</button></td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center text-muted">No contracts end in 90 to 120 days.</td></tr>
      @endforelse
      </tbody>
    </table>
    </div>
    {{ $contracts->links() }}
  </div>
</div>
<div class="modal fade" id="rrModal" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content" id="rrModalBody"></div></div></div>
@endsection
@section('scripts')
<script>
$(document).on('click', '.rr-open', function () {
  $('#rrModalBody').html('<div class="modal-body">Loading...</div>');
  $('#rrModal').modal('show');
  $.get($(this).data('url')).done(function (html) { $('#rrModalBody').html(html); })
    .fail(function (x) { $('#rrModalBody').html('<div class="modal-body text-danger">' + (x.responseText || 'Could not open this contract.') + '</div>'); });
});
$(document).on('submit', '#rrForm', function (e) {
  e.preventDefault();
  var $f = $(this), $btn = $f.find('button[type=submit]').prop('disabled', true);
  $.post($f.attr('action'), $f.serialize()).done(function (res) {
    $f.find('.rr-msg').removeClass('text-danger').addClass('text-success').text(res.message);
    setTimeout(function () { location.reload(); }, 1200);
  }).fail(function (x) {
    var j = x.responseJSON || {};
    var m = j.message ? j.message : 'The revisal could not be saved.';
    if (j.needs_confirm) {
      // large change: the next press confirms it
      if (!$f.find('input[name=confirm_large]').length) { $f.append('<input type="hidden" name="confirm_large" value="1">'); }
      $btn.text('Confirm and send');
    }
    $f.find('.rr-msg').removeClass('text-success').addClass('text-danger').text(m);
    $btn.prop('disabled', false);
  });
});
$(document).on('input', '#rr_rent', function () {
  // amount changed: ask again if it is still a large change
  $('#rrForm input[name=confirm_large]').remove();
  $('#rrForm button[type=submit]').text('Send to tenant');
});
$(document).on('input', '#rr_rent, #rr_note', function () {
  $('#rr_prev_rent').text($('#rr_rent').val() || '...');
  $('#rr_prev_note').text($('#rr_note').val());
});
</script>
@endsection
