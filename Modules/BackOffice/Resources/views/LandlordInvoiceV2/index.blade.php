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

                <form method="GET" id="liv2_filter_form">
                <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table display product-overview mb-30" id="liv2_list_table">
                            <thead>
                                <tr>
                                    <th>Invoice No.</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Vendor</th>
                                    <th>Building</th>
                                    <th>Total</th>
                                    <th width="12%">Status</th>
                                    <th width="12%">Action</th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <select name="invoice_type" class="contract_search_field">
                                            <option value="">Select</option>
                                            <option value="tax_invoice" {{ request('invoice_type') == 'tax_invoice' ? 'selected' : '' }}>Tax Invoice</option>
                                            <option value="other_deductions" {{ request('invoice_type') == 'other_deductions' ? 'selected' : '' }}>Other Deductions</option>
                                        </select>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <input type="date" name="from_date" class="contract_search_field" style="display:inline-block;width:auto;" value="{{ request('from_date') }}" title="From Date">
                                        <input type="date" name="to_date" class="contract_search_field" style="display:inline-block;width:auto;" value="{{ request('to_date') }}" title="To Date">
                                    </td>
                                    <td>
                                        <input type="text" name="vendor_name" class="contract_search_field" value="{{ request('vendor_name') }}" placeholder="Vendor">
                                    </td>
                                    <td>
                                        <input type="text" name="building_name" class="contract_search_field" value="{{ request('building_name') }}" placeholder="Building">
                                    </td>
                                    <td></td>
                                    <td>
                                        <select name="status" class="contract_search_field">
                                            <option value="">Select</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                                        </select>
                                    </td>
                                    <td><button type="submit" class="btn btn-tbl-view btn-xs" title="Filter"><i class="fa fa-search"></i></button></td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($landlordInvoicesV2 as $inv)
                                <tr>
                                    <td>{{ $inv->invoice_no }}</td>
                                    <td>{{ $inv->invoice_type_label }}</td>
                                    <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d/m/Y') }}</td>
                                    <td>{{ $inv->vendor_name }}</td>
                                    <td>{{ $inv->building_name }}</td>
                                    <td>{{ number_format($inv->grand_total, 3) }}</td>
                                    <td>
                                        @if($inv->status == 'voided')
                                            <span class="btn-circle btn-danger btn-sm m-b-10"><b>Voided</b></span>
                                        @else
                                            <span class="btn-circle btn-success btn-sm m-b-10"><b>Active</b></span>
                                        @endif
                                    </td>
                                    <td>
                                        <a title="Print" href="{{ route('landlordInvoiceV2Print', $inv) }}" target="_blank" class="btn btn-tbl-print btn-xs">
                                            <i class="fa fa-print"></i>
                                        </a>
                                        @if($inv->status != 'voided')
                                            <a title="Edit" href="{{ route('landlord-invoice-v2.edit', $inv) }}" class="btn btn-tbl-edit btn-xs">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <form action="{{ route('landlord-invoice-v2.destroy', $inv) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Void this invoice? The invoice number will be permanently reserved.');">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}
                                                <button type="submit" title="Void" class="btn btn-tbl-delete btn-xs">
                                                    <i class="fa fa-ban"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" align="center"><p>No Record</p></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                </form>

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
    var $form = $('#liv2_filter_form');
    var keyupTimer = 0;

    $form.find('select, input[type="date"]').on('change', function () {
        $form.trigger('submit');
    });

    $form.find('input[type="text"]').on('keyup', function () {
        window.clearTimeout(keyupTimer);
        keyupTimer = window.setTimeout(function () {
            $form.trigger('submit');
        }, 600);
    });
});
</script>
@endsection
