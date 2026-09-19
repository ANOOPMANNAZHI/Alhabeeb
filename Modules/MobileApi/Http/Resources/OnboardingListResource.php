<?php

namespace Modules\MobileApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OnboardingListResource extends JsonResource
{
    public function toArray($request): array
    {
        $c = $this->tenantContract;
        $tenant = optional($c)->tenant;
        return [
            'id' => $this->id,
            'contract_id' => $this->tenant_contract_id,
            'contract_no' => optional($c)->tenant_contract_no,
            'contract_start' => optional($c)->tenant_contract_start_date,
            'status' => $this->status,
            'building' => ['id' => $this->building_id, 'name' => optional(optional($c)->building)->building_name],
            'unit' => ['id' => $this->unit_id, 'no' => optional(optional($c)->unit)->unit_no],
            'tenant' => [
                'name' => optional($tenant)->tenant_name,
                'mobile' => optional($tenant)->tenant_contact_no ?: optional($tenant)->tenant_residence_tel,
            ],
            'assigned_user_id' => $this->assigned_user_id,
            'assigned_user' => optional($this->assignedUser)->username,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
