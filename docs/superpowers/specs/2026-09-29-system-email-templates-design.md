# System Emails on Templates (Project C) — Design

Date: 2026-09-29
Status: draft for review
Builds on: Project A (`2026-09-29-message-templates-design.md`, branch `feature/message-templates`)

## Goal

Every automatic email and SMS PLMS sends today is driven by an editable
system template on Mail → Templates, so wording, the SMS and on/off can be
changed without code; every send is recorded in a sent log.

## Decisions taken with the user (2026-09-29)

- Order changed: C1–C4 are built **before** bulk sending (B).
- The sent log is built now (in C1); B reuses it.
- **Active off = the message is not sent at all.** Emails switched off in
  code today arrive as templates switched off.
- **Send SMS switch per template.** On: the SMS text goes to the recipient's
  mobile every time (with or without email). Off: never. Replaces today's
  "SMS only when there is no email" pattern. Defaults: on only for
  `complaint_assigned`, `complaint_subassigned`, `pdc_due_reminder`.
  People without email therefore get nothing for other messages until SMS is
  switched on; the log shows them as skipped (no email address).
- Fixes included: cheque bounce email to **all** AREs of the building;
  sign-off is a fixed "Al Habib Team" instead of the logged-in employee;
  blank-name bugs (termination email, enquiry reject) and "reassign reaches
  only the first user"; key-accepted email gets real wording.
- Employee login / reset stop emailing passwords: a **set-password link**
  is sent instead (C4).
- Mass Mail Compose is out of scope (replaced by B later).

## Change to Project A

`message_templates.is_active` for system templates means "send it" (A's
spec said an inactive system template falls back to hard-coded wording —
superseded). New column `sms_enabled` (boolean, default false); the editor
shows a **Send SMS** switch for system templates. Bulk templates ignore it
(B chooses channels on its send screen).

## Architecture

### Defaults registry — `SystemTemplateDefaults`
One PHP class listing every system template by key:
`name, audience, stage, email_subject, email_body (HTML), sms_body,
is_active, sms_enabled, placeholders (name => [label, sample]),
blocks (names of HTML block placeholders)`. Wording = today's text with the
fixes above, signed "Al Habib Team".

Uses:
1. Each stage's migration inserts its keys (insert-if-missing, never
   overwrites edited rows).
2. If a row is missing at send time (code uploaded before the migration),
   the sender uses the default (respecting its default `is_active`) and logs
   `template_missing` in the log row's note — mail does not go silent.
3. **Reset to default** on the editor for system templates (manage
   permission, confirmation).
4. Registers each key's placeholders with `MessagePlaceholders::registerSystem`
   (done in the BackOffice service provider boot) so the editor and preview
   offer them.

### Renderer change
`MessageTemplateRenderer::render($t, $values, $blocks = [])`. Block
placeholders (e.g. `{inspection_details}`) are replaced in the email body by
trusted HTML built in code, in the same single pass as values (no
re-expansion); in the subject and SMS they become empty. Values are still
escaped in the body, raw in subject and SMS.

### Central sender — `SystemMessenger`
```
SystemMessenger::send($key, MessageRecipient $to, array $values, array $options = [])
  MessageRecipient: name, email (nullable), mobile (nullable), cc (array)
  options: blocks (name => html), attachments ([data, filename, mime]),
           related ([type, id]) for the log
returns ['email' => status, 'sms' => status]
```
1. Load the system template by key (or the default); common values
   (`{today}`, `{company_name}`) are added.
2. Inactive → one log row `skipped_off`, nothing sent.
3. Email: needs subject + body and an address, else `skipped_no_address`
   / nothing if the template has no email version. Sent with one generic
   `TemplatedMail` (HTML view reproducing today's logo header and footer,
   from `plmsalerts@alhabib.om`), CC and attachments as given.
4. SMS: only if `sms_enabled` and `sms_body`; no mobile → `skipped_no_mobile`;
   `sendSms()` false → `failed` (when SMS is disabled in settings the note
   says so).
5. **Errors never break the user's action**: a mail/SMS exception is caught,
   logged as `failed` with the message. (Today an SMTP error throws and can
   break the save that triggered it.)
6. Every attempt writes a log row.

Value helpers so send sites stay short: `MessageValues::contract($contract)`
→ tenant_name, contract_no, building, unit, location, rent, contract_start,
contract_end, mobile; `MessageValues::employee($user)` → employee_name, role.

### Sent log
Table `message_log`: id, template_key, channel (email|sms), recipient_name,
recipient (email address or mobile), cc, subject, body (rendered HTML or SMS
text), status (sent | failed | skipped_off | skipped_no_address |
skipped_no_mobile), note/error (text), related_type (string 50), related_id,
triggered_by (user id, null for scheduled jobs), created_at. Indexes on
created_at, template_key, (related_type, related_id).

Screen **Mail → Sent log** (permission `view_message_log`, granted to
super_admin and backoffice_manager): filters date range (default last 7
days), template, channel, status, recipient text; newest first, paginated;
row detail shows the email in a sandboxed frame / the SMS text. No resend
(B may add it). Rows are kept indefinitely (clean-up can come later).

### Old code
Each stage replaces its send sites with `SystemMessenger::send` and deletes
the Mailable classes and views it no longer uses (backup copies such as
`bkup2422021/`, `TenantContractEditController--.php` are not touched).
`SendMassEmail` stays. The in-app notifications (events) are unchanged.

## Stages

Each stage is released on its own (code, then its migration).

