# Landlord Tax Invoice Report Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a new BackOffice report that generates a downloadable Tax Invoice PDF per landlord (one per eligible building, zipped if more than one), for a from_date/to_date period, billing Management Fees + Cleaning Charges + Repair & Maintenance Charges — matching the reference layout.

**Architecture:** Extract the existing per-building/per-month computation already built for Normal Management Report v2 into a reusable private method, so the new report computes its figures from the exact same income/collection/expense/cleaning-charge data without duplicating ~570 lines of SQL. The new report sums that data across whichever months the requested period touches, computes Management Fee (flat or percentage-of-income/collection per contract), and renders a dompdf invoice using the existing company letterhead partial. Delivery follows the established SSE-progress-bar + token-download pattern already used by Maintenance Invoice Report v2 and Normal Management Report v2.

**Tech Stack:** Laravel 5.x (BackOffice module), PostgreSQL, `barryvdh/laravel-dompdf` (`\PDF::loadView(...)`), Server-Sent Events for progress, jQuery UI autocomplete.

**Spec:** `docs/superpowers/specs/2026-09-06-landlord-tax-invoice-report-design.md`

## Global Constraints

- Only buildings in `BackOfficeReportController::$nmrV2BuildingIds` are eligible (Normal Management Report v2's existing list). Comprehensive-management buildings (`landlord_contract.management_id == 1`) are excluded.
- One PDF per eligible building under the selected landlord; a ZIP if more than one, a single PDF (no ZIP) if exactly one — matching the existing pattern in `normalManagementReportV2Stream()`.
- The `from_date`–`to_date` range sums whole calendar months it touches (no day-level proration beyond what Normal Management Report v2 already does internally for occupancy/rent).
- Invoice No stays blank (manual fill-in). Customer VATIN stays blank (no source column). Company VATIN is the static string `OM110001282X`.
- Invoice Date = Delivery Date = `to_date`; Payment Date = `to_date` + 1 month. All displayed `DD.MM.YYYY`.
- VAT is always 5% of each line's Amount; Total = Amount + VAT.
- Reuse `numberToWords()` from `config/function.php` for the amount-in-words line — do not add a new number-to-words dependency.
- Every currency figure prints with 3 decimals (`number_format($v, 3)`), matching the reference PDF (`120.000`, `25.000`, etc.) and this codebase's `numberFormat()` convention.
- Mirror every created/modified file into `server_upload_files/` at the same relative path (this project's deployment-staging convention) as the final task.

---

## Task 1: Extract `buildNormalManagementMonthData()` and extend it with Management Fee data

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`

**Interfaces:**
- Produces: `private function buildNormalManagementMonthData(\Modules\Masters\Entities\Building $building, int $year, array $monthsToPopulate): array` — returns a `$monthData` array keyed `1`..`12`, each value `['units' => array, 'old_outstanding' => array, 'expenses' => array, 'occupancy' => array, 'cleaning_charge' => float, 'landlord_contract' => object|null]`. `landlord_contract` is the matched `landlord_contract` row for that month (with `status`, `valid_from`, `valid_to`, `cleaning_charge`, `management_method`, `landlord_contract_percentage`, `landlord_contract_management_fee`), or `null` if no contract matched that month. Task 4 reads this new key; every other key is unchanged from today's behavior.

This is a pure refactor (extraction) plus one additive field. `normalManagementReportV2Stream()`'s behavior must be provably unchanged for every existing field.

- [ ] **Step 1: Extend the `landlordContractRows` batch query with Management Fee columns**

In `normalManagementReportV2Stream()`, find (currently at or near line 4989):

```php
        // ── Batch landlord contract cleaning charges for the year ─────────
        $landlordContractRows = DB::select("
            SELECT landlord_contract_status AS status,
                   landlord_contract_valid_from_date AS valid_from,
                   landlord_contract_valid_to_date AS valid_to,
                   landlord_contract_cleaning_charge AS cleaning_charge
            FROM landlord_contract
            WHERE building_id = ?
            ORDER BY landlord_contract_valid_from_date
        ", [$building->id]);
```

Replace with:

```php
        // ── Batch landlord contract cleaning charges + management fee for the year ─
        $landlordContractRows = DB::select("
            SELECT landlord_contract_status AS status,
                   landlord_contract_valid_from_date AS valid_from,
                   landlord_contract_valid_to_date AS valid_to,
                   landlord_contract_cleaning_charge AS cleaning_charge,
                   management_method,
                   landlord_contract_percentage,
                   landlord_contract_management_fee
            FROM landlord_contract
            WHERE building_id = ?
            ORDER BY landlord_contract_valid_from_date
        ", [$building->id]);
```

- [ ] **Step 2: Store the matched contract in `$monthData`, and reset it in the "not populated" branch**

Find (currently at or near line 5507-5524):

```php
                if ($matched !== null) {
                    $cleaningCharge = (float) $matched->cleaning_charge;
                }
            } else {
                $units = []; $oldOutstanding = []; $expenses = []; $cleaningCharge = 0;
                $occupancy = ['total_units' => 0, 'new_leased_residential' => 0, 'new_leased_commercial' => 0,
                    'occupied_residential' => 0, 'occupied_commercial' => 0, 'vacant_residential' => 0,
                    'vacant_commercial' => 0, 'evacuation_residential' => 0, 'evacuation_commercial' => 0];
            }

            $monthData[$m] = [
                'units'           => $units,
                'old_outstanding' => $oldOutstanding ?? [],
                'expenses'        => $expenses,
                'occupancy'       => $occupancy,
                'cleaning_charge' => $cleaningCharge,
            ];
        }
```

Replace with:

```php
                if ($matched !== null) {
                    $cleaningCharge = (float) $matched->cleaning_charge;
                }
            } else {
                $units = []; $oldOutstanding = []; $expenses = []; $cleaningCharge = 0; $matched = null;
                $occupancy = ['total_units' => 0, 'new_leased_residential' => 0, 'new_leased_commercial' => 0,
                    'occupied_residential' => 0, 'occupied_commercial' => 0, 'vacant_residential' => 0,
                    'vacant_commercial' => 0, 'evacuation_residential' => 0, 'evacuation_commercial' => 0];
            }

            $monthData[$m] = [
                'units'             => $units,
                'old_outstanding'   => $oldOutstanding ?? [],
                'expenses'          => $expenses,
                'occupancy'         => $occupancy,
                'cleaning_charge'   => $cleaningCharge,
                'landlord_contract' => $matched,
            ];
        }
