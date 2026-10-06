<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Modules\Maintenance\Events\NewComplaintEnquiry;
use Modules\Maintenance\Services\Inspection;

/**
 * Building Inspection & Maintenance, end to end through the routes: executive and manager access,
 * visits, the inspection submit, issue action plan / progress / resolve / verify, complaint ticket, reports.
 * Uses its own executives, manager and buildings inside a rolled-back transaction.
 */
class BuildingInspectionTest extends TestCase
{
    use DatabaseTransactions;

    private $exec1;
    private $exec2;
    private $manager;
    private $b1;
    private $b2;

    protected function setUp()
    {
        parent::setUp();
        Storage::fake('local');
        app('cache')->forget('spatie.permission.cache');
        $this->exec1 = $this->makeUser('ins_t_exec1', 'maintenance_executive');
        $this->exec2 = $this->makeUser('ins_t_exec2', 'maintenance_executive');
        $this->manager = $this->makeUser('ins_t_mgr', 'maintenance_manager');
        $buildings = DB::table('buildings')->where('building_status', 1)->where('building_name', 'not ilike', '%in house%')
            ->whereNotIn('id', DB::table('building_executives')->whereNull('assigned_till')->pluck('building_id')->all())
            ->orderBy('id')->limit(2)->pluck('id')->all();
        list($this->b1, $this->b2) = $buildings;
        $this->assign($this->b1, $this->exec1);
        $this->assign($this->b2, $this->exec2);
    }

    /** Switch user: AuthenticateSession logs out when the session still holds the previous user's password hash. */
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

    private function assign($building, $user)
    {
        DB::table('building_executives')->insert(['building_id' => $building, 'user_id' => $user->id, 'assigned_from' => date('Y-m-d'),
            'created_at' => now(), 'updated_at' => now()]);
    }

    private function photo($name = 'p.jpg')
    {
        return UploadedFile::fake()->image($name, 1200, 900);
    }

    private function visit($user, $building, $type = 1, $date = null)
    {
        $this->as($user)->post(route('inspection.visits.store'), ['building_id' => $building, 'visit_type' => $type,
            'planned_date' => $date ?: date('Y-m-d'), 'purpose' => 'test', 'executive_id' => $this->exec2->id]);
        return DB::table('inspection_visits')->where('building_id', $building)->orderBy('id', 'desc')->first();
    }

    private function items()
    {
        $areas = DB::table('inspection_areas')->where('status', 1)->pluck('id')->all();
        return DB::table('inspection_checklist_items')->where('status', 1)->whereIn('area_id', $areas)->pluck('id')->all();
    }

    public function test_executive_schedules_only_own_buildings_for_themselves()
    {
        $v = $this->visit($this->exec1, $this->b1);
        $this->assertEquals($this->exec1->id, $v->executive_id, 'executive_id from the form is ignored for an executive');
        $this->assertStringStartsWith('VIS-', $v->visit_no);

        $this->as($this->exec1)->post(route('inspection.visits.store'), ['building_id' => $this->b2, 'visit_type' => 2, 'planned_date' => date('Y-m-d')])
            ->assertSessionHasErrors('building_id');
        // second monthly inspection in the same month is refused, a follow-up is fine
        $this->as($this->exec1)->post(route('inspection.visits.store'), ['building_id' => $this->b1, 'visit_type' => 1, 'planned_date' => date('Y-m-d')])
            ->assertSessionHasErrors('visit_type');
        $this->as($this->exec1)->post(route('inspection.visits.store'), ['building_id' => $this->b1, 'visit_type' => 2, 'planned_date' => date('Y-m-d')])
            ->assertSessionHasNoErrors();

        $this->as($this->exec1)->get(route('inspection.visits'))->assertOk()->assertSee($v->visit_no);
        $this->as($this->exec2)->get(route('inspection.visits', ['tab' => 'all']))->assertOk()->assertDontSee($v->visit_no);
    }

    public function test_manager_schedules_for_an_executive_and_cannot_inspect()
    {
        $v = $this->visit($this->manager, $this->b2);
        $this->assertEquals($this->exec2->id, $v->executive_id);
        $this->as($this->manager)->get(route('inspection.visits.inspect', $v->id))->assertStatus(403);
        $this->as($this->exec1)->get(route('inspection.visits.inspect', $v->id))->assertStatus(403);
        $this->as($this->exec2)->get(route('inspection.visits.inspect', $v->id))->assertOk()->assertSee('Submit inspection');
    }

