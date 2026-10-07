<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Http\Controllers\RoutinesController;
use Modules\BackOffice\Services\VatReceiptCreator;
use Modules\Sales\Entities\TenantContract;

/** VAT receipt from PDC posting: Normal building → 22301 CASH IN TRANSIT, Comprehensive / Commission → 12211 RENT RECEIVABLE. */
class VatReceiptLedgerAccountTest extends TestCase
{
    use DatabaseTransactions;

    private function contractFor($managementId)
    {
        $id = DB::table('tenant_contracts as tc')->join('buildings as b', 'b.id', '=', 'tc.building_id')
            ->where('b.management_id', $managementId)->where('tc.tenant_contract_status', 1)
            ->orderBy('tc.id', 'desc')->value('tc.id');
        return $id ? TenantContract::with(['Unit', 'building'])->find($id) : null;
    }

    private function createVat($contract, $chequeNo)
    {
        $creator = new VatReceiptCreator(app(RoutinesController::class));
        $out = $creator->create([
            'contract'        => $contract,
            'cheque_no'       => $chequeNo,
            'bank_code'       => 'BMI',
            'amount'          => 10.5,
            'eff_from'        => '2026-10-01',
            'eff_to'          => '2026-10-31',
            'deposit_date'    => '2026-10-07',
            'deposit_bank_id' => 32,
        ]);
        $this->assertTrue($out['created']);
        $id = DB::table('receipts_generation')->where('receipts_generation_receipt_no', $out['receipt_no'])->value('id');
        return DB::table('receipts_generation_dim')->where('receipts_generation_id', $id)->get();
    }

    public function test_ledger_account_by_management_type()
    {
        $this->assertSame(12211, VatReceiptCreator::ledgerAccount(1));
        $this->assertSame(22301, VatReceiptCreator::ledgerAccount(2));
        $this->assertSame(22301, VatReceiptCreator::ledgerAccount('2'));
        $this->assertSame(12211, VatReceiptCreator::ledgerAccount(3));
        $this->assertSame(12211, VatReceiptCreator::ledgerAccount(null));
    }

    public function test_normal_building_vat_receipt_goes_to_cash_in_transit()
    {
        $this->actingAs(User::find(1));
        $dims = $this->createVat($this->contractFor(2), '990000001');
        $this->assertCount(1, $dims);
        $this->assertSame(22301, (int) $dims[0]->account_code);
        $this->assertSame('CASH IN TRANSIT', $dims[0]->description);
        $this->assertEquals(10.5, (float) $dims[0]->credit_amount);
    }

    public function test_comprehensive_and_commission_vat_receipts_stay_on_rent_receivable()
    {
        $this->actingAs(User::find(1));
        $dims = $this->createVat($this->contractFor(1), '990000002');
        $this->assertSame(12211, (int) $dims[0]->account_code);
        $this->assertSame('RENT RECEIVABLE', $dims[0]->description);

        $commission = $this->contractFor(3);
        if ($commission === null) {
            $this->markTestSkipped('no active Commission contract');
        }
        $this->assertSame(12211, (int) $this->createVat($commission, '990000003')[0]->account_code);
    }
}