```

- [ ] **Step 3: Extract the per-building block into a new private method**

The block to extract starts at (currently line 4938) `$monthData = [];` and ends at the closing `}` of the per-month loop, i.e. immediately after the `$monthData[$m] = [...];` array shown in Step 2 above (currently line 5524), and immediately BEFORE the blank line + `$safeName = preg_replace(...)` line that follows it. Everything from `$monthData = [];` through that closing `}` (inclusive) — the static per-building counts, the batch expense/landlord-contract/occupancy/new-leased queries, the legal-contract lookup, and the entire per-month `foreach (range(1, 12) as $m) { ... }` loop — moves into a new method. Nothing about the file-writing code below it (`$safeName = ...` onward) moves.

Add this new private method immediately before `normalManagementReportV2Stream()` (i.e. just above its `public function normalManagementReportV2Stream(Request $request)` line):

```php
/**
 * Builds the per-month data structure (units, old_outstanding, expenses,
 * occupancy, cleaning_charge, landlord_contract) for one building/year,
 * exactly as normalManagementReportV2Stream() computes it. Shared with
 * the Landlord Tax Invoice Report so both reuse the same figures.
 */
private function buildNormalManagementMonthData(\Modules\Masters\Entities\Building $building, int $year, array $monthsToPopulate): array
{
    $monthData = [];

    // [Step 3a below: paste the extracted block here verbatim, unindented by
    // one level to match this new method's body, with every occurrence of
    // "$monthsToPopulate" already referring to this method's parameter.]

    return $monthData;
}
```

Then in `normalManagementReportV2Stream()`, replace the entire extracted block (from `$monthData = [];` through the closing `}` of the per-month loop) with a single call:

```php
        $monthData = $this->buildNormalManagementMonthData($building, $year, $monthsToPopulate);
```

- [ ] **Step 3a: Paste the extracted block into the new method**

Use your editor/IDE to cut the exact block described in Step 3 (from the original file, before any edits) and paste it as the body of `buildNormalManagementMonthData()`, replacing the placeholder comment. Do not retype it by hand — copy it verbatim to avoid transcription errors in ~570 lines of business logic (prorating math, legal-contract handling, occupancy CTEs). The block already only references `$building`, `$year`, `$monthsToPopulate`, and local variables it defines itself — no other outer-scope variables are used inside it, so no additional parameters are needed.

- [ ] **Step 4: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/BackOfficeReportController.php"
```
Expected: `No syntax errors detected`

- [ ] **Step 5: Regression-verify Normal Management Report v2 is byte-identical**

Write a throwaway script (scratchpad, not committed) that calls `normalManagementReportV2Stream()`'s underlying logic for one known building (e.g. building id 83, "Al Manara Bldg") for a recent year, both BEFORE this task's changes (checkout the pre-task version of the file to a temp path, or use `git stash`/`git show <prev-commit>:path` to get the old content) and AFTER, and diffs the generated `.xlsx` files byte-for-byte (or at minimum, diffs the `NormalManagementConsolidateSheet::array()` output rows). They must be identical except for whatever the new `landlord_contract` key adds internally (which no existing consumer reads yet). The simplest reliable approach: temporarily instantiate `BackOfficeReportController` and call a small reflection-based invocation of the private method before/after, or — simpler — since this is a pure cut-paste with one additive field, run the full SSE stream via HTTP (or a fake-request script matching the pattern used for prior reports in this project) for that one building/year, save both outputs, and `diff -q` them after stripping the file's internal timestamp/generation metadata if any exists (XLSX files are zips; compare via `unzip -p file.xlsx xl/worksheets/sheet1.xml` for a stable text comparison, or just confirm the `NormalManagementConsolidateSheet::array()` rows match by dumping both to JSON and diffing).

