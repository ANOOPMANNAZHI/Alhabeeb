# Landlord Contract Fee Components Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add three new fee components to landlord contracts — Facility Management Fee, Renewal Fee, and New Leasing Fee — and reflect them correctly in both Normal Management Report v2 and the Landlord Tax Invoice Report, using the same shared per-building/per-month calculation both reports already rely on.

**Architecture:** Four new nullable columns on `landlord_contract`. The same 8 contract forms and 3 controllers already touched by the Cleaning Charges feature gain the new fields (Facility Fee and Renewal Fee as plain amount inputs; New Leasing Fee as a Percentage/Amount toggle, since it appears both ways in real data). `buildNormalManagementMonthData()` — the shared calculation extracted during the Cleaning Charges work — gains a new query splitting each month's tenant-contract-start events into renewals (`tenant_contract_old_no` populated) vs. genuine new leases (null), plus the sum of new leases' rent, and computes each fee. Normal Management Report v2's Consolidate sheet gets 3 new expense rows; the Landlord Tax Invoice Report gets up to 3 new conditional line items (only printed when non-zero for the invoiced period). The PDF template needs no change — it already loops generically over whatever lines the controller builds.

**Tech Stack:** Laravel 5.x modules (Sales, BackOffice), Blade views, PostgreSQL, jQuery form-toggle JS already used by Management Fee / the pre-amount-only-conversion Cleaning Charges pattern.

**Spec:** `docs/superpowers/specs/2026-09-07-landlord-contract-fee-components-design.md`

## Global Constraints

- Four new columns on `landlord_contract`, all nullable: `landlord_contract_facility_management_fee` (double precision), `landlord_contract_renewal_fee` (double precision), `landlord_contract_new_leasing_fee_type` (integer — 1 = Percentage, 2 = Amount), `landlord_contract_new_leasing_fee` (double precision).
- All four shown for **all** management types — not subject to Management Fee's Comprehensive-hides rule (same as Cleaning Charges).
- A tenant contract is a **renewal** when `tenant_contracts.tenant_contract_old_no` is non-null and non-empty; a **new lease** otherwise. No new tracking table or column needed on the tenant side.
- Renewal Fee is always a flat OMR amount — no percentage mode (matches every observed data point).
- New Leasing Fee needs both modes: percentage of the new tenant's rent, or flat OMR amount.
- The bare-value renewal-edit form (`landlord_renew_contract_edit.blade.php`) and its write path (`getContractData()`) get plain value fields only for all three new fees — no radio/method field for New Leasing Fee there either, matching that form's existing simplified shape for every other fee field (Management Fee, Cleaning Charges).
- Every currency amount formatted with 3 decimals via `numberFormat()` (Blade) or `number_format(..., 3)` (PHP), matching every existing fee field's convention.
- `LandlordContract` model (`Modules/Sales/Entities/LandlordContract.php`) already has `protected $guarded = []` — no model changes needed for mass-assignment.
- Do not alter the existing Management Fee or Cleaning Charges fields' behavior, values, or visibility rules.
- The PDF template (`landlord_tax_invoice_pdf.blade.php`) already loops generically over `$lines` — do not modify it.
- Mirror every created/modified file into `server_upload_files/` at the same relative path as the final task, **except** `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php` — that file's `server_upload_files/` copy is already known to be stale from before this plan (confirmed via `git log` during the Landlord Tax Invoice Report plan); do not attempt to sync it blindly.
- This repo has a large amount of pre-existing, unrelated, uncommitted work sitting in the working tree (a separate "Deposit Rent Report V2" / "Expense Details V2" feature spanning several files, including `BackOfficeReportController.php`, `Modules/BackOffice/Routes/web.php`, and `Modules/BackOffice/Routes/breadcrumbs.php`). Every task touching those specific files MUST isolate its commit from that unrelated content using the split technique below — never `git add` those files directly.

### The commit-isolation technique (required for any task touching `BackOfficeReportController.php`)

1. Make your edits on the live working-tree file as normal.
2. Before committing, save a full copy of the current (combined) file content to a temp path.
3. Get the clean starting point via `git show HEAD:<path>`.
4. Re-apply your exact same edits onto that clean HEAD version instead (find the same anchor text).
5. Write that clean result to the actual file path, `git add` it, verify `git diff --cached --stat` shows a small diff matching only your intended change (not hundreds of lines), commit.
6. Restore the file back to its full combined working-tree content (the copy from step 2).
7. Verify: `git diff HEAD --stat -- <path>` should show only the large pre-existing unrelated diff, not your change.

---

## Task 1: Migration — add the four new columns

**Files:**
- Create: `Modules/Sales/Database/Migrations/2026_09_07_000000_add_fee_components_to_landlord_contract.php`
- Create (mirror): `server_upload_files/Modules/Sales/Database/Migrations/2026_09_07_000000_add_fee_components_to_landlord_contract.php`

**Interfaces:**
- Produces: `landlord_contract.landlord_contract_facility_management_fee` (double, nullable), `landlord_contract.landlord_contract_renewal_fee` (double, nullable), `landlord_contract.landlord_contract_new_leasing_fee_type` (unsigned integer, nullable), `landlord_contract.landlord_contract_new_leasing_fee` (double, nullable). Every later task references these four exact column names.

