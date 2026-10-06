{{-- Breakup of the tenant's rent outstanding (TenantRentOutstanding::forTermination), opened from the outstanding total. --}}
@php
  $tos = isset($tenantOs) ? $tenantOs : null;
  $osDate = function ($d) { return $d ? date('d/m/Y', strtotime($d)) : ''; };
@endphp
@if($tos)
<style>
  .tos-link { cursor: pointer; text-decoration: underline dotted; text-underline-offset: 3px; }
  .tos-link:focus-visible { outline: 2px solid #3b63f3; outline-offset: 2px; }
  #tenant-os-modal .modal-dialog { max-width: 980px; }
  #tenant-os-modal .tos-sum { display: flex; flex-wrap: wrap; gap: 24px; margin-bottom: 16px; }
  #tenant-os-modal .tos-sum div { font-size: 13px; color: #475569; }
  #tenant-os-modal .tos-sum b { display: block; font-size: 20px; color: #0f172a; font-weight: 600; }
  #tenant-os-modal table { width: 100%; font-size: 13px; }
  #tenant-os-modal th { font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; color: #475569; padding: 8px 12px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
  #tenant-os-modal td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
  #tenant-os-modal .num { text-align: right; white-space: nowrap; }
  #tenant-os-modal .tos-nw { white-space: nowrap; }
  #tenant-os-modal tr.tos-current td { background: #eef4ff; }
  #tenant-os-modal tfoot td { font-weight: 600; border-top: 2px solid #e2e8f0; border-bottom: 0; }
  #tenant-os-modal .tos-tag { display: inline-block; font-size: 11px; font-weight: 500; padding: 1px 6px; border-radius: 4px; margin-left: 4px; }
  #tenant-os-modal .tos-tag-legal { background: #fef2f2; color: #dc2626; }
  #tenant-os-modal .tos-tag-this { background: #dbe6fe; color: #2740ad; }
  #tenant-os-modal .tos-tag-unit { background: #f1f5f9; color: #475569; }
  #tenant-os-modal .tos-note { font-size: 12px; color: #475569; margin: 12px 0 0; }
  #tenant-os-modal .tos-scroll { overflow-x: auto; }
</style>
<div class="modal fade" id="tenant-os-modal" tabindex="-1" role="dialog" aria-labelledby="tenant-os-title" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tenant-os-title">Rent outstanding of {{ optional($tenantContract->tenant)->tenant_name }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="tos-sum">
          <div>This contract<b>{{ numberFormat($tos['current']['outstanding']) }}</b></div>
          <div>Expired contracts ({{ count($tos['expired']) }})<b>{{ numberFormat($tos['expired_total']) }}</b></div>
          <div>Total outstanding<b>{{ numberFormat($tos['total']) }} OMR</b></div>
        </div>
        <div class="tos-scroll">
          <table>
            <thead>
              <tr><th>Contract No</th><th>Building / Unit</th><th>Period</th><th>Unpaid months</th><th class="num">Outstanding (OMR)</th></tr>
            </thead>
            <tbody>
              @php $c = $tos['current']; @endphp
              <tr class="tos-current">
                <td>{{ $c['contract_no'] }}<span class="tos-tag tos-tag-this">This termination</span>@if($c['in_legal'])<span class="tos-tag tos-tag-legal">In legal</span>@endif</td>
                <td>{{ $c['building'] }} / {{ $c['unit'] }}</td>
                <td class="tos-nw">{{ $osDate($c['from']) }} - {{ $osDate($c['to']) }}</td>
                <td>{{ $c['unpaid'] ?: '-' }}</td>
                <td class="num">{{ numberFormat($c['outstanding']) }}</td>
              </tr>
              @forelse($tos['expired'] as $e)
              <tr>
                <td>{{ $e['contract_no'] }}@if($e['in_legal'])<span class="tos-tag tos-tag-legal">In legal</span>@endif @if($e['same_unit'])<span class="tos-tag tos-tag-unit">Same unit</span>@endif</td>
                <td>{{ $e['building'] }} / {{ $e['unit'] }}</td>
                <td class="tos-nw">{{ $osDate($e['from']) }} - {{ $osDate($e['to']) }}</td>
                <td>{{ $e['unpaid'] ?: '-' }}</td>
                <td class="num">{{ numberFormat($e['outstanding']) }}</td>
              </tr>
              @empty
              <tr><td colspan="5">No rent outstanding on the tenant's expired contracts.</td></tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr><td colspan="4">Total outstanding</td><td class="num">{{ numberFormat($tos['total']) }}</td></tr>
            </tfoot>
          </table>
        </div>
        <p class="tos-note">This contract: rent as on the termination date. Expired contracts: the tenant's ended or terminated contracts on any unit, at the Rent Receivable V2 figure, legal cases included. Contracts in buildings that are no longer active are not included.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endif
