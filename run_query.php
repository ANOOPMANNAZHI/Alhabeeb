<?php
error_reporting(0);
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// ── CONFIG ──────────────────────────────────────────
$startDate = '2025-01-01';
$endDate   = '2025-01-31';

// Get building
$building = DB::table('buildings')
    ->where('building_name', 'ILIKE', '%falaj%')
    ->orWhere('building_name', 'ILIKE', '%1323%')
    ->first();

if (!$building) {
    echo "Building not found\n";
    exit(1);
}
echo "Building: " . $building->building_name . " (ID: " . $building->id . ")\n\n";

// ── MAIN UNITS QUERY ────────────────────────────────
echo "=== MAIN UNITS (Jan 2025) ===\n";
$units = DB::select("
    SELECT
        u.unit_no,
        ut.unit_types_name AS unit_type,
        COALESCE(t.tenant_name, 'VACANT') AS tenant_name,
        tc.tenant_contract_start_date AS contract_start,
        tc.tenant_contract_valid_to_date AS contract_end,
        (
            SELECT rg2.receipts_generation_eff_to
            FROM receipts_generation rg2
            WHERE rg2.tenant_contract_id = tc.id
              AND rg2.receipts_generation_eff_from::date <= ?::date
              AND rg2.receipts_generation_status != 2
              AND rg2.deleted_at IS NULL
            ORDER BY rg2.receipts_generation_eff_from DESC NULLS LAST
            LIMIT 1
        ) AS rent_recd_upto,
        COALESCE(tc.tenant_contract_rent, 0) AS rent_per_month,
        COALESCE(SUM(rg.receipts_generation_amt) FILTER (
            WHERE DATE_TRUNC('month', rg.receipts_generation_eff_from::date) = DATE_TRUNC('month', ?::date)
              AND rg.receipts_generation_status != 2
              AND rg.deleted_at IS NULL
        ), 0) AS collection_amount,
        COALESCE(tc.tenant_contract_rent, 0) AS income_amount,
        0 AS outstanding_amount
    FROM units u
    LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
    LEFT JOIN (
        SELECT DISTINCT ON (unit_id)
            id, unit_id, tenant_id, tenant_contract_rent,
            tenant_contract_start_date, tenant_contract_valid_to_date
        FROM tenant_contracts
        WHERE tenant_contract_start_date <= ?::date
          AND (tenant_contract_valid_to_date >= ?::date OR tenant_contract_valid_to_date IS NULL)
        ORDER BY unit_id, tenant_contract_start_date DESC
    ) tc ON tc.unit_id = u.id
    LEFT JOIN tenant t ON t.id = tc.tenant_id
    LEFT JOIN receipts_generation rg ON rg.tenant_contract_id = tc.id
    LEFT JOIN buildings b ON b.id = u.building_id
    WHERE b.id = ? AND u.unit_status = 1
    GROUP BY u.unit_no, ut.unit_types_name, t.tenant_name,
             tc.id, tc.tenant_contract_rent,
             tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date
    ORDER BY u.unit_no
", [$endDate, $startDate, $endDate, $startDate, $building->id]);

// PHP 30/360 outstanding calculation
foreach ($units as &$unit) {
    $rent  = (float)($unit->rent_per_month ?? 0);
    $effTo = !empty($unit->rent_recd_upto) ? $unit->rent_recd_upto : null;

    if ($rent <= 0) { $unit->outstanding_amount = 0; continue; }
    if (empty($effTo)) { $unit->outstanding_amount = $rent; continue; }

    $effToTs  = strtotime($effTo);
    $reportTs = strtotime($endDate);

    if ($effToTs >= $reportTs) { $unit->outstanding_amount = 0; continue; }

    $effToYear  = (int)date('Y', $effToTs);
    $effToMonth = (int)date('n', $effToTs);
    $effToDay   = (int)date('j', $effToTs);
    $effToDay30 = ($effToDay >= (int)date('t', $effToTs)) ? 30 : min($effToDay, 30);
    $reportYear  = (int)date('Y', $reportTs);
    $reportMonth = (int)date('n', $reportTs);
    $days = ($reportYear - $effToYear) * 360 + ($reportMonth - $effToMonth) * 30 + (30 - $effToDay30);
    $unit->outstanding_amount = $days > 0 ? round($days * $rent / 30, 2) : 0;
}
unset($unit);

printf("%-6s %-6s %-35s %-12s %-12s %-12s %10s %12s %10s %12s\n",
    'UNIT', 'TYPE', 'TENANT NAME', 'CONT.START', 'CONT.END', 'RENT RECD', 'RENT/MON', 'COLLECTION', 'INCOME', 'OUTSTANDING');
echo str_repeat('-', 125) . "\n";

$tRent = $tColl = $tInc = $tOut = 0;
foreach ($units as $u) {
    $cs = $u->contract_start ? date('d.m.Y', strtotime($u->contract_start)) : '';
    $ce = $u->contract_end   ? date('d.m.Y', strtotime($u->contract_end))   : '';
    $rr = $u->rent_recd_upto ? date('d.m.Y', strtotime($u->rent_recd_upto)) : '';
    printf("%-6s %-6s %-35s %-12s %-12s %-12s %10.2f %12.2f %10.2f %12.2f\n",
        $u->unit_no, $u->unit_type, $u->tenant_name,
        $cs, $ce, $rr,
        $u->rent_per_month, $u->collection_amount, $u->income_amount, $u->outstanding_amount);
    $tRent += $u->rent_per_month;
    $tColl += $u->collection_amount;
    $tInc  += $u->income_amount;
    $tOut  += $u->outstanding_amount;
}
echo str_repeat('-', 125) . "\n";
printf("%-6s %-6s %-35s %-12s %-12s %-12s %10.2f %12.2f %10.2f %12.2f\n",
    '', '', 'TOTAL', '', '', '', $tRent, $tColl, $tInc, $tOut);

// ── OLD OUTSTANDING QUERY ───────────────────────────
echo "\n=== OLD OUTSTANDING (units vacant in Jan 2025 with previous tenant) ===\n";
$oldRows = DB::select("
    SELECT
        u.unit_no,
        ut.unit_types_name AS unit_type,
        t.tenant_name,
        tc.tenant_contract_start_date AS contract_start,
        tc.tenant_contract_valid_to_date AS contract_end,
        (
            SELECT rg2.receipts_generation_eff_to
            FROM receipts_generation rg2
            WHERE rg2.tenant_contract_id = tc.id
              AND rg2.receipts_generation_eff_from::date <= ?::date
              AND rg2.receipts_generation_status != 2
              AND rg2.deleted_at IS NULL
            ORDER BY rg2.receipts_generation_eff_from DESC NULLS LAST
            LIMIT 1
        ) AS rent_recd_upto,
        COALESCE(tc.tenant_contract_rent, 0) AS rent_per_month,
        0 AS outstanding_amount
    FROM units u
    LEFT JOIN unit_types ut ON ut.id = u.unit_type_id
    LEFT JOIN (
        SELECT DISTINCT ON (unit_id)
            id, unit_id, tenant_id, tenant_contract_rent,
            tenant_contract_start_date, tenant_contract_valid_to_date
        FROM tenant_contracts
        WHERE tenant_contract_valid_to_date IS NOT NULL
          AND tenant_contract_valid_to_date < ?::date
        ORDER BY unit_id, tenant_contract_valid_to_date DESC NULLS LAST
    ) tc ON tc.unit_id = u.id
    LEFT JOIN tenant t ON t.id = tc.tenant_id
    LEFT JOIN buildings b ON b.id = u.building_id
    WHERE b.id = ?
      AND u.unit_status = 1
      AND tc.id IS NOT NULL
      AND NOT EXISTS (
          SELECT 1 FROM tenant_contracts tc2
          WHERE tc2.unit_id = u.id
            AND tc2.tenant_contract_start_date <= ?::date
            AND (tc2.tenant_contract_valid_to_date >= ?::date OR tc2.tenant_contract_valid_to_date IS NULL)
      )
    ORDER BY u.unit_no
", [$endDate, $startDate, $building->id, $endDate, $startDate]);

echo "Raw rows from query: " . count($oldRows) . "\n\n";

// PHP calculation for old outstanding
foreach ($oldRows as &$unit) {
    $rent          = (float)($unit->rent_per_month ?? 0);
    $effTo         = $unit->rent_recd_upto ?? null;
    $contractEnd   = $unit->contract_end ?? null;
    $contractStart = $unit->contract_start ?? null;

    if ($rent <= 0) { $unit->outstanding_amount = 0; continue; }

    if (!empty($effTo) && !empty($contractEnd)) {
        if (strtotime($effTo) > strtotime($contractEnd)) {
            $effTo = $contractEnd;
            $unit->rent_recd_upto = $contractEnd;
        }
    }

    if (empty($effTo) && !empty($contractStart)) {
        $effTo = date('Y-m-d', strtotime($contractStart) - 86400);
    }

    $ceilingDate = !empty($contractEnd) ? $contractEnd : $endDate;

    if (empty($effTo)) { $unit->outstanding_amount = 0; continue; }

    $effToTs   = strtotime($effTo);
    $ceilingTs = strtotime($ceilingDate);

    if ($effToTs >= $ceilingTs) { $unit->outstanding_amount = 0; continue; }

    $effToYear  = (int)date('Y', $effToTs);
    $effToMonth = (int)date('n', $effToTs);
    $effToDay   = (int)date('j', $effToTs);
    $effToDay30 = ($effToDay >= (int)date('t', $effToTs)) ? 30 : min($effToDay, 30);

    $ceilingYear  = (int)date('Y', $ceilingTs);
    $ceilingMonth = (int)date('n', $ceilingTs);
    $ceilingDay   = (int)date('j', $ceilingTs);
    $ceilingDay30 = ($ceilingDay >= (int)date('t', $ceilingTs)) ? 30 : min($ceilingDay, 30);

    $days = ($ceilingYear - $effToYear) * 360
          + ($ceilingMonth - $effToMonth) * 30
          + ($ceilingDay30 - $effToDay30);

    $unit->outstanding_amount = $days > 0 ? round($days * $rent / 30, 2) : 0;
}
unset($unit);

if (empty($oldRows)) {
    echo "No old outstanding units found.\n";
} else {
    printf("%-6s %-6s %-30s %-12s %-12s %-12s %10s %12s\n",
        'UNIT', 'TYPE', 'TENANT NAME', 'CONT.START', 'CONT.END', 'RENT RECD', 'RENT/MON', 'OUTSTANDING');
    echo str_repeat('-', 105) . "\n";
    $oTotalRent = $oTotalOut = 0;
    foreach ($oldRows as $u) {
        $cs = $u->contract_start ? date('d.m.Y', strtotime($u->contract_start)) : '';
        $ce = $u->contract_end   ? date('d.m.Y', strtotime($u->contract_end))   : '';
        $rr = $u->rent_recd_upto ? date('d.m.Y', strtotime($u->rent_recd_upto)) : '(no receipts)';
        printf("%-6s %-6s %-30s %-12s %-12s %-12s %10.2f %12.2f\n",
            $u->unit_no, $u->unit_type, $u->tenant_name,
            $cs, $ce, $rr,
            $u->rent_per_month, $u->outstanding_amount);
        $oTotalRent += $u->rent_per_month;
        $oTotalOut  += $u->outstanding_amount;
    }
    echo str_repeat('-', 105) . "\n";
    printf("%-6s %-6s %-30s %-12s %-12s %-12s %10.2f %12.2f\n",
        '', '', 'TOTAL OLD OUTSTANDING', '', '', '', $oTotalRent, $oTotalOut);
    printf("GRAND TOTAL OUTSTANDING: %.2f\n", $tOut + $oTotalOut);
}
