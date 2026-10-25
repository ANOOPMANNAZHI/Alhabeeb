# System Email Templates — C4 (Accounts and the rest) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Employee welcome, admin password reset, "Forgot password" and the ARE residence reminder go through system templates; employees get a set-password link instead of a password by email.

**Architecture:** Four new system template keys in `SystemTemplateDefaults` (seeded by migration `2026_09_30_000005`). A new `AccountMessages` service builds password links with Laravel's password broker and sends through `SystemMessenger`; `SystemMessenger` learns to hide secret values (links) in the sent log. The ARE reminder goes through `StaffMessages` and gets a once-a-day guard before its cron call is switched back on.

**Tech Stack:** Laravel 5.7, PHP (live runs < 7.4), PostgreSQL, PHPUnit 7.

**Spec:** `docs/superpowers/specs/2026-09-29-system-email-templates-design.md` (section "C4 — Accounts and the rest")

## Global Constraints

- Live PHP is older than 7.4: no arrow functions (`fn`), no typed properties, no `??=`, no nullsafe, no `match`.
- Run PHP/tests with `C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe` (the CLI default PHP 8 cannot boot this Laravel 5.7 app).
- Test command: `php -d memory_limit=1G vendor/bin/phpunit` from `C:\laragon\www\plms_c4` (128M exhausts).
- Known baseline: 279 tests, 6 failures in `MessageTemplateScreensTest` / `MessageLogScreenTest` caused by the local DB menu row for Bounce Reason (`Route [bounce-reason.index] not defined`) — this branch predates master's Bounce Reason master. These 6 are not caused by C4 and are fixed when the branches are merged with master. Any *other* failure is a regression.
- Work only in the worktree `C:\laragon\www\plms_c4` (branch `feature/system-email-templates-c4`). Never switch branches in `C:\laragon\www\plms_master` (it serves the local site).
- System templates: **Active off = not sent at all**; SMS only when the template's `sms_enabled` is on.
- Defaults (spec): `employee_welcome` on, `employee_password_reset` on, `password_forgot` on, `are_residence_reminder` **off**; SMS off for all four.
- Password links use Laravel's password broker (`password_resets`, existing `/password/reset/{token}` page); lifetime 24 hours: `config/auth.php` `passwords.users.expire` = `1440`.
- A new employee gets a random unguessable password (not `username + id`); admin reset does **not** change the password — the current one keeps working until the user sets a new one.
- Sign-off is `self::regards()` ("Al Habib Team") like the other system templates.
- Seed migrations are insert-only (`SystemTemplateDefaults::seed`), never overwrite edited rows.
- Deploy: upload code first, then run migration `2026_09_30_000005`, then `php artisan config:clear`, `route:clear`, `view:clear`.

## Review Focus

- **Password links in the Sent log:** `message_log.body` stores every email body. A logged set-password/reset link would let anyone with Sent log access take over the account. Expected: the log shows `[link hidden]`, never the token. → Task 1 test + Task 3 / Task 5 tests.
- **Employee without an email address:** no token row may be created with an empty email, the account is still created, and the admin sees why no email went out. → Task 3 test (`welcome` without email) + Task 4 test (admin reset without email).
- **ARE reminder re-enabled inside the every-minute command:** without a guard it would email every ARE every minute. Expected: runs only at 09:00, one email per ARE per tenant per run. → Task 6 tests (guard + duplicates from the join).
- **Admin "Reset Password" must not lock the user out:** the old password keeps working until the link is used. → Task 4 test (hash unchanged).
- **Forgot password for an unknown email / template switched off:** unknown email → Laravel's normal "We can't find a user" error, nothing sent; template off → nothing sent, log `skipped_off`. → Task 5 tests.

---

### Task 1: SystemMessenger hides secret values in the sent log

**Files:**
- Modify: `Modules/BackOffice/Services/SystemMessenger.php`
- Test: `tests/Feature/SystemMessengerTest.php`

**Interfaces:**
- Produces: `SystemMessenger::send($key, MessageRecipient $to, array $values, array $options = [])` accepts `$options['secret']` — array of strings (e.g. a link). Each non-empty secret is replaced by `[link hidden]` in the logged `subject`, `body` (both the raw and the `htmlspecialchars` form) for email and SMS rows. The email/SMS actually sent is unchanged.

- [ ] **Step 1: Write the failing test** — append to `tests/Feature/SystemMessengerTest.php` (inside the class):

```php
    public function test_secret_values_are_hidden_in_the_log_but_sent()
    {
        MessageTemplate::where('key', 'pdc_bounce_are')->update(['is_active' => true, 'sms_enabled' => true,
            'email_body' => '<p>Link: <a href="{remarks}">go</a> {remarks}</p>', 'sms_body' => 'Link {remarks}']);
        $secret = 'https://plms.test/password/reset/abc123?email=a%40b.om&x=1';
        $values = ['remarks' => $secret] + $this->values();
        SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com', '99123456'), $values, ['secret' => [$secret]]);

        Mail::assertSent(TemplatedMail::class, function ($m) {
            return strpos($m->bodyHtml, 'abc123') !== false;
        });
        $this->assertContains('abc123', $this->sms[0][1]);
        $rows = MessageLog::where('template_key', 'pdc_bounce_are')->latest('id')->take(2)->get();
        foreach ($rows as $row) {
            $this->assertNotContains('abc123', (string) $row->body, $row->channel);
            $this->assertContains('[link hidden]', (string) $row->body, $row->channel);
        }
    }
```

