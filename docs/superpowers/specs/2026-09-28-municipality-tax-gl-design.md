# Municipality Tax on Contract Registration — Design

Date: 2026-09-28
Status: approved in chat, awaiting spec review

## Goal

When back office registers a tenant contract with the municipality (the
"Municipality" modal on the contract list and contract view pages), record the
municipal tax, the processing charge and any penalty on the contract, and
create the matching General Ledger voucher(s) automatically. Finance then
approves / posts them to AX with the existing General Ledger screen, instead of
typing them by hand (today: manual AJV vouchers such as AJV2600536
"M.TAX FOR VARIOUS UNITS FOR VARIOUS BUILDINGS").

## Current state

- Modal: `Modules/BackOffice/Resources/views/TenantContract/tenant_contract_municipality_modal.blade.php`
  (list page) and `tenant_contract_municipality_modal_view.blade.php` (view page).
  Both post to `TenantContractEditController@tenantContractAddMunicipalityStore`.
  `tenantContractAddMunicipalityViewStore` has no route and is not touched.
- Setting `municipal_tax_percentage` (configuration table, default 3) already
  exists; used by the Vacating Unit Inspection via `MunicipalTaxCalculator`.
- `tenant_contracts.tenant_contract_value` is `varchar`.
- `tenant_contracts.tenant_contract_penalty` / `tenant_penalty_invoice_amt`
  already exist for the contract's own late-payment penalty — not reused.
- General Ledger: `general_ledgers` + `general_ledger_dim`, created by
  `GeneralLedgerController@store`, posted to AX by
  `GeneralLedgerController@action('post')` (GL journal PLM-GL). Voucher numbers
  come from the `general_ledger_prefix` setting (prefix + year + 5-digit counter).
- Accounts (`acc_codes`): 41102 MUNICIPAL TAX, 41103 MUNICIPAL TAX PENALTY,
  12674 BM Visa Card Account. Manual vouchers use division dim id 2 (PLMS) and
  the unit's building / unit on every line.

## Settings (General Settings → Settings tab, table `configuration`)

| Key | Default | Meaning |
|---|---|---|
| `municipal_tax_percentage` | 3 (exists) | Tax % of contract value |
| `municipality_processing_charge` | 1 | Fixed OMR amount added to the tax |
| `municipal_tax_account` | 41102 | Debit account for tax + processing charge |
| `municipal_tax_penalty_account` | 41103 | Debit account for the penalty |
| `municipality_payment_account` | 12674 | Credit account (how the municipality is paid) |

Added by a migration that inserts only missing keys (same pattern as
`2026_09_16_000001_add_municipal_tax_percentage_setting.php`).

## Contract columns (migration on `tenant_contracts`)

- `tenant_contract_municipality_tax_amt` numeric(12,3) null
- `tenant_contract_municipality_processing_amt` numeric(12,3) null — the charge
  in force when saved (the setting can change later)
- `tenant_contract_municipality_penalty_amt` numeric(12,3) not null default 0
- `tenant_contract_municipality_gl_id` integer null — the tax voucher
- `tenant_contract_municipality_penalty_gl_id` integer null — the penalty voucher

## Modal

New fields after "Contract Registered Date":

- **Municipality Tax Amount** — pre-filled with contract value × tax % (3 dp),
  editable. Under it: "Contract value 3,600.000 × 3% = 108.000".
  When the contract already has a saved amount, that amount is shown.
- **Processing Charge** — read-only, from the setting (or the saved amount).
- **Total (tax + processing)** — read-only, updates as the tax is edited.
- **Penalty Amount** — optional, default 0.
- When a voucher exists, its number and status (e.g. "AJV2600612 — Posted") is
  shown. When the tax voucher is posted, the tax field is read-only; same for
  the penalty field when the penalty voucher is posted.

## Save rules (server)

Validation: tax amount required, numeric, ≥ 0; penalty numeric, ≥ 0 (blank = 0).
Processing charge is always taken from the setting, never from the browser
(or kept from the contract when its voucher is already posted).

Everything below runs in one DB transaction with the contract row locked.

1. Save the existing municipality fields (unchanged behaviour) plus the three
   amounts.
2. Tax voucher (tax + processing > 0):
   - none yet → create
   - exists, not posted (`general_ledger_status` ≠ 3) → rewrite header amount/date
     and its two lines
   - exists, posted → leave it; if the submitted tax differs from the saved
     tax, reject the save with "Municipality tax voucher AJV… is already posted
     to AX; make a correcting entry in General Ledger."
3. Penalty voucher:
   - penalty > 0 and none yet → create
   - penalty > 0 and not posted → update
   - penalty = 0 and not posted voucher exists → soft-delete it, clear the link
   - posted → same rejection rule as the tax voucher
4. Total of 0 (tax 0 and processing 0) → no tax voucher; an unposted one is
   soft-deleted.

### Voucher shape

Header (`general_ledgers`), same fields `GeneralLedgerController@store` sets:
- `voucher_no`: next `general_ledger_prefix` code; the counter row is read with
  a row lock and incremented in the same transaction
- `general_ledger_type` 1 (General Ledger), `bank_id` null
- `doc_date`: Contract Registered Date
- `jv_refer_no`: contract no
- `general_ledger_desc`: "M.TAX <contract no> <building> <unit>" /
  "M.TAX PENALTY <contract no> <building> <unit>"
- `amount`: voucher total
- status: `ledger_approval` users → status 2 / approval 4 (approved);
  others → status 1 / approval 0 (draft), exactly as manual vouchers

Lines (`general_ledger_dim`), both with the contract's building and unit,
`dim1able` = DimDetail id 2 (PLMS), `dim2able` = the building:

| Voucher | Debit | Credit |
|---|---|---|
| Tax | tax account (41102) = tax + processing | payment account (12674) |
| Penalty | penalty account (41103) = penalty | payment account (12674) |

Account settings are account *values* (41102); they are resolved to `acc_codes.id`
when the voucher is built. An unknown account stops the save with a message
naming the setting.

Posting to AX is not touched: Finance opens the voucher in General Ledger,
approves if needed, and presses Post.

## Code layout

- `Modules/BackOffice/Services/MunicipalityRegistrationCharges.php` — pure
  (no DB): default tax from contract value and rate; totals; builds the voucher
  line arrays from amounts + account ids + building/unit. Unit tested.
- `Modules/BackOffice/Services/MunicipalityRegistrationLedger.php` — DB side:
  reads settings, resolves accounts, creates/updates/removes vouchers per the
  save rules, voucher numbering with lock.
- Controller `tenantContractAddMunicipalityStore` calls the service inside the
  transaction; the modal action builds the pre-filled amounts.

Runs on PHP < 7.4 in production: no arrow functions, `?->`, `match`, typed
properties.

## Out of scope

- Vacating Unit Inspection keeps its own municipal tax rule.
- No vouchers for contracts registered before go-live.
- No change to GL posting or AX.
- Existing behaviour where re-saving the modal without a file clears
  `tenant_muncipality_agreement` is not changed here.

## Testing

- Unit: default tax (varchar value, empty value → 0, rounding to 3 dp), totals,
  line building (debit = credit, accounts, dims), penalty-only / zero cases.
- In-process against the local DB inside a rolled-back transaction: create,
  update-unposted, penalty added/removed, posted → rejection.
- Browser: open the modal from the list and the view page, save, open the voucher
  in General Ledger.

## Deploy

1. Run the two migrations (settings, contract columns).
2. Upload code; clear caches.
3. Check the four new settings on General Settings.
