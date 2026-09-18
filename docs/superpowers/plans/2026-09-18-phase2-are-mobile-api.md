# Phase 2 — ARE Mobile API Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A `/api/v1` JSON API (module `MobileApi`) that lets an ARE log in from one registered device and (1) attend maintenance complaints like a technician, (2) perform and submit handover-termination inspections, (3) complete a configurable tenant-onboarding checklist — writing the same rows the desktop writes, through services the desktop is switched to use as well.

**Architecture:** New nwidart module `Modules/MobileApi` owns routes, controllers, requests, resources, device policy and the office "Mobile devices" screen. Business rules are extracted from `ComplaintStageController` and `TenantTerminationController` into `Modules\Maintenance\Services\TicketAttendance` and `Modules\BackOffice\Services\{TerminationWorkflow,TerminationInspection}`; onboarding is new in `Modules\BackOffice\Services\TenantOnboarding`. Building scope comes from one class, `Modules\Masters\Services\AreBuildings`. Auth is Sanctum with never-expiring tokens named after the device uid; a middleware re-checks the device on every request.

**Tech Stack:** Laravel 13 / PHP 8.3+, laravel/sanctum ^4.3, nwidart/laravel-modules 13, spatie/permission 8, spatie/activitylog 5, intervention/image 2.7, barryvdh/laravel-dompdf 3, PostgreSQL, PHPUnit 11.

**Spec:** `docs/superpowers/specs/2026-09-18-phase2-are-mobile-api-design.md`

## Global Constraints

- Branch `platform-upgrade`; never merged until Phase 3 is done. Never touch `server_upload_files/`.
- All work runs against the `.env` database (`plms_upgrade`, a copy of production). Tests that write use `DatabaseTransactions` (rolled back), never `RefreshDatabase`.
- API prefix `/api/v1`; every route except login is behind `auth:sanctum` + `mobile.device`; `MOBILE_API_ENABLED=false` returns 503 `{ "message": "Mobile API disabled", "code": "disabled" }`.
- Error shape: `{ "message", "code", "errors"? }` with 401 `unauthenticated`, 403 `device_not_registered` / `device_blocked` / `forbidden` / `outside_assigned_buildings`, 404 `not_found`, 409 `invalid_state`, 422 `validation`.
- User-facing device messages verbatim: **"This device is not registered. Please contact the administrator."** and **"This device has been blocked. Please contact the administrator."**
- Only users with permission `mobile_app_login` may obtain a token; roles `are` and `are_team_lead` get it.
- Stage codes: complaints 701 Unassigned, 702 Assigned, 703 Sub Assigned, 705 Close; terminations 502 Handover Unassigned, 503 Handover Assigned, 504 TakenOver. Ticket `ticket_status`/`complaint_assign_status`: 1 checked-in, 2 attended, 3 completed, 4 closed.
- Uploads: images only (`jpeg,png,webp`), ≤ 10 MB, resized to max 1600 px + 300 px thumbnail; stored under `storage/app/mobile/…`; served only via signed URLs valid 15 minutes.
- Every API write logs activity with `log_name = 'mobile'` and properties `device_uid`, `app_version`.
- Commits end with `Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>`.
- Commands run from `C:\laragon\www\plms_backup` in Git Bash; `php` is 8.4. Use the Edit/Write tools for files containing backslashes — the shell mangles them.

---

## File map

| Area | Files |
|---|---|
| Module skeleton | `Modules/MobileApi/module.json`, `composer.json`, `Providers/MobileApiServiceProvider.php`, `Providers/RouteServiceProvider.php`, `Routes/api.php`, `Routes/web.php`, `Config/config.php`, `Http/Middleware/{EnsureMobileApiEnabled,EnsureRegisteredDevice}.php`, `Exceptions/ApiExceptionRenderer.php`, `Http/Controllers/ApiController.php` (base) |
| Auth/device | `Modules/MobileApi/Entities/MobileDevice.php`, `Services/DevicePolicy.php`, `Http/Controllers/AuthController.php`, `Http/Requests/LoginRequest.php`, `Http/Resources/{UserResource,BuildingResource}.php`, `database/migrations/2026_09_19_000001_create_mobile_devices_table.php`, `…000002_add_mobile_api_permissions.php`, `app/User.php` (HasApiTokens) |
| Building scope | `Modules/Masters/Services/AreBuildings.php`, `tests/Unit/AreBuildingsTest.php` |
| Complaints | `Modules/Maintenance/Services/TicketAttendance.php`, `Modules/Maintenance/Exceptions/{InvalidStateException,NotAssignedException}.php`, `Modules/MobileApi/Http/Controllers/ComplaintController.php`, `Http/Resources/{TicketListResource,TicketResource}.php`, `Http/Requests/{ClaimTicketRequest,CheckInRequest,NoteRequest,PhotoRequest,ItemRequest,StatusRequest,SignatureRequest}.php`, `Modules/Maintenance/Http/Controllers/ComplaintStageController.php` (delegates) |
| Inspection | `Modules/BackOffice/Services/TerminationWorkflow.php` (extracted `tenantTerminationStage`), `Services/TerminationInspection.php`, `Modules/MobileApi/Http/Controllers/TerminationController.php`, resources/requests |
| Onboarding | `Modules/BackOffice/Database/Migrations/2026_09_19_00000{3,4,5}_…onboarding…`, `Entities/{OnboardingChecklistItem,TenantOnboarding,TenantOnboardingEntry,TenantOnboardingPhoto}.php`, `Services/TenantOnboarding.php`, `Console/OnboardingSync.php`, `Database/Seeders/OnboardingChecklistItemsSeeder.php`, `Modules/MobileApi/Http/Controllers/OnboardingController.php`, desktop: `Modules/Masters/Http/Controllers/OnboardingChecklistItemController.php` + views, `Modules/BackOffice/Http/Controllers/TenantOnboardingController.php` + views + PDF view |
| Office devices screen | `Modules/MobileApi/Http/Controllers/MobileDeviceAdminController.php`, `Resources/views/devices/index.blade.php` |
| Files | `Modules/MobileApi/Services/MobileImage.php`, `Http/Controllers/FileController.php` (signed download) |
| Tests | `Modules/MobileApi/Tests/Feature/*.php`, `Modules/MobileApi/Tests/TestCase.php` (helpers `actingAsAre()`, `withDevice()`) |

---

## Part A — Foundation

### Task 1: Module skeleton, Sanctum, feature flag, error shape

**Files:**
- Create: `Modules/MobileApi/module.json`, `Modules/MobileApi/composer.json`, `Modules/MobileApi/Config/config.php`, `Modules/MobileApi/Providers/MobileApiServiceProvider.php`, `Modules/MobileApi/Providers/RouteServiceProvider.php`, `Modules/MobileApi/Routes/api.php`, `Modules/MobileApi/Routes/web.php`, `Modules/MobileApi/Http/Middleware/EnsureMobileApiEnabled.php`, `Modules/MobileApi/Http/Controllers/ApiController.php`, `Modules/MobileApi/Exceptions/ApiExceptionRenderer.php`, `Modules/MobileApi/Tests/TestCase.php`, `Modules/MobileApi/Tests/Feature/PingTest.php`
- Modify: `modules_statuses.json`, `app/User.php`, `app/Exceptions/Handler.php`, `.env`, `.env.example`, `phpunit.xml`

**Interfaces:**
- Produces: route group `/api/v1` with middleware `['mobile.enabled', 'api']`; `ApiController::error(string $code, string $message, int $status, array $errors = []): JsonResponse`; `Modules\MobileApi\Tests\TestCase` with `protected function api(): static` (adds `Accept: application/json`).

- [ ] **Step 1: Install Sanctum and publish its migration**

```bash
composer require laravel/sanctum:^4.3 --no-interaction 2>&1 | tail -2
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag=sanctum-migrations 2>&1 | tail -1
php artisan migrate --force --path=database/migrations/$(ls database/migrations | grep personal_access_tokens | head -1)
```
Expected: `personal_access_tokens` table created (`migrate` prints DONE).

- [ ] **Step 2: Add `HasApiTokens` to the user model**

In `app/User.php` add `use Laravel\Sanctum\HasApiTokens;` to the imports and `HasApiTokens` to the `use Notifiable, UserRoles;` line → `use HasApiTokens, Notifiable, UserRoles;`.

- [ ] **Step 3: Module files**

`Modules/MobileApi/module.json`:
```json
{
    "name": "MobileApi",
    "alias": "mobileapi",
    "description": "JSON API for the ARE mobile app",
    "keywords": [],
    "priority": 0,
    "providers": ["Modules\\MobileApi\\Providers\\MobileApiServiceProvider"],
    "files": []
}
```
`Modules/MobileApi/composer.json`:
```json
{
    "name": "nwidart/mobileapi",
    "description": "",
    "autoload": { "psr-4": { "Modules\\MobileApi\\": "" } }
}
```
`Modules/MobileApi/Config/config.php`:
```php
<?php
return [
    'name' => 'MobileApi',
    'enabled' => env('MOBILE_API_ENABLED', false),
    'signed_url_minutes' => 15,
    'max_upload_kb' => 10240,
    'image_max_px' => 1600,
    'thumb_px' => 300,
];
```
Add `"MobileApi": true` to `modules_statuses.json`. Add `MOBILE_API_ENABLED=true` to `.env` and `MOBILE_API_ENABLED=false` to `.env.example`.

`Modules/MobileApi/Providers/MobileApiServiceProvider.php`:
```php
<?php

namespace Modules\MobileApi\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MobileApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'mobileapi');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'mobileapi');

        Route::aliasMiddleware('mobile.enabled', \Modules\MobileApi\Http\Middleware\EnsureMobileApiEnabled::class);
        Route::aliasMiddleware('mobile.device', \Modules\MobileApi\Http\Middleware\EnsureRegisteredDevice::class);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
```
`Modules/MobileApi/Providers/RouteServiceProvider.php`:
```php
<?php

namespace Modules\MobileApi\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Modules\MobileApi\Http\Controllers';

    public function map(): void
    {
        Route::prefix('api/v1')
            ->middleware(['api', 'mobile.enabled'])
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/api.php');

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/web.php');
    }
}
```
`Modules/MobileApi/Routes/api.php` (grows in later tasks):
```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('ping', fn () => response()->json(['pong' => true, 'time' => now()->toIso8601String()]))->name('api.ping');
```
`Modules/MobileApi/Routes/web.php`: `<?php` only for now.

`Modules/MobileApi/Http/Middleware/EnsureMobileApiEnabled.php`:
```php
<?php

namespace Modules\MobileApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMobileApiEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('mobileapi.enabled')) {
            return response()->json(['message' => 'Mobile API disabled', 'code' => 'disabled'], 503);
        }
        return $next($request);
    }
}
```
`Modules/MobileApi/Http/Controllers/ApiController.php`:
```php
<?php

namespace Modules\MobileApi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

abstract class ApiController extends Controller
{
    protected function error(string $code, string $message, int $status, array $errors = []): JsonResponse
    {
        $body = ['message' => $message, 'code' => $code];
        if ($errors) {
            $body['errors'] = $errors;
        }
        return response()->json($body, $status);
    }
}
```
`Modules/MobileApi/Exceptions/ApiExceptionRenderer.php` — maps exceptions for `/api/v1/*` requests:
```php
<?php

namespace Modules\MobileApi\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    public static function wants(Request $request): bool
    {
        return $request->is('api/v1/*') || $request->is('api/v1');
    }

    public static function render(Request $request, Throwable $e): ?JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException => response()->json(['message' => 'The given data was invalid.', 'code' => 'validation', 'errors' => $e->errors()], 422),
            $e instanceof AuthenticationException => response()->json(['message' => 'Unauthenticated.', 'code' => 'unauthenticated'], 401),
            $e instanceof AuthorizationException => response()->json(['message' => $e->getMessage() ?: 'Forbidden.', 'code' => 'forbidden'], 403),
            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => response()->json(['message' => 'Not found.', 'code' => 'not_found'], 404),
            $e instanceof \DomainException => response()->json(['message' => $e->getMessage(), 'code' => method_exists($e, 'code') ? $e->code() : 'invalid_state'], 409),
            default => null,
        };
    }
}
```
In `app/Exceptions/Handler.php` add at the top of `render()`:
```php
        if (\Modules\MobileApi\Exceptions\ApiExceptionRenderer::wants($request)) {
            if ($json = \Modules\MobileApi\Exceptions\ApiExceptionRenderer::render($request, $e)) {
                return $json;
            }
            if (!config('app.debug')) {
                return response()->json(['message' => 'Server error.', 'code' => 'server_error'], 500);
            }
        }
```

- [ ] **Step 4: Test base class and first test**

`Modules/MobileApi/Tests/TestCase.php`:
```php
<?php

namespace Modules\MobileApi\Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Modules\MobileApi\Entities\MobileDevice;
use Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected const DEVICE = 'test-device-0001';

    protected function setUp(): void
    {
        parent::setUp();
        config(['mobileapi.enabled' => true]);
    }

    /** An existing user with role `are` and at least one building (the DB copy has 26). */
    protected function areUser(): User
    {
        return User::role('are')->whereHas('areBuildings')->firstOrFail();
    }

    /** Authenticate as $user through Sanctum, with a registered device header. */
    protected function actingAsAre(?User $user = null): User
    {
        $user ??= $this->areUser();
        MobileDevice::query()->updateOrCreate(['user_id' => $user->id], [
            'device_uid' => self::DEVICE, 'device_name' => 'Test', 'platform' => 'android',
            'app_version' => '0.0.1', 'registered_at' => now(), 'last_seen_at' => now(),
        ]);
        Sanctum::actingAs($user, ['*'], 'sanctum');
        $this->withHeaders(['Accept' => 'application/json', 'X-Device-Id' => self::DEVICE]);
        return $user;
    }
}
```
(`areBuildings` relation is added to `User` in Task 2.)

`Modules/MobileApi/Tests/Feature/PingTest.php`:
```php
<?php

namespace Modules\MobileApi\Tests\Feature;

use Modules\MobileApi\Tests\TestCase;

class PingTest extends TestCase
{
    public function test_ping_when_enabled(): void
    {
        $this->getJson('/api/v1/ping')->assertOk()->assertJson(['pong' => true]);
    }

    public function test_disabled_flag_returns_503(): void
    {
        config(['mobileapi.enabled' => false]);
        $this->getJson('/api/v1/ping')->assertStatus(503)->assertJson(['code' => 'disabled']);
    }
}
```
Add to `phpunit.xml` inside `<testsuites>`:
```xml
    <testsuite name="MobileApi">
      <directory suffix="Test.php">./Modules/MobileApi/Tests</directory>
    </testsuite>
```

- [ ] **Step 5: Run — expect failure first (module not yet discovered), then pass**

```bash
composer dump-autoload 2>&1 | tail -1
php artisan module:list 2>&1 | grep -i mobileapi
vendor/bin/phpunit --testsuite MobileApi 2>&1 | grep -E "^(OK|FAIL|Tests:)"
```
Expected: `[Enabled] MobileApi`; `OK (2 tests, 4 assertions)`. (The `areBuildings` relation is only needed by `actingAsAre`, which these tests don't call.)

- [ ] **Step 6: Commit**

```bash
git add Modules/MobileApi modules_statuses.json app/User.php app/Exceptions/Handler.php composer.json composer.lock config/sanctum.php database/migrations phpunit.xml .env.example
git commit -m "feat(mobile-api): module skeleton, sanctum, feature flag, error shape

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Building scope — `AreBuildings::forUser()`

**Files:**
- Create: `Modules/Masters/Services/AreBuildings.php`, `tests/Unit/AreBuildingsTest.php`
- Modify: `app/User.php` (add `areBuildings()` relation)

**Interfaces:**
- Produces: `AreBuildings::forUser(User $user, ?Carbon $on = null): \Illuminate\Support\Collection` of building ids (ints, unique); `AreBuildings::covers(User $user, int $buildingId): bool`.
- Produces: `User::areBuildings(): HasMany` → `Modules\Masters\Entities\AreBuildingAssign`.

- [ ] **Step 1: Failing test**

`tests/Unit/AreBuildingsTest.php`:
```php
<?php

namespace Tests\Unit;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Masters\Entities\AreBuildingAssign;
use Modules\Masters\Services\AreBuildings;
use Tests\TestCase;

class AreBuildingsTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAssignment(User $user, array $buildingIds, ?string $from, ?string $to): AreBuildingAssign
    {
        $a = AreBuildingAssign::create(['user_id' => $user->id, 'building_id' => $buildingIds[0], 'assign_from' => $from, 'assign_too' => $to]);
        foreach ($buildingIds as $b) {
            $a->buildingNames()->attach($b, ['assign_from' => $from, 'assign_to' => $to]);
        }
        return $a;
    }

    public function test_returns_buildings_from_current_assignments_only(): void
    {
        $user = User::role('are')->firstOrFail();
        AreBuildingAssign::where('user_id', $user->id)->get()->each(fn ($a) => $a->buildingNames()->detach());
        AreBuildingAssign::where('user_id', $user->id)->delete();

        $this->makeAssignment($user, [1, 2], '2020-01-01', null);              // open-ended
        $this->makeAssignment($user, [3], '2020-01-01', '2020-12-31');          // expired
        $this->makeAssignment($user, [2, 4], '2099-01-01', null);              // future

        $ids = AreBuildings::forUser($user, Carbon::parse('2026-09-19'));

        $this->assertSame([1, 2], $ids->sort()->values()->all());
        $this->assertTrue(AreBuildings::covers($user, 1));
        $this->assertFalse(AreBuildings::covers($user, 3));
    }
}
```

- [ ] **Step 2: Run to see it fail**

Run: `vendor/bin/phpunit tests/Unit/AreBuildingsTest.php`
Expected: FAIL — `Class "Modules\Masters\Services\AreBuildings" not found`.

- [ ] **Step 3: Implement**

`Modules/Masters/Services/AreBuildings.php`:
```php
<?php

namespace Modules\Masters\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The buildings an ARE is responsible for on a given day:
 * are_buildings (user, assign_from, assign_too) x preferred_buildings (building_id).
 * A null end date means open-ended.
 */
class AreBuildings
{
    public static function forUser(User $user, ?Carbon $on = null): Collection
    {
        $on = ($on ?? Carbon::today())->toDateString();

        return DB::table('are_buildings as ab')
            ->join('preferred_buildings as pb', 'pb.are_building_id', '=', 'ab.id')
            ->where('ab.user_id', $user->id)
            ->where(fn ($q) => $q->whereNull('ab.assign_from')->orWhereDate('ab.assign_from', '<=', $on))
            ->where(fn ($q) => $q->whereNull('ab.assign_too')->orWhereDate('ab.assign_too', '>=', $on))
            ->pluck('pb.building_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    public static function covers(User $user, int $buildingId, ?Carbon $on = null): bool
    {
        return self::forUser($user, $on)->contains($buildingId);
    }
}
```
In `app/User.php` add:
```php
    public function areBuildings()
    {
        return $this->hasMany(\Modules\Masters\Entities\AreBuildingAssign::class, 'user_id');
    }
```

- [ ] **Step 4: Run to see it pass**

Run: `vendor/bin/phpunit tests/Unit/AreBuildingsTest.php`
Expected: `OK (1 test, 3 assertions)`.

- [ ] **Step 5: Commit**

```bash
git add Modules/Masters/Services/AreBuildings.php tests/Unit/AreBuildingsTest.php app/User.php
git commit -m "feat(masters): AreBuildings — single source of an ARE's date-valid building scope

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 3: Device policy, login / logout / me

**Files:**
- Create: `Modules/MobileApi/Database/Migrations/2026_09_19_000001_create_mobile_devices_table.php`, `…000002_add_mobile_api_permissions.php`, `Modules/MobileApi/Entities/MobileDevice.php`, `Modules/MobileApi/Services/DevicePolicy.php`, `Modules/MobileApi/Exceptions/DeviceException.php`, `Modules/MobileApi/Http/Middleware/EnsureRegisteredDevice.php`, `Modules/MobileApi/Http/Requests/LoginRequest.php`, `Modules/MobileApi/Http/Controllers/AuthController.php`, `Modules/MobileApi/Http/Resources/UserResource.php`, `Modules/MobileApi/Http/Resources/BuildingResource.php`, `Modules/MobileApi/Tests/Feature/AuthTest.php`
- Modify: `Modules/MobileApi/Routes/api.php`

**Interfaces:**
- Produces: `DevicePolicy::authorise(User $user, string $deviceUid, array $meta = []): MobileDevice` (throws `DeviceException` with `code()` = `device_not_registered` | `device_blocked`).
- Produces: routes `POST api/v1/auth/login`, `POST api/v1/auth/logout`, `GET api/v1/auth/me`; the `auth:sanctum` + `mobile.device` group that all later tasks append routes to.

- [ ] **Step 1: Migrations**

`2026_09_19_000001_create_mobile_devices_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_devices', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->unique();     // one device per user (spec: first login binds)
            $t->string('device_uid', 191)->index();
            $t->string('device_name')->nullable();
            $t->string('platform', 20)->nullable();
            $t->string('app_version', 20)->nullable();
            $t->timestamp('registered_at');
            $t->timestamp('last_seen_at')->nullable();
            $t->timestamp('blocked_at')->nullable();
            $t->unsignedBigInteger('blocked_by')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_devices');
    }
};
```
`2026_09_19_000002_add_mobile_api_permissions.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const PERMS = [
        'mobile_app_login'      => ['are', 'are_team_lead'],
        'mobile_devices_manage' => ['super_admin', 'backoffice_manager'],
        'onboarding_items_manage' => ['super_admin', 'backoffice_manager'],
        'onboarding_view'       => ['super_admin', 'backoffice_manager', 'backoffice_executive', 'are', 'are_team_lead'],
        'onboarding_verify'     => ['super_admin', 'backoffice_manager', 'backoffice_executive'],
    ];

    public function up(): void
    {
        foreach (self::PERMS as $name => $roles) {
            $pid = DB::table('permissions')->where('name', $name)->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
            foreach (DB::table('roles')->whereIn('name', $roles)->pluck('id') as $rid) {
                DB::table('role_has_permissions')->updateOrInsert(['permission_id' => $pid, 'role_id' => $rid]);
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', array_keys(self::PERMS))->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        app('cache')->forget('spatie.permission.cache');
    }
};
```
Run: `php artisan migrate --force 2>&1 | tail -3` → both DONE.

- [ ] **Step 2: Failing tests**

`Modules/MobileApi/Tests/Feature/AuthTest.php`:
```php
<?php

namespace Modules\MobileApi\Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Hash;
use Modules\MobileApi\Entities\MobileDevice;
use Modules\MobileApi\Tests\TestCase;

class AuthTest extends TestCase
{
    private function areWithPassword(): User
    {
        $user = $this->areUser();
        $user->password = Hash::make('secret123');
        $user->save();
        MobileDevice::where('user_id', $user->id)->delete();
        return $user;
    }

    private function login(User $user, string $device = 'dev-A'): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/v1/auth/login', [
            'username' => $user->username, 'password' => 'secret123',
            'device_uid' => $device, 'device_name' => 'Pixel', 'platform' => 'android', 'app_version' => '1.0.0',
        ]);
    }

    public function test_first_login_binds_device_and_returns_token(): void
    {
        $user = $this->areWithPassword();
        $this->login($user)->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'username'], 'buildings']);
        $this->assertDatabaseHas('mobile_devices', ['user_id' => $user->id, 'device_uid' => 'dev-A']);
    }

    public function test_other_device_is_refused(): void
    {
        $user = $this->areWithPassword();
        $this->login($user, 'dev-A')->assertOk();
        $this->login($user, 'dev-B')->assertStatus(403)->assertJson([
            'code' => 'device_not_registered',
            'message' => 'This device is not registered. Please contact the administrator.',
        ]);
        $this->assertDatabaseMissing('mobile_devices', ['device_uid' => 'dev-B']);
    }

    public function test_blocked_device_is_refused(): void
    {
        $user = $this->areWithPassword();
        $this->login($user, 'dev-A')->assertOk();
        MobileDevice::where('user_id', $user->id)->update(['blocked_at' => now()]);
        $this->login($user, 'dev-A')->assertStatus(403)->assertJson(['code' => 'device_blocked']);
    }

    public function test_wrong_password_and_missing_permission(): void
    {
        $user = $this->areWithPassword();
        $this->postJson('/api/v1/auth/login', ['username' => $user->username, 'password' => 'nope', 'device_uid' => 'dev-A'])
            ->assertStatus(422)->assertJson(['code' => 'validation']);

        $clerk = User::role('call_center')->firstOrFail();
        $clerk->password = Hash::make('secret123'); $clerk->save();
        $this->login($clerk)->assertStatus(403)->assertJson(['code' => 'forbidden']);
    }

    public function test_me_requires_matching_device_header_and_logout_revokes(): void
    {
        $user = $this->areWithPassword();
        $token = $this->login($user, 'dev-A')->json('token');
        $h = ['Authorization' => "Bearer $token", 'Accept' => 'application/json'];

        $this->withHeaders($h + ['X-Device-Id' => 'dev-A'])->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('user.id', $user->id);
        $this->withHeaders($h + ['X-Device-Id' => 'dev-B'])->getJson('/api/v1/auth/me')->assertStatus(403)->assertJson(['code' => 'device_not_registered']);

        $this->withHeaders($h + ['X-Device-Id' => 'dev-A'])->postJson('/api/v1/auth/logout')->assertOk();
        $this->withHeaders($h + ['X-Device-Id' => 'dev-A'])->getJson('/api/v1/auth/me')->assertStatus(401);
    }
}
```

- [ ] **Step 3: Run to see them fail**

Run: `vendor/bin/phpunit --testsuite MobileApi --filter AuthTest`
Expected: failures — route not found (404).

- [ ] **Step 4: Implement**

`Modules/MobileApi/Entities/MobileDevice.php`:
```php
<?php

namespace Modules\MobileApi\Entities;

use Illuminate\Database\Eloquent\Model;

class MobileDevice extends Model
{
    use \App\Concerns\LegacyDateSerialization;

    protected $guarded = [];
    protected $casts = ['registered_at' => 'datetime', 'last_seen_at' => 'datetime', 'blocked_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }
}
```
`Modules/MobileApi/Exceptions/DeviceException.php`:
```php
<?php

namespace Modules\MobileApi\Exceptions;

class DeviceException extends \RuntimeException
{
    public const NOT_REGISTERED = 'device_not_registered';
    public const BLOCKED = 'device_blocked';

    public function __construct(private readonly string $code_)
    {
        parent::__construct(match ($code_) {
            self::BLOCKED => 'This device has been blocked. Please contact the administrator.',
            default => 'This device is not registered. Please contact the administrator.',
        });
    }

    public function code(): string
    {
        return $this->code_;
    }
}
```
`Modules/MobileApi/Services/DevicePolicy.php`:
```php
<?php

namespace Modules\MobileApi\Services;

use App\User;
use Modules\MobileApi\Entities\MobileDevice;
use Modules\MobileApi\Exceptions\DeviceException;

/**
 * One device per user. The first login binds the device; any other device
 * is refused until an administrator clears the row (office screen).
 */
class DevicePolicy
{
    /** @param array{device_name?:string,platform?:string,app_version?:string} $meta */
    public static function authorise(User $user, string $deviceUid, array $meta = []): MobileDevice
    {
        $device = MobileDevice::where('user_id', $user->id)->first();

        if ($device === null) {
            return MobileDevice::create([
                'user_id' => $user->id, 'device_uid' => $deviceUid,
                'device_name' => $meta['device_name'] ?? null, 'platform' => $meta['platform'] ?? null,
                'app_version' => $meta['app_version'] ?? null,
                'registered_at' => now(), 'last_seen_at' => now(),
            ]);
        }

        if (!hash_equals($device->device_uid, $deviceUid)) {
            throw new DeviceException(DeviceException::NOT_REGISTERED);
        }
        if ($device->isBlocked()) {
            throw new DeviceException(DeviceException::BLOCKED);
        }

        $device->forceFill(array_filter([
            'last_seen_at' => now(),
            'app_version' => $meta['app_version'] ?? null,
            'device_name' => $meta['device_name'] ?? null,
        ]))->save();

        return $device;
    }

    /** Office action: forget the device and every token it held. */
    public static function clear(User $user): void
    {
        MobileDevice::where('user_id', $user->id)->delete();
        $user->tokens()->delete();
    }

    public static function block(User $user, User $by): void
    {
        MobileDevice::where('user_id', $user->id)->update(['blocked_at' => now(), 'blocked_by' => $by->id]);
        $user->tokens()->delete();
    }

    public static function unblock(User $user): void
    {
        MobileDevice::where('user_id', $user->id)->update(['blocked_at' => null, 'blocked_by' => null]);
    }
}
```
`Modules/MobileApi/Http/Middleware/EnsureRegisteredDevice.php`:
```php
<?php

namespace Modules\MobileApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\MobileApi\Exceptions\DeviceException;
use Modules\MobileApi\Services\DevicePolicy;

class EnsureRegisteredDevice
{
    public function handle(Request $request, Closure $next)
    {
        $uid = (string) $request->header('X-Device-Id', '');
        if ($uid === '' || !$request->user()) {
            return response()->json(['message' => 'Device id missing.', 'code' => 'device_not_registered'], 403);
        }
        try {
            $device = DevicePolicy::authorise($request->user(), $uid, ['app_version' => $request->header('X-App-Version')]);
        } catch (DeviceException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => $e->code()], 403);
        }
        $request->attributes->set('mobile_device', $device);
        return $next($request);
    }
}
```
`Modules/MobileApi/Http/Requests/LoginRequest.php`:
```php
<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'username' => 'required|string', 'password' => 'required|string',
            'device_uid' => 'required|string|max:191', 'device_name' => 'nullable|string|max:100',
            'platform' => 'nullable|in:android,ios', 'app_version' => 'nullable|string|max:20',
        ];
    }
}
```
`Modules/MobileApi/Http/Resources/BuildingResource.php`:
```php
<?php

namespace Modules\MobileApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'code' => $this->building_code, 'name' => $this->building_name];
    }
}
```
`Modules/MobileApi/Http/Resources/UserResource.php`:
```php
<?php

namespace Modules\MobileApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'username' => $this->username, 'email' => $this->email,
            'name' => optional($this->employee)->employee_name ?: $this->username,
            'mobile' => optional($this->employee)->employee_contact_no,
            'roles' => $this->getRoleNames()->values(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
        ];
    }
}
```
`Modules/MobileApi/Http/Controllers/AuthController.php`:
```php
<?php

namespace Modules\MobileApi\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Masters\Entities\Building;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Exceptions\DeviceException;
use Modules\MobileApi\Http\Requests\LoginRequest;
use Modules\MobileApi\Http\Resources\BuildingResource;
use Modules\MobileApi\Http\Resources\UserResource;
use Modules\MobileApi\Services\DevicePolicy;

class AuthController extends ApiController
{
    public function login(LoginRequest $request)
    {
        if (!Auth::validate(['username' => $request->username, 'password' => $request->password])) {
            throw ValidationException::withMessages(['username' => ['Invalid username or password.']]);
        }
        /** @var User $user */
        $user = User::where('username', $request->username)->firstOrFail();

        if (!$user->can('mobile_app_login')) {
            return $this->error('forbidden', 'Your account is not enabled for the mobile app.', 403);
        }

        try {
            $device = DevicePolicy::authorise($user, $request->device_uid, $request->only('device_name', 'platform', 'app_version'));
        } catch (DeviceException $e) {
            return $this->error($e->code(), $e->getMessage(), 403);
        }

        $user->tokens()->where('name', $device->device_uid)->delete();
        $token = $user->createToken($device->device_uid)->plainTextToken; // no expiry

        activity('mobile')->causedBy($user)->withProperties(['device_uid' => $device->device_uid, 'app_version' => $device->app_version])->log('login');

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
            'buildings' => BuildingResource::collection(Building::whereIn('id', AreBuildings::forUser($user))->orderBy('building_name')->get()),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => new UserResource($user),
            'buildings' => BuildingResource::collection(Building::whereIn('id', AreBuildings::forUser($user))->orderBy('building_name')->get()),
            'device' => $request->attributes->get('mobile_device')?->only('device_uid', 'device_name', 'platform', 'app_version', 'registered_at'),
        ]);
    }
}
```
Replace `Modules/MobileApi/Routes/api.php` with:
```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('ping', fn () => response()->json(['pong' => true, 'time' => now()->toIso8601String()]))->name('api.ping');

Route::post('auth/login', 'AuthController@login')->name('api.auth.login');

Route::middleware(['auth:sanctum', 'mobile.device'])->group(function () {
    Route::post('auth/logout', 'AuthController@logout')->name('api.auth.logout');
    Route::get('auth/me', 'AuthController@me')->name('api.auth.me');
});
```

- [ ] **Step 5: Run to see them pass**

Run: `vendor/bin/phpunit --testsuite MobileApi`
Expected: `OK (7 tests, …)`. If `Auth::validate` fails for a user whose password is stored with an older hash, `hashing.rehash_on_login` is irrelevant here — `Hash::make` in the test writes bcrypt.

- [ ] **Step 6: Commit**

```bash
git add Modules/MobileApi
git commit -m "feat(mobile-api): login/logout/me with one-device-per-user policy

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 4: Office screen — Mobile devices (clear / block / unblock)

**Files:**
- Create: `Modules/MobileApi/Http/Controllers/MobileDeviceAdminController.php`, `Modules/MobileApi/Resources/views/devices/index.blade.php`, `Modules/MobileApi/Database/Migrations/2026_09_19_000003_add_mobile_devices_menu.php`, `Modules/MobileApi/Tests/Feature/DeviceAdminTest.php`
- Modify: `Modules/MobileApi/Routes/web.php`, `Modules/MobileApi/Routes/breadcrumbs.php` (create), `Modules/MobileApi/Providers/MobileApiServiceProvider.php` (require breadcrumbs)

**Interfaces:**
- Consumes: `DevicePolicy::clear/block/unblock` (Task 3).
- Produces: routes `mobile-devices.index` (GET `mobileDevices`), `mobile-devices.clear` (POST `mobileDevices/{user}/clear`), `mobile-devices.block`, `mobile-devices.unblock`.

- [ ] **Step 1: Failing test**

```php
<?php

namespace Modules\MobileApi\Tests\Feature;

use App\User;
use Modules\MobileApi\Entities\MobileDevice;
use Modules\MobileApi\Tests\TestCase;

class DeviceAdminTest extends TestCase
{
    public function test_admin_can_list_clear_and_block(): void
    {
        $are = $this->areUser();
        MobileDevice::updateOrCreate(['user_id' => $are->id], ['device_uid' => 'dev-Z', 'registered_at' => now()]);
        $are->createToken('dev-Z');
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)->get(route('mobile-devices.index'))->assertOk()->assertSee('dev-Z');

        $this->actingAs($admin)->post(route('mobile-devices.block', $are))->assertRedirect();
        $this->assertNotNull(MobileDevice::where('user_id', $are->id)->value('blocked_at'));
        $this->assertSame(0, $are->tokens()->count());

        $this->actingAs($admin)->post(route('mobile-devices.clear', $are))->assertRedirect();
        $this->assertDatabaseMissing('mobile_devices', ['user_id' => $are->id]);
    }

    public function test_requires_permission(): void
    {
        $this->actingAs($this->areUser())->get(route('mobile-devices.index'))->assertStatus(403);
    }
}
```
Run: `vendor/bin/phpunit --testsuite MobileApi --filter DeviceAdminTest` → FAIL (route not defined).

- [ ] **Step 2: Implement**

`Modules/MobileApi/Routes/web.php`:
```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:mobile_devices_manage'])->group(function () {
    Route::get('mobileDevices', 'MobileDeviceAdminController@index')->name('mobile-devices.index');
    Route::post('mobileDevices/{user}/clear', 'MobileDeviceAdminController@clear')->name('mobile-devices.clear');
    Route::post('mobileDevices/{user}/block', 'MobileDeviceAdminController@block')->name('mobile-devices.block');
    Route::post('mobileDevices/{user}/unblock', 'MobileDeviceAdminController@unblock')->name('mobile-devices.unblock');
});
```
`Modules/MobileApi/Routes/breadcrumbs.php`:
```php
<?php

Breadcrumbs::for('mobile-devices.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Mobile Devices', route('mobile-devices.index'));
});
```
In `MobileApiServiceProvider::boot()` add `require __DIR__ . '/../Routes/breadcrumbs.php';` after the view registration.

`MobileDeviceAdminController.php`:
```php
<?php

namespace Modules\MobileApi\Http\Controllers;

use App\User;
use Illuminate\Routing\Controller;
use Modules\MobileApi\Entities\MobileDevice;
use Modules\MobileApi\Services\DevicePolicy;

class MobileDeviceAdminController extends Controller
{
    public function index()
    {
        $devices = MobileDevice::with('user.employee')->orderByDesc('last_seen_at')->paginate(25);
        return view('mobileapi::devices.index', compact('devices'));
    }

    public function clear(User $user)
    {
        DevicePolicy::clear($user);
        activity('mobile')->causedBy(auth()->user())->performedOn($user)->log('device cleared');
        return redirect()->route('mobile-devices.index')->with('success', 'Device cleared. The next login will register a new device.');
    }

    public function block(User $user)
    {
        DevicePolicy::block($user, auth()->user());
        activity('mobile')->causedBy(auth()->user())->performedOn($user)->log('device blocked');
        return redirect()->route('mobile-devices.index')->with('success', 'Device blocked.');
    }

    public function unblock(User $user)
    {
        DevicePolicy::unblock($user);
        activity('mobile')->causedBy(auth()->user())->performedOn($user)->log('device unblocked');
        return redirect()->route('mobile-devices.index')->with('success', 'Device unblocked.');
    }
}
```
`Resources/views/devices/index.blade.php` (uses the standard list layout — copy the table markup from `Modules/BackOffice/Resources/views/TerminationDues/index.blade.php`, which is the current "standard list layout"):
```blade
@extends('layouts.plms-app')
@section('title', 'Mobile Devices')
@section('content')
<div class="page-content-wrapper"><div class="page-content">
  {{ Breadcrumbs::render('mobile-devices.index') }}
  @include('includes.success') @include('includes.error')
  <div class="card card-box"><div class="card-head"><header>Mobile Devices</header></div>
  <div class="card-body">
    <table class="table table-striped table-hover">
      <thead><tr><th>User</th><th>Employee</th><th>Device</th><th>Platform</th><th>App</th><th>Registered</th><th>Last seen</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      @forelse($devices as $d)
        <tr>
          <td>{{ $d->user->username }}</td>
          <td>{{ optional($d->user->employee)->employee_name }}</td>
          <td>{{ $d->device_name }} <small class="text-muted">{{ $d->device_uid }}</small></td>
          <td>{{ $d->platform }}</td><td>{{ $d->app_version }}</td>
          <td>{{ $d->registered_at?->format('d/m/Y H:i') }}</td>
          <td>{{ $d->last_seen_at?->format('d/m/Y H:i') }}</td>
          <td>@if($d->blocked_at)<span class="label label-danger">Blocked</span>@else<span class="label label-success">Active</span>@endif</td>
          <td>
            <form method="post" action="{{ route('mobile-devices.clear', $d->user_id) }}" class="d-inline" onsubmit="return confirm('Clear this device? The user must log in again from the new phone.')">@csrf<button class="btn btn-xs btn-warning">Clear</button></form>
            @if($d->blocked_at)
              <form method="post" action="{{ route('mobile-devices.unblock', $d->user_id) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Unblock</button></form>
            @else
              <form method="post" action="{{ route('mobile-devices.block', $d->user_id) }}" class="d-inline">@csrf<button class="btn btn-xs btn-danger">Block</button></form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="9" class="text-center">No devices registered yet.</td></tr>
      @endforelse
      </tbody>
    </table>
    {{ $devices->links() }}
  </div></div>
</div></div>
@endsection
```
Menu migration `2026_09_19_000003_add_mobile_devices_menu.php` — same pattern as `database/migrations/2026_09_17_000002_add_termination_dues_menu.php`: find `PLM Module` → `Settings`-like group; if there is no such group insert under `PLM Module` root with `menutype 2`, `menu_name 'Mobile Devices'`, `route_name 'mobile-devices.index'`, `url_key 'mobileDevices'`, `menu_icon 'fa-mobile'`, and attach permission `mobile_devices_manage` to it via `permissions.menu_id`.

- [ ] **Step 3: Run tests, migrate, commit**

```bash
php artisan migrate --force 2>&1 | tail -1
vendor/bin/phpunit --testsuite MobileApi 2>&1 | grep -E "^(OK|FAIL|Tests:)"
git add Modules/MobileApi
git commit -m "feat(mobile-api): office screen to clear/block registered devices

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

## Part B — Complaints

### Task 5: `TicketAttendance` service — claim & check-in

**Files:**
- Create: `Modules/Maintenance/Services/TicketAttendance.php`, `Modules/Maintenance/Exceptions/InvalidStateException.php`, `Modules/Maintenance/Exceptions/NotAssignedException.php`, `Modules/Maintenance/Tests/Unit/TicketAttendanceTest.php`
- Modify: `Modules/Maintenance/Http/Controllers/ComplaintStageController.php:1402-1457` (`checkIn` delegates), `phpunit.xml` (add `./Modules/Maintenance/Tests` to the Unit suite)

**Interfaces:**
- Produces:
  - `TicketAttendance::unassignedTickets(User $are, ?int $buildingId = null, ?string $q = null): Builder` — `ComplaintChecklist` rows, `work_flow_processes_code IN (701,702)`, `sub_assigned_to NULL`, complaint building in `AreBuildings::forUser`.
  - `TicketAttendance::myTickets(User $are, string $tab /* mine|attended|completed */, ?int $buildingId = null, ?string $q = null): Builder`.
  - `TicketAttendance::claim(ComplaintChecklist $ticket, User $are): ComplaintChecklist` — throws `InvalidStateException` if already sub-assigned or outside scope.
  - `TicketAttendance::checkIn(ComplaintChecklist $ticket, User $are, ?float $lat, ?float $lng): ComplaintServiceReport` — idempotent.
  - `TicketAttendance::reportFor(ComplaintChecklist $ticket): ?ComplaintServiceReport`.
  - Exceptions: `InvalidStateException extends \DomainException` with `code(): string` = `invalid_state`; `NotAssignedException` → `not_assigned`.

- [ ] **Step 1: Failing test**

`Modules/Maintenance/Tests/Unit/TicketAttendanceTest.php`:
```php
<?php

namespace Modules\Maintenance\Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Maintenance\Services\TicketAttendance;
use Modules\Masters\Services\AreBuildings;
use Tests\TestCase;

class TicketAttendanceTest extends TestCase
{
    use DatabaseTransactions;

    private function are(): User { return User::role('are')->whereHas('areBuildings')->firstOrFail(); }

    /** A fresh unassigned ticket on a building the ARE covers. */
    private function unassignedTicket(User $are): ComplaintChecklist
    {
        $buildingId = AreBuildings::forUser($are)->first();
        $enq = ComplaintEnquiry::create([
            'complaint_date' => now(), 'complaint_no' => 'T-' . uniqid(), 'complainer_name' => 'Test',
            'complaint_mob_no' => '99999999', 'building_id' => $buildingId, 'priority_status' => 1,
            'complaint_status' => 0, 'created_by' => 1,
        ]);
        return ComplaintChecklist::create([
            'work_id' => 1, 'complaint_enquiries_id' => $enq->id, 'checklist_desc' => 'Tap leaking',
            'complaint_ticket_no' => 'TK-' . uniqid(), 'work_flow_processes_code' => 701, 'ticket_status' => 0, 'created_by' => 1,
        ]);
    }

    public function test_unassigned_list_is_scoped_and_claim_moves_to_703(): void
    {
        $are = $this->are();
        $ticket = $this->unassignedTicket($are);

        $this->assertTrue(TicketAttendance::unassignedTickets($are)->pluck('id')->contains($ticket->id));

        $claimed = TicketAttendance::claim($ticket, $are);
        $this->assertSame(703, (int) $claimed->work_flow_processes_code);
        $this->assertSame($are->id, (int) $claimed->sub_assigned_to);
        $this->assertDatabaseHas('complaint_processes', ['complaint_checklists_id' => $ticket->id, 'work_flow_processes_code' => 703, 'sub_assigned_to' => $are->id]);
        $this->assertTrue(TicketAttendance::myTickets($are, 'mine')->pluck('id')->contains($ticket->id));
        $this->assertFalse(TicketAttendance::unassignedTickets($are)->pluck('id')->contains($ticket->id));
    }

    public function test_claim_twice_is_a_conflict(): void
    {
        $are = $this->are();
        $ticket = TicketAttendance::claim($this->unassignedTicket($are), $are);
        $this->expectException(InvalidStateException::class);
        TicketAttendance::claim($ticket->fresh(), $are);
    }

    public function test_check_in_creates_one_service_report(): void
    {
        $are = $this->are();
        $ticket = TicketAttendance::claim($this->unassignedTicket($are), $are);
        $report = TicketAttendance::checkIn($ticket, $are, 23.58, 58.38);
        $again = TicketAttendance::checkIn($ticket->fresh(), $are, null, null);

        $this->assertSame($report->id, $again->id);
        $this->assertSame(1, (int) $report->complaint_assign_status);
        $this->assertSame(1, (int) $ticket->fresh()->ticket_status);
        $this->assertDatabaseHas('complaint_service_report_checklist', ['checklist_id' => $ticket->id, 'complaint_service_report_id' => $report->id]);
        $this->assertDatabaseHas('complaint_service_report_notes', ['complaint_service_report_id' => $report->id, 'stage' => 'checkin']);
    }
}
```
Add `<directory suffix="Test.php">./Modules/Maintenance/Tests</directory>` to the `Unit` testsuite in `phpunit.xml`.

- [ ] **Step 2: Run to see it fail**

Run: `vendor/bin/phpunit --filter TicketAttendanceTest`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement**

`Modules/Maintenance/Exceptions/InvalidStateException.php`:
```php
<?php

namespace Modules\Maintenance\Exceptions;

class InvalidStateException extends \DomainException
{
    public function code(): string { return 'invalid_state'; }
}
```
`NotAssignedException.php` identical with `code(): string { return 'not_assigned'; }`.

`Modules/Maintenance/Services/TicketAttendance.php`:
```php
<?php

namespace Modules\Maintenance\Services;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Maintenance\Entities\ComplaintProcess;
use Modules\Maintenance\Entities\ComplaintServiceReport;
use Modules\Maintenance\Entities\ComplaintServiceReportChecklist;
use Modules\Maintenance\Entities\ComplaintServiceReportNote;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Maintenance\Exceptions\NotAssignedException;
use Modules\Masters\Services\AreBuildings;

/**
 * A ticket (complaint_checklists row) being worked by one person.
 * Mirrors the desktop's sub-assign modal, Check In button and technician
 * service-report screen so both write identical rows.
 */
class TicketAttendance
{
    public const STAGE_UNASSIGNED = 701;
    public const STAGE_ASSIGNED = 702;
    public const STAGE_SUB_ASSIGNED = 703;
    public const STAGE_CLOSED = 705;

    public const STATUS_CHECKED_IN = 1;
    public const STATUS_ATTENDED = 2;
    public const STATUS_COMPLETED = 3;
    public const STATUS_CLOSED = 4;

    private static function base(User $are, ?int $buildingId, ?string $q): Builder
    {
        $buildings = AreBuildings::forUser($are);
        if ($buildingId !== null) {
            $buildings = $buildings->intersect([$buildingId]);
        }
        return ComplaintChecklist::query()
            ->with(['complaintEnquiry.building', 'complaintEnquiry.unit', 'complaintEnquiry.tenant', 'work'])
            ->whereHas('complaintEnquiry', function ($e) use ($buildings, $q) {
                $e->whereIn('building_id', $buildings->all());
                if ($q) {
                    $e->where(fn ($w) => $w->where('complaint_no', 'ilike', "%$q%")
                        ->orWhere('complainer_name', 'ilike', "%$q%")
                        ->orWhereHas('building', fn ($b) => $b->where('building_name', 'ilike', "%$q%")));
                }
            })
            ->orderByDesc('id');
    }

    public static function unassignedTickets(User $are, ?int $buildingId = null, ?string $q = null): Builder
    {
        return self::base($are, $buildingId, $q)
            ->whereIn('work_flow_processes_code', [self::STAGE_UNASSIGNED, self::STAGE_ASSIGNED])
            ->whereNull('sub_assigned_to');
    }

    public static function myTickets(User $are, string $tab, ?int $buildingId = null, ?string $q = null): Builder
    {
        $query = self::base($are, $buildingId, $q)->where('sub_assigned_to', $are->id);
        return match ($tab) {
            'mine' => $query->where('work_flow_processes_code', self::STAGE_SUB_ASSIGNED)->whereIn('ticket_status', [0, 1]),
            'attended' => $query->where('work_flow_processes_code', self::STAGE_SUB_ASSIGNED)->where('ticket_status', self::STATUS_ATTENDED),
            'completed' => $query->where(fn ($w) => $w->where('ticket_status', self::STATUS_COMPLETED)->orWhere('work_flow_processes_code', self::STAGE_CLOSED)),
            default => throw new \InvalidArgumentException("Unknown tab $tab"),
        };
    }

    public static function assertInScope(ComplaintChecklist $ticket, User $are): void
    {
        if (!AreBuildings::covers($are, (int) $ticket->complaintEnquiry->building_id)) {
            throw new NotAssignedException('This complaint is outside your assigned buildings.');
        }
    }

    public static function assertMine(ComplaintChecklist $ticket, User $are): void
    {
        if ((int) $ticket->sub_assigned_to !== $are->id) {
            throw new NotAssignedException('This ticket is not assigned to you.');
        }
    }

    /** Sub-assign the ticket to the ARE — the rows the coordinator's modal writes. */
    public static function claim(ComplaintChecklist $ticket, User $are): ComplaintChecklist
    {
        self::assertInScope($ticket, $are);
        if ($ticket->sub_assigned_to !== null || (int) $ticket->work_flow_processes_code >= self::STAGE_SUB_ASSIGNED) {
            throw new InvalidStateException('This ticket has already been assigned.');
        }

        return DB::transaction(function () use ($ticket, $are) {
            $previous = ComplaintProcess::where('complaint_checklists_id', $ticket->id)->orderByDesc('id')->first();
            if ($previous) {
                $previous->complaintUser()->update(['status' => 0]);
            }

            $process = new ComplaintProcess();
            $process->complaint_checklists_id = $ticket->id;
            $process->complaint_enquiries_id = $ticket->complaint_enquiries_id;
            $process->work_flow_processes_code = self::STAGE_SUB_ASSIGNED;
            $process->assigned_to = $ticket->assigned_to ?: $are->id;
            $process->sub_assigned_to = $are->id;
            $process->created_by = $are->id;
            $process->save();
            $process->complaintUsers()->attach($are->default_role, ['user_id' => $are->id]);

            $ticket->update([
                'work_flow_processes_code' => self::STAGE_SUB_ASSIGNED,
                'assigned_to' => $ticket->assigned_to ?: $are->id,
                'sub_assigned_to' => $are->id,
                'updated_by' => $are->id,
            ]);
            return $ticket->fresh();
        });
    }

    public static function reportFor(ComplaintChecklist $ticket): ?ComplaintServiceReport
    {
        $link = ComplaintServiceReportChecklist::where('checklist_id', $ticket->id)->orderByDesc('id')->first();
        return $link ? ComplaintServiceReport::find($link->complaint_service_report_id) : null;
    }

    /** Desktop "Check In": creates the service report once; records time/location as a note. */
    public static function checkIn(ComplaintChecklist $ticket, User $are, ?float $lat, ?float $lng): ComplaintServiceReport
    {
        self::assertMine($ticket, $are);
        if ($report = self::reportFor($ticket)) {
            return $report;
        }

        return DB::transaction(function () use ($ticket, $are, $lat, $lng) {
            $prefix = prefixData('service_report_prefix')->configuration_value;
            $last = ComplaintServiceReport::latest('id')->first();
            $no = $prefix . str_pad(($last->id ?? 0) + 1, 4, '0', STR_PAD_LEFT);

            $report = ComplaintServiceReport::create([
                'user_id' => $are->id, 'service_report_no' => $no,
                'complaint_assign_note' => $ticket->checklist_desc,
                'complaint_assign_status' => self::STATUS_CHECKED_IN, 'created_by' => $are->id,
            ]);
            ComplaintServiceReportChecklist::create(['checklist_id' => $ticket->id, 'complaint_service_report_id' => $report->id]);
            $ticket->update(['service_report_status' => 1, 'ticket_status' => self::STATUS_CHECKED_IN]);

            ComplaintServiceReportNote::create([
                'complaint_service_report_id' => $report->id, 'stage' => 'checkin',
                'desc' => 'Checked in at ' . now()->format('d/m/Y H:i') . ($lat !== null ? " ($lat, $lng)" : ''),
                'created_by' => $are->id,
            ]);
            return $report;
        });
    }
}
```
(`complaint_service_report_notes.stage` is a string column today holding the numeric status; `'checkin'` is a new marker value — check the column type with `\d complaint_service_report_notes`; if it is integer, use `0` and put the coordinates in `desc` only.)

- [ ] **Step 4: Desktop `checkIn` delegates**

Replace the body of `ComplaintStageController::checkIn($id, $sub_assign)` (lines 1402–1457) with:
```php
      $url = url()->previous();
      $tickets = ComplaintChecklist::where('complaint_enquiries_id', $id)
        ->where('sub_assigned_to', $sub_assign)
        ->where('work_flow_processes_code', '=', 703)
        ->whereIn('ticket_status', [0, 1, 3])->get();
      $user = \App\User::findOrFail($sub_assign);
      foreach ($tickets as $ticket) {
        \Modules\Maintenance\Services\TicketAttendance::checkIn($ticket, $user, null, null);
      }
      return redirect($url);
```
Note the behavioural difference: the desktop created **one** service report covering all of a technician's tickets on the complaint; the service creates one per ticket. `reportFor()` looks up by ticket, so both layouts read correctly. Keep this — the app works per ticket.

- [ ] **Step 5: Run tests; commit**

```bash
vendor/bin/phpunit --filter TicketAttendanceTest 2>&1 | grep -E "^(OK|FAIL|Tests:)"
git add Modules/Maintenance phpunit.xml
git commit -m "feat(maintenance): TicketAttendance service — scoped queues, claim, check-in; desktop check-in delegates

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 6: `TicketAttendance` — notes, photos, items, status, signature/close

**Files:**
- Modify: `Modules/Maintenance/Services/TicketAttendance.php`, `Modules/Maintenance/Tests/Unit/TicketAttendanceTest.php`
- Create: `Modules/MobileApi/Services/MobileImage.php`
- Modify: `Modules/Maintenance/Http/Controllers/ComplaintStageController.php` — `storeServiceReportNote` (962), `serviceReportStatusButtonUpdate` (1682), `generateServiceReport` (1781) delegate.

**Interfaces:**
- Produces:
  - `MobileImage::store(UploadedFile $file, string $dir): array{path:string, thumb:string}` — resizes to `mobileapi.image_max_px`, thumbnail `mobileapi.thumb_px`, stores under `storage/app/mobile/{dir}/`, returns disk-relative paths.
  - `MobileImage::storeSignature(string $dataUrl, string $dir): string`.
  - `TicketAttendance::addNote(ticket, are, string $text, ?string $quickReason): ComplaintServiceReportNote`
  - `TicketAttendance::addPhoto(ticket, are, string $path, string $thumb, string $stage): ComplaintServiceReportImage`
  - `TicketAttendance::addItem(ticket, are, int $inventoryId, float $qty): ComplaintServiceReportInv`; `removeItem(ticket, are, int $itemId): void`
  - `TicketAttendance::setStatus(ticket, are, string $status /* attended|completed|cannot_complete */, ?string $reason): ComplaintServiceReport` — `attended`→2, `completed`→3 (+ the coordinator/supervisor notifications the desktop sends on 2), `cannot_complete` → stays checked-in, adds note `Cannot complete: {reason}`.
  - `TicketAttendance::close(ticket, are, ?string $signatureDataUrl, bool $skipped): ComplaintServiceReport` — requires status 3; requires signature or (`skipped` and ≥1 `after` photo); stage 705, `ticket_status` 4, report status 3, `tenant_signature`, `complaint_status` 1 on the enquiry, coordinator notification event, tenant email/SMS — exactly `generateServiceReport` minus Session.
  - `TicketAttendance::pdfPath(ComplaintServiceReport $report): string` — renders `maintenance::ServiceReport.pdf` (the existing print view used by `technicianServiceReportClosed` — locate it with `grep -n "PDF::loadView" Modules/Maintenance/Http/Controllers/ComplaintStageController.php`) to `mobile/complaints/{ticket}/service-report.pdf` and caches it.

- [ ] **Step 1: Add failing tests** (append to `TicketAttendanceTest`)

```php
    public function test_full_attendance_flow_closes_ticket_with_signature(): void
    {
        $are = $this->are();
        $ticket = TicketAttendance::claim($this->unassignedTicket($are), $are);
        TicketAttendance::checkIn($ticket, $are, null, null);

        $note = TicketAttendance::addNote($ticket, $are, 'Replaced washer', null);
        $this->assertSame('Replaced washer', $note->desc);

        $photo = TicketAttendance::addPhoto($ticket, $are, 'mobile/complaints/x/a.jpg', 'mobile/complaints/x/a_t.jpg', 'after');
        $this->assertSame('after', $photo->stage);

        TicketAttendance::setStatus($ticket, $are, 'attended', null);
        $this->assertSame(2, (int) $ticket->fresh()->ticket_status);
        TicketAttendance::setStatus($ticket, $are, 'completed', null);
        $this->assertSame(3, (int) $ticket->fresh()->ticket_status);

        $png = 'data:image/png;base64,' . base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
        $report = TicketAttendance::close($ticket->fresh(), $are, $png, false);

        $this->assertSame(705, (int) $ticket->fresh()->work_flow_processes_code);
        $this->assertSame(4, (int) $ticket->fresh()->ticket_status);
        $this->assertSame(3, (int) $report->complaint_assign_status);
        $this->assertNotEmpty($report->tenant_signature);
        $this->assertSame(1, (int) $ticket->complaintEnquiry->fresh()->complaint_status);
    }

    public function test_close_requires_completed_status(): void
    {
        $are = $this->are();
        $ticket = TicketAttendance::claim($this->unassignedTicket($are), $are);
        TicketAttendance::checkIn($ticket, $are, null, null);
        $this->expectException(InvalidStateException::class);
        TicketAttendance::close($ticket->fresh(), $are, null, true);
    }
```
Run: `vendor/bin/phpunit --filter TicketAttendanceTest` → the two new tests FAIL (`addNote` undefined).

- [ ] **Step 2: `MobileImage`**

`Modules/MobileApi/Services/MobileImage.php`:
```php
<?php

namespace Modules\MobileApi\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MobileImage
{
    /** @return array{path:string,thumb:string} paths on the `local` disk */
    public static function store(UploadedFile $file, string $dir): array
    {
        $dir = 'mobile/' . trim($dir, '/');
        Storage::disk('local')->makeDirectory($dir);
        $base = Storage::disk('local')->path($dir);
        $name = now()->format('YmdHis') . '_' . Str::random(6);
        $max = (int) config('mobileapi.image_max_px', 1600);
        $thumb = (int) config('mobileapi.thumb_px', 300);

        Image::make($file->getRealPath())->orientate()
            ->resize($max, $max, fn ($c) => $c->aspectRatio()->upsize())
            ->save("$base/$name.jpg", 85);
        Image::make($file->getRealPath())->orientate()
            ->resize($thumb, $thumb, fn ($c) => $c->aspectRatio()->upsize())
            ->save("$base/{$name}_t.jpg", 80);

        return ['path' => "$dir/$name.jpg", 'thumb' => "$dir/{$name}_t.jpg"];
    }

    public static function storeSignature(string $dataUrl, string $dir): string
    {
        if (!preg_match('#^data:image/png;base64,(.+)$#', $dataUrl, $m)) {
            throw new \InvalidArgumentException('Signature must be a PNG data URL.');
        }
        $dir = 'mobile/' . trim($dir, '/');
        Storage::disk('local')->makeDirectory($dir);
        $path = "$dir/signature_" . now()->format('YmdHis') . '.png';
        Storage::disk('local')->put($path, base64_decode($m[1]));
        return $path;
    }
}
```

- [ ] **Step 3: Extend the service** (append inside the class)

```php
    public static function addNote(ComplaintChecklist $ticket, User $are, string $text, ?string $quickReason): ComplaintServiceReportNote
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket) ?? self::checkIn($ticket, $are, null, null);
        $labels = ['part_required' => 'Part required', 'tenant_not_available' => 'Tenant not available', 'access_denied' => 'Access denied', 'not_our_scope' => 'Not our scope'];
        return ComplaintServiceReportNote::create([
            'complaint_service_report_id' => $report->id, 'stage' => $report->complaint_assign_status,
            'desc' => ($quickReason ? ($labels[$quickReason] ?? $quickReason) . ': ' : '') . $text, 'created_by' => $are->id,
        ]);
    }

    public static function addPhoto(ComplaintChecklist $ticket, User $are, string $path, string $thumb, string $stage): \Modules\Maintenance\Entities\ComplaintServiceReportImage
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket) ?? self::checkIn($ticket, $are, null, null);
        return \Modules\Maintenance\Entities\ComplaintServiceReportImage::create([
            'complaint_service_report_id' => $report->id, 'image_path_file_name' => $path,
            'image_path_thumbnail' => $thumb, 'stage' => $stage, 'created_by' => $are->id,
        ]);
    }

    public static function addItem(ComplaintChecklist $ticket, User $are, int $inventoryId, float $qty): \Modules\Maintenance\Entities\ComplaintServiceReportInv
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket) ?? self::checkIn($ticket, $are, null, null);
        return \Modules\Maintenance\Entities\ComplaintServiceReportInv::create([
            'complaint_service_report_id' => $report->id, 'inventory_id' => $inventoryId, 'quantity' => $qty, 'checklist_id' => $ticket->id,
        ]);
    }

    public static function removeItem(ComplaintChecklist $ticket, User $are, int $itemId): void
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket);
        \Modules\Maintenance\Entities\ComplaintServiceReportInv::where('id', $itemId)->where('complaint_service_report_id', $report?->id)->delete();
    }

    public static function setStatus(ComplaintChecklist $ticket, User $are, string $status, ?string $reason): ComplaintServiceReport
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket) ?? self::checkIn($ticket, $are, null, null);

        if ($status === 'cannot_complete') {
            self::addNote($ticket, $are, 'Cannot complete: ' . ($reason ?: 'no reason given'), null);
            return $report;
        }
        $code = match ($status) { 'attended' => self::STATUS_ATTENDED, 'completed' => self::STATUS_COMPLETED, default => throw new \InvalidArgumentException($status) };

        $report->update(['complaint_assign_status' => $code]);
        $ticket->update(['ticket_status' => $code]);

        if ($code === self::STATUS_ATTENDED) {
            self::notifyAttended($ticket, $report);
        }
        return $report->fresh();
    }

    /** The coordinator + supervisor notification the desktop sends on "Attended". */
    private static function notifyAttended(ComplaintChecklist $ticket, ComplaintServiceReport $report): void
    {
        $enquiry = ComplaintEnquiry::find($ticket->complaint_enquiries_id);
        $coordinators = User::role('maintenance_coordinator')->get();
        clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification', $enquiry->id);
        $enquiry->text = 'Complaint - ' . $enquiry->complaint_no . ' Tickets-' . $ticket->complaint_ticket_no . ' To Attended ';
        $enquiry->href = route('complaintReview.view', $report->id);
        event(new \Modules\Maintenance\Events\ComplaintAssigned($enquiry, $coordinators));
        if ($ticket->assigned_to) {
            event(new \Modules\Maintenance\Events\ComplaintAssigned($enquiry, User::where('id', $ticket->assigned_to)->get()));
        }
    }

    public static function close(ComplaintChecklist $ticket, User $are, ?string $signatureDataUrl, bool $skipped): ComplaintServiceReport
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket);
        if (!$report || (int) $report->complaint_assign_status !== self::STATUS_COMPLETED) {
            throw new InvalidStateException('Mark the ticket Completed before closing it.');
        }
        if ($signatureDataUrl === null && !$skipped) {
            throw new InvalidStateException('A tenant signature is required, or mark the signature as skipped.');
        }
        if ($skipped && !\Modules\Maintenance\Entities\ComplaintServiceReportImage::where('complaint_service_report_id', $report->id)->where('stage', 'after')->exists()) {
            throw new InvalidStateException('When the signature is skipped an "after" photo is required.');
        }

        return DB::transaction(function () use ($ticket, $are, $report, $signatureDataUrl, $skipped) {
            $enquiry = ComplaintEnquiry::find($ticket->complaint_enquiries_id);
            $signaturePath = $signatureDataUrl ? \Modules\MobileApi\Services\MobileImage::storeSignature($signatureDataUrl, "complaints/{$ticket->id}") : null;

            $ticket->update(['ticket_status' => self::STATUS_CLOSED, 'work_flow_processes_code' => self::STAGE_CLOSED]);
            $report->update(['complaint_assign_status' => 3, 'tenant_signature' => $signaturePath, 'tenant_id' => $enquiry->tenant_id]);
            $enquiry->update(['complaint_status' => 1]);
            if ($skipped) {
                self::addNote($ticket, $are, 'Tenant signature skipped', null);
            }

            $coordinators = User::role('maintenance_coordinator')->get();
            clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification', $enquiry->id);
            $enquiry->text = 'Complaint - ' . $enquiry->complaint_no . ' Tickets-' . $ticket->complaint_ticket_no . ' To Closed ';
            event(new \Modules\Maintenance\Events\ComplaintAssigned($enquiry, $coordinators));

            $enquiry->subject = 'Complaint Completed';
            $enquiry->textContent = 'Your complaint No ' . $enquiry->complaint_no . ' is successfully attended by Al Habib Maintenance Team. Thanks for your kind co-operation.';
            $tenant = $enquiry->tenant;
            $email = $tenant->tenant_personal_email ?? $tenant->tenant_contact_email ?? null;
            $mobile = $tenant->tenant_contact_no ?? $tenant->tenant_residence_tel ?? null;
            if ($email) {
                \Illuminate\Support\Facades\Mail::to($email)->send(new \Modules\Maintenance\Emails\ComplaintStageEmail($enquiry, $tenant->tenant_name));
            } elseif ($mobile) {
                sendSms($mobile, 'Complaint Completed', 'optional data');
            }
            return $report->fresh();
        });
    }
```
(Import `ComplaintStageEmail`'s real namespace — find it with `grep -rn "class ComplaintStageEmail" Modules`.) `pdfPath()` is added in Task 7 with the controller, once the print view name is confirmed.

- [ ] **Step 4: Desktop delegates**

- `storeServiceReportNote` (962): after validation, replace the `ComplaintServiceReportNote::create([...])` with a plain create (it already matches; leave as is — no ticket context in that form). **No change.**
- `serviceReportStatusButtonUpdate($id,$status)` (1682): keep the multi-ticket loop, but for `status == 2` call `TicketAttendance::setStatus($checklist, $user, 'attended', null)` per checklist where `$user = User::find($checklist->sub_assigned_to ?? $checklist->assigned_to)`; keep the `status == 4` branch as is (it is the desktop's coordinator close, no signature).
- `generateServiceReport` (1781): replace the body of the `if($testSignature != null)` block with a loop `foreach (ComplaintChecklist::whereIn('id',$tickets)->get() as $t) { TicketAttendance::close($t, User::find($t->sub_assigned_to), $img, false); }` and keep `return $url;`. The desktop's `$img` is already a data URL (it is passed to `file_get_contents`), so `storeSignature` accepts it.

- [ ] **Step 5: Run the whole Maintenance + MobileApi suites, then commit**

```bash
vendor/bin/phpunit --filter "TicketAttendanceTest|MobileApi" 2>&1 | grep -E "^(OK|FAIL|Tests:)"
git add Modules/Maintenance Modules/MobileApi
git commit -m "feat(maintenance): notes, photos, items, status and signature/close in TicketAttendance; desktop delegates

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 7: Complaint API endpoints

**Files:**
- Create: `Modules/MobileApi/Http/Controllers/ComplaintController.php`, `Modules/MobileApi/Http/Controllers/FileController.php`, `Modules/MobileApi/Http/Resources/TicketListResource.php`, `Modules/MobileApi/Http/Resources/TicketResource.php`, `Modules/MobileApi/Http/Requests/{CheckInRequest,NoteRequest,PhotoRequest,ItemRequest,StatusRequest,SignatureRequest}.php`, `Modules/MobileApi/Http/Middleware/LogMobileActivity.php`, `Modules/MobileApi/Tests/Feature/ComplaintApiTest.php`
- Modify: `Modules/MobileApi/Routes/api.php`, `Modules/Maintenance/Services/TicketAttendance.php` (`pdfPath`)

**Interfaces:**
- Produces routes (all under `auth:sanctum, mobile.device, mobile.log`): `GET complaints`, `GET complaints/{ticket}`, `POST complaints/{ticket}/claim`, `…/check-in`, `…/notes`, `…/photos`, `…/items`, `DELETE …/items/{item}`, `POST …/status`, `POST …/signature`, `GET complaints/{ticket}/service-report.pdf`; `GET files/{path}` (signed).
- Produces `FileController::signedUrl(string $path): string` (static helper used by all resources).

- [ ] **Step 1: Failing feature test**

`Modules/MobileApi/Tests/Feature/ComplaintApiTest.php`:
```php
<?php

namespace Modules\MobileApi\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Tests\TestCase;

class ComplaintApiTest extends TestCase
{
    private function ticketIn(int $buildingId): ComplaintChecklist
    {
        $enq = ComplaintEnquiry::create(['complaint_date' => now(), 'complaint_no' => 'T-' . uniqid(), 'complainer_name' => 'Api', 'complaint_mob_no' => '9', 'building_id' => $buildingId, 'priority_status' => 1, 'complaint_status' => 0, 'created_by' => 1]);
        return ComplaintChecklist::create(['work_id' => 1, 'complaint_enquiries_id' => $enq->id, 'checklist_desc' => 'AC not cooling', 'complaint_ticket_no' => 'TK-' . uniqid(), 'work_flow_processes_code' => 701, 'ticket_status' => 0, 'created_by' => 1]);
    }

    public function test_end_to_end_attend_and_close(): void
    {
        $are = $this->actingAsAre();
        $ticket = $this->ticketIn(AreBuildings::forUser($are)->first());

        $this->getJson('/api/v1/complaints?tab=unassigned')->assertOk()->assertJsonFragment(['id' => $ticket->id]);
        $this->postJson("/api/v1/complaints/{$ticket->id}/claim")->assertOk()->assertJsonPath('data.stage', 703);
        $this->postJson("/api/v1/complaints/{$ticket->id}/claim")->assertStatus(409)->assertJson(['code' => 'invalid_state']);

        $this->postJson("/api/v1/complaints/{$ticket->id}/check-in", ['lat' => 23.5, 'lng' => 58.4])->assertOk();
        $this->postJson("/api/v1/complaints/{$ticket->id}/notes", ['text' => 'Gas refilled'])->assertCreated();
        $this->postJson("/api/v1/complaints/{$ticket->id}/photos", ['stage' => 'after', 'photo' => UploadedFile::fake()->image('a.jpg', 1200, 900)])->assertCreated()->assertJsonStructure(['data' => ['id', 'url', 'thumb_url']]);
        $this->postJson("/api/v1/complaints/{$ticket->id}/status", ['status' => 'attended'])->assertOk()->assertJsonPath('data.status', 2);
        $this->postJson("/api/v1/complaints/{$ticket->id}/status", ['status' => 'completed'])->assertOk()->assertJsonPath('data.status', 3);
        $this->postJson("/api/v1/complaints/{$ticket->id}/signature", ['skipped' => true])->assertOk()->assertJsonPath('data.stage', 705);

        $this->getJson("/api/v1/complaints/{$ticket->id}/service-report.pdf")->assertOk()->assertJsonStructure(['url']);
        $this->getJson('/api/v1/complaints?tab=completed')->assertOk()->assertJsonFragment(['id' => $ticket->id]);
    }

    public function test_ticket_outside_my_buildings_is_forbidden(): void
    {
        $are = $this->actingAsAre();
        $other = \Modules\Masters\Entities\Building::whereNotIn('id', AreBuildings::forUser($are)->all())->value('id');
        $ticket = $this->ticketIn($other);
        $this->getJson("/api/v1/complaints/{$ticket->id}")->assertStatus(403)->assertJson(['code' => 'not_assigned']);
    }

    public function test_validation_shape(): void
    {
        $this->actingAsAre();
        $this->postJson('/api/v1/complaints/0/status', [])->assertStatus(404);
        $t = $this->ticketIn(AreBuildings::forUser($this->areUser())->first());
        $this->postJson("/api/v1/complaints/{$t->id}/claim");
        $this->postJson("/api/v1/complaints/{$t->id}/status", ['status' => 'bogus'])->assertStatus(422)->assertJson(['code' => 'validation']);
    }
}
```
Run: `vendor/bin/phpunit --testsuite MobileApi --filter ComplaintApiTest` → FAIL (404s).

- [ ] **Step 2: Requests** (one file each under `Modules/MobileApi/Http/Requests`, all `authorize(): bool { return true; }`)

| Class | rules |
|---|---|
| `CheckInRequest` | `lat: nullable\|numeric\|between:-90,90`, `lng: nullable\|numeric\|between:-180,180` |
| `NoteRequest` | `text: required\|string\|max:2000`, `quick_reason: nullable\|in:part_required,tenant_not_available,access_denied,not_our_scope` |
| `PhotoRequest` | `photo: required\|image\|mimes:jpeg,png,webp\|max:10240`, `stage: required\|in:before,during,after` |
| `ItemRequest` | `inventory_id: required\|integer\|exists:inventories,id` (confirm table name with `\dt *invent*`), `quantity: required\|numeric\|min:0.01` |
| `StatusRequest` | `status: required\|in:attended,completed,cannot_complete`, `reason: required_if:status,cannot_complete\|nullable\|string\|max:500` |
| `SignatureRequest` | `signature: required_without:skipped\|nullable\|string\|starts_with:data:image/png;base64,`, `skipped: nullable\|boolean`, `tenant_remark: nullable\|string\|max:500` |

- [ ] **Step 3: Resources, file controller, activity middleware, controller, routes**

`FileController.php`:
```php
<?php

namespace Modules\MobileApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class FileController extends ApiController
{
    public static function signedUrl(?string $path): ?string
    {
        if (!$path) return null;
        return URL::temporarySignedRoute('api.files.show', now()->addMinutes((int) config('mobileapi.signed_url_minutes', 15)), ['path' => $path]);
    }

    /** Signed, unauthenticated download (the app opens PDFs/images in a viewer that cannot send headers). */
    public function show(Request $request)
    {
        $path = $request->query('path');
        abort_unless($request->hasValidSignature() && $path && !str_contains($path, '..'), 403);
        $disk = str_starts_with($path, 'mobile/') ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($path), 404);
        return Storage::disk($disk)->response($path);
    }
}
```
`LogMobileActivity.php` — after the response, for non-GET requests with status < 400:
```php
<?php

namespace Modules\MobileApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogMobileActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (!$request->isMethod('GET') && $response->getStatusCode() < 400 && $request->user()) {
            $device = $request->attributes->get('mobile_device');
            activity('mobile')->causedBy($request->user())
                ->withProperties(['route' => $request->route()?->getName(), 'device_uid' => $device?->device_uid, 'app_version' => $device?->app_version, 'params' => $request->route()?->parameters()])
                ->log($request->method() . ' ' . $request->path());
        }
        return $response;
    }
}
```
Register alias `mobile.log` in `MobileApiServiceProvider::boot()`.

`TicketListResource.php`:
```php
<?php

namespace Modules\MobileApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketListResource extends JsonResource
{
    public function toArray($request): array
    {
        $e = $this->complaintEnquiry;
        return [
            'id' => $this->id, 'ticket_no' => $this->complaint_ticket_no, 'complaint_no' => $e->complaint_no,
            'complaint_date' => $e->complaint_date, 'description' => $this->checklist_desc,
            'work' => optional($this->work)->works_desc, 'priority' => $e->priority_status,
            'stage' => (int) $this->work_flow_processes_code, 'status' => (int) $this->ticket_status,
            'building' => ['id' => $e->building_id, 'name' => optional($e->building)->building_name],
            'unit' => ['id' => $e->unit_id, 'no' => optional($e->unit)->unit_no],
            'tenant' => ['name' => $e->complainer_name, 'mobile' => $e->complaint_mob_no],
            'sub_assigned_to' => $this->sub_assigned_to, 'updated_at' => $this->updated_at,
        ];
    }
}
```
`TicketResource.php` extends the list resource and adds `report` (`service_report_no`, `status`, `notes[]`, `photos[]` with `url`/`thumb_url` via `FileController::signedUrl`, `items[]`, `signature_url`), `flags` (`tenant_status`, `landlord_maintained` from the unit's building `building_maintained_by` if that column exists — else omit) and `previous_complaints` (last 5 `ComplaintEnquiry` on the same unit: `complaint_no, complaint_date, complaint_status`).

`ComplaintController.php`:
```php
<?php

namespace Modules\MobileApi\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Services\TicketAttendance;
use Modules\MobileApi\Http\Requests\{CheckInRequest, ItemRequest, NoteRequest, PhotoRequest, SignatureRequest, StatusRequest};
use Modules\MobileApi\Http\Resources\{TicketListResource, TicketResource};
use Modules\MobileApi\Services\MobileImage;

class ComplaintController extends ApiController
{
    private function ticket(Request $request, int $id): ComplaintChecklist
    {
        $ticket = ComplaintChecklist::with('complaintEnquiry')->findOrFail($id);
        TicketAttendance::assertInScope($ticket, $request->user());
        return $ticket;
    }

    public function index(Request $request)
    {
        $request->validate(['tab' => 'required|in:unassigned,mine,attended,completed', 'building_id' => 'nullable|integer', 'q' => 'nullable|string|max:100', 'updated_since' => 'nullable|date', 'per_page' => 'nullable|integer|max:100']);
        $q = $request->tab === 'unassigned'
            ? TicketAttendance::unassignedTickets($request->user(), $request->integer('building_id') ?: null, $request->q)
            : TicketAttendance::myTickets($request->user(), $request->tab, $request->integer('building_id') ?: null, $request->q);
        if ($request->updated_since) {
            $q->where('complaint_checklists.updated_at', '>=', $request->date('updated_since'));
        }
        return TicketListResource::collection($q->paginate($request->integer('per_page') ?: 25));
    }

    public function show(Request $request, int $id)
    {
        return new TicketResource($this->ticket($request, $id));
    }

    public function claim(Request $request, int $id)
    {
        return new TicketResource(TicketAttendance::claim($this->ticket($request, $id), $request->user()));
    }

    public function checkIn(CheckInRequest $request, int $id)
    {
        $ticket = $this->ticket($request, $id);
        TicketAttendance::checkIn($ticket, $request->user(), $request->lat, $request->lng);
        return new TicketResource($ticket->fresh());
    }

    public function note(NoteRequest $request, int $id)
    {
        $note = TicketAttendance::addNote($this->ticket($request, $id), $request->user(), $request->text, $request->quick_reason);
        return response()->json(['data' => ['id' => $note->id, 'text' => $note->desc, 'created_at' => $note->created_at]], 201);
    }

    public function photo(PhotoRequest $request, int $id)
    {
        $ticket = $this->ticket($request, $id);
        $stored = MobileImage::store($request->file('photo'), "complaints/{$ticket->id}");
        $img = TicketAttendance::addPhoto($ticket, $request->user(), $stored['path'], $stored['thumb'], $request->stage);
        return response()->json(['data' => ['id' => $img->id, 'stage' => $img->stage, 'url' => FileController::signedUrl($stored['path']), 'thumb_url' => FileController::signedUrl($stored['thumb'])]], 201);
    }

    public function item(ItemRequest $request, int $id)
    {
        $item = TicketAttendance::addItem($this->ticket($request, $id), $request->user(), $request->integer('inventory_id'), (float) $request->quantity);
        return response()->json(['data' => $item], 201);
    }

    public function removeItem(Request $request, int $id, int $item)
    {
        TicketAttendance::removeItem($this->ticket($request, $id), $request->user(), $item);
        return response()->json(['message' => 'Removed.']);
    }

    public function status(StatusRequest $request, int $id)
    {
        $ticket = $this->ticket($request, $id);
        TicketAttendance::setStatus($ticket, $request->user(), $request->status, $request->reason);
        return new TicketResource($ticket->fresh());
    }

    public function signature(SignatureRequest $request, int $id)
    {
        $ticket = $this->ticket($request, $id);
        TicketAttendance::close($ticket, $request->user(), $request->signature, (bool) $request->boolean('skipped'));
        return new TicketResource($ticket->fresh());
    }

    public function pdf(Request $request, int $id)
    {
        $ticket = $this->ticket($request, $id);
        $report = TicketAttendance::reportFor($ticket) ?? abort(404);
        return response()->json(['url' => FileController::signedUrl(TicketAttendance::pdfPath($ticket, $report))]);
    }
}
```
Add to `TicketAttendance`:
```php
    /** Renders the desktop's technician print view to a cached PDF under mobile/complaints/{ticket}. */
    public static function pdfPath(ComplaintChecklist $ticket, ComplaintServiceReport $report): string
    {
        $path = "mobile/complaints/{$ticket->id}/service-report.pdf";
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        if ($disk->exists($path) && $disk->lastModified($path) >= $report->updated_at->timestamp) {
            return $path;
        }
        $view = 'maintenance::ServiceReport.service_report_pdf'; // confirm: grep -rn "loadView" Modules/Maintenance/Http/Controllers/ComplaintStageController.php
        $data = ['serviceReport' => $report->load('notes', 'images', 'inventories'), 'checklists' => collect([$ticket->load('complaintEnquiry.tenant', 'complaintEnquiry.building', 'complaintEnquiry.unit', 'work')])];
        $disk->makeDirectory(dirname($path));
        $disk->put($path, \PDF::loadView($view, $data)->output());
        return $path;
    }
```
(Read the desktop's PDF/print action once to mirror its exact view name and variables; if the desktop has no PDF for the technician report, create `Modules/Maintenance/Resources/views/ServiceReport/service_report_pdf.blade.php` by copying the markup of the technician "View" screen minus layout and buttons.)

Append to `Routes/api.php` inside the authenticated group (add `'mobile.log'` to the group middleware):
```php
    Route::get('files', 'FileController@show')->name('api.files.show')->withoutMiddleware(['auth:sanctum', 'mobile.device', 'mobile.log']);

    Route::get('complaints', 'ComplaintController@index');
    Route::get('complaints/{ticket}', 'ComplaintController@show');
    Route::post('complaints/{ticket}/claim', 'ComplaintController@claim');
    Route::post('complaints/{ticket}/check-in', 'ComplaintController@checkIn');
    Route::post('complaints/{ticket}/notes', 'ComplaintController@note');
    Route::post('complaints/{ticket}/photos', 'ComplaintController@photo');
    Route::post('complaints/{ticket}/items', 'ComplaintController@item');
    Route::delete('complaints/{ticket}/items/{item}', 'ComplaintController@removeItem');
    Route::post('complaints/{ticket}/status', 'ComplaintController@status');
    Route::post('complaints/{ticket}/signature', 'ComplaintController@signature');
    Route::get('complaints/{ticket}/service-report.pdf', 'ComplaintController@pdf');
```
Map `NotAssignedException` in `ApiExceptionRenderer`: it is a `DomainException` subclass, so it already returns 409 — change the match arm to use `403` when `$e->code() === 'not_assigned'`:
```php
            $e instanceof \DomainException => response()->json(['message' => $e->getMessage(), 'code' => method_exists($e, 'code') ? $e->code() : 'invalid_state'], method_exists($e, 'code') && $e->code() === 'not_assigned' ? 403 : 409),
```

- [ ] **Step 4: Run, then commit**

```bash
vendor/bin/phpunit --testsuite MobileApi 2>&1 | grep -E "^(OK|FAIL|Tests:)"
git add Modules/MobileApi Modules/Maintenance
git commit -m "feat(mobile-api): complaint endpoints — queues, claim, check-in, notes, photos, items, status, signature, PDF

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

## Part C — Termination inspection

### Task 8: Extract `TerminationWorkflow::advance()` from `tenantTerminationStage`

**Files:**
- Create: `Modules/BackOffice/Services/TerminationWorkflow.php`, `Modules/BackOffice/Tests/Unit/TerminationWorkflowTest.php`
- Modify: `Modules/BackOffice/Http/Controllers/TenantTerminationController.php:426-679`

**Interfaces:**
- Produces: `TerminationWorkflow::advance(Termination $current, string $actionKey, User $actor): Termination` — returns the **new** termination row at the next stage; does everything `tenantTerminationStage` does except session/redirect. `TerminationWorkflow::assignHandover(Termination $current, User $supervisor, User $actor): Termination` — the 502→503 assignment (`storeGroupAssign` for one termination).

- [ ] **Step 1: Failing test**

```php
<?php

namespace Modules\BackOffice\Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Services\TerminationWorkflow;
use Tests\TestCase;

class TerminationWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_assign_handover_moves_502_to_503_for_the_given_user(): void
    {
        $t = Termination::where('work_flow_processes_code', 502)->where('termination_type', 1)->latest('id')->firstOrFail();
        $are = User::role('are')->firstOrFail();
        $admin = User::where('username', 'admin')->firstOrFail();

        $next = TerminationWorkflow::assignHandover($t, $are, $admin);

        $this->assertSame(503, (int) $next->work_flow_processes_code);
        $this->assertSame($t->contract_id, $next->contract_id);
        $this->assertSame($are->id, (int) $next->assigned_to);
        $this->assertDatabaseHas('termination_users', ['termination_id' => $next->id, 'user_id' => $are->id, 'status' => 1]);
    }
}
```
(If the DB copy has no 502 rows, create one in the test from any contract at 108 with `Termination::create([...work_flow_processes_code => 502, termination_type => 1, termination_date => today...])`.)

- [ ] **Step 2: Extract**

Create `TerminationWorkflow` with the **entire body** of `tenantTerminationStage` moved into `advance()`: replace `$terminationId` with `$current->id`, `$contractId` with `$current->contract_id`, `$stage` with `$current->work_flow_processes_code`, `\Auth::user()` with `$actor`, delete the `Session::get('current')` / redirect lines, and `return $termination;` at the end (the newly created row). Keep every side effect (rent OS update, `TerminationDuesService::createForTermination`, unit vacancy, invoice cancellation, `TenantTermination` events, mails).

`assignHandover()` is the single-termination version of `storeGroupAssign` (lines 1217–1386): read it, move the per-termination loop body into the method with `$user_id = $supervisor->id`, `$role_id = $supervisor->default_role`, `$workflow_id = 502`, `$next_process_id = 503`, `\Auth::user()` → `$actor`; return the new `Termination`.

Then make the controller thin:
```php
    public function tenantTerminationStage($terminationId,$contractId,$stage,$action_key)
    {
      $current = Termination::findOrFail($terminationId);
      TerminationWorkflow::advance($current, $action_key, \Auth::user());
      $currentUrl = Session::get('current');
      session()->flash('success', 'Termination moved to next stage');
      return $currentUrl ? redirect()->route($currentUrl) : redirect()->back();
    }
```
and in `storeGroupAssign` replace the loop body with `TerminationWorkflow::assignHandover(Termination::find($termination), User::find($user_id), \Auth::user());`.

- [ ] **Step 3: Verify desktop parity**

```bash
vendor/bin/phpunit --filter TerminationWorkflowTest 2>&1 | grep -E "^(OK|FAIL|Tests:)"
SMOKE_ROUTES="handoverUnassigned,handoverAssigned,takeoverForTermination" vendor/bin/phpunit --group smoke-one 2>&1 | grep "=>"
```
Expected: OK; the three list pages return 200.

- [ ] **Step 4: Commit**

```bash
git add Modules/BackOffice
git commit -m "refactor(termination): extract stage transition and handover assignment into TerminationWorkflow

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 9: `TerminationInspection` service — queue, template, draft, submit

**Files:**
- Create: `Modules/BackOffice/Services/TerminationInspection.php`, `Modules/BackOffice/Tests/Unit/TerminationInspectionTest.php`
- Modify: `Modules/BackOffice/Http/Controllers/TenantTerminationController.php` (`terminationInspectionStore` 1874 and `terminationInspectionUpdate` 2061 delegate the row-writing to `saveDraft`)

**Interfaces:**
- Produces:
  - `TerminationInspection::queue(User $are, string $tab /* pending|inspected|taken_over */, ?int $buildingId): Builder` (Termination rows, `termination_type = 1`, latest row per contract, building via `tenantContract.building_id` in scope).
  - `TerminationInspection::template(): array` — `[ {work_id, code, desc, sub_works: [{id, name}]} ]` from `Work::with('subWork')`.
  - `TerminationInspection::detail(Termination $t): array` — tenant/unit/contract, `outstanding_rent` (via `RentOutstanding` + `totalContractRentCountCalculation` exactly as `handoverAssignedInspection` computes `$remainAmt - $sumOfReceipt`), `deposit_cheque`, `open_documents`, `draft` (checklist lines, utilities, keys, notes), `photos`.
  - `TerminationInspection::saveDraft(Termination $t, User $are, array $payload): Termination` — payload: `lines[] {work_id, sub_work_id|null, other_work|null, quantity, amount}`, `electricity {acc_no, close_reading, amount}`, `water {…}`, `main_key_status`, `maintenance_due`, `notes`, `remark`. Computes `total = Σ line.amount`, `elec_water_total = elec.amount + water.amount`, `net = total + elec_water_total − maintenance_due` and writes the same rows as `terminationInspectionStore` (a new 503 row + `termination_checklists`), re-using the existing draft row when one already exists for this contract by this user (`update` path). From stage 502 it first calls `TerminationWorkflow::assignHandover($t, $are, $are)`.
  - `TerminationInspection::addPhoto(Termination $t, User $are, string $path, string $thumb, string $type /* inspection|meter_electricity|meter_water|key */): TerminationDocument`
  - `TerminationInspection::submit(Termination $t, User $are, string $signatureDataUrl, ?string $tenantRemark): Termination` — requires a saved draft, stores signature (`termination_tenant_signature`, `termination_review_status = 1`, `os` = platform), calls `TerminationWorkflow::advance($draft, 'ACPT', $are)` → 504, renders the inspection PDF to `mobile/terminations/{id}/inspection.pdf`, sends `InspectionSendEmail` (same arguments as `inspectionSendMail`), returns the 504 row.
  - `TerminationInspection::pdfPath(Termination $t): string`.

- [ ] **Step 1: Failing tests**

```php
<?php

namespace Modules\BackOffice\Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Services\TerminationInspection;
use Modules\Masters\Services\AreBuildings;
use Tests\TestCase;

class TerminationInspectionTest extends TestCase
{
    use DatabaseTransactions;

    private function pendingInMyBuildings(User $are): Termination
    {
        $ids = AreBuildings::forUser($are)->all();
        return Termination::where('work_flow_processes_code', 502)->where('termination_type', 1)
            ->whereHas('tenantContract', fn ($c) => $c->whereIn('building_id', $ids))->latest('id')->first()
            ?? $this->markTestSkipped('No pending handover in the ARE buildings on this DB copy');
    }

    public function test_draft_then_submit_moves_to_taken_over_with_desktop_totals(): void
    {
        $are = User::role('are')->whereHas('areBuildings')->firstOrFail();
        $t = $this->pendingInMyBuildings($are);
        $tpl = TerminationInspection::template();
        $sub = $tpl[0]['sub_works'][0];

        $draft = TerminationInspection::saveDraft($t, $are, [
            'lines' => [['work_id' => $tpl[0]['work_id'], 'sub_work_id' => $sub['id'], 'quantity' => 2, 'amount' => 15.5], ['other_work' => 'Broken tile', 'quantity' => 1, 'amount' => 4.5]],
            'electricity' => ['acc_no' => 'E1', 'close_reading' => '1200', 'amount' => 10], 'water' => ['acc_no' => 'W1', 'close_reading' => '300', 'amount' => 2.5],
            'main_key_status' => 1, 'maintenance_due' => 3, 'notes' => 'ok', 'remark' => null,
        ]);
        $this->assertSame(503, (int) $draft->work_flow_processes_code);
        $this->assertSame($are->id, (int) $draft->assigned_to);
        $this->assertEquals(20.0, (float) $draft->termination_total_amount);
        $this->assertEquals(12.5, (float) $draft->termination_total_elec_water_amount);
        $this->assertEquals(29.5, (float) $draft->termination_net_amount); // 20 + 12.5 - 3
        $this->assertSame(2, $draft->terminationChecklist()->count() + 0 ?: \Modules\BackOffice\Entities\TerminationChecklist::where('termination_id', $draft->id)->count());

        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        $done = TerminationInspection::submit($draft, $are, $png, 'Tenant agrees');
        $this->assertSame(504, (int) $done->work_flow_processes_code);
        $this->assertNotEmpty(Termination::find($draft->id)->termination_tenant_signature);
        $this->assertTrue(TerminationInspection::queue($are, 'taken_over', null)->pluck('id')->contains($done->id));
    }
}
```
Run → FAIL (class not found).

- [ ] **Step 2: Implement** the class per the interface, copying the row-writing from `terminationInspectionStore` (lines 1874–2060) into `saveDraft` with the request array replaced by `$payload` and totals computed server-side; the `update` path mirrors `terminationInspectionUpdate` (2061–2196: it updates the existing row and re-creates `termination_checklists`). `submit()`:
```php
    public static function submit(Termination $draft, User $are, string $signatureDataUrl, ?string $tenantRemark): Termination
    {
        if ((int) $draft->work_flow_processes_code !== 503 || (int) $draft->assigned_to !== $are->id) {
            throw new \Modules\Maintenance\Exceptions\InvalidStateException('Save the inspection first; it must be assigned to you and at Handover Assigned.');
        }
        return DB::transaction(function () use ($draft, $are, $signatureDataUrl, $tenantRemark) {
            $sig = MobileImage::storeSignature($signatureDataUrl, "terminations/{$draft->id}");
            $draft->update(['termination_tenant_signature' => $sig, 'termination_review_status' => 1, 'termination_remark' => $tenantRemark ?? $draft->termination_remark, 'os' => 'mobile']);
            $next = TerminationWorkflow::advance($draft->fresh(), 'ACPT', $are);
            self::pdfPath($next);
            $contract = $next->tenantContract;
            $email = $contract->tenant->tenant_personal_email ?? $contract->tenant->tenant_contact_email ?? null;
            if ($email) {
                \Illuminate\Support\Facades\Mail::to($email)->send(new \Modules\BackOffice\Emails\InspectionSendEmail($next, $contract, $contract->terminationChecklistOther, $contract->terminationChecklist->groupBy('work_id')));
            }
            return $next;
        });
    }
```
Use the exceptions from `Modules\Maintenance\Exceptions` (move them to `App\Exceptions\Domain\{InvalidStateException,NotAssignedException}` if sharing across modules feels wrong — update Task 5/7 imports accordingly; keep the `code()` contract).

- [ ] **Step 3: Desktop delegates**

`terminationInspectionStore`: keep validation, build `$payload` from the request (`addinspection{$i}`/`quantity_{$i}`/`amount_{$i}` loops → `lines`, `elec_*`/`water_*` → `electricity`/`water`, etc.) and call `TerminationInspection::saveDraft(Termination::find($request->terminationId), \Auth::user(), $payload)`; keep the flash + redirect. Same for `terminationInspectionUpdate`. Totals posted by the browser are ignored in favour of the server computation (they were computed by the same formula in JS).

- [ ] **Step 4: Run, smoke the desktop inspection pages, commit**

```bash
vendor/bin/phpunit --filter "TerminationInspectionTest|TerminationWorkflowTest" 2>&1 | grep -E "^(OK|FAIL|Tests:|S)"
git add Modules/BackOffice Modules/MobileApi
git commit -m "feat(termination): TerminationInspection service — scoped queue, template, draft with server-side totals, submit

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 10: Termination API endpoints

**Files:**
- Create: `Modules/MobileApi/Http/Controllers/TerminationController.php`, `Http/Resources/{TerminationListResource,TerminationResource}.php`, `Http/Requests/{InspectionDraftRequest,InspectionPhotoRequest,InspectionSubmitRequest}.php`, `Modules/MobileApi/Tests/Feature/TerminationApiTest.php`
- Modify: `Routes/api.php`

**Interfaces:** routes `GET terminations`, `GET terminations/{id}`, `PUT terminations/{id}/inspection`, `POST terminations/{id}/photos`, `POST terminations/{id}/submit`, `GET terminations/{id}/refer-back`, `GET terminations/{id}/inspection.pdf`.

- [ ] **Step 1: Failing feature test** — same shape as `ComplaintApiTest`: pick a pending termination in scope (skip if none), `GET /terminations?tab=pending` contains it, `GET /terminations/{id}` has `template` and `outstanding_rent`, `PUT …/inspection` with the payload from Task 9 returns `stage 503` and `totals {total, elec_water_total, maintenance_due, net}`, `POST …/photos` (type `meter_electricity`) 201, `POST …/submit` with signature returns `stage 504`, `GET …/inspection.pdf` returns `{url}`, and a termination outside scope → 403 `not_assigned`.

- [ ] **Step 2: Requests**

`InspectionDraftRequest` rules:
```php
'lines' => 'present|array', 'lines.*.work_id' => 'nullable|integer|exists:works,id', 'lines.*.sub_work_id' => 'nullable|integer|exists:sub_work,id',
'lines.*.other_work' => 'nullable|string|max:200', 'lines.*.quantity' => 'required|numeric|min:0', 'lines.*.amount' => 'required|numeric|min:0',
'electricity.acc_no' => 'nullable|string|max:50', 'electricity.close_reading' => 'nullable|string|max:50', 'electricity.amount' => 'nullable|numeric|min:0',
'water.acc_no' => 'nullable|string|max:50', 'water.close_reading' => 'nullable|string|max:50', 'water.amount' => 'nullable|numeric|min:0',
'main_key_status' => 'required|in:0,1', 'maintenance_due' => 'nullable|numeric|min:0', 'notes' => 'required|string|max:2000', 'remark' => 'nullable|string|max:1000',
```
plus a `withValidator` rule: each line needs either `sub_work_id` or `other_work`.
`InspectionPhotoRequest`: `photo` image rule as Task 7, `type: required|in:inspection,meter_electricity,meter_water,key`.
`InspectionSubmitRequest`: `signature: required|string|starts_with:data:image/png;base64,`, `tenant_remark: nullable|string|max:1000`.

- [ ] **Step 3: Controller & resources** — same structure as `ComplaintController`: a private `termination(Request, int)` that loads `Termination::with('tenantContract.building','tenantContract.tenant','tenantContract.unit')->findOrFail()` and asserts `AreBuildings::covers($user, building_id)` (throw `NotAssignedException` otherwise). `TerminationResource` returns `TerminationInspection::detail($t)` + `template` + `pdf_url`/`signature_url` via `FileController::signedUrl` + `refer_back {comment, at}` from `termination_refer_back`/`supervisor_comment`. Routes appended to the authenticated group.

- [ ] **Step 4: Run and commit**

```bash
vendor/bin/phpunit --testsuite MobileApi 2>&1 | grep -E "^(OK|FAIL|Tests:|S)"
git add Modules/MobileApi
git commit -m "feat(mobile-api): termination inspection endpoints

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

## Part D — Tenant onboarding

### Task 11: Tables, models, seeder, `TenantOnboarding` service, sync command

**Files:**
- Create: `Modules/BackOffice/Database/Migrations/2026_09_19_000004_create_onboarding_tables.php`, `Modules/BackOffice/Entities/{OnboardingChecklistItem,TenantOnboarding,TenantOnboardingEntry,TenantOnboardingPhoto}.php`, `Modules/BackOffice/Database/Seeders/OnboardingChecklistItemsSeeder.php`, `Modules/BackOffice/Services/TenantOnboarding.php`, `Modules/BackOffice/Console/OnboardingSync.php`, `Modules/BackOffice/Tests/Unit/TenantOnboardingTest.php`
- Modify: `app/Console/Kernel.php` (schedule `onboarding:sync` every 15 minutes), `Modules/Sales/Http/Controllers/TenantStageController.php:871-877`, `Modules/BackOffice/Http/Controllers/TenantContractEditController.php:1629-1646` (call `TenantOnboarding::createForContract`)

**Interfaces:**
- Produces:
  - `TenantOnboarding::createForContract(TenantContract $c): ?TenantOnboardingModel` (null if one exists or contract not active; assigns `AreBuildings` first date-valid user for the building).
  - `TenantOnboarding::sync(Carbon $since): int` — creates missing rows for contracts at stage 108 with `tenant_contract_status = 1` updated since `$since`; returns count.
  - `TenantOnboarding::queue(User $are, string $tab /* open|completed */, ?int $buildingId): Builder`.
  - `TenantOnboarding::detail(TenantOnboardingModel $o): array` — items (active, ordered) merged with entries and photos, progress `{done, total}`.
  - `TenantOnboarding::saveEntries(TenantOnboardingModel $o, User $are, array $entries): TenantOnboardingModel`.
  - `TenantOnboarding::addPhoto(TenantOnboardingModel $o, User $are, int $itemId, string $path, string $thumb): TenantOnboardingPhoto`.
  - `TenantOnboarding::complete(TenantOnboardingModel $o, User $are, string $signatureDataUrl, ?string $remark): TenantOnboardingModel` — validates required items, sets `completed`, `pdf_path`, notifies back-office (`\App\User::role(['backoffice_manager','backoffice_executive'])` via the existing `notifications` mechanism used by `event(new TenantTermination(...))` — reuse that event class with `text`/`href` set, as the termination flow does).
  - `TenantOnboarding::verify(TenantOnboardingModel $o, User $by): void`.
  - Model name clash: the Eloquent model is `Modules\BackOffice\Entities\TenantOnboarding`; the service is `Modules\BackOffice\Services\TenantOnboarding`. In code that uses both, alias the model as `TenantOnboardingModel`.

- [ ] **Step 1: Migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_checklist_items', function (Blueprint $t) {
            $t->id();
            $t->string('group_name', 100);
            $t->string('title', 200);
            $t->string('input_type', 20)->default('check'); // check|text|number|count|photo|meter
            $t->boolean('required')->default(true);
            $t->boolean('needs_photo')->default(false);
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('tenant_onboardings', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_contract_id')->unique();
            $t->unsignedBigInteger('unit_id')->nullable();
            $t->unsignedBigInteger('building_id')->nullable()->index();
            $t->unsignedBigInteger('assigned_user_id')->nullable()->index();
            $t->string('status', 20)->default('open'); // open|completed|verified|cancelled
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->unsignedBigInteger('verified_by')->nullable();
            $t->timestamp('verified_at')->nullable();
            $t->string('tenant_signature')->nullable();
            $t->text('tenant_remark')->nullable();
            $t->string('pdf_path')->nullable();
            $t->timestamps();
        });
        Schema::create('tenant_onboarding_entries', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_onboarding_id')->index();
            $t->unsignedBigInteger('item_id');
            $t->text('value')->nullable();
            $t->text('remark')->nullable();
            $t->boolean('done')->default(false);
            $t->boolean('not_applicable')->default(false);
            $t->timestamps();
            $t->unique(['tenant_onboarding_id', 'item_id']);
        });
        Schema::create('tenant_onboarding_photos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('entry_id')->index();
            $t->string('path');
            $t->string('thumbnail')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_onboarding_photos');
        Schema::dropIfExists('tenant_onboarding_entries');
        Schema::dropIfExists('tenant_onboardings');
        Schema::dropIfExists('onboarding_checklist_items');
    }
};
```
Seeder rows (`OnboardingChecklistItemsSeeder`, idempotent on `title`):

| group_name | title | input_type | required | needs_photo | sort |
|---|---|---|---|---|---|
| Contract | Contract copy handed over and house rules explained | check | 1 | 0 | 10 |
| Documents | Tenant ID / passport / visa collected (note expiry) | text | 1 | 1 | 20 |
| Keys | Keys and access cards handed over (count) | count | 1 | 1 | 30 |
| Utilities | Electricity account no. and opening reading | meter | 1 | 1 | 40 |
| Utilities | Water account no. and opening reading | meter | 1 | 1 | 50 |
| Unit condition | Living room | check | 1 | 1 | 60 |
| Unit condition | Bedrooms | check | 1 | 1 | 61 |
| Unit condition | Kitchen | check | 1 | 1 | 62 |
| Unit condition | Bathrooms | check | 1 | 1 | 63 |
| Unit condition | Balcony / other | check | 0 | 1 | 64 |
| Payment | Payment instructions given / PDC set received | check | 1 | 0 | 70 |

Run: `php artisan migrate --force && php artisan db:seed --class="Modules\BackOffice\Database\Seeders\OnboardingChecklistItemsSeeder"` (register the seeder class in `Modules/BackOffice/Database/Seeders/BackOfficeDatabaseSeeder.php` too).

- [ ] **Step 2: Failing tests**

```php
<?php

namespace Modules\BackOffice\Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\BackOffice\Entities\OnboardingChecklistItem;
use Modules\BackOffice\Services\TenantOnboarding;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Masters\Services\AreBuildings;
use Modules\Sales\Entities\TenantContract;
use Tests\TestCase;

class TenantOnboardingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_assigns_the_building_are_and_complete_validates_required_items(): void
    {
        $are = User::role('are')->whereHas('areBuildings')->firstOrFail();
        $contract = TenantContract::where('work_flow_processes_code', 108)->where('tenant_contract_status', 1)
            ->whereIn('building_id', AreBuildings::forUser($are)->all())->latest('id')->firstOrFail();
        \Modules\BackOffice\Entities\TenantOnboarding::where('tenant_contract_id', $contract->id)->delete();

        $o = TenantOnboarding::createForContract($contract);
        $this->assertNotNull($o);
        $this->assertSame($are->id, (int) $o->assigned_user_id);
        $this->assertNull(TenantOnboarding::createForContract($contract), 'idempotent');

        $items = OnboardingChecklistItem::where('is_active', true)->orderBy('sort_order')->get();
        $required = $items->where('required', true);

        // partial draft is fine
        TenantOnboarding::saveEntries($o, $are, [['item_id' => $items->first()->id, 'done' => true]]);
        $this->assertSame(1, TenantOnboarding::detail($o->fresh())['progress']['done']);

        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        try {
            TenantOnboarding::complete($o->fresh(), $are, $png, null);
            $this->fail('required items missing should block completion');
        } catch (InvalidStateException $e) {
            $this->assertStringContainsString('required', strtolower($e->getMessage()));
        }

        TenantOnboarding::saveEntries($o, $are, $required->map(fn ($i) => ['item_id' => $i->id, 'done' => true, 'value' => 'x'])->values()->all());
        $done = TenantOnboarding::complete($o->fresh(), $are, $png, 'All good');
        $this->assertSame('completed', $done->status);
        $this->assertNotEmpty($done->pdf_path);
    }
}
```
Run → FAIL.

- [ ] **Step 3: Implement** models (all `$guarded = []`, `LegacyDateSerialization`, casts for timestamps/booleans; relations `entries()`, `photos()`, `item()`, `tenantContract()`, `assignedUser()`), the service:

```php
<?php

namespace Modules\BackOffice\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\OnboardingChecklistItem;
use Modules\BackOffice\Entities\TenantOnboarding as Model;
use Modules\BackOffice\Entities\TenantOnboardingEntry;
use Modules\BackOffice\Entities\TenantOnboardingPhoto;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Maintenance\Exceptions\NotAssignedException;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Services\MobileImage;
use Modules\Sales\Entities\TenantContract;

class TenantOnboarding
{
    public static function areForBuilding(int $buildingId): ?User
    {
        $today = Carbon::today()->toDateString();
        $userId = DB::table('are_buildings as ab')->join('preferred_buildings as pb', 'pb.are_building_id', '=', 'ab.id')
            ->where('pb.building_id', $buildingId)
            ->where(fn ($q) => $q->whereNull('ab.assign_from')->orWhereDate('ab.assign_from', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ab.assign_too')->orWhereDate('ab.assign_too', '>=', $today))
            ->orderByDesc('ab.assign_from')->value('ab.user_id');
        return $userId ? User::find($userId) : null;
    }

    public static function createForContract(TenantContract $c): ?Model
    {
        if ((int) $c->work_flow_processes_code !== 108 || (int) $c->tenant_contract_status !== 1) return null;
        if (Model::where('tenant_contract_id', $c->id)->exists()) return null;
        return Model::create([
            'tenant_contract_id' => $c->id, 'unit_id' => $c->unit_id, 'building_id' => $c->building_id,
            'assigned_user_id' => optional(self::areForBuilding((int) $c->building_id))->id, 'status' => 'open',
        ]);
    }

    public static function sync(Carbon $since): int
    {
        $n = 0;
        TenantContract::where('work_flow_processes_code', 108)->where('tenant_contract_status', 1)->where('updated_at', '>=', $since)
            ->whereNotIn('id', Model::pluck('tenant_contract_id'))->orderBy('id')->chunk(200, function ($contracts) use (&$n) {
                foreach ($contracts as $c) { if (self::createForContract($c)) $n++; }
            });
        return $n;
    }

    public static function queue(User $are, string $tab, ?int $buildingId): Builder
    {
        $buildings = AreBuildings::forUser($are);
        if ($buildingId) $buildings = $buildings->intersect([$buildingId]);
        return Model::with('tenantContract.tenant', 'tenantContract.unit', 'tenantContract.building')
            ->where(fn ($q) => $q->where('assigned_user_id', $are->id)->orWhereIn('building_id', $buildings->all()))
            ->when($tab === 'open', fn ($q) => $q->where('status', 'open'))
            ->when($tab === 'completed', fn ($q) => $q->whereIn('status', ['completed', 'verified']))
            ->orderByDesc('id');
    }

    public static function assertMine(Model $o, User $are): void
    {
        if ((int) $o->assigned_user_id !== $are->id && !AreBuildings::covers($are, (int) $o->building_id)) {
            throw new NotAssignedException('This onboarding is outside your assigned buildings.');
        }
    }

    public static function detail(Model $o): array
    {
        $items = OnboardingChecklistItem::where('is_active', true)->orderBy('sort_order')->get();
        $entries = $o->entries()->with('photos')->get()->keyBy('item_id');
        $rows = $items->map(fn ($i) => [
            'item_id' => $i->id, 'group' => $i->group_name, 'title' => $i->title, 'input_type' => $i->input_type,
            'required' => (bool) $i->required, 'needs_photo' => (bool) $i->needs_photo,
            'entry' => $entries->get($i->id)?->only('value', 'remark', 'done', 'not_applicable'),
            'photos' => $entries->get($i->id)?->photos->map(fn ($p) => ['id' => $p->id, 'path' => $p->path, 'thumb' => $p->thumbnail])->values() ?? [],
        ])->values();
        $done = $rows->filter(fn ($r) => $r['entry'] && ($r['entry']['done'] || $r['entry']['not_applicable']))->count();
        return ['items' => $rows, 'progress' => ['done' => $done, 'total' => $rows->count()]];
    }

    public static function saveEntries(Model $o, User $are, array $entries): Model
    {
        self::assertMine($o, $are);
        if ($o->status !== 'open') throw new InvalidStateException('This onboarding is already ' . $o->status . '.');
        DB::transaction(function () use ($o, $entries) {
            foreach ($entries as $e) {
                TenantOnboardingEntry::updateOrCreate(
                    ['tenant_onboarding_id' => $o->id, 'item_id' => (int) $e['item_id']],
                    ['value' => $e['value'] ?? null, 'remark' => $e['remark'] ?? null, 'done' => (bool) ($e['done'] ?? false), 'not_applicable' => (bool) ($e['not_applicable'] ?? false)]
                );
            }
            $o->update(['started_at' => $o->started_at ?? now(), 'assigned_user_id' => $o->assigned_user_id ?? auth()->id()]);
        });
        return $o->fresh();
    }

    public static function addPhoto(Model $o, User $are, int $itemId, string $path, string $thumb): TenantOnboardingPhoto
    {
        self::assertMine($o, $are);
        $entry = TenantOnboardingEntry::firstOrCreate(['tenant_onboarding_id' => $o->id, 'item_id' => $itemId]);
        return TenantOnboardingPhoto::create(['entry_id' => $entry->id, 'path' => $path, 'thumbnail' => $thumb, 'created_by' => $are->id]);
    }

    public static function complete(Model $o, User $are, string $signatureDataUrl, ?string $remark): Model
    {
        self::assertMine($o, $are);
        if ($o->status !== 'open') throw new InvalidStateException('This onboarding is already ' . $o->status . '.');
        $detail = self::detail($o);
        $missing = collect($detail['items'])->filter(fn ($r) => $r['required'] && !($r['entry'] && ($r['entry']['done'] || $r['entry']['not_applicable'])))->pluck('title');
        if ($missing->isNotEmpty()) throw new InvalidStateException('Required items not completed: ' . $missing->implode(', '));

        return DB::transaction(function () use ($o, $are, $signatureDataUrl, $remark) {
            $o->update(['tenant_signature' => MobileImage::storeSignature($signatureDataUrl, "onboarding/{$o->id}"), 'tenant_remark' => $remark, 'status' => 'completed', 'completed_at' => now(), 'assigned_user_id' => $o->assigned_user_id ?? $are->id]);
            $o->update(['pdf_path' => self::pdfPath($o->fresh())]);
            self::notifyBackOffice($o->fresh());
            return $o->fresh();
        });
    }

    public static function verify(Model $o, User $by): void
    {
        if ($o->status !== 'completed') throw new InvalidStateException('Only completed onboardings can be verified.');
        $o->update(['status' => 'verified', 'verified_by' => $by->id, 'verified_at' => now()]);
    }

    public static function pdfPath(Model $o): string
    {
        $path = "mobile/onboarding/{$o->id}/onboarding.pdf";
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $disk->makeDirectory(dirname($path));
        $disk->put($path, \PDF::loadView('backoffice::Onboarding.pdf', ['onboarding' => $o->load('tenantContract.tenant', 'tenantContract.unit', 'tenantContract.building', 'assignedUser.employee'), 'detail' => self::detail($o)])->output());
        return $path;
    }

    private static function notifyBackOffice(Model $o): void
    {
        $users = User::role(['backoffice_manager', 'backoffice_executive'])->get();
        $contract = $o->tenantContract;
        foreach ($users as $u) {
            $u->notify(new \Modules\BackOffice\Notifications\OnboardingCompletedNotification($o, $contract));
        }
    }
}
```
Create `Modules/BackOffice/Notifications/OnboardingCompletedNotification` modelled on the existing `Modules/BackOffice/Notifications/*` (database channel, `toArray` returns `['text' => "Onboarding completed for {$contract->tenant_contract_no}", 'href' => route('tenant-onboarding.show', $o->id)]` — check an existing notification for the exact array keys the sidebar bell reads). The PDF view `backoffice::Onboarding.pdf` is created in Task 13 (until then create a minimal one listing items/values/photos and the signature image).

`OnboardingSync` command (`onboarding:sync {--since=}`): calls `TenantOnboarding::sync(Carbon::parse($this->option('since') ?? '-2 days'))` and prints the count; schedule `->everyFifteenMinutes()` in `app/Console/Kernel.php`. Add `TenantOnboarding::createForContract($contract)` right after the two desktop activation writes (Sales `moveNextStage` after the `TenantContract::where(...)->update` at 108, and `directContractApprovalAcceptReject` after `$contract->update([... 108])`).

- [ ] **Step 4: Run and commit**

```bash
vendor/bin/phpunit --filter TenantOnboardingTest 2>&1 | grep -E "^(OK|FAIL|Tests:)"
git add Modules/BackOffice Modules/Sales app/Console/Kernel.php
git commit -m "feat(onboarding): configurable checklist items, tenant_onboardings, service, sync command, activation hooks

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 12: Onboarding API endpoints

**Files:**
- Create: `Modules/MobileApi/Http/Controllers/OnboardingController.php`, `Http/Resources/{OnboardingListResource,OnboardingResource}.php`, `Http/Requests/{OnboardingEntriesRequest,OnboardingPhotoRequest,OnboardingCompleteRequest}.php`, `Modules/MobileApi/Tests/Feature/OnboardingApiTest.php`
- Modify: `Routes/api.php`

Routes: `GET onboardings?tab=open|completed&building_id=`, `GET onboardings/{id}`, `PUT onboardings/{id}/entries`, `POST onboardings/{id}/photos`, `POST onboardings/{id}/complete`, `GET onboardings/{id}/onboarding.pdf`.

- [ ] **Step 1: Failing feature test** — as `ComplaintApiTest`: create an onboarding via `TenantOnboarding::createForContract` for a contract in scope (delete any existing row first), list contains it, detail has `items` and `progress`, `PUT entries` with all required items done returns `progress.done == total`, `POST photos` with `item_id` 201, `POST complete` with signature → `status completed` and `pdf_url`, second `complete` → 409.
- [ ] **Step 2: Requests** — `OnboardingEntriesRequest`: `entries: required|array|min:1`, `entries.*.item_id: required|integer|exists:onboarding_checklist_items,id`, `entries.*.value: nullable|string|max:1000`, `entries.*.remark: nullable|string|max:1000`, `entries.*.done: nullable|boolean`, `entries.*.not_applicable: nullable|boolean`. `OnboardingPhotoRequest`: image rule + `item_id: required|integer|exists:onboarding_checklist_items,id`. `OnboardingCompleteRequest`: `signature` PNG data URL rule, `tenant_remark: nullable|string|max:1000`.
- [ ] **Step 3: Controller/resources** — private `onboarding(Request, int)` loads the model and calls `TenantOnboarding::assertMine`; `OnboardingResource` = list fields (tenant name/mobile, unit no, building, contract no/start, status, progress, assigned user) + `TenantOnboarding::detail()` with photo paths converted to signed URLs + `signature_url`, `pdf_url`.
- [ ] **Step 4: Run and commit**

```bash
vendor/bin/phpunit --testsuite MobileApi 2>&1 | grep -E "^(OK|FAIL|Tests:|S)"
git add Modules/MobileApi
git commit -m "feat(mobile-api): tenant onboarding endpoints

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

### Task 13: Desktop screens — checklist items, onboarding list/view/verify, PDF, menu

**Files:**
- Create: `Modules/Masters/Http/Controllers/OnboardingChecklistItemController.php`, `Modules/Masters/Resources/views/OnboardingItems/{index,form}.blade.php`, `Modules/BackOffice/Http/Controllers/TenantOnboardingController.php`, `Modules/BackOffice/Resources/views/Onboarding/{index,index_ajax,show,pdf}.blade.php`, `Modules/BackOffice/Database/Migrations/2026_09_19_000005_add_onboarding_menus.php`, `Modules/BackOffice/Tests/Feature/OnboardingScreensTest.php`
- Modify: `Modules/Masters/Routes/web.php`, `Modules/BackOffice/Routes/web.php`, `Modules/Masters/Routes/breadcrumbs.php`, `Modules/BackOffice/Routes/breadcrumbs.php`

- [ ] **Step 1: Failing feature test** — as admin: `GET route('onboarding-items.index')` 200; `POST route('onboarding-items.store')` with a new item → row exists; `GET route('tenant-onboarding.index')` 200 and shows a created onboarding's contract no; `POST route('tenant-onboarding.verify', $o)` on a completed one → status verified; as an `are` user `GET onboarding-items.index` → 403.
- [ ] **Step 2: Routes** — Masters: `Route::resource('onboardingItems', 'OnboardingChecklistItemController')->names('onboarding-items')->middleware('permission:onboarding_items_manage')` + `POST onboardingItems/reorder`. BackOffice: `GET tenantOnboarding` (`tenant-onboarding.index`, `permission:onboarding_view`), `GET tenantOnboarding/{onboarding}` (`.show`), `POST tenantOnboarding/{onboarding}/verify` (`.verify`, `permission:onboarding_verify`), `POST tenantOnboarding/{onboarding}/reassign` (`.reassign`, `user_id`), `GET tenantOnboarding/{onboarding}/pdf` (streams `pdf_path`, regenerating via `TenantOnboarding::pdfPath` if missing).
- [ ] **Step 3: Views** — follow the Termination Dues screens (`Modules/BackOffice/Resources/views/TerminationDues/index.blade.php` + `index_ajax.blade.php` + `show.blade.php`) for layout, filters (building, status, ARE), AJAX list and the detail header/tiles; `pdf.blade.php` mirrors `TerminationDues/pdf.blade.php` (logo, tenant/unit block, grouped items with value/remark/photos, signature). Items form: group, title, input type select, required/needs-photo checkboxes, sort order, active.
- [ ] **Step 4: Menu migration** — two rows: `PLM Module → Masters → "Onboarding Checklist Items"` (`onboarding-items.index`) and `PLM Module → Operations → "Tenant Onboarding"` (`tenant-onboarding.index`), following `2026_09_17_000002_add_termination_dues_menu.php` (look up parents by name; `menutype 2`; order = count+1; attach permissions `onboarding_items_manage` / `onboarding_view` via `permissions.menu_id`).
- [ ] **Step 5: Run screens test + smoke sweep, commit**

```bash
php artisan migrate --force 2>&1 | tail -1
vendor/bin/phpunit --filter OnboardingScreensTest 2>&1 | grep -E "^(OK|FAIL|Tests:)"
SMOKE_ROUTES="onboarding-items.index,tenant-onboarding.index,mobile-devices.index" vendor/bin/phpunit --group smoke-one 2>&1 | grep "=>"
git add Modules/Masters Modules/BackOffice
git commit -m "feat(onboarding): office screens — checklist items, onboarding list/view/verify/reassign, PDF, menus

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

## Part E — Wrap-up

### Task 14: Buildings endpoint, full regression, docs

**Files:**
- Create: `Modules/MobileApi/Http/Controllers/BuildingController.php`, `docs/upgrade/mobile-api.md`
- Modify: `Routes/api.php`, `docs/upgrade/deploy-new-server.md`, `config/cors.php` (publish if absent; allow `*` origins for `api/v1/*` — mobile apps don't send Origin, the web debug tools do)

- [ ] **Step 1:** `GET /buildings` → `BuildingResource` collection with counts: `unassigned_complaints` (`TicketAttendance::unassignedTickets($user, $id)->count()`), `pending_inspections` (`TerminationInspection::queue($user, 'pending', $id)->count()`), `open_onboardings` (`TenantOnboarding::queue($user, 'open', $id)->count()`). Feature test asserts the keys and that counts are integers.
- [ ] **Step 2:** Full regression:
```bash
vendor/bin/phpunit --exclude-group smoke,smoke-one 2>&1 | grep -E "^(OK|FAIL|Tests:|ERRORS)"
vendor/bin/phpunit --group smoke 2>&1 | grep -E "^(OK|FAIL|Tests:)"; head -1 docs/upgrade/smoke-get-routes.txt
```
Expected: all green; the sweep now also covers the three new office screens.
- [ ] **Step 3:** `docs/upgrade/mobile-api.md` — endpoint table (copy from the spec, updated with any deviations), the login/device flow, error codes, `X-Device-Id`/`X-App-Version` headers, signed-URL behaviour, `MOBILE_API_ENABLED`, and the `onboarding:sync` schedule. Add to `deploy-new-server.md`: `php artisan migrate` now includes Sanctum + mobile tables, `MOBILE_API_ENABLED`, the seeder command, and that `storage/app/mobile` must be writable and backed up.
- [ ] **Step 4: Commit**

```bash
git add Modules/MobileApi config/cors.php docs/upgrade
git commit -m "feat(mobile-api): buildings summary endpoint; API docs; deployment notes

Co-Authored-By: Claude Opus 5 (1M context) <noreply@anthropic.com>"
```

---

## Self-review

- **Spec coverage:** auth + device policy (T3), office devices screen (T4), building scope (T2), complaints queues/claim/check-in/notes/photos/items/status/signature/PDF (T5–T7), inspection queue/template/draft/photos/submit/refer-back/PDF (T8–T10), onboarding tables/items/auto-create/backfill/entries/photos/complete/verify + office screens + menus (T11–T13), signed URLs (T7 `FileController`), activity log (T7 middleware + T3 login), `updated_since` (T7 index; add the same `->where('updated_at','>=')` clause in T10/T12 index methods), feature flag (T1), error shape (T1/T7), CORS + docs (T14). Sanctum token never expires: `createToken()` without `expiresAt` and `sanctum.expiration` left `null` (default).
- **Placeholders:** the two "confirm with grep" notes (PDF view name in T7, `stage` column type in T5, inventories table name in T7) are verification steps with a stated fallback, not open design.
- **Consistency:** `AreBuildings::forUser/covers` used in T5, T9, T11, T14; `MobileImage::store/storeSignature` used in T6, T7, T9, T11, T12; `FileController::signedUrl` in T7, T10, T12; exceptions `InvalidStateException`/`NotAssignedException` with `code()` used by the renderer in T1/T7 (403 for `not_assigned`, 409 otherwise); `TerminationWorkflow::advance/assignHandover` defined T8, used T9; onboarding model vs service naming resolved with the `Model` alias in T11.
