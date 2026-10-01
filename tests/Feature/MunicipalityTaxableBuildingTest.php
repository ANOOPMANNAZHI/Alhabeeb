<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\BackOffice\Entities\GeneralLedger;
use Modules\Masters\Entities\Building;
use Modules\Sales\Entities\TenantContract;

class MunicipalityTaxableBuildingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        $this->actingAs(User::find(1));
    }

    /** A contract not yet registered with the municipality, with no vouchers, on a real building. */
    private function contract($taxable)
    {
        $c = TenantContract::where(function ($q) { $q->whereNull('tenant_contract_is_reg_municipality')->orWhere('tenant_contract_is_reg_municipality', '<>', 1); })
            ->whereNull('tenant_contract_municipality_gl_id')->whereNull('tenant_contract_municipality_penalty_gl_id')
            ->where('tenant_contract_value', '>', 0)->whereHas('building')->orderBy('id', 'desc')->firstOrFail();
        Building::where('id', $c->building_id)->update(['building_is_taxable' => $taxable]);
        return $c;
    }

    private function save($c, array $over = [])
    {
        return $this->post(route('tenantContractAddMunicipalityStore'), $over + [
            'tenant_contract_id'                 => $c->id,
            'tenant_contract_muncipality_agr_no' => 'MUN-TEST-1',
            'tenant_contract_registered_in'      => '1',
            'tenant_contract_registered_date'    => '2026-10-01',
        ]);
    }

    public function test_modal_hides_charges_for_non_taxable_building()
    {
        $c = $this->contract(false);
        $html = $this->post(route('tenantContractAddMunicipality'), ['tenant_contract_id' => $c->id])->assertOk()->getContent();
        $this->assertContains('This building is not taxable', $html);
        foreach (['Municipality Tax Amount', 'Processing Charge', 'Total (Tax + Processing)', 'Penalty Amount'] as $label) {
            $this->assertNotContains($label, $html);
        }
    }

    public function test_modal_shows_charges_for_taxable_building()
    {
        $c = $this->contract(true);
        $html = $this->post(route('tenantContractAddMunicipality'), ['tenant_contract_id' => $c->id])->assertOk()->getContent();
        $this->assertNotContains('This building is not taxable', $html);
        foreach (['Municipality Tax Amount', 'Processing Charge', 'Total (Tax + Processing)', 'Penalty Amount'] as $label) {
            $this->assertContains($label, $html);
        }
    }

    public function test_non_taxable_save_registers_without_charges_or_vouchers()
    {
        $c = $this->contract(false);
        $glBefore = GeneralLedger::withTrashed()->count();

        // even if amounts are posted, nothing is charged
        $this->save($c, ['tenant_contract_municipality_tax_amt' => '150', 'tenant_contract_municipality_penalty_amt' => '20'])
            ->assertSessionHasNoErrors()->assertRedirect(route('tenant-contract.index'));

        $c = $c->fresh();
        $this->assertSame(1, (int) $c->tenant_contract_is_reg_municipality);
        $this->assertSame('MUN-TEST-1', $c->tenant_contract_muncipality_agr_no);
        $this->assertNull($c->tenant_contract_municipality_tax_amt);
        $this->assertNull($c->tenant_contract_municipality_processing_amt);
        $this->assertNull($c->tenant_contract_municipality_gl_id);
        $this->assertNull($c->tenant_contract_municipality_penalty_gl_id);
        $this->assertSame($glBefore, GeneralLedger::withTrashed()->count());
    }

    public function test_non_taxable_save_does_not_need_a_tax_amount()
    {
        $c = $this->contract(false);
        $this->save($c)->assertSessionHasNoErrors();
        $this->assertSame(1, (int) $c->fresh()->tenant_contract_is_reg_municipality);
    }

    public function test_vouchers_made_while_taxable_are_left_alone_after_building_turns_non_taxable()
    {
        $c = $this->contract(true);
        $this->save($c, ['tenant_contract_municipality_tax_amt' => '150', 'tenant_contract_municipality_penalty_amt' => '20'])->assertSessionHasNoErrors();
        $before = $c->fresh();
        $taxGl = GeneralLedger::find($before->tenant_contract_municipality_gl_id);

        Building::where('id', $c->building_id)->update(['building_is_taxable' => false]);

        $html = $this->post(route('tenantContractAddMunicipality'), ['tenant_contract_id' => $c->id])->getContent();
        $this->assertContains('This building is not taxable', $html);
        $this->assertContains('Tax voucher made before:</strong> ' . $taxGl->voucher_no, $html);

        $this->save($c, ['tenant_contract_muncipality_agr_no' => 'MUN-TEST-2', 'tenant_contract_municipality_tax_amt' => '999'])->assertSessionHasNoErrors();
        $after = $c->fresh();
        $this->assertSame('MUN-TEST-2', $after->tenant_contract_muncipality_agr_no);
        $this->assertEquals(150, (float) $after->tenant_contract_municipality_tax_amt);
        $this->assertSame($before->tenant_contract_municipality_gl_id, $after->tenant_contract_municipality_gl_id);
        $this->assertSame($before->tenant_contract_municipality_penalty_gl_id, $after->tenant_contract_municipality_penalty_gl_id);
        $this->assertEquals((float) $taxGl->amount, (float) GeneralLedger::find($taxGl->id)->amount);
    }

    public function test_taxable_save_still_needs_tax_and_creates_vouchers()
    {
        $c = $this->contract(true);
        $this->save($c)->assertSessionHasErrors('tenant_contract_municipality_tax_amt');

        $this->save($c, ['tenant_contract_municipality_tax_amt' => '150', 'tenant_contract_municipality_penalty_amt' => '20'])
            ->assertSessionHasNoErrors();
        $c = $c->fresh();
        $this->assertEquals(150, (float) $c->tenant_contract_municipality_tax_amt);
        $this->assertNotNull($c->tenant_contract_municipality_gl_id);
        $this->assertNotNull($c->tenant_contract_municipality_penalty_gl_id);
    }
}
