<?php

namespace Modules\MobileApi\Http\Controllers;

use Illuminate\Http\Request;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Services\TerminationInspection;
use Modules\MobileApi\Http\Requests\InspectionDraftRequest;
use Modules\MobileApi\Http\Requests\InspectionPhotoRequest;
use Modules\MobileApi\Http\Requests\InspectionSubmitRequest;
use Modules\MobileApi\Http\Resources\TerminationListResource;
use Modules\MobileApi\Http\Resources\TerminationResource;
use Modules\MobileApi\Services\MobileImage;

class TerminationController extends ApiController
{
    /** The row must be in the ARE's buildings and, once assigned, assigned to them. */
    private function termination(Request $request, int $id): Termination
    {
        $t = Termination::with('tenantContract.building', 'tenantContract.tenant', 'tenantContract.unit')->findOrFail($id);
        TerminationInspection::assertInScope($t, $request->user());
        TerminationInspection::assertMineOrUnassigned($t, $request->user());
        return $t;
    }

    public function index(Request $request)
    {
        $request->validate([
            'tab' => 'required|in:pending,inspected,taken_over',
            'building_id' => 'nullable|integer',
            'updated_since' => 'nullable|date',
            'per_page' => 'nullable|integer|max:100',
        ]);
        $q = TerminationInspection::queue($request->user(), $request->tab, $request->integer('building_id') ?: null);
        if ($request->updated_since) {
            $q->where('termination.updated_at', '>=', $request->date('updated_since'));
        }
        return TerminationListResource::collection($q->paginate($request->integer('per_page') ?: 25));
    }

    public function show(Request $request, int $id)
    {
        return new TerminationResource($this->termination($request, $id));
    }

    public function saveInspection(InspectionDraftRequest $request, int $id)
    {
        $draft = TerminationInspection::saveDraft($this->termination($request, $id), $request->user(), $request->validated());
        return new TerminationResource($draft);
    }

    public function photo(InspectionPhotoRequest $request, int $id)
    {
        $t = $this->termination($request, $id);
        $stored = MobileImage::store($request->file('photo'), "terminations/{$t->id}");
        $doc = TerminationInspection::addPhoto($t, $request->user(), $stored['path'], $stored['thumb'], $request->type);
        return response()->json(['data' => [
            'id' => $doc->id, 'type' => $request->type,
            'url' => FileController::signedUrl($stored['path']), 'thumb_url' => FileController::signedUrl($stored['thumb']),
        ]], 201);
    }

    public function submit(InspectionSubmitRequest $request, int $id)
    {
        $next = TerminationInspection::submit($this->termination($request, $id), $request->user(), $request->signature, $request->tenant_remark);
        return new TerminationResource($next->fresh());
    }

    public function referBack(Request $request, int $id)
    {
        $t = $this->termination($request, $id);
        $comment = $t->supervisor_comment ?: ((int) $t->is_resubmit === 2 || $t->termination_refer_back ? $t->termination_notes : null);
        return response()->json(['data' => $comment !== null ? ['comment' => $comment, 'at' => $t->updated_at] : null]);
    }

    public function pdf(Request $request, int $id)
    {
        $t = $this->termination($request, $id);
        return response()->json(['url' => FileController::signedUrl(TerminationInspection::pdfPath($t))]);
    }
}