- [ ] **Step 2: Run it and see it fail**

Run: `php -d memory_limit=1G vendor/bin/phpunit --filter test_secret_values_are_hidden_in_the_log_but_sent`
Expected: FAIL (`'abc123'` found in the logged body).

- [ ] **Step 3: Implement** — in `SystemMessenger::send`, right after `$result = ['email' => null, 'sms' => null];` add:

```php
        // Values that must never reach the sent log (e.g. set-password links)
        $secrets = [];
        foreach (isset($options['secret']) ? (array) $options['secret'] : [] as $s) {
            if ((string) $s !== '') {
                $secrets[] = (string) $s;
                $secrets[] = htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
            }
        }
```

Change the three `self::log(...)` calls to pass `$secrets` as a second argument:

```php
            self::log($base + ['channel' => $channel, 'recipient' => $channel === 'sms' ? $to->mobile : $to->email,
                               'status' => MessageLog::SKIPPED_OFF, 'note' => $note], $secrets);
```
```php
            self::log($row + ['status' => $result['email']], $secrets);
```
```php
            self::log($row + ['status' => $result['sms']], $secrets);
```

Change `log` to:

```php
    private static function log(array $row, array $secrets = [])
    {
        if ($secrets) {
            foreach (['subject', 'body'] as $field) {
                if (isset($row[$field])) {
                    $row[$field] = str_replace(array_unique($secrets), '[link hidden]', (string) $row[$field]);
                }
            }
        }
        // Fit the columns: on PostgreSQL an overflowing insert would abort a caller's open transaction.
```
(the rest of `log` stays as it is).

- [ ] **Step 4: Run the test and the whole messenger test file**

Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Feature/SystemMessengerTest.php`
Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/SystemMessenger.php tests/Feature/SystemMessengerTest.php
git commit -m "feat(system-templates): secret values (links) are hidden in the sent log"
```

---

### Task 2: C4 template defaults, seed migration, 24-hour link lifetime

**Files:**
- Modify: `Modules/BackOffice/Services/SystemTemplateDefaults.php` (add placeholder set + 4 keys inside `all()`)
- Create: `database/migrations/2026_09_30_000005_seed_c4_system_templates.php`
- Modify: `config/auth.php:98` (`'expire' => 60` → `'expire' => 1440`)
- Test: `tests/Unit/SystemTemplateDefaultsTest.php`

**Interfaces:**
- Produces keys `employee_welcome`, `employee_password_reset`, `password_forgot`, `are_residence_reminder` (stage `C4`, audience `staff`).
- Placeholders: welcome/reset: `employee_name`, `username`, `set_password_link`; forgot: `employee_name`, `username`, `reset_link`; ARE reminder: `employee_name`, `tenant_name`, `contract_no`, `building`, `residence_expiry`.

- [ ] **Step 1: Write the failing test** — add to `tests/Unit/SystemTemplateDefaultsTest.php`:

```php
    public function test_c4_keys_exist_with_expected_switches()
    {
        foreach (['employee_welcome', 'employee_password_reset', 'password_forgot', 'are_residence_reminder'] as $key) {
            $d = D::get($key);
            $this->assertNotNull($d, $key);
            $this->assertSame('C4', $d['stage'], $key);
            $this->assertSame('staff', $d['audience'], $key);
            $this->assertFalse($d['sms_enabled'], "$key SMS off");
        }
        $this->assertTrue(D::get('employee_welcome')['is_active']);
        $this->assertTrue(D::get('employee_password_reset')['is_active']);
        $this->assertTrue(D::get('password_forgot')['is_active']);
        $this->assertFalse(D::get('are_residence_reminder')['is_active']);
        $this->assertContains('href="{set_password_link}"', D::get('employee_welcome')['email_body']);
        $this->assertContains('{username}', D::get('employee_welcome')['email_body']);
        $this->assertContains('href="{set_password_link}"', D::get('employee_password_reset')['email_body']);
        $this->assertContains('href="{reset_link}"', D::get('password_forgot')['email_body']);
        $this->assertContains('{residence_expiry}', D::get('are_residence_reminder')['email_body']);
        $this->assertNull(D::get('employee_welcome')['sms_body'], 'no password link by SMS');
        $this->assertNull(D::get('password_forgot')['sms_body']);
    }
```

Also add to the same class (config value):

```php
    public function test_password_links_last_24_hours()
    {
        $config = require __DIR__ . '/../../config/auth.php';
        $this->assertSame(1440, $config['passwords']['users']['expire']);
    }
```

- [ ] **Step 2: Run and see both fail**

Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Unit/SystemTemplateDefaultsTest.php`
Expected: the two new tests FAIL (null key / 60 ≠ 1440).

- [ ] **Step 3: Implement defaults** — in `SystemTemplateDefaults`, add a placeholder helper next to `employeePlaceholders()`:

```php
    private static function accountPlaceholders($linkKey)
    {
        return [
            'username' => ['label' => 'Login username', 'sample' => 'rashid.h'],
            $linkKey   => ['label' => 'Password link (valid 24 hours)', 'sample' => 'https://plms.alhabib.om/password/reset/sample-token?email=rashid%40alhabib.om'],
        ];
    }
