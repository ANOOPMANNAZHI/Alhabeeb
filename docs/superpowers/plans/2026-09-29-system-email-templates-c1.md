# System Email Templates — C1 (Base + Cheque Bounce) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A central sender that sends system templates (email + optional SMS) and logs every attempt, a Mail → Sent log screen, and the cheque bounce emails (ARE — now all AREs — and tenant, off) moved onto it.

**Architecture:** `SystemTemplateDefaults` holds default wording per key (seeds rows, fills gaps, powers Reset to default). `SystemMessenger::send($key, MessageRecipient, $values, $options)` loads the template, skips when off, renders with `MessageTemplateRenderer`, sends one generic `TemplatedMail` and optionally an SMS, never throws, and writes `message_log`. `PdcBounceNotifier` replaces the three copy-pasted bounce blocks in `PdcController`.

**Tech Stack:** Laravel 5.7 (nwidart modules), PostgreSQL, Blade + jQuery + TinyMCE 5, spatie/permission, PHPUnit 7 (unit + feature tests with `DatabaseTransactions` against the local DB).

**Spec:** `docs/superpowers/specs/2026-09-29-system-email-templates-design.md` (C1 section + Architecture), builds on `docs/superpowers/specs/2026-09-29-message-templates-design.md`.

## Global Constraints

- Production PHP < 7.4: no arrow functions, no `?->`, no `match`, no typed properties, no `??=`. Laravel 5.7: no `$request->boolean()`.
- Local PHP: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe` (below: `$PHP`).
- System template `is_active` off = not sent at all (log `skipped_off`). `sms_enabled` on = SMS sent every time (with or without email); off = never.
- Sign-off in default wording: "Al Habib Team". From address stays `MAIL_FROM_ADDRESS` (plmsalerts@alhabib.om).
- A mail/SMS error never breaks the user's action: caught, logged `failed`.
- Log statuses exactly: `sent`, `failed`, `skipped_off`, `skipped_no_address`, `skipped_no_mobile`.
- Deploy: code first, then migrations (the Sent log menu row needs its route).
- Own-styled controls (theme `.btn` clips labels): reuse the `.mt` styles from `MessageTemplate/_styles.blade.php`.

## Review Focus

1. **Building with two ARE assignments pointing to the same user, or an assignment whose ARE user was deleted** → that user gets one email, the missing one is skipped without error. Test in Task 5.
2. **ARE without email but with mobile, template SMS off** → no email, no SMS, log `skipped_no_address` (email) only; SMS on → SMS sent. Test in Task 4.
3. **SMTP throws** (e.g. Office 365 rejects) → bounce is still saved, JSON success returned, log row `failed` with the error text. Test in Task 4 (messenger) and Task 5 (notifier).
4. **Template row deleted/missing** (code uploaded before seeding) → default wording used, log note `template_missing`. Test in Task 4.
5. **Remarks empty / bounce reason unknown** → placeholders render "-" rather than a blank "Remarks:" line or `{remarks}`. Test in Task 5.

---

## File Structure

| File | Responsibility |
|---|---|
| `database/migrations/2026_09_30_000000_add_sms_enabled_and_message_log.php` (create) | `sms_enabled` column, `message_log` table |
| `database/migrations/2026_09_30_000001_add_message_log_menu.php` (create) | Mail → Sent log menu + `view_message_log` |
| `database/migrations/2026_09_30_000002_seed_c1_system_templates.php` (create) | insert C1 templates from defaults |
| `Modules/BackOffice/Services/MessageTemplateRenderer.php` (modify) | block placeholders |
| `Modules/BackOffice/Services/SystemTemplateDefaults.php` (create) | default wording + placeholders per key, seeding |
| `Modules/BackOffice/Providers/BackOfficeServiceProvider.php` (modify) | register system placeholders at boot |
| `Modules/BackOffice/Entities/MessageLog.php` (create) | log model + status constants |
| `Modules/BackOffice/Services/MessageRecipient.php` (create) | name/email/mobile/cc value object |
| `Modules/BackOffice/Services/MessageValues.php` (create) | contract / employee / pdc value helpers |
| `Modules/BackOffice/Services/SystemMessenger.php` (create) | send + log |
| `Modules/BackOffice/Emails/TemplatedMail.php`, `Resources/views/Email/templated.blade.php` (create) | generic email in today's layout |
| `Modules/BackOffice/Services/PdcBounceNotifier.php` (create) | bounce → AREs + tenant + in-app event |
| `Modules/BackOffice/Http/Controllers/PdcController.php` (modify) | 3 sites call the notifier |
| `Modules/BackOffice/Emails/ChequeBounceAreEmail.php`, `ChequeBounceEmail.php`, views `cheque_bounce_are_mail`, `cheque_bounce_mail` (delete) | replaced |
| `Modules/BackOffice/Http/Controllers/MessageTemplateController.php`, `Resources/views/MessageTemplate/form.blade.php`, `index.blade.php` (modify) | Send SMS switch, Reset to default, SMS badge |
| `Modules/BackOffice/Http/Controllers/MessageLogController.php`, `Resources/views/MessageLog/index.blade.php` (create) | Sent log screen |
| `Modules/BackOffice/Routes/web.php`, `breadcrumbs.php` (modify) | routes |
| tests: `tests/Unit/MessageTemplateRendererTest.php` (modify), `tests/Unit/SystemTemplateDefaultsTest.php`, `tests/Feature/SystemMessengerTest.php`, `tests/Feature/PdcBounceNotifierTest.php`, `tests/Feature/MessageTemplateScreensTest.php`, `tests/Feature/MessageLogScreenTest.php` (create) | |

---

### Task 1: Migrations — `sms_enabled`, `message_log`, Sent log menu

**Files:** create the first two migrations above.

**Interfaces — Produces:** column `message_templates.sms_enabled` (bool, default false); table `message_log`; permission `view_message_log`; menu route `message-log.index`.

- [ ] **Step 1: Schema migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Per-template SMS switch, and one log row per email/SMS attempt (project C). */
class AddSmsEnabledAndMessageLog extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('message_templates', 'sms_enabled')) {
            Schema::table('message_templates', function (Blueprint $table) {
                $table->boolean('sms_enabled')->default(false);
            });
        }
        if (!Schema::hasTable('message_log')) {
            Schema::create('message_log', function (Blueprint $table) {
                $table->increments('id');
                $table->string('template_key', 100)->nullable();
                $table->string('channel', 10);
                $table->string('recipient_name', 150)->nullable();
                $table->string('recipient', 190)->nullable();
                $table->text('cc')->nullable();
                $table->string('subject', 255)->nullable();
                $table->text('body')->nullable();
                $table->string('status', 30);
                $table->text('note')->nullable();
                $table->string('related_type', 50)->nullable();
                $table->integer('related_id')->nullable();
                $table->integer('triggered_by')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index('created_at');
                $table->index('template_key');
                $table->index(['related_type', 'related_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('message_log');
        if (Schema::hasColumn('message_templates', 'sms_enabled')) {
            Schema::table('message_templates', function (Blueprint $table) {
                $table->dropColumn('sms_enabled');
            });
        }
    }
}
```

