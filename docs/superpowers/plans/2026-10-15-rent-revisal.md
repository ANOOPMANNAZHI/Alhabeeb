# Rent Revisal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let the building's ARE send a rent revisal to the tenant 90–120 days before expiry, email the ARE at 120 days, and make the renewal screens default to the revised rent — without changing the existing renewal process.

**Architecture:** A new `tenant_rent_revisals` table (one row per contract) plus a notices table for the 120-day ARE emails. A `RentRevisal` service owns the window rule and ARE scope; a `RentRevisalController` serves a new "Rent revisal" tab beside Due for Renewal and its popup; a daily command sends the ARE emails. Two new system templates go through the existing `SystemMessenger`, which gains an optional Reply-To. The renewal screens only *read* the revisal as a fallback.

**Tech Stack:** Laravel 5.7, PHP (live < 7.4), PostgreSQL, nWidart modules (`Modules/BackOffice`), Spatie permissions, PHPUnit feature tests with `DatabaseTransactions` against the local `plms_live` DB.

**Spec:** `docs/superpowers/specs/2026-10-15-rent-revisal-design.md`

## Global Constraints

- Nothing in the existing renewal process changes: Due for Renewal list, its 3-month `renewal_notification` setting, its buttons, renewal stages 300–304 and the renewal form email stay as they are.
- Window: active contracts (`tenant_contract_status = 1`) ending in **90–120 days** from today, inclusive; `tenant_renewal_termination_status` in **(0, 1, 4)**.
- One revisal per contract; locked after it is sent. Revisal = new monthly rent (numeric, > 0, 3 decimals) + optional note (max 500 chars).
- Tenant email only when a revisal is entered; **Reply-To = the ARE**; **no SMS**.
- ARE email once per contract at 120 days, only if no revisal; daily at **08:00**.
- Renewal screens rent default: renewal form already sent → revisal rent → contract rent.
- Permission `rent_revisal` for roles `are`, `are_team_lead`, `backoffice_manager`, `super_admin`.
- Live PHP < 7.4: no arrow functions (`fn`), no nullsafe `?->`, no `match`, no typed properties. Match the surrounding code style (`isset(...) ? ... : ...`).
- Run tests with PHP 7.4: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -d memory_limit=1G vendor/bin/phpunit --filter RentRevisal`.
- Migrations run with `--path` only (never plain `php artisan migrate`; older migrations show "No" but are applied).

## Review Focus

1. Contract whose end date is exactly 90 or exactly 120 days away — must be in the window (both ends inclusive). → Task 3 tests.
2. Two people open the same contract's popup; the second Send must be refused, not create a second revisal or second email. → Task 4 test `second_revisal_is_refused`.
3. Rent typed with a comma ("1,250.500") — must save as 1250.500 like other PLMS rent fields (`replaceCommaWithDot` / strip thousands). → Task 4 test `rent_with_thousands_separator_saves`.
4. ARE email job run twice the same day, or after a missed day — exactly one email per contract. → Task 5 tests.
5. Renewal popup when both a renewal form and a revisal exist — the renewal form rent wins. → Task 6 test.

---

## File Structure

| File | Responsibility |
|---|---|
| `database/migrations/2026_10_15_000000_create_tenant_rent_revisals_table.php` | create `tenant_rent_revisals` + `tenant_rent_revisal_notices` |
| `database/migrations/2026_10_15_000001_seed_rent_revisal_templates_and_permission.php` | seed the 2 templates + `rent_revisal` permission |
| `Modules/BackOffice/Entities/TenantRentRevisal.php` | revisal model |
| `Modules/BackOffice/Entities/TenantRentRevisalNotice.php` | ARE-notice model |
| `Modules/Sales/Entities/TenantContract.php` | add `rentRevisal()` hasOne |
| `Modules/BackOffice/Services/SystemMessenger.php` | optional `reply_to` option |
| `Modules/BackOffice/Services/SystemTemplateDefaults.php` | the 2 template defaults |
| `Modules/BackOffice/Services/RentRevisal.php` | window query, scope, ARE lookup, save + send, renewal rent default |
| `Modules/BackOffice/Http/Controllers/RentRevisalController.php` | tab list, popup, store |
| `Modules/BackOffice/Resources/views/RentRevisal/index.blade.php` | Rent revisal tab |
| `Modules/BackOffice/Resources/views/RentRevisal/_tabs.blade.php` | tab bar shared with Due for Renewal |
| `Modules/BackOffice/Resources/views/RentRevisal/form.blade.php` | popup content |
| `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renewal_due_list.blade.php` | include the tab bar (only addition) |
| `Modules/BackOffice/Routes/web.php`, `Modules/BackOffice/Routes/breadcrumbs.php` | routes + breadcrumb |
| `Modules/BackOffice/Console/RentRevisalNotifyAre.php`, `Modules/BackOffice/Providers/BackOfficeServiceProvider.php`, `app/Console/Kernel.php` | daily ARE email |
| `Modules/BackOffice/Http/Controllers/TenantRenewalController.php` | rent default in 2 places |
| `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard/are.blade.php` | ARE dashboard card |
| `tests/Feature/RentRevisalTest.php` | all feature tests |

---

### Task 1: Tables, models and template defaults

**Files:**
- Create: `database/migrations/2026_10_15_000000_create_tenant_rent_revisals_table.php`
- Create: `database/migrations/2026_10_15_000001_seed_rent_revisal_templates_and_permission.php`
- Create: `Modules/BackOffice/Entities/TenantRentRevisal.php`, `Modules/BackOffice/Entities/TenantRentRevisalNotice.php`
- Modify: `Modules/Sales/Entities/TenantContract.php` (after `tenantRenewalFormLatest()`, ~line 167)
- Modify: `Modules/BackOffice/Services/SystemTemplateDefaults.php` (`all()` ~line 66–90)
- Test: `tests/Feature/RentRevisalTest.php`

**Interfaces:**
- Produces: `TenantRentRevisal` (fillable: tenant_contract_id, current_rent, revised_rent, note, email_status, emailed_at, created_by; relations `contract()`, `createdBy()`), `TenantRentRevisalNotice` (fillable: tenant_contract_id, are_user_id, email_status, sent_at), `TenantContract::rentRevisal()`, template keys `rent_revisal_due_are`, `rent_revision_tenant`, permission `rent_revisal`.

- [ ] **Step 1: Write the failing test** — create `tests/Feature/RentRevisalTest.php`:

```php
<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\TenantRentRevisal;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\BackOffice\Services\SystemTemplateDefaults;
use Modules\Sales\Entities\TenantContract;

