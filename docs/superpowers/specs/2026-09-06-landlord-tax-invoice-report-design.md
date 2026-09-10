# Landlord Tax Invoice Report — Design

**Goal:** A new BackOffice report that generates a downloadable Tax Invoice PDF (matching the reference layout at `taxinvoice.pdf`) per landlord, for a selected date range — the document Al Habib sends to landlords billing them for Management Fees, Cleaning Charges, and Repair & Maintenance Charges deducted from their building's rental income.

## Filters

- `from_date`, `to_date` — the billing period.
- `landlord` — a single vendor, selected via the existing autocomplete (`landlordAutocompleteCode` route, already used in Sales' landlord contract forms).

## Scope

- Only landlord contracts on buildings in the Normal Management Report v2 building list (`BackOfficeReportController::$nmrV2BuildingIds`) are eligible. Comprehensive-management buildings (`management_id == 1`) are excluded for now, per explicit instruction.
- If the selected landlord has eligible contracts on more than one building, one Tax Invoice PDF is generated per building, bundled as a ZIP (matching the existing pattern from Maintenance Invoice Report v2 / Normal Management Report v2). A single building produces a single PDF, no ZIP.
- The `from_date`–`to_date` range is treated as one billing period. Amounts are computed by summing across every calendar month the range touches (whole-month granularity, consistent with how Normal Management Report v2 already computes its monthly figures — no day-level proration beyond what that report already does internally).

## Data sourcing (reusing Normal Management Report v2)

Normal Management Report v2's per-building/per-month computation (`normalManagementReportV2Stream()`) already computes, for every building in scope: unit-level income/collection amounts, aggregated expenses (from maintenance invoices + general ledger, grouped by `expense_head`), and (after the Cleaning Charges feature) the cleaning charge amount and the matching landlord contract for that month. This logic is extracted into a reusable method, `buildNormalManagementMonthData(Building $building, int $year, array $monthsToPopulate): array`, called by both the existing report and this new one — no duplicated SQL.

For each building, over the months touched by the period:

- **Management Fees**: read from the landlord contract matched for each month (same date-range matching already used for Cleaning Charges — prefers an active contract on overlap).
  - `management_method == 2` (Amount): flat `landlord_contract_management_fee`, summed across the months in the period (i.e. multiplied by however many months the period covers, since it's a fixed monthly fee).
  - `management_method == 1` (Percentage): that percentage of the building's total Rent Income or Rent Collection for the period, per `landlord_contract_percentage` (`1` = Rent Income, `2` = Rent Collection) — summed from the same `income_amount`/`collection_amount` figures the Normal Management Report already aggregates per unit per month.
- **Cleaning Charges**: `landlord_contract_cleaning_charge` from the matched contract, summed across the months in the period.
- **Repair and Maintenance Charges**: the building's Total Expenses for the period — same expense aggregation Normal Management Report v2 already computes (maintenance invoices + general ledger via `expense_head`), summed across the months in the period as one line.
- VAT is 5% of each line's Amount; Total = Amount + VAT.

## Invoice fields

- Customer Name / Customer ID / Address → the vendor's `vendor_name` / `vendor_code` / `vendor_contact_address`.
- Building Name → the building.
- Customer VATIN → left blank (`vendors` has no VATIN column). The company's own VATIN (`OM110001282X`) is a static constant, matching the reference.
- Invoice Date = `to_date`. Delivery Date = `to_date`. Payment Date = `to_date` + 1 month. All formatted `DD.MM.YYYY`.
- Invoice No → left blank for manual entry (matches the reference sample; no existing numbering scheme for this document type).
- Description text per line includes the billing period, e.g. `MANAGEMENT FEES FOR JUNE'26` when the period is a clean calendar month, or `MANAGEMENT FEES FOR 01.06.26-15.07.26` for a custom range.
- Amount in words: `"Omani Riyals " . numberToWords($whole) . " & Bzs " . $baisa . "/1000 only"`, built on the existing global `numberToWords()` helper (`config/function.php`), matching the pattern already used in `RentReceiptGenerationController::printPreview()`.

## PDF generation & delivery

- dompdf (Blade → PDF), reusing the existing bilingual Al Habib letterhead block from `Modules/BackOffice/Resources/views/Receipt/print_view.blade.php` (logo, Arabic/English company address, C.R. No, Finance code) for visual consistency with other company documents.
- SSE progress-bar download UX, matching the exact pattern already used by Maintenance Invoice Report v2 and Normal Management Report v2 (`EventSource` streaming `{pct, msg}`, final `{done:true, reportToken}` triggering a `cache()`-token-keyed download endpoint with `deleteFileAfterSend(true)`).
- New menu entry under BackOffice → Reports, registered via a migration mirroring `2026_06_16_000000_add_normal_management_report_v2.php`'s pattern (menu + permission + role grants).

## Out of scope (explicitly deferred)

- Comprehensive-management buildings.
- Any persisted invoice numbering/history (Invoice No stays blank for manual fill-in).
- Editing or resending invoices — this is a one-shot PDF generator, not a document management system.
