<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='UTF-8'>
<title>{{ $invoice->invoice_type == 'tax_invoice' ? 'Tax Invoice' : 'Other Deductions Invoice' }}</title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; }
  #identity { overflow: hidden; margin-bottom: 10px; }
  #logo { float: left; width: 260px; }
  #logo img { height: 70px; width: 250px; }
  #address { float: left; width: 500px; margin-bottom: 10px; font-size: 11px; line-height: 1.5; }
  .invoice-title { text-align: center; font-size: 18px; font-weight: bold; margin: 10px 0 16px; }
  table.header-fields { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  table.header-fields td { vertical-align: top; padding: 2px 4px; font-size: 12px; }
  table.header-fields .label { font-weight: bold; white-space: nowrap; }
  table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
  table.items th, table.items td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; }
  table.items th { text-align: center; font-weight: bold; }
  table.items td.desc { text-align: left; height: 60px; vertical-align: top; }
  table.items td.num { text-align: right; }
  table.items tr.total-row td { font-weight: bold; }
  .words-row td { font-weight: bold; border: 1px solid #000; border-top: none; padding: 6px 8px; font-size: 11px; }
  .signature-row { margin-top: 60px; overflow: hidden; }
  .signature-row .received { float: left; }
  .signature-row .for-company { float: right; font-weight: bold; color: #1F4E79; }
</style>
</head>
<body>

<div id="identity">
  <div id="logo">
    <img src="{{public_path('img/logo-print.png')}}" alt="logo">
  </div>
  <div id="address">
    <b style="text-align:right">الحبيب وشركاه ش . م .م</b></br>
    <b>Al Habib & Co. L.L.C</b></br>
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

<div class="invoice-title">{{ $invoice->invoice_type == 'tax_invoice' ? 'Tax Invoice' : 'Other Deductions Invoice' }}</div>

@if($invoice->status === 'voided')
<div style="text-align:center; color:#c00; font-weight:bold; font-size:16px; border:2px solid #c00; padding:6px; margin-bottom:12px;">
  THIS INVOICE HAS BEEN VOIDED
</div>
@endif

<table class="header-fields">
  <tr>
    <td class="label">CUSTOMER NAME:</td>
    <td>{{ $invoice->vendor_name }}</td>
    <td class="label">INVOICE NO:</td>
    <td>{{ $invoice->invoice_no }}</td>
  </tr>
  <tr>
    <td class="label">BUILDING NAME</td>
    <td>{{ $invoice->building_name }}</td>
    <td class="label">INVOICE DATE:</td>
    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d.m.Y') }}</td>
  </tr>
  <tr>
    <td class="label">ADDRESS:</td>
    <td>{{ $invoice->vendor_address }}</td>
    <td class="label">PERIOD:</td>
    <td>{{ date('F', mktime(0,0,0,$invoice->period_month,1)) }} {{ $invoice->period_year }}</td>
  </tr>
  <tr>
    <td class="label">VATIN NO.</td>
    <td>{{ $invoice->vatin_no }}</td>
    <td class="label">VATIN NO.</td>
    <td>OM110001282X</td>
  </tr>
</table>

<table class="items">
  <thead>
    <tr>
      <th style="width:40%">DESCRIPTION</th>
      <th>Quantity<br>No's</th>
      <th>Unit Price<br>(OMR)</th>
      <th>Amount<br>(OMR)</th>
      <th>VAT 5%<br>(OMR)</th>
      <th>Total<br>(OMR)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($lines as $line)
    <tr>
      <td class="desc">{{ $line['desc'] }}</td>
      <td class="num">{{ number_format($line['qty'], 3) }}</td>
      <td class="num">{{ number_format($line['unit_price'], 3) }}</td>
      <td class="num">{{ number_format($line['amount'], 3) }}</td>
      <td class="num">{{ number_format($line['vat'], 3) }}</td>
      <td class="num">{{ number_format($line['total'], 3) }}</td>
    </tr>
    @endforeach
    <tr class="total-row">
      <td colspan="3">Total</td>
      <td class="num">{{ number_format($totalAmount, 3) }}</td>
      <td class="num">{{ number_format($totalVat, 3) }}</td>
      <td class="num">{{ number_format($totalDue, 3) }}</td>
    </tr>
  </tbody>
</table>
<table class="items" style="margin-top:0">
  <tr class="words-row">
    <td colspan="6">{{ $amountInWords }}</td>
  </tr>
</table>

<div class="signature-row">
  <div class="received">Received By:</div>
  <div class="for-company">For AL HABIB & CO. LLC</div>
</div>

</body>
</html>
