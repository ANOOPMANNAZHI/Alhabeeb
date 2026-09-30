# Message Templates, Bulk Send and Template-driven System Emails — Design

Date: 2026-09-29
Status: draft for review

## Goal

One place under the existing **Mail** menu to (1) keep editable email + SMS
templates, (2) send them in bulk or individually to tenants, landlords and
staff filtered by location / building, and (3) drive every automatic email
PLMS already sends from those templates, so wording can change without code.

## Current state (inventory, 2026-09-29)

- 19 Mailable classes, all markdown views with hard-coded text, all sent
  synchronously (`QUEUE_CONNECTION=sync`) from `plmsalerts@alhabib.om`
  (Office 365, from `.env`). About 20 sites fall back to SMS (`sendSms()`)
  when the recipient has no email.
- Live and sending: complaint registered / completed / closed, key accepted
  (tenant + landlord; empty body), renewal form (PDF), inspection details,
  tenant birthday (scheduled 10:00), cheque bounce to ARE, enquiry new /
  assigned / rejected, complaint assigned, ~10 legal alerts, employee login /
  reset password (plain-text passwords), Mass Mail compose.
- Written but never sent: tenant cheque bounce (sends commented out),
  termination details (no link), ARE Residence-ID reminder (scheduler call
  commented out).
- Mail → Compose / Inbox (`MassMailController`, `mass_mails`,
  `preferred_mails`) picks recipients one by one; used 3 times, last 2022.
- General Settings → Email (SMTP rows) is stored but never used.
- No record of sent email except Mass Mail. 7,007 of 7,513 tenants have an
  email address.
- The live scheduler runs `schedule:run` every minute (confirmed by the user;
  birthday emails and PDC reminder SMS depend on it).

## Roadmap (three projects, each released on its own)

| Project | Delivers | Depends on |
|---|---|---|
| **A. Templates** | Template store, placeholder engine, Templates screen with preview | – |
| **B. Send** | Recipient builder (tenants active/past, landlords, staff; location → building → individual), email / SMS / both, review screen, background queue at a controlled rate, Sent log (replaces Compose / Inbox) | A |
| **C. System emails on templates** | Each existing email becomes a system template, in stages: C1 cheque bounce (ARE + tenant, all AREs), C2 tenant-facing, C3 staff / legal, C4 the rest (login / reset move to a set-password link) | A, and B's Sent log and queue |

Decisions already taken with the user:
- Build order A → B → C.
- Recipients for B: active tenants, past tenants, landlords, staff.
- Templates carry an email version and an SMS version.
- Bulk sending runs on a database queue worked by the existing scheduler
  (details in project B's spec). Browser-driven batches were rejected.

B and C each get their own detailed spec before they are built. This
document specifies **Project A** in full.

---

## Project A — Templates

### Data

New table `message_templates`:

| Column | Type | Notes |
|---|---|---|
| id | increments | |
| key | string(100) null, unique | set only on **system** templates (e.g. `pdc_bounce_are`); code looks templates up by it |
| name | string(150) | shown in lists |
| kind | string(10) | `system` or `bulk` |
| audience | string(20) | `tenant`, `landlord`, `staff`, `any` — decides which placeholders are offered |
| email_subject | string(255) null | |
| email_body | text null | HTML from the editor |
| sms_body | text null | plain text |
| is_active | boolean default true | a system template that is inactive falls back to the current hard-coded email (project C) |
| created_by, updated_by | integer null | |
| timestamps, soft deletes | | system templates cannot be deleted |

A template needs at least one channel: subject + body, or SMS text.

### Placeholders

- Written as `{name}` in subject, body and SMS text.
- A registry class (`MessagePlaceholders`) defines, per audience, the
  placeholders and a sample value for preview:
  - common: `{today}`, `{company_name}`
  - tenant: `{tenant_name}`, `{contract_no}`, `{building}`, `{unit}`,
    `{location}`, `{rent}`, `{contract_start}`, `{contract_end}`, `{mobile}`
  - landlord: `{landlord_name}`, `{building}`, `{location}`
  - staff: `{employee_name}`, `{role}`
  System templates (project C) register their own extra placeholders
  (e.g. `{cheque_no}`, `{amount}`, `{bounce_reason}`, `{remarks}`).
- The editor offers only the placeholders valid for the template's audience
  (or its system key), inserted from a dropdown at the cursor.

### Rendering

`MessageTemplateRenderer::render(MessageTemplate $t, array $values)` returns
`['subject' => …, 'html' => …, 'sms' => …, 'unknown' => [placeholders with no value]]`.

- Values are inserted as plain text: HTML-escaped in subject and body,
  raw in SMS. Template content is never executed as Blade/PHP.
- Unknown placeholders are left visible (`{xyz}`) and reported, so preview
  shows what is missing instead of silently dropping text.
- The HTML body is cleaned on save: `<script>`, `<style>`, `<iframe>`,
  `<object>`, `<embed>` and `on*=` / `javascript:` attributes removed.
- The email is sent inside the existing PLMS mail layout (logo header,
  footer), like today's markdown emails.

### Screens (Mail → Templates)

- **Templates list**: name, kind (System / Bulk), audience, channels
  (Email, SMS), active, last updated; filter by kind / audience / text;
  Create button (bulk templates only).
- **Create / edit**:
  - name, audience (fixed for system templates), active switch
  - email subject + body (TinyMCE, as on Compose) with the placeholder dropdown
  - SMS text with placeholder dropdown and a live counter: characters,
    SMS parts (160 per part, 70 when the text has Arabic / other Unicode)
  - **Preview**: email and SMS rendered with the sample values, unknown
    placeholders highlighted
- System templates: key shown read-only, cannot be deleted; a
  "Reset to default" action is added in project C when defaults exist.

### Permissions and menu

- `view_message_templates` (list + preview) and `manage_message_templates`
  (create / edit / delete), attached to a new **Templates** menu row under
  the existing **Mail** parent (found by the Compose / Inbox rows' parent,
  since menu ids differ between databases).
- Granted by the migration to `super_admin` and `backoffice_manager`.

### Out of scope for A

Sending (project B), system template rows and wiring existing emails
(project C), attachments, per-language template variants (a template may
contain Arabic and English text; separate language versions can come later).

### Testing

- Unit: renderer (placeholders, escaping, unknown placeholders, SMS raw),
  SMS part counter (GSM vs Unicode), HTML cleaner, placeholder registry.
- In-process against the local DB (rolled back): create / edit / delete,
  system template protection, permission checks, preview output.
- Screenshots of list, editor and preview.

### Deploy

1. Migrations: `message_templates` table; permissions + menu row.
2. Upload code; `route:clear`, `view:clear`.
3. Assign the two permissions to any other roles in Role settings.
