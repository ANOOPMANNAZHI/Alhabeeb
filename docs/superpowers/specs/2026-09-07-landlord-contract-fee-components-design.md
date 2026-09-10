# Landlord Contract Fee Components — Design

**Goal:** Extend landlord contracts with three additional fee components — Facility Management Fee, Renewal Fee, and New Leasing Fee — so they can be configured per contract and correctly reflected in both Normal Management Report v2 and the Landlord Tax Invoice Report, alongside the existing Management Fee and Cleaning Charges.

## Background

A bulk data-entry pass over 82 buildings' management fee rates surfaced that ~15 of them have compound fee structures the current schema can't represent — e.g. "5% P.M. + RO 65/- P.M. (Facility Management Fees)" or "300/- P.M. & 4% on New Leasing & RO 20/- for Renewal". These break down into three distinct components beyond the existing base Management Fee:

- **Facility Management Fee** — a flat monthly OMR amount, recurring every month alongside the base fee. Always a flat amount in the observed data, never a percentage.
- **Renewal Fee** — a flat OMR amount charged once per tenant-contract renewal event within the building. Always a flat amount in the observed data.
- **New Leasing Fee** — charged once per brand-new tenant lease (not a renewal) within the building. Appears both as a flat OMR amount (e.g. "RO 20/- for New Lease") and as a percentage of the new tenant's rent (e.g. "4% on New Leasing") depending on the building, so this one needs a Percentage/Amount toggle like the existing Management Fee field.

## Distinguishing a renewal from a new lease

`tenant_contracts.tenant_contract_old_no` is populated when a tenant contract row is itself a renewal of a prior contract, and is null/empty for a genuinely new lease. This is already reliable data (23,069 rows with a value, 8,202 without, in the current dataset) and requires no new tracking — the existing per-month "new leased units" calculation in `buildNormalManagementMonthData()` already counts tenant contracts starting each month; it just doesn't yet split that count by this column.

## Schema

Four new nullable columns on `landlord_contract` (mirroring the Cleaning Charges columns added previously):

- `landlord_contract_facility_management_fee` (double precision, nullable) — the flat monthly OMR amount.
- `landlord_contract_renewal_fee` (double precision, nullable) — the flat OMR amount per renewal event.
- `landlord_contract_new_leasing_fee_type` (integer, nullable — 1 = Percentage of new rent, 2 = Amount).
- `landlord_contract_new_leasing_fee` (double precision, nullable) — the % or OMR value, per the type above.

All four are shown for **all** management types (Comprehensive, Normal, Commission), same as Cleaning Charges — not subject to the Management Fee's Comprehensive-hides rule.

## Forms

Added to the same 8 contract create/edit forms already touched by the Cleaning Charges feature (Sales create/edit, BackOffice Direct create/edit, 3 renewal prefill forms, 1 bare-value renewal-edit form), in the same relative position (immediately after the Cleaning Charges field), following the exact same two established patterns from that feature:

- **Facility Management Fee** and **Renewal Fee**: plain amount inputs, matching Cleaning Charges' current amount-only shape (no radio, no method toggle).
- **New Leasing Fee**: a Percentage/Amount radio pair + value input, matching the *original* (pre-amount-only-conversion) Cleaning Charges pattern — since this field genuinely needs both modes, unlike Cleaning Charges which was simplified to amount-only.

The one bare-value renewal-edit form (`landlord_renew_contract_edit.blade.php`) gets bare value fields for all three (no radio for New Leasing Fee either), matching that form's existing simplified shape for every other fee field.

## Controllers

The same 6 controller write-sites already touched by Cleaning Charges (`LandlordContractController::landlordContractAction()`/`update()`, `LandlordContractDirectController::store()`/`update()`, `LandlordRenewalController::renewalContractAdd()`/`renewalNewContractUpdate()`), each gaining the corresponding array keys — 4 keys for the 7 forms with the full field set, 3 keys (no `_type`) for the bare-value renewal-edit form's write path (`getContractData()`), matching that method's existing asymmetry for Cleaning Charges.

## Shared calculation (`buildNormalManagementMonthData()`)

Extend the existing per-building/per-month computation (already shared between both reports since the Cleaning Charges work extracted it):

1. **Facility Management Fee**: read directly from the matched `landlord_contract` for that month (same date-range matching already used for Cleaning Charges and Management Fee) — a flat monthly figure, no counting needed.
2. **Renewal count and New-Lease count**: split the existing new-leased-units query by `tenant_contract_old_no IS NOT NULL` (renewal) vs `IS NULL` (new lease), per month, scoped to the building.
3. **Sum of new tenants' rent** for that month (needed only when `landlord_contract_new_leasing_fee_type == 1`, i.e. percentage-based) — sum `tenant_contract_rent` across the month's new-lease-only tenant contracts.
4. Compute per month: `renewalFee = renewalCount * landlord_contract_renewal_fee`; `newLeasingFee = (type==2) ? newLeaseCount * landlord_contract_new_leasing_fee : sumNewRent * (landlord_contract_new_leasing_fee / 100)`.
5. Store all of the above in the returned `$monthData[$m]` array under new keys, alongside the existing `cleaning_charge`/`landlord_contract` keys.

## Normal Management Report v2

Three new rows in the Consolidate sheet's Expenses section (same treatment as Cleaning Charges — included in Total Expenses, so they flow through to Amount Transfer to Land Lord):
- "Facility Management Fee"
- "Renewal Fee"
- "New Leasing Fee"

Each row shows `-` for a month with a zero/null value, same convention as every existing row in that sheet.

## Landlord Tax Invoice Report

Three new invoice line items (Description/Qty/Unit Price/Amount/VAT/Total, same shape as the existing Management Fee/Cleaning Charges/Repair & Maintenance lines), summed across the months the requested period touches — but **only included in the invoice at all if their summed amount is non-zero for the period** (e.g. no "Renewal Fee" line printed if no renewal happened in that landlord's building during the invoiced period). VAT is 5% of each line's amount, same as every other line.

## Global Constraints

- All new columns nullable, defaulting to no fee (zero contribution) when unset — matches every existing fee field's null-safety.
- Visible for all management types, not just Normal — matches Cleaning Charges, not Management Fee.
- Every currency figure formatted to 3 decimals, matching the rest of both reports.
- Reuse the exact renewal/new-lease distinction already latent in `tenant_contract_old_no` — no new tracking table or column needed on the tenant side.
- Do not alter the existing Management Fee or Cleaning Charges fields' behavior, values, or visibility rules.

## Out of scope

- The bulk data-entry pass populating these values for the 82 buildings (a separate follow-up task once this ships, and once the 8 unmatched building names, the "Al Mawaleh" ambiguity, and the Bait Al Said per-annum-vs-per-month question are resolved).
- Any UI for viewing renewal/new-lease event history — only the aggregate monthly counts feeding the fee calculation are needed.
