@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
{{-- Styles inline: the plms-app layout only yields 'content' and 'scripts'. --}}
<style>
    #drr_table th { white-space: nowrap; }
    #drr_table td.num, #drr_table th.num { text-align: right; }
    .drr-loading { opacity: .5; }
    .drr_search_field { width: 100%; box-sizing: border-box; }
</style>

<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left">
      <div class="page-title">Deposit Refund Receipts</div>
    </div>
    {{ Breadcrumbs::render('depositRefundReceiptList') }}
  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box">
      <div class="card-body">
        <p class="text-muted" style="margin-bottom:10px">
          Customer receipts for amounts deducted from a security deposit. Issued from a deposit refund; these never post to AX.
        </p>
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="drr_table">
            <thead>
              <tr>
                <th width="11%">Receipt No</th>
                <th width="9%">Date</th>
                <th width="11%">Refund No</th>
                <th>Tenant</th>
                <th>Building</th>
                <th width="10%">Unit</th>
                <th class="num" width="9%">Deposit</th>
                <th class="num" width="9%">Deducted</th>
                <th class="num" width="9%">Net Refund</th>
                <th width="8%">Action</th>
              </tr>
              <tr>
                <td><input type="text" id="drr_receipt_no" class="drr_search_field" value="{{ request('receipt_no') }}" placeholder="Receipt"></td>
                <td><input type="date" id="drr_receipt_date" class="drr_search_field" value="{{ request('receipt_date') }}"></td>
                <td><input type="text" id="drr_refund_no" class="drr_search_field" value="{{ request('refund_no') }}" placeholder="Refund"></td>
                <td><input type="text" id="drr_tenant_name" class="drr_search_field" value="{{ request('tenant_name') }}" placeholder="Tenant"></td>
                <td><input type="text" id="drr_building_name" class="drr_search_field" value="{{ request('building_name') }}" placeholder="Building"></td>
                <td><input type="text" id="drr_unit_code" class="drr_search_field" value="{{ request('unit_code') }}" placeholder="Unit"></td>
                <td></td>
                <td></td>
                <td></td>
                <td><button type="button" id="drr_reset" class="btn btn-default btn-xs" title="Clear filters">Clear</button></td>
              </tr>
            </thead>
            <tbody id="drr_body">
              @include('backoffice::Transaction.deposit_refund_receipt_list_ajax')
            </tbody>
          </table>
        </div>
        <div class="pagination_info">
          {{ $receipts->links() }}
          @include('includes.pagination_info', ['paginator' => $receipts])
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
$(function () {
    var url = "{{ route('depositRefundReceiptList') }}";
    var timer = null;

    function load() {
        var $tbody = $('#drr_body').addClass('drr-loading');
        $.get(url, {
            receipt_no:    $('#drr_receipt_no').val(),
            receipt_date:  $('#drr_receipt_date').val(),
            refund_no:     $('#drr_refund_no').val(),
            tenant_name:   $('#drr_tenant_name').val(),
            building_name: $('#drr_building_name').val(),
            unit_code:     $('#drr_unit_code').val()
        }, function (html) {
            $tbody.html(html).removeClass('drr-loading');
        }).fail(function () {
            $tbody.removeClass('drr-loading').html('<tr><td colspan="10" align="center" class="text-danger">Could not load data.</td></tr>');
        });
    }

    $('#drr_receipt_no, #drr_refund_no, #drr_tenant_name, #drr_building_name, #drr_unit_code').on('keyup', function (e) {
        if (e.key === 'Enter') { clearTimeout(timer); load(); return; }
        clearTimeout(timer);
        timer = setTimeout(load, 400);
    });
    $('#drr_receipt_date').on('change', load);
    $('#drr_reset').on('click', function () {
        $('#drr_receipt_no, #drr_receipt_date, #drr_refund_no, #drr_tenant_name, #drr_building_name, #drr_unit_code').val('');
        load();
    });
});
</script>
@endsection
