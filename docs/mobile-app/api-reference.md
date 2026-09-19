# PLMS ARE Mobile API — Reference

Base URL: `https://<plms-host>/api/v1` (local: `http://plms_backup.test/api/v1`). All bodies are JSON unless marked multipart. Every example below was captured from the running API (identifiers anonymised).

See also: **[app-workflow.md](app-workflow.md)** (screens, state machines, offline strategy, Flutter snippets), **[openapi.json](openapi.json)**, **[plms-mobile.postman_collection.json](plms-mobile.postman_collection.json)**, and the server-side notes in `docs/upgrade/mobile-api.md`.

## Conventions

| | |
|---|---|
| Auth | `Authorization: Bearer <token>` (Sanctum, never expires) + `X-Device-Id: <device_uid>` on every call except login and `/files` |
| Optional | `X-App-Version: 1.0.0` — recorded on the device and in the activity log |
| Always | `Accept: application/json` |
| Dates | ISO-8601 UTC strings, e.g. `2026-09-19T06:03:56.000000Z` |
| Money | numbers (OMR, 3 decimals) |
| Lists | Laravel pagination: `data[]`, `links{first,last,prev,next}`, `meta{current_page,last_page,per_page,total,…}` |
| Feature flag | `MOBILE_API_ENABLED=false` on the server → every call returns `503 {"code":"disabled"}` |

### Error shape

```json
{ "message": "human readable", "code": "machine_code", "errors": { "field": ["…"] } }
```

| HTTP | `code` | When | App should |
|---|---|---|---|
| 401 | `unauthenticated` | token missing / revoked (logout, device cleared or blocked) | wipe token, go to login |
| 403 | `device_not_registered` | another phone is bound to this user | show the message verbatim, stay logged out |
| 403 | `device_blocked` | admin blocked this phone | same |
| 403 | `forbidden` | user lacks `mobile_app_login` | show message |
| 403 | `not_assigned` | record outside my buildings / assigned to someone else | drop it from local lists |
| 404 | `not_found` | unknown id | drop it from local lists |
| 409 | `invalid_state` | business rule (already claimed, not completed, already submitted, required items missing) | show `message`, re-fetch the record |
| 422 | `validation` | bad input; `errors` keyed by field | show per-field |
| 503 | `disabled` | API switched off | show maintenance screen |

Verbatim device messages: **"This device is not registered. Please contact the administrator."** and **"This device has been blocked. Please contact the administrator."**

### Codes used in payloads

| Field | Values |
|---|---|
| Complaint `stage` | 701 Unassigned · 702 Assigned · 703 Sub-assigned (mine) · 705 Closed |
| Complaint `status` / `report.status` | 0 open · 1 checked-in · 2 attended · 3 completed · 4 closed |
| Photo `stage` (complaints) | `before` · `during` · `after` (stored as 1 / 2) |
| Termination `stage` | 502 Handover Unassigned · 503 Handover Assigned · 504 Taken Over · 505 Terminated |
| Termination `termination_type_status` | 1 normal · 2 early · 3 legal (desktop labels) |
| Onboarding `status` | `open` · `completed` · `verified` · `cancelled` |
| Onboarding `input_type` | `check` · `text` · `number` · `count` · `photo` · `meter` |

## Endpoints

- **Auth & device**
  - `POST /auth/login` — Login and bind the device
  - `POST /auth/logout` — Revoke the current token
  - `GET /auth/me` — Current user, buildings and device
  - `GET /buildings` — My buildings with open-item counts
  - `GET /inventories` — Inventory master (material picker)
- **Complaints**
  - `GET /complaints` — List tickets by tab
  - `GET /complaints/{ticket}` — Ticket detail with service report
  - `POST /complaints/{ticket}/claim` — Claim an unassigned ticket
  - `POST /complaints/{ticket}/check-in` — Check in at the unit
  - `POST /complaints/{ticket}/notes` — Add a note
  - `POST /complaints/{ticket}/photos` — Upload a photo
  - `POST /complaints/{ticket}/items` — Add material used
  - `DELETE /complaints/{ticket}/items/{item}` — Remove a material line
  - `POST /complaints/{ticket}/status` — Set attended / completed / cannot complete
  - `POST /complaints/{ticket}/signature` — Close with tenant signature
  - `GET /complaints/{ticket}/service-report.pdf` — Service report PDF (signed URL)
- **Termination inspections**
  - `GET /terminations` — List handovers by tab
  - `GET /terminations/{id}` — Handover detail + checklist template + draft
  - `PUT /terminations/{id}/inspection` — Save the inspection (draft)
  - `POST /terminations/{id}/photos` — Upload an inspection photo
  - `POST /terminations/{id}/submit` — Tenant signs → Taken Over
  - `GET /terminations/{id}/refer-back` — Back-office refer-back comment
  - `GET /terminations/{id}/inspection.pdf` — Inspection report PDF (signed URL)
- **Tenant onboarding**
  - `GET /onboardings` — List move-in checklists
  - `GET /onboardings/{id}` — Checklist detail
  - `PUT /onboardings/{id}/entries` — Save entries (partial OK)
  - `POST /onboardings/{id}/photos` — Upload a photo for an item
  - `POST /onboardings/{id}/complete` — Tenant signs → completed
  - `GET /onboardings/{id}/onboarding.pdf` — Onboarding report PDF (signed URL)
- **Files**
  - `GET /files` — Signed file download

---

## Auth & device

### `POST /auth/login`

**Login and bind the device.** No auth.

First login binds `device_uid` to the user. A different device is refused with 403 `device_not_registered` until an admin clears it on **Masters → Mobile Devices**. Store `token` and `device_uid` in secure storage; the token never expires.

JSON body:

| Field | Type | Required | Notes |
|---|---|---|---|
| `username` | string | yes |  |
| `password` | string | yes |  |
| `device_uid` | string | yes | stable per-install id (max 191) |
| `device_name` | string |  | max 100 |
| `platform` | string (`android` \| `ios`) |  |  |
| `app_version` | string |  | max 20 |

<details><summary>Example — 200 first login / registered device</summary>

Request:

```json
{
    "username": "are.user",
    "password": "secret123",
    "device_uid": "a1b2c3d4-android",
    "device_name": "Pixel 7",
    "platform": "android",
    "app_version": "1.0.0"
}
```

Response `200`:

```json
{
    "token": "45|GO21DRgTzRELgwwdmJjLcH6BCJvbvha3Gio9Lt55a241118b",
    "user": {
        "id": 93,
        "username": "are.user",
        "email": "are.user@example.com",
        "name": "Salim Al Harthy",
        "mobile": "97968988",
        "roles": [
            "are"
        ],
        "permissions": [
            "dashboard",
            "tenant_list",
            "tenant_edit",
            "view_building",
            "… (all desktop permissions of the user)"
        ]
    },
    "buildings": [
        {
            "id": 67,
            "code": "167",
            "name": "Al Aroos"
        },
        {
            "id": 16,
            "code": "116",
            "name": "Al Fajr House"
        },
        {
            "id": 289,
            "code": "389",
            "name": "Al Hadani Bldg"
        }
    ]
}
```

</details>

<details><summary>Example — 403 another device is registered</summary>

Request:

```json
{
    "username": "are.user",
    "password": "secret123",
    "device_uid": "other-phone"
}
```

Response `403`:

```json
{
    "message": "This device is not registered. Please contact the administrator.",
    "code": "device_not_registered"
}
```

</details>

<details><summary>Example — 422 bad credentials</summary>

Request:

```json
{
    "username": "are.user",
    "password": "wrong",
    "device_uid": "a1b2c3d4-android"
}
```

Response `422`:

```json
{
    "message": "The given data was invalid.",
    "code": "validation",
    "errors": {
        "username": [
            "Invalid username or password."
        ]
    }
}
```

</details>

### `POST /auth/logout`

**Revoke the current token.** Auth + `X-Device-Id`.

