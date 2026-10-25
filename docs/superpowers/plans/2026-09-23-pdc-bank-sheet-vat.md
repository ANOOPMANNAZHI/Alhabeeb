# PDC Bank Sheet — Rent + VAT Cheques Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Post a bank-sheet cheque that carries rent + 5% VAT in one step — normal Rent Receipt plus a VAT General Receipt in the team's existing format — and show both on the posting result / day / history pages.

**Architecture:** The pure matcher (`BankSheetPdcMatcher`) learns two VAT outcomes: an exact rent+VAT-PDC group (normal `matched`) and a "VAT by difference" group (`vat_check`, needs a tick). A new `VatReceiptCreator` service writes the VAT General Receipt (+ one ledger line, closes the VAT PDC). `PdcBankSheetController` wires it into review, tick storage, posting (one transaction per cheque) and the audit trail; one new audit column records the VAT receipt number so result pages can list it.

**Tech Stack:** Laravel 5.x module (nwidart), PostgreSQL, Blade + jQuery, PHPUnit. **PHP 7.1-compatible syntax** (live server < 7.4). Run PHP with `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe` (PATH php is 8.x and cannot boot this app).

**Spec:** `docs/superpowers/specs/2026-09-23-pdc-bank-sheet-vat-design.md`

## Global Constraints

- No arrow functions, `?->`, `match`, `??=`, typed properties, named arguments.
- VAT rate 5% (`0.05`); Case B tolerance `|actual extra − 5% × rent| ≤ 1.000` OMR.
- Case B only for Comprehensive buildings: `buildings.management_id = 1`.
- VAT receipt: `receipts_generation_type = 1`, `payment_method = 1`, number from `receipt_cheque` sequence, cheque no `<PDC bank code>__<cheque no>`, remark/description `VAT for <d.m.y> to <d.m.y> UNIT NO. <unit_no> , <building_name>`, `finance_dim` = `AX_DIVISION_HO` when `trim(buildings.ax_division) === '01'` else `AX_DIVISION_PLMS`.
- One ledger line: credit account `12211` (`acc_codes.id` looked up by `acc_code_val = 12211`), `dim_type` `LEDGER`, narration `VAT for <d.m.y> to <d.m.y>`.
- Approval: user has permission `request_approval_receipt` → status 1 / approval 3, else status 0 / approval 1 (same as `postSinglePdc`).
- Rent + VAT for one cheque succeed or roll back together.
- VAT PDCs (`pdc_type 3`) are never passed to `RoutinesController::postSinglePdc`.
- Server rollout: run `add_pdc_bank_sheet_vat_receipt.sql` before copying code.

## Review Focus

1. **A VAT General Receipt was already made by hand for the cheque** → do not create a second one; record the existing receipt no and still post the rent. (Task 2 test + Task 6 integration check.)
2. **Rent PDC cannot be posted (receipts already run to contract end)** → the whole cheque fails with that reason; no VAT receipt is left behind. (Task 4 code + Task 6 check.)
3. **Sheet row matches only a VAT PDC** (e.g. tenant paid VAT separately) → never auto-matched; shows Not found. (Task 1 test.)
4. **Extra just outside tolerance** (159 on 150 rent) or **non-Comprehensive building** → stays Amount mismatch, nothing offered. (Task 1 tests.)
5. **Case A group where the VAT PDC amount is wrong** (rent 1,250 + VAT PDC 60 vs bank 1,312.5) → falls to Case B, VAT receipt for the actual 62.5, VAT PDC still closed. (Task 1 test.)

---

## Prerequisite (before Task 1)

The posting result / day / history pages this plan extends (`pdc_bank_sheet_result`, `pdc_bank_sheet_day`, `pdc_bank_sheet_uploads`, `_bank_sheet_receipts`, `_bank_sheet_styles`, `PdcBankSheetController::uploads/result/day/receiptRows`) are **uncommitted on `master`**. Commit them first (user decision), then branch:

```bash
git checkout -b feature/pdc-bank-sheet-vat
```

---

### Task 1: Matcher — Rent + VAT outcomes

**Files:**
- Modify: `Modules/BackOffice/Services/BankSheetPdcMatcher.php`
- Test: `tests/Unit/BankSheetPdcMatcherTest.php`

**Interfaces:**
- Consumes: PDC candidate objects gain `pdc_type` (int; missing → treated as 1) and `management_id` (int; missing → not comprehensive).
- Produces:
  - `const VAT_CHECK = 'vat_check'; const VAT_RATE = 0.05; const VAT_TOLERANCE = 1.0; const COMPREHENSIVE = 1; const PDC_TYPE_VAT = 3;`
  - every result gains `'vat_amount' => float|null` and `'expected_vat' => float|null`.
  - `matched` / `ambiguous` groups may contain VAT PDCs (`pdc_type 3`); `vat_check` results carry `pdcs` = the agreement's rent PDC(s) in the month + its VAT PDC(s) on the cheque (0..n), `vat_amount` = bank − rent, `expected_vat` = 5% × rent.
  - `public static function isVat($pdc)` → bool.

- [ ] **Step 1: Write the failing tests** — append to `tests/Unit/BankSheetPdcMatcherTest.php` (inside the class):

