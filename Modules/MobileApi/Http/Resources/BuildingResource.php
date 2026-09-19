<?php

namespace Modules\MobileApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'code' => $this->building_code, 'name' => $this->building_name];
    }
}