/** Rent revisal (spec 2026-10-15): window, popup save + tenant email, ARE email job, renewal rent default. */
class RentRevisalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        SystemMessenger::$smsSender = function () { return true; };
        app('cache')->forget('spatie.permission.cache');
    }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function as($user)
    {
        $this->flushSession();
        return $this->actingAs($user);
    }

    private function makeUser($username, $role)
    {
        $emp = DB::table('employees')->insertGetId(['employee_code' => strtoupper($username), 'employee_name' => ucfirst($username),
            'employee_contact_address' => 'x', 'employee_contact_no' => '91234567', 'employee_dob' => '1990-01-01', 'designation_id' => 37,
            'employee_status' => 1, 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $roleId = DB::table('roles')->where('name', $role)->value('id');
        $id = DB::table('users')->insertGetId(['username' => $username, 'email' => $username . '@example.test', 'password' => bcrypt('x'),
            'user_type' => 'employee', 'user_type_id' => $emp, 'default_role' => $roleId, 'user_type_status' => 1, 'created_by' => 1,
            'created_at' => now(), 'updated_at' => now()]);
        DB::table('model_has_roles')->insert(['role_id' => $roleId, 'model_type' => 'App\User', 'model_id' => $id]);
        return User::find($id);
    }

    /** Make $user the only ARE of the contract's building (assign_to null = current). */
    private function assignAre(User $user, $buildingId)
    {
        DB::table('preferred_buildings')->where('building_id', $buildingId)->whereNull('assign_to')->update(['assign_to' => now()]);
        $areId = DB::table('are_buildings')->insertGetId(['user_id' => $user->id, 'building_id' => $buildingId, 'assign_from' => now(),
            'created_at' => now(), 'updated_at' => now()]);
        DB::table('preferred_buildings')->insert(['building_id' => $buildingId, 'are_building_id' => $areId, 'assign_from' => now(),
            'created_at' => now(), 'updated_at' => now()]);
    }

    /** An active contract with a tenant email, moved to end in $days days, normal renewal status. */
    private function contractEndingIn($days)
    {
        $id = DB::table('tenant_contracts as tc')->join('tenant as t', 't.id', '=', 'tc.tenant_id')
            ->where('tc.tenant_contract_status', 1)->whereNotNull('t.tenant_contact_email')->where('t.tenant_contact_email', '<>', '')
            ->orderBy('tc.id', 'desc')->value('tc.id');
        DB::table('tenant_contracts')->where('id', $id)->update([
            'tenant_contract_valid_to_date' => Carbon::today()->addDays($days)->toDateString(),
            'tenant_renewal_termination_status' => 0,
        ]);
        DB::table('tenant_rent_revisals')->where('tenant_contract_id', $id)->delete();
        DB::table('tenant_rent_revisal_notices')->where('tenant_contract_id', $id)->delete();
        return TenantContract::find($id);
    }

    public function test_tables_models_and_templates_exist()
    {
        $c = $this->contractEndingIn(100);
        $r = TenantRentRevisal::create(['tenant_contract_id' => $c->id, 'current_rent' => 300, 'revised_rent' => 330, 'created_by' => 1]);
        $this->assertEquals($r->id, $c->fresh()->rentRevisal->id);
        $this->assertNotNull(SystemTemplateDefaults::get('rent_revisal_due_are'));
        $this->assertNotNull(SystemTemplateDefaults::get('rent_revision_tenant'));
        $this->assertTrue(DB::table('message_templates')->where('key', 'rent_revision_tenant')->exists());
        $this->assertTrue(DB::table('permissions')->where('name', 'rent_revisal')->exists());
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -d memory_limit=1G vendor/bin/phpunit --filter test_tables_models_and_templates_exist`
Expected: FAIL — relation `tenant_rent_revisals` does not exist.

- [ ] **Step 3: Create the table migration** — `database/migrations/2026_10_15_000000_create_tenant_rent_revisals_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Rent revisal before renewal (spec 2026-10-15): one revisal per contract + the 120-day ARE emails sent. */
class CreateTenantRentRevisalsTable extends Migration
{
    public function up()
    {
        Schema::create('tenant_rent_revisals', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('tenant_contract_id')->unique();
            $t->decimal('current_rent', 15, 3);
            $t->decimal('revised_rent', 15, 3);
            $t->text('note')->nullable();
            $t->string('email_status', 20)->nullable();
            $t->timestamp('emailed_at')->nullable();
            $t->integer('created_by');
            $t->timestamps();
            $t->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
        });
        Schema::create('tenant_rent_revisal_notices', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('tenant_contract_id')->unique();
            $t->integer('are_user_id');
            $t->string('email_status', 20)->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
            $t->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_rent_revisal_notices');
        Schema::dropIfExists('tenant_rent_revisals');
    }
}
```

- [ ] **Step 4: Create the models**

`Modules/BackOffice/Entities/TenantRentRevisal.php`:

```php
<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

/** A rent revisal sent to the tenant before renewal (one per contract, locked once sent). */
class TenantRentRevisal extends Model
{
    protected $table = 'tenant_rent_revisals';
    protected $fillable = ['tenant_contract_id', 'current_rent', 'revised_rent', 'note', 'email_status', 'emailed_at', 'created_by'];
    protected $dates = ['emailed_at'];

    public function contract()
    {
        return $this->belongsTo('Modules\Sales\Entities\TenantContract', 'tenant_contract_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
```

`Modules/BackOffice/Entities/TenantRentRevisalNotice.php`:

```php
<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

/** The 120-day "start rent revisal" email sent to the ARE for a contract (at most one per contract). */
class TenantRentRevisalNotice extends Model
{
    protected $table = 'tenant_rent_revisal_notices';
    protected $fillable = ['tenant_contract_id', 'are_user_id', 'email_status', 'sent_at'];
    protected $dates = ['sent_at'];
}
```

In `Modules/Sales/Entities/TenantContract.php`, after `tenantRenewalFormLatest()`:

```php
    public function rentRevisal(){

      return $this->hasOne('Modules\BackOffice\Entities\TenantRentRevisal','tenant_contract_id');
    }
```

- [ ] **Step 5: Add the template defaults** — in `SystemTemplateDefaults::all()` change `] + self::c2() + self::c3();` to `] + self::c2() + self::c3() + self::rentRevisal();` and add this method after `c3()`:

```php
    /** Rent revisal before renewal (spec 2026-10-15). */
    private static function rentRevisal()
    {
        $contract = self::contractPlaceholders();
        $revisal = [
            'current_rent' => ['label' => 'Current monthly rent', 'sample' => '300.000'],
            'revised_rent' => ['label' => 'Revised monthly rent', 'sample' => '330.000'],
            'revisal_note' => ['label' => 'Note from the ARE', 'sample' => 'The revision follows the market rate for the area.'],
            'are_name'     => ['label' => 'ARE name', 'sample' => 'Rashid Al Balushi'],
            'are_email'    => ['label' => 'ARE email', 'sample' => 'rashid@alhabib.om'],
            'are_mobile'   => ['label' => 'ARE mobile', 'sample' => '99123456'],
        ];
        return [
            'rent_revisal_due_are' => [
                'name' => 'Rent revisal due (ARE)', 'audience' => 'staff', 'stage' => 'RR',
                'email_subject' => 'Contract expiring soon - start rent revisal if needed - {building} {unit}',
                'email_body' => '<p>Dear {employee_name},</p>'
                    . '<p>The contract below ends on <strong>{contract_end}</strong>. If the rent is to be revised on renewal, '
                    . 'please enter the revised rent in PLMS under Due for Renewal &gt; Rent revisal before it is 90 days from expiry.</p>'
                    . '<p>Tenant: {tenant_name}<br>Contract: {contract_no}<br>Building: {building}, unit {unit}<br>Current rent: OMR {rent}</p>'
                    . '<p><a href="{revisal_link}">Open Rent revisal</a></p>' . self::regards(),
                'sms_body' => null, 'is_active' => true, 'sms_enabled' => false,
                'placeholders' => self::employeePlaceholders() + $contract
                    + ['revisal_link' => ['label' => 'Link to the Rent revisal tab', 'sample' => 'https://plms.alhabib.om/tenantRenewal/rentRevisal']],
            ],
            'rent_revision_tenant' => [
                'name' => 'Rent revision notice (tenant)', 'audience' => 'tenant', 'stage' => 'RR',
                'email_subject' => 'Rent revision on renewal - {building} {unit}',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>Your tenancy contract {contract_no} for {building}, unit {unit} ends on {contract_end}.</p>'
                    . '<p>On renewal, the monthly rent will be <strong>OMR {revised_rent}</strong> (currently OMR {current_rent}).</p>'
                    . '{revisal_note}'
                    . '<p>For any questions please reply to this email or contact {are_name} on {are_mobile}.</p>' . self::regards(),
                'sms_body' => null, 'is_active' => true, 'sms_enabled' => false,
                'placeholders' => $contract + $revisal,
            ],
        ];
    }
```

- [ ] **Step 6: Create the seed migration** — `database/migrations/2026_10_15_000001_seed_rent_revisal_templates_and_permission.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/** Rent revisal templates (insert-only) and the rent_revisal permission. Run after the code is uploaded. */
class SeedRentRevisalTemplatesAndPermission extends Migration
{
    const KEYS = ['rent_revisal_due_are', 'rent_revision_tenant'];
    const ROLES = ['are', 'are_team_lead', 'backoffice_manager', 'super_admin'];

    public function up()
    {
        SystemTemplateDefaults::seed(self::KEYS);
        if (!DB::table('permissions')->where('name', 'rent_revisal')->exists()) {
            // permissions.menu_id is NOT NULL: hang it on the Due for Renewal menu row.
            $menuId = DB::table('menu')->where('route_name', 'tenantRenewal.index')->value('id');
            $pid = DB::table('permissions')->insertGetId(['name' => 'rent_revisal', 'guard_name' => 'web', 'menu_id' => $menuId,
                'created_at' => now(), 'updated_at' => now()]);
            foreach (DB::table('roles')->whereIn('name', self::ROLES)->pluck('id') as $roleId) {
                DB::table('role_has_permissions')->insert(['permission_id' => $pid, 'role_id' => $roleId]);
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        DB::table('message_templates')->whereIn('key', self::KEYS)->delete();
        foreach (DB::table('permissions')->where('name', 'rent_revisal')->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        app('cache')->forget('spatie.permission.cache');
    }
}
```

Before writing it, confirm the Due for Renewal menu row: `"/c/Program Files/PostgreSQL/18/bin/psql.exe" -U postgres -h 127.0.0.1 -d plms_live -tA -c "select id, route_name from menu where route_name='tenantRenewal.index'"` (PGPASSWORD=123456). If the route name differs, use the row that opens Due for Renewal.

- [ ] **Step 7: Run both migrations locally**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=database/migrations/2026_10_15_000000_create_tenant_rent_revisals_table.php` then the same with `2026_10_15_000001_seed_rent_revisal_templates_and_permission.php`.
Expected: both "Migrated".

- [ ] **Step 8: Run the test to verify it passes**

Run: `... vendor/bin/phpunit --filter test_tables_models_and_templates_exist`
Expected: PASS.

- [ ] **Step 9: Commit**

```bash
git add database/migrations/2026_10_15_00000*.php Modules/BackOffice/Entities/TenantRentRevisal*.php Modules/Sales/Entities/TenantContract.php Modules/BackOffice/Services/SystemTemplateDefaults.php tests/Feature/RentRevisalTest.php
git commit -m "feat(rent-revisal): tables, models, templates and permission"
```

---

### Task 2: Reply-To in SystemMessenger

**Files:**
- Modify: `Modules/BackOffice/Services/SystemMessenger.php` (email send block, ~line 98–110)
- Test: `tests/Feature/RentRevisalTest.php`

**Interfaces:**
- Produces: `SystemMessenger::send($key, MessageRecipient $to, array $values, array $options)` accepts `$options['reply_to'] = ['email' => string, 'name' => string|null]`; applied to the email only; ignored when the email is empty.

- [ ] **Step 1: Write the failing test** — add to `RentRevisalTest`:

```php
    public function test_system_messenger_sets_reply_to_only_when_given()
    {
        $to = new \Modules\BackOffice\Services\MessageRecipient('Tenant', 'tenant@example.test');
        $values = ['tenant_name' => 'Tenant', 'contract_no' => 'TAG1', 'building' => 'B', 'unit' => '1', 'contract_end' => '01/01/2027',
            'revised_rent' => '330.000', 'current_rent' => '300.000', 'revisal_note' => '', 'are_name' => 'Rashid', 'are_mobile' => '9'];
        SystemMessenger::send('rent_revision_tenant', $to, $values, ['reply_to' => ['email' => 'are@example.test', 'name' => 'Rashid']]);
        SystemMessenger::send('rent_revision_tenant', $to, $values);
        Mail::assertSent(TemplatedMail::class, function ($m) { return $m->hasReplyTo('are@example.test'); });
        Mail::assertSent(TemplatedMail::class, function ($m) { return !$m->hasReplyTo('are@example.test'); });
    }
```

- [ ] **Step 2: Run to verify it fails**

Run: `... vendor/bin/phpunit --filter test_system_messenger_sets_reply_to_only_when_given`
Expected: FAIL — no mail has Reply-To are@example.test.

- [ ] **Step 3: Implement** — in `SystemMessenger::deliver()`, replace

```php
                    $pending->send(new TemplatedMail($out['subject'], $out['html'], isset($options['attachments']) ? $options['attachments'] : []));
```

with

```php
                    $mail = new TemplatedMail($out['subject'], $out['html'], isset($options['attachments']) ? $options['attachments'] : []);
                    if (!empty($options['reply_to']['email'])) {
                        $mail->replyTo($options['reply_to']['email'], isset($options['reply_to']['name']) ? $options['reply_to']['name'] : null);
                    }
                    $pending->send($mail);
```

Also extend the class docblock with one line: `Option 'reply_to' => ['email' => .., 'name' => ..] sets Reply-To on the email.`

- [ ] **Step 4: Run to verify it passes**, then the whole messenger-related suite to check nothing else broke:

Run: `... vendor/bin/phpunit --filter "RentRevisal|StaffMessages|TenantMessages|SystemMessenger"`
Expected: new test PASS; `StaffMessagesTest::test_legal_alert_for_users_by_role_dedupes_across_roles` may fail — it fails on master already (pre-existing); anything else failing is a regression to fix.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/SystemMessenger.php tests/Feature/RentRevisalTest.php
git commit -m "feat(messages): optional Reply-To on system emails"
```

---

### Task 3: RentRevisal service — window, scope, ARE lookup

**Files:**
- Create: `Modules/BackOffice/Services/RentRevisal.php`
- Test: `tests/Feature/RentRevisalTest.php`

**Interfaces:**
- Consumes: `TenantContract`, `TenantRentRevisal`, relations `building`, `building->areBuildings` (pivot `preferred_buildings`, `assign_to` null = current; `AreBuildingAssign` has `user_id`).
- Produces:
  - `RentRevisal::MIN_DAYS = 90`, `RentRevisal::MAX_DAYS = 120`, `RentRevisal::OPEN_STATUSES = [0, 1, 4]`
  - `RentRevisal::windowQuery($user)` → Eloquent `Builder` on `TenantContract` (in window, open status, scoped to `$user`)
  - `RentRevisal::inWindow(TenantContract $c)` → bool
  - `RentRevisal::canRevise(TenantContract $c, $user)` → bool (in window, no revisal, user in scope)
  - `RentRevisal::dueCount($user)` → int (window, no revisal)
  - `RentRevisal::areUsers(TenantContract $c)` → `Collection` of active `App\User`

- [ ] **Step 1: Write the failing tests** — add to `RentRevisalTest`:

```php
    public function test_window_includes_both_ends_and_excludes_outside()
    {
        $admin = User::find(1);
        foreach ([89 => false, 90 => true, 120 => true, 121 => false] as $days => $in) {
            $c = $this->contractEndingIn($days);
            $this->assertSame($in, \Modules\BackOffice\Services\RentRevisal::inWindow($c->fresh()), "day $days");
            $this->assertSame($in, \Modules\BackOffice\Services\RentRevisal::windowQuery($admin)->where('tenant_contracts.id', $c->id)->exists(), "query day $days");
        }
    }

    public function test_window_skips_termination_and_started_renewals()
    {
        $c = $this->contractEndingIn(100);
        foreach ([0 => true, 1 => true, 4 => true, 2 => false, 3 => false, 5 => false, 6 => false, 7 => false, 8 => false, 9 => false] as $status => $in) {
            DB::table('tenant_contracts')->where('id', $c->id)->update(['tenant_renewal_termination_status' => $status]);
            $this->assertSame($in, \Modules\BackOffice\Services\RentRevisal::inWindow($c->fresh()), "status $status");
        }
    }

    public function test_are_sees_only_own_buildings_and_due_count_skips_revised()
    {
        $are = $this->makeUser('rr_t_are', 'are');
        $other = $this->makeUser('rr_t_are2', 'are');
        $c = $this->contractEndingIn(100);
        $this->assignAre($are, $c->building_id);
        $svc = '\Modules\BackOffice\Services\RentRevisal';
        $this->assertTrue($svc::windowQuery($are)->where('tenant_contracts.id', $c->id)->exists());
        $this->assertFalse($svc::windowQuery($other)->where('tenant_contracts.id', $c->id)->exists());
        $this->assertTrue($svc::canRevise($c->fresh(), $are));
        $this->assertFalse($svc::canRevise($c->fresh(), $other));
        $this->assertEquals([$are->id], $svc::areUsers($c->fresh())->pluck('id')->all());
        $before = $svc::dueCount($are);
        TenantRentRevisal::create(['tenant_contract_id' => $c->id, 'current_rent' => 1, 'revised_rent' => 2, 'created_by' => $are->id]);
        $this->assertEquals($before - 1, $svc::dueCount($are));
        $this->assertFalse($svc::canRevise($c->fresh(), $are));
    }
```

- [ ] **Step 2: Run to verify they fail**

Run: `... vendor/bin/phpunit --filter "test_window_|test_are_sees_only"`
Expected: FAIL — class `RentRevisal` not found.

- [ ] **Step 3: Implement** — `Modules/BackOffice/Services/RentRevisal.php`:

```php
<?php

namespace Modules\BackOffice\Services;

use App\User;
use Carbon\Carbon;
use Modules\Sales\Entities\TenantContract;

/**
 * Rent revisal before renewal (spec 2026-10-15). A contract can be revised when it is active, ends in
 * 90-120 days (both inclusive) and its renewal has not started (tenant_renewal_termination_status 0/1/4).
 * ARE scope follows Due for Renewal's areFilter: an ARE sees the buildings assigned to them, an ARE team
 * lead (as only role) their team's buildings, everyone else all buildings.
 */
class RentRevisal
{
    const MIN_DAYS = 90;
    const MAX_DAYS = 120;
    const OPEN_STATUSES = [0, 1, 4];

    public static function windowQuery($user)
    {
        $q = TenantContract::query()
            ->where('tenant_contracts.tenant_contract_status', 1)
            ->whereIn('tenant_contracts.tenant_renewal_termination_status', self::OPEN_STATUSES)
            ->whereDate('tenant_contracts.tenant_contract_valid_to_date', '>=', Carbon::today()->addDays(self::MIN_DAYS)->toDateString())
            ->whereDate('tenant_contracts.tenant_contract_valid_to_date', '<=', Carbon::today()->addDays(self::MAX_DAYS)->toDateString());
        return self::scope($q, $user);
    }

    public static function inWindow(TenantContract $c)
    {
        if ((int) $c->tenant_contract_status !== 1 || !in_array((int) $c->tenant_renewal_termination_status, self::OPEN_STATUSES, true)) {
            return false;
        }
        $end = Carbon::parse($c->tenant_contract_valid_to_date)->startOfDay();
        $days = Carbon::today()->diffInDays($end, false);
        return $days >= self::MIN_DAYS && $days <= self::MAX_DAYS;
    }

    public static function canRevise(TenantContract $c, $user)
    {
        return self::inWindow($c) && !$c->rentRevisal()->exists()
            && self::windowQuery($user)->where('tenant_contracts.id', $c->id)->exists();
    }

    public static function dueCount($user)
    {
        return self::windowQuery($user)->doesntHave('rentRevisal')->count();
    }

    /** Active users currently assigned as ARE of the contract's building. */
    public static function areUsers(TenantContract $c)
    {
        $building = $c->building;
        if (!$building) {
            return collect();
        }
        $ids = $building->areBuildings()->pluck('user_id')->filter()->unique()->values()->all();
        return User::whereIn('id', $ids)->where('user_type_status', 1)->get();
    }

    private static function scope($q, $user)
    {
        if (!$user) {
            return $q->whereRaw('1 = 0');
        }
        if ($user->hasRole('are')) {
            $uid = $user->id;
            $q->whereHas('building', function ($b) use ($uid) {
                $b->whereHas('areBuildings', function ($a) use ($uid) {
                    $a->where('user_id', $uid);
                });
            });
        } elseif (count($user->roles) == 1 && $user->hasRole('are_team_lead')) {
            $uid = $user->id;
            $q->whereHas('building', function ($b) use ($uid) {
                $b->whereHas('areBuildings', function ($a) use ($uid) {
                    $a->where(function ($w) use ($uid) {
                        $w->whereHas('user', function ($u) use ($uid) {
                            $u->whereHas('employee', function ($e) use ($uid) {
                                $e->where('head_user', $uid);
                            });
                        })->orWhere('user_id', $uid);
                    });
                });
            });
        }
        return $q;
    }
}
```

Check before use: `AreBuildingAssign` must have a `user()` relation (it is used in `ViewDueRenewal::scopeAreFilter`); `TenantContract` must have `building()` (it does). If `AreBuildingAssign::user()` is missing, add `public function user(){ return $this->belongsTo('App\User','user_id'); }`.

- [ ] **Step 4: Run to verify they pass**

Run: `... vendor/bin/phpunit --filter "test_window_|test_are_sees_only"`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/RentRevisal.php tests/Feature/RentRevisalTest.php
git commit -m "feat(rent-revisal): window, ARE scope and due count"
```

---

### Task 4: Rent revisal tab, popup and save

**Files:**
- Modify: `Modules/BackOffice/Services/RentRevisal.php` (add `send()`, `tenantValues()`)
- Create: `Modules/BackOffice/Http/Controllers/RentRevisalController.php`
- Create: `Modules/BackOffice/Resources/views/RentRevisal/index.blade.php`, `_tabs.blade.php`, `form.blade.php`
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renewal_due_list.blade.php` (include `_tabs` only)
- Modify: `Modules/BackOffice/Routes/web.php` (before `Route::resource('tenantRenewal', ...)`, line 81), `Modules/BackOffice/Routes/breadcrumbs.php`
- Test: `tests/Feature/RentRevisalTest.php`

**Interfaces:**
- Consumes: `RentRevisal::windowQuery/canRevise/areUsers` (Task 3), `SystemMessenger::send(..., ['reply_to' => ...])` (Task 2), `ComplaintMessages::tenantRecipient($tenant, 'contact')`, `MessageValues::contract($c)`, `replaceCommaWithDot()` (global helper used by `tenantRenewalTemplateStore`).
- Produces:
  - `RentRevisal::send(TenantContract $c, $user, $rent, $note)` → `[TenantRentRevisal|null, string message]`; refuses (returns `[null, reason]`) when `!canRevise`.
  - Routes: `rentRevisal.index` GET `tenantRenewal/rentRevisal`; `rentRevisal.form` GET `tenantRenewal/rentRevisal/{tenant_contract}/form`; `rentRevisal.store` POST `tenantRenewal/rentRevisal/{tenant_contract}`. All behind `can:rent_revisal`.

- [ ] **Step 1: Write the failing tests** — add to `RentRevisalTest`:

```php
    public function test_are_sends_revisal_and_tenant_gets_email_with_reply_to()
    {
        $are = $this->makeUser('rr_t_are', 'are');
        $c = $this->contractEndingIn(100);
        $this->assignAre($are, $c->building_id);
        $this->as($are)->get(route('rentRevisal.index'))->assertStatus(200)->assertSee($c->tenant_contract_no);
        $this->as($are)->get(route('rentRevisal.form', $c->id))->assertStatus(200);
        $res = $this->as($are)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '330.500', 'note' => 'Market rate']);
        $res->assertStatus(200)->assertJson(['ok' => true]);
        $r = TenantRentRevisal::where('tenant_contract_id', $c->id)->first();
        $this->assertEquals(330.5, (float) $r->revised_rent);
        $this->assertEquals((float) $c->tenant_contract_rent, (float) $r->current_rent);
        $this->assertEquals('sent', $r->email_status);
        Mail::assertSent(TemplatedMail::class, function ($m) use ($are) {
            return $m->hasReplyTo($are->email) && strpos($m->bodyHtml, '330.500') !== false && strpos($m->bodyHtml, 'Market rate') !== false;
        });
    }

    public function test_second_revisal_is_refused()
    {
        $are = $this->makeUser('rr_t_are', 'are');
        $c = $this->contractEndingIn(100);
        $this->assignAre($are, $c->building_id);
        $this->as($are)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '330']);
        $this->as($are)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '400'])->assertStatus(422);
        $this->assertEquals(1, TenantRentRevisal::where('tenant_contract_id', $c->id)->count());
        Mail::assertSent(TemplatedMail::class, 1);
    }

    public function test_refused_outside_window_and_for_other_are()
    {
        $are = $this->makeUser('rr_t_are', 'are');
        $other = $this->makeUser('rr_t_are2', 'are');
        $c = $this->contractEndingIn(80);
        $this->assignAre($are, $c->building_id);
        $this->as($are)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '330'])->assertStatus(422);
        $c2 = $this->contractEndingIn(100);
        $this->as($other)->post(route('rentRevisal.store', $c2->id), ['revised_rent' => '330'])->assertStatus(422);
        $this->assertEquals(0, TenantRentRevisal::whereIn('tenant_contract_id', [$c->id, $c2->id])->count());
    }

    public function test_rent_with_thousands_separator_saves_and_bad_rent_is_rejected()
    {
        $admin = User::find(1);
        $c = $this->contractEndingIn(100);
        $this->as($admin)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '0'])->assertStatus(422);
        $this->as($admin)->post(route('rentRevisal.store', $c->id), ['revised_rent' => 'abc'])->assertStatus(422);
        $this->as($admin)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '1,250.500'])->assertStatus(200);
        $this->assertEquals(1250.5, (float) TenantRentRevisal::where('tenant_contract_id', $c->id)->value('revised_rent'));
    }

    public function test_tenant_without_email_saves_with_status()
    {
        $admin = User::find(1);
        $c = $this->contractEndingIn(100);
        DB::table('tenant')->where('id', $c->tenant_id)->update(['tenant_contact_email' => null, 'tenant_personal_email' => null]);
        $res = $this->as($admin)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '330']);
        $res->assertStatus(200)->assertJsonFragment(['email_status' => 'skipped_no_address']);
        Mail::assertNothingSent();
    }

    public function test_user_without_permission_is_refused()
    {
        $user = $this->makeUser('rr_t_tech', 'maintenance_technician');
        $c = $this->contractEndingIn(100);
        $this->as($user)->get(route('rentRevisal.index'))->assertStatus(403);
        $this->as($user)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '330'])->assertStatus(403);
    }
