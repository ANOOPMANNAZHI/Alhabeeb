# Landlord Invoice v2 AX Posting Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a "POST" action to Landlord Invoice v2 that pushes an active invoice into Microsoft Dynamics AX as an AP Invoice Journal via the existing SOAP helper, and records the returned journal number on the invoice.

**Architecture:** A pure `LandlordInvoiceV2AxLineBuilder` turns scalar invoice facts into the array of AX line payloads (unit-tested, no DB). A `LandlordInvoiceV2AxPoster` service gathers those facts from the models and settings, calls the two existing `Dynamics::LandlordInvoiceRegisterAx*` helpers, and marks the invoice posted. `LandlordInvoiceV2Controller::post()` is a thin wrapper that catches `AxPostingException` and flashes messages. No change to `app/Helpers/DynamicsFunctions.php` and nothing on the AX side.

**Tech Stack:** Laravel 5.x (nwidart modules), PostgreSQL, `Artisaninweb\SoapWrapper` (already wired through the `Dynamics` facade alias), Blade + jQuery, PHPUnit (bundled, `vendor/phpunit/phpunit/phpunit`).

**Spec:** `docs/superpowers/specs/2026-09-09-landlord-invoice-v2-ax-posting-design.md` (read it first; it records every business decision this plan implements).

## Global Constraints

- PHP CLI for all `artisan`, `phpunit`, and one-off scripts: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe` (system `php` is 8.4 and fatals on this app). Call it `$PHP` below.
- Run PHPUnit as: `$PHP vendor/phpunit/phpunit/phpunit <path>` (the `vendor/bin/phpunit` wrapper picks up the wrong PHP on Windows).
- AX constants come from `config/constants.php`: `AX_ENABLE_DISABLE`, `AX_URL`, `LANDLORD_INV_JOURNAL_NAME` (`PLM-INV`), `CURRENCY` (`OMR`), `DATA_AREA_ID` / `COMPANY` (`HAB`), `AX_VENDOR_COMP_ACC_PARAM` (`Comprehensive_Payable_Transaction`), `AX_VENDOR_CR` (`VENDOR`), `AX_LEDGER_DR` (`LEDGER`). Use the constants, never the literals.
- Money: `double`, `round($x, 3)`, `number_format($x, 3)` in Blade. Amounts sent to AX are `floatval()`.
- Status values on `landlord_invoice_v2.status`: `active`, `voided`, and (new) `posted`. Compare with `===` on strings.
- `Modules/BackOffice/Routes/web.php` and `Modules/BackOffice/Routes/breadcrumbs.php` carry large unrelated uncommitted edits. Any task touching them MUST use the commit-isolation technique below. Never `git add` those two files directly.
- Mirror only the permission migration into `server_upload_files/database/migrations/` (established precedent for menu/permission migrations). `server_upload_files/Modules/BackOffice/Routes/web.php` does not exist any more, so do not mirror routes.
- `acc_params.acc_params_tran_desc` is a padded `char` column. `AccountParams::where('acc_params_tran_desc', '=', AX_VENDOR_COMP_ACC_PARAM)->first()` matches correctly in Postgres (trailing spaces ignored). Do not `trim` in the query.
- Real AX calls cannot be made from a developer machine unless `AX_URL` points at a reachable Wrapper service. Every automated test in this plan avoids the network by overriding the two SOAP methods on the poster.
- Commit after every task with `Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>` and `Claude-Session: https://claude.ai/code/session_01WvEdJ7TaSWMm5HHhQJknfn` as the last two lines of the message.

### The commit-isolation technique (for `Modules/BackOffice/Routes/web.php` only in this plan)

1. Edit the live working-tree file as normal.
2. `cp Modules/BackOffice/Routes/web.php "$TEMP/web_full.php"` (save the combined content).
3. `git show HEAD:Modules/BackOffice/Routes/web.php > Modules/BackOffice/Routes/web.php` (clean base).
4. Re-apply only your route line(s) to that clean file at the same anchor.
5. `git add Modules/BackOffice/Routes/web.php && git diff --cached --stat` → must show a 1–3 line diff. Commit.
6. `cp "$TEMP/web_full.php" Modules/BackOffice/Routes/web.php` (restore combined content).
7. `git diff HEAD --stat -- Modules/BackOffice/Routes/web.php` → shows only the pre-existing unrelated diff.

---

## File Structure

| File | Responsibility |
|---|---|
| `Modules/BackOffice/Database/Migrations/2026_09_09_000001_add_ax_posting_to_landlord_invoice_v2.php` | New columns + VAT account setting row |
| `database/migrations/2026_09_09_000002_add_post_landlord_invoice_v2_permission.php` (+ mirror in `server_upload_files/database/migrations/`) | Permission `post_landlord_invoice_v2` |
| `Modules/BackOffice/Exceptions/AxPostingException.php` | Typed failure with a user-facing message |
| `Modules/BackOffice/Services/LandlordInvoiceV2AxLineBuilder.php` | Pure: facts array → AX line payload arrays |
| `Modules/BackOffice/Services/LandlordInvoiceV2AxPoster.php` | Gather facts, validate, call AX, persist |
| `Modules/BackOffice/Entities/LandlordInvoiceV2.php` | `isPosted()`, `postedBy()` relation |
| `Modules/BackOffice/Http/Controllers/LandlordInvoiceV2Controller.php` | `post()` action; posted guards on edit/update/destroy |
| `Modules/BackOffice/Routes/web.php` | `POST landlord-invoice-v2/{id}/post` |
| `Modules/BackOffice/Resources/views/LandlordInvoiceV2/{index,index_ajax,pdf}.blade.php` | Badge, button, filter, PDF field |
| `tests/Unit/LandlordInvoiceV2AxLineBuilderTest.php`, `tests/Unit/LandlordInvoiceV2AxPosterTest.php` | Automated coverage |
| `config/constants.php` | `AX_URL` overridable from `.env` |

---

### Task 1: Migration — AX columns and VAT account setting

**Files:**
- Create: `Modules/BackOffice/Database/Migrations/2026_09_09_000001_add_ax_posting_to_landlord_invoice_v2.php`

