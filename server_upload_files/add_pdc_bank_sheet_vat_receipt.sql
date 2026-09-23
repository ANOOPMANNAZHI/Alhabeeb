-- Equivalent of migration 2026_09_23_000000_add_vat_receipt_no_to_pdc_bank_sheet_upload_rows
-- Records the VAT General Receipt no(s) a bank-sheet row created. PostgreSQL. Safe to re-run.
ALTER TABLE pdc_bank_sheet_upload_rows ADD COLUMN IF NOT EXISTS vat_receipt_no varchar(100) NULL;