    public function test_cancel_needs_a_reason()
    {
        $v = $this->visit($this->exec1, $this->b1);
        $this->as($this->exec1)->post(route('inspection.visits.cancel', $v->id), ['cancel_reason' => 'no'])->assertSessionHasErrors('cancel_reason');
        $this->as($this->exec2)->post(route('inspection.visits.cancel', $v->id), ['cancel_reason' => 'Watchman refused access today'])->assertStatus(403);
        $this->as($this->exec1)->post(route('inspection.visits.cancel', $v->id), ['cancel_reason' => 'Watchman refused access today']);
        $v = DB::table('inspection_visits')->where('id', $v->id)->first();
        $this->assertEquals(Inspection::CANCELLED, $v->status);
        $this->assertEquals('Watchman refused access today', $v->cancel_reason);
        // a cancelled monthly inspection frees the month
        $this->as($this->exec1)->post(route('inspection.visits.store'), ['building_id' => $this->b1, 'visit_type' => 1, 'planned_date' => date('Y-m-d')])
            ->assertSessionHasNoErrors();
    }

    /** Runs a full inspection of b1 with the first line as an issue; returns [visit, issue]. */
    private function inspect()
    {
        $v = $this->visit($this->exec1, $this->b1);
        $items = $this->items();
        $results = array_fill_keys($items, 1);
        $results[$items[0]] = 2;
        $results[$items[1]] = 3;
        $work = DB::table('works')->where('works_code', 'Plumbing')->value('id');

        // missing photo and description -> refused, nothing saved
        $this->as($this->exec1)->post(route('inspection.visits.submit', $v->id), ['results' => $results])->assertSessionHasErrors();
        $this->assertEquals(0, DB::table('inspection_issues')->where('visit_id', $v->id)->count());

        $this->as($this->exec1)->post(route('inspection.visits.submit', $v->id), [
            'results' => $results,
            'issue' => [$items[0] => ['description' => 'Pump leaking at flange', 'work_id' => $work, 'priority' => 2]],
            'issue_photo' => [$items[0] => $this->photo()],
            'general_remarks' => 'All fine otherwise', 'latitude' => '23.5880000', 'longitude' => '58.3829000',
        ])->assertRedirect(route('inspection.visits.show', $v->id));
        return [DB::table('inspection_visits')->where('id', $v->id)->first(), DB::table('inspection_issues')->where('visit_id', $v->id)->first()];
    }

    public function test_inspection_submit_creates_issue_results_and_completes_visit()
    {
        list($v, $issue) = $this->inspect();
        $this->assertEquals(Inspection::COMPLETED, $v->status);
        $this->assertEquals('All fine otherwise', $v->general_remarks);
        $this->assertEquals(count($this->items()), DB::table('inspection_visit_results')->where('visit_id', $v->id)->count());
        $this->assertEquals('Pump leaking at flange', $issue->description);
        $this->assertEquals(2, $issue->priority);
        $this->assertEquals(date('Y-m-d', strtotime('+7 days')), $issue->target_date);
        $this->assertEquals($this->exec1->id, $issue->executive_id);
        $photo = DB::table('inspection_photos')->where('issue_id', $issue->id)->first();
        $this->assertEquals(1, $photo->kind);
        Storage::disk('local')->assertExists($photo->path);
        Storage::disk('local')->assertExists($photo->thumb_path);
        $this->as($this->exec1)->get(route('inspection.visits.show', $v->id))->assertOk()->assertSee($issue->issue_no);
        // submitting again does nothing
        $this->as($this->exec1)->post(route('inspection.visits.submit', $v->id), ['results' => []])->assertRedirect(route('inspection.visits.show', $v->id));
    }

