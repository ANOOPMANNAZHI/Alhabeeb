<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='UTF-8'>
<title>Service Report - {{ $report->service_report_no }}</title>
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
  table.items td.num { text-align: right; white-space: nowrap; }
  table.photos td { border: none; padding: 4px; text-align: center; width: 25%; }
  table.photos img { max-width: 160px; max-height: 120px; }
  table.photos .cap { font-size: 9px; color: #555; }
  .signature-row { margin-top: 30px; overflow: hidden; }
  .signature-row .tenant { float: left; width: 45%; }
  .signature-row .tenant img { max-height: 90px; }
  .signature-row .for-company { float: right; width: 45%; text-align: right; font-weight: bold; color: #1F4E79; }
  .muted { color: #666; font-size: 10px; }
</style>
</head>
<body>
@php
  $enquiry = $ticket->complaintEnquiry;
  $storagePath = fn ($p) => $p ? storage_path('app/' . ltrim($p, '/')) : null;
@endphp

<table id="identity"><tr><td>
  <img src="{{ public_path('img/logo-print.png') }}" alt="Al Habib &amp; Co. L.L.C" class="logo"><br>
  <span class="company">Al Habib &amp; Co. L.L.C · P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman · Tel: +968 247 00247 · Fax: +968 247 03 666<br>C.R. No: 1131575 · Finance code: 10317401</span>
</td></tr></table>

<div class="doc-title">Maintenance Service Report</div>
<div class="doc-sub">{{ $report->service_report_no }} · Status: {{ $report->ServiceReportStatusName }} · Printed {{ $printedAt }}</div>

<table class="header-fields">
  <tr>
    <td class="label">Complaint No</td><td class="value">{{ $enquiry->complaint_no }}</td>
    <td class="label">Complaint Date</td><td class="value">{{ optional($enquiry->complaint_date)->format('d/m/Y H:i') }}</td>
  </tr>
  <tr>
    <td class="label">Ticket No</td><td class="value">{{ $ticket->complaint_ticket_no }}</td>
    <td class="label">Work</td><td class="value">{{ optional($ticket->work)->works_desc }}</td>
  </tr>
  <tr>
    <td class="label">Complainant</td><td class="value">{{ $enquiry->complainer_name }}</td>
    <td class="label">Mobile</td><td class="value">{{ $enquiry->complaint_mob_no ?: '-' }}</td>
  </tr>
  <tr>
    <td class="label">Building</td><td class="value">{{ optional($enquiry->building)->building_name }}{{ optional($enquiry->location)->locations_name ? ' · ' . $enquiry->location->locations_name : '' }}</td>
    <td class="label">Unit</td><td class="value">{{ optional($enquiry->unit)->unit_no ?: '-' }}</td>
  </tr>
  <tr>
    <td class="label">Attended by</td><td class="value">{{ optional(optional($ticket->subAssignedPerson)->employee)->employee_name ?: optional($ticket->subAssignedPerson)->username }}</td>
    <td class="label">Description</td><td class="value">{{ $ticket->checklist_desc }}</td>
  </tr>
</table>

<h3 class="section">Notes</h3>
@if($notes->isEmpty())
  <div class="muted">No notes recorded.</div>
@else
<table class="items">
  <thead><tr><th style="width:22%">When</th><th style="width:16%">Stage</th><th>Note</th></tr></thead>
  <tbody>
  @foreach($notes as $n)
    <tr>
      <td>{{ optional($n->created_at)->format('d/m/Y H:i') }}</td>
      <td>{{ $n->ServiceReportStageName }}</td>
      <td>{{ $n->desc }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
@endif

@if($items->isNotEmpty())
<h3 class="section">Materials used</h3>
<table class="items">
  <thead><tr><th>Item</th><th style="width:15%" class="num">Quantity</th></tr></thead>
  <tbody>
  @foreach($items as $i)
    <tr><td>{{ optional($i->inventory)->inventories_name ?: ('#' . $i->inventory_id) }}</td><td class="num">{{ rtrim(rtrim(number_format((float) $i->quantity, 2, '.', ''), '0'), '.') }}</td></tr>
  @endforeach
  </tbody>
</table>
@endif

@php $shown = $images->filter(fn ($img) => $storagePath($img->image_path_thumbnail) && file_exists($storagePath($img->image_path_thumbnail))); @endphp
@if($shown->isNotEmpty())
<h3 class="section">Photos</h3>
<table class="items photos">
  @foreach($shown->chunk(4) as $row)
  <tr>
    @foreach($row as $img)
      <td>
        <img src="{{ $storagePath($img->image_path_thumbnail) }}" alt=""><br>
        <span class="cap">{{ ucfirst(\Modules\Maintenance\Services\TicketAttendance::photoStageName((int) $img->stage)) }} · {{ optional($img->created_at)->format('d/m/Y H:i') }}</span>
      </td>
    @endforeach
  </tr>
  @endforeach
</table>
@endif

<div class="signature-row">
  <div class="tenant">
    <strong>Tenant signature</strong><br>
    @if($report->tenant_signature && file_exists($storagePath($report->tenant_signature)))
      <img src="{{ $storagePath($report->tenant_signature) }}" alt="Tenant signature">
    @else
      <span class="muted">Not signed</span>
    @endif
  </div>
  <div class="for-company">For Al Habib &amp; Co. L.L.C</div>
</div>

</body>
</html>
