<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Services\LegalSettlement;

/**
 * Legal workflow review fixes (18/10/2026): saves check the case's step and the user, the lawyer step is the
 * assigned lawyer or PLM, closing before acceptance needs a reason and shows in the Closed list, a referred-back
 * case is sent again as the same case or dropped, the contract status is locked while a case is open,
 * "balance written off" becomes a waiver, and only money above the new rent pays a settlement.
 */
class LegalFlowFixesTest extends TestCase
{
    use DatabaseTransactions;

    private $plm;
    private $lawyer;
    private $lawyer2;
    private $are;

    protected function setUp()
    {
        parent::setUp();
        SystemMessenger::$smsSender = function () { return true; };
        app('cache')->forget('spatie.permission.cache');
        $this->plm = $this->makeUser('lgf_t_plm', 'backoffice_manager');
        $this->lawyer = $this->makeUser('lgf_t_lawyer', 'legal_advisor');
        $this->lawyer2 = $this->makeUser('lgf_t_lawyer2', 'legal_advisor');
        $this->are = $this->makeUser('lgf_t_are', 'are');
    }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function as($user)
    {
        $this->flushSession();
        return $this->actingAs($user);
    }

    private function makeUser($username, $role)
    {
        $emp = DB::table('employees')->insertGetId(['employee_code' => strtoupper($username), 'employee_name' => ucfirst($username),
            'employee_contact_address' => 'x', 'employee_contact_no' => '91234567', 'employee_dob' => '1990-01-01', 'designation_id' => 37, 'employee_status' => 1, 'created_by' => 1,
            'created_at' => now(), 'updated_at' => now()]);
        $roleId = DB::table('roles')->where('name', $role)->value('id');
        $id = DB::table('users')->insertGetId(['username' => $username, 'email' => $username . '@example.test', 'password' => bcrypt('x'),
            'user_type' => 'employee', 'user_type_id' => $emp, 'default_role' => $roleId, 'user_type_status' => 1, 'created_by' => 1,
            'created_at' => now(), 'updated_at' => now()]);
        DB::table('model_has_roles')->insert(['role_id' => $roleId, 'model_type' => 'App\User', 'model_id' => $id]);
        return User::find($id);
    }

    private function moveToLegal()
    {
        $contractId = DB::table('tenant_contracts as tc')->join('buildings as b', 'b.id', '=', 'tc.building_id')
            ->where('tc.tenant_contract_status', 1)->where('tc.status', 0)->where('b.building_ownership_type', 4)
            ->where('b.building_name', 'not ilike', '%in house%')->where('tc.tenant_contract_rent', '>', 0)
            ->whereNotIn('tc.id', DB::table('legal')->pluck('tenant_contract_id')->all())
            ->orderBy('tc.id', 'desc')->value('tc.id');
        $this->as($this->plm)->post(route('tenantContractChangeStatusStore'), [
            'tenant_contract_id' => $contractId, 'tenant_contract_status' => 5, 'tenant_contract_comment' => 'Not paying']);
        return Legal::where('tenant_contract_id', $contractId)->orderBy('id', 'desc')->first();
    }

    private function act($user, $legal, $flow, $key, $areStatus = 0, array $extra = [])
    {
        return $this->as($user)->post(route('plmsApproveReferBackStore'), $extra + ['legal_id' => $legal->id, 'process_flow' => $flow,
            'action_key' => $key, 'are_status' => $areStatus, 'legal_note' => 'note']);
    }

    private function plmApprove($legal)
    {
        return $this->act($this->plm, $legal, 801, 'APRV', 0, ['handled_by' => 1, 'case_type' => 1, 'priority' => 1, 'lawyer_user_id' => $this->lawyer->id]);
    }

    private function code($legal)
    {
        return (int) $legal->fresh()->work_flow_processes_code;
    }

    public function test_a_second_click_or_the_wrong_lawyer_cannot_move_the_case()
    {
        $legal = $this->moveToLegal();
        $this->plmApprove($legal);
        $this->assertSame(802, $this->code($legal));

        // the same approve again (old tab, double click) is refused and the case stays put
        $this->plmApprove($legal)->assertSessionHas('error');
        $this->assertSame(802, $this->code($legal));

        // another lawyer cannot accept; the assigned one can
        $this->act($this->lawyer2, $legal, 802, 'APRV')->assertSessionHas('error');
        $this->assertSame(802, $this->code($legal));
        $this->act($this->lawyer, $legal, 802, 'APRV');
        $this->assertSame(803, $this->code($legal));

        // an accepted case cannot be pushed back by an old form or closed on the approval screens
        $this->act($this->lawyer, $legal, 801, 'RFRBK', 1)->assertSessionHas('error');
        $this->as($this->plm)->post(route('legalCaseClose'), ['legal_id' => $legal->id, 'legal_note' => 'x'])->assertSessionHas('error');
        $this->assertSame(803, $this->code($legal));
        $this->assertNull($legal->fresh()->closed_at);
    }

