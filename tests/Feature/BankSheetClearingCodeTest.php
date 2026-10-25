<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Modules\BackOffice\Entities\Pdc;
use Modules\Masters\Entities\Bank;

/** Bank sheet preview: clearing codes no PLMS bank has can be added from the sheet (user, 04/10/2026). */
class BankSheetClearingCodeTest extends TestCase
{
    use DatabaseTransactions;

    const CODE = '987';
    private $stored;
    private $pdc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(1));
        // an unposted rent PDC whose cheque number no other unposted PDC uses
        $this->pdc = Pdc::where('pdc_is_posted', 0)->where('pdc_cancel_reason', 0)->whereNull('pdc_clear_date')
            ->whereIn('pdc_type', [1, 2])->whereNotNull('pdc_check_no')->whereNotNull('pdc_check_date')->where('pdc_amt', '>', 0)
            ->whereNotNull('bank_id')->orderBy('id', 'desc')->limit(200)->get()
            ->first(function ($p) {
                return Pdc::where('pdc_check_no', $p->pdc_check_no)->where('id', '<>', $p->id)->count() === 0;
            });
        if (!$this->pdc) {
            $this->markTestSkipped('No unposted PDC to build a sheet from.');
        }
        DB::table('bank')->where('bank_clearing_codes', 'like', '%' . self::CODE . '%')->update(['bank_clearing_codes' => null]);
        $this->stored = date('Ymd_His') . '_1_clearing_code_test.xls';
        $this->writeSheet(storage_path('app/pdc_bank_sheets/' . $this->stored));
    }

    protected function tearDown(): void
    {
        if ($this->stored) {
            @unlink(storage_path('app/pdc_bank_sheets/' . $this->stored));
        }
        parent::tearDown();
    }

    private function writeSheet($path)
    {
        $headers = ['Status', 'Cheque Number', 'Pay Bank', 'Pay Account', 'Created on', 'Amount', 'Cheque Date', 'Posting Date', 'Onus Reason', 'ECC Reason', 'Resolution'];
        $date = $this->pdc->pdc_check_date->format('d/m/Y');
        $row = ['Replied', (string) $this->pdc->pdc_check_no, self::CODE . ' - Test Clearing Bank', '', $date . ' 09:47:09 AM',
            'OMR ' . number_format((float) $this->pdc->pdc_amt, 3, '.', ''), $date, $date, '0 - 000- ACCEPTED', '', 'Accepted'];
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        foreach ($headers as $i => $h) {
            $ws->setCellValue([$i + 2, 9], $h);
            $ws->setCellValueExplicit([$i + 2, 10], $row[$i], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        (new Xls($ss))->save($path);
    }

    public function test_preview_lists_the_unknown_code_with_the_cheques_bank_suggested()
    {
        $html = $this->get(route('pdcBankSheetPreview', $this->stored))->assertOk()->getContent();
        $this->assertStringContainsString('Bank codes not set up in PLMS', $html);
        $this->assertStringContainsString('<span class="bsp-code-no">' . self::CODE . '</span><strong>Test Clearing Bank</strong>', $html);
        $bank = Bank::find($this->pdc->bank_id);
        $this->assertStringContainsString('in PLMS under ' . e($bank->bank_name), $html);
        $this->assertMatchesRegularExpression('/<option value="' . $bank->id . '" selected>/', $html);
        $this->assertStringContainsString('Add code ' . self::CODE, $html);
    }

    public function test_saving_adds_the_code_next_to_existing_ones_and_the_cheque_then_matches()
    {
        $bank = Bank::find($this->pdc->bank_id);
        $before = $bank->bank_clearing_codes;
        $this->post(route('pdcBankSheetClearingCode', $this->stored), ['code' => self::CODE, 'bank_id' => $bank->id])
            ->assertRedirect(route('pdcBankSheetPreview', $this->stored))->assertSessionHas('success');
        $after = Bank::find($bank->id)->bank_clearing_codes;
        $this->assertSame($before ? $before . ',' . self::CODE : self::CODE, $after);

        $html = $this->get(route('pdcBankSheetPreview', $this->stored))->assertOk()->getContent();
        $this->assertStringNotContainsString('Bank codes not set up in PLMS', $html);
        $this->assertStringNotContainsString('is not mapped to a PLMS bank', $html);

        // a second save changes nothing
        $this->post(route('pdcBankSheetClearingCode', $this->stored), ['code' => self::CODE, 'bank_id' => $bank->id])->assertSessionHas('success');
        $this->assertSame($after, Bank::find($bank->id)->bank_clearing_codes);
    }

    public function test_code_on_another_bank_is_refused()
    {
        $other = Bank::where('id', '<>', $this->pdc->bank_id)->first();
        DB::table('bank')->where('id', $other->id)->update(['bank_clearing_codes' => self::CODE]);
        $this->post(route('pdcBankSheetClearingCode', $this->stored), ['code' => self::CODE, 'bank_id' => $this->pdc->bank_id])
            ->assertSessionHas('error');
        $this->assertStringNotContainsString(self::CODE, (string) Bank::find($this->pdc->bank_id)->bank_clearing_codes);
    }

    public function test_bad_input_is_rejected()
    {
        $this->post(route('pdcBankSheetClearingCode', $this->stored), ['code' => 'abc', 'bank_id' => $this->pdc->bank_id])->assertSessionHasErrors('code');
        $this->post(route('pdcBankSheetClearingCode', $this->stored), ['code' => self::CODE])->assertSessionHasErrors('bank_id');
    }

    public function test_needs_bank_master_edit_right()
    {
        $user = User::all()->first(function ($u) { return $u->can('pdc_posting_list') && !$u->can('edit_bank'); });
        if (!$user) {
            $this->markTestSkipped('No local user with PDC posting but without edit_bank.');
        }
        $this->actingAs($user);
        $html = $this->get(route('pdcBankSheetPreview', $this->stored))->assertOk()->getContent();
        $this->assertStringContainsString('Bank codes not set up in PLMS', $html);
        $this->assertStringNotContainsString('Add code ' . self::CODE, $html);
        $this->post(route('pdcBankSheetClearingCode', $this->stored), ['code' => self::CODE, 'bank_id' => $this->pdc->bank_id])->assertStatus(403);
    }
}
