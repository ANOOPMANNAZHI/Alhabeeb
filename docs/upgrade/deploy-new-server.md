# Deploying PLMS (Laravel 13) to the new server

Branch: `platform-upgrade`. Do not deploy from `master` — it is still the Laravel 5.7 / PHP 7.x codebase.

## Server requirements

- **PHP 8.3 or 8.4** (CLI + FPM). Laravel 13's floor is 8.3.
  Extensions: `pdo_pgsql`, `pgsql`, `mbstring`, `xml`, `curl`, `zip`, `gd`, `bcmath`, `intl`, `fileinfo`, `soap` (Dynamics AX), `imagick` (QR codes as PNG — without it the QR download is served as SVG).
- Composer 2.
- PostgreSQL 13+ client libraries.
- **Java 11+** on `PATH` plus a JasperStarter install for the Jasper reports (see below). Node is *not* required; assets are pre-built in `public/`.
- Web root must be `public/` (the old deployment served the project root and exposed `vendor/`).
- Writable: `storage/`, `bootstrap/cache/`, `public/reports/`, `resources/reports/jasper/` (JasperStarter caches compiled `.jasper` files next to the `.jrxml`).

## JasperStarter

The Java runtime is not in git. Copy the old server's `vendor/cossou/jasperphp/src/JasperStarter/` directory (it has `bin/jasperstarter`, `lib/*.jar`, `jdbc/postgresql-*.jar`) to e.g. `/opt/jasperstarter/` and set in `.env`:

```
JASPERSTARTER_BIN=/opt/jasperstarter/bin/jasperstarter
```

If the variable is unset the code falls back to `packages/cossou/jasperphp/src/JasperStarter/bin/jasperstarter` (not present in git). Generated reports land in `public/reports/` and are served from `/reports/<file>`.

Two templates referenced by Sales enquiry reports were never present on the dev machine: `enquiryReportNew.jrxml` and `enquiryReportNew_Compo.jrxml`. Copy them from the old server's `vendor/cossou/jasperphp/examples/` into `resources/reports/jasper/` if they exist there.

## First deploy (git-based; `server_upload_files/` is retired)

```
git clone <repo> plms && cd plms
git checkout platform-upgrade            # or the release tag
cp .env.example .env                      # fill DB_*, MAIL_*, APP_URL, APP_KEY (copy APP_KEY from the old server so existing encrypted values keep working)
composer install --no-dev --optimize-autoloader
php artisan migrate --force               # adds activity_log.event / attribute_changes, Sanctum tokens, mobile_devices, tenant onboarding tables + menus (+ any pending feature migrations)
php artisan db:seed --class="Modules\BackOffice\Database\Seeders\OnboardingChecklistItemsSeeder"   # default move-in checklist (idempotent)
php artisan storage:link
php artisan permission:cache-reset
php artisan config:cache && php artisan route:cache && php artisan view:cache
chmod -R ug+rwX storage bootstrap/cache public/reports resources/reports/jasper
```

Cron: `* * * * * cd /path/plms && php artisan schedule:run >> /dev/null 2>&1` — the schedule now also runs `onboarding:sync` every 15 minutes (creates move-in checklists for contracts activated outside the hooked screens).

Mobile API (Phase 2, see `mobile-api.md`): set `MOBILE_API_ENABLED=true` when the app goes live; `storage/app/mobile/` holds the app's photos, signatures and PDFs — writable by the web user and part of the backup set. The office screens are **Masters → Mobile Devices**, **Masters → Onboarding Checklist Items** and **Operations → Tenant Onboarding**.

### `.env` keys that changed

| Old | New |
|---|---|
| `MAIL_DRIVER=smtp` | `MAIL_MAILER=smtp` (old key is ignored) |
| — | `JASPERSTARTER_BIN=…` (see above) |
| — | `LOG_DEPRECATIONS_CHANNEL=null` |
| `ACTIVITY_LOGGER_ENABLED` | still honoured; `ACTIVITYLOG_ENABLED` is the new name |
| — | `MOBILE_API_ENABLED=false` (Phase 2; flip to `true` for the ARE app) |

