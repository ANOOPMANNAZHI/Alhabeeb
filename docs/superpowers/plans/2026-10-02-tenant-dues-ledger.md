# Tenant Dues Ledger Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** The Tenant Aging Report and the Receivables Follow-up both get what each tenant owes from one PHP calculation, so the amounts are correct and match.

**Architecture:** `RentLedger` (pure PHP) turns one contract into monthly rent lines, the total due and a FIFO allocation of receipts. `RentDues` runs one SQL query for the contracts plus receipt and termination totals, and builds a ledger for each. The Aging report replaces its `tenantrentreceivable*_v2` calls with `RentDues` and ages the unpaid lines by real days. `ReceivableFigures` (Follow-up) uses `RentDues` with dues up to the cutoff and receipts up to today.

**Tech Stack:** Laravel 5.7 (nwidart modules), PostgreSQL, PHPUnit 7.5, Carbon, maatwebsite/excel, FPDF.

**Spec:** `docs/superpowers/specs/2026-10-02-tenant-dues-ledger-design.md`

## Global Constraints

- Production runs PHP < 7.4: no arrow functions (`fn`), no typed properties, no nullsafe `?->`, no `match`.
- Run PHP, artisan and phpunit locally with `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe` (the default `php` is 8.4 and breaks Laravel 5.7). In Git Bash: `PHP=/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe`.
- Local DB is `plms_live` (live copy). Never run plain `php artisan migrate`. This plan has no migrations. Never touch `plms_new`.
- Laravel 5.7 cache TTLs are in **minutes**.
- Do not copy anything to `server_upload_files/` and do not merge to master until the user explicitly asks.
- Never delete anything from master without listing it for the user. This plan replaces `ReceivableFigures::dueAsOf()` / `build()` and the month-based `TenantAgingCalculator::age()` (listed in Tasks 2 and 5).
- `git add` paths are case sensitive (`Resources/views/Reports`, not `reports`). Run `git status` after each commit.
- End every commit message with `Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>`.

## Review Focus

1. **Contract with no start date** (5 approved contracts): a termination falls back to the effective date for its day count and must not crash. Test in Task 1.
2. **Termination date before the effective date** (bad data, e.g. TAG2200800): due must be 0, not a large positive amount. Test in Task 1.
3. **Overpaid contract:** balance 0, no "not paid from", and it must not appear in either report. Tests in Tasks 1, 2 and 5.
4. **Effective date on the 29th–31st:** each rent date stays on the same day where the month has it (31/01 → 28/02 → 31/03), with no drift. Test in Task 1.
5. **Receipt or termination dated after the as-on date** must not count. Tests in Tasks 1 and 3.

---

### Task 0: Branch

**Files:** none changed.

- [ ] **Step 1: Create the branch from master and bring in the unmerged Follow-up fix**

The Follow-up "match v2" work (never-paid contracts shown, balances under 30 hidden, half-even rounding) is on `fix/receivable-followup-match-v2` (193406bd). This plan builds on it.

```bash
cd /c/laragon/www/plms_master
git switch -c feature/tenant-dues-ledger master
git merge --no-ff fix/receivable-followup-match-v2 -m "Merge fix/receivable-followup-match-v2 into feature/tenant-dues-ledger

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
git diff --stat master HEAD
```
Expected: a clean merge. Only these 5 files change: `ReceivableFollowupController.php`, `_rows.blade.php`, `ReceivableFigures.php`, `tests/Feature/ReceivableFollowupTest.php`, `tests/Unit/ReceivableFiguresTest.php`. No deletions.

- [ ] **Step 2: Baseline tests**

```bash
PHP=/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe
$PHP vendor/bin/phpunit tests/Unit/ReceivableFiguresTest.php tests/Unit/TenantAgingCalculatorTest.php tests/Feature/ReceivableFollowupTest.php tests/Feature/ReceivableFollowupRefreshTest.php
```
Expected: all pass. If anything fails here, stop and report: it is not caused by this plan.

---

### Task 1: RentLedger — what one contract owes

**Files:**
- Create: `Modules/BackOffice/Services/RentLedger.php`
- Test: `tests/Unit/RentLedgerTest.php`

**Interfaces:**
- Produces: `RentLedger::build(object $c, string $asOn, float|string|null $received): array`
  - `$c` needs `eff`, `start`, `vto`, `rent`, `value`, `term_date` (`Y-m-d` strings or null; `rent`/`value` numeric strings or numbers). `term_date` = latest 505 termination date, or null.
  - Returns `['due' => float, 'received' => float, 'balance' => float, 'not_paid_from' => ?string, 'lines' => [['due_date' => 'Y-m-d', 'amount' => float, 'unpaid' => float], ...]]`. Lines are oldest first and add up to `due`. `balance` = sum of `unpaid` = max(0, due − received).

- [ ] **Step 1: Write the failing tests**

`tests/Unit/RentLedgerTest.php`:
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\RentLedger as L;

class RentLedgerTest extends TestCase
{
    private function c(array $o = [])
    {
        return (object) array_merge([
            'eff' => '2026-01-01', 'start' => '2026-01-01', 'vto' => '2026-12-31',
            'rent' => '100', 'value' => '1200', 'term_date' => null,
        ], $o);
    }

    private function amounts(array $l)
    {
        return array_column($l['lines'], 'amount');
    }

    private function dates(array $l)
    {
        return array_column($l['lines'], 'due_date');
    }

    public function test_running_contract_owes_one_rent_per_period_started()
    {
        $l = L::build($this->c(), '2026-08-31', 0);
        $this->assertSame(800.0, $l['due']);
        $this->assertSame(800.0, $l['balance']);
        $this->assertSame('2026-01-01', $l['not_paid_from']);
        $this->assertSame(['2026-01-01', '2026-02-01', '2026-03-01', '2026-04-01', '2026-05-01', '2026-06-01', '2026-07-01', '2026-08-01'], $this->dates($l));
    }

    public function test_mid_month_start_is_due_on_the_same_day_each_month()
    {
        $c = $this->c(['eff' => '2026-06-15', 'start' => '2026-06-15', 'vto' => '2027-06-14']);
        $this->assertSame(300.0, L::build($c, '2026-08-31', 0)['due']);
        // 15/08 not reached yet on 10/08
        $this->assertSame(200.0, L::build($c, '2026-08-10', 0)['due']);
    }

    public function test_month_end_start_does_not_drift()
    {
        $c = $this->c(['eff' => '2026-01-31', 'start' => '2026-01-31', 'vto' => '2027-01-30']);
        $this->assertSame(['2026-01-31', '2026-02-28', '2026-03-31', '2026-04-30'], $this->dates(L::build($c, '2026-04-30', 0)));
    }

    public function test_running_contract_never_owes_more_than_its_value()
    {
        $l = L::build($this->c(['value' => '250']), '2026-08-31', 0);
        $this->assertSame(250.0, $l['due']);
        $this->assertSame([100.0, 100.0, 50.0], $this->amounts($l));
    }

    public function test_ended_contract_owes_its_value_and_nothing_after_the_end()
    {
        // 5 months x 100 = 500 but the agreed value is 550
        $l = L::build($this->c(['vto' => '2026-05-31', 'value' => '550']), '2026-08-31', 0);
        $this->assertSame(550.0, $l['due']);
        $this->assertSame([100.0, 100.0, 100.0, 100.0, 150.0], $this->amounts($l));
    }

    public function test_ended_contract_without_a_value_owes_its_periods()
    {
        $this->assertSame(500.0, L::build($this->c(['vto' => '2026-05-31', 'value' => '0']), '2026-08-31', 0)['due']);
    }

