<table>
    <thead>
        <tr>
            <th>Sl</th>
            <th>Employee</th>
            <th>Building</th>
            <th>Bldg No</th>
            <th>Unit No</th>
            <th>Tenant Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Contract Period (Months)</th>
            <th>Rent P/M</th>
            <th>Deposit</th>
            <th>No of PDC</th>
            <th>Management Type</th>
        </tr>
    </thead>
    <tbody>
        @php $slNo = 1; @endphp
        @foreach($groupedContracts as $empName => $contracts)
            @foreach($contracts as $row)
                <tr>
                    <td>{{ $slNo++ }}</td>
                    <td>{{ $empName }}</td>
                    <td>{{ $row->building_name }}</td>
                    <td>{{ $row->building_no }}</td>
                    <td>{{ $row->unit_no }}</td>
                    <td>{{ $row->tenant_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->startdate)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->enddate)->format('d/m/Y') }}</td>
                    <td>{{ $row->contract_period_months }}</td>
                    <td>{{ $row->tenant_contract_rent }}</td>
                    <td>{{ $row->deposit_amt ?? 0 }}</td>
                    <td>{{ $row->nofpdc ?? 0 }}</td>
                    <td>{{ $row->management_type }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
