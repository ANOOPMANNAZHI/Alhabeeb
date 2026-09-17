<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\TerminationDuesSettlement as S;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;

class TerminationDuesSettlementTest extends TestCase
{
    private function lines()
    {
        return [
            ['id' => 1, 'category' => Cat::RENT,        'owner_team' => 'backoffice',  'description' => 'Outstanding rent',   'amount' => 500.0],
            ['id' => 2, 'category' => Cat::MUNICIPAL,   'owner_team' => 'backoffice',  'description' => 'Municipal tax',      'amount' => 45.0],
            ['id' => 3, 'category' => Cat::EW,          'owner_team' => 'backoffice',  'description' => 'Electricity & water','amount' => 80.0],
            ['id' => 4, 'category' => Cat::MAINTENANCE, 'owner_team' => 'maintenance', 'description' => 'Painting',           'amount' => 120.0],
            ['id' => 5, 'category' => Cat::MAINTENANCE, 'owner_team' => 'maintenance', 'description' => 'Access card',        'amount' => 5.0],
        ];
    }

    private function src($kind, $id, $amount, $category, $date = '2026-09-01', $code = null)
    {
        return ['key' => S::key($kind, $id), 'kind' => $kind, 'id' => $id, 'ref' => strtoupper($kind) . $id,
                'date' => $date, 'amount' => $amount, 'category' => $category, 'account_code' => $code,
                'description' => '', 'url' => '#'];
    }

    public function test_nothing_settled_is_open_with_full_balance()
    {
        $r = S::compute($this->lines(), [], []);

        $this->assertSame('open', $r['status']);
        $this->assertSame(750.0, $r['total']['owed']);
        $this->assertSame(750.0, $r['total']['balance']);
        $this->assertSame(625.0, $r['teams']['backoffice']['balance']);
        $this->assertSame(125.0, $r['teams']['maintenance']['balance']);
    }

    public function test_rent_receipt_and_deposit_deduction_land_in_separate_columns()
    {
        $r = S::compute($this->lines(), [
            $this->src('deposit_deduction', 900, 300.0, Cat::RENT),
            $this->src('rent_receipt', 100, 200.0, Cat::RENT),
        ], []);

        $rent = $r['lines'][1];
        $this->assertSame(300.0, $rent['deposit']);
        $this->assertSame(200.0, $rent['receipts']);
        $this->assertSame(0.0, $rent['balance']);
        $this->assertSame('partial', $r['status']);
        $this->assertSame(250.0, $r['total']['balance']);
    }

    public function test_category_is_filled_oldest_line_first_and_surplus_is_reported()
    {
        $r = S::compute($this->lines(), [
            $this->src('general_receipt_line', 50, 130.0, Cat::MAINTENANCE, '2026-09-02', '41110'),
        ], []);

        $this->assertSame(120.0, $r['lines'][4]['receipts']);
        $this->assertSame(5.0, $r['lines'][5]['receipts']);
        $this->assertSame(5.0, $r['over_collected'][Cat::MAINTENANCE]);
        $this->assertSame(0.0, $r['teams']['maintenance']['balance']);
    }

    public function test_sources_are_applied_in_date_order()
    {
        $r = S::compute($this->lines(), [
            $this->src('general_receipt_line', 52, 100.0, Cat::MAINTENANCE, '2026-09-05'),
            $this->src('general_receipt_line', 51, 30.0,  Cat::MAINTENANCE, '2026-09-01'),
        ], []);

        // 30 (Sep 1) then 100 (Sep 5): painting takes 30 + 90, access card takes 5, surplus 5
        $this->assertSame(120.0, $r['lines'][4]['receipts']);
        $this->assertSame(5.0, $r['lines'][5]['receipts']);
        $this->assertSame(5.0, $r['over_collected'][Cat::MAINTENANCE]);
        $this->assertSame('general_receipt_line:51', $r['allocations'][0]['source_key']);
    }

    public function test_unclassified_or_unmatched_sources_are_listed_not_applied()
    {
        $r = S::compute($this->lines(), [
            $this->src('general_receipt_line', 60, 20.0, null, '2026-09-01', '22301'),
            $this->src('general_receipt_line', 61, 20.0, Cat::OTHER),
        ], []);

        $this->assertCount(2, $r['unallocated']);
        $this->assertSame(750.0, $r['total']['balance']);
    }

    public function test_manual_allocation_pins_a_source_to_a_line_and_removes_it_from_auto()
    {
        $r = S::compute($this->lines(), [
            $this->src('general_receipt_line', 60, 20.0, null, '2026-09-01', '22301'),
        ], [
            ['id' => 1, 'source_type' => 'general_receipt_line', 'source_id' => 60, 'termination_dues_line_id' => 2, 'amount' => 20.0, 'remark' => 'tenant said tax'],
        ]);

        $this->assertSame([], $r['unallocated']);
        $this->assertSame(20.0, $r['lines'][2]['receipts']);
        $this->assertSame(25.0, $r['lines'][2]['balance']);
        $this->assertTrue($r['allocations'][0]['manual']);
    }

    public function test_waiver_counts_as_written_off_when_it_closes_the_balance()
    {
        $r = S::compute($this->lines(), [
            $this->src('rent_receipt', 100, 500.0, Cat::RENT),
            $this->src('deposit_deduction', 900, 125.0, Cat::MAINTENANCE),
            $this->src('deposit_deduction', 901, 80.0, Cat::EW),
        ], [
            ['id' => 2, 'source_type' => 'waiver', 'source_id' => null, 'termination_dues_line_id' => 2, 'amount' => 45.0, 'remark' => 'approved by FM'],
        ]);

        $this->assertSame(45.0, $r['lines'][2]['waived']);
        $this->assertSame(0.0, $r['total']['balance']);
        $this->assertSame(45.0, $r['total']['waived']);
        $this->assertSame('written_off', $r['status']);
    }

    public function test_fully_paid_without_waiver_is_settled()
    {
        $r = S::compute($this->lines(), [
            $this->src('rent_receipt', 100, 500.0, Cat::RENT),
            $this->src('general_receipt_line', 1, 45.0, Cat::MUNICIPAL),
            $this->src('general_receipt_line', 2, 80.0, Cat::EW),
            $this->src('general_receipt_line', 3, 125.0, Cat::MAINTENANCE),
        ], []);

        $this->assertSame('settled', $r['status']);
        $this->assertSame(750.0, $r['total']['settled']);
    }

    public function test_negative_discount_line_reduces_team_owed()
    {
        $lines = $this->lines();
        $lines[] = ['id' => 6, 'category' => Cat::MAINTENANCE, 'owner_team' => 'maintenance', 'description' => 'Maintenance discount', 'amount' => -25.0];

        $r = S::compute($lines, [], []);
        $this->assertSame(100.0, $r['teams']['maintenance']['owed']);
        $this->assertSame(725.0, $r['total']['owed']);
    }
}