```php
    private function vatPdc(array $overrides = [])
    {
        return $this->pdc(array_merge(['pdc_type' => 3, 'pdc_amt' => 62.5], $overrides));
    }

    public function test_rent_plus_vat_pdc_summing_to_bank_amount_is_matched_with_both_rows()
    {
        $rent = $this->pdc(['pdc_amt' => 1250.0, 'pdc_type' => 1]);
        $vat  = $this->vatPdc();
        $out  = $this->matcher()->match([$this->sheetRow(['amount' => 1312.5])], [$rent, $vat]);

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
        $this->assertSame([$rent->id, $vat->id], array_column($out[0]['pdcs'], 'id'));
    }

    public function test_rent_alone_equal_to_bank_amount_matches_without_the_vat_pdc()
    {
        $rent = $this->pdc(['pdc_amt' => 1250.0]);
        $out  = $this->matcher()->match([$this->sheetRow(['amount' => 1250.0])], [$rent, $this->vatPdc()]);

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
        $this->assertSame([$rent->id], array_column($out[0]['pdcs'], 'id'));
    }

    public function test_comprehensive_rent_with_extra_near_five_percent_is_vat_check()
    {
        foreach ([157.0, 157.5, 158.0, 156.5, 158.5] as $bank) {
            $rent = $this->pdc(['pdc_amt' => 150.0, 'management_id' => 1]);
            $out  = $this->matcher()->match([$this->sheetRow(['amount' => $bank])], [$rent]);

            $this->assertSame(BankSheetPdcMatcher::VAT_CHECK, $out[0]['status'], "bank $bank");
            $this->assertSame([$rent->id], array_column($out[0]['pdcs'], 'id'));
            $this->assertEqualsWithDelta($bank - 150.0, $out[0]['vat_amount'], 0.0001);
            $this->assertEqualsWithDelta(7.5, $out[0]['expected_vat'], 0.0001);
        }
    }

    public function test_extra_outside_tolerance_is_an_amount_mismatch()
    {
        foreach ([159.0, 156.0, 150.5] as $bank) {
            $out = $this->matcher()->match(
                [$this->sheetRow(['amount' => $bank])],
                [$this->pdc(['pdc_amt' => 150.0, 'management_id' => 1])]
            );
            $this->assertSame(BankSheetPdcMatcher::AMOUNT_MISMATCH, $out[0]['status'], "bank $bank");
        }
    }

    public function test_non_comprehensive_building_never_gets_vat_by_difference()
    {
        $out = $this->matcher()->match(
            [$this->sheetRow(['amount' => 157.5])],
            [$this->pdc(['pdc_amt' => 150.0, 'management_id' => 2])]
        );
        $this->assertSame(BankSheetPdcMatcher::AMOUNT_MISMATCH, $out[0]['status']);
    }

    public function test_vat_check_carries_the_agreements_vat_pdc_when_its_amount_is_off()
    {
        $rent = $this->pdc(['pdc_amt' => 1250.0, 'management_id' => 1]);
        $vat  = $this->vatPdc(['pdc_amt' => 60.0, 'management_id' => 1]);
        $out  = $this->matcher()->match([$this->sheetRow(['amount' => 1312.5])], [$rent, $vat]);

        $this->assertSame(BankSheetPdcMatcher::VAT_CHECK, $out[0]['status']);
        $this->assertSame([$rent->id, $vat->id], array_column($out[0]['pdcs'], 'id'));
        $this->assertEqualsWithDelta(62.5, $out[0]['vat_amount'], 0.0001);
    }

    public function test_a_row_matching_only_a_vat_pdc_is_not_found()
    {
        $out = $this->matcher()->match([$this->sheetRow(['amount' => 62.5])], [$this->vatPdc()]);
        $this->assertSame(BankSheetPdcMatcher::NOT_FOUND, $out[0]['status']);
    }

    public function test_vat_check_needs_a_single_agreement()
    {
        $a = $this->pdc(['pdc_amt' => 100.0, 'management_id' => 1, 'tenant_contract_id' => 1]);
        $b = $this->pdc(['pdc_amt' => 50.0, 'management_id' => 1, 'tenant_contract_id' => 2]);
        $out = $this->matcher()->match([$this->sheetRow(['amount' => 157.5])], [$a, $b]);
        $this->assertSame(BankSheetPdcMatcher::AMOUNT_MISMATCH, $out[0]['status']);
    }

    public function test_non_vat_results_have_null_vat_fields()
    {
        $out = $this->matcher()->match([$this->sheetRow()], [$this->pdc()]);
        $this->assertNull($out[0]['vat_amount']);
        $this->assertNull($out[0]['expected_vat']);
    }
```

- [ ] **Step 2: Run to see them fail**

Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/BankSheetPdcMatcherTest.php`
Expected: FAIL — `Undefined class constant 'VAT_CHECK'` and wrong statuses.

- [ ] **Step 3: Implement** in `BankSheetPdcMatcher.php`.

Add constants under the existing ones:

```php
    const VAT_CHECK      = 'vat_check';
    const VAT_RATE       = 0.05;
    const VAT_TOLERANCE  = 1.0;
    const COMPREHENSIVE  = 1;
    const PDC_TYPE_VAT   = 3;
```

Add helper:

```php
    public static function isVat($pdc)
    {
        return isset($pdc->pdc_type) && (int) $pdc->pdc_type === self::PDC_TYPE_VAT;
    }
```

In `matchRow`, directly after the `$found` loop, replace
`if (count($found) === 0) { ... NOT_FOUND ... }` with:

```php
        $vatFound = [];
        $rentFound = [];
        foreach ($found as $pdc) {
            if (self::isVat($pdc)) {
                $vatFound[] = $pdc;
            } else {
                $rentFound[] = $pdc;
            }
        }
        $found = $rentFound;
        if (count($found) === 0) {
            return $this->result($row, self::NOT_FOUND, [], [], 'No unposted PDC with this cheque number and bank');
        }
```

Replace the tail of `matchRow` from `$candidates = $this->candidateGroups(...)` to the end of the method with:

```php
        $amount     = (float) $row['amount'];
        $candidates = $this->candidateGroups($inMonth, $amount);
        if (count($candidates) === 0) {
            // Case A: an agreement's rent rows + its VAT PDC(s) on this cheque add up exactly
            $candidates = $this->rentPlusVatGroups($inMonth, $vatFound, $amount);
        }

        if (count($candidates) === 1) {
            return $this->result($row, self::MATCHED, $candidates[0], [], null);
        }
        if (count($candidates) > 1) {
            return $this->result($row, self::AMBIGUOUS, [], $candidates, count($candidates) . ' possible matches');
        }

        // Case B: comprehensive agreement, extra ≈ 5% of its rent
        $vatChecks = $this->vatByDifference($inMonth, $vatFound, $amount);
        if (count($vatChecks) === 1) {
            $c = $vatChecks[0];
            $out = $this->result($row, self::VAT_CHECK, $c['pdcs'], [], 'Rent ' . $this->fmt($c['rent']) . ' + VAT ' . $this->fmt($c['vat']) . ' (5% = ' . $this->fmt($c['expected']) . ')');
            $out['vat_amount']   = $c['vat'];
            $out['expected_vat'] = $c['expected'];
            return $out;
        }

        $totals = [];
        foreach ($this->groupByTenant($inMonth) as $group) {
            $totals[] = $this->fmt($this->sum($group));
        }
        return $this->result(
            $row,
            self::AMOUNT_MISMATCH,
            [],
            [],
            'PLMS total ' . implode(' / ', $totals) . ' does not equal bank amount ' . $this->fmt($amount)
        );
    }

    /** Rent rows of one agreement + that agreement's VAT PDCs, when their sum is the bank amount. */
    private function rentPlusVatGroups(array $rent, array $vat, $amount)
    {
        $out = [];
        foreach ($this->groupByContract($rent) as $contractId => $rows) {
            $vatRows = $this->vatOfContract($vat, $contractId);
            if (count($vatRows) === 0) {
                continue;
            }
            $group = array_merge($rows, $vatRows);
            if ($this->sameAmount($this->sum($group), $amount)) {
                $out[] = $group;
            }
        }
        return $out;
    }

    /** @return array of ['pdcs', 'rent', 'vat', 'expected'] for comprehensive agreements whose extra ≈ 5%. */
    private function vatByDifference(array $rent, array $vat, $amount)
    {
        $out = [];
        foreach ($this->groupByContract($rent) as $contractId => $rows) {
            if (!isset($rows[0]->management_id) || (int) $rows[0]->management_id !== self::COMPREHENSIVE) {
                continue;
            }
            $rentSum  = $this->sum($rows);
            $extra    = round($amount - $rentSum, 3);
            $expected = round($rentSum * self::VAT_RATE, 3);
            if ($extra > self::AMOUNT_EPSILON && abs($extra - $expected) <= self::VAT_TOLERANCE + self::AMOUNT_EPSILON) {
                $out[] = [
                    'pdcs'     => array_merge($rows, $this->vatOfContract($vat, $contractId)),
                    'rent'     => $rentSum,
                    'vat'      => $extra,
                    'expected' => $expected,
                ];
            }
        }
        // one cheque, one agreement: rows spread over several agreements are not guessed
        return count($this->groupByContract($rent)) === 1 ? $out : [];
    }

    private function groupByContract(array $pdcs)
    {
        $groups = [];
        foreach ($pdcs as $pdc) {
            $groups[(int) $pdc->tenant_contract_id][] = $pdc;
        }
        return $groups;
    }

    private function vatOfContract(array $vat, $contractId)
    {
        $out = [];
        foreach ($vat as $pdc) {
            if ((int) $pdc->tenant_contract_id === (int) $contractId) {
                $out[] = $pdc;
            }
        }
        return $out;
    }
