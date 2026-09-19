<?php

namespace Modules\Maintenance\Services;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Maintenance\Emails\ComplaintStageEmail;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Maintenance\Entities\ComplaintProcess;
use Modules\Maintenance\Entities\ComplaintServiceReport;
use Modules\Maintenance\Entities\ComplaintServiceReportChecklist;
use Modules\Maintenance\Entities\ComplaintServiceReportImage;
use Modules\Maintenance\Entities\ComplaintServiceReportInv;
use Modules\Maintenance\Entities\ComplaintServiceReportNote;
use Modules\Maintenance\Events\ComplaintAssigned;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Maintenance\Exceptions\NotAssignedException;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Services\MobileImage;

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

    // ---- service report content ------------------------------------------------

    public const QUICK_REASONS = [
        'part_required' => 'Part required',
        'tenant_not_available' => 'Tenant not available',
        'access_denied' => 'Access denied',
        'not_our_scope' => 'Not our scope',
    ];

    /**
     * complaint_service_report_image.stage is an integer (the desktop stores the
     * report status at upload time). The app talks in before/after.
     */
    public const PHOTO_BEFORE = 1;
    public const PHOTO_AFTER = 2;

    public static function photoStage(string $name): int
    {
        return $name === 'after' ? self::PHOTO_AFTER : self::PHOTO_BEFORE;
    }

    public static function photoStageName(int $stage): string
    {
        return $stage >= self::PHOTO_AFTER ? 'after' : 'before';
    }

    public static function addNote(ComplaintChecklist $ticket, User $are, string $text, ?string $quickReason): ComplaintServiceReportNote
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket) ?? self::checkIn($ticket, $are, null, null);
        $prefix = $quickReason ? (self::QUICK_REASONS[$quickReason] ?? $quickReason) . ': ' : '';
        return ComplaintServiceReportNote::create([
            'complaint_service_report_id' => $report->id, 'stage' => $report->complaint_assign_status,
            'desc' => $prefix . $text, 'created_by' => $are->id,
        ]);
    }

    public static function addPhoto(ComplaintChecklist $ticket, User $are, string $path, string $thumb, string $stage): ComplaintServiceReportImage
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket) ?? self::checkIn($ticket, $are, null, null);
        return ComplaintServiceReportImage::create([
            'complaint_service_report_id' => $report->id, 'image_path_file_name' => $path,
            'image_path_thumbnail' => $thumb, 'stage' => self::photoStage($stage), 'created_by' => $are->id,
        ]);
    }

    public static function addItem(ComplaintChecklist $ticket, User $are, int $inventoryId, float $qty): ComplaintServiceReportInv
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket) ?? self::checkIn($ticket, $are, null, null);
        return ComplaintServiceReportInv::create([
            'complaint_service_report_id' => $report->id, 'inventory_id' => $inventoryId, 'quantity' => $qty, 'checklist_id' => $ticket->id,
        ]);
    }

    public static function removeItem(ComplaintChecklist $ticket, User $are, int $itemId): void
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket);
        ComplaintServiceReportInv::where('id', $itemId)->where('complaint_service_report_id', $report?->id)->delete();
    }

    /** attended → 2, completed → 3, cannot_complete → stays as is with a note. */
    public static function setStatus(ComplaintChecklist $ticket, User $are, string $status, ?string $reason): ComplaintServiceReport
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket) ?? self::checkIn($ticket, $are, null, null);

        if ($status === 'cannot_complete') {
            self::addNote($ticket, $are, 'Cannot complete: ' . ($reason ?: 'no reason given'), null);
            return $report;
        }
        $code = match ($status) {
            'attended' => self::STATUS_ATTENDED,
            'completed' => self::STATUS_COMPLETED,
            default => throw new \InvalidArgumentException("Unknown status $status"),
        };

        $report->update(['complaint_assign_status' => $code]);
        $ticket->update(['ticket_status' => $code]);

        if ($code === self::STATUS_ATTENDED) {
            self::notifyAttended($ticket, $report);
        }
        return $report->fresh();
    }

    /** The coordinator + supervisor notification the desktop sends on "Attended". */
    private static function notifyAttended(ComplaintChecklist $ticket, ComplaintServiceReport $report): void
    {
        $enquiry = ComplaintEnquiry::find($ticket->complaint_enquiries_id);
        clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification', $enquiry->id);
        $enquiry->text = 'Complaint - ' . $enquiry->complaint_no . ' Tickets-' . $ticket->complaint_ticket_no . ' To Attended ';
        $enquiry->href = route('complaintReview.view', $report->id);
        event(new ComplaintAssigned($enquiry, User::role('maintenance_coordinator')->get()));
        if ($ticket->assigned_to) {
            event(new ComplaintAssigned($enquiry, User::where('id', $ticket->assigned_to)->get()));
        }
    }

    /**
     * Desktop generateServiceReport minus the Session: stage 705, ticket 4,
     * report 3 + tenant signature, complaint closed, coordinator notification,
     * tenant email/SMS.
     */
    public static function close(ComplaintChecklist $ticket, User $are, ?string $signatureDataUrl, bool $skipped): ComplaintServiceReport
    {
        self::assertMine($ticket, $are);
        $report = self::reportFor($ticket);
        if (!$report || (int) $report->complaint_assign_status !== self::STATUS_COMPLETED) {
            throw new InvalidStateException('Mark the ticket Completed before closing it.');
        }
        if ($signatureDataUrl === null && !$skipped) {
            throw new InvalidStateException('A tenant signature is required, or mark the signature as skipped.');
        }
        if ($skipped && !ComplaintServiceReportImage::where('complaint_service_report_id', $report->id)->where('stage', '>=', self::PHOTO_AFTER)->exists()) {
            throw new InvalidStateException('When the signature is skipped an "after" photo is required.');
        }

        return DB::transaction(function () use ($ticket, $are, $report, $signatureDataUrl, $skipped) {
            $enquiry = ComplaintEnquiry::find($ticket->complaint_enquiries_id);
            $signaturePath = $signatureDataUrl ? MobileImage::storeSignature($signatureDataUrl, "complaints/{$ticket->id}") : null;

            $ticket->update(['ticket_status' => self::STATUS_CLOSED, 'work_flow_processes_code' => self::STAGE_CLOSED]);
            $report->update(['complaint_assign_status' => self::STATUS_COMPLETED, 'tenant_signature' => $signaturePath, 'tenant_id' => $enquiry->tenant_id]);
            $enquiry->update(['complaint_status' => 1]);
            if ($skipped) {
                self::addNote($ticket, $are, 'Tenant signature skipped', null);
            }

            clearNotification('Modules\Maintenance\Notifications\ComplaintEnquiryNotification', $enquiry->id);
            $enquiry->text = 'Complaint - ' . $enquiry->complaint_no . ' Tickets-' . $ticket->complaint_ticket_no . ' To Closed ';
            event(new ComplaintAssigned($enquiry, User::role('maintenance_coordinator')->get()));

            $enquiry->subject = 'Complaint Completed';
            $enquiry->textContent = 'Your complaint No ' . $enquiry->complaint_no . ' is successfully attended by Al Habib Maintenance Team. Thanks for your kind co-operation.';
            $tenant = $enquiry->tenant;
            $email = $tenant->tenant_personal_email ?? $tenant->tenant_contact_email ?? null;
            $mobile = $tenant->tenant_contact_no ?? $tenant->tenant_residence_tel ?? null;
            if ($email) {
                Mail::to($email)->send(new ComplaintStageEmail($enquiry, $tenant->tenant_name));
            } elseif ($mobile) {
                sendSms($mobile, 'Complaint Completed', 'optional data');
            }
            return $report->fresh();
        });
    }
}
