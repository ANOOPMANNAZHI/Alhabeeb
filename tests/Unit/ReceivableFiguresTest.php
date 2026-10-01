<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\ReceivableFigures as RF;

class ReceivableFiguresTest extends TestCase
{
    private function row($no, $due, $received, $balance, $receipt = null)
    {
        return (object) ['contract_no' => $no, 'totaldue' => $due, 'amount_received' => $received, 'netamtdue' => $balance, 'receipt_date' => $receipt];
    }

    public function test_last_month_and_as_on_date()
    {
        $this->assertSame([9, 2026], RF::lastMonth('2026-10-01'));
        $this->assertSame([12, 2025], RF::lastMonth('2026-01-15'));
        $this->assertSame('2026-09-30', RF::asOnDate(9, 2026, '2026-10-01'));
        $this->assertSame('2024-02-29', RF::asOnDate(2, 2024, '2026-10-01'));
    }

    public function test_as_on_date_caps_at_today()
    {
        $this->assertSame('2026-10-01', RF::asOnDate(10, 2026, '2026-10-01'));
        $this->assertSame('2026-10-01', RF::asOnDate(12, 2026, '2026-10-01'));
    }

    public function test_build_drops_legal_zero_unknown_and_duplicates()
    {
        $rows = [
            $this->row('A1', '1000', '400', '600', '2026-08-01'),
            $this->row('A1', '1000', '400', '600', '2026-08-01'),   // exact repeat
            $this->row('L1', '500', '0', '500'),                     // legal
            $this->row('Z1', '300', '300', '0'),                     // nothing owed
            $this->row('X9', '200', '0', '200'),                     // no matching contract
        ];
        $out = RF::build($rows, ['L1'], ['A1' => 11, 'L1' => 12, 'Z1' => 13]);
        $this->assertSame([11], array_keys($out));
        $this->assertSame(['due' => 1000.0, 'received' => 400.0, 'balance' => 600.0, 'last_received' => '2026-08-01'], $out[11]);
    }

    public function test_build_keeps_null_receipt_date()
    {
        $out = RF::build([$this->row('B2', '750.5', null, '750.5')], [], ['B2' => 7]);
        $this->assertSame(['due' => 750.5, 'received' => 0.0, 'balance' => 750.5, 'last_received' => null], $out[7]);
    }
}
