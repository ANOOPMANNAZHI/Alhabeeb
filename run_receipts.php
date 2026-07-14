<?php
$pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=plms_live', 'postgres', '123456');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$rows = $pdo->query("
    SELECT u.unit_no, t.tenant_name, tc.id AS contract_id,
           tc.tenant_contract_start_date, tc.tenant_contract_valid_to_date
    FROM units u
    JOIN tenant_contracts tc ON tc.unit_id = u.id
    JOIN tenant t ON t.id = tc.tenant_id
    WHERE u.building_id = 430 AND u.unit_no = '03'
    ORDER BY tc.tenant_contract_start_date
")->fetchAll(PDO::FETCH_OBJ);

foreach ($rows as $r) {
    echo "Unit {$r->unit_no} | {$r->tenant_name} | contract_id={$r->contract_id} | {$r->tenant_contract_start_date} to {$r->tenant_contract_valid_to_date}\n";
    $stmt = $pdo->prepare("
        SELECT receipts_generation_eff_from AS eff_from,
               receipts_generation_eff_to AS eff_to,
               receipts_generation_amt AS amt,
               receipts_generation_receipt_date AS receipt_date,
               receipts_generation_status AS status
        FROM receipts_generation
        WHERE tenant_contract_id = ?
          AND deleted_at IS NULL
          AND receipts_generation_status != 2
        ORDER BY receipts_generation_eff_from
    ");
    $stmt->execute([$r->contract_id]);
    $recs = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (empty($recs)) {
        echo "  (no receipts)\n";
    } else {
        printf("  %-14s %-14s %8s %-14s %s\n", 'eff_from','eff_to','amt','receipt_date','status');
        echo "  " . str_repeat('-', 60) . "\n";
        foreach ($recs as $rec) {
            printf("  %-14s %-14s %8s %-14s %s\n",
                substr($rec->eff_from,0,10), substr($rec->eff_to,0,10),
                $rec->amt, $rec->receipt_date ?? 'NULL', $rec->status);
        }
    }
    echo "\n";
}
