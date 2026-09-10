-- ============================================================================
-- Backfills the cheque number into rent receipt remarks that were saved as the
-- bare phrase "Received cash payment for cheque no" with no number.
--
-- Cause (fixed in Receipt/rent_payment_receipt_create.blade.php): the remark was
-- written as soon as the cheque dropdown was populated, while the "Select
-- Cheque" placeholder - which carries no data-cheque - was still selected. A
-- stale line was also left behind when the payment type was switched away from
-- Replace.
--
-- The cheque number is taken from the PDC the receipt is already linked to via
-- receipts_generation_is_pdc_bounce_id, so nothing is invented.
--
-- Safe to re-run: only rows still holding the bare phrase are touched.
-- ============================================================================

-- 1) PREVIEW - run this first and check the list
SELECT rg.id,
       rg.receipts_generation_receipt_no        AS receipt_no,
       rg.receipts_generation_remark            AS current_remark,
       p.pdc_check_no,
       'Received cash payment for cheque no ' || p.pdc_check_no AS new_remark
FROM receipts_generation rg
JOIN pdc p ON p.id = rg.receipts_generation_is_pdc_bounce_id
WHERE TRIM(rg.receipts_generation_remark) ILIKE 'Received cash payment for cheque no'
  AND p.pdc_check_no IS NOT NULL
  AND TRIM(p.pdc_check_no) <> ''
ORDER BY rg.id;

-- 2) APPLY
UPDATE receipts_generation rg
SET    receipts_generation_remark = 'Received cash payment for cheque no ' || p.pdc_check_no,
       updated_at = now()
FROM   pdc p
WHERE  p.id = rg.receipts_generation_is_pdc_bounce_id
  AND  TRIM(rg.receipts_generation_remark) ILIKE 'Received cash payment for cheque no'
  AND  p.pdc_check_no IS NOT NULL
  AND  TRIM(p.pdc_check_no) <> '';

-- 3) VERIFY - expect no rows other than receipts with no PDC link at all
SELECT rg.receipts_generation_receipt_no AS receipt_no,
       rg.receipts_generation_is_pdc_bounce_id AS pdc_id,
       p.pdc_check_no
FROM receipts_generation rg
LEFT JOIN pdc p ON p.id = rg.receipts_generation_is_pdc_bounce_id
WHERE TRIM(rg.receipts_generation_remark) ILIKE 'Received cash payment for cheque no'
ORDER BY rg.id;

-- On the reference database this repaired 8 receipts and left 1 (CPY2616891)
-- untouched because it carries no PDC link, so no cheque number exists for it.
