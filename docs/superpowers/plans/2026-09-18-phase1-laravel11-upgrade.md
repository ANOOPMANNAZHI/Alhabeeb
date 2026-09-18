# Phase 1 — Laravel 5.7 → Laravel 11 / PHP 8.2+ Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Run the existing PLMS application unchanged in behaviour on Laravel 11 and PHP ≥ 8.2, with every dependency on a supported version, so it can be deployed to the new server and serve as the base for the mobile API (Phase 2) and template (Phase 3).

**Architecture:** In-place upgrade on branch `platform-upgrade`. Keep the Kernel-based bootstrap (`bootstrap/app.php`, `app/Http/Kernel.php`), `App\User`, `Modules/` layout and all route/view/permission names — Laravel 11 fully supports the pre-11 application structure, so we only touch what the framework and packages actually broke. Rescue the two hand-copied vendor packages (JasperPHP, and laravel-soap if needed) into `packages/` as Composer path repositories so `composer install` reproduces the server. Mechanical PHP 8 changes are done by Rector; unknown-symbol detection by Larastan; behaviour verification by the existing PHPUnit suite plus a browser smoke checklist derived from `docs/screen-inventory.md`.

**Tech Stack:** PHP 8.2+ (local 8.4.22), Laravel 11, nwidart/laravel-modules 11, spatie/laravel-permission 6, spatie/laravel-activitylog 4, barryvdh/laravel-dompdf 3, diglactic/laravel-breadcrumbs 9, kyslik/column-sortable 6, maatwebsite/excel 3.1, intervention/image 2.7, simple-qrcode 4, laravel/ui 4, laravel/helpers 1, Carbon 2, Rector 1 + rector-laravel, Larastan 2, PHPUnit 10, PostgreSQL.

**Spec:** `docs/superpowers/specs/2026-09-18-platform-modernisation.md`

## Global Constraints

- `composer.json` `"php": "^8.3"`, Laravel `^13.0` (Task 3 found 11.x blocked by security advisories; 13 is current and all packages support it).
- Carbon 3 (forced by Laravel 13): fix every `diffIn*()` call site to be int- and sign-safe; do not rely on Carbon 2 behaviour.
- Keep unchanged: `App\User`, `Modules\*` namespaces, every route name, view name, permission name, table name. The `menu` table and 814 views depend on them.
- Keep `password_resets` as the password-reset table name (config, not the Laravel 11 default `password_reset_tokens`).
- Eloquent JSON date format stays `Y-m-d H:i:s`.
- No schema rewrites. Only the additive migrations packages require (activitylog `event`, `batch_uuid`).
- Branch `platform-upgrade` is **not merged** into anything until Phases 1–3 are all done. Feature work continues on `master` and is merged *into* this branch.
- Every task ends with a commit on `platform-upgrade`. Commit messages end with `Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>`.
- Never copy anything into `server_upload_files/` during this plan.
- Run commands from `C:\laragon\www\plms_backup` with the Bash tool (Git Bash). PHP is `php` (8.4.22).

---

## File map

| Area | Files | Responsibility in this plan |
|---|---|---|
| Baseline | `docs/upgrade/baseline-route-names.txt`, `docs/upgrade/baseline-views.txt` | Frozen list of route names / views before the upgrade, diffed after |
| Jasper rescue | `packages/cossou/jasperphp/` (new, from `vendor/`), `resources/reports/jasper/*.jrxml` (new), `app/Helpers/helpers.php` (new, `jasper_template()`), 4 report controllers | Make the report engine reproducible from git |
| Composer | `composer.json`, `composer.lock`, `packages/artisaninweb/laravel-soap/` (only if fallback needed) | New dependency set |
| Framework skeleton | `app/Http/Kernel.php`, `app/Http/Middleware/{TrustProxies,CheckForMaintenanceMode,RedirectIfAuthenticated,Authenticate}.php`, `app/Exceptions/Handler.php`, `public/index.php`, `artisan`, `bootstrap/app.php`, `config/{app,auth,database,mail,session,cache,queue,logging,filesystems,hashing,view,broadcasting}.php`, `.env`, `.env.example` | Laravel 11 compatible bootstrap without changing structure |
| Modules | `config/modules.php`, `modules_statuses.json` (new), `Modules/*/Providers/*ServiceProvider.php` | nwidart v11 |
| Packages | `config/{permission,activitylog,breadcrumbs,columnsortable,dompdf}.php`, `database/migrations/2026_09_18_000001_upgrade_activity_log_for_v4.php` (new), `app/Http/Kernel.php` (spatie middleware namespace) | Package API/config migrations |
| Code sweep | `app/Concerns/LegacyDateSerialization.php` (new), 152 model files, `rector.php` (new), `phpstan.neon` (new), any file Rector/Larastan flag | PHP 8 / Laravel 11 source compatibility |
| Tests | `phpunit.xml`, `tests/` | PHPUnit 10 |
| Docs | `docs/upgrade/deploy-new-server.md` (new), memory | Deployment requirements for the new server |

---

### Task 1: Baseline snapshot

**Files:**
- Create: `docs/upgrade/baseline-route-names.txt`
- Create: `docs/upgrade/baseline-views.txt`
- Create: `docs/upgrade/baseline-composer.lock` (copy)

**Interfaces:**
- Produces: the two text files that Task 11 diffs against.

- [ ] **Step 1: Confirm branch**

Run: `git branch --show-current`
Expected: `platform-upgrade`

- [ ] **Step 2: Freeze the route-name list from source (the 5.7 app cannot boot on PHP 8.4, so this is a static extraction)**

```bash
grep -rhoE -e "->name\('[^']+'\)" routes Modules/*/Routes \
  | sed -E "s/->name\('([^']+)'\)/\1/" | sort -u > docs/upgrade/baseline-route-names.txt
wc -l docs/upgrade/baseline-route-names.txt
```
Expected: several hundred lines (≈ 900–1200).

- [ ] **Step 3: Freeze the view list**

```bash
find Modules resources/views -name "*.blade.php" | sort > docs/upgrade/baseline-views.txt
wc -l docs/upgrade/baseline-views.txt
```
Expected: `814 docs/upgrade/baseline-views.txt`

- [ ] **Step 4: Keep a copy of the old lock file**

```bash
cp composer.lock docs/upgrade/baseline-composer.lock
```

- [ ] **Step 5: Commit**

