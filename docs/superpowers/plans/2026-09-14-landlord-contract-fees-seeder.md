# Landlord Contract Fees & Cleaning Charges Seeder — Implementation Plan

> **For agentic workers:** Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Load the management-fee rules and cleaning charges from `NM Buildings Fees & Cleaning Expenses.xls` (82 buildings) into the **active landlord contracts** so the Landlord Invoice v2 Tax Invoice calculates the right management fee / cleaning charge, and deliver it as a **Laravel seeder** that can be run directly on the server database.

**Architecture:** No schema change — every value the sheet contains already has a column on `landlord_contract` and is already consumed by `BackOfficeReportController::buildNormalManagementMonthData()` / `landlordTaxInvoiceLineAmounts()`. The work is (a) a hand-verified mapping of the 82 sheet rows to `buildings.building_code` (stable across local and server, since local is a copy of live), (b) a pure parser that turns each sheet rule into the 8 contract fields, (c) an idempotent seeder that updates every active contract of each mapped building, with dry-run by default, a JSON backup of the previous values, and a printed report of matched / skipped rows.

**Tech Stack:** Laravel 5.7, nwidart Modules (`Modules\BackOffice\Database\Seeders`), PostgreSQL, PHP < 7.4 on the server (no arrow functions, nullsafe, match, typed properties).

**Source sheet:** `C:\Users\anoop\Downloads\NM Buildings Fees & Cleaning Expenses.xls` (Sheet1, rows 4–85: S.No | Building | Management Fees | — | Cleaning Charges).

## Findings that shape this plan (from the local copy of the live DB)