- [ ] **Step 2: Menu migration** — copy of `2026_09_29_000001_add_message_templates_menu.php` with: class `AddMessageLogMenu`, `ROUTE = 'message-log.index'`, `PERMS = ['view_message_log']`, `menu_name` `'Sent log'`, `menu_icon` `'fa-paper-plane-o'`, `url_key` `'message-log'`. Same parent lookup (parent of `massMail.create`), same roles, same idempotency, same `down()`.

- [ ] **Step 3: Run both** — `$PHP artisan migrate --path=database/migrations/<file>` each. Expected `Migrated:` ×2. Verify in tinker: `Schema::hasColumn('message_templates','sms_enabled')`, `Schema::hasTable('message_log')`, menu row parent = Templates row parent, `super_admin` has `view_message_log`.

- [ ] **Step 4: Commit** — `feat(system-templates): sms_enabled switch, message_log table, Sent log menu`

---

### Task 2: Renderer block placeholders

**Files:** Modify `Modules/BackOffice/Services/MessageTemplateRenderer.php`, `tests/Unit/MessageTemplateRendererTest.php`.

**Interfaces — Produces:** `MessageTemplateRenderer::render($template, array $values, array $blocks = [])` — `$blocks` name ⇒ trusted HTML; in the body inserted raw, in subject and SMS replaced with `''`; a block name is never reported unknown. `fill($text, $values, $escapeHtml, &$unknown, array $blocks = [], $blockMode = 'raw')` where `$blockMode` is `'raw'` or `'empty'`.

- [ ] **Step 1: Failing tests** (append to the test class)

```php
    public function test_blocks_are_raw_in_body_and_empty_elsewhere()
    {
        $t = (object) ['email_subject' => 'Report {details}', 'email_body' => '<p>{tenant_name}</p>{details}', 'sms_body' => 'See {details}email'];
        $r = R::render($t, ['tenant_name' => 'A<b>'], ['details' => '<table><tr><td>{rent}</td></tr></table>']);
        $this->assertSame('<p>A&lt;b&gt;</p><table><tr><td>{rent}</td></tr></table>', $r['html']);
        $this->assertSame('Report', $r['subject']);
        $this->assertSame('See email', $r['sms']);
        $this->assertSame([], $r['unknown']); // {rent} inside a block is neither expanded nor reported
    }
```

- [ ] **Step 2: Run** `$PHP vendor/bin/phpunit tests/Unit/MessageTemplateRendererTest.php` → FAIL (`{details}` left in html / reported unknown).

- [ ] **Step 3: Implement** — in `fill()` add the two trailing parameters; inside the callback, before the unknown check:

```php
            if (array_key_exists($key, $blocks)) {
                return $blockMode === 'raw' ? (string) $blocks[$key] : '';
            }
```