    public function test_terminated_on_the_end_date_owes_the_value()
    {
        $l = L::build($this->c(['vto' => '2026-06-30', 'term_date' => '2026-06-30']), '2026-08-31', 0);
        $this->assertSame(1200.0, $l['due']);
        $this->assertSame(1200.0, array_sum($this->amounts($l)));
    }

    public function test_terminated_early_owes_days_times_rent_over_30()
    {
        // 31/05 - 01/01 = 150 days, minus 1 = 149; 149 x 100 / 30 = 496.67 -> 497
        $l = L::build($this->c(['term_date' => '2026-05-31']), '2026-08-31', 0);
        $this->assertSame(497.0, $l['due']);
        $this->assertSame([100.0, 100.0, 100.0, 100.0, 97.0], $this->amounts($l));
    }

    /** TAG2000072: 333 days x 435 / 30 = 4,828.5 -> 4,828 (half to even, as v2). */
    public function test_terminated_rounds_half_to_even()
    {
        $c = $this->c(['eff' => '2020-02-01', 'start' => '2020-02-01', 'vto' => '2021-01-31', 'rent' => '435', 'value' => '5220', 'term_date' => '2020-12-31']);
        $this->assertSame(4828.0, L::build($c, '2026-08-31', 0)['due']);
    }

    public function test_termination_after_the_as_on_date_is_not_counted_yet()
    {
        $this->assertSame(800.0, L::build($this->c(['term_date' => '2026-09-15']), '2026-08-31', 0)['due']);
    }

    public function test_termination_before_the_start_owes_nothing()
    {
        $c = $this->c(['eff' => '2022-04-01', 'start' => '2022-04-01', 'vto' => '2022-12-31', 'term_date' => '2021-03-31']);
        $l = L::build($c, '2026-08-31', 0);
        $this->assertSame(0.0, $l['due']);
        $this->assertSame(0.0, $l['balance']);
        $this->assertSame([], $l['lines']);
        $this->assertNull($l['not_paid_from']);
    }

    public function test_missing_start_date_uses_the_effective_date()
    {
        $this->assertSame(497.0, L::build($this->c(['start' => null, 'term_date' => '2026-05-31']), '2026-08-31', 0)['due']);
    }

    public function test_not_started_by_the_as_on_date_owes_nothing()
    {
        $l = L::build($this->c(['eff' => '2026-09-01']), '2026-08-31', 0);
        $this->assertSame(0.0, $l['due']);
        $this->assertSame([], $l['lines']);
    }

    public function test_receipts_clear_the_oldest_months_first()
    {
        $l = L::build($this->c(), '2026-08-31', '250');
        $this->assertSame(550.0, $l['balance']);
        $this->assertSame('2026-03-01', $l['not_paid_from']);
        $this->assertSame([0.0, 0.0, 50.0, 100.0, 100.0, 100.0, 100.0, 100.0], array_column($l['lines'], 'unpaid'));
    }

    public function test_overpaid_contract_owes_nothing()
    {
        $l = L::build($this->c(), '2026-08-31', 900);
        $this->assertSame(0.0, $l['balance']);
        $this->assertNull($l['not_paid_from']);
        $this->assertSame(900.0, $l['received']);
    }

    public function test_lines_always_add_up_to_the_due_amount()
    {
        // Bustan-171 TAG2502870: value 2,096.667, rent 170
        $c = $this->c(['eff' => '2025-05-22', 'start' => '2025-05-22', 'vto' => '2026-05-31', 'rent' => '170', 'value' => '2096.667']);
        $l = L::build($c, '2026-08-31', 0);
        $this->assertSame(2096.667, $l['due']);
        $this->assertEquals(2096.667, array_sum($this->amounts($l)), '', 0.0005);
    }
}
```

- [ ] **Step 2: Run them to make sure they fail**

Run: `$PHP vendor/bin/phpunit tests/Unit/RentLedgerTest.php`
Expected: errors, `Class 'Modules\BackOffice\Services\RentLedger' not found`.

- [ ] **Step 3: Write the implementation**

`Modules/BackOffice/Services/RentLedger.php`:
```php
<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;

/**
 * Pure (no DB). What one tenant contract owes on a date, as monthly rent lines.
 *
 * Lines: one per rent period, due on the day the period starts (the effective
 * date, then the same day each month), for periods starting on or before the
 * as-on date and on or before the contract end, or the termination date when
 * terminated (505) on or before the as-on date.
 * Total due:
 *  - terminated by the as-on date: the contract value when terminated on the end
 *    date, else days x rent / 30 (days = termination - start - 1, half to even,
 *    as Receivable Report v2); a termination before the start owes nothing;
 *  - ended by the as-on date, not terminated: the contract value (rent stops at
 *    the end; a tenant who stays on is taken over manually);
 *  - still running: lines x rent, never above the contract value.
 * Lines are trimmed from the latest, or the last one topped up, to add up to the
 * total. Receipts clear the oldest lines first. See docs/superpowers/specs/
 * 2026-10-02-tenant-dues-ledger-design.md. PHP < 7.4 in production.
 */
class RentLedger
{
    const MAX_LINES = 600;

    public static function build($c, $asOn, $received)
    {
        $asOn = substr((string) $asOn, 0, 10);
        $eff = substr((string) $c->eff, 0, 10);
        $received = round((float) $received, 3);
        if ($eff === '' || $eff > $asOn) {
            return self::allocate([], 0.0, $received);
        }
        $rent = (float) $c->rent;
        $value = (float) $c->value;
        $vto = !empty($c->vto) ? substr((string) $c->vto, 0, 10) : null;
        $term = !empty($c->term_date) ? substr((string) $c->term_date, 0, 10) : null;
        if ($term !== null && $term > $asOn) {
            $term = null; // terminated after the as-on date: not yet
        }
        $end = $term !== null ? $term : $vto;

        $lines = [];
        $first = Carbon::parse($eff)->startOfDay();
        for ($i = 0; $i < self::MAX_LINES; $i++) {
            $d = $first->copy()->addMonthsNoOverflow($i)->format('Y-m-d');
            if ($d > $asOn || ($end !== null && $d > $end)) {
                break;
            }
            $lines[] = ['due_date' => $d, 'amount' => $rent];
        }
        $sum = $rent * count($lines);

        if ($term !== null) {
            if ($term === $vto) {
                $total = $value > 0 ? $value : $sum;
            } else {
                $start = Carbon::parse(!empty($c->start) ? $c->start : $eff)->startOfDay();
                $days = $start->diffInDays(Carbon::parse($term)->startOfDay(), false) - 1;
                $total = $days > 0 ? (float) round($days * ($rent / 30), 0, PHP_ROUND_HALF_EVEN) : 0.0;
            }
        } elseif ($vto !== null && $vto <= $asOn) {
            $total = $value > 0 ? $value : $sum;
        } else {
            $total = $value > 0 ? min($sum, $value) : $sum;
        }
        $total = round($total, 3);

        return self::allocate(self::fit($lines, $total, $eff), $total, $received);
    }

    /** Trim from the latest line, or top up the last one, so the lines add up to $total. */
    private static function fit(array $lines, $total, $eff)
    {
        if ($total <= 0) {
            return [];
        }
        if (!$lines) {
            $lines[] = ['due_date' => $eff, 'amount' => 0.0];
        }
        $sum = 0.0;
        foreach ($lines as $l) {
            $sum += $l['amount'];
        }
        if ($sum < $total) {
            $lines[count($lines) - 1]['amount'] += $total - $sum;
        } else {
            $over = $sum - $total;
            for ($i = count($lines) - 1; $i >= 0 && $over > 0; $i--) {
                $take = min($lines[$i]['amount'], $over);
                $lines[$i]['amount'] -= $take;
                $over -= $take;
            }
        }
        $out = [];
        $sum = 0.0;
        foreach ($lines as $l) {
            $a = round($l['amount'], 3);
            if ($a > 0) {
                $out[] = ['due_date' => $l['due_date'], 'amount' => $a];
                $sum += $a;
            }
        }
        // rounding leftovers go on the last line
        $diff = round($total - $sum, 3);
        if ($diff != 0 && $out) {
            $out[count($out) - 1]['amount'] = round($out[count($out) - 1]['amount'] + $diff, 3);
        }
        return $out;
    }

