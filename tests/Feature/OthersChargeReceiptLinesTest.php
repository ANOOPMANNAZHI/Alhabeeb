<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Http\Controllers\RoutinesController;

/**
 * An "Others" PDC charge (E&W ...) posted from the bank sheet / PDC posting becomes a General Receipt with its
 * credit line (CPY2622678 had none, so Distribution Details was empty and AX would get no lines).
 */
class OthersChargeReceiptLinesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_account_follows_remark_then_management()
    {
        $this->assertSame(41105, RoutinesController::otherChargeAccount('Electricity & Water charges', 1));
        $this->assertSame(41105, RoutinesController::otherChargeAccount('E&W', 2));
        $this->assertSame(41105, RoutinesController::otherChargeAccount('water bill', 3));
        $this->assertSame(22301, RoutinesController::otherChargeAccount('renewal fee', 2));
        $this->assertSame(12211, RoutinesController::otherChargeAccount('', 1));
        $this->assertSame(12211, RoutinesController::otherChargeAccount(null, 3));
        $this->assertSame(12211, RoutinesController::otherChargeAccount('new fee', 1));
    }

    public function test_others_charge_receipt_gets_credit_line()
    {
        $this->actingAs(User::find(1));
        $rent = Pdc::where('tenant_contract_id', 76599)->where('pdc_type', 1)->orderBy('id')->firstOrFail();
        $pdc = $rent->replicate();
        $pdc->pdc_type = 3;
        $pdc->pdc_amt = 135;
        $pdc->pdc_remark = 'Electricity & Water charges';
        $pdc->pdc_check_no = 'T' . mt_rand(100000, 999999);
        $pdc->pdc_is_posted = 0;
        $pdc->pdc_receipt_no = null;
        $pdc->save();

        $result = (new RoutinesController)->postSinglePdc($pdc, '2026-10-05', $rent->bank_id ?: 3);
        $this->assertTrue($result['posted']);

        $receipt = ReceiptsGeneration::where('receipts_generation_receipt_no', $pdc->fresh()->pdc_receipt_no)->firstOrFail();
        $this->assertSame(1, (int) $receipt->receipts_generation_type);
        $lines = DB::table('receipts_generation_dim')->where('receipts_generation_id', $receipt->id)->get();
        $this->assertCount(1, $lines);
        $this->assertSame(41105, (int) $lines[0]->account_code);
        $this->assertSame('LEDGER', trim($lines[0]->dim_type));
        $this->assertEquals(135, (float) $lines[0]->credit_amount);
        $this->assertEquals(0, (float) $lines[0]->debit_amount);
        $this->assertSame('Electricity & Water charges', $lines[0]->narration);
        $this->assertGreaterThan(0, (int) $lines[0]->ac_codes_id);
    }
}
