<?php

namespace Modules\Maintenance\Services;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Entities\ComplaintProcess;
use Modules\Maintenance\Entities\ComplaintServiceReport;
use Modules\Maintenance\Entities\ComplaintServiceReportChecklist;
use Modules\Maintenance\Entities\ComplaintServiceReportNote;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Maintenance\Exceptions\NotAssignedException;
use Modules\Masters\Services\AreBuildings;

/**
 * A ticket (complaint_checklists row) being worked by one person.
 * Mirrors the desktop's sub-assign modal, Check In button and technician
 * service-report screen so both write identical rows.
 */
class TicketAttendance
{
    public const STAGE_UNASSIGNED = 701;
    public const STAGE_ASSIGNED = 702;
    public const STAGE_SUB_ASSIGNED = 703;
    public const STAGE_CLOSED = 705;

    public const STATUS_CHECKED_IN = 1;
    public const STATUS_ATTENDED = 2;
    public const STATUS_COMPLETED = 3;
    public const STATUS_CLOSED = 4;

    private static function base(User $are, ?int $buildingId, ?string $q): Builder
    {
        $buildings = AreBuildings::forUser($are);
        if ($buildingId !== null) {
            $buildings = $buildings->intersect([$buildingId]);
        }
        return ComplaintChecklist::query()
            ->with(['complaintEnquiry.building', 'complaintEnquiry.unit', 'complaintEnquiry.tenant', 'work'])
            ->whereHas('complaintEnquiry', function ($e) use ($buildings, $q) {
                $e->whereIn('building_id', $buildings->all());
                if ($q) {
                    $e->where(fn ($w) => $w->where('complaint_no', 'ilike', "%$q%")
                        ->orWhere('complainer_name', 'ilike', "%$q%")
                        ->orWhereHas('building', fn ($b) => $b->where('building_name', 'ilike', "%$q%")));
                }
            })
            ->orderByDesc('id');
    }

    public static function unassignedTickets(User $are, ?int $buildingId = null, ?string $q = null): Builder
    {
        return self::base($are, $buildingId, $q)
            ->whereIn('work_flow_processes_code', [self::STAGE_UNASSIGNED, self::STAGE_ASSIGNED])
            ->whereNull('sub_assigned_to');
    }

    public static function myTickets(User $are, string $tab, ?int $buildingId = null, ?string $q = null): Builder
    {
        $query = self::base($are, $buildingId, $q)->where('sub_assigned_to', $are->id);
        return match ($tab) {
            'mine' => $query->where('work_flow_processes_code', self::STAGE_SUB_ASSIGNED)->whereIn('ticket_status', [0, 1]),
            'attended' => $query->where('work_flow_processes_code', self::STAGE_SUB_ASSIGNED)->where('ticket_status', self::STATUS_ATTENDED),
            'completed' => $query->where(fn ($w) => $w->where('ticket_status', self::STATUS_COMPLETED)->orWhere('work_flow_processes_code', self::STAGE_CLOSED)),
            default => throw new \InvalidArgumentException("Unknown tab $tab"),
        };
    }

    public static function assertInScope(ComplaintChecklist $ticket, User $are): void
    {
        if (!AreBuildings::covers($are, (int) $ticket->complaintEnquiry->building_id)) {
            throw new NotAssignedException('This complaint is outside your assigned buildings.');
        }
    }

    public static function assertMine(ComplaintChecklist $ticket, User $are): void
    {
        if ((int) $ticket->sub_assigned_to !== $are->id) {
            throw new NotAssignedException('This ticket is not assigned to you.');
        }
    }

    /** Sub-assign the ticket to the ARE — the rows the coordinator's modal writes. */
    public static function claim(ComplaintChecklist $ticket, User $are): ComplaintChecklist
    {
        self::assertInScope($ticket, $are);
        if ($ticket->sub_assigned_to !== null || (int) $ticket->work_flow_processes_code >= self::STAGE_SUB_ASSIGNED) {
            throw new InvalidStateException('This ticket has already been assigned.');
        }

        return DB::transaction(function () use ($ticket, $are) {
            $previous = ComplaintProcess::where('complaint_checklists_id', $ticket->id)->orderByDesc('id')->first();
            if ($previous) {
                $previous->complaintUser()->update(['status' => 0]);
            }

            $process = new ComplaintProcess();
            $process->complaint_checklists_id = $ticket->id;
            $process->complaint_enquiries_id = $ticket->complaint_enquiries_id;
            $process->work_flow_processes_code = self::STAGE_SUB_ASSIGNED;
            $process->assigned_to = $ticket->assigned_to ?: $are->id;
            $process->sub_assigned_to = $are->id;
            $process->created_by = $are->id;
            $process->save();
            $process->complaintUsers()->attach($are->default_role, ['user_id' => $are->id]);

            $ticket->update([
                'work_flow_processes_code' => self::STAGE_SUB_ASSIGNED,
                'assigned_to' => $ticket->assigned_to ?: $are->id,
                'sub_assigned_to' => $are->id,
                'updated_by' => $are->id,
            ]);
            return $ticket->fresh();
        });
    }

    public static function reportFor(ComplaintChecklist $ticket): ?ComplaintServiceReport
    {
        $link = ComplaintServiceReportChecklist::where('checklist_id', $ticket->id)->orderByDesc('id')->first();
        return $link ? ComplaintServiceReport::find($link->complaint_service_report_id) : null;
    }

    /** Desktop "Check In": creates the service report once; records time/location as a note. */
    public static function checkIn(ComplaintChecklist $ticket, User $are, ?float $lat, ?float $lng): ComplaintServiceReport
    {
        self::assertMine($ticket, $are);
        if ($report = self::reportFor($ticket)) {
            return $report;
        }

        return DB::transaction(function () use ($ticket, $are, $lat, $lng) {
            $prefix = prefixData('service_report_prefix')->configuration_value;
            $last = ComplaintServiceReport::latest('id')->first();
            $no = $prefix . str_pad(($last->id ?? 0) + 1, 4, '0', STR_PAD_LEFT);

            $report = ComplaintServiceReport::create([
                'user_id' => $are->id, 'service_report_no' => $no,
                'complaint_assign_note' => $ticket->checklist_desc,
                'complaint_assign_status' => self::STATUS_CHECKED_IN, 'created_by' => $are->id,
            ]);
            ComplaintServiceReportChecklist::create(['checklist_id' => $ticket->id, 'complaint_service_report_id' => $report->id]);
            $ticket->update(['service_report_status' => 1, 'ticket_status' => self::STATUS_CHECKED_IN]);

            // notes.stage is an integer holding the report status at the time (desktop convention)
            ComplaintServiceReportNote::create([
                'complaint_service_report_id' => $report->id, 'stage' => self::STATUS_CHECKED_IN,
                'desc' => 'Checked in at ' . now()->format('d/m/Y H:i') . ($lat !== null ? " ($lat, $lng)" : ''),
                'created_by' => $are->id,
            ]);
            return $report;
        });
    }
}