```

Inside `all()`, just before `return $out;` (after the legal `foreach`), add:

```php
        // C4: accounts and the rest. Links go by email only (never SMS).
        $out['employee_welcome'] = [
            'name' => 'New employee account (set password)', 'audience' => 'staff', 'stage' => 'C4',
            'email_subject' => 'Your PLMS account is ready',
            'email_body' => '<p>Dear {employee_name},</p><p>An account has been created for you on PLMS.</p>'
                . '<p>Username: <strong>{username}</strong></p>'
                . '<p>Please set your password using this link: <a href="{set_password_link}">Set my password</a></p>'
                . '<p>The link is valid for 24 hours. If it expires, use "Forgot Password?" on the login page or ask the administrator for a new link.</p>'
                . self::regards(),
            'sms_body' => null, 'is_active' => true, 'sms_enabled' => false,
            'placeholders' => self::employeePlaceholders() + self::accountPlaceholders('set_password_link'),
        ];
        $out['employee_password_reset'] = [
            'name' => 'Employee password reset (set new password)', 'audience' => 'staff', 'stage' => 'C4',
            'email_subject' => 'Set a new PLMS password',
            'email_body' => '<p>Dear {employee_name},</p><p>The administrator has sent you a link to set a new password for your PLMS account <strong>{username}</strong>.</p>'
                . '<p><a href="{set_password_link}">Set a new password</a></p>'
                . '<p>The link is valid for 24 hours. Your current password keeps working until you change it.</p>'
                . self::regards(),
            'sms_body' => null, 'is_active' => true, 'sms_enabled' => false,
            'placeholders' => self::employeePlaceholders() + self::accountPlaceholders('set_password_link'),
        ];
        $out['password_forgot'] = [
            'name' => 'Forgot password (login page)', 'audience' => 'staff', 'stage' => 'C4',
            'email_subject' => 'Reset your PLMS password',
            'email_body' => '<p>Dear {employee_name},</p><p>We received a request to reset the password of your PLMS account <strong>{username}</strong>.</p>'
                . '<p><a href="{reset_link}">Reset my password</a></p>'
                . '<p>The link is valid for 24 hours. If you did not ask for this, ignore this email; your password stays the same.</p>'
                . self::regards(),
            'sms_body' => null, 'is_active' => true, 'sms_enabled' => false,
            'placeholders' => self::employeePlaceholders() + self::accountPlaceholders('reset_link'),
        ];
        $out['are_residence_reminder'] = [
            'name' => 'Tenant Residence ID expired (ARE)', 'audience' => 'staff', 'stage' => 'C4',
            'email_subject' => 'Residence ID expired - {tenant_name} ({contract_no})',
            'email_body' => '<p>Dear {employee_name},</p><p>The Residence ID of tenant <strong>{tenant_name}</strong> '
                . '(contract {contract_no}, {building}) expired on {residence_expiry}.</p><p>Please follow up with the tenant for the renewed ID.</p>'
                . self::regards(),
            'sms_body' => 'Residence ID of {tenant_name} ({contract_no}, {building}) expired on {residence_expiry}. - PLMS',
            'is_active' => false, 'sms_enabled' => false,
            'placeholders' => self::employeePlaceholders()
                + ['tenant_name' => self::tenantPlaceholders()['tenant_name'], 'contract_no' => self::contractPlaceholders()['contract_no'],
                   'building' => self::contractPlaceholders()['building'],
                   'residence_expiry' => ['label' => 'Residence ID expiry', 'sample' => '15/09/2026']],
        ];
```

- [ ] **Step 4: Migration** — create `database/migrations/2026_09_30_000005_seed_c4_system_templates.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/** C4 system templates (accounts and the rest). Insert-only: edited rows are never overwritten. */
class SeedC4SystemTemplates extends Migration
{
    const KEYS = ['employee_welcome', 'employee_password_reset', 'password_forgot', 'are_residence_reminder'];

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

- [ ] **Step 5: Config** — `config/auth.php`, in `passwords.users`: `'expire' => 1440,` (24 hours; comment above already explains the setting).

- [ ] **Step 6: Run the migration on the local DB and the tests**

Run: `php artisan migrate --path=database/migrations/2026_09_30_000005_seed_c4_system_templates.php --force`
Expected: `Migrated: 2026_09_30_000005_seed_c4_system_templates`
Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Unit/SystemTemplateDefaultsTest.php`
Expected: all PASS (including the existing "every default is complete and uses only known placeholders").

- [ ] **Step 7: Commit**

```bash
git add Modules/BackOffice/Services/SystemTemplateDefaults.php database/migrations/2026_09_30_000005_seed_c4_system_templates.php config/auth.php tests/Unit/SystemTemplateDefaultsTest.php
git commit -m "feat(system-templates): C4 defaults (welcome, password reset, forgot, ARE residence) and 24h links"
```

---

### Task 3: AccountMessages service (links + sends)

**Files:**
- Create: `Modules/BackOffice/Services/AccountMessages.php`
- Test: `tests/Feature/AccountMessagesTest.php`

**Interfaces:**
- Consumes: `SystemMessenger::send(..., ['secret' => [...]])` (Task 1), keys from Task 2, `StaffMessages::userRecipients($users)` (returns `[[User, MessageRecipient], ...]`), `MessageValues::employee($user)`.
- Produces:
  - `AccountMessages::randomPassword()` → string, 40 random chars.
  - `AccountMessages::passwordLink(User $user)` → string URL `route('password.reset', ['token' => ..., 'email' => $user->email])`; creates a `password_resets` row via `Password::broker()->createToken($user)`.
  - `AccountMessages::welcome(User $user)` → `['email' => status, 'sms' => status]` (key `employee_welcome`).
  - `AccountMessages::passwordReset(User $user)` → same shape (key `employee_password_reset`).
  - `AccountMessages::forgot(User $user, $token)` → same shape (key `password_forgot`, link from the given token).
  - No email address → no token is created, result `email` = `skipped_no_address`.

- [ ] **Step 1: Write the failing tests** — create `tests/Feature/AccountMessagesTest.php`:

```php
<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\AccountMessages;
use Modules\BackOffice\Services\SystemMessenger;

class AccountMessagesTest extends TestCase
{
    use DatabaseTransactions;

