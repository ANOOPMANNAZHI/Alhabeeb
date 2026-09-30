# Message Templates (Project A) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** An editable store of email + SMS templates with placeholders, managed under Mail → Templates, ready for bulk sending (project B) and system emails (project C).

**Architecture:** One table (`message_templates`) and model; two pure classes — `MessagePlaceholders` (which placeholders exist per audience / system key, with sample values) and `MessageTemplateRenderer` (fill placeholders safely, count SMS parts, clean HTML); a resource controller with a JSON preview endpoint; Blade list + editor using the TinyMCE already used by Compose.

**Tech Stack:** Laravel 5.x modular app (nwidart), PostgreSQL, Blade + jQuery + TinyMCE (`public/js/tinymce.js`), spatie/permission, PHPUnit 7.

**Spec:** `docs/superpowers/specs/2026-09-29-message-templates-design.md` (Project A section)

## Global Constraints

- Production PHP < 7.4: no arrow functions, no `?->`, no `match`, no typed properties, no `??=`.
- Local PHP for tests: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe`.
- Template content is never executed as Blade/PHP; placeholders are replaced as text.
- Placeholder syntax `{name}`: lowercase letters, digits, underscore.
- SMS parts: 160 chars (7-bit) per single part, 153 per part when multi-part; 70 / 67 when the text contains any non-ASCII character (Arabic etc.).
- `kind`: `system` | `bulk`; `audience`: `tenant` | `landlord` | `staff` | `any`. System templates cannot be deleted and their key/audience cannot change; only bulk templates are created from the screen.
- Permissions `view_message_templates`, `manage_message_templates`; menu row under the existing Mail parent (parent of the `massMail.create` row); granted to `super_admin`, `backoffice_manager`.
- Buttons / controls get own styles (the theme's `.btn` sizing clips labels on these pages): 40px height, visible focus ring.

## Review Focus

1. **Placeholder value containing HTML or `{another}`** (e.g. tenant name `A & B <x>` or `{rent}`) → shown literally, escaped in the email body, never re-expanded. Test in Task 2.
2. **Template with only SMS, or only email** → saves; one with neither → validation error. Test in Task 4.
3. **Editing / deleting a system template** → edit allowed (content, active), key and audience unchanged; delete refused. Test in Task 4.
4. **User with view permission only** → can list and preview, gets 403 on create / edit / delete. Test in Task 4.
5. **Pasted email body with `<script>` or `onclick`** → stripped on save. Test in Task 2 (cleaner) and Task 4 (store).

---

## File Structure

| File | Responsibility |
|---|---|
| `database/migrations/2026_09_29_000000_create_message_templates_table.php` (create) | table |
| `database/migrations/2026_09_29_000001_add_message_templates_menu.php` (create) | menu row + 2 permissions |
| `Modules/BackOffice/Services/MessagePlaceholders.php` (create) | placeholder registry + samples |
| `Modules/BackOffice/Services/MessageTemplateRenderer.php` (create) | fill, SMS parts, HTML clean |
| `tests/Unit/MessagePlaceholdersTest.php`, `tests/Unit/MessageTemplateRendererTest.php` (create) | unit tests |
| `Modules/BackOffice/Entities/MessageTemplate.php` (create) | model |
| `Modules/BackOffice/Http/Controllers/MessageTemplateController.php` (create) | CRUD + preview JSON |
| `Modules/BackOffice/Routes/web.php`, `Routes/breadcrumbs.php` (modify) | routes, breadcrumbs |
| `Modules/BackOffice/Resources/views/MessageTemplate/index.blade.php`, `form.blade.php`, `_preview_modal.blade.php` (create) | screens |

---

### Task 1: Migrations

**Files:** Create the two migrations listed above.

**Interfaces:**
- Produces: table `message_templates` (columns per spec); permissions `view_message_templates`, `manage_message_templates`; menu route `message-templates.index`.

- [ ] **Step 1: Table migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Editable email + SMS templates (system templates keyed for code, bulk templates for sending). */
class CreateMessageTemplatesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('message_templates')) {
            return;
        }
        Schema::create('message_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 100)->nullable()->unique();
            $table->string('name', 150);
            $table->string('kind', 10)->default('bulk');
            $table->string('audience', 20)->default('any');
            $table->string('email_subject', 255)->nullable();
            $table->text('email_body')->nullable();
            $table->text('sms_body')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_templates');
    }
}
```