```

(If role `maintenance_technician` does not exist locally, pick any role without `rent_revisal`: `select name from roles`.)

- [ ] **Step 2: Run to verify they fail**

Run: `... vendor/bin/phpunit --filter "test_are_sends|test_second_revisal|test_refused_outside|test_rent_with_thousands|test_tenant_without_email|test_user_without_permission"`
Expected: FAIL — route `rentRevisal.index` not defined.

- [ ] **Step 3: Add `send()` and `tenantValues()` to `RentRevisal`**

```php
    /**
     * Save the revisal and email the tenant (Reply-To = the first ARE of the building, else the sender).
     * @return array [TenantRentRevisal|null, string message for the user]
     */
    public static function send(TenantContract $c, $user, $rent, $note)
    {
        if (!self::canRevise($c, $user)) {
            return [null, 'This contract cannot be revised: it is outside the 90-120 day window, already revised, or not one of your buildings.'];
        }
        $note = trim((string) $note) !== '' ? trim((string) $note) : null;
        $revisal = \DB::transaction(function () use ($c, $user, $rent, $note) {
            // lock the contract row so two Sends cannot both pass canRevise
            TenantContract::where('id', $c->id)->lockForUpdate()->first();
            if ($c->rentRevisal()->exists()) {
                return null;
            }
            return \Modules\BackOffice\Entities\TenantRentRevisal::create([
                'tenant_contract_id' => $c->id, 'current_rent' => (float) $c->tenant_contract_rent,
                'revised_rent' => $rent, 'note' => $note, 'created_by' => $user->id,
            ]);
        });
        if (!$revisal) {
            return [null, 'This contract has already been revised.'];
        }
        $are = self::areUsers($c)->first() ?: $user;
        $areEmp = $are->employee;
        $to = ComplaintMessages::tenantRecipient($c->tenant, 'contact');
        $r = SystemMessenger::send('rent_revision_tenant', $to, self::tenantValues($c, $revisal, $are), [
            'reply_to' => ['email' => $are->email, 'name' => $areEmp && $areEmp->employee_name ? $areEmp->employee_name : $are->username],
            'related' => ['contract', $c->id],
            'blocks' => ['revisal_note'],
        ]);
        $revisal->update(['email_status' => $r['email'], 'emailed_at' => $r['email'] === \Modules\BackOffice\Entities\MessageLog::SENT ? now() : null]);
        switch ($r['email']) {
            case \Modules\BackOffice\Entities\MessageLog::SENT:
                return [$revisal, 'Revisal saved and emailed to the tenant.'];
            case \Modules\BackOffice\Entities\MessageLog::SKIPPED_NO_ADDRESS:
                return [$revisal, 'Revisal saved. No email on file for this tenant - inform the tenant another way.'];
            case \Modules\BackOffice\Entities\MessageLog::SKIPPED_OFF:
                return [$revisal, 'Revisal saved. The tenant email is switched off in Mail > Templates.'];
            default:
                return [$revisal, 'Revisal saved, but the email could not be sent (see Mail > Sent log).'];
        }
    }

    public static function tenantValues(TenantContract $c, $revisal, $are)
    {
        $emp = $are ? $are->employee : null;
        $note = $revisal->note ? '<p>' . nl2br(e($revisal->note)) . '</p>' : '';
        return MessageValues::contract($c) + [
            'current_rent' => number_format((float) $revisal->current_rent, 3),
            'revised_rent' => number_format((float) $revisal->revised_rent, 3),
            'revisal_note' => $note,
            'are_name'     => $emp && $emp->employee_name ? $emp->employee_name : ($are ? $are->username : ''),
            'are_email'    => $are ? (string) $are->email : '',
            'are_mobile'   => $emp ? (string) ($emp->employee_contact_no ?: $emp->employee_secondary_no) : '',
        ];
    }
