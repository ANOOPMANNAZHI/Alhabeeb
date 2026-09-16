@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
    .liv2-show { --liv2-blue: #2563eb; --liv2-blue-soft: #eff6ff; --liv2-ink: #1f2937; --liv2-muted: #6b7280; --liv2-line: #e5e7eb; --liv2-bg: #f9fafb; --liv2-radius: 8px; color: var(--liv2-ink); }

    /* Header card: number, type, status, actions */
    .liv2-show .liv2-head { display: flex; flex-wrap: wrap; gap: 16px 24px; align-items: flex-start; padding: 4px 0 16px; border-bottom: 1px solid var(--liv2-line); margin-bottom: 20px; }
    .liv2-show .liv2-head-id { flex: 1 1 260px; }
    .liv2-show .liv2-eyebrow { font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: var(--liv2-muted); margin-bottom: 4px; }
    .liv2-show .liv2-invoice-no { font-size: 26px; font-weight: 800; letter-spacing: .01em; line-height: 1.1; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .liv2-show .liv2-type { font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; background: var(--liv2-blue); color: #fff; letter-spacing: .03em; }
    .liv2-show .liv2-type-od { background: #e5e7eb; color: #374151; }
    .liv2-show .liv2-status { display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .liv2-show .liv2-status-active  { background: #dcfce7; color: #166534; }
    .liv2-show .liv2-status-posted  { background: #dbeafe; color: #1e40af; }
    .liv2-show .liv2-status-posting { background: #fef3c7; color: #92400e; }
    .liv2-show .liv2-status-voided  { background: #fee2e2; color: #991b1b; }
    .liv2-show .liv2-head-meta { display: flex; flex-wrap: wrap; gap: 8px 28px; margin-top: 10px; font-size: 13px; color: var(--liv2-muted); }
    .liv2-show .liv2-head-meta b { color: var(--liv2-ink); font-weight: 600; }
    .liv2-show .liv2-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .liv2-show .liv2-actions .btn, .liv2-show .liv2-actions form { margin: 0; }
    .liv2-show .liv2-actions .btn { min-height: 40px; display: inline-flex; align-items: center; gap: 6px; }
    .liv2-show .liv2-btn-ax { background: #0288d1; color: #fff; border-color: #0288d1; }

    /* Key/value panels */
    .liv2-show .liv2-panel { border: 1px solid var(--liv2-line); border-radius: var(--liv2-radius); padding: 16px 18px; margin-bottom: 20px; background: #fff; height: calc(100% - 20px); }
    .liv2-show .liv2-panel-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--liv2-blue); margin: 0 0 12px; padding-left: 10px; border-left: 3px solid var(--liv2-blue); }
    .liv2-show .liv2-kv { display: grid; grid-template-columns: 130px 1fr; gap: 8px 12px; font-size: 14px; margin: 0; }
    .liv2-show .liv2-kv dt { color: var(--liv2-muted); font-weight: 500; }
    .liv2-show .liv2-kv dd { margin: 0; font-weight: 500; word-break: break-word; }
    @media (max-width: 480px) { .liv2-show .liv2-kv { grid-template-columns: 1fr; gap: 2px 0; } .liv2-show .liv2-kv dd { margin-bottom: 8px; } }

    /* Lines */
    .liv2-show .liv2-section-head { display: flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 700; border-left: 4px solid var(--liv2-blue); padding: 8px 14px; margin: 4px 0 12px; background: var(--liv2-blue-soft); border-radius: 6px; }
    .liv2-show .liv2-table thead th { background: var(--liv2-bg); font-size: 12px; text-transform: uppercase; letter-spacing: .03em; color: var(--liv2-muted); border-bottom: 2px solid var(--liv2-line); }
    .liv2-show .liv2-table td, .liv2-show .liv2-table th { vertical-align: middle; }
    .liv2-show .liv2-table .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .liv2-show .liv2-table .liv2-head-cell { color: var(--liv2-muted); font-size: 13px; }
    .liv2-show .liv2-table tfoot td { font-weight: 600; background: var(--liv2-bg); }
    .liv2-show .liv2-table tfoot tr.liv2-grand td { font-size: 16px; font-weight: 800; color: var(--liv2-blue); }
    .liv2-show .liv2-words { font-size: 13px; color: var(--liv2-muted); margin-top: 6px; }
    .liv2-show .liv2-words b { color: var(--liv2-ink); }
    .liv2-show .liv2-audit { font-size: 12px; color: var(--liv2-muted); margin-top: 18px; display: flex; flex-wrap: wrap; gap: 6px 24px; }
</style>
@endsection

@section('content')
@php
    $inv    = $landlordInvoiceV2;
    $isTax  = $inv->invoice_type === 'tax_invoice';
    $period = \Carbon\Carbon::createFromDate($inv->period_year, $inv->period_month, 1)->format('F Y');
    $contract = $inv->landlordContract;
    if ($inv->status == 'voided')        { $statusClass = 'voided';  $statusText = 'Voided'; }
    elseif ($inv->isPosted())            { $statusClass = 'posted';  $statusText = 'Posted to AX'; }
    elseif ($inv->status == 'posting')   { $statusClass = 'posting'; $statusText = 'Posting…'; }
    else                                 { $statusClass = 'active';  $statusText = 'Active'; }
@endphp
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class="pull-left">
            <div class="page-title">View {{ $inv->invoice_type_label }}</div>
        </div>
        {{ Breadcrumbs::render('landlord-invoice-v2.show', $inv) }}
    </div>
</div>

<div class="row liv2-show">
<div class="col-sm-12">
<div class="card card-box salesSearchBox">

    <div class="liv2-head">
        <div class="liv2-head-id">
            <div class="liv2-eyebrow">Landlord invoice</div>
            <div class="liv2-invoice-no">
                {{ $inv->invoice_no }}
                <span class="liv2-type {{ $isTax ? '' : 'liv2-type-od' }}">{{ $inv->invoice_type_label }}</span>
                <span class="liv2-status liv2-status-{{ $statusClass }}">{{ $statusText }}</span>
            </div>
            <div class="liv2-head-meta">
                <span>Invoice date <b>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d/m/Y') }}</b></span>
                <span>Period <b>{{ $period }}</b></span>
                <span>Grand total <b>OMR {{ number_format($inv->grand_total, 3) }}</b></span>
                @if($inv->isPosted())
                <span>AX journal <b>{{ $inv->ax_batch_id }}</b>@if($inv->ax_invoice_no) / <b>{{ $inv->ax_invoice_no }}</b>@endif</span>
                @endif
            </div>
        </div>
        <div class="liv2-actions">
            <a href="{{ route('landlord-invoice-v2.index', ['tab' => $tab]) }}" class="btn btn-circle btn-default"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
            <a href="{{ route('landlordInvoiceV2Print', $inv) }}" target="_blank" class="btn btn-circle btn-default"><i class="fa fa-print" aria-hidden="true"></i> Print</a>
            @if($inv->status == 'active')
                <a href="{{ route('landlord-invoice-v2.edit', $inv) }}" class="btn btn-circle btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
                <form action="{{ route('landlord-invoice-v2.destroy', $inv) }}" method="POST" onsubmit="return confirm('Void invoice {{ $inv->invoice_no }}? The invoice number will be permanently reserved.');">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                    <button type="submit" class="btn btn-circle btn-danger"><i class="fa fa-ban" aria-hidden="true"></i> Void</button>
                </form>
                @if(auth()->user()->can('post_landlord_invoice_v2'))
                <form action="{{ route('landlordInvoiceV2Post', $inv) }}" method="POST" onsubmit="return confirm('Post invoice {{ $inv->invoice_no }} to Microsoft Dynamics AX? This cannot be undone.');">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-circle liv2-btn-ax"><i class="fa fa-upload" aria-hidden="true"></i> Post to AX</button>
                </form>
                @endif
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="liv2-panel">
                <h5 class="liv2-panel-title">Landlord</h5>
                <dl class="liv2-kv">
                    <dt>Name</dt><dd>{{ $inv->vendor_name ?: '-' }}</dd>
                    <dt>Address</dt><dd>{{ $inv->vendor_address ?: '-' }}</dd>
                    <dt>VATIN No</dt><dd>{{ $inv->vatin_no ?: '-' }}</dd>
                    <dt>Vendor code</dt><dd>{{ optional($inv->vendor)->vendor_code ?: '-' }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-md-6">
            <div class="liv2-panel">
                <h5 class="liv2-panel-title">Building &amp; contract</h5>
                <dl class="liv2-kv">
                    <dt>Building</dt><dd>{{ $inv->building_name ?: '-' }}</dd>
                    <dt>Building code</dt><dd>{{ optional(optional($contract)->buildingInfo)->building_code ?: '-' }}</dd>
                    <dt>Contract No</dt><dd>{{ optional($contract)->landlord_contract_no ?: '-' }}</dd>
                    <dt>Contract period</dt>
                    <dd>
                        @if($contract && $contract->landlord_contract_valid_from_date)
                            {{ $contract->landlord_contract_valid_from_date->format('d/m/Y') }}
                            @if($contract->landlord_contract_valid_to_date) – {{ $contract->landlord_contract_valid_to_date->format('d/m/Y') }}@endif
                        @else
                            -
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="liv2-section-head">Invoice lines <small style="margin-left:auto;font-weight:600;color:var(--liv2-muted)">{{ $isTax ? '5% VAT applied per line' : 'No VAT' }}</small></div>
    <div class="table-responsive">
        <table class="table liv2-table">
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    <th style="width:22%">Head</th>
                    <th>Description</th>
                    <th class="num" style="width:140px">Amount (OMR)</th>
                    @if($isTax)<th class="num" style="width:120px">VAT 5%</th>@endif
                    <th class="num" style="width:140px">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inv->lines as $line)
                <tr>
                    <td>{{ $line->line_order }}</td>
                    <td class="liv2-head-cell">{{ $line->head_label ?: '—' }}</td>
                    <td>{{ $line->description }}</td>
                    <td class="num">{{ number_format($line->amount, 3) }}</td>
                    @if($isTax)<td class="num">{{ number_format($line->vat_amount, 3) }}</td>@endif
                    <td class="num">{{ number_format($line->amount + $line->vat_amount, 3) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Subtotal</td>
                    <td class="num">{{ number_format($inv->subtotal, 3) }}</td>
                    @if($isTax)<td class="num">{{ number_format($inv->vat_total, 3) }}</td>@endif
                    <td class="num">{{ number_format($inv->subtotal + $inv->vat_total, 3) }}</td>
                </tr>
                <tr class="liv2-grand">
                    <td colspan="{{ $isTax ? 5 : 4 }}" class="text-right">Grand total (OMR)</td>
                    <td class="num">{{ number_format($inv->grand_total, 3) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <p class="liv2-words">Amount in words: <b>{{ $amountInWords }}</b></p>

    <div class="liv2-audit">
        <span>Created {{ $inv->created_at ? $inv->created_at->format('d/m/Y H:i') : '-' }}</span>
        @if($inv->isPosted())
        <span>Posted {{ $inv->posted_at ? \Carbon\Carbon::parse($inv->posted_at)->format('d/m/Y H:i') : '-' }}@if($inv->postedBy) by {{ $inv->postedBy->username }}@endif</span>
        @endif
        @if($inv->status == 'voided' && $inv->voided_at)
        <span>Voided {{ \Carbon\Carbon::parse($inv->voided_at)->format('d/m/Y H:i') }}</span>
        @endif
    </div>

</div>
</div>
</div>
@endsection
