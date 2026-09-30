# Bulk send - codebase facts (collected 2026-09-30, branch feature/bulk-send, DB pgsql `plms_new`)

Paths relative to C:\laragon\www\plms_b. Laravel 5.7, PHP 7.4 runtime; existing code avoids arrow fns; phpunit setUp()/tearDown() have NO `: void` (PHPUnit 7 style, `assertContains` on strings).

## 1. Tenants and tenant contracts
- Model `Modules\Sales\Entities\Tenant` (Modules/Sales/Entities/Tenant.php), table `tenant` (singular), `$guarded=[]`.
- Columns: id, tenant_code, tenant_name, tenant_contact_no (mobile), tenant_residence_tel, tenant_contact_email, tenant_personal_email, location_id, tenant_status (1 active / 0 inactive), status (VIP flag, unrelated), email_count.
- `scopeActive` (Tenant.php:137) = `where('tenant_status', 1)`.
- Relations: `tenantContracts()` hasMany TenantContract fk tenant_id (Tenant.php:71); `tenantContractsActive()` = same + `where('tenant_contract_status',1)` (:79); `location()` (:55).
- Model `Modules\Sales\Entities\TenantContract`, table `tenant_contracts`, `$guarded=[]`. Columns: id, tenant_id, building_id, unit_id, tenant_contract_no, tenant_contract_status (int), tenant_contract_start_date, tenant_contract_effective_date, tenant_contract_valid_from_date, tenant_contract_valid_to_date, tenant_contract_rent, work_flow_processes_code, tenant_renewal_termination_status, created_at.
- Status values in DB: 0 = 28432 rows, 1 = 2818, 2 = 22 (meaning unknown; never treated as active). Accessor (TenantContract.php:188) knows only 1 Active / 0 Inactive.
- Active contract in code: `TenantContract::scopeActive` (TenantContract.php:235) = `where('tenant_contract_status', 1)`.
- Relations on TenantContract: `tenant()` (:127), `building()` (:25), `Unit()` (:50, capital U; MessageValues reads `->unit`, works because PHP method names are case-insensitive; for eager loading use `with('Unit')`).
- `Modules\Masters\Entities\Unit`: unit_no, unit_code (code carries building prefix).
- Tenant recipient as system emails it: `ComplaintMessages::tenantRecipient($tenant, $emailFirst='contact')` (Modules/BackOffice/Services/ComplaintMessages.php:35-46): email = tenant_contact_email else tenant_personal_email ('personal' reverses); mobile = `tenant_contact_no ?: tenant_residence_tel`; returns `new MessageRecipient($tenant->tenant_name, $email, $mobile)`.

## 2. Landlords
- Landlord model = `Modules\Masters\Entities\Vendor` (Modules/Masters/Entities/Vendor.php), table `vendors`, with `vendor_type_id = 2` (vendor_types: 1 Contractor, 2 Landlord; confirmed e.g. MassMailController.php:239, LandlordPaymentController.php:258).
- Columns: id, vendor_code, vendor_name, vendor_type_id, vendor_contact_email, vendor_contact_no (mobile), vendor_contact_person, vendor_status (1 active/0), location_id.
- `Vendor::scopeActive` (Vendor.php:72) = `where('vendor_status',1)`. `Vendor::landlordContract()` (:91) hasMany LandlordContract vendor_id with `landlord_contract_status = 1`.
- Existing landlord recipient: `new MessageRecipient($vendor->vendor_name, $vendor->vendor_contact_email, $vendor->vendor_contact_no)` (TenantMessages.php:22). Values: `MessageValues::vendor($vendor)` -> only `landlord_name`.
- Model `Modules\Sales\Entities\LandlordContract`, table `landlord_contract` (singular). Columns: id, building_id, vendor_id, landlord_contract_no, landlord_contract_status, landlord_contract_valid_from_date, landlord_contract_valid_to_date, start_date, end_date, landlord_renewal_termination_status, termination_notification, close_activity, landlord_indirect_direct_status.
- Status in DB: 0 = 321, 1 = 169. Accessor (LandlordContract.php:81): 3 Pending-Direct, 2 Unapproved-Direct, 1 Approved, 0 Pending. Active in code: `LandlordContract::scopeActive` (:71) = `where('landlord_contract_status', 1)`.
- Relations: `vendorInfo()`/`vendorName()` (belongsTo Vendor vendor_id), `buildingInfo()` (belongsTo Building building_id). Building side: `landlordContract()` hasMany (Building.php:211), `landlordContractActive()` hasOne->active() (:221).

## 3. Buildings and locations
- `Modules\Masters\Entities\Building`, table `buildings`: id, building_code, building_name, vendor_id, location_id, building_status (`scopeActive` Building.php:147 = `where('building_status',1)`). `location()` belongsTo Location (Building.php:193); `tenantContract()` hasMany (:259).
- `Modules\Masters\Entities\Location`, table `locations`: id, locations_code, locations_name, locations_status (`scopeActive` Location.php:23 = `where('locations_status',1)`).
- NO existing ajax "buildings by location" route (grepped all Routes/web.php). Existing patterns: location select = `Location::active()->get()` passed to view (BackOfficeReportController.php:3891); building picker = jQuery-UI autocomplete `Route::get('/buildingAutocomplete','BuildingController@buildingAutocomplete')->name('buildingAutocomplete')` (Modules/Masters/Routes/web.php:108; returns `building_name AS value, id AS ids`, only buildings with an active landlord contract); `Route::post('tenantContract/getBuildingDetail',...)->name('getBuildingDetail')` (Modules/Sales/Routes/web.php:119). The plan must add its own JSON route or embed a location->buildings map in the page (`Building::active()->orderBy('building_name')->get(['id','building_name','location_id'])`).

## 4. Staff
- `App\User` (app/User.php), table `users`: id, username, email, user_type ('employee' 45 active + 24 inactive, 'admin' 1), user_type_id (= employees.id), user_type_status (boolean, TRUE active), default_role. `User::scopeActive` (app/User.php:52) = `where('user_type_status', TRUE)`. Spatie roles: `model_has_roles.model_type = 'App\User'`.
- `User::employee()` (app/User.php:34) = `belongsTo('Modules\Masters\Entities\Employee','user_type_id')->withDefault()` (check `$employee->exists`). employees columns: employee_name, employee_contact_no, employee_secondary_no, employee_status, deleted_at.
- Existing staff recipient: `StaffMessages::userRecipients($users)` (Modules/BackOffice/Services/StaffMessages.php:9-23) returns list of `[$user, MessageRecipient]`; name = employee_name else username; email = `$user->email`; mobile = `employee_contact_no ?: employee_secondary_no`.
- Tables: `roles` (id, name, guard_name 'web'), `permissions` (id, name, guard_name, menu_id), `model_has_roles` (role_id, model_type, model_id), `role_has_permissions` (permission_id, role_id). Query users by role names: `User::active()->whereIn('id', DB::table('model_has_roles')->join('roles','roles.id','=','model_has_roles.role_id')->whereIn('roles.name',$names)->where('model_type','App\User')->pluck('model_id'))` (or Spatie `User::role($names)`).
- Roles in DB (id name): 1 super_admin, 2 sales_person, 4 call_center, 5 sales_coordinator, 6 sales_head, 7 backoffice_executive, 8 backoffice_manager, 14 are, 15 maintenance_coordinator, 16 maintenance_supervisor, 17 technician, 18 facility_manager, 19 legal_advisor, 20 accountant, 21 finance_manager, 23 maintenance_contracts_executive, 24 maintenance_executive, 25 pro, 26 reception, 27 cashier, 28 take_over_supervisor, 29 take_over_executive, 30 maintenance_engineer, 31 are_team_lead, 32 ceo, 33 md, 34 maintenance_manager.

## 5. Modules\BackOffice\Services\MessageValues (Services/MessageValues.php)
All `public static`; missing data -> '-'. Dates d/m/Y. `today` and `company_name` are NOT here; SystemMessenger::send adds them (line 58).
- `contract($contract)` -> tenant_name, contract_no, building, unit, location, rent (number_format 3dp), contract_start (tenant_contract_start_date), contract_end (tenant_contract_valid_to_date), mobile. Uses `$contract->tenant/building/unit`, `building->location->locations_name`; null-safe.
- `tenant($tenant)` -> tenant_name, mobile.
- `complaint($complaint)` -> complaint_no, complaint_date, building, unit, location + tenant().
- `assignment($checklist, $complaint)` -> works_code, work_desc, complaint_no, complaint_date, complaint_mobile, building, unit.
- `enquiry($enquiry)` -> enquiry_no, enquiry_type, enquirer_name.
- `vendor($vendor)` -> landlord_name ONLY (landlord placeholders building & location have no producer; bulk landlord values must add them, e.g. `place($buildingId,null)` gives building,unit; location via Location name).
- `place($buildingId, $unitId)` -> building, unit.
- `employee($user)` -> employee_name (employee name else username), role (first role, ucwords, '_'->' ').
- `cheque($pdc)` -> cheque_no, amount, cheque_date, bounce_reason, remarks.
- `Modules\BackOffice\Services\MessageRecipient`: `__construct($name, $email=null, $mobile=null, array $cc=[])`; public name,email,mobile,cc; blank -> null.

## 6. Placeholders / renderer / entities
- `MessagePlaceholders` (Services/MessagePlaceholders.php): `const AUDIENCES = ['tenant'=>'Tenants','landlord'=>'Landlords','staff'=>'Staff','any'=>'Anyone']` (:11). `forTemplate($audience, $key=null)` (:54) -> common + audience map name => ['label','sample',('block')]. common: today, company_name. tenant: tenant_name, contract_no, building, unit, location, rent, contract_start, contract_end, mobile. landlord: landlord_name, building, location. staff: employee_name, role. any: common only. `samples($audience,$key=null)` (:64) name=>sample (blocks excluded); `blockSamples()` (:76); `registerSystem($key,array $extra)` (:45).
- `MessageTemplateRenderer::render($template, array $values, array $blocks = [])` (Services/MessageTemplateRenderer.php:45): `$template` array/object with email_subject/email_body/sms_body ('' -> null). Returns `['subject'=>?string (newlines collapsed), 'html'=>?string (values HTML-escaped), 'sms'=>?string, 'unknown'=>sorted array of unknown placeholder names]`. Other statics: `fill()`, `placeholdersIn($text)`, `smsParts($text)` -> ['chars','unicode','parts'], `cleanHtml($html)`, `isBlankHtml($html)`.
- `Modules\BackOffice\Entities\MessageTemplate`: table `message_templates` (id, key, name, kind, audience, email_subject, email_body, sms_body, is_active, sms_enabled, created_by, updated_by, timestamps, deleted_at); SoftDeletes; `const KIND_SYSTEM='system'`, `KIND_BULK='bulk'`; `$guarded=[]`; `$casts=['is_active'=>'boolean']` (sms_enabled not cast); methods `isSystem()`, static `system($key)`. No scopes: bulk list = `MessageTemplate::where('kind', MessageTemplate::KIND_BULK)->where('is_active', true)->orderBy('name')`. Bulk templates: key = null, created_by set.
- `Modules\BackOffice\Entities\MessageLog`: table `message_log`, `$timestamps=false`, `$guarded=[]`, `$dates=['created_at']`; consts SENT='sent', FAILED='failed', SKIPPED_OFF='skipped_off', SKIPPED_NO_ADDRESS='skipped_no_address', SKIPPED_NO_MOBILE='skipped_no_mobile', `STATUSES` label map. Columns: id, template_key(100), channel(10 email|sms), recipient_name(150), recipient(190), cc, subject(255), body, status(30), note, related_type(50), related_id(int), triggered_by(int), created_at; indexes created_at, template_key, [related_type, related_id]. Sent-log template filter dropdown lists only system templates (MessageLogController@index) so `bulk:<id>` keys show raw.
## 7. SystemMessenger::send (Modules/BackOffice/Services/SystemMessenger.php)
`public static $smsSender = 'sendSms'` (line 18): callable `($mobile, $message, [])` returning bool; global `sendSms($mobile,$msg,$params)` in config/function.php:419 (returns false when SMS_ENABLE_DISABLE==2). `send($key, MessageRecipient $to, array $values, array $options = [])` = lines 20-105. Steps:
- 22-32 load template by key + kind system, else `SystemTemplateDefaults::get($key)` fallback (note 'template_missing: default wording used'); throws InvalidArgumentException for an unknown key.
- 33-38 `$base` log row: template_key, recipient_name, related_type/related_id from `$options['related'] = [type, id]`, triggered_by = Auth::id() if Auth::check().
- 39 `$result = ['email'=>null,'sms'=>null]`.
- 40-47 `$secrets` from `$options['secret']` (raw + htmlspecialchars variants).
- 49-56 template `is_active` false -> log SKIPPED_OFF on the channel the template uses, return.
- 58 `$values = $values + ['today'=>date('d/m/Y'),'company_name'=>'Al Habib & Co. L.L.C']`.
- 59-63 `$blocks = $options['blocks'] ?? []`; `MessageTemplateRenderer::render($template,$values,$blocks)`; unknown placeholders appended to `$note`.
- 65-84 EMAIL only if rendered subject and html both non-null. Row = base + channel 'email', recipient, cc, subject, body(html), note. No `$to->email` -> SKIPPED_NO_ADDRESS; else `Mail::to($to->email)` (+cc) `->send(new TemplatedMail($out['subject'],$out['html'],$options['attachments'] ?? []))` in try/catch Throwable -> SENT / FAILED (row note = note + exception message). `self::log($row + ['status'=>...], $secrets)` at line 83.
- 86-103 SMS only if `$template->sms_enabled && $out['sms'] !== null`. No `$to->mobile` -> SKIPPED_NO_MOBILE; else `call_user_func(self::$smsSender, $to->mobile, $out['sms'], [])` truthy -> SENT else FAILED (note 'SMS gateway refused or SMS is disabled in settings'); exceptions -> FAILED with message. `self::log(...)` at 102.
- 104 `return $result` (values 'sent'|'failed'|'skipped_*'|null).
- `private static log(array $row, array $secrets=[])` 107-133: masks secrets with '[link hidden]' in subject/body/note; truncates template_key 100, recipient_name 150, recipient 190, subject 255, related_type 50; `\DB::transaction(function(){ MessageLog::create($row + ['created_at'=>now()]); })` (savepoint); errors swallowed to `\Log::error`.
- Extraction: the template object is used only for is_active (49), sms_enabled (86) and as render input (60; render casts `(object)$template`, so an array of texts works). `sendTexts($logKey,$texts,$to,$values,$options)` = lines 22-31 replaced by texts array + channels option, keep 33-47, 58-103 with the two switches replaced by `$options['channels']['email'|'sms']`.
- Email class: `Modules\BackOffice\Emails\TemplatedMail` (`__construct($subject,$html,array $attachments=[])`, public `$bodyHtml`, view `backoffice::Email.templated`).

## 8. Mail -> Templates / Sent log screens
- Controllers: Modules/BackOffice/Http/Controllers/MessageTemplateController.php (ctor: `$this->middleware('auth'); $this->middleware('permission:view_message_templates|manage_message_templates')->only(['index','preview']); $this->middleware('permission:manage_message_templates')->except(['index','preview']);`) and MessageLogController.php (ctor `auth` + `permission:view_message_log`; `const PER_PAGE = 50`; index builds a `$filters` array, `->paginate(self::PER_PAGE)->appends($filters)`; `show($id)` returns JSON). No permission -> HTTP 403. Views also use `\Auth::user()->can('manage_message_templates')`.
- Routes: Modules/BackOffice/Routes/web.php: `Route::middleware('auth')->group(function () {` begins line 596 (inside `Route::group(['middleware'=>'prevent-back-history'], ...)` at line 13; no URL prefix; module controller namespace). Lines 643-648:
```
Route::get('message-log', 'MessageLogController@index')->name('message-log.index');
Route::get('message-log/{id}', 'MessageLogController@show')->name('message-log.show');
Route::post('message-templates/preview', 'MessageTemplateController@preview')->name('message-templates.preview');
Route::post('message-templates/{messageTemplate}/reset', 'MessageTemplateController@reset')->name('message-templates.reset');
Route::resource('message-templates', 'MessageTemplateController')->except(['show'])->parameters(['message-templates' => 'messageTemplate']);
Route::resource('massMail', 'MassMailController');
```
- Breadcrumbs (must exist for each page that calls `Breadcrumbs::render`): Modules/BackOffice/Routes/breadcrumbs.php:717-728 (message-templates.index/create/edit: `$trail->parent('home'); $trail->push('Templates', route('message-templates.index'));`) and :958-961 (`message-log.index` -> `parent('home')`, `push('Sent log', route('message-log.index'))`).
- Views: Modules/BackOffice/Resources/views/MessageTemplate/{index,form,_preview_modal,_styles}.blade.php, MessageLog/index.blade.php (namespace `backoffice::`, e.g. `view('backoffice::MessageTemplate.index', compact(...))`). Layout `@extends('layouts.plms-app')` (resources/views/layouts/plms-app.blade.php). Sections: `@section('css')` (yielded in layouts/style-script.blade.php:51; used for `@include('backoffice::MessageTemplate._styles')` and the TinyMCE script tag), `@section('content')`, `@section('scripts')` (yield at plms-app:47, jQuery available). Content skeleton: `<div class="page-bar"><div class="page-title-breadcrumb"><div class="pull-left"><div class="page-title">TITLE</div></div>{{ Breadcrumbs::render('route.name') }}</div></div><div class="mt"><div class="card card-box"><div class="card-body">...`. CSS classes in _styles: .mt-input, .mt-btn, .mt-btn-primary, .mt-btn-sm, .mt-btn-danger, .mt-badge, .mt-badge-on, .mt-empty, .mt-section, .mt-field, .mt-row, .mt-help, .mt-error, .mt-footer, .mt-actions, .mt-switch, .mt-count, tables.
- Editor: form.blade.php:12 `<script src="{{ asset('public/js/tinymce.js') }}"></script>` (inside `@section('css')`). Init at form.blade.php:177: `tinymce.init({selector:'#mt-email-body', menubar:false, statusbar:false, branding:false, height:360, plugins:'link lists', toolbar:'undo redo | bold italic underline | bullist numlist | link | placeholders', setup: function(editor){ editor.ui.registry.addMenuButton('placeholders',{text:'Placeholder', fetch:function(cb){ items = {type:'menuitem', text: p.label+'  {'+name+'}', onAction: editor.insertContent('{'+name+'}')} ... }}) }})`; `$('#mt-form').on('submit', function(){ tinymce.triggerSave(); })`. Placeholder dropdown for inputs: `<select class="mt-input mt-ph" data-target="#mt-subject"></select>` filled by JS from `@json($allPlaceholders)` (controller `form()` builds `[audience => MessagePlaceholders::forTemplate($a,$key)]`; blocks skipped for inputs); change handler inserts `'{'+name+'}'` at the cursor (`insertAtCursor`). SMS counter JS `smsParts()` mirrors PHP. Preview: `_preview_modal` exposes JS `mtPreview({id})` or `mtPreview({audience,key,email_subject,email_body,sms_body})` -> POST `route('message-templates.preview')` with `_token` from `meta[name=csrf-token]` -> JSON `{subject,html,sms,unknown,sms_parts}`. Email body saved through `Renderer::cleanHtml()`; blank editor HTML via `Renderer::isBlankHtml()`.
- Controller validation (MessageTemplateController::validated): name required|max:150; audience in AUDIENCES; email_subject nullable max 255; email_body nullable; sms_body nullable max 1000; subject xor body -> ValidationException::withMessages; needs subject or sms.
- Menu ids here: Compose (massMail.create) id 180, parent_menu 179 (= Mail), Templates id 242 order 3, Sent log id 243 order 4.
- Newest migration file now: 2026_09_30_100000_add_cheque_bounce_copy_emails_setting.php (new ones must sort after). Table-creation style: 2026_09_30_000000_add_sms_enabled_and_message_log.php (`Schema::hasTable` guard, `$table->increments('id')`, `$table->string('x',N)`, `$table->index(...)`; class name = StudlyCase of file name sans date).

### database/migrations/2026_09_29_000001_add_message_templates_menu.php (full)
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
### database/migrations/2026_09_30_000001_add_message_log_menu.php (full)
```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Mail -> Sent log. The Mail parent is found through the Compose row
 * (massMail.create) because menu ids differ between databases.
 */
class AddMessageLogMenu extends Migration
{
    const ROUTE = 'message-log.index';
    const PERMS = ['view_message_log'];
    const ROLES = ['super_admin', 'backoffice_manager'];

    public function up()
    {
        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }
        $compose = DB::table('menu')->where('route_name', 'massMail.create')->first();
        $parentId = $compose ? $compose->parent_menu : 0;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Sent log',
            'menu_icon'   => 'fa-paper-plane-o',
            'route_name'  => self::ROUTE,
            'url_key'     => 'message-log',
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

## 9. Console
- app/Console/Kernel.php: `protected $commands = [ Commands\NotificationCommand::class, \Modules\BackOffice\Console\TerminationDuesBackfill::class, \Modules\BackOffice\Console\TerminationDuesRefresh::class, \Modules\BackOffice\Console\RentOutstandingCorrectShortContracts::class, ];` schedule() body (inside `protected function schedule(Schedule $schedule)`):
```php
$schedule->command('taskOverDuenotify:command')->everyMinute();
$schedule->command('termination-dues:refresh --open-only')->dailyAt('01:30');
```
  `commands()` does `$this->load(__DIR__.'/Commands')` (only app/Console/Commands autoloads; module commands must be listed in `$commands`). Add: `\Modules\BackOffice\Console\BulkMessagesSend::class` and `$schedule->command('bulk-messages:send')->everyMinute()->withoutOverlapping();`.
- Pattern: Modules/BackOffice/Console/TerminationDuesRefresh.php:
```php
namespace Modules\BackOffice\Console;
use Illuminate\Console\Command;
class TerminationDuesRefresh extends Command
{
    protected $signature = 'termination-dues:refresh {--open-only : Only records that still have a balance}';
    protected $description = 'Recompute termination dues balances from live receipts and deposit refunds';
    public function handle() { /* ... */ $this->info('Refreshed ' . $n . ' dues record(s).'); return 0; }
}
```

## 10. Settings
- Read: global helper `prefixData($configuration_settings)` (config/function.php:14) = `App\Setting::where('configuration_settings',$key)->first()` (null if the row is missing!) -> `prefixData('x')->configuration_value` (string). `App\Setting` (app/Setting.php) table `configuration`: id, configuration_name, configuration_value, configuration_settings, created_by, updated_by, created_at, updated_at, configuration_icon, configuration_year, configuration_increment_value. Null-safe usage example: PdcController.php:727 (`$setting = prefixData('cheque_bounce_copy_emails');`).
- General Configuration -> Settings tab automatically lists every row with `configuration_name = 'settings'` (resources/views/setting/general_config.blade.php:167-174: text input per row, label = ucwords of key with '_' -> ' ', all `required`); SettingsController@store case 'settings' saves each. New rows need no view change. Existing keys: renewal_notification, no_of_records_in_list_grid, upload_size_in_mb, cheque_bounce_copy_emails, tax_percentage, landlord_invoice_v2_vat_account, deposit_refund_payout_accounts, municipal_tax_percentage, municipality_processing_charge, municipal_tax_account, municipal_tax_penalty_account, municipality_payment_account.
- Migration pattern (database/migrations/2026_09_16_000001_add_municipal_tax_percentage_setting.php), class `AddMunicipalTaxPercentageSetting extends Migration`:
```php
public function up()
{
    $exists = DB::table('configuration')->where('configuration_settings', 'municipal_tax_percentage')->exists();
    if (!$exists) {
        DB::table('configuration')->insert([
            'configuration_name'     => 'settings',
            'configuration_settings' => 'municipal_tax_percentage',
            'configuration_value'    => '3',
            'created_by'             => 1,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);
    }
}
public function down() { DB::table('configuration')->where('configuration_settings', 'municipal_tax_percentage')->delete(); }
```
  (imports: `use Illuminate\Support\Facades\DB; use Illuminate\Database\Migrations\Migration;`)

## 11. Tests
- Base: tests/TestCase.php `abstract class TestCase extends Illuminate\Foundation\Testing\TestCase { use CreatesApplication; }` (namespace `Tests`); feature tests in tests/Feature, namespace `Tests\Feature`. phpunit.xml env: APP_ENV testing, CACHE/SESSION array, QUEUE sync, MAIL_DRIVER array. They run against the local Postgres `plms_new` with `use Illuminate\Foundation\Testing\DatabaseTransactions;` (no RefreshDatabase). Run: `vendor/bin/phpunit --filter X`.
- SystemMessengerTest (tests/Feature/SystemMessengerTest.php):
```php
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
protected function tearDown() { SystemMessenger::$smsSender = 'sendSms'; parent::tearDown(); }
```
  Assertions used: `Mail::assertSent(TemplatedMail::class, function ($m) { return $m->hasTo('x@y') && $m->hasCc('c@y') && $m->subject === '...' && strpos($m->bodyHtml, '...') !== false; })`, `Mail::assertNothingSent()`, `MessageLog::where('template_key',..)->latest('id')->first()`; result compare `assertSame(['email'=>'sent','sms'=>null], $r)`.
- MessageTemplateScreensTest setUp: `parent::setUp(); $this->actingAs(User::find(1));` (`use App\User; use Modules\BackOffice\Entities\MessageTemplate as MT; use Spatie\Permission\Models\Role; use Spatie\Permission\PermissionRegistrar;`); helpers e.g. `MT::create(['name'=>'Bulk one','kind'=>'bulk','audience'=>'tenant','sms_body'=>'Hi','is_active'=>true])`; `$this->put(route('message-templates.update',$t->id), [...])->assertRedirect()`.
- MessageLogScreenTest setUp: `parent::setUp(); $this->actingAs(User::find(1));` then `MessageLog::create($base + [...])` rows. Permission gate test pattern:
```php
$user = User::where('id', '<>', 1)->firstOrFail();
\DB::table('model_has_roles')->where('model_id', $user->id)->delete();
\DB::table('model_has_permissions')->where('model_id', $user->id)->delete();
$role = Role::create(['name' => 'ml_none_tmp', 'guard_name' => 'web']);
$role->givePermissionTo('view_message_templates');   // any permission other than the one under test
$user->assignRole($role);
app(PermissionRegistrar::class)->forgetCachedPermissions();
$this->actingAs(User::find($user->id));
$this->get(route('message-log.index'))->assertStatus(403);
app(PermissionRegistrar::class)->forgetCachedPermissions();   // at the end
```
  HTML assertions: `$html = $this->get(route(..))->assertOk()->getContent(); $this->assertContains('x', $html); $this->assertNotContains(...)`; JSON: `->assertJson([...])`. The new permission `bulk_message_send` only exists in the DB after the new menu migration is run on the local DB (User 1 = super_admin gets it via the migration).

## 12. Counts (local DB plms_new, 2026-09-30)
- Tenants total 7513 (tenant_status=1: 7447). Tenants with an active contract (tenant_contract_status=1): 2546 (tenant_status=1 among them: 2533; with any email: 2430).
- Past tenants (no status-1 contract, newest contract valid_to_date >= today - 12 months): 753 (same by "any contract ended in window").
- Active landlords: vendor_type_id=2 and vendor_status=1: 239 (2 inactive more); with an approved (status 1) landlord contract: 126. landlord_contract rows: 169 approved, 321 pending.
- Active users (user_type_status true) = 46 (admin 1 + employee 45; 24 inactive). Active users per role (users may hold several roles): super_admin 2, sales_person 6, call_center 3, sales_coordinator 1, sales_head 2, backoffice_executive 1, backoffice_manager 2, are 8, maintenance_coordinator 1, maintenance_supervisor 3, technician 6, facility_manager 1, legal_advisor 1, accountant 6, finance_manager 2, maintenance_contracts_executive 0, maintenance_executive 0, pro 0, reception 0, cashier 1, take_over_supervisor 2, take_over_executive 1, maintenance_engineer 1, are_team_lead 1, ceo 1, md 1, maintenance_manager 0.

## Gotchas
- PostgreSQL: `ilike` for case-insensitive search; a failed insert aborts an open transaction (hence the savepoint in SystemMessenger::log).
- Tenant contracts with status 2 (22 rows) are neither active (1) nor clearly ended.
- Landlord location filter must go through landlord_contract.building_id -> buildings.location_id (vendors.location_id exists but spec filters by building).
- Sent-log screen only maps system template names; `bulk:<id>` keys render raw unless MessageLogController is adjusted.