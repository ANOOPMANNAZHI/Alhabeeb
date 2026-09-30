# Bulk Send (Project B) — Design

Date: 2026-09-30. Roadmap: `docs/superpowers/specs/2026-09-29-message-templates-design.md` (project B).
Builds on project A (Mail → Templates, bulk templates) and C1–C4 (`SystemMessenger`, `message_log`, Sent log).

## Goal

From PLMS, send one message to a group — all tenants, all landlords, all
staff, or a narrower group such as the tenants of one building — using a
Bulk template (or a one-off text), by email and optionally SMS, sent in the
background at a safe rate, with every message recorded in the Sent log.

Success: a notice can reach ~7,000 tenants without freezing the page or
overloading the Office 365 mailbox, and afterwards the sender can see who got
it, who failed and who was skipped.

## Decisions taken with the user (2026-09-30)

- **Who may send:** a new permission `bulk_message_send`, given to
  `super_admin` and `backoffice_manager` (same roles as the Templates menu).
- **SMS is a per-send choice:** "Also send SMS" tick box, **off by default**;
  when ticked, the review step shows the SMS count and the confirm repeats it.
  SMS only (email unticked) is allowed. SMS needs SMS text; a template without
  SMS text cannot send SMS.
- **Rate:** 25 recipients per minute; bulk sending pauses for the day after
  8,000 bulk emails and continues after midnight. Both are settings.
  (Office 365 SMTP limits: 30 messages/minute, 10,000 recipients/day per mailbox.)
- **Old Mail → Compose / Inbox stays as it is**; Send is added next to it.
- **Past tenants** = no active contract, last contract ended within the last 12 months.
- **Filters:** location and building for tenants and landlords; roles for staff.
- **Individuals:** after choosing a group, the review list lets the sender
  untick people and add people by name search (within the same group).
