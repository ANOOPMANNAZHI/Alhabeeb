<?php

namespace Modules\Masters\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class BuldingInsuranceController extends \Illuminate\Http\Resources\Json\JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