(lower-case the block keys the same way as values, and pass `$blocks`/`$blockMode` into the closure's `use`). In `render()` add `array $blocks = []` and call `fill($subject, $values, false, $unknown, $blocks, 'empty')`, `fill($body, $values, true, $unknown, $blocks, 'raw')`, `fill($sms, $values, false, $unknown, $blocks, 'empty')`. Update the class docblock: "Block placeholders carry trusted HTML built in code".

- [ ] **Step 4: Run** → `OK`. Then `$PHP vendor/bin/phpunit tests/Unit` → all green.
- [ ] **Step 5: Commit** — `feat(system-templates): block placeholders in the renderer`

---

### Task 3: `SystemTemplateDefaults` + C1 seed

**Files:** Create `Modules/BackOffice/Services/SystemTemplateDefaults.php`, `tests/Unit/SystemTemplateDefaultsTest.php`, `database/migrations/2026_09_30_000002_seed_c1_system_templates.php`; modify `Modules/BackOffice/Providers/BackOfficeServiceProvider.php`.

**Interfaces — Produces:**
- `SystemTemplateDefaults::all(): array` key ⇒ `['name','audience','stage','email_subject','email_body','sms_body','is_active','sms_enabled','placeholders' => [name => ['label','sample']]]`
- `SystemTemplateDefaults::get($key): array|null`
- `SystemTemplateDefaults::registerPlaceholders(): void` (calls `MessagePlaceholders::registerSystem` for every key)
- `SystemTemplateDefaults::seed(array $keys): int` — inserts missing rows (`kind` system), returns count inserted; never updates existing rows.

- [ ] **Step 1: Failing test**

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\SystemTemplateDefaults as D;
use Modules\BackOffice\Services\MessagePlaceholders as P;
use Modules\BackOffice\Services\MessageTemplateRenderer as R;

class SystemTemplateDefaultsTest extends TestCase
{
    public function test_c1_keys_exist_with_expected_switches()
    {
        $are = D::get('pdc_bounce_are');
        $tenant = D::get('pdc_bounce_tenant');
        $this->assertSame('staff', $are['audience']);
        $this->assertTrue($are['is_active']);
        $this->assertFalse($are['sms_enabled']);
        $this->assertSame('tenant', $tenant['audience']);
        $this->assertFalse($tenant['is_active']);
        $this->assertNull(D::get('nope'));
    }

    public function test_every_default_is_complete_and_uses_only_known_placeholders()
    {
        D::registerPlaceholders();
        foreach (D::all() as $key => $d) {
            $this->assertNotEmpty($d['name'], $key);
            $this->assertArrayHasKey($d['audience'], P::AUDIENCES, $key);
            $this->assertTrue(($d['email_subject'] && $d['email_body']) || $d['sms_body'], "$key has a channel");
            $known = array_keys(P::forTemplate($d['audience'], $key));
            foreach ([$d['email_subject'], $d['email_body'], $d['sms_body']] as $text) {
                foreach (R::placeholdersIn($text) as $ph) {
                    $this->assertContains($ph, $known, "$key uses unregistered {{$ph}}");
                }
            }
            $this->assertSame($d['email_body'], R::cleanHtml($d['email_body']), "$key body survives the cleaner unchanged");
        }
    }
}
```

- [ ] **Step 2: Run** → class not found.

- [ ] **Step 3: Implement**

```php
<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\DB;

/**
 * Default wording for every system template (project C). Seeds the
 * message_templates rows, stands in when a row is missing, powers
 * "Reset to default" and registers each key's placeholders.
 */
class SystemTemplateDefaults
{
    private static function contractPlaceholders()
    {
        return [
            'tenant_name' => ['label' => 'Tenant name', 'sample' => 'Mohammed Al Balushi'],
            'contract_no' => ['label' => 'Contract no.', 'sample' => 'TAG2601977'],
            'building'    => ['label' => 'Building', 'sample' => 'Al Qandeel-2 Bldg'],
            'unit'        => ['label' => 'Unit', 'sample' => '84'],
        ];
    }

    private static function chequePlaceholders()
    {
        return [
            'cheque_no'     => ['label' => 'Cheque no.', 'sample' => '82079797'],
            'amount'        => ['label' => 'Cheque amount', 'sample' => '250.000'],
            'cheque_date'   => ['label' => 'Cheque date', 'sample' => '15/09/2026'],
            'bounce_reason' => ['label' => 'Bounce reason', 'sample' => 'Insufficient Funds'],
            'remarks'       => ['label' => 'Remarks', 'sample' => 'FUNDS NOT SUFFICIENT'],
        ];
    }

    public static function all()
    {
        return [
            'pdc_bounce_are' => [
                'name' => 'Cheque bounced (to ARE)', 'audience' => 'staff', 'stage' => 'C1',
                'email_subject' => 'Cheque bounced - {tenant_name} ({cheque_no})',
                'email_body' => '<p>Dear {employee_name},</p>'
                    . '<p>This is to inform you that cheque <strong>{cheque_no}</strong> for OMR {amount} dated {cheque_date} has bounced.</p>'
                    . '<p>Tenant: {tenant_name}<br>Contract: {contract_no}<br>Building: {building}, unit {unit}</p>'
                    . '<p>Bounce reason: {bounce_reason}<br>Remarks: {remarks}</p>'
                    . '<p>Regards,<br>Al Habib Team</p>',
                'sms_body' => 'Cheque {cheque_no} of {tenant_name} ({building} {unit}) bounced: {bounce_reason}. - Al Habib',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => self::contractPlaceholders() + self::chequePlaceholders(),
            ],
            'pdc_bounce_tenant' => [
                'name' => 'Cheque bounced (to tenant)', 'audience' => 'tenant', 'stage' => 'C1',
                'email_subject' => 'Your cheque {cheque_no} has been returned by the bank',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>We regret to inform you that your cheque no. <strong>{cheque_no}</strong> for OMR {amount} dated {cheque_date} '
                    . 'for {building}, unit {unit} (contract {contract_no}) was returned by the bank.</p>'
                    . '<p>Reason: {bounce_reason}</p>'
                    . '<p>Please contact our office at the earliest to replace the cheque and avoid penalty charges.</p>'
                    . '<p>Regards,<br>Al Habib Team</p>',
                'sms_body' => 'Dear {tenant_name}, your cheque {cheque_no} for OMR {amount} was returned by the bank ({bounce_reason}). Please contact Al Habib to replace it.',
                'is_active' => false, 'sms_enabled' => false,
                'placeholders' => self::chequePlaceholders(),
            ],
        ];
    }

    public static function get($key)
    {
        $all = self::all();
        return isset($all[$key]) ? $all[$key] : null;
    }

    public static function registerPlaceholders()
    {
        foreach (self::all() as $key => $d) {
            MessagePlaceholders::registerSystem($key, $d['placeholders']);
        }
    }

    public static function seed(array $keys)
    {
        $inserted = 0;
        foreach ($keys as $key) {
            $d = self::get($key);
            if (!$d || DB::table('message_templates')->where('key', $key)->exists()) {
                continue;
            }
            DB::table('message_templates')->insert([
                'key' => $key, 'name' => $d['name'], 'kind' => 'system', 'audience' => $d['audience'],
                'email_subject' => $d['email_subject'], 'email_body' => $d['email_body'], 'sms_body' => $d['sms_body'],
                'is_active' => $d['is_active'], 'sms_enabled' => $d['sms_enabled'],
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $inserted++;
        }
        return $inserted;
    }
}
```

Note: `seed()` checks `key` including soft-deleted rows is unnecessary — system rows cannot be deleted from the screen.

- [ ] **Step 4: Run** → `OK (2 tests…)`. If the cleaner assertion fails, fix the default HTML (e.g. `<br>` serialisation), not the assertion.

- [ ] **Step 5: Register at boot** — in `BackOfficeServiceProvider::boot()` add `\Modules\BackOffice\Services\SystemTemplateDefaults::registerPlaceholders();` as the last line.

- [ ] **Step 6: Seed migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/** C1 system templates (cheque bounce). Insert-only: edited rows are never overwritten. */
class SeedC1SystemTemplates extends Migration
{
    const KEYS = ['pdc_bounce_are', 'pdc_bounce_tenant'];

    public function up()
    {
        SystemTemplateDefaults::seed(self::KEYS);
    }

    public function down()
    {
        \DB::table('message_templates')->whereIn('key', self::KEYS)->delete();
    }
}
```

Run it; verify two rows (`pdc_bounce_tenant` inactive).

- [ ] **Step 7: Commit** — `feat(system-templates): defaults registry and C1 cheque bounce templates`

---

### Task 4: `SystemMessenger`, `TemplatedMail`, `MessageLog`, `MessageRecipient`, `MessageValues`

**Files:** create the six files named in the File Structure for these, plus `tests/Feature/SystemMessengerTest.php`.

**Interfaces:**
- Consumes: Task 2 `render(..., $blocks)`, Task 3 `SystemTemplateDefaults::get`, Project A `MessageTemplate`, `MessageTemplateRenderer`, global `sendSms($mobile, $msg, $params)`.
- Produces:
  - `new MessageRecipient($name, $email = null, $mobile = null, array $cc = [])` — public props `name`, `email`, `mobile`, `cc` (trimmed; empty strings become null; cc filtered to non-empty unique).
  - `SystemMessenger::send($key, MessageRecipient $to, array $values, array $options = []): array` → `['email' => status|null, 'sms' => status|null]` (null = channel not attempted). Options: `blocks`, `attachments` (list of `['data','name','mime']`), `related` (`['type', id]`).
  - `SystemMessenger::$smsSender` — public static callable, default `'sendSms'` (tests replace it).
  - `MessageLog` model: table `message_log`, `$timestamps = false`, constants `SENT`, `FAILED`, `SKIPPED_OFF`, `SKIPPED_NO_ADDRESS`, `SKIPPED_NO_MOBILE`, `STATUSES` (value ⇒ label).
  - `MessageValues::contract($contract): array` (tenant_name, contract_no, building, unit, location, rent, contract_start, contract_end, mobile), `MessageValues::employee($user): array` (employee_name, role), `MessageValues::cheque($pdc): array` (cheque_no, amount 3 dp, cheque_date d/m/Y, bounce_reason, remarks — empty values become `-`).
  - `new TemplatedMail($subject, $html, array $attachments = [])`.

- [ ] **Step 1: Failing feature test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\MessageRecipient;
use Modules\BackOffice\Services\SystemMessenger;

class SystemMessengerTest extends TestCase
{
    use DatabaseTransactions;

    private $sms;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        $this->sms = [];
        $self = $this;
        SystemMessenger::$smsSender = function ($mobile, $msg) use ($self) { $self->smsSent($mobile, $msg); return true; };
        MessageTemplate::where('key', 'pdc_bounce_are')->update(['is_active' => true, 'sms_enabled' => false]);
    }

    public function smsSent($mobile, $msg) { $this->sms[] = [$mobile, $msg]; }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function values()
    {
        return ['employee_name' => 'Anoop', 'tenant_name' => 'A & B', 'contract_no' => 'TAG1', 'building' => 'Rex', 'unit' => '5',
                'cheque_no' => '123', 'amount' => '250.000', 'cheque_date' => '15/09/2026', 'bounce_reason' => 'Insufficient Funds', 'remarks' => '-'];
    }

    public function test_sends_email_and_logs_it()
    {
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com', '99123456', ['cc@example.com']), $this->values(), ['related' => ['pdc', 77]]);
        $this->assertSame(['email' => 'sent', 'sms' => null], $r);
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('are@example.com') && $m->hasCc('cc@example.com') && $m->subject === 'Cheque bounced - A & B (123)'
                && strpos($m->html, 'A &amp; B') !== false;
        });
        $log = MessageLog::where('template_key', 'pdc_bounce_are')->latest('id')->first();
        $this->assertSame(['email', 'are@example.com', 'sent', 'pdc', 77], [$log->channel, $log->recipient, $log->status, $log->related_type, $log->related_id]);
        $this->assertSame([], $this->sms);
    }

    public function test_switched_off_sends_nothing()
    {
        MessageTemplate::where('key', 'pdc_bounce_are')->update(['is_active' => false, 'sms_enabled' => true]);
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com', '99123456'), $this->values());
        $this->assertSame(['email' => 'skipped_off', 'sms' => null], $r);
        Mail::assertNothingSent();
        $this->assertSame([], $this->sms);
    }

    public function test_no_email_sms_off_is_skipped_no_address()
    {
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', '', '99123456'), $this->values());
        $this->assertSame(['email' => 'skipped_no_address', 'sms' => null], $r);
        $this->assertSame([], $this->sms);
    }

    public function test_sms_on_sends_sms_even_with_email()
    {
        MessageTemplate::where('key', 'pdc_bounce_are')->update(['sms_enabled' => true]);
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com', '99123456'), $this->values());
        $this->assertSame(['email' => 'sent', 'sms' => 'sent'], $r);
        $this->assertSame('99123456', $this->sms[0][0]);
        $this->assertContains('A & B', $this->sms[0][1]);
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com', null), $this->values());
        $this->assertSame('skipped_no_mobile', $r['sms']);
    }

    public function test_mail_exception_is_logged_not_thrown()
    {
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP 554 rejected'));
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com'), $this->values());
        $this->assertSame('failed', $r['email']);
        $this->assertContains('SMTP 554', MessageLog::latest('id')->first()->note);
    }

    public function test_missing_row_uses_default_wording()
    {
        DB::table('message_templates')->where('key', 'pdc_bounce_are')->delete();
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com'), $this->values());
        $this->assertSame('sent', $r['email']);
        $this->assertContains('template_missing', MessageLog::latest('id')->first()->note);
    }

    public function test_unknown_key_is_a_programming_error()
    {
        $this->expectException(\InvalidArgumentException::class);
        SystemMessenger::send('no_such_key', new MessageRecipient('x', 'x@example.com'), []);
    }
}
```

- [ ] **Step 2: Run** `$PHP vendor/bin/phpunit tests/Feature/SystemMessengerTest.php` → errors (classes missing). If the Feature suite itself cannot boot (e.g. `CreatesApplication` issue), fix that first and ledger a ruling.

- [ ] **Step 3: Implement `MessageLog`, `MessageRecipient`, `TemplatedMail` + view**

```php
<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    const SENT = 'sent';
    const FAILED = 'failed';
    const SKIPPED_OFF = 'skipped_off';
    const SKIPPED_NO_ADDRESS = 'skipped_no_address';
    const SKIPPED_NO_MOBILE = 'skipped_no_mobile';
    const STATUSES = [
        'sent' => 'Sent', 'failed' => 'Failed', 'skipped_off' => 'Skipped: switched off',
        'skipped_no_address' => 'Skipped: no email address', 'skipped_no_mobile' => 'Skipped: no mobile',
    ];

    protected $table = 'message_log';
    public $timestamps = false;
    protected $guarded = [];
    protected $dates = ['created_at'];
}
```

```php
<?php

