-- Equivalent of migration 2026_09_20_000001_add_pdc_bank_sheet_upload
-- PDC posting from the bank's cheque clearing sheet:
--   1. bank.bank_clearing_codes  - code(s) the bank's export uses for each PLMS bank
--                                  ("27 - Bank Of Muscat" => 27), comma separated, seeded below.
--                                  Editable on Bank master ("Clearing Sheet Code(s)").
--   2. pdc_bank_sheet_uploads     - one row per uploaded sheet (who, when, deposit bank/date, counts)
--   3. pdc_bank_sheet_upload_rows - one row per cheque on the sheet with what happened to it
-- PostgreSQL. Safe to re-run: every step is guarded.
-- If you see "current transaction is aborted" (25P02): run ROLLBACK; and read the FIRST error above it.

BEGIN;

-- 1. bank.bank_clearing_codes
ALTER TABLE bank ADD COLUMN IF NOT EXISTS bank_clearing_codes varchar(100) NULL;

-- seed only where empty, so codes already edited on the server are kept
UPDATE bank SET bank_clearing_codes = v.codes
FROM (VALUES
    ('BMI',    '27,34,41'),   -- BANK MUSCAT, incl. Meethaq / Al Izz cheques entered under it
    ('BMQ',    '34'),         -- Bank Muscat MEETHAQ
    ('AIB',    '41'),         -- ALIZ ISLAMIC BANK
    ('NBO',    '18'),
    ('HSBC-1', '10'),
    ('OAB',    '2'),
    ('BD',     '25,36'),      -- BANK DHOFAR, incl. Maisarah
    ('BS',     '30,38'),      -- BANK SOHAR, incl. Sohar Islamic
    ('BNW',    '33'),
    ('AHB',    '31'),
    ('NBA',    '17')          -- NATIONAL BANK OF AD / FAB
) AS v(code, codes)
WHERE bank.bank_code = v.code
  AND (bank.bank_clearing_codes IS NULL OR bank.bank_clearing_codes = '');

-- 2. pdc_bank_sheet_uploads
CREATE TABLE IF NOT EXISTS pdc_bank_sheet_uploads (
    id               serial PRIMARY KEY,
    file_name        varchar(255) NOT NULL,
    pdc_deposit_date date         NOT NULL,
    bank_id          integer      NOT NULL,
    rows_total       integer      NOT NULL DEFAULT 0,
    rows_posted      integer      NOT NULL DEFAULT 0,
    pdcs_posted      integer      NOT NULL DEFAULT 0,
    created_by       integer      NOT NULL,
    created_at       timestamp(0) NULL,
    updated_at       timestamp(0) NULL
);

-- 3. pdc_bank_sheet_upload_rows
CREATE TABLE IF NOT EXISTS pdc_bank_sheet_upload_rows (
    id           serial PRIMARY KEY,
    upload_id    integer       NOT NULL,
    sheet_row_no integer       NOT NULL,
    cheque_no    varchar(50)   NOT NULL,
    bank_name    varchar(255)  NULL,
    amount       numeric(14,3) NOT NULL DEFAULT 0,
    cheque_date  date          NULL,
    status       varchar(30)   NOT NULL,
    reason       varchar(500)  NULL,
    pdc_ids      varchar(500)  NULL,
    created_at   timestamp(0)  NULL,
    updated_at   timestamp(0)  NULL
);
COMMENT ON COLUMN pdc_bank_sheet_upload_rows.pdc_ids IS 'comma separated pdc.id posted for this row';
CREATE INDEX IF NOT EXISTS pdc_bank_sheet_upload_rows_upload_id_index ON pdc_bank_sheet_upload_rows (upload_id);

-- 4. Mark the migration as run so `php artisan migrate` does not try it again
INSERT INTO migrations (migration, batch)
SELECT '2026_09_20_000001_add_pdc_bank_sheet_upload', next_batch.b
FROM (SELECT COALESCE(MAX(batch), 0) + 1 AS b FROM migrations) AS next_batch
WHERE NOT EXISTS (
    SELECT 1 FROM migrations m WHERE m.migration = '2026_09_20_000001_add_pdc_bank_sheet_upload'
);

COMMIT;

-- Verify:
-- SELECT id, bank_code, bank_clearing_codes FROM bank WHERE bank_clearing_codes IS NOT NULL ORDER BY id;
-- SELECT * FROM pdc_bank_sheet_uploads;
--
-- Also make sure storage/app/pdc_bank_sheets/ is writable by the web server (created on first upload).
