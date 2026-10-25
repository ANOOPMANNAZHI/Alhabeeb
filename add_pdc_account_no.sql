-- Equivalent of migration 2026_09_24_000000_add_pdc_account_no_to_pdc
-- Drawer's bank account on a PDC (optional). PostgreSQL. Safe to re-run.
ALTER TABLE pdc ADD COLUMN IF NOT EXISTS pdc_account_no varchar(20) NULL;
