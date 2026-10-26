<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Http\Controllers\PdcBankSheetController;

/**
 * The bank sheet searches PDCs with the cheque number as printed (e.g. 00205886) and,
 * failing that, without the leading zeros (205886).
 */
class BankSheetChequeZerosTest extends TestCase
{
    use DatabaseTransactions;

    private function candidates(array $chequeNos, $forRejected = true)
    {
        $rows = [];
        foreach ($chequeNos as $no) {
            $rows[] = ['cheque_no' => $no];
        }
        $c = new PdcBankSheetController();
        $call = \Closure::bind(function ($rows, $forRejected) { return $this->candidatePdcs($rows, $forRejected); }, $c, PdcBankSheetController::class);
        return array_map(function ($p) { return $p->id; }, $call($rows, $forRejected));
    }

    /** A PDC stored with its leading zeros, open (not cleared, not cancelled). */
    private function pdcStoredWithZeros($chequeNo, $posted)
    {
        $id = DB::table('pdc')->whereNull('pdc_clear_date')->where('pdc_cancel_reason', 0)->whereIn('pdc_type', [1, 2, 3])
            ->where('pdc_is_posted', $posted)->orderBy('id', 'desc')->value('id');
        DB::table('pdc')->where('id', $id)->update(['pdc_check_no' => $chequeNo]);
        return $id;
    }

    public function test_pdc_stored_with_leading_zeros_is_found_by_the_printed_number()
    {
        $id = $this->pdcStoredWithZeros('00777123', 1);
        $this->assertContainsEquals($id, $this->candidates(['00777123']));          // rejected tab (posted PDCs count)

        $open = $this->pdcStoredWithZeros('00777124', 0);
        $this->assertContainsEquals($open, $this->candidates(['00777124'], false)); // posting tabs (unposted only)
    }

    public function test_pdc_stored_without_zeros_is_still_found()
    {
        $id = $this->pdcStoredWithZeros('777125', 1);
        $this->assertContainsEquals($id, $this->candidates(['00777125']));
    }
}