- [ ] **Step 2: Menu + permissions migration**

```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Mail -> Templates. The Mail parent is found through the Compose row
 * (massMail.create) because menu ids differ between databases.
 */
class AddMessageTemplatesMenu extends Migration
{
    const ROUTE = 'message-templates.index';
    const PERMS = ['view_message_templates', 'manage_message_templates'];
    const ROLES = ['super_admin', 'backoffice_manager'];

    public function up()
    {
        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }
        $compose = DB::table('menu')->where('route_name', 'massMail.create')->first();
        $parentId = $compose ? $compose->parent_menu : 0;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Templates',
            'menu_icon'   => 'fa-file-text-o',
            'route_name'  => self::ROUTE,
            'url_key'     => 'message-templates',
            'parent_menu' => $parentId,
            'menutype'    => 2,
            'menu_order'  => DB::table('menu')->where('parent_menu', $parentId)->count() + 1,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $roleIds = DB::table('roles')->whereIn('name', self::ROLES)->pluck('id');
        foreach (self::PERMS as $name) {
            if (DB::table('permissions')->where('name', $name)->exists()) {
                continue;
            }
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
        app('cache')->forget('spatie.permission.cache');
    }
}
```

- [ ] **Step 3: Run both locally and verify**

Run each with `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=database/migrations/<file>`.
Expected: `Migrated:` twice. Then tinker: `Schema::hasTable('message_templates')` → true; the menu row's `parent_menu` equals the `massMail.create` row's `parent_menu`; `super_admin` has both permissions.

- [ ] **Step 4: Commit** — `feat(message-templates): table, Mail -> Templates menu and permissions`

---

### Task 2: `MessageTemplateRenderer` (pure)

**Files:** Create `Modules/BackOffice/Services/MessageTemplateRenderer.php`, `tests/Unit/MessageTemplateRendererTest.php`.

**Interfaces:**
- Produces:
  - `MessageTemplateRenderer::fill($text, array $values, $escapeHtml, array &$unknown): string`
  - `MessageTemplateRenderer::render($template, array $values): array` — `$template` object/array with `email_subject`, `email_body`, `sms_body`; returns `['subject' => string|null, 'html' => string|null, 'sms' => string|null, 'unknown' => string[] (sorted, unique)]`. Subject: raw text with newlines collapsed to spaces. Body: values HTML-escaped. SMS: raw.
  - `MessageTemplateRenderer::smsParts($text): array` — `['chars' => int, 'unicode' => bool, 'parts' => int]` (0 parts for empty).
  - `MessageTemplateRenderer::cleanHtml($html): string`
  - `MessageTemplateRenderer::placeholdersIn($text): string[]`

- [ ] **Step 1: Failing tests**

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\MessageTemplateRenderer as R;

class MessageTemplateRendererTest extends TestCase
{
    public function test_fills_placeholders_and_reports_unknown()
    {
        $t = (object) ['email_subject' => "Rent due for {unit}\n", 'email_body' => '<p>Dear {tenant_name}, pay {amount}.</p>', 'sms_body' => 'Dear {tenant_name}: {nope}'];
        $r = R::render($t, ['tenant_name' => 'Ali', 'unit' => 'S 03']);
        $this->assertSame('Rent due for S 03', $r['subject']);
        $this->assertSame('<p>Dear Ali, pay {amount}.</p>', $r['html']);
        $this->assertSame('Dear Ali: {nope}', $r['sms']);
        $this->assertSame(['amount', 'nope'], $r['unknown']);
    }

    public function test_values_are_escaped_in_html_and_never_re_expanded()
    {
        $t = (object) ['email_subject' => '{tenant_name}', 'email_body' => '<b>{tenant_name}</b>', 'sms_body' => '{tenant_name}'];
        $r = R::render($t, ['tenant_name' => 'A & B <x> {rent}', 'rent' => '999']);
        $this->assertSame('<b>A &amp; B &lt;x&gt; {rent}</b>', $r['html']);
        $this->assertSame('A & B <x> {rent}', $r['sms']);
        $this->assertSame('A & B <x> {rent}', $r['subject']);
        $this->assertSame([], $r['unknown']);
    }