```bash
git add docs/upgrade/baseline-route-names.txt docs/upgrade/baseline-views.txt docs/upgrade/baseline-composer.lock docs/superpowers/specs/2026-09-18-platform-modernisation.md docs/superpowers/plans/2026-09-18-phase1-laravel11-upgrade.md
git commit -m "docs(upgrade): phase 1 spec, plan and pre-upgrade baseline

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Rescue JasperPHP and its templates out of `vendor/`

`cossou/jasperphp` and 152 report templates exist only in the git-ignored `vendor/` directory. `composer install` on the new server would delete the report engine. Move the package to a path repository and the templates into `resources/`.

**Files:**
- Create: `packages/cossou/jasperphp/` (copied from `vendor/cossou/jasperphp/`, minus `examples/`)
- Create: `resources/reports/jasper/` (the `examples/*.jrxml` and `*.jasper` files)
- Create: `app/Helpers/helpers.php`
- Modify: `composer.json` (`autoload.files`, `repositories`, `require`)
- Modify: `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`, `Modules/Maintenance/Http/Controllers/MaintenanceReportController.php`, `Modules/Masters/Http/Controllers/MasterReportController.php`, `Modules/Sales/Http/Controllers/SalesReportController.php`
- Modify: `.gitignore`

**Interfaces:**
- Produces: `jasper_template(string $file): string` — absolute path of a template under `resources/reports/jasper/`.
- Produces: package `cossou/jasperphp` version `1.0.0` resolvable from `packages/cossou/jasperphp`.

- [ ] **Step 1: Copy the package source (not the examples) and the JasperStarter runtime**

```bash
mkdir -p packages/cossou/jasperphp resources/reports/jasper
cp -r vendor/cossou/jasperphp/src packages/cossou/jasperphp/src
cp vendor/cossou/jasperphp/composer.json packages/cossou/jasperphp/composer.json
cp vendor/cossou/jasperphp/examples/*.jrxml resources/reports/jasper/
cp vendor/cossou/jasperphp/examples/*.jasper resources/reports/jasper/ 2>/dev/null || true
ls resources/reports/jasper/*.jrxml | wc -l
ls packages/cossou/jasperphp/src/JasperStarter/bin | head
```
Expected: `152` templates; `bin` shows `jasperstarter`, `jasperstarter.exe`.

Note: `JasperPHP.php` computes `$resource_directory = __DIR__ . "/../../../../../"` (five levels up = project root). `packages/cossou/jasperphp/src/JasperPHP/` is the same depth as `vendor/cossou/jasperphp/src/JasperPHP/`, so that resolution is unchanged.

- [ ] **Step 2: Give the path package an explicit version and PSR-0 autoload it**

Write `packages/cossou/jasperphp/composer.json`:

```json
{
    "name": "cossou/jasperphp",
    "description": "Create Reports in PHP with JasperReports (vendored copy)",
    "license": "MIT",
    "version": "1.0.0",
    "autoload": {
        "psr-0": {
            "JasperPHP": "src/"
        }
    }
}
```

- [ ] **Step 3: Add the helper**

Write `app/Helpers/helpers.php`:

```php
<?php

if (!function_exists('jasper_template')) {
    /**
     * Absolute path of a JasperReports template shipped in the repository.
     * Templates used to live in vendor/cossou/jasperphp/examples, which is
     * not under version control.
     */
    function jasper_template(string $file): string
    {
        return resource_path('reports/jasper/' . ltrim($file, '/'));
    }
}
```

- [ ] **Step 4: Rewrite the 150 call sites**

```bash
grep -rl "vendor/cossou/jasperphp/examples" Modules app --include=*.php | xargs sed -i -E \
  "s#base_path\('/vendor/cossou/jasperphp/examples/([^']+)'\)#jasper_template('\1')#g"
grep -rn "cossou/jasperphp" Modules app --include=*.php | grep -v "^\s*//" | wc -l
grep -rho "jasper_template('[^']*')" Modules app | wc -l
```
Expected: first count `0` (only commented-out lines may remain — inspect any hit); second count `≈150`.

- [ ] **Step 5: Verify every referenced template exists**

```bash
grep -rhoE "jasper_template\('[^']+'\)" Modules app | sed -E "s/jasper_template\('([^']+)'\)/\1/" | sort -u \
  | while read f; do [ -f "resources/reports/jasper/$f" ] || echo "MISSING $f"; done; echo check-done
