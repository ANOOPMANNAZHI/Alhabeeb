{{-- Table rows for the Cash Tenant Report. Shared by the screen (inside
     cash_tenant_report.blade.php) and returned on its own for ajax paging. --}}
@php
  use Modules\BackOffice\Http\Controllers\CashTenantReportController as CashTenantReport;
  $start = method_exists($rows, 'firstItem') && $rows->firstItem() ? $rows->firstItem() : 1;
@endphp
@forelse($rows as $i => $row)
<tr>
  <td>{{ $start + $i }}</td>
  <td>{{ $row->tenant_contract_no }}</td>
  <td>{{ $row->tenant_name }}</td>
  <td>{{ $row->building_name }}</td>
  <td>{{ $row->unit_code }}</td>
  <td>{{ $row->unit_types_name }}</td>
  <td>{{ $row->tenant_contract_start_date ? \Carbon\Carbon::parse($row->tenant_contract_start_date)->format('d/m/Y') : 'NA' }}</td>
  <td>{{ $row->tenant_contract_valid_to_date ? \Carbon\Carbon::parse($row->tenant_contract_valid_to_date)->format('d/m/Y') : 'NA' }}</td>
  <td class="num">{{ numberFormat($row->tenant_contract_rent) }}</td>
  <td>{{ CashTenantReport::paymentTermLabel($row->tenant_contract_payment_type) }}</td>
  <td>{{ $row->tenant_contact_no }}</td>
</tr>
@empty
<tr>
  <td colspan="11" align="center">
    <p>No Record</p>
  </td>
</tr>
@endforelse