**Interfaces:**
- Produces: columns `ax_batch_id` (string 50, nullable), `ax_invoice_no` (string 30, nullable), `posted_by` (unsigned int, nullable), `posted_at` (timestamp, nullable) on `landlord_invoice_v2`; `configuration` row with `configuration_settings = 'landlord_invoice_v2_vat_account'`.

- [ ] **Step 1: Write the migration**

```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * AX posting support for Landlord Invoice v2:
 *  - ax_batch_id / ax_invoice_no / posted_by / posted_at on landlord_invoice_v2
 *  - a General Settings entry holding the ledger account used for the VAT line.
 * See docs/superpowers/specs/2026-09-09-landlord-invoice-v2-ax-posting-design.md
 */
class AddAxPostingToLandlordInvoiceV2 extends Migration
{
    public function up()
    {
        Schema::table('landlord_invoice_v2', function (Blueprint $table) {
            $table->string('ax_batch_id', 50)->nullable()->after('grand_total');
            $table->string('ax_invoice_no', 30)->nullable()->after('ax_batch_id');
            $table->unsignedInteger('posted_by')->nullable()->after('ax_invoice_no');
            $table->timestamp('posted_at')->nullable()->after('posted_by');
        });

        $exists = DB::table('configuration')
            ->where('configuration_settings', 'landlord_invoice_v2_vat_account')
            ->exists();

        if (!$exists) {
            DB::table('configuration')->insert([
                'configuration_name'     => 'settings',
                'configuration_settings' => 'landlord_invoice_v2_vat_account',
                'configuration_value'    => '',
                'created_by'             => 1,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::table('landlord_invoice_v2', function (Blueprint $table) {
            $table->dropColumn(['ax_batch_id', 'ax_invoice_no', 'posted_by', 'posted_at']);
        });

        DB::table('configuration')
            ->where('configuration_settings', 'landlord_invoice_v2_vat_account')
            ->delete();
    }
}
```

- [ ] **Step 2: Run the migration**

Run: `$PHP artisan migrate --path=Modules/BackOffice/Database/Migrations/2026_09_09_000001_add_ax_posting_to_landlord_invoice_v2.php`
Expected: `Migrated: 2026_09_09_000001_add_ax_posting_to_landlord_invoice_v2`

- [ ] **Step 3: Verify columns and setting**

Create `verify_tmp.php` in the project root:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Schema;
$cols = Schema::getColumnListing('landlord_invoice_v2');
foreach (['ax_batch_id','ax_invoice_no','posted_by','posted_at'] as $c) {
    if (!in_array($c, $cols)) { echo "MISSING $c\n"; exit(1); }
}
$row = DB::table('configuration')->where('configuration_settings','landlord_invoice_v2_vat_account')->first();
echo $row ? "OK\n" : "MISSING setting\n";
```

Run: `$PHP verify_tmp.php 2>&1 | grep -v Deprecated` — expect `OK`. Then `rm verify_tmp.php`.

- [ ] **Step 4: Commit**

```bash
git add Modules/BackOffice/Database/Migrations/2026_09_09_000001_add_ax_posting_to_landlord_invoice_v2.php
git commit -m "feat: add AX posting columns and VAT account setting for Landlord Invoice v2"
```

---

### Task 2: Migration — `post_landlord_invoice_v2` permission

**Files:**
- Create: `database/migrations/2026_09_09_000002_add_post_landlord_invoice_v2_permission.php`
- Create (mirror, identical content): `server_upload_files/database/migrations/2026_09_09_000002_add_post_landlord_invoice_v2_permission.php`

**Interfaces:**
- Produces: permission name `post_landlord_invoice_v2` (guard `web`), attached to the existing "Landlord Invoice v2" menu row, granted to roles `super_admin`, `finance_manager`, `accountant`, `backoffice_manager`.

- [ ] **Step 1: Write the migration**

```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the post_landlord_invoice_v2 permission (POST to AX button on the
 * Landlord Invoice v2 list). Attached to the same menu row as
 * view_landlord_invoice_v2 so it shows under that screen in Role settings.
 */
class AddPostLandlordInvoiceV2Permission extends Migration
{
    const PERMISSION = 'post_landlord_invoice_v2';
    const ROLES = ['super_admin', 'finance_manager', 'accountant', 'backoffice_manager'];