```
Expected: only `check-done`. If a `MISSING` line appears, that template was never on this machine either — note it in the commit message; it is a pre-existing gap.

- [ ] **Step 6: Wire composer (path repo + helper file) — do not run `composer update` yet; Task 3 does**

In `composer.json`:
- add top-level `"repositories": [{"type": "path", "url": "packages/cossou/jasperphp", "options": {"symlink": false}}]`
- add `"cossou/jasperphp": "^1.0"` to `require`
- add `"app/Helpers/helpers.php"` to `autoload.files` (create the `files` array under `autoload`, not `autoload-dev`)
- also move `"app/Helpers/DynamicsFunctions.php"` from `autoload-dev.files` to `autoload.files` (it is needed in production; it currently only loads because the class is also PSR-4 reachable)

- [ ] **Step 7: Stop ignoring the new directories; ignore Jasper output**

Append to `.gitignore`:
```
/storage/reports_tmp/*
!/storage/reports_tmp/.gitignore
```
Create `storage/reports_tmp/.gitignore` containing `*` and `!.gitignore`.

- [ ] **Step 8: Syntax-check the touched controllers**

```bash
for f in Modules/BackOffice/Http/Controllers/BackOfficeReportController.php Modules/Maintenance/Http/Controllers/MaintenanceReportController.php Modules/Masters/Http/Controllers/MasterReportController.php Modules/Sales/Http/Controllers/SalesReportController.php app/Helpers/helpers.php; do php -l "$f" | grep -v Deprecated; done
```
Expected: `No syntax errors detected` ×5.

- [ ] **Step 9: Commit**

```bash
git add packages resources/reports app/Helpers/helpers.php composer.json .gitignore storage/reports_tmp/.gitignore Modules/*/Http/Controllers/*ReportController.php
git commit -m "build(reports): vendor JasperPHP as a path package and ship jrxml templates in resources/

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 3: New dependency set

**Files:**
- Modify: `composer.json`
- Create: `composer.lock` (regenerated)
- Possibly create: `packages/artisaninweb/laravel-soap/` (fallback only)

**Interfaces:**
- Produces: a `vendor/` matching Laravel 11. Nothing boots yet — Task 4 makes it boot.

- [ ] **Step 1: Replace `composer.json` `require`/`require-dev`/config with the target set (keep `repositories` and `autoload` from Task 2)**

```json
{
    "name": "laravel/laravel",
    "description": "PLMS — property & lease management.",
    "license": "MIT",
    "type": "project",
    "repositories": [
        { "type": "path", "url": "packages/cossou/jasperphp", "options": { "symlink": false } }
    ],
    "require": {
        "php": "^8.2",
        "artisaninweb/laravel-soap": "^0.3.0.13",
        "barryvdh/laravel-dompdf": "^3.0",
        "cossou/jasperphp": "^1.0",
        "diglactic/laravel-breadcrumbs": "^9.0",
        "intervention/image": "^2.7",
        "kyslik/column-sortable": "^6.5",
        "laravel/framework": "^11.23",
        "laravel/helpers": "^1.7",
        "laravel/tinker": "^2.9",
        "laravel/ui": "^4.5",
        "maatwebsite/excel": "^3.1.55",
        "mpdf/mpdf": "^8.2",
        "nesbot/carbon": "^2.72",
        "nwidart/laravel-modules": "^11.0",
        "setasign/fpdf": "^1.8",
        "simplesoftwareio/simple-qrcode": "^4.2",
        "spatie/laravel-activitylog": "^4.8",
        "spatie/laravel-permission": "^6.9"
    },
    "require-dev": {
        "driftingly/rector-laravel": "^1.2",
        "fakerphp/faker": "^1.23",
        "larastan/larastan": "^2.9",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.1",
        "phpunit/phpunit": "^10.5",
        "rector/rector": "^1.2"
    },
    "autoload": {
        "classmap": [
            "database/seeds",
            "database/factories"
        ],
        "psr-4": {
            "App\\": "app/",
            "Modules\\": "Modules/"
        },
        "files": [
            "app/Helpers/helpers.php",
            "app/Helpers/DynamicsFunctions.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi"
        ]
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

Removed on purpose: `fideloper/proxy` (in framework since L7), `doctrine/dbal` (no `->change()` migrations use it; L11 doesn't need it), `davejamesmiller/laravel-breadcrumbs` (abandoned; `diglactic` is the maintained fork with the same `Breadcrumbs::for()` API), `beyondcode/laravel-dump-server`, `filp/whoops` (pulled in by framework dev), `fzaninotto/faker` (→ `fakerphp/faker`).

- [ ] **Step 2: Resolve — skip scripts because the app cannot boot yet**

```bash
rm -rf vendor composer.lock
composer update --no-scripts --no-interaction 2>&1 | tail -30
```
Expected: `Generating optimized autoload files` and no `Your requirements could not be resolved`.

- [ ] **Step 3: If — and only if — resolution fails on `artisaninweb/laravel-soap`, vendor it as a path package**

The package's newest tag may not declare `illuminate/support ^11`. In that case:

```bash
mkdir -p packages/artisaninweb
cp -r docs/upgrade/../../vendor/artisaninweb/laravel-soap packages/artisaninweb/laravel-soap 2>/dev/null \
  || git show master:composer.lock > /dev/null  # (vendor was deleted in Step 2 — restore from the baseline lock's dist URL instead:)
```
If `vendor/artisaninweb/laravel-soap` is gone, download the version pinned in `docs/upgrade/baseline-composer.lock` (`0.3.0.9`, GitHub `artisaninweb/laravel-soap`) into `packages/artisaninweb/laravel-soap/`, then edit its `composer.json`:
- `"version": "0.3.0.9"`
- in `require`, set every `illuminate/*` constraint to `"^11.0"`
- keep `"ext-soap": "*"`

Add a second entry to `repositories`: `{ "type": "path", "url": "packages/artisaninweb/laravel-soap", "options": { "symlink": false } }`, change the require to `"artisaninweb/laravel-soap": "^0.3.0.9"`, and rerun Step 2. The package is two classes (`SoapWrapper`, `Service`) around PHP's `SoapClient`; its PHP-8 compatibility is fine.

- [ ] **Step 4: Confirm the set that was resolved**

```bash
composer show --direct 2>/dev/null | awk '{print $1, $2}'
```
Expected: `laravel/framework v11.x`, `nesbot/carbon 2.x`, `nwidart/laravel-modules v11.x`, `spatie/laravel-permission 6.x`, `spatie/laravel-activitylog 4.x`, `barryvdh/laravel-dompdf v3.x`, `cossou/jasperphp 1.0.0`.

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock packages
git commit -m "build: move to Laravel 11 / PHP ^8.2 dependency set

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 4: Boot on Laravel 11 — bootstrap, Kernel, middleware, handler, config, env

Goal of this task: `php artisan about` runs. Routes may still fail to register (Task 5/6 fix package-level issues).

**Files:**
- Modify: `bootstrap/app.php`, `public/index.php`, `artisan`
- Modify: `app/Http/Kernel.php`
- Modify: `app/Http/Middleware/TrustProxies.php`, `CheckForMaintenanceMode.php` (delete) → `PreventRequestsDuringMaintenance.php` (create), `RedirectIfAuthenticated.php`, `Authenticate.php`
- Modify: `app/Exceptions/Handler.php`
- Modify: `config/app.php`, `config/auth.php`, `config/database.php`, `config/mail.php`, `config/session.php`, `config/cache.php`, `config/queue.php`, `config/logging.php`, `config/filesystems.php`, `config/hashing.php`, `config/view.php`, `config/broadcasting.php`
- Modify: `.env`, `.env.example`

- [ ] **Step 1: Replace the three entry files with the Laravel 10-style versions (still Kernel-based; Laravel 11 supports them)**

`bootstrap/app.php`:
```php
<?php

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

return $app;
```

`public/index.php`:
```php
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

`artisan`:
```php
#!/usr/bin/env php
<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput,
    new Symfony\Component\Console\Output\ConsoleOutput
);

$kernel->terminate($input, $status);

exit($status);
```

- [ ] **Step 2: Kernel — maintenance middleware, `$middlewareAliases`, spatie namespace**

Edit `app/Http/Kernel.php`:
- `\App\Http\Middleware\CheckForMaintenanceMode::class` → `\App\Http\Middleware\PreventRequestsDuringMaintenance::class`
- rename property `protected $routeMiddleware` → `protected $middlewareAliases`
- `\Spatie\Permission\Middlewares\RoleMiddleware::class` → `\Spatie\Permission\Middleware\RoleMiddleware::class` (same for `PermissionMiddleware`, `RoleOrPermissionMiddleware` — v6 dropped the trailing "s")
- add `'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,` and `'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,` to the aliases (harmless; keeps parity with the L11 skeleton)
- leave `web`/`api` groups and `AuthenticateSession` exactly as they are.

- [ ] **Step 3: Middleware classes**

Delete `app/Http/Middleware/CheckForMaintenanceMode.php`. Create `app/Http/Middleware/PreventRequestsDuringMaintenance.php`:
```php
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    protected $except = [];
}
```

Replace `app/Http/Middleware/TrustProxies.php`:
```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    protected $proxies;

    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
```

Replace `app/Http/Middleware/RedirectIfAuthenticated.php`:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect('/');
            }
        }

        return $next($request);
    }
}
```
(Check the old file first: if it redirected somewhere other than `/`, keep that target.)

Replace `app/Http/Middleware/Authenticate.php`:
```php
<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
```

`EncryptCookies`, `TrimStrings`, `VerifyCsrfToken` — open each; they only declare `$except` arrays and extend framework classes, so they stay.

- [ ] **Step 4: Exception handler — `Exception` → `Throwable`**

Replace `app/Exceptions/Handler.php`:
```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            session()->flash('expiredSession', 'null');
            return redirect()
                ->back()
                ->withInput($request->except('_token'));
        }

        return parent::render($request, $e);
    }
}
```

- [ ] **Step 5: `config/app.php` — providers and aliases**

Replace the whole `'providers' => [ ... ]` array with:
```php
    'providers' => \Illuminate\Support\ServiceProvider::defaultProviders()->merge([
        Spatie\Permission\PermissionServiceProvider::class,
        Intervention\Image\ImageServiceProvider::class,
        Barryvdh\DomPDF\ServiceProvider::class,
        SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class,
        Artisaninweb\SoapWrapper\ServiceProvider::class,
        Maatwebsite\Excel\ExcelServiceProvider::class,
        JasperPHP\JasperPHPServiceProvider::class,

        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\ComposerServiceProvider::class,
    ])->toArray(),
```
Replace the `'aliases'` array with `Illuminate\Support\Facades\Facade::defaultAliases()->merge([...])->toArray()` keeping only the non-framework entries:
```php
    'aliases' => \Illuminate\Support\Facades\Facade::defaultAliases()->merge([
        'Image'    => Intervention\Image\Facades\Image::class,
        'TenantRenewalController' => Modules\BackOffice\Http\Controllers\TenantRenewalController::class,
        'PDF'      => Barryvdh\DomPDF\Facade\Pdf::class,
        'QrCode'   => SimpleSoftwareIO\QrCode\Facades\QrCode::class,
        'Dynamics' => App\Helpers\DynamicsFunctions::class,
        'Excel'    => Maatwebsite\Excel\Facades\Excel::class,
        'Breadcrumbs' => Diglactic\Breadcrumbs\Breadcrumbs::class,
    ])->toArray(),
```
Keep `'timezone' => 'Asia/Muscat'`, `'locale' => 'en'`, `'name'`, `'env'`, `'debug'`, `'url'`, `'key'`, `'cipher'`. Add `'maintenance' => ['driver' => 'file']` if absent.

- [ ] **Step 6: Other config files — apply exactly these changes**

- `config/auth.php`: keep `providers.users.model => App\User::class` and `passwords.users.table => 'password_resets'`. Add `'expire' => 60, 'throttle' => 60` under `passwords.users` if missing. Add `'password_timeout' => 10800` at the end.
- `config/database.php`: in the `pgsql` connection rename `'schema' => 'public'` → `'search_path' => 'public'`; add `'url' => env('DATABASE_URL')`, `'charset' => 'utf8'`, `'prefix_indexes' => true` if missing. Add a `'sqlite'` connection with `'database' => env('DB_DATABASE', database_path('database.sqlite'))` for tests. Keep `'default' => env('DB_CONNECTION', 'pgsql')`.
- `config/mail.php`: replace the file with the Laravel 11 structure (`'default' => env('MAIL_MAILER', 'smtp')`, `'mailers' => ['smtp' => [...], 'log' => [...], 'array' => [...], 'failover' => [...]]`, `'from' => ['address' => env('MAIL_FROM_ADDRESS'), 'name' => env('MAIL_FROM_NAME')]`). The old file's only custom value is the `from` block — keep it.
- `config/session.php`: keep `'driver' => env('SESSION_DRIVER', 'file')`, `'lifetime'`, `'expire_on_close' => false`; add `'partitioned' => false` and `'same_site' => 'lax'` if missing.
- `config/cache.php`: keep `'default' => env('CACHE_DRIVER', 'file')`; add `'stores.file.lock_path' => storage_path('framework/cache/data')`.
- `config/queue.php`: keep `'default' => env('QUEUE_CONNECTION', 'sync')`; add `'batching'` block:
  `'batching' => ['database' => env('DB_CONNECTION', 'pgsql'), 'table' => 'job_batches']`.
- `config/logging.php`: keep as is; add `'deprecations' => ['channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'), 'trace' => false]`.
- `config/filesystems.php`: keep disks; add `'links' => [public_path('storage') => storage_path('app/public')]` if missing.
- `config/hashing.php`: add `'rehash_on_login' => true`.
- `config/view.php`, `config/broadcasting.php`, `config/services.php`: unchanged.
- `config/breadcrumbs.php`: unchanged for now (Task 6 handles it).

- [ ] **Step 7: `.env` / `.env.example`**

Add to both (keep existing keys):
```
MAIL_MAILER=smtp
LOG_DEPRECATIONS_CHANNEL=null
```
`MAIL_DRIVER` can stay; it is ignored.

- [ ] **Step 8: Boot the console**

```bash
composer dump-autoload 2>&1 | tail -3
php artisan about 2>&1 | head -40
```
Expected: the `Environment` / `Cache` / `Drivers` tables print; `Laravel Version 11.x`, `PHP Version 8.4.22`. If it stops with a class-not-found from a package provider or a module, note the class — Task 5 (modules) and Task 6 (packages) are the fixes; only fix here if it is one of the files in this task's list.

- [ ] **Step 9: Commit**

```bash
git add bootstrap/app.php public/index.php artisan app/Http/Kernel.php app/Http/Middleware app/Exceptions/Handler.php config .env.example
git commit -m "chore(upgrade): Laravel 11 bootstrap, kernel, middleware, handler and config

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```
(`.env` is git-ignored — do not add it.)

---

### Task 5: nwidart/laravel-modules 11

**Files:**
- Modify: `config/modules.php` (republish)
- Create: `modules_statuses.json`
- Modify: `Modules/{BackOffice,General,Maintenance,Masters,Menu,Sales}/Providers/*ServiceProvider.php`

**Interfaces:**
- Consumes: module directories and `module.json` files exactly as they are.

- [ ] **Step 1: Republish the v11 config, then restore our paths**

```bash
mv config/modules.php config/modules.v4.php
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider" --tag=config --force 2>&1 | tail -2
```
Then in the new `config/modules.php` set:
- `'namespace' => 'Modules'`
- `'paths.modules' => base_path('Modules')`
- `'paths.assets' => public_path('modules')`
- `'paths.migration' => base_path('database/migrations')`
- under `'paths.generator'`, the module sub-folders use capitalised names in this project. Set:
  `'config' => ['path' => 'Config', 'generate' => true]`, `'command' => ['path' => 'Console', 'generate' => true]`, `'migration' => ['path' => 'Database/Migrations', 'generate' => true]`, `'seeder' => ['path' => 'Database/Seeders', 'generate' => true]`, `'factory' => ['path' => 'Database/factories', 'generate' => true]`, `'model' => ['path' => 'Entities', 'generate' => true]`, `'routes' => ['path' => 'Routes', 'generate' => true]`, `'controller' => ['path' => 'Http/Controllers', 'generate' => true]`, `'filter' => ['path' => 'Http/Middleware', 'generate' => true]`, `'request' => ['path' => 'Http/Requests', 'generate' => true]`, `'provider' => ['path' => 'Providers', 'generate' => true]`, `'assets' => ['path' => 'Resources/assets', 'generate' => true]`, `'lang' => ['path' => 'Resources/lang', 'generate' => true]`, `'views' => ['path' => 'Resources/views', 'generate' => true]`, `'test' => ['path' => 'Tests/Unit', 'generate' => true]`, `'test-feature' => ['path' => 'Tests/Feature', 'generate' => true]`, `'event' => ['path' => 'Events', 'generate' => true]`, `'listener' => ['path' => 'Listeners', 'generate' => true]`, `'emails' => ['path' => 'Emails', 'generate' => true]`, `'notifications' => ['path' => 'Notifications', 'generate' => true]`, `'jobs' => ['path' => 'Jobs', 'generate' => true]`.
- `'scan.enabled' => false`
- `'activators.file.statuses-file' => base_path('modules_statuses.json')`

Delete `config/modules.v4.php`.

- [ ] **Step 2: Statuses file (v6+ ignores `"active"` in module.json)**

Write `modules_statuses.json`:
```json
{
    "BackOffice": true,
    "General": true,
    "Maintenance": true,
    "Masters": true,
    "Menu": true,
    "Sales": true
}
```

- [ ] **Step 3: Remove the legacy factory loader from each module provider**

In each `Modules/<M>/Providers/<M>ServiceProvider.php`:
- delete the `use Illuminate\Database\Eloquent\Factory;` import (class removed in Laravel 8)
- delete the `registerFactories()` method and its call in `boot()`
- delete `protected $defer = false;` (property removed from the base class in Laravel 5.8)
Everything else (`registerConfig`, `registerViews`, `registerTranslations`, `registerBreadcrumbs`, `loadMigrationsFrom`) stays.

Also in `Modules/<M>/Providers/RouteServiceProvider.php`: keep `$namespace` and `map()` — Laravel 11 still calls `map()` when it exists and honours `Route::namespace()`.

- [ ] **Step 4: Verify modules are seen and their providers boot**

```bash
php artisan module:list 2>&1 | tail -10
php artisan route:list --json 2>/dev/null | php -r '$r=json_decode(stream_get_contents(STDIN),true); echo count($r)," routes\n";'
```
Expected: 6 modules `Enabled`; a route count > 900. If `route:list` throws a class-not-found from a *package* (breadcrumbs, sortable, spatie…) continue to Task 6; if it throws from a module file, fix that file here.

- [ ] **Step 5: Commit**

```bash
git add config/modules.php modules_statuses.json Modules/*/Providers
git commit -m "chore(upgrade): nwidart/laravel-modules 11 — config, statuses file, provider cleanup

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 6: Package API and config migrations

