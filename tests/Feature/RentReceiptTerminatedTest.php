<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\TerminationDuesService;

/** Rent receipts on terminated contracts are always saved; a dues mismatch is only a note (user, 03/10/2026). */
class RentReceiptTerminatedTest extends TestCase
{
    use DatabaseTransactions;

    /** A terminated legal-case contract with no termination dues record (old legal cases). */
    private function oldLegalContract()
    {
        $c = DB::table('tenant_contracts as tc')
            ->where('tc.tenant_renewal_termination_status', 8)
            ->whereExists(function ($q) { $q->select(DB::raw(1))->from('legal')->whereRaw('legal.tenant_contract_id = tc.id'); })
            ->whereNotExists(function ($q) { $q->select(DB::raw(1))->from('termination_dues')->whereRaw('termination_dues.tenant_contract_id = tc.id'); })
            ->orderBy('tc.id')->first(['tc.id', 'tc.tenant_contract_no']);
        if (!$c) {
            $this->markTestSkipped('no terminated legal contract without dues in this database');
        }
        return $c;
    }

    public function test_receipt_on_an_old_terminated_legal_contract_is_saved_with_a_note()
    {
        $c = $this->oldLegalContract();
        $this->actingAs(User::find(1));
        $before = DB::table('receipts_generation')->where('tenant_contract_id', $c->id)->count();

        $res = $this->post(route('rentReceiptGeneration.store'), [
            'receipts_generation_receipt_no'     => 'TEST',
            'receipts_generation_receipt_date'   => date('Y-m-d'),
            'tenant_contract_no'                 => $c->id,
            'receipts_generation_payment_method' => 2,
            'receipts_generation_amt'            => '125.000',
            'receipts_generation_eff_from'       => date('Y-m-01'),
            'receipts_generation_eff_to'         => date('Y-m-t'),
        ]);

        $res->assertRedirect(route('receiptsTabViewListtab', 'rent'));
        $this->assertSame($before + 1, DB::table('receipts_generation')->where('tenant_contract_id', $c->id)->count());
        $this->assertSame(125.0, (float) DB::table('receipts_generation')->where('tenant_contract_id', $c->id)->orderBy('id', 'desc')->value('receipts_generation_amt'));
        $msg = session('success');
        $this->assertStringContainsString('Saved Successfully. Please check:', $msg);
        $this->assertStringContainsString('no termination dues on record', $msg);
    }

    public function test_note_when_amount_is_more_than_the_open_rent_dues()
    {
        $d = DB::table('termination_dues as d')->join('tenant_contracts as tc', 'tc.id', '=', 'd.tenant_contract_id')
            ->where('tc.tenant_renewal_termination_status', 8)->orderBy('d.id', 'desc')->first(['tc.id']);
        if (!$d) {
            $this->markTestSkipped('no terminated contract with dues in this database');
        }
        $note = (new TerminationDuesService)->rentReceiptNote($d->id, 99999999);
        $this->assertNotNull($note);
        $this->assertMatchesRegularExpression('/more than the (open rent dues|rent owed)/', $note);
        $this->assertStringNotContainsString('cannot', $note);
    }

    public function test_no_note_for_contracts_that_are_not_terminated()
    {
        $id = DB::table('tenant_contracts')->where('tenant_contract_status', 1)->where('tenant_renewal_termination_status', '<>', 8)->value('id');
        $this->assertNull((new TerminationDuesService)->rentReceiptNote($id, 100));
    }
}
