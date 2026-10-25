# Receivables Follow-up Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A Receivables Follow-up screen where AREs see agreements that owe money (as on a month end), record follow-ups (comment + optional promised date, who/when captured), and view each agreement's follow-up history as a timeline.

**Architecture:** Figures (Due / Received / Balance / Last Received) come from the existing base plpgsql function `tenantrentreceivable(date)` (the same one Receivable Report v2 wraps), filtered like v2 (legal excluded, balance > 0), de-duplicated per contract and cached per as-on date by a `ReceivableFigures` service; a scheduled command refreshes last month every 15 minutes. A new `receivable_followups` table holds the append-only history. One controller serves the list (AJAX rows), the follow-up modal, the save, and the timeline modal; ARE scoping reuses `TenantContract::scopeAreFilter`.

**Tech Stack:** Laravel 5.7, PHP (production < 7.4), PostgreSQL, Blade + jQuery + Bootstrap 4 modals, spatie/laravel-permission, PHPUnit 7.

**Spec:** `docs/superpowers/specs/2026-10-01-receivables-followup-design.md`

## Global Constraints

- Production PHP < 7.4: no arrow functions (`fn`), no typed properties, no `??=`. (`??` is fine.)
- Laravel 5.7: `Cache::put($key, $value, $minutes)` — the TTL is **minutes**.
- Run PHP/tests with `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe` from `C:/laragon/www/plms_master`.
- Tests use the local DB with `DatabaseTransactions`; `phpunit.xml` sets `CACHE_DRIVER=array`.
- Never run a plain `php artisan migrate` locally (many old migrations are pending); run only the new file with `--path`.
- Never compute real figures in feature tests (the base function takes ~15 s): seed the cache or bind a fake `ReceivableFigures`.
- Follow-ups are append-only from the UI: no edit or delete routes.
- Balance filter: `netamtdue > 0`. Legal: exclude any contract with a `legal` row. Management types: 1 Comprehensive, 2 Normal.
- Permissions: `view_receivable_followup`, `add_receivable_followup`; granted to roles super_admin, are, are_team_lead, backoffice_executive, backoffice_manager.
- Menu: PLM Module → Operations → "Receivables Follow-up", route `receivable-followup.index`.
- Commit messages end with `Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>`.

## Review Focus

1. Month filter set to the current month or a future month → as-on date is today, never a future date (Task 2 test `test_as_on_date_caps_at_today`).
2. An `are` user with no assigned buildings → empty list, not every building (Task 4 test `test_are_user_without_buildings_sees_nothing`).
3. Saving a follow-up when the figures cache for that month has expired → saves with `balance_at_followup` null instead of failing (Task 5 test `test_save_without_cached_figures_keeps_balance_null`).
4. A comment containing HTML/script → stored as typed and shown escaped in the timeline (Task 5 test `test_timeline_escapes_comment`).
5. Promised date equal to today → accepted; yesterday → rejected (Task 5 test `test_promised_date_today_ok_yesterday_rejected`).

---

## File Structure

| File | Responsibility |
|---|---|
| `database/migrations/2026_10_01_100000_create_receivable_followups.php` | Table + menu row + 2 permissions |
| `Modules/BackOffice/Entities/ReceivableFollowup.php` | Model, creator relation, pure `promiseStatus()` |
| `Modules/BackOffice/Services/ReceivableFigures.php` | As-on date rules, base-function query, legal/zero/duplicate filtering (pure `build()`), cache get/refresh |
| `Modules/BackOffice/Console/RefreshReceivableFollowup.php` | `receivables:refresh-followup` command |
| `app/Console/Kernel.php` | Register command + 15-minute schedule |
| `Modules/BackOffice/Http/Controllers/ReceivableFollowupController.php` | index, rows, refresh, form, store, timeline |
| `Modules/BackOffice/Routes/web.php`, `Modules/BackOffice/Routes/breadcrumbs.php` | Routes, breadcrumb |
| `Modules/BackOffice/Resources/views/ReceivableFollowup/index.blade.php` | Page shell, filters, scripts, styles |
| `.../ReceivableFollowup/_rows.blade.php` | As-of line, totals, table, pagination |
| `.../ReceivableFollowup/_form.blade.php` | Follow-up modal |
| `.../ReceivableFollowup/_timeline.blade.php` | Timeline modal |
| `tests/Unit/ReceivableFollowupStatusTest.php`, `tests/Unit/ReceivableFiguresTest.php`, `tests/Feature/ReceivableFollowupTest.php` | Tests |

---

### Task 1: Table, menu, permissions and model

**Files:**
- Create: `database/migrations/2026_10_01_100000_create_receivable_followups.php`
- Create: `Modules/BackOffice/Entities/ReceivableFollowup.php`
- Test: `tests/Unit/ReceivableFollowupStatusTest.php`

**Interfaces:**
- Produces: table `receivable_followups` (`id`, `tenant_contract_id`, `comment`, `promised_date`, `balance_at_followup`, `created_by`, timestamps); model `Modules\BackOffice\Entities\ReceivableFollowup` with constants `KEPT='kept'`, `BROKEN='broken'`, `UPCOMING='upcoming'`, `creator()` (belongsTo `App\User`), `creatorName(): string`, `static promiseStatus($promisedDate, $followedUpAt, array $receiptDates, $today): ?string`.

- [ ] **Step 1: Write the failing test**

`tests/Unit/ReceivableFollowupStatusTest.php`:
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Entities\ReceivableFollowup as F;

class ReceivableFollowupStatusTest extends TestCase
{
    public function test_no_promise_has_no_status()
    {
        $this->assertNull(F::promiseStatus(null, '2026-09-01 10:00:00', ['2026-09-02'], '2026-10-01'));
    }

    public function test_receipt_between_followup_and_promise_is_kept()
    {
        $this->assertSame(F::KEPT, F::promiseStatus('2026-09-10', '2026-09-01 10:00:00', ['2026-09-10'], '2026-10-01'));
        // same day as the follow-up counts
        $this->assertSame(F::KEPT, F::promiseStatus('2026-09-10', '2026-09-01 10:00:00', ['2026-09-01'], '2026-10-01'));
    }

    public function test_receipt_before_followup_or_after_promise_does_not_count()
    {
        $this->assertSame(F::BROKEN, F::promiseStatus('2026-09-10', '2026-09-01 10:00:00', ['2026-08-31', '2026-09-11'], '2026-10-01'));
    }

