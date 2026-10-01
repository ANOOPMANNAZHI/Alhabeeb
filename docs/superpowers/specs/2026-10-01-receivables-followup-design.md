# Receivables Follow-up — design

Date: 2026-10-01 · Branch: `feature/receivables-followup`

## Purpose

Today the only receivables screen is a report. AREs take it, phone tenants, and
write down by hand who they called and what date the tenant promised to pay.
This section replaces those notes: a working list of agreements that owe money,
a one-click follow-up (comment + optional promised date, who/when captured
automatically), and the full follow-up history per agreement as a timeline.

Success: an ARE opens one screen, sees last month's balances for their own
buildings, records each call in seconds, and anyone can see the full call
history and broken promises without asking.

## Decisions (agreed with the user)

| Topic | Decision |
|---|---|
| Follow-up belongs to | The tenant contract (agreement). A renewal starts a fresh history. |
| Visibility | Existing ARE rule: `are` = own buildings, `are_team_lead` = team + own, everyone else = all. |
| Which rows | Same logic as Receivable Report v2: legal excluded, active buildings, **Balance > 0**. |
| Promised date | Optional. Comment required. |
| List extra column | Latest promised date, red when passed and balance still due. |
| Figures source | Approach A: base calculation of v2, cached per as-on date, refreshed in background. |

## Findings that shaped the design

- Receivable Report v2 (`tenantrentreceivable_v2`) is a wrapper around the base
  plpgsql function `tenantrentreceivable(date)` (latest definition:
  `database/migrations/2026_09_20_000000_fix_terminated_receivable_net_amount.php`).
- Figures are **cumulative as on a date**: Total Due = rent from the contract's
  effective date up to and including the as-on month (terminated: pro-rata value;
  expired: contract value); Amount Received = all rent receipts (type 0) on the
  contract up to today; Balance (`netamtdue`) = Due − Received.
- The base function takes ~15 s locally; v2 ~17 s.
- The v2 wrapper repeats each contract once per active ARE assignment row of its
  building (locally 10,207 rows for 3,404 contracts as on 2026-09-30; e.g. Bait
  Maskan-A has 3 rows). The v2 report screen does not de-duplicate. **Separate
  bug, out of scope here** — reported to the user. This feature therefore uses
  the base function (one row per contract; 3,774 rows / 3,764 contracts) and
  applies v2's legal exclusion itself.