    public function test_missing_channels_render_as_null()
    {
        $r = R::render((object) ['email_subject' => null, 'email_body' => null, 'sms_body' => 'Hi'], []);
        $this->assertNull($r['subject']);
        $this->assertNull($r['html']);
        $this->assertSame('Hi', $r['sms']);
    }

    public function test_placeholder_names_are_case_insensitive()
    {
        $unknown = [];
        $this->assertSame('Hi Ali', R::fill('Hi {Tenant_Name}', ['tenant_name' => 'Ali'], false, $unknown));
    }

    public function test_sms_parts_gsm_and_unicode()
    {
        $this->assertSame(['chars' => 0, 'unicode' => false, 'parts' => 0], R::smsParts(''));
        $this->assertSame(1, R::smsParts(str_repeat('a', 160))['parts']);
        $this->assertSame(2, R::smsParts(str_repeat('a', 161))['parts']);
        $this->assertSame(3, R::smsParts(str_repeat('a', 307))['parts']);
        $ar = R::smsParts(str_repeat('ب', 70));
        $this->assertSame(['chars' => 70, 'unicode' => true, 'parts' => 1], $ar);
        $this->assertSame(2, R::smsParts(str_repeat('ب', 71))['parts']);
    }

    public function test_clean_html_strips_scripts_and_handlers()
    {
        $dirty = '<p onclick="x()">Hi<script>alert(1)</script></p><a href="javascript:bad()">l</a><iframe src="x"></iframe><style>p{}</style><img src="a.png" onerror="y()">';
        $clean = R::cleanHtml($dirty);
        $this->assertSame('<p>Hi</p><a href="#">l</a><img src="a.png">', $clean);
    }

    public function test_placeholders_in_text()
    {
        $this->assertSame(['rent', 'tenant_name'], R::placeholdersIn('{tenant_name} owes {rent}; {tenant_name}'));
    }
}
```

- [ ] **Step 2: Run to watch it fail** — `…/php.exe vendor/bin/phpunit tests/Unit/MessageTemplateRendererTest.php` → class not found.

- [ ] **Step 3: Implement**

```php
<?php

namespace Modules\BackOffice\Services;

/**
 * Renders message templates. Placeholders ({name}) are replaced as text:
 * escaped in the email body, raw in the subject and SMS; template content is
 * never executed. Values are not re-expanded, so a value containing "{x}"
 * stays literal. Pure: no DB. PHP < 7.4: no arrow functions.
 */
class MessageTemplateRenderer
{
    const PLACEHOLDER = '/\{([A-Za-z0-9_]+)\}/';

    public static function fill($text, array $values, $escapeHtml, array &$unknown)
    {
        $lower = [];
        foreach ($values as $k => $v) {
            $lower[strtolower($k)] = $v;
        }
        return preg_replace_callback(self::PLACEHOLDER, function ($m) use ($lower, $escapeHtml, &$unknown) {
            $key = strtolower($m[1]);
            if (!array_key_exists($key, $lower)) {
                $unknown[$key] = true;
                return $m[0];
            }
            $value = (string) $lower[$key];
            return $escapeHtml ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
        }, (string) $text);
    }

    public static function render($template, array $values)
    {
        $t = (object) $template;
        $unknown = [];
        $subject = isset($t->email_subject) && $t->email_subject !== '' ? $t->email_subject : null;
        $body    = isset($t->email_body) && $t->email_body !== '' ? $t->email_body : null;
        $sms     = isset($t->sms_body) && $t->sms_body !== '' ? $t->sms_body : null;

        $out = [
            'subject' => $subject === null ? null : trim(preg_replace('/\s*[\r\n]+\s*/', ' ', self::fill($subject, $values, false, $unknown))),
            'html'    => $body === null ? null : self::fill($body, $values, true, $unknown),
            'sms'     => $sms === null ? null : self::fill($sms, $values, false, $unknown),
        ];
        $keys = array_keys($unknown);
        sort($keys);
        $out['unknown'] = $keys;
        return $out;
    }

