# ARE Mobile API (`/api/v1`)

Phase 2 of the platform upgrade. JSON API for the ARE mobile app, served by the
`MobileApi` nwidart module on Laravel 13. Spec: `docs/superpowers/specs/2026-09-18-phase2-are-mobile-api-design.md`.

## Switching it on

| Setting | Meaning |
|---|---|
| `MOBILE_API_ENABLED=true` (`.env`) | Every `/api/v1/*` request returns `503 {"code":"disabled"}` while false. Default false. |
| `config/mobileapi.php` (module `Config/config.php`) | `signed_url_minutes` (15), `max_upload_kb` (10240), `image_max_px` (1600), `thumb_px` (300) |
| Permission `mobile_app_login` | Required to obtain a token. Granted to roles `are` and `are_team_lead` by the migration; manage it on the *Mobile Devices* menu's permission screen. |
| Permission `mobile_devices_manage` | Office screen **PLM Module → Masters → Mobile Devices** (clear / block / unblock a phone). `super_admin`, `backoffice_manager`. |
| `onboarding_items_manage`, `onboarding_view`, `onboarding_verify` | Office screens for the move-in checklist (Masters → Onboarding Checklist Items, Operations → Tenant Onboarding). |

## Headers

| Header | When | Purpose |
|---|---|---|
| `Authorization: Bearer <token>` | every call after login | Sanctum personal access token, never expires; revoked by logout, device clear/block |
| `X-Device-Id: <uid>` | every authenticated call | Must equal the device bound at first login |
| `X-App-Version: 1.2.0` | optional | Recorded on the device row and in the activity log |
| `Accept: application/json` | always | |

## Login and the one-device rule

`POST /api/v1/auth/login` `{username, password, device_uid, device_name?, platform? (android|ios), app_version?}`

- The **first** login binds `device_uid` to the user (`mobile_devices`, one row per user).
- Any other device gets `403 {"code":"device_not_registered","message":"This device is not registered. Please contact the administrator."}` until an admin **clears** the row on the office screen; the next login re-binds.
- A blocked device gets `403 {"code":"device_blocked","message":"This device has been blocked. Please contact the administrator."}`.
- Wrong credentials → `422 validation`; user without `mobile_app_login` → `403 forbidden`.
- Response: `{token, user:{id, username, name, mobile, roles[], permissions[]}, buildings:[{id, code, name}]}`.

`POST /auth/logout` revokes the current token. `GET /auth/me` returns user, buildings and the device row.

## Error shape

`{ "message": "...", "code": "...", "errors"?: {field: [..]} }`

