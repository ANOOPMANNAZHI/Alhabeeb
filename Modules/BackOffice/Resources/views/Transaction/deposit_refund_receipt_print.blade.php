<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='UTF-8'>
<title>Deposit Refund Receipt {{ $receipt->receipt_no }}</title>
<link rel='stylesheet' href="{{asset('public/css/print_style.css')}}">
<link rel='stylesheet' href="{{asset('public/css/print.css')}}" media="print">
<style>
  /* deduction table, styled to sit inside the standard voucher layout */
  table.ded { width: 100%; border-collapse: collapse; margin: 6px 0 4px; }
  table.ded th, table.ded td { border: 1px solid #999; padding: 6px 8px; font-size: 13px; }
  table.ded th { background: #f2f2f2; text-align: left; }
  table.ded td.num, table.ded th.num { text-align: right; }
  table.ded tr.sum td { font-weight: bold; background: #fafafa; }
  table.ded tr.net td { font-weight: bold; background: #eef7f2; }
  .noprint { margin: 10px 0; }
  @media print { .noprint { display: none; } }
</style>
</head>
<body>
<div id="page-wrap">

<div class="noprint">
  <button onclick="window.print()">Print</button>
</div>

<div id="identity">
<div id="logo" style="margin-top:0px">
  <img id="image" src="{{asset('public/img/logo-print.png')}}" alt="logo" style="height:70px;width:250px"/></div>
<div id="address" style="width:500px;margin-bottom:10px">
<b style="text-align:right">الحبيب وشركاه ش . م .م</b></br>
<b>Al Habib &amp; Co. L.L.C</b></br>
<span style="text-align:right">
ص. ب: 2663, روي الرمز البريدي : 112 , مسقط , سلطنة عمان
</span></br>
<span style="text-align:right">
هاتف :96824700247+|فاكس :96824703666+| س.ت. :1131575|المالية 10317401
</span></br>
P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman
</br>
Tel:+968 247 00247 | Fax:+968 247 03 666
</br> C.R. No: 1131575 | Finance code : 10317401
</div>
</div>
<div style="clear:both"></div>

<div id="header">DEPOSIT REFUND RECEIPT</div>

<div id="items">
  <div id="head-line">
    <div class="item-no"><label>No. :</label> {{ $receipt->receipt_no }}</div>
    <div class="item-date"><label>Date :</label> {{ \Carbon\Carbon::parse($receipt->receipt_date)->format('d/m/Y') }}</div>
  </div>

  <div class="item-row">
  </br>
    <div class="item-full-width"><label>Paid with thanks to: </label> {{ $receipt->tenant_name }}</div>
  </br>
  </div>

  <div class="item-row">
    <div class="item-full-width"><label>Property: </label>
      {{ $receipt->building_name }}@if($receipt->unit_code), Unit {{ $receipt->unit_code }}@endif
    </div></br>
  </div>

  <div class="item-row">
    <div class="item-full-width"><label>Against Refund No.:</label>
      {{ optional($receipt->depositRefund)->deposit_refund_no }}@if($receipt->deposit_receipt_no) &nbsp;|&nbsp; <label>Deposit Receipt No.:</label> {{ $receipt->deposit_receipt_no }}@endif
    </div></br>
  </div>

  <div class="item-row">
    <div class="item-full-width"><label>The sum of Rials Omani :</label> {{ ucwords($inWords) }}</div></br>
  </div>

  <div class="item-row">
    <div class="item-full-height"><label>Being: </label>
      Refund of security deposit after the deductions shown below.
    </div>
  </div>

  <table class="ded">
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
            <span style="color:#555">(deposit balance retained)</span>
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

  <div id="head-line">
    <div class="item-no"><label>R.O.: </label>
      <span class="item-box">{{ numberFormat($receipt->net_refund) }}</span>
    </div>
    <div class="item-date"><label>For Al Habib &amp; Co. L.L.C</label></div>
  </div>

  <div class="item-row">
  </br>
    <div class="item-full-width"><label>Please keep all the receipts for future reference.</label></div>
  </div>
</div>

</div>
</body>
</html>
