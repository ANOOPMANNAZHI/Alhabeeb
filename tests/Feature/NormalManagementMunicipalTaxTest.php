<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Modules\Masters\Entities\Building;
use Modules\BackOffice\Exports\NormalManagementMonthSheet;
use Modules\BackOffice\Exports\NormalManagementConsolidateSheet;
use Modules\BackOffice\Http\Controllers\BackOfficeReportController;

/**
 * Normal Management Report V2: municipal tax paid back by a tenant who left early is
 * the landlord's collection of that month, shown as "Add: Mun Tax F: <unit>" (user, 04/10/2026).
 * Aashirwad 43: Frangstin Praveen Kulandairaj, terminated 15/01/2026, contract to 15/04/2026,
 * paid 3 x 130 x 3% = 11.700 on 06/01/2026 (CPY2600415).
 */
class NormalManagementMunicipalTaxTest extends TestCase
{
    private function building()
    {
        $b = Building::where('building_name', 'ilike', 'Aashirwad%')->first();
        if (!$b) {
            $this->markTestSkipped('Aashirwad is not in this database');
        }
        $this->actingAs(User::find(1));
        return $b;
    }

    private function sheetRows($data, $m)
    {
        return (new NormalManagementMonthSheet($data[$m], $m, 2026, 'Aashirwad'))->array();
    }

    private function find(array $rows, $text)
    {
        foreach ($rows as $r) {
            if (trim((string) $r[2]) === $text) {
                return $r;
            }
        }
        return null;
    }

    public function test_january_shows_the_municipal_tax_row_and_counts_it_in_total_collection()
    {
        $b    = $this->building();
        $data = app(BackOfficeReportController::class)->buildNormalManagementMonthData($b, 2026, [1, 2]);

        $mt = array_values(array_filter($data[1]['mun_tax'], function ($r) { return (string) $r->unit_no === '43'; }));
        $this->assertCount(1, $mt);
        $this->assertSame('Add: Mun Tax F: 43', $mt[0]->description);
        $this->assertEqualsWithDelta(11.7, $mt[0]->amount, 0.001);

        $rows  = $this->sheetRows($data, 1);
        $row   = $this->find($rows, 'Add: Mun Tax F: 43');
        $this->assertNotNull($row, 'Add: Mun Tax row in January');
        $this->assertEqualsWithDelta(11.7, $row[7], 0.001);

        // TOTAL COLLECTION = unit rows + municipal tax rows
        $units = 0.0;
        foreach ($data[1]['units'] as $u) {
            $units += (float) $u->collection_amount;
        }
        $total = $this->find($rows, 'TOTAL COLLECTION');
        $this->assertEqualsWithDelta($units + NormalManagementConsolidateSheet::munTaxCollection($data[1]), $total[7], 0.001);

        // not in February (received in January)
        $this->assertNull($this->find($this->sheetRows($data, 2), 'Add: Mun Tax F: 43'));
    }

    /** The Generate path has its own query copy: same row in its January sheet. */
    public function test_generate_excel_has_the_row_too()
    {
        $b   = $this->building();
        $res = $this->post(route('normalManagementReportV2Generate'), ['building_id' => $b->id, 'month' => 1, 'year' => 2026]);
        $res->assertOk();
        $found = null;
        foreach (\PhpOffice\PhpSpreadsheet\IOFactory::load($res->baseResponse->getFile()->getPathname())->getAllSheets() as $sh) {
            foreach ($sh->toArray(null, true, false, false) as $r) {
                if (trim((string) $r[2]) === 'Add: Mun Tax F: 43') {
                    $found = $r;
                }
            }
        }
        $this->assertNotNull($found);
        $this->assertEqualsWithDelta(11.7, (float) $found[7], 0.001);
    }
}
