{{-- Legal Cases Summary tables: screen, PDF and Excel. Inline styles only (Excel reads no <style>). --}}
@php
    $fs = !empty($pdf) ? 'font-size:7.5px;' : '';
    $pad = !empty($pdf) ? 'padding:2px 3px;' : '';
    $LC = \Modules\Masters\Services\LegalCase::class;
    $th = 'background-color:#1F3864;color:#FFFFFF;font-weight:bold;font-size:11px;border:1px solid #1F3864;padding:4px 6px;text-align:left;' . $fs . $pad;
    $thr = $th . 'text-align:right;';
    $td = 'font-size:11px;border:1px solid #CFD9E6;padding:3px 6px;' . $fs . $pad;
    $tdr = $td . 'text-align:right;';
    $tot = 'font-size:11px;border:1px solid #8FAADC;padding:3px 6px;background-color:#EAF0FA;font-weight:bold;' . $fs . $pad;
    $totr = $tot . 'text-align:right;';
    $cap = 'font-size:13px;font-weight:bold;color:#1F3864;padding:10px 0 4px;';
    $prio = $summary['priorities'];
    $n = function ($v) { return $v === null ? '' : numberFormat($v); };
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
<table class="{{ $screen ? 'lg-table' : '' }}" style="border-collapse:collapse;margin-bottom:6px;">
  <tr><td colspan="{{ count($prio) + 2 }}" style="{{ $cap }}">Open cases by stage and priority ({{ $summary['open'] }} open, outstanding {{ numberFormat($summary['outstanding']) }})</td></tr>
  <tr><th style="{{ $th }}">Stage</th>@foreach($prio as $p)<th style="{{ $thr }}">{{ $p }}</th>@endforeach<th style="{{ $thr }}">Total</th></tr>
  @forelse($summary['byStage'] as $stage => $c)
    <tr><td style="{{ $td }}">{{ $stage }}</td>@foreach($prio as $k => $p)<td style="{{ $tdr }}">{{ $c[$k] ?: '' }}</td>@endforeach<td style="{{ $totr }}">{{ $c['total'] }}</td></tr>
  @empty
    <tr><td colspan="{{ count($prio) + 2 }}" style="{{ $td }}">No open cases.</td></tr>
  @endforelse
</table>
<table class="{{ $screen ? 'lg-table' : '' }}" style="border-collapse:collapse;margin-bottom:6px;">
  <tr><td colspan="{{ count($prio) + 2 }}" style="{{ $cap }}">Open cases by days in legal and priority</td></tr>
  <tr><th style="{{ $th }}">Days in legal</th>@foreach($prio as $p)<th style="{{ $thr }}">{{ $p }}</th>@endforeach<th style="{{ $thr }}">Total</th></tr>
  @foreach($summary['byAge'] as $age => $c)
    <tr><td style="{{ $td }}">{{ $age }}</td>@foreach($prio as $k => $p)<td style="{{ $tdr }}">{{ $c[$k] ?: '' }}</td>@endforeach<td style="{{ $totr }}">{{ $c['total'] }}</td></tr>
  @endforeach
</table>
{{-- dompdf runs out of memory on one table of ~600 rows: the PDF gets page-sized tables --}}
@php $chunks = !empty($pdf) ? $rows->chunk(22)->values() : collect([$rows]); if (!$chunks->count()) { $chunks = collect([collect()]); } @endphp
@foreach($chunks as $ci => $chunk)
<table class="{{ $screen ? 'lg-table' : '' }}" style="border-collapse:collapse;width:100%;@if(!empty($pdf) && $ci > 0) page-break-before:always;@endif">
  @if($ci === 0)<tr><td colspan="19" style="{{ $cap }}">Cases ({{ count($rows) }})</td></tr>@endif
  <tr>
    <th style="{{ $th }}">Case</th><th style="{{ $th }}">Agreement</th><th style="{{ $th }}">Tenant</th><th style="{{ $th }}">Building</th><th style="{{ $th }}">Unit</th>
    <th style="{{ $th }}">Handled by</th><th style="{{ $th }}">Type</th><th style="{{ $th }}">Priority</th><th style="{{ $th }}">Lawyer</th>
    <th style="{{ $th }}">State</th><th style="{{ $th }}">Stage</th><th style="{{ $thr }}">Days in legal</th><th style="{{ $thr }}">Days at stage</th>
    <th style="{{ $th }}">Next hearing</th><th style="{{ $thr }}">Claim</th><th style="{{ $thr }}">Outstanding today</th>
    <th style="{{ $th }}">Settlement</th><th style="{{ $thr }}">Settlement balance</th><th style="{{ $th }}">Closed</th>
  </tr>
  @forelse($chunk as $r)
  <tr>
    <td style="{{ $td }}">@if(!isset($pdf))<a href="{{ (int) $r->work_flow_processes_code === 803 ? route('legal.case', $r->id) : (\Modules\Masters\Services\LegalCase::screenUrl(\Modules\Masters\Entities\Legal::find($r->id))) }}">#{{ $r->id }}</a>@else #{{ $r->id }} @endif</td>
    <td style="{{ $td }}">{{ $r->tenant_contract_no }}</td>
    <td style="{{ $td }}">{{ $r->tenant_name }}</td>
    <td style="{{ $td }}">{{ $r->building_name }}</td>
    <td style="{{ $td }}">{{ $r->unit_code }}</td>
    <td style="{{ $td }}">{{ $LC::HANDLED_BY[$r->handled_by] ?? '' }}</td>
    <td style="{{ $td }}">{{ $r->case_type ? ($LC::TYPES[$r->case_type] ?? '') : '' }}</td>
    <td style="{{ $td }}">{{ $r->priority ? ($LC::PRIORITIES[$r->priority] ?? '') : '' }}</td>
    <td style="{{ $td }}">{{ $r->lawyer_name }}</td>
    <td style="{{ $td }}">{{ $r->state }}</td>
    <td style="{{ $td }}">{{ $r->stage_name }}</td>
    <td style="{{ $tdr }}">{{ $r->days_in_legal }}</td>
    <td style="{{ $tdr }}">{{ $r->days_at_stage }}</td>
    <td style="{{ $td }}">{{ $d($r->next_hearing_date) }}</td>
    <td style="{{ $tdr }}">{{ $n($r->claim_amount) }}</td>
    <td style="{{ $tdr }}">{{ $n($r->outstanding) }}</td>
    <td style="{{ $td }}">{{ $r->settlement_status }}</td>
    <td style="{{ $tdr }}">{{ $n($r->settlement_balance) }}</td>
    <td style="{{ $td }}">{{ $r->closed_at ? $d($r->closed_at) . ' ' . $LC::reasonLabel($r->close_reason) : '' }}</td>
  </tr>
  @empty
  <tr><td colspan="19" style="{{ $td }}">No cases match the filters.</td></tr>
  @endforelse
</table>
@endforeach
