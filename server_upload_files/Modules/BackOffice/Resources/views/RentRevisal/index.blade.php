@extends('layouts.plms-app')
@section('content')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">Rent revisal</div></div>
    {{ Breadcrumbs::render('rentRevisal.index') }}
  </div>
</div>
@include('backoffice::RentRevisal._tabs')
<style>
  .rr-status{display:inline-block;padding:2px 9px;border-radius:999px;font-size:12px;font-weight:700;white-space:nowrap}
  .rr-st-none{background:#EEF0F2;color:#4A4B4D}
  .rr-st-draft{background:#FFF1DC;color:#9A5B00}
  .rr-st-sent{background:#DDF3E6;color:#1D6B3F}
</style>
<div class="row">
  <div class="col-md-12 col-sm-12">
    <div class="card card-box">
      <div class="card-body">
        <h4>
          <div id="pagination_info">@include('includes.pagination_info', ['paginator' => $contracts])</div>
          <div class="clr"></div>
        </h4>
        <p class="text-muted">Contracts ending in 90 to 150 days. Save the revised rent as a draft, or send it to the tenant now.</p>
        <form method="GET" class="form-inline" style="gap:8px;margin-bottom:12px">
          <input type="text" name="search" id="rr_search" class="form-control" placeholder="Contract, tenant or building" value="{{ $request->search }}">
          <select name="revisal" id="rr_revisal" class="form-control">
            <option value="">All status</option>
            <option value="due" {{ $request->revisal === 'due' ? 'selected' : '' }}>Not sent yet (not revised + drafts)</option>
            <option value="pending" {{ $request->revisal === 'pending' ? 'selected' : '' }}>Not revised</option>
            <option value="draft" {{ $request->revisal === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="done" {{ $request->revisal === 'done' ? 'selected' : '' }}>Sent</option>
          </select>
          <button class="btn btn-info">Filter</button>
        </form>
        <div class="table-wrap">
        <div class="table-responsive">
        <table class="table display product-overview mb-30" id="dtBasicExample">
          <thead>
            <tr>
              <th>Contract</th><th>Building</th><th>Unit No</th><th>End Date</th><th>Days Left</th><th>Rent</th><th>Tenant</th>
              <th>Revised Rent</th><th>Status</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
          @forelse($contracts as $c)
            @php
              $r = $c->rentRevisal; $end = \Carbon\Carbon::parse($c->tenant_contract_valid_to_date);
              $isDraft = $r && $r->isDraft();
              $by = $r ? (optional(optional($r->createdBy)->employee)->employee_name ?: optional($r->createdBy)->username) : '';
            @endphp
            <tr id="rr-row-{{ $c->id }}">
              <td>{{ $c->tenant_contract_no }}</td>
              <td>{{ $c->building->building_name ?? '' }}</td>
              <td>{{ $c->unit->unit_no ?? '' }}</td>
              <td>{{ $end->format('d/m/Y') }}</td>
              <td>{{ \Carbon\Carbon::today()->diffInDays($end, false) }}</td>
              <td>{{ numberFormat($c->tenant_contract_rent) }}</td>
              <td>{{ $c->tenant->tenant_name ?? '' }}</td>
              <td>{{ $r ? numberFormat($r->revised_rent) : '' }}</td>
              <td>
                @if(!$r)
                  <span class="rr-status rr-st-none">Not revised</span>
                @elseif($isDraft)
                  <span class="rr-status rr-st-draft">Draft</span>
                  <div class="text-muted small">saved {{ $r->updated_at->format('d/m/Y') }} by {{ $by }}</div>
                @else
                  <span class="rr-status rr-st-sent">Sent</span>
                  <div class="text-muted small">{{ $r->emailed_at ? $r->emailed_at->format('d/m/Y') : 'email: ' . $r->email_status }} by {{ $by }}</div>
                @endif
              </td>
              <td>
                @if($r && !$isDraft)
                  <a href="#" class="btn btn-tbl-view btn-xs rr-open" title="View" data-url="{{ route('rentRevisal.form', $c->id) }}"><i class="fa fa-eye"></i></a>
                @elseif($isDraft)
                  <a href="#" class="btn btn-tbl-edit btn-xs rr-open" title="Edit draft" data-url="{{ route('rentRevisal.form', $c->id) }}"><i class="fa fa-pencil"></i></a>
                @else
                  <a href="#" class="btn btn-tbl-general btn-xs rr-open" title="Revisal" data-url="{{ route('rentRevisal.form', $c->id) }}"><i class="fa fa-line-chart"></i></a>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="10" class="text-center text-muted">No contracts end in 90 to 150 days.</td></tr>
          @endforelse
          </tbody>
        </table>
        </div>
        </div>
        {{ $contracts->links() }}
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="rrModal" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content" id="rrModalBody"></div></div></div>
@endsection
@section('scripts')
<script>
$(document).on('click', '.rr-open', function (e) {
  e.preventDefault();
  $('#rrModalBody').html('<div class="modal-body">Loading...</div>');
  $('#rrModal').modal('show');
  $.get($(this).data('url')).done(function (html) { $('#rrModalBody').html(html); })
    .fail(function (x) { $('#rrModalBody').html('<div class="modal-body text-danger">' + (x.responseText || 'Could not open this contract.') + '</div>'); });
});
// Save as draft / Send now: the pressed button decides the action
$(document).on('click', '#rrForm .rr-submit', function () {
  $('#rrForm input[name=action]').val($(this).data('action'));
});
$(document).on('submit', '#rrForm', function (e) {
  e.preventDefault();
  var $f = $(this), $btns = $f.find('button[type=submit]').prop('disabled', true);
  $.post($f.attr('action'), $f.serialize()).done(function (res) {
    $f.find('.rr-msg').removeClass('text-danger').addClass('text-success').text(res.message);
    setTimeout(function () { location.reload(); }, 1200);
  }).fail(function (x) {
    var j = x.responseJSON || {};
    var m = j.message ? j.message : 'The revisal could not be saved.';
    if (j.needs_confirm && !$f.find('input[name=confirm_large]').length) {
      // large change: the next press (draft or send) confirms it
      $f.append('<input type="hidden" name="confirm_large" value="1">');
    }
    $f.find('.rr-msg').removeClass('text-success').addClass('text-danger').text(m);
    $btns.prop('disabled', false);
  });
});
$(document).on('input', '#rr_rent', function () {
  // amount changed: ask again if it is still a large change
  $('#rrForm input[name=confirm_large]').remove();
});
$(document).on('input', '#rr_rent, #rr_note', function () {
  $('#rr_prev_rent').text($('#rr_rent').val() || '...');
  $('#rr_prev_note').text($('#rr_note').val());
});
</script>
@endsection