**Files:**
- Modify: `config/permission.php`, `config/activitylog.php`, `config/breadcrumbs.php`, `config/columnsortable.php`
- Create: `config/dompdf.php` (publish)
- Create: `database/migrations/2026_09_18_000001_upgrade_activity_log_for_v4.php`
- Modify: files using `PDF::` (7 controllers) — only if the alias in Task 5 is not enough

- [ ] **Step 1: spatie/permission 6 config**

```bash
mv config/permission.php config/permission.v2.php
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag=permission-config --force 2>&1 | tail -1
```
In the new `config/permission.php` keep the defaults except: `'cache.key' => 'spatie.permission.cache'` (unchanged name — the app calls `app('cache')->forget('spatie.permission.cache')` in migrations), `'teams' => false`, `'register_permission_check_method' => true`. Diff `config/permission.v2.php` for any custom `table_names`/`column_names` and carry them over; then delete the `.v2` file. No DB migration is needed: the v2.25 tables already carry `model_type` and the pivot names v6 expects.

- [ ] **Step 2: spatie/activitylog 4 — config and columns**

```bash
mv config/activitylog.php config/activitylog.v3.php
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config" --force 2>&1 | tail -1
```
Carry over any custom `default_log_name`/`table_name` from the `.v3` file, then delete it.

