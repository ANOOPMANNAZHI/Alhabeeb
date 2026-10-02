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