```

Check `MessageTemplateRenderer::render($template, $values, $blocks)`: confirm how `blocks` marks a placeholder as raw HTML (look at how `inspection_details` is passed in `TenantMessages`) and pass `revisal_note` the same way, so the `<p>` is not escaped.

- [ ] **Step 4: Create the controller** — `Modules/BackOffice/Http/Controllers/RentRevisalController.php`:

```php
<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackOffice\Services\RentRevisal;
use Modules\Sales\Entities\TenantContract;

/** Due for Renewal > Rent revisal tab, its popup and save (spec 2026-10-15). */
class RentRevisalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:rent_revisal']);
    }

    public function index(Request $request)
    {
        $user = \Auth::user();
        $q = RentRevisal::windowQuery($user)->with(['tenant', 'building', 'unit', 'rentRevisal.createdBy.employee'])
            ->orderBy('tenant_contracts.tenant_contract_valid_to_date');
        if (trim((string) $request->search) !== '') {
            $term = '%' . trim($request->search) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('tenant_contracts.tenant_contract_no', 'ilike', $term)
                  ->orWhereHas('tenant', function ($t) use ($term) { $t->where('tenant_name', 'ilike', $term); })
                  ->orWhereHas('building', function ($b) use ($term) { $b->where('building_name', 'ilike', $term); });
            });
        }
        if ($request->revisal === 'pending') {
            $q->doesntHave('rentRevisal');
        } elseif ($request->revisal === 'done') {
            $q->has('rentRevisal');
        }
        $contracts = $q->paginate(25)->appends($request->only('search', 'revisal'));
        return view('backoffice::RentRevisal.index', compact('contracts', 'request'));
    }

    public function form(TenantContract $tenant_contract)
    {
        $user = \Auth::user();
        $revisal = $tenant_contract->rentRevisal;
        if (!$revisal && !RentRevisal::canRevise($tenant_contract, $user)) {
            return response('This contract cannot be revised.', 422);
        }
        return view('backoffice::RentRevisal.form', ['contract' => $tenant_contract, 'revisal' => $revisal]);
    }

    public function store(Request $request, TenantContract $tenant_contract)
    {
        $rent = str_replace(',', '', trim((string) $request->revised_rent));
        if (!is_numeric($rent) || (float) $rent <= 0) {
            return response()->json(['ok' => false, 'message' => 'Enter the new monthly rent as a number above 0.'], 422);
        }
        if (mb_strlen((string) $request->note) > 500) {
            return response()->json(['ok' => false, 'message' => 'The note can be at most 500 characters.'], 422);
        }
        list($revisal, $message) = RentRevisal::send($tenant_contract, \Auth::user(), round((float) $rent, 3), $request->note);
        if (!$revisal) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }
        return response()->json(['ok' => true, 'message' => $message, 'email_status' => $revisal->email_status]);
    }
}
```

(`replaceCommaWithDot` turns a decimal comma into a dot; here commas are thousands separators as in "1,250.500", so they are stripped instead. Keep this choice; Review Focus 3.)

- [ ] **Step 5: Routes and breadcrumb**

In `Modules/BackOffice/Routes/web.php`, directly **above** `Route::resource('tenantRenewal', 'TenantRenewalController');` (line 81; it must come first or `tenantRenewal/{id}` swallows `rentRevisal`):

```php
// Rent revisal before renewal (spec 2026-10-15)
Route::get('tenantRenewal/rentRevisal', 'RentRevisalController@index')->name('rentRevisal.index');
Route::get('tenantRenewal/rentRevisal/{tenant_contract}/form', 'RentRevisalController@form')->name('rentRevisal.form');
Route::post('tenantRenewal/rentRevisal/{tenant_contract}', 'RentRevisalController@store')->name('rentRevisal.store');
```

In `Modules/BackOffice/Routes/breadcrumbs.php`, next to the `tenantRenewal.index` breadcrumb:

```php
Breadcrumbs::for('rentRevisal.index', function ($trail) {
    $trail->parent('tenantRenewal.index');
    $trail->push('Rent revisal', route('rentRevisal.index'));
});
```

- [ ] **Step 6: Views**

`Modules/BackOffice/Resources/views/RentRevisal/_tabs.blade.php` (shown only to users with the permission):

```blade
@can('rent_revisal')
<ul class="nav nav-tabs" style="margin-bottom:15px">
  <li class="nav-item"><a class="nav-link {{ Route::currentRouteName() === 'rentRevisal.index' ? '' : 'active' }}" href="{{ route('tenantRenewal.index') }}">Due for Renewal</a></li>
  <li class="nav-item"><a class="nav-link {{ Route::currentRouteName() === 'rentRevisal.index' ? 'active' : '' }}" href="{{ route('rentRevisal.index') }}">Rent revisal</a></li>
