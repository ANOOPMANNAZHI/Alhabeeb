# Termination Dues Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** When a tenant contract is terminated with money still owed, record the outstanding as a tracked receivable ("termination dues") split by owner team, and have every real settlement (rent receipt, general receipt, deposit-refund deduction) reduce it automatically — so the team stops creating receipts for money that has not arrived.

**Architecture:** Four new tables (`termination_dues`, `_lines`, `_allocations`, `_followups`). Owed lines are snapshotted once, at the final termination stage (505), by a pure builder from the inspection checklist and termination amounts. Settlements are **computed live** (never stored) by a pure settlement engine fed by a DB "sources" gatherer; only manual decisions (assign an unclassified receipt to a line, waive) are stored. Hooks in the receipt / PDC / deposit-refund controllers only *refresh* the stored status/balance columns used for list filtering. A guard refuses rent receipts on terminated contracts beyond the open rent balance.

**Tech Stack:** Laravel 5.7 (nwidart Modules, `Modules\BackOffice`), PostgreSQL, Blade + jQuery, spatie/laravel-permission, PHPUnit 7 (pure unit tests, no app boot). Server runs **PHP < 7.4**: no arrow functions, no typed properties, no nullsafe, no `match`, no `str_contains`.

**Spec:** `docs/superpowers/specs/2026-09-17-termination-dues-design.md`

## Global Constraints

- PHP CLI for everything local: `$PHP=/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe` (system `php` is 8.4 and fatals on this app). Run tests as `$PHP vendor/phpunit/phpunit/phpunit <path>`.
- Local DB is a copy of live: pgsql `plms_new` on 127.0.0.1:5432, user `postgres` / `123456` (from `.env`). Migrations run with `$PHP artisan migrate --path=Modules/BackOffice/Database/Migrations/<file>` (module migrations are not auto-discovered).
- Code must run on PHP 7.1–7.3 syntax. `??` and `list()` are fine; `fn() =>`, `?->`, `match`, typed class properties, `static fn`, trailing comma in parameter lists are not.
- Money: `decimal(12,3)`, compare with `abs($a - $b) < 0.005`. Format for display with the global helper `numberFormat($amount)` (`config/function.php:253`).
- Do **not** touch anything under `server_upload_files/` (memory rule: only on explicit request).
- Do **not** mirror or edit `BackOfficeReportController.php`.
- New views extend `layouts.plms-app`; follow the scoped-token CSS approach in `Modules/BackOffice/Resources/views/LandlordInvoiceV2/index.blade.php` (a `.tdue-*` prefix here). 8px spacing grid, tables with right-aligned numeric columns, one primary action per view, semantic `button`/`a`.
- Existing status codes to reuse: `receipts_generation_type` 0 rent / 1 general / 2 deposit; `receipts_generation_status` 2 = cancelled, 3 = posted; `receipts_generation_approval_status` 3 = approved, 6 = posted; `tenant_contracts.tenant_renewal_termination_status` 8 = terminated; termination stages 503 = inspection (checklist saved), 505 = final.
- Commit after every task with a conventional message; end each commit message with `Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>`.

---

## File map

| File | Responsibility |
|---|---|
| `Modules/BackOffice/Database/Migrations/2026_09_17_000001_create_termination_dues_tables.php` | the four tables |
| `database/migrations/2026_09_17_000002_add_termination_dues_menu.php` | menu row + 2 permissions + `termination_dues_account_map` setting |
| `Modules/BackOffice/Entities/TerminationDues.php`, `TerminationDuesLine.php`, `TerminationDuesAllocation.php`, `TerminationDuesFollowup.php` | Eloquent models |
| `Modules/BackOffice/Services/TerminationDuesCategory.php` | pure: category constants, labels, owner team, account-code → category |
| `Modules/BackOffice/Services/TerminationDuesBuilder.php` | pure: termination data → owed lines |
| `Modules/BackOffice/Services/TerminationDuesSettlement.php` | pure: lines + sources + manual → matrix/balances/status |
| `Modules/BackOffice/Services/TerminationDuesSources.php` | DB: gathers receipts & deposit deductions for a dues record |
| `Modules/BackOffice/Services/TerminationDuesService.php` | DB orchestration: create at 505, refresh, summary, rent-receipt guard |
| `Modules/BackOffice/Http/Controllers/TerminationDuesController.php` | list / detail / follow-up / allocation / waiver / JSON summary |
| `Modules/BackOffice/Resources/views/TerminationDues/index.blade.php`, `show.blade.php`, `_summary_box.blade.php` | screens + reusable AJAX box |
| `Modules/BackOffice/Console/TerminationDuesBackfill.php`, `TerminationDuesRefresh.php` | artisan commands |
| `tests/Unit/TerminationDuesCategoryTest.php`, `TerminationDuesBuilderTest.php`, `TerminationDuesSettlementTest.php` | pure unit tests |
| Modified: `TenantTerminationController.php` (505 block), `RentReceiptGenerationController.php` (guard + refresh hooks), `PdcController.php` (refresh), `DepositRefundController.php` (refresh), `Routes/web.php`, `Routes/breadcrumbs.php`, `app/Console/Kernel.php`, three create forms (summary box include) | integration |

---

### Task 1: Tables and models

**Files:**
- Create: `Modules/BackOffice/Database/Migrations/2026_09_17_000001_create_termination_dues_tables.php`
- Create: `Modules/BackOffice/Entities/TerminationDues.php`
- Create: `Modules/BackOffice/Entities/TerminationDuesLine.php`
- Create: `Modules/BackOffice/Entities/TerminationDuesAllocation.php`
- Create: `Modules/BackOffice/Entities/TerminationDuesFollowup.php`

**Interfaces:**
- Produces: models above with `$guarded = []`, relations `TerminationDues::lines()`, `followups()`, `tenantContract()`, `termination()`; `TerminationDuesLine::dues()`, `allocations()`; `TerminationDuesAllocation::line()`; `TerminationDuesFollowup::dues()`.

- [ ] **Step 1: Write the migration**

```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Termination dues: what a terminated tenant still owes, by category and
 * owner team. Settlements are computed live from receipts and deposit
 * refunds; only manual decisions (assignments, waivers) are stored here.
 * See docs/superpowers/specs/2026-09-17-termination-dues-design.md.
 */
class CreateTerminationDuesTables extends Migration
{
    public function up()
    {
        Schema::create('termination_dues', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_contract_id')->unique();
            $table->unsignedInteger('termination_id')->nullable();
            $table->date('termination_date')->nullable();
            $table->timestamp('terminated_at')->nullable();
            // Inspection (503) row created_at: general receipts from here on settle the charges.
            $table->timestamp('charges_fixed_at')->nullable();
            // Highest approved rent receipt already netted into the rent outstanding.
            $table->unsignedInteger('rent_receipts_upto_id')->nullable();
            $table->string('status', 20)->default('open'); // open | partial | settled | written_off
            $table->decimal('total_owed', 12, 3)->default(0);
            $table->decimal('total_settled', 12, 3)->default(0);
            $table->decimal('balance', 12, 3)->default(0);
            $table->decimal('backoffice_balance', 12, 3)->default(0);
            $table->decimal('maintenance_balance', 12, 3)->default(0);
            $table->date('next_promise_date')->nullable();
            $table->timestamp('last_followup_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('termination_date');
        });

        Schema::create('termination_dues_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('termination_dues_id');
            $table->string('category', 20);   // rent | municipal | ew | maintenance | other
            $table->string('owner_team', 20); // backoffice | maintenance
            $table->string('description', 255);
            $table->string('source_type', 50)->nullable(); // termination_checklist | termination | computed
            $table->unsignedInteger('source_id')->nullable();
            $table->decimal('amount', 12, 3)->default(0);
            $table->unsignedSmallInteger('line_order')->default(0);
            $table->timestamps();

            $table->foreign('termination_dues_id')->references('id')->on('termination_dues')->onDelete('cascade');
            $table->index(['termination_dues_id', 'category']);
        });

        Schema::create('termination_dues_allocations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('termination_dues_line_id');
            $table->string('source_type', 30); // rent_receipt | general_receipt_line | deposit_deduction | waiver
            $table->unsignedInteger('source_id')->nullable();
            $table->decimal('amount', 12, 3)->default(0);
            $table->text('remark')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('termination_dues_line_id')->references('id')->on('termination_dues_lines')->onDelete('cascade');
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('termination_dues_followups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('termination_dues_id');
            $table->string('owner_team', 20);
            $table->date('followup_date');
            $table->string('method', 30); // call | sms | whatsapp | email | visit | other
            $table->text('note')->nullable();
            $table->date('promise_date')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('termination_dues_id')->references('id')->on('termination_dues')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('termination_dues_followups');
        Schema::dropIfExists('termination_dues_allocations');
        Schema::dropIfExists('termination_dues_lines');
        Schema::dropIfExists('termination_dues');
    }
}
```

- [ ] **Step 2: Run the migration and verify the tables**

Run: `$PHP artisan migrate --path=Modules/BackOffice/Database/Migrations/2026_09_17_000001_create_termination_dues_tables.php`
Then: `$PHP -r '$p=new PDO("pgsql:host=127.0.0.1;dbname=plms_new","postgres","123456"); echo implode(",", $p->query("select table_name from information_schema.tables where table_name like \x27termination_dues%\x27 order by 1")->fetchAll(PDO::FETCH_COLUMN));'`
Expected: `termination_dues,termination_dues_allocations,termination_dues_followups,termination_dues_lines`

- [ ] **Step 3: Write the four models**

`Modules/BackOffice/Entities/TerminationDues.php`:
```php
<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TerminationDues extends Model
{
    protected $table = 'termination_dues';
    protected $guarded = [];
    protected $dates = ['termination_date', 'terminated_at', 'charges_fixed_at', 'next_promise_date', 'last_followup_at'];

    const STATUS_OPEN = 'open';
    const STATUS_PARTIAL = 'partial';
    const STATUS_SETTLED = 'settled';
    const STATUS_WRITTEN_OFF = 'written_off';

    public function lines()
    {
        return $this->hasMany('Modules\BackOffice\Entities\TerminationDuesLine', 'termination_dues_id')->orderBy('line_order')->orderBy('id');
    }

    public function followups()
    {
        return $this->hasMany('Modules\BackOffice\Entities\TerminationDuesFollowup', 'termination_dues_id')->orderBy('followup_date', 'desc')->orderBy('id', 'desc');
    }

    public function tenantContract()
    {
        return $this->belongsTo('Modules\Sales\Entities\TenantContract', 'tenant_contract_id', 'id');
    }

    public function termination()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\Termination', 'termination_id', 'id');
    }
}
```

`Modules/BackOffice/Entities/TerminationDuesLine.php`:
```php
<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TerminationDuesLine extends Model
{
    protected $table = 'termination_dues_lines';
    protected $guarded = [];

    public function dues()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\TerminationDues', 'termination_dues_id', 'id');
    }

    public function allocations()
    {
        return $this->hasMany('Modules\BackOffice\Entities\TerminationDuesAllocation', 'termination_dues_line_id');
    }
}
```

`Modules/BackOffice/Entities/TerminationDuesAllocation.php`:
```php
<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TerminationDuesAllocation extends Model
{
    protected $table = 'termination_dues_allocations';
    protected $guarded = [];

    public function line()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\TerminationDuesLine', 'termination_dues_line_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
}
```

`Modules/BackOffice/Entities/TerminationDuesFollowup.php`:
```php
<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TerminationDuesFollowup extends Model
{
    protected $table = 'termination_dues_followups';
    protected $guarded = [];
    protected $dates = ['followup_date', 'promise_date'];

    public function dues()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\TerminationDues', 'termination_dues_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
}
```

- [ ] **Step 4: Smoke-test the models**

Run: `$PHP artisan tinker --execute='$d = Modules\BackOffice\Entities\TerminationDues::create(["tenant_contract_id"=>-1,"status"=>"open"]); $d->lines()->create(["category"=>"rent","owner_team"=>"backoffice","description"=>"t","amount"=>1]); echo $d->fresh()->lines->count(); $d->delete(); echo " | left: ".Modules\BackOffice\Entities\TerminationDuesLine::where("termination_dues_id",$d->id)->count();'`
Expected output ends with `1 | left: 0` (cascade delete works).

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Database/Migrations/2026_09_17_000001_create_termination_dues_tables.php Modules/BackOffice/Entities/TerminationDues*.php
git commit -m "feat(termination-dues): tables and models for post-termination receivables"
```

---

### Task 2: Category rules (pure)

**Files:**
- Create: `Modules/BackOffice/Services/TerminationDuesCategory.php`
- Test: `tests/Unit/TerminationDuesCategoryTest.php`

**Interfaces:**
- Produces: constants `RENT='rent'`, `MUNICIPAL='municipal'`, `EW='ew'`, `MAINTENANCE='maintenance'`, `OTHER='other'`; `TEAM_BACKOFFICE='backoffice'`, `TEAM_MAINTENANCE='maintenance'`; `SETTING_KEY='termination_dues_account_map'`; `static all()`, `static label($category)`, `static ownerFor($category)`, `static defaultAccountMap()`, `static fromAccountCode($code, array $map)`, `static parseMap($json)`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;

class TerminationDuesCategoryTest extends TestCase
{
    public function test_default_map_classifies_the_known_account_codes()
    {
        $map = Cat::defaultAccountMap();

        $this->assertSame(Cat::RENT, Cat::fromAccountCode('12211', $map));
        $this->assertSame(Cat::MUNICIPAL, Cat::fromAccountCode('41102', $map));
        $this->assertSame(Cat::MUNICIPAL, Cat::fromAccountCode('41103', $map));
        $this->assertSame(Cat::EW, Cat::fromAccountCode('41105', $map));
        $this->assertSame(Cat::EW, Cat::fromAccountCode('22305', $map));
        $this->assertSame(Cat::MAINTENANCE, Cat::fromAccountCode('41110', $map));
        $this->assertSame(Cat::MAINTENANCE, Cat::fromAccountCode('12302', $map));
    }

    public function test_unknown_or_empty_code_is_unclassified()
    {
        $map = Cat::defaultAccountMap();

        $this->assertNull(Cat::fromAccountCode('22301', $map)); // cash in transit
        $this->assertNull(Cat::fromAccountCode('', $map));
        $this->assertNull(Cat::fromAccountCode(null, $map));
    }

    public function test_codes_are_compared_as_trimmed_strings()
    {
        $map = Cat::defaultAccountMap();
        $this->assertSame(Cat::RENT, Cat::fromAccountCode(12211, $map));
        $this->assertSame(Cat::RENT, Cat::fromAccountCode(' 12211 ', $map));
    }

    public function test_owner_team_per_category()
    {
        $this->assertSame(Cat::TEAM_MAINTENANCE, Cat::ownerFor(Cat::MAINTENANCE));
        foreach ([Cat::RENT, Cat::MUNICIPAL, Cat::EW, Cat::OTHER] as $c) {
            $this->assertSame(Cat::TEAM_BACKOFFICE, Cat::ownerFor($c));
        }
    }

    public function test_parse_map_falls_back_to_default_on_bad_json()
    {
        $this->assertSame(Cat::defaultAccountMap(), Cat::parseMap(''));
        $this->assertSame(Cat::defaultAccountMap(), Cat::parseMap('not json'));

        $custom = Cat::parseMap('{"rent":["99999"],"ew":["41105"]}');
        $this->assertSame(Cat::RENT, Cat::fromAccountCode('99999', $custom));
        $this->assertNull(Cat::fromAccountCode('12211', $custom));
    }

    public function test_labels_exist_for_every_category()
    {
        foreach (Cat::all() as $c) {
            $this->assertNotSame('', Cat::label($c));
        }
        $this->assertSame('Electricity & water', Cat::label(Cat::EW));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit/TerminationDuesCategoryTest.php`
Expected: Error `Class 'Modules\BackOffice\Services\TerminationDuesCategory' not found`.

- [ ] **Step 3: Implement**

