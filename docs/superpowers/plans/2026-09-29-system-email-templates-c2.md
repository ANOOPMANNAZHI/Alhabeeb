# System Email Templates — C2 (Tenant & Landlord Emails) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move the tenant- and landlord-facing automatic messages (complaint registered / completed / closed, key accepted ×2, renewal form, inspection details, termination details, birthday, PDC due reminder) onto system templates sent by `SystemMessenger`, and delete the Mailables they replace.

**Architecture:** Builds on C1 (branch `feature/system-email-templates`, this branch `feature/system-email-templates-c2` stacks on it). Each send site calls one small method on a domain service (`ComplaintMessages`, `TenantMessages`) that builds values and calls `SystemMessenger::send`. The inspection and termination tables stay in Blade partials rendered in code and passed as block placeholders. System templates offer only the placeholders their send site fills.

**Tech Stack:** Laravel 5.7 modules, PostgreSQL, PHPUnit 7 (unit + feature with `DatabaseTransactions`, `Mail::fake`, SMS sender stub).

**Spec:** `docs/superpowers/specs/2026-09-29-system-email-templates-design.md` (C2 section).

## Global Constraints

- Production PHP < 7.4 (no arrow functions, `?->`, `match`, typed properties, `??=`); Laravel 5.7 APIs.
- `$PHP` = `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe`. In Git Bash set `MSYS_NO_PATHCONV=1` before passing `/url` arguments to PHP scripts.
- Active off = not sent (logged `skipped_off`); Send SMS switch per template; defaults: SMS **on** only for `pdc_due_reminder`; `termination_details` arrives **off**.
- Default wording signed "Al Habib Team"; today's wording kept in meaning.
- Delivery errors never break the user's action (messenger already guarantees this).
- Deploy: code first, then migrations.

## Review Focus

1. **Tenant with only a personal email / only a contact email / neither** → email goes to whichever exists (complaint & key: contact first; birthday & bounce: personal first — as today); neither → `skipped_no_address`, action still completes. Tests in Tasks 3–4.
2. **Renewal form when the template is off or the send fails** → the user still gets the PDF (download) instead of a false "Mail sent"; `email_count` only increments on `sent`. Test in Task 4.
3. **PDC reminder with SMS switched off or template off** → no SMS, one log row per PDC, cron completes. Test in Task 6.
4. **Inspection/termination email for a contract with no checklist rows** → block renders empty tables without error. Test in Task 5.
5. **Complaint registered for a non-tenant complaint (cmp_type ≠ 1)** → nothing sent, as today. Test in Task 3.

---

## File Structure

| File | Responsibility |
|---|---|
| `Modules/BackOffice/Services/MessagePlaceholders.php` (modify) | system keys offer common + their own placeholders only; `blockSamples()` |
| `Modules/BackOffice/Services/SystemTemplateDefaults.php` (modify) | C1 placeholder lists completed; 10 C2 defaults |
| `Modules/BackOffice/Services/MessageValues.php` (modify) | unit = unit_no; `tenant()`, `complaint()`, `vendor()` |
| `Modules/BackOffice/Services/SystemMessenger.php` (modify) | truncate log fields (C1 deferred minor 5) |
| `Modules/BackOffice/Http/Controllers/MessageTemplateController.php` (modify) | preview passes block samples |
| `database/migrations/2026_09_30_000003_seed_c2_system_templates.php` (create) | seed C2 keys |
| `Modules/BackOffice/Services/ComplaintMessages.php` (create) | registered / completed / closed |
| `Modules/BackOffice/Services/TenantMessages.php` (create) | key accepted, renewal form, inspection, termination, birthday, PDC reminder |
| `Modules/BackOffice/Resources/views/Email/partials/inspection_details.blade.php`, `termination_details.blade.php` (create) | table blocks moved out of the old views |
| Controllers (modify): `Maintenance/MaintenanceController@store`, `Maintenance/ComplaintStageController@generateServiceReport` + `@complaintCloseModalAction`, `BackOffice/KeyController@keyAcceptAginstLandlordTenantStore`, `BackOffice/TenantRenewalController@generatePDF`, `BackOffice/TenantTerminationController@terminationEmail` + `@inspectionSendMail`, `Sales/TenantController@tenantBirthdayNotification`, `BackOffice/PdcController@pdcExpiryReminderSms` | call the services |
| Delete: `ComplaintRegisterEmail`, `ComplaintCloseEmail`, `KeyHandoverEmail`, `KeyHandoverEmailLandlord`, `RenewalFormPdfEmail`, `InspectionSendEmail`, `TerminationTenantEmail`, `TenantBirthdayEmail` + their views | replaced (`ComplaintStageEmail` stays until C3) |
| Tests: `tests/Unit/MessagePlaceholdersTest.php`, `tests/Unit/SystemTemplateDefaultsTest.php` (modify); `tests/Feature/ComplaintMessagesTest.php`, `tests/Feature/TenantMessagesTest.php` (create) | |

---

### Task 1: Placeholder scoping, value helpers, log truncation

**Interfaces — Produces:**
- `MessagePlaceholders::forTemplate($audience, $key)`: when `$key` is registered → common + that key's placeholders only (audience ignored); otherwise unchanged.
- Placeholder entries may carry `'block' => true`; `MessagePlaceholders::samples()` excludes blocks; `MessagePlaceholders::blockSamples($audience, $key)` returns name ⇒ sample HTML for blocks.
- `MessageValues::contract()` `unit` = `unit_no` (fallback `unit_code`); `MessageValues::tenant($tenant)` → tenant_name, mobile; `MessageValues::complaint($c)` → complaint_no, complaint_date, tenant_name, building, unit, location; `MessageValues::vendor($v)` → landlord_name.
- `SystemMessenger` log rows truncated to column sizes (recipient_name 150, recipient 190, subject 255, template_key 100).

- [ ] **Step 1: Failing tests**
  - `MessagePlaceholdersTest`: `registerSystem('t_scope', ['cheque_no' => [...]])` → `forTemplate('tenant','t_scope')` keys == `['company_name','cheque_no','today']` (sorted); a block entry is absent from `samples()` and present in `blockSamples()`.
  - `SystemTemplateDefaultsTest` (existing test already checks every placeholder used is registered) — with scoping on, it fails for `pdc_bounce_are` (`{employee_name}`) and `pdc_bounce_tenant` (`{tenant_name}` …) until their lists are completed.
  - `SystemMessengerTest`: `MessageValues::contract` on a contract whose unit has `unit_no` → `unit` equals `unit_no`; a 300-char recipient name is logged truncated (no exception, row written).
- [ ] **Step 2: Run** → failures as described.
- [ ] **Step 3: Implement** the Produces list. C1 defaults: `pdc_bounce_are` placeholders = `employee_name` + contract set (tenant_name, contract_no, building, unit) + cheque set; `pdc_bounce_tenant` = contract set + cheque set. Preview in `MessageTemplateController::preview`: pass `MessagePlaceholders::blockSamples($audience, $key)` as `$blocks` to `render()`. Editor dropdown labels blocks "(table)".
- [ ] **Step 4: Run** unit + feature suites → green (ExampleTest pre-existing failure only).
- [ ] **Step 5: Commit** — `feat(system-templates): templates offer only their own placeholders; tenant/complaint/vendor values`

### Task 2: C2 defaults + seed

Keys, audience, switches (all SMS off except where noted), placeholders, default wording (HTML paragraphs, "Regards,<br>Al Habib Team"):

| Key | Aud. | Active | Placeholders | Subject / SMS gist |
|---|---|---|---|---|
| `complaint_registered` | tenant | on | complaint set | "Complaint {complaint_no} registered" — received, team will get back at the earliest |
| `complaint_completed` | tenant | on | complaint set | "Complaint {complaint_no} completed" — attended by the Al Habib Maintenance Team, thanks |
| `complaint_closed` | tenant | on | complaint set | "Complaint {complaint_no} closed" — work completed, contact us with questions |
| `key_accepted_tenant` | tenant | on | tenant_name, building, unit | "Keys received - {building}, unit {unit}" — confirms keys received on {today} |
| `key_accepted_landlord` | landlord | on | landlord_name, building, unit | same for the landlord |
| `renewal_form` | tenant | on | tenant set + contract set | "Tenancy renewal form - {building}, unit {unit}" — today's renewal letter text with {contract_end}; PDF attached in code |
| `inspection_details` | tenant | on | tenant + contract set + block `inspection_details` | "Inspection report - contract {contract_no}" |
| `termination_details` | tenant | **off** | tenant + contract set + block `termination_details` | "Termination inspection details - contract {contract_no}" |
| `tenant_birthday` | tenant | on | tenant_name | "Happy Birthday" — today's wish text |
| `pdc_due_reminder` | tenant | on, **SMS on**, no email | tenant_name, contract_no, building, unit, cheque_no, amount, cheque_date | SMS: "Dear {tenant_name}, your cheque No. {cheque_no} of amount {amount} is due on {cheque_date}. Please ensure sufficient funds are available in your account to avoid penalty charges. - Al Habib" |

- [ ] **Step 1:** extend `SystemTemplateDefaultsTest::test_c1_keys_exist…` pattern with a C2 test (all 10 keys exist; `termination_details` off; `pdc_due_reminder` sms_enabled and no email subject/body; `renewal_form` body mentions `{contract_end}`) → fails.
- [ ] **Step 2:** add the defaults (block placeholders registered with `'block' => true` and a small sample table); run → green (the existing completeness test also covers them).
- [ ] **Step 3:** migration `2026_09_30_000003_seed_c2_system_templates.php` (same shape as C1 seed, KEYS = the 10); run it; verify 10 rows.
- [ ] **Step 4: Commit** — `feat(system-templates): C2 default templates`

### Task 3: Complaint messages

`ComplaintMessages::registered(ComplaintEnquiry $c)`, `::completed(ComplaintEnquiry $c)`, `::closed(ComplaintEnquiry $c)` — recipient = complaint tenant: email contact → personal (registered, closed) / personal → contact (completed, as today); mobile `tenant_contact_no ?: tenant_residence_tel`; values `MessageValues::complaint($c)`; related `['complaint', $c->id]`; return the messenger result.

- [ ] **Step 1: Failing feature test** `ComplaintMessagesTest` (pick a complaint with a tenant that has an email; set emails in-transaction to control the cases): each method sends one `TemplatedMail` to the expected address with the complaint no. in the subject; tenant without email → `skipped_no_address`; `complaint_completed` with SMS switched on → SMS stub called (replaces today's "SMS when no email").
- [ ] **Step 2: Run** → class missing.
- [ ] **Step 3: Implement** and wire:
  - `MaintenanceController@store` (~:226-233): inside `if($request->cmp_type == 1)` replace the email block with `ComplaintMessages::registered($complaint);`.
  - `ComplaintStageController@generateServiceReport` (~:1834-1852): replace from `$complaintEnqu->subject = "Complaint Completed";` through the `sendSms` else-branch with `ComplaintMessages::completed($complaintEnqu);`.
  - `ComplaintStageController@complaintCloseModalAction` (~:1332-1337): replace with `ComplaintMessages::closed(ComplaintEnquiry::find($complaint_id));`.
  - Remove unused imports (`ComplaintRegisterEmail`, `ComplaintCloseEmail`); `ComplaintStageEmail` stays (assign sites, C3).
- [ ] **Step 4:** controller-level test for `store` is out of reach (large form); instead assert in the feature test that `ComplaintMessages::registered` is a no-op-safe call for a complaint whose `tenant_id` is null (returns skipped, no exception). `$PHP -l` on both controllers. Run → green.
- [ ] **Step 5: Commit** — `feat(system-templates): complaint registered/completed/closed via templates`

### Task 4: Key accepted + renewal form

- `TenantMessages::keyAccepted(Key $key)`: tenant key → `key_accepted_tenant` to tenant (contact → personal); landlord key → `key_accepted_landlord` to vendor (`vendor_contact_email`, `vendor_contact_no`); values building/unit from `$key->building_id` / `$key->unit_id`; related `['key', $key->id]`.
- `TenantMessages::renewalForm(TenantContract $c, $pdfData, $pdfName, array $cc)`: `renewal_form` to tenant (contact → personal) with attachment `['data' => $pdfData, 'name' => $pdfName, 'mime' => 'application/pdf']`, cc = sender + ARE (nulls dropped by `MessageRecipient`); returns the email status.
- Controller `generatePDF`: build the PDF as today; `$status = TenantMessages::renewalForm(...)`; `sent` → increment `email_count`, flash success, redirect as today; otherwise → if `failed` flash error "The email could not be sent (see Mail → Sent log). The form has been downloaded instead."; return `$pdf->download($pdfName)`.
- Controller key store: after `$Key->save()`, replace both email branches with `TenantMessages::keyAccepted($Key);`.

- [ ] **Step 1: Failing tests** in `TenantMessagesTest`: key accepted tenant & landlord (build an unsaved-then-saved `Key` in transaction for an existing tenant / vendor with email) → one mail each to the right address, subject contains the building; renewal: `GET route('generate-pdf-email' route name — check web.php)` for a contract whose tenant has email → `Mail::assertSent` with one attachment and cc, `email_count` +1; same with template off → response is a PDF download (`Content-Type application/pdf`) and `email_count` unchanged.
- [ ] **Step 2–4:** run (fail) → implement → run (pass). `$PHP -l`.
- [ ] **Step 5: Commit** — `feat(system-templates): key accepted and renewal form via templates`

### Task 5: Inspection + termination (blocks)

- Create partials by moving the table markup out of `Email/inspection_send_mail.blade.php` (everything between the intro sentence and "Sincerely") into `Email/partials/inspection_details.blade.php`, and the same for `termination_tenant_mail.blade.php` → `Email/partials/termination_details.blade.php`. Partials take `$termination, $tenantContract, $terminationChecklistOther, $groupedWork` (and whatever the old Mailable passed — read each Mailable's `build()` and pass the same data). Strip markdown-only syntax; keep the inline table styles.
- `TenantMessages::inspection(Termination $t, TenantContract $c)` / `::terminationDetails(...)`: render the partial with `view(...)->render()`, send `inspection_details` / `termination_details` with `blocks`, recipient tenant (contact → personal; the termination email: contact → personal as today), values tenant + contract, related `['termination', $t->id]`. Return email status.
- Controllers: `inspectionSendMail` → call, flash "Mail Send Successfully" only when `sent`; otherwise flash the matching message ("Tenant email not found" / "This email is switched off in Mail → Templates" / "The email could not be sent (see Mail → Sent log)"), redirect as today (today the method returns nothing when there is no email — it now always redirects). `terminationEmail` → same pattern (template arrives off, so by default it shows the switched-off message).

- [ ] **Step 1: Failing tests**: pick a termination with checklist rows; `inspection()` → mail sent, logged body contains a `<table` and the contract no.; a contract with no checklist rows → no exception; `terminationDetails()` with template off → `skipped_off`; switch on → sent and the tenant's name appears (old blank-name bug). Controller: `GET inspectionSendMail` route → redirect to `handoverAssigned` with success flash.
- [ ] **Step 2–4:** run → implement → run. Screenshot the rendered inspection email (through `buildView()`, as in C1's visual check).
- [ ] **Step 5: Commit** — `feat(system-templates): inspection and termination details via templates with table blocks`

### Task 6: Birthday + PDC due reminder (cron)

- `TenantMessages::birthday(Tenant $t)` → `tenant_birthday` (personal → contact, as today).
- `TenantMessages::pdcDueReminder(Pdc $p)` → `pdc_due_reminder`, recipient mobile `tenant_contact_no` only (as today), values contract + cheque; returns the SMS status.
- `tenantBirthdayNotification`: keep the 10:00 guard and the query; loop body → `TenantMessages::birthday($tenant)`.
- `pdcExpiryReminderSms`: keep guard, query, try/catch and Log lines; loop body → `$r = TenantMessages::pdcDueReminder($pdc)` and count `sent` / other for the existing summary log (drop the manual "no contact number" skip — the messenger logs `skipped_no_mobile`).
- Tests (call the service methods directly; the 10:00 guard makes the cron entry untestable at other times): birthday → one mail; reminder with SMS on → stub called with the cheque no. and amount; SMS off → no SMS call and no email; tenant without mobile → `skipped_no_mobile`.
- [ ] Steps: failing tests → implement/wire → green → **Commit** — `feat(system-templates): birthday email and PDC due reminder SMS via templates`

### Task 7: Remove old Mailables + wrap-up

- [ ] Delete the 8 Mailables and their views (list in File Structure); `grep -rn` for each class/view name over `Modules app resources` → only backup copies (`bkup*`, `*--.php`) remain.
- [ ] Full suite; PHP 7.4-syntax scan over changed files; `$PHP -l`.
- [ ] Final whole-branch review (fresh reviewer, most capable model) with this plan's Review Focus; fix Critical/Important with RED→GREEN tests.
- [ ] Hand-over: deploy — code first, then migration `2026_09_30_000003` (after A and C1 migrations); review the 10 templates; `termination_details` arrives off.