    public function test_follow_up_visit_updates_pending_issues()
    {
        list(, $issue) = $this->inspect();
        $v2 = $this->visit($this->exec1, $this->b1, 2);
        $this->as($this->exec1)->get(route('inspection.visits.inspect', $v2->id))->assertOk()->assertSee($issue->issue_no);
        $results = array_fill_keys($this->items(), 1);
        // Resolved without an after photo is refused
        $this->as($this->exec1)->post(route('inspection.visits.submit', $v2->id), ['results' => $results,
            'followup' => [$issue->id => ['condition' => 2, 'note' => 'fixed']]])->assertSessionHasErrors();
        // Worsened: High -> Critical, target pulled in to 1 day
        $this->as($this->exec1)->post(route('inspection.visits.submit', $v2->id), ['results' => $results,
            'followup' => [$issue->id => ['condition' => 3, 'note' => 'Leak is spreading']]])->assertSessionHasNoErrors();
        $i = DB::table('inspection_issues')->where('id', $issue->id)->first();
        $this->assertEquals(1, $i->priority);
        $this->assertEquals(date('Y-m-d', strtotime('+1 day')), $i->target_date);
        $this->assertEquals(1, DB::table('inspection_issue_updates')->where('issue_id', $issue->id)->where('kind', 'followup')->where('condition', 3)->count());
    }

    public function test_issue_plan_progress_resolve_verify()
    {
        list(, $issue) = $this->inspect();
        $url = route('inspection.issues.show', $issue->id);
        $this->as($this->exec1)->get($url)->assertOk()->assertSee('Save action plan');
        $this->as($this->exec2)->get($url)->assertStatus(404); // not their building

        $this->as($this->exec1)->post(route('inspection.issues.plan', $issue->id), ['action_plan' => 'Replace gasket', 'responsible_party' => 'Contractor',
            'estimated_cost' => '45.5', 'landlord_approval' => 1, 'target_date' => date('Y-m-d', strtotime('+3 days'))])->assertSessionHasNoErrors();
        $i = DB::table('inspection_issues')->where('id', $issue->id)->first();
        $this->assertEquals('45.500', $i->estimated_cost);
        $this->assertTrue((bool) $i->landlord_approval);
        $this->assertEquals(Inspection::IN_PROGRESS, $i->status);

        $this->as($this->exec1)->post(route('inspection.issues.progress', $issue->id), ['note' => 'Contractor on site', 'photo' => $this->photo()])->assertSessionHasNoErrors();
        $this->as($this->exec1)->post(route('inspection.issues.resolve', $issue->id), [])->assertSessionHasErrors('photo');
        $this->as($this->exec1)->post(route('inspection.issues.resolve', $issue->id), ['photo' => $this->photo(), 'note' => 'done'])->assertSessionHasNoErrors();
        $this->assertEquals(Inspection::RESOLVED, DB::table('inspection_issues')->where('id', $issue->id)->value('status'));

        // the executive cannot verify; the manager sends back (needs a note), then closes
        $this->as($this->exec1)->post(route('inspection.issues.verify', $issue->id), ['decision' => 'close'])->assertStatus(403);
        $this->as($this->manager)->post(route('inspection.issues.verify', $issue->id), ['decision' => 'send_back'])->assertSessionHasErrors('note');
        $this->as($this->manager)->post(route('inspection.issues.verify', $issue->id), ['decision' => 'send_back', 'note' => 'Still dripping']);
        $this->assertEquals(Inspection::IN_PROGRESS, DB::table('inspection_issues')->where('id', $issue->id)->value('status'));
        $this->as($this->exec1)->post(route('inspection.issues.resolve', $issue->id), ['photo' => $this->photo()]);
        $this->as($this->manager)->post(route('inspection.issues.verify', $issue->id), ['decision' => 'close']);
        $i = DB::table('inspection_issues')->where('id', $issue->id)->first();
        $this->assertEquals(Inspection::CLOSED, $i->status);
        $this->assertEquals($this->manager->id, $i->closed_by);
        $this->assertEquals(2, DB::table('inspection_photos')->where('issue_id', $issue->id)->where('kind', 3)->count());
        $this->as($this->manager)->get($url)->assertOk()->assertSee('Verified');
    }

