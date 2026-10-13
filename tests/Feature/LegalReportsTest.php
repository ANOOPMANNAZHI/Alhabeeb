<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Services\LegalReports;

/**
 * Legal module phase 5 (plan 2026-10-13): Legal Cases Summary and Legal Settlements reports -
 * figures, filters, summaries, PDF / Excel downloads and permissions.
 */
class LegalReportsTest extends TestCase
{
    use DatabaseTransactions;

    private $plm;
    private $lawyer;

    protected function setUp()
    {
        parent::setUp();
        SystemMessenger::$smsSender = function () { return true; };
        app('cache')->forget('spatie.permission.cache');
        $this->plm = $this->makeUser('lgr_t_plm', 'backoffice_manager');
        $this->lawyer = $this->makeUser('lgr_t_lawyer', 'legal_advisor');
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

    /** Active case with a verified settlement of 600 (2 x 300) and 300 collected. */
    private function caseWithSettlement()
    {
        $contractId = DB::table('tenant_contracts as tc')->join('buildings as b', 'b.id', '=', 'tc.building_id')
            ->where('tc.tenant_contract_status', 1)->where('tc.status', 0)->where('b.building_ownership_type', 4)
            ->where('b.building_name', 'not ilike', '%in house%')
            ->whereNotIn('tc.id', DB::table('legal')->pluck('tenant_contract_id')->all())
            ->orderBy('tc.id', 'desc')->value('tc.id');
        $this->as($this->plm)->post(route('tenantContractChangeStatusStore'), [
            'tenant_contract_id' => $contractId, 'tenant_contract_status' => 5, 'tenant_contract_comment' => 'Not paying']);
        $legal = Legal::where('tenant_contract_id', $contractId)->orderBy('id', 'desc')->first();
        $this->as($this->plm)->post(route('plmsApproveReferBackStore'), ['legal_id' => $legal->id, 'process_flow' => 801, 'action_key' => 'APRV',
            'are_status' => 0, 'handled_by' => 1, 'case_type' => 1, 'priority' => 1, 'lawyer_user_id' => $this->lawyer->id, 'legal_note' => 'go']);
        $this->as($this->lawyer)->post(route('plmsApproveReferBackStore'), ['legal_id' => $legal->id, 'process_flow' => 802, 'action_key' => 'APRV',
            'are_status' => 0, 'legal_note' => 'accepted']);
        $hearing = DB::table('legal_stages')->where('code', 'hearing')->value('id');
        $this->as($this->lawyer)->post(route('legal.case.stage', $legal->id), ['stage_id' => $hearing, 'event_date' => date('Y-m-d'), 'note' => 'h']);
        $dates = [date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+20 days'))];
        $this->as($this->lawyer)->post(route('legal.case.settlement', $legal->id), ['settlement_type' => 1, 'agreed_date' => date('Y-m-d', strtotime('-15 days')),
            'amount' => 600, 'note' => 'deal', 'due_date' => $dates, 'due_amount' => [300, 300]]);
        $this->as($this->plm)->post(route('legal.case.settlement.verify', $legal->id), ['decision' => 'verify', 'waiver_amount' => 0,
            'fees_recovered' => 0, 'due_date' => $dates, 'due_amount' => [300, 300]]);
        DB::table('receipts_generation')->insert(['tenant_contract_id' => $contractId, 'receipts_generation_payment_method' => 1,
            'receipts_generation_receipt_no' => 'LGR-T-1', 'receipts_generation_receipt_date' => date('Y-m-d', strtotime('-12 days')),
            'receipts_generation_type' => 0, 'receipts_generation_amt' => 200, 'receipts_generation_approval_status' => 3,
            'receipts_generation_status' => 3, 'receipts_generation_index' => 0, 'created_by' => 1]);
        return $legal->fresh();
    }

    public function test_cases_report_rows_summary_and_filters()
    {
        $legal = $this->caseWithSettlement();
        $rows = LegalReports::cases(['status' => 'open', 'lawyer' => $this->lawyer->id]);
        $this->assertCount(1, $rows);
        $r = $rows->first();
        $this->assertEquals($legal->id, $r->id);
        $this->assertEquals('Active', $r->state);
        $this->assertEquals('Hearing', $r->stage_name);
        $this->assertEquals(0, $r->days_in_legal);
        $this->assertEquals('Active', $r->settlement_status);
        $this->assertEquals(400, $r->settlement_balance);

        $summary = LegalReports::casesSummary($rows);
        $this->assertEquals(1, $summary['byStage']['Hearing'][1]);
        $this->assertEquals(1, $summary['byAge']['0-30']['total']);

        $this->assertCount(0, LegalReports::cases(['status' => 'closed', 'lawyer' => $this->lawyer->id]));
        $this->assertCount(0, LegalReports::cases(['status' => 'open', 'lawyer' => $this->lawyer->id, 'priority' => 3]));

        $this->as($this->plm)->get(route('legal.reports.cases', ['lawyer' => $this->lawyer->id]))->assertStatus(200)
            ->assertSee('Open cases by stage and priority')->assertSee(route('legal.case', $legal->id));
    }

    public function test_settlements_report_collected_overdue_and_totals()
    {
        $legal = $this->caseWithSettlement();
        $rows = LegalReports::settlements(['lawyer' => $this->lawyer->id]);
        $this->assertCount(1, $rows);
        $r = $rows->first();
        $this->assertEquals(200, $r->collected);
        $this->assertEquals(400, $r->balance);
        $this->assertEquals(100, $r->overdue);             // first installment 300, 200 paid, due 10 days ago
        $this->assertEquals(10, $r->oldest_overdue_days);
        $this->assertEquals(date('Y-m-d', strtotime('+20 days')), substr($r->next_due, 0, 10));
        $t = LegalReports::settlementTotals($rows);
        $this->assertEquals(600, $t['settled_total']);
        $this->assertEquals(400, $t['balance']);

        $this->as($this->plm)->get(route('legal.reports.settlements', ['lawyer' => $this->lawyer->id]))->assertStatus(200)
            ->assertSee($legal->tenantContract->tenant_contract_no);
    }

    public function test_pdf_and_excel_downloads_and_permission()
    {
        $this->caseWithSettlement();
        $pdf = $this->as($this->plm)->get(route('legal.reports.cases', ['lawyer' => $this->lawyer->id, 'export' => 'pdf']));
        $pdf->assertStatus(200);
        $this->assertContains('application/pdf', $pdf->headers->get('content-type'));
        $xls = $this->as($this->plm)->get(route('legal.reports.settlements', ['export' => 'excel']));
        $xls->assertStatus(200);
        $this->assertContains('.xlsx', $xls->headers->get('content-disposition'));

        $are = $this->makeUser('lgr_t_are', 'are');
        $this->as($are)->get(route('legal.reports.cases'))->assertStatus(403);
        $this->as($are)->get(route('legal.reports.settlements'))->assertStatus(403);
    }
}