    /** Receipts clear the oldest lines first. */
    private static function allocate(array $lines, $total, $received)
    {
        $left = max(0.0, $received);
        $notPaidFrom = null;
        foreach ($lines as $k => $l) {
            $paid = min($l['amount'], $left);
            $left = round($left - $paid, 3);
            $lines[$k]['unpaid'] = round($l['amount'] - $paid, 3);
            if ($notPaidFrom === null && $lines[$k]['unpaid'] > 0) {
                $notPaidFrom = $l['due_date'];
            }
        }
        return [
            'due'           => $total,
            'received'      => $received,
            'balance'       => round(max(0.0, $total - $received), 3),
            'not_paid_from' => $notPaidFrom,
            'lines'         => $lines,
        ];
    }
}
```

- [ ] **Step 4: Run the tests and make sure they pass**

Run: `$PHP vendor/bin/phpunit tests/Unit/RentLedgerTest.php`
Expected: `OK (16 tests, ...)`.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/RentLedger.php tests/Unit/RentLedgerTest.php
git commit -m "feat(tenant-dues): RentLedger — monthly rent lines, total due and FIFO receipts for one contract

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
git status --short
```

---

### Task 2: Aging buckets by real days overdue

Replaces the month-based `TenantAgingCalculator::age($row, $asOn)` (which guessed from `lastpaid_till`) with day-based aging of the ledger's unpaid lines. `buckets()` keeps its keys and return shape, so the PDF/Excel code is unchanged.

**Files:**
- Modify (rewrite): `Modules/BackOffice/Services/TenantAgingCalculator.php`
- Modify (rewrite): `tests/Unit/TenantAgingCalculatorTest.php`

**Interfaces:**
- Consumes: `RentLedger::build()` result (`lines` with `due_date`, `unpaid`; `balance`).
- Produces:
  - `TenantAgingCalculator::buckets(string $asOn): array` key ⇒ `['label', 'from' => ?Y-m-d, 'to' => Y-m-d]`. Keys: `current, b31_60, b61_90, b91_120, b121_180, b180plus`.
  - `TenantAgingCalculator::age(array $lines, string $asOn): array` key ⇒ float (3 dp), summing to the unpaid total.
  - `TenantAgingCalculator::rows(array $dues, string $asOn): object[]` where each `$dues` item has `building_name, tenant_name, contract_no, unit_code, pdc, ledger`. Returns objects `{buildingname, tenant_name, contract_no, unit_code, pdc, netamtdue, aging}` for balances > 0, sorted by building, unit and contract. These are the field names the PDF and `tenant_aging_excel.blade.php` already use.

- [ ] **Step 1: Write the failing tests (replace the whole file)**

`tests/Unit/TenantAgingCalculatorTest.php`:
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\TenantAgingCalculator as A;

class TenantAgingCalculatorTest extends TestCase
{
    public function test_buckets_are_day_windows_back_from_the_as_on_date()
    {
        $b = A::buckets('2026-08-31');
        $this->assertSame(['current', 'b31_60', 'b61_90', 'b91_120', 'b121_180', 'b180plus'], array_keys($b));
        $this->assertSame(['label' => '0 - 30', 'from' => '2026-08-01', 'to' => '2026-08-31'], $b['current']);
        $this->assertSame(['label' => '31 - 60', 'from' => '2026-07-02', 'to' => '2026-07-31'], $b['b31_60']);
        $this->assertSame(['label' => '180 +', 'from' => null, 'to' => '2026-03-03'], $b['b180plus']);
    }

    public function test_unpaid_lines_age_by_days_since_they_fell_due()
    {
        $lines = [
            ['due_date' => '2026-01-01', 'amount' => 100.0, 'unpaid' => 100.0], // 242 days
            ['due_date' => '2026-07-31', 'amount' => 100.0, 'unpaid' => 100.0], // 31 days
            ['due_date' => '2026-08-01', 'amount' => 100.0, 'unpaid' => 40.0],  // 30 days
            ['due_date' => '2026-08-15', 'amount' => 100.0, 'unpaid' => 0.0],   // paid
        ];
        $a = A::age($lines, '2026-08-31');
        $this->assertSame(['current' => 40.0, 'b31_60' => 100.0, 'b61_90' => 0.0, 'b91_120' => 0.0, 'b121_180' => 0.0, 'b180plus' => 100.0], $a);
    }

    public function test_rows_keep_owing_contracts_sorted_by_building_and_unit()
    {
        $d = function ($bld, $unit, $no, $balance) {
            return (object) ['building_name' => $bld, 'tenant_name' => 'T ' . $no, 'contract_no' => $no, 'unit_code' => $unit, 'pdc' => 'Full',
                'ledger' => ['balance' => $balance, 'lines' => $balance > 0 ? [['due_date' => '2026-08-01', 'amount' => $balance, 'unpaid' => $balance]] : []]];
        };
        $rows = A::rows([$d('B Bldg', '2', 'TAG3', 50.0), $d('A Bldg', '9', 'TAG1', 10.0), $d('A Bldg', '1', 'TAG2', 0.0)], '2026-08-31');
        $this->assertSame(['TAG1', 'TAG3'], array_column($rows, 'contract_no'));
        $this->assertSame('A Bldg', $rows[0]->buildingname);
        $this->assertSame(10.0, $rows[0]->netamtdue);
        $this->assertSame(10.0, $rows[0]->aging['current']);
        $this->assertSame('Full', $rows[0]->pdc);
    }
}
```

- [ ] **Step 2: Run them to make sure they fail**

Run: `$PHP vendor/bin/phpunit tests/Unit/TenantAgingCalculatorTest.php`
Expected: FAIL. The labels differ (`Current`), and `age()` gets the wrong argument type or `rows()` is undefined.

- [ ] **Step 3: Write the implementation (replace the whole file)**

`Modules/BackOffice/Services/TenantAgingCalculator.php`:
```php
<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;

/**
 * Tenant Aging Report: how long each contract's unpaid rent has been due.
 * Works on RentLedger lines: receipts have already cleared the oldest lines,
 * so every unpaid line is aged by the days since its due date (as-on date -
 * due date) into fixed day windows. The buckets always add up to the balance.
 * Pure PHP (no DB); PHP < 7.4 in production: no arrow functions.
 */
class TenantAgingCalculator
{
    /** key => [label, min days overdue, max days overdue (null = open ended)] */
    const BUCKETS = [
        'current'  => ['0 - 30',    0,   30],
        'b31_60'   => ['31 - 60',   31,  60],
        'b61_90'   => ['61 - 90',   61,  90],
        'b91_120'  => ['91 - 120',  91,  120],
        'b121_180' => ['121 - 180', 121, 180],
        'b180plus' => ['180 +',     181, null],
    ];

    /** @return array key => ['label', 'from' (Y-m-d|null), 'to' (Y-m-d)] */
    public static function buckets($asOn)
    {
        $asOn = Carbon::parse($asOn)->startOfDay();
        $out = [];
        foreach (self::BUCKETS as $key => $def) {
            $out[$key] = [
                'label' => $def[0],
                'from'  => $def[2] === null ? null : $asOn->copy()->subDays($def[2])->toDateString(),
                'to'    => $asOn->copy()->subDays($def[1])->toDateString(),
            ];
        }
        return $out;
    }