- [ ] **Step 6: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/BackOfficeReportController.php
git commit -m "refactor: extract buildNormalManagementMonthData() from Normal Management Report v2, add landlord_contract to its output"
```

---

## Task 2: Migration — register the new report's menu entry

**Files:**
- Create: `database/migrations/2026_09_06_000002_add_landlord_tax_invoice_report_menu.php`
- Create (mirror for deployment): `server_upload_files/database/migrations/2026_09_06_000002_add_landlord_tax_invoice_report_menu.php`

**Interfaces:**
- Produces: a `menu` row with `route_name` = `showLandlordTaxInvoiceReport`, and a `permissions` row named `view_landlord_tax_invoice_report` granted to every existing role. Task 3's controller route and Task 7's `@can(...)` check both depend on this exact permission name.

- [ ] **Step 1: Write the migration**

```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddLandlordTaxInvoiceReportMenu extends Migration
{
    public function up()
    {
        $parentMenuId = DB::table('menu')->where('id', 183)->value('id') ?? 0;

        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Landlord Tax Invoice Report',
            'menu_icon'   => 'fa-file-text-o',
            'route_name'  => 'showLandlordTaxInvoiceReport',
            'url_key'     => 'showLandlordTaxInvoiceReport',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('permissions')->insert([
            'name'       => 'view_landlord_tax_invoice_report',
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permission = DB::table('permissions')
            ->where('name', 'view_landlord_tax_invoice_report')
            ->first();

        if ($permission) {
            $roles = DB::table('roles')->pluck('id');
            foreach ($roles as $roleId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permission->id,
                    'role_id'       => $roleId,
                ]);
            }
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $menu = DB::table('menu')->where('route_name', 'showLandlordTaxInvoiceReport')->first();
        if ($menu) {
            $permission = DB::table('permissions')->where('menu_id', $menu->id)->first();
            if ($permission) {
                DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
                DB::table('permissions')->where('id', $permission->id)->delete();
            }
            DB::table('menu')->where('id', $menu->id)->delete();
        }

        app('cache')->forget('spatie.permission.cache');
    }
}
```

- [ ] **Step 2: Run the migration**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=database/migrations/2026_09_06_000002_add_landlord_tax_invoice_report_menu.php --force
```
Expected: `Migrated: 2026_09_06_000002_add_landlord_tax_invoice_report_menu`

- [ ] **Step 3: Verify**

Run a one-off script confirming a `menu` row exists with `route_name = 'showLandlordTaxInvoiceReport'` and a `permissions` row named `view_landlord_tax_invoice_report` exists and is granted to at least one role.

- [ ] **Step 4: Mirror to `server_upload_files/` and commit**

```bash
cp "database/migrations/2026_09_06_000002_add_landlord_tax_invoice_report_menu.php" "server_upload_files/database/migrations/2026_09_06_000002_add_landlord_tax_invoice_report_menu.php"
git add database/migrations/2026_09_06_000002_add_landlord_tax_invoice_report_menu.php server_upload_files/database/migrations/2026_09_06_000002_add_landlord_tax_invoice_report_menu.php
git commit -m "feat: add Landlord Tax Invoice Report menu entry and permission"
```

---

## Task 3: Controller — `showLandlordTaxInvoiceReport()` (filter form) and eligible-buildings helper

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`

**Interfaces:**
- Produces: `public function showLandlordTaxInvoiceReport()` — renders the filter form view (Task 6). `private function landlordTaxInvoiceEligibleContracts(int $vendorId): \Illuminate\Support\Collection` — returns `landlord_contract` rows (with their `building`) for the given vendor, restricted to buildings in `self::$nmrV2BuildingIds` and `management_id != 1` (excluding Comprehensive). Task 4's stream method calls this to determine which buildings to generate invoices for.

- [ ] **Step 1: Add the `use` import for `Vendor` and `LandlordContract`**

At the top of `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`, find the existing `use` block (around line 5-27) and add these two lines alongside the other `use Modules\...\Entities\...;` imports:

```php
use Modules\Masters\Entities\Vendor;
use Modules\Sales\Entities\LandlordContract;
```

- [ ] **Step 2: Add `showLandlordTaxInvoiceReport()` and `landlordTaxInvoiceEligibleContracts()`**

Add these two methods immediately before `buildNormalManagementMonthData()` (i.e. just above the method Task 1 created), inside a new comment block:

```php
/*
 *
 * Landlord Tax Invoice Report
 *
 */

public function showLandlordTaxInvoiceReport()
{
    return view('backoffice::Reports.landlord_tax_invoice_report');
}

/**
 * Landlord contracts for the given vendor, restricted to buildings in the
 * Normal Management Report v2 building list and excluding Comprehensive
 * management (management_id == 1), per the report's explicit scope.
 */
private function landlordTaxInvoiceEligibleContracts(int $vendorId): \Illuminate\Support\Collection
{
    return LandlordContract::with('buildingInfo')
        ->where('vendor_id', $vendorId)
        ->where('management_id', '!=', 1)
        ->whereIn('building_id', self::$nmrV2BuildingIds)
        ->get()
        ->unique('building_id')
        ->values();
}
```

- [ ] **Step 3: Confirm the `LandlordContract` model has a `buildingInfo` relation**

Run: `grep -n "function buildingInfo\|function building(" Modules/Sales/Entities/LandlordContract.php`

If no such relation exists, add one instead of relying on a wrong name — open `Modules/Sales/Entities/LandlordContract.php` and add:

```php
public function buildingInfo()
{
    return $this->belongsTo(\Modules\Masters\Entities\Building::class, 'building_id');
}
```

(Skip this step's addition if the grep confirms a relation named `buildingInfo` already exists — do not add a duplicate.)

- [ ] **Step 4: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/BackOfficeReportController.php"
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Entities/LandlordContract.php"
git add Modules/BackOffice/Http/Controllers/BackOfficeReportController.php Modules/Sales/Entities/LandlordContract.php
git commit -m "feat: add Landlord Tax Invoice Report show endpoint and eligible-contracts lookup"
```

(If Step 3 made no changes to `LandlordContract.php`, omit it from the `git add`.)

---

## Task 4: Controller — `landlordTaxInvoiceReportStream()` (SSE, computation + PDF generation)

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`

**Interfaces:**
- Consumes: `buildNormalManagementMonthData()` (Task 1), `landlordTaxInvoiceEligibleContracts()` (Task 3), the `numberToWords()` global helper, the Blade view `backoffice::Reports.landlord_tax_invoice_pdf` (Task 7).
- Produces: `public function landlordTaxInvoiceReportStream(Request $request)` — SSE endpoint, query params `from_date`, `to_date`, `vendor_id`. Emits `{pct, msg}` progress events, and a final `{pct:100, done:true, token}` event — `token` addresses either the single generated PDF or the ZIP (when more than one building matched), both stored under the same `ltir_dl_<token>` cache key by Task 5's download endpoint. This mirrors Normal Management Report v2's single-token pattern (not Maintenance Invoice Report v2's separate reportToken/zipToken pattern, which doesn't apply here since there's only one file type to deliver).

- [ ] **Step 1: Add the month-range helper**

Add this private method immediately after `landlordTaxInvoiceEligibleContracts()` (Task 3):

```php
/**
 * Every {year, month} pair the given date range touches, in order.
 * E.g. 2026-06-15..2026-07-10 returns [{2026,6}, {2026,7}].
 */
private function monthsTouchedByRange(string $fromDate, string $toDate): array
{
    $result = [];
    $cursor = new \DateTime(date('Y-m-01', strtotime($fromDate)));
    $end    = new \DateTime(date('Y-m-01', strtotime($toDate)));
    while ($cursor <= $end) {
        $result[] = ['year' => (int) $cursor->format('Y'), 'month' => (int) $cursor->format('n')];
        $cursor->modify('+1 month');
    }
    return $result;
}
```

- [ ] **Step 2: Add the invoice-line computation helper**

Add this private method immediately after `monthsTouchedByRange()`:

```php
/**
 * Sums Management Fee, Cleaning Charges, and Repair & Maintenance for one
 * building over the given date range, reusing buildNormalManagementMonthData()
 * per distinct year the range touches. The applicable landlord contract can
 * change month to month, so each total is accumulated per month rather than
 * assuming one contract covers the whole range. Returns
 * ['management_fee' => float, 'cleaning_charge' => float, 'repair_maintenance' => float,
 *  'period_label' => string].
 */
private function landlordTaxInvoiceLineAmounts(\Modules\Masters\Entities\Building $building, string $fromDate, string $toDate): array
{
    $monthsTouched = $this->monthsTouchedByRange($fromDate, $toDate);

    $byYear = [];
    foreach ($monthsTouched as $mt) {
        $byYear[$mt['year']][] = $mt['month'];
    }

    $managementFee = 0.0;
    $totalCleaning = 0.0;
    $totalExpenses = 0.0;
    foreach ($byYear as $yr => $months) {
        $monthData = $this->buildNormalManagementMonthData($building, $yr, $months);
        foreach ($months as $m) {
            $data = $monthData[$m] ?? null;
            if (!$data) continue;

            foreach ($data['expenses'] ?? [] as $exp) {
                $totalExpenses += (float) $exp->expense_amount;
            }
            $totalCleaning += (float) ($data['cleaning_charge'] ?? 0);

            $lc = $data['landlord_contract'] ?? null;
            if ($lc === null) continue;

            if ((int) $lc->management_method === 2) {
                $managementFee += (float) $lc->landlord_contract_management_fee;
                continue;
            }

            // Percentage type: apply to this month's income or collection only.
            $monthIncome     = 0.0;
            $monthCollection = 0.0;
            foreach ($data['units'] ?? [] as $u) {
                $monthIncome     += (float) ($u->income_amount ?? 0);
                $monthCollection += (float) ($u->collection_amount ?? 0);
            }
            foreach ($data['old_outstanding'] ?? [] as $u) {
                $monthCollection += (float) ($u->collection_amount ?? 0);
            }
            $basis = ((int) $lc->landlord_contract_percentage === 2) ? $monthCollection : $monthIncome;
            $managementFee += round($basis * ((float) $lc->landlord_contract_management_fee / 100), 3);
        }
    }

    // Period label for the line descriptions.
    $fromTs = strtotime($fromDate);
    $toTs   = strtotime($toDate);
    $isCleanCalendarMonth = date('Y-m-d', $fromTs) === date('Y-m-01', $fromTs)
        && date('Y-m-d', $toTs) === date('Y-m-t', $toTs)
        && date('Y-m', $fromTs) === date('Y-m', $toTs);
    $periodLabel = $isCleanCalendarMonth
        ? strtoupper(date('M', $fromTs)) . "'" . date('y', $fromTs)
        : date('d.m.y', $fromTs) . '-' . date('d.m.y', $toTs);

    return [
        'management_fee'     => round($managementFee, 3),
        'cleaning_charge'    => round($totalCleaning, 3),
        'repair_maintenance' => round($totalExpenses, 3),
        'period_label'       => $periodLabel,
    ];
}
```

- [ ] **Step 3: Add the amount-in-words helper**

Add this private method immediately after `landlordTaxInvoiceLineAmounts()`:

```php
/**
 * "Omani Riyals <words> & Bzs <NNN>/1000 only" — same convention as
 * RentReceiptGenerationController::printPreview(), built on the existing
 * numberToWords() global helper (config/function.php).
 */
private function landlordTaxInvoiceAmountInWords(float $total): string
{
    $parts   = explode('.', number_format($total, 3, '.', ''));
    $whole   = (int) $parts[0];
    $baisa   = $parts[1] ?? '000';
    return 'Omani Riyals ' . ucwords(numberToWords($whole)) . ' & Bzs ' . $baisa . '/1000 only';
}
```

- [ ] **Step 4: Add the SSE stream endpoint**

Add this public method immediately after `showLandlordTaxInvoiceReport()` (Task 3, Step 2) — i.e. before `landlordTaxInvoiceEligibleContracts()`:

```php
public function landlordTaxInvoiceReportStream(Request $request)
{
    // ── Kill all output buffering (critical for Apache + Windows / Laragon) ──
    @ini_set('output_buffering', 'off');
    @ini_set('zlib.output_compression', false);
    while (ob_get_level()) @ob_end_clean();
    ob_implicit_flush(true);

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache, no-store');
    header('X-Accel-Buffering: no');
    header('Content-Encoding: none');
    header('Connection: keep-alive');

    session()->save();

    ini_set('memory_limit', '512M');
    set_time_limit(600);

    echo ': ' . str_repeat(' ', 4096) . "\n\n";
    flush();

    $send = function (array $data): void {
        echo 'data: ' . json_encode($data) . "\n\n";
        flush();
    };

    $fromDate = $request->input('from_date');
    $toDate   = $request->input('to_date');
    $vendorId = (int) $request->input('vendor_id');

    $vendor = Vendor::find($vendorId);
    if (!$vendor || !$fromDate || !$toDate) {
        $send(['pct' => 100, 'msg' => 'Invalid request — missing landlord or dates.', 'done' => true, 'error' => true]);
        exit;
    }

    $contracts = $this->landlordTaxInvoiceEligibleContracts($vendorId);
    if ($contracts->isEmpty()) {
        $send(['pct' => 100, 'msg' => 'No eligible (Normal-management) buildings found for this landlord.', 'done' => true, 'error' => true]);
        exit;
    }

    $totalBuildings = $contracts->count();
    $send(['pct' => 1, 'msg' => 'Preparing — ' . $totalBuildings . ' invoice' . ($totalBuildings !== 1 ? 's' : '') . ' to generate']);

    $tempDir = storage_path('app/temp/landlord_tax_invoice_' . uniqid());
    if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);
    $files = [];

    foreach ($contracts as $idx => $contract) {
        $building = $contract->buildingInfo;
        if (!$building) continue;

        $pctNow = (int) round(1 + ($idx / $totalBuildings) * 88);
        $send(['pct' => $pctNow, 'msg' => 'Processing ' . $building->building_name . ' (' . ($idx + 1) . ' / ' . $totalBuildings . ')']);

        $amounts = $this->landlordTaxInvoiceLineAmounts($building, $fromDate, $toDate);

        $mgmtAmount    = $amounts['management_fee'];
        $cleanAmount   = $amounts['cleaning_charge'];
        $repairAmount  = $amounts['repair_maintenance'];
        $periodLabel   = $amounts['period_label'];

        $lines = [
            ['desc' => 'MANAGEMENT FEES FOR ' . $periodLabel, 'amount' => $mgmtAmount],
            ['desc' => "CLEANING CHARGES FOR " . $periodLabel, 'amount' => $cleanAmount],
            ['desc' => 'REPAIR AND MAINTENANCE CHARGES', 'amount' => $repairAmount],
        ];
        foreach ($lines as &$line) {
            $line['qty']   = 1.000;
            $line['unit_price'] = $line['amount'];
            $line['vat']   = round($line['amount'] * 0.05, 3);
            $line['total'] = round($line['amount'] + $line['vat'], 3);
        }
        unset($line);

        $totalAmount = round(array_sum(array_column($lines, 'amount')), 3);
        $totalVat    = round(array_sum(array_column($lines, 'vat')), 3);
        $totalDue    = round(array_sum(array_column($lines, 'total')), 3);

        $data = [
            'vendor'       => $vendor,
            'building'     => $building,
            'lines'        => $lines,
            'totalAmount'  => $totalAmount,
            'totalVat'     => $totalVat,
            'totalDue'     => $totalDue,
            'invoiceDate'  => date('d.m.Y', strtotime($toDate)),
            'deliveryDate' => date('d.m.Y', strtotime($toDate)),
            'paymentDate'  => date('d.m.Y', strtotime($toDate . ' +1 month')),
            'amountInWords' => $this->landlordTaxInvoiceAmountInWords($totalDue),
        ];

        $pdf = \PDF::loadView('backoffice::Reports.landlord_tax_invoice_pdf', $data)->setPaper('a4', 'portrait');

        $safeVendor   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $vendor->vendor_name);
        $safeBuilding = preg_replace('/[^A-Za-z0-9_\-]/', '_', $building->building_name);
        $fileName     = $safeVendor . '_' . $safeBuilding . '_TaxInvoice.pdf';
        $filePath     = $tempDir . '/' . $fileName;
        $pdf->save($filePath);

        $files[] = ['path' => $filePath, 'name' => $fileName];

        $send(['pct' => (int) round(1 + (($idx + 1) / $totalBuildings) * 88), 'msg' => 'Done: ' . $building->building_name]);
    }

    if (empty($files)) {
        $send(['pct' => 100, 'msg' => 'No invoices could be generated.', 'done' => true, 'error' => true]);
        exit;
    }

    if (count($files) === 1) {
        $finalPath = $files[0]['path'];
        $finalName = $files[0]['name'];
    } else {
        $send(['pct' => 92, 'msg' => 'Creating ZIP archive…']);
        $zipFileName = 'Landlord_Tax_Invoices_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $vendor->vendor_name) . '.zip';
        $zipPath     = $tempDir . '/' . $zipFileName;
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            foreach ($files as $f) { $zip->addFile($f['path'], $f['name']); }
            $zip->close();
        }
        foreach ($files as $f) {
            if (file_exists($f['path'])) unlink($f['path']);
        }
        $finalPath = $zipPath;
        $finalName = $zipFileName;
    }

    $token = uniqid('ltir_dl_', true);
    cache()->put('ltir_dl_' . $token, ['path' => $finalPath, 'name' => $finalName], now()->addMinutes(5));

    $send(['pct' => 100, 'msg' => 'Complete! Starting download…', 'done' => true, 'token' => $token]);
    exit;
}
```

- [ ] **Step 5: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/BackOfficeReportController.php"
```

