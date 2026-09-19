<?php

namespace Modules\BackOffice\Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Entities\TerminationChecklist;
use Modules\BackOffice\Services\TerminationInspection;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Masters\Services\AreBuildings;
use Tests\TestCase;

class TerminationInspectionTest extends TestCase
{
    use DatabaseTransactions;

    private function are(): User
    {
        return User::role('are')->whereHas('areBuildings')->firstOrFail();
    }

    private function pendingInMyBuildings(User $are): Termination
    {
        return TerminationInspection::queue($are, 'pending', null)->where('work_flow_processes_code', 502)->first()
            ?? $this->markTestSkipped('No pending handover in the ARE buildings on this DB copy');
    }

    private function payload(array $tpl): array
    {
        $sub = $tpl[0]['sub_works'][0];
        return [
            'lines' => [
                ['work_id' => $tpl[0]['work_id'], 'sub_work_id' => $sub['id'], 'quantity' => 2, 'amount' => 15.5],
                ['other_work' => 'Broken tile', 'quantity' => 1, 'amount' => 4.5],
            ],
            'electricity' => ['acc_no' => 'E1', 'close_reading' => '1200', 'amount' => 10],
            'water' => ['acc_no' => 'W1', 'close_reading' => '300', 'amount' => 2.5],
            'main_key_status' => 1, 'maintenance_due' => 3, 'notes' => 'ok', 'remark' => null,
        ];
    }

    public function test_draft_then_submit_moves_to_taken_over_with_desktop_totals(): void
    {
        Mail::fake();
        Event::fake();
        $are = $this->are();
        $t = $this->pendingInMyBuildings($are);
        $tpl = TerminationInspection::template();
        $this->assertNotEmpty($tpl[0]['sub_works']);

        $draft = TerminationInspection::saveDraft($t, $are, $this->payload($tpl));
        $this->assertSame(503, (int) $draft->work_flow_processes_code);
        $this->assertSame($are->id, (int) $draft->assigned_to);
        $this->assertEquals(20.0, (float) $draft->termination_total_amount);
        $this->assertEquals(12.5, (float) $draft->termination_total_elec_water_amount);
        $this->assertEquals(29.5, (float) $draft->termination_net_amount); // 20 + 12.5 - 3
        $this->assertSame('W1', $draft->termination_water_acc_no);
        $this->assertSame(2, TerminationChecklist::where('termination_id', $draft->id)->count());
        $this->assertTrue(TerminationInspection::queue($are, 'inspected', null)->pluck('id')->contains($draft->id));
        $this->assertFalse(TerminationInspection::queue($are, 'pending', null)->pluck('contract_id')->contains($t->contract_id));

        // re-saving updates the same row and replaces the lines
        $p = $this->payload($tpl);
        $p['lines'] = [$p['lines'][1]];
        $again = TerminationInspection::saveDraft($draft->fresh(), $are, $p);
        $this->assertSame($draft->id, $again->id);
        $this->assertEquals(4.5, (float) $again->termination_total_amount);
        $this->assertSame(1, TerminationChecklist::where('termination_id', $draft->id)->count());

        $detail = TerminationInspection::detail($again);
        $this->assertArrayHasKey('outstanding_rent', $detail);
        $this->assertCount(1, $detail['draft']['lines']);

        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        $done = TerminationInspection::submit($again, $are, $png, 'Tenant agrees');
        $this->assertSame(504, (int) $done->work_flow_processes_code);
        $this->assertNotEmpty(Termination::find($draft->id)->termination_tenant_signature);
        $this->assertSame(1, (int) Termination::find($draft->id)->termination_review_status);
        $this->assertNotNull($done->termination_takenover_date);
        $this->assertTrue(TerminationInspection::queue($are, 'taken_over', null)->pluck('id')->contains($done->id));
        $this->assertTrue(Storage::disk('local')->exists(TerminationInspection::pdfPath($done)));
        Storage::disk('local')->deleteDirectory("mobile/terminations/{$draft->id}");
        Storage::disk('local')->deleteDirectory("mobile/terminations/{$done->id}");
    }

    public function test_submit_requires_a_saved_draft(): void
    {
        Event::fake();
        $are = $this->are();
        $t = $this->pendingInMyBuildings($are);
        $this->expectException(InvalidStateException::class);
        TerminationInspection::submit($t, $are, 'data:image/png;base64,AAAA', null);
    }
}