- **No attachments** (text/images in the body only).
- **Send now** only, with **Pause / Resume / Cancel**; no scheduling.
- **Approach 1:** PLMS's own queue tables worked by the every-minute scheduler
  (Laravel's job queue and browser-driven batches rejected).

## Architecture

### Data

`bulk_sends` — one row per send:

| column | type | notes |
|---|---|---|
| id | bigint | |
| template_id | int null | the bulk template used; null for a one-off message |
| audience | string(20) | `tenant_active`, `tenant_past`, `landlord`, `staff` |
| filters | json/text | what was chosen (locations, buildings, roles), for the record |
| send_email, send_sms | boolean | channels |
| email_subject, email_body, sms_body | text null | **frozen copy** taken at Start; edits to the template later do not affect this send |
| status | string(20) | `running`, `paused`, `cancelled`, `done` |
| note | string(255) null | e.g. "Daily limit reached, continues tomorrow", "Paused: mail server refusing" |
| total, sent, failed, skipped | int | counters (recipient rows) |
| created_by | int | |
| created_at, updated_at, finished_at | timestamps | |

`bulk_send_recipients` — one row per person in a send:

| column | type | notes |
|---|---|---|
| id | bigint | |
| bulk_send_id | bigint, indexed | |
| person_type | string(10) | `tenant`, `landlord`, `staff` |
| person_id | int | tenant.id / landlord (vendor) id / users.id |
| name, email, mobile | strings null | copied at Start |
| values | text (json) | placeholder values worked out at Start |
| status | string(12), indexed with bulk_send_id | `pending`, `sent`, `failed`, `skipped`, `cancelled` |
| note | string(255) null | failure reason / skip reason |
| processed_at | timestamp null | |

`message_log` rows written for bulk sends use `template_key` = `bulk:<template_id>`
(or `bulk:one-off`), `related_type` = `bulk_send`, `related_id` = the send id.

### Units

- **`RecipientFinder`** (`Modules/BackOffice/Services/Bulk/RecipientFinder.php`)
  — turns (audience, filters) into people, one row per person:
  - active tenants: tenants with an active contract (`tenant_contract_status` = 1);
  - past tenants: no active contract, newest contract ended within the last 12 months;
  - landlords: landlords (vendors) with a landlord contract on the chosen buildings;
  - staff: active users (`user_type_status` true) with any of the chosen roles (or all);
  - location → building filter for tenants and landlords (a person counts if
    they have/had a contract in one of the buildings);
  - placeholder values: tenant values from the tenant's **newest contract in
    the chosen buildings** (newest overall without a building filter; last
    contract for past tenants), using the existing `MessageValues` helpers;
  - email: tenant contact email, else personal email (as the system emails);
    mobile: contact number; landlord and staff as in the existing messages.
  - `count(audience, filters)` returns total / with email / with mobile;
    `people(...)` returns the list. Review screen and Start use the same code.
- **`BulkSendService`** — creates a send: validates channels vs. template
  (SMS needs SMS text; template audience must match the group — `any` fits
  all), freezes the texts, writes the recipient rows (minus unticked, plus
  added), sets counters.
- **`BulkSender`** — the every-minute job: see "Sending".
- **`SystemMessenger`** — extended with
  `sendTexts($logKey, array $texts, MessageRecipient $to, array $values, array $options = [])`
  where `$texts` = `['email_subject' => ?, 'email_body' => ?, 'sms_body' => ?]`
  and `$options['channels']` = `['email' => bool, 'sms' => bool]` (the send's
  choice, replacing the template's is_active / sms_enabled switches). It
  renders, sends and logs exactly like `send()` (same renderer, `[link hidden]`
  masking, `message_log` row per channel) and returns
  `['email' => status|null, 'sms' => status|null]`. `send()` is unchanged.
- **Settings:** `bulk_emails_per_minute` = 25 and `bulk_emails_per_day` = 8000
  in General Configuration → Settings (`configuration` table).

### Sending

- New command `bulk-messages:send`, scheduled **every minute** with
  `withoutOverlapping()` in `app/Console/Kernel.php`.
- Each run: take the next N (= per-minute setting) `pending` recipient rows of
  `running` sends, oldest send first; send email and/or SMS as the send
  chose; mark each row `sent` / `failed` / `skipped` with a note; update the
  send's counters; when a send has no pending rows left, mark it `done`.
- **Skipped:** no email (email channel) / no mobile (SMS channel). With both
  channels the row is `sent` if any channel was sent, and the Sent log holds
  one row per channel.
- **Daily limit:** before sending, count today's bulk emails in `message_log`
  (`related_type` = `bulk_send`, channel email, status sent/failed). At the
  per-day setting stop for the day and put the note "Daily limit reached,
  continues tomorrow" on the running sends; clear it when sending resumes.
  System emails are not counted and never wait.
- **Safety stop:** 5 consecutive email failures within a send → the send is
  set `paused` with the note "Paused: mail server refusing".
- **No automatic retry.** A done send offers **Resend to failed**, which
  creates a new send with only the failed people (same frozen texts).
- **Pause / Resume / Cancel:** Pause sets `paused` (job skips it); Resume sets
  `running`; Cancel sets `cancelled` and all `pending` rows `cancelled`.

## Screens

Menu **Mail → Send** (permission `bulk_message_send`), inserted under the Mail
parent like Templates / Sent log. Menu row, permission and role grants are
added by a migration (same pattern as `2026_09_29_000001`).

1. **Message:** pick a Bulk template, or "write a one-off message" (subject,
   body with the placeholder list, SMS text). Channels: ☑ Email, ☐ SMS (SMS
   disabled when there is no SMS text).
2. **Recipients:** one group per send — Active tenants / Past tenants /
   Landlords / Staff. Tenants & landlords: Locations (multi) → Buildings
   (multi, optional). Staff: Roles (multi) or All staff. Template audience
   must match (`any` fits all). Live count under the filters.
3. **Review:** paged list (50) with search, tick boxes to leave people out,
   **Add person** (name search in the same group); preview of the email /
   SMS for the first person with real values; summary "Email to X, SMS to Y,
   about Z minutes"; **Start sending** with a confirm (repeats the SMS count
   when SMS is on).
4. **Sends list:** who, when, template, audience, status, sent / failed /
   skipped / pending, note; Pause / Resume / Cancel / Resend to failed.
   Opening a send lists its recipients and statuses, linking to the Sent log.

## Error handling

- Mail/SMS exceptions never stop the job: the row is `failed` with the
  message in `note` (secrets masked as today).
- A send whose template was deleted still works (frozen texts).
- If the job crashes mid-batch, rows not yet marked stay `pending` and are
  picked up next minute; a row is marked immediately after its own send, so
  at most the one in-flight message could be sent twice.

## Testing

- Unit/feature tests with `Mail::fake` and a stubbed SMS sender, inside
  `DatabaseTransactions` on the local DB:
  - RecipientFinder: each audience, location/building filters, one row per
    person, past-tenant window, counts with/without email/mobile, newest-contract rule.
  - BulkSendService: channel validation, audience match, frozen texts,
    untick/add, counters.
  - BulkSender: 25 per run, oldest send first, skipped/failed/sent marking,
    done detection, daily limit stop and next-day resume (Carbon test now),
    safety stop after 5 failures, pause/resume/cancel, Resend to failed.
  - Screens: permission gate, the three steps, the sends list actions.
- No real email or SMS is sent by any test.

## Deploy

1. Upload code first (the menu row needs its route).
2. Run the B migrations (tables, settings, menu/permission).
3. `php artisan config:clear`, `route:clear`, `view:clear`.
4. Live already runs `schedule:run` every minute, so the new command starts by itself.
5. Create the first Bulk templates on Mail → Templates, then try a small send
   (e.g. staff, one role) before a large one.

Implemented notes: migrations 2026_09_30_200000 (tables), 200001 (rate
settings), 200002 (menu + permission — run AFTER the code upload). On the
local DB the Send menu row is kept at status 0 until the branch is merged,
because master has no bulk-send routes yet. Migration 200003 adds
`bulk_sends.resent_send_id` (Resend to failed runs once per send, only for a
done or cancelled send); run it with the code. Never run
`artisan bulk-messages:send` by hand: it sends real email/SMS and the
scheduler already runs it every minute (overlap lock expires after 10
minutes; the rate setting is capped at 30/min). A row that throws is marked
failed with `error: ...` in its note; failed rows carry the mail/SMS failure
reason in their note.

## Out of scope

Attachments, scheduled sends, Excel upload of recipients, retiring Mail →
Compose / Inbox, AREs sending for their own buildings, automatic retries.
