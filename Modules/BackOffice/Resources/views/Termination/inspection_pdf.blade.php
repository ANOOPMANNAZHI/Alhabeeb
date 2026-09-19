<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='UTF-8'>
<title>Vacating Unit Inspection - {{ $tenantContract->tenant_contract_no }}</title>
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
  h3.section { font-size: 13px; margin: 14px 0 4px; border-bottom: 1px solid #000; padding-bottom: 2px; }
  table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
  table.items th, table.items td { border: 1px solid #000; padding: 5px 8px; font-size: 11px; vertical-align: top; }
  table.items th { text-align: left; font-weight: bold; background: #f2f2f2; }
  table.items td.num, table.items th.num { text-align: right; white-space: nowrap; }
  table.items tr.cat td { background: #f7f7f7; font-weight: bold; }
  table.items tr.total-row td { font-weight: bold; background: #f7f7f7; }
  table.balance-box { width: 100%; border-collapse: collapse; margin-top: 14px; }
  table.balance-box td { border: 2px solid #000; padding: 8px 12px; font-weight: bold; }
  table.balance-box td.lbl { font-size: 13px; }
  table.balance-box td.amt { font-size: 15px; text-align: right; }
  table.photos td { border: none; padding: 4px; text-align: center; width: 25%; }
  table.photos img { max-width: 160px; max-height: 120px; }
  table.photos .cap { font-size: 9px; color: #555; }
  .notes { border: 1px solid #000; padding: 8px; margin-top: 6px; white-space: pre-line; }
  .signature-row { margin-top: 30px; overflow: hidden; }
  .signature-row .tenant { float: left; width: 45%; }
  .signature-row .tenant img { max-height: 90px; }
  .signature-row .for-company { float: right; width: 45%; text-align: right; font-weight: bold; color: #1F4E79; }
  .muted { color: #666; font-size: 10px; }
</style>
</head>
<body>
@php
  $n3 = fn ($v) => number_format((float) $v, 3);
  $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
  $storagePath = fn ($p) => $p ? storage_path('app/' . ltrim($p, '/')) : null;
  $tenant = $tenantContract->tenant;
  $grouped = $lines->whereNotNull('work_id')->groupBy('work_id');
  $others = $lines->whereNull('work_id');
  $inspector = \App\User::find($termination->assigned_to);
@endphp

<table id="identity"><tr><td>
  <img src="{{ public_path('img/logo-print.png') }}" alt="Al Habib &amp; Co. L.L.C" class="logo"><br>
  <span class="company">Al Habib &amp; Co. L.L.C &middot; P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman &middot; Tel: +968 247 00247 &middot; Fax: +968 247 03 666<br>C.R. No: 1131575 &middot; Finance code: 10317401</span>
</td></tr></table>

<div class="doc-title">Vacating Unit Inspection Report</div>
<div class="doc-sub">Contract {{ $tenantContract->tenant_contract_no }} &middot; Printed {{ $printedAt }}</div>

<table class="header-fields">
  <tr>
    <td class="label">Tenant</td><td class="value">{{ optional($tenant)->tenant_name }}</td>
    <td class="label">Mobile</td><td class="value">{{ optional($tenant)->tenant_contact_no ?: (optional($tenant)->tenant_residence_tel ?: '-') }}</td>
  </tr>
  <tr>
    <td class="label">Building</td><td class="value">{{ optional($tenantContract->building)->building_name }}{{ optional(optional($tenantContract->building)->location)->locations_name ? ' - ' . $tenantContract->building->location->locations_name : '' }}</td>
    <td class="label">Unit</td><td class="value">{{ optional($tenantContract->unit)->unit_no }}</td>
  </tr>
  <tr>
    <td class="label">Termination date</td><td class="value">{{ optional($termination->termination_date)->format('d/m/Y') ?: '-' }}</td>
    <td class="label">Taken over on</td><td class="value">{{ optional($termination->termination_takenover_date)->format('d/m/Y') ?: '-' }}</td>
  </tr>
  <tr>
    <td class="label">Main key</td><td class="value">{{ (int) $termination->termination_main_key_status === 1 ? 'Received' : 'Not received' }}</td>
    <td class="label">Inspected by</td><td class="value">{{ optional(optional($inspector)->employee)->employee_name ?: optional($inspector)->username }}</td>
  </tr>
</table>

<h3 class="section">Utilities</h3>
<table class="items">
  <thead><tr><th>Meter</th><th>Account no</th><th>Closing reading</th><th class="num">Amount (OMR)</th></tr></thead>
  <tbody>
    <tr><td>Electricity</td><td>{{ $termination->termination_electricity_acc_no ?: '-' }}</td><td>{{ $termination->termination_electricity_close_reading ?: '-' }}</td><td class="num">{{ $n3($termination->termination_electricity_amount) }}</td></tr>
    <tr><td>Water</td><td>{{ $termination->termination_water_acc_no ?: '-' }}</td><td>{{ $termination->termination_water_close_reading ?: '-' }}</td><td class="num">{{ $n3($termination->termination_water_amount) }}</td></tr>
    <tr class="total-row"><td colspan="3">Electricity &amp; water total</td><td class="num">{{ $n3($termination->termination_total_elec_water_amount) }}</td></tr>
  </tbody>
</table>

<h3 class="section">Damages / works</h3>
@if($lines->isEmpty())
  <div class="muted">No works recorded.</div>
@else
<table class="items">
  <thead><tr><th>Item</th><th class="num">Qty</th><th class="num">Amount (OMR)</th></tr></thead>
  <tbody>
  @foreach($grouped as $workId => $rows)
    <tr class="cat"><td colspan="3">{{ optional($rows->first()->work)->works_desc ?: ('Work #' . $workId) }}</td></tr>
    @foreach($rows as $l)
      <tr><td>{{ optional($l->subWorks)->sub_work }}</td><td class="num">{{ $qty($l->termination_quantity) }}</td><td class="num">{{ $n3($l->termination_amount) }}</td></tr>
    @endforeach
  @endforeach
  @if($others->isNotEmpty())
    <tr class="cat"><td colspan="3">Other</td></tr>
    @foreach($others as $l)
      <tr><td>{{ $l->termination_other_work }}</td><td class="num">{{ $qty($l->termination_quantity) }}</td><td class="num">{{ $n3($l->termination_amount) }}</td></tr>
    @endforeach
  @endif
    <tr class="total-row"><td colspan="2">Works total</td><td class="num">{{ $n3($termination->termination_total_amount) }}</td></tr>
  </tbody>
</table>
@endif

<table class="balance-box">
  <tr><td class="lbl">Works {{ $n3($termination->termination_total_amount) }} + Utilities {{ $n3($termination->termination_total_elec_water_amount) }} - Maintenance due {{ $n3($termination->termination_discount_maintenance_due) }}</td><td class="amt">Net {{ $n3($termination->termination_net_amount) }} OMR</td></tr>
</table>

@if($termination->termination_notes)
<h3 class="section">Notes</h3>
<div class="notes">{{ $termination->termination_notes }}</div>
@endif
@if($termination->termination_remark)
<h3 class="section">Tenant remark</h3>
<div class="notes">{{ $termination->termination_remark }}</div>
@endif

@php $shown = $documents->filter(fn ($d) => $storagePath($d->image_path_thumbnail ?: $d->termination_doc) && file_exists($storagePath($d->image_path_thumbnail ?: $d->termination_doc))); @endphp
@if($shown->isNotEmpty())
<h3 class="section">Photos</h3>
<table class="items photos">
  @foreach($shown->chunk(4) as $row)
  <tr>
    @foreach($row as $d)
      <td><img src="{{ $storagePath($d->image_path_thumbnail ?: $d->termination_doc) }}" alt=""><br><span class="cap">{{ $d->termination_doc_type }} &middot; {{ optional($d->created_at)->format('d/m/Y H:i') }}</span></td>
    @endforeach
  </tr>
  @endforeach
</table>
@endif

<div class="signature-row">
  <div class="tenant">
    <strong>Tenant signature</strong><br>
    @if($termination->termination_tenant_signature && file_exists($storagePath($termination->termination_tenant_signature)))
      <img src="{{ $storagePath($termination->termination_tenant_signature) }}" alt="Tenant signature">
    @else
      <span class="muted">Not signed</span>
    @endif
  </div>
  <div class="for-company">For Al Habib &amp; Co. L.L.C</div>
</div>

</body>
</html>
