# Landlord Invoice v2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a new CRUD module — "Landlord Invoice v2" — under PLM Module → Operations, where BackOffice staff create, list, edit, void, and print landlord invoices (Tax Invoice / Other Deductions) with strictly sequential, per-type invoice numbers for tax-audit purposes, reusing the existing Landlord Tax Invoice Report's calculation engine rather than duplicating it.

**Architecture:** Two new tables (`landlord_invoice_v2` header + `landlord_invoice_v2_lines`), a new `LandlordInvoiceV2Controller` in the BackOffice module, and two new Blade views (create/edit form, list) plus a PDF template. Calculation logic is reused, not duplicated, by widening three existing `private` methods on `BackOfficeReportController` (`monthsTouchedByRange`, `buildNormalManagementMonthData`, `landlordTaxInvoiceLineAmounts`, plus `landlordTaxInvoiceAmountInWords`) to `public` and calling them from the new controller via `app(BackOfficeReportController::class)`, and adding two new public methods there (`landlordOtherDeductionsLineAmounts()`, `landlordSubcontractorRepairMaintenanceAmount()`) built on the same `buildNormalManagementMonthData()` data and the same in-house/sub-contractor `service_report_id` signal Maintenance Invoice Report v2 already uses. This is a deliberate, narrower alternative to the spec's "extract into a shared service" wording — see Global Constraints for the reasoning.

**Tech Stack:** Laravel 5.x modules (BackOffice, Sales, Masters), Blade views, PostgreSQL, `barryvdh/laravel-dompdf` (`\PDF::loadView(...)`), jQuery.

**Spec:** `docs/superpowers/specs/2026-09-07-landlord-invoice-v2-design.md`

## Global Constraints

- PHP CLI for all `artisan` commands and one-off verification scripts: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe` (the system `php` on PATH is 8.4 and throws fatal errors on this Laravel 5.x app's deprecated-API usage — do not use it).
- **Calculation reuse, not extraction:** rather than moving `buildNormalManagementMonthData()` (a long, business-critical method also used by Normal Management Report v2) into a new service class, we widen its visibility (and three sibling methods') from `private` to `public` on `BackOfficeReportController` and call them from the new controller. This is a one-line-per-method diff with zero behavior change, versus a large risky move of code we don't have license to rewrite. It still satisfies the spec's real requirement: one source of truth for the calculation, called from two places.
- `LandlordContract` (`Modules\Sales\Entities\LandlordContract`) and `Vendor` (`Modules\Masters\Entities\Vendor`) both have `protected $guarded = []` already — no model changes needed there beyond the new `vatin_no` column.
- Money columns follow the existing convention: PHP `double` / Postgres `double precision`, rounded to 3 decimals with `round($x, 3)`, formatted with 3 decimals in Blade (`number_format($x, 3)`).
- Every new/modified route lives inside the existing `Route::group(['middleware' => 'prevent-back-history'], function () { ... })` block in `Modules/BackOffice/Routes/web.php` (see existing routes at that file's top level, e.g. line 740-742) — not inside a `backoffice` prefix group.
- Single permission `view_landlord_invoice_v2` gates every controller action (index/create/store/edit/update/destroy/ajax/print) — this module has no approval workflow, unlike v1 `LandlordInvoice`, so it does not need `add_/edit_/delete_` split permissions.
- **This repo has a large amount of pre-existing, unrelated, uncommitted work already sitting in the working tree** (a separate "Deposit Rent Report V2" / "Expense Details V2" feature spanning `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php` — 959 uncommitted lines — plus `Modules/BackOffice/Routes/web.php` and `Modules/BackOffice/Routes/breadcrumbs.php`, each with their own uncommitted additions). Every task touching those three specific files MUST isolate its commit from that unrelated content using the technique below — never `git add` those files directly.
- Do not mirror `BackOfficeReportController.php` into `server_upload_files/` — its `server_upload_files/` copy is already known stale from before this plan (established precedent in `docs/superpowers/plans/2026-09-07-landlord-contract-fee-components.md`). DO mirror your specific route/breadcrumb additions into `server_upload_files/Modules/BackOffice/Routes/web.php` and `breadcrumbs.php` (both exist there and are currently clean/unmodified) using the same isolation technique. New files (migrations, controller, models, views) are not mirrored into `server_upload_files/` — there is no established precedent requiring it for a brand-new feature's own files (the existing v1 `LandlordInvoiceController` and its views aren't mirrored there either), except menu-adding migrations, which the existing precedent does mirror — mirror those two.

### The commit-isolation technique (required for any task touching `BackOfficeReportController.php`, `Modules/BackOffice/Routes/web.php`, or `Modules/BackOffice/Routes/breadcrumbs.php`)

1. Make your edits on the live working-tree file as normal.
2. Before committing, save a full copy of the current (combined) file content to a temp path.
3. Get the clean starting point via `git show HEAD:<path>`.
4. Re-apply your exact same edits onto that clean HEAD version instead (find the same anchor text).
5. Write that clean result to the actual file path, `git add` it, verify `git diff --cached --stat` shows a small diff matching only your intended change (not hundreds of lines), commit.
6. Restore the file back to its full combined working-tree content (the copy from step 2).
7. Verify: `git diff HEAD --stat -- <path>` should show only the large pre-existing unrelated diff, not your change.

---

## Task 1: Vendor VATIN field

**Files:**
- Create: `Modules/Masters/Database/Migrations/2026_09_07_000001_add_vatin_no_to_vendors.php`
- Modify: `Modules/Masters/Http/Controllers/VendorController.php:133-149` (store), `:227-243` (update)
- Modify: `Modules/Masters/Resources/views/Vendors/add_edit.blade.php` (add a field near the other address fields, ~line 130-157)

**Interfaces:**
- Produces: `vendors.vatin_no` (string, nullable) — Task 6's `store()` reads `$vendor->vatin_no` to snapshot onto the invoice header.

- [ ] **Step 1: Write and run the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVatinNoToVendors extends Migration
{
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('vatin_no', 50)->nullable()->after('vendor_acc_no');
        });
    }

    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('vatin_no');
        });
    }
}
```

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=Modules/Masters/Database/Migrations/2026_09_07_000001_add_vatin_no_to_vendors.php --force
```
Expected: `Migrated: 2026_09_07_000001_add_vatin_no_to_vendors`

- [ ] **Step 2: Verify the column**

Write a one-off script `verify_tmp.php` in the project root:
```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$cols = \DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'vendors' AND column_name = 'vatin_no'");
echo count($cols) === 1 ? "OK\n" : "MISSING\n";
```
Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe verify_tmp.php` — expect `OK`. Then `rm verify_tmp.php`.

- [ ] **Step 3: Add the field to the Vendor create/edit form**

In `Modules/Masters/Resources/views/Vendors/add_edit.blade.php`, immediately after the `vendor_pc` field block (around line 150-157), insert a new field following the same `.col-sm-6 > .form-group` pattern used throughout this file:

```blade
<div class="col-sm-6">
    <div class="form-group">
        <label for="vatin_no">VATIN No</label>
         <div class="p-relative">
        <i class="fa fa-id-card icn-add" aria-hidden="true"></i>
        <input type="text" class="form-control" id="vatin_no" name="vatin_no" value="{{ old('vatin_no', isset($vendor)? $vendor->vatin_no : '' )}}" placeholder="Enter VATIN No">
      </div>
    </div>
</div>
```

- [ ] **Step 4: Persist it in the controller**

In `Modules/Masters/Http/Controllers/VendorController.php`, in `store()` (around line 147, right after `'bank_id' => $request->bank_id,`):

```php
         'bank_id' => $request->bank_id,
         'vatin_no' => $request->vatin_no,
         'created_by' =>  \Auth::user()->id
        ]);
```

And in `update()` (around line 241, right after `'bank_id' => $request->bank_id,`):

```php
         'bank_id' => $request->bank_id,
         'vatin_no' => $request->vatin_no,
         'updated_by' =>  \Auth::user()->id,
        ]);
```

