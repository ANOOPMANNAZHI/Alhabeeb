<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Http\Controllers\PdcBankSheetController;
use Modules\BackOffice\Services\BankSheetPdcMatcher;
use Modules\BackOffice\Services\BankSheetResultRows;

/**
 * Bank sheet: a bounced cheque the bank deposited again, with no open PDC in PLMS yet, reads
 * "Redeposit needed" with what to do, instead of "Date mismatch" against other tenants'
 * cheques of that number (user, 07/10/2026). TAG2503810 cheque 2100094: Rent 125 + VAT 6.25.
 */
class BankSheetRedepositNeededTest extends TestCase
{
    use DatabaseTransactions;

    const CONTRACT = 76735;

    private function analyse(array $rows)
    {
        $c = new PdcBankSheetController();
        $run = \Closure::bind(function ($rows) {
            $bankMap = $this->bankMap();
            $results = (new BankSheetPdcMatcher($bankMap))->match($rows, $this->candidatePdcs($rows));
            $results = $this->markAlreadyPosted($results, $bankMap);
            return $this->markRedepositNeeded($results, $bankMap);
        }, $c, PdcBankSheetController::class);
        return $run($rows)[0];
    }

    private function sheetRow()
    {
        return [[
            'row_no' => 20, 'cheque_no' => '02100094', 'bank_code' => '18', 'bank_name' => '18 - National Bank Of Oman', 'account_no' => '',
            'amount' => 131.25, 'cheque_date' => '2026-09-25', 'created_on' => '2026-10-07', 'accepted' => true, 'reject_reason' => '',
        ]];
    }

    /** Leaves only the two bounced PDCs of cheque 2100094 (drops the redeposit rows and copies). */
    private function onlyBounced()
    {
        $bounced = DB::table('pdc')->where('tenant_contract_id', self::CONTRACT)->where('pdc_check_no', '2100094')
            ->where('pdc_cancel_reason', '1')->pluck('id')->all();
        if (count($bounced) !== 2) {
            $this->markTestSkipped('TAG2503810 cheque 2100094 is not in this database');
        }
        DB::table('pdc')->where('tenant_contract_id', self::CONTRACT)->where('pdc_check_no', '2100094')->whereNotIn('id', $bounced)->delete();
        return $bounced;
    }

    public function test_bounced_cheque_deposited_again_says_redeposit_needed()
    {
        $this->actingAs(User::find(1));
        $bounced = $this->onlyBounced();

        $r = $this->analyse($this->sheetRow());

        $this->assertSame(BankSheetPdcMatcher::REDEPOSIT_NEEDED, $r['status']);
        $this->assertSame([self::CONTRACT => 'TAG2503810'], $r['redeposit_contracts']);
        $this->assertStringContainsString('Bounced cheque deposited again - click Redeposit on TAG2503810', $r['reason']);
        $this->assertCount(1, $r['related'], 'the agreement once (Rent + VAT of one cheque)');
        $this->assertContainsEquals($r['related'][0]->id, $bounced);
        $this->assertSame('Redeposit needed', BankSheetResultRows::rowStatus('redeposit_needed'));
    }

    public function test_after_redeposit_the_cheque_matches_again()
    {
        $this->actingAs(User::find(1));
        $bounced = $this->onlyBounced();
        // what the Redeposit button + Save creates: open copies of both bounced rows
        foreach ($bounced as $id) {
            $row = (array) DB::table('pdc')->find($id);
            unset($row['id']);
            DB::table('pdc')->insert(array_merge($row, ['pdc_is_saved' => 1, 'pdc_cancel_reason' => '0', 'pdc_cancel_date' => null,
                'pdc_bounce_reason' => null, 'pdc_remark' => 'Redeposit', 'created_at' => now(), 'updated_at' => now()]));
        }

        $r = $this->analyse($this->sheetRow());

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $r['status']);
        $this->assertEqualsWithDelta(131.25, array_sum(array_map(function ($p) { return (float) $p->pdc_amt; }, $r['pdcs'])), 0.001);
    }

    public function test_preview_page_shows_the_status_and_a_link_to_the_pdc_screen()
    {
        $this->actingAs(User::find(1));
        $this->onlyBounced();
        $stored = date('Ymd_His') . '_1_redeposit_test.xls';
        $h = ['Status', 'Cheque Number', 'Pay Bank', 'Pay Account', 'Created on', 'Amount', 'Cheque Date', 'Posting Date', 'Onus Reason', 'ECC Reason', 'Resolution'];
        $row = ['Replied', '02100094', '18 - National Bank Of Oman', '', '07/10/2026 09:00:00 AM', 'OMR 131.250', '25/09/2026', '07/10/2026',
            '0 - 000- ACCEPTED', '', 'Accepted'];
        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $ws = $ss->getActiveSheet();
        foreach ($h as $i => $t) {
            $ws->setCellValue([$i + 2, 9], $t);
            $ws->setCellValueExplicit([$i + 2, 10], $row[$i], 's');
        }
        (new \PhpOffice\PhpSpreadsheet\Writer\Xls($ss))->save(storage_path('app/pdc_bank_sheets/' . $stored));
        try {
            $html = $this->get(route('pdcBankSheetPreview', $stored) . '?tab=cannot')->assertOk()->getContent();
        } finally {
            @unlink(storage_path('app/pdc_bank_sheets/' . $stored));
        }
        $this->assertStringContainsString('Redeposit needed', $html);
        $this->assertStringContainsString('click Redeposit on TAG2503810', $html);
        $this->assertStringContainsString(route('pdc.edit', self::CONTRACT), $html);
    }

    public function test_redeposit_already_there_keeps_the_original_reason()
    {
        $this->actingAs(User::find(1));
        $bounced = $this->onlyBounced();
        // an open PDC with the number exists but the amount is wrong: not a "redeposit needed" case
        $row = (array) DB::table('pdc')->find($bounced[0]);
        unset($row['id']);
        DB::table('pdc')->insert(array_merge($row, ['pdc_is_saved' => 1, 'pdc_cancel_reason' => '0', 'pdc_cancel_date' => null,
            'pdc_amt' => 999, 'created_at' => now(), 'updated_at' => now()]));

        $r = $this->analyse($this->sheetRow());

        $this->assertNotSame(BankSheetPdcMatcher::REDEPOSIT_NEEDED, $r['status']);
    }
}