- 49 of 82 rows match a building by name automatically; 23 more are mapped below by search; 10 have no confident match and are **skipped and reported** (user decision).
- Management-fee method/value is already right on most matched contracts (e.g. Al Falaj `m=1 fee=4`), but **`landlord_contract_cleaning_charge` is 25 on every contract** (a default) — the cleaning column is the main new data.
- `landlord_contract_percentage` (basis; the UI's "Percentage Type": 1 = Rent Income, 2 = Rent Collection) is junk/blank on 56 of 81 active %-contracts (values 0, 3.5, 5, 6, 7, NULL). `landlordTaxInvoiceLineAmounts()` **skips the management fee** when the basis is not 1 or 2, so those buildings invoice a zero management fee today. User decision: set **2 (Collection)** where the current value is not 1 or 2; keep existing 1/2 values.
- User decision: the sheet is the source of truth — the seeder writes **all** fee fields (method, fee, basis, cleaning, facility, renewal, new-leasing type/value), logging the previous values.

## Field model (target columns on `landlord_contract`)

| Sheet phrase | `management_method` | `landlord_contract_management_fee` | `landlord_contract_percentage` | other |
|---|---|---|---|---|
| `5% P. M.` | 1 (Percentage) | 5 | keep if 1/2 else 2 | |
| `400/- P. M.` | 2 (Amount) | 400 | untouched | |
| `100/- P.A.` | 2 | 8.333 (100 ÷ 12, 3 dp — matches what the contract already holds) | untouched | |
| `+ 65/- P.M. (Facility Management Fees)` | | | | `landlord_contract_facility_management_fee` = 65 |
| `RO 15/- for Renewal` / `Each Renewal RO 25` | | | | `landlord_contract_renewal_fee` = 15 / 25 |
| `4% on New Leasing` | | | | `landlord_contract_new_leasing_fee_type` = 1, `landlord_contract_new_leasing_fee` = 4 |
| `RO 20/- for New Lease` | | | | `landlord_contract_new_leasing_fee_type` = 2, `landlord_contract_new_leasing_fee` = 20 |
| Cleaning column `25` / `0` | | | | `landlord_contract_cleaning_charge` = 25 / 0 |
| Cleaning column blank (SAIF WAHAIBI) | | | | cleaning **left unchanged** (null in the map = don't touch) |

Fields not mentioned by a row are written as `NULL` for facility / renewal / new-leasing (the sheet is the source of truth), except the basis which is only normalised, never blanked.

## Building map (sheet name → `building_code`, DB name)

Confident (auto-matched or unambiguous search) — 72 rows:

| # | Sheet | Code | DB building | Rule → fields |
|---|---|---|---|---|
| 1 | 1323 AL FALAJ BUILDING | 457 | 1323-Al Falaj | m1 4 · clean 25 |
| 2 | AASHIRWAD BLDG | (217) | Aashirwad Bldg | m1 5 · clean 0 |
| 3 | ABDUL MALIK BALDG - RUWI | (319) | Abdul Malik-Ruwi Bldg | m1 5 · clean 40 |
| 4 | ABDUL MALIK GARAGE | (320) | Abdul Malik-Garage Plot | m1 5 · clean 0 |
| 6 | ADIL TAQI 3 FLATS | (466) | Adil Taqi-3 Flats | m1 5 · clean 0 |
| 7 | Al AROOS | (67) | Al Aroos | m1 5 · clean 40 |
| 8 | AL AZAIN BLDG | (470) | Al Azain Bldg | m2 400 · clean 0 |
| 9 | AL HAIL 1 BUILDING | 461 | Al Hail-01 Bldg | m1 6 · renewal 15 · clean 0 |
| 10 | AL HAIL 2 BUILDING | (437) | Al Hail-02 Bldg | m2 125 · new-lease amt 20 · clean 0 |
| 11 | AL HAIL 3 BUILDING | (439) | Al Hail-03 Bldg | m2 50 · new-lease amt 20 · clean 0 |
| 12 | Al KHUWAIR HOUSE | (116) | Al Khuwair House | m1 5 · clean 0 |
| 13 | AL MANARA BLDG | (83) | Al Manara Bldg | m2 120 · clean 25 |
| 15 | AL MIR BLDG | (303) | Al Mir Bldg | m1 6 · clean 0 |
| 16 | AL MUNTHER BLDG | (293) | Al Munther Bldg | m1 5 · clean 0 |
| 18 | AL QANDEEL BLDG | (465) | Al Qandeel-2 Bldg | m1 3.5 · clean 200 |
| 19 | AL RAWAHI BLDG | (198) | Al Rawahi Bldg | m2 270 · clean 0 |
| 20 | AL SAHWA SQUARE | (427) | Al Sahwa Square | m1 3.5 · clean 0 |
| 23 | AL ZEHRA BUILDING | (435) | Al Zahra Building | m2 100 · new-lease % 4 · clean 25 |
| 24 | ALI - 2 BUILDING | (440) | Ali-02 Bldg | m1 5 · clean 20 |
| 25 | ALI - 3 BUILDING | (442) | Ali-03 Bldg | m1 5 · clean 20 |
| 26 | ALI BLDG | (18) | Ali Bldg | m1 5 · clean 40 |
| 27 | ANOOD VILLAS | (438) | Anood Villas | m2 130 · clean 0 |
| 28 | AVENUE 6 BLDG | (467) | Avenue-6 Bldg | m1 3 · new-lease % 3 · clean 250 |
| 30 | BAIT ABDULLA | (323) | Bait Abdullah | m1 5 · clean 0 |
| 31 | BAIT AL RAFAY | (201) | Bait Rafey | m1 5 · clean 0 |
| 32 | BAIT AL SAID | (81) | Bait Al Said | m2 8.333 (100 P.A.) · clean 0 |
| 33 | BAIT FATMA | (243) | Bait Fatma | m1 4 · clean 30 |
| 34 | BAIT NASEEB | (316) | Bait Naseeb | m1 5 · clean 40 |
| 35 | BAIT RAWAN | (288) | Bait Rawan | m1 5 · clean 250 |
| 36 | BANK DHOFAR ANSAB | (464) | Bank Dhofar-Ansab | m2 300 · new-lease % 4 · renewal 20 · clean 250 |
| 37 | BANK DHOFAR DARSAIT | (461) | Bank Dhofar-Darsait | m2 300 · new-lease % 4 · renewal 20 · clean 200 |
| 38 | BANK DHOFAR WALJA | (463) | Bank Dhofar-Walja | m2 100 · new-lease % 4 · renewal 20 · clean 50 |
| 39 | BANK DHOFAR WEAR HOUSE | (462) | Bank Dhofar-Warehouse | m2 50 · new-lease % 4 · renewal 20 · clean 0 |
| 40 | BARAQ AL REEF | (423) | Baraq Al Reef | m2 99 · renewal 25 · new-lease % 4 · clean 0 |
| 41 | BASEEMA VILLA AZAIBA | (251) | Baseema Villa-Azaiba | m2 50 · clean 0 |
| 42 | BASEEMA VILLA QURUM | (406) | Baseema Qurum Villa | m2 50 · clean 15 |
| 43 | BUSTAN BLDG | (468) | Bustan-171 Bldg | m1 4 · clean 250 |
| 44 | BUTTERFLY BLDG | (66) | Butterfly Bldg | m2 125 · clean 40 |
| 45 | DAISAIT VILLA | (54) | Darsait Villas | m1 5 · clean 0 |
| 46 | DAR AL ZAIN | (426) | Dar Al Zain Complex | m2 170 · clean 0 |
| 47 | DR. NASSER BLDG | (88) | Dr.Nasser Bldg | m2 60 · clean 0 |
| 48 | EWAN M1 BLDG | (294) | Ewan-M1 Bldg | m1 5 · clean 175 |
| 49 | FALAJ NEW BLDG | (295) | Falaj New Bldg | m2 50 · clean 25 |
| 50 | G D S BLDG | (299) | GDS Bldg | m1 5 · clean 0 |
| 51 | GETCO BLDG | (306) | GETCO Towers | m1 5 · clean 50 |
| 52 | GHALA 109 BLDG | (469) | Ghala-109 Bldg | m1 4.5 · clean 250 |
| 53 | HABIB HOUSE | (89) | Habib House | m1 5 · clean 40 |
| 54 | HAITAM JAFFER BLDG | (220) | Haitham Jaffer Bldg | m1 5 · clean 30 |
| 55 | HASSAN SAID BLDG | (279) | Hassan Said Bldgs | m1 5 · clean 0 |
| 56 | IMAN BLDG | (322) | Iman House | m1 5 · clean 20 |
| 57 | INTISAR BLDG | (253) | Intisar Bldg | m1 5 · clean 15 |
| 58 | KARAMA BLDG | (53) | Karama Bldg | m1 5 · clean 65 |
| 59 | KHA 1 | 126 | KHA-01 | m2 90 · clean 20 |
| 60 | KHA 5 | 509 | KHA-05 Bldg | m2 75 · clean 0 |
| 61 | KISHORE MUMTAZ FLAT | (290) | Kishore-Mumtaz Flat | m1 5 · clean 0 |
| 62 | MAHMOOD | (413) | Mahmood Bldg | m2 70 · clean 20 |
| 63 | MAZOON | (114) | Mazoon Bldg | m1 5 · facility 100 · clean 75 |
| 64 | MEHDI BLDG | (448) | Mehdi Bldg | m2 350 · new-lease % 4 · clean 250 |
| 69 | OMAN S 1 | (424) | Oman S1 | m2 300 · clean 125 |
| 70 | OMAN S 2 | (425) | Oman S2 | m2 200 · clean 125 |
| 73 | R C A BLDG | 308 | RCA-120 Bldg | m1 3.5 · clean 250 |
| 74 | RADHIYA BLDG | (105) | Radhiya Bldg | m2 85 · clean 20 |
| 75 | SAID SULTAN W K BLDG | (33) | Said Sultan Bldg | m1 5 · clean 0 |
| 76 | SAIF WAHAIBI QURUM VILLA | (405) | Saif Wahaibi-Qurum Villas | m2 125 · clean unchanged (blank) |
| 77 | SAMA BLDG | (187) | Sama Bldg | m1 3.5 · clean 250 |
| 79 | SMC MABELLA | (318) | SMC Mabella Bldg | m1 5 · clean 75 |
| 80 | STELLA BLDG | (208) | Stella Bldg | m1 4.5 · clean 250 |
| 81 | TAKAFUL - 2 | (133) | Takaful-2 Bldg | m1 5 · clean 25 |
| 82 | TRUST BLDG | (113) | Trust Bldg | m2 200 · clean 50 |

`(nnn)` = local `buildings.id`; Task 1 resolves each to its `building_code` (codes are unique and non-null on all 278 buildings) and the seeder keys on the code, verifying the DB name still matches.

Mapped by search but **flag in the seeder report for a second look** (still applied): #5 ABDUL MALIK VILLA → (321) Abdul Malik-Rex Villas · #45 DAISAIT VILLA → (54) Darsait Villas · #56 IMAN BLDG → (322) Iman House · #78 SAMAR BLDG → (209) Samer Bldg · #31 BAIT AL RAFAY → (201) Bait Rafey · #18 AL QANDEEL BLDG → (465) Al Qandeel-2 Bldg.

**Skipped — no confident match (10 rows), reported by the seeder; add to the map once the user names the building:**

| # | Sheet | Rule | Candidates seen |
|---|---|---|---|
| 14 | AL MAWALEH BUILDING MOD | m2 1500 · clean 1200 | (265) Al Mawallah Bldg, (132) Mawallah New Bldg |
| 17 | AL NOOR BLDG | m1 5 · clean 25 | (108) Al Noor Bldg **or** (310) Al Noor Bldg-MERA |
| 21 | Al SULAEF | m1 5 · facility 65 · clean 50 | none |
| 22 | AL TEHMAR BUILDING | m1 5 · renewal 25 · clean 250 | none |
| 29 | AWQAF BLDGS | m2 900 · clean 0 | none |
| 65 | MOJ BLDGS - AL KHUWAIR | m1 4 · clean 0 | none |
| 66 | MOJ BLDGS - LEFT BUILDINGS | m1 4 · clean 0 | none |
| 67 | MOJ BLDGS - RUWI | m1 4.5 · clean 0 | none |
| 68 | NARGIS BLDG | m2 50 · clean 30 | none |
| 71 | QURUM HEIGHTS RESIDENCES | "30/- P. VILLA & 1,800/- P.A." — per-villa rule the schema can't express; needs a decision (e.g. m2 150/month = 1800 ÷ 12) | none |
| 72 | QURUM SHOPING CENTER | m1 5 · clean 25 | none |

## Files

| Action | File |
|---|---|
| new | `Modules/BackOffice/Services/LandlordContractFeeRuleParser.php` — pure: sheet phrase → field array |
| new | `tests/Unit/LandlordContractFeeRuleParserTest.php` |
| new | `Modules/BackOffice/Database/Seeders/LandlordContractFeesSeeder.php` — the mapping data + apply logic |
| new | `Modules/BackOffice/Database/Seeders/data/landlord_contract_fees.php` — returns the 82-row array (sheet name, building_code, expected DB name, raw fee phrase, cleaning) so the data is reviewable in one place |
| edit | `docs/superpowers/plans/2026-09-14-landlord-contract-fees-seeder.md` (this file) — tick tasks, record run output |

Nothing else changes: no migration, no controller/view edits, calc engine untouched.

## Task 1 — Resolve building codes and freeze the data file

- [x] Write `scratchpad/resolve_codes.php` (throwaway) that reads the sheet + the map above, looks up `buildings.building_code` and `building_name` for each mapped id, and prints the 82-row PHP array.
- [x] Save the output as `Modules/BackOffice/Database/Seeders/data/landlord_contract_fees.php`:
  ```php
  return [
      ['sheet' => '1323 AL FALAJ BUILDING', 'code' => '457', 'db_name' => '1323-Al Falaj', 'fee' => '4% P. M.', 'cleaning' => '25', 'review' => false],
      ['sheet' => 'AL NOOR BLDG', 'code' => null, 'db_name' => null, 'fee' => '5% P. M.', 'cleaning' => '25', 'review' => false], // unmatched → skipped
      ...
  ];
  ```
  Rows with `code === null` are the 10 skipped ones; they stay in the file so the report lists them and so filling in a code later is a one-line edit.

## Task 2 — Rule parser (TDD)

- [x] `tests/Unit/LandlordContractFeeRuleParserTest.php` (PHPUnit `TestCase`, no DB), cases:
  - `'5% P. M.'` → `['management_method'=>1,'landlord_contract_management_fee'=>5.0]`
  - `'3.5% P.M.'` → method 1, fee 3.5
  - `'400/- P. M.'`, `'1,500/- P.M.'`, `'50 P.M.'` → method 2, fee 400 / 1500 / 50
  - `'100/- P.A.'` → method 2, fee 8.333
  - `'6% P. M.  & RO 15/- for Renwal'` → method 1 fee 6, renewal 15 (note the sheet typo "Renwal")
  - `'125/- P. M. & RO 20/- for New Lease'` → new_leasing_fee_type 2, fee 20
  - `'100/- P. M. & 4% on New Leasing'` → new_leasing_fee_type 1, fee 4
  - `'5% P. M.  + 65/- P.M. (Facility Management Fees)'` → facility 65
  - `'300/- P. M. & 4% on New Leasing & RO 20/- for Renewal'` → all three
  - `'5% P. M.  + Each Renewal RO 25'` → renewal 25
  - `'30/- P. VILLA & 1,800/- P.A.'` → throws `InvalidArgumentException` (unsupported; row is skipped and reported)
  - cleaning: `'25'` → 25.0, `'0'` → 0.0, `''` → null (unchanged)
- [x] `LandlordContractFeeRuleParser::parse(string $feePhrase, string $cleaning): array` returning exactly the 7 columns (`management_method`, `landlord_contract_management_fee`, `landlord_contract_facility_management_fee`, `landlord_contract_renewal_fee`, `landlord_contract_new_leasing_fee_type`, `landlord_contract_new_leasing_fee`, `landlord_contract_cleaning_charge`) — unmentioned fee components are `null`; cleaning `null` means "don't write". Implementation: split on `&` / `+`, classify each part with small regexes (`%`, `P.A.`, `Renew`, `New Leas`, `Facility`), strip `RO`, `/-`, commas. PHP 7.1-compatible.
- [x] Run: `C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe vendor/phpunit/phpunit/phpunit tests/Unit/LandlordContractFeeRuleParserTest.php` → green.

## Task 3 — Seeder

- [x] `Modules/BackOffice/Database/Seeders/LandlordContractFeesSeeder.php`:
  - `run()` loads the data file, and for each row:
    1. `code === null` → collect in `$skipped` (reason "no building mapped").
    2. `Building::where('building_code', $code)->first()`; missing → `$skipped` ("code not found"); `building_name !== db_name` → still apply but add to `$warnings` (name drift between environments).
    3. `parse()`; `InvalidArgumentException` → `$skipped` ("unsupported rule").
    4. Active contracts: `LandlordContract::active()->where('building_id', $b->id)->get()` (status 1). None → `$skipped` ("no active contract"). More than one → apply to each, note in `$warnings` (e.g. Al Muntaha has 2).
    5. Build `$new` = parsed fields; when `management_method === 1` and the contract's current `landlord_contract_percentage` is not 1 or 2 → `$new['landlord_contract_percentage'] = 2`; drop `landlord_contract_cleaning_charge` from `$new` when null.
    6. Record `['contract_no', 'building', 'before' => current values of the 8 columns, 'after' => $new]` in `$changes`; skip the write if nothing differs.
  - **Dry-run by default.** Writes happen only when `env('LC_FEES_APPLY') === '1'` (`LC_FEES_APPLY=1 php artisan db:seed --class=...`; on Windows `set LC_FEES_APPLY=1 && ...`). Inside `DB::transaction`, `LandlordContract::where('id', $id)->update($new)` per contract (no timestamps churn beyond `updated_at`, no model events).
  - Before writing, dump `$changes` to `storage/app/landlord_contract_fees_backup_<Ymd_His>.json` — this is the rollback (a tiny `--rollback` is not built; restoring is `UPDATE ... SET` from the JSON if ever needed).
  - Print via `$this->command->info()/warn()/table()`: a `table()` of contract no · building · field · before → after; then WARNINGS; then SKIPPED with reasons; then a summary line `applied N contracts / M buildings, skipped K rows` and, in dry-run, `DRY RUN — nothing written. Re-run with LC_FEES_APPLY=1 to apply.`
  - Idempotent: re-running after apply reports 0 changes.
- [x] Register nothing — run by class name: `php artisan db:seed --class="Modules\BackOffice\Database\Seeders\LandlordContractFeesSeeder"` (nwidart autoloads `Modules\BackOffice\Database\Seeders`). Do **not** add it to `BackOfficeDatabaseSeeder` (that would make a generic `db:seed` rewrite contracts).

## Task 4 — Verify locally, then hand-off for the server

- [x] Dry run locally → eyeball the table: cleaning charges move off 25, methods/fees unchanged where already equal, basis fixed to 2 on the junk rows, 10 rows skipped with reasons, 6 "review" rows flagged.
- [x] Apply locally (`LC_FEES_APPLY=1`) → re-run dry → "0 changes". Spot-check in psql: `SELECT landlord_contract_no, management_method, landlord_contract_management_fee, landlord_contract_percentage, landlord_contract_cleaning_charge FROM landlord_contract WHERE building_id IN (430, 434, 114, 464) AND landlord_contract_status = 1;`
- [x] Functional check: `/landlord-invoice-v2/create` for Mazoon (5% + facility 100, cleaning 75) for last month — Tax Invoice lines show MANAGEMENT FEES = 5% of that month's collection, CLEANING CHARGES = 75.000 (previously 25.000). For a building whose basis was junk (e.g. Al Aroos, pct=0 → 2), the management fee line is non-zero where it was 0 before.
- [x] `vendor/phpunit/phpunit --testsuite Unit` green.
- [ ] Server run-book (pending: files mirrored to server_upload_files/, not yet run on server) (paste into the hand-off):
  1. Upload `Modules/BackOffice/Services/LandlordContractFeeRuleParser.php`, `Modules/BackOffice/Database/Seeders/LandlordContractFeesSeeder.php`, `Modules/BackOffice/Database/Seeders/data/landlord_contract_fees.php` (mirror into `server_upload_files/`).
  2. `php artisan db:seed --class="Modules\BackOffice\Database\Seeders\LandlordContractFeesSeeder"` → review the dry-run table (compare skipped/warnings against local).
  3. `LC_FEES_APPLY=1 php artisan db:seed --class="Modules\BackOffice\Database\Seeders\LandlordContractFeesSeeder"`; keep the printed backup path.
  4. Re-run step 2 → expect "0 changes".
- [ ] Commit: parser + test + seeder + data + this plan (`feat(landlord-contract): seed management fees and cleaning charges from NM sheet`).

## Open items for the user (do not block Tasks 1–3)

1. Building names for the 10 skipped rows (or confirm they are not in PLMS). In particular: AL NOOR → plain or MERA? AL MAWALEH BUILDING MOD → Al Mawallah Bldg or Mawallah New Bldg?
2. QURUM HEIGHTS RESIDENCES "30/- per villa & 1,800/- P.A." — how to represent (monthly fixed 150 = 1800 ÷ 12, with the per-villa part ignored?).
3. Confirm the 6 "review" mappings (Abdul Malik Villa → Rex Villas, Daisait → Darsait Villas, Iman → Iman House, Samar → Samer, Bait Al Rafay → Bait Rafey, Al Qandeel → Al Qandeel-2).

## Run log

- 2026-09-14 local (2nd pass): mapped Al Sulaef → Al Suleyf Bldg (215), Al Tehmar → Al Themar Bldg (456), Nargis → Narjis Bldg (346); applied 3 more contracts; 6 rows remain skipped by decision (Awqaf, MOJ ×3, Qurum Heights, Qurum Shopping Center).
- 2026-09-14 local: dry run → 68 contracts / 68 buildings to change (cleaning charge off the 25 default everywhere; %-basis normalised to 2 on 40 contracts; management fee, facility, renewal and new-leasing already matched the sheet). 9 rows skipped (Al Sulaef, Al Tehmar, Awqaf, MOJ ×3, Nargis, Qurum Heights, Qurum Shopping Center). Applied → backup `storage/app/landlord_contract_fees_backup_20260914_130749.json`; re-run dry → 0 changes.
- Decisions applied: AL NOOR → Al Noor Bldg (code 208), AL MAWALEH MOD → Al Mawallah Bldg (code 366), QURUM HEIGHTS skipped.
- Calc check Aug-2026: Al Aroos management fee 108.500 (was 0 — basis was 0), cleaning 40; Mazoon cleaning 75, facility 100.