No new validation rule is needed — `vatin_no` is optional.

- [ ] **Step 5: Manually verify in the browser**

Start the dev server if not already running, log in, go to Masters → Vendors → edit any landlord vendor, enter a VATIN value, save, reopen the edit form, and confirm the value persisted.

- [ ] **Step 6: Commit**

```bash
git add Modules/Masters/Database/Migrations/2026_09_07_000001_add_vatin_no_to_vendors.php Modules/Masters/Http/Controllers/VendorController.php Modules/Masters/Resources/views/Vendors/add_edit.blade.php
git commit -m "feat: add VATIN No field to vendors"
```

---

## Task 2: Landlord Invoice v2 schema — header table, line table, numbering prefixes

**Files:**
- Create: `Modules/BackOffice/Database/Migrations/2026_09_07_000002_create_landlord_invoice_v2_tables.php`
- Create: `Modules/BackOffice/Database/Migrations/2026_09_07_000003_add_landlord_invoice_v2_prefixes.php`

**Interfaces:**
- Produces: `landlord_invoice_v2` table (`id, invoice_type, invoice_no, invoice_date, vendor_id, landlord_contract_id, period_month, period_year, vendor_name, building_name, vendor_address, vatin_no, status, voided_at, voided_by, created_by, subtotal, vat_total, grand_total, created_at, updated_at`) and `landlord_invoice_v2_lines` (`id, landlord_invoice_v2_id, description, amount, vat_amount, line_order, created_at, updated_at`) — Task 4's models map onto these exact column names. Also produces two `configuration` rows keyed `landlord_invoice_v2_tax_invoice_prefix` and `landlord_invoice_v2_other_deductions_prefix` — Task 6's `store()` reads them by these exact `configuration_settings` values.