## Each release

```
git pull && composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## Data migration from the old server

`pg_dump` the old database, restore it, then `php artisan migrate --force`. Only the `activity_log` columns are new; spatie/permission 8 works with the existing tables unchanged.

If `migrate:status` shows older feature migrations as *Pending* on the restored DB (the old server was updated with SQL scripts rather than `migrate`), check each one before running it — most insert menu rows and would duplicate them. Mark them as run instead: `php artisan migrate --pretend` to see the SQL, then insert the filename into the `migrations` table.

## Behaviour notes

- `App\Support\Php7NoticeCompat` restores PHP 7 semantics for undefined variable/index/property reads and `foreach` over null (PHP 8 made them warnings, which Laravel turns into exceptions; the old server hid the notices). Each hit is logged at debug level with file:line so views can be cleaned up over time.
- Carbon 3 is strict about `add*/sub*` arguments: settings/request strings are cast to int at 56 call sites.

- JSON/AJAX dates stay `Y-m-d H:i:s` (`App\Concerns\LegacyDateSerialization` on every model).
- Carbon 3: `diffInMonths/Days()` return signed floats; every call site is wrapped as `(int) abs(...)` to keep the old integer values.
- Password hashes are re-hashed on login (`hashing.rehash_on_login`), which is a no-op for bcrypt users.
- `Modules/` layout, `App\User`, route names, view names and permission names are unchanged.

## Phase 1 verification record

| Check | Result |
|---|---|
| `composer install` from a clean `vendor/` | clean (Laravel 13.32, PHP 8.4.22 locally) |
| `php artisan about` / `module:list` | boots; 6 modules enabled |
| Routes | 1335 registered; every baseline route name present (4 "missing" names were commented-out routes) |
| Blade | all 814 views compile (`view:cache`) |
| PHPUnit 11 | 99 tests / 372 assertions green, incl. `/login` render through the real layout |
| Larastan level 0 | upgrade-caused findings fixed; remaining are pre-existing |
| `migrate` (activity_log v5) on a DB copy | DONE; models read 70 users / 509 permissions / 51,772 activity rows |
| Route sweep as super admin (`vendor/bin/phpunit --group smoke`) | 424 named parameterless GET routes: 0 errors. 15 `Route::resource` create() scaffolds that never had a view are counted separately; 12 dead/debug routes are skipped (listed in the test). |
| Compiled-view PHP lint (same test group) | 854 compiled views, 0 parse errors (3 nested ternaries fixed) |
| Excel 4 multi-sheet export | `normalManagementReportV2Stream` generated all 80 building workbooks + ZIP during the sweep |
| Interactive browser check (receipts, PDC, deposit refund, PDFs, QR, restricted role 403) | **still worth a manual pass before go-live** |

## Phase 2 verification record (2026-09-19)

| Check | Result |
|---|---|
| PHPUnit (unit + feature, `--exclude-group smoke --exclude-group smoke-one`) | 132 tests / 645 assertions green (99 Phase 1 + 33 Phase 2) |
| `--testsuite MobileApi` | 17 feature tests: auth/device policy, complaints end-to-end (claim → close → PDF), inspection end-to-end (draft → submit → 504), onboarding end-to-end, buildings, office devices screen |
| Desktop parity | `handoverUnassigned`, `handoverAssigned`, `takeoverForTermination`, `tenantTermination.index`, the three new office pages: 200 via `SMOKE_ROUTES` |
| Route sweep as super admin (`--group smoke`) | see the line below this table once the sweep has run |
| Two Phase 1 regressions found by the write-path tests and fixed | Laravel 13's own `Illuminate\Image` provider replaced Intervention on `'image'` (all `Image::make()` uploads); activitylog v5 rejected the ~180 `withProperties($model)` calls (all logged creates/updates). Both fixed centrally (`config/app.php`, `App\Support\LenientActivityLogger`). |