</ul>
@endcan
```

In `tenant_renewal_due_list.blade.php`, add one line directly after `{{ Breadcrumbs::render('tenantRenewal.index') }}`'s closing wrapper (inside the page content, above the list card): `@include('backoffice::RentRevisal._tabs')`. No other change to that file.

`Modules/BackOffice/Resources/views/RentRevisal/index.blade.php`:

```blade
@extends('layouts.plms-app')
@section('content')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">Rent revisal</div></div>
    {{ Breadcrumbs::render('rentRevisal.index') }}
  </div>
</div>
@include('backoffice::RentRevisal._tabs')
<div class="card card-box">
  <div class="card-body">
    <p class="text-muted">Contracts ending in 90 to 120 days. Enter the revised rent once it is decided; the tenant is emailed straight away.</p>
    <form method="GET" class="form-inline" style="gap:8px;margin-bottom:12px">
      <input type="text" name="search" id="rr_search" class="form-control" placeholder="Contract, tenant or building" value="{{ $request->search }}">
      <select name="revisal" id="rr_revisal" class="form-control">
        <option value="">All</option>
        <option value="pending" {{ $request->revisal === 'pending' ? 'selected' : '' }}>Not revised</option>
        <option value="done" {{ $request->revisal === 'done' ? 'selected' : '' }}>Revised</option>
      </select>
      <button class="btn btn-primary">Filter</button>
    </form>
    <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Contract</th><th>Tenant</th><th>Building / Unit</th><th>End date</th><th>Days left</th><th class="text-right">Current rent</th><th>Revisal</th><th></th></tr></thead>
      <tbody>
      @forelse($contracts as $c)
        @php $r = $c->rentRevisal; $end = \Carbon\Carbon::parse($c->tenant_contract_valid_to_date); @endphp
        <tr id="rr-row-{{ $c->id }}">
          <td>{{ $c->tenant_contract_no }}</td>
          <td>{{ $c->tenant->tenant_name ?? '' }}</td>
          <td>{{ $c->building->building_name ?? '' }} / {{ $c->unit->unit_no ?? '' }}</td>
          <td>{{ $end->format('d/m/Y') }}</td>
          <td>{{ \Carbon\Carbon::today()->diffInDays($end, false) }}</td>
          <td class="text-right">{{ number_format((float) $c->tenant_contract_rent, 3) }}</td>
          <td>
            @if($r)
              OMR {{ number_format((float) $r->revised_rent, 3) }}
              <div class="text-muted small">{{ $r->emailed_at ? 'emailed ' . $r->emailed_at->format('d/m/Y') : 'not emailed (' . $r->email_status . ')' }} by {{ optional(optional($r->createdBy)->employee)->employee_name ?: optional($r->createdBy)->username }}</div>
            @else
              <span class="text-muted">Not revised</span>
            @endif
          </td>
          <td><button type="button" class="btn btn-sm {{ $r ? 'btn-default' : 'btn-primary' }} rr-open" data-url="{{ route('rentRevisal.form', $c->id) }}">{{ $r ? 'View' : 'Revisal' }}</button></td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center text-muted">No contracts end in 90 to 120 days.</td></tr>
      @endforelse
      </tbody>
    </table>
    </div>
    {{ $contracts->links() }}
  </div>