```

In `result()`, add the two keys to the returned array:

```php
            'vat_amount'   => null,
            'expected_vat' => null,
```

Note: `vatByDifference` accepts VAT PDCs in `$vatFound` regardless of month (they share the cheque and agreement). Rows used in `$used` include VAT PDCs automatically, because `match()` marks every id in `result['pdcs']`.

- [ ] **Step 4: Run all matcher tests**

Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/BankSheetPdcMatcherTest.php`
Expected: `OK (25 tests, …)` — the 16 existing tests still pass.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/BankSheetPdcMatcher.php tests/Unit/BankSheetPdcMatcherTest.php
git commit -m "feat(pdc-bank-sheet): match rent + VAT cheques (exact VAT PDC or ~5% extra)"
```

---

### Task 2: `VatReceiptCreator` service

**Files:**
- Create: `Modules/BackOffice/Services/VatReceiptCreator.php`
- Test: `tests/Unit/VatReceiptCreatorTest.php`

**Interfaces:**
- Consumes: `RoutinesController::receiptGenerateCode($key)`, `RoutinesController::incrementSequenceNo($key)` (both public).
- Produces:
  - `public function __construct(RoutinesController $routines)`
  - `public static function remark($effFrom, $effTo, $unitNo, $buildingName)` → string
  - `public static function narration($effFrom, $effTo)` → string
  - `public static function chequeNo($bankCode, $chequeNo)` → string
  - `public static function division($axDivision)` → string
  - `public function existing($contractId, $chequeNo)` → `ReceiptsGeneration|null`
  - `public function create(array $a)` → `['receipt_no' => string, 'created' => bool]`, with `$a` keys: `contract` (TenantContract with `Unit`, `building`), `cheque_no`, `bank_code`, `amount`, `eff_from`, `eff_to`, `deposit_date`, `deposit_bank_id`, `vat_pdcs` (array of `Pdc`, may be empty).

- [ ] **Step 1: Write the failing test** `tests/Unit/VatReceiptCreatorTest.php`:

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\VatReceiptCreator;

class VatReceiptCreatorTest extends TestCase
{
    public function test_remark_matches_the_teams_general_receipt_format()
    {
        $this->assertSame(
            'VAT for 16.08.26 to 15.09.26 UNIT NO. S 03 , Teeb Complex',
            VatReceiptCreator::remark('2026-08-16 00:00:00', '2026-09-15', 'S 03', 'Teeb Complex')
        );
    }

    public function test_narration_is_the_period_only()
    {
        $this->assertSame('VAT for 01.09.26 to 30.09.26', VatReceiptCreator::narration('2026-09-01', '2026-09-30'));
    }

    public function test_cheque_no_uses_pdc_bank_code_and_double_underscore()
    {
        $this->assertSame('BMI__81742436', VatReceiptCreator::chequeNo(' BMI ', '81742436'));
    }

    public function test_division_is_head_office_only_for_01()
    {
        $this->assertSame(AX_DIVISION_HO, VatReceiptCreator::division('01'));
        $this->assertSame(AX_DIVISION_PLMS, VatReceiptCreator::division('02'));
        $this->assertSame(AX_DIVISION_PLMS, VatReceiptCreator::division('2'));
        $this->assertSame(AX_DIVISION_PLMS, VatReceiptCreator::division(null));
    }
}
```

Add at the top of the test file (after `use` lines) so the constants exist without booting Laravel (values from `config/constants.php`; that file calls `env()`, so it is not required here):

```php
if (!defined('AX_DIVISION_HO')) {
    define('AX_DIVISION_HO', '01');
}
if (!defined('AX_DIVISION_PLMS')) {
    define('AX_DIVISION_PLMS', '02');
}
```

- [ ] **Step 2: Run to see it fail**

Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/VatReceiptCreatorTest.php`
Expected: FAIL — `Class 'Modules\BackOffice\Services\VatReceiptCreator' not found`.

- [ ] **Step 3: Implement** `Modules/BackOffice/Services/VatReceiptCreator.php`:

```php
<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Entities\ReceiptGenerationDim;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Http\Controllers\RoutinesController;

/**
 * The VAT part of a rent + VAT cheque, booked as a General Receipt in the
 * format the back office types by hand (reference: CPY2619717):
 * "VAT for 16.08.26 to 15.09.26 UNIT NO. S 03 , Teeb Complex", one credit
 * line to 12211 RENT RECEIVABLE. Runs inside the caller's transaction.
 * PHP < 7.4 in production: no arrow functions.
 */
class VatReceiptCreator
{
    const SEQUENCE       = 'receipt_cheque';
    const LEDGER_ACCOUNT = 12211;

    private $routines;

    public function __construct(RoutinesController $routines)
    {
        $this->routines = $routines;
    }

    public static function remark($effFrom, $effTo, $unitNo, $buildingName)
    {
        return self::narration($effFrom, $effTo) . ' UNIT NO. ' . $unitNo . ' , ' . $buildingName;
    }

    public static function narration($effFrom, $effTo)
    {
        return 'VAT for ' . Carbon::parse($effFrom)->format('d.m.y') . ' to ' . Carbon::parse($effTo)->format('d.m.y');
    }

    public static function chequeNo($bankCode, $chequeNo)
    {
        return trim((string) $bankCode) . '__' . trim((string) $chequeNo);
    }

    public static function division($axDivision)
    {
        return trim((string) $axDivision) === '01' ? AX_DIVISION_HO : AX_DIVISION_PLMS;
    }

    /** A live (not deleted, not cancelled) General Receipt already booked for this agreement's cheque. */
    public function existing($contractId, $chequeNo)
    {
        return ReceiptsGeneration::where('tenant_contract_id', $contractId)
            ->where('receipts_generation_type', 1)
            ->where('receipts_generation_status', '<>', 2)
            ->whereRaw("split_part(receipts_generation_cheque_no, '__', 2) = ?", [trim((string) $chequeNo)])
            ->orderBy('id')
            ->first();
    }

