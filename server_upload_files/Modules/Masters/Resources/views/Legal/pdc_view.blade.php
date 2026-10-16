@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

@section('content')
@php
    $d = function ($v) { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : ''; };
    $first = $pdc->first();
    $contractNo = $first && $first->tenantContractInfo ? $first->tenantContractInfo->tenant_contract_no : '';
    $total = $pdc->sum('pdc_amt');
@endphp
<div class="lg">
  <header class="lg-page-head">
    <div>
      <h1 class="lg-page-head__title">Cheques @if($contractNo)&middot; {{ $contractNo }}@endif</h1>
      <div class="lg-page-head__sub"><span class="tnum">{{ count($pdc) }}</span> cheque{{ count($pdc) == 1 ? '' : 's' }}<span class="sep">/</span>total <span class="tnum">{{ numberFormat($total) }}</span></div>
    </div>
    <div class="lg-page-head__actions"><a class="lg-btn lg-btn--ghost" href="{{ url()->previous() }}">Back</a></div>
  </header>
  <section class="lg-card" aria-label="Cheques">
    <div class="lg-table-wrap">
      <table class="lg-table">
        <thead>
          <tr>
            <th scope="col">Cheque no.</th><th scope="col">Cheque date</th><th scope="col" class="num">Amount</th><th scope="col">Bank</th>
            <th scope="col">Received</th><th scope="col">Deposited</th><th scope="col">Cleared</th><th scope="col">Receipt no.</th>
            <th scope="col">Status</th><th scope="col">Period</th><th scope="col">Transaction</th><th scope="col">Remark</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pdc as $row)
            @php
              $status = $row->pdc_cancel_date ? ($row->pdc_bounce_reason ? ['Bounced', 'danger'] : ['Cancelled', 'neutral'])
                : ($row->pdc_receipt_no ? ['Receipted', 'success'] : ($row->pdc_deposit_date ? ['Deposited', 'info'] : ['Held', 'warning']));
            @endphp
            <tr>
              <td class="strong">{{ $row->pdc_check_no }}</td>
              <td class="tnum">{{ $d($row->pdc_check_date) }}</td>
              <td class="num">{{ numberFormat($row->pdc_amt) }}</td>
              <td>{{ $row->bankInfo->bank_name ?? '' }}</td>
              <td class="tnum">{{ $d($row->pdc_recieve_date) }}</td>
              <td class="tnum">{{ $d($row->pdc_deposit_date) }}</td>
              <td class="tnum">{{ $d($row->pdc_clear_date) }}</td>
              <td>{{ $row->pdc_receipt_no }}</td>
              <td><span class="lg-badge lg-badge--{{ $status[1] }}">{{ $status[0] }}</span>@if($row->pdc_cancel_date)<div class="muted" style="font-size:12px;">{{ $d($row->pdc_cancel_date) }}</div>@endif</td>
              <td>{{ $row->pdc_period }}</td>
              <td>{{ $row->pdc_transaction_no }}<div class="muted" style="font-size:12px;">{{ $d($row->pdc_transaction_date) }}</div></td>
              <td style="white-space:normal;min-width:160px;">{{ strtoupper(trim((string) $row->pdc_remark)) === 'NULL' ? '' : $row->pdc_remark }}</td>
            </tr>
          @empty
            <tr><td colspan="12"><div class="lg-empty"><div class="lg-empty__title">No cheques</div>This contract has no post-dated cheques.</div></td></tr>
          @endforelse
        </tbody>
        @if(count($pdc))
        <tfoot><tr><td colspan="2">Total</td><td class="num">{{ numberFormat($total) }}</td><td colspan="9"></td></tr></tfoot>
        @endif
      </table>
    </div>
  </section>
</div>
@endsection