    public function up()
    {
        if (DB::table('permissions')->where('name', self::PERMISSION)->exists()) {
            return;
        }

        $menu = DB::table('menu')->where('route_name', 'landlord-invoice-v2.index')->first();

        $permissionId = DB::table('permissions')->insertGetId([
            'name'       => self::PERMISSION,
            'guard_name' => 'web',
            'menu_id'    => $menu ? $menu->id : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleIds = DB::table('roles')->whereIn('name', self::ROLES)->pluck('id');
        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id'       => $roleId,
            ]);
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $permission = DB::table('permissions')->where('name', self::PERMISSION)->first();
        if ($permission) {
            DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
        app('cache')->forget('spatie.permission.cache');
    }
}
```

- [ ] **Step 2: Mirror the file**

Run: `cp database/migrations/2026_09_09_000002_add_post_landlord_invoice_v2_permission.php server_upload_files/database/migrations/`

- [ ] **Step 3: Run the migration**

Run: `$PHP artisan migrate --path=database/migrations/2026_09_09_000002_add_post_landlord_invoice_v2_permission.php`
Expected: `Migrated: 2026_09_09_000002_add_post_landlord_invoice_v2_permission`

- [ ] **Step 4: Verify**

`verify_tmp.php`:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$p = DB::table('permissions')->where('name','post_landlord_invoice_v2')->first();
if (!$p) { echo "MISSING permission\n"; exit(1); }
$n = DB::table('role_has_permissions')->where('permission_id',$p->id)->count();
echo $n === 4 ? "OK\n" : "roles attached: $n (expected 4)\n";
```

Run: `$PHP verify_tmp.php 2>&1 | grep -v Deprecated` — expect `OK`. `rm verify_tmp.php`.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_09_000002_add_post_landlord_invoice_v2_permission.php server_upload_files/database/migrations/2026_09_09_000002_add_post_landlord_invoice_v2_permission.php
git commit -m "feat: add post_landlord_invoice_v2 permission"
```

---

### Task 3: Pure AX line builder with unit tests

**Files:**
- Create: `Modules/BackOffice/Services/LandlordInvoiceV2AxLineBuilder.php`
- Test: `tests/Unit/LandlordInvoiceV2AxLineBuilderTest.php`

**Interfaces:**
- Produces: `LandlordInvoiceV2AxLineBuilder::build(array $facts): array` — returns a list of AX line payload arrays in the exact key set `Dynamics::LandlordInvoiceRegisterAxLineItemPushData` reads. `$facts` keys (all required unless noted):
  - `journal_num` string, `invoice_no` string, `invoice_date` `Y-m-d` string, `invoice_type` `'tax_invoice'|'other_deductions'`
  - `description` string, `vendor_code` string, `expense_account` string, `vat_account` string|null
  - `building_code` string|null, `ax_division` string|null
  - `subtotal` float, `vat_total` float, `grand_total` float

- [ ] **Step 1: Write the failing tests**

`tests/Unit/LandlordInvoiceV2AxLineBuilderTest.php`:

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\LandlordInvoiceV2AxLineBuilder;

class LandlordInvoiceV2AxLineBuilderTest extends TestCase
{
    private function facts(array $overrides = [])
    {
        return array_merge([
            'journal_num'     => 'J000123',
            'invoice_no'      => 'LTI2600007',
            'invoice_date'    => '2026-09-09',
            'invoice_type'    => 'tax_invoice',
            'description'     => 'Tax Invoice LTI2600007 ACME 09/2026',
            'vendor_code'     => 'V00042',
            'expense_account' => '12306',
            'vat_account'     => '21500',
            'building_code'   => 'B017',
            'ax_division'     => '02',
            'subtotal'        => 100.000,
            'vat_total'       => 5.000,
            'grand_total'     => 105.000,
        ], $overrides);
    }

    public function test_tax_invoice_produces_vendor_credit_expense_debit_and_vat_debit()
    {
        $lines = LandlordInvoiceV2AxLineBuilder::build($this->facts());

        $this->assertCount(3, $lines);

        $this->assertSame('VENDOR', $lines[0]['accountType']);
        $this->assertSame('V00042', $lines[0]['vendAccount']);
        $this->assertSame(0.0, $lines[0]['AmountDebit']);
        $this->assertSame(105.0, $lines[0]['AmountCredit']);

        $this->assertSame('LEDGER', $lines[1]['accountType']);
        $this->assertSame('12306', $lines[1]['vendAccount']);
        $this->assertSame(100.0, $lines[1]['AmountDebit']);
        $this->assertSame(0.0, $lines[1]['AmountCredit']);

        $this->assertSame('LEDGER', $lines[2]['accountType']);
        $this->assertSame('21500', $lines[2]['vendAccount']);
        $this->assertSame(5.0, $lines[2]['AmountDebit']);
        $this->assertSame(0.0, $lines[2]['AmountCredit']);
    }

    public function test_other_deductions_reverses_every_debit_and_credit()
    {
        $lines = LandlordInvoiceV2AxLineBuilder::build($this->facts(['invoice_type' => 'other_deductions']));

        $this->assertSame(105.0, $lines[0]['AmountDebit']);
        $this->assertSame(0.0, $lines[0]['AmountCredit']);
        $this->assertSame(0.0, $lines[1]['AmountDebit']);
        $this->assertSame(100.0, $lines[1]['AmountCredit']);
        $this->assertSame(0.0, $lines[2]['AmountDebit']);
        $this->assertSame(5.0, $lines[2]['AmountCredit']);
    }

    public function test_zero_vat_omits_the_vat_line()
    {
        $lines = LandlordInvoiceV2AxLineBuilder::build($this->facts([
            'vat_total' => 0.0, 'grand_total' => 100.0, 'vat_account' => null,
        ]));

        $this->assertCount(2, $lines);
    }

    public function test_common_fields_and_dimensions_match_v1_layout()
    {
        $line = LandlordInvoiceV2AxLineBuilder::build($this->facts())[0];

        $this->assertSame('J000123', $line['JournalNum']);
        $this->assertSame(LANDLORD_INV_JOURNAL_NAME, $line['JournalName']);
        $this->assertSame('2026-09-09', $line['PaymentDate']);
        $this->assertSame(CURRENCY, $line['currency']);
        $this->assertSame('LTI2600007', $line['voucher']);
        $this->assertSame('LTI2600007', $line['Invoice']);
        $this->assertSame('LTI2600007', $line['documentNo']);
        $this->assertSame('Tax Invoice LTI2600007 ACME 09/2026', $line['Description']);
        $this->assertSame('Tax Invoice LTI2600007 ACME 09/2026', $line['Remarks']);
        $this->assertSame('', $line['paymentMethod']);
        $this->assertSame('', $line['checkBookid']);
        $this->assertSame('Building', $line['dimension1']);
        $this->assertSame('B017', $line['dimension1value']);
        $this->assertSame('Division', $line['dimension2']);
        $this->assertSame('02', $line['dimension2value']);
        $this->assertSame('Employee', $line['dimension3']);
        $this->assertSame('00000', $line['dimension3value']);
        $this->assertSame('Location', $line['dimension4']);
        $this->assertSame('00', $line['dimension4value']);
        $this->assertSame('Projects', $line['dimension5']);
        $this->assertSame('00', $line['dimension5value']);
        $this->assertSame(DATA_AREA_ID, $line['DataAreaId']);
        $this->assertSame(COMPANY, $line['company']);
    }

    public function test_missing_building_code_and_division_fall_back_to_v1_defaults()
    {
        $line = LandlordInvoiceV2AxLineBuilder::build($this->facts([
            'building_code' => null, 'ax_division' => null,
        ]))[0];

        $this->assertSame('000', $line['dimension1value']);
        $this->assertSame('02', $line['dimension2value']);
    }
}
```

Note: the constants (`LANDLORD_INV_JOURNAL_NAME` etc.) are defined in `config/constants.php`, which Laravel loads during bootstrap. A plain PHPUnit `TestCase` does not boot Laravel, so add this to the top of the test file, after the `use` lines:

```php
if (!defined('LANDLORD_INV_JOURNAL_NAME')) {
    require_once __DIR__ . '/../../config/constants.php';
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit/LandlordInvoiceV2AxLineBuilderTest.php`
Expected: errors with `Class 'Modules\BackOffice\Services\LandlordInvoiceV2AxLineBuilder' not found`.

- [ ] **Step 3: Implement the builder**

`Modules/BackOffice/Services/LandlordInvoiceV2AxLineBuilder.php`:

```php
<?php

namespace Modules\BackOffice\Services;

/**
 * Turns plain Landlord Invoice v2 facts into the AX AP Invoice Journal line
 * payloads consumed by Dynamics::LandlordInvoiceRegisterAxLineItemPushData().
 * Pure function: no models, no DB, no SOAP. See the AX posting spec.
 */
class LandlordInvoiceV2AxLineBuilder
{
    const TYPE_OTHER_DEDUCTIONS = 'other_deductions';

    /**
     * @param array $facts see LandlordInvoiceV2AxPoster::facts() for the key list
     * @return array[] ordered list of line payloads
     */
    public static function build(array $facts)
    {
        $reverse = ($facts['invoice_type'] === self::TYPE_OTHER_DEDUCTIONS);

        $lines = [];

        // 1. Vendor side for the grand total (credit on a tax invoice)
        $lines[] = self::line($facts, AX_VENDOR_CR, $facts['vendor_code'], 0.0, (float) $facts['grand_total'], $reverse);

        // 2. Expense ledger for the subtotal (debit on a tax invoice)
        $lines[] = self::line($facts, AX_LEDGER_DR, $facts['expense_account'], (float) $facts['subtotal'], 0.0, $reverse);

        // 3. VAT ledger, only when there is VAT
        if ((float) $facts['vat_total'] > 0) {
            $lines[] = self::line($facts, AX_LEDGER_DR, $facts['vat_account'], (float) $facts['vat_total'], 0.0, $reverse);
        }

        return $lines;
    }

    private static function line(array $f, $accountType, $account, $debit, $credit, $reverse)
    {
        if ($reverse) {
            list($debit, $credit) = [$credit, $debit];
        }

        return [
            'JournalName'     => LANDLORD_INV_JOURNAL_NAME,
            'JournalNum'      => $f['journal_num'],
            'PaymentDate'     => $f['invoice_date'],
            'vendAccount'     => (string) $account,
            'currency'        => CURRENCY,
            'accountType'     => $accountType,
            'paymentMethod'   => '',
            'Description'     => $f['description'],
            'documentNo'      => $f['invoice_no'],
            'checkBookid'     => '',
            'AmountCredit'    => floatval($credit),
            'AmountDebit'     => floatval($debit),
            'voucher'         => $f['invoice_no'],
            'Invoice'         => $f['invoice_no'],
            'Remarks'         => $f['description'],
            'dimension1'      => 'Building',
            'dimension1value' => !empty($f['building_code']) ? $f['building_code'] : '000',
            'dimension2'      => 'Division',
            'dimension2value' => !empty($f['ax_division']) ? $f['ax_division'] : AX_DIVISION_PLMS,
            'dimension3'      => 'Employee',
            'dimension3value' => '00000',
            'dimension4'      => 'Location',
            'dimension4value' => '00',
            'dimension5'      => 'Projects',
            'dimension5value' => '00',
            'DataAreaId'      => DATA_AREA_ID,
            'company'         => COMPANY,
        ];
    }
}
```

- [ ] **Step 4: Run the tests**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit/LandlordInvoiceV2AxLineBuilderTest.php`
Expected: `OK (5 tests, ...)`

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/LandlordInvoiceV2AxLineBuilder.php tests/Unit/LandlordInvoiceV2AxLineBuilderTest.php
git commit -m "feat: pure AX line builder for Landlord Invoice v2"
```

---

### Task 4: Poster service, exception, and model helpers

**Files:**
- Create: `Modules/BackOffice/Exceptions/AxPostingException.php`
- Create: `Modules/BackOffice/Services/LandlordInvoiceV2AxPoster.php`
- Modify: `Modules/BackOffice/Entities/LandlordInvoiceV2.php`
- Test: `tests/Unit/LandlordInvoiceV2AxPosterTest.php`

**Interfaces:**
- Consumes: `LandlordInvoiceV2AxLineBuilder::build(array)` from Task 3.
- Produces:
  - `AxPostingException extends \RuntimeException` (message is user-facing).
  - `LandlordInvoiceV2AxPoster::post(LandlordInvoiceV2 $invoice, int $userId): string` — returns the AX journal number; throws `AxPostingException`.
  - Protected seams for tests and future change: `openJournal(): string`, `pushLine(array $line): string`, `expenseAccount(): ?string`, `vatAccount(): ?string`, `axEnabled(): bool`, `persist(LandlordInvoiceV2 $invoice, string $journalNum, int $userId): void`.
  - `LandlordInvoiceV2::isPosted(): bool`, `LandlordInvoiceV2::postedBy()` belongsTo `App\User`.

- [ ] **Step 1: Write the exception**

`Modules/BackOffice/Exceptions/AxPostingException.php`:

```php
<?php

namespace Modules\BackOffice\Exceptions;

/**
 * Raised by LandlordInvoiceV2AxPoster when an invoice cannot be posted to AX.
 * The message is safe to flash to the user.
 */
class AxPostingException extends \RuntimeException
{
}
```

- [ ] **Step 2: Add model helpers**

In `Modules/BackOffice/Entities/LandlordInvoiceV2.php`, add after the `lines()` method:

```php
    public function postedBy()
    {
        return $this->belongsTo(\App\User::class, 'posted_by');
    }

    public function isPosted()
    {
        return $this->status === 'posted';
    }
```

- [ ] **Step 3: Write the failing poster tests**

`tests/Unit/LandlordInvoiceV2AxPosterTest.php` (boots Laravel via `Tests\TestCase` so Eloquent models can be instantiated; no query is executed because `persist()` is overridden):

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use Modules\Masters\Entities\Vendor;
use Modules\Masters\Entities\Building;
use Modules\Sales\Entities\LandlordContract;
use Modules\BackOffice\Entities\LandlordInvoiceV2;
use Modules\BackOffice\Exceptions\AxPostingException;
use Modules\BackOffice\Services\LandlordInvoiceV2AxPoster;

/** Test double: records SOAP calls instead of making them; skips the DB write. */
class RecordingPoster extends LandlordInvoiceV2AxPoster
{
    public $journal = 'J000777';
    public $lineResult = 'OK';
    public $enabled = true;
    public $expense = '12306';
    public $vat = '21500';
    public $pushed = [];
    public $persisted = null;