    public $sms = [];

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        $self = $this;
        SystemMessenger::$smsSender = function ($mobile, $msg) use ($self) { $self->sms[] = [$mobile, $msg]; return true; };
        MessageTemplate::whereIn('key', ['employee_welcome', 'employee_password_reset', 'password_forgot'])->update(['is_active' => true]);
    }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function staff($email)
    {
        $user = User::whereHas('employee')->orderBy('id')->skip(1)->first();
        DB::table('users')->where('id', $user->id)->update(['email' => $email]);
        return User::find($user->id);
    }

    public function test_random_password_is_long_and_not_username_based()
    {
        $a = AccountMessages::randomPassword();
        $this->assertSame(40, strlen($a));
        $this->assertNotSame($a, AccountMessages::randomPassword());
    }

    public function test_welcome_sends_a_working_link_and_hides_it_in_the_log()
    {
        $user = $this->staff('new.staff@example.com');
        $r = AccountMessages::welcome($user);
        $this->assertSame('sent', $r['email']);

        $link = null;
        Mail::assertSent(TemplatedMail::class, function ($m) use (&$link, $user) {
            if (!$m->hasTo('new.staff@example.com') || $m->subject !== 'Your PLMS account is ready') {
                return false;
            }
            preg_match('#href="([^"]*/password/reset/[^"]+)"#', $m->bodyHtml, $x);
            $link = isset($x[1]) ? html_entity_decode($x[1]) : null;
            return $link && strpos($m->bodyHtml, $user->username) !== false;
        });
        preg_match('#/password/reset/([^?"]+)#', $link, $t);
        $this->assertTrue(Password::broker()->tokenExists($user, $t[1]), 'token in the link is valid');
        $this->assertContains('email=new.staff%40example.com', $link);

        $log = MessageLog::where('template_key', 'employee_welcome')->latest('id')->first();
        $this->assertNotContains($t[1], $log->body);
        $this->assertContains('[link hidden]', $log->body);
        $this->assertSame([], $this->sms);
    }

    public function test_welcome_without_email_creates_no_token_and_reports_it()
    {
        $user = $this->staff('');
        $before = DB::table('password_resets')->count();
        $r = AccountMessages::welcome($user);
        $this->assertSame('skipped_no_address', $r['email']);
        $this->assertSame($before, DB::table('password_resets')->count());
        Mail::assertNothingSent();
    }

    public function test_password_reset_uses_its_own_template()
    {
        $user = $this->staff('reset.me@example.com');
        $this->assertSame('sent', AccountMessages::passwordReset($user)['email']);
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('reset.me@example.com') && $m->subject === 'Set a new PLMS password';
        });
    }

    public function test_forgot_uses_the_given_token()
    {
        $user = $this->staff('forgot@example.com');
        AccountMessages::forgot($user, 'tok-123');
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('forgot@example.com') && $m->subject === 'Reset your PLMS password'
                && strpos($m->bodyHtml, '/password/reset/tok-123') !== false;
        });
        $log = MessageLog::where('template_key', 'password_forgot')->latest('id')->first();
        $this->assertNotContains('tok-123', $log->body);
    }

    public function test_switched_off_template_sends_nothing()
    {
        MessageTemplate::where('key', 'employee_password_reset')->update(['is_active' => false]);
        $user = $this->staff('off@example.com');
        $this->assertSame('skipped_off', AccountMessages::passwordReset($user)['email']);
        Mail::assertNothingSent();
    }
}
```

- [ ] **Step 2: Run and see them fail**

Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Feature/AccountMessagesTest.php`
Expected: FAIL — `Class 'Modules\BackOffice\Services\AccountMessages' not found`.

- [ ] **Step 3: Implement** — create `Modules/BackOffice/Services/AccountMessages.php`:

```php
<?php

namespace Modules\BackOffice\Services;

use App\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Account messages (C4): new employee welcome, admin password reset and the
 * login page "Forgot password". Passwords are never emailed: the user gets a
 * set-password link (Laravel password broker, valid config auth.passwords.users.expire
 * minutes), and the link is hidden in the sent log.
 */
class AccountMessages
{
    public static function randomPassword()
    {
        return Str::random(40);
    }

    /** New reset token for the user (replaces any older one) as a link to the reset page. */
    public static function passwordLink(User $user)
    {
        $token = Password::broker()->createToken($user);
        return self::linkFor($user, $token);
    }

    public static function welcome(User $user)
    {
        return self::sendLink('employee_welcome', $user, 'set_password_link', $user->email ? self::passwordLink($user) : '');
    }

    public static function passwordReset(User $user)
    {
        return self::sendLink('employee_password_reset', $user, 'set_password_link', $user->email ? self::passwordLink($user) : '');
    }

    /** Login page "Forgot password": the broker already made the token. */
    public static function forgot(User $user, $token)
    {
        return self::sendLink('password_forgot', $user, 'reset_link', self::linkFor($user, $token));
    }

    private static function linkFor(User $user, $token)
    {
        return route('password.reset', ['token' => $token, 'email' => $user->email]);
    }

    private static function sendLink($key, User $user, $linkKey, $link)
    {
        $pair = StaffMessages::userRecipients([$user]);
        $values = MessageValues::employee($user) + ['username' => (string) $user->username, $linkKey => $link];
        return SystemMessenger::send($key, $pair[0][1], $values, ['related' => ['user', $user->id], 'secret' => [$link]]);
    }
}
```

- [ ] **Step 4: Run the tests**

Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Feature/AccountMessagesTest.php`
Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/AccountMessages.php tests/Feature/AccountMessagesTest.php
git commit -m "feat(system-templates): AccountMessages - set-password links through system templates"
```

---

### Task 4: Employee create and admin reset send links (no passwords by email)

**Files:**
- Modify: `Modules/Masters/Http/Controllers/EmployeeController.php` (`store` ~:150–184, `resetPassword` ~:371–395, imports)
- Delete: `Modules/Masters/Emails/EmployeeLoginEmail.php`, `Modules/Masters/Emails/EmployeeResetEmail.php`, `Modules/Masters/Resources/views/Email/employee_mail.blade.php`, `Modules/Masters/Resources/views/Email/employee_resetpassword_mail.blade.php`
- Test: `tests/Feature/EmployeeAccountLinkTest.php`

**Interfaces:**
- Consumes: `AccountMessages::randomPassword()`, `AccountMessages::welcome(User)`, `AccountMessages::passwordReset(User)` (Task 3).
- Route used by the test: `GET employeeResetPass/{id}` named `employee.resetPassword` (`{id}` = employee id; permission `reset_password_employee`; user 1 is super_admin).

- [ ] **Step 1: Write the failing tests** — create `tests/Feature/EmployeeAccountLinkTest.php`:

```php
<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\SystemMessenger;

class EmployeeAccountLinkTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        SystemMessenger::$smsSender = function () { return true; };
        MessageTemplate::where('key', 'employee_password_reset')->update(['is_active' => true]);
        $this->actingAs(User::find(1));
    }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function employeeUser($email)
    {
        $user = User::where('user_type', 'employee')->whereHas('employee')->orderBy('id')->skip(2)->first();
        DB::table('users')->where('id', $user->id)->update(['email' => $email]);
        return User::find($user->id);
    }

    public function test_admin_reset_sends_link_and_keeps_the_password()
    {
        $user = $this->employeeUser('staff.reset@example.com');
        $hashBefore = $user->password;
        $this->get(route('employee.resetPassword', $user->user_type_id))
            ->assertRedirect(route('employee.index'))
            ->assertSessionHas('success');
        $this->assertSame($hashBefore, User::find($user->id)->password, 'current password still works');
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('staff.reset@example.com') && $m->subject === 'Set a new PLMS password'
                && strpos($m->bodyHtml, '/password/reset/') !== false;
        });
    }

    public function test_admin_reset_without_email_explains_why()
    {
        $user = $this->employeeUser('');
        $this->get(route('employee.resetPassword', $user->user_type_id))
            ->assertRedirect(route('employee.index'))
            ->assertSessionHas('error');
        Mail::assertNothingSent();
    }

    public function test_admin_reset_with_template_off_explains_why()
    {
        MessageTemplate::where('key', 'employee_password_reset')->update(['is_active' => false]);
        $user = $this->employeeUser('off@example.com');
        $this->get(route('employee.resetPassword', $user->user_type_id))->assertSessionHas('error');
        Mail::assertNothingSent();
    }

    public function test_old_employee_mail_classes_are_gone()
    {
        $this->assertFalse(class_exists('Modules\Masters\Emails\EmployeeLoginEmail'));
        $this->assertFalse(class_exists('Modules\Masters\Emails\EmployeeResetEmail'));
    }
}
```

- [ ] **Step 2: Run and see them fail**

Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Feature/EmployeeAccountLinkTest.php`
Expected: FAIL (password hash changes; old classes exist; old email sent instead of TemplatedMail).

- [ ] **Step 3: Implement `resetPassword`** — replace the whole method body with:

```php
    public function resetPassword($id,Employee $employee)
    {
      $user = User::where('user_type_id',$id)->where('user_type','employee')->first();
      if (!$user) {
          session()->flash('error', 'This employee has no login user');
          return redirect()->route('employee.index');
      }

      // The current password keeps working; the user sets a new one from the emailed link.
      $sent = AccountMessages::passwordReset($user);
      if ($sent['email'] === MessageLog::SENT) {
          session()->flash('success', 'A link to set a new password was emailed to ' . $user->email);
      } else {
          session()->flash('error', self::linkNotSentReason($sent['email']));
      }
      return redirect()->route('employee.index');
    }

    /** Why the set-password email did not go out, for the admin. */
    private static function linkNotSentReason($status)
    {
      if ($status === MessageLog::SKIPPED_NO_ADDRESS) {
          return 'No link sent: the employee has no email address. Add one and use Reset Password again.';
      }
      if ($status === MessageLog::SKIPPED_OFF) {
          return 'No link sent: the password email template is switched off (Mail -> Templates).';
      }
      return 'The password email could not be sent. See Mail -> Sent log for the reason.';
    }
