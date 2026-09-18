# PLMS Platform Modernisation — Spec

**Date:** 2026-09-18
**Branch:** `platform-upgrade` (from `master`). **Not merged until Phases 1–3 are all complete.**

## Why

1. The live server is being replaced; new hosting ships PHP 8.2/8.3. Laravel 5.7 cannot run on PHP ≥ 7.4.
2. A mobile application is planned (see `docs/mobile-app/PLMS_Mobile_App_Phase1_Phase2.docx`). It needs a token-authenticated JSON API, which should be built on a supported framework.
3. Developers are not available for Laravel 5.7 / PHP 7.1 / jQuery-era code.

## Current state (measured 2026-09-18)

- Laravel 5.7, `php ^7.1.3`, PostgreSQL (`plms_new`), nwidart/laravel-modules 4, spatie/permission 2.25, spatie/activitylog 3, barryvdh/dompdf 0.8, maatwebsite/excel 3.1, davejamesmiller/breadcrumbs 5, kyslik/column-sortable 5.7, intervention/image 2, simple-qrcode 2, artisaninweb/laravel-soap 0.3.0.9 (Dynamics AX), mpdf, fpdf.
- `cossou/jasperphp` is used by 150 report call sites across 4 report controllers, with 117 distinct `.jrxml` templates — but it is **not in composer.json**; it was copied into git-ignored `vendor/cossou/jasperphp/` by hand, templates included.
- 6 modules (BackOffice, General, Maintenance, Masters, Menu, Sales), ~25k lines of PHP in `app/` + `Modules/`, 814 Blade views (421 extend `layouts.plms-app`), 52 Eloquent models using `protected $dates`.
- Tests: 13 unit test classes under `tests/Unit` (money-path calculators), no feature tests.
- Deployment is copy-based via `server_upload_files/`; vendor is not deployed from git.
- Local dev machine runs PHP 8.4.22.

## Phases

### Phase 1 — Framework & PHP (this plan: `docs/superpowers/plans/2026-09-18-phase1-laravel11-upgrade.md`)

Laravel 5.7 → **Laravel 11**, `php ^8.2`, in place on the existing application structure (Kernel-based bootstrap, `App\User`, `Modules/`), with every third-party package moved to a version that supports Laravel 11. Same UI, same behaviour, same database (only additive package migrations). Done when: `composer install` is clean, all routes register, `phpunit` is green, and the critical-path smoke checklist passes in the browser.

Non-goals for Phase 1: new template, API, restructuring into Laravel 11's slim skeleton, replacing jQuery, replacing JasperPHP.

### Phase 2 — Mobile API

Laravel Sanctum token auth; `Modules/*/Routes/api.php` under `/api/v1`; `Http/Controllers/Api/` per module reusing existing Services; API Resources; CORS. Scope is defined by the mobile-app document. Separate spec/plan.

### Phase 3 — Template

New admin layout side-by-side (`layouts/plms-app-v2`), Bootstrap 5 + jQuery compatible so existing inline JS partials survive; migrate module by module; both layouts coexist until the last view moves. Separate spec/plan.

## Global constraints

- PHP target `^8.2` (server will be 8.2 or 8.3; local is 8.4 — Laravel 11 ≥ 11.23 supports 8.4).
- PostgreSQL stays; no schema rewrites. Package-required migrations only.
- Keep `App\User`, `Modules\*` namespaces, route names, view names, and permission names unchanged — the menu table, breadcrumbs and 814 views depend on them.
- Pin `nesbot/carbon ^2.72` (Carbon 3 makes `diffInMonths()` return float; reports rely on integers).
- Eloquent date serialisation must stay `Y-m-d H:i:s` in JSON (Laravel 7 switched to ISO-8601; AJAX list pages format dates client-side).
- `master` continues to receive feature work; `platform-upgrade` merges `master` in periodically (`git merge master`), never the other way until Phase 3 is done.