    public function test_action_plan_edits_are_recorded_field_by_field()
    {
        list(, $issue) = $this->inspect();
        $plan = ['action_plan' => 'Replace gasket', 'responsible_party' => 'Contractor', 'estimated_cost' => '45.5',
            'landlord_approval' => 0, 'target_date' => $issue->target_date];
        $this->as($this->exec1)->post(route('inspection.issues.plan', $issue->id), $plan)->assertSessionHasNoErrors();
        // first save: the four new fields are recorded as set (target date unchanged)
        $this->assertEquals(['action_plan', 'estimated_cost', 'landlord_approval', 'responsible_party'],
            DB::table('inspection_issue_changes')->where('issue_id', $issue->id)->orderBy('field')->pluck('field')->all());

        // same values again (45.500 = 45.5): nothing recorded
        $this->as($this->exec1)->post(route('inspection.issues.plan', $issue->id), ['estimated_cost' => '45.500'] + $plan)
            ->assertSessionHas('success', 'Nothing changed - the action plan is the same as before.');
        $this->assertEquals(4, DB::table('inspection_issue_changes')->where('issue_id', $issue->id)->count());

        // the manager edits cost and target date, with a reason
        $newTarget = date('Y-m-d', strtotime('+20 days'));
        $this->as($this->manager)->post(route('inspection.issues.plan', $issue->id),
            ['estimated_cost' => '60', 'target_date' => $newTarget, 'change_reason' => 'Contractor quote received'] + $plan)->assertSessionHasNoErrors();
        $edit = DB::table('inspection_issue_changes')->where('issue_id', $issue->id)->where('changed_by', $this->manager->id)->orderBy('field')->get();
        $this->assertCount(2, $edit);
        $this->assertEquals(['estimated_cost', '45.500', '60.000'], [$edit[0]->field, $edit[0]->old_value, $edit[0]->new_value]);
        $this->assertEquals(['target_date', $issue->target_date, $newTarget], [$edit[1]->field, $edit[1]->old_value, $edit[1]->new_value]);
        $note = DB::table('inspection_issue_updates')->where('id', $edit[0]->update_id)->value('note');
        $this->assertEquals("Action plan changed: Estimated cost, Target date.\nReason: Contractor quote received", $note);

        $this->as($this->exec1)->get(route('inspection.issues.show', $issue->id))->assertOk()
            ->assertSee('Change history (2)')->assertSee('OMR 45.500')->assertSee('OMR 60.000')->assertSee('Contractor quote received');
    }

    public function test_worsened_follow_up_is_recorded_in_change_history()
    {
        list(, $issue) = $this->inspect();
        $v2 = $this->visit($this->exec1, $this->b1, 2);
        $this->as($this->exec1)->post(route('inspection.visits.submit', $v2->id), ['results' => array_fill_keys($this->items(), 1),
            'followup' => [$issue->id => ['condition' => 3, 'note' => 'Worse']]])->assertSessionHasNoErrors();
        $c = DB::table('inspection_issue_changes')->where('issue_id', $issue->id)->orderBy('field')->get();
        $this->assertEquals(['priority', '2', '1'], [$c[0]->field, $c[0]->old_value, $c[0]->new_value]);
        $this->assertEquals('target_date', $c[1]->field);
    }

    public function test_supervisor_cannot_verify_own_issue()
    {
        $sup = $this->makeUser('ins_t_sup', 'maintenance_supervisor');
        $this->assign(DB::table('buildings')->where('building_status', 1)->whereNotIn('id', DB::table('building_executives')->whereNull('assigned_till')->pluck('building_id')->all())->value('id'), $sup);
        $issue = (object) ['status' => Inspection::RESOLVED, 'executive_id' => $sup->id, 'raised_by' => $sup->id];
        $this->assertFalse(Inspection::canVerify($issue, $sup));
        $issue->executive_id = $issue->raised_by = $this->exec1->id;
        $this->assertTrue(Inspection::canVerify($issue, $sup));
        $this->assertTrue(Inspection::canVerify($issue, $this->manager));
        $this->assertFalse(Inspection::canVerify($issue, $this->exec2));
    }