    protected function axEnabled() { return $this->enabled; }
    protected function expenseAccount() { return $this->expense; }
    protected function vatAccount() { return $this->vat; }
    protected function openJournal() { return $this->journal; }
    protected function pushLine(array $line) { $this->pushed[] = $line; return $this->lineResult; }
    protected function persist(LandlordInvoiceV2 $invoice, $journalNum, $userId)
    {
        $this->persisted = compact('journalNum', 'userId');
    }
}

class LandlordInvoiceV2AxPosterTest extends TestCase
{
    private function invoice(array $overrides = [])
    {
        $invoice = new LandlordInvoiceV2(array_merge([
            'invoice_type' => 'tax_invoice',
            'invoice_no'   => 'LTI2600007',
            'invoice_date' => '2026-09-09',
            'period_month' => 9,
            'period_year'  => 2026,
            'vendor_name'  => 'ACME',
            'status'       => 'active',
            'subtotal'     => 100.0,
            'vat_total'    => 5.0,
            'grand_total'  => 105.0,
        ], $overrides));

        $building = new Building(['building_code' => 'B017', 'ax_division' => '02']);
        $contract = new LandlordContract([]);
        $contract->setRelation('buildingInfo', $building);

        $invoice->setRelation('vendor', new Vendor(['vendor_code' => 'V00042']));
        $invoice->setRelation('landlordContract', $contract);

        return $invoice;
    }

