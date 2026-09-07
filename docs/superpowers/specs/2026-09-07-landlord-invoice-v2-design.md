# Landlord Invoice v2 — Design

## Purpose

Give BackOffice staff a single place to create, list, edit, void, and print landlord invoices (Tax Invoice and Other Deductions), with invoice numbers that stay strictly sequential for tax-audit purposes. This is a record-keeping CRUD feature — distinct from the existing "Landlord Tax Invoice Report" (`Modules/BackOffice/Http/Controllers/BackOfficeReportController.php`), which is a report-only, non-persisted PDF export. Landlord Invoice v2 persists the invoice as a record of truth and reuses that report's calculation engine rather than duplicating it.

It is also distinct from the existing v1 `LandlordInvoice` module (nested under Landlord Contract, with an approval/AX-dimension workflow) — v2 is a separate, independent, simpler module. v1 is untouched.

## Menu

New leaf menu item **"Landlord Invoice v2"** under PLM Module → Operations (menu id 96 in the live `menu` table).

Added via a standalone migration in the app-root `database/migrations/` directory, following the exact pattern of `2026_09_06_000002_add_landlord_tax_invoice_report_menu.php`:
- Resolve `PLM Module` (parent_menu=0) → `Operations` (parent_menu=<PLM Module id>, menutype=1) by name (not hardcoded ID, to avoid the fuzzy-match bug called out in that migration's comments).
- Insert a `menutype=2` leaf row with `menu_order = count(existing Operations children) + 1`, `route_name`/`url_key` = `landlord-invoice-v2.index`.
- Insert a `permissions` row named `view_landlord_invoice_v2` linked via `menu_id`.
- Grant that permission to every role in `role_has_permissions`.
- `cache()->forget('spatie.permission.cache')`.

## Data Model

All new tables live in `Modules/BackOffice/Database/Migrations/` (BackOffice owns invoicing, consistent with the existing v1 `LandlordInvoice` and the Tax Invoice Report).

### `vendors` (Modules/Masters) — additive migration

Add `vatin_no` (nullable string) to the `vendors` table. Add a corresponding field to the existing Vendor create/edit view/controller in `Modules/Masters` so it can be populated. This is the only change touching Masters.

### `landlord_invoice_v2` (header)

| Column | Notes |
|---|---|
| `id` | PK |
| `invoice_type` | enum/string: `tax_invoice`, `other_deductions` |
| `invoice_no` | string, unique, generated on save (not on page load) |
| `invoice_date` | date, defaults to today in the UI |
| `vendor_id` | FK vendors |
| `landlord_contract_id` | FK landlord_contract |
| `period_month`, `period_year` | the billing month this invoice covers |
| `vendor_name`, `building_name`, `vendor_address`, `vatin_no` | **snapshotted** at creation time — protects the audit record if the vendor/contract/building data changes later |
| `status` | `active` \| `voided` |
| `voided_at`, `voided_by` | nullable, set when voided |
| `created_by` | user id |
| `subtotal`, `vat_total`, `grand_total` | denormalized totals for list/PDF display |
| timestamps | |

### `landlord_invoice_v2_lines`

| Column | Notes |
|---|---|
| `id` | PK |
| `landlord_invoice_v2_id` | FK |
| `description` | e.g. "Management Fee", "Mun Tax Charges" |
| `amount` | decimal |
| `vat_amount` | decimal, 0 for `other_deductions` type |
| `line_order` | int, controls display order |

### Sequential numbering

Two independent series — one per invoice type — reusing the existing `Setting`/configuration `prefixData()` pattern already used by `LandlordInvoiceController::landlordInvoiceCode()` and `MaintenanceInvoiceController`:

- `code = prefix + year + str_pad(increment, 5, '0', STR_PAD_LEFT)`
- On successful `store()`, increment `Setting::where('configuration_settings', '<key>_prefix')->update(['configuration_increment_value' => inc + 1])`.
- Number is generated and reserved only at Store time (not shown/reserved while the create form is open), so abandoned drafts never create a gap in the sequence.
- Two configuration rows: one for `landlord_invoice_v2_tax_invoice_prefix`, one for `landlord_invoice_v2_other_deductions_prefix`.

## Calculation Sourcing

### Tax Invoice type

Reuses the existing calculation engine from the Landlord Tax Invoice Report (`BackOfficeReportController::landlordTaxInvoiceLineAmounts()`, `BackOfficeReportController.php:5105`), which itself calls `buildNormalManagementMonthData()`. This method should be extracted into a shared service/trait callable from both the existing report controller and the new Landlord Invoice v2 controller, so the two never drift out of sync.

Lines produced (for the selected contract + month):
- Management Fee
- Cleaning Charges
- Repair and Maintenance Charges (in-house) — sums `expense_name IN ('ROUTINE & MAINTENANCE EXPENSES', 'CIT - ROUTINE & MAINTENANCE EXPENSES')`

5% VAT applied per line, matching the existing report's behavior. All amounts are pre-filled from the calculation but remain editable in the form before save; the saved value (possibly adjusted) is what's recorded.

### Other Deductions type

New calculation method, also built on `buildNormalManagementMonthData()`'s `expenses` array (keyed by `expense_name`, confirmed live in the `expense_head` table):

| Invoice line | Source expense_name(s) |
|---|---|
| Mun Tax Charges | `MUNICIPAL TAX` (excludes `MUNICIPAL TAX PENALTY`) |
| Elect. and Water Charges | `ELECTRICITY & WATER` |
| Dewatering Charges | `DEWATERING` |
| AMC / Repair & Maintenance for A/C Units | `A C MAINTENANCE` + `CIT - AC MAINTENANCE` (combined — no separate A/C repair category exists in `expense_head`) |
| AMC for F.A.S | `FIRE ALARM SYSTEM MAINT.` + `CIT - FIRE ALARAM SYSTEM MAINT.` |
| Repair and Maintenance Charges (sub-contractor) | New query, modeled on `MaintenanceReportController::maintenanceInvoiceReportV2Query()` (`Modules/Maintenance/Http/Controllers/MaintenanceReportController.php:810`) — sums `maintenance_invoice_details`/`maintenance_invoices` amounts for the selected vendor + building + month where `service_report_id IS NULL` (the same in-house/sub-contractor flag that report already uses) |

No VAT is applied to any Other Deductions line. All amounts pre-fill from calculation but are editable before save.

## Controller / Routes

New controller in `Modules/BackOffice/Http/Controllers/`, e.g. `LandlordInvoiceV2Controller`, with resourceful routes under `landlord-invoice-v2`:
- `index` — list with filters (type, vendor, date range, status)
- `create` / `store`
- `edit` / `update`
- `destroy` — implemented as **void**, not a hard delete (see below)
- AJAX endpoints:
  - contracts-by-vendor (populate the Contract dropdown after Vendor is selected)
  - contract details (Vendor Name, Building Name, Address, VATIN) on contract selection
  - calculation preview (returns line items) for the selected contract + month + invoice type
- `print`/`show` — renders PDF from the **saved** invoice record (not a live recompute), adapting the existing Tax Invoice PDF blade (`Reports/landlord_tax_invoice_pdf.blade.php`) for both invoice types, including invoice header (No/Date/Vendor/Building/Address/VATIN), line items, VAT (tax invoice only), grand total, and amount-in-words (reusing the existing `numberToWords()` / `landlordTaxInvoiceAmountInWords()` helper).

## Create/Edit Flow

**Create**: Invoice Type (Tax Invoice / Other Deductions) → Invoice No shown as "auto-generated on save" placeholder → Invoice Date (defaults to today, editable) → Vendor dropdown → Contract dropdown (AJAX-filtered by selected vendor) → Month picker. Selecting a contract + month triggers an AJAX call that auto-fills Vendor Name, Building Name, Vendor Address, VATIN, and the relevant calculated line items (per the table above) — all editable before Save.

**Edit**: After first save, `invoice_type`, `invoice_no`, `vendor_id`, and `landlord_contract_id` become locked (read-only) — only `invoice_date` and line amounts remain editable. This keeps the saved record trustworthy: the number, type, and party are fixed once issued, but line amounts can still be corrected if a mistake is found before finalizing internal review.

## List Screen

Columns: Invoice No, Type, Date, Vendor, Building, Total, Status, Actions (View/Print, Edit — hidden if voided, Void — hidden if already voided). Filters: type, vendor, date range, status.

## Delete = Void

"Delete" does not remove the row. It sets `status = voided`, `voided_at`, `voided_by`. The `invoice_no` remains permanently reserved and visible in the list (with a "Voided" badge) but is excluded from any total/summary calculations. This preserves an unbroken sequential audit trail — a hard delete would leave a gap in invoice numbers that could look suspicious to an auditor.

## Out of Scope

- Any change to the existing v1 `LandlordInvoice` module or its approval workflow.
- Any change to the existing Landlord Tax Invoice Report's own PDF/report flow, beyond extracting its calculation method into a shared, reusable service.
- Populating `expense_head`-based categorization for new charge types — the mapping above uses only categories that already exist live in the `expense_head` table today.
- Any AX/ERP integration or dimension mapping (v1-only concern).
