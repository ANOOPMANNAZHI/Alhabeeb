# Municipality Tax on Contract Registration — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Record municipal tax, processing charge and penalty in the tenant contract's Municipality modal and create the matching General Ledger vouchers automatically; also stop the modal wiping the uploaded agreement file.

**Architecture:** A pure calculator class (`MunicipalityRegistrationCharges`) does the arithmetic and builds voucher line arrays; a DB service (`MunicipalityRegistrationLedger`) reads settings, resolves accounts, and creates / updates / removes the vouchers inside the controller's transaction. The existing General Ledger screen approves and posts them to AX unchanged.

**Tech Stack:** Laravel 5.x modular app (nwidart modules), PostgreSQL, Blade + jQuery, PHPUnit 7.

**Spec:** `docs/superpowers/specs/2026-09-28-municipality-tax-gl-design.md`

## Global Constraints

- Production runs PHP < 7.4: no arrow functions (`fn`), no `?->`, no `match`, no typed properties, no `??=`.
- Run PHP/PHPUnit locally with `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe` (system PHP 8.4 breaks PHPUnit 7).
- Money is OMR with 3 decimals; round with `round($x, 3)`.
- Settings live in table `configuration` (`App\Setting`), `configuration_name = 'settings'`, read with `prefixData($key)`.
- Voucher shape copies `GeneralLedgerController@store`: `general_ledger_type` 1, status 2 / approval 4 when the user can `ledger_approval`, else status 1 / approval 0. Posted = `general_ledger_status` 3.
- Line dims: `dim1able_type` `Modules\BackOffice\Entities\DimDetail`, `dim1able_id` 2 (PLMS); `dim2able_type` `Modules\Masters\Entities\Building`, `dim2able_id` = building id.
- Default accounts: tax 41102, penalty 41103, payment (credit) 12674; processing charge 1; tax % comes from the existing `municipal_tax_percentage` (3).
- Do not change `GeneralLedgerController`, AX posting code, or the Vacating Unit Inspection.

## Review Focus

1. **Contract value blank, "0" or "3,600.000" (varchar with commas)** → default tax is 0 or 108.000 without an error. Test in Task 2.
2. **Re-saving a contract whose tax voucher is posted with only the remark changed** (submitted "108" vs stored "108.000") → saves; no false "already posted" rejection. Test in Task 3.
3. **An account setting pointing at a code not in `acc_codes`** → save stops with a message naming the setting; contract fields not half-saved. Test in Task 3.
4. **Registered date missing in the POST** (required only in HTML) → server validation error, never a voucher with null `doc_date` (would crash GL Post). Test in Task 4.
5. **Double submit of the modal** → one voucher, sequential voucher numbers (contract row lock + counter row lock). Test in Task 3.

---

## File Structure

| File | Responsibility |
|---|---|
| `database/migrations/2026_09_28_000000_add_municipality_registration_settings.php` (create) | Insert the 4 new settings |
| `database/migrations/2026_09_28_000001_add_municipality_charges_to_tenant_contracts.php` (create) | 5 new contract columns |
| `Modules/BackOffice/Services/MunicipalityRegistrationCharges.php` (create) | Pure arithmetic + voucher line arrays |
| `tests/Unit/MunicipalityRegistrationChargesTest.php` (create) | Unit tests for the above |
| `Modules/BackOffice/Exceptions/MunicipalityLedgerException.php` (create) | User-facing save errors |
| `Modules/BackOffice/Services/MunicipalityRegistrationLedger.php` (create) | Settings, accounts, voucher create/update/remove, modal data |
| `Modules/BackOffice/Http/Controllers/TenantContractEditController.php` (modify `tenantContractAddMunicipality`, `tenantContractAddMunicipalityview`, `tenantContractAddMunicipalityStore`) | Wire it up + agreement fix |
| `Modules/BackOffice/Resources/views/TenantContract/_municipality_charges.blade.php` (create) | Shared field block + JS |
| `tenant_contract_municipality_modal.blade.php`, `tenant_contract_municipality_modal_view.blade.php` (modify) | Include the block |

---

### Task 1: Migrations

**Files:**
- Create: `database/migrations/2026_09_28_000000_add_municipality_registration_settings.php`
- Create: `database/migrations/2026_09_28_000001_add_municipality_charges_to_tenant_contracts.php`

**Interfaces:**
- Produces: settings keys `municipality_processing_charge`, `municipal_tax_account`, `municipal_tax_penalty_account`, `municipality_payment_account`; columns `tenant_contract_municipality_tax_amt`, `tenant_contract_municipality_processing_amt`, `tenant_contract_municipality_penalty_amt`, `tenant_contract_municipality_gl_id`, `tenant_contract_municipality_penalty_gl_id`.

- [ ] **Step 1: Write the settings migration**

```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Municipality registration settings (General Settings -> Settings tab).
 * The tax % already exists as municipal_tax_percentage (2026_09_16_000001).
 */
class AddMunicipalityRegistrationSettings extends Migration
{
    private $settings = [
        'municipality_processing_charge' => '1',
        'municipal_tax_account'          => '41102',
        'municipal_tax_penalty_account'  => '41103',
        'municipality_payment_account'   => '12674',
    ];

    public function up()
    {
        foreach ($this->settings as $key => $value) {
            if (!DB::table('configuration')->where('configuration_settings', $key)->exists()) {
                DB::table('configuration')->insert([
                    'configuration_name'     => 'settings',
                    'configuration_settings' => $key,
                    'configuration_value'    => $value,
                    'created_by'             => 1,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('configuration')->whereIn('configuration_settings', array_keys($this->settings))->delete();
    }
}
```