    public function test_manual_issue_and_complaint_ticket()
    {
        Event::fake([NewComplaintEnquiry::class]);
        $work = DB::table('works')->where('works_code', 'Electrical')->value('id');
        $this->as($this->manager)->post(route('inspection.issues.store'), ['building_id' => $this->b2, 'work_id' => $work, 'priority' => 1,
            'description' => 'Corridor lights out', 'executive_id' => $this->exec2->id])->assertSessionHasErrors('photo');
        $this->as($this->manager)->post(route('inspection.issues.store'), ['building_id' => $this->b2, 'work_id' => $work, 'priority' => 1,
            'description' => 'Corridor lights out', 'executive_id' => $this->exec2->id, 'photo' => $this->photo()])->assertSessionHasNoErrors();
        $issue = DB::table('inspection_issues')->where('building_id', $this->b2)->orderBy('id', 'desc')->first();
        $this->assertEquals(2, $issue->source);
        $this->assertEquals($this->exec2->id, $issue->executive_id);
        $this->assertEquals(date('Y-m-d', strtotime('+1 day')), $issue->target_date);

        $before = DB::table('complaint_enquiries')->count();
        $this->as($this->exec2)->post(route('inspection.issues.complaint', $issue->id), ['complainer_name' => 'Exec Two', 'complaint_mob_no' => '91234567'])
            ->assertSessionHasNoErrors();
        $this->assertEquals($before + 1, DB::table('complaint_enquiries')->count());
        $issue = DB::table('inspection_issues')->where('id', $issue->id)->first();
        $c = DB::table('complaint_enquiries')->where('id', $issue->complaint_enquiry_id)->first();
        $this->assertEquals($this->b2, $c->building_id);
        $this->assertEquals(3, $c->complainer_category);
        $this->assertEquals(1, $c->priority_status);
        $t = DB::table('complaint_checklists')->where('complaint_enquiries_id', $c->id)->first();
        $this->assertEquals($work, $t->work_id);
        $this->assertEquals($c->complaint_no . '-SUB1', $t->complaint_ticket_no);
        $this->assertEquals(1, DB::table('complaint_processes')->where('complaint_enquiries_id', $c->id)->count());
        Event::assertDispatched(NewComplaintEnquiry::class);
        // only once
        $this->as($this->exec2)->post(route('inspection.issues.complaint', $issue->id), ['complainer_name' => 'x', 'complaint_mob_no' => '1']);
        $this->assertEquals($before + 1, DB::table('complaint_enquiries')->count());
    }

    public function test_pages_render_and_reports_count()
    {
        list(, $issue) = $this->inspect();
        foreach (['inspection.dashboard', 'inspection.visits', 'inspection.issues'] as $r) {
            $this->as($this->exec1)->get(route($r))->assertOk();
            $this->as($this->manager)->get(route($r))->assertOk();
        }
        $this->as($this->exec1)->get(route('inspection.reports'))->assertStatus(403);
        $this->as($this->exec1)->get(route('inspection.setup'))->assertStatus(403);
        $this->as($this->manager)->get(route('inspection.setup'))->assertOk()->assertSee('Building assignment');
        $this->as($this->manager)->get(route('inspection.setup', ['tab' => 'checklist']))->assertOk()->assertSee('Water Tanks');
        $this->as($this->manager)->get(route('inspection.reports', ['executive' => $this->exec1->id]))->assertOk()
            ->assertSee('Visit compliance by executive')->assertSee('100%');
        $this->as($this->manager)->get(route('inspection.reports.export'))->assertOk();

        $r = new \Modules\Maintenance\Services\InspectionReports($this->exec1->id);
        $this->as($this->manager);
        $c = $r->compliance(date('Y-m'), date('Y-m'));
        $this->assertEquals([1, 1], $c['rows'][$this->exec1->id][date('Y-m')]);
        $this->assertEquals(1, $r->aging()[2]['0-7 days']);
        $s = $r->summary(date('Y-m-01'), date('Y-m-t'));
        $this->assertEquals([1, 1, 0, null], $s[$this->exec1->id]);
    }

    public function test_setup_assignment_keeps_history()
    {
        $this->as($this->manager)->post(route('inspection.setup.assign'), ['building_ids' => [$this->b1], 'user_id' => $this->exec2->id])
            ->assertSessionHasNoErrors();
        $rows = DB::table('building_executives')->where('building_id', $this->b1)->orderBy('id')->get();
        $this->assertCount(2, $rows);
        $this->assertEquals(date('Y-m-d'), $rows[0]->assigned_till);
        $this->assertEquals($this->exec2->id, $rows[1]->user_id);
        $this->assertNull($rows[1]->assigned_till);
        $this->as($this->exec1)->post(route('inspection.setup.assign'), ['building_ids' => [$this->b1], 'user_id' => $this->exec1->id])->assertStatus(403);
    }
}
