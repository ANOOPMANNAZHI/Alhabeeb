<?php

namespace Modules\MobileApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketListResource extends JsonResource
{
    public function toArray($request): array
    {
        $e = $this->complaintEnquiry;
        return [
            'id' => $this->id,
            'ticket_no' => $this->complaint_ticket_no,
            'complaint_no' => $e->complaint_no,
            'complaint_date' => $e->complaint_date,
            'description' => $this->checklist_desc,
            'work' => optional($this->work)->works_desc,
            'priority' => $e->priority_status,
            'stage' => (int) $this->work_flow_processes_code,
            'status' => (int) $this->ticket_status,
            'building' => ['id' => $e->building_id, 'name' => optional($e->building)->building_name],
            'unit' => ['id' => $e->unit_id, 'no' => optional($e->unit)->unit_no],
            'tenant' => ['name' => $e->complainer_name, 'mobile' => $e->complaint_mob_no],
            'sub_assigned_to' => $this->sub_assigned_to,
            'updated_at' => $this->updated_at,
        ];
    }
}