- v2's filtered variant (`tenantrentreceivablecompo_v2`) ORs its filters and uses
  `netamtdue >= 30`; this screen ANDs its filters and uses `> 0` (user's choice).
- Management types: 1 Comprehensive, 2 Normal, 3 Commission (`buildings.management_id`).

## Data

### New table `receivable_followups`

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | |
| `tenant_contract_id` | integer, indexed | FK-style link to `tenant_contracts.id` (no DB constraint, like the rest of the schema) |
| `comment` | text, required | max 1000 chars (validated) |
| `promised_date` | date, nullable | not in the past when saved |
| `balance_at_followup` | decimal(14,3), nullable | balance shown when saved |
| `created_by` | integer | `Auth::id()` |
| `created_at`, `updated_at` | timestamps | `created_at` = when followed up |

Append-only from the UI: no edit or delete routes. Index on
(`tenant_contract_id`, `created_at`).

### Figures — `ReceivableFigures` service

- `asOnDate(month, year)`: last day of that month; current month → today.
- `rows(asOn)`: `SELECT contract_no, totaldue, amount_received, netamtdue,
  receipt_date FROM tenantrentreceivable(:asOn)`, then
  - drop contracts with any `legal` row (same rule as v2),
  - keep `netamtdue > 0` (the base function already keeps active buildings and `> 0`),
  - de-duplicate by `contract_no` (the base function has ~10 exact repeats),
  - map `contract_no` → `tenant_contracts.id`.
  Result: array keyed by contract id → [due, received, balance, last_received].
- Cached with `Cache::put('receivable_followup:'.asOn, [computed_at, rows], 1 day)`.
  `get(asOn)` returns the cache or computes on demand; `refresh(asOn)` recomputes.
- If a refresh throws, the previous cache entry is kept and the error is logged.

### Background refresh

New command `receivables:refresh-followup` → refreshes last month's as-on date
(and the current month if it has been opened today). Scheduled in
`app/Console/Kernel.php` `everyFifteenMinutes()->withoutOverlapping(30)`.

### Live parts (never cached)

Tenant (name, contact person, `tenant_contact_no`), contract no + period,
building, unit, management type — read from `tenant_contracts` and relations for
the contract ids in the page. Last follow-up and latest promised date —
aggregated from `receivable_followups`.

## Screens

### Menu and permissions

- Menu row: **PLM Module → Operations → Receivables Follow-up**
  (route `receivable-followup.index`), added by migration found by name,
  following `2026_09_10_000002_add_cash_tenant_report_menu.php`.
- Permissions: `view_receivable_followup` (list, history) and
  `add_receivable_followup` (save). Migration grants both to roles
  super_admin, are, are_team_lead, backoffice_executive, backoffice_manager.

### List

- Filters (AND): **Month** (month + year, default last month), **Building**
  (autocomplete limited to the user's buildings), **Management type**
  (All / Comprehensive / Normal), **Search** (tenant name, contract no or unit no).
- Scoping: contracts whose building passes `Building::areFilter()`.
- Columns: Tenant (name; contact person + phone below) · Agreement (contract no;
  start – end below) · Building · Unit No · Last Received · Total Due · Amount
  Received · Balance · Last Follow-up (date-time + user, or —) · Promised Date
  (red when < today and balance > 0) · Actions (Follow-up, View).
- Default sort Balance desc; sortable headers; 25 per page; totals row for Due,
  Received, Balance over the whole filtered list.
- Header line: "Figures as of HH:MM · Refresh". Refresh (POST) recomputes the
  chosen month; a "Calculating…" state shows while the request runs.
- Filtering/paging over AJAX (same pattern as other PLMS lists).

### Follow-up form (modal)

- Read-only header: tenant, contract no, building, unit, current balance, last
  promised date.
- Comment (required, ≤ 1000), Promised Date (optional, ≥ today).
- Save via AJAX → JSON. On success the modal closes and that row's Last
  Follow-up / Promised Date cells update in place. Validation errors show inside
  the modal.
- Server: `permission:add_receivable_followup`; contract must be within the
  user's ARE scope (403 otherwise). `created_by`, `created_at` and
  `balance_at_followup` (from the cached figures for the list's as-on date) are
  set server-side.

### Timeline (View, modal)

- Same read-only header.
- Entries newest first: date-time · user name · comment · promised date with
  status · balance at the time.
- Promise status:
  - **Kept** — a rent receipt (`receipts_generation` type 0, not cancelled, not
    deleted) on this contract dated after the follow-up and on/before the
    promised date;
  - **Broken** — promised date < today and no such receipt;
  - **Upcoming** — promised date ≥ today and no such receipt yet.
- Empty: "No follow-ups yet." No edit/delete.
- Same ARE scope check as the form.

## Error handling

- Background refresh failure → previous figures stay, error logged.
- Unknown/forbidden contract → 404/403 JSON for modal requests.
- Production runs PHP < 7.4: no arrow functions, no typed properties, no `??=`.

## Files

New:
- `database/migrations/2026_10_01_100000_create_receivable_followups.php`
  (table + menu + permissions)
- `Modules/BackOffice/Entities/ReceivableFollowup.php`
- `Modules/BackOffice/Services/ReceivableFigures.php`
- `Modules/BackOffice/Http/Controllers/ReceivableFollowupController.php`
- `app/Console/Commands/RefreshReceivableFollowup.php`
- Views in `Modules/BackOffice/Resources/views/ReceivableFollowup/`:
  `index.blade.php`, `_rows.blade.php`, `_form.blade.php`, `_timeline.blade.php`
- Tests: `tests/Feature/ReceivableFollowupTest.php`,
  `tests/Unit/ReceivableFiguresTest.php`

Changed:
- `Modules/BackOffice/Routes/web.php`, `Modules/BackOffice/Routes/breadcrumbs.php`
- `app/Console/Kernel.php` (schedule)

## Testing

- Figures equal the base function for sampled contracts; legal and zero-balance
  contracts excluded; duplicates collapsed.
- Each filter (month, building, management type, search) and their combination.
- ARE scoping on list, form save and timeline.
- Save with and without promised date; empty comment and past date rejected;
  who/when/balance captured.
- Permissions: no `add_` → no button, save refused; no `view_` → no access.
- Timeline order and Kept / Broken / Upcoming.
- Browser check of list, modal and timeline without saving.

## Out of scope

- Fixing the v2 report's duplicate rows (reported separately).
- Editing/deleting follow-ups, reminders/notifications for promised dates,
  exports.
