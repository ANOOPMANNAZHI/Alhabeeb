<?php

namespace Modules\Maintenance\Services;

use Illuminate\Support\Facades\DB;

/**
 * Repair & Maintenance Report data: per building and invoice month, the In-house lines (maintenance invoice
 * lines from a technician service report, with the complaint behind it) and the Sub-contract lines (invoice
 * lines with no service report). Same invoice lines as Maintenance Invoice Report v2 / Expense Details v2, so a
 * building's monthly total matches; lines whose service report link was lost are recovered by SERV no. (SR_JOIN).
 * PHP < 7.4 in production: no arrow functions.
 */
class RepairMaintenanceReport
{
    const EMERGENCY = 1; // complaint_enquiries.priority_status: 0 Normal, 1 High (shown as Emergency)

    /**
     * The line's service report: mid.service_report_id, or, when an invoice edit lost that link, the SERV no. still
     * in the line text (invoice_desc "SERV41650 ..."). Such lines are In-house work, not sub-contract.
     */
    const SR_JOIN = "LEFT JOIN LATERAL (
                SELECT csr0.id FROM complaint_service_report csr0
                WHERE csr0.id = mid.service_report_id
                   OR (mid.service_report_id IS NULL AND TRIM(mid.invoice_desc) LIKE 'SERV%'
                       AND csr0.service_report_no = split_part(TRIM(mid.invoice_desc), ' ', 1))
                ORDER BY csr0.id LIMIT 1
            ) sr ON true";

    /** Months to export for a year: 1..12, or up to the current month for the current year. */
    public static function months($year)
    {
        $year = (int) $year;
        $last = $year === (int) date('Y') ? (int) date('n') : ($year > (int) date('Y') ? 0 : 12);
        return $last > 0 ? range(1, $last) : [];
    }

    /** Buildings with at least one maintenance invoice line dated in the year, by name. */
    public static function buildingsWithData($year)
    {
        return DB::select("
            SELECT b.id, b.building_name
            FROM buildings b
            WHERE EXISTS (
                SELECT 1 FROM maintenance_invoice_details mid
                JOIN maintenance_invoices mi ON mi.id = mid.maintenance_invoice_id
                WHERE mid.building_id = b.id AND mi.deleted_at IS NULL
                  AND mi.maintenance_invoice_date BETWEEN ? AND ?)
            ORDER BY b.building_name", [$year . '-01-01', $year . '-12-31']);
    }

    /** In-house lines of a building for the year, keyed by month (1..12). */
    public static function inHouse($buildingId, $year)
    {
        $rows = DB::select("
            SELECT EXTRACT(MONTH FROM mi.maintenance_invoice_date)::int AS month, mid.id AS line_id,
                   c.complaint_no, c.complaint_date, c.priority, c.description, c.technician, c.category,
                   COALESCE(u.unit_no, c.unit_no) AS unit_no,
                   COALESCE(CAST(NULLIF(TRIM(mid.material_charge), '') AS numeric), 0) AS material,
                   COALESCE(CAST(NULLIF(TRIM(mid.labour_charge), '') AS numeric), 0) AS labour,
                   COALESCE(CAST(NULLIF(TRIM(mid.debit_amt), '') AS numeric), 0) AS amount
            FROM maintenance_invoices mi
            JOIN maintenance_invoice_details mid ON mid.maintenance_invoice_id = mi.id
            " . self::SR_JOIN . "
            LEFT JOIN units u ON u.id = mid.unit_id
            LEFT JOIN LATERAL (
                SELECT string_agg(DISTINCT ce.complaint_no, ', ') AS complaint_no,
                       MIN(ce.complaint_date) AS complaint_date,
                       MAX(ce.priority_status) AS priority,
                       string_agg(DISTINCT cc.checklist_desc, '; ') AS description,
                       string_agg(DISTINCT e.employee_name, ', ') AS technician,
                       string_agg(DISTINCT w.works_code, '/') AS category,
                       MIN(cu.unit_no) AS unit_no
                FROM complaint_service_report_checklist csr
                JOIN complaint_checklists cc ON cc.id = csr.checklist_id
                JOIN complaint_enquiries ce ON ce.id = cc.complaint_enquiries_id
                LEFT JOIN units cu ON cu.id = ce.unit_id
                LEFT JOIN works w ON w.id = cc.work_id
                LEFT JOIN users us ON us.id = cc.sub_assigned_to
                LEFT JOIN employees e ON e.id = us.user_type_id
                WHERE csr.complaint_service_report_id = sr.id
            ) c ON true
            WHERE mi.deleted_at IS NULL AND sr.id IS NOT NULL AND mid.building_id = ?
              AND mi.maintenance_invoice_date BETWEEN ? AND ?
            ORDER BY mi.maintenance_invoice_date, c.complaint_date, mid.id",
            [$buildingId, $year . '-01-01', $year . '-12-31']);
        return self::byMonth($rows);
    }

    /** Sub-contract lines of a building for the year, keyed by month (1..12). */
    public static function subContract($buildingId, $year)
    {
        $rows = DB::select("
            SELECT EXTRACT(MONTH FROM mi.maintenance_invoice_date)::int AS month, mid.id AS line_id,
                   mi.maintenance_invoice_date AS invoice_date, mi.maintenance_invoice_no AS invoice_no,
                   v.vendor_name, COALESCE(NULLIF(TRIM(mi.maintenance_invoice_desc), ''), mid.description) AS description,
                   COALESCE(ac.acc_code_desc, mid.description) AS category, u.unit_no,
                   COALESCE(CAST(NULLIF(TRIM(mid.debit_amt), '') AS numeric), 0) AS amount
            FROM maintenance_invoices mi
            JOIN maintenance_invoice_details mid ON mid.maintenance_invoice_id = mi.id
            " . self::SR_JOIN . "
            LEFT JOIN vendors v ON v.id = mi.vendor_id
            LEFT JOIN acc_codes ac ON ac.id = mid.ac_codes_id
            LEFT JOIN units u ON u.id = mid.unit_id
            WHERE mi.deleted_at IS NULL AND sr.id IS NULL AND mid.building_id = ?
              AND mi.maintenance_invoice_date BETWEEN ? AND ?
            ORDER BY mi.maintenance_invoice_date, mid.id",
            [$buildingId, $year . '-01-01', $year . '-12-31']);
        return self::byMonth($rows);
    }

    public static function complaintType($priority)
    {
        return (int) $priority === self::EMERGENCY ? 'Emergency' : 'Normal';
    }

    private static function byMonth(array $rows)
    {
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->month][] = $r;
        }
        return $out;
    }
}
