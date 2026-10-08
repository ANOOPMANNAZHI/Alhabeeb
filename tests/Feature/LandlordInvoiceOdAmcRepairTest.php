<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Http\Controllers\BackOfficeReportController;

/**
 * Landlord Invoice v2, Other Deductions: A/C, F.A.S and lift bills each give an AMC line and a
 * Repair & Maintenance line (08/10/2026: Bait Rawan Aug'26 lift AMC was missing, A/C AMC and repair
 * were one line). From 01/10/2026 the account decides (22307/22308/22309 = AMC); before that the
 * bill text does ("AMC" = AMC, anything else = repair).
 */
class LandlordInvoiceOdAmcRepairTest extends TestCase
{
    use DatabaseTransactions;

    private $buildingId;

    protected function setUp(): void
    {
        parent::setUp();
        // a building with no maintenance bills at all, so only the bills made here count
        $this->buildingId = (int) DB::table('buildings')->whereNotIn('id', function ($q) {
            $q->select('building_id')->from('maintenance_invoice_details')->whereNotNull('building_id');
        })->orderBy('id')->value('id');
    }

    /** One maintenance bill with one line for the test building on the given account. */
    private function bill($date, $accCode, $text, $amount)
    {
        $tpl = (array) DB::table('maintenance_invoices')->where('maintenance_invoice_status', '!=', 2)->whereNull('deleted_at')->orderBy('id')->first();
        unset($tpl['id']);
        $id = DB::table('maintenance_invoices')->insertGetId(array_merge($tpl, [
            'maintenance_invoice_no' => 'TST' . mt_rand(100000, 999999), 'maintenance_invoice_date' => $date,
            'maintenance_invoice_desc' => $text, 'ax_batch_id' => null, 'ax_invoice_no' => null,
        ]));
        DB::table('maintenance_invoice_details')->insert([
            'maintenance_invoice_id' => $id, 'building_id' => $this->buildingId, 'description' => 'test',
            'debit_amt' => $amount, 'material_charge' => $amount,
            'ac_codes_id' => DB::table('acc_codes')->where('acc_code_val', $accCode)->value('id'),
        ]);
    }

    private function lines($from, $to)
    {
        $building = \Modules\Masters\Entities\Building::find($this->buildingId);
        $out = [];
        foreach (app(BackOfficeReportController::class)->landlordAmcRepairLines($building, $from, $to) as $l) {
            $out[$l['description']] = $l['amount'];
        }
        return $out;
    }

    public function test_before_october_2026_the_bill_text_splits_amc_from_repair()
    {
        $this->bill('2026-08-01', 41107, "AMC FOR AC MAINTAINENCE FOR JULY'26 TO SEP'26", 390.6);
        $this->bill('2026-08-15', 41107, 'AC REPAIR & MAINTAINENCE CHARGES FOR VARIOUS FLATS', 338.1);
        $this->bill('2026-08-01', 41109, "AMC FOR LIFT MAINTAINENCE FOR JULY'26 TO DEC'26", 328.167);
        $this->bill('2026-08-03', 41109, 'LIFT REPAIR CHARGES', 650.003);
        $this->bill('2026-08-20', 41108, "AMC for Fire Alaram System for 01.07.2026 to 30.09.26", 112.875);

        $this->assertSame([
            'AMC FOR A/C UNITS'                             => 390.6,
            'REPAIR AND MAINTENANCE CHARGES FOR A/C UNITS'  => 338.1,
            'AMC FOR F.A.S FOR THE PERIOD'                  => 112.875,
            'AMC FOR LIFT'                                  => 328.167,
            'REPAIR AND MAINTENANCE CHARGES FOR LIFT'       => 650.003,
        ], $this->lines('2026-08-01', '2026-08-31'));
    }

    public function test_from_october_2026_the_account_decides()
    {
        $this->bill('2026-10-02', 22307, 'AC CONTRACT OCT', 100);
        $this->bill('2026-10-05', 41107, 'AMC WORD BUT REPAIR ACCOUNT', 40);
        $this->bill('2026-10-06', 22309, 'LIFT CONTRACT', 30);

        $this->assertSame([
            'AMC FOR A/C UNITS'                            => 100.0,
            'REPAIR AND MAINTENANCE CHARGES FOR A/C UNITS' => 40.0,
            'AMC FOR LIFT'                                 => 30.0,
        ], $this->lines('2026-10-01', '2026-10-31'));
    }

    public function test_no_bills_gives_no_lines()
    {
        $this->assertSame([], $this->lines('2026-08-01', '2026-08-31'));
    }
}