Create `database/migrations/2026_09_18_000001_upgrade_activity_log_for_v4.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Columns spatie/laravel-activitylog v4 expects on the v3 table. */
return new class extends Migration
{
    public function up(): void
    {
        $table = config('activitylog.table_name', 'activity_log');

        Schema::table($table, function (Blueprint $t) use ($table) {
            if (!Schema::hasColumn($table, 'event')) {
                $t->string('event')->nullable()->after('subject_type');
            }
            if (!Schema::hasColumn($table, 'batch_uuid')) {
                $t->uuid('batch_uuid')->nullable()->after('properties');
            }
        });
    }

    public function down(): void
    {
        $table = config('activitylog.table_name', 'activity_log');

        Schema::table($table, function (Blueprint $t) {
            $t->dropColumn(['event', 'batch_uuid']);
        });
    }
};
```

- [ ] **Step 3: Breadcrumbs — the diglactic fork**

`config/breadcrumbs.php` keeps `'view' => 'includes.breadcrumbs'`. Add `'files' => []` (the modules `require` their own `Routes/breadcrumbs.php` from their providers, so the package must not also load them), and `'unnamed-route-exception' => false`, `'missing-route-bound-breadcrumb-exception' => false`, `'invalid-named-breadcrumb-exception' => false`, `'manager-class' => Diglactic\Breadcrumbs\Manager::class`, `'generator-class' => Diglactic\Breadcrumbs\Generator::class`.

The `Breadcrumbs` alias was set in Task 4 Step 5, so `Breadcrumbs::for(...)`/`Breadcrumbs::render()` in modules and views resolve without edits.

- [ ] **Step 4: column-sortable 6 and dompdf 3**

