<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Tenant Report</title>
    <style>
        @page { size: A4 landscape; margin: 15px; }
        body { font-family: sans-serif; font-size: 8px; margin: 0; padding: 0; }
        .header { width: 100%; margin-bottom: 8px; }
        .header td { vertical-align: top; border: none; }
        .logo { height: 55px; }
        .report-title { font-size: 16px; font-weight: bold; text-align: center; padding-top: 5px; }
        .meta-info { text-align: right; font-size: 8px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #999; padding: 3px 4px; }
        table.data th { background: #eee; font-weight: bold; text-align: left; }
        table.data td.num { text-align: right; }
        .note { margin: 4px 0 8px 0; font-size: 8px; }
    </style>
</head>
<body>

<table class="header">
    <tr>
        <td width="20%">
            @isset($logo)<img src="{{ $logo }}" class="logo">@endisset
        </td>
        <td width="60%"><div class="report-title">Cash Tenant Report</div></td>
        <td width="20%">
            <div class="meta-info">
                {{ date('d/m/Y H:i') }}<br>
                @isset($user){{ $user->username }}@endisset
            </div>
        </td>
    </tr>
</table>

<div class="note">
    Active tenant contracts with no post dated cheque on record, i.e. tenants paying by cash.
    Total: <b>{{ $total }}</b>
</div>

<table class="data">
    <thead>
        <tr>
            <th>Sl No</th>
            <th>Contract No</th>
            <th>Tenant</th>
            <th>Building</th>
            <th>Unit No</th>
            <th>Unit Type</th>
            <th>Start</th>
            <th>End</th>
            <th>Rent</th>
            <th>Payment Term</th>
            <th>Mobile No</th>
        </tr>
    </thead>
    <tbody>
        @php use Modules\BackOffice\Http\Controllers\CashTenantReportController as CashTenantReport; @endphp
        @forelse($rows as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row->tenant_contract_no }}</td>
            <td>{{ $row->tenant_name }}</td>
            <td>{{ $row->building_name }}</td>
            <td>{{ $row->unit_code }}</td>
            <td>{{ $row->unit_types_name }}</td>
            <td>{{ $row->tenant_contract_start_date ? \Carbon\Carbon::parse($row->tenant_contract_start_date)->format('d/m/Y') : '' }}</td>
            <td>{{ $row->tenant_contract_valid_to_date ? \Carbon\Carbon::parse($row->tenant_contract_valid_to_date)->format('d/m/Y') : '' }}</td>
            <td class="num">{{ numberFormat($row->tenant_contract_rent) }}</td>
            <td>{{ CashTenantReport::paymentTermLabel($row->tenant_contract_payment_type) }}</td>
            <td>{{ $row->tenant_contact_no }}</td>
        </tr>
        @empty
        <tr><td colspan="11" align="center">No Record</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