    public function test_plm_can_accept_for_the_lawyer_and_the_timeline_says_so()
    {
        $legal = $this->moveToLegal();
        $this->plmApprove($legal);
        $this->act($this->plm, $legal, 802, 'APRV');
        $this->assertSame(803, $this->code($legal));
        $note = DB::table('legal_case_updates')->where('legal_id', $legal->id)->where('kind', 'lawyer_approved')->value('note');
        $this->assertContains('for the lawyer', $note);
    }

    public function test_closing_before_acceptance_needs_a_reason_and_lists_under_closed()
    {
        $legal = $this->moveToLegal();
        $this->as($this->plm)->post(route('legalCloseForm'), ['legal_id' => $legal->id])->assertOk()->assertSee('Reason for closing');
        $this->as($this->plm)->post(route('legalCaseClose'), ['legal_id' => $legal->id])->assertSessionHasErrors('legal_note');
        $this->assertNull($legal->fresh()->closed_at);

        $this->as($this->plm)->post(route('legalCaseClose'), ['legal_id' => $legal->id, 'legal_note' => 'Tenant paid in full']);
        $legal = $legal->fresh();
        $this->assertNotNull($legal->closed_at);
        $this->assertSame(1, (int) $legal->legal_is_closed);
        $this->assertSame(0, (int) DB::table('tenant_contracts')->where('id', $legal->tenant_contract_id)->value('status'));
        $this->as($this->plm)->get(route('closedLegalCases', ['ajax' => 1]))->assertSee(route('closedLegalCasesShow', $legal->id));
    }

    public function test_referred_back_case_is_sent_again_as_the_same_case_or_dropped()
    {
        $legal = $this->moveToLegal();
        $this->act($this->plm, $legal, 801, 'RFRBK');
        $this->assertSame(804, $this->code($legal));

        $this->as($this->are)->post(route('areStageStore'), ['legal_id' => $legal->id, 'process_flow' => 802, 'are_status' => 1, 'are_note' => 'Fixed the documents']);
        $this->assertSame(801, $this->code($legal), 'back to PLM, never straight to the lawyer');
        $this->assertSame(1, Legal::where('tenant_contract_id', $legal->tenant_contract_id)->count(), 'no new case row');
        $this->assertTrue(DB::table('legal_case_updates')->where('legal_id', $legal->id)->where('kind', 'are_resent')->exists());
        $this->as($this->are)->post(route('areStageStore'), ['legal_id' => $legal->id, 'are_note' => 'again'])->assertSessionHas('error');

        $this->act($this->plm, $legal, 801, 'RFRBK');
        $this->as($this->are)->get(route('legalCase.show', $legal->id))->assertOk()->assertSee('Drop case')->assertSee('Send for verification');
        $this->as($this->plm)->get(route('referBackCases', ['ajax' => 1]))->assertOk();
        $this->as($this->are)->post(route('legalCloseForm'), ['legal_id' => $legal->id])->assertOk()->assertSee('Why is the case dropped?');
        $this->as($this->are)->post(route('legalCaseClose'), ['legal_id' => $legal->id, 'legal_note' => 'Tenant settled with the landlord']);
        $legal = $legal->fresh();
        $this->assertSame('dropped', $legal->close_reason);
        $this->assertSame(0, (int) DB::table('tenant_contracts')->where('id', $legal->tenant_contract_id)->value('status'));
    }

    public function test_contract_status_is_locked_while_a_case_is_open()
    {
        $legal = $this->moveToLegal();
        $this->as($this->plm)->post(route('tenantContractChangeStatusStore'), [
            'tenant_contract_id' => $legal->tenant_contract_id, 'tenant_contract_status' => 0, 'tenant_contract_comment' => 'Paid'])
            ->assertSessionHas('error');
        $this->assertSame(5, (int) DB::table('tenant_contracts')->where('id', $legal->tenant_contract_id)->value('status'));
        $this->assertNull($legal->fresh()->closed_at);
    }