    public function test_posts_three_lines_and_persists_journal_number()
    {
        $poster = new RecordingPoster;

        $result = $poster->post($this->invoice(), 7);

        $this->assertSame('J000777', $result);
        $this->assertCount(3, $poster->pushed);
        $this->assertSame('J000777', $poster->pushed[0]['JournalNum']);
        $this->assertSame('V00042', $poster->pushed[0]['vendAccount']);
        $this->assertSame('Tax Invoice LTI2600007 ACME 09/2026', $poster->pushed[0]['Description']);
        $this->assertSame(['journalNum' => 'J000777', 'userId' => 7], $poster->persisted);
    }

    public function test_refuses_when_ax_disabled()
    {
        $poster = new RecordingPoster;
        $poster->enabled = false;

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('AX integration is disabled');
        $poster->post($this->invoice(), 7);
        $this->assertSame([], $poster->pushed);
    }

    public function test_refuses_non_active_invoice()
    {
        $poster = new RecordingPoster;

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('Only active invoices can be posted');
        $poster->post($this->invoice(['status' => 'posted']), 7);
    }

    public function test_refuses_when_vendor_has_no_code()
    {
        $poster = new RecordingPoster;
        $invoice = $this->invoice();
        $invoice->setRelation('vendor', new Vendor(['vendor_code' => '']));

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('Vendor has no AX vendor code');
        $poster->post($invoice, 7);
    }

    public function test_refuses_when_expense_account_missing()
    {
        $poster = new RecordingPoster;
        $poster->expense = null;

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('Comprehensive_Payable_Transaction');
        $poster->post($this->invoice(), 7);
    }

    public function test_refuses_when_vat_account_missing_and_vat_present()
    {
        $poster = new RecordingPoster;
        $poster->vat = '';

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('landlord_invoice_v2_vat_account');
        $poster->post($this->invoice(), 7);
    }

    public function test_allows_blank_vat_account_when_vat_is_zero()
    {
        $poster = new RecordingPoster;
        $poster->vat = '';

        $poster->post($this->invoice(['vat_total' => 0.0, 'grand_total' => 100.0]), 7);

        $this->assertCount(2, $poster->pushed);
    }

    public function test_header_error_aborts_before_any_line()
    {
        $poster = new RecordingPoster;
        $poster->journal = 'Error';

        try {
            $poster->post($this->invoice(), 7);
            $this->fail('expected exception');
        } catch (AxPostingException $e) {
            $this->assertSame([], $poster->pushed);
            $this->assertNull($poster->persisted);
        }
    }

