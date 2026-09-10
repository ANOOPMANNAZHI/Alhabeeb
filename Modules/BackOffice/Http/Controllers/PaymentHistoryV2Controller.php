<?php

namespace Modules\BackOffice\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Unit;
use Modules\Sales\Entities\Tenant;
use Modules\BackOffice\Exports\PaymentHistoryV2Export;

/**
 * Tenant's Payment History - Agreement Wise (V2). PLM Module -> Reports.
 *
 * Same filters as the original showPaymentHistoryReport (building, unit,
 * tenant, download type), but rendered from a native query into Blade instead
 * of going through JasperReports, so the output can group by contract with a
 * subtotal per contract and a grand total at the end.
 *
 * The original query lives in
 *   vendor/cossou/jasperphp/examples/tenant_payment_history.jrxml
 * and matched on building_name / unit_no / tenant_name. This filters on the IDs
 * the form already posts, so two tenants sharing a name cannot bleed into each
 * other's report.
 */
class PaymentHistoryV2Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_payment_history_v2');
    }

    public function index()
    {
        $buildings = Building::active()
            ->whereHas('tenantContract', function ($query) {
                $query->where('tenant_contract_status', 1)->whereHas('rentReceiptGenerationList');
            })
            ->orderBy('building_name', 'asc')
            ->get();

        return view('backoffice::Reports.payment_history_report_v2', compact('buildings'));
    }

    /**
     * Build the report and hand back a PDF or an Excel file.
     */
    public function download(Request $request)
    {
        $this->validate($request, [
            'building_id'   => 'required',
            'u_id'          => 'required',
            't_id'          => 'required',
            'download_type' => 'required|in:pdf,excel',
        ]);

        $buildingId = $request->input('building_id');
        $unitId     = $request->input('u_id');
        $tenantId   = $request->input('t_id');

        $rows = $this->reportRows($buildingId, $unitId, $tenantId);

        // One block per contract, in the order the rows came back.
        $groups       = $this->groupByContract($rows);
        $grandTotal   = array_sum(array_column($groups, 'subtotal'));
        $receiptCount = array_sum(array_column($groups, 'count'));

        $building = Building::find($buildingId);
        $unit     = Unit::find($unitId);
        $tenant   = Tenant::find($tenantId);

        $data = [
            'groups'        => $groups,
            'grandTotal'    => $grandTotal,
            'receiptCount'  => $receiptCount,
            'contractCount' => count($groups),
            'buildingName'  => $building ? $building->building_name : '',
            'unitNo'        => $unit ? $unit->unit_no : '',
            'tenantName'    => $tenant ? $tenant->tenant_name : '',
            'user'          => Auth::user()->username,
            'generatedAt'   => date('d/m/Y H:i'),
        ];

        if ($request->input('download_type') === 'pdf') {
            $logoPath = public_path('img/logo_pdf.jpg');
            if (is_file($logoPath)) {
                $data['logo'] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
            }

            $pdf = \PDF::loadView('backoffice::Reports.payment_history_report_v2_pdf', $data)
                       ->setPaper('a4', 'landscape');

            return $pdf->download($this->fileName($data, 'pdf'));
        }

        return \Excel::download(new PaymentHistoryV2Export($data), $this->fileName($data, 'xlsx'));
    }

    /**
     * Posted rent receipts for one building/unit/tenant.
     * type 0 = tenant/rent receipt, status 3 = posted - both as the original
     * Jasper report had them.
     */
    private function reportRows($buildingId, $unitId, $tenantId)
    {
        return DB::select("
            SELECT tc.tenant_contract_no,
                   tc.tenant_contract_start_date,
                   tc.tenant_contract_valid_to_date,
                   tc.tenant_contract_rent,
                   rg.receipts_generation_receipt_no   AS receipt_no,
                   rg.receipts_generation_receipt_date AS receipt_date,
                   rg.receipts_generation_eff_from     AS eff_from,
                   rg.receipts_generation_eff_to       AS eff_to,
                   rg.receipts_generation_amt          AS amount,
                   rg.receipts_generation_cheque_no    AS cheque_no,
                   CASE rg.receipts_generation_payment_method
                        WHEN 1 THEN 'Cheque'
                        WHEN 3 THEN 'Bank Transfer'
                        ELSE 'Cash'
                   END AS payment_method
            FROM buildings b
            LEFT JOIN units u             ON b.id  = u.building_id
            LEFT JOIN tenant_contracts tc ON u.id  = tc.unit_id
            LEFT JOIN receipts_generation rg ON tc.id = rg.tenant_contract_id
            LEFT JOIN tenant t            ON t.id  = tc.tenant_id
            WHERE b.id  = ?
              AND u.id  = ?
              AND t.id  = ?
              AND rg.receipts_generation_type   = '0'
              AND rg.receipts_generation_status = '3'
            ORDER BY tc.tenant_contract_no, rg.receipts_generation_receipt_date, rg.id
        ", [$buildingId, $unitId, $tenantId]);
    }

    /**
     * Collapse flat rows into one entry per contract:
     *   contract_no, start, end, rent, rows[], count, subtotal
     */
    private function groupByContract(array $rows)
    {
        $groups = [];

        foreach ($rows as $row) {
            $key = $row->tenant_contract_no;

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'contract_no' => $row->tenant_contract_no,
                    'start_date'  => $row->tenant_contract_start_date,
                    'end_date'    => $row->tenant_contract_valid_to_date,
                    'rent'        => $row->tenant_contract_rent,
                    'rows'        => [],
                    'count'       => 0,
                    'subtotal'    => 0,
                ];
            }

            $groups[$key]['rows'][] = $row;
            $groups[$key]['count']++;
            $groups[$key]['subtotal'] += (float) $row->amount;
        }

        return array_values($groups);
    }

    /** e.g. payment_history_v2_Radhiya_Bldg_S_01.pdf */
    private function fileName(array $data, $extension)
    {
        $parts = array_filter([$data['buildingName'], $data['unitNo']]);
        $slug  = preg_replace('/[^A-Za-z0-9_\-]/', '_', implode('_', $parts));

        return 'payment_history_v2_' . trim($slug, '_') . '.' . $extension;
    }
}
