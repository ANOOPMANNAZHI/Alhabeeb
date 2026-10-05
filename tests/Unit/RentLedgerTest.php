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
        // 20/05 - 01/01 = 139 days, minus 1 = 138; 138 x 100 / 30 = 460
        $l = L::build($this->c(['term_date' => '2026-05-20']), '2026-08-31', 0);
        $this->assertSame(460.0, $l['due']);
        $this->assertSame([100.0, 100.0, 100.0, 100.0, 60.0], $this->amounts($l));
    }

    /** 03/02 - 01/02 - 1 = 1 day x 435 / 30 = 14.5 -> 14 (half to even, as v2). */
    public function test_terminated_rounds_half_to_even()
    {
        $c = $this->c(['eff' => '2020-02-01', 'start' => '2020-02-01', 'vto' => '2021-01-31', 'rent' => '435', 'value' => '5220', 'term_date' => '2020-02-03']);
        $this->assertSame(14.0, L::build($c, '2026-08-31', 0)['due']);
    }

    /**
     * Before 01/08/2026, a termination on the last day of a rent month never owes more
     * than the months used (user, 05/10/2026). TAG2400856: 01/07/2024 - 31/05/2025 at 400
     * = 11 months = 4,400 (the old count said 333 days = 4,440). TAG2000072: 11 x 435 = 4,785.
     * Where the old count is lower (February, 30-day months) it stands: those were settled
     * with it (TAG2200489: 16/02 - 15/05/2022 at 160 = 87 days = 464, collected 464).
     */
    public function test_old_termination_on_the_last_day_of_a_rent_month_is_capped_at_whole_months()
    {
        $this->assertSame(4400.0, L::earlyTerminationDue('2024-07-01', '2025-05-31', 400));
        $this->assertSame(4785.0, L::earlyTerminationDue('2020-02-01', '2020-12-31', 435));
        $this->assertSame(600.0, L::earlyTerminationDue('2024-07-01', '2024-12-31', 100));   // 182 days = 607 -> 6 months
        $this->assertSame(700.0, L::earlyTerminationDue('2025-07-10', '2026-02-09', 100));   // mid-month periods: 215 days = 717 -> 7 months
        // old count lower than whole months: kept
        $this->assertSame(464.0, L::earlyTerminationDue('2022-02-16', '2022-05-15', 160));
        $this->assertSame(522.0, L::earlyTerminationDue('2022-01-01', '2022-02-28', 275));  // 57 days = 522.5 -> 522
        $this->assertSame(497.0, L::earlyTerminationDue('2026-01-01', '2026-05-31', 100));
        // not on a period end: the old count
        $this->assertSame(460.0, L::earlyTerminationDue('2026-01-01', '2026-05-20', 100));
        $l = L::build($this->c(['eff' => '2024-07-01', 'start' => '2024-07-01', 'vto' => '2025-06-30', 'rent' => '400', 'value' => '4800', 'term_date' => '2025-05-31']), '2026-10-05', 4400);
        $this->assertSame(0.0, $l['balance']);
    }

    /** Due for an early termination from 01/08/2026 on, rent 650. */
    private function due650($eff, $term)
    {
        $c = $this->c(['eff' => $eff, 'start' => $eff, 'vto' => '2028-12-31', 'rent' => '650', 'value' => '20000', 'term_date' => $term]);
        return L::build($c, '2028-12-31', 0)['due'];
    }

    /** Every month counts 30 days: whole months x 30 + days used after them (termination day included, max 30). */
    public function test_every_month_counts_30_days()
    {
        $this->assertSame(650.0, $this->due650('2026-08-01', '2026-08-31'));   // TAG2601341: August (31 days) = 1 month
        $this->assertSame(650.0, $this->due650('2027-02-01', '2027-02-28'));   // February (28 days) = 1 month
        $this->assertSame(650.0, $this->due650('2028-02-01', '2028-02-29'));   // February (29 days) = 1 month
        $this->assertSame(650.0, $this->due650('2026-09-01', '2026-09-30'));   // September (30 days) = 1 month
        $this->assertSame(650.0, $this->due650('2026-08-16', '2026-09-15'));   // mid-month to mid-month = 1 month
        $this->assertSame(325.0, $this->due650('2026-08-01', '2026-08-15'));   // 15 days
        $this->assertSame(1517.0, $this->due650('2026-08-01', '2026-10-10'));  // 2 months + 10 days = 70 days: 1,516.67 -> 1,517
        $this->assertSame(585.0, $this->due650('2027-02-01', '2027-02-27'));   // February not finished: 27 days
        $this->assertSame(650.0, $this->due650('2026-08-01', '2026-08-30'));   // 30 days used: never more than one month
    }

    /** Shared with RentOutstanding (termination screens / Termination Dues). */
    public function test_early_termination_due_is_shared()
    {
        $this->assertSame(650.0, L::earlyTerminationDue('2026-08-01', '2026-08-31', 650));
        $this->assertSame(120.0, L::earlyTerminationDue('2026-09-01', '2026-09-15', 240));   // TAG2602074: 15 days
        $this->assertSame(0.0, L::earlyTerminationDue('2026-10-01', '2026-10-01', 650));     // on the start date
        $this->assertSame(290.0, L::earlyTerminationDue('2026-07-01', '2026-07-31', 300));   // before 01/08/2026: old count (lower than the whole month)
        $this->assertSame(140.0, L::earlyTerminationDue('2026-07-01', '2026-07-16', 300));   // before 01/08/2026: old count, 15 - 1 = 14 days
        $this->assertSame(1837.0, L::earlyTerminationDue('2026-06-16', '2026-08-31', '725')); // 2 months + 16 days
    }

    /** 16/06 -> 31/08 by the day = 1,837, but this contract (to 15/09) is worth only 1,812.5. */
    public function test_early_termination_never_owes_more_than_the_contract_value()
    {
        $c = $this->c(['eff' => '2026-06-16', 'start' => '2026-06-16', 'vto' => '2026-09-15', 'rent' => '725', 'value' => '1812.5', 'term_date' => '2026-08-31']);
        $this->assertSame(1812.5, L::build($c, '2026-10-03', 0)['due']);
    }

    public function test_new_day_count_trims_the_last_month()
    {
        // 01/08 - 30/09 = 2 months (60 days) + 01/10..20/10 = 20 days -> 80 x 100 / 30 = 266.67 -> 267
        $c = $this->c(['eff' => '2026-08-01', 'start' => '2026-08-01', 'vto' => '2027-07-31', 'term_date' => '2026-10-20']);
        $l = L::build($c, '2026-12-31', 0);
        $this->assertSame(267.0, $l['due']);
        $this->assertSame([100.0, 100.0, 67.0], $this->amounts($l));
    }

    public function test_cut_over_is_the_termination_date_01_08_2026()
    {
        $c = $this->c(['eff' => '2026-07-01', 'start' => '2026-07-01', 'vto' => '2027-06-30', 'rent' => '300', 'value' => '3600']);
        // 20/07: old rule, 19 - 1 = 18 days -> 180
        $this->assertSame(180.0, L::build($this->with($c, '2026-07-20'), '2026-10-02', 0)['due']);
        // 01/08: new rule, July (30 days) + 01/08 (1 day) = 31 days -> 310
        $this->assertSame(310.0, L::build($this->with($c, '2026-08-01'), '2026-10-02', 0)['due']);
    }

    public function test_terminated_on_the_start_date_owes_nothing_under_either_rule()
    {
        $this->assertSame(0.0, L::build($this->c(['term_date' => '2026-01-01']), '2026-08-31', 0)['due']);
        $c = $this->c(['eff' => '2026-09-01', 'start' => '2026-09-01', 'vto' => '2027-08-31', 'term_date' => '2026-09-01']);
        $this->assertSame(0.0, L::build($c, '2026-10-02', 0)['due']);
    }

    private function with($c, $termDate)
    {
        $x = clone $c;
        $x->term_date = $termDate;
        return $x;
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
        $this->assertSame(460.0, L::build($this->c(['start' => null, 'term_date' => '2026-05-20']), '2026-08-31', 0)['due']);
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

    public function test_thousands_commas_in_value_and_rent_are_ignored()
    {
        $c = $this->c(['value' => '1,200', 'rent' => '1,000', 'vto' => '2026-12-31']);
        $a = L::build($c, '2026-12-31', 0);
        $b = L::build($this->c(['value' => '1200', 'rent' => '1000']), '2026-12-31', 0);
        $this->assertSame(1200.0, $a['due']);
        $this->assertSame($b, $a);
    }

    public function test_negative_received_counts_as_zero_everywhere()
    {
        $l = L::build($this->c(), '2026-08-31', -50);
        $this->assertSame(0.0, $l['received']);
        $this->assertSame($l['due'], $l['balance']);
        $this->assertEquals($l['balance'], array_sum(array_column($l['lines'], 'unpaid')), '', 0.0005);
    }
}
