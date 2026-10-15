# Rent revisal before renewal — design

**Date:** 2026-10-15
**Status:** draft for review
**Request:** management email (CA notice 4 months before expiry, CA enters revised rent, renewal email uses it).
Reworked with the user so that **nothing in the existing renewal process changes**.

## Goal

Rent increases are decided and sent to the tenant **before** the renewal agreement goes out, so the
renewal agreement already carries the right rent and no second "rent increase" email is needed.

## What the user decided

| Point | Decision |
|---|---|
| Who acts | The building's ARE (ARE Building Assign). Managers and admins can act on any building. |
| Window | A contract can be revised only when it ends in **90–120 days** from today. |
| Notice to ARE | One email when a contract enters the window (120 days), only if no revisal exists yet. |
| What is entered | New monthly rent + optional note to the tenant. Nothing else. |
| Tenant email | Sent **only** when a revisal is entered. Reply-To = the ARE. No SMS. |
| Approval | None. The ARE enters and sends. |
| Skipped contracts | Under termination, and contracts whose renewal has already started. Legal-case and In House contracts are **included**. |
| Renewal screens | Rent field defaults to: renewal form already sent → **revisal rent** → previous rent. Both the renewal email popup and the renewal contract screen. |
| After sending | The revisal is locked (read-only). One revisal per contract. |
| Not wanted | No "tenant already informed, don't email" option. No change to the 3-month Due for Renewal list. |

## Out of scope

- Any change to Due for Renewal's own list, its 3-month setting (`renewal_notification`), its buttons, the
  renewal stages (300–304) or the renewal form email.
- Approval workflow, SMS, other renewal terms (period, payment type), editing a sent revisal.

## Data

New table `tenant_rent_revisals` (migration `2026_10_15_000000_create_tenant_rent_revisals_table`):

| Column | Type | Notes |
|---|---|---|
| id | serial | |
| tenant_contract_id | int, unique, FK tenant_contracts | one revisal per contract |
| current_rent | numeric(15,3) | contract rent at the time of the revisal |
| revised_rent | numeric(15,3) | the decided monthly rent |
| note | text, null | optional note shown in the tenant email |
| email_status | varchar(20), null | MessageLog status of the tenant email (sent / failed / skipped_no_email / skipped_off) |
| emailed_at | timestamp, null | |
| created_by | int | user who sent it |
| timestamps | | |

New table `tenant_rent_revisal_notices` (same migration) — records the 120-day ARE emails so none is sent twice:
`id, tenant_contract_id (unique), are_user_id, email_status, sent_at, timestamps`.

Entity: `Modules\BackOffice\Entities\TenantRentRevisal` (+ `TenantContract::rentRevisal()` hasOne).

## Which contracts are in the window

`RentRevisal::windowQuery()` (service `Modules\BackOffice\Services\RentRevisal`), used by the tab, the dashboard
card and the daily job:

- `tenant_contract_status = 1` (active)
- `tenant_contract_valid_to_date` between today + 90 days and today + 120 days (inclusive)
- `tenant_renewal_termination_status` in (0, 1, 4) — normal, requested, renewal rejected.
  Excluded: 2 under renewal, 3 under approval, 5/6 renewed, 7 termination request, 8 terminated, 9 approved.
- ARE scope: an ARE sees contracts of buildings assigned to them (same rule as Due for Renewal's `areFilter`);
  managers / admins see all.

`RentRevisal::canRevise($contract)` = contract is in the window, has no revisal yet, and the user may act on its building.
Checked again on save (server side), not only in the screen.

## Screens

1. **Due for Renewal → new "Rent revisal" tab** (`tenantRenewal.revisal`, route `tenantRenewal/rentRevisal`).
   The existing Due for Renewal list is untouched; the tab sits beside it.
   Columns: Contract · Tenant · Building / Unit · End date · Days left · Current rent · Revisal · Action.
   Revisal column: "Not revised" or "OMR 380.000 · emailed 12/11/2026 by Rashid". Action: **Revisal** / **View**.
   Filters like the other lists (contract, tenant, building, revisal status).
2. **Revisal popup** (modal, loaded by AJAX): contract, tenant, end date, current rent; inputs New monthly rent
   (required, > 0, numeric with 3 decimals) and Note (optional, max 500 chars); live preview of the tenant email.
   **Send to tenant** → saves the revisal, sends the email, closes, refreshes the row. Read-only once sent.
   If the tenant has no email: revisal saved, popup says "No email on file — inform the tenant another way".
3. **ARE dashboard** (`resources/views/dashboard/are.blade.php`): card "Rent revisal due" = contracts in the
   ARE's window with no revisal, linking to the tab.
4. **Renewal screens** (read-only change): in `TenantRenewalController` the rent default for the renewal email
   popup (`renewalTemplatePdfModel`, ~line 2002) and the renewal contract screen (~line 1029) becomes
   renewal form already sent → `rentRevisal->revised_rent` → contract rent. Nothing else on those screens changes.

## Emails (system templates, editable in Mail → Templates, logged in Sent log)

Seeded by migration `2026_10_15_000001_seed_rent_revisal_templates` and added to `SystemTemplateDefaults`:

- `rent_revisal_due_are` (staff) — subject "Contract expiring soon – start rent revisal if needed – {building} {unit}";
  body: tenant, contract no, unit, end date, current rent, link to the Rent revisal tab. Active, SMS off.
- `rent_revision_tenant` (tenant) — subject "Rent revision on renewal – {building} {unit}"; body: contract end
  date, new monthly rent from renewal, current rent, ARE note, ARE name and contact. Active, SMS off.
  **Reply-To = ARE email**: `SystemMessenger::send` gains an optional `reply_to` option (email + name),
  applied to the email channel only; other callers unaffected.

## Daily job

Command `rent-revisal:notify-are`, scheduled `dailyAt('08:00')` in `app/Console/Kernel.php`:
for each contract in the window with no revisal and no row in `tenant_rent_revisal_notices`, email the building's
ARE(s) and record the notice. A contract that entered the window on a missed day is still caught (it is in the
window, has no notice). No ARE assigned → no email, no notice row (it is picked up if an ARE is assigned later
while the contract is still in the window).

## Permissions

New permission `rent_revisal` (migration with the templates or its own), given to roles `are`, `are_team_lead`,
`backoffice_manager`, `super_admin`. It controls the tab, the popup and the save route. ARE scope as above.

## Errors

- Save outside the window, second revisal, or a building the user may not act on → refused with a message, nothing saved.
- Email failure → revisal still saved, `email_status = failed`, popup tells the user; Sent log has the detail.
- Template switched off → revisal saved, `email_status = skipped_off`, popup says the email is switched off.

## Testing

Feature tests `tests/Feature/RentRevisalTest.php`:
- window query: 89 / 90 / 120 / 121 days; excluded statuses; ARE scope.
- save: valid revisal saves + sends with Reply-To; refused outside window / second time / other ARE's building.
- tenant without email → saved, status skipped_no_email.
- daily job: emails once, not twice; skips contracts with a revisal; skips buildings with no ARE.
- renewal popup and renewal contract screen: rent default order (form → revisal → contract rent).

## Deploy (live)

Code first, then migrations `2026_10_15_000000` and `_000001`; the scheduler must run (08:00 job). No change
to existing data. Live PHP is < 7.4: no arrow functions, nullsafe or match in this code.
