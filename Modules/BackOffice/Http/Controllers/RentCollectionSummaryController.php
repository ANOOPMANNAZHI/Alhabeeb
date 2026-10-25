<?php

namespace Modules\BackOffice\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Masters\Entities\ManagementType;

/**
 * Building-wise Rent Collection Summary (PLM Module -> Reports).
 *
 * One screen: month/year filter (defaults to the current month) and a table of
 * every building that had rent activity in that month, with the rent collected
 * (rent receipts by receipt date) and the pending amount (monthly rent of the
 * contracts active in that month minus the collection, floored at zero).
 * Optional building / building type (management type: Comprehensive, Normal, ...) / ARE filters.
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
        $buildingName   = trim((string) $request->input('building_name'));
        $managementId   = (int) $request->input('management_id');
        $areId          = (int) $request->input('are_id');
        $rows = $this->summaryRows($month, $year, $buildingName, $managementId, $areId);
        $monthLabel = Carbon::createFromDate($year, $month, 1)->format('M Y');

        if ($request->ajax()) {
            return view('backoffice::Reports.rent_collection_summary_ajax', compact('rows', 'monthLabel'));
        }

        $managementTypes = ManagementType::active()->orderBy('management_types_name')->get(['id', 'management_types_name']);
        $ares = DB::select("
            SELECT DISTINCT emp.id, emp.employee_name
            FROM employees emp
            JOIN users us ON us.user_type_id = emp.id
            JOIN are_buildings ab ON ab.user_id = us.id
            JOIN preferred_buildings pb ON pb.are_building_id = ab.id
            WHERE pb.assign_to IS NULL
            ORDER BY emp.employee_name");

        return view('backoffice::Reports.rent_collection_summary', compact(
            'rows', 'monthLabel', 'month', 'year', 'buildingName',
            'managementTypes', 'ares', 'managementId', 'areId'
        ));
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
     * are_names: the building's current ARE(s) (preferred_buildings with no assign_to),
     * the same mapping the Tenancy Details report uses.
     */
    private function summaryRows($month, $year, $buildingName = '', $managementId = 0, $areId = 0)
    {
        $anchor = sprintf('%04d-%02d-01', $year, $month);
        $bindings = [$anchor, $anchor];

        $filters = '';
        $filterBindings = [];

        // Free-text building search (name or code), same convention as the
        // Landlord Contract / Landlord Invoice v2 list filters.
        if ($buildingName !== '') {
            $filters .= " AND (b.building_name ILIKE ? OR b.building_code ILIKE ?)";
            $like = '%' . $buildingName . '%';
            $filterBindings[] = $like;
            $filterBindings[] = $like;
        }
        if ($managementId > 0) {
            $filters .= " AND b.management_id = ?";
            $filterBindings[] = $managementId;
        }
        if ($areId > 0) {
            $filters .= " AND EXISTS (SELECT 1 FROM current_are ca WHERE ca.building_id = b.id AND ca.employee_id = ?)";
            $filterBindings[] = $areId;
        }

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
            ),
            current_are AS (
                SELECT DISTINCT pb.building_id, emp.id AS employee_id, emp.employee_name
                FROM employees emp
                JOIN users us ON us.user_type_id = emp.id
                JOIN are_buildings ab ON ab.user_id = us.id
                JOIN preferred_buildings pb ON pb.are_building_id = ab.id
                WHERE pb.assign_to IS NULL
            )
            SELECT b.id, b.building_name, b.building_code,
                   mt.management_types_name,
                   (SELECT STRING_AGG(ca.employee_name, ', ' ORDER BY ca.employee_name)
                      FROM current_are ca WHERE ca.building_id = b.id) AS are_names,
                   COALESCE(c.collected, 0)     AS collected,
                   COALESCE(e.expected_rent, 0) AS expected_rent,
                   GREATEST(COALESCE(e.expected_rent, 0) - COALESCE(c.collected, 0), 0) AS pending
            FROM buildings b
            LEFT JOIN management_types mt ON mt.id = b.management_id
            LEFT JOIN collected c ON c.building_id = b.id
            LEFT JOIN expected  e ON e.building_id = b.id
            WHERE (c.building_id IS NOT NULL OR e.building_id IS NOT NULL)
                  {$filters}
            ORDER BY b.building_name";

        return DB::select($sql, array_merge($bindings, $filterBindings));
    }
}
