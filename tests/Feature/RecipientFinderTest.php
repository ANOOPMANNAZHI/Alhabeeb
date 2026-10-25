<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\Bulk\RecipientFinder as F;
use Modules\BackOffice\Services\MessageValues;
use Modules\Sales\Entities\TenantContract;

class RecipientFinderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_active_tenants_are_one_row_per_tenant()
    {
        $expected = DB::table('tenant_contracts as c')->join('tenant as t', 't.id', '=', 'c.tenant_id')
            ->where('c.tenant_contract_status', 1)->where('t.tenant_status', 1)->distinct()->count('t.id');
        $people = F::people('tenant_active', []);
        $this->assertCount($expected, $people);
        foreach ($people as $key => $p) {
            $this->assertSame('tenant:' . $p['person_id'], $key);
            $this->assertSame('tenant', $p['person_type']);
        }
    }

    public function test_a_tenant_with_several_active_contracts_gets_the_newest_one()
    {
        $row = DB::table('tenant_contracts as c')->join('tenant as t', 't.id', '=', 'c.tenant_id')
            ->where('c.tenant_contract_status', 1)->where('t.tenant_status', 1)
            ->groupBy('c.tenant_id')->havingRaw('count(*) > 1')->select('c.tenant_id')->first();
        if (!$row) {
            $this->markTestSkipped('No tenant with two active contracts');
        }
        $newest = TenantContract::where('tenant_id', $row->tenant_id)->where('tenant_contract_status', 1)
            ->orderBy('tenant_contract_effective_date', 'desc')->orderBy('id', 'desc')->first();
        $p = F::people('tenant_active', [])['tenant:' . $row->tenant_id];
        $this->assertSame($newest->tenant_contract_no, $p['values']['contract_no']);
    }

    public function test_tenant_values_match_message_values_contract()
    {
        $c = TenantContract::with(['tenant', 'building', 'Unit'])->where('tenant_contract_status', 1)
            ->whereHas('tenant', function ($q) { $q->where('tenant_status', 1); })->latest('id')->first();
        $p = F::people('tenant_active', ['buildings' => [$c->building_id]])['tenant:' . $c->tenant_id];
        $mv = MessageValues::contract(TenantContract::where('tenant_id', $c->tenant_id)->where('building_id', $c->building_id)
            ->where('tenant_contract_status', 1)->orderBy('tenant_contract_effective_date', 'desc')->orderBy('id', 'desc')->first());
        foreach (['tenant_name', 'contract_no', 'building', 'unit', 'location', 'rent', 'contract_start', 'contract_end'] as $k) {
            $this->assertSame($mv[$k], $p['values'][$k], $k);
        }
    }

    public function test_building_filter_only_includes_tenants_of_those_buildings()
    {
        $buildingId = DB::table('tenant_contracts')->where('tenant_contract_status', 1)->value('building_id');
        $expected = DB::table('tenant_contracts as c')->join('tenant as t', 't.id', '=', 'c.tenant_id')
            ->where('c.tenant_contract_status', 1)->where('t.tenant_status', 1)->where('c.building_id', $buildingId)->distinct()->count('t.id');
        $this->assertCount($expected, F::people('tenant_active', ['buildings' => [$buildingId]]));
        $locationId = DB::table('buildings')->where('id', $buildingId)->value('location_id');
        $this->assertGreaterThanOrEqual($expected, count(F::people('tenant_active', ['locations' => [$locationId]])));
    }

    public function test_location_with_no_buildings_gives_nobody()
    {
        $this->assertSame([], F::buildingIds(['locations' => [999999]]));
        $this->assertSame([], F::people('tenant_active', ['locations' => [999999]]));
        $this->assertNull(F::buildingIds([]));
    }

    public function test_past_tenants_have_no_active_contract_and_ended_within_12_months()
    {
        $cutoff = date('Y-m-d', strtotime('-12 months'));
        foreach (array_slice(F::people('tenant_past', []), 0, 25, true) as $p) {
            $this->assertFalse(DB::table('tenant_contracts')->where('tenant_id', $p['person_id'])->where('tenant_contract_status', 1)->exists());
            $this->assertGreaterThanOrEqual($cutoff, substr(DB::table('tenant_contracts')->where('tenant_id', $p['person_id'])->max('tenant_contract_valid_to_date'), 0, 10));
        }
        $this->assertSame([], array_intersect_key(F::people('tenant_past', []), F::people('tenant_active', [])));
    }

    public function test_tenant_email_falls_back_to_personal_email()
    {
        $t = DB::table('tenant')->where('tenant_status', 1)->whereIn('id', DB::table('tenant_contracts')->where('tenant_contract_status', 1)->pluck('tenant_id'))->first();
        DB::table('tenant')->where('id', $t->id)->update(['tenant_contact_email' => '', 'tenant_personal_email' => 'personal@example.com']);
        $this->assertSame('personal@example.com', F::people('tenant_active', [])['tenant:' . $t->id]['email']);
    }

    public function test_landlords_are_active_vendors_of_type_2()
    {
        $expected = DB::table('vendors')->where('vendor_type_id', 2)->where('vendor_status', 1)->count();
        $people = F::people('landlord', []);
        $this->assertCount($expected, $people);
        $p = reset($people);
        $this->assertSame('landlord', $p['person_type']);
        $this->assertArrayHasKey('landlord_name', $p['values']);
        $this->assertArrayHasKey('building', $p['values']);
        $this->assertArrayHasKey('location', $p['values']);
    }

    public function test_landlord_building_filter_uses_approved_contracts()
    {
        $lc = DB::table('landlord_contract as lc')->join('vendors as v', 'v.id', '=', 'lc.vendor_id')
            ->where('lc.landlord_contract_status', 1)->where('v.vendor_type_id', 2)->where('v.vendor_status', 1)
            ->select('lc.vendor_id', 'lc.building_id')->first();
        if (!$lc) {
            $this->markTestSkipped('No approved landlord contract of an active landlord');
        }
        $people = F::people('landlord', ['buildings' => [$lc->building_id]]);
        $this->assertArrayHasKey('landlord:' . $lc->vendor_id, $people);
        foreach ($people as $p) {
            $this->assertTrue(DB::table('landlord_contract')->where('vendor_id', $p['person_id'])->where('building_id', $lc->building_id)->where('landlord_contract_status', 1)->exists());
        }
    }

    public function test_staff_by_role_one_row_per_user_and_active_only()
    {
        $user = User::active()->where('user_type', 'employee')->first();
        $roles = $user->getRoleNames()->all();
        if (count($roles) < 1) {
            $this->markTestSkipped('User without roles');
        }
        $people = F::people('staff', ['roles' => $roles]);
        $this->assertArrayHasKey('staff:' . $user->id, $people);
        $this->assertCount(count(array_unique(array_keys($people))), $people);
        DB::table('users')->where('id', $user->id)->update(['user_type_status' => false]);
        $this->assertArrayNotHasKey('staff:' . $user->id, F::people('staff', ['roles' => $roles]));
        $this->assertArrayHasKey('employee_name', $people['staff:' . $user->id]['values']);
    }

    public function test_rows_carry_the_building_of_the_contract_used()
    {
        $c = TenantContract::where('tenant_contract_status', 1)->whereNotNull('building_id')
            ->whereHas('tenant', function ($q) { $q->where('tenant_status', 1); })->latest('id')->first();
        $newest = TenantContract::where('tenant_id', $c->tenant_id)->where('tenant_contract_status', 1)
            ->orderBy('tenant_contract_effective_date', 'desc')->orderBy('id', 'desc')->first();
        $this->assertSame($newest->building_id === null ? null : (int) $newest->building_id, F::people('tenant_active', [])['tenant:' . $c->tenant_id]['building_id']);
        $this->assertSame((int) $c->building_id, F::people('tenant_active', ['buildings' => [$c->building_id]])['tenant:' . $c->tenant_id]['building_id']);
        $lc = DB::table('landlord_contract as lc')->join('vendors as v', 'v.id', '=', 'lc.vendor_id')
            ->where('lc.landlord_contract_status', 1)->where('v.vendor_type_id', 2)->where('v.vendor_status', 1)
            ->select('lc.vendor_id', 'lc.building_id')->first();
        if ($lc) {
            $this->assertSame((int) $lc->building_id, F::people('landlord', ['buildings' => [$lc->building_id]])['landlord:' . $lc->vendor_id]['building_id']);
        }
        $staff = F::people('staff', []);
        $this->assertNull(reset($staff)['building_id']);
    }

    public function test_count_search_person_and_unknown_audience()
    {
        $c = F::count('staff', []);
        $this->assertSame(count(F::people('staff', [])), $c['total']);
        $this->assertLessThanOrEqual($c['total'], $c['email']);
        $all = F::people('staff', []);
        $one = reset($all);
        $key = 'staff:' . $one['person_id'];
        $this->assertSame($one['name'], F::person('staff', $key)['name']);
        $this->assertNull(F::person('staff', 'staff:99999999'));
        $hits = F::search('staff', mb_substr($one['name'], 0, 3));
        $this->assertStringContainsString($key, array_column($hits, 'key'));
        $this->assertSame([], F::search('staff', '   '));
        $this->assertSame('tenant', F::templateAudience('tenant_past'));
        $this->expectException(\InvalidArgumentException::class);
        F::people('nobody', []);
    }
}
