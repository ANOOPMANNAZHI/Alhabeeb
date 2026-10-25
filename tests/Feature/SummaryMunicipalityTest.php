<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Masters\Entities\Building;
use Modules\Sales\Entities\TenantContract;

class SummaryMunicipalityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(1));
    }

    /** Approved renewal that starts in the future ("renewed" on Summary). */
    private function renewed()
    {
        return TenantContract::where('tenant_contract_status', 0)->where('work_flow_processes_code', 108)
            ->where('tenant_renewal_termination_status', 5)->whereDate('tenant_contract_effective_date', '>', Carbon::today())
            ->whereHas('building')->orderBy('id', 'desc')->firstOrFail();
    }

    private function summaryHtml($c)
    {
        return $this->post(route('contractDetails'), ['building_id' => $c->building_id, 'unit_id' => $c->unit_id, 'tenant_id' => $c->tenant_id])
            ->assertOk()->getContent();
    }

    private function buttonFor($id)
    {
        return 'class="btn btn-tbl-violet btn-xs addMunicipality" data-id="' . $id . '"';
    }

    public function test_summary_shows_add_municipality_only_on_renewed_contracts()
    {
        $c = $this->renewed();
        $html = $this->summaryHtml($c);
        $this->assertStringContainsString($this->buttonFor($c->id), $html);

        // every button on the page belongs to a renewed contract
        preg_match_all('/addMunicipality" data-id="(\d+)"/', $html, $m);
        foreach ($m[1] as $id) {
            $row = TenantContract::find($id);
            $this->assertSame(0, (int) $row->tenant_contract_status);
            $this->assertSame(5, (int) $row->tenant_renewal_termination_status);
            $this->assertTrue($row->tenant_contract_effective_date->gt(Carbon::today()));
        }
    }

    public function test_no_button_once_the_renewal_has_started()
    {
        $c = $this->renewed();
        TenantContract::where('id', $c->id)->update(['tenant_contract_effective_date' => Carbon::today()->format('Y-m-d')]);
        $this->assertStringNotContainsString($this->buttonFor($c->id), $this->summaryHtml($c));
    }

    public function test_no_button_without_permission()
    {
        $user = User::all()->first(function ($u) { return !$u->can('tenant_municipality_details'); });
        if (!$user) {
            $this->markTestSkipped('No local user without tenant_municipality_details.');
        }
        $c = $this->renewed();
        $this->actingAs($user);
        $this->assertStringNotContainsString($this->buttonFor($c->id), $this->summaryHtml($c));
    }

    public function test_ajax_save_returns_json_and_saves()
    {
        $c = $this->renewed();
        Building::where('id', $c->building_id)->update(['building_is_taxable' => false]);
        $this->postJson(route('tenantContractAddMunicipalityStore'), [
            'tenant_contract_id'                 => $c->id,
            'tenant_contract_muncipality_agr_no' => 'MUN-SUM-1',
            'tenant_contract_registered_in'      => '1',
            'tenant_contract_registered_date'    => '2026-10-01',
        ])->assertOk()->assertExactJson(['message' => 'Tenant Contract Municipality Changed Successfully']);
        $this->assertSame('MUN-SUM-1', $c->fresh()->tenant_contract_muncipality_agr_no);
        $this->assertSame(1, (int) $c->fresh()->tenant_contract_is_reg_municipality);
    }

    public function test_ajax_save_returns_validation_errors_as_json()
    {
        $c = $this->renewed();
        Building::where('id', $c->building_id)->update(['building_is_taxable' => true]);
        $this->postJson(route('tenantContractAddMunicipalityStore'), ['tenant_contract_id' => $c->id])
            ->assertStatus(422)->assertJsonValidationErrors(['tenant_contract_muncipality_agr_no', 'tenant_contract_municipality_tax_amt']);
    }

    public function test_normal_save_still_redirects_to_the_contract_list()
    {
        $c = $this->renewed();
        Building::where('id', $c->building_id)->update(['building_is_taxable' => false]);
        $this->post(route('tenantContractAddMunicipalityStore'), [
            'tenant_contract_id'                 => $c->id,
            'tenant_contract_muncipality_agr_no' => 'MUN-SUM-2',
            'tenant_contract_registered_in'      => '1',
            'tenant_contract_registered_date'    => '2026-10-01',
        ])->assertRedirect(route('tenant-contract.index'));
    }
}