namespace Modules\BackOffice\Services;

/** Who a system message goes to. Empty strings are treated as missing. */
class MessageRecipient
{
    public $name;
    public $email;
    public $mobile;
    public $cc;

    public function __construct($name, $email = null, $mobile = null, array $cc = [])
    {
        $this->name = trim((string) $name);
        $this->email = trim((string) $email) !== '' ? trim((string) $email) : null;
        $this->mobile = trim((string) $mobile) !== '' ? trim((string) $mobile) : null;
        $clean = [];
        foreach ($cc as $c) {
            $c = trim((string) $c);
            if ($c !== '' && strcasecmp($c, (string) $this->email) !== 0 && !in_array($c, $clean, true)) {
                $clean[] = $c;
            }
        }
        $this->cc = $clean;
    }
}
```

```php
<?php

namespace Modules\BackOffice\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

/** Any system template email: subject + rendered HTML inside the PLMS mail layout. */
class TemplatedMail extends Mailable
{
    use Queueable;

    public $html;
    private $files;

    public function __construct($subject, $html, array $attachments = [])
    {
        $this->subject = $subject;
        $this->html = $html;
        $this->files = $attachments;
    }

    public function build()
    {
        $mail = $this->view('backoffice::Email.templated', ['bodyHtml' => $this->html]);
        foreach ($this->files as $f) {
            $mail->attachData($f['data'], $f['name'], ['mime' => $f['mime']]);
        }
        return $mail;
    }
}
```

View `Modules/BackOffice/Resources/views/Email/templated.blade.php` — same outer markup as `resources/views/vendor/mail/html/layout.blade.php`, with the header div from `header.blade.php` and footer div from `footer.blade.php` pasted inline (copy exactly: light-blue header with `asset(PUBLIC_PATH.'/img/logo_habib.png')`, footer "©Copyright | Al Habib 2018.All Right Reserved"), and the body area rendering `{!! $bodyHtml !!}` with `text-align:left; line-height:1.5` instead of the markdown slot (template emails are left-aligned paragraphs; today's centring made multi-line text hard to read).

- [ ] **Step 4: Implement `MessageValues`**

```php
<?php