Deletes the token used for this request. The device stays registered; the next login on it succeeds.

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "message": "Logged out."
}
```

</details>

### `GET /auth/me`

**Current user, buildings and device.** Auth + `X-Device-Id`.

Call on app start to refresh the building list and confirm the token/device pair is still valid.

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "user": {
        "id": 93,
        "username": "are.user",
        "email": "are.user@example.com",
        "name": "Salim Al Harthy",
        "mobile": "97968988",
        "roles": [
            "are"
        ],
        "permissions": [
            "dashboard",
            "tenant_list",
            "tenant_edit",
            "view_building",
            "… (all desktop permissions of the user)"
        ]
    },
    "buildings": [
        {
            "id": 67,
            "code": "167",
            "name": "Al Aroos"
        },
        {
            "id": 16,
            "code": "116",
            "name": "Al Fajr House"
        },
        {
            "id": 289,
            "code": "389",
            "name": "Al Hadani Bldg"
        }
    ],
    "device": {
        "device_uid": "a1b2c3d4-android",
        "device_name": "Pixel 7",
        "platform": "android",
        "app_version": "1.0.0",
        "registered_at": "2026-09-19T06:03:53.000000Z"
    }
}
```

</details>

<details><summary>Example — 403 X-Device-Id does not match</summary>

Response `403`:

```json
{
    "message": "This device is not registered. Please contact the administrator.",
    "code": "device_not_registered"
}
```

</details>

<details><summary>Example — 401 missing/revoked token</summary>

Response `403`:

```json
{
    "message": "This device is not registered. Please contact the administrator.",
    "code": "device_not_registered"
}
```

</details>

### `GET /buildings`

**My buildings with open-item counts.** Auth + `X-Device-Id`.

Home screen tiles. Counts are computed live; refresh on pull-down.

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "data": [
        {
            "id": 67,
            "code": "167",
            "name": "Al Aroos",
            "units": 10,
            "unassigned_complaints": 3,
            "my_complaints": 0,
            "pending_inspections": 0,
            "open_onboardings": 0
        },
        {
            "id": 16,
            "code": "116",
            "name": "Al Fajr House",
            "units": 17,
            "unassigned_complaints": 0,
            "my_complaints": 0,
            "pending_inspections": 0,
            "open_onboardings": 0
        },
        {
            "id": 289,
            "code": "389",
            "name": "Al Hadani Bldg",
            "units": 32,
            "unassigned_complaints": 2,
            "my_complaints": 0,
            "pending_inspections": 0,
            "open_onboardings": 0
        }
    ]
}
```

</details>

### `GET /inventories`

**Inventory master (material picker).** Auth + `X-Device-Id`.

Read-only list for the "material used" picker on a ticket. Cache it locally; refresh with `updated_since`.

Query parameters:

| Field | Type | Required | Notes |
|---|---|---|---|
| `q` | string |  | name contains |
| `updated_since` | string |  |  |
| `per_page` | integer |  | ≤ 500, default 200 |
| `page` | integer |  |  |

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "data": [
        {
            "id": 490,
            "name": "1.5 Flexible cable",
            "brand": null,
            "description": null,
            "updated_at": "2021-09-30T06:15:01.000000Z"
        },
        {
            "id": 547,
            "name": "11/2 \" copper valve",
            "brand": null,
            "description": null,
            "updated_at": "2022-12-24T08:53:09.000000Z"
        },
        {
            "id": 483,
            "name": "2'' Copper Male Socket",
            "brand": null,
            "description": null,
            "updated_at": "2021-08-17T08:10:32.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 98,
        "per_page": 3,
        "total": 293
    }
}
```

</details>

---

## Complaints

### `GET /complaints`

**List tickets by tab.** Auth + `X-Device-Id`.

Tabs: `unassigned` (stage 701/702, no sub-assignee, in my buildings — claimable), `mine` (703 sub-assigned to me, status 0/1), `attended` (status 2), `completed` (status 3 or stage 705). Paginated.

Query parameters:

| Field | Type | Required | Notes |
|---|---|---|---|
| `tab` | string (`unassigned` \| `mine` \| `attended` \| `completed`) | yes |  |
| `building_id` | integer |  |  |
| `q` | string |  | complaint no / complainant / building |
| `updated_since` | string |  | ISO date-time; incremental sync |
| `per_page` | integer |  | ≤ 100, default 25 |
| `page` | integer |  |  |

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "data": [
        {
            "id": 58936,
            "ticket_no": "TKT-2026-1187",
            "complaint_no": "CMP-2026-0912",
            "complaint_date": "2026-09-19T06:03:56.000000Z",
            "description": "AC not cooling in master bedroom",
            "work": "Painting",
            "priority": 1,
            "stage": 701,
            "status": 0,
            "building": {
                "id": 66,
                "name": "Butterfly Bldg"
            },
            "unit": {
                "id": null,
                "no": null
            },
            "tenant": {
                "name": "Ahmed Al Balushi",
                "mobile": "92222716"
            },
            "sub_assigned_to": null,
            "updated_at": "2026-09-19T06:03:56.000000Z"
        },
        {
            "id": 58850,
            "ticket_no": "CMP50548-SUB1",
            "complaint_no": "CMP50548",
            "complaint_date": "2026-08-31T20:00:00.000000Z",
            "description": "AC not working",
            "work": "sample data",
            "priority": 0,
            "stage": 702,
            "status": 0,
            "building": {
                "id": 260,
                "name": "Office Avenue-2 Bldg"
            },
            "unit": {
                "id": 5475,
                "no": "72"
            },
            "tenant": {
                "name": "Fatma Al Lawati",
                "mobile": "98210093"
            },
            "sub_assigned_to": null,
            "updated_at": "2026-09-01T07:35:47.000000Z"
        }
    ],
    "links": {
        "first": "https://plms.example.com/api/v1/complaints?page=1",
        "last": "https://plms.example.com/api/v1/complaints?page=37",
        "prev": null,
        "next": "https://plms.example.com/api/v1/complaints?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 37,
        "links": [
            {
                "url": null,
                "label": "« Previous",
                "page": null,
                "active": false
            },
            {
                "url": "http://localhost:8000/api/v1/complaints?page=1",
                "label": "1",
                "page": 1,
                "active": true
            },
            {
                "url": "http://localhost:8000/api/v1/complaints?page=2",
                "label": "2",
                "page": 2,
                "active": false
            }
        ],
        "path": "https://plms.example.com/api/v1/complaints",
        "per_page": 2,
        "to": 2,
        "total": 74
    }
}
```

</details>

### `GET /complaints/{ticket}`

**Ticket detail with service report.** Auth + `X-Device-Id`.

Includes `report` (notes, photos, items, signature) once checked in, `flags`, and the last 5 `previous_complaints` on the unit. 403 `not_assigned` outside my buildings.

<details><summary>Example — 200 (after close)</summary>

Response `200`:

```json
{
    "data": {
        "id": 58936,
        "ticket_no": "TKT-2026-1187",
        "complaint_no": "CMP-2026-0912",
        "complaint_date": "2026-09-19T06:03:56.000000Z",
        "description": "AC not cooling in master bedroom",
        "work": "Painting",
        "priority": 1,
        "stage": 705,
        "status": 4,
        "building": {
            "id": 66,
            "name": "Butterfly Bldg"
        },
        "unit": {
            "id": null,
            "no": null
        },
        "tenant": {
            "name": "Ahmed Al Balushi",
            "mobile": "92222716"
        },
        "sub_assigned_to": 93,
        "updated_at": "2026-09-19T06:03:58.000000Z",
        "flags": {
            "tenant_status": 0,
            "complainer_category": 1
        },
        "report": {
            "id": 45933,
            "service_report_no": "SERV45901",
            "status": 3,
            "signature_url": "https://plms.example.com/api/v1/files?expires=1789798738&path=mobile%2Fcomplaints%2F58936%2Fsignature_20260919100358_65r7.png&signature=…",
            "notes": [
                {
                    "id": 41808,
                    "text": "Checked in at 19/09/2026 10:03 (23.588, 58.3829)",
                    "stage": 1,
                    "created_at": "2026-09-19T06:03:56.000000Z"
                },
                {
                    "id": 41809,
                    "text": "Compressor relay replaced, gas topped up",
                    "stage": 1,
                    "created_at": "2026-09-19T06:03:56.000000Z"
                },
                {
                    "id": 41810,
                    "text": "Tenant remark: Working fine now",
                    "stage": 3,
                    "created_at": "2026-09-19T06:03:58.000000Z"
                }
            ],
            "photos": [
                {
                    "id": 777,
                    "stage": "after",
                    "url": "https://plms.example.com/api/v1/files?expires=1789798738&path=mobile%2Fcomplaints%2F58936%2F20260919100357_bwmMMA.jpg&signature=…",
                    "thumb_url": "https://plms.example.com/api/v1/files?expires=1789798738&path=mobile%2Fcomplaints%2F58936%2F20260919100357_bwmMMA_t.jpg&signature=…",
                    "created_at": "2026-09-19T06:03:57.000000Z"
                }
            ],
            "items": [
                {
                    "id": 56902,
                    "inventory_id": 1,
                    "name": "Flexible Pipe",
                    "quantity": 1
                }
            ]
        },
        "previous_complaints": [
            {
                "id": 50516,
                "complaint_no": "CMP50516",
                "complaint_date": "2026-08-30T20:00:00.000000Z",
                "complaint_status": 2
            },
            {
                "id": 50409,
                "complaint_no": "CMP50409",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50408,
                "complaint_no": "CMP50408",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50201,
                "complaint_no": "CMP50201",
                "complaint_date": "2026-08-16T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 49508,
                "complaint_no": "CMP49508",
                "complaint_date": "2026-07-25T20:00:00.000000Z",
                "complaint_status": 0
            }
        ]
    }
}
```

</details>

### `POST /complaints/{ticket}/claim`

**Claim an unassigned ticket.** Auth + `X-Device-Id`.

Sub-assigns the ticket to me (stage 703) — same rows the coordinator's modal writes. 409 `invalid_state` if someone already has it.

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "data": {
        "id": 58936,
        "ticket_no": "TKT-2026-1187",
        "complaint_no": "CMP-2026-0912",
        "complaint_date": "2026-09-19T06:03:56.000000Z",
        "description": "AC not cooling in master bedroom",
        "work": "Painting",
        "priority": 1,
        "stage": 703,
        "status": 0,
        "building": {
            "id": 66,
            "name": "Butterfly Bldg"
        },
        "unit": {
            "id": null,
            "no": null
        },
        "tenant": {
            "name": "Ahmed Al Balushi",
            "mobile": "92222716"
        },
        "sub_assigned_to": 93,
        "updated_at": "2026-09-19T06:03:56.000000Z",
        "flags": {
            "tenant_status": 0,
            "complainer_category": 1
        },
        "report": null,
        "previous_complaints": [
            {
                "id": 50516,
                "complaint_no": "CMP50516",
                "complaint_date": "2026-08-30T20:00:00.000000Z",
                "complaint_status": 2
            },
            {
                "id": 50409,
                "complaint_no": "CMP50409",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50408,
                "complaint_no": "CMP50408",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50201,
                "complaint_no": "CMP50201",
                "complaint_date": "2026-08-16T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 49508,
                "complaint_no": "CMP49508",
                "complaint_date": "2026-07-25T20:00:00.000000Z",
                "complaint_status": 0
            }
        ]
    }
}
```

