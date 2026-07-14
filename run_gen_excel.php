<?php
/**
 * Standalone Excel generator for Normal Management Report v2
 * Uses direct PDO — bypasses Laravel bootstrap (PHP 8.4 compat issues).
 * Bootstraps only the autoloader so Maatwebsite Excel classes are available.
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('memory_limit', '512M');

require __DIR__ . '/vendor/autoload.php';

// ─── Config ───────────────────────────────────────────────────────────────────
$bid   = 430;
$month = 1;       // 1 = Jan, or 'all' for all months
$year  = 2025;
$outDir = __DIR__ . '/storage/app/temp/test_excel';
// ─────────────────────────────────────────────────────────────────────────────

$pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=plms_live', 'postgres', '123456');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

$building = $pdo->query("SELECT id, building_name FROM buildings WHERE id = $bid")->fetch();
echo "Building: {$building->building_name}\n";

$monthsToPopulate = ($month === 'all') ? range(1, 12) : [(int)$month];

// ─── Helper: outstanding 30/360 ──────────────────────────────────────────────
function calcOutstanding($rent, $effTo, $ceilingDate) {
    if ($rent <= 0 || empty($ceilingDate)) return 0;
    if (empty($effTo)) return $rent;

    $effToTs    = strtotime($effTo);
    $ceilingTs  = strtotime($ceilingDate);
    if ($effToTs >= $ceilingTs) return 0;

    $eD   = (int)date('j', $effToTs);
    $eD30 = ($eD >= (int)date('t', $effToTs)) ? 30 : min($eD, 30);
    $cD   = (int)date('j', $ceilingTs);
    $cD30 = ($cD >= (int)date('t', $ceilingTs)) ? 30 : min($cD, 30);
    $days = ((int)date('Y',$ceilingTs)-(int)date('Y',$effToTs))*360
          + ((int)date('n',$ceilingTs)-(int)date('n',$effToTs))*30
          + ($cD30 - $eD30);
    return $days > 0 ? round($days * $rent / 30, 2) : 0;
}

// ─── Build monthData ─────────────────────────────────────────────────────────
$monthData = [];

foreach (range(1, 12) as $m) {
    $startDate = date('Y-m-d', mktime(0,0,0,$m,1,$year));
    $endDate   = date('Y-m-t', mktime(0,0,0,$m,1,$year));

    if (!in_array($m, $monthsToPopulate)) {
        $monthData[$m] = ['units'=>[], 'old_outstanding'=>[], 'expenses'=>[], 'occupancy'=>[
            'total_units'=>0,'new_leased_residential'=>0,'new_leased_commercial'=>0,
            'occupied_residential'=>0,'occupied_commercial'=>0,
            'vacant_residential'=>0,'vacant_commercial'=>0,
            'evacuation_residential'=>0,'evacuation_commercial'=>0,
        ]];
        continue;
    }

    echo "  Month $m ($startDate to $endDate)...\n";

    // ── Active units ──────────────────────────────────────────────────────────
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
                  AND rg2.receipts_generation_eff_to::date <= ?::date
                  AND rg2.receipts_generation_receipt_date::date <= ?::date
                  AND rg2.receipts_generation_status != 2
                  AND rg2.deleted_at IS NULL
                ORDER BY rg2.receipts_generation_eff_to DESC NULLS LAST
                LIMIT 1
            ) AS rent_recd_upto,
            (
                SELECT rg2.receipts_generation_eff_to
                FROM receipts_generation rg2
                WHERE rg2.tenant_contract_id = tc.id
                  AND rg2.receipts_generation_eff_from::date <= ?::date
                  AND rg2.receipts_generation_receipt_date::date <= ?::date
                  AND rg2.receipts_generation_status != 2
                  AND rg2.deleted_at IS NULL
                ORDER BY rg2.receipts_generation_eff_from DESC NULLS LAST
                LIMIT 1
            ) AS paid_through,
            COALESCE(tc.tenant_contract_rent, 0) AS rent_per_month,
            COALESCE(
                SUM(rg.receipts_generation_amt) FILTER (
                    WHERE DATE_TRUNC('month', rg.receipts_generation_receipt_date::date) = DATE_TRUNC('month', ?::date)
                      AND rg.receipts_generation_status != 2
                      AND rg.deleted_at IS NULL
                ), 0
            ) AS collection_amount,
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
        GROUP BY u.id, u.unit_no, ut.unit_types_name, t.tenant_name,
                 tc.id, tc.tenant_contract_rent,
                 tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date
        ORDER BY u.unit_no
    ");
    $stmt->execute([$endDate, $endDate, $endDate, $endDate, $startDate, $endDate, $startDate, $bid]);
    $units = $stmt->fetchAll();

    // Outstanding calc
    foreach ($units as $unit) {
        $rent  = (float)$unit->rent_per_month;
        $effTo = $unit->paid_through ?? null;

        if ($rent <= 0) { $unit->outstanding_amount = 0; continue; }
        if (empty($effTo)) { $unit->outstanding_amount = $rent; continue; }

        $effToTs   = strtotime($effTo);
        $endDateTs = strtotime($endDate);
        if ($effToTs >= $endDateTs) { $unit->outstanding_amount = 0; continue; }

        $nextDayTs     = $effToTs + 86400;
        $nextPeriodEnd = date('Y-m-t', mktime(0,0,0,(int)date('n',$nextDayTs),1,(int)date('Y',$nextDayTs)));
        $ceilingDate   = (strtotime($nextPeriodEnd) > $endDateTs) ? $nextPeriodEnd : $endDate;
        $unit->outstanding_amount = calcOutstanding($rent, $effTo, $ceilingDate);
    }

    // ── Old outstanding ───────────────────────────────────────────────────────
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
            0 AS collection_amount, 0 AS income_amount, 0 AS outstanding_amount
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
    ");
    $stmt2->execute([$startDate, $endDate, $endDate, $startDate, $endDate, $endDate, $startDate, $bid, $endDate, $startDate]);
    $oldOutstanding = $stmt2->fetchAll();

    foreach ($oldOutstanding as $unit) {
        $rent        = (float)$unit->rent_per_month;
        $effTo       = $unit->paid_through ?? null;
        $contractEnd = $unit->contract_end ?? null;
        $contractStart = $unit->contract_start ?? null;
        if ($rent <= 0) { $unit->outstanding_amount = 0; continue; }
        if (!empty($effTo) && !empty($contractEnd) && strtotime($effTo) > strtotime($contractEnd)) $effTo = $contractEnd;
        if (empty($effTo) && !empty($contractStart)) $effTo = date('Y-m-d', strtotime($contractStart) - 86400);
        $ceilingDate = !empty($contractEnd) ? $contractEnd : $endDate;
        $unit->outstanding_amount = calcOutstanding($rent, $effTo, $ceilingDate);
    }
    $oldOutstanding = array_values(array_filter($oldOutstanding, function($u) { return $u->outstanding_amount > 0; }));

    // ── Expenses ──────────────────────────────────────────────────────────────
    $stmt3 = $pdo->prepare("
        SELECT eh.expense_name,
               COALESCE(SUM(CAST(mid.debit_amt AS NUMERIC)), 0) AS expense_amount
        FROM maintenance_invoice_details mid
        JOIN maintenance_invoices mi ON mi.id = mid.maintenance_invoice_id
        JOIN acc_codes ac ON ac.id = mid.ac_codes_id
        JOIN expense_head eh ON eh.acc_codes_id = ac.id
        WHERE mid.building_id = ?
          AND mi.maintenance_invoice_date BETWEEN ? AND ?
          AND mi.maintenance_invoice_status != 2
          AND mi.deleted_at IS NULL
        GROUP BY eh.expense_name ORDER BY eh.expense_name
    ");
    $stmt3->execute([$bid, $startDate, $endDate]);
    $expenses = $stmt3->fetchAll();

    // ── Occupancy ─────────────────────────────────────────────────────────────
    $totalUnits = (int)$pdo->query("SELECT COUNT(*) FROM units WHERE building_id=$bid AND unit_status=1")->fetchColumn();
    $monthData[$m] = [
        'units'           => $units,
        'old_outstanding' => $oldOutstanding,
        'expenses'        => $expenses,
        'occupancy'       => [
            'total_units'            => $totalUnits,
            'new_leased_residential' => 0,
            'new_leased_commercial'  => 0,
            'occupied_residential'   => 0,
            'occupied_commercial'    => 0,
            'vacant_residential'     => 0,
            'vacant_commercial'      => 0,
            'evacuation_residential' => 0,
            'evacuation_commercial'  => 0,
        ],
    ];

    echo "    Units: " . count($units) . ", OldOutstanding: " . count($oldOutstanding) . ", Expenses: " . count($expenses) . "\n";
}

// ─── Generate Excel ───────────────────────────────────────────────────────────
if (!file_exists($outDir)) mkdir($outDir, 0755, true);

$safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $building->building_name);
$outFile  = $outDir . '/' . $safeName . '_' . $year . '.xlsx';

echo "Generating Excel...\n";

$export = new \Modules\BackOffice\Exports\NormalManagementV2Export(
    $monthData, $year, $building->building_name, $month === 'all' ? 'all' : (string)$month
);

$writer = \Maatwebsite\Excel\Excel::XLSX;
$excel  = app(\Maatwebsite\Excel\Excel::class);
// Use PhpSpreadsheet directly since we don't have Laravel app container here
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$spreadsheet->removeSheetByIndex(0); // remove default blank sheet

foreach ($export->sheets() as $sheetExport) {
    $ws = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheetExport->title());
    $spreadsheet->addSheet($ws);

    // Fill data
    $rows = $sheetExport->array();
    foreach ($rows as $rowIndex => $rowData) {
        foreach ($rowData as $colIndex => $value) {
            $ws->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 1, $value);
        }
    }

    // Fire AfterSheet events via WithEvents
    if (method_exists($sheetExport, 'registerEvents')) {
        $events = $sheetExport->registerEvents();
        // Build a fake AfterSheet event
        $delegate = $ws;
        $sheetWrapper = new class($ws) {
            public function __construct(private $ws) {}
            public function getDelegate() { return $this->ws; }
        };
        $fakeEvent = new class($sheetWrapper) extends \Maatwebsite\Excel\Events\AfterSheet {
            public function __construct(public $sheet) { }
        };
        if (isset($events[\Maatwebsite\Excel\Events\AfterSheet::class])) {
            ($events[\Maatwebsite\Excel\Events\AfterSheet::class])($fakeEvent);
        }
    }

    // Auto-size columns
    foreach (range('A', $ws->getHighestColumn()) as $col) {
        $ws->getColumnDimension($col)->setAutoSize(true);
    }
}

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($outFile);

echo "Saved: $outFile\n";