- [ ] **Step 2: Write the columns migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Municipal tax / processing / penalty saved at municipality registration, and their GL vouchers. */
class AddMunicipalityChargesToTenantContracts extends Migration
{
    public function up()
    {
        Schema::table('tenant_contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_contracts', 'tenant_contract_municipality_tax_amt')) {
                $table->decimal('tenant_contract_municipality_tax_amt', 12, 3)->nullable();
            }
            if (!Schema::hasColumn('tenant_contracts', 'tenant_contract_municipality_processing_amt')) {
                $table->decimal('tenant_contract_municipality_processing_amt', 12, 3)->nullable();
            }
            if (!Schema::hasColumn('tenant_contracts', 'tenant_contract_municipality_penalty_amt')) {
                $table->decimal('tenant_contract_municipality_penalty_amt', 12, 3)->default(0);
            }
            if (!Schema::hasColumn('tenant_contracts', 'tenant_contract_municipality_gl_id')) {
                $table->integer('tenant_contract_municipality_gl_id')->nullable();
            }
            if (!Schema::hasColumn('tenant_contracts', 'tenant_contract_municipality_penalty_gl_id')) {
                $table->integer('tenant_contract_municipality_penalty_gl_id')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('tenant_contracts', function (Blueprint $table) {
            foreach (['tenant_contract_municipality_tax_amt', 'tenant_contract_municipality_processing_amt',
                      'tenant_contract_municipality_penalty_amt', 'tenant_contract_municipality_gl_id',
                      'tenant_contract_municipality_penalty_gl_id'] as $col) {
                if (Schema::hasColumn('tenant_contracts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
```

- [ ] **Step 3: Run both migrations locally**

Run:
```
C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=database/migrations/2026_09_28_000000_add_municipality_registration_settings.php
C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=database/migrations/2026_09_28_000001_add_municipality_charges_to_tenant_contracts.php
```
Expected: `Migrated:` for each.

- [ ] **Step 4: Verify**

Run: `echo "echo json_encode(DB::table('configuration')->where('configuration_settings','like','municip%')->pluck('configuration_value','configuration_settings'));" | C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan tinker`
Expected: includes the 4 new keys with values 1 / 41102 / 41103 / 12674 and `municipal_tax_percentage` 3.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_28_000000_add_municipality_registration_settings.php database/migrations/2026_09_28_000001_add_municipality_charges_to_tenant_contracts.php
git commit -m "feat(municipality): settings and contract columns for registration charges"
```

---

### Task 2: Pure calculator `MunicipalityRegistrationCharges`

**Files:**
- Create: `Modules/BackOffice/Services/MunicipalityRegistrationCharges.php`
- Test: `tests/Unit/MunicipalityRegistrationChargesTest.php`

**Interfaces:**
- Produces:
  - `MunicipalityRegistrationCharges::money($value): float` — `"3,600.500"`→3600.5, `""`/`null`/non-numeric → 0.0, rounded 3 dp.
  - `MunicipalityRegistrationCharges::defaultTax($contractValue, $rate): float` — `money($contractValue) * (float) $rate / 100`, rounded 3 dp.
  - `MunicipalityRegistrationCharges::sameAmount($a, $b): bool` — `abs(money($a) - money($b)) < 0.0005`.
  - `MunicipalityRegistrationCharges::description($prefix, $contractNo, $buildingName, $unitNo): string` — words joined by single spaces, blanks dropped.
  - `MunicipalityRegistrationCharges::voucherLines($amount, $debitAccountId, $creditAccountId, $buildingId, $unitId): array` — two arrays with keys `account_id, building_id, unit_id, jv_desc, debit_amt, credit_amt, recovery, dim1able_type, dim1able_id, dim2able_type, dim2able_id`.
  - Constants `DIVISION_DIM_ID = 2`, `DIM1_TYPE`, `DIM2_TYPE`.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\MunicipalityRegistrationCharges as C;

class MunicipalityRegistrationChargesTest extends TestCase
{
    public function test_money_parses_varchar_values()
    {
        $this->assertSame(3600.5, C::money('3,600.500'));
        $this->assertSame(1920.0, C::money('1920'));
        $this->assertSame(0.0, C::money(''));
        $this->assertSame(0.0, C::money(null));
        $this->assertSame(0.0, C::money('abc'));
        $this->assertSame(1.235, C::money('1.2345'));
    }

    public function test_default_tax_is_value_times_rate()
    {
        $this->assertSame(57.6, C::defaultTax('1920', 3));
        $this->assertSame(108.0, C::defaultTax('3,600.000', '3'));
        $this->assertSame(0.0, C::defaultTax('', 3));
        $this->assertSame(0.0, C::defaultTax('0', 3));
        $this->assertSame(10.001, C::defaultTax('333.367', 3));
    }

    public function test_same_amount_ignores_formatting()
    {
        $this->assertTrue(C::sameAmount('108', '108.000'));
        $this->assertTrue(C::sameAmount(108.0, '108.0004'));
        $this->assertFalse(C::sameAmount('108', '108.01'));
    }

    public function test_description_drops_blanks()
    {
        $this->assertSame('M.TAX TAG2301963 Teeb Complex S 03', C::description('M.TAX', 'TAG2301963', 'Teeb Complex', 'S 03'));
        $this->assertSame('M.TAX PENALTY TAG1', C::description('M.TAX PENALTY', 'TAG1', '', null));
    }

    public function test_voucher_lines_balance_and_carry_dims()
    {
        $lines = C::voucherLines(58.6, 747, 1, 250, 5300);
        $this->assertCount(2, $lines);

        $this->assertSame(747, $lines[0]['account_id']);
        $this->assertSame(58.6, $lines[0]['debit_amt']);
        $this->assertSame(0.0, $lines[0]['credit_amt']);

        $this->assertSame(1, $lines[1]['account_id']);
        $this->assertSame(0.0, $lines[1]['debit_amt']);
        $this->assertSame(58.6, $lines[1]['credit_amt']);

        foreach ($lines as $l) {
            $this->assertSame(250, $l['building_id']);
            $this->assertSame(5300, $l['unit_id']);
            $this->assertSame(C::DIVISION_DIM_ID, $l['dim1able_id']);
            $this->assertSame('Modules\BackOffice\Entities\DimDetail', $l['dim1able_type']);
            $this->assertSame('Modules\Masters\Entities\Building', $l['dim2able_type']);
            $this->assertSame(250, $l['dim2able_id']);
            $this->assertSame(0, $l['recovery']);
            $this->assertNull($l['jv_desc']);
        }
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/MunicipalityRegistrationChargesTest.php`
Expected: 5 errors, `Class 'Modules\BackOffice\Services\MunicipalityRegistrationCharges' not found`.

- [ ] **Step 3: Implement**

```php
<?php

namespace Modules\BackOffice\Services;

/**
 * Arithmetic and General Ledger line shapes for the municipal tax recorded
 * when a tenant contract is registered with the municipality. Pure: no DB.
 * Runs on PHP < 7.4 in production: no arrow functions.
 */
class MunicipalityRegistrationCharges
{
    /** DimDetail id for the PLMS division (manual M.TAX vouchers use it). */
    const DIVISION_DIM_ID = 2;
    const DIM1_TYPE = 'Modules\BackOffice\Entities\DimDetail';
    const DIM2_TYPE = 'Modules\Masters\Entities\Building';

    /** "3,600.500" -> 3600.5; blank / non-numeric -> 0. */
    public static function money($value)
    {
        $clean = str_replace(',', '', trim((string) $value));
        return is_numeric($clean) ? round((float) $clean, 3) : 0.0;
    }

    public static function defaultTax($contractValue, $rate)
    {
        return round(self::money($contractValue) * (float) $rate / 100, 3);
    }

    public static function sameAmount($a, $b)
    {
        return abs(self::money($a) - self::money($b)) < 0.0005;
    }

    public static function description($prefix, $contractNo, $buildingName, $unitNo)
    {
        $parts = [];
        foreach ([$prefix, $contractNo, $buildingName, $unitNo] as $p) {
            $p = trim((string) $p);
            if ($p !== '') {
                $parts[] = $p;
            }
        }
        return implode(' ', $parts);
    }

    /** Debit line then credit line, both on the contract's building and unit. */
    public static function voucherLines($amount, $debitAccountId, $creditAccountId, $buildingId, $unitId)
    {
        $amount = round((float) $amount, 3);
        $make = function ($accountId, $debit, $credit) use ($buildingId, $unitId) {
            return [
                'account_id'    => $accountId,
                'building_id'   => $buildingId,
                'unit_id'       => $unitId,
                'jv_desc'       => null,
                'debit_amt'     => $debit,
                'credit_amt'    => $credit,
                'recovery'      => 0,
                'dim1able_type' => self::DIM1_TYPE,
                'dim1able_id'   => self::DIVISION_DIM_ID,
                'dim2able_type' => self::DIM2_TYPE,
                'dim2able_id'   => $buildingId,
            ];
        };
        return [
            $make($debitAccountId, $amount, 0.0),
            $make($creditAccountId, 0.0, $amount),
        ];
    }
}
```

- [ ] **Step 4: Run to verify it passes**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit/MunicipalityRegistrationChargesTest.php`
Expected: `OK (5 tests, ...)`.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/MunicipalityRegistrationCharges.php tests/Unit/MunicipalityRegistrationChargesTest.php
git commit -m "feat(municipality): registration charge calculator and voucher lines"
```

---

### Task 3: DB service `MunicipalityRegistrationLedger`

**Files:**
- Create: `Modules/BackOffice/Exceptions/MunicipalityLedgerException.php`
- Create: `Modules/BackOffice/Services/MunicipalityRegistrationLedger.php`
- Verify with a scratch script outside the repo (rolled-back transaction)

**Interfaces:**
- Consumes: `MunicipalityRegistrationCharges::{money, defaultTax, sameAmount, description, voucherLines}` (Task 2); columns and settings (Task 1).
- Produces:
  - `new MunicipalityRegistrationLedger()`
  - `->settings(): array` keys `rate, processing, tax_account, penalty_account, payment_account`
  - `->modalData($contract): array` keys `contract_value, rate, processing, tax, penalty, tax_voucher, penalty_voucher, tax_locked, penalty_locked` (`*_voucher` = `null` or `['voucher_no' => string, 'status' => string, 'posted' => bool]`)
  - `->sync($contract, $taxInput, $penaltyInput, $docDate, $userId, $canApprove): array` keys `tax_amt, processing_amt, penalty_amt, tax_gl_id, penalty_gl_id` — caller must be inside `DB::transaction` with the contract row locked; throws `MunicipalityLedgerException`.
  - `Modules\BackOffice\Exceptions\MunicipalityLedgerException extends \RuntimeException`

- [ ] **Step 1: Exception class**

```php
<?php

namespace Modules\BackOffice\Exceptions;

/** A municipality registration save that must stop with a message for the user. */
class MunicipalityLedgerException extends \RuntimeException
{
}
```

- [ ] **Step 2: Write the service**

```php
<?php

namespace Modules\BackOffice\Services;

use App\Setting;
use Carbon\Carbon;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\GeneralLedger;
use Modules\BackOffice\Exceptions\MunicipalityLedgerException;
use Modules\BackOffice\Services\MunicipalityRegistrationCharges as Charges;

/**
 * Creates, updates and removes the General Ledger vouchers for the municipal
 * tax (tax + processing charge) and the municipality penalty of one tenant
 * contract. Vouchers already posted to AX are never changed. Finance approves
 * and posts them from the General Ledger screen. Call sync() inside a DB
 * transaction with the contract row locked. PHP < 7.4: no arrow functions.
 */
class MunicipalityRegistrationLedger
{
    const POSTED = 3;

    private $defaults = [
        'municipal_tax_percentage'       => '3',
        'municipality_processing_charge' => '1',
        'municipal_tax_account'          => '41102',
        'municipal_tax_penalty_account'  => '41103',
        'municipality_payment_account'   => '12674',
    ];

    public function settings()
    {
        return [
            'rate'            => (float) $this->setting('municipal_tax_percentage'),
            'processing'      => Charges::money($this->setting('municipality_processing_charge')),
            'tax_account'     => $this->setting('municipal_tax_account'),
            'penalty_account' => $this->setting('municipal_tax_penalty_account'),
            'payment_account' => $this->setting('municipality_payment_account'),
        ];
    }

    public function modalData($contract)
    {
        $s = $this->settings();
        $taxGl = $this->voucher($contract->tenant_contract_municipality_gl_id);
        $penGl = $this->voucher($contract->tenant_contract_municipality_penalty_gl_id);
        $taxPosted = $taxGl && (int) $taxGl->general_ledger_status === self::POSTED;

        return [
            'contract_value'  => Charges::money($contract->tenant_contract_value),
            'rate'            => $s['rate'],
            'processing'      => $taxPosted && $contract->tenant_contract_municipality_processing_amt !== null
                                    ? Charges::money($contract->tenant_contract_municipality_processing_amt) : $s['processing'],
            'tax'             => $contract->tenant_contract_municipality_tax_amt !== null
                                    ? Charges::money($contract->tenant_contract_municipality_tax_amt)
                                    : Charges::defaultTax($contract->tenant_contract_value, $s['rate']),
            'penalty'         => Charges::money($contract->tenant_contract_municipality_penalty_amt),
            'tax_voucher'     => $this->voucherInfo($taxGl),
            'penalty_voucher' => $this->voucherInfo($penGl),
            'tax_locked'      => $taxPosted,
            'penalty_locked'  => $penGl && (int) $penGl->general_ledger_status === self::POSTED,
        ];
    }

    public function sync($contract, $taxInput, $penaltyInput, $docDate, $userId, $canApprove)
    {
        $s = $this->settings();
        $tax = Charges::money($taxInput);
        $penalty = Charges::money($penaltyInput);

        $taxGl = $this->voucher($contract->tenant_contract_municipality_gl_id);
        $penGl = $this->voucher($contract->tenant_contract_municipality_penalty_gl_id);

        // ---- tax + processing ------------------------------------------
        if ($taxGl && (int) $taxGl->general_ledger_status === self::POSTED) {
            if (!Charges::sameAmount($tax, $contract->tenant_contract_municipality_tax_amt)) {
                throw new MunicipalityLedgerException('Municipality tax voucher ' . $taxGl->voucher_no
                    . ' is already posted to AX; make a correcting entry in General Ledger.');
            }
            $tax = Charges::money($contract->tenant_contract_municipality_tax_amt);
            $processing = Charges::money($contract->tenant_contract_municipality_processing_amt);
            $taxGlId = $taxGl->id;
        } else {
            $processing = $s['processing'];
            $total = round($tax + $processing, 3);
            if ($total > 0) {
                $taxGl = $this->writeVoucher($taxGl, $total, 'M.TAX', $s['tax_account'], 'municipal_tax_account',
                    $s['payment_account'], $contract, $docDate, $userId, $canApprove);
                $taxGlId = $taxGl->id;
            } else {
                if ($taxGl) {
                    $taxGl->delete();
                }
                $taxGlId = null;
            }
        }

        // ---- penalty -------------------------------------------------
        if ($penGl && (int) $penGl->general_ledger_status === self::POSTED) {
            if (!Charges::sameAmount($penalty, $contract->tenant_contract_municipality_penalty_amt)) {
                throw new MunicipalityLedgerException('Municipality penalty voucher ' . $penGl->voucher_no
                    . ' is already posted to AX; make a correcting entry in General Ledger.');
            }
            $penalty = Charges::money($contract->tenant_contract_municipality_penalty_amt);
            $penGlId = $penGl->id;
        } elseif ($penalty > 0) {
            $penGl = $this->writeVoucher($penGl, $penalty, 'M.TAX PENALTY', $s['penalty_account'], 'municipal_tax_penalty_account',
                $s['payment_account'], $contract, $docDate, $userId, $canApprove);
            $penGlId = $penGl->id;
        } else {
            if ($penGl) {
                $penGl->delete();
            }
            $penGlId = null;
        }

        return [
            'tax_amt'        => $tax,
            'processing_amt' => $processing,
            'penalty_amt'    => $penalty,
            'tax_gl_id'      => $taxGlId,
            'penalty_gl_id'  => $penGlId,
        ];
    }

    // ---- helpers -------------------------------------------------------

    private function setting($key)
    {
        $row = prefixData($key);
        return $row && $row->configuration_value !== null && $row->configuration_value !== ''
            ? $row->configuration_value : $this->defaults[$key];
    }

    /** Live (not soft-deleted) voucher or null. */
    private function voucher($id)
    {
        return $id ? GeneralLedger::find($id) : null;
    }

    private function voucherInfo($gl)
    {
        if (!$gl) {
            return null;
        }
        $parts = explode('|', (string) $gl->general_ledger_status_name);
        return [
            'voucher_no' => $gl->voucher_no,
            'status'     => trim(isset($parts[1]) ? $parts[1] : ''),
            'posted'     => (int) $gl->general_ledger_status === self::POSTED,
        ];
    }

    private function account($value, $settingKey)
    {
        $acc = AccountCodes::where('acc_code_val', trim((string) $value))->first();
        if (!$acc) {
            throw new MunicipalityLedgerException('Account ' . $value . ' (General Settings: '
                . $settingKey . ') was not found in the chart of accounts.');
        }
        return $acc;
    }

    private function writeVoucher($gl, $amount, $prefix, $debitValue, $debitSetting, $creditValue, $contract, $docDate, $userId, $canApprove)
    {
        $debit = $this->account($debitValue, $debitSetting);
        $credit = $this->account($creditValue, 'municipality_payment_account');

        $building = $contract->building;
        $unit = $contract->Unit;
        $desc = Charges::description($prefix, $contract->tenant_contract_no,
            $building ? $building->building_name : '', $unit ? $unit->unit_no : '');

        $header = [
            'doc_date'            => Carbon::parse($docDate)->format('Y-m-d'),
            'jv_refer_no'         => $contract->tenant_contract_no,
            'general_ledger_desc' => $desc,
            'amount'              => $amount,
        ];

        if ($gl) {
            $gl->update($header);
            $gl->generalLedgerDim()->delete();
        } else {
            $gl = GeneralLedger::create($header + [
                'voucher_no'                     => $this->nextVoucherNo(),
                'general_ledger_type'            => 1,
                'bank_id'                        => null,
                'created_by'                     => $userId,
                'general_ledger_status'          => $canApprove ? 2 : 1,
                'general_ledger_approval_status' => $canApprove ? 4 : 0,
            ]);
        }

        $descByAccount = [$debit->id => $debit->acc_code_desc, $credit->id => $credit->acc_code_desc];
        foreach (Charges::voucherLines($amount, $debit->id, $credit->id, $contract->building_id, $contract->unit_id) as $line) {
            $gl->generalLedgerDim()->create($line + ['description' => $descByAccount[$line['account_id']]]);
        }
        return $gl;
    }

    /** Same format as GeneralLedgerController::generalLedgerCode(), counter row locked. */
    private function nextVoucherNo()
    {
        $row = Setting::where('configuration_settings', 'general_ledger_prefix')->lockForUpdate()->first();
        $inc = (int) $row->configuration_increment_value;
        $code = $row->configuration_value . $row->configuration_year . str_pad($inc, 5, '0', STR_PAD_LEFT);
        Setting::where('id', $row->id)->update(['configuration_increment_value' => $inc + 1]);
        return $code;
    }
}
```

- [ ] **Step 3: Write the verification script (scratch, NOT committed)**

Save as `<scratchpad>/municipality_ledger_check.php`:

```php
<?php
require 'C:/laragon/www/plms_master/vendor/autoload.php';
$app = require 'C:/laragon/www/plms_master/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\BackOffice\Services\MunicipalityRegistrationLedger;
use Modules\BackOffice\Exceptions\MunicipalityLedgerException;
use Modules\BackOffice\Entities\GeneralLedger;
use Modules\Sales\Entities\TenantContract;

function check($label, $ok) { echo ($ok ? 'PASS ' : 'FAIL ') . $label . "\n"; }

DB::beginTransaction();
try {
    $svc = new MunicipalityRegistrationLedger();
    $c = TenantContract::whereNotNull('building_id')->whereNotNull('unit_id')
        ->where('tenant_contract_value', '1920')->lockForUpdate()->first()
        ?: TenantContract::whereNotNull('building_id')->whereNotNull('unit_id')->lockForUpdate()->first();
    $c->tenant_contract_value = '1920';
    $c->tenant_contract_municipality_gl_id = null;
    $c->tenant_contract_municipality_penalty_gl_id = null;
    $before = DB::table('configuration')->where('configuration_settings', 'general_ledger_prefix')->value('configuration_increment_value');

    $apply = function ($r) use ($c) {
        $c->tenant_contract_municipality_tax_amt = $r['tax_amt'];
        $c->tenant_contract_municipality_processing_amt = $r['processing_amt'];
        $c->tenant_contract_municipality_penalty_amt = $r['penalty_amt'];
        $c->tenant_contract_municipality_gl_id = $r['tax_gl_id'];
        $c->tenant_contract_municipality_penalty_gl_id = $r['penalty_gl_id'];
    };

    // 1. create tax voucher only
    $r = $svc->sync($c, '57.6', '0', '2026-09-28', 1, true); $apply($r);
    $gl = GeneralLedger::find($r['tax_gl_id']);
    check('tax voucher created', $gl !== null && $r['penalty_gl_id'] === null);
    check('amount = tax + processing = 58.6', abs($gl->amount - 58.6) < 0.0005);
    $lines = $gl->generalLedgerDim()->get();
    check('2 balanced lines', $lines->count() === 2 && abs($lines->sum('debit_amt') - $lines->sum('credit_amt')) < 0.0005);
    check('debit 41102 / credit 12674', $lines->where('debit_amt', '>', 0)->first()->accountCode->acc_code_val == 41102
        && $lines->where('credit_amt', '>', 0)->first()->accountCode->acc_code_val == 12674);
    check('approved when user can approve', (int) $gl->general_ledger_status === 2 && (int) $gl->general_ledger_approval_status === 4);

    // 2. update unposted: same voucher, new amount; add penalty
    $r = $svc->sync($c, '60', '25', '2026-09-28', 1, true); $apply($r);
    check('tax voucher reused', $r['tax_gl_id'] === $gl->id && abs(GeneralLedger::find($gl->id)->amount - 61) < 0.0005);
    $pen = GeneralLedger::find($r['penalty_gl_id']);
    check('penalty voucher created on 41103', $pen && $pen->generalLedgerDim()->where('debit_amt', '>', 0)->first()->accountCode->acc_code_val == 41103);
    check('voucher numbers sequential', DB::table('configuration')->where('configuration_settings', 'general_ledger_prefix')->value('configuration_increment_value') == $before + 2);

    // 3. clear penalty -> soft-deleted
    $penId = $r['penalty_gl_id'];
    $r = $svc->sync($c, '60', '', '2026-09-28', 1, true); $apply($r);
    check('penalty voucher removed', $r['penalty_gl_id'] === null && GeneralLedger::find($penId) === null);

    // 4. posted: same amount (formatted differently) is allowed, change rejected
    GeneralLedger::where('id', $gl->id)->update(['general_ledger_status' => 3]);
    $r = $svc->sync($c, '60.000', '0', '2026-09-28', 1, true); $apply($r);
    check('posted + unchanged tax saves', $r['tax_gl_id'] === $gl->id && abs($r['processing_amt'] - 1) < 0.0005);
    try { $svc->sync($c, '61', '0', '2026-09-28', 1, true); check('posted + changed tax rejected', false); }
    catch (MunicipalityLedgerException $e) { check('posted + changed tax rejected', strpos($e->getMessage(), 'already posted') !== false); }

    // 5. unknown account setting
    DB::table('configuration')->where('configuration_settings', 'municipal_tax_penalty_account')->update(['configuration_value' => '99999']);
    try { $svc->sync($c, '60', '5', '2026-09-28', 1, true); check('unknown account rejected', false); }
    catch (MunicipalityLedgerException $e) { check('unknown account rejected', strpos($e->getMessage(), 'municipal_tax_penalty_account') !== false); }

    // 6. draft when user cannot approve
    $c->tenant_contract_municipality_gl_id = null;
    $r = $svc->sync($c, '10', '0', '2026-09-28', 1, false);
    $d = GeneralLedger::find($r['tax_gl_id']);
    check('draft when user cannot approve', (int) $d->general_ledger_status === 1 && (int) $d->general_ledger_approval_status === 0);

    // 7. modal data
    $m = $svc->modalData($c);
    check('modal default tax from value', abs((new MunicipalityRegistrationLedger())->modalData(tap(clone $c, function ($x) { $x->tenant_contract_municipality_tax_amt = null; }))['tax'] - 57.6) < 0.0005);
    check('modal shows voucher', $m['tax_voucher'] !== null && $m['tax_voucher']['voucher_no'] !== '');
} finally {
    DB::rollBack();
    echo "rolled back\n";
}
```

- [ ] **Step 4: Run it**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe <scratchpad>/municipality_ledger_check.php`
Expected: every line `PASS`, last line `rolled back`. Fix the service until all pass.

- [ ] **Step 5: Run the unit suite**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit`
Expected: `OK`.

- [ ] **Step 6: Commit**

```bash
git add Modules/BackOffice/Exceptions/MunicipalityLedgerException.php Modules/BackOffice/Services/MunicipalityRegistrationLedger.php
git commit -m "feat(municipality): create GL vouchers for municipal tax and penalty"
```

---

### Task 4: Controller wiring + agreement fix

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/TenantContractEditController.php` — `tenantContractAddMunicipality` (≈line 1746), `tenantContractAddMunicipalityStore` (≈1752), `tenantContractAddMunicipalityview` (≈1796)

**Interfaces:**
- Consumes: `MunicipalityRegistrationLedger::{modalData, sync}`, `MunicipalityLedgerException` (Task 3).
- Produces: view variable `$municipality` (the `modalData()` array) for both modal views; POST fields `tenant_contract_municipality_tax_amt`, `tenant_contract_municipality_penalty_amt`.

- [ ] **Step 1: Add imports at the top of the controller** (next to the other `use Modules\BackOffice\...` lines)

```php
use Modules\BackOffice\Services\MunicipalityRegistrationLedger;
use Modules\BackOffice\Exceptions\MunicipalityLedgerException;
```

- [ ] **Step 2: Pass modal data in both modal actions**

In `tenantContractAddMunicipality` and `tenantContractAddMunicipalityview`, after `$tenantContractInfo = ...`:

```php
    $municipality = (new MunicipalityRegistrationLedger())->modalData($tenantContractInfo);
```
and add `'municipality'` to the `compact(...)` of each `view(...)` call.

- [ ] **Step 3: Replace `tenantContractAddMunicipalityStore`**

```php
  public function tenantContractAddMunicipalityStore(Request $request){

    $this->validate($request, [
        'tenant_contract_muncipality_agr_no'      => 'required',
        'tenant_contract_registered_in'           => 'required',
        'tenant_contract_registered_date'         => 'required|date',
        'tenant_contract_municipality_tax_amt'    => 'required|numeric|min:0',
        'tenant_contract_municipality_penalty_amt'=> 'nullable|numeric|min:0',
        ]);

    $tenant_contract_id = $request->tenant_contract_id;

    // Upload the municipality agreement; only overwrite the stored one when a new file is sent.
    $agreement = null;
    $documents = $request->file('tenant_document_file_name');
    if(!empty($documents)) {
      foreach($documents as $document){
        if ($document) {
          $agreement = Storage::putFile('public/Document', $document);
        }
      }
    }

    try {
      DB::transaction(function () use ($request, $tenant_contract_id, $agreement) {
        $contract = TenantContract::where('id', $tenant_contract_id)->lockForUpdate()->firstOrFail();

        $charges = (new MunicipalityRegistrationLedger())->sync(
          $contract,
          $request->tenant_contract_municipality_tax_amt,
          $request->tenant_contract_municipality_penalty_amt,
          $request->tenant_contract_registered_date,
          \Auth::user()->id,
          \Auth::user()->can('ledger_approval')
        );

        $fields = [
          'tenant_contract_muncipality_agr_no'        => $request->tenant_contract_muncipality_agr_no,
          'tenant_contract_registered_in'             => $request->tenant_contract_registered_in,
          'tenant_contract_registered_date'           => $request->tenant_contract_registered_date,
          'tenant_contract_note'                      => $request->tenant_contract_note,
          'tenant_contract_is_reg_municipality'       => 1,
          'tenant_contract_guarantee_cheque_details'  => $request->tenant_contract_guarantee_cheque_details,
          'pdc_check'                                 => $request->pdc_check,
          'deposit_check'                             => $request->deposit_check,
          'tenant_contract_municipality_tax_amt'        => $charges['tax_amt'],
          'tenant_contract_municipality_processing_amt' => $charges['processing_amt'],
          'tenant_contract_municipality_penalty_amt'    => $charges['penalty_amt'],
          'tenant_contract_municipality_gl_id'          => $charges['tax_gl_id'],
          'tenant_contract_municipality_penalty_gl_id'  => $charges['penalty_gl_id'],
        ];
        if ($agreement !== null) {
          $fields['tenant_muncipality_agreement'] = $agreement;
        }
        TenantContract::where('id', $contract->id)->update($fields);
      });
    } catch (MunicipalityLedgerException $e) {
      return redirect()->back()->withInput()->with('error', $e->getMessage());
    }

      session()->flash('success', 'Tenant Contract Municipality Changed Successfully');
      return redirect()->route('tenant-contract.index');
    }
```

Check the controller already imports `DB` (`use Illuminate\Support\Facades\DB;` or `use DB;`) and `Storage`; add `use Illuminate\Support\Facades\DB;` if missing.

- [ ] **Step 4: Syntax check**

Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l Modules/BackOffice/Http/Controllers/TenantContractEditController.php`
Expected: `No syntax errors detected`.

- [ ] **Step 5: Validation check for missing registered date (Review Focus 4)**

Scratch script (not committed), run with the 7.4 PHP:

```php
<?php
require 'C:/laragon/www/plms_master/vendor/autoload.php';
$app = require 'C:/laragon/www/plms_master/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$v = Validator::make(
  ['tenant_contract_muncipality_agr_no' => 'A1', 'tenant_contract_registered_in' => 1,
   'tenant_contract_municipality_tax_amt' => '57.6', 'tenant_contract_municipality_penalty_amt' => ''],
  ['tenant_contract_muncipality_agr_no' => 'required', 'tenant_contract_registered_in' => 'required',
   'tenant_contract_registered_date' => 'required|date', 'tenant_contract_municipality_tax_amt' => 'required|numeric|min:0',
   'tenant_contract_municipality_penalty_amt' => 'nullable|numeric|min:0']);
echo $v->fails() && $v->errors()->has('tenant_contract_registered_date') && !$v->errors()->has('tenant_contract_municipality_penalty_amt')
  ? "PASS missing date rejected, blank penalty accepted\n" : "FAIL " . json_encode($v->errors()->toArray()) . "\n";
```
Expected: `PASS ...`.

- [ ] **Step 6: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/TenantContractEditController.php
git commit -m "feat(municipality): save charges and vouchers from the municipality modal; keep uploaded agreement on re-save"
```

---

### Task 5: Modal fields

**Files:**
- Create: `Modules/BackOffice/Resources/views/TenantContract/_municipality_charges.blade.php`
- Modify: `tenant_contract_municipality_modal.blade.php` and `tenant_contract_municipality_modal_view.blade.php` — include the partial right after the "Contract Registered Date" form-group (≈line 56 in each)

**Interfaces:**
- Consumes: `$municipality` (Task 4), POST field names from Task 4.

- [ ] **Step 1: Create the partial**

```blade
{{-- Municipal tax / processing / penalty. $municipality = MunicipalityRegistrationLedger::modalData(). --}}
@php
  $m = $municipality;
  $fmt = function ($v) { return number_format((float) $v, 3); };
  $rateLabel = rtrim(rtrim(number_format($m['rate'], 2, '.', ''), '0'), '.') . '%';
  $taxVal = old('tenant_contract_municipality_tax_amt', number_format($m['tax'], 3, '.', ''));
  $penVal = old('tenant_contract_municipality_penalty_amt', number_format($m['penalty'], 3, '.', ''));
@endphp
<div class="row municipality-charges" data-processing="{{ $m['processing'] }}">
  <div class="col-md-6 col-sm-12">
    <div class="form-group">
      <label for="tenant_contract_municipality_tax_amt">Municipality Tax Amount<small class="textRed">*</small></label>
      <input type="number" step="0.001" min="0" class="form-control mc-tax" id="tenant_contract_municipality_tax_amt"
             name="tenant_contract_municipality_tax_amt" value="{{ $taxVal }}" required {{ $m['tax_locked'] ? 'readonly' : '' }}>
      <small class="text-muted">Contract value {{ $fmt($m['contract_value']) }} &times; {{ $rateLabel }} = {{ $fmt($m['contract_value'] * $m['rate'] / 100) }}</small>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="form-group">
      <label>Processing Charge</label>
      <input type="text" class="form-control" value="{{ $fmt($m['processing']) }}" readonly>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="form-group">
      <label>Total (Tax + Processing)</label>
      <input type="text" class="form-control mc-total" value="{{ $fmt((float) $taxVal + $m['processing']) }}" readonly>
    </div>
  </div>
  <div class="col-md-6 col-sm-12">
    <div class="form-group">
      <label for="tenant_contract_municipality_penalty_amt">Penalty Amount</label>
      <input type="number" step="0.001" min="0" class="form-control" id="tenant_contract_municipality_penalty_amt"
             name="tenant_contract_municipality_penalty_amt" value="{{ $penVal }}" {{ $m['penalty_locked'] ? 'readonly' : '' }}>
    </div>
  </div>
  <div class="col-md-6 col-sm-12">
    @foreach (['Tax voucher' => $m['tax_voucher'], 'Penalty voucher' => $m['penalty_voucher']] as $label => $v)
      @if ($v)
        <div><strong>{{ $label }}:</strong> {{ $v['voucher_no'] }} &mdash; {{ $v['status'] }}@if($v['posted']) <span class="text-muted">(posted to AX, amount locked)</span>@endif</div>
      @endif
    @endforeach
  </div>
</div>
<script>
  $(document).on('input', '.municipality-charges .mc-tax', function () {
    var box = $(this).closest('.municipality-charges');
    var tax = parseFloat($(this).val()) || 0;
    var processing = parseFloat(box.data('processing')) || 0;
    box.find('.mc-total').val((tax + processing).toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3}));
  });
</script>
```

- [ ] **Step 2: Include it in both modals**

In each modal file, directly after the closing `</div>` of the form-group that contains `tenant_contract_registered_date`, add:

```blade
@include('backoffice::TenantContract._municipality_charges', ['municipality' => $municipality])
```

- [ ] **Step 3: Render check (scratch script, rolled back)**

```php
<?php
require 'C:/laragon/www/plms_master/vendor/autoload.php';
$app = require 'C:/laragon/www/plms_master/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = Modules\Sales\Entities\TenantContract::whereNotNull('building_id')->first();
$m = (new Modules\BackOffice\Services\MunicipalityRegistrationLedger())->modalData($c);
foreach (['tenant_contract_municipality_modal', 'tenant_contract_municipality_modal_view'] as $v) {
  $html = view('backoffice::TenantContract.' . $v, ['tenant_contract_id' => $c->id, 'tenantContractInfo' => $c, 'municipality' => $m])->render();
  echo (strpos($html, 'name="tenant_contract_municipality_tax_amt"') !== false && strpos($html, 'Processing Charge') !== false ? 'PASS ' : 'FAIL ') . $v . "\n";
}
```
Expected: `PASS` for both. (If the views need `$errors`, wrap with `view()->share('errors', new Illuminate\Support\ViewErrorBag);` before rendering.)

- [ ] **Step 4: Commit**

```bash
git add Modules/BackOffice/Resources/views/TenantContract/_municipality_charges.blade.php Modules/BackOffice/Resources/views/TenantContract/tenant_contract_municipality_modal.blade.php Modules/BackOffice/Resources/views/TenantContract/tenant_contract_municipality_modal_view.blade.php
git commit -m "feat(municipality): tax, processing charge and penalty fields in the municipality modal"
```

---

### Task 6: Browser check and wrap-up

- [ ] **Step 1: Browser — list page modal**
Open Tenant Contract list → Municipality on a contract with a contract value. Expect tax pre-filled (value × 3%), processing 1.000, total updates as the tax is edited. Save.

- [ ] **Step 2: General Ledger**
Open General Ledger list; the new AJV voucher shows reference = contract no, description "M.TAX …", lines 41102 debit / 12674 credit with the building. Do NOT press Post locally (it would call AX).

- [ ] **Step 3: Penalty + re-save**
Re-open the modal (view page), enter penalty 25, save → second voucher on 41103. Clear the penalty, save → penalty voucher gone. Re-save without a file → the agreement link on the contract still opens.

- [ ] **Step 4: Full unit suite**
Run: `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe vendor/bin/phpunit tests/Unit` → `OK`.

- [ ] **Step 5: Final commit if anything changed, then hand over for review/merge.**
