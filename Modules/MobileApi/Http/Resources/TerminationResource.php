<?php

namespace Modules\MobileApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Services\TerminationInspection;
use Modules\BackOffice\Services\TerminationWorkflow;
use Modules\MobileApi\Http\Controllers\FileController;

class TerminationResource extends JsonResource
{
    public function toArray($request): array
    {
        $detail = TerminationInspection::detail($this->resource);

        $detail['photos'] = array_map(fn ($p) => $p + [
            'url' => FileController::signedUrl($p['path']),
            'thumb_url' => FileController::signedUrl($p['thumb'] ?: $p['path']),
        ], $detail['photos']);
        $detail['open_documents'] = array_map(fn ($d) => $d + ['url' => FileController::signedUrl($d['path'])], $detail['open_documents']);

        $signed = $detail['draft']['signed'] ?? false;
        $sigRow = $detail['draft']['id'] ?? null;
        $detail['signature_url'] = $this->termination_tenant_signature ? FileController::signedUrl($this->termination_tenant_signature) : null;

        $pdfPath = "mobile/terminations/{$this->id}/inspection.pdf";
        $detail['pdf_url'] = ((int) $this->work_flow_processes_code >= TerminationWorkflow::TAKEN_OVER && Storage::disk('local')->exists($pdfPath))
            ? FileController::signedUrl($pdfPath) : null;

        $detail['template'] = TerminationInspection::template();
        return $detail;
    }
}