- [ ] **Step 1: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeeComponentsToLandlordContract extends Migration
{
    public function up()
    {
        Schema::table('landlord_contract', function (Blueprint $table) {
            $table->double('landlord_contract_facility_management_fee')->nullable()->after('landlord_contract_cleaning_charge');
            $table->double('landlord_contract_renewal_fee')->nullable()->after('landlord_contract_facility_management_fee');
            $table->unsignedInteger('landlord_contract_new_leasing_fee_type')->nullable()->after('landlord_contract_renewal_fee');
            $table->double('landlord_contract_new_leasing_fee')->nullable()->after('landlord_contract_new_leasing_fee_type');
        });
    }

    public function down()
    {
        Schema::table('landlord_contract', function (Blueprint $table) {
            $table->dropColumn([
                'landlord_contract_facility_management_fee',
                'landlord_contract_renewal_fee',
                'landlord_contract_new_leasing_fee_type',
                'landlord_contract_new_leasing_fee',
            ]);
        });
    }
}
```

- [ ] **Step 2: Run the migration**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=Modules/Sales/Database/Migrations/2026_09_07_000000_add_fee_components_to_landlord_contract.php --force
```
Expected: `Migrated: 2026_09_07_000000_add_fee_components_to_landlord_contract`

- [ ] **Step 3: Verify the columns exist**

Run a one-off script confirming:
```sql
SELECT column_name, data_type FROM information_schema.columns
WHERE table_name = 'landlord_contract'
  AND column_name IN ('landlord_contract_facility_management_fee', 'landlord_contract_renewal_fee', 'landlord_contract_new_leasing_fee_type', 'landlord_contract_new_leasing_fee');
```
Expected: 4 rows.

- [ ] **Step 4: Mirror and commit**

```bash
cp "Modules/Sales/Database/Migrations/2026_09_07_000000_add_fee_components_to_landlord_contract.php" "server_upload_files/Modules/Sales/Database/Migrations/2026_09_07_000000_add_fee_components_to_landlord_contract.php"
git add Modules/Sales/Database/Migrations/2026_09_07_000000_add_fee_components_to_landlord_contract.php server_upload_files/Modules/Sales/Database/Migrations/2026_09_07_000000_add_fee_components_to_landlord_contract.php
git commit -m "feat: add facility management fee, renewal fee, and new leasing fee columns to landlord_contract"
```

---

