-- Mark 18 bounced cheques as settled. Each was paid by receipt(s) entered with pay type "Normal"
-- instead of "Bounce", so the cheque stayed open on the bounced-cheque lists and dashboard cards.
-- In every case the receipt's cheque/reference field names the bounced cheque number, the
-- receipt(s) add up exactly to the cheque amount, and they are on the same contract.
-- Only the cheque rows change: pdc_settlement = 2 and pdc_receipt_no, the same 2 fields that
-- approving a "Bounce" receipt sets. When several receipts paid one cheque, the last one is
-- stored (the system does the same). Receipts and AX postings are not touched.
-- Run AFTER (or independently of) bounce_settle_deposit_receipts_2026-10-24.sql: no overlap.
--
--   contract     cheque     amount  paid by
--   TAG2502987   79323549   740     CPY2607585
--   TAG2503157   263371     780     CPY2607115
--   TAG2503238   986680     185     CPY2603703
--   TAG2503238   986681     185     CPY2603706 + CPY2603707
--   TAG2503678   13         975     CPY2603278 + CPY2603327
--   TAG2503834   2200003    160     CPY2610439
--   TAG2503836   91745260   150     CPY2624268 + CPY2624269
--   TAG2600002   3114797    800     CPY2623710 + CPY2623711   (rent)
--   TAG2600002   3114797     40     CPY2623712                (VAT)
--   TAG2600010   81577928   250     CPY2607041 + CPY2613258
--   TAG2600223   17939      450     CPY2609059 + CPY2609345
--   TAG2600367   92592851   160     CPY2607052 + CPY2611128
--   TAG2600389   81265685   110     CPY2610492 + CPY2610493
--   TAG2600801   81679982   200     CPY2622693
--   TAG2601600   2734137    260     CPY2623839
--   TAG2601601   2734137    210     CPY2623837 + CPY2623838
--   TAG2601602   2734137    210     CPY2623840
--   TAG2601612   78119089   160     CPY2623713 + CPY2623714
--
-- Each row applies only while that cheque is still bounced and unsettled, and only if the
-- receipt is on the same contract, not cancelled/rejected and approved.
-- Expected: "UPDATE 18". A smaller number means some rows differ on live: ROLLBACK and check.

BEGIN;

CREATE TEMP TABLE bounce_fix (contract_no text, pdc_id int, check_no text, receipt_no text) ON COMMIT DROP;
INSERT INTO bounce_fix VALUES
 ('TAG2502987', 514581, '79323549', 'CPY2607585'),
 ('TAG2503157', 515737, '263371',   'CPY2607115'),
 ('TAG2503238', 517121, '986680',   'CPY2603703'),
 ('TAG2503238', 517122, '986681',   'CPY2603707'),
 ('TAG2503678', 520822, '13',       'CPY2603327'),
 ('TAG2503834', 520465, '2200003',  'CPY2610439'),
 ('TAG2503836', 520484, '91745260', 'CPY2624269'),
 ('TAG2600002', 521701, '3114797',  'CPY2623711'),
 ('TAG2600002', 521713, '3114797',  'CPY2623712'),
 ('TAG2600010', 521767, '81577928', 'CPY2613258'),
 ('TAG2600223', 524389, '17939',    'CPY2609345'),
 ('TAG2600367', 524659, '92592851', 'CPY2611128'),
 ('TAG2600389', 525329, '81265685', 'CPY2610493'),
 ('TAG2600801', 528344, '81679982', 'CPY2622693'),
 ('TAG2601600', 534907, '2734137',  'CPY2623839'),
 ('TAG2601601', 534919, '2734137',  'CPY2623838'),
 ('TAG2601602', 534931, '2734137',  'CPY2623840'),
 ('TAG2601612', 536395, '78119089', 'CPY2623714');

-- before
SELECT f.contract_no, p.id, p.pdc_check_no, p.pdc_amt, p.pdc_settlement, p.pdc_receipt_no
FROM bounce_fix f JOIN pdc p ON p.id = f.pdc_id ORDER BY 1, 2;

UPDATE pdc p SET pdc_settlement = 2, pdc_receipt_no = f.receipt_no, updated_at = now()
FROM bounce_fix f, tenant_contracts tc, receipts_generation rg
WHERE p.id = f.pdc_id
  AND tc.id = p.tenant_contract_id AND tc.tenant_contract_no = f.contract_no
  AND ltrim(p.pdc_check_no::text, '0') = f.check_no
  AND p.pdc_cancel_reason = '1' AND (p.pdc_settlement IS NULL OR p.pdc_settlement <> 2)
  AND rg.receipts_generation_receipt_no = f.receipt_no AND rg.tenant_contract_id = tc.id
  AND rg.deleted_at IS NULL AND rg.receipts_generation_status <> 2
  AND rg.receipts_generation_approval_status = 6;

-- after: all 18 should show pdc_settlement 2 and the receipt no
SELECT f.contract_no, p.id, p.pdc_check_no, p.pdc_amt, p.pdc_settlement, p.pdc_receipt_no
FROM bounce_fix f JOIN pdc p ON p.id = f.pdc_id ORDER BY 1, 2;

COMMIT;
