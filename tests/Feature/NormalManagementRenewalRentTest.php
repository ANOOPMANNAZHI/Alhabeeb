<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Modules\Masters\Entities\Building;
use Modules\BackOffice\Http\Controllers\BackOfficeReportController;

/**
 * Normal Management Report V2: a contract starting mid-month is prorated only for a
 * tenant moving into an empty unit. A renewal follows a contract that was running on
 * the 1st, even though that contract is inactive now (user, 03/10/2026).
 */
class NormalManagementRenewalRentTest extends TestCase
{
    private function unitRow(Building $b, $month, $unitNo)
    {
        $this->actingAs(User::find(1));
        $data = app(BackOfficeReportController::class)->buildNormalManagementMonthData($b, 2026, [$month]);
        foreach ($data[$month]['units'] as $u) {
            if ((string) $u->unit_no === (string) $unitNo) {
                return $u;
            }
        }
        return null;
    }

    /** Aashirwad S 713, Premier World LLC: TAG2501384 to 15/05/2026, renewed TAG2600815 from 16/05/2026, rent 260. */
    public function test_renewal_starting_mid_month_gets_the_full_month()
    {
        $b = Building::where('building_name', 'ilike', 'Aashirwad%')->first();
        if (!$b) {
            $this->markTestSkipped('Aashirwad is not in this database');
        }
        $u = $this->unitRow($b, 5, 'S 713');
        $this->assertNotNull($u);
        $this->assertEquals(260.0, (float) $u->rent_per_month);
        $this->assertEquals(260.0, (float) $u->income_amount);
    }

    /** The older Generate path has its own copy of the query: its Excel shows the full month too. */
    public function test_generate_excel_shows_the_full_month_for_the_renewal()
    {
        $b = Building::where('building_name', 'ilike', 'Aashirwad%')->first();
        if (!$b) {
            $this->markTestSkipped('Aashirwad is not in this database');
        }
        $this->actingAs(User::find(1));
        $res = $this->post(route('normalManagementReportV2Generate'), ['building_id' => $b->id, 'month' => 5, 'year' => 2026]);
        $res->assertOk();
        $row = null;
        foreach (\PhpOffice\PhpSpreadsheet\IOFactory::load($res->baseResponse->getFile()->getPathname())->getAllSheets() as $sh) {
            foreach ($sh->toArray(null, true, false, false) as $r) {
                if (trim((string) $r[0]) === 'S 713' && stripos(implode('|', array_map('strval', $r)), 'Premier') !== false) {
                    $row = $r;
                    break 2;
                }
            }
        }
        $this->assertNotNull($row, 'S 713 row in the May sheet');
        $this->assertStringContainsString(260.0, array_map(function ($v) { return is_numeric($v) ? (float) $v : null; }, $row));
        $this->assertStringNotContainsString(130.0, array_map(function ($v) { return is_numeric($v) ? (float) $v : null; }, $row));
    }

    public function test_new_tenant_moving_into_an_empty_unit_mid_month_is_still_prorated()
    {
        // an approved contract starting on the 16th of a 2026 month, in a unit with no contract running on the 1st
        $c = DB::selectOne("
            SELECT tc.id, tc.unit_id, u.unit_no, u.building_id, tc.tenant_contract_rent AS rent,
                   EXTRACT(MONTH FROM tc.tenant_contract_start_date)::int AS m
              FROM tenant_contracts tc JOIN units u ON u.id = tc.unit_id AND u.unit_status = 1
              JOIN buildings b ON b.id = u.building_id AND b.building_status = '1'
             WHERE tc.work_flow_processes_code = 108 AND tc.tenant_contract_status = 1
               AND EXTRACT(YEAR FROM tc.tenant_contract_start_date) = 2026
               AND EXTRACT(DAY FROM tc.tenant_contract_start_date) = 16
               AND tc.tenant_contract_rent > 0
               AND NOT EXISTS (
                   SELECT 1 FROM tenant_contracts p
                    WHERE p.unit_id = tc.unit_id AND p.id <> tc.id
                      AND p.tenant_contract_start_date <= date_trunc('month', tc.tenant_contract_start_date)
                      AND COALESCE(p.tenant_contract_valid_to_date, '2999-12-31') >= date_trunc('month', tc.tenant_contract_start_date))
             ORDER BY tc.id DESC LIMIT 1");
        if (!$c) {
            $this->markTestSkipped('no new-occupancy contract starting on the 16th in 2026');
        }
        $u = $this->unitRow(Building::find($c->building_id), $c->m, $c->unit_no);
        $this->assertNotNull($u);
        // 16th to month end = 15 days of 30
        $this->assertEquals(round($c->rent * 15 / 30, 2), (float) $u->rent_per_month, 'unit ' . $c->unit_no);
    }
}
