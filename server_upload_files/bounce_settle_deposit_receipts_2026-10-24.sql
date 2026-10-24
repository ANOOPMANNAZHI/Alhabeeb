-- Mark 3 bounced DEPOSIT cheques as settled by the deposit receipts that paid them.
-- The receipts were entered with pay type "Normal" instead of "Bounce", so the cheques stayed
-- open on the bounced-cheque lists and dashboard cards (sales, ARE, back office).
-- Only the cheque rows change, the same 2 fields that approving a "Bounce" receipt sets.
-- Receipts and AX postings are not touched.
--
--   TAG2600199  cheque 70812469  OMR 240  paid by CAR2600462
--   TAG2600268  cheque 1258937   OMR 250  paid by CPY2606565
--   TAG2600270  cheque 2159900   OMR 250  paid by CPY2607049
--
-- Each update applies only while the cheque is still bounced and unsettled, and only if the
-- receipt exists on the same contract, is not cancelled/rejected and is approved.
-- Expected: "UPDATE 1" three times. Any "UPDATE 0" means the data differs: ROLLBACK and check.

BEGIN;

-- before
SELECT tc.tenant_contract_no, p.id, p.pdc_check_no, p.pdc_amt, p.pdc_settlement, p.pdc_receipt_no
FROM pdc p JOIN tenant_contracts tc ON tc.id = p.tenant_contract_id
WHERE (tc.tenant_contract_no, ltrim(p.pdc_check_no::text, '0')) IN
      (('TAG2600199', '70812469'), ('TAG2600268', '1258937'), ('TAG2600270', '2159900'))
  AND p.pdc_cancel_reason = '1';

UPDATE pdc p SET pdc_settlement = 2, pdc_receipt_no = rg.receipts_generation_receipt_no, updated_at = now()
FROM tenant_contracts tc, receipts_generation rg
WHERE tc.id = p.tenant_contract_id AND rg.tenant_contract_id = tc.id
  AND tc.tenant_contract_no = 'TAG2600199' AND ltrim(p.pdc_check_no::text, '0') = '70812469'
  AND p.pdc_cancel_reason = '1' AND (p.pdc_settlement IS NULL OR p.pdc_settlement <> 2)
  AND rg.receipts_generation_receipt_no = 'CAR2600462' AND rg.deleted_at IS NULL
  AND rg.receipts_generation_status <> 2 AND rg.receipts_generation_approval_status = 6;

UPDATE pdc p SET pdc_settlement = 2, pdc_receipt_no = rg.receipts_generation_receipt_no, updated_at = now()
FROM tenant_contracts tc, receipts_generation rg
WHERE tc.id = p.tenant_contract_id AND rg.tenant_contract_id = tc.id
  AND tc.tenant_contract_no = 'TAG2600268' AND ltrim(p.pdc_check_no::text, '0') = '1258937'
  AND p.pdc_cancel_reason = '1' AND (p.pdc_settlement IS NULL OR p.pdc_settlement <> 2)
  AND rg.receipts_generation_receipt_no = 'CPY2606565' AND rg.deleted_at IS NULL
  AND rg.receipts_generation_status <> 2 AND rg.receipts_generation_approval_status = 6;

UPDATE pdc p SET pdc_settlement = 2, pdc_receipt_no = rg.receipts_generation_receipt_no, updated_at = now()
FROM tenant_contracts tc, receipts_generation rg
WHERE tc.id = p.tenant_contract_id AND rg.tenant_contract_id = tc.id
  AND tc.tenant_contract_no = 'TAG2600270' AND ltrim(p.pdc_check_no::text, '0') = '2159900'
  AND p.pdc_cancel_reason = '1' AND (p.pdc_settlement IS NULL OR p.pdc_settlement <> 2)
  AND rg.receipts_generation_receipt_no = 'CPY2607049' AND rg.deleted_at IS NULL
  AND rg.receipts_generation_status <> 2 AND rg.receipts_generation_approval_status = 6;

-- after: all 3 should show pdc_settlement 2 and the receipt no
SELECT tc.tenant_contract_no, p.id, p.pdc_check_no, p.pdc_amt, p.pdc_settlement, p.pdc_receipt_no
FROM pdc p JOIN tenant_contracts tc ON tc.id = p.tenant_contract_id
WHERE (tc.tenant_contract_no, ltrim(p.pdc_check_no::text, '0')) IN
      (('TAG2600199', '70812469'), ('TAG2600268', '1258937'), ('TAG2600270', '2159900'))
  AND p.pdc_cancel_reason = '1';

COMMIT;
