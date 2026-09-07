@extends('layouts.plms-app')
@section('css')
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
    <div class="col-md-12">
        <div class="card card-box">
            <div class="card-body">
                <a href="{{ route('landlord-invoice-v2.create') }}" class="btn btn-primary pull-right">Add Invoice</a>
                <div class="clearfix"></div>

                <form method="GET" class="form-inline" style="margin-bottom:15px;">
                    <select name="invoice_type" class="form-control" style="margin-right:10px;">
                        <option value="">All Types</option>
                        <option value="tax_invoice" {{ request('invoice_type') == 'tax_invoice' ? 'selected' : '' }}>Tax Invoice</option>
                        <option value="other_deductions" {{ request('invoice_type') == 'other_deductions' ? 'selected' : '' }}>Other Deductions</option>
                    </select>
                    <select name="vendor_id" class="form-control" style="margin-right:10px;">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->vendor_name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-control" style="margin-right:10px;">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                    </select>
                    <button type="submit" class="btn btn-default">Filter</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Invoice No.</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Vendor</th>
                                <th>Building</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($landlordInvoicesV2 as $inv)
                            <tr>
                                <td>{{ $inv->invoice_no }}</td>
                                <td>{{ $inv->invoice_type_label }}</td>
                                <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d-m-Y') }}</td>
                                <td>{{ $inv->vendor_name }}</td>
                                <td>{{ $inv->building_name }}</td>
                                <td>{{ number_format($inv->grand_total, 3) }}</td>
                                <td>
                                    @if($inv->status == 'voided')
                                        <span class="label label-danger">Voided</span>
                                    @else
                                        <span class="label label-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('landlordInvoiceV2Print', $inv) }}" target="_blank" class="btn btn-sm btn-default">Print</a>
                                    @if($inv->status != 'voided')
                                        <a href="{{ route('landlord-invoice-v2.edit', $inv) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('landlord-invoice-v2.destroy', $inv) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Void this invoice? The invoice number will be permanently reserved.');">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <button type="submit" class="btn btn-sm btn-danger">Void</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8">No invoices found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $landlordInvoicesV2->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
