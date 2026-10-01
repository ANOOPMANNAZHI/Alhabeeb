# Server upload — 2026-10-01 batch (server_upload_files, 109 files)

Rehearsed on 2026-10-01 against `plms_live` (a fresh copy of the live database): all
14 migrations ran in this order without errors and every new screen opened.

Contents of the batch: message templates (A + C1–C4), Mail → Send (bulk send) with the
redesigned screens and wizard fix, building Taxable flag, municipal tax only for taxable
buildings, Summary → Add Municipality for renewed contracts, Receivables Follow-up,
bank-sheet rejected-cheque fixes.

---

## 0. Before you start

1. Take a database backup of live.
2. **Do NOT run a plain `php artisan migrate`.** On live, several older migrations were
   applied by hand from SQL files and are not recorded, so `migrate` would try to run them
   again and fail or duplicate data. Run only the files below, one at a time, with `--path`.
3. Check the id counters are not behind (a lagging counter makes a migration fail when it
   inserts a row). Run in psql / pgAdmin on the live database:

```sql
SELECT 'migrations' t, (SELECT max(id) FROM migrations) max_id, (SELECT last_value FROM migrations_id_seq) seq
UNION ALL SELECT 'menu', (SELECT max(id) FROM menu), (SELECT last_value FROM menu_id_seq)
UNION ALL SELECT 'permissions', (SELECT max(id) FROM permissions), (SELECT last_value FROM permissions_id_seq)
UNION ALL SELECT 'configuration', (SELECT max(id) FROM configuration), (SELECT last_value FROM configuration_id_seq);
```

   If any `seq` is **smaller** than `max_id`, fix that one before continuing, e.g.:

```sql
SELECT setval('migrations_id_seq', (SELECT max(id) FROM migrations));
```

## 1. Upload the files

Copy everything in `server_upload_files/` to the server (same paths).

## 2. Run the migrations — in this order, one command each

Run from the application folder. Stop at the first error and send the message.
Steps 2.9 and 2.10 should follow straight after 2.1–2.8: the every-minute
`bulk-messages:send` schedule errors until the bulk-send tables exist.

```bash
# message templates
php artisan migrate --force --path=database/migrations/2026_09_29_000000_create_message_templates_table.php
php artisan migrate --force --path=database/migrations/2026_09_29_000001_add_message_templates_menu.php
php artisan migrate --force --path=database/migrations/2026_09_30_000000_add_sms_enabled_and_message_log.php
php artisan migrate --force --path=database/migrations/2026_09_30_000001_add_message_log_menu.php
php artisan migrate --force --path=database/migrations/2026_09_30_000002_seed_c1_system_templates.php
php artisan migrate --force --path=database/migrations/2026_09_30_000003_seed_c2_system_templates.php
php artisan migrate --force --path=database/migrations/2026_09_30_000004_seed_c3_system_templates.php
php artisan migrate --force --path=database/migrations/2026_09_30_000005_seed_c4_system_templates.php

# bulk send (run 2.9 and 2.10 right away)
php artisan migrate --force --path=database/migrations/2026_09_30_200000_create_bulk_sends_tables.php
php artisan migrate --force --path=database/migrations/2026_09_30_200001_add_bulk_send_settings.php
php artisan migrate --force --path=database/migrations/2026_09_30_200002_add_bulk_send_menu.php
php artisan migrate --force --path=database/migrations/2026_09_30_200003_add_resent_send_id_to_bulk_sends.php

# building Taxable (needed before the municipality / building files are used)
php artisan migrate --force --path=database/migrations/2026_10_01_000000_add_is_taxable_to_buildings.php

# Receivables Follow-up
php artisan migrate --force --path=database/migrations/2026_10_01_100000_create_receivable_followups.php
```

Each command should print `Migrated: <name>`.

## 3. Clear the caches

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 4. Warm the Receivables Follow-up figures

```bash
php artisan receivables:refresh-followup
```

Prints e.g. `2026-09-30: 1377 contracts` (takes a few seconds to ~20 s).

## 5. Check the scheduler user

The cron entry that runs `php artisan schedule:run` every minute must run as the **same
user as the web server** (e.g. `www-data` / `apache`). Otherwise cache files written by the
scheduler cannot be updated by the website and the Refresh button on Receivables
Follow-up fails.

**Never** run `php artisan bulk-messages:send` by hand.

## 6. Verify

```sql
-- the 14 migrations are recorded
SELECT migration FROM migrations WHERE migration >= '2026_09_29_000000'
  AND migration NOT LIKE '2026_09_29_1%' AND migration NOT LIKE '2026_09_30_1%' ORDER BY id;   -- expect 14 rows

-- new menu rows
SELECT id, menu_name, route_name, status FROM menu
 WHERE route_name IN ('message-templates.index','message-log.index','bulk-send.index','receivable-followup.index');  -- expect 4

-- buildings default to taxable
SELECT count(*) FILTER (WHERE building_is_taxable) taxable, count(*) total FROM buildings;           -- taxable = total

-- message templates seeded
SELECT count(*) FROM message_templates;                                                             -- 35 on the rehearsal
```

Then open in the browser (as an admin): Mail → Templates, Mail → Send → New send,
Masters → Building (Taxable column), PLM Module → Operations → Receivables Follow-up,
Tenant Contract → Add Municipality on a taxable and a non-taxable building, and the bank
sheet review page (Rejected by bank tab).