    /** @return array ['receipt_no' => string, 'created' => bool] */
    public function create(array $a)
    {
        $contract = $a['contract'];
        $vatPdcs  = isset($a['vat_pdcs']) ? $a['vat_pdcs'] : [];

        $existing = $this->existing($contract->id, $a['cheque_no']);
        if ($existing !== null) {
            $this->closePdcs($vatPdcs, $existing->receipts_generation_receipt_no, $a['deposit_date']);
            return ['receipt_no' => $existing->receipts_generation_receipt_no, 'created' => false];
        }

        $account = AccountCodes::where('acc_code_val', self::LEDGER_ACCOUNT)->first();
        if ($account === null) {
            throw new \RuntimeException('Account ' . self::LEDGER_ACCOUNT . ' (RENT RECEIVABLE) is missing');
        }

        $approved  = \Auth::user()->hasPermissionTo('request_approval_receipt');
        $receiptNo = $this->routines->receiptGenerateCode(self::SEQUENCE);
        $remark    = self::remark($a['eff_from'], $a['eff_to'], $contract->Unit->unit_no, $contract->building->building_name);
        $amount    = round((float) $a['amount'], 3);

        $receipt = ReceiptsGeneration::create([
            'tenant_contract_id'                  => $contract->id,
            'receipts_generation_payment_method'  => 1, // cheque
            'receipts_generation_receipt_no'      => $receiptNo,
            'receipts_generation_receipt_date'    => $a['deposit_date'],
            'bank_id'                             => $a['deposit_bank_id'],
            'receipts_generation_amt'             => $amount,
            'receipts_generation_cheque_no'       => self::chequeNo($a['bank_code'], $a['cheque_no']),
            'receipts_generation_remark'          => $remark,
            'receipts_generation_description'     => $remark,
            'receipts_generation_posted_by'       => \Auth::user()->id,
            'receipts_generation_posted_date'     => date('Y-m-d'),
            'receipts_generation_status'          => $approved ? 1 : 0,
            'receipts_generation_approval_status' => $approved ? 3 : 1,
            'receipts_generation_type'            => 1, // general
            'receipts_generation_index'           => 0,
            'finance_dim'                         => self::division($contract->building->ax_division),
            'created_by'                          => \Auth::user()->id,
        ]);

        ReceiptGenerationDim::create([
            'receipts_generation_id' => $receipt->id,
            'account_code'           => self::LEDGER_ACCOUNT,
            'description'            => $account->acc_code_desc,
            'dim_type'               => 'LEDGER',
            'debit_amount'           => 0,
            'credit_amount'          => $amount,
            'narration'              => self::narration($a['eff_from'], $a['eff_to']),
            'ac_codes_id'            => $account->id,
            'created_by'             => \Auth::user()->id,
        ]);

        $this->routines->incrementSequenceNo(self::SEQUENCE);
        $this->closePdcs($vatPdcs, $receiptNo, $a['deposit_date']);

        return ['receipt_no' => $receiptNo, 'created' => true];
    }

    private function closePdcs(array $vatPdcs, $receiptNo, $depositDate)
    {
        foreach ($vatPdcs as $pdc) {
            Pdc::where('id', $pdc->id)->update([
                'pdc_is_posted'    => 1,
                'pdc_deposit_date' => $depositDate,
                'pdc_posted_date'  => date('Y-m-d'),
                'pdc_posted_by'    => \Auth::user()->id,
                'pdc_receipt_no'   => $receiptNo,
            ]);
        }
    }
}
```

Before writing, confirm `ReceiptsGeneration` and `ReceiptGenerationDim` allow these columns (`$guarded = []` or `$fillable` includes them): `grep -n "fillable\|guarded" Modules/BackOffice/Entities/ReceiptsGeneration.php Modules/BackOffice/Entities/ReceiptGenerationDim.php`. If a column is missing from `$fillable`, add it there.

- [ ] **Step 4: Run the test**

Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/VatReceiptCreatorTest.php`
Expected: `OK (4 tests, …)`. `create()` / `existing()` are exercised by the Task 6 integration check (they need the database).

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/VatReceiptCreator.php tests/Unit/VatReceiptCreatorTest.php
git commit -m "feat(pdc-bank-sheet): VatReceiptCreator books VAT as a General Receipt"
```

---

### Task 3: Audit column `vat_receipt_no`

**Files:**
- Create: `database/migrations/2026_09_23_000000_add_vat_receipt_no_to_pdc_bank_sheet_upload_rows.php`
- Create: `add_pdc_bank_sheet_vat_receipt.sql` (repo root, like the other server SQL files; copied to `server_upload_files/` only when the user asks)

- [ ] **Step 1: Migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** VAT General Receipt no(s) created (or found) for a bank-sheet row: "CPY2619717[,CPY…]". */
class AddVatReceiptNoToPdcBankSheetUploadRows extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('pdc_bank_sheet_upload_rows', 'vat_receipt_no')) {
            Schema::table('pdc_bank_sheet_upload_rows', function (Blueprint $table) {
                $table->string('vat_receipt_no', 100)->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('pdc_bank_sheet_upload_rows', 'vat_receipt_no')) {
            Schema::table('pdc_bank_sheet_upload_rows', function (Blueprint $table) {
                $table->dropColumn('vat_receipt_no');
            });
        }
    }
}
```

