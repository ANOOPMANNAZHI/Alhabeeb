<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\DB;

/**
 * What every tenant contract owes on a date: the one calculation behind the
 * Tenant Aging Report and the Receivables Follow-up (a RentLedger per contract).
 * Contracts: approved (108), in active buildings, not legal cases, effective by the as-on date.
 * Receipts: rent receipts (type 0), not deleted, approved or posted (3, 6), not
 * cancelled (status 2) - the same rule as RentOutstanding / the termination screens -
 * dated on or before $receiptsUpTo.
 * Terminations: 505 done, termination date on or before the as-on date.
 * Ledgers carry unpaid lines only (consumers age unpaid lines; totals are unaffected).
 * See docs/superpowers/specs/2026-10-02-tenant-dues-ledger-design.md.
 */
class RentDues
{
    /** Optional exact-match filters => column. */
    const FILTERS = [
        'building_name'   => 'b.building_name',
        'building_no'     => 'b.building_no',
        'tenant_name'     => 't.tenant_name',
        'management_type' => 'mt.management_types_name',
    ];

    /**
     * @param  string $asOn          Y-m-d: rent that fell due on or before this date
     * @param  string $receiptsUpTo  Y-m-d: rent receipts dated on or before this date
     * @param  array  $filters       building_name, building_no, tenant_name, management_type, are;
     *                               tenant_id + include_legal (termination screens: a tenant's
     *                               contracts including legal cases, each flagged ->in_legal)
     * @return object[] keyed by contract id, each with ->ledger (RentLedger::build result)
     */
    public function forDate($asOn, $receiptsUpTo, array $filters = [])
    {
        $where = '';
        $bind = [$receiptsUpTo, $receiptsUpTo, $asOn, $asOn];   // waivers up to, receipts up to, terminations, effective
        foreach (self::FILTERS as $key => $col) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $where .= " AND $col = ?";
                $bind[] = $filters[$key];
            }
        }
        if (isset($filters['are']) && $filters['are'] !== '') {
            // the ARE the building is assigned to, as the old receivable functions matched it
            $where .= " AND EXISTS (SELECT 1 FROM employees emp
                          JOIN users us ON emp.id = us.user_type_id
                          JOIN are_buildings ab ON us.id = ab.user_id
                          JOIN preferred_buildings pb ON ab.id = pb.are_building_id
                         WHERE pb.assign_to IS NULL AND pb.building_id = b.id AND emp.employee_name = ?)";
            $bind[] = $filters['are'];
        }
        if (isset($filters['tenant_id']) && $filters['tenant_id'] !== '') {
            $where .= " AND tc.tenant_id = ?";
            $bind[] = (int) $filters['tenant_id'];
        }
        $withLegal = !empty($filters['include_legal']);
        $legalWhere = $withLegal ? '' : 'AND NOT EXISTS (SELECT 1 FROM legal l WHERE l.tenant_contract_id = tc.id)';
        $legalCol = $withLegal ? ', EXISTS (SELECT 1 FROM legal l WHERE l.tenant_contract_id = tc.id) AS in_legal' : '';

        $rows = DB::select("
            SELECT tc.id, tc.tenant_contract_no AS contract_no, t.tenant_name,
                   b.building_name, b.building_no, u.unit_no AS unit_code,
                   tc.tenant_contract_effective_date::date AS eff,
                   tc.tenant_contract_start_date::date      AS start,
                   tc.tenant_contract_valid_to_date::date   AS vto,
                   tc.tenant_contract_rent                  AS rent,
                   tc.tenant_contract_value                 AS value,
                   CASE WHEN tc.pdc_check = 1 THEN 'Full' WHEN tc.pdc_check = 2 THEN 'Partial' ELSE '' END AS pdc,
                   ter.term_date,
                   r.received, r.paid_till, r.last_received,
                   (SELECT sum(rl.receipts_generation_amt) FROM receipts_generation rl
                     WHERE rl.tenant_contract_id = tc.id AND rl.receipts_generation_type = '0' AND rl.deleted_at IS NULL
                       AND rl.receipts_generation_approval_status IN (3, 6) AND rl.receipts_generation_status <> 2
                       AND rl.receipts_generation_receipt_date::date = r.last_received::date) AS last_amount,
                   (SELECT coalesce(sum(lw.amount), 0) FROM legal_waivers lw
                     WHERE lw.tenant_contract_id = tc.id AND lw.cancelled_at IS NULL
                       AND lw.approved_at::date <= ?::date) AS waived
                   $legalCol
              FROM tenant_contracts tc
              JOIN buildings b ON b.id = tc.building_id AND b.building_status = '1'
              LEFT JOIN tenant t ON t.id = tc.tenant_id
              LEFT JOIN units u ON u.id = tc.unit_id
              LEFT JOIN management_types mt ON mt.id = b.management_id
              LEFT JOIN (SELECT tenant_contract_id,
                           sum(receipts_generation_amt)          AS received,
                           max(receipts_generation_eff_to)        AS paid_till,
                           max(receipts_generation_receipt_date)  AS last_received
                      FROM receipts_generation
                     WHERE receipts_generation_type = '0' AND deleted_at IS NULL
                       AND receipts_generation_approval_status IN (3, 6) AND receipts_generation_status <> 2
                       AND receipts_generation_receipt_date::date <= ?::date
                     GROUP BY tenant_contract_id) r ON r.tenant_contract_id = tc.id
              LEFT JOIN (SELECT contract_id, max(termination_date)::date AS term_date
                           FROM termination
                          WHERE work_flow_processes_code = '505' AND termination_date::date <= ?::date
                          GROUP BY contract_id) ter ON ter.contract_id = tc.id
             WHERE tc.work_flow_processes_code = 108
               AND tc.tenant_contract_effective_date::date <= ?::date
               $legalWhere
               $where
        ", $bind);

        $out = [];
        foreach ($rows as $r) {
            // a legal settlement waiver settles rent like a receipt (oldest lines first) but is kept apart
            // from 'received' (legal plan phase 4)
            $r->waived = round((float) $r->waived, 3);
            $r->ledger = RentLedger::build($r, $asOn, (float) $r->received + $r->waived);
            $r->ledger['received'] = round((float) $r->received, 3);   // money only; the balance is net of the waiver
            $r->ledger['waived'] = $r->waived;
            // keep unpaid lines only: consumers age unpaid lines, totals are unaffected
            $r->ledger['lines'] = array_values(array_filter($r->ledger['lines'], function ($l) {
                return $l['unpaid'] > 0;
            }));
            $out[(int) $r->id] = $r;
        }
        return $out;
    }

    /**
     * Report-only columns for the Rent Receivable V2 report, kept out of forDate()
     * so the Aging report and Follow-up stay lean: payment mode, tenant contact,
     * management type, the building's ARE(s) and the closed-cheque count (same
     * count as the old receivable functions).
     *
     * @param  int[] $ids contract ids
     * @return object[] keyed by contract id
     */
    public function details(array $ids)
    {
        $out = [];
        foreach (array_chunk(array_values(array_unique(array_map('intval', $ids))), 1000) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $rows = DB::select("
                SELECT tc.id,
                       CASE tc.tenant_contract_payment_type::text
                            WHEN '1' THEN 'Monthly' WHEN '2' THEN 'Bi-Monthly' WHEN '3' THEN 'Quaterly'
                            WHEN '4' THEN 'Half Yearly' WHEN '5' THEN 'Yearly' ELSE '' END AS paymentmode,
                       t.tenant_contact_person AS contact_person,
                       t.tenant_contact_no     AS contact_no,
                       mt.management_types_name AS management_type,
                       (SELECT NULLIF(count(rg.id), 0) FROM receipts_generation rg
                          JOIN pdc p ON p.tenant_contract_id = rg.tenant_contract_id
                         WHERE rg.tenant_contract_id = tc.id AND p.pdc_cancel_reason = '1'
                           AND rg.receipts_generation_payment_method = '1') AS pdc_closed,
                       (SELECT string_agg(DISTINCT emp.employee_name, ', ') FROM employees emp
                          JOIN users us ON emp.id = us.user_type_id
                          JOIN are_buildings ab ON us.id = ab.user_id
                          JOIN preferred_buildings pb ON ab.id = pb.are_building_id
                         WHERE pb.assign_to IS NULL AND pb.building_id = tc.building_id) AS employee_name
                  FROM tenant_contracts tc
                  LEFT JOIN tenant t ON t.id = tc.tenant_id
                  LEFT JOIN buildings b ON b.id = tc.building_id
                  LEFT JOIN management_types mt ON mt.id = b.management_id
                 WHERE tc.id IN ($in)
            ", $chunk);
            foreach ($rows as $r) {
                $r->pdc_closed = $r->pdc_closed === null ? null : (int) $r->pdc_closed;
                $out[(int) $r->id] = $r;
            }
        }
        return $out;
    }
}
