<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Services\LegalCase;

/**
 * Legal module phase 2 (plan 2026-10-13): case page, stage updates (fields per stage, no moving back,
 * only the assigned lawyer), eviction turns the case into a recovery case, comments with files,
 * Legal Stages master, active-case filters and the lawyer summary.
 */
class LegalCasePageTest extends TestCase
{
    use DatabaseTransactions;

    private $plm;
    private $lawyer;
    private $lawyer2;

    protected function setUp()
    {
        parent::setUp();
        Storage::fake('local');
        SystemMessenger::$smsSender = function () { return true; };
        app('cache')->forget('spatie.permission.cache');
        $this->plm = $this->makeUser('lgp_t_plm', 'backoffice_manager');
        $this->lawyer = $this->makeUser('lgp_t_lawyer', 'legal_advisor');
        $this->lawyer2 = $this->makeUser('lgp_t_lawyer2', 'legal_advisor');
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

    /** An active (803) case on an occupying tenant, assigned to $this->lawyer. */
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
        $legal = $legal->fresh();
        $this->assertEquals(803, $legal->work_flow_processes_code);
        return $legal;
    }

    private function stageId($code)
    {
        return DB::table('legal_stages')->where('code', $code)->value('id');
    }

    private function stage($user, $legal, $code, array $data = [])
    {
        return $this->as($user)->post(route('legal.case.stage', $legal->id), $data + [
            'stage_id' => $this->stageId($code), 'event_date' => date('Y-m-d'), 'note' => 'Stage ' . $code]);
    }

    public function test_case_page_renders_and_only_the_assigned_lawyer_updates_stages()
    {
        $legal = $this->activeCase();
        $this->as($this->lawyer)->get(route('legal.case', $legal->id))->assertStatus(200)
            ->assertSee('Update stage')->assertSee('Case timeline')->assertSee('Lawyer approved');
        $this->as($this->lawyer2)->get(route('legal.case', $legal->id))->assertStatus(200)->assertDontSee('lcpStageModal');
        $this->stage($this->lawyer2, $legal, 'notice')->assertStatus(403);
        $this->stage($this->plm, $legal, 'notice')->assertStatus(403);   // PLM has no stage permission
        $this->assertNull($legal->fresh()->stage_id);
    }

    public function test_filed_stage_needs_case_no_and_records_court_and_claim()
    {
        $legal = $this->activeCase();
        $this->stage($this->lawyer, $legal, 'filed')->assertSessionHasErrorsIn('modal', 'court_case_no');

        $this->stage($this->lawyer, $legal, 'filed', ['court_case_no' => 'RDC/2026/551', 'court_name' => 'Rental Dispute Committee',
            'next_hearing_date' => date('Y-m-d', strtotime('+10 days')), 'files' => [UploadedFile::fake()->create('filing.pdf', 100)]])
            ->assertRedirect(route('legal.case', $legal->id));
        $legal = $legal->fresh();
        $this->assertEquals($this->stageId('filed'), $legal->stage_id);
        $this->assertEquals('RDC/2026/551', $legal->court_case_no);
        $this->assertEquals(round(LegalCase::outstanding($legal), 3), (float) $legal->claim_amount, 'claim defaults to the outstanding');
        $this->assertEquals(date('Y-m-d', strtotime('+10 days')), substr($legal->next_hearing_date, 0, 10));

        $row = DB::table('legal_case_updates')->where('legal_id', $legal->id)->where('kind', 'stage')->first();
        $this->assertContains('Case no. RDC/2026/551', $row->note);
        $doc = DB::table('legal_documents')->where('update_id', $row->id)->first();
        $this->assertEquals('filing.pdf', $doc->legal_documents_name);
        Storage::disk('local')->assertExists($doc->legal_documents_file_name);

        $this->as($this->lawyer)->get(route('legal.case', $legal->id))->assertSee('RDC/2026/551')->assertSee('filing.pdf');
    }

    public function test_stage_cannot_move_back_but_can_repeat()
    {
        $legal = $this->activeCase();
        $this->stage($this->lawyer, $legal, 'hearing');
        $this->stage($this->lawyer, $legal, 'hearing')->assertSessionHasNoErrors();   // another hearing
        $this->stage($this->lawyer, $legal, 'notice')->assertSessionHas('modal_error');
        $this->assertEquals($this->stageId('hearing'), $legal->fresh()->stage_id);
        $this->assertEquals(2, DB::table('legal_case_updates')->where('legal_id', $legal->id)->where('kind', 'stage')->count());
    }