    /**
     * @param  array  $lines RentLedger lines (due_date, amount, unpaid)
     * @return array  bucket key => unpaid amount (3 dp)
     */
    public static function age(array $lines, $asOn)
    {
        $asOn = Carbon::parse($asOn)->startOfDay();
        $amounts = array_fill_keys(array_keys(self::BUCKETS), 0.0);
        foreach ($lines as $l) {
            if ($l['unpaid'] <= 0) {
                continue;
            }
            $days = Carbon::parse($l['due_date'])->startOfDay()->diffInDays($asOn, false);
            $amounts[self::bucketFor(max(0, $days))] += $l['unpaid'];
        }
        foreach ($amounts as $k => $v) {
            $amounts[$k] = round($v, 3);
        }
        return $amounts;
    }

    public static function bucketFor($days)
    {
        foreach (self::BUCKETS as $key => $def) {
            if ($days >= $def[1] && ($def[2] === null || $days <= $def[2])) {
                return $key;
            }
        }
        return 'b180plus';
    }

    /**
     * Report rows from RentDues::forDate(): every contract with a balance, sorted by
     * building, unit and contract, with the field names the PDF and Excel use.
     */
    public static function rows(array $dues, $asOn)
    {
        $rows = [];
        foreach ($dues as $d) {
            if ($d->ledger['balance'] <= 0) {
                continue;
            }
            $rows[] = (object) [
                'buildingname' => $d->building_name,
                'tenant_name'  => $d->tenant_name,
                'contract_no'  => $d->contract_no,
                'unit_code'    => $d->unit_code,
                'pdc'          => $d->pdc,
                'netamtdue'    => $d->ledger['balance'],
                'aging'        => self::age($d->ledger['lines'], $asOn),
            ];
        }
        usort($rows, function ($a, $b) {
            $cmp = strcmp((string) $a->buildingname, (string) $b->buildingname);
            if ($cmp === 0) {
                $cmp = strnatcasecmp((string) $a->unit_code, (string) $b->unit_code);
            }
            return $cmp !== 0 ? $cmp : strcmp((string) $a->contract_no, (string) $b->contract_no);
        });
        return $rows;
    }
}
```

- [ ] **Step 4: Run the tests and make sure they pass**

Run: `$PHP vendor/bin/phpunit tests/Unit/TenantAgingCalculatorTest.php`
Expected: `OK (3 tests, ...)`.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Services/TenantAgingCalculator.php tests/Unit/TenantAgingCalculatorTest.php
git commit -m "feat(tenant-aging): age unpaid rent lines by real days overdue

Replaces the month-based guess from paid-till. Buckets 0-30 .. 180+ by days
since each unpaid line fell due; they always add up to the balance.

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
git status --short
```

---

### Task 3: RentDues — every contract's ledger on a date

**Files:**
- Create: `Modules/BackOffice/Services/RentDues.php`
- Test: `tests/Feature/RentDuesTest.php` (read-only against the local `plms_live` data, like the existing Follow-up feature tests)

**Interfaces:**
- Consumes: `RentLedger::build($c, $asOn, $received)`.
- Produces: `(new RentDues)->forDate(string $asOn, string $receiptsUpTo, array $filters = []): array`, keyed by contract id. Each value is an object with `id, contract_no, tenant_name, building_name, building_no, unit_code, eff, start, vto, rent, value, pdc ('Full'|'Partial'|''), term_date, received, paid_till, last_received, last_amount, ledger`. `$filters` are optional exact matches: `building_name, building_no, tenant_name, management_type, are`.

- [ ] **Step 1: Write the failing tests**

`tests/Feature/RentDuesTest.php`:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\RentDues;

class RentDuesTest extends TestCase
{
    public function test_only_approved_contracts_in_active_buildings_without_legal_cases()
    {
        $ids = array_keys((new RentDues)->forDate(date('Y-m-d'), date('Y-m-d')));
        $this->assertNotEmpty($ids);
        $bad = DB::table('tenant_contracts as tc')->join('buildings as b', 'b.id', '=', 'tc.building_id')
            ->whereIn('tc.id', $ids)
            ->where(function ($q) {
                $q->where('tc.work_flow_processes_code', '<>', 108)->orWhereNull('tc.work_flow_processes_code')
                  ->orWhere('b.building_status', '<>', '1')
                  ->orWhereExists(function ($e) { $e->select(DB::raw(1))->from('legal')->whereRaw('legal.tenant_contract_id = tc.id'); });
            })->count();
        $this->assertSame(0, $bad);
    }

