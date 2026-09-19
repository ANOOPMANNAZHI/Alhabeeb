<?php

namespace Modules\MobileApi\Http\Resources;

use Modules\BackOffice\Services\TenantOnboarding;
use Modules\MobileApi\Http\Controllers\FileController;

class OnboardingResource extends OnboardingListResource
{
    public function toArray($request): array
    {
        $detail = TenantOnboarding::detail($this->resource);
        $detail['items'] = array_map(function ($row) {
            $row['photos'] = array_map(fn ($p) => $p + [
                'url' => FileController::signedUrl($p['path']),
                'thumb_url' => FileController::signedUrl($p['thumb'] ?: $p['path']),
            ], $row['photos']);
            return $row;
        }, $detail['items']);

        return parent::toArray($request) + $detail + [
            'tenant_remark' => $this->tenant_remark,
            'signature_url' => FileController::signedUrl($this->tenant_signature),
            'pdf_url' => FileController::signedUrl($this->pdf_path),
            'verified_at' => $this->verified_at,
        ];
    }
}