    public function test_line_error_aborts_without_persisting()
    {
        $poster = new RecordingPoster;
        $poster->lineResult = 'Error';

        try {
            $poster->post($this->invoice(), 7);
            $this->fail('expected exception');
        } catch (AxPostingException $e) {
            $this->assertCount(1, $poster->pushed);
            $this->assertNull($poster->persisted);
        }
    }
}
```

- [ ] **Step 4: Run to verify it fails**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit/LandlordInvoiceV2AxPosterTest.php`
Expected: errors with `Class 'Modules\BackOffice\Services\LandlordInvoiceV2AxPoster' not found`.

- [ ] **Step 5: Implement the poster**

`Modules/BackOffice/Services/LandlordInvoiceV2AxPoster.php`:

```php
<?php

namespace Modules\BackOffice\Services;

use App\Setting;
use Carbon\Carbon;
use Modules\BackOffice\Entities\AccountParams;
use Modules\BackOffice\Entities\LandlordInvoiceV2;
use Modules\BackOffice\Exceptions\AxPostingException;

/**
 * Posts one Landlord Invoice v2 to Microsoft Dynamics AX as an AP Invoice
 * Journal, using the same two SOAP helpers as v1 Landlord Invoice
 * (Dynamics::LandlordInvoiceRegisterAxHeaderPushData / ...LineItemPushData).
 *
 * The protected methods are deliberate seams: tests override them to avoid
 * the network and the database.
 */
class LandlordInvoiceV2AxPoster
{
    const VAT_SETTING_KEY = 'landlord_invoice_v2_vat_account';
    const SOAP_HEADER_SERVICE = 'AXLandlordInvoiceV2Header';
    const SOAP_LINE_SERVICE = 'AXLandlordInvoiceV2Line';

    /**
     * @return string AX journal number
     * @throws AxPostingException
     */
    public function post(LandlordInvoiceV2 $invoice, $userId)
    {
        $facts = $this->facts($invoice);            // validates, throws on problems

        $journalNum = $this->openJournal();
        if ($journalNum === 'Error' || $journalNum === '' || $journalNum === null) {
            throw new AxPostingException('Microsoft Dynamics API Service Error while creating the journal header.');
        }
        $facts['journal_num'] = $journalNum;

        foreach (LandlordInvoiceV2AxLineBuilder::build($facts) as $index => $line) {
            if ($this->pushLine($line) === 'Error') {
                throw new AxPostingException(sprintf(
                    'Microsoft Dynamics API Service Error on line %d (journal %s). Invoice left unposted.',
                    $index + 1, $journalNum
                ));
            }
        }

        $this->persist($invoice, $journalNum, $userId);

        return $journalNum;
    }

    /**
     * Gather and validate every scalar the line builder needs.
     * @throws AxPostingException
     */
    protected function facts(LandlordInvoiceV2 $invoice)
    {
        if (!$this->axEnabled()) {
            throw new AxPostingException('AX integration is disabled (AX_ENABLE_DISABLE).');
        }
        if ($invoice->status !== 'active') {
            throw new AxPostingException('Only active invoices can be posted to AX.');
        }

        $vendorCode = trim((string) optional($invoice->vendor)->vendor_code);
        if ($vendorCode === '') {
            throw new AxPostingException('Vendor has no AX vendor code.');
        }

        $expense = trim((string) $this->expenseAccount());
        if ($expense === '') {
            throw new AxPostingException('Debit account is not configured in Account Params (Comprehensive_Payable_Transaction).');
        }

        $vat = trim((string) $this->vatAccount());
        if ((float) $invoice->vat_total > 0 && $vat === '') {
            throw new AxPostingException('VAT ledger account is not configured (General Settings: landlord_invoice_v2_vat_account).');
        }

        $building = optional($invoice->landlordContract)->buildingInfo;

        return [
            'journal_num'     => '',
            'invoice_no'      => $invoice->invoice_no,
            'invoice_date'    => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
            'invoice_type'    => $invoice->invoice_type,
            'description'     => sprintf('%s %s %s %02d/%d',
                                    $invoice->invoice_type_label,
                                    $invoice->invoice_no,
                                    $invoice->vendor_name,
                                    (int) $invoice->period_month,
                                    (int) $invoice->period_year),
            'vendor_code'     => $vendorCode,
            'expense_account' => $expense,
            'vat_account'     => $vat,
            'building_code'   => $building ? $building->building_code : null,
            'ax_division'     => $building ? $building->ax_division : null,
            'subtotal'        => (float) $invoice->subtotal,
            'vat_total'       => (float) $invoice->vat_total,
            'grand_total'     => (float) $invoice->grand_total,
        ];
    }

    // ---- seams -----------------------------------------------------------

    protected function axEnabled()
    {
        return defined('AX_ENABLE_DISABLE') && AX_ENABLE_DISABLE == 1;
    }

    protected function expenseAccount()
    {
        $params = AccountParams::where('acc_params_tran_desc', '=', AX_VENDOR_COMP_ACC_PARAM)->first();
        return $params ? $params->acc_params_dr_acc : null;
    }

    protected function vatAccount()
    {
        $setting = Setting::where('configuration_settings', self::VAT_SETTING_KEY)->first();
        return $setting ? $setting->configuration_value : null;
    }

    protected function openJournal()
    {
        return \Dynamics::LandlordInvoiceRegisterAxHeaderPushData(self::SOAP_HEADER_SERVICE);
    }

    protected function pushLine(array $line)
    {
        return \Dynamics::LandlordInvoiceRegisterAxLineItemPushData(self::SOAP_LINE_SERVICE, [$line]);
    }

    protected function persist(LandlordInvoiceV2 $invoice, $journalNum, $userId)
    {
        $invoice->update([
            'status'        => 'posted',
            'ax_batch_id'   => $journalNum,
            'ax_invoice_no' => $invoice->invoice_no,
            'posted_by'     => $userId,
            'posted_at'     => now(),
        ]);
    }
}
```

- [ ] **Step 6: Run the tests**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit/LandlordInvoiceV2AxPosterTest.php`
Expected: `OK (9 tests, ...)`. If `Tests\TestCase` bootstrap fails because `.env` DB is unreachable, that indicates a local env problem, not a code problem: models are only instantiated, never queried.

- [ ] **Step 7: Commit**

```bash
git add Modules/BackOffice/Exceptions/AxPostingException.php Modules/BackOffice/Services/LandlordInvoiceV2AxPoster.php Modules/BackOffice/Entities/LandlordInvoiceV2.php tests/Unit/LandlordInvoiceV2AxPosterTest.php
git commit -m "feat: LandlordInvoiceV2AxPoster service with validation and AX line push"
```

---

### Task 5: Controller `post()` action, posted guards, and route

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/LandlordInvoiceV2Controller.php` (imports at top; `edit` ~line 147; `update` ~line 154; `destroy` ~line 187; new method after `destroy`)
- Modify: `Modules/BackOffice/Routes/web.php` (after the `landlordInvoiceV2Print` route, ~line 749) — **commit-isolation technique required**

**Interfaces:**
- Consumes: `LandlordInvoiceV2AxPoster::post()` and `AxPostingException` from Task 4.
- Produces: route name `landlordInvoiceV2Post` (`POST landlord-invoice-v2/{landlordInvoiceV2}/post`).

- [ ] **Step 1: Add imports**

At the top of the controller, after `use Modules\BackOffice\Entities\LandlordInvoiceV2;` add:

```php
use Modules\BackOffice\Exceptions\AxPostingException;
use Modules\BackOffice\Services\LandlordInvoiceV2AxPoster;
```

- [ ] **Step 2: Guard edit/update/destroy against posted invoices**

Replace, in `edit()` and `update()`:

```php
        abort_if($landlordInvoiceV2->status === 'voided', 403, 'Voided invoices cannot be edited.');
```

with:

```php
        abort_if($landlordInvoiceV2->status === 'voided', 403, 'Voided invoices cannot be edited.');
        abort_if($landlordInvoiceV2->isPosted(), 403, 'Invoices posted to AX cannot be edited.');
```

And in `destroy()`, after the existing `abort_if` for voided, add:

```php
        abort_if($landlordInvoiceV2->isPosted(), 403, 'Invoices posted to AX cannot be voided.');
```

- [ ] **Step 3: Add the `post()` action**

After `destroy()`:

```php
    /**
     * Push an active invoice to Microsoft Dynamics AX (AP Invoice Journal).
     * Route: POST landlord-invoice-v2/{landlordInvoiceV2}/post
     */
    public function post(LandlordInvoiceV2 $landlordInvoiceV2, LandlordInvoiceV2AxPoster $poster)
    {
        abort_unless(\Auth::user()->can('post_landlord_invoice_v2'), 403);

        $landlordInvoiceV2->load(['vendor', 'landlordContract.buildingInfo']);

        try {
            $journalNum = $poster->post($landlordInvoiceV2, \Auth::user()->id);
        } catch (AxPostingException $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->route('landlord-invoice-v2.index');
        }

        session()->flash('success', 'Landlord Invoice ' . $landlordInvoiceV2->invoice_no . ' posted to AX. Journal: ' . $journalNum);
        return redirect()->route('landlord-invoice-v2.index');
    }
```

- [ ] **Step 4: Add the route (live file first)**

In `Modules/BackOffice/Routes/web.php`, directly after:

```php
	Route::get('landlord-invoice-v2/{landlordInvoiceV2}/print', 'LandlordInvoiceV2Controller@print')->name('landlordInvoiceV2Print');
```

add:

```php
	Route::post('landlord-invoice-v2/{landlordInvoiceV2}/post', 'LandlordInvoiceV2Controller@post')->name('landlordInvoiceV2Post');
```

- [ ] **Step 5: Verify the route resolves**

Run: `$PHP artisan route:list --name=landlordInvoiceV2Post 2>&1 | grep -v Deprecated`
Expected: one row, method `POST`, URI `landlord-invoice-v2/{landlordInvoiceV2}/post`.

- [ ] **Step 6: Verify the guards with a quick script**

`verify_tmp.php`:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$inv = new Modules\BackOffice\Entities\LandlordInvoiceV2(['status' => 'posted']);
echo $inv->isPosted() ? "OK\n" : "isPosted broken\n";
```

Run: `$PHP verify_tmp.php 2>&1 | grep -v Deprecated` — expect `OK`. `rm verify_tmp.php`.

- [ ] **Step 7: Commit the controller, then the route with isolation**

```bash
git add Modules/BackOffice/Http/Controllers/LandlordInvoiceV2Controller.php
git commit -m "feat: post Landlord Invoice v2 to AX action and posted-state guards"
```

Then apply the commit-isolation technique from Global Constraints to `Modules/BackOffice/Routes/web.php` for the single `landlordInvoiceV2Post` line and commit:

```bash
git commit -m "feat: route for posting Landlord Invoice v2 to AX"
```

Confirm afterwards: `git diff HEAD --stat -- Modules/BackOffice/Routes/web.php` shows only the pre-existing unrelated diff, and `grep -n landlordInvoiceV2Post Modules/BackOffice/Routes/web.php` still finds the line in the working tree.

---

### Task 6: List view — POST button, Posted badge, status filter

**Files:**
- Modify: `Modules/BackOffice/Resources/views/LandlordInvoiceV2/index_ajax.blade.php`
- Modify: `Modules/BackOffice/Resources/views/LandlordInvoiceV2/index.blade.php` (status `<select>`, ~line 65)

**Interfaces:**
- Consumes: route `landlordInvoiceV2Post` (Task 5), permission `post_landlord_invoice_v2` (Task 2), `$inv->isPosted()` (Task 4).

- [ ] **Step 1: Update the status badge in `index_ajax.blade.php`**

Replace:

```blade
        @if($inv->status == 'voided')
            <span class="btn-circle btn-danger btn-sm m-b-10"><b>Voided</b></span>
        @else
            <span class="btn-circle btn-success btn-sm m-b-10"><b>Active</b></span>
        @endif
```

with:

```blade
        @if($inv->status == 'voided')
            <span class="btn-circle btn-danger btn-sm m-b-10"><b>Voided</b></span>
        @elseif($inv->isPosted())
            <span class="btn-circle btn-info btn-sm m-b-10" title="AX Journal {{ $inv->ax_batch_id }}"><b>Posted</b></span>
        @else
            <span class="btn-circle btn-success btn-sm m-b-10"><b>Active</b></span>
        @endif
```

- [ ] **Step 2: Update the action cell**

Replace the `@if($inv->status != 'voided') ... @endif` block (Edit link + Void form) with:

```blade
        @if($inv->status == 'active')
            <a title="Edit" href="{{ route('landlord-invoice-v2.edit', $inv) }}" class="btn btn-tbl-edit btn-xs">
                <i class="fa fa-pencil"></i>
            </a>
            <form action="{{ route('landlord-invoice-v2.destroy', $inv) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Void this invoice? The invoice number will be permanently reserved.');">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <button type="submit" title="Void" class="btn btn-tbl-delete btn-xs">
                    <i class="fa fa-ban"></i>
                </button>
            </form>
            @if(auth()->user()->can('post_landlord_invoice_v2'))
            <form action="{{ route('landlordInvoiceV2Post', $inv) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Post invoice {{ $inv->invoice_no }} to Microsoft Dynamics AX? This cannot be undone.');">
                {{ csrf_field() }}
                <button type="submit" title="Post to AX" class="btn btn-tbl-edit btn-xs" style="background:#0288d1;color:#fff;">
                    <i class="fa fa-upload"></i>
                </button>
            </form>
            @endif
        @endif
```

- [ ] **Step 3: Add "Posted" to the status filter in `index.blade.php`**

Replace:

```blade
                                            <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
```

with:

```blade
                                            <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                                            <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
```

- [ ] **Step 4: Verify the Blade compiles**

Run: `$PHP artisan view:clear 2>&1 | grep -v Deprecated` then create `verify_tmp.php`:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = app('blade.compiler');
foreach (['index', 'index_ajax'] as $v) {
    $c->compile(base_path("Modules/BackOffice/Resources/views/LandlordInvoiceV2/$v.blade.php"));
}
echo "OK\n";
```

Run: `$PHP verify_tmp.php 2>&1 | grep -v Deprecated` — expect `OK`. `rm verify_tmp.php`.

- [ ] **Step 5: Manually verify in the browser**

Log in as `super_admin` → Operations → Landlord Invoice v2. Expect: Active rows show Edit, Void, and a blue upload button; the status filter lists Posted. Do not click POST unless `AX_URL` points at a reachable Wrapper service.

- [ ] **Step 6: Commit**

```bash
git add Modules/BackOffice/Resources/views/LandlordInvoiceV2/index_ajax.blade.php Modules/BackOffice/Resources/views/LandlordInvoiceV2/index.blade.php
git commit -m "feat: POST to AX button, Posted badge and filter on Landlord Invoice v2 list"
```

---

### Task 7: PDF — show AX batch when posted

**Files:**
- Modify: `Modules/BackOffice/Resources/views/LandlordInvoiceV2/pdf.blade.php` (header-fields table, ~line 59)

- [ ] **Step 1: Add the AX row**

Inside `<table class="header-fields">`, after the `</tr>` that closes the "ADDRESS / PERIOD" row, add:

```blade
  @if($invoice->status === 'posted')
  <tr>
    <td class="label">AX BATCH:</td>
    <td>{{ $invoice->ax_batch_id }}</td>
    <td class="label">POSTED ON:</td>
    <td>{{ $invoice->posted_at ? \Carbon\Carbon::parse($invoice->posted_at)->format('d.m.Y') : '' }}</td>
  </tr>
  @endif
```

- [ ] **Step 2: Verify the Blade compiles**

Same `verify_tmp.php` shape as Task 6 Step 4 with `foreach (['pdf'] as $v)`. Expect `OK`. `rm verify_tmp.php`.

- [ ] **Step 3: Commit**

```bash
git add Modules/BackOffice/Resources/views/LandlordInvoiceV2/pdf.blade.php
git commit -m "feat: show AX batch and posted date on Landlord Invoice v2 PDF"
```

---

### Task 8: Make `AX_URL` overridable from `.env` for local testing

**Files:**
- Modify: `config/constants.php` (line with `define("AX_URL", ...)`)
- Modify: `.env.example` (append one line)

**Interfaces:**
- Produces: `.env` key `AX_URL`; when absent, behaviour is unchanged.

- [ ] **Step 1: Change the define**

Replace:

```php
define("AX_URL", "http://alh-ax01:8018/Wrapper.asmx?wsdl");
```

with:

```php
// Override per environment via AX_URL in .env (e.g. a local AX PC for testing).
define("AX_URL", env('AX_URL', "http://alh-ax01:8018/Wrapper.asmx?wsdl"));
```

- [ ] **Step 2: Document the key**

Append to `.env.example`:

```
AX_URL=http://alh-ax01:8018/Wrapper.asmx?wsdl
```

- [ ] **Step 3: Verify default and override**

`verify_tmp.php`:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo AX_URL . PHP_EOL;
```

Run once with no `AX_URL` in `.env` → expect the `alh-ax01` URL. Add `AX_URL=http://127.0.0.1:8018/Wrapper.asmx?wsdl` to `.env`, run `$PHP artisan config:clear`, run again → expect the local URL. Remove the `.env` line afterwards unless a local AX PC is actually being tested against. `rm verify_tmp.php`.

- [ ] **Step 4: Commit**

```bash
git add config/constants.php .env.example
git commit -m "chore: allow AX_URL override from .env"
```

---

### Task 9: Docs — update v2 spec non-goals, run full test set

**Files:**
- Modify: `docs/superpowers/specs/2026-09-07-landlord-invoice-v2-design.md` (Out of Scope list, ~line 126)

- [ ] **Step 1: Update the original spec**

Replace:

```
- Any AX/ERP integration or dimension mapping (v1-only concern).
```

with:

```
- ~~Any AX/ERP integration or dimension mapping (v1-only concern).~~ Superseded on 2026-09-09 by `2026-09-09-landlord-invoice-v2-ax-posting-design.md`, which adds AX posting to v2.
```

- [ ] **Step 2: Run both unit test files together**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit`
Expected: `OK (15 tests, ...)` (5 builder + 9 poster + the pre-existing ExampleTest).

- [ ] **Step 3: Commit**

```bash
git add docs/superpowers/specs/2026-09-07-landlord-invoice-v2-design.md docs/superpowers/specs/2026-09-09-landlord-invoice-v2-ax-posting-design.md docs/superpowers/plans/2026-09-09-landlord-invoice-v2-ax-posting.md
git commit -m "docs: Landlord Invoice v2 AX posting spec and plan"
```

---

## End-to-end check (requires a reachable AX Wrapper)

1. Set `AX_URL` in `.env` to the local AX PC (Task 8), `config:clear`.
2. Set General Settings → `landlord_invoice_v2_vat_account` to the VAT input ledger account agreed with finance.
3. Confirm the test vendor's `vendor_code` exists in AX (`IsVendorExist`) and the building has `ax_division`.
4. Create a small Tax Invoice in v2, click the upload button, confirm.
5. Expect a success flash with the journal number; the row turns "Posted"; Edit and Void disappear; the PDF shows the AX batch.
6. In AX, open Accounts payable → Journals → Invoices → `PLM-INV`, find the journal, confirm three balanced lines.
7. Repeat with an Other Deductions invoice and confirm debits and credits are reversed.
