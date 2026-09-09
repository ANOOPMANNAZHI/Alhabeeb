<?php

namespace Modules\BackOffice\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

/**
 * Building-wise Rent Collection Summary (PLM Module -> Reports).
 *
 * One screen: month/year filter (defaults to the current month) and a table of
 * every building that had rent activity in that month, with the rent collected
 * (rent receipts by receipt date) and the pending amount (monthly rent of the
 * contracts active in that month minus the collection, floored at zero).
 * Display only — no PDF/Excel.
 */
class RentCollectionSummaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_rent_collection_summary');
    }

    public function index(Request $request)
    {
        list($month, $year) = $this->monthYear($request);
        $rows = $this->summaryRows($month, $year);
        $monthLabel = Carbon::createFromDate($year, $month, 1)->format('M Y');

        if ($request->ajax()) {
            return view('backoffice::Reports.rent_collection_summary_ajax', compact('rows', 'monthLabel'));
        }

        return view('backoffice::Reports.rent_collection_summary', compact('rows', 'monthLabel', 'month', 'year'));
    }

    /** @return array [int $month, int $year] — falls back to the current month/year. */
    private function monthYear(Request $request)
    {
        $month = (int) $request->input('month', date('n'));
        $year  = (int) $request->input('year', date('Y'));
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }
        return [$month, $year];
    }

    /**
     * One row per building with activity in the month.
     * collected: rent receipts (type 0), not cancelled, not deleted, receipt date within the month.
     * expected_rent: SUM(tenant_contract_rent) of active contracts overlapping the month.
     * pending: GREATEST(expected_rent - collected, 0).
     */
    private function summaryRows($month, $year)
    {
        $anchor = sprintf('%04d-%02d-01', $year, $month);

        $sql = "
            WITH month_bounds AS (
                SELECT DATE_TRUNC('month', ?::date)::date AS m_start,
                       (DATE_TRUNC('month', ?::date) + INTERVAL '1 month - 1 day')::date AS m_end
            ),
            collected AS (
                SELECT un.building_id, SUM(rg.receipts_generation_amt) AS collected
                FROM receipts_generation rg
                JOIN tenant_contracts tc ON tc.id = rg.tenant_contract_id
                JOIN units un ON un.id = tc.unit_id, month_bounds mb
                WHERE rg.receipts_generation_type = 0
                  AND rg.receipts_generation_status <> 2
                  AND rg.deleted_at IS NULL
                  AND rg.receipts_generation_receipt_date::date BETWEEN mb.m_start AND mb.m_end
                GROUP BY un.building_id
            ),
            expected AS (
                SELECT un.building_id, SUM(COALESCE(tc.tenant_contract_rent, 0)) AS expected_rent
                FROM tenant_contracts tc
                JOIN units un ON un.id = tc.unit_id, month_bounds mb
                WHERE tc.tenant_contract_status = 1
                  AND tc.tenant_contract_start_date::date <= mb.m_end
                  AND (tc.tenant_contract_valid_to_date IS NULL OR tc.tenant_contract_valid_to_date::date >= mb.m_start)
                GROUP BY un.building_id
            )
            SELECT b.id, b.building_name, b.building_code,
                   COALESCE(c.collected, 0)     AS collected,
                   COALESCE(e.expected_rent, 0) AS expected_rent,
                   GREATEST(COALESCE(e.expected_rent, 0) - COALESCE(c.collected, 0), 0) AS pending
            FROM buildings b
            LEFT JOIN collected c ON c.building_id = b.id
            LEFT JOIN expected  e ON e.building_id = b.id
            WHERE c.building_id IS NOT NULL OR e.building_id IS NOT NULL
            ORDER BY b.building_name";

        return DB::select($sql, [$anchor, $anchor]);
    }
}