namespace Modules\BackOffice\Services;

/** Placeholder values from PLMS records, so send sites stay short. Missing data becomes "-". */
class MessageValues
{
    private static function v($x)
    {
        $x = trim((string) $x);
        return $x === '' ? '-' : $x;
    }

    private static function date($d)
    {
        return $d ? date('d/m/Y', strtotime($d)) : '-';
    }

    public static function contract($contract)
    {
        $tenant = $contract ? $contract->tenant : null;
        $building = $contract ? $contract->building : null;
        $unit = $contract ? $contract->unit : null;
        $location = $building ? $building->location : null;
        return [
            'tenant_name'    => self::v($tenant ? $tenant->tenant_name : ''),
            'contract_no'    => self::v($contract ? $contract->tenant_contract_no : ''),
            'building'       => self::v($building ? $building->building_name : ''),
            'unit'           => self::v($unit ? $unit->unit_code : ''),
            'location'       => self::v($location ? $location->locations_name : ''),
            'rent'           => $contract && $contract->tenant_contract_rent !== null ? number_format((float) $contract->tenant_contract_rent, 3) : '-',
            'contract_start' => self::date($contract ? $contract->tenant_contract_start_date : null),
            'contract_end'   => self::date($contract ? $contract->tenant_contract_valid_to_date : null),
            'mobile'         => self::v($tenant ? ($tenant->tenant_contact_no ?: $tenant->tenant_residence_tel) : ''),
        ];
    }

    public static function employee($user)
    {
        $employee = $user ? $user->employee : null;
        $role = $user && method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : '';
        return [
            'employee_name' => self::v($employee ? $employee->employee_name : ($user ? $user->username : '')),
            'role'          => self::v($role ? ucwords(str_replace('_', ' ', $role)) : ''),
        ];
    }

