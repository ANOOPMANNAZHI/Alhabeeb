@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class="pull-left">
            <div class="page-title">Edit Landlord Invoice</div>
        </div>
        {{ Breadcrumbs::render('landlord-invoice-v2.edit') }}
    </div>
</div>

<form action="{{ route('landlord-invoice-v2.update', $landlordInvoiceV2) }}" method="POST" class="form-horizontal" data-toggle="validator">
{{ csrf_field() }}
{{ method_field('PUT') }}
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice No.</label>
                <input type="text" class="form-control" value="{{ $landlordInvoiceV2->invoice_no }}" disabled>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice Type</label>
                <input type="text" class="form-control" value="{{ $landlordInvoiceV2->invoice_type_label }}" disabled>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Vendor</label>
                <input type="text" class="form-control" value="{{ $landlordInvoiceV2->vendor_name }}" disabled>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Building</label>
                <input type="text" class="form-control" value="{{ $landlordInvoiceV2->building_name }}" disabled>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice Date<small class="textRed">*</small></label>
                <input type="date" class="form-control" name="invoice_date" required value="{{ old('invoice_date', \Carbon\Carbon::parse($landlordInvoiceV2->invoice_date)->format('Y-m-d')) }}">
            </div>
        </div>
    </div>
</div>

<h3>Invoice Lines</h3>
<div class="dataSearchBox">
    <div class="row">
        <table class="table">
            <thead><tr><th>Description</th><th>Amount (OMR)</th></tr></thead>
            <tbody>
                @foreach($landlordInvoiceV2->lines as $i => $line)
                <tr>
                    <td>
                        <input type="hidden" name="lines[{{ $i }}][id]" value="{{ $line->id }}">
                        {{ $line->description }}
                    </td>
                    <td><input type="number" step="0.001" class="form-control" name="lines[{{ $i }}][amount]" value="{{ old('lines.'.$i.'.amount', $line->amount) }}"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-12">
            <button type="submit" class="btn btn-primary">SAVE</button>
        </div>
    </div>
</div>

</div>
</div>
</div>
</form>
@endsection