```bash
php artisan vendor:publish --provider="Kyslik\ColumnSortable\ColumnSortableServiceProvider" --tag=config --force 2>&1 | tail -1
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider" --tag=config --force 2>&1 | tail -1
git diff --stat config/columnsortable.php
```
Carry over any custom icon classes from the old `columnsortable.php` shown in the diff. In `config/dompdf.php` set `'options.enable_remote' => true` (report views embed the logo by URL) and `'options.chroot' => base_path()`.

- [ ] **Step 5: simple-qrcode 4 — PNG needs Imagick**

`Modules/*/…` use `QrCode::format('png')->size(400)->generate(...)`. simple-qrcode 4 renders PNG through Imagick only. Check:

```bash
php -m | grep -i imagick
```
If present: nothing to change; add `ext-imagick` to the deploy notes (Task 12).
If absent locally: install the Imagick extension for the local PHP (laragon: drop `php_imagick.dll` + ImageMagick DLLs into the PHP `ext` dir and enable in `php.ini`) — do **not** switch to SVG; the 7 call sites embed the result as `data:image/png;base64` in PDFs rendered by dompdf, which does not render inline SVG.

- [ ] **Step 6: laravel/ui auth scaffolding**

`Auth::routes()` and `AuthenticatesUsers` / `ResetsPasswords` / `SendsPasswordResetEmails` / `RegistersUsers` / `VerifiesEmails` now come from `laravel/ui`. No file edits: the traits keep the `Illuminate\Foundation\Auth\*` namespace. Verify:

```bash
php -r 'require "vendor/autoload.php"; echo class_exists("Illuminate\\Foundation\\Auth\\AuthenticatesUsers") ? "ok\n" : "missing\n";'
```
Expected: `ok`.

- [ ] **Step 7: Routes register**

```bash
php artisan route:list --json 2>/dev/null | php -r '$r=json_decode(stream_get_contents(STDIN),true); $n=array_filter(array_column($r,"name")); sort($n); file_put_contents("docs/upgrade/after-route-names.txt", implode("\n",array_unique($n))."\n"); echo count($r)," routes\n";'
comm -23 docs/upgrade/baseline-route-names.txt docs/upgrade/after-route-names.txt
```
Expected: route count > 900 and `comm` prints nothing (every baseline route name still exists). Any name printed = a route that failed to register; find it in `Modules/*/Routes/web.php` and fix.

- [ ] **Step 8: Commit**

```bash
git add config database/migrations/2026_09_18_000001_upgrade_activity_log_for_v4.php docs/upgrade/after-route-names.txt
git commit -m "chore(upgrade): spatie permission 6 / activitylog 4, breadcrumbs fork, sortable 6, dompdf 3 config

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 7: Eloquent — `$dates` → `$casts`, legacy date serialisation

Laravel 10 removed `protected $dates`; Laravel 7 changed JSON date serialisation to ISO-8601. 152 models.

**Files:**
- Create: `app/Concerns/LegacyDateSerialization.php`
- Modify: all `Modules/*/Entities/*.php` and `app/User.php`, `app/Setting.php`
- Create (scratch, not committed): `scratch/convert_dates.php`

**Interfaces:**
- Produces: trait `App\Concerns\LegacyDateSerialization` with `serializeDate(DateTimeInterface $date): string` returning `$date->format('Y-m-d H:i:s')`.

- [ ] **Step 1: Write the trait**

`app/Concerns/LegacyDateSerialization.php`:
```php
<?php

namespace App\Concerns;

use DateTimeInterface;

/**
 * Laravel 7+ serialises dates as ISO-8601 in toArray()/toJson().
 * The AJAX list pages and reports were written against the
 * Laravel 5.7 format, so keep that.
 */
trait LegacyDateSerialization
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
```

- [ ] **Step 2: Write a failing test for the conversion outcome**

`tests/Unit/ModelDateCompatibilityTest.php`:
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ModelDateCompatibilityTest extends TestCase
{
    private function modelFiles(): array
    {
        $files = glob(base_path('Modules/*/Entities/*.php'));
        $files[] = base_path('app/User.php');
        return $files;
    }

    public function test_no_model_uses_removed_dates_property(): void
    {
        $offenders = [];
        foreach ($this->modelFiles() as $f) {
            if (preg_match('/protected\s+\$dates\s*=/', file_get_contents($f))) {
                $offenders[] = basename($f);
            }
        }
        $this->assertSame([], $offenders, 'protected $dates was removed in Laravel 10');
    }

    public function test_every_model_keeps_legacy_date_serialisation(): void
    {
        $missing = [];
        foreach ($this->modelFiles() as $f) {
            $src = file_get_contents($f);
            if (preg_match('/extends\s+(Model|Authenticatable|Pivot)\b/', $src)
                && !str_contains($src, 'LegacyDateSerialization')) {
                $missing[] = basename($f);
            }
        }
        $this->assertSame([], $missing);
    }
}
```
`base_path()` is not available in a bare PHPUnit TestCase — replace the two `base_path(...)` calls with `dirname(__DIR__, 2) . '/...'`.

- [ ] **Step 3: Run it to see it fail**

Run: `vendor/bin/phpunit tests/Unit/ModelDateCompatibilityTest.php`
Expected: 2 failures listing ~52 and ~152 files.

- [ ] **Step 4: Conversion script**

`scratch/convert_dates.php` (scratchpad directory, not committed):
```php
<?php
// Rewrites `protected $dates = [...]` into datetime casts and adds the
// LegacyDateSerialization trait to every Eloquent model.
$root = dirname(__DIR__);
$files = glob("$root/Modules/*/Entities/*.php");
$files[] = "$root/app/User.php";

foreach ($files as $file) {
    $src = file_get_contents($file);
    $orig = $src;

    // 1. $dates -> $casts
    if (preg_match('/protected\s+\$dates\s*=\s*\[(.*?)\];/s', $src, $m)) {
        preg_match_all("/'([^']+)'/", $m[1], $names);
        $castLines = array_map(fn ($n) => "'$n' => 'datetime'", $names[1]);
        if (preg_match('/protected\s+\$casts\s*=\s*\[(.*?)\];/s', $src, $c)) {
            $merged = rtrim(trim($c[1]), ',');
            $merged = ($merged === '' ? '' : $merged . ', ') . implode(', ', $castLines);
            $src = str_replace($c[0], "protected \$casts = [$merged];", $src);
            $src = str_replace($m[0], '', $src);
        } else {
            $src = str_replace($m[0], 'protected $casts = [' . implode(', ', $castLines) . '];', $src);
        }
    }

    // 2. trait
    if (preg_match('/extends\s+(Model|Authenticatable|Pivot)\b[^{]*\{/', $src, $cls, PREG_OFFSET_CAPTURE)
        && !str_contains($src, 'LegacyDateSerialization')) {
        $pos = $cls[0][1] + strlen($cls[0][0]);
        $src = substr($src, 0, $pos) . "\n    use \\App\\Concerns\\LegacyDateSerialization;" . substr($src, $pos);
    }

    if ($src !== $orig) {
        file_put_contents($file, $src);
        echo "updated ", str_replace("$root/", '', $file), "\n";
    }
}
```
Save it under the session scratchpad and run: `php <scratchpad>/convert_dates.php | wc -l`
Expected: ≈152 `updated` lines.