```

- [ ] **Step 4: Implement `store`** — in the `User::create([...])` array change

```php
        'password'        => Hash::make($request['username'].$empInsertId),
```
to
```php
        'password'        => Hash::make(AccountMessages::randomPassword()), // never emailed; the user sets their own from the link
```

and replace

```php
      $user->assignRole($role_name);
      $user->new_password = $request['username'].$empInsertId;

      Mail::to($request['email'])->send(new EmployeeLoginEmail($user));

      session()->flash('success', 'Employee created successfully');
```
with
```php
      $user->assignRole($role_name);

      $sent = AccountMessages::welcome($user);
      session()->flash('success', 'Employee created successfully');
      if ($sent['email'] !== MessageLog::SENT) {
          session()->flash('error', 'Employee created, but no set-password email went out. ' . self::linkNotSentReason($sent['email']));
      }
```

Imports at the top of the controller: add

```php
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Services\AccountMessages;
```
and remove the `use` lines for `EmployeeLoginEmail` and `EmployeeResetEmail` (keep `Mail` only if still used elsewhere in the file — check with `grep -n "Mail::" Modules/Masters/Http/Controllers/EmployeeController.php`; remove the `Mail` import if there are no other uses).

- [ ] **Step 5: Delete the old mail classes and views**

```bash
git rm Modules/Masters/Emails/EmployeeLoginEmail.php Modules/Masters/Emails/EmployeeResetEmail.php Modules/Masters/Resources/views/Email/employee_mail.blade.php Modules/Masters/Resources/views/Email/employee_resetpassword_mail.blade.php
grep -rn "EmployeeLoginEmail\|EmployeeResetEmail\|employee_mail\|employee_resetpassword_mail" app Modules resources routes --include=*.php
```
Expected: the grep prints nothing.

- [ ] **Step 6: Run tests + lint**

Run: `php -l Modules/Masters/Http/Controllers/EmployeeController.php` → `No syntax errors detected`
Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Feature/EmployeeAccountLinkTest.php tests/Feature/AccountMessagesTest.php`
Expected: all PASS.

- [ ] **Step 7: Commit**

```bash
git add Modules/Masters/Http/Controllers/EmployeeController.php tests/Feature/EmployeeAccountLinkTest.php
git commit -m "feat(system-templates): new employee and admin reset get a set-password link, no password by email"
```

---

### Task 5: Login page "Forgot password" through the template

**Files:**
- Modify: `app/User.php` (add `sendPasswordResetNotification`)
- Modify: `app/Http/Controllers/Auth/ForgotPasswordController.php` (remove the dead `sendPasswordResetNotification`)
- Modify: `resources/views/auth/passwords/reset.blade.php:33` (pre-fill the email from the link)
- Test: `tests/Feature/ForgotPasswordTemplateTest.php`

**Interfaces:**
- Consumes: `AccountMessages::forgot(User $user, $token)` (Task 3).
- Laravel 5.7: `SendsPasswordResetEmails::sendResetLinkEmail` → broker → `$user->sendPasswordResetNotification($token)`; routes `password.email` (POST `password/email`), `password.reset` (GET `password/reset/{token}`, the controller passes `$email` from the query to the view).

- [ ] **Step 1: Write the failing tests** — create `tests/Feature/ForgotPasswordTemplateTest.php`:

```php
<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\SystemMessenger;

class ForgotPasswordTemplateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        SystemMessenger::$smsSender = function () { return true; };
        MessageTemplate::where('key', 'password_forgot')->update(['is_active' => true]);
    }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    public function test_forgot_password_sends_the_template_not_the_laravel_notification()
    {
        $user = User::whereHas('employee')->orderBy('id')->skip(3)->first();
        DB::table('users')->where('id', $user->id)->update(['email' => 'forgot.me@example.com']);

        $this->post(route('password.email'), ['email' => 'forgot.me@example.com'])->assertSessionHas('status');

        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('forgot.me@example.com') && $m->subject === 'Reset your PLMS password'
                && strpos($m->bodyHtml, '/password/reset/') !== false;
        });
        Notification::assertNothingSent();
        $this->assertSame(1, DB::table('password_resets')->where('email', 'forgot.me@example.com')->count());
        $log = MessageLog::where('template_key', 'password_forgot')->latest('id')->first();
        $this->assertContains('[link hidden]', $log->body);
    }

    public function test_unknown_email_sends_nothing()
    {
        $this->post(route('password.email'), ['email' => 'nobody-here@example.com'])->assertSessionHasErrors('email');
        Mail::assertNothingSent();
    }

    public function test_reset_page_prefills_the_email_from_the_link()
    {
        $this->get(route('password.reset', ['token' => 'abc', 'email' => 'x.y@example.com']))
            ->assertStatus(200)
            ->assertSee('value="x.y@example.com"');
    }
}
```