    public static function cheque($pdc)
    {
        return [
            'cheque_no'     => self::v($pdc->pdc_check_no),
            'amount'        => $pdc->pdc_amt !== null ? number_format((float) $pdc->pdc_amt, 3) : '-',
            'cheque_date'   => self::date($pdc->pdc_check_date),
            'bounce_reason' => self::v($pdc->pdc_cancel_reason_name),
            'remarks'       => self::v(isset($pdc->bounce_remark) ? $pdc->bounce_remark : ''),
        ];
    }
}
```

- [ ] **Step 5: Implement `SystemMessenger`**

```php
<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;

/**
 * Sends a system template to one recipient: email when the template has one
 * and the recipient has an address, SMS only when the template's Send SMS
 * switch is on. Never throws for delivery problems; every attempt is logged.
 */
class SystemMessenger
{
    /** @var callable ($mobile, $message) => bool; replaced in tests */
    public static $smsSender = 'sendSms';

    public static function send($key, MessageRecipient $to, array $values, array $options = [])
    {
        $note = null;
        $template = MessageTemplate::where('key', $key)->where('kind', MessageTemplate::KIND_SYSTEM)->first();
        if (!$template) {
            $d = SystemTemplateDefaults::get($key);
            if (!$d) {
                throw new \InvalidArgumentException("Unknown system template key: $key");
            }
            $template = new MessageTemplate($d + ['key' => $key, 'kind' => MessageTemplate::KIND_SYSTEM]);
            $note = 'template_missing: default wording used';
        }
        $base = [
            'template_key' => $key, 'recipient_name' => $to->name,
            'related_type' => isset($options['related'][0]) ? $options['related'][0] : null,
            'related_id'   => isset($options['related'][1]) ? $options['related'][1] : null,
            'triggered_by' => \Auth::check() ? \Auth::id() : null,
        ];
        $result = ['email' => null, 'sms' => null];

        if (!$template->is_active) {
            self::log($base + ['channel' => 'email', 'recipient' => $to->email, 'status' => MessageLog::SKIPPED_OFF, 'note' => $note]);
            $result['email'] = MessageLog::SKIPPED_OFF;
            return $result;
        }

        $values = $values + ['today' => date('d/m/Y'), 'company_name' => 'Al Habib & Co. L.L.C'];
        $blocks = isset($options['blocks']) ? $options['blocks'] : [];
        $out = MessageTemplateRenderer::render($template, $values, $blocks);
        if ($out['unknown']) {
            $note = trim($note . ' unknown placeholders: ' . implode(', ', $out['unknown']));
        }

        if ($out['subject'] !== null && $out['html'] !== null) {
            $row = $base + ['channel' => 'email', 'recipient' => $to->email, 'cc' => $to->cc ? implode(', ', $to->cc) : null,
                            'subject' => $out['subject'], 'body' => $out['html'], 'note' => $note];
            if (!$to->email) {
                $result['email'] = MessageLog::SKIPPED_NO_ADDRESS;
            } else {
                try {
                    $pending = Mail::to($to->email);
                    if ($to->cc) {
                        $pending->cc($to->cc);
                    }
                    $pending->send(new TemplatedMail($out['subject'], $out['html'], isset($options['attachments']) ? $options['attachments'] : []));
                    $result['email'] = MessageLog::SENT;
                } catch (\Throwable $e) {
                    $result['email'] = MessageLog::FAILED;
                    $row['note'] = trim($note . ' ' . $e->getMessage());
                }
            }
            self::log($row + ['status' => $result['email']]);
        }

        if ($template->sms_enabled && $out['sms'] !== null) {
            $row = $base + ['channel' => 'sms', 'recipient' => $to->mobile, 'body' => $out['sms'], 'note' => $note];
            if (!$to->mobile) {
                $result['sms'] = MessageLog::SKIPPED_NO_MOBILE;
            } else {
                try {
                    $ok = call_user_func(self::$smsSender, $to->mobile, $out['sms'], []);
                    $result['sms'] = $ok ? MessageLog::SENT : MessageLog::FAILED;
                    if (!$ok) {
                        $row['note'] = trim($note . ' SMS gateway refused or SMS is disabled in settings');
                    }
                } catch (\Throwable $e) {
                    $result['sms'] = MessageLog::FAILED;
                    $row['note'] = trim($note . ' ' . $e->getMessage());
                }
            }
            self::log($row + ['status' => $result['sms']]);
        }
        return $result;
    }

