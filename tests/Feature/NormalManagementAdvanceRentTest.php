<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Modules\Masters\Entities\Building;
use Modules\BackOffice\Http\Controllers\BackOfficeReportController;

/**
 * Normal Management Report V2: rent paid before a contract starts counts as
 * collection in the month the money was received (user, 03/10/2026).
 * Aashirwad 36: TAG2600751 starts 01/05/2026; May's rent (150) was paid on 29/04/2026.
 */
class NormalManagementAdvanceRentTest extends TestCase
{
    private function building()
    {
        $b = Building::where('building_name', 'ilike', 'Aashirwad%')->first();
        if (!$b) {
            $this->markTestSkipped('Aashirwad is not in this database');
        }
        return $b;
    }

    private function rows($month, $unitNo)
    {
        $this->actingAs(User::find(1));
        $data = app(BackOfficeReportController::class)->buildNormalManagementMonthData($this->building(), 2026, [$month]);
        return array_values(array_filter($data[$month]['units'], function ($u) use ($unitNo) { return (string) $u->unit_no === $unitNo; }));
    }

    public function test_advance_paid_before_the_contract_starts_is_april_collection()
    {
        $rows = $this->rows(4, '36');
        $incoming = array_values(array_filter($rows, function ($u) { return stripos((string) $u->tenant_name, 'Arun') !== false; }));
        $this->assertCount(1, $incoming);
        $u = $incoming[0];
        $this->assertStringContainsString('(from 01/05/2026)', $u->tenant_name);
        $this->assertEquals(150.0, (float) $u->collection_amount);
        $this->assertEquals(0.0, (float) $u->rent_per_month);
        $this->assertEquals(0.0, (float) $u->income_amount);
        $this->assertEquals(0.0, (float) $u->outstanding_amount);
        // the unit was still vacant in April: no second row with figures
        foreach ($rows as $r) {
            if ($r !== $u) {
                $this->assertSame('VACANT', $r->tenant_name);
            }
        }
    }

    public function test_may_is_unchanged()
    {
        $rows = $this->rows(5, '36');
        $this->assertCount(1, $rows);
        $this->assertSame('Arun Prasath Kalyana Sundaram', $rows[0]->tenant_name);
        $this->assertEquals(150.0, (float) $rows[0]->rent_per_month);
        $this->assertEquals(150.0, (float) $rows[0]->collection_amount);   // the receipt of 10/05 (June's rent)
    }

    public function test_generate_excel_shows_the_advance_in_april()
    {
        $this->actingAs(User::find(1));
        $res = $this->post(route('normalManagementReportV2Generate'), ['building_id' => $this->building()->id, 'month' => 4, 'year' => 2026]);
        $res->assertOk();
        $row = null;
        foreach (\PhpOffice\PhpSpreadsheet\IOFactory::load($res->baseResponse->getFile()->getPathname())->getAllSheets() as $sh) {
            foreach ($sh->toArray(null, true, false, false) as $r) {
                if (trim((string) $r[0]) === '36' && stripos(implode('|', array_map('strval', $r)), 'Arun') !== false) {
                    $row = $r;
                    break 2;
                }
            }
        }
        $this->assertNotNull($row, 'incoming tenant row for unit 36 in April');
        $this->assertStringContainsString(150.0, array_map(function ($v) { return is_numeric($v) ? (float) $v : null; }, $row));
    }
}