</div>
<div class="modal fade" id="rrModal" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content" id="rrModalBody"></div></div></div>
@endsection
@section('scripts')
<script>
$(document).on('click', '.rr-open', function () {
  $('#rrModalBody').html('<div class="modal-body">Loading...</div>');
  $('#rrModal').modal('show');
  $.get($(this).data('url')).done(function (html) { $('#rrModalBody').html(html); })
    .fail(function (x) { $('#rrModalBody').html('<div class="modal-body text-danger">' + (x.responseText || 'Could not open this contract.') + '</div>'); });
});
$(document).on('submit', '#rrForm', function (e) {
  e.preventDefault();
  var $f = $(this), $btn = $f.find('button[type=submit]').prop('disabled', true);
  $.post($f.attr('action'), $f.serialize()).done(function (res) {
    $f.find('.rr-msg').removeClass('text-danger').addClass('text-success').text(res.message);
    setTimeout(function () { location.reload(); }, 1200);
  }).fail(function (x) {
    var m = x.responseJSON && x.responseJSON.message ? x.responseJSON.message : 'The revisal could not be saved.';
    $f.find('.rr-msg').removeClass('text-success').addClass('text-danger').text(m);
    $btn.prop('disabled', false);
  });
});
$(document).on('input', '#rr_rent, #rr_note', function () {
  $('#rr_prev_rent').text($('#rr_rent').val() || '...');
  $('#rr_prev_note').text($('#rr_note').val());
});
</script>
@endsection
```

`Modules/BackOffice/Resources/views/RentRevisal/form.blade.php`:

```blade
<div class="modal-header"><h4 class="modal-title">Rent revisal · {{ $contract->tenant_contract_no }}</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<form id="rrForm" action="{{ route('rentRevisal.store', $contract->id) }}">
  {{ csrf_field() }}
  <div class="modal-body">
    <p><strong>{{ $contract->tenant->tenant_name ?? '' }}</strong><br>
      {{ $contract->building->building_name ?? '' }}, unit {{ $contract->unit->unit_no ?? '' }} · ends {{ \Carbon\Carbon::parse($contract->tenant_contract_valid_to_date)->format('d/m/Y') }}<br>
      Current rent: OMR {{ number_format((float) $contract->tenant_contract_rent, 3) }}</p>
    @if($revisal)
      <p>Revised rent: <strong>OMR {{ number_format((float) $revisal->revised_rent, 3) }}</strong></p>
      @if($revisal->note)<p>Note: {{ $revisal->note }}</p>@endif
      <p class="text-muted">Sent {{ $revisal->created_at->format('d/m/Y H:i') }} · email: {{ $revisal->email_status }}</p>
    @else
      <div class="form-group"><label for="rr_rent">New monthly rent (OMR) *</label>
        <input type="text" class="form-control" id="rr_rent" name="revised_rent" required autocomplete="off"></div>
      <div class="form-group"><label for="rr_note">Note to the tenant (optional)</label>
        <textarea class="form-control" id="rr_note" name="note" maxlength="500" rows="3"></textarea></div>
      <div class="alert alert-info small" style="margin:0">
        Preview: "On renewal, the monthly rent will be <strong>OMR <span id="rr_prev_rent">...</span></strong> (currently OMR {{ number_format((float) $contract->tenant_contract_rent, 3) }})." <span id="rr_prev_note"></span>
      </div>
    @endif
    <p class="rr-msg" style="margin-top:10px"></p>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    @unless($revisal)<button type="submit" class="btn btn-primary">Send to tenant</button>@endunless
  </div>
