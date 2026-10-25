<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Services\LegalCase;

/**
 * Legal module phase 1 (plan 2026-10-13) through the routes: Move to Legal opens one case per contract,
 * handled-by default from building ownership, PLM approval with priority / lawyer / type, hand over and
 * close for Landlord / Government / MERA, lawyer reject back to PLM with a reason, timeline rows.
 * Uses its own PLM and lawyer users inside a rolled-back transaction.
 */
class LegalWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private $plm;
    private $lawyer;
    private $lawyer2;

    protected function setUp()
    {
        parent::setUp();
        SystemMessenger::$smsSender = function () { return true; };
        app('cache')->forget('spatie.permission.cache');
        $this->plm = $this->makeUser('lg_t_plm', 'backoffice_manager');
        $this->lawyer = $this->makeUser('lg_t_lawyer', 'legal_advisor');
        $this->lawyer2 = $this->makeUser('lg_t_lawyer2', 'legal_advisor');
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

    /** An active contract not in legal, in a building of the given ownership type. */
    private function contract($ownership)
    {
        $id = DB::table('tenant_contracts as tc')->join('buildings as b', 'b.id', '=', 'tc.building_id')
            ->where('tc.tenant_contract_status', 1)->where('tc.status', 0)
            ->where('b.building_ownership_type', $ownership)->where('b.building_name', 'not ilike', '%in house%')
            ->whereNotIn('tc.id', DB::table('legal')->pluck('tenant_contract_id')->all())
            ->orderBy('tc.id', 'desc')->value('tc.id');
        $this->assertNotNull($id, "no active contract in an ownership-$ownership building");
        return $id;
    }

    private function moveToLegal($contractId)
    {
        $this->as($this->plm)->post(route('tenantContractChangeStatusStore'), [
            'tenant_contract_id' => $contractId, 'tenant_contract_status' => 5, 'tenant_contract_comment' => 'Not paying since June']);
        return Legal::where('tenant_contract_id', $contractId)->orderBy('id', 'desc')->first();
    }

    private function store($user, $legal, array $data)
    {
        return $this->as($user)->post(route('plmsApproveReferBackStore'), $data + ['legal_id' => $legal->id]);
    }

    private function kinds($legal)
    {
        return DB::table('legal_case_updates')->where('legal_id', $legal->id)->orderBy('id')->pluck('kind')->all();
    }

    public function test_move_to_legal_opens_one_case_with_defaults()
    {
        $contractId = $this->contract(4);
        $legal = $this->moveToLegal($contractId);

        $this->assertNotNull($legal);
        $this->assertEquals(801, $legal->work_flow_processes_code);
        $this->assertEquals(LegalCase::HANDLED_ALHABIB, $legal->handled_by);
        $this->assertEquals(1, $legal->case_type, 'active contract = occupying / refusing');
        $this->assertSame(['moved'], $this->kinds($legal));

        // a second move does not create another case or touch its users
        $users = DB::table('legal_users')->where('legal_id', $legal->id)->count();
        $this->moveToLegal($contractId);
        $this->assertEquals($users, DB::table('legal_users')->where('legal_id', $legal->id)->count());
        $this->assertEquals(1, Legal::where('tenant_contract_id', $contractId)->count());
    }

    public function test_government_and_mera_buildings_default_to_their_own_legal()
    {
        $this->assertEquals(LegalCase::HANDLED_GOVERNMENT, $this->moveToLegal($this->contract(2))->handled_by);
        $this->assertEquals(LegalCase::HANDLED_MERA, $this->moveToLegal($this->contract(1))->handled_by);
    }

    public function test_plm_approval_needs_priority_and_lawyer_and_records_them()
    {
        $legal = $this->moveToLegal($this->contract(4));
        $base = ['process_flow' => 801, 'action_key' => 'APRV', 'are_status' => 0, 'handled_by' => 1, 'legal_note' => 'Go ahead'];

        $this->store($this->plm, $legal, $base + ['case_type' => 1])->assertSessionHasErrors(['priority', 'lawyer_user_id']);
        $this->assertEquals(801, $legal->fresh()->work_flow_processes_code);

        $this->store($this->plm, $legal, $base + ['case_type' => 1, 'priority' => 1, 'lawyer_user_id' => $this->lawyer->id])
            ->assertRedirect(route('plmsApproval'));
        $legal = $legal->fresh();
        $this->assertEquals(802, $legal->work_flow_processes_code);
        $this->assertEquals(1, $legal->priority);
        $this->assertEquals($this->lawyer->id, $legal->lawyer_user_id);
        $this->assertSame(['moved', 'plm_approved'], $this->kinds($legal));
    }

    public function test_hand_over_closes_the_case_and_keeps_contract_in_legal()
    {
        $contractId = $this->contract(2);
        $legal = $this->moveToLegal($contractId);
        $data = ['process_flow' => 801, 'action_key' => 'APRV', 'are_status' => 0, 'handled_by' => 3, 'legal_note' => 'Ministry handles it'];

        $this->store($this->plm, $legal, $data)->assertSessionHasErrors(['handed_to_name', 'handover_date']);
        $this->assertNull($legal->fresh()->closed_at);

        $this->store($this->plm, $legal, $data + ['handed_to_name' => 'Ministry of Housing legal dept', 'handover_ref' => 'MH/22',
            'handover_date' => date('Y-m-d')])->assertRedirect(route('plmsApproval'));
        $legal = $legal->fresh();
        $this->assertNotNull($legal->closed_at);
        $this->assertEquals('handed_over', $legal->close_reason);
        $this->assertEquals(801, $legal->work_flow_processes_code, 'no lawyer step');
        $this->assertEquals(5, DB::table('tenant_contracts')->where('id', $contractId)->value('status'));
        $this->assertSame(['moved', 'handed_over', 'closed'], $this->kinds($legal));

        $this->as($this->plm)->get(route('handedOverLegalCases'))->assertStatus(200)->assertSee('MH/22');
        // closed cases leave the PLM queue
        $this->as($this->plm)->get(route('plmsApproval', ['ajax' => 1]))->assertDontSee(route('plmsApprovalShow', $legal->id));
    }

    public function test_plm_can_switch_a_case_to_landlord_and_hand_it_over()
    {
        $legal = $this->moveToLegal($this->contract(4));
        $this->store($this->plm, $legal, ['process_flow' => 801, 'action_key' => 'APRV', 'are_status' => 0, 'handled_by' => 2,
            'legal_note' => 'Landlord uses own lawyer', 'handed_to_name' => 'Landlord lawyer', 'handover_date' => date('Y-m-d')]);
        $legal = $legal->fresh();
        $this->assertEquals(LegalCase::HANDLED_LANDLORD, $legal->handled_by);
        $this->assertSame(['moved', 'handled_by', 'handed_over', 'closed'], $this->kinds($legal));
    }

    public function test_lawyer_reject_needs_reason_and_goes_back_to_plm()
    {
        $legal = $this->moveToLegal($this->contract(4));
        $this->store($this->plm, $legal, ['process_flow' => 801, 'action_key' => 'APRV', 'are_status' => 0, 'handled_by' => 1,
            'case_type' => 1, 'priority' => 2, 'lawyer_user_id' => $this->lawyer->id, 'legal_note' => 'ok']);

        // only the assigned lawyer sees it
        $this->as($this->lawyer)->get(route('lawyerApproval', ['ajax' => 1, 'sort' => 'id', 'direction' => 'desc']))->assertSee(route('lawyerApprovalShow', $legal->id));
        $this->as($this->lawyer2)->get(route('lawyerApproval', ['ajax' => 1, 'sort' => 'id', 'direction' => 'desc']))->assertDontSee(route('lawyerApprovalShow', $legal->id));

        $reject = ['process_flow' => 801, 'action_key' => 'RFRBK', 'are_status' => 1];
        $this->store($this->lawyer, $legal, $reject + ['legal_note' => ''])->assertSessionHasErrors('legal_note');
        $this->assertEquals(802, $legal->fresh()->work_flow_processes_code);

        $this->store($this->lawyer, $legal, $reject + ['legal_note' => 'Notice period not finished'])->assertRedirect(route('lawyerApproval'));
        $legal = $legal->fresh();
        $this->assertEquals(801, $legal->work_flow_processes_code);
        $this->assertSame(['moved', 'plm_approved', 'lawyer_rejected'], $this->kinds($legal));
        $this->as($this->plm)->get(route('plmsApprovalShow', $legal->id))->assertStatus(200)
            ->assertSee('Rejected by lawyer')->assertSee('Notice period not finished');

        // PLM sends it again, the lawyer approves
        $this->store($this->plm, $legal, ['process_flow' => 801, 'action_key' => 'APRV', 'are_status' => 0, 'handled_by' => 1,
            'case_type' => 1, 'priority' => 1, 'lawyer_user_id' => $this->lawyer->id, 'legal_note' => 'Notice done now']);
        $this->store($this->lawyer, $legal, ['process_flow' => 802, 'action_key' => 'APRV', 'are_status' => 0, 'legal_note' => 'Filing'])
            ->assertRedirect(route('lawyerApproval'));
        $legal = $legal->fresh();
        $this->assertEquals(803, $legal->work_flow_processes_code);
        $this->assertEquals(2, $legal->legal_is_closed);
        $this->assertSame(['moved', 'plm_approved', 'lawyer_rejected', 'plm_approved', 'lawyer_approved'], $this->kinds($legal));
    }

    public function test_closing_marks_the_case_and_a_new_move_opens_a_linked_case()
    {
        $contractId = $this->contract(4);
        $legal = $this->moveToLegal($contractId);
        $this->as($this->plm)->post(route('legalCaseClose'), ['legal_id' => $legal->id, 'legal_note' => 'Not pursued']);
        $legal = $legal->fresh();
        $this->assertNotNull($legal->closed_at);
        $this->assertEquals(0, DB::table('tenant_contracts')->where('id', $contractId)->value('status'));

        $again = $this->moveToLegal($contractId);
        $this->assertNotEquals($legal->id, $again->id);
        $this->assertEquals($legal->id, $again->parent_legal_id);
        $this->assertSame(['moved', 'reopened'], $this->kinds($again));
    }

    public function test_approval_screens_render()
    {
        $legal = $this->moveToLegal($this->contract(4));
        $this->as($this->plm)->get(route('plmsApprovalShow', $legal->id))->assertStatus(200)->assertSee('Case timeline')->assertSee('Moved to legal');
        $this->as($this->plm)->post(route('plmsApproveReferBack'), ['legal_id' => $legal->id, 'process_flow' => 801, 'action_key' => 'APRV', 'are_status' => 0])
            ->assertStatus(200)->assertSee('lc_handled_by')->assertSee('Lg_t_lawyer');
    }

    public function test_case_page_only_after_the_lawyer_accepts()
    {
        $legal = $this->moveToLegal($this->contract(4));
        $caseUrl = route('legal.case', $legal->id);

        // at PLM approval: no case page links, the case page sends you to the PLM screen
        $this->as($this->plm)->get(route('plmsApproval', ['ajax' => 1, 'sort' => 'id', 'direction' => 'desc']))
            ->assertSee(route('plmsApprovalShow', $legal->id))->assertDontSee($caseUrl);
        $this->as($this->plm)->get(route('plmsApprovalShow', $legal->id))->assertStatus(200)->assertDontSee($caseUrl);
        $this->as($this->plm)->get($caseUrl)->assertRedirect(route('plmsApprovalShow', $legal->id));

        // at lawyer approval: still none
        $this->store($this->plm, $legal, ['process_flow' => 801, 'action_key' => 'APRV', 'are_status' => 0, 'handled_by' => 1,
            'case_type' => 1, 'priority' => 2, 'lawyer_user_id' => $this->lawyer->id, 'legal_note' => 'ok']);
        $this->as($this->lawyer)->get(route('lawyerApprovalShow', $legal->id))->assertStatus(200)->assertDontSee($caseUrl);
        $this->as($this->lawyer)->get($caseUrl)->assertRedirect(route('lawyerApprovalShow', $legal->id));

        // accepted: the case page opens
        $this->store($this->lawyer, $legal, ['process_flow' => 802, 'action_key' => 'APRV', 'are_status' => 0, 'legal_note' => 'go']);
        $this->as($this->lawyer)->get($caseUrl)->assertStatus(200)->assertSee('Update stage');
    }
}
