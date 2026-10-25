# PDC Bank Sheet — Rent + VAT cheques

Date: 2026-09-23
Status: approved in chat, spec awaiting review

## Problem

On Comprehensive buildings a tenant (mostly Company tenants) pays rent **plus
5% VAT** in one cheque. PLMS holds the rent as a Rent PDC (`pdc_type 1`) and,
when the team enters it, the VAT as a separate **VAT PDC** (`pdc_type 3`) on the
same cheque number. Example, cheque 81742436 / TAG2502824:

| PDC | type | amount | state |
|---|---|---|---|
| 514859 | 1 Rent | 1,250.000 | posted → CPY2619718 |
| 514871 | 3 VAT  | 62.500    | **never posted** |

The bank clearing sheet shows the real cheque amount (1,312.500), so the
bank-sheet matcher reports **Amount mismatch** and the cheque is posted by hand.
The VAT is then booked as a hand-made General Receipt (CPY2619717).

Facts from the local DB (2026 cheques):
- 1,548 VAT PDCs exist; **none has ever been posted** — both the bank-sheet
  matcher and the manual PDC Posting list only load `pdc_type IN (1,2)`.
- VAT PDC / rent PDC is exactly 5.0% in 1,203 cases, 4.8–5.3% in most others.
- VAT PDCs are entered for only ~12% of comprehensive rent PDCs, so the sheet
  often has 157/158 against a 150 PDC with **no** VAT PDC to match.
- `RoutinesController::postSinglePdc` already has a `pdc_type 3` branch
  (→ General Receipt) that is never reached; its remark does not match the
  team's format and it writes no account line.

## Goal

From the bank sheet review page, post a Rent + VAT cheque in one step: the
normal Rent Receipt plus a VAT **General Receipt** in the team's existing
format, both visible on the posting result / day / history pages.

## Reference receipt (format to copy): CPY2619717

| Field | Value | Rule |
|---|---|---|
| `receipts_generation_type` | 1 (General) | same |
| `receipts_generation_payment_method` | 1 (cheque) | same |
| receipt no | CPY sequence (`receipt_cheque`) | same sequence as rent |
| receipt date / `bank_id` | deposit date / deposit bank | from the review page |
| cheque no | `BD__81742436` | `<PDC bank code>__<cheque no>` (same as the rent receipt; the sample's `BD` vs `BMI` was a hand-entry inconsistency) |
| remark = description | `VAT for 16.08.26 to 15.09.26 UNIT NO. S 02 , Teeb Complex` | `VAT for <eff_from d.m.y> to <eff_to d.m.y> UNIT NO. <unit_no> , <building_name>` — period = the rent receipt's effective period (sample's `S 02` was a typo; contract unit is `S 03`) |
| `finance_dim` | 2 | building `ax_division == '01'` → `AX_DIVISION_HO`, else `AX_DIVISION_PLMS` (rule used by the receipt forms) |
| status / approval | posted to AX later | same rule as the rent receipt in `postSinglePdc` (`request_approval_receipt` permission → status 1 / approval 3, else 0 / 1) |
| dim line | credit **12211 RENT RECEIVABLE** (`ac_codes_id` of 12211), `dim_type` LEDGER, narration `VAT for <from> to <to>` | single credit line, as on the sample |

## Design

### 1. Matching — `BankSheetPdcMatcher`

The candidate PDC query in `PdcBankSheetController::candidatePdcs` loads
`pdc_type IN (1,2,3)` and adds `management_id` (building) and `pdc_type` to
each candidate. The matcher keeps its current rules for rent/deposit and adds:

- **Case A — Rent + VAT PDC, exact.** A group made of one agreement's rent PDC(s)
  in the cheque month plus that agreement's VAT PDC(s) on the same cheque sums
  to the bank amount (existing ±0.0005 epsilon) → status `matched` (ticked by
  default), the group's PDCs include the VAT PDC. Shown in the Matched table
  with a "Rent + VAT" label.
- **Case B — VAT by difference.** No exact group, but for one agreement:
  building is Comprehensive (`management_id = 1`), bank amount > sum of its
  rent PDC(s) in the month, and `| (bank − rent) − 5% × rent | ≤ 1.000` →
  new status `vat_check` (**unticked** by default). The result carries
  `rent_pdcs`, `vat_amount = bank − rent`, `expected_vat = 5% × rent`, and the
  agreement's VAT PDC(s) on that cheque if any (to be closed on post).
