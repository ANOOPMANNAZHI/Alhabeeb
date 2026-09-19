<?php

namespace Modules\Maintenance\Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Maintenance\Services\TicketAttendance;
use Modules\Masters\Services\AreBuildings;
use Tests\TestCase;

class TicketAttendanceTest extends TestCase
{
    use DatabaseTransactions;

    private function are(): User { return User::role('are')->whereHas('areBuildings')->firstOrFail(); }

    /** A fresh unassigned ticket on a building the ARE covers. */
    private function unassignedTicket(User $are): ComplaintChecklist
    {
        $buildingId = AreBuildings::forUser($are)->first();
        $enq = ComplaintEnquiry::create([
            'complaint_date' => now(), 'complaint_no' => 'T-' . uniqid(), 'complainer_name' => 'Test',
            'complaint_mob_no' => '99999999', 'building_id' => $buildingId, 'priority_status' => 1,
            'complaint_status' => 0, 'created_by' => 1,
        ]);
        return ComplaintChecklist::create([
            'work_id' => 1, 'complaint_enquiries_id' => $enq->id, 'checklist_desc' => 'Tap leaking',
            'complaint_ticket_no' => 'TK-' . uniqid(), 'work_flow_processes_code' => 701, 'ticket_status' => 0, 'created_by' => 1,
        ]);
    }

    public function test_unassigned_list_is_scoped_and_claim_moves_to_703(): void
    {
        $are = $this->are();
        $ticket = $this->unassignedTicket($are);

        $this->assertTrue(TicketAttendance::unassignedTickets($are)->pluck('id')->contains($ticket->id));

        $claimed = TicketAttendance::claim($ticket, $are);
        $this->assertSame(703, (int) $claimed->work_flow_processes_code);
        $this->assertSame($are->id, (int) $claimed->sub_assigned_to);
        $this->assertDatabaseHas('complaint_processes', ['complaint_checklists_id' => $ticket->id, 'work_flow_processes_code' => 703, 'sub_assigned_to' => $are->id]);
        $this->assertTrue(TicketAttendance::myTickets($are, 'mine')->pluck('id')->contains($ticket->id));
        $this->assertFalse(TicketAttendance::unassignedTickets($are)->pluck('id')->contains($ticket->id));
    }

    public function test_claim_twice_is_a_conflict(): void
    {
        $are = $this->are();
        $ticket = TicketAttendance::claim($this->unassignedTicket($are), $are);
        $this->expectException(InvalidStateException::class);
        TicketAttendance::claim($ticket->fresh(), $are);
    }

    public function test_check_in_creates_one_service_report(): void
    {
        $are = $this->are();
        $ticket = TicketAttendance::claim($this->unassignedTicket($are), $are);
        $report = TicketAttendance::checkIn($ticket, $are, 23.58, 58.38);
        $again = TicketAttendance::checkIn($ticket->fresh(), $are, null, null);

        $this->assertSame($report->id, $again->id);
        $this->assertSame(1, (int) $report->complaint_assign_status);
        $this->assertSame(1, (int) $ticket->fresh()->ticket_status);
        $this->assertDatabaseHas('complaint_service_report_checklist', ['checklist_id' => $ticket->id, 'complaint_service_report_id' => $report->id]);
        $this->assertDatabaseHas('complaint_service_report_notes', ['complaint_service_report_id' => $report->id, 'stage' => 1]);
        $this->assertStringContainsString('23.58', \Modules\Maintenance\Entities\ComplaintServiceReportNote::where('complaint_service_report_id', $report->id)->value('desc'));
    }

    public function test_full_attendance_flow_closes_ticket_with_signature(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        \Illuminate\Support\Facades\Event::fake();
        $are = $this->are();
        $ticket = TicketAttendance::claim($this->unassignedTicket($are), $are);
        TicketAttendance::checkIn($ticket, $are, null, null);

        $note = TicketAttendance::addNote($ticket, $are, 'Replaced washer', null);
        $this->assertSame('Replaced washer', $note->desc);
        $quick = TicketAttendance::addNote($ticket, $are, 'need a valve', 'part_required');
        $this->assertSame('Part required: need a valve', $quick->desc);

        $photo = TicketAttendance::addPhoto($ticket, $are, 'mobile/complaints/x/a.jpg', 'mobile/complaints/x/a_t.jpg', 'after');
        $this->assertSame('after', TicketAttendance::photoStageName((int) $photo->stage));

        TicketAttendance::setStatus($ticket, $are, 'attended', null);
        $this->assertSame(2, (int) $ticket->fresh()->ticket_status);
        TicketAttendance::setStatus($ticket, $are, 'completed', null);
        $this->assertSame(3, (int) $ticket->fresh()->ticket_status);

        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        $report = TicketAttendance::close($ticket->fresh(), $are, $png, false);

        $this->assertSame(705, (int) $ticket->fresh()->work_flow_processes_code);
        $this->assertSame(4, (int) $ticket->fresh()->ticket_status);
        $this->assertSame(3, (int) $report->complaint_assign_status);
        $this->assertNotEmpty($report->tenant_signature);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('local')->exists($report->tenant_signature));
        \Illuminate\Support\Facades\Storage::disk('local')->delete($report->tenant_signature);
        $this->assertSame(1, (int) $ticket->complaintEnquiry->fresh()->complaint_status);
    }

    public function test_close_requires_completed_status(): void
    {
        $are = $this->are();
        $ticket = TicketAttendance::claim($this->unassignedTicket($are), $are);
        TicketAttendance::checkIn($ticket, $are, null, null);
        $this->expectException(InvalidStateException::class);
        TicketAttendance::close($ticket->fresh(), $are, null, true);
    }

    public function test_skipped_signature_needs_an_after_photo(): void
    {
        $are = $this->are();
        $ticket = TicketAttendance::claim($this->unassignedTicket($are), $are);
        TicketAttendance::checkIn($ticket, $are, null, null);
        TicketAttendance::setStatus($ticket, $are, 'completed', null);
        $this->expectException(InvalidStateException::class);
        TicketAttendance::close($ticket->fresh(), $are, null, true);
    }
}
