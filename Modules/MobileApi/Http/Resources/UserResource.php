<?php

namespace Modules\MobileApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'username' => $this->username, 'email' => $this->email,
            'name' => optional($this->employee)->employee_name ?: $this->username,
            'mobile' => optional($this->employee)->employee_contact_no,
            'roles' => $this->getRoleNames()->values(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
        ];
    }
}