```php
<?php

namespace Modules\BackOffice\Services;

/**
 * Dues categories, who follows each one up, and how an account code on a
 * general receipt or deposit-refund line maps to a category.
 * Pure PHP (no DB); the account map itself is read from the `configuration`
 * table by the caller (key SETTING_KEY) and parsed with parseMap().
 */
class TerminationDuesCategory
{
    const RENT        = 'rent';
    const MUNICIPAL   = 'municipal';
    const EW          = 'ew';
    const MAINTENANCE = 'maintenance';
    const OTHER       = 'other';

    const TEAM_BACKOFFICE  = 'backoffice';
    const TEAM_MAINTENANCE = 'maintenance';

    const SETTING_KEY = 'termination_dues_account_map';

    public static function all()
    {
        return [self::RENT, self::MUNICIPAL, self::EW, self::MAINTENANCE, self::OTHER];
    }

    public static function label($category)
    {
        $labels = [
            self::RENT        => 'Rent',
            self::MUNICIPAL   => 'Municipal tax',
            self::EW          => 'Electricity & water',
            self::MAINTENANCE => 'Maintenance',
            self::OTHER       => 'Other charges',
        ];
        return isset($labels[$category]) ? $labels[$category] : ucfirst((string) $category);
    }

    public static function teamLabel($team)
    {
        return $team === self::TEAM_MAINTENANCE ? 'Maintenance' : 'Back Office';
    }

    public static function ownerFor($category)
    {
        return $category === self::MAINTENANCE ? self::TEAM_MAINTENANCE : self::TEAM_BACKOFFICE;
    }

    /** Account codes seen on live deduction / general-receipt lines. */
    public static function defaultAccountMap()
    {
        return [
            self::RENT        => ['12211', '31001'],
            self::MUNICIPAL   => ['41102', '41103'],
            self::EW          => ['41105', '22305'],
            self::MAINTENANCE => ['41110', '22310', '41107', '22307', '12302'],
        ];
    }

    /**
     * @param string|null $json value of the configuration setting
     * @return array category => [codes]
     */
    public static function parseMap($json)
    {
        $decoded = json_decode((string) $json, true);
        if (!is_array($decoded) || empty($decoded)) {
            return self::defaultAccountMap();
        }
        $map = [];
        foreach ($decoded as $category => $codes) {
            if (!in_array($category, self::all(), true) || !is_array($codes)) {
                continue;
            }
            $map[$category] = array_values(array_map(function ($c) { return trim((string) $c); }, $codes));
        }
        return empty($map) ? self::defaultAccountMap() : $map;
    }

    /**
     * @return string|null category, or null when the code is not mapped
     */
    public static function fromAccountCode($code, array $map)
    {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }
        foreach ($map as $category => $codes) {
            if (in_array($code, array_map('strval', $codes), true)) {
                return $category;
            }
        }
        return null;
    }
}
```

- [ ] **Step 4: Run to verify it passes**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit/TerminationDuesCategoryTest.php`
Expected: `OK (6 tests, ...)`

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/TerminationDuesCategory.php tests/Unit/TerminationDuesCategoryTest.php
git commit -m "feat(termination-dues): category rules and account-code map"
```

---

### Task 3: Owed-lines builder (pure)

**Files:**
- Create: `Modules/BackOffice/Services/TerminationDuesBuilder.php`
- Test: `tests/Unit/TerminationDuesBuilderTest.php`

**Interfaces:**
- Consumes: `TerminationDuesCategory` constants and `ownerFor()`.
- Produces: `static build(array $input)` → `array` of lines `['category','owner_team','description','source_type','source_id','amount','line_order']`. Input keys: `rent_os` (float), `checklist_other` (list of `['id','name','amount']`), `checklist_works` (list of `['id','description','amount']`), `elec_water` (float), `maintenance_discount` (float). Also `static total(array $lines)`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\TerminationDuesBuilder;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;

/**
 * Snapshot of what a terminated tenant owes, from the inspection checklist
 * and the termination amounts. Pure: arrays in, arrays out.
 */
class TerminationDuesBuilderTest extends TestCase
{
    private function input(array $overrides = [])
    {
        return array_merge([
            'rent_os'              => 500.0,
            'checklist_other'      => [
                ['id' => 11, 'name' => 'Muncipal Tax',      'amount' => 45.0],
                ['id' => 12, 'name' => 'Any Other Charges', 'amount' => 10.0],
            ],
            'checklist_works'      => [
                ['id' => 21, 'description' => 'Painting - Full flat', 'amount' => 120.0],
                ['id' => 22, 'description' => 'Access card',          'amount' => 5.0],
            ],
            'elec_water'           => 80.0,
            'maintenance_discount' => 0.0,
        ], $overrides);
    }

    private function byCategory(array $lines, $category)
    {
        return array_values(array_filter($lines, function ($l) use ($category) { return $l['category'] === $category; }));
    }

    public function test_builds_one_line_per_charge_with_owner_team()
    {
        $lines = TerminationDuesBuilder::build($this->input());

        $this->assertCount(6, $lines);
        $rent = $this->byCategory($lines, Cat::RENT);
        $this->assertSame(500.0, $rent[0]['amount']);
        $this->assertSame(Cat::TEAM_BACKOFFICE, $rent[0]['owner_team']);
        $this->assertSame('computed', $rent[0]['source_type']);

        $mun = $this->byCategory($lines, Cat::MUNICIPAL);
        $this->assertSame(45.0, $mun[0]['amount']);
        $this->assertSame('termination_checklist', $mun[0]['source_type']);
        $this->assertSame(11, $mun[0]['source_id']);

        $maint = $this->byCategory($lines, Cat::MAINTENANCE);
        $this->assertCount(2, $maint);
        $this->assertSame('Painting - Full flat', $maint[0]['description']);
        $this->assertSame(Cat::TEAM_MAINTENANCE, $maint[0]['owner_team']);

        $this->assertSame(760.0, TerminationDuesBuilder::total($lines));
    }

    public function test_inspector_rent_row_overrides_computed_outstanding()
    {
        $in = $this->input();
        $in['checklist_other'][] = ['id' => 13, 'name' => 'Rent', 'amount' => 450.0];

        $rent = $this->byCategory(TerminationDuesBuilder::build($in), Cat::RENT);
        $this->assertCount(1, $rent);
        $this->assertSame(450.0, $rent[0]['amount']);
        $this->assertSame('termination_checklist', $rent[0]['source_type']);
        $this->assertSame(13, $rent[0]['source_id']);
    }

    public function test_zero_and_negative_amounts_are_skipped()
    {
        $lines = TerminationDuesBuilder::build($this->input([
            'rent_os'         => -12.5,
            'elec_water'      => 0,
            'checklist_other' => [['id' => 11, 'name' => 'Muncipal Tax', 'amount' => 0]],
            'checklist_works' => [['id' => 21, 'description' => 'Cleaning', 'amount' => '0.000']],
        ]));

        $this->assertSame([], $lines);
    }

    public function test_maintenance_discount_becomes_a_negative_maintenance_line_capped_at_the_maintenance_total()
    {
        $lines = TerminationDuesBuilder::build($this->input(['maintenance_discount' => 200.0]));

        $maint = $this->byCategory($lines, Cat::MAINTENANCE);
        $this->assertCount(3, $maint);
        $this->assertSame('Maintenance discount', $maint[2]['description']);
        $this->assertSame(-125.0, $maint[2]['amount']); // 120 + 5 = 125, so the 200 discount is capped
        $this->assertSame(635.0, TerminationDuesBuilder::total($lines));
    }

    public function test_amounts_are_rounded_to_three_decimals_and_strings_accepted()
    {
        $lines = TerminationDuesBuilder::build($this->input([
            'rent_os' => '1,234.5678', 'checklist_other' => [], 'checklist_works' => [], 'elec_water' => '0',
        ]));

        $this->assertCount(1, $lines);
        $this->assertSame(1234.568, $lines[0]['amount']);
    }