- [ ] **Step 5: Lint everything the script touched and run the test**

```bash
git diff --name-only | grep -E "Entities|User.php" | while read f; do php -l "$f" | grep -v "No syntax errors" | grep -v Deprecated; done; echo lint-done
vendor/bin/phpunit tests/Unit/ModelDateCompatibilityTest.php
```
Expected: only `lint-done`; then `OK (2 tests)`.

- [ ] **Step 6: Spot-check one converted model**

Run: `sed -n 1,25p Modules/BackOffice/Entities/DepositRefund.php`
Expected: `use \App\Concerns\LegacyDateSerialization;` inside the class and `protected $casts = ['deposit_refund_date' => 'datetime', ...]`, no `$dates`.

- [ ] **Step 7: Commit**

```bash
git add app/Concerns/LegacyDateSerialization.php Modules/*/Entities app/User.php tests/Unit/ModelDateCompatibilityTest.php
git commit -m "refactor(models): \$dates -> datetime casts; keep Y-m-d H:i:s JSON dates via trait

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 8: PHP 8 source sweep — Rector, syntax, Larastan

**Files:**
- Create: `rector.php`, `phpstan.neon`
- Modify: whatever Rector rewrites and Larastan flags under `app/`, `Modules/`, `config/function.php`, `database/`

- [ ] **Step 1: Rector config — PHP-level sets only, plus the Laravel migration sets**

`rector.php`:
```php
<?php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/Modules',
        __DIR__ . '/config/function.php',
        __DIR__ . '/database',
        __DIR__ . '/routes',
    ])
    ->withSkip([
        __DIR__ . '/Modules/*/Resources/*',
        __DIR__ . '/Modules/*/Database/factories/*',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_82,
        LaravelSetList::LARAVEL_60,
        LaravelSetList::LARAVEL_70,
        LaravelSetList::LARAVEL_80,
        LaravelSetList::LARAVEL_90,
        LaravelSetList::LARAVEL_100,
        LaravelSetList::LARAVEL_110,
    ]);
```

- [ ] **Step 2: Dry run, read the summary**

```bash
vendor/bin/rector process --dry-run 2>&1 | tail -40
```
Expected: a list of files with diffs. Read the applied rule names printed at the end; if any rule renames public methods or changes return types in a way views depend on (e.g. `Str`/`Arr` replacements are fine, `RequestStaticValidateToInjectRector` is fine), proceed. Rector only rewrites PHP syntax and known framework API renames.

- [ ] **Step 3: Apply, lint, commit Rector separately**

```bash
vendor/bin/rector process 2>&1 | tail -5
find app Modules config database routes -name "*.php" -not -name "*.blade.php" -print0 | xargs -0 -n1 php -l 2>&1 | grep -v "No syntax errors" | grep -v Deprecated; echo lint-done
git add -A app Modules config database routes rector.php
git commit -m "refactor: rector PHP 8.2 + Laravel 6..11 sets

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```
Expected: only `lint-done` between the rector output and the commit.

- [ ] **Step 4: Blade syntax — compile every view**

```bash
php artisan view:clear && php artisan view:cache 2>&1 | tail -3
```
Expected: `Blade templates cached successfully.` A failure names the view and line — fix and rerun. Then `php artisan view:clear` (do not leave the cache in dev).

- [ ] **Step 5: Larastan level 0 — undefined classes, methods, facades**

`phpstan.neon`:
```neon
includes:
    - vendor/larastan/larastan/extension.neon
