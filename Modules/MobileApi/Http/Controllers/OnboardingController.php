<?php

namespace Modules\MobileApi\Http\Controllers;

use Illuminate\Http\Request;
use Modules\BackOffice\Entities\TenantOnboarding as TenantOnboardingModel;
use Modules\BackOffice\Services\TenantOnboarding;
use Modules\MobileApi\Http\Requests\OnboardingCompleteRequest;
use Modules\MobileApi\Http\Requests\OnboardingEntriesRequest;
use Modules\MobileApi\Http\Requests\OnboardingPhotoRequest;
use Modules\MobileApi\Http\Resources\OnboardingListResource;
use Modules\MobileApi\Http\Resources\OnboardingResource;
use Modules\MobileApi\Services\MobileImage;

class OnboardingController extends ApiController
{
    private function onboarding(Request $request, int $id): TenantOnboardingModel
    {
        $o = TenantOnboardingModel::with('tenantContract.tenant', 'tenantContract.unit', 'tenantContract.building', 'assignedUser')->findOrFail($id);
        TenantOnboarding::assertMine($o, $request->user());
        return $o;
    }

    public function index(Request $request)
    {
        $request->validate([
            'tab' => 'required|in:open,completed',
            'building_id' => 'nullable|integer',
            'updated_since' => 'nullable|date',
            'per_page' => 'nullable|integer|max:100',
        ]);
        $q = TenantOnboarding::queue($request->user(), $request->tab, $request->integer('building_id') ?: null);
        if ($request->updated_since) {
            $q->where('tenant_onboardings.updated_at', '>=', $request->date('updated_since'));
        }
        return OnboardingListResource::collection($q->paginate($request->integer('per_page') ?: 25));
    }

    public function show(Request $request, int $id)
    {
        return new OnboardingResource($this->onboarding($request, $id));
    }

    public function entries(OnboardingEntriesRequest $request, int $id)
    {
        $o = TenantOnboarding::saveEntries($this->onboarding($request, $id), $request->user(), $request->validated()['entries']);
        return new OnboardingResource($o);
    }

    public function photo(OnboardingPhotoRequest $request, int $id)
    {
        $o = $this->onboarding($request, $id);
        $stored = MobileImage::store($request->file('photo'), "onboarding/{$o->id}");
        $photo = TenantOnboarding::addPhoto($o, $request->user(), $request->integer('item_id'), $stored['path'], $stored['thumb']);
        return response()->json(['data' => [
            'id' => $photo->id, 'item_id' => $request->integer('item_id'),
            'url' => FileController::signedUrl($stored['path']), 'thumb_url' => FileController::signedUrl($stored['thumb']),
        ]], 201);
    }

    public function complete(OnboardingCompleteRequest $request, int $id)
    {
        $o = TenantOnboarding::complete($this->onboarding($request, $id), $request->user(), $request->signature, $request->tenant_remark);
        return new OnboardingResource($o);
    }

    public function pdf(Request $request, int $id)
    {
        $o = $this->onboarding($request, $id);
        $path = $o->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($o->pdf_path) ? $o->pdf_path : TenantOnboarding::pdfPath($o);
        return response()->json(['url' => FileController::signedUrl($path)]);
    }
}
