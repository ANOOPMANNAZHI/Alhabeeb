<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Modules\Masters\Entities\Building;
use Modules\BackOffice\Http\Controllers\BackOfficeReportController;

/** Normal Management Report V2 leaves legal cases out (user, 03/10/2026). */
class NormalManagementLegalTest extends TestCase
{
    private function monthData($buildingLike, array $months)
    {
        $this->actingAs(User::find(1));
        $b = Building::where('building_name', 'ilike', $buildingLike)->first();
        if (!$b) {
            $this->markTestSkipped($buildingLike . ' is not in this database');
        }
        return app(BackOfficeReportController::class)->buildNormalManagementMonthData($b, 2026, $months);
    }

    /** Aashirwad unit 42: Powerline (legal case) settlement receipt of 705 dated 01/01/2026. */
    public function test_legal_settlement_is_not_in_old_outstanding_or_collection()
    {
        $data = $this->monthData('Aashirwad%', [1]);
        foreach (array_merge($data[1]['old_outstanding'], $data[1]['units']) as $u) {
            $this->assertNotContains('Powerline', (string) $u->tenant_name, 'unit ' . $u->unit_no);
        }
    }

    /** The older Generate path keeps its own copy of the queries: same result in its Excel. */
    public function test_generate_excel_has_no_legal_settlement()
    {
        $b = Building::where('building_name', 'ilike', 'Aashirwad%')->first();
        if (!$b) {
            $this->markTestSkipped('Aashirwad is not in this database');
        }
        $this->actingAs(User::find(1));
        $res = $this->post(route('normalManagementReportV2Generate'), ['building_id' => $b->id, 'month' => 1, 'year' => 2026]);
        $res->assertOk();
        $path = $res->baseResponse->getFile()->getPathname();
        $text = '';
        foreach (\PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getAllSheets() as $sh) {
            foreach ($sh->toArray(null, true, true, false) as $row) {
                $text .= implode('|', array_map('strval', $row)) . "\n";
            }
        }
        $this->assertNotFalse(stripos($text, 'Aashirwad'), 'report for Aashirwad');
        $this->assertFalse(stripos($text, 'Powerline'), 'legal tenant Powerline in the Excel');
    }

    public function test_no_extra_rows_for_legal_tenants_whose_contract_ended()
    {
        // Buildings with legal cases: every unit appears once at most in the unit list
        $ids = DB::table('legal as l')->join('units as u', 'u.id', '=', 'l.unit_id')
            ->join('buildings as b', 'b.id', '=', 'u.building_id')->where('b.building_status', '1')->where('b.management_id', 2)
            ->distinct()->limit(3)->pluck('b.id');
        if ($ids->isEmpty()) {
            $this->markTestSkipped('no Normal building with legal cases');
        }
        $this->actingAs(User::find(1));
        foreach ($ids as $id) {
            $data = app(BackOfficeReportController::class)->buildNormalManagementMonthData(Building::find($id), 2026, [9]);
            $own = array_filter($data[9]['units'], function ($u) { return empty($u->is_incoming); }); // incoming tenants have their own row
            $nos = array_map(function ($u) { return (string) $u->unit_no; }, $own);
            $this->assertSame(count($nos), count(array_unique($nos)), 'building ' . $id . ' lists a unit twice');
            foreach ($data[9]['old_outstanding'] as $u) {
                $legal = DB::table('legal as l')->join('tenant_contracts as tc', 'tc.tenant_id', '=', 'l.tenant_id')
                    ->join('tenant as t', 't.id', '=', 'l.tenant_id')->where('t.tenant_name', $u->tenant_name)->exists();
                $this->assertFalse($legal, 'legal tenant ' . $u->tenant_name . ' in old outstanding of building ' . $id);
                $this->assertNotRegExp('/-u?\.?legal/i', (string) $u->tenant_name);
            }
        }
    }
}