- [ ] **Step 1: Write the tables migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandlordInvoiceV2Tables extends Migration
{
    public function up()
    {
        Schema::create('landlord_invoice_v2', function (Blueprint $table) {
            $table->increments('id');
            $table->string('invoice_type', 30);
            $table->string('invoice_no', 30)->unique();
            $table->date('invoice_date');
            $table->unsignedInteger('vendor_id');
            $table->unsignedInteger('landlord_contract_id');
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->string('vendor_name')->nullable();
            $table->string('building_name')->nullable();
            $table->text('vendor_address')->nullable();
            $table->string('vatin_no', 50)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('voided_at')->nullable();
            $table->unsignedInteger('voided_by')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->double('subtotal')->default(0);
            $table->double('vat_total')->default(0);
            $table->double('grand_total')->default(0);
            $table->timestamps();
        });

        Schema::create('landlord_invoice_v2_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('landlord_invoice_v2_id');
            $table->string('description');
            $table->double('amount')->default(0);
            $table->double('vat_amount')->default(0);
            $table->unsignedInteger('line_order')->default(1);
            $table->timestamps();

            $table->foreign('landlord_invoice_v2_id')
                ->references('id')->on('landlord_invoice_v2')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('landlord_invoice_v2_lines');
        Schema::dropIfExists('landlord_invoice_v2');
    }
}
```

- [ ] **Step 2: Run it**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=Modules/BackOffice/Database/Migrations/2026_09_07_000002_create_landlord_invoice_v2_tables.php --force
```
Expected: `Migrated: 2026_09_07_000002_create_landlord_invoice_v2_tables`

- [ ] **Step 3: Write and run the prefix-seed migration**

```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddLandlordInvoiceV2Prefixes extends Migration
{
    public function up()
    {
        $year = (int) date('y');

        DB::table('configuration')->insert([
            [
                'configuration_name'             => 'general',
                'configuration_value'            => 'LTI',
                'configuration_settings'         => 'landlord_invoice_v2_tax_invoice_prefix',
                'configuration_icon'             => 'fa-file-text-o',
                'configuration_year'             => $year,
                'configuration_increment_value'  => 1,
                'created_by'                     => 1,
                'created_at'                     => now(),
                'updated_at'                     => now(),
            ],
            [
                'configuration_name'             => 'general',
                'configuration_value'            => 'LOD',
                'configuration_settings'         => 'landlord_invoice_v2_other_deductions_prefix',
                'configuration_icon'             => 'fa-file-text-o',
                'configuration_year'             => $year,
                'configuration_increment_value'  => 1,
                'created_by'                     => 1,
                'created_at'                     => now(),
                'updated_at'                     => now(),
            ],
        ]);
    }

    public function down()
    {
        DB::table('configuration')->whereIn('configuration_settings', [
            'landlord_invoice_v2_tax_invoice_prefix',
            'landlord_invoice_v2_other_deductions_prefix',
        ])->delete();
    }
}
```

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=Modules/BackOffice/Database/Migrations/2026_09_07_000003_add_landlord_invoice_v2_prefixes.php --force
```
Expected: `Migrated: 2026_09_07_000003_add_landlord_invoice_v2_prefixes`

- [ ] **Step 4: Verify**

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo \Schema::hasTable('landlord_invoice_v2') ? "table OK\n" : "table MISSING\n";
echo \Schema::hasTable('landlord_invoice_v2_lines') ? "lines table OK\n" : "lines table MISSING\n";
$rows = \DB::table('configuration')->whereIn('configuration_settings', ['landlord_invoice_v2_tax_invoice_prefix','landlord_invoice_v2_other_deductions_prefix'])->get();
echo $rows->count() === 2 ? "prefixes OK\n" : "prefixes MISSING (" . $rows->count() . ")\n";
```
Run with the PHP 7.4 binary, expect all three "OK" lines, then delete the script.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Database/Migrations/2026_09_07_000002_create_landlord_invoice_v2_tables.php Modules/BackOffice/Database/Migrations/2026_09_07_000003_add_landlord_invoice_v2_prefixes.php
git commit -m "feat: add landlord_invoice_v2 tables and numbering prefixes"
```

---

## Task 3: Menu — "Landlord Invoice v2" under Operations

**Files:**
- Create: `database/migrations/2026_09_07_000004_add_landlord_invoice_v2_menu.php`
- Create (mirror): `server_upload_files/database/migrations/2026_09_07_000004_add_landlord_invoice_v2_menu.php`

**Interfaces:**
- Produces: a `menu` row with `route_name = 'landlord-invoice-v2.index'`, `url_key = 'landlord-invoice-v2'`, `parent_menu` = the id of "Operations" (menu id 96 in the live DB, resolved by name here rather than hardcoded), plus a `view_landlord_invoice_v2` permission granted to every role.

- [ ] **Step 1: Write the migration**

Follow the exact by-name-resolution pattern already established in `database/migrations/2026_09_02_000001_add_expense_details_report_v2_menu.php` (resolves `PLM Module` → named child, not a hardcoded id), but resolving "Operations" instead of "Finance Reports", and using the resource route name convention already used by sibling Operations menu items (`landlord-contract.index` / `tenant-contract.index`, confirmed live in the `menu` table):

```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds "Landlord Invoice v2" under PLM Module -> Operations. Resolves the
 * parent by exact name under PLM Module specifically (not hardcoded id or a
 * fuzzy match) - see the Tenancy Details MERA menu fix (2026_08_15_000001)
 * and the Expense Details v2 menu (2026_09_02_000001) for why.
 */
class AddLandlordInvoiceV2Menu extends Migration
{
    public function up()
    {
        $plmModule = DB::table('menu')
            ->where('menu_name', 'PLM Module')
            ->where('parent_menu', 0)
            ->first();

        $operations = $plmModule
            ? DB::table('menu')
                ->where('menu_name', 'Operations')
                ->where('parent_menu', $plmModule->id)
                ->where('menutype', 1)
                ->first()
            : null;

        $parentMenuId = $operations ? $operations->id : 0;

        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Landlord Invoice v2',
            'menu_icon'   => 'fa-file-text-o',
            'route_name'  => 'landlord-invoice-v2.index',
            'url_key'     => 'landlord-invoice-v2',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('permissions')->insert([
            'name'       => 'view_landlord_invoice_v2',
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permission = DB::table('permissions')
            ->where('name', 'view_landlord_invoice_v2')
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
        $menu = DB::table('menu')->where('route_name', 'landlord-invoice-v2.index')->first();
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

- [ ] **Step 2: Run it**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=database/migrations/2026_09_07_000004_add_landlord_invoice_v2_menu.php --force
```
Expected: `Migrated: 2026_09_07_000004_add_landlord_invoice_v2_menu`

- [ ] **Step 3: Verify**

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$menu = \DB::table('menu')->where('route_name', 'landlord-invoice-v2.index')->first();
echo $menu ? "menu OK, parent={$menu->parent_menu}\n" : "menu MISSING\n";
$perm = \DB::table('permissions')->where('name', 'view_landlord_invoice_v2')->first();
echo $perm ? "permission OK\n" : "permission MISSING\n";
$grants = \DB::table('role_has_permissions')->where('permission_id', $perm->id ?? 0)->count();
echo "granted to $grants roles\n";
```
Expect: menu OK with `parent=96` (the live Operations id), permission OK, granted to roles > 0.

- [ ] **Step 4: Mirror and commit**

```bash
cp database/migrations/2026_09_07_000004_add_landlord_invoice_v2_menu.php server_upload_files/database/migrations/2026_09_07_000004_add_landlord_invoice_v2_menu.php
git add database/migrations/2026_09_07_000004_add_landlord_invoice_v2_menu.php server_upload_files/database/migrations/2026_09_07_000004_add_landlord_invoice_v2_menu.php
git commit -m "feat: add Landlord Invoice v2 menu under Operations"
```

---

## Task 4: Eloquent models

**Files:**
- Create: `Modules/BackOffice/Entities/LandlordInvoiceV2.php`
- Create: `Modules/BackOffice/Entities/LandlordInvoiceV2Line.php`

**Interfaces:**
- Consumes: tables from Task 2.
- Produces: `LandlordInvoiceV2::vendor()`, `::landlordContract()`, `::lines()` relations and `scopeActive()` — Task 6/7/9/10 controller and view code reference these exact method names.

- [ ] **Step 1: Write `LandlordInvoiceV2Line`**

```php
<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class LandlordInvoiceV2Line extends Model
{
    protected $table = 'landlord_invoice_v2_lines';
    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(LandlordInvoiceV2::class, 'landlord_invoice_v2_id');
    }
}
```

- [ ] **Step 2: Write `LandlordInvoiceV2`**

```php
<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Masters\Entities\Vendor;
use Modules\Sales\Entities\LandlordContract;

class LandlordInvoiceV2 extends Model
{
    protected $table = 'landlord_invoice_v2';
    protected $guarded = [];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function landlordContract()
    {
        return $this->belongsTo(LandlordContract::class, 'landlord_contract_id');
    }

    public function lines()
    {
        return $this->hasMany(LandlordInvoiceV2Line::class, 'landlord_invoice_v2_id')->orderBy('line_order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getInvoiceTypeLabelAttribute()
    {
        return $this->invoice_type === 'tax_invoice' ? 'Tax Invoice' : 'Other Deductions';
    }
}
```

- [ ] **Step 3: Verify with a one-off script**

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$inv = new \Modules\BackOffice\Entities\LandlordInvoiceV2();
echo get_class($inv->vendor()->getRelated()) . "\n";
echo get_class($inv->landlordContract()->getRelated()) . "\n";
echo get_class($inv->lines()->getRelated()) . "\n";
```
Run with the PHP 7.4 binary; expect `Modules\Masters\Entities\Vendor`, `Modules\Sales\Entities\LandlordContract`, `Modules\BackOffice\Entities\LandlordInvoiceV2Line`.

- [ ] **Step 4: Commit**

```bash
git add Modules/BackOffice/Entities/LandlordInvoiceV2.php Modules/BackOffice/Entities/LandlordInvoiceV2Line.php
git commit -m "feat: add LandlordInvoiceV2 and LandlordInvoiceV2Line models"
```

---

## Task 5: Widen and extend the calculation methods on `BackOfficeReportController`

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`

**Interfaces:**
- Consumes: existing `private function monthsTouchedByRange(string $fromDate, string $toDate): array` (line ~5084), `private function landlordTaxInvoiceLineAmounts(...)` (line ~5105), `private function buildNormalManagementMonthData(...)` (line ~5238), `private function landlordTaxInvoiceAmountInWords(float $total): string` (line ~5218).
- Produces: all four widened to `public`, unchanged bodies/signatures, plus two new `public` methods — `landlordOtherDeductionsLineAmounts(\Modules\Masters\Entities\Building $building, string $fromDate, string $toDate): array` returning `['mun_tax_charges', 'elect_water_charges', 'dewatering_charges', 'ac_amc_repair_charges', 'fas_amc_charges']` (all floats), and `landlordSubcontractorRepairMaintenanceAmount(int $vendorId, int $buildingId, string $fromDate, string $toDate): float`. Task 6's `calculationPreview()` calls all six methods by these exact names.

This task requires the commit-isolation technique described in Global Constraints (this file has 959 pre-existing uncommitted lines).

- [ ] **Step 1: Make the edits on the live file**

Change these four lines from `private function` to `public function` (keep everything else about each method identical):
- Line ~5084: `private function monthsTouchedByRange(...)` → `public function monthsTouchedByRange(...)`
- Line ~5105: `private function landlordTaxInvoiceLineAmounts(...)` → `public function landlordTaxInvoiceLineAmounts(...)`
- Line ~5218: `private function landlordTaxInvoiceAmountInWords(...)` → `public function landlordTaxInvoiceAmountInWords(...)`
- Line ~5238: `private function buildNormalManagementMonthData(...)` → `public function buildNormalManagementMonthData(...)`

Then add the two new methods directly after `landlordTaxInvoiceLineAmounts()`'s closing brace (after line ~5210, before the `landlordTaxInvoiceAmountInWords()` doc comment):

```php
/**
 * Sums Mun Tax, Elect & Water, Dewatering, AMC/Repair for A/C Units, and
 * AMC for F.A.S for one building over the given date range, reusing
 * buildNormalManagementMonthData() the same way landlordTaxInvoiceLineAmounts()
 * does for Repair & Maintenance - same building-level expense source, just
 * matched against different expense_name categories. No vendor-ownership
 * guard is applied here (unlike Management Fee) because these are
 * building-level utility/AMC expenses, not contract-derived fees.
 */
public function landlordOtherDeductionsLineAmounts(\Modules\Masters\Entities\Building $building, string $fromDate, string $toDate): array
{
    $monthsTouched = $this->monthsTouchedByRange($fromDate, $toDate);

    $byYear = [];
    foreach ($monthsTouched as $mt) {
        $byYear[$mt['year']][] = $mt['month'];
    }

    $munTax = $electWater = $dewatering = $acMaintenance = $fas = 0.0;
    foreach ($byYear as $yr => $months) {
        $monthData = $this->buildNormalManagementMonthData($building, $yr, $months);
        foreach ($months as $m) {
            $data = $monthData[$m] ?? null;
            if (!$data) continue;

            foreach ($data['expenses'] ?? [] as $exp) {
                switch ($exp->expense_name) {
                    case 'MUNICIPAL TAX':
                        $munTax += (float) $exp->expense_amount;
                        break;
                    case 'ELECTRICITY & WATER':
                        $electWater += (float) $exp->expense_amount;
                        break;
                    case 'DEWATERING':
                        $dewatering += (float) $exp->expense_amount;
                        break;
                    case 'A C MAINTENANCE':
                    case 'CIT - AC MAINTENANCE':
                        $acMaintenance += (float) $exp->expense_amount;
                        break;
                    case 'FIRE ALARM SYSTEM MAINT.':
                    case 'CIT - FIRE ALARAM SYSTEM MAINT.':
                        $fas += (float) $exp->expense_amount;
                        break;
                }
            }
        }
    }

    return [
        'mun_tax_charges'       => round($munTax, 3),
        'elect_water_charges'   => round($electWater, 3),
        'dewatering_charges'    => round($dewatering, 3),
        'ac_amc_repair_charges' => round($acMaintenance, 3),
        'fas_amc_charges'       => round($fas, 3),
    ];
}

/**
 * Sums Repair & Maintenance charges billed by a sub-contractor (i.e. NOT
 * generated from an in-house technician service report) for one vendor +
 * building over the given date range - same in-house/sub-contractor signal
 * (mid.service_report_id) as Maintenance Invoice Report v2
 * (MaintenanceReportController::maintenanceInvoiceReportV2Query()).
 */
public function landlordSubcontractorRepairMaintenanceAmount(int $vendorId, int $buildingId, string $fromDate, string $toDate): float
{
    $row = DB::selectOne("
        SELECT COALESCE(SUM(CAST(mid.debit_amt AS NUMERIC)), 0) AS total
        FROM maintenance_invoice_details mid
        JOIN maintenance_invoices mi ON mi.id = mid.maintenance_invoice_id
        WHERE mi.vendor_id = ? AND mid.building_id = ?
          AND mi.maintenance_invoice_date BETWEEN ? AND ?
          AND mi.maintenance_invoice_status != 2 AND mi.deleted_at IS NULL
          AND mid.service_report_id IS NULL
    ", [$vendorId, $buildingId, $fromDate, $toDate]);

    return round((float) ($row->total ?? 0), 3);
}
```

- [ ] **Step 2: Verify the existing Tax Invoice Report is unaffected**

Manually generate a report for a known landlord/date-range in the browser (Operations menu → Landlord Tax Invoice Report), confirm it still downloads a PDF with the same line items and amounts as before this change (visibility changes and additive new methods cannot alter existing behavior, but this confirms no typo broke the file).

- [ ] **Step 3: Verify the two new methods with a one-off script**

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$controller = $app->make(\Modules\BackOffice\Http\Controllers\BackOfficeReportController::class);
$building = \Modules\Masters\Entities\Building::query()->first();
if (!$building) { echo "no building to test with\n"; exit; }
$amounts = $controller->landlordOtherDeductionsLineAmounts($building, date('Y-m-01'), date('Y-m-t'));
print_r($amounts);
$sub = $controller->landlordSubcontractorRepairMaintenanceAmount(1, $building->id, date('Y-m-01'), date('Y-m-t'));
echo "subcontractor total: $sub\n";
```
Run with the PHP 7.4 binary; expect no fatal errors and an array with the five expected keys (values may legitimately be 0.0 if no matching expenses exist for the test month — that is not a failure, it confirms the query runs and returns the right shape).

- [ ] **Step 4: Commit using the isolation technique**

```bash
cp Modules/BackOffice/Http/Controllers/BackOfficeReportController.php /tmp/borc_combined.php
git show HEAD:Modules/BackOffice/Http/Controllers/BackOfficeReportController.php > Modules/BackOffice/Http/Controllers/BackOfficeReportController.php
```
Now re-apply Step 1's edits (the 4 visibility changes + 2 new methods) onto this clean HEAD version of the file, in the same locations (search for `private function landlordTaxInvoiceLineAmounts`, `private function buildNormalManagementMonthData`, etc. — they exist at HEAD too since they were committed in the Landlord Tax Invoice Report and Fee Components plans).
```bash
git add Modules/BackOffice/Http/Controllers/BackOfficeReportController.php
git diff --cached --stat
```
Expect a diff of roughly 4 changed lines + ~90 new lines — not hundreds. If it's large, stop and re-check you applied the edits to the clean HEAD copy, not the combined one.
```bash
git commit -m "feat: expose landlord tax invoice calculation methods for reuse, add Other Deductions and sub-contractor R&M calculations"
cp /tmp/borc_combined.php Modules/BackOffice/Http/Controllers/BackOfficeReportController.php
git diff HEAD --stat -- Modules/BackOffice/Http/Controllers/BackOfficeReportController.php
```
Expect this last diff to show only the large pre-existing unrelated changes, not your edits (they're now in git history, so the restored combined file's diff against HEAD should be smaller than before by exactly your change).

---

## Task 6: Controller — index, create, store

**Files:**
- Create: `Modules/BackOffice/Http/Controllers/LandlordInvoiceV2Controller.php`

**Interfaces:**
- Consumes: `LandlordInvoiceV2`, `LandlordInvoiceV2Line` (Task 4); `BackOfficeReportController::landlordTaxInvoiceLineAmounts()` (Task 5); `App\Setting` (`Setting::where('configuration_settings', $key)->first()` / `->update([...])`, matching `LandlordInvoiceController::landlordInvoiceCode()`'s pattern).
- Produces: `index(Request $request)`, `create()`, `store(Request $request)` — Task 7 adds `edit`/`update`/`destroy` to the same class, Task 8 adds the AJAX + print actions.

- [ ] **Step 1: Write the controller shell + index + create + store**

```php
<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Setting;
use Modules\Masters\Entities\Vendor;
use Modules\Sales\Entities\LandlordContract;
use Modules\BackOffice\Entities\LandlordInvoiceV2;

class LandlordInvoiceV2Controller extends Controller
{
    protected $calc;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_landlord_invoice_v2');
        $this->calc = app(BackOfficeReportController::class);
    }

    public function index(Request $request)
    {
        $query = LandlordInvoiceV2::with(['vendor', 'landlordContract.buildingInfo']);

        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->where('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('invoice_date', '<=', $request->to_date);
        }

        $landlordInvoicesV2 = $query->orderBy('id', 'desc')->paginate(20)->appends($request->query());
        $vendors = Vendor::active()->orderBy('vendor_name')->get();

        return view('backoffice::LandlordInvoiceV2.index', compact('landlordInvoicesV2', 'vendors'));
    }

    public function create()
    {
        $vendors = Vendor::active()->orderBy('vendor_name')->get();
        return view('backoffice::LandlordInvoiceV2.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_type'          => 'required|in:tax_invoice,other_deductions',
            'invoice_date'          => 'required|date',
            'vendor_id'             => 'required|exists:vendors,id',
            'landlord_contract_id'  => 'required|exists:landlord_contract,id',
            'period_month'          => 'required|integer|min:1|max:12',
            'period_year'           => 'required|integer|min:2000|max:2100',
            'lines'                 => 'required|array|min:1',
            'lines.*.description'   => 'required|string',
            'lines.*.amount'        => 'required|numeric',
        ]);

        $vendor   = Vendor::findOrFail($request->vendor_id);
        $contract = LandlordContract::with('buildingInfo')->findOrFail($request->landlord_contract_id);

        $prefixKey = $request->invoice_type === 'tax_invoice'
            ? 'landlord_invoice_v2_tax_invoice_prefix'
            : 'landlord_invoice_v2_other_deductions_prefix';

        $setting = Setting::where('configuration_settings', $prefixKey)->first();
        abort_if(!$setting, 500, 'Invoice numbering is not configured for this invoice type.');

        $nextCode = $setting->configuration_value . $setting->configuration_year
            . str_pad($setting->configuration_increment_value, 5, '0', STR_PAD_LEFT);

        $subtotal = 0.0;
        $vatTotal = 0.0;
        $lineRows = [];
        foreach (array_values($request->lines) as $i => $line) {
            $amount = round((float) $line['amount'], 3);
            $vat = $request->invoice_type === 'tax_invoice' ? round($amount * 0.05, 3) : 0.0;
            $subtotal += $amount;
            $vatTotal += $vat;
            $lineRows[] = [
                'description' => $line['description'],
                'amount'      => $amount,
                'vat_amount'  => $vat,
                'line_order'  => $i + 1,
            ];
        }

        $invoice = LandlordInvoiceV2::create([
            'invoice_type'         => $request->invoice_type,
            'invoice_no'           => $nextCode,
            'invoice_date'         => $request->invoice_date,
            'vendor_id'            => $vendor->id,
            'landlord_contract_id' => $contract->id,
            'period_month'         => $request->period_month,
            'period_year'          => $request->period_year,
            'vendor_name'          => $vendor->vendor_name,
            'building_name'        => optional($contract->buildingInfo)->building_name,
            'vendor_address'       => $vendor->vendor_contact_address,
            'vatin_no'             => $vendor->vatin_no,
            'status'               => 'active',
            'subtotal'             => round($subtotal, 3),
            'vat_total'            => round($vatTotal, 3),
            'grand_total'          => round($subtotal + $vatTotal, 3),
            'created_by'           => \Auth::user()->id,
        ]);

        $invoice->lines()->createMany($lineRows);

        Setting::where('configuration_settings', $prefixKey)
            ->update(['configuration_increment_value' => $setting->configuration_increment_value + 1]);

        session()->flash('success', 'Landlord Invoice Created: ' . $nextCode);
        return redirect()->route('landlord-invoice-v2.index');
    }
}
```

- [ ] **Step 2: Manual verification (deferred to Task 9's browser test)**

Routes and views don't exist yet — this task's code is exercised once Task 9's routes and views land. Continue to Task 7.

- [ ] **Step 3: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/LandlordInvoiceV2Controller.php
git commit -m "feat: add LandlordInvoiceV2Controller index/create/store"
```

---

## Task 7: Controller — edit, update, destroy (void)

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/LandlordInvoiceV2Controller.php`

**Interfaces:**
- Consumes: `LandlordInvoiceV2` (Task 4), `LandlordInvoiceV2::$status` values `'active'` / `'voided'`.
- Produces: `edit(LandlordInvoiceV2 $landlordInvoiceV2)`, `update(Request $request, LandlordInvoiceV2 $landlordInvoiceV2)`, `destroy(LandlordInvoiceV2 $landlordInvoiceV2)`.

- [ ] **Step 1: Add the three methods**

Add these methods to the end of the class body (before the final closing `}`):

```php
    public function edit(LandlordInvoiceV2 $landlordInvoiceV2)
    {
        abort_if($landlordInvoiceV2->status === 'voided', 403, 'Voided invoices cannot be edited.');
        $landlordInvoiceV2->load('lines');
        return view('backoffice::LandlordInvoiceV2.edit', compact('landlordInvoiceV2'));
    }

    public function update(Request $request, LandlordInvoiceV2 $landlordInvoiceV2)
    {
        abort_if($landlordInvoiceV2->status === 'voided', 403, 'Voided invoices cannot be edited.');

        $request->validate([
            'invoice_date'    => 'required|date',
            'lines'           => 'required|array|min:1',
            'lines.*.id'      => 'required|exists:landlord_invoice_v2_lines,id',
            'lines.*.amount'  => 'required|numeric',
        ]);

        $subtotal = 0.0;
        $vatTotal = 0.0;
        foreach ($request->lines as $line) {
            $lineModel = $landlordInvoiceV2->lines()->findOrFail($line['id']);
            $amount = round((float) $line['amount'], 3);
            $vat = $landlordInvoiceV2->invoice_type === 'tax_invoice' ? round($amount * 0.05, 3) : 0.0;
            $lineModel->update(['amount' => $amount, 'vat_amount' => $vat]);
            $subtotal += $amount;
            $vatTotal += $vat;
        }

        $landlordInvoiceV2->update([
            'invoice_date' => $request->invoice_date,
            'subtotal'     => round($subtotal, 3),
            'vat_total'    => round($vatTotal, 3),
            'grand_total'  => round($subtotal + $vatTotal, 3),
        ]);

        session()->flash('success', 'Landlord Invoice Updated');
        return redirect()->route('landlord-invoice-v2.index');
    }

    public function destroy(LandlordInvoiceV2 $landlordInvoiceV2)
    {
        abort_if($landlordInvoiceV2->status === 'voided', 403, 'This invoice is already voided.');

        $landlordInvoiceV2->update([
            'status'    => 'voided',
            'voided_at' => now(),
            'voided_by' => \Auth::user()->id,
        ]);

        session()->flash('success', 'Landlord Invoice Voided: ' . $landlordInvoiceV2->invoice_no);
        return redirect()->route('landlord-invoice-v2.index');
    }
```

- [ ] **Step 2: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/LandlordInvoiceV2Controller.php
git commit -m "feat: add LandlordInvoiceV2Controller edit/update/void"
```

---

## Task 8: Controller — AJAX endpoints and print

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/LandlordInvoiceV2Controller.php`

**Interfaces:**
- Consumes: `BackOfficeReportController::landlordTaxInvoiceLineAmounts()`, `::landlordOtherDeductionsLineAmounts()`, `::landlordSubcontractorRepairMaintenanceAmount()`, `::landlordTaxInvoiceAmountInWords()` (all Task 5).
- Produces: `contractsByVendor(Request $request)`, `contractDetails(LandlordContract $landlordContract)`, `calculationPreview(Request $request)`, `print(LandlordInvoiceV2 $landlordInvoiceV2)` — Task 9's `create.blade.php`/`edit.blade.php` JS calls the first three by their route names; Task 10's `index.blade.php` links to `print`.

- [ ] **Step 1: Add the four methods**

Add before the final closing `}` of the class:

```php
    public function contractsByVendor(Request $request)
    {
        $contracts = LandlordContract::with('buildingInfo')
            ->where('vendor_id', $request->vendor_id)
            ->where('landlord_contract_status', 1)
            ->get()
            ->map(function ($c) {
                return [
                    'id'    => $c->id,
                    'label' => $c->landlord_contract_no . ' - ' . optional($c->buildingInfo)->building_name,
                ];
            });

        return response()->json($contracts);
    }

    public function contractDetails(LandlordContract $landlordContract)
    {
        $vendor = $landlordContract->vendorInfo;

        return response()->json([
            'vendor_name'    => optional($vendor)->vendor_name,
            'vendor_address' => optional($vendor)->vendor_contact_address,
            'vatin_no'       => optional($vendor)->vatin_no,
            'building_name'  => optional($landlordContract->buildingInfo)->building_name,
        ]);
    }

    public function calculationPreview(Request $request)
    {
        $request->validate([
            'landlord_contract_id' => 'required|exists:landlord_contract,id',
            'invoice_type'         => 'required|in:tax_invoice,other_deductions',
            'period_month'         => 'required|integer|min:1|max:12',
            'period_year'          => 'required|integer|min:2000|max:2100',
        ]);

        $contract = LandlordContract::with('buildingInfo')->findOrFail($request->landlord_contract_id);
        $building = $contract->buildingInfo;
        abort_if(!$building, 404, 'This contract has no building assigned.');

        $fromDate = sprintf('%04d-%02d-01', $request->period_year, $request->period_month);
        $toDate   = date('Y-m-t', strtotime($fromDate));

        if ($request->invoice_type === 'tax_invoice') {
            $amounts = $this->calc->landlordTaxInvoiceLineAmounts($building, $fromDate, $toDate, $contract->vendor_id);
            $lines = [
                ['description' => 'MANAGEMENT FEES FOR ' . $amounts['period_label'], 'amount' => $amounts['management_fee']],
                ['description' => 'CLEANING CHARGES FOR ' . $amounts['period_label'], 'amount' => $amounts['cleaning_charge']],
                ['description' => 'REPAIR AND MAINTENANCE CHARGES', 'amount' => $amounts['repair_maintenance']],
            ];
        } else {
            $amounts = $this->calc->landlordOtherDeductionsLineAmounts($building, $fromDate, $toDate);
            $subAmount = $this->calc->landlordSubcontractorRepairMaintenanceAmount($contract->vendor_id, $building->id, $fromDate, $toDate);
            $lines = [
                ['description' => 'MUN TAX CHARGES', 'amount' => $amounts['mun_tax_charges']],
                ['description' => 'ELECT. AND WATER CHARGES', 'amount' => $amounts['elect_water_charges']],
                ['description' => 'DEWATERING CHARGES', 'amount' => $amounts['dewatering_charges']],
                ['description' => 'AMC / REPAIR AND MAINTENANCE CHARGES FOR A/C UNITS', 'amount' => $amounts['ac_amc_repair_charges']],
                ['description' => 'AMC FOR F.A.S FOR THE PERIOD', 'amount' => $amounts['fas_amc_charges']],
                ['description' => 'REPAIR AND MAINTENANCE CHARGES (SUB-CONTRACTOR)', 'amount' => $subAmount],
            ];
        }

        return response()->json(['lines' => $lines]);
    }

    public function print(LandlordInvoiceV2 $landlordInvoiceV2)
    {
        $landlordInvoiceV2->load('lines');

        $lines = $landlordInvoiceV2->lines->map(function ($l) {
            return [
                'desc'       => $l->description,
                'qty'        => 1.000,
                'unit_price' => (float) $l->amount,
                'amount'     => (float) $l->amount,
                'vat'        => (float) $l->vat_amount,
                'total'      => round((float) $l->amount + (float) $l->vat_amount, 3),
            ];
        })->toArray();

        $data = [
            'invoice'       => $landlordInvoiceV2,
            'lines'         => $lines,
            'totalAmount'   => (float) $landlordInvoiceV2->subtotal,
            'totalVat'      => (float) $landlordInvoiceV2->vat_total,
            'totalDue'      => (float) $landlordInvoiceV2->grand_total,
            'amountInWords' => $this->calc->landlordTaxInvoiceAmountInWords((float) $landlordInvoiceV2->grand_total),
        ];

        $pdf = \PDF::loadView('backoffice::LandlordInvoiceV2.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->stream($landlordInvoiceV2->invoice_no . '.pdf');
    }
```

- [ ] **Step 2: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/LandlordInvoiceV2Controller.php
git commit -m "feat: add LandlordInvoiceV2Controller AJAX endpoints and PDF print"
```

---

## Task 9: Routes and breadcrumbs

**Files:**
- Modify: `Modules/BackOffice/Routes/web.php`
- Modify: `Modules/BackOffice/Routes/breadcrumbs.php`
- Mirror: `server_upload_files/Modules/BackOffice/Routes/web.php`, `server_upload_files/Modules/BackOffice/Routes/breadcrumbs.php`

**Interfaces:**
- Consumes: `LandlordInvoiceV2Controller` (Tasks 6-8).
- Produces: named routes `landlord-invoice-v2.index/create/store/edit/update/destroy`, `landlordInvoiceV2ContractsByVendor`, `landlordInvoiceV2ContractDetails`, `landlordInvoiceV2CalculationPreview`, `landlordInvoiceV2Print` — Task 10's views reference these exact route names.

This task requires the commit-isolation technique (both files have pre-existing uncommitted content: `breadcrumbs.php` +10 lines, `web.php` +6 lines).

- [ ] **Step 1: Add the routes**

In `Modules/BackOffice/Routes/web.php`, inside the existing `Route::group(['middleware' => 'prevent-back-history'], function() { ... })` block (add near the existing Landlord Tax Invoice Report routes at line ~740-742):

```php
Route::resource('landlord-invoice-v2', 'LandlordInvoiceV2Controller')->except(['show']);
Route::get('landlord-invoice-v2-contracts-by-vendor', 'LandlordInvoiceV2Controller@contractsByVendor')->name('landlordInvoiceV2ContractsByVendor');
Route::get('landlord-invoice-v2-contract-details/{landlordContract}', 'LandlordInvoiceV2Controller@contractDetails')->name('landlordInvoiceV2ContractDetails');
Route::get('landlord-invoice-v2-calculation-preview', 'LandlordInvoiceV2Controller@calculationPreview')->name('landlordInvoiceV2CalculationPreview');
Route::get('landlord-invoice-v2/{landlordInvoiceV2}/print', 'LandlordInvoiceV2Controller@print')->name('landlordInvoiceV2Print');
```

In `Modules/BackOffice/Routes/breadcrumbs.php` (near the `showLandlordTaxInvoiceReport` breadcrumb at line ~794-797):

```php
Breadcrumbs::for('landlord-invoice-v2.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Invoice v2', route('landlord-invoice-v2.index'));
});

Breadcrumbs::for('landlord-invoice-v2.create', function ($trail) {
    $trail->parent('landlord-invoice-v2.index');
    $trail->push('Add Invoice', route('landlord-invoice-v2.create'));
});

Breadcrumbs::for('landlord-invoice-v2.edit', function ($trail) {
    $trail->parent('landlord-invoice-v2.index');
    $trail->push('Edit Invoice', route('landlord-invoice-v2.index'));
});
```

- [ ] **Step 2: Verify the routes register**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan route:list | grep landlord-invoice-v2
```
Expect 7 rows: index/create/store/edit/update/destroy plus the print route, and separately grep for the three named AJAX routes:
```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan route:list | grep landlordInvoiceV2
```
Expect 4 rows (the 3 AJAX + print).

- [ ] **Step 3: Commit using the isolation technique (both files)**

```bash
cp Modules/BackOffice/Routes/web.php /tmp/web_combined.php
cp Modules/BackOffice/Routes/breadcrumbs.php /tmp/breadcrumbs_combined.php
git show HEAD:Modules/BackOffice/Routes/web.php > Modules/BackOffice/Routes/web.php
git show HEAD:Modules/BackOffice/Routes/breadcrumbs.php > Modules/BackOffice/Routes/breadcrumbs.php
```
Re-apply Step 1's additions onto these clean HEAD versions (same anchor locations).
```bash
git add Modules/BackOffice/Routes/web.php Modules/BackOffice/Routes/breadcrumbs.php
git diff --cached --stat
```
Expect roughly +5 lines in web.php and +15 in breadcrumbs.php — not the pre-existing +6/+10.
```bash
git commit -m "feat: add Landlord Invoice v2 routes and breadcrumbs"
```
Now mirror the same isolated diff into `server_upload_files/`:
```bash
cp Modules/BackOffice/Routes/web.php server_upload_files/Modules/BackOffice/Routes/web.php
cp Modules/BackOffice/Routes/breadcrumbs.php server_upload_files/Modules/BackOffice/Routes/breadcrumbs.php
git add server_upload_files/Modules/BackOffice/Routes/web.php server_upload_files/Modules/BackOffice/Routes/breadcrumbs.php
git commit -m "chore: sync Landlord Invoice v2 routes/breadcrumbs to server_upload_files/"
```
Restore the live files to their full combined working-tree content:
```bash
cp /tmp/web_combined.php Modules/BackOffice/Routes/web.php
cp /tmp/breadcrumbs_combined.php Modules/BackOffice/Routes/breadcrumbs.php
```
Verify: `git diff HEAD --stat -- Modules/BackOffice/Routes/web.php Modules/BackOffice/Routes/breadcrumbs.php` should show only the pre-existing unrelated diff (now shrunk by exactly your committed change), not your addition.

Note: since `server_upload_files/Modules/BackOffice/Routes/web.php` and `breadcrumbs.php` were clean/unmodified before this task, no isolation trick is needed on the `server_upload_files/` copies themselves — a plain `cp` + commit is safe there.

---

## Task 10: View — create and edit forms

**Files:**
- Create: `Modules/BackOffice/Resources/views/LandlordInvoiceV2/create.blade.php`
- Create: `Modules/BackOffice/Resources/views/LandlordInvoiceV2/edit.blade.php`

**Interfaces:**
- Consumes: `route('landlord-invoice-v2.store')`, `route('landlord-invoice-v2.update', $landlordInvoiceV2)`, `route('landlordInvoiceV2ContractsByVendor')`, `route('landlordInvoiceV2ContractDetails', ':id')`, `route('landlordInvoiceV2CalculationPreview')` (Task 9); `$vendors` (Task 6's `create()`), `$landlordInvoiceV2` with `->lines` loaded (Task 7's `edit()`).

- [ ] **Step 1: Write `create.blade.php`**

Following the layout conventions in `Modules/BackOffice/Resources/views/LandlordInvoice/add_invoice.blade.php` (`@extends('layouts.plms-app')`, `.card.card-box`, `.dataSearchBox`, `.form-group`) and the vendor-driven AJAX pattern already used elsewhere in this module:

```blade
@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class="pull-left">
            <div class="page-title">Add Landlord Invoice</div>
        </div>
        {{ Breadcrumbs::render('landlord-invoice-v2.create') }}
    </div>
</div>

<form action="{{ route('landlord-invoice-v2.store') }}" method="POST" id="liv2_form" class="form-horizontal" data-toggle="validator">
{{ csrf_field() }}
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice Type<small class="textRed">*</small></label>
                <select class="form-control" id="invoice_type" name="invoice_type" required>
                    <option value="">Select Invoice Type</option>
                    <option value="tax_invoice">Tax Invoice</option>
                    <option value="other_deductions">Other Deductions</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice No.</label>
                <input type="text" class="form-control" value="(auto-generated on save)" disabled>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice Date<small class="textRed">*</small></label>
                <input type="date" class="form-control" id="invoice_date" name="invoice_date" required value="{{ old('invoice_date', date('Y-m-d')) }}">
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Vendor<small class="textRed">*</small></label>
                <select class="form-control" id="vendor_id" name="vendor_id" required>
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $v)
                    <option value="{{ $v->id }}">{{ $v->vendor_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Contract<small class="textRed">*</small></label>
                <select class="form-control" id="landlord_contract_id" name="landlord_contract_id" required disabled>
                    <option value="">Select Vendor First</option>
                </select>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Month<small class="textRed">*</small></label>
                <select class="form-control" id="period_month" name="period_month" required>
                    <option value="">Month</option>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Year<small class="textRed">*</small></label>
                <input type="number" class="form-control" id="period_year" name="period_year" required value="{{ date('Y') }}">
            </div>
        </div>
    </div>
</div>

<h3>Vendor / Building Details</h3>
<div class="dataSearchBox">
    <div class="row">
        <div class="col-lg-6 p-t-20"><h5 class="details"><b>Vendor Name: </b><span id="disp_vendor_name">-</span></h5></div>
        <div class="col-lg-6 p-t-20"><h5 class="details"><b>Building Name: </b><span id="disp_building_name">-</span></h5></div>
        <div class="col-lg-6 p-t-20"><h5 class="details"><b>Vendor Address: </b><span id="disp_vendor_address">-</span></h5></div>
        <div class="col-lg-6 p-t-20"><h5 class="details"><b>VATIN No: </b><span id="disp_vatin_no">-</span></h5></div>
    </div>
</div>

<h3>Invoice Lines</h3>
<div class="dataSearchBox">
    <div class="row">
        <table class="table" id="liv2_lines_table">
            <thead>
                <tr><th>Description</th><th>Amount (OMR)</th></tr>
            </thead>
            <tbody id="liv2_lines_body">
                <tr><td colspan="2">Select vendor, contract, invoice type and month to load calculated lines.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-12">
            <button type="submit" class="btn btn-primary">SAVE</button>
        </div>
    </div>
</div>

</div>
</div>
</div>
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    var contractsUrl = '{{ route("landlordInvoiceV2ContractsByVendor") }}';
    var detailsUrlBase = '{{ url("landlord-invoice-v2-contract-details") }}';
    var previewUrl = '{{ route("landlordInvoiceV2CalculationPreview") }}';

    function maybeLoadPreview() {
        var contractId = $('#landlord_contract_id').val();
        var invoiceType = $('#invoice_type').val();
        var month = $('#period_month').val();
        var year = $('#period_year').val();
        if (!contractId || !invoiceType || !month || !year) return;

        $.getJSON(previewUrl, {
            landlord_contract_id: contractId,
            invoice_type: invoiceType,
            period_month: month,
            period_year: year
        }, function (res) {
            var rows = '';
            $.each(res.lines, function (i, line) {
                rows += '<tr>' +
                    '<td><input type="hidden" name="lines[' + i + '][description]" value="' + line.description + '">' + line.description + '</td>' +
                    '<td><input type="number" step="0.001" class="form-control" name="lines[' + i + '][amount]" value="' + line.amount + '"></td>' +
                    '</tr>';
            });
            $('#liv2_lines_body').html(rows);
        });
    }

    $('#vendor_id').on('change', function () {
        var vendorId = $(this).val();
        var $contract = $('#landlord_contract_id');
        $contract.prop('disabled', true).html('<option value="">Loading...</option>');
        $('#disp_vendor_name, #disp_building_name, #disp_vendor_address, #disp_vatin_no').text('-');

        if (!vendorId) {
            $contract.html('<option value="">Select Vendor First</option>');
            return;
        }

        $.getJSON(contractsUrl, { vendor_id: vendorId }, function (contracts) {
            var opts = '<option value="">Select Contract</option>';
            $.each(contracts, function (i, c) {
                opts += '<option value="' + c.id + '">' + c.label + '</option>';
            });
            $contract.html(opts).prop('disabled', false);
        });
    });

    $('#landlord_contract_id').on('change', function () {
        var contractId = $(this).val();
        if (!contractId) return;

        $.getJSON(detailsUrlBase + '/' + contractId, function (details) {
            $('#disp_vendor_name').text(details.vendor_name || '-');
            $('#disp_building_name').text(details.building_name || '-');
            $('#disp_vendor_address').text(details.vendor_address || '-');
            $('#disp_vatin_no').text(details.vatin_no || '-');
        });

        maybeLoadPreview();
    });

    $('#invoice_type, #period_month, #period_year').on('change', maybeLoadPreview);
});
</script>
@endsection
```

- [ ] **Step 2: Write `edit.blade.php`**

Locked fields (type/no/vendor/contract) shown read-only; only date and line amounts editable:

```blade
@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class="pull-left">
            <div class="page-title">Edit Landlord Invoice</div>
        </div>
        {{ Breadcrumbs::render('landlord-invoice-v2.edit') }}
    </div>
</div>

<form action="{{ route('landlord-invoice-v2.update', $landlordInvoiceV2) }}" method="POST" class="form-horizontal" data-toggle="validator">
{{ csrf_field() }}
{{ method_field('PUT') }}
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice No.</label>
                <input type="text" class="form-control" value="{{ $landlordInvoiceV2->invoice_no }}" disabled>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice Type</label>
                <input type="text" class="form-control" value="{{ $landlordInvoiceV2->invoice_type_label }}" disabled>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Vendor</label>
                <input type="text" class="form-control" value="{{ $landlordInvoiceV2->vendor_name }}" disabled>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Building</label>
                <input type="text" class="form-control" value="{{ $landlordInvoiceV2->building_name }}" disabled>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Invoice Date<small class="textRed">*</small></label>
                <input type="date" class="form-control" name="invoice_date" required value="{{ old('invoice_date', \Carbon\Carbon::parse($landlordInvoiceV2->invoice_date)->format('Y-m-d')) }}">
            </div>
        </div>
    </div>
</div>

<h3>Invoice Lines</h3>
<div class="dataSearchBox">
    <div class="row">
        <table class="table">
            <thead><tr><th>Description</th><th>Amount (OMR)</th></tr></thead>
            <tbody>
                @foreach($landlordInvoiceV2->lines as $i => $line)
                <tr>
                    <td>
                        <input type="hidden" name="lines[{{ $i }}][id]" value="{{ $line->id }}">
                        {{ $line->description }}
                    </td>
                    <td><input type="number" step="0.001" class="form-control" name="lines[{{ $i }}][amount]" value="{{ old('lines.'.$i.'.amount', $line->amount) }}"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-12">
            <button type="submit" class="btn btn-primary">SAVE</button>
        </div>
    </div>
</div>

</div>
</div>
</div>
</form>
@endsection
```

- [ ] **Step 3: Manual verification in the browser**

Go to Operations → Landlord Invoice v2 → Add, select a Tax Invoice type + a vendor with an active landlord contract + the current month, confirm the contract dropdown populates, confirm vendor/building/address/VATIN display fields fill in, confirm the three Tax Invoice lines populate with amounts (0.000 is fine if there's no billing data for the test month), edit an amount, save, and confirm it redirects to the list with a success message. Then open Edit on that new invoice and confirm the locked fields show correctly and only date/amounts are editable.

- [ ] **Step 4: Commit**

```bash
git add Modules/BackOffice/Resources/views/LandlordInvoiceV2/create.blade.php Modules/BackOffice/Resources/views/LandlordInvoiceV2/edit.blade.php
git commit -m "feat: add Landlord Invoice v2 create/edit views"
```

---

## Task 11: View — list

**Files:**
- Create: `Modules/BackOffice/Resources/views/LandlordInvoiceV2/index.blade.php`

**Interfaces:**
- Consumes: `$landlordInvoicesV2` (paginated, Task 6's `index()`), `$vendors` (same), routes `landlord-invoice-v2.create/edit/destroy`, `landlordInvoiceV2Print` (Task 9).

- [ ] **Step 1: Write the view**

```blade
@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class="pull-left">
            <div class="page-title">Landlord Invoice v2</div>
        </div>
        {{ Breadcrumbs::render('landlord-invoice-v2.index') }}
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-box">
            <div class="card-body">
                <a href="{{ route('landlord-invoice-v2.create') }}" class="btn btn-primary pull-right">Add Invoice</a>
                <div class="clearfix"></div>

                <form method="GET" class="form-inline" style="margin-bottom:15px;">
                    <select name="invoice_type" class="form-control" style="margin-right:10px;">
                        <option value="">All Types</option>
                        <option value="tax_invoice" {{ request('invoice_type') == 'tax_invoice' ? 'selected' : '' }}>Tax Invoice</option>
                        <option value="other_deductions" {{ request('invoice_type') == 'other_deductions' ? 'selected' : '' }}>Other Deductions</option>
                    </select>
                    <select name="vendor_id" class="form-control" style="margin-right:10px;">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->vendor_name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-control" style="margin-right:10px;">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                    </select>
                    <button type="submit" class="btn btn-default">Filter</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Invoice No.</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Vendor</th>
                                <th>Building</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($landlordInvoicesV2 as $inv)
                            <tr>
                                <td>{{ $inv->invoice_no }}</td>
                                <td>{{ $inv->invoice_type_label }}</td>
                                <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d-m-Y') }}</td>
                                <td>{{ $inv->vendor_name }}</td>
                                <td>{{ $inv->building_name }}</td>
                                <td>{{ number_format($inv->grand_total, 3) }}</td>
                                <td>
                                    @if($inv->status == 'voided')
                                        <span class="label label-danger">Voided</span>
                                    @else
                                        <span class="label label-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('landlordInvoiceV2Print', $inv) }}" target="_blank" class="btn btn-sm btn-default">Print</a>
                                    @if($inv->status != 'voided')
                                        <a href="{{ route('landlord-invoice-v2.edit', $inv) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('landlord-invoice-v2.destroy', $inv) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Void this invoice? The invoice number will be permanently reserved.');">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <button type="submit" class="btn btn-sm btn-danger">Void</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8">No invoices found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $landlordInvoicesV2->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Manual verification in the browser**

Confirm the list page loads at Operations → Landlord Invoice v2, shows the invoice created in Task 10's verification, and that Print/Edit/Void links work — Print opens a PDF in a new tab, Void asks for confirmation and then shows the invoice with an "Voided" badge and no more Edit/Void links.

- [ ] **Step 3: Commit**

```bash
git add Modules/BackOffice/Resources/views/LandlordInvoiceV2/index.blade.php
git commit -m "feat: add Landlord Invoice v2 list view"
```

---

## Task 12: View — PDF print template

**Files:**
- Create: `Modules/BackOffice/Resources/views/LandlordInvoiceV2/pdf.blade.php`

**Interfaces:**
- Consumes: `$invoice` (a `LandlordInvoiceV2`), `$lines` (array of `['desc','qty','unit_price','amount','vat','total']`), `$totalAmount`, `$totalVat`, `$totalDue`, `$amountInWords` — all produced by Task 8's `print()`.

- [ ] **Step 1: Write the template**

Adapted from `Modules/BackOffice/Resources/views/Reports/landlord_tax_invoice_pdf.blade.php`, now populating the previously-blank Invoice No. and Customer VATIN fields from the saved invoice, and showing "Other Deductions" as the title/VAT column suppressed when `invoice_type` is `other_deductions`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='UTF-8'>
<title>{{ $invoice->invoice_type == 'tax_invoice' ? 'Tax Invoice' : 'Other Deductions Invoice' }}</title>
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

<div class="invoice-title">{{ $invoice->invoice_type == 'tax_invoice' ? 'Tax Invoice' : 'Other Deductions Invoice' }}</div>

<table class="header-fields">
  <tr>
    <td class="label">CUSTOMER NAME:</td>
    <td>{{ $invoice->vendor_name }}</td>
    <td class="label">INVOICE NO:</td>
    <td>{{ $invoice->invoice_no }}</td>
  </tr>
  <tr>
    <td class="label">BUILDING NAME</td>
    <td>{{ $invoice->building_name }}</td>
    <td class="label">INVOICE DATE:</td>
    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d.m.Y') }}</td>
  </tr>
  <tr>
    <td class="label">ADDRESS:</td>
    <td>{{ $invoice->vendor_address }}</td>
    <td class="label">PERIOD:</td>
    <td>{{ date('F', mktime(0,0,0,$invoice->period_month,1)) }} {{ $invoice->period_year }}</td>
  </tr>
  <tr>
    <td class="label">VATIN NO.</td>
    <td>{{ $invoice->vatin_no }}</td>
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

- [ ] **Step 2: Manual verification**

Re-open the invoice created in Task 10 from the list view's Print link, confirm the PDF now shows the Invoice No. and VATIN No. that the original report template left blank, and that the totals/amount-in-words line match the saved invoice.

- [ ] **Step 3: Commit**

```bash
git add Modules/BackOffice/Resources/views/LandlordInvoiceV2/pdf.blade.php
git commit -m "feat: add Landlord Invoice v2 PDF print template"
```

---

## Task 13: End-to-end verification

**Files:** none (verification only)

- [ ] **Step 1: Full Tax Invoice flow**

In the browser: Operations → Landlord Invoice v2 → Add → Tax Invoice → pick a vendor/contract/month with known billing data → confirm calculated lines → Save → confirm invoice number format `LTI<yy><00001>` → open Print → confirm PDF renders with correct number/VAT/total.

- [ ] **Step 2: Full Other Deductions flow**

Add → Other Deductions → pick a vendor/contract/month → confirm all six lines appear (Mun Tax, Elect & Water, Dewatering, AMC/A-C combined, AMC F.A.S, Sub-contractor R&M) with no VAT column applied → Save → confirm invoice number format `LOD<yy><00001>` → Print.

- [ ] **Step 3: Sequential numbering across both types**

Create a second Tax Invoice and a second Other Deductions invoice; confirm each type's number incremented independently (`LTI...00002`, `LOD...00002`) and neither series skipped a number.

- [ ] **Step 4: Void behavior**

Void one invoice from the list; confirm it shows a "Voided" badge, Edit/Void links disappear, its invoice number remains visible in the list (not deleted), and creating a new invoice of the same type still continues the sequence from where it left off (does not reuse the voided number).

- [ ] **Step 5: Existing report regression check**

Re-run the existing Landlord Tax Invoice Report (Operations → Reports → Landlord Tax Invoice Report) for the same vendor/date-range used in Task 5's verification, confirm it still produces the same output as before this plan's changes.

- [ ] **Step 6: Menu and permission check**

Confirm "Landlord Invoice v2" appears under PLM Module → Operations in the sidebar for a user with the `view_landlord_invoice_v2` permission, and log in as (or simulate) a role without that permission to confirm the menu item and routes are inaccessible (should redirect/403).
