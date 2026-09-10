<!DOCTYPE html>
<html>
<head>
<style>
    /*
     * Reproduces Service_Report_date.jrxml / Service_Report_compo.jrxml (the v1
     * Jasper report) using normal document flow (tables), not absolute
     * positioning - item descriptions in real data are often long free text,
     * and an earlier absolute-position version let that text overflow into
     * neighboring columns since it can't reflow or grow row height. Tables
     * wrap and grow naturally, so long text pushes rows down instead of
     * overlapping the next cell.
     *
     * dompdf 0.8.4 (this app's PDF engine) does not support flexbox - vertical
     * centering here uses line-height instead.
     */
    @page { margin: 20px; }
    body { font-family: Arial, sans-serif; font-size: 18px; color: #000; margin: 0; }

    /* A3 is much wider than the report was designed for - scale the whole
       canvas (and every font size / image) up by ~1.5x so the report fills
       the page instead of sitting as a narrow column with empty space on
       the right. */
    .page-content { width: 1080px; }
    .sheet { page-break-after: always; position: relative; }
    .sheet:last-child { page-break-after: auto; }

    .top-table { width: 100%; margin-bottom: 22px; }
    .top-table td { vertical-align: top; }
    .logo-cell img { width: 210px; }
    .address-cell img { width: 390px; }

    .title { text-align: center; font-size: 33px; font-weight: bold; text-decoration: underline; margin: 22px 0 30px 0; }

    .info-table { width: 100%; border-collapse: separate; border-spacing: 8px 6px; margin-bottom: 10px; font-size: 17px; }
    .info-table col.label { width: 17%; }
    .info-table col.value { width: 19%; }
    .info-table col.label2 { width: 6%; }
    .info-table col.value2 { width: 13%; }
    .info-table col.label3 { width: 24%; }
    .info-table col.value3 { width: 21%; }
    .info-table td { padding: 9px 12px; height: 30px; }
    .info-table td.box { border: 1px solid #000; white-space: nowrap; }
    .info-table td.plain { border: none; white-space: nowrap; }
    .info-table td.label-right { text-align: right; }

    .section-title { font-size: 20px; font-weight: bold; margin: 15px 0 9px 0; }

    .items-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .items-table th { background-color: #045DC2; color: #F7F5F5; font-size: 17px; font-weight: bold; padding: 12px; text-align: left; border: 1px solid #045DC2; }
    .items-table td { border: 1px solid #D6D4D4; padding: 12px; font-size: 18px; word-wrap: break-word; }
    .items-table td.num { text-align: right; }

    .total-table { width: 100%; border-collapse: collapse; }
    .total-table td { border: 1px solid #999; padding: 12px; font-size: 18px; background-color: #DCEBF5; font-weight: bold; }
    .total-table td.total-label { text-align: right; }
    .total-table td.total-value { text-align: right; }

    .satisfactory { font-size: 20px; font-weight: bold; margin-top: 30px; }

    .sign-table { width: 100%; table-layout: fixed; margin-top: 45px; }
    .sign-table td { font-size: 18px; vertical-align: bottom; padding-right: 15px; word-wrap: break-word; }
    .sign-underline { border-bottom: 1px solid #000; display: inline-block; max-width: 100%; word-wrap: break-word; padding-bottom: 3px; }
    .sign-image { max-height: 68px; max-width: 195px; vertical-align: bottom; }

    .page-footer { position: fixed; bottom: 15mm; left: 0; width: 1080px; font-size: 19px; }
</style>
</head>
<body>
@foreach($reports as $report)
    @php $header = $report['header']; $items = $report['items']; @endphp
    <div class="sheet">
      <div class="page-content">
        <table class="top-table">
            <tr>
                <td class="logo-cell" style="width:50%; text-align:left;">
                    @if($logo)<img src="{{ $logo }}">@endif
                </td>
                <td class="address-cell" style="width:50%; text-align:right;">
                    @if($address)<img src="{{ $address }}">@endif
                </td>
            </tr>
        </table>

        <div class="title">Service Report</div>

        <table class="info-table">
            <colgroup>
                <col class="label"><col class="value"><col class="label2"><col class="value2"><col class="label3"><col class="value3">
            </colgroup>
            <tr>
                <td class="plain">Service Report No</td>
                <td class="box">{{ $header->service_report_no }}</td>
                <td class="plain" colspan="4"></td>
            </tr>
            <tr>
                <td class="plain">Complaint No</td>
                <td class="box">{{ $header->complaint_no }}</td>
                <td class="plain label-right">Date</td>
                <td class="box">{{ $header->complaint_date ? date('d/m/Y', strtotime($header->complaint_date)) : '' }}</td>
                <td class="plain label-right">Complaint Attended On</td>
                <td class="box">{{ $header->created_at ? date('d/m/Y', strtotime($header->created_at)) : '' }}</td>
            </tr>
            <tr>
                <td class="plain">Name of the Property</td>
                <td class="box" colspan="3">{{ $header->building_name }}</td>
                <td class="plain label-right">Unit No</td>
                <td class="box">{{ $header->unit_no }}</td>
            </tr>
            <tr>
                <td class="plain">Name of the Technician</td>
                <td class="box" colspan="3">{{ $header->technician }}</td>
                <td class="plain" colspan="2"></td>
            </tr>
        </table>

        <div class="section-title">Details and cost of repair of the work carried out</div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:8%;">Sl No:</th>
                    <th style="width:28%;">Description</th>
                    <th style="width:18%;">Materials</th>
                    <th style="width:10%;">Quantity</th>
                    <th style="width:18%;">Material Charge</th>
                    <th style="width:18%;">Labour Charge</th>
                </tr>
            </thead>
            <tbody>
                @php $materialTotal = 0; $labourTotal = 0; $grandTotal = 0; @endphp
                @foreach($items as $index => $item)
                    @php
                        $materialTotal += (float)$item->material_charge;
                        $labourTotal   += (float)$item->labour_charge;
                        $grandTotal    += (float)$item->total_charge;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->complaint_assign_note }}</td>
                        <td>{{ $item->inventories_name }}</td>
                        <td class="num">{{ $item->qty }}</td>
                        <td class="num">{{ numberFormat($item->material_charge) }}</td>
                        <td class="num">{{ numberFormat($item->labour_charge) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="total-table">
            <tr>
                <td class="total-label" style="width:65%;">Sub Total</td>
                <td class="total-value" style="width:17.5%;">{{ numberFormat($materialTotal) }}</td>
                <td class="total-value" style="width:17.5%;">{{ numberFormat($labourTotal) }}</td>
            </tr>
            <tr>
                <td class="total-label" style="width:65%;">Grand Total</td>
                <td class="total-value" colspan="2">{{ numberFormat($grandTotal) }}</td>
            </tr>
        </table>

        <div class="satisfactory">The above service has been carried out satisfactorily</div>

        <table class="sign-table">
            <tr>
                <td style="width:43%;">Tenant's Name : <span class="sign-underline">{{ $header->complainer_name }}</span></td>
                <td style="width:24%;">Mob/GSM : <span class="sign-underline">{{ $header->complaint_mob_no }}</span></td>
                <td style="width:33%;">
                    Signature :
                    @if($report['signature'])
                        <img class="sign-image" src="{{ $report['signature'] }}">
                    @else
                        <span class="sign-underline">&nbsp;</span>
                    @endif
                </td>
            </tr>
        </table>
      </div>
        <div class="page-footer">This is a system generated document. No signature is required</div>
    </div>
@endforeach
</body>
</html>
