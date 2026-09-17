# Termination Dues (post-termination receivables) — Design

**Date:** 2026-09-17

## Problem

When a tenant contract is terminated with money still owed (rent, municipal tax, electricity & water, maintenance/checklist charges), the team today creates a **receipt** so the unit can be released to the next tenant — even though the old tenant has not paid. The receipt is then used as an IOU, followed up manually, and finance cannot tell a real payment from a promise. Audit of the local copy of live: 5,454 non-cancelled receipts created after the contract's termination completed; 158 rent receipts cover a period *after* the termination date.

## Goal

Terminate normally, release the unit, and hold the outstanding as a **tracked receivable against the old tenant** ("termination dues"). A receipt is created only when money is actually received. Every real settlement — rent receipts, general receipts, deposit-refund deductions — reduces the dues automatically. Two teams see their own portion: **Back Office** (rent, municipal tax, other charges, E&W) and **Maintenance** (checklist / maintenance items).

## Decisions

| Question | Decision |
|---|---|
| When is a dues record created? | At the final termination stage (`work_flow_processes_code = 505`, where the contract gets `tenant_renewal_termination_status = 8`), only if at least one line has amount > 0. Also by a one-time backfill command for already-terminated contracts that still show an unpaid balance. |
| Where do the owed amounts come from? | Rent: the inspector's "Rent" other-charge row on `termination_checklists` if present, else the computed outstanding (`totalContractRentCountCalculation(contract, termination_date)` − approved rent receipts). Municipal tax: "Muncipal Tax" other-charge row. Other: "Any Other Charges" / "Others" rows. E&W: `termination.termination_total_elec_water_amount` (fallback electricity + water). Maintenance: one line per checklist row with `work_id`; `termination_discount_maintenance_due` becomes a negative maintenance line. |
| Owner team per category | rent, municipal, other, ew → `backoffice`; maintenance → `maintenance`. Stored per line so it can be changed later without code. |
| How are settlements tracked? | **Computed, not stored.** Balances are recomputed from live data (receipts, deposit-refund deductions) each time. Cancelling, bouncing, soft-deleting or editing a receipt therefore needs no reversal code. Only *manual* decisions are stored: assigning an unclassifiable receipt to a line, or a waiver/write-off. |
| Which receipts count? | Rent receipts (`type 0`) with `receipts_generation_approval_status IN (3, 6)` (3 approved, 6 posted to AX), not soft-deleted, `status <> 2`, and `id > rent_receipts_upto_id`. `rent_receipts_upto_id` is the highest counted rent receipt already netted into the "Outstanding rent" line, fixed at creation: when the rent line is *computed* (`totalContractRentCountCalculation` minus receipts) it is the max id of all counted rent receipts at that moment; when the rent line comes from the inspector's checklist "Rent" row (fixed at stage 503, `charges_fixed_at`) it is the max id of counted rent receipts created on/before `charges_fixed_at`, so receipts taken after the inspection settle the line instead of vanishing. General receipts (`type 1`, same approval/status filters) created on/after `charges_fixed_at` (the inspection stage row's `created_at`, fallback the 505 row's), classified per credit line by account code. Pending/unapproved receipts are listed as "awaiting approval" but not deducted from the balance (the rent receipt guard does subtract them from what may still be collected, see below). |
| Which deposit-refund deductions count? | For deposit refunds on the contract that are not soft-deleted and not cancelled: every credit line that is neither a payout account (`configuration.deposit_refund_payout_accounts`) nor the deposit account `22311` — the same rule `DepositRefundReceiptBuilder` already applies — classified by account code. |
| Account code → category | Configurable JSON in `configuration` under `termination_dues_account_map`. Default: rent `12211, 31001`; municipal `41102, 41103`; ew `41105, 22305`; maintenance `41110, 22310, 41107, 22307, 12302`. Anything else → **unallocated**, shown on the dues page for the owner to assign by hand. |
| Allocation within a category | Oldest line first (FIFO) until each line's balance is zero; surplus in a category is reported as over-collection. |
| Status | `open` (nothing settled), `partial`, `settled` (balance ≤ 0.005), `written_off` (balance covered wholly or partly by waivers). Stored on the header and refreshed after every hook and on page view, so lists can filter without recomputing every row. |
| Receipt guard | Creating/updating a **rent** receipt on a contract with `tenant_renewal_termination_status = 8` is refused unless the contract has dues with an open rent balance, the amount is ≤ that balance, and `eff_to ≤ termination_date`. The collectable balance is the open rent balance **less pending rent receipts** (type 0, approval not in 3/6, not cancelled, `id > rent_receipts_upto_id`, excluding the receipt being edited); the message shows both figures. When editing a counted receipt its amount is added back only if `id > rent_receipts_upto_id` (baseline receipts are already netted into the owed figure). A terminated contract **without** dues refuses new rent receipts, but an existing receipt may still be edited provided the amount does not increase and `eff_to` is not after the termination date of the latest 505 row. General receipts are not blocked. |
| Existing fake receipts | Not reversed by code (many are posted to AX). The backfill creates dues only where the computed balance is still > 0. The audit CSVs in `storage/reports_tmp/` are for finance to review by hand. |
| Deposit refund form pre-fill | Out of scope for this iteration (the accounting grid is hand-built JS). Instead the refund form, the rent receipt form and the general receipt form show a **read-only dues summary box** for the selected contract so the user sees what to deduct/collect. |
| server_upload_files mirror | Not touched. Nothing is copied there until the user explicitly asks. |