</details>

<details><summary>Example — 409 already assigned</summary>

Response `409`:

```json
{
    "message": "This ticket has already been assigned.",
    "code": "invalid_state"
}
```

</details>

### `POST /complaints/{ticket}/check-in`

**Check in at the unit.** Auth + `X-Device-Id`.

Creates the service report once (idempotent) and sets status 1. Send GPS when available; it is recorded as the first note.

JSON body:

| Field | Type | Required | Notes |
|---|---|---|---|
| `lat` | number |  | -90..90 |
| `lng` | number |  | -180..180 |

<details><summary>Example — 200</summary>

Request:

```json
{
    "lat": 23.588,
    "lng": 58.3829
}
```

Response `200`:

```json
{
    "data": {
        "id": 58936,
        "ticket_no": "TKT-2026-1187",
        "complaint_no": "CMP-2026-0912",
        "complaint_date": "2026-09-19T06:03:56.000000Z",
        "description": "AC not cooling in master bedroom",
        "work": "Painting",
        "priority": 1,
        "stage": 703,
        "status": 1,
        "building": {
            "id": 66,
            "name": "Butterfly Bldg"
        },
        "unit": {
            "id": null,
            "no": null
        },
        "tenant": {
            "name": "Ahmed Al Balushi",
            "mobile": "92222716"
        },
        "sub_assigned_to": 93,
        "updated_at": "2026-09-19T06:03:56.000000Z",
        "flags": {
            "tenant_status": 0,
            "complainer_category": 1
        },
        "report": {
            "id": 45933,
            "service_report_no": "SERV45901",
            "status": 1,
            "signature_url": null,
            "notes": [
                {
                    "id": 41808,
                    "text": "Checked in at 19/09/2026 10:03 (23.588, 58.3829)",
                    "stage": 1,
                    "created_at": "2026-09-19T06:03:56.000000Z"
                }
            ],
            "photos": [],
            "items": []
        },
        "previous_complaints": [
            {
                "id": 50516,
                "complaint_no": "CMP50516",
                "complaint_date": "2026-08-30T20:00:00.000000Z",
                "complaint_status": 2
            },
            {
                "id": 50409,
                "complaint_no": "CMP50409",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50408,
                "complaint_no": "CMP50408",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50201,
                "complaint_no": "CMP50201",
                "complaint_date": "2026-08-16T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 49508,
                "complaint_no": "CMP49508",
                "complaint_date": "2026-07-25T20:00:00.000000Z",
                "complaint_status": 0
            }
        ]
    }
}
```

</details>

### `POST /complaints/{ticket}/notes`

**Add a note.** Auth + `X-Device-Id`.

Checks in implicitly if not yet done. `quick_reason` prefixes the note with its label.

JSON body:

| Field | Type | Required | Notes |
|---|---|---|---|
| `text` | string | yes | max 2000 |
| `quick_reason` | string (`part_required` \| `tenant_not_available` \| `access_denied` \| `not_our_scope`) |  |  |

<details><summary>Example — 201</summary>

Request:

```json
{
    "text": "Compressor relay replaced, gas topped up"
}
```

Response `201`:

```json
{
    "data": {
        "id": 41809,
        "text": "Compressor relay replaced, gas topped up",
        "created_at": "2026-09-19T06:03:56.000000Z"
    }
}
```

</details>

### `POST /complaints/{ticket}/photos`

**Upload a photo.** Auth + `X-Device-Id`.

Multipart. `stage` = before | during | after. At least one `after` photo is required to close without a signature.

Multipart form fields:

| Field | Type | Required | Notes |
|---|---|---|---|
| `stage` | string (`before` \| `during` \| `after`) | yes |  |
| `photo` | file | yes | jpeg/png/webp, ≤ 10 MB; resized server-side to 1600 px + 300 px thumbnail |

<details><summary>Example — 201</summary>

Request:

```json
{
    "multipart": {
        "stage": "after",
        "photo": "(file)"
    }
}
```

Response `201`:

```json
{
    "data": {
        "id": 777,
        "stage": "after",
        "url": "https://plms.example.com/api/v1/files?expires=1789798737&path=mobile%2Fcomplaints%2F58936%2F20260919100357_bwmMMA.jpg&signature=…",
        "thumb_url": "https://plms.example.com/api/v1/files?expires=1789798737&path=mobile%2Fcomplaints%2F58936%2F20260919100357_bwmMMA_t.jpg&signature=…"
    }
}
```

</details>

### `POST /complaints/{ticket}/items`

**Add material used.** Auth + `X-Device-Id`.

`inventory_id` from `GET /inventories`.

JSON body:

| Field | Type | Required | Notes |
|---|---|---|---|
| `inventory_id` | integer | yes |  |
| `quantity` | number | yes | > 0 |

<details><summary>Example — 201</summary>

Request:

```json
{
    "inventory_id": 1,
    "quantity": 1
}
```

Response `201`:

```json
{
    "data": {
        "id": 56902,
        "inventory_id": 1,
        "quantity": 1
    }
}
```

</details>

### `DELETE /complaints/{ticket}/items/{item}`

**Remove a material line.** Auth + `X-Device-Id`.

### `POST /complaints/{ticket}/status`

**Set attended / completed / cannot complete.** Auth + `X-Device-Id`.

`attended` → 2 (notifies coordinator + supervisor), `completed` → 3, `cannot_complete` → status unchanged, note "Cannot complete: reason".

JSON body:

| Field | Type | Required | Notes |
|---|---|---|---|
| `status` | string (`attended` \| `completed` \| `cannot_complete`) | yes |  |
| `reason` | string |  | required for cannot_complete, max 500 |

