@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
{{-- Styles inline: the plms-app layout only yields 'content' and 'scripts'. --}}
<style>
    .drr-voucher { background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 26px 30px; max-width: 900px; margin: 0 auto; }
    .drr-voucher .co { text-align: center; border-bottom: 2px solid #333; padding-bottom: 12px; margin-bottom: 16px; }
    .drr-voucher .co .ar { font-weight: 700; font-size: 15px; }
    .drr-voucher .co .en { font-weight: 700; font-size: 15px; }
    .drr-voucher .co .addr { font-size: 11px; color: #666; line-height: 1.6; margin-top: 4px; }
    .drr-title { text-align: center; font-size: 16px; font-weight: 700; letter-spacing: .08em; margin: 6px 0 18px; }
    table.drr-fields { width: 100%; margin-bottom: 14px; }
    table.drr-fields td { padding: 5px 4px; font-size: 13px; vertical-align: top; }
    table.drr-fields td.lbl { color: #666; width: 20%; }
    table.drr-lines { width: 100%; border-collapse: collapse; margin: 10px 0; }
    table.drr-lines th, table.drr-lines td { border: 1px solid #ccc; padding: 8px 10px; font-size: 13px; }
    table.drr-lines th { background: #f5f5f5; text-align: left; text-transform: uppercase; font-size: 11px; letter-spacing: .04em; color: #555; }
    table.drr-lines td.num, table.drr-lines th.num { text-align: right; }
    table.drr-lines tr.sum td { font-weight: 700; background: #fafafa; }
    table.drr-lines tr.net td { font-weight: 700; background: #eef7f2; font-size: 14px; }
    .drr-foot { display: flex; justify-content: space-between; margin-top: 18px; font-size: 13px; }
    .drr-amountbox { border: 1px solid #333; padding: 6px 16px; font-weight: 700; }
    .drr-note { margin-top: 22px; font-size: 11px; color: #777; border-top: 1px dashed #ccc; padding-top: 10px; }
</style>

<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left">
      <div class="page-title">Deposit Refund Receipt</div>
    </div>
    {{ Breadcrumbs::render('depositRefundReceiptShow', $receipt) }}
  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box">
      <div class="card-head" style="padding:12px 16px">
        <a href="{{ route('depositRefundReceiptView', $receipt->id) }}" target="_blank" class="btn btn-circle btn-info align-right">
          Print
        </a>
        @if(optional($receipt->depositRefund)->id)
        <a href="{{ route('depositRefund.show', $receipt->depositRefund->id) }}" class="btn btn-circle btn-default align-right">
          Open Refund
        </a>
        @endif
        <a href="{{ route('depositRefundReceiptList') }}" class="btn btn-circle btn-default align-right">
          Back to List
        </a>
      </div>

      <div class="card-body">
        <div class="drr-voucher">

          <div class="co">
            <div class="ar">الحبيب وشركاه ش . م .م</div>
            <div class="en">Al Habib &amp; Co. L.L.C</div>
            <div class="addr">
              P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman<br>
              Tel: +968 247 00247 &nbsp;|&nbsp; Fax: +968 247 03 666<br>
              C.R. No: 1131575 &nbsp;|&nbsp; Finance code: 10317401
            </div>
          </div>

          <div class="drr-title">DEPOSIT REFUND RECEIPT</div>

          <table class="drr-fields">
            <tr>
              <td class="lbl">Receipt No.</td>
              <td><b>{{ $receipt->receipt_no }}</b></td>
              <td class="lbl">Date</td>
              <td>{{ \Carbon\Carbon::parse($receipt->receipt_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
              <td class="lbl">Paid with thanks to</td>
              <td colspan="3">{{ $receipt->tenant_name }} @if($receipt->tenant_code)<span class="text-muted">({{ $receipt->tenant_code }})</span>@endif</td>
            </tr>
            <tr>
              <td class="lbl">Property</td>
              <td>{{ $receipt->building_name }}</td>
              <td class="lbl">Unit</td>
              <td>{{ $receipt->unit_code }}</td>
            </tr>
            <tr>
              <td class="lbl">Against Refund No.</td>
              <td>{{ optional($receipt->depositRefund)->deposit_refund_no }}</td>
              <td class="lbl">Deposit Receipt No.</td>
              <td>{{ $receipt->deposit_receipt_no ?: '-' }}</td>
            </tr>
            <tr>
              <td class="lbl">The sum of Rials Omani</td>
              <td colspan="3">{{ ucwords($inWords) }}</td>
            </tr>
            <tr>
              <td class="lbl">Being</td>
              <td colspan="3">Refund of security deposit after the deductions shown below.</td>
            </tr>
          </table>

          <table class="drr-lines">
            <thead>
              <tr>
                <th width="6%">#</th>
                <th>Description</th>
                <th class="num" width="24%">Amount (R.O.)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td></td>
                <td><b>Security deposit held</b></td>
                <td class="num"><b>{{ numberFormat($receipt->deposit_amount) }}</b></td>
              </tr>
              @foreach($receipt->lines as $line)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  Less: {{ $line->description }}
                  @if($line->line_type === 'retained')
                    <span class="text-muted">(deposit balance retained)</span>
                  @endif
                </td>
                <td class="num">{{ numberFormat($line->amount) }}</td>
              </tr>
              @endforeach
              <tr class="sum">
                <td></td>
                <td>Total deducted</td>
                <td class="num">{{ numberFormat($receipt->deduction_total + $receipt->retained_amount) }}</td>
              </tr>
              <tr class="net">
                <td></td>
                <td>Net amount refunded</td>
                <td class="num">{{ numberFormat($receipt->net_refund) }}</td>
              </tr>
            </tbody>
          </table>

          <div class="drr-foot">
            <div>R.O. <span class="drr-amountbox">{{ numberFormat($receipt->net_refund) }}</span></div>
            <div><b>For Al Habib &amp; Co. L.L.C</b></div>
          </div>

          <div class="drr-note">
            Please keep all the receipts for future reference.
            @if($receipt->createdBy)
              <span style="float:right">Issued by {{ $receipt->createdBy->username }} on {{ $receipt->created_at->format('d/m/Y') }}</span>
            @endif
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
