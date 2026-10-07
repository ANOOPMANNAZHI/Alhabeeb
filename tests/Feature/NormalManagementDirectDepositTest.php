<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Masters\Entities\Building;
use Modules\BackOffice\Exports\NormalManagementMonthSheet;
use Modules\BackOffice\Exports\NormalManagementConsolidateSheet;
use Modules\BackOffice\Http\Controllers\BackOfficeReportController;

/**
 * Normal Management Report V2: rent receipts marked "Direct deposit to landlord" stay on the
 * unit row but get a "Less: D.Deposit - <unit>" row, so TOTAL COLLECTION, the consolidated
 * Collection and Amount Transfer to Land Lord are net. Management fee keeps the full collection
 * (user, 07/10/2026).
 */
class NormalManagementDirectDepositTest extends TestCase
{
    use DatabaseTransactions;

    const MONTH = 9;

    private function building()
    {
        $b = Building::where('building_name', 'ilike', 'Aashirwad%')->first();
        if (!$b) {
            $this->markTestSkipped('Aashirwad is not in this database');
        }
        $this->actingAs(User::find(1));
        return $b;
    }

    private function monthData($b)
    {
        return app(BackOfficeReportController::class)->buildNormalManagementMonthData($b, 2026, [self::MONTH]);
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

    /** Marks one September rent receipt of a unit that has collection; returns [unit_no, amount]. */
    private function markOneReceipt($b, array $data)
    {
        foreach ($data[self::MONTH]['units'] as $u) {
            if ((float) $u->collection_amount <= 0 || empty($u->contract_id)) {
                continue;
            }
            $r = DB::table('receipts_generation as rg')->join('tenant_contracts as tc', 'tc.id', '=', 'rg.tenant_contract_id')
                ->where('tc.unit_id', $u->unit_id)->where('tc.tenant_id', DB::table('tenant_contracts')->where('id', $u->contract_id)->value('tenant_id'))
                ->where('rg.receipts_generation_status', '<>', 2)->whereNull('rg.deleted_at')
                ->whereNotNull('rg.receipts_generation_eff_from')
                ->whereRaw("to_char(rg.receipts_generation_receipt_date, 'YYYY-MM') = '2026-09'")
                ->orderBy('rg.id')->first(['rg.id', 'rg.receipts_generation_amt']);
            if ($r) {
                DB::table('receipts_generation')->where('id', $r->id)->update(['receipts_generation_paid_to_landlord' => true]);
                return [(string) $u->unit_no, (float) $r->receipts_generation_amt];
            }
        }
        $this->markTestSkipped('no September rent receipt in Aashirwad');
    }

    private function consolidated(array $data, $label)
    {
        $rows = (new NormalManagementConsolidateSheet($data, 2026, 'Aashirwad'))->array();
        foreach ($rows as $r) {
            if (($r[1] ?? null) === $label) {
                return $r[1 + self::MONTH];
            }
        }
        $this->fail("no consolidated row $label");
    }

    public function test_marked_receipt_gets_a_less_row_and_nets_collection_and_transfer()
    {
        $b      = $this->building();
        $before = $this->monthData($b);
        $beforeRows = (new NormalManagementMonthSheet($before[self::MONTH], self::MONTH, 2026, 'Aashirwad'))->array();
        $this->assertSame([], NormalManagementConsolidateSheet::directDepositRows($before[self::MONTH]['units']), 'nothing marked yet');

        list($unitNo, $amount) = $this->markOneReceipt($b, $before);
        $after = $this->monthData($b);
        $rows  = (new NormalManagementMonthSheet($after[self::MONTH], self::MONTH, 2026, 'Aashirwad'))->array();

        $less = $this->find($rows, 'Less: D.Deposit - ' . $unitNo);
        $this->assertNotNull($less, 'Less: D.Deposit row');
        $this->assertEqualsWithDelta(-$amount, $less[7], 0.001);

        // unit row keeps what the tenant paid
        $unitsBefore = $unitsAfter = 0.0;
        foreach ($before[self::MONTH]['units'] as $u) { $unitsBefore += (float) $u->collection_amount; }
        foreach ($after[self::MONTH]['units'] as $u) { $unitsAfter += (float) $u->collection_amount; }
        $this->assertEqualsWithDelta($unitsBefore, $unitsAfter, 0.001);

        // TOTAL COLLECTION, consolidated Collection and Transfer drop by the amount
        $this->assertEqualsWithDelta($this->find($beforeRows, 'TOTAL COLLECTION')[7] - $amount, $this->find($rows, 'TOTAL COLLECTION')[7], 0.001);
        $this->assertEqualsWithDelta((float) $this->consolidated($before, 'Collection') - $amount, (float) $this->consolidated($after, 'Collection'), 0.001);
        $this->assertEqualsWithDelta((float) $this->consolidated($before, 'Amount Transfer to Land Lord') - $amount,
            (float) $this->consolidated($after, 'Amount Transfer to Land Lord'), 0.001);

        // management fee still on the full collection
        $this->assertEqualsWithDelta(NormalManagementConsolidateSheet::managementFee($before[self::MONTH]),
            NormalManagementConsolidateSheet::managementFee($after[self::MONTH]), 0.001);
    }

    /** The Generate path has its own query copy: same Less row in its sheet. */
    public function test_generate_excel_has_the_less_row_too()
    {
        $b = $this->building();
        list($unitNo, $amount) = $this->markOneReceipt($b, $this->monthData($b));

        $res = $this->post(route('normalManagementReportV2Generate'), ['building_id' => $b->id, 'month' => self::MONTH, 'year' => 2026]);
        $res->assertOk();
        $found = null;
        foreach (\PhpOffice\PhpSpreadsheet\IOFactory::load($res->baseResponse->getFile()->getPathname())->getAllSheets() as $sh) {
            foreach ($sh->toArray(null, true, false, false) as $r) {
                if (trim((string) $r[2]) === 'Less: D.Deposit - ' . $unitNo) {
                    $found = $r;
                }
            }
        }
        $this->assertNotNull($found);
        $this->assertEqualsWithDelta(-$amount, (float) $found[7], 0.001);
    }

    public function test_direct_deposit_rows_group_by_unit_in_natural_order()
    {
        $units = [
            (object) ['unit_no' => '10', 'direct_deposit_amount' => 100],
            (object) ['unit_no' => '9', 'direct_deposit_amount' => 50],
            (object) ['unit_no' => '10', 'direct_deposit_amount' => 25.5],
            (object) ['unit_no' => '11', 'direct_deposit_amount' => 0],
        ];
        $rows = NormalManagementConsolidateSheet::directDepositRows($units);
        $this->assertSame(['Less: D.Deposit - 9', 'Less: D.Deposit - 10'], array_map(function ($r) { return $r->description; }, $rows));
        $this->assertEqualsWithDelta(125.5, $rows[1]->amount, 0.001);
        $this->assertEqualsWithDelta(175.5, NormalManagementConsolidateSheet::directDepositTotal(['units' => $units]), 0.001);
    }
}
