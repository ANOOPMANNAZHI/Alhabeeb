<?php
error_reporting(E_ALL);
$pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=plms_live', 'postgres', '123456');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$startDate = '2025-11-01';
$endDate   = '2025-11-30';
$bid       = 430;

$building = $pdo->query("SELECT building_name FROM buildings WHERE id = $bid")->fetch(PDO::FETCH_OBJ);
echo "Building: {$building->building_name} (ID: $bid)\n";
echo "Period: $startDate to $endDate\n\n";

$stmt = $pdo->prepare("
    SELECT
        u.unit_no,
        ut.unit_types_name AS unit_type,
        COALESCE(t.tenant_name, 'VACANT') AS tenant_name,
        tc.tenant_contract_start_date AS contract_start,
        tc.tenant_contract_valid_to_date AS contract_end,
        (
            SELECT rg2.receipts_generation_eff_to
            FROM receipts_generation rg2
            JOIN tenant_contracts tc_r ON tc_r.id = rg2.tenant_contract_id
            WHERE tc_r.unit_id = u.id
              AND tc_r.tenant_id = tc.tenant_id
              AND rg2.receipts_generation_eff_to::date <= ?::date
              AND rg2.receipts_generation_receipt_date::date <= ?::date
              AND rg2.receipts_generation_status != 2
              AND rg2.deleted_at IS NULL
            ORDER BY rg2.receipts_generation_eff_to DESC NULLS LAST
            LIMIT 1
        ) AS rent_recd_upto,
        CASE WHEN tc.id IS NOT NULL THEN (
            SELECT rg2.receipts_generation_eff_to
            FROM receipts_generation rg2
            JOIN tenant_contracts tc_r ON tc_r.id = rg2.tenant_contract_id
            WHERE tc_r.unit_id = u.id
              AND tc_r.tenant_id = tc.tenant_id
              AND rg2.receipts_generation_eff_from::date <= ?::date
              AND rg2.receipts_generation_receipt_date::date <= ?::date
              AND rg2.receipts_generation_status != 2
              AND rg2.deleted_at IS NULL
            ORDER BY rg2.receipts_generation_eff_from DESC NULLS LAST
            LIMIT 1
        ) END AS paid_through,
        COALESCE(tc.tenant_contract_rent, 0) AS rent_per_month,
        CASE WHEN tc.id IS NOT NULL THEN COALESCE(
            (
                SELECT SUM(rg2.receipts_generation_amt)
                FROM receipts_generation rg2
                JOIN tenant_contracts tc_r ON tc_r.id = rg2.tenant_contract_id
                WHERE tc_r.unit_id = u.id
                  AND tc_r.tenant_id = tc.tenant_id
                  AND DATE_TRUNC('month', rg2.receipts_generation_receipt_date::date) = DATE_TRUNC('month', ?::date)
                  AND rg2.receipts_generation_eff_from IS NOT NULL
                  AND rg2.receipts_generation_status != 2
                  AND rg2.deleted_at IS NULL
            ), 0
        ) ELSE 0 END AS collection_amount,
        COALESCE(tc.tenant_contract_rent, 0) AS income_amount,
        0 AS outstanding_amount,
        CASE WHEN EXISTS (
            SELECT 1 FROM tenant_contracts tc_prev
            WHERE tc_prev.unit_id = u.id
              AND tc_prev.tenant_contract_start_date <= ?::date
              AND (tc_prev.tenant_contract_valid_to_date >= ?::date OR tc_prev.tenant_contract_valid_to_date IS NULL)
        ) THEN 1 ELSE 0 END AS occupied_at_month_start
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
    LEFT JOIN buildings b ON b.id = u.building_id
    WHERE b.id = ? AND u.unit_status = 1
    GROUP BY u.id, u.unit_no, ut.unit_types_name, t.tenant_name,
             tc.id, tc.tenant_id, tc.tenant_contract_rent,
             tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date
    ORDER BY u.unit_no
");
$stmt->execute([$endDate, $endDate, $endDate, $endDate, $startDate, $startDate, $startDate, $endDate, $startDate, $bid]);
$units = $stmt->fetchAll(PDO::FETCH_OBJ);

echo "=== ACTIVE UNITS ===\n";
echo "Rows: " . count($units) . "\n\n";

printf("%-6s %-35s %-12s %-12s %-8s %-12s %10s %10s\n",
    'UNIT', 'TENANT', 'RENT RECD', 'PAID THRU', 'RENT/MO', 'COLLECTION', 'INCOME', 'OUTSTANDING');
echo str_repeat('-', 115) . "\n";

foreach ($units as $unit) {
    $rr = $unit->rent_recd_upto ? date('d.m.Y', strtotime($unit->rent_recd_upto)) : '-';
    $pt = $unit->paid_through   ? date('d.m.Y', strtotime($unit->paid_through))   : '-';

    // Prorate for genuinely new occupancy starting mid-month
    // Skip if unit was already occupied at the start of the month (renewal/continuous occupancy)
    $rent = (float)$unit->rent_per_month;
    if ($rent > 0 && !empty($unit->contract_start)) {
        $contractStartTs = strtotime($unit->contract_start);
        $isNewOccupancy  = empty($unit->occupied_at_month_start);
        if ($contractStartTs > strtotime($startDate) && $contractStartTs <= strtotime($endDate) && $isNewOccupancy) {
            $startDay   = (int)date('j', $contractStartTs);
            $startDay30 = ($startDay >= (int)date('t', $contractStartTs)) ? 30 : min($startDay, 30);
            $proratedDays = 30 - $startDay30 + 1;
            $rent = round($rent * $proratedDays / 30, 2);
            $unit->rent_per_month = $rent;
            $unit->income_amount  = $rent;
            // collection = actual cash received, never capped by prorated rent
        }
    }

    // Recalculate outstanding
    $effTo = $unit->paid_through ?? null;
    $outstanding = 0;

    if ($rent > 0) {
        if (empty($effTo)) {
            $outstanding = $rent;
        } else {
            $effToTs   = strtotime($effTo);
            $endDateTs = strtotime($endDate);
            if ($effToTs >= $endDateTs) {
                $outstanding = 0;
            } else {
                $nextDayTs     = $effToTs + 86400;
                $nextPeriodEnd = date('Y-m-t', mktime(0, 0, 0, (int)date('n', $nextDayTs), 1, (int)date('Y', $nextDayTs)));
                $ceilingDate   = (strtotime($nextPeriodEnd) > $endDateTs) ? $nextPeriodEnd : $endDate;
                $ceilingTs     = strtotime($ceilingDate);

                $eD    = (int)date('j', $effToTs);
                $eD30  = ($eD >= (int)date('t', $effToTs)) ? 30 : min($eD, 30);
                $cD    = (int)date('j', $ceilingTs);
                $cD30  = ($cD >= (int)date('t', $ceilingTs)) ? 30 : min($cD, 30);
                $days  = ((int)date('Y', $ceilingTs) - (int)date('Y', $effToTs)) * 360
                       + ((int)date('n', $ceilingTs) - (int)date('n', $effToTs)) * 30
                       + ($cD30 - $eD30);
                $outstanding = $days > 0 ? round($days * $rent / 30, 2) : 0;
            }
        }
    }

    printf("%-6s %-35s %-12s %-12s %8.0f %10.2f %10.2f %10.2f\n",
        $unit->unit_no, $unit->tenant_name, $rr, $pt,
        $rent, (float)$unit->collection_amount, (float)$unit->income_amount, $outstanding);
}

// ─── Old Outstanding ─────────────────────────────────────────────────────────
$stmt2 = $pdo->prepare("
    SELECT
        u.unit_no, ut.unit_types_name AS unit_type, t.tenant_name,
        tc.tenant_contract_start_date AS contract_start,
        tc.tenant_contract_valid_to_date AS contract_end,
        (
            SELECT rg2.receipts_generation_eff_to
            FROM receipts_generation rg2
            JOIN tenant_contracts tc_any ON tc_any.id = rg2.tenant_contract_id
            WHERE tc_any.unit_id = u.id
              AND tc_any.tenant_contract_valid_to_date IS NOT NULL
              AND tc_any.tenant_contract_valid_to_date < ?::date
              AND rg2.receipts_generation_eff_from::date <= ?::date
              AND rg2.receipts_generation_receipt_date::date <= ?::date
              AND rg2.receipts_generation_status != 2
              AND rg2.deleted_at IS NULL
            ORDER BY rg2.receipts_generation_eff_from DESC NULLS LAST
            LIMIT 1
        ) AS rent_recd_upto,
        (
            SELECT rg2.receipts_generation_eff_to
            FROM receipts_generation rg2
            JOIN tenant_contracts tc_any ON tc_any.id = rg2.tenant_contract_id
            WHERE tc_any.unit_id = u.id
              AND tc_any.tenant_contract_valid_to_date IS NOT NULL
              AND tc_any.tenant_contract_valid_to_date < ?::date
              AND rg2.receipts_generation_eff_from::date <= ?::date
              AND rg2.receipts_generation_receipt_date::date <= ?::date
              AND rg2.receipts_generation_status != 2
              AND rg2.deleted_at IS NULL
            ORDER BY rg2.receipts_generation_eff_from DESC NULLS LAST
            LIMIT 1
        ) AS paid_through,
        COALESCE(tc.tenant_contract_rent, 0) AS rent_per_month,
        COALESCE(
            (
                SELECT SUM(rg2.receipts_generation_amt)
                FROM receipts_generation rg2
                JOIN tenant_contracts tc_any ON tc_any.id = rg2.tenant_contract_id
                WHERE tc_any.unit_id = u.id
                  AND tc_any.tenant_contract_valid_to_date IS NOT NULL
                  AND tc_any.tenant_contract_valid_to_date < ?::date
                  AND DATE_TRUNC('month', rg2.receipts_generation_receipt_date::date) = DATE_TRUNC('month', ?::date)
                  AND rg2.receipts_generation_eff_from IS NOT NULL
                  AND rg2.receipts_generation_status != 2
                  AND rg2.deleted_at IS NULL
            ), 0
        ) AS collection_amount
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
            AND tc2.tenant_id = tc.tenant_id
            AND tc2.tenant_contract_start_date <= ?::date
            AND (tc2.tenant_contract_valid_to_date >= ?::date OR tc2.tenant_contract_valid_to_date IS NULL)
      )
    ORDER BY u.unit_no
");
$stmt2->execute([$startDate, $endDate, $endDate, $startDate, $endDate, $endDate, $startDate, $startDate, $startDate, $bid, $endDate, $startDate]);
$oldRows = $stmt2->fetchAll(PDO::FETCH_OBJ);

echo "\n=== OLD OUTSTANDING ===\n";
echo "Rows: " . count($oldRows) . "\n\n";

if (count($oldRows) > 0) {
    printf("%-6s %-30s %-12s %-12s %-12s %8s %10s %10s\n",
        'UNIT','TENANT','CONT.END','RENT RECD','PAID THRU','RENT/MO','COLL','OUTSTANDING');
    echo str_repeat('-', 115) . "\n";

    foreach ($oldRows as $u) {
        $rent        = (float)$u->rent_per_month;
        $effTo       = $u->paid_through ?? null;
        $contractEnd = $u->contract_end ?? null;
        $contractStart = $u->contract_start ?? null;
        $out = 0;

        if ($rent > 0) {
            if (!empty($effTo) && !empty($contractEnd) && strtotime($effTo) > strtotime($contractEnd)) $effTo = $contractEnd;
            if (empty($effTo) && !empty($contractStart)) $effTo = date('Y-m-d', strtotime($contractStart) - 86400);
            $ceilingDate = !empty($contractEnd) ? $contractEnd : $endDate;
            if (!empty($effTo)) {
                $effToTs = strtotime($effTo); $cTs = strtotime($ceilingDate);
                if ($effToTs < $cTs) {
                    $eD=(int)date('j',$effToTs); $eD30=($eD>=(int)date('t',$effToTs))?30:min($eD,30);
                    $cD=(int)date('j',$cTs); $cD30=($cD>=(int)date('t',$cTs))?30:min($cD,30);
                    $days=((int)date('Y',$cTs)-(int)date('Y',$effToTs))*360+((int)date('n',$cTs)-(int)date('n',$effToTs))*30+($cD30-$eD30);
                    $out = $days > 0 ? round($days*$rent/30,2) : 0;
                }
            }
        }

        $ce = $u->contract_end   ? date('d.m.Y', strtotime($u->contract_end))   : '-';
        $rr = $u->rent_recd_upto ? date('d.m.Y', strtotime($u->rent_recd_upto)) : '-';
        $pt = $u->paid_through   ? date('d.m.Y', strtotime($u->paid_through))   : '-';

        printf("%-6s %-30s %-12s %-12s %-12s %8.0f %10.2f %10.2f\n",
            $u->unit_no, $u->tenant_name, $ce, $rr, $pt, $rent, (float)$u->collection_amount, $out);
    }
}
