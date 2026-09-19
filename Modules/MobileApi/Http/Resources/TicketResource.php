<?php

namespace Modules\MobileApi\Http\Resources;

use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Maintenance\Entities\ComplaintServiceReportImage;
use Modules\Maintenance\Entities\ComplaintServiceReportInv;
use Modules\Maintenance\Entities\ComplaintServiceReportNote;
use Modules\Maintenance\Services\TicketAttendance;
use Modules\MobileApi\Http\Controllers\FileController;

class TicketResource extends TicketListResource
{
    public function toArray($request): array
    {
        $e = $this->complaintEnquiry;
        $report = TicketAttendance::reportFor($this->resource);

        $previous = ComplaintEnquiry::query()
            ->where('id', '!=', $e->id)
            ->when($e->unit_id, fn ($q) => $q->where('unit_id', $e->unit_id), fn ($q) => $q->where('building_id', $e->building_id))
            ->orderByDesc('complaint_date')->limit(5)
            ->get(['id', 'complaint_no', 'complaint_date', 'complaint_status']);

        return parent::toArray($request) + [
            'flags' => [
                'tenant_status' => $e->tenant_status,
                'complainer_category' => $e->complainer_category,
            ],
            'report' => $report ? [
                'id' => $report->id,
                'service_report_no' => $report->service_report_no,
                'status' => (int) $report->complaint_assign_status,
                'signature_url' => FileController::signedUrl($report->tenant_signature),
                'notes' => ComplaintServiceReportNote::where('complaint_service_report_id', $report->id)->orderBy('id')->get()
                    ->map(fn ($n) => ['id' => $n->id, 'text' => $n->desc, 'stage' => (int) $n->stage, 'created_at' => $n->created_at])->values(),
                'photos' => ComplaintServiceReportImage::where('complaint_service_report_id', $report->id)->orderBy('id')->get()
                    ->map(fn ($p) => [
                        'id' => $p->id, 'stage' => TicketAttendance::photoStageName((int) $p->stage),
                        'url' => FileController::signedUrl($p->image_path_file_name), 'thumb_url' => FileController::signedUrl($p->image_path_thumbnail),
                        'created_at' => $p->created_at,
                    ])->values(),
                'items' => ComplaintServiceReportInv::with('inventory')->where('complaint_service_report_id', $report->id)->orderBy('id')->get()
                    ->map(fn ($i) => ['id' => $i->id, 'inventory_id' => $i->inventory_id, 'name' => optional($i->inventory)->inventories_name, 'quantity' => (float) $i->quantity])->values(),
            ] : null,
            'previous_complaints' => $previous->map(fn ($p) => [
                'id' => $p->id, 'complaint_no' => $p->complaint_no, 'complaint_date' => $p->complaint_date, 'complaint_status' => (int) $p->complaint_status,
            ])->values(),
        ];
    }
}