parameters:
    level: 0
    paths:
        - app
        - Modules
        - config/function.php
    excludePaths:
        - Modules/*/Resources/*
        - Modules/*/Tests/*
    ignoreErrors:
        - '#Unsafe usage of new static#'
```

```bash
vendor/bin/phpstan analyse --memory-limit=1G 2>&1 | tail -60
```
Work through every `Class ... not found`, `Call to undefined method`, `Function ... not found`. Known ones and their fixes:
- `Barryvdh\DomPDF\Facade` → already aliased as `PDF`; any `use Barryvdh\DomPDF\Facade;` import becomes `use Barryvdh\DomPDF\Facade\Pdf as PDF;`
- `Illuminate\Database\Eloquent\Factory` → already removed in Task 5
- `Spatie\Permission\Middlewares\*` → `Spatie\Permission\Middleware\*`
- `DaveJamesMiller\Breadcrumbs\...` imports → `Diglactic\Breadcrumbs\...`
- `Illuminate\Http\Request::get()` on a *facade* `Request::get()` is fine; `Input::` does not occur.
- `(string) DB::raw(...)` → `DB::raw(...)->getValue(DB::getQueryGrammar())`
- `str_*`/`array_*` helpers are provided by `laravel/helpers`; no change.
Repeat until the output is `[OK] No errors`.

- [ ] **Step 6: Commit the hand fixes**

```bash
git add -A app Modules config phpstan.neon
git commit -m "fix(upgrade): resolve Larastan level-0 findings after Laravel 11 move

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 9: Test suite on PHPUnit 10

**Files:**
- Modify: `phpunit.xml`
- Modify: `tests/TestCase.php`, `tests/CreatesApplication.php` (if needed)

- [ ] **Step 1: Migrate the XML schema**

```bash
vendor/bin/phpunit --migrate-configuration 2>&1 | tail -2
```
Then edit `phpunit.xml`: keep the `Unit`/`Feature` suites and the `<php>` env block; change `<env name="QUEUE_DRIVER" .../>` to `<env name="QUEUE_CONNECTION" value="sync"/>`; add `<env name="DB_CONNECTION" value="pgsql"/>` (the money-path tests are pure unit tests; feature tests are only `ExampleTest`). Set `<coverage>` / `<source>` to `./app` and `./Modules`.

- [ ] **Step 2: Run**

```bash
vendor/bin/phpunit 2>&1 | tail -15
```
Expected: `OK (N tests, M assertions)` with N ≥ 40 (the 13 existing classes + Task 7's). Failures in the calculators point at Carbon/collection behaviour changes — fix the code, not the test, unless the test encoded a Carbon-1 quirk.

- [ ] **Step 3: Commit**

```bash
git add phpunit.xml tests
git commit -m "test: phpunit 10 configuration

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 10: Database — package migrations against a copy

**Files:** none new.

- [ ] **Step 1: Work on a copy of the local DB, never `plms_new` directly**

Use the PDO helper approach from the session scratchpad (`q.php`) or HeidiSQL/pgAdmin:
```sql
CREATE DATABASE plms_upgrade TEMPLATE plms_new;
```
Set `DB_DATABASE=plms_upgrade` in `.env`.

- [ ] **Step 2: Status, then migrate**

```bash
php artisan migrate:status 2>&1 | grep -v "Ran" | head -20
php artisan migrate --force 2>&1 | tail -10
```
Expected: pending = the activitylog upgrade migration (plus any unrun feature migrations from master that are also pending on `plms_new` — leave those to run as well; they are yours). After: `DONE` for each.

- [ ] **Step 3: Permission cache and config sanity**

```bash
php artisan permission:cache-reset
php artisan config:clear && php artisan route:clear && php artisan view:clear
php artisan tinker --execute='echo \App\User::count(), " users; ", \Spatie\Permission\Models\Permission::count(), " permissions\n";'
```
Expected: non-zero counts, no deprecation flood (that was the 5.7-on-8.4 symptom).

No commit (no file changes).

---

### Task 11: Browser smoke test — critical paths

Use `docs/screen-inventory.md` for route names. Run the app with `php artisan serve --port=8001` (or the laragon vhost) and drive it with the Chrome tools. Fix as found; each fix is its own commit `fix(upgrade): <screen> — <what>`.

- [ ] **Step 1: Login / session / menu**
  - `GET /login`, log in with a known admin. Expected: dashboard renders, sidebar shows the same tree as `docs/screen-inventory.md` "Current Navigation Menu".
  - Reload → still logged in (session). Log out → redirected to login.

- [ ] **Step 2: One list page per module (`index_ajax` pattern)**
  - Sales: `enquiry.index`; Masters: `unit.index` (or `building.index`); Maintenance: `complaint.index`; BackOffice: `tenantRenewal.index`, `termination-dues.index`; Menu: `menu.index`; General: any list.
  - On each: page loads, AJAX table fills, sort a column (column-sortable), paginate, use the search partial.

- [ ] **Step 3: Money paths**
  - Rent receipt create (`rent_payment_receipt_create`) → save → view. Dates shown as before (not ISO).
  - Deposit refund create → save. PDC list. Landlord Invoice v2 list → show → PDF.
  - Termination Dues show → PDF (dompdf 3).

- [ ] **Step 4: Output formats**
  - One Excel export (`Excel::download`) e.g. tenant aging.
  - One JasperPHP report from `BackOfficeReportController` (PDF and XLSX variants) — requires Java on PATH; the output lands in `storage/reports_tmp/`.
  - One QR code (unit QR).
  - One mail-sending action with `MAIL_MAILER=log`, then confirm the mail body in `storage/logs/laravel.log`.

- [ ] **Step 5: Permissions**
  - Log in as a restricted role; confirm a menu item hidden for that role is hidden and its route returns 403 when hit directly.

- [ ] **Step 6: Record the result**

Append a "Smoke test 2026-xx-xx" section to `docs/upgrade/deploy-new-server.md` (created in Task 12) listing each check as pass/fail with the commit that fixed any failure. Commit together with Task 12.

---

### Task 12: Deployment notes and memory

**Files:**
- Create: `docs/upgrade/deploy-new-server.md`
- Update memory: `server-php-version.md` (after the new server is live, not now — add a note to the doc instead)

- [ ] **Step 1: Write the server requirements**

`docs/upgrade/deploy-new-server.md`:
```markdown
# Deploying PLMS (Laravel 11) to the new server

## Server requirements
- PHP 8.2 or 8.3 (CLI + FPM) with extensions: pdo_pgsql, pgsql, mbstring, xml, curl, zip, gd, **imagick** (QR PNG), **soap** (Dynamics AX), bcmath, intl, fileinfo
- Composer 2
- PostgreSQL 13+ client libs
- Java 11+ runtime on PATH (JasperPHP → JasperStarter in `packages/cossou/jasperphp/src/JasperStarter`)
- Node is NOT required (assets are pre-built in `public/`)
- Writable: `storage/`, `bootstrap/cache/`, `storage/reports_tmp/`

## First deploy (git-based; server_upload_files/ is retired)
git clone <repo> && git checkout <release tag>
cp .env.example .env  # then fill DB_*, MAIL_*, APP_URL, APP_KEY (copy from old server)
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan permission:cache-reset
chmod -R ug+rwx storage bootstrap/cache
crontab: * * * * * php /path/artisan schedule:run >> /dev/null 2>&1

## Each release
git pull && composer install --no-dev --optimize-autoloader && php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache

## Data migration from the old server
pg_dump the old DB, restore, then `php artisan migrate --force` (adds activity_log.event / batch_uuid and any pending feature migrations).

## Known behaviour notes
- Dates in JSON responses stay `Y-m-d H:i:s` (App\Concerns\LegacyDateSerialization).
- Carbon is pinned to 2.x on purpose.
```

- [ ] **Step 2: Commit**

```bash
git add docs/upgrade/deploy-new-server.md
git commit -m "docs(upgrade): new-server deployment notes and phase 1 smoke-test record

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

- [ ] **Step 3: Memory**

Update `C:\Users\anoop\.claude\projects\C--laragon-www-plms-backup\memory\server-php-version.md` with one line: "Branch `platform-upgrade` targets PHP ^8.2 / Laravel 11; the <7.4 syntax restriction applies only to `master` / `server_upload_files` until the new server is live." Keep the existing restriction for master.

---

## Self-review

- **Spec coverage:** PHP ^8.2 + Laravel 11 (T3–T8); packages on supported versions (T3, T6); JasperPHP reproducible (T2); same DB with additive migrations only (T6, T10); route/view/permission names preserved and verified (T1, T6 step 7); Carbon 2 pinned and JSON date format kept (T3, T7); tests green (T9); browser verification (T11); new-server deployment (T12); no merge / no server_upload_files (Global Constraints). Phase 2/3 explicitly out of scope.
- **Placeholder scan:** the only conditional branches are T3 step 3 (soap fallback) and T6 step 5 (imagick) — both give the concrete action for each outcome.
- **Consistency:** `jasper_template()` defined in T2 and used in T2 step 4; `LegacyDateSerialization` defined in T7 step 1, asserted in T7 step 2, documented in T12; `docs/upgrade/baseline-route-names.txt` produced in T1 and consumed in T6 step 7; `Breadcrumbs` alias set in T4 step 5 and relied on in T6 step 3; spatie `Middleware` namespace changed in T4 step 2 and listed again in T8 step 5.
