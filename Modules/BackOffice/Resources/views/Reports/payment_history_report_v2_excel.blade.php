{{-- Excel sheet: same contract grouping, subtotals and grand total as the PDF.
     Maatwebsite's FromView reads INLINE styles only - no <style> block and no
     classes - so every cell carries its own formatting. --}}
@php
    $thStyle    = 'background-color:#1F3864;color:#FFFFFF;font-weight:bold;font-size:12px;border:1px solid #1F3864;';
    $tdStyle    = 'font-size:12px;border:1px solid #CFD9E6;';
    $tdNum      = $tdStyle . 'text-align:right;';
    $zebra      = 'background-color:#F6F9FD;';
    $headStyle  = 'background-color:#D9E2F3;color:#1F3864;font-weight:bold;font-size:13px;border:1px solid #8FAADC;';
    $subStyle   = 'background-color:#EAF0FA;color:#1F3864;font-weight:bold;font-size:12px;border:1px solid #8FAADC;';
    $grandStyle = 'background-color:#1F3864;color:#FFFFFF;font-weight:bold;font-size:14px;border:1px solid #1F3864;';
    $lblStyle   = 'font-size:12px;color:#5B6B7F;';
    $valStyle   = 'font-size:13px;font-weight:bold;color:#1F3864;';
@endphp
<table>
  <thead>
    <tr>
      <td colspan="8" style="font-size:20px;font-weight:bold;color:#1F3864;text-align:center;">Tenant's Payment History</td>
    </tr>
    <tr>
      <td colspan="8" style="font-size:13px;color:#5B6B7F;text-align:center;">Agreement Wise</td>
    </tr>
    <tr><td colspan="8"></td></tr>
    <tr>
      <td style="{{ $lblStyle }}">Building</td>
      <td colspan="3" style="{{ $valStyle }}">{{ $buildingName }}</td>
      <td style="{{ $lblStyle }}">Unit</td>
      <td style="{{ $valStyle }}">{{ $unitNo }}</td>
      <td style="{{ $lblStyle }}">Tenant</td>
      <td style="{{ $valStyle }}">{{ $tenantName }}</td>
    </tr>
    <tr>
      <td colspan="8" style="font-size:11px;color:#8A94A6;">Generated {{ $generatedAt }} by {{ $user }}</td>
    </tr>
    <tr><td colspan="8"></td></tr>
    <tr>
      <th style="{{ $thStyle }}">#</th>
      <th style="{{ $thStyle }}">Receipt No</th>
      <th style="{{ $thStyle }}">Receipt Date</th>
      <th style="{{ $thStyle }}">Period From</th>
      <th style="{{ $thStyle }}">Period To</th>
      <th style="{{ $thStyle }}">Payment Method</th>
      <th style="{{ $thStyle }}">Cheque No</th>
      <th style="{{ $thStyle }}text-align:right;">Amount</th>
    </tr>
  </thead>
  <tbody>
    @forelse($groups as $group)
    <tr>
      <td colspan="8" style="{{ $headStyle }}">
        Contract No : {{ $group['contract_no'] }}@if($group['start_date']) &bull; {{ \Carbon\Carbon::parse($group['start_date'])->format('d/m/Y') }} to {{ $group['end_date'] ? \Carbon\Carbon::parse($group['end_date'])->format('d/m/Y') : '' }}@endif &bull; Rent : {{ $group['rent'] }}
      </td>
    </tr>
    @foreach($group['rows'] as $i => $row)
    @php $rowStyle = $tdStyle . ($i % 2 == 1 ? $zebra : ''); $rowNum = $tdNum . ($i % 2 == 1 ? $zebra : ''); @endphp
    <tr>
      <td style="{{ $rowStyle }}">{{ $i + 1 }}</td>
      <td style="{{ $rowStyle }}">{{ $row->receipt_no }}</td>
      <td style="{{ $rowStyle }}">{{ $row->receipt_date ? \Carbon\Carbon::parse($row->receipt_date)->format('d/m/Y') : '' }}</td>
      <td style="{{ $rowStyle }}">{{ $row->eff_from ? \Carbon\Carbon::parse($row->eff_from)->format('d/m/Y') : '' }}</td>
      <td style="{{ $rowStyle }}">{{ $row->eff_to ? \Carbon\Carbon::parse($row->eff_to)->format('d/m/Y') : '' }}</td>
      <td style="{{ $rowStyle }}">{{ $row->payment_method }}</td>
      <td style="{{ $rowStyle }}">{{ $row->cheque_no }}</td>
      {{-- numeric, not a string, so Excel can sum the column --}}
      <td style="{{ $rowNum }}">{{ $row->amount }}</td>
    </tr>
    @endforeach
    <tr>
      <td colspan="7" style="{{ $subStyle }}">Subtotal &mdash; {{ $group['contract_no'] }} ({{ $group['count'] }} receipt{{ $group['count'] == 1 ? '' : 's' }})</td>
      <td style="{{ $subStyle }}text-align:right;">{{ $group['subtotal'] }}</td>
    </tr>
    {{-- blank row separates one contract block from the next --}}
    <tr><td colspan="8"></td></tr>
    @empty
    <tr><td colspan="8" style="font-size:12px;text-align:center;color:#8A94A6;">No records found for this selection.</td></tr>
    @endforelse

    <tr>
      <td colspan="7" style="{{ $grandStyle }}">GRAND TOTAL &mdash; {{ $receiptCount }} receipt{{ $receiptCount == 1 ? '' : 's' }} across {{ $contractCount }} contract{{ $contractCount == 1 ? '' : 's' }}</td>
      <td style="{{ $grandStyle }}text-align:right;">{{ $grandTotal }}</td>
    </tr>
  </tbody>
</table>
