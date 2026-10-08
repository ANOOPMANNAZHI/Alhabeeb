@php
  // Landlord Invoice v2 print (dompdf 0.8: tables only, no flex/grid). Brand blues from the logo.
  $isTax  = $invoice->invoice_type == 'tax_invoice';
  $title  = $isTax ? 'Tax Invoice' : 'Other Deductions Invoice';
  $period = date('F', mktime(0, 0, 0, $invoice->period_month, 1)) . ' ' . $invoice->period_year;
  // lines with no amount add nothing to the invoice; leave them off the print
  $printLines = array_values(array_filter($lines, function ($l) { return round($l['total'], 3) != 0; }));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }} {{ $invoice->invoice_no }}</title>
<style>
  @page { margin: 28px 34px 40px 34px; }
  body { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; color: #1f2937; line-height: 1.45; }
  table { border-collapse: collapse; width: 100%; }
  td, th { vertical-align: top; }

  /* letterhead */
  .lh td { padding: 0; }
  .lh .logo img { width: 190px; height: auto; }
  .lh .co { text-align: right; font-size: 9px; color: #4b5563; line-height: 1.5; }
  .lh .co .name { font-size: 12.5px; font-weight: bold; color: #0B4F9C; letter-spacing: .3px; }
  .rule { height: 3px; background: #0B4F9C; margin-top: 10px; }
  .rule2 { height: 1px; background: #1BA8DF; margin-top: 2px; }

  /* title + reference */
  .head { margin-top: 18px; }
  .head .title { font-size: 20px; font-weight: bold; color: #0B4F9C; text-transform: uppercase; letter-spacing: 1.5px; }
  .head .sub { font-size: 9.5px; color: #6b7280; margin-top: 2px; }
  .ref { width: 100%; border: 1px solid #d6e2f0; }
  .ref td { padding: 5px 9px; font-size: 10px; }
  .ref td.k { color: #6b7280; width: 46%; background: #f3f7fc; }
  .ref td.v { font-weight: bold; color: #111827; text-align: right; }
  .ref tr + tr td { border-top: 1px solid #e5edf6; }

  /* parties */
  .parties { margin-top: 16px; }
  .card { border: 1px solid #d6e2f0; }
  .card .cap { background: #0B4F9C; color: #fff; font-size: 9px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; padding: 5px 10px; }
  .card .body { padding: 8px 10px 9px; }
  .card .main { font-size: 12px; font-weight: bold; color: #111827; }
  .card table.kv td { padding: 2px 0; font-size: 10px; }
  .card table.kv td.k { color: #6b7280; width: 92px; }

  /* lines */
  .items { margin-top: 18px; }
  .items th { background: #0B4F9C; color: #fff; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .6px; padding: 7px 8px; text-align: right; }
  .items th.l { text-align: left; }
  .items td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10.5px; text-align: right; }
  .items td.l { text-align: left; }
  .items td.no { color: #6b7280; width: 22px; text-align: left; }
  .items tr.alt td { background: #f7fafd; }
  .items td.empty { text-align: center; color: #6b7280; padding: 16px; }

  /* totals */
  .sum { margin-top: 10px; }
  .totals td { padding: 5px 10px; font-size: 10.5px; }
  .totals td.k { color: #4b5563; }
  .totals td.v { text-align: right; font-weight: bold; }
  .totals tr.due td { background: #0B4F9C; color: #fff; font-size: 12px; font-weight: bold; padding: 8px 10px; }
  .words { border-left: 3px solid #1BA8DF; background: #f3f7fc; padding: 8px 10px; font-size: 10px; }
  .words .k { display: block; font-size: 8.5px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
  .words .v { font-weight: bold; color: #111827; }

  /* signatures */
  .sign { margin-top: 56px; }
  .sign td { width: 50%; }
  .sign .line { border-top: 1px solid #9ca3af; width: 210px; padding-top: 5px; font-size: 9.5px; color: #4b5563; }
  .sign .co { font-weight: bold; color: #0B4F9C; }
  .sign .right { text-align: right; }
  .sign .right .line { margin-left: auto; text-align: right; }

  .void { margin-top: 12px; border: 2px solid #b91c1c; color: #b91c1c; text-align: center; font-weight: bold; font-size: 13px; letter-spacing: 2px; padding: 6px; }
  .foot { position: fixed; bottom: -24px; left: 0; right: 0; text-align: center; font-size: 8.5px; color: #9ca3af; }
</style>
</head>
<body>

<div class="foot">Al Habib &amp; Co. L.L.C &middot; P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman &middot; {{ $invoice->invoice_no }}</div>

{{-- Letterhead --}}
<table class="lh">
  <tr>
    <td class="logo" style="width:50%"><img src="{{ public_path('img/logo-print.png') }}" alt="Al Habib"></td>
    <td class="co">
      <div class="name">Al Habib &amp; Co. L.L.C</div>
      P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman<br>
      Tel: +968 247 00247 &nbsp;|&nbsp; Fax: +968 247 03 666<br>
      C.R. No: 1131575 &nbsp;|&nbsp; Finance code: 10317401<br>
      VATIN: OM110001282X
    </td>
  </tr>
</table>
<div class="rule"></div><div class="rule2"></div>

@if($invoice->status === 'voided')
<div class="void">THIS INVOICE HAS BEEN VOIDED</div>
@endif

{{-- Title + reference --}}
<table class="head">
  <tr>
    <td style="width:55%; padding-right:20px">
      <div class="title">{{ $title }}</div>
      <div class="sub">{{ $isTax ? 'Management and service charges, VAT 5%' : 'Building expenses deducted from the landlord, no VAT' }}</div>
    </td>
    <td>
      <table class="ref">
        <tr><td class="k">Invoice No.</td><td class="v">{{ $invoice->invoice_no }}</td></tr>
        <tr><td class="k">Invoice Date</td><td class="v">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d.m.Y') }}</td></tr>
        <tr><td class="k">Period</td><td class="v">{{ $period }}</td></tr>
      </table>
    </td>
  </tr>
</table>

{{-- Bill to / Property --}}
<table class="parties">
  <tr>
    <td style="width:55%; padding-right:12px">
      <div class="card">
        <div class="cap">Bill To</div>
        <div class="body">
          <div class="main">{{ $invoice->vendor_name }}</div>
          <table class="kv" style="margin-top:4px">
            <tr><td class="k">Address</td><td>{{ $invoice->vendor_address ?: '-' }}</td></tr>
            <tr><td class="k">VATIN</td><td>{{ $invoice->vatin_no ?: '-' }}</td></tr>
          </table>
        </div>
      </div>
    </td>
    <td>
      <div class="card">
        <div class="cap">Property</div>
        <div class="body">
          <div class="main">{{ $invoice->building_name }}</div>
          <table class="kv" style="margin-top:4px">
            <tr><td class="k">Billing period</td><td>{{ $period }}</td></tr>
            @if($invoice->status === 'posted')
            <tr><td class="k">AX batch</td><td>{{ $invoice->ax_batch_id }}</td></tr>
            <tr><td class="k">Posted on</td><td>{{ $invoice->posted_at ? \Carbon\Carbon::parse($invoice->posted_at)->format('d.m.Y') : '-' }}</td></tr>
            @endif
          </table>
        </div>
      </div>
    </td>
  </tr>
</table>

{{-- Lines --}}
<table class="items">
  <thead>
    <tr>
      <th class="l" style="vertical-align:bottom">#</th>
      <th class="l" style="vertical-align:bottom">Description</th>
      @if($isTax)
      <th style="width:52px; vertical-align:bottom">Qty</th>
      <th style="width:78px">Unit Price<br>(OMR)</th>
      <th style="width:78px">Amount<br>(OMR)</th>
      <th style="width:70px">VAT 5%<br>(OMR)</th>
      <th style="width:82px">Total<br>(OMR)</th>
      @else
      <th style="width:110px">Amount (OMR)</th>
      @endif
    </tr>
  </thead>
  <tbody>
    @forelse($printLines as $i => $line)
    <tr class="{{ $i % 2 ? 'alt' : '' }}">
      <td class="no">{{ $i + 1 }}</td>
      <td class="l">{{ $line['desc'] }}</td>
      @if($isTax)
      <td>{{ number_format($line['qty'], 0) }}</td>
      <td>{{ number_format($line['unit_price'], 3) }}</td>
      <td>{{ number_format($line['amount'], 3) }}</td>
      <td>{{ number_format($line['vat'], 3) }}</td>
      <td>{{ number_format($line['total'], 3) }}</td>
      @else
      <td>{{ number_format($line['amount'], 3) }}</td>
      @endif
    </tr>
    @empty
    <tr><td class="empty" colspan="{{ $isTax ? 7 : 3 }}">No charges for this period.</td></tr>
    @endforelse
  </tbody>
</table>

{{-- Amount in words + totals --}}
<table class="sum">
  <tr>
    <td style="width:56%; padding-right:16px; vertical-align:bottom">
      <div class="words"><span class="k">Amount in words</span><span class="v">{{ $amountInWords }}</span></div>
    </td>
    <td>
      <table class="totals">
        @if($isTax)
        <tr><td class="k">Subtotal</td><td class="v">{{ number_format($totalAmount, 3) }}</td></tr>
        <tr><td class="k">VAT 5%</td><td class="v">{{ number_format($totalVat, 3) }}</td></tr>
        @endif
        <tr class="due"><td>Total Due (OMR)</td><td style="text-align:right">{{ number_format($totalDue, 3) }}</td></tr>
      </table>
    </td>
  </tr>
</table>

{{-- Signatures --}}
<table class="sign">
  <tr>
    <td><div class="line">Received by (name, signature &amp; date)</div></td>
    <td class="right"><div class="line"><span class="co">For AL HABIB &amp; CO. LLC</span><br>Authorised signatory</div></td>
  </tr>
</table>

</body>
</html>
