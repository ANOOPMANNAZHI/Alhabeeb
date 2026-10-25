<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Http\Controllers\PdcBankSheetProgressController as Progress;

/** Bank sheet post: progress overlay data (user, 04/10/2026). */
class BankSheetPostProgressTest extends TestCase
{
    use DatabaseTransactions;

    private $stored;

    protected function setUp(): void
    {
        parent::setUp();
        \Mail::fake();
        $this->actingAs(User::find(1));
        $this->stored = date('Ymd_His') . '_1_progress_test.xls';
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('app/pdc_bank_sheets/' . $this->stored));
        Cache::forget(Progress::cacheKey($this->stored));
        parent::tearDown();
    }

    /** A one-row sheet for an unposted rent PDC (alone on its cheque) on a bank with a clearing code. */
    private function sheetForOnePdc()
    {
        $codes = [];
        foreach (DB::table('bank')->whereNotNull('bank_clearing_codes')->get() as $b) {
            $codes[(int) $b->id] = explode(',', $b->bank_clearing_codes)[0];
        }
        $pdc = Pdc::where('pdc_is_posted', 0)->where('pdc_cancel_reason', 0)->whereNull('pdc_clear_date')->where('pdc_type', 1)
            ->whereIn('bank_id', array_keys($codes))->where('pdc_amt', '>', 0)->whereNotNull('pdc_check_date')
            ->where('pdc_check_date', '>=', '2026-09-01')->orderBy('id')->limit(100)->get()
            ->first(function ($p) { return Pdc::where('pdc_check_no', $p->pdc_check_no)->count() === 1; });
        if (!$pdc) {
            $this->markTestSkipped('No unposted single-row PDC on a mapped bank.');
        }
        $d = $pdc->pdc_check_date->format('d/m/Y');
        $h = ['Status', 'Cheque Number', 'Pay Bank', 'Pay Account', 'Created on', 'Amount', 'Cheque Date', 'Posting Date', 'Onus Reason', 'ECC Reason', 'Resolution'];
        $row = ['Replied', (string) $pdc->pdc_check_no, $codes[(int) $pdc->bank_id] . ' - Bank', '', $d . ' 09:00:00 AM',
            'OMR ' . number_format((float) $pdc->pdc_amt, 3, '.', ''), $d, $d, '0 - 000- ACCEPTED', '', 'Accepted'];
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        foreach ($h as $i => $t) {
            $ws->setCellValue([$i + 2, 9], $t);
            $ws->setCellValueExplicit([$i + 2, 10], $row[$i], 's');
        }
        (new Xls($ss))->save(storage_path('app/pdc_bank_sheets/' . $this->stored));
        return $pdc;
    }

    public function test_progress_needs_the_pages_key()
    {
        $this->get(route('pdcBankSheetProgress', [$this->stored, 'wrong']))->assertStatus(404);
        $this->get(route('pdcBankSheetProgress', [$this->stored, Progress::key($this->stored)]))
            ->assertOk()->assertJson(['total' => 0, 'finished' => false]);
    }

    public function test_review_page_has_the_overlay_and_its_progress_url()
    {
        $this->sheetForOnePdc();
        $html = $this->get(route('pdcBankSheetPreview', $this->stored))->assertOk()->getContent();
        $this->assertStringContainsString('id="bsp-overlay"', $html);
        $this->assertStringContainsString(str_replace('/', '\/', route('pdcBankSheetProgress', [$this->stored, Progress::key($this->stored)])), $html);
    }

    public function test_post_records_progress_and_keeps_the_result_message()
    {
        $pdc = $this->sheetForOnePdc();
        $bank = DB::table('bank')->where('bank_code', 'BMI')->value('id');
        $this->post(route('pdcBankSheetPost', $this->stored), ['pdc_deposit_date' => date('Y-m-d'), 'bank_id' => $bank], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJson(['ok' => true]);

        $p = $this->get(route('pdcBankSheetProgress', [$this->stored, Progress::key($this->stored)]))->assertOk()->json();
        $this->assertSame(1, $p['total']);
        $this->assertSame(1, $p['done']);
        $this->assertSame(1, $p['posted']);
        $this->assertTrue($p['finished']);
        // the progress call has no session, so the result page's message is still there
        $this->assertStringContainsString('1 cheque(s) posted', (string) session('success'));
        $this->assertSame(1, (int) Pdc::find($pdc->id)->pdc_is_posted);
    }
}
