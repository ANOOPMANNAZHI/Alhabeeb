{{-- Legal Settlements table: screen, PDF and Excel. Inline styles only (Excel reads no <style>). --}}
@php
    $fs = !empty($pdf) ? 'font-size:7.5px;' : '';
    $pad = !empty($pdf) ? 'padding:2px 3px;' : '';
    $LS = \Modules\Masters\Services\LegalSettlement::class;
    $th = 'background-color:#1F3864;color:#FFFFFF;font-weight:bold;font-size:11px;border:1px solid #1F3864;padding:4px 6px;text-align:left;' . $fs . $pad;
    $thr = $th . 'text-align:right;';
    $td = 'font-size:11px;border:1px solid #CFD9E6;padding:3px 6px;' . $fs . $pad;
    $tdr = $td . 'text-align:right;';
    $tot = 'font-size:11px;border:1px solid #8FAADC;padding:3px 6px;background-color:#EAF0FA;font-weight:bold;' . $fs . $pad;
    $totr = $tot . 'text-align:right;';
    $red = 'color:#B42318;font-weight:bold;';
    $d = function ($v) { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : ''; };
    // on screen (no $pdf) the design-system table classes style the cells; exports keep the inline styles
    $screen = !isset($pdf);
    if ($screen) {
        $th = ''; $thr = 'text-align:right;'; $td = ''; $tdr = 'text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap;';
        $tot = 'font-weight:600;'; $totr = $tot . $tdr;
        if (isset($cap)) { $cap = 'font-size:15px;font-weight:600;color:#0F172A;padding:16px 0 8px;background:transparent;border:0;'; }
        if (isset($red)) { $red = 'color:#B91C1C;font-weight:600;'; }
    }
@endphp
{{-- dompdf runs out of memory on very long tables: the PDF gets page-sized tables --}}
@php $chunks = !empty($pdf) ? $rows->chunk(22)->values() : collect([$rows]); if (!$chunks->count()) { $chunks = collect([collect()]); } @endphp
@foreach($chunks as $ci => $chunk)
<table class="{{ $screen ? 'lg-table' : '' }}" style="border-collapse:collapse;width:100%;@if(!empty($pdf) && $ci > 0) page-break-before:always;@endif">
  <tr>
    <th style="{{ $th }}">Case</th><th style="{{ $th }}">Agreement</th><th style="{{ $th }}">Tenant</th><th style="{{ $th }}">Building / unit</th>
    <th style="{{ $th }}">Lawyer</th><th style="{{ $th }}">Type</th><th style="{{ $th }}">Date</th><th style="{{ $th }}">Status</th>
    <th style="{{ $thr }}">Amount</th><th style="{{ $thr }}">Waiver</th><th style="{{ $thr }}">Fees</th><th style="{{ $thr }}">Total</th>
    <th style="{{ $thr }}">Collected</th><th style="{{ $thr }}">Cheques held</th><th style="{{ $thr }}">Balance</th>
    <th style="{{ $thr }}">Overdue</th><th style="{{ $thr }}">Oldest overdue (days)</th><th style="{{ $th }}">Next due</th>
  </tr>
  @forelse($chunk as $r)
  <tr>
    <td style="{{ $td }}">@if(!isset($pdf))<a href="{{ route('legal.case', $r->legal_id) }}">#{{ $r->legal_id }}</a>@else #{{ $r->legal_id }} @endif</td>
    <td style="{{ $td }}">{{ $r->tenant_contract_no }}</td>
    <td style="{{ $td }}">{{ $r->tenant_name }}</td>
    <td style="{{ $td }}">{{ $r->building_name }} / {{ $r->unit_code }}</td>
    <td style="{{ $td }}">{{ $r->lawyer_name }}</td>
    <td style="{{ $td }}">{{ $LS::TYPES[$r->settlement_type] ?? '' }}</td>
    <td style="{{ $td }}">{{ $d($r->agreed_date) }}</td>
    <td style="{{ $td }}">{{ $r->status_label }}@if($r->closed_at) (case closed)@endif</td>
    <td style="{{ $tdr }}">{{ numberFormat($r->amount) }}</td>
    <td style="{{ $tdr }}">{{ (float) $r->waiver_amount > 0 ? numberFormat($r->waiver_amount) . ($r->waiver_live ? '' : ' (cancelled)') : '' }}</td>
    <td style="{{ $tdr }}">{{ (float) $r->fees_recovered > 0 ? numberFormat($r->fees_recovered) : '' }}</td>
    <td style="{{ $tdr }}">{{ numberFormat($r->settled_total) }}</td>
    <td style="{{ $tdr }}">{{ numberFormat($r->collected) }}</td>
    <td style="{{ $tdr }}">{{ $r->held > 0 ? numberFormat($r->held) : '' }}</td>
    <td style="{{ $tdr }}">{{ numberFormat($r->balance) }}</td>
    <td style="{{ $tdr }}{{ $r->overdue > 0 ? $red : '' }}">{{ $r->overdue > 0 ? numberFormat($r->overdue) : '' }}</td>
    <td style="{{ $tdr }}{{ $r->oldest_overdue_days > 0 ? $red : '' }}">{{ $r->oldest_overdue_days ?: '' }}</td>
    <td style="{{ $td }}">{{ $d($r->next_due) }}</td>
  </tr>
  @empty
  <tr><td colspan="18" style="{{ $td }}">No settlements match the filters.</td></tr>
  @endforelse
@if($ci === $chunks->count() - 1)
  <tr>
    <td colspan="8" style="{{ $tot }}">Total ({{ $totals['count'] }} settlement{{ $totals['count'] == 1 ? '' : 's' }}; live waivers only)</td>
    <td style="{{ $totr }}">{{ numberFormat($totals['amount']) }}</td>
    <td style="{{ $totr }}">{{ numberFormat($totals['waiver_amount']) }}</td>
    <td style="{{ $totr }}">{{ numberFormat($totals['fees_recovered']) }}</td>
    <td style="{{ $totr }}">{{ numberFormat($totals['settled_total']) }}</td>
    <td style="{{ $totr }}">{{ numberFormat($totals['collected']) }}</td>
    <td style="{{ $totr }}">{{ numberFormat($totals['held']) }}</td>
    <td style="{{ $totr }}">{{ numberFormat($totals['balance']) }}</td>
    <td style="{{ $totr }}">{{ numberFormat($totals['overdue']) }}</td>
    <td colspan="2" style="{{ $tot }}"></td>
  </tr>
@endif
</table>
@endforeach