    public function test_judgment_on_occupying_case_needs_eviction_answer_and_eviction_turns_it_into_recovery()
    {
        $legal = $this->activeCase();
        $this->stage($this->lawyer, $legal, 'judgment', ['judgment_amount' => 1500])->assertSessionHasErrorsIn('modal', 'eviction_ordered');
        $this->stage($this->lawyer, $legal, 'judgment', ['judgment_amount' => 1500, 'eviction_ordered' => 1]);
        $legal = $legal->fresh();
        $this->assertEquals(1500, (float) $legal->judgment_amount);
        $this->assertTrue((bool) $legal->eviction_ordered);

        $this->stage($this->lawyer, $legal, 'eviction');
        $legal = $legal->fresh();
        $this->assertEquals(2, $legal->case_type);
        $this->assertTrue(DB::table('legal_case_updates')->where('legal_id', $legal->id)->where('kind', 'type_changed')->exists());
        $this->as($this->lawyer)->get(route('legal.case', $legal->id))->assertStatus(200)->assertSee('Start termination');
    }

    public function test_comments_with_files_and_closed_case_refuses_comments()
    {
        $legal = $this->activeCase();
        $this->as($this->plm)->post(route('legal.case.comment', $legal->id), ['comment' => ''])->assertSessionHasErrors('comment');
        $this->as($this->plm)->post(route('legal.case.comment', $legal->id), ['comment' => 'Tenant called, promises to pay',
            'files' => [UploadedFile::fake()->image('letter.jpg')]])->assertRedirect(route('legal.case', $legal->id));
        $row = DB::table('legal_case_updates')->where('legal_id', $legal->id)->where('kind', 'comment')->first();
        $this->assertEquals('Tenant called, promises to pay', $row->note);
        $this->assertEquals(1, DB::table('legal_documents')->where('update_id', $row->id)->count());

        $legal->update(['closed_at' => now()]);
        $this->as($this->plm)->post(route('legal.case.comment', $legal->id), ['comment' => 'late'])->assertSessionHas('error');
        $this->as($this->lawyer)->get(route('legal.case', $legal->id))->assertDontSee('Add comment');
    }

    public function test_stage_master_add_rename_move_and_permission()
    {
        $this->as($this->lawyer)->get(route('legal.stages'))->assertStatus(403);
        $this->as($this->plm)->get(route('legal.stages'))->assertStatus(200)->assertSee('Eviction completed');

        $this->as($this->plm)->post(route('legal.stages.store'), ['name' => 'Mediation', 'applies_to' => 0]);
        $final = DB::table('legal_stages')->where('is_final', true)->first();
        $mediation = DB::table('legal_stages')->where('name', 'Mediation')->first();
        $this->assertLessThan($final->sort_order, $mediation->sort_order, 'new stages go before Closed');

        $this->as($this->plm)->post(route('legal.stages.store'), ['name' => 'hearing', 'applies_to' => 0])->assertSessionHas('error');

        $before = DB::table('legal_stages')->where('is_final', false)->where('sort_order', '<', $mediation->sort_order)->orderBy('sort_order', 'desc')->first();
        $this->as($this->plm)->post(route('legal.stages.move', $mediation->id), ['dir' => 'up']);
        $this->assertEquals($before->sort_order, DB::table('legal_stages')->where('id', $mediation->id)->value('sort_order'));

        $this->as($this->plm)->post(route('legal.stages.store'), ['id' => $mediation->id, 'name' => 'Mediation session', 'applies_to' => 2, 'status' => 0]);
        $this->assertEquals(0, DB::table('legal_stages')->where('id', $mediation->id)->value('status'));
    }

    public function test_active_case_filters_and_lawyer_summary()
    {
        $legal = $this->activeCase();
        $this->stage($this->lawyer, $legal, 'hearing', ['next_hearing_date' => date('Y-m-d', strtotime('+3 days'))]);

        $this->as($this->plm)->get(route('activeCases', ['ajax' => 1, 'lc_stage' => $this->stageId('hearing'), 'lc_lawyer' => $this->lawyer->id, 'lc_hearing' => 7]))
            ->assertSee(route('legal.case', $legal->id));
        $this->as($this->plm)->get(route('activeCases', ['ajax' => 1, 'lc_stage' => $this->stageId('judgment'), 'lc_lawyer' => $this->lawyer->id]))
            ->assertDontSee(route('legal.case', $legal->id));

        $summary = LegalCase::lawyerSummary($this->lawyer->id);
        $this->assertEquals(1, $summary['total']);
        $this->assertEquals($legal->id, $summary['hearings']->first()->id);
        $this->as($this->lawyer)->get(route('home'))->assertStatus(200);
    }
}