- [ ] **Step 6: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/BackOfficeReportController.php
git commit -m "feat: add Landlord Tax Invoice Report SSE generation endpoint"
```

---

## Task 5: Controller — `landlordTaxInvoiceReportDownload()`

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`

**Interfaces:**
- Consumes: the `ltir_dl_<token>` cache key written by Task 4.
- Produces: `public function landlordTaxInvoiceReportDownload(string $token)`.

- [ ] **Step 1: Add the download endpoint**

Add this method immediately after `landlordTaxInvoiceReportStream()`:

```php
public function landlordTaxInvoiceReportDownload(string $token)
{
    $info = cache()->get('ltir_dl_' . $token);
    abort_if(!$info || !file_exists($info['path']), 404, 'File not found or expired.');
    return response()->download($info['path'], $info['name'])->deleteFileAfterSend(true);
}
```

- [ ] **Step 2: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/BackOfficeReportController.php"
git add Modules/BackOffice/Http/Controllers/BackOfficeReportController.php
git commit -m "feat: add Landlord Tax Invoice Report download endpoint"
```

---

## Task 6: Blade view — filter form with SSE progress bar

**Files:**
- Create: `Modules/BackOffice/Resources/views/Reports/landlord_tax_invoice_report.blade.php`

**Interfaces:**
- Consumes: route `landlordTaxInvoiceReportStream` (Task 4), route `landlordTaxInvoiceReportDownload` (Task 5), route `landlordAutocompleteCode` (existing, `Modules/Sales/Http/Controllers/LandlordContractController.php:1022`).
- Produces: a form posting to the stream endpoint via `EventSource`, matching the exact pattern already used by `maintenance_invoice_report_v2.blade.php`.

- [ ] **Step 1: Write the view**

```blade
@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<style>
  #ltir-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 9999;
    align-items: center;
    justify-content: center;
  }
  #ltir-overlay.active { display: flex; }
  #ltir-card {
    background: #fff;
    border-radius: 10px;
    padding: 36px 44px;
    min-width: 380px;
    max-width: 480px;
    width: 90%;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.22);
  }
  #ltir-card h5 { color: #1F4E79; font-weight: 700; font-size: 16px; margin-bottom: 6px; }
  #ltir-msg { color: #555; font-size: 13px; margin-bottom: 18px; min-height: 18px; }
  #ltir-track { background: #e2eaf3; border-radius: 20px; height: 18px; overflow: hidden; margin-bottom: 10px; }
  #ltir-bar  { height: 100%; width: 0%; background: linear-gradient(90deg,#1F4E79,#2E75B6); border-radius: 20px; transition: width .4s ease; }
  #ltir-pct  { font-size: 20px; font-weight: 700; color: #1F4E79; }
  #ltir-note { font-size: 11px; color: #999; margin-top: 14px; }