<details><summary>Example — 200</summary>

Request:

```json
{
    "status": "attended"
}
```

Response `200`:

```json
{
    "data": {
        "id": 58936,
        "ticket_no": "TKT-2026-1187",
        "complaint_no": "CMP-2026-0912",
        "complaint_date": "2026-09-19T06:03:56.000000Z",
        "description": "AC not cooling in master bedroom",
        "work": "Painting",
        "priority": 1,
        "stage": 703,
        "status": 2,
        "building": {
            "id": 66,
            "name": "Butterfly Bldg"
        },
        "unit": {
            "id": null,
            "no": null
        },
        "tenant": {
            "name": "Ahmed Al Balushi",
            "mobile": "92222716"
        },
        "sub_assigned_to": 93,
        "updated_at": "2026-09-19T06:03:57.000000Z",
        "flags": {
            "tenant_status": 0,
            "complainer_category": 1
        },
        "report": {
            "id": 45933,
            "service_report_no": "SERV45901",
            "status": 2,
            "signature_url": null,
            "notes": [
                {
                    "id": 41808,
                    "text": "Checked in at 19/09/2026 10:03 (23.588, 58.3829)",
                    "stage": 1,
                    "created_at": "2026-09-19T06:03:56.000000Z"
                },
                {
                    "id": 41809,
                    "text": "Compressor relay replaced, gas topped up",
                    "stage": 1,
                    "created_at": "2026-09-19T06:03:56.000000Z"
                }
            ],
            "photos": [
                {
                    "id": 777,
                    "stage": "after",
                    "url": "https://plms.example.com/api/v1/files?expires=1789798738&path=mobile%2Fcomplaints%2F58936%2F20260919100357_bwmMMA.jpg&signature=…",
                    "thumb_url": "https://plms.example.com/api/v1/files?expires=1789798738&path=mobile%2Fcomplaints%2F58936%2F20260919100357_bwmMMA_t.jpg&signature=…",
                    "created_at": "2026-09-19T06:03:57.000000Z"
                }
            ],
            "items": [
                {
                    "id": 56902,
                    "inventory_id": 1,
                    "name": "Flexible Pipe",
                    "quantity": 1
                }
            ]
        },
        "previous_complaints": [
            {
                "id": 50516,
                "complaint_no": "CMP50516",
                "complaint_date": "2026-08-30T20:00:00.000000Z",
                "complaint_status": 2
            },
            {
                "id": 50409,
                "complaint_no": "CMP50409",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50408,
                "complaint_no": "CMP50408",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50201,
                "complaint_no": "CMP50201",
                "complaint_date": "2026-08-16T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 49508,
                "complaint_no": "CMP49508",
                "complaint_date": "2026-07-25T20:00:00.000000Z",
                "complaint_status": 0
            }
        ]
    }
}
```

</details>

<details><summary>Example — 200</summary>

Request:

```json
{
    "status": "completed"
}
```

Response `200`:

```json
{
    "data": {
        "id": 58936,
        "ticket_no": "TKT-2026-1187",
        "complaint_no": "CMP-2026-0912",
        "complaint_date": "2026-09-19T06:03:56.000000Z",
        "description": "AC not cooling in master bedroom",
        "work": "Painting",
        "priority": 1,
        "stage": 703,
        "status": 3,
        "building": {
            "id": 66,
            "name": "Butterfly Bldg"
        },
        "unit": {
            "id": null,
            "no": null
        },
        "tenant": {
            "name": "Ahmed Al Balushi",
            "mobile": "92222716"
        },
        "sub_assigned_to": 93,
        "updated_at": "2026-09-19T06:03:58.000000Z",
        "flags": {
            "tenant_status": 0,
            "complainer_category": 1
        },
        "report": {
            "id": 45933,
            "service_report_no": "SERV45901",
            "status": 3,
            "signature_url": null,
            "notes": [
                {
                    "id": 41808,
                    "text": "Checked in at 19/09/2026 10:03 (23.588, 58.3829)",
                    "stage": 1,
                    "created_at": "2026-09-19T06:03:56.000000Z"
                },
                {
                    "id": 41809,
                    "text": "Compressor relay replaced, gas topped up",
                    "stage": 1,
                    "created_at": "2026-09-19T06:03:56.000000Z"
                }
            ],
            "photos": [
                {
                    "id": 777,
                    "stage": "after",
                    "url": "https://plms.example.com/api/v1/files?expires=1789798738&path=mobile%2Fcomplaints%2F58936%2F20260919100357_bwmMMA.jpg&signature=…",
                    "thumb_url": "https://plms.example.com/api/v1/files?expires=1789798738&path=mobile%2Fcomplaints%2F58936%2F20260919100357_bwmMMA_t.jpg&signature=…",
                    "created_at": "2026-09-19T06:03:57.000000Z"
                }
            ],
            "items": [
                {
                    "id": 56902,
                    "inventory_id": 1,
                    "name": "Flexible Pipe",
                    "quantity": 1
                }
            ]
        },
        "previous_complaints": [
            {
                "id": 50516,
                "complaint_no": "CMP50516",
                "complaint_date": "2026-08-30T20:00:00.000000Z",
                "complaint_status": 2
            },
            {
                "id": 50409,
                "complaint_no": "CMP50409",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50408,
                "complaint_no": "CMP50408",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50201,
                "complaint_no": "CMP50201",
                "complaint_date": "2026-08-16T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 49508,
                "complaint_no": "CMP49508",
                "complaint_date": "2026-07-25T20:00:00.000000Z",
                "complaint_status": 0
            }
        ]
    }
}
```

</details>

<details><summary>Example — 422</summary>

Request:

```json
{
    "status": "bogus"
}
```

Response `422`:

```json
{
    "message": "The given data was invalid.",
    "code": "validation",
    "errors": {
        "status": [
            "The selected status is invalid."
        ]
    }
}
```

</details>

### `POST /complaints/{ticket}/signature`

**Close with tenant signature.** Auth + `X-Device-Id`.

Requires status 3. Either `signature` (PNG data URL) or `skipped: true` with ≥ 1 `after` photo. Moves to stage 705 / status 4, closes the complaint, emails/SMSes the tenant. `tenant_remark` becomes a note.

JSON body:

| Field | Type | Required | Notes |
|---|---|---|---|
| `signature` | string |  | data:image/png;base64,… |
| `skipped` | boolean |  |  |
| `tenant_remark` | string |  | max 500 |

<details><summary>Example — 200</summary>

Request:

```json
{
    "signature": "data:image/png;base64,…",
    "tenant_remark": "Working fine now"
}
```

Response `200`:

```json
{
    "data": {
        "id": 58936,
        "ticket_no": "TKT-2026-1187",
        "complaint_no": "CMP-2026-0912",
        "complaint_date": "2026-09-19T06:03:56.000000Z",
        "description": "AC not cooling in master bedroom",
        "work": "Painting",
        "priority": 1,
        "stage": 705,
        "status": 4,
        "building": {
            "id": 66,
            "name": "Butterfly Bldg"
        },
        "unit": {
            "id": null,
            "no": null
        },
        "tenant": {
            "name": "Ahmed Al Balushi",
            "mobile": "92222716"
        },
        "sub_assigned_to": 93,
        "updated_at": "2026-09-19T06:03:58.000000Z",
        "flags": {
            "tenant_status": 0,
            "complainer_category": 1
        },
        "report": {
            "id": 45933,
            "service_report_no": "SERV45901",
            "status": 3,
            "signature_url": "https://plms.example.com/api/v1/files?expires=1789798738&path=mobile%2Fcomplaints%2F58936%2Fsignature_20260919100358_65r7.png&signature=…",
            "notes": [
                {
                    "id": 41808,
                    "text": "Checked in at 19/09/2026 10:03 (23.588, 58.3829)",
                    "stage": 1,
                    "created_at": "2026-09-19T06:03:56.000000Z"
                },
                {
                    "id": 41809,
                    "text": "Compressor relay replaced, gas topped up",
                    "stage": 1,
                    "created_at": "2026-09-19T06:03:56.000000Z"
                },
                {
                    "id": 41810,
                    "text": "Tenant remark: Working fine now",
                    "stage": 3,
                    "created_at": "2026-09-19T06:03:58.000000Z"
                }
            ],
            "photos": [
                {
                    "id": 777,
                    "stage": "after",
                    "url": "https://plms.example.com/api/v1/files?expires=1789798738&path=mobile%2Fcomplaints%2F58936%2F20260919100357_bwmMMA.jpg&signature=…",
                    "thumb_url": "https://plms.example.com/api/v1/files?expires=1789798738&path=mobile%2Fcomplaints%2F58936%2F20260919100357_bwmMMA_t.jpg&signature=…",
                    "created_at": "2026-09-19T06:03:57.000000Z"
                }
            ],
            "items": [
                {
                    "id": 56902,
                    "inventory_id": 1,
                    "name": "Flexible Pipe",
                    "quantity": 1
                }
            ]
        },
        "previous_complaints": [
            {
                "id": 50516,
                "complaint_no": "CMP50516",
                "complaint_date": "2026-08-30T20:00:00.000000Z",
                "complaint_status": 2
            },
            {
                "id": 50409,
                "complaint_no": "CMP50409",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50408,
                "complaint_no": "CMP50408",
                "complaint_date": "2026-08-25T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 50201,
                "complaint_no": "CMP50201",
                "complaint_date": "2026-08-16T20:00:00.000000Z",
                "complaint_status": 0
            },
            {
                "id": 49508,
                "complaint_no": "CMP49508",
                "complaint_date": "2026-07-25T20:00:00.000000Z",
                "complaint_status": 0
            }
        ]
    }
}
```

