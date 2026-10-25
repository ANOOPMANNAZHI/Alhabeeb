<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Masters\Entities\Building;

class BuildingTaxableTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(1));
    }

    private function building()
    {
        return Building::where('building_status', 1)->orderBy('id')->first();
    }

    public function test_new_column_defaults_to_taxable()
    {
        $b = $this->building();
        $this->assertTrue($b->building_is_taxable);
        $this->assertSame(0, Building::where('building_is_taxable', false)->count());
    }

    public function test_list_switch_saves_and_returns_json()
    {
        $b = $this->building();
        $this->post(route('building.changeTaxable', $b->id), ['taxable' => 0])
            ->assertOk()->assertExactJson(['ok' => true, 'taxable' => false]);
        $this->assertFalse($b->fresh()->building_is_taxable);

        $this->post(route('building.changeTaxable', $b->id), ['taxable' => 1])
            ->assertOk()->assertExactJson(['ok' => true, 'taxable' => true]);
        $this->assertTrue($b->fresh()->building_is_taxable);
    }

    public function test_list_switch_rejects_other_values()
    {
        $b = $this->building();
        $this->postJson(route('building.changeTaxable', $b->id), ['taxable' => 'x'])->assertStatus(422);
        $this->assertTrue($b->fresh()->building_is_taxable);
    }

    public function test_list_switch_needs_edit_permission()
    {
        $user = User::all()->first(function ($u) { return !$u->can('edit_building'); });
        if (!$user) {
            $this->markTestSkipped('No local user without edit_building.');
        }
        $b = $this->building();
        $this->actingAs($user)->post(route('building.changeTaxable', $b->id), ['taxable' => 0])->assertStatus(403);
        $this->assertTrue($b->fresh()->building_is_taxable);
    }

    public function test_list_shows_switch_column_and_filter()
    {
        $html = $this->get(route('building.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Taxable', $html);
        $this->assertStringContainsString('id="building_is_taxable"', $html);
        $this->assertStringContainsString('class="building-taxable"', $html);
    }

    public function test_list_filter_by_taxable()
    {
        $b = $this->building();
        $b->update(['building_is_taxable' => false]);

        $no = $this->post(route('buildingFilter'), ['building_is_taxable' => '0', 'building_status' => '1'])->assertOk()->getContent();
        $this->assertStringContainsString(e($b->building_name), $no);
        $this->assertSame(1, substr_count($no, 'class="building-taxable"'));

        $yes = $this->post(route('buildingFilter'), ['building_is_taxable' => '1', 'building_status' => '1'])->assertOk()->getContent();
        $this->assertStringNotContainsString('data-url="' . route('building.changeTaxable', $b->id) . '"', $yes);
    }

    public function test_edit_form_saves_unticked_and_ticked()
    {
        $b = Building::where('building_status', 1)->whereNotNull('building_address')->whereNotNull('building_pc')
            ->whereNotNull('google_location')->whereNotNull('building_note')->where('building_note', '<>', '')
            ->whereRaw('char_length(building_name) <= 30')->orderBy('id')->first();
        $form = [
            'building_name' => $b->building_name, 'vendor_id' => $b->vendor_id, 'building_no' => $b->building_no ?: '1',
            'building_type_id' => $b->building_type_id, 'management_id' => $b->management_id,
            'building_ownership_type' => $b->building_ownership_type, 'building_address' => substr($b->building_address, 0, 250),
            'building_pc' => $b->building_pc, 'location_id' => $b->location_id, 'google_location' => $b->google_location,
            'building_note' => $b->building_note, 'building_geo_long' => $b->building_geo_long, 'building_geo_lat' => $b->building_geo_lat,
            'building_no_floor' => $b->building_no_floor, 'backurl' => route('building.index'),
        ];

        // unticked: only the hidden 0 is sent
        $this->put(route('building.update', $b->id), $form + ['building_is_taxable' => '0'])->assertSessionHasNoErrors();
        $this->assertFalse($b->fresh()->building_is_taxable);

        $this->put(route('building.update', $b->id), $form + ['building_is_taxable' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($b->fresh()->building_is_taxable);
    }

    public function test_form_ignores_list_filter_flash_but_keeps_failed_save_input()
    {
        $tick = '/<input type="checkbox" id="building_is_taxable" name="building_is_taxable" value="1"\s+checked/';
        // list filter "Taxable: No" flashes building_is_taxable=0, then Add is clicked
        $html = $this->withSession(['_old_input' => ['building_is_taxable' => '0', 'building_ownership_type' => '']])
            ->get(route('building.create'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression($tick, $html);
        $this->assertMatchesRegularExpression('/<option selected value="4">Individual<\/option>/', $html);

        // failed save with the box unticked: keep what the user chose
        $html = $this->withSession(['_old_input' => ['building_is_taxable' => '0'], 'errors' => (new \Illuminate\Support\ViewErrorBag)->put('default', new \Illuminate\Support\MessageBag(['building_name' => 'required']))])
            ->get(route('building.create'))->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression($tick, $html);
    }

    public function test_create_form_ticks_taxable_and_edit_form_shows_saved_value()
    {
        $html = $this->get(route('building.create'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<input type="checkbox" id="building_is_taxable" name="building_is_taxable" value="1"\s+checked/', $html);

        $b = $this->building();
        $b->update(['building_is_taxable' => false]);
        $html = $this->get(route('building.edit', $b->id))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<input type="checkbox" id="building_is_taxable" name="building_is_taxable" value="1"\s+style/', $html);

        $this->assertStringContainsString('<b>Taxable  :  </b><span>No</span>', $this->get(route('building.show', $b->id))->getContent());
    }
}