    public function test_unapproved_renewal_draft_is_left_out()
    {
        $draft = DB::table('tenant_contracts')->whereNull('work_flow_processes_code')
            ->where('tenant_contract_effective_date', '<=', date('Y-m-d'))->orderBy('id', 'desc')->first();
        if (!$draft) {
            $this->markTestSkipped('no unapproved draft in this database');
        }
        $this->assertArrayNotHasKey($draft->id, (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d')));
    }

    public function test_receipts_after_the_receipts_date_are_not_counted()
    {
        // a contract in an active building with rent receipts on at least two different days
        $c = DB::selectOne("
            SELECT rg.tenant_contract_id AS id, b.building_name, max(rg.receipts_generation_receipt_date)::date AS last_day
              FROM receipts_generation rg
              JOIN tenant_contracts tc ON tc.id = rg.tenant_contract_id AND tc.work_flow_processes_code = 108
              JOIN buildings b ON b.id = tc.building_id AND b.building_status = '1'
             WHERE rg.receipts_generation_type = '0' AND rg.deleted_at IS NULL
               AND NOT EXISTS (SELECT 1 FROM legal l WHERE l.tenant_contract_id = tc.id)
             GROUP BY rg.tenant_contract_id, b.building_name
            HAVING count(DISTINCT rg.receipts_generation_receipt_date::date) >= 2
             ORDER BY rg.tenant_contract_id DESC LIMIT 1");
        $upTo = date('Y-m-d', strtotime($c->last_day . ' -1 day'));
        $expected = (float) DB::table('receipts_generation')->where('tenant_contract_id', $c->id)
            ->where('receipts_generation_type', '0')->whereNull('deleted_at')
            ->whereRaw('receipts_generation_receipt_date::date <= ?', [$upTo])->sum('receipts_generation_amt');

        $row = (new RentDues)->forDate(date('Y-m-d'), $upTo, ['building_name' => $c->building_name])[$c->id];
        $this->assertEquals($expected, (float) $row->received, '', 0.0005);
        $this->assertEquals(round($expected, 3), $row->ledger['received'], '', 0.0005);
    }

    public function test_termination_dated_after_the_as_on_date_is_ignored()
    {
        $t = DB::selectOne("
            SELECT tc.id, b.building_name, max(t.termination_date)::date AS term
              FROM termination t
              JOIN tenant_contracts tc ON tc.id = t.contract_id AND tc.work_flow_processes_code = 108
              JOIN buildings b ON b.id = tc.building_id AND b.building_status = '1'
             WHERE t.work_flow_processes_code = '505'
               AND NOT EXISTS (SELECT 1 FROM legal l WHERE l.tenant_contract_id = tc.id)
             GROUP BY tc.id, b.building_name, tc.tenant_contract_effective_date
            HAVING max(t.termination_date)::date > tc.tenant_contract_effective_date::date + 40
             ORDER BY tc.id DESC LIMIT 1");
        $asOn = date('Y-m-d', strtotime($t->term . ' -1 day'));
        $dues = (new RentDues)->forDate($asOn, $asOn, ['building_name' => $t->building_name]);
        $this->assertNull($dues[$t->id]->term_date);
        $after = (new RentDues)->forDate($t->term, $t->term, ['building_name' => $t->building_name]);
        $this->assertSame($t->term, $after[$t->id]->term_date);
    }

    public function test_building_filter_keeps_only_that_building()
    {
        $name = DB::table('buildings')->where('building_status', '1')->orderBy('id')->value('building_name');
        $dues = (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'), ['building_name' => $name]);
        $this->assertNotEmpty($dues);
        foreach ($dues as $d) {
            $this->assertSame($name, $d->building_name);
        }
    }
}
```

- [ ] **Step 2: Run them to make sure they fail**

Run: `$PHP vendor/bin/phpunit tests/Feature/RentDuesTest.php`
Expected: errors, `Class 'Modules\BackOffice\Services\RentDues' not found`.

- [ ] **Step 3: Write the implementation**

`Modules/BackOffice/Services/RentDues.php`:
```php
<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\DB;

/**
 * What every tenant contract owes on a date: the one calculation behind the
 * Tenant Aging Report and the Receivables Follow-up (a RentLedger per contract).
 * Contracts: approved (108), in active buildings, not legal cases, effective by the as-on date.
 * Receipts: rent receipts (type 0), not deleted, dated on or before $receiptsUpTo.
 * Terminations: 505 done, termination date on or before the as-on date.
 * See docs/superpowers/specs/2026-10-02-tenant-dues-ledger-design.md.
 */
class RentDues
{
    /** Optional exact-match filters => column. */
    const FILTERS = [
        'building_name'   => 'b.building_name',
        'building_no'     => 'b.building_no',
        'tenant_name'     => 't.tenant_name',
        'management_type' => 'mt.management_types_name',
    ];

    /**
     * @param  string $asOn          Y-m-d: rent that fell due on or before this date
     * @param  string $receiptsUpTo  Y-m-d: rent receipts dated on or before this date
     * @param  array  $filters       building_name, building_no, tenant_name, management_type, are
     * @return object[] keyed by contract id, each with ->ledger (RentLedger::build result)
     */
    public function forDate($asOn, $receiptsUpTo, array $filters = [])
    {
        $where = '';
        $bind = [$receiptsUpTo, $asOn, $asOn];
        foreach (self::FILTERS as $key => $col) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $where .= " AND $col = ?";
                $bind[] = $filters[$key];
            }
        }
        if (isset($filters['are']) && $filters['are'] !== '') {
            // the ARE the building is assigned to, as the old receivable functions matched it
            $where .= " AND EXISTS (SELECT 1 FROM employees emp
                          JOIN users us ON emp.id = us.user_type_id
                          JOIN are_buildings ab ON us.id = ab.user_id
                          JOIN preferred_buildings pb ON ab.id = pb.are_building_id
                         WHERE pb.assign_to IS NULL AND pb.building_id = b.id AND emp.employee_name = ?)";
            $bind[] = $filters['are'];
        }

        $rows = DB::select("
            SELECT tc.id, tc.tenant_contract_no AS contract_no, t.tenant_name,
                   b.building_name, b.building_no, u.unit_no AS unit_code,
                   tc.tenant_contract_effective_date::date AS eff,
                   tc.tenant_contract_start_date::date      AS start,
                   tc.tenant_contract_valid_to_date::date   AS vto,
                   tc.tenant_contract_rent                  AS rent,
                   tc.tenant_contract_value                 AS value,
                   CASE WHEN tc.pdc_check = 1 THEN 'Full' WHEN tc.pdc_check = 2 THEN 'Partial' ELSE '' END AS pdc,
                   ter.term_date,
                   r.received, r.paid_till, r.last_received,
                   (SELECT sum(rl.receipts_generation_amt) FROM receipts_generation rl
                     WHERE rl.tenant_contract_id = tc.id AND rl.receipts_generation_type = '0' AND rl.deleted_at IS NULL
                       AND rl.receipts_generation_receipt_date::date = r.last_received::date) AS last_amount
              FROM tenant_contracts tc
              JOIN buildings b ON b.id = tc.building_id AND b.building_status = '1'
              LEFT JOIN tenant t ON t.id = tc.tenant_id
              LEFT JOIN units u ON u.id = tc.unit_id
              LEFT JOIN management_types mt ON mt.id = b.management_id
              LEFT JOIN (SELECT tenant_contract_id,
                           sum(receipts_generation_amt)          AS received,
                           max(receipts_generation_eff_to)        AS paid_till,
                           max(receipts_generation_receipt_date)  AS last_received
                      FROM receipts_generation
                     WHERE receipts_generation_type = '0' AND deleted_at IS NULL
                       AND receipts_generation_receipt_date::date <= ?::date
                     GROUP BY tenant_contract_id) r ON r.tenant_contract_id = tc.id
              LEFT JOIN (SELECT contract_id, max(termination_date)::date AS term_date
                           FROM termination
                          WHERE work_flow_processes_code = '505' AND termination_date::date <= ?::date
                          GROUP BY contract_id) ter ON ter.contract_id = tc.id
             WHERE tc.work_flow_processes_code = 108
               AND tc.tenant_contract_effective_date::date <= ?::date
               AND NOT EXISTS (SELECT 1 FROM legal l WHERE l.tenant_contract_id = tc.id)
               $where
        ", $bind);

        $out = [];
        foreach ($rows as $r) {
            $r->ledger = RentLedger::build($r, $asOn, $r->received);
            $out[(int) $r->id] = $r;
        }
        return $out;
    }
}
```

- [ ] **Step 4: Run the tests and make sure they pass**

Run: `$PHP vendor/bin/phpunit tests/Feature/RentDuesTest.php`
Expected: `OK (5 tests, ...)`. The draft test may be skipped if the DB has no drafts.

- [ ] **Step 5: Check speed on the full data**

```bash
$PHP artisan tinker --execute="\$t=microtime(true); \$d=(new Modules\BackOffice\Services\RentDues)->forDate(date('Y-m-d'), date('Y-m-d')); echo count(\$d).' contracts, '.round(microtime(true)-\$t,1).'s, '.round(memory_get_peak_usage()/1048576).'MB'.PHP_EOL;"
```
Expected: well under 60 s and 256 MB. If it is slower, report the numbers before going on; don't optimise blindly.

- [ ] **Step 6: Commit**

```bash
git add Modules/BackOffice/Services/RentDues.php tests/Feature/RentDuesTest.php
git commit -m "feat(tenant-dues): RentDues — every approved contract's rent ledger on a date

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
git status --short
```

---

### Task 4: Tenant Aging Report uses RentDues

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/BackOfficeReportController.php` (the `use` block near line 29; `tenantAgingReportPdf()` near lines 2263–2298)
- Test: `tests/Feature/TenantAgingReportTest.php`

**Interfaces:**
- Consumes: `RentDues::forDate()`, `TenantAgingCalculator::rows()`, `TenantAgingCalculator::buckets()`.

- [ ] **Step 1: Write the failing test**

`tests/Feature/TenantAgingReportTest.php`:
```php
<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\RentDues;
use Modules\BackOffice\Services\TenantAgingCalculator;

class TenantAgingReportTest extends TestCase
{
    private function busyBuilding()
    {
        // an active building with at least one contract owing today
        $dues = (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'));
        foreach ($dues as $d) {
            if ($d->ledger['balance'] > 0) {
                return $d->building_name;
            }
        }
        $this->markTestSkipped('nothing owing in this database');
    }

    public function test_excel_download_for_one_building()
    {
        $this->actingAs(User::find(1));
        $res = $this->post(route('tenantAgingReportPdf'), [
            'building_name' => $this->busyBuilding(), 'end_date' => date('Y-m-d'), 'download_type' => 'excel',
        ]);
        $res->assertOk();
        $this->assertContains('tenant_aging_' . date('Y-m-d') . '.xlsx', $res->headers->get('content-disposition'));
    }

    public function test_pdf_download_for_one_building()
    {
        $this->actingAs(User::find(1));
        $res = $this->post(route('tenantAgingReportPdf'), [
            'building_name' => $this->busyBuilding(), 'end_date' => date('Y-m-d'), 'download_type' => 'pdf',
        ]);
        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
    }

    public function test_buckets_add_up_to_each_balance()
    {
        $building = $this->busyBuilding();
        $rows = TenantAgingCalculator::rows((new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'), ['building_name' => $building]), date('Y-m-d'));
        $this->assertNotEmpty($rows);
        foreach ($rows as $r) {
            $this->assertEquals($r->netamtdue, array_sum($r->aging), $r->contract_no, 0.002);
        }
    }

    public function test_report_no_longer_calls_the_old_functions()
    {
        $src = file_get_contents(base_path('Modules/BackOffice/Http/Controllers/BackOfficeReportController.php'));
        preg_match('/function tenantAgingReportPdf\(.*?\n}\n/s', $src, $m);
        $this->assertNotContains('tenantrentreceivable', $m[0]);
    }
}
```

- [ ] **Step 2: Run it to make sure it fails**

Run: `$PHP vendor/bin/phpunit tests/Feature/TenantAgingReportTest.php`
Expected: `test_report_no_longer_calls_the_old_functions` FAILS. The download tests may already pass, and they act as the safety net for Step 3.

- [ ] **Step 3: Switch the controller**

In `BackOfficeReportController.php`, add the import under the existing `use Modules\BackOffice\Services\TenantAgingCalculator;`:
```php
use Modules\BackOffice\Services\RentDues;
```

In `tenantAgingReportPdf()`, replace this block:
```php
  $dateOnly = !empty($date2) && empty($buildingname) && empty($buildingno) && empty($tenantname) && empty($managetype) && empty($are);

  if ($dateOnly) {
    $rows = DB::select("SELECT * FROM tenantrentreceivable_v2(?::date) ORDER BY buildingname, unit_code", [$date2]);
  } else {
    $rows = DB::select(
      "SELECT * FROM tenantrentreceivablecompo_v2(?::date,?,?,?,?,?) ORDER BY buildingname, unit_code",
      [$date2, $tenantname, $buildingname, $buildingno, $managetype, $are]
    );
  }
```
with:
```php
  ini_set('memory_limit', '256M');
  set_time_limit(300);
  // one calculation for the tenant reports (RentDues / RentLedger): dues and receipts up to the date
  $rows = TenantAgingCalculator::rows(app(RentDues::class)->forDate($date2, $date2, [
    'building_name'   => $buildingname,
    'building_no'     => $buildingno,
    'tenant_name'     => $tenantname,
    'management_type' => $managetype,
    'are'             => $are,
  ]), $date2);
```

Then replace:
```php
  // Derive the aging split for every row; buckets always sum to netamtdue
  $buckets = TenantAgingCalculator::buckets($date2);
  $keys    = array_keys($buckets);
  foreach ($rows as $row) {
    $row->aging = TenantAgingCalculator::age($row, $date2);
  }
```
with:
```php
  // aging split by days overdue; buckets always sum to netamtdue
  $buckets = TenantAgingCalculator::buckets($date2);
  $keys    = array_keys($buckets);
```
Leave the PDC filter, the PDF builder and the Excel export as they are. They read `buildingname, tenant_name, contract_no, unit_code, netamtdue, aging, pdc`, which `rows()` provides.

- [ ] **Step 4: Run the tests and make sure they pass**

Run: `$PHP vendor/bin/phpunit tests/Feature/TenantAgingReportTest.php tests/Unit/TenantAgingCalculatorTest.php`
Expected: all pass.

- [ ] **Step 5: Commit**

```bash
git add Modules/BackOffice/Http/Controllers/BackOfficeReportController.php tests/Feature/TenantAgingReportTest.php
git commit -m "feat(tenant-aging): report from RentDues — correct dues, days-overdue buckets

No more rent after the contract end, deposit-only contracts kept, later
receipts and terminations ignored for a past date.

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
git status --short
```

---

### Task 5: Receivables Follow-up uses RentDues, payments up to today

Replaces `ReceivableFigures::dueAsOf()` and `build()` (their rules now live in `RentLedger`) with `fromDues()`. The cache key now includes today, because the payments counted change every day.

**Files:**
- Modify (rewrite): `Modules/BackOffice/Services/ReceivableFigures.php`
- Modify: `Modules/BackOffice/Http/Controllers/ReceivableFollowupController.php`
- Modify: `Modules/BackOffice/Resources/views/ReceivableFollowup/_rows.blade.php` (lines 14, 28, 50)
- No change: `_header.blade.php` (it prints `$notPaidFrom`, whose text comes from the controller)
- Modify (rewrite): `tests/Unit/ReceivableFiguresTest.php`
- Modify: `tests/Feature/ReceivableFollowupTest.php`

**Interfaces:**
- Consumes: `RentDues::forDate($asOn, $receiptsUpTo)`.
- Produces: `ReceivableFigures::fromDues(array $dues): array` of contractId ⇒ `['due', 'received', 'balance', 'not_paid_from', 'last_received', 'last_amount', 'paid_till', 'start']`. `ReceivableFigures::key($asOn, $today = null)`. `lastMonth()`, `asOnDate()`, `compute($asOn)`, `refresh()`, `cached()` and `get()` keep their signatures.

- [ ] **Step 1: Write the failing unit tests (replace the whole file)**

`tests/Unit/ReceivableFiguresTest.php`:
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\RentLedger;
use Modules\BackOffice\Services\ReceivableFigures as RF;

class ReceivableFiguresTest extends TestCase
{
    /** One RentDues item: contract row + its ledger. */
    private function due($id, $received, array $o = [])
    {
        $c = (object) array_merge([
            'id' => $id, 'eff' => '2026-01-01', 'start' => '2026-01-01', 'vto' => '2026-12-31', 'rent' => '100', 'value' => '1200',
            'term_date' => null, 'received' => $received, 'paid_till' => null, 'last_received' => null, 'last_amount' => null,
        ], $o);
        $c->ledger = RentLedger::build($c, '2026-08-31', $received);
        return $c;
    }

    public function test_last_month()
    {
        $this->assertSame([9, 2026], RF::lastMonth('2026-10-01'));
        $this->assertSame([12, 2025], RF::lastMonth('2026-01-15'));
    }

    public function test_cutoff_is_the_end_of_the_month_two_months_before()
    {
        $this->assertSame('2026-08-31', RF::asOnDate(10, 2026, '2026-10-01'));
        $this->assertSame('2026-07-31', RF::asOnDate(9, 2026, '2026-10-01'));
        $this->assertSame('2025-11-30', RF::asOnDate(1, 2026, '2026-10-01'));
        $this->assertSame('2024-02-29', RF::asOnDate(4, 2024, '2026-10-01'));
    }

    public function test_cutoff_never_after_today()
    {
        $this->assertSame('2026-10-01', RF::asOnDate(12, 2026, '2026-10-01'));
    }

    public function test_balances_under_30_are_left_out()
    {
        $out = RF::fromDues([31 => $this->due(31, '771'), 32 => $this->due(32, '770')]);   // owe 29 and 30
        $this->assertSame([32], array_keys($out));
    }

    public function test_paid_up_and_overpaid_are_left_out()
    {
        $this->assertSame([], RF::fromDues([1 => $this->due(1, '800'), 2 => $this->due(2, '950')]));
    }

    public function test_row_shape_with_not_paid_from_the_oldest_unpaid_month()
    {
        $out = RF::fromDues([11 => $this->due(11, '250', ['paid_till' => '2026-03-15', 'last_received' => '2026-09-20 10:00:00', 'last_amount' => '50'])]);
        $this->assertSame([
            'due' => 800.0, 'received' => 250.0, 'balance' => 550.0, 'not_paid_from' => '2026-03-01',
            'last_received' => '2026-09-20', 'last_amount' => 50.0, 'paid_till' => '2026-03-15', 'start' => '2026-01-01',
        ], $out[11]);
    }

    public function test_never_paid_is_kept_and_not_paid_from_its_first_month()
    {
        $out = RF::fromDues([21 => $this->due(21, null)]);
        $this->assertSame(800.0, $out[21]['balance']);
        $this->assertSame('2026-01-01', $out[21]['not_paid_from']);
        $this->assertNull($out[21]['paid_till']);
    }

    public function test_cache_key_has_the_cutoff_and_today()
    {
        $this->assertSame('receivable_followup:v6:2026-08-31:2026-10-02', RF::key('2026-08-31', '2026-10-02'));
    }
}
```

- [ ] **Step 2: Run them to make sure they fail**

Run: `$PHP vendor/bin/phpunit tests/Unit/ReceivableFiguresTest.php`
Expected: FAIL, `Call to undefined method ...ReceivableFigures::fromDues()` and the key mismatch.

- [ ] **Step 3: Rewrite ReceivableFigures**

`Modules/BackOffice/Services/ReceivableFigures.php` (whole file):
```php
<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Due / Received / Balance / Not Paid From / Last Received / Paid Till per tenant
 * contract for the Receivables Follow-up screen.
 *
 * Dues: rent that fell due up to the cutoff, the end of the month two months before
 * the month picked (October -> 31 August), from the shared RentDues / RentLedger
 * calculation (same rules as the Tenant Aging Report). Payments: every rent receipt
 * up to today, so a tenant who has paid since the cutoff is not chased. Only
 * agreements still owing at least 30 are kept (never-paid included).
 * Cached per cutoff and day (Laravel 5.7: TTL in minutes). PHP < 7.4: no arrow functions.
 */
class ReceivableFigures
{
    const TTL_MINUTES = 1440;
    const BUSY_MINUTES = 2;
    const MONTHS_BACK = 2;
    const MIN_BALANCE = 30; // tiny leftovers are not chased

    public static function lastMonth($today = null)
    {
        $d = Carbon::parse($today ?: Carbon::today())->startOfMonth()->subMonth();
        return [(int) $d->month, (int) $d->year];
    }

    /** The cutoff for a picked month: last day of the month two months earlier, never after today. */
    public static function asOnDate($month, $year, $today = null)
    {
        $today = Carbon::parse($today ?: Carbon::today())->startOfDay();
        $end = Carbon::create((int) $year, (int) $month, 1, 0, 0, 0)->subMonthsNoOverflow(self::MONTHS_BACK)->endOfMonth()->startOfDay();
        return ($end->gt($today) ? $today : $end)->format('Y-m-d');
    }

    /** v6: RentLedger figures, payments up to the day they were computed. */
    public static function key($asOn, $today = null)
    {
        return 'receivable_followup:v6:' . $asOn . ':' . Carbon::parse($today ?: Carbon::today())->format('Y-m-d');
    }

    /**
     * Pure. $dues: RentDues::forDate() result. Keeps contracts owing at least MIN_BALANCE.
     * Returns [contractId => ['due', 'received', 'balance', 'not_paid_from', 'last_received', 'last_amount', 'paid_till', 'start']].
     */
    public static function fromDues(array $dues)
    {
        $out = [];
        foreach ($dues as $id => $d) {
            $l = $d->ledger;
            if ($l['balance'] < self::MIN_BALANCE) {
                continue;
            }
            $out[(int) $id] = [
                'due'           => $l['due'],
                'received'      => $l['received'],
                'balance'       => $l['balance'],
                'not_paid_from' => $l['not_paid_from'],
                'last_received' => $d->last_received ? Carbon::parse($d->last_received)->format('Y-m-d') : null,
                'last_amount'   => $d->last_amount !== null ? round((float) $d->last_amount, 3) : null,
                'paid_till'     => $d->paid_till ? Carbon::parse($d->paid_till)->format('Y-m-d') : null,
                'start'         => Carbon::parse($d->eff)->format('Y-m-d'),
            ];
        }
        return $out;
    }

    /** Dues up to the cutoff, payments up to today. */
    public function compute($asOn)
    {
        $today = Carbon::today()->format('Y-m-d');
        return self::fromDues(app(RentDues::class)->forDate($asOn, max($asOn, $today)));
    }

    /**
     * Recompute and cache. On failure the exception propagates and the old cache entry stays.
     * One calculation per cutoff at a time (the file cache has no locks in 5.7, so a
     * short-lived ":busy" marker set with add() stands in); others get FiguresBusy.
     */
    public function refresh($asOn)
    {
        $busy = self::key($asOn) . ':busy';
        if (!Cache::add($busy, 1, self::BUSY_MINUTES)) {
            throw new FiguresBusy('Figures for ' . $asOn . ' are being calculated.');
        }
        try {
            $data = ['computed_at' => Carbon::now()->format('Y-m-d H:i:s'), 'rows' => $this->compute($asOn)];
            Cache::put(self::key($asOn), $data, self::TTL_MINUTES);
            return $data;
        } finally {
            Cache::forget($busy);
        }
    }

    public function cached($asOn)
    {
        $data = Cache::get(self::key($asOn));
        return is_array($data) ? $data : null;
    }

    public function get($asOn)
    {
        $data = $this->cached($asOn);
        return $data ?: $this->refresh($asOn);
    }
}
```
Before saving, compare with the current file (`git show HEAD:Modules/BackOffice/Services/ReceivableFigures.php`). Keep any method other than `dueAsOf()`, `build()` and `compute()` exactly as it is. If the file has a method not shown above, keep it and tell the reviewer.

- [ ] **Step 4: Run the unit tests and make sure they pass**

Run: `$PHP vendor/bin/phpunit tests/Unit/ReceivableFiguresTest.php`
Expected: `OK (8 tests, ...)`.

- [ ] **Step 5: Update the Follow-up feature tests**

In `tests/Feature/ReceivableFollowupTest.php`:

5a. In `seed3()`, add `not_paid_from` to every seeded row (replace the `$rows[$c->id] = [...]` statement):
```php
            $rows[$c->id] = ['due' => $balances[$i] + 100, 'received' => 100.0, 'balance' => $balances[$i],
                'last_received' => '2026-05-02', 'last_amount' => 350.0, 'paid_till' => $paidTill[$i],
                'not_paid_from' => date('Y-m-d', strtotime($paidTill[$i] . ' +1 day')), 'start' => '2025-01-01'];
```

5b. Replace `test_never_paid_row_says_never_paid_since_its_start` with:
```php
    public function test_never_paid_row_says_not_paid_from_its_first_month()
    {
        $c = $this->contracts[2];
        $rows = Cache::get(ReceivableFigures::key($this->asOn));
        $rows['rows'][$c->id] = ['due' => 2800.0, 'received' => 0.0, 'balance' => 2800.0, 'not_paid_from' => '2026-01-01',
            'last_received' => null, 'last_amount' => null, 'paid_till' => null, 'start' => '2026-01-01'];
        Cache::put(ReceivableFigures::key($this->asOn), $rows, 60);
        $row = $this->rowHtml($this->rows(), $c->id);
        $this->assertRegExp('/Not paid from.*?01\/01\/2026.*?never paid/s', $row);

        $html = $this->get(route('receivable-followup.form', $c->id) . '?as_on=' . $this->asOn)->assertOk()->getContent();
        $this->assertContains('01/01/2026 (never paid)', $html);
    }
```

5c. In `test_sort_by_not_paid_from`, change the sort key:
```php
        $html = $this->rows(['sort' => 'not_paid_from', 'direction' => 'asc']);   // longest unpaid first
```

5d. In `test_summary_explains_the_cutoff`, add after the existing assertions:
```php
        $this->assertContains('Payments up to <strong>' . Carbon::today()->format('d/m/Y') . '</strong>', $html);
```

- [ ] **Step 6: Run them to make sure the changed ones fail**

Run: `$PHP vendor/bin/phpunit tests/Feature/ReceivableFollowupTest.php`
Expected: the never-paid, sort and summary tests FAIL. The others pass.

- [ ] **Step 7: Update the controller**

In `ReceivableFollowupController.php`:

7a. Replace `'paid_till'` in `SORTS` with `'not_paid_from'`:
```php
    const SORTS = ['balance', 'due', 'received', 'last_received', 'not_paid_from', 'tenant', 'building', 'contract'];
```

7b. In `rows()`, add `not_paid_from` to each list item, next to `paid_till`:
```php
                'not_paid_from' => isset($f['not_paid_from']) ? $f['not_paid_from'] : null,
```

7c. In `header()`, replace the `'notPaidFrom' => ...` entry (both lines, including the "Never paid (started …)" fallback) with:
```php
            'notPaidFrom'  => $fig && !empty($fig['not_paid_from'])
                ? Carbon::parse($fig['not_paid_from'])->format('d/m/Y') . ($paidTill ? '' : ' (never paid)')
                : null,
```

- [ ] **Step 8: Update the rows view**

In `_rows.blade.php`:

Line 14, after the "Dues up to" span, add:
```blade
    <span>Payments up to <strong>{{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}</strong></span>
```

Line 28, the column header:
```blade
    {!! $th('not_paid_from', 'Not Paid From', 'asc') !!}
```

Line 50, the Not Paid From cell (replace the whole `<td>…</td>`):
```blade
      <td>@if($r->not_paid_from)<span class="rf-unpaid"><span class="rf-sr">Not paid from </span>{{ $fmtDate($r->not_paid_from) }}</span>@if($r->paid_till)<span class="rf-sub">paid till {{ $fmtDate($r->paid_till) }}</span>@else<span class="rf-sub">never paid</span>@endif @else — @endif</td>
```

- [ ] **Step 9: Run all Follow-up tests**

Run: `$PHP vendor/bin/phpunit tests/Unit/ReceivableFiguresTest.php tests/Feature/ReceivableFollowupTest.php tests/Feature/ReceivableFollowupRefreshTest.php`
Expected: all pass. If `ReceivableFollowupRefreshTest` fails on the cache key or compute, read it. It should only need the key to include today, which `key($asOn)` does by default. Fix the test only where it pins the old `v5` key.

- [ ] **Step 10: Commit**

```bash
git add Modules/BackOffice/Services/ReceivableFigures.php Modules/BackOffice/Http/Controllers/ReceivableFollowupController.php Modules/BackOffice/Resources/views/ReceivableFollowup/_rows.blade.php tests/Unit/ReceivableFiguresTest.php tests/Feature/ReceivableFollowupTest.php tests/Feature/ReceivableFollowupRefreshTest.php
git commit -m "feat(receivable-followup): figures from RentDues, payments up to today

Same dues as the Tenant Aging Report; tenants who paid after the cutoff are
no longer listed; Not Paid From = oldest unpaid month. Replaces
ReceivableFigures::dueAsOf()/build() (rules now in RentLedger).

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
git status --short
```

---

### Task 6: Check the numbers and the screens

**Files:** none committed (scratch scripts only, in the session scratchpad).

- [ ] **Step 1: Aging and Follow-up agree on the same date**

Create `<scratchpad>/check_same.php`:
```php
<?php
require 'C:/laragon/www/plms_master/vendor/autoload.php';
$app = require 'C:/laragon/www/plms_master/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Modules\BackOffice\Services\{RentDues, ReceivableFigures, TenantAgingCalculator};
$d = $argv[1] ?? date('Y-m-d');
$dues = (new RentDues)->forDate($d, $d);
$fu = ReceivableFigures::fromDues($dues);
$ag = [];
foreach (TenantAgingCalculator::rows($dues, $d) as $r) { $ag[$r->contract_no][] = $r; }
$bad = 0;
foreach ($fu as $id => $f) {
    $a = $dues[$id]->ledger['balance'];
    if (abs($a - $f['balance']) > 0.0005) { $bad++; echo "MISMATCH {$dues[$id]->contract_no} aging $a follow-up {$f['balance']}\n"; }
}
$under = 0; foreach ($dues as $x) { if ($x->ledger['balance'] > 0 && $x->ledger['balance'] < 30) $under++; }
$sumOk = 0; foreach ($ag as $list) foreach ($list as $r) { if (abs(array_sum($r->aging) - $r->netamtdue) < 0.002) $sumOk++; }
echo "date $d: aging rows " . array_sum(array_map('count', $ag)) . ", follow-up rows " . count($fu) . ", aging-only (balance < 30) $under, mismatches $bad, rows whose buckets add up $sumOk\n";
```
Run: `$PHP <scratchpad>/check_same.php 2026-08-31` and `$PHP <scratchpad>/check_same.php 2026-10-02`
Expected: `mismatches 0`; aging rows = follow-up rows + aging-only; the bucket count equals the aging rows.

- [ ] **Step 2: What changed against the old Aging report**

Run the old functions and the new calculation for 2026-10-02 and group the differences by contract. Expected, from the 02/10 comparison:
- Active contracts past their end date drop to their contract value (e.g. TAG2500918, TAG2600824).
- Deposit-only contracts appear (e.g. TAG2601752).
- In-progress terminations get their last month (e.g. TAG2600780).
- Nothing else moves.

Any other kind of difference: list 5 examples with figures and stop for review.

- [ ] **Step 3: Screens**

Load the Aging report (`/showTenantAgingReport`): download Excel and PDF for one building and for the full date. Load the Follow-up (`/receivable-followup`): check the "Payments up to" line, "Not Paid From / never paid" and sorting by Not Paid From. If the Chrome extension isn't connected, render as user 1 and screenshot with headless Chrome (see memory `ui-screenshot-check`).

- [ ] **Step 4: Full test run**

Run: `$PHP vendor/bin/phpunit tests/Unit tests/Feature/RentDuesTest.php tests/Feature/TenantAgingReportTest.php tests/Feature/ReceivableFollowupTest.php tests/Feature/ReceivableFollowupRefreshTest.php`
Expected: all pass.

- [ ] **Step 5: Report to the user**

Give the counts and totals from Steps 1–2 and the screenshots. State that the branch is **not merged and not staged**, and wait for the user before merging or copying to `server_upload_files/`. The upload batch will be 6 PHP files (`RentLedger`, `RentDues`, `TenantAgingCalculator`, `ReceivableFigures`, `BackOfficeReportController`, `ReceivableFollowupController`) plus `ReceivableFollowup/_rows.blade.php`, no SQL. If the match-v2 merge from Task 0 hasn't been uploaded yet, its files are already covered by this list. After upload: `php artisan view:clear` then `php artisan receivables:refresh-followup`.
