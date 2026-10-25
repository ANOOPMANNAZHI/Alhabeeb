<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

/** ARE dashboard "Contracts Expiring" card: opens Due for Renewal filtered to the chosen days, and counts the same rows. */
class AreExpiringCardTest extends TestCase
{
    use DatabaseTransactions;

    private $used = [];

    protected function setUp(): void
    {
        parent::setUp();
        app('cache')->forget('spatie.permission.cache');
    }

    private function as($user)
    {
        $this->flushSession();
        return $this->actingAs($user);
    }

    private function makeAre($username)
    {
        $emp = DB::table('employees')->insertGetId(['employee_code' => strtoupper($username), 'employee_name' => ucfirst($username),
            'employee_contact_address' => 'x', 'employee_contact_no' => '91234567', 'employee_dob' => '1990-01-01', 'designation_id' => 37,
            'employee_status' => 1, 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $roleId = DB::table('roles')->where('name', 'are')->value('id');
        $id = DB::table('users')->insertGetId(['username' => $username, 'email' => $username . '@example.test', 'password' => bcrypt('x'),
            'user_type' => 'employee', 'user_type_id' => $emp, 'default_role' => $roleId, 'user_type_status' => 1, 'created_by' => 1,
            'created_at' => now(), 'updated_at' => now()]);
        DB::table('model_has_roles')->insert(['role_id' => $roleId, 'model_type' => 'App\User', 'model_id' => $id]);
        return User::find($id);
    }

    private function assignAre(User $user, $buildingId)
    {
        DB::table('preferred_buildings')->where('building_id', $buildingId)->whereNull('assign_to')->update(['assign_to' => now()]);
        $areId = DB::table('are_buildings')->insertGetId(['user_id' => $user->id, 'building_id' => $buildingId, 'assign_from' => now(),
            'created_at' => now(), 'updated_at' => now()]);
        DB::table('preferred_buildings')->insert(['building_id' => $buildingId, 'are_building_id' => $areId, 'assign_from' => now(),
            'created_at' => now(), 'updated_at' => now()]);
    }

    /** An active contract (normal renewal status) moved to end in $days days; a new contract each call. */
    private function contractEndingIn($days)
    {
        $c = DB::table('tenant_contracts')->where('tenant_contract_status', 1)->whereNotIn('id', $this->used ?: [0])
            ->orderBy('id', 'desc')->first();
        $this->used[] = $c->id;
        DB::table('tenant_contracts')->where('id', $c->id)->update([
            'tenant_contract_valid_to_date' => Carbon::today()->addDays($days)->toDateString(),
            'tenant_renewal_termination_status' => 0,
        ]);
        return DB::table('tenant_contracts')->where('id', $c->id)->first();
    }

    public function test_due_for_renewal_filters_by_expdays_only_when_given()
    {
        $admin = User::find(1);
        $soon = $this->contractEndingIn(20);
        $later = $this->contractEndingIn(50);
        $this->as($admin)->get(route('tenantRenewal.index', ['ajax' => 1, 'expdays' => 30, 'tenant_contract_no' => $soon->tenant_contract_no]))
            ->assertStatus(200)->assertSee($soon->tenant_contract_no);
        $this->as($admin)->get(route('tenantRenewal.index', ['ajax' => 1, 'expdays' => 30, 'tenant_contract_no' => $later->tenant_contract_no]))
            ->assertStatus(200)->assertDontSee($later->tenant_contract_no);
        $this->as($admin)->get(route('tenantRenewal.index', ['ajax' => 1, 'tenant_contract_no' => $later->tenant_contract_no]))
            ->assertStatus(200)->assertSee($later->tenant_contract_no);
    }

    public function test_card_count_matches_due_for_renewal_rows()
    {
        $are = $this->makeAre('exp_t_are');
        $c = $this->contractEndingIn(50);
        $this->assignAre($are, $c->building_id);
        $count = function () use ($are) {
            $res = $this->as($are)->get(route('areDueForRenewalCount', ['expdays' => 30]));
            $res->assertStatus(200);
            return json_decode($res->getContent(), true)[0]['count'];
        };
        $before = $count();   // the building may already have other contracts ending within 30 days
        DB::table('tenant_contracts')->where('id', $c->id)->update(['tenant_contract_valid_to_date' => Carbon::today()->addDays(20)->toDateString()]);
        $this->assertEquals($before + 1, $count());
        // renewal already started: not in Due for Renewal, so not counted either
        DB::table('tenant_contracts')->where('id', $c->id)->update(['tenant_renewal_termination_status' => 2]);
        $this->assertEquals($before, $count());
    }

    public function test_are_card_links_to_due_for_renewal_without_120_days()
    {
        $are = $this->makeAre('exp_t_are2');
        $html = $this->as($are)->get(route('dashboard'))->assertStatus(200)->getContent();
        $this->assertStringContainsString(route('areDueForRenewalCount'), $html);
        $this->assertStringContainsString('var href1 =   "' . route('tenantRenewal.index') . '"', $html);
        $this->assertStringNotContainsString('<option value="120">120 Days</option>', $html);
    }
}