- [ ] **Step 2: Run and see them fail**

Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Feature/ForgotPasswordTemplateTest.php`
Expected: first test FAILS (Laravel's `ResetPassword` notification is used, no TemplatedMail); third FAILS (email not pre-filled).

- [ ] **Step 3: Implement** — `app/User.php`, inside the class (after the `use Notifiable, UserRoles;` line's properties/methods, anywhere in the class body):

```php
    /**
     * Login page "Forgot password": the system template password_forgot
     * (Mail -> Templates) instead of Laravel's built-in notification.
     */
    public function sendPasswordResetNotification($token)
    {
        \Modules\BackOffice\Services\AccountMessages::forgot($this, $token);
    }
```

`app/Http/Controllers/Auth/ForgotPasswordController.php`: delete the `sendPasswordResetNotification` method and its doc comment (dead code; it referenced a class that does not exist).

`resources/views/auth/passwords/reset.blade.php` line 33: change `value="{{ old('email') }}"` to `value="{{ old('email', isset($email) ? $email : '') }}"`.

- [ ] **Step 4: Run the tests**

Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Feature/ForgotPasswordTemplateTest.php`
Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add app/User.php app/Http/Controllers/Auth/ForgotPasswordController.php resources/views/auth/passwords/reset.blade.php tests/Feature/ForgotPasswordTemplateTest.php
git commit -m "feat(system-templates): login page Forgot password uses the password_forgot template"
```

---

### Task 6: ARE residence reminder through the template, once a day

**Files:**
- Modify: `Modules/BackOffice/Services/StaffMessages.php` (add `areResidenceReminder`)
- Modify: `Modules/Sales/Http/Controllers/TenantController.php` (`areReminderNotification` ~:470)
- Modify: `app/Console/Commands/NotificationCommand.php` (~:86 re-enable the call, wrapped like the others)
- Delete: the old mail class used there, `AreReminderEmail` (find with `grep -rn "class AreReminderEmail" Modules app`) and its view, if nothing else uses them
- Test: `tests/Feature/AreResidenceReminderTest.php`

**Interfaces:**
- Consumes: `SystemMessenger::send`, key `are_residence_reminder` (Task 2).
- Produces: `StaffMessages::areResidenceReminder($rows)` → int (number of messages attempted). `$rows`: iterable of objects with `are_user_id, are_email, employee_name, employee_contact_no, tenant_id, tenant_name, tenant_contract_no, building_name, tenant_resident_exp_date`. Duplicates (same `are_user_id` + `tenant_id` + `tenant_contract_no`) are sent once; rows without `are_user_id` are skipped.
- `TenantController::areReminderNotification()` returns early unless it is 09:00.

- [ ] **Step 1: Write the failing tests** — create `tests/Feature/AreResidenceReminderTest.php`:

```php
<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\StaffMessages;
use Modules\BackOffice\Services\SystemMessenger;

class AreResidenceReminderTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        SystemMessenger::$smsSender = function () { return true; };
        MessageTemplate::where('key', 'are_residence_reminder')->update(['is_active' => true]);
    }

    protected function tearDown()
    {
        Carbon::setTestNow();
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function row($userId, $tenantId, $email = 'are@example.com')
    {
        return (object) ['are_user_id' => $userId, 'are_email' => $email, 'employee_name' => 'Munira', 'employee_contact_no' => '99111222',
            'tenant_id' => $tenantId, 'tenant_name' => 'Ali', 'tenant_contract_no' => 'TAG1', 'building_name' => 'Rex', 'tenant_resident_exp_date' => '2026-09-15'];
    }

    public function test_one_email_per_are_and_tenant_even_when_the_join_repeats_rows()
    {
        $sent = StaffMessages::areResidenceReminder([$this->row(5, 9), $this->row(5, 9), $this->row(5, 9), $this->row(6, 9, 'b@example.com'), $this->row(null, 9)]);
        $this->assertSame(2, $sent);
        Mail::assertSent(TemplatedMail::class, 2);
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('are@example.com') && $m->subject === 'Residence ID expired - Ali (TAG1)'
                && strpos($m->bodyHtml, '15/09/2026') !== false;
        });
    }

    public function test_switched_off_by_default_sends_nothing()
    {
        MessageTemplate::where('key', 'are_residence_reminder')->update(['is_active' => false]);
        StaffMessages::areResidenceReminder([$this->row(5, 9)]);
        Mail::assertNothingSent();
    }

    public function test_cron_does_nothing_outside_09_00()
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 30, 10, 0, 0));
        $before = MessageLog::where('template_key', 'are_residence_reminder')->count();
        (new \Modules\Sales\Http\Controllers\TenantController)->areReminderNotification();
        $this->assertSame($before, MessageLog::where('template_key', 'are_residence_reminder')->count());
        Mail::assertNothingSent();
    }
}
```

- [ ] **Step 2: Run and see them fail**

Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Feature/AreResidenceReminderTest.php`
Expected: FAIL — `areResidenceReminder` undefined; the guard test may send/fail on the old code.

- [ ] **Step 3: Implement the service** — add to `StaffMessages`:

```php
    /**
     * are_residence_reminder: one message per ARE per expired tenant/contract.
     * The source query joins ARE assignments, which repeats rows; duplicates are sent once.
     */
    public static function areResidenceReminder($rows)
    {
        $done = [];
        $sent = 0;
        foreach ($rows as $r) {
            if (empty($r->are_user_id)) {
                continue;
            }
            $k = $r->are_user_id . '|' . $r->tenant_id . '|' . $r->tenant_contract_no;
            if (isset($done[$k])) {
                continue;
            }
            $done[$k] = true;
            $to = new MessageRecipient($r->employee_name, $r->are_email, $r->employee_contact_no);
            SystemMessenger::send('are_residence_reminder', $to, [
                'employee_name'    => (string) $r->employee_name,
                'tenant_name'      => (string) $r->tenant_name,
                'contract_no'      => (string) $r->tenant_contract_no,
                'building'         => (string) $r->building_name,
                'residence_expiry' => $r->tenant_resident_exp_date ? date('d/m/Y', strtotime($r->tenant_resident_exp_date)) : '',
            ], ['related' => ['tenant', $r->tenant_id]]);
            $sent++;
        }
        return $sent;
    }
```

- [ ] **Step 4: Rewire the controller** — replace the body of `TenantController::areReminderNotification()` with:

```php
    public function areReminderNotification()
    {
      // Called from the every-minute notification command: run once a day at 09:00.
      if (Carbon::now()->hour != 9 || Carbon::now()->minute != 0) {
          return;
      }
      $today = date('Y-m-d');

      $rows = Tenant::active()->leftJoin('tenant_contracts', 'tenant.id', '=', 'tenant_contracts.tenant_id')
      ->leftJoin('buildings', 'tenant_contracts.building_id', '=', 'buildings.id')
      ->leftJoin('preferred_buildings', 'buildings.id', '=', 'preferred_buildings.building_id')
      ->leftJoin('are_buildings', 'preferred_buildings.are_building_id', '=', 'are_buildings.id')
      ->leftJoin('users', 'are_buildings.user_id', '=', 'users.id')
      ->leftJoin('employees', 'users.user_type_id', '=', 'employees.id')
      ->where('preferred_buildings.assign_to','=',null)
      ->where('tenant.tenant_type_id',1)->where('tenant_contracts.tenant_contract_status',1)->whereDate('tenant.tenant_resident_exp_date','<',$today)
      ->whereNotNull('users.id')
      ->get(['users.id as are_user_id', 'users.email as are_email', 'employees.employee_name', 'employees.employee_contact_no',
             'tenant.id as tenant_id', 'tenant.tenant_name', 'tenant.tenant_resident_exp_date',
             'tenant_contracts.tenant_contract_no', 'buildings.building_name']);

      // System template are_residence_reminder (switched off by default)
      \Modules\BackOffice\Services\StaffMessages::areResidenceReminder($rows);
    }
```
Remove the now-unused `use` of `AreReminderEmail` at the top of `TenantController` if present.

- [ ] **Step 5: Re-enable the cron call** — in `NotificationCommand::handle`, replace

```php
        //$reminder =  new Tenant;
        //$reminder->areReminderNotification();
        //$this->info('Reminder');
```
with
```php
        try {
            $reminder =  new Tenant;
            $reminder->areReminderNotification(); // 09:00 only; template are_residence_reminder decides if anything is sent
            $this->info('ARE Residence Reminder');
        } catch (\Exception $e) {
            Log::error('NotificationCommand: areReminderNotification failed - ' . $e->getMessage());
            $this->error('ARE Residence Reminder FAILED: ' . $e->getMessage());
        }
```

- [ ] **Step 6: Delete the old mail class** — find it and check it is unused:

```bash
grep -rn "AreReminderEmail" app Modules resources routes --include=*.php
```
If the only hits are its own class file and its view reference, `git rm` the class file and the view it renders (the `->view(`/`->markdown(` name inside it, under `Modules/*/Resources/views/`). Re-run the grep: expected no hits.

- [ ] **Step 7: Run tests + lint**

Run: `php -l Modules/Sales/Http/Controllers/TenantController.php` and `php -l app/Console/Commands/NotificationCommand.php` → no syntax errors
Run: `php -d memory_limit=1G vendor/bin/phpunit tests/Feature/AreResidenceReminderTest.php`
Expected: all PASS.

- [ ] **Step 8: Commit**

```bash
git add -A Modules/BackOffice/Services/StaffMessages.php Modules/Sales/Http/Controllers/TenantController.php app/Console/Commands/NotificationCommand.php tests/Feature/AreResidenceReminderTest.php
git commit -m "feat(system-templates): ARE residence reminder via template, once a day at 09:00, cron call re-enabled"
```

---

### Task 7: Full suite, sample render, docs

**Files:**
- Modify: `docs/superpowers/specs/2026-09-29-system-email-templates-design.md` (C4 section: note 09:00 guard, link hidden in the log)

- [ ] **Step 1: Full test run**

Run: `php -d memory_limit=1G vendor/bin/phpunit`
Expected: only the 6 known baseline failures (bounce-reason menu route); every new test passes.

- [ ] **Step 2: Render one real email per C4 key** (scratch script, nothing sent) — using `MessageTemplateRenderer::render(MessageTemplate::where('key', $k)->first(), $values)` for each of the four keys with a real user, and check by eye: link text present, username shown, "Al Habib Team" sign-off.

- [ ] **Step 3: Spec note** — in the C4 section of the spec, under the table, add:

```markdown
Implemented notes: the ARE reminder runs only at 09:00 (it is called from the
every-minute command) and sends one message per ARE per tenant/contract;
set-password and reset links are replaced by `[link hidden]` in the sent log.
```

- [ ] **Step 4: Commit**

```bash
git add docs/superpowers/specs/2026-09-29-system-email-templates-design.md
git commit -m "docs(system-templates): C4 implementation notes"
```