</details>

### `GET /complaints/{ticket}/service-report.pdf`

**Service report PDF (signed URL).** Auth + `X-Device-Id`.

Returns `{url}` valid 15 minutes — open it with the OS viewer / share sheet.

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "url": "https://plms.example.com/api/v1/files?expires=1789798740&path=mobile%2Fcomplaints%2F58936%2Fservice-report.pdf&signature=…"
}
```

</details>

---

## Termination inspections

### `GET /terminations`

**List handovers by tab.** Auth + `X-Device-Id`.

Tabs: `pending` (502 unassigned, or 503 assigned to me with nothing saved / referred back), `inspected` (503 with my saved draft), `taken_over` (504 submitted by me, last 90 days). `overdue` = termination date has passed.

Query parameters:

| Field | Type | Required | Notes |
|---|---|---|---|
| `tab` | string (`pending` \| `inspected` \| `taken_over`) | yes |  |
| `building_id` | integer |  |  |
| `updated_since` | string |  |  |
| `per_page` | integer |  |  |
| `page` | integer |  |  |

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "data": [
        {
            "id": 23753,
            "contract_id": 76038,
            "contract_no": "TC-2024-0451",
            "stage": 502,
            "termination_date": "2026-07-14T20:00:00.000000Z",
            "termination_type_status": 3,
            "overdue": true,
            "assigned_to": null,
            "draft_saved": false,
            "referred_back": false,
            "building": {
                "id": 469,
                "name": "Ghala-109 Bldg"
            },
            "unit": {
                "id": 9080,
                "no": "62"
            },
            "tenant": {
                "name": "Khalid Al Rashdi",
                "mobile": "99532188"
            },
            "net": 0,
            "updated_at": "2026-09-17T07:46:26.000000Z"
        },
        {
            "id": 23746,
            "contract_id": 75355,
            "contract_no": "TC-2024-0451",
            "stage": 502,
            "termination_date": "2026-08-30T20:00:00.000000Z",
            "termination_type_status": 0,
            "overdue": true,
            "assigned_to": null,
            "draft_saved": false,
            "referred_back": false,
            "building": {
                "id": 186,
                "name": "Sala-02 Bldg"
            },
            "unit": {
                "id": 3371,
                "no": "28"
            },
            "tenant": {
                "name": "Maryam Al Siyabi",
                "mobile": "91581315"
            },
            "net": 0,
            "updated_at": "2026-09-01T13:02:07.000000Z"
        }
    ],
    "links": {
        "first": "https://plms.example.com/api/v1/terminations?page=1",
        "last": "https://plms.example.com/api/v1/terminations?page=2",
        "prev": null,
        "next": "https://plms.example.com/api/v1/terminations?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 2,
        "links": [
            {
                "url": null,
                "label": "« Previous",
                "page": null,
                "active": false
            },
            {
                "url": "http://localhost:8000/api/v1/terminations?page=1",
                "label": "1",
                "page": 1,
                "active": true
            },
            {
                "url": "http://localhost:8000/api/v1/terminations?page=2",
                "label": "2",
                "page": 2,
                "active": false
            }
        ],
        "path": "https://plms.example.com/api/v1/terminations",
        "per_page": 2,
        "to": 2,
        "total": 4
    }
}
```

</details>

### `GET /terminations/{id}`

**Handover detail + checklist template + draft.** Auth + `X-Device-Id`.