## Task 2: Extend `buildNormalManagementMonthData()` with the new fee calculations

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`

**Interfaces:**
- Consumes: the private method `buildNormalManagementMonthData(Building $building, int $year, array $monthsToPopulate): array` (already exists — you are extending its body, not changing its signature).
- Produces: `$monthData[$m]` gains three new keys — `facility_management_fee` (float), `renewal_fee` (float), `new_leasing_fee` (float) — alongside the existing `units`, `old_outstanding`, `expenses`, `occupancy`, `cleaning_charge`, `landlord_contract` keys. Tasks 7 and 8 read these three new keys by exact name.

This task requires the commit-isolation technique described in Global Constraints (this file has pre-existing unrelated uncommitted content).

- [ ] **Step 1: Extend the `landlordContractRows` query with the four new columns**

Find (via `grep -n "landlordContractRows = DB::select" -A 12 Modules/BackOffice/Http/Controllers/BackOfficeReportController.php` — there are TWO occurrences of a similarly-named block in this file; the one you want is inside the `buildNormalManagementMonthData()` method, currently around line 5256 — verify by confirming it's the occurrence whose surrounding method signature is `private function buildNormalManagementMonthData`, not the older duplicated logic inside the unused legacy `normalManagementReportV2Generate()` method around line 4000-4800, which must NOT be touched):

```php
    $landlordContractRows = DB::select("
        SELECT landlord_contract_status AS status,
               landlord_contract_valid_from_date AS valid_from,
               landlord_contract_valid_to_date AS valid_to,
               landlord_contract_cleaning_charge AS cleaning_charge,
               management_method,
               landlord_contract_percentage,
               landlord_contract_management_fee,
               vendor_id
        FROM landlord_contract
        WHERE building_id = ?
        ORDER BY landlord_contract_valid_from_date
    ", [$building->id]);
```

Replace with:

```php
    $landlordContractRows = DB::select("
        SELECT landlord_contract_status AS status,
               landlord_contract_valid_from_date AS valid_from,
               landlord_contract_valid_to_date AS valid_to,
               landlord_contract_cleaning_charge AS cleaning_charge,
               management_method,
               landlord_contract_percentage,
               landlord_contract_management_fee,
               vendor_id,
               landlord_contract_facility_management_fee AS facility_management_fee,
               landlord_contract_renewal_fee AS renewal_fee,
               landlord_contract_new_leasing_fee_type AS new_leasing_fee_type,
               landlord_contract_new_leasing_fee AS new_leasing_fee
        FROM landlord_contract
        WHERE building_id = ?
        ORDER BY landlord_contract_valid_from_date
    ", [$building->id]);
```

- [ ] **Step 2: Add the renewal/new-lease batch query**

Find (via `grep -n "newLeasedByMonth = \[\];" Modules/BackOffice/Http/Controllers/BackOfficeReportController.php` — again there are two occurrences, use the one inside `buildNormalManagementMonthData()`, currently around line 5315):

```php
    $newLeasedByMonth = [];
    foreach ($newLeasedRows as $nlRow) {
        $newLeasedByMonth[(int) $nlRow->month] = [
            'new_leased_residential' => (int) $nlRow->new_leased_residential,
            'new_leased_commercial'  => (int) $nlRow->new_leased_commercial,
        ];
    }

    // ── Legal contract ID lookup (contract-level, not unit-level) ────────────
```

Replace with:

```php
    $newLeasedByMonth = [];
    foreach ($newLeasedRows as $nlRow) {
        $newLeasedByMonth[(int) $nlRow->month] = [
            'new_leased_residential' => (int) $nlRow->new_leased_residential,
            'new_leased_commercial'  => (int) $nlRow->new_leased_commercial,
        ];
    }

    // ── Batch renewal/new-lease counts + new-lease rent sum for the year ──
    // A tenant contract is a renewal when tenant_contract_old_no is populated
    // (it references the prior contract it renews), and a genuine new lease
    // when it is null/empty. Used to compute the Renewal Fee and New Leasing
    // Fee components below.
    $renewalNewLeaseRows = DB::select("
        SELECT EXTRACT(MONTH FROM tc.tenant_contract_start_date)::int AS month,
            COUNT(DISTINCT CASE WHEN tc.tenant_contract_old_no IS NOT NULL AND tc.tenant_contract_old_no != '' THEN tc.id END) AS renewal_count,
            COUNT(DISTINCT CASE WHEN tc.tenant_contract_old_no IS NULL OR tc.tenant_contract_old_no = '' THEN tc.id END) AS new_lease_count,
            SUM(CASE WHEN tc.tenant_contract_old_no IS NULL OR tc.tenant_contract_old_no = '' THEN tc.tenant_contract_rent ELSE 0 END) AS new_lease_rent_sum
        FROM tenant_contracts tc
        JOIN units u ON u.id = tc.unit_id AND u.building_id = ? AND u.unit_status = 1
        WHERE tc.tenant_contract_status != 2 AND EXTRACT(YEAR FROM tc.tenant_contract_start_date) = ?
        GROUP BY month ORDER BY month
    ", [$building->id, $year]);

    $renewalNewLeaseByMonth = [];
    foreach ($renewalNewLeaseRows as $rlRow) {
        $renewalNewLeaseByMonth[(int) $rlRow->month] = [
            'renewal_count'      => (int) $rlRow->renewal_count,
            'new_lease_count'    => (int) $rlRow->new_lease_count,
            'new_lease_rent_sum' => (float) $rlRow->new_lease_rent_sum,
        ];
    }

    // ── Legal contract ID lookup (contract-level, not unit-level) ────────────
```

- [ ] **Step 3: Compute the three new fees per month and add them to `$monthData`**

Find (via `grep -n "if (\$matched !== null) {" Modules/BackOffice/Http/Controllers/BackOfficeReportController.php` — use the occurrence inside `buildNormalManagementMonthData()`, currently around line 5778):

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

Replace with:

```php
            if ($matched !== null) {
                $cleaningCharge = (float) $matched->cleaning_charge;
            }

            // Facility Management Fee, Renewal Fee, New Leasing Fee — all
            // derived from the matched landlord contract, same date-range
            // matching as Cleaning Charges above.
            $facilityManagementFee = 0.0;
            $renewalFee = 0.0;
            $newLeasingFee = 0.0;
            if ($matched !== null) {
                $facilityManagementFee = (float) ($matched->facility_management_fee ?? 0);

                $rn = $renewalNewLeaseByMonth[$m] ?? ['renewal_count' => 0, 'new_lease_count' => 0, 'new_lease_rent_sum' => 0];
                $renewalFee = $rn['renewal_count'] * (float) ($matched->renewal_fee ?? 0);

                $newLeasingFeeType = (int) ($matched->new_leasing_fee_type ?? 0);
                if ($newLeasingFeeType === 1) {
                    $newLeasingFee = round($rn['new_lease_rent_sum'] * ((float) ($matched->new_leasing_fee ?? 0) / 100), 3);
                } elseif ($newLeasingFeeType === 2) {
                    $newLeasingFee = $rn['new_lease_count'] * (float) ($matched->new_leasing_fee ?? 0);
                }
            }
        } else {
            $units = []; $oldOutstanding = []; $expenses = []; $cleaningCharge = 0; $matched = null;
            $facilityManagementFee = 0.0; $renewalFee = 0.0; $newLeasingFee = 0.0;
            $occupancy = ['total_units' => 0, 'new_leased_residential' => 0, 'new_leased_commercial' => 0,
                'occupied_residential' => 0, 'occupied_commercial' => 0, 'vacant_residential' => 0,
                'vacant_commercial' => 0, 'evacuation_residential' => 0, 'evacuation_commercial' => 0];
        }

        $monthData[$m] = [
            'units'                   => $units,
            'old_outstanding'         => $oldOutstanding ?? [],
            'expenses'                => $expenses,
            'occupancy'               => $occupancy,
            'cleaning_charge'         => $cleaningCharge,
            'landlord_contract'       => $matched,
            'facility_management_fee' => $facilityManagementFee,
            'renewal_fee'             => $renewalFee,
            'new_leasing_fee'         => $newLeasingFee,
        ];
    }
```

- [ ] **Step 4: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/BackOfficeReportController.php"
```

- [ ] **Step 5: Regression-verify Normal Management Report v2 and the Landlord Tax Invoice Report are unaffected for existing fields**

Write a throwaway script (scratchpad, not committed) that calls `buildNormalManagementMonthData()` (via reflection, since it's private) for one known building (e.g. building id 83, "Al Manara Bldg") for a recent year, both before and after this change, and confirms every existing key (`units`, `old_outstanding`, `expenses`, `occupancy`, `cleaning_charge`, `landlord_contract`) is byte-identical — only the three new keys should differ (from absent to present). Also confirm the three new keys are numeric floats, never null or missing, for every month 1-12.

- [ ] **Step 6: Commit using the commit-isolation technique**

```bash
git commit -m "feat: compute Facility Management Fee, Renewal Fee, and New Leasing Fee per month in buildNormalManagementMonthData()"
```

---

## Task 3: Forms batch A — Sales create/edit + BackOffice Direct create/edit (4 files)

**Files:**
- Modify: `Modules/Sales/Resources/views/LandlordSales/contract_in_draft.blade.php` (create-only, no prefill)
- Modify: `Modules/Sales/Resources/views/LandlordSales/edit_contract.blade.php` (edit, has prefill)
- Modify: `Modules/BackOffice/Resources/views/LandlordContract/add_contract_direct.blade.php` (create-only, no prefill)
- Modify: `Modules/BackOffice/Resources/views/LandlordContract/edit_contract.blade.php` (edit, has prefill)

**Interfaces:**
- Consumes: nothing from other tasks.
- Produces: `landlord_contract_facility_management_fee`, `landlord_contract_renewal_fee`, `landlord_contract_new_leasing_fee_type`, `landlord_contract_new_leasing_fee` POST fields, consumed by Task 6's controller write-sites.

None of these 4 files carry pre-existing unrelated content — normal `git add` is fine for this task.

- [ ] **Step 1: Insert the new fields into the two create-only forms**

In `Modules/Sales/Resources/views/LandlordSales/contract_in_draft.blade.php` and `Modules/BackOffice/Resources/views/LandlordContract/add_contract_direct.blade.php`, find the existing Cleaning Charges block (verify with `grep -n "landlord_contract_cleaning_charge" <file>` — it currently ends with the closing `</div>` of a `col-sm-6` right after the Cleaning Amount input, immediately preceded by a blank line):

```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_cleaning_charge">Cleaning Amount</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_cleaning_charge" name="landlord_contract_cleaning_charge" placeholder="Enter Cleaning Amount" min="0" max="999999999">
                </div>
            </div>
        </div>
```

Insert this block immediately after it (this is the bare/no-prefill variant — matches how the original Cleaning Charges Form Block worked before it was simplified to amount-only):

```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_facility_management_fee">Facility Management Fee</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_facility_management_fee" name="landlord_contract_facility_management_fee" placeholder="Enter Facility Management Fee" min="0" max="999999999">
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_renewal_fee">Renewal Fee</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_renewal_fee" name="landlord_contract_renewal_fee" placeholder="Enter Renewal Fee" min="0" max="999999999">
                </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_new_leasing_fee_type">New Leasing Fee</label>
                <div class="p-relative">
                <label for="landlord_contract_new_leasing_fee_type_one">
                       <input type="radio" name="landlord_contract_new_leasing_fee_type" id="landlord_contract_new_leasing_fee_type_one" value="1" checked class="new_leasing_fee_type"> Percentage
               </label>
               <label for="landlord_contract_new_leasing_fee_type_two">
                   <input type="radio" name="landlord_contract_new_leasing_fee_type" id="landlord_contract_new_leasing_fee_type_two" value="2" class="new_leasing_fee_type"> Amount
               </label>
           </div>
           </div>
       </div>
       <div class="col-sm-6">
           <div class="form-group">
                <label for="landlord_contract_new_leasing_fee_label" id="landlord_contract_new_leasing_fee_label">New Leasing Value</label>
                <div class="p-relative">
                   <i class="fa fa-money icn-add" aria-hidden="true"></i>
                   <input type="number" class="form-control" id="landlord_contract_new_leasing_fee" name="landlord_contract_new_leasing_fee" placeholder="Enter New Leasing Value" min="0" max="999999999">
           </div>
           </div>
       </div>
```

- [ ] **Step 2: Insert the JS toggle handler into the two create-only forms**

In both files, find the existing `.cleaning_charge_method` reference to confirm it's gone (it was removed when Cleaning Charges was converted to amount-only) — there is no existing toggle handler to anchor after. Instead, find the `.management_method` click handler (search `grep -n '\.management_method' <file>`) and insert this new handler immediately after that handler's closing `});`:

```javascript
    $(".new_leasing_fee_type").on('click', function(e) {
        var new_leasing_fee_type = $(this).val();
        if (new_leasing_fee_type == 1) {
            $("#landlord_contract_new_leasing_fee_label").html('New Leasing Value');
            $("#landlord_contract_new_leasing_fee").attr('placeholder', 'Enter New Leasing Value');
            $("#landlord_contract_new_leasing_fee").attr("max", 100);
        } else {
            $("#landlord_contract_new_leasing_fee_label").html('New Leasing Amount');
            $("#landlord_contract_new_leasing_fee").attr('placeholder', 'Enter New Leasing Amount');
            $("#landlord_contract_new_leasing_fee").attr("max", 999999999);
        }
    });
```

- [ ] **Step 3: Insert the new fields into the two edit/prefill forms**

In `Modules/Sales/Resources/views/LandlordSales/edit_contract.blade.php` and `Modules/BackOffice/Resources/views/LandlordContract/edit_contract.blade.php`, find the existing Cleaning Charges block (it has a `value="{{ isset($landlordContract->landlord_contract_cleaning_charge)?$landlordContract->landlord_contract_cleaning_charge:'' }}"` attribute on the input, unlike the bare version above):

```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_cleaning_charge">Cleaning Amount</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_cleaning_charge" name="landlord_contract_cleaning_charge" value="{{ isset($landlordContract->landlord_contract_cleaning_charge)?$landlordContract->landlord_contract_cleaning_charge:'' }}" placeholder="Enter Cleaning Amount" min="0" max="999999999">
                </div>
            </div>
        </div>
```

Insert this prefill-aware block immediately after it (mirrors the same prefill pattern used for Management Fee and the pre-simplification Cleaning Charges):

```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_facility_management_fee">Facility Management Fee</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_facility_management_fee" name="landlord_contract_facility_management_fee" value="{{ isset($landlordContract->landlord_contract_facility_management_fee)?$landlordContract->landlord_contract_facility_management_fee:'' }}" placeholder="Enter Facility Management Fee" min="0" max="999999999">
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_renewal_fee">Renewal Fee</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_renewal_fee" name="landlord_contract_renewal_fee" value="{{ isset($landlordContract->landlord_contract_renewal_fee)?$landlordContract->landlord_contract_renewal_fee:'' }}" placeholder="Enter Renewal Fee" min="0" max="999999999">
                </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_new_leasing_fee_type">New Leasing Fee</label>
                <div class="p-relative">
                <label for="landlord_contract_new_leasing_fee_type_one">
                       <input type="radio" name="landlord_contract_new_leasing_fee_type" value="1" {{ isset($landlordContract->landlord_contract_new_leasing_fee_type)? (($landlordContract->landlord_contract_new_leasing_fee_type==1)?'CHECKED':''):'CHECKED' }} class="new_leasing_fee_type"> Percentage
               </label>
               <label for="landlord_contract_new_leasing_fee_type_two">
                   <input type="radio" name="landlord_contract_new_leasing_fee_type" value="2" {{ isset($landlordContract->landlord_contract_new_leasing_fee_type)? (($landlordContract->landlord_contract_new_leasing_fee_type==2)?'CHECKED':''):'' }} class="new_leasing_fee_type"> Amount
               </label>
           </div>
           </div>
       </div>
       <div class="col-sm-6">
           <div class="form-group">
                <label for="landlord_contract_new_leasing_fee_label" id="landlord_contract_new_leasing_fee_label">New Leasing Value</label>
                <div class="p-relative">
                   <i class="fa fa-money icn-add" aria-hidden="true"></i>
                   <input type="number" class="form-control" id="landlord_contract_new_leasing_fee" name="landlord_contract_new_leasing_fee" value="{{ isset($landlordContract->landlord_contract_new_leasing_fee)?$landlordContract->landlord_contract_new_leasing_fee:'' }}" placeholder="Enter New Leasing Value" min="0" max="999999999">
           </div>
           </div>
       </div>
```

- [ ] **Step 4: Insert the JS toggle handler into the two edit/prefill forms**

Same handler as Step 2, inserted after the `.management_method` click handler in each of these two files.

- [ ] **Step 5: Lint all 4 files**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Resources/views/LandlordSales/contract_in_draft.blade.php"
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Resources/views/LandlordSales/edit_contract.blade.php"
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/LandlordContract/add_contract_direct.blade.php"
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/LandlordContract/edit_contract.blade.php"
```

- [ ] **Step 6: Commit each file separately**

```bash
git add Modules/Sales/Resources/views/LandlordSales/contract_in_draft.blade.php
git commit -m "feat: add fee component fields to Sales landlord contract draft-creation form"
git add Modules/Sales/Resources/views/LandlordSales/edit_contract.blade.php
git commit -m "feat: add fee component fields to Sales landlord contract edit form"
git add Modules/BackOffice/Resources/views/LandlordContract/add_contract_direct.blade.php
git commit -m "feat: add fee component fields to BackOffice direct contract creation form"
git add Modules/BackOffice/Resources/views/LandlordContract/edit_contract.blade.php
git commit -m "feat: add fee component fields to BackOffice direct contract edit form"
```

---

## Task 4: Forms batch B — 3 renewal prefill forms

**Files:**
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_add.blade.php`
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_update.blade.php`
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_create.blade.php`

**Interfaces:**
- Consumes: nothing from other tasks.
- Produces: same 4 POST fields as Task 3, consumed by Task 6.

None of these 3 files carry pre-existing unrelated content — normal `git add` is fine for this task.

- [ ] **Step 1: Insert the new fields into all 3 files**

In each file, find the existing Cleaning Charges block (prefill variant, same shape as Task 3 Step 3 — verify exact text via `grep -n "landlord_contract_cleaning_charge" <file>`, since these files' Cleaning Charges block was added at a slightly different point in each file during the earlier plan but follows the identical prefill pattern). Insert the identical prefill-aware block given in Task 3 Step 3 immediately after it.

- [ ] **Step 2: Insert the JS toggle handler into all 3 files**

Insert the identical JS handler from Task 3 Step 2 after the `.management_method` click handler in each file. Note: `landlord_renew_contract_create.blade.php` has no `.management_method` handler in this file (it has Task 8's bare-value shape for Management Fee, confirmed during the earlier Cleaning Charges plan) — for this one file only, insert the JS handler standalone at the end of the file's `$(document).ready(...)` block instead, matching how that file's Cleaning Charges JS handler was placed (verify via `grep -n "cleaning_charge_method\|document).ready" landlord_renew_contract_create.blade.php` — if no `.cleaning_charge_method` handler exists in this file either since Cleaning Charges was later simplified to bare-value with no JS, insert immediately before the final closing `});` of the `$(document).ready(...)` block).

- [ ] **Step 3: Lint all 3 files**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_add.blade.php"
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_update.blade.php"
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_create.blade.php"
```

- [ ] **Step 4: Commit each file separately**

```bash
git add Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_add.blade.php
git commit -m "feat: add fee component fields to landlord renewal contract add form"
git add Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_update.blade.php
git commit -m "feat: add fee component fields to landlord renewal new-contract update form"
git add Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_create.blade.php
git commit -m "feat: add fee component fields to landlord renewal contract create form"
```

---

## Task 5: Bare-value renewal-edit form

**Files:**
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_edit.blade.php`

**Interfaces:**
- Consumes: nothing from other tasks.
- Produces: `landlord_contract_facility_management_fee`, `landlord_contract_renewal_fee`, `landlord_contract_new_leasing_fee` POST fields (no `_type` field — this form never has a method/percentage toggle for any fee, matching its existing simplified shape). Consumed by Task 6's `getContractData()` write-site.

This file carries no pre-existing unrelated content — normal `git add` is fine.

- [ ] **Step 1: Insert bare value fields (no radio, no JS)**

Find the existing bare Cleaning Charges field (verify via `grep -n "landlord_contract_cleaning_charge" Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_edit.blade.php`):

```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_cleaning_charge">Cleaning Charges</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="number" class="form-control" value="{{ isset($landlordContract->landlord_contract_cleaning_charge)?$landlordContract->landlord_contract_cleaning_charge:'' }}" id="landlord_contract_cleaning_charge" name="landlord_contract_cleaning_charge" placeholder="Enter Cleaning Charges" min="1">
                </div>
            </div>
        </div>
```

Insert immediately after it:

```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_facility_management_fee">Facility Management Fee</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="number" class="form-control" value="{{ isset($landlordContract->landlord_contract_facility_management_fee)?$landlordContract->landlord_contract_facility_management_fee:'' }}" id="landlord_contract_facility_management_fee" name="landlord_contract_facility_management_fee" placeholder="Enter Facility Management Fee" min="1">
                </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_renewal_fee">Renewal Fee</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="number" class="form-control" value="{{ isset($landlordContract->landlord_contract_renewal_fee)?$landlordContract->landlord_contract_renewal_fee:'' }}" id="landlord_contract_renewal_fee" name="landlord_contract_renewal_fee" placeholder="Enter Renewal Fee" min="1">
                </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_new_leasing_fee">New Leasing Fee</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="number" class="form-control" value="{{ isset($landlordContract->landlord_contract_new_leasing_fee)?$landlordContract->landlord_contract_new_leasing_fee:'' }}" id="landlord_contract_new_leasing_fee" name="landlord_contract_new_leasing_fee" placeholder="Enter New Leasing Fee" min="1">
                </div>
            </div>
        </div>
```

- [ ] **Step 2: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_edit.blade.php"
git add Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_edit.blade.php
git commit -m "feat: add fee component fields to landlord renewal contract edit form"
```

---

## Task 6: Controllers — 3 files, 7 write-sites

**Files:**
- Modify: `Modules/Sales/Http/Controllers/LandlordContractController.php`
- Modify: `Modules/BackOffice/Http/Controllers/LandlordContractDirectController.php`
- Modify: `Modules/BackOffice/Http/Controllers/LandlordRenewalController.php`

**Interfaces:**
- Consumes: the POST field names from Tasks 3-5.

None of these 3 files carry pre-existing unrelated content — normal `git add` is fine for this task.

- [ ] **Step 1: Add 4 array keys at each of the 6 full-form write-sites**

In each of these 6 locations (all identifiable via `grep -n "landlord_contract_cleaning_charge'    => \$request\['landlord_contract_cleaning_charge'\]," <file>`):

- `Modules/Sales/Http/Controllers/LandlordContractController.php`, method `update()` (~line 208)
- `Modules/Sales/Http/Controllers/LandlordContractController.php`, method `landlordContractAction()` (~line 313)
- `Modules/BackOffice/Http/Controllers/LandlordContractDirectController.php`, method `store()` (~line 226)
- `Modules/BackOffice/Http/Controllers/LandlordContractDirectController.php`, method `update()` (~line 353)
- `Modules/BackOffice/Http/Controllers/LandlordRenewalController.php`, method `renewalContractAdd()` (~line 905)
- `Modules/BackOffice/Http/Controllers/LandlordRenewalController.php`, method `renewalNewContractUpdate()` (~line 1022)

Find the existing line:
```php
          'landlord_contract_cleaning_charge'    => $request['landlord_contract_cleaning_charge'],
```
Immediately after it, in the same array, add:
```php
          'landlord_contract_facility_management_fee' => $request['landlord_contract_facility_management_fee'],
          'landlord_contract_renewal_fee'              => $request['landlord_contract_renewal_fee'],
          'landlord_contract_new_leasing_fee_type'     => $request['landlord_contract_new_leasing_fee_type'],
          'landlord_contract_new_leasing_fee'          => $request['landlord_contract_new_leasing_fee'],
```

- [ ] **Step 2: Add 3 array keys to `getContractData()`**

In `Modules/BackOffice/Http/Controllers/LandlordRenewalController.php`, find (via `grep -n "contractData\['landlord_contract_cleaning_charge'\]" Modules/BackOffice/Http/Controllers/LandlordRenewalController.php`):
```php
      $contractData['landlord_contract_cleaning_charge'] = request('landlord_contract_cleaning_charge');
```
Immediately after it, add (no `_type` key — this method matches Task 5's bare-value form, which has no method/percentage toggle):
```php
      $contractData['landlord_contract_facility_management_fee'] = request('landlord_contract_facility_management_fee');
      $contractData['landlord_contract_renewal_fee'] = request('landlord_contract_renewal_fee');
      $contractData['landlord_contract_new_leasing_fee'] = request('landlord_contract_new_leasing_fee');
```

- [ ] **Step 3: Lint all 3 files**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Http/Controllers/LandlordContractController.php"
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/LandlordContractDirectController.php"
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/LandlordRenewalController.php"
```

- [ ] **Step 4: Commit each file separately**

```bash
git add Modules/Sales/Http/Controllers/LandlordContractController.php
git commit -m "feat: save fee component fields when creating/editing a landlord contract (Sales)"
git add Modules/BackOffice/Http/Controllers/LandlordContractDirectController.php
git commit -m "feat: save fee component fields in BackOffice direct contract create/update"
git add Modules/BackOffice/Http/Controllers/LandlordRenewalController.php
git commit -m "feat: save fee component fields in landlord renewal add/edit/update flows"
```

---

## Task 7: Normal Management Report v2 — 3 new expense rows

**Files:**
- Modify: `Modules/BackOffice/Exports/NormalManagementConsolidateSheet.php`

**Interfaces:**
- Consumes: `$this->monthData[$m]['facility_management_fee']`, `['renewal_fee']`, `['new_leasing_fee']` (Task 2).

This file carries no pre-existing unrelated content — normal `git add` is fine.

- [ ] **Step 1: Insert the 3 new rows before Total Expenses**

Find (via `grep -n "Cleaning Charges (from the landlord contract" Modules/BackOffice/Exports/NormalManagementConsolidateSheet.php`):

```php
        // Cleaning Charges (from the landlord contract, not a transactional expense —
        // included in the Expenses section since it's deducted like one)
        $cleaningRow  = ['', 'Cleaning Charges'];
        $cleaningYTD  = 0;
        foreach (range(1, 12) as $m) {
            $amount = (float)($this->monthData[$m]['cleaning_charge'] ?? 0);
            $cleaningRow[] = $amount > 0 ? $amount : '-';
            $cleaningYTD  += $amount;
            $expenseTotalsByMonth[$m] += $amount;
        }
        $cleaningRow[] = $cleaningYTD ?: '-';
        $rows[] = $cleaningRow;

        // Total Expenses
```

Replace with:

```php
        // Cleaning Charges (from the landlord contract, not a transactional expense —
        // included in the Expenses section since it's deducted like one)
        $cleaningRow  = ['', 'Cleaning Charges'];
        $cleaningYTD  = 0;
        foreach (range(1, 12) as $m) {
            $amount = (float)($this->monthData[$m]['cleaning_charge'] ?? 0);
            $cleaningRow[] = $amount > 0 ? $amount : '-';
            $cleaningYTD  += $amount;
            $expenseTotalsByMonth[$m] += $amount;
        }
        $cleaningRow[] = $cleaningYTD ?: '-';
        $rows[] = $cleaningRow;

        // Facility Management Fee — same treatment as Cleaning Charges: from the
        // landlord contract, not a transactional expense, folded into Total Expenses.
        $facilityRow = ['', 'Facility Management Fee'];
        $facilityYTD = 0;
        foreach (range(1, 12) as $m) {
            $amount = (float)($this->monthData[$m]['facility_management_fee'] ?? 0);
            $facilityRow[] = $amount > 0 ? $amount : '-';
            $facilityYTD += $amount;
            $expenseTotalsByMonth[$m] += $amount;
        }
        $facilityRow[] = $facilityYTD ?: '-';
        $rows[] = $facilityRow;

        // Renewal Fee — count of tenant contract renewals that month times the
        // landlord contract's flat renewal fee (computed in buildNormalManagementMonthData()).
        $renewalRow = ['', 'Renewal Fee'];
        $renewalYTD = 0;
        foreach (range(1, 12) as $m) {
            $amount = (float)($this->monthData[$m]['renewal_fee'] ?? 0);
            $renewalRow[] = $amount > 0 ? $amount : '-';
            $renewalYTD += $amount;
            $expenseTotalsByMonth[$m] += $amount;
        }
        $renewalRow[] = $renewalYTD ?: '-';
        $rows[] = $renewalRow;

        // New Leasing Fee — count/rent-sum of genuinely new tenant leases that
        // month times the landlord contract's flat fee or percentage.
        $newLeasingRow = ['', 'New Leasing Fee'];
        $newLeasingYTD = 0;
        foreach (range(1, 12) as $m) {
            $amount = (float)($this->monthData[$m]['new_leasing_fee'] ?? 0);
            $newLeasingRow[] = $amount > 0 ? $amount : '-';
            $newLeasingYTD += $amount;
            $expenseTotalsByMonth[$m] += $amount;
        }
        $newLeasingRow[] = $newLeasingYTD ?: '-';
        $rows[] = $newLeasingRow;

        // Total Expenses
```

- [ ] **Step 2: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Exports/NormalManagementConsolidateSheet.php"
git add Modules/BackOffice/Exports/NormalManagementConsolidateSheet.php
git commit -m "feat: add Facility Management Fee, Renewal Fee, and New Leasing Fee rows to Normal Management Report v2 consolidated sheet"
```

- [ ] **Step 3: Verify with a synthetic month-data test**

Write a throwaway script (scratchpad, not committed) instantiating `NormalManagementConsolidateSheet` with fake `$monthData` containing non-zero `facility_management_fee`, `renewal_fee`, and `new_leasing_fee` values, calling `->array()`, and confirming: the three new rows appear with correct YTD sums, and `Total Expenses`/`Amount Transfer to Land Lord` correctly include them (same verification pattern used for Cleaning Charges' original implementation).

---

## Task 8: Landlord Tax Invoice Report — 3 new conditional line items

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`

**Interfaces:**
- Consumes: `$monthData[$m]['facility_management_fee']`, `['renewal_fee']`, `['new_leasing_fee']` (Task 2).
- Produces: `landlordTaxInvoiceLineAmounts()`'s return array gains `facility_management_fee`, `renewal_fee`, `new_leasing_fee` keys. The PDF template needs no change — it already loops generically over whatever `$lines` the stream method builds.

This task requires the commit-isolation technique (this file has pre-existing unrelated uncommitted content).

- [ ] **Step 1: Accumulate the three new totals in `landlordTaxInvoiceLineAmounts()`**

Find (via `grep -n "function landlordTaxInvoiceLineAmounts" -A 15 Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`):

```php
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
```

Replace with:

```php
    $managementFee = 0.0;
    $totalCleaning = 0.0;
    $totalExpenses = 0.0;
    $totalFacility = 0.0;
    $totalRenewal = 0.0;
    $totalNewLeasing = 0.0;
    foreach ($byYear as $yr => $months) {
        $monthData = $this->buildNormalManagementMonthData($building, $yr, $months);
        foreach ($months as $m) {
            $data = $monthData[$m] ?? null;
            if (!$data) continue;

            foreach ($data['expenses'] ?? [] as $exp) {
                $totalExpenses += (float) $exp->expense_amount;
            }
            $totalCleaning   += (float) ($data['cleaning_charge'] ?? 0);
            $totalFacility   += (float) ($data['facility_management_fee'] ?? 0);
            $totalRenewal    += (float) ($data['renewal_fee'] ?? 0);
            $totalNewLeasing += (float) ($data['new_leasing_fee'] ?? 0);

            $lc = $data['landlord_contract'] ?? null;
```

- [ ] **Step 2: Return the three new totals**

Find (via `grep -n "'repair_maintenance' => round" Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`):

```php
    return [
        'management_fee'     => round($managementFee, 3),
        'cleaning_charge'    => round($totalCleaning, 3),
        'repair_maintenance' => round($totalExpenses, 3),
        'period_label'       => $periodLabel,
    ];
}
```

Replace with:

```php
    return [
        'management_fee'          => round($managementFee, 3),
        'cleaning_charge'         => round($totalCleaning, 3),
        'repair_maintenance'      => round($totalExpenses, 3),
        'facility_management_fee' => round($totalFacility, 3),
        'renewal_fee'             => round($totalRenewal, 3),
        'new_leasing_fee'         => round($totalNewLeasing, 3),
        'period_label'            => $periodLabel,
    ];
}
```

- [ ] **Step 3: Conditionally add the three new lines in `landlordTaxInvoiceReportStream()`**

Find (via `grep -n "\$lines = \[" Modules/BackOffice/Http/Controllers/BackOfficeReportController.php` — use the occurrence inside `landlordTaxInvoiceReportStream()`, currently around line 4960):

```php
            $amounts = $this->landlordTaxInvoiceLineAmounts($building, $fromDate, $toDate, $vendorId);

            $mgmtAmount    = $amounts['management_fee'];
            $cleanAmount   = $amounts['cleaning_charge'];
            $repairAmount  = $amounts['repair_maintenance'];
            $periodLabel   = $amounts['period_label'];

            $lines = [
                ['desc' => 'MANAGEMENT FEES FOR ' . $periodLabel, 'amount' => $mgmtAmount],
                ['desc' => "CLEANING CHARGES FOR " . $periodLabel, 'amount' => $cleanAmount],
                ['desc' => 'REPAIR AND MAINTENANCE CHARGES', 'amount' => $repairAmount],
            ];
```

Replace with:

```php
            $amounts = $this->landlordTaxInvoiceLineAmounts($building, $fromDate, $toDate, $vendorId);

            $mgmtAmount     = $amounts['management_fee'];
            $cleanAmount    = $amounts['cleaning_charge'];
            $repairAmount   = $amounts['repair_maintenance'];
            $facilityAmount = $amounts['facility_management_fee'];
            $renewalAmount  = $amounts['renewal_fee'];
            $newLeaseAmount = $amounts['new_leasing_fee'];
            $periodLabel    = $amounts['period_label'];

            $lines = [
                ['desc' => 'MANAGEMENT FEES FOR ' . $periodLabel, 'amount' => $mgmtAmount],
                ['desc' => "CLEANING CHARGES FOR " . $periodLabel, 'amount' => $cleanAmount],
                ['desc' => 'REPAIR AND MAINTENANCE CHARGES', 'amount' => $repairAmount],
            ];
            // Only printed when non-zero for the invoiced period — e.g. no
            // "Renewal Fee" line if no renewal happened in this building
            // during the requested from_date/to_date range.
            if ($facilityAmount > 0) {
                $lines[] = ['desc' => 'FACILITY MANAGEMENT FEE FOR ' . $periodLabel, 'amount' => $facilityAmount];
            }
            if ($renewalAmount > 0) {
                $lines[] = ['desc' => 'RENEWAL FEE FOR ' . $periodLabel, 'amount' => $renewalAmount];
            }
            if ($newLeaseAmount > 0) {
                $lines[] = ['desc' => 'NEW LEASING FEE FOR ' . $periodLabel, 'amount' => $newLeaseAmount];
            }
```

- [ ] **Step 4: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/BackOfficeReportController.php"
```

- [ ] **Step 5: Commit using the commit-isolation technique**

```bash
git commit -m "feat: include Facility Management Fee, Renewal Fee, and New Leasing Fee as conditional lines in Landlord Tax Invoice Report"
```

---

## Task 9: End-to-end verification

**Files:** none (verification only)

- [ ] **Step 1: Verify a full create → compute → display round trip**

Write a throwaway script (scratchpad, not committed) that:
1. Finds a building with at least one tenant contract renewal (`tenant_contract_old_no` populated) and at least one genuinely new lease in the same month, if one exists in the dataset — otherwise use two different months.
2. Sets that building's active landlord contract's `landlord_contract_facility_management_fee = 65`, `landlord_contract_renewal_fee = 25`, `landlord_contract_new_leasing_fee_type = 2`, `landlord_contract_new_leasing_fee = 20` directly via `LandlordContract::where('id', $id)->update([...])`.
3. Calls `buildNormalManagementMonthData()` (via reflection) for that building/year and confirms the month with a renewal shows `renewal_fee = 25 * (renewal count)`, and the month with a new lease shows `new_leasing_fee = 20 * (new lease count)`.
4. Calls `landlordTaxInvoiceLineAmounts()` (via reflection) for a date range covering those months and confirms the returned `facility_management_fee`/`renewal_fee`/`new_leasing_fee` match.
5. Renders `NormalManagementConsolidateSheet::array()` with matching fake `$monthData` and confirms the three new rows appear with the right values (or reuse Task 7 Step 3's script).
6. Restores the landlord contract's original fee values afterward (do not leave test data in a real contract).

- [ ] **Step 2: Manually click through one full flow in the browser**

Open a landlord contract edit form, fill in Facility Management Fee, Renewal Fee, and New Leasing Fee (toggle both Percentage and Amount to confirm the JS label/placeholder swap works), save, then re-open the same contract and confirm the values persisted and prefilled correctly.

- [ ] **Step 3: Report results, no commit needed for this task**

---

## Sync to `server_upload_files/`

After all tasks pass review, mirror every created/modified file **except** `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php` (per Global Constraints — that file's `server_upload_files/` copy is already known to be stale from before this plan and needs a separate, deliberate reconciliation pass, not a blind sync) into `server_upload_files/` at the same relative path, `diff -q` each pair to confirm, then commit that sync as its own commit — mirroring the convention already established for the Cleaning Charges and Landlord Tax Invoice Report features.
