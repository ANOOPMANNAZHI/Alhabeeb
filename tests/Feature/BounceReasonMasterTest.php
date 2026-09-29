<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\BackOffice\Entities\Pdc;
use Modules\Masters\Entities\PdcBounceReason;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BounceReasonMasterTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        PdcBounceReason::flushCache();
        $this->actingAs(User::find(1));
    }

    protected function tearDown()
    {
        PdcBounceReason::flushCache();
        parent::tearDown();
    }

    public function test_list_shows_reasons_with_usage()
    {
        $html = $this->get(route('bounce-reason.index'))->assertOk()->getContent();
        $this->assertContains('Account Closed / Frozen / Dormant', $html);
        $this->assertContains('Misc', $html);
    }

    public function test_add_reason_gets_the_next_code_and_is_offered()
    {
        $this->post(route('bounce-reason.store'), ['name' => 'Post-dated cheque', 'bank_keywords' => 'Post Dated , PDC,', 'sort_order' => 12, 'is_active' => 1])
            ->assertRedirect(route('bounce-reason.index'));
        $r = PdcBounceReason::where('name', 'Post-dated cheque')->first();
        $this->assertGreaterThanOrEqual(11, $r->id);
        $this->assertSame('post dated, pdc', $r->bank_keywords);
        PdcBounceReason::flushCache();
        $this->assertArrayHasKey($r->id, PdcBounceReason::options());
    }

    public function test_duplicate_name_is_refused()
    {
        $this->post(route('bounce-reason.store'), ['name' => 'Stop Payment', 'is_active' => 1])->assertSessionHasErrors('name');
    }

    public function test_only_one_fallback_and_never_on_a_switched_off_reason()
    {
        $this->put(route('bounce-reason.update', 1), ['name' => 'Insufficient Funds', 'is_active' => 1, 'is_fallback' => 1, 'sort_order' => 1]);
        $this->put(route('bounce-reason.update', 5), ['name' => 'Stop Payment', 'is_active' => 1, 'is_fallback' => 1, 'sort_order' => 5]);
        $this->assertSame([5], PdcBounceReason::where('is_fallback', true)->pluck('id')->all());
        $this->put(route('bounce-reason.update', 5), ['name' => 'Stop Payment', 'is_fallback' => 1, 'sort_order' => 5]); // switched off
        $this->assertSame([], PdcBounceReason::where('is_fallback', true)->pluck('id')->all());
    }

    public function test_switched_off_reason_keeps_its_name_on_old_pdcs_and_shows_when_selected()
    {
        $pdc = Pdc::where('pdc_bounce_reason', 9)->first();
        if (!$pdc) {
            $this->markTestSkipped('No Misc PDC');
        }
        $this->assertSame('Misc', $pdc->pdc_cancel_reason_name);
        $this->assertArrayNotHasKey(9, PdcBounceReason::options());
        $this->assertArrayHasKey(9, PdcBounceReason::options(9));
    }

    public function test_renamed_reason_shows_everywhere()
    {
        $this->put(route('bounce-reason.update', 7), ['name' => 'Correction / Alteration', 'is_active' => 1, 'sort_order' => 7]);
        PdcBounceReason::flushCache();
        $pdc = new Pdc(['pdc_bounce_reason' => 7]);
        $this->assertSame('Correction / Alteration', $pdc->pdc_cancel_reason_name);
    }

    public function test_contract_pdc_screen_lists_reasons_from_the_master()
    {
        $pdc = Pdc::where('pdc_cancel_reason', 1)->where('pdc_bounce_reason', 9)->whereNotNull('tenant_contract_id')->latest('id')->first();
        if (!$pdc) {
            $this->markTestSkipped('No bounced Misc PDC');
        }
        $html = $this->get(url('pdc/' . $pdc->tenant_contract_id . '/edit'))->assertOk()->getContent();
        $this->assertContains('Account Closed / Frozen / Dormant', $html);
        $this->assertRegExp('#<option value="9"\s+selected\s*>Misc</option>#', $html);   // old PDC keeps Misc
        $this->assertContains('var PDC_BOUNCE_REASONS', $html);
        $this->assertNotContains('>Signature Missing</option><option value="3"', str_replace('<option value="1" >Signature Missing', '', $html)); // old mislabelled row list gone
    }

    public function test_needs_permission()
    {
        $user = User::where('id', '<>', 1)->firstOrFail();
        \DB::table('model_has_roles')->where('model_id', $user->id)->delete();
        \DB::table('model_has_permissions')->where('model_id', $user->id)->delete();
        $role = Role::create(['name' => 'br_view_only_tmp', 'guard_name' => 'web']);
        $role->givePermissionTo('view_bounce_reason');
        $user->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs(User::find($user->id));
        $this->get(route('bounce-reason.index'))->assertOk();
        $this->get(route('bounce-reason.create'))->assertStatus(403);
        $this->put(route('bounce-reason.update', 1), ['name' => 'x', 'is_active' => 1])->assertStatus(403);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