    public static function placeholdersIn($text)
    {
        preg_match_all(self::PLACEHOLDER, (string) $text, $m);
        $keys = array_values(array_unique(array_map('strtolower', $m[1])));
        sort($keys);
        return $keys;
    }

    public static function smsParts($text)
    {
        $text = (string) $text;
        $chars = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        $unicode = (bool) preg_match('/[^\x00-\x7F]/', $text);
        if ($chars === 0) {
            return ['chars' => 0, 'unicode' => $unicode, 'parts' => 0];
        }
        $single = $unicode ? 70 : 160;
        $multi  = $unicode ? 67 : 153;
        $parts = $chars <= $single ? 1 : (int) ceil($chars / $multi);
        return ['chars' => $chars, 'unicode' => $unicode, 'parts' => $parts];
    }

    /** Removes executable content from editor HTML. */
    public static function cleanHtml($html)
    {
        $html = (string) $html;
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1\s*>#is', '', $html);
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*/?>#is', '', $html);
        $html = preg_replace('#\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
        $html = preg_replace('#(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2#i', '$1="#"', $html);
        return $html;
    }
}
```

- [ ] **Step 4: Run to pass** — same command → `OK (7 tests, …)`. If the `cleanHtml` exact-string test fails only on attribute quoting, fix the regex, not the test.

- [ ] **Step 5: Commit** — `feat(message-templates): renderer (placeholders, SMS parts, HTML cleaning)`

---

### Task 3: `MessagePlaceholders` (pure)

**Files:** Create `Modules/BackOffice/Services/MessagePlaceholders.php`, `tests/Unit/MessagePlaceholdersTest.php`.

**Interfaces:**
- Produces:
  - `MessagePlaceholders::AUDIENCES` — `['tenant' => 'Tenants', 'landlord' => 'Landlords', 'staff' => 'Staff', 'any' => 'Anyone']`
  - `MessagePlaceholders::forTemplate($audience, $key = null): array` — `name => ['label' => string, 'sample' => string]`, common + audience (+ system key extras; `any` gets common only).
  - `MessagePlaceholders::samples($audience, $key = null): array` — `name => sample`.
  - `MessagePlaceholders::registerSystem($key, array $extra)` — used by project C.

- [ ] **Step 1: Failing tests**

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\MessagePlaceholders as P;

class MessagePlaceholdersTest extends TestCase
{
    public function test_tenant_gets_common_and_tenant_placeholders()
    {
        $p = P::forTemplate('tenant');
        foreach (['today', 'company_name', 'tenant_name', 'contract_no', 'building', 'unit', 'location', 'rent', 'contract_start', 'contract_end', 'mobile'] as $k) {
            $this->assertArrayHasKey($k, $p);
        }
        $this->assertArrayNotHasKey('landlord_name', $p);
    }

    public function test_any_gets_common_only()
    {
        $this->assertSame(['company_name', 'today'], self::sorted(array_keys(P::forTemplate('any'))));
    }

    public function test_samples_are_strings_for_every_placeholder()
    {
        foreach (array_keys(P::AUDIENCES) as $a) {
            foreach (P::samples($a) as $k => $v) {
                $this->assertInternalType('string', $v, "$a.$k");
                $this->assertNotSame('', $v, "$a.$k");
            }
        }
    }

    public function test_system_key_adds_its_placeholders()
    {
        P::registerSystem('test_bounce', ['cheque_no' => ['label' => 'Cheque no.', 'sample' => '82079797']]);
        $this->assertSame('82079797', P::samples('staff', 'test_bounce')['cheque_no']);
        $this->assertArrayNotHasKey('cheque_no', P::samples('staff'));
    }

    public function test_unknown_audience_falls_back_to_common()
    {
        $this->assertSame(self::sorted(array_keys(P::forTemplate('any'))), self::sorted(array_keys(P::forTemplate('nope'))));
    }

    private static function sorted(array $a) { sort($a); return $a; }
}
```

- [ ] **Step 2: Run to watch it fail** → class not found.

- [ ] **Step 3: Implement**

```php
<?php

namespace Modules\BackOffice\Services;

/**
 * Which placeholders a template may use, by audience (and, for system
 * templates, by key), with sample values for preview. Pure: no DB.
 */
class MessagePlaceholders
{
    const AUDIENCES = ['tenant' => 'Tenants', 'landlord' => 'Landlords', 'staff' => 'Staff', 'any' => 'Anyone'];

    private static $common = [
        'today'        => ['label' => "Today's date", 'sample' => '29/09/2026'],
        'company_name' => ['label' => 'Company name', 'sample' => 'Al Habib & Co. L.L.C'],
    ];

    private static $byAudience = [
        'tenant' => [
            'tenant_name'    => ['label' => 'Tenant name', 'sample' => 'Mohammed Al Balushi'],
            'contract_no'    => ['label' => 'Contract no.', 'sample' => 'TAG2601977'],
            'building'       => ['label' => 'Building', 'sample' => 'Al Qandeel-2 Bldg'],
            'unit'           => ['label' => 'Unit', 'sample' => '84'],
            'location'       => ['label' => 'Location', 'sample' => 'Al Khuwair'],
            'rent'           => ['label' => 'Monthly rent', 'sample' => '250.000'],
            'contract_start' => ['label' => 'Contract start', 'sample' => '15/09/2026'],
            'contract_end'   => ['label' => 'Contract end', 'sample' => '30/09/2027'],
            'mobile'         => ['label' => 'Tenant mobile', 'sample' => '99123456'],
        ],
        'landlord' => [
            'landlord_name' => ['label' => 'Landlord name', 'sample' => 'Ali Mallalah Habib'],
            'building'      => ['label' => 'Building', 'sample' => 'Rex Bldg'],
            'location'      => ['label' => 'Location', 'sample' => 'Ruwi'],
        ],
        'staff' => [
            'employee_name' => ['label' => 'Employee name', 'sample' => 'Anoop'],
            'role'          => ['label' => 'Role', 'sample' => 'Back Office Manager'],
        ],
        'any' => [],
    ];

    /** @var array system template key => extra placeholders (registered by project C) */
    private static $system = [];

    public static function registerSystem($key, array $extra)
    {
        self::$system[$key] = $extra;
    }

    public static function forTemplate($audience, $key = null)
    {
        $audience = isset(self::$byAudience[$audience]) ? $audience : 'any';
        $out = self::$common + self::$byAudience[$audience];
        if ($key !== null && isset(self::$system[$key])) {
            $out = $out + self::$system[$key];
        }
        return $out;
    }

    public static function samples($audience, $key = null)
    {
        $out = [];
        foreach (self::forTemplate($audience, $key) as $name => $p) {
            $out[$name] = (string) $p['sample'];
        }
        return $out;
    }
}
```

- [ ] **Step 4: Run to pass** → `OK (5 tests, …)`.
- [ ] **Step 5: Commit** — `feat(message-templates): placeholder registry with sample values`

---

### Task 4: Model, controller, routes

**Files:**
- Create `Modules/BackOffice/Entities/MessageTemplate.php`, `Modules/BackOffice/Http/Controllers/MessageTemplateController.php`
- Modify `Modules/BackOffice/Routes/web.php` (inside the `Route::middleware('auth')->group` block, next to `Route::resource('massMail', …)`), `Modules/BackOffice/Routes/breadcrumbs.php`

**Interfaces:**
- Consumes: Task 1 table/permissions, Task 2 renderer, Task 3 placeholders.
- Produces routes: `message-templates.index|create|store|edit|update|destroy` (resource, no show) and `message-templates.preview` (POST, JSON). View variables for Task 5: index → `$templates` (paginator), `$filters`; form → `$template` (model or null), `$placeholders` (for its audience/key), `$audiences`, `$canManage`.

- [ ] **Step 1: Write the in-process check (scratch, not committed) — watch it fail**

`<scratchpad>/mt_check.php` — boots Laravel, `DB::beginTransaction()`, logs in as user 1 (has `manage_message_templates` via super_admin), then:
- POST store with only `sms_body` → saved, kind `bulk`, `key` null.
- POST store with neither channel → `ValidationException` naming `channels`.
- POST store with `email_body` containing `<script>` / `onclick` → saved body has neither.
- Create a system row directly (`key` = `test_sys`, kind `system`, audience `staff`); PUT update changing name/content/active and also sending `key=hack`, `audience=tenant` → content changed, key/audience unchanged.
- DELETE system row → refused (redirect with error, row still there); DELETE bulk row → soft-deleted.
- POST preview with `{tenant_name}` and audience tenant → JSON `html` contains `Mohammed Al Balushi`, `unknown` lists any bad placeholder, `sms.parts` ≥ 1.
- Log in as a user with only `view_message_templates` (create a throwaway role in the transaction) → index 200, preview 200, create/store/edit/update/destroy abort 403.
- `DB::rollBack()` in `finally`.

Run it before the controller exists → errors (class/route missing).

- [ ] **Step 2: Model**

```php
<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageTemplate extends Model
{
    use SoftDeletes;

    const KIND_SYSTEM = 'system';
    const KIND_BULK = 'bulk';

    protected $table = 'message_templates';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function isSystem()
    {
        return $this->kind === self::KIND_SYSTEM;
    }

    /** Active system template by key, or null (callers fall back to their built-in wording). */
    public static function system($key)
    {
        return static::where('key', $key)->where('kind', self::KIND_SYSTEM)->where('is_active', true)->first();
    }
}
```

- [ ] **Step 3: Controller**

```php
<?php

namespace Modules\BackOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\MessagePlaceholders;
use Modules\BackOffice\Services\MessageTemplateRenderer as Renderer;

/** Mail -> Templates: editable email + SMS templates (project A of the messaging roadmap). */
class MessageTemplateController extends Controller
{
    const PER_PAGE = 25;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_message_templates|manage_message_templates')->only(['index', 'preview']);
        $this->middleware('permission:manage_message_templates')->except(['index', 'preview']);
    }

    public function index(Request $request)
    {
        $filters = [
            'q'        => trim((string) $request->input('q')),
            'kind'     => in_array($request->input('kind'), ['system', 'bulk'], true) ? $request->input('kind') : '',
            'audience' => array_key_exists((string) $request->input('audience'), MessagePlaceholders::AUDIENCES) ? $request->input('audience') : '',
        ];
        $q = MessageTemplate::query();
        if ($filters['q'] !== '') {
            $like = '%' . $filters['q'] . '%';
            $q->where(function ($w) use ($like) { $w->where('name', 'ilike', $like)->orWhere('email_subject', 'ilike', $like); });
        }
        if ($filters['kind'] !== '') { $q->where('kind', $filters['kind']); }
        if ($filters['audience'] !== '') { $q->where('audience', $filters['audience']); }
        $templates = $q->orderBy('kind', 'desc')->orderBy('name')->paginate(self::PER_PAGE)->appends($filters);
        $canManage = \Auth::user()->can('manage_message_templates');
        $audiences = MessagePlaceholders::AUDIENCES;
        return view('backoffice::MessageTemplate.index', compact('templates', 'filters', 'canManage', 'audiences'));
    }

    public function create()
    {
        return $this->form(null);
    }

    public function edit(MessageTemplate $messageTemplate)
    {
        return $this->form($messageTemplate);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        $data['kind'] = MessageTemplate::KIND_BULK;
        $data['key'] = null;
        $data['created_by'] = \Auth::user()->id;
        MessageTemplate::create($data);
        return redirect()->route('message-templates.index')->with('success', 'Template "' . $data['name'] . '" created.');
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        $data = $this->validated($request, $messageTemplate);
        if ($messageTemplate->isSystem()) {
            unset($data['audience']); // fixed for system templates; key is never taken from the request
        }
        $data['updated_by'] = \Auth::user()->id;
        $messageTemplate->update($data);
        return redirect()->route('message-templates.index')->with('success', 'Template "' . $messageTemplate->name . '" saved.');
    }

    public function destroy(MessageTemplate $messageTemplate)
    {
        if ($messageTemplate->isSystem()) {
            return redirect()->route('message-templates.index')->with('error', 'System templates cannot be deleted; switch them off instead.');
        }
        $messageTemplate->delete();
        return redirect()->route('message-templates.index')->with('success', 'Template "' . $messageTemplate->name . '" deleted.');
    }

    /** JSON preview with sample values: from a saved template (id) or the editor's current content. */
    public function preview(Request $request)
    {
        $template = $request->filled('id') ? MessageTemplate::find((int) $request->input('id')) : null;
        $source = $template ?: (object) [
            'email_subject' => (string) $request->input('email_subject'),
            'email_body'    => Renderer::cleanHtml((string) $request->input('email_body')),
            'sms_body'      => (string) $request->input('sms_body'),
            'audience'      => (string) $request->input('audience', 'any'),
            'key'           => $request->input('key'),
        ];
        $samples = MessagePlaceholders::samples($source->audience, $source->key);
        $out = Renderer::render($source, $samples);
        $out['sms_parts'] = Renderer::smsParts($out['sms']);
        return response()->json($out);
    }

    private function form($template)
    {
        $audience = $template ? $template->audience : 'tenant';
        $key = $template ? $template->key : null;
        $placeholders = MessagePlaceholders::forTemplate($audience, $key);
        $audiences = MessagePlaceholders::AUDIENCES;
        $allPlaceholders = [];
        foreach (array_keys($audiences) as $a) {
            $allPlaceholders[$a] = MessagePlaceholders::forTemplate($a, $key);
        }
        return view('backoffice::MessageTemplate.form', compact('template', 'placeholders', 'audiences', 'allPlaceholders'));
    }

    private function validated(Request $request, $template)
    {
        $this->validate($request, [
            'name'          => 'required|max:150',
            'audience'      => 'required|in:' . implode(',', array_keys(MessagePlaceholders::AUDIENCES)),
            'email_subject' => 'nullable|max:255|required_with:email_body',
            'email_body'    => 'nullable|required_with:email_subject',
            'sms_body'      => 'nullable|max:1000',
        ]);
        $body = trim(strip_tags((string) $request->input('email_body'))) === '' && stripos((string) $request->input('email_body'), '<img') === false
            ? null : Renderer::cleanHtml($request->input('email_body'));
        $data = [
            'name'          => trim($request->input('name')),
            'audience'      => $request->input('audience'),
            'email_subject' => $request->filled('email_subject') ? trim($request->input('email_subject')) : null,
            'email_body'    => $body,
            'sms_body'      => $request->filled('sms_body') ? $request->input('sms_body') : null,
            'is_active'     => $request->boolean('is_active'),
        ];
        if ($data['email_subject'] === null && $data['sms_body'] === null) {
            throw ValidationException::withMessages(['channels' => 'Fill in the email (subject and body) or the SMS text, or both.']);
        }
        return $data;
    }
}
```

If `$request->boolean()` does not exist in this Laravel version, use `(bool) $request->input('is_active')`.

- [ ] **Step 4: Routes + breadcrumbs**

In `web.php`, directly above `Route::resource('massMail', 'MassMailController');`:

```php
	Route::post('message-templates/preview', 'MessageTemplateController@preview')->name('message-templates.preview');
	Route::resource('message-templates', 'MessageTemplateController')->except(['show'])->parameters(['message-templates' => 'messageTemplate']);
```

In `breadcrumbs.php` next to the `massMail.*` entries:

```php
Breadcrumbs::for('message-templates.index', function ($trail) {
    $trail->push('Home', route('home'));
    $trail->push('Templates', route('message-templates.index'));
});
Breadcrumbs::for('message-templates.create', function ($trail) {
    $trail->parent('message-templates.index');
    $trail->push('New Template', route('message-templates.create'));
});
Breadcrumbs::for('message-templates.edit', function ($trail, $template) {
    $trail->parent('message-templates.index');
    $trail->push($template->name, route('message-templates.edit', $template->id));
});
```

Check how `massMail.index` starts its trail (`parent('home')` vs push) and copy that.

- [ ] **Step 5: Temporary minimal views so the check can render** — create `index.blade.php` and `form.blade.php` containing just `@extends('layouts.plms-app') @section('content') … @endsection` with the template names / fields; Task 5 replaces them.

- [ ] **Step 6: Run the check** → all PASS; `php -l` on controller/model; unit suite green.

- [ ] **Step 7: Commit** — `feat(message-templates): model, controller, routes (CRUD + preview, system template protection)`

---

### Task 5: Screens

**Files:** Replace `index.blade.php`, `form.blade.php`; create `_preview_modal.blade.php` (all in `Modules/BackOffice/Resources/views/MessageTemplate/`).

**Interfaces:** Consumes Task 4 view variables and routes.

- [ ] **Step 1: Render check first (scratch)** — render index (with one system + one bulk template created in a rolled-back transaction), create form, edit form of the system template; assert: filter bar, kind/audience/channel badges, Preview action, Create button only when `$canManage`; editor has TinyMCE textarea `#mt-email-body`, placeholder dropdowns listing `{tenant_name}` for audience tenant, SMS counter element `#mt-sms-count`, audience select disabled for system template, key shown read-only. Watch it fail against the temporary views.

- [ ] **Step 2: `index.blade.php`**
  - Page bar + breadcrumbs `message-templates.index`; primary button **New template** (own style, green, 40px) when `$canManage`.
  - Filter bar (GET): search text, Kind (All / System / Bulk), Audience (All + `$audiences`).
  - Table: Name (+ key in small grey for system), Kind badge (System = indigo, Bulk = grey), Audience, Channels (Email / SMS pills), Active (green "Active" / grey "Off", with icon), Updated (d/m/Y), Actions: **Preview** (opens modal via `message-templates.preview` with `id`), **Edit** (if manage), **Delete** (if manage and bulk; confirm dialog `Delete template "<name>"?`).
  - Empty state: "No templates yet. Create one to reuse it when sending." + New template button.
  - Pagination links.

- [ ] **Step 3: `form.blade.php`**
  - Single column, max-width ~960px, sections: **Details** (name, audience select — disabled for system with hidden input, key read-only for system, Active switch), **Email** (subject input + placeholder dropdown that inserts at cursor; body TinyMCE `#mt-email-body` with toolbar incl. a "Insert placeholder" menu button listing the audience's placeholders), **SMS** (textarea `#mt-sms` + placeholder dropdown + counter `#mt-sms-count` showing "123 characters · 1 SMS" / "… · Arabic · 2 SMS", computed in JS with the same rules as `Renderer::smsParts`), **Actions** (Preview, Save, Cancel).
  - Changing audience (bulk only) rebuilds the placeholder dropdowns from `$allPlaceholders` (JSON in the page).
  - Validation errors shown next to the field; the `channels` error shown above Email/SMS sections.
  - TinyMCE loaded as on Compose: `<script src="{{ asset('public/js/tinymce.js') }}">`, `tinymce.init({selector:'#mt-email-body', menubar:false, statusbar:false, branding:false, plugins/toolbar kept minimal, setup: add menu button "Placeholder"})`; before submit call `tinymce.triggerSave()`.
  - Scripts go in `@section('scripts')` (the layout yields `scripts`, not `js`); CSS in `@section('css')`.

- [ ] **Step 4: `_preview_modal.blade.php`** — Bootstrap modal: tabs Email / SMS; Email shows subject line + rendered HTML in a bordered box; SMS shows text in a phone-bubble style + parts; a yellow notice listing `unknown` placeholders if any. Filled by JS from the preview JSON; used by both list and form (form posts current editor content).

- [ ] **Step 5: Run the render check** → PASS. Screenshot list, editor (tenant audience), preview modal open (render the modal HTML with a sample payload) with headless Chrome after rewriting asset URLs to `file:///C:/laragon/www/plms_master/`; check at 1366px and 390px widths.

- [ ] **Step 6: Commit** — `feat(message-templates): Templates list, editor with placeholders + SMS counter, preview`

---

### Task 6: Wrap-up

- [ ] Full unit suite green; re-run the Task 4 check and Task 5 render check.
- [ ] Final whole-branch review (fresh reviewer), fix Critical/Important per executing-plans.
- [ ] Hand over for merge / upload: files list + deploy steps (2 migrations first, `route:clear`, `view:clear`, assign permissions to other roles if needed).