</form>
```

- [ ] **Step 7: Run the tests to verify they pass**

Run: `... vendor/bin/phpunit --filter RentRevisal`
Expected: all PASS.

- [ ] **Step 8: Check the screen in a browser** — `php artisan view:clear` (PHP 7.4), open `/tenantRenewal/rentRevisal` as admin: tab bar shows on both Due for Renewal and Rent revisal, a row opens the popup, Send saves (use a local contract; local SMTP is slow, ~26s).

- [ ] **Step 9: Commit**

```bash
git add Modules/BackOffice/Services/RentRevisal.php Modules/BackOffice/Http/Controllers/RentRevisalController.php Modules/BackOffice/Resources/views/RentRevisal Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renewal_due_list.blade.php Modules/BackOffice/Routes/web.php Modules/BackOffice/Routes/breadcrumbs.php tests/Feature/RentRevisalTest.php
git status   # path-case trap: confirm every file above is staged
git commit -m "feat(rent-revisal): Rent revisal tab, popup and tenant email"
```

---

### Task 5: Daily ARE email at 120 days

**Files:**
- Create: `Modules/BackOffice/Console/RentRevisalNotifyAre.php`
- Modify: `Modules/BackOffice/Providers/BackOfficeServiceProvider.php` (register the command next to the existing ones, e.g. `RefreshReceivableFollowup`)
- Modify: `app/Console/Kernel.php` (`schedule()`)
- Modify: `Modules/BackOffice/Services/RentRevisal.php` (add `notifyAres()`)
- Test: `tests/Feature/RentRevisalTest.php`

**Interfaces:**
- Consumes: `RentRevisal::windowQuery`, `areUsers` (Task 3), `TenantRentRevisalNotice` (Task 1), `StaffMessages::userRecipients($users)`, `MessageValues::employee($user)`.
- Produces: `RentRevisal::notifyAres()` → int (emails sent); artisan `rent-revisal:notify-are`.

- [ ] **Step 1: Write the failing tests** — add to `RentRevisalTest`:

```php
    public function test_notify_ares_emails_once_and_skips_revised_and_unassigned()
    {
        $svc = '\Modules\BackOffice\Services\RentRevisal';
        $are = $this->makeUser('rr_t_are', 'are');
        $c = $this->contractEndingIn(118);
        $this->assignAre($are, $c->building_id);
        // only our contract counts: mark every other window contract as already notified
        foreach ($svc::windowQuery(User::find(1))->where('tenant_contracts.id', '<>', $c->id)->pluck('tenant_contracts.id') as $id) {
            DB::table('tenant_rent_revisal_notices')->insert(['tenant_contract_id' => $id, 'are_user_id' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->assertEquals(1, $svc::notifyAres());
        $this->assertEquals(0, $svc::notifyAres());
        Mail::assertSent(TemplatedMail::class, 1);
        Mail::assertSent(TemplatedMail::class, function ($m) use ($are) { return $m->hasTo($are->email); });

        $c2 = $this->contractEndingIn(110);
        TenantRentRevisal::create(['tenant_contract_id' => $c2->id, 'current_rent' => 1, 'revised_rent' => 2, 'created_by' => 1]);
        $c3 = $this->contractEndingIn(105);
        DB::table('preferred_buildings')->where('building_id', $c3->building_id)->whereNull('assign_to')->update(['assign_to' => now()]);
        $svc::notifyAres();
        $this->assertFalse(DB::table('tenant_rent_revisal_notices')->where('tenant_contract_id', $c2->id)->exists());
        $this->assertFalse(DB::table('tenant_rent_revisal_notices')->where('tenant_contract_id', $c3->id)->exists());
    }
```

(If `$c2`/`$c3` happen to share `$c`'s building, the test still holds: `$c2` has a revisal and `$c3`'s building has no current ARE.)

- [ ] **Step 2: Run to verify it fails**

Run: `... vendor/bin/phpunit --filter test_notify_ares`
Expected: FAIL — method `notifyAres` not found.

- [ ] **Step 3: Implement `notifyAres()` in `RentRevisal`**

```php
    /** Daily: email the ARE(s) of every contract in the window with no revisal and no notice yet. */
    public static function notifyAres()
    {
        $sent = 0;
        $admin = User::find(1);   // all buildings: the job runs without a logged-in user
        $contracts = self::windowQuery($admin)->doesntHave('rentRevisal')
            ->whereNotIn('tenant_contracts.id', \DB::table('tenant_rent_revisal_notices')->select('tenant_contract_id'))
            ->with(['tenant', 'building', 'unit'])->get();
        foreach ($contracts as $c) {
            $ares = self::areUsers($c);
            if ($ares->isEmpty()) {
                continue;   // picked up later if an ARE is assigned while still in the window
            }
            $status = null;
            foreach (StaffMessages::userRecipients($ares) as $pair) {
                $r = SystemMessenger::send('rent_revisal_due_are', $pair[1], MessageValues::contract($c) + MessageValues::employee($pair[0])
                    + ['revisal_link' => route('rentRevisal.index')], ['related' => ['contract', $c->id]]);
                $status = $status === \Modules\BackOffice\Entities\MessageLog::SENT ? $status : $r['email'];
            }
            \Modules\BackOffice\Entities\TenantRentRevisalNotice::create(['tenant_contract_id' => $c->id, 'are_user_id' => $ares->first()->id,
                'email_status' => $status, 'sent_at' => now()]);
            $sent++;
        }
        return $sent;
    }
```

- [ ] **Step 4: Command + registration + schedule**

`Modules/BackOffice/Console/RentRevisalNotifyAre.php`:

```php
<?php

namespace Modules\BackOffice\Console;

use Illuminate\Console\Command;
use Modules\BackOffice\Services\RentRevisal;

/** Emails each ARE once when a contract enters the 90-120 day rent revisal window (spec 2026-10-15). */
class RentRevisalNotifyAre extends Command
{
    protected $signature = 'rent-revisal:notify-are';
    protected $description = 'Email AREs about contracts entering the rent revisal window';

    public function handle()
    {
        $this->info('Rent revisal ARE emails: ' . RentRevisal::notifyAres());
    }
}
```

Register it in `BackOfficeServiceProvider` where the other module commands are registered (look for `$this->commands([` listing `RefreshReceivableFollowup` / `BulkMessagesSend`) by adding `\Modules\BackOffice\Console\RentRevisalNotifyAre::class,`.

In `app/Console/Kernel.php` `schedule()`, after the `legal:settlement-check` line:

```php
       $schedule->command('rent-revisal:notify-are')->dailyAt('08:00');
```

- [ ] **Step 5: Run to verify it passes**

Run: `... vendor/bin/phpunit --filter test_notify_ares` and `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan list | grep rent-revisal`
Expected: PASS; the command is listed. Do **not** run the command against the local DB (it would email real AREs through Office 365).

- [ ] **Step 6: Commit**

```bash
git add Modules/BackOffice/Console/RentRevisalNotifyAre.php Modules/BackOffice/Providers/BackOfficeServiceProvider.php app/Console/Kernel.php Modules/BackOffice/Services/RentRevisal.php tests/Feature/RentRevisalTest.php
git commit -m "feat(rent-revisal): daily 08:00 email to the ARE at 120 days"
```

---

### Task 6: Renewal screens default to the revised rent + ARE dashboard card

**Files:**
- Modify: `Modules/BackOffice/Services/RentRevisal.php` (add `renewalRent()`)
- Modify: `Modules/BackOffice/Http/Controllers/TenantRenewalController.php` (`renewalNewContract` ~line 1026, `renewalTemplatePdf` ~line 2002)
- Modify: `app/Http/Controllers/DashboardController.php` (`areDashboard` result array, ~line 1570–1582)
- Modify: `resources/views/dashboard/are.blade.php` (new card after "Contracts Expired", ~line 160)
- Test: `tests/Feature/RentRevisalTest.php`

**Interfaces:**
- Consumes: `TenantContract::rentRevisal()`, `tenantRenewalForm` (existing), `RentRevisal::dueCount($user)`.
- Produces: `RentRevisal::renewalRent(TenantContract $c)` → float — latest renewal form rent, else revised rent, else contract rent. Dashboard key `rentRevisalDueCount`.

- [ ] **Step 1: Write the failing tests** — add to `RentRevisalTest`:

```php
    public function test_renewal_rent_default_order()
    {
        $svc = '\Modules\BackOffice\Services\RentRevisal';
        $c = $this->contractEndingIn(100);
        DB::table('tenant_renewal_forms')->where('tenant_contract_id', $c->id)->delete();
        $this->assertEquals((float) $c->tenant_contract_rent, $svc::renewalRent($c->fresh()));
        TenantRentRevisal::create(['tenant_contract_id' => $c->id, 'current_rent' => $c->tenant_contract_rent, 'revised_rent' => 777.5, 'created_by' => 1]);
        $this->assertEquals(777.5, $svc::renewalRent($c->fresh()));
        DB::table('tenant_renewal_forms')->insert(['tenant_contract_id' => $c->id, 'tenant_contract_rent' => 650, 'tenant_contract_start_date' => '2027-01-01',
            'tenant_contract_valid_to_date' => '2027-12-31', 'tenant_contract_payment_type' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $this->assertEquals(650.0, $svc::renewalRent($c->fresh()));
    }

    public function test_renewal_popup_shows_revised_rent()
    {
        $c = $this->contractEndingIn(100);
        DB::table('tenant_renewal_forms')->where('tenant_contract_id', $c->id)->delete();
        TenantRentRevisal::create(['tenant_contract_id' => $c->id, 'current_rent' => $c->tenant_contract_rent, 'revised_rent' => 777.5, 'created_by' => 1]);
        $this->as(User::find(1))->post(route('renewalTemplatePdf'), ['tenant_contract_id' => $c->id])->assertSee('777.500');
    }

    public function test_are_dashboard_has_rent_revisal_due_count()
    {
        $are = $this->makeUser('rr_t_are', 'are');
        $c = $this->contractEndingIn(100);
        $this->assignAre($are, $c->building_id);
        $this->actingAs($are);
        $data = app(\App\Http\Controllers\DashboardController::class)->areDashboard(request(), true);
        $this->assertEquals(\Modules\BackOffice\Services\RentRevisal::dueCount($are), $data['rentRevisalDueCount']);
        $this->assertGreaterThanOrEqual(1, $data['rentRevisalDueCount']);
    }
```

Before running, confirm the renewal popup route name and method: `grep -n "renewalTemplatePdf" Modules/BackOffice/Routes/web.php`; use that name/verb in the second test, and the `areDashboard` signature (`grep -n "public function areDashboard" app/Http/Controllers/DashboardController.php`).

- [ ] **Step 2: Run to verify they fail**

Run: `... vendor/bin/phpunit --filter "test_renewal_rent_default_order|test_renewal_popup_shows|test_are_dashboard_has"`
Expected: FAIL — `renewalRent` not found / key missing.

- [ ] **Step 3: Add `renewalRent()` to `RentRevisal`**

```php
    /** Rent the renewal screens start from: latest renewal form sent, else the revisal, else the contract rent. */
    public static function renewalRent(TenantContract $c)
    {
        $form = $c->tenantRenewalForm()->orderBy('id', 'desc')->first();
        if ($form) {
            return (float) $form->tenant_contract_rent;
        }
        $revisal = $c->rentRevisal;
        return $revisal ? (float) $revisal->revised_rent : (float) $c->tenant_contract_rent;
    }
```

- [ ] **Step 4: Use it in the two renewal screens (read-only change)**

The renewal popup view (`renewalTemplatePdfModel.blade.php` line 153) already prefers `$renewal_data->tenant_contract_rent` and falls back to `$tenantContract->tenant_contract_rent`. To slot the revisal into that fallback without touching the view, in `TenantRenewalController::renewalTemplatePdf()` right after the `if(count($tenantContract->tenantRenewalForm) > 0){ ... }` block add:

```php
      // Rent revisal (spec 2026-10-15): with no renewal form yet, start from the revised rent.
      if (!$renewal_data && $tenantContract->rentRevisal) {
        $tenantContract->tenant_contract_rent = $tenantContract->rentRevisal->revised_rent;
      }
```

Do the same in `renewalNewContract()` after its `if(count($tenantContract->tenantRenewalForm) > 0){ ... }` block (~line 1026–1035), using its `$renewal_data` variable. The contract is **not saved** in either method (check that neither method calls `$tenantContract->save()` after this point; if one does, set a separate variable passed to the view instead).

- [ ] **Step 5: Dashboard card**

In `DashboardController::areDashboard`, in the returned array (the one ending `'referBackContractsCount'=>$referBackContractsCount` at ~line 1581), add:

```php
	'rentRevisalDueCount'=>\Modules\BackOffice\Services\RentRevisal::dueCount(\Auth::user()),
```

(If `areDashboard` has the second array at ~line 1983 for another role, leave that one alone.)

In `resources/views/dashboard/are.blade.php`, after the "Contracts Expired" card (`</div>` closing it, ~line 160):

```blade
          @can('rent_revisal')
          <div class="col-xl-4 col-md-4 col-12">
            <div class="info-box bg-primary">
            <a href="{{ route('rentRevisal.index', ['revisal' => 'pending']) }}">
              <span class="info-box-icon push-bottom"><i class="material-icons extra">trending_up</i></span>
              </a>
              <div class="info-box-content">
                <span class="info-box-number">{{ $are['rentRevisalDueCount'] ?? 0 }}</span>
                <span class="info-box-text">Rent revisal due</span>
                <div class="progress"><div class="progress-bar width-60"></div></div>
                <span class="progress-description">Contracts ending in 90-120 days</span>
              </div>
            </div>
          </div>
          @endcan
```

- [ ] **Step 6: Run the full feature file and the renewal/termination suites**

Run: `... vendor/bin/phpunit --filter "RentRevisal|TenantRenewal|Termination"`
Expected: all RentRevisal tests PASS; no new failures elsewhere (compare with a run on master if anything fails).

- [ ] **Step 7: Commit**

```bash
git add Modules/BackOffice/Services/RentRevisal.php Modules/BackOffice/Http/Controllers/TenantRenewalController.php app/Http/Controllers/DashboardController.php resources/views/dashboard/are.blade.php tests/Feature/RentRevisalTest.php
git status   # confirm resources/views/dashboard/are.blade.php is staged (path-case trap)
git commit -m "feat(rent-revisal): renewal screens start from the revised rent; ARE dashboard card"
```

---

## Deploy notes (live, when the user asks to stage)

Upload code first, then run `2026_10_15_000000` and `2026_10_15_000001` with `--path`, then `php artisan view:clear` and `route:clear`. The scheduler must run (`rent-revisal:notify-are` at 08:00). Stage files only when the user asks ("add to server upload").
