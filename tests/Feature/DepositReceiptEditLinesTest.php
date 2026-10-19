<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

/**
 * Editing a deposit receipt's amount also changes its distribution lines (CPY2623237: amount 375 → 125,
 * Distribution Details stayed at 375).
 */
class DepositReceiptEditLinesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_edit_amount_updates_bank_debit_and_ledger_credit()
    {
        $this->actingAs(User::find(1));
        $contractId = DB::table('tenant_contracts')->orderBy('id', 'desc')->value('id');
        $id = DB::table('receipts_generation')->insertGetId([
            'tenant_contract_id' => $contractId, 'receipts_generation_payment_method' => 2,
            'receipts_generation_receipt_no' => 'TDEP' . mt_rand(10000, 99999), 'receipts_generation_receipt_date' => '2026-10-19',
            'receipts_generation_amt' => 375, 'receipts_generation_status' => 0, 'receipts_generation_approval_status' => 1,
            'receipts_generation_type' => 2, 'receipts_generation_index' => 0, 'created_by' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $debit = DB::table('receipts_generation_dim')->insertGetId(['receipts_generation_id' => $id, 'dim_type' => 'BANK',
            'debit_amount' => 375, 'credit_amount' => 0, 'ac_codes_id' => 0, 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $credit = DB::table('receipts_generation_dim')->insertGetId(['receipts_generation_id' => $id, 'account_code' => 22311, 'dim_type' => 'LEDGER',
            'debit_amount' => 0, 'credit_amount' => 375, 'ac_codes_id' => 702, 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);

        $this->post(route('updateDepositReceiptAction', $id), [
            'receipts_generation_receipt_no' => 'X', 'receipts_generation_receipt_date' => '2026-10-19',
            'tenant_contract_no' => $contractId, 'receipts_generation_payment_method' => 2,
            'receipts_generation_amt' => '125.000',
        ])->assertRedirect();

        $this->assertEquals(125, (float) DB::table('receipts_generation')->where('id', $id)->value('receipts_generation_amt'));
        $d = DB::table('receipts_generation_dim')->where('id', $debit)->first();
        $c = DB::table('receipts_generation_dim')->where('id', $credit)->first();
        $this->assertEquals([125, 0], [(float) $d->debit_amount, (float) $d->credit_amount]);
        $this->assertEquals([0, 125], [(float) $c->debit_amount, (float) $c->credit_amount]);
        $this->assertSame(22311, (int) $c->account_code);
        $this->assertSame(2, DB::table('receipts_generation_dim')->where('receipts_generation_id', $id)->count());
    }
}
