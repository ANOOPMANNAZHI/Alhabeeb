{{-- Legal Cases Summary export: PDF ($pdf true) wraps the tables in a page; Excel ($pdf false) gets the tables only. --}}
@if($pdf)
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Legal Cases Summary</title>
<style>@page { size: A4 landscape; margin: 22px 18px 28px 18px; } body { font-family: "DejaVu Sans", sans-serif; color: #1f2933; }</style>
</head><body>
@endif
<table style="border-collapse:collapse;width:100%;">
  <tr><td style="font-size:18px;font-weight:bold;color:#1F3864;">Legal Cases Summary</td></tr>
  <tr><td style="font-size:10px;color:#8A94A6;">Generated {{ $generatedAt }} by {{ $user }}</td></tr>
</table>
@include('masters::Legal.reports.cases_tables')
@if($pdf)
</body></html>
@endif