    private static function log(array $row)
    {
        try {
            MessageLog::create($row + ['created_at' => now()]);
        } catch (\Throwable $e) {
            \Log::error('message_log write failed: ' . $e->getMessage());
        }
    }
}
```

Note for the implementer: `new MessageTemplate($d + …)` works because `$guarded = []`; the extra array keys (`stage`, `placeholders`) are ignored by the renderer — unset them before constructing (`unset($d['stage'], $d['placeholders'])`) so nothing unexpected is set on the model.

- [ ] **Step 6: Run** the feature test → `OK (7 tests…)`. The `Mail::shouldReceive` test replaces the fake; if it conflicts with `Mail::fake()` in `setUp`, call `Mail::swap(...)` via `Mail::shouldReceive` only in that test (it replaces the facade root) — adjust the test, not the messenger, and ledger it.

- [ ] **Step 7: Full suite** `$PHP vendor/bin/phpunit` → unit + feature green (ExampleTest may already fail on `/` — if it failed before this branch, note it and leave it).

- [ ] **Step 8: Commit** — `feat(system-templates): SystemMessenger with sent log, TemplatedMail, value helpers`

---

### Task 5: Cheque bounce via `PdcBounceNotifier`

**Files:** create `Modules/BackOffice/Services/PdcBounceNotifier.php`, `tests/Feature/PdcBounceNotifierTest.php`; modify `PdcController.php` (the ARE blocks in `store()` ~:158-170 and `tenantAjaxPdcBounceSearch` ~:421-440 and ~:454-472, and the `use` lines for the two old emails); delete `ChequeBounceAreEmail.php`, `ChequeBounceEmail.php`, views `Email/cheque_bounce_are_mail.blade.php`, `Email/cheque_bounce_mail.blade.php`.

**Interfaces:**
- Consumes: Task 4 `SystemMessenger::send`, `MessageRecipient`, `MessageValues::contract/employee/cheque`.
- Produces: `PdcBounceNotifier::notify(Pdc $pdc, $href, $notificationId = null): array` — returns `['ares' => int emailed-or-attempted, 'tenant' => status]`.

Behaviour:
1. `$contract = $pdc->tenantContractInfo`; ARE users = `$contract->building->buildingAssignTo` → `buildingAssignToName` → `areUser`, skipping nulls, unique by user id.
2. For each ARE user: `SystemMessenger::send('pdc_bounce_are', new MessageRecipient(name, $user->email, employee_contact_no ?: employee_secondary_no), MessageValues::contract($contract) + MessageValues::cheque($pdc) + MessageValues::employee($user), ['related' => ['pdc', $pdc->id]])`.
3. Tenant: `SystemMessenger::send('pdc_bounce_tenant', new MessageRecipient(tenant_name, tenant_personal_email ?: tenant_contact_email, tenant_contact_no ?: tenant_residence_tel), contract + cheque values, related pdc)` (template arrives off → logged `skipped_off`).
4. In-app alert: if any ARE users, set `$pdc->href = $href`; if `$notificationId !== null` set `$pdc->id = $notificationId` (keeps today's behaviour of each site); `event(new ChequeBounceAre($pdc, $collectionOfAllAreUsers))`. **Ruling in plan:** the in-app alert also goes to all AREs (today last only — same bug as the email; the user approved "all AREs").

- [ ] **Step 1: Failing test** — `tests/Feature/PdcBounceNotifierTest.php` with `DatabaseTransactions`, `Mail::fake()`, `Event::fake([ChequeBounceAre::class])`, SMS sender stubbed as in Task 4:
  - Pick a PDC whose contract's building has ≥2 `preferred_buildings` rows with distinct ARE users that have emails (query: join `preferred_buildings` → `are_buildings` → `users`, `having count(distinct users.id) >= 2`); `markTestSkipped` if none locally.
  - `notify($pdc, 'pdc/x/edit')` → `Mail::assertSent(TemplatedMail::class, N)` where N = distinct ARE users with email; each `hasTo` one of them; `Event::assertDispatched(ChequeBounceAre::class, function ($e) use ($ids) { return $e->users->pluck('id')->sort()->values()->all() == $ids; })`.
  - Log: N rows `pdc_bounce_are` status `sent`, related `('pdc', $pdc->id)`; one `pdc_bounce_tenant` row `skipped_off`.
  - Duplicate assignment: insert (in the transaction) a second `preferred_buildings` row for the same building pointing to an `are_buildings` row of an already-counted user → still N emails.
  - Empty remarks: pdc with `bounce_remark` unset → rendered html contains `Remarks: -`.
  - Mail failure: `Mail::shouldReceive('to')->andThrow(new \Exception('boom'))` → `notify` returns normally, log rows `failed`.

- [ ] **Step 2: Run** → class not found.
- [ ] **Step 3: Implement** the notifier per Behaviour (no arrow functions; `collect($users)->unique('id')->values()`).
- [ ] **Step 4: Run** → pass.
- [ ] **Step 5: Wire the controller.** Replace each ARE block:
  - `store()`: the block from `/*-----------Are Email Starts` to `Are Email Ends*/` (and the commented tenant block above it) becomes `PdcBounceNotifier::notify($pdcData, "pdc/".$pdcData->tenantContractInfo->id."/edit");`
  - `tenantAjaxPdcBounceSearch`, not-posted and posted branches: the commented tenant block + ARE block become `PdcBounceNotifier::notify($isPosted, url("pdc/".$isPosted->tenantContractInfo->id."/edit"), $isPosted->tenantContractInfo->id);` followed by the existing `return response()->json(...)`.
  - Remove `use ...ChequeBounceAreEmail;` / `ChequeBounceEmail` imports; add `use Modules\BackOffice\Services\PdcBounceNotifier;`. Keep the `ChequeBounceAre` event import only if still referenced.
  - Delete the two Mailables and their two views; `grep -rn "ChequeBounceAreEmail\|ChequeBounceEmail\|cheque_bounce_are_mail\|cheque_bounce_mail" Modules app resources` → only backup folders (`bkup*`, `*--.php`) may remain.
- [ ] **Step 6: Controller-level check** — extend the feature test: authenticate as user 1, POST the bounce endpoint used by the bank-sheet page (`tenantAjaxPdcBounceSearch` route, same payload the page sends: pdc id, reason 1, `bounce_remark`) for an unbounced PDC → JSON success, PDC `pdc_cancel_reason` = 1, log rows written, `Mail::assertSent` count = distinct AREs. `$PHP -l` on the controller.
- [ ] **Step 7: Full suite** green. **Commit** — `feat(system-templates): cheque bounce emails via templates to all AREs; tenant bounce template (off)`

---

### Task 6: Editor — Send SMS switch, Reset to default, list badge

**Files:** modify `MessageTemplateController.php`, `MessageTemplate/form.blade.php`, `MessageTemplate/index.blade.php`, `Routes/web.php`; create `tests/Feature/MessageTemplateScreensTest.php`.

**Interfaces:** route `message-templates.reset` (POST `message-templates/{messageTemplate}/reset`, manage permission via the existing `except(['index','preview'])` middleware).

- [ ] **Step 1: Failing feature test** (`DatabaseTransactions`, acting as user 1; for CSRF the app env is `testing`, so `VerifyCsrfToken` skips):
  - PUT update on the `pdc_bounce_are` row with `sms_enabled=1` and content → saved `sms_enabled` true; PUT without it → false.
  - PUT on a **bulk** template with `sms_enabled=1` → stays false.
  - After editing `pdc_bounce_are` subject, POST reset → subject/body/sms equal `SystemTemplateDefaults::get('pdc_bounce_are')` values; `is_active`/`sms_enabled` unchanged; redirect with success.
  - POST reset on a bulk template → 404.
  - GET edit of the system row contains `name="sms_enabled"` and the Reset button; bulk edit page contains neither.
  - GET index shows an "SMS on" badge for a system row with `sms_enabled`.
  - The Project A checks from the scratch script that matter (store validation, view-only 403s) are ported here too (closes review minor M4).
- [ ] **Step 2: Run** → fail.
- [ ] **Step 3: Implement**
  - `validated()`: after building `$data`, `if ($template && $template->isSystem()) { $data['sms_enabled'] = (bool) $request->input('sms_enabled'); }`.
  - `reset(MessageTemplate $messageTemplate)`: abort 404 unless system; `$d = SystemTemplateDefaults::get($messageTemplate->key)`; if null → redirect back with error "No default wording exists for this template."; else update `email_subject`, `email_body`, `sms_body`, `updated_by`; redirect to edit with success "Default wording restored."
  - Route in `web.php` right after the preview route: `Route::post('message-templates/{messageTemplate}/reset', 'MessageTemplateController@reset')->name('message-templates.reset');`
  - Form (system only): a second switch after Active: `<label class="mt-switch"><input type="checkbox" name="sms_enabled" value="1" …> Send SMS</label>` with help text "When on, the SMS text is sent to the recipient's mobile every time, as well as the email." — old-input handling like `is_active`. In the footer, a **Reset to default** button that submits a separate small form (`id="mt-reset-form"`, POST to `message-templates.reset`, `@csrf`) with confirm "Replace the subject, body and SMS text with the default wording? Your changes will be lost." (place the separate `<form>` outside the main form).
  - Index: in Channels, for system rows with `sms_enabled` show `SMS on` badge (green) instead of plain `SMS`.
- [ ] **Step 4: Run** → pass; full suite green. Screenshot the system edit page (headless Chrome, asset URLs rewritten to `file:///C:/laragon/www/plms_master/`).
- [ ] **Step 5: Commit** — `feat(system-templates): Send SMS switch and Reset to default on system templates`

