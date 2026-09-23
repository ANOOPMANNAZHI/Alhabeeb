{{-- Receipts created by bank-sheet posting runs + the cheques they did not post.
     Params: $receipts, $notPosted, $uploadNames (upload id => sheet name; given => show an Upload column). --}}
@php
  $showUpload  = isset($uploadNames);
  $uploadNames = $showUpload ? $uploadNames : [];
  $fmtDate = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-'; };
  $types = [0 => 'Rent', 1 => 'General', 2 => 'Deposit'];
  // label + icon so status never relies on colour alone.
  // approval_status: 1 not approved, 2/5 approval requested, 3 approved, 4 rejected, 6 posted to AX
  $receiptStatus = function ($r) {
    $a = (int) $r->approval_status;
    if ($r->deleted_at)                 return ['bsr-pill-off',  'fa-trash',        'Deleted'];
    if ((int) $r->receipt_status === 2) return ['bsr-pill-bad',  'fa-ban',          'Cancelled'];
    if ($a === 6)                       return ['bsr-pill-ok',   'fa-check-circle', 'Posted to AX'];
    if ($a === 3)                       return ['bsr-pill-ok',   'fa-check',        'Approved'];
    if ($a === 4)                       return ['bsr-pill-bad',  'fa-times-circle', 'Rejected'];
    if ($a === 2 || $a === 5)           return ['bsr-pill-warn', 'fa-clock-o',      'Approval requested'];
    return ['bsr-pill-warn', 'fa-clock-o', 'Not approved'];
  };
  $rowStatus = [
    'failed'          => 'Failed',
    'skipped'         => 'Not selected',
    'not_found'       => 'Not in PLMS',
    'amount_mismatch' => 'Amount mismatch',
    'date_mismatch'   => 'Date mismatch',
    'unknown_bank'    => 'Unknown bank',
  ];
  $uploadCell = function ($id) use ($uploadNames) {
    return '<a href="' . e(route('pdcBankSheetResult', $id)) . '">#' . (int) $id . '</a>'
         . (isset($uploadNames[$id]) ? '<span class="bsr-sub">' . e($uploadNames[$id]) . '</span>' : '');
  };
  $total = 0;
  foreach ($receipts as $r) { if (!$r->deleted_at && (int) $r->receipt_status !== 2) $total += $r->amount; }
  $cols = $showUpload ? 12 : 11;
@endphp
<div class="card card-box">
  <div class="card-body">
    <div class="bsr-head">
      <h4>Receipts created</h4>
      <span class="bsr-hint">Current status of each receipt; the total excludes cancelled and deleted receipts.</span>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th width="4%">#</th>
            @if($showUpload)<th>Upload</th>@endif
            <th>Receipt No</th>
            <th>Receipt Date</th>
            <th>Tenant</th>
            <th>Agreement No</th>
            <th>Building / Unit</th>
            <th>Cheque No</th>
            <th>Cheque Bank</th>
            <th>Type</th>
            <th class="num">Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($receipts as $i => $r)
          @php list($cls, $icon, $label) = $receiptStatus($r); @endphp
          <tr>
            <td>{{ $i + 1 }}</td>
            @if($showUpload)<td>{!! $uploadCell($r->upload_id) !!}</td>@endif
            <td>{{ $r->receipt_no }}</td>
            <td>{{ $fmtDate($r->receipt_date) }}</td>
            <td>{{ $r->tenant_name ?: '-' }}</td>
            <td>{{ $r->tenant_contract_no ?: '-' }}</td>
            <td>{{ $r->building_name ?: '-' }}{{ $r->unit_no ? ' / ' . $r->unit_no : '' }}</td>
            <td>{{ $r->sheet_cheque_no }}</td>
            <td>{{ $r->sheet_bank_name ?: '-' }}</td>
            <td>{{ isset($types[(int) $r->receipt_type]) ? $types[(int) $r->receipt_type] : '-' }}</td>
            <td class="num">{{ number_format($r->amount, 3) }}</td>
            <td><span class="bsr-pill {{ $cls }}"><i class="fa {{ $icon }}" aria-hidden="true"></i>{{ $label }}</span></td>
          </tr>
          @empty
          <tr><td colspan="{{ $cols }}" align="center">No receipts were created.</td></tr>
          @endforelse
          @if(count($receipts))
          <tr class="bsr-total">
            <td colspan="{{ $cols - 2 }}">Total ({{ count($receipts) }} receipts)</td>
            <td class="num">{{ number_format($total, 3) }}</td>
            <td></td>
          </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>

@if(count($notPosted) > 0)
<div class="card card-box">
  <div class="card-body">
    <div class="bsr-head">
      <h4>Not posted</h4>
      <span class="bsr-hint">Cheques on the sheet that were not posted; post them manually from PDC Posting if needed.</span>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            @if($showUpload)<th>Upload</th>@endif
            <th width="6%">Sheet Row</th>
            <th>Cheque No</th>
            <th>Cheque Bank</th>
            <th>Cheque Date</th>
            <th class="num">Amount</th>
            <th>Status</th>
            <th>Reason</th>
          </tr>
        </thead>
        <tbody>
          @foreach($notPosted as $n)
          <tr>
            @if($showUpload)<td>{!! $uploadCell($n->upload_id) !!}</td>@endif
            <td>{{ $n->sheet_row_no }}</td>
            <td>{{ $n->cheque_no }}</td>
            <td>{{ $n->bank_name ?: '-' }}</td>
            <td>{{ $fmtDate($n->cheque_date) }}</td>
            <td class="num">{{ number_format($n->amount, 3) }}</td>
            <td>{{ isset($rowStatus[$n->status]) ? $rowStatus[$n->status] : $n->status }}</td>
            <td>{{ $n->reason ?: '-' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif
