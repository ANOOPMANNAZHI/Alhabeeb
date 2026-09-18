# Phase 2 — ARE Mobile API — Design

**Date:** 2026-09-18
**Branch:** `platform-upgrade` (continues after Phase 1; not merged until Phase 3 is done)
**Parent spec:** `docs/superpowers/specs/2026-09-18-platform-modernisation.md`
**Requirements source:** `docs/mobile-app/PLMS_Mobile_App_Phase1_Phase2.docx` (narrowed to the ARE role by the user on 2026-09-18)

## Purpose

A JSON API on the upgraded PLMS (Laravel 13) for a mobile app used by the **ARE** (role `are`, and `are_team_lead`) — the field executive for a set of buildings. Three jobs, done at the unit on a phone instead of later on the desktop:

1. **Tenant onboarding checklist** — new feature; nothing records move-in condition or handover of keys/meters/documents today.
2. **Complaint attending** — the ARE works maintenance tickets the way a technician does today.
3. **Handover termination inspection** — the ARE performs the move-out inspection and submits it, moving the contract to *Taken Over*.

Decisions taken with the user:

| Topic | Decision |
|---|---|
| Complaint role | ARE attends like a technician (check-in, notes, photos, items, status, tenant signature) |
| Complaint queue | Tickets sub-assigned to the ARE **plus** unassigned tickets in the ARE's buildings, which the ARE can claim |
| Inspection | ARE performs the inspection itself and submits it (stands in for the take-over supervisor) |
| Onboarding items | Admin-configurable master list |
| Login | Username + password now (Sanctum token, no expiry); SMS OTP later |
| Device policy | First login binds the device; any other device is refused with *"This device is not registered. Please contact the administrator."* until an admin clears it |

Out of scope: OTP login, push notifications, tenant/technician/landlord apps, Arabic, offline sync beyond `updated_since` lists, move-in↔move-out side-by-side UI (the data model supports it).

## Architecture

- **New nwidart module `MobileApi`** (`Modules/MobileApi`). Owns: `Routes/api.php` under prefix `/api/v1`, controllers, form requests, JSON resources, the device/token policy, and the office "Mobile devices" screen. It contains no business rules.
- **Business rules move into services** in the modules that own the data, and the desktop controllers are changed to call the same services:
  - `Modules\Maintenance\Services\TicketAttendance` — claim, check-in, note, photo, item, status change, signature/close. Extracted from `ComplaintStageController` (the technician service-report actions).
  - `Modules\BackOffice\Services\TerminationInspection` — load checklist template, save draft, submit (signature → Taken Over → PDF → mail → notify). Extracted from `TenantTerminationController`.
  - `Modules\BackOffice\Services\TenantOnboarding` — new.
- **Auth:** Laravel Sanctum personal access tokens. Guard `sanctum` on all `/api/v1` routes except login. Tokens never expire; they are deleted on logout, on device clear/block, and when the user is deactivated.
- **Authorisation:** same spatie permissions as the desktop, checked in the services (so web and API agree). New permissions: `mobile_app_login`, `mobile_devices_manage`, `onboarding_items_manage`, `onboarding_view`, `onboarding_verify`. Role `are` gets `mobile_app_login` + the complaint-attending permissions technicians have; `are_team_lead` the same.
- **Scoping:** every list is limited to the ARE's buildings — `are_buildings` (user_id, date-valid on `assign_from`/`assign_too`) joined with `preferred_buildings.building_id`. A `BuildingScope` value object (`Modules\Masters\Services\AreBuildings::forUser($user): Collection<int>`) is the single source of this rule and is reused by the desktop `scopeAreFilter`s over time.
- **Files:** uploads go to `storage/app/mobile/{complaints|terminations|onboarding}/{id}/`, resized to max 1600 px with a 300 px thumbnail (intervention/image, as the desktop does), and are exposed only through signed URLs valid 15 minutes (`URL::temporarySignedRoute`). Signatures are PNG data URLs stored the same way the desktop stores `tenant_signature`.
- **Audit:** every API write is logged with spatie/activitylog (`log_name = mobile`, properties: device id, app version). The existing `os` column on `termination` is set to the device platform.
- **Versioning & errors:** `/api/v1` only. Errors are `{ "message": "...", "code": "snake_case", "errors": {field: [..]} }` with 401 (no/invalid token), 403 (device not registered / no permission / outside my buildings), 404, 409 (state conflict, e.g. claiming a ticket someone else took), 422 (validation).

## Endpoints

All responses are JSON resources. Lists are paginated (`per_page` ≤ 100) and accept `updated_since=<ISO datetime>` for incremental refresh.

### Auth & device

| Method | Path | Notes |
|---|---|---|
| POST | `/auth/login` | `username, password, device_uid, device_name, platform, app_version`. Checks: user active, has `mobile_app_login`, device policy (below). Returns `{ token, user, buildings }`. |
| POST | `/auth/logout` | Deletes the current token. |
| GET | `/auth/me` | User, roles, permission names, buildings, device. |

Device policy in `MobileApi\Services\DevicePolicy::authorise(User, deviceUid)`:
1. Load `mobile_devices` for the user.
2. No row → create one (`registered_at = now`) and continue (first login binds).
3. Row exists, `device_uid` matches → update `last_seen_at`, `app_version`; continue. If `blocked_at` set → 403 `device_blocked`, message *"This device has been blocked. Please contact the administrator."*
4. Row exists, `device_uid` differs → 403 `device_not_registered`, message *"This device is not registered. Please contact the administrator."* Nothing is written.

Every authenticated request re-checks step 3/4 via middleware `mobile.device` using the `X-Device-Id` header (so a cleared device is cut off immediately, not at next login). The Sanctum token name is the device uid.

### Buildings

| GET | `/buildings` | My buildings with unit counts and open-item counts (unassigned complaints, pending inspections, open onboardings). |

### Complaints (tickets = `complaint_processes` rows)

| Method | Path | Notes |
|---|---|---|
| GET | `/complaints?tab=unassigned\|mine\|attended\|completed&building_id=&q=` | `unassigned`: stage 701/702 in my buildings with no sub-assignee. `mine`: stage 703 sub-assigned to me. `attended`/`completed`: mine by service-report status. |
| GET | `/complaints/{ticket}` | Complaint text, priority, category, tenant (name, phone), building/unit, previous complaints on the unit (last 5), hold/legal/landlord-maintained banner flags, service report (notes, photos, items, status), check-in. |
| POST | `/complaints/{ticket}/claim` | Sub-assigns to me: writes `complaint_processes.sub_assigned_to`, `complaint_users` (role `are`), moves to 703, creates the `complaint_service_report` — identical rows to the coordinator's sub-assign modal. 409 if already sub-assigned. |
| POST | `/complaints/{ticket}/check-in` | `lat, lng`; stored in `complaint_service_report_notes` (stage `checkin`) as the desktop check-in does; triggers the existing tenant SMS. |
| POST | `/complaints/{ticket}/notes` | `text, quick_reason?` (`part_required`, `tenant_not_available`, `access_denied`, `not_our_scope`) |
| POST | `/complaints/{ticket}/photos` | multipart `photo`, `stage=before\|during\|after` → `complaint_service_report_image` |
| POST | `/complaints/{ticket}/items` / DELETE `/items/{item}` | inventory id + quantity → `complaint_service_report_inv` |
| POST | `/complaints/{ticket}/status` | `status=attended\|completed\|cannot_complete, reason?` → `complaint_assign_status` exactly as the desktop status modal |
| POST | `/complaints/{ticket}/signature` | `signature` (PNG data URL) or `skipped=true` + after-photo required → closes the ticket (stage 705), generates the service-report PDF, sends the existing tenant notifications. |
| GET | `/complaints/{ticket}/service-report.pdf` | Signed URL to the PDF. |

### Termination inspections (`termination` rows)

| Method | Path | Notes |
|---|---|---|
| GET | `/terminations?tab=pending\|inspected\|taken_over&building_id=` | `pending`: stage 502 (Handover Unassigned) or 503 assigned to me, in my buildings. `inspected`: draft saved by me, not submitted. `taken_over`: 504 submitted by me (last 90 days). Overdue flag when `termination_date < today`. |
| GET | `/terminations/{id}` | Tenant, unit, contract period, termination date/type, outstanding rent, deposit cheque, open documents, tenant phone, previous inspections on the unit, **checklist template** (works → sub-works with default rate), saved draft. |
| PUT | `/terminations/{id}/inspection` | Saves the whole checklist: lines `{work_id, sub_work_id, quantity, amount, other_work?}` → `termination_checklists`; utilities `{electricity_acc_no, electricity_close_reading, electricity_amount, water_…}`; `main_key_status`; `notes`, `remark`. On first save from stage 502 the ARE is assigned (`termination.assigned_to`, `termination_users`) and the stage moves to 503 so the desktop shows it as Handover Assigned to this user. |
| POST | `/terminations/{id}/photos` | multipart → `termination_document` (`termination_doc_type = inspection`, `meter_electricity`, `meter_water`, `key`) |
| POST | `/terminations/{id}/submit` | `signature` (PNG) + `tenant_remark?`. Recomputes totals (`termination_total_amount`, `termination_total_elec_water_amount`, `termination_discount_maintenance_due`, `termination_net_amount` — same arithmetic as the desktop's Taken Over action), sets `termination_takenover_date`, stage 504, generates the inspection PDF, emails the tenant, notifies back-office. 409 if the contract is no longer at 503. |
| GET | `/terminations/{id}/refer-back` | Comment when back-office referred it back (`termination_refer_back`, `supervisor_comment`); the item reappears in `pending`. |
| GET | `/terminations/{id}/inspection.pdf` | Signed URL. |

### Tenant onboarding (new)

Tables:

- `onboarding_checklist_items` — `id, group_name, title, input_type (check|text|number|count|photo|meter), required (bool), needs_photo (bool), sort_order, is_active, timestamps`. Seeded with the eight items agreed with the user (contract & house rules, documents collected, keys/access cards count, electricity account + opening reading + meter photo, water account + opening reading + meter photo, unit condition per room, payment instructions/PDC received, tenant signature is implicit).
- `tenant_onboardings` — `id, tenant_contract_id, unit_id, building_id, assigned_user_id, status (open|completed|verified|cancelled), started_at, completed_at, verified_by, verified_at, tenant_signature (path), tenant_remark, pdf_path, timestamps`.
- `tenant_onboarding_entries` — `id, tenant_onboarding_id, item_id, value (text), remark, done (bool), not_applicable (bool), timestamps`; photos in `tenant_onboarding_photos` (`entry_id, path, thumbnail`).

Creation: an `Illuminate` event listener on tenant contract activation (the point where today's code sets the contract active and hands over keys — `TenantContractController` approval) creates a `tenant_onboardings` row per new contract, `assigned_user_id` = the ARE of the building (first date-valid `are_buildings` user). A backfill command `onboarding:backfill --since=` creates rows for contracts activated since a date.

| Method | Path | Notes |
|---|---|---|
| GET | `/onboardings?tab=open\|completed&building_id=` | mine (assigned or in my buildings), tenant, unit, contract start, progress `done/total`. |
| GET | `/onboardings/{id}` | Active items in `sort_order` merged with saved entries and photos; tenant and unit info. |
| PUT | `/onboardings/{id}/entries` | Array of `{item_id, value?, remark?, done, not_applicable}`; upsert. 422 if a required item is neither done nor N/A **at completion time only** (drafts may be partial). |
| POST | `/onboardings/{id}/photos` | multipart `photo, item_id` |
| POST | `/onboardings/{id}/complete` | `signature` (PNG), `tenant_remark?` → validates required items, sets `completed`, generates the onboarding PDF, notifies back-office (existing notification table + email). |

Desktop (Blade, existing layout):

- Masters → **Onboarding Checklist Items**: list / add / edit / deactivate / reorder (permission `onboarding_items_manage`).
- BackOffice → **Tenant Onboarding**: list (building, tenant, ARE, status, progress), view (entries, photos, signature, PDF), **Verify** button (`onboarding_verify`) and **Reassign ARE**. Menu rows inserted by migration under PLM Module → Operations.
- Settings → **Mobile Devices**: user, device name, platform, app version, registered/last seen, actions **Clear device** (deletes the row and its tokens; next login binds again) and **Block/Unblock** (permission `mobile_devices_manage`).

## Data flow (complaint close, as the representative case)

App `POST /complaints/{ticket}/signature` → `mobile.device` middleware re-validates device → `SignatureRequest` validates PNG → `ComplaintController::signature` → `TicketAttendance::close($ticket, $user, $signaturePng)`:
1. asserts the ticket is sub-assigned to `$user` and status is `completed` (else 409),
2. stores the signature file, sets `complaint_service_report.tenant_signature`,
3. moves `complaint_processes.work_flow_processes_code` to 705, writes `complaint_users` status,
4. renders the existing service-report Blade to PDF (dompdf) to `storage/app/mobile/complaints/{id}/service-report.pdf`,
5. dispatches the existing tenant email/SMS notifications,
6. logs activity (`mobile`),
7. returns the ticket resource. The desktop's `ComplaintStageController` closure action is changed to call the same `close()`.

## Error handling

- Unknown/invalid token → 401 `unauthenticated`. Device mismatch/blocked → 403 with the exact user-facing messages above. Not my building → 403 `outside_assigned_buildings`. Ticket/termination in the wrong stage → 409 `invalid_state` with the current stage in the payload. Validation → 422 standard Laravel shape under `errors`. Uploads > 10 MB or non-image → 422.
- Service exceptions are `DomainException` subclasses (`InvalidStateException`, `NotAssignedException`) mapped to the codes above in the module's exception renderer; the desktop keeps its flash-message behaviour by catching the same exceptions.

## Testing

- Feature tests per endpoint (`Modules/MobileApi/Tests/Feature`) against the `.env` database copy using `actingAs` with Sanctum: auth + device policy (bind, mismatch, blocked, cleared → re-bind), scoping (ticket outside my buildings → 403), each state transition, and one end-to-end flow each for complaint (claim → check-in → note → photo → status → signature → PDF exists, stage 705), inspection (draft → photo → submit → stage 504, totals match desktop arithmetic on a known contract), onboarding (create on activation → entries → complete → PDF, required-item validation).
- Unit tests for `AreBuildings::forUser`, `DevicePolicy`, and the inspection totals calculation.
- The Phase 1 smoke sweep is extended with the new desktop screens.

## Migration / rollout

- Migrations: `mobile_devices`, the three onboarding tables, permissions + role grants, menu rows. All additive.
- `composer require laravel/sanctum`; publish + run its migration (`personal_access_tokens`).
- `config/cors.php` allows the app (mobile apps do not need CORS, but the test harness/web debug does).
- Feature flag: `MOBILE_API_ENABLED=false` in `.env` short-circuits `/api/v1` with 503 until the app is released.
