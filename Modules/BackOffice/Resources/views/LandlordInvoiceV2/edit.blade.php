@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<style>
    .liv2-section-head {
        font-size: 15px;
        font-weight: 700;
        color: #222;
        border-left: 4px solid #18c98e;
        padding: 6px 12px;
        margin: 20px 0 10px;
        background: #f7fdfb;
        border-radius: 4px;
    }
    .liv2-section-head:first-child { margin-top: 0; }
    #liv2_edit_lines_table thead th {
        background: #f7fdfb;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #666;
    }
</style>
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

<div class="liv2-section-head">Invoice Details</div>
<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice No.</label>
                <div class="p-relative">
                    <i class="fa fa-hashtag icn-add" aria-hidden="true"></i>
                    <input type="text" class="form-control" value="{{ $landlordInvoiceV2->invoice_no }}" disabled>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice Type</label>
                <div class="p-relative">
                    <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                    <input type="text" class="form-control" value="{{ $landlordInvoiceV2->invoice_type_label }}" disabled>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Vendor</label>
                <div class="p-relative">
                    <i class="icon icon-landlord" aria-hidden="true"></i>
                    <input type="text" class="form-control" value="{{ $landlordInvoiceV2->vendor_name }}" disabled>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Building</label>
                <div class="p-relative">
                    <i class="icon icon-building" aria-hidden="true"></i>
                    <input type="text" class="form-control" value="{{ $landlordInvoiceV2->building_name }}" disabled>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice Date<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="date" class="form-control" name="invoice_date" required value="{{ old('invoice_date', \Carbon\Carbon::parse($landlordInvoiceV2->invoice_date)->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="liv2-section-head">Invoice Lines</div>
<div class="dataSearchBox">
    <div class="row">
        <table class="table" id="liv2_edit_lines_table">
            <thead><tr><th>Description</th><th style="width:220px">Amount (OMR)</th></tr></thead>
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