---

### Task 7: Mail → Sent log screen

**Files:** create `MessageLogController.php`, `Resources/views/MessageLog/index.blade.php`, `tests/Feature/MessageLogScreenTest.php`; modify `Routes/web.php`, `breadcrumbs.php`.

**Interfaces:** routes `message-log.index` (GET `message-log`), `message-log.show` (GET `message-log/{id}`, JSON); both `auth` + `permission:view_message_log`. View vars: `$logs` (paginator 50), `$filters` (from, to, template, channel, status, q), `$templates` (key ⇒ name from `message_templates` system rows), `$statuses` (`MessageLog::STATUSES`).

- [ ] **Step 1: Failing feature test** — create 3 log rows in the transaction (sent email, failed sms, skipped_off) with distinct recipients; as user 1: index 200 shows all three; `?status=failed` shows only the failed; `?q=<recipient>` filters; `?from=` tomorrow shows none + empty-state text; show JSON returns `subject`, `body`, `status_label`; a user with a throwaway role lacking `view_message_log` gets 403 on both.
- [ ] **Step 2: Run** → fail.
- [ ] **Step 3: Implement** controller: filters default `from` = today − 6 days, `to` = today (inclusive, `created_at < to + 1 day`); `q` matches `recipient` or `recipient_name` or `subject` (`ilike`); order `id desc`; `paginate(50)->appends($filters)`. `show($id)` → `response()->json([...row, 'status_label' => …, 'created' => d/m/Y H:i])`, 404 when missing.
  View (reuse `MessageTemplate._styles`): filter bar (From, To date inputs; Template select; Channel All/Email/SMS; Status select; Search), table columns When (d/m/Y H:i), Template (name, key small), Channel icon+text, Recipient (name + address), Subject / SMS start (60 chars), Status badge (Sent green, Failed red, Skipped grey) with note as `title` and shown under the badge for failed rows, **View** button → modal: meta list + email body in `<iframe sandbox>` via `srcdoc` (same approach as the template preview) or SMS text in a bubble. Empty state: "Nothing was sent in this period." Breadcrumb `message-log.index` (parent home, "Sent log").
- [ ] **Step 4: Run** → pass; full suite green; screenshot list with a failed row expanded and the modal.
- [ ] **Step 5: Commit** — `feat(system-templates): Mail -> Sent log screen`

---

### Task 8: Wrap-up

- [ ] Full suite green; `grep -rn "=>\s*fn\|fn(\|?->\|match (" ` over new/changed PHP files → no hits.
- [ ] Real-browser pass on the local site if available: bounce a test PDC (then roll it back manually or use a scratch copy) — otherwise the Task 5 controller-level test stands.
- [ ] Final whole-branch review (fresh reviewer, most capable model) with this plan's Review Focus; fix Critical/Important.
- [ ] Hand-over note for the user: files list, deploy steps — **upload code first**, then migrations `2026_09_30_000000`, `000001`, `000002` (and Project A's `2026_09_29_000000` / `000001` if not yet live), `route:clear`, `view:clear`; review the two templates; assign `view_message_log` to other roles if wanted.
