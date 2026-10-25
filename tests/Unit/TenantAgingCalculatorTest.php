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