    public function test_future_promise_without_receipt_is_upcoming()
    {
        $this->assertSame(F::UPCOMING, F::promiseStatus('2026-10-05', '2026-10-01 09:00:00', [], '2026-10-01'));
        // promise due today is still upcoming
        $this->assertSame(F::UPCOMING, F::promiseStatus('2026-10-01', '2026-09-28 09:00:00', [], '2026-10-01'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/ReceivableFollowupStatusTest.php`
Expected: FAIL — `Class 'Modules\BackOffice\Entities\ReceivableFollowup' not found`.

- [ ] **Step 3: Write the model**

`Modules/BackOffice/Entities/ReceivableFollowup.php`:
```php
<?php

namespace Modules\BackOffice\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * One receivables follow-up on a tenant contract (append-only history).
 * PHP < 7.4 in production: no arrow functions.
 */
class ReceivableFollowup extends Model
{
    const KEPT = 'kept';
    const BROKEN = 'broken';
    const UPCOMING = 'upcoming';

    protected $table = 'receivable_followups';
    protected $guarded = [];
    protected $dates = ['promised_date'];

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    /** Employee name of the user who followed up, else the username. */
    public function creatorName()
    {
        $u = $this->creator;
        if (!$u) {
            return '';
        }
        return optional($u->employee)->employee_name ?: (string) $u->username;
    }

    /**
     * Kept: a rent receipt dated on/after the follow-up day and on/before the promised date.
     * Broken: promised date before today and no such receipt. Upcoming: otherwise.
     * $receiptDates are 'Y-m-d' strings; null when no promised date.
     */
    public static function promiseStatus($promisedDate, $followedUpAt, array $receiptDates, $today)
    {
        if (!$promisedDate) {
            return null;
        }
        $from = Carbon::parse($followedUpAt)->format('Y-m-d');
        $to = Carbon::parse($promisedDate)->format('Y-m-d');
        foreach ($receiptDates as $d) {
            $d = Carbon::parse($d)->format('Y-m-d');
            if ($d >= $from && $d <= $to) {
                return self::KEPT;
            }
        }
        return $to < Carbon::parse($today)->format('Y-m-d') ? self::BROKEN : self::UPCOMING;
    }
}
```

- [ ] **Step 4: Run the unit test**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/ReceivableFollowupStatusTest.php`
Expected: `OK (4 tests, 6 assertions)`.

- [ ] **Step 5: Write the migration**

`database/migrations/2026_10_01_100000_create_receivable_followups.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Receivables Follow-up: append-only follow-up history per tenant contract,
 * menu PLM Module -> Operations -> Receivables Follow-up, and two permissions
 * (view / add) for the roles that chase rent.
 */
class CreateReceivableFollowups extends Migration
{
    const ROUTE = 'receivable-followup.index';
    const PERMS = ['view_receivable_followup', 'add_receivable_followup'];
    const ROLES = ['super_admin', 'are', 'are_team_lead', 'backoffice_executive', 'backoffice_manager'];

    public function up()
    {
        if (!Schema::hasTable('receivable_followups')) {
            Schema::create('receivable_followups', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('tenant_contract_id');
                $table->text('comment');
                $table->date('promised_date')->nullable();
                $table->decimal('balance_at_followup', 14, 3)->nullable();
                $table->integer('created_by');
                $table->timestamps();
                $table->index(['tenant_contract_id', 'created_at']);
            });
        }

        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }
        $plm = DB::table('menu')->where('menu_name', 'PLM Module')->where('parent_menu', 0)->first();
        $operations = $plm
            ? DB::table('menu')->where('menu_name', 'Operations')->where('parent_menu', $plm->id)->where('menutype', 1)->first()
            : null;
        if (!$operations) {
            throw new \RuntimeException('Menu "PLM Module -> Operations" not found.');
        }
        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Receivables Follow-up',
            'menu_icon'   => 'fa-phone',
            'route_name'  => self::ROUTE,
            'url_key'     => 'receivable-followup',
            'parent_menu' => $operations->id,
            'menutype'    => 2,
            'menu_order'  => DB::table('menu')->where('parent_menu', $operations->id)->count() + 1,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $roleIds = DB::table('roles')->whereIn('name', self::ROLES)->pluck('id');
        foreach (self::PERMS as $name) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $name, 'guard_name' => 'web', 'menu_id' => $menuId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($roleIds as $roleId) {
                DB::table('role_has_permissions')->insert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        foreach (DB::table('permissions')->whereIn('name', self::PERMS)->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        DB::table('menu')->where('route_name', self::ROUTE)->delete();
        Schema::dropIfExists('receivable_followups');
        app('cache')->forget('spatie.permission.cache');
    }
}
```

- [ ] **Step 6: Run the migration locally (only this file) and check it**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=database/migrations/2026_10_01_100000_create_receivable_followups.php --force`
Expected: `Migrated: 2026_10_01_100000_create_receivable_followups`.

Then write `C:/Users/anoop/AppData/Local/Temp/claude/C--laragon-www-plms-master/623b6df8-0831-4950-9dcf-22dab33ae303/scratchpad/rfmig.php`:
```php
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
echo json_encode([
    Illuminate\Support\Facades\Schema::hasTable('receivable_followups'),
    DB::table('menu')->where('route_name', 'receivable-followup.index')->value('parent_menu'),
    DB::table('permissions')->whereIn('name', ['view_receivable_followup', 'add_receivable_followup'])->count(),
]), "\n";
```
Run it with `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe <that file>` from the project folder.
Expected: `[true,96,2]` (96 = Operations locally).

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_10_01_100000_create_receivable_followups.php Modules/BackOffice/Entities/ReceivableFollowup.php tests/Unit/ReceivableFollowupStatusTest.php
git commit -m "feat(receivable-followup): table, menu, permissions and follow-up model

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 2: Figures service (base calculation, filtering, cache)

**Files:**
- Create: `Modules/BackOffice/Services/ReceivableFigures.php`
- Test: `tests/Unit/ReceivableFiguresTest.php`

**Interfaces:**
- Produces (class `Modules\BackOffice\Services\ReceivableFigures`, resolved with `app(ReceivableFigures::class)` everywhere so tests can bind a fake):
  - `static lastMonth($today = null): array` → `[month(int), year(int)]`
  - `static asOnDate($month, $year, $today = null): string` → `'Y-m-d'`, last day of the month, capped at today
  - `static key($asOn): string` → `'receivable_followup:' . $asOn`
  - `static build(array $rows, array $legalNos, array $idsByNo): array` → `[contractId => ['due'=>float,'received'=>float,'balance'=>float,'last_received'=>'Y-m-d'|null]]`
  - `compute($asOn): array` (same shape as `build`)
  - `refresh($asOn): array` → `['computed_at' => 'Y-m-d H:i:s', 'rows' => build-shape]`, stored in cache for 1440 minutes
  - `cached($asOn): ?array` (same shape as `refresh`, or null)
  - `get($asOn): array` → cached or refreshed

- [ ] **Step 1: Write the failing tests**

`tests/Unit/ReceivableFiguresTest.php`:
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\ReceivableFigures as RF;

class ReceivableFiguresTest extends TestCase
{
    private function row($no, $due, $received, $balance, $receipt = null)
    {
        return (object) ['contract_no' => $no, 'totaldue' => $due, 'amount_received' => $received, 'netamtdue' => $balance, 'receipt_date' => $receipt];
    }

    public function test_last_month_and_as_on_date()
    {
        $this->assertSame([9, 2026], RF::lastMonth('2026-10-01'));
        $this->assertSame([12, 2025], RF::lastMonth('2026-01-15'));
        $this->assertSame('2026-09-30', RF::asOnDate(9, 2026, '2026-10-01'));
        $this->assertSame('2024-02-29', RF::asOnDate(2, 2024, '2026-10-01'));
    }

    public function test_as_on_date_caps_at_today()
    {
        $this->assertSame('2026-10-01', RF::asOnDate(10, 2026, '2026-10-01'));
        $this->assertSame('2026-10-01', RF::asOnDate(12, 2026, '2026-10-01'));
    }

    public function test_build_drops_legal_zero_unknown_and_duplicates()
    {
        $rows = [
            $this->row('A1', '1000', '400', '600', '2026-08-01'),
            $this->row('A1', '1000', '400', '600', '2026-08-01'),   // exact repeat
            $this->row('L1', '500', '0', '500'),                     // legal
            $this->row('Z1', '300', '300', '0'),                     // nothing owed
            $this->row('X9', '200', '0', '200'),                     // no matching contract
        ];
        $out = RF::build($rows, ['L1'], ['A1' => 11, 'L1' => 12, 'Z1' => 13]);
        $this->assertSame([11], array_keys($out));
        $this->assertSame(['due' => 1000.0, 'received' => 400.0, 'balance' => 600.0, 'last_received' => '2026-08-01'], $out[11]);
    }

    public function test_build_keeps_null_receipt_date()
    {
        $out = RF::build([$this->row('B2', '750.5', null, '750.5')], [], ['B2' => 7]);
        $this->assertSame(['due' => 750.5, 'received' => 0.0, 'balance' => 750.5, 'last_received' => null], $out[7]);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/ReceivableFiguresTest.php`
Expected: FAIL — class `ReceivableFigures` not found.

- [ ] **Step 3: Write the service**

`Modules/BackOffice/Services/ReceivableFigures.php`:
```php
<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Due / Received / Balance / Last Received per tenant contract for the
 * Receivables Follow-up screen. Same base calculation as Receivable Report v2
 * (tenantrentreceivable), with v2's legal exclusion, one row per contract and
 * only contracts that still owe money. The base function takes ~15 s, so the
 * result is cached per as-on date (Laravel 5.7: TTL in minutes).
 * PHP < 7.4 in production: no arrow functions.
 */
class ReceivableFigures
{
    const TTL_MINUTES = 1440;

    public static function lastMonth($today = null)
    {
        $d = Carbon::parse($today ?: Carbon::today())->startOfMonth()->subMonth();
        return [(int) $d->month, (int) $d->year];
    }

    /** Last day of the month, never later than today. */
    public static function asOnDate($month, $year, $today = null)
    {
        $today = Carbon::parse($today ?: Carbon::today())->startOfDay();
        $end = Carbon::create((int) $year, (int) $month, 1, 0, 0, 0)->endOfMonth()->startOfDay();
        return ($end->gt($today) ? $today : $end)->format('Y-m-d');
    }

    public static function key($asOn)
    {
        return 'receivable_followup:' . $asOn;
    }

    /**
     * Pure. $rows: objects from tenantrentreceivable(); $legalNos: contract numbers
     * with a legal record; $idsByNo: contract_no => tenant_contracts.id.
     */
    public static function build(array $rows, array $legalNos, array $idsByNo)
    {
        $legal = array_flip(array_map('strval', $legalNos));
        $out = [];
        foreach ($rows as $r) {
            $no = (string) $r->contract_no;
            if (isset($legal[$no]) || !isset($idsByNo[$no])) {
                continue;
            }
            $balance = round((float) $r->netamtdue, 3);
            if ($balance <= 0) {
                continue;
            }
            $id = (int) $idsByNo[$no];
            if (isset($out[$id])) {
                continue;
            }
            $out[$id] = [
                'due'           => round((float) $r->totaldue, 3),
                'received'      => round((float) $r->amount_received, 3),
                'balance'       => $balance,
                'last_received' => $r->receipt_date ? Carbon::parse($r->receipt_date)->format('Y-m-d') : null,
            ];
        }
        return $out;
    }

    public function compute($asOn)
    {
        $rows = DB::select('SELECT contract_no, totaldue, amount_received, netamtdue, receipt_date FROM tenantrentreceivable(?::date)', [$asOn]);

        $legalNos = DB::table('legal')
            ->join('tenant_contracts', 'tenant_contracts.id', '=', 'legal.tenant_contract_id')
            ->pluck('tenant_contracts.tenant_contract_no')->all();

        $nos = [];
        foreach ($rows as $r) {
            $nos[(string) $r->contract_no] = true;
        }
        $idsByNo = [];
        foreach (array_chunk(array_keys($nos), 1000) as $chunk) {
            // a contract number used twice maps to the newest contract (highest id)
            foreach (DB::table('tenant_contracts')->whereIn('tenant_contract_no', $chunk)->orderBy('id')->get(['id', 'tenant_contract_no']) as $c) {
                $idsByNo[(string) $c->tenant_contract_no] = (int) $c->id;
            }
        }
        return self::build($rows, $legalNos, $idsByNo);
    }

    /** Recompute and cache. On failure the exception propagates and the old cache entry stays. */
    public function refresh($asOn)
    {
        $data = ['computed_at' => Carbon::now()->format('Y-m-d H:i:s'), 'rows' => $this->compute($asOn)];
        Cache::put(self::key($asOn), $data, self::TTL_MINUTES);
        return $data;
    }

    public function cached($asOn)
    {
        $data = Cache::get(self::key($asOn));
        return is_array($data) ? $data : null;
    }

    public function get($asOn)
    {
        $data = $this->cached($asOn);
        return $data ?: $this->refresh($asOn);
    }
}
```

- [ ] **Step 4: Run the unit tests**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/ReceivableFiguresTest.php`
Expected: `OK (4 tests, ...)`.

- [ ] **Step 5: One real run against the local DB (manual check, ~15 s)**

Write `C:/Users/anoop/AppData/Local/Temp/claude/C--laragon-www-plms-master/623b6df8-0831-4950-9dcf-22dab33ae303/scratchpad/rf.php`:
```php
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$t = microtime(true);
$rows = (new Modules\BackOffice\Services\ReceivableFigures)->compute('2026-09-30');
$bal = 0; foreach ($rows as $r) { $bal += $r['balance']; }
printf("%d contracts, balance %.3f, %.1fs\n", count($rows), $bal, microtime(true) - $t);
$legal = Illuminate\Support\Facades\DB::table('legal')->pluck('tenant_contract_id')->all();
echo 'legal contracts present: ', count(array_intersect(array_keys($rows), $legal)), "\n";
```
Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe <that file>`
Expected: about 3,400 contracts (v2 shows 3,404 distinct contracts as on 2026-09-30), `legal contracts present: 0`.

- [ ] **Step 6: Commit**

```bash
git add Modules/BackOffice/Services/ReceivableFigures.php tests/Unit/ReceivableFiguresTest.php
git commit -m "feat(receivable-followup): cached figures from the receivables base calculation

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 3: Background refresh command and schedule

**Files:**
- Create: `Modules/BackOffice/Console/RefreshReceivableFollowup.php`
- Modify: `app/Console/Kernel.php` (`$commands` array and `schedule()`)
- Test: `tests/Feature/ReceivableFollowupRefreshTest.php`

**Interfaces:**
- Consumes: `ReceivableFigures::lastMonth()`, `asOnDate()`, `key()`, `refresh()` (Task 2).
- Produces: artisan command `receivables:refresh-followup`.

- [ ] **Step 1: Write the failing test**

`tests/Feature/ReceivableFollowupRefreshTest.php`:
```php
<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Modules\BackOffice\Services\ReceivableFigures;

class FakeFigures extends ReceivableFigures
{
    public static $computed = [];
    public static $fail = false;
    public function compute($asOn)
    {
        if (self::$fail) {
            throw new \RuntimeException('db down');
        }
        self::$computed[] = $asOn;
        return [5 => ['due' => 10.0, 'received' => 0.0, 'balance' => 10.0, 'last_received' => null]];
    }
}

class ReceivableFollowupRefreshTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        FakeFigures::$computed = [];
        FakeFigures::$fail = false;
        $this->app->bind(ReceivableFigures::class, FakeFigures::class);
    }

    private function lastMonthAsOn()
    {
        list($m, $y) = ReceivableFigures::lastMonth();
        return ReceivableFigures::asOnDate($m, $y);
    }

    public function test_refreshes_last_month()
    {
        $this->artisan('receivables:refresh-followup')->assertExitCode(0);
        $this->assertSame([$this->lastMonthAsOn()], FakeFigures::$computed);
        $this->assertSame(10.0, Cache::get(ReceivableFigures::key($this->lastMonthAsOn()))['rows'][5]['balance']);
    }

    public function test_also_refreshes_today_when_opened_today()
    {
        $today = Carbon::today()->format('Y-m-d');
        Cache::put(ReceivableFigures::key($today), ['computed_at' => 'x', 'rows' => []], 10);
        $this->artisan('receivables:refresh-followup')->assertExitCode(0);
        $this->assertContains($today, FakeFigures::$computed);
    }

    public function test_failure_keeps_previous_figures()
    {
        $key = ReceivableFigures::key($this->lastMonthAsOn());
        Cache::put($key, ['computed_at' => 'old', 'rows' => []], 10);
        FakeFigures::$fail = true;
        $this->artisan('receivables:refresh-followup');
        $this->assertSame('old', Cache::get($key)['computed_at']);
    }

    public function test_scheduled_every_fifteen_minutes()
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->filter(function ($e) {
            return strpos($e->command, 'receivables:refresh-followup') !== false;
        });
        $this->assertCount(1, $events);
        $this->assertSame('*/15 * * * *', $events->first()->expression);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Feature/ReceivableFollowupRefreshTest.php`
Expected: FAIL — command `receivables:refresh-followup` not defined.

- [ ] **Step 3: Write the command**

`Modules/BackOffice/Console/RefreshReceivableFollowup.php`:
```php
<?php

namespace Modules\BackOffice\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\BackOffice\Services\ReceivableFigures;

/**
 * Keeps the Receivables Follow-up figures warm: last month's as-on date always,
 * and today's (the current month) when someone opened it today.
 */
class RefreshReceivableFollowup extends Command
{
    protected $signature = 'receivables:refresh-followup';
    protected $description = 'Refresh the cached figures of the Receivables Follow-up screen';

    public function handle()
    {
        $figures = app(ReceivableFigures::class);
        list($m, $y) = ReceivableFigures::lastMonth();
        $dates = [ReceivableFigures::asOnDate($m, $y)];
        $today = Carbon::today()->format('Y-m-d');
        if (Cache::has(ReceivableFigures::key($today))) {
            $dates[] = $today;
        }
        foreach (array_unique($dates) as $asOn) {
            try {
                $data = $figures->refresh($asOn);
                $this->info($asOn . ': ' . count($data['rows']) . ' contracts');
            } catch (\Exception $e) {
                Log::error('receivables:refresh-followup ' . $asOn . ' failed - ' . $e->getMessage());
                $this->error($asOn . ' failed: ' . $e->getMessage());
            }
        }
        return 0;
    }
}
```

- [ ] **Step 4: Register and schedule it in `app/Console/Kernel.php`**

In `$commands`, after `\Modules\BackOffice\Console\RentOutstandingCorrectShortContracts::class,` add:
```php
         \Modules\BackOffice\Console\RefreshReceivableFollowup::class,
```
In `schedule()`, after the `bulk-messages:send` line add:
```php

       $schedule->command('receivables:refresh-followup')->everyFifteenMinutes()->withoutOverlapping(30);
```

- [ ] **Step 5: Run the tests**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Feature/ReceivableFollowupRefreshTest.php`
Expected: `OK (4 tests, ...)`.

- [ ] **Step 6: Commit**

```bash
git add Modules/BackOffice/Console/RefreshReceivableFollowup.php app/Console/Kernel.php tests/Feature/ReceivableFollowupRefreshTest.php
git commit -m "feat(receivable-followup): refresh figures every 15 minutes

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 4: List screen (filters, ARE scope, totals, sorting, refresh)

**Files:**
- Create: `Modules/BackOffice/Http/Controllers/ReceivableFollowupController.php` (index, rows, refresh)
- Modify: `Modules/BackOffice/Routes/web.php` (after the `termination-dues/{terminationDues}/print` line, ~810)
- Modify: `Modules/BackOffice/Routes/breadcrumbs.php` (end of file)
- Create: `Modules/BackOffice/Resources/views/ReceivableFollowup/index.blade.php`, `_rows.blade.php`
- Test: `tests/Feature/ReceivableFollowupTest.php`

**Interfaces:**
- Consumes: `ReceivableFigures` (Task 2), `ReceivableFollowup` (Task 1), `TenantContract::scopeAreFilter`.
- Produces: routes `receivable-followup.index` (GET page), `receivable-followup.rows` (GET, HTML partial), `receivable-followup.refresh` (POST, JSON `{computed_at}`); controller private helper `period(Request): array [month, year, asOn]`; the rows partial renders buttons `.rf-followup` / `.rf-view` with `data-url` attributes used by Task 5, and cells `#rf-last-{id}` / `#rf-promise-{id}` that Task 5 updates.

- [ ] **Step 1: Write the failing feature tests**

`tests/Feature/ReceivableFollowupTest.php`:
```php
<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\ReceivableFollowup;
use Modules\BackOffice\Services\ReceivableFigures;
use Modules\Sales\Entities\TenantContract;

class NoComputeFigures extends ReceivableFigures
{
    public function compute($asOn)
    {
        throw new \RuntimeException('figures must come from the seeded cache in tests');
    }
}

class ReceivableFollowupTest extends TestCase
{
    use DatabaseTransactions;

    private $asOn;
    private $contracts;

    protected function setUp()
    {
        parent::setUp();
        $this->app->bind(ReceivableFigures::class, NoComputeFigures::class);
        $this->actingAs(User::find(1));
        list($m, $y) = ReceivableFigures::lastMonth();
        $this->asOn = ReceivableFigures::asOnDate($m, $y);
        // three real contracts in two different active buildings with different management types
        $this->contracts = TenantContract::join('buildings', 'buildings.id', '=', 'tenant_contracts.building_id')
            ->where('buildings.building_status', 1)->whereIn('buildings.management_id', [1, 2])
            ->whereHas('tenant')->whereNotNull('tenant_contracts.unit_id')
            ->orderBy('tenant_contracts.id', 'desc')->limit(400)->get(['tenant_contracts.*', 'buildings.management_id'])
            ->unique('building_id')->take(3)->values();
        $this->seed3([500.0, 1500.0, 900.0]);
    }

    private function seed3(array $balances)
    {
        $rows = [];
        foreach ($this->contracts as $i => $c) {
            $rows[$c->id] = ['due' => $balances[$i] + 100, 'received' => 100.0, 'balance' => $balances[$i], 'last_received' => '2026-08-15'];
        }
        Cache::put(ReceivableFigures::key($this->asOn), ['computed_at' => '2026-10-01 09:45:00', 'rows' => $rows], 60);
    }

    private function rows(array $q = [])
    {
        return $this->get(route('receivable-followup.rows', $q))->assertOk()->getContent();
    }

    public function test_page_opens_with_last_month_selected()
    {
        list($m, $y) = ReceivableFigures::lastMonth();
        $html = $this->get(route('receivable-followup.index'))->assertOk()->getContent();
        $this->assertContains('Receivables Follow-up', $html);
        $this->assertRegExp('/<option value="' . $m . '" selected/', $html);
        $this->assertRegExp('/<option value="' . $y . '" selected/', $html);
    }

    public function test_rows_show_figures_sorted_by_balance_with_totals()
    {
        $html = $this->rows();
        $nos = $this->contracts->pluck('tenant_contract_no')->all();
        // balance desc: 1500 (#2), 900 (#3), 500 (#1)
        $this->assertTrue(strpos($html, $nos[1]) < strpos($html, $nos[2]) && strpos($html, $nos[2]) < strpos($html, $nos[0]));
        $this->assertContains('2,900.000', $html);           // total balance
        $this->assertContains('Figures as of 01/10/2026 09:45', $html);
    }

    public function test_filter_by_building_and_management_type()
    {
        $c = $this->contracts[0];
        $html = $this->rows(['building_id' => $c->building_id]);
        $this->assertContains($c->tenant_contract_no, $html);
        $this->assertNotContains($this->contracts[1]->tenant_contract_no, $html);

        $html = $this->rows(['management_id' => $c->management_id]);
        foreach ($this->contracts as $other) {
            $want = (int) $other->management_id === (int) $c->management_id;
            $this->assertSame($want, strpos($html, $other->tenant_contract_no) !== false, $other->tenant_contract_no);
        }
    }

    public function test_search_by_contract_no()
    {
        $html = $this->rows(['q' => $this->contracts[2]->tenant_contract_no]);
        $this->assertContains($this->contracts[2]->tenant_contract_no, $html);
        $this->assertNotContains($this->contracts[0]->tenant_contract_no, $html);
    }

    public function test_last_followup_and_overdue_promise_shown()
    {
        $c = $this->contracts[0];
        ReceivableFollowup::create(['tenant_contract_id' => $c->id, 'comment' => 'will pay', 'promised_date' => Carbon::yesterday(), 'created_by' => 1]);
        $html = $this->rows();
        $this->assertRegExp('/id="rf-promise-' . $c->id . '"[^>]*class="[^"]*rf-overdue/', $html);
        $this->assertContains(Carbon::yesterday()->format('d/m/Y'), $html);
    }

    public function test_are_user_without_buildings_sees_nothing()
    {
        $are = User::role('are')->get()->first(function ($u) {
            return $u->roles->count() === 1 && !DB::table('are_buildings')->where('user_id', $u->id)->exists();
        });
        if (!$are) {
            $this->markTestSkipped('No local ARE user without buildings.');
        }
        $this->actingAs($are);
        $html = $this->rows();
        foreach ($this->contracts as $c) {
            $this->assertNotContains($c->tenant_contract_no, $html);
        }
        $this->assertContains('No agreements with a balance', $html);
    }

    public function test_are_user_sees_only_own_buildings()
    {
        $pb = DB::table('preferred_buildings')->join('are_buildings', 'are_buildings.id', '=', 'preferred_buildings.are_building_id')
            ->whereNull('preferred_buildings.assign_to')->whereIn('preferred_buildings.building_id', $this->contracts->pluck('building_id'))
            ->first(['are_buildings.user_id', 'preferred_buildings.building_id']);
        $are = $pb ? User::find($pb->user_id) : null;
        if (!$are || $are->roles->count() !== 1 || !$are->hasRole('are')) {
            $this->markTestSkipped('No single-role ARE assigned to the sample buildings.');
        }
        $this->actingAs($are);
        $html = $this->rows();
        foreach ($this->contracts as $c) {
            $own = DB::table('preferred_buildings')->join('are_buildings', 'are_buildings.id', '=', 'preferred_buildings.are_building_id')
                ->whereNull('preferred_buildings.assign_to')->where('are_buildings.user_id', $are->id)->where('preferred_buildings.building_id', $c->building_id)->exists();
            $this->assertSame($own, strpos($html, $c->tenant_contract_no) !== false);
        }
    }

    public function test_needs_view_permission()
    {
        $user = User::all()->first(function ($u) { return !$u->can('view_receivable_followup'); });
        if (!$user) {
            $this->markTestSkipped('No local user without view_receivable_followup.');
        }
        $this->actingAs($user)->get(route('receivable-followup.index'))->assertStatus(403);
    }

    public function test_refresh_recomputes_the_month()
    {
        $this->app->bind(ReceivableFigures::class, function () {
            return new class extends ReceivableFigures {
                public function compute($asOn) { return []; }
            };
        });
        $this->postJson(route('receivable-followup.refresh'), [])->assertOk()->assertJsonStructure(['computed_at']);
        $this->assertSame([], Cache::get(ReceivableFigures::key($this->asOn))['rows']);
    }
}
```

- [ ] **Step 2: Run to verify they fail**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Feature/ReceivableFollowupTest.php`
Expected: FAIL — route `receivable-followup.rows` not defined.

- [ ] **Step 3: Routes and breadcrumb**

In `Modules/BackOffice/Routes/web.php`, directly after
`Route::get('termination-dues/{terminationDues}/print', 'TerminationDuesController@print')->name('termination-dues.print');` add:
```php

	// Receivables follow-up: AREs record calls and promised dates on agreements that owe rent
	Route::get('receivable-followup', 'ReceivableFollowupController@index')->name('receivable-followup.index');
	Route::get('receivable-followup/rows', 'ReceivableFollowupController@rows')->name('receivable-followup.rows');
	Route::post('receivable-followup/refresh', 'ReceivableFollowupController@refresh')->name('receivable-followup.refresh');
	Route::get('receivable-followup/{contract}/form', 'ReceivableFollowupController@form')->where('contract', '[0-9]+')->name('receivable-followup.form');
	Route::post('receivable-followup/{contract}', 'ReceivableFollowupController@store')->where('contract', '[0-9]+')->name('receivable-followup.store');
	Route::get('receivable-followup/{contract}/timeline', 'ReceivableFollowupController@timeline')->where('contract', '[0-9]+')->name('receivable-followup.timeline');
```
At the end of `Modules/BackOffice/Routes/breadcrumbs.php` add:
```php

Breadcrumbs::for('receivable-followup.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Receivables Follow-up', route('receivable-followup.index'));
});
```

- [ ] **Step 4: Controller (list part; form/store/timeline are added in Task 5)**

`Modules/BackOffice/Http/Controllers/ReceivableFollowupController.php`:
```php
<?php

namespace Modules\BackOffice\Http\Controllers;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\BackOffice\Entities\ReceivableFollowup;
use Modules\BackOffice\Services\ReceivableFigures;
use Modules\Masters\Entities\Building;
use Modules\Sales\Entities\TenantContract;

/**
 * Receivables Follow-up: agreements that owe rent as on a month end, with the
 * ARE's follow-ups (comment + promised date). Figures come cached from
 * ReceivableFigures; contract details and follow-ups are read live.
 * PHP < 7.4 in production: no arrow functions.
 */
class ReceivableFollowupController extends Controller
{
    const PER_PAGE = 25;
    const SORTS = ['balance', 'due', 'received', 'last_received', 'tenant', 'building', 'contract'];
    const MANAGEMENT = [1 => 'Comprehensive', 2 => 'Normal'];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_receivable_followup');
        $this->middleware('permission:add_receivable_followup')->only(['form', 'store']);
    }

