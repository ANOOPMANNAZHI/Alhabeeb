{{-- Excel sheet for the Cash Tenant Report. $rows is the FULL result set. --}}
@php
  use Modules\BackOffice\Http\Controllers\CashTenantReportController as CashTenantReport;
@endphp
<table>
  <thead>
    <tr>
      <th colspan="11" style="font-weight:bold;font-size:14px">Cash Tenant Report</th>
    </tr>
    <tr>
      <td colspan="11">Active tenant contracts with no post dated cheque on record. Total: {{ $total }}</td>
    </tr>
    <tr>
      <td colspan="11">Generated {{ date('d/m/Y H:i') }}@isset($user) by {{ $user->username }}@endisset</td>
    </tr>
    <tr><td colspan="11"></td></tr>
    <tr>
      <th style="font-weight:bold">Sl No</th>
      <th style="font-weight:bold">Contract No</th>
      <th style="font-weight:bold">Tenant</th>
      <th style="font-weight:bold">Building</th>
      <th style="font-weight:bold">Unit No</th>
      <th style="font-weight:bold">Unit Type</th>
      <th style="font-weight:bold">Start</th>
      <th style="font-weight:bold">End</th>
      <th style="font-weight:bold">Rent</th>
      <th style="font-weight:bold">Payment Term</th>
      <th style="font-weight:bold">Mobile No</th>
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $i => $row)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $row->tenant_contract_no }}</td>
      <td>{{ $row->tenant_name }}</td>
      <td>{{ $row->building_name }}</td>
      <td>{{ $row->unit_code }}</td>
      <td>{{ $row->unit_types_name }}</td>
      <td>{{ $row->tenant_contract_start_date ? \Carbon\Carbon::parse($row->tenant_contract_start_date)->format('d/m/Y') : '' }}</td>
      <td>{{ $row->tenant_contract_valid_to_date ? \Carbon\Carbon::parse($row->tenant_contract_valid_to_date)->format('d/m/Y') : '' }}</td>
      <td>{{ $row->tenant_contract_rent }}</td>
      <td>{{ CashTenantReport::paymentTermLabel($row->tenant_contract_payment_type) }}</td>
      <td>{{ $row->tenant_contact_no }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
