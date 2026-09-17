<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='UTF-8'>
<title>Termination Dues Statement - {{ $contractNo }}</title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; }
  table#identity { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
  table#identity td { text-align: center; padding: 0; }
  #identity .logo { width: 240px; height: 64px; margin-bottom: 6px; }
  #identity .company { font-size: 10px; line-height: 1.5; color: #333; }
  .doc-title { text-align: center; font-size: 18px; font-weight: bold; margin: 10px 0 4px; }
  .doc-sub { text-align: center; font-size: 11px; color: #444; margin-bottom: 14px; }
  table.header-fields { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  table.header-fields td { vertical-align: top; padding: 4px 6px; font-size: 12px; border: 1px solid #000; }
  table.header-fields td.label { font-weight: bold; white-space: nowrap; width: 15%; background: #f2f2f2; }
  table.header-fields td.value { width: 35%; }
  table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
  table.items th, table.items td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; }
  table.items th { text-align: center; font-weight: bold; background: #f2f2f2; }
  table.items td.num { text-align: right; white-space: nowrap; }
  table.items tr.total-row td { font-weight: bold; background: #f7f7f7; }
  table.items td.credit { color: #555; font-style: italic; }
  table.balance-box { width: 100%; border-collapse: collapse; margin-top: 14px; }
  table.balance-box td { border: 2px solid #000; padding: 8px 12px; font-weight: bold; }
  table.balance-box td.lbl { font-size: 13px; }
  table.balance-box td.amt { font-size: 15px; text-align: right; }
  .note { margin-top: 10px; font-size: 10px; color: #444; }
  .signature-row { margin-top: 50px; overflow: hidden; }
  .signature-row .received { float: left; }
  .signature-row .for-company { float: right; font-weight: bold; color: #1F4E79; }
</style>
</head>
<body>

<table id="identity"><tr><td>
  <img src="{{ public_path('img/logo-print.png') }}" alt="Al Habib &amp; Co. L.L.C" class="logo"><br>
  <span class="company">Al Habib &amp; Co. L.L.C · P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman · Tel: +968 247 00247 · Fax: +968 247 03 666<br>C.R. No: 1131575 · Finance code: 10317401</span>
</td></tr></table>

<div class="doc-title">Termination Dues Statement</div>
<div class="doc-sub">As on {{ $printedAt }} · Status: {{ ucfirst(str_replace('_', ' ', $status)) }}</div>

<table class="header-fields">
  <tr>
    <td class="label">Tenant</td><td class="value">{{ $tenantName }}</td>
    <td class="label">Mobile</td><td class="value">{{ $tenantMobile ?: '-' }}</td>
  </tr>
  <tr>
    <td class="label">Building</td><td class="value">{{ $buildingName }}{{ $location ? ' · ' . $location : '' }}</td>
    <td class="label">Unit</td><td class="value">{{ $unitNo }}</td>
  </tr>
  <tr>
    <td class="label">Contract No</td><td class="value">{{ $contractNo }}</td>
    <td class="label">Terminated on</td><td class="value">{{ $terminationDate ?: '-' }}</td>
  </tr>
</table>

<table class="items">
  <thead>
    <tr>
      <th style="width:32px">#</th>
      <th>Particulars</th>
      <th style="width:90px">Owed</th>
      <th style="width:90px">Deposit deduction</th>
      <th style="width:90px">Receipts</th>
      <th style="width:90px">Waived</th>
      <th style="width:90px">Balance</th>
    </tr>
  </thead>
  <tbody>
  @foreach($lines as $i => $l)
    <tr>
      <td class="num">{{ $i + 1 }}</td>
      <td class="{{ $l['credit'] ? 'credit' : '' }}">{{ $l['description'] }}</td>
      <td class="num">{{ numberFormat($l['owed']) }}</td>
      <td class="num">{{ $l['credit'] ? '' : numberFormat($l['deposit']) }}</td>
      <td class="num">{{ $l['credit'] ? '' : numberFormat($l['receipts']) }}</td>
      <td class="num">{{ $l['credit'] ? '' : numberFormat($l['waived']) }}</td>
      <td class="num">{{ $l['credit'] ? '' : numberFormat($l['balance']) }}</td>
    </tr>
  @endforeach
    <tr class="total-row">
      <td colspan="2">Total (OMR)</td>
      <td class="num">{{ numberFormat($total['owed']) }}</td>
      <td class="num">{{ numberFormat($total['deposit']) }}</td>
      <td class="num">{{ numberFormat($total['receipts']) }}</td>
      <td class="num">{{ numberFormat($total['waived']) }}</td>
      <td class="num">{{ numberFormat($total['balance']) }}</td>
    </tr>
  </tbody>
</table>

<table class="balance-box"><tr><td class="lbl">Balance outstanding</td><td class="amt">OMR {{ numberFormat($total['balance']) }}</td></tr></table>
<div class="note">Owed = charges recorded at termination. Deposit deduction = amounts withheld from the security deposit refund. Receipts = payments received and approved. Waived = amounts written off with approval.</div>

<div class="signature-row">
  <div class="received">Tenant's acknowledgement: ____________________</div>
  <div class="for-company">For Al Habib &amp; Co. L.L.C</div>
</div>

</body>
</html>
