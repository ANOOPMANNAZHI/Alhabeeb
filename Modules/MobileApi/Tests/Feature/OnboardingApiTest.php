<?php

namespace Modules\MobileApi\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Entities\OnboardingChecklistItem;
use Modules\BackOffice\Entities\TenantOnboarding as TenantOnboardingModel;
use Modules\BackOffice\Services\TenantOnboarding;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Tests\TestCase;
use Modules\Sales\Entities\TenantContract;

class OnboardingApiTest extends TestCase
{
    private function onboardingFor($are): TenantOnboardingModel
    {
        $contract = TenantContract::where('work_flow_processes_code', 108)->where('tenant_contract_status', 1)
            ->whereIn('building_id', AreBuildings::forUser($are)->all())->latest('id')->firstOrFail();
        TenantOnboardingModel::where('tenant_contract_id', $contract->id)->delete();
        return TenantOnboarding::createForContract($contract);
    }

    public function test_end_to_end_onboarding(): void
    {
        Notification::fake();
        $are = $this->actingAsAre();
        $o = $this->onboardingFor($are);

        $this->getJson('/api/v1/onboardings?tab=open')->assertOk()->assertJsonFragment(['id' => $o->id]);
        $detail = $this->getJson("/api/v1/onboardings/{$o->id}")->assertOk()
            ->assertJsonStructure(['data' => ['items', 'progress' => ['done', 'total'], 'tenant', 'unit', 'status']]);
        $items = collect($detail->json('data.items'));
        $required = $items->where('required', true);

        $this->postJson("/api/v1/onboardings/{$o->id}/complete", ['signature' => 'data:image/png;base64,AAAA'])
            ->assertStatus(409)->assertJson(['code' => 'invalid_state']);

        $entries = $required->map(fn ($i) => ['item_id' => $i['item_id'], 'done' => true, 'value' => 'ok'])->values()->all();
        $saved = $this->putJson("/api/v1/onboardings/{$o->id}/entries", ['entries' => $entries])->assertOk();
        $this->assertSame($required->count(), $saved->json('data.progress.done'));

        $this->postJson("/api/v1/onboardings/{$o->id}/photos", ['item_id' => $items->first()['item_id'], 'photo' => UploadedFile::fake()->image('k.jpg', 800, 600)])
            ->assertCreated()->assertJsonStructure(['data' => ['id', 'url', 'thumb_url']]);

        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        $this->postJson("/api/v1/onboardings/{$o->id}/complete", ['signature' => $png, 'tenant_remark' => 'thanks'])
            ->assertOk()->assertJsonPath('data.status', 'completed')->assertJsonStructure(['data' => ['pdf_url', 'signature_url']]);
        $this->postJson("/api/v1/onboardings/{$o->id}/complete", ['signature' => $png])->assertStatus(409);
        $this->getJson("/api/v1/onboardings/{$o->id}/onboarding.pdf")->assertOk()->assertJsonStructure(['url']);
        $this->getJson('/api/v1/onboardings?tab=completed')->assertOk()->assertJsonFragment(['id' => $o->id]);

        Storage::disk('local')->deleteDirectory("mobile/onboarding/{$o->id}");
    }

    public function test_validation_and_scope(): void
    {
        $are = $this->actingAsAre();
        $o = $this->onboardingFor($are);
        $this->putJson("/api/v1/onboardings/{$o->id}/entries", ['entries' => [['item_id' => 999999, 'done' => true]]])
            ->assertStatus(422)->assertJson(['code' => 'validation']);
        $this->getJson('/api/v1/onboardings?tab=bogus')->assertStatus(422);

        $other = TenantContract::where('work_flow_processes_code', 108)->where('tenant_contract_status', 1)
            ->whereNotIn('building_id', AreBuildings::forUser($are)->all())->latest('id')->firstOrFail();
        TenantOnboardingModel::where('tenant_contract_id', $other->id)->delete();
        $foreign = TenantOnboardingModel::create(['tenant_contract_id' => $other->id, 'building_id' => $other->building_id, 'unit_id' => $other->unit_id, 'status' => 'open']);
        $this->getJson("/api/v1/onboardings/{$foreign->id}")->assertStatus(403)->assertJson(['code' => 'not_assigned']);
    }
}