Everything the inspection screen needs: tenant/unit/contract, `outstanding_rent`, `deposit_cheque`, `open_documents`, existing `photos`, `previous_inspections` on the unit, `refer_back` comment, saved `draft` and the `template` (works → sub-works) to build the form.

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "data": {
        "id": 23753,
        "stage": 502,
        "contract": {
            "id": 76038,
            "no": "TC-2024-0451",
            "start_date": "2025-11-14T20:00:00.000000Z",
            "valid_to": "2026-11-13T20:00:00.000000Z",
            "rent": 160,
            "deposit": 0
        },
        "tenant": {
            "id": 17021,
            "name": "Khalid Al Rashdi",
            "mobile": "99532188",
            "email": null
        },
        "building": {
            "id": 469,
            "name": "Ghala-109 Bldg"
        },
        "unit": {
            "id": 9080,
            "no": "62"
        },
        "termination_date": "2026-07-14T20:00:00.000000Z",
        "termination_type_status": 3,
        "overdue": true,
        "assigned_to": null,
        "outstanding_rent": 0,
        "outstanding_rent_this_contract": 0,
        "deposit_cheque": null,
        "open_documents": [],
        "photos": [],
        "previous_inspections": [],
        "refer_back": null,
        "draft": null,
        "signature_url": null,
        "pdf_url": null,
        "template": [
            {
                "work_id": 12,
                "code": "Appliances",
                "desc": null,
                "sub_works": [
                    {
                        "id": 29,
                        "name": "Door Keys"
                    },
                    {
                        "id": 30,
                        "name": "A/C Remote"
                    },
                    {
                        "id": 31,
                        "name": "Gas Chamber Keys / Regulator"
                    }
                ]
            },
            {
                "work_id": 2,
                "code": "Carpenter",
                "desc": "Test",
                "sub_works": [
                    {
                        "id": 23,
                        "name": "Door Locks - Door Keys,Door Viewer,Wardrobe Keys"
                    },
                    {
                        "id": 24,
                        "name": "Kitchen Cabinets / Kitchen Top"
                    },
                    {
                        "id": 25,
                        "name": "Aluminium Window Locks, Fly Mesh"
                    }
                ]
            }
        ]
    }
}
```

</details>

### `PUT /terminations/{id}/inspection`

**Save the inspection (draft).** Auth + `X-Device-Id`.

Whole checklist every time. From stage 502 the ARE is assigned first (502 → 503). The **first** save creates the 503 inspection row — its id comes back as `data.draft.id`; use that id for photos and submit. Later saves update it and replace the lines. Totals are computed server-side: `net = Σ lines + electricity + water − maintenance_due`.

JSON body:

| Field | Type | Required | Notes |
|---|---|---|---|
| `lines` | array | yes |  |
| `lines[].sub_work_id` | integer |  | from template; or use other_work |
| `lines[].other_work` | string |  | free text, max 200 |
| `lines[].quantity` | number | yes |  |
| `lines[].amount` | number | yes |  |
| `electricity` | object |  |  |
| `electricity[].acc_no` | string |  |  |
| `electricity[].close_reading` | string |  |  |
| `electricity[].amount` | number |  |  |
| `water` | object |  |  |
| `water[].acc_no` | string |  |  |
| `water[].close_reading` | string |  |  |
| `water[].amount` | number |  |  |
| `main_key_status` | integer (`0` \| `1`) | yes | 1 = main key received |
| `maintenance_due` | number |  |  |
| `notes` | string | yes | max 2000 |
| `remark` | string |  | max 1000 |

<details><summary>Example — 200</summary>

Request:

```json
{
    "lines": [
        {
            "sub_work_id": 29,
            "quantity": 2,
            "amount": 15.5
        },
        {
            "other_work": "Broken bathroom tile",
            "quantity": 1,
            "amount": 4.5
        }
    ],
    "electricity": {
        "acc_no": "10234567",
        "close_reading": "48213",
        "amount": 10
    },
    "water": {
        "acc_no": "W-55321",
        "close_reading": "912",
        "amount": 2.5
    },
    "main_key_status": 1,
    "maintenance_due": 3,
    "notes": "Unit in fair condition, minor tile damage",
    "remark": null
}
```

Response `200`:

```json
{
    "data": {
        "id": 23801,
        "stage": 503,
        "contract": {
            "id": 76038,
            "no": "TC-2024-0451",
            "start_date": "2025-11-14T20:00:00.000000Z",
            "valid_to": "2026-11-13T20:00:00.000000Z",
            "rent": 160,
            "deposit": 0
        },
        "tenant": {
            "id": 17021,
            "name": "Khalid Al Rashdi",
            "mobile": "99532188",
            "email": null
        },
        "building": {
            "id": 469,
            "name": "Ghala-109 Bldg"
        },
        "unit": {
            "id": 9080,
            "no": "62"
        },
        "termination_date": "2026-07-14T20:00:00.000000Z",
        "termination_type_status": 3,
        "overdue": true,
        "assigned_to": 93,
        "outstanding_rent": 0,
        "outstanding_rent_this_contract": 0,
        "deposit_cheque": null,
        "open_documents": [],
        "photos": [],
        "previous_inspections": [],
        "refer_back": null,
        "draft": {
            "id": 23801,
            "lines": [
                {
                    "id": 2526,
                    "work_id": 12,
                    "sub_work_id": 29,
                    "other_work": null,
                    "quantity": 2,
                    "amount": 15.5
                },
                {
                    "id": 2527,
                    "work_id": null,
                    "sub_work_id": null,
                    "other_work": "Broken bathroom tile",
                    "quantity": 1,
                    "amount": 4.5
                }
            ],
            "electricity": {
                "acc_no": "10234567",
                "close_reading": "48213",
                "amount": 10
            },
            "water": {
                "acc_no": "W-55321",
                "close_reading": "912",
                "amount": 2.5
            },
            "main_key_status": 1,
            "maintenance_due": 3,
            "notes": "Unit in fair condition, minor tile damage",
            "remark": null,
            "totals": {
                "total": 20,
                "elec_water_total": 12.5,
                "maintenance_due": 3,
                "net": 29.5
            },
            "signed": false,
            "updated_at": "2026-09-19T06:04:01.000000Z"
        },
        "signature_url": null,
        "pdf_url": null,
        "template": [
            {
                "work_id": 12,
                "code": "Appliances",
                "desc": null,
                "sub_works": [
                    {
                        "id": 29,
                        "name": "Door Keys"
                    },
                    {
                        "id": 30,
                        "name": "A/C Remote"
                    },
                    {
                        "id": 31,
                        "name": "Gas Chamber Keys / Regulator"
                    }
                ]
            },
            {
                "work_id": 2,
                "code": "Carpenter",
                "desc": "Test",
                "sub_works": [
                    {
                        "id": 23,
                        "name": "Door Locks - Door Keys,Door Viewer,Wardrobe Keys"
                    },
                    {
                        "id": 24,
                        "name": "Kitchen Cabinets / Kitchen Top"
                    },
                    {
                        "id": 25,
                        "name": "Aluminium Window Locks, Fly Mesh"
                    }
                ]
            }
        ]
    }
}
```

</details>

### `POST /terminations/{id}/photos`

**Upload an inspection photo.** Auth + `X-Device-Id`.

Multipart. Use the draft id once a draft exists.

Multipart form fields:

| Field | Type | Required | Notes |
|---|---|---|---|
| `type` | string (`inspection` \| `meter_electricity` \| `meter_water` \| `key`) | yes |  |
| `photo` | file | yes | jpeg/png/webp, ≤ 10 MB; resized server-side to 1600 px + 300 px thumbnail |

<details><summary>Example — 201</summary>

Request:

```json
{
    "multipart": {
        "type": "meter_electricity",
        "photo": "(file)"
    }
}
```

Response `201`:

```json
{
    "data": {
        "id": 36,
        "type": "meter_electricity",
        "url": "https://plms.example.com/api/v1/files?expires=1789798742&path=mobile%2Fterminations%2F23801%2F20260919100402_Vecs3B.jpg&signature=…",
        "thumb_url": "https://plms.example.com/api/v1/files?expires=1789798742&path=mobile%2Fterminations%2F23801%2F20260919100402_Vecs3B_t.jpg&signature=…"
    }
}
```

</details>

### `POST /terminations/{id}/submit`

**Tenant signs → Taken Over.** Auth + `X-Device-Id`.

Requires a saved draft and the contract still at 503. Stores the signature, moves to **504 Taken Over**, renders the PDF, emails the tenant, notifies back-office. The response is the **new 504 row** (`data.id` changes) — use it for the PDF.

JSON body:

| Field | Type | Required | Notes |
|---|---|---|---|
| `signature` | string | yes | data:image/png;base64,… |
| `tenant_remark` | string |  | max 1000 |

<details><summary>Example — 200</summary>

Request:

```json
{
    "signature": "data:image/png;base64,…",
    "tenant_remark": "Agreed"
}
```

Response `200`:

```json
{
    "data": {
        "id": 23802,
        "stage": 504,
        "contract": {
            "id": 76038,
            "no": "TC-2024-0451",
            "start_date": "2025-11-14T20:00:00.000000Z",
            "valid_to": "2026-11-13T20:00:00.000000Z",
            "rent": 160,
            "deposit": 0
        },
        "tenant": {
            "id": 17021,
            "name": "Khalid Al Rashdi",
            "mobile": "99532188",
            "email": null
        },
        "building": {
            "id": 469,
            "name": "Ghala-109 Bldg"
        },
        "unit": {
            "id": 9080,
            "no": "62"
        },
        "termination_date": "2026-07-14T20:00:00.000000Z",
        "termination_type_status": 3,
        "overdue": true,
        "assigned_to": 93,
        "outstanding_rent": 0,
        "outstanding_rent_this_contract": 0,
        "deposit_cheque": null,
        "open_documents": [],
        "photos": [
            {
                "id": 36,
                "type": "meter_electricity",
                "path": "mobile/terminations/23801/20260919100402_Vecs3B.jpg",
                "thumb": "mobile/terminations/23801/20260919100402_Vecs3B_t.jpg",
                "created_at": "2026-09-19T06:04:02.000000Z",
                "url": "https://plms.example.com/api/v1/files?expires=1789798745&path=mobile%2Fterminations%2F23801%2F20260919100402_Vecs3B.jpg&signature=…",
                "thumb_url": "https://plms.example.com/api/v1/files?expires=1789798745&path=mobile%2Fterminations%2F23801%2F20260919100402_Vecs3B_t.jpg&signature=…"
            }
        ],
        "previous_inspections": [],
        "refer_back": null,
        "draft": {
            "id": 23801,
            "lines": [
                {
                    "id": 2526,
                    "work_id": 12,
                    "sub_work_id": 29,
                    "other_work": null,
                    "quantity": 2,
                    "amount": 15.5
                },
                {
                    "id": 2527,
                    "work_id": null,
                    "sub_work_id": null,
                    "other_work": "Broken bathroom tile",
                    "quantity": 1,
                    "amount": 4.5
                }
            ],
            "electricity": {
                "acc_no": "10234567",
                "close_reading": "48213",
                "amount": 10
            },
            "water": {
                "acc_no": "W-55321",
                "close_reading": "912",
                "amount": 2.5
            },
            "main_key_status": 1,
            "maintenance_due": 3,
            "notes": "Unit in fair condition, minor tile damage",
            "remark": "Agreed",
            "totals": {
                "total": 20,
                "elec_water_total": 12.5,
                "maintenance_due": 3,
                "net": 29.5
            },
            "signed": true,
            "updated_at": "2026-09-19T06:04:02.000000Z"
        },
        "signature_url": "https://plms.example.com/api/v1/files?expires=1789798745&path=mobile%2Fterminations%2F23801%2Fsignature_20260919100402_lJdW.png&signature=…",
        "pdf_url": "https://plms.example.com/api/v1/files?expires=1789798745&path=mobile%2Fterminations%2F23802%2Finspection.pdf&signature=…",
        "template": [
            {
                "work_id": 12,
                "code": "Appliances",
                "desc": null,
                "sub_works": [
                    {
                        "id": 29,
                        "name": "Door Keys"
                    },
                    {
                        "id": 30,
                        "name": "A/C Remote"
                    },
                    {
                        "id": 31,
                        "name": "Gas Chamber Keys / Regulator"
                    }
                ]
            },
            {
                "work_id": 2,
                "code": "Carpenter",
                "desc": "Test",
                "sub_works": [
                    {
                        "id": 23,
                        "name": "Door Locks - Door Keys,Door Viewer,Wardrobe Keys"
                    },
                    {
                        "id": 24,
                        "name": "Kitchen Cabinets / Kitchen Top"
                    },
                    {
                        "id": 25,
                        "name": "Aluminium Window Locks, Fly Mesh"
                    }
                ]
            }
        ]
    }
}
```

</details>

<details><summary>Example — 409 already submitted / moved on</summary>

Request:

```json
{
    "signature": "data:image/png;base64,…"
}
```

Response `409`:

```json
{
    "message": "This termination is no longer at Handover Assigned.",
    "code": "invalid_state"
}
```

</details>

### `GET /terminations/{id}/refer-back`

**Back-office refer-back comment.** Auth + `X-Device-Id`.

`data` is null when nothing was referred back.

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "data": null
}
```

