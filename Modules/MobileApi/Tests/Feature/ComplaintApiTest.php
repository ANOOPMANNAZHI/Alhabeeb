<?php

namespace Modules\MobileApi\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Tests\TestCase;

class ComplaintApiTest extends TestCase
{
    private function ticketIn(int $buildingId): ComplaintChecklist
    {
        $enq = ComplaintEnquiry::create(['complaint_date' => now(), 'complaint_no' => 'T-' . uniqid(), 'complainer_name' => 'Api', 'complaint_mob_no' => '9', 'building_id' => $buildingId, 'priority_status' => 1, 'complaint_status' => 0, 'created_by' => 1]);
        return ComplaintChecklist::create(['work_id' => 1, 'complaint_enquiries_id' => $enq->id, 'checklist_desc' => 'AC not cooling', 'complaint_ticket_no' => 'TK-' . uniqid(), 'work_flow_processes_code' => 701, 'ticket_status' => 0, 'created_by' => 1]);
    }

    public function test_end_to_end_attend_and_close(): void
    {
        Mail::fake();
        Event::fake();
        $are = $this->actingAsAre();
        $ticket = $this->ticketIn(AreBuildings::forUser($are)->first());

        $this->getJson('/api/v1/complaints?tab=unassigned')->assertOk()->assertJsonFragment(['id' => $ticket->id]);
        $this->postJson("/api/v1/complaints/{$ticket->id}/claim")->assertOk()->assertJsonPath('data.stage', 703);
        $this->postJson("/api/v1/complaints/{$ticket->id}/claim")->assertStatus(409)->assertJson(['code' => 'invalid_state']);

        $this->postJson("/api/v1/complaints/{$ticket->id}/check-in", ['lat' => 23.5, 'lng' => 58.4])->assertOk()->assertJsonPath('data.status', 1);
        $this->postJson("/api/v1/complaints/{$ticket->id}/notes", ['text' => 'Gas refilled'])->assertCreated();
        $photo = $this->postJson("/api/v1/complaints/{$ticket->id}/photos", ['stage' => 'after', 'photo' => UploadedFile::fake()->image('a.jpg', 1200, 900)])
            ->assertCreated()->assertJsonStructure(['data' => ['id', 'url', 'thumb_url']])->assertJsonPath('data.stage', 'after');
        $this->postJson("/api/v1/complaints/{$ticket->id}/status", ['status' => 'attended'])->assertOk()->assertJsonPath('data.status', 2);
        $this->postJson("/api/v1/complaints/{$ticket->id}/status", ['status' => 'completed'])->assertOk()->assertJsonPath('data.status', 3);
        $this->postJson("/api/v1/complaints/{$ticket->id}/signature", ['skipped' => true])->assertOk()->assertJsonPath('data.stage', 705);

        $detail = $this->getJson("/api/v1/complaints/{$ticket->id}")->assertOk()->assertJsonPath('data.report.status', 3);
        $this->assertCount(1, $detail->json('data.report.photos'));
        $this->assertNotEmpty($detail->json('data.report.notes'));

        // the signed file URL works without a token
        $url = $photo->json('data.thumb_url');
        $this->flushHeaders();
        $this->get($url)->assertOk();
        $this->get(preg_replace('/signature=\w+/', 'signature=bad', $url))->assertStatus(403);

        $this->actingAsAre($are);
        $pdf = $this->getJson("/api/v1/complaints/{$ticket->id}/service-report.pdf")->assertOk()->assertJsonStructure(['url']);
        $this->assertTrue(Storage::disk('local')->exists("mobile/complaints/{$ticket->id}/service-report.pdf"));
        $this->getJson('/api/v1/complaints?tab=completed')->assertOk()->assertJsonFragment(['id' => $ticket->id]);

        $this->assertDatabaseHas('activity_log', ['log_name' => 'mobile', 'causer_id' => $are->id, 'description' => "POST api/v1/complaints/{$ticket->id}/claim"]);
        Storage::disk('local')->deleteDirectory("mobile/complaints/{$ticket->id}");
    }

    public function test_ticket_outside_my_buildings_is_forbidden(): void
    {
        $are = $this->actingAsAre();
        $other = \Modules\Masters\Entities\Building::whereNotIn('id', AreBuildings::forUser($are)->all())->value('id');
        $ticket = $this->ticketIn($other);
        $this->getJson("/api/v1/complaints/{$ticket->id}")->assertStatus(403)->assertJson(['code' => 'not_assigned']);
    }

    public function test_validation_shape(): void
    {
        $this->actingAsAre();
        $this->postJson('/api/v1/complaints/0/status', ['status' => 'attended'])->assertStatus(404)->assertJson(['code' => 'not_found']);
        $t = $this->ticketIn(AreBuildings::forUser($this->areUser())->first());
        $this->postJson("/api/v1/complaints/{$t->id}/claim");
        $this->postJson("/api/v1/complaints/{$t->id}/status", ['status' => 'bogus'])->assertStatus(422)->assertJson(['code' => 'validation']);
        $this->getJson('/api/v1/complaints')->assertStatus(422);
    }
}
