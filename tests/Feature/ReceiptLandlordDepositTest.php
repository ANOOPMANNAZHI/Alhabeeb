<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

/** Receipt flag "Direct deposit to landlord" (06/10/2026): forms, Mark / Unmark on the view, list filter. */
class ReceiptLandlordDepositTest extends TestCase
{
    use DatabaseTransactions;

    private function contractId()
    {
        return DB::table('tenant_contracts')->where('tenant_contract_status', 1)->where('tenant_renewal_termination_status', '<>', 8)
            ->orderBy('id', 'desc')->value('id');
    }

    private function latestReceipt($contractId)
    {
        return DB::table('receipts_generation')->where('tenant_contract_id', $contractId)->orderBy('id', 'desc')->first();
    }

    private function base($contractId, array $extra = [])
    {
        return $extra + [
            'receipts_generation_receipt_no'     => 'TEST',
            'receipts_generation_receipt_date'   => date('Y-m-d'),
            'tenant_contract_no'                 => $contractId,
            'receipts_generation_payment_method' => 1,
            'bank_id'                            => 32,
            'pdc_bank_id'                        => 'BMI',
            'cheque_no'                          => '123456',
            'receipts_generation_amt'            => '170.000',
            'receipts_generation_eff_from'       => date('Y-m-01'),
            'receipts_generation_eff_to'         => date('Y-m-t'),
        ];
    }

    public function test_rent_receipt_create_and_edit_keep_the_flag()
    {
        $c = $this->contractId();
        $this->actingAs(User::find(1));

        $this->post(route('rentReceiptGeneration.store'), $this->base($c, ['paid_to_landlord' => '1']))->assertRedirect();
        $r = $this->latestReceipt($c);
        $this->assertTrue((bool) $r->receipts_generation_paid_to_landlord);
        $this->assertSame(1, (int) $r->receipts_generation_landlord_marked_by);
        $this->assertNotNull($r->receipts_generation_landlord_marked_at);

        // edit form shows the box ticked
        $this->get(route('rentReceiptGeneration.edit', $r->id))->assertOk()->assertSee('Direct deposit to landlord');

        // saving again with the box still ticked keeps who/when
        DB::table('receipts_generation')->where('id', $r->id)->update(['receipts_generation_landlord_marked_at' => '2026-01-01 10:00:00']);
        $this->post(route('rentReceiptGeneration.update', $r->id), $this->base($c, ['paid_to_landlord' => '1', '_method' => 'PUT']));
        $r2 = DB::table('receipts_generation')->find($r->id);
        $this->assertTrue((bool) $r2->receipts_generation_paid_to_landlord);
        $this->assertSame('2026-01-01 10:00:00', $r2->receipts_generation_landlord_marked_at);

        // unticked (only the hidden 0 is sent) clears it
        $this->post(route('rentReceiptGeneration.update', $r->id), $this->base($c, ['paid_to_landlord' => '0', '_method' => 'PUT']));
        $this->assertFalse((bool) DB::table('receipts_generation')->where('id', $r->id)->value('receipts_generation_paid_to_landlord'));
    }

    public function test_receipt_without_the_box_is_not_flagged()
    {
        $c = $this->contractId();
        $this->actingAs(User::find(1));
        $this->post(route('rentReceiptGeneration.store'), $this->base($c))->assertRedirect();
        $r = $this->latestReceipt($c);
        $this->assertFalse((bool) $r->receipts_generation_paid_to_landlord);
        $this->assertNull($r->receipts_generation_landlord_marked_by);
    }

    public function test_general_and_deposit_receipts_save_the_flag()
    {
        $c = $this->contractId();
        $this->actingAs(User::find(1));

        $this->post(route('addGeneralReceiptAction'), $this->base($c, ['paid_to_landlord' => '1', 'account_code' => []]))->assertRedirect();
        $g = $this->latestReceipt($c);
        $this->assertSame(1, (int) $g->receipts_generation_type);
        $this->assertTrue((bool) $g->receipts_generation_paid_to_landlord);

        $this->post(route('addDepositReceiptAction'), $this->base($c, ['paid_to_landlord' => '1']))->assertRedirect();
        $d = $this->latestReceipt($c);
        $this->assertSame(2, (int) $d->receipts_generation_type);
        $this->assertTrue((bool) $d->receipts_generation_paid_to_landlord);
    }

    public function test_mark_and_unmark_on_a_posted_receipt_changes_only_the_flag()
    {
        $r = DB::table('receipts_generation')->where('receipts_generation_status', 3)->whereNull('deleted_at')
            ->where('receipts_generation_paid_to_landlord', false)->orderBy('id', 'desc')->first();
        $this->actingAs(User::find(1));

        $this->get(route('rentReceiptGeneration.show', $r->id))->assertOk()->assertSee('Mark as direct deposit to landlord');
        $this->post(route('receiptLandlordDeposit', $r->id), ['paid_to_landlord' => 1])
            ->assertRedirect(route('rentReceiptGeneration.show', $r->id));
        $after = DB::table('receipts_generation')->find($r->id);
        $this->assertTrue((bool) $after->receipts_generation_paid_to_landlord);
        $this->assertSame(1, (int) $after->receipts_generation_landlord_marked_by);
        foreach (['receipts_generation_amt', 'receipts_generation_status', 'receipts_generation_approval_status', 'bank_id',
                     'receipts_generation_cheque_no', 'receipt_journal_no'] as $col) {
            $this->assertEquals($r->$col, $after->$col, $col);
        }
        $this->get(route('rentReceiptGeneration.show', $r->id))->assertSee('Paid directly to landlord')
            ->assertSee('Unmark direct deposit to landlord');

        $this->post(route('receiptLandlordDeposit', $r->id), ['paid_to_landlord' => 0]);
        $this->assertFalse((bool) DB::table('receipts_generation')->where('id', $r->id)->value('receipts_generation_paid_to_landlord'));
    }

    public function test_mark_needs_the_permission()
    {
        $user = User::where('username', 'test_exec1')->first();
        if (!$user || $user->can('mark_receipt_landlord_deposit')) {
            $this->markTestSkipped('no user without the permission');
        }
        $id = DB::table('receipts_generation')->whereNull('deleted_at')->orderBy('id', 'desc')->value('id');
        $this->actingAs($user);
        $this->post(route('receiptLandlordDeposit', $id), ['paid_to_landlord' => 1])->assertStatus(403);
        $this->assertFalse((bool) DB::table('receipts_generation')->where('id', $id)->value('receipts_generation_paid_to_landlord'));
    }

    public function test_list_filter_and_marker()
    {
        $r = DB::table('receipts_generation as rg')->join('tenant_contracts as tc', 'tc.id', '=', 'rg.tenant_contract_id')
            ->where('rg.receipts_generation_type', 0)->whereNull('rg.deleted_at')->orderBy('rg.id', 'desc')
            ->first(['rg.id', 'rg.receipts_generation_receipt_no as no']);
        DB::table('receipts_generation')->where('id', $r->id)->update(['receipts_generation_paid_to_landlord' => true]);
        $this->actingAs(User::find(1));

        $url = route('receiptsTabViewListtab', 'rent');
        $yes = $this->get($url . '?ajax=1&paid_to_landlord=1&receipts_generation_receipt_no=' . $r->no)->assertOk();
        $yes->assertSee($r->no)->assertSee('Paid to LL');
        $this->get($url . '?ajax=1&paid_to_landlord=0&receipts_generation_receipt_no=' . $r->no)->assertOk()->assertDontSee($r->no);
        $this->get($url)->assertOk()->assertSee('Paid to LL: Yes');
    }
}
