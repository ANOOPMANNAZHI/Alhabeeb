<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='UTF-8'>
<title>Tenant Onboarding - {{ optional($onboarding->tenantContract)->tenant_contract_no }}</title>
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
  table.items th, table.items td { border: 1px solid #000; padding: 5px 8px; font-size: 11px; vertical-align: top; }
  table.items th { text-align: left; font-weight: bold; background: #f2f2f2; }
  table.items tr.cat td { background: #f7f7f7; font-weight: bold; }
  table.items td.st { text-align: center; white-space: nowrap; width: 12%; }
  table.items img { max-width: 110px; max-height: 80px; margin: 2px; }
  .notes { border: 1px solid #000; padding: 8px; margin-top: 6px; white-space: pre-line; }
  h3.section { font-size: 13px; margin: 14px 0 4px; border-bottom: 1px solid #000; padding-bottom: 2px; }
  .signature-row { margin-top: 30px; overflow: hidden; }
  .signature-row .tenant { float: left; width: 45%; }
  .signature-row .tenant img { max-height: 90px; }
  .signature-row .for-company { float: right; width: 45%; text-align: right; font-weight: bold; color: #1F4E79; }
  .muted { color: #666; font-size: 10px; }
</style>
</head>
<body>
@php
  $c = $onboarding->tenantContract;
  $tenant = optional($c)->tenant;
  $storagePath = fn ($p) => $p ? storage_path('app/' . ltrim($p, '/')) : null;
  $groups = collect($detail['items'])->groupBy('group');
@endphp

<table id="identity"><tr><td>
  <img src="{{ public_path('img/logo-print.png') }}" alt="Al Habib &amp; Co. L.L.C" class="logo"><br>
  <span class="company">Al Habib &amp; Co. L.L.C &middot; P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman &middot; Tel: +968 247 00247 &middot; Fax: +968 247 03 666<br>C.R. No: 1131575 &middot; Finance code: 10317401</span>
</td></tr></table>

<div class="doc-title">Tenant Onboarding Checklist</div>
<div class="doc-sub">Contract {{ optional($c)->tenant_contract_no }} &middot; {{ ucfirst($onboarding->status) }} &middot; Printed {{ $printedAt }}</div>

<table class="header-fields">
  <tr>
    <td class="label">Tenant</td><td class="value">{{ optional($tenant)->tenant_name }}</td>
    <td class="label">Mobile</td><td class="value">{{ optional($tenant)->tenant_contact_no ?: (optional($tenant)->tenant_residence_tel ?: '-') }}</td>
  </tr>
  <tr>
    <td class="label">Building</td><td class="value">{{ optional(optional($c)->building)->building_name }}</td>
    <td class="label">Unit</td><td class="value">{{ optional(optional($c)->unit)->unit_no }}</td>
  </tr>
  <tr>
    <td class="label">Contract start</td><td class="value">{{ optional(optional($c)->tenant_contract_start_date)->format('d/m/Y') ?: '-' }}</td>
    <td class="label">Completed on</td><td class="value">{{ optional($onboarding->completed_at)->format('d/m/Y H:i') ?: '-' }}</td>
  </tr>
  <tr>
    <td class="label">Handled by</td><td class="value">{{ optional(optional($onboarding->assignedUser)->employee)->employee_name ?: optional($onboarding->assignedUser)->username }}</td>
    <td class="label">Progress</td><td class="value">{{ $detail['progress']['done'] }} / {{ $detail['progress']['total'] }}</td>
  </tr>
</table>

<table class="items">
  <thead><tr><th>Item</th><th class="st">Status</th><th style="width:22%">Value</th><th style="width:22%">Remark / photos</th></tr></thead>
  <tbody>
  @foreach($groups as $group => $rows)
    <tr class="cat"><td colspan="4">{{ $group }}</td></tr>
    @foreach($rows as $r)
      @php $e = $r['entry']; $shown = collect($r['photos'])->filter(fn ($p) => $storagePath($p['thumb'] ?: $p['path']) && file_exists($storagePath($p['thumb'] ?: $p['path']))); @endphp
      <tr>
        <td>{{ $r['title'] }}@if($r['required']) <span class="muted">(required)</span>@endif</td>
        <td class="st">@if($e && $e['not_applicable']) N/A @elseif($e && $e['done']) Done @else - @endif</td>
        <td>{{ $e['value'] ?? '' }}</td>
        <td>
          {{ $e['remark'] ?? '' }}
          @foreach($shown as $p)<img src="{{ $storagePath($p['thumb'] ?: $p['path']) }}" alt="">@endforeach
        </td>
      </tr>
    @endforeach
  @endforeach
  </tbody>
</table>

@if($onboarding->tenant_remark)
<h3 class="section">Tenant remark</h3>
<div class="notes">{{ $onboarding->tenant_remark }}</div>
@endif

<div class="signature-row">
  <div class="tenant">
    <strong>Tenant signature</strong><br>
    @if($onboarding->tenant_signature && file_exists($storagePath($onboarding->tenant_signature)))
      <img src="{{ $storagePath($onboarding->tenant_signature) }}" alt="Tenant signature">
    @else
      <span class="muted">Not signed</span>
    @endif
  </div>
  <div class="for-company">For Al Habib &amp; Co. L.L.C</div>
</div>

</body>
</html>