</details>

### `GET /terminations/{id}/inspection.pdf`

**Inspection report PDF (signed URL).** Auth + `X-Device-Id`.

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "url": "https://plms.example.com/api/v1/files?expires=1789798745&path=mobile%2Fterminations%2F23802%2Finspection.pdf&signature=…"
}
```

</details>

---

## Tenant onboarding

### `GET /onboardings`

**List move-in checklists.** Auth + `X-Device-Id`.

Tabs: `open`, `completed` (includes verified). Rows are created automatically when a contract is activated.

Query parameters:

| Field | Type | Required | Notes |
|---|---|---|---|
| `tab` | string (`open` \| `completed`) | yes |  |
| `building_id` | integer |  |  |
| `updated_since` | string |  |  |
| `per_page` | integer |  |  |
| `page` | integer |  |  |

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "data": [
        {
            "id": 22,
            "contract_id": 78795,
            "contract_no": "TC-2024-0451",
            "contract_start": "2026-08-31T20:00:00.000000Z",
            "status": "open",
            "building": {
                "id": 187,
                "name": "Sama Bldg"
            },
            "unit": {
                "id": 3401,
                "no": "34"
            },
            "tenant": {
                "name": "Yousuf Al Hinai",
                "mobile": "95307144"
            },
            "assigned_user_id": 93,
            "assigned_user": "are.user",
            "started_at": null,
            "completed_at": null,
            "created_at": "2026-09-19T06:04:06.000000Z",
            "updated_at": "2026-09-19T06:04:06.000000Z"
        }
    ],
    "links": {
        "first": "https://plms.example.com/api/v1/onboardings?page=1",
        "last": "https://plms.example.com/api/v1/onboardings?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "links": [
            {
                "url": null,
                "label": "« Previous",
                "page": null,
                "active": false
            },
            {
                "url": "http://localhost:8000/api/v1/onboardings?page=1",
                "label": "1",
                "page": 1,
                "active": true
            },
            {
                "url": null,
                "label": "Next »",
                "page": null,
                "active": false
            }
        ],
        "path": "https://plms.example.com/api/v1/onboardings",
        "per_page": 2,
        "to": 1,
        "total": 1
    }
}
```

</details>

### `GET /onboardings/{id}`

**Checklist detail.** Auth + `X-Device-Id`.

