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