(`varchar(100)` rather than the spec's 30: a Case A cheque can close VAT PDCs of more than one agreement, stored comma-separated.)

- [ ] **Step 2: Server SQL** `add_pdc_bank_sheet_vat_receipt.sql`:

```sql
-- Equivalent of migration 2026_09_23_000000_add_vat_receipt_no_to_pdc_bank_sheet_upload_rows
-- Records the VAT General Receipt no(s) a bank-sheet row created. PostgreSQL. Safe to re-run.
ALTER TABLE pdc_bank_sheet_upload_rows ADD COLUMN IF NOT EXISTS vat_receipt_no varchar(100) NULL;
```

- [ ] **Step 3: Run the migration locally**

Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=database/migrations/2026_09_23_000000_add_vat_receipt_no_to_pdc_bank_sheet_upload_rows.php`
Expected: `Migrated: 2026_09_23_000000_add_vat_receipt_no_to_pdc_bank_sheet_upload_rows`

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_09_23_000000_add_vat_receipt_no_to_pdc_bank_sheet_upload_rows.php add_pdc_bank_sheet_vat_receipt.sql
git commit -m "feat(pdc-bank-sheet): audit column for the VAT receipt no"
```

---

### Task 4: Controller — candidates, review selection, posting, audit

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/PdcBankSheetController.php`
- Modify: `Modules/BackOffice/Entities/Pdc.php` (type label)

**Interfaces:**
- Consumes: Task 1 (`VAT_CHECK`, `isVat`, `vat_amount`, `expected_vat`), Task 2 (`VatReceiptCreator`), Task 3 column.
- Produces for Task 5 (view): `analyse()['sections']['vat']` (array of results), `counts['vat']`, `selection['vat']` (`[row_no => true]` = ticked), `select()` accepting `kind = vat`, view variable `$vatRows` (array, not paginated).
- Produces for Task 6 (result pages): audit rows with `vat_receipt_no`.

- [ ] **Step 1: `Pdc` type label** — in `getPdcTypeNameAttribute()` add `case '3' : return 'VAT';` after the Deposit case.

- [ ] **Step 2: `candidatePdcs()`** — change `->whereIn('pdc_type', [1, 2])` to `->whereIn('pdc_type', [1, 2, 3])` and add two keys to each `$out[]` object:

```php
                'pdc_type'           => (int) $pdc->pdc_type,
                'management_id'      => $contract && $contract->building ? (int) $contract->building->management_id : 0,
```

- [ ] **Step 3: `analyse()`** — sections become `['ambiguous' => [], 'vat' => [], 'matched' => [], 'cannot' => []]`; add before the `else` branch:

```php
            } elseif ($r['status'] === BankSheetPdcMatcher::VAT_CHECK) {
                $sections['vat'][] = $r;
```

and add `'vat' => count($sections['vat']),` to `$counts`.

- [ ] **Step 4: selection storage** — `selection()` returns a third key:

```php
            'vat'      => isset($s['vat']) ? (array) $s['vat'] : [],
```

(update its docblock: `'vat' => [row_no => true]` = ticked Rent + VAT row). In `select()` change the rule to `'kind' => 'required|in:tick,choice,vat'` and add a branch before `else`:

```php
        } elseif ($request->kind === 'vat') {
            if ($request->value === '1' || $request->value === 1 || $request->value === true) {
                $selection['vat'][$row] = true;
            } else {
                unset($selection['vat'][$row]);
            }
```

- [ ] **Step 5: `preview()`** — after `$toPost` is computed add the ticked VAT rows that are still VAT_CHECK rows, and pass the section to the view:

```php
        $vatRowNos = [];
        foreach ($sections['vat'] as $r) {
            $vatRowNos[$r['row']['row_no']] = true;
        }
        $toPost += count(array_intersect_key($selection['vat'], $vatRowNos));
```

Add `'vatRows' => $sections['vat'],` to the view data.

- [ ] **Step 6: `post()` selection** — in the `foreach ($analysis['results'] as $r)` loop add:

```php
            } elseif ($r['status'] === BankSheetPdcMatcher::VAT_CHECK && !empty($selection['vat'][$rowNo])) {
                $toPost[$rowNo] = $r['pdcs'];
                $vatByDiff[$rowNo] = $r['vat_amount'];
```

with `$vatByDiff = []; // row_no => VAT amount (Case B)` declared next to `$toPost`, and keep the sheet row for the cheque no: declare `$sheetRows = [];` and set `$sheetRows[$rowNo] = $r['row'];` at the top of the loop body.

- [ ] **Step 7: `post()` transaction** — replace the per-cheque block (`$ids = []; try { DB::transaction(... ) ... } catch ...`) with:

```php
        $vatCreator = new VatReceiptCreator($routines);
        $vatMade    = 0;
        foreach ($toPost as $rowNo => $pdcs) {
            $ids = [];
            $vatNos = [];
            $vatBefore = $vatMade; // the closure counts receipts; undo if the cheque rolls back
            try {
                DB::transaction(function () use ($routines, $vatCreator, $pdcs, $request, $rowNo, $vatByDiff, $sheetRows, &$ids, &$tillEnd, &$vatNos, &$vatMade) {
                    $rent = [];
                    $vat  = [];
                    foreach ($pdcs as $p) {
                        $pdc = Pdc::lockForUpdate()->find($p->id);
                        if ($pdc === null || (int) $pdc->pdc_is_posted === 1) {
                            throw new \RuntimeException('cheque ' . $p->pdc_check_no . ' was already posted');
                        }
                        if (BankSheetPdcMatcher::isVat($pdc)) {
                            $vat[] = $pdc;
                        } else {
                            $rent[] = $pdc;
                        }
                    }
                    $hasVat = count($vat) > 0 || isset($vatByDiff[$rowNo]);

                    $periods = []; // contract id => [from, to] of the rent receipts just made
                    foreach ($rent as $pdc) {
                        $result = $routines->postSinglePdc($pdc, $request->pdc_deposit_date, $request->bank_id);
                        if (!$result['posted']) {
                            if ($hasVat) {
                                throw new \RuntimeException('receipts already run to contract end for ' . $result['contract_no'] . '; VAT not posted');
                            }
                            $tillEnd[] = $result['contract_no'];
                            continue;
                        }
                        $ids[] = $pdc->id;
                        $receipt = ReceiptsGeneration::where('receipts_generation_receipt_no', Pdc::where('id', $pdc->id)->value('pdc_receipt_no'))->first();
                        $cid = (int) $pdc->tenant_contract_id;
                        $from = $receipt ? $receipt->receipts_generation_eff_from : null;
                        $to   = $receipt ? $receipt->receipts_generation_eff_to : null;
                        if (!isset($periods[$cid])) {
                            $periods[$cid] = [$from, $to];
                        } else {
                            if ($from && (!$periods[$cid][0] || $from < $periods[$cid][0])) { $periods[$cid][0] = $from; }
                            if ($to && (!$periods[$cid][1] || $to > $periods[$cid][1])) { $periods[$cid][1] = $to; }
                        }
                    }
                    if (!$hasVat) {
                        return;
                    }

                    $chequeNo = $rent[0]->pdc_check_no;
                    $bankCode = Bank::where('id', $rent[0]->bank_id)->value('bank_code');
                    // Case B: one receipt for the actual extra; Case A: one per VAT PDC, its own amount
                    $jobs = isset($vatByDiff[$rowNo])
                        ? [['contract_id' => (int) $rent[0]->tenant_contract_id, 'amount' => $vatByDiff[$rowNo], 'pdcs' => $vat]]
                        : array_map(function ($v) { return ['contract_id' => (int) $v->tenant_contract_id, 'amount' => (float) $v->pdc_amt, 'pdcs' => [$v]]; }, $vat);

                    foreach ($jobs as $job) {
                        if (!isset($periods[$job['contract_id']]) || !$periods[$job['contract_id']][0]) {
                            throw new \RuntimeException('no rent receipt period for the VAT of cheque ' . $chequeNo);
                        }
                        $made = $vatCreator->create([
                            'contract'        => TenantContract::with(['Unit', 'building'])->find($job['contract_id']),
                            'cheque_no'       => $chequeNo,
                            'bank_code'       => $bankCode,
                            'amount'          => $job['amount'],
                            'eff_from'        => $periods[$job['contract_id']][0],
                            'eff_to'          => $periods[$job['contract_id']][1],
                            'deposit_date'    => $request->pdc_deposit_date,
                            'deposit_bank_id' => $request->bank_id,
                            'vat_pdcs'        => $job['pdcs'],
                        ]);
                        $vatNos[] = $made['receipt_no'];
                        if ($made['created']) {
                            $vatMade++;
                        }
                        foreach ($job['pdcs'] as $v) {
                            $ids[] = $v->id;
                        }
                    }
                });
                $posted++;
                $pdcsDone += count($ids);
                $outcome[$rowNo] = ['posted', null, $ids, implode(',', $vatNos)];
            } catch (\Exception $e) {
                $vatMade = $vatBefore;
                $failed[] = $pdcs[0]->pdc_check_no . ': ' . $e->getMessage();
                $outcome[$rowNo] = ['failed', $e->getMessage(), [], null];
            }
        }
```

Add imports at the top of the controller:

```php
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Services\VatReceiptCreator;
use Modules\Sales\Entities\TenantContract;
```

(`Bank` and `Pdc` are already imported.)

- [ ] **Step 8: success message** — replace the `$msg` first line with:

```php
        $msg = $posted . ' cheque(s) posted (' . $pdcsDone . ' PDC row(s)' . ($vatMade > 0 ? ', ' . $vatMade . ' VAT receipt(s)' : '') . '); '
             . ($analysis['counts']['cannot']) . ' left for manual posting';
```

- [ ] **Step 9: `audit()`** — outcome tuples now have 4 items. Change

```php
            $ids    = [];
            if (isset($outcome[$rowNo])) {
                list($status, $reason, $ids) = $outcome[$rowNo];
            } elseif ($status === BankSheetPdcMatcher::MATCHED || $status === BankSheetPdcMatcher::AMBIGUOUS) {
```

to

```php
            $ids    = [];
            $vatNo  = null;
            if (isset($outcome[$rowNo])) {
                list($status, $reason, $ids, $vatNo) = $outcome[$rowNo];
            } elseif (in_array($status, [BankSheetPdcMatcher::MATCHED, BankSheetPdcMatcher::AMBIGUOUS, BankSheetPdcMatcher::VAT_CHECK], true)) {
```

and add `'vat_receipt_no' => $vatNo !== '' ? $vatNo : null,` to the `$lines[]` row.

- [ ] **Step 10: Lint + existing tests**

Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l Modules/BackOffice/Http/Controllers/PdcBankSheetController.php` → `No syntax errors detected`
Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/BankSheetPdcMatcherTest.php tests/Unit/VatReceiptCreatorTest.php` → OK

- [ ] **Step 11: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/PdcBankSheetController.php Modules/BackOffice/Entities/Pdc.php
git commit -m "feat(pdc-bank-sheet): post rent + VAT cheques with a VAT General Receipt"
```

---

### Task 5: Review page — "Rent + VAT (check)" section and Matched badge

**Files:**
- Modify: `Modules/BackOffice/Resources/views/Routine/pdc_bank_sheet_preview.blade.php`

**Interfaces:**
- Consumes: `$vatRows`, `$counts['vat']`, `$selection['vat']`, `select` endpoint `kind=vat` (Task 4); `$p->pdc_type_name` returns `VAT` (Task 4).

- [ ] **Step 1: CSS** — add to the `<style>` in `@section('css')`:

```css
  .bsp-pill-vat { background:#e0e7ff; color:#3730a3; }
```

- [ ] **Step 2: pill in the action bar** — after the `need choice` pill add:

```blade
            @if($counts['vat'] > 0)
            <span class="bsp-pill bsp-pill-vat"><i class="fa fa-percent" aria-hidden="true"></i>{{ $counts['vat'] }} rent + VAT to check</span>
            @endif
```

- [ ] **Step 3: new section** — insert directly before `{{-- ── 2. Auto-matched ── --}}`:

```blade
{{-- ── 1b. Rent + VAT by difference (tick to post) ─────────────── --}}
@if(count($vatRows) > 0)
<div class="row"><div class="col-md-12 col-sm-12">
<div class="card card-box">
  <div class="card-body">
    <div class="bsp-head">
      <h4><i class="fa fa-percent" style="color:#3730a3" aria-hidden="true"></i> Rent + VAT (check) <span class="bsp-pill bsp-pill-vat">{{ $counts['vat'] }}</span></h4>
      <span class="bsp-hint">Bank amount is the rent plus about 5% VAT. Tick to post the rent receipt and a VAT General Receipt for the extra.</span>
    </div>
    <div class="table-wrap"><div class="table-responsive">
      <table class="table display product-overview mb-30">
        <thead>
          <tr>
            <th width="4%"></th>
            <th width="10%">Cheque No</th>
            <th width="9%">Cheque Date</th>
            <th class="num" width="9%">Bank Amount</th>
            <th>Tenant</th>
            <th>Agr No</th>
            <th>Building / Unit</th>
            <th class="num">Rent</th>
            <th class="num">VAT 5%</th>
            <th class="num">Actual Extra</th>
            <th class="num">Difference</th>
          </tr>
        </thead>
        <tbody>
          @foreach($vatRows as $r)
          @php
            $rowNo = $r['row']['row_no'];
            $rentPdcs = array_values(array_filter($r['pdcs'], function ($p) { return (int) $p->pdc_type !== 3; }));
            $first = $rentPdcs[0];
            $rentSum = 0; foreach ($rentPdcs as $p) { $rentSum += $p->pdc_amt; }
            $on = !empty($selection['vat'][$rowNo]);
          @endphp
          <tr class="{{ $on ? '' : 'is-off' }}">
            <td><input type="checkbox" class="bsp-vat-tick" data-row="{{ $rowNo }}" {{ $on ? 'checked' : '' }} aria-label="Post cheque {{ $r['row']['cheque_no'] }} with VAT"></td>
            <td><b>{{ $r['row']['cheque_no'] }}</b></td>
            <td>{{ $fmtDate($r['row']['cheque_date']) }}</td>
            <td class="num"><b>{{ numberFormat($r['row']['amount']) }}</b></td>
            <td>{{ $first->tenant_name }}</td>
            <td>{{ $first->tenant_contract_no }}</td>
            <td>{{ $first->building_name }} / {{ $first->unit_no }}</td>
            <td class="num">{{ numberFormat($rentSum) }}</td>
            <td class="num">{{ numberFormat($r['expected_vat']) }}</td>
            <td class="num"><b>{{ numberFormat($r['vat_amount']) }}</b></td>
            <td class="num">{{ numberFormat($r['vat_amount'] - $r['expected_vat']) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div></div>
  </div>
</div>
</div></div>
@endif
```

- [ ] **Step 4: Matched table "Rent + VAT" label** — replace the Type cell `<td>{{ $first->pdc_type_name }}</td>` with:

```blade
            <td>
              {{ $first->pdc_type_name }}
              @if(count(array_filter($r['pdcs'], function ($p) { return (int) $p->pdc_type === 3; })) > 0)
                <span class="bsp-pill bsp-pill-vat">+ VAT</span>
              @endif
            </td>
```

- [ ] **Step 5: JS** — add after the `.bsp-tick` change handler:

```js
  $(document).on('change', '.bsp-vat-tick', function () {
    var $cb = $(this), row = $cb.data('row'), on = $cb.is(':checked');
    $cb.closest('tr').toggleClass('is-off', !on);
    setCount(count + (on ? 1 : -1));
    remember(row, 'vat', on ? '1' : '0', function () {
      $cb.prop('checked', !on).closest('tr').toggleClass('is-off', on);
      setCount(count + (on ? -1 : 1));
    });
  });
```

- [ ] **Step 6: Compile check** — render the Blade source through the compiler and lint:

```bash
cat > "$SCRATCH/compile.php" <<'EOF'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
file_put_contents($argv[1], Blade::compileString(file_get_contents($argv[2])));
EOF
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe "$SCRATCH/compile.php" "$SCRATCH/preview.php" Modules/BackOffice/Resources/views/Routine/pdc_bank_sheet_preview.blade.php && /c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "$SCRATCH/preview.php"
```

Expected: `No syntax errors detected`. (`$SCRATCH` = the session scratchpad directory.)

- [ ] **Step 7: Commit**

```bash
git add Modules/BackOffice/Resources/views/Routine/pdc_bank_sheet_preview.blade.php
git commit -m "ui(pdc-bank-sheet): Rent + VAT review section and VAT badge"
```

---

### Task 6: Result pages list VAT receipts + end-to-end check

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/PdcBankSheetController.php` (`receiptRows`)
- Modify: `Modules/BackOffice/Resources/views/Routine/_bank_sheet_receipts.blade.php` (type label)
- Scratch (not committed): `$SCRATCH/vat_e2e.php`

**Interfaces:**
- Consumes: audit `vat_receipt_no` (Task 3/4).
- Produces: `receiptRows()` rows gain `is_vat` (bool/int).

- [ ] **Step 1: `receiptRows()`** — replace the SQL with a UNION of the rent path (via `pdc_ids`) and the VAT path (via `vat_receipt_no`), de-duplicated by receipt id so a Case A VAT receipt reachable both ways appears once. Keep the empty-ids guard and `$in` placeholder line above it; the method body after the guard becomes:

```php
        $rows = DB::select("
            SELECT y.upload_id, y.sheet_row_no, y.sheet_cheque_no, y.sheet_bank_name,
                   (y.is_vat = 1 OR y.pdc_type = 3) AS is_vat,
                   rg.id AS receipt_id, rg.receipts_generation_receipt_no AS receipt_no,
                   rg.receipts_generation_receipt_date AS receipt_date,
                   rg.receipts_generation_amt AS amount,
                   rg.receipts_generation_type AS receipt_type,
                   rg.receipts_generation_status AS receipt_status,
                   rg.receipts_generation_approval_status AS approval_status,
                   rg.deleted_at,
                   tc.tenant_contract_no, t.tenant_name, b.building_name, un.unit_no
            FROM (
                SELECT DISTINCT ON (x.receipt_id) x.*
                FROM (
                    SELECT ur.upload_id, ur.sheet_row_no, ur.cheque_no AS sheet_cheque_no, ur.bank_name AS sheet_bank_name,
                           rg.id AS receipt_id, 0 AS is_vat, p.pdc_type
                    FROM pdc_bank_sheet_upload_rows ur
                    JOIN pdc p ON p.id = ANY (string_to_array(ur.pdc_ids, ',')::int[])
                    JOIN receipts_generation rg ON rg.receipts_generation_receipt_no = p.pdc_receipt_no
                                               AND rg.tenant_contract_id = p.tenant_contract_id
                    WHERE ur.upload_id IN ($in) AND ur.status = 'posted' AND ur.pdc_ids IS NOT NULL
                    UNION ALL
                    SELECT ur.upload_id, ur.sheet_row_no, ur.cheque_no, ur.bank_name, rg.id, 1, 3
                    FROM pdc_bank_sheet_upload_rows ur
                    JOIN receipts_generation rg ON rg.receipts_generation_receipt_no = ANY (string_to_array(ur.vat_receipt_no, ','))
                    WHERE ur.upload_id IN ($in) AND ur.status = 'posted' AND ur.vat_receipt_no IS NOT NULL
                ) x
                ORDER BY x.receipt_id, x.is_vat
            ) y
            JOIN receipts_generation rg ON rg.id = y.receipt_id
            LEFT JOIN tenant_contracts tc ON tc.id = rg.tenant_contract_id
            LEFT JOIN tenant t ON t.id = tc.tenant_id
            LEFT JOIN buildings b ON b.id = tc.building_id
            LEFT JOIN units un ON un.id = tc.unit_id
            ORDER BY y.upload_id, y.sheet_row_no, y.is_vat, rg.receipts_generation_receipt_no", array_merge($uploadIds, $uploadIds));

        return $rows;
```

(`receipts_generation.deleted_at` rows are intentionally still returned so the page can show "Deleted".)

- [ ] **Step 2: type label** — in `_bank_sheet_receipts.blade.php` replace the Type cell with:

```blade
            <td>{{ $r->is_vat ? 'General (VAT)' : (isset($types[(int) $r->receipt_type]) ? $types[(int) $r->receipt_type] : '-') }}</td>
```

- [ ] **Step 3: Write the end-to-end check** `$SCRATCH/vat_e2e.php` (runs inside a transaction, always rolled back). It fakes a stored sheet by calling the controller's post flow directly on matcher results:

```php
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\User::whereHas('roles')->first(); Auth::login($user);
View::share('errors', new \Illuminate\Support\ViewErrorBag);

use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Services\BankSheetPdcMatcher;

function check($ok, $msg) { echo ($ok ? 'PASS ' : 'FAIL ') . $msg . "\n"; }

DB::beginTransaction();
try {
    // Case A candidate: unposted comprehensive rent PDC with an unposted VAT PDC on the same cheque
    $pair = DB::selectOne("SELECT r.id rid, v.id vid, r.pdc_amt ramt, v.pdc_amt vamt, r.pdc_check_no chq, r.pdc_check_date d, r.bank_id
        FROM pdc r JOIN pdc v ON v.tenant_contract_id=r.tenant_contract_id AND v.pdc_check_no=r.pdc_check_no AND v.pdc_type=3 AND v.pdc_is_posted=0
        JOIN tenant_contracts tc ON tc.id=r.tenant_contract_id AND tc.tenant_contract_status=1
        WHERE r.pdc_type=1 AND r.pdc_is_posted=0 AND r.pdc_cancel_reason='0' AND r.pdc_clear_date IS NULL
        ORDER BY r.pdc_check_date LIMIT 1");
    check($pair !== null, 'found an unposted rent + VAT PDC pair');

    $bankCode = DB::table('bank')->where('id', $pair->bank_id)->value('bank_clearing_codes');
    $code = trim(explode(',', (string) $bankCode)[0]);
    $row = ['row_no' => 1, 'cheque_no' => $pair->chq, 'bank_code' => $code, 'bank_name' => 'test',
            'amount' => (float) $pair->ramt + (float) $pair->vamt, 'cheque_date' => substr($pair->d, 0, 10),
            'accepted' => true, 'reject_reason' => null];

    $c = new Modules\BackOffice\Http\Controllers\PdcBankSheetController;
    $m = new ReflectionMethod($c, 'candidatePdcs'); $m->setAccessible(true);
    $bm = new ReflectionMethod($c, 'bankMap'); $bm->setAccessible(true);
    $res = (new BankSheetPdcMatcher($bm->invoke($c)))->match([$row], $m->invoke($c, [$row]));
    check($res[0]['status'] === BankSheetPdcMatcher::MATCHED, 'Case A is matched: ' . $res[0]['status'] . ' ' . $res[0]['reason']);
    check(in_array((int) $pair->vid, array_column($res[0]['pdcs'], 'id'), true), 'Case A group includes the VAT PDC');

    // Post it through VatReceiptCreator path by calling the same code post() uses is covered by the browser test;
    // here exercise VatReceiptCreator directly after posting the rent PDC.
    $routines = app(Modules\BackOffice\Http\Controllers\RoutinesController::class);
    $rentPdc = Pdc::find($pair->rid);
    $r = $routines->postSinglePdc($rentPdc, date('Y-m-d'), $pair->bank_id);
    check($r['posted'], 'rent PDC posted');
    $rentNo = Pdc::where('id', $pair->rid)->value('pdc_receipt_no');
    $rent = Modules\BackOffice\Entities\ReceiptsGeneration::where('receipts_generation_receipt_no', $rentNo)->first();

    $creator = new Modules\BackOffice\Services\VatReceiptCreator($routines);
    $made = $creator->create([
        'contract' => Modules\Sales\Entities\TenantContract::with(['Unit', 'building'])->find($rentPdc->tenant_contract_id),
        'cheque_no' => $pair->chq, 'bank_code' => DB::table('bank')->where('id', $pair->bank_id)->value('bank_code'),
        'amount' => $pair->vamt, 'eff_from' => $rent->receipts_generation_eff_from, 'eff_to' => $rent->receipts_generation_eff_to,
        'deposit_date' => date('Y-m-d'), 'deposit_bank_id' => $pair->bank_id, 'vat_pdcs' => [Pdc::find($pair->vid)],
    ]);
    check($made['created'], 'VAT receipt created: ' . $made['receipt_no']);
    $vat = Modules\BackOffice\Entities\ReceiptsGeneration::where('receipts_generation_receipt_no', $made['receipt_no'])->first();
    check((int) $vat->receipts_generation_type === 1, 'VAT receipt is a General Receipt');
    check(strpos($vat->receipts_generation_remark, 'VAT for ') === 0 && strpos($vat->receipts_generation_remark, ' UNIT NO. ') !== false, 'remark: ' . $vat->receipts_generation_remark);
    $dim = DB::table('receipts_generation_dim')->where('receipts_generation_id', $vat->id)->get();
    check(count($dim) === 1 && (int) $dim[0]->account_code === 12211 && abs($dim[0]->credit_amount - $pair->vamt) < 0.001, 'one credit line to 12211');
    check((int) Pdc::where('id', $pair->vid)->value('pdc_is_posted') === 1 && Pdc::where('id', $pair->vid)->value('pdc_receipt_no') === $made['receipt_no'], 'VAT PDC closed with the receipt no');

    $again = $creator->create([
        'contract' => Modules\Sales\Entities\TenantContract::with(['Unit', 'building'])->find($rentPdc->tenant_contract_id),
        'cheque_no' => $pair->chq, 'bank_code' => 'X', 'amount' => 1, 'eff_from' => $rent->receipts_generation_eff_from,
        'eff_to' => $rent->receipts_generation_eff_to, 'deposit_date' => date('Y-m-d'), 'deposit_bank_id' => $pair->bank_id, 'vat_pdcs' => [],
    ]);
    check(!$again['created'] && $again['receipt_no'] === $made['receipt_no'], 'duplicate guard returns the existing VAT receipt');

    // audit + result page list both receipts
    $uid = DB::table('pdc_bank_sheet_uploads')->insertGetId(['file_name' => '20260923_120000_1_vat.xls', 'pdc_deposit_date' => date('Y-m-d'), 'bank_id' => $pair->bank_id, 'rows_total' => 1, 'rows_posted' => 1, 'pdcs_posted' => 2, 'created_by' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('pdc_bank_sheet_upload_rows')->insert(['upload_id' => $uid, 'sheet_row_no' => 1, 'cheque_no' => $pair->chq, 'bank_name' => 'test', 'amount' => $row['amount'], 'cheque_date' => $row['cheque_date'], 'status' => 'posted', 'pdc_ids' => $pair->rid . ',' . $pair->vid, 'vat_receipt_no' => $made['receipt_no'], 'created_at' => now(), 'updated_at' => now()]);
    $v = $c->result($uid);
    $rows = $v->getData()['receipts'];
    check(count($rows) === 2, 'result page lists rent + VAT receipts (got ' . count($rows) . ')');
    $html = $v->render();
    check(strpos($html, 'General (VAT)') !== false, 'VAT receipt labelled General (VAT)');
} finally {
    DB::rollBack();
}
echo "rolled back\n";
```

- [ ] **Step 4: Run it**

Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe "$SCRATCH/vat_e2e.php"`
Expected: every line `PASS …`, then `rolled back`. If no unposted pair exists locally, the first check fails — then pick a posted pair and reset both PDCs (`pdc_is_posted=0, pdc_receipt_no=null`) inside the same transaction before matching.

- [ ] **Step 5: Full unit suite + lint**

Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit`
Expected: all green (the pre-existing `tests/Feature/ExampleTest` failure is unrelated; Unit only).
Run: `/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l Modules/BackOffice/Http/Controllers/PdcBankSheetController.php`

- [ ] **Step 6: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/PdcBankSheetController.php Modules/BackOffice/Resources/views/Routine/_bank_sheet_receipts.blade.php
git commit -m "feat(pdc-bank-sheet): result pages list VAT General Receipts"
```

- [ ] **Step 7: Hand-off to the user**
  - Ask the user to post a small real sheet containing one Rent + VAT cheque (Case A or B) in the browser and confirm the result page shows both receipts.
  - Server files (copy to `server_upload_files/` only when asked): `add_pdc_bank_sheet_vat_receipt.sql` (run first), `BankSheetPdcMatcher.php`, `VatReceiptCreator.php`, `PdcBankSheetController.php`, `Pdc.php`, `pdc_bank_sheet_preview.blade.php`, `_bank_sheet_receipts.blade.php`, migration file.
