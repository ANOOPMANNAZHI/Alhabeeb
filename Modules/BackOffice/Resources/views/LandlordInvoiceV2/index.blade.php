@extends('layouts.plms-app')
@section('css')
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class="pull-left">
            <div class="page-title">Landlord Invoice v2</div>
        </div>
        {{ Breadcrumbs::render('landlord-invoice-v2.index') }}
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="card card-box">
            <div class="card-body">
                <h4>
                    <div id="pagination_info">
                        @include('includes.pagination_info', ['paginator' => $landlordInvoicesV2])
                    </div>
                    <a href="{{ route('landlord-invoice-v2.create') }}" class="btn btn-circle btn-primary align-right">Add</a>
                    <div class="clr"></div>
                </h4>

                <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table display product-overview mb-30" id="liv2_list_table">
                            <thead>
                                <tr>
                                    <th>@sortablelink('invoice_no', 'Invoice No.', [], ['class' => 'sort_url'])</th>
                                    <th>Type</th>
                                    <th>@sortablelink('invoice_date', 'Date', [], ['class' => 'sort_url'])</th>
                                    <th>@sortablelink('vendor_name', 'Vendor', [], ['class' => 'sort_url'])</th>
                                    <th>@sortablelink('building_name', 'Building', [], ['class' => 'sort_url'])</th>
                                    <th>@sortablelink('grand_total', 'Total', [], ['class' => 'sort_url'])</th>
                                    <th width="12%">@sortablelink('status', 'Status', [], ['class' => 'sort_url'])</th>
                                    <th width="12%">Action</th>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="text" name="invoice_no" id="liv2_invoice_no" class="contract_search_field" value="{{ request('invoice_no') }}" placeholder="Invoice No.">
                                    </td>
                                    <td>
                                        <select name="invoice_type" class="contract_search_field">
                                            <option value="">Select</option>
                                            <option value="tax_invoice" {{ request('invoice_type') == 'tax_invoice' ? 'selected' : '' }}>Tax Invoice</option>
                                            <option value="other_deductions" {{ request('invoice_type') == 'other_deductions' ? 'selected' : '' }}>Other Deductions</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="date" name="invoice_date" id="liv2_invoice_date" class="contract_search_field" value="{{ request('invoice_date') }}">
                                    </td>
                                    <td>
                                        <input type="text" name="vendor_name" id="liv2_vendor_name" class="contract_search_field" value="{{ request('vendor_name') }}" placeholder="Vendor">
                                    </td>
                                    <td>
                                        <input type="text" name="building_name" id="liv2_building_name" class="contract_search_field" value="{{ request('building_name') }}" placeholder="Building">
                                    </td>
                                    <td></td>
                                    <td>
                                        <select name="status" class="contract_search_field">
                                            <option value="">Select</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                                        </select>
                                    </td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody id="liv2-search">
                                @include('backoffice::LandlordInvoiceV2.index_ajax')
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="pagination">
                    <div class="text-center">
                        {{ $landlordInvoicesV2->appends(request()->except(['page']))->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    var quickUrl = '{{ $route }}';

    $(document).on('change keyup paste', '.contract_search_field', function () {
        var invoiceNo    = $('#liv2_invoice_no').val();
        var invoiceType  = $('select[name="invoice_type"]').val();
        var invoiceDate  = $('#liv2_invoice_date').val();
        var vendorName   = $('#liv2_vendor_name').val();
        var buildingName = $('#liv2_building_name').val();
        var status       = $('select[name="status"]').val();

        $.ajax({
            method: 'GET',
            url: quickUrl,
            data: {
                invoice_no: invoiceNo,
                invoice_type: invoiceType,
                invoice_date: invoiceDate,
                vendor_name: vendorName,
                building_name: buildingName,
                status: status,
                ajax: true,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                $('#liv2-search').html('<tr><td colspan="8" align="center"><img src="{{ url("/") }}/public/img/pre-loader.gif" width="75" height="75"></td></tr>');
            },
            success: function (data) {
                var temp = $(data);
                var paginateInfo = temp.find('.pagination_info').clone();
                temp.find('.pagination_info').remove();
                var paginate = temp.find('#pagination_ajax').clone();
                temp.find('#pagination_ajax').remove();

                $('#liv2-search').html(temp);
                $('#pagination').html(paginate);
                $('#pagination_info').html(paginateInfo);

                var href_txt = $.param({
                    invoice_no: invoiceNo,
                    invoice_type: invoiceType,
                    invoice_date: invoiceDate,
                    vendor_name: vendorName,
                    building_name: buildingName,
                    status: status
                });

                $('.sort_url').each(function (i, n) {
                    var href = $(n).attr('href');
                    var hashes = href.slice(href.indexOf('sort'));
                    href = href.split('?')[0];
                    $(n).attr('href', href + '?' + href_txt + '&' + hashes);
                });
            }
        });
    });
});
</script>
@endsection
