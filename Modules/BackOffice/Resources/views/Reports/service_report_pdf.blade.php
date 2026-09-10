<!DOCTYPE html>
<html>
<head>
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    .header-table td { padding: 2px 4px; vertical-align: top; }
    .title { font-size: 18px; font-weight: bold; text-align: center; }
    .info-table td { padding: 3px 4px; }
    .info-table td.label { font-weight: bold; width: 150px; }
    .items-table { margin-top: 10px; }
    .items-table th, .items-table td { border: 1px solid #999; padding: 4px 6px; }
    .items-table th { background-color: #045DC2; color: #FFFFFF; text-align: center; }
    .items-table td.num { text-align: right; }
    .items-table tfoot td { font-weight: bold; background-color: #E8F4FC; text-align: right; }
</style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width:120px;">
                @if($logo)
                    <img src="{{ $logo }}" style="width:100px;">
                @endif
            </td>
            <td class="title">Service Report</td>
            <td style="width:150px; text-align:right;">
                Date: {{ date('d/m/Y H:i') }}<br>
                User: {{ $user }}
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="label">Service Report No</td>
            <td>{{ $header->service_report_no }}</td>
            <td class="label">Report Date</td>
            <td>{{ $header->report_date ? date('d/m/Y', strtotime($header->report_date)) : '' }}</td>
        </tr>
        <tr>
            <td class="label">Building</td>
            <td>{{ $header->building_name }}</td>
            <td class="label">Unit No</td>
            <td>{{ $header->unit_no }}</td>
        </tr>
        <tr>
            <td class="label">Complaint No</td>
            <td>{{ $header->complaint_no }}</td>
            <td class="label"></td>
            <td></td>
        </tr>
        <tr>
            <td class="label">Description</td>
            <td colspan="3">{{ $header->checklist_desc }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Material Charge</th>
                <th>Labour Charge</th>
                <th>Tax %</th>
                <th>Tax Amount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($items as $item)
                @php $grandTotal += (float)$item->total_charge; @endphp
                <tr>
                    <td>{{ $item->inventories_name }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ numberFormat($item->material_charge) }}</td>
                    <td class="num">{{ numberFormat($item->labour_charge) }}</td>
                    <td class="num">{{ numberFormat($item->tax_percentage) }}</td>
                    <td class="num">{{ numberFormat($item->tax_amount) }}</td>
                    <td class="num">{{ numberFormat($item->total_charge) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;">No items found</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">Grand Total</td>
                <td>{{ numberFormat($grandTotal) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
