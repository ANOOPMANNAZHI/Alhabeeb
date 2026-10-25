<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Services\RentDues;
use Modules\BackOffice\Services\RentOutstanding;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\BackOffice\Services\TerminationDuesService;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Services\LegalSettlement;
use Modules\Masters\Services\LegalWaiver;

/**
 * Legal module phase 4 (plan 2026-10-13): a verified settlement waiver leaves every receivable -
 * RentOutstanding, RentDues (aging / receivable v2 / follow-up), Legal Receivable V2, stored termination
 * outstanding, termination dues - and comes back when the settlement breaks or the case is reopened.
 */
class LegalWaiverTest extends TestCase
{
    use DatabaseTransactions;

    private $plm;
    private $lawyer;

    protected function setUp(): void
    {
        parent::setUp();
        SystemMessenger::$smsSender = function () { return true; };
        app('cache')->forget('spatie.permission.cache');
        $this->plm = $this->makeUser('lgw_t_plm', 'backoffice_manager');
        $this->lawyer = $this->makeUser('lgw_t_lawyer', 'legal_advisor');
    }

    protected function tearDown(): void
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

    /** An active legal case on an active contract that owes rent today. */
    private function activeCase()
    {
        $ids = DB::table('tenant_contracts as tc')->join('buildings as b', 'b.id', '=', 'tc.building_id')
            ->where('tc.tenant_contract_status', 1)->where('tc.status', 0)->where('b.building_ownership_type', 4)->where('b.building_status', '1')
            ->where('tc.work_flow_processes_code', 108)->where('b.building_name', 'not ilike', '%in house%')
            ->where('tc.tenant_contract_effective_date', '<=', date('Y-m-d', strtotime('-4 months')))
            ->whereNotIn('tc.id', DB::table('legal')->pluck('tenant_contract_id')->all())
            ->orderBy('tc.id', 'desc')->limit(80)->pluck('tc.id');
        $contractId = null;
        foreach ($ids as $id) {
            if (RentOutstanding::amountForContract($id) > 100) {
                $contractId = $id;
                break;
            }
        }
        $this->assertNotNull($contractId, 'no active contract with rent outstanding');
        $this->as($this->plm)->post(route('tenantContractChangeStatusStore'), [
            'tenant_contract_id' => $contractId, 'tenant_contract_status' => 5, 'tenant_contract_comment' => 'Not paying']);
        $legal = Legal::where('tenant_contract_id', $contractId)->orderBy('id', 'desc')->first();
        $this->as($this->plm)->post(route('plmsApproveReferBackStore'), ['legal_id' => $legal->id, 'process_flow' => 801, 'action_key' => 'APRV',
            'are_status' => 0, 'handled_by' => 1, 'case_type' => 1, 'priority' => 1, 'lawyer_user_id' => $this->lawyer->id, 'legal_note' => 'go']);
        $this->as($this->lawyer)->post(route('plmsApproveReferBackStore'), ['legal_id' => $legal->id, 'process_flow' => 802, 'action_key' => 'APRV',
            'are_status' => 0, 'legal_note' => 'accepted']);
        return $legal->fresh();
    }

    private function settleWithWaiver($legal, $waiver, $agreed = null, $due = null)
    {
        $agreed = $agreed ?: date('Y-m-d');
        $due = $due ?: date('Y-m-d', strtotime('+20 days'));
        $this->as($this->lawyer)->post(route('legal.case.settlement', $legal->id), ['settlement_type' => 1, 'agreed_date' => $agreed,
            'amount' => 50, 'note' => 'deal', 'due_date' => [$due], 'due_amount' => [50]]);
        $this->as($this->plm)->post(route('legal.case.settlement.verify', $legal->id), ['decision' => 'verify', 'waiver_amount' => $waiver,
            'waiver_reason' => 'agreed discount', 'fees_recovered' => 0, 'due_date' => [$due], 'due_amount' => [50]])
            ->assertRedirect(route('legal.case', $legal->id));
    }