    public function test_only_money_above_the_new_rent_pays_a_settlement()
    {
        $legal = $this->moveToLegal();
        $c = DB::table('tenant_contracts')->where('id', $legal->tenant_contract_id)->first();
        // agreed on the day the contract started: every month's rent since then is new rent
        $agreed = substr($c->tenant_contract_effective_date ?: $c->tenant_contract_start_date, 0, 10);
        $newRent = LegalSettlement::newRentSince($agreed, $c->id);
        $months = \Modules\BackOffice\Services\RentOutstanding::monthsBetween($agreed, date('Y-m-d'));
        $this->assertGreaterThan(0, $months);
        $this->assertEquals($months * (float) $c->tenant_contract_rent, $newRent, '', 0.001);

        DB::table('receipts_generation')->insertGetId(['tenant_contract_id' => $c->id, 'receipts_generation_payment_method' => 1,
            'receipts_generation_receipt_no' => 'LGF-T-1', 'receipts_generation_receipt_date' => date('Y-m-d'),
            'receipts_generation_type' => 0, 'receipts_generation_amt' => $newRent + 100, 'receipts_generation_approval_status' => 3,
            'receipts_generation_status' => 3, 'receipts_generation_index' => 0, 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $d = LegalSettlement::collectedDetail((object) ['agreed_date' => $agreed], $c->id);
        $this->assertEquals(100, $d['collected'], 'the running rent does not count, the 100 above it does', 0.001);
    }

    public function test_balance_written_off_becomes_a_waiver()
    {
        $legal = $this->moveToLegal();
        $this->plmApprove($legal);
        $this->act($this->lawyer, $legal, 802, 'APRV');
        $this->as($this->lawyer)->post(route('legal.case.settlement', $legal->id), ['settlement_type' => 1, 'agreed_date' => date('Y-m-d'),
            'amount' => 900, 'note' => 'Signed', 'due_date' => [date('Y-m-d', strtotime('+10 days'))], 'due_amount' => [900]])->assertSessionHasNoErrors();
        $rows = LegalSettlement::installments(LegalSettlement::current($legal->id)->id);
        // the lawyer who recorded it cannot verify it; PLM can
        $this->as($this->plm)->post(route('legal.case.settlement.verify', $legal->id), ['decision' => 'verify', 'waiver_amount' => 0,
            'fees_recovered' => 0, 'due_date' => $rows->pluck('due_date')->all(), 'due_amount' => $rows->pluck('amount')->all()]);
        $settlement = LegalSettlement::current($legal->id);
        $this->assertSame(LegalSettlement::ACTIVE, (int) $settlement->status);

        $expected = LegalSettlement::writeOffAmount($settlement, $legal->fresh());
        $this->as($this->plm)->post(route('legal.case.close', $legal->id), ['close_reason' => 'written_off', 'close_note' => 'Tenant left the country']);
        $this->assertNotNull($legal->fresh()->closed_at);
        $waiver = DB::table('legal_waivers')->where('legal_id', $legal->id)->where('reason', 'like', 'Balance written off%')->first();
        if ($expected > 0) {
            $this->assertNotNull($waiver);
            $this->assertEquals($expected, (float) $waiver->amount, '', 0.001);
        } else {
            $this->assertNull($waiver, 'nothing owed, nothing written off');
        }
    }

    public function test_the_person_who_recorded_a_settlement_cannot_verify_it()
    {
        $legal = $this->moveToLegal();
        $this->plmApprove($legal);
        $this->act($this->lawyer, $legal, 802, 'APRV');
        DB::table('legal_settlements')->insert(['legal_id' => $legal->id, 'settlement_type' => 1, 'agreed_date' => date('Y-m-d'),
            'amount' => 100, 'settled_total' => 100, 'status' => LegalSettlement::PENDING, 'entered_by' => $this->plm->id,
            'outstanding_at_entry' => 100, 'created_at' => now(), 'updated_at' => now()]);
        $this->as($this->plm)->post(route('legal.case.settlement.verify', $legal->id), ['decision' => 'verify', 'waiver_amount' => 0,
            'fees_recovered' => 0, 'due_date' => [date('Y-m-d')], 'due_amount' => [100]])->assertSessionHas('error');
        $this->assertSame(LegalSettlement::PENDING, (int) LegalSettlement::current($legal->id)->status);
    }
}
