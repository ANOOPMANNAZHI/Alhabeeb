<?php

namespace Modules\MobileApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TerminationListResource extends JsonResource
{
    public function toArray($request): array
    {
        $c = $this->tenantContract;
        return [
            'id' => $this->id,
            'contract_id' => $this->contract_id,
            'contract_no' => optional($c)->tenant_contract_no,
            'stage' => (int) $this->work_flow_processes_code,
            'termination_date' => $this->termination_date,
            'termination_type_status' => $this->termination_type_status,
            'overdue' => $this->termination_date ? $this->termination_date->lt(now()->startOfDay()) : false,
            'assigned_to' => $this->assigned_to,
            'draft_saved' => $this->termination_notes !== null,
            'referred_back' => (int) $this->is_resubmit === 2 || (bool) $this->termination_refer_back,
            'building' => ['id' => optional($c)->building_id, 'name' => optional(optional($c)->building)->building_name],
            'unit' => ['id' => optional($c)->unit_id, 'no' => optional(optional($c)->unit)->unit_no],
            'tenant' => [
                'name' => optional(optional($c)->tenant)->tenant_name,
                'mobile' => optional(optional($c)->tenant)->tenant_contact_no ?: optional(optional($c)->tenant)->tenant_residence_tel,
            ],
            'net' => (float) $this->termination_net_amount,
            'updated_at' => $this->updated_at,
        ];
    }
}