`items[]` is the admin-configured master list merged with what was saved (`entry`, `photos`). `input_type` drives the widget: check | text | number | count | photo | meter. `required` items must be done or N/A before completion.

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "data": {
        "id": 22,
        "contract_id": 78795,
        "contract_no": "TC-2024-0451",
        "contract_start": "2026-08-31T20:00:00.000000Z",
        "status": "open",
        "building": {
            "id": 187,
            "name": "Sama Bldg"
        },
        "unit": {
            "id": 3401,
            "no": "34"
        },
        "tenant": {
            "name": "Yousuf Al Hinai",
            "mobile": "95307144"
        },
        "assigned_user_id": 93,
        "assigned_user": "are.user",
        "started_at": null,
        "completed_at": null,
        "created_at": "2026-09-19T06:04:06.000000Z",
        "updated_at": "2026-09-19T06:04:06.000000Z",
        "items": [
            {
                "item_id": 1,
                "group": "Contract",
                "title": "Contract copy handed over and house rules explained",
                "input_type": "check",
                "required": true,
                "needs_photo": false,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 2,
                "group": "Documents",
                "title": "Tenant ID / passport / visa collected (note expiry)",
                "input_type": "text",
                "required": true,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 3,
                "group": "Keys",
                "title": "Keys and access cards handed over (count)",
                "input_type": "count",
                "required": true,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 4,
                "group": "Utilities",
                "title": "Electricity account no. and opening reading",
                "input_type": "meter",
                "required": true,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 5,
                "group": "Utilities",
                "title": "Water account no. and opening reading",
                "input_type": "meter",
                "required": true,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 6,
                "group": "Unit condition",
                "title": "Living room",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 7,
                "group": "Unit condition",
                "title": "Bedrooms",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 8,
                "group": "Unit condition",
                "title": "Kitchen",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 9,
                "group": "Unit condition",
                "title": "Bathrooms",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 10,
                "group": "Unit condition",
                "title": "Balcony / other",
                "input_type": "check",
                "required": false,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 11,
                "group": "Payment",
                "title": "Payment instructions given / PDC set received",
                "input_type": "check",
                "required": true,
                "needs_photo": false,
                "entry": null,
                "photos": []
            }
        ],
        "progress": {
            "done": 0,
            "total": 11
        },
        "tenant_remark": null,
        "signature_url": null,
        "pdf_url": null,
        "verified_at": null
    }
}
```

</details>

### `PUT /onboardings/{id}/entries`

**Save entries (partial OK).** Auth + `X-Device-Id`.

Upserts by `item_id`; send only what changed. 409 once completed.

JSON body:

| Field | Type | Required | Notes |
|---|---|---|---|
| `entries` | array | yes |  |
| `entries[].item_id` | integer | yes |  |
| `entries[].value` | string |  | max 1000 |
| `entries[].remark` | string |  |  |
| `entries[].done` | boolean |  |  |
| `entries[].not_applicable` | boolean |  |  |

<details><summary>Example — 200</summary>

Request:

```json
{
    "entries": [
        {
            "item_id": 1,
            "done": true,
            "value": null
        },
        {
            "item_id": 2,
            "done": true,
            "value": null
        },
        {
            "item_id": 3,
            "done": true,
            "value": "3"
        },
        {
            "item_id": 4,
            "done": true,
            "value": "ACC 10234567 / 48213"
        },
        {
            "item_id": 5,
            "done": true,
            "value": "ACC 10234567 / 48213"
        },
        {
            "item_id": 6,
            "done": true,
            "value": null
        },
        {
            "item_id": 7,
            "done": true,
            "value": null
        },
        {
            "item_id": 8,
            "done": true,
            "value": null
        },
        {
            "item_id": 9,
            "done": true,
            "value": null
        },
        {
            "item_id": 11,
            "done": true,
            "value": null
        }
    ]
}
```

Response `200`:

```json
{
    "data": {
        "id": 22,
        "contract_id": 78795,
        "contract_no": "TC-2024-0451",
        "contract_start": "2026-08-31T20:00:00.000000Z",
        "status": "open",
        "building": {
            "id": 187,
            "name": "Sama Bldg"
        },
        "unit": {
            "id": 3401,
            "no": "34"
        },
        "tenant": {
            "name": "Yousuf Al Hinai",
            "mobile": "95307144"
        },
        "assigned_user_id": 93,
        "assigned_user": "are.user",
        "started_at": "2026-09-19T06:04:06.000000Z",
        "completed_at": null,
        "created_at": "2026-09-19T06:04:06.000000Z",
        "updated_at": "2026-09-19T06:04:06.000000Z",
        "items": [
            {
                "item_id": 1,
                "group": "Contract",
                "title": "Contract copy handed over and house rules explained",
                "input_type": "check",
                "required": true,
                "needs_photo": false,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 2,
                "group": "Documents",
                "title": "Tenant ID / passport / visa collected (note expiry)",
                "input_type": "text",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 3,
                "group": "Keys",
                "title": "Keys and access cards handed over (count)",
                "input_type": "count",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": "3",
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 4,
                "group": "Utilities",
                "title": "Electricity account no. and opening reading",
                "input_type": "meter",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": "ACC 10234567 / 48213",
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 5,
                "group": "Utilities",
                "title": "Water account no. and opening reading",
                "input_type": "meter",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": "ACC 10234567 / 48213",
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 6,
                "group": "Unit condition",
                "title": "Living room",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 7,
                "group": "Unit condition",
                "title": "Bedrooms",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 8,
                "group": "Unit condition",
                "title": "Kitchen",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 9,
                "group": "Unit condition",
                "title": "Bathrooms",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 10,
                "group": "Unit condition",
                "title": "Balcony / other",
                "input_type": "check",
                "required": false,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 11,
                "group": "Payment",
                "title": "Payment instructions given / PDC set received",
                "input_type": "check",
                "required": true,
                "needs_photo": false,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            }
        ],
        "progress": {
            "done": 10,
            "total": 11
        },
        "tenant_remark": null,
        "signature_url": null,
        "pdf_url": null,
        "verified_at": null
    }
}
```

</details>

### `POST /onboardings/{id}/photos`

**Upload a photo for an item.** Auth + `X-Device-Id`.

Multipart.

Multipart form fields:

| Field | Type | Required | Notes |
|---|---|---|---|
| `item_id` | integer | yes |  |
| `photo` | file | yes | jpeg/png/webp, ≤ 10 MB; resized server-side to 1600 px + 300 px thumbnail |

<details><summary>Example — 201</summary>

Request:

```json
{
    "multipart": {
        "item_id": 1,
        "photo": "(file)"
    }
}
```

Response `201`:

```json
{
    "data": {
        "id": 4,
        "item_id": 1,
        "url": "https://plms.example.com/api/v1/files?expires=1789798746&path=mobile%2Fonboarding%2F22%2F20260919100406_pKbZV2.jpg&signature=…",
        "thumb_url": "https://plms.example.com/api/v1/files?expires=1789798746&path=mobile%2Fonboarding%2F22%2F20260919100406_pKbZV2_t.jpg&signature=…"
    }
}
```

</details>

### `POST /onboardings/{id}/complete`

**Tenant signs → completed.** Auth + `X-Device-Id`.

409 `invalid_state` listing the required items still missing. On success: status `completed`, PDF rendered, back-office notified.

JSON body:

| Field | Type | Required | Notes |
|---|---|---|---|
| `signature` | string | yes | data:image/png;base64,… |
| `tenant_remark` | string |  | max 1000 |

<details><summary>Example — 409 required items missing</summary>

Request:

```json
{
    "signature": "data:image/png;base64,…"
}
```

Response `409`:

```json
{
    "message": "Required items not completed: Contract copy handed over and house rules explained, Tenant ID / passport / visa collected (note expiry), Keys and access cards handed over (count), Electricity account no. and opening reading, Water account no. and opening reading, Living room, Bedrooms, Kitchen, Bathrooms, Payment instructions given / PDC set received",
    "code": "invalid_state"
}
```

</details>

<details><summary>Example — 200</summary>

Request:

```json
{
    "signature": "data:image/png;base64,…",
    "tenant_remark": "All received"
}
```

Response `200`:

```json
{
    "data": {
        "id": 22,
        "contract_id": 78795,
        "contract_no": "TC-2024-0451",
        "contract_start": "2026-08-31T20:00:00.000000Z",
        "status": "completed",
        "building": {
            "id": 187,
            "name": "Sama Bldg"
        },
        "unit": {
            "id": 3401,
            "no": "34"
        },
        "tenant": {
            "name": "Yousuf Al Hinai",
            "mobile": "95307144"
        },
        "assigned_user_id": 93,
        "assigned_user": "are.user",
        "started_at": "2026-09-19T06:04:06.000000Z",
        "completed_at": "2026-09-19T06:04:06.000000Z",
        "created_at": "2026-09-19T06:04:06.000000Z",
        "updated_at": "2026-09-19T06:04:08.000000Z",
        "items": [
            {
                "item_id": 1,
                "group": "Contract",
                "title": "Contract copy handed over and house rules explained",
                "input_type": "check",
                "required": true,
                "needs_photo": false,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": [
                    {
                        "id": 4,
                        "path": "mobile/onboarding/22/20260919100406_pKbZV2.jpg",
                        "thumb": "mobile/onboarding/22/20260919100406_pKbZV2_t.jpg",
                        "created_at": "2026-09-19T06:04:06.000000Z",
                        "url": "https://plms.example.com/api/v1/files?expires=1789798748&path=mobile%2Fonboarding%2F22%2F20260919100406_pKbZV2.jpg&signature=…",
                        "thumb_url": "https://plms.example.com/api/v1/files?expires=1789798748&path=mobile%2Fonboarding%2F22%2F20260919100406_pKbZV2_t.jpg&signature=…"
                    }
                ]
            },
            {
                "item_id": 2,
                "group": "Documents",
                "title": "Tenant ID / passport / visa collected (note expiry)",
                "input_type": "text",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 3,
                "group": "Keys",
                "title": "Keys and access cards handed over (count)",
                "input_type": "count",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": "3",
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 4,
                "group": "Utilities",
                "title": "Electricity account no. and opening reading",
                "input_type": "meter",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": "ACC 10234567 / 48213",
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 5,
                "group": "Utilities",
                "title": "Water account no. and opening reading",
                "input_type": "meter",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": "ACC 10234567 / 48213",
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 6,
                "group": "Unit condition",
                "title": "Living room",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 7,
                "group": "Unit condition",
                "title": "Bedrooms",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 8,
                "group": "Unit condition",
                "title": "Kitchen",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 9,
                "group": "Unit condition",
                "title": "Bathrooms",
                "input_type": "check",
                "required": true,
                "needs_photo": true,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            },
            {
                "item_id": 10,
                "group": "Unit condition",
                "title": "Balcony / other",
                "input_type": "check",
                "required": false,
                "needs_photo": true,
                "entry": null,
                "photos": []
            },
            {
                "item_id": 11,
                "group": "Payment",
                "title": "Payment instructions given / PDC set received",
                "input_type": "check",
                "required": true,
                "needs_photo": false,
                "entry": {
                    "value": null,
                    "remark": null,
                    "done": true,
                    "not_applicable": false
                },
                "photos": []
            }
        ],
        "progress": {
            "done": 10,
            "total": 11
        },
        "tenant_remark": "All received",
        "signature_url": "https://plms.example.com/api/v1/files?expires=1789798748&path=mobile%2Fonboarding%2F22%2Fsignature_20260919100406_1ONz.png&signature=…",
        "pdf_url": "https://plms.example.com/api/v1/files?expires=1789798748&path=mobile%2Fonboarding%2F22%2Fonboarding.pdf&signature=…",
        "verified_at": null
    }
}
```

</details>

### `GET /onboardings/{id}/onboarding.pdf`

**Onboarding report PDF (signed URL).** Auth + `X-Device-Id`.

<details><summary>Example — 200</summary>

Response `200`:

```json
{
    "url": "https://plms.example.com/api/v1/files?expires=1789798748&path=mobile%2Fonboarding%2F22%2Fonboarding.pdf&signature=…"
}
```

</details>

---

## Files

### `GET /files`

**Signed file download.** No auth.

Never build this URL yourself — use the `url`/`thumb_url`/`pdf_url`/`signature_url` values from responses. Valid 15 minutes, no headers needed (hand it to the OS viewer / image cache). 403 when expired or tampered, 404 when the file is gone.

Query parameters:

| Field | Type | Required | Notes |
|---|---|---|---|
| `path` | string | yes |  |
| `expires` | integer | yes |  |
| `signature` | string | yes |  |

---

## Changelog

| Date | Change |
|---|---|
| 2026-09-19 | v1 — initial contract (Phase 2 of the platform upgrade) |
