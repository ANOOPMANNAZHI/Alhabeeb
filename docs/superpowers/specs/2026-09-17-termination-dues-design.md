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
| Which receipts count? | Rent receipts (`type 0`) with `receipts_generation_approval_status = 3`, not soft-deleted, `status <> 2`, and `id > rent_receipts_upto_id` (the highest rent receipt already netted into the rent outstanding when the dues were created). General receipts (`type 1`, same approval/status filters) created on/after `charges_fixed_at` (the inspection stage row's `created_at`, fallback the 505 row's), classified per credit line by account code. Pending/unapproved receipts are listed as "awaiting approval" but not deducted. |
| Which deposit-refund deductions count? | For deposit refunds on the contract that are not soft-deleted and not cancelled: every credit line that is neither a payout account (`configuration.deposit_refund_payout_accounts`) nor the deposit account `22311` — the same rule `DepositRefundReceiptBuilder` already applies — classified by account code. |
| Account code → category | Configurable JSON in `configuration` under `termination_dues_account_map`. Default: rent `12211, 31001`; municipal `41102, 41103`; ew `41105, 22305`; maintenance `41110, 22310, 41107, 22307, 12302`. Anything else → **unallocated**, shown on the dues page for the owner to assign by hand. |
| Allocation within a category | Oldest line first (FIFO) until each line's balance is zero; surplus in a category is reported as over-collection. |
| Status | `open` (nothing settled), `partial`, `settled` (balance ≤ 0.005), `written_off` (balance covered wholly or partly by waivers). Stored on the header and refreshed after every hook and on page view, so lists can filter without recomputing every row. |
| Receipt guard | Creating/updating a **rent** receipt on a contract with `tenant_renewal_termination_status = 8` is refused unless the contract has dues with an open rent balance, the amount is ≤ that balance, and `eff_to ≤ termination_date`. General receipts are not blocked. |
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
  id, termination_dues_line_id, source_type ('receipt'|'deposit_refund'|'waiver'),
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