## Data model

```
termination_dues                  one per terminated contract
  id, tenant_contract_id (unique), termination_id, termination_date, terminated_at,
  charges_fixed_at, rent_receipts_upto_id, status,
  total_owed, total_settled, balance, backoffice_balance, maintenance_balance,
  next_promise_date, last_followup_at, created_by, timestamps

termination_dues_lines            what is owed, by category
  id, termination_dues_id, category, owner_team, description,
  source_type, source_id, amount, line_order, timestamps

termination_dues_allocations      manual decisions only
  id, termination_dues_line_id, source_type ('rent_receipt'|'general_receipt_line'|'deposit_deduction'|'waiver'),
  source_id, amount, remark, created_by, timestamps

termination_dues_followups
  id, termination_dues_id, owner_team, followup_date, method, note,
  promise_date, created_by, timestamps
```

## Screens

- **Termination Dues** (menu under PLM Module → Operations). Tabs *Back Office* / *Maintenance* / *All*, shown according to permissions `view_termination_dues_backoffice` / `view_termination_dues_maintenance`. Columns: contract, tenant, mobile, building / unit, terminated on, days, owed, settled, balance (for the tab's team), status, last follow-up, promise date. Filters: status, search.
- **Dues detail**: settlement matrix (line × owed / deposit deduction / receipts / waived / balance, subtotals per team), source list with links to the receipt / deposit refund, unallocated items with an "assign to line" form, follow-up log + add form, waiver form, "Collect" links to the rent and general receipt screens.
- **Dues summary box** on rent receipt create, general receipt create and deposit refund create: fetched by AJAX when the agreement changes; shows per-category balance and a link to the dues page.

## Non-goals

- No AX posting changes.
- No automatic cancellation or reversal of historical receipts.
- No change to how the termination workflow itself progresses.

## Known limitations

- The computed rent fallback (used only when the inspector left no checklist "Rent" row) relies on the legacy `totalContractRentCountCalculation()` (`config/function.php:578`), which counts months with `date_diff()->format('%m')` only and ignores whole years, so a contract older than twelve months understates the rent due. Kept for parity with the termination screen; decision pending.

## Deployment notes

1. Run migrations: `Modules/BackOffice/Database/Migrations/2026_09_17_000001_create_termination_dues_tables.php`, then `database/migrations/2026_09_17_000002_add_termination_dues_menu.php`.
2. Review `configuration.termination_dues_account_map` against the live chart of accounts; edit the JSON if finance uses other codes.
3. Prune the two permissions per role (the migration grants both to every role): Back Office roles keep `view_termination_dues_backoffice`, Maintenance roles keep `view_termination_dues_maintenance`.
4. `php artisan termination-dues:backfill --since=2024-01-01` (dry run), review, then `--apply`.
5. Confirm the scheduler runs (`termination-dues:refresh --open-only` at 01:30).
6. Hand `storage/reports_tmp/rent_receipts_past_termination_date.csv` to finance for the historical receipts that were used as IOUs.
7. Receipts count as settlements when `receipts_generation_approval_status` is 3 (approved) or 6 (posted); the legacy outstanding calculation in `TenantTerminationController` still uses `= 3` only — consider aligning it.
8. The backfill dry run on the local copy (`--since=2025-01-01`, re-run 2026-09-17 after the checklist-rent cut-off and discount-netting fixes) reported 184 to create (182 open, 2 partial), 9 already settled, 870 with nothing owed, 0 failed.
