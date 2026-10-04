-- Rename the three AMC expense heads to their account names (04/10/2026).
-- They sit on accounts 22307 / 22308 / 22309 and were still called "CIT - ...".
-- Each UPDATE matches the account code AND the old name, so it changes nothing
-- if the head was already renamed or points somewhere else.
-- Run the SELECTs first, then the BEGIN ... COMMIT block.

-- 1. Check (expect 3 rows with the old names)
SELECT eh.id, eh.expense_name, ac.acc_code_val, ac.acc_code_desc
  FROM expense_head eh
  JOIN acc_codes ac ON ac.id = eh.acc_codes_id
 WHERE ac.acc_code_val IN (22307, 22308, 22309)
 ORDER BY ac.acc_code_val;

-- 2. Rename (each UPDATE should report "UPDATE 1")
BEGIN;

UPDATE expense_head eh SET expense_name = 'A C MAINTENANCE - A M C', updated_at = now()
  FROM acc_codes ac
 WHERE ac.id = eh.acc_codes_id AND ac.acc_code_val = 22307
   AND eh.expense_name = 'CIT - AC MAINTENANCE';

UPDATE expense_head eh SET expense_name = 'FIRE ALARAM SYSTEM MAINT. - A M C', updated_at = now()
  FROM acc_codes ac
 WHERE ac.id = eh.acc_codes_id AND ac.acc_code_val = 22308
   AND eh.expense_name = 'CIT - FIRE ALARAM SYSTEM MAINT.';

UPDATE expense_head eh SET expense_name = 'LIFT & ESCALATORS MAINT. - A M C', updated_at = now()
  FROM acc_codes ac
 WHERE ac.id = eh.acc_codes_id AND ac.acc_code_val = 22309
   AND eh.expense_name = 'CIT - LIFT & ESCALATORS MAINT.';

-- Check again: 3 rows with the new names, then COMMIT (or ROLLBACK if anything looks wrong)
SELECT eh.id, eh.expense_name, ac.acc_code_val
  FROM expense_head eh
  JOIN acc_codes ac ON ac.id = eh.acc_codes_id
 WHERE ac.acc_code_val IN (22307, 22308, 22309)
 ORDER BY ac.acc_code_val;

COMMIT;

-- UNDO (only if needed): puts the old names back
-- BEGIN;
-- UPDATE expense_head eh SET expense_name = 'CIT - AC MAINTENANCE', updated_at = now()
--   FROM acc_codes ac WHERE ac.id = eh.acc_codes_id AND ac.acc_code_val = 22307 AND eh.expense_name = 'A C MAINTENANCE - A M C';
-- UPDATE expense_head eh SET expense_name = 'CIT - FIRE ALARAM SYSTEM MAINT.', updated_at = now()
--   FROM acc_codes ac WHERE ac.id = eh.acc_codes_id AND ac.acc_code_val = 22308 AND eh.expense_name = 'FIRE ALARAM SYSTEM MAINT. - A M C';
-- UPDATE expense_head eh SET expense_name = 'CIT - LIFT & ESCALATORS MAINT.', updated_at = now()
--   FROM acc_codes ac WHERE ac.id = eh.acc_codes_id AND ac.acc_code_val = 22309 AND eh.expense_name = 'LIFT & ESCALATORS MAINT. - A M C';
-- COMMIT;
