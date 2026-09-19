<?php

namespace Modules\MobileApi\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Services\TerminationInspection;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Tests\TestCase;

class TerminationApiTest extends TestCase
{
    private function pending($are): Termination
    {
        return TerminationInspection::queue($are, 'pending', null)->where('work_flow_processes_code', 502)->first()
            ?? $this->markTestSkipped('No pending handover in the ARE buildings on this DB copy');
    }

    public function test_end_to_end_inspection(): void
    {
        Mail::fake();
        Event::fake();
        $are = $this->actingAsAre();
        $t = $this->pending($are);

        $this->getJson('/api/v1/terminations?tab=pending')->assertOk()->assertJsonFragment(['id' => $t->id]);
        $detail = $this->getJson("/api/v1/terminations/{$t->id}")->assertOk()
            ->assertJsonStructure(['data' => ['template', 'outstanding_rent', 'tenant', 'unit', 'draft']]);
        $tpl = $detail->json('data.template');
        $sub = $tpl[0]['sub_works'][0];

        $payload = [
            'lines' => [
                ['work_id' => $tpl[0]['work_id'], 'sub_work_id' => $sub['id'], 'quantity' => 2, 'amount' => 15.5],
                ['other_work' => 'Broken tile', 'quantity' => 1, 'amount' => 4.5],
            ],
            'electricity' => ['acc_no' => 'E1', 'close_reading' => '1200', 'amount' => 10],
            'water' => ['acc_no' => 'W1', 'close_reading' => '300', 'amount' => 2.5],
            'main_key_status' => 1, 'maintenance_due' => 3, 'notes' => 'ok',
        ];
        $saved = $this->putJson("/api/v1/terminations/{$t->id}/inspection", $payload)->assertOk()
            ->assertJsonPath('data.stage', 503);
        $this->assertEquals(['total' => 20, 'elec_water_total' => 12.5, 'maintenance_due' => 3, 'net' => 29.5], $saved->json('data.draft.totals'));
        $draftId = $saved->json('data.draft.id');

        // the saved draft is now the row the app works with
        $this->getJson('/api/v1/terminations?tab=inspected')->assertOk()->assertJsonFragment(['id' => $draftId]);
        $this->postJson("/api/v1/terminations/{$draftId}/photos", ['type' => 'meter_electricity', 'photo' => UploadedFile::fake()->image('m.jpg', 800, 600)])
            ->assertCreated()->assertJsonPath('data.type', 'meter_electricity')->assertJsonStructure(['data' => ['url', 'thumb_url']]);

        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        $done = $this->postJson("/api/v1/terminations/{$draftId}/submit", ['signature' => $png, 'tenant_remark' => 'fine'])
            ->assertOk()->assertJsonPath('data.stage', 504);
        $doneId = $done->json('data.id');
        $this->getJson("/api/v1/terminations/{$doneId}/inspection.pdf")->assertOk()->assertJsonStructure(['url']);
        $this->getJson('/api/v1/terminations?tab=taken_over')->assertOk()->assertJsonFragment(['id' => $doneId]);
        $this->getJson("/api/v1/terminations/{$doneId}/refer-back")->assertOk()->assertJson(['data' => null]);

        // 409 once the contract has moved on
        $this->postJson("/api/v1/terminations/{$draftId}/submit", ['signature' => $png])->assertStatus(409);

        foreach ([$draftId, $doneId] as $id) {
            Storage::disk('local')->deleteDirectory("mobile/terminations/{$id}");
        }
    }

    public function test_outside_scope_is_forbidden_and_validation_shape(): void
    {
        $are = $this->actingAsAre();
        $other = Termination::where('termination_type', 1)->where('work_flow_processes_code', 502)
            ->whereHas('tenantContract', fn ($c) => $c->whereNotIn('building_id', AreBuildings::forUser($are)->all()))->latest('id')->first();
        $this->getJson("/api/v1/terminations/{$other->id}")->assertStatus(403)->assertJson(['code' => 'not_assigned']);

        $t = $this->pending($are);
        $this->putJson("/api/v1/terminations/{$t->id}/inspection", ['lines' => [['quantity' => 1, 'amount' => 1]], 'main_key_status' => 1, 'notes' => 'x'])
            ->assertStatus(422)->assertJsonValidationErrors(['lines.0']);
        $this->getJson('/api/v1/terminations?tab=nope')->assertStatus(422);
    }
}