| HTTP | code |
|---|---|
| 401 | `unauthenticated` |
| 403 | `device_not_registered`, `device_blocked`, `forbidden`, `not_assigned` (outside the ARE's buildings / assigned to someone else) |
| 404 | `not_found` |
| 409 | `invalid_state` (business rule: already claimed, not completed, already submitted…) |
| 422 | `validation` |
| 503 | `disabled` |

## Building scope

Every list and every write is limited to the buildings the ARE holds **today**:
`are_buildings` × `preferred_buildings`, date-valid on both the header (`assign_from`/`assign_too`) and the pivot (`assign_from`/`assign_to`, which is how the desktop closes a single building). Single source: `Modules\Masters\Services\AreBuildings`.

`GET /buildings` → my buildings with `units`, `unassigned_complaints`, `my_complaints`, `pending_inspections`, `open_onboardings`.

## Endpoints

All below need `auth:sanctum` + `X-Device-Id`. Lists are paginated (`per_page` ≤ 100, default 25) and accept `building_id` and `updated_since` (ISO date-time) for incremental sync.

### Complaints (tickets = `complaint_checklists` rows)

| Method | Path | Notes |
|---|---|---|
| GET | `/complaints?tab=unassigned\|mine\|attended\|completed&q=` | `unassigned`: stage 701/702, no sub-assignee, in my buildings. `mine`: 703 sub-assigned to me, status 0/1. `attended`: status 2. `completed`: status 3 or stage 705. |
| GET | `/complaints/{ticket}` | + `report {service_report_no, status, notes[], photos[] (signed urls), items[], signature_url}`, `flags`, `previous_complaints[]` |
| POST | `/complaints/{ticket}/claim` | Sub-assigns the ticket to me (stage 703) — the rows the coordinator's modal writes. 409 if already assigned. |
| POST | `/complaints/{ticket}/check-in` `{lat?, lng?}` | Creates the service report once (idempotent); status 1. |
| POST | `/complaints/{ticket}/notes` `{text, quick_reason?}` | `quick_reason`: `part_required`, `tenant_not_available`, `access_denied`, `not_our_scope` |
| POST | `/complaints/{ticket}/photos` multipart `photo`, `stage=before\|during\|after` | 201 `{id, stage, url, thumb_url}` |
| POST | `/complaints/{ticket}/items` `{inventory_id, quantity}` / DELETE `…/items/{item}` | material used |
| POST | `/complaints/{ticket}/status` `{status: attended\|completed\|cannot_complete, reason?}` | `attended` → 2 (+ coordinator/supervisor notification), `completed` → 3, `cannot_complete` → note only |
| POST | `/complaints/{ticket}/signature` `{signature?: PNG data URL, skipped?: bool, tenant_remark?}` | Requires status 3 and a signature **or** `skipped` with ≥1 `after` photo. Stage 705, ticket 4, complaint closed, tenant email/SMS. |
| GET | `/complaints/{ticket}/service-report.pdf` | `{url}` (signed) — the report as a PDF |

Statuses: 1 checked-in, 2 attended, 3 completed, 4 closed. Stages: 701 unassigned, 702 assigned, 703 sub-assigned, 705 closed.

### Termination inspections (`termination` rows, type 1)

| Method | Path | Notes |
|---|---|---|
| GET | `/terminations?tab=pending\|inspected\|taken_over` | `pending`: 502, or 503 assigned to me with nothing saved (or referred back). `inspected`: 503 with my saved draft. `taken_over`: 504 submitted by me, last 90 days. `overdue` when `termination_date < today`. |
| GET | `/terminations/{id}` | tenant/unit/contract, `outstanding_rent`, `deposit_cheque`, `open_documents`, `photos`, `previous_inspections`, `refer_back`, `draft` (lines, utilities, totals), `template` (works → sub-works), `pdf_url`, `signature_url` |
| PUT | `/terminations/{id}/inspection` | body below. From 502 the ARE is assigned first (502 → 503, desktop shows *Handover Assigned* to them). First save creates the 503 inspection row; later saves update it and replace the lines. Totals are computed server-side. |
| POST | `/terminations/{id}/photos` multipart `photo`, `type=inspection\|meter_electricity\|meter_water\|key` | → `termination_document` |
| POST | `/terminations/{id}/submit` `{signature: PNG data URL, tenant_remark?}` | Signature → `ACPT` → **504 Taken Over**, PDF, tenant email, back-office notification. 409 unless the contract is still at 503 with a saved draft. Returns the 504 row (new id). |
| GET | `/terminations/{id}/refer-back` | `{comment, at}` or `null` |
| GET | `/terminations/{id}/inspection.pdf` | `{url}` |

Inspection body:
```json
{ "lines": [ {"sub_work_id": 12, "quantity": 2, "amount": 15.5}, {"other_work": "Broken tile", "quantity": 1, "amount": 4.5} ],
  "electricity": {"acc_no": "E1", "close_reading": "1200", "amount": 10},
  "water": {"acc_no": "W1", "close_reading": "300", "amount": 2.5},
  "main_key_status": 1, "maintenance_due": 3, "notes": "…", "remark": "…" }
```
Totals (desktop arithmetic): `total = Σ line.amount`, `elec_water_total = electricity + water`, `net = total + elec_water_total − maintenance_due`.

### Tenant onboarding (move-in checklist, `tenant_onboardings`)

Created automatically when a contract is activated (stage 108) and back-filled by `php artisan onboarding:sync` (scheduled every 15 minutes). Items come from the admin-configurable master list.

| Method | Path | Notes |
|---|---|---|
| GET | `/onboardings?tab=open\|completed` | assigned to me or in my buildings |
| GET | `/onboardings/{id}` | list fields + `items[] {item_id, group, title, input_type, required, needs_photo, entry, photos[]}`, `progress {done, total}`, `signature_url`, `pdf_url` |
| PUT | `/onboardings/{id}/entries` `{entries: [{item_id, value?, remark?, done?, not_applicable?}]}` | partial saves allowed; 409 once completed |
| POST | `/onboardings/{id}/photos` multipart `photo`, `item_id` | 201 |
| POST | `/onboardings/{id}/complete` `{signature: PNG data URL, tenant_remark?}` | 409 with the missing required items listed; sets `completed`, renders the PDF, notifies back-office |
| GET | `/onboardings/{id}/onboarding.pdf` | `{url}` |

Back-office verifies on **Operations → Tenant Onboarding** (status `verified`) and can reassign.

## Files and signed URLs

- Uploads are resized (max 1600 px, 300 px thumbnail, JPEG) and stored under `storage/app/mobile/{complaints|terminations|onboarding}/{id}/`; signatures as PNG next to them. Paths are stored in the same tables/columns the desktop uses, so the office screens render them unchanged.
- Every `url`/`thumb_url`/`pdf_url`/`signature_url` in a response is `GET /api/v1/files?path=…&expires=…&signature=…`, valid **15 minutes**, no token or device header needed (the app hands it to the OS viewer). Tampered or expired → 403.
- `storage/app/mobile` must be writable by the web user and included in backups.

## Activity log

Every successful non-GET API call writes an `activity_log` row with `log_name = mobile`, `causer` = the ARE, and properties `route`, `device_uid`, `app_version`, `params`. Login writes `login`; the office screen writes `device cleared/blocked/unblocked`.

## Desktop parity

The business rules were extracted into services the desktop now calls too, so both write identical rows:

| Service | Desktop caller |
|---|---|
| `Modules\Maintenance\Services\TicketAttendance` | `ComplaintStageController::checkIn`, `serviceReportStatusButtonUpdate` (attended), `generateServiceReport` (signature close) |
| `Modules\BackOffice\Services\TerminationWorkflow` | `TenantTerminationController::tenantTerminationStage`, `storeGroupAssign` |
| `Modules\BackOffice\Services\TerminationInspection` | `terminationInspectionStore`, `terminationInspectionUpdate` |
| `Modules\BackOffice\Services\TenantOnboarding` | contract activation in `TenantStageController` and `TenantContractEditController` |

Deviations from the pre-Phase-2 desktop, all deliberate:
- Desktop check-in now creates **one service report per ticket** (was one per technician per complaint); `reportFor()` reads both layouts.
- Photo `stage` (integer column) is 1 = before, 2 = after.
- Two latent bugs fixed in the extracted termination code: water account no. was copied from the electricity amount; `VacantVacancyLoss.unit_type_id` read an undefined `$request`.

## Tests

`vendor/bin/phpunit --testsuite MobileApi` (17 feature tests, run against the `.env` DB copy inside transactions) plus unit tests for `AreBuildings`, `TicketAttendance`, `TerminationWorkflow`, `TerminationInspection`, `TenantOnboarding` and the office screens (`--exclude-group smoke --exclude-group smoke-one`).
