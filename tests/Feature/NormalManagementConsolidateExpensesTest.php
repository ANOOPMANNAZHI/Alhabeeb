<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Modules\Masters\Entities\Building;
use Modules\BackOffice\Exports\NormalManagementConsolidateSheet;

/** Consolidated sheet: expense rows in the user's fixed order (03/10/2026). */
class NormalManagementConsolidateExpensesTest extends TestCase
{
    const ORDER = [
        'Municipal Tax',
        'Management Fees',
        'Value Added Tax (VAT)',
        'Annual Maint. Cont. - A C, FAS, Lift & Others',
        'Sewage Removal',
        'Common Area E/Water Charges',
        'Legal Expenses',
        'A C, FAS, Lift, etc. Repair Charges - (a)',
        'Electric, Plumbing, Repair & Maint - (b)',
    ];

    private function exp($name, $amount, $inhouse = false)
    {
        return (object) ['expense_name' => $name, 'expense_amount' => $amount, 'inhouse' => $inhouse];
    }

    public function test_expense_heads_land_on_the_right_rows()
    {
        $lines = NormalManagementConsolidateSheet::expenseLines(['expenses' => [
            $this->exp('MUNICIPAL TAX', 100),
            $this->exp('A C MAINTENANCE', 50),                  // sub-contractor -> AMC
            $this->exp('A C MAINTENANCE', 7, true),             // in-house -> (a)
            $this->exp('LIFT & ESCALATORS MAINT.', 30),
            $this->exp('CIT - FIRE ALARAM SYSTEM MAINT.', 4, 't'),
            $this->exp('DEWATERING', 20),
            $this->exp('ELECTRICITY & WATER', 15),
            $this->exp('ROUTINE & MAINTENANCE EXPENSES', 9, true),
            $this->exp('ROUTINE & MAINTENANCE EXPENSES', 11),
            $this->exp('CIT - ROUTINE & MAINTENANCE EXPENSES', 1),
            $this->exp('CLEANING', 500),                        // not on the sheet
            $this->exp('MUNICIPAL TAX PENALTY', 500),           // not on the sheet
            $this->exp('LEASE RENT', 500),                      // not on the sheet
        ], 'cleaning_charge' => 999, 'facility_management_fee' => 999]);

        $this->assertSame(self::ORDER, array_keys($lines));
        $this->assertEquals(100, $lines['Municipal Tax']);
        $this->assertEquals(0, $lines['Management Fees']);
        $this->assertEquals(0, $lines['Value Added Tax (VAT)']);
        $this->assertEquals(80, $lines['Annual Maint. Cont. - A C, FAS, Lift & Others']);
        $this->assertEquals(20, $lines['Sewage Removal']);
        $this->assertEquals(15, $lines['Common Area E/Water Charges']);
        $this->assertEquals(0, $lines['Legal Expenses']);
        $this->assertEquals(11, $lines['A C, FAS, Lift, etc. Repair Charges - (a)']);
        $this->assertEquals(21, $lines['Electric, Plumbing, Repair & Maint - (b)']);
        $this->assertEquals(247, array_sum($lines));
    }

    public function test_excel_lists_the_rows_in_order_and_totals_them()
    {
        $b = Building::where('building_name', 'ilike', 'Aashirwad%')->first();
        if (!$b) {
            $this->markTestSkipped('Aashirwad is not in this database');
        }
        $this->actingAs(User::find(1));
        $res = $this->post(route('normalManagementReportV2Generate'), ['building_id' => $b->id, 'month' => 'all', 'year' => 2026]);
        $res->assertOk();
        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($res->baseResponse->getFile()->getPathname())->getSheetByName('Consolidate');
        $this->assertNotNull($sheet, 'Consolidate sheet');
        $rows = $sheet->toArray(null, true, false, false);

        $start = null;
        foreach ($rows as $i => $r) {
            if (trim((string) $r[2]) === 'EXPENSES OVERVIEW') {
                $start = $i + 1;
                break;
            }
        }
        $this->assertNotNull($start, 'EXPENSES OVERVIEW row');
        $labels = array_map(function ($r) { return trim((string) $r[1]); }, array_slice($rows, $start, count(self::ORDER) + 1));
        $this->assertSame(array_merge(self::ORDER, ['Total Expenses']), $labels);

        // May (col G): Total Expenses = sum of the 9 rows
        $num = function ($v) { return is_numeric($v) ? (float) $v : 0.0; };
        $sum = 0.0;
        foreach (array_slice($rows, $start, count(self::ORDER)) as $r) {
            $sum += $num($r[6]);
        }
        $this->assertEqualsWithDelta($sum, $num($rows[$start + count(self::ORDER)][6]), 0.001);
        $this->assertGreaterThan(0, $sum, 'Aashirwad has expenses in May 2026');

        // YTD (col O) from the Generate path's own query copy = the stream path's figures
        $data = app(\Modules\BackOffice\Http\Controllers\BackOfficeReportController::class)
            ->buildNormalManagementMonthData($b, 2026, range(1, 12));
        $ytd = array_fill_keys(self::ORDER, 0.0);
        foreach (range(1, 12) as $m) {
            foreach (NormalManagementConsolidateSheet::expenseLines($data[$m] ?? []) as $k => $v) {
                $ytd[$k] += $v;
            }
        }
        foreach (self::ORDER as $i => $label) {
            $this->assertEqualsWithDelta($ytd[$label], $num($rows[$start + $i][14]), 0.001, $label);
        }
    }
}