</style>
@endsection
@section('content')

<div id="ltir-overlay">
  <div id="ltir-card">
    <h5>Generating Invoice</h5>
    <div id="ltir-msg">Preparing…</div>
    <div id="ltir-track"><div id="ltir-bar"></div></div>
    <div id="ltir-pct">0%</div>
    <div id="ltir-note">Please keep this tab open until the download starts.</div>
  </div>
</div>

<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left">
      <div class="page-title">Landlord Tax Invoice Report</div>
    </div>
    {{ Breadcrumbs::render('showLandlordTaxInvoiceReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      @can('view_landlord_tax_invoice_report')
      <form id="ltir_form" class="form-horizontal" autocomplete="off">
        <div class="dataSearchBox">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="from_date">From Date<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="from_date" name="from_date" required>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="to_date">To Date<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                  <input type="date" class="form-control" id="to_date" name="to_date" required>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="vendor_name">Landlord<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-user icn-add" aria-hidden="true"></i>
                  <input type="text" class="form-control" id="vendor_name" placeholder="Enter Landlord Name" required autocomplete="off">
                  <input type="hidden" id="vendor_id" name="vendor_id">
                </div>
              </div>
            </div>
            <div class="w-100"></div>
            <div class="col">
              <div class="w-100"></div>
              <button type="submit" id="ltir_submit" class="btn btn-primary">Generate</button>
            </div>
          </div>
        </div>
      </form>
      @endcan
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function () {
    $.validator.addMethod("greaterThan",
      function (value, element, params) {
        if (!/Invalid|NaN/.test(new Date(value))) {
          return new Date(value) > new Date($(params).val());
        }
        return isNaN(value) && isNaN($(params).val())
          || (Number(value) > Number($(params).val()));
      }, 'Must be greater than From Date.');
    $('#ltir_form').validate({
      rules: { to_date: { greaterThan: '#from_date' } }
    });

    $('#vendor_name').autocomplete({
      source: '{!!URL::route('landlordAutocompleteCode')!!}',
      minLength: 2,
      autoFocus: true,
      change: function (e, ui) {
        if (ui.item == null || ui.item == undefined) {
          $('#vendor_name').val('');
          $('#vendor_id').val('');
        } else {
          $('#vendor_id').val(ui.item.ids);
        }
      }
    });

    var $overlay = $('#ltir-overlay');
    var $bar     = $('#ltir-bar');
    var $msg     = $('#ltir-msg');
    var $pct     = $('#ltir-pct');
    var streamUrl    = '{{ route("landlordTaxInvoiceReportStream") }}';
    var downloadBase = '{{ url("landlordTaxInvoiceReportDownload") }}';

    function setProgress(p, m) {
      $bar.css('width', p + '%');
      $pct.text(p + '%');
      if (m) $msg.text(m);
    }

    $('#ltir_form').on('submit', function (e) {
      e.preventDefault();

      if (!$(this).valid()) return;
      if (!$('#vendor_id').val()) {
        alert('Please select a landlord from the suggestions list.');
        return;
      }

      var params = new URLSearchParams({
        from_date: $('#from_date').val(),
        to_date:   $('#to_date').val(),
        vendor_id: $('#vendor_id').val()
      });

      setProgress(0, 'Preparing…');
      $overlay.addClass('active');

      var evtSource = new EventSource(streamUrl + '?' + params.toString());

      evtSource.onmessage = function (e) {
        try {
          var data = JSON.parse(e.data);
          setProgress(data.pct || 0, data.msg || '');
          if (data.error) {
            evtSource.close();
            $overlay.removeClass('active');
            alert(data.msg || 'An error occurred while generating the invoice.');
            return;
          }
          if (data.done) {
            evtSource.close();
            window.location.href = downloadBase + '/' + data.token;
            setTimeout(function () { $overlay.removeClass('active'); }, 1200);
          }
        } catch (err) {}
      };

      evtSource.onerror = function () {
        evtSource.close();
        $overlay.removeClass('active');
        alert('An error occurred while generating the invoice. Please try again.');
      };
    });
  });
</script>
@endsection
```

- [ ] **Step 2: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/Reports/landlord_tax_invoice_report.blade.php"
```
Expected: `No syntax errors detected` (Blade files lint as plain PHP for unmatched braces; this catches gross syntax errors, not `@` directive mistakes — visually re-check `@can`/`@endcan` pairing).

- [ ] **Step 3: Commit**

```bash
git add Modules/BackOffice/Resources/views/Reports/landlord_tax_invoice_report.blade.php
git commit -m "feat: add Landlord Tax Invoice Report filter form"
```

---

## Task 7: Blade view — the Tax Invoice PDF template

**Files:**
- Create: `Modules/BackOffice/Resources/views/Reports/landlord_tax_invoice_pdf.blade.php`

**Interfaces:**
- Consumes: the `$data` array built in Task 4 Step 5 (`vendor`, `building`, `lines`, `totalAmount`, `totalVat`, `totalDue`, `invoiceDate`, `deliveryDate`, `paymentDate`, `amountInWords`).

- [ ] **Step 1: Write the view**

This reuses the exact bilingual letterhead block from `Modules/BackOffice/Resources/views/Receipt/print_view.blade.php:12-29` (logo, Arabic/English address, C.R. No, Finance code), followed by the invoice table matching the reference layout.

```blade
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='UTF-8'>
<title>Tax Invoice</title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; }
  #identity { overflow: hidden; margin-bottom: 10px; }
  #logo { float: left; width: 260px; }
  #logo img { height: 70px; width: 250px; }
  #address { float: left; width: 500px; margin-bottom: 10px; font-size: 11px; line-height: 1.5; }
  .invoice-title { text-align: center; font-size: 18px; font-weight: bold; margin: 10px 0 16px; }
  table.header-fields { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  table.header-fields td { vertical-align: top; padding: 2px 4px; font-size: 12px; }
  table.header-fields .label { font-weight: bold; white-space: nowrap; }
  table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
  table.items th, table.items td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; }
  table.items th { text-align: center; font-weight: bold; }
  table.items td.desc { text-align: left; height: 60px; vertical-align: top; }
  table.items td.num { text-align: right; }
  table.items tr.total-row td { font-weight: bold; }
  .words-row td { font-weight: bold; border: 1px solid #000; border-top: none; padding: 6px 8px; font-size: 11px; }
  .signature-row { margin-top: 60px; overflow: hidden; }
  .signature-row .received { float: left; }
  .signature-row .for-company { float: right; font-weight: bold; color: #1F4E79; }
</style>
</head>
<body>

<div id="identity">
  <div id="logo">
    <img src="{{asset('public/img/logo-print.png')}}" alt="logo">
  </div>
  <div id="address">
    <b style="text-align:right">الحبيب وشركاه ش . م .م</b></br>
    <b>Al Habib & Co. L.L.C</b></br>
    <span style="text-align:right">
    ص. ب: 2663, روي الرمز البريدي : 112 , مسقط , سلطنة عمان
    </span></br>
    <span style="text-align:right">
    هاتف :96824700247+|فاكس :96824703666+| س.ت. :1131575|المالية 10317401
    </span></br>
    P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman
    </br>
    Tel:+968 247 00247 | Fax:+968 247 03 666
    </br> C.R. No: 1131575 | Finance code : 10317401
  </div>
</div>
<div style="clear:both"></div>

<div class="invoice-title">Tax Invoice</div>

<table class="header-fields">
  <tr>
    <td class="label">CUSTOMER NAME:</td>
    <td>{{ $vendor->vendor_name }}</td>
    <td class="label">INVOICE NO:</td>
    <td></td>
  </tr>
  <tr>
    <td class="label">BUILDING NAME</td>
    <td>{{ $building->building_name }}</td>
    <td class="label">INVOICE DATE:</td>
    <td>{{ $invoiceDate }}</td>
  </tr>
  <tr>
    <td class="label">CUSTOMER ID :</td>
    <td>{{ $vendor->vendor_code }}</td>
    <td class="label">DELIVERY DATE</td>
    <td>{{ $deliveryDate }}</td>
  </tr>
  <tr>
    <td class="label">ADDRESS:</td>
    <td>{{ $vendor->vendor_contact_address }}</td>
    <td class="label">PAYMENT DATE</td>
    <td>{{ $paymentDate }}</td>
  </tr>
  <tr>
    <td class="label">VATIN NO.</td>
    <td></td>
    <td class="label">VATIN NO.</td>
    <td>OM110001282X</td>
  </tr>
</table>

<table class="items">
  <thead>
    <tr>
      <th style="width:40%">DESCRIPTION</th>
      <th>Quantity<br>No's</th>
      <th>Unit Price<br>(OMR)</th>
      <th>Amount<br>(OMR)</th>
      <th>VAT 5%<br>(OMR)</th>
      <th>Total<br>(OMR)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($lines as $line)
    <tr>
      <td class="desc">{{ $line['desc'] }}</td>
      <td class="num">{{ number_format($line['qty'], 3) }}</td>
      <td class="num">{{ number_format($line['unit_price'], 3) }}</td>
      <td class="num">{{ number_format($line['amount'], 3) }}</td>
      <td class="num">{{ number_format($line['vat'], 3) }}</td>
      <td class="num">{{ number_format($line['total'], 3) }}</td>
    </tr>
    @endforeach
    <tr class="total-row">
      <td colspan="3">Total</td>
      <td class="num">{{ number_format($totalAmount, 3) }}</td>
      <td class="num">{{ number_format($totalVat, 3) }}</td>
      <td class="num">{{ number_format($totalDue, 3) }}</td>
    </tr>
  </tbody>
</table>
<table class="items" style="margin-top:0">
  <tr class="words-row">
    <td colspan="6">{{ $amountInWords }}</td>
  </tr>
</table>

<div class="signature-row">
  <div class="received">Received By:</div>
  <div class="for-company">For AL HABIB & CO. LLC</div>
</div>

</body>
</html>
```

- [ ] **Step 2: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/Reports/landlord_tax_invoice_pdf.blade.php"
```

- [ ] **Step 3: Commit**

```bash
git add Modules/BackOffice/Resources/views/Reports/landlord_tax_invoice_pdf.blade.php
git commit -m "feat: add Landlord Tax Invoice PDF template"
```

---

## Task 8: Routes and breadcrumbs

**Files:**
- Modify: `Modules/BackOffice/Routes/web.php`
- Modify: `Modules/BackOffice/Routes/breadcrumbs.php`

**Interfaces:**
- Consumes: `showLandlordTaxInvoiceReport`, `landlordTaxInvoiceReportStream`, `landlordTaxInvoiceReportDownload` controller methods (Tasks 3-5).
- Produces: named routes the Blade views (Tasks 6-7) and menu migration (Task 2) reference.

- [ ] **Step 1: Add routes**

In `Modules/BackOffice/Routes/web.php`, find the existing Normal Management Report v2 route block (search for `normalManagementReportV2Generate`) and add these three lines immediately after that block, inside the same route group:

```php
	Route::get('showLandlordTaxInvoiceReport', 'BackOfficeReportController@showLandlordTaxInvoiceReport')->name('showLandlordTaxInvoiceReport');
	Route::get('landlordTaxInvoiceReportStream', 'BackOfficeReportController@landlordTaxInvoiceReportStream')->name('landlordTaxInvoiceReportStream');
	Route::get('landlordTaxInvoiceReportDownload/{token}', 'BackOfficeReportController@landlordTaxInvoiceReportDownload')->name('landlordTaxInvoiceReportDownload');
```

Verify indentation/tab-vs-space matches the surrounding lines in that file before committing (this file mixes styles across historical edits — match whatever the three lines immediately above your insertion point use).

- [ ] **Step 2: Add breadcrumb**

In `Modules/BackOffice/Routes/breadcrumbs.php`, find the `showNormalManagementReportV2` breadcrumb block and add immediately after it:

```php
Breadcrumbs::for('showLandlordTaxInvoiceReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Tax Invoice Report', route('showLandlordTaxInvoiceReport'));
});
```

- [ ] **Step 3: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Routes/web.php"
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Routes/breadcrumbs.php"
git add Modules/BackOffice/Routes/web.php Modules/BackOffice/Routes/breadcrumbs.php
git commit -m "feat: register Landlord Tax Invoice Report routes and breadcrumb"
```

