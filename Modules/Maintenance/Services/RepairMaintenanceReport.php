<?php

namespace Modules\Maintenance\Services;

use Illuminate\Support\Facades\DB;

/**
 * Repair & Maintenance Report data, per building:
 * - In-house: one row per technician service report, in the month of the service report date, with the material /
 *   labour / total charges entered on the service report (complaint_service_report_inv) and the complaint behind it.
 * - Sub-contract: vendor invoice lines (maintenance invoices) in the month of the invoice date. Invoice lines that
 *   bill a service report (linked by service_report_id, or naming its SERV no.) are left out: that work is In-house.
 * PHP < 7.4 in production: no arrow functions.
 */
class RepairMaintenanceReport
{
    const EMERGENCY = 1; // complaint_enquiries.priority_status: 0 Normal, 1 High (shown as Emergency)

    /**
     * Buildings whose report month does not follow the calendar: building id => first day of the period.
     * Al Themar Bldg (429): "January" = 22 Jan to 21 Feb (user, 19/10/2026). Both sections follow it.
     */
    const PERIOD_START_DAY = [429 => 22];

    /** The service report an invoice line bills: by service_report_id, or by the SERV no. its text starts with. */
    const SR_JOIN = "LEFT JOIN LATERAL (
                SELECT csr0.id FROM complaint_service_report csr0
                WHERE csr0.id = mid.service_report_id
                   OR (mid.service_report_id IS NULL AND TRIM(mid.invoice_desc) LIKE 'SERV%'
                       AND csr0.service_report_no = split_part(TRIM(mid.invoice_desc), ' ', 1))
                ORDER BY csr0.id LIMIT 1
            ) sr ON true";

    /** First day of a building's report month: 1 (calendar month) unless set in PERIOD_START_DAY. */
    public static function startDay($buildingId)
    {
        return isset(self::PERIOD_START_DAY[(int) $buildingId]) ? self::PERIOD_START_DAY[(int) $buildingId] : 1;
    }

    /**
     * Months to export for a year: 1..12, or up to the period running today for the current year
     * (for a 22nd-to-21st building, on 19 Oct that is September).
     */
    public static function months($year, $startDay = 1)
    {
        $year  = (int) $year;
        $today = strtotime('-' . ((int) $startDay - 1) . ' days', strtotime(date('Y-m-d')));
        $nowY  = (int) date('Y', $today);
        $last  = $year === $nowY ? (int) date('n', $today) : ($year > $nowY ? 0 : 12);
        return $last > 0 ? range(1, $last) : [];
    }

    /** "22.01.2026 to 21.02.2026" for a building with its own period, or null for calendar months. */
    public static function periodLabel($year, $month, $startDay)
    {
        if ((int) $startDay === 1) {
            return null;
        }
        $from = mktime(0, 0, 0, (int) $month, (int) $startDay, (int) $year);
        $to   = mktime(0, 0, 0, (int) $month + 1, (int) $startDay - 1, (int) $year);
        return date('d.m.Y', $from) . ' to ' . date('d.m.Y', $to);
    }

    /** Buildings with a service report or a vendor invoice line in the year, by name. */
    public static function buildingsWithData($year)
    {
        list($from, $to) = self::range($year);
        return DB::select("
            SELECT b.id, b.building_name
            FROM buildings b
            WHERE EXISTS (
                SELECT 1 FROM complaint_service_report cs
                JOIN complaint_service_report_checklist csr ON csr.complaint_service_report_id = cs.id
                JOIN complaint_checklists cc ON cc.id = csr.checklist_id
                JOIN complaint_enquiries ce ON ce.id = cc.complaint_enquiries_id
                WHERE ce.building_id = b.id AND cs.created_at >= ? AND cs.created_at < ?)
            OR EXISTS (
                SELECT 1 FROM maintenance_invoice_details mid
                JOIN maintenance_invoices mi ON mi.id = mid.maintenance_invoice_id
                WHERE mid.building_id = b.id AND mi.deleted_at IS NULL
                  AND mi.maintenance_invoice_date >= ? AND mi.maintenance_invoice_date < ?)
            ORDER BY b.building_name", [$from, $to, $from, $to]);
    }

    /** In-house service reports of a building for the year, keyed by month (1..12) of the service report date. */
    public static function inHouse($buildingId, $year)
    {
        $shift = self::shift($buildingId);
        list($from, $to) = self::range($year, self::startDay($buildingId));
        $rows = DB::select("
            SELECT EXTRACT(MONTH FROM cs.created_at::date - {$shift})::int AS month, cs.id AS sr_id, cs.service_report_no,
                   cs.created_at::date AS sr_date,
                   c.complaint_no, c.complaint_date, c.priority, c.description, c.technician, c.category, c.unit_no,
                   COALESCE(ch.material, 0) AS material, COALESCE(ch.labour, 0) AS labour, COALESCE(ch.amount, 0) AS amount
            FROM complaint_service_report cs
            JOIN LATERAL (
                SELECT string_agg(DISTINCT ce.complaint_no, ', ') AS complaint_no,
                       MIN(ce.complaint_date) AS complaint_date,
                       MAX(ce.priority_status) AS priority,
                       string_agg(DISTINCT cc.checklist_desc, '; ') AS description,
                       string_agg(DISTINCT e.employee_name, ', ') AS technician,
                       string_agg(DISTINCT w.works_code, '/') AS category,
                       string_agg(DISTINCT cu.unit_no, ', ') AS unit_no,
                       MIN(ce.building_id) AS building_id
                FROM complaint_service_report_checklist csr
                JOIN complaint_checklists cc ON cc.id = csr.checklist_id
                JOIN complaint_enquiries ce ON ce.id = cc.complaint_enquiries_id
                LEFT JOIN units cu ON cu.id = ce.unit_id
                LEFT JOIN works w ON w.id = cc.work_id
                LEFT JOIN users us ON us.id = cc.sub_assigned_to
                LEFT JOIN employees e ON e.id = us.user_type_id
                WHERE csr.complaint_service_report_id = cs.id
            ) c ON true
            LEFT JOIN LATERAL (
                SELECT SUM(ci.material_charge) AS material, SUM(ci.labour_charge) AS labour, SUM(ci.total_charge) AS amount
                FROM complaint_service_report_inv ci WHERE ci.complaint_service_report_id = cs.id
            ) ch ON true
            WHERE cs.id IN (
                    SELECT csr.complaint_service_report_id FROM complaint_service_report_checklist csr
                    JOIN complaint_checklists cc ON cc.id = csr.checklist_id
                    JOIN complaint_enquiries ce ON ce.id = cc.complaint_enquiries_id
                    WHERE ce.building_id = ?)
              AND cs.created_at >= ? AND cs.created_at < ?
            ORDER BY cs.created_at, cs.id",
            [$buildingId, $from, $to]);
        return self::byMonth($rows);
    }

    /** Vendor invoice lines of a building for the year, keyed by month (1..12) of the invoice date. */
    public static function subContract($buildingId, $year)
    {
        $rows = DB::select("
            SELECT EXTRACT(MONTH FROM mi.maintenance_invoice_date - " . self::shift($buildingId) . ")::int AS month, mid.id AS line_id,
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
              AND mi.maintenance_invoice_date >= ? AND mi.maintenance_invoice_date < ?
            ORDER BY mi.maintenance_invoice_date, mid.id",
            array_merge([$buildingId], self::range($year, self::startDay($buildingId))));
        return self::byMonth($rows);
    }

    public static function complaintType($priority)
    {
        return (int) $priority === self::EMERGENCY ? 'Emergency' : 'Normal';
    }

    /**
     * [first day of the year's first period, first day of the next year's] for half-open date filters:
     * 1 Jan to 1 Jan, or for a 22nd-to-21st building 22 Jan to 22 Jan.
     */
    private static function range($year, $startDay = 1)
    {
        $d = sprintf('-01-%02d', (int) $startDay);
        return [(int) $year . $d, ((int) $year + 1) . $d];
    }

    /** Days to subtract from a date so its calendar month is its report month (0 for calendar months). */
    private static function shift($buildingId)
    {
        return self::startDay($buildingId) - 1;
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