    public function test_verified_waiver_reduces_outstanding_everywhere_and_comes_back_when_reopened()
    {
        $legal = $this->activeCase();
        $cid = $legal->tenant_contract_id;
        $before = RentOutstanding::forContract($cid);
        $duesBefore = (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'), ['include_legal' => 1, 'tenant_id' => $legal->tenant_id]);
        $waiver = round(min(60, $before['outstanding'] - 1), 3);

        $this->settleWithWaiver($legal, $waiver);
        $this->assertEquals($waiver, LegalWaiver::forContract($cid));

        // RentOutstanding: outstanding down by the waiver, paid stays the money received
        $after = RentOutstanding::forContract($cid);
        $this->assertEquals(round($before['outstanding'] - $waiver, 3), round($after['outstanding'], 3));
        $this->assertEquals($before['paid'], $after['paid']);
        $this->assertEquals($waiver, $after['waived']);

        // RentDues (aging, tenant receivable v2, follow-up, termination screen): balance down, received unchanged
        $duesAfter = (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'), ['include_legal' => 1, 'tenant_id' => $legal->tenant_id]);
        $this->assertEquals(round($duesBefore[$cid]->ledger['balance'] - $waiver, 3), $duesAfter[$cid]->ledger['balance']);
        $this->assertEquals($duesBefore[$cid]->ledger['received'], $duesAfter[$cid]->ledger['received']);
        $this->assertEquals($waiver, $duesAfter[$cid]->ledger['waived']);

        // Legal Receivable V2 (DB function)
        $no = DB::table('tenant_contracts')->where('id', $cid)->value('tenant_contract_no');
        $row = collect(DB::select('SELECT * FROM legalrentreceivable_v2(current_date) WHERE contract_no = ?', [$no]))->first();
        if ($row) {
            $base = collect(DB::select('SELECT * FROM tenantrentreceivable(current_date) WHERE contract_no = ?', [$no]))->first();
            $this->assertEquals(round(max(0, $base->netamtdue - $waiver), 3), round($row->netamtdue, 3));
        }

        // close as written off, then reopen with "waiver no longer applies": owed again
        $this->as($this->plm)->post(route('legal.case.close', $legal->id), ['close_reason' => 'written_off', 'close_note' => 'rest written off']);
        $this->as($this->plm)->post(route('legal.case.reopen', $legal->id), ['reopen_reason' => 'Cheque bounced', 'waiver_no_longer_applies' => 1]);
        $this->assertEquals(0, LegalWaiver::forContract($cid));
        $this->assertEquals(round($before['outstanding'], 3), round(RentOutstanding::amountForContract($cid), 3));
    }

    public function test_broken_settlement_cancels_its_waiver()
    {
        $legal = $this->activeCase();
        $this->settleWithWaiver($legal, 40, date('Y-m-d', strtotime('-60 days')), date('Y-m-d', strtotime('-45 days')));
        $this->assertEquals(LegalSettlement::BROKEN, LegalSettlement::current($legal->id)->status);
        $this->assertEquals(0, LegalWaiver::forContract($legal->tenant_contract_id));
        $this->assertNotNull(DB::table('legal_waivers')->where('legal_id', $legal->id)->value('cancelled_at'));
    }

    public function test_stored_termination_outstanding_and_termination_dues_net_of_waiver()
    {
        $dues = TerminationDues::whereHas('lines', function ($q) { $q->where('category', 'rent')->where('amount', '>', 100); })
            ->where('balance', '>', 100)->orderBy('id', 'desc')->first();
        if (!$dues) {
            $this->markTestSkipped('no termination dues with rent outstanding in this database');
        }
        $svc = new TerminationDuesService();
        $before = $svc->refresh($dues);

        // a waiver approved after the charges were fixed settles the rent line
        DB::table('legal_waivers')->insert(['legal_id' => 0, 'settlement_id' => 0, 'tenant_contract_id' => $dues->tenant_contract_id,
            'amount' => 75, 'approved_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $after = $svc->refresh($dues->fresh());
        $this->assertEquals(round($before['total']['balance'] - 75, 3), round($after['total']['balance'], 3));
        $this->assertEquals(round($before['total']['waived'] + 75, 3), round($after['total']['waived'], 3));

        // cancelled: the amount is owed again
        DB::table('legal_waivers')->where('tenant_contract_id', $dues->tenant_contract_id)->update(['cancelled_at' => now()]);
        $this->assertEquals($before['total']['balance'], $svc->refresh($dues->fresh())['total']['balance']);
    }

    public function test_no_waivers_leaves_legal_receivable_unchanged()
    {
        if (DB::table('legal_waivers')->whereNull('cancelled_at')->exists()) {
            $this->markTestSkipped('database already has live waivers');
        }
        $legal = collect(DB::select('SELECT count(*) n, coalesce(sum(netamtdue), 0) s FROM legalrentreceivable_v2(current_date)'))->first();
        $base = collect(DB::select("SELECT count(*) n, coalesce(sum(r.netamtdue), 0) s FROM tenantrentreceivable(current_date) r
            WHERE r.contract_no IN (SELECT tc.tenant_contract_no FROM legal l JOIN tenant_contracts tc ON tc.id = l.tenant_contract_id)"))->first();
        $this->assertEquals($base->n, $legal->n);
        $this->assertEquals(round($base->s, 3), round($legal->s, 3));
    }
}