---

## Task 9: End-to-end verification

**Files:** none (verification only)

- [ ] **Step 1: Verify the eligible-buildings lookup**

Write a throwaway script (scratchpad, not committed) that calls `landlordTaxInvoiceEligibleContracts()` for a vendor known to have a Normal-management contract on a building in `$nmrV2BuildingIds` (e.g. the vendor tied to building id 83, "Al Manara Bldg" — the reference sample's own building) and confirms it returns that contract and excludes any Comprehensive-management contract for the same vendor if one exists.

- [ ] **Step 2: Verify one full PDF generation via a fake-request script**

Following the same pattern used to verify Maintenance Invoice Report v2 and Normal Management Report v2 earlier in this project (a standalone script that fakes authentication and invokes the controller method directly, capturing output to a file), invoke `landlordTaxInvoiceReportStream()` for that same vendor/building with a one-month `from_date`/`to_date` range, capture the SSE events to a log, and confirm:
- The final event has `done: true` and a `token`.
- `landlordTaxInvoiceReportDownload($token)` returns a valid PDF file (non-empty, starts with `%PDF-`).
- Open the generated PDF and visually compare its layout against `C:\Users\anoop\OneDrive\文档\works docs\alhabib\taxinvoice.pdf` — company header, field positions, table columns, totals, amount-in-words line, signature block.

- [ ] **Step 3: Cross-check the computed amounts against Normal Management Report v2**

For the same building/month, generate a Normal Management Report v2 export and confirm: the invoice's "REPAIR AND MAINTENANCE CHARGES" line equals that report's "Total Expenses" figure for that month, and the invoice's "CLEANING CHARGES" line equals that report's "Cleaning Charges" row for that month. This confirms the shared `buildNormalManagementMonthData()` extraction (Task 1) produces consistent figures across both reports.

- [ ] **Step 4: Manually click through the UI**

Open the new report's filter form in a browser, select a landlord with a multi-building Normal-management contract set, generate, and confirm a ZIP downloads with one correctly-named PDF per building. Then select a landlord with only one eligible building and confirm a single PDF (no ZIP) downloads.

- [ ] **Step 5: Report results, no commit needed for this task**

Delete any scratch scripts created for Steps 1-3 after running them.

---

## Sync to `server_upload_files/`

After all tasks pass review, mirror every created/modified file (the controller, both migrations already mirrored in their own tasks, both new Blade views, the routes/breadcrumbs files, and `LandlordContract.php` if Task 3 Step 3 modified it) into `server_upload_files/` at the same relative path, `diff -q` each pair to confirm, then commit that sync as its own commit — mirroring the convention already established for the Cleaning Charges feature earlier in this project.