    /** [month, year, asOn] from the request; default last month. */
    private function period(Request $request)
    {
        list($m, $y) = ReceivableFigures::lastMonth();
        $month = (int) $request->input('month', $m);
        $year = (int) $request->input('year', $y);
        if ($month < 1 || $month > 12 || $year < 2000 || $year > (int) date('Y')) {
            $month = $m;
            $year = $y;
        }
        return [$month, $year, ReceivableFigures::asOnDate($month, $year)];
    }

    public function index(Request $request)
    {
        list($month, $year) = $this->period($request);
        $buildings = Building::areFilter()->where('building_status', 1)->orderBy('building_name')->get(['id', 'building_name']);
        $years = range((int) date('Y'), 2018);
        $management = self::MANAGEMENT;
        return view('backoffice::ReceivableFollowup.index', compact('month', 'year', 'buildings', 'years', 'management'));
    }

    public function rows(Request $request)
    {
        list($month, $year, $asOn) = $this->period($request);
        $data = app(ReceivableFigures::class)->get($asOn);
        $fig = $data['rows'];

        $q = TenantContract::query()->areFilter()
            ->join('buildings', 'buildings.id', '=', 'tenant_contracts.building_id')
            ->join('tenant', 'tenant.id', '=', 'tenant_contracts.tenant_id')
            ->leftJoin('units', 'units.id', '=', 'tenant_contracts.unit_id')
            ->whereIn('tenant_contracts.id', $fig ? array_keys($fig) : [0])
            ->select('tenant_contracts.id', 'tenant_contracts.tenant_contract_no', 'tenant_contracts.tenant_contract_effective_date',
                'tenant_contracts.tenant_contract_valid_to_date', 'tenant_contracts.building_id', 'buildings.building_name',
                'buildings.management_id', 'tenant.tenant_name', 'tenant.tenant_contact_person', 'tenant.tenant_contact_no', 'units.unit_no');
        if ($request->filled('building_id')) {
            $q->where('tenant_contracts.building_id', (int) $request->building_id);
        }
        if (isset(self::MANAGEMENT[(int) $request->input('management_id')])) {
            $q->where('buildings.management_id', (int) $request->management_id);
        }
        if ($request->filled('q')) {
            $term = '%' . trim($request->q) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('tenant.tenant_name', 'ilike', $term)
                  ->orWhere('tenant_contracts.tenant_contract_no', 'ilike', $term)
                  ->orWhere('units.unit_no', 'ilike', $term);
            });
        }

        $list = [];
        $totals = ['due' => 0.0, 'received' => 0.0, 'balance' => 0.0];
        foreach ($q->get() as $c) {
            $f = $fig[$c->id];
            $list[] = (object) [
                'id' => $c->id, 'contract' => $c->tenant_contract_no, 'tenant' => $c->tenant_name,
                'contact_person' => $c->tenant_contact_person, 'phone' => $c->tenant_contact_no,
                'from' => $c->tenant_contract_effective_date, 'to' => $c->tenant_contract_valid_to_date,
                'building' => $c->building_name, 'unit' => $c->unit_no,
                'due' => $f['due'], 'received' => $f['received'], 'balance' => $f['balance'], 'last_received' => $f['last_received'],
            ];
            $totals['due'] += $f['due'];
            $totals['received'] += $f['received'];
            $totals['balance'] += $f['balance'];
        }

        $sort = in_array($request->input('sort'), self::SORTS, true) ? $request->input('sort') : 'balance';
        $desc = $request->input('direction', $sort === 'balance' ? 'desc' : 'asc') === 'desc';
        usort($list, function ($a, $b) use ($sort, $desc) {
            $x = $a->$sort;
            $y = $b->$sort;
            $cmp = is_numeric($x) && is_numeric($y) ? ($x <=> $y) : strcasecmp((string) $x, (string) $y);
            if ($cmp === 0) {
                $cmp = $a->id <=> $b->id;
            }
            return $desc ? -$cmp : $cmp;
        });

        $page = max(1, (int) $request->input('page', 1));
        $paginator = new LengthAwarePaginator(array_slice($list, ($page - 1) * self::PER_PAGE, self::PER_PAGE), count($list), self::PER_PAGE, $page,
            ['path' => route('receivable-followup.rows'), 'query' => $request->except('page')]);

        $followups = ReceivableFollowup::with('creator.employee')
            ->whereIn('tenant_contract_id', collect($paginator->items())->pluck('id')->all() ?: [0])
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get()->groupBy('tenant_contract_id');

        return view('backoffice::ReceivableFollowup._rows', [
            'rows' => $paginator, 'totals' => $totals, 'followups' => $followups, 'computedAt' => $data['computed_at'],
            'asOn' => $asOn, 'sort' => $sort, 'desc' => $desc, 'today' => Carbon::today()->format('Y-m-d'),
        ]);
    }

    public function refresh(Request $request)
    {
        list($month, $year, $asOn) = $this->period($request);
        $data = app(ReceivableFigures::class)->refresh($asOn);
        return response()->json(['computed_at' => $data['computed_at']]);
    }
}
```

- [ ] **Step 5: Page view**

`Modules/BackOffice/Resources/views/ReceivableFollowup/index.blade.php`:
```blade
@extends('layouts.plms-app')
@section('content')
<style>
  .rf .rf-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; margin-bottom:16px; }
  .rf .rf-filters label { display:block; font-size:12px; font-weight:600; margin:0 0 4px; }
  .rf .rf-filters .form-control { min-width:140px; }
  .rf .rf-asof { display:flex; justify-content:space-between; align-items:center; font-size:13px; color:#64748b; margin-bottom:8px; }
  .rf table td, .rf table th { vertical-align:top; font-size:13px; }
  .rf .rf-sub { display:block; font-size:12px; color:#64748b; }
  .rf .num { text-align:right; white-space:nowrap; }
  .rf .rf-overdue { color:#b91c1c; font-weight:600; }
  .rf .rf-totals td { font-weight:600; border-top:2px solid #cbd5e1; }
  .rf .rf-sort { color:inherit; text-decoration:none; }
  .rf .rf-sort:hover { text-decoration:underline; }
  .rf-tl { list-style:none; margin:0; padding:0 0 0 18px; border-left:2px solid #cbd5e1; }
  .rf-tl li { position:relative; margin:0 0 16px; }
  .rf-tl li:before { content:''; position:absolute; left:-25px; top:4px; width:12px; height:12px; border-radius:50%; background:#2563eb; }
  .rf-tl .who { font-size:12px; color:#64748b; }
  .rf-badge { display:inline-block; padding:1px 8px; border-radius:10px; font-size:12px; font-weight:600; }
  .rf-badge.kept { background:#dcfce7; color:#166534; } .rf-badge.broken { background:#fee2e2; color:#991b1b; } .rf-badge.upcoming { background:#e0e7ff; color:#3730a3; }
</style>
<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">Receivables Follow-up</div></div>{{ Breadcrumbs::render('receivable-followup.index') }}</div></div>
<div class="rf"><div class="card card-box"><div class="card-body">
  <form id="rf-filters" class="rf-filters" onsubmit="return false">
    <div><label for="rf-month">Month</label>
      <select id="rf-month" name="month" class="form-control">
        @foreach(range(1, 12) as $m)<option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>@endforeach
      </select></div>
    <div><label for="rf-year">Year</label>
      <select id="rf-year" name="year" class="form-control">
        @foreach($years as $y)<option value="{{ $y }}" {{ $y === $year ? 'selected' : '' }}>{{ $y }}</option>@endforeach
      </select></div>
    <div><label for="rf-building">Building</label>
      <select id="rf-building" name="building_id" class="form-control">
        <option value="">All buildings</option>
        @foreach($buildings as $b)<option value="{{ $b->id }}">{{ $b->building_name }}</option>@endforeach
      </select></div>
    <div><label for="rf-mgmt">Management type</label>
      <select id="rf-mgmt" name="management_id" class="form-control">
        <option value="">All</option>
        @foreach($management as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
      </select></div>
    <div><label for="rf-q">Search</label>
      <input type="search" id="rf-q" name="q" class="form-control" placeholder="Tenant, contract or unit" autocomplete="off"></div>
  </form>
  <div id="rf-rows"><p class="rf-sub">Loading…</p></div>
</div></div></div>
<div class="modal" id="rf-modal" tabindex="-1" role="dialog"></div>
@endsection
@section('scripts')
<script>
(function () {
  var rowsUrl = @json(route('receivable-followup.rows'));
  var refreshUrl = @json(route('receivable-followup.refresh'));
  var token = $('meta[name="csrf-token"]').attr('content');
  var state = {page: 1, sort: '', direction: ''};
  var slowTimer, req;

  function params() {
    var p = $('#rf-filters').serializeArray();
    p.push({name: 'page', value: state.page});
    if (state.sort) { p.push({name: 'sort', value: state.sort}, {name: 'direction', value: state.direction}); }
    return $.param(p);
  }
  function load() {
    if (req) { req.abort(); }
    clearTimeout(slowTimer);
    slowTimer = setTimeout(function () { $('#rf-rows').prepend('<p class="rf-sub rf-calc">Calculating figures for this month… this can take about 15 seconds.</p>'); }, 1500);
    req = $.get(rowsUrl + '?' + params()).done(function (html) { $('#rf-rows').html(html); })
      .fail(function (x) { if (x.statusText !== 'abort') { $('#rf-rows').html('<p class="rf-overdue">Could not load the list. Please try again.</p>'); } })
      .always(function () { clearTimeout(slowTimer); $('.rf-calc').remove(); });
  }
  var typing;
  $('#rf-filters select').on('change', function () { state.page = 1; load(); });
  $('#rf-q').on('input', function () { clearTimeout(typing); typing = setTimeout(function () { state.page = 1; load(); }, 400); });
  $(document).on('click', '#rf-rows .pagination a', function (e) {
    e.preventDefault();
    var m = /[?&]page=(\d+)/.exec($(this).attr('href'));
    state.page = m ? Number(m[1]) : 1; load();
  });
  $(document).on('click', '#rf-rows .rf-sort', function (e) {
    e.preventDefault();
    var s = $(this).attr('data-sort');
    state.direction = (state.sort === s && state.direction === 'desc') ? 'asc' : (state.sort === s ? 'desc' : $(this).attr('data-first'));
    state.sort = s; state.page = 1; load();
  });
  $(document).on('click', '#rf-refresh', function () {
    var $b = $(this).prop('disabled', true).text('Refreshing…');
    $.post(refreshUrl, $('#rf-filters').serialize() + '&_token=' + encodeURIComponent(token))
      .done(function () { load(); })
      .fail(function () { $b.prop('disabled', false).text('Refresh'); alert('Could not refresh the figures. Please try again.'); });
  });
  window.rfReload = load;
  load();
})();
</script>
@endsection
```

- [ ] **Step 6: Rows partial**

`Modules/BackOffice/Resources/views/ReceivableFollowup/_rows.blade.php`:
```blade
@php
  $sortLink = function ($key, $label, $first) use ($sort, $desc) {
      $arrow = $sort === $key ? ($desc ? ' ▼' : ' ▲') : '';
      return '<a href="#" class="rf-sort" data-sort="' . $key . '" data-first="' . $first . '">' . e($label) . $arrow . '</a>';
  };
  $fmtDate = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—'; };
@endphp
<div class="rf-asof">
  <span>As on {{ \Carbon\Carbon::parse($asOn)->format('d/m/Y') }} · {{ $rows->total() }} agreements</span>
  <span>Figures as of {{ \Carbon\Carbon::parse($computedAt)->format('d/m/Y H:i') }} · <button type="button" class="btn btn-link btn-sm p-0" id="rf-refresh">Refresh</button></span>
</div>
<div class="table-responsive">
<table class="table table-hover">
  <thead><tr>
    <th>{!! $sortLink('tenant', 'Tenant', 'asc') !!}</th>
    <th>{!! $sortLink('contract', 'Agreement', 'asc') !!}</th>
    <th>{!! $sortLink('building', 'Building', 'asc') !!}</th>
    <th>Unit No</th>
    <th>{!! $sortLink('last_received', 'Last Received', 'desc') !!}</th>
    <th class="num">{!! $sortLink('due', 'Total Due', 'desc') !!}</th>
    <th class="num">{!! $sortLink('received', 'Amount Received', 'desc') !!}</th>
    <th class="num">{!! $sortLink('balance', 'Balance', 'desc') !!}</th>
    <th>Last Follow-up</th>
    <th>Promised Date</th>
    <th>Actions</th>
  </tr></thead>
  <tbody>
  @forelse($rows as $r)
    @php
      $fus = $followups->get($r->id, collect());
      $last = $fus->first();
      $promise = $fus->first(function ($f) { return $f->promised_date !== null; });
      $overdue = $promise && $promise->promised_date->format('Y-m-d') < $today;
    @endphp
    <tr>
      <td>{{ $r->tenant }}<span class="rf-sub">{{ $r->contact_person }}@if($r->contact_person && $r->phone) · @endif{{ $r->phone }}</span></td>
      <td>{{ $r->contract }}<span class="rf-sub">{{ $fmtDate($r->from) }} – {{ $fmtDate($r->to) }}</span></td>
      <td>{{ $r->building }}</td>
      <td>{{ $r->unit }}</td>
      <td>{{ $fmtDate($r->last_received) }}</td>
      <td class="num">{{ number_format($r->due, 3) }}</td>
      <td class="num">{{ number_format($r->received, 3) }}</td>
      <td class="num"><strong>{{ number_format($r->balance, 3) }}</strong></td>
      <td id="rf-last-{{ $r->id }}">@if($last){{ $last->created_at->format('d/m/Y H:i') }}<span class="rf-sub">{{ $last->creatorName() }}</span>@else — @endif</td>
      <td id="rf-promise-{{ $r->id }}" class="{{ $overdue ? 'rf-overdue' : '' }}">{{ $promise ? $promise->promised_date->format('d/m/Y') : '—' }}</td>
      <td style="white-space:nowrap">
        @can('add_receivable_followup')
        <button type="button" class="btn btn-primary btn-xs rf-followup" data-url="{{ route('receivable-followup.form', $r->id) }}?as_on={{ $asOn }}">Follow-up</button>
        @endcan
        <button type="button" class="btn btn-default btn-xs rf-view" data-url="{{ route('receivable-followup.timeline', $r->id) }}?as_on={{ $asOn }}">View</button>
      </td>
    </tr>
  @empty
    <tr><td colspan="11" class="text-center">No agreements with a balance for these filters.</td></tr>
  @endforelse
  </tbody>
  @if($rows->total())
  <tfoot><tr class="rf-totals">
    <td colspan="5">Total ({{ $rows->total() }} agreements)</td>
    <td class="num">{{ number_format($totals['due'], 3) }}</td>
    <td class="num">{{ number_format($totals['received'], 3) }}</td>
    <td class="num">{{ number_format($totals['balance'], 3) }}</td>
    <td colspan="3"></td>
  </tr></tfoot>
  @endif
</table>
</div>
{{ $rows->links() }}
```

Note: the overdue check uses only the latest promise; every listed agreement has balance > 0, so "passed and still owed" equals "date < today" here.

- [ ] **Step 7: Run the feature tests**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Feature/ReceivableFollowupTest.php`
Expected: `OK` (ARE tests may report `S` skipped if no matching local user — note which in the task report).

- [ ] **Step 8: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/ReceivableFollowupController.php Modules/BackOffice/Routes/web.php Modules/BackOffice/Routes/breadcrumbs.php Modules/BackOffice/Resources/views/ReceivableFollowup/index.blade.php Modules/BackOffice/Resources/views/ReceivableFollowup/_rows.blade.php tests/Feature/ReceivableFollowupTest.php
git commit -m "feat(receivable-followup): list with month, building, management and search filters

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 5: Follow-up form, save and timeline

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/ReceivableFollowupController.php` (add `form`, `store`, `timeline`, private `contractInScope`, private `header`)
- Create: `Modules/BackOffice/Resources/views/ReceivableFollowup/_header.blade.php`, `_form.blade.php`, `_timeline.blade.php`
- Modify: `Modules/BackOffice/Resources/views/ReceivableFollowup/index.blade.php` (modal scripts)
- Test: `tests/Feature/ReceivableFollowupTest.php` (append tests)

**Interfaces:**
- Consumes: routes `receivable-followup.form|store|timeline` (Task 4), `.rf-followup` / `.rf-view` buttons with `data-url`, cells `#rf-last-{id}` / `#rf-promise-{id}` (Task 4), `ReceivableFigures::cached()` (Task 2), `ReceivableFollowup::promiseStatus()` / `creatorName()` (Task 1).
- Produces: `POST receivable-followup/{contract}` → JSON `{last: 'd/m/Y H:i', by: string, promised: 'd/m/Y'|null, overdue: bool}`; 422 JSON on validation errors; 403 outside ARE scope.

- [ ] **Step 1: Append the failing tests** to `tests/Feature/ReceivableFollowupTest.php` (inside the class):
```php
    private function save($c, array $data)
    {
        return $this->postJson(route('receivable-followup.store', $c->id), $data + ['as_on' => $this->asOn]);
    }

    public function test_save_captures_user_time_and_balance()
    {
        $c = $this->contracts[1];
        $this->save($c, ['comment' => 'Called, will pay Sunday', 'promised_date' => Carbon::today()->addDays(3)->format('Y-m-d')])
            ->assertOk()->assertJson(['promised' => Carbon::today()->addDays(3)->format('d/m/Y'), 'overdue' => false]);
        $f = ReceivableFollowup::where('tenant_contract_id', $c->id)->latest('id')->first();
        $this->assertSame(1, (int) $f->created_by);
        $this->assertEquals(1500.0, (float) $f->balance_at_followup);
        $this->assertTrue($f->created_at->gt(Carbon::now()->subMinute()));
    }

    public function test_save_without_promised_date()
    {
        $c = $this->contracts[0];
        $this->save($c, ['comment' => 'No answer'])->assertOk()->assertJson(['promised' => null]);
        $this->assertNull(ReceivableFollowup::where('tenant_contract_id', $c->id)->latest('id')->first()->promised_date);
    }

    public function test_comment_required_and_max_1000()
    {
        $c = $this->contracts[0];
        $this->save($c, ['comment' => ''])->assertStatus(422)->assertJsonValidationErrors('comment');
        $this->save($c, ['comment' => str_repeat('x', 1001)])->assertStatus(422)->assertJsonValidationErrors('comment');
    }

    public function test_promised_date_today_ok_yesterday_rejected()
    {
        $c = $this->contracts[0];
        $this->save($c, ['comment' => 'today', 'promised_date' => Carbon::today()->format('Y-m-d')])->assertOk();
        $this->save($c, ['comment' => 'past', 'promised_date' => Carbon::yesterday()->format('Y-m-d')])
            ->assertStatus(422)->assertJsonValidationErrors('promised_date');
    }

    public function test_save_without_cached_figures_keeps_balance_null()
    {
        Cache::forget(ReceivableFigures::key($this->asOn));
        $c = $this->contracts[0];
        $this->save($c, ['comment' => 'cache expired'])->assertOk();
        $this->assertNull(ReceivableFollowup::where('tenant_contract_id', $c->id)->latest('id')->first()->balance_at_followup);
    }

    public function test_save_needs_add_permission()
    {
        $user = User::all()->first(function ($u) { return $u->can('view_receivable_followup') && !$u->can('add_receivable_followup'); });
        if (!$user) {
            $this->markTestSkipped('No local user with view but without add.');
        }
        $this->actingAs($user);
        $this->save($this->contracts[0], ['comment' => 'x'])->assertStatus(403);
        $this->assertNotContains('rf-followup', $this->rows());
    }

    public function test_are_cannot_save_or_view_outside_own_buildings()
    {
        $are = User::role('are')->get()->first(function ($u) {
            return $u->roles->count() === 1 && !DB::table('are_buildings')->where('user_id', $u->id)->exists();
        });
        if (!$are) {
            $this->markTestSkipped('No local ARE user without buildings.');
        }
        $this->actingAs($are);
        $c = $this->contracts[0];
        $this->save($c, ['comment' => 'x'])->assertStatus(403);
        $this->get(route('receivable-followup.timeline', $c->id))->assertStatus(403);
    }

    public function test_form_shows_header()
    {
        $c = $this->contracts[1];
        $html = $this->get(route('receivable-followup.form', $c->id) . '?as_on=' . $this->asOn)->assertOk()->getContent();
        $this->assertContains($c->tenant_contract_no, $html);
        $this->assertContains('1,500.000', $html);
        $this->assertContains('name="comment"', $html);
        $this->assertContains('name="promised_date"', $html);
    }

    public function test_timeline_newest_first_with_status()
    {
        $c = $this->contracts[0];
        ReceivableFollowup::create(['tenant_contract_id' => $c->id, 'comment' => 'first call', 'promised_date' => '2020-01-10', 'created_by' => 1, 'created_at' => '2020-01-01 10:00:00']);
        ReceivableFollowup::create(['tenant_contract_id' => $c->id, 'comment' => 'second call', 'promised_date' => Carbon::today()->addDays(5)->format('Y-m-d'), 'created_by' => 1]);
        $html = $this->get(route('receivable-followup.timeline', $c->id))->assertOk()->getContent();
        $this->assertTrue(strpos($html, 'second call') < strpos($html, 'first call'));
        $this->assertContains('rf-badge upcoming', $html);
        $this->assertRegExp('/rf-badge (kept|broken)/', $html);
    }

    public function test_timeline_escapes_comment()
    {
        $c = $this->contracts[0];
        ReceivableFollowup::create(['tenant_contract_id' => $c->id, 'comment' => '<script>alert(1)</script>', 'created_by' => 1]);
        $html = $this->get(route('receivable-followup.timeline', $c->id))->assertOk()->getContent();
        $this->assertNotContains('<script>alert(1)</script>', $html);
        $this->assertContains('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_timeline_empty_state()
    {
        $html = $this->get(route('receivable-followup.timeline', $this->contracts[2]->id))->assertOk()->getContent();
        $this->assertContains('No follow-ups yet.', $html);
    }
```

- [ ] **Step 2: Run to verify the new tests fail**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Feature/ReceivableFollowupTest.php`
Expected: the new tests FAIL (`Call to undefined method ...::store()` / `form()` / `timeline()`); Task 4 tests still pass.

- [ ] **Step 3: Add the controller methods**

In `ReceivableFollowupController`, add `use Modules\BackOffice\Entities\ReceiptsGeneration;` to the imports, then add these methods before the closing `}` of the class:
```php
    /** The contract, if it exists and is inside the user's ARE scope; 404 / 403 otherwise. */
    private function contractInScope($id)
    {
        $contract = TenantContract::with(['tenant', 'building', 'Unit'])->findOrFail($id);
        if (!TenantContract::areFilter()->where('tenant_contracts.id', $contract->id)->exists()) {
            abort(403);
        }
        return $contract;
    }

    /** Header shared by the form and the timeline. */
    private function header(TenantContract $contract, $asOn)
    {
        $cached = $asOn ? app(ReceivableFigures::class)->cached($asOn) : null;
        $fig = $cached && isset($cached['rows'][$contract->id]) ? $cached['rows'][$contract->id] : null;
        $lastPromise = ReceivableFollowup::where('tenant_contract_id', $contract->id)->whereNotNull('promised_date')
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
        return [
            'contract'    => $contract,
            'balance'     => $fig ? $fig['balance'] : null,
            'lastPromise' => $lastPromise ? $lastPromise->promised_date->format('d/m/Y') : null,
            'asOn'        => $asOn,
        ];
    }

    private function asOnParam(Request $request)
    {
        $v = (string) $request->input('as_on', '');
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;
    }

    public function form(Request $request, $contract)
    {
        $c = $this->contractInScope($contract);
        return view('backoffice::ReceivableFollowup._form', $this->header($c, $this->asOnParam($request)));
    }

    public function store(Request $request, $contract)
    {
        $c = $this->contractInScope($contract);
        $this->validate($request, [
            'comment'       => 'required|string|max:1000',
            'promised_date' => 'nullable|date|after_or_equal:today',
        ]);
        $h = $this->header($c, $this->asOnParam($request));
        $f = ReceivableFollowup::create([
            'tenant_contract_id'  => $c->id,
            'comment'             => trim($request->comment),
            'promised_date'       => $request->filled('promised_date') ? $request->promised_date : null,
            'balance_at_followup' => $h['balance'],
            'created_by'          => \Auth::user()->id,
        ]);
        $f->load('creator.employee');

        $promise = ReceivableFollowup::where('tenant_contract_id', $c->id)->whereNotNull('promised_date')
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
        return response()->json([
            'last'     => $f->created_at->format('d/m/Y H:i'),
            'by'       => $f->creatorName(),
            'promised' => $promise ? $promise->promised_date->format('d/m/Y') : null,
            'overdue'  => $promise ? $promise->promised_date->format('Y-m-d') < Carbon::today()->format('Y-m-d') : false,
        ]);
    }

    public function timeline(Request $request, $contract)
    {
        $c = $this->contractInScope($contract);
        $followups = ReceivableFollowup::with('creator.employee')->where('tenant_contract_id', $c->id)
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();
        $receiptDates = ReceiptsGeneration::where('tenant_contract_id', $c->id)->where('receipts_generation_type', 0)
            ->where(function ($q) { $q->whereNull('receipts_generation_status')->orWhere('receipts_generation_status', '!=', 2); })
            ->whereNotNull('receipts_generation_receipt_date')->pluck('receipts_generation_receipt_date')
            ->map(function ($d) { return Carbon::parse($d)->format('Y-m-d'); })->all();
        $today = Carbon::today()->format('Y-m-d');
        $statusFor = function ($f) use ($receiptDates, $today) {
            return ReceivableFollowup::promiseStatus($f->promised_date, $f->created_at, $receiptDates, $today);
        };
        return view('backoffice::ReceivableFollowup._timeline', $this->header($c, $this->asOnParam($request)) + compact('followups', 'statusFor'));
    }
```

- [ ] **Step 4: Shared header + form modal**

`Modules/BackOffice/Resources/views/ReceivableFollowup/_form.blade.php`:
```blade
<div class="modal-dialog" role="document"><div class="modal-content">
  <div class="modal-header"><h4 class="modal-title">Follow-up</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
  <div class="modal-body">
    @include('backoffice::ReceivableFollowup._header')
    <form id="rf-form" action="{{ route('receivable-followup.store', $contract->id) }}" method="post">
      @csrf
      <input type="hidden" name="as_on" value="{{ $asOn }}">
      <div class="rf-error alert alert-danger" role="alert" hidden></div>
      <div class="form-group">
        <label for="rf-comment">Comment <small class="textRed">*</small></label>
        <textarea id="rf-comment" name="comment" class="form-control" rows="4" maxlength="1000" required></textarea>
      </div>
      <div class="form-group">
        <label for="rf-promised">Promised Date</label>
        <input type="date" id="rf-promised" name="promised_date" class="form-control" min="{{ date('Y-m-d') }}">
        <small class="rf-sub">Leave empty when the tenant gave no date.</small>
      </div>
      <button type="submit" class="btn btn-primary">Save follow-up</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
    </form>
  </div>
</div></div>
```

`Modules/BackOffice/Resources/views/ReceivableFollowup/_header.blade.php` (add this file too — listed under Files for Task 5):
```blade
<dl class="row" style="font-size:13px;margin-bottom:12px">
  <dt class="col-sm-4">Tenant</dt><dd class="col-sm-8">{{ optional($contract->tenant)->tenant_name }}<span class="rf-sub">{{ optional($contract->tenant)->tenant_contact_no }}</span></dd>
  <dt class="col-sm-4">Agreement</dt><dd class="col-sm-8">{{ $contract->tenant_contract_no }}</dd>
  <dt class="col-sm-4">Building / Unit</dt><dd class="col-sm-8">{{ optional($contract->building)->building_name }} / {{ optional($contract->Unit)->unit_no }}</dd>
  <dt class="col-sm-4">Balance</dt><dd class="col-sm-8">{{ $balance !== null ? number_format($balance, 3) : '—' }}</dd>
  <dt class="col-sm-4">Last promised date</dt><dd class="col-sm-8">{{ $lastPromise ?: '—' }}</dd>
</dl>
```

- [ ] **Step 5: Timeline modal**

`Modules/BackOffice/Resources/views/ReceivableFollowup/_timeline.blade.php`:
```blade
@php $labels = ['kept' => 'Kept', 'broken' => 'Broken', 'upcoming' => 'Upcoming']; @endphp
<div class="modal-dialog modal-lg" role="document"><div class="modal-content">
  <div class="modal-header"><h4 class="modal-title">Follow-up history</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
  <div class="modal-body">
    @include('backoffice::ReceivableFollowup._header')
    @if($followups->isEmpty())
      <p class="rf-sub">No follow-ups yet.</p>
    @else
      <ul class="rf-tl">
        @foreach($followups as $f)
          @php $st = $statusFor($f); @endphp
          <li>
            <div class="who">{{ $f->created_at->format('d/m/Y H:i') }} · {{ $f->creatorName() }}@if($f->balance_at_followup !== null) · balance {{ number_format($f->balance_at_followup, 3) }}@endif</div>
            <div style="white-space:pre-wrap">{{ $f->comment }}</div>
            @if($f->promised_date)
              <div>Promised {{ $f->promised_date->format('d/m/Y') }} <span class="rf-badge {{ $st }}">{{ $labels[$st] }}</span></div>
            @endif
          </li>
        @endforeach
      </ul>
    @endif
  </div>
</div></div>
```

- [ ] **Step 6: Modal scripts in `index.blade.php`**

In `index.blade.php`, inside the `(function () { ... })();` block, directly before `window.rfReload = load;`, add:
```js
  function openModal(url) {
    $('#rf-modal').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body">Loading…</div></div></div>').modal('show');
    $.get(url).done(function (html) { $('#rf-modal').html(html); })
      .fail(function (x) { $('#rf-modal .modal-body').text(x.status === 403 ? 'You cannot open this agreement.' : 'Could not load. Please try again.'); });
  }
  $(document).on('click', '.rf-followup, .rf-view', function () { openModal($(this).attr('data-url')); });
  $(document).on('submit', '#rf-form', function (e) {
    e.preventDefault();
    var form = this, $btn = $(form).find('button[type=submit]').prop('disabled', true), id = form.action.split('/').pop();
    $(form).find('.rf-error').prop('hidden', true).empty();
    $.ajax({method: 'POST', url: form.action, data: $(form).serialize(), headers: {'Accept': 'application/json'}})
      .done(function (r) {
        $('#rf-last-' + id).empty().append(document.createTextNode(r.last)).append($('<span class="rf-sub">').text(r.by));
        $('#rf-promise-' + id).text(r.promised || '—').toggleClass('rf-overdue', !!r.overdue);
        $('#rf-modal').modal('hide');
      })
      .fail(function (x) {
        var r = x.responseJSON || {}, msgs = [];
        if (r.errors) { $.each(r.errors, function (k, v) { msgs.push(v[0]); }); } else { msgs.push(r.message || 'Could not save. Please try again.'); }
        var $e = $(form).find('.rf-error').prop('hidden', false);
        $.each(msgs, function (i, m) { $e.append($('<div>').text(m)); });
      })
      .always(function () { $btn.prop('disabled', false); });
  });
```

- [ ] **Step 7: Run all the tests for this feature**

Run each:
`C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Feature/ReceivableFollowupTest.php`
`C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Feature/ReceivableFollowupRefreshTest.php`
`C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/ReceivableFiguresTest.php`
`C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/ReceivableFollowupStatusTest.php`
Expected: all OK (skips allowed only for the "no matching local user" tests; list them in the report).

- [ ] **Step 8: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/ReceivableFollowupController.php Modules/BackOffice/Resources/views/ReceivableFollowup tests/Feature/ReceivableFollowupTest.php
git commit -m "feat(receivable-followup): follow-up form, save and history timeline

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 6: Browser check

**Files:** none (verification only).

- [ ] **Step 1: Warm the cache for last month** (one real ~15 s run):
`C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan receivables:refresh-followup`
Expected: `2026-09-30: <about 3400> contracts`.

- [ ] **Step 2: Serve the site** with the scratchpad dev router (`php -S localhost:8001 -t . <scratchpad>/router.php`, run in background) and log in through `/__devlogin` (user 1).

- [ ] **Step 3: Check in Chrome** at `http://localhost:8001/receivable-followup`:
  - last month selected, rows load, sorted by balance, totals row, "Figures as of" line;
  - building / management / search filters change the rows; paging and header sorting work;
  - Follow-up opens the modal with the header; submitting an empty comment shows the browser's required message (no request);
  - View opens the timeline (empty state on a contract without follow-ups);
  - no console errors.
  Do **not** click Save with real data (it would write to the local DB); saving is covered by tests.

- [ ] **Step 4: Stop the dev server** (`Get-NetTCPConnection -LocalPort 8001 -State Listen | Stop-Process`).