- VAT PDCs are never matched on their own (a sheet row matching only a VAT PDC
  is not a candidate).
- Anything else stays `amount_mismatch` / `not_found` / `date_mismatch` as now.

Review page: Case A rows appear in **Matched** with a "Rent + VAT" badge.
Case B rows get a new section **"Rent + VAT (check)"** above Matched with
columns: tick, cheque, bank, tenant/agreement, building/unit, rent, expected
VAT, actual extra, difference. Ticks use the same session selection mechanism
(`selected` set for this section, since it defaults to unticked).

### 2. Posting

`PdcBankSheetController::post` handles a ticked Case A or Case B row inside the
existing per-cheque `DB::transaction`:

1. Split the row's PDCs by type: rent/deposit PDCs go to `postSinglePdc`
   (unchanged behaviour); VAT PDCs (`pdc_type 3`) are **never** sent there —
   they are handled in step 2.
2. Create the VAT General Receipt through a new **`VatReceiptCreator`** service
   (`Modules/BackOffice/Services`), called with: agreement, rent receipt's
   effective period, VAT amount, cheque no, PDC bank code, deposit date, deposit
   bank, and the VAT PDC (nullable). It:
   - **duplicate guard:** if a non-deleted, non-cancelled General Receipt already
     exists for the agreement with cheque no ending `__<cheque no>`, creates
     nothing and returns that receipt no (row reason: "VAT receipt already
     exists: CPYxxxx");
   - otherwise inserts `receipts_generation` + one `receipts_generation_dim`
     per the table above, increments the `receipt_cheque` sequence, and, when a
     VAT PDC is given, marks it posted (`pdc_is_posted 1`, deposit/posted date,
     posted by, `pdc_receipt_no`).
   Case A: VAT amount = the VAT PDC amount. Case B: VAT amount = actual extra.
3. Any exception rolls back the whole cheque (rent + VAT); the row is recorded
   as `failed` with the message, as today.
4. The rent period for the remark comes from the rent receipt just created
   (`receipts_generation_eff_from/to`). If the rent PDC could not be posted
   (receipts already run to contract end), the cheque fails with that reason
   rather than posting VAT alone.

The `pdc_type 3` branch in `postSinglePdc` is left untouched (not reached by
this flow; the manual screen still excludes type 3).

### 3. Audit trail and result pages

Schema change (migration + server SQL file, both idempotent):
`pdc_bank_sheet_upload_rows.vat_receipt_no varchar(30) NULL`.

- `audit()` stores the VAT receipt no (created or pre-existing) per row.
- `pdc_ids` keeps listing every PDC the row closed (rent and VAT).
- `receiptRows()` (result / day / history) returns rent receipts as today
  plus VAT receipts joined on `vat_receipt_no`, de-duplicated by receipt id
  (a Case A VAT receipt is reachable both ways); Type shows
  "General (VAT)"; totals then equal the bank amounts.
- Posting message counts VAT receipts: "… 12 cheque(s) posted (14 PDC rows,
  5 VAT receipts)".

## Out of scope

- VAT PDCs on the manual PDC Posting screen.
- Clean-up of the 1,548 historic unposted VAT PDCs.
- Non-Comprehensive buildings in Case B (Case A works for any building, since
  a VAT PDC was entered deliberately).

## Testing

- Unit (`tests/Unit/BankSheetPdcMatcherTest.php`): Case A exact group; Case B
  within ±1.000 (157, 158 on 150 rent), just outside (159 → mismatch);
  non-comprehensive → mismatch; VAT PDC alone never matched; a Case B with an
  existing VAT PDC carries it; existing cases unchanged.
- Unit (`VatReceiptCreatorTest`): remark / narration format (d.m.y dates),
  cheque no format, division rule, duplicate guard returns existing receipt.
- Integration script against the local DB inside a rolled-back transaction:
  post a sample Case A and Case B cheque; assert rent + VAT receipts, dim line
  12211, VAT PDC closed, audit `vat_receipt_no`, result page lists both.
- Browser check by the user on a small real sheet (my browser session has no
  PLMS login).

## Deployment

Files: matcher, `PdcBankSheetController`, new `VatReceiptCreator`, review /
result partial views, migration, `add_pdc_bank_sheet_vat_receipt.sql`. Run the
SQL on the server before copying the code. PHP 7.1-compatible syntax only.
