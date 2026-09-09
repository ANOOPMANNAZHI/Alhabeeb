# Landlord Invoice v2 — AX Posting Design

**Date:** 2026-09-09
**Extends:** `docs/superpowers/specs/2026-09-07-landlord-invoice-v2-design.md` (which listed AX integration as out of scope; this addendum brings it in scope).

## Goal

Let an authorised BackOffice user push an active Landlord Invoice v2 into Microsoft Dynamics AX as an AP Invoice Journal, using the SOAP wrapper that v1 Landlord Invoice already uses, and record the returned AX journal number on the invoice.

## Decisions (defaults chosen because the user did not specify)

| Question | Decision |
|---|---|
| Journal | Same `PLM-INV` journal as v1 (`LANDLORD_INV_JOURNAL_NAME`). No new AX journal. |
| Expense (debit) account | Read from the existing `acc_params` row `Comprehensive_Payable_Transaction` (`acc_params_dr_acc`, currently `12306`, type `LEDGER`). Same source v1 uses. |
| Vendor (credit) account | Vendor's `vendor_code`, account type `VENDOR`. Same as v1. |
| VAT line | Posted as a plain `LEDGER` line to a configurable account. The account number lives in the `configuration` table under key `landlord_invoice_v2_vat_account` (editable in General Settings → Settings tab, same mechanism as `tax_percentage`). If `vat_total > 0` and the setting is blank, posting is refused with a clear message. No AX-side tax code is sent, so no AX change is required. |
| Other Deductions type | Posted with the entries reversed: `VENDOR` debit for grand total, `LEDGER` credit for subtotal (and VAT). |
| Approval | None. Any invoice with `status = 'active'` can be posted by a user holding the new permission `post_landlord_invoice_v2`. |
| Posted state | New status value `posted`. Posted invoices cannot be edited or voided. |
| HTTP method | `POST` (v1 uses `GET`; a link prefetch must never trigger a posting). |
| Dimensions | Identical to v1: Building = `buildings.building_code` (fallback `000`), Division = `buildings.ax_division` (fallback `02`), Employee `00000`, Location `00`, Projects `00`. |

## Data

New nullable columns on `landlord_invoice_v2`:

- `ax_batch_id` string(50) — AX journal number returned by the header call.
- `ax_invoice_no` string(30) — copy of `invoice_no` sent as voucher/invoice to AX.
- `posted_by` unsigned int.
- `posted_at` timestamp.

New `configuration` row: `configuration_settings = 'landlord_invoice_v2_vat_account'`, `configuration_name = 'settings'`, value blank.

New permission: `post_landlord_invoice_v2`, granted to `super_admin`, `finance_manager`, `accountant`, `backoffice_manager`.

## AX payload

Header: `Dynamics::LandlordInvoiceRegisterAxHeaderPushData(...)` → returns journal number or `'Error'`.

Lines (one SOAP call each via `Dynamics::LandlordInvoiceRegisterAxLineItemPushData`), for a Tax Invoice:

| # | accountType | vendAccount | AmountDebit | AmountCredit |
|---|---|---|---|---|
| 1 | VENDOR | vendor_code | 0 | grand_total |
| 2 | LEDGER | expense account (12306) | subtotal | 0 |
| 3 | LEDGER | VAT account (setting) | vat_total | 0 — only when vat_total > 0 |

Other Deductions swaps every debit and credit above.

Common fields per line: `JournalName = PLM-INV`, `JournalNum = <header result>`, `PaymentDate = invoice_date (Y-m-d)`, `currency = OMR`, `voucher = Invoice = documentNo = invoice_no`, `Description = Remarks = "<Tax Invoice|Other Deductions> <invoice_no> <vendor_name> <MM/YYYY>"`, `paymentMethod = checkBookid = ''`, five dimensions as above, `DataAreaId = company = HAB`.

## Behaviour

1. User clicks POST on an active row (button visible only with the permission). Confirm dialog.
2. Controller `post()` delegates to `LandlordInvoiceV2AxPoster::post($invoice, $userId)`.
3. Poster validates: AX enabled, status active, vendor code present, expense account configured, VAT account configured when needed. Any failure throws `AxPostingException` with a human message; nothing is sent to AX.
4. Poster opens the journal, pushes lines in order, aborts on the first `'Error'` (throws). Partial journals may remain in AX in that case, exactly as v1 behaves today; the invoice stays `active` so the user can retry.
5. On success poster updates the invoice: `status = 'posted'`, `ax_batch_id`, `ax_invoice_no`, `posted_by`, `posted_at`.
6. Controller flashes success or error and redirects to the list.

## UI

- List: status badge "Posted" (blue), POST button (active + permission), Edit/Void hidden once posted, status filter gains "Posted".
- Edit screen: read-only "AX Posting" section showing batch id, posted date, and posted by, shown only when posted. Edit screen is not reachable for posted invoices (403), so this appears only via the print/PDF.
- PDF: "AX BATCH:" field in the header table when posted.

## Out of scope

- Reversal or un-posting.
- Cost recognition / distribution breakup (v1's monthly cost posting).
- Queueing, retry, or persisting AX error text.
- Changing the AX Wrapper web service.
