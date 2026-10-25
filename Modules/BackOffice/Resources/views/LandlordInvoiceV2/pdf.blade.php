@php
  // Landlord Invoice v2 print, classic bordered corporate format (black & grey, ruled grid).
  // dompdf 0.8: tables only, no flex/grid.
  $isTax  = $invoice->invoice_type == 'tax_invoice';
  $title  = $isTax ? 'Tax Invoice' : 'Other Deductions Invoice';
  $period = date('F', mktime(0, 0, 0, $invoice->period_month, 1)) . ' ' . $invoice->period_year;
  // lines with no amount add nothing to the invoice; leave them off the print
  $printLines = array_values(array_filter($lines, function ($l) { return round($l['total'], 3) != 0; }));
  $cols   = 6;
  // keep the lines box a fixed height so short invoices still look like a full form
  $filler = max(0, 300 - count($printLines) * 26);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }} {{ $invoice->invoice_no }}</title>
<style>
  @page { margin: 30px 36px 42px 36px; }
  body { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; color: #000; line-height: 1.4; }
  table { border-collapse: collapse; width: 100%; }
  td, th { vertical-align: top; }

  .lh td { padding: 0; vertical-align: middle; }
  .lh .logo img { width: 180px; height: auto; }
  .lh .co { text-align: right; font-size: 9.5px; line-height: 1.5; }
  .lh .co .name { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; }
  .rule  { height: 2px; background: #000; margin-top: 8px; font-size: 0; line-height: 0; }
  .rule2 { height: 1px; background: #000; margin-top: 2px; font-size: 0; line-height: 0; }

  .title { margin-top: 12px; border: 1px solid #000; background: #e8e8e8; text-align: center; font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 3px; padding: 6px; }
  .void  { margin-top: 6px; border: 2px solid #000; text-align: center; font-weight: bold; font-size: 12px; letter-spacing: 2px; padding: 4px; }

  .info { margin-top: 8px; border: 1px solid #000; }
  .info td { padding: 5px 8px; border: 1px solid #000; font-size: 10px; }
  .info td.k { background: #f2f2f2; font-weight: bold; width: 17%; white-space: nowrap; }
  .info td.v { width: 33%; }

  .items { margin-top: 10px; border: 1px solid #000; }
  .items th { border: 1px solid #000; background: #e8e8e8; font-size: 9.5px; font-weight: bold; text-transform: uppercase; padding: 6px 6px; text-align: center; vertical-align: middle; }
  .items td { border-left: 1px solid #000; border-right: 1px solid #000; padding: 6px; font-size: 10.5px; text-align: right; }
  .items td.c { text-align: center; }
  .items td.l { text-align: left; }
  .items tr.fill td { padding: 0; }
  .items tr.tot td { border: 1px solid #000; font-weight: bold; padding: 6px; }
  .items tr.tot td.k { text-align: right; background: #f2f2f2; }
  .items tr.due td { font-size: 11.5px; background: #e8e8e8; }
  .items tr.words td { border: 1px solid #000; text-align: left; padding: 7px 8px; }

  .sign { margin-top: 46px; }
  .sign td { font-size: 10px; }
  .sign td.box { width: 215px; text-align: center; }
  .sign .cap { font-weight: bold; height: 52px; }
  .sign .line { border-top: 1px solid #000; padding-top: 4px; }

  .foot { position: fixed; bottom: -26px; left: 0; right: 0; border-top: 1px solid #999; padding-top: 3px; font-size: 8.5px; color: #555; }
  .foot td { font-size: 8.5px; color: #555; }
</style>
</head>
<body>

<div class="foot">
  <table><tr>
    <td>Al Habib &amp; Co. L.L.C &middot; C.R. No: 1131575 &middot; VATIN: OM110001282X</td>
    <td style="text-align:right">{{ $title }} {{ $invoice->invoice_no }}</td>
  </tr></table>
</div>

{{-- Letterhead --}}
<table class="lh">
  <tr>
    <td class="logo" style="width:45%"><img src="{{ public_path('img/logo-print.png') }}" alt="Al Habib"></td>
    <td class="co">
      <div class="name">Al Habib &amp; Co. L.L.C</div>
      P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman<br>
      Tel: +968 247 00247 &nbsp;|&nbsp; Fax: +968 247 03 666<br>
      C.R. No: 1131575 &nbsp;|&nbsp; Finance Code: 10317401 &nbsp;|&nbsp; VATIN: OM110001282X
    </td>
  </tr>
</table>
<div class="rule">&nbsp;</div><div class="rule2">&nbsp;</div>

<div class="title">{{ $title }}</div>
@if($invoice->status === 'voided')
<div class="void">VOID &mdash; THIS INVOICE HAS BEEN CANCELLED</div>
@endif

{{-- Customer / invoice details --}}
<table class="info">
  <tr>
    <td class="k">Customer Name</td><td class="v">{{ $invoice->vendor_name }}</td>
    <td class="k">Invoice No.</td><td class="v"><b>{{ $invoice->invoice_no }}</b></td>
  </tr>
  <tr>
    <td class="k">Address</td><td class="v">{{ $invoice->vendor_address ?: '-' }}</td>
    <td class="k">Invoice Date</td><td class="v">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d.m.Y') }}</td>
  </tr>
  <tr>
    <td class="k">Customer VATIN</td><td class="v">{{ $invoice->vatin_no ?: '-' }}</td>
    <td class="k">Period</td><td class="v">{{ $period }}</td>
  </tr>
  <tr>
    <td class="k">Building Name</td><td class="v">{{ $invoice->building_name }}</td>
    @if($invoice->status === 'posted')
    <td class="k">AX Batch</td><td class="v">{{ $invoice->ax_batch_id }}{{ $invoice->posted_at ? ' (' . \Carbon\Carbon::parse($invoice->posted_at)->format('d.m.Y') . ')' : '' }}</td>
    @else
    <td class="k">Currency</td><td class="v">Omani Rial (OMR)</td>
    @endif
  </tr>
</table>

{{-- Lines, totals and amount in words: same six columns on both invoice types (user, 08/10/2026) --}}
<table class="items">
  <thead>
    <tr>
      <th>Description</th>
      <th style="width:62px">Quantity<br>No's</th>
      <th style="width:80px">Unit Price<br>(OMR)</th>
      <th style="width:84px">Amount<br>(OMR)</th>
      <th style="width:78px">VAT 5%<br>(OMR)</th>
      <th style="width:88px">Total<br>(OMR)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($printLines as $line)
    <tr>
      <td class="l">{{ $line['desc'] }}</td>
      <td class="c">{{ number_format($line['qty'], 3) }}</td>
      <td>{{ number_format($line['unit_price'], 3) }}</td>
      <td>{{ number_format($line['amount'], 3) }}</td>
      <td>{{ number_format($line['vat'], 3) }}</td>
      <td>{{ number_format($line['total'], 3) }}</td>
    </tr>
    @endforeach
    @if($filler > 0)
    <tr class="fill">@for($c = 0; $c < $cols; $c++)<td style="height:{{ $filler }}px"></td>@endfor</tr>
    @endif

    <tr class="tot due">
      <td class="k" colspan="3">Total Amount Due (OMR)</td>
      <td>{{ number_format($totalAmount, 3) }}</td>
      <td>{{ number_format($totalVat, 3) }}</td>
      <td>{{ number_format($totalDue, 3) }}</td>
    </tr>
    <tr class="words">
      <td colspan="{{ $cols }}"><b>Amount in words:</b> {{ $amountInWords }}</td>
    </tr>
  </tbody>
</table>

{{-- Signatures --}}
<table class="sign">
  <tr>
    <td class="box"><div class="cap">Received by</div><div class="line">Name, Signature &amp; Date</div></td>
    <td></td>
    <td class="box"><div class="cap">For AL HABIB &amp; CO. L.L.C</div><div class="line">Authorised Signatory</div></td>
  </tr>
</table>

</body>
</html>
