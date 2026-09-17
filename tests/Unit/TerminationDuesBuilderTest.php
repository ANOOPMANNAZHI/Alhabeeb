<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\TerminationDuesBuilder;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;

/**
 * Snapshot of what a terminated tenant owes, from the inspection checklist
 * and the termination amounts. Pure: arrays in, arrays out.
 */
class TerminationDuesBuilderTest extends TestCase
{
    private function input(array $overrides = [])
    {
        return array_merge([
            'rent_os'              => 500.0,
            'checklist_other'      => [
                ['id' => 11, 'name' => 'Muncipal Tax',      'amount' => 45.0],
                ['id' => 12, 'name' => 'Any Other Charges', 'amount' => 10.0],
            ],
            'checklist_works'      => [
                ['id' => 21, 'description' => 'Painting - Full flat', 'amount' => 120.0],
                ['id' => 22, 'description' => 'Access card',          'amount' => 5.0],
            ],
            'elec_water'           => 80.0,
            'maintenance_discount' => 0.0,
        ], $overrides);
    }

    private function byCategory(array $lines, $category)
    {
        return array_values(array_filter($lines, function ($l) use ($category) { return $l['category'] === $category; }));
    }

    public function test_builds_one_line_per_charge_with_owner_team()
    {
        $lines = TerminationDuesBuilder::build($this->input());

        $this->assertCount(6, $lines);
        $rent = $this->byCategory($lines, Cat::RENT);
        $this->assertSame(500.0, $rent[0]['amount']);
        $this->assertSame(Cat::TEAM_BACKOFFICE, $rent[0]['owner_team']);
        $this->assertSame('computed', $rent[0]['source_type']);

        $mun = $this->byCategory($lines, Cat::MUNICIPAL);
        $this->assertSame(45.0, $mun[0]['amount']);
        $this->assertSame('termination_checklist', $mun[0]['source_type']);
        $this->assertSame(11, $mun[0]['source_id']);

        $maint = $this->byCategory($lines, Cat::MAINTENANCE);
        $this->assertCount(2, $maint);
        $this->assertSame('Painting - Full flat', $maint[0]['description']);
        $this->assertSame(Cat::TEAM_MAINTENANCE, $maint[0]['owner_team']);

        $this->assertSame(760.0, TerminationDuesBuilder::total($lines));
    }

    public function test_inspector_rent_row_overrides_computed_outstanding()
    {
        $in = $this->input();
        $in['checklist_other'][] = ['id' => 13, 'name' => 'Rent', 'amount' => 450.0];

        $rent = $this->byCategory(TerminationDuesBuilder::build($in), Cat::RENT);
        $this->assertCount(1, $rent);
        $this->assertSame(450.0, $rent[0]['amount']);
        $this->assertSame('termination_checklist', $rent[0]['source_type']);
        $this->assertSame(13, $rent[0]['source_id']);
    }

    public function test_zero_and_negative_amounts_are_skipped()
    {
        $lines = TerminationDuesBuilder::build($this->input([
            'rent_os'         => -12.5,
            'elec_water'      => 0,
            'checklist_other' => [['id' => 11, 'name' => 'Muncipal Tax', 'amount' => 0]],
            'checklist_works' => [['id' => 21, 'description' => 'Cleaning', 'amount' => '0.000']],
        ]));

        $this->assertSame([], $lines);
    }

    public function test_maintenance_discount_becomes_a_negative_maintenance_line_capped_at_the_maintenance_total()
    {
        $lines = TerminationDuesBuilder::build($this->input(['maintenance_discount' => 200.0]));

        $maint = $this->byCategory($lines, Cat::MAINTENANCE);
        $this->assertCount(3, $maint);
        $this->assertSame('Maintenance discount', $maint[2]['description']);
        $this->assertSame(-125.0, $maint[2]['amount']); // 120 + 5 = 125, so the 200 discount is capped
        $this->assertSame(635.0, TerminationDuesBuilder::total($lines));
    }

    public function test_amounts_are_rounded_to_three_decimals_and_strings_accepted()
    {
        $lines = TerminationDuesBuilder::build($this->input([
            'rent_os' => '1,234.5678', 'checklist_other' => [], 'checklist_works' => [], 'elec_water' => '0',
        ]));

        $this->assertCount(1, $lines);
        $this->assertSame(1234.568, $lines[0]['amount']);
    }

    public function test_line_order_is_sequential()
    {
        $lines = TerminationDuesBuilder::build($this->input());
        $this->assertSame(range(1, 6), array_column($lines, 'line_order'));
    }
}
