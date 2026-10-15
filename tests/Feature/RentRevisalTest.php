<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\TenantRentRevisal;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\BackOffice\Services\SystemTemplateDefaults;
use Modules\Sales\Entities\TenantContract;

/** Rent revisal (spec 2026-10-15): window, popup save + tenant email, ARE email job, renewal rent default. */
class RentRevisalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        SystemMessenger::$smsSender = function () { return true; };
        app('cache')->forget('spatie.permission.cache');
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
            'employee_contact_address' => 'x', 'employee_contact_no' => '91234567', 'employee_dob' => '1990-01-01', 'designation_id' => 37,
            'employee_status' => 1, 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $roleId = DB::table('roles')->where('name', $role)->value('id');
        $id = DB::table('users')->insertGetId(['username' => $username, 'email' => $username . '@example.test', 'password' => bcrypt('x'),
            'user_type' => 'employee', 'user_type_id' => $emp, 'default_role' => $roleId, 'user_type_status' => 1, 'created_by' => 1,
            'created_at' => now(), 'updated_at' => now()]);
        DB::table('model_has_roles')->insert(['role_id' => $roleId, 'model_type' => 'App\User', 'model_id' => $id]);
        return User::find($id);
    }

    /** Make $user the only ARE of the building (assign_to null = current). */
    private function assignAre(User $user, $buildingId)
    {
        DB::table('preferred_buildings')->where('building_id', $buildingId)->whereNull('assign_to')->update(['assign_to' => now()]);
        $areId = DB::table('are_buildings')->insertGetId(['user_id' => $user->id, 'building_id' => $buildingId, 'assign_from' => now(),
            'created_at' => now(), 'updated_at' => now()]);
        DB::table('preferred_buildings')->insert(['building_id' => $buildingId, 'are_building_id' => $areId, 'assign_from' => now(),
            'created_at' => now(), 'updated_at' => now()]);
    }

    private $used = [];

    /** An active contract with a tenant email, moved to end in $days days, normal renewal status. Each call picks a new contract. */
    private function contractEndingIn($days)
    {
        $id = DB::table('tenant_contracts as tc')->join('tenant as t', 't.id', '=', 'tc.tenant_id')
            ->where('tc.tenant_contract_status', 1)->whereNotNull('t.tenant_contact_email')->where('t.tenant_contact_email', '<>', '')
            ->whereNotIn('tc.id', $this->used ?: [0])
            ->orderBy('tc.id', 'desc')->value('tc.id');
        $this->used[] = $id;
        DB::table('tenant_contracts')->where('id', $id)->update([
            'tenant_contract_valid_to_date' => Carbon::today()->addDays($days)->toDateString(),
            'tenant_renewal_termination_status' => 0,
        ]);
        DB::table('tenant_rent_revisals')->where('tenant_contract_id', $id)->delete();
        DB::table('tenant_rent_revisal_notices')->where('tenant_contract_id', $id)->delete();
        return TenantContract::find($id);
    }

    public function test_tables_models_and_templates_exist()
    {
        $c = $this->contractEndingIn(100);
        $r = TenantRentRevisal::create(['tenant_contract_id' => $c->id, 'current_rent' => 300, 'revised_rent' => 330, 'created_by' => 1]);
        $this->assertEquals($r->id, $c->fresh()->rentRevisal->id);
        $this->assertNotNull(SystemTemplateDefaults::get('rent_revisal_due_are'));
        $this->assertNotNull(SystemTemplateDefaults::get('rent_revision_tenant'));
        $this->assertTrue(DB::table('message_templates')->where('key', 'rent_revision_tenant')->exists());
        $this->assertTrue(DB::table('permissions')->where('name', 'rent_revisal')->exists());
    }

    public function test_system_messenger_sets_reply_to_only_when_given()
    {
        $to = new \Modules\BackOffice\Services\MessageRecipient('Tenant', 'tenant@example.test');
        $values = ['tenant_name' => 'Tenant', 'contract_no' => 'TAG1', 'building' => 'B', 'unit' => '1', 'contract_end' => '01/01/2027',
            'revised_rent' => '330.000', 'current_rent' => '300.000', 'revisal_note' => '', 'are_name' => 'Rashid', 'are_mobile' => '9'];
        SystemMessenger::send('rent_revision_tenant', $to, $values, ['reply_to' => ['email' => 'are@example.test', 'name' => 'Rashid']]);
        SystemMessenger::send('rent_revision_tenant', $to, $values);
        Mail::assertSent(TemplatedMail::class, function ($m) { return $m->hasReplyTo('are@example.test'); });
        Mail::assertSent(TemplatedMail::class, function ($m) { return !$m->hasReplyTo('are@example.test'); });
    }

    public function test_window_includes_both_ends_and_excludes_outside()
    {
        $admin = User::find(1);
        foreach ([89 => false, 90 => true, 120 => true, 121 => false] as $days => $in) {
            $c = $this->contractEndingIn($days);
            $this->assertSame($in, \Modules\BackOffice\Services\RentRevisal::inWindow($c->fresh()), "day $days");
            $this->assertSame($in, \Modules\BackOffice\Services\RentRevisal::windowQuery($admin)->where('tenant_contracts.id', $c->id)->exists(), "query day $days");
        }
    }

    public function test_window_skips_termination_and_started_renewals()
    {
        $c = $this->contractEndingIn(100);
        foreach ([0 => true, 1 => true, 4 => true, 2 => false, 3 => false, 5 => false, 6 => false, 7 => false, 8 => false, 9 => false] as $status => $in) {
            DB::table('tenant_contracts')->where('id', $c->id)->update(['tenant_renewal_termination_status' => $status]);
            $this->assertSame($in, \Modules\BackOffice\Services\RentRevisal::inWindow($c->fresh()), "status $status");
        }
    }

    public function test_are_sees_only_own_buildings_and_due_count_skips_revised()
    {
        $are = $this->makeUser('rr_t_are', 'are');
        $other = $this->makeUser('rr_t_are2', 'are');
        $c = $this->contractEndingIn(100);
        $this->assignAre($are, $c->building_id);
        $svc = '\Modules\BackOffice\Services\RentRevisal';
        $this->assertTrue($svc::windowQuery($are)->where('tenant_contracts.id', $c->id)->exists());
        $this->assertFalse($svc::windowQuery($other)->where('tenant_contracts.id', $c->id)->exists());
        $this->assertTrue($svc::canRevise($c->fresh(), $are));
        $this->assertFalse($svc::canRevise($c->fresh(), $other));
        $this->assertEquals([$are->id], $svc::areUsers($c->fresh())->pluck('id')->all());
        $before = $svc::dueCount($are);
        TenantRentRevisal::create(['tenant_contract_id' => $c->id, 'current_rent' => 1, 'revised_rent' => 2, 'created_by' => $are->id]);
        $this->assertEquals($before - 1, $svc::dueCount($are));
        $this->assertFalse($svc::canRevise($c->fresh(), $are));
    }

    public function test_are_sends_revisal_and_tenant_gets_email_with_reply_to()
    {
        $are = $this->makeUser('rr_t_are', 'are');
        $c = $this->contractEndingIn(100);
        $this->assignAre($are, $c->building_id);
        $this->as($are)->get(route('rentRevisal.index'))->assertStatus(200)->assertSee($c->tenant_contract_no);
        $this->as($are)->get(route('rentRevisal.form', $c->id))->assertStatus(200);
        $res = $this->as($are)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '330.500', 'note' => 'Market rate']);
        $res->assertStatus(200)->assertJson(['ok' => true]);
        $r = TenantRentRevisal::where('tenant_contract_id', $c->id)->first();
        $this->assertEquals(330.5, (float) $r->revised_rent);
        $this->assertEquals((float) $c->tenant_contract_rent, (float) $r->current_rent);
        $this->assertEquals('sent', $r->email_status);
        Mail::assertSent(TemplatedMail::class, function ($m) use ($are) {
            return $m->hasReplyTo($are->email) && strpos($m->bodyHtml, '330.500') !== false && strpos($m->bodyHtml, 'Market rate') !== false;
        });
    }

    public function test_second_revisal_is_refused()
    {
        $are = $this->makeUser('rr_t_are', 'are');
        $c = $this->contractEndingIn(100);
        $this->assignAre($are, $c->building_id);
        $this->as($are)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '330']);
        $this->as($are)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '400'])->assertStatus(422);
        $this->assertEquals(1, TenantRentRevisal::where('tenant_contract_id', $c->id)->count());
        Mail::assertSent(TemplatedMail::class, 1);
    }

    public function test_refused_outside_window_and_for_other_are()
    {
        $are = $this->makeUser('rr_t_are', 'are');
        $other = $this->makeUser('rr_t_are2', 'are');
        $c = $this->contractEndingIn(80);
        $this->assignAre($are, $c->building_id);
        $this->as($are)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '330'])->assertStatus(422);
        $c2 = $this->contractEndingIn(100);
        $this->as($other)->post(route('rentRevisal.store', $c2->id), ['revised_rent' => '330'])->assertStatus(422);
        $this->assertEquals(0, TenantRentRevisal::whereIn('tenant_contract_id', [$c->id, $c2->id])->count());
    }

    public function test_rent_with_thousands_separator_saves_and_bad_rent_is_rejected()
    {
        $admin = User::find(1);
        $c = $this->contractEndingIn(100);
        $this->as($admin)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '0'])->assertStatus(422);
        $this->as($admin)->post(route('rentRevisal.store', $c->id), ['revised_rent' => 'abc'])->assertStatus(422);
        $this->as($admin)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '1,250.500'])->assertStatus(200);
        $this->assertEquals(1250.5, (float) TenantRentRevisal::where('tenant_contract_id', $c->id)->value('revised_rent'));
    }

    public function test_tenant_without_email_saves_with_status()
    {
        $admin = User::find(1);
        $c = $this->contractEndingIn(100);
        DB::table('tenant')->where('id', $c->tenant_id)->update(['tenant_contact_email' => null, 'tenant_personal_email' => null]);
        $res = $this->as($admin)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '330']);
        $res->assertStatus(200)->assertJsonFragment(['email_status' => 'skipped_no_address']);
        Mail::assertNothingSent();
    }

    public function test_user_without_permission_is_refused()
    {
        $user = $this->makeUser('rr_t_cc', 'call_center');
        $c = $this->contractEndingIn(100);
        $this->as($user)->get(route('rentRevisal.index'))->assertStatus(403);
        $this->as($user)->post(route('rentRevisal.store', $c->id), ['revised_rent' => '330'])->assertStatus(403);
    }
}
