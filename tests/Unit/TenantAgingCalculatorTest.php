<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\TenantAgingCalculator;

class TenantAgingCalculatorTest extends TestCase
{
    private function row(array $overrides = [])
    {
        return (object) array_merge([
            'start_date'       => '2026-01-01',
            'lastpaid_till'    => null,
            'termination_date' => null,
            'rentper_month'    => 100,
            'netamtdue'        => 0,
        ], $overrides);
    }

    public function test_bucket_windows_are_whole_calendar_months_back_from_as_on_date()
    {
        $b = TenantAgingCalculator::buckets('2026-09-20');

        $this->assertSame(['current', 'b31_60', 'b61_90', 'b91_120', 'b121_180', 'b180plus'], array_keys($b));
        $this->assertSame('2026-09-01', $b['current']['from']);
        $this->assertSame('2026-09-20', $b['current']['to']);
        $this->assertSame('2026-08-01', $b['b31_60']['from']);
        $this->assertSame('2026-08-31', $b['b31_60']['to']);
        $this->assertSame('2026-07-01', $b['b61_90']['from']);
        $this->assertSame('2026-07-31', $b['b61_90']['to']);
        $this->assertSame('2026-06-01', $b['b91_120']['from']);
        $this->assertSame('2026-06-30', $b['b91_120']['to']);
        $this->assertSame('2026-04-01', $b['b121_180']['from']);
        $this->assertSame('2026-05-31', $b['b121_180']['to']);
        $this->assertNull($b['b180plus']['from']);
        $this->assertSame('2026-03-31', $b['b180plus']['to']);
    }

    public function test_bucket_windows_are_contiguous_across_a_year_boundary()
    {
        $b = TenantAgingCalculator::buckets('2027-02-10');

        $this->assertSame('2027-02-01', $b['current']['from']);
        $this->assertSame('2027-01-31', $b['b31_60']['to']);
        $this->assertSame('2026-12-01', $b['b61_90']['from']);
        $this->assertSame('2026-11-30', $b['b91_120']['to']);
        $this->assertSame('2026-09-01', $b['b121_180']['from']);
        $this->assertSame('2026-08-31', $b['b180plus']['to']);
    }

    public function test_zero_balance_gives_all_zero_buckets()
    {
        $out = TenantAgingCalculator::age($this->row(['netamtdue' => 0]), '2026-09-16');
        $this->assertSame(0.0, array_sum($out));
    }

    public function test_never_paid_contract_spreads_each_month_by_its_age()
    {
        // Start 01/06, as on 16/09: months due 01/06 (3 months back), 01/07 (2), 01/08 (1), 01/09 (current)
        $out = TenantAgingCalculator::age($this->row([
            'start_date' => '2026-06-01', 'rentper_month' => 100, 'netamtdue' => 400,
        ]), '2026-09-16');

        $this->assertSame([
            'current'  => 100.0,
            'b31_60'   => 100.0,
            'b61_90'   => 100.0,
            'b91_120'  => 100.0,
            'b121_180' => 0.0,
            'b180plus' => 0.0,
        ], $out);
    }

    public function test_unpaid_period_starts_the_day_after_lastpaid_till()
    {
        // Paid till 31/07 -> unpaid from 01/08 (previous month) and 01/09 (current month)
        $out = TenantAgingCalculator::age($this->row([
            'start_date' => '2026-01-01', 'lastpaid_till' => '2026-07-31',
            'rentper_month' => 100, 'netamtdue' => 200,
        ]), '2026-09-16');

        $this->assertSame(100.0, $out['current']);
        $this->assertSame(100.0, $out['b31_60']);
        $this->assertSame(200.0, array_sum($out));
    }

    public function test_old_arrears_land_in_180_plus()
    {
        $out = TenantAgingCalculator::age($this->row([
            'start_date' => '2025-10-01', 'rentper_month' => 100, 'netamtdue' => 1200,
        ]), '2026-09-16');

        // Oct 25 .. Mar 26 = the 6 months that are 6+ months before Sep 26 (180+ = up to 31/03/2026)
        $this->assertSame(600.0, $out['b180plus']);
        $this->assertSame(1200.0, array_sum($out));
    }

    public function test_partial_payment_reduces_oldest_bucket_first()
    {
        // 4 unpaid months (400) but balance is only 250 -> 150 knocked off oldest buckets
        $out = TenantAgingCalculator::age($this->row([
            'start_date' => '2026-06-01', 'rentper_month' => 100, 'netamtdue' => 250,
        ]), '2026-09-16');

        $this->assertSame(0.0, $out['b91_120']);
        $this->assertSame(50.0, $out['b61_90']);
        $this->assertSame(100.0, $out['b31_60']);
        $this->assertSame(100.0, $out['current']);
        $this->assertSame(250.0, array_sum($out));
    }

    public function test_extra_balance_is_added_to_oldest_bucket()
    {
        $out = TenantAgingCalculator::age($this->row([
            'start_date' => '2026-06-01', 'rentper_month' => 100, 'netamtdue' => 430,
        ]), '2026-09-16');

        $this->assertSame(130.0, $out['b91_120']);
        $this->assertSame(430.0, array_sum($out));
    }

    public function test_month_due_late_in_previous_month_is_still_previous_month_bucket()
    {
        // Due 28/08, as on 01/09: only 4 days old but it belongs to August -> 31 - 60
        $out = TenantAgingCalculator::age($this->row([
            'start_date' => '2026-08-28', 'rentper_month' => 100, 'netamtdue' => 100,
        ]), '2026-09-01');

        $this->assertSame(0.0, $out['current']);
        $this->assertSame(100.0, $out['b31_60']);
    }

    public function test_terminated_contract_stops_accruing_at_termination_date()
    {
        // Terminated 15/07: only Jun and Jul are due, but balance says 200
        $out = TenantAgingCalculator::age($this->row([
            'start_date' => '2026-06-01', 'termination_date' => '2026-07-15',
            'rentper_month' => 100, 'netamtdue' => 200,
        ]), '2026-09-16');

        $this->assertSame(0.0, $out['current']);
        $this->assertSame(0.0, $out['b31_60']);
        $this->assertSame(100.0, $out['b61_90']);
        $this->assertSame(100.0, $out['b91_120']);
    }

    public function test_balance_with_no_derivable_unpaid_month_goes_to_current()
    {
        // Paid till after as-on date but still has a balance (data quirk)
        $out = TenantAgingCalculator::age($this->row([
            'lastpaid_till' => '2026-10-31', 'rentper_month' => 100, 'netamtdue' => 75.5,
        ]), '2026-09-16');

        $this->assertSame(75.5, $out['current']);
        $this->assertSame(75.5, array_sum($out));
    }

    public function test_amounts_are_rounded_to_three_decimals()
    {
        $out = TenantAgingCalculator::age($this->row([
            'start_date' => '2026-09-01', 'rentper_month' => 33.33333, 'netamtdue' => 33.33333,
        ]), '2026-09-16');

        $this->assertSame(33.333, $out['current']);
    }
}
