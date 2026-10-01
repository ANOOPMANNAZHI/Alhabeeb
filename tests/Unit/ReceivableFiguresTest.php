<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\ReceivableFigures as RF;

class ReceivableFiguresTest extends TestCase
{
    /** One contract row as the figures query returns it. */
    private function row(array $o = [])
    {
        return (object) array_merge([
            'id' => 1, 'eff' => '2026-01-01', 'start' => '2026-01-01', 'vto' => '2026-12-31', 'rent' => '100', 'value' => '1200',
            'term_date' => null, 'received' => '0', 'paid_till' => null, 'last_received' => null, 'last_amount' => null,
        ], $o);
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
        $this->assertSame('2026-06-30', RF::asOnDate(8, 2026, '2026-10-01'));
        $this->assertSame('2025-11-30', RF::asOnDate(1, 2026, '2026-10-01'));
        $this->assertSame('2024-02-29', RF::asOnDate(4, 2024, '2026-10-01'));
    }

    public function test_cutoff_never_after_today()
    {
        $this->assertSame('2026-10-01', RF::asOnDate(12, 2026, '2026-10-01'));   // Dec -> 31 Oct, capped
    }

    public function test_due_counts_whole_months_from_the_effective_month_to_the_cutoff()
    {
        // Jan..Aug = 8 months x 100
        $this->assertSame(800.0, RF::dueAsOf($this->row(), '2026-08-31'));
        // effective mid-month still counts that month
        $this->assertSame(300.0, RF::dueAsOf($this->row(['eff' => '2026-06-15']), '2026-08-31'));
    }

    public function test_due_stops_at_the_contract_end_and_never_exceeds_its_value()
    {
        // ended 31/05: Jan..May only
        $this->assertSame(500.0, RF::dueAsOf($this->row(['vto' => '2026-05-31']), '2026-08-31'));
        // value smaller than months x rent
        $this->assertSame(250.0, RF::dueAsOf($this->row(['value' => '250']), '2026-08-31'));
    }

    public function test_terminated_by_the_cutoff_uses_the_v2_termination_rule()
    {
        // terminated on the end date: full contract value
        $this->assertSame(1200.0, RF::dueAsOf($this->row(['vto' => '2026-06-30', 'term_date' => '2026-06-30']), '2026-08-31'));
        // terminated early: rent / 30 x (termination - start - 1) days, rounded: 31/05 - 01/01 = 150, minus 1 = 149 days
        $this->assertSame((float) round(149 * (100 / 30)), RF::dueAsOf($this->row(['term_date' => '2026-05-31']), '2026-08-31'));
        // terminated after the cutoff: still counted like an active contract up to the cutoff
        $this->assertSame(800.0, RF::dueAsOf($this->row(['term_date' => '2026-09-15']), '2026-08-31'));
    }

    public function test_build_keeps_only_agreements_owing_at_the_cutoff()
    {
        $rows = [
            $this->row(['id' => 11, 'received' => '500', 'paid_till' => '2026-05-31', 'last_received' => '2026-05-02', 'last_amount' => '200']), // owes 300
            $this->row(['id' => 12, 'received' => '800', 'paid_till' => '2026-08-31', 'last_received' => '2026-08-01']), // paid up
            $this->row(['id' => 13, 'eff' => '2026-09-01', 'received' => '100']),                                         // not started
        ];
        $out = RF::build($rows, '2026-08-31');
        $this->assertSame([11], array_keys($out));
        $this->assertSame(['due' => 800.0, 'received' => 500.0, 'balance' => 300.0, 'last_received' => '2026-05-02', 'last_amount' => 200.0, 'paid_till' => '2026-05-31'], $out[11]);
    }

    public function test_cache_key_is_versioned()
    {
        $this->assertSame('receivable_followup:v4:2026-08-31', RF::key('2026-08-31'));
    }
}