### C1 — Base + cheque bounce
Base: `sms_enabled` column, `message_log` table, sent log screen + menu +
permission, defaults registry, renderer blocks, `SystemMessenger`,
`TemplatedMail`, `MessageValues`, editor Send SMS switch + Reset to default.

| Key | Recipient | Extra placeholders | Default |
|---|---|---|---|
| `pdc_bounce_are` | every ARE assigned to the building (today: last one only) | employee_name (the ARE, from the staff audience), cheque_no, amount, cheque_date, bounce_reason, remarks + tenant_name, contract_no, building, unit | on, SMS off |
| `pdc_bounce_tenant` | tenant | cheque_no, amount, cheque_date, bounce_reason, remarks + the tenant audience set | **off**, SMS off |

Send sites: `PdcController` store (inside the row loop), and
`tenantAjaxPdcBounceSearch` (both branches) — currently :168, :435, :468 for
the ARE; the tenant send is added at the same three places (template off).

### C2 — Tenant and landlord
| Key | Trigger (current site) | Notes |
|---|---|---|
| `complaint_registered` | `MaintenanceController@store` :232, tenant complaints | {complaint_no} |
| `complaint_completed` | `ComplaintStageController@generateServiceReport` :1851 | |
| `complaint_closed` | `complaintCloseModalAction` :1336 | |
| `key_accepted_tenant` / `key_accepted_landlord` | `KeyController` :383 / :393 | new wording; {landlord_name} |
| `renewal_form` | `TenantRenewalController@generatePDF` :2026 | PDF attachment and CC (sender + ARE) kept in code; email_count still incremented only when sent; no email → PDF download as today |
| `inspection_details` | `TenantTerminationController@inspectionSendMail` :2781 | block {inspection_details} = today's tables |
| `termination_details` | `terminationEmail` :2665 | **off**; block {termination_details}; name bug fixed |
| `tenant_birthday` | cron `tenantBirthdayNotification` | |
| `pdc_due_reminder` | cron `pdcExpiryReminderSms` | SMS only, SMS **on**; {cheque_no} {amount} {cheque_date} |

### C3 — Staff and legal
| Key | Trigger | Notes |
|---|---|---|
| `complaint_assigned` / `complaint_subassigned` | `ComplaintStageController` :548 / :775 | SMS **on**; {works_code} {work_desc} {complaint_no} {complaint_date} {complaint_mobile} + building/unit |
| `enquiry_new` | `SalesEnquiryController@store` :298 | to the enquirer; {enquiry_no} {enquiry_type} |
| `enquiry_assigned` | `TenantStageController` :228, :2405 | |
| `enquiry_reassigned` | `assignNotify` :2668 | all notify users (fix) |
| `enquiry_rejected` | `moveNextStage` :1037 | name fix |
| `legal_deposit_refund_posted`, `legal_rental_income_posted`, `legal_pdc_posted`, `legal_receipt_posted`, `legal_contract_revoked`, `legal_case_received`, `legal_case_approved`, `legal_advisor_referback`, `legal_case_closed`, `legal_advisor_closed`, `legal_status_note` | sites listed in the inventory (DepositRefund, Invoice, Routines ×2, TenantContractEdit ×2, LegalController ×5) | each its own subject/text; {contract_no}, {invoice_no}, {cheque_no}, {receipt_no}, {status_note} where relevant |
| `legal_case_referback`, `legal_advisor_approved` | LegalController :650, :671 (commented out today) | **off** |

### C4 — Accounts and the rest
| Key | Trigger | Notes |
|---|---|---|
| `employee_welcome` | `EmployeeController@store` :180 | new user gets a random unguessable password (not username+id) and a **set-password link**; {username} {set_password_link} |
| `employee_password_reset` | `EmployeeController@resetPassword` :390 | sends a link; current password keeps working until changed |
| `password_forgot` | login page "Forgot password" | `User::sendPasswordResetNotification` routed through the messenger; {reset_link} |
| `are_residence_reminder` | cron `areReminderNotification` | **off**; the cron call is re-enabled so switching the template on is enough |

Implemented notes: the ARE reminder runs only at 09:00 (it is called from the
every-minute command) and sends one message per ARE per tenant/contract;
set-password and reset links are replaced by `[link hidden]` in the sent log.

Links use Laravel's password broker tokens (`password_resets`, existing
reset page); link lifetime raised from 60 minutes to 24 hours
(`config/auth.php` expire 1440). A user without an email cannot receive a
link — logged `skipped_no_address`; the admin sees a message saying so.
The dead `sendPasswordResetNotification` in `ForgotPasswordController` is
removed.

## Testing

- Unit: renderer blocks (escaping, no re-expansion, blocks empty in
  subject/SMS), defaults registry (every key has name, audience, a channel,
  placeholders used in its text are registered), messenger decisions with a
  fake mailer/SMS (off, no address, SMS switch, exception → failed, missing
  row → default).
- In-process against local DB (rolled back, `Mail::fake`, SMS stubbed): each
  stage's send sites produce the expected log rows and recipients (e.g. two
  AREs → two emails).
- Screenshots: sent log, editor with Send SMS / Reset to default, one real
  rendered email per stage.

## Deploy (per stage)

1. Upload code first (the Sent log menu row needs its route to exist).
2. Run that stage's migrations; `route:clear`, `view:clear`.
3. Review the stage's templates on Mail → Templates; switch on the ones that
   arrive off when wanted, and switch SMS on where needed.
