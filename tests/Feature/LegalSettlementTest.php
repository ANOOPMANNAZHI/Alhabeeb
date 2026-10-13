<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\BackOffice\Services\TerminationDuesService;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Services\LegalCase;
use Modules\Masters\Services\LegalSettlement;

/**
 * Legal module phase 3 (plan 2026-10-13): settlements. Lawyer enters (out of court / court decided),
 * PLM verifies or sends back, collection from receipts oldest first, cheques held, completed / broken,
 * closing rules, bounce after close and reopen as a linked case.
 */
class LegalSettlementTest extends TestCase
{
    use DatabaseTransactions;

    private $plm;
    private $lawyer;
    private $receiptNo = 0;

    protected function setUp()
    {
        parent::setUp();
        Storage::fake('local');
        SystemMessenger::$smsSender = function () { return true; };
        app('cache')->forget('spatie.permission.cache');
        $this->plm = $this->makeUser('lgs_t_plm', 'backoffice_manager');
        $this->lawyer = $this->makeUser('lgs_t_lawyer', 'legal_advisor');
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

    private function activeCase()
    {
        $contractId = DB::table('tenant_contracts as tc')->join('buildings as b', 'b.id', '=', 'tc.building_id')
            ->where('tc.tenant_contract_status', 1)->where('tc.status', 0)->where('b.building_ownership_type', 4)
            ->where('b.building_name', 'not ilike', '%in house%')
            ->whereNotIn('tc.id', DB::table('legal')->pluck('tenant_contract_id')->all())
            ->orderBy('tc.id', 'desc')->value('tc.id');
        $this->as($this->plm)->post(route('tenantContractChangeStatusStore'), [
            'tenant_contract_id' => $contractId, 'tenant_contract_status' => 5, 'tenant_contract_comment' => 'Refuses to pay']);
        $legal = Legal::where('tenant_contract_id', $contractId)->orderBy('id', 'desc')->first();
        $this->as($this->plm)->post(route('plmsApproveReferBackStore'), ['legal_id' => $legal->id, 'process_flow' => 801, 'action_key' => 'APRV',
            'are_status' => 0, 'handled_by' => 1, 'case_type' => 1, 'priority' => 1, 'lawyer_user_id' => $this->lawyer->id, 'legal_note' => 'go']);
        $this->as($this->lawyer)->post(route('plmsApproveReferBackStore'), ['legal_id' => $legal->id, 'process_flow' => 802, 'action_key' => 'APRV',
            'are_status' => 0, 'legal_note' => 'accepted']);
        return $legal->fresh();
    }

    private function enter($legal, array $data = [])
    {
        return $this->as($this->lawyer)->post(route('legal.case.settlement', $legal->id), $data + [
            'settlement_type' => 1, 'agreed_date' => date('Y-m-d'), 'amount' => 900, 'note' => 'Signed at office',
            'due_date' => [date('Y-m-d', strtotime('+10 days')), date('Y-m-d', strtotime('+40 days'))], 'due_amount' => [450, 450]]);
    }

    private function verify($legal, array $data = [])
    {
        $rows = LegalSettlement::installments(LegalSettlement::current($legal->id)->id);
        return $this->as($this->plm)->post(route('legal.case.settlement.verify', $legal->id), $data + [
            'decision' => 'verify', 'waiver_amount' => 0, 'fees_recovered' => 0,
            'due_date' => $rows->pluck('due_date')->all(), 'due_amount' => $rows->pluck('amount')->all()]);
    }

    private function receipt($contractId, $amount, $date, $type = 0)
    {
        return DB::table('receipts_generation')->insertGetId(['tenant_contract_id' => $contractId, 'receipts_generation_payment_method' => 1,
            'receipts_generation_receipt_no' => 'LGS-T-' . (++$this->receiptNo), 'receipts_generation_receipt_date' => $date,
            'receipts_generation_type' => $type, 'receipts_generation_amt' => $amount, 'receipts_generation_approval_status' => 3,
            'receipts_generation_status' => 3, 'receipts_generation_index' => 0, 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function settlement($legal)
    {
        return LegalSettlement::current($legal->id);
    }

    public function test_lawyer_enters_out_of_court_settlement_and_installments_must_add_up()
    {
        $legal = $this->activeCase();
        $this->enter($legal, ['due_amount' => [450, 400]])->assertSessionHas('error');
        $this->assertNull($this->settlement($legal));

        $this->enter($legal)->assertRedirect(route('legal.case', $legal->id));
        $s = $this->settlement($legal);
        $this->assertEquals(LegalSettlement::PENDING, $s->status);
        $this->assertEquals(900, (float) $s->amount);
        $this->assertEquals(2, LegalSettlement::installments($s->id)->count());

        $this->enter($legal)->assertSessionHas('error');   // one at a time
        $this->as($this->plm)->get(route('legal.case', $legal->id))->assertSee('Verify settlement')->assertSee('Waiting for PLM to verify');
    }

    public function test_court_decided_needs_judgment_stage()
    {
        $legal = $this->activeCase();
        $this->enter($legal, ['settlement_type' => 2])->assertSessionHas('error');
        $this->as($this->lawyer)->post(route('legal.case.stage', $legal->id), ['stage_id' => DB::table('legal_stages')->where('code', 'judgment')->value('id'),
            'event_date' => date('Y-m-d'), 'note' => 'Judgment', 'judgment_amount' => 900, 'eviction_ordered' => 0]);
        $this->enter($legal, ['settlement_type' => 2])->assertSessionHasNoErrors();
        $this->assertEquals(2, $this->settlement($legal)->settlement_type);
    }

    public function test_plm_send_back_needs_reason_and_lawyer_corrects_same_settlement()
    {
        $legal = $this->activeCase();
        $this->enter($legal);
        $this->as($this->plm)->post(route('legal.case.settlement.verify', $legal->id), ['decision' => 'sendback', 'sendback_reason' => ''])
            ->assertSessionHasErrors('sendback_reason');
        $this->as($this->plm)->post(route('legal.case.settlement.verify', $legal->id), ['decision' => 'sendback', 'sendback_reason' => 'Dates too far'])
            ->assertRedirect(route('legal.case', $legal->id));
        $s = $this->settlement($legal);
        $this->assertEquals(LegalSettlement::SENT_BACK, $s->status);
        $this->as($this->lawyer)->get(route('legal.case', $legal->id))->assertSee('Sent back by PLM')->assertSee('Dates too far')->assertSee('Correct settlement');

        $this->enter($legal, ['amount' => 800, 'due_date' => [date('Y-m-d', strtotime('+5 days'))], 'due_amount' => [800]]);
        $again = $this->settlement($legal);
        $this->assertEquals($s->id, $again->id);
        $this->assertEquals(LegalSettlement::PENDING, $again->status);
        $this->assertEquals(1, LegalSettlement::installments($s->id)->count());
    }

    public function test_verify_with_waiver_and_fees()
    {
        $legal = $this->activeCase();
        $this->enter($legal);
        $this->verify($legal, ['waiver_amount' => 300])->assertSessionHas('error');                  // waiver needs a reason
        $this->verify($legal, ['fees_recovered' => 50])->assertSessionHas('error');                   // rows no longer add up
        $this->verify($legal, ['waiver_amount' => 300, 'waiver_reason' => 'Paying quickly', 'fees_recovered' => 50,
            'due_date' => [date('Y-m-d', strtotime('+10 days')), date('Y-m-d', strtotime('+40 days'))], 'due_amount' => [500, 450]])
            ->assertRedirect(route('legal.case', $legal->id));
        $s = $this->settlement($legal);
        $this->assertEquals(LegalSettlement::ACTIVE, $s->status);
        $this->assertEquals(950, (float) $s->settled_total);
        $this->assertEquals(300, (float) $s->waiver_amount);
        $this->assertEquals($this->plm->id, $s->verified_by);
    }

    public function test_collection_oldest_first_cheques_held_completed_and_close_rules()
    {
        $legal = $this->activeCase();
        $this->enter($legal);
        $this->verify($legal);
        $cid = $legal->tenant_contract_id;

        // nothing settled yet: paid in full is refused, withdrawn is refused (there is a settlement)
        $this->as($this->plm)->post(route('legal.case.close', $legal->id), ['close_reason' => 'paid_in_full'])->assertSessionHas('error');
        $this->as($this->plm)->post(route('legal.case.close', $legal->id), ['close_reason' => 'withdrawn', 'close_note' => 'x'])->assertSessionHas('error');

        $this->receipt($cid, 999, date('Y-m-d', strtotime('-5 days')));   // before the agreement: not counted
        $this->receipt($cid, 600, date('Y-m-d'));
        DB::table('pdc')->insert(['tenant_contract_id' => $cid, 'pdc_transaction_no' => 'LGS-T', 'pdc_transaction_date' => date('Y-m-d'),
            'pdc_check_date' => date('Y-m-d', strtotime('+40 days')), 'pdc_recieve_date' => date('Y-m-d'), 'pdc_period' => 1, 'bank_id' => 1,
            'pdc_stage' => 1, 'created_by' => 1, 'pdc_amt' => 300, 'pdc_check_no' => '000777']);
        $sched = LegalSettlement::schedule($this->settlement($legal), $cid);
        $this->assertEquals(600, $sched['collected']);
        $this->assertSame(['paid', 'cheque held'], array_map(function ($r) { return $r->status; }, $sched['rows']));
        $this->assertEquals(150, $sched['rows'][1]->paid);
        $this->assertEquals(300, $sched['rows'][1]->held);

        $this->receipt($cid, 300, date('Y-m-d'));
        TerminationDuesService::touchContract($cid);   // the hook every receipt change calls
        $this->assertEquals(LegalSettlement::COMPLETED, $this->settlement($legal)->status);

        $this->as($this->plm)->post(route('legal.case.close', $legal->id), ['close_reason' => 'paid_in_full'])->assertRedirect(route('legal.case', $legal->id));
        $legal = $legal->fresh();
        $this->assertEquals('paid_in_full', $legal->close_reason);
        $this->assertEquals(1, $legal->legal_is_closed);
        $this->assertEquals(DB::table('legal_stages')->where('is_final', true)->value('id'), $legal->stage_id);
        $this->assertEquals(0, DB::table('tenant_contracts')->where('id', $cid)->value('status'));
    }

    public function test_overdue_breaks_the_settlement_and_lawyer_can_resume_and_enter_new()
    {
        $legal = $this->activeCase();
        $hearing = DB::table('legal_stages')->where('code', 'hearing')->value('id');
        $this->as($this->lawyer)->post(route('legal.case.stage', $legal->id), ['stage_id' => $hearing, 'event_date' => date('Y-m-d', strtotime('-70 days')), 'note' => 'h']);
        DB::table('legal_case_updates')->where('legal_id', $legal->id)->where('kind', 'stage')->update(['created_at' => now()->subDays(70)]);
        $this->enter($legal, ['agreed_date' => date('Y-m-d', strtotime('-60 days')),
            'due_date' => [date('Y-m-d', strtotime('-50 days')), date('Y-m-d', strtotime('+10 days'))]]);
        $this->verify($legal);

        // verification already checks the dates: an installment 50 days overdue breaks it at once
        LegalSettlement::checkAll();
        $s = $this->settlement($legal);
        $this->assertEquals(LegalSettlement::BROKEN, $s->status);
        $this->assertTrue(DB::table('legal_case_updates')->where('legal_id', $legal->id)->where('kind', 'settlement_broken')->exists());

        // broken: the case may go back a stage once, and a new settlement may be entered
        $notice = DB::table('legal_stages')->where('code', 'notice')->value('id');
        $this->as($this->lawyer)->post(route('legal.case.stage', $legal->id), ['stage_id' => $notice, 'event_date' => date('Y-m-d'), 'note' => 'fresh notice'])
            ->assertSessionHasNoErrors();
        $this->assertEquals($notice, $legal->fresh()->stage_id);
        $this->enter($legal)->assertSessionHasNoErrors();
        $this->assertEquals(LegalSettlement::PENDING, $this->settlement($legal)->status);
        $this->assertEquals(2, DB::table('legal_settlements')->where('legal_id', $legal->id)->count());
    }

    public function test_overdue_alert_once_per_installment()
    {
        $legal = $this->activeCase();
        $this->enter($legal, ['agreed_date' => date('Y-m-d', strtotime('-20 days')),
            'due_date' => [date('Y-m-d', strtotime('-5 days')), date('Y-m-d', strtotime('+10 days'))]]);
        $this->verify($legal);
        $this->assertEquals(1, LegalSettlement::checkAll()['overdue_alerts']);
        $this->assertEquals(0, LegalSettlement::checkAll()['overdue_alerts']);
        $this->assertEquals(LegalSettlement::ACTIVE, $this->settlement($legal)->status, '5 days overdue is not broken yet');
    }

    public function test_bounce_after_close_alerts_and_reopen_links_a_new_case()
    {
        $legal = $this->activeCase();
        $this->enter($legal, ['amount' => 900, 'due_date' => [date('Y-m-d')], 'due_amount' => [900]]);
        $this->verify($legal, ['waiver_amount' => 100, 'waiver_reason' => 'deal']);
        $cid = $legal->tenant_contract_id;
        $receipt = $this->receipt($cid, 900, date('Y-m-d'));
        TerminationDuesService::touchContract($cid);
        $this->as($this->plm)->post(route('legal.case.close', $legal->id), ['close_reason' => 'paid_in_full']);

        // the settlement cheque bounces: receipt cancelled
        DB::table('receipts_generation')->where('id', $receipt)->update(['receipts_generation_status' => 2]);
        TerminationDuesService::touchContract($cid);
        $this->assertNotNull($this->settlement($legal)->shortfall_alerted_at);
        $this->as($this->plm)->get(route('legal.case', $legal->id))->assertSee('Reopen as new case');

        $this->as($this->plm)->post(route('legal.case.reopen', $legal->id), ['reopen_reason' => ''])->assertSessionHasErrors('reopen_reason');
        $this->as($this->plm)->post(route('legal.case.reopen', $legal->id), ['reopen_reason' => 'Cheque bounced', 'waiver_no_longer_applies' => 1]);
        $new = Legal::where('tenant_contract_id', $cid)->whereNull('closed_at')->first();
        $this->assertNotNull($new);
        $this->assertEquals($legal->id, $new->parent_legal_id);
        $this->assertEquals(801, $new->work_flow_processes_code);
        $this->assertEquals($this->lawyer->id, $new->lawyer_user_id);
        $this->assertEquals(5, DB::table('tenant_contracts')->where('id', $cid)->value('status'));
        $this->assertNotNull($this->settlement($legal)->waiver_cancelled_at);
        $this->as($this->plm)->post(route('legal.case.reopen', $legal->id), ['reopen_reason' => 'again'])->assertSessionHas('error');
    }

    public function test_withdrawn_close_needs_reason_and_old_end_note_is_redirected()
    {
        $legal = $this->activeCase();
        $this->as($this->plm)->post(route('legalNoteStore'), ['legal_id' => $legal->id, 'legal_notes_status' => 0, 'legal_notes_note' => 'end', 'current_url' => '/x'])
            ->assertRedirect(route('legal.case', $legal->id));
        $this->assertNull($legal->fresh()->closed_at);
        $this->assertNotEquals(1, $legal->fresh()->legal_is_closed);

        $this->as($this->plm)->post(route('legal.case.close', $legal->id), ['close_reason' => 'withdrawn'])->assertSessionHas('error');
        $this->as($this->lawyer)->post(route('legal.case.close', $legal->id), ['close_reason' => 'withdrawn', 'close_note' => 'x'])->assertStatus(403);
        $this->as($this->plm)->post(route('legal.case.close', $legal->id), ['close_reason' => 'withdrawn', 'close_note' => 'Tenant left, nothing to recover']);
        $this->assertEquals('withdrawn', $legal->fresh()->close_reason);
    }

    public function test_receipts_since_the_agreement_are_listed_in_verify_popup_and_card()
    {
        $legal = $this->activeCase();
        $cid = $legal->tenant_contract_id;
        $this->enter($legal, ['agreed_date' => date('Y-m-d', strtotime('-3 days'))]);

        $this->receipt($cid, 111, date('Y-m-d', strtotime('-10 days')));                 // before the agreement: not listed
        $approved = $this->receipt($cid, 450, date('Y-m-d', strtotime('-2 days')));
        $waiting = $this->receipt($cid, 70, date('Y-m-d'));
        DB::table('receipts_generation')->where('id', $waiting)->update(['receipts_generation_approval_status' => 1, 'receipts_generation_status' => 0]);
        $bounced = $this->receipt($cid, 90, date('Y-m-d'));
        DB::table('receipts_generation')->where('id', $bounced)->update(['receipts_generation_status' => 2]);

        $rows = LegalSettlement::receipts($this->settlement($legal), $cid)->keyBy('id');
        $this->assertCount(3, $rows);
        $this->assertTrue($rows[$approved]->counts);
        $this->assertFalse($rows[$waiting]->counts);
        $this->assertSame('Waiting approval', $rows[$waiting]->state[0]);
        $this->assertFalse($rows[$bounced]->counts);
        $this->assertSame('Cancelled', $rows[$bounced]->state[0]);

        // PLM sees them while verifying (pending settlement) ...
        $this->as($this->plm)->get(route('legal.case', $legal->id))->assertStatus(200)
            ->assertSee('Receipts since')->assertSee('LGS-T-2')->assertSee('Waiting approval')->assertSee('Cancelled');
        // ... and on the card once verified, next to the collected figure
        $this->verify($legal);
        $this->as($this->lawyer)->get(route('legal.case', $legal->id))->assertSee('Receipts since')->assertSee('450.000');
        $this->assertEquals(450, LegalSettlement::schedule($this->settlement($legal), $cid)['collected']);
    }
}