    public function test_line_order_is_sequential()
    {
        $lines = TerminationDuesBuilder::build($this->input());
        $this->assertSame(range(1, 6), array_column($lines, 'line_order'));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit/TerminationDuesBuilderTest.php`
Expected: class not found error.

- [ ] **Step 3: Implement**

```php
<?php

namespace Modules\BackOffice\Services;

use Modules\BackOffice\Services\TerminationDuesCategory as Cat;

/**
 * Turns the termination figures into the owed lines of a dues record.
 *
 * Rules (see the design spec):
 *   rent        = inspector's "Rent" other-charge row if present, else the
 *                 computed outstanding as on the termination date
 *   municipal   = "Muncipal Tax" other-charge row
 *   other       = every "Any Other Charges" / "Others" row
 *   ew          = termination electricity + water total
 *   maintenance = one line per checklist work row, then a negative
 *                 "Maintenance discount" line capped at the maintenance total
 * Lines with amount <= 0 are dropped. Pure: no models, no DB.
 */
class TerminationDuesBuilder
{
    const DECIMALS = 3;

    const OTHER_ROW_RENT      = 'Rent';
    const OTHER_ROW_MUNICIPAL = 'Muncipal Tax'; // spelling as stored by the inspection form
    const OTHER_ROWS_OTHER    = ['Any Other Charges', 'Others'];

    /**
     * @param array $input rent_os, checklist_other[], checklist_works[], elec_water, maintenance_discount
     * @return array lines: category, owner_team, description, source_type, source_id, amount, line_order
     */
    public static function build(array $input)
    {
        $other = isset($input['checklist_other']) && is_array($input['checklist_other']) ? $input['checklist_other'] : [];
        $works = isset($input['checklist_works']) && is_array($input['checklist_works']) ? $input['checklist_works'] : [];

        $lines = [];

        // Rent
        $rentRow = self::findOther($other, [self::OTHER_ROW_RENT]);
        if ($rentRow) {
            $lines[] = self::line(Cat::RENT, 'Outstanding rent', 'termination_checklist', $rentRow['id'], $rentRow['amount']);
        } else {
            $lines[] = self::line(Cat::RENT, 'Outstanding rent', 'computed', null, isset($input['rent_os']) ? $input['rent_os'] : 0);
        }

        // Municipal tax
        $munRow = self::findOther($other, [self::OTHER_ROW_MUNICIPAL]);
        if ($munRow) {
            $lines[] = self::line(Cat::MUNICIPAL, 'Municipal tax', 'termination_checklist', $munRow['id'], $munRow['amount']);
        }

        // Electricity & water
        $lines[] = self::line(Cat::EW, 'Electricity & water', 'termination', null, isset($input['elec_water']) ? $input['elec_water'] : 0);

        // Other charges
        foreach ($other as $row) {
            if (in_array(trim((string) $row['name']), self::OTHER_ROWS_OTHER, true)) {
                $lines[] = self::line(Cat::OTHER, trim((string) $row['name']), 'termination_checklist', $row['id'], $row['amount']);
            }
        }

        // Maintenance
        $maintenanceTotal = 0.0;
        foreach ($works as $row) {
            $l = self::line(Cat::MAINTENANCE, $row['description'], 'termination_checklist', $row['id'], $row['amount']);
            $lines[] = $l;
            if ($l['amount'] > 0) {
                $maintenanceTotal += $l['amount'];
            }
        }
        $discount = self::money(isset($input['maintenance_discount']) ? $input['maintenance_discount'] : 0);
        if ($discount > 0 && $maintenanceTotal > 0) {
            $lines[] = self::line(Cat::MAINTENANCE, 'Maintenance discount', 'termination', null, -min($discount, $maintenanceTotal), true);
        }

        // Drop empties, number the rest
        $out = [];
        $order = 0;
        foreach ($lines as $l) {
            if ($l['amount'] <= 0 && !$l['_keep_negative']) {
                continue;
            }
            unset($l['_keep_negative']);
            $l['line_order'] = ++$order;
            $out[] = $l;
        }

        // A discount alone is not a debt
        if (count($out) === 1 && $out[0]['amount'] < 0) {
            return [];
        }
        return $out;
    }

    public static function total(array $lines)
    {
        $t = 0.0;
        foreach ($lines as $l) {
            $t += (float) $l['amount'];
        }
        return round($t, self::DECIMALS);
    }

    private static function line($category, $description, $sourceType, $sourceId, $amount, $keepNegative = false)
    {
        return [
            'category'       => $category,
            'owner_team'     => Cat::ownerFor($category),
            'description'    => trim((string) $description) !== '' ? trim((string) $description) : Cat::label($category),
            'source_type'    => $sourceType,
            'source_id'      => $sourceId !== null ? (int) $sourceId : null,
            'amount'         => self::money($amount),
            '_keep_negative' => $keepNegative,
        ];
    }

    private static function findOther(array $rows, array $names)
    {
        foreach ($rows as $row) {
            if (in_array(trim((string) $row['name']), $names, true) && self::money($row['amount']) > 0) {
                return $row;
            }
        }
        return null;
    }

    /** Accepts "1,234.567" strings as the forms post them. */
    private static function money($value)
    {
        if (is_string($value)) {
            $value = str_replace(',', '', $value);
        }
        return round((float) $value, self::DECIMALS);
    }
}
```

- [ ] **Step 4: Run to verify it passes**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit/TerminationDuesBuilderTest.php`
Expected: `OK (6 tests, ...)`

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/TerminationDuesBuilder.php tests/Unit/TerminationDuesBuilderTest.php
git commit -m "feat(termination-dues): build owed lines from termination figures"
```

---

### Task 4: Settlement engine (pure)

**Files:**
- Create: `Modules/BackOffice/Services/TerminationDuesSettlement.php`
- Test: `tests/Unit/TerminationDuesSettlementTest.php`

**Interfaces:**
- Consumes: category constants.
- Produces: `static compute(array $lines, array $sources, array $manual)` → array:
  ```
  [
    'lines' => [ line_id => ['id','category','owner_team','description','owed','deposit','receipts','waived','settled','balance'] ],
    'teams' => [ 'backoffice' => ['owed','settled','balance'], 'maintenance' => [...] ],
    'total' => ['owed','settled','waived','balance'],
    'unallocated' => [ source... ],            // sources with no category or no line to take them
    'over_collected' => [ category => amount ], // surplus inside a category
    'allocations' => [ ['source_key','line_id','amount','manual'=>bool] ],
    'status' => 'open'|'partial'|'settled'|'written_off',
  ]
  ```
  - `$lines`: `['id','category','owner_team','description','amount']` in order.
  - `$sources`: `['key' => 'rent_receipt:123', 'kind' => 'rent_receipt'|'general_receipt_line'|'deposit_deduction', 'id', 'ref', 'date' (Y-m-d), 'amount', 'category' (string|null), 'account_code', 'description', 'url']`.
  - `$manual`: stored allocations `['id','source_type','source_id','termination_dues_line_id','amount','remark']` — `source_type` `waiver` adds to `waived`; any other type pins that source to that line.
  - `static key($kind, $id)` → `"$kind:$id"`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\TerminationDuesSettlement as S;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;

class TerminationDuesSettlementTest extends TestCase
{
    private function lines()
    {
        return [
            ['id' => 1, 'category' => Cat::RENT,        'owner_team' => 'backoffice',  'description' => 'Outstanding rent',   'amount' => 500.0],
            ['id' => 2, 'category' => Cat::MUNICIPAL,   'owner_team' => 'backoffice',  'description' => 'Municipal tax',      'amount' => 45.0],
            ['id' => 3, 'category' => Cat::EW,          'owner_team' => 'backoffice',  'description' => 'Electricity & water','amount' => 80.0],
            ['id' => 4, 'category' => Cat::MAINTENANCE, 'owner_team' => 'maintenance', 'description' => 'Painting',           'amount' => 120.0],
            ['id' => 5, 'category' => Cat::MAINTENANCE, 'owner_team' => 'maintenance', 'description' => 'Access card',        'amount' => 5.0],
        ];
    }

    private function src($kind, $id, $amount, $category, $date = '2026-09-01', $code = null)
    {
        return ['key' => S::key($kind, $id), 'kind' => $kind, 'id' => $id, 'ref' => strtoupper($kind) . $id,
                'date' => $date, 'amount' => $amount, 'category' => $category, 'account_code' => $code,
                'description' => '', 'url' => '#'];
    }

    public function test_nothing_settled_is_open_with_full_balance()
    {
        $r = S::compute($this->lines(), [], []);

        $this->assertSame('open', $r['status']);
        $this->assertSame(750.0, $r['total']['owed']);
        $this->assertSame(750.0, $r['total']['balance']);
        $this->assertSame(625.0, $r['teams']['backoffice']['balance']);
        $this->assertSame(125.0, $r['teams']['maintenance']['balance']);
    }

    public function test_rent_receipt_and_deposit_deduction_land_in_separate_columns()
    {
        $r = S::compute($this->lines(), [
            $this->src('deposit_deduction', 900, 300.0, Cat::RENT),
            $this->src('rent_receipt', 100, 200.0, Cat::RENT),
        ], []);

        $rent = $r['lines'][1];
        $this->assertSame(300.0, $rent['deposit']);
        $this->assertSame(200.0, $rent['receipts']);
        $this->assertSame(0.0, $rent['balance']);
        $this->assertSame('partial', $r['status']);
        $this->assertSame(250.0, $r['total']['balance']);
    }

    public function test_category_is_filled_oldest_line_first_and_surplus_is_reported()
    {
        $r = S::compute($this->lines(), [
            $this->src('general_receipt_line', 50, 130.0, Cat::MAINTENANCE, '2026-09-02', '41110'),
        ], []);

        $this->assertSame(120.0, $r['lines'][4]['receipts']);
        $this->assertSame(5.0, $r['lines'][5]['receipts']);
        $this->assertSame(5.0, $r['over_collected'][Cat::MAINTENANCE]);
        $this->assertSame(0.0, $r['teams']['maintenance']['balance']);
    }

    public function test_sources_are_applied_in_date_order()
    {
        $r = S::compute($this->lines(), [
            $this->src('general_receipt_line', 52, 100.0, Cat::MAINTENANCE, '2026-09-05'),
            $this->src('general_receipt_line', 51, 30.0,  Cat::MAINTENANCE, '2026-09-01'),
        ], []);

        // 30 (Sep 1) then 100 (Sep 5): painting takes 30 + 90, access card takes 5, surplus 5
        $this->assertSame(120.0, $r['lines'][4]['receipts']);
        $this->assertSame(5.0, $r['lines'][5]['receipts']);
        $this->assertSame(5.0, $r['over_collected'][Cat::MAINTENANCE]);
        $this->assertSame('general_receipt_line:51', $r['allocations'][0]['source_key']);
    }

    public function test_unclassified_or_unmatched_sources_are_listed_not_applied()
    {
        $r = S::compute($this->lines(), [
            $this->src('general_receipt_line', 60, 20.0, null, '2026-09-01', '22301'),
            $this->src('general_receipt_line', 61, 20.0, Cat::OTHER),
        ], []);

        $this->assertCount(2, $r['unallocated']);
        $this->assertSame(750.0, $r['total']['balance']);
    }

    public function test_manual_allocation_pins_a_source_to_a_line_and_removes_it_from_auto()
    {
        $r = S::compute($this->lines(), [
            $this->src('general_receipt_line', 60, 20.0, null, '2026-09-01', '22301'),
        ], [
            ['id' => 1, 'source_type' => 'general_receipt_line', 'source_id' => 60, 'termination_dues_line_id' => 2, 'amount' => 20.0, 'remark' => 'tenant said tax'],
        ]);

        $this->assertSame([], $r['unallocated']);
        $this->assertSame(20.0, $r['lines'][2]['receipts']);
        $this->assertSame(25.0, $r['lines'][2]['balance']);
        $this->assertTrue($r['allocations'][0]['manual']);
    }

    public function test_waiver_counts_as_written_off_when_it_closes_the_balance()
    {
        $r = S::compute($this->lines(), [
            $this->src('rent_receipt', 100, 500.0, Cat::RENT),
            $this->src('deposit_deduction', 900, 125.0, Cat::MAINTENANCE),
            $this->src('deposit_deduction', 901, 80.0, Cat::EW),
        ], [
            ['id' => 2, 'source_type' => 'waiver', 'source_id' => null, 'termination_dues_line_id' => 2, 'amount' => 45.0, 'remark' => 'approved by FM'],
        ]);

        $this->assertSame(45.0, $r['lines'][2]['waived']);
        $this->assertSame(0.0, $r['total']['balance']);
        $this->assertSame(45.0, $r['total']['waived']);
        $this->assertSame('written_off', $r['status']);
    }

    public function test_fully_paid_without_waiver_is_settled()
    {
        $r = S::compute($this->lines(), [
            $this->src('rent_receipt', 100, 500.0, Cat::RENT),
            $this->src('general_receipt_line', 1, 45.0, Cat::MUNICIPAL),
            $this->src('general_receipt_line', 2, 80.0, Cat::EW),
            $this->src('general_receipt_line', 3, 125.0, Cat::MAINTENANCE),
        ], []);

        $this->assertSame('settled', $r['status']);
        $this->assertSame(750.0, $r['total']['settled']);
    }

    public function test_negative_discount_line_reduces_team_owed()
    {
        $lines = $this->lines();
        $lines[] = ['id' => 6, 'category' => Cat::MAINTENANCE, 'owner_team' => 'maintenance', 'description' => 'Maintenance discount', 'amount' => -25.0];

        $r = S::compute($lines, [], []);
        $this->assertSame(100.0, $r['teams']['maintenance']['owed']);
        $this->assertSame(725.0, $r['total']['owed']);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit/TerminationDuesSettlementTest.php`
Expected: class not found.

- [ ] **Step 3: Implement**

```php
<?php

namespace Modules\BackOffice\Services;

use Modules\BackOffice\Services\TerminationDuesCategory as Cat;
use Modules\BackOffice\Entities\TerminationDues;

/**
 * Applies settlements (rent receipts, general-receipt lines, deposit-refund
 * deductions) to the owed lines of a dues record and reports balances.
 *
 * Nothing here is persisted: the caller gathers live sources each time, so
 * a cancelled, bounced or deleted receipt simply stops appearing. Only the
 * manual decisions passed in $manual come from the database.
 *
 * Allocation: a source pinned by a manual allocation goes to that line. Every
 * other source goes to its category, oldest source first, oldest line first,
 * until each line's balance is zero; what is left over in a category is
 * reported as over-collected. Sources without a category, or whose category
 * has no line, are reported as unallocated for a person to assign.
 */
class TerminationDuesSettlement
{
    const EPS = 0.005;

    public static function key($kind, $id)
    {
        return $kind . ':' . (int) $id;
    }

    public static function compute(array $lines, array $sources, array $manual)
    {
        $state = [];
        $byCategory = [];
        foreach ($lines as $l) {
            $id = (int) $l['id'];
            $state[$id] = [
                'id'          => $id,
                'category'    => $l['category'],
                'owner_team'  => $l['owner_team'],
                'description' => $l['description'],
                'owed'        => round((float) $l['amount'], 3),
                'deposit'     => 0.0,
                'receipts'    => 0.0,
                'waived'      => 0.0,
            ];
            if ($state[$id]['owed'] > 0) {
                $byCategory[$l['category']][] = $id;
            }
        }

        $allocations = [];
        $pinned = []; // source key => [line_id, amount]
        foreach ($manual as $m) {
            $lineId = (int) $m['termination_dues_line_id'];
            if (!isset($state[$lineId])) {
                continue;
            }
            $amount = round((float) $m['amount'], 3);
            if ($m['source_type'] === 'waiver') {
                $state[$lineId]['waived'] += $amount;
                $allocations[] = ['source_key' => 'waiver:' . (int) $m['id'], 'line_id' => $lineId, 'amount' => $amount, 'manual' => true];
                continue;
            }
            $pinned[self::key($m['source_type'], $m['source_id'])] = ['line_id' => $lineId, 'amount' => $amount];
        }

        usort($sources, function ($a, $b) {
            $c = strcmp((string) $a['date'], (string) $b['date']);
            return $c !== 0 ? $c : ((int) $a['id'] - (int) $b['id']);
        });

        $unallocated = [];
        $over = [];
        foreach ($sources as $s) {
            $column = $s['kind'] === 'deposit_deduction' ? 'deposit' : 'receipts';
            $amount = round((float) $s['amount'], 3);

            if (isset($pinned[$s['key']])) {
                $p = $pinned[$s['key']];
                $state[$p['line_id']][$column] += $p['amount'];
                $allocations[] = ['source_key' => $s['key'], 'line_id' => $p['line_id'], 'amount' => $p['amount'], 'manual' => true];
                continue;
            }

            $category = isset($s['category']) ? $s['category'] : null;
            if ($category === null || empty($byCategory[$category])) {
                $unallocated[] = $s;
                continue;
            }

            $remaining = $amount;
            foreach ($byCategory[$category] as $lineId) {
                if ($remaining <= self::EPS) {
                    break;
                }
                $room = self::balance($state[$lineId]);
                if ($room <= self::EPS) {
                    continue;
                }
                $take = min($room, $remaining);
                $state[$lineId][$column] += $take;
                $allocations[] = ['source_key' => $s['key'], 'line_id' => $lineId, 'amount' => round($take, 3), 'manual' => false];
                $remaining -= $take;
            }
            if ($remaining > self::EPS) {
                $over[$category] = round((isset($over[$category]) ? $over[$category] : 0) + $remaining, 3);
            }
        }

        $teams = [
            Cat::TEAM_BACKOFFICE  => ['owed' => 0.0, 'settled' => 0.0, 'balance' => 0.0],
            Cat::TEAM_MAINTENANCE => ['owed' => 0.0, 'settled' => 0.0, 'balance' => 0.0],
        ];
        $total = ['owed' => 0.0, 'settled' => 0.0, 'waived' => 0.0, 'balance' => 0.0];
        foreach ($state as $id => &$l) {
            $l['settled'] = round($l['deposit'] + $l['receipts'], 3);
            $l['balance'] = round(max(self::balance($l), 0), 3);
            $l['deposit'] = round($l['deposit'], 3);
            $l['receipts'] = round($l['receipts'], 3);
            $l['waived'] = round($l['waived'], 3);

            $team = isset($teams[$l['owner_team']]) ? $l['owner_team'] : Cat::TEAM_BACKOFFICE;
            $teams[$team]['owed'] += $l['owed'];
            $teams[$team]['settled'] += $l['settled'];
            $teams[$team]['balance'] += $l['balance'];
            $total['owed'] += $l['owed'];
            $total['settled'] += $l['settled'];
            $total['waived'] += $l['waived'];
            $total['balance'] += $l['balance'];
        }
        unset($l);
        foreach ($teams as &$t) {
            $t = array_map(function ($v) { return round($v, 3); }, $t);
        }
        unset($t);
        $total = array_map(function ($v) { return round($v, 3); }, $total);

        return [
            'lines'          => $state,
            'teams'          => $teams,
            'total'          => $total,
            'unallocated'    => $unallocated,
            'over_collected' => $over,
            'allocations'    => $allocations,
            'status'         => self::status($total),
        ];
    }

    private static function balance(array $line)
    {
        return $line['owed'] - $line['deposit'] - $line['receipts'] - $line['waived'];
    }

    private static function status(array $total)
    {
        if ($total['balance'] <= self::EPS) {
            return $total['waived'] > self::EPS ? TerminationDues::STATUS_WRITTEN_OFF : TerminationDues::STATUS_SETTLED;
        }
        return ($total['settled'] + $total['waived']) > self::EPS ? TerminationDues::STATUS_PARTIAL : TerminationDues::STATUS_OPEN;
    }
}
```

Note: this class references `TerminationDues::STATUS_*` constants. That model has no Eloquent bootstrapping on constant access, so the pure test still runs without the app (autoload only). If the test errors on `Illuminate\Database\Eloquent\Model` not found, the vendor autoloader is missing — run `composer dump-autoload` with `$PHP`.

- [ ] **Step 4: Run to verify it passes**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit/TerminationDuesSettlementTest.php`
Expected: `OK (9 tests, ...)`

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/TerminationDuesSettlement.php tests/Unit/TerminationDuesSettlementTest.php
git commit -m "feat(termination-dues): pure settlement engine with FIFO allocation, manual pins and waivers"
```

---

### Task 5: Sources gatherer and orchestration service (DB)

**Files:**
- Create: `Modules/BackOffice/Services/TerminationDuesSources.php`
- Create: `Modules/BackOffice/Services/TerminationDuesService.php`

**Interfaces:**
- Consumes: Task 2–4 classes; `DepositRefundReceiptBuilder::build($rows, $payoutCodes)` (`Modules/BackOffice/Services/DepositRefundReceiptBuilder.php`, returns `lines[]` with `description, account_code, amount, line_type`); `DepositRefundReceiptIssuer::PAYOUT_KEY`; global helper `totalContractRentCountCalculation($contractId, $terminationDate)` (`config/function.php:578`).
- Produces:
  - `TerminationDuesSources::forDues(TerminationDues $dues)` → array of source arrays (see Task 4 shape) plus `TerminationDuesSources::pendingForDues($dues)` → receipts awaiting approval (for display only).
  - `TerminationDuesService`:
    - `createForTermination(Termination $termination, $userId)` → `TerminationDues|null`
    - `buildInputFor($contractId, Termination $finalRow)` → builder input array
    - `refresh(TerminationDues $dues)` → computed result array (and persists status/balances)
    - `static touchContract($contractId)` — refresh wrapped in try/catch, safe in hooks
    - `static touchReceipt($receiptId)` / `static touchDepositRefund($depositRefundId)`
    - `summaryForContract($contractId)` → `null|['dues_id','url','status','balance','teams','categories'=>[cat=>['label','owed','settled','balance']]]`
    - `rentReceiptError($contractId, $amount, $effTo, $ignoreReceiptId = null)` → `string|null`

- [ ] **Step 1: Write `TerminationDuesSources`**

```php
<?php

namespace Modules\BackOffice\Services;

use App\Setting;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;
use Modules\BackOffice\Services\TerminationDuesSettlement as S;

/**
 * Gathers, from live data, everything that settles a dues record:
 *   - approved rent receipts newer than the ones already netted into the
 *     rent outstanding (receipts_generation type 0),
 *   - credit lines of approved general receipts created after the charges
 *     were fixed at inspection (type 1, one source per dim line),
 *   - deduction lines of live deposit refunds (same rule as the deposit
 *     refund receipt: credit lines that are neither payout nor deposit).
 * Cancelled (status 2), soft-deleted and unapproved receipts are excluded,
 * so reversing a payment needs no code here.
 */
class TerminationDuesSources
{
    /** @var array category => [codes] */
    private $map;
    /** @var array payout account codes */
    private $payout;

    public function __construct()
    {
        $setting = Setting::where('configuration_settings', Cat::SETTING_KEY)->first();
        $this->map = Cat::parseMap($setting ? $setting->configuration_value : null);

        $payout = Setting::where('configuration_settings', DepositRefundReceiptIssuer::PAYOUT_KEY)->first();
        $decoded = $payout ? json_decode((string) $payout->configuration_value, true) : null;
        $this->payout = is_array($decoded) ? $decoded : ['12601', '22461', '22401', '22301', '12651'];
    }

    public function accountMap()
    {
        return $this->map;
    }

    public function forDues(TerminationDues $dues)
    {
        return array_merge(
            $this->rentReceipts($dues),
            $this->generalReceiptLines($dues),
            $this->depositDeductions($dues)
        );
    }

    private function rentReceipts(TerminationDues $dues)
    {
        $q = DB::table('receipts_generation')
            ->where('tenant_contract_id', $dues->tenant_contract_id)
            ->where('receipts_generation_type', 0)
            ->where('receipts_generation_approval_status', 3)
            ->where('receipts_generation_status', '<>', 2)
            ->whereNull('deleted_at');
        if ($dues->rent_receipts_upto_id) {
            $q->where('id', '>', $dues->rent_receipts_upto_id);
        }
        $out = [];
        foreach ($q->orderBy('id')->get() as $r) {
            $out[] = [
                'key'          => S::key('rent_receipt', $r->id),
                'kind'         => 'rent_receipt',
                'id'           => (int) $r->id,
                'ref'          => $r->receipts_generation_receipt_no,
                'date'         => substr((string) $r->receipts_generation_receipt_date, 0, 10),
                'amount'       => (float) $r->receipts_generation_amt,
                'category'     => Cat::RENT,
                'account_code' => null,
                'description'  => 'Rent receipt' . ($r->receipts_generation_eff_from ? ' ' . date('d/m/Y', strtotime($r->receipts_generation_eff_from)) . ' - ' . date('d/m/Y', strtotime($r->receipts_generation_eff_to)) : ''),
                'url'          => url('rentReceiptGeneration/' . $r->id),
            ];
        }
        return $out;
    }

    private function generalReceiptLines(TerminationDues $dues)
    {
        $q = DB::table('receipts_generation_dim as d')
            ->join('receipts_generation as r', 'r.id', '=', 'd.receipts_generation_id')
            ->where('r.tenant_contract_id', $dues->tenant_contract_id)
            ->where('r.receipts_generation_type', 1)
            ->where('r.receipts_generation_approval_status', 3)
            ->where('r.receipts_generation_status', '<>', 2)
            ->whereNull('r.deleted_at')
            ->where('d.credit_amount', '>', 0);
        if ($dues->charges_fixed_at) {
            $q->where('r.created_at', '>=', $dues->charges_fixed_at);
        }
        $out = [];
        foreach ($q->select('d.id', 'd.account_code', 'd.description', 'd.credit_amount', 'r.id as receipt_id', 'r.receipts_generation_receipt_no', 'r.receipts_generation_receipt_date', 'r.receipts_generation_description')->orderBy('d.id')->get() as $l) {
            $out[] = [
                'key'          => S::key('general_receipt_line', $l->id),
                'kind'         => 'general_receipt_line',
                'id'           => (int) $l->id,
                'ref'          => $l->receipts_generation_receipt_no,
                'date'         => substr((string) $l->receipts_generation_receipt_date, 0, 10),
                'amount'       => (float) $l->credit_amount,
                'category'     => Cat::fromAccountCode($l->account_code, $this->map),
                'account_code' => $l->account_code,
                'description'  => trim((string) ($l->description ?: $l->receipts_generation_description)),
                'url'          => url('rentReceiptGeneration/' . $l->receipt_id),
            ];
        }
        return $out;
    }

    private function depositDeductions(TerminationDues $dues)
    {
        $refunds = DB::table('deposit_refund')
            ->where('tenant_contract_id', $dues->tenant_contract_id)
            ->whereNull('deleted_at')
            ->whereNull('deposit_refund_cancelled_date')
            ->get();
        $out = [];
        foreach ($refunds as $refund) {
            $dims = DB::table('deposit_refund_dim')->where('deposit_refund_id', $refund->id)->orderBy('id')->get();
            foreach ($dims as $d) {
                $credit = (float) $d->credit_amount;
                $code = trim((string) $d->account_code);
                if ($credit <= 0 || in_array($code, array_map('strval', $this->payout), true) || $code === DepositRefundReceiptBuilder::DEPOSIT_ACCOUNT) {
                    continue;
                }
                $out[] = [
                    'key'          => S::key('deposit_deduction', $d->id),
                    'kind'         => 'deposit_deduction',
                    'id'           => (int) $d->id,
                    'ref'          => $refund->deposit_refund_no,
                    'date'         => substr((string) $refund->deposit_refund_date, 0, 10),
                    'amount'       => $credit,
                    'category'     => Cat::fromAccountCode($code, $this->map),
                    'account_code' => $code,
                    'description'  => trim((string) $d->description),
                    'url'          => url('depositRefund/' . $refund->id),
                ];
            }
        }
        return $out;
    }

    /** Receipts on the contract that exist but are not yet approved (display only). */
    public function pendingForDues(TerminationDues $dues)
    {
        return DB::table('receipts_generation')
            ->where('tenant_contract_id', $dues->tenant_contract_id)
            ->whereIn('receipts_generation_type', [0, 1])
            ->where('receipts_generation_approval_status', '<>', 3)
            ->where('receipts_generation_approval_status', '<>', 6)
            ->where('receipts_generation_status', '<>', 2)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $dues->charges_fixed_at ?: $dues->terminated_at ?: '1970-01-01')
            ->orderBy('id')
            ->get(['id', 'receipts_generation_receipt_no', 'receipts_generation_receipt_date', 'receipts_generation_amt', 'receipts_generation_type']);
    }
}
```

- [ ] **Step 2: Write `TerminationDuesService`**

```php
<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Entities\TerminationChecklist;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Entities\TerminationDuesAllocation;
use Modules\Sales\Entities\TenantContract;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;

/**
 * Creates a dues record when a termination completes, recomputes its
 * balances from live sources, and answers the two questions other screens
 * ask: "what does this contract still owe?" and "may this rent receipt be
 * saved?".
 */
class TerminationDuesService
{
    const FINAL_STAGE = 505;
    const INSPECTION_STAGE = 503;

    /**
     * Called from the 505 block of TenantTerminationController. Idempotent:
     * a contract that already has dues is left alone.
     *
     * @return TerminationDues|null null when nothing is owed
     */
    public function createForTermination(Termination $termination, $userId = null)
    {
        $existing = TerminationDues::where('tenant_contract_id', $termination->contract_id)->first();
        if ($existing) {
            return $existing;
        }

        $input = $this->buildInputFor($termination->contract_id, $termination);
        $lines = TerminationDuesBuilder::build($input);
        if (empty($lines)) {
            return null;
        }

        $inspection = Termination::where('contract_id', $termination->contract_id)
            ->where('work_flow_processes_code', self::INSPECTION_STAGE)
            ->orderBy('id', 'desc')->first();

        $uptoId = DB::table('receipts_generation')
            ->where('tenant_contract_id', $termination->contract_id)
            ->where('receipts_generation_type', 0)
            ->where('receipts_generation_approval_status', 3)
            ->whereNull('deleted_at')
            ->max('id');

        return DB::transaction(function () use ($termination, $lines, $inspection, $uptoId, $userId) {
            $dues = TerminationDues::create([
                'tenant_contract_id'   => $termination->contract_id,
                'termination_id'       => $termination->id,
                'termination_date'     => $termination->termination_date,
                'terminated_at'        => $termination->created_at ?: now(),
                'charges_fixed_at'     => $inspection && $inspection->created_at ? $inspection->created_at : ($termination->created_at ?: now()),
                'rent_receipts_upto_id'=> $uptoId,
                'status'               => TerminationDues::STATUS_OPEN,
                'created_by'           => $userId,
            ]);
            foreach ($lines as $l) {
                $dues->lines()->create($l);
            }
            $this->refresh($dues);
            return $dues;
        });
    }

    /**
     * Builder input from the inspection checklist and the termination row.
     * Public so the backfill command can reuse it.
     */
    public function buildInputFor($contractId, Termination $finalRow)
    {
        // Checklist rows of the latest inspection for this contract
        $latestTerminationId = TerminationChecklist::where('termination_contract_id', $contractId)->max('termination_id');
        $rows = $latestTerminationId
            ? TerminationChecklist::with(['work', 'subWorks'])->where('termination_contract_id', $contractId)->where('termination_id', $latestTerminationId)->orderBy('id')->get()
            : collect();

        $other = [];
        $works = [];
        foreach ($rows as $row) {
            if ($row->work_id) {
                $desc = trim((string) (optional($row->work)->works_desc));
                $sub  = trim((string) (optional($row->subWorks)->sub_work));
                $works[] = [
                    'id'          => $row->id,
                    'description' => trim($desc . ($sub !== '' ? ' - ' . $sub : '')) ?: 'Maintenance item',
                    'amount'      => $row->termination_amount,
                ];
            } elseif ($row->termination_other_work) {
                $other[] = ['id' => $row->id, 'name' => $row->termination_other_work, 'amount' => $row->termination_amount];
            }
        }

        // Rent outstanding as on the termination date, same formula as the termination flow
        $rentOs = 0;
        $terminationDate = $finalRow->termination_date ? date('Y-m-d', strtotime($finalRow->termination_date)) : date('Y-m-d');
        try {
            $sumOfReceipt = DB::table('receipts_generation')
                ->where('receipts_generation_type', 0)
                ->where('tenant_contract_id', $contractId)
                ->where('receipts_generation_approval_status', 3)
                ->whereNull('deleted_at')
                ->sum('receipts_generation_amt');
            $rentOs = (float) totalContractRentCountCalculation($contractId, $terminationDate) - (float) $sumOfReceipt;
        } catch (\Exception $e) {
            Log::warning('TerminationDues: rent OS calculation failed for contract ' . $contractId . ': ' . $e->getMessage());
        }

        // Amounts live on the row that carries the inspection figures
        $figures = Termination::where('contract_id', $contractId)->whereNotNull('termination_total_amount')->orderBy('id', 'desc')->first() ?: $finalRow;
        $ew = $figures->termination_total_elec_water_amount;
        if ($ew === null || $ew === '') {
            $ew = (float) $figures->termination_electricity_amount + (float) $figures->termination_water_amount;
        }

        return [
            'rent_os'              => $rentOs,
            'checklist_other'      => $other,
            'checklist_works'      => $works,
            'elec_water'           => $ew,
            'maintenance_discount' => $figures->termination_discount_maintenance_due,
        ];
    }

    /**
     * Recompute from live sources and persist the summary columns.
     * @return array the TerminationDuesSettlement::compute() result plus 'sources', 'pending', 'account_map'
     */
    public function refresh(TerminationDues $dues)
    {
        $dues->loadMissing('lines');
        $sources = new TerminationDuesSources();
        $lines = $dues->lines->map(function ($l) {
            return ['id' => $l->id, 'category' => $l->category, 'owner_team' => $l->owner_team, 'description' => $l->description, 'amount' => $l->amount];
        })->all();
        $manual = TerminationDuesAllocation::whereIn('termination_dues_line_id', array_column($lines, 'id'))->orderBy('id')->get()->toArray();
        $src = $sources->forDues($dues);

        $result = TerminationDuesSettlement::compute($lines, $src, $manual);
        $result['sources'] = $src;
        $result['pending'] = $sources->pendingForDues($dues);
        $result['account_map'] = $sources->accountMap();

        $latestFollowup = $dues->followups()->first();
        $dues->forceFill([
            'status'              => $result['status'],
            'total_owed'          => $result['total']['owed'],
            'total_settled'       => $result['total']['settled'] + $result['total']['waived'],
            'balance'             => $result['total']['balance'],
            'backoffice_balance'  => $result['teams'][Cat::TEAM_BACKOFFICE]['balance'],
            'maintenance_balance' => $result['teams'][Cat::TEAM_MAINTENANCE]['balance'],
            'last_followup_at'    => $latestFollowup ? $latestFollowup->created_at : null,
            'next_promise_date'   => $latestFollowup ? $latestFollowup->promise_date : null,
        ])->save();

        return $result;
    }

    /** Safe to call from any hook: never throws. */
    public static function touchContract($contractId)
    {
        if (!$contractId) {
            return;
        }
        try {
            $dues = TerminationDues::where('tenant_contract_id', $contractId)->first();
            if ($dues) {
                (new self)->refresh($dues);
            }
        } catch (\Exception $e) {
            Log::warning('TerminationDues refresh failed for contract ' . $contractId . ': ' . $e->getMessage());
        }
    }

    public static function touchReceipt($receiptId)
    {
        $contractId = DB::table('receipts_generation')->where('id', $receiptId)->value('tenant_contract_id');
        self::touchContract($contractId);
    }

    public static function touchDepositRefund($depositRefundId)
    {
        $contractId = DB::table('deposit_refund')->where('id', $depositRefundId)->value('tenant_contract_id');
        self::touchContract($contractId);
    }

    /**
     * Compact figures for the summary box on the receipt / deposit refund forms.
     * @return array|null null when the contract has no dues
     */
    public function summaryForContract($contractId)
    {
        $dues = TerminationDues::where('tenant_contract_id', $contractId)->first();
        if (!$dues) {
            return null;
        }
        $r = $this->refresh($dues);
        $categories = [];
        foreach ($r['lines'] as $l) {
            $c = $l['category'];
            if (!isset($categories[$c])) {
                $categories[$c] = ['label' => Cat::label($c), 'owner_team' => $l['owner_team'], 'owed' => 0.0, 'settled' => 0.0, 'balance' => 0.0];
            }
            $categories[$c]['owed'] += $l['owed'];
            $categories[$c]['settled'] += $l['settled'] + $l['waived'];
            $categories[$c]['balance'] += $l['balance'];
        }
        return [
            'dues_id'          => $dues->id,
            'url'              => route('termination-dues.show', $dues->id),
            'status'           => $r['status'],
            'termination_date' => $dues->termination_date ? $dues->termination_date->format('d/m/Y') : null,
            'balance'          => $r['total']['balance'],
            'teams'            => $r['teams'],
            'categories'       => array_values($categories),
        ];
    }

    /**
     * Guard for rent receipts on terminated contracts.
     * @return string|null error message, or null when the receipt may be saved
     */
    public function rentReceiptError($contractId, $amount, $effTo = null, $ignoreReceiptId = null)
    {
        $contract = TenantContract::where('id', $contractId)->first(['id', 'tenant_renewal_termination_status', 'tenant_contract_no']);
        if (!$contract || (int) $contract->tenant_renewal_termination_status !== 8) {
            return null;
        }
        $dues = TerminationDues::where('tenant_contract_id', $contractId)->first();
        if (!$dues) {
            return 'Contract ' . $contract->tenant_contract_no . ' is terminated and has no termination dues on record. Rent receipts cannot be created on it; if rent is genuinely outstanding ask an administrator to run the termination dues backfill.';
        }
        $r = $this->refresh($dues);
        $rentBalance = 0.0;
        foreach ($r['lines'] as $l) {
            if ($l['category'] === Cat::RENT) {
                $rentBalance += $l['balance'];
            }
        }
        if ($ignoreReceiptId) {
            // Editing an approved receipt that is already counted: give its amount back to the balance
            $current = DB::table('receipts_generation')->where('id', $ignoreReceiptId)->where('receipts_generation_approval_status', 3)->value('receipts_generation_amt');
            $rentBalance += (float) $current;
        }
        $amount = (float) $amount;
        if ($rentBalance <= TerminationDuesSettlement::EPS) {
            return 'Contract ' . $contract->tenant_contract_no . ' is terminated and its rent dues are fully settled (see Termination Dues). No further rent receipt can be created.';
        }
        if ($amount > $rentBalance + TerminationDuesSettlement::EPS) {
            return 'Receipt amount ' . numberFormat($amount) . ' exceeds the open rent dues of ' . numberFormat($rentBalance) . ' on terminated contract ' . $contract->tenant_contract_no . '. Enter at most ' . numberFormat($rentBalance) . '.';
        }
        if ($effTo && $dues->termination_date && strtotime($effTo) > $dues->termination_date->getTimestamp()) {
            return 'Effective-to date ' . date('d/m/Y', strtotime($effTo)) . ' is after the termination date ' . $dues->termination_date->format('d/m/Y') . '. Rent cannot be collected for a period after termination.';
        }
        return null;
    }
}
```

- [ ] **Step 3: Verify against real data with a scratch script (no writes)**

Create `C:\Users\anoop\AppData\Local\Temp\claude\C--laragon-www-plms-backup\<session>\scratchpad\dues_dryrun.php` (or run through tinker):

```php
// $PHP artisan tinker --execute='...'
$svc = new Modules\BackOffice\Services\TerminationDuesService;
$t = Modules\BackOffice\Entities\Termination::where("work_flow_processes_code",505)->whereHas("tenantContract", function($q){ $q->where("tenant_renewal_termination_status",8); })->orderBy("id","desc")->first();
print_r($svc->buildInputFor($t->contract_id, $t));
print_r(Modules\BackOffice\Services\TerminationDuesBuilder::build($svc->buildInputFor($t->contract_id, $t)));
```
Expected: an input array with `rent_os`, checklist rows and E&W for the latest terminated contract, and a non-empty lines list when anything is owed. Try 2–3 different contracts (e.g. `TAG2502139`, `TAG2601471` from the audit) with `Termination::where('contract_id', TenantContract::where('tenant_contract_no','TAG2601471')->value('id'))->where('work_flow_processes_code',505)->first()`.

- [ ] **Step 4: Create one real dues record in a transaction and roll it back**

```php
// tinker
DB::beginTransaction();
$svc = new Modules\BackOffice\Services\TerminationDuesService;
$t = Modules\BackOffice\Entities\Termination::where("work_flow_processes_code",505)->orderBy("id","desc")->first();
$d = $svc->createForTermination($t, 1);
if ($d) { $r = $svc->refresh($d); echo $r["status"]." owed=".$r["total"]["owed"]." balance=".$r["total"]["balance"]." sources=".count($r["sources"])." unallocated=".count($r["unallocated"])."\n"; print_r($r["teams"]); }
else echo "nothing owed\n";
DB::rollBack();
echo "rows left: ".Modules\BackOffice\Entities\TerminationDues::count();
```
Expected: sensible figures and `rows left: 0`.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/TerminationDuesSources.php Modules/BackOffice/Services/TerminationDuesService.php
git commit -m "feat(termination-dues): live sources gatherer and orchestration service"
```

---

### Task 6: Create dues at final termination; refresh hooks in receipts, PDC and deposit refund

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/TenantTerminationController.php:580-586` (the `if($next_process_id == 505)` block)
- Modify: `Modules/BackOffice/Http/Controllers/RentReceiptGenerationController.php` — `store` (~line 152 after create), `update`, `addGeneralReceiptAction` (~line 900), `updateGeneralReceiptAction`, `receiptApprovalStatus` (case 3, ~line 1218), `receiptAsPosted` (~line 1600), `destroy` (~line 291)
- Modify: `Modules/BackOffice/Http/Controllers/PdcController.php:385,416,486,494`
- Modify: `Modules/BackOffice/Http/Controllers/DepositRefundController.php` — `store` (~line 224), `update`, `cancelDepositRefundStore` (~line 750)

**Interfaces:**
- Consumes: `TerminationDuesService::createForTermination()`, `::touchContract()`, `::touchReceipt()`, `::touchDepositRefund()`.

- [ ] **Step 1: Termination 505 block**

In `TenantTerminationController.php`, add the import near the other `use Modules\BackOffice\Services\...` lines (there is already `use Modules\BackOffice\Services\MunicipalTaxCalculator;` at line 43):
```php
use Modules\BackOffice\Services\TerminationDuesService;
```
Inside `if($next_process_id == 505){`, directly after
```php
    $contractUpdate = TenantContract::where('id',$termination->contract_id)->update(['tenant_contract_status'=>0,'tenant_renewal_termination_status'=>8]);
```
add:
```php
    // Outstanding rent / tax / E&W / maintenance becomes a tracked receivable
    // instead of a receipt nobody has paid. Never blocks the termination.
    try {
      (new TerminationDuesService)->createForTermination($termination, \Auth::user()->id);
    } catch (\Exception $e) {
      \Log::error('TerminationDues create failed for contract '.$termination->contract_id.': '.$e->getMessage());
    }
```

- [ ] **Step 2: Rent receipt controller hooks**

Add import at top of `RentReceiptGenerationController.php`:
```php
use Modules\BackOffice\Services\TerminationDuesService;
```
- `store()`: after `$this->incrementSequenceNo($configIncKey);` (line ~152) add `TerminationDuesService::touchContract($request->tenant_contract_no);`
- `update()`: before its redirect add `TerminationDuesService::touchReceipt($id);`
- `addGeneralReceiptAction()`: after `$this->incrementSequenceNo($configIncKey);` add `TerminationDuesService::touchContract($request->tenant_contract_no);`
- `updateGeneralReceiptAction()`: before its redirect add `TerminationDuesService::touchReceipt($id);`
- `receiptApprovalStatus()`: at the top of the method, right after `$receiptId = ...` is resolved and before the `switch`, nothing; instead inside `case 3:` and `case 4:` after each `ReceiptsGeneration::where('id',$receiptId)->update([...])` add `TerminationDuesService::touchReceipt($receiptId);`
- `receiptAsPosted()`: after the `->update(['receipts_generation_status'=> 3, ...])` at ~line 1600 add `TerminationDuesService::touchReceipt($receiptId);`
- `destroy()`: after `ReceiptsGeneration::where('id',$id)->delete();` add `TerminationDuesService::touchReceipt($id);` (soft delete — the row still exists, so the lookup works).

- [ ] **Step 3: PDC bounce / exchange hooks**

In `PdcController.php`, after each of the four `ReceiptsGeneration::where('receipts_generation_receipt_no',$isPosted->pdc_receipt_no)->update([... 'receipts_generation_status'=>2 ...]);` statements (lines 385, 416, 486, 494) add:
```php
            \Modules\BackOffice\Services\TerminationDuesService::touchContract($isPosted->tenant_contract_id);
```
Verify the variable: `grep -n "isPosted" Modules/BackOffice/Http/Controllers/PdcController.php | head` — `$isPosted` is the `Pdc` row and `pdc` has `tenant_contract_id` (check with `grep -n tenant_contract_id Modules/BackOffice/Entities/Pdc.php` or the table columns). If the Pdc row lacks it, use `\Modules\BackOffice\Services\TerminationDuesService::touchReceipt(ReceiptsGeneration::where('receipts_generation_receipt_no',$isPosted->pdc_receipt_no)->value('id'));` instead.

- [ ] **Step 4: Deposit refund hooks**

In `DepositRefundController.php`:
- `store()`: right before `session()->flash('success', $receipt ? ...` add `\Modules\BackOffice\Services\TerminationDuesService::touchContract($request['tenant_contract_id']);`
- `update()`: before its redirect add `\Modules\BackOffice\Services\TerminationDuesService::touchDepositRefund($depositRefund->id);`
- `cancelDepositRefundStore()`: **before** `DepositRefund::where('id',$deposit_refund_id)->delete();` read the contract id, and after the delete refresh:
```php
  $duesContractId = DepositRefund::where('id',$deposit_refund_id)->value('tenant_contract_id');
  DepositRefund::where('id',$deposit_refund_id)->delete();
  \Modules\BackOffice\Services\TerminationDuesService::touchContract($duesContractId);
```

- [ ] **Step 5: Syntax-check every touched controller**

Run: `for f in TenantTerminationController RentReceiptGenerationController PdcController DepositRefundController; do $PHP -l Modules/BackOffice/Http/Controllers/$f.php; done`
Expected: `No syntax errors detected` ×4.

- [ ] **Step 6: Manual verification — terminate a contract locally**

In the browser (local app), take a contract that is at the last termination stage (`Termination::where('work_flow_processes_code',504)->latest()->first()`) through to completion, or, if none is convenient, create dues via tinker without rollback for a terminated contract: `(new Modules\BackOffice\Services\TerminationDuesService)->createForTermination(Modules\BackOffice\Entities\Termination::where('work_flow_processes_code',505)->orderBy('id','desc')->first(), 1);`. Then approve a general receipt on that contract and confirm `termination_dues.balance` drops:
`$PHP artisan tinker --execute='print_r(Modules\BackOffice\Entities\TerminationDues::orderBy("id","desc")->first()->toArray());'`

- [ ] **Step 7: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/TenantTerminationController.php Modules/BackOffice/Http/Controllers/RentReceiptGenerationController.php Modules/BackOffice/Http/Controllers/PdcController.php Modules/BackOffice/Http/Controllers/DepositRefundController.php
git commit -m "feat(termination-dues): create dues at final termination; refresh on receipt, PDC and deposit refund changes"
```

---

### Task 7: Rent receipt guard on terminated contracts

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/RentReceiptGenerationController.php` — `store()` after `$this->validate(...)` (line ~94) and `update()` after its validate.

**Interfaces:**
- Consumes: `TerminationDuesService::rentReceiptError($contractId, $amount, $effTo, $ignoreReceiptId = null)`.

- [ ] **Step 1: Add the guard to `store()`**

Immediately after the `$this->validate($request, [...]);` block in `store()`:
```php
        // Terminated contracts: only the open rent dues may be collected, and never for a period after termination.
        $duesError = (new TerminationDuesService)->rentReceiptError(
            $request->tenant_contract_no,
            replaceCommaWithDot($request->receipts_generation_amt),
            $request->receipts_generation_eff_to
        );
        if ($duesError) {
            return redirect()->back()->withInput()->with('error', $duesError);
        }
```

- [ ] **Step 2: Add the guard to `update()`**

Same block after the validate in `update(Request $request, $id)`, passing `$id` as the fourth argument:
```php
        $duesError = (new TerminationDuesService)->rentReceiptError(
            $request->tenant_contract_no,
            replaceCommaWithDot($request->receipts_generation_amt),
            $request->receipts_generation_eff_to,
            $id
        );
        if ($duesError) {
            return redirect()->back()->withInput()->with('error', $duesError);
        }
```
Confirm the rent receipt form shows the `error` flash: `grep -n "session('error')\|Session::get('error')\|has('error')" resources/views/layouts/plms-app.blade.php Modules/BackOffice/Resources/views/Receipt/rent_payment_receipt_create.blade.php | head`. If the layout does not render it, add at the top of the form's `@section('content')`:
```blade
@if(session('error'))
  <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
@endif
```

- [ ] **Step 3: Manual verification**

Local browser: open Rent Receipt → choose a terminated contract that has dues (from Task 6) and enter an amount larger than the rent balance → expect the red message with the exact balance. Enter an amount within the balance and eff-to ≤ termination date → saves. Choose a terminated contract without dues → refused with the "no termination dues on record" message. Choose an active contract → unaffected.

- [ ] **Step 4: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/RentReceiptGenerationController.php Modules/BackOffice/Resources/views/Receipt/rent_payment_receipt_create.blade.php
git commit -m "feat(termination-dues): refuse rent receipts beyond open dues on terminated contracts"
```

---

### Task 8: Menu, permissions, setting, routes, breadcrumbs

**Files:**
- Create: `database/migrations/2026_09_17_000002_add_termination_dues_menu.php`
- Modify: `Modules/BackOffice/Routes/web.php` (inside the `Route::middleware('auth')->group` — add after the `landlord-invoice-v2` lines, ~line 771)
- Modify: `Modules/BackOffice/Routes/breadcrumbs.php` (append after the `landlord-invoice-v2.*` entries, ~line 845)

**Interfaces:**
- Produces: permissions `view_termination_dues_backoffice`, `view_termination_dues_maintenance`; menu `Termination Dues` → route `termination-dues.index`; setting `termination_dues_account_map`; named routes `termination-dues.index`, `termination-dues.show`, `termination-dues.followup`, `termination-dues.allocate`, `termination-dues.waive`, `termination-dues.allocation.destroy`, `terminationDuesSummary`.

- [ ] **Step 1: Migration (menu + permissions + setting)**

Follow the by-name pattern of `database/migrations/2026_09_07_000004_add_landlord_invoice_v2_menu.php`:
```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * "Termination Dues" under PLM Module -> Operations, two team permissions
 * granted to every role (admins can then prune per role), and the
 * account-code -> category map setting used to classify general receipts
 * and deposit-refund deductions.
 */
class AddTerminationDuesMenu extends Migration
{
    const ROUTE = 'termination-dues.index';
    const PERMS = ['view_termination_dues_backoffice', 'view_termination_dues_maintenance'];

    public function up()
    {
        $plmModule = DB::table('menu')->where('menu_name', 'PLM Module')->where('parent_menu', 0)->first();
        $operations = $plmModule
            ? DB::table('menu')->where('menu_name', 'Operations')->where('parent_menu', $plmModule->id)->where('menutype', 1)->first()
            : null;
        $parentMenuId = $operations ? $operations->id : 0;
        $orderNext = DB::table('menu')->where('parent_menu', $parentMenuId)->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Termination Dues',
            'menu_icon'   => 'fa-hand-holding-usd',
            'route_name'  => self::ROUTE,
            'url_key'     => 'termination-dues',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $roles = DB::table('roles')->pluck('id');
        foreach (self::PERMS as $name) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $name, 'guard_name' => 'web', 'menu_id' => $menuId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($roles as $roleId) {
                DB::table('role_has_permissions')->insert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }

        if (!DB::table('configuration')->where('configuration_settings', 'termination_dues_account_map')->exists()) {
            DB::table('configuration')->insert([
                'configuration_settings' => 'termination_dues_account_map',
                'configuration_value'    => json_encode([
                    'rent' => ['12211', '31001'], 'municipal' => ['41102', '41103'],
                    'ew' => ['41105', '22305'], 'maintenance' => ['41110', '22310', '41107', '22307', '12302'],
                ]),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $menu = DB::table('menu')->where('route_name', self::ROUTE)->first();
        foreach (DB::table('permissions')->whereIn('name', self::PERMS)->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        if ($menu) {
            DB::table('menu')->where('id', $menu->id)->delete();
        }
        DB::table('configuration')->where('configuration_settings', 'termination_dues_account_map')->delete();
        app('cache')->forget('spatie.permission.cache');
    }
}
```
Before running, check the `configuration` table's columns match the insert: `$PHP -r '$p=new PDO("pgsql:host=127.0.0.1;dbname=plms_new","postgres","123456"); echo implode(",", $p->query("select column_name from information_schema.columns where table_name=\x27configuration\x27")->fetchAll(PDO::FETCH_COLUMN));'` — if there is no `created_at`/`updated_at`, drop those two keys; if there are NOT NULL columns such as `configuration_year` or `configuration_increment_value`, add them with `0`/`null` as the other rows use (`select * from configuration limit 3`).

- [ ] **Step 2: Run it**

Run: `$PHP artisan migrate --path=database/migrations/2026_09_17_000002_add_termination_dues_menu.php`
Verify: `$PHP artisan tinker --execute='echo DB::table("menu")->where("route_name","termination-dues.index")->count(), " ", DB::table("permissions")->whereIn("name",["view_termination_dues_backoffice","view_termination_dues_maintenance"])->count();'` → `1 2`.

- [ ] **Step 3: Routes**

In `Modules/BackOffice/Routes/web.php`, inside the `Route::middleware('auth')->group(function () {` block, after the `landlord-invoice-v2` routes:
```php
	// Termination dues: what terminated tenants still owe, by team
	Route::get('termination-dues', 'TerminationDuesController@index')->name('termination-dues.index');
	Route::get('termination-dues-summary', 'TerminationDuesController@summary')->name('terminationDuesSummary');
	Route::get('termination-dues/{terminationDues}', 'TerminationDuesController@show')->name('termination-dues.show');
	Route::post('termination-dues/{terminationDues}/followup', 'TerminationDuesController@storeFollowup')->name('termination-dues.followup');
	Route::post('termination-dues/{terminationDues}/allocate', 'TerminationDuesController@storeAllocation')->name('termination-dues.allocate');
	Route::post('termination-dues/{terminationDues}/waive', 'TerminationDuesController@storeWaiver')->name('termination-dues.waive');
	Route::delete('termination-dues/{terminationDues}/allocation/{allocation}', 'TerminationDuesController@destroyAllocation')->name('termination-dues.allocation.destroy');
```
(`termination-dues-summary` is declared before `termination-dues/{terminationDues}` on purpose so it is never captured as an id.)

- [ ] **Step 4: Breadcrumbs**

Append to `Modules/BackOffice/Routes/breadcrumbs.php`:
```php
Breadcrumbs::for('termination-dues.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Termination Dues', route('termination-dues.index'));
});

Breadcrumbs::for('termination-dues.show', function ($trail, $dues) {
    $trail->parent('termination-dues.index');
    $trail->push(optional($dues->tenantContract)->tenant_contract_no ?: ('Dues #' . $dues->id), route('termination-dues.show', $dues->id));
});
```

- [ ] **Step 5: Verify the routes register (controller comes in Task 9, so only check the list)**

Run: `$PHP artisan route:list --name=termination-dues 2>&1 | grep -c termination-dues`
Expected: `7` (route:list only needs the class name to exist as a string; if it errors because the controller class is missing, defer this check to Task 9 Step 6).

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_09_17_000002_add_termination_dues_menu.php Modules/BackOffice/Routes/web.php Modules/BackOffice/Routes/breadcrumbs.php
git commit -m "feat(termination-dues): menu, team permissions, account map setting, routes and breadcrumbs"
```

---

### Task 9: Controller and screens (list, detail, follow-ups, manual allocation, waiver)

**Files:**
- Create: `Modules/BackOffice/Http/Controllers/TerminationDuesController.php`
- Create: `Modules/BackOffice/Resources/views/TerminationDues/index.blade.php`
- Create: `Modules/BackOffice/Resources/views/TerminationDues/show.blade.php`

**Interfaces:**
- Consumes: `TerminationDuesService::refresh()`, `::summaryForContract()`; models from Task 1; `TerminationDuesCategory::label()/teamLabel()`; routes from Task 8; helpers `numberFormat()`.
- Produces: `summary(Request)` JSON used by Task 10's box: `{found: bool, ...summaryForContract()}`.

- [ ] **Step 1: Controller**

```php
<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Entities\TerminationDuesAllocation;
use Modules\BackOffice\Entities\TerminationDuesLine;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;
use Modules\BackOffice\Services\TerminationDuesService;

/**
 * Termination dues: the receivable left behind by a terminated tenant.
 * Back Office follows up rent / tax / E&W / other; Maintenance follows up
 * checklist charges. Balances are recomputed on every page view.
 */
class TerminationDuesController extends Controller
{
    const PER_PAGE = 25;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_termination_dues_backoffice|view_termination_dues_maintenance');
    }

    /** Teams the current user may see, in tab order. */
    private function visibleTeams()
    {
        $user = \Auth::user();
        $teams = [];
        if ($user->can('view_termination_dues_backoffice')) {
            $teams[] = Cat::TEAM_BACKOFFICE;
        }
        if ($user->can('view_termination_dues_maintenance')) {
            $teams[] = Cat::TEAM_MAINTENANCE;
        }
        return $teams;
    }

    public function index(Request $request)
    {
        $teams = $this->visibleTeams();
        $tab = $request->get('team', count($teams) === 1 ? $teams[0] : 'all');
        if ($tab !== 'all' && !in_array($tab, $teams, true)) {
            $tab = count($teams) === 1 ? $teams[0] : 'all';
        }
        $status = $request->get('status', 'outstanding'); // outstanding | open | partial | settled | written_off | all
        $q = trim((string) $request->get('q', ''));

        $balanceColumn = $tab === Cat::TEAM_MAINTENANCE ? 'maintenance_balance' : ($tab === Cat::TEAM_BACKOFFICE ? 'backoffice_balance' : 'balance');

        $query = TerminationDues::with(['tenantContract.tenant', 'tenantContract.building', 'tenantContract.unit']);
        if ($status === 'outstanding') {
            $query->where($balanceColumn, '>', 0);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
            if ($tab !== 'all') {
                $query->where($balanceColumn, '>', 0);
            }
        } elseif ($tab !== 'all') {
            // "all" statuses but a single team: only records that ever had this team's lines
            $query->whereHas('lines', function ($l) use ($tab) { $l->where('owner_team', $tab); });
        }
        if ($q !== '') {
            $query->whereHas('tenantContract', function ($c) use ($q) {
                $c->where('tenant_contract_no', 'ILIKE', '%' . $q . '%')
                  ->orWhereHas('tenant', function ($t) use ($q) {
                      $t->where('tenant_name', 'ILIKE', '%' . $q . '%')->orWhere('tenant_contact_no', 'ILIKE', '%' . $q . '%');
                  });
            });
        }
        $dues = $query->orderBy($balanceColumn, 'desc')->orderBy('termination_date')->paginate(self::PER_PAGE)->appends($request->query());

        $counts = [];
        foreach (array_merge($teams, ['all']) as $t) {
            $col = $t === Cat::TEAM_MAINTENANCE ? 'maintenance_balance' : ($t === Cat::TEAM_BACKOFFICE ? 'backoffice_balance' : 'balance');
            $counts[$t] = TerminationDues::where($col, '>', 0)->count();
        }

        return view('backoffice::TerminationDues.index', compact('dues', 'teams', 'tab', 'status', 'q', 'counts', 'balanceColumn'));
    }

    public function show(TerminationDues $terminationDues)
    {
        $terminationDues->load(['lines', 'followups.creator', 'tenantContract.tenant', 'tenantContract.building', 'tenantContract.unit', 'termination']);
        $result = (new TerminationDuesService)->refresh($terminationDues);
        $manual = TerminationDuesAllocation::with('creator')->whereIn('termination_dues_line_id', $terminationDues->lines->pluck('id'))->orderBy('id')->get();
        $teams = $this->visibleTeams();

        // Which source keys are pinned manually (for the source list)
        $pinnedKeys = [];
        foreach ($manual as $m) {
            if ($m->source_type !== 'waiver') {
                $pinnedKeys[$m->source_type . ':' . $m->source_id] = $m;
            }
        }

        return view('backoffice::TerminationDues.show', [
            'dues'       => $terminationDues,
            'r'          => $result,
            'manual'     => $manual,
            'pinnedKeys' => $pinnedKeys,
            'teams'      => $teams,
            'methods'    => ['call' => 'Phone call', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'visit' => 'Visit', 'other' => 'Other'],
        ]);
    }

    public function storeFollowup(Request $request, TerminationDues $terminationDues)
    {
        $this->validate($request, [
            'owner_team'    => 'required|in:backoffice,maintenance',
            'followup_date' => 'required|date',
            'method'        => 'required|in:call,sms,whatsapp,email,visit,other',
            'note'          => 'nullable|string|max:2000',
            'promise_date'  => 'nullable|date',
        ]);
        $this->authorizeTeam($request->owner_team);

        $terminationDues->followups()->create([
            'owner_team'    => $request->owner_team,
            'followup_date' => $request->followup_date,
            'method'        => $request->method,
            'note'          => $request->note,
            'promise_date'  => $request->promise_date ?: null,
            'created_by'    => \Auth::user()->id,
        ]);
        (new TerminationDuesService)->refresh($terminationDues);

        return redirect()->route('termination-dues.show', $terminationDues->id)->with('success', 'Follow-up recorded.');
    }

    /** Pin an unallocated receipt line / deduction to one of this record's lines. */
    public function storeAllocation(Request $request, TerminationDues $terminationDues)
    {
        $this->validate($request, [
            'line_id'     => 'required|integer',
            'source_type' => 'required|in:rent_receipt,general_receipt_line,deposit_deduction',
            'source_id'   => 'required|integer',
            'amount'      => 'required|numeric|min:0.001',
            'remark'      => 'nullable|string|max:1000',
        ]);
        $line = TerminationDuesLine::where('termination_dues_id', $terminationDues->id)->where('id', $request->line_id)->firstOrFail();
        $this->authorizeTeam($line->owner_team);

        $exists = TerminationDuesAllocation::where('source_type', $request->source_type)->where('source_id', $request->source_id)
            ->whereIn('termination_dues_line_id', $terminationDues->lines()->pluck('id'))->exists();
        if ($exists) {
            return redirect()->route('termination-dues.show', $terminationDues->id)->with('error', 'That receipt line is already assigned. Remove the existing assignment first.');
        }

        TerminationDuesAllocation::create([
            'termination_dues_line_id' => $line->id,
            'source_type'              => $request->source_type,
            'source_id'                => $request->source_id,
            'amount'                   => $request->amount,
            'remark'                   => $request->remark,
            'created_by'               => \Auth::user()->id,
        ]);
        (new TerminationDuesService)->refresh($terminationDues);

        return redirect()->route('termination-dues.show', $terminationDues->id)->with('success', 'Receipt assigned to "' . $line->description . '".');
    }

    public function storeWaiver(Request $request, TerminationDues $terminationDues)
    {
        $this->validate($request, [
            'line_id' => 'required|integer',
            'amount'  => 'required|numeric|min:0.001',
            'remark'  => 'required|string|max:1000',
        ]);
        $line = TerminationDuesLine::where('termination_dues_id', $terminationDues->id)->where('id', $request->line_id)->firstOrFail();
        $this->authorizeTeam($line->owner_team);

        TerminationDuesAllocation::create([
            'termination_dues_line_id' => $line->id,
            'source_type'              => 'waiver',
            'source_id'                => null,
            'amount'                   => $request->amount,
            'remark'                   => $request->remark,
            'created_by'               => \Auth::user()->id,
        ]);
        (new TerminationDuesService)->refresh($terminationDues);

        return redirect()->route('termination-dues.show', $terminationDues->id)->with('success', numberFormat($request->amount) . ' waived on "' . $line->description . '".');
    }

    public function destroyAllocation(TerminationDues $terminationDues, $allocation)
    {
        $alloc = TerminationDuesAllocation::whereIn('termination_dues_line_id', $terminationDues->lines()->pluck('id'))->findOrFail($allocation);
        $this->authorizeTeam($alloc->line->owner_team);
        $alloc->delete();
        (new TerminationDuesService)->refresh($terminationDues);

        return redirect()->route('termination-dues.show', $terminationDues->id)->with('success', 'Assignment removed.');
    }

    /** JSON for the summary box on receipt / deposit refund forms. */
    public function summary(Request $request)
    {
        $contractId = (int) $request->get('contract_id');
        if (!$contractId) {
            return response()->json(['found' => false]);
        }
        $summary = (new TerminationDuesService)->summaryForContract($contractId);
        if (!$summary) {
            return response()->json(['found' => false]);
        }
        foreach ($summary['categories'] as &$c) {
            $c['owed_fmt'] = numberFormat($c['owed']);
            $c['settled_fmt'] = numberFormat($c['settled']);
            $c['balance_fmt'] = numberFormat($c['balance']);
        }
        unset($c);
        $summary['balance_fmt'] = numberFormat($summary['balance']);
        $summary['found'] = true;
        return response()->json($summary);
    }

    private function authorizeTeam($team)
    {
        $permission = $team === Cat::TEAM_MAINTENANCE ? 'view_termination_dues_maintenance' : 'view_termination_dues_backoffice';
        if (!\Auth::user()->can($permission)) {
            abort(403, 'You can only act on ' . Cat::teamLabel($team) . ' dues.');
        }
    }
}
```
Check the spatie middleware alias supports `|` OR: `grep -rn "'permission'" app/Http/Kernel.php` → `\Spatie\Permission\Middlewares\PermissionMiddleware` (pipe-separated permissions are OR in spatie ≥ 2.x). If the installed version is older and does not, use `$this->middleware(function ($request, $next) { if (!\Auth::user()->can('view_termination_dues_backoffice') && !\Auth::user()->can('view_termination_dues_maintenance')) abort(403); return $next($request); });` instead.

- [ ] **Step 2: Index view**

`Modules/BackOffice/Resources/views/TerminationDues/index.blade.php`:
```blade
@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
  /* Tokens (8px grid, neutral surfaces, one accent for the active tab) */
  .tdue { --tdue-ink:#0f172a; --tdue-muted:#475569; --tdue-line:#e2e8f0; --tdue-fill:#f1f5f9; --tdue-primary:#2f4fd6; --tdue-accent:#FF9800; --tdue-danger:#dc2626; --tdue-success:#16a34a; --tdue-warn:#d97706; }
  .tdue .tdue-tabs { display:flex; flex-wrap:wrap; gap:8px; margin:0 0 16px; padding:0; list-style:none; }
  .tdue .tdue-tabs a { display:inline-flex; align-items:center; gap:8px; min-height:40px; padding:0 16px; border:1px solid var(--tdue-line); border-radius:8px; color:var(--tdue-ink); text-decoration:none; }
  .tdue .tdue-tabs a:hover, .tdue .tdue-tabs a:focus-visible { background:#fff3e0; color:#e65100; outline:2px solid var(--tdue-accent); outline-offset:2px; }
  .tdue .tdue-tabs a.active { background:var(--tdue-accent); border-color:var(--tdue-accent); color:#fff; }
  .tdue .tdue-count { min-width:24px; padding:2px 8px; border-radius:999px; background:var(--tdue-fill); color:var(--tdue-muted); font-size:12px; font-weight:600; text-align:center; }
  .tdue .tdue-tabs a.active .tdue-count { background:#fff; color:#e65100; }
  .tdue .tdue-filters { display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end; margin-bottom:24px; }
  .tdue .tdue-filters label { display:block; font-size:14px; font-weight:500; margin-bottom:8px; color:var(--tdue-ink); }
  .tdue .tdue-filters .form-control { height:40px; min-width:200px; }
  .tdue table.product-overview th { font-size:12px; font-weight:500; text-transform:uppercase; letter-spacing:.04em; color:var(--tdue-muted); white-space:nowrap; }
  .tdue table.product-overview td { padding:12px 16px; vertical-align:middle; }
  .tdue td.num, .tdue th.num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
  .tdue td.num.bal { font-weight:600; }
  .tdue .tdue-status { display:inline-block; min-width:80px; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.03em; text-align:center; }
  .tdue .tdue-status-open { background:#fee2e2; color:#991b1b; }
  .tdue .tdue-status-partial { background:#fef3c7; color:#92400e; }
  .tdue .tdue-status-settled { background:#dcfce7; color:#166534; }
  .tdue .tdue-status-written_off { background:var(--tdue-fill); color:var(--tdue-muted); }
  .tdue .tdue-sub { display:block; font-size:12px; color:var(--tdue-muted); }
  .tdue .tdue-empty { padding:32px 16px; text-align:center; color:var(--tdue-muted); }
  .tdue .tdue-overdue { color:var(--tdue-danger); }
  @media (max-width:600px){ .tdue .tdue-filters .form-control{ min-width:100%; } }
</style>
@endsection

@section('content')
@php $teamLabels = ['backoffice' => 'Back Office', 'maintenance' => 'Maintenance', 'all' => 'All teams']; @endphp
<div class="row page-titles">
  <div class="col-md-6 align-self-center"><h3 class="text-themecolor">Termination Dues</h3></div>
  <div class="col-md-6 align-self-center text-right">{{ Breadcrumbs::render('termination-dues.index') }}</div>
</div>

<div class="card tdue">
  <div class="card-body">
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif

    <ul class="tdue-tabs" role="tablist">
      @foreach(array_merge($teams, count($teams) > 1 ? ['all'] : []) as $t)
        <li><a href="{{ route('termination-dues.index', array_merge(request()->except('page'), ['team' => $t])) }}" class="{{ $tab === $t ? 'active' : '' }}" @if($tab === $t) aria-current="page" @endif>
          {{ $teamLabels[$t] }} <span class="tdue-count">{{ $counts[$t] }}</span></a></li>
      @endforeach
    </ul>

    <form method="get" class="tdue-filters" action="{{ route('termination-dues.index') }}">
      <input type="hidden" name="team" value="{{ $tab }}">
      <div>
        <label for="tdue-status">Show</label>
        <select id="tdue-status" name="status" class="form-control">
          @foreach(['outstanding' => 'With balance', 'open' => 'Open (nothing paid)', 'partial' => 'Partially paid', 'settled' => 'Settled', 'written_off' => 'Written off', 'all' => 'Everything'] as $k => $label)
            <option value="{{ $k }}" {{ $status === $k ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="tdue-q">Contract, tenant or mobile</label>
        <input id="tdue-q" type="search" name="q" class="form-control" value="{{ $q }}" placeholder="TAG25… or name">
      </div>
      <div><button type="submit" class="btn btn-primary" style="height:40px">Apply filters</button></div>
    </form>

    <div class="table-responsive">
      <table class="table product-overview">
        <thead><tr>
          <th>Contract</th><th>Tenant</th><th>Building / unit</th><th>Terminated</th>
          <th class="num">Owed</th><th class="num">Settled</th><th class="num">Balance{{ $tab !== 'all' ? ' (' . $teamLabels[$tab] . ')' : '' }}</th>
          <th>Status</th><th>Last follow-up</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($dues as $d)
          @php
            $c = $d->tenantContract;
            $days = $d->termination_date ? $d->termination_date->diffInDays(now()) : null;
            $promiseOverdue = $d->next_promise_date && $d->next_promise_date->isPast() && $d->{$balanceColumn} > 0;
          @endphp
          <tr>
            <td><a href="{{ route('termination-dues.show', $d->id) }}">{{ optional($c)->tenant_contract_no ?: '#' . $d->tenant_contract_id }}</a></td>
            <td>{{ optional(optional($c)->tenant)->tenant_name }}<span class="tdue-sub">{{ optional(optional($c)->tenant)->tenant_contact_no }}</span></td>
            <td>{{ optional(optional($c)->building)->building_name }}<span class="tdue-sub">{{ optional(optional($c)->unit)->unit_code }}</span></td>
            <td>{{ $d->termination_date ? $d->termination_date->format('d/m/Y') : '—' }}@if($days !== null)<span class="tdue-sub">{{ $days }} days ago</span>@endif</td>
            <td class="num">{{ numberFormat($d->total_owed) }}</td>
            <td class="num">{{ numberFormat($d->total_settled) }}</td>
            <td class="num bal">{{ numberFormat($d->{$balanceColumn}) }}</td>
            <td><span class="tdue-status tdue-status-{{ $d->status }}">{{ str_replace('_', ' ', $d->status) }}</span></td>
            <td>{{ $d->last_followup_at ? $d->last_followup_at->format('d/m/Y') : '—' }}
              @if($d->next_promise_date)<span class="tdue-sub {{ $promiseOverdue ? 'tdue-overdue' : '' }}">promised {{ $d->next_promise_date->format('d/m/Y') }}</span>@endif</td>
            <td><a class="btn btn-sm btn-outline-secondary" href="{{ route('termination-dues.show', $d->id) }}">Open</a></td>
          </tr>
        @empty
          <tr><td colspan="10" class="tdue-empty">No termination dues match these filters.<br><small>Dues appear here automatically when a termination completes with an unpaid balance.</small></td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    {{ $dues->links() }}
  </div>
</div>
@endsection
```

- [ ] **Step 3: Detail view**

`Modules/BackOffice/Resources/views/TerminationDues/show.blade.php`:
```blade
@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
  .tdue { --tdue-ink:#0f172a; --tdue-muted:#475569; --tdue-line:#e2e8f0; --tdue-fill:#f1f5f9; --tdue-accent:#FF9800; --tdue-danger:#dc2626; --tdue-success:#16a34a; }
  .tdue .tdue-head { display:flex; flex-wrap:wrap; gap:24px; align-items:flex-start; justify-content:space-between; margin-bottom:24px; }
  .tdue .tdue-meta { display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:16px; }
  .tdue .tdue-meta .k { display:block; font-size:12px; font-weight:500; text-transform:uppercase; letter-spacing:.04em; color:var(--tdue-muted); margin-bottom:4px; }
  .tdue .tdue-meta .v { font-size:16px; color:var(--tdue-ink); }
  .tdue .tdue-balance { text-align:right; }
  .tdue .tdue-balance .amt { font-size:31px; font-weight:600; line-height:1.2; font-variant-numeric:tabular-nums; }
  .tdue .tdue-balance .amt.zero { color:var(--tdue-success); }
  .tdue h4 { font-size:20px; font-weight:600; margin:32px 0 16px; }
  .tdue table.product-overview th { font-size:12px; font-weight:500; text-transform:uppercase; letter-spacing:.04em; color:var(--tdue-muted); white-space:nowrap; }
  .tdue table.product-overview td { padding:12px 16px; vertical-align:middle; }
  .tdue td.num, .tdue th.num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
  .tdue tr.team-row td { background:var(--tdue-fill); font-weight:600; }
  .tdue tr.total-row td { border-top:2px solid var(--tdue-ink); font-weight:600; }
  .tdue .tdue-status { display:inline-block; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:600; text-transform:uppercase; }
  .tdue .tdue-status-open { background:#fee2e2; color:#991b1b; } .tdue .tdue-status-partial { background:#fef3c7; color:#92400e; }
  .tdue .tdue-status-settled { background:#dcfce7; color:#166534; } .tdue .tdue-status-written_off { background:var(--tdue-fill); color:var(--tdue-muted); }
  .tdue .tdue-actions { display:flex; flex-wrap:wrap; gap:8px; }
  .tdue .tdue-actions .btn { height:40px; display:inline-flex; align-items:center; }
  .tdue .tdue-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px; }
  .tdue .tdue-panel { border:1px solid var(--tdue-line); border-radius:8px; padding:24px; }
  .tdue .tdue-panel h5 { font-size:16px; font-weight:600; margin:0 0 16px; }
  .tdue form .form-group { margin-bottom:16px; } .tdue form label { font-size:14px; font-weight:500; margin-bottom:8px; }
  .tdue form .form-control { height:40px; } .tdue form textarea.form-control { height:auto; }
  .tdue .tdue-log { list-style:none; margin:0; padding:0; } .tdue .tdue-log li { padding:12px 0; border-bottom:1px solid var(--tdue-line); }
  .tdue .tdue-log .who { font-size:12px; color:var(--tdue-muted); }
  .tdue .tdue-inline { display:inline; }
  .tdue .tdue-muted { color:var(--tdue-muted); font-size:14px; }
  .tdue .btn:focus-visible, .tdue a:focus-visible { outline:2px solid var(--tdue-accent); outline-offset:2px; }
</style>
@endsection

@section('content')
@php
  $c = $dues->tenantContract;
  $teamLabels = ['backoffice' => 'Back Office', 'maintenance' => 'Maintenance'];
  $canTeam = function ($team) use ($teams) { return in_array($team, $teams, true); };
  $byTeam = ['backoffice' => [], 'maintenance' => []];
  foreach ($r['lines'] as $l) { $byTeam[isset($byTeam[$l['owner_team']]) ? $l['owner_team'] : 'backoffice'][] = $l; }
  $kindLabel = ['rent_receipt' => 'Rent receipt', 'general_receipt_line' => 'General receipt', 'deposit_deduction' => 'Deposit deduction'];
  $lineById = $dues->lines->keyBy('id');
@endphp
<div class="row page-titles">
  <div class="col-md-6 align-self-center"><h3 class="text-themecolor">Termination dues · {{ optional($c)->tenant_contract_no }}</h3></div>
  <div class="col-md-6 align-self-center text-right">{{ Breadcrumbs::render('termination-dues.show', $dues) }}</div>
</div>

<div class="card tdue"><div class="card-body">
  @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

  <div class="tdue-head">
    <div class="tdue-meta">
      <div><span class="k">Tenant</span><span class="v">{{ optional(optional($c)->tenant)->tenant_name }}<br><small>{{ optional(optional($c)->tenant)->tenant_contact_no }}</small></span></div>
      <div><span class="k">Building / unit</span><span class="v">{{ optional(optional($c)->building)->building_name }}<br><small>{{ optional(optional($c)->unit)->unit_code }}</small></span></div>
      <div><span class="k">Terminated on</span><span class="v">{{ $dues->termination_date ? $dues->termination_date->format('d/m/Y') : '—' }}<br><small>{{ $dues->termination_date ? $dues->termination_date->diffInDays(now()) . ' days ago' : '' }}</small></span></div>
      <div><span class="k">Status</span><span class="v"><span class="tdue-status tdue-status-{{ $r['status'] }}">{{ str_replace('_', ' ', $r['status']) }}</span></span></div>
    </div>
    <div class="tdue-balance">
      <span class="k tdue-muted">Balance outstanding (OMR)</span>
      <div class="amt {{ $r['total']['balance'] <= 0.005 ? 'zero' : '' }}">{{ numberFormat($r['total']['balance']) }}</div>
      <div class="tdue-muted">Back Office {{ numberFormat($r['teams']['backoffice']['balance']) }} · Maintenance {{ numberFormat($r['teams']['maintenance']['balance']) }}</div>
    </div>
  </div>

  <div class="tdue-actions">
    <a class="btn btn-primary" href="{{ route('rentReceiptGeneration.create') }}?contract_id={{ $dues->tenant_contract_id }}">Collect rent</a>
    <a class="btn btn-outline-secondary" href="{{ route('addGeneralReceipt') }}?contract_id={{ $dues->tenant_contract_id }}">Collect other charges</a>
    <a class="btn btn-outline-secondary" href="{{ route('depositRefund.create') }}?contract_id={{ $dues->tenant_contract_id }}">Deposit refund</a>
  </div>

  <h4>Settlement</h4>
  <div class="table-responsive"><table class="table product-overview">
    <thead><tr><th>Line</th><th class="num">Owed</th><th class="num">Deposit deduction</th><th class="num">Receipts</th><th class="num">Waived</th><th class="num">Balance</th></tr></thead>
    <tbody>
    @foreach(['backoffice', 'maintenance'] as $team)
      @if(count($byTeam[$team]))
        <tr class="team-row"><td>{{ $teamLabels[$team] }}</td>
          <td class="num">{{ numberFormat($r['teams'][$team]['owed']) }}</td><td class="num" colspan="2">{{ numberFormat($r['teams'][$team]['settled']) }} settled</td><td></td>
          <td class="num">{{ numberFormat($r['teams'][$team]['balance']) }}</td></tr>
        @foreach($byTeam[$team] as $l)
          <tr><td>&nbsp;&nbsp;{{ $l['description'] }} <span class="tdue-muted">· {{ \Modules\BackOffice\Services\TerminationDuesCategory::label($l['category']) }}</span></td>
            <td class="num">{{ numberFormat($l['owed']) }}</td><td class="num">{{ numberFormat($l['deposit']) }}</td><td class="num">{{ numberFormat($l['receipts']) }}</td>
            <td class="num">{{ numberFormat($l['waived']) }}</td><td class="num">{{ numberFormat($l['balance']) }}</td></tr>
        @endforeach
      @endif
    @endforeach
    <tr class="total-row"><td>Total</td><td class="num">{{ numberFormat($r['total']['owed']) }}</td><td class="num" colspan="2">{{ numberFormat($r['total']['settled']) }}</td>
      <td class="num">{{ numberFormat($r['total']['waived']) }}</td><td class="num">{{ numberFormat($r['total']['balance']) }}</td></tr>
    </tbody></table></div>
  @foreach($r['over_collected'] as $cat => $amt)
    <p class="tdue-muted">Over-collected on {{ \Modules\BackOffice\Services\TerminationDuesCategory::label($cat) }}: {{ numberFormat($amt) }} — finance may need to refund or re-assign.</p>
  @endforeach

  <h4>What has been received</h4>
  <div class="table-responsive"><table class="table product-overview">
    <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>Description</th><th>Account</th><th class="num">Amount</th><th>Applied to</th></tr></thead>
    <tbody>
    @forelse($r['sources'] as $s)
      @php $applied = array_values(array_filter($r['allocations'], function ($a) use ($s) { return $a['source_key'] === $s['key']; })); @endphp
      <tr><td>{{ $s['date'] ? date('d/m/Y', strtotime($s['date'])) : '—' }}</td><td>{{ $kindLabel[$s['kind']] }}</td>
        <td><a href="{{ $s['url'] }}">{{ $s['ref'] }}</a></td><td>{{ $s['description'] }}</td><td>{{ $s['account_code'] }}</td>
        <td class="num">{{ numberFormat($s['amount']) }}</td>
        <td>@forelse($applied as $a){{ $lineById[$a['line_id']]->description }} ({{ numberFormat($a['amount']) }}){{ $a['manual'] ? ' · manual' : '' }}@if(!$loop->last), @endif
            @empty <span class="tdue-muted">unallocated</span> @endforelse</td></tr>
    @empty
      <tr><td colspan="7" class="tdue-muted">Nothing received yet.</td></tr>
    @endforelse
    </tbody></table></div>
  @if(count($r['pending']))
    <p class="tdue-muted">Awaiting approval (not counted yet): @foreach($r['pending'] as $p)<a href="{{ url('rentReceiptGeneration/' . $p->id) }}">{{ $p->receipts_generation_receipt_no }}</a> {{ numberFormat($p->receipts_generation_amt) }}@if(!$loop->last), @endif @endforeach</p>
  @endif

  @if(count($r['unallocated']))
    <h4>Needs your decision</h4>
    <p class="tdue-muted">These payments could not be matched to a category by account code. Assign each to the line it settles.</p>
    @foreach($r['unallocated'] as $s)
      <form method="post" action="{{ route('termination-dues.allocate', $dues->id) }}" class="form-inline" style="gap:8px; margin-bottom:8px; flex-wrap:wrap">
        @csrf
        <input type="hidden" name="source_type" value="{{ $s['kind'] }}"><input type="hidden" name="source_id" value="{{ $s['id'] }}">
        <span><a href="{{ $s['url'] }}">{{ $s['ref'] }}</a> · {{ $s['description'] ?: $kindLabel[$s['kind']] }} · acc {{ $s['account_code'] ?: '—' }} · <strong>{{ numberFormat($s['amount']) }}</strong></span>
        <label class="sr-only" for="line-{{ $s['id'] }}">Assign to line</label>
        <select id="line-{{ $s['id'] }}" name="line_id" class="form-control" required>
          @foreach($dues->lines as $line)@if($canTeam($line->owner_team))<option value="{{ $line->id }}">{{ $line->description }} ({{ $teamLabels[$line->owner_team] }})</option>@endif @endforeach
        </select>
        <input type="number" step="0.001" min="0.001" name="amount" class="form-control" value="{{ $s['amount'] }}" aria-label="Amount to apply" required>
        <input type="text" name="remark" class="form-control" placeholder="Why (optional)" aria-label="Remark">
        <button type="submit" class="btn btn-outline-primary">Assign</button>
      </form>
    @endforeach
  @endif

  <div class="tdue-grid" style="margin-top:32px">
    <section class="tdue-panel">
      <h5>Follow-up log</h5>
      <form method="post" action="{{ route('termination-dues.followup', $dues->id) }}">
        @csrf
        <div class="form-group"><label for="fu-team">Team</label>
          <select id="fu-team" name="owner_team" class="form-control" required>@foreach($teams as $t)<option value="{{ $t }}">{{ $teamLabels[$t] }}</option>@endforeach</select></div>
        <div class="form-row">
          <div class="form-group col-sm-6"><label for="fu-date">Date</label><input id="fu-date" type="date" name="followup_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
          <div class="form-group col-sm-6"><label for="fu-method">How</label>
            <select id="fu-method" name="method" class="form-control" required>@foreach($methods as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
        </div>
        <div class="form-group"><label for="fu-note">What was said</label><textarea id="fu-note" name="note" class="form-control" rows="2" maxlength="2000"></textarea></div>
        <div class="form-group"><label for="fu-promise">Tenant promised to pay by</label><input id="fu-promise" type="date" name="promise_date" class="form-control"></div>
        <button type="submit" class="btn btn-primary">Record follow-up</button>
      </form>
      <ul class="tdue-log" style="margin-top:24px">
        @forelse($dues->followups as $f)
          <li><strong>{{ $f->followup_date->format('d/m/Y') }}</strong> · {{ $methods[$f->method] ?? $f->method }} · {{ $teamLabels[$f->owner_team] ?? $f->owner_team }}
            @if($f->promise_date)<span class="tdue-muted"> · promised {{ $f->promise_date->format('d/m/Y') }}</span>@endif
            <div>{{ $f->note }}</div>
            <div class="who">{{ optional($f->creator)->username }} · {{ $f->created_at->format('d/m/Y H:i') }}</div></li>
        @empty
          <li class="tdue-muted">No follow-ups recorded yet.</li>
        @endforelse
      </ul>
    </section>

    <section class="tdue-panel">
      <h5>Manual decisions</h5>
      <ul class="tdue-log">
        @forelse($manual as $m)
          <li>{{ $m->source_type === 'waiver' ? 'Waived' : 'Assigned ' . ($kindLabel[$m->source_type] ?? $m->source_type) . ' #' . $m->source_id }} {{ numberFormat($m->amount) }} → {{ optional($lineById->get($m->termination_dues_line_id))->description }}
            @if($m->remark)<div class="tdue-muted">{{ $m->remark }}</div>@endif
            <div class="who">{{ optional($m->creator)->username }} · {{ $m->created_at->format('d/m/Y') }}
              @if($canTeam(optional($lineById->get($m->termination_dues_line_id))->owner_team))
              <form method="post" action="{{ route('termination-dues.allocation.destroy', [$dues->id, $m->id]) }}" class="tdue-inline" onsubmit="return confirm('Remove this decision? The balance will be recalculated.');">
                @csrf @method('DELETE')<button type="submit" class="btn btn-link btn-sm">Remove</button></form>
              @endif</div></li>
        @empty
          <li class="tdue-muted">None.</li>
        @endforelse
      </ul>
      <h5 style="margin-top:24px">Waive an amount</h5>
      <form method="post" action="{{ route('termination-dues.waive', $dues->id) }}">
        @csrf
        <div class="form-group"><label for="wv-line">Line</label>
          <select id="wv-line" name="line_id" class="form-control" required>@foreach($dues->lines as $line)@if($canTeam($line->owner_team) && $r['lines'][$line->id]['balance'] > 0)<option value="{{ $line->id }}">{{ $line->description }} — balance {{ numberFormat($r['lines'][$line->id]['balance']) }}</option>@endif @endforeach</select></div>
        <div class="form-group"><label for="wv-amt">Amount</label><input id="wv-amt" type="number" step="0.001" min="0.001" name="amount" class="form-control" required></div>
        <div class="form-group"><label for="wv-remark">Approval / reason</label><input id="wv-remark" type="text" name="remark" class="form-control" maxlength="1000" required placeholder="e.g. approved by Finance Manager on 17/09"></div>
        <button type="submit" class="btn btn-outline-danger">Waive</button>
      </form>
    </section>
  </div>
</div></div>
@endsection
```
Note on the `Collect` links: the receipt forms ignore `?contract_id` until Task 10 adds the summary box; the link is still the right destination. The `onsubmit="return confirm(...)"` on "Remove" is a native confirm — when testing with browser automation do not click it.

Check `depositRefund.create` and `rentReceiptGeneration.create` route names exist: `$PHP artisan route:list 2>/dev/null | grep -E "depositRefund.create|rentReceiptGeneration.create|addGeneralReceipt"`. If `depositRefund.create` is missing, use the name shown for `DepositRefundController@create`.

- [ ] **Step 4: Verify routes + pages**

Run: `$PHP artisan route:list --name=termination-dues 2>&1 | grep -c termination-dues` → `7`.
Browser (local, logged in as a user whose role has both permissions): open `/termination-dues` → tabs Back Office / Maintenance / All with counts; open a record → matrix, sources, follow-up form. Record a follow-up → appears in the log and `last_followup_at` shows on the list. If a source is unallocated, assign it → balance moves. Waive 1 OMR with a reason → `waived` column and status update; remove the decision → reverts.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/TerminationDuesController.php Modules/BackOffice/Resources/views/TerminationDues/
git commit -m "feat(termination-dues): list, detail, follow-ups, manual allocation and waiver screens"
```

---

### Task 10: Dues summary box on the rent receipt, general receipt and deposit refund forms

**Files:**
- Create: `Modules/BackOffice/Resources/views/TerminationDues/_summary_box.blade.php`
- Modify: `Modules/BackOffice/Resources/views/Receipt/rent_payment_receipt_create.blade.php` (inside `@section('content')`, directly above the agreement-number field at ~line 100; and at the end of `@section('scripts')` before `@endsection` at ~line 1215)
- Modify: `Modules/BackOffice/Resources/views/Receipt/general_receipt_form.blade.php` (same two spots: ~line 97 and before `@endsection` at ~line 1163)
- Modify: `Modules/BackOffice/Resources/views/Transaction/add_deposit_refund.blade.php` (above the hidden `tenant_contract_id` input ~line 182; and before the final `@endsection` ~line 1001)

**Interfaces:**
- Consumes: `route('terminationDuesSummary')` JSON from Task 9 (`found`, `url`, `status`, `termination_date`, `balance_fmt`, `categories[]{label, owner_team, owed_fmt, settled_fmt, balance_fmt}`).

- [ ] **Step 1: Partial**

`Modules/BackOffice/Resources/views/TerminationDues/_summary_box.blade.php` — takes `$selector` (jQuery selector of the element holding the contract id) and optional `$eventTargets` (selectors whose `change` should re-fetch):
```blade
{{-- Read-only dues summary for the contract chosen on this form.
     Usage: @include('backoffice::TerminationDues._summary_box', ['selector' => '#tenant_contract_no']) inside content,
            and @include('backoffice::TerminationDues._summary_box_js', ...) is NOT needed: this file emits both markup and script. --}}
<div id="tdue-box" class="alert" role="status" style="display:none; border:1px solid #fcd34d; background:#fffbeb; color:#0f172a; border-radius:8px; padding:16px; margin-bottom:24px">
  <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:baseline">
    <strong>Terminated contract — dues outstanding: <span id="tdue-box-balance"></span> OMR</strong>
    <a id="tdue-box-link" href="#" target="_blank" rel="noopener">Open termination dues</a>
  </div>
  <div id="tdue-box-meta" style="font-size:14px; color:#475569; margin:8px 0"></div>
  <table style="width:100%; font-size:14px; font-variant-numeric:tabular-nums">
    <thead><tr><th style="text-align:left; padding:4px 8px">Category</th><th style="text-align:left; padding:4px 8px">Team</th><th style="text-align:right; padding:4px 8px">Owed</th><th style="text-align:right; padding:4px 8px">Settled</th><th style="text-align:right; padding:4px 8px">Balance</th></tr></thead>
    <tbody id="tdue-box-rows"></tbody>
  </table>
</div>
<script>
(function () {
  var selector = @json($selector);
  var url = @json(route('terminationDuesSummary'));
  var lastId = null;
  function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }
  function render(d) {
    var $box = $('#tdue-box');
    if (!d || !d.found) { $box.hide(); return; }
    $('#tdue-box-balance').text(d.balance_fmt);
    $('#tdue-box-link').attr('href', d.url);
    $('#tdue-box-meta').text('Terminated ' + (d.termination_date || '') + ' · status ' + String(d.status).replace('_', ' ') + '. Collect only what is listed here; do not create receipts for money not yet received.');
    var rows = '';
    $.each(d.categories || [], function (_, c) {
      rows += '<tr><td style="padding:4px 8px">' + esc(c.label) + '</td><td style="padding:4px 8px">' + (c.owner_team === 'maintenance' ? 'Maintenance' : 'Back Office') + '</td>'
            + '<td style="text-align:right; padding:4px 8px">' + esc(c.owed_fmt) + '</td><td style="text-align:right; padding:4px 8px">' + esc(c.settled_fmt) + '</td>'
            + '<td style="text-align:right; padding:4px 8px; font-weight:600">' + esc(c.balance_fmt) + '</td></tr>';
    });
    $('#tdue-box-rows').html(rows);
    $box.show();
  }
  function refresh() {
    var id = $(selector).val();
    if (!id || id === lastId) { if (!id) { $('#tdue-box').hide(); lastId = null; } return; }
    lastId = id;
    $.getJSON(url, { contract_id: id }).done(render).fail(function () { $('#tdue-box').hide(); });
  }
  $(document).on('change', selector, refresh);
  // Hidden inputs do not fire change when set by script: poll cheaply.
  setInterval(refresh, 1500);
  $(function () {
    var pre = @json(request()->get('contract_id'));
    if (pre && !$(selector).val()) {
      // Collect links from the dues page: fetch the summary straight away even before the agreement is picked.
      $.getJSON(url, { contract_id: pre }).done(render);
    }
    refresh();
  });
})();
</script>
```

- [ ] **Step 2: Include it on the three forms**

Rent receipt create (`rent_payment_receipt_create.blade.php`), just above the row containing `<label for="tenant_contract_no">Agreement No`:
```blade
@include('backoffice::TerminationDues._summary_box', ['selector' => '#tenant_contract_no'])
```
General receipt form (`general_receipt_form.blade.php`), same position above its `Agreement No` label:
```blade
@include('backoffice::TerminationDues._summary_box', ['selector' => '#tenant_contract_no'])
```
Deposit refund (`add_deposit_refund.blade.php`), just above the hidden `#tenant_contract_id` input:
```blade
@include('backoffice::TerminationDues._summary_box', ['selector' => '#tenant_contract_id'])
```
The partial emits its own `<script>` inline; jQuery is loaded by the layout before the content section is rendered? Verify: `grep -n "jquery" resources/views/layouts/plms-app.blade.php | head -3`. If jQuery is only included at the bottom of the layout (after content), wrap the partial's script in `document.addEventListener('DOMContentLoaded', function(){ ... })` or move the `@include` into each form's `@section('scripts')` and keep only the markup in content — simplest: split the partial into `_summary_box.blade.php` (markup) and `_summary_box_js.blade.php` (script), include the JS one inside each form's `@section('scripts')` before `@endsection`.

- [ ] **Step 3: Verify in the browser**

Rent receipt page: pick a terminated contract with dues → the amber box appears with per-category balances and a link; pick an active contract → box hides. Same on the general receipt and deposit refund pages. Open `/termination-dues/{id}` → "Collect rent" → the box appears immediately from `?contract_id`.

- [ ] **Step 4: Commit**

```bash
git add Modules/BackOffice/Resources/views/TerminationDues/_summary_box*.blade.php Modules/BackOffice/Resources/views/Receipt/rent_payment_receipt_create.blade.php Modules/BackOffice/Resources/views/Receipt/general_receipt_form.blade.php Modules/BackOffice/Resources/views/Transaction/add_deposit_refund.blade.php
git commit -m "feat(termination-dues): dues summary box on receipt and deposit refund forms"
```

---

### Task 11: Backfill and refresh commands

**Files:**
- Create: `Modules/BackOffice/Console/TerminationDuesBackfill.php`
- Create: `Modules/BackOffice/Console/TerminationDuesRefresh.php`
- Modify: `app/Console/Kernel.php:16-18` (`$commands` array) and `schedule()`

**Interfaces:**
- Consumes: `TerminationDuesService::buildInputFor()`, `::createForTermination()`, `::refresh()`; `TerminationDuesBuilder::build()`.
- Produces: `termination-dues:backfill [--apply] [--since=YYYY-MM-DD] [--contract=TAG...]`, `termination-dues:refresh`.

- [ ] **Step 1: Backfill command**

```php
<?php

namespace Modules\BackOffice\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Services\TerminationDuesBuilder;
use Modules\BackOffice\Services\TerminationDuesService;

/**
 * Creates dues records for contracts that were terminated before this
 * feature existed and still show an unpaid balance. Dry-run by default:
 * prints what it would create; --apply writes. Contracts whose computed
 * balance is already zero (paid, or "paid" via the old receipt-as-IOU
 * practice) are skipped and counted so the noise stays out of the list.
 */
class TerminationDuesBackfill extends Command
{
    protected $signature = 'termination-dues:backfill {--apply : Write the records (default is a dry run)} {--since= : Only terminations on/after this date (Y-m-d)} {--contract= : Single tenant_contract_no}';
    protected $description = 'Create termination dues for already-terminated contracts with an unpaid balance';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $svc = new TerminationDuesService();

        $q = Termination::where('work_flow_processes_code', TerminationDuesService::FINAL_STAGE)
            ->whereIn('contract_id', function ($s) {
                $s->select('id')->from('tenant_contracts')->where('tenant_renewal_termination_status', 8);
            })
            ->whereNotIn('contract_id', function ($s) { $s->select('tenant_contract_id')->from('termination_dues'); })
            ->orderBy('contract_id')->orderBy('id');
        if ($this->option('since')) {
            $q->whereDate('termination_date', '>=', $this->option('since'));
        }
        if ($this->option('contract')) {
            $q->whereIn('contract_id', function ($s) { $s->select('id')->from('tenant_contracts')->where('tenant_contract_no', $this->option('contract')); });
        }

        $seen = [];
        $created = 0; $skippedZero = 0; $skippedNothing = 0; $failed = 0;
        $rows = [];
        foreach ($q->get() as $final) {
            if (isset($seen[$final->contract_id])) { continue; }
            $seen[$final->contract_id] = true;
            try {
                $lines = TerminationDuesBuilder::build($svc->buildInputFor($final->contract_id, $final));
                if (empty($lines)) { $skippedNothing++; continue; }

                DB::beginTransaction();
                $dues = $svc->createForTermination($final, null);
                if (!$dues) { DB::rollBack(); $skippedNothing++; continue; }
                $r = $svc->refresh($dues);
                if ($r['total']['balance'] <= 0.005) {
                    DB::rollBack(); $skippedZero++; continue;
                }
                $rows[] = [$final->contract_id, DB::table('tenant_contracts')->where('id', $final->contract_id)->value('tenant_contract_no'), (string) $final->termination_date, number_format($r['total']['owed'], 3), number_format($r['total']['balance'], 3), $r['status']];
                if ($apply) { DB::commit(); $created++; } else { DB::rollBack(); $created++; }
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                $this->warn('contract ' . $final->contract_id . ': ' . $e->getMessage());
            }
        }

        $this->table(['contract_id', 'contract_no', 'termination_date', 'owed', 'balance', 'status'], array_slice($rows, 0, 200));
        if (count($rows) > 200) { $this->line('... ' . (count($rows) - 200) . ' more'); }
        $this->info(($apply ? 'Created ' : 'Would create ') . $created . ' dues record(s); skipped ' . $skippedZero . ' already settled, ' . $skippedNothing . ' with nothing owed; ' . $failed . ' failed.');
        if (!$apply) { $this->comment('Dry run. Re-run with --apply to write.'); }
        return 0;
    }
}
```

- [ ] **Step 2: Refresh command**

```php
<?php

namespace Modules\BackOffice\Console;

use Illuminate\Console\Command;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Services\TerminationDuesService;

/** Recomputes stored balances/status for every dues record (nightly safety net for the hooks). */
class TerminationDuesRefresh extends Command
{
    protected $signature = 'termination-dues:refresh {--open-only : Only records that still have a balance}';
    protected $description = 'Recompute termination dues balances from live receipts and deposit refunds';

    public function handle()
    {
        $svc = new TerminationDuesService();
        $q = TerminationDues::orderBy('id');
        if ($this->option('open-only')) { $q->where('balance', '>', 0); }
        $n = 0;
        $q->chunk(200, function ($chunk) use ($svc, &$n) {
            foreach ($chunk as $dues) { $svc->refresh($dues); $n++; }
        });
        $this->info('Refreshed ' . $n . ' dues record(s).');
        return 0;
    }
}
```

- [ ] **Step 3: Register and schedule**

In `app/Console/Kernel.php`:
```php
    protected $commands = [
         Commands\NotificationCommand::class,
         \Modules\BackOffice\Console\TerminationDuesBackfill::class,
         \Modules\BackOffice\Console\TerminationDuesRefresh::class,
    ];
```
and in `schedule()` after the existing line:
```php
       $schedule->command('termination-dues:refresh --open-only')->dailyAt('01:30');
```

- [ ] **Step 4: Dry-run against local data**

Run: `$PHP artisan termination-dues:backfill --since=2025-01-01`
Expected: a table of contracts with owed/balance and the summary line `Would create N ...`. Sanity-check two rows by hand against the audit CSV (`storage/reports_tmp/receipts_after_termination.csv`): a contract that already has a big post-termination rent receipt should appear as *skipped already settled*, one with none should appear with balance = owed.
Then: `$PHP artisan termination-dues:backfill --contract=TAG2601471 --apply` and open `/termination-dues` → the record is there. Then `$PHP artisan termination-dues:refresh` → `Refreshed N dues record(s).`

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Console/TerminationDuesBackfill.php Modules/BackOffice/Console/TerminationDuesRefresh.php app/Console/Kernel.php
git commit -m "feat(termination-dues): backfill and nightly refresh commands"
```

---

### Task 12: Full verification pass and hand-over notes

**Files:**
- Modify: `docs/superpowers/specs/2026-09-17-termination-dues-design.md` (append a "Deployment notes" section)

- [ ] **Step 1: Run the whole unit suite**

Run: `$PHP vendor/phpunit/phpunit/phpunit tests/Unit`
Expected: `OK` — all existing tests plus the 21 new ones.

- [ ] **Step 2: Lint every PHP file touched or created**

Run: `git diff --name-only HEAD~11 -- '*.php' | while read f; do $PHP -l "$f" | grep -v "No syntax errors" ; done` (adjust `HEAD~11` to the first commit of this plan). Expected: no output.

- [ ] **Step 3: PHP < 7.4 syntax audit of new files**

Run: `grep -nE "fn\s*\(|\?->|\bmatch\s*\(|^\s*(public|private|protected)\s+(static\s+)?(int|string|float|bool|array|\?\w+)\s+\\$|str_contains|str_starts_with" Modules/BackOffice/Services/TerminationDues*.php Modules/BackOffice/Http/Controllers/TerminationDuesController.php Modules/BackOffice/Console/TerminationDues*.php Modules/BackOffice/Entities/TerminationDues*.php`
Expected: no matches.

- [ ] **Step 4: End-to-end scenario on local**

1. Pick a contract at stage 504 (or backfill one) → complete termination → `/termination-dues` shows it with owed = rent + tax + E&W + maintenance.
2. Rent receipt for more than the rent balance → refused with message. For exactly the balance, eff-to ≤ termination date → saved → after approval the dues rent line shows `receipts` = amount, status `partial`.
3. General receipt with account code 41105 → E&W line settles. With 22301 only → appears under "Needs your decision"; assign → settles the chosen line.
4. Deposit refund with a 41110 deduction → maintenance line shows it under *Deposit deduction*.
5. Cancel that deposit refund → maintenance balance comes back without any extra action.
6. Waive the remainder with a reason → status `written_off`; remove the waiver → reverts.

- [ ] **Step 5: Append deployment notes to the spec**

```markdown
## Deployment notes

1. Run migrations: `Modules/BackOffice/Database/Migrations/2026_09_17_000001_create_termination_dues_tables.php`, then `database/migrations/2026_09_17_000002_add_termination_dues_menu.php`.
2. Review `configuration.termination_dues_account_map` against the live chart of accounts; edit the JSON if finance uses other codes.
3. Prune the two permissions per role (the migration grants both to every role): Back Office roles keep `view_termination_dues_backoffice`, Maintenance roles keep `view_termination_dues_maintenance`.
4. `php artisan termination-dues:backfill --since=2024-01-01` (dry run), review, then `--apply`.
5. Confirm the scheduler runs (`termination-dues:refresh --open-only` at 01:30).
6. Hand `storage/reports_tmp/rent_receipts_past_termination_date.csv` to finance for the historical receipts that were used as IOUs.
```

- [ ] **Step 6: Commit**

```bash
git add docs/superpowers/specs/2026-09-17-termination-dues-design.md
git commit -m "docs(termination-dues): deployment notes"
```
